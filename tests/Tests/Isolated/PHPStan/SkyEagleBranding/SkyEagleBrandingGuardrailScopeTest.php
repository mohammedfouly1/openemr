<?php

/**
 * Cross-check: the guardrail namespace constants against the real branding module
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\SkyEagleBranding;

use OpenEMR\PHPStan\Rules\BrandingGuardrailScope;
use OpenEMR\PHPStan\Rules\ForbiddenBrandingHttpClientRule;
use OpenEMR\PHPStan\Rules\ForbiddenBrandingPlaceholderDomainRule;
use OpenEMR\PHPStan\Rules\ForbiddenBrandingSiteConfigRule;
use OpenEMR\PHPStan\Rules\ForbiddenBrandingTwigPathRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;

/**
 * Finding S1-P1-04. All four guardrails decide their scope by comparing PHPStan's
 * `Scope::getNamespace()` against a hardcoded `MODULE_NAMESPACE` constant. Nothing checked
 * that constant against the namespace the module actually ships under, and the deliberately
 * violating fixtures declare the same literal — so rule, fixture and expectation were
 * consistent *with each other* by construction. Rename the production namespace and miss the
 * constants, and every rule keeps loading, keeps running, matches nothing, and reports
 * `0 errors`: indistinguishable from compliance. `SkyEagleBrandingRuleRegistrationTest` cannot
 * catch that; its own docblock says it proves wiring, not matching.
 *
 * This suite closes it by deriving the production namespace from the module itself, never
 * from a rule, and failing when the two disagree in **either** direction:
 *
 *  - production renamed, constants not → the equality assertions fail;
 *  - constants renamed, production not → the same assertions fail;
 *  - fixtures renamed alone → the fixture-scope assertion fails, so the rule suites cannot
 *    quietly go on testing a namespace nothing ships under.
 *
 * The module is located by a **brand-neutral** anchor — the unique custom module carrying
 * `src/Config/BrandingGlobalKey.php`, whose `LAYER_PREFIX` is the `saas_branding_` prefix
 * locked decision Q58 forbids renaming. A future SkyEagle migration renames the namespace and
 * may rename the directory; it does not rename that file or that prefix, so this test keeps
 * finding the right module and keeps demanding the constants follow.
 *
 * ## Findings S4B-10 / S4E-06 — the second question this suite now answers
 *
 * The above asks "does the guarded namespace still exist". It cannot ask "is every namespace
 * that holds branding code guarded", and those turned out to be different questions: shipped
 * branding code had grown into `OpenEMR\Common\Branding` and `OpenEMR\Branding`, and no rule
 * could see either. Both cross-checks now run, and the scope itself has a single owner in
 * `BrandingGuardrailScope` rather than a copy inside each of the four rules.
 */
final class SkyEagleBrandingGuardrailScopeTest extends TestCase
{
    private const REPOSITORY_ROOT = __DIR__ . '/../../../../..';

    private const CUSTOM_MODULES = self::REPOSITORY_ROOT . '/interface/modules/custom_modules';

    /** Brand-neutral file that identifies the branding module regardless of its name. */
    private const MODULE_ANCHOR = 'src/Config/BrandingGlobalKey.php';

    /** Locked Q58's reserved prefix, used to confirm the anchor found the right module. */
    private const RESERVED_GLOBALS_PREFIX = 'saas_branding_';

    /**
     * A floor, not a count. It only has to be high enough that a broken glob or a moved
     * directory cannot make the namespace sweep pass vacuously; the module ships far more.
     */
    private const MINIMUM_MODULE_SOURCE_FILES = 50;

    /**
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

    // ------------------------------------------------------- the independent derivation

    public function testTheModuleIsFoundByItsBrandNeutralAnchor(): void
    {
        $directory = $this->moduleDirectory();

        self::assertFileExists($directory . '/' . self::MODULE_ANCHOR);
        self::assertStringContainsString(
            self::RESERVED_GLOBALS_PREFIX,
            (string) file_get_contents($directory . '/' . self::MODULE_ANCHOR),
            'The anchor file no longer carries the Q58 reserved prefix, so it may not be the branding module.',
        );
    }

    /**
     * The PSR-4 prefix is not decoration: it is what makes the module's classes loadable at
     * all, so a real namespace rename cannot skip it.
     */
    public function testEveryShippedSourceFileDeclaresTheAutoloadedNamespace(): void
    {
        $namespace = $this->productionNamespace();
        $files = $this->moduleSourceFiles();

        self::assertGreaterThanOrEqual(
            self::MINIMUM_MODULE_SOURCE_FILES,
            count($files),
            'Found implausibly few module source files; the sweep would pass vacuously.',
        );

        foreach ($files as $file) {
            $declared = $this->declaredNamespace($file);

            self::assertNotNull($declared, sprintf('%s declares no namespace.', basename($file)));
            self::assertTrue(
                $declared === $namespace || str_starts_with($declared, $namespace . '\\'),
                sprintf('%s declares %s, outside the autoloaded prefix %s.', basename($file), $declared, $namespace),
            );
        }
    }

    // --------------------------------------------------------------- the cross-check itself

    /**
     * The assertion the finding asked for, in both directions.
     *
     * The constant now has **one** owner. It used to be copied into each of the four rules, and
     * this test checked all four copies against reality — sound, but the S1-P2-12 lesson applies
     * here as much as it did to the module-directory literal: a value with four definitions is a
     * value with four chances to drift. `BrandingGuardrailScope` owns it, the rules read it, and
     * {@see self::testNoRuleReintroducesItsOwnNamespaceLiteral()} keeps a copy from creeping back.
     */
    public function testTheGuardrailScopeConstantMatchesTheProductionNamespace(): void
    {
        self::assertSame(
            $this->productionNamespace(),
            BrandingGuardrailScope::MODULE_NAMESPACE,
            'Every branding guardrail would go inert: they match a namespace the module does not '
            . 'ship under, so they report 0 errors whatever the module does.',
        );
    }

    /**
     * A rule that hardcodes its own namespace has quietly opted out of the single owner, and
     * with it out of both cross-checks above and below.
     *
     * @param class-string $ruleClass
     */
    #[DataProvider('brandingRuleProvider')]
    public function testNoRuleReintroducesItsOwnNamespaceLiteral(string $ruleClass): void
    {
        $reflection = new \ReflectionClass($ruleClass);
        $file = $reflection->getFileName();
        self::assertIsString($file);

        $source = file_get_contents($file);
        self::assertIsString($source);

        self::assertDoesNotMatchRegularExpression(
            "~const\s+\w*NAMESPACE\s*=\s*'~",
            $source,
            sprintf(
                '%s declares a namespace as a string literal. Scope belongs to '
                . 'BrandingGuardrailScope; a private copy drifts silently and takes the rule out '
                . 'of guardrail coverage without changing a single test.',
                $ruleClass,
            ),
        );
    }

    /**
     * S4B-10 / S4E-06: the guardrails must cover every namespace branding code actually lives in.
     *
     * The rules governed only the module. Shipped branding code had since grown into
     * `OpenEMR\Common\Branding` (the pre-database identity layer, ADR-BRAND-005) and
     * `OpenEMR\Branding` (the generator toolchain), and neither was inside any rule's scope —
     * so the newest and least supervised branding code in the repository was unguarded while
     * all four rules went on reporting 0 errors.
     *
     * The roots are named as **paths**, and the namespaces are read out of the files found
     * there, for the same reason the module is located by a brand-neutral anchor: a rename that
     * changes a namespace must fail this test rather than quietly satisfy it. Adding a fourth
     * branding source root and forgetting the scope list fails here too.
     */
    public function testEveryBrandingOwnedNamespaceIsWithinGuardrailScope(): void
    {
        $roots = [
            $this->moduleDirectory() . '/src',
            self::REPOSITORY_ROOT . '/src/Common/Branding',
            self::REPOSITORY_ROOT . '/tools/branding/src',
        ];

        $found = [];
        foreach ($roots as $root) {
            self::assertDirectoryExists($root, 'A declared branding source root has moved: ' . $root);

            foreach ($this->phpFilesUnder($root) as $file) {
                $namespace = $this->declaredNamespace($file);
                if ($namespace !== null) {
                    $found[$namespace] = $file;
                }
            }
        }

        self::assertGreaterThanOrEqual(
            3,
            count($found),
            'Implausibly few namespaces across the branding source roots; the walk found nothing.',
        );

        $unguarded = [];
        foreach ($found as $namespace => $file) {
            if (!BrandingGuardrailScope::covers($namespace)) {
                $unguarded[] = $namespace . ' (' . basename($file) . ')';
            }
        }

        self::assertSame(
            [],
            $unguarded,
            'These namespaces hold branding code that no guardrail rule can see, so every rule '
            . 'reports 0 errors for them whatever they contain.',
        );
    }

    /**
     * The HTTP-client rule's one exemption. If this drifted, the out-of-request plane would
     * either lose its exemption or hand it to a namespace that does not exist.
     */
    public function testTheMaterialisationExemptionNamesARealSubNamespace(): void
    {
        $expected = $this->productionNamespace() . '\\Materialisation';

        self::assertSame(
            $expected,
            $this->constantValue(ForbiddenBrandingHttpClientRule::class, 'MATERIALISATION_NAMESPACE'),
        );

        self::assertDirectoryExists(
            $this->moduleDirectory() . '/src/Materialisation',
            'The exempted sub-namespace has no directory in the shipped module.',
        );
    }

    /**
     * Fixtures share the production namespace by construction, which is exactly why they
     * cannot also be the evidence that the constants are right. Pinning them here means a
     * rename that touches only the fixtures fails instead of keeping the suites green
     * against a namespace nothing ships under.
     */
    public function testTheViolatingFixturesDeclareTheProductionNamespace(): void
    {
        $namespace = $this->productionNamespace();
        $inScope = 0;

        foreach ($this->fixtureFiles() as $file) {
            $declared = $this->declaredNamespace($file);
            if ($declared === null) {
                continue;
            }

            // Out-of-scope fixtures are deliberate negative controls (a sibling namespace, a
            // core namespace); only the in-scope ones have to track production.
            if ($declared === $namespace || str_starts_with($declared, $namespace . '\\')) {
                ++$inScope;
            }
        }

        self::assertGreaterThan(
            0,
            $inScope,
            sprintf('No guardrail fixture declares %s, so the rule suites prove nothing about it.', $namespace),
        );
    }

    /**
     * The Twig rule tells an operator to call `addPath($dir, '<slug>')`. The slug is a
     * literal in the message, so a directory rename would leave the guardrail instructing
     * people to register a namespace that does not exist.
     */
    public function testTheTwigRuleTipNamesTheRealModuleDirectory(): void
    {
        $slug = basename($this->moduleDirectory());
        $source = (string) file_get_contents(
            self::REPOSITORY_ROOT . '/tests/PHPStan/Rules/ForbiddenBrandingTwigPathRule.php',
        );

        self::assertStringContainsString(
            $slug,
            $source,
            'The Twig guardrail names a module directory that does not exist.',
        );
    }

    // ---------------------------------------------------------------------------- helpers

    /**
     * @param class-string $ruleClass
     */
    private function constantValue(string $ruleClass, string $name): string
    {
        $constant = new ReflectionClassConstant($ruleClass, $name);
        $value = $constant->getValue();

        self::assertIsString($value);

        return $value;
    }

    /** The branding module's directory, located without reference to any brand name. */
    private function moduleDirectory(): string
    {
        $matches = glob(self::CUSTOM_MODULES . '/*/' . self::MODULE_ANCHOR) ?: [];

        self::assertCount(
            1,
            $matches,
            sprintf('Expected exactly one custom module carrying %s.', self::MODULE_ANCHOR),
        );

        return dirname($matches[0], 3);
    }

    /** The namespace the module is actually autoloaded under, from its own composer.json. */
    private function productionNamespace(): string
    {
        $manifest = json_decode(
            (string) file_get_contents($this->moduleDirectory() . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        self::assertIsArray($manifest);
        $autoload = $manifest['autoload']['psr-4'] ?? null;
        self::assertIsArray($autoload);
        self::assertCount(1, $autoload, 'The branding module must declare exactly one PSR-4 prefix.');

        $prefix = (string) array_key_first($autoload);
        self::assertNotSame('', $prefix);

        return rtrim($prefix, '\\');
    }

    /**
     * @return list<string>
     */
    private function moduleSourceFiles(): array
    {
        return $this->phpFilesUnder($this->moduleDirectory() . '/src');
    }

    /**
     * @return list<string>
     */
    private function fixtureFiles(): array
    {
        return $this->phpFilesUnder(__DIR__ . '/data');
    }

    /**
     * @return list<string>
     */
    private function phpFilesUnder(string $directory): array
    {
        $found = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $entry) {
            if ($entry instanceof \SplFileInfo && $entry->isFile() && $entry->getExtension() === 'php') {
                $found[] = $entry->getPathname();
            }
        }

        sort($found);

        return $found;
    }

    /** The `namespace X;` a file declares, read as text so nothing has to be loaded. */
    private function declaredNamespace(string $file): ?string
    {
        $matches = [];
        $found = preg_match(
            '~^namespace\s+([A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff\\\\]*)\s*;~m',
            (string) file_get_contents($file),
            $matches,
        );

        return $found === 1 ? $matches[1] : null;
    }
}
