<?php

/**
 * One generated `@font-face` declaration.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * The `unicodeRange` is not authored here: it is lifted verbatim from
 * `brand/typography/thiqa-fonts.scss` so the Latin/Arabic subset split that
 * keeps Arabic faces off Latin pages survives regeneration.
 */
final readonly class FontFace
{
    public function __construct(
        public string $family,
        public int $weight,
        public string $fileName,
        public string $unicodeRange,
    ) {
    }
}
