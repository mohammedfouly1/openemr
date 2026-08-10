<?php

/**
 * MaterialiseCommand: mandatory tenant, exit codes per outcome, structured logging.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Console;

use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\LogoValidator;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\RasterImageReader;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\SvgInspector;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\ThiqaBranding\Console\MaterialiseCommand;
use OpenEMR\Modules\ThiqaBranding\Console\SiteOption;
use OpenEMR\Modules\ThiqaBranding\Materialisation\AtomicFileWriter;
use OpenEMR\Modules\ThiqaBranding\Materialisation\BrandingMaterialiser;
use OpenEMR\Modules\ThiqaBranding\Materialisation\JsonFileTier1PaletteProvider;
use OpenEMR\Modules\ThiqaBranding\Materialisation\TenantBrandingPaths;
use OpenEMR\Modules\ThiqaBranding\Materialisation\TokenCssWriter;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthCheck;
use OpenEMR\Modules\ThiqaBranding\Observability\FilesystemStylesheetProbe;
use OpenEMR\Modules\ThiqaBranding\Observability\MaterialisationLogger;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use OpenEMR\Modules\ThiqaBranding\Token\CssVariableRenderer;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSetParser;
use OpenEMR\Modules\ThiqaBranding\Token\TokenValidator;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation\CertifiedAssetTrait;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation\FrozenClock;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation\RecordingGlobalsWriter;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation\TemporaryTreeTrait;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Observability\RecordingLogger;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Token\ContrastCalculatorStub;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

/**
 * BrandingMaterialiser is `final readonly` and cannot be doubled, which is the right
 * design and simply means the command is exercised against a real one wired to a
 * temporary tree and an in-memory globals table — the same rig BrandingMaterialiserTest
 * uses. The outcomes under test are produced by driving that rig into each state rather
 * than by stubbing a return value, so the exit codes are asserted against outcomes the
 * materialiser genuinely produces.
 */
final class MaterialiseCommandTest extends TestCase
{
    use TemporaryTreeTrait;
    use CertifiedAssetTrait;

    private const SITE = 'tenantalpha';

    private const NOW = '2026-08-09T12:00:00+00:00';

    /** Path to the shipped Tier 1 document, relative to this file. */
    private const TOKEN_DOCUMENT = '/../../../../../../brand/tokens/thiqa-tokens.json';

    private SiteId $site;

    private RecordingGlobalsWriter $globals;

    private TenantBrandingPaths $paths;

    private RecordingLogger $recorder;

    private MaterialiseCommand $command;

    protected function setUp(): void
    {
        $root = $this->makeTree();

        $this->site = new SiteId(self::SITE);
        $this->globals = new RecordingGlobalsWriter($this->site);
        $this->paths = new TenantBrandingPaths($root . '/module/public/branding', $root . '/sites');
        $this->recorder = new RecordingLogger();

        $files = new AtomicFileWriter();
        $clock = FrozenClock::at(self::NOW);

        $materialiser = new BrandingMaterialiser(
            new TokenValidator(new ContrastCalculatorStub()),
            new JsonFileTier1PaletteProvider(new TokenSetParser(), __DIR__ . self::TOKEN_DOCUMENT),
            new TokenCssWriter(new CssVariableRenderer(), $files, $this->paths),
            $files,
            $this->paths,
            $this->globals,
            $clock,
            new NullLogger(),
        );

        $health = new BrandingHealthCheck(
            $this->globals,
            new FilesystemStylesheetProbe($this->paths),
            $clock,
            new NullLogger(),
        );

        $this->command = new MaterialiseCommand(
            $materialiser,
            $health,
            new MaterialisationLogger($this->recorder),
            new LogoValidator(new RasterImageReader(), new SvgInspector(), new NullLogger()),
        );
    }

    protected function tearDown(): void
    {
        $this->removeTree();
    }

    // -------------------------------------------------------------------- tenant scoping

    /**
     * Tenant scope is a locked constraint, and the CLI is where an operator could most
     * easily forget it. No site means no run, and nothing is read, staged or written.
     */
    public function testItRefusesToRunWithoutASite(): void
    {
        $tester = $this->tester();

        $tester->execute([], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('--site', $tester->getDisplay());
        self::assertSame([], $this->globals->operations);
        self::assertSame([], $this->globals->stored);
        self::assertSame([], $this->recorder->records);
        self::assertSame([], $this->filesUnder($this->paths->tokenCssDirectory($this->site)));
    }

    /**
     * Every other OpenEMR command defaults `--site` to `default`. A materialisation run
     * that silently targets a fallback tenant is precisely the accident tenant scoping
     * exists to prevent, so this option has no default at all.
     */
    public function testTheSiteOptionHasNoDefault(): void
    {
        $option = $this->command->getDefinition()->getOption(SiteOption::NAME);

        self::assertTrue($option->isValueRequired());
        self::assertNull($option->getDefault());
    }

    public function testAMalformedSiteIsRefusedWithoutBeingEchoedBack(): void
    {
        $tester = $this->tester();

        $tester->execute(['--site' => '../tenantbeta'], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringNotContainsString('tenantbeta', $tester->getDisplay());
        self::assertSame([], $this->globals->operations);
    }

    // ------------------------------------------------------------------------ exit codes

    public function testAnAppliedRunExitsZeroAndMakesTheRevisionLive(): void
    {
        $tester = $this->tester();

        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('applied', $tester->getDisplay());
        self::assertSame('1', $this->globals->stored[BrandingGlobalKey::Revision->value] ?? null);
        self::assertFileExists($this->paths->tokenCssFile($this->site, ThemeVariant::Light));
        self::assertFileExists($this->paths->tokenCssFile($this->site, ThemeVariant::Dark));
    }

    public function testTheTargetRevisionDefaultsToOneAboveTheLiveOne(): void
    {
        $this->globals->stored[BrandingGlobalKey::Revision->value] = '4';

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame('5', $this->globals->stored[BrandingGlobalKey::Revision->value] ?? null);
    }

    /**
     * Idempotence made operational: a target that is already live is a success, so a cron
     * retry loop converges instead of alarming.
     */
    public function testAnAlreadyAppliedRevisionExitsZeroAsUnchanged(): void
    {
        $this->globals->stored[BrandingGlobalKey::Revision->value] = '5';

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE, '--revision' => '3'], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('unchanged', $tester->getDisplay());
        self::assertSame('5', $this->globals->stored[BrandingGlobalKey::Revision->value] ?? null);
    }

    /**
     * A transient fault: the last write of the transaction fails, the run unwinds, and the
     * previous revision stays live. Exit 1 tells a caller the same payload is worth retrying.
     */
    public function testATransientFaultExitsOneAndLeavesThePreviousRevisionLive(): void
    {
        $this->globals->failOnWriteOf = BrandingGlobalKey::Revision->value;

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('failed', $tester->getDisplay());
        self::assertArrayNotHasKey(BrandingGlobalKey::Revision->value, $this->globals->stored);
    }

    /**
     * A refused payload is exit 2, not exit 1: retrying it is pointless, so a caller must
     * be able to tell it apart from a transient fault without reading the output.
     */
    public function testARefusedPayloadExitsTwo(): void
    {
        $payload = $this->writePayload('{"strings": {"saas_branding_revision": "9"}}');

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE, '--payload' => $payload], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('Rejections', $tester->getDisplay());
        self::assertArrayNotHasKey(BrandingGlobalKey::Revision->value, $this->globals->stored);
    }

    public function testAValidPayloadOverlayIsAppliedToTheJob(): void
    {
        $payload = $this->writePayload(
            '{"light": {"link.default": "#1E4574"}, "dark": {"link.default": "#B7D9F5"},'
            . ' "strings": {"openemr_name": "Thiqa"}}',
        );

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE, '--payload' => $payload], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame('Thiqa', $this->globals->stored[BrandingGlobalKey::OpenemrName->value] ?? null);
        self::assertStringContainsString(
            '#1E4574',
            $this->readFile($this->paths->tokenCssFile($this->site, ThemeVariant::Light)),
        );
    }

    // --------------------------------------------------------------------------- assets

    /**
     * End-to-end proof that a JSON-named path becomes a placed file, going through the
     * real LogoValidator via MaterialiseCommand::resolveAssets() -- not through
     * CertifiedAssetTrait's own internal validator call, which would only prove the
     * validator works and say nothing about the command wiring (audit finding AR-P2-002).
     */
    public function testAValidAssetInThePayloadIsPlacedAndTheRevisionApplied(): void
    {
        $slot = LogoSlot::CoreLoginPrimary;
        $dimensions = $slot->expectedDimensions() ?? ['width' => 64, 'height' => 64];
        $bytes = self::validPngBytes($dimensions['width'], $dimensions['height']);

        $sourcePath = $this->treeRoot() . '/staged-logo.png';
        if (file_put_contents($sourcePath, $bytes) === false) {
            throw new RuntimeException('Unable to stage the source asset fixture.');
        }

        $payload = $this->writePayload((string) json_encode([
            'assets' => [
                ['slot' => $slot->value, 'path' => $sourcePath],
            ],
        ]));

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE, '--payload' => $payload], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());

        $placedFile = $this->paths->logoDirectory($this->site, $slot) . '/logo.png';
        self::assertFileExists($placedFile);
        self::assertSame($bytes, file_get_contents($placedFile));
    }

    /**
     * A candidate that fails real validation (here: wrong dimensions for the slot) must
     * refuse the whole run -- no file placed, no globals touched, revision unchanged --
     * exactly like a rejected token overlay. Proves the command cannot be talked into
     * placing bytes LogoValidator would refuse.
     */
    public function testAnAssetThatFailsValidationRefusesTheWholeRunAndWritesNothing(): void
    {
        $slot = LogoSlot::CoreLoginPrimary;
        // The slot expects 1053x390; a 10x10 image fails DimensionMismatch.
        $bytes = self::validPngBytes(10, 10);

        $sourcePath = $this->treeRoot() . '/wrong-size-logo.png';
        if (file_put_contents($sourcePath, $bytes) === false) {
            throw new RuntimeException('Unable to stage the source asset fixture.');
        }

        $payload = $this->writePayload((string) json_encode([
            'assets' => [
                ['slot' => $slot->value, 'path' => $sourcePath],
            ],
        ]));

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE, '--payload' => $payload], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('refused', $tester->getDisplay());
        self::assertSame([], $this->globals->operations);
        self::assertSame([], $this->filesUnder($this->paths->logoDirectory($this->site, $slot)));
    }

    /**
     * A payload naming an unknown slot is refused by JobPayload's own parsing, before the
     * command ever asks the validator to read a file.
     */
    public function testAnAssetNamingAnUnknownSlotIsRefusedBeforeValidationRuns(): void
    {
        $payload = $this->writePayload((string) json_encode([
            'assets' => [
                ['slot' => 'not/a/real/slot', 'path' => $this->treeRoot() . '/whatever.png'],
            ],
        ]));

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE, '--payload' => $payload], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('not a branding logo slot', $tester->getDisplay());
        self::assertSame([], $this->globals->operations);
    }

    // ------------------------------------------------------------- invocation-level errors

    public function testANonNumericRevisionIsRefusedBeforeAnythingRuns(): void
    {
        $tester = $this->tester();

        $tester->execute(['--site' => self::SITE, '--revision' => 'latest'], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertSame([], $this->globals->operations);
        self::assertSame([], $this->recorder->records);
    }

    public function testRevisionZeroIsRefused(): void
    {
        $tester = $this->tester();

        $tester->execute(['--site' => self::SITE, '--revision' => '0'], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertSame([], $this->globals->operations);
    }

    public function testAnUnreadablePayloadFileIsRefused(): void
    {
        $tester = $this->tester();

        $tester->execute(
            ['--site' => self::SITE, '--payload' => $this->treeRoot() . '/nothing-here.json'],
            ['interactive' => false],
        );

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertSame([], $this->globals->operations);
    }

    public function testAPayloadNamingAnUnknownGlobalIsRefused(): void
    {
        $payload = $this->writePayload('{"strings": {"not_a_branding_global": "x"}}');

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE, '--payload' => $payload], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('not_a_branding_global', $tester->getDisplay());
        self::assertSame([], $this->globals->operations);
    }

    public function testMalformedPayloadJsonIsRefusedWithoutParserDetail(): void
    {
        $payload = $this->writePayload('{"light": ');

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE, '--payload' => $payload], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('not valid JSON', $tester->getDisplay());
    }

    // -------------------------------------------------------------------------- observability

    public function testEveryLogEventCarriesAContextArrayAndNoInterpolatedValue(): void
    {
        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        self::assertCount(2, $this->recorder->records);
        self::assertCount(1, $this->recorder->withEvent(MaterialisationLogger::EVENT_STARTED));
        self::assertCount(1, $this->recorder->withEvent(MaterialisationLogger::EVENT_PREFIX . 'applied'));

        foreach ($this->recorder->records as $record) {
            self::assertNotSame([], $record['context']);
            self::assertSame(self::SITE, $record['context']['site'] ?? null);
            self::assertSame(1, $record['context']['target_revision'] ?? null);
            self::assertStringNotContainsString(self::SITE, $record['message']);
        }
    }

    public function testTheTerminalEventNamesTheOutcome(): void
    {
        $this->globals->failOnWriteOf = BrandingGlobalKey::Revision->value;

        $tester = $this->tester();
        $tester->execute(['--site' => self::SITE], ['interactive' => false]);

        $terminal = $this->recorder->withEvent(MaterialisationLogger::EVENT_PREFIX . 'failed');

        self::assertCount(1, $terminal);
        self::assertSame('failed', $terminal[0]['context']['outcome'] ?? null);
        self::assertTrue($terminal[0]['context']['retryable'] ?? null);
        self::assertSame('error', $terminal[0]['level']);
    }

    // ---------------------------------------------------------------------------- fixtures

    private function tester(): CommandTester
    {
        return new CommandTester($this->command);
    }

    private function writePayload(string $json): string
    {
        $path = $this->treeRoot() . '/payload.json';
        if (file_put_contents($path, $json) === false) {
            throw new RuntimeException('Unable to write the payload fixture.');
        }

        return $path;
    }

    private function readFile(string $path): string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read a generated stylesheet.');
        }

        return $contents;
    }
}
