<?php

/**
 * Isolated tests for the Thiqa branding WCAG 2.2 contrast calculator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Accessibility;

use Composer\Autoload\ClassLoader;
use InvalidArgumentException;
use OpenEMR\Modules\SkyEagleBranding\Accessibility\ContrastCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The custom_modules/oe-module-skyeagle-branding module is not registered in the
 * root composer.json autoload map. At runtime the module manager registers its
 * PSR-4 prefix when the module is enabled in the database. The isolated test
 * suite has no database, so we register the prefix ourselves before
 * referencing any module class.
 *
 * The core assertion is that every pair recorded in
 * brand/qa/wcag-contrast-results.json reproduces exactly: those figures are
 * the published accessibility evidence for the palette, so a drift here is a
 * drift in the brand contract, not merely a failing test.
 */
final class ContrastCalculatorTest extends TestCase
{
    private const RESULTS_FILE = '/brand/qa/wcag-contrast-results.json';

    private const MODULE_SOURCE = '/interface/modules/custom_modules/oe-module-skyeagle-branding/src/';

    /**
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    public static function setUpBeforeClass(): void
    {
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }

        $loader->addPsr4(
            'OpenEMR\\Modules\\SkyEagleBranding\\',
            self::repositoryRoot() . self::MODULE_SOURCE
        );
    }

    #[DataProvider('recordedResultProvider')]
    public function testRatioReproducesRecordedResult(string $foreground, string $background, float $expected): void
    {
        $this->assertSame(
            $expected,
            (new ContrastCalculator())->ratio($foreground, $background),
            "Recorded contrast for {$foreground} on {$background} must reproduce exactly."
        );
    }

    #[DataProvider('anchorProvider')]
    public function testRatioMatchesKnownAnchor(string $foreground, string $background, float $expected): void
    {
        $this->assertSame($expected, (new ContrastCalculator())->ratio($foreground, $background));
    }

    public function testRatioIsSymmetric(): void
    {
        $calculator = new ContrastCalculator();

        $this->assertSame(
            $calculator->ratio('#0B1B4D', '#FAFAF8'),
            $calculator->ratio('#FAFAF8', '#0B1B4D')
        );
    }

    #[DataProvider('acceptedFormatProvider')]
    public function testHexParsingAcceptsOptionalHashAndEitherCase(string $foreground, string $background): void
    {
        $this->assertSame(6.34, (new ContrastCalculator())->ratio($foreground, $background));
    }

    #[DataProvider('rejectedColourProvider')]
    public function testInvalidColourThrows(string $colour): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ContrastCalculator())->ratio($colour, '#FFFFFF');
    }

    public function testInvalidBackgroundThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ContrastCalculator())->ratio('#FFFFFF', 'rebeccapurple');
    }

    public function testMeetsNormalTextAppliesTheFourPointFiveThreshold(): void
    {
        $calculator = new ContrastCalculator();

        // 6.34:1 clears SC 1.4.3; the 4.04:1 focus ring does not.
        $this->assertTrue($calculator->meetsNormalText('#2C5F94', '#FAFAF8'));
        $this->assertFalse($calculator->meetsNormalText('#3E7FBD', '#FAFAF8'));
    }

    public function testMeetsUiComponentAppliesTheThreeToOneThreshold(): void
    {
        $calculator = new ContrastCalculator();

        // 4.04:1 clears SC 1.4.11; the 1.24:1 default border does not.
        $this->assertTrue($calculator->meetsUiComponent('#3E7FBD', '#FAFAF8'));
        $this->assertFalse($calculator->meetsUiComponent('#E4E2DC', '#FAFAF8'));
    }

    #[DataProvider('recordedResultProvider')]
    public function testRatioStaysWithinTheWcagRange(string $foreground, string $background, float $expected): void
    {
        $ratio = (new ContrastCalculator())->ratio($foreground, $background);

        $this->assertGreaterThanOrEqual(1.0, $ratio);
        $this->assertLessThanOrEqual(21.0, $ratio);
        $this->assertSame($expected, $ratio);
    }

    /**
     * Every pair recorded in brand/qa/wcag-contrast-results.json.
     *
     * @return iterable<string, array{string, string, float}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function recordedResultProvider(): iterable
    {
        $path = self::repositoryRoot() . self::RESULTS_FILE;
        $raw = file_get_contents($path);
        if ($raw === false) {
            self::fail("Unable to read recorded contrast results at {$path}.");
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $results = is_array($decoded) ? ($decoded['results'] ?? null) : null;
        if (!is_array($results) || $results === []) {
            self::fail("Recorded contrast results at {$path} have no 'results' list.");
        }

        foreach ($results as $index => $entry) {
            self::assertIsArray($entry, "Recorded result #{$index} must be an object.");

            $theme = self::readString($entry, 'theme', $index);
            $label = self::readString($entry, 'label', $index);
            $foreground = self::readString($entry, 'fg', $index);
            $background = self::readString($entry, 'bg', $index);
            $ratio = self::readFloat($entry, 'ratio', $index);

            yield "{$theme}: {$label} ({$foreground} on {$background})" => [$foreground, $background, $ratio];
        }
    }

    /**
     * Anchors that must hold independently of the recorded file.
     *
     * @return iterable<string, array{string, string, float}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function anchorProvider(): iterable
    {
        yield 'white on white is the floor' => ['#FFFFFF', '#FFFFFF', 1.0];
        yield 'black on white is the ceiling' => ['#000000', '#FFFFFF', 21.0];
        yield 'white on black is the ceiling' => ['#FFFFFF', '#000000', 21.0];
        yield 'brand ink on canvas' => ['#0B1B4D', '#FAFAF8', 15.72];
        yield 'link default on canvas' => ['#2C5F94', '#FAFAF8', 6.34];
        yield 'link default on surface' => ['#2C5F94', '#FFFFFF', 6.62];
        yield 'link hover on canvas' => ['#1E4574', '#FAFAF8', 9.31];
        yield 'focus ring on canvas' => ['#3E7FBD', '#FAFAF8', 4.04];
        yield 'secondary text on canvas' => ['#4B5266', '#FAFAF8', 7.45];
        yield 'dark border strong on canvas' => ['#AEB5C4', '#0B1220', 9.10];
        yield 'dark body text on canvas' => ['#F5F6F8', '#0B1220', 17.31];
    }

    /**
     * Hex spellings that must all parse to the same colour pair.
     *
     * @return iterable<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function acceptedFormatProvider(): iterable
    {
        yield 'hashed upper case' => ['#2C5F94', '#FAFAF8'];
        yield 'hashed lower case' => ['#2c5f94', '#fafaf8'];
        yield 'hashed mixed case' => ['#2c5F94', '#FaFaF8'];
        yield 'bare upper case' => ['2C5F94', 'FAFAF8'];
        yield 'bare lower case' => ['2c5f94', 'fafaf8'];
    }

    /**
     * Colour strings the calculator must reject outright.
     *
     * @return iterable<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rejectedColourProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'three digit shorthand' => ['#FFF'];
        yield 'four digit' => ['#FFFF'];
        yield 'eight digit with alpha' => ['#FFFFFFFF'];
        yield 'seven digits' => ['#FFFFFFF'];
        yield 'non hex letters' => ['#GGGGGG'];
        yield 'css colour name' => ['white'];
        yield 'rgb function' => ['rgb(255, 255, 255)'];
        yield 'double hash' => ['##FFFFFF'];
        yield 'trailing whitespace' => ['#FFFFFF '];
        yield 'leading whitespace' => [' #FFFFFF'];
        yield 'inner whitespace' => ['#FF FFFF'];
        yield 'hash only' => ['#'];
        yield 'newline injection' => ["#FFFFFF\n"];
    }

    /**
     * Absolute path to the repository root.
     */
    private static function repositoryRoot(): string
    {
        return dirname(__DIR__, 6);
    }

    /**
     * @param array<array-key, mixed> $entry
     */
    private static function readString(array $entry, string $key, int|string $index): string
    {
        $value = $entry[$key] ?? null;
        self::assertIsString($value, "Recorded result #{$index} needs a string '{$key}'.");

        return $value;
    }

    /**
     * @param array<array-key, mixed> $entry
     */
    private static function readFloat(array $entry, string $key, int|string $index): float
    {
        $value = $entry[$key] ?? null;
        if (is_int($value)) {
            return (float) $value;
        }

        self::assertIsFloat($value, "Recorded result #{$index} needs a numeric '{$key}'.");

        return $value;
    }
}
