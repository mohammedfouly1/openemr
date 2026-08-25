<?php

/**
 * Resolves the session language through core's own resolver, so the product has one rule.
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
 * The adapter, and the only class in this module that reaches into global state.
 *
 * It delegates to `xl_session_language_id()` and `getLanguageCode()` in
 * `library/translation.inc.php` rather than reimplementing the lookup, because
 * `xl_product_name()` already decides "is this session Arabic?" for the authenticated shell.
 * Two implementations of one predicate is how the shell and the login page came to disagree
 * in the first place (SKY-F01); there is now one, and this class borrows it.
 *
 * ## Why every failure is `false` rather than an exception
 *
 * Three of them are reachable, and none is worth a broken login page:
 *
 *  - **The functions are absent.** `library/translation.inc.php` is loaded by
 *    `interface/globals.php`, which every page requires -- but this module also runs under
 *    `bin/console` and inside the isolated test suite, where it is not. `function_exists()`
 *    keeps the module loadable there instead of fatal.
 *  - **The session cannot be started.** `xl_session_language_id()` calls
 *    `getActiveSession()`, and Symfony raises `RuntimeException: Failed to start the session
 *    because headers have already been sent` when output is already flushed --
 *    `sql_upgrade.php:551` and `sql_patch.php:74` reach `globals.inc.php` in exactly that
 *    state. `xl_product_name()` guards the same call for the same reason.
 *  - **The language row is missing.** `getLanguageCode()` returns `''` for an unknown
 *    `lang_id`, which is not Arabic, which is the correct answer.
 *
 * The catch is `\RuntimeException` specifically, not `\Throwable` or `\Exception`: the project
 * PHPStan guardrail `ForbiddenCatchTypeRule` forbids the broad forms, and a `\Error` here would
 * be a programming fault this class must not swallow.
 *
 * A database failure is deliberately not handled. `sqlQuery()` routes errors through
 * `HelpfulDie()`, which ends in `exit(1)`, so no catch placed here could recover from one.
 */
final class CoreSessionLanguage implements SessionLanguageInterface
{
    /** The ISO 639-1 code `lang_languages` stores for Arabic. */
    private const ARABIC_CODE = 'ar';

    public function isArabic(): bool
    {
        if (!function_exists('xl_session_language_id') || !function_exists('getLanguageCode')) {
            return false;
        }

        try {
            return getLanguageCode(xl_session_language_id()) === self::ARABIC_CODE;
        } catch (\RuntimeException) {
            return false;
        }
    }
}
