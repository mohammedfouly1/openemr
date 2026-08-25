<?php

/**
 * Isolated tests for css_header to ThemeVariant resolution and RTL detection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Theme;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingConfig;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeResolver;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/*
 * The module's PSR-4 prefix is not in the root autoloader, so it is registered here.
 * This uses the file-scope shim rather than ModuleAutoloadTrait because a data provider
 * that names a module enum runs before setUpBeforeClass() would have had a chance to run.
 */
require_once __DIR__ . '/../Token/token_autoloader.php';

/**
 * The values in the providers are the real shapes interface/globals.php produces, not
 * tidy filenames: a full web path with a `?v=<v_js_includes>` cache buster, optionally
 * with the `rtl_` and `compact_` prefixes core prepends.
 *
 * The most important case is the last one in each provider: anything unrecognised must
 * resolve to Light. Locked decision Q77 allows exactly two variants, and the fallback has
 * to be the product's own light theme — degrading to an upstream-branded theme would put
 * OpenEMR branding in front of a user because of a typo in a global.
 */
final class ThemeResolverTest extends TestCase
{
    #[DataProvider('stylesheetProvider')]
    public function testStylesheetResolvesToItsVariant(string $cssHeader, ThemeVariant $expected): void
    {
        $this->assertSame($expected, (new ThemeResolver())->resolveStylesheet($cssHeader));
    }

    #[DataProvider('rtlProvider')]
    public function testDirectionIsReadFromTheStylesheetName(string $cssHeader, bool $expected): void
    {
        $this->assertSame($expected, (new ThemeResolver())->isRtlStylesheet($cssHeader));
    }

    public function testConfigIsResolvedThroughTheCssHeaderGlobal(): void
    {
        $resolver = new ThemeResolver();
        $config = $this->config([
            BrandingGlobalKey::CssHeader->value => '/openemr/public/themes/rtl_style_dark.css?v=abc123',
            BrandingGlobalKey::PortalCssHeader->value => '/openemr/public/themes/style_light.css?v=abc123',
        ]);

        $this->assertSame(ThemeVariant::Dark, $resolver->resolve($config));
        $this->assertTrue($resolver->isRtl($config));
        $this->assertSame(ThemeVariant::Light, $resolver->resolvePortal($config));
        $this->assertFalse($resolver->isPortalRtl($config));
    }

    /**
     * With nothing configured the factory supplies style_light.css, so an unconfigured
     * tenant renders Saudi Light left-to-right.
     */
    public function testAnUnconfiguredTenantRendersSaudiLight(): void
    {
        $resolver = new ThemeResolver();
        $config = $this->config([]);

        $this->assertSame(ThemeVariant::Light, $resolver->resolve($config));
        $this->assertFalse($resolver->isRtl($config));
    }

    /** Both variants round-trip through their own stylesheet name. */
    #[DataProvider('variantProvider')]
    public function testEveryVariantRoundTripsThroughItsStylesheet(ThemeVariant $variant): void
    {
        $resolver = new ThemeResolver();

        $this->assertSame($variant, $resolver->resolveStylesheet($variant->stylesheet()));
        $this->assertSame($variant, $resolver->resolveStylesheet('rtl_' . $variant->stylesheet()));
        $this->assertSame($variant, $resolver->resolveStylesheet('compact_' . $variant->stylesheet()));
        $this->assertSame($variant, $resolver->resolveStylesheet('rtl_compact_' . $variant->stylesheet()));
    }

    /**
     * @return array<string, array{string, ThemeVariant}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function stylesheetProvider(): array
    {
        return [
            'bare light filename' => ['style_light.css', ThemeVariant::Light],
            'bare dark filename' => ['style_dark.css', ThemeVariant::Dark],
            'light web path with version query' => [
                '/openemr/public/themes/style_light.css?v=8f2c1d',
                ThemeVariant::Light,
            ],
            'dark web path with version query' => [
                '/openemr/public/themes/style_dark.css?v=8f2c1d',
                ThemeVariant::Dark,
            ],
            'rtl dark' => ['/openemr/public/themes/rtl_style_dark.css?v=8f2c1d', ThemeVariant::Dark],
            'rtl light' => ['/openemr/public/themes/rtl_style_light.css?v=8f2c1d', ThemeVariant::Light],
            'compact dark' => ['/openemr/public/themes/compact_style_dark.css?v=8f2c1d', ThemeVariant::Dark],
            'compact light' => ['/openemr/public/themes/compact_style_light.css?v=8f2c1d', ThemeVariant::Light],
            'rtl compact dark' => [
                '/openemr/public/themes/rtl_compact_style_dark.css?v=8f2c1d',
                ThemeVariant::Dark,
            ],
            'absolute url with fragment' => ['https://example.test/themes/style_dark.css?v=1#top', ThemeVariant::Dark],
            'windows separators' => ['C:\\openemr\\public\\themes\\style_dark.css', ThemeVariant::Dark],
            'surrounding whitespace' => ["  style_dark.css  ", ThemeVariant::Dark],
            'mixed case' => ['/openemr/public/themes/Style_Dark.CSS?v=1', ThemeVariant::Dark],
            'upstream legacy theme falls back to light' => ['style_manila.css', ThemeVariant::Light],
            'unknown value falls back to light' => ['not-a-theme', ThemeVariant::Light],
            'empty value falls back to light' => ['', ThemeVariant::Light],
            'query only falls back to light' => ['?v=8f2c1d', ThemeVariant::Light],
            'dark as a directory name does not count' => [
                '/openemr/public/themes/style_dark.css/style_manila.css?v=1',
                ThemeVariant::Light,
            ],
        ];
    }

    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rtlProvider(): array
    {
        return [
            'ltr light' => ['/openemr/public/themes/style_light.css?v=1', false],
            'ltr dark' => ['/openemr/public/themes/style_dark.css?v=1', false],
            'rtl light' => ['/openemr/public/themes/rtl_style_light.css?v=1', true],
            'rtl dark' => ['/openemr/public/themes/rtl_style_dark.css?v=1', true],
            'rtl compact' => ['/openemr/public/themes/rtl_compact_style_dark.css?v=1', true],
            'compact is not rtl' => ['/openemr/public/themes/compact_style_dark.css?v=1', false],
            'rtl in a directory name is not the theme' => ['/openemr/rtl_themes/style_dark.css?v=1', false],
            'empty' => ['', false],
        ];
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
     * @param array<string, string> $globals
     */
    private function config(array $globals): BrandingConfig
    {
        return (new BrandingConfigFactory(new OEGlobalsBag($globals)))->create();
    }
}
