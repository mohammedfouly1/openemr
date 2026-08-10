<?php

/**
 * TokenSet and DesignToken immutability and ordering.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Token;

use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use OpenEMR\Modules\ThiqaBranding\Token\ColorValue;
use OpenEMR\Modules\ThiqaBranding\Token\DesignToken;
use OpenEMR\Modules\ThiqaBranding\Token\TokenKey;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSet;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/token_autoloader.php';

final class TokenSetTest extends TestCase
{
    private static function token(TokenKey $key, string $value): DesignToken
    {
        return new DesignToken($key, new ColorValue($value));
    }

    public function testGetReturnsNullForAnUndefinedKey(): void
    {
        $set = new TokenSet(ThemeVariant::Light, self::token(TokenKey::Background, '#FAFAF8'));

        self::assertNull($set->get(TokenKey::SurfaceRaised));
        self::assertNull($set->valueOf(TokenKey::SurfaceRaised));
        self::assertFalse($set->has(TokenKey::SurfaceRaised));
    }

    public function testGetReturnsTheStoredToken(): void
    {
        $token = self::token(TokenKey::Background, '#fafaf8');
        $set = new TokenSet(ThemeVariant::Light, $token);

        self::assertSame($token, $set->get(TokenKey::Background));
        self::assertTrue($set->has(TokenKey::Background));
        self::assertSame('#FAFAF8', $set->valueOf(TokenKey::Background)?->value);
        self::assertSame(ThemeVariant::Light, $set->variant);
        self::assertCount(1, $set);
    }

    public function testLaterTokenWinsForTheSameKey(): void
    {
        $set = new TokenSet(
            ThemeVariant::Dark,
            self::token(TokenKey::LinkDefault, '#111111'),
            self::token(TokenKey::LinkDefault, '#222222'),
        );

        self::assertSame('#222222', $set->valueOf(TokenKey::LinkDefault)?->value);
        self::assertCount(1, $set);
    }

    public function testAllIsOrderedByEnumDeclarationNotInsertion(): void
    {
        $set = new TokenSet(
            ThemeVariant::Light,
            self::token(TokenKey::LinkHover, '#1E4574'),
            self::token(TokenKey::BrandNavy, '#0B1B4D'),
            self::token(TokenKey::Background, '#FAFAF8'),
        );

        self::assertSame(
            [TokenKey::BrandNavy, TokenKey::Background, TokenKey::LinkHover],
            $set->keys(),
        );
    }

    public function testWithReturnsANewInstanceAndLeavesTheOriginalUntouched(): void
    {
        $original = new TokenSet(
            ThemeVariant::Light,
            self::token(TokenKey::Background, '#FAFAF8'),
            self::token(TokenKey::LinkDefault, '#2C5F94'),
        );

        $overlaid = $original->with(self::token(TokenKey::LinkDefault, '#1E4574'));

        self::assertNotSame($original, $overlaid);
        self::assertSame('#2C5F94', $original->valueOf(TokenKey::LinkDefault)?->value);
        self::assertSame('#1E4574', $overlaid->valueOf(TokenKey::LinkDefault)?->value);
        self::assertSame('#FAFAF8', $overlaid->valueOf(TokenKey::Background)?->value);
        self::assertSame(ThemeVariant::Light, $overlaid->variant);
    }

    public function testWithNoArgumentsStillReturnsANewEqualSet(): void
    {
        $original = new TokenSet(ThemeVariant::Dark, self::token(TokenKey::Background, '#0B1220'));
        $copy = $original->with();

        self::assertNotSame($original, $copy);
        self::assertSame($original->keys(), $copy->keys());
    }

    public function testDesignTokenWitherReplacesOnlyTheValue(): void
    {
        $token = self::token(TokenKey::LinkDefault, '#2C5F94');
        $updated = $token->withValue(new ColorValue('#1e4574'));

        self::assertNotSame($token, $updated);
        self::assertSame(TokenKey::LinkDefault, $updated->key);
        self::assertSame('#1E4574', $updated->value->value);
        self::assertSame('#2C5F94', $token->value->value);
    }

    public function testDesignTokenEquality(): void
    {
        $token = DesignToken::fromLiteral(TokenKey::LinkDefault, '#2c5f94');

        self::assertTrue($token->equals(self::token(TokenKey::LinkDefault, '#2C5F94')));
        self::assertFalse($token->equals(self::token(TokenKey::LinkHover, '#2C5F94')));
        self::assertFalse($token->equals(self::token(TokenKey::LinkDefault, '#000000')));
    }
}
