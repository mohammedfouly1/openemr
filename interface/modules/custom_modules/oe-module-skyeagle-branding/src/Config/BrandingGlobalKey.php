<?php

/**
 * The closed set of globals the SkyEagle branding layer owns.
 *
 * Two groups, distinguished by isLayerOwned():
 *
 *  - INHERITED (33 cases) -- globals that already exist in library/globals.inc.php and
 *    that the branding layer merely materialises. These are the 33 branding-relevant
 *    globals of docs/rebranding.md section 3, enumerated in
 *    docs/branding-production/14-string-replacement-map.md Part 1 rows 1-33. The layer
 *    never registers them; it only writes and reads them.
 *
 *  - LAYER-OWNED (7 cases) -- new globals introduced by this layer, every one carrying
 *    the reserved 'saas_branding_' prefix (locked Q58). GlobalsRegistrationListener
 *    registers these through GlobalsInitializedEvent so library/globals.inc.php is never
 *    patched (plan section 3.4.2).
 *
 * Rows 34-35 of the string map (portal_onsite_two_address, portal_onsite_two_enable) are
 * deliberately absent: they are portal-enablement settings, not branding, and the layer
 * has no authority over them.
 *
 * Every default declared here is a SkyEagle value or a safe blank. No default is ever an
 * upstream OpenEMR identity value, and two defaults exist purely to prevent one:
 * a blank main_menu_logo_title and a blank user_manual_link both make upstream
 * auto-generate open-emr.org content, so both carry an explicit SkyEagle default.
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
 * String-backed because the value is the literal globals key, exchanged with the
 * `globals` table, $GLOBALS and the Administration screen.
 */
enum BrandingGlobalKey: string
{
    /** Reserved prefix for every global this layer introduces (locked Q58). */
    public const LAYER_PREFIX = 'saas_branding_';

    /** Product root URL. Single source for every default that would otherwise be open-emr.org. */
    public const PRODUCT_URL = 'https://skyeagle.uk/';

    // -----------------------------------------------------------------------------
    // Inherited OpenEMR globals (string map Part 1, rows 1-33)
    // -----------------------------------------------------------------------------

    case OpenemrName = 'openemr_name';
    case LoginTaglineText = 'login_tagline_text';
    case ShowTaglineOnLogin = 'show_tagline_on_login';
    case MainMenuLogoLink = 'main_menu_logo_link';
    case MainMenuLogoTitle = 'main_menu_logo_title';
    case DisplayMainMenuLogo = 'display_main_menu_logo';
    case OnlineSupportLink = 'online_support_link';
    case UserManualLink = 'user_manual_link';
    case SupportPhoneNumber = 'support_phone_number';
    case DisplayAcknowledgements = 'display_acknowledgements';
    case DisplayReviewLink = 'display_review_link';
    case DisplayDonationsLink = 'display_donations_link';
    case DisplayAcknowledgementsOnLogin = 'display_acknowledgements_on_login';
    case LoginPageLayout = 'login_page_layout';
    case PrimaryLogoWidth = 'primary_logo_width';
    case SecondaryLogoWidth = 'secondary_logo_width';
    case LogoPosition = 'logo_position';
    case ShowPrimaryLogo = 'show_primary_logo';
    case ExtraLogoLogin = 'extra_logo_login';
    case SecondaryLogoPosition = 'secondary_logo_position';
    case ShowLabelsOnLoginForm = 'show_labels_on_login_form';
    case ShowLabelLogin = 'show_label_login';
    case TinyLogo1 = 'tiny_logo_1';
    case TinyLogo2 = 'tiny_logo_2';
    case CssHeader = 'css_header';
    case ThemeTabsLayout = 'theme_tabs_layout';
    case WindowTitleAddPatientName = 'window_title_add_patient_name';
    case PortalCssHeader = 'portal_css_header';
    case ShowPortalPrimaryLogo = 'show_portal_primary_logo';
    case ExtraPortalLogoLogin = 'extra_portal_logo_login';
    case SecondaryPortalLogoPosition = 'secondary_portal_logo_position';
    case PortalPrimaryMenuLogoHeight = 'portal_primary_menu_logo_height';
    case StatementLogo = 'statement_logo';

    // -----------------------------------------------------------------------------
    // Layer-owned globals (plan section 3.4.2)
    // -----------------------------------------------------------------------------

    case Revision = 'saas_branding_revision';
    case TokensLight = 'saas_branding_tokens_light';
    case TokensDark = 'saas_branding_tokens_dark';
    case ProductNameArabic = 'saas_branding_product_name_ar';
    case TenantDisplayName = 'saas_branding_tenant_display_name';
    case TenantDisplayNameArabic = 'saas_branding_tenant_display_name_ar';
    case MaterialisedAt = 'saas_branding_materialised_at';

    /**
     * True for the globals this layer introduces, false for the ones it inherits.
     *
     * Ownership is derived from the reserved prefix rather than restated in a match:
     * locked Q58 makes the prefix the definition of ownership, so deriving it means a
     * new case cannot be added to the wrong group by accident.
     */
    public function isLayerOwned(): bool
    {
        return str_starts_with($this->value, self::LAYER_PREFIX);
    }

    /** True for globals that already exist in library/globals.inc.php. */
    public function isInherited(): bool
    {
        return !$this->isLayerOwned();
    }

    /**
     * The layer-owned globals, in declaration order.
     *
     * @return list<self>
     */
    public static function layerOwned(): array
    {
        return array_values(array_filter(self::cases(), static fn (self $key): bool => $key->isLayerOwned()));
    }

    /**
     * The inherited globals, in declaration order.
     *
     * @return list<self>
     */
    public static function inherited(): array
    {
        return array_values(array_filter(self::cases(), static fn (self $key): bool => $key->isInherited()));
    }

    /** How BrandingConfigFactory must parse this key's raw value. */
    public function valueType(): BrandingValueType
    {
        return $this->definition()->valueType;
    }

    /** The safe fallback for a missing or blank value. Never an upstream identity value. */
    public function defaultValue(): string|int|bool
    {
        return $this->definition()->default;
    }

    /** Short human name for the Administration screen. */
    public function label(): string
    {
        return $this->definition()->label;
    }

    /** One-line explanation for the Administration screen. */
    public function description(): string
    {
        return $this->definition()->description;
    }

    /** True when an administrator may type this value; false for materialiser-owned values. */
    public function isEditableInAdministration(): bool
    {
        return $this->definition()->editableInAdministration;
    }

    /**
     * The single source of truth for every key's metadata.
     *
     * Exhaustive match with no default branch: adding a case without describing it is a
     * static-analysis failure, which is the point.
     */
    public function definition(): BrandingGlobalDefinition
    {
        return match ($this) {
            self::OpenemrName => new BrandingGlobalDefinition(
                'Product name',
                'The product name shown throughout the interface.',
                BrandingValueType::Text,
                'SkyEagle',
            ),
            self::LoginTaglineText => new BrandingGlobalDefinition(
                'Login tagline',
                'The tagline shown beneath the logo on the login page.',
                BrandingValueType::Text,
                'Clinical confidence, connected care.',
            ),
            self::ShowTaglineOnLogin => new BrandingGlobalDefinition(
                'Show tagline on login',
                'Whether the login page displays the tagline.',
                BrandingValueType::Flag,
                true,
            ),
            self::MainMenuLogoLink => new BrandingGlobalDefinition(
                'Main menu logo link',
                'Destination of the main menu logo. Defaults to the product site, never open-emr.org.',
                BrandingValueType::Text,
                self::PRODUCT_URL,
            ),
            self::MainMenuLogoTitle => new BrandingGlobalDefinition(
                'Main menu logo title',
                'Tooltip on the main menu logo. Must never be blank: upstream generates an '
                    . 'open-emr.org title when it is.',
                BrandingValueType::Text,
                'SkyEagle Health Information System',
            ),
            self::DisplayMainMenuLogo => new BrandingGlobalDefinition(
                'Display main menu logo',
                'Whether the main menu shows the product logo.',
                BrandingValueType::Flag,
                true,
            ),
            self::OnlineSupportLink => new BrandingGlobalDefinition(
                'Online support link',
                'Support destination. HTTPS is mandatory and the host is the product domain.',
                BrandingValueType::Text,
                'https://skyeagle.uk/en/contact',
            ),
            self::UserManualLink => new BrandingGlobalDefinition(
                'User manual link',
                'Documentation destination. Must never be blank: upstream falls back to the '
                    . 'open-emr.org wiki when it is.',
                BrandingValueType::Text,
                'https://skyeagle.uk/en/resources',
            ),
            self::SupportPhoneNumber => new BrandingGlobalDefinition(
                'Support phone number',
                'Tenant-specific support telephone number. Blank at product level, which is safe: '
                    . 'a blank number renders nothing rather than an upstream value.',
                BrandingValueType::Text,
                '',
            ),
            self::DisplayAcknowledgements => new BrandingGlobalDefinition(
                'Display acknowledgements',
                'Whether the acknowledgements, licensing and certification page is reachable.',
                BrandingValueType::Flag,
                true,
            ),
            self::DisplayReviewLink => new BrandingGlobalDefinition(
                'Display review link',
                'Upstream community review link. Off: it points away from the product.',
                BrandingValueType::Flag,
                false,
            ),
            self::DisplayDonationsLink => new BrandingGlobalDefinition(
                'Display donations link',
                'Upstream donations link. Off: it points away from the product.',
                BrandingValueType::Flag,
                false,
            ),
            self::DisplayAcknowledgementsOnLogin => new BrandingGlobalDefinition(
                'Display acknowledgements on login',
                'Whether the login page links to the acknowledgements page.',
                BrandingValueType::Flag,
                true,
            ),
            self::LoginPageLayout => new BrandingGlobalDefinition(
                'Login page layout',
                'Login page arrangement.',
                BrandingValueType::Text,
                'vertical_band',
            ),
            self::PrimaryLogoWidth => new BrandingGlobalDefinition(
                'Primary logo width',
                'Bootstrap width utility applied to the primary login logo.',
                BrandingValueType::Text,
                'w-50',
            ),
            self::SecondaryLogoWidth => new BrandingGlobalDefinition(
                'Secondary logo width',
                'Bootstrap width utility applied to the secondary login logo.',
                BrandingValueType::Text,
                'w-50',
            ),
            self::LogoPosition => new BrandingGlobalDefinition(
                'Logo position',
                'Flex direction of the login logo stack.',
                BrandingValueType::Text,
                'flex-column',
            ),
            self::ShowPrimaryLogo => new BrandingGlobalDefinition(
                'Show primary logo',
                'Whether the login page shows the primary logo.',
                BrandingValueType::Flag,
                true,
            ),
            self::ExtraLogoLogin => new BrandingGlobalDefinition(
                'Show secondary login logo',
                'Whether the login page shows a second logo.',
                BrandingValueType::Flag,
                false,
            ),
            self::SecondaryLogoPosition => new BrandingGlobalDefinition(
                'Secondary logo position',
                'Where the secondary login logo sits relative to the primary.',
                BrandingValueType::Text,
                'second',
            ),
            self::ShowLabelsOnLoginForm => new BrandingGlobalDefinition(
                'Show labels on login form',
                'Whether login inputs carry visible labels.',
                BrandingValueType::Flag,
                true,
            ),
            self::ShowLabelLogin => new BrandingGlobalDefinition(
                'Show product label on login',
                'Whether the product name is printed beside the login logo.',
                BrandingValueType::Flag,
                false,
            ),
            self::TinyLogo1 => new BrandingGlobalDefinition(
                'Tiny primary logo',
                'Whether the primary login logo renders at reduced size.',
                BrandingValueType::Flag,
                false,
            ),
            self::TinyLogo2 => new BrandingGlobalDefinition(
                'Tiny secondary logo',
                'Whether the secondary login logo renders at reduced size.',
                BrandingValueType::Flag,
                false,
            ),
            self::CssHeader => new BrandingGlobalDefinition(
                'Theme stylesheet',
                'Stored without an rtl_ prefix in every language: OpenEMR derives the RTL and '
                    . 'compact variants itself (decision CR-3).',
                BrandingValueType::Text,
                'style_light.css',
            ),
            self::ThemeTabsLayout => new BrandingGlobalDefinition(
                'Tabs layout stylesheet',
                'Stylesheet controlling the tabbed navigation layout.',
                BrandingValueType::Text,
                'tabs_style_full.css',
            ),
            self::WindowTitleAddPatientName => new BrandingGlobalDefinition(
                'Add patient name to window title',
                'Off by default: patient names do not belong in browser history or window titles.',
                BrandingValueType::Flag,
                false,
            ),
            self::PortalCssHeader => new BrandingGlobalDefinition(
                'Portal theme stylesheet',
                'Portal stylesheet, stored without an rtl_ prefix for the same reason as the '
                    . 'main theme (decision CR-3).',
                BrandingValueType::Text,
                'style_light.css',
            ),
            self::ShowPortalPrimaryLogo => new BrandingGlobalDefinition(
                'Show portal primary logo',
                'Whether the patient portal shows the primary logo.',
                BrandingValueType::Flag,
                true,
            ),
            self::ExtraPortalLogoLogin => new BrandingGlobalDefinition(
                'Show secondary portal logo',
                'Whether the patient portal login shows a second logo.',
                BrandingValueType::Flag,
                false,
            ),
            self::SecondaryPortalLogoPosition => new BrandingGlobalDefinition(
                'Secondary portal logo position',
                'Where the secondary portal logo sits relative to the primary.',
                BrandingValueType::Text,
                'second',
            ),
            self::PortalPrimaryMenuLogoHeight => new BrandingGlobalDefinition(
                'Portal menu logo height',
                'Height in pixels of the portal menu logo.',
                BrandingValueType::Integer,
                30,
            ),
            self::StatementLogo => new BrandingGlobalDefinition(
                'Statement logo',
                'Logo file stamped on printed statements.',
                BrandingValueType::Text,
                'practice_logo.gif',
            ),
            self::Revision => new BrandingGlobalDefinition(
                'Branding revision',
                'Cache key stamped onto every branding URL. Written by the materialiser; '
                    . '0 means nothing has been materialised yet.',
                BrandingValueType::Integer,
                0,
            ),
            self::TokensLight => new BrandingGlobalDefinition(
                'Light token overlay',
                'Validated Tier 2 token overlay for the light variant. Written by the '
                    . 'materialiser only; empty means the shared Tier 1 palette applies unchanged.',
                BrandingValueType::TokenJson,
                '',
            ),
            self::TokensDark => new BrandingGlobalDefinition(
                'Dark token overlay',
                'Validated Tier 2 token overlay for the dark variant. Written by the '
                    . 'materialiser only; empty means the shared Tier 1 palette applies unchanged.',
                BrandingValueType::TokenJson,
                '',
            ),
            self::ProductNameArabic => new BrandingGlobalDefinition(
                'Product name (Arabic)',
                'Arabic product name used on right-to-left surfaces.',
                BrandingValueType::Text,
                'سكاي إيجل',
                true,
            ),
            self::TenantDisplayName => new BrandingGlobalDefinition(
                'Tenant display name',
                'Tenant lockup text in English. Blank hides the lockup entirely.',
                BrandingValueType::Text,
                '',
                true,
            ),
            self::TenantDisplayNameArabic => new BrandingGlobalDefinition(
                'Tenant display name (Arabic)',
                'Tenant lockup text in Arabic. Blank hides the lockup entirely.',
                BrandingValueType::Text,
                '',
                true,
            ),
            self::MaterialisedAt => new BrandingGlobalDefinition(
                'Last materialised at',
                'ISO-8601 instant of the last successful materialisation. Written by the '
                    . 'materialiser; used to detect staleness.',
                BrandingValueType::Timestamp,
                '',
            ),
        };
    }
}
