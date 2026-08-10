<?php

/**
 * Redirects a logo slot to a variant-specific mark shipped inside this module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Listener;

use OpenEMR\Events\Services\LogoFilterEvent;
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingServiceInterface;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use Psr\Log\LoggerInterface;

/**
 * Extension point E3 (plan section 3.6): `logo.filter.url`, dispatched from
 * Services\LogoService::getLogo().
 *
 * What this exists for: the tenant's own logo binaries are delivered through E8 (the
 * per-site `OE_SITE_DIR/images/logos/<slot>/` path) and core finds them without help.
 * The one thing core cannot express is *variant*: the same navbar mark is unreadable on
 * the Saudi Dark surface, so the dark variant needs a different file. This listener
 * supplies that file, and only that file.
 *
 * ## The constraint that shapes the whole class
 *
 * LogoService re-filters anything a listener sets:
 *
 * ```php
 * if ($updatedWebPath != $webPath) {
 *     $safePaths = ModulesApplication::filterSafeLocalModuleFiles([$updatedWebPath]);
 *     return $safePaths[0] ?? '';
 * }
 * ```
 *
 * Two consequences, both stated plainly rather than hoped away:
 *
 *  1. Only a path that resolves **physically under `interface/modules/`** survives. A
 *     path pointing at `sites/`, `public/` or anywhere else is not merely ignored --
 *     `$safePaths[0] ?? ''` yields the **empty string**, and the logo disappears from the
 *     page entirely. Setting a path core will reject is therefore strictly worse than
 *     setting nothing, so this listener refuses to set one and logs instead.
 *  2. The override asset must be shipped inside this module. That is why the constructor
 *     takes the module's own asset directory and its matching web path, and why the file
 *     is confirmed to exist on disk before the event is touched: core's filter also drops
 *     paths that do not resolve to a real file.
 *
 * ## Re-entrancy
 *
 * BrandingServiceInterface::logo() resolves through BrandAssetResolver, which calls
 * LogoService::getLogo(), which dispatches this very event. Calling it from here would
 * recurse without bound. This listener therefore consults only themeVariant() and
 * revision(), neither of which touches LogoService.
 *
 * Plane 3 constraint (locked Q76 / C5): a stat call on local disk, no network, no query.
 */
final readonly class LogoOverrideListener
{
    /**
     * The only web-path prefix ModulesApplication::filterSafeLocalModuleFiles() accepts.
     */
    public const MODULE_PATH_SEGMENT = '/interface/modules/';

    /**
     * Extensions tried for a `logo.*` slot, in fixed precedence order.
     *
     * A fixed list rather than a glob: resolution must be deterministic, because two
     * files differing only in extension would otherwise make the rendered page depend on
     * filesystem ordering.
     *
     * @var list<string>
     */
    private const CANDIDATE_EXTENSIONS = ['svg', 'png', 'webp', 'jpg', 'jpeg', 'gif', 'ico'];

    /**
     * @param string $darkAssetDirectory absolute filesystem directory holding the dark
     *                                   variant marks, laid out as
     *                                   `<dir>/<slot value>/logo.<ext>` -- the same shape
     *                                   LogoService uses, so a slot maps across unchanged
     * @param string $darkAssetWebPath   web path prefix for that directory; must resolve
     *                                   under interface/modules/ or every override is
     *                                   declined
     */
    public function __construct(
        private BrandingServiceInterface $branding,
        private string $darkAssetDirectory,
        private string $darkAssetWebPath,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Listener for LogoFilterEvent::EVENT_NAME ('logo.filter.url').
     *
     * Every exit before setWebPath() leaves the event exactly as core built it, which is
     * the required behaviour whenever the branding layer has nothing configured for the
     * slot.
     *
     * The catch is a \LogicException|\RuntimeException union rather than \Throwable: see
     * StyleInjectionListener::onStyleFilter() for the guardrail that forces that and why
     * those two types are the branding runtime plane's complete throw surface.
     */
    public function onLogoFilter(LogoFilterEvent $event): void
    {
        try {
            $override = $this->overrideFor($event->getLogoType());
        } catch (\LogicException | \RuntimeException $exception) {
            $this->logger->error('Branding logo override could not be resolved', [
                'event' => LogoFilterEvent::EVENT_NAME,
                'logoType' => $event->getLogoType(),
                'exception' => $exception,
            ]);

            return;
        }

        if ($override === null) {
            return;
        }

        $event->setWebPath($override);
    }

    /**
     * The replacement web path for a slot, or null when there is nothing to replace.
     */
    private function overrideFor(string $logoType): ?string
    {
        // LogoService is called with both 'core/menu/primary' and 'core/menu/primary/'
        // depending on the caller, so the separator is normalised before the lookup.
        $slot = LogoSlot::tryFrom(trim($logoType, '/'));
        if (!$slot instanceof LogoSlot) {
            return null;
        }

        // Exhaustive on purpose: a third variant could not be added without someone
        // deciding which asset directory it reads from.
        $directory = match ($this->branding->themeVariant()) {
            // The light variant is the one core's own site assets were authored for.
            ThemeVariant::Light => null,
            ThemeVariant::Dark => $this->darkAssetDirectory,
        };

        if ($directory === null || $directory === '') {
            return null;
        }

        if (!$this->isUnderModulesTree($this->darkAssetWebPath)) {
            $this->logger->warning(
                'Branding logo override declined: the configured web path is outside '
                . 'interface/modules/, where LogoService would discard it and render no logo at all.',
                [
                    'logoType' => $logoType,
                    'configuredWebPath' => $this->darkAssetWebPath,
                    'requiredSegment' => self::MODULE_PATH_SEGMENT,
                ],
            );

            return null;
        }

        $relativePath = $this->firstExistingAsset($directory, $slot);
        if ($relativePath === null) {
            return null;
        }

        // Revision-keyed exactly as BrandAssetResolver keys a core-resolved asset. The
        // query string is stripped by filterSafeLocalModuleFiles() before it realpaths,
        // so the cache buster does not endanger the safety check.
        return rtrim($this->darkAssetWebPath, '/')
            . '/' . $relativePath
            . '?rev=' . $this->branding->revision()->cacheKey();
    }

    /**
     * The first candidate that exists on disk, as a slot-relative web path.
     */
    private function firstExistingAsset(string $directory, LogoSlot $slot): ?string
    {
        foreach ($this->candidateFilenames($slot) as $filename) {
            $relativePath = $slot->value . '/' . $filename;
            $absolutePath = rtrim($directory, '/\\')
                . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (is_file($absolutePath) && is_readable($absolutePath)) {
                return $relativePath;
            }
        }

        return null;
    }

    /**
     * Filenames to try for a slot, honouring the slot's own pattern.
     *
     * The favicon names an exact file rather than `logo.*`; every other slot is
     * extension-agnostic and expands to the fixed precedence list.
     *
     * @return list<string>
     */
    private function candidateFilenames(LogoSlot $slot): array
    {
        $pattern = $slot->filenamePattern();

        if (!str_ends_with($pattern, '.*')) {
            return [$pattern];
        }

        $stem = substr($pattern, 0, -2);

        return array_map(
            static fn (string $extension): string => $stem . '.' . $extension,
            self::CANDIDATE_EXTENSIONS,
        );
    }

    /**
     * A conservative pre-flight of core's own safety filter.
     *
     * Core remains the authority -- it realpaths against the project directory, which
     * cannot be done here without reading global state. This check only declines the
     * cases core would certainly reject, so a path that passes here may still be dropped
     * by core; a path that fails here would certainly have been.
     */
    private function isUnderModulesTree(string $webPath): bool
    {
        $path = parse_url($webPath, PHP_URL_PATH);

        return is_string($path) && str_contains($path, self::MODULE_PATH_SEGMENT);
    }
}
