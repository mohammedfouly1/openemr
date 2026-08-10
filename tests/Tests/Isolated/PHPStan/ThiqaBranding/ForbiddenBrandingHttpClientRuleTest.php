<?php

/**
 * Tests for ForbiddenBrandingHttpClientRule
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\ThiqaBranding;

use OpenEMR\PHPStan\Rules\ForbiddenBrandingHttpClientRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenBrandingHttpClientRule>
 */
final class ForbiddenBrandingHttpClientRuleTest extends RuleTestCase
{
    private const TIP = 'Move the network call into the out-of-request materialisation plane '
        . '(OpenEMR\Modules\ThiqaBranding\Materialisation); the runtime plane may read only '
        . 'the globals table and the filesystem.';

    private const IDENTIFIER = 'thiqaBranding.noRuntimeHttpClient';

    protected function getRule(): Rule
    {
        return new ForbiddenBrandingHttpClientRule();
    }

    public function testFlagsHttpClientsInTheRuntimePlane(): void
    {
        $this->analyse(
            [__DIR__ . '/data/http_client_runtime_violations.php'],
            [
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 7, self::TIP],
                [
                    $this->expectedMessage(
                        'HTTP client class "Symfony\Contracts\HttpClient\HttpClientInterface"',
                    ),
                    8,
                    self::TIP,
                ],
                [
                    $this->expectedMessage(
                        'HTTP client class "Symfony\Contracts\HttpClient\HttpClientInterface"',
                    ),
                    11,
                    self::TIP,
                ],
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 14, self::TIP],
                [$this->expectedMessage('Function curl_init()'), 17, self::TIP],
                [$this->expectedMessage('Function curl_close()'), 18, self::TIP],
                [$this->expectedMessage('Call to file_get_contents() with an http(s) URL'), 20, self::TIP],
                [$this->expectedMessage('Call to fopen() with an http(s) URL'), 21, self::TIP],
            ],
        );
    }

    public function testFlagsFurtherHttpClientShapesInTheRuntimePlane(): void
    {
        // Group use, static call, the OpenEMR HTTP wrapper namespace,
        // nullable and union parameter types, and URLs built by
        // concatenation or interpolation.
        $this->analyse(
            [__DIR__ . '/data/http_client_runtime_violations_extended.php'],
            [
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 11, self::TIP],
                [
                    $this->expectedMessage('HTTP client class "OpenEMR\Common\Http\oeHttp"'),
                    12,
                    self::TIP,
                ],
                [
                    $this->expectedMessage('HTTP client class "OpenEMR\Common\Http\oeHttp"'),
                    16,
                    self::TIP,
                ],
                [
                    $this->expectedMessage('HTTP client class "GuzzleHttp\ClientInterface"'),
                    18,
                    self::TIP,
                ],
                [
                    $this->expectedMessage('HTTP client class "Psr\Http\Client\ClientInterface"'),
                    21,
                    self::TIP,
                ],
                [$this->expectedMessage('Call to file_get_contents() with an http(s) URL'), 24, self::TIP],
                [$this->expectedMessage('Call to file_get_contents() with an http(s) URL'), 25, self::TIP],
            ],
        );
    }

    public function testAllowsHttpClientsInTheMaterialisationPlane(): void
    {
        $this->analyse(
            [__DIR__ . '/data/http_client_materialisation_allowed.php'],
            [],
        );
    }

    public function testAllowsHttpClientsInNestedMaterialisationNamespaces(): void
    {
        $this->analyse(
            [__DIR__ . '/data/http_client_materialisation_nested_allowed.php'],
            [],
        );
    }

    public function testFlagsHttpClientsInAMaterialisationLookalikeNamespace(): void
    {
        // `...\MaterialisationHelper` is a different namespace segment, so
        // the out-of-request exemption must not extend to it.
        $this->analyse(
            [__DIR__ . '/data/http_client_materialisation_lookalike.php'],
            [
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 10, self::TIP],
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 12, self::TIP],
                [$this->expectedMessage('Function curl_init()'), 14, self::TIP],
            ],
        );
    }

    public function testAllowsFilesystemReadsInTheRuntimePlane(): void
    {
        $this->analyse(
            [__DIR__ . '/data/http_client_runtime_allowed.php'],
            [],
        );
    }

    public function testDoesNotFireOutsideTheBrandingModule(): void
    {
        $this->analyse(
            [__DIR__ . '/data/http_client_outside_module.php'],
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
        // Only HTTP-client errors are expected: the same file also violates
        // the site-config, Twig-path and placeholder-domain guardrails, and
        // RuleTestCase fails on any unexpected error, so this simultaneously
        // proves this rule does not fire on the other three rules' subjects.
        $this->analyse(
            [__DIR__ . '/data/all_violations_branding_namespace.php'],
            [
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 11, self::TIP],
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 14, self::TIP],
                [$this->expectedMessage('Function curl_init()'), 15, self::TIP],
                [$this->expectedMessage('Function curl_close()'), 16, self::TIP],
                [$this->expectedMessage('Call to file_get_contents() with an http(s) URL'), 17, self::TIP],
            ],
        );
    }

    public function testFlagsViolationsInTheModuleRootNamespace(): void
    {
        $this->analyse(
            [__DIR__ . '/data/all_violations_module_root_namespace.php'],
            [
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 10, self::TIP],
                [$this->expectedMessage('HTTP client class "GuzzleHttp\Client"'), 13, self::TIP],
                [$this->expectedMessage('Function curl_init()'), 14, self::TIP],
                [$this->expectedMessage('Function curl_close()'), 15, self::TIP],
                [$this->expectedMessage('Call to file_get_contents() with an http(s) URL'), 16, self::TIP],
            ],
        );
    }

    public function testFlagsIntersectionTypesAndNonLiteralUrls(): void
    {
        // Lines 17-18 are folded to constant strings by PHPStan's inference;
        // lines 21-22 cannot be folded at all, so they exercise the rule's
        // fallback to the leading literal of a concatenation and of an
        // interpolation respectively.
        $this->analyse(
            [__DIR__ . '/data/http_client_runtime_type_violations.php'],
            [
                [
                    $this->expectedMessage('HTTP client class "GuzzleHttp\ClientInterface"'),
                    12,
                    self::TIP,
                ],
                [$this->expectedMessage('Call to file_get_contents() with an http(s) URL'), 17, self::TIP],
                [$this->expectedMessage('Call to fopen() with an http(s) URL'), 18, self::TIP],
                [$this->expectedMessage('Call to file_get_contents() with an http(s) URL'), 21, self::TIP],
                [$this->expectedMessage('Call to file_get_contents() with an http(s) URL'), 22, self::TIP],
            ],
        );
    }

    public function testStaysSilentOnStaticallyUnresolvableShapes(): void
    {
        // Dynamic class names, dynamic static-call targets, dynamic function
        // names, argument-less stream calls, unknown URL parameters, built-in
        // and absent parameter types, and a non-network intersection type.
        $this->analyse(
            [__DIR__ . '/data/http_client_runtime_unresolvable_allowed.php'],
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

    private function expectedMessage(string $subject): string
    {
        return sprintf(
            '%s is forbidden in the ThiqaBranding runtime plane: OpenEMR must make no Control Plane '
            . 'network request during ordinary page rendering (prohibited by locked Q76 / constraint C5).',
            $subject,
        );
    }
}
