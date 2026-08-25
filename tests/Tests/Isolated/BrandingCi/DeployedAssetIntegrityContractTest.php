<?php

/**
 * Proves the release gate detects tampering with the artefacts the product actually serves.
 *
 * S3-P2-35: `brand/manifests/SHA256SUMS` used to cover only `brand/**` and
 * `docs/branding-production/**`. `verify-brand-manifest.php` printed `123/123 verified` and
 * exited 0 while every deployed logo, favicon and font could have been replaced. The tests
 * here assert the **effect** of the fix — a tampered deployed byte fails the gate — rather
 * than the presence of any particular manifest line, and they assert the ownership boundary
 * that keeps generated theme output *out* of the recorded-hash manifest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use OpenEMR\Branding\BrandManifestVerifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class DeployedAssetIntegrityContractTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';

    /**
     * Regulatory / third-party-trademark paths install-assets.php names only to refuse to
     * write them (its `DenyList`, locked constraint C7). They are upstream artefacts, not
     * brand outputs, so they are outside this manifest by design.
     *
     * @var list<string>
     */
    private const DENY_LISTED = [
        'public/images/cms1500.png',
        'public/images/ub04.svg',
    ];

    private string $fixtureRoot = '';

    protected function setUp(): void
    {
        self::loadVerifier();

        $root = sys_get_temp_dir() . '/oe-brand-integrity-' . bin2hex(random_bytes(8));
        $this->fixtureRoot = $root;
        $this->buildFixture($root);
    }

    protected function tearDown(): void
    {
        if ($this->fixtureRoot !== '') {
            self::removeTree($this->fixtureRoot);
            $this->fixtureRoot = '';
        }
    }

    /**
     * The fixture must pass before any tamper, or a later failure proves nothing.
     */
    public function testUntamperedFixtureVerifiesCleanly(): void
    {
        $report = (new BrandManifestVerifier($this->fixtureRoot))->verify();

        self::assertSame([], $report->problems);
        self::assertSame(0, $report->exitCode());
    }

    /**
     * One byte of a deployed, git-tracked artefact — the class the finding was about.
     */
    public function testSingleTamperedByteInADeployedArtefactFailsTheGate(): void
    {
        $target = $this->fixtureRoot . '/public/images/mark.svg';
        $original = (string) file_get_contents($target);

        file_put_contents($target, substr_replace($original, 'X', 40, 1));

        $report = (new BrandManifestVerifier($this->fixtureRoot))->verify();

        self::assertSame(1, $report->exitCode(), 'A replaced deployed logo must fail the gate.');
        self::assertCount(1, $report->problems);
        self::assertStringContainsString('public/images/mark.svg', $report->problems[0]);
        self::assertStringContainsString('mismatch', $report->problems[0]);

        // Restore byte-for-byte and prove the gate goes green again, so the failure above is
        // attributable to the tampered byte and nothing else.
        file_put_contents($target, $original);
        self::assertSame(0, (new BrandManifestVerifier($this->fixtureRoot))->verify()->exitCode());
    }

    public function testDeletedDeployedArtefactFailsTheGate(): void
    {
        unlink($this->fixtureRoot . '/public/images/mark.svg');

        $report = (new BrandManifestVerifier($this->fixtureRoot))->verify();

        self::assertSame(1, $report->exitCode());
        self::assertSame(['public/images/mark.svg (missing)'], $report->problems);
    }

    /**
     * The git-ignored class, verified by equality with its source rather than a recorded hash.
     */
    public function testSingleTamperedByteInAMirroredDeploymentFailsTheGate(): void
    {
        $target = $this->fixtureRoot . '/public/assets/fonts/thiqa/Face-Regular.woff2';
        $original = (string) file_get_contents($target);

        file_put_contents($target, substr_replace($original, 'Z', 5, 1));

        $report = (new BrandManifestVerifier($this->fixtureRoot))->verify();

        self::assertSame(1, $report->exitCode(), 'A swapped deployed font must fail the gate.');
        self::assertCount(1, $report->problems);
        self::assertStringContainsString('public/assets/fonts/thiqa/Face-Regular.woff2', $report->problems[0]);
        self::assertStringContainsString('brand/typography/fonts/Face-Regular.woff2', $report->problems[0]);

        file_put_contents($target, $original);
        self::assertSame(0, (new BrandManifestVerifier($this->fixtureRoot))->verify()->exitCode());
    }

    /**
     * A file dropped into the deployed font tree with no manifest-covered source behind it is
     * unpinned by definition, so it is reported rather than ignored.
     */
    public function testUnpairedFileInTheMirroredTreeFailsTheGate(): void
    {
        file_put_contents($this->fixtureRoot . '/public/assets/fonts/thiqa/Attacker.woff2', 'payload');

        $report = (new BrandManifestVerifier($this->fixtureRoot))->verify();

        self::assertSame(1, $report->exitCode());
        self::assertCount(1, $report->problems);
        self::assertStringContainsString('Attacker.woff2', $report->problems[0]);
        self::assertStringContainsString('no manifest-covered source', $report->problems[0]);
    }

    /**
     * A half-installed font directory is a real defect: the browser 404s and silently falls
     * back to a system face, so the Arabic surface stops matching the locked specimen.
     */
    public function testPartiallyInstalledMirroredTreeFailsTheGate(): void
    {
        unlink($this->fixtureRoot . '/public/assets/fonts/thiqa/Face-Regular.woff2');
        file_put_contents($this->fixtureRoot . '/public/assets/fonts/thiqa/Face-Bold.woff2', 'bold-bytes');

        $report = (new BrandManifestVerifier($this->fixtureRoot))->verify();

        self::assertSame(1, $report->exitCode());
        self::assertStringContainsString(
            'missing from a populated deployment',
            implode("\n", $report->problems),
        );
    }

    /**
     * `.gitignore` line 16 excludes `/public/assets/*`, so a clean checkout has no deployed
     * fonts at all. That is not a tampering signal and must not red the gate — otherwise the
     * gate is one people learn to ignore.
     */
    public function testAbsentMirroredTreeIsNotAFailure(): void
    {
        self::removeTree($this->fixtureRoot . '/public/assets');

        $report = (new BrandManifestVerifier($this->fixtureRoot))->verify();

        self::assertSame([], $report->problems);
        self::assertSame(0, $report->exitCode());
        self::assertStringContainsString('not installed', $report->summary);
    }

    /**
     * S4B-08: an absent tree is only innocent where nobody promised to install one.
     *
     * The skip above is correct for a bare checkout, and it is exactly what CI took in every
     * leg — `/public/assets/*` is gitignored and no workflow step ever invoked
     * install-assets.php, so the eleven-file byte-equality guarantee ran precisely nowhere
     * while the gate printed a line implying it had been considered.
     *
     * The environment now declares its obligation, the same wiring `e203d5bdd` gave the locked
     * Q77 deployed-theme check. `putenv()` rather than a fixture flag, because the production
     * code reads the real environment and a test that stubs that reads nothing real.
     */
    public function testAbsentMirroredTreeIsAFailureWhereTheEnvironmentDeclaresItInstalled(): void
    {
        self::removeTree($this->fixtureRoot . '/public/assets');

        $variable = BrandManifestVerifier::DEPLOYED_ASSETS_REQUIRED_ENV;
        $restore = getenv($variable);

        try {
            putenv($variable . '=1');
            $report = (new BrandManifestVerifier($this->fixtureRoot))->verify();
        } finally {
            if ($restore === false) {
                putenv($variable);
            } else {
                putenv($variable . '=' . $restore);
            }
        }

        self::assertNotSame([], $report->problems, 'A declared-but-absent tree must fail the gate.');
        self::assertSame(1, $report->exitCode());
        self::assertStringContainsString($variable, implode("\n", $report->problems));

        // The environment must be left exactly as found, or every later test in this process
        // inherits an obligation it never declared.
        self::assertSame($restore, getenv($variable));
    }

    /**
     * The obligation is worthless unless something actually installs the tree first, and
     * unless the leg that installs it is the leg that declares it.
     *
     * Both halves are asserted against the workflow because both are deletable in one line,
     * and deleting either restores the silent skip this pair exists to close.
     */
    public function testTheWorkflowInstallsTheMirroredTreesAndDeclaresTheObligation(): void
    {
        $workflow = file_get_contents(self::PROJECT_ROOT . '/.github/workflows/isolated-tests.yml');
        self::assertIsString($workflow);
        $workflow = str_replace("\r\n", "\n", $workflow);

        $install = strpos($workflow, 'run: php tools/branding/install-assets.php');
        self::assertIsInt(
            $install,
            'No CI step installs the mirrored asset trees, so the equality check runs in zero legs.',
        );

        $gate = strpos($workflow, 'run: composer branding-ci');
        self::assertIsInt($gate);
        self::assertLessThan(
            $gate,
            $install,
            'Assets must be installed BEFORE the gate runs; afterwards the verifier still sees '
            . 'an empty tree and the obligation fails for the wrong reason.',
        );

        self::assertStringContainsString(
            BrandManifestVerifier::DEPLOYED_ASSETS_REQUIRED_ENV . ": \${{ matrix.php-version == '8.2' && '1' || '' }}",
            $workflow,
            'The gate step must declare the obligation on exactly the leg that installed the trees.',
        );
    }

    /**
     * The summary must name each class separately, so `123/123` can never again be read as
     * "everything deployed is intact".
     */
    public function testSummaryReportsEachOwnershipClassSeparately(): void
    {
        $summary = (new BrandManifestVerifier($this->fixtureRoot))->verify()->summary;

        self::assertStringContainsString('source artefacts', $summary);
        self::assertStringContainsString('deployed artefacts', $summary);
        self::assertStringContainsString('equality-with-source coverage', $summary);
    }

    // ---------------------------------------------------------------- real-repository drift

    /**
     * Drift guard: an installer row added under `public/images/` without a manifest entry
     * would silently re-open the finding.
     */
    public function testEveryInstallerImageTargetIsManifestCovered(): void
    {
        $installer = $this->read('tools/branding/install-assets.php');

        $matches = [];
        preg_match_all('~\x27(public/images/[^\x27]+)\x27~', $installer, $matches);

        $targets = array_values(array_diff(array_unique($matches[1]), self::DENY_LISTED));
        self::assertNotSame([], $targets, 'The extraction found no installer image targets at all.');

        self::assertSame([], $this->uncovered($targets));
    }

    /**
     * Same guard for the `sites/<site>/images` rows, which the installer builds from a
     * `$siteImages` prefix rather than writing the path out in full.
     */
    public function testEveryInstallerSiteImageTargetIsManifestCovered(): void
    {
        $installer = $this->read('tools/branding/install-assets.php');

        $matches = [];
        preg_match_all('~\$siteImages\s*\.\s*\x27(/[^\x27]+)\x27~', $installer, $matches);

        self::assertNotSame([], $matches[1], 'The extraction found no sites/<site>/images rows.');

        $targets = array_map(
            static fn (string $suffix): string => 'sites/default/images' . $suffix,
            $matches[1],
        );

        self::assertSame([], $this->uncovered($targets));
    }

    /**
     * Whole-subtree sweeps for the three trees that exist only to hold branding output. A new
     * slot directory is covered the moment it is added, with no list to keep in step.
     *
     * @param non-empty-string $tree
     */
    #[DataProvider('brandingOnlyTreeProvider')]
    public function testEveryFileInABrandingOnlyTreeIsManifestCovered(string $tree): void
    {
        $absolute = $this->root() . '/' . $tree;
        self::assertDirectoryExists($absolute, $tree . ' is a branding-owned tree and must exist.');

        $found = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absolute, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $entry) {
            if (!$entry instanceof \SplFileInfo || !$entry->isFile()) {
                continue;
            }

            $found[] = $tree . '/' . str_replace(
                '\\',
                '/',
                substr($entry->getPathname(), strlen($absolute) + 1),
            );
        }

        self::assertNotSame([], $found, $tree . ' contains no files, so this sweep proves nothing.');
        self::assertSame([], $this->uncovered($found));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function brandingOnlyTreeProvider(): array
    {
        return [
            'core and portal logo slots' => ['public/images/logos'],
            'dark-variant module marks' => [
                'interface/modules/custom_modules/oe-module-thiqa-branding/public/logos',
            ],
        ];
    }

    /**
     * Ownership boundary with S3-P2-36. `public/themes/*.css` and the generated SCSS under
     * `interface/themes/thiqa/` are build output whose bytes legitimately change on every
     * rebuild. A recorded hash for them would be stale by design and would train people to
     * re-issue hashes reflexively — which is exactly how RB-25 happened. Their authority is
     * the generator's own `--check` mode, not this manifest.
     *
     * @param non-empty-string $forbiddenPrefix
     */
    #[DataProvider('generatedOutputPrefixProvider')]
    public function testGeneratedBuildOutputIsNeverGivenARecordedHash(string $forbiddenPrefix): void
    {
        $covered = array_filter(
            $this->manifestPaths(),
            static fn (string $path): bool => str_starts_with($path, $forbiddenPrefix),
        );

        self::assertSame(
            [],
            array_values($covered),
            $forbiddenPrefix . ' is regenerated by the build, so a recorded hash there goes stale '
            . 'on every legitimate rebuild. It belongs to the token generator\'s --check mode.',
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function generatedOutputPrefixProvider(): array
    {
        return [
            'compiled themes' => ['public/themes/'],
            'generated theme partials' => ['interface/themes/'],
            'tenant materialised output' => ['public/branding/'],
        ];
    }

    // ------------------------------------------------------------------------------ helpers

    /**
     * @param list<string> $paths
     *
     * @return list<string>
     */
    private function uncovered(array $paths): array
    {
        $covered = array_flip($this->manifestPaths());

        return array_values(array_filter(
            $paths,
            static fn (string $path): bool => !isset($covered[$path]),
        ));
    }

    /**
     * @return list<string>
     */
    private function manifestPaths(): array
    {
        $paths = [];

        foreach (explode("\n", $this->read('brand/manifests/SHA256SUMS')) as $line) {
            $matches = [];
            if (preg_match('~^[0-9a-f]{64}\s+\*?(.+)$~i', trim($line), $matches) === 1) {
                $paths[] = str_replace('\\', '/', trim($matches[1]));
            }
        }

        return $paths;
    }

    /**
     * A miniature repository with one artefact of every covered class.
     */
    private function buildFixture(string $root): void
    {
        $mark = "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 8 8\"><rect width=\"8\" height=\"8\"/></svg>\n";
        $face = 'wOF2FIXTUREFONTBYTES';

        $files = [
            'brand/master/mark.svg' => $mark,
            'docs/branding-production/00-fixture.md' => "# fixture design document\n",
            'public/images/mark.svg' => $mark,
            'brand/typography/fonts/Face-Regular.woff2' => $face,
            'brand/typography/fonts/pdf/README-amiri.md' => "# provenance\n",
            'public/assets/fonts/thiqa/Face-Regular.woff2' => $face,
        ];

        foreach ($files as $relative => $contents) {
            $path = $root . '/' . $relative;
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0o777, true);
            }
            file_put_contents($path, $contents);
        }

        $recorded = [
            'brand/master/mark.svg',
            'docs/branding-production/00-fixture.md',
            'brand/typography/fonts/Face-Regular.woff2',
            'brand/typography/fonts/pdf/README-amiri.md',
            'public/images/mark.svg',
        ];

        $lines = ['# fixture manifest', ''];
        foreach ($recorded as $relative) {
            $lines[] = hash_file('sha256', $root . '/' . $relative) . '  ' . $relative;
        }

        mkdir($root . '/brand/manifests', 0o777, true);
        file_put_contents($root . '/brand/manifests/SHA256SUMS', implode("\n", $lines) . "\n");
    }

    private static function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $entry) {
            if (!$entry instanceof \SplFileInfo) {
                continue;
            }

            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }

        rmdir($path);
    }

    /**
     * The verifier is a standalone deploy-time script, not a composer-autoloaded class, so it
     * is required in library mode — the constant suppresses its CLI run, leaving the same code
     * the release gate executes available to drive directly.
     */
    private static function loadVerifier(): void
    {
        if (!defined('OPENEMR_BRAND_MANIFEST_VERIFIER_LIBRARY')) {
            define('OPENEMR_BRAND_MANIFEST_VERIFIER_LIBRARY', true);
        }

        require_once self::PROJECT_ROOT . '/tools/branding/verify-brand-manifest.php';
    }

    private function root(): string
    {
        return self::PROJECT_ROOT;
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(self::PROJECT_ROOT . '/' . $relativePath);
        self::assertIsString($contents, sprintf('Unable to read %s', $relativePath));

        return $contents;
    }
}
