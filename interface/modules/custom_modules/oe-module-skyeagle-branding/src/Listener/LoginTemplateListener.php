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

namespace OpenEMR\Modules\SkyEagleBranding\Listener;

use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\Language\SessionLanguageInterface;
use OpenEMR\Modules\SkyEagleBranding\Service\BrandingServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Extension point E5 (plan section 3.6): TemplatePageEvent::RENDER_EVENT
 * ('events.core.page'), dispatched from interface/login/login.php.
 *
 * The variable that justifies the class is `primaryLogoAlt` (**BRAND-053**). Core's
 * partial hardcodes `alt=""` on both login logos, which announces a hospital's identity
 * mark as decorative and leaves a screen-reader user with nothing where the wordmark is.
 * The accessible name is resolved service-side, in the page's own reading language, and
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
 * ## The four logo URLs ARE rewritten, and why that is not a contradiction
 *
 * `primaryLogo`, `secondaryLogo`, `smallLogoOne` and `smallLogoTwo` are core-owned too,
 * but the rewrite here is **additive**: the same `LogoService` path core resolved, with
 * `&rev=<branding revision>` appended (BrandAssetResolver preserves core's `?t=<mtime>`
 * and fixes the parameter order). Nothing about which file is served changes.
 *
 * This exists because locked Q76 requires that "cache keys for branding resources MUST
 * incorporate a tenant-safe revision", and nothing was satisfying it: core call sites go
 * straight to LogoService::getLogo(), so BrandAssetResolver -- which does append the
 * revision correctly -- was never in the path of a rendered page. A live login render
 * showed `logo.png?t=1786356307` with no revision at all (docs/RebrandingBugs.md RB-03).
 *
 * It has to happen here rather than in LogoOverrideListener because LogoService re-filters
 * any listener-modified path through ModulesApplication::filterSafeLocalModuleFiles(),
 * which keeps only paths under interface/modules/ -- a rev-stamped `/public/images/...`
 * path would be replaced with the empty string and the logo would vanish. The template
 * variable is the only seam that can carry a revision on a core-resolved asset.
 *
 * Each key is rewritten only when the branding layer actually resolved that slot, so an
 * unresolved slot leaves core's own value untouched rather than blanking it.
 *
 * Plane 3 constraint (locked Q76 / C5): every BRANDING value comes from data already in
 * memory. No network call.
 *
 * **One qualified exception, added with SKY-F01 and stated here rather than left to be
 * discovered.** `SessionLanguageInterface::isArabic()` is not a branding value and does not
 * come from `BrandingConfig`; the shipped adapter resolves it through core's
 * `getLanguageCode()`, which is one indexed `lang_languages` lookup, memoised per language
 * for the request. That is a database query, so the blanket "no database query" this docblock
 * used to claim is no longer true and has been narrowed rather than quietly left standing.
 *
 * It is a deliberate trade, on these grounds: core already performs the identical lookup on
 * every page load -- `interface/globals.php:566` calls `getLanguageDir()` to decide the `rtl_`
 * stylesheet, which is the very value `isRtl()` reads back -- so the query is not new work on
 * this page, and the login page reaches the database many times over through `xl()` regardless.
 * The alternative, keeping the direction proxy, is what SKY-F01 is. The dependency is injected,
 * so a caller that genuinely cannot query passes an implementation that does not.
 */
final readonly class LoginTemplateListener
{
    /** Page name interface/login/login.php dispatches under. */
    public const LOGIN_PAGE = 'login/login.php';

    /**
     * Login view variable => the slot whose revision-stamped URL replaces it.
     *
     * These four names are core's own (interface/login/login.php:246-263). The mapping is
     * explicit rather than derived so that adding a slot to LogoSlot cannot silently start
     * rewriting a template variable nobody intended.
     *
     * @var array<string, LogoSlot>
     */
    private const LOGO_VIEW_KEYS = [
        'primaryLogo' => LogoSlot::CoreLoginPrimary,
        'secondaryLogo' => LogoSlot::CoreLoginSecondary,
        'smallLogoOne' => LogoSlot::CoreLoginSmallPrimary,
        'smallLogoTwo' => LogoSlot::CoreLoginSmallSecondary,
    ];

    public function __construct(
        private BrandingServiceInterface $branding,
        private SessionLanguageInterface $language,
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
        // SKY-F01: the predicate is the LANGUAGE, not the direction. This read used to be
        // `$this->branding->isRtl()`, which is true for all four right-to-left locales --
        // Hebrew, Arabic, Persian and Urdu -- so a Hebrew login page was announcing its logo
        // to a screen reader as `شعار ثقة`. `library/translation.inc.php:149-152` names that
        // exact substitution as "a worse error than the one being fixed"; the login page was
        // making it. isRtl() keeps its job -- deciding layout -- and answers nothing about
        // which script the reader wants.
        $arabic = $this->language->isArabic();

        $candidates = [
            // BRAND-053: the accessible name core hardcodes as "".
            'primaryLogoAlt' => $this->branding->logo(LogoSlot::CoreLoginPrimary)->alt($arabic),
            'secondaryLogoAlt' => $this->branding->logo(LogoSlot::CoreLoginSecondary)->alt($arabic),
            'brandProductName' => $this->branding->productName($arabic),
            'brandTagline' => $this->branding->tagline($arabic) ?? '',
        ];

        // C8 / MVP-010 AC-4: re-publish each logo URL carrying the branding revision.
        // Same file, same core-resolved path, plus &rev= — see the class docblock for why
        // this cannot be done from LogoOverrideListener.
        foreach (self::LOGO_VIEW_KEYS as $viewKey => $slot) {
            $asset = $this->branding->logo($slot);
            if ($asset->isResolved()) {
                $candidates[$viewKey] = $asset->url();
            }
        }

        return array_filter($candidates, static fn (string $value): bool => $value !== '');
    }
}
