<?php

/**
 * Isolated tests for logo slot resolution and the additive cache-key rule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Asset;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandAsset;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandAssetResolver;
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfig;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingGlobalKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/*
 * The module's PSR-4 prefix is not in the root autoloader, so it is registered here.
 * This uses the file-scope shim rather than ModuleAutoloadTrait because a data provider
 * that names a module enum runs before setUpBeforeClass() would have had a chance to run.
 */
require_once __DIR__ . '/../Token/token_autoloader.php';

/**
 * The load-bearing property proved here is plan §3.8.1's append-never-replace rule: core's
 * `?t=<mtime>` survives untouched and `&rev=<n>` is added after it, in that fixed order.
 * A test that only asserted "the URL contains rev" would pass even if the mtime had been
 * stripped, so every case asserts the whole composed string.
 */
final class BrandAssetResolverTest extends TestCase
{
    /** What core's LogoService returns for a site-scoped logo, mtime buster included. */
    private const CORE_PATH = '/openemr/sites/default/images/logos/core/login/primary/logo.png?t=1700000000';

    /**
     * Every one of the nine slots resolves, is asked for using its own type fragment and
     * filename pattern, and comes back revision-stamped.
     */
    #[DataProvider('slotProvider')]
    public function testEverySlotResolves(LogoSlot $slot): void
    {
        $corePath = '/openemr/sites/default/images/logos/' . $slot->value . '/logo.png?t=1700000000';
        $logoService = new FakeLogoService([$slot->value => $corePath]);
        $asset = $this->resolverWith($logoService)->resolve($slot, $this->config(['revision' => '7']));

        $this->assertSame([['type' => $slot->value, 'filename' => $slot->filenamePattern()]], $logoService->calls);
        $this->assertSame($slot, $asset->slot);
        $this->assertSame(7, $asset->revision->value);
        $this->assertTrue($asset->isResolved());
        $this->assertSame($corePath . '&rev=7', $asset->url());
        $this->assertSame($slot->expectedDimensions(), $asset->expectedDimensions());
    }

    /**
     * The rule stated as its own test: the mtime buster is PRESERVED, not replaced.
     */
    public function testExistingTimestampCacheBusterIsPreservedNotReplaced(): void
    {
        $asset = $this->resolve(self::CORE_PATH, '7');

        $this->assertSame(self::CORE_PATH . '&rev=7', $asset->url());
        $this->assertStringContainsString('?t=1700000000', $asset->url());
        $this->assertStringStartsWith(self::CORE_PATH, $asset->url());
        $this->assertSame(1, substr_count($asset->url(), 't=1700000000'));
        $this->assertSame(1, substr_count($asset->url(), 'rev='));
    }

    /** Fixed parameter order — `?t=` first, then `&rev=` — so proxies do not fragment. */
    public function testParameterOrderIsTimestampThenRevision(): void
    {
        $url = $this->resolve(self::CORE_PATH, '3')->url();

        $timestamp = strpos($url, 't=');
        $revision = strpos($url, 'rev=');

        $this->assertIsInt($timestamp);
        $this->assertIsInt($revision);
        $this->assertLessThan($revision, $timestamp);
    }

    /** A path with no query string still gets keyed, using `?rev=` rather than `&rev=`. */
    public function testPathWithoutAQueryStringOpensOneWithRev(): void
    {
        $asset = $this->resolve('/openemr/public/images/logo.svg', '12');

        $this->assertSame('/openemr/public/images/logo.svg?rev=12', $asset->url());
    }

    /** Revision zero is a real state ("never materialised") and is still stamped. */
    public function testRevisionZeroIsStillStamped(): void
    {
        $asset = $this->resolve(self::CORE_PATH, '0');

        $this->assertSame(self::CORE_PATH . '&rev=0', $asset->url());
        $this->assertFalse($asset->revision->isMaterialised());
    }

    /** An already-stamped path is left alone rather than gaining a second rev parameter. */
    #[DataProvider('alreadyStampedProvider')]
    public function testAnAlreadyStampedPathIsNotStampedTwice(string $corePath): void
    {
        $asset = $this->resolve($corePath, '9');

        $this->assertSame($corePath, $asset->url());
        $this->assertSame(1, substr_count($asset->url(), 'rev='));
    }

    /**
     * A slot core cannot satisfy yields an empty URL — never a substituted path from
     * another scope and never an upstream default image.
     */
    public function testMissingSlotYieldsAnEmptyUrlAndNoFallbackPath(): void
    {
        $asset = $this->resolverWith(new FakeLogoService())->resolve(
            LogoSlot::CoreLoginPrimary,
            $this->config(['revision' => '4']),
        );

        $this->assertFalse($asset->isResolved());
        $this->assertSame('', $asset->url());
        $this->assertSame('', $asset->altText);
        $this->assertTrue($asset->isDecorative());
        $this->assertSame(4, $asset->revision->value);
        $this->assertTrue($asset->equals(BrandAsset::missing(LogoSlot::CoreLoginPrimary, $asset->revision)));
    }

    /** Whitespace around a core path is not a resolution: it is trimmed to empty. */
    public function testWhitespaceOnlyCorePathIsTreatedAsMissing(): void
    {
        $asset = $this->resolve("   \n", '2');

        $this->assertFalse($asset->isResolved());
        $this->assertSame('', $asset->url());
    }

    /** Alt text is built from the configured product names, in both languages. */
    public function testAltTextUsesTheConfiguredProductNames(): void
    {
        $logoService = new FakeLogoService([], self::CORE_PATH);
        $resolver = $this->resolverWith($logoService);
        $config = $this->config([
            BrandingGlobalKey::OpenemrName->value => 'Thiqa',
            BrandingGlobalKey::ProductNameArabic->value => 'ثقة',
        ]);

        $primary = $resolver->resolve(LogoSlot::CoreLoginPrimary, $config);
        $secondary = $resolver->resolve(LogoSlot::CoreLoginSecondary, $config);
        $menu = $resolver->resolve(LogoSlot::CoreMenuPrimary, $config);

        $this->assertSame('Thiqa logo', $primary->alt());
        $this->assertSame('شعار ثقة', $primary->alt(true));
        $this->assertSame('Thiqa secondary logo', $secondary->alt());
        $this->assertSame('Thiqa home', $menu->alt());
        $this->assertFalse($primary->isDecorative());
    }

    /**
     * A blank Arabic product name resolves to the Thiqa default rather than to the
     * configured Latin wordmark — the factory's documented-default rule reaches the alt
     * attribute intact, so an RTL screen reader is never handed a Latin string.
     */
    public function testBlankArabicProductNameResolvesToTheProductDefault(): void
    {
        $logoService = new FakeLogoService([], self::CORE_PATH);
        $config = $this->config([
            BrandingGlobalKey::OpenemrName->value => 'Thiqa Enterprise',
            BrandingGlobalKey::ProductNameArabic->value => '   ',
        ]);

        $asset = $this->resolverWith($logoService)->resolve(LogoSlot::PortalLoginPrimary, $config);

        $this->assertSame('شعار ثقة', $asset->alt(true));
    }

    /** The favicon is decorative: assistive technology never announces it. */
    public function testTheFaviconIsDecorative(): void
    {
        $logoService = new FakeLogoService([
            LogoSlot::CoreFavicon->value => '/openemr/sites/default/images/logos/core/favicon/favicon.ico?t=1',
        ]);

        $asset = $this->resolverWith($logoService)->resolve(LogoSlot::CoreFavicon, $this->config());

        $this->assertTrue($asset->isResolved());
        $this->assertTrue($asset->isDecorative());
        $this->assertSame('', $asset->alt());
        $this->assertSame('', $asset->alt(true));
        $this->assertSame('favicon.ico', $logoService->calls[0]['filename']);
    }

    /** resolveAll() covers the closed set exactly once, in declaration order. */
    public function testResolveAllCoversEverySlotOnce(): void
    {
        $logoService = new FakeLogoService([], self::CORE_PATH);

        $assets = $this->resolverWith($logoService)->resolveAll($this->config());

        $this->assertCount(count(LogoSlot::cases()), $assets);
        $this->assertSame(
            array_map(static fn (LogoSlot $slot): string => $slot->value, LogoSlot::cases()),
            array_map(static fn (BrandAsset $asset): string => $asset->slot->value, $assets),
        );
    }

    /**
     * @return list<array{LogoSlot}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function slotProvider(): array
    {
        return array_map(static fn (LogoSlot $slot): array => [$slot], LogoSlot::cases());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function alreadyStampedProvider(): array
    {
        return [
            'rev only' => ['/openemr/public/images/logo.svg?rev=9'],
            'rev after mtime' => ['/openemr/public/images/logo.svg?t=1700000000&rev=9'],
            'rev before mtime' => ['/openemr/public/images/logo.svg?rev=9&t=1700000000'],
        ];
    }

    private function resolve(string $corePath, string $revision): BrandAsset
    {
        return $this->resolverWith(new FakeLogoService([], $corePath))->resolve(
            LogoSlot::CoreLoginPrimary,
            $this->config(['revision' => $revision]),
        );
    }

    private function resolverWith(FakeLogoService $logoService): BrandAssetResolver
    {
        return new BrandAssetResolver($logoService);
    }

    /**
     * @param array<string, string> $globals the literal key 'revision' is an alias for the
     *                                       branding revision global, to keep cases short
     */
    private function config(array $globals = []): BrandingConfig
    {
        if (array_key_exists('revision', $globals)) {
            $globals[BrandingGlobalKey::Revision->value] = $globals['revision'];
            unset($globals['revision']);
        }

        return (new BrandingConfigFactory(new OEGlobalsBag($globals)))->create();
    }
}
