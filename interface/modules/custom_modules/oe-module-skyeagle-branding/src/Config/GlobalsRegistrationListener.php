<?php

/**
 * Registers the layer-owned branding globals with the Administration screen.
 *
 * The seven saas_branding_* globals are declared here, through the published
 * GlobalsInitializedEvent extension point, so library/globals.inc.php is never patched
 * (locked Invariant 4, plan section 3.4.2).
 *
 * Two kinds of field are registered, and the split is what makes locked constraint C1
 * structurally true rather than merely intended:
 *
 *  - The three tenant name strings are ordinary text fields. They are names, never
 *    markup, and an administrator is the right author of them.
 *  - The revision, the two token overlays and the materialisation timestamp are
 *    registered as read-only display sections. No input element is emitted for them at
 *    all, so there is no box anywhere in Administration into which a person could type
 *    CSS, JS or HTML, and the overlays remain machine-produced by the materialiser.
 *
 * Consequence worth stating plainly: because a display section emits no input, saving
 * the Administration form posts nothing for those four keys and OpenEMR's save loop
 * (interface/super/edit_globals.php) will blank their rows. That is safe here and only
 * here -- the Control Plane remains authoritative (C5), the materialiser is the sole
 * writer of those four values and restamps them on the next materialisation, and until
 * then BrandingConfigFactory resolves each blank to its documented default. Trading a
 * blankable cache of materialiser output for the absence of a free-text field is the
 * intended bargain.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Config;

use DateTimeInterface;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Services\Globals\GlobalSetting;

final readonly class GlobalsRegistrationListener
{
    /**
     * Administration tab this layer's settings live under.
     *
     * Plain English on purpose: edit_globals.php passes the section key itself through
     * xlt(), so the key must be the untranslated literal.
     */
    public const SECTION = 'SkyEagle Branding';

    public function __construct(private BrandingConfigFactory $configFactory)
    {
    }

    /**
     * Listener for GlobalsInitializedEvent::EVENT_HANDLE ('globals.initialized').
     *
     * save() is intentionally not called: library/globals.inc.php saves the service
     * itself once every listener has run, and calling it here would only publish a
     * half-built metadata structure early.
     */
    public function onGlobalsInitialized(GlobalsInitializedEvent $event): void
    {
        $service = $event->getGlobalsService();

        if (!array_key_exists(self::SECTION, $service->getGlobalsMetadata())) {
            $service->createSection(self::SECTION);
        }

        foreach (BrandingGlobalKey::layerOwned() as $key) {
            $service->appendToSection(self::SECTION, $key->value, $this->settingFor($key));
        }
    }

    /**
     * Build the Administration field for one layer-owned global.
     */
    public function settingFor(BrandingGlobalKey $key): GlobalSetting
    {
        $definition = $key->definition();

        if ($definition->editableInAdministration) {
            return new GlobalSetting(
                $definition->label,
                GlobalSetting::DATA_TYPE_TEXT,
                $definition->defaultAsGlobalsValue(),
                $definition->description,
            );
        }

        $setting = new GlobalSetting(
            $definition->label,
            GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION,
            $definition->defaultAsGlobalsValue(),
            $definition->description,
        );
        $setting->addFieldOption(
            GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
            $this->renderMaterialisedValue(...),
        );

        return $setting;
    }

    /**
     * Render the current value of a materialiser-owned global as read-only markup.
     *
     * Called by interface/super/templates/field_html_display_section.php with the field
     * id and its metadata row; only the id is needed. The value comes from the parsed
     * BrandingConfig rather than the raw global, so what is displayed is exactly what the
     * runtime will use, and an overlay is shown as its parsed token pairs -- never as a
     * blob that could carry markup.
     *
     * @param array<array-key, mixed> $fieldDefinition Metadata row, unused; present to
     *                                                 match the caller's signature.
     */
    public function renderMaterialisedValue(string $fieldId, array $fieldDefinition = []): string
    {
        unset($fieldDefinition);

        $key = BrandingGlobalKey::tryFrom($fieldId);
        if ($key === null || !$key->isLayerOwned() || $key->isEditableInAdministration()) {
            return '';
        }

        // Past that guard the key is one of exactly four: the revision, the two overlays
        // and the timestamp. Their value types are distinct, so dispatching on the value
        // type is both exhaustive and unambiguous -- Integer can only be the revision.
        $definition = $key->definition();
        $config = $this->configFactory->create();
        $body = match ($definition->valueType) {
            BrandingValueType::Integer => $this->renderScalar((string) $config->revision),
            BrandingValueType::TokenJson => $this->renderOverlay(
                $key === BrandingGlobalKey::TokensDark ? $config->darkOverlay : $config->lightOverlay
            ),
            BrandingValueType::Timestamp => $this->renderTimestamp($config),
            BrandingValueType::Text, BrandingValueType::Flag => '',
        };

        if ($body === '') {
            return '';
        }

        return '<div class="row form-group">'
            . '<div class="col-sm-4">' . $this->escape($definition->label) . '</div>'
            . '<div class="col-sm-8">' . $body
            . '<div class="text-muted small">' . $this->escape($definition->description) . '</div>'
            . '</div></div>';
    }

    private function renderScalar(string $value): string
    {
        return '<span class="font-weight-bold">' . $this->escape($value) . '</span>';
    }

    private function renderTimestamp(BrandingConfig $config): string
    {
        $materialisedAt = $config->materialisedAt;
        if ($materialisedAt === null) {
            return $this->renderScalar('Never materialised');
        }

        return $this->renderScalar($materialisedAt->format(DateTimeInterface::ATOM));
    }

    /**
     * Render an overlay as its parsed token pairs.
     *
     * Each value has already been proven to match ^#[0-9A-Fa-f]{6}$ by TokenOverlay, so
     * the swatch below cannot become an injection vector; escaping is applied anyway
     * because defence in depth costs nothing here.
     */
    private function renderOverlay(TokenOverlay $overlay): string
    {
        if ($overlay->isEmpty()) {
            return $this->renderScalar('No tenant overlay: the shared product palette applies unchanged.');
        }

        $rows = '';
        foreach ($overlay->all() as $token => $colour) {
            $rows .= '<tr><td><code>' . $this->escape($token) . '</code></td>'
                . '<td><code>' . $this->escape($colour) . '</code></td></tr>';
        }

        return '<table class="table table-sm mb-1"><tbody>' . $rows . '</tbody></table>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
