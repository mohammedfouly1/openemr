<?php

/**
 * Isolated tests for the variant-aware logo override listener.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Listener;

use OpenEMR\Events\Services\LogoFilterEvent;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\Listener\LogoOverrideListener;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config\ModuleAutoloadTrait;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation\TemporaryTreeTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * A real temporary directory is used rather than a virtual filesystem, because the
 * property under test is "the override is only offered when the file is actually there" --
 * core discards a path that does not resolve, and discarding does not mean "keep the
 * original", it means the logo vanishes.
 *
 * The other half of the suite is about refusing to set a path core would reject. That is
 * the case most likely to be got wrong, and the one whose failure mode is worst: a web
 * path outside interface/modules/ makes LogoService return the empty string, so a "helpful"
 * override would silently delete the tenant's logo from every page.
 */
final class LogoOverrideListenerTest extends TestCase
{
    use ModuleAutoloadTrait;
    use TemporaryTreeTrait;

    /** A web path that satisfies ModulesApplication::filterSafeLocalModuleFiles(). */
    private const SAFE_WEB_PATH =
        '/openemr/interface/modules/custom_modules/oe-module-skyeagle-branding/public/logos/dark';

    /** The path core would return, and which must survive untouched on every no-op. */
    private const CORE_WEB_PATH = '/openemr/sites/default/images/logos/core/menu/primary/logo.png?t=1700000000';

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    protected function setUp(): void
    {
        $this->makeTree();
    }

    protected function tearDown(): void
    {
        $this->removeTree();
    }

    /** Nothing shipped for the slot: the event leaves exactly as core built it. */
    public function testNoOverrideIsAppliedWhenNoVariantAssetIsShipped(): void
    {
        $branding = $this->darkTenant();
        $event = $this->event(LogoSlot::CoreMenuPrimary);

        $this->listener($branding)->onLogoFilter($event);

        $this->assertSame(self::CORE_WEB_PATH, $event->getWebPath());
    }

    /** The light variant is what core's own site assets were authored for. */
    public function testTheLightVariantNeverOverrides(): void
    {
        $this->shipAsset(LogoSlot::CoreMenuPrimary, 'logo.svg');
        $branding = $this->darkTenant();
        $branding->themeVariant = ThemeVariant::Light;
        $event = $this->event(LogoSlot::CoreMenuPrimary);

        $this->listener($branding)->onLogoFilter($event);

        $this->assertSame(self::CORE_WEB_PATH, $event->getWebPath());
    }

    /** The whole point of the listener: a dark-variant mark shipped inside the module. */
    public function testTheDarkVariantMarkReplacesTheWebPathAndIsRevisionKeyed(): void
    {
        $this->shipAsset(LogoSlot::CoreMenuPrimary, 'logo.svg');
        $branding = $this->darkTenant();
        $branding->revision = 12;
        $event = $this->event(LogoSlot::CoreMenuPrimary);

        $this->listener($branding)->onLogoFilter($event);

        $this->assertSame(
            self::SAFE_WEB_PATH . '/core/menu/primary/logo.svg?rev=12',
            $event->getWebPath(),
        );
    }

    /** LogoService is called with and without a trailing separator depending on caller. */
    #[DataProvider('logoTypeSeparatorProvider')]
    public function testTheSlotIsResolvedRegardlessOfSeparatorStyle(string $logoType): void
    {
        $this->shipAsset(LogoSlot::CoreMenuPrimary, 'logo.svg');
        $event = new LogoFilterEvent($logoType, '/var/www/logo.png', self::CORE_WEB_PATH);

        $this->listener($this->darkTenant())->onLogoFilter($event);

        $this->assertStringContainsString('/core/menu/primary/logo.svg', $event->getWebPath());
    }

    /** Extension precedence is fixed, so the rendered page never depends on FS ordering. */
    public function testSvgWinsOverRasterWhenBothAreShipped(): void
    {
        $this->shipAsset(LogoSlot::CoreMenuPrimary, 'logo.png');
        $this->shipAsset(LogoSlot::CoreMenuPrimary, 'logo.svg');
        $event = $this->event(LogoSlot::CoreMenuPrimary);

        $this->listener($this->darkTenant())->onLogoFilter($event);

        $this->assertStringContainsString('logo.svg', $event->getWebPath());
    }

    /** The favicon names an exact file rather than logo.*, and that is honoured. */
    public function testTheFaviconSlotUsesItsOwnFilename(): void
    {
        $this->shipAsset(LogoSlot::CoreFavicon, 'favicon.ico');
        $event = $this->event(LogoSlot::CoreFavicon);

        $this->listener($this->darkTenant())->onLogoFilter($event);

        $this->assertSame(
            self::SAFE_WEB_PATH . '/core/favicon/favicon.ico?rev=0',
            $event->getWebPath(),
        );
    }

    /** A logo.svg would not satisfy the favicon slot, whose pattern is exact. */
    public function testTheFaviconSlotDoesNotAcceptALogoFile(): void
    {
        $this->shipAsset(LogoSlot::CoreFavicon, 'logo.svg');
        $event = $this->event(LogoSlot::CoreFavicon);

        $this->listener($this->darkTenant())->onLogoFilter($event);

        $this->assertSame(self::CORE_WEB_PATH, $event->getWebPath());
    }

    /** A type outside the closed slot set is not this layer's business. */
    public function testAnUnknownLogoTypeIsIgnored(): void
    {
        $event = new LogoFilterEvent('vendor/unknown/slot', '/var/www/logo.png', self::CORE_WEB_PATH);

        $this->listener($this->darkTenant())->onLogoFilter($event);

        $this->assertSame(self::CORE_WEB_PATH, $event->getWebPath());
    }

    /**
     * The dangerous case, stated honestly: core would filter this path out and return the
     * empty string, deleting the logo. Declining is the only safe behaviour.
     */
    public function testAWebPathOutsideTheModulesTreeIsDeclinedRatherThanSet(): void
    {
        $this->shipAsset(LogoSlot::CoreMenuPrimary, 'logo.svg');
        $event = $this->event(LogoSlot::CoreMenuPrimary);

        $listener = new LogoOverrideListener(
            $this->darkTenant(),
            $this->treeRoot(),
            '/openemr/public/images/branding/dark',
            new NullLogger(),
        );
        $listener->onLogoFilter($event);

        $this->assertSame(self::CORE_WEB_PATH, $event->getWebPath());
    }

    /** No directory configured at all is a supported, silent no-op. */
    public function testAnUnconfiguredAssetDirectoryIsANoOp(): void
    {
        $event = $this->event(LogoSlot::CoreMenuPrimary);

        $listener = new LogoOverrideListener($this->darkTenant(), '', self::SAFE_WEB_PATH, new NullLogger());
        $listener->onLogoFilter($event);

        $this->assertSame(self::CORE_WEB_PATH, $event->getWebPath());
    }

    /** A branding failure must never remove a logo from a page. */
    public function testAFailingBrandingServiceDoesNotPropagateOrMutate(): void
    {
        $this->shipAsset(LogoSlot::CoreMenuPrimary, 'logo.svg');
        $branding = $this->darkTenant();
        $branding->failure = new RuntimeException('branding is unavailable');
        $event = $this->event(LogoSlot::CoreMenuPrimary);

        $this->listener($branding)->onLogoFilter($event);

        $this->assertSame(self::CORE_WEB_PATH, $event->getWebPath());
    }

    /**
     * BrandingServiceInterface::logo() resolves through LogoService, which dispatches this
     * very event. Calling it here would recurse without bound, so it must never be called.
     */
    public function testTheListenerNeverAsksTheServiceToResolveALogo(): void
    {
        $this->shipAsset(LogoSlot::CoreMenuPrimary, 'logo.svg');
        $branding = $this->darkTenant();

        $this->listener($branding)->onLogoFilter($this->event(LogoSlot::CoreMenuPrimary));

        $this->assertSame(0, $branding->logoCallCount);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function logoTypeSeparatorProvider(): array
    {
        return [
            'no separators' => ['core/menu/primary'],
            'trailing separator' => ['core/menu/primary/'],
            'both separators' => ['/core/menu/primary/'],
        ];
    }

    private function listener(StubBrandingService $branding): LogoOverrideListener
    {
        return new LogoOverrideListener(
            $branding,
            $this->treeRoot(),
            self::SAFE_WEB_PATH,
            new NullLogger(),
        );
    }

    private function darkTenant(): StubBrandingService
    {
        $branding = new StubBrandingService();
        $branding->themeVariant = ThemeVariant::Dark;

        return $branding;
    }

    private function event(LogoSlot $slot): LogoFilterEvent
    {
        return new LogoFilterEvent($slot->value, '/var/www/logo.png', self::CORE_WEB_PATH);
    }

    private function shipAsset(LogoSlot $slot, string $filename): void
    {
        $directory = $this->treeRoot() . '/' . $slot->value;

        if (!is_dir($directory) && !mkdir($directory, 0o777, true) && !is_dir($directory)) {
            self::fail('Unable to create the temporary variant asset directory.');
        }

        self::assertNotFalse(file_put_contents($directory . '/' . $filename, 'placeholder'));
    }
}
