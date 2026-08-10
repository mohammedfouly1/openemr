<?php

/**
 * Renders `_css-variables.scss` — the `:root` custom-property surface.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * OpenEMR compiles light and dark as separate stylesheets rather than toggling
 * an attribute at runtime, so the file exposes one mixin per variant and
 * applies the light one to `:root` as the default. A dark entry file overrides
 * with `:root { @include thiqa-css-variables-dark; }`.
 *
 * Every block emits the `--thiqa-*` tokens first and then the thirteen
 * `--oe-*` names existing OpenEMR selectors already read, defined as
 * `var(--thiqa-*)` aliases so the two can never disagree.
 */
final readonly class CssVariablesRenderer
{
    private const INDENT = '    ';

    /**
     * @param list<string> $sources
     */
    public function __construct(private OeVariableMap $oeMap, private array $sources)
    {
    }

    /**
     * @param array<string, ColorPalette> $palettes keyed by variant key
     */
    public function render(array $palettes): GeneratedFile
    {
        $body = GeneratedHeader::scss(
            'Thiqa CSS custom properties, with --oe-* compatibility aliases.',
            $this->sources,
        );

        $body .= "\n" . implode("\n", [
            '// Usage: importing this partial is enough — the :root rule at the bottom',
            '// applies a whole variant. Light is the default; a dark entry file selects',
            '// the other one before importing:',
            '//',
            "//   \$thiqa-css-variables-variant: 'dark';",
            '//   @import "css-variables";',
            '//',
            '// Selecting rather than always emitting both blocks keeps the compiled dark',
            '// stylesheet free of a dead light block it would only override.',
        ]) . "\n";

        foreach (Variant::all() as $variant) {
            $palette = $palettes[$variant->key()] ?? null;
            if (!$palette instanceof ColorPalette) {
                throw new GeneratorException(sprintf(
                    'No resolved palette supplied for the %s variant.',
                    $variant->key(),
                ));
            }
            $body .= "\n" . $this->renderMixin($palette);
        }

        $body .= "\n" . $this->renderRootRule();

        return new GeneratedFile('_css-variables.scss', $body);
    }

    /**
     * The `@error` branch matters: a typo in the variant name must stop the
     * build rather than silently compile a stylesheet with no tokens at all.
     */
    private function renderRootRule(): string
    {
        $branches = [];
        foreach (Variant::all() as $variant) {
            $condition = sprintf(
                '%s $thiqa-css-variables-variant == \'%s\' {',
                $branches === [] ? '@if' : '} @else if',
                $variant->key(),
            );
            $branches[] = self::INDENT . $condition;
            $branches[] = str_repeat(self::INDENT, 2)
                . sprintf('@include thiqa-css-variables-%s;', $variant->key());
        }

        $branches[] = self::INDENT . '} @else {';
        $branches[] = str_repeat(self::INDENT, 2)
            . '@error "Unknown $thiqa-css-variables-variant \'#{$thiqa-css-variables-variant}\'.";';
        $branches[] = self::INDENT . '}';

        $lines = array_merge(
            [
                '// Pick the variant before importing this partial; light is the default.',
                '$thiqa-css-variables-variant: \'light\' !default;',
                '',
                ':root {',
            ],
            $branches,
            ['}'],
        );

        return implode("\n", $lines) . "\n";
    }

    private function renderMixin(ColorPalette $palette): string
    {
        $variant = $palette->variant;
        $lines = [sprintf('@mixin thiqa-css-variables-%s {', $variant->key())];

        $width = 0;
        foreach ($palette->entries() as $entry) {
            $width = max($width, strlen($entry->token->cssVariable()) + 1);
        }
        foreach ($this->oeMap->variables() as $oeVariable) {
            $width = max($width, strlen($oeVariable->name) + 1);
        }

        $group = null;
        foreach ($palette->entries() as $entry) {
            if ($entry->token->group !== $group) {
                if ($group !== null) {
                    $lines[] = '';
                }
                $group = $entry->token->group;
                $lines[] = self::INDENT . '/* ' . $group . ' */';
            }
            $lines[] = self::INDENT
                . str_pad($entry->token->cssVariable() . ':', $width + 1)
                . $entry->color->value . ';';
        }

        $lines[] = '';
        $lines[] = self::INDENT . '/* ------------------------------------------------------------------';
        $lines[] = self::INDENT . ' * OpenEMR compatibility aliases. Existing selectors read these names;';
        $lines[] = self::INDENT . ' * each one resolves to a Thiqa token above, never to a literal colour.';
        $lines[] = self::INDENT . ' * ------------------------------------------------------------------ */';

        $group = null;
        foreach ($this->oeMap->variables() as $oeVariable) {
            if ($oeVariable->group !== $group) {
                $group = $oeVariable->group;
                $lines[] = '';
                $lines[] = self::INDENT . '/* ' . $group . ' */';
            }
            $target = $palette->entry($oeVariable->pathFor($variant))->token->cssVariable();
            $line = self::INDENT
                . str_pad($oeVariable->name . ':', $width + 1)
                . 'var(' . $target . ');';
            if ($oeVariable->note !== null) {
                $line .= ' /* ' . $oeVariable->note . ' */';
            }
            $lines[] = $line;
        }

        $lines[] = '}';

        return implode("\n", $lines) . "\n";
    }
}
