<?php

/**
 * TokenSetParser turns the shipped JSON into typed sets and rejects everything else.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Token;

use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenKey;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenParseException;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSetParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/token_autoloader.php';

final class TokenSetParserTest extends TestCase
{
    private TokenSetParser $parser;

    protected function setUp(): void
    {
        $this->parser = new TokenSetParser();
    }

    public function testParsesTheShippedBrandDocument(): void
    {
        $sets = $this->parser->parseDocument(TokenDocumentFixture::json());

        self::assertSame(ThemeVariant::Light, $sets['light']->variant);
        self::assertSame(ThemeVariant::Dark, $sets['dark']->variant);

        // surfaceRaised is dark-only, which is why get() is nullable.
        self::assertFalse($sets['light']->has(TokenKey::SurfaceRaised));
        self::assertTrue($sets['dark']->has(TokenKey::SurfaceRaised));

        self::assertSame('#0B1B4D', $sets['light']->valueOf(TokenKey::BrandNavy)?->value);
        self::assertSame('#FAFAF8', $sets['light']->valueOf(TokenKey::Background)?->value);
        self::assertSame('#8E271D', $sets['light']->valueOf(TokenKey::SemanticCriticalText)?->value);
        self::assertSame('#C43F2E', $sets['light']->valueOf(TokenKey::InteractivePrimaryDefault)?->value);
        self::assertSame('#3E7FBD', $sets['light']->valueOf(TokenKey::InteractiveFocusRing)?->value);
        self::assertSame('#B7D9F5', $sets['dark']->valueOf(TokenKey::LinkHover)?->value);
    }

    public function testEveryDocumentedTokenSurvivesTheRoundTrip(): void
    {
        $sets = $this->parser->parseDocument(TokenDocumentFixture::json());
        $parsed = array_unique(array_merge(
            array_map(static fn (TokenKey $key): string => $key->value, $sets['light']->keys()),
            array_map(static fn (TokenKey $key): string => $key->value, $sets['dark']->keys()),
        ));

        sort($parsed);
        $documented = TokenDocumentFixture::dotPaths();
        sort($documented);

        self::assertSame($documented, $parsed);
    }

    public function testUnknownKeysAreRejectedNotIgnored(): void
    {
        $this->expectException(TokenParseException::class);
        $this->expectExceptionMessage('not in the allowlist');

        $this->parser->parseVariant(ThemeVariant::Light, [
            'background' => '#FAFAF8',
            'somethingNew' => '#FFFFFF',
        ]);
    }

    public function testUnknownNestedKeysAreRejected(): void
    {
        $this->expectException(TokenParseException::class);

        $this->parser->parseVariant(ThemeVariant::Light, [
            'interactive' => ['primary' => ['tertiary' => '#FFFFFF']],
        ]);
    }

    /**
     * A key that is not on the allowlist is refused even when it would otherwise look
     * like a legitimate CSS custom property.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hostileKeyProvider(): array
    {
        return [
            'raw custom property' => ['--injected'],
            'declaration escape'  => ['background; } html {'],
            'style close'         => ['</style><script>alert(1)</script>'],
            'newline in key'      => ["background\n--injected"],
            'at rule'             => ['@import'],
            'empty key'           => [''],
            'over long key'       => [str_repeat('background.', 500)],
        ];
    }

    #[DataProvider('hostileKeyProvider')]
    public function testHostileKeysAreRejected(string $key): void
    {
        $this->expectException(TokenParseException::class);

        $this->parser->parseVariant(ThemeVariant::Light, [$key => '#FFFFFF']);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hostileValueProvider(): array
    {
        return [
            'script tag'         => ['<script>alert(1)</script>'],
            'declaration escape' => ['#FFFFFF; } body { display:none'],
            'url function'       => ['url(https://evil.invalid/x)'],
            'expression'         => ['expression(alert(1))'],
            'javascript uri'     => ['javascript:alert(1)'],
            'newline injection'  => ["#FFFFFF\n--injected: red;"],
            'over long'          => [str_repeat('#FFFFFF', 1000)],
            'shorthand'          => ['#FFF'],
        ];
    }

    #[DataProvider('hostileValueProvider')]
    public function testHostileValuesAreRejected(string $value): void
    {
        $this->expectException(TokenParseException::class);

        $this->parser->parseVariant(ThemeVariant::Light, ['background' => $value]);
    }

    public function testNonStringLeafIsRejected(): void
    {
        $this->expectException(TokenParseException::class);

        $this->parser->parseVariant(ThemeVariant::Light, ['background' => 16777215]);
    }

    public function testNumericSegmentIsRejectedBecauseThereIsNoKeyToAllowlist(): void
    {
        $this->expectException(TokenParseException::class);

        $this->parser->parseVariant(ThemeVariant::Light, ['#FAFAF8', '#FFFFFF']);
    }

    public function testInvalidJsonIsRejectedWithoutEchoingTheInput(): void
    {
        try {
            $this->parser->parseDocument('{"light": ');
            self::fail('Expected TokenParseException.');
        } catch (TokenParseException $exception) {
            self::assertStringContainsString('not valid JSON', $exception->getMessage());
            self::assertStringNotContainsString('"light"', $exception->getMessage());
        }
    }

    public function testMissingVariantSectionIsRejected(): void
    {
        $this->expectException(TokenParseException::class);
        $this->expectExceptionMessage('missing "dark" section');

        $this->parser->parseDocument('{"light": {"background": "#FAFAF8"}}');
    }

    public function testNonObjectDocumentIsRejected(): void
    {
        $this->expectException(TokenParseException::class);
        $this->expectExceptionMessage('top level must be an object');

        $this->parser->parseDocument('"#FFFFFF"');
    }

    public function testParsingIsDeterministic(): void
    {
        $first = $this->parser->parseDocument(TokenDocumentFixture::json());
        $second = $this->parser->parseDocument(TokenDocumentFixture::json());

        self::assertEquals($first['light']->all(), $second['light']->all());
        self::assertEquals($first['dark']->all(), $second['dark']->all());
    }
}
