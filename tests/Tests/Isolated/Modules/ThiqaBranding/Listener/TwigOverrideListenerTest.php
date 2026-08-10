<?php

/**
 * Isolated tests for Twig namespace registration and SMART template substitution.
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
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Modules\ThiqaBranding\Bootstrap;
use OpenEMR\Modules\ThiqaBranding\Listener\TwigOverrideListener;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/*
 * The module's PSR-4 prefix is not in the root autoloader, so it is registered here.
 * This uses the file-scope shim rather than ModuleAutoloadTrait because variantTemplateProvider()
 * names a module enum, and PHPUnit resolves data providers while it builds the suite --
 * before setUpBeforeClass() would have had a chance to run.
 */
require_once __DIR__ . '/../Token/token_autoloader.php';

/**
 * Locked Q38 / resolution CR-17 is the whole subject of this file. Two things are asserted
 * that a reviewer should be able to check at a glance:
 *
 *  - the module directory lands in the module's OWN namespace and the MAIN namespace stays
 *    empty -- that is what "no shadowing, no resolution-order dependence" means in code;
 *  - the SMART style page, and only that page, has its template name rewritten to the
 *    `@oe-module-thiqa-branding/...` form, because a namespaced template is unreachable by
 *    any other means.
 *
 * The real module templates directory is used rather than a fixture: the variant templates
 * shipping under the names the listener composes is exactly the coupling worth testing.
 */
final class TwigOverrideListenerTest extends TestCase
{
    private const CORE_SMART_TEMPLATE = '/api/smart/smart-style_light.json.twig';

    // -- (a) namespace registration ------------------------------------------------

    public function testTheTemplateDirectoryIsRegisteredUnderTheModuleNamespaceOnly(): void
    {
        $loader = new FilesystemLoader([]);
        $event = new TwigEnvironmentEvent(new Environment($loader));

        $this->listener()->onTwigEnvironmentCreated($event);

        $this->assertSame([$this->moduleTemplateDirectory()], $loader->getPaths(Bootstrap::TWIG_NAMESPACE));
        $this->assertSame([], $loader->getPaths(), 'The main Twig namespace must never be written to.');
    }

    public function testRegistrationIsIdempotent(): void
    {
        $loader = new FilesystemLoader([]);
        $event = new TwigEnvironmentEvent(new Environment($loader));
        $listener = $this->listener();

        $listener->onTwigEnvironmentCreated($event);
        $listener->onTwigEnvironmentCreated($event);

        $this->assertCount(1, $loader->getPaths(Bootstrap::TWIG_NAMESPACE));
    }

    /** A module shipped without its templates registers nothing rather than throwing. */
    public function testAMissingTemplateDirectoryRegistersNothing(): void
    {
        $loader = new FilesystemLoader([]);
        $event = new TwigEnvironmentEvent(new Environment($loader));

        $this->listenerFor(__DIR__ . '/there-is-no-such-template-directory')
            ->onTwigEnvironmentCreated($event);

        $this->assertSame([], $loader->getPaths(Bootstrap::TWIG_NAMESPACE));
        $this->assertSame([], $loader->getPaths());
    }

    /** The registered namespace is what makes the rewritten name resolvable. */
    public function testTheRewrittenTemplateNameResolvesAgainstTheRegisteredNamespace(): void
    {
        $branding = new StubBrandingService();
        $branding->themeVariant = ThemeVariant::Dark;

        $loader = new FilesystemLoader([]);
        $listener = $this->listener($branding);
        $listener->onTwigEnvironmentCreated(new TwigEnvironmentEvent(new Environment($loader)));

        $event = $this->smartStyleEvent();
        $listener->onTemplatePage($event);

        $this->assertTrue($loader->exists($event->getTwigTemplate()));
    }

    // -- (b) SMART template substitution ---------------------------------------------

    #[DataProvider('variantTemplateProvider')]
    public function testTheSmartStyleTemplateFollowsTheThemeVariant(
        ThemeVariant $variant,
        string $expectedTemplate,
    ): void {
        $branding = new StubBrandingService();
        $branding->themeVariant = $variant;
        $event = $this->smartStyleEvent();

        $this->listener($branding)->onTemplatePage($event);

        $this->assertSame($expectedTemplate, $event->getTwigTemplate());
    }

    /** Registration and substitution are independent: no path is added on this half. */
    public function testSubstitutionLeavesTheTwigVariablesAlone(): void
    {
        $variables = ['logo' => ['primary' => '/logo.png']];
        $event = new TemplatePageEvent(
            TwigOverrideListener::SMART_STYLE_PAGE,
            [],
            self::CORE_SMART_TEMPLATE,
            $variables,
        );

        $this->listener()->onTemplatePage($event);

        $this->assertSame($variables, $event->getTwigVariables());
    }

    #[DataProvider('foreignPageProvider')]
    public function testAPageThisListenerDoesNotOwnIsLeftAlone(string $pageName): void
    {
        $branding = new StubBrandingService();
        $branding->themeVariant = ThemeVariant::Dark;
        $event = new TemplatePageEvent($pageName, [], self::CORE_SMART_TEMPLATE, ['a' => 1]);

        $this->listener($branding)->onTemplatePage($event);

        $this->assertSame(self::CORE_SMART_TEMPLATE, $event->getTwigTemplate());
        $this->assertSame(['a' => 1], $event->getTwigVariables());
    }

    /**
     * Without the shipped template, core's own contract is left in place.
     *
     * The directory used exists but holds only the login partials, so the listener finds
     * no api/smart/ file under it -- the same state a module shipped without its SMART
     * contracts would be in.
     */
    public function testAMissingVariantTemplateLeavesTheCoreTemplateInPlace(): void
    {
        $branding = new StubBrandingService();
        $branding->themeVariant = ThemeVariant::Dark;
        $event = $this->smartStyleEvent();

        $this->listenerFor($this->moduleTemplateDirectory() . '/login', $branding)->onTemplatePage($event);

        $this->assertSame(self::CORE_SMART_TEMPLATE, $event->getTwigTemplate());
    }

    /** A branding failure must not break the SMART style endpoint. */
    public function testAFailingBrandingServiceDoesNotPropagateOrMutate(): void
    {
        $branding = new StubBrandingService();
        $branding->failure = new RuntimeException('branding is unavailable');
        $event = $this->smartStyleEvent();

        $this->listener($branding)->onTemplatePage($event);

        $this->assertSame(self::CORE_SMART_TEMPLATE, $event->getTwigTemplate());
    }

    /**
     * @return array<string, array{ThemeVariant, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function variantTemplateProvider(): array
    {
        return [
            'dark' => [
                ThemeVariant::Dark,
                '@oe-module-thiqa-branding/api/smart/smart-style_dark.json.twig',
            ],
            'light' => [
                ThemeVariant::Light,
                '@oe-module-thiqa-branding/api/smart/smart-style_light.json.twig',
            ],
        ];
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function foreignPageProvider(): array
    {
        return [
            'login page' => ['login/login.php'],
            'oauth2 authorize' => ['oauth2/authorize'],
            'empty page name' => [''],
        ];
    }

    private function listener(?StubBrandingService $branding = null): TwigOverrideListener
    {
        return $this->listenerFor($this->moduleTemplateDirectory(), $branding);
    }

    private function listenerFor(string $directory, ?StubBrandingService $branding = null): TwigOverrideListener
    {
        return new TwigOverrideListener(
            $branding ?? new StubBrandingService(),
            $directory,
            new NullLogger(),
        );
    }

    private function smartStyleEvent(): TemplatePageEvent
    {
        return new TemplatePageEvent(
            TwigOverrideListener::SMART_STYLE_PAGE,
            [],
            self::CORE_SMART_TEMPLATE,
        );
    }

    private function moduleTemplateDirectory(): string
    {
        return dirname(__DIR__, 6)
            . '/interface/modules/custom_modules/oe-module-thiqa-branding/templates';
    }
}
