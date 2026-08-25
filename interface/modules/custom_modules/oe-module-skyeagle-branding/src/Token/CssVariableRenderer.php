<?php

/**
 * Renders a TokenSet as CSS custom property declarations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Token;

/**
 * The narrowest possible waist between the branding layer and a stylesheet.
 *
 * There is no string passthrough: the only public parameter is a TokenSet, whose contents
 * are TokenKey/ColorValue pairs. The property name comes from TokenKey::cssVariableName(),
 * a match over literals; the value comes from ColorValue, which is anchored to
 * `\A#[0-9A-Fa-f]{6}\z`. Nothing is concatenated from caller input, there is no selector
 * parameter, and no brace is ever emitted — so there is no context to escape from.
 *
 * That combination is what makes "a tenant cannot inject CSS" a property of the type
 * system rather than a rule someone has to remember (plan §3.9, locked Invariant 9).
 *
 * Every produced line matches /\A--[a-z0-9-]+: \#[0-9A-F]{6};\z/.
 */
final readonly class CssVariableRenderer
{
    private const LINE_SEPARATOR = "\n";

    /**
     * One `--name: #RRGGBB;` declaration per token, in TokenKey declaration order.
     *
     * @return list<string>
     */
    public function renderDeclarations(TokenSet $tokens): array
    {
        return array_map(
            static fn (DesignToken $token): string => $token->key->cssVariableName()
                . ': '
                . $token->value->value
                . ';',
            $tokens->all(),
        );
    }

    /**
     * The declarations joined by newlines, with no trailing newline and no wrapper.
     *
     * Deliberately not a rule block: emitting a selector would mean emitting braces,
     * and a renderer that can produce `{` and `}` is a renderer that can, in principle,
     * be steered out of the declaration context. Whoever needs a `:root { ... }` block
     * supplies the selector from its own closed set and wraps this output.
     */
    public function render(TokenSet $tokens): string
    {
        return implode(self::LINE_SEPARATOR, $this->renderDeclarations($tokens));
    }
}
