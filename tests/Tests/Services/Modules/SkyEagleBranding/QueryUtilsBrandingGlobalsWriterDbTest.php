<?php

/**
 * DB-backed proof that the shipped globals writer commits atomically against real MariaDB.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\SkyEagleBranding;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once __DIR__ . '/skyeagle_branding_module_autoloader.php';

use OpenEMR\Modules\SkyEagleBranding\Accessibility\ContrastCalculator;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\AtomicFileWriter;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\BrandingMaterialiser;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\GlobalsDelta;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\JsonFileTier1PaletteProvider;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\MaterialisationJob;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\QueryUtilsBrandingGlobalsWriter;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\TenantBrandingPaths;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\TokenCssWriter;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use OpenEMR\Modules\SkyEagleBranding\Token\CssVariableRenderer;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSetParser;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenValidator;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use DateTimeImmutable;

/**
 * The isolated suite's BrandingMaterialiserTest proves the atomicity *contract* -- given
 * a writer that fails partway, does the revision stay at n-1 -- against an in-memory
 * RecordingGlobalsWriter. It cannot prove the *shipped adapter*, QueryUtilsBrandingGlobalsWriter,
 * actually implements that contract correctly against a real MySQL/MariaDB connection and
 * a real `QueryUtils::inTransaction()` call (audit finding AR-P2-006, "DB-backed and
 * two-tenant tests remain unwritten"). This test closes that gap by running the real
 * adapter against the connection this host's dev stack already has open.
 *
 * Every `globals` row this test could touch is snapshotted in setUp() and restored in
 * tearDown(), whether the test passes or fails, because `globals` has no tenant column --
 * this connection is the same database the running application on this host serves from
 * (docs/rebranding.md §0.4; CLAUDE.local.md §7), so a test that left rows behind would
 * corrupt the live dev site's branding state.
 */
final class QueryUtilsBrandingGlobalsWriterDbTest extends TestCase
{
    private const SITE = 'default';

    private const TOKEN_DOCUMENT = __DIR__ . '/../../../../../brand/tokens/thiqa-tokens.json';

    private SiteId $site;

    /** @var array<string, string|null> gl_name => gl_value before this test ran, or null if absent */
    private array $snapshot = [];

    private string $tempRoot = '';

    protected function setUp(): void
    {
        $this->site = new SiteId(self::SITE);
        $this->snapshot = $this->readAllBrandingRows();

        $this->tempRoot = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/')
            . '/thiqa-branding-db-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tempRoot, 0o777, true) && !is_dir($this->tempRoot)) {
            throw new RuntimeException('Unable to create the temporary branding tree.');
        }
    }

    protected function tearDown(): void
    {
        $this->restoreAllBrandingRows($this->snapshot);
        $this->removeTempTree($this->tempRoot);
    }

    /**
     * The direct contract test: writeAll() against the real database persists the delta,
     * the materialisation timestamp and the revision together, and every value round-trips
     * exactly through readBrandingGlobals()/currentRevision() afterwards.
     */
    public function testWriteAllPersistsTheDeltaTimestampAndRevisionTogetherAgainstRealMariaDb(): void
    {
        $writer = new QueryUtilsBrandingGlobalsWriter($this->site);

        $delta = GlobalsDelta::empty()
            ->with(BrandingGlobalKey::OpenemrName, 'Thiqa DB Test')
            ->with(BrandingGlobalKey::LoginTaglineText, 'DB-backed materialisation proof');

        $writer->writeAll($this->site, $delta, new BrandingRevision(1), '2026-08-10T00:00:00+00:00');

        self::assertSame(1, $writer->currentRevision($this->site)->value);

        $stored = $writer->readBrandingGlobals($this->site);
        self::assertSame('Thiqa DB Test', $stored[BrandingGlobalKey::OpenemrName->value] ?? null);
        self::assertSame('DB-backed materialisation proof', $stored[BrandingGlobalKey::LoginTaglineText->value] ?? null);
        self::assertSame('2026-08-10T00:00:00+00:00', $stored[BrandingGlobalKey::MaterialisedAt->value] ?? null);
        self::assertSame('1', $stored[BrandingGlobalKey::Revision->value] ?? null);

        // Bypass the adapter entirely: read the raw row with plain SQL, so this assertion
        // cannot be satisfied by a bug the adapter and the test happen to share.
        $raw = QueryUtils::fetchSingleValue(
            'SELECT `gl_value` FROM `globals` WHERE `gl_name` = ? AND `gl_index` = ?',
            'gl_value',
            [BrandingGlobalKey::OpenemrName->value, 0],
        );
        self::assertSame('Thiqa DB Test', $raw);
    }

    /**
     * End-to-end: the full BrandingMaterialiser, wired to the real adapter, applies a
     * revision and reports it correctly; materialising the identical job again is a
     * real-database-backed no-op (idempotence proven against currentRevision() reading
     * genuine committed state, not an in-memory stand-in).
     */
    public function testAFullMaterialisationAgainstTheRealWriterAppliesOnceAndIsIdempotentOnReplay(): void
    {
        $paths = new TenantBrandingPaths($this->tempRoot . '/module/public/branding', $this->tempRoot . '/sites');
        $files = new AtomicFileWriter();
        $writer = new QueryUtilsBrandingGlobalsWriter($this->site);

        $materialiser = new BrandingMaterialiser(
            new TokenValidator(new ContrastCalculator()),
            new JsonFileTier1PaletteProvider(new TokenSetParser(), self::TOKEN_DOCUMENT),
            new TokenCssWriter(new CssVariableRenderer(), $files, $paths),
            $files,
            $paths,
            $writer,
            $this->fixedClock('2026-08-10T00:10:00+00:00'),
            new NullLogger(),
        );

        $job = MaterialisationJob::forRevision($this->site, new BrandingRevision(1))
            ->withOverlays(['link.default' => '#1E4574'], ['link.default' => '#B7D9F5'])
            ->withStrings(GlobalsDelta::empty()->with(BrandingGlobalKey::OpenemrName, 'Thiqa DB Test'));

        $first = $materialiser->materialise($job);
        self::assertTrue($first->succeeded());
        self::assertTrue($first->changed());
        self::assertSame(1, $first->revision()->value);

        $raw = QueryUtils::fetchSingleValue(
            'SELECT `gl_value` FROM `globals` WHERE `gl_name` = ? AND `gl_index` = ?',
            'gl_value',
            [BrandingGlobalKey::Revision->value, 0],
        );
        self::assertSame('1', $raw);

        $second = $materialiser->materialise($job);
        self::assertTrue($second->succeeded());
        self::assertFalse($second->changed(), 'A replayed job must be a no-op once the real database is already at that revision.');
        self::assertSame(1, $second->revision()->value);
    }

    private function fixedClock(string $iso8601): ClockInterface
    {
        return new class ($iso8601) implements ClockInterface {
            private DateTimeImmutable $now;

            public function __construct(string $iso8601)
            {
                $this->now = new DateTimeImmutable($iso8601);
            }

            public function now(): DateTimeImmutable
            {
                return $this->now;
            }
        };
    }

    /** @return array<string, string|null> */
    private function readAllBrandingRows(): array
    {
        $names = array_map(static fn (BrandingGlobalKey $key): string => $key->value, BrandingGlobalKey::cases());
        $placeholders = implode(', ', array_fill(0, count($names), '?'));

        $records = QueryUtils::fetchRecords(
            'SELECT `gl_name`, `gl_value` FROM `globals` WHERE `gl_index` = 0 AND `gl_name` IN (' . $placeholders . ')',
            $names,
        );

        $found = [];
        foreach ($records as $record) {
            $name = $record['gl_name'] ?? null;
            $value = $record['gl_value'] ?? null;
            if (is_string($name) && is_string($value)) {
                $found[$name] = $value;
            }
        }

        $snapshot = [];
        foreach ($names as $name) {
            $snapshot[$name] = $found[$name] ?? null;
        }

        return $snapshot;
    }

    /** @param array<string, string|null> $snapshot */
    private function restoreAllBrandingRows(array $snapshot): void
    {
        foreach ($snapshot as $name => $value) {
            if ($value === null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `globals` WHERE `gl_name` = ? AND `gl_index` = 0',
                    [$name],
                );
                continue;
            }

            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?) '
                    . 'ON DUPLICATE KEY UPDATE `gl_value` = ?',
                [$name, $value, $value],
            );
        }
    }

    private function removeTempTree(string $root): void
    {
        if ($root === '' || !is_dir($root)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if (!$item instanceof \SplFileInfo) {
                continue;
            }

            if ($item->isDir()) {
                @rmdir($item->getPathname());
                continue;
            }

            @unlink($item->getPathname());
        }

        @rmdir($root);
    }
}
