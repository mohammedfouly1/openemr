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

    /** @return array<string, array{string}> */
    public static function requiredThemeEntryProvider(): array
    {
        return self::provider(self::REQUIRED_THEME_ENTRIES);
    }

    /** @return array<string, array{string}> */
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
