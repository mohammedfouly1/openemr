# Thiqa Rebranding — Phase 4 Verification: BRAND-001 … BRAND-136

Verification pass against the working tree of `feat/thiqa-branding-foundation` (all Phase 2/3 work is
**committed** — `a1c22b6a1` … `c6c3f9e6e`, verified via `git log`; this corrects an assumption in the
audit brief that the work was still uncommitted, see "Corrections to the audit brief" below).

Authoritative action mapping: `docs/rebranding.md` §16.2 (136 rows, BRAND-001…136, one action each).
Descriptions/consumers: `docs/rebranding.md` §9 (001–120) and §11.3 (121–136).

**Status legend:** DONE (artefact confirmed) · PARTIAL (some but not all done) · NOT DONE (no evidence
of the action) · BLOCKED (external dependency, D-register cited).

---

## SET-CONFIG (37 items)

Mechanism for nearly all rows: `interface/modules/custom_modules/oe-module-thiqa-branding/config/branding-profile.json`
applied via `thiqa-branding:apply-profile` into the `globals` table; consumed by unmodified core code that
already read these keys. Live values queried directly:

```
mariadb -u root --host=127.0.0.1 --port=3306 openemr -e "SELECT gl_name, gl_value FROM globals WHERE gl_name IN (...)"
```

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 001 | SET-CONFIG | DONE | `openemr_name` global, applied by profile | `globals.openemr_name = 'Thiqa'` | DB query 2026-08-10; consumed unmodified by `admin.php`, FHIR, HL7, QRDA, prescription email |
| 002 | SET-CONFIG | DONE | Login `<title>` derives from `openemr_name` | `base.html.twig:24` (unmodified) | Value flows from 001; mechanism unmodified |
| 003 | SET-CONFIG | DONE | Main app `<title>` derives from `openemr_name` | `main.php:102` (unmodified) | Value flows from 001 |
| 004 | SET-CONFIG | DONE | JS window-title base derives from `openemr_name` | `main.php:153` (unmodified) | Value flows from 001 |
| 013 | SET-CONFIG | DONE | `window_title_add_patient_name` unchanged from safe default | `globals.window_title_add_patient_name = '0'` | DB query; profile row asserts `0` deliberately (no patient names in titles) |
| 042 | SET-CONFIG | DONE | `login_tagline_text` | `globals.login_tagline_text = 'Clinical confidence, connected care.'` | DB query |
| 043 | SET-CONFIG | DONE | `show_tagline_on_login` | `globals.show_tagline_on_login = '1'` | DB query |
| 044 | SET-CONFIG | DONE | `login_page_layout` | `globals.login_page_layout = 'login/layouts/vertical_band.html.twig'` | DB query; profile note explains the Twig-path form is required (bare label renders no page) |
| 045 | SET-CONFIG | DONE | `primary_logo_width` / `secondary_logo_width` | `globals: w-50 / w-50` | DB query — unchanged default, asserted intentionally |
| 046 | SET-CONFIG | DONE | `logo_position` | `globals.logo_position = 'flex-column'` | DB query |
| 047 | SET-CONFIG | DONE | `show_primary_logo`/`extra_logo_login`/`secondary_logo_position` | `globals: 1 / 0 / second` | DB query |
| 048 | SET-CONFIG | DONE | `show_labels_on_login_form`/`show_label_login` | `globals: 1 / 0` | DB query |
| 049 | SET-CONFIG | DONE | `tiny_logo_1`/`tiny_logo_2` | `globals: 0 / 0` | DB query — unchanged default |
| 050 | SET-CONFIG | DONE | `display_acknowledgements_on_login` | `globals.display_acknowledgements_on_login = '0'` | DB query; matches `CLAUDE.local.md` §10 (superseded from Part-1's `1` per `16-conflict-resolutions.md` §12.1, pending D-11) |
| 054 | SET-CONFIG | DONE | `display_main_menu_logo` | `globals.display_main_menu_logo = '1'` | DB query — unchanged default |
| 055 | SET-CONFIG | DONE | `main_menu_logo_link` | `globals.main_menu_logo_link = 'https://skyeagle.uk/'` | DB query |
| 056 | SET-CONFIG | DONE | `main_menu_logo_title` | `globals.main_menu_logo_title = 'Thiqa Health Information System'` | DB query — no longer empty (was `''`, K-01 defect) |
| 057 | SET-CONFIG | DONE | `online_support_link` | `globals.online_support_link = 'https://skyeagle.uk/support'` | DB query — now HTTPS |
| 058 | SET-CONFIG | DONE | `user_manual_link` | `globals.user_manual_link = 'https://skyeagle.uk/docs'` | DB query |
| 059 | SET-CONFIG | DONE | `support_phone_number` deliberately left blank | `globals.support_phone_number = ''` | DB query; `branding-profile.json` "omitted" list explains: tenant data, writing a blank would clobber a configured number |
| 062 | SET-CONFIG | DONE | `display_acknowledgements` | `globals.display_acknowledgements = '0'` | DB query |
| 064 | SET-CONFIG | DONE | `portal_onsite_two_enable` unchanged from upstream default | `globals.portal_onsite_two_enable = '0'` | DB query; profile "omitted" list: enablement is a provisioning decision (CR-4), not branding |
| 065 | SET-CONFIG | DONE | `portal_css_header` | `globals.portal_css_header = 'style_light.css'` | DB query — filename retained per CR-3, content is now the Thiqa build |
| 066 | SET-CONFIG | DONE | `show_portal_primary_logo` | `globals.show_portal_primary_logo = '1'` | DB query |
| 067 | SET-CONFIG | DONE | `extra_portal_logo_login` | `globals.extra_portal_logo_login = '0'` | DB query |
| 068 | SET-CONFIG | DONE | `secondary_portal_logo_position` | `globals.secondary_portal_logo_position = 'second'` | DB query |
| 069 | SET-CONFIG | DONE | `portal_primary_menu_logo_height` | `globals.portal_primary_menu_logo_height = '30'` | DB query |
| 070 | SET-CONFIG | **BLOCKED** | Tenant-specific portal address; deliberately excluded from the product-level profile | `globals.portal_onsite_two_address = 'https://your_web_site.com/openemr/portal'` (unchanged placeholder) | DB query 2026-08-10 — **confirms the audit brief's claim was correct.** `branding-profile.json` "omitted" list: "Tenant-specific endpoint under the Q12 subdomain-per-tenant scheme. Provisioning owns it." Blocked on **D-6** (second tenant) / provisioning |
| 071 | SET-CONFIG | DONE | Portal login legend derives from `openemr_name` | `portal/index.php:623` (unmodified) | Value flows from 001, code path read directly |
| 072 | SET-CONFIG | DONE | Portal page title derives from `openemr_name` | `portal/home.php:375` (unmodified) | Value flows from 001 |
| 074 | SET-CONFIG | DONE | `css_header` | `globals.css_header = 'style_light.css'` | DB query — filename retained (CR-3/CR-9), content is Thiqa Light |
| 075 | SET-CONFIG | DONE | `theme_tabs_layout` unchanged from upstream default | `globals.theme_tabs_layout = 'tabs_style_full.css'` | DB query |
| 096 | SET-CONFIG | DONE | HL7 MSH-3 sender derives from `openemr_name` | `non_reported.php:162` (unmodified) | Value flows from 001 |
| 097 | SET-CONFIG | DONE | QRDA Cat III org name derives from `openemr_name` | `ExportCat3Service.php:430`, `QrdaReportService.php:218` (unmodified) | Value flows from 001 |
| 107 | SET-CONFIG | DONE | Prescription email subject/body derives from `openemr_name` | `C_Prescription.class.php:1016,1122,1132` (unmodified) | Value flows from 001 |
| 109 | SET-CONFIG | DONE | `statement_logo` filename unchanged; file contents replaced | `globals.statement_logo = 'practice_logo.gif'`; `sites/default/images/practice_logo.gif` | DB query + `cmp` against `brand/logos/legacy/practice_logo.gif` — byte-identical |
| 110 | SET-CONFIG | **BLOCKED** | Facility name is tenant data, not a global; deliberately excluded from the profile | `facility.name = 'Your Clinic Name Here'` (unchanged) | `SELECT id,name FROM facility` 2026-08-10; `branding-profile.json` "omitted" list confirms deliberate exclusion. Blocked on **D-6/D-7** (tenant provisioning) |

## REPLACE-ASSET (20 items)

Mechanism for all rows: `tools/branding/install-assets.php`, SHA-256-manifested from `brand/`, committed
`d9757fc55`. Verified by comparing on-disk byte sizes against the Group-1 discovery baseline (`docs/rebranding.md` §9.2/9.3) and, for git-tracked files, confirming the replacing commit touched the path.

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 014 | REPLACE-ASSET | DONE | Asset installer | `public/images/logos/core/login/primary/logo.png` | 54,480 B (was 35,917 B) |
| 015 | REPLACE-ASSET | DONE | Asset installer | `.../core/login/secondary/logo.png` | 9,532 B (was 2,245 B) |
| 016 | REPLACE-ASSET | DONE | Asset installer | `.../core/login/small_logo_1/logo.png` | 5,998 B (was 1,708 B) |
| 017 | REPLACE-ASSET | DONE | Asset installer | `.../core/login/small_logo_2/logo.png` | 5,998 B (was 1,795 B) |
| 018 | REPLACE-ASSET | DONE | Asset installer + C2PA strip | `.../core/menu/primary/logo.svg` | 1,972 B (was 1,532 B; post-C2PA-strip per `docs/AuditRebranding.md:1261`) |
| 019 | REPLACE-ASSET | DONE | Asset installer | `.../core/favicon/favicon.ico` | `git log -- public/images/logos/core/favicon/favicon.ico` shows it modified in `d9757fc55` |
| 020 | REPLACE-ASSET | DONE | Asset installer | `.../portal/login/primary/logo.png` | 54,480 B, dup of 014 |
| 021 | REPLACE-ASSET | DONE | Asset installer — **closes a Group-1 gap** | `.../portal/login/secondary/logo.png` | File now exists, 9,532 B (was **absent** at discovery — BRAND-021 was a documented gap) |
| 022 | REPLACE-ASSET | DONE | Asset installer | `.../portal/menu/primary/logo.png` | 27,871 B (was 30,769 B) |
| 025 | REPLACE-ASSET | DONE | Asset installer (legacy dup path) | `public/images/login-logo.png` | 54,480 B |
| 026 | REPLACE-ASSET | DONE | Asset installer (legacy dup path) | `public/images/logo-full-con.png` | 27,871 B |
| 027 | REPLACE-ASSET | DONE | Asset installer (legacy path) | `public/images/menu-logo.png` | 21,624 B (was 18,754 B) |
| 028 | REPLACE-ASSET | DONE | Asset installer (legacy path) | `public/images/favicon-32x32.png` | 1,364 B (was 1,973 B) |
| 029 | REPLACE-ASSET | DONE | Asset installer — **closes a Group-1 gap** | `public/images/favicon.ico` | File now exists, 15,086 B (was **missing → HTTP 404** at discovery); `git log` confirms creation in `d9757fc55` |
| 031 | REPLACE-ASSET | DONE | Asset installer | `sites/default/images/logo_1.png` | 2,884 B (was 357 B) |
| 032 | REPLACE-ASSET | DONE | Asset installer | `sites/default/images/logo_2.png` | 2,884 B (was 395 B); identical to `logo_1.png` by `cmp` |
| 033 | REPLACE-ASSET | DONE | Asset installer — **closes a Group-1 gap** | `sites/default/images/practice_logo.gif` | File now exists (was "not shipped by default" at discovery); `cmp` against `brand/logos/legacy/practice_logo.gif` — byte-identical |
| 108 | REPLACE-ASSET | DONE | Inherits from BRAND-014 (`LogoService`) | Telehealth invite email logo | Same asset as row 014; no separate file |
| 112 | REPLACE-ASSET | DONE | Inherits from BRAND-109 | Report/receipt logos (`custom_report.php`, `cash_receipt.php`) | Same `practice_logo.gif` as row 109 |
| 133 | REPLACE-ASSET | DONE | Inherits from BRAND-019 (`LogoService`, core favicon slot) | Portal favicon | Same asset as row 019; portal has no separate favicon slot |

## PATCH (18 items)

Independently re-grepped against the working tree (not taken from the prior audit's tables, though the
results agree with `docs/AuditRebranding.md`'s "WS-C independent verification" section).

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 005 | PATCH | DONE | Literal string edit | `admin.php:40` | `<title>Thiqa Site Administration</title>` |
| 006 | PATCH | DONE | Literal string edit | `admin.php:53` | `<h2>Thiqa Multi-Site Administration</h2>` |
| 007 | PATCH | **NOT DONE** | — | `setup.php:145,356` | `grep` still returns `<title>OpenEMR Setup Tool</title>` at both lines 2026-08-10 |
| 008 | PATCH | **NOT DONE** | — | `setup.php:160,452` | `grep` still returns `<a class="navbar-brand" href="#">OpenEMR Setup</a>` at both lines |
| 009 | PATCH | **NOT DONE** | — | `setup.php:522,524,526,976,1530,1747` | Not re-grepped line-by-line but §007/008 in the same file are unpatched; no evidence of any edit to this file |
| 010 | PATCH | **NOT DONE** | — | `sql_patch.php:47,54,106` | `grep` still returns `OpenEMR <?php ... ?> <?php echo xlt('Database Patch'); ?>` |
| 011 | PATCH | **NOT DONE** | — | `sql_upgrade.php:159,414` | `grep` still returns `<title>OpenEMR Database Upgrade</title>` and `xlt("OpenEMR Database Upgrade")` |
| 012 | PATCH | **NOT DONE** | — | `ippf_upgrade.php:104,111` | `grep` still returns `<title>OpenEMR IPPF Upgrade</title>` and `<h2>OpenEMR IPPF Upgrade</h2>` |
| 030 | PATCH | **NOT DONE** | — | `interface/forms/eye_mag/help.php:45`, `eye_mag_functions.php:4033` | `grep` still returns hardcoded `sites/default/images/login_logo.gif` at both call sites (the underlying asset was replaced — smaller GIF, 3,461 B vs 10,112 B — but the hardcoded path itself, the actual PATCH target, is untouched) |
| 053 | PATCH | DONE | Core template now reads a variable (F-02 fix, K-21) | `templates/login/partials/html/primary_logo.html.twig:15,20` | `alt="{{ primaryLogoAlt\|default('')\|attr }}"` and same for secondary — confirmed via direct read; `git diff` shows the file changed |
| 087 | PATCH | DONE | `openemr_name` global read with literal fallback | `FhirMetaDataRestController.php:75` | `$this->globalsBag->getString('openemr_name') ?: 'Thiqa'` |
| 113 | PATCH | DONE | Registration cURL block removed entirely | `ProductRegistrationService.php` | `grep -n "curl_\|reg.open-emr"` → 0 matches; `registerProduct()`/`optInStrategy()` write only to the local `product_registration` table, no HTTP call |
| 119 | PATCH | **NOT DONE** | — | `src/Core/Header.php:95` + template | Plan itself (`RebrandingPlan.md:936`) records this as "Defer unless the duplicate breaks a client" — contradicts its own §16.2 classification as PATCH. No code change found; cosmetic, low-impact |
| 126 | PATCH | DONE | Same mechanism as 087 | `FhirMetaDataRestController.php:78` | `$implementation->setDescription(new FHIRString($productName . " FHIR API"))` |
| 130 | PATCH | DONE | Literal URL edit | `Installer/.../index.phtml:36,117` | Both `href="https://skyeagle.uk/docs/installer"` |
| 134 | PATCH | DONE | Literal string edit | `OAuth2AuthorizationListener.php:108` | `throw new NotFoundHttpException("Thiqa Error: API is disabled")` |
| 135 | PATCH | DONE | Literal string edit | `interface/globals.php:93` | `echo "Thiqa Error: Thiqa is not working since the php openssl module is not installed."` |
| 136 | PATCH | DONE | Literal string edit | `interface/globals.php:100` | `echo "Thiqa Error: Thiqa is not working since the openssl aes-256-cbc cipher is not available."` |

**PATCH summary: 10 of 18 done, 8 not done** (007–012, 030, 119 — all six conditional-patch installer/upgrade
screens, the Eye-Magic hardcode, and the cosmetic duplicate favicon link). These are exactly the items
`docs/rebranding.md` §15.2 labelled "conditional" (operator-only, MVP-optional) — but §16.2 classifies
all of them as unconditional PATCH actions, so by that authoritative mapping they are gaps, not accepted
scope exclusions.

## PRESERVE (15 items)

"Done" = confirmed byte-identical / unmodified, i.e. correctly left alone.

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 038 | PRESERVE | DONE | Untouched by design (regulatory) | `public/images/cms1500.png` | 2,036,937 B — matches discovery byte count exactly |
| 039 | PRESERVE | DONE | Untouched by design (regulatory) | `public/images/ub04.svg` | 146,742 B — matches discovery byte count exactly |
| 040 | PRESERVE | DONE | Untouched by design (trademark); also in installer deny-list | `sites/default/images/visa_mc_disc_credit_card_logos_176x35.gif` | 1,852 B — matches; `tools/branding/install-assets.php` `FILENAMES` deny-list explicitly names this file |
| 063 | PRESERVE | DONE | Untouched file + config/webserver suppression, not deletion | `acknowledge_license_cert.html` | 24,739 B — matches discovery byte count exactly; access is `HTTP 403` via Apache `<Files>` block per `CLAUDE.local.md` §10, not file modification |
| 089 | PRESERVE | DONE | Untouched (locked Q17) | `SessionUtil.php:86` | `const PORTAL_SESSION_ID = 'PortalOpenEMR'` |
| 090 | PRESERVE | DONE | Untouched (locked Q17) | `SessionUtil.php:82` | `const OAUTH_SESSION_ID = 'authserverOpenEMR'` |
| 091 | PRESERVE | DONE | Untouched (locked Q17) | `SessionUtil.php:84` | `const API_SESSION_ID = 'apiOpenEMR'` |
| 092 | PRESERVE | DONE | Untouched (locked Q17) | `SessionUtil.php:88` | `const SETUP_SESSION_ID = 'setupOpenEMR'` |
| 093 | PRESERVE | DONE | Untouched (locked Q17) | (same file, cross-ref of 089–092) | Same read |
| 114 | PRESERVE | DONE | Untouched (legal — DO-NOT-TOUCH) | ~2,645 `@link`/`@license` docblocks | Spot-checked `interface/login/login.php` header — unmodified `@link https://www.open-emr.org` |
| 115 | PRESERVE | DONE | Untouched (legal — DO-NOT-TOUCH) | 530 `@author …@opencoreemr.com` | Not individually re-enumerated; no file outside the 7-file WS-C patch set was touched (`git diff --stat` against pre-branding baseline) |
| 116 | PRESERVE | DONE | Untouched (legal — DO-NOT-TOUCH) | `OpenEMR\` PHP namespace, 3,611 files | Every patched file confirmed still under `namespace OpenEMR\...` |
| 117 | PRESERVE | DONE | Untouched (legal — DO-NOT-TOUCH) | GPL-3.0 headers | Spot-checked `login.php` — full header intact |
| 118 | PRESERVE | DONE | Untouched (legal — DO-NOT-TOUCH w/o counsel) | ONC certification claims in `acknowledge_license_cert.html` | Same file, same 24,739 B — content unmodified; D-11 (counsel review) still open, blocks the page's ultimate disposition, not this PRESERVE action |
| 131 | PRESERVE | DONE | Untouched (locked Q17) | `SessionUtil.php:81` | `const CORE_SESSION_ID = "OpenEMR"` — session cookie name unchanged |

## NO-ACTION (11 items)

"Done" = confirmed the non-defect still holds and nothing regressed.

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 023 | NO-ACTION | DONE | Internal accessor, unchanged | `TwigExtension.php` | `git diff --stat` vs pre-branding baseline shows this file untouched |
| 024 | NO-ACTION | DONE | Internal hook, unchanged | `LogoService.php` | Same `git diff --stat` — untouched |
| 041 | NO-ACTION | DONE | Third-party npm assets, unrelated | `public/assets/**` (368 files) | Out of scope by design; not part of any branding commit |
| 051 | NO-ACTION | DONE | Overridable mechanism, unchanged | 25 login Twig partials | Mechanism (class-list override) unchanged; content changes are covered under BRAND-053/PATCH row above |
| 052 | NO-ACTION | DONE | Dead include, confirmed still dead | `vertical_box.html.twig:32` → `tiny_logo.html.twig` | Not re-wired; correctly documented as "not a defect" |
| 073 | NO-ACTION | DONE | Trap dirs, still never read | `sites/default/images/logos/portal/{primary,secondary}` READMEs | Unchanged; real assets resolve through `LogoService`, not these paths |
| 084 | NO-ACTION | DONE | `file_exists()` gate, still correct | `interface/globals.php:476` | `css_header = 'style_light.css'`, an existing file; `style_default.css` remains absent and still correctly gated |
| 088 | NO-ACTION | DONE | Confirmed still absent | `composer.json` | `grep '"version"'` → no top-level `version` key; FHIR `software.version` still not emitted |
| 099 | NO-ACTION | DONE | Confirmed still absent | `src/Core/Header.php` | No `theme-color`, `application-name`, `og:*`, or manifest reference found |
| 100 | NO-ACTION | DONE | Infra config, unchanged | `X-Powered-By`/`Server` headers | Not a branding-module concern; unchanged |
| 106 | NO-ACTION | DONE | Confirmed unchanged | `templates/emails/**` (12 templates) | `git diff --stat` vs pre-branding baseline — zero changes in this directory |

## BUILD-SHARED-THEME (9 items)

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 076 | BUILD-SHARED-THEME | DONE | Webpack entry map restricted to Q77 set | `public/themes/*.css` | 17 compiled CSS files present: `style_{light,dark}`, `compact_style_{light,dark}`, `rtl_style_{light,dark}`, `rtl_compact_style_{light,dark}`, `style_pdf`, `rtl_style_pdf`, `tabs_style_{full,compact}`, `rtl_tabs_style_{full,compact}` + 3 non-theme shells (`style.css`, `directional.css`, `ajax_calendar_ie.css`, `jquery.autocomplete.css`); zero `solar`/`manila`/`cobalt_blue`/`forest_green` files |
| 077 | BUILD-SHARED-THEME | DONE | New Thiqa SCSS source tree | `interface/themes/thiqa/*.scss` (7 files), `interface/themes/oe-styles/style_thiqa_{light,dark}.scss` | Directory listing confirms both present and referenced by `webpack.themes.js:160-173` |
| 081 | BUILD-SHARED-THEME | DONE | Unchanged by design (CR-9: filenames retained) | `config/config.yaml` | No `thiqa`-specific bindings needed since `style_light.css`/`style_dark.css` names are unchanged; `grep` confirms file untouched |
| 082 | BUILD-SHARED-THEME | DONE | Unchanged filename-swap mechanism, now produces Thiqa RTL CSS | `webpack.themes.js:168-173` | `rtl_style_light`/`rtl_style_dark` entries present, compiled to `public/themes/rtl_style_{light,dark}.css` |
| 083 | BUILD-SHARED-THEME | DONE | Unchanged filesystem-scan mechanism | `edit_globals.php:714-731` | Not independently re-run against the admin UI this pass, but the underlying gate (`file_exists()` over `public/themes/`) is unchanged code and the file set now only contains the 2 Saudi variants, so the scan can only surface those |
| 085 | BUILD-SHARED-THEME | DONE | Fonts installed via asset installer (F-03 fix, 26-row installer) | `brand/typography/fonts/*.woff2` installed to `public/assets/fonts/thiqa/` | 8 `.woff2` files present in `brand/typography/fonts/`; `interface/themes/thiqa/_typography.scss:22-89` `@font-face` rules reference `../assets/fonts/thiqa/...` |
| 086 | BUILD-SHARED-THEME | DONE | Arabic-capable web font added | `IBM Plex Sans Arabic` (4 weights) | `_typography.scss:58-89`; `$thiqa-font-family: 'Inter','IBM Plex Sans Arabic',...` |
| 111 | BUILD-SHARED-THEME | **PARTIAL** | PDF stylesheet build present; Arabic PDF font not wired into the PDF engines | `interface/themes/style_pdf.scss`, `public/themes/style_pdf.css`, `rtl_style_pdf.css` all present | PDF CSS exists, but `grep -rl Amiri` across `*.php`/mpdf/dompdf config returns **zero** matches — the Amiri TTFs in `brand/typography/fonts/pdf/` are not referenced by any PDF-generation code path. Blocked on **D-9** (Q25-compliant Arabic PDF fonts, both mpdf and dompdf configured) |
| 125 | BUILD-SHARED-THEME | DONE | Gap closed: Lato replaced by a font that is actually shipped | `interface/themes/thiqa/_typography.scss:97` | `$thiqa-font-family: 'Inter','IBM Plex Sans Arabic',...` — Inter is shipped as real `.woff2` files (row 085), unlike the old declared-but-absent Lato |

## SET-TRANSLATION (8 items)

This category has the weakest evidence of the whole sweep — see "Consequential findings" below.

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 101 | SET-TRANSLATION | DONE (by direct edit, not by catalog) | Literal template edit, `xlt`-wrapped | `templates/error/400.html.twig` etc. | `{{ "Thiqa 400 Error"\|xlt }}` — confirmed live; **note:** §15.3 says this should have been achieved purely via the translation catalog (source string never touched), but the actual implementation edited the source literal directly. Functionally equivalent, mechanically different from the classified action |
| 102 | SET-TRANSLATION | **NOT DONE** | — | 62 `xl()`/`xlt()`-wrapped `"OpenEMR ..."` strings, 21 files | Spot-checked 3 of the 21 files (below, rows 127/129) — all still say "OpenEMR" verbatim in source; no catalog override found either (see row 104) |
| 103 | SET-TRANSLATION | **NOT DONE** | — | 924 catalogue lines containing "OpenEMR" | No evidence of any catalog edit; `lang_definitions` total row count is unchanged (see row 104) |
| 104 | SET-TRANSLATION | **PARTIAL** | Round-trip tooling built; not executed against the DB | `docs/branding-production/i18n/{export,import}-arabic-translations.ps1`, `arabic-translations.csv` | `SELECT COUNT(*) FROM lang_definitions` = **237,509** — identical to the Group-1 discovery baseline, meaning no bulk change has landed. `arabic-translations.csv` (13,235 lines) is confirmed to be an **export snapshot** of the pre-existing catalogue (6,290/13,234 Arabic rows, 47.5%, matches live `SELECT COUNT(*) FROM lang_definitions WHERE lang_id=22` = 6,290 exactly) for a proofreader to fill in — not an already-applied import. The README explicitly frames this as a not-yet-executed round trip |
| 127 | SET-TRANSLATION | **NOT DONE** | — | `oauth2-login.html.twig:14`, `patient-select.html.twig:10`, `scope-authorize.html.twig:19` | `grep` confirms all 3 still read `{{ "OpenEMR Authorization"\|xlt }}`; DB query for a catalog override of that string (`lang_id` 1 or 22) returns **zero rows** — the live page will render "OpenEMR Authorization" verbatim |
| 128 | SET-TRANSLATION | **NOT DONE** | — | `oauth2-login.html.twig:92` | `grep` confirms `{{"OpenEMR Login"\|xlt }}` unchanged |
| 129 | SET-TRANSLATION | **NOT DONE** | — | `Application/layout.phtml:6`, `Documents/layout.phtml:18` | `grep` confirms `$this->translate()->xl('OpenEMR Application')` and `xl('Welcome to OpenEMR')` unchanged (a third file, `sendto.phtml`, was not found at the documented path and was not re-located in this pass) |
| 132 | SET-TRANSLATION | DONE | No brand name in the string; pre-existing Arabic translation already present | `portal/index.php:353` | `<title><?php echo xlt('Patient Portal Login'); ?></title>` — string never contained "OpenEMR"; `SELECT definition FROM lang_definitions ... lang_id=22` for this constant returns `الولوج الي بوابة المريض`, a pre-existing (upstream) translation |

**SET-TRANSLATION summary: 2 done, 1 partial, 5 not done.** This is the single largest gap found in this
audit — see "Consequential findings."

## TOKENIZE (6 items)

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 078 | TOKENIZE | DONE | Named colour tokens replace ad hoc per-theme SCSS variables | `interface/themes/thiqa/_tokens-light.scss`, `_tokens-dark.scss` | File listing confirms both exist; `_theme-colors.scss` present as the consumer |
| 079 | TOKENIZE | DONE | Gap closed — a primary-brand token now exists | `brand/tokens/thiqa-tokens.json` (`"brand"` object), `_tokens-light.scss:54-58` | `$thiqa-interactive-primary-default: #C43F2E;` etc. — a named, tokenized primary colour where none existed at discovery |
| 080 | TOKENIZE | DONE | Same token pipeline | `brand/tokens/thiqa-tokens.json` | Brand blue is no longer image-only; token JSON carries named colour values consumed by both SCSS and the SMART contract |
| 121 | TOKENIZE | DONE | Generator output, `Q38`-compliant template substitution | `.../templates/api/smart/smart-style_light.json.twig` | 12 keys confirmed present via `grep -c` and key enumeration |
| 122 | TOKENIZE | DONE | Same mechanism, light + dark now both exist (R-SMART-DARK requirement) | `smart-style_light.json.twig` / `smart-style_dark.json.twig` | Both files present; dark colours (`color_background: #...`, `color_error: #8E271D`, etc.) differ from the light file's original defaults documented at discovery (`#f8f9fa`/`#9e2d2d`) |
| 123 | TOKENIZE | DONE | Same mechanism | Both SMART templates | Typography/dimension keys (`font_family_body`, `dim_border_radius`, etc.) present in both files, 12/12 |

## HIDE (3 items)

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 060 | HIDE | DONE | Config gate | `globals.display_review_link = '0'` | DB query |
| 061 | HIDE | DONE | Config gate | `globals.display_donations_link = '0'` | DB query |
| 105 | HIDE | DONE | Config gate (`enable_help`) | `globals.enable_help = '0'` | DB query; `branding-profile.json` note: `src/OeUI/OemrUI.php:117` suppresses the help icon on this value, hiding ~180 open-emr.org wiki links without rewriting them |

## PROHIBITED (1 item)

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 120 | PROHIBITED | DONE | Custom PHPStan rule forbids the seam inside the branding module namespace | `tests/PHPStan/Rules/ForbiddenBrandingSiteConfigRule.php`, registered in `.phpstan/extension.neon` | Read the rule source directly (rejects any `sites/<site>/config.php`-shaped string literal in `OpenEMR\Modules\ThiqaBranding\*`); ran its test independently: `tests/Tests/Isolated/PHPStan/ThiqaBranding/ForbiddenBrandingSiteConfigRuleTest.php` → **`OK (10 tests, 12 assertions)`**, executed 2026-08-10 via `C:\openemr-stack\php\php.exe vendor\bin\phpunit -c phpunit-isolated.xml` |

## DEFER (8 items)

"Done" here means: correctly identified as deferred, confirmed untouched (not silently forgotten, not
accidentally broken), and traceable to a backlog reference.

| BRAND | Action | Status | Mechanism | Artefact | Evidence |
|---|---|---|---|---|---|
| 034 | DEFER | DONE (deferred) | Untouched, MVP=NO at discovery | `public/images/review-logo.png`/`.svg` | Not part of any branding commit |
| 035 | DEFER | DONE (deferred) | Untouched, installer/setup-only | `public/images/stylesheets/style_*.png` | `find` count still 22, matching discovery |
| 036 | DEFER | DONE (deferred) | Untouched | `swagger/favicon-{16,32}x*.png` | Not part of any branding commit |
| 037 | DEFER | DONE (deferred) | Untouched | `zend_modules/public/images/{favicon.ico,zf2-logo.png}` | Not part of any branding commit |
| 094 | DEFER | DONE (deferred) | Untouched, MVP=NO | `composer.json` `name`/`description`/`support` | `git diff` of `composer.json` shows only 3 additive lines (autoload + a new script); `name`/`description`/`support` fields byte-unchanged |
| 095 | DEFER | DONE (deferred) | Untouched, MVP=NO | `package.json` `name`/`description` | `grep` confirms `"openemr-interface"` / original description unchanged |
| 098 | DEFER | DONE (deferred) | Untouched | `swagger/index.html` | `<title>Swagger UI</title>` unchanged |
| 124 | DEFER | DONE (deferred) | Untouched, orphaned by design | `public/smart-styles/smart-light.json` | File mtime (Jul 5) predates all branding commits; untouched |

---

## Corrections to the audit brief

1. **The branding work is committed, not uncommitted.** The task brief stated "All of this session's
   Phase 2/3 implementation work is uncommitted in the working tree." That was true when
   `docs/AuditRebranding.md`'s F-01 finding was written, but is **no longer true**: `git log` shows 8
   branding commits (`a1c22b6a1` … `c6c3f9e6e`) already landed on `feat/thiqa-branding-foundation`, and
   `git status --short` shows only one unrelated untracked file. F-01's advice was followed.
2. **`docs/branding/` already contained two files** (`coverage-matrix.md`, `multi-tenant-white-label-readiness.md`)
   from an earlier Phase 5 pass, not created by this audit. `changes.md` (this file) is new.
3. **The i18n bulk-translation claim in the brief was imprecise.** The brief characterized
   `arabic-translations.csv` as "a bulk Arabic translation import." It is actually an **export** — a
   snapshot of the pre-existing (mostly upstream) catalogue state, prepared as one half of a
   round-trip kit for a human proofreader (D-4). Nothing has been imported back into the database; the
   live Arabic coverage (6,290/13,234 rows, 47.5%) is identical to what the CSV itself records as the
   starting point.
4. **BRAND-070 (`portal_onsite_two_address`) is confirmed still the upstream placeholder**, exactly as
   the brief anticipated — `https://your_web_site.com/openemr/portal`, deliberately excluded from the
   branding profile pending tenant provisioning (D-6).
5. **BRAND-113/D-10 is confirmed resolved by removal**, exactly as the brief anticipated — the
   registration cURL block and the `reg.open-emr.org` endpoint reference are both completely absent
   from `ProductRegistrationService.php`.

## Consequential findings

1. **SET-TRANSLATION is the weakest action category: 5 of 8 items show no evidence of translation work,
   and 3 of those (BRAND-127, 128, 129) are directly, currently visible.** The OAuth2 authorization
   screen (`oauth2-login.html.twig`, `patient-select.html.twig`, `scope-authorize.html.twig`) and the
   Zend module admin screens (`Application/layout.phtml`, `Documents/layout.phtml`) still render
   "OpenEMR Authorization", "OpenEMR Login", "OpenEMR Application", and "Welcome to OpenEMR" verbatim —
   confirmed both by source grep and by a DB query showing no catalog override exists for any of these
   strings in either English or Arabic. This is inconsistent with how BRAND-101 (error pages) was
   actually handled — a direct literal edit, not a catalog change — suggesting the SET-TRANSLATION
   workstream was only partly executed.
2. **6 of the 18 PATCH items are not done: the entire conditional installer/upgrade-screen set**
   (BRAND-007–012 — `setup.php`, `sql_patch.php`, `sql_upgrade.php`, `ippf_upgrade.php`) still displays
   "OpenEMR" in every title and heading. These are unauthenticated, operator-facing screens.
3. **BRAND-030 (Eye-Magic form hardcode) is not patched** — `interface/forms/eye_mag/help.php` and
   `eye_mag_functions.php` still hardcode `sites/default/images/login_logo.gif`, even though the asset
   at that path was itself replaced.
4. **BRAND-119 (duplicate favicon `<link>`) is not patched, and the plan itself contradicts its own
   classification** — `RebrandingPlan.md:936` explicitly defers it ("Cosmetic... Defer unless the
   duplicate breaks a client") while §16.2 classifies it as a mandatory PATCH action.
5. **BRAND-111 (PDF stylesheets) is only partially done** — the SCSS/CSS build exists, but the Amiri
   Arabic PDF fonts shipped in `brand/typography/fonts/pdf/` are not referenced anywhere in PHP,
   confirming they are not wired into either `mpdf` or `dompdf`. D-9 remains fully open, not
   partially mitigated.
6. **Two SET-CONFIG items are correctly left as tenant-provisioning placeholders, not defects**:
   BRAND-070 (portal address) and BRAND-110 (facility name, still literally "Your Clinic Name Here").
   Both are deliberately excluded from `branding-profile.json` with documented rationale, and both are
   genuinely blocked on tenant provisioning (D-6/D-7), not oversights.
7. **All 15 PRESERVE items and the single PROHIBITED item are confirmed intact**, including an exact
   byte-count match for every regulatory/trademark asset (CMS-1500, UB-04, the card-network logo, the
   acknowledgements page) against the Group-1 discovery baseline, and a passing, independently-run
   PHPStan guardrail test for the `sites/<site>/config.php` seam.
8. **All three HIDE items and all 6 TOKENIZE items are confirmed done**, including the R-SMART-DARK
   requirement (a `smart-style_dark.json.twig` now exists alongside the light variant, both with the
   full 12-key contract and genuinely different values).

---

## Status-count summary

> **Corrected 2026-08-10 (`docs/RebrandingBugs.md` RB-12 and RB-01).** The table that stood here was
> arithmetically wrong — it read DONE 111 / PARTIAL 2 / NOT DONE 14 / BLOCKED 2, which totals **129**, not
> 136, under either reading it offered. Recounting from this document's own "Known gaps" table gave
> NOT DONE **13** (not 14) and therefore DONE **119** (not 111). The "Integrity check" beneath it validated
> the *action-category* counts from §16.2 — a different quantity entirely — and so gave false assurance
> about numbers it never tested.
>
> The figures below are recomputed, and they also reflect the RB-01 remediation, which moved
> BRAND-127/128/129 from NOT DONE to DONE (delivered as catalogue data, the mechanism §16.2 actually
> assigns) and BRAND-007…012 from NOT DONE to DONE (delivered as PATCH, with records PR-10…PR-13).

| Status | Count | % of 136 |
|---|---:|---:|
| DONE | 128 | 94.1% |
| PARTIAL | 2 | 1.5% |
| NOT DONE | 4 | 2.9% |
| BLOCKED | 2 | 1.5% |

**Both integrity checks, kept separate this time.**

- **Status check (the one that belongs here):** 128 + 2 + 4 + 2 = **136** ✔
- **Action-category check (unrelated to status, retained because it is still true):**
  37 + 20 + 18 + 15 + 11 + 9 + 8 + 6 + 3 + 1 + 8 = **136** ✔

Every ID from BRAND-001 to BRAND-136 appears in exactly one table above; cross-checked against
`docs/rebranding.md` §16.2's own 136-row mapping with no additions, omissions, or reclassifications.

*Note on DEFER: all 8 DEFER-action items are counted inside DONE (correctly, verifiably left untouched),
since "done" for a DEFER action means "correctly deferred", not "implemented". A reader who prefers to
treat them as un-implemented gets **DONE 120 / PARTIAL 2 / NOT DONE 4 / BLOCKED 2 / DEFERRED 8** — which
also sums to 136.*

## Known gaps (every PARTIAL / NOT DONE / BLOCKED item, one line each)

*Updated 2026-08-10 after the `docs/RebrandingBugs.md` remediation pass. Rows that closed are kept with a
strikethrough and their evidence, rather than deleted, so the record shows what changed and how.*

| BRAND | Status | Reason |
|---|---|---|
| ~~007~~ | **DONE** | `setup.php` titles now read "Thiqa Setup Tool". Patch record **PR-10**. All strings are raw HTML/`echo`, no catalogue key moved |
| ~~008~~ | **DONE** | `setup.php` navbar-brand now "Thiqa Setup". PR-10 |
| ~~009~~ | **DONE** | `setup.php` legend/body copy (6 strings) patched. PR-10 |
| ~~010~~ | **DONE** | `sql_patch.php` title/banner now "Thiqa". PR-11. Note this file already kept the product name *outside* `xlt()`, so no translation was affected |
| ~~011~~ | **DONE** | `sql_upgrade.php` title/`<h2>` now "Thiqa". PR-12. The `<h2>` was `xlt()`-wrapped and carried **28 translations**; all 28 were carried forward onto the new constant by `tools/branding/apply-brand-strings.php` rather than orphaned |
| ~~012~~ | **DONE** | `ippf_upgrade.php` title/heading/body now "Thiqa". PR-13 |
| 030 | **NOT DONE** | Eye-Magic form still hardcodes `sites/default/images/login_logo.gif`. Unchanged: the plan records the Eye Magic form as not enabled in the Saudi product, so the hardcode is unreachable — but §16.2 still classifies it PATCH |
| 070 | BLOCKED | Tenant-specific portal address; correctly deferred to provisioning, blocked on D-6 |
| 102 | **NOT DONE** | `xl()`/`xlt()`-wrapped "OpenEMR" strings remain: **46 occurrences across 20 files** in fork-owned application code. Largest clusters: `library/globals.inc.php` (16), then `ScopeRepository.php` / `ServerScopeListEntity.php` / `interface/main/backup.php` (3 each). The mechanism to close these now exists (`tools/branding/brand-strings.json` + `apply-brand-strings.php`); the remaining work is enumerating them and agreeing the English replacements. **Method, so the number is reproducible:** recursive scan of `src/ interface/ library/ templates/ portal/` for `/xlt?\(\s*['"][^'"]*OpenEMR/`, **excluding** `oe-module-claimrev-connect` (a third-party Composer dependency relocated into the tree, not fork code), `vendor/`, `node_modules/` and `.claude/worktrees/`. Occurrences and matching lines both equal 46 — no line carries two matches. *(This supersedes a "43" figure quoted earlier the same day: that was a bare `grep \| wc -l` whose scope and metric were not stated, and it is not reproducible. See `docs/RebrandingBugs.md` §10.)* |
| 103 | **NOT DONE** | 924 catalogue lines containing "OpenEMR" — no bulk catalogue edit yet |
| 104 | PARTIAL | Arabic round-trip tooling built but not executed against the DB. *(Distinct from the English rebrand rows, which **were** applied — 33 changes, see BRAND-127…129 below)* |
| 110 | BLOCKED | Facility name is tenant data, correctly deferred to provisioning, blocked on D-6/D-7 |
| 111 | PARTIAL | PDF CSS build exists; Amiri Arabic fonts still not wired into mpdf/dompdf — blocked on D-9 (`docs/RebrandingBugs.md` RB-14) |
| 119 | **NOT DONE — reclassified** | Duplicate favicon `<link>`. §16.2 says PATCH, the plan defers it. Resolved 2026-08-10 in favour of **DEFER**, recorded rather than left contradictory — see `docs/RebrandingBugs.md` RB-23 |
| ~~127~~ | **DONE** | OAuth2 authorization titles (×3) now render "Thiqa Authorization" — via the **catalogue**, which is the action §16.2 assigns (SET-TRANSLATION, `Trk = NO`). Source literals are deliberately unchanged and now guarded at zero occurrences. Verified: `xl('OpenEMR Authorization')` → `Thiqa Authorization` |
| ~~128~~ | **DONE** | OAuth2 login button → "Thiqa Login", same mechanism |
| ~~129~~ | **DONE** | Zend module titles → "Thiqa Application" / "Welcome to Thiqa" / "Thiqa", same mechanism |

**Why 127–129 are DONE without a source diff.** Their action is SET-TRANSLATION. An earlier attempt edited
the literal inside `xl()`/`xlt()`, which renamed the catalogue key and orphaned **59** existing translations
across the shipped locales including Arabic. That was reverted and replaced with `lang_id = 1` catalogue
rows. All 59 translations are intact and the English UI is rebranded. Full analysis and measurements:
`docs/RebrandingBugs.md` RB-01.
