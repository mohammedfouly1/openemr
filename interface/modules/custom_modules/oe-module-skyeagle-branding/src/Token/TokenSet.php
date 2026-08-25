<?php

/**
 * The immutable token collection for one theme variant.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Token;

use Countable;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;

/**
 * A resolved palette: Tier 1 product tokens, optionally overlaid with a validated
 * Tier 2 tenant subset (plan §2.4, §3.4.3).
 *
 * `get()` is nullable rather than total because the two source documents are not
 * symmetric — `surfaceRaised` exists only in the dark palette.
 */
final readonly class TokenSet implements Countable
{
    /** @var array<string, DesignToken> indexed by TokenKey::$value */
    private array $tokens;

    public function __construct(public ThemeVariant $variant, DesignToken ...$tokens)
    {
        $indexed = [];
        foreach ($tokens as $token) {
            // Later wins: this is what makes with() an overlay rather than a merge conflict.
            $indexed[$token->key->value] = $token;
        }

        $this->tokens = $indexed;
    }

    public function get(TokenKey $key): ?DesignToken
    {
        return $this->tokens[$key->value] ?? null;
    }

    public function has(TokenKey $key): bool
    {
        return isset($this->tokens[$key->value]);
    }

    /** The colour for a key, or null when the variant does not define it. */
    public function valueOf(TokenKey $key): ?ColorValue
    {
        return $this->get($key)?->value;
    }

    /**
     * Every token present, in TokenKey declaration order.
     *
     * Ordering is by the enum rather than by insertion so that the rendered CSS is
     * byte-identical for equal inputs — the materialiser checksums it (plan §4.4).
     *
     * @return list<DesignToken>
     */
    public function all(): array
    {
        $ordered = [];
        foreach (TokenKey::cases() as $key) {
            $token = $this->tokens[$key->value] ?? null;
            if ($token instanceof DesignToken) {
                $ordered[] = $token;
            }
        }

        return $ordered;
    }

    /** @return list<TokenKey> */
    public function keys(): array
    {
        return array_map(static fn (DesignToken $token): TokenKey => $token->key, $this->all());
    }

    /** Wither: returns a NEW set with the supplied tokens applied over this one. */
    public function with(DesignToken ...$tokens): self
    {
        return new self($this->variant, ...$this->all(), ...$tokens);
    }

    public function count(): int
    {
        return count($this->tokens);
    }
}
