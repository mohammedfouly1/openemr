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
use OpenEMR\Modules\ThiqaBranding\Config\TokenOverlay;
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
 * **It measures the served state.** A tenant's branding reaches a browser by exactly one
 * route: the overlay stored in `saas_branding_tokens_light`/`_dark` decides whether
 * BrandingService emits a `<link>` at all, the revision keys that link's URL, and
 * `public/branding-tokens.php` renders the overlay when the link is followed. Those facts,
 * plus the materialisation stamp written with them, are the served plane, and only a
 * disagreement among them can change what a user sees.
 *
 * The generated `public/branding/<site>/tokens-{light,dark}.css` files are a second,
 * unserved route: TokenCssWriter still commits them on every materialisation, including
 * for an empty overlay, and nothing but this check's own probe ever opens the directory
 * (dependency D-8). Their state is still reported — a missing file after a materialisation
 * is real evidence of a run that did not finish — but as an advisory on its own plane,
 * never as a failure. Finding S2-P1-18 is what this separation repairs: cross-referencing
 * the revision against file existence reported the live tenant as `inconsistent`, exit 1,
 * while its served branding was completely correct.
 *
 * **The overlay is read through the runtime's own parser.** TokenOverlay::fromJson() is
 * what BrandingConfigFactory uses on every request, so "does this tenant serve an overlay"
 * is answered here exactly as the endpoint will answer it, rather than approximated with a
 * second emptiness test that could disagree with the page.
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

        $rawStamp = $this->rawValue($stored, BrandingGlobalKey::MaterialisedAt);
        $rawLightOverlay = $this->rawValue($stored, BrandingGlobalKey::TokensLight);
        $rawDarkOverlay = $this->rawValue($stored, BrandingGlobalKey::TokensDark);

        $lightOverlay = TokenOverlay::fromJson($rawLightOverlay);
        $darkOverlay = TokenOverlay::fromJson($rawDarkOverlay);

        $findings = [];

        $materialisedAt = null;
        if ($rawStamp !== '') {
            $parsed = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $rawStamp);
            if ($parsed === false) {
                $findings[] = BrandingInconsistency::UnreadableMaterialisationStamp;
            } else {
                $materialisedAt = $parsed;
            }
        }

        $age = $materialisedAt instanceof DateTimeImmutable
            ? $this->clock->now()->getTimestamp() - $materialisedAt->getTimestamp()
            : null;

        if ($age !== null && $age < 0) {
            $findings[] = BrandingInconsistency::StampInTheFuture;
        }

        // Stored but unrenderable, on either variant, is one finding: the operator's next
        // step is the same whichever variant lost its overlay.
        if ($this->isUnrenderable($rawLightOverlay, $lightOverlay)
            || $this->isUnrenderable($rawDarkOverlay, $darkOverlay)) {
            $findings[] = BrandingInconsistency::UnrenderableTokenOverlay;
        }

        $servesOverlay = !$lightOverlay->isEmpty() || !$darkOverlay->isEmpty();

        if ($revision->isMaterialised()) {
            if (!$light || !$dark) {
                $findings[] = BrandingInconsistency::RevisionWithoutStylesheet;
            }

            if ($rawStamp === '') {
                $findings[] = BrandingInconsistency::MissingMaterialisationStamp;
            }
        } else {
            if ($servesOverlay) {
                $findings[] = BrandingInconsistency::OverlayWithoutRevision;
            }

            if ($light || $dark) {
                $findings[] = BrandingInconsistency::StylesheetWithoutRevision;
            }

            if ($rawStamp !== '') {
                $findings[] = BrandingInconsistency::StampWithoutRevision;
            }
        }

        $inconsistencies = $this->onPlane($findings, BrandingObservationPlane::Served);
        $advisories = $this->onPlane($findings, BrandingObservationPlane::StaticArtefact);

        return BrandingHealthReport::of(
            $site,
            $revision,
            $materialisedAt,
            $age,
            $light,
            $dark,
            $servesOverlay,
            $lightOverlay->count(),
            $darkOverlay->count(),
            $this->statusFor($revision, $age, $inconsistencies),
            $inconsistencies,
            $advisories,
        );
    }

    /** The configured age, in seconds, beyond which a materialisation counts as stale. */
    public function stalenessThresholdSeconds(): int
    {
        return $this->stalenessThresholdSeconds;
    }

    /**
     * A stored overlay that produced nothing.
     *
     * TokenOverlay::fromJson() is total: blank, malformed and shape-violating documents all
     * come back empty. Only the first of those is a legitimate "no overlay", so a non-blank
     * raw value with an empty parse is the silent-degradation case.
     */
    private function isUnrenderable(string $raw, TokenOverlay $overlay): bool
    {
        return $raw !== '' && $overlay->isEmpty();
    }

    /**
     * The trimmed value behind one branding global, with absent and blank collapsed.
     *
     * The same rule BrandingConfigFactory::raw() applies, for the same reason: a blank
     * global is indistinguishable from an unset one at the globals layer, so the check must
     * not treat a row of spaces as configuration the page would honour.
     *
     * @param array<string, string> $stored
     */
    private function rawValue(array $stored, BrandingGlobalKey $key): string
    {
        return trim($stored[$key->value] ?? '');
    }

    /**
     * @param list<BrandingInconsistency> $findings
     *
     * @return list<BrandingInconsistency>
     */
    private function onPlane(array $findings, BrandingObservationPlane $plane): array
    {
        return array_values(array_filter(
            $findings,
            static fn (BrandingInconsistency $finding): bool => $finding->plane() === $plane,
        ));
    }

    /**
     * Inconsistency outranks everything: a contradictory state cannot also be called
     * healthy, and its age is not worth reporting on until it is resolved.
     *
     * Only served-plane findings reach this method. Advisories about the unserved static
     * files are deliberately not an input: no state of a file nothing reads can make a
     * rendered page wrong, and failing on one produced exactly the false alarm S2-P1-18
     * recorded.
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
