<?php

/**
 * Which of the two branding delivery planes an observation belongs to.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Observability;

/**
 * The materialiser leaves branding in two places, and only one of them reaches a browser.
 *
 *  - **Served.** The tenant overlay stored in `saas_branding_tokens_light` /
 *    `saas_branding_tokens_dark`, the `saas_branding_revision` that keys its URL, and the
 *    `saas_branding_materialised_at` stamp written with it. This is what
 *    `BrandingService::tokenStylesheetUrl()` reads to decide whether to emit a `<link>`,
 *    and what `public/branding-tokens.php` renders when that link is followed. A fault
 *    here changes what a user sees.
 *  - **Static artefact.** The generated `public/branding/<site>/tokens-{light,dark}.css`
 *    files that `TokenCssWriter` still commits on every materialisation, including for an
 *    empty overlay. **No page links them and no request reads them** — the only code that
 *    touches them is FilesystemStylesheetProbe's `is_file()`. Dependency D-8 records that
 *    this second route is still written and still unserved; see
 *    `docs/branding/remaining-dependencies.md`.
 *
 * Separating them is the whole point. Before this distinction existed the health check
 * cross-referenced a served fact (the revision) against an unserved one (file existence)
 * and called the disagreement an inconsistency — so the live tenant was reported
 * `inconsistent`, exit 1, while its served branding was entirely correct. Reporting a
 * healthy tenant as broken is as untruthful as the reverse, and a failing exit code that
 * nobody can act on trains operators to ignore it (finding S2-P1-18).
 *
 * Backed by a string because the plane is serialised into monitoring payloads and PSR-3
 * log context alongside the finding it qualifies.
 */
enum BrandingObservationPlane: string
{
    /** What the browser actually fetches and renders. */
    case Served = 'served';

    /** Generated files the materialiser writes that nothing serves (D-8). */
    case StaticArtefact = 'static_artefact';

    /**
     * Whether a finding on this plane makes the tenant's branding a failure.
     *
     * This is the single place the severity rule lives, so the health check, the console
     * command and the CI contract cannot drift apart on it. Only the served plane can
     * fail: no state of an unread file can change a rendered page, so a static-artefact
     * finding is worth printing and worth acting on eventually, but it is never grounds
     * for failing a health probe.
     */
    public function failsHealth(): bool
    {
        return match ($this) {
            self::Served => true,
            self::StaticArtefact => false,
        };
    }

    /** Operator-facing wording for CLI output and report sections. */
    public function label(): string
    {
        return match ($this) {
            self::Served => 'served branding state',
            self::StaticArtefact => 'generated files that nothing serves',
        };
    }
}
