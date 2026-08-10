<?php

/**
 * WCAG 2.2 contrast test double for the token validator tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Token;

use OpenEMR\Modules\ThiqaBranding\Accessibility\ContrastCalculatorInterface;

/**
 * A real relative-luminance implementation rather than a canned mock.
 *
 * The point of TokenValidator is that a bad palette is refused, so the tests need a
 * calculator that actually distinguishes a passing pair from a failing one. The
 * shipped implementation is WP-2.3's deliverable and is out of this work package's
 * scope; this double keeps the validator tests honest without depending on it.
 */
final class ContrastCalculatorStub implements ContrastCalculatorInterface
{
    public function ratio(string $foreground, string $background): float
    {
        $first = $this->relativeLuminance($foreground);
        $second = $this->relativeLuminance($background);

        $lighter = max($first, $second);
        $darker = min($first, $second);

        return round(($lighter + 0.05) / ($darker + 0.05), 2);
    }

    public function meetsNormalText(string $foreground, string $background): bool
    {
        return $this->ratio($foreground, $background) >= 4.5;
    }

    public function meetsUiComponent(string $foreground, string $background): bool
    {
        return $this->ratio($foreground, $background) >= 3.0;
    }

    private function relativeLuminance(string $hex): float
    {
        $digits = ltrim($hex, '#');

        $red = $this->channel(substr($digits, 0, 2));
        $green = $this->channel(substr($digits, 2, 2));
        $blue = $this->channel(substr($digits, 4, 2));

        return (0.2126 * $red) + (0.7152 * $green) + (0.0722 * $blue);
    }

    private function channel(string $twoHexDigits): float
    {
        // hexdec() of two digits is always an int in 0..255; the cast documents that
        // rather than widening anything.
        $srgb = (float) hexdec($twoHexDigits) / 255.0;

        return $srgb <= 0.04045
            ? $srgb / 12.92
            : (($srgb + 0.055) / 1.055) ** 2.4;
    }
}
