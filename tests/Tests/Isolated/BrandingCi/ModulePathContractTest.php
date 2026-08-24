<?php

/**
 * Regression contract: one module directory name, everywhere it is written down.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use OpenEMR\Modules\ThiqaBranding\Bootstrap;
use OpenEMR\Modules\ThiqaBranding\Config\ModulePaths;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Modules/ThiqaBranding/Materialisation/materialisation_autoloader.php';

/**
 * Finding S1-P2-12: the module directory name was hand-typed in five places, and each
 * duplicate fails **silently** — a wrong logo web path is a 404 on one image, a wrong token
 * path is a `<link>` to nothing. Nothing goes red, so a rename that misses one leaves a
 * surface quietly broken.
 *
 * `ModulePaths` now derives every PHP-side path from one constant, which removes four of the
 * five. The remaining consumers cannot share a PHP constant at all:
 *
 *  - `tools/branding/install-assets.php` is a build-time tool under the `OpenEMR\Branding\`
 *    autoload prefix, and the module's own prefix is not in the root autoloader;
 *  - `webpack.themes.js` is JavaScript;
 *  - `.gitignore` is neither.
 *
 * A boundary a constant cannot cross is exactly where a test belongs, so those are asserted
 * here against the same single source of truth.
 */
#[Group('isolated')]
final class ModulePathContractTest extends TestCase
{
    public function testEveryPhpPathIsDerivedFromTheOneDirectoryName(): void
    {
        $directory = ModulePaths::DIRECTORY_NAME;

        self::assertSame('interface/modules/custom_modules/' . $directory, ModulePaths::APPLICATION_RELATIVE_ROOT);
        self::assertSame(ModulePaths::APPLICATION_RELATIVE_ROOT . '/public/branding-tokens.php', ModulePaths::TOKEN_STYLESHEET);
        self::assertSame('/' . ModulePaths::APPLICATION_RELATIVE_ROOT . '/public/logos/dark', ModulePaths::DARK_LOGO_WEB_PATH);

        // The two public constants other code and documentation already reference by name.
        self::assertSame(ModulePaths::TOKEN_STYLESHEET, BrandingService::TOKEN_STYLESHEET_RELATIVE_PATH);
        self::assertSame(ModulePaths::TOKEN_DOCUMENT, BrandingService::TOKEN_DOCUMENT_RELATIVE_PATH);
    }

    /**
     * The Twig namespace and the directory name are the same string on purpose: templates
     * resolve as `@<namespace>/…` and the namespace has to stay recognisable as the module it
     * serves. Deriving it means a rename cannot move one without the other.
     */
    public function testTheTwigNamespaceIsTheDirectoryName(): void
    {
        self::assertSame(ModulePaths::DIRECTORY_NAME, Bootstrap::TWIG_NAMESPACE);
    }

    /** The module really is installed where these constants claim. */
    public function testTheDerivedRootIsWhereTheModuleActuallyLives(): void
    {
        self::assertDirectoryExists($this->path(ModulePaths::APPLICATION_RELATIVE_ROOT));
        self::assertFileExists($this->path(ModulePaths::TOKEN_STYLESHEET));
        self::assertFileExists($this->path(ModulePaths::TOKEN_DOCUMENT));
    }

    /**
     * The build-time asset installer, which cannot autoload the module's namespace.
     */
    public function testTheAssetInstallerUsesTheSameDarkLogoRoot(): void
    {
        self::assertStringContainsString(
            "'" . ModulePaths::DARK_LOGO_APPLICATION_RELATIVE . "'",
            $this->read('tools/branding/install-assets.php'),
            'install-assets.php names a dark-logo root that no longer matches ModulePaths.',
        );
    }

    /**
     * The one non-PHP consumer, and the most expensive to get wrong quietly: after a rename
     * `.gitignore` simply stops matching, and the next commit sweeps in every tenant's
     * materialised branding output as if it were source.
     *
     * `webpack.themes.js` is deliberately **not** asserted here. It was listed among the
     * module's rename surfaces, but reading it shows it references the SCSS source tree
     * (`interface/themes/thiqa/`, via `oe-styles/style_thiqa_*.scss` entries) and never the
     * module directory — so there is nothing here for it to agree with.
     */
    public function testTheIgnoreRuleStillCoversMaterialisedTenantOutput(): void
    {
        self::assertStringContainsString(
            ModulePaths::APPLICATION_RELATIVE_ROOT . '/' . ModulePaths::TENANT_BRANDING_SUBPATH . '/',
            $this->read('.gitignore'),
            '.gitignore no longer ignores the module\'s materialised tenant output.',
        );
    }

    private function read(string $relative): string
    {
        $contents = file_get_contents($this->path($relative));
        self::assertIsString($contents);

        return str_replace("\r\n", "\n", $contents);
    }

    private function path(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . $relative;
    }
}
