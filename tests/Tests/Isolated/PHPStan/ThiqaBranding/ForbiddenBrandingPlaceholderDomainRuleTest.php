<?php

/**
 * Tests for ForbiddenBrandingPlaceholderDomainRule
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\ThiqaBranding;

use OpenEMR\PHPStan\Rules\ForbiddenBrandingPlaceholderDomainRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenBrandingPlaceholderDomainRule>
 */
final class ForbiddenBrandingPlaceholderDomainRuleTest extends RuleTestCase
{
    private const TIP = 'Read the endpoint from tenant configuration instead of hard-coding a '
        . '.example placeholder or an upstream open-emr.org host.';

    private const IDENTIFIER = 'thiqaBranding.noPlaceholderEndpoint';

    protected function getRule(): Rule
    {
        return new ForbiddenBrandingPlaceholderDomainRule();
    }

    public function testFlagsPlaceholderAndUpstreamEndpoints(): void
    {
        $this->analyse(
            [__DIR__ . '/data/placeholder_domain_violations.php'],
            [
                [$this->expectedMessage('thiqa.example'), 7, self::TIP],
                [$this->expectedMessage('thiqa.example'), 8, self::TIP],
                [$this->expectedMessage('reg.open-emr.org'), 9, self::TIP],
                [$this->expectedMessage('tenant.acme.example'), 10, self::TIP],
            ],
        );
    }

    public function testAllowsRealHosts(): void
    {
        $this->analyse(
            [__DIR__ . '/data/placeholder_domain_allowed.php'],
            [],
        );
    }

    public function testAllowsPlaceholdersInModuleTestCode(): void
    {
        $this->analyse(
            [__DIR__ . '/data/placeholder_domain_module_tests.php'],
            [],
        );
    }

    public function testDoesNotFireOutsideTheBrandingModule(): void
    {
        $this->analyse(
            [__DIR__ . '/data/placeholder_domain_outside_module.php'],
            [],
        );
    }

    public function testStaysSilentOnViolatingCodeInForeignNamespaces(): void
    {
        // Every shape all four guardrails forbid, written once in a core
        // namespace and once in a namespace that merely shares the module
        // namespace text as a prefix. A scoping mistake here would fire
        // across the whole codebase and break every build.
        $this->analyse(
            [
                __DIR__ . '/data/all_violations_outside_module.php',
                __DIR__ . '/data/all_violations_sibling_namespace.php',
            ],
            [],
        );
    }

    public function testStaysSilentInTheGlobalNamespace(): void
    {
        // Most legacy OpenEMR code has no namespace at all, and core OpenEMR
        // references reg.open-emr.org legitimately.
        $this->analyse(
            [__DIR__ . '/data/all_violations_global_namespace.php'],
            [],
        );
    }

    public function testFiresOnTheSameBodyThatIsSilentOutsideTheModule(): void
    {
        // data/all_violations_branding_namespace.php is byte-identical to
        // data/all_violations_outside_module.php from line 10 down; only the
        // namespace differs. The expectations below, next to the empty
        // expectation in testStaysSilentOnViolatingCodeInForeignNamespaces(),
        // pin the namespace as the sole discriminator.
        //
        // Only placeholder-domain errors are expected: the same file also
        // violates the HTTP-client, site-config and Twig-path guardrails, and
        // RuleTestCase fails on any unexpected error, so this simultaneously
        // proves this rule does not fire on the other three rules' subjects.
        $this->analyse(
            [__DIR__ . '/data/all_violations_branding_namespace.php'],
            [
                [$this->expectedMessage('reg.open-emr.org'), 17, self::TIP],
                [$this->expectedMessage('thiqa.example'), 26, self::TIP],
                [$this->expectedMessage('reg.open-emr.org'), 27, self::TIP],
            ],
        );
    }

    public function testFlagsViolationsInTheModuleRootNamespace(): void
    {
        $this->analyse(
            [__DIR__ . '/data/all_violations_module_root_namespace.php'],
            [
                [$this->expectedMessage('reg.open-emr.org'), 16, self::TIP],
                [$this->expectedMessage('thiqa.example'), 25, self::TIP],
                [$this->expectedMessage('reg.open-emr.org'), 26, self::TIP],
            ],
        );
    }

    public function testDoesNotExemptANamespaceThatMerelyStartsWithTests(): void
    {
        $this->analyse(
            [__DIR__ . '/data/placeholder_domain_tests_lookalike_violations.php'],
            [
                [$this->expectedMessage('thiqa.example'), 11, self::TIP],
                [$this->expectedMessage('thiqa.example'), 12, self::TIP],
                [$this->expectedMessage('reg.open-emr.org'), 13, self::TIP],
            ],
        );
    }

    public function testAllowsStringsThatMerelyContainTheWordExample(): void
    {
        $this->analyse(
            [__DIR__ . '/data/placeholder_domain_near_miss_allowed.php'],
            [],
        );
    }

    public function testEveryErrorCarriesTheDocumentedIdentifier(): void
    {
        // analyse() compares message, line and tip but not the identifier,
        // which is what `phpstan.rules.rule` consumers and any future
        // ignoreErrors entry key off. Assert it directly.
        $errors = $this->gatherAnalyserErrors([
            __DIR__ . '/data/all_violations_branding_namespace.php',
        ]);

        self::assertNotSame([], $errors);

        foreach ($errors as $error) {
            self::assertSame(self::IDENTIFIER, $error->getIdentifier(), $error->getMessage());
        }
    }

    private function expectedMessage(string $host): string
    {
        return sprintf(
            'Placeholder or upstream-brand endpoint "%s" must not appear in shipped ThiqaBranding '
            . 'configuration (RebrandingPlan WP-2.7g).',
            $host,
        );
    }
}
