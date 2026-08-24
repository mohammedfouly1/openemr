<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Translation;

use OpenEMR\Common\Translation\ProductContextTranslation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class ProductContextTranslationTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function translatedPatterns(): iterable
    {
        yield 'English fallback' => ['%s Database Upgrade', 'Care & Trust Database Upgrade'];
        yield 'Spanish LTR ordering' => ['Actualización base de datos %s', 'Actualización base de datos Care & Trust'];
        yield 'Arabic RTL ordering' => ['ترقية قاعدة بيانات %s', 'ترقية قاعدة بيانات Care & Trust'];
        yield 'Hebrew RTL ordering' => ['שדרוג מסד נתונים של %1$s', 'שדרוג מסד נתונים של Care & Trust'];
        yield 'literal percent' => ['%s Database Upgrade — 100%%', 'Care & Trust Database Upgrade — 100%'];
    }

    #[DataProvider('translatedPatterns')]
    public function testComposesProductOutsideTheCatalogueKey(string $pattern, string $expected): void
    {
        self::assertSame($expected, ProductContextTranslation::compose($pattern, 'Care & Trust'));
        self::assertSame($expected, ProductContextTranslation::compose($pattern, 'Care & Trust'));
    }

    public function testCallerCanEscapeExactlyOnceAfterComposition(): void
    {
        $raw = ProductContextTranslation::compose('%s Database Upgrade', '<Clinic & "Care">');

        self::assertSame('<Clinic & "Care"> Database Upgrade', $raw);
        self::assertSame(
            '&lt;Clinic &amp; &quot;Care&quot;&gt; Database Upgrade',
            htmlspecialchars($raw, ENT_QUOTES),
        );
    }

    #[DataProvider('invalidPatterns')]
    public function testRejectsUnsafeOrAmbiguousPatterns(string $pattern): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ProductContextTranslation::compose($pattern, 'Product');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidPatterns(): iterable
    {
        yield 'missing placeholder' => ['Database Upgrade'];
        yield 'two placeholders' => ['%s %s Database Upgrade'];
        yield 'numeric format' => ['%d Database Upgrade'];
        yield 'positional second argument' => ['%2$s Database Upgrade'];
        yield 'dangling percent' => ['%s Database Upgrade %'];
    }
}
