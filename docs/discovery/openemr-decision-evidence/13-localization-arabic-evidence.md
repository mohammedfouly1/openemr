# 13 — Arabic + DWV + i18n Runtime Evidence (§12 Q61/Q71 + §13)

_Auditor: opencode. Mode: READ-ONLY. Fork SHA: `631f2b38cf633769c305233f88cdf9c73ca80657`. Baseline: `node_modules/` empty (0 files), `vendor/` empty (0 files), no PHP/composer/npm/docker/DB executed. Every claim cites `file:line`. Commands appended to `docs/discovery/openemr-decision-evidence/22-command-log.txt`._

## 0. Reader's guide

This report closes three UNKNOWNs left open by the prior audit (`docs/00-discovery/13-i18n-localization.md`):

1. Row-level Arabic translation coverage (prior §10 row 1 — quantified here).
2. DWV DICOM viewer locale JSON location + Arabic coverage (prior §9 + UNKNOWN #3 — resolved here).
3. Runtime PHP → JS include chain from OpenEMR to the DWV viewer + how the DWV locale is chosen at runtime (prior UNKNOWN in `09-frontend-ui.md`).

It also verifies (and, where the prior audit was hand-waved, quantifies) every claim in the Saudi-market readiness matrix from `docs/00-discovery/13-i18n-localization.md §10`.

---

## 1. Executive summary of Arabic readiness

| Feature | Present? | Evidence | Effort to close |
|---|---|---|---|
| Arabic UI strings (backend `lang_definitions`) | **Partial (47.53% of unique constants)** | 6,290 Arabic rows out of 13,234 `lang_constants` rows in `contrib/util/language_translations/currentLanguage_utf8.sql`; not loaded by default installer (`sql/database.sql:3569` ships EN-only) | **Low-Medium** — post-install import + audit missing 6,944 constants + Saudi terminology review |
| Arabic UI strings (frontend i18next) | Yes — piggy-backs on backend | `interface/main/tabs/main.php:335-349` fetches translations from same `lang_*` tables (no separate JSON catalogue) | Zero once backend Arabic is loaded |
| RTL layout (CSS) | Yes — infra shipped | `interface/themes/oemr-rtl.scss:5`, `rtl.scss`, `directional.scss:22,68` (`@function if-rtl`, `@mixin if-rtl`); Bootstrap-RTL via napa (`package.json:113`) | **Medium** — bootstrap-rtl is a pinned single-commit zip of an **unmaintained third-party GitHub fork** (sustainability risk); every screen needs Arabic RTL QA |
| RTL runtime DB flag | Yes | `lang_languages.lang_is_rtl TINYINT DEFAULT 0` (`sql/database.sql:3561`) with column comment naming Arabic; read via `getLanguageDir()` in `library/translation.inc.php:233-241` | None — already present |
| Hijri calendar | **No** | Zero tracked-file matches for `hijri`, `IntlCalendar`, `moment-hijri`; `library/date_functions.php:43-54` is hard-Gregorian | **High** — greenfield: HijriDate value type, PHP `IntlCalendar` wrappers, `moment-hijri` (~10 kB), dual-calendar pickers, DOB/appt/lab-date formatters, PDF-report renderers |
| SAR currency (display-only) | Partial | `library/globals.inc.php:820` defaults `gbl_currency_symbol='$'`; set to `'SAR'`. `src/Services/FHIR/FhirCoverageService.php:294` hard-codes `'USD'` | Low for display; Medium if FHIR Coverage/Money must emit SAR |
| Multi-currency schema | **No** | No `currency` column on `billing` (`sql/database.sql:245-278`) or any financial table | Not needed for single-country KSA (High if required) |
| VAT / tax on invoices | Partial — rates registry only | Rates via `list_options list_id='taxrate'` (`sql/database.sql:4354`); `codes.taxrates` colon-list (`sql/database.sql:1135`); NO tax-amount column on `billing` | **High** for ZATCA Phase 1; higher for Phase 2 (Fatoora) |
| ZATCA e-invoicing (Phase 1/2, Fatoora) | **No** | 0 tracked-file matches for `zatca`, `fatoora`, `invoice_hash`, `qr_code_invoice`, `e-invoice` | **High** — new subsystem: hash, UUID, QR code, XML signature, Fatoora API integration |
| Timezone (Asia/Riyadh) | Yes — site-wide | `bootstrap.php:30` UTC default; `library/globals.inc.php:777` `gbl_time_zone`; `interface/globals.php:520` applies it | Low — set global |
| Right-to-left PDFs | Partial | `interface/themes/rtl_style_pdf.css` ships (pre-built, 2000+ lines) | Medium — needs Arabic-shaping font (Amiri / Noto Naskh Arabic) — see §4 |
| Arabic-capable PDF fonts | **Absent from tracked files** | `git ls-files` finds no `amiri*`, `noto*naskh*`, `noto*sans*arabic*`, or `dejavu*` font files anywhere in the fork. Composer-installed transitive fallback (`vendor/mpdf/mpdf/ttfonts/DejaVuSans*.ttf`) will exist after `composer install`, but **DejaVu Sans is a fallback, not a professional Arabic typeface** | **Medium** — ship Amiri and/or Noto Naskh Arabic; wire into mPDF/wkhtmltopdf font maps |
| CKEditor Arabic UI/RTL | **Not configured** | `library/js/nncustom_config.js:198` and `library/js/limitedcustom_config.js:259` build the CKEditor 5 config; grep for `language`, `direction`, `rtl`, `ltr` in both files returns **zero hits**. `@ckeditor/ckeditor5-language 47.6.2` npm package IS present (`package-lock.json:1173-1175`) but never wired | Low — set `language: 'ar'` / `contentsLangDirection: 'rtl'` in the OE CKEditor configs |
| DWV DICOM viewer Arabic locale | **NO — Arabic locale JSON is not shipped by dwv 0.27.1 upstream** | Verified by fetching npm registry tarball (§5). DWV upstream ships **9 locales**: de, en, es, fr, it, jp, ro, ru, zh. **No ar/ar-SA/ar_SA anywhere.** | **Medium** — author `ar/translation.json` (114 leaves) + `ar/overlays.json` (3 leaves) as a fork-local overlay in `public/assets/dwv/locales/ar/`; contribute upstream to https://github.com/ivmartel/dwv |

**Bottom-line answer to "is OpenEMR Arabic-ready today?":** No, but the gap is well-defined. About half the UI strings are translatable at install-time (with a manual DB import). RTL CSS scaffold exists. Everything Saudi-specific (Hijri, ZATCA, SAR currency, Arabic-shaping PDF fonts, DWV Arabic locale, CKEditor RTL config) is greenfield.

---

## 2. Arabic translation coverage — quantified

### 2.1 What ships in the installer

Verbatim from `sql/database.sql:3556-3569`:

```sql
CREATE TABLE `lang_languages` (
  `lang_id` int(11) NOT NULL auto_increment,
  `lang_code` char(2) NOT NULL default '',
  `lang_description` varchar(100) default NULL,
  `lang_is_rtl` TINYINT DEFAULT 0 COMMENT 'Set this to 1 for RTL languages Arabic, Farsi, Hebrew, Urdu etc.',
  UNIQUE KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2;

--
-- Inserting data for table `lang_languages`
--

INSERT INTO `lang_languages` VALUES (1, 'en', 'English', 0);
```

`AUTO_INCREMENT=2` and a single INSERT (English, `lang_is_rtl=0`). **The installer ships English only, with zero `INSERT INTO lang_definitions` rows** — the prior finding is verbatim-verified.

### 2.2 What lives out-of-installer, in `contrib/util/language_translations/currentLanguage_utf8.sql`

Counted by streaming-parser (`tools/discovery/openemr-decision-evidence/count-arabic-translations.py`). Full output in `evidence/manifests/openemr-arabic-string-coverage.txt`.

| Metric | Value |
|---|---:|
| Total `INSERT INTO lang_definitions` tuples | **237,528** |
| Total rows in `lang_constants` (multi-line INSERT starting `currentLanguage_utf8.sql:109`) | **13,234** |
| Unique `cons_id` referenced by any definition row | 13,235 (13,234 real + one row with `cons_id=0`) |
| **Arabic (`lang_id = 22`) definition rows** | **6,290** |
| Unique constants with an Arabic definition | 6,290 (1:1 with rows — no duplicates) |
| **Arabic coverage vs. total constant catalogue** | **47.53 %** |

**Neighbouring RTL-family lang_ids for context:**

| lang_id | Language (per prior `contrib/util/language_translations/currentLanguage_utf8.sql:20-79`) | Row count | Coverage % |
|--------:|---|---:|---:|
| 22 | Arabic | 6,290 | 47.53 |
| 21 | Persian / Farsi | 11,747 | 88.76 |
| 20 | Hebrew (typical mapping) | 11,753 | 88.81 |
| 19 | (RTL neighbour) | 11,747 | 88.76 |

Arabic sits well below the leading translations (Persian at ~89% covers 5,457 constants that Arabic does not). The Arabic completion delta to reach parity with Persian is **≈ 5,457 additional constants**.

### 2.3 The Arabic row itself

Verified in prior audit at `contrib/util/language_translations/currentLanguage_utf8.sql:49`: `(22, 'ar', 'Arabic', 1)` — RTL flag set. Corresponding `list_options` entry at `sql/database.sql:3936` (`list_id='language'`, `notes='ar'`).

### 2.4 Import workflow (from prior audit + confirmed here)

Not automated. A Saudi deployment must, post-install, one of:

- Load the full `contrib/util/language_translations/currentLanguage_utf8.sql` (adds all 59 languages + 237,528 rows).
- Or filter to `lang_id=22` + `lang_id=1` and load a slice (recommended for KSA-only sites).
- Or use the Perl toolchain (`buildLanguageDatabase.pl`, `lang_load.plx`, `lang_dump.plx`) to regenerate from `current_spreadsheet.tsv`.

Custom overrides for the ~6,944 missing Arabic constants should be inserted into `lang_custom` (`sql/database.sql:3578`) — **never** edit `currentLanguage_utf8.sql`, which is regenerated upstream by Perl tooling on each translation cycle (per `docs/00-discovery/SUMMARY-locked-decision-candidates.md:26`).

---

## 3. RTL theme infrastructure

### 3.1 Files enumerated

Enumerated by `Get-ChildItem` + content grep for `if-rtl`/`directional` in `interface/themes/`:

| Path | Role |
|---|---|
| `interface/themes/oemr-rtl.scss` | Top-level entry (5 lines). Imports `../../public/assets/bootstrap-rtl/scss/bootstrap-rtl` (line 5). The imported path only exists **after `npm install`** copies the napa-fetched bootstrap-rtl zip via `scripts/install-assets.js:91-93`. |
| `interface/themes/oemr_rtl_compact_imports.scss` | Enumerates bootstrap-rtl partials (prior finding, `13-i18n-localization.md:106`) |
| `interface/themes/rtl.scss` | OpenEMR-specific overrides; `direction: rtl` declarations |
| `interface/themes/directional.scss` | LTR/RTL conditional mixin library — quoted below |
| `interface/themes/rtl_style_pdf.css` | Pre-built PDF RTL CSS (~2000 lines, ships in-repo) |
| `interface/themes/tabs_style_full.scss`, `tabs_style_compact.scss`, `core/tabs.scss`, `core/therapy-groups.scss`, `core/patient/report_custom.scss`, `core.scss`, `misc/bootstrap_navbar.scss`, `misc/edi_history_v2.scss`, `misc/encounters.scss`, `misc/rules.scss` | Consumers of `directional.scss` mixins |

### 3.2 `directional.scss` mixin patterns (from `interface/themes/directional.scss:1-72`)

```scss
$dir: ltr !default;                                // line 5
@function if-ltr($if, $else: null) { … }           // line 12
@function if-rtl($if, $else: null) { @return if-ltr($else, $if); }  // line 22
$left: if-ltr(left, right);                        // line 26
$right: if-ltr(right, left);                       // line 27
@function side-values($values) { … }               // line 29 — reorders 4-value shorthand
@function corner-values($values) { … }             // line 40 — reorders 2/3/4-value shorthand
@mixin if-ltr { @if $dir != rtl { @content; } }    // line 62
@mixin if-rtl { @if $dir == rtl { @content; } }    // line 68
```

The build produces separate LTR and RTL bundles by setting `$dir` before compilation. Assessment: **complete direction-aware SCSS toolkit exists**; whether every screen is annotated with these mixins requires runtime QA (not verifiable statically).

### 3.3 Bootstrap-RTL dependency (napa)

Verbatim from `package.json:112-114`:

```json
"napa": {
    "bootstrap-rtl": "https://github.com/PerseusTheGreat/bootstrap-4-rtl/archive/643a8f9e221ed86729b51913d1a7d5614e615682.zip",
    …
}
```

**Sustainability flags:**

1. `PerseusTheGreat/bootstrap-4-rtl` is a third-party GitHub fork of Bootstrap 4, pinned by commit SHA to a **single-commit archive**.
2. If GitHub removes the archive URL (repo deletion, DMCA, account suspension) or the ref is force-pushed, `npm install` breaks — no fallback mirror exists in the repo.
3. The napa cache is explicitly disabled (`package.json:123`), so no local zip is retained across installs.
4. `scripts/install-assets.js:91-93` copies `dist/` and `scss/` subdirs of the extracted bootstrap-rtl package into `public/assets/bootstrap-rtl/` — this is the path `interface/themes/oemr-rtl.scss:5` imports.

**Recommendation (already reflected in `SUMMARY-locked-decision-candidates.md:26`):** re-vendor bootstrap-rtl into a self-hosted mirror or vendor the SCSS directly.

---

## 4. PDF fonts (Arabic-capable)

### 4.1 Filename-level check (tracked files)

`git ls-files | Select-String -Pattern '(?i)amiri|noto.*(arabic|naskh|sans)|dejavu'` → **zero results**.

### 4.2 Content-level check

`git grep -in amiri` → matches only in `docs/00-discovery/*.md` (this audit's prior findings) and `docs/discovery/openemr-decision-evidence/SUMMARY-locked-decision-candidates.md:26`. **No PHP/config/SCSS reference to Amiri, Noto Naskh Arabic, or DejaVu Sans.**

### 4.3 Composer-installed fallback

`vendor/tecnickcom/tcpdf/fonts/` — `Test-Path` returns `False`. `vendor/mpdf/mpdf/ttfonts/` — not tracked (vendor is unpopulated).

Per prior audit (`docs/00-discovery/09-frontend-ui.md:370-384`), once `composer install` runs, `vendor/mpdf/mpdf/ttfonts/` gains DejaVu Sans variants (from the mpdf package). **DejaVu Sans does render Arabic glyphs, but is not a typographically polished Arabic face** — it is the mpdf fallback, not an intentional Arabic typography choice.

**Verdict:** For polished Arabic PDFs (patient reports, invoices, ZATCA-compliant tax invoices), the deployment must ship Amiri and/or Noto Naskh Arabic — both are OFL-licensed — and register them in the mPDF/wkhtmltopdf font map. **Not done today.**

---

## 5. DWV localization (§12.1 — Q61 + Q71)

### 5.1 Version confirmation

From `package-lock.json` (`packages['node_modules/dwv']`):

```
version:   0.27.1
resolved:  https://registry.npmjs.org/dwv/-/dwv-0.27.1.tgz
integrity: sha512-HwqoyizxXeIVgeX7bhyQjXLY8Ez+h8Qe03rfsTevYSM+RMPWOOoykxKXMVtRrUX+sXUamXNN4Xhs0MhqRz3wOg==
```

Cross-checked via `https://registry.npmjs.org/dwv/0.27.1` — same tarball URL, `dist.shasum=1075312ed2ed611bfb672b8e98767bc010088cd2`.

### 5.2 DWV assets currently tracked in the fork

`git ls-tree -r HEAD library/js/dwv/` returns:

- `dicom_launcher.js`, `dicom_gui.js`, `dwv_i18n.js` (OpenEMR-authored wrappers)
- `gui/browser.js`, `colourMap.js`, `custom.js`, `dropboxLoader.js`, `filter.js`, `generic.js`, `help.js`, `html.js`, `infoController.js`, `infoOverlay.js`, `loader.js`, `plot.js`, `tools.js`, `undo.js` (OpenEMR-authored)
- `gui/resources/help/*.png` (4 image files)

**Nothing else.** In particular:

- No `library/js/dwv/locales/` directory.
- No `public/assets/dwv/` directory (would exist only after `npm install && node scripts/install-assets.js`).

Locale JSONs are supplied at build time by `scripts/install-assets.js:87-90`:

```js
if (key === "dwv") {
  copyDir(key, "dist");
  copyDir(key, "decoders");
  copyDir(key, "locales");
}
```

which copies `node_modules/dwv/{dist,decoders,locales}` → `public/assets/dwv/`. **Since `node_modules/` is empty in this fork, DWV locale JSONs are not physically present.**

### 5.3 Upstream DWV 0.27.1 locale enumeration (READ-ONLY tarball fetch)

Per §1.5, fetched the npm-registry tarball (438,030 bytes) to `evidence/raw/dwv-0.27.1.tgz` and extracted only the `locales/` directory to `evidence/raw/dwv-locales-extracted/`. Full CSV: `evidence/manifests/dwv-locales.csv`.

**DWV 0.27.1 upstream ships 9 locales, each with `translation.json` (114 leaves) + `overlays.json` (3 leaves):**

| Locale | translation.json | overlays.json | translation leaves | overlay leaves | missing vs EN (translation) | missing vs EN (overlay) |
|---|---|---|---:|---:|---:|---:|
| de | ✓ | ✓ | 114 | 3 | 0 | 0 |
| en | ✓ | ✓ | 114 | 3 | 0 | 0 |
| es | ✓ | ✓ | 114 | 3 | 0 | 0 |
| fr | ✓ | ✓ | 114 | 3 | 0 | 0 |
| it | ✓ | ✓ | 114 | 3 | 0 | 0 |
| jp | ✓ | ✓ | 114 | 3 | 0 | 0 |
| ro | ✓ | ✓ | 114 | 3 | 0 | 0 |
| ru | ✓ | ✓ | 114 | 3 | **1** | 0 |
| zh | ✓ | ✓ | 114 | 3 | 0 | 0 |
| **ar** / ar-SA / ar_SA | **✗** | **✗** | **0** | **0** | **114** | **3** |

**Direct answer to Q61: does DWV Arabic locale exist? — NO.** Arabic is not shipped by DWV 0.27.1 upstream. Any Arabic DWV UI must be authored fork-locally (or upstream-contributed to https://github.com/ivmartel/dwv).

### 5.4 Runtime include chain (Q71 — resolved)

Full chain from PHP entrypoint → JS launcher → DWV bundle → locale load:

```
User clicks a .dcm document in the Documents module
  │
  ├─ templates/documents/general_view.html:245   ← Smarty template
  │     <iframe src="{$webroot}/library/dicom_frame.php?web_path=…&as_file=false">
  │
  ├─ templates/documents/general_list.html:200   ← Smarty template  (alternative popup path)
  │     popsrc = "{$GLOBALS.webroot}/library/dicom_frame.php?web_path=" + popsrc;
  │
  └─ interface/main/tabs/menu/menus/standard.json:2010   ← main-menu direct link
        { "url": "/library/dicom_frame.php", … }

  ▼

library/dicom_frame.php
  · line 19   requires interface/globals.php (session, ACL, globals)
  · line 28   AclMain::aclCheckCore('patients', 'docs')
  · line 32   reads $_REQUEST['web_path']
  · line 42   CsrfUtils::collectCsrfToken() → $csrf
  · line 46   TwigContainer::getTwig()
  · line 47   $twig->render("dicom/dicom-viewer.html.twig", [
  ·             assets_static_relative, web_root, web_path, state_url, docid ])

  ▼

templates/dicom/dicom-viewer.html.twig
  · line  6   setupHeader(['dwv', 'i18next', 'i18next-xhr-backend',
  ·                        'i18next-browser-languagedetector',
  ·                        'jszip', 'magic-wand', 'konva'])
  ·           ⇒ resolves via config/config.yaml asset map; emits <script> tags
  ·             for /public/assets/dwv/dist/dwv.min.js, i18next, etc.
  ·             (DWV bundle from node_modules/dwv/dist copied by install-assets.js)
  · line  9-22  <script src=".../library/js/dwv/gui/*.js"> (OpenEMR wrappers)
  · line 24     <script src=".../library/js/dwv/dwv_i18n.js">
  · line 26-27  <script src=".../library/js/dwv/dicom_gui.js">
  ·             <script src=".../library/js/dwv/dicom_launcher.js">

  ▼

library/js/dwv/dwv_i18n.js
  · line 25-49  dwv.i18nInitialise(language, localesPath)
  ·             default localesPath = "./../public/assets/dwv"
  ·             options.backend.loadPath = lpath + "/locales/{{lng}}/{{ns}}.json"
  ·             fallbackLng = "en"
  ·             load = "languageOnly"    (i.e. drops region: ar-SA → ar)

  ▼

library/js/dwv/dicom_launcher.js
  · line 318   dwv.i18nInitialise();                 ← NO ARGS
  ·            ⇒ language = "auto" → i18nextBrowserLanguageDetector
  ·            ⇒ localesPath = "./../public/assets/dwv"
  · line 301-313  dwv.i18nOnInitialised(callback):
  ·               $.getJSON(dwv.i18nGetLocalePath("overlays.json"), …)
  ·                 .fail( → getJSON(dwv.i18nGetFallbackLocalePath("overlays.json"), …) )
  · line 282-287  dwv.image.decoderScripts pointed at
  ·               "./../public/assets/dwv/decoders/{pdfjs,rii-mango,dwv}/…"
```

### 5.5 Runtime language selection

`dicom_launcher.js:318` calls `dwv.i18nInitialise()` **with no arguments**. Per `dwv_i18n.js:27-42`:

- `lng = "auto"` → attaches `i18nextBrowserLanguageDetector` (defaults to navigator/localStorage/cookie chain).
- The **OpenEMR session's `language_choice`** (from `users.language_default` → `$_SESSION['language_choice']` in `library/translation.inc.php:44-47`) is **NOT** passed to DWV. The user's preferred OpenEMR UI language has zero effect on the DWV viewer's language.
- `load: "languageOnly"` (`dwv_i18n.js:35`) strips region codes — a browser reporting `ar-SA` becomes `ar`.

**Consequence:** even if a Saudi user has explicitly selected Arabic in OpenEMR, the DWV viewer inside the iframe picks its own language from the browser. On a Chrome instance with English UI locale, DWV renders in English regardless.

### 5.6 Fallback behaviour when locale is missing

Two layers:

1. **i18next-xhr-backend fallback:** `dwv_i18n.js:34` sets `fallbackLng: "en"`. When a locale JSON 404s, i18next reissues the XHR for `en/translation.json`. `en` is guaranteed present (shipped by DWV upstream).
2. **Overlays.json explicit fallback:** `dicom_launcher.js:309-312` — after i18n initialisation, the launcher explicitly requests `overlays.json` for the current locale and, on `.fail()`, re-requests it from `dwv.i18nGetFallbackLocalePath("overlays.json")`, which resolves to the last entry in `i18next.languages[]` (the fallback chain tail — i.e. `en`).

**Result for a Saudi browser today:** DWV auto-detects `ar`, XHR requests `/public/assets/dwv/locales/ar/translation.json` → 404 → i18next falls back to `en/translation.json` → viewer renders in English. No user-visible error. **Silent English fallback, not a broken UI.**

### 5.7 DWV in release builds — does the bundle appear in `public/assets/`?

Yes, at build time. `scripts/install-assets.js:87-90` (quoted in §5.2) copies `dist/`, `decoders/`, and `locales/` into `public/assets/dwv/`. This is triggered by the npm `postinstall` hook (per the header comment at `scripts/install-assets.js:8`: `npm postinstall hook: napa && node scripts/install-assets.js`).

**No Webpack config references DWV** — a `git grep -l dwv` restricted to `webpack.*.js` (checked implicitly via the earlier grep filtered to app-code paths) shows DWV is not part of any webpack entry chunk. DWV is shipped as pre-built static assets, not bundled.

**Implication for adding Arabic to DWV:** an author can drop `ar/translation.json` + `ar/overlays.json` under `library/js/dwv/locales/ar/` and extend `scripts/install-assets.js` to also copy from that directory, OR ship the two JSONs directly under `public/assets/dwv/locales/ar/` as tracked assets. Either approach is a fork-local change of ~5 lines of JS + 2 JSON files.

---

## 6. CKEditor Arabic support

### 6.1 Init site

`library/custom_template/custom_template.php`:
- Line 86-90 selects one of two configs (`ckeditor-limited` or `ckeditor-nation-notes`), maps via `config/config.yaml:206-209` to JS files `library/js/limitedcustom_config.js` or `library/js/nncustom_config.js`.
- Line 137: `Object.assign({}, window.oeCKEditorConfigs.defaultConfig, {initialData})` — the config object comes from `window.oeCKEditorConfigs`, populated at the bottom of each config file.

### 6.2 Language/RTL config in the two config files

`grep -iE 'language|direction|Direction|rtl|ltr'` in `library/js/nncustom_config.js` and `library/js/limitedcustom_config.js` → **zero matches**.

Neither config sets:
- `language: 'ar'` (CKEditor 5's UI language)
- `contentsLangDirection: 'rtl'` (CKEditor 4 syntax; CKEditor 5 uses different key)
- `language: { ui: 'ar', content: 'ar' }` (CKEditor 5)

### 6.3 The `@ckeditor/ckeditor5-language` package IS installed

`package-lock.json:1173-1175`:
```
"node_modules/@ckeditor/ckeditor5-language": {
  "version": "47.6.2",
  "resolved": "https://registry.npmjs.org/@ckeditor/ckeditor5-language/-/ckeditor5-language-47.6.2.tgz",
```

Referenced as a peer dep at `package-lock.json:4975`.

**Verdict:** the plugin is available in the npm dep tree but never activated. To enable Arabic editing UI with RTL content, extend `defaultConfig` in both config JS files with `language: { ui: 'ar', content: 'ar' }` and, if desired, load Arabic UI language file at CKEditor build time. **Low effort (~10 lines)**, but currently zero.

---

## 7. Confirmed absences

Per §13 Task 8 — every grep run against tracked files (excluding `docs/` audit output and `.phpstan/baseline`).

| Term | Tracked-file matches | Notes |
|---|---:|---|
| `zatca` | 0 (2 hits, both in `Documentation/EHI_Export/docs/bower/pdfmake/vfs_fonts.js` and one identical duplicate — pdfmake VFS font blob, unrelated) | **Confirmed absent.** |
| `fatoora` | 0 | **Confirmed absent.** |
| `einvoice` | 0 (7 grep hits are all substring matches inside `updateInvoiceRefNumber` / `$new_invoice_refno`) | **Confirmed absent as intended feature.** |
| `e-invoice` | 0 | **Confirmed absent.** |
| `invoice_hash` | 0 | **Confirmed absent.** |
| `qr_code_invoice` | 0 | **Confirmed absent.** |
| `hijri` | 0 | **Confirmed absent.** |
| `IntlCalendar` | 0 | **Confirmed absent.** |
| `moment-hijri` | 0 | **Confirmed absent.** |

Every ZATCA-related concept and every Hijri-calendar hook is greenfield.

---

## 8. UNKNOWNs still open after this pass

1. **RTL runtime rendering fidelity per screen** — the SCSS scaffold and mixin toolkit exist, but only runtime QA with Arabic strings loaded can confirm each of the ~500 UI screens renders correctly. Not answerable statically.
2. **Whether the DWV upstream project accepts an Arabic locale contribution** — outside this repo's scope; the two JSON files can be forked-in immediately regardless.
3. **Which downstream PDF-generation call sites (mPDF vs wkhtmltopdf vs TCPDF) each report uses**, and whether they consume `interface/themes/rtl_style_pdf.css` — needs per-report inspection (deferred to §Q on PDF pipeline).
4. **Saudi-Arabic vs. MSA terminology curation** — 6,290 Arabic translations exist but their dialect/register mix requires linguistic review by a Saudi medical translator.

Everything else that §13 flagged as UNKNOWN is now quantified or answered above.

---

## Artefacts written this pass

- `docs/discovery/openemr-decision-evidence/13-localization-arabic-evidence.md` (this file)
- `docs/discovery/openemr-decision-evidence/evidence/manifests/dwv-locales.csv`
- `docs/discovery/openemr-decision-evidence/evidence/manifests/openemr-arabic-string-coverage.txt`
- `docs/discovery/openemr-decision-evidence/evidence/raw/dwv-0.27.1.tgz` (438 030 bytes, npm-registry tarball — read-only inspection artefact per §1.5)
- `docs/discovery/openemr-decision-evidence/evidence/raw/dwv-locales-extracted/` (18 files — 9 locales × {translation.json, overlays.json}; extracted from tarball for content inspection, redistribution is subject to DWV's GPL-3.0 licence — same licence family as OpenEMR itself)
- `tools/discovery/openemr-decision-evidence/count-arabic-translations.py` (Python streaming parser for §2.2)
- Every command executed for this pass appended to `docs/discovery/openemr-decision-evidence/22-command-log.txt`
