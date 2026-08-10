<?php

/**
 * Appends the per-tenant token stylesheet to the document head.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Listener;

use OpenEMR\Events\Core\StyleFilterEvent;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Extension point E1 (plan section 3.6): `html.head.style.filter`, dispatched from
 * Core\Header::setupHeader().
 *
 * The listener has exactly one job -- emit the Tier 2 `<link>` when, and only when, the
 * tenant has a validated token overlay. Three properties are load-bearing:
 *
 *  - **Null is the default and the common case.** BrandingServiceInterface::
 *    tokenStylesheetUrl() returns null whenever no overlay is configured, and this
 *    listener then does nothing whatsoever: no array is read back into the event, no
 *    `<link>` is emitted and the browser makes no extra request. The product renders
 *    entirely from the shared immutable bundle (plan section 3.8.1).
 *  - **Append, never replace.** The event carries whatever other modules have already
 *    contributed. The existing array is read, one element is appended and the whole
 *    array is handed back, so no other module's stylesheet is dropped.
 *  - **The URL must live under interface/modules/.** StyleFilterEvent::setStyles() runs
 *    every entry through ModulesApplication::filterSafeLocalModuleFiles(), which keeps
 *    only paths resolving inside the modules tree. The Tier 2 endpoint is
 *    BrandingService::TOKEN_STYLESHEET_RELATIVE_PATH, i.e. this module's own
 *    public/branding-tokens.php, so it satisfies that gate by construction.
 *
 * Plane 3 constraint (locked Q76 / C5): the service answers from data already in memory.
 * Nothing here performs a network call or a database query.
 */
final readonly class StyleInjectionListener
{
    public function __construct(
        private BrandingServiceInterface $branding,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Listener for StyleFilterEvent::EVENT_NAME ('html.head.style.filter').
     *
     * ## Why the catch is a union rather than \Throwable
     *
     * The ForbiddenCatchTypeRule guardrail forbids any catch that would suppress \Error or
     * \ErrorException, which rules out \Throwable and \Exception alike. That is the right
     * call: a TypeError raised inside the branding layer is a programming fault and must
     * reach OpenEMR's global handler rather than disappear into a silently unstyled page.
     *
     * \LogicException and \RuntimeException are not a guess at what might go wrong -- they
     * are the complete set of base types the branding runtime plane throws. Every throw
     * site in Config, Asset, Token, Theme and Accessibility raises LogicException,
     * DomainException, InvalidArgumentException or RuntimeException, all of which land
     * here.
     */
    public function onStyleFilter(StyleFilterEvent $event): void
    {
        try {
            $stylesheetUrl = $this->branding->tokenStylesheetUrl();
        } catch (\LogicException | \RuntimeException $exception) {
            $this->logger->error('Branding token stylesheet could not be resolved', [
                'event' => StyleFilterEvent::EVENT_NAME,
                'page' => $event->getPageName(),
                'exception' => $exception,
            ]);

            return;
        }

        // The default state. Nothing is added and nothing is touched.
        if ($stylesheetUrl === null || $stylesheetUrl === '') {
            return;
        }

        $styles = $event->getStyles();

        // setupHeader() can run more than once on a composed page; a second identical
        // <link> would be harmless but wasteful, and it would break the byte-identical
        // rendering the release verification compares against.
        if (in_array($stylesheetUrl, $styles, true)) {
            return;
        }

        $styles[] = $stylesheetUrl;

        $event->setStyles($styles);
    }
}
