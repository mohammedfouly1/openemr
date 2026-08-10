<?php

/**
 * Tests for ForbiddenBrandingTwigPathRule
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\ThiqaBranding;

use OpenEMR\PHPStan\Rules\ForbiddenBrandingTwigPathRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenBrandingTwigPathRule>
 */
final class ForbiddenBrandingTwigPathRuleTest extends RuleTestCase
{
    private const PREPEND_PATH_TIP = 'Register the module template directory with '
        . 'addPath($dir, \'oe-module-thiqa-branding\') and rewrite the template name to '
        . '@oe-module-thiqa-branding/… from a TemplatePageEvent listener.';

    private const ADD_PATH_TIP = 'Pass the module slug as the second argument: '
        . 'addPath($dir, \'oe-module-thiqa-branding\').';

    private const IDENTIFIER = 'thiqaBranding.twigNamespaceDiscipline';

    protected function getRule(): Rule
    {
        return new ForbiddenBrandingTwigPathRule();
    }

    public function testFlagsPrependPathAndUnnamespacedAddPath(): void
    {
        $this->analyse(
            [__DIR__ . '/data/twig_path_violations.php'],
            [
                [$this->prependPathMessage(), 12, self::PREPEND_PATH_TIP],
                [$this->prependPathMessage(), 13, self::PREPEND_PATH_TIP],
                [$this->addPathMessage(), 14, self::ADD_PATH_TIP],
            ],
        );
    }

    public function testAllowsNamespacedAddPath(): void
    {
        $this->analyse(
            [__DIR__ . '/data/twig_path_allowed.php'],
            [],
        );
    }

    public function testDoesNotFireOutsideTheBrandingModule(): void
    {
        $this->analyse(
            [__DIR__ . '/data/twig_path_outside_module.php'],
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
        // Most legacy OpenEMR code has no namespace at all.
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
        // Only Twig-path errors are expected: the same file also violates the
        // HTTP-client, site-config and placeholder-domain guardrails, and
        // RuleTestCase fails on any unexpected error, so this simultaneously
        // proves this rule does not fire on the other three rules' subjects.
        $this->analyse(
            [__DIR__ . '/data/all_violations_branding_namespace.php'],
            [
                [$this->prependPathMessage(), 23, self::PREPEND_PATH_TIP],
                [$this->addPathMessage(), 24, self::ADD_PATH_TIP],
            ],
        );
    }

    public function testFlagsViolationsInTheModuleRootNamespace(): void
    {
        $this->analyse(
            [__DIR__ . '/data/all_violations_module_root_namespace.php'],
            [
                [$this->prependPathMessage(), 22, self::PREPEND_PATH_TIP],
                [$this->addPathMessage(), 23, self::ADD_PATH_TIP],
            ],
        );
    }

    public function testMatchesMethodNamesCaseInsensitively(): void
    {
        $this->analyse(
            [__DIR__ . '/data/twig_path_case_violations.php'],
            [
                [$this->prependPathMessage(), 14, self::PREPEND_PATH_TIP],
                [$this->addPathMessage(), 15, self::ADD_PATH_TIP],
            ],
        );
    }

    public function testAllowsDynamicMethodNamesAndVariableNamespaces(): void
    {
        $this->analyse(
            [__DIR__ . '/data/twig_path_dynamic_allowed.php'],
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

    private function prependPathMessage(): string
    {
        return 'FilesystemLoader::prependPath() is forbidden in ThiqaBranding code: it registers an '
            . 'unnamespaced, resolution-order dependent template path. '
            . 'Prohibited by locked Q38 / resolution CR-17.';
    }

    private function addPathMessage(): string
    {
        return 'FilesystemLoader::addPath() called without an explicit Twig namespace is forbidden in '
            . 'ThiqaBranding code: an unnamespaced path shadows core templates in a '
            . 'resolution-order dependent way. Prohibited by locked Q38 / resolution CR-17.';
    }
}
