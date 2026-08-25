<?php

/**
 * CssVariableRenderer can emit nothing except `--key: #RRGGBB;` lines.
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
use OpenEMR\Modules\SkyEagleBranding\Token\CssVariableRenderer;
use OpenEMR\Modules\SkyEagleBranding\Token\DesignToken;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenKey;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSet;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSetParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/token_autoloader.php';

final class CssVariableRendererTest extends TestCase
{
    /** The one shape any rendered line is allowed to take. */
    private const LINE_PATTERN = '/^--[a-z0-9-]+: #[0-9A-F]{6};$/';

    private CssVariableRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new CssVariableRenderer();
    }

    private function assertEveryLineIsSafe(string $css): void
    {
        self::assertNotSame('', $css);
        foreach (explode("\n", $css) as $line) {
            self::assertMatchesRegularExpression(self::LINE_PATTERN, $line);
        }
    }

    public function testRendersTheShippedLightPaletteSafely(): void
    {
        $sets = (new TokenSetParser())->parseDocument(TokenDocumentFixture::json());

        $this->assertEveryLineIsSafe($this->renderer->render($sets['light']));
        $this->assertEveryLineIsSafe($this->renderer->render($sets['dark']));
    }

    /**
     * Exhaustive over the allowlist: every key, rendered, is a safe declaration.
     *
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

    #[DataProvider('everyKeyProvider')]
    public function testEveryKeyRendersAsASingleSafeDeclaration(TokenKey $key): void
    {
        $set = new TokenSet(
            ThemeVariant::Light,
            new DesignToken($key, new ColorValue('#0a1B2c')),
        );

        $declarations = $this->renderer->renderDeclarations($set);

        self::assertCount(1, $declarations);
        self::assertMatchesRegularExpression(self::LINE_PATTERN, $declarations[0]);
        self::assertSame($key->cssVariableName() . ': #0A1B2C;', $declarations[0]);
    }

    /**
     * The full colour space, in case any value could slip past normalisation.
     *
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function colourProvider(): array
    {
        return [
            'black'          => ['#000000', '#000000'],
            'white lower'    => ['#ffffff', '#FFFFFF'],
            'mixed case'     => ['#aAbBcC', '#AABBCC'],
            'digits'         => ['#012345', '#012345'],
            'high letters'   => ['#deadbe', '#DEADBE'],
            'thiqa coral'    => ['#FF6F5E', '#FF6F5E'],
        ];
    }

    #[DataProvider('colourProvider')]
    public function testValuesAreAlwaysUppercasedInOutput(string $input, string $expected): void
    {
        $set = new TokenSet(
            ThemeVariant::Dark,
            new DesignToken(TokenKey::LinkDefault, new ColorValue($input)),
        );

        self::assertSame('--link-default: ' . $expected . ';', $this->renderer->render($set));
        $this->assertEveryLineIsSafe($this->renderer->render($set));
    }

    public function testOutputContainsNoSelectorBracesOrAtRules(): void
    {
        $sets = (new TokenSetParser())->parseDocument(TokenDocumentFixture::json());
        $css = $this->renderer->render($sets['dark']);

        // No braces means no declaration context to escape from.
        self::assertStringNotContainsString('{', $css);
        self::assertStringNotContainsString('}', $css);
        self::assertStringNotContainsString('@', $css);
        self::assertStringNotContainsString('/*', $css);
        self::assertStringNotContainsString('url(', $css);
        self::assertStringNotContainsString('<', $css);
        self::assertStringNotContainsString("\r", $css);
        self::assertStringNotContainsString("\0", $css);
    }

    public function testEmptySetRendersToNothing(): void
    {
        $set = new TokenSet(ThemeVariant::Light);

        self::assertSame([], $this->renderer->renderDeclarations($set));
        self::assertSame('', $this->renderer->render($set));
    }

    public function testOutputIsOrderedAndDeterministic(): void
    {
        $set = new TokenSet(
            ThemeVariant::Light,
            new DesignToken(TokenKey::LinkHover, new ColorValue('#1E4574')),
            new DesignToken(TokenKey::BrandNavy, new ColorValue('#0B1B4D')),
        );

        self::assertSame(
            "--brand-navy: #0B1B4D;\n--link-hover: #1E4574;",
            $this->renderer->render($set),
        );
        self::assertSame($this->renderer->render($set), $this->renderer->render($set));
    }

    public function testOverlaidTenantValuesAreStillConstrainedToTheSameShape(): void
    {
        $base = (new TokenSetParser())->parseDocument(TokenDocumentFixture::json())['light'];
        $overlaid = $base->with(
            new DesignToken(TokenKey::InteractivePrimaryDefault, new ColorValue('#000000')),
            new DesignToken(TokenKey::LinkDefault, new ColorValue('#1e4574')),
        );

        $css = $this->renderer->render($overlaid);

        $this->assertEveryLineIsSafe($css);
        self::assertStringContainsString('--interactive-primary-default: #000000;', $css);
        self::assertStringContainsString('--link-default: #1E4574;', $css);
        self::assertSame(count($base->all()), substr_count($css, "\n") + 1);
    }

    public function testLineCountMatchesTokenCount(): void
    {
        $sets = (new TokenSetParser())->parseDocument(TokenDocumentFixture::json());

        foreach ($sets as $set) {
            $declarations = $this->renderer->renderDeclarations($set);
            self::assertCount(count($set->all()), $declarations);
            self::assertCount(count($set), $declarations);
        }
    }
}
