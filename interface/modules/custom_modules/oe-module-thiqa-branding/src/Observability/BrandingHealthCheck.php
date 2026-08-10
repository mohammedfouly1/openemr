<?php

/**
 * Reports whether one tenant's materialised branding is self-consistent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Observability;

use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\ThiqaBranding\Materialisation\BrandingGlobalsWriterInterface;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Plan §4.3 WP-2.12's health check, as a plain service. VerifyCommand is a thin CLI
 * wrapper over it; nothing here knows that a console exists, so the same check can back a
 * monitoring endpoint or an administration screen without being reimplemented.
 *
 * **Read-only, and structurally so.** The only database surface it holds is
 * BrandingGlobalsWriterInterface, of which it calls exactly two methods —
 * `currentRevision()` and `readBrandingGlobals()`. It never opens a transaction, so a
 * `write()` from here would throw in the shipped adapter rather than corrupt anything.
 * (The interface is the layer's only route to the tenant globals; there is no read-only
 * sibling to depend on instead, and inventing one would put a second definition of "the
 * branding globals" in the layer.)
 *
 * **No network, ever.** The check may be called from a request, so constraint C5 and
 * locked Q76 apply in full: it cannot ask the Control Plane what revision it believes is
 * current. Staleness is therefore measured purely as the age of the tenant's own
 * `saas_branding_materialised_at` stamp. That is a weaker signal than a CP comparison and
 * is reported as its own non-failing status rather than as an inconsistency.
 *
 * **What "inconsistent" means.** The materialisation transaction (plan §4.4) moves three
 * things together and writes the revision last: the token stylesheets, the stamp, and the
 * revision global. Every case in BrandingInconsistency is one of those three having moved
 * without the others — the observable signature of a run that did not finish, or of a hand
 * edit. That is the class of fault this check exists to surface.
 */
final readonly class BrandingHealthCheck
{
    /** Thirty days. A settled tenant is expected to go a long time between pushes. */
    public const DEFAULT_STALENESS_THRESHOLD_SECONDS = 2592000;

    public function __construct(
        private BrandingGlobalsWriterInterface $globals,
        private StylesheetProbeInterface $stylesheets,
        private ClockInterface $clock,
        private LoggerInterface $logger,
        private int $stalenessThresholdSeconds = self::DEFAULT_STALENESS_THRESHOLD_SECONDS,
    ) {
    }

    /**
     * Inspect one tenant. Never throws for a fault it can describe, and never mutates.
     *
     * A read failure is reported as BrandingHealthStatus::Unreadable rather than raised:
     * "the tenant database is unreachable" is a health finding, and a health check that
     * dies on the thing it is meant to detect is not a health check. The exception itself
     * is logged with its context and is never rendered into the report, because its text
     * can carry SQL and connection details.
     *
     * The caught types are the adapter's whole fault domain and no wider. Every database
     * and filesystem failure this layer can meet is a RuntimeException (SqlQueryException
     * and FilesystemException both extend it), and LogicException is the shipped adapter's
     * refusal when the site it is handed is not the site it is connected to — a
     * misconfiguration that must be reported rather than allowed to become a 500 in a
     * request that merely wanted to know the branding revision.
     *
     * CLAUDE.md's general advice is to catch `\Throwable`; this repo's own
     * ForbiddenCatchTypeRule (PHPStan level 10, CI-enforced) overrides it by forbidding any
     * catch that would suppress `\Error` or `\ErrorException`, which rules out `\Throwable`
     * and `\Exception` alike. Narrow types satisfy both: an `\Error` from a broken adapter
     * is a bug in this layer, not a finding about a tenant's branding, and it reaches the
     * global handler.
     */
    public function check(SiteId $site): BrandingHealthReport
    {
        try {
            $revision = $this->globals->currentRevision($site);
            $stored = $this->globals->readBrandingGlobals($site);
            $light = $this->stylesheets->isPresent($site, ThemeVariant::Light);
            $dark = $this->stylesheets->isPresent($site, ThemeVariant::Dark);
        } catch (LogicException | RuntimeException $exception) {
            $this->logger->error('Branding health check could not read the tenant\'s branding state', [
                'event' => 'branding.health.unreadable',
                'site' => $site->value,
                'exception' => $exception,
            ]);

            return BrandingHealthReport::unreadable($site);
        }

        $rawStamp = $stored[BrandingGlobalKey::MaterialisedAt->value] ?? '';

        $inconsistencies = [];

        $materialisedAt = null;
        if ($rawStamp !== '') {
            $parsed = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $rawStamp);
            if ($parsed === false) {
                $inconsistencies[] = BrandingInconsistency::UnreadableMaterialisationStamp;
            } else {
                $materialisedAt = $parsed;
            }
        }

        $age = $materialisedAt instanceof DateTimeImmutable
            ? $this->clock->now()->getTimestamp() - $materialisedAt->getTimestamp()
            : null;

        if ($age !== null && $age < 0) {
            $inconsistencies[] = BrandingInconsistency::StampInTheFuture;
        }

        if ($revision->isMaterialised()) {
            if (!$light || !$dark) {
                $inconsistencies[] = BrandingInconsistency::RevisionWithoutStylesheet;
            }

            if ($rawStamp === '') {
                $inconsistencies[] = BrandingInconsistency::MissingMaterialisationStamp;
            }
        } else {
            if ($light || $dark) {
                $inconsistencies[] = BrandingInconsistency::StylesheetWithoutRevision;
            }

            if ($rawStamp !== '') {
                $inconsistencies[] = BrandingInconsistency::StampWithoutRevision;
            }
        }

        return BrandingHealthReport::of(
            $site,
            $revision,
            $materialisedAt,
            $age,
            $light,
            $dark,
            $this->statusFor($revision, $age, $inconsistencies),
            $inconsistencies,
        );
    }

    /** The configured age, in seconds, beyond which a materialisation counts as stale. */
    public function stalenessThresholdSeconds(): int
    {
        return $this->stalenessThresholdSeconds;
    }

    /**
     * Inconsistency outranks everything: a contradictory state cannot also be called
     * healthy, and its age is not worth reporting on until it is resolved.
     *
     * @param list<BrandingInconsistency> $inconsistencies
     */
    private function statusFor(
        BrandingRevision $revision,
        ?int $age,
        array $inconsistencies,
    ): BrandingHealthStatus {
        if ($inconsistencies !== []) {
            return BrandingHealthStatus::Inconsistent;
        }

        if (!$revision->isMaterialised()) {
            return BrandingHealthStatus::NeverMaterialised;
        }

        if ($age !== null && $age > $this->stalenessThresholdSeconds) {
            return BrandingHealthStatus::Stale;
        }

        return BrandingHealthStatus::Healthy;
    }
}
