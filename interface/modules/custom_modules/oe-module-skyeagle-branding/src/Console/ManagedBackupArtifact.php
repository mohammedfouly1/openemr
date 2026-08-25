<?php

/**
 * A strictly recognized, retention-managed database backup artefact.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Console;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Filename parsing is the retention trust boundary. A file is not managed merely because
 * it ends in .sql; it must match one of the closed formats and have a valid verification
 * sidecar (the latter is checked by {@see ManagedBackupRetention}).
 */
final readonly class ManagedBackupArtifact
{
    public const FAMILY_NEUTRAL = 'neutral';
    public const FAMILY_LEGACY = 'legacy';
    public const NEUTRAL_PREFIX = 'managed-db-backup-v1-';
    public const LEGACY_PREFIX = 'thiqa-';

    private const LABEL_PATTERN = '[A-Za-z0-9][A-Za-z0-9_-]{0,62}';
    // The old command imposed no length limit after sanitizing, so compatibility cannot invent one now.
    private const LEGACY_LABEL_PATTERN = '[A-Za-z0-9][A-Za-z0-9_-]*';
    private const TIMESTAMP_PATTERN = '[0-9]{8}-[0-9]{6}';

    private function __construct(
        public string $directory,
        public string $filename,
        public string $family,
        public string $label,
        public string $timestamp,
    ) {
    }

    public static function canonicalFilename(string $label, DateTimeInterface $createdAt): string
    {
        self::assertValidLabel($label);

        return self::NEUTRAL_PREFIX . $label . '-' . $createdAt->format('Ymd-His') . '.sql';
    }

    public static function assertValidLabel(string $label): void
    {
        if (preg_match('/\A' . self::LABEL_PATTERN . '\z/D', $label) !== 1) {
            throw new InvalidArgumentException(
                'Backup labels must be 1-63 ASCII letters, digits, underscores or hyphens, '
                . 'start with a letter or digit, and contain no path or dot segments.'
            );
        }
    }

    public static function parse(string $directory, string $filename): ?self
    {
        $neutral = '/\A' . preg_quote(self::NEUTRAL_PREFIX, '/')
            . '(?<label>' . self::LABEL_PATTERN . ')-(?<timestamp>' . self::TIMESTAMP_PATTERN . ')\.sql\z/D';
        if (preg_match($neutral, $filename, $matches) === 1) {
            return self::fromMatch($directory, $filename, self::FAMILY_NEUTRAL, $matches);
        }

        $legacy = '/\A' . preg_quote(self::LEGACY_PREFIX, '/')
            . '(?<label>' . self::LEGACY_LABEL_PATTERN . ')-(?<timestamp>' . self::TIMESTAMP_PATTERN . ')\.sql\z/D';
        if (preg_match($legacy, $filename, $matches) === 1) {
            return self::fromMatch($directory, $filename, self::FAMILY_LEGACY, $matches);
        }

        return null;
    }

    public static function looksManaged(string $filename): bool
    {
        return str_starts_with($filename, self::NEUTRAL_PREFIX)
            || str_starts_with($filename, self::LEGACY_PREFIX);
    }

    public static function isVerificationSidecar(string $filename): bool
    {
        if (!str_ends_with($filename, '.sha256')) {
            return false;
        }

        return self::parse('', substr($filename, 0, -7)) !== null;
    }

    public function path(): string
    {
        return self::join($this->directory, $this->filename);
    }

    public function sidecarPath(): string
    {
        return $this->path() . '.sha256';
    }

    /**
     * @param array<int|string, string> $matches a preg_match() capture set from {@see parse()}
     */
    private static function fromMatch(string $directory, string $filename, string $family, array $matches): ?self
    {
        $timestamp = $matches['timestamp'];
        $date = DateTimeImmutable::createFromFormat('!Ymd-His', $timestamp, new DateTimeZone('UTC'));
        $errors = DateTimeImmutable::getLastErrors();
        if (
            $date === false
            || ($errors !== false && ($errors['warning_count'] !== 0 || $errors['error_count'] !== 0))
            || $date->format('Ymd-His') !== $timestamp
        ) {
            return null;
        }

        return new self($directory, $filename, $family, $matches['label'], $timestamp);
    }

    private static function join(string $directory, string $filename): string
    {
        if (str_ends_with($directory, '/') || str_ends_with($directory, '\\')) {
            return $directory . $filename;
        }

        return $directory . DIRECTORY_SEPARATOR . $filename;
    }
}
