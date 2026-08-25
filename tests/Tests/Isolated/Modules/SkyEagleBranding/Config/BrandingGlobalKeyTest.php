<?php

/**
 * Isolated tests for the branding globals registry enum.
 *
 * The registry is a contract, not merely a list: the plan fixes its membership at 33
 * inherited globals plus 7 layer-owned ones, and the string replacement map fixes each
 * key's spelling. A drift here is a drift in the contract, so the membership and the
 * exact key strings are asserted rather than assumed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config;

use LogicException;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingValueType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BrandingGlobalKeyTest extends TestCase
{
    use ModuleAutoloadTrait;

    /**
     * The 33 branding-relevant globals of docs/rebranding.md section 3, in the order of
     * docs/branding-production/14-string-replacement-map.md Part 1 rows 1-33.
     */
    private const INHERITED_KEYS = [
        'openemr_name',
        'login_tagline_text',
        'show_tagline_on_login',
        'main_menu_logo_link',
        'main_menu_logo_title',
        'display_main_menu_logo',
        'online_support_link',
        'user_manual_link',
        'support_phone_number',
        'display_acknowledgements',
        'display_review_link',
        'display_donations_link',
        'display_acknowledgements_on_login',
        'login_page_layout',
        'primary_logo_width',
        'secondary_logo_width',
        'logo_position',
        'show_primary_logo',
        'extra_logo_login',
        'secondary_logo_position',
        'show_labels_on_login_form',
        'show_label_login',
        'tiny_logo_1',
        'tiny_logo_2',
        'css_header',
        'theme_tabs_layout',
        'window_title_add_patient_name',
        'portal_css_header',
        'show_portal_primary_logo',
        'extra_portal_logo_login',
        'secondary_portal_logo_position',
        'portal_primary_menu_logo_height',
        'statement_logo',
    ];

    /** The 7 layer-owned globals of plan section 3.4.2. */
    private const LAYER_OWNED_KEYS = [
        'saas_branding_revision',
        'saas_branding_tokens_light',
        'saas_branding_tokens_dark',
        'saas_branding_product_name_ar',
        'saas_branding_tenant_display_name',
        'saas_branding_tenant_display_name_ar',
        'saas_branding_materialised_at',
    ];

    /**
     * Substrings that would leak upstream OpenEMR identity if they ever reached a
     * rendered surface through a branding default.
     */
    private const FORBIDDEN_IN_DEFAULTS = ['open-emr', 'openemr', 'open emr'];

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    public function testRegistryHoldsExactlyTheContractedKeys(): void
    {
        $this->assertSame(
            [...self::INHERITED_KEYS, ...self::LAYER_OWNED_KEYS],
            array_map(static fn (BrandingGlobalKey $key): string => $key->value, BrandingGlobalKey::cases())
        );
    }

    public function testInheritedAndLayerOwnedGroupsPartitionTheRegistry(): void
    {
        $this->assertSame(
            self::INHERITED_KEYS,
            array_map(static fn (BrandingGlobalKey $key): string => $key->value, BrandingGlobalKey::inherited())
        );
        $this->assertSame(
            self::LAYER_OWNED_KEYS,
            array_map(static fn (BrandingGlobalKey $key): string => $key->value, BrandingGlobalKey::layerOwned())
        );
        $this->assertCount(33, BrandingGlobalKey::inherited());
        $this->assertCount(7, BrandingGlobalKey::layerOwned());
    }

    #[DataProvider('everyKeyProvider')]
    public function testEveryKeyRoundTripsThroughItsGlobalsName(BrandingGlobalKey $key): void
    {
        $this->assertSame($key, BrandingGlobalKey::from($key->value));
        $this->assertSame($key, BrandingGlobalKey::tryFrom($key->value));
    }

    #[DataProvider('everyKeyProvider')]
    public function testOwnershipIsDecidedByTheReservedPrefix(BrandingGlobalKey $key): void
    {
        $this->assertSame(
            str_starts_with($key->value, BrandingGlobalKey::LAYER_PREFIX),
            $key->isLayerOwned()
        );
        $this->assertNotSame($key->isLayerOwned(), $key->isInherited());
    }

    #[DataProvider('everyKeyProvider')]
    public function testEveryKeyIsFullyDescribed(BrandingGlobalKey $key): void
    {
        $this->assertNotSame('', $key->label(), "{$key->value} needs an Administration label.");
        $this->assertNotSame('', $key->description(), "{$key->value} needs a description.");
    }

    #[DataProvider('everyKeyProvider')]
    public function testDeclaredValueTypeMatchesTheDeclaredDefault(BrandingGlobalKey $key): void
    {
        $this->assertSame(
            $key->valueType()->rawPhpType(),
            get_debug_type($key->defaultValue()),
            "{$key->value} declares a default whose type contradicts its value type."
        );
    }

    /**
     * The load-bearing assertion of this file: nothing the branding layer falls back to
     * may name the upstream project.
     */
    #[DataProvider('everyKeyProvider')]
    public function testNoDefaultLeaksUpstreamIdentity(BrandingGlobalKey $key): void
    {
        $default = $key->defaultValue();
        if (!is_string($default)) {
            $this->addToAssertionCount(1);

            return;
        }

        foreach (self::FORBIDDEN_IN_DEFAULTS as $forbidden) {
            $this->assertStringNotContainsStringIgnoringCase(
                $forbidden,
                $default,
                "Default for {$key->value} leaks upstream identity."
            );
        }
    }

    public function testCriticalBlankSensitiveDefaultsArePopulated(): void
    {
        // Upstream auto-generates open-emr.org content for both of these when blank.
        $this->assertSame(
            'Thiqa Health Information System',
            BrandingGlobalKey::MainMenuLogoTitle->defaultValue()
        );
        $this->assertSame('https://skyeagle.uk/docs', BrandingGlobalKey::UserManualLink->defaultValue());
        $this->assertSame('https://skyeagle.uk/', BrandingGlobalKey::MainMenuLogoLink->defaultValue());
        $this->assertSame('https://skyeagle.uk/support', BrandingGlobalKey::OnlineSupportLink->defaultValue());
    }

    public function testUpstreamCommunityLinksAreOffByDefault(): void
    {
        $this->assertFalse(BrandingGlobalKey::DisplayReviewLink->defaultValue());
        $this->assertFalse(BrandingGlobalKey::DisplayDonationsLink->defaultValue());
    }

    public function testThemeStylesheetsAreStoredWithoutAnRtlPrefix(): void
    {
        // Decision CR-3: OpenEMR derives rtl_ and compact_ variants itself.
        $this->assertSame('style_light.css', BrandingGlobalKey::CssHeader->defaultValue());
        $this->assertSame('style_light.css', BrandingGlobalKey::PortalCssHeader->defaultValue());
    }

    public function testTokenOverlaysAreEmptyAndNotAdministratorEditable(): void
    {
        foreach ([BrandingGlobalKey::TokensLight, BrandingGlobalKey::TokensDark] as $key) {
            $this->assertSame(BrandingValueType::TokenJson, $key->valueType());
            $this->assertSame('', $key->defaultValue());
            $this->assertFalse(
                $key->isEditableInAdministration(),
                'Locked constraint C1: token overlays must never be administrator-editable.'
            );
        }
    }

    public function testOnlyTenantNameStringsAreAdministratorEditable(): void
    {
        $editable = array_values(array_filter(
            BrandingGlobalKey::layerOwned(),
            static fn (BrandingGlobalKey $key): bool => $key->isEditableInAdministration()
        ));

        $this->assertSame(
            [
                BrandingGlobalKey::ProductNameArabic,
                BrandingGlobalKey::TenantDisplayName,
                BrandingGlobalKey::TenantDisplayNameArabic,
            ],
            $editable
        );
    }

    public function testBooleanDefaultsRenderAsGlobalsValues(): void
    {
        $this->assertSame('1', BrandingGlobalKey::ShowPrimaryLogo->definition()->defaultAsGlobalsValue());
        $this->assertSame('0', BrandingGlobalKey::TinyLogo1->definition()->defaultAsGlobalsValue());
        $this->assertSame('30', BrandingGlobalKey::PortalPrimaryMenuLogoHeight->definition()->defaultAsGlobalsValue());
        $this->assertSame('Thiqa', BrandingGlobalKey::OpenemrName->definition()->defaultAsGlobalsValue());
    }

    public function testTypedDefaultAccessorsRejectMismatchedKeys(): void
    {
        $this->expectException(LogicException::class);

        BrandingGlobalKey::OpenemrName->definition()->intDefault();
    }

    public function testStringAccessorRejectsAnIntegerKey(): void
    {
        $this->expectException(LogicException::class);

        BrandingGlobalKey::Revision->definition()->stringDefault();
    }

    public function testBooleanAccessorRejectsAStringKey(): void
    {
        $this->expectException(LogicException::class);

        BrandingGlobalKey::CssHeader->definition()->boolDefault();
    }

    /**
     * @return iterable<string, array{BrandingGlobalKey}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function everyKeyProvider(): iterable
    {
        self::registerModuleAutoload();

        foreach (BrandingGlobalKey::cases() as $key) {
            yield $key->value => [$key];
        }
    }
}
