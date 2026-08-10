<?php

/**
 * Resolves a logo slot to a revision-stamped, tenant-correct BrandAsset.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Asset;

use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfig;
use OpenEMR\Services\LogoService;

/**
 * The only place the branding layer talks to core's LogoService (plan §3.8.1).
 *
 * Core already scopes the lookup to the active site and already returns a path carrying
 * its own cache buster, `?t=<mtime>`. This class adds exactly one thing: the tenant's
 * monotonic branding revision, APPENDED as `&rev=<n>`.
 *
 * The append-never-replace rule is the whole point. `?t=` alone can collide across a
 * restore from backup, so it cannot express "the branding changed"; `&rev=` alone would
 * discard the file-level identity core maintains. Both identifiers are required, in the
 * fixed order `?t=` then `&rev=`, so that query-string-keyed proxies and CDNs do not
 * fragment the cache over parameter ordering.
 *
 * Plane 3 constraint (locked Q76 / C5): resolution is a pure function of the injected
 * LogoService and the already-parsed config. No network call, no database query, no
 * global state.
 */
final readonly class BrandAssetResolver
{
    /** The branding layer's own cache identifier. Never replaces a core one. */
    private const REVISION_PARAMETER = 'rev';

    /** Matches an already-present `rev` parameter in either leading or trailing position. */
    private const REVISION_PRESENT_PATTERN = '/[?&]rev=/';

    public function __construct(private LogoService $logoService)
    {
    }

    /**
     * Resolve one slot for the current tenant.
     *
     * The whole BrandingConfig is taken rather than a revision plus two name strings: the
     * three values always travel together, and passing them separately would put two
     * same-typed strings side by side in the signature, which is exactly the transposition
     * hazard domain primitives exist to remove.
     */
    public function resolve(LogoSlot $slot, BrandingConfig $config): BrandAsset
    {
        $revision = new BrandingRevision($config->revision);
        $corePath = $this->lookUp($slot);

        // Empty means core found nothing for this slot in this site's scope. Rendering
        // stops here: there is no fallback path, because every candidate fallback belongs
        // either to another tenant or to upstream branding (plan §3.9).
        if ($corePath === '') {
            return BrandAsset::missing($slot, $revision);
        }

        return new BrandAsset(
            $slot,
            $revision,
            $this->withRevision($corePath, $revision),
            $this->altText($slot, $config->productName),
            $this->altTextArabic($slot, $this->productNameArabic($config)),
        );
    }

    /**
     * Every slot, resolved in declaration order.
     *
     * @return list<BrandAsset>
     */
    public function resolveAll(BrandingConfig $config): array
    {
        return array_map(
            fn (LogoSlot $slot): BrandAsset => $this->resolve($slot, $config),
            LogoSlot::cases(),
        );
    }

    /**
     * Ask core for the slot's web path.
     *
     * LogoService is total: it catches its own failures and returns "" when nothing
     * matches, so there is nothing here to catch and nothing to recover from.
     */
    private function lookUp(LogoSlot $slot): string
    {
        return trim($this->logoService->getLogo($slot->value, $slot->filenamePattern()));
    }

    /**
     * Append the revision to whatever core produced.
     *
     * Three cases, and none of them mutates an existing parameter:
     *
     *  - the usual one, `…/logo.png?t=1700000000` -> `…/logo.png?t=1700000000&rev=7`;
     *  - no query string at all (a LogoFilterEvent listener may return a bare path)
     *    -> `?rev=7`, so the asset is still revision-keyed;
     *  - a `rev` parameter is already present -> left exactly as it is, because
     *    appending a second one would make the URL ambiguous and would break the
     *    byte-identical-on-rollback requirement (plan §3.8.1 rule 5).
     */
    private function withRevision(string $path, BrandingRevision $revision): string
    {
        if (preg_match(self::REVISION_PRESENT_PATTERN, $path) === 1) {
            return $path;
        }

        $separator = str_contains($path, '?') ? '&' : '?';

        return $path . $separator . self::REVISION_PARAMETER . '=' . $revision->cacheKey();
    }

    /**
     * The English accessible name for a slot.
     *
     * Exhaustive over the closed slot set, so a new slot cannot ship without someone
     * deciding how a screen reader announces it. The favicon is the one decorative slot:
     * browsers never expose it to assistive technology, so an alt string would be noise.
     */
    private function altText(LogoSlot $slot, string $productName): string
    {
        return match ($slot) {
            LogoSlot::CoreFavicon => '',
            LogoSlot::CoreLoginPrimary,
            LogoSlot::CoreLoginSmallPrimary,
            LogoSlot::PortalLoginPrimary => sprintf('%s logo', $productName),
            LogoSlot::CoreLoginSecondary,
            LogoSlot::CoreLoginSmallSecondary,
            LogoSlot::PortalLoginSecondary => sprintf('%s secondary logo', $productName),
            LogoSlot::CoreMenuPrimary,
            LogoSlot::PortalMenuPrimary => sprintf('%s home', $productName),
        };
    }

    /** The Arabic accessible name, so an RTL surface never falls back to the Latin string. */
    private function altTextArabic(LogoSlot $slot, string $productName): string
    {
        return match ($slot) {
            LogoSlot::CoreFavicon => '',
            LogoSlot::CoreLoginPrimary,
            LogoSlot::CoreLoginSmallPrimary,
            LogoSlot::PortalLoginPrimary => sprintf('شعار %s', $productName),
            LogoSlot::CoreLoginSecondary,
            LogoSlot::CoreLoginSmallSecondary,
            LogoSlot::PortalLoginSecondary => sprintf('شعار %s الثانوي', $productName),
            LogoSlot::CoreMenuPrimary,
            LogoSlot::PortalMenuPrimary => sprintf('%s - الصفحة الرئيسية', $productName),
        };
    }

    /** Arabic identity, degrading to the Latin wordmark only when none is configured. */
    private function productNameArabic(BrandingConfig $config): string
    {
        return $config->productNameArabic !== '' ? $config->productNameArabic : $config->productName;
    }
}
