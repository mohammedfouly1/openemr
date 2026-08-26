<?php

/**
 * Isolated tests for the branding front door: token composition and Tier 2 delivery.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Service;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandAssetResolver;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Service\BrandingService;
use OpenEMR\Modules\SkyEagleBranding\Service\BrandingServiceInterface;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeResolver;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenKey;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSetParser;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Asset\FakeLogoService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/*
 * The module's PSR-4 prefix is not in the root autoloader, so it is registered here.
 * This uses the file-scope shim rather than ModuleAutoloadTrait because a data provider
 * that names a module enum runs before setUpBeforeClass() would have had a chance to run.
 */
require_once __DIR__ . '/../Token/token_autoloader.php';

/**
 * The service is exercised against the real brand/tokens/thiqa-tokens.json, because the
 * property under test is that the shipped Tier 1 palette composes correctly — a fixture
 * copy would let the product document drift without failing anything.
 *
 * The empty-overlay path is asserted first and hardest: it is the default state for every
 * tenant, and the plan requires it to cost nothing extra (no merge, no second stylesheet,
 * no <link>).
 */
final class BrandingServiceTest extends TestCase
{
    private const TOKEN_STYLESHEET_URL =
        '/openemr/interface/modules/custom_modules/oe-module-skyeagle-branding/public/branding-tokens.php';

    /** A valid Tier 2 override: an accessible link colour on the light background. */
    private const OVERLAY_LIGHT = '{"link.default":"#1F5C99"}';

    /**
     * The concrete service must expose nothing beyond the published interface.
     *
     * This replaced an assertInstanceOf() against the interface, which PHP already
     * guarantees at compile time -- PHPStan flagged it as always true, and it was. The
     * check that actually carries weight is the reverse one: that no extra public method
     * has appeared on the concrete class, because the moment one does, callers can bind to
     * BrandingService instead of BrandingServiceInterface and the seam stops being
     * substitutable.
     */
    public function testTheServiceSatisfiesThePublishedContract(): void
    {
        $beyondContract = array_diff(
            self::publicMethodsOf(BrandingService::class),
            self::publicMethodsOf(BrandingServiceInterface::class),
        );

        $this->assertSame(
            [],
            array_values($beyondContract),
            'BrandingService exposes public methods absent from BrandingServiceInterface: '
            . implode(', ', $beyondContract),
        );
    }

    /**
     * Public method names of a class or interface, excluding the constructor.
     *
     * A named method rather than a closure so `class-string` can be declared: ReflectionClass
     * requires it, and a closure parameter typed as plain `string` fails PHPStan level 10.
     *
     * @param class-string $class
     *
     * @return list<string>
     */
    private static function publicMethodsOf(string $class): array
    {
        return array_values(array_diff(
            array_map(
                static fn (\ReflectionMethod $method): string => $method->getName(),
                (new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC),
            ),
            ['__construct'],
        ));
    }

    /** The default state: no tenant overlay, so no second stylesheet is ever requested. */
    public function testAnEmptyOverlayEmitsNoTokenStylesheet(): void
    {
        $service = $this->service();

        $this->assertFalse($service->config()->hasTenantOverlay());
        $this->assertNull($service->tokenStylesheetUrl());
    }

    /** Even a materialised tenant with no overlay stays on the shared immutable bundle. */
    public function testARevisionAloneDoesNotProduceATokenStylesheet(): void
    {
        $service = $this->service([BrandingGlobalKey::Revision->value => '11']);

        $this->assertSame(11, $service->revision()->value);
        $this->assertNull($service->tokenStylesheetUrl());
    }

    /** A populated overlay produces a revision-keyed URL for the module endpoint. */
    public function testAPopulatedOverlayProducesARevisionKeyedStylesheetUrl(): void
    {
        $service = $this->service([
            BrandingGlobalKey::TokensLight->value => self::OVERLAY_LIGHT,
            BrandingGlobalKey::Revision->value => '11',
        ]);

        $this->assertSame(self::TOKEN_STYLESHEET_URL . '?rev=11', $service->tokenStylesheetUrl());
    }

    /** Tier 1 is loaded from the shipped document and covers both variants. */
    #[DataProvider('variantProvider')]
    public function testTierOneIsLoadedForEveryVariant(ThemeVariant $variant): void
    {
        $tokens = $this->service()->tokens($variant);

        $this->assertSame($variant, $tokens->variant);
        $this->assertGreaterThan(30, $tokens->count());
        $this->assertTrue($tokens->has(TokenKey::BrandNavy));
        $this->assertTrue($tokens->has(TokenKey::LinkDefault));
    }

    /** An empty overlay returns the Tier 1 set unchanged — the fast path. */
    public function testAnEmptyOverlayLeavesTierOneUntouched(): void
    {
        $service = $this->service();
        $productPalette = (new TokenSetParser())->parseDocument($this->tokenDocument());

        $this->assertEquals($productPalette['light'], $service->tokens(ThemeVariant::Light));
        $this->assertEquals($productPalette['dark'], $service->tokens(ThemeVariant::Dark));
    }

    /** A populated overlay is merged over Tier 1, leaving every untouched key alone. */
    public function testAPopulatedOverlayIsMergedOverTierOne(): void
    {
        $service = $this->service([BrandingGlobalKey::TokensLight->value => self::OVERLAY_LIGHT]);
        $productPalette = (new TokenSetParser())->parseDocument($this->tokenDocument());

        $light = $service->tokens(ThemeVariant::Light);

        $this->assertSame('#1F5C99', $light->valueOf(TokenKey::LinkDefault)?->value);
        $this->assertCount($productPalette['light']->count(), $light);
        $this->assertSame(
            $productPalette['light']->valueOf(TokenKey::BrandNavy)?->value,
            $light->valueOf(TokenKey::BrandNavy)?->value,
        );
        // The dark variant has its own overlay slot and this tenant left it empty.
        $this->assertEquals($productPalette['dark'], $service->tokens(ThemeVariant::Dark));
    }

    /**
     * Defence in depth at the last point before rendering: a non-overridable key that
     * somehow reached globals is dropped rather than applied.
     */
    #[DataProvider('refusedOverlayProvider')]
    public function testKeysOutsideTheTenantOverridableSetAreDropped(string $overlayJson, TokenKey $key): void
    {
        $service = $this->service([BrandingGlobalKey::TokensLight->value => $overlayJson]);
        $productPalette = (new TokenSetParser())->parseDocument($this->tokenDocument());

        $this->assertSame(
            $productPalette['light']->valueOf($key)?->value,
            $service->tokens(ThemeVariant::Light)->valueOf($key)?->value,
        );
    }

    /** The document is read at most once per request: the composed set is memoised. */
    public function testTheComposedPaletteIsMemoised(): void
    {
        $service = $this->service([BrandingGlobalKey::TokensLight->value => self::OVERLAY_LIGHT]);

        $this->assertSame($service->tokens(ThemeVariant::Light), $service->tokens(ThemeVariant::Light));
        $this->assertSame($service->tokens(ThemeVariant::Dark), $service->tokens(ThemeVariant::Dark));
    }

    /**
     * A missing Tier 1 artefact is a packaging fault, not a reason to 500 the login page:
     * the palette degrades to empty and the compiled stylesheets stay in charge.
     */
    public function testAMissingTokenDocumentDegradesToAnEmptyPalette(): void
    {
        $service = $this->service([], __DIR__ . '/there-is-no-such-token-document.json');

        $this->assertCount(0, $service->tokens(ThemeVariant::Light));
        $this->assertCount(0, $service->tokens(ThemeVariant::Dark));
        $this->assertNull($service->tokens(ThemeVariant::Light)->valueOf(TokenKey::BrandNavy));
    }

    public function testProductNameSelectsTheConfiguredLanguage(): void
    {
        $service = $this->service([
            BrandingGlobalKey::OpenemrName->value => 'Thiqa',
            BrandingGlobalKey::ProductNameArabic->value => 'ثقة',
        ]);

        $this->assertSame('Thiqa', $service->productName());
        $this->assertSame('ثقة', $service->productName(true));
    }

    public function testTaglineIsSuppressedWhenTheFlagIsOff(): void
    {
        $shown = $this->service();
        $hidden = $this->service([BrandingGlobalKey::ShowTaglineOnLogin->value => '0']);

        $this->assertSame('Better care begins here.', $shown->tagline());
        $this->assertNull($hidden->tagline());
    }

    public function testThemeAndDirectionComeFromTheThemeResolver(): void
    {
        $service = $this->service([
            BrandingGlobalKey::CssHeader->value => '/openemr/public/themes/rtl_style_dark.css?v=abc123',
        ]);

        $this->assertSame(ThemeVariant::Dark, $service->themeVariant());
        $this->assertTrue($service->isRtl());
    }

    /** The unconfigured tenant renders Saudi Light, never an upstream default. */
    public function testAnUnconfiguredTenantRendersSaudiLightLeftToRight(): void
    {
        $service = $this->service();

        $this->assertSame(ThemeVariant::Light, $service->themeVariant());
        $this->assertFalse($service->isRtl());
    }

    /** Logo resolution is delegated, and the composed URL keeps both cache identifiers. */
    public function testLogoResolutionIsDelegatedAndRevisionStamped(): void
    {
        $corePath = '/openemr/sites/default/images/logos/core/login/primary/logo.png?t=1700000000';
        $service = $this->service([BrandingGlobalKey::Revision->value => '5'], null, $corePath);

        $asset = $service->logo(LogoSlot::CoreLoginPrimary);

        $this->assertSame($corePath . '&rev=5', $asset->url());
        $this->assertSame(5, $asset->revision->value);
    }

    public function testConfigIsTheSameImmutableSnapshotForEveryCall(): void
    {
        $service = $this->service();

        $this->assertSame($service->config(), $service->config());
        $this->assertSame($service->revision(), $service->revision());
    }

    /**
     * @return list<array{ThemeVariant}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function variantProvider(): array
    {
        return array_map(static fn (ThemeVariant $variant): array => [$variant], ThemeVariant::cases());
    }

    /**
     * @return array<string, array{string, TokenKey}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedOverlayProvider(): array
    {
        return [
            'brand identity' => ['{"brand.navy":"#123456"}', TokenKey::BrandNavy],
            'clinical semantic colour' => ['{"semantic.critical.text":"#123456"}', TokenKey::SemanticCriticalText],
            'structural surface' => ['{"background":"#123456"}', TokenKey::Background],
            'body text' => ['{"text.primary":"#123456"}', TokenKey::TextPrimary],
        ];
    }

    private function tokenDocument(): string
    {
        $contents = file_get_contents($this->tokenDocumentPath());
        $this->assertIsString($contents);

        return $contents;
    }

    private function tokenDocumentPath(): string
    {
        return dirname(__DIR__, 6) . '/' . BrandingService::TOKEN_DOCUMENT_RELATIVE_PATH;
    }

    /**
     * @param array<string, string> $globals
     */
    private function service(
        array $globals = [],
        ?string $tokenDocumentPath = null,
        string $logoPath = '',
    ): BrandingService {
        return new BrandingService(
            new BrandingConfigFactory(new OEGlobalsBag($globals)),
            new BrandAssetResolver(new FakeLogoService([], $logoPath)),
            new ThemeResolver(),
            new TokenSetParser(),
            $tokenDocumentPath ?? $this->tokenDocumentPath(),
            self::TOKEN_STYLESHEET_URL,
        );
    }
}
