<?php

/**
 * Gate for Tier 2 tenant token overlays.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Token;

use OpenEMR\Modules\SkyEagleBranding\Accessibility\ContrastCalculatorInterface;

/**
 * Turns MVP-010 AC-2 from a promise into a property (plan §3.4.1): a tenant cannot
 * configure an inaccessible palette, because the overlay is refused before it can be
 * materialised.
 *
 * Three checks, in order:
 *   (a) the key is in the closed allowlist AND TokenKey::isTenantOverridable();
 *   (b) the value parses as a ColorValue;
 *   (c) every contrast pair the overlay disturbs still clears its WCAG 2.2 gate,
 *       measured on the merged palette rather than on the submitted fragment.
 *
 * Validation failures are reported, not thrown — a tenant sending a bad colour is an
 * expected event. Exceptions here would mean programmer error only.
 *
 * This runs tenant-side at materialisation as well as Control-Plane-side, because the
 * tenant does not trust the Control Plane blindly (plan §3.9).
 */
final readonly class TokenValidator
{
    /**
     * Longest key string that is even looked at.
     *
     * The longest real key is `interactive.secondary.default` (29 bytes). Anything past
     * this is refused on length so no oversized attacker-controlled string is ever fed
     * to a matcher or copied into a report.
     */
    public const MAX_KEY_LENGTH = 64;

    /** Product rule, not a WCAG threshold: inactive controls are exempt from 1.4.3/1.4.11. */
    public const DISABLED_STATE_SEPARATION_MINIMUM = 1.5;

    public function __construct(
        private ContrastCalculatorInterface $contrast,
    ) {
    }

    /**
     * @param array<array-key, mixed> $overlay raw decoded tenant overlay: this is the
     *                                         trust boundary, so the input is genuinely
     *                                         untyped and is narrowed immediately below
     * @param TokenSet                $base    the Tier 1 palette for the same variant
     */
    public function validateOverlay(array $overlay, TokenSet $base): TokenValidationResult
    {
        $rejections = [];
        /** @var list<DesignToken> $accepted */
        $accepted = [];
        /** @var list<TokenKey> $touched */
        $touched = [];

        foreach ($overlay as $rawKey => $rawValue) {
            $keyText = is_string($rawKey) ? $rawKey : (string) $rawKey;

            if (strlen($keyText) > self::MAX_KEY_LENGTH) {
                $rejections[] = TokenRejection::forRawKey(
                    $keyText,
                    RejectionReason::UnknownKey,
                    'Key is longer than any token name and was refused on length.',
                );
                continue;
            }

            $key = TokenKey::tryFrom($keyText);
            if (!$key instanceof TokenKey) {
                $rejections[] = TokenRejection::forRawKey($keyText, RejectionReason::UnknownKey);
                continue;
            }

            if (!$key->isTenantOverridable()) {
                $rejections[] = TokenRejection::forKey($key, RejectionReason::NotOverridable);
                continue;
            }

            if (!is_string($rawValue)) {
                $rejections[] = TokenRejection::forKey(
                    $key,
                    RejectionReason::MalformedValue,
                    'Value must be a string in #RRGGBB form.',
                );
                continue;
            }

            $value = ColorValue::tryFrom($rawValue);
            if (!$value instanceof ColorValue) {
                $rejections[] = TokenRejection::forKey($key, RejectionReason::MalformedValue);
                continue;
            }

            $accepted[] = new DesignToken($key, $value);
            $touched[] = $key;
        }

        if ($rejections !== []) {
            // Do not spend contrast work on a set that is already refused.
            return TokenValidationResult::rejected(...$rejections);
        }

        $merged = $base->with(...$accepted);
        $rejections = [
            ...$this->checkContrast($merged, $touched),
            ...$this->checkDisabledStateSeparation($merged, $touched),
        ];

        return $rejections === []
            ? TokenValidationResult::accepted($merged)
            : TokenValidationResult::rejected(...$rejections);
    }

    /**
     * Evaluate every rule the overlay disturbs — as foreground or as background.
     *
     * Rules untouched by the overlay are left alone on purpose: the Tier 1 palette
     * carries recorded, signed-off exceptions (CR-11), and re-gating it here would let
     * one of those block an unrelated tenant change.
     *
     * @param list<TokenKey> $touched
     *
     * @return list<TokenRejection>
     */
    private function checkContrast(TokenSet $merged, array $touched): array
    {
        $rejections = [];

        foreach (TokenKey::cases() as $key) {
            $rule = $key->contrastRule();
            if (!$rule instanceof ContrastRule) {
                continue;
            }

            $affected = array_values(array_filter(
                $touched,
                static fn (TokenKey $candidate): bool => $rule->involves($candidate),
            ));
            if ($affected === []) {
                continue;
            }

            $foreground = $merged->valueOf($rule->foreground);
            $background = $merged->valueOf($rule->background);

            if (!$foreground instanceof ColorValue || !$background instanceof ColorValue) {
                foreach ($affected as $touchedKey) {
                    $rejections[] = TokenRejection::forKey(
                        $touchedKey,
                        RejectionReason::MissingReferenceToken,
                        sprintf(
                            'Cannot measure "%s" against "%s": the base palette does not define both.',
                            $rule->foreground->value,
                            $rule->background->value,
                        ),
                    );
                }
                continue;
            }

            if ($rule->gate->isSatisfiedBy($this->contrast, $foreground, $background)) {
                continue;
            }

            $ratio = $this->contrast->ratio($foreground->value, $background->value);
            foreach ($affected as $touchedKey) {
                $rejections[] = TokenRejection::forKey(
                    $touchedKey,
                    RejectionReason::InsufficientContrast,
                    sprintf(
                        '"%s" on "%s" is %.2f:1, below the required %.1f:1.',
                        $rule->foreground->value,
                        $rule->background->value,
                        $ratio,
                        $rule->gate->minimumRatio(),
                    ),
                );
            }
        }

        return $rejections;
    }

    /**
     * Keep an overridable disabled fill visibly separate without inventing a WCAG gate.
     *
     * A tenant may move both the disabled and enabled primary fills, so either change
     * re-opens this product rule. The fixed page background is included because a
     * disabled control that disappears into the canvas is not a useful state cue.
     *
     * @param list<TokenKey> $touched
     *
     * @return list<TokenRejection>
     */
    private function checkDisabledStateSeparation(TokenSet $merged, array $touched): array
    {
        $affected = array_values(array_filter(
            $touched,
            static fn (TokenKey $key): bool => in_array(
                $key,
                [TokenKey::InteractivePrimaryDisabled, TokenKey::InteractivePrimaryDefault],
                true,
            ),
        ));
        if ($affected === []) {
            return [];
        }

        $disabled = $merged->valueOf(TokenKey::InteractivePrimaryDisabled);
        if (!$disabled instanceof ColorValue) {
            return array_map(
                static fn (TokenKey $key): TokenRejection => TokenRejection::forKey(
                    $key,
                    RejectionReason::MissingReferenceToken,
                    'Cannot evaluate disabled-state separation: the palette has no disabled primary fill.',
                ),
                $affected,
            );
        }

        $rejections = [];
        foreach ([TokenKey::InteractivePrimaryDefault, TokenKey::Background] as $referenceKey) {
            $reference = $merged->valueOf($referenceKey);
            if (!$reference instanceof ColorValue) {
                foreach ($affected as $key) {
                    $rejections[] = TokenRejection::forKey(
                        $key,
                        RejectionReason::MissingReferenceToken,
                        sprintf(
                            'Cannot evaluate disabled-state separation: the palette has no "%s" token.',
                            $referenceKey->value,
                        ),
                    );
                }
                continue;
            }

            $ratio = $this->contrast->ratio($disabled->value, $reference->value);
            if ($ratio >= self::DISABLED_STATE_SEPARATION_MINIMUM) {
                continue;
            }

            foreach ($affected as $key) {
                $rejections[] = TokenRejection::forKey(
                    $key,
                    RejectionReason::InsufficientStateSeparation,
                    sprintf(
                        'Disabled fill against "%s" is %.2f:1, below the product distinguishability floor of %.1f:1 (not a WCAG contrast gate).',
                        $referenceKey->value,
                        $ratio,
                        self::DISABLED_STATE_SEPARATION_MINIMUM,
                    ),
                );
            }
        }

        return $rejections;
    }
}
