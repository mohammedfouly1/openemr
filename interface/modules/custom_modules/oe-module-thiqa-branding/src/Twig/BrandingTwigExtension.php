<?php

/**
 * Exposes the branding facade to Twig, and nothing else.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Twig;

use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingServiceInterface;
use OpenEMR\Modules\ThiqaBranding\Token\TokenKey;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * A thin adapter over BrandingServiceInterface (plan WP-2.11).
 *
 * The extension deliberately holds NO branding knowledge of its own: it owns no
 * palette, no fallback colour and no default logo path. Plan principle P1 says the
 * service is the single front door, so anything this class decided for itself would
 * be a second source of truth that templates could reach but the rest of the
 * application could not.
 *
 * The one thing it does own is the string boundary. Twig calls arrive as bare
 * strings, so each function parses its argument into the closed enum that guards the
 * value (TokenKey, LogoSlot) before the service is consulted -- "parse, don't
 * validate" applied at the template edge. An argument that is not a member of the
 * closed set resolves to the empty string rather than an exception: these functions
 * run on the login page and on the SMART style contract, where a template typo must
 * degrade to an unstyled element, never to a 500 that locks users out. The empty
 * string is also what an unresolved asset yields (BrandAsset::missing()), so callers
 * have one absent-value shape to handle instead of two.
 *
 * Return values are NOT marked is_safe. OpenEMR builds Twig with autoescape off, so
 * every consuming template must escape explicitly with the project's |attr and |text
 * filters; declaring these outputs pre-escaped would quietly remove that obligation.
 */
final class BrandingTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly BrandingServiceInterface $branding,
    ) {
    }

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('productName', $this->productName(...)),
            new TwigFunction('brandingToken', $this->brandingToken(...)),
            new TwigFunction('brandLogo', $this->brandLogo(...)),
            new TwigFunction('brandLogoAlt', $this->brandLogoAlt(...)),
        ];
    }

    /** The product name for the requested language. */
    public function productName(bool $arabic = false): string
    {
        return $this->branding->productName($arabic);
    }

    /**
     * The `#RRGGBB` value of an allowlisted token in the variant this request renders.
     *
     * The variant is read from the service rather than accepted as an argument: a
     * template that could ask for the light palette while the page renders dark would
     * be able to reintroduce the contrast failures the token gate exists to prevent.
     *
     * Returns the empty string when the key is not allowlisted, or when the active
     * variant does not define it (the two palettes are not symmetric -- surfaceRaised
     * is dark-only).
     */
    public function brandingToken(string $key): string
    {
        $tokenKey = TokenKey::tryFrom($key);
        if (!$tokenKey instanceof TokenKey) {
            return '';
        }

        $tokens = $this->branding->tokens($this->branding->themeVariant());

        return $tokens->valueOf($tokenKey)->value ?? '';
    }

    /**
     * The final, cache-keyed URL for a logo slot, addressed by its backed value.
     *
     * Empty when the slot name is not one of the nine runtime lookups, or when the
     * tenant has supplied no asset for it.
     */
    public function brandLogo(string $slot): string
    {
        $logoSlot = LogoSlot::tryFrom($slot);
        if (!$logoSlot instanceof LogoSlot) {
            return '';
        }

        return $this->branding->logo($logoSlot)->url();
    }

    /**
     * The accessible name for a logo slot.
     *
     * Empty means the image is decorative and must be rendered with `alt=""`, which is
     * the correct announcement for a logo that carries no information the surrounding
     * text does not already give.
     */
    public function brandLogoAlt(string $slot, bool $arabic = false): string
    {
        $logoSlot = LogoSlot::tryFrom($slot);
        if (!$logoSlot instanceof LogoSlot) {
            return '';
        }

        return $this->branding->logo($logoSlot)->alt($arabic);
    }
}
