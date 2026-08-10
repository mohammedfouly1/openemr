<?php

/**
 * The 12-key SMART on FHIR style contract, derived from the brand tokens.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * Implements the mapping table in `docs/branding-production/10-channel-evidence.md`
 * and `docs/RebrandingPlan.md` §3.7.5, satisfying `docs/rebranding.md` §16.1:
 * SMART tokens derive from the same source as the web CSS variables and are
 * never maintained separately.
 *
 * Key order matches the existing `templates/api/smart/smart-style_light.json.twig`
 * so the generated light template is a drop-in replacement for it.
 */
final readonly class SmartStyleContract
{
    /**
     * Dimensional keys are contract constants, not brand tokens: the SMART
     * contract fixes them and the existing light template already ships these
     * values. `dim_font_size` is the exception — it comes from the type scale.
     */
    private const BORDER_RADIUS = '6px';
    private const SPACING_SIZE = '20px';
    private const FONT_SIZE_SCALE_STEP = 'body';

    private const MODAL_BACKDROP_ALPHA = 0.6;

    /**
     * The dark backdrop base is pure black per the evidence table
     * (`10-channel-evidence.md`, SMART section). It is the one contract value
     * with no counterpart in `brand/tokens/thiqa-tokens.json`; if a scrim token
     * is ever added there, this constant should be replaced by a token read.
     */
    private const DARK_MODAL_BACKDROP_BASE = '#000000';

    /**
     * Rendered verbatim: `logo_primary` stays a Twig expression so the runtime
     * resolves an absolute, per-tenant, revisioned URL through LogoService.
     */
    private const LOGO_EXPRESSION = '{{ logo.primary }}';

    public function __construct(private ColorPalette $palette, private Typography $typography)
    {
    }

    /**
     * The 12 contract keys in wire order.
     *
     * @return array<string, string>
     */
    public function values(): array
    {
        return [
            'color_background' => $this->palette->color('background')->value,
            'color_error' => $this->palette->color('semantic.critical.text')->value,
            'color_highlight' => $this->palette->color('interactive.focusRing')->value,
            'color_modal_backdrop' => $this->modalBackdrop(),
            'color_success' => $this->palette->color('semantic.success.text')->value,
            'color_text' => $this->palette->color('text.primary')->value,
            'dim_border_radius' => self::BORDER_RADIUS,
            'dim_font_size' => $this->typography->step(self::FONT_SIZE_SCALE_STEP)->size . 'px',
            'dim_spacing_size' => self::SPACING_SIZE,
            'font_family_body' => $this->fontStack(),
            'font_family_heading' => $this->fontStack(),
            'logo_primary' => self::LOGO_EXPRESSION,
        ];
    }

    private function modalBackdrop(): string
    {
        $base = match ($this->palette->variant) {
            Variant::Light => $this->palette->color('brand.navy'),
            Variant::Dark => HexColor::fromString(self::DARK_MODAL_BACKDROP_BASE, 'smart.dark.color_modal_backdrop'),
        };

        return $base->toRgba(self::MODAL_BACKDROP_ALPHA);
    }

    /**
     * SMART apps get the compact two-family stack from the evidence table,
     * built from the typography tokens rather than hard-coded.
     */
    private function fontStack(): string
    {
        $names = $this->typography->familyNames();
        foreach (['name-latin', 'name-arabic'] as $required) {
            if (!isset($names[$required])) {
                throw new GeneratorException(sprintf(
                    'Typography tokens do not provide "%s", required for the SMART font stack.',
                    $required,
                ));
            }
        }

        return sprintf("'%s','%s',sans-serif", $names['name-latin'], $names['name-arabic']);
    }
}
