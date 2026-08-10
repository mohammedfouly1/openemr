<?php

/**
 * The resolved colour palette for a single theme variant.
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
 * Parse, do not validate: the whole variant sub-tree is turned into validated
 * `HexColor` values up front, in schema order. Every renderer downstream reads
 * from here, which is what guarantees the SCSS, the CSS custom properties and
 * the SMART contract can never drift apart.
 */
final readonly class ColorPalette
{
    /**
     * @param list<PaletteEntry>         $entries indexed positionally, in schema order
     * @param array<string, PaletteEntry> $byPath  same entries keyed by token path
     */
    private function __construct(
        public Variant $variant,
        private array $entries,
        private array $byPath,
    ) {
    }

    public static function fromDocument(JsonDocument $document, ColorTokenSchema $schema, Variant $variant): self
    {
        $schema->assertCovers($document, $variant);

        $entries = [];
        $byPath = [];
        foreach ($schema->tokens() as $token) {
            $absolute = $variant->key() . '.' . $token->path;
            $raw = $document->optionalString($absolute);
            $aliasOf = null;

            if ($raw === null) {
                if ($token->fallbackPath === null) {
                    throw new GeneratorException(sprintf(
                        'Required token "%s" is missing from %s and declares no fallback.',
                        $absolute,
                        $document->origin(),
                    ));
                }
                $aliasOf = $token->fallbackPath;
                $raw = $document->requireString($variant->key() . '.' . $token->fallbackPath);
            }

            $entry = new PaletteEntry($token, HexColor::fromString($raw, $absolute), $aliasOf);
            $entries[] = $entry;
            $byPath[$token->path] = $entry;
        }

        return new self($variant, $entries, $byPath);
    }

    /**
     * @return list<PaletteEntry>
     */
    public function entries(): array
    {
        return $this->entries;
    }

    public function entry(string $path): PaletteEntry
    {
        if (!isset($this->byPath[$path])) {
            throw new GeneratorException(sprintf(
                'Colour token "%s" was requested for the %s variant but is not in the resolved palette.',
                $path,
                $this->variant->key(),
            ));
        }

        return $this->byPath[$path];
    }

    public function color(string $path): HexColor
    {
        return $this->entry($path)->color;
    }
}
