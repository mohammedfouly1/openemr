<?php

/**
 * Bytes that have passed every intake check, carried as proof for the placer.
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
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;

/**
 * "Parse, don't validate": {@see AssetPlacer} takes this type and nothing else, so the
 * type system carries the proof that validation happened. There is no code path that
 * writes a file from a plain string path.
 *
 * The verified *bytes* travel here, not the source path. Re-reading the file at
 * placement time would open a time-of-check-to-time-of-use window in which the source
 * — which lives in a staging area an intake process wrote — could be swapped for a
 * different file between validation and rename.
 */
final readonly class ValidatedAsset
{
    private function __construct(
        public LogoSlot $slot,
        public ImageFormat $format,
        public string $contents,
        public ImageDimensions $dimensions,
        public string $sha256,
    ) {
    }

    /**
     * Build the proof object.
     *
     * @internal Only {@see LogoValidator} may call this. PHP has no friend visibility,
     *           so the contract is documentary; the PHPStan rule set and code review
     *           enforce it. Any other caller is asserting a validation that never ran.
     */
    public static function certified(
        LogoSlot $slot,
        ImageFormat $format,
        string $contents,
        ImageDimensions $dimensions,
    ): self {
        if ($contents === '') {
            throw new DomainException('A validated asset cannot be empty.');
        }

        return new self($slot, $format, $contents, $dimensions, hash('sha256', $contents));
    }

    public function byteLength(): int
    {
        return strlen($this->contents);
    }

    /**
     * The name this asset must be written under, derived from the slot and the verified
     * format only.
     *
     * `LogoSlot::filenamePattern()` is either a fixed name (`favicon.ico`) or the
     * `logo.*` wildcard `LogoService::findLogo()` globs for. Nothing the caller supplied
     * contributes a single character, which is why the placer can never be talked into
     * writing `logo.php`.
     */
    public function targetFilename(): string
    {
        $pattern = $this->slot->filenamePattern();

        return str_ends_with($pattern, '.*')
            ? substr($pattern, 0, -1) . $this->format->extension()
            : $pattern;
    }
}
