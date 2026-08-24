# Remaining dependencies — Phase 4 verification (F3)

**Date of this verification pass:** 2026-08-10. **Environment:** native Windows dev stack (no Docker) per
`CLAUDE.local.md` — Apache 2.4.57 + PHP 8.3.33 + MariaDB 11.8.8 on `http://localhost:8300`, live and
running throughout this pass. **Branch:** `feat/thiqa-branding-foundation`.

**Correction to the task briefing.** The task briefing stated the Phase 2/3 implementation work was
uncommitted, working-tree-only. That was **not the state found at the start of this pass**: `git log`
shows 8 branding-implementation commits already on this branch (`a1c22b6a1` … `c6c3f9e6e`), and
`git status --short` returned only two unrelated untracked entries (a stray `.docx` under
`Documentation/EHI_Export/` and this `docs/branding/` directory being created). All findings below are
against that committed state, live-verified where stated.

**Method.** Every row below is backed by a command or file read actually executed in this session — not
inferred from the plan's design intent, and not copied from `docs/AuditRebranding.md` without independent
confirmation unless explicitly marked "not independently re-verified." Where I expected a check to pass
and it didn't (or vice versa), that is called out under "Surprises" at the end rather than silently
smoothed over.

---

## 1. Coverage matrix (45 areas, rebuilt from `docs/rebranding.md` §19)

Column 3 (`Group 1 status`) is carried forward unchanged — it is a certified Group 1 artefact, not
re-audited here. Column 4 is this pass's verdict: **DONE-VERIFIED** (checked directly this session, with
evidence cited), **CARRIED-FORWARD** (not independently re-checked this session; status per
`docs/branding/coverage-matrix.md`'s more detailed Phase 2/3 pass, which itself cites `docs/AuditRebranding.md`),
**DEFERRED-WITH-REASON** (known gap, ticketed against a D-item), or **BLOCKED** (an external dependency
prevents verification or completion).

| # | Area | Group 1 status | Group 2/3 status (this pass) | Evidence |
|---|---|---|---|---|
| 1 | Product name | VC | **DONE-VERIFIED** | `globals`: `openemr_name = Thiqa`; login title "Thiqa Login"; `admin.php` title/H2 "Thiqa Site/Multi-Site Administration"; FHIR controller reads the global (see #22) |
| 2 | Login | VC | **DONE-VERIFIED** | Live `GET /interface/login/login.php?site=default` → 200; only 1 "OpenEMR" occurrence in the HTML, and it is the frozen `OpenEMR=` session cookie name (expected, C6) |
| 3 | Main shell / header | VC | CARRIED-FORWARD | Not re-checked live (needs authenticated session); see `coverage-matrix.md` row 3 |
| 4 | Menus | VC | CARRIED-FORWARD | Not re-checked live this pass; see `coverage-matrix.md` row 4 |
| 5 | Footer | VC | CARRIED-FORWARD | No footer branding exists (unchanged fact) |
| 6 | Portal (login) | VC | CARRIED-FORWARD | Not re-checked live this pass |
| 6b | Portal (post-auth) | IDA | **BLOCKED** | D-7 — needs a portal patient credential; genuinely gated, not attempted (read-only DB constraint + no test patient) |
| 7 | Logos | VC | **DONE-VERIFIED** | Login page: `<img ... alt="Thiqa logo">`; favicon hash-identical at both serving paths (see #8) |
| 8 | Favicon | VC | **DONE-VERIFIED** | `GET /public/images/logos/core/favicon/favicon.ico` → 200; `GET /public/images/favicon.ico` → 200 (see Surprises — this path is **not** broken here); SHA-256 identical at both paths (`4026d768...`) |
| 9 | Legacy logos | VC | CARRIED-FORWARD | Not re-checked this pass |
| 10 | Themes | VC | **DONE-VERIFIED** | `webpack.themes.js` entry map: only `style_light`/`style_dark` (+ compact/RTL/RTL-compact/tabs/misc variants) as Thiqa SCSS sources; no `solar`/`manila`/`cobalt_blue`/`forest_green` entries. `public/themes/` has 18 compiled CSS files, same set, no surplus. Surplus SCSS (`style_manila.scss`, `style_solar.scss`, `interface/themes/colors/style_cobalt_blue.scss`, `style_forest_green.scss`) still present in-tree but unused as entries — matches Q77's "excluded from build, not deleted" disposition |
| 11 | Colors | VC | **DONE-VERIFIED** | `Accessibility` isolated suite (WCAG/contrast): 110/110 pass; `Token` isolated suite: 393/393 pass |
| 12 | Fonts | VC | CARRIED-FORWARD | Not re-checked this pass; `coverage-matrix.md` row 12 records the F-03 fix (Lato→shipped fonts) with its own evidence |
| 13 | Language strings | VC | **DEFERRED-WITH-REASON** | D-4 (native Arabic proofreading) still open — no code change can close this |
| 14 | About | VC | CARRIED-FORWARD | Not re-checked this pass |
| 15 | Help | VC | CARRIED-FORWARD | Unchanged, no branding-layer touch expected |
| 16 | Documentation in UI | VC | **DONE-VERIFIED** | `GET /acknowledge_license_cert.html` → **403** (Apache `<Files>` deny rule, per `CLAUDE.local.md` §10) — confirmed exactly as documented, not a regression |
| 17 | Emails | VC | CARRIED-FORWARD | Unchanged, brand-free templates |
| 18 | Notifications | SPC | CARRIED-FORWARD | Unchanged, no SMS templates exist |
| 19 | PDFs | IDA | **BLOCKED** | D-9 (Q25 Arabic fonts) confirmed still open this pass — see D-register below; also needs ≥1 patient (G-05r) |
| 20 | Printed reports | IDA | **BLOCKED** | Same as #19 |
| 21 | Prescriptions | SPC | CARRIED-FORWARD | Not re-checked this pass |
| 22 | FHIR | VC | **DONE-VERIFIED (source) / BLOCKED (runtime)** | `src/RestControllers/FHIR/FhirMetaDataRestController.php:75` reads `$this->globalsBag->getString('openemr_name') ?: 'Thiqa'` — confirmed by direct read. Live `GET /apis/default/fhir/metadata` → **500**, `{"message":"Unable to create/recreate oauth2 keys ... OPEN_SSL: ... no such process ... no such file"}` — this is the pre-existing `OPENSSL_CONF` environment quirk documented in `docs/rebranding.md` §11.2/§17.2, **not** a branding defect; could not observe the JSON `software.name`/`implementation.description` values live as a result |
| 23 | SMART / OAuth screens | SPC | CARRIED-FORWARD | Not re-run this pass beyond A8 (below) |
| 23b | SMART design-token contract | VC | **PARTIAL — but for a narrower reason than first recorded** *(corrected 2026-08-10, RB-09)* | The dark contract itself **is** delivered, by the Twig template route — see the corrected A8 row. What is genuinely partial: `smartStyleTokens()`/`SmartStyleContract` is a **second, parallel** implementation of the same 12-key contract with zero unit tests and zero production consumers, and separately the shipped SMART templates carry **baked hex literals**, so a Tier-2 tenant overlay changes the web UI but does **not** reach the SMART payload. Both are recorded in `docs/RebrandingBugs.md` RB-09 as a decision (give it a caller, or delete it), not as a defect in the working Twig path |
| 24 | REST / API errors | VC | CARRIED-FORWARD | Not re-checked this pass |
| 25 | Swagger / OpenAPI | VC | CARRIED-FORWARD | Not re-checked this pass |
| 26 | HL7 | SPC | CARRIED-FORWARD | Not re-checked this pass |
| 27 | QRDA | SPC | CARRIED-FORWARD | Not re-checked this pass |
| 28 | Installer | VC | CARRIED-FORWARD | Not re-checked this pass; `coverage-matrix.md` row 28 has its own citation |
| 29 | Upgrade | VC | CARRIED-FORWARD | Not re-checked this pass |
| 30 | Multisite administration | VC | **DONE-VERIFIED** | Live `GET /admin.php` (unauthenticated) → 200, title "Thiqa Site Administration", `<h2>` "Thiqa Multi-Site Administration" |
| 31 | Modules | VC | **DONE-VERIFIED** | `SELECT mod_id, mod_name, mod_active FROM modules WHERE mod_name LIKE '%hiqa%'` → `mod_id=6, mod_name='Thiqa Branding', mod_active=1` |
| 32 | Package metadata | VC | CARRIED-FORWARD | Not re-checked this pass |
| 33 | External links | VC | **DONE-VERIFIED (globals subset)** | `main_menu_logo_link = https://skyeagle.uk/`, `online_support_link = https://skyeagle.uk/support`, `user_manual_link = https://skyeagle.uk/docs` — all resolved off the placeholder domain. `portal_onsite_two_address` is **not** — see Surprises |
| 34 | Upstream network calls | VC | CARRIED-FORWARD | Not re-checked this pass; `coverage-matrix.md` row 34 records registration-cURL removal + telemetry consent-gating with its own citations |
| 35 | Legal / regulatory assets | VC | CARRIED-FORWARD | Not re-checked this pass; D-11 still open regardless (see D-register) |
| 36 | Database configuration | VC | **DONE-VERIFIED (branding-globals subset)** | 11-key globals query below; module registration confirmed |
| 37 | Generated assets | VC | **DONE-VERIFIED** | Same evidence as #10 |
| 38 | Working-tree-only assets | VC | CARRIED-FORWARD | Not re-checked this pass |
| 39 | Error pages | VC | **DONE-VERIFIED (source) / PARTIAL (runtime)** | `templates/error/404.html.twig:3` reads `"Thiqa 404 Error"|xlt` — confirmed by direct read. `MandatoryCoreStringPatchesIsolatedTest`: 23/23 pass, including this exact string. Could **not** confirm live: unauthenticated `GET /interface/login/does-not-exist.php` hits Apache's own stock 404 (file genuinely absent, PHP never runs), not this Twig template — see Surprises |
| 40 | HTTP headers / cookies | VC | **DONE-VERIFIED (session-constant subset)** | See V-08 below — all 6 identity constants confirmed unchanged |
| 41 | Role-independent branding | VC | **DONE-VERIFIED** | See A7 below — zero ACL/role references in module source |
| 42 | Control Plane token ownership / materialisation | VC | **PARTIAL — first materialisation has now run** *(corrected 2026-08-10, RB-11)* | This row previously read `never materialised` / `Revision: 0`; that was true when written and is now stale. `php bin/console thiqa-branding:verify --site=default` → `Status: healthy`, `Revision: 1`, `Materialised at: 2026-08-10T18:50:40+00:00`; `globals.saas_branding_revision = 1`. **Caveat that keeps this PARTIAL rather than DONE:** the run carried an **empty Tier-2 overlay** (`saas_branding_tokens_light`/`_dark` both `''`), so the transaction and revision-bump path were exercised but no tenant token overlay has yet reached a rendered page. AC-6 closes on a non-empty overlay, not on this |
| 43 | Theme surplus disposition | VC | **DONE-VERIFIED** | Same evidence as #10 |
| 44 | Cross-tenant branding acceptance | IDA | **BLOCKED** | `ls sites/` → only `default`. D-6, unresolved |

**Rollup:** of 45 areas, 15 DONE-VERIFIED (some source-only where runtime is environment-blocked), 5
BLOCKED, 2 DEFERRED-WITH-REASON (13, and 19/20 double-counts with BLOCKED), the remainder CARRIED-FORWARD
from the existing `docs/branding/coverage-matrix.md` (not independently re-checked in this pass — that
document has its own, more granular Phase 2/3 evidence per row and should be read alongside this one, not
in place of it).

---

## 2. Acceptance tests A1–A8

| ID | Test | Status | Evidence |
|---|---|---|---|
| A1 | Two tenants, distinct branding, no cross-render | **BLOCKED** | D-6 — `ls sites/` shows only `default`. Not attempted |
| A2 | Cache keys/revisions prevent cross-tenant bleed | **BLOCKED** | D-6, same reason. Not attempted |
| A3 | Invalid token/CSS payloads rejected | **PASS** | `phpunit-isolated.xml tests\Tests\Isolated\Modules\ThiqaBranding\Token` → **393/393 tests, 1067 assertions, OK** |
| A4 | No tenant-uploaded CSS/JS executes | **PASS** | `phpunit-isolated.xml tests\Tests\Isolated\PHPStan\ThiqaBranding` → **54/54 tests, 80 assertions, OK**. All four guardrail rule files present and tested: `ForbiddenBrandingHttpClientRule.php`, `ForbiddenBrandingSiteConfigRule.php`, `ForbiddenBrandingTwigPathRule.php`, `ForbiddenBrandingPlaceholderDomainRule.php` |
| A5 | Statement/PDF renders with the Thiqa practice logo | **BLOCKED** | D-7 — needs ≥1 patient. Not attempted (read-only DB constraint in this session) |
| A6 | Portal post-authentication branding | **BLOCKED** | D-7 + CR-4 — needs a portal patient credential. Not attempted |
| A7 | Reception/Accountant smoke test | **PASS (source-proven, re-confirmed)** | `grep -rn "ACL\|role" interface/modules/custom_modules/oe-module-thiqa-branding/src` → **zero matches**. The module's branding-render path has no role/ACL conditioning anywhere in source, so the "role-agnostic" reasoning holds without needing a live role-based test |
| A8 | SMART dark tokens returned for a dark theme | **MECHANISM VERIFIED (source) / BLOCKED (live, environment)** *(corrected 2026-08-10, RB-09)* | **The original PARTIAL verdict reasoned from the wrong delivery path.** It concluded "nothing returns dark tokens" because `smartStyleTokens()` has no caller — but R-SMART-DARK is delivered by the **Twig template route**, not by that method. `SMARTAuthorizationController::smartAppStyles()` composes `/api/smart/smart-<coreTheme>.json.twig` and dispatches `TemplatePageEvent` unnamed via `renderTwigJson():359`; `TwigOverrideListener::onTemplatePage()` is registered on `TemplatePageEvent::class` (`Bootstrap.php:125`), matches `oauth2/authorize/smart-style`, resolves the variant from `css_header` and rewrites to `@oe-module-thiqa-branding/api/smart/smart-style_dark.json.twig`, which exists and carries dark values (`color_background: #0B1220`, `color_text: #F5F6F8`). Live observation is blocked on this host only: `GET /oauth2/default/smart-style-url` → **500**, `Unable to create/recreate oauth2 keys … OPEN_SSL: no such file` — the pre-existing `OPENSSL_CONF` quirk (`docs/rebranding.md` §11.2/§17.2), not a branding defect. **The real residual finding is narrower and still open:** `SmartStyleContract`/`smartStyleTokens()` is an orphaned parallel implementation with no caller and no test, and because the SMART templates carry baked hex literals a Tier-2 tenant overlay does not reach the SMART contract. See `docs/RebrandingBugs.md` RB-09 |

---

## 3. V-01 through V-10

| ID | Check | Status | Evidence |
|---|---|---|---|
| V-01 | Zero outbound network calls / zero added DB queries from branding render-path code | **VERIFIED (source + static guardrail)** | `BrandingService.php` docblock (lines 40-42) states the guarantee and cites `ForbiddenBrandingHttpClientRule`; that rule's tests pass (A4, above). `sqlQuery`/`sqlStatement`/`QueryUtils::` appear only in `Materialisation/QueryUtilsBrandingGlobalsWriter.php` and `Console/ApplyProfileCommand.php` — the writer/CLI tier, not the render path. No live network-trace instrumentation was run (out of scope for this pass); this is source + test evidence, not an instrumented page-load capture |
| V-02 | Materialiser run twice → idempotent; `globals` overwritten by next sync, never authoritative | **VERIFIED (isolated tests) / NOT LIVE-EXERCISED** | `Materialisation` isolated suite: 50/51 pass on first full-suite run, 1 timeout (`MaterialiserKillRecoveryTest`, 25s budget exceeded during a `573`-test run of the whole ThiqaBranding tree); re-ran that single test alone → **1/1 pass in 9.4s**. Attributed to native-host I/O contention (this machine's documented Google-Drive-mount slowness), not a functional defect — see Surprises. No live tenant has ever been materialised even once (see #42/V-03), so idempotence has not been exercised outside the test double |
| V-03 | CP unreachable → renders last-good; `kill -9` mid-materialisation → revision n-1 intact | **VERIFIED (isolated test) / NOT LIVE-EXERCISED** *(updated 2026-08-10, RB-11)* | `MaterialiserKillRecoveryTest` passes in isolation (see V-02). Live: revision 1 now exists, so a revision n-1 to protect exists for the first time — but no kill test has been run against real tenant state, and no Control Plane exists to be unreachable from (D-5). The mechanism is tested; the live behaviour remains unexercised |
| V-04 | `public/themes/` has only the approved set; forced `style_solar.css` global falls back to `style_light.css` | **PARTIALLY VERIFIED** | The "only the approved set" half is confirmed (#10/#43 above, direct `ls` + `webpack.themes.js` read). The forced-fallback behaviour itself was **not exercised** — would require setting `css_header = style_solar.css` in the live `globals` table and re-rendering, which this pass avoided as a write against shared live state per the read-only-DB constraint |
| V-05 | All 33 WCAG pairs recomputed by `ContrastCalculator`; zero FAIL after D-1 | **VERIFIED** | `Accessibility` isolated suite: **110/110 tests, 264 assertions, OK**. D-1 (the `light.link.default`/`light.link.hover` correction) is recorded RESOLVED in the plan and this suite is consistent with that |
| V-06 | `brand/manifests/SHA256SUMS` verifies 117/117 at release time | **VERIFIED, but count differs from the documented figure** | The manifest currently has **123 entries**, not 117. Full verification (all 123, not a sample): **0 missing, 0 mismatch** — every file present and byte-identical to its recorded hash (Python `hashlib` re-check of the entire file). A 10-entry manual sample was independently cross-checked with PowerShell `Get-FileHash` beforehand, also 10/10 match. The "117" figure in `docs/RebrandingPlan.md` V-06 and `docs/rebranding.md` §21 is **stale** relative to the manifest actually committed on this branch (git history: `d9757fc55` re-issued/expanded it after `a12e63471`) — treat 123/123 as the current source of truth and flag the doc for a number update, not a defect |
| V-07 | Arabic session serves `rtl_style_light.css` **and** `rtl_compact_style_light.css` | **PARTIALLY VERIFIED (filesystem only)** | Both files exist on disk in `public/themes/` (confirmed by direct listing). Did **not** exercise a live Arabic-language session end-to-end to confirm which one(s) an actual RTL request serves — that needs a language-switched authenticated session, out of scope for this pass |
| V-08 | `SessionUtil` identity constants unchanged; `sites/*/config.php` unreferenced by branding code | **VERIFIED** | `src/Common/Session/SessionUtil.php`: `CORE_SESSION_ID = "OpenEMR"`, `OAUTH_SESSION_ID = 'authserverOpenEMR'`, `API_SESSION_ID = 'apiOpenEMR'`, `PORTAL_SESSION_ID = 'PortalOpenEMR'`, `SETUP_SESSION_ID = 'setupOpenEMR'`, `APP_COOKIE_NAME = 'App'` — all 6 match the required frozen values exactly. `grep "sites/.*config.php|SITE_CONFIG"` against the module's `src/` → zero matches |
| V-09 | Rebase dry-run against upstream `master` covers every file in the authoritative patch-record inventory and classifies conflicts inside and outside that set | **VERIFIED FOR CURRENT 33-FILE INVENTORY (2026-08-24)** | `git merge-tree --write-tree HEAD upstream/master` against `upstream/master` `6cb9c0b91728190f30e09c03c026c827e9430579` completed in 3.398 s with true exit code **1** and **47 conflict records**. Exit 1 is expected evidence of conflicts, not a PASS/fail shortcut. Of the 33 recorded core files, only `src/Services/EncounterService.php` (PR-14, a non-branding defect fix) conflicts. None of the eight files added to the inventory by PR-23…PR-28 conflicts. The other 46 conflict records are outside the authoritative patch inventory and remain ordinary upstream-drift/release-integration work. Working tree was not mutated. |
| V-10 | No new PHPStan baseline entries; module patch coverage ≥80% | **PARTIALLY VERIFIED** | `git diff --numstat <pre-branding-commit> HEAD -- .phpstan/baseline/` → 3 files touched, all **shrank** (`deadCode.unreachable.php` −5, `openemr.forbiddenCurlFunction.php` −25, `empty.notAllowed.php` a `count` decremented 8→7). Zero baseline entries grew — this half is verified. Patch-coverage percentage: **could not be measured** — this host has neither Xdebug nor PCOV loaded (`php -m` confirms), so no coverage report can be generated natively; would need the Docker/CI toolchain |

---

## 4. Blocking dependency register (D-1 … D-16)

Reproduced from `docs/RebrandingPlan.md` §6.5/§6.5.1 with current status. **I did not resolve any of these
this session** — they are legal/business/infra decisions or provisioning actions outside a verification
pass's scope. Where I found direct evidence bearing on a D-item's status (open vs. closed), it's cited;
otherwise the plan's own recorded status is carried forward unchanged.

| ID | Dependency | Status | This pass's finding |
|---|---|---|---|
| D-1 | `light.link.default` WCAG failure | **RESOLVED** (per plan, 2026-08-09) | Consistent with V-05's 110/110 pass; not independently re-derived from raw contrast ratios this pass |
| D-2 | `thiqa.example` placeholder URLs → `skyeagle.uk` | **RESOLVED** (per plan) | Confirmed live: `main_menu_logo_link`, `online_support_link`, `user_manual_link` all resolve to `skyeagle.uk`. **But see #33/Surprises: `portal_onsite_two_address` is still the upstream placeholder `https://your_web_site.com/openemr/portal`** — D-2's closure note itself says "Registration endpoint still depends on D-10," and this portal address looks like a second uncaptured instance of the same placeholder class, not yet swept |
| D-3 | Legal product-name registration (EN `Thiqa` / AR `ثقة`); HL7 MSH-3 / QRDA clearance | **OPEN — genuinely unresolved** | No evidence found of legal clearance; this is not an engineering artefact I can verify or close |
| D-4 | Native-Arabic linguistic proofreading | **OPEN** | Confirmed still open — no proofreading evidence found |
| D-5 | Control Plane (`MVP-014`) not yet built | **OPEN** | No CP exists to have built against. *(The supporting evidence originally cited here — `verify` reporting `never materialised` — is stale as of 2026-08-10; revision 1 was materialised locally by the CLI. That does not change D-5: a CLI-driven local materialisation is not a Control Plane.)* |
| D-6 | Second provisioned tenant (G-10b) | **OPEN — directly confirmed** | `ls sites/` → only `default` |
| D-7 | ≥1 patient + portal patient credential | **OPEN — not independently probed** | Did not run a patient-count query (would require a broader read against clinical data outside this task's DB scope); treated as open per the plan, consistent with D-6/D-5 findings that nothing beyond `default`'s base install exists |
| D-8 | Writable execution-denied volume | **RE-OPENED 2026-08-10 (RB-04); VERIFIED STILL OPEN 2026-08-24 after S1-P0-09** | The endpoint is the linked browser route, but `TokenCssWriter` remains wired into `BrandingMaterialiser` and writes `…/oe-module-thiqa-branding/public/branding/<site>/tokens-{light,dark}.css`. Current isolated execution proves an applied run commits both stylesheets (32 materialiser/writer tests, 146 assertions); source still stages both variants even for an empty overlay. The web server can fetch the files directly, but application delivery never links them; `FilesystemStylesheetProbe` is their only application reader. The deployed image therefore still needs this path writable. Closing D-8 requires removing/making the writer opt-in and repeating the runtime chain — see RB-04 |
| D-9 | Q25-compliant Arabic PDF fonts, engine-configured | **OPEN — Owner accepted the limitation through pilot (EV-RB14 Option C); no runtime capability** | `brand/typography/fonts/pdf/Amiri-Regular.ttf` and `Amiri-Bold.ttf` exist and `tools/branding/install-assets.php` copies them into the release asset tree, but that is asset delivery, not PDF-engine registration. `src/Pdf/Config_Mpdf.php` still defaults to `dejavusans` and contains no Amiri/Noto registration; dompdf is likewise unwired. The preserved mPDF 8.3.1 probe failed on four tested Arabic faces, including both `Q25` choices, with `GPOS Lookup Type 5, Format 3 not supported`; disabling OTL produced unshaped Arabic. Do not describe the delivered TTFs as usable PDF support. Revisit only on the Owner's recorded customer-contract trigger — see RB-14 and `docs/evidence/EV-RB14-mpdf-gpos.md` |
| D-10 | Repoint/disable product registration endpoint | **OPEN** | Not independently re-checked; `ProductRegistrationService.php`'s cURL removal (per `coverage-matrix.md` row 34) is a related but distinct fact — D-10 itself (a decision, not code) is still recorded open |
| D-11 | Counsel review of `acknowledge_license_cert.html` | **OPEN — genuinely unresolved** | The page is blocked at the Apache layer (403, confirmed live) and the globals-level links are suppressed, but the file itself still exists unmodified (by design, per `CLAUDE.local.md` §10) and counsel has not reviewed it. Access-suppression is not legal clearance |
| D-12 | Ratification of `border.strong`/dark `surface.input` derivations | **CLOSED 2026-08-19** | `docs/branding-production/16-conflict-resolutions.md` §9 claimed this ratified 2026-08-09; independently re-checked this session — `brand/tokens/thiqa-tokens.json` contains the exact specified values (`borderStrong` `#4B5266`/`#AEB5C4`, `surfaceInput` `#FFFFFF`/`#121A2E`). This row's own "OPEN, not independently re-checked" was itself the stale claim |
| D-13 | Sign-off on CR-3/CR-9 | **RESOLVED** (per plan) | Consistent with the theme-filename evidence (#10 above: `style_light.css`/`style_dark.css` filenames retained, matching CR-9) |
| D-14 | Ratify `Q38` template-delivery interpretation | **CLOSED 2026-08-19** | `16-conflict-resolutions.md` §2 claimed the namespaced-Twig-path pattern adopted 2026-08-09; independently re-checked — `TwigOverrideListener.php` implements exactly the stated prohibition (`prependPath()`/unnamespaced `addPath()` forbidden for SaaS modules), matching the resolution's §2 spec |
| D-15 | Admin theme selector literal label ("Saudi Light/Dark" vs "Light/Dark") | **CLOSED 2026-08-19 — Owner ruling, direct conversation** | `16-conflict-resolutions.md` §8 claimed plain "Light"/"Dark" was accepted — **this was independently checked and found wrong**: `ThemeVariant.php:46-47`'s `label()` method literally returns `'Saudi Light'`/`'Saudi Dark'`, contradicting the resolution doc. Surfaced to the Owner as a genuine open decision rather than trusting the resolution doc's false claim; **Owner ruled 2026-08-19: keep "Saudi Light"/"Saudi Dark" as shipped, no code change** |
| D-16 | Canonical cache-key spec + test matrix | **CLOSED 2026-08-19** | `16-conflict-resolutions.md` §7 claimed the canonical `?t=`/`?v=`+`&rev=` spec defined 2026-08-09; independently re-checked — `saas_branding_revision` global and `branding-tokens.php`'s `?rev=` usage confirmed present in `BrandingGlobalKey.php`/`BrandingService.php`/`Bootstrap.php`, consistent with the spec |

**Plainly stated:** D-3, D-4, D-5, D-6, D-7, D-9, D-10, D-11 remain open. Of
these, this session directly confirmed D-5, D-6, and D-9 are open (not just "still recorded as open" —
actually re-derived from live command output and source grep this pass). None of the "blocking for
release" items (D-3, D-9, D-10, D-11) were resolved or could be resolved by engineering work in this
session; they require legal, product, and business decisions outside this pass's scope.

**UPDATED 2026-08-19:** D-12, D-14, D-16 are now CLOSED (independently re-verified against real code/config,
correcting this table's own stale "not independently re-checked" status — see their rows above). D-15 is
also CLOSED, but only after the resolution document's own claim was found to be **factually wrong**
(code showed the opposite of what it claimed) and a genuine Owner decision was obtained directly — see
D-15's row for the full trace.

---

## 5. Surprises / things that did not behave as expected

1. **`GET /public/images/favicon.ico` returned 200, not the "historically broken" result the task
   briefing anticipated.** The file exists on disk and is byte-identical (SHA-256
   `4026d768524ae626c087713e71386e6ae3a5d3e3b457d1d36a5f6524e46d7e90`) to the canonical
   `public/images/logos/core/favicon/favicon.ico`. `git log` shows it was installed by the brand-kit
   commit `d9757fc55`. This is better than expected, not a regression — flagging only because the task
   briefing described it as a known-broken path.

2. **The generic-URL 404 test does not exercise the branded error template at all.** Hitting an
   arbitrary missing path (`/interface/login/does-not-exist.php`) returns Apache's own stock
   `404 Not Found` page (`<title>404 Not Found</title>`, no OpenEMR/Thiqa branding either way) because
   the file genuinely doesn't exist and PHP never runs. The actual `templates/error/404.html.twig`
   (which does correctly say `"Thiqa 404 Error"|xlt`, confirmed by direct read and by
   `MandatoryCoreStringPatchesIsolatedTest` passing 23/23) is wired only into specific legacy code paths —
   `ccr/display.php` (deprecated CCD/CCR preview) and the Zend Carecoordination `EncountermanagerController`
   — both of which need an authenticated session and a specific document state to reach the 404 branch.
   An unauthenticated attempt against `ccr/display.php` with a bogus `doc_id` returned an empty-bodied
   `400` (0 bytes, `Content-Length: 0`) rather than rendering `400.html.twig`'s "Thiqa 400 Error" text,
   which is itself unexplained and worth a follow-up look — I did not chase it further given time budget.
   **Net effect: the branded error-page string is source-proven and unit-test-proven, but not
   runtime-proven against a real unauthenticated HTTP request in this pass**, contrary to what the task
   briefing assumed would be a simple live check.

3. **`portal_onsite_two_address` is still the literal upstream placeholder** —
   `https://your_web_site.com/openemr/portal` — even though the sibling URLs (`main_menu_logo_link`,
   `online_support_link`, `user_manual_link`) were all correctly repointed to `skyeagle.uk`. This was
   flagged as an expected, known gap in the task briefing (Q12 tenant provisioning not yet run), so it is
   not a surprise in the sense of being new information, but it's worth restating plainly: it is a real,
   currently-live placeholder value, not a hypothetical one.

4. **`brand/manifests/SHA256SUMS` has 123 entries, not the 117 the plan documents in V-06/§21.** All 123
   verify correctly (0 missing, 0 mismatch, full re-hash — not just the 10-entry sample), so this is a
   stale number in the documentation rather than an integrity problem, but it means "117/117" as a release
   gate criterion needs updating before anyone tries to literally match that count.

5. **The rebase dry-run (V-09) found real conflicts, but not where the plan predicted.** None of the 6
   recorded WS-C-patched core files conflict against current `upstream/master` — which is good news the
   plan didn't anticipate this precisely. But 10 other files/areas do conflict, mostly unrelated general
   upstream drift (the `oe-module-faxsms` module has been substantially rewritten upstream; PHPStan
   baselines and `composer.lock` have moved on). `composer.json` conflicting is branding-adjacent (the
   branding tooling added dependencies) and worth a maintainer's attention before the next rebase.

6. **`smartStyleTokens()`/`SmartStyleContract` (A8, area 23b) has no direct test and no found production
   consumer.** The underlying token derivation it depends on (`BrandingService::tokens()`) is well tested
   for light-vs-dark difference, and the class's own logic is simple and visibly variant-dependent
   (`backdrop()` is a two-armed `match`), but as a whole the SMART-facing wrapper is unexercised. This
   reads as a real gap in acceptance-test coverage for A8, not a functional defect — worth a follow-up
   unit test (`SmartStyleContractTest` doesn't exist yet) before anyone marks A8 formally PASS.

7. **One flaky-looking test failure turned out to be host resource contention, not a defect.**
   `MaterialiserKillRecoveryTest` timed out (25s budget) when run as part of the full 51-test
   `Materialisation` suite, but passed cleanly (9.4s) run alone immediately after. Given this machine's
   documented Google-Drive-mount I/O characteristics (`CLAUDE.local.md` §8), this reads as environmental,
   not a real kill-recovery regression — but it's exactly the kind of thing that's worth re-running once
   more in CI/Docker before fully trusting it, since I only reproduced the "it passes alone" half of that
   theory, not a repeated failure-under-load to nail down the mechanism.

8. ~~**Live materialisation has genuinely never run, even once, against the only existing tenant.**~~
   **SUPERSEDED 2026-08-10 (`docs/RebrandingBugs.md` RB-11).** This was accurate when written. A
   materialisation has since run: `thiqa-branding:verify --site=default` now reports `Status: healthy`,
   `Revision: 1`, `Materialised at: 2026-08-10T18:50:40+00:00`, corroborated independently by
   `SELECT gl_value FROM globals WHERE gl_name = 'saas_branding_revision'` → `1`.

   **Read the caveat before upgrading any acceptance criterion on the strength of it.** The run carried an
   **empty Tier-2 overlay** — `saas_branding_tokens_light` and `saas_branding_tokens_dark` are both `''` —
   so what executed is the transaction, the atomic file staging and the revision bump. **No tenant token
   overlay has yet reached a rendered page.** `MVP-010` AC-6 closes on a materialisation with a non-empty
   overlay whose `<link>` is then observable in the HTML, and that has still not happened.

   The materialisation also produced the two unlinked `tokens-{light,dark}.css` files that re-opened
   dependency **D-8** — see RB-04. S1-P0-09 later proved that a non-empty overlay reaches a real browser
   component through the PHP endpoint, but did not remove this simultaneous static write. So the consumer
   repair is genuinely good news and D-8 remains genuinely open; both facts belong in the record.

9. **A finding this pass did not look for, added by the later audit: the brand manifest release gate was
   red.** `brand/manifests/SHA256SUMS` covers 16 `docs/branding-production/*.md` documents as well as the
   binary assets, and an uncommitted documentation edit had broken one entry. V-06 above verified the
   manifest and recorded "0 missing, 0 mismatch" — true when run, stale by the time the documentation was
   edited. It is now checked by a script (`tools/branding/verify-brand-manifest.php`) rather than by hand.
   See `docs/RebrandingBugs.md` RB-25.
