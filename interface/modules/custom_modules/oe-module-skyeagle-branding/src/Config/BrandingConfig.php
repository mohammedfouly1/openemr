<?php

/**
 * The parsed branding configuration for the current request.
 *
 * Every property is already the type its consumer needs: flags are booleans, the logo
 * height is an integer, the materialisation instant is a DateTimeImmutable or null, and
 * the Tier 2 overlays are TokenOverlay objects that cannot contain anything but token
 * pairs. Nothing downstream re-reads a global, re-parses a string or re-validates a
 * value -- BrandingConfigFactory is the only boundary where raw input is interpreted.
 *
 * There is deliberately no free-text CSS, JS or HTML property here (locked constraint
 * C1); the overlays are the only structured payload and they are machine-produced.
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

final readonly class BrandingConfig
{
    public function __construct(
        // Identity ---------------------------------------------------------------
        public string $productName,
        public string $productNameArabic,
        public string $tenantDisplayName,
        public string $tenantDisplayNameArabic,
        public string $loginTagline,
        public bool $showTaglineOnLogin,
        // Outbound links ---------------------------------------------------------
        public string $mainMenuLogoLink,
        public string $mainMenuLogoTitle,
        public bool $displayMainMenuLogo,
        public string $onlineSupportLink,
        public string $userManualLink,
        public string $supportPhoneNumber,
        // Upstream community surfaces --------------------------------------------
        public bool $displayAcknowledgements,
        public bool $displayReviewLink,
        public bool $displayDonationsLink,
        public bool $displayAcknowledgementsOnLogin,
        // Login page layout ------------------------------------------------------
        public string $loginPageLayout,
        public string $primaryLogoWidth,
        public string $secondaryLogoWidth,
        public string $logoPosition,
        public bool $showPrimaryLogo,
        public bool $extraLogoLogin,
        public string $secondaryLogoPosition,
        public bool $showLabelsOnLoginForm,
        public bool $showLabelLogin,
        public bool $tinyLogoPrimary,
        public bool $tinyLogoSecondary,
        // Theme ------------------------------------------------------------------
        public string $cssHeader,
        public string $themeTabsLayout,
        public bool $windowTitleAddPatientName,
        // Patient portal ---------------------------------------------------------
        public string $portalCssHeader,
        public bool $showPortalPrimaryLogo,
        public bool $extraPortalLogoLogin,
        public string $secondaryPortalLogoPosition,
        public int $portalPrimaryMenuLogoHeight,
        // Print / documents ------------------------------------------------------
        public string $statementLogo,
        // Materialisation state (layer-owned) ------------------------------------
        public int $revision,
        public TokenOverlay $lightOverlay,
        public TokenOverlay $darkOverlay,
        public ?DateTimeImmutable $materialisedAt,
    ) {
    }

    /**
     * Whether the branding layer has ever run a successful materialisation.
     *
     * False means the request is being served from defaults alone, which is safe but
     * worth surfacing in Administration and in observability.
     */
    public function isMaterialised(): bool
    {
        return $this->materialisedAt instanceof DateTimeImmutable && $this->revision > 0;
    }

    /** Whether any Tier 2 tenant overlay applies to either variant. */
    public function hasTenantOverlay(): bool
    {
        return !$this->lightOverlay->isEmpty() || !$this->darkOverlay->isEmpty();
    }

    /** Whether a tenant lockup should render alongside the product mark. */
    public function hasTenantLockup(): bool
    {
        return $this->tenantDisplayName !== '' || $this->tenantDisplayNameArabic !== '';
    }
}
