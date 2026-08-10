<?php

/**
 * A resolved colour token for one variant.
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
 * Pairs a schema entry with the value the variant actually resolved to, and
 * records whether that value came from the token's declared fallback.
 */
final readonly class PaletteEntry
{
    public function __construct(
        public ColorToken $token,
        public HexColor $color,
        public ?string $aliasOf = null,
    ) {
    }
}
