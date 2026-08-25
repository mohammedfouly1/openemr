<?php

/**
 * WCAG 2.2 contrast calculation contract.
 *
 * Consumed by the token validator so that a tenant cannot configure an
 * inaccessible palette: the check is a gate, not documentation (plan WP-2.3).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Accessibility;

interface ContrastCalculatorInterface
{
    /**
     * Contrast ratio between two colours, per WCAG 2.2 relative luminance.
     *
     * Returns a value in the range 1.0 (identical) to 21.0 (black on white),
     * rounded to two decimal places so results are reproducible against the
     * recorded figures in brand/qa/wcag-contrast-results.json.
     *
     * @param string $foreground Six-digit hex colour, e.g. "#2C5F94"
     * @param string $background Six-digit hex colour, e.g. "#FAFAF8"
     */
    public function ratio(string $foreground, string $background): float;

    /** True when the pair meets SC 1.4.3 for normal-size text (>= 4.5:1). */
    public function meetsNormalText(string $foreground, string $background): bool;

    /** True when the pair meets SC 1.4.11 for non-text UI components (>= 3:1). */
    public function meetsUiComponent(string $foreground, string $background): bool;
}
