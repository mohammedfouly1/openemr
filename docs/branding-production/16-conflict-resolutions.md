# 16 — Resolution of the Nine Open Conflicts

**Date:** 2026-08-09
**Authority:** project owner instruction to resolve the nine open items in `docs/RebrandingPlan.md` §1.2
according to the recommended advice.
**Scope:** brand package, token contract and planning documentation. **No OpenEMR application file, asset
or database value was changed.**

## Status summary

| # | Item | Outcome |
|---|---|---|
| 1 | CR-16 / D-9 — Arabic PDF fonts violate `Q25` | **CLOSED 2026-08-09.** Amiri Regular + Bold installed at `brand/typography/fonts/pdf/` with OFL-1.1 licence text, registered as THIQA-100–103, verified as genuine TTF (`0x00010000`) and hashed. `Q25` is now satisfied at the asset level; engine configuration (§1.3) is Phase 3 work |
| 2 | CR-17 / D-14 — `Q38` template-delivery pattern | **CLOSED** — namespaced `TemplatePageEvent` route adopted as the standing pattern (§2) |
| 3 | D-3 — product name legal + integration clearance | **SCOPED** — the machine-facing surface is now an exact 3-line inventory (§3). Legal and integration sign-off remain external |
| 4 | D-10 — product registration endpoint | **CLOSED** — registration is **disabled**, not repointed (§4) |
| 5 | D-11 — acknowledgements page | **SCOPED** — itemised disposition request prepared for counsel (§5). Counsel opinion remains external |
| 6 | D-4 — native Arabic proofreading | **SCOPED** — handoff package defined with safety-priority ordering (§6). Proofreading remains external |
| 7 | CR-20 / D-16 — cache-key specification | **CLOSED** — canonical specification and test matrix defined (§7) |
| 8 | CR-18 / D-15 — theme selector labels | **CLOSED, but not as decided here — corrected 2026-08-19.** This document decided "Light"/"Dark"; the shipped code was found to already return "Saudi Light"/"Saudi Dark", and the Owner ruled 2026-08-19 to keep that shipped behaviour instead. See §8 |
| 9 | CR-10 / D-12 — `border.strong`, dark `surface.input` | **CLOSED** — both ratified into the token contract and re-hashed (§9) |

Four items (2, 4, 7, 8, 9 — five in fact) are fully closed. Four (1, 3, 5, 6) are reduced from open
questions to defined work with a single named external action each.

---

## 1. Arabic PDF fonts — `Q25` compliance restored

**The conflict.** `Q25` locks *"Amiri and/or Noto Naskh Arabic"* for PDF, with explicit engine
configuration. The brand kit ships **IBM Plex Sans Arabic WOFF2**, which is a web format and is not either
named font. An earlier plan revision proposed IBM Plex for PDF; that would not satisfy `Q25`.

**Decision.** Do **not** seek an ADR to substitute. Amiri and Noto Naskh Arabic are both free, open-licensed
and purpose-designed for Arabic body text; there is no engineering reason to reopen a locked decision.
IBM Plex Sans Arabic remains the **web** face — no locked decision governs the web Arabic font.

**Selected font: Amiri.** Rationale: it is a Naskh typeface designed for body text in the Amiri tradition,
ships TTF directly, is OFL-1.1 licensed, and has mature Arabic shaping coverage. Noto Naskh Arabic is an
acceptable equivalent if the brand owner prefers it; `Q25` permits either.

### 1.1 Current state discovered during this resolution

`mpdf/mpdf ^8.2.7` bundles **83** fonts, of which the only Arabic-capable family is **XB Riyaz**
(`vendor/mpdf/mpdf/ttfonts/XB Riyaz*.ttf`, registered as `xbriyaz` in
`vendor/mpdf/mpdf/src/Config/FontVariables.php:260`). So Arabic PDF output is not *impossible* today — but
XB Riyaz is neither of the fonts `Q25` names, is a Persian-oriented face, and was never a deliberate
product choice. It must not be relied on.

`dompdf/dompdf ^3.1.4` is also a dependency and has **no** Arabic-capable font at all. Both engines must be
handled; a single-engine fix is incomplete.

### 1.2 Required assets — INSTALLED 2026-08-09

| Asset ID | File | Bytes | Signature | SHA-256 |
|---|---|---:|---|---|
| THIQA-100 | `brand/typography/fonts/pdf/Amiri-Bold.ttf` | 414,560 | `0x00010000` (TrueType) | `8cfed49b…92e79a` |
| THIQA-101 | `brand/typography/fonts/pdf/Amiri-Regular.ttf` | 437,780 | `0x00010000` (TrueType) | `cd2550c0…12c43c0` |
| THIQA-102 | `brand/typography/fonts/pdf/OFL.txt` | 4,389 | — | `72de68e5…fb9b22` |
| THIQA-103 | `brand/typography/fonts/pdf/README-amiri.md` | 2,238 | — | `44db47f8…1ec5426` |

Both font files carry a valid TrueType signature, and the OFL-1.1 licence text ships alongside them as
that licence requires. `brand/manifests/asset-manifest.{json,csv}` and `SHA256SUMS` were regenerated:
**107 assets + 16 docs = 123 entries, verified 123/123 by two independent hashers.**

**`Q25` is satisfied at the asset level.** What remains is Phase 3 implementation work, not a blocker:
engine configuration per §1.3 and the acceptance tests in §1.4.

### 1.3 Engine configuration specification

**mPDF** — register the family and point the font directory at the bundled copy:

| Setting | Value |
|---|---|
| `fontDir` | default mPDF dirs **plus** the product PDF font directory |
| `fontdata` entry | `'amiri' => ['R' => 'Amiri-Regular.ttf', 'B' => 'Amiri-Bold.ttf', 'useOTL' => 0xFF, 'useKashida' => 75]` |
| `default_font` | `amiri` for Arabic documents |
| `autoScriptToLang` / `autoLangToFont` | enabled, so mixed EN/AR content selects the right face |

`useOTL => 0xFF` enables OpenType layout (required for correct Arabic joining); `useKashida => 75` enables
justification by elongation, which is the correct Arabic typographic behaviour and is why a Naskh face is
specified rather than a Latin fallback.

**dompdf** — register via `@font-face` in the PDF stylesheet with the font directory configured, since
dompdf has no equivalent bundled-font registry. Note dompdf's Arabic shaping is weaker than mPDF's; if a
report renders Arabic through dompdf, evaluate moving that report to mPDF rather than accepting degraded
shaping.

### 1.4 Acceptance tests (dependency D-9)

1. Arabic text renders with correct letter joining (initial / medial / final / isolated forms).
2. Kashida justification behaves correctly in a justified paragraph.
3. Mixed Arabic/Latin runs render both faces correctly in one line.
4. Western Arabic numerals `0–9` render LTR inside an RTL run (patient IDs, claim numbers, money, dates).
5. The font is **embedded** in the output PDF — verify it is not relying on a host font (`Q25`: *"Arabic
   PDFs may not rely on host-font availability"*).
6. Statement / receipt / clinical report templates all pass 1–5.
7. Both engines are exercised, or every Arabic-producing path is proven to use mPDF only.

---

## 2. `Q38` template-delivery pattern — standing pattern adopted

**The conflict.** Shadowing a core Twig template requires adding a path to the **main** namespace, which is
inherently unnamespaced and resolution-order dependent — the exact fragility `Q38` exists to eliminate.

**Resolution — adopted as the standing pattern for every SaaS module, no ADR required:**

1. A module registers its templates **only** under its own namespace:
   `$loader->addPath($dir, 'oe-module-thiqa-branding')` on `TwigEnvironmentEvent::EVENT_CREATED`.
2. To substitute a template on a render path, a listener on `TemplatePageEvent` rewrites the template name
   to the **namespaced** path, e.g. `@oe-module-thiqa-branding/api/smart/smart-style_dark.json.twig`.
3. `FilesystemLoader::prependPath()` into the main namespace is **prohibited** for SaaS modules.

**Verified dispatch points:**

| Path | Dispatch | Listener key | Discriminator |
|---|---|---|---|
| SMART style JSON | `SMARTAuthorizationController::renderTwigJson():359` — dispatched **without** an event name | `TemplatePageEvent::class` | `getPageName() === 'oauth2/authorize/smart-style'` |
| Login page | `interface/login/login.php:272` — dispatched with a name | `TemplatePageEvent::RENDER_EVENT` (`events.core.page`) | `getPageName() === 'login/login.php'` |
| OAuth2 authorize screens | `AuthorizationController.php:953`; `ClientAdminController.php:245` | `TemplatePageEvent::class` | per-page name |

Note the two different registration keys — a listener registered only on `RENDER_EVENT` will **not** fire
on the SMART path, and vice versa. Both registrations are required.

**Consequence for the login logo (`BRAND-053`).** The core partial hardcodes `alt=""` with no variable, so
the namespaced route would mean forking a layout *and* a partial. The accepted approach is instead a
one-line upstream-first change to `templates/login/partials/html/primary_logo.html.twig` reading
`{{ primaryLogoAlt|default('') }}`, with the value supplied through the existing event. BRAND-053 therefore
remains a **tracked conditional patch**; the earlier `Trk: NO` claim stays retracted.

**CI enforcement (plan WP-2.7):** fail the build if a SaaS module calls `prependPath()`, or calls
`addPath()` without a namespace argument.

---

## 3. Product name — the exact machine-facing surface

**Purpose.** Replace the assumption *"changing the product name may break hospital interfaces"* with the
actual, verified list, so the integration owner can clear a short, concrete set rather than an open-ended
risk.

**Method.** Repository-wide search for consumers of the `openemr_name` global.

### 3.1 Machine-facing carriers — the complete list

| # | Carrier | Location | What receives it | Real-world risk |
|---|---|---|---|---|
| 1 | **HL7 `MSH-3` (Sending Application)** | `interface/reports/non_reported.php:162` | The **only** first-party HL7 MSH emission in the codebase. It is an `ADT^A01` **syndromic-surveillance** export addressed to `PH_SS-NoAck^SS Sender^2.16.840.1.114222.4.10.3^ISO` | **Narrow.** This is a public-health surveillance feed, *not* the general lab/radiology interface traffic that the concern assumed. There is no other MSH emission to break |
| 2 | **QRDA Cat III — organisation name** | `src/Services/Qrda/ExportCat3Service.php:430` | Quality-reporting XML | Receiving body may validate the organisation name |
| 3 | **QRDA report — organisation name** | `src/Services/Qrda/QrdaReportService.php:218` | Quality-reporting XML | As above |

### 3.2 Non-machine-facing consumers (no clearance needed)

`interface/main/tabs/main.php` (browser title ×3), `interface/main/about_page.php`,
`interface/login/login.php`, `portal/index.php`, `portal/home.php`,
`interface/patient_file/manage_dup_patients.php`, `interface/smart/register-app.php`,
`templates/interface/main/tabs/user_data_template.html.twig`,
`interface/modules/custom_modules/oe-module-comlink-telehealth/src/TelehealthGlobalConfig.php`,
plus the bootstrap fallback in `interface/globals.php`. All are user-visible UI or email text.

### 3.3 What to ask each owner

**Legal:** clear `Thiqa` (EN) and `ثقة` (AR) as the product name for a medical product marketed in Saudi
Arabia. Note that *thiqa* is a common Arabic noun meaning *trust* and is in use as a health-sector brand
elsewhere in the Gulf, so a clearance search is genuinely warranted rather than a formality.

**Integration owner:** for the three carriers in §3.1 only —

1. Is the syndromic-surveillance feed live with a real receiver? If not, item 1 is moot.
2. If live, does the receiver match on `MSH-3`? Confirm with the receiving authority, do not assume.
3. For QRDA, does the receiving body validate the organisation name against a registration?
4. Agree a cutover date per live interface.

Until cleared, `openemr_name` can be set for **UI** purposes while these three carriers are pinned — but
that requires a small code change, so the simpler path is to clear the three and switch once.

---

## 4. Product registration — disabled

**Decision (D-10, closed).** Product registration is **disabled**, not repointed to a Thiqa endpoint.

**Rationale.** The feature is opt-in telemetry (registration email plus install UUID) sent to the
infrastructure of the upstream project this product is replacing. Repointing means standing up, securing
and operating a registration service that delivers no product value. Disabling removes the outbound call
entirely, needs no endpoint to exist, and is the smaller and more defensible change.

**Implementation note for Phase 3 (core patch #3).** Make registration a disabled feature at
`src/Services/ProductRegistrationService.php:121` rather than substituting a URL, so no placeholder or
live endpoint can ever be reached. A release assertion must confirm zero outbound calls to
`reg.open-emr.org` **and** that no `reg.skyeagle.uk` host is referenced.

---

## 5. Acknowledgements page — disposition request for counsel

**The exposure.** `acknowledge_license_cert.html` returns **HTTP 200 without authentication**
(runtime-verified, 24,739 bytes) and is linked from both the login page and the About page.

**Itemised list for counsel** — each item needs one of: *keep as-is*, *keep but authenticate*, *modify*,
*remove*.

| # | Content | Consideration |
|---|---|---|
| 1 | OpenEMR Foundation branding and attribution | GPL-3.0 attribution may be legally required — probably *keep* |
| 2 | ONC certification claims | Certification belongs to the certified product/version. Making claims about a rebranded derivative may be a misrepresentation — needs a definite answer |
| 3 | OpenCoreEMR Inc. — website, GitHub, X/@openCoreEMR, LinkedIn, Bluesky | Third-party corporate identity |
| 4 | **Two personal email addresses** | Personal data on a public page; PDPL relevance independent of licensing |
| 5 | `nahahealthclinic.com`, `discoverandchange.com`, `infeg.com`, `affordablecustomehr.com`, `openplanit.com` / `openplanit.ie`, `github.com/epsdky` | Six further third-party identities |

**Recommended framing.** Two questions are separable: (a) what must be retained for GPL/licensing
compliance, and (b) whether the page should be reachable **unauthenticated**. Authentication-gating is a
platform decision that reduces exposure without touching any attribution text, and can proceed
independently of the content review.

**Constraint (C7):** no attribution, licence text or certification claim may be altered on branding
grounds. Only counsel can authorise a change here.

---

## 6. Arabic proofreading — handoff package

**Deliverable to the localisation owner:**

| Element | Source |
|---|---|
| EN/AR value pairs | `14-string-replacement-map.md` Parts 4 and 5 |
| Product name and tagline | Same, header block |
| Mockup-rendered Arabic strings | `brand/rtl/*.png` (5 surfaces) |
| Target catalogue | OpenEMR `lang_definitions` (SQL-backed `lang_*` tables — **not** `.po`/`.mo`) |

**Review order — safety priority first.** Not alphabetical, not document order:

1. Clinical and safety-bearing strings (status badges: نشط / متابعة / حرج; clinical form labels).
2. Identity and authentication strings (login, portal login, authorisation prompts).
3. Financial and billing strings (statements, balances, claim references).
4. Navigation and general UI.
5. Marketing copy (tagline).

**Required return evidence:** the exact `lang_constant` identifiers reviewed, the approved Arabic value for
each, reviewer name and date, and an explicit statement of MSA compliance (`Q22` locks Modern Standard
Arabic as the product language, not dialect). Import into `lang_definitions` executes under `MVP-004`,
which must return proof that those exact constants were applied.

**Also verify during the same pass:** numerals inside RTL runs. The Arabic data-table mockup uses
Arabic-Indic digits in pagination while using Western digits in identifier columns. `14-…md` Part 7 item 4
requires Western Arabic numerals `0–9` LTR for patient IDs, claim numbers, money and dates — confirm the
rendered strings match that rule.

---

## 7. Canonical cache-key specification

**Principle.** The tenant branding revision is **always added alongside** existing core cache identifiers,
never substituted for them. Replacing `?v=` would leave stylesheets stale across an application upgrade;
omitting the revision would leave branding stale across a branding change. Both are required.

**Verified core behaviour:** `interface/globals.php:479-480` appends `?v=<$v_js_includes>` to
`css_header` and `compact_header`. The variable is `$v_js_includes`, not `$v_database`.

### 7.1 Per-asset-type URL construction

| Asset | URL form | Owner | Notes |
|---|---|---|---|
| Theme CSS (shared bundle) | `…/public/themes/<file>.css?v=<v_js_includes>` | Core — **unchanged** | Identical for every tenant by design. Must **not** carry a tenant revision, or the shared bundle becomes uncacheable across tenants |
| Tier 2 tenant token CSS | `…/oe-module-thiqa-branding/public/branding-tokens.php?rev=<branding_revision>` | Branding layer | Served `Cache-Control: public, max-age=31536000, immutable`. Tenant scope comes from the session/host, never from a URL parameter |
| Logos | `<LogoService path>?t=<mtime>&rev=<branding_revision>` | Branding layer extends core | `?t=` is existing core behaviour and is preserved; `&rev=` is appended |
| Favicon | `?t=<mtime>` | Core — unchanged | Already per-site |
| SMART style JSON | not cached by OpenEMR; `logo_primary` carries `?t=` and `&rev=` | Branding layer | The machine contract must reflect the current revision |
| Fonts | `…/public/assets/fonts/thiqa/<file>.woff2` | Build | Content-addressed by release; no per-tenant component |

### 7.2 Rules

1. Never remove or overwrite a core cache parameter; only append.
2. Parameter order is fixed as `?t=` then `&rev=` (or `?v=` then `&rev=`) so proxy caches keyed on the raw
   query string do not fragment.
3. `branding_revision` is a monotonic integer, tenant-scoped, materialised into
   `globals.saas_branding_revision`.
4. No URL may contain a tenant identifier, site name, or any value that discloses platform topology.
   Tenant scope is established by host/session, never by a query parameter (`Q12`, `BLK-005`).
5. A rollback to revision *n−1* must produce byte-identical URLs to the earlier revision *n−1* state.

### 7.3 Test matrix (dependency D-16 acceptance)

| # | Scenario | Expected |
|---|---|---|
| 1 | Application upgrade changes `$v_js_includes` | Theme CSS re-fetched; branding assets untouched |
| 2 | Branding revision increments | All branding URLs change; theme CSS URL unchanged |
| 3 | Two tenants at the **same** revision number | No shared URL resolves to the other tenant's asset |
| 4 | Rollback to revision *n−1* | URLs and bytes identical to the prior state |
| 5 | Behind a CDN/proxy keyed on the full query string | No cross-tenant cache hit |
| 6 | Query-parameter order reversed by an intermediary | Still resolves; no duplicate cache entries created by our own code |
| 7 | Tier 2 empty (default) | No token stylesheet link is emitted at all |
| 8 | Logo replaced without a revision bump | `?t=` mtime alone still invalidates |

---

## 8. Theme selector labels — "Light" / "Dark" accepted

> **CORRECTED 2026-08-19 (D-15).** This section's "Verified behaviour" claim below was checked against
> the actually-shipped code and found wrong: `interface/modules/custom_modules/oe-module-thiqa-branding/src/Theme/ThemeVariant.php:46-47`'s
> `label()` method literally returns `'Saudi Light'`/`'Saudi Dark'`, not the generic "Light"/"Dark" this
> section predicted from `edit_globals.php`'s filename-derivation logic. The branding module apparently
> supplies its own label rather than relying on that core fallback. This was surfaced to the Owner as a
> genuine open decision rather than silently trusting this document, and the **Owner ruled 2026-08-19:
> keep "Saudi Light"/"Saudi Dark" as shipped, no code change.** The "Decision" line immediately below
> ("Accept 'Light' and 'Dark' in the administration selector") is therefore superseded — the shipped,
> Owner-ratified behaviour is the opposite of what was decided here. See
> `docs/branding/remaining-dependencies.md` §4, D-15, and `docs/branding/adr/ADR-BRAND-004-q77-theme-surface-exclusion.md`
> for the full trace.

**Verified behaviour.** `interface/super/edit_globals.php:736-742` derives the dropdown label as
`ucfirst(str_replace('_', ' ', substr($filename, 6)))` with `.css` stripped. `style_light.css` therefore
displays as **"Light"**, and `style_dark.css` as **"Dark"**. Keeping the filenames (decision CR-9) means
administrators will not see the words "Saudi Light" / "Saudi Dark". *(See the 2026-08-19 correction
above: the branding module overrides this with its own label, so this prediction did not hold.)*

**Decision (superseded 2026-08-19 — see correction above).** Accept "Light" and "Dark" in the administration selector.

**Rationale.** `Q77` explicitly states the solution *"SHOULD be implemented at the build/deployment layer
rather than by adding a recurring OpenEMR core selector patch"* — and its evidence section singles out this
very file as one that would otherwise need patching on every rebase. Producing the literal product labels
would require patching it. The string is admin-only: no patient, clinician or receptionist ever sees it,
and the administrator is choosing between exactly two options whose meaning is unambiguous.

**Documentation rule.** *Saudi Light* and *Saudi Dark* are the product names used in every user-facing
document, release note and specification. The administration selector shows the inherited internal names.
Acceptance criteria must not require the literal strings in that dropdown.

---

## 9. `borderStrong` and `surfaceInput` — ratified

**Decision.** Both governance gaps recorded in `07-token-validation.md` are closed by adding the tokens
explicitly to `brand/tokens/thiqa-tokens.json`, so no implementation has to infer them.

| Token | Light | Dark | Derivation |
|---|---|---|---|
| `borderStrong` | `#4B5266` | `#AEB5C4` | `text.secondary` |
| `surfaceInput` | `#FFFFFF` | `#121A2E` | light `surface`; dark `surface` (body context) |
| `surfaceInputOnRaised` | `#FFFFFF` | `#0B1220` | dark `background` (inputs on raised cards) |

Naming follows the existing flat-sibling style (`surfaceSunken`, `surfaceRaised`).

**Contrast verification — 4 new pairs, all PASS** (added to `brand/qa/wcag-contrast-results.json`, taking
the total from 34 to 38 pairs, still **0 FAIL**):

| Pair | Ratio | Required | Status |
|---|---:|---:|---|
| light `borderStrong` on background | 7.45 | 3.0 | PASS |
| light `borderStrong` on surface | 7.78 | 3.0 | PASS |
| dark `borderStrong` on background | 9.10 | 3.0 | PASS |
| dark `borderStrong` on surface | 8.41 | 3.0 | PASS |

Body text on the new input surfaces was also checked and is already covered by existing rows: 16.43 (light
on `surfaceInput`), 16.01 (dark on `surfaceInput`), 17.31 (dark on `surfaceInputOnRaised`).

---

## 10. Files changed by this resolution

| File | Change |
|---|---|
| `brand/tokens/thiqa-tokens.json` | Added `borderStrong`, `surfaceInput`, `surfaceInputOnRaised` to both themes (item 9) |
| `brand/qa/wcag-contrast-results.json` | 4 new `borderStrong` UI pairs; 34 → 38 pairs; 0 FAIL |
| `docs/branding-production/07-token-validation.md` | Governance flags 2 and 3 marked resolved; coverage table updated |
| `docs/branding-production/14-string-replacement-map.md` | Registration disabled (item 4); D-3 scope narrowed to the §3 inventory |
| `docs/branding-production/16-conflict-resolutions.md` | **New** — this file |
| `brand/manifests/*` | Re-issued after the above |
| `docs/RebrandingPlan.md` | §1.2 register updated; cache-key spec and `Q38` pattern referenced here |

**No OpenEMR application file, asset or database value was changed.**

## 11. What remains genuinely external

| Item | Single remaining action | Owner | Status |
|---|---|---|---|
| 1 | ~~Obtain the Amiri TTFs~~ | Brand | **DONE 2026-08-09** |
| 3 | Legal clearance + integration owner confirmation on the three carriers in §3.1 | Legal + integration | **Deferred by scope** — see §12 |
| 5 | Counsel disposition on the five items in §5 | Legal | **Superseded by scope** — see §12 |
| 6 | Native MSA proofreading pass per §6 | Localisation | Open |

---

## 12. Scope amendment — MVP demo (2026-08-09)

**Owner instruction.** The current target is an **MVP marketing demo hosted on a public website**. It is
not deployed at a hospital, is not connected to FHIR, and neither sends nor receives claims. Compliance and
registration steps are taken if and when a real customer adopts the product. Only synthetic demo data is
required.

**Effect on the open items:**

| Item | Effect |
|---|---|
| **3 — product name** | The *integration* half is **moot for demo scope**: with no live HL7 receiver and no claims traffic, the single syndromic-surveillance `MSH-3` emission and the two QRDA fields have nobody to break. **Gate:** re-open before any real interface is connected. The *legal* half is deferred by owner decision (see the note below). |
| **5 — acknowledgements page** | Counsel review is **not the right instrument for demo scope**. Replaced by a cheaper, fully reversible control: hide the links via `display_acknowledgements=0` and `display_acknowledgements_on_login=0`, **and** block the file at the web server, since it is a static file reachable by direct URL regardless of those globals (§12.1). No page content is altered, so nothing is prejudged for a later customer deployment. |
| **6 — Arabic proofreading** | **Unchanged, and arguably raised in priority**: for a demo aimed at Saudi buyers, Arabic quality is the product's shop window. |

### 12.1 Acknowledgements page under demo scope

> **APPLIED 2026-08-09 — step 1 of 2 complete.** On owner instruction, both link gates were switched off in
> the local demo database:
>
> | Global | Before | After |
> |---|---|---|
> | `display_acknowledgements` (BRAND-062) | `1` | **`0`** |
> | `display_acknowledgements_on_login` (BRAND-050) | `1` | **`0`** |
>
> `UPDATE globals` affected exactly 2 rows; `SELECT COUNT(*) FROM globals` remains **490**. This is a
> SAFE-CONFIG change, reversible by setting both back to `1`.
>
> **Verified:**
> - Login page: `HTTP 200`, **9,206 bytes** (was 9,375), **zero** references to `acknowledge_license_cert`.
> - About page: gated at `templates/core/about.html.twig:58` on `displayAcknowledgements`, bound from the
>   global at `interface/main/about_page.php:54` — link suppressed by the same switch.
> - Direct URL returned `HTTP 200`, 24,739 bytes at this point — exactly as predicted, which is why step 2
>   was required.
>
> **APPLIED 2026-08-09 — step 2 of 2 complete.** A deny rule was added to
> `C:\openemr-stack\apache\conf\httpd.conf` (backup taken first at `httpd.conf.bak-20260809-173921`;
> pre-edit SHA-256 `417f9603…bacb68a7`):
>
> ```apache
> <Files "acknowledge_license_cert.html">
>     Require all denied
> </Files>
> ```
>
> Placed alongside the existing `<DirectoryMatch>` hardening block, with an inline comment explaining why
> the file is blocked rather than deleted. `httpd -t` returned **Syntax OK** before restart.
>
> **Verified after restart:**
>
> | Check | Result |
> |---|---|
> | `/acknowledge_license_cert.html` | **HTTP 403** |
> | `Acknowledge_License_Cert.html` (mixed case) | **HTTP 403** |
> | `ACKNOWLEDGE_LICENSE_CERT.HTML` (upper) | **HTTP 403** |
> | `acknowledge_license_cert.HTML` (mixed ext) | **HTTP 403** |
> | Login page | HTTP 200, 9,206 bytes — unchanged |
> | Favicon | HTTP 200, 15,086 bytes |
> | Theme CSS | HTTP 200, 321,808 bytes |
> | Apache error log | `AH01630: client denied by server configuration` for each attempt |
> | PHP error log | no new entries after restart (last entry predates it) |
>
> Case variants were tested specifically because Apache's `<Files>` is case-sensitive by default while the
> Windows filesystem is not; on this platform Apache matches case-insensitively, so no bypass exists.
>
> **Both steps are now complete: the page is unreachable, in-app and by direct URL.**
>
> No page content was altered. `acknowledge_license_cert.html` is untouched and remains a tracked upstream
> file, preserving constraint **C7** and the GPL notices it carries. Reverse by setting the two globals
> back to `1` and removing the `<Files>` block.
>
> **Production note:** this rule is applied to the local development stack. The same rule must be added to
> the demo host's web-server configuration (and to any Kubernetes ingress/vhost config) — it is not carried
> in the application image.

`acknowledge_license_cert.html` is a **static file at the repository root**. The two globals only suppress
the *links* to it from the login and About pages; the file itself remains fetchable at its direct URL.
Complete removal from a public demo therefore needs both:

1. `display_acknowledgements = 0` and `display_acknowledgements_on_login = 0` (SAFE-CONFIG, BRAND-062/050).
2. A web-server deny rule for `/acknowledge_license_cert.html` on the demo host (infrastructure config, not
   a code change).

This satisfies constraint **C7** — no attribution text, licence notice or certification claim is altered,
so the page remains intact and available for a future deployment where counsel has ruled on it.

### 12.2 Two risks the demo framing raises rather than lowers

Recorded for the owner's awareness; neither blocks the demo, and both are cheap to address.

1. **ONC certification claims on a public marketing site.** The page asserts ONC certification, which
   attaches to the certified product and version — not to a rebranded derivative. On an internal demo this
   is inert; on a public marketing site aimed at prospective buyers it is a claim being made to the market.
   §12.1 removes it from the demo entirely, which is why that control is recommended regardless of scope.
2. **Public trademark use.** Marketing use of a brand name on a public site is the most visible form of
   commercial use, not the least. The owner has decided to defer clearance; recorded here so the sequencing
   is deliberate rather than accidental.
