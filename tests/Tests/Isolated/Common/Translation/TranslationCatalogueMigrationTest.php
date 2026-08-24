<?php

/** @package OpenEMR */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Translation;

use OpenEMR\Common\Translation\TranslationCatalogueContract;
use OpenEMR\Common\Translation\TranslationCatalogueMigration;
use OpenEMR\Common\Translation\TranslationCatalogueStore;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class TranslationCatalogueMigrationTest extends TestCase
{
    public function testOldKeyOnlyCreatesNeutralTargetWithoutChangingSource(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $sourceId = $store->seedConstant('Thiqa Database Upgrade', [3 => 'Base de datos Thiqa']);

        $result = $this->migration()->forward($this->contract(), $store);

        self::assertSame('applied', $result->action);
        self::assertSame([3 => 'Base de datos Thiqa'], $store->definitions($sourceId));
        self::assertSame([3 => 'Base de datos %s', 22 => 'ترقية قاعدة بيانات %s'], $store->targetDefinitions());
    }

    public function testTargetOnlyIsAlreadyCurrent(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $targetId = $store->seedConstant('%s Database Upgrade', $this->contract()->definitions);

        $result = $this->migration()->forward($this->contract(), $store);

        self::assertSame('already_current', $result->action);
        self::assertSame($targetId, $result->targetId);
        self::assertNull($store->readJournal('test-neutral'));
    }

    public function testBothKeysMergeMissingDefinitionsAndPreserveExtraTargetLanguage(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('Thiqa Database Upgrade', [3 => 'Base de datos Thiqa']);
        $store->seedConstant('%s Database Upgrade', [3 => 'Base de datos %s', 99 => 'Custom %s']);

        $this->migration()->forward($this->contract(), $store);

        self::assertSame(
            [3 => 'Base de datos %s', 22 => 'ترقية قاعدة بيانات %s', 99 => 'Custom %s'],
            $store->targetDefinitions(),
        );
    }

    public function testNeitherKeyCreatesContractDefinitions(): void
    {
        $store = new MemoryTranslationCatalogueStore();

        $result = $this->migration()->forward($this->contract(), $store);

        self::assertSame(2, $result->definitionsChanged);
        self::assertSame($this->contract()->definitions, $store->targetDefinitions());
    }

    public function testDifferentSourceLanguageSetIsPreservedWhenItCanBeNeutralised(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('Thiqa Database Upgrade', [77 => 'Thiqa bespoke upgrade']);

        $this->migration()->forward($this->contract(), $store);

        self::assertSame('%s bespoke upgrade', $store->targetDefinitions()[77]);
    }

    public function testConflictingTargetDefinitionRollsBackEveryWrite(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $targetId = $store->seedConstant('%s Database Upgrade', [3 => 'Conflicting %s']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Conflicting target definition');
        try {
            $this->migration()->forward($this->contract(), $store);
        } finally {
            self::assertSame([3 => 'Conflicting %s'], $store->definitions($targetId));
            self::assertNull($store->readJournal('test-neutral'));
        }
    }

    public function testConflictingLegacyDefinitionsAreExplicit(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Database Upgrade', [3 => 'OpenEMR first']);
        $store->seedConstant('Thiqa Database Upgrade', [3 => 'second Thiqa']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Conflicting legacy definitions');
        $this->migration()->forward($this->contract(), $store);
    }

    public function testRepeatedForwardIsIdempotent(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $first = $this->migration()->forward($this->contract(), $store);
        $second = $this->migration()->forward($this->contract(), $store);

        self::assertSame('applied', $first->action);
        self::assertSame('already_applied', $second->action);
        self::assertCount(1, $store->constantIds('%s Database Upgrade'));
    }

    public function testRollbackRestoresExactPartialTargetAndIsIdempotent(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $targetId = $store->seedConstant('%s Database Upgrade', [3 => 'Base de datos %s']);
        $this->migration()->forward($this->contract(), $store);

        $first = $this->migration()->rollback($this->contract(), $store);
        $second = $this->migration()->rollback($this->contract(), $store);

        self::assertSame('rolled_back', $first->action);
        self::assertSame('already_rolled_back', $second->action);
        self::assertSame([3 => 'Base de datos %s'], $store->definitions($targetId));
    }

    public function testForwardRollbackForwardCycle(): void
    {
        $store = new MemoryTranslationCatalogueStore();

        $this->migration()->forward($this->contract(), $store);
        $this->migration()->rollback($this->contract(), $store);
        $result = $this->migration()->forward($this->contract(), $store);

        self::assertSame('applied', $result->action);
        self::assertSame($this->contract()->definitions, $store->targetDefinitions());
        self::assertSame(['definitions' => 2, 'orphans' => 0, 'duplicate_pairs' => 0], $store->integrity());
    }

    public function testDuplicateExactConstantNamesBlockMigration(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('Thiqa Database Upgrade', []);
        $store->seedConstant('Thiqa Database Upgrade', []);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Duplicate exact translation constants');
        $this->migration()->forward($this->contract(), $store);
    }

    public function testPreExistingOrphanBlocksMigration(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->orphanCount = 1;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('integrity precondition');
        $this->migration()->forward($this->contract(), $store);
    }

    public function testPreExistingDuplicatePairBlocksMigration(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->duplicatePairCount = 1;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('integrity precondition');
        $this->migration()->forward($this->contract(), $store);
    }

    public function testRepositoryContractPreservesTwentyEightLanguagesIncludingRtl(): void
    {
        $contract = TranslationCatalogueContract::fromFile(
            dirname(__DIR__, 5) . '/contrib/util/language_translations/contracts/database-upgrade.json',
        );
        $store = new MemoryTranslationCatalogueStore();

        $this->migration()->forward($contract, $store);

        self::assertCount(28, $store->targetDefinitions());
        self::assertArrayHasKey(7, $store->targetDefinitions());
        self::assertArrayHasKey(22, $store->targetDefinitions());
        self::assertArrayHasKey(37, $store->targetDefinitions());
        self::assertSame(0, $store->integrity()['orphans']);
        self::assertSame(0, $store->integrity()['duplicate_pairs']);
    }

    public function testRollbackRejectsJournalledTargetDrift(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $this->migration()->forward($this->contract(), $store);
        $store->replaceTargetDefinition(3, 'Changed after migration');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('drifted');
        $this->migration()->rollback($this->contract(), $store);
    }

    private function migration(): TranslationCatalogueMigration
    {
        return new TranslationCatalogueMigration();
    }

    private function contract(): TranslationCatalogueContract
    {
        return TranslationCatalogueContract::fromJson(<<<'JSON'
            {
                "schema": "openemr-translation-contract/1",
                "id": "test-neutral",
                "target_key": "%s Database Upgrade",
                "legacy_keys": {
                    "OpenEMR Database Upgrade": "OpenEMR",
                    "Thiqa Database Upgrade": "Thiqa"
                },
                "definitions": {
                    "3": "Base de datos %s",
                    "22": "ترقية قاعدة بيانات %s"
                }
            }
            JSON);
    }
}

final class MemoryTranslationCatalogueStore implements TranslationCatalogueStore
{
    /** @var array<int, string> */
    private array $constants = [];
    /** @var array<int, array<int, string>> */
    private array $definitionsByConstant = [];
    /** @var array<string, array<string, mixed>> */
    private array $journals = [];
    private int $nextId = 1;
    public int $orphanCount = 0;
    public int $duplicatePairCount = 0;

    public function transaction(callable $operation): mixed
    {
        $snapshot = [$this->constants, $this->definitionsByConstant, $this->journals, $this->nextId];
        try {
            return $operation();
        } catch (\Throwable $throwable) {
            [$this->constants, $this->definitionsByConstant, $this->journals, $this->nextId] = $snapshot;
            throw $throwable;
        }
    }

    public function constantIds(string $exactName): array
    {
        return array_values(array_keys($this->constants, $exactName, true));
    }

    public function definitions(int $constantId): array
    {
        $definitions = $this->definitionsByConstant[$constantId] ?? [];
        ksort($definitions);
        return $definitions;
    }

    public function insertConstant(string $name): int
    {
        $id = $this->nextId++;
        $this->constants[$id] = $name;
        $this->definitionsByConstant[$id] = [];
        return $id;
    }

    public function insertDefinition(int $constantId, int $languageId, string $definition): void
    {
        if (isset($this->definitionsByConstant[$constantId][$languageId])) {
            throw new \RuntimeException('Duplicate pair');
        }
        $this->definitionsByConstant[$constantId][$languageId] = $definition;
    }

    public function deleteDefinition(int $constantId, int $languageId): void
    {
        unset($this->definitionsByConstant[$constantId][$languageId]);
    }

    public function deleteConstant(int $constantId): void
    {
        if (($this->definitionsByConstant[$constantId] ?? []) !== []) {
            throw new \RuntimeException('Cannot delete constant with definitions');
        }
        unset($this->definitionsByConstant[$constantId], $this->constants[$constantId]);
    }

    public function readJournal(string $migrationId): ?array
    {
        return $this->journals[$migrationId] ?? null;
    }

    public function writeJournal(
        string $migrationId,
        string $contractHash,
        string $status,
        array $before,
        array $after,
    ): void {
        $this->journals[$migrationId] = [
            'contract_hash' => $contractHash,
            'status' => $status,
            'before' => $before,
            'after' => $after,
        ];
    }

    public function integrity(): array
    {
        return [
            'definitions' => array_sum(array_map('count', $this->definitionsByConstant)),
            'orphans' => $this->orphanCount,
            'duplicate_pairs' => $this->duplicatePairCount,
        ];
    }

    /** @param array<int, string> $definitions */
    public function seedConstant(string $name, array $definitions): int
    {
        $id = $this->insertConstant($name);
        foreach ($definitions as $languageId => $definition) {
            $this->insertDefinition($id, $languageId, $definition);
        }
        return $id;
    }

    /** @return array<int, string> */
    public function targetDefinitions(): array
    {
        $ids = $this->constantIds('%s Database Upgrade');
        return $ids === [] ? [] : $this->definitions($ids[0]);
    }

    public function replaceTargetDefinition(int $languageId, string $definition): void
    {
        $id = $this->constantIds('%s Database Upgrade')[0];
        $this->definitionsByConstant[$id][$languageId] = $definition;
    }
}
