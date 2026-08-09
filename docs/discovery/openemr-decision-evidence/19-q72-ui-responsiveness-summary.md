# 19 — Q72: UI Responsiveness & RTL Inventory (Summary)

_Auditor: opencode (read-only). Fork SHA `631f2b38...`. All counts derived from
static source inspection — Docker/DB/PHP toolchain not executed._

## Artifacts

| Path | Purpose |
|------|---------|
| `18-q72-ui-responsiveness-inventory.csv` | One row per first-party UI file (17 columns) |
| `tools/discovery/openemr-decision-evidence/scan-ui-inventory.py` | Reproducible scanner |
| `evidence/raw/q72-scanner-output.json` | Raw per-file JSON (input to CSV) |
| `evidence/raw/q72-file-list.txt` | Exact set of scanned files (fingerprint below) |
| `evidence/raw/q72-scanner-exclusions.txt` | Excluded paths + rationale |

**Reproducibility fingerprint** — `sha256(q72-file-list.txt)` =
`eeaee99e60392dff40a968d5961e552812904bdbee842d5715f4d50f359d776f`

## Method

1. Enumerated tracked files via `git ls-files` (8,745 total).
2. Filtered by extension (`.php .phtml .html .twig .mustache .js .jsx .ts .tsx .vue .scss .css`), then applied exclusion rules from §15.1 of the mission spec:
   - Top-level: `vendor/`, `node_modules/`, `public/assets/`, `Documentation/EHI_Export/`, `.git/`, `.webpack-cache/`, `tmp/`, `tmp-phpstan/`, `docs/`, `**/*.min.{js,css}`.
   - Bundled JS under `library/js/`: `jquery-*`, `knockout-*`, `bootstrap*`, `dwv/` (except `dicom_launcher.js`), `summernote-*`, `select2*`, `datatables*`, `moment*`, `chart*`, `backbone*`, `underscore*`, `purecss*`, `dropzone*`, `flot*`, `ckeditor*`, `vendors/`.
   - **Additional exclusions applied after first-pass review** (documented in `q72-scanner-exclusions.txt` and inline in the scanner):
     - `swagger/` (bundled swagger-ui third-party assets).
     - Vendor CSS drops under `interface/modules/zend_modules/public/css/`: `easyui.css`, `jquery-ui.css`, `jquery.contextMenu.css`, `jquery.custom-scrollbar.css`, `jquery.treeview-*/`, `slider/`, `multipledb/`.
     - Built module assets: `interface/modules/custom_modules/oe-module-comlink-telehealth/public/assets/`, `interface/modules/zend_modules/public/js/`.
     - Any path segment containing `/dist/`, nested `/vendor/`, or nested `/node_modules/`.
   - `contrib/util/**` retained as legacy first-party (spec).
3. For each retained file, regex-scanned line by line for the six §15.2 signal sets (`grid`, `responsive`, `fixed_width`, `direction`, `rtl`, `iframe`), recording per-signal **match count** and **line numbers** (line numbers truncated to 20 per signal in CSV).
4. Classified each file per §15.3. Built cross-file relations:
   - `included_by[target]` from static `include`/`require` literals (best-effort tail-match against the included set).
   - `iframe_or_tab_launcher` from `<iframe src="…">` literal matches.
   - Menu labels harvested from `interface/main/tabs/menu/menus/**/*.json` (133 URL→label entries).
5. Applied a targeted reclassification pass to move non-UI backend PHP (`src/**`, `tests/**`, `tools/**`, `sites/**`, `.phpstan/**`, `ccdaservice/**`, `apis/**`) from `unknown` into `unknown` with a `HIGH`-confidence explanatory note and `manual_review_required = FALSE`. This is compliant with §15.3 (`unknown` is the sink bucket for anything not on the enumerated list; the notes column explains what it actually is).

## Reconciled totals

**Total first-party UI files scanned = 5,460** (= CSV row count).

### Classification breakdown (must sum to total)

| Classification | Count | % |
|---|---:|---:|
| `unknown` (mostly non-UI first-party PHP; see below) | 3,111 | 57.0 |
| `shared_template` | 1,098 | 20.1 |
| `custom_module_screen` | 416 | 7.6 |
| `legacy_iframe_included_file` | 288 | 5.3 |
| `legacy_standalone_page` | 287 | 5.3 |
| `patient_portal` | 187 | 3.4 |
| `administration_screen` | 67 | 1.2 |
| `legacy_iframe_entrypoint` | 5 | 0.09 |
| `modern_shell` | 1 | 0.02 |
| **SUM** | **5,460** | **100.0** |

Formula: `Σ classification = 5460 = total_rows` ✓

Note on the large `unknown` bucket: 2,998 of those 3,111 rows are backend PHP files under `src/**`, `tests/**`, `tools/**`, `sites/**`, `.phpstan/**`, `ccdaservice/**`, `apis/**`, or root-level installer scripts — they are first-party and included by the extension filter, but they do not render UI in the browser. The scanner marks them `unknown, HIGH confidence, manual_review_required=FALSE` with an explanatory note. Removing them would leave **2,462 UI-rendering rows** (`5460 − 2998`).

### Signal / risk counts

| Metric | Count | Notes |
|---|---:|---|
| Files containing valid grid classes (`grid_patterns >= 1`) | **1,149** | See "prior 611" reconciliation below |
| Files with responsive breakpoint patterns (`@media`/`col-md-`/`d-md-`/…) | 286 | |
| Legacy iframe entrypoints | 5 | See list below |
| Files launched by an iframe entrypoint (via detectable `<iframe src=>` join) | 47 | Best-effort; understates truth because many iframe `src` are built at runtime from JS/PHP variables |
| Modern shell files | 1 | `interface/main/tabs/main.php` only (Twig `base.twig` files exist under `templates/` but none matched the strict layout heuristic) |
| Shared templates | 1,098 | Includes 284 `.twig`, 180 `.html`, 104 `.mustache`, 74 `.scss`, 48 `.css`, plus JS modules and library PHP helpers |
| Custom-module screens | 416 | `oe-module-*` under `interface/modules/custom_modules/` + the `zend_modules/` subtree |
| Patient portal screens | 187 | `portal/**` |
| Administration screens | 67 | `interface/usergroup/`, `interface/super/`, `interface/main/administration/`, plus `gacl/admin/` |
| Mobile-risk HIGH (`fixed_width>=3 AND grid==0`) | **19** | |
| Mobile-risk MEDIUM (`fixed_width>=1` OR `legacy_standalone_page` with no grid) | 291 | |
| Mobile-risk (HIGH + MEDIUM) | **310** | |
| RTL-risk HIGH (`direction>=5 AND rtl==0`) | **62** | |
| RTL-risk MEDIUM (`direction>=1 AND rtl==0`) | 187 | |
| RTL-risk (HIGH + MEDIUM) | **249** | |
| `manual_review_required = TRUE` | 408 | Almost entirely `legacy_iframe_included_file` heuristics that could not confirm HTML output at parse time |

### Extension breakdown of scanned files

| ext | count |
|---|---:|
| `.php` | 4,519 |
| `.twig` | 284 |
| `.js` | 206 |
| `.html` | 180 |
| `.mustache` | 104 |
| `.scss` | 74 |
| `.css` | 48 |
| `.phtml` | 45 |
| **SUM** | **5,460** |

## Reconciliation with the prior "611 files use grid classes" claim

The prior audit (`docs/00-discovery/09-frontend-ui.md:324-326`) recorded:

> `findstr /S /I /R /C:"col-md-[0-9]" /C:"col-lg-[0-9]" /C:"container-fluid" interface\*.php` **→ 611 matching lines**.

This was **verified verbatim in this phase** (see command log entry for the direct `findstr` re-run) — the query still returns exactly **611 matching lines, across 108 distinct files**, in the current tree.

**Verdict: the "611" number is correct but its unit was mis-described as "files" in the phrasing at 09-frontend-ui.md:474 and 09-frontend-ui.md:495.** It is a **line count**, not a file count, and it is scoped to (a) only `interface/**/*.php`, (b) only three of the many Bootstrap grid patterns.

This phase's broader scanner (all first-party UI file extensions, full Bootstrap 4 grid + utility pattern set) finds:

- **1,149 files** contain at least one grid/utility pattern.
- Of those, **869 are `.php`**, **155 are `.twig`**, **72 are `.js`**, **34 are `.html`**, **26 are `.phtml`**, **19 are `.css`**, **14 are `.scss`**.
- Restricting to the prior query's scope (`.php` under `interface/**`, only the three prior patterns) reproduces **108 files / 611 lines**.

The wider count includes noise from **`\brow\b`** matching SQL/PHP tokens like `sqlQueryFetchRow`, the word "row" in comments, and array-key strings. The most extreme false-positive is `interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php` with `grid=553` — a data-access class with hundreds of SQL `row`/`rows` tokens and no Bootstrap markup. This is a known limitation of line-based regex scanning; correcting it would require a Twig/HTML class-attribute parser. The CSV column `matched_line_numbers` is preserved so any consumer can spot-check the actual matches per file.

## Legacy iframe entrypoints (5)

| Path | Detected iframe tags | Notes |
|---|---:|---|
| `interface/main/main_screen.php` | 0 (frameset uses `<frame>` not `<iframe>`) | Historical frameset host; classified by path rule |
| `interface/main/tabs/main.php` | reclassified as `modern_shell` (§15.3 explicit rule) | The Knockout+Twig tab shell — this is the modern entrypoint |
| `interface/main/tabs/templates/patient_data_template.php` | 0 | Under `interface/main/tabs/` so caught by path rule; is a template partial, not a true iframe host — LOW confidence, should be moved to `modern_shell` on manual review |
| `interface/patient_file/encounter/encounter_top.php` | 2 | Legit iframe host for encounter forms |
| `src/Services/DocumentTemplates/DocumentTemplateRender.php` | 3 | Server-side renderer that emits `<iframe>` markup into documents |
| `templates/documents/general_view.html` | 3 | Twig document viewer with 3 iframes |

## Manual inspection sample

### Top 20 highest-risk legacy / mobile-risk screens

1. **`interface/billing/ub04_form.php`** — Legacy UB-04 institutional claims paper form. `fixed_width=435, direction=716, line_count=1420`. Verdict: **NOT mobile-viable, NOT RTL-safe**. Print-oriented pixel-perfect form; will require complete redesign for either concern.
2. **`interface/forms/bronchitis/new.php`** — Encounter form. `fixed_width=41`, zero grid. Old-school HTML `<table>` layout. Mobile: FAIL. RTL: OK (no direction properties).
3. **`interface/forms/bronchitis/view.php`** — Read-only twin of the above. Same verdict.
4. **`interface/modules/custom_modules/oe-module-comlink-telehealth/templates/comlink/emails/telehealth-invitation-existing.html.twig`** — Transactional email HTML (deliberately fixed-width). Email HTML doesn't need mobile grids; low actual risk despite score.
5. **`interface/modules/custom_modules/oe-module-comlink-telehealth/templates/comlink/emails/telehealth-invitation-new.html.twig`** — Same category as #4.
6. **`interface/themes/style_pdf.scss`** — Print/PDF stylesheet. Fixed widths are correct for print media; low real risk.
7. **`interface/forms/eye_mag/a_issue.php`** — Ophthalmology "issues" screen, 1,415 lines of legacy HTML+PHP. Mobile: FAIL.
8. **`interface/modules/zend_modules/public/css/bubbletip.css`** — Tooltip widget CSS with fixed sizing. Standalone widget — narrow blast radius.
9. **`interface/themes/misc/edi_history_v2.scss`** — EDI 837/835 history viewer stylesheet, fixed cell widths. Print-adjacent screen.
10. **`interface/themes/oe-common/main-common.scss`** — Shared theme partial with a few pixel widths in header rules.
11. **`interface/modules/zend_modules/public/css/emr.css`** — Zend module base CSS.
12. **`interface/modules/zend_modules/module/Ccr/view/ccr/ccr/revandapprove.phtml`** — CCR review-and-approve view. Table-driven layout.
13. **`library/js/CategoryTreeMenu.js`** — In-app tree menu component; emits inline `width:...px` styles.
14. **`library/js/DocumentTreeMenu.js`** — Sibling of #13.
15. **`interface/forms/ankleinjury/new.php`** — Injury encounter form; small file (244 lines) but same legacy pattern.
16. **`interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml`** — CCDA reconciliation screen, 1,788 lines. High-touch clinical workflow.
17. **`interface/modules/zend_modules/module/PrescriptionTemplates/view/prescription-templates/default.phtml`** — Small admin screen.
18. **`interface/themes/color_base.scss`** — Theme color helpers, a few pixel widths.
19. **`interface/themes/core/cursor.scss`** — Cursor rules with pixel offsets. Trivial.
20. **`interface/themes/core/tabs.scss`** — Tab widget rules with pixel widths.

Pattern: the true mobile-risk mass is the **encounter-form family** (`interface/forms/*/new.php`, `view.php`) plus billing (`ub04_form`) and the `eye_mag` optometry family. Emails and print stylesheets score high but are not real risks.

### 10 modern shell / top templates by grid usage

1. **`interface/main/tabs/main.php`** — The Knockout+Twig main shell. THE modern entrypoint (`modern_shell`).
2. **`templates/super/facilities/form.html.twig`** (grid=70) — Admin facilities form; well-adopted Bootstrap grid.
3. **`interface/forms/observation/templates/observation_edit.html.twig`** (grid=66) — Observation form edit view.
4. **`interface/forms/observation/templates/observation_list.html.twig`** (grid=56) — Observation list view.
5. **`templates/portal/portal-credentials-settings.html.twig`** (grid=55) — Portal user credentials screen.
6. **`templates/patient/demographics/relation_form.html.twig`** (grid=52) — Modern patient demographics — related-persons form.
7. **`templates/interface/smart/admin-client/edit.html.twig`** (grid=49) — SMART-on-FHIR client admin.
8. **`templates/patient/demographics/address_form.html.twig`** (grid=45) — Patient address form.
9. **`templates/patient/insurance/_insurance_edit_screen_edit.html.twig`** (grid=44) — Insurance edit form (partial).
10. **`templates/patient/insurance/_insurance_edit_screen_new.html.twig`** (grid=44) — Insurance new-policy form (partial).

These are the templates where the modern Twig+Bootstrap adoption is deepest — worth using as reference exemplars.

### 10 Arabic/RTL-relevant screens

1. **`interface/themes/oemr-rtl.scss`** — RTL entrypoint theme; imports `bootstrap-rtl`. `rtl=2, direction=0`.
2. **`interface/themes/oemr_rtl_compact_imports.scss`** — Compact-mode RTL imports. `rtl=14`.
3. **`interface/themes/rtl.scss`** — Core RTL adjustments (`@mixin rtl_style` — flips margin/padding/text-align/float per §13). `rtl=4, direction=31`. **This is the mixin RTL-risk files SHOULD apply.**
4. **`interface/themes/rtl_style_pdf.css`** — RTL PDF stylesheet. `rtl=4, direction=123`.
5. **`src/FHIR/SMART/SMARTLaunchToken.php`** — Path contains "RTL" only because "SMART" — false-positive on path substring. No UI content. IGNORE.
6. **`src/FHIR/SMART/SmartLaunchController.php`** — Same as #5.
7. **`src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php`** — Same false-positive.
8. **`tests/Tests/Unit/FHIR/SMART/SMARTLaunchTokenTest.php`** — Same.
9. **`interface/billing/ub04_form.php`** — top RTL-risk score (dir=716, rtl=0). See #1 in mobile section.
10. **`interface/forms/eye_mag/css/style.css`** — Ophthalmology stylesheet. `direction=203, rtl=0`. Not RTL-safe.

Real RTL exposure: the **`interface/forms/eye_mag/**` family** (report.php:176, php/eye_mag_functions.php:140, view.php:77) and **billing/ub04_form** are the top RTL-hostile screens; none reference the RTL mixin.

### 10 custom module screens (one per module where possible)

1. **`interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php`** — Data-access model class. `grid=553` is a false positive (`row` in SQL). Not a UI file despite the extension.
2. **`interface/modules/custom_modules/oe-module-faxsms/src/Notification/AppointmentNotificationRunner.php`** — Backend notification runner. `grid=59` is likely noise (SQL `row`). Not UI.
3. **`interface/modules/custom_modules/oe-module-dorn/public/lab_setup.php`** — DORN lab-orders module setup screen. `grid=55, fw=2, dir=2`. Real Bootstrap use; low mobile risk.
4. **`interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php`** — ACL controller class; `grid=46` is noise.
5. **`interface/modules/custom_modules/oe-module-dashboard-context/src/Services/DashboardContextAdminService.php`** — Service class; `grid=43` is noise.
6. **`interface/modules/custom_modules/oe-module-weno/templates/weno_setup.php`** — Weno eRx module setup screen. `grid=39`. Actual Bootstrap use.
7. **`interface/modules/zend_modules/module/Ccr/view/ccr/ccr/index.phtml`** — CCR module index view. `grid=26, fw=2, dir=10`.
8. **`interface/modules/zend_modules/module/Installer/view/installer/installer/index.phtml`** — Zend installer module index.
9. **`interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/SyndromicsurveillanceTable.php`** — Model class; grid noise.
10. **`interface/modules/custom_modules/oe-module-comlink-telehealth/welcome.php`** — Telehealth welcome page.

Observation: the `custom_module_screen` classification currently catches both `.php` view files AND backend service/model classes under `interface/modules/*/*/src/`. A future refinement could split these, but does not affect the mobile/RTL-risk metrics because the noisy backend files score `LOW` risk (their grid noise inflates `grid_patterns` but they have `fixed_width=0, direction=0`).

### All DICOM screens

Only **3 files**:

1. **`library/dicom_frame.php`** — PHP wrapper that launches the DICOM viewer. Zero UI signals.
2. **`library/js/dwv/dicom_launcher.js`** — The one JS file kept from the bundled `dwv/` tree per spec. Launcher only; no UI markup.
3. **`templates/dicom/dicom-viewer.html.twig`** — The Twig viewer template. `grid=4, fw=2, dir=22, rtl=0`. Uses `left:`/`right:` positioning for overlay controls — RTL: MEDIUM-risk if Arabic support is required.

Prior audit's UNKNOWN "PHP entry that includes `dicom_launcher.js`" (`09-frontend-ui.md:494`) is partially closed by finding `library/dicom_frame.php` here — but this scanner did not confirm the include-chain from `interface/patient_file/documents/` (that requires JS/template parsing).

### NPHIES / SaaS / Saudi / ZATCA / Arabic screens

**None found.** Path-substring scan for `nphies`, `saas`, `saudi`, `zatca`, `arabic` returned **zero files** in the first-party inventory. This fork is not Saudi-localised at the source-tree level. Any Arabic/RTL support would come from OpenEMR's generic RTL theme infrastructure (§13 audit + `interface/themes/rtl.scss`), not from a dedicated NPHIES module.

## EVIDENCE-BLOCKED items

- **True iframe hierarchy** — many `<iframe src="…">` attributes in this codebase are built at runtime from JS/PHP variables (e.g., `main.php` composes tab URLs from Knockout view-model + menu JSON). Only 47 iframe→child edges resolved via literal string match. The real graph is larger; measuring it requires either a JS runtime or a Knockout-aware parser.
- **Regex noise on `\brow\b`** — inflates `grid_patterns` for many backend PHP files with SQL. Real fix requires HTML/Twig class-attribute parsing (an AST-level scan, not line regex). The CSV keeps `matched_line_numbers` so any consumer can spot-check.
- **CKEditor / Summernote / DataTables built-in RTL support** — these are excluded as bundled third-party libs, so their RTL capability doesn't show in the counts. Confirming whether OpenEMR configures them for RTL requires reading module-init JS (not part of this scan).
- **Mustache templates (104 files)** — classified as `shared_template` uniformly; per-file semantic role (e.g., portal partial vs shell) is not distinguished.

## Signal-pattern reference (as used)

Regexes are stored verbatim in `metadata.signal_patterns` inside `q72-scanner-output.json`. Summary:

| Signal | Pattern (abridged) |
|---|---|
| `grid` | `\bcol(-(xs|sm|md|lg|xl))?(-(auto|\d+))?\b` \| `\brow\b` \| `\bcontainer(-fluid)?\b` \| `\bd-(none\|inline\|block\|flex\|…)\b` \| `\bflex-(row\|column\|…)` \| `\bjustify-content-` \| `\balign-items-` \| `\bg-[0-5]\b` |
| `responsive` | `@media` \| `col-{sm,md,lg,xl}-` \| `d-{sm,md,lg,xl}-` \| `d-none.*d-` |
| `fixed_width` | `<table\s+[^>]*width=` \| `width:\s*\d+px` \| `width="\d+"` \| `style="[^"]*width:\s*\d+px` \| `<img\s+[^>]*width=` |
| `direction` | `margin-{left,right}` \| `padding-{left,right}` \| `text-align:\s*{left,right}` \| `float:\s*{left,right}` \| `{left,right}:\s*\d` \| `border-{left,right}` |
| `rtl` | `dir="{rtl,ltr}"` \| `direction:\s*{rtl,ltr}` \| `\.rtl\b` \| `is_rtl` \| `IS_RTL` \| `getRtl` \| `bootstrap-rtl` \| `rtl_style_pdf` |
| `iframe` | `<iframe` \| `document.write` \| `window.open` \| `newwindow` \| `frameborder` |

## Reproducibility

To regenerate from a clean tree (from repo root; requires only Python 3.10+ and Git):

```
python tools/discovery/openemr-decision-evidence/scan-ui-inventory.py \
  --root D:/OpenEmr \
  --out-json        docs/discovery/openemr-decision-evidence/evidence/raw/q72-scanner-output.json \
  --out-file-list   docs/discovery/openemr-decision-evidence/evidence/raw/q72-file-list.txt \
  --out-exclusions  docs/discovery/openemr-decision-evidence/evidence/raw/q72-scanner-exclusions.txt

python tools/discovery/openemr-decision-evidence/scan-ui-inventory.py --emit-csv \
  --in-json  docs/discovery/openemr-decision-evidence/evidence/raw/q72-scanner-output.json \
  --out-csv  docs/discovery/openemr-decision-evidence/18-q72-ui-responsiveness-inventory.csv
```

`sha256(q72-file-list.txt)` must be `eeaee99e60392dff40a968d5961e552812904bdbee842d5715f4d50f359d776f` at fork SHA `631f2b38...` for byte-identical reproduction (before the reclassification pass, which is not part of the scanner and would need to be re-applied separately if consuming a fresh scan).
