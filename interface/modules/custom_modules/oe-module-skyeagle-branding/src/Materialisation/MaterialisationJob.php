<?php

/**
 * One tenant-scoped unit of work handed to the materialiser.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

use InvalidArgumentException;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;

/**
 * Plan §4.4 step 1: `{tenant_id, target_revision, tokens[], asset_refs[], strings[]}`.
 *
 * The two overlays are typed `array<array-key, mixed>` on purpose. They are the Control
 * Plane's raw claim about what the tenant's palette should be, and this is the trust
 * boundary: narrowing them here would move validation out of TokenValidator, which is
 * the one component whose rejections are auditable. They stay untyped exactly as far as
 * BrandingMaterialiser, which re-runs the validator on them before anything is staged.
 *
 * `strings` is already narrow, because GlobalsDelta can only hold BrandingGlobalKey
 * cases — a job cannot name a global outside the layer's registry.
 *
 * The job carries no path, no root and no connection. Everything addressable is derived
 * tenant-side from `$site`, which is mandatory: there is no constructor that omits it.
 */
final readonly class MaterialisationJob
{
    /**
     * @param array<array-key, mixed> $lightOverlay untrusted; validated tenant-side
     * @param array<array-key, mixed> $darkOverlay  untrusted; validated tenant-side
     * @param list<AssetPlacement>    $assets
     */
    public function __construct(
        public SiteId $site,
        public BrandingRevision $targetRevision,
        public array $lightOverlay,
        public array $darkOverlay,
        public GlobalsDelta $strings,
        public array $assets,
    ) {
        if (!$targetRevision->isMaterialised()) {
            // Revision 0 means "never materialised"; it can be a starting state, never a target.
            throw new InvalidArgumentException('A materialisation target revision must be 1 or greater.');
        }
    }

    /** The minimal job: bump the revision, change nothing else. Useful for a forced re-emit. */
    public static function forRevision(SiteId $site, BrandingRevision $targetRevision): self
    {
        return new self($site, $targetRevision, [], [], GlobalsDelta::empty(), []);
    }

    /**
     * @param array<array-key, mixed> $lightOverlay
     * @param array<array-key, mixed> $darkOverlay
     */
    public function withOverlays(array $lightOverlay, array $darkOverlay): self
    {
        return new self(
            $this->site,
            $this->targetRevision,
            $lightOverlay,
            $darkOverlay,
            $this->strings,
            $this->assets,
        );
    }

    public function withStrings(GlobalsDelta $strings): self
    {
        return new self(
            $this->site,
            $this->targetRevision,
            $this->lightOverlay,
            $this->darkOverlay,
            $strings,
            $this->assets,
        );
    }

    /**
     * @param list<AssetPlacement> $assets
     */
    public function withAssets(array $assets): self
    {
        return new self(
            $this->site,
            $this->targetRevision,
            $this->lightOverlay,
            $this->darkOverlay,
            $this->strings,
            $assets,
        );
    }
}
