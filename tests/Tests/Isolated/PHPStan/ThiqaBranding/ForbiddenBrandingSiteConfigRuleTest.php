<?php

/**
 * Tests for ForbiddenBrandingSiteConfigRule
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\ThiqaBranding;

use OpenEMR\PHPStan\Rules\ForbiddenBrandingSiteConfigRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenBrandingSiteConfigRule>
 */
final class ForbiddenBrandingSiteConfigRuleTest extends RuleTestCase
{
    private const TIP = 'Brand values are materialised into the globals table and read through '
        . 'BrandingConfig; no free-text tenant CSS/JS/PHP seam is permitted.';

    private const IDENTIFIER = 'thiqaBranding.noSiteConfigSeam';

    protected function getRule(): Rule
    {
        return new ForbiddenBrandingSiteConfigRule();
    }

    public function testFlagsSiteConfigReferencesInBrandingCode(): void
    {
        $this->analyse(
            [__DIR__ . '/data/site_config_violations.php'],
            [
                [$this->expectedMessage('sites/default/config.php'), 9, self::TIP],
                [$this->expectedMessage('sites/*/config.php'), 10, self::TIP],
                [$this->expectedMessage('/config.php'), 11, self::TIP],
                [$this->expectedMessage('config.php'), 12, self::TIP],
            ],
        );
    }

    public function testAllowsOtherPerSitePaths(): void
    {
        $this->analyse(
            [__DIR__ . '/data/site_config_allowed.php'],
            [],
        );
    }

    public function testDoesNotFireOutsideTheBrandingModule(): void
    {
        $this->analyse(
            [__DIR__ . '/data/site_config_outside_module.php'],
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
        // Most legacy OpenEMR code has no namespace at all, and a great deal
        // of it names sites/<site>/config.php legitimately.
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
        // Only site-config errors are expected: the same file also violates
        // the HTTP-client, Twig-path and placeholder-domain guardrails, and
        // RuleTestCase fails on any unexpected error, so this simultaneously
        // proves this rule does not fire on the other three rules' subjects.
        $this->analyse(
            [__DIR__ . '/data/all_violations_branding_namespace.php'],
            [
                [$this->expectedMessage('sites/default/config.php'), 19, self::TIP],
                [$this->expectedMessage('sites/*/config.php'), 20, self::TIP],
            ],
        );
    }

    public function testFlagsViolationsInTheModuleRootNamespace(): void
    {
        $this->analyse(
            [__DIR__ . '/data/all_violations_module_root_namespace.php'],
            [
                [$this->expectedMessage('sites/default/config.php'), 18, self::TIP],
                [$this->expectedMessage('sites/*/config.php'), 19, self::TIP],
            ],
        );
    }

    public function testFlagsEverySpellingOfTheSeam(): void
    {
        $this->analyse(
            [__DIR__ . '/data/site_config_path_form_violations.php'],
            [
                [$this->expectedMessage('sites\default\config.php'), 10, self::TIP],
                [$this->expectedMessage('sites/default/config.php?site=default'), 11, self::TIP],
                [$this->expectedMessage('sites/default/config.php#branding'), 12, self::TIP],
                [$this->expectedMessage('sites/default/Config.PHP'), 13, self::TIP],
            ],
        );
    }

    public function testAllowsNearMissesThatMerelyContainTheSubstring(): void
    {
        $this->analyse(
            [__DIR__ . '/data/site_config_near_miss_allowed.php'],
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

    private function expectedMessage(string $literal): string
    {
        return sprintf(
            'Reference to the per-site config.php seam ("%s") is forbidden in ThiqaBranding code: '
            . 'sites/<site>/config.php is a PROHIBITED branding mechanism '
            . '(constraint C1 / BRAND-120).',
            $literal,
        );
    }
}
