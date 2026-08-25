<?php

/**
 * A staged file that has been renamed into place, still individually reversible.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

/**
 * Plan §4.4 requires every apply step to be "individually reversible". For a file that
 * means remembering what it replaced: `previousPath` holds the displaced original,
 * moved aside rather than overwritten, so `AtomicFileWriter::revert()` can put it back
 * byte-for-byte if a later step fails.
 *
 * `previousPath` is null when the target did not exist, in which case reverting means
 * deleting rather than restoring.
 */
final readonly class CommittedFile
{
    public function __construct(
        public string $targetPath,
        public ?string $previousPath,
    ) {
    }
}
