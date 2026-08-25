<?php

/**
 * OpenEMR database adapter for translation-catalogue migrations.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

use OpenEMR\Common\Database\QueryUtils;

final class QueryUtilsTranslationCatalogueStore implements TranslationCatalogueStore
{
    public function transaction(callable $operation): mixed
    {
        // QueryUtils::inTransaction() is begin/commit/rollback-and-rethrow, which is exactly
        // what this method used to spell out with the three deprecated calls.
        return QueryUtils::inTransaction($operation);
    }

    public function constantIds(string $exactName): array
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT `cons_id` FROM `lang_constants` WHERE BINARY `constant_name` = ? ORDER BY `cons_id` FOR UPDATE',
            [$exactName],
        );
        return array_map(
            static fn (array $row): int => self::columnAsInt($row['cons_id'] ?? null, 'cons_id'),
            $rows,
        );
    }

    public function definitions(int $constantId): array
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT `lang_id`, `definition` FROM `lang_definitions` WHERE `cons_id` = ? ORDER BY `lang_id`, `def_id` FOR UPDATE',
            [$constantId],
        );
        $definitions = [];
        foreach ($rows as $row) {
            $languageId = self::columnAsInt($row['lang_id'] ?? null, 'lang_id');
            if (array_key_exists($languageId, $definitions)) {
                throw new \RuntimeException("Duplicate definition pair for cons_id {$constantId}, lang_id {$languageId}.");
            }
            $definitions[$languageId] = self::columnAsString($row['definition'] ?? null, 'definition');
        }
        return $definitions;
    }

    public function insertConstant(string $name): int
    {
        return (int) QueryUtils::sqlInsert(
            'INSERT INTO `lang_constants` (`constant_name`) VALUES (?)',
            [$name],
        );
    }

    public function insertDefinition(int $constantId, int $languageId, string $definition): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`) VALUES (?, ?, ?)',
            [$constantId, $languageId, $definition],
            true,
        );
    }

    public function deleteDefinition(int $constantId, int $languageId): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `lang_definitions` WHERE `cons_id` = ? AND `lang_id` = ?',
            [$constantId, $languageId],
            true,
        );
    }

    public function deleteConstant(int $constantId): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `lang_constants` WHERE `cons_id` = ?',
            [$constantId],
            true,
        );
    }

    public function readJournal(string $migrationId): ?array
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT `contract_hash`, `status`, `before_state`, `after_state` '
                . 'FROM `translation_migration_journal` WHERE `migration_id` = ? FOR UPDATE',
            [$migrationId],
        );
        if ($rows === []) {
            return null;
        }
        if (count($rows) !== 1) {
            throw new \RuntimeException('Duplicate translation migration journal rows.');
        }
        try {
            return [
                'contract_hash' => self::columnAsString($rows[0]['contract_hash'] ?? null, 'contract_hash'),
                'status' => self::columnAsString($rows[0]['status'] ?? null, 'status'),
                'before' => json_decode(
                    self::columnAsString($rows[0]['before_state'] ?? null, 'before_state'),
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                ),
                'after' => json_decode(
                    self::columnAsString($rows[0]['after_state'] ?? null, 'after_state'),
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                ),
            ];
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Invalid translation migration journal state.', 0, $exception);
        }
    }

    public function writeJournal(
        string $migrationId,
        string $contractHash,
        string $status,
        array $before,
        array $after,
    ): void {
        $beforeJson = json_encode($before, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $afterJson = json_encode($after, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `translation_migration_journal` '
                . '(`migration_id`, `contract_hash`, `status`, `before_state`, `after_state`) '
                . 'VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE '
                . '`contract_hash` = VALUES(`contract_hash`), `status` = VALUES(`status`), '
                . '`before_state` = VALUES(`before_state`), `after_state` = VALUES(`after_state`)',
            [$migrationId, $contractHash, $status, $beforeJson, $afterJson],
            true,
        );
    }

    public function integrity(): array
    {
        $definitions = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `count` FROM `lang_definitions`',
            'count',
            [],
        );
        $orphans = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `count` FROM `lang_definitions` d '
                . 'LEFT JOIN `lang_constants` c ON c.`cons_id` = d.`cons_id` WHERE c.`cons_id` IS NULL',
            'count',
            [],
        );
        $duplicates = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `count` FROM ('
                . 'SELECT 1 FROM `lang_definitions` GROUP BY `cons_id`, `lang_id` HAVING COUNT(*) > 1'
                . ') duplicate_groups',
            'count',
            [],
        );
        return [
            'definitions' => (int) $definitions,
            'orphans' => (int) $orphans,
            'duplicate_pairs' => (int) $duplicates,
        ];
    }

    /**
     * Narrows an untyped database column value to int.
     *
     * QueryUtils hands rows back as `array<mixed>`, so a column value arrives untyped and cannot
     * be cast directly. Every scalar the driver can return is converted exactly as the previous
     * `(int)` cast converted it; only an array or object — which `(int)` would have turned into
     * a warning and a meaningless `1` — becomes an error, because that means the query no longer
     * returns the shape this class was written against.
     */
    private static function columnAsInt(mixed $value, string $column): int
    {
        if (is_int($value)) {
            return $value;
        }
        if ($value === null || is_bool($value) || is_float($value) || is_string($value)) {
            return (int) $value;
        }

        throw new \RuntimeException(sprintf('Column "%s" did not return a scalar value.', $column));
    }

    /**
     * Narrows an untyped database column value to string. See {@see columnAsInt()} for why the
     * scalar branches are exhaustive and why the remaining branch is an error rather than a cast.
     */
    private static function columnAsString(mixed $value, string $column): string
    {
        if (is_string($value)) {
            return $value;
        }
        if ($value === null || is_bool($value) || is_float($value) || is_int($value)) {
            return (string) $value;
        }

        throw new \RuntimeException(sprintf('Column "%s" did not return a scalar value.', $column));
    }
}
