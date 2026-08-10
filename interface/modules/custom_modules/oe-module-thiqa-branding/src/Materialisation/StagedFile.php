<?php

/**
 * A file written to a temporary name, not yet visible at its target path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Materialisation;

/**
 * Stage 3 of the transaction model (plan §4.4): content exists on disk, in the same
 * directory as its eventual target, under a name nothing serves.
 *
 * Holding the pair as one object is what lets the materialiser discard a whole batch
 * on failure without reconstructing any name.
 */
final readonly class StagedFile
{
    public function __construct(
        public string $temporaryPath,
        public string $targetPath,
        public string $checksum,
    ) {
    }
}
