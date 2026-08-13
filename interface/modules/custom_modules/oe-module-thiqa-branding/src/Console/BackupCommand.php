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

use OpenEMR\Common\Database\QueryUtils;
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
    private const DEFAULT_TARGET = 'C:/openemr-stack/backups';
    private const DEFAULT_KEEP = 7;

    protected function configure(): void
    {
        $this->addOption('target', null, InputOption::VALUE_REQUIRED, 'Backup directory', self::DEFAULT_TARGET);
        $this->addOption('keep', null, InputOption::VALUE_REQUIRED, 'Backups to retain', (string) self::DEFAULT_KEEP);
        $this->addOption('label', null, InputOption::VALUE_REQUIRED, 'Label for this run', 'scheduled');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $target = rtrim((string) $input->getOption('target'), '/\\');
        $keep = max(1, (int) $input->getOption('keep'));
        $label = preg_replace('/[^a-z0-9_-]/i', '', (string) $input->getOption('label')) ?: 'run';

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

        if (!is_dir($target) && !mkdir($target, 0700, true) && !is_dir($target)) {
            $io->error("Cannot create backup target: {$target}");

            return self::FAILURE;
        }

        $file = sprintf('%s/thiqa-%s-%s.sql', $target, $label, date('Ymd-His'));
        $defaults = tempnam(sys_get_temp_dir(), 'oemrbk');
        file_put_contents($defaults, "[client]\nuser={$login}\npassword={$pass}\nhost={$host}\nport={$port}\n");
        @chmod($defaults, 0600);

        $cmd = escapeshellarg($resolved . DIRECTORY_SEPARATOR . 'mysqldump')
            . ' --defaults-file=' . escapeshellarg($defaults)
            . ' --single-transaction --routines --events '
            . escapeshellarg($dbase)
            . ' > ' . escapeshellarg($file) . ' 2>&1';

        $started = microtime(true);
        exec($cmd, $ignored, $rc);
        $elapsed = round(microtime(true) - $started, 2);
        unlink($defaults);

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
        file_put_contents($file . '.sha256', $hash . "  " . basename($file) . "\n");

        // Retention: newest $keep verified artefacts survive.
        $existing = glob($target . '/thiqa-*.sql') ?: [];
        usort($existing, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
        $pruned = 0;
        foreach (array_slice($existing, $keep) as $old) {
            @unlink($old);
            @unlink($old . '.sha256');
            $pruned++;
        }

        $io->success(sprintf(
            'Backup verified. %s bytes, %d tables, %.2fs. sha256 %s… Retained %d, pruned %d.',
            number_format($size),
            $tables,
            $elapsed,
            substr($hash, 0, 16),
            min(count($existing), $keep),
            $pruned
        ));

        return self::SUCCESS;
    }
}
