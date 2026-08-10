<?php

/**
 * The closed set of binary formats a tenant logo is allowed to be.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\AssetIntake;

/**
 * Four formats, no more. JPEG is deliberately absent (no alpha, and the JFIF/EXIF
 * container is a well known carrier for appended payloads); WEBP and AVIF are absent
 * because nothing in the certified manifest uses them and every extra decoder is extra
 * attack surface.
 *
 * The backed value doubles as the on-disk extension, so a caller can never introduce a
 * third spelling of the same format.
 *
 * Signatures are compared against the file's first bytes only. A client-supplied MIME
 * type or filename extension is never evidence of format — see {@see LogoValidator}.
 */
enum ImageFormat: string
{
    case Png = 'png';
    case Svg = 'svg';
    case Gif = 'gif';
    case Ico = 'ico';

    /**
     * Byte sequences that must appear at offset 0 for this format.
     *
     * Empty for SVG, which is XML text and therefore has no fixed binary signature; it
     * is identified structurally by {@see SvgInspector} instead.
     *
     * @return list<string>
     */
    public function signatures(): array
    {
        return match ($this) {
            self::Png => ["\x89PNG\r\n\x1A\n"],
            self::Gif => ['GIF87a', 'GIF89a'],
            // Reserved word 0x0000, then image type 1 (icon) little-endian.
            self::Ico => ["\x00\x00\x01\x00"],
            self::Svg => [],
        };
    }

    /** The MIME type this module emits for the format. Never read from the client. */
    public function mimeType(): string
    {
        return match ($this) {
            self::Png => 'image/png',
            self::Svg => 'image/svg+xml',
            self::Gif => 'image/gif',
            self::Ico => 'image/vnd.microsoft.icon',
        };
    }

    /** The single canonical on-disk extension, without a leading dot. */
    public function extension(): string
    {
        return $this->value;
    }

    /** True for the XML format, which needs the hostile-content inspection pass. */
    public function isVector(): bool
    {
        return match ($this) {
            self::Svg => true,
            self::Png, self::Gif, self::Ico => false,
        };
    }

    /**
     * Formats identified by a binary signature, i.e. everything except SVG.
     *
     * @return list<self>
     */
    public static function binaryCases(): array
    {
        return [self::Png, self::Gif, self::Ico];
    }

    /** True when $head (the file's leading bytes) carries one of this format's signatures. */
    public function matchesSignature(string $head): bool
    {
        foreach ($this->signatures() as $signature) {
            if (str_starts_with($head, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Every permitted format whose signature matches $head.
     *
     * Returns a list rather than a single case on purpose: two matches means the bytes
     * are a polyglot and the file must be refused, not resolved to a "best" guess.
     *
     * @return list<self>
     */
    public static function signatureMatches(string $head): array
    {
        $matches = [];
        foreach (self::binaryCases() as $case) {
            if ($case->matchesSignature($head)) {
                $matches[] = $case;
            }
        }

        return $matches;
    }

    /**
     * Resolve a filename extension to a format, or null when it is not permitted.
     *
     * $extension is untrusted: it is compared, never concatenated into a path.
     */
    public static function fromExtension(string $extension): ?self
    {
        return self::tryFrom(strtolower(ltrim($extension, '.')));
    }
}
