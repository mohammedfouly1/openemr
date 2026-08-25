<?php

/**
 * One reviewed row of the declarative branding profile.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Config;

/**
 * A profile row after parsing: an enum-typed key, the exact string that will reach
 * `globals.gl_value`, and the provenance a reviewer needs to check the row against the
 * string replacement map without leaving the code.
 *
 * The provenance fields are deliberately part of the value, not comments in the JSON
 * that get dropped at parse time. A branding change that nobody can trace back to an
 * inventory ID is a branding change nobody can audit, and the apply command prints the
 * ID beside the value for exactly that reason.
 *
 * `arabicValue` is carried, never written to this key's row. `globals` stores one value
 * per key; the Arabic strings belong to the translation catalogues. The one exception is
 * {@see BrandingGlobalKey::ProductNameArabic}, whose *English-slot* value simply is the
 * Arabic product name — there the two fields agree, which is the signal that the row is
 * the Arabic carrier rather than an untranslated leftover.
 */
final readonly class BrandingProfileEntry
{
    public function __construct(
        public BrandingGlobalKey|SuppressionGlobalKey $key,
        public string $value,
        public string $inventoryId,
        public ?int $mapRow,
        public ?string $arabicValue,
        public string $note,
    ) {
    }

    /** The literal `globals.gl_name` this row writes. */
    public function globalName(): string
    {
        return $this->key->value;
    }

    /** Short human name for the apply command's table. */
    public function label(): string
    {
        return $this->key->label();
    }

    /** True when the row targets a global the branding layer itself owns. */
    public function isBrandingGlobal(): bool
    {
        return $this->key instanceof BrandingGlobalKey;
    }

    /**
     * Provenance rendered for a one-line table cell, e.g. `BRAND-001 (map row 1)`.
     */
    public function provenance(): string
    {
        if ($this->mapRow === null) {
            return $this->inventoryId;
        }

        return $this->inventoryId . ' (map row ' . $this->mapRow . ')';
    }
}
