<?php

/**
 * SiteInventory: finding B1 — a second configured tenant must never be invisible.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Tenant;

use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteInventory;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

final class SiteInventoryTest extends TestCase
{
    use SitesFixtureTrait;

    protected function tearDown(): void
    {
        $this->removeSites();
    }

    // ------------------------------------------------------------------ the finding itself

    /**
     * The live shape of finding B1: `sites/default` and `sites/rdy0082restore`, both with
     * `$config = 1`, and a branding run that named only the first.
     */
    public function testASecondConfiguredSiteIsFound(): void
    {
        $root = $this->makeSites(['default' => 1, 'rdy0082restore' => 1]);

        $report = (new SiteInventory($root))->take();

        self::assertTrue($report->readable);
        self::assertSame(
            ['default', 'rdy0082restore'],
            array_map(static fn (SiteId $s): string => $s->value, $report->configured),
        );
    }

    public function testTheSiteBeingActedOnIsExcludedFromTheOthers(): void
    {
        $root = $this->makeSites(['default' => 1, 'rdy0082restore' => 1, 'clinictwo' => 1]);

        $others = (new SiteInventory($root))->take()->othersThan(new SiteId('default'));

        self::assertSame(
            ['clinictwo', 'rdy0082restore'],
            array_map(static fn (SiteId $s): string => $s->value, $others),
        );
    }

    /**
     * A `--site` naming a tenant that is not configured must not also blind the report to
     * the tenants that are.
     */
    public function testAnActingSiteOutsideTheInventoryHidesNothing(): void
    {
        $root = $this->makeSites(['default' => 1, 'rdy0082restore' => 1]);

        $others = (new SiteInventory($root))->take()->othersThan(new SiteId('nosuchtenant'));

        self::assertCount(2, $others);
    }

    // ------------------------------------------------------------------- what does not count

    public function testASiteWithConfigZeroIsNotConfigured(): void
    {
        $root = $this->makeSites(['default' => 1, 'halfinstalled' => 0]);

        $report = (new SiteInventory($root))->take();

        self::assertSame(
            ['default'],
            array_map(static fn (SiteId $s): string => $s->value, $report->configured),
        );
        self::assertTrue($report->isSingleTenant(new SiteId('default')));
    }

    public function testADirectoryWithNoSqlconfIsNotConfigured(): void
    {
        $root = $this->makeSites(['default' => 1, 'notasite' => null]);

        $report = (new SiteInventory($root))->take();

        self::assertSame(
            ['default'],
            array_map(static fn (SiteId $s): string => $s->value, $report->configured),
        );
    }

    public function testAFileInTheSitesDirectoryIsIgnored(): void
    {
        $root = $this->makeSites(['default' => 1]);
        file_put_contents($root . '/README.md', 'not a tenant');

        $report = (new SiteInventory($root))->take();

        self::assertCount(1, $report->configured);
    }

    /**
     * A line-oriented match would read the commented line and report an abandoned tenant as
     * live. The tokenizer knows the difference, and the last real assignment wins.
     */
    public function testACommentedOutAssignmentDoesNotCount(): void
    {
        $root = $this->makeSites([
            'default' => 1,
            'commented' => "<?php\n// \$config = 1;\n\$config = 0;\n",
        ]);

        $report = (new SiteInventory($root))->take();

        self::assertSame(
            ['default'],
            array_map(static fn (SiteId $s): string => $s->value, $report->configured),
        );
    }

    public function testTheLastAssignmentWins(): void
    {
        $root = $this->makeSites(['reinstated' => "<?php\n\$config = 0;\n\$config = 1;\n"]);

        self::assertCount(1, (new SiteInventory($root))->take()->configured);
    }

    public function testAQuotedOneStillCounts(): void
    {
        $root = $this->makeSites(['handedited' => "<?php\n\$config = '1';\n"]);

        self::assertCount(1, (new SiteInventory($root))->take()->configured);
    }

    /** An expression the parser declines to evaluate is reported absent, never asserted in. */
    public function testAnUnevaluatableAssignmentIsNotConfigured(): void
    {
        $root = $this->makeSites(['computed' => "<?php\n\$config = SOME_CONSTANT;\n"]);

        self::assertSame([], (new SiteInventory($root))->take()->configured);
    }

    public function testAComparisonIsNotAnAssignment(): void
    {
        $root = $this->makeSites(['comparing' => "<?php\nif (\$config == 1) { echo 'x'; }\n"]);

        self::assertSame([], (new SiteInventory($root))->take()->configured);
    }

    // ------------------------------------------------------------------- unsupported names

    /**
     * A dotted site id is a real OpenEMR tenant (RB-05) that this layer cannot name. Dropping
     * it silently would be the same invisibility defect in a new place, so it is counted.
     */
    public function testAConfiguredSiteWithAnUnsupportedNameIsCountedNotDropped(): void
    {
        $root = $this->makeSites(['default' => 1, 'clinic.one' => 1]);

        $report = (new SiteInventory($root))->take();

        self::assertSame(
            ['default'],
            array_map(static fn (SiteId $s): string => $s->value, $report->configured),
        );
        self::assertSame(1, $report->unsupportedNameCount);
        self::assertFalse($report->isSingleTenant(new SiteId('default')));
    }

    // ------------------------------------------------------------------------ unknown state

    /**
     * "Could not read" and "found one tenant" are different facts. Collapsing them would
     * hand the operator the same false reassurance the finding is about.
     */
    public function testAnAbsentSitesDirectoryIsUnknownRatherThanEmpty(): void
    {
        $report = (new SiteInventory(sys_get_temp_dir() . '/thiqa-no-such-sites-dir'))->take();

        self::assertFalse($report->readable);
        self::assertSame([], $report->configured);
        self::assertFalse($report->isSingleTenant(new SiteId('default')));
    }

    // ------------------------------------------------------------------------- the contract

    /** Filesystem only. A tenant whose database is down is the one most likely forgotten. */
    public function testTheScannerNeverReachesForADatabase(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../../../interface/modules/custom_modules/'
            . 'oe-module-thiqa-branding/src/Tenant/SiteInventory.php',
        );

        self::assertIsString($source);

        foreach (['QueryUtils', 'sqlStatement', 'mysqli', 'new PDO', 'Doctrine', 'getConnection'] as $forbidden) {
            self::assertStringNotContainsString(
                $forbidden,
                $source,
                'SiteInventory must stay filesystem-only.',
            );
        }
    }

    /** sqlconf.php is parsed, never included: no credential may be defined into any scope. */
    public function testTheScannerNeverIncludesTheConfigurationFile(): void
    {
        $root = $this->makeSites(['default' => 1]);

        (new SiteInventory($root))->take();

        self::assertArrayNotHasKey('pass', $GLOBALS);
        self::assertArrayNotHasKey('sqlconf', $GLOBALS);
    }
}
