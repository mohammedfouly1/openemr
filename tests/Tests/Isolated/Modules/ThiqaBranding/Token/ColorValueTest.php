<?php

/**
 * ColorValue rejects everything that is not a #RRGGBB literal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Token;

use InvalidArgumentException;
use OpenEMR\Modules\ThiqaBranding\Token\ColorValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/token_autoloader.php';

final class ColorValueTest extends TestCase
{
    /**
     * Everything a hostile tenant might put in a colour field.
     *
     * These are the payloads that would matter if a colour ever reached a stylesheet
     * as an unvalidated string: context escapes, at-rule smuggling, legacy IE
     * expression(), url() fetches, script tags and newline injection.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hostileValueProvider(): array
    {
        return [
            'script tag'                => ['<script>alert(1)</script>'],
            'style close then script'   => ['</style><script>alert(1)</script>'],
            'valid colour then script'  => ['#FFFFFF</style><script>alert(1)</script>'],
            'declaration escape'        => ['red; } body { display: none'],
            'brace escape after colour' => ['#FFFFFF; } html { opacity: 0 } .x {'],
            'closing brace only'        => ['}'],
            'extra declaration'         => ['#FFFFFF; --injected: red'],
            'url function'              => ['url(https://evil.invalid/beacon.png)'],
            'url after colour'          => ['#FFFFFF url(//evil.invalid/x)'],
            'expression function'       => ['expression(alert(1))'],
            'javascript uri'            => ['javascript:alert(1)'],
            'data uri'                  => ['data:text/html,<script>alert(1)</script>'],
            'at import'                 => ['@import "https://evil.invalid/x.css"'],
            'css comment escape'        => ['#FFFFFF/*'],
            'trailing newline'          => ["#FFFFFF\n"],
            'newline injection'         => ["#FFFFFF\n--injected: red;"],
            'crlf injection'            => ["#FFFFFF\r\n--injected: red;"],
            'leading newline'           => ["\n#FFFFFF"],
            'null byte'                 => ["#FFFFFF\0"],
            'tab suffix'                => ["#FFFFFF\t"],
            'trailing space'            => ['#FFFFFF '],
            'leading space'             => [' #FFFFFF'],
            'empty string'              => [''],
            'hash only'                 => ['#'],
            'missing hash'              => ['FFFFFF'],
            'three digit shorthand'     => ['#FFF'],
            'eight digit with alpha'    => ['#FFFFFFFF'],
            'seven hex digits'          => ['#FFFFFFF'],
            'non hex letters'           => ['#GGGGGG'],
            'named colour'              => ['transparent'],
            'rgb function'              => ['rgb(255,255,255)'],
            'var reference'             => ['var(--text-primary)'],
            'important flag'            => ['#FFFFFF !important'],
            'fullwidth hash'            => ['＃FFFFFF'],
            'unicode escape'            => ['\\23 FFFFFF'],
            'over long repeated colour' => [self::overLong()],
        ];
    }

    /** A 7000-byte payload: length alone must not get a slow path or a partial match. */
    private static function overLong(): string
    {
        return str_repeat('#FFFFFF', 1000);
    }

    #[DataProvider('hostileValueProvider')]
    public function testConstructorRejectsHostileValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ColorValue($value);
    }

    #[DataProvider('hostileValueProvider')]
    public function testTryFromReturnsNullForHostileValue(string $value): void
    {
        self::assertNull(ColorValue::tryFrom($value));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function validValueProvider(): array
    {
        return [
            'uppercase'      => ['#FAFAF8', '#FAFAF8'],
            'lowercase'      => ['#fafaf8', '#FAFAF8'],
            'mixed case'     => ['#FaFaF8', '#FAFAF8'],
            'all zeros'      => ['#000000', '#000000'],
            'all f'          => ['#ffffff', '#FFFFFF'],
            'digits only'    => ['#123456', '#123456'],
            'thiqa coral'    => ['#FF6F5E', '#FF6F5E'],
            'thiqa navy'     => ['#0b1b4d', '#0B1B4D'],
        ];
    }

    #[DataProvider('validValueProvider')]
    public function testNormalisesToUppercase(string $input, string $expected): void
    {
        self::assertSame($expected, (new ColorValue($input))->value);
        self::assertSame($expected, (string) new ColorValue($input));
        self::assertSame($expected, ColorValue::tryFrom($input)?->value);
    }

    public function testEqualityIgnoresInputCase(): void
    {
        self::assertTrue((new ColorValue('#ff6f5e'))->equals(new ColorValue('#FF6F5E')));
        self::assertFalse((new ColorValue('#FF6F5E'))->equals(new ColorValue('#FF6F5F')));
    }

    public function testExceptionMessageDoesNotEchoTheRejectedInput(): void
    {
        // The rejected text is untrusted and this message reaches logs.
        try {
            new ColorValue('</style><script>alert(1)</script>');
            self::fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringNotContainsString('script', $exception->getMessage());
            self::assertStringNotContainsString('<', $exception->getMessage());
        }
    }
}
