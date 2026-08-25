<?php

/**
 * Regression contract: the language editor cannot save a translation that fatals its call site.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use OpenEMR\Common\Translation\ProductContextTranslation;
use OpenEMR\Common\Translation\TranslationCatalogueContractSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Finding S3-P1-31, from Scan-3A.
 *
 * The composition work introduced a class of catalogue constant that is a *pattern*: `%s Login`,
 * `About %s`. The code rendering one calls `ProductContextTranslation::compose()`, which throws
 * unless the string carries exactly one `%s` or `%1$s`. `interface/language/lang_definition.php`
 * is the admin UI for editing translations, and it wrote whatever was typed. An administrator
 * translating `%s Login` and dropping the placeholder — the natural thing to do if you have never
 * heard of the convention, since the placeholder looks like noise — therefore took the OAuth2
 * login page to a 500 for that locale, with nothing in the editor to say so and no obvious way
 * back other than finding the row again.
 *
 * The subsystem validates on the way in from a contract; this closes the other door.
 *
 * **The interesting half is the classification, not the rejection.** Refusing too much would be
 * its own defect: `Atropine 1%` and `Pct (%) of rows` are real catalogue constants, and an editor
 * that demanded a `%s` in their translations would be worse than the bug it replaced. The guard
 * therefore asks `compose()` to classify, so the two sets are decided by the same code that will
 * later render them, and this test pins both directions against real shipped data.
 */
#[Group('isolated')]
final class LanguageEditorPlaceholderGuardContractTest extends TestCase
{
    /**
     * Every catalogue constant that contains a `%` and is *not* a product-context pattern.
     *
     * Enumerated from the live catalogue (`SELECT constant_name FROM lang_constants WHERE
     * constant_name LIKE '%\%%'`, 16 rows on a 13,235-constant database). They are listed rather
     * than queried because the isolated suite has no database, and because a hard-coded list makes
     * a future regression a visible diff instead of a silently shrinking scan.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unguardedConstantProvider(): array
    {
        $constants = [
            'Atropine 1%',
            'Cyclo 1%',
            'Neo 10%',
            'Neo 2.5%',
            'Optional drug name, use % as a wildcard',
            'Optional immunization name or code, use % as a wildcard',
            'Optional lot number, use % as a wildcard',
            'Pct (%) of rows',
            'Pt %',
            'Result, use % as a wildcard',
            'Tropic 2.5%',
            'Use % alone in a field to just sort on that column',
            '%',
            '% Canceled < 24h',
            '% Cancelled <  24h ',
            '(% matches any string, _ matches any character)',
        ];

        $cases = [];
        foreach ($constants as $constant) {
            $cases[$constant] = [$constant];
        }

        return $cases;
    }

    /**
     * A constant that merely contains a percent sign must stay editable without restriction.
     */
    #[DataProvider('unguardedConstantProvider')]
    public function testPercentBearingConstantsAreNotTreatedAsProductPatterns(string $constant): void
    {
        self::assertFalse(
            $this->isProductContextConstant($constant),
            'S3-P1-31: "' . $constant . '" is ordinary catalogue text. Guarding it would force '
            . 'translators to invent a placeholder that nothing composes.',
        );
    }

    /**
     * Every key a contract ships is a pattern, so every one of them must be guarded.
     */
    public function testEveryShippedContractTargetIsTreatedAsAProductPattern(): void
    {
        $set = TranslationCatalogueContractSet::fromProjectDirectory($this->root());
        $checked = 0;

        foreach ($set->all() as $contract) {
            self::assertTrue(
                $this->isProductContextConstant($contract->targetKey),
                'S3-P1-31: ' . $contract->id . ' ships a target key the editor would not guard: '
                . $contract->targetKey,
            );
            $checked++;
        }

        // A floor, so a loader that silently yields nothing cannot pass this vacuously.
        self::assertGreaterThanOrEqual(28, $checked);
    }

    /**
     * The rejected shapes, which are exactly the ones that used to reach production.
     *
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rejectedDefinitionProvider(): array
    {
        return [
            'placeholder dropped entirely' => ['%s Login', 'تسجيل الدخول'],
            'placeholder doubled' => ['About %s', 'About %s and %s'],
            'placeholder mistyped as %d' => ['%s Login', '%d Login'],
            'stray percent introduced' => ['About %s', 'About %s 50% off'],
        ];
    }

    /**
     * @param string $target     the pattern constant being edited
     * @param string $definition the translation an administrator typed
     */
    #[DataProvider('rejectedDefinitionProvider')]
    public function testDefinitionsThatWouldFatalTheCallSiteAreRejected(
        string $target,
        string $definition,
    ): void {
        self::assertTrue($this->isProductContextConstant($target));
        self::assertFalse(
            $this->isProductContextConstant($definition),
            'This definition composes, so the guard would let it through and it would not fatal. '
            . 'Pick a genuinely broken example or delete the case.',
        );
    }

    /**
     * A correct translation still saves — including one that moves the placeholder, which is the
     * entire reason the pattern exists rather than a hard-coded juxtaposition.
     */
    public function testATranslationThatKeepsThePlaceholderIsAccepted(): void
    {
        foreach (['%s تسجيل الدخول', 'تسجيل الدخول %s', 'حول %s', '%1$s Login'] as $definition) {
            self::assertTrue(
                $this->isProductContextConstant($definition),
                'S3-P1-31: the guard must not reject a valid translation: ' . $definition,
            );
        }
    }

    /**
     * Both write paths in the editor are guarded, not just the one that is easier to find.
     *
     * `lang_definition.php` inserts new definitions in one loop and updates existing ones in
     * another, several dozen lines apart. Guarding only the insert would leave the more common
     * operation — correcting a translation that already exists — completely open.
     */
    public function testBothEditorWritePathsCallTheGuard(): void
    {
        $source = $this->read($this->root() . '/interface/language/lang_definition.php');

        self::assertStringContainsString('function lang_definition_placeholder_error(', $source);
        self::assertStringContainsString(
            'ProductContextTranslation::compose',
            $source,
            'The guard must classify with the same parser that renders, not a substring test.',
        );

        // One declaration plus two call sites: the insert loop and the update loop. Guarding only
        // the insert would leave the more common operation wide open.
        self::assertSame(
            2,
            substr_count($source, '= lang_definition_placeholder_error('),
            'S3-P1-31: the insert path and the update path must both call the guard.',
        );

        // Rejection has to be visible. A guard that drops the edit silently is a worse bug than
        // the 500 it prevents, because the administrator retypes it and it vanishes again.
        self::assertStringContainsString('$rejectedDefinitions', $source);
        self::assertStringContainsString('alert-danger', $source);
    }

    /**
     * Mirrors the classification half of `lang_definition_placeholder_error()`.
     */
    private function isProductContextConstant(string $candidate): bool
    {
        try {
            ProductContextTranslation::compose($candidate, 'X');

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }

    private function read(string $path): string
    {
        $contents = file_get_contents($path);
        self::assertIsString($contents);

        return str_replace("\r\n", "\n", $contents);
    }

    private function root(): string
    {
        return dirname(__DIR__, 4);
    }
}
