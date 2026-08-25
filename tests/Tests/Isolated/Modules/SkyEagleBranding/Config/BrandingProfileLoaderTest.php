<?php

/**
 * Isolated tests for the declarative branding profile and its loader.
 *
 * Two things are under test and they are different in kind. The loader is a parsing
 * boundary, so its refusals matter more than its successes: a profile that quietly
 * accepts an unknown key, an over-long value or a materialiser-owned key would report
 * success while leaving the product still branded OpenEMR. And the shipped profile
 * itself is a contract with docs/branding-production/14-string-replacement-map.md, so
 * the values that have caused defects before — the RTL stylesheet names, the two globals
 * whose blank form leaks open-emr.org content, the login layout's stored form — are
 * asserted literally rather than left to review.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config;

use InvalidArgumentException;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingProfile;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingProfileLoader;
use OpenEMR\Modules\SkyEagleBranding\Config\SuppressionGlobalKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BrandingProfileLoaderTest extends TestCase
{
    use ModuleAutoloadTrait;

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    // -----------------------------------------------------------------------------
    // The shipped profile
    // -----------------------------------------------------------------------------

    public function testShippedProfileLoads(): void
    {
        $profile = $this->shippedProfile();

        self::assertSame('skyeagle-product-default', $profile->name);
        self::assertSame('SkyEagle', $profile->productName);
        self::assertGreaterThan(0, count($profile));
    }

    public function testShippedProfileNamesOnlyKnownKeys(): void
    {
        foreach ($this->shippedProfile()->globalNames() as $name) {
            self::assertTrue(
                BrandingGlobalKey::tryFrom($name) !== null || SuppressionGlobalKey::tryFrom($name) !== null,
                'Profile names a global outside both closed enums: ' . $name,
            );
        }
    }

    public function testShippedProfileFitsTheGlobalsColumn(): void
    {
        foreach ($this->shippedProfile()->entries() as $entry) {
            self::assertLessThanOrEqual(
                BrandingProfileLoader::MAX_VALUE_LENGTH,
                mb_strlen($entry->value, 'UTF-8'),
                'Value for ' . $entry->globalName() . ' would be truncated by globals.gl_value.',
            );
        }
    }

    public function testShippedProfileGivesEveryRowAnInventoryId(): void
    {
        foreach ($this->shippedProfile()->entries() as $entry) {
            self::assertMatchesRegularExpression(
                '/^(BRAND-\d{3}|saas_branding_)/',
                $entry->inventoryId,
                'Row for ' . $entry->globalName() . ' has no traceable inventory id.',
            );
        }
    }

    /**
     * The values Part 1 fixes and that a careless edit has broken before.
     */
    #[DataProvider('lockedValueProvider')]
    public function testShippedProfileCarriesTheLockedValue(string $key, string $expected): void
    {
        $entry = $this->shippedProfile()->entryFor($this->key($key));

        self::assertNotNull($entry, 'Profile does not name ' . $key . '.');
        self::assertSame($expected, $entry->value);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function lockedValueProvider(): array
    {
        return [
            'product name' => ['openemr_name', 'SkyEagle'],
            'tagline' => ['login_tagline_text', 'Clinical confidence, connected care.'],
            'product root url' => ['main_menu_logo_link', 'https://skyeagle.uk/'],
            'support url over https' => ['online_support_link', 'https://skyeagle.uk/support'],
            'documentation url' => ['user_manual_link', 'https://skyeagle.uk/docs'],
            'logo tooltip is never blank' => ['main_menu_logo_title', 'SkyEagle Health Information System'],
            'theme stylesheet, never rtl-prefixed' => ['css_header', 'style_light.css'],
            'portal stylesheet, never rtl-prefixed' => ['portal_css_header', 'style_light.css'],
            'login layout is stored as a twig path' => [
                'login_page_layout',
                'login/layouts/vertical_band.html.twig',
            ],
            'arabic product name' => ['saas_branding_product_name_ar', 'سكاي إيجل'],
        ];
    }

    /**
     * WS-G: the three HIDE items, all of them plain profile rows.
     */
    #[DataProvider('hiddenGlobalProvider')]
    public function testShippedProfileHidesTheInheritedSurface(string $key): void
    {
        $entry = $this->shippedProfile()->entryFor($this->key($key));

        self::assertNotNull($entry, 'Profile does not name ' . $key . '.');
        self::assertSame('0', $entry->value);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hiddenGlobalProvider(): array
    {
        return [
            'BRAND-060 review link' => ['display_review_link'],
            'BRAND-061 donations link' => ['display_donations_link'],
            'BRAND-105 in-app help' => ['enable_help'],
        ];
    }

    /**
     * Never an `rtl_` prefix, in any language: OpenEMR derives both the RTL and the
     * compact variants from the stored value (decision CR-3).
     */
    #[DataProvider('stylesheetGlobalProvider')]
    public function testStylesheetValuesAreNeverRtlPrefixed(string $key): void
    {
        $entry = $this->shippedProfile()->entryFor($this->key($key));

        self::assertNotNull($entry);
        self::assertStringNotContainsString('rtl', $entry->value);
        self::assertStringNotContainsString('rtl', (string) $entry->arabicValue);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function stylesheetGlobalProvider(): array
    {
        return [
            'main theme' => ['css_header'],
            'portal theme' => ['portal_css_header'],
        ];
    }

    /**
     * Portal enablement is provisioning, not branding (CR-4); the facility name and the
     * support phone are tenant data. None may be written by a product-level profile.
     */
    #[DataProvider('outOfScopeGlobalProvider')]
    public function testShippedProfileLeavesOutOfScopeGlobalsAlone(string $key): void
    {
        self::assertNotContains($key, $this->shippedProfile()->globalNames());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function outOfScopeGlobalProvider(): array
    {
        return [
            'portal enablement' => ['portal_onsite_two_enable'],
            'portal address' => ['portal_onsite_two_address'],
            'support phone' => ['support_phone_number'],
            'revision counter' => ['saas_branding_revision'],
            'materialisation timestamp' => ['saas_branding_materialised_at'],
        ];
    }

    // -----------------------------------------------------------------------------
    // What the loader refuses
    // -----------------------------------------------------------------------------

    #[DataProvider('rejectedDocumentProvider')]
    public function testLoaderRefuses(string $json, string $expectedFragment): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote($expectedFragment, '/') . '/');

        (new BrandingProfileLoader())->parse($json, 'test-profile');
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rejectedDocumentProvider(): array
    {
        $row = static fn (string $key, string $value): string => self::document([
            ['inventory_id' => 'BRAND-001', 'key' => $key, 'value' => $value],
        ]);

        return [
            'not json' => ['{oops', 'not valid JSON'],
            'not an object' => ['[]', 'missing the "profile" field'],
            'no globals array' => ['{"profile":"p","product_name":"Thiqa","source_document":"d"}', 'globals'],
            'empty globals array' => [self::document([]), 'non-empty "globals" array'],
            'unknown key' => [
                $row('openemr_nane', 'SkyEagle'),
                'is not a case of BrandingGlobalKey or SuppressionGlobalKey',
            ],
            'materialiser-owned revision' => [
                $row('saas_branding_revision', '7'),
                'the materialiser owns',
            ],
            'materialiser-owned overlay' => [
                $row('saas_branding_tokens_light', '{}'),
                'the materialiser owns',
            ],
            'materialiser-owned timestamp' => [
                $row('saas_branding_materialised_at', '2026-08-10T00:00:00+00:00'),
                'the materialiser owns',
            ],
            'value too long' => [
                $row('login_tagline_text', str_repeat('x', 256)),
                'globals.gl_value holds at most 255',
            ],
            'flag that is not 0 or 1' => [
                $row('show_tagline_on_login', 'yes'),
                'must be "0" or "1"',
            ],
            'integer that is not digits' => [
                $row('portal_primary_menu_logo_height', '30px'),
                'must be a whole number written in digits',
            ],
            'multi-line text' => [
                $row('openemr_name', "Thiqa\nHIS"),
                'must be a single line',
            ],
            'suppression key outside its permitted set' => [
                $row('enable_help', '3'),
                'must be one of 0, 1, 2',
            ],
            'value is not a string' => [
                self::document([['inventory_id' => 'BRAND-001', 'key' => 'openemr_name', 'value' => 1]]),
                'must give "value" as a string',
            ],
            'row without an inventory id' => [
                self::document([['inventory_id' => '', 'key' => 'openemr_name', 'value' => 'SkyEagle']]),
                'needs an "inventory_id"',
            ],
            'row missing a value' => [
                self::document([['inventory_id' => 'BRAND-001', 'key' => 'openemr_name']]),
                'is missing "value"',
            ],
            'duplicate key' => [
                self::document([
                    ['inventory_id' => 'BRAND-001', 'key' => 'openemr_name', 'value' => 'SkyEagle'],
                    ['inventory_id' => 'BRAND-001', 'key' => 'openemr_name', 'value' => 'Thiqah'],
                ]),
                'more than once',
            ],
            'map row that is not a positive number' => [
                self::document([
                    [
                        'inventory_id' => 'BRAND-001',
                        'key' => 'openemr_name',
                        'value' => 'SkyEagle',
                        'map_row' => 0,
                    ],
                ]),
                'positive whole number',
            ],
        ];
    }

    public function testLoaderAcceptsExactlyTwoHundredAndFiftyFiveCharacters(): void
    {
        $value = str_repeat('x', BrandingProfileLoader::MAX_VALUE_LENGTH);
        $profile = (new BrandingProfileLoader())->parse(
            self::document([['inventory_id' => 'BRAND-042', 'key' => 'login_tagline_text', 'value' => $value]]),
            'test-profile',
        );

        self::assertSame($value, $profile->valueFor(BrandingGlobalKey::LoginTaglineText));
    }

    /**
     * Arabic is 2 bytes per character in UTF-8, so a byte-counting bound would reject a
     * value the column holds perfectly well.
     */
    public function testLoaderCountsCharactersNotBytes(): void
    {
        $value = str_repeat('ث', 200);
        self::assertGreaterThan(BrandingProfileLoader::MAX_VALUE_LENGTH, strlen($value));

        $profile = (new BrandingProfileLoader())->parse(
            self::document([
                ['inventory_id' => 'BRAND-001', 'key' => 'saas_branding_product_name_ar', 'value' => $value],
            ]),
            'test-profile',
        );

        self::assertSame($value, $profile->valueFor(BrandingGlobalKey::ProductNameArabic));
    }

    public function testLoaderRefusesAnUnreadablePath(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BrandingProfileLoader())->load(__DIR__ . '/no-such-profile.json');
    }

    public function testEntryCarriesProvenance(): void
    {
        $profile = (new BrandingProfileLoader())->parse(
            self::document([
                [
                    'inventory_id' => 'BRAND-001',
                    'key' => 'openemr_name',
                    'value' => 'SkyEagle',
                    'map_row' => 1,
                    'value_ar' => 'سكاي إيجل',
                    'note' => 'flows to 20 consumers',
                ],
            ]),
            'test-profile',
        );

        $entry = $profile->entryFor(BrandingGlobalKey::OpenemrName);

        self::assertNotNull($entry);
        self::assertSame('openemr_name', $entry->globalName());
        self::assertSame('BRAND-001 (map row 1)', $entry->provenance());
        self::assertSame('سكاي إيجل', $entry->arabicValue);
        self::assertSame('flows to 20 consumers', $entry->note);
        self::assertTrue($entry->isBrandingGlobal());
    }

    public function testSuppressionEntryIsNotABrandingGlobal(): void
    {
        $profile = (new BrandingProfileLoader())->parse(
            self::document([['inventory_id' => 'BRAND-105', 'key' => 'enable_help', 'value' => '0']]),
            'test-profile',
        );

        $entry = $profile->entryFor(SuppressionGlobalKey::EnableHelp);

        self::assertNotNull($entry);
        self::assertFalse($entry->isBrandingGlobal());
        self::assertSame('BRAND-105', $entry->provenance());
        self::assertSame('In-app help modal', $entry->label());
    }

    public function testProfileOrderFollowsTheFile(): void
    {
        $profile = (new BrandingProfileLoader())->parse(
            self::document([
                ['inventory_id' => 'BRAND-042', 'key' => 'login_tagline_text', 'value' => 'Second'],
                ['inventory_id' => 'BRAND-001', 'key' => 'openemr_name', 'value' => 'First'],
            ]),
            'test-profile',
        );

        self::assertSame(['login_tagline_text', 'openemr_name'], $profile->globalNames());
    }

    // -----------------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------------

    private function shippedProfile(): BrandingProfile
    {
        return (new BrandingProfileLoader())->load(BrandingProfileLoader::defaultProfilePath());
    }

    private function key(string $name): BrandingGlobalKey|SuppressionGlobalKey
    {
        $key = BrandingGlobalKey::tryFrom($name) ?? SuppressionGlobalKey::tryFrom($name);
        if ($key === null) {
            self::fail('Test names a global outside both closed enums: ' . $name);
        }

        return $key;
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private static function document(array $rows): string
    {
        return json_encode(
            [
                'profile' => 'test',
                'product_name' => 'SkyEagle',
                'source_document' => 'test',
                'globals' => $rows,
            ],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
        );
    }
}
