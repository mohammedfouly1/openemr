# Phase 9 — Frontend UI Discovery

Scope: main-UI shell, theming, iframe topology, patient portal, mobile
responsiveness, RTL/Arabic reality, menu extensibility, rich-text editor,
DICOM viewer, and evidence-based effort classes for frontend strategy.

Repo wins. Read-only. All findings cited `file:line`.

---

## 1. Main UI shell

### Entry point

`interface/main/tabs/main.php` — 577 lines, the single top-level HTML
document for the authenticated clinical/admin UI. There is no
`main_screen.php` under `interface/main/tabs/`; `main.php` is the entry.
(A legacy `main_screen.php` reference survives only in a `list_options.apps`
default value at `interface/main/tabs/main.php:478`.)

Top of file — `interface/main/tabs/main.php:22-97`:

- `require_once .../globals.php` (loads `OEGlobalsBag`, ACL, session).
- CSRF token round-trip: `token_main` query param must match
  `token_main_php` session key or the session is destroyed and the login
  screen is shown (`main.php:78-87`). Prevents deep-link / refresh into
  `main.php`.
- Builds a `TwigContainer` (`main.php:95`) — the shell renders **six Twig
  templates** for its major regions (tabs, menu, JSON menu payload,
  therapy-group data, user-data, product-registration modal —
  `main.php:396-410, 527`).
- `MainMenuRole` is instantiated with the Symfony event dispatcher
  (`main.php:407`), which is what makes the menu extensible (see §7).

### Shell structure — Knockout-driven tab container, not a `<frameset>`

There is **no HTML `<frameset>`** in `main.php`. The historical OpenEMR
frameset was replaced with a Knockout MVVM tab manager. The visible shell
is (`main.php:485-526`):

1. `<nav class="navbar navbar-expand-xl navbar-light bg-light py-0">`
   with Bootstrap 4 collapsable menu, populated by a Knockout template
   binding: `data-bind="template: {name: 'menu-template', data: application_data}"` (`main.php:503`).
2. `#attendantData` — patient/user context strip (KO template).
3. `#tabs_div` — the tab-strip control (KO template `tabs-controls`).
4. `#mainFrames_div` → `#framesDisplay` — the tab-body container
   (`main.php:524-525`), which KO expands into **one `<iframe>` per open
   tab** (see §3).

Knockout is loaded via `Header::setupHeader(['knockout', 'tabs-theme', ...])`
at `main.php:318`. The view-models live at:
`interface/main/tabs/js/{tabs_view_model,application_view_model,user_data_view_model,patient_data_view_model,frame_proxies}.js`
(`main.php:365-373`). Bindings applied by `ko.applyBindings(app_view_model)`
at `main.php:536`.

### Menu JSON files

`interface/main/tabs/menu/menus/` contains 4 role-menu JSON files plus a
`patient_menus/` subdir:

| File | Role |
|------|------|
| `standard.json` | 2307 lines — default provider menu |
| `chart_review.json` | Chart-review restricted role |
| `front_office.json` | Front-office role |
| `answering_service.json` | Answering-service role |
| `patient_menus/*.json` | Per-role patient-chart submenus (separate — loaded by `src/Menu/PatientMenuRole.php:68`) |

Loader logic — `src/Menu/MainMenuRole.php:53-58`: if the configured
`main_menu_role` global ends with `.json`, load from
`OE_SITE_DIR/documents/custom_menus/<file>.json`; otherwise load
`interface/main/tabs/menu/menus/<role>.json`. **Site-local custom menus are
therefore a first-class extension mechanism** — no core changes required to
ship a per-tenant menu.

Menu-item schema (`standard.json:2-17`):

```json
{
  "label":  "Calendar",
  "menu_id":"cal0",
  "target": "cal",
  "url":    "/interface/main/main_info.php",
  "children": [],
  "requirement": 0,
  "acl_req":            ["patients","appt"],
  "global_req_strict":  ["!disable_calendar","!ippf_specific"]
}
```

- `target` — the KO tab name; opening the item creates an `<iframe
  name="cal">` inside `#framesDisplay`.
- `acl_req` — pair of ACL section/subsection strings passed to
  `AclMain::aclCheckCore`. Item is hidden if the user lacks access.
- `global_req_strict` — list of `OEGlobalsBag` keys the item requires.
  Prefix `!` inverts the check (item is shown only if the flag is
  falsy). `requirement: 0` = always required to pass; higher values
  correspond to alternative/optional evaluation modes.
- `children[]` — recursive; drives the Bootstrap dropdowns.

### Top nav bar

Rendered inline in `interface/main/tabs/main.php:486-521` (not a separate
Twig file):

- **Hardcoded:** `<button.navbar-toggler>` (mobile hamburger),
  `#anySearchBox` global-search input (`main.php:507`), `#userData` KO
  region for user avatar/logout.
- **Config-driven:** brand logo (`display_main_menu_logo`,
  `main_menu_logo_link`, `main_menu_logo_title` globals — `main.php:487-499`),
  visibility of any-patient search (`search_any_patient` global —
  `main.php:504`), portal / SMS / fax indicators (JS globals seeded from
  `oefax_enable_sms`, `oefax_enable_fax`, `portal_onsite_two_enable` —
  `main.php:130,154-155`).
- **Menu items themselves are entirely JSON-driven** via the KO
  `menu-template` binding at `main.php:503`.

Two extension hooks fire at nav render:
`RenderEvent::EVENT_BODY_RENDER_PRE` (`main.php:464`),
`EVENT_BODY_RENDER_NAV` (`main.php:519`), `EVENT_BODY_RENDER_POST`
(`main.php:567`).

---

## 2. Theming

### SCSS entry files in `interface/themes/`

17 top-level `.scss` files (`glob interface/themes/*.scss`), grouped:

| File | Role |
|------|------|
| `style.scss` | Master light theme (compiles to `style_light.css` per webpack) |
| `oe-bootstrap.scss` | Bootstrap-4 flavored theme |
| `theme-defaults.scss`, `default-variables.scss`, `color_base.scss` | Shared variable + color partials |
| `compact-theme-defaults.scss`, `oemr_compact_imports.scss` | Compact-density variant |
| `tabs_style_full.scss`, `tabs_style_compact.scss` | Chrome for the tab-shell (`Header::setupHeader(['tabs-theme'])`) |
| `login_page.scss` | Standalone login page |
| `style_pdf.scss` | Print/PDF stylesheet |
| `ajax_calendar_sass.scss` | Legacy calendar popup |
| `core.scss` | Base variables shared with legacy code |
| **`rtl.scss`** | RTL master |
| **`oemr-rtl.scss`** | OEMR RTL variant |
| **`oemr_rtl_compact_imports.scss`** | OEMR RTL compact variant |
| `directional.scss` | Logical-property mixins for LTR/RTL |

**3 of 17 top-level SCSS files (~18%) are RTL variants.** RTL is
opt-in — a user is served an RTL bundle only if `css_header` was set to a
theme whose filename contains `rtl` (`interface/globals.php:554,564,573`).

Per-user themes: `interface/themes/` also contains many _generated_ per-theme
CSS files served from `public/themes/` (see §3 output paths). The full
list of user-selectable themes is enumerated in `sql/database.sql` /
list_options seeds — **UNKNOWN — total count of user-selectable themes
requires enumerating `list_options.list_id='themes'` after DB seed**;
however the SCSS entrypoints above are the ones webpack builds.

### Webpack pipeline

`webpack.themes.js:1-80` — one Webpack config compiles **every**
`interface/themes/*.scss` into a matching `public/themes/*.css`
(`webpack.themes.js:70-72`, output dir `public/themes/`). A custom Sass
importer (`webpack.themes.js:41-65`) rewrites `public/assets/<pkg>/…` paths
to `node_modules/<pkg>/…` so webpack can build without the postinstall asset
copy. **Result: one CSS bundle per `.scss` entry — themes are not merged.**

Served path: `public/themes/<theme>.css` — see the runtime construction at
`interface/globals.php:587` (`$css_header = "$web_root/public/themes/…"`)
and the safe default at `interface/globals.php:634`
(`$css_header = "$web_root/public/themes/style_default.css"`).

### Default theme

Fallback / bootstrap default is **`style_default.css`**:

- Runtime default: `interface/globals.php:634`
  `$css_header = "$web_root/public/themes/style_default.css";`
- Compact fallback: `interface/globals.php:637` (`compact_header`).
- Historical evidence of theme migrations: `sql/3_2_0-to-4_0_0_upgrade.sql:1003,1007`
  once migrated users from `style_default.css` → `style_oemr.css`.

The DB-configured theme (`globals.css_header`, plus per-user override in
`user_settings.setting_label = 'global:css_header'`) is resolved at
`interface/globals.php:474-495` (both main-UI and portal —
`portal_css_header`).

---

## 3. Iframe pattern

### Frameset topology today

Not a nested-frameset architecture. The shell is one HTML document
(`main.php`) containing:

1. One hidden logout iframe: `main.php:469` — 0×0 helper for logout
   redirects.
2. Optional single "app1" full-window iframe: `main.php:479-480` — used
   when `session.app1` selects an alternative top-level UI (e.g., a
   completely custom module page); when active, `#mainBox` is hidden.
3. **One iframe per open tab** in the tab strip. The tab body is a
   Knockout foreach over the tabs list; each iteration renders:

   ```
   templates/interface/main/tabs/tabs_template.html.twig:38-40
       <iframe data-bind="location: $data, iframeName: $data.name, ">
       </iframe>
   ```

`findstr /S /I` for `<iframe` in `interface/main/tabs/` returns only
`main.php` (2 static iframes above). The dynamic tab-body iframes live in
one Twig template (`templates/interface/main/tabs/tabs_template.html.twig:38`).

### Depth

**2 levels max.** Top window (`main.php`) → one `<iframe>` per tab
(rendered by `tabs_template.html.twig`). Legacy pages loaded inside those
tab iframes may themselves open dialogs (`dlgopen` in
`main.php:355-362`) — those become sibling `<iframe>`s inside a modal, not
nested frames.

### Implication for SPAs

A modern SPA has three feasible shapes:

- Live inside **one tab iframe** — the SPA is a single "target" URL. It
  can navigate freely inside its own tab and post messages via
  `frame_proxies.js` (loaded at `main.php:371`) to reach `top`. Auth is
  the existing session cookie.
- Replace the shell entirely — remove `main.php`; rebuild menu, tabs,
  ACL wiring, and every module that assumes it lives in a target
  iframe. High effort.
- Become the "app1" full-window iframe (`main.php:479`) — a defined
  extension seam already; the SPA can occupy the whole viewport while
  reusing the existing session. Lower effort than replacing the shell.

---

## 4. Patient portal (`portal/`)

### Entry & stack

- `portal/index.php` — 821 lines — login / registration / password-reset
  landing. Starts a **separate** session (`SessionUtil::PORTAL_SESSION_ID`
  cookie — `portal/index.php:44-50`); does not share the main
  authenticated session.
- `portal/home.php` — 418 lines — post-login dashboard. Loaded via
  Twig: `home.php` reaches `TwigContainer` (`portal/home.php:23`) and
  renders `templates/portal/home.html.twig` (which contains the
  dashboard cards and, importantly, its own iframes for messages / docs /
  ledger — `templates/portal/home.html.twig:908,939,951`).

Templating: **Twig** (via `OpenEMR\Common\Twig\TwigContainer`). There is
also a legacy Smarty-style layer under `portal/patient/templates/*.tpl.php`
used by the older Phreeze-based JS app (`portal/patient/fwk/…`); these are
raw PHP files invoked as tpl scripts, not Smarty proper. Backbone/underscore
front-end code lives under `portal/patient/scripts/` (references at
`portal/patient/templates/OnsiteDocumentListView.tpl.php:123-126`).

### Auth — separate credentials

The portal does **not** authenticate against the `users` table. Portal
credentials live in `patient_access_onsite`:

- `portal/index.php:257` — `SELECT * FROM patient_access_onsite WHERE portal_onetime LIKE …` (one-time reset link flow).
- `portal/account/account.php:88` — `SELECT * FROM patient_access_onsite WHERE portal_login_username = ? OR portal_username = ?` (login lookup).
- Columns of interest: `portal_pwd`, `portal_pwd_status`,
  `portal_username`, `portal_login_username`, `portal_onetime`
  (`portal/account/index_reset.php:54-60`).
- Password reset flow: `portal/account/account.lib.php:418-432`.

**Verdict:** portal users are wholly separate from the `users` table. The
join back to a chart is via `patient_access_onsite.pid → patient_data.pid`
(`portal/messaging/messages.php:94`, `portal/portal_payment.php:81-82`).

### Portal REST API

`apis/routes/_rest_routes_portal.inc.php:29-45` registers portal-only
routes:

```
GET /portal/patient
GET /portal/patient/encounter
GET /portal/patient/encounter/:euuid
GET /portal/patient/appointment
GET /portal/patient/appointment/:auuid
```

Header at `_rest_routes_portal.inc.php:26-27`:
> "the portal (api) route is only for patient role
> (there is a mechanism in place to ensure only patient role can access
>  the portal (api) route)"

**However**: no PHP file _inside_ `portal/` currently calls
`/apis/default/portal/*` (`grep apis/[^/]+/portal` under `portal/` — no
matches). The portal UI still uses direct PHP endpoints (`portal/lib/paylib.php`,
`portal/get_patient_documents.php`, `portal/lib/doc_lib.php`, etc.). The
REST portal routes exist but are consumed by _external_ clients (mobile
apps, third-party portals) via OAuth2 patient-role tokens, not by the
in-repo portal.

### Reusability

- **Rebrand path:** portal already uses Twig + Bootstrap 4 and has a
  separate `portal_css_header` global (`interface/globals.php:486-495,
  605-607`). A cosmetic re-skin is a purely-CSS effort.
- **SPA replacement path:** the REST endpoints under
  `/apis/default/portal/*` cover encounter + appointment reads today
  (only 5 routes — the standard `/patient/*` FHIR/REST endpoints supply
  the rest). Documents, messaging, ledger, onsite-signing all still live
  in server-rendered PHP under `portal/` — building an SPA that replaces
  them would require either **extending the portal API** to cover
  documents, secure messages, payments and PROs, or **calling the
  standard FHIR API** with a patient-scoped SMART token.
- **Findings-level answer:** rebranding the existing portal is cheap
  (weeks). Replacing it with an SPA is expensive but avoids inheriting
  the Phreeze/Backbone legacy under `portal/patient/`. **Greenfield SPA
  is the more strategically clean option; rebrand is faster to ship.**

---

## 5. Mobile responsiveness

- Bootstrap 4.6 grid classes _are_ used inside PHP templates:
  `findstr /S /I /R /C:"col-md-[0-9]" /C:"col-lg-[0-9]" /C:"container-fluid" interface\*.php`
  returns **611 matching lines** across `interface/**/*.php`.
- Twig templates: same query against `templates\*.twig` → **110 matching
  lines**.
- Total PHP files under `interface/` (baseline): **1057** (`dir /S /B
  interface\*.php`). So responsive-grid usage is spread across a minority
  of files (line-count, not file-count — but Phase 1's finding that ~92.5%
  of `interface/*.php` are raw-echo pages predicts most are not
  responsive).
- **Fixed-width `<table width="…">`** occurrences in `interface/**/*.php`:
  **10 matches** (`findstr /S /I /N /R /C:"<table width="`). Not zero, but
  small. The dominant legacy pattern is `<table>` without `width=`
  attribute and CSS from the theme, rather than explicit percentage or
  pixel widths.

**Honest state:** the shell (`main.php`) and Twig-based templates use
Bootstrap 4 grid. The 1000+ legacy raw-echo pages under `interface/` (per
Phase 1) render inside tab iframes and are largely non-responsive fixed
layouts. Mobile-usable is the **shell** (nav collapses at `xl` per
`main.php:486`); the **content** in each tab varies file-by-file.

---

## 6. RTL / Arabic reality

- RTL SCSS confirmed: `interface/themes/rtl.scss`,
  `interface/themes/oemr-rtl.scss`,
  `interface/themes/oemr_rtl_compact_imports.scss`,
  `interface/themes/directional.scss` — 3 of 17 top-level `.scss`
  entrypoints (~18%) are RTL variants. Every LTR theme therefore does
  **not** get automatic RTL — only the three RTL-suffixed themes are
  usable in RTL.
- `interface/globals.php:554,564,573` — if the user's language is RTL
  (`getLanguageDir`) but their `css_header` filename does not contain
  `rtl`, RTL is _not_ applied. There is no automatic LTR-theme→RTL fallback.
- `bootstrap-rtl` sustainability: confirmed in Phase 12 —
  `package.json` napa entry pins
  `github.com/PerseusTheGreat/bootstrap-4-rtl/archive/643a8f9e221ed86729b51913d1a7d5614e615682.zip`
  (docs/00-discovery/13-i18n-localization.md:95). Single-commit archive
  of an unmaintained third-party fork. **Risk = high for long-term
  Arabic support.**

### PDF Arabic fonts

Searched `library/`, `public/`, `src/`, `vendor/mpdf/` for `dejavu`,
`amiri`, `noto`, `.ttf`:

- No `Amiri*`, no `Noto*`, no `DejaVu*` font files ship in the repo
  outside `Documentation/EHI_Export/schemaspy/…` (a schema-doc theme,
  unrelated to app PDFs).
- `glob **/*.ttf` returns only SchemaSpy report fonts (Source Sans Pro,
  Indie Flower, FontAwesome, Ionicons, Glyphicons).
- mpdf's own font pack (`vendor/mpdf/mpdf/ttfonts/`) is a transitive
  Composer dep and includes DejaVu Sans by default; **not shipped in
  the repo's `library/` or `public/` trees**.

**Verdict:** Arabic RTL PDF output via mpdf works out-of-the-box _only_
because mpdf's Composer-installed fonts include DejaVu Sans, which
supports basic Arabic. There are **no Arabic-typography-optimized fonts
(Amiri, Noto Naskh Arabic) shipped**. For professional Arabic PDFs, a
font vendor step is required — no evidence anyone has done that here.

---

## 7. Menu extensibility for custom modules

Event class: **`OpenEMR\Menu\MenuEvent`** — `src/Menu/MenuEvent.php:17`.

Two constants:

- `MenuEvent::MENU_UPDATE = 'menu.update'` (`MenuEvent.php:28`) — fired
  once the raw JSON menu has been parsed, before ACL filtering.
- `MenuEvent::MENU_RESTRICT = 'menu.restrict'` (`MenuEvent.php:34`) —
  fired after ACL/global filtering has produced the restrictions list.

Dispatch site: `src/Menu/MainMenuRole.php:67,72` —

```php
$updatedMenuEvent = $this->dispatcher->dispatch(
    new MenuEvent($menu_parsed), MenuEvent::MENU_UPDATE);
$tmp = $updatedMenuEvent->getMenu();
$updatedRestrictions = $this->dispatcher->dispatch(
    new MenuEvent($menu_restrictions), MenuEvent::MENU_RESTRICT);
```

Sibling events for patient-chart submenu and encounter menu:

- `src/Menu/PatientMenuEvent.php:19` (fired from
  `src/Menu/PatientMenuRole.php:81,86` — same UPDATE/RESTRICT pair).
- `src/Events/Encounter/EncounterMenuEvent.php:20`.

**How a custom module adds a menu item:** register a Symfony
event-subscriber on `MenuEvent::MENU_UPDATE`, receive the menu array,
`array_push` a new item matching the standard.json schema (§1), and call
`$event->setMenu($menu)`. No file modifications to `standard.json` are
needed. The module wire-up path is the Modules framework covered in
Phase 6.

---

## 8. CKEditor / rich text

`ckeditor5` 47.6.2 is in `package.json` per Phase 1. Repo touchpoints:

- `library/custom_template/custom_template.php:86-90` selects one of two
  configs — `ckeditor-limited` (default) or `ckeditor-nation-notes`
  (when handling nation-notes) — and loads them via
  `Header::setupHeader(['common','opener','select2','ckeditor', $ckeditorConfig])`.
- `library/custom_template/custom_template.php:138` reads
  `window.oeCKEditorConfigs.defaultConfig`.
- `library/custom_template/custom_template.php:359` — a `<textarea
  class="ckeditor …">` is the actual editor mount.

Search across `interface/`, `templates/`, `src/`, `public/` for further
`ckeditor` references returned **no matches** — the editor is used in
exactly one place: the **document/letter template editor** under
`library/custom_template/`. Patient encounter notes (`interface/forms/*`)
use their own textarea widgets, **not** CKEditor. Newcrop / eRx and other
richtext-adjacent surfaces use their own controls.

**Arabic RTL with CKEditor 5:** CKEditor 5 supports RTL languages
natively via editor.config `language: { ui: 'ar', content: 'ar' }` and
`contentsLangDirection: 'rtl'`. **No evidence** in the repo that RTL
config is passed through — `oeCKEditorConfigs.defaultConfig` would need
to be extended. UNKNOWN — whether the CKEditor bundle build includes the
Arabic UI translation package (`@ckeditor/ckeditor5-…/translations/ar.js`)
requires inspecting the built bundle under `public/`.

---

## 9. DICOM viewer (`dwv`)

- Instantiation: `library/js/dwv/dicom_launcher.js:86` — `const oemrApp
  = new dwv.App();`.
- Adjacent i18n bootstrap: `library/js/dwv/dwv_i18n.js`.
- No other `new dwv.App` sites in the repo (`grep new dwv\.App` returns 1
  file).

The launcher is invoked from the document viewer flow — **UNKNOWN — the
exact PHP entry that includes `dicom_launcher.js` was not confirmed by
grep**; likely under `interface/patient_file/documents/` per the file's
location but not proven here.

---

## 10. Effort classes — findings, not decisions

| Frontend strategy | Effort | Constraints | Evidence |
|-------------------|--------|-------------|----------|
| (a) **Restyle existing UI** — Bootstrap themes + Twig template overrides, no shell change | **Low** (weeks) | Cosmetic only; still Knockout MVVM + iframe-per-tab; still 611+ legacy PHP pages inside tabs unaffected by shell CSS | `interface/themes/*.scss` (17 entries), `webpack.themes.js:70-72` (one bundle per theme), `interface/globals.php:634` (default swap trivial) |
| (b) **New screens as Twig-rendered custom-module pages matching existing shell** | **Low–Medium** | Must live in a tab iframe; must respect ACL via `AclMain::aclCheckCore`; menu item added via `MenuEvent::MENU_UPDATE` subscriber; renders through `TwigContainer` (autoescape OFF globally per Phase 1 — module must escape explicitly) | `src/Menu/MenuEvent.php:17,28,34`, `src/Menu/MainMenuRole.php:67-72`, `interface/main/tabs/main.php:407` (menu dispatched), Phase 6 event catalog |
| (c) **Modern SPA (React/Vue/Angular) inside one main tab** | **Medium** | Lives inside a single tab `<iframe>` per `tabs_template.html.twig:38`; auth via existing session cookie or OAuth2 patient/user tokens; can post-message to `top` via `frame_proxies.js`; cannot own the top nav | `interface/main/tabs/main.php:479-480` (app1 iframe escape hatch), `main.php:371` (frame_proxies loaded), Phase 5 OAuth2 inventory |
| (d) **Modern SPA replacing the shell entirely, REST/FHIR only** | **High** (quarters) | Rebuild: menu system + JSON schema, ACL wiring, tab manager, i18n via `library/ajax/i18n_generator.php` port, Twig-rendered legacy modules (all 1057+ `interface/**/*.php` pages must be reimplemented or proxied), menu-event hook loses value | `main.php` is 577 lines wiring six Twig fragments, four KO view-models, seven event dispatch points, three ACL-driven globals loads; 1057 PHP files under `interface/`; only ~92.5% of them are raw-echo pages per Phase 1 |
| (e) **New patient portal as separate SPA** | **Medium** | Portal is already a separate app with its own session (`SessionUtil::PORTAL_SESSION_ID` — `portal/index.php:44-50`) and separate credentials in `patient_access_onsite`. Portal REST covers 5 endpoints today (`apis/routes/_rest_routes_portal.inc.php:29-45`); documents, messaging, ledger, PRO, payments would need either new API surfaces or FHIR/patient-scoped SMART. Rebrand alone is trivial (`portal_css_header` swap) | `portal/index.php`, `portal/home.php`, `apis/routes/_rest_routes_portal.inc.php`, `interface/globals.php:486-495` |

---

## Summary

- Main-UI shell = single Twig+Knockout page (`interface/main/tabs/main.php`) with **1 hidden iframe + 1 optional app1 iframe + 1 iframe per open tab** (max 2 levels deep, no legacy frameset).
- Menu is fully JSON-driven (`interface/main/tabs/menu/menus/*.json`) and extensible without core edits via `MenuEvent::MENU_UPDATE` — `src/Menu/MenuEvent.php:17`. Custom per-tenant menus supported via `OE_SITE_DIR/documents/custom_menus/*.json`.
- Themes: 17 SCSS entrypoints compiled 1:1 to `public/themes/*.css` by `webpack.themes.js:70-72`; default `style_default.css` at `interface/globals.php:634`; **only 3 (~18%) are RTL variants**, opt-in per user.
- Patient portal is a separate app with separate credentials (`patient_access_onsite`), served from `portal/`, only lightly consuming `/apis/default/portal/*` (5 endpoints); rebrand is trivial, SPA replacement is medium effort.
- Arabic reality: RTL SCSS infra exists but relies on a pinned zip of an unmaintained third-party bootstrap-rtl fork; **no Arabic-optimized fonts (Amiri, Noto Naskh) ship**; only mpdf's transitive DejaVu Sans covers Arabic — insufficient for polished output.

## UNKNOWNs

- **Total count of user-selectable themes** — requires enumerating `list_options.list_id='themes'` after DB seed; the 17 SCSS entrypoints are a lower bound on what webpack builds, not the enumerated user-facing list.
- **CKEditor 5 Arabic translation bundling** — requires inspecting built assets under `public/` (npm build not permitted in read-only run).
- **PHP entry that includes `dicom_launcher.js`** — grep for the include from `interface/patient_file/documents/` was not exhaustively confirmed; UNKNOWN — requires additional grep or product-owner input on which module owns DICOM view.
- **How many of the 611 grid-class-using files are inside tab iframes vs are new-shell code** — the count is line-based, not file-based; file-level responsiveness coverage requires per-file inspection.
