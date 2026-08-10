<?php

/**
 * Forbid the per-site config.php seam in Thiqa branding code
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
 * Guardrail for RebrandingPlan WP-2.7(b), constraint C1, BRAND-120.
 *
 * `sites/<site>/config.php` is a PROHIBITED branding mechanism: it is
 * free-form tenant PHP, which is exactly what constraint C1 exists to
 * prevent. Any string literal inside `OpenEMR\Modules\ThiqaBranding\` that
 * names that file — whether written whole or assembled by concatenating a
 * site directory with `/config.php` — is rejected.
 *
 * The check is deliberately scoped to the branding module namespace: the rest
 * of the OpenEMR codebase reads `sites/*\/config.php` legitimately.
 *
 * @implements Rule<String_>
 */
final class ForbiddenBrandingSiteConfigRule implements Rule
{
    private const MODULE_NAMESPACE = 'OpenEMR\\Modules\\ThiqaBranding';

    /**
     * Matches the whole literal `sites/<something>/config.php`, a bare
     * `config.php`, and the `/config.php` tail of a constructed path.
     * A `*` or a variable placeholder in the site position is matched too.
     */
    private const CONFIG_PATH_PATTERN = '~(?:^|[/\\\\])config\.php(?:$|[?#])~i';

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
        if (!$this->isBrandingNamespace($scope)) {
            return [];
        }

        $value = $node->value;
        $normalised = str_replace('\\', '/', $value);

        if (preg_match(self::CONFIG_PATH_PATTERN, $normalised) !== 1) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Reference to the per-site config.php seam ("%s") is forbidden in ThiqaBranding code: '
                . 'sites/<site>/config.php is a PROHIBITED branding mechanism '
                . '(constraint C1 / BRAND-120).',
                $value,
            ))
                ->identifier('thiqaBranding.noSiteConfigSeam')
                ->tip(
                    'Brand values are materialised into the globals table and read through '
                    . 'BrandingConfig; no free-text tenant CSS/JS/PHP seam is permitted.',
                )
                ->build(),
        ];
    }

    /**
     * Scoped to the branding module only — core OpenEMR reads
     * sites/<site>/config.php legitimately and must never be flagged.
     */
    private function isBrandingNamespace(Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if ($namespace === null) {
            return false;
        }

        return $namespace === self::MODULE_NAMESPACE
            || str_starts_with($namespace, self::MODULE_NAMESPACE . '\\');
    }
}
