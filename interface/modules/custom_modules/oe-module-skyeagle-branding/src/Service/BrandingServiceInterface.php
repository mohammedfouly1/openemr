<?php

/**
 * The single front door to branding.
 *
 * Plan principle P1: nothing else in the codebase resolves a branding value for
 * itself. Templates, listeners and machine-facing contracts all ask this service,
 * so there is exactly one place to change when branding changes.
 *
 * Plane 3 (runtime resolution) contract: implementations MUST be read-only and
 * dependency-free. No network call, no Control Plane request, and no database query
 * beyond the single globals read OpenEMR already performs each request (locked Q76,
 * constraint C5). This is enforced by the ForbiddenBrandingHttpClientRule guardrail.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Service;

use OpenEMR\Modules\SkyEagleBranding\Asset\BrandAsset;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingConfig;
use OpenEMR\Modules\SkyEagleBranding\Theme\SmartStyleContract;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSet;

interface BrandingServiceInterface
{
    /** The immutable, already-parsed configuration for this request. */
    public function config(): BrandingConfig;

    /**
     * The product name for the given language.
     *
     * Arabic callers receive the Arabic product name where one is configured, so
     * RTL surfaces are not forced through the Latin wordmark.
     */
    public function productName(bool $arabic = false): string;

    /** The login tagline, or null when suppressed by configuration. */
    public function tagline(bool $arabic = false): ?string;

    /** Which of the two locked Saudi variants (Q77) this request renders. */
    public function themeVariant(): ThemeVariant;

    /** True when the active session renders right-to-left. */
    public function isRtl(): bool;

    /**
     * The effective token set: the immutable product palette (tier 1) with any
     * validated tenant overlay (tier 2) applied on top.
     */
    public function tokens(ThemeVariant $variant): TokenSet;

    /** Resolve a logo slot to a revision-stamped, tenant-correct web path. */
    public function logo(LogoSlot $slot): BrandAsset;

    /** The tenant's current branding revision; participates in every cache key. */
    public function revision(): BrandingRevision;

    /**
     * The SMART on FHIR style contract for a variant.
     *
     * Derived from the same TokenSet as the web CSS variables, so the two cannot drift
     * (docs/rebranding.md section 16.1). Exposing it here is what makes it reachable
     * through the single service contract (audit finding AR-P2-003).
     */
    public function smartStyleTokens(ThemeVariant $variant): SmartStyleContract;

    /**
     * URL of the per-tenant token stylesheet, or null when no tenant overlay is
     * configured. Null is the default and the common case: the product then renders
     * entirely from the shared immutable bundle and no extra request is made.
     */
    public function tokenStylesheetUrl(): ?string;
}
