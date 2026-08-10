<?php

/**
 * One design token: an allowlisted key bound to a validated colour.
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
 * The unit the branding layer passes around instead of a string pair.
 *
 * Both halves are already parsed, so a DesignToken cannot represent an unknown
 * property name or an unsafe value (plan §3.9). Downstream code never re-validates.
 */
final readonly class DesignToken
{
    public function __construct(
        public TokenKey $key,
        public ColorValue $value,
    ) {
    }

    /** Convenience factory for the parser; throws on a malformed literal. */
    public static function fromLiteral(TokenKey $key, string $value): self
    {
        return new self($key, new ColorValue($value));
    }

    /** Wither: value objects are replaced, never mutated. */
    public function withValue(ColorValue $value): self
    {
        return new self($this->key, $value);
    }

    public function equals(self $other): bool
    {
        return $this->key === $other->key && $this->value->equals($other->value);
    }
}
