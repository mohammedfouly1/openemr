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
        'thiqaBranding.noRuntimeHttpClient',
        'thiqaBranding.noSiteConfigSeam',
        'thiqaBranding.twigNamespaceDiscipline',
        'thiqaBranding.noPlaceholderEndpoint',
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
        $gate = $composer['scripts']['branding-ci'] ?? null;

        self::assertIsArray($gate);

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
            if (is_string($step) && str_contains($step, 'vendor/bin/phpunit')) {
                $tests = $step;
                break;
            }
        }

        self::assertIsString($tests, 'The canonical gate no longer runs phpunit at all.');
        self::assertStringContainsString('--fail-on-empty-test-suite', $tests);
        self::assertStringContainsString('--fail-on-incomplete', $tests);
        self::assertStringContainsString('--fail-on-risky', $tests);
        self::assertStringContainsString('tests/Tests/Isolated/BrandingCi', $tests);
        self::assertStringContainsString('tests/Tests/Isolated/PHPStan/ThiqaBranding', $tests);
        self::assertStringContainsString('tests/Tests/Isolated/BrandingCoreStrings', $tests);
        self::assertStringContainsString(
            'tests/Tests/Isolated/Modules/ThiqaBranding/Console/BackupRetentionTest.php',
            $tests,
        );

        // S2-P1-18: the health check's own behaviour, and the command that reports it, are
        // gated rather than left to a human remembering to run `verify` by hand.
        self::assertStringContainsString(
            'tests/Tests/Isolated/Modules/ThiqaBranding/Observability',
            $tests,
        );
        self::assertStringContainsString(
            'tests/Tests/Isolated/Modules/ThiqaBranding/Console/VerifyCommandTest.php',
            $tests,
        );

        self::assertStringNotContainsString('|| true', implode("\n", $gate));

        // Composer kills a child process after 300 seconds by default. This gate runs a 253-test
        // suite including PHPStan RuleTestCase analysis, which CI always pays with a cold cache;
        // measured here at 457-607 s once `src/` edits invalidated that cache. A passing suite
        // killed by the default reads exactly like a failing gate, and a gate that can go red
        // with nothing wrong is a gate people learn to ignore. This is a ceiling, not a removal:
        // a genuinely hung process still dies.
        self::assertSame(1800, $composer['config']['process-timeout'] ?? null);
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
     */
    public function testTheMatrixLegTheGateIsPinnedToActuallyExists(): void
    {
        $workflow = $this->read('.github/workflows/isolated-tests.yml');

        $matches = [];
        self::assertSame(
            1,
            preg_match("~if: matrix\.php-version == '([^']+)'~", $workflow, $matches),
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
     * Scan-3B P1-5: `--fail-on-empty-test-suite` fires only when the WHOLE run is empty.
     *
     * A gated directory that loses every test class — deleted, or its classes renamed off the
     * `*Test` convention — contributes zero tests while the run stays green, because other
     * directories still have some. `BrandingCiContractTest` only checked that the path *strings*
     * appeared in composer.json. A floor on the total makes that collapse visible.
     *
     * The number is deliberately well below the current count (257 at the time of writing): this
     * is a collapse detector, not a ratchet that fails every time someone removes a redundant test.
     */
    public function testTheGateRunsASubstantialNumberOfTests(): void
    {
        $composer = json_decode($this->read('composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);

        $gate = $composer['scripts']['branding-ci'] ?? null;
        self::assertIsArray($gate);

        // Each gated path must still resolve to something that exists; a deleted path already
        // fails loudly (PHPUnit exits 70), but a path that silently became empty does not.
        $tests = '';
        foreach ($gate as $step) {
            if (is_string($step) && str_contains($step, 'vendor/bin/phpunit')) {
                $tests = $step;
                break;
            }
        }
        $paths = [];
        foreach (explode(' ', $tests) as $token) {
            if (str_starts_with($token, 'tests/')) {
                $paths[] = $token;
            }
        }

        self::assertGreaterThanOrEqual(4, count($paths), 'The gate should cover several suites.');

        foreach ($paths as $path) {
            $absolute = $this->root() . '/' . $path;
            self::assertFileExists($absolute, 'Gated path has disappeared: ' . $path);

            if (is_dir($absolute)) {
                $found = glob($absolute . '/*Test.php') ?: [];
                self::assertNotSame(
                    [],
                    $found,
                    'Gated directory ' . $path . ' contains no *Test.php, so it contributes nothing '
                    . 'to the gate while the run still reports green.',
                );
            }
        }
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
        $testDirectory = $this->root() . '/tests/Tests/Isolated/PHPStan/ThiqaBranding';
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
