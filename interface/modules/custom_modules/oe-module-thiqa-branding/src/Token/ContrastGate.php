<?php

/**
 * The WCAG 2.2 threshold a token pair has to clear.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Token;

use OpenEMR\Modules\ThiqaBranding\Accessibility\ContrastCalculatorInterface;

/**
 * Unit enum: the gate is runtime state, never persisted (plan §3.4.1).
 */
enum ContrastGate
{
    /** SC 1.4.3 — text against its own background. */
    case NormalText;

    /** SC 1.4.11 — a non-text UI component against the surface behind it. */
    case UiComponent;

    public function minimumRatio(): float
    {
        return match ($this) {
            self::NormalText => 4.5,
            self::UiComponent => 3.0,
        };
    }

    public function isSatisfiedBy(
        ContrastCalculatorInterface $calculator,
        ColorValue $foreground,
        ColorValue $background,
    ): bool {
        return match ($this) {
            self::NormalText => $calculator->meetsNormalText($foreground->value, $background->value),
            self::UiComponent => $calculator->meetsUiComponent($foreground->value, $background->value),
        };
    }
}
