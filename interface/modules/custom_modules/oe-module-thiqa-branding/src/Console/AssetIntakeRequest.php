<?php

/**
 * One operator-declared "put this file in this slot" line from a materialisation payload.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;

/**
 * Carries no proof of validity: this is the untrusted intent JobPayload parsed out of
 * the JSON file, before {@see \OpenEMR\Modules\ThiqaBranding\AssetIntake\LogoValidator}
 * has looked at the bytes at `$sourcePath`. Only a {@see
 * \OpenEMR\Modules\ThiqaBranding\Materialisation\AssetPlacement} built from the
 * validator's output may reach the materialiser (audit finding AR-P2-002).
 */
final readonly class AssetIntakeRequest
{
    public function __construct(
        public LogoSlot $slot,
        public string $sourcePath,
        public string $declaredFilename,
    ) {
    }
}
