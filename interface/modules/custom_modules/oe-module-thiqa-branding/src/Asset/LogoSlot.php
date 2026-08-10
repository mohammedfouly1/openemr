<?php

/**
 * The nine runtime logo lookups OpenEMR performs.
 *
 * Enumerated from the certified Group 1 discovery inventory (docs/rebranding.md
 * section 6, Direction A) and mapped to BRAND ids 014-022. This is a closed set:
 * a slot that is not listed here cannot be resolved, which keeps tenant asset
 * intake bounded to slots the application actually reads.
 *
 * The backed value is the path fragment LogoService expects as its $type argument.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Asset;

enum LogoSlot: string
{
    case CoreLoginPrimary = 'core/login/primary';
    case CoreLoginSecondary = 'core/login/secondary';
    case CoreLoginSmallPrimary = 'core/login/small_logo_1';
    case CoreLoginSmallSecondary = 'core/login/small_logo_2';
    case CoreMenuPrimary = 'core/menu/primary';
    case CoreFavicon = 'core/favicon';
    case PortalLoginPrimary = 'portal/login/primary';
    case PortalLoginSecondary = 'portal/login/secondary';
    case PortalMenuPrimary = 'portal/menu/primary';

    /** The canonical BRAND inventory id for this slot. */
    public function brandId(): string
    {
        return match ($this) {
            self::CoreLoginPrimary => 'BRAND-014',
            self::CoreLoginSecondary => 'BRAND-015',
            self::CoreLoginSmallPrimary => 'BRAND-016',
            self::CoreLoginSmallSecondary => 'BRAND-017',
            self::CoreMenuPrimary => 'BRAND-018',
            self::CoreFavicon => 'BRAND-019',
            self::PortalLoginPrimary => 'BRAND-020',
            self::PortalLoginSecondary => 'BRAND-021',
            self::PortalMenuPrimary => 'BRAND-022',
        };
    }

    /**
     * The filename pattern LogoService should match for this slot.
     *
     * Every slot is extension-agnostic ("logo.*") except the favicon, which
     * Header::getFavIcon() requests by exact name.
     */
    public function filenamePattern(): string
    {
        return match ($this) {
            self::CoreFavicon => 'favicon.ico',
            default => 'logo.*',
        };
    }

    /**
     * Expected pixel dimensions, from the certified asset manifest.
     *
     * @return array{width: int, height: int}|null Null where the slot has no fixed size.
     */
    public function expectedDimensions(): ?array
    {
        return match ($this) {
            self::CoreLoginPrimary, self::PortalLoginPrimary => ['width' => 1053, 'height' => 390],
            self::CoreLoginSecondary, self::PortalLoginSecondary => ['width' => 300, 'height' => 100],
            self::CoreLoginSmallPrimary, self::CoreLoginSmallSecondary => ['width' => 101, 'height' => 100],
            self::PortalMenuPrimary => ['width' => 870, 'height' => 222],
            self::CoreMenuPrimary, self::CoreFavicon => null,
        };
    }

    /** True when the slot is rendered inside the patient portal rather than the main shell. */
    public function isPortalSlot(): bool
    {
        return match ($this) {
            self::PortalLoginPrimary, self::PortalLoginSecondary, self::PortalMenuPrimary => true,
            self::CoreLoginPrimary, self::CoreLoginSecondary, self::CoreLoginSmallPrimary,
            self::CoreLoginSmallSecondary, self::CoreMenuPrimary, self::CoreFavicon => false,
        };
    }
}
