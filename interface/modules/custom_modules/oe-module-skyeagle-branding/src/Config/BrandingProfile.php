<?php

/**
 * A parsed, validated declarative branding profile.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Config;

use Countable;
use InvalidArgumentException;

/**
 * The in-memory form of `config/branding-profile.json`, and the only shape
 * {@see \OpenEMR\Modules\SkyEagleBranding\Console\ApplyProfileCommand} will act on.
 *
 * Construction is private and goes through {@see self::fromEntries()}, which is where
 * the one structural invariant lives: a profile may not name the same global twice.
 * Two rows for one key would make the applied value depend on iteration order, and a
 * profile whose meaning depends on iteration order is not reviewable as a table — which
 * is the whole point of keeping it as data.
 *
 * Ordering is preserved from the file. The apply command prints rows in that order, so
 * a diff of the printed table lines up with a diff of the JSON.
 *
 * This object holds no defaults and no fallbacks. Everything it can answer, it was told.
 * {@see BrandingGlobalKey::defaultValue()} remains the runtime fallback for a global
 * that was never written; the profile is the record of what *will* be written.
 */
final readonly class BrandingProfile implements Countable
{
    /**
     * @param array<string, BrandingProfileEntry> $entries keyed by globals name, in file order
     */
    private function __construct(
        public string $name,
        public string $productName,
        public string $sourceDocument,
        private array $entries,
    ) {
    }

    /**
     * @param list<BrandingProfileEntry> $entries
     *
     * @throws InvalidArgumentException when two rows name the same global.
     */
    public static function fromEntries(
        string $name,
        string $productName,
        string $sourceDocument,
        array $entries,
    ): self {
        $indexed = [];
        foreach ($entries as $entry) {
            $globalName = $entry->globalName();
            if (array_key_exists($globalName, $indexed)) {
                throw new InvalidArgumentException(
                    'Branding profile names the global "' . $globalName . '" more than once.',
                );
            }

            $indexed[$globalName] = $entry;
        }

        return new self($name, $productName, $sourceDocument, $indexed);
    }

    /**
     * Every row, in file order.
     *
     * @return list<BrandingProfileEntry>
     */
    public function entries(): array
    {
        return array_values($this->entries);
    }

    /**
     * The globals this profile writes, in file order.
     *
     * @return list<string>
     */
    public function globalNames(): array
    {
        return array_keys($this->entries);
    }

    public function entryFor(BrandingGlobalKey|SuppressionGlobalKey $key): ?BrandingProfileEntry
    {
        return $this->entries[$key->value] ?? null;
    }

    public function has(BrandingGlobalKey|SuppressionGlobalKey $key): bool
    {
        return array_key_exists($key->value, $this->entries);
    }

    /** The string this profile would write for a key, or null when it does not name it. */
    public function valueFor(BrandingGlobalKey|SuppressionGlobalKey $key): ?string
    {
        return ($this->entries[$key->value] ?? null)?->value;
    }

    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    public function count(): int
    {
        return count($this->entries);
    }
}
