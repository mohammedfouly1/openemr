<?php

/**
 * SQL renderer for durable translation contracts used by fresh installs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * The installer loads `currentLanguage_utf8.sql` — the wholesale upstream snapshot — and then this
 * supplement. That ordering is what makes a **derived** contract expressible as SQL at all: by the
 * time these statements run, the source constants and their translations are already in the
 * catalogue, so the derivation can be a `CONCAT` over rows that exist rather than ~110 literals
 * frozen into a generated file.
 *
 * Deriving in SQL rather than at generation time is deliberate. A generated literal would be a
 * snapshot of upstream's translations on the day the file was regenerated, and would then quietly
 * stop tracking them; the `INSERT … SELECT` below re-derives from whatever the installed snapshot
 * actually contains, so `%s Authorization` follows `Authorization` for as long as both exist.
 *
 * Every statement is written to be safe to run twice — each guards on the row not already being
 * there — because the supplement is executed on fresh installs, on the patch path, and by hand
 * during recovery.
 */
final class TranslationContractSqlRenderer
{
    /** Mirrors TranslationDerivation::derive()'s refusal, so SQL and PHP agree on what is derivable. */
    private const NO_PERCENT_IN_SOURCE = "LOCATE('%', `d`.`definition`) = 0";

    /**
     * Render one contract.
     */
    public function render(TranslationCatalogueContract $contract): string
    {
        return implode("\n", array_merge(
            [
                '-- Generated from ' . TranslationCatalogueContractSet::RELATIVE_DIRECTORY . '/.',
                '-- Do not edit this generated file directly.',
                '',
            ],
            $this->statementsFor($contract),
        ));
    }

    /**
     * Render every contract in the set into one deterministic supplement.
     */
    public function renderSet(TranslationCatalogueContractSet $set): string
    {
        $lines = [
            '-- Generated from ' . TranslationCatalogueContractSet::RELATIVE_DIRECTORY . '/.',
            '-- Contracts: ' . $set->count(),
            '-- Do not edit this generated file directly.',
            '',
        ];

        foreach ($set->all() as $contract) {
            $lines = array_merge($lines, $this->statementsFor($contract));
        }

        return implode("\n", $lines);
    }

    /**
     * @return list<string>
     */
    private function statementsFor(TranslationCatalogueContract $contract): array
    {
        $target = self::quote($contract->targetKey);

        $lines = [
            '-- Contract: ' . $contract->id,
            '-- SHA256: ' . $contract->hash,
            '',
            'INSERT INTO `lang_constants` (`constant_name`)',
            "SELECT '{$target}' FROM DUAL",
            "WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '{$target}');",
            '',
            'SET @openemr_translation_contract_cons_id = (',
            '    SELECT `cons_id` FROM `lang_constants`',
            "    WHERE BINARY `constant_name` = '{$target}'",
            ');',
            '',
        ];

        foreach ($contract->definitions as $langId => $definition) {
            $definition = self::quote($definition);
            $lines[] = 'INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)';
            $lines[] = "SELECT @openemr_translation_contract_cons_id, {$langId}, '{$definition}' FROM DUAL";
            $lines[] = 'WHERE NOT EXISTS (';
            $lines[] = '    SELECT 1 FROM `lang_definitions`';
            $lines[] = "    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = {$langId}";
            $lines[] = ');';
            $lines[] = '';
        }

        if ($contract->derivation instanceof TranslationDerivation) {
            $lines = array_merge($lines, $this->derivationStatement($contract->derivation));
        }

        // Legacy neutralisation is emitted for schema v2 only. Version 1's rendering is frozen on
        // purpose: `database-upgrade.json` already ships an explicit definition per language, and
        // re-rendering it with an extra statement would change a supplement that installed
        // databases have already run, for no gain.
        if ($contract->schema === TranslationCatalogueContract::SCHEMA_V2) {
            foreach ($contract->legacyKeys as $legacyKey => $identity) {
                $lines = array_merge($lines, $this->legacyStatement($legacyKey, $identity));
            }
        }

        $lines[] = 'SET @openemr_translation_contract_cons_id = NULL;';
        $lines[] = '';

        return $lines;
    }

    /**
     * Carry every source translation forward, skipping languages an explicit definition already
     * covered above and skipping any source text containing `%`.
     *
     * The source rows are wrapped in a derived table so the statement reads as one set operation,
     * and the anti-join against the target's existing rows is what makes a second run a no-op.
     *
     * @return list<string>
     */
    private function derivationStatement(TranslationDerivation $derivation): array
    {
        $source = self::quote($derivation->sourceKey);
        $composed = match ($derivation->placement) {
            TranslationPlacement::Prefix => "CONCAT('%s ', `src`.`definition`)",
            TranslationPlacement::Suffix => "CONCAT(`src`.`definition`, ' %s')",
        };

        return [
            '-- Carry forward: ' . $derivation->sourceKey . ' (' . $derivation->placement->value . ')',
            'INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)',
            "SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, {$composed}",
            'FROM (',
            '    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`',
            '    FROM `lang_definitions` `d`',
            '    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`',
            "    WHERE BINARY `c`.`constant_name` = '{$source}'",
            '      AND ' . self::NO_PERCENT_IN_SOURCE,
            ') AS `src`',
            'LEFT JOIN `lang_definitions` `existing`',
            '    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id',
            '    AND `existing`.`lang_id` = `src`.`lang_id`',
            'WHERE @openemr_translation_contract_cons_id IS NOT NULL',
            '  AND `existing`.`def_id` IS NULL;',
            '',
        ];
    }

    /**
     * Carry a brand-bearing constant forward by replacing the identity literal with `%s`.
     *
     * The SQL mirror of TranslationCatalogueContract::neutraliseLegacyDefinition(). Only rows that
     * actually contain the literal are taken: a translation that never mentioned the product name
     * has no placeholder to gain, and inserting it unchanged would produce a pattern
     * ProductContextTranslation rejects.
     *
     * @return list<string>
     */
    private function legacyStatement(string $legacyKey, string $identity): array
    {
        $source = self::quote($legacyKey);
        $literal = self::quote($identity);

        return [
            '-- Carry forward: ' . $legacyKey . ' (neutralise "' . $identity . '")',
            'INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)',
            "SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`,"
                . " REPLACE(`src`.`definition`, '{$literal}', '%s')",
            'FROM (',
            '    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`',
            '    FROM `lang_definitions` `d`',
            '    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`',
            "    WHERE BINARY `c`.`constant_name` = '{$source}'",
            // Exactly one occurrence, not merely at least one. A definition naming the product
            // twice would neutralise to a two-placeholder pattern, which ProductContextTranslation
            // refuses — so the row would install cleanly and then fatal the page that rendered it.
            // The PHP path already validates each candidate through compose(); this is the SQL
            // mirror of that guard, and without it the two paths fail in opposite directions.
            "      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, "
                . "'{$literal}', '')) = CHAR_LENGTH('{$literal}')",
            '      AND ' . self::NO_PERCENT_IN_SOURCE,
            ') AS `src`',
            'LEFT JOIN `lang_definitions` `existing`',
            '    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id',
            '    AND `existing`.`lang_id` = `src`.`lang_id`',
            'WHERE @openemr_translation_contract_cons_id IS NOT NULL',
            '  AND `existing`.`def_id` IS NULL;',
            '',
        ];
    }

    private static function quote(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "''"], $value);
    }
}
