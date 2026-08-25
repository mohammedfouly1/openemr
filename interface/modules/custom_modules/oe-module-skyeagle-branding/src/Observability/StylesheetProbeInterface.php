<?php

/**
 * Answers whether a tenant's generated token stylesheet is on disk.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Observability;

use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;

/**
 * The one filesystem-touching dependency of BrandingHealthCheck, behind an interface so
 * the check itself stays a pure function of (globals, stylesheet presence, clock).
 *
 * Implementations MUST be total: no exception, no network, and no write. A probe is called
 * from a read-only health check that may run inside a request, so a probe that blocks or
 * throws turns a diagnostic into an outage.
 */
interface StylesheetProbeInterface
{
    /** True when this tenant's generated stylesheet for the variant exists. */
    public function isPresent(SiteId $site, ThemeVariant $variant): bool;
}
