<?php

/**
 * The shipped stylesheet probe: a single stat() against the tenant's own scope.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Observability;

use OpenEMR\Modules\ThiqaBranding\Materialisation\TenantBrandingPaths;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;

/**
 * The path is derived by TenantBrandingPaths, never assembled here, so this probe cannot
 * be pointed at another tenant's scope however it is called: SiteId admits no separator
 * and ThemeVariant is a unit enum, so `<root>/<site>/tokens-<variant>.css` is the only
 * shape reachable.
 *
 * Existence only, deliberately. Reading the file to check its contents would make a
 * health check as expensive as the thing it checks, and the stylesheet's *content*
 * correctness is already guaranteed by the materialiser's staging and verify steps
 * (plan §4.4 steps 3-4). What can drift afterwards is the file being absent, and that is
 * exactly what one stat() answers.
 */
final readonly class FilesystemStylesheetProbe implements StylesheetProbeInterface
{
    public function __construct(private TenantBrandingPaths $paths)
    {
    }

    public function isPresent(SiteId $site, ThemeVariant $variant): bool
    {
        return is_file($this->paths->tokenCssFile($site, $variant));
    }
}
