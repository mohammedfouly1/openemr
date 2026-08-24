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
use OpenEMR\Common\Translation\TranslationCatalogueContractSet;
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

    /**
     * The deployed supplement must be exactly what the whole contract directory renders — not
     * what one contract renders. A contract that generated into the file but was dropped from the
     * set, or added to the set without regenerating, both fail here rather than reaching an
     * installer.
     */
    public function testGeneratedSqlIsDeterministicAndCurrent(): void
    {
        $set = TranslationCatalogueContractSet::fromProjectDirectory($this->repoRoot());
        $renderer = new TranslationContractSqlRenderer();
        $rendered = $renderer->renderSet($set);
        $deployed = file_get_contents($this->repoRoot() . '/contrib/util/language_translations/durableTranslationContracts_utf8.sql');

        self::assertNotFalse($deployed);
        self::assertSame($rendered, $deployed);
        self::assertSame($rendered, $renderer->renderSet($set), 'Rendering must be deterministic.');
        self::assertStringNotContainsString('SkyEagle', $rendered);

        // Every contract's target key reaches the supplement.
        foreach ($set->all() as $contract) {
            self::assertStringContainsString(
                "BINARY `constant_name` = '" . $contract->targetKey . "'",
                $rendered,
            );
        }
    }

    /**
     * The v1 contract's statements are frozen. Re-rendering it differently would change SQL that
     * installed databases have already executed, and its journal row records the contract hash.
     */
    public function testTheVersionOneContractStillRendersItsTwentyEightExplicitInserts(): void
    {
        $rendered = (new TranslationContractSqlRenderer())->render($this->contract());

        self::assertSame(28, substr_count($rendered, 'INSERT INTO `lang_definitions`'));
        self::assertStringContainsString("BINARY `constant_name` = '%s Database Upgrade'", $rendered);

        // v1 never emits carry-forward SQL, even though it declares legacy keys.
        self::assertStringNotContainsString('-- Carry forward:', $rendered);
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
