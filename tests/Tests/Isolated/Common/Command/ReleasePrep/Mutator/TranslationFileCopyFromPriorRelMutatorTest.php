<?php

/**
 * Tests for TranslationFileCopyFromPriorRelMutator: copies the
 * currentLanguage_utf8.sql blob from the prior rel branch via git
 * fetch + git show. The Process factory is injected so these tests
 * don't touch the network or require a real git repo.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\Mutator\TranslationFileCopyFromPriorRelMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[Group('isolated')]
#[Group('release-prep')]
final class TranslationFileCopyFromPriorRelMutatorTest extends TestCase
{
    private const RELATIVE_PATH = 'contrib/util/language_translations/currentLanguage_utf8.sql';
    private const SUPPLEMENT_PATH = 'contrib/util/language_translations/durableTranslationContracts_utf8.sql';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-tfc-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir . '/contrib/util/language_translations/contracts', 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir');
        }
        $this->writeContract();
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testReplacesLocalFileWithPriorRelContent(): void
    {
        $this->writeLocal('-- local: master tip content\n');

        $priorBlob = "-- prior rel-810 tip blob\nINSERT INTO lang ...\n";
        $factory = $this->stubProcessFactory(true, $priorBlob, '');

        $result = (new TranslationFileCopyFromPriorRelMutator($factory))->apply($this->context('rel-810'));
        self::assertTrue($result->changed());
        self::assertSame($priorBlob, $this->readLocal());
    }

    public function testIdempotentWhenLocalAlreadyMatchesPriorBlob(): void
    {
        $priorBlob = "-- already matches\n";
        $this->writeLocal($priorBlob);

        $factory = $this->stubProcessFactory(true, $priorBlob, '');
        $mutator = new TranslationFileCopyFromPriorRelMutator($factory);
        $mutator->apply($this->context('rel-810'));
        $result = $mutator->apply($this->context('rel-810'));
        self::assertFalse($result->changed());
    }

    public function testRegeneratesDurableSupplementAfterPriorBlobCopy(): void
    {
        $priorBlob = "-- prior translation snapshot\n";
        $this->writeLocal($priorBlob);
        file_put_contents($this->tmpDir . '/' . self::SUPPLEMENT_PATH, '-- stale supplement');

        $factory = $this->stubProcessFactory(true, $priorBlob, '');
        $result = (new TranslationFileCopyFromPriorRelMutator($factory))->apply($this->context('rel-810'));

        self::assertTrue($result->changed());
        self::assertSame([self::SUPPLEMENT_PATH], $result->changedFiles);
        self::assertStringContainsString("'%s Database Upgrade'", $this->readSupplement());
        self::assertStringContainsString("'Actualización base de datos %s'", $this->readSupplement());
    }

    public function testThrowsWhenPrevRelBranchMissing(): void
    {
        $this->writeLocal('foo');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.0', 'rel-820');

        $factory = $this->stubProcessFactory(true, '', '');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/--prev-rel-branch/');
        (new TranslationFileCopyFromPriorRelMutator($factory))->apply($context);
    }

    public function testThrowsOnFetchFailure(): void
    {
        $this->writeLocal('foo');

        // Simulate fetch failure with the current PHP executable so this
        // remains portable across Windows and POSIX test environments.
        $factory = static fn (array $cmd, string $cwd): Process => self::exitProcess(1, $cwd);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/git fetch.*failed/');
        (new TranslationFileCopyFromPriorRelMutator($factory))->apply($this->context('rel-810'));
    }

    public function testThrowsOnShowFailure(): void
    {
        $this->writeLocal('foo');

        // First call (fetch) succeeds; second call (show) fails.
        $callCount = 0;
        $factory = static function (array $cmd, string $cwd) use (&$callCount): Process {
            $callCount++;
            if ($callCount === 1) {
                return self::exitProcess(0, $cwd);
            }
            return self::exitProcess(1, $cwd);
        };
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/git show.*failed/');
        (new TranslationFileCopyFromPriorRelMutator($factory))->apply($this->context('rel-810'));
    }

    private function context(string $prevRelBranch): MutatorContext
    {
        return MutatorContext::fromVersionString(
            $this->tmpDir,
            '8.2.0',
            'rel-820',
            $prevRelBranch,
        );
    }

    /**
     * Build a process factory that returns Processes which, when
     * ->run() is invoked, behave according to the stubbed values. We
     * achieve this with the PHP executable already running the tests.
     *
     * @return \Closure(list<string>, string): Process
     */
    private function stubProcessFactory(bool $success, string $stdout, string $stderr): \Closure
    {
        return static function (array $cmd, string $cwd) use ($success, $stdout, $stderr): Process {
            if ($cmd[1] === 'show') {
                return self::outputProcess($success ? 0 : 1, $stdout, $stderr, $cwd);
            }
            return self::exitProcess($success ? 0 : 1, $cwd);
        };
    }

    private static function exitProcess(int $exitCode, string $cwd): Process
    {
        return self::outputProcess($exitCode, '', '', $cwd);
    }

    private static function outputProcess(int $exitCode, string $stdout, string $stderr, string $cwd): Process
    {
        $code = 'fwrite(STDOUT, base64_decode($argv[1])); '
            . 'fwrite(STDERR, base64_decode($argv[2])); exit((int) $argv[3]);';
        return new Process([
            PHP_BINARY,
            '-r',
            $code,
            base64_encode($stdout),
            base64_encode($stderr),
            (string) $exitCode,
        ], $cwd);
    }

    private function writeLocal(string $content): void
    {
        $path = $this->tmpDir . '/' . self::RELATIVE_PATH;
        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException('Cannot write local file');
        }
    }

    private function readLocal(): string
    {
        $path = $this->tmpDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read local file');
        }
        return $contents;
    }

    private function readSupplement(): string
    {
        $contents = file_get_contents($this->tmpDir . '/' . self::SUPPLEMENT_PATH);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read supplement');
        }
        return $contents;
    }

    private function writeContract(): void
    {
        $json = <<<'JSON'
            {
                "schema": "openemr-translation-contract/1",
                "id": "test-contract",
                "target_key": "%s Database Upgrade",
                "legacy_keys": {"Legacy Database Upgrade": "Legacy"},
                "definitions": {"3": "Actualización base de datos %s"}
            }
            JSON;
        $path = $this->tmpDir . '/contrib/util/language_translations/contracts/database-upgrade.json';
        if (file_put_contents($path, $json) === false) {
            throw new \RuntimeException('Cannot write test contract');
        }
    }

    private function removeRecursive(string $path): void
    {
        if (!is_dir($path)) {
            if (is_file($path) || is_link($path)) {
                unlink($path);
            }
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var \SplFileInfo $entry */
        foreach ($iterator as $entry) {
            $p = $entry->getPathname();
            if ($entry->isDir() && !$entry->isLink()) {
                rmdir($p);
            } else {
                unlink($p);
            }
        }
        rmdir($path);
    }
}
