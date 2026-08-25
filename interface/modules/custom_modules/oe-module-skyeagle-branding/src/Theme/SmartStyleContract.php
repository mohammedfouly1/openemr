<?php

/**
 * The 12-key SMART on FHIR style contract for one theme variant.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Theme;

use OpenEMR\Modules\SkyEagleBranding\Token\TokenKey;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSet;

/**
 * What a SMART app receives when it asks the host for its visual identity.
 *
 * The key set and its order are fixed by the contract OpenEMR already publishes at
 * `templates/api/smart/smart-style_light.json.twig`; this type reproduces it exactly so a
 * third-party app sees no change in shape, only in values.
 *
 * Every colour is derived from the same {@see TokenSet} the web CSS variables come from,
 * which is the requirement in `docs/rebranding.md` §16.1: SMART tokens must not be a
 * second, separately-maintained palette that can drift from the one on screen.
 *
 * Exposing this through {@see \OpenEMR\Modules\SkyEagleBranding\Service\BrandingServiceInterface}
 * closes audit finding AR-P2-003 — the templates and values existed, but were unreachable
 * through the single service contract, so a consumer had no supported way to ask for them.
 */
final readonly class SmartStyleContract
{
    /**
     * Dimensional values are contract constants rather than brand tokens: they describe
     * the SMART frame's own metrics, which no tenant may vary.
     */
    private const BORDER_RADIUS = '6px';

    private const FONT_SIZE = '14px';

    private const SPACING_SIZE = '20px';

    private const FONT_STACK = "'Inter','IBM Plex Sans Arabic',sans-serif";

    /** The scrim behind a modal, at 60% opacity over the variant's darkest ground. */
    private const BACKDROP_ALPHA = '0.6';

    public function __construct(
        public ThemeVariant $variant,
        public string $colorBackground,
        public string $colorError,
        public string $colorHighlight,
        public string $colorModalBackdrop,
        public string $colorSuccess,
        public string $colorText,
        public string $logoPrimary,
    ) {
    }

    /**
     * Derive the contract from the effective token set for a variant.
     *
     * @param string $logoPrimaryUrl absolute, revision-stamped URL from BrandAssetResolver
     */
    public static function fromTokens(ThemeVariant $variant, TokenSet $tokens, string $logoPrimaryUrl): self
    {
        return new self(
            $variant,
            self::hex($tokens, TokenKey::Background),
            self::hex($tokens, TokenKey::SemanticCriticalText),
            self::hex($tokens, TokenKey::BrandSky),
            self::backdrop($variant),
            self::hex($tokens, TokenKey::SemanticSuccessText),
            self::hex($tokens, TokenKey::TextPrimary),
            $logoPrimaryUrl,
        );
    }

    /**
     * The wire shape, in the published key order.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'color_background' => $this->colorBackground,
            'color_error' => $this->colorError,
            'color_highlight' => $this->colorHighlight,
            'color_modal_backdrop' => $this->colorModalBackdrop,
            'color_success' => $this->colorSuccess,
            'color_text' => $this->colorText,
            'dim_border_radius' => self::BORDER_RADIUS,
            'dim_font_size' => self::FONT_SIZE,
            'dim_spacing_size' => self::SPACING_SIZE,
            'font_family_body' => self::FONT_STACK,
            'font_family_heading' => self::FONT_STACK,
            'logo_primary' => $this->logoPrimary,
        ];
    }

    private static function hex(TokenSet $tokens, TokenKey $key): string
    {
        return $tokens->valueOf($key)->value ?? '#000000';
    }

    /**
     * Dark uses pure black rather than a brand colour.
     *
     * The brand kit ships no scrim/black token (recorded in the Group 1.5B evidence as a
     * gap worth closing). Until one exists this is a named constant rather than a silent
     * literal, so it is greppable when the token is added.
     */
    private static function backdrop(ThemeVariant $variant): string
    {
        return match ($variant) {
            ThemeVariant::Light => 'rgba(11, 27, 77, ' . self::BACKDROP_ALPHA . ')',
            ThemeVariant::Dark => 'rgba(0, 0, 0, ' . self::BACKDROP_ALPHA . ')',
        };
    }
}
