<?php

/**
 * The value types a branding global may carry.
 *
 * A closed set: every key declared by BrandingGlobalKey resolves to exactly one of
 * these, and BrandingConfigFactory parses each one accordingly. TokenJson is listed
 * separately from Text on purpose -- it is the only type whose payload is structured,
 * and locked constraint C1 requires it to be machine-produced token pairs rather than
 * anything a person could type into a box.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Config;

/**
 * Unit enum: the value type is runtime metadata, never persisted, so nothing is backed.
 */
enum BrandingValueType
{
    /** A plain string. Never CSS, JS or HTML -- see locked constraint C1. */
    case Text;

    /** An integer, stored in globals as its decimal string form. */
    case Integer;

    /** A boolean, stored in globals as '1' or '0'. */
    case Flag;

    /** A validated design-token overlay document, produced only by the materialiser. */
    case TokenJson;

    /** An ISO-8601 (RFC 3339 / DATE_ATOM) instant. */
    case Timestamp;

    /**
     * The PHP type of the raw default carried by a key of this value type.
     *
     * TokenJson and Timestamp are both transported as strings; they differ from Text
     * only in how BrandingConfigFactory parses them.
     */
    public function rawPhpType(): string
    {
        return match ($this) {
            self::Text, self::TokenJson, self::Timestamp => 'string',
            self::Integer => 'int',
            self::Flag => 'bool',
        };
    }
}
