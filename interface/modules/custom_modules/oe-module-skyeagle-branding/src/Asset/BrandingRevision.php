<?php

/**
 * A tenant-scoped, monotonic branding revision.
 *
 * Locked constraint C8 (Q76, MVP-010 AC-4) requires every branding resource to carry
 * a tenant-safe revision so that stale or cross-tenant branding cannot be served from
 * a cache. Wrapping the integer in a domain primitive stops it being transposed with
 * any other integer in a URL-building call.
 *
 * Revision 0 means "never materialised": the tenant is rendering product defaults.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Asset;

use DomainException;

final readonly class BrandingRevision
{
    public function __construct(public int $value)
    {
        if ($value < 0) {
            throw new DomainException('Branding revision must not be negative');
        }
    }

    /** The initial state: no materialisation has succeeded yet. */
    public static function initial(): self
    {
        return new self(0);
    }

    /** True when at least one materialisation has completed. */
    public function isMaterialised(): bool
    {
        return $this->value > 0;
    }

    /** The next revision. Wither: the current instance is unchanged. */
    public function next(): self
    {
        return new self($this->value + 1);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * The cache-key fragment appended to branding URLs.
     *
     * Always additive: callers must append this alongside OpenEMR's existing
     * cache identifiers, never in place of them (plan section 3.8.1).
     */
    public function cacheKey(): string
    {
        return (string) $this->value;
    }
}
