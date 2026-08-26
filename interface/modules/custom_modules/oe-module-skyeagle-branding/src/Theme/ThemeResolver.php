<?php

/**
 * Maps OpenEMR's css_header value to one of the two locked Saudi theme variants.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Theme;

use OpenEMR\Modules\SkyEagleBranding\Config\BrandingConfig;

/**
 * The single answer to "which variant am I rendering?" (plan §3.5, table row for Theme).
 *
 * `css_header` is not a bare filename at runtime. interface/globals.php:468-478 rewrites
 * it into a full web path with a cache-busting query — `/openemr/public/themes/
 * style_dark.css?v=<v_js_includes>` — and lines 571-586 may additionally swap in the
 * `rtl_` variant for right-to-left sessions, while `compact_header` carries the
 * `compact_` (or `rtl_compact_`) form of the same theme. All four shapes describe the
 * same variant, so this resolver reduces the value to its canonical stylesheet name
 * before matching.
 *
 * Locked decision Q77: there are exactly two variants, and ANY value that is not
 * recognisably the dark stylesheet resolves to Light. The fallback is deliberately the
 * product's own light theme and never an upstream-branded default — a mis-typed or
 * stale global must degrade to SkyEagle branding, not to OpenEMR branding.
 */
final readonly class ThemeResolver
{
    /** Prepended by globals.php for right-to-left sessions. */
    private const RTL_PREFIX = 'rtl_';

    /** Prepended for the compact header stylesheet, after any rtl_ prefix. */
    private const COMPACT_PREFIX = 'compact_';

    /** Which variant the main application shell renders. */
    public function resolve(BrandingConfig $config): ThemeVariant
    {
        return $this->resolveStylesheet($config->cssHeader);
    }

    /** Which variant the patient portal renders; it carries its own global. */
    public function resolvePortal(BrandingConfig $config): ThemeVariant
    {
        return $this->resolveStylesheet($config->portalCssHeader);
    }

    /**
     * Reduce any css_header spelling to a variant.
     *
     * Only an exact match on the dark stylesheet name yields Dark. Everything else —
     * the light theme, a legacy upstream theme such as style_manila.css, an empty
     * string, or anything unparseable — yields Light.
     */
    public function resolveStylesheet(string $cssHeader): ThemeVariant
    {
        return $this->canonicalFilename($cssHeader) === ThemeVariant::Dark->stylesheet()
            ? ThemeVariant::Dark
            : ThemeVariant::Light;
    }

    /** True when the active session renders right-to-left. */
    public function isRtl(BrandingConfig $config): bool
    {
        return $this->isRtlStylesheet($config->cssHeader);
    }

    /** True when the patient portal renders right-to-left. */
    public function isPortalRtl(BrandingConfig $config): bool
    {
        return $this->isRtlStylesheet($config->portalCssHeader);
    }

    /**
     * Direction is read from the stylesheet name rather than from the session.
     *
     * globals.php only swaps in the `rtl_` stylesheet once it has established the
     * language direction, so the filename is a faithful, side-effect-free proxy — and
     * unlike the session it is already in the immutable per-request config, which keeps
     * this class free of global state (locked constraint C5).
     */
    public function isRtlStylesheet(string $cssHeader): bool
    {
        return str_starts_with($this->basename($cssHeader), self::RTL_PREFIX);
    }

    /**
     * Strip the directional and density prefixes so the theme identity remains.
     *
     * The order matters and mirrors core: globals.php:581-583 emits `rtl_compact_<theme>`,
     * never `compact_rtl_<theme>`.
     */
    private function canonicalFilename(string $cssHeader): string
    {
        $name = $this->basename($cssHeader);

        if (str_starts_with($name, self::RTL_PREFIX)) {
            $name = substr($name, strlen(self::RTL_PREFIX));
        }

        if (str_starts_with($name, self::COMPACT_PREFIX)) {
            $name = substr($name, strlen(self::COMPACT_PREFIX));
        }

        return $name;
    }

    /**
     * The lower-cased filename, with any query string, fragment and directory removed.
     *
     * Both separators are handled because a Windows development host can produce
     * backslashes in a path that was built by concatenation.
     */
    private function basename(string $cssHeader): string
    {
        $path = trim($cssHeader);

        $fragment = strpos($path, '#');
        if ($fragment !== false) {
            $path = substr($path, 0, $fragment);
        }

        $query = strpos($path, '?');
        if ($query !== false) {
            $path = substr($path, 0, $query);
        }

        $path = str_replace('\\', '/', $path);

        $separator = strrpos($path, '/');
        if ($separator !== false) {
            $path = substr($path, $separator + 1);
        }

        return strtolower(trim($path));
    }
}
