<?php

/**
 * Custom PHPStan Rule to forbid defining new functions in the global namespace
 *
 * @package   OpenEMR
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Function_>
 */
class ForbiddenGlobalNamespaceRule implements Rule
{
    public function getNodeType(): string
    {
        return Function_::class;
    }

    /**
     * @param Function_ $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $ns = $scope->getNamespace();
        if ($ns !== null) {
            // Not in global namespace, this is permitted.
            return [];
        }

        // This is a bit fragile - trim the leading app root plus trailing
        // slash from the file being examined, then check if it's in the
        // autoload.files path in composer.json.
        $composer = file_get_contents('composer.json');
        if ($composer === false) {
            return [];
        }
        /** @var array{autoload?: array{files?: list<string>}} $parsed */
        $parsed = json_decode($composer, true);
        $allowed = $parsed['autoload']['files'] ?? [];

        $appRoot = getcwd();
        if ($appRoot === false) {
            return [];
        }
        $definingFileAbs = $scope->getFile();
        $definingFile = substr($definingFileAbs, strlen($appRoot) + 1);
        // Normalise to the forward slashes composer.json's autoload.files always uses. On a
        // native Windows host both getcwd() and $scope->getFile() return backslash-separated
        // paths, so the raw substr() above never matches an allowed entry and every legacy
        // global function in every allow-listed file — not just new ones — was misreported as
        // forbidden. A no-op on POSIX, where the paths already use forward slashes.
        $definingFile = str_replace('\\', '/', $definingFile);

        if (in_array($definingFile, $allowed, true)) {
            return [];
        }

        // Everything else past this point is forbidden: globally-namespaced
        // function outside of the autoload path.

        $functionName = $node->name->toString();

        $message = sprintf(
            'Function %s may not be defined in the global namespace.',
            $functionName,
        );
        $closureTip = sprintf(
            'Try a closure, like $%s = function () { ... }',
            $functionName,
        );
        $ooTip = 'A static method in an auto-loaded class works too';
        $includeTip = 'If this MUST be a global function, use `library/global_functions.inc.php` as a last resort.';
        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.noGlobalNsFunctions')
                ->addTip($closureTip)
                ->addTip($ooTip)
                ->addTip($includeTip)
                ->build(),
        ];
    }
}
