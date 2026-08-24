<?php

/**
 * Reversible, identity-neutral translation-catalogue migration.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

final class TranslationCatalogueMigration
{
    public function forward(
        TranslationCatalogueContract $contract,
        TranslationCatalogueStore $store,
    ): TranslationCatalogueMigrationResult {
        return $store->transaction(function () use ($contract, $store): TranslationCatalogueMigrationResult {
            $beforeIntegrity = $this->assertIntegrity($store);
            $journal = $store->readJournal($contract->id);
            if (($journal['status'] ?? null) === 'applied') {
                $this->assertJournalContract($journal, $contract);
                $this->assertCurrentMatches($store, $contract->targetKey, $journal['after']);
                return new TranslationCatalogueMigrationResult('already_applied', 0, $journal['after']['id']);
            }
            if (($journal['status'] ?? null) === 'rolled_back') {
                $this->assertJournalContract($journal, $contract);
                $this->assertCurrentMatches($store, $contract->targetKey, $journal['before']);
            }

            $targetId = $this->singleExactId($store, $contract->targetKey);
            $before = $this->snapshot($store, $targetId, $beforeIntegrity);
            $desired = $this->desiredDefinitions($contract, $store);
            $existing = $targetId === null ? [] : $store->definitions($targetId);

            foreach ($existing as $languageId => $definition) {
                if (isset($desired[$languageId]) && $desired[$languageId] !== $definition) {
                    throw new \RuntimeException("Conflicting target definition for lang_id {$languageId}.");
                }
            }

            if ($targetId === null) {
                $targetId = $store->insertConstant($contract->targetKey);
            }
            $added = 0;
            foreach ($desired as $languageId => $definition) {
                if (!array_key_exists($languageId, $existing)) {
                    $store->insertDefinition($targetId, $languageId, $definition);
                    $added++;
                }
            }

            if ($added === 0 && ($before['exists'] ?? false)) {
                return new TranslationCatalogueMigrationResult('already_current', 0, $targetId);
            }

            $afterIntegrity = $this->assertIntegrity($store);
            if ($afterIntegrity['definitions'] - $beforeIntegrity['definitions'] !== $added) {
                throw new \RuntimeException('Unexplained translation definition-count change during migration.');
            }
            $after = $this->snapshot($store, $targetId, $afterIntegrity);
            $store->writeJournal($contract->id, $contract->hash, 'applied', $before, $after);

            return new TranslationCatalogueMigrationResult('applied', $added, $targetId);
        });
    }

    public function rollback(
        TranslationCatalogueContract $contract,
        TranslationCatalogueStore $store,
    ): TranslationCatalogueMigrationResult {
        return $store->transaction(function () use ($contract, $store): TranslationCatalogueMigrationResult {
            $this->assertIntegrity($store);
            $journal = $store->readJournal($contract->id);
            if ($journal === null) {
                return new TranslationCatalogueMigrationResult('nothing_to_rollback');
            }
            $this->assertJournalContract($journal, $contract);
            if (($journal['status'] ?? null) === 'rolled_back') {
                $this->assertCurrentMatches($store, $contract->targetKey, $journal['before']);
                return new TranslationCatalogueMigrationResult('already_rolled_back');
            }
            if (($journal['status'] ?? null) !== 'applied') {
                throw new \RuntimeException('Unknown translation migration journal status.');
            }

            $this->assertCurrentMatches($store, $contract->targetKey, $journal['after']);
            $before = $journal['before'];
            $after = $journal['after'];
            $targetId = $after['id'];
            $beforeDefinitions = $before['definitions'] ?? [];
            $removed = 0;
            foreach ($after['definitions'] as $languageId => $definition) {
                if (!array_key_exists((int) $languageId, $beforeDefinitions)) {
                    $store->deleteDefinition($targetId, (int) $languageId);
                    $removed++;
                }
            }
            if (!($before['exists'] ?? false)) {
                $store->deleteConstant($targetId);
            }

            $afterRollback = $this->assertIntegrity($store);
            if ($afterRollback !== $before['integrity']) {
                throw new \RuntimeException('Rollback did not restore the recorded integrity counts.');
            }
            $this->assertCurrentMatches($store, $contract->targetKey, $before);
            $store->writeJournal($contract->id, $contract->hash, 'rolled_back', $before, $after);

            return new TranslationCatalogueMigrationResult('rolled_back', $removed, $targetId);
        });
    }

    /** @return array<int, string> */
    private function desiredDefinitions(TranslationCatalogueContract $contract, TranslationCatalogueStore $store): array
    {
        $desired = $contract->definitions;

        // Carry-forward from a still-current constant (schema v2). Explicit definitions in the
        // contract win, so a locale whose mechanical derivation reads badly can be corrected by
        // hand without abandoning the rule for every other language.
        //
        // A missing or empty source is not an error. `Product Registration`, for instance, has no
        // catalogue row at all; deriving nothing from it leaves the neutral key falling back to
        // its own English text, which is exactly what that call site renders today.
        $derivation = $contract->derivation;
        if ($derivation instanceof TranslationDerivation) {
            $sourceId = $this->singleExactId($store, $derivation->sourceKey);
            if ($sourceId !== null) {
                foreach ($store->definitions($sourceId) as $languageId => $definition) {
                    if (!isset($desired[$languageId])) {
                        $desired[$languageId] = $derivation->derive($definition);
                    }
                }
            }
        }

        $sourceCandidates = [];
        foreach ($contract->legacyKeys as $legacyKey => $identity) {
            $sourceId = $this->singleExactId($store, $legacyKey);
            if ($sourceId === null) {
                continue;
            }
            foreach ($store->definitions($sourceId) as $languageId => $definition) {
                if (!str_contains($definition, $identity)) {
                    if (!isset($desired[$languageId])) {
                        throw new \RuntimeException(
                            "Cannot neutralise source-only definition for lang_id {$languageId} without losing identity context.",
                        );
                    }
                    continue;
                }
                $candidate = $contract->neutraliseLegacyDefinition($legacyKey, $definition);
                ProductContextTranslation::compose($candidate, 'Product');
                if (isset($sourceCandidates[$languageId]) && $sourceCandidates[$languageId] !== $candidate) {
                    throw new \RuntimeException("Conflicting legacy definitions for lang_id {$languageId}.");
                }
                $sourceCandidates[$languageId] = $candidate;
            }
        }
        foreach ($sourceCandidates as $languageId => $candidate) {
            $desired[$languageId] = $candidate;
        }
        ksort($desired);
        return $desired;
    }

    private function singleExactId(TranslationCatalogueStore $store, string $name): ?int
    {
        $ids = $store->constantIds($name);
        if (count($ids) > 1) {
            throw new \RuntimeException('Duplicate exact translation constants block deterministic migration: ' . $name);
        }
        return $ids[0] ?? null;
    }

    /** @param array<string, mixed> $journal */
    private function assertJournalContract(array $journal, TranslationCatalogueContract $contract): void
    {
        if (($journal['contract_hash'] ?? null) !== $contract->hash) {
            throw new \RuntimeException('Translation contract changed since the journal was recorded.');
        }
    }

    /** @param array<string, mixed> $expected */
    private function assertCurrentMatches(TranslationCatalogueStore $store, string $targetKey, array $expected): void
    {
        $id = $this->singleExactId($store, $targetKey);
        if (!($expected['exists'] ?? false)) {
            if ($id !== null) {
                throw new \RuntimeException('Translation target drifted from the recorded absent state.');
            }
            return;
        }
        if ($id !== $expected['id'] || $store->definitions($id) !== $expected['definitions']) {
            throw new \RuntimeException('Translation target drifted from its journalled state.');
        }
    }

    /** @param array{definitions: int, orphans: int, duplicate_pairs: int} $integrity */
    private function snapshot(TranslationCatalogueStore $store, ?int $targetId, array $integrity): array
    {
        return [
            'exists' => $targetId !== null,
            'id' => $targetId,
            'definitions' => $targetId === null ? [] : $store->definitions($targetId),
            'integrity' => $integrity,
        ];
    }

    /** @return array{definitions: int, orphans: int, duplicate_pairs: int} */
    private function assertIntegrity(TranslationCatalogueStore $store): array
    {
        $integrity = $store->integrity();
        if ($integrity['orphans'] !== 0 || $integrity['duplicate_pairs'] !== 0) {
            throw new \RuntimeException('Translation catalogue integrity precondition failed.');
        }
        return $integrity;
    }
}
