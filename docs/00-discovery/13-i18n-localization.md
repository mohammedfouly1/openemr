# 13 — i18n / Localization (Saudi-market readiness)

Read-only audit. Every claim cites `file:line`. Prior: `00-environment.md`, `01-repo-inventory.md`.

---

## 1. `xl()` — the translation engine

**Definition:** `library/translation.inc.php:39` — `function xl($constant)`. The parameter is annotated `@param literal-string`
(`library/translation.inc.php:36`) — dynamic strings will not be picked up by the extraction tooling.

**Lookup:**
1. Early return if `disable_translation` or `temp_skip_translations` is set on `OEGlobalsBag` (`library/translation.inc.php:41-43`).
2. `language_choice` is read from the session; defaults to `1` (English) (`library/translation.inc.php:44-47`).
3. Newlines/CR are stripped from the constant (`library/translation.inc.php:52-54`).
4. In-request cache check via `OpenEMR\Common\Translation\TranslationCache::has()` (`library/translation.inc.php:57`).
5. If cache is not warmed, a single-row SQL join over `lang_definitions` × `lang_constants` on `(lang_id, constant_name)` runs
   via `sqlStatementNoLog` (`library/translation.inc.php:64-73`); the result is stored back in `TranslationCache::set()`
   (`library/translation.inc.php:77`).
6. If no translation exists, returns the source constant unchanged (`library/translation.inc.php:80-82`).

**Cache:** two-tier — a cold-start warmer (`xlWarmCache()`, `library/translation.inc.php:12-18`) loads all translations for the
current language up front; a per-request cache absorbs remaining lookups. `TranslationCache::isWarmed()` lets the fast path
return `''` without a DB round-trip when a constant is missing (`library/translation.inc.php:59-61`).

**Escaping:** `xl()` does NOT HTML-escape. It only sanitizes apostrophes/quotes/newlines and strips `{{...}}` mustache-style
placeholders — apostrophes and `"` become backticks unless `translate_no_safe_apostrophe` is on (`library/translation.inc.php:84-93`).
Escaping is the caller's responsibility (`xlt()`, `xla()`, `xlj()` wrappers live elsewhere).

**Directionality helper:** `getLanguageDir($lang_id)` — reads `lang_is_rtl` on `lang_languages`, returns `'rtl'` or `'ltr'`
(`library/translation.inc.php:233-241`).

---

## 2. Translation storage — the `lang_*` tables

All four defined in `sql/database.sql:3525-3583`.

| Table | Purpose | Schema highlights |
|---|---|---|
| `lang_constants` (`sql/database.sql:3526`) | Source strings | `cons_id` PK, `constant_name mediumtext BINARY` (100-char key index) |
| `lang_definitions` (`sql/database.sql:3540`) | Translations | `def_id` PK, `cons_id`, `lang_id`, `definition mediumtext`, composite key `(lang_id, cons_id)` |
| `lang_languages` (`sql/database.sql:3557`) | Language registry | `lang_id` PK, `lang_code char(2)`, `lang_description varchar(100)`, **`lang_is_rtl tinyint`** (`sql/database.sql:3561`) |
| `lang_custom` (`sql/database.sql:3578`) | User overrides (no PK) | `lang_description`, `lang_code`, `constant_name`, `definition` |

**Adding a language** at install time: INSERT into `lang_languages` (id, ISO-ish 2-char code, description, rtl-flag). The
`lang_code` column is `char(2)` — insufficient for full BCP-47 tags. Note the language selector list in
`list_options` (`list_id='language'`) is a separate registry — the two must be kept in sync (see the `arabic` row at
`sql/database.sql:3936`, and the `notes='ar'` mapping added in `sql/4_1_2-to-4_2_0_upgrade.sql:378`).

---

## 3. Language coverage shipped

**`sql/database.sql` ships EXACTLY ONE language and ZERO translations:**

```sql
INSERT INTO `lang_languages` VALUES (1, 'en', 'English', 0);  -- sql/database.sql:3569
```

No `INSERT INTO lang_definitions` rows exist in `sql/database.sql` at all (grep confirmed empty). A fresh install has an
English-only shell.

**Bulk translations live outside the installer**, in `contrib/util/language_translations/`:

- `contrib/util/language_translations/currentLanguage_utf8.sql` — the master dump. It redefines `lang_languages` with **59
  languages** (`AUTO_INCREMENT=60`, `contrib/util/language_translations/currentLanguage_utf8.sql:20`) and contains hundreds of
  thousands of `INSERT INTO lang_definitions` rows (the file is 250,879 lines long).
- `contrib/util/language_translations/current_spreadsheet.tsv` — source TSV, ~9,400 constants × 59 language columns.
- Perl toolchain to regenerate: `buildLanguageDatabase.pl`, `lang_load.plx`, `lang_dump.plx`, `collectConstants.pl`,
  `combineConstantsSpreadsheet.pl`, `sortCleanList.pl`.
- Historical partial: `sql/ins_lang_def_nl.sql` (Dutch-only, legacy).

**Arabic:**
- Row present as `(22, 'ar', 'Arabic', 1)` — RTL flagged (`contrib/util/language_translations/currentLanguage_utf8.sql:49`).
- In `list_options` under `list_id='language'`: `sql/database.sql:3936`.
- Row count targeting `lang_id=22` in `currentLanguage_utf8.sql`: `UNKNOWN — file is 250k lines, not measured this pass`.
  Column 22 in `current_spreadsheet.tsv` is populated (samples on lines 7315, 9458-9460, 11, etc.), suggesting reasonable
  coverage across the ~9,400 constants, but real completeness requires row-level analysis.
- **Not loaded by default installer.** A Saudi deployment must import `currentLanguage_utf8.sql` post-install, or a Perl
  extraction filtered to `lang_id=22`.

---

## 4. RTL support

**Schema:** `lang_languages.lang_is_rtl TINYINT DEFAULT 0` — column comment names Arabic explicitly
(`sql/database.sql:3561`). Populated by `sql/4_2_1-to-4_2_2_upgrade.sql:131-133`. Read at runtime by
`getLanguageDir()` (`library/translation.inc.php:240`) and at DB-upgrade time (`sql_upgrade.php:107`).

**Bootstrap-RTL asset:** fetched at npm-install time via the `napa` postinstall hook — **direct GitHub zip URL to a pinned
commit of a third-party fork** (`package.json:112-113`):

```
"bootstrap-rtl": "https://github.com/PerseusTheGreat/bootstrap-4-rtl/archive/643a8f9e221ed86729b51913d1a7d5614e615682.zip"
```

Also referenced by `config/config.yaml:38-39` (asset link) and hard-coded in `library/dialog.js:499` and
`interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/BaseController.php:81`.
**Flag:** the fork appears unmaintained (single commit hash, pinned to Bootstrap 4). Sustainability risk — if that GitHub
archive URL goes away or the pinned commit is force-pushed, `npm install` breaks. See
`scripts/install-assets.js:76-91` for how napa dependencies are copied.

**Built RTL stylesheets:**
- `interface/themes/oemr-rtl.scss` — top-level entry, imports the bootstrap-rtl SCSS bundle.
- `interface/themes/oemr_rtl_compact_imports.scss:8-21` — enumerates all bootstrap-rtl partials.
- `interface/themes/rtl.scss` — OpenEMR-specific RTL overrides, `@mixin rtl_style` at line 6, `direction: rtl` at line 8/262.
- `interface/themes/directional.scss` — LTR/RTL conditional mixins (`if-rtl`, `@function if-rtl`, `@mixin if-rtl` at lines
  22, 63, 68). Consumed by `tabs_style_full.scss:466`, `tabs_style_compact.scss:375,466`, `core/therapy-groups.scss:205`,
  `core/tabs.scss:106`, `core/patient/report_custom.scss:65`.
- **Pre-built PDF RTL CSS:** `interface/themes/rtl_style_pdf.css` (2000+ lines, ships in-repo).

**Assessment:** RTL infrastructure exists at the CSS/build layer. Whether every screen renders correctly is not verifiable
statically — `UNKNOWN — requires runtime QA in Arabic against every module`.

---

## 5. Date / calendar

**Zero Hijri support.** Grep for `Hijri`, `Islamic`, `Umm al-Qura`, `HijriDate`, `intlcal_create_calendar`,
`IntlDateFormatter`, `moment-hijri` across `**/*.{php,js,json}` returns only false positives (`napa` race-code substrings,
`momentum` in comments, an Iranian Farsi translation string mentioning "near point of accommodation"). **No calendar
abstraction beyond Gregorian exists anywhere in the codebase.**

**Server-side date formatting:** `library/date_functions.php` (autoloaded). The only top-level function is `dateformat()`
at line 22. It is a `match` on language name (`sql/database.sql:3569` etc.) with hard-coded Gregorian day/month layouts —
`library/date_functions.php:43-54`. Hebrew is explicitly documented as "displays English calendar, NOT Jewish calendar"
(`library/date_functions.php:19`). No branch for Arabic; falls through `default` — `"$nom $day_num, $year"`, Gregorian.
Day/month names come from `OpenEMR\Common\Calendar\DayOfWeek` and `Month` enums (`library/date_functions.php:3-4`) —
Gregorian-only.

**JS side:** `moment 2.30.1` (`package.json:102`). `moment-hijri` is **NOT** in `package.json` or `package-lock.json`
(confirmed by grep). Moment itself has no built-in Hijri support.

**PHP `ext-intl`** is required by the environment but no code uses `IntlCalendar` / `IntlDateFormatter` for Hijri conversion.

---

## 6. Currency

**`moneyphp/money`** IS used, but only in the Rainforest payment integration and the FHIR Money element:

- `src/PaymentProcessing/Recorder.php:15,225` (uses `ISOCurrencies`, `DecimalMoneyFormatter`).
- `src/PaymentProcessing/Rainforest/Api.php:17,88` — reads `->getCurrency()->getCode()` off inbound webhook payloads.
- `src/PaymentProcessing/Rainforest/Webhooks/RecordPayment.php:15,95,101` — instantiates `new Currency($data['currency_code'])`.
- `src/PaymentProcessing/Rainforest/Apis/GetPayinComponentParameters.php:80,83` — **`$usd = new Currency('USD')` hard-coded**.
- `src/PaymentProcessing/Rainforest/EncounterData.php:16,72`.
- `src/Services/FHIR/FhirCoverageService.php:294` — **`$valueMoney->setCurrency('USD')` hard-coded**.

Everywhere else, money is a raw `decimal(12,2)` — e.g. `billing.fee decimal(12,2)` (`sql/database.sql:266`),
`prices.pr_price decimal(12,2) NOT NULL default '0.00' COMMENT 'price in local currency'` (`sql/database.sql:8764`).

**No `currency` column** exists on `billing`, `insurance_companies`, `fees`, or any other financial table
(grep for `\`currency\`` returned only two comments that mention "local currency" as a schema note, not an actual column).

**Currency configuration is display-only:** `library/globals.inc.php:784-822` defines four globals:
- `currency_decimals` (default `2`)
- `currency_dec_point` (period/comma)
- `currency_thousands_sep`
- `gbl_currency_symbol` — free-text, **default `'$'`** (`library/globals.inc.php:820`)

**USD/dollar hard-codes** in `src/`, `library/`, `interface/` PHP: 6 files match `\bUSD\b`
(`Get-ChildItem | Select-String -List` count = 6): `src/PaymentProcessing/Rainforest/Apis/GetPayinComponentParameters.php`,
`src/PaymentProcessing/Recorder.php`, `src/Services/FHIR/FhirCoverageService.php`, `library/classes/OFX.class.php`,
`interface/main/ippf_export.php`, `interface/patient_file/front_payment_cc.php`.

**Multi-currency assessment:** No. Financial tables have no currency column; the only currency-aware code path is
inbound Rainforest webhooks parroting back the caller's currency. Everything user-visible funnels through a single
site-wide `gbl_currency_symbol` string with no per-transaction currency identity.

---

## 7. Timezone

**Default:** `date_default_timezone_set('UTC')` in `bootstrap.php:30`. Overridden per-site if `gbl_time_zone` is set in
`globals` — `interface/globals.php:520` calls `date_default_timezone_set($gl_value)` when reading
`gbl_time_zone` (`interface/globals.php:515`).

**Global config:** `gbl_time_zone` global defined in `library/globals.inc.php:777-782` — list built by `gblTimeZones()`.
Read elsewhere: `src/Telemetry/TelemetryService.php:174-175`, `interface/main/tabs/main.php:150` (pushed to
`jsGlobals.timezone`), `portal/home.php:399`, telehealth module (`TelehealthGlobalConfig.php:67`).

**Per-user timezone:** **None.** The `users` table schema (`sql/database.sql:9786-9855`) has no `timezone`, `tz`, or
locale column. All users of a site share `gbl_time_zone`. `UNKNOWN — whether the product needs per-user timezone for
Saudi (single-country deployment) is a product decision`.

---

## 8. VAT / tax fields in billing

**No dedicated tax-rate table.** Grep for `CREATE TABLE .tax` returned no rows.

**Tax rate registry is via `list_options`:**
- `list_id='taxrate'` — meta-list declaration (`sql/database.sql:4354`, dating from `sql/2_8_3-to-2_9_0_upgrade.sql:461`).
- Actual rate rows go into `list_options` where `list_id='taxrate'`, one row per rate name.

**Product/service side references rates by colon-delimited name-list:**
- `codes.taxrates varchar(255)` — "tax rate names delimited by colons" (`sql/database.sql:1135`, comment at
  `sql/2_8_3-to-2_9_0_upgrade.sql:201`).
- `drug_templates.taxrates varchar(255)` (`sql/database.sql:1587`, `sql/2_8_3-to-2_9_0_upgrade.sql:206`).

**Transaction side has NO tax column.** `billing` (`sql/database.sql:245-278`) has no `tax`, `vat`, `tax_amount`, or
`tax_rate` column. `fee_sheet_options` (`sql/database.sql:1944`) — not shown but grep across the file for `vat` returned
zero hits.

**Facility-level tax ID:** `facility.tax_id_type varchar(31)` (`sql/database.sql:1868`) — a *taxpayer ID type* field, not
a tax rate.

**Assessment for Saudi VAT (15% ZATCA):**
- The rate registry (list_options taxrate rows) can hold a "VAT 15%" entry.
- BUT: no tax-amount fields on billing rows, no linkage to ZATCA e-invoice fields (invoice UUID, hash, QR code, XML
  signature), no `tax_registration_number` on `insurance_companies` or `facility`, no `taxable_amount` column.
- ZATCA Phase 2 (Fatoora) e-invoicing compliance would require: new tax-rate table with effective dates, per-line-item
  tax columns on `billing`, invoice-hash/UUID/QR-code fields, XML signature workflow. `UNKNOWN — full ZATCA scope
  requires product-owner input`.

---

## 9. Frontend i18n (i18next)

**Frontend bootstrap** at `interface/main/tabs/main.php:318` loads the `i18next` header asset, and lines 335-349 initialize
i18next by calling `setupI18n(languageChoice)` and passing the returned JSON into `i18next.init({resources:{selected:
{translation: translationsJson}}})`. The translations JSON is **fetched from the backend**, i.e. sourced from the same
`lang_*` tables — there is no separate frontend translation catalogue on disk.

**Confirmed by exhaustive search:** `**/locales/**/*.json` glob returned zero files. There are no per-locale JSON bundles
in `public/` or `interface/`.

**Portal & other init sites:**
- `templates/portal/home.html.twig:104` — patient portal calls `i18next.init({...})`.
- `portal/patient/templates/OnsiteDocumentListView.tpl.php:127`.
- `library/js/utility.js:15-22` — a JS-side `xl()` wrapper that delegates to `top.i18next.t()`.

**DWV DICOM viewer** ships its own copy: `library/js/dwv/dwv_i18n.js` uses `i18nextXHRBackend` and
`i18nextBrowserLanguageDetector` — that one *does* expect on-disk JSON files (loaded via XHR from the DWV asset dir).
`UNKNOWN — locations of DWV locale JSON files not enumerated this pass`.

**Verdict:** Frontend i18n reuses backend translations. Once Arabic rows are loaded into `lang_definitions`, the frontend
picks them up automatically — no separate JS translation deployment needed.

---

## 10. Saudi-market readiness — summary

| Feature | Present? | Evidence | Effort to close gap |
|---|---|---|---|
| Arabic UI (backend `lang_*`) | Partial — infra yes, data not shipped | `sql/database.sql:3569` ships EN only; `contrib/util/language_translations/currentLanguage_utf8.sql:49` has `(22,'ar','Arabic',1)` and translations, not auto-loaded | Low — import filtered dump post-install; QA every screen. Consider curating Saudi-Arabic (vs. MSA) terminology. |
| Arabic UI (frontend i18next) | Yes — reuses backend | `interface/main/tabs/main.php:335-349` fetches from backend; no separate JSON catalogue | Zero once backend Arabic is loaded |
| RTL layout (CSS) | Yes — Bootstrap-RTL fork + custom SCSS | `package.json:112-113`, `interface/themes/oemr-rtl.scss`, `interface/themes/rtl.scss`, `directional.scss` mixins | Medium — bootstrap-rtl fork is a pinned zip from an unmaintained third-party GitHub; every screen needs Arabic RTL QA; forms, tables, PDF headers all suspect |
| Hijri calendar | **No** | Grep across entire tree for Hijri/Islamic/moment-hijri/IntlCalendar returned zero hits; `library/date_functions.php:43-54` is hard-Gregorian with no Arabic branch | **High** — needs a `HijriDate` value type, `moment-hijri` or PHP IntlCalendar wrappers, dual-calendar pickers in the UI, DOB/appointment/lab-date display switches, print-report formatters |
| SAR currency | Partial — only as free-text symbol | `library/globals.inc.php:820` default is `'$'`; set `gbl_currency_symbol='SAR'`. `src/Services/FHIR/FhirCoverageService.php:294` hard-codes `'USD'` | Low for display; Medium if FHIR Coverage/Money resources need SAR — fix hard-coded USD in FHIR service, decide policy for Rainforest USD constant |
| Multi-currency | **No** | No `currency` column on `billing` / `insurance_companies` / `fees` (`sql/database.sql:245-278`); only per-site `gbl_currency_symbol` | High — schema migration on every financial table, backfill, UI, reports. Unlikely needed for single-country KSA deployment |
| VAT / tax fields | Partial — rate registry only | Rates via `list_options list_id='taxrate'` (`sql/database.sql:4354`); `codes.taxrates` colon-list (`sql/database.sql:1135`); NO tax columns on `billing` | **High** for ZATCA compliance — no per-invoice tax amounts, no invoice hash/UUID/QR code, no XML signature, no seller/buyer VAT number fields |
| Timezone (Riyadh) | Yes — site-wide only | `bootstrap.php:30` UTC default; `library/globals.inc.php:777` `gbl_time_zone` config; `interface/globals.php:520` applies it | Low — set `gbl_time_zone='Asia/Riyadh'`. No per-user timezone if that matters. |
| Right-to-left PDFs | Partial | `interface/themes/rtl_style_pdf.css` (2000+ lines) ships; whether every PDF template uses it, and font-embed for Arabic script, `UNKNOWN — requires per-report runtime QA` | Medium — Arabic glyph rendering in mPDF/wkhtmltopdf requires Arabic-shaping fonts (Amiri, Noto Naskh) bundled and referenced |

---

## UNKNOWNs (require product-owner input or later phases)

1. **Row-level Arabic completeness** — how many of the ~9,400 constants have non-empty Arabic translations in
   `contrib/util/language_translations/currentLanguage_utf8.sql`. Not counted this pass.
2. **RTL rendering fidelity** per screen — the CSS scaffold exists but no runtime QA has been done. Legacy forms
   (Smarty-era) especially suspect.
3. **DWV DICOM viewer locale JSON** — location and Arabic coverage not enumerated.
4. **PDF Arabic font embedding** — whether the shipped mPDF/wkhtmltopdf configs bundle Arabic-shaping fonts.
5. **ZATCA e-invoicing scope** — Phase 1 (basic tax invoice) vs. Phase 2 (Fatoora integration, XML signing, QR code,
   real-time clearance) is a business decision.
6. **Hijri calendar UX policy** — Hijri-primary, Gregorian-primary, or dual-display; which fields (DOB? appointments?
   lab dates? billing? all?).
7. **Saudi-Arabic vs. Modern Standard Arabic** terminology — the bundled Arabic translations are generic MSA/mixed;
   medical/administrative terminology preferences for the Saudi market are unspecified.
8. **Per-user timezone / language preference** — currently site-wide (`gbl_time_zone`, session `language_choice`);
   whether multi-tenant or multi-user overrides are needed is undecided.
9. **`bootstrap-rtl` fork sustainability** — the pinned GitHub zip is a single-commit archive of a third-party fork; if
   that URL disappears the build breaks.
10. **Hard-coded `USD` in FHIR Coverage** (`src/Services/FHIR/FhirCoverageService.php:294`) and Rainforest
    (`GetPayinComponentParameters.php:83`) — policy on whether to fix or ignore.
