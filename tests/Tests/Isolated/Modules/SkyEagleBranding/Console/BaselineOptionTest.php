<?php

/**
 * BaselineOption: configurable baseline location, unchanged fail-closed integrity gate.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Console;

use OpenEMR\Modules\SkyEagleBranding\Console\BaselineOption;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

/**
 * These tests exist because `SeedDemoCommand` cannot be exercised without a database, so the
 * baseline gate had to move somewhere reachable to be provable at all (DG-005 / B-03).
 *
 * The point under test is narrow and deliberate: **the location became configurable; the
 * integrity requirement did not move.** Several tests below assert the *absence* of an escape
 * hatch rather than the presence of a feature, because the failure mode being guarded against
 * is a future change that makes a missing or wrong baseline tolerable.
 */
final class BaselineOptionTest extends TestCase
{
    /** Stands in for the recorded development default the command passes to define(). */
    private const RECORDED_DEFAULT = 'C:/openemr-stack/backups/protected/rdy0044a/preseed.sql';

    private string $tempRoot = '';

    /** @var list<string> */
    private array $madeDirs = [];

    protected function setUp(): void
    {
        $this->tempRoot = sys_get_temp_dir() . '/thiqa-baseline-' . bin2hex(random_bytes(6));
        $this->makeDir($this->tempRoot);
    }

    protected function tearDown(): void
    {
        // Deepest-first, so directories are empty by the time they are removed.
        foreach (array_reverse($this->madeDirs) as $dir) {
            foreach (glob($dir . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        foreach (array_reverse($this->madeDirs) as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
        $this->madeDirs = [];
    }

    // ------------------------------------------------------------------ backward compatibility

    /**
     * The whole point of giving the option a default: a caller written against the previous
     * interface — which had no option at all — must keep resolving to the recorded path.
     */
    public function testOmittingTheOptionFallsBackToTheRecordedDefault(): void
    {
        $resolved = BaselineOption::resolve($this->input([]));

        self::assertNull($resolved->error);
        self::assertSame(self::RECORDED_DEFAULT, $resolved->path);
    }

    /**
     * Backward compatibility must not extend to *tolerating* the default when it is absent.
     * On any host that is not the original workstation the recorded path does not exist, and
     * that must still refuse — the same refusal the command produced before this change.
     */
    public function testTheDefaultStillFailsClosedWhenItDoesNotExist(): void
    {
        $resolved = BaselineOption::resolve($this->input([]));

        $problem = $resolved->verify(str_repeat('a', 64));

        self::assertNotNull($problem);
        self::assertStringContainsString('not found', $problem);
    }

    // ------------------------------------------------------------------------ the two failures

    public function testANonexistentPathIsRefused(): void
    {
        $missing = $this->tempRoot . '/does-not-exist.sql';

        $problem = BaselineOption::resolve($this->input(['--baseline-path' => $missing]))
            ->verify(str_repeat('b', 64));

        self::assertNotNull($problem);
        self::assertStringContainsString('not found', $problem);
    }

    public function testAHashMismatchIsRefused(): void
    {
        $path = $this->writeBaseline('baseline.sql', 'the accepted bytes');

        // A real file, at a real path, with the wrong contents. This is the case the integrity
        // check exists for, and it is the one a "just make the path configurable" change is
        // most likely to accidentally drop.
        $problem = BaselineOption::resolve($this->input(['--baseline-path' => $path]))
            ->verify(hash('sha256', 'DIFFERENT bytes'));

        self::assertNotNull($problem);
        self::assertStringContainsString('MISMATCH', $problem);
    }

    public function testAnEmptyOptionValueIsRefusedRatherThanTreatedAsUnset(): void
    {
        // Falling back to the default here would let `--baseline-path=` silently mean
        // "use the developer's path", which is precisely the confusion this option removes.
        $resolved = BaselineOption::resolve($this->input(['--baseline-path' => '']));

        self::assertNull($resolved->path);
        self::assertNotNull($resolved->error);
        self::assertNotNull($resolved->verify(str_repeat('c', 64)));
    }

    public function testAWhitespaceOnlyValueIsRefused(): void
    {
        $resolved = BaselineOption::resolve($this->input(['--baseline-path' => '   ']));

        self::assertNull($resolved->path);
        self::assertNotNull($resolved->error);
    }

    // ------------------------------------------------------------------------- the success path

    public function testAValidPathWithTheAcceptedHashIsAccepted(): void
    {
        $contents = "-- RDY-0044-A preseed baseline\nSELECT 1;\n";
        $path = $this->writeBaseline('baseline.sql', $contents);

        $problem = BaselineOption::resolve($this->input(['--baseline-path' => $path]))
            ->verify(hash('sha256', $contents));

        self::assertNull($problem, 'A present baseline with the accepted digest must be accepted.');
    }

    // --------------------------------------------------------------------- portability of paths

    /**
     * Requirement 9 of the remediation brief. A baseline living under a directory with a space
     * in its name is entirely ordinary on both target platforms, and a naive implementation
     * that splits or shell-quotes the value would break here.
     */
    public function testAPathContainingSpacesWorksEndToEnd(): void
    {
        $dir = $this->tempRoot . '/My Protected Backups';
        $this->makeDir($dir);

        $contents = "-- baseline under a spaced directory\n";
        $path = $dir . '/rdy0044a preseed.sql';
        file_put_contents($path, $contents);

        $resolved = BaselineOption::resolve($this->input(['--baseline-path' => $path]));

        self::assertSame($path, $resolved->path, 'The path must survive resolution byte-for-byte.');
        self::assertNull($resolved->verify(hash('sha256', $contents)));
    }

    /**
     * A Linux-style absolute path is the form the Ubuntu demo target will actually pass. The
     * portable assertion is that resolution neither rejects nor rewrites it; PHP's filesystem
     * functions accept forward slashes on both platforms, which the second half exercises with
     * a real file.
     */
    public function testALinuxStyleForwardSlashPathIsAcceptedVerbatim(): void
    {
        $linuxStyle = '/srv/openemr/backups/protected/rdy0044a/preseed.sql';

        $resolved = BaselineOption::resolve($this->input(['--baseline-path' => $linuxStyle]));

        self::assertNull($resolved->error);
        self::assertSame($linuxStyle, $resolved->path, 'No normalisation, no separator rewriting.');

        // And a forward-slash path that does resolve to a real file verifies normally.
        $contents = "-- forward-slash addressed baseline\n";
        $path = str_replace('\\', '/', $this->writeBaseline('forward-slash.sql', $contents));

        self::assertNull(
            BaselineOption::resolve($this->input(['--baseline-path' => $path]))->verify(hash('sha256', $contents)),
        );
    }

    // ------------------------------------------------------------- the gate cannot be bypassed

    /**
     * Guards the one regression that would matter most: some later change adding a value that
     * means "skip the check". Every input shape must either verify or refuse — never pass
     * without verifying.
     *
     * @param array<string, string> $params
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('bypassAttemptProvider')]
    public function testNoInputShapeSkipsVerification(array $params): void
    {
        $problem = BaselineOption::resolve($this->input($params))->verify(hash('sha256', 'accepted'));

        self::assertNotNull(
            $problem,
            'No option value may result in the baseline gate passing without a matching file.',
        );
    }

    /**
     * @return array<string, array{array<string, string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function bypassAttemptProvider(): array
    {
        return [
            'omitted (recorded default, absent here)' => [[]],
            'empty string'                            => [['--baseline-path' => '']],
            'whitespace only'                         => [['--baseline-path' => '   ']],
            'a directory rather than a file'          => [['--baseline-path' => sys_get_temp_dir()]],
            'a path that does not exist'              => [['--baseline-path' => '/nonexistent/baseline.sql']],
            'the literal word skip'                   => [['--baseline-path' => 'skip']],
            'a null-byte injection attempt'           => [['--baseline-path' => "/tmp/ok.sql\0ignored"]],
        ];
    }

    // ------------------------------------------------------------------------------- the option

    public function testTheOptionIsDeclaredAsRequiringAValue(): void
    {
        $option = BaselineOption::define(self::RECORDED_DEFAULT);

        self::assertSame(BaselineOption::NAME, $option->getName());
        self::assertTrue($option->isValueRequired(), 'A bare --baseline-path with no value is meaningless.');
        self::assertSame(self::RECORDED_DEFAULT, $option->getDefault());
        self::assertNull($option->getShortcut(), 'No shortcut: provisioning flags should be spelled out.');
    }

    /**
     * The class must never itself name a machine — the default is supplied by the caller, which
     * is what keeps the recorded workstation path in the command it belongs to rather than
     * spreading it into shared code.
     */
    public function testDefineDoesNotEmbedAnyHostPath(): void
    {
        $option = BaselineOption::define('/anything/at/all.sql');

        self::assertSame('/anything/at/all.sql', $option->getDefault());
        self::assertStringNotContainsString('openemr-stack', (string) $option->getDescription());
    }

    // ------------------------------------------------------------------------------- helpers

    /** @param array<string, string> $params */
    private function input(array $params): InputInterface
    {
        return new ArrayInput($params, new InputDefinition([BaselineOption::define(self::RECORDED_DEFAULT)]));
    }

    private function writeBaseline(string $name, string $contents): string
    {
        $path = $this->tempRoot . '/' . $name;
        file_put_contents($path, $contents);

        return $path;
    }

    private function makeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0o700, true);
        }
        $this->madeDirs[] = $dir;
    }
}
