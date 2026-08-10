<?php

/**
 * The single runtime implementation of the branding front door.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Service;

use OpenEMR\Modules\ThiqaBranding\Asset\BrandAsset;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandAssetResolver;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfig;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\ThiqaBranding\Config\TokenOverlay;
use OpenEMR\Modules\ThiqaBranding\Theme\SmartStyleContract;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeResolver;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use OpenEMR\Modules\ThiqaBranding\Token\ColorValue;
use OpenEMR\Modules\ThiqaBranding\Token\DesignToken;
use OpenEMR\Modules\ThiqaBranding\Token\TokenKey;
use OpenEMR\Modules\ThiqaBranding\Token\TokenParseException;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSet;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSetParser;

/**
 * Plane 3 — runtime resolution (plan §4.2, WP-2.5).
 *
 * Everything the rest of the codebase needs from branding is answered here, from data
 * that is already in memory: the per-request BrandingConfig (one globals read, already
 * performed by OpenEMR) and a build artefact on local disk. There is deliberately no
 * HTTP client, no Control Plane call and no database query in this class or in anything
 * it collaborates with — locked Q76 / constraint C5, enforced by the
 * ForbiddenBrandingHttpClientRule guardrail.
 *
 * Two tiers compose into the rendered palette:
 *
 *  - Tier 1 is the immutable product palette shipped in brand/tokens/thiqa-tokens.json.
 *    It is read from disk at most once per request and then memoised, including the
 *    failure case, so a broken artefact costs one stat call rather than one per lookup.
 *  - Tier 2 is the tenant overlay already parsed and shape-validated into TokenOverlay
 *    by BrandingConfigFactory. It is normally EMPTY, and that is the fast path: an empty
 *    overlay returns the Tier 1 set untouched and makes tokenStylesheetUrl() null, so no
 *    extra stylesheet request is made and no <link> is emitted.
 */
final class BrandingService implements BrandingServiceInterface
{
    /** Location of the Tier 1 token document, relative to the application root. */
    public const TOKEN_DOCUMENT_RELATIVE_PATH = 'brand/tokens/thiqa-tokens.json';

    /** Location of the Tier 2 stylesheet endpoint, relative to the web root (plan §3.8.1). */
    public const TOKEN_STYLESHEET_RELATIVE_PATH =
        'interface/modules/custom_modules/oe-module-thiqa-branding/public/branding-tokens.php';

    /** The branding layer's cache identifier on the Tier 2 stylesheet URL. */
    private const REVISION_PARAMETER = 'rev';

    /**
     * Tier 1, keyed by ThemeVariant::$name. Null until the document has been read.
     *
     * @var array<string, TokenSet>|null
     */
    private ?array $tierOne = null;

    /**
     * Tier 1 with Tier 2 applied, keyed by ThemeVariant::$name.
     *
     * @var array<string, TokenSet>
     */
    private array $effective = [];

    private ?BrandingRevision $revision = null;

    /**
     * @param string $tokenDocumentPath     absolute filesystem path to the Tier 1 document
     * @param string $tokenStylesheetBaseUrl web path of the Tier 2 endpoint, without a query
     */
    public function __construct(
        private readonly BrandingConfigFactory $configFactory,
        private readonly BrandAssetResolver $assetResolver,
        private readonly ThemeResolver $themeResolver,
        private readonly TokenSetParser $tokenParser,
        private readonly string $tokenDocumentPath,
        private readonly string $tokenStylesheetBaseUrl,
    ) {
    }

    public function config(): BrandingConfig
    {
        // The factory memoises; this stays a plain delegation so there is exactly one
        // parsing boundary rather than two competing caches.
        return $this->configFactory->create();
    }

    public function productName(bool $arabic = false): string
    {
        $config = $this->config();

        if ($arabic && $config->productNameArabic !== '') {
            return $config->productNameArabic;
        }

        return $config->productName;
    }

    /**
     * The login tagline, or null when it is suppressed or unset.
     *
     * The $arabic argument is accepted for symmetry with productName() but currently
     * selects nothing: the BRAND inventory defines a single tagline global, so there is
     * no Arabic variant to choose between. Returning the configured tagline for both
     * languages is the honest behaviour; inventing a transliteration here would put an
     * untranslated string in front of Arabic users under the pretence of localisation.
     */
    public function tagline(bool $arabic = false): ?string
    {
        $config = $this->config();

        if (!$config->showTaglineOnLogin || $config->loginTagline === '') {
            return null;
        }

        return $config->loginTagline;
    }

    public function themeVariant(): ThemeVariant
    {
        return $this->themeResolver->resolve($this->config());
    }

    public function isRtl(): bool
    {
        return $this->themeResolver->isRtl($this->config());
    }

    public function tokens(ThemeVariant $variant): TokenSet
    {
        return $this->effective[$variant->name] ??= $this->compose($variant);
    }

    public function logo(LogoSlot $slot): BrandAsset
    {
        return $this->assetResolver->resolve($slot, $this->config());
    }

    public function revision(): BrandingRevision
    {
        return $this->revision ??= new BrandingRevision($this->config()->revision);
    }

    public function smartStyleTokens(ThemeVariant $variant): SmartStyleContract
    {
        return SmartStyleContract::fromTokens(
            $variant,
            $this->tokens($variant),
            $this->logo(LogoSlot::CoreLoginPrimary)->url(),
        );
    }

    public function tokenStylesheetUrl(): ?string
    {
        // The common case, and the reason it is checked first: with no tenant overlay the
        // product renders entirely from the shared immutable bundle, so the caller emits
        // no <link> and the browser makes no extra request (plan §3.8.1).
        if (!$this->config()->hasTenantOverlay()) {
            return null;
        }

        $separator = str_contains($this->tokenStylesheetBaseUrl, '?') ? '&' : '?';

        return $this->tokenStylesheetBaseUrl
            . $separator
            . self::REVISION_PARAMETER
            . '='
            . $this->revision()->cacheKey();
    }

    /**
     * Apply the tenant overlay for one variant over the product palette.
     *
     * The overlay was already shape-validated by TokenOverlay and allowlist/contrast
     * validated by TokenValidator before it was written to globals. It is filtered again
     * here — unknown key, non-overridable key or unparseable colour is dropped — not
     * because the earlier gates are distrusted, but because this is the last point before
     * a value reaches a rendered page, and a defence that costs a hash lookup per entry is
     * worth having in depth (plan §3.9).
     */
    private function compose(ThemeVariant $variant): TokenSet
    {
        $base = $this->tierOne()[$variant->name] ?? new TokenSet($variant);
        $overlay = $this->overlayFor($variant);

        if ($overlay->isEmpty()) {
            return $base;
        }

        $tokens = [];
        foreach ($overlay->all() as $rawKey => $rawValue) {
            $key = TokenKey::tryFrom($rawKey);
            if (!$key instanceof TokenKey || !$key->isTenantOverridable()) {
                continue;
            }

            $value = ColorValue::tryFrom($rawValue);
            if (!$value instanceof ColorValue) {
                continue;
            }

            $tokens[] = new DesignToken($key, $value);
        }

        return $tokens === [] ? $base : $base->with(...$tokens);
    }

    private function overlayFor(ThemeVariant $variant): TokenOverlay
    {
        $config = $this->config();

        return match ($variant) {
            ThemeVariant::Light => $config->lightOverlay,
            ThemeVariant::Dark => $config->darkOverlay,
        };
    }

    /**
     * @return array<string, TokenSet>
     */
    private function tierOne(): array
    {
        return $this->tierOne ??= $this->loadTierOne();
    }

    /**
     * Read and parse the shipped token document, once.
     *
     * A missing or malformed artefact is a packaging fault, not a tenant fault, and it
     * must not take the login page down: the variant degrades to an empty TokenSet, which
     * emits no custom properties and leaves the compiled stylesheets in charge. Because
     * the result is memoised either way, the failure is paid for at most once per request.
     *
     * @return array<string, TokenSet>
     */
    private function loadTierOne(): array
    {
        if (!is_file($this->tokenDocumentPath) || !is_readable($this->tokenDocumentPath)) {
            return $this->emptyPalette();
        }

        $json = file_get_contents($this->tokenDocumentPath);
        if ($json === false) {
            return $this->emptyPalette();
        }

        try {
            $document = $this->tokenParser->parseDocument($json);
        } catch (TokenParseException) {
            return $this->emptyPalette();
        }

        return [
            ThemeVariant::Light->name => $document['light'],
            ThemeVariant::Dark->name => $document['dark'],
        ];
    }

    /**
     * @return array<string, TokenSet>
     */
    private function emptyPalette(): array
    {
        return [
            ThemeVariant::Light->name => new TokenSet(ThemeVariant::Light),
            ThemeVariant::Dark->name => new TokenSet(ThemeVariant::Dark),
        ];
    }
}
