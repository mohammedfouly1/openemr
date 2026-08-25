<?php

/**
 * Attaches the branding layer to OpenEMR.
 *
 * Every attachment is a published extension point. This class is the only place
 * the module touches global state, and it registers listeners exclusively -- no
 * core file is modified (locked Invariant 4).
 *
 * Namespacing rule (locked Q38, resolution CR-17): module templates are registered
 * under the module's own Twig namespace and substituted by rewriting the template
 * name from a TemplatePageEvent listener. FilesystemLoader::prependPath() into the
 * main namespace is prohibited -- it is unnamespaced and resolution-order dependent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Command\CommandRunnerFilterEvent;
use OpenEMR\Events\Core\StyleFilterEvent;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Events\Services\LogoFilterEvent;
use OpenEMR\Modules\ThiqaBranding\Accessibility\ContrastCalculator;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandAssetResolver;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\LogoValidator;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\RasterImageReader;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\SvgInspector;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingProfileLoader;
use OpenEMR\Modules\ThiqaBranding\Config\GlobalsRegistrationListener;
use OpenEMR\Modules\ThiqaBranding\Config\ModulePaths;
use OpenEMR\Modules\ThiqaBranding\Console\ApplyProfileCommand;
use OpenEMR\Modules\ThiqaBranding\Console\BackupCommand;
use OpenEMR\Modules\ThiqaBranding\Console\MaterialiseCommand;
use OpenEMR\Modules\ThiqaBranding\Console\ProvisionReportAclCommand;
use OpenEMR\Modules\ThiqaBranding\Console\SeedDemoCommand;
use OpenEMR\Modules\ThiqaBranding\Console\SiteScopeNotice;
use OpenEMR\Modules\ThiqaBranding\Console\VerifyCommand;
use OpenEMR\Modules\ThiqaBranding\Language\CoreSessionLanguage;
use OpenEMR\Modules\ThiqaBranding\Listener\LoginTemplateListener;
use OpenEMR\Modules\ThiqaBranding\Listener\LogoOverrideListener;
use OpenEMR\Modules\ThiqaBranding\Listener\StyleInjectionListener;
use OpenEMR\Modules\ThiqaBranding\Listener\TwigOverrideListener;
use OpenEMR\Modules\ThiqaBranding\Materialisation\AtomicFileWriter;
use OpenEMR\Modules\ThiqaBranding\Materialisation\BrandingMaterialiser;
use OpenEMR\Modules\ThiqaBranding\Materialisation\JsonFileTier1PaletteProvider;
use OpenEMR\Modules\ThiqaBranding\Materialisation\QueryUtilsBrandingGlobalsWriter;
use OpenEMR\Modules\ThiqaBranding\Materialisation\TenantBrandingPaths;
use OpenEMR\Modules\ThiqaBranding\Materialisation\TokenCssWriter;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthCheck;
use OpenEMR\Modules\ThiqaBranding\Observability\FilesystemStylesheetProbe;
use OpenEMR\Modules\ThiqaBranding\Observability\MaterialisationLogger;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingService;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingServiceInterface;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteInventory;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeResolver;
use OpenEMR\Modules\ThiqaBranding\Token\CssVariableRenderer;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSetParser;
use OpenEMR\Modules\ThiqaBranding\Token\TokenValidator;
use OpenEMR\Services\LogoService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class Bootstrap
{
    /**
     * Twig namespace for this module's templates (locked Q38).
     *
     * Derived rather than retyped: the namespace and the directory name are deliberately the
     * same string, so a rename must not be able to move one without the other (S1-P2-12).
     */
    public const TWIG_NAMESPACE = ModulePaths::DIRECTORY_NAME;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly string $moduleDirectory,
    ) {
    }

    /**
     * Attach the branding layer to OpenEMR.
     *
     * Every attachment below is a published extension point (plan section 3.6): no core
     * file is modified. Construction is eager but cheap -- the services hold references
     * only, and the token document is read lazily on first use inside BrandingService.
     *
     * Note TwigOverrideListener registers on TWO keys. The SMART style path dispatches
     * TemplatePageEvent **unnamed** (SMARTAuthorizationController::renderTwigJson), while
     * the login page dispatches it **with** RENDER_EVENT. A listener bound to only one of
     * those keys silently misses the other, which is why the class name is used as well.
     */
    public function register(): void
    {
        $logger = ServiceContainer::getLogger();
        $globals = OEGlobalsBag::getInstance();
        $configFactory = new BrandingConfigFactory($globals);

        $this->dispatcher->addListener(
            GlobalsInitializedEvent::EVENT_HANDLE,
            (new GlobalsRegistrationListener($configFactory))->onGlobalsInitialized(...)
        );

        $branding = $this->brandingService($configFactory, $globals);

        $this->dispatcher->addListener(
            StyleFilterEvent::EVENT_NAME,
            (new StyleInjectionListener($branding, $logger))->onStyleFilter(...)
        );

        $this->dispatcher->addListener(
            LogoFilterEvent::EVENT_NAME,
            (new LogoOverrideListener(
                $branding,
                $this->moduleDirectory . '/' . ModulePaths::DARK_LOGO_SUBPATH,
                ModulePaths::DARK_LOGO_WEB_PATH,
                $logger,
            ))->onLogoFilter(...)
        );

        $twig = new TwigOverrideListener($branding, $this->templateDirectory(), $logger);
        $this->dispatcher->addListener(
            TwigEnvironmentEvent::EVENT_CREATED,
            $twig->onTwigEnvironmentCreated(...)
        );
        // Both keys, deliberately -- see the class docblock note above.
        $this->dispatcher->addListener(TemplatePageEvent::class, $twig->onTemplatePage(...));

        $this->dispatcher->addListener(
            TemplatePageEvent::RENDER_EVENT,
            (new LoginTemplateListener($branding, new CoreSessionLanguage(), $logger))->onTemplatePage(...)
        );

        $this->dispatcher->addListener(
            CommandRunnerFilterEvent::EVENT_NAME,
            $this->registerConsoleCommands(...)
        );
    }

    /**
     * Expose the module's commands to bin/console.
     *
     * Without this the three Console classes exist but are unreachable: `bin/console`
     * answers "There are no commands defined in the thiqa-branding namespace", which is
     * exactly what a live check found. Construction is done here rather than in a
     * container because the module has no container of its own, and it is lazy in the
     * sense that this listener only fires on a CLI run.
     */
    public function registerConsoleCommands(CommandRunnerFilterEvent $event): void
    {
        $globals = OEGlobalsBag::getInstance();
        $logger = ServiceContainer::getLogger();
        $projectDir = $globals->getProjectDir();

        // Never guess the tenant.
        //
        // This used to read `SiteId::tryFrom(...) ?? new SiteId('default')`. That fallback
        // was a cross-tenant write path: interface/globals.php:304 permits `.` in a site id
        // while SiteId deliberately does not (see its docblock — excluding `.` is what makes
        // `..` unrepresentable rather than merely filtered). So for a tenant legitimately
        // named e.g. `clinic.one`, $bootstrappedSite silently became `default`,
        // ApplyProfileCommand's `--site` equality guard then ACCEPTED `--site=default`, and
        // the profile was written into clinic.one's database while every log line said
        // `default`. Recorded as docs/RebrandingBugs.md RB-05.
        //
        // Widening SiteId to accept dots was considered and rejected: relaxing a security
        // guard to fix a binding bug is the wrong trade, and SiteId's own docblock warns
        // against exactly that. The branding layer instead treats a dotted site id as
        // unsupported and says so loudly — no commands are registered at all, so there is no
        // command that could act on the wrong tenant.
        $siteDirectory = basename($globals->getString('OE_SITE_DIR'));
        $site = SiteId::tryFrom($siteDirectory);

        if (!$site instanceof SiteId) {
            $logger->error(
                'Thiqa branding console commands were not registered: this site id is outside the '
                . 'character set the branding layer supports, so no tenant can be bound safely.',
                [
                    'siteDirectory' => $siteDirectory,
                    'permitted' => 'one alphanumeric character, then alphanumerics, underscores and hyphens; '
                        . 'maximum ' . SiteId::MAX_LENGTH . ' characters; no dots',
                    'remedy' => 'Rename the site directory, or provision tenants within the supported set.',
                ]
            );

            return;
        }

        $paths = new TenantBrandingPaths(
            $this->moduleDirectory . '/' . ModulePaths::TENANT_BRANDING_SUBPATH,
            $projectDir . '/sites',
        );

        // Finding B1. --site is mandatory and has no default, and nothing enumerated the
        // tenants that exist, so a branding run could exit 0 having silently left a second
        // fully configured tenant untouched. Every tenant-scoped command below now carries
        // this notice; it reads the sites directory and nothing else, opens no database
        // connection, and never changes an exit code.
        $siteScopeNotice = new SiteScopeNotice(new SiteInventory($projectDir . '/sites'));

        $globalsWriter = new QueryUtilsBrandingGlobalsWriter($site);
        $clock = ServiceContainer::getClock();

        $health = new BrandingHealthCheck(
            $globalsWriter,
            new FilesystemStylesheetProbe($paths),
            $clock,
            $logger,
        );

        $files = new AtomicFileWriter();
        $materialiser = new BrandingMaterialiser(
            new TokenValidator(new ContrastCalculator()),
            new JsonFileTier1PaletteProvider(new TokenSetParser(), $projectDir . '/brand/tokens/thiqa-tokens.json'),
            new TokenCssWriter(new CssVariableRenderer(), $files, $paths),
            $files,
            $paths,
            $globalsWriter,
            $clock,
            $logger,
        );

        $logoValidator = new LogoValidator(new RasterImageReader(), new SvgInspector(), $logger);

        foreach ([
            new ApplyProfileCommand(
                new BrandingProfileLoader(),
                $this->moduleDirectory . '/' . ModulePaths::PROFILE_SUBPATH,
                $site,
                $siteScopeNotice,
            ),
            new VerifyCommand($health, $siteScopeNotice),
            new ProvisionReportAclCommand(),
            new BackupCommand(),
            new SeedDemoCommand(),
            new MaterialiseCommand(
                $materialiser,
                $health,
                new MaterialisationLogger($logger),
                $logoValidator,
                $siteScopeNotice,
            ),
        ] as $command) {
            $event->setCommand($command::class, $command);
        }
    }

    private function brandingService(
        BrandingConfigFactory $configFactory,
        OEGlobalsBag $globals,
    ): BrandingServiceInterface {
        return new BrandingService(
            $configFactory,
            new BrandAssetResolver(new LogoService()),
            new ThemeResolver(),
            new TokenSetParser(),
            $globals->getProjectDir() . '/' . BrandingService::TOKEN_DOCUMENT_RELATIVE_PATH,
            $globals->getWebRoot() . '/' . BrandingService::TOKEN_STYLESHEET_RELATIVE_PATH,
        );
    }

    public function templateDirectory(): string
    {
        return $this->moduleDirectory . DIRECTORY_SEPARATOR . ModulePaths::TEMPLATE_SUBPATH;
    }

    public function moduleDirectory(): string
    {
        return $this->moduleDirectory;
    }
}
