<?php

/**
 * Verifies the brand kit **and the artefacts the running product actually serves**.
 *
 * This is check **V-06** of `docs/RebrandingPlan.md` §6.4, and part of the release gate
 * in §7.3. It exists as a script rather than a hand-run one-liner because V-06 was being
 * satisfied by ad-hoc verification, and an ad-hoc check is only as current as the last
 * person who remembered to run it.
 *
 * ## Why this file grew a second and a third class of check (S3-P2-35)
 *
 * Until 2026-08-25 every entry in `brand/manifests/SHA256SUMS` pointed at `brand/**` or
 * `docs/branding-production/**` — the *source* kit. The gate printed `123/123 verified`
 * and exited 0 while **every deployed artefact was unguarded**: the login wordmark, the
 * navbar symbol, both favicons, the portal marks, the four legacy `sites/<site>/images`
 * slots, the three dark-variant marks and all eleven installed font payloads could be
 * swapped for anything at all and the release gate stayed green. `123/123` was read as
 * "the branding is intact"; it only ever meant "the design package is intact".
 *
 * Coverage is deliberately **not** "hash everything under `public/`". A recorded hash is
 * the right instrument for one kind of file and the wrong instrument for another, so each
 * artefact is verified by the strategy its ownership actually supports:
 *
 *  1. **Source artefact** — `brand/**`, `docs/branding-production/*.md`. Immutable inputs,
 *     tracked in git. Verified against a **recorded hash**.
 *  2. **Deployed artefact** — the installer targets that are tracked in git:
 *     `public/images/**`, `sites/default/images/*`, and the module's dark-variant marks.
 *     Present in every checkout, so a recorded hash is enforceable unconditionally.
 *     Verified against a **recorded hash**.
 *  3. **Mirrored deployment** — `public/assets/fonts/thiqa/**`. `.gitignore` line 16
 *     (`/public/assets/*`) excludes this tree, so the files are absent from a clean
 *     checkout and a recorded hash in a checked-in manifest would red the gate on every
 *     fresh clone. They are byte-for-byte copies of sources this manifest already covers,
 *     so they are verified by **equality with the recorded hash of their source** — a
 *     check that cannot go stale, because re-installing from a changed source updates
 *     both sides at once.
 *
 * Three things are deliberately **out of scope**, and their absence here is a decision
 * rather than an oversight:
 *
 *  - **Generated deterministic artefacts** — `public/themes/*.css` and the generated SCSS
 *     under `interface/themes/thiqa/`. Build output whose bytes legitimately change on
 *     every rebuild; a recorded hash would be stale by design. Their authority is the
 *     generator's own `--check` mode (`composer branding-tokens-check`, which runs ahead
 *     of this script in the `branding-ci` gate) and `OpenEMR\Branding\DeployedArtefacts`.
 *  - **Tenant materialised output** — `public/branding/<site>/**`. Written at runtime,
 *     per tenant, and revisioned by the module; not a release artefact.
 *  - **Runtime data** — the database branding overlay globals. Not files, and mutable by
 *     an authorised operator by design.
 *
 * ## Re-issue discipline (this changed with S3-P2-35 — read it)
 *
 *  1. **The manifest covers documents, not just binaries.** 16 of its recorded-hash
 *     entries are the `docs/branding-production/*.md` design documents. Editing one of
 *     those breaks the release gate exactly as surely as tampering with a logo would —
 *     which is the point, but it surprises people. `docs/RebrandingBugs.md` RB-25 records
 *     an instance where an uncommitted documentation edit left the gate red and nothing
 *     noticed.
 *  2. **A brand source change now re-issues two entries, not one.** Changing, say,
 *     `brand/favicon/favicon.ico` means re-running `php tools/branding/install-assets.php`
 *     and re-issuing the recorded hash for **both** the source entry and each deployed
 *     entry fed from it. Class 3 needs no re-issue at all — re-running the installer is
 *     the whole fix.
 *  3. **Paths in the manifest are repo-root-relative**, so this must be run from the repo
 *     root (it resolves the root itself, so the working directory does not matter).
 *  4. When a mismatch is legitimate — you meant to change that document — re-issue the
 *     entry rather than deleting it, and say so in the change record.
 *
 * Line endings: every recorded-hash path outside `brand/` and `docs/branding-production/`
 * is either binary (`.png`, `.ico`, `.gif`) or matched by `.gitattributes:78`
 * (`*.svg text eol=lf`), so the working tree carries the same bytes on every platform
 * despite `core.autocrlf=true`. Nothing in classes 2 or 3 is eol-ambiguous.
 *
 * Usage:
 *   php tools/branding/verify-brand-manifest.php            # verify, exit 1 on any problem
 *   php tools/branding/verify-brand-manifest.php --quiet    # only report problems
 *
 * Exit codes: 0 = every check verifies; 1 = at least one missing/mismatched/unparsable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * How a manifest-recorded artefact is owned, which decides how it is verified.
 */
enum ArtefactClass
{
    /** `brand/**`, `docs/branding-production/*.md` — the immutable design package. */
    case Source;

    /** Installer targets tracked in git: `public/images/**`, `sites/*`, module marks. */
    case Deployed;

    public function label(): string
    {
        return match ($this) {
            self::Source => 'source artefacts   (brand/, docs/branding-production/)',
            self::Deployed => 'deployed artefacts (public/images/, sites/, module dark marks)',
        };
    }
}

/**
 * One `<sha256>  <repo-relative-path>` line of the manifest.
 */
final readonly class ManifestEntry
{
    public function __construct(
        public string $expectedHash,
        public string $path,
        public ArtefactClass $class,
    ) {
    }
}

/**
 * A deployed directory that mirrors a manifest-covered source directory one-for-one.
 *
 * The installer's font rows are a pure identity mapping under two roots
 * (`tools/branding/install-assets.php`, `fontMappings()` and `pdfFontMappings()`), so the
 * whole tree is expressed as a rule rather than a list. A font added to the kit and the
 * installer is covered here the moment it is installed, with nothing to keep in step.
 */
final readonly class MirroredTree
{
    /**
     * @param list<string> $sourceOnly repo-relative source paths the installer does not deploy
     */
    public function __construct(
        public string $deployedRoot,
        public string $sourceRoot,
        public array $sourceOnly,
    ) {
    }
}

/**
 * The outcome of one verification run, separated from the streams it is printed to.
 */
final readonly class VerificationReport
{
    /**
     * @param list<string> $problems
     */
    public function __construct(
        public string $summary,
        public array $problems,
    ) {
    }

    public function isClean(): bool
    {
        return $this->problems === [];
    }

    public function exitCode(): int
    {
        return $this->isClean() ? 0 : 1;
    }

    /**
     * The operator-facing failure block: what broke, and what to do about it.
     */
    public function failureText(): string
    {
        if ($this->isClean()) {
            return '';
        }

        $text = count($this->problems) . ' problem(s):' . PHP_EOL;
        foreach ($this->problems as $problem) {
            $text .= '  ' . $problem . PHP_EOL;
        }

        return $text
            . PHP_EOL
            . "If a change was intentional, re-issue that entry's hash and record why. "
            . 'Do not delete the entry.' . PHP_EOL
            . 'A changed brand source re-issues the source entry AND every deployed entry fed from it; '
            . 're-run `php tools/branding/install-assets.php` first.' . PHP_EOL;
    }
}

/**
 * Reads the manifest, runs every class of check, and reports.
 */
final class BrandManifestVerifier
{
    /**
     * Path prefixes that make an entry a class-1 source artefact. Everything else recorded
     * in the manifest is a class-2 deployed artefact.
     *
     * @var list<string>
     */
    private const SOURCE_PREFIXES = ['brand/', 'docs/'];

    /**
     * Declarative form of {@see MirroredTree}; constants cannot hold objects built here.
     *
     * @var list<array{deployedRoot: non-empty-string, sourceRoot: non-empty-string, sourceOnly: list<string>}>
     */
    private const MIRRORED_TREES = [
        [
            'deployedRoot' => 'public/assets/fonts/thiqa',
            'sourceRoot' => 'brand/typography/fonts',
            // Source-side documentation for the vendored Amiri faces. install-assets.php
            // ships OFL.txt with the fonts because SIL OFL 1.1 requires the licence to
            // travel with them; the README is provenance for maintainers and stays in the
            // kit. Anything else appearing here would be an unshipped source file and is
            // reported rather than ignored.
            'sourceOnly' => ['brand/typography/fonts/pdf/README-amiri.md'],
        ],
    ];

    /** @var list<string> */
    /**
     * Set to `1` by any environment that has installed the mirrored asset trees and therefore
     * expects the equality check to actually run. See {@see self::verifyMirroredTree()} for why
     * this is declared rather than inferred.
     */
    public const DEPLOYED_ASSETS_REQUIRED_ENV = 'OPENEMR_DEPLOYED_ASSETS_REQUIRED';

    private array $problems = [];

    /** @var array<string, int> */
    private array $verified = [];

    /** @var array<string, int> */
    private array $expected = [];

    /** @var list<string> */
    private array $mirrorSummaries = [];

    public function __construct(private readonly string $repoRoot)
    {
        foreach (ArtefactClass::cases() as $case) {
            $this->verified[$case->name] = 0;
            $this->expected[$case->name] = 0;
        }
    }

    /**
     * Runs every class of check and returns the result. Writes to no stream, so the same
     * code path is exercised by the CLI entry point below and by the contract test.
     */
    public function verify(): VerificationReport
    {
        $manifestPath = $this->repoRoot . '/brand/manifests/SHA256SUMS';

        $raw = is_file($manifestPath) ? file($manifestPath, FILE_IGNORE_NEW_LINES) : false;
        if ($raw === false) {
            return new VerificationReport(
                'brand/manifests/SHA256SUMS' . PHP_EOL,
                ['cannot read ' . $manifestPath],
            );
        }

        $entries = $this->parse($raw);

        foreach ($entries as $entry) {
            $this->expected[$entry->class->name]++;
            if ($this->verifyRecordedHash($entry)) {
                $this->verified[$entry->class->name]++;
            }
        }

        $recorded = [];
        foreach ($entries as $entry) {
            $recorded[$entry->path] = $entry->expectedHash;
        }

        foreach (self::MIRRORED_TREES as $tree) {
            $this->verifyMirroredTree(
                new MirroredTree($tree['deployedRoot'], $tree['sourceRoot'], $tree['sourceOnly']),
                $recorded,
            );
        }

        return new VerificationReport($this->summary(), $this->problems);
    }

    /**
     * @param list<string> $raw
     *
     * @return list<ManifestEntry>
     */
    private function parse(array $raw): array
    {
        $entries = [];

        foreach ($raw as $lineNumber => $line) {
            $trimmed = trim($line);

            // Section headers and spacing. GNU `sha256sum -c` skips these too, so the file
            // stays readable by the standard tool.
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            // Accept both the plain `<hash>  <path>` form and coreutils' binary-mode `*<path>`.
            $matches = [];
            if (preg_match('/\A([0-9a-f]{64})\s+\*?(.+)\z/', $trimmed, $matches) !== 1) {
                $this->problems[] = sprintf('line %d: unparsable manifest entry', $lineNumber + 1);
                continue;
            }

            [, $expected, $relativePath] = $matches;
            $relativePath = str_replace('\\', '/', $relativePath);

            $entries[] = new ManifestEntry($expected, $relativePath, self::classify($relativePath));
        }

        return $entries;
    }

    private static function classify(string $relativePath): ArtefactClass
    {
        foreach (self::SOURCE_PREFIXES as $prefix) {
            if (str_starts_with($relativePath, $prefix)) {
                return ArtefactClass::Source;
            }
        }

        return ArtefactClass::Deployed;
    }

    private function verifyRecordedHash(ManifestEntry $entry): bool
    {
        $absolutePath = $this->repoRoot . '/' . $entry->path;

        if (!is_file($absolutePath)) {
            $this->problems[] = $entry->path . ' (missing)';

            return false;
        }

        $actual = hash_file('sha256', $absolutePath);
        if ($actual === false) {
            $this->problems[] = $entry->path . ' (unreadable)';

            return false;
        }

        if (!hash_equals($entry->expectedHash, $actual)) {
            $this->problems[] = sprintf(
                '%s (mismatch: expected %s, got %s)',
                $entry->path,
                $entry->expectedHash,
                $actual,
            );

            return false;
        }

        return true;
    }

    /**
     * @param array<string, string> $recorded repo-relative path => recorded sha256
     */
    /**
     * Whether this environment has declared that the mirrored trees are installed.
     *
     * Only the exact string `1` counts. GitHub Actions renders an unset expression as the empty
     * string rather than omitting the variable, so a truthiness test would read `''` as "set" on
     * every leg that is meant to opt out.
     */
    private function deployedAssetsRequired(): bool
    {
        return getenv(self::DEPLOYED_ASSETS_REQUIRED_ENV) === '1';
    }

    private function verifyMirroredTree(MirroredTree $tree, array $recorded): void
    {
        $label = $tree->deployedRoot . ' <- ' . $tree->sourceRoot;

        $expectedRelatives = [];
        foreach (array_keys($recorded) as $path) {
            if (!str_starts_with($path, $tree->sourceRoot . '/')) {
                continue;
            }
            if (in_array($path, $tree->sourceOnly, true)) {
                continue;
            }

            $expectedRelatives[substr($path, strlen($tree->sourceRoot) + 1)] = $path;
        }

        $deployedRelatives = $this->filesUnder($this->repoRoot . '/' . $tree->deployedRoot);

        if ($deployedRelatives === []) {
            // S4B-08: absent is not automatically innocent, and inferring it from the filesystem
            // is how this check came to run in ZERO CI legs while printing a reassuring line.
            // `/public/assets/*` is gitignored and nothing in CI invoked install-assets.php, so
            // every run took this branch: the byte-equality guarantee for the eleven mirrored
            // font files was never once exercised by the gate that claims to cover them.
            //
            // The environment declares its obligation rather than the verifier guessing it —
            // the same wiring `e203d5bdd` used for the locked Q77 deployed-theme check, and for
            // the same reason. Where the tree is supposed to be installed, absence is a hard
            // failure; everywhere else the skip survives, which is correct for a bare checkout
            // and for developer hosts that install off-tree.
            if ($this->deployedAssetsRequired()) {
                $this->problems[] = sprintf(
                    '%s: not installed, but %s is set. Either install-assets.php did not run '
                        . 'before this gate, or it failed silently — and an uninstalled tree means '
                        . 'the equality check covering %d recorded source files did not run at all.',
                    $label,
                    self::DEPLOYED_ASSETS_REQUIRED_ENV,
                    count($expectedRelatives),
                );

                return;
            }

            // A clean checkout: `.gitignore` excludes this tree and install-assets.php has
            // not run. Nothing to verify, and nothing wrong.
            $this->mirrorSummaries[] = sprintf(
                '%s: not installed (git-ignored build output; run install-assets.php to populate)',
                $label,
            );

            return;
        }

        // Once the tree exists at all, it must be complete. A half-installed font directory
        // is a real defect: the browser 404s and silently falls back to a system face.
        foreach ($expectedRelatives as $relative => $sourcePath) {
            if (!in_array($relative, $deployedRelatives, true)) {
                $this->problems[] = sprintf(
                    '%s/%s (missing from a populated deployment; source %s is installed by install-assets.php)',
                    $tree->deployedRoot,
                    $relative,
                    $sourcePath,
                );
            }
        }

        $identical = 0;

        foreach ($deployedRelatives as $relative) {
            $deployedPath = $tree->deployedRoot . '/' . $relative;
            $sourcePath = $expectedRelatives[$relative] ?? null;

            if ($sourcePath === null) {
                $this->problems[] = sprintf(
                    '%s (no manifest-covered source at %s/%s, so nothing pins its bytes)',
                    $deployedPath,
                    $tree->sourceRoot,
                    $relative,
                );
                continue;
            }

            $actual = hash_file('sha256', $this->repoRoot . '/' . $deployedPath);
            if ($actual === false) {
                $this->problems[] = $deployedPath . ' (unreadable)';
                continue;
            }

            if (!hash_equals($recorded[$sourcePath], $actual)) {
                $this->problems[] = sprintf(
                    '%s (differs from its brand source %s: expected %s, got %s)',
                    $deployedPath,
                    $sourcePath,
                    $recorded[$sourcePath],
                    $actual,
                );
                continue;
            }

            $identical++;
        }

        $this->mirrorSummaries[] = sprintf(
            '%s: %d/%d identical to their manifest-covered source',
            $label,
            $identical,
            count($expectedRelatives),
        );
    }

    /**
     * Repo-relative-to-$root paths of every file below $root, sorted.
     *
     * @return list<string>
     */
    private function filesUnder(string $root): array
    {
        if (!is_dir($root)) {
            return [];
        }

        $found = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $entry) {
            if (!$entry instanceof \SplFileInfo || !$entry->isFile()) {
                continue;
            }

            $found[] = str_replace('\\', '/', substr($entry->getPathname(), strlen($root) + 1));
        }

        sort($found);

        return $found;
    }

    private function summary(): string
    {
        $lines = ['brand/manifests/SHA256SUMS'];
        $lines[] = '  recorded-hash coverage';

        foreach (ArtefactClass::cases() as $case) {
            $lines[] = sprintf(
                '    %s : %d/%d verified',
                str_pad($case->label(), 62),
                $this->verified[$case->name],
                $this->expected[$case->name],
            );
        }

        $lines[] = '  equality-with-source coverage (git-ignored deployments)';
        foreach ($this->mirrorSummaries as $summary) {
            $lines[] = '    ' . $summary;
        }

        $lines[] = '';

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}

/*
 * Library mode. A caller that defines this constant before requiring the file gets the
 * class definitions without the CLI run — which is how the contract test drives the real
 * verifier against a throwaway fixture repository, in-process. Without it the script
 * behaves exactly as before.
 */
if (defined('OPENEMR_BRAND_MANIFEST_VERIFIER_LIBRARY')) {
    return;
}

$options = getopt('', ['quiet', 'help']);

if (isset($options['help'])) {
    fwrite(STDOUT, "Usage: php tools/branding/verify-brand-manifest.php [--quiet]\n");
    exit(0);
}

$report = (new BrandManifestVerifier(dirname(__DIR__, 2)))->verify();

if ($report->isClean()) {
    if (!isset($options['quiet'])) {
        fwrite(STDOUT, $report->summary);
    }

    exit(0);
}

fwrite(STDERR, $report->summary . $report->failureText());

exit(1);
