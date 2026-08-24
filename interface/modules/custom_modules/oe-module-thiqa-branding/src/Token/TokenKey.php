<?php

/**
 * The closed allowlist of Thiqa design-token keys.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Token;

/**
 * THIS ENUM IS THE SECURITY BOUNDARY OF THE BRANDING LAYER.
 *
 * Locked Invariant 9 says a tenant must never be able to supply CSS or JavaScript.
 * An allowlist expressed as documentation is a prohibition; an allowlist expressed
 * as a closed enum is a structural impossibility — there is no code path that turns
 * an arbitrary string into a TokenKey, so there is no code path that turns tenant
 * input into a CSS property name. Combined with ColorValue on the value side, the
 * renderer can express nothing except `--known-name: #RRGGBB;` (plan §3.9).
 *
 * Cases are derived one-for-one from brand/tokens/thiqa-tokens.json (the union of the
 * light and dark documents; `surfaceRaised` is dark-only, which is why TokenSet::get()
 * is nullable rather than total).
 *
 * Backed by the dot path used in the JSON source so parse and export round-trip.
 */
enum TokenKey: string
{
    // Brand identity (plan §3.4.1 — never tenant-overridable).
    case BrandNavy = 'brand.navy';
    case BrandCoral = 'brand.coral';
    case BrandCoralDeep = 'brand.coralDeep';
    case BrandSage = 'brand.sage';
    case BrandSky = 'brand.sky';
    case BrandAmber = 'brand.amber';
    case BrandCritical = 'brand.critical';

    // Structural surfaces.
    case Background = 'background';
    case Surface = 'surface';
    case SurfaceRaised = 'surfaceRaised';
    case SurfaceSunken = 'surfaceSunken';
    case SurfaceInput = 'surfaceInput';
    case SurfaceInputOnRaised = 'surfaceInputOnRaised';

    // Borders and dividers.
    case Border = 'border';
    case BorderStrong = 'borderStrong';
    case Divider = 'divider';

    // Text.
    case TextPrimary = 'text.primary';
    case TextSecondary = 'text.secondary';
    case TextDisabled = 'text.disabled';
    case TextInverse = 'text.inverse';

    // Semantic state colours — clinical safety, see isTenantOverridable().
    case SemanticSuccessBg = 'semantic.success.bg';
    case SemanticSuccessText = 'semantic.success.text';
    case SemanticSuccessBorder = 'semantic.success.border';
    case SemanticWarningBg = 'semantic.warning.bg';
    case SemanticWarningText = 'semantic.warning.text';
    case SemanticWarningBorder = 'semantic.warning.border';
    case SemanticCriticalBg = 'semantic.critical.bg';
    case SemanticCriticalText = 'semantic.critical.text';
    case SemanticCriticalBorder = 'semantic.critical.border';
    case SemanticInfoBg = 'semantic.info.bg';
    case SemanticInfoText = 'semantic.info.text';
    case SemanticInfoBorder = 'semantic.info.border';

    // Interactive — the tenant-overridable surface.
    case InteractivePrimaryDefault = 'interactive.primary.default';
    case InteractivePrimaryHover = 'interactive.primary.hover';
    case InteractivePrimaryActive = 'interactive.primary.active';
    case InteractivePrimaryDisabled = 'interactive.primary.disabled';
    case InteractivePrimaryTextOn = 'interactive.primary.textOn';
    case InteractiveSecondaryDefault = 'interactive.secondary.default';
    case InteractiveSecondaryHover = 'interactive.secondary.hover';
    case InteractiveSecondaryTextOn = 'interactive.secondary.textOn';
    case InteractiveFocusRing = 'interactive.focusRing';
    case LinkDefault = 'link.default';
    case LinkHover = 'link.hover';

    /**
     * The CSS custom property this key materialises as.
     *
     * Every arm is a literal. The name is never computed from the backing value,
     * so no transformation bug can widen the character set that reaches a stylesheet.
     * Each literal satisfies /\A--[a-z0-9-]+\z/, which CssVariableRendererTest pins.
     */
    public function cssVariableName(): string
    {
        return match ($this) {
            self::BrandNavy => '--brand-navy',
            self::BrandCoral => '--brand-coral',
            self::BrandCoralDeep => '--brand-coral-deep',
            self::BrandSage => '--brand-sage',
            self::BrandSky => '--brand-sky',
            self::BrandAmber => '--brand-amber',
            self::BrandCritical => '--brand-critical',
            self::Background => '--background',
            self::Surface => '--surface',
            self::SurfaceRaised => '--surface-raised',
            self::SurfaceSunken => '--surface-sunken',
            self::SurfaceInput => '--surface-input',
            self::SurfaceInputOnRaised => '--surface-input-on-raised',
            self::Border => '--border',
            self::BorderStrong => '--border-strong',
            self::Divider => '--divider',
            self::TextPrimary => '--text-primary',
            self::TextSecondary => '--text-secondary',
            self::TextDisabled => '--text-disabled',
            self::TextInverse => '--text-inverse',
            self::SemanticSuccessBg => '--semantic-success-bg',
            self::SemanticSuccessText => '--semantic-success-text',
            self::SemanticSuccessBorder => '--semantic-success-border',
            self::SemanticWarningBg => '--semantic-warning-bg',
            self::SemanticWarningText => '--semantic-warning-text',
            self::SemanticWarningBorder => '--semantic-warning-border',
            self::SemanticCriticalBg => '--semantic-critical-bg',
            self::SemanticCriticalText => '--semantic-critical-text',
            self::SemanticCriticalBorder => '--semantic-critical-border',
            self::SemanticInfoBg => '--semantic-info-bg',
            self::SemanticInfoText => '--semantic-info-text',
            self::SemanticInfoBorder => '--semantic-info-border',
            self::InteractivePrimaryDefault => '--interactive-primary-default',
            self::InteractivePrimaryHover => '--interactive-primary-hover',
            self::InteractivePrimaryActive => '--interactive-primary-active',
            self::InteractivePrimaryDisabled => '--interactive-primary-disabled',
            self::InteractivePrimaryTextOn => '--interactive-primary-text-on',
            self::InteractiveSecondaryDefault => '--interactive-secondary-default',
            self::InteractiveSecondaryHover => '--interactive-secondary-hover',
            self::InteractiveSecondaryTextOn => '--interactive-secondary-text-on',
            self::InteractiveFocusRing => '--interactive-focus-ring',
            self::LinkDefault => '--link-default',
            self::LinkHover => '--link-hover',
        };
    }

    /**
     * Whether a tenant may override this key through the Tier 2 overlay (plan §2.4, §3.4.1).
     *
     * True for exactly the interactive and link surfaces. The disabled primary fill is
     * deliberately included: tenants may align it with their palette, while
     * TokenValidator applies a non-WCAG distinguishability floor against both the
     * enabled primary fill and the page background. Everything else is false:
     *
     *  - brand.* is identity; a tenant recolouring the Thiqa mark is a trademark problem;
     *  - surfaces, borders, dividers and text are structural, and the certified design
     *    (and its recorded WCAG results) depends on them holding still;
     *  - semantic.{success,warning,critical,info}.* are NON-OVERRIDABLE FOR CLINICAL
     *    SAFETY. Status colour is a clinical signal, not decoration: a clinician who has
     *    learned that red means critical must read the same meaning on every tenant. A
     *    tenant that could recolour "critical" toward its brand palette — or swap the
     *    success and warning hues — would be changing the meaning of an alert, not its
     *    styling. This one is a patient-safety boundary and is not negotiable per tenant.
     */
    public function isTenantOverridable(): bool
    {
        return match ($this) {
            self::InteractivePrimaryDefault,
            self::InteractivePrimaryHover,
            self::InteractivePrimaryActive,
            self::InteractivePrimaryDisabled,
            self::InteractivePrimaryTextOn,
            self::InteractiveSecondaryDefault,
            self::InteractiveSecondaryHover,
            self::InteractiveSecondaryTextOn,
            self::InteractiveFocusRing,
            self::LinkDefault,
            self::LinkHover => true,

            self::BrandNavy,
            self::BrandCoral,
            self::BrandCoralDeep,
            self::BrandSage,
            self::BrandSky,
            self::BrandAmber,
            self::BrandCritical,
            self::Background,
            self::Surface,
            self::SurfaceRaised,
            self::SurfaceSunken,
            self::SurfaceInput,
            self::SurfaceInputOnRaised,
            self::Border,
            self::BorderStrong,
            self::Divider,
            self::TextPrimary,
            self::TextSecondary,
            self::TextDisabled,
            self::TextInverse,
            self::SemanticSuccessBg,
            self::SemanticSuccessText,
            self::SemanticSuccessBorder,
            self::SemanticWarningBg,
            self::SemanticWarningText,
            self::SemanticWarningBorder,
            self::SemanticCriticalBg,
            self::SemanticCriticalText,
            self::SemanticCriticalBorder,
            self::SemanticInfoBg,
            self::SemanticInfoText,
            self::SemanticInfoBorder => false,
        };
    }

    /**
     * The WCAG pair this key participates in, or null when it has no gate of its own.
     *
     * Only the overridable surface carries rules, because only the overridable surface
     * can move: the Tier 1 palette is validated once at build time against
     * brand/qa/wcag-contrast-results.json, and re-gating it here would let a recorded
     * Tier 1 exception block an unrelated tenant override.
     *
     * `interactive.primary.disabled` has no WCAG rule: WCAG 2.2 exempts inactive
     * controls from both SC 1.4.3 and SC 1.4.11. TokenValidator applies its separate
     * product distinguishability rule; it must not be represented as a WCAG gate.
     */
    public function contrastRule(): ?ContrastRule
    {
        return match ($this) {
            // Filled controls sit on the page background: SC 1.4.11.
            self::InteractivePrimaryDefault => new ContrastRule(self::InteractivePrimaryDefault, self::Background, ContrastGate::UiComponent),
            self::InteractivePrimaryHover => new ContrastRule(self::InteractivePrimaryHover, self::Background, ContrastGate::UiComponent),
            self::InteractivePrimaryActive => new ContrastRule(self::InteractivePrimaryActive, self::Background, ContrastGate::UiComponent),
            self::InteractiveSecondaryDefault => new ContrastRule(self::InteractiveSecondaryDefault, self::Background, ContrastGate::UiComponent),
            self::InteractiveSecondaryHover => new ContrastRule(self::InteractiveSecondaryHover, self::Background, ContrastGate::UiComponent),
            self::InteractiveFocusRing => new ContrastRule(self::InteractiveFocusRing, self::Background, ContrastGate::UiComponent),

            // Label text sits on its own control: SC 1.4.3.
            self::InteractivePrimaryTextOn => new ContrastRule(self::InteractivePrimaryTextOn, self::InteractivePrimaryDefault, ContrastGate::NormalText),
            self::InteractiveSecondaryTextOn => new ContrastRule(self::InteractiveSecondaryTextOn, self::InteractiveSecondaryDefault, ContrastGate::NormalText),

            // Links are body text on the page background: SC 1.4.3.
            self::LinkDefault => new ContrastRule(self::LinkDefault, self::Background, ContrastGate::NormalText),
            self::LinkHover => new ContrastRule(self::LinkHover, self::Background, ContrastGate::NormalText),

            self::InteractivePrimaryDisabled,
            self::BrandNavy,
            self::BrandCoral,
            self::BrandCoralDeep,
            self::BrandSage,
            self::BrandSky,
            self::BrandAmber,
            self::BrandCritical,
            self::Background,
            self::Surface,
            self::SurfaceRaised,
            self::SurfaceSunken,
            self::SurfaceInput,
            self::SurfaceInputOnRaised,
            self::Border,
            self::BorderStrong,
            self::Divider,
            self::TextPrimary,
            self::TextSecondary,
            self::TextDisabled,
            self::TextInverse,
            self::SemanticSuccessBg,
            self::SemanticSuccessText,
            self::SemanticSuccessBorder,
            self::SemanticWarningBg,
            self::SemanticWarningText,
            self::SemanticWarningBorder,
            self::SemanticCriticalBg,
            self::SemanticCriticalText,
            self::SemanticCriticalBorder,
            self::SemanticInfoBg,
            self::SemanticInfoText,
            self::SemanticInfoBorder => null,
        };
    }

    /**
     * The subset a tenant may override, in declaration order.
     *
     * @return list<self>
     */
    public static function tenantOverridableKeys(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $key): bool => $key->isTenantOverridable(),
        ));
    }
}
