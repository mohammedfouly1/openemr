<?php

/**
 * Why a proposed tenant token override was refused.
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
 * Backed because it is reported back to the Control Plane and written to structured
 * logs (plan §4.3 WP-2.12), so the wire value has to be stable.
 */
enum RejectionReason: string
{
    /** The key is not a TokenKey at all — it is outside the closed allowlist. */
    case UnknownKey = 'unknown_key';

    /** A real token, but Tier 1 owns it: brand identity, structure, or a semantic state. */
    case NotOverridable = 'not_overridable';

    /** The value is not a #RRGGBB literal (or was not a string). */
    case MalformedValue = 'malformed_value';

    /** The resulting pair misses its WCAG 2.2 threshold. */
    case InsufficientContrast = 'insufficient_contrast';

    /** A disabled fill is too close to the enabled fill or page background. */
    case InsufficientStateSeparation = 'insufficient_state_separation';

    /** The pair cannot be evaluated because the base palette lacks the other side. */
    case MissingReferenceToken = 'missing_reference_token';

    public function summary(): string
    {
        return match ($this) {
            self::UnknownKey => 'Key is not a recognised branding token.',
            self::NotOverridable => 'Key is fixed by the product and cannot be set per tenant.',
            self::MalformedValue => 'Value is not a six-digit hexadecimal colour.',
            self::InsufficientContrast => 'Resulting colour pair fails its WCAG 2.2 contrast gate.',
            self::InsufficientStateSeparation =>
                'Disabled control is not distinguishable from its enabled state and background.',
            self::MissingReferenceToken => 'Base palette does not define the token this pair is measured against.',
        };
    }
}
