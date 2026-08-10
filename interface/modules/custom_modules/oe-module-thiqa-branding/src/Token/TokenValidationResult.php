<?php

/**
 * Outcome of validating a proposed tenant overlay.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Token;

/**
 * All-or-nothing by construction: `resolved()` is non-null only when there are no
 * rejections.
 *
 * A partially applied overlay would be a palette nobody validated — the contrast gates
 * are evaluated against the *merged* set, so dropping one entry and keeping the rest
 * can produce a pair that was never measured. Refusing the whole overlay keeps the
 * tenant on revision n-1, which is the same failure posture as the materialiser
 * (plan §4.4).
 */
final readonly class TokenValidationResult
{
    /**
     * @param list<TokenRejection> $rejections
     */
    private function __construct(
        private ?TokenSet $resolved,
        private array $rejections,
    ) {
    }

    public static function accepted(TokenSet $resolved): self
    {
        return new self($resolved, []);
    }

    public static function rejected(TokenRejection ...$rejections): self
    {
        return new self(null, array_values($rejections));
    }

    public function isValid(): bool
    {
        return $this->rejections === [];
    }

    /** The merged Tier 1 + Tier 2 palette, or null when the overlay was refused. */
    public function resolved(): ?TokenSet
    {
        return $this->resolved;
    }

    /** @return list<TokenRejection> */
    public function rejections(): array
    {
        return $this->rejections;
    }

    /** @return list<string> one human-readable line per rejection, safe to log */
    public function messages(): array
    {
        return array_map(
            static fn (TokenRejection $rejection): string => sprintf(
                '%s: %s',
                $rejection->key,
                $rejection->detail,
            ),
            $this->rejections,
        );
    }
}
