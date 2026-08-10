<?php

/**
 * WCAG 2.2 contrast ratio calculator for the Thiqa branding token gate.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Accessibility;

use InvalidArgumentException;

/**
 * Pure computation of the WCAG 2.2 contrast ratio between two sRGB colours.
 *
 * Deliberately free of I/O, globals and superglobals: the token validator
 * calls this on every tenant palette change, so it has to be cheap and
 * deterministic. Results are rounded to two decimal places to stay byte
 * reproducible against brand/qa/wcag-contrast-results.json.
 */
final class ContrastCalculator implements ContrastCalculatorInterface
{
    /** SC 1.4.3 minimum for normal-size text. */
    private const NORMAL_TEXT_MINIMUM = 4.5;

    /** SC 1.4.11 minimum for non-text user interface components. */
    private const UI_COMPONENT_MINIMUM = 3.0;

    /** Break point between the linear and the gamma segment of the sRGB curve. */
    private const SRGB_LINEAR_THRESHOLD = 0.03928;

    /** Flare constant that keeps the ratio finite for pure black. */
    private const FLARE = 0.05;

    /**
     * Six hex digits, optional leading hash, either case. Nothing else.
     *
     * The D modifier is load bearing: without it "$" also matches before a
     * trailing newline, so "#FFFFFF\n" would be accepted.
     */
    private const HEX_PATTERN = '/^#?([0-9A-Fa-f]{6})$/D';

    public function ratio(string $foreground, string $background): float
    {
        $foregroundLuminance = $this->relativeLuminance($foreground);
        $backgroundLuminance = $this->relativeLuminance($background);

        $lighter = max($foregroundLuminance, $backgroundLuminance);
        $darker = min($foregroundLuminance, $backgroundLuminance);

        return round(($lighter + self::FLARE) / ($darker + self::FLARE), 2);
    }

    public function meetsNormalText(string $foreground, string $background): bool
    {
        return $this->ratio($foreground, $background) >= self::NORMAL_TEXT_MINIMUM;
    }

    public function meetsUiComponent(string $foreground, string $background): bool
    {
        return $this->ratio($foreground, $background) >= self::UI_COMPONENT_MINIMUM;
    }

    /**
     * WCAG 2.2 relative luminance of a six-digit hex colour.
     *
     * @throws InvalidArgumentException When the colour is not six hex digits.
     */
    private function relativeLuminance(string $colour): float
    {
        [$red, $green, $blue] = $this->channels($colour);

        return (0.2126 * $this->linearise($red))
            + (0.7152 * $this->linearise($green))
            + (0.0722 * $this->linearise($blue));
    }

    /**
     * Split a validated hex colour into its three 0-255 channel values.
     *
     * @return array{int, int, int}
     *
     * @throws InvalidArgumentException When the colour is not six hex digits.
     */
    private function channels(string $colour): array
    {
        $matches = [];
        if (preg_match(self::HEX_PATTERN, $colour, $matches) !== 1) {
            throw new InvalidArgumentException(
                'Colour must be a six-digit hex value such as "#2C5F94".'
            );
        }

        $digits = $matches[1];

        return [
            intval(substr($digits, 0, 2), 16),
            intval(substr($digits, 2, 2), 16),
            intval(substr($digits, 4, 2), 16),
        ];
    }

    /** Undo the sRGB transfer function for a single 0-255 channel value. */
    private function linearise(int $channel): float
    {
        $normalised = $channel / 255.0;

        if ($normalised <= self::SRGB_LINEAR_THRESHOLD) {
            return $normalised / 12.92;
        }

        return (($normalised + 0.055) / 1.055) ** 2.4;
    }
}
