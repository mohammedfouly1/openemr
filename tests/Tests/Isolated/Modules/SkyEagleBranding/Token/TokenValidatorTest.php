<?php

/**
 * TokenValidator: the Tier 2 overlay gate.
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
use OpenEMR\Modules\SkyEagleBranding\Token\ColorValue;
use OpenEMR\Modules\SkyEagleBranding\Token\DesignToken;
use OpenEMR\Modules\SkyEagleBranding\Token\RejectionReason;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenKey;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenRejection;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSet;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSetParser;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/token_autoloader.php';

final class TokenValidatorTest extends TestCase
{
    private TokenValidator $validator;

    private TokenSet $lightBase;

    protected function setUp(): void
    {
        $this->validator = new TokenValidator(new ContrastCalculatorStub());
        $this->lightBase = (new TokenSetParser())
            ->parseDocument(TokenDocumentFixture::json())['light'];
    }

    /** @return list<string> */
    private static function reasons(TokenRejection ...$rejections): array
    {
        // array_values keeps the result a list: array_map over a variadic preserves keys,
        // which PHPStan types as array<int|string, string> rather than list<string>.
        return array_values(array_map(
            static fn (TokenRejection $rejection): string => $rejection->reason->value,
            $rejections,
        ));
    }

    // ---------------------------------------------------------------- happy path

    public function testEmptyOverlayIsValidAndLeavesTheBaseUnchanged(): void
    {
        $result = $this->validator->validateOverlay([], $this->lightBase);

        self::assertTrue($result->isValid());
        self::assertSame([], $result->rejections());
        self::assertSame($this->lightBase->keys(), $result->resolved()?->keys());
    }

    public function testAccessibleOverrideIsAccepted(): void
    {
        $result = $this->validator->validateOverlay(
            ['link.default' => '#1e4574'],
            $this->lightBase,
        );

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
        self::assertSame('#1E4574', $result->resolved()?->valueOf(TokenKey::LinkDefault)?->value);
        // Untouched Tier 1 tokens survive the overlay. No nullsafe on resolved() here: the
        // assertion above already proves it is non-null, so `?->` would be dead syntax.
        self::assertSame('#FAFAF8', $result->resolved()->valueOf(TokenKey::Background)?->value);
    }

    public function testAcceptedOverlayDoesNotMutateTheBase(): void
    {
        $this->validator->validateOverlay(['link.default' => '#1E4574'], $this->lightBase);

        self::assertSame('#2C5F94', $this->lightBase->valueOf(TokenKey::LinkDefault)?->value);
    }

    public function testHighContrastPrimaryIsAcceptedTogetherWithItsInheritedLabelColour(): void
    {
        $result = $this->validator->validateOverlay(
            ['interactive.primary.default' => '#000000'],
            $this->lightBase,
        );

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
    }

    // ------------------------------------------------------------- allowlist gate

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonAllowlistedKeyProvider(): array
    {
        return [
            'raw custom property'  => ['--injected'],
            'brace escape'         => ['link.default; } html {'],
            'style close'          => ['</style><script>alert(1)</script>'],
            'newline in key'       => ["link.default\n--injected"],
            'at rule'              => ['@import'],
            'url function'         => ['url(x)'],
            'empty key'            => [''],
            'near miss casing'     => ['Link.Default'],
            'near miss separator'  => ['link-default'],
            'unknown namespace'    => ['tenant.custom'],
        ];
    }

    #[DataProvider('nonAllowlistedKeyProvider')]
    public function testNonAllowlistedKeyIsRejected(string $key): void
    {
        $result = $this->validator->validateOverlay([$key => '#000000'], $this->lightBase);

        self::assertFalse($result->isValid());
        self::assertNull($result->resolved());
        self::assertSame([RejectionReason::UnknownKey->value], self::reasons(...$result->rejections()));
    }

    public function testOverLongKeyIsRefusedOnLengthBeforeAnythingElse(): void
    {
        $result = $this->validator->validateOverlay(
            [str_repeat('a', TokenValidator::MAX_KEY_LENGTH + 1) => '#000000'],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertSame([RejectionReason::UnknownKey->value], self::reasons(...$result->rejections()));
        self::assertStringContainsString('length', $result->rejections()[0]->detail);
        // The echoed key is clipped so a huge payload cannot flood a log line.
        self::assertLessThanOrEqual(
            TokenRejection::KEY_PREVIEW_LENGTH + 3,
            strlen($result->rejections()[0]->key),
        );
    }

    public function testNumericKeyIsRejected(): void
    {
        $result = $this->validator->validateOverlay(['#000000'], $this->lightBase);

        self::assertFalse($result->isValid());
        self::assertSame([RejectionReason::UnknownKey->value], self::reasons(...$result->rejections()));
    }

    // ---------------------------------------------------------- overridable gate

    /**
     * Every semantic state colour, plus a sample of brand and structural keys.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonOverridableKeyProvider(): array
    {
        $cases = [];
        foreach (TokenKey::cases() as $key) {
            if (!$key->isTenantOverridable()) {
                $cases[$key->value] = [$key->value];
            }
        }

        return $cases;
    }

    /**
     * A valid colour on a locked key is still refused — validity of the value is not
     * what protects the palette; the allowlist is.
     */
    #[DataProvider('nonOverridableKeyProvider')]
    public function testNonOverridableKeyIsRejectedEvenWithAPerfectlyValidColour(string $key): void
    {
        $result = $this->validator->validateOverlay([$key => '#000000'], $this->lightBase);

        self::assertFalse($result->isValid());
        self::assertNull($result->resolved());
        self::assertSame([RejectionReason::NotOverridable->value], self::reasons(...$result->rejections()));
    }

    public function testSemanticCriticalCannotBeRecolouredForClinicalSafety(): void
    {
        $result = $this->validator->validateOverlay(
            [
                'semantic.critical.text' => '#2F6B45',
                'semantic.success.text' => '#8E271D',
            ],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertSame(
            [RejectionReason::NotOverridable->value, RejectionReason::NotOverridable->value],
            self::reasons(...$result->rejections()),
        );
    }

    // -------------------------------------------------------------- value gate

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hostileValueProvider(): array
    {
        return [
            'script tag'              => ['<script>alert(1)</script>'],
            'style close then script' => ['</style><script>alert(1)</script>'],
            'declaration escape'      => ['#FFFFFF; } html { display: none } .x {'],
            'closing brace'           => ['}'],
            'extra declaration'       => ['#FFFFFF; --injected: red'],
            'url function'            => ['url(https://evil.invalid/beacon.png)'],
            'expression function'     => ['expression(alert(1))'],
            'javascript uri'          => ['javascript:alert(1)'],
            'data uri'                => ['data:text/html,<script>alert(1)</script>'],
            'at import'               => ['@import "https://evil.invalid/x.css"'],
            'css comment'             => ['#FFFFFF/*'],
            'newline injection'       => ["#FFFFFF\n--injected: red;"],
            'crlf injection'          => ["#FFFFFF\r\n--injected: red;"],
            'trailing newline'        => ["#FFFFFF\n"],
            'null byte'               => ["#FFFFFF\0"],
            'important flag'          => ['#FFFFFF !important'],
            'var reference'           => ['var(--interactive-primary-default)'],
            'rgb function'            => ['rgb(0,0,0)'],
            'named colour'            => ['black'],
            'shorthand'               => ['#000'],
            'eight digits'            => ['#000000FF'],
            'non hex'                 => ['#ZZZZZZ'],
            'empty'                   => [''],
            'over long'               => [str_repeat('#000000', 1000)],
        ];
    }

    #[DataProvider('hostileValueProvider')]
    public function testHostileValueOnAnOverridableKeyIsRejected(string $value): void
    {
        $result = $this->validator->validateOverlay(
            ['interactive.primary.default' => $value],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertNull($result->resolved());
        self::assertSame([RejectionReason::MalformedValue->value], self::reasons(...$result->rejections()));
    }

    #[DataProvider('hostileValueProvider')]
    public function testRejectionNeverEchoesTheHostileValue(string $value): void
    {
        $result = $this->validator->validateOverlay(['link.default' => $value], $this->lightBase);

        foreach ($result->messages() as $message) {
            self::assertStringNotContainsString('script', $message);
            self::assertStringNotContainsString('<', $message);
            self::assertStringNotContainsString('url(', $message);
            self::assertStringNotContainsString('expression(', $message);
            self::assertStringNotContainsString("\n", $message);
        }
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonStringValueProvider(): array
    {
        return [
            'integer' => [16777215],
            'float'   => [1.5],
            'true'    => [true],
            'false'   => [false],
            'null'    => [null],
            'array'   => [['#000000']],
        ];
    }

    #[DataProvider('nonStringValueProvider')]
    public function testNonStringValueIsRejected(mixed $value): void
    {
        $result = $this->validator->validateOverlay(['link.default' => $value], $this->lightBase);

        self::assertFalse($result->isValid());
        self::assertSame([RejectionReason::MalformedValue->value], self::reasons(...$result->rejections()));
    }

    // ------------------------------------------------------------ contrast gate

    public function testLowContrastLinkIsRejected(): void
    {
        $result = $this->validator->validateOverlay(
            ['link.default' => '#FFFF00'],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertSame(
            [RejectionReason::InsufficientContrast->value],
            self::reasons(...$result->rejections()),
        );
        self::assertStringContainsString('4.5:1', $result->rejections()[0]->detail);
    }

    public function testLowContrastFocusRingIsRejectedAgainstTheUiThreshold(): void
    {
        $result = $this->validator->validateOverlay(
            ['interactive.focusRing' => '#FFFFFE'],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertSame(
            [RejectionReason::InsufficientContrast->value],
            self::reasons(...$result->rejections()),
        );
        self::assertStringContainsString('3.0:1', $result->rejections()[0]->detail);
    }

    /**
     * Overriding the button colour re-opens the label pair even though the label colour
     * itself was not submitted — the gate is on the resulting palette, not the payload.
     */
    public function testOverridingAButtonAlsoRegatesItsInheritedLabelColour(): void
    {
        $result = $this->validator->validateOverlay(
            ['interactive.primary.default' => '#FFFF00'],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertCount(2, $result->rejections());
        foreach ($result->rejections() as $rejection) {
            self::assertSame(RejectionReason::InsufficientContrast, $rejection->reason);
            self::assertSame('interactive.primary.default', $rejection->key);
        }
    }

    public function testLabelColourIsGatedAgainstItsOwnButton(): void
    {
        $tooPale = $this->validator->validateOverlay(
            ['interactive.primary.textOn' => '#EAC1BA'],
            $this->lightBase,
        );
        self::assertFalse($tooPale->isValid());
        self::assertSame(
            [RejectionReason::InsufficientContrast->value],
            self::reasons(...$tooPale->rejections()),
        );

        $legible = $this->validator->validateOverlay(
            ['interactive.primary.textOn' => '#FFFFFF'],
            $this->lightBase,
        );
        self::assertTrue($legible->isValid(), implode(' | ', $legible->messages()));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function validDisabledStateProvider(): array
    {
        return [
            'light' => ['light', '#E5B0A5'],
            'dark' => ['dark', '#6B3D36'],
        ];
    }

    #[DataProvider('validDisabledStateProvider')]
    public function testDisabledStateUsesProductSeparationInsteadOfAWcagGate(string $variant, string $value): void
    {
        $base = (new TokenSetParser())->parseDocument(TokenDocumentFixture::json())[$variant];
        $result = $this->validator->validateOverlay(
            ['interactive.primary.disabled' => $value],
            $base,
        );

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
        self::assertSame($value, $result->resolved()?->valueOf(TokenKey::InteractivePrimaryDisabled)?->value);
    }

    public function testDisabledStateCannotDisappearIntoTheBackground(): void
    {
        $result = $this->validator->validateOverlay(
            ['interactive.primary.disabled' => '#FAFAF8'],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertSame(
            [RejectionReason::InsufficientStateSeparation->value],
            self::reasons(...$result->rejections()),
        );
        self::assertStringContainsString('not a WCAG contrast gate', $result->rejections()[0]->detail);
    }

    public function testEnabledPrimaryChangeCannotCollapseOntoTheDisabledFill(): void
    {
        $result = $this->validator->validateOverlay(
            ['interactive.primary.default' => '#EAC1BA'],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertContains(
            RejectionReason::InsufficientStateSeparation->value,
            self::reasons(...$result->rejections()),
        );
    }

    public function testDisabledStateStillRejectsMalformedValues(): void
    {
        $result = $this->validator->validateOverlay(
            ['interactive.primary.disabled' => 'rgba(0,0,0,0)'],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertSame(
            [RejectionReason::MalformedValue->value],
            self::reasons(...$result->rejections()),
        );
    }

    public function testMissingReferenceTokenIsReportedRatherThanAssumed(): void
    {
        $baseWithoutBackground = new TokenSet(
            ThemeVariant::Light,
            new DesignToken(TokenKey::LinkDefault, new ColorValue('#2C5F94')),
        );

        $result = $this->validator->validateOverlay(
            ['link.default' => '#1E4574'],
            $baseWithoutBackground,
        );

        self::assertFalse($result->isValid());
        self::assertSame(
            [RejectionReason::MissingReferenceToken->value],
            self::reasons(...$result->rejections()),
        );
    }

    // --------------------------------------------------------------- all or nothing

    public function testOneBadEntryRefusesTheWholeOverlay(): void
    {
        $result = $this->validator->validateOverlay(
            [
                'link.default' => '#1E4574',
                'semantic.critical.text' => '#000000',
            ],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertNull($result->resolved(), 'A partially applied overlay was never measured.');
        self::assertSame([RejectionReason::NotOverridable->value], self::reasons(...$result->rejections()));
    }

    public function testMultipleDistinctFailuresAreAllReported(): void
    {
        $result = $this->validator->validateOverlay(
            [
                '--injected' => '#000000',
                'semantic.info.bg' => '#000000',
                'link.hover' => '</style><script>alert(1)</script>',
            ],
            $this->lightBase,
        );

        self::assertFalse($result->isValid());
        self::assertSame(
            [
                RejectionReason::UnknownKey->value,
                RejectionReason::NotOverridable->value,
                RejectionReason::MalformedValue->value,
            ],
            self::reasons(...$result->rejections()),
        );
    }

    public function testEveryOverridableKeyRoundTripsThroughAnAcceptedOverlay(): void
    {
        // A conservative, high-contrast palette: proves the accept path is reachable for
        // the whole Tier 2 surface, not just for one key.
        $overlay = [
            'interactive.primary.default' => '#000000',
            'interactive.primary.hover' => '#111111',
            'interactive.primary.active' => '#222222',
            'interactive.primary.disabled' => '#C0C0C0',
            'interactive.primary.textOn' => '#FFFFFF',
            'interactive.secondary.default' => '#0B1B4D',
            'interactive.secondary.hover' => '#0E2461',
            'interactive.secondary.textOn' => '#FFFFFF',
            'interactive.focusRing' => '#1E4574',
            'link.default' => '#1E4574',
            'link.hover' => '#0B1B4D',
        ];

        self::assertSame(
            array_keys($overlay),
            array_map(static fn (TokenKey $key): string => $key->value, TokenKey::tenantOverridableKeys()),
            'The overlay must exercise exactly the overridable surface.',
        );

        $result = $this->validator->validateOverlay($overlay, $this->lightBase);

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
        foreach ($overlay as $key => $value) {
            $tokenKey = TokenKey::from($key);
            self::assertSame($value, $result->resolved()?->valueOf($tokenKey)?->value);
        }
    }
}
