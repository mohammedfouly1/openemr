<?php

/**
 * Forbid placeholder and upstream-brand endpoints in shipped Thiqa branding config
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
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Guardrail for RebrandingPlan WP-2.7(g).
 *
 * Shipped branding code must not carry a documentation placeholder host
 * (`thiqa.example` or any other RFC 2606 `.example` domain) nor the upstream
 * product registration endpoint `reg.open-emr.org`. Either one reaching
 * production means the tenant endpoint was never configured, or upstream
 * branding leaked back in.
 *
 * Test code inside the module may use placeholder hosts freely, so any
 * namespace containing a `Tests` segment is skipped.
 *
 * @implements Rule<String_>
 */
final class ForbiddenBrandingPlaceholderDomainRule implements Rule
{
    /**
     * Literal hosts that are always wrong in shipped code.
     */
    private const FORBIDDEN_LITERALS = [
        'thiqa.example',
        'reg.open-emr.org',
    ];

    /**
     * Any RFC 2606 `.example` host, e.g. `cp.thiqa.example`. The trailing
     * lookahead keeps `config.example.php` and `.example.com` from matching.
     */
    private const EXAMPLE_DOMAIN_PATTERN =
        '~(?:^|[^a-z0-9.-])((?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+example)(?![a-z0-9.-])~i';

    public function getNodeType(): string
    {
        return String_::class;
    }

    /**
     * @param String_ $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isShippedBrandingCode($scope)) {
            return [];
        }

        $value = $node->value;

        foreach (self::FORBIDDEN_LITERALS as $literal) {
            if (stripos($value, $literal) !== false) {
                return [$this->buildError($literal)];
            }
        }

        $matches = [];
        if (preg_match(self::EXAMPLE_DOMAIN_PATTERN, $value, $matches) === 1) {
            return [$this->buildError($matches[1])];
        }

        return [];
    }

    private function buildError(string $host): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(
            'Placeholder or upstream-brand endpoint "%s" must not appear in shipped ThiqaBranding '
            . 'configuration (RebrandingPlan WP-2.7g).',
            $host,
        ))
            ->identifier('thiqaBranding.noPlaceholderEndpoint')
            ->tip(
                'Read the endpoint from tenant configuration instead of hard-coding a '
                . '.example placeholder or an upstream open-emr.org host.',
            )
            ->build();
    }

    /**
     * Scoped to non-test code in the branding module only.
     */
    private function isShippedBrandingCode(Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if (!BrandingGuardrailScope::covers($namespace)) {
            return false;
        }

        return !BrandingGuardrailScope::isTestNamespace((string) $namespace);
    }
}
