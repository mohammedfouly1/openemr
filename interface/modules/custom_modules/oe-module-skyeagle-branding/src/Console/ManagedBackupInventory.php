<?php

/**
 * Immutable result of a managed-backup directory scan.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Console;

final readonly class ManagedBackupInventory
{
    /**
     * @param list<ManagedBackupArtifact> $managed Newest first, with filename as the deterministic tie-breaker.
     * @param list<string> $malformed Managed-looking names that failed format or verification-marker checks.
     * @param list<string> $unmanaged Entries outside the closed managed-backup contract.
     */
    public function __construct(
        public string $directory,
        public array $managed,
        public array $malformed,
        public array $unmanaged,
    ) {
    }

    public function neutralCount(): int
    {
        return count(array_filter(
            $this->managed,
            static fn(ManagedBackupArtifact $backup): bool =>
                $backup->family === ManagedBackupArtifact::FAMILY_NEUTRAL,
        ));
    }

    public function legacyCount(): int
    {
        return count($this->managed) - $this->neutralCount();
    }
}
