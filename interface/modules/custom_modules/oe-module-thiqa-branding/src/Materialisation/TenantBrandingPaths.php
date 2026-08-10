<?php

/**
 * The single place that turns a tenant plus a slot into an absolute path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Materialisation;

use InvalidArgumentException;
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;

/**
 * Tenant scoping is enforced here once, so no caller has to remember it.
 *
 * Every path this class returns is `<root>/<site>/…`, and every variable segment is
 * drawn from a closed set: the site comes from SiteId (no separators representable),
 * the variant from ThemeVariant (a unit enum), the slot from LogoSlot (a backed enum
 * whose values are fixed literals), and the filename from AssetPlacement, which has
 * already refused anything but a bare safe name. Nothing in a Control Plane payload is
 * ever concatenated into a path directly, so a job cannot address another tenant's
 * scope — the plan's "writes only into the tenant's own scope" arrow (§3.2) is
 * structural rather than reviewed.
 *
 * The roots are injected because the two trees differ per deployment: the token CSS
 * root must live under `interface/modules/` for StyleFilterEvent to accept it (plan
 * §3.2.2), while logos live under the site's own `sites/<site>/images` tree.
 */
final readonly class TenantBrandingPaths
{
    /** Where per-tenant logo binaries sit inside a site directory. */
    private const LOGO_SUBTREE = 'images/logos';

    private string $tokenCssRoot;

    private string $sitesRoot;

    public function __construct(string $tokenCssRoot, string $sitesRoot)
    {
        $this->tokenCssRoot = self::normaliseRoot($tokenCssRoot, 'token CSS root');
        $this->sitesRoot = self::normaliseRoot($sitesRoot, 'sites root');
    }

    /** The directory holding one tenant's generated stylesheets. */
    public function tokenCssDirectory(SiteId $site): string
    {
        return $this->tokenCssRoot . '/' . $site->value;
    }

    /**
     * The generated stylesheet for one variant.
     *
     * One file per variant rather than a single `tokens.css`: CssVariableRenderer emits
     * declarations only and cannot produce a selector, so a combined file would require
     * this class to invent a variant-switching selector contract. Splitting keeps the
     * selector a fixed `:root` and leaves variant selection to the runtime resolver.
     */
    public function tokenCssFile(SiteId $site, ThemeVariant $variant): string
    {
        return $this->tokenCssDirectory($site) . '/' . self::variantFilename($variant);
    }

    /** The directory a single logo slot's binary lives in, inside the tenant's own site tree. */
    public function logoDirectory(SiteId $site, LogoSlot $slot): string
    {
        return $this->sitesRoot . '/' . $site->value . '/' . self::LOGO_SUBTREE . '/' . $slot->value;
    }

    /**
     * The absolute target for one staged logo binary.
     *
     * The filename is taken from AssetPlacement, whose constructor has already rejected
     * separators, dot forms and NUL bytes.
     */
    public function logoFile(SiteId $site, AssetPlacement $asset): string
    {
        return $this->logoDirectory($site, $asset->slot) . '/' . $asset->filename;
    }

    /** True when the path is inside the tenant's own scope. Used as a post-condition assertion. */
    public function isWithinTenantScope(SiteId $site, string $path): bool
    {
        $normalised = str_replace('\\', '/', $path);

        return str_starts_with($normalised, $this->tokenCssDirectory($site) . '/')
            || str_starts_with($normalised, $this->sitesRoot . '/' . $site->value . '/');
    }

    private static function variantFilename(ThemeVariant $variant): string
    {
        return match ($variant) {
            ThemeVariant::Light => 'tokens-light.css',
            ThemeVariant::Dark => 'tokens-dark.css',
        };
    }

    private static function normaliseRoot(string $root, string $label): string
    {
        $normalised = rtrim(str_replace('\\', '/', $root), '/');
        if ($normalised === '') {
            throw new InvalidArgumentException(sprintf('The %s must be a non-empty path.', $label));
        }

        return $normalised;
    }
}
