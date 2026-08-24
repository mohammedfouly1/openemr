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

final class TranslationContractSqlRenderer
{
    public function render(TranslationCatalogueContract $contract): string
    {
        $target = self::quote($contract->targetKey);
        $lines = [
            '-- Generated from contrib/util/language_translations/contracts/database-upgrade.json.',
            '-- Contract: ' . $contract->id,
            '-- SHA256: ' . $contract->hash,
            '-- Do not edit this generated file directly.',
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

        $lines[] = 'SET @openemr_translation_contract_cons_id = NULL;';
        $lines[] = '';

        return implode("\n", $lines);
    }

    private static function quote(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "''"], $value);
    }
}
