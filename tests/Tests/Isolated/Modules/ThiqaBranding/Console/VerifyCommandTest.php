<?php

/**
 * VerifyCommand: mandatory tenant, read-only behaviour, health-check exit codes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Console;

use OpenEMR\Modules\ThiqaBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\ThiqaBranding\Console\SiteOption;
use OpenEMR\Modules\ThiqaBranding\Console\VerifyCommand;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthCheck;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation\FrozenClock;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation\RecordingGlobalsWriter;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Observability\InMemoryStylesheetProbe;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Observability\RecordingLogger;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Observability\UnreadableGlobalsWriter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

final class VerifyCommandTest extends TestCase
{
    private const SITE = 'tenantalpha';

    private const NOW = '2026-08-09T12:00:00+00:00';

    private const FRESH_STAMP = '2026-08-09T11:00:00+00:00';

    private SiteId $site;

    private RecordingGlobalsWriter $globals;

    private RecordingLogger $logger;

    protected function setUp(): void
    {
        $this->site = new SiteId(self::SITE);
        $this->globals = new RecordingGlobalsWriter($this->site);
        $this->logger = new RecordingLogger();
    }

    // -------------------------------------------------------------------- tenant scoping

    public function testItRefusesToRunWithoutASite(): void
    {
        $tester = $this->tester(new InMemoryStylesheetProbe());

        $tester->execute([], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('--site', $tester->getDisplay());
        self::assertSame([], $this->globals->operations);
    }

    /**
     * Every other OpenEMR command defaults `--site` to `default`. Branding must not: the
     * default is what turns "I forgot the flag" into "I inspected the wrong tenant".
     */
    public function testTheSiteOptionHasNoDefault(): void
    {
        $option = $this->command(new InMemoryStylesheetProbe())
            ->getDefinition()
            ->getOption(SiteOption::NAME);

        self::assertTrue($option->isValueRequired());
        self::assertNull($option->getDefault());
    }

    public function testItRefusesAMalformedSiteWithoutEchoingIt(): void
    {
        $tester = $this->tester(new InMemoryStylesheetProbe());

        $tester->execute(['--site' => '../other-tenant'], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringNotContainsString('other-tenant', $tester->getDisplay());
        self::assertSame([], $this->globals->operations);
    }

    // ------------------------------------------------------------------------ exit codes

    public function testAConsistentTenantExitsZero(): void
    {
        $this->store(4, self::FRESH_STAMP);
        $tester = $this->tester(new InMemoryStylesheetProbe());

        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('self-consistent', $tester->getDisplay());
    }

    public function testATenantThatHasNeverBeenMaterialisedExitsZero(): void
    {
        $tester = $this->tester(new InMemoryStylesheetProbe(light: false, dark: false));

        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('never materialised', $tester->getDisplay());
    }

    public function testAnInconsistentTenantExitsNonZero(): void
    {
        $this->globals->stored[BrandingGlobalKey::Revision->value] = '4';
        $tester = $this->tester(new InMemoryStylesheetProbe());

        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('Inconsistencies', $tester->getDisplay());
    }

    /**
     * The finding S2-P1-18 case: generated files on disk with no revision. Nothing serves
     * those files, so the command has to print them, name them as unserved, and still exit
     * zero — the old behaviour failed a tenant whose rendering was entirely correct.
     */
    public function testStaticArtefactAdvisoriesArePrintedButExitZero(): void
    {
        $tester = $this->tester(new InMemoryStylesheetProbe(light: true, dark: true));

        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        $display = $tester->getDisplay();

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('Advisories', $display);
        self::assertStringContainsString('not served', $display);
        self::assertStringNotContainsString('Inconsistencies', $display);
    }

    /** What the browser would fetch is stated outright, not left to be inferred. */
    public function testTheServedOverlayIsReportedExplicitly(): void
    {
        $this->store(4, self::FRESH_STAMP);
        $tester = $this->tester(new InMemoryStylesheetProbe());

        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertStringContainsString('Serves tenant overlay', $tester->getDisplay());
        self::assertStringContainsString('rendering the product palette', $tester->getDisplay());
    }

    public function testAnUnreadableTenantExitsNonZero(): void
    {
        $command = new VerifyCommand(new BrandingHealthCheck(
            new UnreadableGlobalsWriter(),
            new InMemoryStylesheetProbe(),
            FrozenClock::at(self::NOW),
            $this->logger,
        ));

        $tester = new CommandTester($command);
        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringNotContainsString(UnreadableGlobalsWriter::FAILURE_MESSAGE, $tester->getDisplay());
    }

    public function testAStaleButCoherentTenantExitsZero(): void
    {
        $this->store(2, '2026-06-01T12:00:00+00:00');
        $tester = $this->tester(new InMemoryStylesheetProbe());

        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('stale', $tester->getDisplay());
    }

    // -------------------------------------------------------------------------- read-only

    /**
     * The claim the command's docblock makes, asserted on every path it has: the
     * transaction log stays empty and the stored globals come back byte-identical.
     */
    public function testNoInvocationEverMutatesTenantState(): void
    {
        $this->store(4, self::FRESH_STAMP);
        $before = $this->globals->stored;

        foreach ([[], ['--site' => self::SITE], ['--site' => 'nosuchtenant!!']] as $arguments) {
            $probe = new InMemoryStylesheetProbe(light: false, dark: true);
            $tester = $this->tester($probe);
            $tester->execute($arguments, ['interactive' => false]);
        }

        self::assertSame([], $this->globals->operations);
        self::assertSame($before, $this->globals->stored);
    }

    // ---------------------------------------------------------------------------- fixtures

    private function command(InMemoryStylesheetProbe $probe): VerifyCommand
    {
        return new VerifyCommand(new BrandingHealthCheck(
            $this->globals,
            $probe,
            FrozenClock::at(self::NOW),
            $this->logger,
        ));
    }

    private function tester(InMemoryStylesheetProbe $probe): CommandTester
    {
        return new CommandTester($this->command($probe));
    }

    private function store(int $revision, string $stamp): void
    {
        $this->globals->stored[BrandingGlobalKey::Revision->value] = (string) $revision;
        $this->globals->stored[BrandingGlobalKey::MaterialisedAt->value] = $stamp;
    }
}
