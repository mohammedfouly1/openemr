<?php

/** @package OpenEMR */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Translation;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class TranslationDurabilitySchemaTest extends TestCase
{
    public function testFreshInstallAndBothUpgradePathsCreateMigrationJournal(): void
    {
        $root = dirname(__DIR__, 5);
        foreach ([
            'sql/database.sql',
            'sql/8_1_1-to-8_2_0_upgrade.sql',
            'sql/patch.sql',
        ] as $relativePath) {
            $sql = file_get_contents($root . '/' . $relativePath);
            self::assertNotFalse($sql);
            self::assertStringContainsString('CREATE TABLE `translation_migration_journal`', $sql, $relativePath);
            self::assertStringContainsString('PRIMARY KEY (`migration_id`)', $sql, $relativePath);
        }
    }

    public function testDatabaseVersionAndSchemaMarkerRemainSynchronized(): void
    {
        $root = dirname(__DIR__, 5);
        $version = file_get_contents($root . '/version.php');
        $schema = file_get_contents($root . '/sql/database.sql');

        self::assertNotFalse($version);
        self::assertNotFalse($schema);
        self::assertMatchesRegularExpression('/\$v_database\s*=\s*542;/', $version);
        self::assertStringContainsString('-- v_database: 542', $schema);
    }

    public function testUpgradeConsumerInvokesJournalledMigrationAfterSqlFiles(): void
    {
        $source = file_get_contents(dirname(__DIR__, 5) . '/sql_upgrade.php');

        self::assertNotFalse($source);
        $patchPosition = strpos($source, "upgradeFromSqlFile('patch.sql')");
        $migrationPosition = strpos($source, 'TranslationCatalogueMigration())->forward');
        self::assertIsInt($patchPosition);
        self::assertIsInt($migrationPosition);
        self::assertGreaterThan($patchPosition, $migrationPosition);
    }
}
