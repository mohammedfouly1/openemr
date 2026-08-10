<?php

/**
 * A validated six-digit hexadecimal colour.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Token;

use InvalidArgumentException;
use Stringable;

/**
 * Domain primitive for a colour token value (plan §3.4.1).
 *
 * Nothing downstream re-validates: once a ColorValue exists, its `value` is known
 * to be exactly seven characters of the form `#RRGGBB` with uppercase hex digits.
 * That is what allows CssVariableRenderer to be structurally incapable of emitting
 * a CSS value that escapes its declaration context (plan §3.9).
 */
final readonly class ColorValue implements Stringable
{
    /**
     * The only shape a colour token may take.
     *
     * `\A` and `\z` are used deliberately in place of `^` and `$`: PCRE's `$`
     * also matches immediately before a trailing newline, so `^#[0-9A-Fa-f]{6}$`
     * would accept "#FFFFFF\n" and let a newline reach the stylesheet. `\z`
     * anchors at the true end of the subject and closes that injection route.
     */
    private const PATTERN = '/\A#[0-9A-Fa-f]{6}\z/';

    /** Length of a well-formed literal; anything longer is rejected before matching. */
    public const MAX_LENGTH = 7;

    /** Normalised `#RRGGBB` with uppercase hex digits. */
    public string $value;

    public function __construct(string $value)
    {
        if (strlen($value) !== self::MAX_LENGTH || preg_match(self::PATTERN, $value) !== 1) {
            // The rejected text is never echoed back: it is untrusted input and this
            // message may reach a log or an error page.
            throw new InvalidArgumentException('Colour token must match #RRGGBB.');
        }

        $this->value = strtoupper($value);
    }

    /** Null instead of an exception, for boundary code that reports rather than fails. */
    public static function tryFrom(string $value): ?self
    {
        if (strlen($value) !== self::MAX_LENGTH || preg_match(self::PATTERN, $value) !== 1) {
            return null;
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
