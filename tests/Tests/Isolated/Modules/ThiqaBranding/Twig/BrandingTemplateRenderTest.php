<?php

/**
 * Isolated render tests for the namespaced branding templates.
 *
 * Three properties are under test and none of them are provable by compilation alone:
 *
 *  1. the templates are reachable ONLY through the module's own Twig namespace
 *     (locked Q38 / CR-17) -- the environment here is built with a namespaced
 *     addPath(), never a prependPath() into the main namespace, and the test asserts
 *     that the unnamespaced core name still resolves to the core file;
 *  2. the SMART contract keeps its exact twelve-key shape, and the dark variant
 *     carries dark values with no light value anywhere in it;
 *  3. the login partial's accessible name is escaped for an attribute context. A
 *     hostile alt value is rendered and the output is checked for the absence of the
 *     characters that would break out of the attribute.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Twig;

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Modules\ThiqaBranding\Bootstrap;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Config\ModuleAutoloadTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

#[Group('isolated')]
#[Group('twig')]
final class BrandingTemplateRenderTest extends TestCase
{
    /** Mirrors Bootstrap::TWIG_NAMESPACE; asserted against it below rather than trusted. */
    private const NAMESPACE_NAME = 'oe-module-thiqa-branding';

    private const MODULE_TEMPLATES = '/interface/modules/custom_modules/oe-module-thiqa-branding/templates';

    /**
     * The SMART Style contract. Order matters: the assertion is on the exact key list,
     * so an added, removed or renamed key fails rather than passing silently.
     */
    private const SMART_KEYS = [
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
    ];

    use ModuleAutoloadTrait;

    private static ?Environment $twig = null;

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    protected function setUp(): void
    {
        $GLOBALS['fileroot'] ??= self::fileroot();
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['disable_translation'] = true;
    }

    /**
     * The guard is real -- if the two drift, every template in this file resolves against a
     * namespace the module does not register.
     *
     * It is read reflectively because PHPStan folds literal class constants and reports the
     * comparison as always true, which would hide a genuine drift check behind a suppressed
     * error. `constant()` was tried first and is folded just the same;
     * ReflectionClassConstant::getValue() returns mixed, so the comparison survives to
     * runtime where it can actually catch the drift.
     */
    public function testTheNamespaceConstantMatchesBootstrap(): void
    {
        $declared = (new \ReflectionClassConstant(Bootstrap::class, 'TWIG_NAMESPACE'))->getValue();

        self::assertSame($declared, self::NAMESPACE_NAME);
    }

    /**
     * Q38 guardrail expressed as a test: the module templates are reachable at
     * "@oe-module-thiqa-branding/...", and the bare core name still resolves to the
     * core file. If anyone swaps the namespaced addPath() for a prependPath(), the
     * second assertion starts failing.
     */
    public function testCoreNamesAreNotShadowedByTheModuleTemplates(): void
    {
        $loader = self::twig()->getLoader();

        self::assertTrue($loader->exists('@' . self::NAMESPACE_NAME . '/api/smart/smart-style_light.json.twig'));
        self::assertTrue($loader->exists('api/smart/smart-style_light.json.twig'));

        self::assertSame(
            self::fileroot() . '/templates/api/smart/smart-style_light.json.twig',
            str_replace('\\', '/', $loader->getSourceContext('api/smart/smart-style_light.json.twig')->getPath()),
        );
    }

    #[DataProvider('smartTemplateProvider')]
    public function testSmartTemplateEmitsExactlyTheTwelveContractKeys(string $template): void
    {
        $decoded = $this->renderSmart($template);

        self::assertSame(self::SMART_KEYS, array_keys($decoded));
    }

    #[DataProvider('smartTemplateProvider')]
    public function testSmartTemplateInterpolatesTheControllerSuppliedLogo(string $template): void
    {
        $decoded = $this->renderSmart($template);

        self::assertSame('https://thiqa.example/openemr/logo.svg', $decoded['logo_primary']);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function smartTemplateProvider(): array
    {
        return [
            'light' => ['smart-style_light.json.twig'],
            'dark' => ['smart-style_dark.json.twig'],
        ];
    }

    public function testDarkSmartTemplateCarriesTheDarkPalette(): void
    {
        $decoded = $this->renderSmart('smart-style_dark.json.twig');

        self::assertSame('#0B1220', $decoded['color_background']);
        self::assertSame('#F5F6F8', $decoded['color_text']);
        self::assertSame('#F29088', $decoded['color_error']);
        self::assertSame('#8FC1EE', $decoded['color_highlight']);
        self::assertSame('#8FD1A6', $decoded['color_success']);
        self::assertSame('rgba(0, 0, 0, 0.6)', $decoded['color_modal_backdrop']);
    }

    /**
     * A dark contract that leaked a single light surface would be worse than no dark
     * contract at all, so the whole rendered document is searched for every light value.
     */
    #[DataProvider('lightOnlyValueProvider')]
    public function testDarkSmartTemplateContainsNoLightValue(string $lightValue): void
    {
        $rendered = $this->render('api/smart/smart-style_dark.json.twig', self::smartContext());

        self::assertStringNotContainsStringIgnoringCase($lightValue, $rendered);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function lightOnlyValueProvider(): array
    {
        return [
            'light background' => ['#FAFAF8'],
            'light text' => ['#0B1B4D'],
            'light error' => ['#8E271D'],
            'light highlight' => ['#3E7FBD'],
            'light success' => ['#2F6B45'],
            'light modal backdrop' => ['rgba(11, 27, 77, 0.6)'],
        ];
    }

    public function testLightSmartTemplateCarriesTheLightPalette(): void
    {
        $decoded = $this->renderSmart('smart-style_light.json.twig');

        self::assertSame('#FAFAF8', $decoded['color_background']);
        self::assertSame('#0B1B4D', $decoded['color_text']);
        self::assertSame('rgba(11, 27, 77, 0.6)', $decoded['color_modal_backdrop']);
    }

    /** The comment banner must not push whitespace in front of the JSON document. */
    #[DataProvider('smartTemplateProvider')]
    public function testSmartTemplateStartsAtTheOpeningBrace(string $template): void
    {
        $rendered = $this->render('api/smart/' . $template, self::smartContext());

        self::assertStringStartsWith('{', $rendered);
    }

    public function testLoginPartialEmitsANonEmptyAltText(): void
    {
        $rendered = $this->renderCore(
            'login/partials/html/primary_logo.html.twig',
            self::loginContext(['primaryLogoAlt' => 'Thiqa Care']),
        );

        self::assertStringContainsString('alt="Thiqa Care"', $rendered);
        self::assertStringNotContainsString('alt=""', $rendered);
    }

    public function testLoginPartialEmitsTheArabicAltTextUnmangled(): void
    {
        $rendered = $this->renderCore(
            'login/partials/html/primary_logo.html.twig',
            self::loginContext(['primaryLogoAlt' => 'ثقة كير']),
        );

        self::assertStringContainsString('alt="ثقة كير"', $rendered);
    }

    /**
     * The hostile value carries every character that can escape a double-quoted
     * attribute: the closing quote itself, a single quote, an angle bracket pair and a
     * bare ampersand. After |attr (htmlspecialchars with ENT_QUOTES) none of them may
     * survive as markup.
     */
    public function testLoginPartialEscapesAHostileAltText(): void
    {
        $hostile = 'Thiqa" onerror="alert(1)" x="<script>alert(2)</script> & \'q\'';

        $rendered = $this->renderCore(
            'login/partials/html/primary_logo.html.twig',
            self::loginContext(['primaryLogoAlt' => $hostile]),
        );

        // Nothing that could terminate the attribute or open a tag survives.
        //
        // Note the bare text `onerror=` DOES survive, and must: escaping neutralises
        // the quotes around it, not the letters themselves. The browser sees one
        // attribute whose value happens to contain that text, which is inert. What
        // must never appear is the live form `onerror="` with an unescaped quote,
        // because only an unescaped quote can close alt= and start a new attribute.
        self::assertStringNotContainsString('onerror="', $rendered);
        self::assertStringNotContainsString("onerror='", $rendered);
        self::assertStringNotContainsString('<script', $rendered);
        self::assertStringNotContainsString('</script', $rendered);
        self::assertStringNotContainsString($hostile, $rendered);

        // The value is present, but only in its escaped form.
        self::assertStringContainsString('&quot;', $rendered);
        self::assertStringContainsString('&lt;script&gt;', $rendered);
        self::assertStringContainsString('&#039;q&#039;', $rendered);
        self::assertStringContainsString(
            'alt="' . htmlspecialchars($hostile, ENT_QUOTES) . '"',
            $rendered,
        );

        // The img tag still has exactly the attributes it should, and no injected one.
        self::assertSame(1, substr_count($rendered, '<img '));
    }

    public function testLoginPartialFallsBackToADecorativeAltWhenTheVariableIsAbsent(): void
    {
        $rendered = $this->renderCore(
            'login/partials/html/primary_logo.html.twig',
            self::loginContext(),
        );

        self::assertStringContainsString('alt=""', $rendered);
    }

    public function testLoginPartialEscapesTheSecondaryAltText(): void
    {
        $rendered = $this->renderCore(
            'login/partials/html/primary_logo.html.twig',
            self::loginContext([
                'displaySecondaryLogo' => true,
                'secondaryLogo' => '/sites/default/images/logo_2.svg',
                'secondaryLogoAlt' => 'Ministry of Health "MOH" <b>',
            ]),
        );

        self::assertStringContainsString('alt="Ministry of Health &quot;MOH&quot; &lt;b&gt;"', $rendered);
        self::assertStringNotContainsString('<b>', $rendered);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function render(string $relativeName, array $context): string
    {
        return self::twig()->render('@' . self::NAMESPACE_NAME . '/' . $relativeName, $context);
    }

    /**
     * Render a CORE template by its bare name -- no module namespace.
     *
     * The login logo partial is reached from five core parents through
     * {% include "login/partials/html/primary_logo.html.twig" %}. An include resolves by
     * name through the loader and cannot be intercepted by the TemplatePageEvent name
     * rewrite, so the file core renders is the only one that can ever reach a user. The
     * alt-text tests therefore drive the core template directly; pointing them at a module
     * copy would leave them green while the real login page regressed.
     *
     * @param array<string, mixed> $context
     */
    private function renderCore(string $relativeName, array $context): string
    {
        return self::twig()->render($relativeName, $context);
    }

    /**
     * @return array<string, mixed>
     */
    private function renderSmart(string $template): array
    {
        $rendered = $this->render('api/smart/' . $template, self::smartContext());

        $decoded = json_decode($rendered, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            self::fail('SMART style template did not render a JSON object: ' . $template);
        }

        // json_decode gives array<mixed, mixed>. Rebuilding key by key lets PHPStan infer
        // array<string, mixed> from the is_string() guard instead of being told it with an
        // @var, and it turns the declared type into something actually enforced: a numeric
        // key means the template emitted a JSON array rather than an object.
        $object = [];
        foreach ($decoded as $key => $value) {
            if (!is_string($key)) {
                self::fail('SMART style template emitted a non-object JSON value: ' . $template);
            }

            $object[$key] = $value;
        }

        return $object;
    }

    /**
     * @return array{logo: array{primary: string}}
     */
    private static function smartContext(): array
    {
        return ['logo' => ['primary' => 'https://thiqa.example/openemr/logo.svg']];
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private static function loginContext(array $overrides = []): array
    {
        return array_merge([
            'displayPrimaryLogo' => true,
            'primaryLogo' => '/sites/default/images/logo_1.svg',
            'primaryLogoWidth' => 'w-75',
            'logoPosition' => 'flex-column',
            'displaySecondaryLogo' => false,
            'showTitleOnLogin' => false,
        ], $overrides);
    }

    private static function twig(): Environment
    {
        if (self::$twig instanceof Environment) {
            return self::$twig;
        }

        $GLOBALS['fileroot'] ??= self::fileroot();
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['disable_translation'] = true;

        $twig = (new TwigContainer())->getTwig();

        $loader = $twig->getLoader();
        if (!$loader instanceof FilesystemLoader) {
            self::fail('Expected a FilesystemLoader from TwigContainer.');
        }

        // Namespaced addPath() -- the locked Q38 pattern. Never prependPath(), and never
        // an addPath() without the namespace argument: either would shadow core.
        $loader->addPath(self::fileroot() . self::MODULE_TEMPLATES, self::NAMESPACE_NAME);

        self::$twig = $twig;

        return $twig;
    }

    private static function fileroot(): string
    {
        return str_replace('\\', '/', dirname(__DIR__, 6));
    }
}
