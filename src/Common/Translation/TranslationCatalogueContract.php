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
    /** Original schema: explicit per-language definitions, optional brand-literal neutralisation. */
    public const SCHEMA = 'openemr-translation-contract/1';

    /**
     * Adds `derive_from`, and allows `definitions` to be empty because they are then derived.
     *
     * Version 1 is still accepted and still behaves exactly as before. It is not re-issued: the
     * migration journal records the contract hash and refuses to run against a contract that has
     * changed since, so silently rewriting a shipped contract would strand any database that had
     * already applied it.
     */
    public const SCHEMA_V2 = 'openemr-translation-contract/2';

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
        public ?TranslationDerivation $derivation = null,
        public string $schema = self::SCHEMA,
        public MissingIdentityPolicy $onMissingIdentity = MissingIdentityPolicy::Fail,
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

        $schema = is_array($data) ? ($data['schema'] ?? null) : null;
        if ($schema !== self::SCHEMA && $schema !== self::SCHEMA_V2) {
            throw new \InvalidArgumentException('Unsupported translation contract schema.');
        }
        /** @var array<string, mixed> $data */

        $derivation = null;
        if (array_key_exists('derive_from', $data)) {
            if ($schema !== self::SCHEMA_V2) {
                throw new \InvalidArgumentException('derive_from requires schema ' . self::SCHEMA_V2 . '.');
            }
            if (!is_array($data['derive_from'])) {
                throw new \InvalidArgumentException('derive_from must be an object.');
            }
            $deriveFrom = [];
            foreach ($data['derive_from'] as $deriveFromKey => $deriveFromValue) {
                if (!is_string($deriveFromKey)) {
                    throw new \InvalidArgumentException('derive_from must be a JSON object, not an array.');
                }
                $deriveFrom[$deriveFromKey] = $deriveFromValue;
            }
            $derivation = TranslationDerivation::fromArray($deriveFrom);
        }

        $id = self::requiredString($data, 'id');
        $targetKey = self::requiredString($data, 'target_key');
        self::assertPattern($targetKey, 'target_key');

        $onMissingIdentity = $data['on_missing_identity'] ?? MissingIdentityPolicy::Fail->value;
        if (!is_string($onMissingIdentity)) {
            throw new \InvalidArgumentException('on_missing_identity must be a string.');
        }
        $missingIdentityPolicy = MissingIdentityPolicy::tryFrom($onMissingIdentity);
        if (!$missingIdentityPolicy instanceof MissingIdentityPolicy) {
            throw new \InvalidArgumentException('on_missing_identity must be one of: fail, skip.');
        }
        if ($missingIdentityPolicy !== MissingIdentityPolicy::Fail && $schema !== self::SCHEMA_V2) {
            throw new \InvalidArgumentException('on_missing_identity requires schema ' . self::SCHEMA_V2 . '.');
        }

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
        if (!is_array($definitions)) {
            throw new \InvalidArgumentException('definitions must be an object of language ID => pattern.');
        }

        // A carry-forward contract legitimately ships no explicit definitions: every language comes
        // from the source constant at migration time, either by derivation or by neutralising a
        // brand literal. With neither, there is nothing else to supply them, so an empty set would
        // mean a constant with no translations at all.
        $carriesForward = $derivation !== null || $validatedLegacyKeys !== [];
        if ($definitions === [] && !$carriesForward) {
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
            $derivation,
            $schema,
            $missingIdentityPolicy,
        );
    }

    /** True when this contract carries translations forward from an existing constant. */
    public function isDerived(): bool
    {
        return $this->derivation instanceof TranslationDerivation;
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
