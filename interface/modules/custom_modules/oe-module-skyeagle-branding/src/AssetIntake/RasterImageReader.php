<?php

/**
 * Structural parser for the three permitted raster containers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\AssetIntake;

use DomainException;

/**
 * Reads dimensions *and* proves the file ends exactly where the format says it ends.
 *
 * The second half is the point. `getimagesize()` answers "what are the dimensions" by
 * reading a header and stopping; it says nothing about the 4 KB of PHP appended after
 * the IEND chunk. Every parser here walks the whole container to its terminator and
 * refuses the file if a single byte follows it, which is what makes the classic
 * image/script polyglot unrepresentable.
 *
 * Nothing here decodes pixels. No image library is invoked on tenant bytes at all, so
 * the decoder CVE surface (libpng, giflib, ImageMagick delegates) is simply not reached.
 */
final class RasterImageReader
{
    /**
     * Interpreter and markup openers that must never appear inside image bytes.
     *
     * Each is at least five bytes, so the chance of one occurring by accident in
     * compressed image data is around 5e-8 for a 50 KB file: negligible against the
     * value of catching a payload hidden inside a tEXt chunk or a GIF comment block.
     * Shorter markers such as "<?=" were considered and rejected: at three bytes the
     * false-positive rate over a 50 KB file is roughly 0.3 percent.
     */
    private const CODE_MARKERS = ['<?php', '<script', '<!DOCTYPE', '<html'];

    /** Largest plausible ICO directory. Real favicons carry three to six entries. */
    private const MAX_ICO_ENTRIES = 64;

    /**
     * Parse $bytes as $format and return its dimensions.
     *
     * @throws AssetInspectionException when the container is malformed, has trailing
     *                                  data, or carries an interpreter marker.
     */
    public function read(ImageFormat $format, string $bytes): ImageDimensions
    {
        $this->assertNoCodeMarkers($bytes);

        return match ($format) {
            ImageFormat::Png => $this->readPng($bytes),
            ImageFormat::Gif => $this->readGif($bytes),
            ImageFormat::Ico => $this->readIco($bytes),
            ImageFormat::Svg => throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'SVG is inspected as XML, not as a raster container.',
            ),
        };
    }

    /**
     * Walk the PNG chunk chain from the signature to IEND.
     *
     * A PNG is a signature followed by length/type/data/CRC chunks. IHDR must come
     * first and IEND must come last; the file must end at IEND's CRC.
     */
    private function readPng(string $bytes): ImageDimensions
    {
        $length = strlen($bytes);
        // 8 signature + 25 IHDR + 12 empty IEND.
        if ($length < 45) {
            throw AssetInspectionException::because(AssetRejectionReason::TooSmall);
        }

        $offset = 8;
        $dimensions = null;
        $sawIend = false;

        while (!$sawIend) {
            if ($offset + 8 > $length) {
                throw AssetInspectionException::because(
                    AssetRejectionReason::MalformedImage,
                    'PNG chunk chain ended without an IEND chunk.',
                );
            }

            $chunkLength = $this->uint32be($bytes, $offset);
            $type = substr($bytes, $offset + 4, 4);

            if (preg_match('/\A[A-Za-z]{4}\z/', $type) !== 1) {
                throw AssetInspectionException::because(
                    AssetRejectionReason::MalformedImage,
                    'PNG chunk type is not four ASCII letters.',
                );
            }

            // The 12 is length(4) + type(4) + CRC(4). Guard against integer overflow of
            // a hostile 0x7FFFFFFF length by comparing against the remaining bytes.
            if ($chunkLength > $length - $offset - 12) {
                throw AssetInspectionException::because(
                    AssetRejectionReason::MalformedImage,
                    'PNG chunk declares a length that runs past the end of the file.',
                );
            }

            if ($offset === 8) {
                if ($type !== 'IHDR' || $chunkLength !== 13) {
                    throw AssetInspectionException::because(
                        AssetRejectionReason::MalformedImage,
                        'PNG does not begin with a 13-byte IHDR chunk.',
                    );
                }

                $dimensions = $this->makeDimensions(
                    $this->uint32be($bytes, $offset + 8),
                    $this->uint32be($bytes, $offset + 12),
                );
            }

            if ($type === 'IEND') {
                $sawIend = true;
            }

            $offset += 12 + $chunkLength;
        }

        if ($offset !== $length) {
            throw AssetInspectionException::because(
                AssetRejectionReason::TrailingData,
                'PNG has ' . ($length - $offset) . ' byte(s) after the IEND chunk.',
            );
        }

        if ($dimensions === null) {
            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'PNG header did not yield dimensions.',
            );
        }

        return $dimensions;
    }

    /**
     * Walk the GIF block chain from the logical screen descriptor to the 0x3B trailer.
     */
    private function readGif(string $bytes): ImageDimensions
    {
        $length = strlen($bytes);
        // 6 header + 7 logical screen descriptor + 1 trailer.
        if ($length < 14) {
            throw AssetInspectionException::because(AssetRejectionReason::TooSmall);
        }

        $dimensions = $this->makeDimensions(
            $this->uint16le($bytes, 6),
            $this->uint16le($bytes, 8),
        );

        $packed = ord($bytes[10]);
        $offset = 13;
        if (($packed & 0x80) !== 0) {
            $offset += 3 * (1 << (($packed & 0x07) + 1));
        }

        while (true) {
            if ($offset >= $length) {
                throw AssetInspectionException::because(
                    AssetRejectionReason::MalformedImage,
                    'GIF block chain ended without a trailer.',
                );
            }

            $marker = ord($bytes[$offset]);
            $offset++;

            if ($marker === 0x3B) {
                break;
            }

            if ($marker === 0x21) {
                // Extension: one label byte, then a sub-block chain.
                if ($offset >= $length) {
                    throw AssetInspectionException::because(
                        AssetRejectionReason::MalformedImage,
                        'GIF extension block is truncated.',
                    );
                }
                $offset++;
                $offset = $this->skipGifSubBlocks($bytes, $offset);
                continue;
            }

            if ($marker === 0x2C) {
                // Image descriptor: 9 bytes, optional local colour table, LZW code size,
                // then a sub-block chain of compressed data.
                if ($offset + 9 > $length) {
                    throw AssetInspectionException::because(
                        AssetRejectionReason::MalformedImage,
                        'GIF image descriptor is truncated.',
                    );
                }

                $localPacked = ord($bytes[$offset + 8]);
                $offset += 9;
                if (($localPacked & 0x80) !== 0) {
                    $offset += 3 * (1 << (($localPacked & 0x07) + 1));
                }

                if ($offset >= $length) {
                    throw AssetInspectionException::because(
                        AssetRejectionReason::MalformedImage,
                        'GIF image data is truncated.',
                    );
                }
                $offset++;
                $offset = $this->skipGifSubBlocks($bytes, $offset);
                continue;
            }

            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'GIF contains an unrecognised block marker.',
            );
        }

        if ($offset !== $length) {
            throw AssetInspectionException::because(
                AssetRejectionReason::TrailingData,
                'GIF has ' . ($length - $offset) . ' byte(s) after the trailer.',
            );
        }

        return $dimensions;
    }

    /** @return int offset of the byte following the terminating 0x00 sub-block */
    private function skipGifSubBlocks(string $bytes, int $offset): int
    {
        $length = strlen($bytes);

        while (true) {
            if ($offset >= $length) {
                throw AssetInspectionException::because(
                    AssetRejectionReason::MalformedImage,
                    'GIF sub-block chain is truncated.',
                );
            }

            $size = ord($bytes[$offset]);
            $offset++;
            if ($size === 0) {
                return $offset;
            }

            $offset += $size;
        }
    }

    /**
     * Read the ICO directory, verify every entry lies inside the file, and report the
     * largest entry's size.
     *
     * The manifest records the largest image in the icon, which is what a browser picks
     * for a high-DPI tab, so that is the size compared against the slot.
     */
    private function readIco(string $bytes): ImageDimensions
    {
        $length = strlen($bytes);
        if ($length < 22) {
            throw AssetInspectionException::because(AssetRejectionReason::TooSmall);
        }

        $count = $this->uint16le($bytes, 4);
        if ($count < 1 || $count > self::MAX_ICO_ENTRIES) {
            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'ICO directory entry count is out of range.',
            );
        }

        $directoryEnd = 6 + ($count * 16);
        if ($directoryEnd > $length) {
            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'ICO directory runs past the end of the file.',
            );
        }

        $payloadEnd = $directoryEnd;
        $bestArea = 0;
        $best = null;

        for ($index = 0; $index < $count; $index++) {
            $entry = 6 + ($index * 16);
            // A stored 0 means 256 pixels, which is how ICO encodes its maximum edge.
            $width = ord($bytes[$entry]) === 0 ? 256 : ord($bytes[$entry]);
            $height = ord($bytes[$entry + 1]) === 0 ? 256 : ord($bytes[$entry + 1]);
            $size = $this->uint32le($bytes, $entry + 8);
            $imageOffset = $this->uint32le($bytes, $entry + 12);

            if ($size < 1 || $imageOffset < $directoryEnd || $imageOffset > $length - $size) {
                throw AssetInspectionException::because(
                    AssetRejectionReason::MalformedImage,
                    'ICO entry points outside the file body.',
                );
            }

            $this->assertIcoEntryIsDecodable(substr($bytes, $imageOffset, $size));

            $payloadEnd = max($payloadEnd, $imageOffset + $size);

            if ($width * $height > $bestArea) {
                $bestArea = $width * $height;
                $best = $this->makeDimensions($width, $height);
            }
        }

        if ($payloadEnd !== $length) {
            throw AssetInspectionException::because(
                AssetRejectionReason::TrailingData,
                'ICO has ' . ($length - $payloadEnd) . ' byte(s) outside any directory entry.',
            );
        }

        if ($best === null) {
            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'ICO directory yielded no usable entry.',
            );
        }

        return $best;
    }

    /**
     * An ICO entry body is either an embedded PNG or a BITMAPINFOHEADER DIB.
     *
     * Without this an "icon" could be a directory of arbitrary blobs and still satisfy
     * every offset check above.
     */
    private function assertIcoEntryIsDecodable(string $entryBytes): void
    {
        if (ImageFormat::Png->matchesSignature($entryBytes)) {
            return;
        }

        if (strlen($entryBytes) >= 40 && $this->uint32le($entryBytes, 0) === 40) {
            return;
        }

        throw AssetInspectionException::because(
            AssetRejectionReason::MalformedImage,
            'ICO entry is neither an embedded PNG nor a BITMAPINFOHEADER bitmap.',
        );
    }

    private function assertNoCodeMarkers(string $bytes): void
    {
        foreach (self::CODE_MARKERS as $marker) {
            if (stripos($bytes, $marker) !== false) {
                throw AssetInspectionException::because(
                    AssetRejectionReason::EmbeddedCode,
                    'Image payload contains a script or interpreter opening marker.',
                );
            }
        }
    }

    /** Turn header integers into the value object, converting its guard into a rejection. */
    private function makeDimensions(int $width, int $height): ImageDimensions
    {
        try {
            return new ImageDimensions($width, $height);
        } catch (DomainException) {
            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'Image header declares an implausible pixel size.',
            );
        }
    }

    /** Big-endian unsigned 32-bit read; callers guarantee the four bytes exist. */
    private function uint32be(string $bytes, int $offset): int
    {
        if ($offset + 4 > strlen($bytes)) {
            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'Image header is truncated.',
            );
        }

        return (ord($bytes[$offset]) << 24)
            | (ord($bytes[$offset + 1]) << 16)
            | (ord($bytes[$offset + 2]) << 8)
            | ord($bytes[$offset + 3]);
    }

    /** Little-endian unsigned 32-bit read, as used throughout the ICO directory. */
    private function uint32le(string $bytes, int $offset): int
    {
        if ($offset + 4 > strlen($bytes)) {
            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'Image header is truncated.',
            );
        }

        return ord($bytes[$offset])
            | (ord($bytes[$offset + 1]) << 8)
            | (ord($bytes[$offset + 2]) << 16)
            | (ord($bytes[$offset + 3]) << 24);
    }

    /** Little-endian unsigned 16-bit read. */
    private function uint16le(string $bytes, int $offset): int
    {
        if ($offset + 2 > strlen($bytes)) {
            throw AssetInspectionException::because(
                AssetRejectionReason::MalformedImage,
                'Image header is truncated.',
            );
        }

        return ord($bytes[$offset]) | (ord($bytes[$offset + 1]) << 8);
    }
}
