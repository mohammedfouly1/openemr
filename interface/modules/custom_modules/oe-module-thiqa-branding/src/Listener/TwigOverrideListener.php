<?php

/**
 * Registers the module Twig namespace and redirects the SMART style contract into it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Listener;

use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Modules\ThiqaBranding\Bootstrap;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingServiceInterface;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use Psr\Log\LoggerInterface;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

/**
 * Extension points E4 and E6 (plan section 3.6), implementing locked Q38 / resolution
 * CR-17 exactly as written in docs/branding-production/16-conflict-resolutions.md section 2.
 *
 * The two halves are inseparable, which is why they live in one class:
 *
 *  - **(a) Namespace registration.** On TwigEnvironmentEvent::EVENT_CREATED the module
 *    template directory is added under the module's OWN namespace,
 *    `addPath($dir, Bootstrap::TWIG_NAMESPACE)`. Nothing is shadowed and no core template
 *    name changes meaning.
 *  - **(b) Name rewriting.** On TemplatePageEvent the SMART style page has its template
 *    name rewritten to `@oe-module-thiqa-branding/...`, which is the only way a template
 *    inside a private namespace is ever reached.
 *
 * `FilesystemLoader::prependPath()` and `addPath()` without a namespace are **prohibited**
 * -- they are unnamespaced and resolution-order dependent, the exact fragility Q38 exists
 * to remove. ForbiddenBrandingTwigPathRule fails the build on either.
 *
 * ## Why the TemplatePageEvent half is registered on the class, not on a name
 *
 * SMARTAuthorizationController::renderTwigJson() dispatches `TemplatePageEvent` with **no
 * event name**, so Symfony keys it by class. The login page dispatches the same class
 * **with** `TemplatePageEvent::RENDER_EVENT`. A listener registered on only one key
 * silently misses the other. This listener is registered on the class; LoginTemplateListener
 * is registered on RENDER_EVENT.
 *
 * ## Why the file existence check is here
 *
 * renderTwigJson() resolves `[$rewritten, $coreDefault]`, so an unresolvable rewrite would
 * fall back to core anyway. Relying on that would still be relying on someone else's
 * safety net: if the module ships without its templates, this listener declines to rewrite
 * and the SMART contract is served by core, unchanged.
 */
final readonly class TwigOverrideListener
{
    /** Page name SMARTAuthorizationController::smartAppStyles() dispatches under. */
    public const SMART_STYLE_PAGE = 'oauth2/authorize/smart-style';

    /** Template path, relative to the module template directory, per variant. */
    private const SMART_STYLE_RELATIVE_FORMAT = 'api/smart/smart-style_%s.json.twig';

    /** The namespaced Twig name the render is redirected to. */
    private const SMART_STYLE_TEMPLATE_FORMAT = '@' . Bootstrap::TWIG_NAMESPACE . '/' . self::SMART_STYLE_RELATIVE_FORMAT;

    /**
     * @param string $templateDirectory absolute path to the module's templates/ directory
     */
    public function __construct(
        private BrandingServiceInterface $branding,
        private string $templateDirectory,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * (a) Listener for TwigEnvironmentEvent::EVENT_CREATED ('core.twig.environment.create').
     *
     * Registers the module templates under the module namespace and nothing else. The
     * main namespace is never written to.
     */
    public function onTwigEnvironmentCreated(TwigEnvironmentEvent $event): void
    {
        $loader = $event->getTwigEnvironment()->getLoader();

        // OpenEMR builds a FilesystemLoader today, but the event exposes the interface;
        // any other loader has no notion of a path and is left alone.
        if (!$loader instanceof FilesystemLoader) {
            return;
        }

        if (!is_dir($this->templateDirectory)) {
            $this->logger->warning('Branding template directory is missing; no Twig namespace registered', [
                'event' => TwigEnvironmentEvent::EVENT_CREATED,
                'namespace' => Bootstrap::TWIG_NAMESPACE,
                'directory' => $this->templateDirectory,
            ]);

            return;
        }

        // A TwigContainer can be built more than once per request; re-adding the same
        // path would grow the namespace's search list for no benefit.
        if (in_array($this->templateDirectory, $loader->getPaths(Bootstrap::TWIG_NAMESPACE), true)) {
            return;
        }

        try {
            // The is_dir() check above already covers the documented failure, but addPath()
            // owns the authoritative test and can still reject a path this process cannot
            // stat. LoaderError is the only type it raises.
            $loader->addPath($this->templateDirectory, Bootstrap::TWIG_NAMESPACE);
        } catch (LoaderError $exception) {
            $this->logger->error('Branding Twig namespace could not be registered', [
                'event' => TwigEnvironmentEvent::EVENT_CREATED,
                'namespace' => Bootstrap::TWIG_NAMESPACE,
                'directory' => $this->templateDirectory,
                'exception' => $exception,
            ]);
        }
    }

    /**
     * (b) Listener for the TemplatePageEvent CLASS (dispatched unnamed by the SMART path).
     *
     * Any page other than the SMART style contract is left untouched -- including the
     * login page, which LoginTemplateListener owns and which must keep the core layout.
     *
     * The catch is a \LogicException|\RuntimeException union rather than \Throwable: see
     * StyleInjectionListener::onStyleFilter() for the guardrail that forces that and why
     * those two types are the branding runtime plane's complete throw surface.
     */
    public function onTemplatePage(TemplatePageEvent $event): void
    {
        if ($event->getPageName() !== self::SMART_STYLE_PAGE) {
            return;
        }

        try {
            $variant = $this->branding->themeVariant();
        } catch (\LogicException | \RuntimeException $exception) {
            $this->logger->error('Branding theme variant could not be resolved for the SMART style contract', [
                'page' => self::SMART_STYLE_PAGE,
                'exception' => $exception,
            ]);

            return;
        }

        $suffix = $this->variantSuffix($variant);

        if (!$this->shipsTemplateFor($suffix)) {
            $this->logger->warning('Branding SMART style template is missing; core template left in place', [
                'page' => self::SMART_STYLE_PAGE,
                'variant' => $suffix,
                'directory' => $this->templateDirectory,
            ]);

            return;
        }

        $event->setTwigTemplate(sprintf(self::SMART_STYLE_TEMPLATE_FORMAT, $suffix));
    }

    /**
     * Whether this module actually carries the SMART contract for a variant.
     *
     * Checked on disk rather than through the loader, because the rewrite decision is made
     * on the TemplatePageEvent, which carries no Twig environment.
     */
    private function shipsTemplateFor(string $suffix): bool
    {
        $relativePath = sprintf(self::SMART_STYLE_RELATIVE_FORMAT, $suffix);

        return is_file(
            rtrim($this->templateDirectory, '/\\')
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relativePath)
        );
    }

    /**
     * The filename fragment for a variant.
     *
     * Exhaustive match with no default: PHPStan verifies both cases are handled, so a
     * third variant cannot ship without a template being authored for it.
     */
    private function variantSuffix(ThemeVariant $variant): string
    {
        return match ($variant) {
            ThemeVariant::Light => 'light',
            ThemeVariant::Dark => 'dark',
        };
    }
}
