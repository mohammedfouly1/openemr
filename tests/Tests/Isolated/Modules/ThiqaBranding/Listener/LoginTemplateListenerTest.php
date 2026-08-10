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

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Listener;

use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Listener\LoginTemplateListener;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Config\ModuleAutoloadTrait;
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

    /** Merge, never replace: the core variable set must come through untouched. */
    public function testEveryCoreVariableSurvivesTheMerge(): void
    {
        $event = $this->loginEvent();

        $this->listener($this->brandedTenant())->onTemplatePage($event);

        $variables = $event->getTwigVariables();
        foreach (self::CORE_VARIABLES as $key => $value) {
            $this->assertArrayHasKey($key, $variables);
            $this->assertSame($value, $variables[$key], "Core variable {$key} was altered.");
        }
    }

    /** The branding contribution is additive and named, so it cannot collide with core. */
    public function testOnlyTheBrandingKeysAreAdded(): void
    {
        $event = $this->loginEvent();

        $this->listener($this->brandedTenant())->onTemplatePage($event);

        $this->assertSame(
            ['brandProductName', 'brandTagline', 'primaryLogoAlt', 'secondaryLogoAlt'],
            $this->addedKeys($event),
        );
    }

    /** An RTL session gets the Arabic wordmark and Arabic accessible names. */
    public function testARightToLeftSessionReceivesTheArabicNames(): void
    {
        $branding = $this->brandedTenant();
        $branding->rtl = true;
        $event = $this->loginEvent();

        $this->listener($branding)->onTemplatePage($event);

        $variables = $event->getTwigVariables();
        $this->assertSame('شعار ثقة', $variables['primaryLogoAlt']);
        $this->assertSame('ثقة', $variables['brandProductName']);
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

    private function listener(StubBrandingService $branding): LoginTemplateListener
    {
        return new LoginTemplateListener($branding, new NullLogger());
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
