<?php

/**
 * Result of driving and terminating the kill-point subprocess.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation;

final readonly class KillOutcome
{
    public function __construct(
        public bool $reachedSentinel,
        public ?int $exitCode,
    ) {
    }
}
