<?php

/**
 * Wiring guard for the four SkyEagleBranding PHPStan guardrails
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\SkyEagleBranding;

use OpenEMR\PHPStan\Rules\ForbiddenBrandingHttpClientRule;
use OpenEMR\PHPStan\Rules\ForbiddenBrandingPlaceholderDomainRule;
use OpenEMR\PHPStan\Rules\ForbiddenBrandingSiteConfigRule;
use OpenEMR\PHPStan\Rules\ForbiddenBrandingTwigPathRule;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The rule-behaviour suites instantiate each rule directly, so they keep
 * passing even if a rule is never wired into PHPStan's configuration — at
 * which point the guardrail silently protects nothing in CI.
 *
 * This test closes that gap from the other side: it asserts each rule is
 * registered in `.phpstan/extension.neon` with the `phpstan.rules.rule` tag,
 * and that the deliberately-violating fixture directory stays excluded from
 * analysis so full-codebase runs do not report the fixtures' intentional
 * violations as real ones.
 */
final class SkyEagleBrandingRuleRegistrationTest extends TestCase
{
    private const REPOSITORY_ROOT = __DIR__ . '/../../../../..';

    private const EXTENSION_NEON = self::REPOSITORY_ROOT . '/.phpstan/extension.neon';

    private const GITHUB_NEON = self::REPOSITORY_ROOT . '/.phpstan/phpstan.github.neon';

    private const FIXTURE_EXCLUDE_PATH = '../tests/Tests/Isolated/PHPStan/SkyEagleBranding/data';

    /**
     * Deliberately typed as a bare `class-string` rather than
     * `class-string<Rule<...>>`: each rule binds a different node type, and
     * testRuleIsInstantiableAsAPhpstanRule() is the assertion that proves
     * these classes really are PHPStan rules.
     *
     * @return array<string, array{class-string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function brandingRuleProvider(): array
    {
        return [
            'http client' => [ForbiddenBrandingHttpClientRule::class],
            'site config' => [ForbiddenBrandingSiteConfigRule::class],
            'twig path' => [ForbiddenBrandingTwigPathRule::class],
            'placeholder domain' => [ForbiddenBrandingPlaceholderDomainRule::class],
        ];
    }

    /**
     * @param class-string $ruleClass
     */
    #[DataProvider('brandingRuleProvider')]
    public function testRuleIsRegisteredWithThePhpstanRulesTag(string $ruleClass): void
    {
        $services = $this->parseRegisteredServices();

        self::assertArrayHasKey(
            $ruleClass,
            $services,
            sprintf('%s is not registered as a service in .phpstan/extension.neon.', $ruleClass),
        );

        self::assertContains(
            'phpstan.rules.rule',
            $services[$ruleClass],
            sprintf('%s is registered but not tagged phpstan.rules.rule, so it never runs.', $ruleClass),
        );
    }

    /**
     * A registered service that is not actually a Rule would be a
     * configuration error PHPStan only reports at run time.
     *
     * @param class-string $ruleClass
     */
    #[DataProvider('brandingRuleProvider')]
    public function testRuleIsInstantiableAsAPhpstanRule(string $ruleClass): void
    {
        $rule = new $ruleClass();

        self::assertInstanceOf(Rule::class, $rule);
    }

    public function testFixtureDirectoryIsExcludedFromAnalysis(): void
    {
        // The fixtures declare the SkyEagleBranding namespace and violate every
        // guardrail on purpose. Without this exclusion a full-codebase PHPStan
        // run reports them as real violations.
        $neon = $this->read(self::GITHUB_NEON);

        self::assertStringContainsString(
            '- ' . self::FIXTURE_EXCLUDE_PATH,
            $neon,
            'The SkyEagleBranding fixture directory must stay in excludePaths.',
        );
    }

    /**
     * Minimal scan of the `services:` section: each `- class: <name>` entry
     * owns every `- <tag>` line that follows it until the next entry. Parsing
     * the file as text keeps this test free of a NEON parser dependency.
     *
     * @return array<string, list<string>> class name => tags
     */
    private function parseRegisteredServices(): array
    {
        $services = [];
        $currentClass = null;

        foreach (explode("\n", $this->read(self::EXTENSION_NEON)) as $line) {
            $matches = [];

            if (preg_match('~^\s*-\s*class:\s*(\S+)\s*$~', $line, $matches) === 1) {
                $currentClass = ltrim($matches[1], '\\');
                $services[$currentClass] ??= [];

                continue;
            }

            if ($currentClass === null) {
                continue;
            }

            if (preg_match('~^\s*-\s*(phpstan\.[a-z.]+)\s*$~i', $line, $matches) === 1) {
                $services[$currentClass][] = $matches[1];
            }
        }

        return $services;
    }

    private function read(string $path): string
    {
        self::assertFileExists($path);

        $contents = file_get_contents($path);
        self::assertIsString($contents);

        return $contents;
    }
}
