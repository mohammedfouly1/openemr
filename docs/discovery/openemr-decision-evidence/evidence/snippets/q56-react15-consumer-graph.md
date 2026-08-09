# Q56 — React 15 consumer graph + full Napa/Composer dependency-ownership analysis

**Investigator agent context:** batch §9 of mission spec. READ-ONLY. FORK_SHA `631f2b38…`. `node_modules/` empty.

Companion CSVs (join at synthesis time):
- `evidence/manifests/08b-js-dependency-runtime-inventory.csv` (this batch — JS/napa)
- `docs/discovery/openemr-decision-evidence/08-dependency-runtime-inventory.csv` (Batch A3 — different schema, PHP module ownership). The two CSVs cover disjoint components; join by `component`/`module` name where they overlap.

---

## 1. Q56 direct answer

**React 15 is an `active_runtime_dependency`, not dead code.** *(confidence: high)*

It is loaded via `<script>` tags emitted by `library/options.inc.php:4815` (`lbf_canvas_head()`), which is invoked from `interface/forms/LBF/new.php:446` — the Layout-Based Forms editor page — whenever a form field of `drawable-image` type is rendered. React is present only to satisfy LiterallyCanvas 0.4.14 (`package.json:118`), which internally uses React 15's `React.createElement`/`ReactDOM.render`.

**No first-party OpenEMR code imports React.** All `React.createElement` / `ReactDOM.render` hits in tracked code are inside `swagger/swagger-ui*.js` bundles (SwaggerUI's own webpacked bundle, self-contained; those refs do NOT load the napa `react/build/*.min.js`). See `evidence/raw/react-consumer-search-hits.txt` for the raw hit list.

---

## 2. React 15 consumer graph

```
declared: package.json:119 (napa)
   "react": "https://github.com/facebook/react/releases/download/v15.1.0/react-15.1.0.zip"
→ downloaded by: napa (postinstall — package.json:7)
   → node_modules/react/                               (default napa destination)
→ copied by: scripts/install-assets.js
   → NO react-specific branch. Falls through the generic branch at
     scripts/install-assets.js:101-109. The v15.1.0 zip ships a `build/`
     directory (no `dist/`), so `copyAll()` copies the entire package
     (line 108) to public/assets/react/.
→ static asset at: public/assets/react/build/react-with-addons.min.js
                   public/assets/react/build/react-dom.min.js
→ referenced by:
   - library/options.inc.php:4815
        (function lbf_canvas_head() emits <link>/<script> tags for
         literallycanvas.css, react-with-addons.min.js, react-dom.min.js,
         literallycanvas.min.js)
   - library/options.js.php:310
        (imageURLPrefix pointing to literallycanvas/img — companion to
         the react-hosted widget)
→ browser entry point:
   - interface/forms/LBF/new.php:446    <?php echo lbf_canvas_head(); ?>
     (the Layout-Based Forms editor; triggered when a drawable-image
      field is present in the form definition)
```

**Note on false-positive matches** (see `react-consumer-search-hits.txt`):

- `swagger/swagger-ui*.js`, `swagger/swagger-ui*.js.map`, `swagger/swagger-ui-es-bundle-core.js` etc. — these are the pre-built SwaggerUI browser bundles which internally use React, but they bundle their own React. They do NOT load `/public/assets/react/*` and are unaffected by the napa react entry.
- `interface/forms/eye_mag/js/eye_base.php:3176-3180` — a local variable named `react` (as in "pupillary reactivity"), unrelated to React.js.
- `interface/forms/eye_mag/report.php:1001`, `view.php:1239` — translation string `xlt('react{{reactivity}}')`, unrelated.
- `contrib/util/language_translations/*` — translation table entries for the same "reactivity" string.
- `Documentation/api/SMART_ON_FHIR.md:1081-1082` — example code showing `react-native-app-auth` in a markdown snippet; not runtime.
- `interface/modules/zend_modules/public/js/lib/jquery.ui.tabs.js:954` — code comment "// Don't react to nested tabs".
- `interface/forms/questionnaire_assessments/lforms/webcomponent/*.js.map` — Angular-built lforms bundle refers to `react` in some source-map paths; the lforms binary itself is Angular, not React.

---

## 3. Evidence trail (every citation)

| Fact | Cite |
|---|---|
| Napa declares React 15.1.0 zip | `package.json:119` |
| Postinstall runs napa + install-assets.js | `package.json:7` |
| install-assets.js copies whole pkg if no `dist/` | `scripts/install-assets.js:101-109` |
| install-assets.js has NO react-specific case | (absence — no line in scripts/install-assets.js matches `react`) |
| React 15 `<script>` tags emitted for browser | `library/options.inc.php:4815` |
| Function that emits them | `library/options.inc.php:4813` (`lbf_canvas_head`) |
| Browser entry that calls the function | `interface/forms/LBF/new.php:446` |
| `getAssetsRelative()` → `public/assets` | `src/Core/Kernel.php:97`; `src/RestControllers/Config/RestConfig.php:128` (`assets_static_relative` = `webroot/public/assets`) |
| LiterallyCanvas image-prefix companion ref | `library/options.js.php:310` |
| webpack.themes.js has no React entry | (absence — `git grep -i react -- webpack.themes.js` returns nothing) |

---

## 4. Removal-safety analysis for React 15

**If we remove `react` from `package.json:119`:**

- `npm install` (and the postinstall hook) will no longer place `node_modules/react/` on disk.
- `scripts/install-assets.js` will not copy anything to `public/assets/react/`.
- Any subsequent HTTP GET for `/public/assets/react/build/react-with-addons.min.js` from a browser rendering `interface/forms/LBF/new.php` (with a drawable-image field) returns 404.
- LiterallyCanvas fails to boot in the browser (its `.min.js` also 404s, since `literallycanvas` is loaded from the same asset root and has the same napa-download pattern).
- Any Layout-Based Form containing a drawable-image (`data_type=…drawable…`) field type breaks in the LBF editor.

**Coupled removal:** React 15 and `literallycanvas` (`package.json:118`) MUST be removed together — either both stay or both go. Both are unmaintained since 2016. Path forward would be a modern signature/canvas widget (e.g., signature_pad — already used elsewhere per `library/options.inc.php:4806`) replacing the LBF drawable-image field type in `library/options.inc.php`.

**Files whose behavior changes on removal:**
- `library/options.inc.php:4813-4815` (`lbf_canvas_head`) — would need rewrite or deletion.
- `library/options.js.php:310` — imageURLPrefix reference would dangle.
- `interface/forms/LBF/new.php:446` — call site of `lbf_canvas_head()` still safe (returns empty string is fine) but the field rendering elsewhere in `library/options.inc.php` for the drawable image type must be updated.
- Search `library/options.inc.php` around line 1530 for the drawable-image field type wiring — comment there references `lbf_canvas_head`.

**Data risk:** any existing drawable-image data stored in EMRs would need a migration path or read-only viewer.

---

## 5. Verdicts for other napa entries, composer repositories entries, and DWV

### Napa (all 8 entries in `package.json:112-121`)

| Napa key | Classification | Consumer count | Key evidence | Notes |
|---|---|---|---|---|
| `bootstrap-rtl` | **active_runtime_dependency** | many | `config/config.yaml:38-39`; `interface/themes/oemr-rtl.scss:5`; `interface/themes/oemr_rtl_compact_imports.scss:8-21+`; `library/dialog.js:499` | **SUSTAINABILITY RISK.** Pinned to a single commit (`643a8f9e…`) of an unmaintained third-party fork (`PerseusTheGreat/bootstrap-4-rtl`). No upstream releases; the fork owner may delete the repository without notice. Compare with `bootstrap` 4.6.2 (official, npm) at `package.json:72`. |
| `jquery-creditcardvalidator` | active_runtime_dependency | 2 | `interface/patient_file/front_payment.php`; `portal/portal_payment.php` | Small unmaintained plugin; tagged v1.1.0 download is stable. |
| `jquery-panelslider` | active_runtime_dependency | 1 | `interface/forms/eye_mag/view.php` | Used only by one form. Candidate for replacement. |
| `jquery-ui` | active_runtime_dependency | many | `config/config.yaml:80-82` | Downloaded from `jqueryui.com` directly (not GitHub). jQuery UI has been in maintenance-only mode since 2021. |
| `jquery-ui-themes` | active_runtime_dependency | 3+ | `config/config.yaml:87-102`; `interface/themes/core/cursor.scss:21`; `templates/super/rules/controllers/browse/plans_config.php:26` | Multiple named themes referenced. |
| `literallycanvas` | active_runtime_dependency | 2 | `library/options.inc.php:4815`; `library/options.js.php:310` | Coupled with React 15. EOL May 2016. |
| `react` | active_runtime_dependency | 2 | `library/options.inc.php:4815`; (also `library/options.js.php:310` via companion) | **Q56 answer.** EOL v15.1.0 (Jun 2016). |
| `lforms` | **dead_dependency** | **0** | (absence — `git grep 'assets[/\\]lforms'` returns nothing) | **REMOVE-SAFE napa entry.** lforms IS used at runtime, but from a VENDORED, git-checked-in copy at `interface/forms/questionnaire_assessments/lforms/` (28 tracked files including `webcomponent/lhc-forms.js` and `fhir/*/lformsFHIR.min.js`). The napa-downloaded copy at `public/assets/lforms/` has zero consumers. |

### Composer `repositories[]` entries (`composer.json:161-170`)

| repo URL | Any package requires it? | Classification | Evidence |
|---|---|---|---|
| `https://github.com/openemr/wkhtmltopdf-openemr` (`composer.json:164`) | **NO — not in composer.lock** (findstr `wkhtmltopdf` returns only `knplabs/knp-snappy` description text at composer.lock:2878, no matching package entry with that name) | **broken_declaration** (repositories entry references code that IS consumed at runtime, but no `require`s pull it in) | `composer.json:161-165`; `composer.lock` (no matching entry); consumer at `src/Pdf/PdfCreator.php:38` reads binary from `getVendorDir() . "/openemr/wkhtmltopdf-openemr/bin"` — **so runtime PDF generation depends on this directory existing**, but nothing in Composer's dependency graph will create it. Suggests the package is required externally (Docker image bakes it in? A missing `require` in root composer.json?). **HIGH-PRIORITY finding — recommend explicit `require openemr/wkhtmltopdf-openemr` in composer.json.** |
| `https://github.com/openemr/oe-module-cqm` (`composer.json:167-168`) | **NO — not in composer.lock, and zero consumers anywhere** | **dead_dependency** (repositories declaration with no require and no consumer) | `composer.json:166-169`; `git grep 'oe-module-cqm\|oe_module_cqm\|ModuleCqm'` in `src/`, `library/`, `interface/`, `modules/`, `composer.lock` returns zero hits |

### DWV

Cross-references Batch A5 (DWV consumer analysis). Not duplicated here. `package.json:87` (`dwv 0.27.1`) is a regular npm dependency (not napa); `install-assets.js:87-90` special-cases it (copies `dist/`, `decoders/`, `locales/`). See Batch A5 for consumer map.

---

## 6. Evidence-blocked items

- **Confirm actual runtime browser load.** Cannot execute the app (no Docker per task rules). Cannot confirm the LBF drawable-image field type is used by any active layout in seed data. If **zero forms in the DB use `data_type=drawable-image`**, then `lbf_canvas_head()` is called but rendered React scripts are never actually needed by any user — reducing React 15 from `active_runtime_dependency` to `active_only_if_field_used`. Recommend a DB query at synthesis time: `SELECT * FROM layout_options WHERE data_type IN (<drawable-image type ids>);`.
- **Docker image contents.** Whether the OpenEMR Docker image bundles `vendor/openemr/wkhtmltopdf-openemr/bin/` from an external step (Dockerfile RUN, or a separate `git clone`) is not verifiable from source alone. Recommend inspecting `docker/**/Dockerfile*` at synthesis.
- **Actual napa download behavior for the react zip.** The v15.1.0 GitHub release zip's top-level directory structure was not verified (would require download). Analysis assumes it contains `build/react-with-addons.min.js` and `build/react-dom.min.js` because the consumer at `library/options.inc.php:4815` reads from those exact paths and works in production.
