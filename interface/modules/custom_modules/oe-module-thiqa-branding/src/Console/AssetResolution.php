<?php

/**
 * Outcome of running every AssetIntakeRequest in a payload through the real validator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use OpenEMR\Modules\ThiqaBranding\Materialisation\AssetPlacement;

/**
 * All-or-nothing, mirroring {@see \OpenEMR\Modules\ThiqaBranding\Token\TokenValidationResult}
 * and {@see \OpenEMR\Modules\ThiqaBranding\AssetIntake\AssetValidationResult}: a job that
 * names three logos and gets one wrong does not silently place the other two, so a caller
 * only ever inspects `$placements` after confirming `$rejections === []`.
 */
final readonly class AssetResolution
{
    /**
     * @param list<AssetPlacement> $placements
     * @param list<string>         $rejections
     */
    private function __construct(
        public array $placements,
        public array $rejections,
    ) {
    }

    /** @param list<AssetPlacement> $placements */
    public static function accepted(array $placements): self
    {
        return new self($placements, []);
    }

    /** @param list<string> $rejections */
    public static function rejected(array $rejections): self
    {
        return new self([], $rejections);
    }

    public function isValid(): bool
    {
        return $this->rejections === [];
    }
}
