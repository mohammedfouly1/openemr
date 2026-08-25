<?php

/**
 * Performs a verified, hashed, retention-managed database backup (RDY-0081).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use DateTimeImmutable;
use InvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * RDY-0080 proved the configured dump tool *runs*. That is not a backup policy.
 * This command is the policy's executable half: every run produces an artefact whose
 * integrity is recorded, verifies the artefact before trusting it, prunes by an explicit
 * retention rule, and exits non-zero on any failure so a scheduler raises a real signal.
 *
 * **A backup that nobody verified is a file, not a backup.** Each run therefore checks the
 * dump terminated cleanly and contains the expected table count before it is retained.
 *
 * Credentials are read from the site's own `sqlconf.php` and passed through a private
 * defaults-file, so no secret ever reaches a command line, a process list or this repository.
 *
 * **Exit codes**: `0` verified backup retained; `1` the dump failed or failed verification —
 * treat as a monitoring alert, not a warning.
 */
#[AsCommand(
    name: 'thiqa-branding:backup',
    description: 'Run a verified, hashed, retention-managed database backup (RDY-0081).',
)]
final class BackupCommand extends Command
{
    // Deployment-compatibility default only. Portable/provisioned hosts must pass --target.
    private const DEFAULT_TARGET = 'C:/openemr-stack/backups';
    private const DEFAULT_KEEP = 7;

    protected function configure(): void
    {
        $this->addOption('target', null, InputOption::VALUE_REQUIRED, 'Backup directory', self::DEFAULT_TARGET);
        $this->addOption(
            'keep',
            null,
            InputOption::VALUE_REQUIRED,
            'Positive number of managed backups to retain across neutral and legacy formats',
            (string) self::DEFAULT_KEEP
        );
        $this->addOption(
            'label',
            null,
            InputOption::VALUE_REQUIRED,
            'Run label: 1-63 ASCII letters, digits, underscores or hyphens',
            'scheduled'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $target = self::stringOption($input, 'target');
        $retention = new ManagedBackupRetention();
        try {
            ManagedBackupRetention::assertTargetInput($target);
            $keep = ManagedBackupRetention::parseKeep($input->getOption('keep'));
            $label = self::stringOption($input, 'label');
            ManagedBackupArtifact::assertValidLabel($label);
        } catch (InvalidArgumentException $error) {
            $io->error($error->getMessage());

            return self::FAILURE;
        }

        $siteDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR');
        $conf = $siteDir . '/sqlconf.php';
        if (!is_file($conf)) {
            $io->error('sqlconf.php not found for the active site.');

            return self::FAILURE;
        }

        // Read back through readSqlConf() rather than requiring straight into this scope, so
        // every credential has one place to be narrowed before it is used.
        $sqlConf = self::readSqlConf($conf);

        $host = self::confValue($sqlConf, 'host');
        $port = self::confValue($sqlConf, 'port');
        $login = self::confValue($sqlConf, 'login');
        $pass = self::confValue($sqlConf, 'pass');
        $dbase = self::confValue($sqlConf, 'dbase');

        $binDir = self::scalarToString(QueryUtils::fetchSingleValue(
            "SELECT gl_value FROM globals WHERE gl_name = 'mysql_bin_dir'",
            'gl_value',
            []
        ));
        $resolved = realpath($binDir);
        if ($resolved === false) {
            $io->error("mysql_bin_dir does not resolve: {$binDir} (see RDY-0080)");

            return self::FAILURE;
        }

        try {
            $target = $retention->prepareDirectory($target);
            $file = $retention->newBackupPath($target, $label, new DateTimeImmutable());
        } catch (RuntimeException $error) {
            $io->error($error->getMessage());

            return self::FAILURE;
        }

        $io->writeln('Backup target: ' . $target);
        $io->writeln('New backup: ' . $file);

        $defaults = tempnam(sys_get_temp_dir(), 'oemrbk');
        if ($defaults === false) {
            $io->error('Could not create the private database-client defaults file.');

            return self::FAILURE;
        }
        $defaultsContents = "[client]\nuser={$login}\npassword={$pass}\nhost={$host}\nport={$port}\n";
        if (file_put_contents($defaults, $defaultsContents) !== strlen($defaultsContents)) {
            @unlink($defaults);
            $io->error('Could not write the private database-client defaults file.');

            return self::FAILURE;
        }
        @chmod($defaults, 0600);

        // mysqldump requires --defaults-file to be the FIRST option, so the order here is load
        // bearing. Arguments are passed as an array so the child is spawned directly, with no
        // shell in the middle and nothing to escape by hand.
        $dump = new Process([
            $resolved . DIRECTORY_SEPARATOR . 'mysqldump',
            '--defaults-file=' . $defaults,
            '--single-transaction',
            '--routines',
            '--events',
            $dbase,
        ]);
        // A full-database dump has no meaningful upper bound; the scheduler that invokes this
        // command decides how long a backup may take, not a library default of sixty seconds.
        $dump->setTimeout(null);

        $artefact = fopen($file, 'wb');
        if ($artefact === false) {
            @unlink($defaults);
            $io->error('Could not open the backup artefact for writing.');

            return self::FAILURE;
        }

        $started = microtime(true);
        // The previous shell form redirected both streams into the artefact, and the
        // "Dump completed" tail check below reads that trailer, so both streams still land in
        // the same file in the order the child emits them.
        $dump->run(static function (string $type, string $buffer) use ($artefact): void {
            fwrite($artefact, $buffer);
        });
        $elapsed = round(microtime(true) - $started, 2);
        fclose($artefact);
        $rc = $dump->getExitCode() ?? -1;
        @unlink($defaults);

        if ($rc !== 0 || !is_file($file)) {
            $io->error("Backup FAILED (rc={$rc}).");

            return self::FAILURE;
        }

        // Verify before trusting: clean termination + expected table count.
        $tail = '';
        $size = (int) filesize($file);
        $fh = fopen($file, 'r');
        if ($fh !== false) {
            fseek($fh, max(0, $size - 200));
            $tail = (string) fread($fh, 200);
            fclose($fh);
        }
        $tables = 0;
        foreach (file($file) ?: [] as $line) {
            if (str_starts_with($line, 'CREATE TABLE')) {
                $tables++;
            }
        }
        $expected = self::scalarToInt(QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE()',
            'c',
            []
        ));

        if (!str_contains($tail, 'Dump completed') || $tables !== $expected) {
            $io->error(sprintf(
                'Backup FAILED VERIFICATION: tables %d of %d, clean termination %s. Artefact retained for inspection.',
                $tables,
                $expected,
                str_contains($tail, 'Dump completed') ? 'yes' : 'no'
            ));

            return self::FAILURE;
        }

        $hash = hash_file('sha256', $file);
        if (!is_string($hash)) {
            $io->error('Backup verification hash could not be calculated. Artefact retained for inspection.');

            return self::FAILURE;
        }
        $sidecar = $hash . '  ' . basename($file) . "\n";
        if (file_put_contents($file . '.sha256', $sidecar) !== strlen($sidecar)) {
            $io->error('Backup hash sidecar could not be written. Artefact retained for inspection.');

            return self::FAILURE;
        }

        try {
            $inventory = $retention->discover($target);
            $selected = $retention->selectForDeletion($inventory, $keep);
        } catch (RuntimeException $error) {
            $io->error('Backup verified, but retention scan FAILED: ' . $error->getMessage());

            return self::FAILURE;
        }

        $io->writeln(sprintf(
            'Retention scan: managed %d (neutral %d, legacy %d), keep %d, selected %d.',
            count($inventory->managed),
            $inventory->neutralCount(),
            $inventory->legacyCount(),
            $keep,
            count($selected),
        ));
        if ($inventory->malformed !== []) {
            $io->warning(sprintf(
                '%d managed-looking file(s) were malformed or unverified and were left untouched.',
                count($inventory->malformed),
            ));
        }

        foreach ($selected as $old) {
            try {
                $retention->delete($target, $old);
                $io->writeln('Deleted managed backup: ' . $old->filename);
            } catch (RuntimeException $error) {
                $io->error('Backup verified, but retention deletion FAILED: ' . $error->getMessage());

                return self::FAILURE;
            }
        }

        $io->success(sprintf(
            'Backup verified. %s bytes, %d tables, %.2fs. sha256 %s… Retained %d, pruned %d.',
            number_format($size),
            $tables,
            $elapsed,
            substr($hash, 0, 16),
            count($inventory->managed) - count($selected),
            count($selected)
        ));

        return self::SUCCESS;
    }

    /**
     * Reads a console option as a string.
     *
     * Both options read here are VALUE_REQUIRED with a string default, so the console component
     * only ever hands back a string; the other branch exists so the value reaches the validators
     * already typed rather than cast.
     */
    private static function stringOption(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);

        return is_string($value) ? $value : '';
    }

    /**
     * Includes sqlconf.php in a scope of its own and hands back the variables it defined.
     *
     * The file assigns its credentials as loose variables, which nothing can follow into a method
     * scope. Its own `global $sqlconf` line still reaches global scope exactly as it did when the
     * include sat directly in execute().
     *
     * @return array<string, mixed>
     */
    private static function readSqlConf(string $sqlconfPath): array
    {
        require $sqlconfPath;

        return get_defined_vars();
    }

    /**
     * Reads one credential out of the included sqlconf.php variable set.
     *
     * @param array<string, mixed> $conf the variables sqlconf.php defined
     */
    private static function confValue(array $conf, string $key): string
    {
        $value = $conf[$key] ?? null;

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * Narrows an untyped single-column query result to string.
     *
     * QueryUtils hands single values back untyped. Every scalar is converted exactly as the
     * previous cast converted it; an array or object, which the cast would have turned into a
     * warning and the literal text "Array", is refused instead, because it means the query no
     * longer returns what this command was written against.
     */
    private static function scalarToString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if ($value === null || is_bool($value) || is_float($value) || is_int($value)) {
            return (string) $value;
        }

        throw new RuntimeException('Expected a single scalar column value.');
    }

    /**
     * Narrows an untyped single-column query result to int. See {@see scalarToString()}.
     */
    private static function scalarToInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if ($value === null || is_bool($value) || is_float($value) || is_string($value)) {
            return (int) $value;
        }

        throw new RuntimeException('Expected a single scalar column value.');
    }
}
