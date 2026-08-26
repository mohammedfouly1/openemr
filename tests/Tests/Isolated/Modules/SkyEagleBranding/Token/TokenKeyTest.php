<?php

/**
 * TokenKey is the closed allowlist; these tests pin its shape.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Token;

use OpenEMR\Modules\SkyEagleBranding\Token\ContrastRule;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/token_autoloader.php';

final class TokenKeyTest extends TestCase
{
    /**
     * The exact Tier 2 surface from plan §3.4.1. Written out rather than derived so a
     * change to isTenantOverridable() has to be made twice, deliberately.
     */
    private const EXPECTED_OVERRIDABLE = [
        'interactive.primary.default',
        'interactive.primary.hover',
        'interactive.primary.active',
        'interactive.primary.disabled',
        'interactive.primary.textOn',
        'interactive.secondary.default',
        'interactive.secondary.hover',
        'interactive.secondary.textOn',
        'interactive.focusRing',
        'link.default',
        'link.hover',
    ];

    /**
     * There is no code path from a string to a TokenKey other than tryFrom/from.
     *
     * Driven by a provider rather than inline literals on purpose: with literal arguments
     * PHPStan folds tryFrom() to null at analysis time and reports every assertion as
     * "will always evaluate to true", which means the check is proven by the analyser and
     * never actually exercised. Routing the same inputs through a provider keeps the
     * argument typed as a plain string, so the assertion is a real runtime check.
     */
    #[DataProvider('hostileKeyProvider')]
    public function testAllowlistIsClosedToArbitraryStrings(string $candidate): void
    {
        self::assertNull(TokenKey::tryFrom($candidate));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hostileKeyProvider(): array
    {
        return [
            'css custom property' => ['--injected'],
            'declaration break out' => ['link.default;}'],
            'trailing newline' => ["link.default\n"],
            'style element close' => ['</style>'],
            'empty string' => [''],
            'oversized input' => [str_repeat('a', 5000)],
        ];
    }

    public function testOverridableSetIsExactlyTheInteractiveAndLinkSurface(): void
    {
        $actual = array_map(
            static fn (TokenKey $key): string => $key->value,
            TokenKey::tenantOverridableKeys(),
        );

        self::assertSame(self::EXPECTED_OVERRIDABLE, $actual);
        self::assertCount(
            10,
            array_filter(
                TokenKey::tenantOverridableKeys(),
                static fn (TokenKey $key): bool => $key->contrastRule() instanceof ContrastRule,
            ),
            'The disabled primary fill is the sole tenant override without a WCAG contrast rule.',
        );
    }

    /**
     * @return array<string, array{TokenKey}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function semanticStateProvider(): array
    {
        $cases = [];
        foreach (TokenKey::cases() as $key) {
            if (str_starts_with($key->value, 'semantic.')) {
                $cases[$key->value] = [$key];
            }
        }

        return $cases;
    }

    /**
     * Semantic state colour is a clinical signal, not decoration: a tenant must never
     * be able to recolour "critical" toward its own palette.
     */
    #[DataProvider('semanticStateProvider')]
    public function testSemanticStateColoursAreNeverOverridable(TokenKey $key): void
    {
        self::assertFalse($key->isTenantOverridable());
    }

    public function testTwelveSemanticStateTokensExist(): void
    {
        self::assertCount(12, self::semanticStateProvider());
    }

    /**
     * @return array<string, array{TokenKey}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function everyKeyProvider(): array
    {
        $cases = [];
        foreach (TokenKey::cases() as $key) {
            $cases[$key->value] = [$key];
        }

        return $cases;
    }

    /** The renderer's safety argument rests on this: property names are a closed literal set. */
    #[DataProvider('everyKeyProvider')]
    public function testCssVariableNameIsASafeCustomPropertyName(TokenKey $key): void
    {
        self::assertMatchesRegularExpression('/\A--[a-z0-9-]+\z/', $key->cssVariableName());
    }

    public function testCssVariableNamesAreUnique(): void
    {
        $names = array_map(
            static fn (TokenKey $key): string => $key->cssVariableName(),
            TokenKey::cases(),
        );

        self::assertCount(count($names), array_unique($names));
    }

    #[DataProvider('everyKeyProvider')]
    public function testContrastRuleIsSelfConsistent(TokenKey $key): void
    {
        $rule = $key->contrastRule();

        if (!$rule instanceof ContrastRule) {
            // No gate is attached to this key. Counted rather than asserted: assertTrue(true)
            // is a tautology PHPStan rejects, and this branch has nothing to assert.
            $this->addToAssertionCount(1);

            return;
        }

        self::assertSame($key, $rule->foreground);
        self::assertTrue($rule->involves($key));
        self::assertTrue(
            $key->isTenantOverridable(),
            'Only the movable Tier 2 surface carries contrast rules.',
        );
    }

    public function testDisabledControlUsesASeparateProductRuleBecauseWcagExemptsIt(): void
    {
        self::assertNull(TokenKey::InteractivePrimaryDisabled->contrastRule());
        self::assertTrue(TokenKey::InteractivePrimaryDisabled->isTenantOverridable());
    }

    public function testBrandIdentityAndStructureAreNotOverridable(): void
    {
        self::assertFalse(TokenKey::BrandNavy->isTenantOverridable());
        self::assertFalse(TokenKey::BrandCoral->isTenantOverridable());
        self::assertFalse(TokenKey::Background->isTenantOverridable());
        self::assertFalse(TokenKey::Surface->isTenantOverridable());
        self::assertFalse(TokenKey::Border->isTenantOverridable());
        self::assertFalse(TokenKey::BorderStrong->isTenantOverridable());
        self::assertFalse(TokenKey::Divider->isTenantOverridable());
        self::assertFalse(TokenKey::TextPrimary->isTenantOverridable());
        self::assertFalse(TokenKey::TextInverse->isTenantOverridable());
    }

    /** The enum must stay a superset of the shipped token document. */
    public function testEveryKeyInTheBrandDocumentIsAllowlisted(): void
    {
        foreach (TokenDocumentFixture::dotPaths() as $dotPath) {
            self::assertInstanceOf(
                TokenKey::class,
                TokenKey::tryFrom($dotPath),
                sprintf('brand/tokens/thiqa-tokens.json defines "%s" but TokenKey does not.', $dotPath),
            );
        }
    }

    /** ...and not a strict superset: an unused case means the allowlist has drifted. */
    public function testEveryAllowlistedKeyAppearsInTheBrandDocument(): void
    {
        $documented = TokenDocumentFixture::dotPaths();

        foreach (TokenKey::cases() as $key) {
            self::assertContains(
                $key->value,
                $documented,
                sprintf('TokenKey::%s is not defined in brand/tokens/thiqa-tokens.json.', $key->name),
            );
        }
    }
}
