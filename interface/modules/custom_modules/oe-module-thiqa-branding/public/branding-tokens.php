<?php

/**
 * Serves the per-tenant Tier 2 design-token stylesheet.
 *
 * Plan section 3.2.2 option (a). This endpoint exists because StyleFilterEvent runs every
 * candidate path through ModulesApplication::filterSafeLocalModuleFiles(), which accepts
 * a file only if it resolves under interface/modules/ -- so the tenant stylesheet has to
 * live inside the module tree. Emitting it from PHP rather than writing a generated .css
 * file keeps the deployed image read-only, which the signed/pinned OCI artifact model
 * assumes; nothing here writes to disk.
 *
 * Cost is bounded: the response is immutable and revision-keyed, so a browser fetches it
 * once per branding revision, not once per page. When the tenant has no overlay -- the
 * default and by far the common case -- BrandingService::tokenStylesheetUrl() returns
 * null, no <link> is emitted, and this file is never requested at all.
 *
 * Output is structurally constrained: every byte comes from CssVariableRenderer, which
 * accepts only typed DesignToken objects and can express nothing but
 * `--name: #RRGGBB;` declarations. There is no string passthrough, which is what makes
 * locked Invariant 9 (no tenant-supplied CSS) hold here rather than being a promise.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandAssetResolver;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingConfigFactory;
use OpenEMR\Modules\ThiqaBranding\Service\BrandingService;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeResolver;
use OpenEMR\Modules\ThiqaBranding\Token\CssVariableRenderer;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSetParser;
use OpenEMR\Services\LogoService;

// Branding applies to the login page, which is unauthenticated, so this stylesheet has to
// be fetchable without a session — exactly as interface/login/login.php does it. Without
// this flag globals.php cannot resolve a site for an anonymous request and answers
// HTTP 400 "Invalid URL", which is what a live fetch of this endpoint originally returned.
//
// $ignoreAuth only relaxes the *authentication* requirement. It grants no data access:
// everything below reads branding globals and a product token file, and the response is a
// CSS custom-property block. No patient data is reachable from here.
//
// Tenant scope is then resolved by globals.php itself, from the session or the host, on
// exactly the same code path as every other unauthenticated entry point. This endpoint
// introduces no tenant-selection mechanism of its own; keeping a query parameter from
// switching tenant context is the platform routing guarantee under locked Q12 / BLK-005,
// and is not weakened or strengthened here.
$ignoreAuth = true;

require_once __DIR__ . '/../../../../globals.php';

$globals = OEGlobalsBag::getInstance();

$branding = new BrandingService(
    new BrandingConfigFactory($globals),
    new BrandAssetResolver(new LogoService()),
    new ThemeResolver(),
    new TokenSetParser(),
    $globals->getProjectDir() . '/' . BrandingService::TOKEN_DOCUMENT_RELATIVE_PATH,
    $globals->getWebRoot() . '/' . BrandingService::TOKEN_STYLESHEET_RELATIVE_PATH,
);

$config = $branding->config();

header('Content-Type: text/css; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!$config->hasTenantOverlay()) {
    // Reachable only if a stale cached <link> outlives the overlay being cleared. An
    // empty stylesheet is the correct answer: the shared immutable bundle already holds
    // the product palette, so emitting nothing leaves Tier 1 in force.
    header('Cache-Control: no-store');
    exit;
}

// Immutable: the URL carries ?rev=<n> and a new revision produces a new URL, so this
// response can never need revalidating (plan section 3.8.1).
header('Cache-Control: public, max-age=31536000, immutable');

// Only the active variant is emitted. Sending both would mean the second `:root` block
// unconditionally overriding the first, since neither carries a distinguishing selector
// -- dark would win even in light mode. The variant is already resolved server-side from
// css_header, so the correct set is known here.
//
// CssVariableRenderer deliberately emits declarations without braces so that it cannot
// be steered out of the declaration context; the `:root` selector is therefore supplied
// here as a fixed literal, never from data.
$renderer = new CssVariableRenderer();
$declarations = $renderer->render($branding->tokens($branding->themeVariant()));

echo ':root {' . PHP_EOL . $declarations . PHP_EOL . '}' . PHP_EOL;
