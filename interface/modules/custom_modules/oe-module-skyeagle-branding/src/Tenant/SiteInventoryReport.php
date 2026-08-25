<?php

/**
 * What a filesystem scan of the sites directory found, as a value.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Tenant;

/**
 * The result of a {@see SiteInventory} scan.
 *
 * **Why "unreadable" is a state and not an empty list.** A scan that could not read the
 * sites directory and a scan that found one tenant are entirely different facts, and
 * collapsing them would recreate the very defect this class exists to close: an operator
 * being told, with confidence, that there is nothing else to worry about. `readable`
 * false means *unknown*, and callers are expected to say so out loud rather than print a
 * reassuring single-tenant line.
 *
 * **Why unsupported directory names are counted, not named.** A directory under `sites/`
 * whose name is outside {@see SiteId}'s character set cannot be represented here, but it
 * may still be a real, configured tenant — {@see \OpenEMR\Modules\SkyEagleBranding\Bootstrap}
 * refuses to bind such a site rather than mangling it, and a dotted site id is the known
 * case (RB-05). Dropping those entries silently would be the same invisibility bug in a
 * new place, so they are counted. They are not printed, because the name is arbitrary
 * bytes off the filesystem and this value ends up on a terminal: a name carrying ANSI
 * escapes would rewrite the very warning that reported it.
 *
 * Nothing here ever carries a credential. The scan reads `sqlconf.php` only far enough to
 * learn whether `$config` is 1; see {@see SiteInventory}.
 */
final readonly class SiteInventoryReport
{
    /**
     * @param list<SiteId> $configured sites whose sqlconf.php establishes `$config = 1`
     * @param int          $unsupportedNameCount directories skipped because their name is not a valid site id
     * @param bool         $readable whether the sites directory could be enumerated at all
     */
    private function __construct(
        public array $configured,
        public int $unsupportedNameCount,
        public bool $readable,
    ) {
    }

    /**
     * @param list<SiteId> $configured
     */
    public static function of(array $configured, int $unsupportedNameCount = 0): self
    {
        return new self($configured, max(0, $unsupportedNameCount), true);
    }

    /** The sites directory could not be enumerated; the tenant population is unknown. */
    public static function unreadable(): self
    {
        return new self([], 0, false);
    }

    /**
     * Every configured site other than the one a command is acting on.
     *
     * The acting site does not have to appear in the inventory. A `--site` naming a tenant
     * that is not configured is an operator error the command itself reports; it must not
     * also cause this report to hide the tenants that *are* configured.
     *
     * @return list<SiteId>
     */
    public function othersThan(SiteId $acting): array
    {
        return array_values(array_filter(
            $this->configured,
            static fn (SiteId $site): bool => !$site->equals($acting),
        ));
    }

    /** True when the scan found nothing at all worth telling an operator about. */
    public function isSingleTenant(SiteId $acting): bool
    {
        return $this->readable
            && $this->unsupportedNameCount === 0
            && $this->othersThan($acting) === [];
    }
}
