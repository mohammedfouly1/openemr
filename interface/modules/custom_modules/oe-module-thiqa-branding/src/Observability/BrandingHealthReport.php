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

namespace OpenEMR\Modules\ThiqaBranding\Observability;

use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;

/**
 * A value, not a boolean. A health check that answered only yes/no would force every
 * caller — the console command, a monitoring endpoint, an operator reading a log — to
 * re-derive the *reason* from the tenant database, which is precisely the manual step
 * plan §4.3 WP-2.12 exists to remove.
 *
 * Everything here is already safe to print: the inconsistencies are enum cases with fixed
 * descriptions, and no field carries a filesystem path or an exception message.
 *
 * `ageInSeconds` is null exactly when `materialisedAt` is null. It is precomputed rather
 * than derived on demand so the report stays a snapshot: two reads of the same report can
 * never disagree because the clock moved between them.
 */
final readonly class BrandingHealthReport
{
    /**
     * @param list<BrandingInconsistency> $inconsistencies
     */
    private function __construct(
        public SiteId $site,
        public BrandingRevision $revision,
        public ?DateTimeImmutable $materialisedAt,
        public ?int $ageInSeconds,
        public bool $lightStylesheetPresent,
        public bool $darkStylesheetPresent,
        public BrandingHealthStatus $status,
        public array $inconsistencies,
    ) {
        if ($status === BrandingHealthStatus::Inconsistent && $inconsistencies === []) {
            throw new LogicException('An inconsistent branding health report must name its inconsistencies.');
        }

        if (($materialisedAt === null) !== ($ageInSeconds === null)) {
            throw new LogicException('A branding health report must carry an age exactly when it has a stamp.');
        }
    }

    /**
     * The ordinary case: state was read, and the verdict follows from it.
     *
     * @param list<BrandingInconsistency> $inconsistencies
     */
    public static function of(
        SiteId $site,
        BrandingRevision $revision,
        ?DateTimeImmutable $materialisedAt,
        ?int $ageInSeconds,
        bool $lightStylesheetPresent,
        bool $darkStylesheetPresent,
        BrandingHealthStatus $status,
        array $inconsistencies,
    ): self {
        return new self(
            $site,
            $revision,
            $materialisedAt,
            $ageInSeconds,
            $lightStylesheetPresent,
            $darkStylesheetPresent,
            $status,
            $inconsistencies,
        );
    }

    /**
     * State could not be read. Nothing is claimed about revision, stamp or stylesheets.
     *
     * Revision reads as initial() rather than as a guess: reporting a revision the check
     * did not actually observe would be worse than reporting none.
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
            BrandingHealthStatus::Unreadable,
            [BrandingInconsistency::StateUnreadable],
        );
    }

    /** True when a monitor or CI job should treat this report as a failure. */
    public function isFailure(): bool
    {
        return $this->status->isFailure();
    }

    public function hasInconsistencies(): bool
    {
        return $this->inconsistencies !== [];
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

    /**
     * One operator-facing line per inconsistency, in detection order.
     *
     * @return list<string>
     */
    public function messages(): array
    {
        return array_map(
            static fn (BrandingInconsistency $inconsistency): string => $inconsistency->description(),
            $this->inconsistencies,
        );
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
     * value is a scalar or a list of scalars: nested objects do not survive either.
     *
     * @return array{
     *     site: string,
     *     status: string,
     *     revision: int,
     *     materialised: bool,
     *     materialised_at: string|null,
     *     age_seconds: int|null,
     *     stylesheet_light: bool,
     *     stylesheet_dark: bool,
     *     inconsistencies: list<string>
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
            'stylesheet_light' => $this->lightStylesheetPresent,
            'stylesheet_dark' => $this->darkStylesheetPresent,
            'inconsistencies' => array_map(
                static fn (BrandingInconsistency $inconsistency): string => $inconsistency->value,
                $this->inconsistencies,
            ),
        ];
    }
}
