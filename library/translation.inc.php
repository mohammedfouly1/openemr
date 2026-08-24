<?php

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Translation\TranslationCache;
use OpenEMR\Core\OEGlobalsBag;

if (!(function_exists('xlWarmCache'))) {
    /**
     * Warm the translation cache by loading all translations for the current language.
     * Call this early in the request lifecycle for best performance.
     */
    function xlWarmCache(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $language_choice = $session->get('language_choice');
        $lang_id = !empty($language_choice) ? (int)$language_choice : 1;
        TranslationCache::warm($lang_id);
    }
}

if (!(function_exists('xl'))) {
    /**
     * Translation function - the translation engine for OpenEMR
     *
     * Translates a given constant string into the current session language.
     * Note: In some installation scenarios this function may already be declared,
     * so we check to ensure it hasn't been declared yet.
     *
     * The parameter is typed `literal-string` on purpose: translatable text is
     * looked up by exact match against the lang_constants table, so it must be a
     * source-code literal that the string-extraction tooling can collect. Passing
     * a dynamic value (a database column, request input, a concatenation) cannot
     * be translated and signals that the value should have been narrowed to a
     * known string at the call site instead of handed to xl().
     *
     * @param literal-string $constant The text constant to translate
     * @return string The translated string
     */
    function xl($constant)
    {
        if (OEGlobalsBag::getInstance()->getBoolean('disable_translation') || !empty(OEGlobalsBag::getInstance()->get('temp_skip_translations'))) {
            return $constant;
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $language_choice = $session->get('language_choice');
        // set language id
        $lang_id = !empty($language_choice) ? $language_choice : 1;

        // TRANSLATE
        // first, clean lines
        // convert new lines to spaces and remove windows end of lines
        $patterns =  ['/\n/','/\r/'];
        $replace =  [' ',''];
        $constant = preg_replace($patterns, $replace, $constant);

        // Check cache first
        if (TranslationCache::has($lang_id, $constant)) {
            $string = TranslationCache::get($lang_id, $constant);
        } elseif (TranslationCache::isWarmed()) {
            // Cache is warmed but constant not found - no translation exists
            $string = '';
        } else {
            // Cache not warmed, query database
            $sql = <<<'SQL'
                SELECT lang_definitions.definition
                  FROM lang_definitions
                  JOIN lang_constants
                 USING (cons_id)
                 WHERE lang_definitions.lang_id = ?
                   AND lang_constants.constant_name = ?
                 LIMIT 1
                SQL;
            $res = sqlStatementNoLog($sql, [$lang_id, $constant]);
            $row = sqlFetchArray($res);
            $string = $row['definition'] ?? '';
            // Cache for future lookups this request
            TranslationCache::set($lang_id, $constant, $string);
        }

        if ($string == '') {
            $string = "$constant";
        }
        // remove dangerous characters and remove comments
        if (OEGlobalsBag::getInstance()->getBoolean('translate_no_safe_apostrophe')) {
            $patterns =  ['/\n/','/\r/','/\{\{.*\}\}/'];
            $replace =  [' ','',''];
            $string = preg_replace($patterns, $replace, (string) $string);
        } else {
            // convert apostrophes and quotes to safe apostrophe
            $patterns =  ['/\n/','/\r/','/"/',"/'/",'/\{\{.*\}\}/'];
            $replace =  [' ','','`','`',''];
            $string = preg_replace($patterns, $replace, (string) $string);
        }

        return $string;
    }
}

/**
 * Translate a phrase that carries the product name, with the name composed in afterwards.
 *
 * The problem this solves: a message such as "OpenEMR requires Javascript to perform user
 * authentication." bakes the product name into the translatable key. Every locale's translation
 * then carries it too, so the name cannot be changed without either editing every translation or
 * orphaning the key. Writing `xlp('%s requires Javascript to perform user authentication.')`
 * instead keeps one translatable unit per locale while the name stays configuration.
 *
 * It also reaches a class of leak that catalogue overrides cannot. Many of these literals have no
 * `lang_constants` row at all, so there is nothing for a translation override to override; the
 * only ways to fix them are a source edit or this composition, and this composition is the one
 * that also keeps working after the next rename.
 *
 * Returns the composed string **unescaped**, exactly like `xl()`, so the caller applies the
 * escaper its context needs (`text()`, `attr()`, …). That mirrors the `xlp` Twig filter and the
 * compose-then-escape-once rule `sql_upgrade.php` follows.
 *
 * `ProductContextTranslation` accepts only `%s`, `%1$s` and a literal `%%`, so a catalogue entry
 * can never turn into an arbitrary format string, and a translation that has lost its placeholder
 * raises rather than silently dropping the product name.
 *
 * @param string $pattern translatable text containing exactly one product-name placeholder
 * @return string the translated pattern with the configured product name composed in
 */
function xlp($pattern)
{
    return \OpenEMR\Common\Translation\ProductContextTranslation::compose(
        xl($pattern),
        \OpenEMR\Core\OEGlobalsBag::getInstance()->getString('openemr_name'),
    );
}

/**
 * The product name to display for the session's own language.
 *
 * Finding S2-P1-24: `saas_branding_product_name_ar` is populated and the branding layer has code to
 * consume it, yet the authenticated Arabic shell still rendered `<title>Thiqa</title>` and
 * `WindowTitleBase = "Thiqa"` — the UI chrome around them genuinely translated, the product name
 * alone still Latin. Those surfaces read `openemr_name` unconditionally, so they never had a chance
 * to pick the Arabic variant.
 *
 * **The predicate is the language, not the direction.** `lang_languages` marks four locales RTL —
 * Hebrew, Arabic, Persian and Urdu — and an Arabic wordmark is correct for exactly one of them.
 * Keying on `lang_is_rtl` would put Arabic script in front of Hebrew and Persian users, which is a
 * worse error than the one being fixed.
 *
 * Degrades in both directions rather than failing: no Arabic name configured, or any session that
 * is not Arabic, yields `openemr_name` unchanged. That also keeps this usable when the branding
 * layer is not installed at all, since `saas_branding_product_name_ar` is then simply absent.
 *
 * @return string the configured product name, in Arabic when the session language is Arabic
 */
function xl_product_name()
{
    static $resolved = null;
    if ($resolved !== null) {
        return $resolved;
    }

    $globals = OEGlobalsBag::getInstance();
    $name = $globals->getString('openemr_name');

    if (!$globals->has('saas_branding_product_name_ar')) {
        return $resolved = $name;
    }

    $arabic = trim($globals->getString('saas_branding_product_name_ar'));
    if ($arabic === '') {
        return $resolved = $name;
    }

    return $resolved = (getLanguageCode(xl_session_language_id()) === 'ar') ? $arabic : $name;
}

/**
 * The lang_id this session is reading in, defaulting to English exactly as xl() does.
 *
 * @return int
 */
function xl_session_language_id()
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $choice = $session->get('language_choice');

    return !empty($choice) ? (int) $choice : 1;
}

/**
 * The ISO code of a language, or '' when it cannot be resolved.
 *
 * Mirrors getLanguageDir()'s shape: one small lookup, memoised per language so a page rendering
 * the product name more than once does not repeat the query.
 *
 * @param int $lang_id language code
 * @return string
 */
function getLanguageCode($lang_id)
{
    static $codes = [];

    $lang_id = empty($lang_id) ? 1 : (int) $lang_id;
    if (array_key_exists($lang_id, $codes)) {
        return $codes[$lang_id];
    }

    $row = sqlQuery('SELECT lang_code FROM lang_languages WHERE lang_id = ?', [$lang_id]);

    return $codes[$lang_id] = (string) ($row['lang_code'] ?? '');
}

// ----------- xl() function wrappers ------------------------------
//
// Use above xl() function the majority of time for translations. The
//  below wrappers are only for specific situations in order to support
//  granular control of translations in certain parts of OpenEMR.
//  Wrappers:
//    xlp()
//    xl_list_label()
//    xl_layout_label()
//    xl_gacl_group()
//    xl_form_title()
//    xl_document_category()
//    xl_appt_category()
//
/**
 * Conditionally translates list labels based on global setting
 *
 * Only translates if $GLOBALS['translate_lists'] is set to true.
 * Added 5-09 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_list_label($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_lists') ? xl($constant) : $constant;
}

/**
 * Conditionally translates layout labels based on global setting
 *
 * Only translates if $GLOBALS['translate_layout'] is set to true.
 * Added 5-09 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_layout_label($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_layout') ? xl($constant) : $constant;
}

/**
 * Conditionally translates access control group labels based on global setting
 *
 * Only translates if $GLOBALS['translate_gacl_groups'] is set to true.
 * Added 6-2009 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_gacl_group($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_gacl_groups') ? xl($constant) : $constant;
}

/**
 * Conditionally translates patient form (notes) titles based on global setting
 *
 * Only translates if $GLOBALS['translate_form_titles'] is set to true.
 * Added 6-2009 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_form_title($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_form_titles') ? xl($constant) : $constant;
}

/**
 * Conditionally translates document categories based on global setting
 *
 * Only translates if $GLOBALS['translate_document_categories'] is set to true.
 * Added 6-2009 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_document_category($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_document_categories') ? xl($constant) : $constant;
}

/**
 * Conditionally translates appointment categories based on global setting
 *
 * Only translates if $GLOBALS['translate_appt_categories'] is set to true.
 * Added 6-2009 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_appt_category($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_appt_categories') ? xl($constant) : $constant;
}
// ---------------------------------------------------------------------------

// ---------------------------------
// Miscellaneous language translation functions

// Function to return the title of a language from the id
// @param integer (language id)
// return string (language title)
function getLanguageTitle($val)
{

 // validate language id
    $lang_id = !empty($val) ? $val : 1;

 // get language title
    $res = sqlStatement("select lang_description from lang_languages where lang_id =?", [$lang_id]);
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result[$iter] = $row;
    };
    $languageTitle = $result[0]["lang_description"];
    return $languageTitle;
}




/**
 * Returns language directionality as string 'rtl' or 'ltr'
 * @param int $lang_id language code
 * @return string 'ltr' 'rtl'
 * @author Amiel <amielel@matrix.co.il>
 */
function getLanguageDir($lang_id)
{
    // validate language id
    $lang_id = empty($lang_id) ? 1 : $lang_id;
    // get language code
    $row = sqlQuery('SELECT * FROM lang_languages WHERE lang_id = ?', [$lang_id]);

    return !empty($row['lang_is_rtl']) ? 'rtl' : 'ltr';
}
