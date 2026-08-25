<?php

/**
 * Builds AssetPlacements the only way production can: through the real LogoValidator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation;

use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\LogoValidator;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\RasterImageReader;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\SvgInspector;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\ValidatedAsset;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\AssetPlacement;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * AssetPlacement can no longer be constructed from raw bytes (audit finding AR-P2-002):
 * its constructor is private and the only factories take a ValidatedAsset, which in turn
 * only LogoValidator can mint. Tests therefore have to produce assets the same way the
 * product does, which is the point — a test helper that could forge one would prove
 * nothing about the boundary it is meant to exercise.
 */
trait CertifiedAssetTrait
{
    /** A real, validator-certified placement for a slot, at the slot's expected size. */
    private function certifiedPlacement(LogoSlot $slot, ?string $directory = null): AssetPlacement
    {
        return AssetPlacement::fromValidatedSelfChecked($this->certifiedAsset($slot, $directory));
    }

    /** The ValidatedAsset itself, for tests that need to pair it with a wrong checksum. */
    private function certifiedAsset(LogoSlot $slot, ?string $directory = null): ValidatedAsset
    {
        $dimensions = $slot->expectedDimensions() ?? ['width' => 64, 'height' => 64];
        $bytes = self::validPngBytes($dimensions['width'], $dimensions['height']);

        $directory ??= sys_get_temp_dir();
        $path = $directory . DIRECTORY_SEPARATOR . 'certified-' . bin2hex(random_bytes(6)) . '.png';

        if (file_put_contents($path, $bytes) !== strlen($bytes)) {
            throw new RuntimeException('Could not stage the certified asset fixture.');
        }

        try {
            $validator = new LogoValidator(new RasterImageReader(), new SvgInspector(), new NullLogger());
            $result = $validator->validate($slot, $path, 'logo.png');

            $asset = $result->asset();
            if ($asset === null) {
                throw new RuntimeException(
                    'The fixture failed real validation: ' . implode('; ', $result->messages()),
                );
            }

            return $asset;
        } finally {
            @unlink($path);
        }
    }

    /** A structurally complete, CRC-correct 8-bit RGBA PNG of the requested size. */
    private static function validPngBytes(int $width, int $height): string
    {
        $chunk = static function (string $type, string $payload): string {
            return pack('N', strlen($payload))
                . $type
                . $payload
                . pack('N', crc32($type . $payload));
        };

        $ihdr = $chunk('IHDR', pack('NN', $width, $height) . chr(8) . chr(6) . chr(0) . chr(0) . chr(0));

        $raw = '';
        for ($y = 0; $y < $height; $y++) {
            $raw .= chr(0) . str_repeat(chr(0) . chr(0) . chr(0) . chr(255), $width);
        }

        $compressed = gzcompress($raw, 9);
        if ($compressed === false) {
            throw new RuntimeException('Could not compress the PNG fixture.');
        }

        return "\x89PNG\r\n\x1a\n" . $ihdr . $chunk('IDAT', $compressed) . $chunk('IEND', '');
    }
}
