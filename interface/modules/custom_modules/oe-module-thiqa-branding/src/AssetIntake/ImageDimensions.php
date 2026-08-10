<?php

/**
 * A positive pixel size, kept as a type so width and height cannot be transposed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\AssetIntake;

use DomainException;
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;

/**
 * `LogoSlot::expectedDimensions()` returns a bare `array{width:int,height:int}` because
 * it predates this package and lives in another agent's file. Everything on the intake
 * path converts to this object at the boundary and compares objects afterwards.
 */
final readonly class ImageDimensions
{
    /** Nothing certified is anywhere near this; it exists to stop absurd header values. */
    public const MAX_EDGE = 16384;

    public function __construct(
        public int $width,
        public int $height,
    ) {
        if ($width <= 0 || $height <= 0) {
            throw new DomainException('Image dimensions must be positive.');
        }

        if ($width > self::MAX_EDGE || $height > self::MAX_EDGE) {
            throw new DomainException('Image dimensions exceed the permitted maximum edge.');
        }
    }

    /**
     * The certified size for a slot, or null where the slot fixes no size.
     *
     * Comparison is exact. The manifest records one integral pixel size per slot and the
     * production exports are generated from those masters, so any tolerance band would
     * only widen what a tenant can substitute without widening anything legitimate.
     */
    public static function forSlot(LogoSlot $slot): ?self
    {
        $expected = $slot->expectedDimensions();
        if ($expected === null) {
            return null;
        }

        return new self($expected['width'], $expected['height']);
    }

    public function equals(self $other): bool
    {
        return $this->width === $other->width && $this->height === $other->height;
    }

    /** "1053x390" — integers only, so it is always safe to log. */
    public function describe(): string
    {
        return $this->width . 'x' . $this->height;
    }
}
