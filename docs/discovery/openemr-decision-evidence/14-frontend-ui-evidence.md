# 14 — Frontend UI Evidence (Q59: theming + tenant branding)

_Auditor: opencode. Mode: READ-ONLY. Fork SHA: `631f2b38cf633769c305233f88cdf9c73ca80657`. OS: Windows 10 / PowerShell 5.1. No composer/npm/docker/DB._

Scope is narrow: this file closes **Q59 — whether `sites/<tenant>/documents/theme/` has runtime behaviour** and inventories the actual runtime paths for theme + branding assets. General frontend UI (shell, iframes, portal, menu, i18n, DICOM) is not re-covered here — see prior audit `docs/00-discovery/09-frontend-ui.md`.

Companion artefact: `evidence/snippets/q59-theme-runtime-path-table.md`.
Command log: `22-command-log.txt`. Raw grep captures: `evidence/raw/*.txt`.

---

## 1. Executive

1. **`sites/<tenant>/documents/theme/` has NO runtime behaviour** — 0 matches in `**/*.php`, `**/*.twig`, `**/*.js`; the string exists only in prior discovery docs (`docs/00-discovery/{15-upgrade-and-patch-strategy,SUMMARY-open-questions}.md`).
2. Themes are **shared immutable CSS files** under `public/themes/` (webpack output), selected by filename via `globals.css_header` (site-wide) and optionally overridden per-user via `user_settings.setting_label='global:css_header'`. Filesystem `file_exists()` gate at `interface/globals.php:476` blocks arbitrary filenames.
3. Per-tenant branding today = **logos only** — via `LogoService` reading `sites/<site>/images/logos/<type>/logo.*` (`src/Services/LogoService.php:75-108`) plus legacy hard-coded `{OE_SITE_WEBROOT}/images/{login_logo.gif,logo_1.png,logo_2.png,practice_logo.gif}`.
4. The single per-site executable extension seam is **`sites/<site>/config.php`** (`interface/globals.php:649` — `require_once` of arbitrary PHP). No per-site `.css` / `.js` file is included at runtime anywhere in the tracked codebase.
5. Theme dropdown is populated by a **filesystem scan** of `public/themes/` (`interface/super/edit_globals.php:714-731`), not from a `list_options.list_id='themes'` seed — grep for `list_id.{0,3}themes` in `sql/*.sql` returns **0 matches**.

---

## 2. Runtime asset resolution

See `evidence/snippets/q59-theme-runtime-path-table.md` for the full table (14 rows, 10 columns). Summary rows:

| Asset type | Runtime path shape | Tenant-specific? | User-specific? | Executable? | Uploadable? | Fallback | Primary evidence |
|---|---|---|---|---|---|---|---|
| Core theme CSS | `public/themes/{name}.css?v={v_js_includes}` | No (shared) | Yes (via `user_settings`) | No (CSS) | No | `style_light.css` → `style_default.css` | `interface/globals.php:474-483,634` |
| Tabs-shell theme CSS | `public/themes/tabs_style_{name}.css` | No | Yes | No (CSS) | No | `tabs_style_full.css` | `library/globals.inc.php:190-194`, `interface/super/edit_globals.php:710-746` |
| Portal theme CSS | `public/themes/{name}.css` | No | Yes (`patient_settings.portal_theme`) | No (CSS) | No | Site default | `interface/globals.php:486-495` |
| RTL theme substitution | `public/themes/rtl_{name}.css` | No | Session lang-dir | No (CSS) | No | Non-RTL variant | `interface/globals.php:551-593,597-611` |
| Login logos (primary/secondary/tiny) | `sites/{site}/images/logos/core/login/{type}/logo.*` (LogoService) or `public/images/logos/...` | **Yes** | No | No (image) | Filesystem drop, no UI writer found | Empty string | `src/Services/LogoService.php:75-108`, `interface/login/login.php:61-65` |
| Menu logo | `sites/{site}/images/logos/core/menu/primary/logo.*` | **Yes** | No | No | Same | Empty | `interface/main/tabs/main.php:47-48` |
| Favicon | `sites/{site}/images/logos/core/favicon/favicon.ico` | **Yes** | No | No | Same | Empty | `src/Core/Header.php:137-138` |
| Portal logos | `sites/{site}/images/logos/portal/{login\|menu}/{primary\|secondary}/logo.*` | **Yes** | No | No | Same | Empty | `portal/index.php:62-64`, `portal/home.php:87,362` |
| Legacy per-site images | `{OE_SITE_WEBROOT}/images/{login_logo.gif,logo_1.png,logo_2.png}` | **Yes** | No | No | Filesystem drop | Broken `<img>` (no `file_exists` on most emit sites) | `interface/globals.php:688,691,692` |
| Practice / statement logo | `{OE_SITE_DIR}/images/{statement_logo}` filtered by `convert_safe_file_dir_name()` | **Yes** | No | No | Existing admin | `practice_logo.gif` | `sites/default/statement.inc.php:87-89`, `interface/patient_file/report/custom_report.php:202,211` |
| Per-site config | `sites/{site}/config.php` `require_once`d after globals load | **Yes** | No | **YES (PHP)** | Filesystem only | Missing → next steps see undefined values | `interface/globals.php:649` |
| Per-site custom menu JSON | `sites/{site}/documents/custom_menus/{file}.json` | **Yes** | No | No (parsed as JSON) | Existing admin UI | Falls to `interface/main/tabs/menu/menus/{role}.json` | `src/Menu/MainMenuRole.php:55` |
| Compiled frontend theme CSS (build) | `public/themes/{entry}.css` (webpack output, `.gitignore:17`) | No | No | No | No | Build required | `webpack.themes.js:70-72,148-218` |

Additional definitions:

- `OE_SITE_DIR` = filesystem-absolute — `{OE_SITES_BASE}/{site_id}` — set at `interface/globals.php:332`.
- `OE_SITE_WEBROOT` = URL-relative — `{web_root}/sites/{site_id}` — set at `interface/globals.php:335`.
- `themes_static_relative` = URL-relative — `{web_root}/public/themes` — set at `interface/globals.php:354` (also `src/RestControllers/Config/RestConfig.php:130`).
- `site_id` is a session value populated by the multi-site router (out of scope here; see `docs/00-discovery/10-multisite-multitenant.md`).

### Theme selection precedence (verified)

Order actually applied at `interface/globals.php:437-483` and `:649`:

1. Load `globals` table row for `css_header` (site default).
2. Overlay `user_settings` row `global:css_header` if present for the current `authUserID` (lines 437-450, 464-469) — **per-user override wins.**
3. Validate: `file_exists({webserver_root}/public/themes/{value})`; on false, force `style_light.css` (lines 476-478).
4. Optionally substitute an `rtl_` prefixed variant if session `language_direction === 'rtl'` **and** the current theme name does not already contain `rtl` **and** the `rtl_` file exists (lines 551-593).
5. `require_once({OE_SITE_DIR}/config.php)` at line 649 — this per-site PHP file may unconditionally re-assign `$GLOBALS['css_header']` (or any other key). This is the only path by which the resolved CSS URL can be re-pointed per-tenant without a code patch, and it costs a full PHP execution seam.

### Theme dropdown source

`interface/super/edit_globals.php:714-731`: `opendir($themedir=public/themes)` and `preg_match("/^style_.*\.css$/")` (or `/^tabs_style_.*\.css$/`). No DB seed:

- `grep -R "list_id.{0,5}themes" sql/**.sql` → **0 hits** (`evidence/raw/themes_list.txt` empty; see command log). The "shipped user-selectable theme count" is therefore the count of `public/themes/style_*.css` after `webpack.themes.js` runs — the entry list in `webpack.themes.js:151-218` enumerates **12 `style_*` entries** (4 base × {LTR, compact, RTL, RTL-compact} = 16 base variants **plus** 2 colour variants × 4 = 8 → 24 style bundles; **plus** 4 `tabs_style_*` bundles; **plus** `style.scss`, `style_pdf.scss`, `directional.scss`, and 5+5 misc — matches the "17 top-level SCSS files" from the prior audit `09-frontend-ui.md:129` once one accounts for `?variant=` re-use).

### Webpack pipeline (from `webpack.themes.js`)

- Single config object (`themesConfig`) exported by `module.exports = (env, argv) => [themesConfig]` (line 145).
- **Entry count: 41** distinct entries (lines 151-218): 4 oe-styles × 4 orientations (16), 2 colours × 4 orientations (8), 2 tabs × 2 orientations (4), 3 root-level (style, style_pdf, directional), 5 misc × 2 orientations (10). Every one maps 1:1 to a CSS bundle emitted under `public/themes/` (line 71: `outputThemes`) with `[name].css` filename (line 257).
- Same `.scss` source is re-used across LTR/compact/RTL/rtl_compact via `sass-bsimport-loader` reading a `?variant=` query (helper `entry()` at line 138-141).
- Output dir is a constant `public/themes`; there is **no mechanism in the pipeline to add per-tenant SCSS entries at build time or serve a per-tenant compiled bundle at runtime** — a tenant SCSS file would require editing `webpack.themes.js` and rebuilding the whole shared bundle.

---

## 3. Q59 direct answer

> **Does `sites/<tenant>/documents/theme/` have runtime behaviour? — NO.**

Evidence (`evidence/raw/documents_theme.txt`, `evidence/raw/sites_docs_theme.txt`):

- Literal grep for `documents/theme` across `**/*.php`, `**/*.twig`, `**/*.js`: **5 matches, all in `docs/00-discovery/*.md`** — the prior audit's own open question and the upgrade-strategy doc that raised the hypothesis.
- No PHP `require`/`include`, no Twig `{% include %}`, no JS `<script>`/`<link>`, no `readdir`/`glob`/`file_exists` targets any `documents/theme` path.
- The actual per-site branding surface (proven present) is `sites/{site}/images/` (both the legacy flat layout and the newer `LogoService`-scanned `sites/{site}/images/logos/<type>/` tree — the latter shipped in `sites/default/` as README stubs, see `git ls-files "sites/default/images"`).

**Implication for the discovery corpus:** the earlier hypothesis in `docs/00-discovery/15-upgrade-and-patch-strategy.md:317,324,451` and `SUMMARY-open-questions.md:399,401` is unsupported by the code. Per-tenant *theme CSS* is not a supported runtime concept; per-tenant *logos* are (via `sites/<site>/images/logos/...`).

---

## 4. Security risk analysis for tenant-supplied CSS/JS

Framed against the proven runtime paths. "Applicable" = there is a proven ingestion or execution path in tracked code today; "Latent" = would apply if a naive per-site `.css` include were added; "Not applicable" = no code path exists.

### 4.1 XSS via `<script>` in a site-controlled file

- **Not applicable for CSS files** — no per-site `.css` is included at runtime (`evidence/raw/public_themes.txt` shows all `<link>`-emit sites resolve to `public/themes/...` — no `sites/*.css`).
- **Applicable, high-severity for `sites/{site}/config.php`** — `interface/globals.php:649` unconditionally `require_once`s this file. Anyone who can write it has full PHP execution in the OpenEMR context (session, DB, filesystem, network). This is not a tenant-uploadable path today — access is filesystem/root-level — but it is the exact mechanism that would need to be replaced by a *validated* branding table to be tenant-safe.
- **Latent for logos** — `LogoService::findLogo` matches by regex `logo.*` (`src/Services/LogoService.php:141-159`), so a file named `logo.svg` would be served as-is. SVG can contain `<script>` and inline event handlers; a tenant with write access to `sites/{site}/images/logos/{type}/` could ship an XSS payload if the front-end embeds the SVG via `<object>`/`<iframe>` or inline. Emit sites use `<img>` (`interface/login/login.php` renders via `login_screen.php` template, and portal/main via Twig `logo` filter → `TwigExtension.php:274-278`) — `<img src="…svg">` in browsers does not execute embedded scripts, so **de facto not exploitable through the current emit paths**, but adding an inline-SVG emit path would make it exploitable.

### 4.2 Data exfiltration via CSS `background-image: url(https://…)`

- **Not applicable today** — no per-tenant CSS is served. All served CSS is from the built-in `public/themes/*.css` shared bundles.
- **Latent** — if per-site CSS override were added, `background-image: url(https://evil…)` in a tenant CSS would trigger a request from the victim's browser carrying the OpenEMR session cookie (SameSite defaults apply) and any URL fragments visible to CSS selectors. This is the classic reason why "tenant-supplied CSS" is a whole-tenant-scoped XSS class, not just a styling feature.

### 4.3 Cross-tenant asset leakage via shared bind-mount

- `public/themes/` is a shared named volume (`themevolume:/var/www/localhost/htdocs/openemr/public/themes:rw` — `docker/development-easy/docker-compose.yml:37`, plus the same in `development-easy-light` and `development-easy-redis`). This is the **build-artefact** dir — one theme bundle per site is impossible in this layout.
- Per-site files live under `sites/{site}/` (a separate site tree). Grep confirms every branding write path uses `OE_SITE_DIR` or `OE_SITE_WEBROOT` — both scoped to `{web_root}/sites/{site_id}` — so **cross-tenant asset leakage via the branding paths is not present today** provided the multi-site router pins `site_id` correctly (see `docs/00-discovery/10-multisite-multitenant.md`).
- Risk **would** appear if a hypothetical per-site CSS were placed under `public/themes/{site_id}/…` — the shared themevolume makes that a mount-point that every site's container would see and cache-bust identically.

### 4.4 Cache poisoning at reverse proxy

- Cache-buster is `?v={v_js_includes}` (a build constant from `version.php`, currently `82` per `02-repository-baseline.md`). Appended at every theme URL emit (`interface/globals.php:479,480,494,587,605`).
- A tenant-varying URL would need to include the tenant identifier in the URL to avoid a proxy that keys only by URL serving Site-A's CSS to Site-B. Today's shared `public/themes/{name}.css?v=82` URL is safe **only because the content is not tenant-specific.**
- **Not applicable today; latent if per-tenant CSS were added at a `public/themes/…` URL.**

### 4.5 Upgrade conflicts if per-site override matches an upstream filename

- Not applicable today (no per-tenant CSS override path).
- Latent: if per-site override lived at `sites/{site}/documents/theme/{name}.css` and were served in place of `public/themes/{name}.css`, then any upstream rename of the core file (routine — see `sql/3_2_0-to-4_0_0_upgrade.sql:1003,1007` moving `style_default.css` → `style_oemr.css` per the prior audit) would silently orphan every tenant's override. This is a known anti-pattern for filename-based per-tenant overrides.

Summary: **all five risks are either "not applicable" (no code path) or "latent" (would apply to a hypothesised per-site CSS/JS include).** The one *applicable-today* execution risk is `sites/{site}/config.php` — a filesystem-only, root/admin-only surface, not a tenant-uploadable one.

---

## 5. Recommended tenant-safe branding mechanism (findings-only)

Aligned with what is already true in the code:

1. **Keep the shared immutable theme bundles.** `public/themes/*.css` stays the sole source of stylesheet URLs. This preserves the `file_exists` gate at `interface/globals.php:476`, the cache-buster contract, and upgrade safety. Do **not** introduce per-site `.css` overrides.
2. **Per-tenant logos → keep and formalise `sites/{site}/images/logos/{type}/`.** `LogoService` (`src/Services/LogoService.php:75-108`) already implements the dual-search (public → per-site), filesystem-scoped, image-only, with an event hook (`LogoFilterEvent`). This is the safe branding surface today. The legacy hard-coded `{OE_SITE_WEBROOT}/images/{login_logo.gif,logo_1.png,logo_2.png}` sites (`interface/globals.php:688,691,692`) should be migrated to `LogoService` calls — they are functionally equivalent but skip the safety wrapping.
3. **Design tokens via CSS variables** would be the correct evolution to give tenants brand-colour control **without** giving them arbitrary CSS. This requires: (a) declaring a `:root { --oe-brand-primary: …; }` block in the shared themes; (b) rendering a tiny per-tenant `<style>:root { --oe-brand-primary: {value}; }</style>` in `templates/basic/generic_header.html.twig` from validated globals values. This is **not a runtime feature today** — grep for `--oe-` in `interface/themes/*.scss` returns 0 CSS-custom-property declarations. This is a new-code proposal, not a discovery finding.
4. **Validated branding values via a dedicated table** — e.g., `custom_saas_branding` in the tenant DB — with strict per-column validation (hex colour regex; image URL constrained to `sites/{site}/images/logos/`; string-length limits; no free-form CSS). This is the SaaS-layer piece; cross-reference §16 control-plane work in the discovery corpus.
5. **Keep the `?v={v_js_includes}` cache-buster** — `interface/globals.php:479` already implements it. Any per-tenant CSS-variable `<style>` block should live inline in the Twig header so it inherits the same page cache scope as the request itself (no separate asset URL to cache).

Explicitly **not** recommended by this audit:

- Adding a `sites/{site}/documents/theme/` per-site CSS-override path (the hypothesis of Q59) — this would introduce every latent risk in §4 (§4.2 exfiltration, §4.4 poisoning, §4.5 upgrade drift) with no capability that CSS-variable design tokens do not already give more safely.
- Any per-site `.js` include mechanism — would introduce full XSS-class §4.1 risk. The event/hook mechanism already documented (`MenuEvent`, `LogoFilterEvent`, and general Symfony event dispatchers per `docs/00-discovery/09-frontend-ui.md` §7) is the correct extension seam for tenant-varying *behaviour*.

---

## 6. UNKNOWNs

- **Shipped user-selectable theme count as observed by a running instance** — requires `ls public/themes/style_*.css` after `webpack.themes.js` build; only the webpack entry list (24 `style_*` + 4 `tabs_style_*`) is derivable statically. The `edit_globals.php` filesystem scan means the number changes with the build, not with an SQL seed.
- **Whether any custom module in `interface/modules/custom_modules/` ships its own `<link>` to `sites/{site}/…` at runtime** — grep across `interface/modules/custom_modules/**/*.php` for `sites/.*\.css|sites/.*\.js` returns 0 matches within the audit's grep scope, but the tracked custom_modules subtree contains vendored third-party code with its own layouts (e.g. `oe-module-comlink-telehealth`, `oe-module-faxsms`, `oe-module-weno`) — a per-module deep-dive would confirm.
- **Whether `LogoService`'s SVG-serving behaviour is exercised anywhere** — `Finder->name('logo.*')` matches `.svg`; if the emit path is ever changed from `<img>` to inline-SVG (or `<object>`), §4.1 becomes exploitable. Not exploitable today per the emit-site inspection but worth an ongoing lint rule.
- **Behaviour of `patient_settings.setting_label='portal_theme'`** override — proven present at `interface/globals.php:488-493` but the write path (which admin/portal UI sets it) was not enumerated in this audit.
