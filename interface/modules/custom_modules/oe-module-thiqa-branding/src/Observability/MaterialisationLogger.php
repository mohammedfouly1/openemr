<?php

/**
 * The branding layer's single vocabulary of materialisation log events.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Observability;

use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Materialisation\MaterialisationResult;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Plan §4.3 WP-2.12: "structured PSR-3 logs on materialisation start/success/failure …
 * a stale tenant is detectable without querying the tenant DB by hand".
 *
 * Two properties this class exists to guarantee, neither of which survives being left to
 * each call site:
 *
 *  1. **Every message is a constant.** No value is ever concatenated or interpolated into
 *     the message text. The tenant id, the revision and the outcome travel in the PSR-3
 *     context array, so a log aggregator can group by message and filter by field. A
 *     message such as "materialised site tenantalpha to revision 7" is one distinct
 *     string per tenant per revision and is unqueryable by construction.
 *  2. **Every event carries the same three keys.** `event`, `site` and `target_revision`
 *     on all of them; `outcome` on every terminal event. An alert rule can therefore be
 *     written once against the field names rather than per call site.
 *
 * `started()` carries no `outcome` key on purpose — at that point there is no outcome, and
 * a placeholder value would make "outcome=..." mean two different things in one stream.
 *
 * This is a thin wrapper, not a logger: it holds no state, decides no destination and
 * swallows nothing. It is safe to construct with any PSR-3 implementation, including the
 * NullLogger used by the isolated suite.
 */
final readonly class MaterialisationLogger
{
    /** Shared prefix so one glob (`branding.materialisation.*`) selects the whole stream. */
    public const EVENT_PREFIX = 'branding.materialisation.';

    public const EVENT_STARTED = self::EVENT_PREFIX . 'started';

    /**
     * An unhandled error escaped the materialiser itself.
     *
     * Distinct from the `failed` outcome: `failed` is the materialiser reporting a
     * transient fault it handled and unwound, whereas `crashed` means it did not get to
     * report anything, so the tenant's state is only known to be "revision n-1 or worse".
     */
    public const EVENT_CRASHED = self::EVENT_PREFIX . 'crashed';

    public function __construct(private LoggerInterface $logger)
    {
    }

    /** Plan §4.4 step 1: the job has been received and is about to be validated. */
    public function started(SiteId $site, BrandingRevision $target): void
    {
        $this->logger->info('Branding materialisation started', [
            'event' => self::EVENT_STARTED,
            'site' => $site->value,
            'target_revision' => $target->value,
        ]);
    }

    /**
     * Log the terminal event for a completed attempt and return its outcome.
     *
     * Returning the outcome is what keeps the log and the caller's exit code derived from
     * the same value: a caller cannot log one thing and branch on another.
     */
    public function outcome(
        SiteId $site,
        BrandingRevision $target,
        MaterialisationResult $result,
    ): MaterialisationOutcome {
        $outcome = MaterialisationOutcome::of($result);

        $this->logger->log($outcome->logLevel(), $outcome->logMessage(), [
            'event' => self::EVENT_PREFIX . $outcome->value,
            'site' => $site->value,
            'target_revision' => $target->value,
            'outcome' => $outcome->value,
            'live_revision' => $result->revision()->value,
            'retryable' => $result->isRetryable(),
            'messages' => $result->messages(),
        ]);

        return $outcome;
    }

    /**
     * The materialiser threw instead of returning a result.
     *
     * The throwable goes in the context under the conventional `exception` key, which every
     * PSR-3 implementation renders itself. It is never rendered into the message, and never
     * reaches operator-facing output: its text can carry SQL and filesystem paths.
     */
    public function crashed(SiteId $site, BrandingRevision $target, Throwable $throwable): void
    {
        $this->logger->error('Branding materialisation ended in an unhandled error', [
            'event' => self::EVENT_CRASHED,
            'site' => $site->value,
            'target_revision' => $target->value,
            'outcome' => MaterialisationOutcome::Failed->value,
            'exception' => $throwable,
        ]);
    }
}
