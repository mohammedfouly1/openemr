<?php

/**
 * A globals adapter whose reads always fail, standing in for an unreachable tenant DB.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Observability;

use LogicException;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Materialisation\BrandingGlobalsWriterInterface;
use OpenEMR\Modules\ThiqaBranding\Materialisation\GlobalsDelta;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use RuntimeException;

/**
 * Every write method throws LogicException rather than recording anything: a health check
 * that reached one would be a defect, and a test double that quietly tolerated it would
 * hide that. Only the read path fails "normally", with the kind of runtime exception a
 * database adapter raises.
 */
final class UnreadableGlobalsWriter implements BrandingGlobalsWriterInterface
{
    public const FAILURE_MESSAGE = 'Simulated tenant database failure with sensitive detail inside.';

    public function currentRevision(SiteId $site): BrandingRevision
    {
        throw new RuntimeException(self::FAILURE_MESSAGE);
    }

    /**
     * @return array<string, string>
     */
    public function readBrandingGlobals(SiteId $site): array
    {
        throw new RuntimeException(self::FAILURE_MESSAGE);
    }

    public function writeAll(
        SiteId $site,
        GlobalsDelta $delta,
        BrandingRevision $revision,
        string $materialisedAt,
    ): void {
        throw new LogicException('A read-only caller wrote branding globals.');
    }
}
