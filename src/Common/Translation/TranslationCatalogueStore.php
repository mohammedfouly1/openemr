<?php

/**
 * Storage boundary for reversible translation-catalogue migrations.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

interface TranslationCatalogueStore
{
    public function transaction(callable $operation): mixed;

    /** @return list<int> */
    public function constantIds(string $exactName): array;

    /** @return array<int, string> */
    public function definitions(int $constantId): array;

    public function insertConstant(string $name): int;

    public function insertDefinition(int $constantId, int $languageId, string $definition): void;

    public function deleteDefinition(int $constantId, int $languageId): void;

    public function deleteConstant(int $constantId): void;

    /** @return array<string, mixed>|null */
    public function readJournal(string $migrationId): ?array;

    /** @param array<string, mixed> $before @param array<string, mixed> $after */
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
