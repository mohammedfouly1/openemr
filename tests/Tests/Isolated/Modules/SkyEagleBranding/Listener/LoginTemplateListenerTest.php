<?php

/**
 * Isolated tests for the login page branding variables (BRAND-053).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Listener;

use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\Listener\LoginTemplateListener;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config\ModuleAutoloadTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * The login page is the one surface where a branding bug is unrecoverable by the user:
 * blank it and nobody can get in to fix the configuration. Every test here is therefore a
 * variation on one question -- does the core variable set survive?
 *
 * The positive claim is BRAND-053: the primary logo's accessible name reaches the template
 * instead of core's hardcoded alt="".
 */
final class LoginTemplateListenerTest extends TestCase
{
    use ModuleAutoloadTrait;

    private const LAYOUT = 'login/layouts/login_layout.html.twig';

    /** A representative slice of the ~30 variables interface/login/login.php supplies. */
    private const CORE_VARIABLES = [
        'title' => 'OpenEMR',
        'displayPrimaryLogo' => true,
        'primaryLogo' => '/sites/default/images/logos/core/login/primary/logo.png',
        'tagline' => 'Clinical confidence, connected care.',
        'displayTagline' => true,
        'showLabels' => false,
    ];

    /**
     * The four core view variables the listener is permitted to rewrite, and the slot each
     * one is rewritten from. Mirrors LoginTemplateListener::LOGO_VIEW_KEYS deliberately:
     * if that map grows, this test must be updated consciously rather than following along.
     *
     * @var array<string, LogoSlot>
     */
    private const REWRITTEN_LOGO_KEYS = [
        'primaryLogo' => LogoSlot::CoreLoginPrimary,
        'secondaryLogo' => LogoSlot::CoreLoginSecondary,
        'smallLogoOne' => LogoSlot::CoreLoginSmallPrimary,
        'smallLogoTwo' => LogoSlot::CoreLoginSmallSecondary,
    ];

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    /** Unconfigured branding contributes nothing at all -- not even an empty alt. */
    public function testAnUnconfiguredTenantLeavesTheVariablesByteForByteIdentical(): void
    {
        $event = $this->loginEvent();

        $this->listener(new StubBrandingService())->onTemplatePage($event);

        $this->assertSame(self::CORE_VARIABLES, $event->getTwigVariables());
        $this->assertSame(self::LAYOUT, $event->getTwigTemplate());
    }

    /** BRAND-053: the accessible name core hardcodes as "" is supplied here. */
    public function testThePrimaryLogoAccessibleNameIsSupplied(): void
    {
        $event = $this->loginEvent();

        $this->listener($this->brandedTenant())->onTemplatePage($event);

        $this->assertSame('Thiqa logo', $event->getTwigVariables()['primaryLogoAlt']);
        $this->assertSame('Thiqa secondary logo', $event->getTwigVariables()['secondaryLogoAlt']);
    }

    /**
     * Merge, never replace -- with exactly four deliberate exceptions.
     *
     * The four logo URL variables ARE rewritten, to carry the branding revision that locked
     * Q76 / constraint C8 require in every branding cache key (docs/RebrandingBugs.md RB-03).
     * Everything else core supplied must come through byte-for-byte.
     */
    public function testEveryCoreVariableExceptTheLogoUrlsSurvivesTheMerge(): void
    {
        $event = $this->loginEvent();

        $this->listener($this->brandedTenant())->onTemplatePage($event);

        $variables = $event->getTwigVariables();
        foreach (self::CORE_VARIABLES as $key => $value) {
            $this->assertArrayHasKey($key, $variables);

            if (array_key_exists($key, self::REWRITTEN_LOGO_KEYS)) {
                continue;
            }

            $this->assertSame($value, $variables[$key], "Core variable {$key} was altered.");
        }
    }

    /**
     * A rewritten logo URL is the branding layer's own URL for that slot -- nothing else.
     *
     * This is the property that makes the rewrite safe: the listener may not invent a path,
     * only republish what BrandAssetResolver produced (which is core's resolved path with
     * `&rev=` appended -- proven separately in BrandAssetResolverTest).
     */
    public function testARewrittenLogoUrlIsExactlyTheBrandingServiceUrl(): void
    {
        $branding = $this->brandedTenant();
        $event = $this->loginEvent();

        $this->listener($branding)->onTemplatePage($event);

        $variables = $event->getTwigVariables();
        foreach (self::REWRITTEN_LOGO_KEYS as $viewKey => $slot) {
            $asset = $branding->logo($slot);
            if (!$asset->isResolved()) {
                continue;
            }

            $this->assertSame($asset->url(), $variables[$viewKey], "Logo URL {$viewKey} is not the service's.");
        }
    }

    /**
     * An unresolved slot must leave core's own URL in place.
     *
     * Publishing an empty string here would blank the logo on the one page a locked-out
     * administrator has to be able to read.
     */
    public function testAnUnresolvedLogoSlotLeavesTheCoreUrlUntouched(): void
    {
        $branding = new StubBrandingService();
        $branding->productName = 'Thiqa';
        $event = $this->loginEvent();

        $this->listener($branding)->onTemplatePage($event);

        $this->assertSame(
            self::CORE_VARIABLES['primaryLogo'],
            $event->getTwigVariables()['primaryLogo'],
        );
    }

    /** The branding contribution is additive and named, so it cannot collide with core. */
    public function testOnlyTheBrandingKeysAreAdded(): void
    {
        $event = $this->loginEvent();

        $this->listener($this->brandedTenant())->onTemplatePage($event);

        $this->assertSame(
            ['brandProductName', 'brandTagline', 'primaryLogoAlt', 'secondaryLogo', 'secondaryLogoAlt'],
            $this->addedKeys($event),
        );
    }

    /**
     * SKY-F01. An Arabic session gets the Arabic wordmark and Arabic accessible names.
     *
     * This used to set `$branding->rtl = true` and assert the same thing, which passed for the
     * wrong reason: it proved only that SOME right-to-left session got Arabic. The three cases
     * below separate the two variables the old test conflated, and the Hebrew one is the case
     * that actually failed before the fix.
     */
    public function testAnArabicSessionReceivesTheArabicNames(): void
    {
        $branding = $this->brandedTenant();
        $branding->rtl = true;
        $event = $this->loginEvent();

        $this->listener($branding, arabic: true)->onTemplatePage($event);

        $variables = $event->getTwigVariables();
        $this->assertSame('شعار ثقة', $variables['primaryLogoAlt']);
        $this->assertSame('شعار ثقة الثانوي', $variables['secondaryLogoAlt']);
        $this->assertSame('ثقة', $variables['brandProductName']);
    }

    /** An English session gets the Latin names -- the baseline the other two are read against. */
    public function testAnEnglishSessionReceivesTheLatinNames(): void
    {
        $branding = $this->brandedTenant();
        $event = $this->loginEvent();

        $this->listener($branding, arabic: false)->onTemplatePage($event);

        $variables = $event->getTwigVariables();
        $this->assertSame('Thiqa logo', $variables['primaryLogoAlt']);
        $this->assertSame('Thiqa secondary logo', $variables['secondaryLogoAlt']);
        $this->assertSame('Thiqa', $variables['brandProductName']);
    }

    /**
     * SKY-F01, the regression this exists for: a right-to-left session that is NOT Arabic.
     *
     * `lang_languages` marks four locales right-to-left -- Hebrew, Arabic, Persian and Urdu --
     * so `interface/globals.php:566-570` swaps in the `rtl_` stylesheet for a Hebrew session
     * exactly as it does for an Arabic one, and `BrandingServiceInterface::isRtl()` reads that
     * prefix back as true. Selecting branding content from it announced this product's logo to
     * a Hebrew screen-reader user as `شعار ثقة`.
     *
     * The direction is deliberately left true here. If this test ever passes only because the
     * layout is left-to-right, it is testing nothing: the whole defect was direction and
     * language disagreeing, so they must disagree in the fixture.
     */
    public function testARightToLeftSessionThatIsNotArabicKeepsTheLatinNames(): void
    {
        $branding = $this->brandedTenant();
        $branding->rtl = true;
        $event = $this->loginEvent();

        $this->listener($branding, arabic: false)->onTemplatePage($event);

        $variables = $event->getTwigVariables();
        $this->assertSame('Thiqa logo', $variables['primaryLogoAlt']);
        $this->assertSame('Thiqa secondary logo', $variables['secondaryLogoAlt']);
        $this->assertSame('Thiqa', $variables['brandProductName']);
        $this->assertStringNotContainsString('شعار', $variables['primaryLogoAlt']);
        $this->assertStringNotContainsString('ثقة', $variables['brandProductName']);
    }

    /** A suppressed tagline is absent, not present-and-empty. */
    public function testASuppressedTaglineIsNotPublished(): void
    {
        $branding = $this->brandedTenant();
        $branding->tagline = null;
        $event = $this->loginEvent();

        $this->listener($branding)->onTemplatePage($event);

        $this->assertArrayNotHasKey('brandTagline', $event->getTwigVariables());
        $this->assertSame('Clinical confidence, connected care.', $event->getTwigVariables()['tagline']);
    }

    /**
     * An unresolved logo yields an empty accessible name, and an empty name must not be
     * published: it would override the template's own default with "this is decorative".
     */
    public function testAnUnresolvedLogoPublishesNoAccessibleName(): void
    {
        $branding = new StubBrandingService();
        $branding->productName = 'Thiqa';
        $event = $this->loginEvent();

        $this->listener($branding)->onTemplatePage($event);

        $this->assertSame(['brandProductName'], $this->addedKeys($event));
    }

    #[DataProvider('foreignPageProvider')]
    public function testAPageThisListenerDoesNotOwnIsLeftAlone(string $pageName): void
    {
        $event = new TemplatePageEvent($pageName, [], self::LAYOUT, self::CORE_VARIABLES);

        $this->listener($this->brandedTenant())->onTemplatePage($event);

        $this->assertSame(self::CORE_VARIABLES, $event->getTwigVariables());
    }

    /** The failure that matters most: a branding fault must not blank the login page. */
    public function testAFailingBrandingServiceDoesNotPropagateOrMutate(): void
    {
        $branding = $this->brandedTenant();
        $branding->failure = new RuntimeException('branding is unavailable');
        $event = $this->loginEvent();

        $this->listener($branding)->onTemplatePage($event);

        $this->assertSame(self::CORE_VARIABLES, $event->getTwigVariables());
        $this->assertSame(self::LAYOUT, $event->getTwigTemplate());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function foreignPageProvider(): array
    {
        return [
            'smart style contract' => ['oauth2/authorize/smart-style'],
            'portal login' => ['portal/index.php'],
            'empty page name' => [''],
        ];
    }

    /**
     * Keys present after the listener ran that core did not supply, sorted.
     *
     * @return list<string>
     */
    private function addedKeys(TemplatePageEvent $event): array
    {
        $added = array_map(strval(...), array_keys(array_diff_key($event->getTwigVariables(), self::CORE_VARIABLES)));
        sort($added);

        return $added;
    }

    private function listener(StubBrandingService $branding, bool $arabic = false): LoginTemplateListener
    {
        return new LoginTemplateListener($branding, new StubSessionLanguage($arabic), new NullLogger());
    }

    private function loginEvent(): TemplatePageEvent
    {
        return new TemplatePageEvent(
            LoginTemplateListener::LOGIN_PAGE,
            [],
            self::LAYOUT,
            self::CORE_VARIABLES,
        );
    }

    /** A tenant with a product name, a tagline and both login logos resolved. */
    private function brandedTenant(): StubBrandingService
    {
        $branding = new StubBrandingService();
        $branding->productName = 'Thiqa';
        $branding->productNameArabic = 'ثقة';
        $branding->tagline = 'Clinical confidence, connected care.';
        $branding->logoAltText = [
            LogoSlot::CoreLoginPrimary->value => ['Thiqa logo', 'شعار ثقة'],
            LogoSlot::CoreLoginSecondary->value => ['Thiqa secondary logo', 'شعار ثقة الثانوي'],
        ];

        return $branding;
    }
}
