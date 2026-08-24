<?php

/**
 * What to do with a legacy translation that never named the product.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * Neutralising a brand-bearing key works by replacing the identity literal in each locale's
 * translation with `%s`. That only works if the translation actually contains the literal, and in
 * the shipped catalogue many do not: a translator rendering "Dumping OpenEMR database" into their
 * own language may reasonably have written the equivalent of "Dumping the database". Measured on a
 * real catalogue, every brand-bearing key has between one and four such rows.
 *
 * There is no honest way to insert a placeholder into a sentence that never had one — the position
 * is grammar, and guessing it produces a subtly wrong string in a language the author cannot read.
 * So there are exactly two defensible behaviours, and which one is right depends on the key. This
 * enum makes a contract say which, in the checked-in file, where a reviewer sees it.
 *
 * **It also closes a divergence between the two execution paths.** The generated installer SQL has
 * always skipped these rows (`LOCATE(literal, definition) > 0`), while the PHP upgrade migration
 * threw on them. A fresh install and an upgrade therefore produced different catalogues from the
 * same contract — precisely the class of fresh-install-versus-upgrade drift S2-P0-21 was raised
 * about. With `Skip` the two agree; with `Fail` the upgrade refuses loudly and the contract author
 * is told to supply explicit definitions instead.
 */
enum MissingIdentityPolicy: string
{
    /**
     * Refuse the migration. The default, and correct whenever losing a locale's translation would
     * matter more than the rename: the contract author must then supply an explicit definition for
     * that language rather than let it silently degrade.
     */
    case Fail = 'fail';

    /**
     * Leave the row behind. The neutral key gains no definition for that language, so `xl()` falls
     * back to the key's own English text with the product name composed in.
     *
     * This is a real, bounded loss — that locale sees English at that one call site — and it is
     * chosen deliberately, per contract, in exchange for the product name no longer being frozen
     * into the catalogue. It is never the default.
     */
    case Skip = 'skip';

    /** Whether a definition lacking the identity literal aborts the migration. */
    public function isFatal(): bool
    {
        return $this === self::Fail;
    }
}
