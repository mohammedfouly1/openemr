<?php

/**
 * Where the product name sits relative to a phrase that is already translated.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * Used only to carry an existing translation forward onto a placeholder-bearing key.
 *
 * A template that writes `{{ "About"|xlt }} {{ applicationTitle }}` has already made a word-order
 * decision, in PHP, that no translator can reach. Moving it to the key `About %s` gives the
 * translator control — but the new key starts with no translations at all, so every locale that
 * had one would silently drop to English. That is the RB-01 failure mode: a rename that orphans
 * the catalogue.
 *
 * The carry-forward avoids it without inventing grammar. Each locale's new pattern is its own
 * existing translation with `%s` placed where the *source template* put the product name, so the
 * rendered string is byte-identical to today in every language. Nothing is authored and nothing is
 * translated; only the join point moves, from PHP into catalogue data a translator can then reorder
 * properly.
 *
 * Backed by a string because the value is written in the checked-in contract JSON.
 */
enum TranslationPlacement: string
{
    /** The product name led the phrase: `Thiqa Authorization` -> `%s Authorization`. */
    case Prefix = 'prefix';

    /** The product name trailed the phrase: `About Thiqa` -> `About %s`. */
    case Suffix = 'suffix';

    /**
     * Insert the placeholder into one already-translated phrase.
     *
     * A single space is the separator because a single space is exactly what the source
     * templates emit between the two halves. Anything else would change rendering.
     */
    public function applyTo(string $translatedPhrase): string
    {
        return match ($this) {
            self::Prefix => '%s ' . $translatedPhrase,
            self::Suffix => $translatedPhrase . ' %s',
        };
    }
}
