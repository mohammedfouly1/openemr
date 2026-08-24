<?php

/**
 * Rebase guard for the mandatory branding core-file string patches.
 *
 * These are the only branding strings that cannot be delivered through
 * configuration, assets or module events, so each one is a literal edit to a
 * tracked upstream file (docs/rebranding.md 15.1, docs/RebrandingPlan.md 5.4).
 * A rebase or merge that silently restores the upstream literal would ship
 * upstream branding — and, for the product registration endpoint, would restore
 * an outbound phone-home. This test reads the files as text so it can assert
 * on the literals themselves without booting the application.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCoreStrings;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MandatoryCoreStringPatchesIsolatedTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';

    private const ZEND_INSTALLER_VIEW =
        'interface/modules/zend_modules/module/Installer/view/installer/installer/index.phtml';

    /**
     * @param non-empty-list<string> $mustContain
     * @param list<string>           $mustNotContain
     */
    #[DataProvider('mandatoryPatchProvider')]
    public function testMandatoryPatchIsPresent(string $relativePath, array $mustContain, array $mustNotContain): void
    {
        $contents = $this->readProjectFile($relativePath);

        foreach ($mustContain as $needle) {
            $this->assertStringContainsString(
                $needle,
                $contents,
                sprintf('%s lost the branding patch: expected to find %s', $relativePath, var_export($needle, true))
            );
        }

        foreach ($mustNotContain as $needle) {
            $this->assertStringNotContainsString(
                $needle,
                $contents,
                sprintf('%s regressed to the upstream literal %s', $relativePath, var_export($needle, true))
            );
        }
    }

    /**
     * Product registration is a disabled/no-op feature (decision D-10): the preference is
     * recorded locally and no endpoint is contacted, so none has to exist or be operated.
     */
    public function testProductRegistrationPerformsNoOutboundRequest(): void
    {
        $contents = $this->readProjectFile('src/Services/ProductRegistrationService.php');

        $this->assertDoesNotMatchRegularExpression(
            '/\bcurl_[a-z_]+\s*\(/',
            $contents,
            'ProductRegistrationService must not issue cURL requests; registration is disabled.'
        );
        // The only URLs left in the file must be the docblock @link / @license lines.
        foreach (preg_split('/\R/', $contents) ?: [] as $lineNumber => $line) {
            if (!str_contains($line, 'http://') && !str_contains($line, 'https://')) {
                continue;
            }
            $this->assertMatchesRegularExpression(
                '/^\s*\*/',
                $line,
                sprintf(
                    'ProductRegistrationService line %d carries a URL outside the docblock: %s',
                    $lineNumber + 1,
                    trim($line)
                )
            );
        }
    }

    /**
     * Census of every literal "Thiqa" occurrence in the core-patched files (decision D-3,
     * docs/RebrandingPlan.md §6.5/§8.3, still open at time of writing). Two purposes:
     *
     *  - if D-3 concludes with a different approved name, this provider is the complete,
     *    exhaustive checklist of literals to revisit — no grep-and-hope required;
     *  - if a future change silently adds a new hardcoded "Thiqa" literal to one of these
     *    files without updating the inventory, the count assertion fails immediately instead
     *    of the drift going unnoticed until D-3 closes.
     *
     * `ProductRegistrationService.php` is deliberately included at an expected count of 0:
     * it is a core-patched file (BRAND-113) that must stay free of the literal, since its
     * fix was to remove a phone-home endpoint, not to rename one.
     */
    #[DataProvider('hardcodedProductNameInventoryProvider')]
    public function testHardcodedProductNameLiteralCountMatchesInventory(string $relativePath, int $expectedCount): void
    {
        $contents = $this->readProjectFile($relativePath);
        $actualCount = substr_count($contents, 'Thiqa');

        $this->assertSame(
            $expectedCount,
            $actualCount,
            sprintf(
                '%s: expected %d literal "Thiqa" occurrence(s) but found %d. If this is a deliberate new '
                    . 'hardcoded reference, update this test\'s inventory (it is the D-3 checklist); do not '
                    . 'let the two drift apart.',
                $relativePath,
                $expectedCount,
                $actualCount
            )
        );
    }

    /**
     * @return array<string, array{string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hardcodedProductNameInventoryProvider(): array
    {
        return [
            'admin.php' => ['admin.php', 2],
            'interface/globals.php' => ['interface/globals.php', 4],
            'zend installer view' => [self::ZEND_INSTALLER_VIEW, 1],
            'FHIR capability statement' => ['src/RestControllers/FHIR/FhirMetaDataRestController.php', 1],
            'OAuth2 API disabled message' => ['src/RestControllers/Subscriber/OAuth2AuthorizationListener.php', 1],
            'product registration service (must stay clean)' => ['src/Services/ProductRegistrationService.php', 0],
            'error page 400 title' => ['templates/error/400.html.twig', 1],
            'error page 404 title' => ['templates/error/404.html.twig', 1],
            'error page 400 JSON location' => ['templates/error/400.json.twig', 1],
            'error page 404 JSON location' => ['templates/error/404.json.twig', 1],
            'general HTTP error page title' => ['templates/error/general_http_error.html.twig', 1],
            // The six former compound-string consumers below are asserted at ZERO deliberately.
            //
            // BRAND-127/128/129 carry the action SET-TRANSLATION in docs/rebranding.md §16.2
            // (Trk = NO), which means the rebrand is catalogue data, not a source edit. An
            // earlier attempt edited the literal inside xl()/xlt() instead. Because the
            // English source string IS the catalogue key (library/translation.inc.php:39-77
            // matches lang_constants.constant_name exactly), that renamed the key and
            // orphaned 59 existing translations across the shipped locales, Arabic included
            // — measured and recorded as docs/RebrandingBugs.md RB-01.
            //
            // These rows are therefore a regression guard, not an omission: a "Thiqa"
            // literal reappearing in any of these files would hardcode tenant identity.
            // OAuth now composes tenant applicationTitle with independently translated
            // action phrases; the three Zend views retain active catalogue overrides.
            'oauth2 login (tenant title composition: must stay clean)' => [
                'templates/oauth2/oauth2-login.html.twig', 0,
            ],
            'oauth2 patient-select (tenant title composition: must stay clean)' => [
                'templates/oauth2/patient-select.html.twig', 0,
            ],
            'oauth2 scope-authorize (tenant title composition: must stay clean)' => [
                'templates/oauth2/scope-authorize.html.twig', 0,
            ],
            'zend Application layout (SET-TRANSLATION: must stay clean)' => [
                'interface/modules/zend_modules/module/Application/view/layout/layout.phtml', 0,
            ],
            'zend Application sendto layout (SET-TRANSLATION: must stay clean)' => [
                'interface/modules/zend_modules/module/Application/view/layout/sendto.phtml', 0,
            ],
            'zend Documents layout (SET-TRANSLATION: must stay clean)' => [
                'interface/modules/zend_modules/module/Documents/view/layout/layout.phtml', 0,
            ],
        ];
    }

    /**
     * @return array<string, array{string, non-empty-list<string>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function mandatoryPatchProvider(): array
    {
        return [
            'admin.php title and heading (BRAND-005, BRAND-006)' => [
                'admin.php',
                ['<title>Thiqa Site Administration</title>', '<h2>Thiqa Multi-Site Administration</h2>'],
                ['OpenEMR Site Administration', 'OpenEMR Multi Site Administration'],
            ],
            'FHIR capability statement product name (BRAND-087, BRAND-126)' => [
                'src/RestControllers/FHIR/FhirMetaDataRestController.php',
                ["getString('openemr_name')", '$productName . " FHIR API"', 'setName(new FHIRString($productName))'],
                ['new FHIRString("OpenEMR")', '"OpenEMR FHIR API"'],
            ],
            'product registration endpoint removed (BRAND-113)' => [
                'src/Services/ProductRegistrationService.php',
                ['Remote product registration is disabled'],
                ['reg.open-emr.org', 'reg.skyeagle.uk'],
            ],
            'OAuth2 API disabled message (BRAND-134)' => [
                'src/RestControllers/Subscriber/OAuth2AuthorizationListener.php',
                ['"Thiqa Error: API is disabled"'],
                ['"OpenEMR Error: API is disabled"'],
            ],
            'pre-bootstrap fatal messages (BRAND-135, BRAND-136)' => [
                'interface/globals.php',
                [
                    'echo "Thiqa Error: Thiqa is not working since the php openssl module is not installed.";',
                    'echo "Thiqa Error: Thiqa is not working since the openssl aes-256-cbc cipher is not available.";',
                ],
                ['echo "OpenEMR Error :'],
            ],
            'Zend module installer documentation links (BRAND-130)' => [
                self::ZEND_INSTALLER_VIEW,
                ['href="https://skyeagle.uk/docs/installer"', 'Visit additional modules for Thiqa developed'],
                ['open-emr.org/wiki', 'modules for OpenEMR developed'],
            ],
            'error page 400 title (BRAND-101)' => [
                'templates/error/400.html.twig',
                ['"Thiqa 400 Error"|xlt'],
                ['"OpenEMR 400 Error"'],
            ],
            'error page 404 title (BRAND-101)' => [
                'templates/error/404.html.twig',
                ['"Thiqa 404 Error"|xlt'],
                ['"OpenEMR 404 Error"'],
            ],
            'error page 400 JSON location (BRAND-101)' => [
                'templates/error/400.json.twig',
                ['"Thiqa 400 Error"|xl|json_encode'],
                ['"OpenEMR 400 Error"'],
            ],
            'error page 404 JSON location (BRAND-101)' => [
                'templates/error/404.json.twig',
                ['"Thiqa 404 Error"|xl|json_encode'],
                ['"OpenEMR 404 Error"'],
            ],
            'general HTTP error page title (BRAND-101)' => [
                'templates/error/general_http_error.html.twig',
                ['"Thiqa Error"|xlt'],
                ['"OpenEMR Error"'],
            ],
            // BRAND-127/128/129 are NOT listed here. The OAuth views use tenant-provided
            // applicationTitle plus translated action phrases, while the Zend views use
            // unchanged upstream catalogue keys. Asserting a "Thiqa" literal in any of
            // those files would enshrine a tenant-isolation regression. Their guard is the
            // zero-count inventory above; the detailed mechanism is covered separately.
            'setup.php titles and navbar (BRAND-007, BRAND-008)' => [
                'setup.php',
                [
                    '<title>Thiqa Setup Tool</title>',
                    '<a class="navbar-brand" href="#">Thiqa Setup</a>',
                ],
                ['OpenEMR Setup Tool', '>OpenEMR Setup<'],
            ],
            'setup.php body copy and legend (BRAND-009)' => [
                'setup.php',
                [
                    'The initial Thiqa user is',
                    'The initial Thiqa user name and password is the same',
                    'before following below Thiqa link',
                    '>Thiqa Initial User Details<',
                    'To ensure proper functioning of Thiqa you must make sure',
                    'Select a theme for Thiqa...',
                ],
                [
                    'The initial OpenEMR user is',
                    'The initial OpenEMR user name and password is the same',
                    'before following below OpenEMR link',
                    '>OpenEMR Initial User Details<',
                    'To ensure proper functioning of OpenEMR you must make sure',
                    'Select a theme for OpenEMR...',
                ],
            ],
            'sql_patch.php title and banner (BRAND-010)' => [
                'sql_patch.php',
                [
                    "<title>Thiqa <?php echo attr(\$EMRversion)",
                    'font-size:1.8em; text-align:center">Thiqa <?php echo text($EMRversion)',
                    'font-size:1.8em;">Thiqa \',xlt(\'Version\')',
                ],
                [
                    "<title>OpenEMR <?php echo attr(\$EMRversion)",
                    'font-size:1.8em; text-align:center">OpenEMR <?php echo text($EMRversion)',
                    'font-size:1.8em;">OpenEMR \',xlt(\'Version\')',
                ],
            ],
            'sql_upgrade.php neutral translated title and heading (BRAND-011)' => [
                'sql_upgrade.php',
                [
                    'ProductContextTranslation::compose(',
                    "xl('%s Database Upgrade')",
                    "getString('openemr_name')",
                    '<title><?php echo text($databaseUpgradeTitle); ?></title>',
                    '<h2><?php echo text($databaseUpgradeTitle); ?></h2>',
                ],
                [
                    'Thiqa Database Upgrade',
                    'OpenEMR Database Upgrade',
                ],
            ],
            'ippf_upgrade.php title and heading (BRAND-012)' => [
                'ippf_upgrade.php',
                ['<title>Thiqa IPPF Upgrade</title>', '<h2>Thiqa IPPF Upgrade</h2>', 'converts your Thiqa database'],
                ['<title>OpenEMR IPPF Upgrade</title>', '<h2>OpenEMR IPPF Upgrade</h2>', 'converts your OpenEMR database'],
            ],
        ];
    }

    private function readProjectFile(string $relativePath): string
    {
        $path = self::PROJECT_ROOT . '/' . $relativePath;
        $this->assertFileExists($path);

        $contents = file_get_contents($path);
        $this->assertIsString($contents, sprintf('Unable to read %s', $relativePath));

        return $contents;
    }
}
