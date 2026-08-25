<?php

/**
 * A BrandingGlobalsWriterInterface that pauses at the exact moment production code would
 * write to the database, and blocks until the test harness terminates the process.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation;

use LogicException;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\BrandingGlobalsWriterInterface;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\GlobalsDelta;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use RuntimeException;

/**
 * Used only from kill_point_subprocess.php, run as a standalone child process by
 * MaterialiserKillRecoveryTest. `writeAll()` is the one call BrandingMaterialiser makes
 * after every staged file has already been renamed into place (steps 5a/5b complete) --
 * so touching `$sentinelPath` here and then blocking is "the process is now exactly
 * where AR-P2-006 asks about: files committed, database not yet written." The parent
 * test polls for the sentinel and then genuinely terminates this process at the OS
 * level (TerminateProcess on Windows; the same ungraceful-stop guarantee SIGKILL gives
 * on POSIX), so nothing after this point -- no catch, no finally, no destructor -- ever
 * runs. `$safetyTimeoutSeconds` exists only so a wiring bug produces a clear subprocess
 * failure instead of a hang.
 */
final readonly class KillPointGlobalsWriter implements BrandingGlobalsWriterInterface
{
    public function __construct(
        private SiteId $boundSite,
        private string $sentinelPath,
        private int $safetyTimeoutSeconds = 30,
    ) {
    }

    /** Nothing has ever been materialised from this writer's point of view -- no database read happens. */
    public function currentRevision(SiteId $site): BrandingRevision
    {
        $this->assertBoundTo($site);

        return BrandingRevision::initial();
    }

    /** @return array<string, string> */
    public function readBrandingGlobals(SiteId $site): array
    {
        $this->assertBoundTo($site);

        return [];
    }

    public function writeAll(
        SiteId $site,
        GlobalsDelta $delta,
        BrandingRevision $revision,
        string $materialisedAt,
    ): void {
        $this->assertBoundTo($site);

        if (file_put_contents($this->sentinelPath, (string) getmypid()) === false) {
            throw new RuntimeException('Unable to signal the kill-point sentinel.');
        }

        $deadline = microtime(true) + $this->safetyTimeoutSeconds;
        while (microtime(true) < $deadline) {
            usleep(20000);
        }

        // Reached only if the test harness failed to terminate this process in time.
        // Exit code 66 (unused elsewhere in this fixture) tells the parent test that
        // the kill point was reached but never exercised, rather than being confused
        // with a genuine termination.
        exit(66);
    }

    private function assertBoundTo(SiteId $site): void
    {
        if (!$site->equals($this->boundSite)) {
            throw new LogicException('Refusing a branding globals call for an unbound site.');
        }
    }
}
