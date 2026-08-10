<?php

/**
 * The materialiser's only route to the tenant database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Materialisation;

use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;

/**
 * An interface rather than a direct QueryUtils call, for two reasons that are not
 * "testability" alone:
 *
 *  1. Plan §4.4 step 5 requires the globals delta to be written "in a single DB
 *     transaction" and the revision to be written *after* it. That is a sequencing
 *     contract, and a contract needs a surface to be stated on. Explicit
 *     begin/commit/rollback is that surface — it also mirrors what QueryUtils already
 *     exposes (`startTransaction()`, `commitTransaction()`, `rollbackTransaction()`),
 *     so the shipped adapter is a thin pass-through rather than a reimplementation.
 *  2. Every method takes a SiteId. OpenEMR's `globals` table has no tenant column —
 *     tenant scope is which database the connection is bound to — so the implementation
 *     is required to *assert* that the site it was handed is the site it is connected
 *     to, and refuse otherwise. Passing the tenant explicitly is what makes that check
 *     possible at all; a bare `write()` could not tell one tenant from another.
 *
 * Implementations must throw on failure. The materialiser catches, rolls back and
 * converts to a MaterialisationResult; it never lets a database error escape.
 */
interface BrandingGlobalsWriterInterface
{
    /**
     * The revision currently live for this tenant.
     *
     * Returns BrandingRevision::initial() when nothing has been materialised yet.
     */
    public function currentRevision(SiteId $site): BrandingRevision;

    /**
     * The branding globals currently persisted, keyed by BrandingGlobalKey::value.
     *
     * Only keys the layer owns are returned; a key absent from the table is absent here
     * rather than present as its default. The materialiser uses this to decide whether
     * a delta would actually change anything.
     *
     * @return array<string, string>
     */
    public function readBrandingGlobals(SiteId $site): array;

    /**
     * Write the delta, the materialisation timestamp and the revision as ONE unit.
     *
     * All-or-nothing is the entire contract. Locked decision Q76 requires that a failed
     * materialisation must not partially apply a branding revision, and the revision is
     * what every branding URL is keyed on — so a delta that commits while the revision
     * does not would leave the tenant serving new product names and tenant strings under
     * a stale revision, with no cache key change to reveal it and the run reported as
     * failed. Non-URL values such as the product name cannot be protected by revisioning
     * at all, which is why the guarantee has to be transactional rather than ordered.
     *
     * The revision MUST be the last write inside the transaction, so that a database
     * that flushes partially still never exposes a revision newer than its own data.
     *
     * Implementations must roll back completely on any failure and must not leave a
     * transaction open.
     *
     * This replaces an earlier begin/write/commit/rollback surface, which allowed —
     * and in practice produced — two separate transactions (audit finding AR-P2-001).
     *
     * @throws \RuntimeException if the unit could not be committed in full.
     */
    public function writeAll(
        SiteId $site,
        GlobalsDelta $delta,
        BrandingRevision $revision,
        string $materialisedAt,
    ): void;
}
