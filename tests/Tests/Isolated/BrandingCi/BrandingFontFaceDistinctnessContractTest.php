<?php

/**
 * Regression contract: a declared font weight must be backed by a face that can render it.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Finding S2-P1-20 observed that `Inter-Regular`, `-Medium`, `-SemiBold` and `-Bold` are four
 * byte-identical files and concluded that `font-weight: 500/600/700` therefore all resolve
 * identically — "the Latin surface has no real weight axis" — and that RB-22's `FIXED` was
 * false.
 *
 * **The observation is right and the conclusion is wrong.** Inter ships as a *variable* face:
 * decoding the WOFF2 table directory finds `fvar`, `gvar` and `HVAR`, which IBM Plex Sans
 * Arabic (17 tables, no `fvar`) does not carry. One variable file declared with a
 * `font-weight: <min> <max>` range renders every weight in that range, so four names for one
 * variable file is redundancy, not a rendering defect. That is exactly what RB-22 fixed and
 * what the shipped artefacts do today: one `@font-face` for Inter at `font-weight: 400 700`,
 * four separate faces for static IBM Plex.
 *
 * So the useful guard is not "the files must differ" — that would be wrong for a variable
 * family — but the conditional the manifest could never express:
 *
 *   a family may back several declared weights with one file **only if that file is
 *   variable**; a static family must have a distinct, non-duplicate face per declared weight.
 *
 * Nothing checked that before. `SHA256SUMS` verifies each file against its own recorded hash,
 * so it happily passes four identical files under four names — for a static family that would
 * be the real defect S2-P1-20 described, silently shipped.
 */
#[Group('isolated')]
final class BrandingFontFaceDistinctnessContractTest extends TestCase
{
    private const TOKENS = 'brand/typography/typography-tokens.json';

    /** WOFF2's table directory encodes known tags as indices into this fixed list. */
    private const KNOWN_TAGS = [
        'cmap', 'head', 'hhea', 'hmtx', 'maxp', 'name', 'OS/2', 'post', 'cvt ', 'fpgm',
        'glyf', 'loca', 'prep', 'CFF ', 'VORG', 'EBDT', 'EBLC', 'gasp', 'hdmx', 'kern',
        'LTSH', 'PCLT', 'VDMX', 'vhea', 'vmtx', 'BASE', 'GDEF', 'GPOS', 'GSUB', 'EBSC',
        'JSTF', 'MATH', 'CBDT', 'CBLC', 'COLR', 'CPAL', 'SVG ', 'sbix', 'acnt', 'avar',
        'bdat', 'bloc', 'bsln', 'cvar', 'fdsc', 'feat', 'fmtx', 'fvar', 'gvar', 'hsty',
        'just', 'lcar', 'mort', 'morx', 'opbd', 'prop', 'trak', 'Zapf', 'Silf', 'Glat',
        'Gloc', 'Feat', 'Sill',
    ];

    /**
     * The three redundant Inter binaries: byte-identical to the variable face, shipped, and
     * referenced by nothing. Recorded rather than deleted — they carry approved-asset IDs in
     * the 107-entry brand inventory, so removing them is an asset-governance change, not a
     * cleanup. Pinning them here means a weight silently re-pointed at one is a test failure.
     */
    private const UNREFERENCED_DUPLICATES = [
        'brand/typography/fonts/Inter-Medium.woff2',
        'brand/typography/fonts/Inter-SemiBold.woff2',
        'brand/typography/fonts/Inter-Bold.woff2',
    ];

    private const CANONICAL_VARIABLE_FACE = 'brand/typography/fonts/Inter-Regular.woff2';

    public function testEveryDeclaredFaceFileExists(): void
    {
        foreach ($this->declaredFiles() as $entry) {
            self::assertFileExists($this->path($entry['path']));
        }
    }

    /**
     * The contract S2-P1-20 should have been asking for.
     */
    public function testASharedFaceIsAllowedOnlyWhenItIsVariable(): void
    {
        foreach ($this->byFamily() as $family => $weights) {
            $paths = array_values(array_unique(array_values($weights)));

            if (count($paths) === count($weights)) {
                continue;
            }

            foreach ($paths as $path) {
                self::assertTrue(
                    $this->isVariable($this->path($path)),
                    sprintf(
                        '%s backs %d declared weights with %s, which carries no fvar table. A static '
                        . 'face cannot render a weight range, so those weights would all render the same.',
                        $family,
                        count($weights),
                        basename($path),
                    ),
                );
            }
        }
    }

    /**
     * The other half: two *different* filenames inside one family holding identical bytes.
     * That is four-names-one-face by another route, and the manifest cannot see it because
     * each file still matches its own recorded hash.
     */
    public function testNoFamilyDeclaresTwoDistinctPathsWithIdenticalBytes(): void
    {
        foreach ($this->byFamily() as $family => $weights) {
            $seen = [];

            foreach (array_unique(array_values($weights)) as $path) {
                $hash = hash_file('sha256', $this->path($path));
                self::assertIsString($hash);

                self::assertArrayNotHasKey(
                    $hash,
                    $seen,
                    sprintf(
                        '%s declares %s and %s as separate faces, but they are byte-identical.',
                        $family,
                        basename($seen[$hash] ?? ''),
                        basename($path),
                    ),
                );

                $seen[$hash] = $path;
            }
        }
    }

    /**
     * Positive control on the detector itself. If `isVariable()` ever returned true for
     * everything, the shared-face assertion above would pass vacuously, so the known-static
     * family has to keep reading as static.
     */
    public function testTheVariableFontDetectorSeparatesTheTwoShippedFamilies(): void
    {
        self::assertTrue(
            $this->isVariable($this->path(self::CANONICAL_VARIABLE_FACE)),
            'Inter must read as a variable face; the whole shared-file design depends on it.',
        );

        self::assertFalse(
            $this->isVariable($this->path('brand/typography/fonts/IBMPlexSansArabic-Regular.woff2')),
            'IBM Plex Sans Arabic must read as static; if not, the detector is not discriminating.',
        );
    }

    /**
     * The residue S2-P1-20 correctly spotted, stated exactly: still shipped, still identical,
     * still referenced by nothing.
     */
    public function testTheKnownRedundantBinariesRemainUnreferenced(): void
    {
        $referenced = array_map(
            static fn (array $entry): string => $entry['path'],
            $this->declaredFiles(),
        );

        $canonical = hash_file('sha256', $this->path(self::CANONICAL_VARIABLE_FACE));

        foreach (self::UNREFERENCED_DUPLICATES as $duplicate) {
            self::assertFileExists($this->path($duplicate));
            self::assertSame(
                $canonical,
                hash_file('sha256', $this->path($duplicate)),
                sprintf('%s is no longer a copy of the variable face; re-classify it.', basename($duplicate)),
            );
            self::assertNotContains(
                $duplicate,
                $referenced,
                sprintf(
                    '%s is now referenced by the typography contract. It is a duplicate of the '
                    . 'variable face, so pointing a weight at it re-creates the redundancy RB-22 removed.',
                    basename($duplicate),
                ),
            );
        }
    }

    // ---------------------------------------------------------------------------- helpers

    /**
     * @return list<array{path: string, family: string, weight: int}>
     */
    private function declaredFiles(): array
    {
        $document = json_decode($this->read(self::TOKENS), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        $files = $document['files'] ?? null;
        self::assertIsArray($files);
        self::assertNotSame([], $files);

        $declared = [];
        foreach ($files as $entry) {
            self::assertIsArray($entry);
            self::assertIsString($entry['path'] ?? null);
            self::assertIsString($entry['family'] ?? null);
            self::assertIsInt($entry['weight'] ?? null);

            $declared[] = [
                'path' => $entry['path'],
                'family' => $entry['family'],
                'weight' => $entry['weight'],
            ];
        }

        return $declared;
    }

    /**
     * @return array<string, array<int, string>> family => weight => path
     */
    private function byFamily(): array
    {
        $families = [];
        foreach ($this->declaredFiles() as $entry) {
            $families[$entry['family']][$entry['weight']] = $entry['path'];
        }

        return $families;
    }

    /** True when the face carries an `fvar` table, i.e. it can render a weight range. */
    private function isVariable(string $file): bool
    {
        return in_array('fvar', $this->tableTags($file), true);
    }

    /**
     * @return list<string>
     */
    private function tableTags(string $file): array
    {
        $data = file_get_contents($file);
        self::assertIsString($data);

        return str_starts_with($data, 'wOF2')
            ? $this->woff2TableTags($data)
            : $this->sfntTableTags($data);
    }

    /**
     * WOFF2 stores its directory as flag bytes plus UIntBase128 lengths, so the entries must
     * be walked in order; there is no fixed-width record to index into.
     *
     * @return list<string>
     */
    private function woff2TableTags(string $data): array
    {
        $unpacked = unpack('n', substr($data, 12, 2));
        self::assertIsArray($unpacked);
        $numTables = $unpacked[1];

        $offset = 48;
        $tags = [];

        for ($i = 0; $i < $numTables; $i++) {
            $flags = ord($data[$offset++]);
            $index = $flags & 0x3F;

            if ($index === 0x3F) {
                $tag = substr($data, $offset, 4);
                $offset += 4;
            } else {
                $tag = self::KNOWN_TAGS[$index];
            }

            $tags[] = $tag;

            $this->readBase128($data, $offset);

            // glyf and loca use transform 0 as their null transform; every other table uses 3.
            // A non-null transform adds a second length field.
            $nullTransform = ($tag === 'glyf' || $tag === 'loca') ? 0 : 3;
            if ((($flags >> 6) & 0x03) !== $nullTransform) {
                $this->readBase128($data, $offset);
            }
        }

        return $tags;
    }

    /**
     * Plain sfnt (`.ttf`/`.otf`): a fixed 16-byte record per table after a 12-byte header.
     *
     * @return list<string>
     */
    private function sfntTableTags(string $data): array
    {
        $unpacked = unpack('n', substr($data, 4, 2));
        self::assertIsArray($unpacked);
        $numTables = $unpacked[1];

        $tags = [];
        for ($i = 0; $i < $numTables; $i++) {
            $tags[] = substr($data, 12 + ($i * 16), 4);
        }

        return $tags;
    }

    private function readBase128(string $data, int &$offset): void
    {
        for ($i = 0; $i < 5; $i++) {
            $byte = ord($data[$offset++]);
            if (($byte & 0x80) === 0) {
                return;
            }
        }

        throw new RuntimeException('Malformed UIntBase128 in the WOFF2 table directory.');
    }

    private function path(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . $relative;
    }

    private function read(string $relative): string
    {
        $contents = file_get_contents($this->path($relative));
        self::assertIsString($contents);

        return $contents;
    }
}
