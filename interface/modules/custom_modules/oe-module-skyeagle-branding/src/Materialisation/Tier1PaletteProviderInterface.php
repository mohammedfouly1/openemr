<?php

/**
 * Supplies the product-level palette a tenant overlay is validated against.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenParseException;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSet;

/**
 * The Tier 1 base is deliberately *not* part of MaterialisationJob.
 *
 * TokenValidator measures contrast on the merged palette, so whoever supplies the base
 * decides what "accessible" is measured against. If the Control Plane could send the
 * base along with the overlay it could send a base that makes any overlay pass, and the
 * tenant-side re-validation of plan §4.4 step 2 would be theatre. The base therefore
 * comes from product data the tenant already has — `brand/tokens/thiqa-tokens.json`,
 * shipped in the image — through this interface.
 */
interface Tier1PaletteProviderInterface
{
    /**
     * @throws TokenParseException when the shipped token document is unreadable or invalid
     */
    public function paletteFor(ThemeVariant $variant): TokenSet;
}
