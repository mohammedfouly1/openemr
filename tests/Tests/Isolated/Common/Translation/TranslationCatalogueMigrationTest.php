<?php

/** @package OpenEMR */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Translation;

use OpenEMR\Common\Translation\ProductContextTranslation;
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

    // ------------------------------------- precedence: explicit beats carry-forward (S3-P0-28)

    /**
     * An explicit contract definition must WIN over the neutralised legacy string.
     *
     * The generated installer SQL inserts explicit definitions first and anti-joins the
     * carry-forward against rows already present, so SQL has always preferred the explicit value.
     * The PHP path used to overwrite it. On real shipped data those differ — the contract declares
     * French "Mettre à jour la base de donnée de %s" while the upstream seed's legacy row
     * neutralises to "…d'%s" — so a site installed from the SQL and then upgraded through the PHP
     * path hit "Conflicting target definition for lang_id 8", uncaught, mid-upgrade.
     */
    public function testAnExplicitDefinitionBeatsTheNeutralisedLegacyValue(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Database Upgrade', [8 => "Mettre a jour la base de donnee d'OpenEMR"]);

        $contract = TranslationCatalogueContract::fromJson(json_encode([
            'schema' => 'openemr-translation-contract/1',
            'id' => 'precedence-test',
            'target_key' => '%s Database Upgrade',
            'legacy_keys' => ['OpenEMR Database Upgrade' => 'OpenEMR'],
            'definitions' => ['8' => 'Mettre a jour la base de donnee de %s'],
        ], JSON_THROW_ON_ERROR));

        $this->migration()->forward($contract, $store);

        self::assertSame(
            [8 => 'Mettre a jour la base de donnee de %s'],
            $store->definitionsOf('%s Database Upgrade'),
            'The explicit contract definition must win, matching what the installer SQL inserts.',
        );
    }

    /**
     * The other half of the same rule: carry-forward still fills a language the contract does not
     * mention. Losing that would silently drop translations for every locale outside the explicit
     * set, which is the failure this whole subsystem exists to prevent.
     */
    public function testCarryForwardStillFillsLanguagesTheContractDoesNotDeclare(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Database Upgrade', [
            8 => "Mettre a jour la base de donnee d'OpenEMR",
            77 => 'Bespoke OpenEMR upgrade',
        ]);

        $contract = TranslationCatalogueContract::fromJson(json_encode([
            'schema' => 'openemr-translation-contract/1',
            'id' => 'precedence-gapfill',
            'target_key' => '%s Database Upgrade',
            'legacy_keys' => ['OpenEMR Database Upgrade' => 'OpenEMR'],
            'definitions' => ['8' => 'Mettre a jour la base de donnee de %s'],
        ], JSON_THROW_ON_ERROR));

        $this->migration()->forward($contract, $store);

        self::assertSame(
            [8 => 'Mettre a jour la base de donnee de %s', 77 => 'Bespoke %s upgrade'],
            $store->definitionsOf('%s Database Upgrade'),
        );
    }

    /**
     * The end-to-end shape of the reported defect: the target already holds what the installer SQL
     * would have written, and a later upgrade must be a clean no-op rather than a thrown conflict.
     */
    public function testUpgradingASiteInstalledFromTheSqlPathDoesNotConflict(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Database Upgrade', [8 => "Mettre a jour la base de donnee d'OpenEMR"]);
        // What the generated supplement inserts on a fresh install:
        $store->seedConstant('%s Database Upgrade', [8 => 'Mettre a jour la base de donnee de %s']);

        $contract = TranslationCatalogueContract::fromJson(json_encode([
            'schema' => 'openemr-translation-contract/1',
            'id' => 'precedence-upgrade',
            'target_key' => '%s Database Upgrade',
            'legacy_keys' => ['OpenEMR Database Upgrade' => 'OpenEMR'],
            'definitions' => ['8' => 'Mettre a jour la base de donnee de %s'],
        ], JSON_THROW_ON_ERROR));

        $result = $this->migration()->forward($contract, $store);

        self::assertSame('already_current', $result->action);
    }

    // --------------------------------------- schema v2: missing-identity policy (S2-P1-26)

    /**
     * The default refuses. A translation that never named the product has no placeholder position
     * to infer, and silently dropping it would lose that locale without anyone noticing.
     */
    public function testADefinitionWithoutTheIdentityLiteralIsFatalByDefault(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Users', [3 => 'Usuarios de OpenEMR', 7 => 'משתמשים']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('without losing identity context');
        $this->migration()->forward($this->legacyContract('%s Users', 'OpenEMR Users', 'fail'), $store);
    }

    /**
     * `skip` is the deliberate opt-out, and it is what makes the PHP upgrade path agree with the
     * generated installer SQL, which has always skipped these rows. Measured on the real
     * catalogue, every brand-bearing key has one to four of them.
     */
    public function testSkipPolicyLeavesUntranslatedIdentityRowsBehind(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Users', [3 => 'Usuarios de OpenEMR', 7 => 'משתמשים', 22 => 'مستخدمو OpenEMR']);

        $result = $this->migration()->forward($this->legacyContract('%s Users', 'OpenEMR Users', 'skip'), $store);

        self::assertSame('applied', $result->action);
        self::assertSame(
            [3 => 'Usuarios de %s', 22 => 'مستخدمو %s'],
            $store->definitionsOf('%s Users'),
            'Only rows that actually named the product may be carried forward.',
        );
        // The source is never touched, so other call sites keep their translations.
        self::assertSame(
            [3 => 'Usuarios de OpenEMR', 7 => 'משתמשים', 22 => 'مستخدمو OpenEMR'],
            $store->definitionsOf('OpenEMR Users'),
        );
    }

    /** An explicit contract definition settles the language regardless of policy. */
    public function testAnExplicitDefinitionCoversALanguageThatNeverNamedTheProduct(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Users', [7 => 'משתמשים']);

        $contract = TranslationCatalogueContract::fromJson(<<<'JSON'
            {
                "schema": "openemr-translation-contract/2",
                "id": "users-explicit",
                "target_key": "%s Users",
                "legacy_keys": {"OpenEMR Users": "OpenEMR"},
                "definitions": {"7": "משתמשי %s"}
            }
            JSON);

        $result = $this->migration()->forward($contract, $store);

        self::assertSame('applied', $result->action);
        self::assertSame([7 => 'משתמשי %s'], $store->definitionsOf('%s Users'));
    }

    /** Every carried-forward pattern must still be composable, whichever policy applied. */
    public function testEveryCarriedPatternRemainsComposable(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Users', [3 => 'Usuarios de OpenEMR', 22 => 'مستخدمو OpenEMR']);

        $this->migration()->forward($this->legacyContract('%s Users', 'OpenEMR Users', 'skip'), $store);

        foreach ($store->definitionsOf('%s Users') as $pattern) {
            self::assertNotSame('', ProductContextTranslation::compose($pattern, 'Thiqa'));
        }
    }

    private function legacyContract(string $target, string $legacyKey, string $policy): TranslationCatalogueContract
    {
        return TranslationCatalogueContract::fromJson(json_encode([
            'schema' => 'openemr-translation-contract/2',
            'id' => 'legacy-' . $policy . '-' . $legacyKey,
            'target_key' => $target,
            'legacy_keys' => [$legacyKey => 'OpenEMR'],
            'on_missing_identity' => $policy,
            'definitions' => (object) [],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    // ------------------------------------------------- schema v2: derive-from carry-forward

    /**
     * The whole point of the derivation. `About` has 24 translations including a live Arabic one;
     * moving the call site to the key `About %s` without carrying them forward would drop every
     * one of them to English — the RB-01 failure mode. Each locale's pattern is its own existing
     * translation with the placeholder where the template put the product name, so rendering is
     * byte-identical to before and only the join point becomes translator-editable.
     */
    public function testSuffixDerivationCarriesEveryLanguageForwardUnchanged(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('About', [22 => 'حول', 7 => 'אודות', 5 => 'Uber']);

        $result = $this->migration()->forward($this->derivedContract('About %s', 'About', 'suffix'), $store);

        self::assertSame('applied', $result->action);
        self::assertSame(3, $result->definitionsChanged);
        self::assertSame(
            [5 => 'Uber %s', 7 => 'אודות %s', 22 => 'حول %s'],
            $store->definitionsOf('About %s'),
        );
        // The source constant is never touched; other call sites keep using it.
        self::assertSame([5 => 'Uber', 7 => 'אודות', 22 => 'حول'], $store->definitionsOf('About'));
    }

    public function testPrefixDerivationPutsThePlaceholderFirst(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('Login', [22 => 'تسجيل الدخول', 3 => 'Acceso']);

        $this->migration()->forward($this->derivedContract('%s Login', 'Login', 'prefix'), $store);

        self::assertSame(
            [3 => '%s Acceso', 22 => '%s تسجيل الدخول'],
            $store->definitionsOf('%s Login'),
        );
    }

    /**
     * S4-P0-40. A source translation containing `%` is SKIPPED, exactly as the generated installer
     * SQL skips it — not refused with an exception.
     *
     * This test previously asserted the throw, and in doing so encoded the defect as the contract.
     * The reasoning it carried was sound as far as it went: a `%` in the source cannot be turned
     * into a safe pattern, because the sign may be literal (`100%`) and nothing can tell that from
     * a placeholder without reading the sentence. Refusing to WRITE the row is correct. Refusing by
     * THROWING is not, because the generated installer SQL filters the identical row out with
     * `NO_PERCENT_IN_SOURCE` — so a fresh install skipped it silently while an upgrade died on it,
     * after the DDL had applied and before the version row was bumped, wedging the site.
     *
     * What is asserted now is the property that actually matters: the bad row does not reach the
     * catalogue, the good rows in the same batch still do, and the migration completes.
     */
    public function testASourceDefinitionContainingPercentIsSkippedNotRefused(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('Insurance Companies', [
            5 => '100% Versicherung',
            3 => 'Companias de seguros',
        ]);

        $result = $this->migration()->forward(
            $this->derivedContract('Insurance Companies %s', 'Insurance Companies', 'suffix'),
            $store,
        );

        self::assertNotNull($result, 'The migration must complete rather than throw.');
        self::assertSame(
            [3 => 'Companias de seguros %s'],
            $store->definitionsOf('Insurance Companies %s'),
            'The percent-bearing locale must be skipped and every other locale still carried.',
        );
    }

    /**
     * S4-P0-40, the other half. A legacy definition naming the product TWICE is skipped.
     *
     * This case had no test at all, which is why it survived. Neutralising it yields a
     * two-placeholder pattern that `ProductContextTranslation` refuses, so before the fix the row
     * either installed and then fatalled the page that rendered it, or fatalled the upgrade itself.
     * The installer SQL has always excluded it with a CHAR_LENGTH occurrence count; this asserts
     * the PHP path agrees.
     */
    public function testALegacyDefinitionNamingTheProductTwiceIsSkipped(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('OpenEMR Login', [
            8 => 'OpenEMR: OpenEMR a besoin de Javascript.',
            3 => 'Acceso a OpenEMR',
        ]);

        $result = $this->migration()->forward(
            $this->legacyContract('%s Login', 'OpenEMR Login', 'skip'),
            $store,
        );

        self::assertNotNull($result, 'The migration must complete rather than throw.');
        self::assertSame(
            [3 => 'Acceso a %s'],
            $store->definitionsOf('%s Login'),
            'The twice-naming locale must be skipped and the single-naming locale still carried.',
        );
    }

    /**
     * `Product Registration` has no catalogue row at all. Deriving nothing from it is correct:
     * the neutral key then falls back to its own English text, which is what that call site
     * already renders.
     */
    public function testAnAbsentSourceLeavesTheTargetEmptyRatherThanFailing(): void
    {
        $store = new MemoryTranslationCatalogueStore();

        $result = $this->migration()->forward(
            $this->derivedContract('%s Product Registration', 'Product Registration', 'prefix'),
            $store,
        );

        self::assertSame('applied', $result->action);
        self::assertSame(0, $result->definitionsChanged);
        self::assertSame([], $store->definitionsOf('%s Product Registration'));
    }

    /** An explicit definition in the contract beats the mechanical derivation for that language. */
    public function testAnExplicitDefinitionOverridesTheDerivationForThatLanguage(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('About', [22 => 'حول', 5 => 'Uber']);

        $contract = TranslationCatalogueContract::fromJson(<<<'JSON'
            {
                "schema": "openemr-translation-contract/2",
                "id": "about-test",
                "target_key": "About %s",
                "legacy_keys": {},
                "derive_from": {"source_key": "About", "placement": "suffix"},
                "definitions": {"5": "Uber das %s Produkt"}
            }
            JSON);

        $this->migration()->forward($contract, $store);

        self::assertSame(
            [5 => 'Uber das %s Produkt', 22 => 'حول %s'],
            $store->definitionsOf('About %s'),
        );
    }

    /** Re-running a derived contract is a journalled no-op, exactly as for an explicit one. */
    public function testASecondForwardRunOfADerivedContractIsANoOp(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('About', [22 => 'حول']);
        $contract = $this->derivedContract('About %s', 'About', 'suffix');

        $this->migration()->forward($contract, $store);
        $second = $this->migration()->forward($contract, $store);

        self::assertSame('already_applied', $second->action);
        self::assertSame([22 => 'حول %s'], $store->definitionsOf('About %s'));
    }

    /** Rollback removes only what the derivation added, and restores the recorded counts. */
    public function testRollbackOfADerivedContractRemovesOnlyItsOwnRows(): void
    {
        $store = new MemoryTranslationCatalogueStore();
        $store->seedConstant('About', [22 => 'حول', 5 => 'Uber']);
        $contract = $this->derivedContract('About %s', 'About', 'suffix');

        $this->migration()->forward($contract, $store);
        $result = $this->migration()->rollback($contract, $store);

        self::assertSame('rolled_back', $result->action);
        self::assertSame([], $store->definitionsOf('About %s'));
        self::assertSame([5 => 'Uber', 22 => 'حول'], $store->definitionsOf('About'));
    }

    private function derivedContract(string $target, string $source, string $placement): TranslationCatalogueContract
    {
        return TranslationCatalogueContract::fromJson(json_encode([
            'schema' => 'openemr-translation-contract/2',
            'id' => 'derived-' . $source,
            'target_key' => $target,
            'legacy_keys' => (object) [],
            'derive_from' => ['source_key' => $source, 'placement' => $placement],
            'definitions' => (object) [],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
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
        return $this->definitionsOf('%s Database Upgrade');
    }

    /** @return array<int, string> */
    public function definitionsOf(string $name): array
    {
        $ids = $this->constantIds($name);
        return $ids === [] ? [] : $this->definitions($ids[0]);
    }

    public function replaceTargetDefinition(int $languageId, string $definition): void
    {
        $id = $this->constantIds('%s Database Upgrade')[0];
        $this->definitionsByConstant[$id][$languageId] = $definition;
    }
}
