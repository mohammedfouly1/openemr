<?php

/**
 * Brand-neutral, migration-safe database-backup discovery and retention.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use Closure;
use DateTimeInterface;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ManagedBackupRetention
{
    /** @var Closure(string): (list<string>|false) */
    private Closure $directoryReader;

    /** @var Closure(string): bool */
    private Closure $fileDeleter;

    /** @var Closure(string): bool */
    private Closure $readableProbe;

    /** @var Closure(string): bool */
    private Closure $linkProbe;

    /**
     * The injected operations exist only to make failure behavior deterministic in isolated tests.
     * Production callers use PHP's filesystem operations supplied by the defaults.
     *
     * @param null|Closure(string): (list<string>|false) $directoryReader
     * @param null|Closure(string): bool $fileDeleter
     * @param null|Closure(string): bool $readableProbe
     * @param null|Closure(string): bool $linkProbe
     */
    public function __construct(
        ?Closure $directoryReader = null,
        ?Closure $fileDeleter = null,
        ?Closure $readableProbe = null,
        ?Closure $linkProbe = null,
    ) {
        $this->directoryReader = $directoryReader ?? static fn(string $directory): array|false => @scandir($directory);
        $this->fileDeleter = $fileDeleter ?? static fn(string $path): bool => @unlink($path);
        $this->readableProbe = $readableProbe ?? static fn(string $directory): bool => is_readable($directory);
        $this->linkProbe = $linkProbe ?? static fn(string $path): bool => is_link($path);
    }

    public static function parseKeep(mixed $raw): int
    {
        if (is_int($raw)) {
            $keep = $raw;
        } elseif (is_string($raw) && preg_match('/\A[1-9][0-9]*\z/D', $raw) === 1) {
            $keep = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        } else {
            $keep = false;
        }

        if (!is_int($keep) || $keep < 1) {
            throw new InvalidArgumentException('The --keep value must be a whole number greater than zero.');
        }

        return $keep;
    }

    public static function assertTargetInput(string $target): void
    {
        if (trim($target) === '' || str_contains($target, "\0")) {
            throw new InvalidArgumentException('The backup target must be a non-empty filesystem path.');
        }

        $segments = preg_split('#[\\\\/]+#', $target);
        if ($segments === false || in_array('..', $segments, true)) {
            throw new InvalidArgumentException('The backup target must not contain parent-directory traversal.');
        }
    }

    public function prepareDirectory(string $target): string
    {
        self::assertTargetInput($target);
        if (($this->linkProbe)($target)) {
            throw new RuntimeException('The backup target must not be a symbolic link or exposed reparse link.');
        }
        if (!file_exists($target) && !@mkdir($target, 0o700, true) && !is_dir($target)) {
            throw new RuntimeException('Cannot create the configured backup target.');
        }

        return $this->resolveDirectory($target, true);
    }

    public function newBackupPath(string $directory, string $label, DateTimeInterface $createdAt): string
    {
        $directory = $this->resolveDirectory($directory, true);
        $filename = ManagedBackupArtifact::canonicalFilename($label, $createdAt);
        $path = $this->join($directory, $filename);
        if (
            file_exists($path)
            || ($this->linkProbe)($path)
            || file_exists($path . '.sha256')
            || ($this->linkProbe)($path . '.sha256')
        ) {
            throw new RuntimeException('A managed backup already exists for this label and timestamp.');
        }

        return $path;
    }

    public function discover(string $target): ManagedBackupInventory
    {
        $directory = $this->resolveDirectory($target, false);
        if (!(($this->readableProbe)($directory))) {
            throw new RuntimeException('The configured backup target is not readable.');
        }

        try {
            $entries = ($this->directoryReader)($directory);
        } catch (Throwable $error) {
            throw new RuntimeException('The configured backup target could not be scanned.', 0, $error);
        }
        if ($entries === false) {
            throw new RuntimeException('The configured backup target could not be scanned.');
        }

        $managed = [];
        $malformed = [];
        $unmanaged = [];
        foreach ($entries as $filename) {
            if ($filename === '.' || $filename === '..' || ManagedBackupArtifact::isVerificationSidecar($filename)) {
                continue;
            }

            $path = $this->join($directory, $filename);
            if (($this->linkProbe)($path) || !is_file($path)) {
                $unmanaged[] = $filename;
                continue;
            }

            $artifact = ManagedBackupArtifact::parse($directory, $filename);
            if ($artifact === null) {
                if (ManagedBackupArtifact::looksManaged($filename)) {
                    $malformed[] = $filename;
                } else {
                    $unmanaged[] = $filename;
                }
                continue;
            }

            if (!$this->hasValidSidecar($artifact)) {
                $malformed[] = $filename;
                continue;
            }

            $managed[] = $artifact;
        }

        usort($managed, static function (ManagedBackupArtifact $left, ManagedBackupArtifact $right): int {
            $byTimestamp = strcmp($right->timestamp, $left->timestamp);
            return $byTimestamp !== 0 ? $byTimestamp : strcmp($right->filename, $left->filename);
        });
        sort($malformed, SORT_STRING);
        sort($unmanaged, SORT_STRING);

        return new ManagedBackupInventory($directory, $managed, $malformed, $unmanaged);
    }

    /**
     * @return list<ManagedBackupArtifact>
     */
    public function selectForDeletion(ManagedBackupInventory $inventory, int $keep): array
    {
        self::parseKeep($keep);

        // array_slice() on a list already returns a list; array_values() would be a no-op.
        return array_slice($inventory->managed, $keep);
    }

    public function delete(string $target, ManagedBackupArtifact $artifact): void
    {
        $directory = $this->resolveDirectory($target, true);
        $expectedPath = $this->join($directory, $artifact->filename);
        $realPath = realpath($expectedPath);
        // parse() is pure filename parsing, so one call answers both guards below; calling it
        // twice with the same arguments never produced a different answer.
        $fresh = ManagedBackupArtifact::parse($directory, $artifact->filename);
        if (
            $realPath === false
            || !$this->samePath(dirname($realPath), $directory)
            || ($this->linkProbe)($expectedPath)
            || !is_file($expectedPath)
            || $fresh === null
        ) {
            throw new RuntimeException('Refused to delete a candidate outside the validated backup contract.');
        }

        if (!$this->hasValidSidecar($fresh)) {
            throw new RuntimeException('Refused to delete a candidate whose verification sidecar changed.');
        }

        if (!(($this->fileDeleter)($expectedPath))) {
            throw new RuntimeException('Failed to delete managed backup: ' . $artifact->filename);
        }
        if (!(($this->fileDeleter)($expectedPath . '.sha256'))) {
            throw new RuntimeException('Deleted backup but failed to delete its verification sidecar: '
                . $artifact->filename . '.sha256');
        }
    }

    private function resolveDirectory(string $target, bool $requireWritable): string
    {
        self::assertTargetInput($target);
        if (($this->linkProbe)($target)) {
            throw new RuntimeException('The backup target must not be a symbolic link or exposed reparse link.');
        }
        if (!file_exists($target)) {
            throw new RuntimeException('The configured backup target does not exist.');
        }
        if (!is_dir($target)) {
            throw new RuntimeException('The configured backup target is not a directory.');
        }

        $resolved = realpath($target);
        if ($resolved === false) {
            throw new RuntimeException('The configured backup target cannot be resolved.');
        }
        if ($requireWritable && !is_writable($resolved)) {
            throw new RuntimeException('The configured backup target is not writable.');
        }

        return $resolved;
    }

    private function hasValidSidecar(ManagedBackupArtifact $artifact): bool
    {
        $sidecar = $artifact->sidecarPath();
        if (($this->linkProbe)($sidecar) || !is_file($sidecar)) {
            return false;
        }
        $contents = @file_get_contents($sidecar);
        if (!is_string($contents)) {
            return false;
        }

        return preg_match(
            '/\A[a-f0-9]{64}  ' . preg_quote($artifact->filename, '/') . '(?:\r?\n)?\z/D',
            $contents,
        ) === 1;
    }

    private function samePath(string $left, string $right): bool
    {
        return PHP_OS_FAMILY === 'Windows'
            ? strcasecmp($left, $right) === 0
            : strcmp($left, $right) === 0;
    }

    private function join(string $directory, string $filename): string
    {
        if (str_ends_with($directory, '/') || str_ends_with($directory, '\\')) {
            return $directory . $filename;
        }

        return $directory . DIRECTORY_SEPARATOR . $filename;
    }
}
