<?php

/**
 * A configurable BrandingServiceInterface double for the listener tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Listener;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandAsset;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfig;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingServiceInterface;
use OpenEMR\Modules\ThiqaBranding\Theme\SmartStyleContract;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSet;

/**
 * Plain mutable properties rather than a constructor with nine optional arguments: a
 * listener test reads better when the one value under test is named at the point it is
 * set, and every other value stays at the unconfigured default.
 *
 * The defaults ARE the unconfigured tenant, which is what most of these tests assert
 * against: no token stylesheet, no logo assets, no product name, no tagline, Saudi Light,
 * left-to-right, revision 0.
 *
 * `$failure`, when set, is thrown by every accessor. It is typed \Exception rather than
 * \Throwable because the listeners catch \Exception -- see the listener docblocks for the
 * ForbiddenCatchTypeRule guardrail that forces that, and what it means for \Error.
 */
final class StubBrandingService implements BrandingServiceInterface
{
    /** Return value of tokenStylesheetUrl(); null is the unconfigured default. */
    public ?string $tokenStylesheetUrl = null;

    public ThemeVariant $themeVariant = ThemeVariant::Light;

    public bool $rtl = false;

    public string $productName = '';

    public string $productNameArabic = '';

    public ?string $tagline = null;

    public int $revision = 0;

    /**
     * Accessible names per slot: slot value => [english, arabic]. A slot that is absent
     * resolves to BrandAsset::missing(), which is what an unconfigured tenant produces.
     *
     * @var array<string, array{string, string}>
     */
    public array $logoAltText = [];

    public ?\Exception $failure = null;

    /** How many times logo() was asked for a slot; proves no re-entrant lookup. */
    public int $logoCallCount = 0;

    private ?BrandingConfig $config = null;

    public function config(): BrandingConfig
    {
        $this->guard();

        // Listeners never read the config directly -- they go through the named accessors
        // -- so this only has to be a valid, defaulted snapshot.
        return $this->config ??= (new BrandingConfigFactory(new OEGlobalsBag([])))->create();
    }

    public function productName(bool $arabic = false): string
    {
        $this->guard();

        if ($arabic && $this->productNameArabic !== '') {
            return $this->productNameArabic;
        }

        return $this->productName;
    }

    public function tagline(bool $arabic = false): ?string
    {
        $this->guard();

        return $this->tagline;
    }

    public function themeVariant(): ThemeVariant
    {
        $this->guard();

        return $this->themeVariant;
    }

    public function isRtl(): bool
    {
        $this->guard();

        return $this->rtl;
    }

    public function tokens(ThemeVariant $variant): TokenSet
    {
        $this->guard();

        return new TokenSet($variant);
    }

    public function logo(LogoSlot $slot): BrandAsset
    {
        $this->guard();
        $this->logoCallCount++;

        $revision = $this->revision();
        $altText = $this->logoAltText[$slot->value] ?? null;

        if ($altText === null) {
            return BrandAsset::missing($slot, $revision);
        }

        return new BrandAsset($slot, $revision, '/stub/' . $slot->value . '/logo.png', $altText[0], $altText[1]);
    }

    public function revision(): BrandingRevision
    {
        $this->guard();

        return new BrandingRevision($this->revision);
    }

    public function smartStyleTokens(ThemeVariant $variant): SmartStyleContract
    {
        return SmartStyleContract::fromTokens($variant, $this->tokens($variant), $this->logo(LogoSlot::CoreLoginPrimary)->url());
    }

    public function tokenStylesheetUrl(): ?string
    {
        $this->guard();

        return $this->tokenStylesheetUrl;
    }

    private function guard(): void
    {
        if ($this->failure instanceof \Exception) {
            throw $this->failure;
        }
    }
}
