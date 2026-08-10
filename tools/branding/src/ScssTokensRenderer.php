<?php

/**
 * Renders `_tokens-light.scss` / `_tokens-dark.scss`.
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
 * Emits one SCSS variable per schema token, grouped and column-aligned. Both
 * variants emit the identical variable set — a variant that omits a token gets
 * it as an alias of another token, never as a missing variable, so a theme
 * partial can reference any token without knowing which variant it compiles in.
 */
final readonly class ScssTokensRenderer
{
    /**
     * @param list<string> $sources
     */
    public function __construct(private array $sources)
    {
    }

    public function render(ColorPalette $palette): GeneratedFile
    {
        $variant = $palette->variant;
        $body = GeneratedHeader::scss(
            sprintf('Thiqa colour tokens — %s variant.', $variant->label()),
            $this->sources,
        );

        $width = 0;
        foreach ($palette->entries() as $entry) {
            $width = max($width, strlen($entry->token->scssVariable()) + 1);
        }

        $group = null;
        foreach ($palette->entries() as $entry) {
            if ($entry->token->group !== $group) {
                $group = $entry->token->group;
                $body .= "\n// " . $group . "\n";
            }

            $declaration = $entry->token->scssVariable() . ':';
            $line = str_pad($declaration, $width + 1) . $entry->color->value . ';';
            if ($entry->aliasOf !== null) {
                $line .= sprintf(
                    ' // alias of %s — %s is not defined for the %s variant',
                    $entry->aliasOf,
                    $entry->token->path,
                    $variant->key(),
                );
            }
            $body .= $line . "\n";
        }

        return new GeneratedFile(sprintf('_tokens-%s.scss', $variant->key()), $body);
    }
}
