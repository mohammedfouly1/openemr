<?php

/**
 * The four terminal outcomes of one materialisation attempt.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Observability;

use OpenEMR\Modules\SkyEagleBranding\Materialisation\MaterialisationResult;
use Psr\Log\LogLevel;

/**
 * MaterialisationResult is a rich value with four booleans; an operator reading a log
 * line, and a cron job branching on an exit code, both want one word instead. This enum
 * is that word, and it is the *only* place the four-boolean combination is collapsed —
 * so the log field, the CLI summary and the exit code can never disagree about what
 * happened.
 *
 * Backed by a string because the value is persisted into log context arrays and read by
 * machines (log queries, alert rules), which plan §4.3 WP-2.12 requires: "a stale tenant
 * is detectable without querying the tenant DB by hand".
 *
 * Deliberately four cases and not five: "started" is not an outcome. MaterialisationLogger
 * emits a start event with no `outcome` key at all rather than inventing a pending case
 * that every `match` here would then have to absorb.
 */
enum MaterialisationOutcome: string
{
    /** The target revision is live; files and globals changed. */
    case Applied = 'applied';

    /** The tenant was already at or past the target; nothing was read, staged or written. */
    case Unchanged = 'unchanged';

    /** The payload was refused. Revision n-1 untouched; retrying the same payload refuses again. */
    case Rejected = 'rejected';

    /** A transient fault stopped the run. Revision n-1 fully active; the payload is retryable. */
    case Failed = 'failed';

    /**
     * Collapse a result into its outcome.
     *
     * The order of the tests mirrors MaterialisationResult's own invariants: a success is
     * never retryable, so `changed()` fully separates the two success cases, and
     * `isRetryable()` fully separates the two failure cases.
     */
    public static function of(MaterialisationResult $result): self
    {
        if ($result->succeeded()) {
            return $result->changed() ? self::Applied : self::Unchanged;
        }

        return $result->isRetryable() ? self::Failed : self::Rejected;
    }

    public function isSuccess(): bool
    {
        return match ($this) {
            self::Applied, self::Unchanged => true,
            self::Rejected, self::Failed => false,
        };
    }

    /**
     * The PSR-3 severity this outcome is logged at.
     *
     * A rejection is a warning rather than an error: the layer worked exactly as designed
     * and refused a bad payload. Only a transient fault, which leaves work undone that
     * nobody asked to be undone, is an error.
     */
    public function logLevel(): string
    {
        return match ($this) {
            self::Applied, self::Unchanged => LogLevel::INFO,
            self::Rejected => LogLevel::WARNING,
            self::Failed => LogLevel::ERROR,
        };
    }

    /** The constant, variable-free PSR-3 message logged for this outcome. */
    public function logMessage(): string
    {
        return match ($this) {
            self::Applied => 'Branding materialisation applied',
            self::Unchanged => 'Branding materialisation skipped: revision already applied',
            self::Rejected => 'Branding materialisation rejected the payload',
            self::Failed => 'Branding materialisation failed; the previous revision is still active',
        };
    }

    /** Operator-facing one-word summary for CLI output. */
    public function label(): string
    {
        return match ($this) {
            self::Applied => 'applied',
            self::Unchanged => 'unchanged (already at or past the target revision)',
            self::Rejected => 'rejected (the payload was refused)',
            self::Failed => 'failed (transient fault; retryable)',
        };
    }
}
