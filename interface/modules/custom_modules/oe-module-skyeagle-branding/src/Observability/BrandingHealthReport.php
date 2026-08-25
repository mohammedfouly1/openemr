<?php

/**
 * The structured answer to "is this tenant's branding healthy?".
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Observability;

use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;

/**
 * A value, not a boolean. A health check that answered only yes/no would force every
 * caller — the console command, a monitoring endpoint, an operator reading a log — to
 * re-derive the *reason* from the tenant database, which is precisely the manual step
 * plan §4.3 WP-2.12 exists to remove.
 *
 * Findings arrive in two lists, and the split is load-bearing rather than cosmetic:
 * `inconsistencies` are served-plane faults that change what a user sees and make the
 * report a failure, `advisories` are static-artefact observations about generated files no
 * page links (D-8) and never affect the verdict. Merging them is what made the live tenant
 * report `inconsistent` while rendering correctly (finding S2-P1-18), so the constructor
 * refuses a report whose lists are on the wrong planes.
 *
 * Everything here is already safe to print: the findings are enum cases with fixed
 * descriptions, and no field carries a filesystem path or an exception message.
 *
 * `ageInSeconds` is null exactly when `materialisedAt` is null. It is precomputed rather
 * than derived on demand so the report stays a snapshot: two reads of the same report can
 * never disagree because the clock moved between them.
 */
final readonly class BrandingHealthReport
{
    /**
     * @param list<BrandingInconsistency> $inconsistencies served-plane faults
     * @param list<BrandingInconsistency> $advisories      static-artefact observations
     */
    private function __construct(
        public SiteId $site,
        public BrandingRevision $revision,
        public ?DateTimeImmutable $materialisedAt,
        public ?int $ageInSeconds,
        public bool $lightStylesheetPresent,
        public bool $darkStylesheetPresent,
        public bool $servesTenantOverlay,
        public int $lightOverlayTokenCount,
        public int $darkOverlayTokenCount,
        public BrandingHealthStatus $status,
        public array $inconsistencies,
        public array $advisories,
    ) {
        if ($status === BrandingHealthStatus::Inconsistent && $inconsistencies === []) {
            throw new LogicException('An inconsistent branding health report must name its inconsistencies.');
        }

        if (($materialisedAt === null) !== ($ageInSeconds === null)) {
            throw new LogicException('A branding health report must carry an age exactly when it has a stamp.');
        }

        self::assertPlane($inconsistencies, BrandingObservationPlane::Served);
        self::assertPlane($advisories, BrandingObservationPlane::StaticArtefact);
    }

    /**
     * The ordinary case: state was read, and the verdict follows from it.
     *
     * @param list<BrandingInconsistency> $inconsistencies
     * @param list<BrandingInconsistency> $advisories
     */
    public static function of(
        SiteId $site,
        BrandingRevision $revision,
        ?DateTimeImmutable $materialisedAt,
        ?int $ageInSeconds,
        bool $lightStylesheetPresent,
        bool $darkStylesheetPresent,
        bool $servesTenantOverlay,
        int $lightOverlayTokenCount,
        int $darkOverlayTokenCount,
        BrandingHealthStatus $status,
        array $inconsistencies,
        array $advisories,
    ): self {
        return new self(
            $site,
            $revision,
            $materialisedAt,
            $ageInSeconds,
            $lightStylesheetPresent,
            $darkStylesheetPresent,
            $servesTenantOverlay,
            $lightOverlayTokenCount,
            $darkOverlayTokenCount,
            $status,
            $inconsistencies,
            $advisories,
        );
    }

    /**
     * State could not be read. Nothing is claimed about revision, stamp, overlay or
     * stylesheets.
     *
     * Revision reads as initial() rather than as a guess: reporting a revision the check
     * did not actually observe would be worse than reporting none. The overlay reads as
     * absent for the same reason — an unread tenant is not evidence of a served overlay.
     */
    public static function unreadable(SiteId $site): self
    {
        return new self(
            $site,
            BrandingRevision::initial(),
            null,
            null,
            false,
            false,
            false,
            0,
            0,
            BrandingHealthStatus::Unreadable,
            [BrandingInconsistency::StateUnreadable],
            [],
        );
    }

    /** True when a monitor or CI job should treat this report as a failure. */
    public function isFailure(): bool
    {
        return $this->status->isFailure();
    }

    /** True when the served branding state contradicts itself. */
    public function hasInconsistencies(): bool
    {
        return $this->inconsistencies !== [];
    }

    /** True when the generated, unserved files disagree with the served state. */
    public function hasAdvisories(): bool
    {
        return $this->advisories !== [];
    }

    public function isStale(): bool
    {
        return $this->status === BrandingHealthStatus::Stale;
    }

    /** True when both generated token stylesheets are on disk. */
    public function bothStylesheetsPresent(): bool
    {
        return $this->lightStylesheetPresent && $this->darkStylesheetPresent;
    }

    /** How many tenant token overrides the endpoint would actually render. */
    public function servedOverlayTokenCount(): int
    {
        return $this->lightOverlayTokenCount + $this->darkOverlayTokenCount;
    }

    /**
     * One operator-facing line per served-plane fault, in detection order.
     *
     * @return list<string>
     */
    public function messages(): array
    {
        return self::describe($this->inconsistencies);
    }

    /**
     * One operator-facing line per static-artefact advisory, in detection order.
     *
     * @return list<string>
     */
    public function advisoryMessages(): array
    {
        return self::describe($this->advisories);
    }

    /** The materialisation stamp as the materialiser wrote it, or null when there is none. */
    public function materialisedAtIso(): ?string
    {
        return $this->materialisedAt?->format(DateTimeInterface::ATOM);
    }

    /**
     * The whole report as a flat, machine-queryable map.
     *
     * Shaped for a PSR-3 context array and for a monitoring payload, which is why every
     * value is a scalar or a list of scalars: nested objects do not survive either. The
     * pre-existing keys keep their names and meanings so an existing dashboard query does
     * not break; the served-plane facts are additions beside them.
     *
     * @return array{
     *     site: string,
     *     status: string,
     *     revision: int,
     *     materialised: bool,
     *     materialised_at: string|null,
     *     age_seconds: int|null,
     *     serves_tenant_overlay: bool,
     *     overlay_tokens_light: int,
     *     overlay_tokens_dark: int,
     *     stylesheet_light: bool,
     *     stylesheet_dark: bool,
     *     inconsistencies: list<string>,
     *     advisories: list<string>
     * }
     */
    public function toContext(): array
    {
        return [
            'site' => $this->site->value,
            'status' => $this->status->value,
            'revision' => $this->revision->value,
            'materialised' => $this->revision->isMaterialised(),
            'materialised_at' => $this->materialisedAtIso(),
            'age_seconds' => $this->ageInSeconds,
            'serves_tenant_overlay' => $this->servesTenantOverlay,
            'overlay_tokens_light' => $this->lightOverlayTokenCount,
            'overlay_tokens_dark' => $this->darkOverlayTokenCount,
            'stylesheet_light' => $this->lightStylesheetPresent,
            'stylesheet_dark' => $this->darkStylesheetPresent,
            'inconsistencies' => self::identify($this->inconsistencies),
            'advisories' => self::identify($this->advisories),
        ];
    }

    /**
     * @param list<BrandingInconsistency> $findings
     *
     * @return list<string>
     */
    private static function describe(array $findings): array
    {
        return array_map(
            static fn (BrandingInconsistency $finding): string => $finding->description(),
            $findings,
        );
    }

    /**
     * @param list<BrandingInconsistency> $findings
     *
     * @return list<string>
     */
    private static function identify(array $findings): array
    {
        return array_map(
            static fn (BrandingInconsistency $finding): string => $finding->value,
            $findings,
        );
    }

    /**
     * @param list<BrandingInconsistency> $findings
     */
    private static function assertPlane(array $findings, BrandingObservationPlane $plane): void
    {
        foreach ($findings as $finding) {
            if ($finding->plane() !== $plane) {
                throw new LogicException(
                    'A branding health finding was reported on the wrong observation plane.',
                );
            }
        }
    }
}
