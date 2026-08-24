<?php

/**
 * A rule for carrying an existing constant's translations onto a neutral key.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * The alternative to this rule is embedding every carried translation as a literal in the
 * contract JSON. That was rejected for two reasons, both practical rather than aesthetic:
 *
 *  - it is ~110 hand-copied strings across four source constants, each one an opportunity to
 *    mistype a locale's text in a language the author cannot read; and
 *  - it freezes a snapshot. Upstream keeps improving `currentLanguage_utf8.sql`, and a literal
 *    copy would silently stop tracking it, so the neutral key would drift behind the very
 *    translations it was derived from.
 *
 * Deriving instead means the rule is auditable in one line and stays correct as upstream
 * translations improve: whatever `Authorization` means in Greek next release, `%s Authorization`
 * follows it.
 *
 * `sourceKey` is matched exactly and case-sensitively (`BINARY` in SQL, exact comparison in PHP),
 * because `lang_constants` holds near-duplicate names that differ only by case or trailing
 * whitespace — Scan-2E counted 32 such groups.
 */
final readonly class TranslationDerivation
{
    public function __construct(
        public string $sourceKey,
        public TranslationPlacement $placement,
    ) {
    }

    /**
     * @param array<string, mixed> $data the contract's `derive_from` object
     */
    public static function fromArray(array $data): self
    {
        $sourceKey = $data['source_key'] ?? null;
        if (!is_string($sourceKey) || trim($sourceKey) === '') {
            throw new \InvalidArgumentException('derive_from.source_key must be a non-empty string.');
        }

        $placement = $data['placement'] ?? null;
        if (!is_string($placement)) {
            throw new \InvalidArgumentException('derive_from.placement must be a string.');
        }

        $resolved = TranslationPlacement::tryFrom($placement);
        if (!$resolved instanceof TranslationPlacement) {
            throw new \InvalidArgumentException(
                'derive_from.placement must be one of: prefix, suffix.',
            );
        }

        return new self($sourceKey, $resolved);
    }

    /**
     * Turn one source translation into the neutral pattern for the same language.
     *
     * A source definition that already contains a `%` is refused rather than escaped. Percent is
     * meaningful to ProductContextTranslation, and a phrase such as `50% Complete` would compose
     * into something neither correct nor obviously wrong — it would just render oddly in one
     * locale, which is the class of defect this whole contract exists to prevent.
     */
    public function derive(string $sourceDefinition): string
    {
        if (str_contains($sourceDefinition, '%')) {
            throw new \RuntimeException(
                'Refusing to derive a neutral pattern from a source definition containing "%".',
            );
        }

        $pattern = $this->placement->applyTo($sourceDefinition);

        // Prove the result is composable before it can reach the catalogue: exactly one
        // placeholder, no unsupported token. Throws if the rule ever produces something unsafe.
        ProductContextTranslation::compose($pattern, 'Product');

        return $pattern;
    }
}
