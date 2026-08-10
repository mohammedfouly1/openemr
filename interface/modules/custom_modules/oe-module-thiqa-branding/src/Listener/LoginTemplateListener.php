<?php

/**
 * Supplies branding view variables to the login page render.
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
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Extension point E5 (plan section 3.6): TemplatePageEvent::RENDER_EVENT
 * ('events.core.page'), dispatched from interface/login/login.php.
 *
 * The variable that justifies the class is `primaryLogoAlt` (**BRAND-053**). Core's
 * partial hardcodes `alt=""` on both login logos, which announces a hospital's identity
 * mark as decorative and leaves a screen-reader user with nothing where the wordmark is.
 * The accessible name is resolved service-side, in the page's own reading direction, and
 * handed to the template through the event that already exists -- no core file is edited
 * (locked Invariant 4), and the template reads it as `{{ primaryLogoAlt|default('') }}`
 * so an install without this module renders byte-identically to core.
 *
 * ## Two rules this class will not break
 *
 *  - **Merge, never replace.** The login page is built from ~30 core-supplied variables.
 *    Replacing the array blanks the page. The existing array is read, the branding keys
 *    are merged on top, and the result is handed back. The merge is written out
 *    explicitly rather than leaning on TemplatePageEvent::setTwigVariables() doing it
 *    internally, so the behaviour does not depend on a core implementation detail.
 *  - **Contribute nothing when there is nothing to contribute.** Every candidate is
 *    dropped when its value is empty, and when that leaves nothing the event is not
 *    touched at all. An empty accessible name is not merely useless, it is wrong: it
 *    would override a template default with a value that says "decorative".
 *
 * ## Keys deliberately not written
 *
 * `title` and `tagline` are core-owned keys already populated from the `openemr_name` and
 * `login_tagline_text` globals -- the very globals BrandingConfig reads for its own
 * product name and tagline. Overwriting them would be a no-change in the Latin case and
 * an out-of-scope mutation of a core key in every other. The branded values are published
 * under their own names instead, and templates that want them ask for them.
 *
 * Plane 3 constraint (locked Q76 / C5): every value comes from data already in memory.
 * No network call, no database query.
 */
final readonly class LoginTemplateListener
{
    /** Page name interface/login/login.php dispatches under. */
    public const LOGIN_PAGE = 'login/login.php';

    public function __construct(
        private BrandingServiceInterface $branding,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Listener for TemplatePageEvent::RENDER_EVENT ('events.core.page').
     *
     * The catch is a \LogicException|\RuntimeException union rather than \Throwable: see
     * StyleInjectionListener::onStyleFilter() for the guardrail that forces that and why
     * those two types are the branding runtime plane's complete throw surface. Within that
     * limit, a branding failure degrades the login page to core's own rendering; it never
     * blocks it.
     */
    public function onTemplatePage(TemplatePageEvent $event): void
    {
        if ($event->getPageName() !== self::LOGIN_PAGE) {
            return;
        }

        try {
            $brandingVariables = $this->brandingVariables();
        } catch (\LogicException | \RuntimeException $exception) {
            $this->logger->error('Branding login variables could not be resolved', [
                'event' => TemplatePageEvent::RENDER_EVENT,
                'page' => self::LOGIN_PAGE,
                'exception' => $exception,
            ]);

            return;
        }

        if ($brandingVariables === []) {
            return;
        }

        $event->setTwigVariables(array_merge($event->getTwigVariables(), $brandingVariables));
    }

    /**
     * The branding contribution, with every empty value removed.
     *
     * @return array<string, string>
     */
    private function brandingVariables(): array
    {
        // One direction decision for the whole page: an RTL session gets the Arabic
        // accessible names and the Arabic wordmark, rather than a Latin fallback sitting
        // inside a right-to-left layout.
        $arabic = $this->branding->isRtl();

        $candidates = [
            // BRAND-053: the accessible name core hardcodes as "".
            'primaryLogoAlt' => $this->branding->logo(LogoSlot::CoreLoginPrimary)->alt($arabic),
            'secondaryLogoAlt' => $this->branding->logo(LogoSlot::CoreLoginSecondary)->alt($arabic),
            'brandProductName' => $this->branding->productName($arabic),
            'brandTagline' => $this->branding->tagline($arabic) ?? '',
        ];

        return array_filter($candidates, static fn (string $value): bool => $value !== '');
    }
}
