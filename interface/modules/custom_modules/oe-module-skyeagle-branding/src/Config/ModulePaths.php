<?php

/**
 * The single source of truth for where this module lives.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Config;

/**
 * Finding S1-P2-12: the module's own directory name was hand-typed in five places, and the
 * duplicates fail **silently**. A wrong logo web path yields a 404 on one image; a wrong
 * token-stylesheet path yields a `<link>` to nothing. Neither raises an error, so a rename
 * that misses one leaves a surface quietly broken rather than a build red.
 *
 * Every path below is derived from `DIRECTORY_NAME`, so a rename is one edit. The pattern is
 * the one already used well elsewhere in this codebase — `CliOptions::DEFAULT_FONT_URL_BASE`
 * is a single parameterised source of truth rather than a literal repeated per call site.
 *
 * **Two consumers deliberately stay outside this class**, and both are guarded rather than
 * wired:
 *
 *  - `tools/branding/install-assets.php` is a build-time tool under the `OpenEMR\Branding\`
 *    autoload prefix. The module's own prefix is not in the root autoloader, so the tool
 *    cannot load this class. `ModulePathContractTest` asserts its literal matches instead.
 *  - `.gitignore` is not PHP at all, and the same test pins it — the most expensive one to get
 *    wrong quietly, since after a rename it simply stops matching and the next commit sweeps in
 *    every tenant's materialised branding output as if it were source.
 *
 * `webpack.themes.js` is **not** in that list, despite appearing on the module's inherited
 * rename-surface inventory. Reading it shows it references the SCSS source tree
 * (`oe-styles/style_thiqa_*.scss` → `interface/themes/thiqa/`) and never this directory, so there
 * is nothing here for it to agree with. *(Corrected 2026-08-24 after Scan-3E caught this docblock
 * claiming the test pinned webpack when it deliberately does not.)*
 *
 * A constant cannot cross those boundaries; a test can, so the coupling is enforced where it
 * can actually be enforced instead of being left to memory.
 */
final class ModulePaths
{
    /**
     * The module's directory name, which is also its Twig namespace.
     *
     * These are the same string by design, not by coincidence: `TwigOverrideListener`
     * registers `addPath($dir, <namespace>)` and templates resolve as
     * `@oe-module-skyeagle-branding/…`, so the namespace has to remain recognisable as the
     * module it serves.
     */
    public const DIRECTORY_NAME = 'oe-module-skyeagle-branding';

    /** Where the module sits relative to the application root, with no leading slash. */
    public const APPLICATION_RELATIVE_ROOT = 'interface/modules/custom_modules/' . self::DIRECTORY_NAME;

    /** The Tier 1 product token document, relative to the application root. */
    public const TOKEN_DOCUMENT = 'brand/tokens/thiqa-tokens.json';

    /** The Tier 2 stylesheet endpoint, relative to the web root (plan §3.8.1). */
    public const TOKEN_STYLESHEET = self::APPLICATION_RELATIVE_ROOT . '/public/branding-tokens.php';

    /** Dark-variant logo directory, relative to the module root. */
    public const DARK_LOGO_SUBPATH = 'public/logos/dark';

    /** Dark-variant logo directory, relative to the application root. */
    public const DARK_LOGO_APPLICATION_RELATIVE = self::APPLICATION_RELATIVE_ROOT . '/' . self::DARK_LOGO_SUBPATH;

    /** Dark-variant logo directory as a web path, with the leading slash a browser needs. */
    public const DARK_LOGO_WEB_PATH = '/' . self::DARK_LOGO_APPLICATION_RELATIVE;

    /** Materialised per-tenant branding root, relative to the module root. */
    public const TENANT_BRANDING_SUBPATH = 'public/branding';

    /** The declarative Tier 1 profile the apply-profile command reads, relative to the module root. */
    public const PROFILE_SUBPATH = 'config/branding-profile.json';

    /** Twig template root, relative to the module root. */
    public const TEMPLATE_SUBPATH = 'templates';
}
