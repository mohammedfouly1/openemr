<?php

/**
 * Durable, identity-neutral translation catalogue contract.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

final readonly class TranslationCatalogueContract
{
    public const SCHEMA = 'openemr-translation-contract/1';

    /**
     * @param array<string, string> $legacyKeys Exact legacy key => identity literal to neutralise.
     * @param array<int, string> $definitions Language ID => neutral translated pattern.
     */
    private function __construct(
        public string $id,
        public string $targetKey,
        public array $legacyKeys,
        public array $definitions,
        public string $hash,
    ) {
    }

    public static function fromFile(string $path): self
    {
        $json = file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException('Cannot read translation contract: ' . $path);
        }

        return self::fromJson($json);
    }

    public static function fromJson(string $json): self
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException('Invalid translation contract JSON.', 0, $exception);
        }

        if (!is_array($data) || ($data['schema'] ?? null) !== self::SCHEMA) {
            throw new \InvalidArgumentException('Unsupported translation contract schema.');
        }

        $id = self::requiredString($data, 'id');
        $targetKey = self::requiredString($data, 'target_key');
        self::assertPattern($targetKey, 'target_key');

        $legacyKeys = $data['legacy_keys'] ?? null;
        if (!is_array($legacyKeys)) {
            throw new \InvalidArgumentException('legacy_keys must be an object of exact key/literal pairs.');
        }
        $validatedLegacyKeys = [];
        foreach ($legacyKeys as $key => $literal) {
            if (!is_string($key) || $key === '' || !is_string($literal) || $literal === '') {
                throw new \InvalidArgumentException('Every legacy key and identity literal must be non-empty.');
            }
            $validatedLegacyKeys[$key] = $literal;
        }

        $definitions = $data['definitions'] ?? null;
        if (!is_array($definitions) || $definitions === []) {
            throw new \InvalidArgumentException('definitions must contain at least one translated pattern.');
        }
        $validatedDefinitions = [];
        foreach ($definitions as $langId => $definition) {
            if (!is_string($definition) || !ctype_digit((string) $langId) || (int) $langId < 1) {
                throw new \InvalidArgumentException('Definitions must map positive language IDs to strings.');
            }
            self::assertPattern($definition, 'definition for lang_id ' . $langId);
            $validatedDefinitions[(int) $langId] = $definition;
        }
        ksort($validatedDefinitions);

        return new self(
            $id,
            $targetKey,
            $validatedLegacyKeys,
            $validatedDefinitions,
            hash('sha256', $json),
        );
    }

    /**
     * Neutralise a legacy definition when it carries the configured identity.
     * A definition without that identity is left untouched; the authoritative
     * per-language contract decides the safe placeholder order for known rows.
     */
    public function neutraliseLegacyDefinition(string $legacyKey, string $definition): string
    {
        $literal = $this->legacyKeys[$legacyKey] ?? null;
        if ($literal === null) {
            throw new \InvalidArgumentException('Unknown legacy translation key: ' . $legacyKey);
        }

        return str_replace($literal, '%s', $definition);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function requiredString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (!is_string($value) || $value === '') {
            throw new \InvalidArgumentException($key . ' must be a non-empty string.');
        }
        return $value;
    }

    private static function assertPattern(string $pattern, string $field): void
    {
        try {
            ProductContextTranslation::compose($pattern, 'Product');
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException($field . ' must contain exactly one safe product placeholder.', 0, $exception);
        }
    }
}
