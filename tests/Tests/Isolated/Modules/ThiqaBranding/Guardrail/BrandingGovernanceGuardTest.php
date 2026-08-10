<?php

/**
 * Repository-level guardrails for locked Thiqa branding invariants.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Guardrail;

use OpenEMR\Common\Session\SessionUtil;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BrandingGovernanceGuardTest extends TestCase
{
    private const EXPECTED_SESSION_IDENTITIES = [
        'CORE_SESSION_ID' => 'OpenEMR',
        'OAUTH_SESSION_ID' => 'authserverOpenEMR',
        'API_SESSION_ID' => 'apiOpenEMR',
        'PORTAL_SESSION_ID' => 'PortalOpenEMR',
        'SETUP_SESSION_ID' => 'setupOpenEMR',
    ];

    private const REQUIRED_THEME_ENTRIES = [
        'style_light',
        'style_dark',
        'compact_style_light',
        'compact_style_dark',
        'rtl_style_light',
        'rtl_style_dark',
        'rtl_compact_style_light',
        'rtl_compact_style_dark',
    ];

    /**
     * The four inherited themes locked Q77 excludes from the Saudi product.
     *
     * Held separately from FORBIDDEN_THEME_ENTRIES because that list is entry NAMES (a
     * build-config concern) while this one is matched against compiled FILENAMES in the
     * deployed directory (what Q77 actually constrains).
     *
     * @var list<string>
     */
    private const FORBIDDEN_THEME_NAMES = ['solar', 'manila', 'cobalt_blue', 'forest_green'];

    private const FORBIDDEN_THEME_ENTRIES = [
        'style_solar',
        'style_manila',
        'style_cobalt_blue',
        'style_forest_green',
        'compact_style_solar',
        'compact_style_manila',
        'compact_style_cobalt_blue',
        'compact_style_forest_green',
        'rtl_style_solar',
        'rtl_style_manila',
        'rtl_style_cobalt_blue',
        'rtl_style_forest_green',
        'rtl_compact_style_solar',
        'rtl_compact_style_manila',
        'rtl_compact_style_cobalt_blue',
        'rtl_compact_style_forest_green',
    ];

    /**
     * Locked Q12: "Public `?site=` tenant selection is prohibited."
     *
     * The Tier 2 token endpoint is an $ignoreAuth entry point, and interface/globals.php
     * gives $_GET['site'] precedence over the session on every such page. It must therefore
     * drop the parameter BEFORE bootstrap, or it becomes both a tenant selector and — via
     * globals.php's clearSession()+redirect on a site mismatch — a cross-origin logout
     * vector, since a stylesheet URL is a natural <link> embed target.
     *
     * Asserted on source order rather than behaviour because the endpoint cannot be
     * exercised without a full application bootstrap, which the isolated suite has no
     * database for. See docs/RebrandingBugs.md RB-06.
     */
    public function testTokenEndpointRefusesASiteParameterBeforeBootstrap(): void
    {
        $source = self::tokenEndpointSource();

        $guardAt = strpos($source, "filter_input(INPUT_GET, 'site') !== null");
        $bootstrapAt = strpos($source, "require_once __DIR__ . '/../../../../globals.php';");

        self::assertNotFalse($guardAt, 'The token endpoint must reject a `site` query parameter (locked Q12).');
        self::assertNotFalse($bootstrapAt, 'The token endpoint must bootstrap through interface/globals.php.');
        self::assertLessThan(
            $bootstrapAt,
            $guardAt,
            'The `site` guard must run BEFORE globals.php, or globals.php has already resolved the '
            . 'tenant from the query parameter and the guard is worthless.',
        );
        self::assertStringContainsString(
            'http_response_code(400)',
            $source,
            'A `site` parameter must be refused outright, not silently ignored.',
        );
    }

    /**
     * An anonymous stylesheet fetch must not be handed a session cookie.
     *
     * ini_set('session.use_cookies', '0') does not achieve this — SessionConfiguration
     * passes 'use_cookies' => true directly to session_start(), and per-call options beat
     * ini settings — so the endpoint suppresses the response header instead, and only when
     * the request arrived without a session. See docs/RebrandingBugs.md RB-06.
     */
    public function testTokenEndpointSuppressesSessionCookiesForAnonymousRequests(): void
    {
        $source = self::tokenEndpointSource();

        self::assertStringContainsString(
            "header_remove('Set-Cookie');",
            $source,
            'The token endpoint must not hand a session cookie to an anonymous fetch.',
        );
        self::assertStringContainsString(
            '$arrivedWithSession',
            $source,
            'Cookie suppression must be conditional on the request having arrived without a session, '
            . 'so a real authenticated request is left untouched.',
        );
    }

    private static function tokenEndpointSource(): string
    {
        $path = dirname(__DIR__, 6)
            . '/interface/modules/custom_modules/oe-module-thiqa-branding/public/branding-tokens.php';

        $contents = file_get_contents($path);
        self::assertNotFalse($contents, 'branding-tokens.php must be readable.');

        return $contents;
    }

    /**
     * The dark-variant marks LogoOverrideListener resolves must actually be installed.
     *
     * Extension point E3 is wired to `<module>/public/logos/dark/<slot>/logo.*`. That
     * directory did not exist, so `firstExistingAsset()` returned null for every slot and
     * the listener was a permanent no-op — the Saudi Dark theme rendered light-optimised
     * marks (a navy wordmark on a #0B1220 surface). The failure was silent because
     * declining is the listener's correct behaviour when it has nothing to offer.
     * Recorded as docs/RebrandingBugs.md RB-10.
     *
     * Asserted on presence rather than behaviour: the listener's own logic is covered by
     * LogoOverrideListenerTest, and what regressed here was the *assets*, not the code.
     *
     * @param non-empty-string $slot
     */
    #[DataProvider('darkVariantSlotProvider')]
    public function testDarkVariantMarksAreInstalled(string $slot): void
    {
        $path = dirname(__DIR__, 6)
            . '/interface/modules/custom_modules/oe-module-thiqa-branding/public/logos/dark/'
            . $slot . '/logo.svg';

        self::assertFileExists(
            $path,
            'Dark-variant mark missing for slot "' . $slot . '". LogoOverrideListener silently '
            . 'declines when the asset is absent, so the dark theme falls back to the light mark. '
            . 'Reinstall with: php tools/branding/install-assets.php --site=<site>',
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function darkVariantSlotProvider(): array
    {
        return [
            'navbar symbol' => ['core/menu/primary'],
            'login wordmark' => ['core/login/primary'],
            'login secondary' => ['core/login/secondary'],
        ];
    }

    public function testMachineFacingSessionIdentitiesRemainByteExact(): void
    {
        $reflection = new \ReflectionClass(SessionUtil::class);

        foreach (self::EXPECTED_SESSION_IDENTITIES as $name => $expected) {
            self::assertSame($expected, $reflection->getConstant($name), $name . ' must remain byte-exact.');
        }
    }

    #[DataProvider('requiredThemeEntryProvider')]
    public function testSaudiBuildRetainsEveryApprovedThemeEntry(string $entry): void
    {
        self::assertMatchesRegularExpression(
            '/^\s*' . preg_quote($entry, '/') . '\s*:/m',
            self::webpackThemeMap(),
        );
    }

    #[DataProvider('forbiddenThemeEntryProvider')]
    public function testSaudiBuildExcludesEveryForbiddenThemeEntry(string $entry): void
    {
        self::assertDoesNotMatchRegularExpression(
            '/^\s*' . preg_quote($entry, '/') . '\s*:/m',
            self::webpackThemeMap(),
        );
    }

    /**
     * Locked Q77 constrains the DEPLOYED directory, not the entry map.
     *
     * Its words are: the surplus themes' "user-selectable CSS artifacts MUST NOT **exist**
     * in the deployed `public/themes/` directory". Pruning `webpack.themes.js` is how that
     * is achieved, but the two are not the same claim — webpack's output cleaning applies
     * to the build workspace, and the documented deploy step copies without deleting, so a
     * stylesheet built before the entry map was pruned can survive at the destination
     * indefinitely. `interface/globals.php:476` gates only on `file_exists()`, so a stale
     * `globals`/`user_settings` value would then resolve it. See docs/RebrandingBugs.md
     * RB-07 and RB-08.
     *
     * This asserts the thing Q77 actually says. It is check **V-04**'s first half, which
     * was previously only ever hand-run.
     *
     * `public/themes/` is gitignored build output, so its absence is not a failure — a
     * fresh checkout that has not run the theme build has nothing to constrain.
     */
    public function testDeployedThemeDirectoryContainsNoForbiddenStylesheet(): void
    {
        $themeDirectory = dirname(__DIR__, 6) . '/public/themes';

        if (!is_dir($themeDirectory)) {
            self::markTestSkipped('public/themes/ is build output and has not been generated here.');
        }

        $files = scandir($themeDirectory);
        self::assertNotFalse($files, 'public/themes/ must be readable.');

        $forbidden = [];
        foreach ($files as $file) {
            if (!str_ends_with($file, '.css')) {
                continue;
            }

            foreach (self::FORBIDDEN_THEME_NAMES as $name) {
                if (str_contains($file, $name)) {
                    $forbidden[] = $file;
                    break;
                }
            }
        }

        self::assertSame(
            [],
            $forbidden,
            'Locked Q77: these stylesheets must not exist in the deployed public/themes/ directory. '
            . 'Webpack no longer builds them, so their presence means a stale artefact survived a deploy '
            . 'that copied without purging — see docs/branding/runbook.md section 4.',
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function requiredThemeEntryProvider(): array
    {
        return self::provider(self::REQUIRED_THEME_ENTRIES);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function forbiddenThemeEntryProvider(): array
    {
        return self::provider(self::FORBIDDEN_THEME_ENTRIES);
    }

    /**
     * @param list<string> $entries
     * @return array<string, array{string}>
     */
    private static function provider(array $entries): array
    {
        $cases = [];
        foreach ($entries as $entry) {
            $cases[$entry] = [$entry];
        }

        return $cases;
    }

    private static function webpackThemeMap(): string
    {
        $contents = file_get_contents(dirname(__DIR__, 6) . '/webpack.themes.js');
        self::assertNotFalse($contents, 'webpack.themes.js must be readable.');

        return $contents;
    }
}
