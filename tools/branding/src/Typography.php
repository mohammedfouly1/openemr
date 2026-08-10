<?php

/**
 * The resolved typography system: families, weights, scale and font faces.
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
 * Built from `brand/typography/typography-tokens.json` for the values and
 * `brand/typography/thiqa-fonts.scss` for the authored subset ranges.
 *
 * Families and weights are read through fixed ordered lists so emission order
 * never depends on JSON key order; the scale and the font-file list are read in
 * document order because they are open sets the brand kit may extend.
 */
final readonly class Typography
{
    /**
     * SCSS variable suffix => token path. Ordered; drives emission order.
     */
    private const FAMILY_VARIABLES = [
        '' => 'family.stack',
        'latin' => 'family.stackLatin',
        'arabic' => 'family.stackArabic',
        'arabic-pdf' => 'family.stackArabicPdf',
    ];

    /**
     * SCSS variable suffix => token path for the bare family names.
     */
    private const FAMILY_NAME_VARIABLES = [
        'name-latin' => 'family.latin',
        'name-arabic' => 'family.arabic',
        'name-arabic-pdf' => 'family.arabicPdf',
    ];

    /**
     * SCSS variable suffix => token path. Ordered lightest to heaviest.
     */
    private const WEIGHT_VARIABLES = [
        'regular' => 'weight.regular',
        'medium' => 'weight.medium',
        'semibold' => 'weight.semibold',
        'bold' => 'weight.bold',
    ];

    /**
     * @param array<string, string> $familyStacks suffix => stack, in emission order
     * @param array<string, string> $familyNames  suffix => family name, in emission order
     * @param array<string, int>    $weights      suffix => numeric weight, in emission order
     * @param list<TypeScaleStep>   $scale
     * @param list<FontFace>        $fontFaces
     */
    private function __construct(
        private array $familyStacks,
        private array $familyNames,
        private array $weights,
        private array $scale,
        private array $fontFaces,
        public string $numericFeatureSettings,
        public string $license,
    ) {
    }

    public static function fromDocument(JsonDocument $document, UnicodeRangeIndex $ranges): self
    {
        $familyStacks = [];
        foreach (self::FAMILY_VARIABLES as $suffix => $path) {
            $familyStacks[$suffix] = $document->requireString($path);
        }

        $familyNames = [];
        foreach (self::FAMILY_NAME_VARIABLES as $suffix => $path) {
            $familyNames[$suffix] = $document->requireString($path);
        }

        $weights = [];
        foreach (self::WEIGHT_VARIABLES as $suffix => $path) {
            $weights[$suffix] = $document->requireInt($path);
        }

        $scale = [];
        foreach ($document->requireObjectKeys('scale') as $key) {
            $scale[] = new TypeScaleStep(
                $key,
                $document->requireInt('scale.' . $key . '.size'),
                $document->requireInt('scale.' . $key . '.lineHeight'),
                $document->requireInt('scale.' . $key . '.weight'),
            );
        }
        if ($scale === []) {
            throw new GeneratorException(sprintf('No type scale steps found in %s.', $document->origin()));
        }
        // Scale steps and named weights share the $thiqa-font-weight-* namespace.
        foreach ($scale as $step) {
            if (isset($weights[$step->slug()])) {
                throw new GeneratorException(sprintf(
                    'Type scale step "%s" collides with the named weight of the same slug; '
                    . 'rename one of them in %s.',
                    $step->key,
                    $document->origin(),
                ));
            }
        }

        $fontFaces = [];
        $fileCount = $document->requireListCount('files');
        for ($index = 0; $index < $fileCount; $index++) {
            $prefix = 'files.' . $index;
            if ($document->optionalString($prefix . '.usage') === 'pdf') {
                // PDF faces are embedded by the PDF engines, not by CSS.
                continue;
            }
            $family = $document->requireString($prefix . '.family');
            $path = $document->requireString($prefix . '.path');
            $fontFaces[] = new FontFace(
                $family,
                $document->requireInt($prefix . '.weight'),
                basename($path),
                $ranges->forFamily($family),
            );
        }
        if ($fontFaces === []) {
            throw new GeneratorException(sprintf('No web font files declared in %s.', $document->origin()));
        }

        return new self(
            $familyStacks,
            $familyNames,
            $weights,
            $scale,
            $fontFaces,
            $document->requireString('numeric.featureSettings'),
            $document->requireString('family.license'),
        );
    }

    /**
     * @return array<string, string>
     */
    public function familyStacks(): array
    {
        return $this->familyStacks;
    }

    /**
     * @return array<string, string>
     */
    public function familyNames(): array
    {
        return $this->familyNames;
    }

    /**
     * @return array<string, int>
     */
    public function weights(): array
    {
        return $this->weights;
    }

    /**
     * @return list<TypeScaleStep>
     */
    public function scale(): array
    {
        return $this->scale;
    }

    /**
     * @return list<FontFace>
     */
    public function fontFaces(): array
    {
        return $this->fontFaces;
    }

    /**
     * The base UI body step, which the SMART contract's `dim_font_size` uses.
     */
    public function step(string $key): TypeScaleStep
    {
        foreach ($this->scale as $step) {
            if ($step->key === $key) {
                return $step;
            }
        }

        throw new GeneratorException(sprintf('Type scale step "%s" is not defined in the typography tokens.', $key));
    }
}
