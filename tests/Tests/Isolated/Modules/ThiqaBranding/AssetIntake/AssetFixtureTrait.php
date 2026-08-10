<?php

/**
 * Builds the hostile and benign image fixtures the asset intake tests need.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\AssetIntake;

/**
 * Every fixture is generated, never committed. Committing a file whose whole purpose is
 * to contain `<script>` inside an image invites a scanner or a build step to quarantine
 * the repository, and a generated fixture also documents precisely which byte makes it
 * hostile.
 */
trait AssetFixtureTrait
{
    private string $fixtureDirectory = '';

    /** Absolute path of the repository root, from this file's known depth. */
    private static function repositoryRoot(): string
    {
        return dirname(__DIR__, 6);
    }

    private function makeFixtureDirectory(): void
    {
        $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'oe-thiqa-intake-' . bin2hex(random_bytes(6));
        if (!mkdir($base, 0o755, true) && !is_dir($base)) {
            self::fail('Could not create the fixture directory.');
        }

        $this->fixtureDirectory = $base;
    }

    private function removeFixtureDirectory(): void
    {
        if ($this->fixtureDirectory === '' || !is_dir($this->fixtureDirectory)) {
            return;
        }

        $this->removeTree($this->fixtureDirectory);
        $this->fixtureDirectory = '';
    }

    private function removeTree(string $directory): void
    {
        $entries = scandir($directory);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                $this->removeTree($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }

    /** Write $contents under the fixture directory and return the absolute path. */
    private function writeFixture(string $name, string $contents): string
    {
        $path = $this->fixtureDirectory . DIRECTORY_SEPARATOR . $name;
        if (file_put_contents($path, $contents) !== strlen($contents)) {
            self::fail('Could not write the fixture ' . $name . '.');
        }

        return $path;
    }

    /** A structurally complete, CRC-correct 8-bit RGBA PNG of the requested size. */
    private static function pngBytes(int $width, int $height): string
    {
        $raw = '';
        for ($row = 0; $row < $height; $row++) {
            // Filter type 0, then opaque white pixels.
            $raw .= "\x00" . str_repeat("\xFF\xFF\xFF\xFF", $width);
        }

        $compressed = gzcompress($raw, 9);
        if ($compressed === false) {
            self::fail('Could not compress the PNG fixture body.');
        }

        // Width, height, bit depth 8, colour type 6 (RGBA), deflate, adaptive, no interlace.
        $header = pack('NN', $width, $height) . "\x08\x06\x00\x00\x00";

        return "\x89PNG\r\n\x1A\n"
            . self::pngChunk('IHDR', $header)
            . self::pngChunk('IDAT', $compressed)
            . self::pngChunk('IEND', '');
    }

    private static function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
    }

    /**
     * A GIF89a whose block chain is structurally complete.
     *
     * The LZW body is a placeholder: the reader under test walks sub-block lengths and
     * never decodes pixels, and the real certified GIFs cover the decode-shaped cases.
     */
    private static function gifBytes(int $width, int $height): string
    {
        return 'GIF89a'
            . pack('vv', $width, $height)
            // Global colour table present, 2 entries.
            . "\x80\x00\x00"
            . "\x00\x00\x00\xFF\xFF\xFF"
            // Image descriptor at 0,0 with no local colour table.
            . "\x2C" . pack('vvvv', 0, 0, $width, $height) . "\x00"
            // LZW minimum code size, one data sub-block, sub-block terminator.
            . "\x02" . "\x02\x44\x01" . "\x00"
            . "\x3B";
    }

    /** A one-entry ICO whose body is an embedded PNG of the same size. */
    private static function icoBytes(int $edge): string
    {
        $png = self::pngBytes($edge, $edge);
        $stored = $edge >= 256 ? 0 : $edge;

        $entry = chr($stored) . chr($stored) . "\x00\x00" . pack('vv', 1, 32)
            . pack('VV', strlen($png), 22);

        return "\x00\x00\x01\x00" . pack('v', 1) . $entry . $png;
    }

    /** A minimal path-only SVG, optionally with extra markup spliced inside the root. */
    private static function svgBytes(int $width, int $height, string $injected = ''): string
    {
        return '<?xml version="1.0" encoding="utf-8" ?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"'
            . ' width="' . $width . '" height="' . $height . '"'
            . ' viewBox="0 0 ' . $width . ' ' . $height . '">'
            . $injected
            . '<path d="M0 0 L10 10 Z" fill="#123456"/>'
            . '</svg>';
    }
}
