<?php

/**
 * The reading language of the current session, as branding needs to know it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Language;

/**
 * Finding SKY-F01: branding was selecting Arabic assets from the page's *direction*.
 *
 * `BrandingServiceInterface::isRtl()` answers "is this page laid out right-to-left?" and it
 * answers it correctly -- it reads the `rtl_` stylesheet prefix that `interface/globals.php`
 * has already resolved. It is the right predicate for layout, and the wrong one for content:
 * `lang_languages` marks **four** locales right-to-left -- Hebrew, Arabic, Persian and Urdu --
 * and an Arabic wordmark or an Arabic accessible name is correct for exactly one of them.
 *
 * `library/translation.inc.php:149-152` states this rule for the authenticated shell in so many
 * words -- "the predicate is the language, not the direction" -- and warns that keying on
 * `lang_is_rtl` "would put Arabic script in front of Hebrew and Persian users, which is a worse
 * error than the one being fixed". The login page was doing exactly that.
 *
 * This interface exists so the listener can ask the question it actually means. It is deliberately
 * a collaborator rather than a static call: locked constraint C5 keeps the branding runtime plane
 * free of global state, and a session lookup is global state. The adapter that reaches core lives
 * at the composition root (see CoreSessionLanguage), which is the one place allowed to know.
 *
 * Narrow on purpose. This is not a general locale service; it answers the single question branding
 * has an Arabic variant for. A second script variant would add a second method, not a `code()`
 * accessor that every caller then has to compare correctly for itself.
 */
interface SessionLanguageInterface
{
    /**
     * True only when the session is actually reading Arabic.
     *
     * Must degrade to false rather than throw: choosing the Latin wordmark is a cosmetic
     * miss, and the login page is the one surface that must render for a user who cannot
     * yet authenticate to report that it did not.
     */
    public function isArabic(): bool;
}
