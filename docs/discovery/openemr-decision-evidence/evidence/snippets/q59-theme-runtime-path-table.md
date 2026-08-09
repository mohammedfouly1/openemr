# Q59 — Runtime-path decision table for theme/branding assets

_Fork SHA_: `631f2b38cf633769c305233f88cdf9c73ca80657`. READ-ONLY.
Cross-reference: prior audit `docs/00-discovery/09-frontend-ui.md` §2.

## Verdict on `sites/<tenant>/documents/theme/`

**NO — this path has ZERO runtime behaviour anywhere in the codebase.**

Evidence: literal grep for `documents/theme` across `**/*.php`, `**/*.twig`,
`**/*.js` returns **5 hits, all in `docs/00-discovery/*.md`** (prior audit's
own open question — Q59 origin). No PHP/Twig/JS file reads, writes, includes,
serves, or references any `sites/<tenant>/documents/theme/` path.
(evidence: `evidence/raw/documents_theme.txt`,
`evidence/raw/sites_docs_theme.txt`.)

## Runtime path table (all theme/branding asset kinds present in the repo today)

| Asset kind | Runtime resolver (file:line) | Path shape | Where value is stored | Per-tenant? | Per-user? | Executable in browser? | Tenant-uploadable via UI? | Validation at read | Fallback | Evidence |
|---|---|---|---|---|---|---|---|---|---|---|
| Main-UI theme CSS (user-selectable) | `interface/globals.php:474-483` | `{web_root}/public/themes/{gl_value}?v={v_js_includes}` | `globals.gl_name='css_header'` **+** `user_settings.setting_label='global:css_header'` override at `interface/globals.php:437-450,464-469` | **No** — theme filename picks a shared file from a shared dir. Only the *choice* is per-globals-row. | Yes — `user_settings.global:css_header` overrides site default | CSS only (`<link rel=stylesheet>` at `templates/basic/generic_header.html.twig` etc.) | **No** UI writes into `public/themes/` at runtime | `file_exists($webserver_root . '/public/themes/' . attr($gl_value))` at `interface/globals.php:476`; falls back to `style_light.css` if missing | `style_light.css` (line 477), then `style_default.css` when the globals table is absent (`interface/globals.php:634`) | `evidence/raw/css_header.txt`, `evidence/raw/public_themes.txt` |
| Main-UI compact theme CSS | `interface/globals.php:480` | `{web_root}/public/themes/compact_{gl_value}?v=...` | Derived from `css_header` | No (derived) | Yes (derived) | CSS only | No | Same `attr()` escape; no separate `file_exists` on `compact_` variant | Same as main | `interface/globals.php:480,637` |
| RTL theme substitution | `interface/globals.php:579-593` | `{web_root}/public/themes/rtl_{name}?v=...` | Session `language_direction` + globals `css_header` | No | Session-derived per-user language | CSS only | No | `file_exists($webserver_root.'/public/themes/'.$new_theme)` at line 585; logs warning + no substitution on miss | Non-RTL theme continues | `interface/globals.php:551-593` |
| Portal theme CSS | `interface/globals.php:486-495` | `{web_root}/public/themes/{gl_value}?v=...` | `globals.gl_name='portal_css_header'` **+** per-patient `patient_settings.setting_label='portal_theme'` at lines 488-493 | No | Per-patient (portal side) | CSS only | No | `attr()` escape only | Falls through to whatever `gl_value` was set | `evidence/raw/portal_css_header.txt` |
| Portal RTL theme substitution | `interface/globals.php:597-611` | `{web_root}/public/themes/rtl_{name}?v=...` | Session `language_direction` | No | Session per-portal-user | CSS only | No | `file_exists` (line 603); warning on miss | Non-RTL portal theme continues | `interface/globals.php:597-611` |
| Tabs shell theme | `library/globals.inc.php:190-194`, `Header::setupHeader(['tabs-theme'])` | `public/themes/tabs_style_*.css` | `globals.gl_name='gbl_tab_layout'` (theme dropdown via filesystem scan) | No | Yes (user_settings) | CSS only | No | filesystem scan filter `preg_match("/^tabs_style_.*\.css$/")` at `interface/super/edit_globals.php:722,730` | `tabs_style_full.css` (default in `library/globals.inc.php:192`) | `interface/super/edit_globals.php:710-746` |
| Menu logo (main UI) | `interface/main/tabs/main.php:47-48` → `LogoService::getLogo('core/menu/primary/')` | Search order (`src/Services/LogoService.php:75-95`): `public/images/logos/core/menu/primary/logo.*` **then** `OE_SITE_DIR/images/logos/core/menu/primary/logo.*`; last-match wins | Filesystem only — per-tenant image lives at `sites/{site}/images/logos/core/menu/primary/` (README stub shipped at `sites/default/images/logos/core/menu/primary/README`) | **Yes** (per-site directory) | No | Image only (`<img>`) | Presumably via admin — no code path found in this audit for a UI writer | `Finder->files()->in($paths)->name('logo.*')` — matches by regex, no MIME check | Empty string on error (line 93) | `evidence/raw/logo_service.txt`, `sites-default.txt` |
| Login primary/secondary/tiny logos | `interface/login/login.php:62-65` → `LogoService::getLogo('core/login/{primary,secondary,small_logo_1,small_logo_2}')` | same LogoService dual-path | `sites/{site}/images/logos/core/login/{...}/` | **Yes** | No | Image only | Same as above | Same as above | Empty string | `interface/login/login.php:61-65`, `src/Services/LogoService.php:75-108` |
| Portal login/menu logos | `portal/index.php:62-64`, `portal/home.php:87,362` → `LogoService::getLogo('portal/{login,menu}/{primary,secondary}')` | same LogoService dual-path | `sites/{site}/images/logos/portal/...` | **Yes** | No | Image only | Same | Same | Empty string | `evidence/raw/logo_service.txt` |
| Favicon | `src/Core/Header.php:137-138` → `LogoService::getLogo('core/favicon/', 'favicon.ico')` | same LogoService dual-path | `sites/{site}/images/logos/core/favicon/` | **Yes** | No | icon | Same | Filename fixed to `favicon.ico` | Empty | `src/Core/Header.php:137-138` |
| Legacy per-site login logo | `interface/globals.php:688,691,692` | `{OE_SITE_WEBROOT}/images/login_logo.gif`, `logo_1.png`, `logo_2.png` | Filesystem: `sites/{site}/images/*.{gif,png}` (shipped in `sites/default/images/` per `git ls-files`) | **Yes** | No | Image | Same | **None** — string concatenation, no `file_exists` before emit (though `front_payment.php:558` does check) | `<img>` renders a broken image if missing | `evidence/raw/logos.txt` |
| Practice logo on statements/reports | `sites/default/statement.inc.php:87`, `interface/patient_file/report/custom_report.php:202,211` | `{OE_SITE_DIR}/images/{practice_logo}` where `{practice_logo}` is `globals.gl_name='statement_logo'` filtered by `convert_safe_file_dir_name()` | Filesystem: `sites/{site}/images/` | **Yes** | No | Image (PDF & HTML) | Via admin (uploads pass `convert_safe_file_dir_name` normalization) | `convert_safe_file_dir_name()` (line 87); `glob(... /images/*)` on the report path | `sites/{site}/images/practice_logo.gif` (line 89) | `evidence/raw/oe_site_dir.txt` |
| Per-site config | `interface/globals.php:649` — `require_once($globalsBag->getString('OE_SITE_DIR') . "/config.php")` **after** globals load, so it can override any `$GLOBALS` value | `sites/{site}/config.php` | Filesystem (**PHP** — executable) | **Yes** | No | **YES — executed as PHP** | No UI writer; edited on filesystem only | None — arbitrary PHP is `require_once`d | `sites/default/config.php` shipped | `interface/globals.php:649`, `sites/default/config.php` |
| Per-site custom menu JSON | `src/Menu/MainMenuRole.php:55`, `PatientMenuRole.php:65` | `{OE_SITE_DIR}/documents/custom_menus/{file}.json` | Filesystem | **Yes** | No | JSON parsed only | Existing admin UI writes here | `json_decode`; ACL/global filters afterwards | Falls to `interface/main/tabs/menu/menus/{role}.json` | `evidence/raw/oe_site_dir.txt`, prior audit 09-frontend-ui.md:71 |
| Compiled frontend theme CSS (build artefact) | `webpack.themes.js:70-72,148-218` | `public/themes/{entry}.css` | Build (webpack) — not tracked (`/public/themes/*` in `.gitignore:17`) | No | No | CSS only | No | Build-time only | Missing → runtime falls back to `style_light.css` per `interface/globals.php:476` | `webpack.themes.js`, `.gitignore:17` |
| Static CSS synced from `interface/themes/*.css` | `scripts/sync-css.js:7-21` | `interface/themes/*.css` → `public/themes/*.css` | Build | No | No | CSS only | No | Autoprefixed only | — | `scripts/sync-css.js`, `interface/README.md:36` |

## Notes on tenant/user precedence

Order at `interface/globals.php:437-483`:

1. Load `globals` rows (site-wide defaults) into a working array.
2. Overlay any `user_settings` rows whose label is `global:*` (line 442,
   substring at 447) — **user override wins over site global** (lines 464-469).
3. For `css_header`: verify `file_exists({webserver_root}/public/themes/{value})`;
   if false, force to `style_light.css` (lines 476-478).
4. After globals apply, `require_once(OE_SITE_DIR . "/config.php")` at line 649
   — this per-site PHP file may unconditionally overwrite any `$GLOBALS` key.

Therefore theme selection order is: **per-user > per-site (globals row) >
per-site (config.php override) > `style_light.css` (missing-file fallback) >
`style_default.css` (no-globals-table fallback, line 634).**

## Notes on per-tenant CSS/JS injection

Grep across `**/*.php` for any string like `sites/*/*.css`, `sites/*.js`,
`OE_SITE_DIR . '.*\.css'`: **0 matches** (`evidence/raw/`). The only
per-site executable path is `sites/{site}/config.php` (see §Per-site config
row above); the only per-site static file paths that reach the browser are
under `sites/{site}/images/` (via `LogoService` or hard-coded
`OE_SITE_WEBROOT/images/*` in `interface/globals.php:688-692`).

There is **no runtime include of any `.css` or `.js` file from under
`sites/`** anywhere in the tracked codebase.
