<?php

/**
 * A PSR-20 clock that never moves, for deterministic materialisation stamps.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * The materialiser stamps `saas_branding_materialised_at`; without an injected clock the
 * tests could only assert that *something* was written. Freezing time lets them assert
 * the exact value, which in turn pins the write ordering around it.
 */
final readonly class FrozenClock implements ClockInterface
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    public static function at(string $iso8601): self
    {
        return new self(new DateTimeImmutable($iso8601));
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
