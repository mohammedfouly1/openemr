<?php

/**
 * A stylesheet probe whose answers are set by the test rather than by a disk.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Observability;

use OpenEMR\Modules\ThiqaBranding\Observability\StylesheetProbeInterface;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;

/**
 * The inconsistencies this check exists to find — a revision with no stylesheet, a
 * stylesheet with no revision — are combinations that a healthy filesystem will not
 * produce on demand. Setting the two answers directly is the only way to test them
 * without corrupting a real tree first.
 *
 * It also records the sites it was asked about, so a test can prove the check stayed
 * inside the tenant it was given.
 */
final class InMemoryStylesheetProbe implements StylesheetProbeInterface
{
    /** @var list<string> */
    public array $sitesProbed = [];

    public function __construct(
        public bool $light = true,
        public bool $dark = true,
    ) {
    }

    public function isPresent(SiteId $site, ThemeVariant $variant): bool
    {
        $this->sitesProbed[] = $site->value;

        return match ($variant) {
            ThemeVariant::Light => $this->light,
            ThemeVariant::Dark => $this->dark,
        };
    }
}
