<?php

/**
 * The set of branding globals a materialisation intends to write.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

use Countable;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;

/**
 * Plan §4.4 step 3 computes the globals delta "in memory" before anything is applied.
 * This is that value: an immutable key/value map whose keys can only be
 * BrandingGlobalKey cases, so a job cannot name a global outside the layer's closed
 * registry however the Control Plane spelled it.
 *
 * Values are strings because `globals.gl_value` is a `varchar(255)` column; typed
 * reading back out is BrandingConfigFactory's job, driven by
 * BrandingGlobalKey::valueType().
 */
final readonly class GlobalsDelta implements Countable
{
    /**
     * @param array<string, string> $values keyed by BrandingGlobalKey::value, insertion-ordered
     */
    private function __construct(private array $values)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** Wither: a new delta with this key set. A repeated key replaces the earlier value. */
    public function with(BrandingGlobalKey $key, string $value): self
    {
        $values = $this->values;
        $values[$key->value] = $value;

        return new self($values);
    }

    public function has(BrandingGlobalKey $key): bool
    {
        return array_key_exists($key->value, $this->values);
    }

    public function get(BrandingGlobalKey $key): ?string
    {
        return $this->values[$key->value] ?? null;
    }

    /**
     * The keys present, in the order they were set.
     *
     * @return list<BrandingGlobalKey>
     */
    public function keys(): array
    {
        $keys = [];
        foreach (array_keys($this->values) as $name) {
            $key = BrandingGlobalKey::tryFrom($name);
            if ($key instanceof BrandingGlobalKey) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * The raw map, keyed by globals name.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->values;
    }

    public function isEmpty(): bool
    {
        return $this->values === [];
    }

    public function count(): int
    {
        return count($this->values);
    }
}
