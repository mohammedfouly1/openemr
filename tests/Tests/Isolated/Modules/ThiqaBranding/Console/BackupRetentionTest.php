<?php

/**
 * Brand-neutral managed-backup naming, discovery, retention and deletion safety.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Console;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use OpenEMR\Modules\ThiqaBranding\Console\BackupCommand;
use OpenEMR\Modules\ThiqaBranding\Console\ManagedBackupArtifact;
use OpenEMR\Modules\ThiqaBranding\Console\ManagedBackupRetention;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

final class BackupRetentionTest extends TestCase
{
    private string $tempRoot = '';

    protected function setUp(): void
    {
        $this->tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'prebrand-backup-retention-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->tempRoot, 0o700, true));
        self::assertNotFalse(realpath($this->tempRoot));
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->tempRoot)) {
            return;
        }

        $root = realpath($this->tempRoot);
        self::assertIsString($root);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var SplFileInfo $entry */
        foreach ($iterator as $entry) {
            $path = $entry->getPathname();
            $this->assertInsideTempRoot($path);
            if ($entry->isLink()) {
                $resolvedLink = realpath($path);
                if ($resolvedLink !== false) {
                    $this->assertInsideTempRoot($resolvedLink);
                }
                self::assertTrue(unlink($path));
            } elseif ($entry->isDir()) {
                self::assertTrue(rmdir($path));
            } else {
                $resolvedFile = realpath($path);
                self::assertIsString($resolvedFile);
                $this->assertInsideTempRoot($resolvedFile);
                self::assertTrue(unlink($path));
            }
        }
        $this->assertInsideSystemTemp($root);
        self::assertTrue(rmdir($root));
    }

    public function testCanonicalFilenameIsNeutralVersionedAndStrictlyParseable(): void
    {
        $filename = ManagedBackupArtifact::canonicalFilename(
            'scheduled_1',
            new DateTimeImmutable('2026-08-24 12:34:56', new DateTimeZone('UTC')),
        );

        self::assertSame('managed-db-backup-v1-scheduled_1-20260824-123456.sql', $filename);
        $artifact = ManagedBackupArtifact::parse($this->tempRoot, $filename);
        self::assertNotNull($artifact);
        self::assertSame(ManagedBackupArtifact::FAMILY_NEUTRAL, $artifact->family);
        self::assertSame('scheduled_1', $artifact->label);
        self::assertSame('20260824-123456', $artifact->timestamp);
    }

    #[DataProvider('validLabelProvider')]
    public function testAcceptedLabelsUseOnlyThePortableGrammar(string $label): void
    {
        ManagedBackupArtifact::assertValidLabel($label);
        self::assertTrue(true);
    }

    public static function validLabelProvider(): array
    {
        return [
            'letters' => ['scheduled'],
            'hyphen' => ['brand-a'],
            'underscore' => ['future_2'],
            'mixed case retained' => ['RunA'],
            'maximum length' => [str_repeat('a', 63)],
        ];
    }

    #[DataProvider('invalidLabelProvider')]
    public function testUnsafeOrMalformedLabelsAreRejected(string $label): void
    {
        $this->expectException(InvalidArgumentException::class);
        ManagedBackupArtifact::assertValidLabel($label);
    }

    public static function invalidLabelProvider(): array
    {
        return [
            'empty' => [''],
            'slash' => ['brand/a'],
            'backslash' => ['brand\\a'],
            'dot traversal' => ['..'],
            'embedded traversal' => ['brand..a'],
            'absolute Unix path' => ['/tmp/a'],
            'absolute Windows path' => ['C:\\tmp\\a'],
            'space' => ['brand a'],
            'colon' => ['brand:a'],
            'leading hyphen' => ['-brand'],
            'too long' => [str_repeat('a', 64)],
        ];
    }

    public function testLegacyOnlyArchiveIsRecognizedWithoutRename(): void
    {
        $legacy = $this->writeManaged('thiqa-legacy-20260820-100000.sql');
        $inventory = (new ManagedBackupRetention())->discover($this->tempRoot);

        self::assertCount(1, $inventory->managed);
        self::assertSame(1, $inventory->legacyCount());
        self::assertSame(0, $inventory->neutralCount());
        self::assertFileExists($legacy);
        self::assertSame('thiqa-legacy-20260820-100000.sql', basename($legacy));
    }

    public function testNeutralOnlyArchiveIsRecognized(): void
    {
        $this->writeManaged('managed-db-backup-v1-current-20260821-100000.sql');
        $inventory = (new ManagedBackupRetention())->discover($this->tempRoot);

        self::assertCount(1, $inventory->managed);
        self::assertSame(0, $inventory->legacyCount());
        self::assertSame(1, $inventory->neutralCount());
    }

    public function testMixedArchiveIsOneChronologicalSetIndependentOfMtime(): void
    {
        $old = $this->writeManaged('thiqa-legacy-20260820-100000.sql');
        $new = $this->writeManaged('managed-db-backup-v1-current-20260822-100000.sql');
        self::assertTrue(touch($old, strtotime('2030-01-01')));
        self::assertTrue(touch($new, strtotime('2020-01-01')));

        $inventory = (new ManagedBackupRetention())->discover($this->tempRoot);

        self::assertSame(
            ['managed-db-backup-v1-current-20260822-100000.sql', 'thiqa-legacy-20260820-100000.sql'],
            array_column($inventory->managed, 'filename'),
        );
        self::assertSame(1, $inventory->legacyCount());
        self::assertSame(1, $inventory->neutralCount());
    }

    public function testUnrelatedMalformedCaseVariantAndPartialFilesStayUnmanaged(): void
    {
        $this->writeRaw('customer.sql');
        $this->writeRaw('notes.txt');
        $this->writeRaw('THIQA-case-20260820-100000.sql');
        $this->writeRaw('managed-db-backup-v1-partial-20260820-100000.sql.part');
        $this->writeRaw('thiqa-malformed-date-20260230-100000.sql');
        $this->writeRaw('managed-db-backup-v1-no-sidecar-20260820-100000.sql');
        self::assertTrue(mkdir($this->tempRoot . DIRECTORY_SEPARATOR
            . 'managed-db-backup-v1-directory-20260820-100000.sql'));

        $inventory = (new ManagedBackupRetention())->discover($this->tempRoot);

        self::assertSame([], $inventory->managed);
        self::assertContains('thiqa-malformed-date-20260230-100000.sql', $inventory->malformed);
        self::assertContains('managed-db-backup-v1-no-sidecar-20260820-100000.sql', $inventory->malformed);
        self::assertContains('customer.sql', $inventory->unmanaged);
        self::assertContains('THIQA-case-20260820-100000.sql', $inventory->unmanaged);
        self::assertContains('managed-db-backup-v1-directory-20260820-100000.sql', $inventory->unmanaged);
    }

    public function testInvalidOrMismatchedSidecarNeverMakesAFileManaged(): void
    {
        $filename = 'managed-db-backup-v1-current-20260820-100000.sql';
        $this->writeRaw($filename);
        $this->writeRaw($filename . '.sha256', str_repeat('a', 64) . "  another.sql\n");

        $inventory = (new ManagedBackupRetention())->discover($this->tempRoot);

        self::assertSame([], $inventory->managed);
        self::assertSame([$filename], $inventory->malformed);
    }

    #[DataProvider('keepProvider')]
    public function testKeepSemanticsSelectExactlyTheOldestManagedBackups(int $keep, array $expected): void
    {
        $this->writeManaged('thiqa-legacy-20260820-100000.sql');
        $this->writeManaged('managed-db-backup-v1-current-20260821-100000.sql');
        $this->writeManaged('thiqa-legacy-20260822-100000.sql');
        $retention = new ManagedBackupRetention();

        $selected = $retention->selectForDeletion($retention->discover($this->tempRoot), $keep);

        self::assertSame($expected, array_column($selected, 'filename'));
    }

    public static function keepProvider(): array
    {
        return [
            'keep one' => [1, [
                'managed-db-backup-v1-current-20260821-100000.sql',
                'thiqa-legacy-20260820-100000.sql',
            ]],
            'keep two' => [2, ['thiqa-legacy-20260820-100000.sql']],
            'keep equal to count' => [3, []],
            'keep greater than count' => [5, []],
        ];
    }

    public function testZeroManagedBackupsAndOneManagedBackupAreExplicitNoOps(): void
    {
        $retention = new ManagedBackupRetention();
        self::assertSame([], $retention->selectForDeletion($retention->discover($this->tempRoot), 1));

        $this->writeManaged('managed-db-backup-v1-only-20260821-100000.sql');
        self::assertSame([], $retention->selectForDeletion($retention->discover($this->tempRoot), 1));
    }

    public function testEqualParsedTimestampsUseFilenameAsDeterministicTieBreaker(): void
    {
        $this->writeManaged('managed-db-backup-v1-brand-a-20260821-100000.sql');
        $this->writeManaged('managed-db-backup-v1-brand-b-20260821-100000.sql');
        $retention = new ManagedBackupRetention();
        $inventory = $retention->discover($this->tempRoot);

        self::assertSame(
            ['managed-db-backup-v1-brand-b-20260821-100000.sql',
                'managed-db-backup-v1-brand-a-20260821-100000.sql'],
            array_column($inventory->managed, 'filename'),
        );
        self::assertSame(
            ['managed-db-backup-v1-brand-a-20260821-100000.sql'],
            array_column($retention->selectForDeletion($inventory, 1), 'filename'),
        );
    }

    public function testNeutralOlderThanLegacyAndLegacyOlderThanNeutralBothSortCorrectly(): void
    {
        $this->writeManaged('managed-db-backup-v1-current-20260819-100000.sql');
        $this->writeManaged('thiqa-legacy-20260820-100000.sql');
        $this->writeManaged('thiqa-legacy-20260821-100000.sql');
        $this->writeManaged('managed-db-backup-v1-current-20260822-100000.sql');

        $inventory = (new ManagedBackupRetention())->discover($this->tempRoot);

        self::assertSame(
            ['managed-db-backup-v1-current-20260822-100000.sql', 'thiqa-legacy-20260821-100000.sql',
                'thiqa-legacy-20260820-100000.sql', 'managed-db-backup-v1-current-20260819-100000.sql'],
            array_column($inventory->managed, 'filename'),
        );
    }

    #[DataProvider('invalidKeepProvider')]
    public function testInvalidKeepValuesFailInsteadOfClamping(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        ManagedBackupRetention::parseKeep($value);
    }

    public static function invalidKeepProvider(): array
    {
        return [
            'negative' => [-1],
            'zero integer' => [0],
            'zero string' => ['0'],
            'decimal' => ['1.5'],
            'letters' => ['seven'],
            'leading whitespace' => [' 7'],
            'boolean' => [true],
            'null' => [null],
            'overflow' => [str_repeat('9', 40)],
        ];
    }

    #[DataProvider('unsafeTargetProvider')]
    public function testTargetTraversalAndEmptyTargetsAreRejected(string $target): void
    {
        $this->expectException(InvalidArgumentException::class);
        ManagedBackupRetention::assertTargetInput($target);
    }

    public static function unsafeTargetProvider(): array
    {
        return [
            'empty' => [''],
            'whitespace' => ['   '],
            'parent forward slash' => ['safe/../outside'],
            'parent backslash' => ['safe\\..\\outside'],
            'null byte' => ["safe\0outside"],
        ];
    }

    public function testMissingTargetAndTargetFileAreDistinctErrors(): void
    {
        $retention = new ManagedBackupRetention();
        try {
            $retention->discover($this->tempRoot . DIRECTORY_SEPARATOR . 'missing');
            self::fail('A missing target must fail.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('does not exist', $error->getMessage());
        }

        $file = $this->writeRaw('not-a-directory');
        try {
            $retention->discover($file);
            self::fail('A file target must fail.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('not a directory', $error->getMessage());
        }
    }

    public function testPrepareDirectoryCreatesOnlyTheValidatedConfiguredTarget(): void
    {
        $target = $this->tempRoot . DIRECTORY_SEPARATOR . 'new target' . DIRECTORY_SEPARATOR . 'backups';

        $resolved = (new ManagedBackupRetention())->prepareDirectory($target);

        self::assertSame(realpath($target), $resolved);
        self::assertDirectoryExists($target);
        $this->assertInsideTempRoot($resolved);
    }

    public function testSymbolicOrReparseLinkTargetsAndCandidatesAreNeverManaged(): void
    {
        $targetLink = $this->tempRoot . DIRECTORY_SEPARATOR . 'linked-target';
        self::assertTrue(mkdir($targetLink));
        $linkProbe = static fn(string $path): bool => str_contains(basename($path), 'linked');
        $retention = new ManagedBackupRetention(linkProbe: $linkProbe);

        try {
            $retention->discover($targetLink);
            self::fail('A linked target must be rejected.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('symbolic link', $error->getMessage());
        }

        $this->writeManaged('managed-db-backup-v1-real-20260821-100000.sql');
        $candidateLink = $this->writeManaged('managed-db-backup-v1-linked-20260822-100000.sql');
        $inventory = $retention->discover($this->tempRoot);
        self::assertCount(1, $inventory->managed);
        self::assertContains(basename($candidateLink), $inventory->unmanaged);
    }

    public function testUnreadableAndFailedScansAreErrorsNotEmptyInventories(): void
    {
        $unreadable = new ManagedBackupRetention(readableProbe: static fn(string $directory): bool => false);
        try {
            $unreadable->discover($this->tempRoot);
            self::fail('An unreadable target must fail.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('not readable', $error->getMessage());
        }

        $failed = new ManagedBackupRetention(directoryReader: static fn(string $directory): false => false);
        try {
            $failed->discover($this->tempRoot);
            self::fail('A failed directory scan must fail.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('could not be scanned', $error->getMessage());
        }

        $throwing = new ManagedBackupRetention(directoryReader: static function (string $directory): array {
            throw new RuntimeException('synthetic scan failure');
        });
        $this->expectException(RuntimeException::class);
        $throwing->discover($this->tempRoot);
    }

    public function testNewPathUsesPlatformJoinAndRefusesCollisions(): void
    {
        $retention = new ManagedBackupRetention();
        $time = new DateTimeImmutable('2026-08-24 12:34:56', new DateTimeZone('UTC'));
        $path = $retention->newBackupPath($this->tempRoot, 'current', $time);
        $resolvedRoot = realpath($this->tempRoot);
        self::assertIsString($resolvedRoot);

        self::assertSame(
            $resolvedRoot . DIRECTORY_SEPARATOR . 'managed-db-backup-v1-current-20260824-123456.sql',
            $path,
        );
        file_put_contents($path, 'collision');

        $this->expectException(RuntimeException::class);
        $retention->newBackupPath($this->tempRoot, 'current', $time);
    }

    public function testPathsWithSpacesWorkWithoutSeparatorAssumptions(): void
    {
        $spaced = $this->tempRoot . DIRECTORY_SEPARATOR . 'Managed Backups';
        self::assertTrue(mkdir($spaced));
        $this->writeManaged('managed-db-backup-v1-current-20260821-100000.sql', $spaced);

        $inventory = (new ManagedBackupRetention())->discover($spaced);

        self::assertCount(1, $inventory->managed);
        self::assertSame(realpath($spaced), $inventory->directory);
    }

    public function testDeletionRemovesOnlySelectedManagedFilesAndSidecars(): void
    {
        $unrelated = $this->writeRaw('customer.sql', 'never delete');
        $old = $this->writeManaged('thiqa-legacy-20260820-100000.sql');
        $new = $this->writeManaged('managed-db-backup-v1-current-20260821-100000.sql');
        $retention = new ManagedBackupRetention();
        $selected = $retention->selectForDeletion($retention->discover($this->tempRoot), 1);
        self::assertCount(1, $selected);

        $retention->delete($this->tempRoot, $selected[0]);

        self::assertFileDoesNotExist($old);
        self::assertFileDoesNotExist($old . '.sha256');
        self::assertFileExists($new);
        self::assertFileExists($unrelated);
        self::assertSame('never delete', file_get_contents($unrelated));
    }

    public function testDeletionFailureIsReportedAndSidecarIsNotFalselyRemoved(): void
    {
        $path = $this->writeManaged('managed-db-backup-v1-current-20260821-100000.sql');
        $retention = new ManagedBackupRetention(fileDeleter: static fn(string $candidate): bool => false);
        $artifact = $retention->discover($this->tempRoot)->managed[0];

        try {
            $retention->delete($this->tempRoot, $artifact);
            self::fail('A failed deletion must be reported.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('Failed to delete', $error->getMessage());
        }
        self::assertFileExists($path);
        self::assertFileExists($path . '.sha256');
    }

    public function testChangedSidecarBlocksDeletion(): void
    {
        $path = $this->writeManaged('managed-db-backup-v1-current-20260821-100000.sql');
        $retention = new ManagedBackupRetention();
        $artifact = $retention->discover($this->tempRoot)->managed[0];
        file_put_contents($path . '.sha256', 'changed');

        $this->expectException(RuntimeException::class);
        $retention->delete($this->tempRoot, $artifact);
    }

    public function testRepeatRetentionExecutionIsStable(): void
    {
        $this->writeManaged('thiqa-legacy-20260820-100000.sql');
        $this->writeManaged('managed-db-backup-v1-current-20260821-100000.sql');
        $retention = new ManagedBackupRetention();
        foreach ($retention->selectForDeletion($retention->discover($this->tempRoot), 1) as $artifact) {
            $retention->delete($this->tempRoot, $artifact);
        }

        self::assertSame([], $retention->selectForDeletion($retention->discover($this->tempRoot), 1));
        self::assertCount(1, $retention->discover($this->tempRoot)->managed);
    }

    public function testNewPrefixOnlySelectorMissesLegacyButRepairedSelectorDoesNot(): void
    {
        $unrelated = $this->writeRaw('unrelated.sql', 'unchanged');
        $this->writeManaged('thiqa-legacy-20260818-100000.sql');
        $this->writeManaged('thiqa-legacy-20260819-100000.sql');
        $this->writeManaged('thiqa-legacy-20260820-100000.sql');
        $this->writeManaged('managed-db-backup-v1-current-20260821-100000.sql');
        $this->writeManaged('managed-db-backup-v1-current-20260822-100000.sql');

        $allNames = scandir($this->tempRoot);
        self::assertIsArray($allNames);
        $neutralOnly = array_values(array_filter(
            $allNames,
            static fn(string $name): bool => str_starts_with($name, ManagedBackupArtifact::NEUTRAL_PREFIX)
                && str_ends_with($name, '.sql'),
        ));
        self::assertCount(2, $neutralOnly, 'A new-prefix-only selector silently ignores all three legacy files.');

        $retention = new ManagedBackupRetention();
        $inventory = $retention->discover($this->tempRoot);
        self::assertCount(5, $inventory->managed);
        $selected = $retention->selectForDeletion($inventory, 2);
        self::assertSame(
            ['thiqa-legacy-20260820-100000.sql', 'thiqa-legacy-20260819-100000.sql',
                'thiqa-legacy-20260818-100000.sql'],
            array_column($selected, 'filename'),
        );
        foreach ($selected as $artifact) {
            $retention->delete($this->tempRoot, $artifact);
        }

        self::assertCount(2, $retention->discover($this->tempRoot)->managed);
        self::assertFileExists($unrelated);
        self::assertSame('unchanged', file_get_contents($unrelated));
    }

    public function testCommandRejectsZeroKeepAndUnsafeLabelBeforeDatabaseAccess(): void
    {
        $zero = new CommandTester(new BackupCommand());
        self::assertSame(1, $zero->execute([
            '--target' => $this->tempRoot,
            '--keep' => '0',
            '--label' => 'scheduled',
        ]));
        self::assertStringContainsString('greater than zero', $zero->getDisplay());

        $unsafe = new CommandTester(new BackupCommand());
        self::assertSame(1, $unsafe->execute([
            '--target' => $this->tempRoot,
            '--keep' => '1',
            '--label' => '../outside',
        ]));
        self::assertStringContainsString('contain no path', $unsafe->getDisplay());
    }

    public function testCommandKeepsTheDeploymentDefaultButDelegatesNamingAndRetention(): void
    {
        $command = new BackupCommand();
        $definition = $command->getDefinition();

        self::assertSame('C:/openemr-stack/backups', $definition->getOption('target')->getDefault());
        self::assertSame('7', $definition->getOption('keep')->getDefault());
        self::assertSame('scheduled', $definition->getOption('label')->getDefault());

        $source = file_get_contents(
            dirname(__DIR__, 6)
            . '/interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/BackupCommand.php',
        );
        self::assertIsString($source);
        self::assertStringContainsString('newBackupPath(', $source);
        self::assertStringContainsString('selectForDeletion(', $source);
        self::assertStringNotContainsString("'/thiqa-*.sql'", $source);
        self::assertStringNotContainsString("'/thiqa-%s-%s.sql'", $source);
    }

    private function writeManaged(string $filename, ?string $directory = null): string
    {
        $directory ??= $this->tempRoot;
        self::assertNotNull(ManagedBackupArtifact::parse($directory, $filename));
        $path = $this->writeRaw($filename, 'synthetic managed backup', $directory);
        $hash = hash_file('sha256', $path);
        self::assertIsString($hash);
        $this->writeRaw($filename . '.sha256', $hash . '  ' . $filename . "\n", $directory);

        return $path;
    }

    private function writeRaw(string $filename, string $contents = 'fixture', ?string $directory = null): string
    {
        $directory ??= $this->tempRoot;
        self::assertSame($filename, basename($filename));
        $resolvedDirectory = realpath($directory);
        self::assertIsString($resolvedDirectory);
        $this->assertInsideTempRoot($resolvedDirectory);
        $path = $resolvedDirectory . DIRECTORY_SEPARATOR . $filename;
        self::assertSame(strlen($contents), file_put_contents($path, $contents));

        return $path;
    }

    private function assertInsideTempRoot(string $path): void
    {
        $root = realpath($this->tempRoot);
        self::assertIsString($root);
        $prefix = $root . DIRECTORY_SEPARATOR;
        $inside = PHP_OS_FAMILY === 'Windows'
            ? strcasecmp($path, $root) === 0 || str_starts_with(strtolower($path), strtolower($prefix))
            : strcmp($path, $root) === 0 || str_starts_with($path, $prefix);
        self::assertTrue($inside, 'Refusing a test filesystem operation outside its unique temporary directory.');
    }

    private function assertInsideSystemTemp(string $path): void
    {
        $systemTemp = realpath(sys_get_temp_dir());
        self::assertIsString($systemTemp);
        $prefix = $systemTemp . DIRECTORY_SEPARATOR . 'prebrand-backup-retention-';
        $inside = PHP_OS_FAMILY === 'Windows'
            ? str_starts_with(strtolower($path), strtolower($prefix))
            : str_starts_with($path, $prefix);
        self::assertTrue($inside, 'Refusing to remove a directory outside the dedicated test prefix.');
    }
}
