<?php

/**
 * Isolated tests for the Administration registration of the layer-owned globals.
 *
 * Two things are proven. First, that the seven saas_branding_* globals reach
 * Administration through GlobalsInitializedEvent alone, so library/globals.inc.php never
 * needs patching. Second, and the reason this file matters, that the four
 * materialiser-owned values are registered as read-only display sections: locked
 * constraint C1 is only true if Administration offers no box into which token JSON --
 * and therefore CSS, JS or HTML -- could be typed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Config\GlobalsRegistrationListener;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use PHPUnit\Framework\TestCase;

final class GlobalsRegistrationListenerTest extends TestCase
{
    use ModuleAutoloadTrait;

    /** Data types that put an editable free-text box on the screen. */
    private const FREE_TEXT_DATA_TYPES = [
        GlobalSetting::DATA_TYPE_TEXT,
        GlobalSetting::DATA_TYPE_PASS,
        GlobalSetting::DATA_TYPE_ENCRYPTED,
    ];

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    public function testEverySaasBrandingGlobalIsRegisteredInItsOwnSection(): void
    {
        $metadata = $this->registerInto([]);

        $this->assertArrayHasKey(GlobalsRegistrationListener::SECTION, $metadata);

        $section = $metadata[GlobalsRegistrationListener::SECTION];
        $this->assertIsArray($section);
        $this->assertSame(
            array_map(static fn (BrandingGlobalKey $key): string => $key->value, BrandingGlobalKey::layerOwned()),
            array_keys($section)
        );
    }

    public function testNoInheritedGlobalIsRegistered(): void
    {
        $section = $this->section($this->registerInto([]));

        foreach (BrandingGlobalKey::inherited() as $key) {
            $this->assertArrayNotHasKey(
                $key->value,
                $section,
                "{$key->value} already exists in library/globals.inc.php and must not be re-registered."
            );
        }
    }

    public function testExistingSectionsAreLeftIntactAndRegistrationIsRepeatable(): void
    {
        $service = new GlobalsService(['Appearance' => ['css_header' => []]], [], []);
        $listener = $this->listener([]);
        $event = new GlobalsInitializedEvent($service);

        $listener->onGlobalsInitialized($event);
        $listener->onGlobalsInitialized($event);

        $metadata = $service->getGlobalsMetadata();
        $this->assertArrayHasKey('Appearance', $metadata);
        $this->assertCount(7, $this->section($metadata));
    }

    public function testOnlyTenantNameFieldsAreEditableText(): void
    {
        $section = $this->section($this->registerInto([]));

        $textFields = [];
        foreach ($section as $key => $field) {
            $this->assertIsArray($field);
            if (in_array($field[1], self::FREE_TEXT_DATA_TYPES, true)) {
                $textFields[] = $key;
            }
        }

        $this->assertSame(
            [
                BrandingGlobalKey::ProductNameArabic->value,
                BrandingGlobalKey::TenantDisplayName->value,
                BrandingGlobalKey::TenantDisplayNameArabic->value,
            ],
            $textFields
        );
    }

    /**
     * Locked constraint C1, asserted at the surface where it could be violated.
     */
    public function testMaterialiserOwnedValuesAreReadOnlyDisplaySections(): void
    {
        $section = $this->section($this->registerInto([]));

        foreach (
            [
                BrandingGlobalKey::Revision,
                BrandingGlobalKey::TokensLight,
                BrandingGlobalKey::TokensDark,
                BrandingGlobalKey::MaterialisedAt,
            ] as $key
        ) {
            $field = $section[$key->value];
            $this->assertIsArray($field);
            $this->assertSame(
                GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION,
                $field[1],
                "{$key->value} must render read-only, never as an input."
            );
            $this->assertArrayHasKey(4, $field, "{$key->value} must carry a render callback.");
            $this->assertIsArray($field[4]);
            $this->assertArrayHasKey(GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK, $field[4]);
            $this->assertIsCallable($field[4][GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK]);
        }
    }

    public function testRegisteredDefaultsCarryTheDocumentedValues(): void
    {
        $section = $this->section($this->registerInto([]));

        $arabicName = $section[BrandingGlobalKey::ProductNameArabic->value];
        $this->assertIsArray($arabicName);
        $this->assertSame('سكاي إيجل', $arabicName[2]);

        $revision = $section[BrandingGlobalKey::Revision->value];
        $this->assertIsArray($revision);
        $this->assertSame('0', $revision[2]);
    }

    public function testUnmaterialisedStateRendersAsPlainProse(): void
    {
        $listener = $this->listener([]);

        $revision = $listener->renderMaterialisedValue(BrandingGlobalKey::Revision->value);
        $timestamp = $listener->renderMaterialisedValue(BrandingGlobalKey::MaterialisedAt->value);
        $overlay = $listener->renderMaterialisedValue(BrandingGlobalKey::TokensLight->value);

        $this->assertStringContainsString('Branding revision', $revision);
        $this->assertStringContainsString('Never materialised', $timestamp);
        $this->assertStringContainsString('shared product palette', $overlay);
    }

    public function testOverlayRendersItsParsedTokenPairs(): void
    {
        $listener = $this->listener([
            BrandingGlobalKey::TokensDark->value => '{"link.default":"#8fb8e8"}',
        ]);

        $rendered = $listener->renderMaterialisedValue(BrandingGlobalKey::TokensDark->value);

        $this->assertStringContainsString('link.default', $rendered);
        $this->assertStringContainsString('#8FB8E8', $rendered);
    }

    public function testAMalformedOverlayNeverReachesTheRenderedPage(): void
    {
        $listener = $this->listener([
            BrandingGlobalKey::TokensLight->value => '{"link.default":"<script>alert(1)</script>"}',
        ]);

        $rendered = $listener->renderMaterialisedValue(BrandingGlobalKey::TokensLight->value);

        $this->assertStringNotContainsString('<script>', $rendered);
        $this->assertStringNotContainsString('alert(1)', $rendered);
        $this->assertStringContainsString('shared product palette', $rendered);
    }

    public function testRenderIgnoresKeysItDoesNotOwn(): void
    {
        $listener = $this->listener([]);

        $this->assertSame('', $listener->renderMaterialisedValue('not_a_branding_global'));
        $this->assertSame('', $listener->renderMaterialisedValue(BrandingGlobalKey::CssHeader->value));
        $this->assertSame('', $listener->renderMaterialisedValue(BrandingGlobalKey::TenantDisplayName->value));
    }

    /**
     * @param array<string, string> $globals
     *
     * @return array<array-key, mixed>
     */
    private function registerInto(array $globals): array
    {
        $service = new GlobalsService([], [], []);
        $this->listener($globals)->onGlobalsInitialized(new GlobalsInitializedEvent($service));

        return $service->getGlobalsMetadata();
    }

    /**
     * @param array<array-key, mixed> $metadata
     *
     * @return array<array-key, mixed>
     */
    private function section(array $metadata): array
    {
        $section = $metadata[GlobalsRegistrationListener::SECTION] ?? null;
        $this->assertIsArray($section);

        return $section;
    }

    /**
     * @param array<string, string> $globals
     */
    private function listener(array $globals): GlobalsRegistrationListener
    {
        return new GlobalsRegistrationListener(new BrandingConfigFactory(new OEGlobalsBag($globals)));
    }
}
