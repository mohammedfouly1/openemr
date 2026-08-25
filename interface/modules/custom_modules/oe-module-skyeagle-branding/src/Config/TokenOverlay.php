<?php

/**
 * A parsed Tier 2 design-token overlay.
 *
 * This type exists so that locked constraint C1 holds structurally rather than by
 * convention. BrandingConfig never carries the overlay as a string: it carries this
 * object, whose only possible contents are token-key/six-digit-hex-colour pairs. A
 * stylesheet, a script tag or any other free text cannot survive fromJson(), so no
 * downstream consumer has to re-check what it was handed.
 *
 * Key allowlisting (which token names a tenant may override) is deliberately NOT done
 * here -- that belongs to the materialiser's TokenValidator, where a rejection is
 * actionable. This class enforces shape only, at the point of reading.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Config;

use JsonException;

final readonly class TokenOverlay
{
    /** Dotted token path, e.g. interactive.primary.default. Nothing else parses. */
    private const KEY_PATTERN = '/^[A-Za-z][A-Za-z0-9]*(?:\.[A-Za-z0-9]+)*$/';

    /** Six-digit hex colour, with the hash. The only value form an overlay may carry. */
    private const VALUE_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    /**
     * Upper bound on overlay size. The canonical token contract has fewer than 40 keys,
     * so anything larger is malformed rather than merely generous.
     */
    private const MAX_ENTRIES = 64;

    /**
     * @param array<string, string> $values
     */
    private function __construct(private array $values)
    {
    }

    /** The identity overlay: Tier 1 tokens apply unchanged. */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Parse a materialised overlay document.
     *
     * Total by design: any malformed input yields an empty overlay rather than an
     * exception. A branding overlay is decoration on top of a complete Tier 1 palette,
     * so degrading to Tier 1 keeps every surface rendered and correctly coloured, while
     * throwing would turn a bad overlay into a broken login page. Rejection is
     * all-or-nothing -- a document with one bad entry is discarded whole, so a partially
     * applied palette can never reach the page.
     */
    public static function fromJson(string $json): self
    {
        $trimmed = trim($json);
        if ($trimmed === '') {
            return self::empty();
        }

        try {
            $decoded = json_decode($trimmed, true, 3, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return self::empty();
        }

        if (!is_array($decoded) || $decoded === [] || count($decoded) > self::MAX_ENTRIES) {
            return self::empty();
        }

        $values = [];
        foreach ($decoded as $key => $value) {
            if (!is_string($key) || preg_match(self::KEY_PATTERN, $key) !== 1) {
                return self::empty();
            }

            if (!is_string($value) || preg_match(self::VALUE_PATTERN, $value) !== 1) {
                return self::empty();
            }

            $values[$key] = strtoupper($value);
        }

        return new self($values);
    }

    public function isEmpty(): bool
    {
        return $this->values === [];
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->values;
    }
}
