<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Translation;

use OpenEMR\Common\Translation\ProductContextTranslation;
use OpenEMR\Common\Translation\TranslationCatalogueContract;
use OpenEMR\Common\Translation\TranslationContractSqlRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class TranslationCatalogueContractTest extends TestCase
{
    private const EXPECTED_LANG_IDS = [
        3, 4, 5, 6, 7, 8, 11, 12, 13, 14, 16, 17, 19, 20, 21, 22,
        24, 27, 28, 29, 30, 33, 34, 37, 40, 47, 50, 59,
    ];

    public function testRepositoryContractPreservesAllTwentyEightLanguageIds(): void
    {
        $contract = $this->contract();

        self::assertSame('%s Database Upgrade', $contract->targetKey);
        self::assertSame(self::EXPECTED_LANG_IDS, array_keys($contract->definitions));
        self::assertCount(28, $contract->definitions);
        self::assertArrayHasKey(7, $contract->definitions);
        self::assertArrayHasKey(22, $contract->definitions);
        self::assertArrayHasKey(37, $contract->definitions);

        foreach ($contract->definitions as $definition) {
            self::assertNotSame('', ProductContextTranslation::compose($definition, 'Product'));
        }
    }

    public function testLegacyDefinitionsAreNeutralisedWithoutNamingAFutureProduct(): void
    {
        $contract = $this->contract();

        self::assertSame(
            'Actualización base de datos %s',
            $contract->neutraliseLegacyDefinition(
                'Thiqa Database Upgrade',
                'Actualización base de datos Thiqa',
            ),
        );
        self::assertSame(
            'שדרוג מסד נתונים של %s',
            $contract->neutraliseLegacyDefinition(
                'OpenEMR Database Upgrade',
                'שדרוג מסד נתונים של OpenEMR',
            ),
        );
    }

    public function testGeneratedSqlIsDeterministicAndCurrent(): void
    {
        $rendered = (new TranslationContractSqlRenderer())->render($this->contract());
        $deployed = file_get_contents($this->repoRoot() . '/contrib/util/language_translations/durableTranslationContracts_utf8.sql');

        self::assertNotFalse($deployed);
        self::assertSame($rendered, $deployed);
        self::assertSame(28, substr_count($rendered, 'INSERT INTO `lang_definitions`'));
        self::assertStringContainsString("BINARY `constant_name` = '%s Database Upgrade'", $rendered);
        self::assertStringNotContainsString('SkyEagle', $rendered);
    }

    public function testRejectsMissingOrUnsafePlaceholders(): void
    {
        $json = <<<'JSON'
            {
                "schema": "openemr-translation-contract/1",
                "id": "bad",
                "target_key": "Database Upgrade",
                "legacy_keys": {},
                "definitions": {"3": "%s Database Upgrade"}
            }
            JSON;

        $this->expectException(\InvalidArgumentException::class);
        TranslationCatalogueContract::fromJson($json);
    }

    private function contract(): TranslationCatalogueContract
    {
        return TranslationCatalogueContract::fromFile(
            $this->repoRoot() . '/contrib/util/language_translations/contracts/database-upgrade.json',
        );
    }

    private function repoRoot(): string
    {
        return dirname(__DIR__, 5);
    }
}
