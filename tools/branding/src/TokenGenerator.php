<?php

/**
 * Orchestrates the branding token pipeline: two JSON sources, six artefacts.
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
 * Implements `docs/RebrandingPlan.md` §3.7.1: one source of truth feeding the
 * SCSS variables, the CSS custom properties, the typography partial and the
 * SMART style contract, so `docs/rebranding.md` §16.1's "SMART tokens must
 * derive from the same design-token source as the web CSS variables" holds by
 * construction rather than by convention.
 *
 * Everything is produced in memory and returned in a fixed order; nothing is
 * written here, so a validation failure can never leave partial artefacts.
 */
final readonly class TokenGenerator
{
    public const COLOR_TOKENS = 'brand/tokens/thiqa-tokens.json';
    public const TYPOGRAPHY_TOKENS = 'brand/typography/typography-tokens.json';
    public const FONT_FACES = 'brand/typography/thiqa-fonts.scss';

    public function __construct(private string $repoRoot, private string $fontUrlBase)
    {
    }

    /**
     * @return list<GeneratedFile> in a fixed, source-order-independent sequence
     */
    public function generate(): array
    {
        $colorDocument = JsonDocument::fromFile($this->path(self::COLOR_TOKENS), self::COLOR_TOKENS);
        $typographyDocument = JsonDocument::fromFile($this->path(self::TYPOGRAPHY_TOKENS), self::TYPOGRAPHY_TOKENS);
        $ranges = UnicodeRangeIndex::fromScssFile($this->path(self::FONT_FACES), self::FONT_FACES);

        $schema = ColorTokenSchema::create();
        $typography = Typography::fromDocument($typographyDocument, $ranges);

        $palettes = [];
        foreach (Variant::all() as $variant) {
            $palettes[$variant->key()] = ColorPalette::fromDocument($colorDocument, $schema, $variant);
        }

        $colorSources = [self::COLOR_TOKENS];
        $typographySources = [self::TYPOGRAPHY_TOKENS, self::FONT_FACES];

        $scssRenderer = new ScssTokensRenderer($colorSources);
        $cssRenderer = new CssVariablesRenderer(OeVariableMap::create(), $colorSources);
        $typographyRenderer = new TypographyRenderer($this->fontUrlBase, $typographySources);
        $smartRenderer = new SmartStyleTemplateRenderer();

        $files = [];
        foreach (Variant::all() as $variant) {
            $files[] = $scssRenderer->render($palettes[$variant->key()]);
        }
        $files[] = $cssRenderer->render($palettes);
        $files[] = $typographyRenderer->render($typography);
        foreach (Variant::all() as $variant) {
            $files[] = $smartRenderer->render(
                new SmartStyleContract($palettes[$variant->key()], $typography),
                $variant,
            );
        }

        return $files;
    }

    private function path(string $relative): string
    {
        return rtrim($this->repoRoot, "/\\") . '/' . $relative;
    }
}
