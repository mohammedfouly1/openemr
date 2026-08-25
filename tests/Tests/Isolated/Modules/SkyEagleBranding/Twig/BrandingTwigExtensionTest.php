<?php

/**
 * Isolated tests for the branding Twig adapter.
 *
 * The extension is asserted to be exactly a pass-through: for every function, a stubbed
 * BrandingServiceInterface returns a distinctive value and the test proves that value --
 * not a transformed or defaulted one -- reaches the caller. Anything the extension
 * decided for itself would be a second source of branding truth (plan principle P1), so
 * "it forwarded and nothing more" is the property under test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Twig;

use OpenEMR\Modules\SkyEagleBranding\Asset\BrandAsset;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\Service\BrandingServiceInterface;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use OpenEMR\Modules\SkyEagleBranding\Token\DesignToken;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenKey;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSet;
use OpenEMR\Modules\SkyEagleBranding\Twig\BrandingTwigExtension;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config\ModuleAutoloadTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Node\Node;
use Twig\TwigFunction;

#[Group('isolated')]
#[Group('twig')]
final class BrandingTwigExtensionTest extends TestCase
{
    use ModuleAutoloadTrait;

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    public function testExposesExactlyTheFourDocumentedFunctions(): void
    {
        $extension = new BrandingTwigExtension($this->service());

        $names = array_map(
            static fn (TwigFunction $function): string => $function->getName(),
            $extension->getFunctions(),
        );

        self::assertSame(
            ['productName', 'brandingToken', 'brandLogo', 'brandLogoAlt'],
            $names,
        );
    }

    /**
     * Autoescape is off in OpenEMR's Twig, so a function that declared itself HTML-safe
     * would silently exempt its output from the |attr / |text discipline every template
     * relies on. None of these may be safe-marked.
     */
    public function testNoFunctionDeclaresItselfPreEscaped(): void
    {
        $extension = new BrandingTwigExtension($this->service());

        foreach ($extension->getFunctions() as $function) {
            self::assertSame(
                [],
                $function->getSafe(new Node()),
                $function->getName() . ' must not be marked is_safe',
            );
        }
    }

    public function testProductNameForwardsTheLatinName(): void
    {
        $service = $this->service();
        $service->method('productName')->willReturnMap([
            [false, 'Thiqa Care'],
            [true, 'ثقة كير'],
        ]);

        self::assertSame('Thiqa Care', (new BrandingTwigExtension($service))->productName());
    }

    public function testProductNameForwardsTheArabicName(): void
    {
        $service = $this->service();
        $service->method('productName')->willReturnMap([
            [false, 'Thiqa Care'],
            [true, 'ثقة كير'],
        ]);

        self::assertSame('ثقة كير', (new BrandingTwigExtension($service))->productName(true));
    }

    public function testBrandingTokenResolvesAgainstTheActiveVariant(): void
    {
        $service = $this->service();
        $service->method('themeVariant')->willReturn(ThemeVariant::Dark);
        $service->method('tokens')->willReturnMap([
            [ThemeVariant::Light, $this->lightTokens()],
            [ThemeVariant::Dark, $this->darkTokens()],
        ]);

        $extension = new BrandingTwigExtension($service);

        // The dark value, because themeVariant() says dark -- never the light one.
        self::assertSame('#0B1220', $extension->brandingToken('background'));
        self::assertNotSame('#FAFAF8', $extension->brandingToken('background'));
    }

    public function testBrandingTokenFollowsTheActiveVariantWhenItIsLight(): void
    {
        $service = $this->service();
        $service->method('themeVariant')->willReturn(ThemeVariant::Light);
        $service->method('tokens')->willReturnMap([
            [ThemeVariant::Light, $this->lightTokens()],
            [ThemeVariant::Dark, $this->darkTokens()],
        ]);

        self::assertSame('#FAFAF8', (new BrandingTwigExtension($service))->brandingToken('background'));
    }

    /**
     * A template typo must degrade to an unstyled declaration, not to a 500 on the login
     * page. It must also never reach the service, because a non-allowlisted key is
     * exactly what the TokenKey boundary exists to stop.
     */
    #[DataProvider('unresolvableTokenKeyProvider')]
    public function testBrandingTokenReturnsEmptyForAnUnresolvableKey(string $key): void
    {
        $service = $this->service();
        $service->method('themeVariant')->willReturn(ThemeVariant::Light);
        $service->method('tokens')->willReturn($this->lightTokens());

        self::assertSame('', (new BrandingTwigExtension($service))->brandingToken($key));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unresolvableTokenKeyProvider(): array
    {
        return [
            'not in the allowlist' => ['not.a.token'],
            'empty string' => [''],
            'css injection attempt' => ['background: red; --x'],
            'allowlisted but absent from this variant' => ['surfaceRaised'],
        ];
    }

    public function testBrandLogoForwardsTheResolvedUrl(): void
    {
        $service = $this->service();
        $service->method('logo')->willReturnMap([
            [LogoSlot::CoreLoginPrimary, $this->asset(LogoSlot::CoreLoginPrimary)],
        ]);

        self::assertSame(
            '/sites/default/images/logos/core/login/primary/logo.svg?v=7',
            (new BrandingTwigExtension($service))->brandLogo('core/login/primary'),
        );
    }

    public function testBrandLogoReturnsEmptyForAnUnknownSlot(): void
    {
        $service = $this->service();
        $service->expects(self::never())->method('logo');

        self::assertSame('', (new BrandingTwigExtension($service))->brandLogo('core/login/nope'));
    }

    public function testBrandLogoReturnsEmptyForAnUnresolvedAsset(): void
    {
        $service = $this->service();
        $service->method('logo')->willReturn(
            BrandAsset::missing(LogoSlot::CoreLoginPrimary, new BrandingRevision(7)),
        );

        self::assertSame('', (new BrandingTwigExtension($service))->brandLogo('core/login/primary'));
    }

    public function testBrandLogoAltForwardsTheEnglishName(): void
    {
        $service = $this->service();
        $service->method('logo')->willReturn($this->asset(LogoSlot::CoreLoginPrimary));

        self::assertSame(
            'Thiqa Care',
            (new BrandingTwigExtension($service))->brandLogoAlt('core/login/primary'),
        );
    }

    public function testBrandLogoAltForwardsTheArabicName(): void
    {
        $service = $this->service();
        $service->method('logo')->willReturn($this->asset(LogoSlot::CoreLoginPrimary));

        self::assertSame(
            'ثقة كير',
            (new BrandingTwigExtension($service))->brandLogoAlt('core/login/primary', true),
        );
    }

    public function testBrandLogoAltIsEmptyForADecorativeAsset(): void
    {
        $service = $this->service();
        $service->method('logo')->willReturn(
            new BrandAsset(LogoSlot::CoreFavicon, new BrandingRevision(7), '/favicon.ico?v=7', '', ''),
        );

        self::assertSame('', (new BrandingTwigExtension($service))->brandLogoAlt('core/favicon'));
    }

    public function testBrandLogoAltReturnsEmptyForAnUnknownSlot(): void
    {
        $service = $this->service();
        $service->expects(self::never())->method('logo');

        self::assertSame('', (new BrandingTwigExtension($service))->brandLogoAlt('portal/login/nope'));
    }

    /**
     * The adapter must not sanitise: a hostile value has to arrive at the template
     * intact so that the template's |attr filter is the thing that neutralises it. An
     * extension that stripped characters here would hide a missing filter downstream.
     */
    public function testBrandLogoAltDoesNotSanitiseTheServiceValue(): void
    {
        $hostile = 'Thiqa "Care" <script>alert(1)</script> & \'co\'';
        $service = $this->service();
        $service->method('logo')->willReturn(
            new BrandAsset(LogoSlot::CoreLoginPrimary, new BrandingRevision(1), '/logo.svg', $hostile, ''),
        );

        self::assertSame(
            $hostile,
            (new BrandingTwigExtension($service))->brandLogoAlt('core/login/primary'),
        );
    }

    private function service(): BrandingServiceInterface&MockObject
    {
        return $this->createMock(BrandingServiceInterface::class);
    }

    private function asset(LogoSlot $slot): BrandAsset
    {
        return new BrandAsset(
            $slot,
            new BrandingRevision(7),
            '/sites/default/images/logos/' . $slot->value . '/logo.svg?v=7',
            'Thiqa Care',
            'ثقة كير',
        );
    }

    private function lightTokens(): TokenSet
    {
        return new TokenSet(
            ThemeVariant::Light,
            DesignToken::fromLiteral(TokenKey::Background, '#FAFAF8'),
            DesignToken::fromLiteral(TokenKey::TextPrimary, '#0B1B4D'),
        );
    }

    private function darkTokens(): TokenSet
    {
        return new TokenSet(
            ThemeVariant::Dark,
            DesignToken::fromLiteral(TokenKey::Background, '#0B1220'),
            DesignToken::fromLiteral(TokenKey::TextPrimary, '#F5F6F8'),
        );
    }
}
