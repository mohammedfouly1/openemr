<?php

/**
 * Regression contract for deterministic branding CI wiring.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class BrandingCiContractTest extends TestCase
{
    private const EXPECTED_RULE_IDENTIFIERS = [
        'skyeagleBranding.noRuntimeHttpClient',
        'skyeagleBranding.noSiteConfigSeam',
        'skyeagleBranding.twigNamespaceDiscipline',
        'skyeagleBranding.noPlaceholderEndpoint',
    ];

    /**
     * Steps the canonical gate must run, in any order.
     *
     * Order is deliberately not asserted: each step is independently fatal, so what matters
     * is that none was dropped. `@branding-identity-check` is the ADR-BRAND-005 pre-database
     * identity drift gate -- without it a hand-edited `library/product_identity.generated.php`
     * would survive CI, which is the whole guarantee that architecture rests on.
     *
     * @var list<string>
     */
    private const REQUIRED_GATE_STEPS = [
        '@branding-tokens-check',
        '@branding-identity-check',
        '@php tools/branding/verify-brand-manifest.php',
    ];

    public function testCanonicalComposerGateContainsEveryRequiredFailureBoundary(): void
    {
        $composer = json_decode($this->read('composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);
        $scripts = $composer['scripts'] ?? null;
        self::assertIsArray($scripts);
        $gateRaw = $scripts['branding-ci'] ?? null;

        self::assertIsArray($gateRaw);
        $gate = [];
        foreach ($gateRaw as $step) {
            self::assertIsString($step, 'Every branding-ci gate step must be a string.');
            $gate[] = $step;
        }

        // Located by CONTENT, never by index. Indexing this array positionally made the
        // contract brittle in the one way it must not be: adding a legitimate new step --
        // `@branding-identity-check`, the ADR-BRAND-005 drift gate -- shifted every later
        // element and turned this test red while the gate itself was perfectly correct. A
        // guard that fails when the thing it guards is strengthened trains people to edit
        // the guard, which is how a real regression eventually gets waved through.
        foreach (self::REQUIRED_GATE_STEPS as $step) {
            self::assertContains($step, $gate, 'The canonical gate lost the step ' . $step . '.');
        }

        $tests = null;
        foreach ($gate as $step) {
            if (str_contains($step, 'vendor/bin/phpunit')) {
                $tests = $step;
                break;
            }
        }

        self::assertIsString($tests, 'The canonical gate no longer runs phpunit at all.');
        self::assertStringContainsString('--fail-on-empty-test-suite', $tests);
        self::assertStringContainsString('--fail-on-incomplete', $tests);
        self::assertStringContainsString('--fail-on-risky', $tests);
        self::assertStringContainsString('tests/Tests/Isolated/BrandingCi', $tests);
        self::assertStringContainsString('tests/Tests/Isolated/PHPStan/SkyEagleBranding', $tests);
        self::assertStringContainsString('tests/Tests/Isolated/BrandingCoreStrings', $tests);
        // S1-P1-15's backup-retention suite and S2-P1-18's verify command were named as
        // individual FILES here. Both now arrive via their whole directory, which is strictly
        // more coverage — so the assertion moves up to the directory rather than pinning a
        // filename that no longer appears verbatim in the gate string.
        self::assertStringContainsString(
            'tests/Tests/Isolated/Modules/SkyEagleBranding/Console',
            $tests,
        );

        // S2-P1-18: the health check's own behaviour, and the command that reports it, are
        // gated rather than left to a human remembering to run `verify` by hand.
        self::assertStringContainsString(
            'tests/Tests/Isolated/Modules/SkyEagleBranding/Observability',
            $tests,
        );

        self::assertStringNotContainsString('|| true', implode("\n", $gate));

        // Composer kills a child process after 300 seconds by default. This gate runs a 253-test
        // suite including PHPStan RuleTestCase analysis, which CI always pays with a cold cache;
        // measured here at 457-607 s once `src/` edits invalidated that cache. A passing suite
        // killed by the default reads exactly like a failing gate, and a gate that can go red
        // with nothing wrong is a gate people learn to ignore. This is a ceiling, not a removal:
        // a genuinely hung process still dies.
        $config = $composer['config'] ?? null;
        self::assertIsArray($config);
        self::assertSame(1800, $config['process-timeout'] ?? null);
    }

    public function testWorkflowRunsCanonicalGateWithoutSecretsOrFailureMasking(): void
    {
        $workflow = $this->read('.github/workflows/isolated-tests.yml');

        self::assertStringContainsString("permissions:\n  contents: read", $workflow);
        self::assertStringContainsString('Run deterministic branding gates', $workflow);
        self::assertStringContainsString("if: matrix.php-version == '8.2'", $workflow);
        self::assertStringContainsString('run: composer branding-ci', $workflow);
        self::assertStringNotContainsString('continue-on-error:', $workflow);
        self::assertStringNotContainsString('secrets.', $this->brandingStep($workflow));

        // Scan-3B P2-1: `paths:` is not a substring of `paths-ignore:`, so the original
        // assertion let a path filter through under the other spelling — and a path filter on
        // `on:` skips the whole workflow, gate included.
        self::assertStringNotContainsString('paths:', $workflow);
        self::assertStringNotContainsString('paths-ignore:', $workflow);
    }

    /**
     * Scan-3B P1-1: the gate is pinned to one matrix leg, and nothing checked that the leg exists.
     *
     * The step's `if: matrix.php-version == '8.2'` is a string the previous assertion happily found
     * whether or not `'8.2'` was still in `strategy.matrix.php-version`. Delete that one line — an
     * ordinary version-cleanup edit — and the condition is false on every leg: the branding gate
     * never runs, all five legs go green, and this contract test used to pass. The pin and the
     * matrix have to be asserted against each other, not separately.
     *
     * **S4B-02: the pin must be read out of the branding step, not out of the file.** Commit
     * `e203d5bdd` added the Node/npm theme-build steps, each carrying its own identical
     * `if: matrix.php-version ==` line — five in the file now. A bare `preg_match` takes the first
     * match, so this guard was reading whichever pinned step happened to appear first. It is the
     * branding step today only because that step happens to sit above the others; insert a step
     * ahead of it and the guard silently begins validating something else while still passing.
     *
     * That is the same positional fragility that turned this suite red at Rev 32 when a new gate
     * step shifted the composer script array. Locating by content, via {@see self::brandingStep()},
     * is the fix in both places.
     */
    public function testTheMatrixLegTheGateIsPinnedToActuallyExists(): void
    {
        $workflow = $this->read('.github/workflows/isolated-tests.yml');

        $matches = [];
        self::assertSame(
            1,
            preg_match("~if: matrix\.php-version == '([^']+)'~", $this->brandingStep($workflow), $matches),
            'The branding gate must stay pinned to one explicit matrix leg.',
        );
        $pinned = $matches[1];

        $block = [];
        self::assertSame(
            1,
            preg_match('~php-version:\R((?:\s*-\s*\'[^\']+\'\R)+)~', $workflow, $block),
            'Could not locate the php-version matrix.',
        );

        $legs = [];
        preg_match_all("~-\s*'([^']+)'~", $block[1], $legs);

        self::assertContains(
            $pinned,
            $legs[1],
            sprintf(
                'The gate is pinned to PHP %s, which is not in the matrix (%s), so the step never '
                . 'runs and every leg reports green.',
                $pinned,
                implode(', ', $legs[1]),
            ),
        );
    }

    /**
     * Scan-3B P1-2: `|| true` was forbidden in the composer script but not in the workflow step.
     *
     * `run: composer branding-ci || true` passed every previous assertion — `continue-on-error:`
     * was blocked, but shell-level masking was not looked for at all.
     */
    public function testTheWorkflowStepDoesNotMaskTheGateExitCode(): void
    {
        $step = $this->brandingStep($this->read('.github/workflows/isolated-tests.yml'));

        foreach (['|| true', '|| :', '; exit 0', '|| echo', 'set +e'] as $mask) {
            self::assertStringNotContainsString(
                $mask,
                $step,
                'The branding gate step must not mask its exit code with "' . $mask . '".',
            );
        }
    }

    /**
     * The floor below which the gate has collapsed rather than merely been tidied.
     *
     * 258 test methods are declared across the gated paths at the time of writing, producing 513
     * executed tests once data providers multiply them out. This number is deliberately well
     * below that: it is a collapse detector, not a ratchet that reddens every time someone
     * removes a redundant case.
     */
    private const MINIMUM_DECLARED_TEST_METHODS = 500;

    /**
     * The gate must cover the branding module's whole test tree, not a curated slice of it.
     *
     * The slice was a real blind spot, and the SkyEagle rename walked straight into it: the gate
     * ran 523 tests while the module tree alone declares 1,448, and `Config/`, `Asset/`,
     * `Listener/`, `Materialisation/`, `Service/`, `Theme/`, `Token/`, `Generator/` and
     * `Accessibility/` were in none of them. Stale brand assertions in three of those directories
     * survived a green gate and were found only by running the tree by hand.
     *
     * That is the S4B-01 and S4B-08 shape a third time — a gate reporting green over a surface it
     * does not execute — so the fix is coverage, not another assertion about coverage.
     *
     * `Twig/` is the one deliberate omission. Those suites render templates, which hangs
     * indefinitely on the Windows host (CLAUDE.local.md section 9), and the gate has to stay
     * runnable by a developer locally or it stops being run at all. CI still executes them: the
     * workflow's separate full-suite step runs the entire isolated configuration with coverage,
     * so nothing is unguarded — it is guarded by the other step.
     *
     * @var list<string>
     */
    private const MODULE_TREE_DIRECTORIES = [
        'Accessibility', 'Asset', 'AssetIntake', 'Config', 'Console', 'Generator', 'Guardrail',
        'Listener', 'Materialisation', 'Observability', 'Service', 'Tenant', 'Theme', 'Token',
    ];

    public function testTheGateCoversTheWholeModuleTestTree(): void
    {
        $gated = $this->gatedTestPaths();
        $root = 'tests/Tests/Isolated/Modules/SkyEagleBranding/';

        foreach (self::MODULE_TREE_DIRECTORIES as $directory) {
            self::assertContains(
                $root . $directory,
                $gated,
                sprintf(
                    'The canonical gate does not run %s%s. A directory outside the gate can go '
                    . 'stale, or empty, while the gate reports green - which is exactly how the '
                    . 'SkyEagle rename broke assertions in three module directories without '
                    . 'reddening anything.',
                    $root,
                    $directory,
                ),
            );
        }

        // Twig is excluded on purpose; see the constant's docblock. Asserting its absence keeps
        // someone from "completing" the list and making the gate unrunnable on the Windows host.
        self::assertNotContains(
            $root . 'Twig',
            $gated,
            'Twig render suites hang on the Windows host and must stay out of the gate; the '
            . 'workflow\'s full-suite step covers them in CI.',
        );
    }

    /**
     * Scan-3B P1-5: `--fail-on-empty-test-suite` fires only when the WHOLE run is empty.
     *
     * A gated directory that loses every test class — deleted, or its classes renamed off the
     * `*Test` convention — contributes zero tests while the run stays green, because other
     * directories still have some.
     *
     * **S4B-01: this test used to promise that floor and never compute it.** The docblock claimed
     * "a floor on the total"; the body asserted only that four or more paths were gated and that
     * each directory held at least one `*Test.php`. A fake repository of empty test files
     * satisfied it, so the gate could have fallen from 513 executed tests to seven and stayed
     * green — the exact false-green shape this suite exists to catch, inside the suite itself.
     *
     * The floor is now actually counted, by declared test method rather than by file: a file that
     * survives with every method deleted is precisely the silent collapse, and a file count
     * cannot see it.
     */
    public function testTheGateRunsASubstantialNumberOfTests(): void
    {
        $paths = $this->gatedTestPaths();

        self::assertGreaterThanOrEqual(4, count($paths), 'The gate should cover several suites.');

        $total = 0;
        foreach ($paths as $path) {
            $total += $this->declaredTestMethodsUnder($path);
        }

        self::assertGreaterThanOrEqual(
            self::MINIMUM_DECLARED_TEST_METHODS,
            $total,
            sprintf(
                'The canonical gate declares only %d test methods across %d gated paths, below the '
                . 'floor of %d. Either the gate has collapsed or the floor needs a deliberate, '
                . 'documented revision — do not simply lower it.',
                $total,
                count($paths),
                self::MINIMUM_DECLARED_TEST_METHODS,
            ),
        );
    }

    /**
     * The floor answers "is this gate running anything"; this answers "is every part of it still
     * contributing".
     *
     * Both are needed, and the reason is the S4D-02 lesson repeated: a total large enough to pass
     * hides a single path that has silently stopped contributing, because the other paths carry
     * it. The two smallest gated paths contribute 11 methods each — either could vanish entirely
     * without troubling a floor of 180.
     *
     * The walk is recursive. The previous non-recursive `glob('/*Test.php')` would have reported a
     * directory as populated or empty purely by whether its tests happened to sit at the top
     * level, which is a property of nobody's intent.
     */
    public function testEveryGatedPathStillContributesTests(): void
    {
        foreach ($this->gatedTestPaths() as $path) {
            $absolute = $this->root() . '/' . $path;
            self::assertFileExists($absolute, 'Gated path has disappeared: ' . $path);

            self::assertGreaterThan(
                0,
                $this->declaredTestMethodsUnder($path),
                'Gated path ' . $path . ' declares no test methods, so it contributes nothing to '
                . 'the gate while the run still reports green.',
            );
        }
    }

    /**
     * The `tests/…` arguments of the gate's phpunit step, located by content.
     *
     * @return list<string>
     */
    private function gatedTestPaths(): array
    {
        $composer = json_decode($this->read('composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);

        $scripts = $composer['scripts'] ?? null;
        self::assertIsArray($scripts);

        $gate = $scripts['branding-ci'] ?? null;
        self::assertIsArray($gate);

        $tests = '';
        foreach ($gate as $step) {
            if (is_string($step) && str_contains($step, 'vendor/bin/phpunit')) {
                $tests = $step;
                break;
            }
        }

        self::assertNotSame('', $tests, 'The canonical gate no longer runs phpunit at all.');

        $paths = [];
        foreach (explode(' ', $tests) as $token) {
            if (str_starts_with($token, 'tests/')) {
                $paths[] = $token;
            }
        }

        return $paths;
    }

    /**
     * Declared test methods under one gated path, counting both the `test*` naming convention and
     * the `#[Test]` attribute, since this suite uses both.
     */
    private function declaredTestMethodsUnder(string $relativePath): int
    {
        $absolute = $this->root() . '/' . $relativePath;

        $files = [];
        if (is_dir($absolute)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolute, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $entry) {
                if ($entry instanceof \SplFileInfo && $entry->isFile() && str_ends_with($entry->getFilename(), 'Test.php')) {
                    $files[] = $entry->getPathname();
                }
            }
        } elseif (is_file($absolute)) {
            $files[] = $absolute;
        }

        $methods = 0;
        foreach ($files as $file) {
            $source = file_get_contents($file);
            self::assertIsString($source);

            $methods += preg_match_all('~\n\s*public (?:static )?function test[A-Z_]~', $source);
            $methods += preg_match_all('~#\[Test\]~', $source);
        }

        return $methods;
    }

    /**
     * Scan-3B P1-4: manifest verification is strictly manifest → disk.
     *
     * A path with no manifest line is never examined, so deleting a line un-guards that file and
     * the verifier reports one *more* apparently-clean entry than before. The manifest's own header
     * says to re-issue an entry rather than delete it; nothing enforced that. This sweeps the other
     * direction: every file under `brand/` must be covered, except the manifest files themselves
     * and `.gitattributes`, which are metadata rather than assets.
     */
    public function testEveryBrandAssetIsCoveredByTheManifest(): void
    {
        $manifest = $this->read('brand/manifests/SHA256SUMS');

        $covered = [];
        foreach (explode("\n", $manifest) as $line) {
            $matches = [];
            if (preg_match('~^[0-9a-f]{64}\s+\*?(.+)$~i', trim($line), $matches) === 1) {
                $covered[str_replace('\\', '/', trim($matches[1]))] = true;
            }
        }

        self::assertGreaterThanOrEqual(100, count($covered), 'Implausibly few manifest entries.');

        $unmanifested = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root() . '/brand', \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $entry) {
            if (!$entry instanceof \SplFileInfo || !$entry->isFile()) {
                continue;
            }

            $relative = 'brand/' . str_replace(
                '\\',
                '/',
                substr($entry->getPathname(), strlen($this->root() . '/brand/')),
            );

            // Metadata, not assets: the manifests cannot hash themselves, and .gitattributes
            // governs checkout behaviour rather than shipping anything.
            if (str_starts_with($relative, 'brand/manifests/') || str_ends_with($relative, '.gitattributes')) {
                continue;
            }

            if (!isset($covered[$relative])) {
                $unmanifested[] = $relative;
            }
        }

        self::assertSame(
            [],
            $unmanifested,
            'These files ship under brand/ but no manifest entry guards them, so they can be '
            . 'replaced without the release gate noticing.',
        );
    }

    public function testEveryExpectedGuardrailIdentifierRemainsAssertedByTests(): void
    {
        $testDirectory = $this->root() . '/tests/Tests/Isolated/PHPStan/SkyEagleBranding';
        $contents = '';
        foreach (glob($testDirectory . '/*RuleTest.php') ?: [] as $path) {
            $file = file_get_contents($path);
            self::assertIsString($file);
            $contents .= $file;
        }

        foreach (self::EXPECTED_RULE_IDENTIFIERS as $identifier) {
            self::assertStringContainsString($identifier, $contents);
        }
    }

    private function brandingStep(string $workflow): string
    {
        $start = strpos($workflow, '- name: Run deterministic branding gates');
        self::assertIsInt($start);
        $run = strpos($workflow, 'run: composer branding-ci', $start);
        self::assertIsInt($run);
        $end = strpos($workflow, "\n", $run);

        return substr($workflow, $start, $end === false ? null : $end - $start);
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents($this->root() . '/' . $relativePath);
        self::assertIsString($contents);
        return str_replace("\r\n", "\n", $contents);
    }

    private function root(): string
    {
        return dirname(__DIR__, 4);
    }
}
