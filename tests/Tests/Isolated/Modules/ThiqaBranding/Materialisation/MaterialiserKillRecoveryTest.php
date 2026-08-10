<?php

/**
 * Proves a real, OS-level process kill between file commit and database write is safe.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation;

use OpenEMR\Modules\ThiqaBranding\Accessibility\ContrastCalculator;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\ThiqaBranding\Materialisation\AtomicFileWriter;
use OpenEMR\Modules\ThiqaBranding\Materialisation\BrandingMaterialiser;
use OpenEMR\Modules\ThiqaBranding\Materialisation\JsonFileTier1PaletteProvider;
use OpenEMR\Modules\ThiqaBranding\Materialisation\MaterialisationJob;
use OpenEMR\Modules\ThiqaBranding\Materialisation\TenantBrandingPaths;
use OpenEMR\Modules\ThiqaBranding\Materialisation\TokenCssWriter;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use OpenEMR\Modules\ThiqaBranding\Token\CssVariableRenderer;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSetParser;
use OpenEMR\Modules\ThiqaBranding\Token\TokenValidator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

require_once __DIR__ . '/materialisation_autoloader.php';

/**
 * AR-P2-006, the last standing open finding from the two Phase 2 audits: nothing had
 * ever proven that a process killed between the file renames (steps 5a/5b) and the
 * database write (steps 5c/5d) leaves a safely recoverable state. Unit tests using
 * RecordingGlobalsWriter cannot prove this -- they can only simulate a *thrown
 * exception*, which still runs BrandingMaterialiser's own catch/unwind path. A real
 * kill runs *nothing* after the instant it lands: no catch, no finally, no destructor.
 * This test reproduces exactly that by genuinely terminating a child PHP process at the
 * OS level (TerminateProcess on Windows; the same ungraceful-stop guarantee SIGKILL
 * gives on POSIX) at the moment its database write would begin, synchronised through a
 * sentinel file rather than a timing guess so the test is deterministic rather than
 * flaky.
 */
final class MaterialiserKillRecoveryTest extends TestCase
{
    use TemporaryTreeTrait;

    private const SITE = 'killrecoverytenant';

    /** Seconds to wait for the subprocess to reach its sentinel before failing the test. */
    private const SENTINEL_TIMEOUT_SECONDS = 15;

    /** Seconds to wait for the terminated subprocess to actually exit. */
    private const EXIT_TIMEOUT_SECONDS = 10;

    protected function tearDown(): void
    {
        $this->removeTree();
    }

    public function testAKilledProcessLeavesFilesCommittedWithNoDatabaseWriteAndTheJobIsSafelyRetryable(): void
    {
        $root = $this->makeTree();
        $site = new SiteId(self::SITE);
        $sentinel = $root . '/kill-sentinel';

        $outcome = $this->runAndKillSubprocess($root, self::SITE, $sentinel);

        self::assertTrue(
            $outcome->reachedSentinel,
            'The subprocess never reached its database-write sentinel; the kill point was not exercised.',
        );
        self::assertNotSame(
            77,
            $outcome->exitCode,
            'The subprocess ran to completion instead of being terminated -- the kill did not take effect.',
        );
        self::assertNotSame(
            66,
            $outcome->exitCode,
            'The subprocess hit its own safety timeout instead of being terminated by the test harness.',
        );

        $paths = new TenantBrandingPaths($root . '/module/public/branding', $root . '/sites');

        // The property under test: bytes are already at their live path even though no
        // database write ever happened, because the kill landed strictly after 5a/5b.
        self::assertFileExists($paths->tokenCssFile($site, ThemeVariant::Light));
        self::assertStringContainsString(
            '#1E4574',
            (string) file_get_contents($paths->tokenCssFile($site, ThemeVariant::Light)),
        );

        // Recovery: a healthy retry of the *same* job, against the *same* tree, from a
        // writer that -- like the real database, which was never written -- still
        // reports revision 0, must converge cleanly to revision 1.
        $files = new AtomicFileWriter();
        $globals = new RecordingGlobalsWriter($site);
        $materialiser = new BrandingMaterialiser(
            new TokenValidator(new ContrastCalculator()),
            new JsonFileTier1PaletteProvider(
                new TokenSetParser(),
                __DIR__ . '/../../../../../../brand/tokens/thiqa-tokens.json',
            ),
            new TokenCssWriter(new CssVariableRenderer(), $files, $paths),
            $files,
            $paths,
            $globals,
            FrozenClock::at('2026-08-09T12:05:00+00:00'),
            new NullLogger(),
        );

        $job = MaterialisationJob::forRevision($site, new BrandingRevision(1))
            ->withOverlays(['link.default' => '#1E4574'], ['link.default' => '#B7D9F5']);

        $result = $materialiser->materialise($job);

        self::assertTrue($result->succeeded());
        self::assertTrue($result->changed());
        self::assertSame(1, $result->revision()->value);
        self::assertSame('1', $globals->stored[BrandingGlobalKey::Revision->value] ?? null);
    }

    private function runAndKillSubprocess(string $root, string $site, string $sentinel): KillOutcome
    {
        $php = getenv('PHP_BINARY') ?: PHP_BINARY;
        $script = __DIR__ . '/kill_point_subprocess.php';

        $process = new Process([$php, $script, $root, $site, $sentinel]);
        $process->setTimeout(self::SENTINEL_TIMEOUT_SECONDS + self::EXIT_TIMEOUT_SECONDS);
        $process->start();

        $deadline = microtime(true) + self::SENTINEL_TIMEOUT_SECONDS;
        $reachedSentinel = false;
        while (microtime(true) < $deadline) {
            if (is_file($sentinel)) {
                $reachedSentinel = true;
                break;
            }
            usleep(10000);
        }

        if ($reachedSentinel) {
            // Immediate hard stop: a 0 timeout skips waiting for a graceful exit and
            // escalates straight to the OS-level kill (TerminateProcess on Windows,
            // SIGKILL on POSIX after SIGTERM is given no time to be handled).
            $process->stop(0);
        }

        $process->wait();

        return new KillOutcome($reachedSentinel, $process->getExitCode());
    }
}
