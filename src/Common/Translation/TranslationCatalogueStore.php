<?php

/**
 * Storage boundary for reversible translation-catalogue migrations.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * @phpstan-type TranslationSnapshot array{
 *     exists: bool,
 *     id: int|null,
 *     definitions: array<int, string>,
 *     integrity: array{definitions: int, orphans: int, duplicate_pairs: int},
 * }
 * @phpstan-type TranslationJournalEntry array{
 *     contract_hash: string,
 *     status: string,
 *     before: TranslationSnapshot,
 *     after: TranslationSnapshot,
 * }
 */
interface TranslationCatalogueStore
{
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function transaction(callable $operation): mixed;

    /** @return list<int> */
    public function constantIds(string $exactName): array;

    /** @return array<int, string> */
    public function definitions(int $constantId): array;

    public function insertConstant(string $name): int;

    public function insertDefinition(int $constantId, int $languageId, string $definition): void;

    public function deleteDefinition(int $constantId, int $languageId): void;

    public function deleteConstant(int $constantId): void;

    /** @return TranslationJournalEntry|null */
    public function readJournal(string $migrationId): ?array;

    /**
     * @param TranslationSnapshot $before
     * @param TranslationSnapshot $after
     */
    public function writeJournal(
        string $migrationId,
        string $contractHash,
        string $status,
        array $before,
        array $after,
    ): void;

    /** @return array{definitions: int, orphans: int, duplicate_pairs: int} */
    public function integrity(): array;
}
