<?php

/**
 * Forbid HTTP clients in the Thiqa branding runtime plane
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
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UnionType;
use PhpParser\Node\UseItem;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Guardrail for RebrandingPlan WP-2.7(a), constraint C5, locked Q76.
 *
 * OpenEMR must perform no Control Plane network request while rendering an
 * ordinary page. Every class under `OpenEMR\Modules\SkyEagleBranding\` therefore
 * belongs to the runtime plane and may not touch an HTTP client, with the
 * single exception of the out-of-request `\Materialisation` sub-namespace.
 *
 * Only literal and constant-string URLs are resolvable statically, so
 * `file_get_contents()` / `fopen()` are flagged when the first argument is a
 * constant string, an interpolated string or a concatenation whose leftmost
 * literal begins with `http://` or `https://`.
 *
 * @implements Rule<Node>
 */
final class ForbiddenBrandingHttpClientRule implements Rule
{
    /**
     * The materialisation plane runs out-of-request (console command), so it
     * is the one place a Control Plane call is legitimate.
     */
    private const MATERIALISATION_NAMESPACE = BrandingGuardrailScope::MODULE_NAMESPACE . '\\Materialisation';

    /**
     * Class-name prefixes that identify an HTTP client (or its interface).
     *
     * `GuzzleHttp\Client` as a prefix also covers `GuzzleHttp\ClientInterface`.
     */
    private const FORBIDDEN_CLASS_PREFIXES = [
        'GuzzleHttp\\Client',
        'Symfony\\Component\\HttpClient\\',
        'Symfony\\Contracts\\HttpClient\\',
        'Psr\\Http\\Client\\',
        'OpenEMR\\Common\\Http\\',
    ];

    /**
     * Stream functions that reach the network when handed an http(s) URL.
     */
    private const URL_AWARE_FUNCTIONS = [
        'file_get_contents' => true,
        'fopen' => true,
    ];

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @param Node $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isRuntimePlane($scope)) {
            return [];
        }

        if ($node instanceof Use_) {
            return $this->checkImports($node->uses, '');
        }

        if ($node instanceof GroupUse) {
            return $this->checkImports($node->uses, $node->prefix->toString() . '\\');
        }

        if ($node instanceof New_ && $node->class instanceof Name) {
            return $this->checkClassName($node->class->toString());
        }

        if ($node instanceof StaticCall && $node->class instanceof Name) {
            return $this->checkClassName($node->class->toString());
        }

        if ($node instanceof Param) {
            return $this->checkTypeNode($node->type);
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            return $this->checkFunctionCall($node, $node->name->toLowerString(), $scope);
        }

        return [];
    }

    /**
     * True only for the branding module's runtime plane — the module
     * namespace minus its `\Materialisation` sub-tree. Everything else in the
     * OpenEMR codebase legitimately uses HTTP clients and must never be
     * flagged by this rule.
     */
    private function isRuntimePlane(Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if (!BrandingGuardrailScope::covers($namespace)) {
            return false;
        }

        return $namespace !== self::MATERIALISATION_NAMESPACE
            && !str_starts_with($namespace, self::MATERIALISATION_NAMESPACE . '\\');
    }

    /**
     * php-parser types both `Use_::$uses` and `GroupUse::$uses` as `UseItem[]`,
     * which carries no key guarantee, so the contract here matches that rather
     * than claiming a narrower `list`. Only the values are ever read.
     *
     * @param array<array-key, UseItem> $uses
     * @return list<IdentifierRuleError>
     */
    private function checkImports(array $uses, string $prefix): array
    {
        $errors = [];
        foreach ($uses as $use) {
            foreach ($this->checkClassName($prefix . $use->name->toString()) as $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function checkTypeNode(?Node $type): array
    {
        if ($type instanceof Name) {
            return $this->checkClassName($type->toString());
        }

        if ($type instanceof NullableType) {
            return $this->checkTypeNode($type->type);
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            $errors = [];
            foreach ($type->types as $inner) {
                foreach ($this->checkTypeNode($inner) as $error) {
                    $errors[] = $error;
                }
            }

            return $errors;
        }

        // Identifier (a built-in type) and a missing type carry no class name.
        return [];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function checkClassName(string $className): array
    {
        $normalised = ltrim($className, '\\');
        foreach (self::FORBIDDEN_CLASS_PREFIXES as $prefix) {
            if (str_starts_with($normalised, $prefix)) {
                return [$this->buildError(sprintf('HTTP client class "%s"', $normalised))];
            }
        }

        return [];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function checkFunctionCall(FuncCall $node, string $functionName, Scope $scope): array
    {
        if (str_starts_with($functionName, 'curl_')) {
            return [$this->buildError(sprintf('Function %s()', $functionName))];
        }

        if (!isset(self::URL_AWARE_FUNCTIONS[$functionName])) {
            return [];
        }

        $firstArg = $node->getArgs()[0] ?? null;
        if ($firstArg === null) {
            return [];
        }

        if (!$this->looksLikeHttpUrl($firstArg->value, $scope)) {
            return [];
        }

        return [$this->buildError(sprintf('Call to %s() with an http(s) URL', $functionName))];
    }

    private function looksLikeHttpUrl(Expr $expr, Scope $scope): bool
    {
        foreach ($scope->getType($expr)->getConstantStrings() as $constantString) {
            if ($this->isHttpUrl($constantString->getValue())) {
                return true;
            }
        }

        // A concatenation or interpolation is not a constant string, but its
        // leftmost literal still reveals the scheme.
        while ($expr instanceof Concat) {
            $expr = $expr->left;
        }

        if ($expr instanceof String_) {
            return $this->isHttpUrl($expr->value);
        }

        if ($expr instanceof InterpolatedString) {
            $first = $expr->parts[0] ?? null;

            return $first instanceof Node\InterpolatedStringPart && $this->isHttpUrl($first->value);
        }

        return false;
    }

    private function isHttpUrl(string $value): bool
    {
        return preg_match('~^https?://~i', $value) === 1;
    }

    private function buildError(string $subject): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(
            '%s is forbidden in the SkyEagleBranding runtime plane: OpenEMR must make no Control Plane '
            . 'network request during ordinary page rendering (prohibited by locked Q76 / constraint C5).',
            $subject,
        ))
            ->identifier('skyeagleBranding.noRuntimeHttpClient')
            ->tip(
                'Move the network call into the out-of-request materialisation plane '
                . '(OpenEMR\Modules\SkyEagleBranding\Materialisation); the runtime plane may read only '
                . 'the globals table and the filesystem.',
            )
            ->build();
    }
}
