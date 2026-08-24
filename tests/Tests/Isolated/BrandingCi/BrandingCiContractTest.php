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

    public function testCanonicalComposerGateContainsEveryRequiredFailureBoundary(): void
    {
        $composer = json_decode($this->read('composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $gate = $composer['scripts']['branding-ci'] ?? null;

        self::assertIsArray($gate);
        self::assertSame('@branding-tokens-check', $gate[0] ?? null);
        self::assertSame('@php tools/branding/verify-brand-manifest.php', $gate[1] ?? null);

        $tests = $gate[2] ?? null;
        self::assertIsString($tests);
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
        self::assertStringNotContainsString('paths:', $workflow);
        self::assertStringNotContainsString('secrets.', $this->brandingStep($workflow));
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
