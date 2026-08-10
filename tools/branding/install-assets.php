#!/usr/bin/env php
<?php

/**
 * Installs the approved Thiqa brand kit assets into the slots the application reads.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/*
 * Deliberately self-contained: no vendor/autoload.php, no OpenEMR bootstrap and
 * no database. The overlay has to be runnable from a bare checkout at deploy
 * time, before composer install and before the application can boot.
 *
 * Source of truth for the mapping is docs/RebrandingPlan.md section 3.7.6, and
 * for the slot inventory docs/rebranding.md sections 9.2/9.3 (BRAND-014..033).
 * Every source is hash-verified against brand/manifests/asset-manifest.json
 * before it is allowed to overwrite anything.
 */

/**
 * Outcome of considering one mapping row.
 */
enum InstallStatus
{
    case Created;
    case Replaced;
    case Unchanged;
    case Denied;
    case Failed;
}

/**
 * One source-to-target row of the section 3.7.6 overlay table.
 */
final readonly class AssetMapping
{
    public function __construct(
        public string $brandId,
        public string $source,
        public string $target,
        public string $note = '',
    ) {
    }
}

/**
 * What the installer decided (and, outside dry-run, did) for one mapping row.
 */
final readonly class InstallResult
{
    public function __construct(
        public AssetMapping $mapping,
        public InstallStatus $status,
        public string $detail,
    ) {
    }
}

/**
 * Guards the regulatory / third-party-trademark assets named by locked
 * constraint C7. These are never writable by this installer, whatever a
 * mapping row or a future edit to it might say.
 */
final readonly class DenyList
{
    /**
     * Repository-relative paths that must never be written.
     *
     * @var list<string>
     */
    private const PATHS = [
        'public/images/cms1500.png',
        'public/images/ub04.svg',
    ];

    /**
     * Filenames that must never be written wherever they appear in the tree.
     *
     * @var list<string>
     */
    private const FILENAMES = [
        'visa_mc_disc_credit_card_logos_176x35.gif',
    ];

    /**
     * Directory prefixes that are entirely off limits.
     *
     * @var list<string>
     */
    private const PREFIXES = [
        'Documentation/',
    ];

    /**
     * Returns the reason $relativePath is denied, or null when writing is allowed.
     */
    public function reject(string $relativePath): ?string
    {
        $normalised = strtolower(str_replace('\\', '/', $relativePath));

        foreach (self::PATHS as $denied) {
            if ($normalised === strtolower($denied)) {
                return 'C7 deny-list: regulatory claim form (' . $denied . ')';
            }
        }

        foreach (self::FILENAMES as $denied) {
            if (basename($normalised) === strtolower($denied)) {
                return 'C7 deny-list: third-party trademark (' . $denied . ')';
            }
        }

        foreach (self::PREFIXES as $prefix) {
            if (str_starts_with($normalised, strtolower($prefix))) {
                return 'C7 deny-list: protected tree (' . $prefix . ')';
            }
        }

        return null;
    }
}

/**
 * Reads brand/manifests/asset-manifest.json and answers "what is the approved
 * SHA-256 for this kit path?".
 */
final class AssetManifest
{
    /** @var array<string, string> repository-relative path => lowercase sha256 */
    private array $hashes = [];

    public function __construct(string $manifestFile)
    {
        $raw = @file_get_contents($manifestFile);
        if ($raw === false) {
            throw new \RuntimeException('Cannot read asset manifest: ' . $manifestFile);
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \RuntimeException('Asset manifest is not valid JSON: ' . $manifestFile);
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('Asset manifest must decode to an array: ' . $manifestFile);
        }

        foreach ($decoded as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $path = $entry['relative_path'] ?? null;
            $hash = $entry['sha256'] ?? null;
            if (!is_string($path) || !is_string($hash)) {
                continue;
            }
            $this->hashes[str_replace('\\', '/', $path)] = strtolower($hash);
        }

        if ($this->hashes === []) {
            throw new \RuntimeException('Asset manifest contains no usable entries: ' . $manifestFile);
        }
    }

    public function expectedHash(string $relativePath): ?string
    {
        return $this->hashes[str_replace('\\', '/', $relativePath)] ?? null;
    }

    public function count(): int
    {
        return count($this->hashes);
    }
}

/**
 * Performs the overlay: verify, then copy, then re-verify.
 */
final readonly class AssetInstaller
{
    public function __construct(
        private string $repoRoot,
        private AssetManifest $manifest,
        private DenyList $denyList,
        private bool $dryRun,
    ) {
    }

    /**
     * @param  list<AssetMapping> $mappings
     * @return list<InstallResult>
     */
    public function install(array $mappings): array
    {
        $results = [];
        foreach ($mappings as $mapping) {
            $results[] = $this->installOne($mapping);
        }

        return $results;
    }

    private function installOne(AssetMapping $mapping): InstallResult
    {
        $denied = $this->denyList->reject($mapping->target);
        if ($denied !== null) {
            return new InstallResult($mapping, InstallStatus::Denied, $denied);
        }

        $sourceFile = $this->repoRoot . '/' . $mapping->source;
        if (!is_file($sourceFile)) {
            return new InstallResult($mapping, InstallStatus::Failed, 'source missing: ' . $mapping->source);
        }

        $expected = $this->manifest->expectedHash($mapping->source);
        if ($expected === null) {
            return new InstallResult(
                $mapping,
                InstallStatus::Failed,
                'source not listed in asset-manifest.json: ' . $mapping->source,
            );
        }

        $actual = $this->hashFile($sourceFile);
        if ($actual === null) {
            return new InstallResult($mapping, InstallStatus::Failed, 'cannot hash source: ' . $mapping->source);
        }

        if ($actual !== $expected) {
            return new InstallResult(
                $mapping,
                InstallStatus::Failed,
                'SHA-256 mismatch, refusing to copy (manifest ' . substr($expected, 0, 16)
                    . '..., file ' . substr($actual, 0, 16) . '...)',
            );
        }

        $targetFile = $this->repoRoot . '/' . $mapping->target;
        $targetExists = is_file($targetFile);

        if ($targetExists) {
            $current = $this->hashFile($targetFile);
            if ($current === $actual) {
                return new InstallResult($mapping, InstallStatus::Unchanged, 'already installed (' . substr($actual, 0, 16) . '...)');
            }
        }

        $status = $targetExists ? InstallStatus::Replaced : InstallStatus::Created;

        if ($this->dryRun) {
            return new InstallResult($mapping, $status, 'would write ' . substr($actual, 0, 16) . '...');
        }

        $targetDir = dirname($targetFile);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0o755, true) && !is_dir($targetDir)) {
            return new InstallResult($mapping, InstallStatus::Failed, 'cannot create directory: ' . dirname($mapping->target));
        }

        if (!copy($sourceFile, $targetFile)) {
            return new InstallResult($mapping, InstallStatus::Failed, 'copy failed: ' . $mapping->target);
        }

        clearstatcache(true, $targetFile);
        $written = $this->hashFile($targetFile);
        if ($written !== $actual) {
            return new InstallResult($mapping, InstallStatus::Failed, 'post-copy verification failed: ' . $mapping->target);
        }

        return new InstallResult($mapping, $status, 'wrote ' . substr($written, 0, 16) . '...');
    }

    private function hashFile(string $file): ?string
    {
        $hash = hash_file('sha256', $file);

        return $hash === false ? null : strtolower($hash);
    }
}

/**
 * The section 3.7.6 overlay table, plus argument parsing and reporting.
 */
final class InstallAssetsCommand
{
    private const USAGE = <<<'TXT'
        Usage: php tools/branding/install-assets.php [options]

        Installs the approved Thiqa brand kit assets (brand/) into the logo,
        favicon and legacy slots the running application reads.

        Options:
          --dry-run        Report what would change and write nothing.
          --site=<id>      Site whose sites/<id>/images slots are provisioned.
                           Default: default
          --root=<path>    Repository root. Default: two levels above this script.
          --help           Show this message.

        Exit codes: 0 success, 1 one or more rows failed, 2 bad usage.

        TXT;

    /**
     * The overlay mapping, in the order of docs/RebrandingPlan.md section 3.7.6.
     *
     * @return list<AssetMapping>
     */
    public static function mappings(string $site): array
    {
        $siteImages = 'sites/' . $site . '/images';

        return [
            new AssetMapping(
                'BRAND-014',
                'brand/logos/login/login-primary-1053x390.png',
                'public/images/logos/core/login/primary/logo.png',
            ),
            new AssetMapping(
                'BRAND-015',
                'brand/logos/login/login-secondary-300x100.png',
                'public/images/logos/core/login/secondary/logo.png',
            ),
            new AssetMapping(
                'BRAND-016',
                'brand/logos/login/login-small-a-101x100.png',
                'public/images/logos/core/login/small_logo_1/logo.png',
            ),
            new AssetMapping(
                'BRAND-017',
                'brand/logos/login/login-small-b-101x100.png',
                'public/images/logos/core/login/small_logo_2/logo.png',
            ),
            new AssetMapping(
                'BRAND-018',
                'brand/master/brand-symbol.svg',
                'public/images/logos/core/menu/primary/logo.svg',
                'navbar symbol, rendered at a fixed height="16" by main.php:48',
            ),
            new AssetMapping(
                'BRAND-019',
                'brand/favicon/favicon.ico',
                'public/images/logos/core/favicon/favicon.ico',
                'multi-frame 16/32/48',
            ),
            new AssetMapping(
                'BRAND-020',
                'brand/logos/portal/portal-login-primary-1053x390.png',
                'public/images/logos/portal/login/primary/logo.png',
            ),
            new AssetMapping(
                'BRAND-021',
                'brand/logos/portal/portal-login-secondary-300x100.png',
                'public/images/logos/portal/login/secondary/logo.png',
                'closes the BRAND-021 gap: this slot has never had a default',
            ),
            new AssetMapping(
                'BRAND-022',
                'brand/logos/portal/portal-navbar-870x222.png',
                'public/images/logos/portal/menu/primary/logo.png',
            ),
            new AssetMapping(
                'BRAND-025',
                'brand/logos/legacy/login-logo.png',
                'public/images/login-logo.png',
                'legacy duplicate',
            ),
            new AssetMapping(
                'BRAND-026',
                'brand/logos/legacy/logo-full-con.png',
                'public/images/logo-full-con.png',
                'legacy duplicate',
            ),
            new AssetMapping(
                'BRAND-027',
                'brand/logos/legacy/menu-logo.png',
                'public/images/menu-logo.png',
                'legacy duplicate',
            ),
            new AssetMapping(
                'BRAND-028',
                'brand/favicon/favicon-32x32.png',
                'public/images/favicon-32x32.png',
                'legacy duplicate',
            ),
            new AssetMapping(
                'BRAND-029',
                'brand/favicon/favicon.ico',
                'public/images/favicon.ico',
                'new file, fixes 5 x HTTP 404',
            ),
            new AssetMapping(
                'BRAND-030',
                'brand/logos/legacy/login_logo.gif',
                $siteImages . '/login_logo.gif',
            ),
            new AssetMapping(
                'BRAND-031',
                'brand/logos/legacy/logo_1.png',
                $siteImages . '/logo_1.png',
            ),
            new AssetMapping(
                'BRAND-032',
                'brand/logos/legacy/logo_2.png',
                $siteImages . '/logo_2.png',
            ),
            new AssetMapping(
                'BRAND-033',
                'brand/logos/legacy/practice_logo.gif',
                $siteImages . '/practice_logo.gif',
                'referenced by statement.inc.php but never shipped',
            ),
            ...self::fontMappings(),
        ];
    }

    /**
     * The eight web fonts, installed exactly like the image assets.
     *
     * These are here because `.gitignore` line 16 (`/public/assets/*`) excludes the whole
     * built-assets tree, so the woff2 files that the compiled themes reference by
     * `url(../assets/fonts/thiqa/…)` were present on one machine and absent from every
     * clean checkout. Nothing failed loudly: the browser 404s and silently falls back to a
     * system font, which for IBM Plex Sans Arabic means the Arabic surface stops matching
     * the locked type specimen.
     *
     * Routing them through this installer rather than un-ignoring the directory keeps
     * `brand/` the single source of truth, keeps built output out of the repository, and
     * gives the fonts the same SHA-256 manifest verification every image row already gets.
     *
     * @return list<AssetMapping>
     */
    private static function fontMappings(): array
    {
        $families = [
            'Inter' => 'BRAND-076',
            'IBMPlexSansArabic' => 'BRAND-077',
        ];

        $mappings = [];

        foreach ($families as $family => $brandId) {
            foreach (['Regular', 'Medium', 'SemiBold', 'Bold'] as $weight) {
                $file = $family . '-' . $weight . '.woff2';

                $mappings[] = new AssetMapping(
                    $brandId,
                    'brand/typography/fonts/' . $file,
                    'public/assets/fonts/thiqa/' . $file,
                    'referenced by the compiled themes as url(../assets/fonts/thiqa/' . $file . ')',
                );
            }
        }

        return $mappings;
    }

    /**
     * @param list<string> $arguments
     */
    public function run(array $arguments, string $defaultRoot): int
    {
        $dryRun = false;
        $site = 'default';
        $root = $defaultRoot;

        foreach ($arguments as $argument) {
            if ($argument === '--help' || $argument === '-h') {
                fwrite(STDOUT, self::USAGE);

                return 0;
            }
            if ($argument === '--dry-run') {
                $dryRun = true;
                continue;
            }
            if (str_starts_with($argument, '--site=')) {
                $site = substr($argument, 7);
                continue;
            }
            if (str_starts_with($argument, '--root=')) {
                $root = substr($argument, 7);
                continue;
            }

            fwrite(STDERR, 'Unknown option: ' . $argument . "\n" . self::USAGE);

            return 2;
        }

        if ($site === '' || preg_match('/^[A-Za-z0-9._-]+$/', $site) !== 1) {
            fwrite(STDERR, "Invalid --site value; expected a simple site id.\n");

            return 2;
        }

        $root = rtrim(str_replace('\\', '/', $root), '/');
        if (!is_dir($root . '/brand')) {
            fwrite(STDERR, 'Repository root does not contain brand/: ' . $root . "\n");

            return 2;
        }

        try {
            $manifest = new AssetManifest($root . '/brand/manifests/asset-manifest.json');
        } catch (\RuntimeException $e) {
            fwrite(STDERR, 'Could not load the asset manifest: ' . $e->getMessage() . "\n");

            return 1;
        }

        $mappings = self::mappings($site);
        $installer = new AssetInstaller($root, $manifest, new DenyList(), $dryRun);
        $results = $installer->install($mappings);

        return $this->report($results, $dryRun, $site, $root, $manifest->count());
    }

    /**
     * @param list<InstallResult> $results
     */
    private function report(array $results, bool $dryRun, string $site, string $root, int $manifestEntries): int
    {
        $mode = $dryRun ? 'DRY RUN - nothing is written' : 'LIVE';
        $lines = [
            'Thiqa brand asset overlay (RebrandingPlan.md 3.7.6)',
            '  mode      : ' . $mode,
            '  root      : ' . $root,
            '  site      : ' . $site,
            '  manifest  : ' . $manifestEntries . ' hashed entries',
            '',
        ];

        $tally = [];
        foreach (InstallStatus::cases() as $case) {
            $tally[$case->name] = 0;
        }

        foreach ($results as $result) {
            $tally[$result->status->name]++;
            $lines[] = sprintf(
                '  [%-9s] %-10s %s',
                strtoupper($result->status->name),
                $result->mapping->brandId,
                $result->mapping->target,
            );
            $lines[] = sprintf(
                '  %-11s   from %s',
                '',
                $result->mapping->source,
            );
            $lines[] = sprintf('  %-11s   %s', '', $result->detail);
            if ($result->mapping->note !== '') {
                $lines[] = sprintf('  %-11s   note: %s', '', $result->mapping->note);
            }
            $lines[] = '';
        }

        $lines[] = sprintf(
            '  Summary: %d created, %d replaced, %d unchanged, %d denied, %d failed (%d rows)',
            $tally[InstallStatus::Created->name],
            $tally[InstallStatus::Replaced->name],
            $tally[InstallStatus::Unchanged->name],
            $tally[InstallStatus::Denied->name],
            $tally[InstallStatus::Failed->name],
            count($results),
        );
        $lines[] = '';

        fwrite(STDOUT, implode("\n", $lines));

        return ($tally[InstallStatus::Failed->name] > 0) ? 1 : 0;
    }
}

/**
 * @param array<array-key, mixed> $rawArgv
 */
$main = static function (array $rawArgv): int {
    $arguments = [];
    foreach (array_slice(array_values($rawArgv), 1) as $argument) {
        if (is_scalar($argument)) {
            $arguments[] = (string) $argument;
        }
    }

    // dirname(__DIR__, 1): <repo>/tools/branding -> <repo>/tools; one more gets
    // the repository root. Derived from the script location, not the cwd, so the
    // default is stable wherever it is invoked from.
    return (new InstallAssetsCommand())->run($arguments, dirname(__DIR__, 2));
};

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");

    exit(2);
}

if (!isset($argv)) {
    fwrite(STDERR, "register_argc_argv must be enabled to run this script.\n");

    exit(2);
}

exit($main($argv));
