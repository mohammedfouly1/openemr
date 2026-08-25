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
                    if (isset($desired[$languageId])) {
                        continue;
                    }
                    // S4-P0-40. `derive()` THROWS on a source containing '%', while the generated
                    // installer SQL FILTERS the same row out with NO_PERCENT_IN_SOURCE. Letting the
                    // throw escape here is what wedged the upgrade; skipping is what the installer
                    // has always done, so skipping is what makes the two paths agree.
                    if (!self::isCarryForwardable($definition)) {
                        continue;
                    }
                    $desired[$languageId] = $derivation->derive($definition);
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
                    // A translation that never named the product has no placeholder position to
                    // infer, and inventing one produces a subtly wrong sentence in a language the
                    // author cannot read. An explicit contract definition settles it; otherwise
                    // the contract's own policy decides, and `skip` here is what makes the PHP
                    // upgrade path agree with the generated installer SQL, which has always
                    // skipped these rows.
                    if (!isset($desired[$languageId]) && $contract->onMissingIdentity->isFatal()) {
                        throw new \RuntimeException(
                            "Cannot neutralise source-only definition for lang_id {$languageId} without losing identity context.",
                        );
                    }
                    continue;
                }
                // S4-P0-40. The generated installer SQL takes this row only when it contains
                // no '%' AND names the product EXACTLY once; anything else it filters out. The PHP
                // path had neither filter -- it neutralised unconditionally and then called
                // compose(), which throws. So on real upstream data (French
                // `Sauvegarde de la base OpenEMR (100% des tables)`) a fresh install skipped the
                // row silently and an upgrade died on it, after the DDL had applied and before the
                // version row was bumped, leaving the site permanently unable to re-run.
                //
                // The filters are mirrored here rather than the SQL being taught to throw, because
                // between "one locale silently keeps its old string" and "every upgrade of every
                // site wedges mid-run", only the first is recoverable. That is the S3-P0-28 lesson.
                if (!self::isNeutralisable($definition, $identity)) {
                    continue;
                }

                $candidate = $contract->neutraliseLegacyDefinition($legacyKey, $definition);
                // Retained as a belt-and-braces assertion: the two guards above should already
                // guarantee this composes. If it ever throws again, the filters and the SQL have
                // drifted apart and that is worth failing loudly in a test, not in production.
                ProductContextTranslation::compose($candidate, 'Product');
                if (isset($sourceCandidates[$languageId]) && $sourceCandidates[$languageId] !== $candidate) {
                    throw new \RuntimeException("Conflicting legacy definitions for lang_id {$languageId}.");
                }
                $sourceCandidates[$languageId] = $candidate;
            }
        }
        // An explicit contract definition is AUTHORITATIVE; carry-forward only fills gaps.
        //
        // This used to be an unconditional overwrite, which made the PHP upgrade path prefer the
        // neutralised legacy string while the generated installer SQL prefers the explicit one —
        // its carry-forward statement anti-joins against rows already inserted. The two paths then
        // disagreed on real shipped data: `database-upgrade.json` declares French as
        // "Mettre à jour la base de donnée de %s", while the upstream seed's legacy row is
        // "…de donnée d'OpenEMR", which neutralises to "…de donnée d'%s".
        //
        // The consequence was not cosmetic. A site installed by this branch's installer got the SQL
        // value; the first `sql_upgrade.php` run then computed the PHP value, found it different
        // from what was already there, and threw "Conflicting target definition for lang_id 8" —
        // uncaught, after the DDL had applied and before the version row was bumped, leaving the
        // upgrade wedged and unrecoverable without hand-editing lang_definitions. Found by the
        // Scan-3A adversarial pass, which reproduced both halves.
        //
        // One precedence rule, both paths: `??=`.
        foreach ($sourceCandidates as $languageId => $candidate) {
            $desired[$languageId] ??= $candidate;
        }
        ksort($desired);
        return $desired;
    }

    /**
     * The PHP mirror of the SQL's `NO_PERCENT_IN_SOURCE` filter.
     *
     * `TranslationContractSqlRenderer::NO_PERCENT_IN_SOURCE` is `LOCATE('%', d.definition) = 0`,
     * applied to both the derivation and the legacy carry-forward statements. A source definition
     * containing a per-cent sign cannot yield a safe pattern: the sign may be a literal (`100%`),
     * and there is no way to tell that from a placeholder without understanding the sentence.
     *
     * Keep this and the SQL constant in lockstep. They are two spellings of one rule, and the whole
     * of S4-P0-40 was that they had drifted into filtering versus throwing.
     */
    private static function isCarryForwardable(string $definition): bool
    {
        return !str_contains($definition, '%');
    }

    /**
     * The PHP mirror of the SQL's legacy-row filters: no per-cent sign, and the identity present
     * EXACTLY once.
     *
     * The exactly-once half matters as much as the other. A definition naming the product twice
     * neutralises to a two-placeholder pattern, which `ProductContextTranslation` refuses -- so
     * before this guard the row either installed cleanly and then fatalled the page that rendered
     * it, or (on the upgrade path) fatalled the upgrade itself. The SQL expresses it as a
     * CHAR_LENGTH difference; `substr_count()` is the same predicate.
     */
    private static function isNeutralisable(string $definition, string $identity): bool
    {
        if (!self::isCarryForwardable($definition)) {
            return false;
        }

        return $identity !== '' && substr_count($definition, $identity) === 1;
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
