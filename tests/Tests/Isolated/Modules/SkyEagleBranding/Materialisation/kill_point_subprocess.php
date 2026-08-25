<?php

/**
 * Standalone entry point for MaterialiserKillRecoveryTest.
 *
 * Runs one materialisation whose database write blocks at a sentinel file, so the parent
 * test process can terminate this process at the OS level exactly between the file-commit
 * steps (5a/5b) and the database write (5c/5d) -- the boundary audit finding AR-P2-006
 * asked to have proven, not merely simulated with a thrown exception.
 *
 * Usage: php kill_point_subprocess.php <treeRoot> <siteId> <sentinelPath>
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require __DIR__ . '/../../../../../../vendor/autoload.php';
require __DIR__ . '/materialisation_autoloader.php';
require __DIR__ . '/FrozenClock.php';
require __DIR__ . '/KillPointGlobalsWriter.php';

use OpenEMR\Modules\SkyEagleBranding\Accessibility\ContrastCalculator;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\AtomicFileWriter;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\BrandingMaterialiser;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\JsonFileTier1PaletteProvider;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\MaterialisationJob;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\TenantBrandingPaths;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\TokenCssWriter;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use OpenEMR\Modules\SkyEagleBranding\Token\CssVariableRenderer;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSetParser;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenValidator;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation\FrozenClock;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation\KillPointGlobalsWriter;
use Psr\Log\NullLogger;

/** @var list<string> $argv */
[, $treeRoot, $siteValue, $sentinelPath] = $argv;

$site = new SiteId($siteValue);
$paths = new TenantBrandingPaths($treeRoot . '/module/public/branding', $treeRoot . '/sites');
$files = new AtomicFileWriter();

$materialiser = new BrandingMaterialiser(
    new TokenValidator(new ContrastCalculator()),
    new JsonFileTier1PaletteProvider(
        new TokenSetParser(),
        __DIR__ . '/../../../../../../brand/tokens/thiqa-tokens.json',
    ),
    new TokenCssWriter(new CssVariableRenderer(), $files, $paths),
    $files,
    $paths,
    new KillPointGlobalsWriter($site, $sentinelPath),
    FrozenClock::at('2026-08-09T12:00:00+00:00'),
    new NullLogger(),
);

$job = MaterialisationJob::forRevision($site, new BrandingRevision(1))
    ->withOverlays(['link.default' => '#1E4574'], ['link.default' => '#B7D9F5']);

$materialiser->materialise($job);

// Unreachable unless the harness failed to terminate this process before the writer's
// own safety timeout; distinct from that timeout's exit(66) so the parent test can tell
// "ran to completion" apart from "gave up waiting to be killed".
exit(77);
