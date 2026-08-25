<?php

/**
 * SiteScopeNotice: finding B1 — the tenants a command did NOT act on are reported.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Console;

use OpenEMR\Modules\ThiqaBranding\Console\SiteScopeNotice;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteInventory;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Tenant\SitesFixtureTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

final class SiteScopeNoticeTest extends TestCase
{
    use SitesFixtureTrait;

    protected function tearDown(): void
    {
        $this->removeSites();
    }

    // ------------------------------------------------------------------ the multi-site case

    /**
     * The live shape of finding B1. `sites/rdy0082restore` is a fully branded tenant that a
     * run against `default` leaves untouched; the operator is now told which one.
     */
    public function testEveryOtherConfiguredTenantIsNamed(): void
    {
        $display = $this->render(['default' => 1, 'rdy0082restore' => 1], 'default');

        self::assertStringContainsString('WARNING', $display);
        self::assertStringContainsString('NOT touched', $display);
        self::assertStringContainsString('rdy0082restore', $display);
    }

    public function testTheTenantBeingActedOnIsNotListedAsUntouched(): void
    {
        $display = $this->render(['default' => 1, 'rdy0082restore' => 1], 'rdy0082restore');

        self::assertStringContainsString('default', $display);
        self::assertStringContainsString('1 other configured tenant', $display);
    }

    public function testTheCountIsPluralisedForSeveralTenants(): void
    {
        $display = $this->render(
            ['default' => 1, 'rdy0082restore' => 1, 'clinictwo' => 1],
            'default',
        );

        self::assertStringContainsString('2 other configured tenants', $display);
    }

    /** The remedy is stated, and it is never "we picked one for you". */
    public function testTheNoticeTellsTheOperatorWhatToDoWithoutOfferingADefault(): void
    {
        $display = $this->render(['default' => 1, 'rdy0082restore' => 1], 'default');

        self::assertStringContainsString('--site', $display);
        self::assertStringContainsString('no default tenant', $display);
    }

    // ----------------------------------------------------------------- the single-site case

    /** Graceful, not an empty scary block: one plain line, no warning. */
    public function testASingleTenantInstallationGetsAQuietLine(): void
    {
        $display = $this->render(['default' => 1], 'default');

        self::assertStringContainsString('no other configured tenant', $display);
        self::assertStringNotContainsString('WARNING', $display);
        self::assertStringNotContainsString('NOT touched', $display);
    }

    public function testAnUninstalledSecondDirectoryDoesNotTriggerTheWarning(): void
    {
        $display = $this->render(['default' => 1, 'halfinstalled' => 0, 'notasite' => null], 'default');

        self::assertStringNotContainsString('WARNING', $display);
        self::assertStringNotContainsString('halfinstalled', $display);
    }

    // ---------------------------------------------------------------------- the unknown case

    /** "Cannot read the sites directory" is reported as unknown, never as reassurance. */
    public function testAnUnreadableSitesDirectoryIsLoud(): void
    {
        $io = $this->style($output = new BufferedOutput());

        (new SiteScopeNotice(new SiteInventory(sys_get_temp_dir() . '/thiqa-absent-sites')))
            ->render($io, new SiteId('default'));

        $display = $output->fetch();

        self::assertStringContainsString('WARNING', $display);
        self::assertStringContainsString('unknown', $display);
        self::assertStringNotContainsString('no other configured tenant', $display);
    }

    // ------------------------------------------------------------------- unsupported names

    /**
     * A configured tenant this layer cannot name still has to be surfaced, and its
     * directory name still must not be piped to a terminal.
     */
    public function testAnUnsupportedDirectoryNameIsReportedButNotPrinted(): void
    {
        $display = $this->render(['default' => 1, 'clinic.one' => 1], 'default');

        self::assertStringContainsString('WARNING', $display);
        self::assertStringContainsString('outside the supported site-id character', $display);
        self::assertStringNotContainsString('clinic.one', $display);
    }

    // -------------------------------------------------------------------------- credentials

    /** Nothing read out of sqlconf.php but one integer ever reaches an output stream. */
    public function testNoCredentialFromSqlconfEverReachesTheOutput(): void
    {
        $display = $this->render(['default' => 1, 'rdy0082restore' => 1], 'default');

        self::assertStringNotContainsString($this->fixturePassword(), $display);
        self::assertStringNotContainsString('openemr_rdy0082restore', $display);
        self::assertStringNotContainsString('127.0.0.1', $display);
    }

    // ----------------------------------------------------------------------------- fixtures

    /** @param array<string, int|string|null> $sites */
    private function render(array $sites, string $acting): string
    {
        $root = $this->makeSites($sites);
        $io = $this->style($output = new BufferedOutput());

        (new SiteScopeNotice(new SiteInventory($root)))->render($io, new SiteId($acting));

        return $output->fetch();
    }

    private function style(BufferedOutput $output): SymfonyStyle
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);

        return new SymfonyStyle(new ArrayInput([]), $output);
    }
}
