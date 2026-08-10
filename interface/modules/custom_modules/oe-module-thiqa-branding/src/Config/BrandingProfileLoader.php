<?php

/**
 * Parses the declarative branding profile into typed, enum-keyed values.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Config;

use InvalidArgumentException;
use JsonException;

/**
 * The parsing boundary for the profile file, in the same sense that
 * {@see BrandingConfigFactory} is the parsing boundary for the globals bag: past this
 * class nothing is `mixed`, nothing is a raw key string, and nothing needs re-checking.
 *
 * What it refuses, and why each refusal exists rather than a coercion:
 *
 *  - **A key outside the two closed enums.** A typo like `openemr_nane` would otherwise
 *    write a global nobody reads, and the profile would report success while the product
 *    still said OpenEMR. Silence is the failure mode being designed out.
 *  - **A key the materialiser owns** — the revision counter, the token overlays, the
 *    materialisation timestamp. A hand-edited file must not be able to forge a revision
 *    or backdate a materialisation; those are outputs of a run, not inputs to one.
 *  - **A value longer than `globals.gl_value` holds.** MySQL would truncate a varchar(255)
 *    overflow, and a truncated URL or tagline is worse than a rejected one: it looks
 *    applied. The bound is counted in characters, which is what `varchar(255)` limits,
 *    not bytes — the Arabic values would fail a byte count while fitting the column
 *    perfectly well.
 *  - **A value that does not match the key's declared type.** `show_tagline_on_login`
 *    taking `yes` is a review error even though the runtime would read it as true, because
 *    the next reader of the table cannot tell it from a mistake.
 *
 * Nothing here touches a database or the filesystem beyond reading the named file, so it
 * is exercised entirely by the isolated suite.
 */
final readonly class BrandingProfileLoader
{
    /** `globals.gl_value` is `varchar(255)`, counted in characters. */
    public const MAX_VALUE_LENGTH = 255;

    private const REQUIRED_ROW_KEYS = ['inventory_id', 'key', 'value'];

    /** The profile shipped with the module. */
    public static function defaultProfilePath(): string
    {
        return dirname(__DIR__, 2) . '/config/branding-profile.json';
    }

    /**
     * @throws InvalidArgumentException when the file is unreadable or the profile is invalid.
     */
    public function load(string $path): BrandingProfile
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException('Branding profile is not a readable file: ' . $path);
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new InvalidArgumentException('Branding profile could not be read: ' . $path);
        }

        return $this->parse($raw, $path);
    }

    /**
     * @throws InvalidArgumentException when the document is invalid.
     */
    public function parse(string $json, string $origin): BrandingProfile
    {
        try {
            $decoded = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // The parser message names byte offsets and is of no use to a reviewer; the
            // origin is.
            throw new InvalidArgumentException('Branding profile is not valid JSON: ' . $origin);
        }

        if (!is_array($decoded)) {
            throw new InvalidArgumentException('Branding profile must be a JSON object: ' . $origin);
        }

        $name = $this->requiredString($decoded, 'profile', $origin);
        $productName = $this->requiredString($decoded, 'product_name', $origin);
        $sourceDocument = $this->requiredString($decoded, 'source_document', $origin);

        $rows = $decoded['globals'] ?? null;
        if (!is_array($rows) || $rows === []) {
            throw new InvalidArgumentException(
                'Branding profile must carry a non-empty "globals" array: ' . $origin,
            );
        }

        $entries = [];
        foreach (array_values($rows) as $position => $row) {
            $entries[] = $this->entry($row, $position, $origin);
        }

        return BrandingProfile::fromEntries($name, $productName, $sourceDocument, $entries);
    }

    /**
     * @param mixed $row
     *
     * @throws InvalidArgumentException
     */
    private function entry(mixed $row, int $position, string $origin): BrandingProfileEntry
    {
        $where = 'row ' . ($position + 1) . ' of ' . $origin;

        if (!is_array($row)) {
            throw new InvalidArgumentException('Branding profile ' . $where . ' is not an object.');
        }

        foreach (self::REQUIRED_ROW_KEYS as $required) {
            if (!array_key_exists($required, $row)) {
                throw new InvalidArgumentException(
                    'Branding profile ' . $where . ' is missing "' . $required . '".',
                );
            }
        }

        $rawKey = $row['key'];
        if (!is_string($rawKey) || $rawKey === '') {
            throw new InvalidArgumentException('Branding profile ' . $where . ' has a non-string "key".');
        }

        $key = $this->resolveKey($rawKey, $where);

        $value = $row['value'];
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' ("' . $rawKey . '") must give "value" as a string; '
                . 'globals.gl_value is a varchar and every value is stored in its string form.',
            );
        }

        $this->assertValueFits($rawKey, $value, $where);
        $this->assertValueMatchesType($key, $rawKey, $value, $where);

        $inventoryId = $row['inventory_id'];
        if (!is_string($inventoryId) || $inventoryId === '') {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' ("' . $rawKey . '") needs an "inventory_id" so the value '
                . 'can be traced back to the branding inventory.',
            );
        }

        return new BrandingProfileEntry(
            $key,
            $value,
            $inventoryId,
            $this->optionalRow($row, $rawKey, $where),
            $this->optionalString($row, 'value_ar'),
            $this->optionalString($row, 'note') ?? '',
        );
    }

    /**
     * @throws InvalidArgumentException when the key is outside both closed enums, or is one
     *         the materialiser owns.
     */
    private function resolveKey(string $rawKey, string $where): BrandingGlobalKey|SuppressionGlobalKey
    {
        $key = BrandingGlobalKey::tryFrom($rawKey) ?? SuppressionGlobalKey::tryFrom($rawKey);

        if ($key === null) {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' names "' . $rawKey . '", which is not a case of '
                . 'BrandingGlobalKey or SuppressionGlobalKey. The profile may only write globals one of '
                . 'those closed enums declares.',
            );
        }

        if ($key instanceof BrandingGlobalKey && $this->isMaterialiserOwned($key)) {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' names "' . $rawKey . '", which the materialiser owns. '
                . 'Revisions, token overlays and materialisation timestamps are outputs of a '
                . 'thiqa-branding:materialise run and must not be declared in a profile.',
            );
        }

        return $key;
    }

    /**
     * Revision, both token overlays and the materialisation timestamp.
     *
     * Derived from the value type rather than listed by hand wherever possible, so a new
     * materialiser-owned key of an existing type is covered without editing this method.
     * `Revision` is named explicitly because it is an ordinary integer and nothing in its
     * type distinguishes it.
     */
    private function isMaterialiserOwned(BrandingGlobalKey $key): bool
    {
        if ($key === BrandingGlobalKey::Revision) {
            return true;
        }

        return match ($key->valueType()) {
            BrandingValueType::TokenJson, BrandingValueType::Timestamp => true,
            BrandingValueType::Text, BrandingValueType::Integer, BrandingValueType::Flag => false,
        };
    }

    /**
     * @throws InvalidArgumentException when the value cannot survive a round trip through
     *         `globals.gl_value`.
     */
    private function assertValueFits(string $rawKey, string $value, string $where): void
    {
        if (!mb_check_encoding($value, 'UTF-8')) {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' ("' . $rawKey . '") is not valid UTF-8.',
            );
        }

        $length = mb_strlen($value, 'UTF-8');
        if ($length > self::MAX_VALUE_LENGTH) {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' ("' . $rawKey . '") is ' . $length . ' characters; '
                . 'globals.gl_value holds at most ' . self::MAX_VALUE_LENGTH . '. MySQL would truncate '
                . 'it, which looks applied but is not.',
            );
        }
    }

    /**
     * @throws InvalidArgumentException when the value contradicts the key's declared type.
     */
    private function assertValueMatchesType(
        BrandingGlobalKey|SuppressionGlobalKey $key,
        string $rawKey,
        string $value,
        string $where,
    ): void {
        if ($key instanceof SuppressionGlobalKey && !in_array($value, $key->permittedValues(), true)) {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' ("' . $rawKey . '") must be one of '
                . implode(', ', $key->permittedValues()) . '.',
            );
        }

        $problem = match ($key->valueType()) {
            BrandingValueType::Flag => in_array($value, ['0', '1'], true)
                ? null
                : 'must be "0" or "1"; OpenEMR stores flags in that form',
            BrandingValueType::Integer => $value !== '' && ctype_digit($value)
                ? null
                : 'must be a whole number written in digits',
            BrandingValueType::Text => str_contains($value, "\n") || str_contains($value, "\r")
                ? 'must be a single line'
                : null,
            // Unreachable: resolveKey() has already refused every key of these types.
            BrandingValueType::TokenJson, BrandingValueType::Timestamp => 'is materialiser-owned',
        };

        if ($problem !== null) {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' ("' . $rawKey . '") ' . $problem . '.',
            );
        }
    }

    /**
     * @param array<mixed> $document
     *
     * @throws InvalidArgumentException
     */
    private function requiredString(array $document, string $field, string $origin): string
    {
        $value = $document[$field] ?? null;
        if (!is_string($value) || $value === '') {
            throw new InvalidArgumentException(
                'Branding profile is missing the "' . $field . '" field: ' . $origin,
            );
        }

        return $value;
    }

    /**
     * @param array<mixed> $row
     *
     * @throws InvalidArgumentException when present but not a whole number.
     */
    private function optionalRow(array $row, string $rawKey, string $where): ?int
    {
        $value = $row['map_row'] ?? null;
        if ($value === null) {
            return null;
        }

        if (!is_int($value) || $value < 1) {
            throw new InvalidArgumentException(
                'Branding profile ' . $where . ' ("' . $rawKey . '") has a "map_row" that is not a '
                . 'positive whole number. Use null for a row the string map does not number.',
            );
        }

        return $value;
    }

    /**
     * @param array<mixed> $row
     */
    private function optionalString(array $row, string $field): ?string
    {
        $value = $row[$field] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
