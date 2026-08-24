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
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $target = (string) $input->getOption('target');
        $retention = new ManagedBackupRetention();
        try {
            ManagedBackupRetention::assertTargetInput($target);
            $keep = ManagedBackupRetention::parseKeep($input->getOption('keep'));
            $label = (string) $input->getOption('label');
            ManagedBackupArtifact::assertValidLabel($label);
        } catch (InvalidArgumentException $error) {
            $io->error($error->getMessage());

            return self::FAILURE;
        }

        $siteDir = $GLOBALS['OE_SITE_DIR'] ?? '';
        $conf = $siteDir . '/sqlconf.php';
        if (!is_file($conf)) {
            $io->error('sqlconf.php not found for the active site.');

            return self::FAILURE;
        }
        /** @var string $host */
        /** @var string $port */
        /** @var string $login */
        /** @var string $pass */
        /** @var string $dbase */
        require $conf;

        $binDir = (string) QueryUtils::fetchSingleValue(
            "SELECT gl_value FROM globals WHERE gl_name = 'mysql_bin_dir'",
            'gl_value',
            []
        );
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

        $cmd = escapeshellarg($resolved . DIRECTORY_SEPARATOR . 'mysqldump')
            . ' --defaults-file=' . escapeshellarg($defaults)
            . ' --single-transaction --routines --events '
            . escapeshellarg($dbase)
            . ' > ' . escapeshellarg($file) . ' 2>&1';

        $started = microtime(true);
        exec($cmd, $ignored, $rc);
        $elapsed = round(microtime(true) - $started, 2);
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
        $expected = (int) QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE()',
            'c',
            []
        );

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
}
