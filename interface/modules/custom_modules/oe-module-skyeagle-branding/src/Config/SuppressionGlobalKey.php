<?php

/**
 * The closed set of non-branding globals the SkyEagle profile is allowed to switch off.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Config;

/**
 * Why this exists at all, next to {@see BrandingGlobalKey}.
 *
 * `BrandingGlobalKey` is defined as "the globals the branding layer *owns*", and its
 * docblock is explicit that settings the layer has no authority over stay out of it —
 * that is why `portal_onsite_two_enable` is deliberately absent. WS-G's third HIDE item,
 * BRAND-105, is exactly that shape: `enable_help` is a feature gate, not a brand
 * identity value. The branding layer does not own it, has no opinion on it outside the
 * product profile, and must not start claiming it.
 *
 * But the profile still has to suppress it, because the in-app help pages carry roughly
 * 180 open-emr.org wiki links and inherited brand prose that the demo must not show.
 * Hiding the modal is the cheap, fully reversible control; rewriting
 * `Documentation/help_files/**` is the expensive one, and is separately tracked.
 *
 * So: a second closed enum, deliberately tiny, holding the keys the *profile* switches
 * off without the *layer* owning them. Every profile key still resolves to an enum case;
 * a raw string is still never written; and adding a case here is a visible, reviewable
 * act rather than a widening of what "branding" means.
 */
enum SuppressionGlobalKey: string
{
    /**
     * BRAND-105. Tri-state upstream (`library/globals.inc.php:1086-1096`):
     * `0` hide, `1` show, `2` disable.
     */
    case EnableHelp = 'enable_help';

    /** Short human name, matching the vocabulary BrandingGlobalKey uses. */
    public function label(): string
    {
        return match ($this) {
            self::EnableHelp => 'In-app help modal',
        };
    }

    /** One-line explanation of why the profile touches a global the layer does not own. */
    public function description(): string
    {
        return match ($this) {
            self::EnableHelp => 'Upstream in-app help. Hidden: its pages carry open-emr.org wiki links '
                . 'and inherited brand prose (BRAND-105).',
        };
    }

    /**
     * How a profile value for this key must be parsed.
     *
     * Text rather than Flag: `enable_help` is tri-state, so a boolean reading would
     * silently collapse "disable" into "hide". {@see self::permittedValues()} carries the
     * real constraint.
     */
    public function valueType(): BrandingValueType
    {
        return match ($this) {
            self::EnableHelp => BrandingValueType::Text,
        };
    }

    /**
     * The exact set of values upstream understands for this key.
     *
     * @return non-empty-list<string>
     */
    public function permittedValues(): array
    {
        return match ($this) {
            self::EnableHelp => ['0', '1', '2'],
        };
    }
}
