<?php

/**
 * Renders `_typography.scss` — family, weight and scale variables plus @font-face.
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
 * Variable names match the ones already authored in
 * `brand/typography/thiqa-fonts.scss` (`$thiqa-font-family-latin`,
 * `$thiqa-font-weight-semibold`, …) so this file supersedes that one without
 * breaking a consumer that already imports it.
 */
final readonly class TypographyRenderer
{
    private const INDENT = '    ';

    /**
     * @param string       $fontUrlBase URL prefix the compiled stylesheet resolves fonts against
     * @param list<string> $sources
     */
    public function __construct(private string $fontUrlBase, private array $sources)
    {
    }

    public function render(Typography $typography): GeneratedFile
    {
        $body = GeneratedHeader::scss(
            'Thiqa typography — families, weights, type scale and @font-face blocks.',
            $this->sources,
        );

        $body .= "\n// Fonts are licensed under " . $typography->license . ".\n";
        $body .= '// Faces are resolved against "' . $this->fontUrlBase . '"; pass --font-url-base to change it.' . "\n";

        $body .= $this->renderFontFaces($typography);
        $body .= $this->renderFamilies($typography);
        $body .= $this->renderWeights($typography);
        $body .= $this->renderScale($typography);
        $body .= $this->renderScaleMap($typography);
        $body .= $this->renderNumerics($typography);

        return new GeneratedFile('_typography.scss', $body);
    }

    private function renderFontFaces(Typography $typography): string
    {
        $out = "\n// " . str_repeat('=', 76) . "\n// Font faces\n// " . str_repeat('=', 76) . "\n";

        foreach ($typography->fontFaces() as $face) {
            $out .= "\n@font-face {\n"
                . self::INDENT . "font-family: '" . $face->family . "';\n"
                . self::INDENT . "font-style: normal;\n"
                . self::INDENT . 'font-weight: ' . $face->weight . ";\n"
                . self::INDENT . "font-display: swap;\n"
                . self::INDENT . "src: url('" . $this->fontUrl($face->fileName) . "') format('woff2');\n"
                . self::INDENT . 'unicode-range: ' . $face->unicodeRange . ";\n"
                . "}\n";
        }

        return $out;
    }

    private function renderFamilies(Typography $typography): string
    {
        $declarations = [];
        foreach ($typography->familyStacks() as $suffix => $stack) {
            $declarations[$this->familyVariable($suffix)] = $stack;
        }
        foreach ($typography->familyNames() as $suffix => $name) {
            $declarations['$thiqa-font-' . $suffix] = "'" . $name . "'";
        }

        return "\n// " . str_repeat('=', 76) . "\n// Families\n// " . str_repeat('=', 76) . "\n\n"
            . $this->alignedDeclarations($declarations);
    }

    private function renderWeights(Typography $typography): string
    {
        $declarations = [];
        foreach ($typography->weights() as $suffix => $weight) {
            $declarations['$thiqa-font-weight-' . $suffix] = (string) $weight;
        }

        return "\n// Weights\n\n" . $this->alignedDeclarations($declarations);
    }

    private function renderScale(Typography $typography): string
    {
        $declarations = [];
        foreach ($typography->scale() as $step) {
            $declarations['$thiqa-font-size-' . $step->slug()] = $step->size . 'px';
            $declarations['$thiqa-line-height-' . $step->slug()] = $step->lineHeight . 'px';
            $declarations['$thiqa-font-weight-' . $step->slug()] = (string) $step->weight;
        }

        return "\n// Type scale\n\n" . $this->alignedDeclarations($declarations);
    }

    private function renderScaleMap(Typography $typography): string
    {
        $out = "\n// Type scale as a map, for programmatic consumers.\n\$thiqa-type-scale: (\n";
        foreach ($typography->scale() as $step) {
            $out .= self::INDENT . sprintf(
                "'%s': (size: %dpx, line-height: %dpx, weight: %d),\n",
                $step->slug(),
                $step->size,
                $step->lineHeight,
                $step->weight,
            );
        }

        return $out . ");\n";
    }

    private function renderNumerics(Typography $typography): string
    {
        return "\n// Tabular lining figures for identifiers, money, dates and table cells.\n"
            . '$thiqa-font-feature-numeric: ' . $typography->numericFeatureSettings . ";\n";
    }

    /**
     * @param array<string, string> $declarations
     */
    private function alignedDeclarations(array $declarations): string
    {
        $width = 0;
        foreach (array_keys($declarations) as $name) {
            $width = max($width, strlen($name) + 1);
        }

        $out = '';
        foreach ($declarations as $name => $value) {
            $out .= str_pad($name . ':', $width + 1) . $value . ";\n";
        }

        return $out;
    }

    private function familyVariable(string $suffix): string
    {
        return $suffix === '' ? '$thiqa-font-family' : '$thiqa-font-family-' . $suffix;
    }

    private function fontUrl(string $fileName): string
    {
        return rtrim($this->fontUrlBase, '/') . '/' . $fileName;
    }
}
