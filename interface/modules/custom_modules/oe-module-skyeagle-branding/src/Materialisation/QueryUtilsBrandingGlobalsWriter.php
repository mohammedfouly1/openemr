<?php

/**
 * BrandingGlobalsWriterInterface over OpenEMR's `globals` table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

use LogicException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;

/**
 * The shipped adapter. Deliberately thin: every statement is parameterised, every
 * globals name comes from the BrandingGlobalKey enum, and the transaction methods are
 * pass-throughs to `QueryUtils`, which wraps ADODB's real `BeginTrans` /
 * `CommitTrans` / `RollbackTrans`. No connection is constructed here.
 *
 * **Tenant binding.** OpenEMR resolves a site to a database at bootstrap; the `globals`
 * table itself has no tenant column. This adapter is therefore constructed *for* one
 * site and refuses any call naming a different one. That converts an invisible ambient
 * assumption — "the connection happens to be the right tenant's" — into a checked
 * pre-condition, which is what the plan's cross-tenant isolation tests (A1/A2) need in
 * order to be meaningful. A mismatch is programmer error and throws; it is not a
 * MaterialisationResult failure, because there is no payload to retry.
 *
 * `gl_index` is fixed at 0: every branding global is a scalar setting, not one row of a
 * multi-valued list.
 */
final readonly class QueryUtilsBrandingGlobalsWriter implements BrandingGlobalsWriterInterface
{
    private const GL_INDEX = 0;

    public function __construct(private SiteId $boundSite)
    {
    }

    public function currentRevision(SiteId $site): BrandingRevision
    {
        $raw = $this->readSingle($site, BrandingGlobalKey::Revision);
        if ($raw === null || !ctype_digit($raw)) {
            return BrandingRevision::initial();
        }

        return new BrandingRevision((int) $raw);
    }

    /**
     * @return array<string, string>
     */
    public function readBrandingGlobals(SiteId $site): array
    {
        $this->assertBoundTo($site);

        $names = array_map(
            static fn (BrandingGlobalKey $key): string => $key->value,
            BrandingGlobalKey::cases(),
        );

        $placeholders = implode(', ', array_fill(0, count($names), '?'));
        $records = QueryUtils::fetchRecords(
            'SELECT `gl_name`, `gl_value` FROM `globals` WHERE `gl_index` = ? AND `gl_name` IN (' . $placeholders . ')',
            [self::GL_INDEX, ...$names],
        );

        $values = [];
        foreach ($records as $record) {
            $name = $record['gl_name'] ?? null;
            $value = $record['gl_value'] ?? null;
            if (!is_string($name) || !is_string($value)) {
                continue;
            }

            $values[$name] = $value;
        }

        return $values;
    }

    /**
     * One transaction for the delta, the timestamp and the revision.
     *
     * QueryUtils::inTransaction() owns the begin/commit/rollback cycle: it commits when
     * the callable returns and rolls back and rethrows on any throwable. That is exactly
     * the all-or-nothing semantic Q76 requires, and using it also retires the three
     * deprecated QueryUtils transaction calls this class previously made (audit finding
     * B-04) — the deprecation and the atomicity gap had the same fix.
     *
     * Ordering inside the transaction still matters: the revision is written last, so a
     * database that flushes partially cannot expose a revision newer than its own data.
     */
    public function writeAll(
        SiteId $site,
        GlobalsDelta $delta,
        BrandingRevision $revision,
        string $materialisedAt,
    ): void {
        $this->assertBoundTo($site);

        QueryUtils::inTransaction(function () use ($delta, $revision, $materialisedAt): void {
            foreach ($delta->all() as $name => $value) {
                $key = BrandingGlobalKey::tryFrom($name);
                if (!$key instanceof BrandingGlobalKey) {
                    // Unreachable: GlobalsDelta only admits enum-backed names. Throwing
                    // rather than skipping keeps the transaction all-or-nothing.
                    throw new LogicException('Globals delta carried a name outside the branding registry.');
                }

                $this->upsert($key, $value);
            }

            $this->upsert(BrandingGlobalKey::MaterialisedAt, $materialisedAt);

            // LAST, inside the same transaction.
            $this->upsert(BrandingGlobalKey::Revision, (string) $revision->value);
        });
    }

    private function upsert(BrandingGlobalKey $key, string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, ?, ?) '
                . 'ON DUPLICATE KEY UPDATE `gl_value` = ?',
            [$key->value, self::GL_INDEX, $value, $value],
        );
    }

    private function readSingle(SiteId $site, BrandingGlobalKey $key): ?string
    {
        $this->assertBoundTo($site);

        $value = QueryUtils::fetchSingleValue(
            'SELECT `gl_value` FROM `globals` WHERE `gl_name` = ? AND `gl_index` = ?',
            'gl_value',
            [$key->value, self::GL_INDEX],
        );

        return is_string($value) ? $value : null;
    }

    private function assertBoundTo(SiteId $site): void
    {
        if (!$site->equals($this->boundSite)) {
            // The site is never interpolated: this message can reach a log shared across
            // tenants, and topology detail must not leak into it (plan §3.8.1 rule 4).
            throw new LogicException(
                'Refusing a branding globals write for a site this connection is not bound to.',
            );
        }
    }
}
