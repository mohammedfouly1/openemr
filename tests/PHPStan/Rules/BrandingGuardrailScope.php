<?php

/**
 * Which namespaces the branding guardrails govern.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PHPStan\Analyser\Scope;

/**
 * Findings S4B-10 / S4E-06: the guardrails governed one namespace, and branding code had
 * grown into three.
 *
 * All four `ForbiddenBranding*Rule` classes decided their scope by comparing PHPStan's
 * `Scope::getNamespace()` against a private `MODULE_NAMESPACE` constant naming the module.
 * That was right when the module was the only place branding code lived. It stopped being
 * right the moment the pre-database identity work (ADR-BRAND-005) put shipped branding code
 * in `OpenEMR\Common\Branding`, and the generator toolchain in `OpenEMR\Branding` — neither
 * of which any rule could see. Those namespaces were outside every guardrail while the rules
 * went on reporting `0 errors`, which reads exactly like compliance.
 *
 * **Why the module constant could not simply be widened.** `ThiqaBrandingGuardrailScopeTest`
 * asserts that constant *equals* the namespace the module actually ships under, in both
 * directions, so that a production rename cannot silently orphan the rules. That invariant is
 * correct and worth keeping. Scope is therefore a separate axis: the module namespace remains
 * exactly the module's, and this class names the full set of namespaces the rules govern.
 *
 * The set is checked against reality rather than trusted: the scope suite walks the
 * branding-owned source roots, reads the namespaces actually declared there, and fails when
 * one is not covered here. Add a fourth branding namespace and forget this list, and the
 * suite says so.
 */
final class BrandingGuardrailScope
{
    /**
     * The branding module's own namespace.
     *
     * Kept as its own constant because `ThiqaBrandingGuardrailScopeTest` pins it to the
     * module's real namespace in both directions; it is not merely the first entry of a list.
     */
    public const MODULE_NAMESPACE = 'OpenEMR\\Modules\\ThiqaBranding';

    /**
     * Shipped branding code that runs before the module can boot.
     *
     * `setup.php`, `interface/globals.php` and `library/globals.inc.php` reach this namespace
     * without an OpenEMR bootstrap, which is precisely why it exists and precisely why it
     * needs guarding: it is the least supervised code in the branding layer.
     */
    public const PRE_DATABASE_NAMESPACE = 'OpenEMR\\Common\\Branding';

    /**
     * The generator toolchain under `tools/branding/src`.
     *
     * It does not run in production, but it *writes* what production reads, so a guardrail
     * breach here ships downstream rather than staying in a tool.
     */
    public const TOOLCHAIN_NAMESPACE = 'OpenEMR\\Branding';

    /**
     * Every namespace the branding guardrails govern.
     *
     * @var list<string>
     */
    public const GUARDED_NAMESPACES = [
        self::MODULE_NAMESPACE,
        self::PRE_DATABASE_NAMESPACE,
        self::TOOLCHAIN_NAMESPACE,
    ];

    /**
     * Whether a namespace, or any namespace beneath it, is branding-owned.
     *
     * The separator is appended before the prefix test so that a sibling such as
     * `OpenEMR\BrandingReports` is not swept in by a bare `str_starts_with`.
     */
    public static function covers(?string $namespace): bool
    {
        if ($namespace === null || $namespace === '') {
            return false;
        }

        foreach (self::GUARDED_NAMESPACES as $guarded) {
            if ($namespace === $guarded || str_starts_with($namespace, $guarded . '\\')) {
                return true;
            }
        }

        return false;
    }

    /** Convenience for the rules, which all begin from a PHPStan scope. */
    public static function coversScope(Scope $scope): bool
    {
        return self::covers($scope->getNamespace());
    }

    /**
     * Whether a namespace sits under one of the guarded roots but inside a `Tests` segment.
     *
     * Test code may hold placeholder hosts and other fixtures that shipped code may not, and
     * more than one rule needs that distinction.
     */
    public static function isTestNamespace(string $namespace): bool
    {
        return in_array('Tests', explode('\\', $namespace), true);
    }
}
