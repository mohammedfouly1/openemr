<?php

/**
 * The single-word verdict of a branding health check.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Observability;

/**
 * Backed by a string so the verdict survives into a log field or a monitoring payload
 * unchanged.
 *
 * `NeverMaterialised` is deliberately *not* a failure. A tenant that has never had a
 * revision applied renders the shared Tier 1 product palette, which is a complete and
 * correct state — BrandingRevision::initial() documents exactly that. Treating it as
 * unhealthy would make every freshly provisioned tenant page a human for no reason.
 *
 * `Stale` is likewise not a failure. Staleness here is measured against the age of the
 * materialisation stamp only: the health check may run inside a request and is therefore
 * forbidden from asking the Control Plane what revision it believes is current
 * (constraint C5). An old stamp means "nobody has pushed branding for a while", which is
 * ordinary for a settled tenant.
 */
enum BrandingHealthStatus: string
{
    /** A revision is live, the stylesheets are present, and the stamp is recent. */
    case Healthy = 'healthy';

    /** Nothing has been materialised; the tenant renders product defaults. */
    case NeverMaterialised = 'never_materialised';

    /** Consistent, but the last materialisation is older than the configured threshold. */
    case Stale = 'stale';

    /** At least one part of the materialised state contradicts another. */
    case Inconsistent = 'inconsistent';

    /** The state could not be read, so nothing can be asserted about it. */
    case Unreadable = 'unreadable';

    /**
     * Whether a health check reporting this status should be treated as a failure by a
     * monitor or a CI job.
     */
    public function isFailure(): bool
    {
        return match ($this) {
            self::Healthy, self::NeverMaterialised, self::Stale => false,
            self::Inconsistent, self::Unreadable => true,
        };
    }

    /** Operator-facing wording for CLI output. */
    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'healthy',
            self::NeverMaterialised => 'never materialised (rendering product defaults)',
            self::Stale => 'stale',
            self::Inconsistent => 'inconsistent',
            self::Unreadable => 'unreadable',
        };
    }
}
