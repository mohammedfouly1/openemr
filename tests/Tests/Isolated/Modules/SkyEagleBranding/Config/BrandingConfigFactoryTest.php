<?php

/**
 * Isolated tests for the branding configuration parsing boundary.
 *
 * No database is involved: OEGlobalsBag is constructed directly from an array, which is
 * exactly the surface the factory consumes. Two properties are proven here. First, that
 * a populated bag produces correctly typed values. Second, and more important, that an
 * empty or blank bag produces the documented Thiqa defaults and never a value naming the
 * upstream project -- a blank main_menu_logo_title or user_manual_link makes upstream
 * generate open-emr.org content, so the defaults are a functional requirement rather
 * than a cosmetic one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config;

use DateTimeImmutable;
use DateTimeInterface;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingConfig;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

final class BrandingConfigFactoryTest extends TestCase
{
    use ModuleAutoloadTrait;

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    public function testEmptyGlobalsProduceTheDocumentedDefaults(): void
    {
        $config = $this->configFrom([]);

        $this->assertSame('SkyEagle', $config->productName);
        $this->assertSame('سكاي إيجل', $config->productNameArabic);
        $this->assertSame('Clinical confidence, connected care.', $config->loginTagline);
        $this->assertTrue($config->showTaglineOnLogin);
        $this->assertSame('https://skyeagle.uk/', $config->mainMenuLogoLink);
        $this->assertSame('SkyEagle Health Information System', $config->mainMenuLogoTitle);
        $this->assertSame('https://skyeagle.uk/en/contact', $config->onlineSupportLink);
        $this->assertSame('https://skyeagle.uk/en/resources', $config->userManualLink);
        $this->assertSame('', $config->supportPhoneNumber);
        $this->assertSame('style_light.css', $config->cssHeader);
        $this->assertSame('style_light.css', $config->portalCssHeader);
        $this->assertSame('tabs_style_full.css', $config->themeTabsLayout);
        $this->assertSame(30, $config->portalPrimaryMenuLogoHeight);
        $this->assertSame('practice_logo.gif', $config->statementLogo);
        $this->assertSame(0, $config->revision);
        $this->assertNull($config->materialisedAt);
        $this->assertTrue($config->lightOverlay->isEmpty());
        $this->assertTrue($config->darkOverlay->isEmpty());
        $this->assertFalse($config->isMaterialised());
        $this->assertFalse($config->hasTenantOverlay());
        $this->assertFalse($config->hasTenantLockup());
    }

    /**
     * The load-bearing assertion: with nothing configured at all, no string the factory
     * hands downstream may name the upstream project.
     *
     * @param array<string, string> $globals
     */
    #[DataProvider('emptyAndBlankBagProvider')]
    public function testNoResolvedValueEverLeaksUpstreamIdentity(array $globals): void
    {
        $config = $this->configFrom($globals);

        foreach ((new ReflectionObject($config))->getProperties() as $property) {
            $value = $property->getValue($config);
            if (!is_string($value)) {
                continue;
            }

            foreach (['open-emr', 'openemr', 'open emr'] as $forbidden) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $forbidden,
                    $value,
                    "BrandingConfig::\${$property->getName()} resolved to an upstream identity value."
                );
            }
        }
    }

    public function testBlankValuesResolveToDefaultsRatherThanEmptyStrings(): void
    {
        $config = $this->configFrom([
            BrandingGlobalKey::MainMenuLogoTitle->value => '   ',
            BrandingGlobalKey::UserManualLink->value => '',
            BrandingGlobalKey::OpenemrName->value => "\t\n",
            BrandingGlobalKey::CssHeader->value => '',
        ]);

        $this->assertSame('SkyEagle Health Information System', $config->mainMenuLogoTitle);
        $this->assertSame('https://skyeagle.uk/en/resources', $config->userManualLink);
        $this->assertSame('SkyEagle', $config->productName);
        $this->assertSame('style_light.css', $config->cssHeader);
    }

    public function testConfiguredValuesAreParsedIntoTheirDeclaredTypes(): void
    {
        $config = $this->configFrom([
            BrandingGlobalKey::OpenemrName->value => 'Thiqa Enterprise',
            BrandingGlobalKey::TenantDisplayName->value => 'King Faisal Specialist Hospital',
            BrandingGlobalKey::TenantDisplayNameArabic->value => 'مستشفى الملك فيصل التخصصي',
            BrandingGlobalKey::ShowTaglineOnLogin->value => '0',
            BrandingGlobalKey::DisplayReviewLink->value => '1',
            BrandingGlobalKey::PortalPrimaryMenuLogoHeight->value => '48',
            BrandingGlobalKey::Revision->value => '17',
            BrandingGlobalKey::MaterialisedAt->value => '2026-08-09T10:15:30+00:00',
            BrandingGlobalKey::TokensLight->value => '{"link.default":"#2C5F94"}',
            BrandingGlobalKey::TokensDark->value => '{"link.default":"#8FB8E8","link.hover":"#B7D2F2"}',
        ]);

        $this->assertSame('Thiqa Enterprise', $config->productName);
        $this->assertSame('King Faisal Specialist Hospital', $config->tenantDisplayName);
        $this->assertTrue($config->hasTenantLockup());
        $this->assertFalse($config->showTaglineOnLogin);
        $this->assertTrue($config->displayReviewLink);
        $this->assertSame(48, $config->portalPrimaryMenuLogoHeight);
        $this->assertSame(17, $config->revision);
        $this->assertInstanceOf(DateTimeImmutable::class, $config->materialisedAt);
        $this->assertSame('2026-08-09T10:15:30+00:00', $config->materialisedAt->format(DateTimeInterface::ATOM));
        $this->assertSame(1, $config->lightOverlay->count());
        $this->assertSame('#8FB8E8', $config->darkOverlay->get('link.default'));
        $this->assertTrue($config->hasTenantOverlay());
        $this->assertTrue($config->isMaterialised());
    }

    #[DataProvider('flagProvider')]
    public function testFlagsAcceptTheUsualGlobalsSpellings(string $raw, bool $expected): void
    {
        $config = $this->configFrom([BrandingGlobalKey::ShowPrimaryLogo->value => $raw]);

        $this->assertSame($expected, $config->showPrimaryLogo);
    }

    #[DataProvider('portalLogoHeightProvider')]
    public function testMeasurementsRejectNonPositiveValues(string $raw, int $expected): void
    {
        $config = $this->configFrom([BrandingGlobalKey::PortalPrimaryMenuLogoHeight->value => $raw]);

        $this->assertSame($expected, $config->portalPrimaryMenuLogoHeight);
    }

    #[DataProvider('revisionProvider')]
    public function testRevisionKeepsZeroButRejectsNegatives(string $raw, int $expected): void
    {
        $config = $this->configFrom([BrandingGlobalKey::Revision->value => $raw]);

        $this->assertSame($expected, $config->revision);
    }

    #[DataProvider('rejectedTimestampProvider')]
    public function testOnlyStrictIso8601TimestampsParse(string $raw): void
    {
        $config = $this->configFrom([BrandingGlobalKey::MaterialisedAt->value => $raw]);

        $this->assertNull($config->materialisedAt);
        $this->assertFalse($config->isMaterialised());
    }

    public function testMalformedOverlayDegradesToTheSharedPalette(): void
    {
        $config = $this->configFrom([
            BrandingGlobalKey::TokensLight->value => 'body { display: none }',
            BrandingGlobalKey::TokensDark->value => '{"link.default":"<script>alert(1)</script>"}',
        ]);

        $this->assertTrue($config->lightOverlay->isEmpty());
        $this->assertTrue($config->darkOverlay->isEmpty());
        $this->assertFalse($config->hasTenantOverlay());
    }

    public function testGlobalsAreReadExactlyOncePerRequest(): void
    {
        $bag = new OEGlobalsBag([BrandingGlobalKey::OpenemrName->value => 'SkyEagle']);
        $factory = new BrandingConfigFactory($bag);

        $first = $factory->create();
        // add() rather than set(): OEGlobalsBag::set() writes through to $GLOBALS, which
        // would leak out of this test.
        $bag->add([BrandingGlobalKey::OpenemrName->value => 'Changed After Parsing']);
        $second = $factory->create();

        $this->assertSame('Changed After Parsing', $bag->getString(BrandingGlobalKey::OpenemrName->value));
        $this->assertSame($first, $second);
        $this->assertSame('SkyEagle', $second->productName);
    }

    public function testMaterialisationRequiresBothARevisionAndATimestamp(): void
    {
        $revisionOnly = $this->configFrom([BrandingGlobalKey::Revision->value => '4']);
        $timestampOnly = $this->configFrom([
            BrandingGlobalKey::MaterialisedAt->value => '2026-08-09T10:15:30+00:00',
        ]);

        $this->assertFalse($revisionOnly->isMaterialised());
        $this->assertFalse($timestampOnly->isMaterialised());
    }

    /**
     * @param array<string, string> $globals
     */
    private function configFrom(array $globals): BrandingConfig
    {
        return (new BrandingConfigFactory(new OEGlobalsBag($globals)))->create();
    }

    /**
     * @return iterable<string, array{array<string, string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function emptyAndBlankBagProvider(): iterable
    {
        self::registerModuleAutoload();

        yield 'nothing configured' => [[]];

        $blank = [];
        foreach (BrandingGlobalKey::cases() as $key) {
            $blank[$key->value] = '';
        }
        yield 'every key blank' => [$blank];

        $whitespace = [];
        foreach (BrandingGlobalKey::cases() as $key) {
            $whitespace[$key->value] = "  \t ";
        }
        yield 'every key whitespace' => [$whitespace];
    }

    /**
     * @return iterable<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function flagProvider(): iterable
    {
        yield 'one is true' => ['1', true];
        yield 'zero is false' => ['0', false];
        yield 'true is true' => ['true', true];
        yield 'false is false' => ['false', false];
        yield 'off is false' => ['off', false];
        yield 'blank falls back to the default' => ['', true];
        yield 'unreadable falls back to the default' => ['perhaps', true];
    }

    /**
     * @return iterable<string, array{string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function portalLogoHeightProvider(): iterable
    {
        yield 'positive height is kept' => ['48', 48];
        yield 'zero collapses the logo so the default wins' => ['0', 30];
        yield 'negative is nonsense so the default wins' => ['-12', 30];
        yield 'non numeric falls back' => ['tall', 30];
        yield 'blank falls back' => ['', 30];
    }

    /**
     * @return iterable<string, array{string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function revisionProvider(): iterable
    {
        yield 'zero means never materialised' => ['0', 0];
        yield 'positive revision is kept' => ['17', 17];
        yield 'negative is rejected' => ['-1', 0];
        yield 'blank falls back' => ['', 0];
    }

    /**
     * @return iterable<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rejectedTimestampProvider(): iterable
    {
        yield 'blank' => [''];
        yield 'date only' => ['2026-08-09'];
        yield 'sql datetime' => ['2026-08-09 10:15:30'];
        yield 'no timezone' => ['2026-08-09T10:15:30'];
        yield 'rfc 2822' => ['Sun, 09 Aug 2026 10:15:30 +0000'];
        yield 'unix timestamp' => ['1786385730'];
        yield 'not a date' => ['soon'];
    }
}
