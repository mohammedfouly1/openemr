<?php

/**
 * Enforce Twig namespace discipline in Thiqa branding code
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Guardrail for RebrandingPlan WP-2.7(f), locked Q38, resolution CR-17.
 *
 * A SaaS module may register Twig template paths only under its own
 * namespace, i.e. `addPath($dir, '<module_slug>')`, and substitute templates
 * by rewriting the template name to `@<module_slug>/…`.
 *
 * `FilesystemLoader::prependPath()` is prohibited outright, and `addPath()`
 * without an explicit second argument is prohibited, because an unnamespaced
 * path shadows core templates in a resolution-order dependent way — the exact
 * fragility Q38 exists to remove.
 *
 * Matching is by method name (the same approach as ForbiddenMethodsRule),
 * scoped to the branding module namespace so the rest of the codebase is
 * untouched.
 *
 * Both the instance and the static call forms are inspected. `prependPath()` and
 * `addPath()` are instance methods on Twig's FilesystemLoader, so writing either
 * statically is a PHP fatal rather than a working bypass — but a rule that silently
 * ignores the static form would report "clean" on code that is plainly trying to do the
 * forbidden thing, and would enshrine that blind spot as intended behaviour. Matching
 * on the shared CallLike shape costs nothing and removes the ambiguity.
 *
 * @implements Rule<CallLike>
 */
final class ForbiddenBrandingTwigPathRule implements Rule
{
    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param CallLike $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!($node instanceof MethodCall) && !($node instanceof StaticCall)) {
            return [];
        }

        if (!$this->isBrandingNamespace($scope)) {
            return [];
        }

        if (!($node->name instanceof Identifier)) {
            return [];
        }

        $methodName = $node->name->toLowerString();

        if ($methodName === 'prependpath') {
            return [
                $this->buildError(
                    'FilesystemLoader::prependPath() is forbidden in ThiqaBranding code: it registers an '
                    . 'unnamespaced, resolution-order dependent template path.',
                    'Register the module template directory with '
                    . 'addPath($dir, \'oe-module-thiqa-branding\') and rewrite the template name to '
                    . '@oe-module-thiqa-branding/… from a TemplatePageEvent listener.',
                ),
            ];
        }

        if ($methodName !== 'addpath') {
            return [];
        }

        if (count($node->getArgs()) >= 2) {
            return [];
        }

        return [
            $this->buildError(
                'FilesystemLoader::addPath() called without an explicit Twig namespace is forbidden in '
                . 'ThiqaBranding code: an unnamespaced path shadows core templates in a '
                . 'resolution-order dependent way.',
                'Pass the module slug as the second argument: '
                . 'addPath($dir, \'oe-module-thiqa-branding\').',
            ),
        ];
    }

    private function buildError(string $message, string $tip): IdentifierRuleError
    {
        return RuleErrorBuilder::message(
            $message . ' Prohibited by locked Q38 / resolution CR-17.',
        )
            ->identifier('thiqaBranding.twigNamespaceDiscipline')
            ->tip($tip)
            ->build();
    }

    /**
     * Scoped to the branding module only; core OpenEMR and other modules keep
     * their existing Twig loader calls.
     */
    private function isBrandingNamespace(Scope $scope): bool
    {
        return BrandingGuardrailScope::covers($scope->getNamespace());
    }
}
