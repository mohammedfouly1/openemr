<?php

/**
 * Parses brand/tokens/thiqa-tokens.json into typed TokenSets.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Token;

use JsonException;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;

/**
 * Parse, don't validate: this is the boundary where the nested JSON document becomes
 * TokenKey/ColorValue objects. After it returns, nothing downstream re-checks a key
 * name or a colour literal — the types carry the guarantee (plan §3.4.1).
 *
 * Unknown keys are REJECTED rather than skipped. Silently dropping them would let the
 * allowlist and the design source drift apart without anyone noticing, which is exactly
 * how a security boundary rots.
 */
final readonly class TokenSetParser
{
    /** JSON nesting beyond this is not a token document. */
    private const MAX_DEPTH = 16;

    private const VARIANT_LIGHT = 'light';
    private const VARIANT_DARK = 'dark';

    /**
     * Parse the full two-variant document.
     *
     * @return array{light: TokenSet, dark: TokenSet}
     *
     * @throws TokenParseException
     */
    public function parseDocument(string $json): array
    {
        $decoded = $this->decode($json);

        return [
            self::VARIANT_LIGHT => $this->parseVariant(
                ThemeVariant::Light,
                $this->variantSection($decoded, self::VARIANT_LIGHT),
            ),
            self::VARIANT_DARK => $this->parseVariant(
                ThemeVariant::Dark,
                $this->variantSection($decoded, self::VARIANT_DARK),
            ),
        ];
    }

    /**
     * Parse one variant's nested section into a TokenSet.
     *
     * @param array<array-key, mixed> $section the decoded `light` or `dark` object
     *
     * @throws TokenParseException
     */
    public function parseVariant(ThemeVariant $variant, array $section): TokenSet
    {
        $tokens = [];
        foreach ($this->flatten($section, []) as $dotPath => $literal) {
            $key = TokenKey::tryFrom($dotPath);
            if (!$key instanceof TokenKey) {
                throw TokenParseException::unknownKey($dotPath);
            }

            $value = ColorValue::tryFrom($literal);
            if (!$value instanceof ColorValue) {
                throw TokenParseException::malformedValue($dotPath);
            }

            $tokens[] = new DesignToken($key, $value);
        }

        return new TokenSet($variant, ...$tokens);
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws TokenParseException
     */
    private function decode(string $json): array
    {
        try {
            $decoded = json_decode($json, true, self::MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // The decoder message can quote the offending bytes; do not surface it.
            throw TokenParseException::malformedDocument('not valid JSON');
        }

        if (!is_array($decoded)) {
            throw TokenParseException::malformedDocument('top level must be an object');
        }

        return $decoded;
    }

    /**
     * @param array<array-key, mixed> $document
     *
     * @return array<array-key, mixed>
     *
     * @throws TokenParseException
     */
    private function variantSection(array $document, string $name): array
    {
        $section = $document[$name] ?? null;
        if (!is_array($section)) {
            throw TokenParseException::malformedDocument(sprintf('missing "%s" section', $name));
        }

        return $section;
    }

    /**
     * Depth-first flatten of the nested object into `a.b.c` paths.
     *
     * @param array<array-key, mixed> $data
     * @param list<string>            $path
     *
     * @return array<string, string>
     *
     * @throws TokenParseException
     */
    private function flatten(array $data, array $path): array
    {
        $flat = [];
        foreach ($data as $segment => $node) {
            if (!is_string($segment)) {
                // A JSON list where an object was expected: there is no key to allowlist.
                throw TokenParseException::malformedDocument('token names must be strings');
            }

            $childPath = [...$path, $segment];
            $dotPath = implode('.', $childPath);

            if (is_array($node)) {
                foreach ($this->flatten($node, $childPath) as $nestedPath => $literal) {
                    $flat[$nestedPath] = $literal;
                }
                continue;
            }

            if (!is_string($node)) {
                throw TokenParseException::malformedValue($dotPath);
            }

            $flat[$dotPath] = $node;
        }

        return $flat;
    }
}
