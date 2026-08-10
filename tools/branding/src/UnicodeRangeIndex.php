<?php

/**
 * Extracts the authored `unicode-range` subset split per font family.
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
 * `brand/typography/thiqa-fonts.scss` is the authored source for the Latin and
 * Arabic subsets. Rather than duplicating those ranges in generator code, the
 * generator reads them back out, so hand-tuning the subsets in the brand kit
 * flows through on the next run.
 *
 * A family whose blocks disagree on their range is an error: the whole point of
 * the index is that one family means one subset.
 */
final readonly class UnicodeRangeIndex
{
    /**
     * @param array<string, string> $rangesByFamily
     */
    private function __construct(private array $rangesByFamily, private string $origin)
    {
    }

    /**
     * @param string $absolutePath filesystem path actually read
     * @param string $origin       repo-relative label used in error messages
     */
    public static function fromScssFile(string $absolutePath, string $origin): self
    {
        if (!is_file($absolutePath)) {
            throw new GeneratorException(sprintf('Font stylesheet "%s" does not exist.', $origin));
        }

        $scss = file_get_contents($absolutePath);
        if ($scss === false) {
            throw new GeneratorException(sprintf('Font stylesheet "%s" could not be read.', $origin));
        }

        $blocks = [];
        if (preg_match_all('/@font-face\s*\{(?<body>[^}]*)\}/', $scss, $blocks, PREG_SET_ORDER) === false) {
            throw new GeneratorException(sprintf('Font stylesheet "%s" could not be scanned.', $origin));
        }

        $ranges = [];
        foreach ($blocks as $block) {
            $body = $block['body'];
            $family = self::captureDeclaration($body, 'font-family');
            $range = self::captureDeclaration($body, 'unicode-range');
            if ($family === null || $range === null) {
                continue;
            }

            $family = trim($family, " \t'\"");
            if (isset($ranges[$family]) && $ranges[$family] !== $range) {
                throw new GeneratorException(sprintf(
                    'Family "%s" declares conflicting unicode-range values in %s; '
                    . 'every face of a family must share one subset.',
                    $family,
                    $origin,
                ));
            }
            $ranges[$family] = $range;
        }

        if ($ranges === []) {
            throw new GeneratorException(sprintf(
                'No @font-face block with a unicode-range was found in %s.',
                $origin,
            ));
        }

        ksort($ranges, SORT_STRING);

        return new self($ranges, $origin);
    }

    public function forFamily(string $family): string
    {
        if (!isset($this->rangesByFamily[$family])) {
            throw new GeneratorException(sprintf(
                'No unicode-range is authored for family "%s" in %s; add an @font-face block there '
                . 'before referencing the family from typography-tokens.json.',
                $family,
                $this->origin,
            ));
        }

        return $this->rangesByFamily[$family];
    }

    /**
     * @return list<string>
     */
    public function families(): array
    {
        return array_keys($this->rangesByFamily);
    }

    private static function captureDeclaration(string $body, string $property): ?string
    {
        $matches = [];
        $pattern = '/(?:^|;|\s)' . preg_quote($property, '/') . '\s*:\s*(?<value>[^;]+);/';
        if (preg_match($pattern, $body, $matches) !== 1) {
            return null;
        }

        return trim($matches['value']);
    }
}
