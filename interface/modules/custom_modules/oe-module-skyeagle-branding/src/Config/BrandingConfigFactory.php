<?php

/**
 * Builds the request's BrandingConfig from the globals bag, once.
 *
 * This class is the branding layer's parsing boundary. Raw globals are strings of
 * unknown provenance; everything past this point is typed and already valid, so no
 * consumer needs to re-check a value or invent its own fallback ("parse, don't
 * validate"). Only the typed ParameterBag getters are used -- getString, getInt,
 * getBoolean -- never get() plus a cast.
 *
 * The default rule, stated once so no caller has to guess: a global that is absent, or
 * present but blank after trimming, resolves to the documented default declared on
 * BrandingGlobalKey. A blank global is indistinguishable from an unset one at the
 * globals layer, so both are treated as "not configured". Crucially, no default is an
 * upstream OpenEMR identity value: main_menu_logo_title and user_manual_link both make
 * upstream auto-generate open-emr.org content when left blank, so this factory can
 * never produce a blank for either.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Config;

use DateTimeImmutable;
use DateTimeInterface;
use OpenEMR\Core\OEGlobalsBag;
use UnexpectedValueException;

final class BrandingConfigFactory
{
    private ?BrandingConfig $config = null;

    public function __construct(private readonly OEGlobalsBag $globals)
    {
    }

    /**
     * The branding configuration for this request.
     *
     * Memoised: the globals bag is read exactly once per request, so every consumer sees
     * the same immutable snapshot even if a later component mutates $GLOBALS.
     */
    public function create(): BrandingConfig
    {
        return $this->config ??= $this->build();
    }

    private function build(): BrandingConfig
    {
        return new BrandingConfig(
            productName: $this->text(BrandingGlobalKey::OpenemrName),
            productNameArabic: $this->text(BrandingGlobalKey::ProductNameArabic),
            tenantDisplayName: $this->text(BrandingGlobalKey::TenantDisplayName),
            tenantDisplayNameArabic: $this->text(BrandingGlobalKey::TenantDisplayNameArabic),
            loginTagline: $this->text(BrandingGlobalKey::LoginTaglineText),
            showTaglineOnLogin: $this->flag(BrandingGlobalKey::ShowTaglineOnLogin),
            mainMenuLogoLink: $this->text(BrandingGlobalKey::MainMenuLogoLink),
            mainMenuLogoTitle: $this->text(BrandingGlobalKey::MainMenuLogoTitle),
            displayMainMenuLogo: $this->flag(BrandingGlobalKey::DisplayMainMenuLogo),
            onlineSupportLink: $this->text(BrandingGlobalKey::OnlineSupportLink),
            userManualLink: $this->text(BrandingGlobalKey::UserManualLink),
            supportPhoneNumber: $this->text(BrandingGlobalKey::SupportPhoneNumber),
            displayAcknowledgements: $this->flag(BrandingGlobalKey::DisplayAcknowledgements),
            displayReviewLink: $this->flag(BrandingGlobalKey::DisplayReviewLink),
            displayDonationsLink: $this->flag(BrandingGlobalKey::DisplayDonationsLink),
            displayAcknowledgementsOnLogin: $this->flag(BrandingGlobalKey::DisplayAcknowledgementsOnLogin),
            loginPageLayout: $this->text(BrandingGlobalKey::LoginPageLayout),
            primaryLogoWidth: $this->text(BrandingGlobalKey::PrimaryLogoWidth),
            secondaryLogoWidth: $this->text(BrandingGlobalKey::SecondaryLogoWidth),
            logoPosition: $this->text(BrandingGlobalKey::LogoPosition),
            showPrimaryLogo: $this->flag(BrandingGlobalKey::ShowPrimaryLogo),
            extraLogoLogin: $this->flag(BrandingGlobalKey::ExtraLogoLogin),
            secondaryLogoPosition: $this->text(BrandingGlobalKey::SecondaryLogoPosition),
            showLabelsOnLoginForm: $this->flag(BrandingGlobalKey::ShowLabelsOnLoginForm),
            showLabelLogin: $this->flag(BrandingGlobalKey::ShowLabelLogin),
            tinyLogoPrimary: $this->flag(BrandingGlobalKey::TinyLogo1),
            tinyLogoSecondary: $this->flag(BrandingGlobalKey::TinyLogo2),
            cssHeader: $this->text(BrandingGlobalKey::CssHeader),
            themeTabsLayout: $this->text(BrandingGlobalKey::ThemeTabsLayout),
            windowTitleAddPatientName: $this->flag(BrandingGlobalKey::WindowTitleAddPatientName),
            portalCssHeader: $this->text(BrandingGlobalKey::PortalCssHeader),
            showPortalPrimaryLogo: $this->flag(BrandingGlobalKey::ShowPortalPrimaryLogo),
            extraPortalLogoLogin: $this->flag(BrandingGlobalKey::ExtraPortalLogoLogin),
            secondaryPortalLogoPosition: $this->text(BrandingGlobalKey::SecondaryPortalLogoPosition),
            portalPrimaryMenuLogoHeight: $this->positiveInteger(BrandingGlobalKey::PortalPrimaryMenuLogoHeight),
            statementLogo: $this->text(BrandingGlobalKey::StatementLogo),
            revision: $this->nonNegativeInteger(BrandingGlobalKey::Revision),
            lightOverlay: $this->overlay(BrandingGlobalKey::TokensLight),
            darkOverlay: $this->overlay(BrandingGlobalKey::TokensDark),
            materialisedAt: $this->timestamp(BrandingGlobalKey::MaterialisedAt),
        );
    }

    /**
     * A trimmed string, or the key's documented default when absent or blank.
     */
    private function text(BrandingGlobalKey $key): string
    {
        $raw = $this->raw($key);

        return $raw ?? $key->definition()->stringDefault();
    }

    /**
     * A boolean. Absent or blank resolves to the documented default; anything else goes
     * through getBoolean, so '1', 'true', 'on' and 'yes' are all true and '0' is false.
     */
    private function flag(BrandingGlobalKey $key): bool
    {
        $default = $key->definition()->boolDefault();
        if ($this->raw($key) === null) {
            return $default;
        }

        try {
            return $this->globals->getBoolean($key->value, $default);
        } catch (UnexpectedValueException) {
            // getBoolean throws on a value FILTER_VALIDATE_BOOL cannot read at all (say
            // 'perhaps'; 'off', 'no' and '0' are all read fine). An unreadable value is
            // treated the same way as a missing one -- the documented default -- so a
            // corrupt global degrades to product branding instead of 500ing the page.
            return $default;
        }
    }

    /**
     * An integer that must be greater than zero; zero or negative is malformed and
     * resolves to the documented default. Used for measurements such as logo heights,
     * where zero would silently collapse the element.
     */
    private function positiveInteger(BrandingGlobalKey $key): int
    {
        $default = $key->definition()->intDefault();
        $value = $this->integer($key, $default);

        return $value > 0 ? $value : $default;
    }

    /**
     * An integer that may legitimately be zero. Used for the revision counter, where
     * zero is the meaningful "never materialised" state; only negatives are rejected.
     */
    private function nonNegativeInteger(BrandingGlobalKey $key): int
    {
        $default = $key->definition()->intDefault();
        $value = $this->integer($key, $default);

        return $value >= 0 ? $value : $default;
    }

    /**
     * The raw integer behind a key, with absent, blank and unreadable values all
     * resolving to the supplied default. Range rules belong to the callers above.
     */
    private function integer(BrandingGlobalKey $key, int $default): int
    {
        if ($this->raw($key) === null) {
            return $default;
        }

        try {
            return $this->globals->getInt($key->value, $default);
        } catch (UnexpectedValueException) {
            // getInt throws on anything FILTER_VALIDATE_INT rejects, such as '30px'.
            return $default;
        }
    }

    /**
     * A Tier 2 token overlay. Blank, absent or malformed all yield the empty overlay, so
     * the shared Tier 1 palette applies unchanged rather than a partial one.
     */
    private function overlay(BrandingGlobalKey $key): TokenOverlay
    {
        $raw = $this->raw($key);

        return $raw === null ? TokenOverlay::empty() : TokenOverlay::fromJson($raw);
    }

    /**
     * An ISO-8601 (RFC 3339) instant, or null when absent, blank or not exactly in that
     * format. Round-tripping through DATE_ATOM rejects values PHP would otherwise accept
     * loosely, so a consumer can trust the instant it is given.
     */
    private function timestamp(BrandingGlobalKey $key): ?DateTimeImmutable
    {
        $raw = $this->raw($key);
        if ($raw === null) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $raw);
        if (!$parsed instanceof DateTimeImmutable) {
            return null;
        }

        return $parsed->format(DateTimeInterface::ATOM) === $raw ? $parsed : null;
    }

    /**
     * The trimmed raw value, or null when the global is absent or blank.
     *
     * Blank and absent are collapsed here so that every parse helper applies the same
     * documented rule instead of each inventing its own emptiness test.
     */
    private function raw(BrandingGlobalKey $key): ?string
    {
        if (!$this->globals->has($key->value)) {
            return null;
        }

        try {
            $value = trim($this->globals->getString($key->value));
        } catch (UnexpectedValueException) {
            // getString throws when the global holds something with no string form, such
            // as an array. Unreadable is treated as unconfigured.
            return null;
        }

        return $value === '' ? null : $value;
    }
}
