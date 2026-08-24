<?php

/**
 * Isolated tests for the deterministic Thiqa branding token generator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Generator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * The generator is exercised through its real CLI entry point rather than by
 * instantiating its classes.
 *
 * That is deliberate on two counts. It is the contract CI actually invokes, so
 * exit codes, `--check` and the fail-loud paths are covered rather than assumed.
 * And `OpenEMR\Branding\` deliberately sits outside composer's autoload map so
 * the tool can run from a bare checkout — importing those classes here would
 * make this file unanalysable by the repo-wide PHPStan run until the
 * integration phase wires the namespace in.
 *
 * Generation is driven twice in `setUpBeforeClass` and the bytes cached, so the
 * determinism assertion compares two genuinely independent runs while the rest
 * of the suite costs no extra subprocesses.
 */
class TokenGeneratorIsolatedTest extends TestCase
{
    private const REPO_ROOT_DEPTH = 6;
    private const ENTRY_POINT = 'tools/branding/bin/generate-tokens.php';

    private const EXPECTED_ARTEFACTS = [
        '_tokens-light.scss',
        '_tokens-dark.scss',
        '_css-variables.scss',
        '_typography.scss',
        'smart-style_light.json.twig',
        'smart-style_dark.json.twig',
    ];

    /**
     * Artefact name => contents, from the first run.
     *
     * @var array<string, string>
     */
    private static array $firstRun = [];

    /**
     * Artefact name => contents, from an independent second run.
     *
     * @var array<string, string>
     */
    private static array $secondRun = [];

    private static string $firstRunDirectory = '';

    public static function setUpBeforeClass(): void
    {
        self::$firstRunDirectory = self::makeTemporaryDirectory('run-a');
        self::$firstRun = self::runGenerator(self::$firstRunDirectory);

        $secondDirectory = self::makeTemporaryDirectory('run-b');
        self::$secondRun = self::runGenerator($secondDirectory);
        self::removeDirectory($secondDirectory);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$firstRunDirectory !== '') {
            self::removeDirectory(self::$firstRunDirectory);
            self::$firstRunDirectory = '';
        }
        self::$firstRun = [];
        self::$secondRun = [];
    }

    public function testGeneratesTheSixExpectedArtefacts(): void
    {
        $this->assertSame(self::EXPECTED_ARTEFACTS, array_keys(self::$firstRun));
    }

    public function testOutputIsByteIdenticalAcrossIndependentRuns(): void
    {
        $this->assertSame(
            self::hashes(self::$firstRun),
            self::hashes(self::$secondRun),
            'The generator must be byte-for-byte reproducible across runs.',
        );
        $this->assertSame(self::$firstRun, self::$secondRun);
    }

    public function testCheckModeAcceptsFreshlyGeneratedOutput(): void
    {
        $process = self::process(['--out-dir=' . self::$firstRunDirectory, '--check']);
        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertStringContainsString('up to date', $process->getOutput());
    }

    public function testCheckModeRejectsAHandEditedArtefact(): void
    {
        $directory = self::makeTemporaryDirectory('drift');

        try {
            self::runGenerator($directory);
            file_put_contents($directory . '/_tokens-light.scss', "// hand edited\n", FILE_APPEND);

            $process = self::process(['--out-dir=' . $directory, '--check']);
            $process->run();

            $this->assertSame(3, $process->getExitCode(), 'Drift must exit 3 so CI fails.');
            $this->assertStringContainsString('_tokens-light.scss (differs)', $process->getErrorOutput());
        } finally {
            self::removeDirectory($directory);
        }
    }

    public function testMissingTokenSourceFailsLoudlyAndWritesNothing(): void
    {
        $emptyRoot = self::makeTemporaryDirectory('empty-root');
        $outputDirectory = $emptyRoot . '/out';

        try {
            $process = self::process(['--repo-root=' . $emptyRoot, '--out-dir=' . $outputDirectory]);
            $process->run();

            $this->assertSame(1, $process->getExitCode());
            $this->assertStringContainsString('brand/tokens/thiqa-tokens.json', $process->getErrorOutput());
            $this->assertDirectoryDoesNotExist(
                $outputDirectory,
                'A validation failure must not leave a partially written output directory.',
            );
        } finally {
            self::removeDirectory($emptyRoot);
        }
    }

    public function testUnknownOptionIsRejected(): void
    {
        $process = self::process(['--not-an-option']);
        $process->run();

        $this->assertSame(2, $process->getExitCode());
        $this->assertStringContainsString('Unknown option', $process->getErrorOutput());
    }

    public function testEveryArtefactUsesLfEndingsAndEndsWithANewline(): void
    {
        foreach (self::$firstRun as $name => $contents) {
            $this->assertStringNotContainsString("\r", $contents, $name . ' must use LF endings.');
            $this->assertStringEndsWith("\n", $contents, $name . ' must end with a newline.');
        }
    }

    public function testNoArtefactLeaksAnAbsolutePathOrHostDetail(): void
    {
        $repoRoot = self::repoRoot();
        foreach (self::$firstRun as $name => $contents) {
            $this->assertStringNotContainsString($repoRoot, $contents, $name . ' leaks the repo path.');
            $this->assertStringNotContainsString(strtr($repoRoot, '/', '\\'), $contents, $name . ' leaks a host path.');
            $this->assertStringNotContainsString(self::$firstRunDirectory, $contents, $name . ' leaks the output path.');
        }
    }

    public function testSmartDarkTemplateCarriesDarkValuesNotLightFallbacks(): void
    {
        $dark = self::artefact('smart-style_dark.json.twig');

        $this->assertStringContainsString('"color_background": "#0B1220"', $dark);
        $this->assertStringContainsString('"color_text": "#F5F6F8"', $dark);
        $this->assertStringContainsString('"color_error": "#F29088"', $dark);
        $this->assertStringContainsString('"color_highlight": "#8FC1EE"', $dark);
        $this->assertStringContainsString('"color_success": "#8FD1A6"', $dark);
        $this->assertStringContainsString('"color_modal_backdrop": "rgba(0, 0, 0, 0.6)"', $dark);

        // The whole point of R-SMART-DARK: no light value may survive into dark.
        foreach (['#FAFAF8', '#0B1B4D', '#8E271D', '#3E7FBD', '#2F6B45'] as $lightValue) {
            $this->assertStringNotContainsString($lightValue, $dark, 'Dark must not fall back to a light value.');
        }
    }

    public function testSmartLightTemplateMatchesTheTwelveKeyContract(): void
    {
        $light = self::artefact('smart-style_light.json.twig');

        $this->assertStringContainsString('"logo_primary": "{{ logo.primary }}"', $light);
        $this->assertStringContainsString('"color_background": "#FAFAF8"', $light);
        $this->assertStringContainsString('"color_modal_backdrop": "rgba(11, 27, 77, 0.6)"', $light);
        $this->assertStringContainsString('"dim_font_size": "14px"', $light);
        $this->assertStringContainsString(
            '"font_family_body": "\'Inter\',\'IBM Plex Sans Arabic\',sans-serif"',
            $light,
        );
    }

    #[DataProvider('smartVariantProvider')]
    public function testBothSmartTemplatesCarryExactlyTheTwelveContractKeys(string $artefact): void
    {
        $decoded = json_decode(self::renderedJson($artefact), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($decoded);
        $this->assertSame(
            [
                'color_background',
                'color_error',
                'color_highlight',
                'color_modal_backdrop',
                'color_success',
                'color_text',
                'dim_border_radius',
                'dim_font_size',
                'dim_spacing_size',
                'font_family_body',
                'font_family_heading',
                'logo_primary',
            ],
            array_keys($decoded),
        );
    }

    #[DataProvider('smartVariantProvider')]
    public function testShippedModuleSmartTemplateMatchesGeneratedContract(string $artefact): void
    {
        $shippedPath = self::repoRoot()
            . '/interface/modules/custom_modules/oe-module-thiqa-branding/templates/api/smart/'
            . $artefact;
        $shipped = file_get_contents($shippedPath);

        $this->assertNotFalse($shipped, 'The shipped SMART template must be readable.');
        $this->assertSame(
            json_decode(self::renderedJson($artefact), true, 512, JSON_THROW_ON_ERROR),
            json_decode(self::stripTwigBanner($shipped), true, 512, JSON_THROW_ON_ERROR),
            $artefact . ' drifted from the canonical token generator output.',
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function smartVariantProvider(): array
    {
        return [
            'light' => ['smart-style_light.json.twig'],
            'dark' => ['smart-style_dark.json.twig'],
        ];
    }

    /**
     * Proves docs/rebranding.md §16.1: the SMART contract and the web custom
     * properties are the same numbers because they share one resolved palette.
     *
     * @param list<string> $smartFragments
     */
    #[DataProvider('sharedTokenProvider')]
    public function testSmartAndCssVariablesAgreeOnSharedTokens(
        string $cssDeclaration,
        string $smartArtefact,
        array $smartFragments,
    ): void {
        $this->assertStringContainsString($cssDeclaration, self::artefact('_css-variables.scss'));
        foreach ($smartFragments as $fragment) {
            $this->assertStringContainsString($fragment, self::artefact($smartArtefact));
        }
    }

    /**
     * @return array<string, array{string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sharedTokenProvider(): array
    {
        return [
            'light background and text' => [
                '--background:                          #FAFAF8;',
                'smart-style_light.json.twig',
                ['"color_background": "#FAFAF8"', '"color_text": "#0B1B4D"'],
            ],
            'dark background and text' => [
                '--background:                          #0B1220;',
                'smart-style_dark.json.twig',
                ['"color_background": "#0B1220"', '"color_text": "#F5F6F8"'],
            ],
        ];
    }

    public function testCssVariablesExposeCanonicalLegacyAndOeNames(): void
    {
        $css = self::artefact('_css-variables.scss');

        $this->assertSame(2, substr_count($css, '--interactive-primary-default:'));
        $this->assertSame(
            2,
            preg_match_all(
                '/--thiqa-interactive-primary-default:\s+var\(--interactive-primary-default\);/',
                $css,
            ),
        );
        $this->assertStringContainsString('@mixin thiqa-css-variables-light {', $css);
        $this->assertStringContainsString('@mixin thiqa-css-variables-dark {', $css);

        // Exactly one :root rule, which selects a variant, so a compiled dark
        // stylesheet never carries a light block it would only override.
        $this->assertSame(1, substr_count($css, "\n:root {"));
        $this->assertStringContainsString("\$thiqa-css-variables-variant: 'light' !default;", $css);
        $this->assertStringContainsString("@if \$thiqa-css-variables-variant == 'light' {", $css);
        $this->assertStringContainsString("} @else if \$thiqa-css-variables-variant == 'dark' {", $css);
        $this->assertStringContainsString('@error "Unknown $thiqa-css-variables-variant', $css);
    }

    /**
     * Every `--oe-*` name declared in interface/themes/oe-styles/style_light.scss
     * must survive the retheme, aliased to a token rather than a literal colour.
     */
    #[DataProvider('oeVariableProvider')]
    public function testOeCompatibilityNamesAliasThiqaTokens(string $name): void
    {
        $css = self::artefact('_css-variables.scss');

        $this->assertSame(
            2,
            substr_count($css, $name . ':'),
            $name . ' must be defined in both the light and the dark mixin.',
        );
        $this->assertSame(
            2,
            preg_match_all('/' . preg_quote($name, '/') . ':\s+var\(--[a-z0-9-]+\);/', $css),
            $name . ' must alias a canonical identity-neutral token in both mixins, never a literal colour.',
        );
    }

    /**
     * Tier 2 emits these identity-neutral names. Both generated variants must
     * supply Tier-1 defaults, and real component rules must consume the same
     * names so a materialised overlay changes rendered UI instead of creating
     * an inert parallel namespace.
     */
    public function testLightAndDarkTenantTokensHaveLiveComponentConsumers(): void
    {
        $css = self::artefact('_css-variables.scss');
        $overridesPath = self::repoRoot() . '/interface/themes/thiqa/_overrides.scss';
        $overrides = file_get_contents($overridesPath);

        $this->assertNotFalse($overrides, 'The hand-authored component override partial must be readable.');

        $expected = [
            '--interactive-primary-default',
            '--interactive-primary-hover',
            '--interactive-primary-active',
            '--interactive-primary-disabled',
            '--interactive-primary-text-on',
            '--interactive-secondary-default',
            '--interactive-secondary-hover',
            '--interactive-secondary-text-on',
            '--interactive-focus-ring',
            '--link-default',
            '--link-hover',
        ];

        foreach ($expected as $name) {
            $this->assertSame(2, substr_count($css, $name . ':'), $name . ' needs light and dark defaults.');
            $this->assertStringContainsString('var(' . $name . ',', $overrides, $name . ' needs a live consumer.');
        }

        $this->assertStringContainsString(
            'background-color: var(--interactive-primary-default,',
            $overrides,
        );
        $this->assertStringContainsString('color: var(--link-default,', $overrides);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function oeVariableProvider(): array
    {
        $names = [
            '--oe-body-bg',
            '--oe-body-color',
            '--oe-surface-bg',
            '--oe-navbar-bg',
            '--oe-navbar-color',
            '--oe-navbar-link-hover-bg',
            '--oe-navbar-link-hover-color',
            '--oe-navbar-link-active-bg',
            '--oe-navbar-link-active-color',
            '--oe-fieldset-bg',
            '--oe-fieldset-color',
            '--oe-search-bg',
            '--oe-panel-bg',
        ];

        $cases = [];
        foreach ($names as $name) {
            $cases[$name] = [$name];
        }

        return $cases;
    }

    public function testLightVariantAliasesTheRaisedSurfaceRatherThanInventingOne(): void
    {
        $this->assertStringContainsString(
            '$thiqa-surface-raised:                #FFFFFF; // alias of surface',
            self::artefact('_tokens-light.scss'),
        );
        $this->assertStringContainsString(
            '$thiqa-surface-raised:                #17213A;',
            self::artefact('_tokens-dark.scss'),
        );
    }

    public function testTypographyPreservesTheAuthoredUnicodeRangeSplits(): void
    {
        $typography = self::artefact('_typography.scss');

        $this->assertStringContainsString('unicode-range: U+0000-00FF,', $typography);
        $this->assertStringContainsString('unicode-range: U+0600-06FF,', $typography);
        $this->assertStringContainsString("font-family: 'Inter';", $typography);
        $this->assertStringContainsString("font-family: 'IBM Plex Sans Arabic';", $typography);
        // One @font-face per PHYSICAL face, not per declared weight.
        //
        // Inter is a variable font — its WOFF2 carries fvar/gvar/avar/HVAR/MVAR/STAT — so a
        // single file covers 400 through 700 and is emitted once with a weight RANGE. It was
        // previously emitted four times under four filenames that were byte-identical, which
        // cost ~145 KB of duplicate download on every cold load (docs/RebrandingBugs.md RB-22).
        //
        // IBM Plex Sans Arabic is static (no fvar) and ships four genuinely distinct files, so
        // it keeps four rules. That asymmetry is the point of the assertion: 1 + 4 = 5.
        $this->assertSame(
            5,
            substr_count($typography, '@font-face {'),
            'Expected 1 Inter rule (variable, 400-700) + 4 IBM Plex Sans Arabic rules (static).',
        );
        $this->assertStringContainsString(
            'font-weight: 400 700;',
            $typography,
            'The variable Inter face must declare a weight range, not a single weight.',
        );
        $this->assertSame(
            1,
            substr_count($typography, "src: url('../assets/fonts/thiqa/Inter-Regular.woff2')"),
            'Inter must be fetched from exactly one URL.',
        );
        foreach (['Inter-Medium', 'Inter-SemiBold', 'Inter-Bold'] as $duplicate) {
            $this->assertStringNotContainsString(
                $duplicate . '.woff2',
                $typography,
                $duplicate . '.woff2 is a byte-identical copy of Inter-Regular.woff2; referencing it '
                . 'makes the browser download the same face twice.',
            );
        }

        $this->assertStringContainsString('$thiqa-font-weight-semibold: 600;', $typography);
        $this->assertStringContainsString('$thiqa-font-size-body:', $typography);
        $this->assertStringContainsString('$thiqa-type-scale: (', $typography);

        // Amiri is exported as a family name for the PDF engines, but must never
        // become a web @font-face or pull a .ttf over the wire.
        $this->assertMatchesRegularExpression('/\$thiqa-font-name-arabic-pdf:\s+\'Amiri\';/', $typography);
        $this->assertStringNotContainsString("font-family: 'Amiri'", $typography);
        $this->assertStringNotContainsString('.ttf', $typography);
    }

    public function testFontUrlBaseIsConfigurable(): void
    {
        $directory = self::makeTemporaryDirectory('font-url');

        try {
            $artefacts = self::runGenerator($directory, ['--font-url-base=/custom/fonts']);
            $typography = $artefacts['_typography.scss'] ?? '';

            $this->assertStringContainsString("src: url('/custom/fonts/Inter-Regular.woff2')", $typography);
            $this->assertStringNotContainsString('../assets/fonts/thiqa', $typography);
        } finally {
            self::removeDirectory($directory);
        }
    }

    private static function artefact(string $name): string
    {
        if (!isset(self::$firstRun[$name])) {
            self::fail(sprintf('The generator did not produce "%s".', $name));
        }

        return self::$firstRun[$name];
    }

    /**
     * Strips the Twig banner so the remainder can be decoded as plain JSON.
     */
    private static function renderedJson(string $artefact): string
    {
        return self::stripTwigBanner(self::artefact($artefact));
    }

    private static function stripTwigBanner(string $contents): string
    {
        $stripped = preg_replace('/^\{#-.*?-#\}\R/s', '', $contents);
        if ($stripped === null) {
            self::fail('Could not strip the generated Twig banner.');
        }

        return $stripped;
    }

    /**
     * @param list<string> $extraArguments
     *
     * @return array<string, string> artefact name => contents
     */
    private static function runGenerator(string $outputDirectory, array $extraArguments = []): array
    {
        $process = self::process(array_merge(['--out-dir=' . $outputDirectory], $extraArguments));
        $process->run();

        if ($process->getExitCode() !== 0) {
            self::fail(sprintf(
                "The generator exited with %s.\nstdout: %s\nstderr: %s",
                var_export($process->getExitCode(), true),
                $process->getOutput(),
                $process->getErrorOutput(),
            ));
        }

        $artefacts = [];
        foreach (self::EXPECTED_ARTEFACTS as $name) {
            $path = $outputDirectory . '/' . $name;
            if (!is_file($path)) {
                self::fail(sprintf('The generator did not write "%s".', $name));
            }
            $contents = file_get_contents($path);
            if ($contents === false) {
                self::fail(sprintf('Generated artefact "%s" could not be read back.', $name));
            }
            $artefacts[$name] = $contents;
        }

        return $artefacts;
    }

    /**
     * @param list<string> $arguments
     */
    private static function process(array $arguments): Process
    {
        // PHP_BINARY keeps the child on the same interpreter as the suite, so the
        // test needs no host-specific php path and works in CI unchanged.
        $process = new Process(
            array_merge([PHP_BINARY, self::repoRoot() . '/' . self::ENTRY_POINT], $arguments),
            self::repoRoot(),
        );
        $process->setTimeout(120.0);

        return $process;
    }

    /**
     * @param array<string, string> $artefacts
     *
     * @return array<string, string>
     */
    private static function hashes(array $artefacts): array
    {
        $hashes = [];
        foreach ($artefacts as $name => $contents) {
            $hashes[$name] = hash('sha256', $contents);
        }

        return $hashes;
    }

    private static function makeTemporaryDirectory(string $suffix): string
    {
        $directory = sys_get_temp_dir() . '/thiqa-branding-' . $suffix . '-' . bin2hex(random_bytes(6));
        if (!mkdir($directory, 0o755, true) && !is_dir($directory)) {
            self::fail(sprintf('Could not create the temporary directory "%s".', $directory));
        }

        return strtr($directory, '\\', '/');
    }

    private static function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $entries = glob($directory . '/*');
        foreach ($entries === false ? [] : $entries as $entry) {
            if (is_dir($entry)) {
                self::removeDirectory($entry);
                continue;
            }
            unlink($entry);
        }

        rmdir($directory);
    }

    private static function repoRoot(): string
    {
        return strtr(dirname(__DIR__, self::REPO_ROOT_DEPTH), '\\', '/');
    }
}
