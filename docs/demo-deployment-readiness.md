# Demo Deployment Readiness — Discovery and Certification

**Target:** `demo-openemr` · Ubuntu 24.04 LTS x86_64 · Apache 2.4 · PHP 8.3 · MariaDB 10.11 ·
2 vCPU / 8 GB RAM / 100 GB · static IPv4 · **no Docker**
**Produced:** 2026-08-15 · from the working tree at `G:\My Drive\OpenEMR`
**Revised:** 2026-08-15 (Revision 2 — source remediation pass)

---

## Revision history

| Rev | Date | Scope | Source modified? |
|---|---|---|---|
| 1 | 2026-08-15 | **Discovery and certification only.** No source, database, git, server configuration or dependency was modified. | No |
| **2** | **2026-08-15** | **Source remediation pass.** Closed `DG-005`/`B-03` with a source patch (`PR-17`); closed `DG-011`, `DG-012`, `DG-014`, `DG-015` on evidence with **no source change**; folded in operator-verified target-VM evidence closing `DG-002`, `DG-003`, `DG-007`; recorded the locked Option 3 decision closing `B-05`/`DG-006`. | **Yes — 1 file edited, 2 added, 1 governance record extended.** See §26 |
| **3 (addendum)** | **2026-08-19** | **Correction only, no source change.** `B-06`'s "the `patients\|bulk_rep` ACO does not exist — not even on the dev instance" claim (§17.1 C-2, §22, §24, §25 scoring) is **wrong as of today**: live re-verification (2026-08-19, twice independently) found six report ACOs present on this development instance, including `bulk_rep` and `op_rep`. **`B-06` itself is not closed** — the requirement is that `thiqa-branding:provision-report-acl` runs on the **target** deployment instance, which has not happened and is unaffected by what's on this dev machine. Only the "not even on dev" evidentiary detail is retracted; every score, ranking and required-step that cites B-06 is otherwise unchanged and still applies to the target. Not swept through every citing location (13+ across this file) — this is the correction of record; treat any inline "0 rows on the dev instance" text elsewhere in this file as superseded by this entry, not as independently authoritative. | No |

**Standing constraints, both revisions:** no database was modified, no server was modified, no
package was installed, no credential was read or printed, nothing was pushed and no tag was created.

**Three provenance classes are used from Revision 2 onward, and are never blended:**

- **Proven here** — verified by a command run by this analysis against this working tree or the
  running local development instance. Reproducible from the command shown.
- **`OPERATOR-VERIFIED TARGET EVIDENCE`** — supplied by the operator from the target VM
  `demo-openemr`. **This analysis did not execute any command on the VM** and cannot independently
  reproduce these; they are recorded as attested facts and labelled at every point of use.
- **Cited** — taken from an in-repo authoritative document. Where this pass independently
  re-verified one, that is stated.

**This report never prints a credential, token, key or patient value.**

---

## 0. How to read this document

Every factual claim below carries an inline evidence pointer: a file path, a `file:line`, a command
that was actually run, or a query result. Where a question could not be settled from the repository
or the live development instance, it is marked **`KNOWLEDGE GAP`** and given a numbered entry in
§23 with the exact command needed to close it.

**Two classes of claim are deliberately distinguished:**

- **Proven here** — verified by a command run during this discovery pass against this working tree
  or the running local instance.
- **Cited** — taken from an in-repo authoritative document (`docs/evidence/EV-*.md`, the Locked
  Decisions register). These are treated as authoritative per the instruction in §17, and where this
  pass independently re-verified one, that is stated.

**This report never prints a credential, token, key or patient value.** Sensitive items appear only
as `PRESENT — REDACTED` or `NOT FOUND`.

---

# 1 — Application baseline

| # | Item | Value | Evidence |
|---|---|---|---|
| 1 | Repository root | `G:/My Drive/OpenEMR` | `git rev-parse --show-toplevel` |
| 2 | Git repository | Yes; working tree clean except one untracked file (below) | `git status --porcelain` |
| 3 | Current branch | `feat/thiqa-branding-foundation` | `git rev-parse --abbrev-ref HEAD` |
| 4 | HEAD commit | `4d09baef135a3cf90dfd2e48f8163e3eb6d6b16e` | `git rev-parse HEAD` |
| 5 | Remotes | `origin` → `https://github.com/mohammedfouly1/openemr`<br>`upstream` → `https://github.com/openemr/openemr.git` | `git remote -v` |
| 6 | Upstream OpenEMR remote exists | **Yes** — `upstream` is canonical `openemr/openemr` | as above |
| 7 | OpenEMR version represented | **8.2.0** (`v_database = 541`, `v_acl = 13`, `v_js_includes = 82`) | `version.php:18-20,32,42` |
| 8 | Classification | **Fork of a release branch, with a custom module and branding layer** — see below | §1.1 |
| 10 | Uncommitted changes | **None.** `git status` reports only one untracked file | `git status --porcelain` |
| 11 | Untracked, application-critical? | **No.** The single untracked file is `Documentation/EHI_Export/docs/diagrams/tables/lists_medication.2degrees.docx` — a diagram artefact, not runtime code | `git status --porcelain` |
| 12 | Runtime files intentionally gitignored | **Yes, and this is the single largest deployment consequence** — see §4, §5, §21 | `.gitignore:9,16,17` |

## 1.1 Version identity — evidence and classification

**Classification: mixed/custom — a release-branch fork carrying a custom module, a rebrand, and a
small set of deliberate core patches.** The evidence:

```
merge-base with upstream/master : b91c12aee3f6022954dd071c53917b2047eabf95
ahead of that merge-base        : 92 commits
behind upstream/master          : 418 commits
ahead of origin/master          : 92 commits
origin/feat/thiqa-branding-foundation = 203f24de5e8f6eae2f553f505cb4c5e7e512e225
commits present locally but NOT pushed : 71
```
*(commands: `git merge-base HEAD upstream/master`; `git rev-list --count <mb>..HEAD`;
`git rev-list --count <mb>..upstream/master`; `git rev-list --count origin/feat/thiqa-branding-foundation..HEAD`)*

- `version.php` on this branch declares **8.2.0** (`version.php:18-20`).
- `upstream/master` declares **8.3.0-dev** (`git show upstream/master:version.php`).
- Both carry `$v_database = 541` and `$v_acl = 13` — i.e. **the two lines are schema-identical**
  even though their display versions differ.
- `git describe --tags` → `pre-branding-implementation-71-g4d09baef1`, so the local tag
  `pre-branding-implementation` marks the pre-fork point of the branding work.

**The branch is no longer an ancestor of `upstream/master`** (92 ahead / 418 behind), so any upstream
catch-up is a merge, not a fast-forward. This is not a deployment blocker — it is a maintenance fact
that matters for patching after the demo goes live.

### 1.2 ⚠ DOCUMENTATION / IMPLEMENTATION CONFLICT — recorded application version

| Source | Says |
|---|---|
| `version.php:18-20` (code) | `8` / `2` / `0`, `$v_tag = ''` → **8.2.0** |
| `openemr.version` table (live dev DB) | `v_major=8, v_minor=3, v_patch=0, v_tag='-dev'` → **8.3.0-dev** |

*(query: `SELECT * FROM openemr.version;`)*

**Interpretation:** the local database was installed from a **master (8.3.0-dev)** checkout, and the
code was subsequently moved to the rel-820-based 8.2.0 line. Because `v_database` is `541` on both,
**the schema is identical and the application runs correctly** — this was confirmed live (§2.5). The
consequence is confined to two places:

1. The version string displayed in the UI / About page would read 8.3.0-dev on a cloned database.
2. `sql_upgrade.php` picks its starting upgrade file from this row. Against 8.2.0 code it would
   compute a nonsensical starting point.

**This is a decisive argument in favour of a fresh install for the demo** (§7), where the row is
written by the installer from `version.php` and therefore agrees with the code.

### 1.3 What the fork actually changed

`git diff --name-only <merge-base>..HEAD` → **1,136 files**, distributed:

| Count | Area | Nature |
|---:|---|---|
| 573 | `docs/` | Governance, evidence, readiness registers — not runtime |
| 160 | `interface/` | 97 of these are the new `oe-module-thiqa-branding`; the rest are listed below |
| 113 | `tests/` | New isolated test coverage for branding/governance |
| 111 | `brand/` | Brand asset masters, tokens, manifests |
| 54 | `tools/` | `tools/branding`, `tools/release`, `tools/ci`, `tools/discovery` |
| 44 | `.phpstan/` | Baseline splits |
| 14 | `public/` | **Branding images — tracked**, see below |
| 13 | `ccdaservice/` | Upstream-side churn |
| 9 | `templates/` | Twig: error pages, login primary logo, portal cards |
| 7 | `src/` | Listed below |
| 5 | `sites/` | Branding logos + `docker-version` |
| 5 | `library/` | Listed below |

**Core (non-module) files this fork edits — these travel with a git deploy and are part of the
branding:**

```
interface/globals.php
interface/main/tabs/menu/menus/front_office.json
interface/reports/*.php                       (11 files — report authorisation remediation)
interface/orders/pending_followup.php
interface/super/layout_listitems_ajax.php
interface/themes/oe-styles/style_thiqa_{light,dark}.scss
interface/themes/thiqa/_*.scss                (7 partials)
library/globals.inc.php
library/classes/Controller.class.php
library/smarty/plugins/function.headerTemplate.php
library/ajax/sql_server_status.php
library/ajax/sql_upgrade_version_check.php
src/Menu/MainMenuRole.php
src/Services/{EncounterService,ProductRegistrationService}.php
src/Telemetry/TelemetryService.php
src/Billing/EdiHistory/X12File.php
src/RestControllers/FHIR/FhirMetaDataRestController.php
src/RestControllers/Subscriber/OAuth2AuthorizationListener.php
templates/login/partials/html/primary_logo.html.twig
templates/error/*.twig  templates/portal/*.twig
portal/home.php  portal/patient/scripts/app.js
public/images/**  sites/default/images/**     (branding raster/vector assets — TRACKED)
webpack.themes.js                             (Q77 entry map)
```

**Deployment significance:** all of the above are **tracked**, so a `git clone`/`git archive` of this
branch carries the entire brand identity, the theme sources and the Q77-pruned webpack entry map.
`public/images/**` is *not* covered by the `.gitignore` `/public/assets/*` and `/public/themes/*`
rules, which is why the logos survive a git-only transfer while the built CSS does not.

### 1.4 ⚠ Zero database schema change

```
git diff --name-only <merge-base>..HEAD -- sql/ src/Migrations/
→ (empty)
```

Corroborated independently against the live database:

| Object | Count | Query |
|---|---:|---|
| Tables | 283 | `information_schema.TABLES WHERE TABLE_SCHEMA='openemr'` |
| Triggers | 0 | `information_schema.TRIGGERS` |
| Stored routines | 0 | `information_schema.ROUTINES` |
| Views | 0 | `information_schema.VIEWS` |
| Events | 0 | `information_schema.EVENTS` |

**Conclusion: this fork adds no tables, no columns, no indexes, no triggers, no procedures and no
views.** Every customisation is either code, a tracked asset, or a row in an existing OpenEMR table
(`globals`, `modules`, `gacl_*`, `users`, `facility`). This is the single most important finding for
§7 — it means a **fresh stock 8.2.0 schema is a complete and correct substrate** for this
application, and no schema migration is required at deploy time.

---

# 2 — How this application currently runs

**It is running right now.** Live verification performed during this pass:

```powershell
Invoke-WebRequest 'http://localhost:8300/interface/login/login.php?site=default' -UseBasicParsing
→ StatusCode=200  Length=9165  Title=Thiqa Login
```

## 2.1 Runtime component versions (measured, not assumed)

| Component | Version | Command |
|---|---|---|
| OS (development) | Windows Server 2025 Datacenter 10.0.26100, GCE VM, **no nested virtualization** | `CLAUDE.local.md` §1 |
| Web server | **Apache 2.4.57 (Win64) Apache Lounge VS16**, built 2023-04-14 | `httpd.exe -v` |
| PHP | **8.3.33 (ZTS, Visual C++ 2019 / VS16, x64)** | `php.exe -v` |
| PHP SAPI | `mod_php` (`php8apache2_4.dll`), in-process | `httpd.conf:540` |
| `php.ini` | `C:\openemr-stack\php\php.ini` (single file, no `conf.d` scan dir) | `php.exe --ini` |
| Database | **MariaDB 11.8.8** for Win64 | `mariadbd.exe --version` |
| Composer | **2.10.2** (2026-07-01), plugin-api 2.9.0 | `composer.phar --version`; `composer.lock` |
| Node.js | **v24.18.1** at `C:\Program Files\nodejs\node.exe` | `node -v` |
| npm | **11.16.0** | `npm -v` |

## 2.2 Web serving topology

| Aspect | Current value | Evidence |
|---|---|---|
| `Listen` | `8300` (plain HTTP; **no TLS configured**) | `httpd.conf:60` |
| `ServerName` | `localhost:8300` | `httpd.conf:227` |
| `DocumentRoot` | `G:/My Drive/OpenEMR` — **the repository root is the web root** | `httpd.conf:251` |
| Application URL | `http://localhost:8300/` → app served at `/`, not `/openemr` | `httpd.conf:251`, live check above |
| `AllowOverride` | `All` on the DocumentRoot | `httpd.conf:272` |
| `DirectoryIndex` | `index.php index.html` | `httpd.conf:285` |
| Protected paths | `<DirectoryMatch "^G:/My Drive/OpenEMR/(sites/[^/]+/documents\|contrib\|tests)">` → `Require all denied` | `httpd.conf:547-548` |
| Extra deny | `<Files "acknowledge_license_cert.html"> Require all denied` (branding, `CLAUDE.local.md` §10) | `httpd.conf:561-562` |
| `<Files ".ht*">` | `Require all denied` | `httpd.conf:292-293` |
| Aliases | **None** | grep of `httpd.conf` for `Alias` |
| VirtualHost | **None** — single default server, no `<VirtualHost>` block | grep of `httpd.conf` |

**Loaded Apache modules (22):** `actions alias allowmethods asis auth_basic authn_core authn_file
authz_core authz_groupfile authz_host authz_user autoindex cgi dir env include isapi log_config mime
negotiation rewrite setenvif php`
*(command: `Select-String -Path httpd.conf -Pattern '^LoadModule'`)*

⚠ **`access_compat` is NOT loaded on the development host.** This matters — see §10.3.

## 2.3 Environment and PATH dependencies (Windows-specific, all of them disappear on Ubuntu)

| Dependency | Why it exists | Ubuntu equivalent |
|---|---|---|
| `C:\openemr-stack\php` must be on `PATH` **before `httpd.exe` starts** | `mod_php` runs in-process; PHP's directory is not on the DLL search path, so `openssl curl intl ldap sodium imagick` fail to load and every page 500s | **Not applicable.** `.so` resolution on Linux uses the configured extension_dir; there is no analogue |
| Apache + PHP must share the **MSVC VS16** toolchain | ABI compatibility for `mod_php` | **Not applicable.** Use `libapache2-mod-php8.3` or PHP-FPM |
| Apache and MariaDB run as **session console processes**, not services | Google Drive mounts `G:` per user session; a `LocalSystem` service cannot see the app directory | **Not applicable.** Ubuntu will use real systemd units |
| `start-openemr.ps1` / `stop-openemr.ps1` | Session lifecycle management | `systemctl` |

**No `.env` file exists** (`Test-Path .env` → `False`), and `interface/globals.php:69-70` loads one
only if present. The only `OPENEMR__` environment variable referenced anywhere in the bootstrap is
`OPENEMR__ENVIRONMENT` in `version.php:47` (dev cache-busting). **The application has no environment
variable dependency to reproduce on the target.**

## 2.4 Base-URL / web-root derivation — good news for portability

`interface/globals.php`:

```php
L55: $webserver_root = dirname(__FILE__, 2);
L57: $webserver_root = str_replace("\\", "/", $webserver_root);      // backslashes normalised
L192: $web_root = substr($webserver_root, strspn($webserver_root ^ $server_document_root, "\0"));
L205: $scheme = ($_SERVER['REQUEST_SCHEME'] ?? 'https') . "://";
L206: $possibleHostSources = ['HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'];
```

**There is no hard-coded filesystem root, hostname, scheme or port in the application bootstrap.**
`$webserver_root` is derived from `__FILE__`; `$web_root` is derived by comparing it with the web
server's DocumentRoot; the scheme and host come from the request. Deploying to
`https://demo.<DOMAIN>/` therefore requires **no code change** for base-URL purposes. Line 57
explicitly normalises Windows backslashes, which is why the current Windows path never leaks.

The hard-coded environment values that *do* exist live in the **database**, not the code — see §11.

## 2.5 Things that will not work unchanged on Ubuntu

See §18 for the full classified audit. Summary: **the application code is portable; the operational
scaffolding around it is not.** Specifically —

- The entire `C:\openemr-stack` stack, its PowerShell start/stop scripts, and the `PATH`-before-Apache
  rule are host artefacts with no Ubuntu counterpart. **Nothing in the repository depends on them
  except two evidence harnesses and one branding CLI precondition** (§18).
- Four `globals` rows carry Windows paths and will be wrong if the database is cloned (§11.2).
- `bin/console` **refuses to run as root** (`RootCliGuard::assertNotRoot()`, `bin/console:25`), so
  every cron entry must run as the web user.

---

# 3 — PHP runtime requirements

## 3.1 Version

| Bound | Value | Source | Verdict for PHP 8.3 |
|---|---|---|---|
| Minimum (Composer) | `>=8.2.0` | `composer.json:14` | **PASS** |
| Minimum (application-enforced) | `8.2.0` | `src/Common/Compatibility/Checker.php:19,47` | **PASS** |
| Maximum | **None declared anywhere** | grep of `Checker.php`, `composer.json` | **PASS** |
| Dependency ceiling | highest cap found in `composer.lock` is `~8.5.0`; the lowest ceiling across all 186 production packages is `~8.4.0` (`laminas/laminas-mvc`, `laminas/laminas-router`, `laminas/laminas-view`, `lcobucci/clock`) | `composer.lock` scan | **PASS — 8.3 is inside every constraint** |
| Development environment | 8.3.33 ZTS VS16 | `php -v` | matches target minor exactly |

**PHP 8.3 on the target is not merely acceptable — it is the same minor version the application is
being developed against today.**

## 3.2 Extensions

`REQUIRED BY CODE` for every row below: all 33 are hard requirements in `composer.json:15-47`.
"Current dev env" was measured with `php -m` during this pass. The **target column cannot be measured
from here** — see `DG-001`.

| Extension | Required by project | Current dev env | Target Ubuntu VM | Compatible? | Evidence |
|---|---|---|---|---|---|
| `bcmath` | REQUIRED | LOADED | `php8.3-bcmath` | expected PASS | `composer.json:15` |
| `calendar` | REQUIRED | LOADED | core (`php8.3-cli`) | expected PASS | `:16` |
| `ctype` | REQUIRED | LOADED | core | expected PASS | `:17` |
| `curl` | REQUIRED | LOADED | `php8.3-curl` | expected PASS | `:18` |
| `dom` | REQUIRED | LOADED | `php8.3-xml` | expected PASS | `:19` |
| `fileinfo` | REQUIRED | LOADED | core | expected PASS | `:20` |
| `filter` | REQUIRED | LOADED | core | expected PASS | `:21` |
| `gd` | REQUIRED | LOADED | `php8.3-gd` | expected PASS | `:22` |
| `iconv` | REQUIRED | LOADED | core | expected PASS | `:23` |
| **`imagick`** | REQUIRED | LOADED (3.8.1) | `php8.3-imagick` — **universe repo, PECL-backed** | **VERIFY — `DG-001`** | `:24` |
| `intl` | REQUIRED | LOADED | `php8.3-intl` | expected PASS | `:25` |
| `json` | REQUIRED | LOADED | core | expected PASS | `:26` |
| `ldap` | REQUIRED | LOADED | `php8.3-ldap` | expected PASS | `:27` |
| `libxml` | REQUIRED | LOADED | core | expected PASS | `:28` |
| `mbstring` | REQUIRED | LOADED | `php8.3-mbstring` | expected PASS | `:29` |
| `mysqli` | REQUIRED | LOADED | `php8.3-mysql` | expected PASS | `:30` |
| `openssl` | REQUIRED | LOADED | core | expected PASS | `:31` |
| `pdo` | REQUIRED | LOADED | core | expected PASS | `:32` |
| `pdo_mysql` | REQUIRED | LOADED | `php8.3-mysql` | expected PASS | `:33` |
| `phar` | REQUIRED | LOADED | core | expected PASS | `:34` |
| **`redis`** | REQUIRED | LOADED (6.3.0) | `php8.3-redis` — **universe repo, PECL-backed** | **VERIFY — `DG-001`** | `:35` |
| `session` | REQUIRED | LOADED | core | expected PASS | `:36` |
| `simplexml` | REQUIRED | LOADED | `php8.3-xml` | expected PASS | `:37` |
| `soap` | REQUIRED | LOADED | `php8.3-soap` | expected PASS | `:38` |
| `sockets` | REQUIRED | LOADED | core | expected PASS | `:39` |
| **`sodium`** | REQUIRED | LOADED | packaging varies by distro | **VERIFY — `DG-001`** | `:40` |
| `tokenizer` | REQUIRED | LOADED | core | expected PASS | `:41` |
| `xml` | REQUIRED | LOADED | `php8.3-xml` | expected PASS | `:42` |
| `xmlreader` | REQUIRED | LOADED | `php8.3-xml` | expected PASS | `:43` |
| `xmlwriter` | REQUIRED | LOADED | `php8.3-xml` | expected PASS | `:44` |
| **`xsl`** | REQUIRED | LOADED | `php8.3-xsl` — **separate package, easy to miss** | **VERIFY — `DG-001`** | `:45` |
| `zip` | REQUIRED | LOADED | `php8.3-zip` | expected PASS | `:46` |
| `zlib` | REQUIRED | LOADED | core | expected PASS | `:47` |
| `opcache` (Zend OPcache) | RECOMMENDED (not in `composer.json`) | LOADED, `opcache.enable=1` | `php8.3-opcache` | expected PASS | `php -m`; `CLAUDE.local.md` §5 |

**`exif` is deliberately absent** — it is not in OpenEMR's required list, and its absence on the dev
host is documented as acceptable (`CLAUDE.local.md` §5).

### 3.2.1 ⚠ A trap that will hide a missing extension

`composer.json:210-245` declares a `config.platform` block asserting **every one of the 33 extensions
is present at version 8.2**, and `composer.lock` carries the same as `platform-overrides`
(verified: `(Get-Content composer.lock | ConvertFrom-Json)."platform-overrides"` lists all 33 plus
`php: 8.2`).

**Consequence:** `composer install` on the target **will succeed even if `imagick`, `redis`, `xsl` or
`sodium` are not installed.** Composer will not check — it has been told they are there. The failure
then surfaces at runtime as a fatal error or a silent feature outage, not at install time.

**This is why the verification step must be an independent `php -m`, run under both SAPIs, and not
"composer install exited 0".** Recorded as `DG-001` and as blocker `B-04` (§24).

## 3.3 PHP settings

Measured on the dev host with `php -r "echo ini_get('…');"`. CLI values shown; where the web SAPI
differs it is noted.

| Setting | Dev value | Classification | Target value | Evidence |
|---|---|---|---|---|
| `memory_limit` | `512M` | **REQUIRED** — OpenEMR requirement | `512M` | `CLAUDE.local.md` §5; `EV-047` §3 step 3 |
| `max_execution_time` | `0` (CLI) / `300` (web) | **REQUIRED** — long installer & report pages | `300` | `CLAUDE.local.md` §5; `EV-047` §3 |
| `max_input_time` | `-1` (CLI) / `300` (web) | RECOMMENDED | `300` | `CLAUDE.local.md` §5 |
| `max_input_vars` | `3000` | **REQUIRED** — OpenEMR forms exceed the 1000 default | `3000` | `CLAUDE.local.md` §5; `EV-047` §3 |
| `post_max_size` | `100M` | **REQUIRED** for document upload | `100M` | `CLAUDE.local.md` §5; `EV-047` §3 |
| `upload_max_filesize` | `100M` | **REQUIRED** for document upload | `100M` | as above |
| `file_uploads` | `1` | **REQUIRED** | `1` | measured |
| `date.timezone` | `UTC` | RECOMMENDED — app overrides from `gbl_time_zone` at runtime | `UTC` acceptable; `Asia/Riyadh` clearer | measured; `globals.gbl_time_zone = Asia/Riyadh` |
| `session.gc_maxlifetime` | `14400` | RECOMMENDED | `14400` | measured |
| `session.save_path` | empty (PHP default temp) | RECOMMENDED to set explicitly | see §9 / `DG-007` | measured |
| `display_errors` | off (empty) | **REQUIRED for a public demo** | `Off` | measured |
| `error_reporting` | `32767` (`E_ALL`) | RECOMMENDED | `E_ALL & ~E_DEPRECATED` acceptable | measured |
| `error_log` | `C:\openemr-stack\logs\php_error.log` | **REQUIRED** — first place to look on a 500 | a real path on the VM | `CLAUDE.local.md` §5; `EV-047` §3 step 4 |
| `opcache.enable` | `1` | RECOMMENDED (was a Drive-latency mitigation here; still valuable on the VM) | `1` | measured |
| `opcache.revalidate_freq` | `30` | RECOMMENDED | `2`–`60` | measured |
| `realpath_cache_size` | `8M` | Dev-specific (Drive FS mitigation) — **not needed on the VM** | default fine | `CLAUDE.local.md` §5 |
| `mysqli.allow_local_infile` | `0` | RECOMMENDED (security) | `0` | measured |
| `output_buffering` | `0` | UNKNOWN whether required | `0` | measured |
| `short_open_tag` | off | RECOMMENDED off | off | measured |
| `open_basedir` | not set | **Must remain unset or include `/tmp`** — see §9.2 | unset | measured |

---

# 4 — Composer / PHP dependencies

Analysis performed by parsing `composer.lock` (186 production packages, 61 dev packages,
`plugin-api-version: 2.9.0`).

| # | Question | Answer | Evidence |
|---|---|---|---|
| 1 | Is `vendor/` committed to git? | **No.** `git ls-files vendor` → 0 files; `.gitignore:9` = `/vendor/*` | measured |
| 2 | Copy `vendor/` from dev, or rebuild on Ubuntu? | **Rebuild on Ubuntu.** The dev `vendor/` was resolved against a Windows ZTS PHP; rebuilding is cheap, deterministic from the lockfile, and avoids any platform-specific artefact | see below |
| 3 | Exact command for a demo deployment | `composer install --no-dev --optimize-autoloader --no-interaction` | §4.2 |
| 4 | Does the build require dev dependencies? | **No.** All 61 dev packages are QA tooling (PHPUnit, PHPStan, Rector, phpcs, psysh, panther, composer-normalize) | `composer.json:138-160` |
| 5 | Can the demo use `--no-dev`? | **Yes** — with one caveat, §4.3 | as above |
| 6 | Composer scripts that modify files? | **None run automatically.** `composer.json:279-315` defines only manually-invoked QA scripts; there is **no `post-install-cmd` or `post-autoload-dump`** | `composer.json:279-315` |
| 7 | External credentials/tokens needed? | **No.** All 247 packages resolve to public `dist` archives | `composer.lock` scan |
| 8 | Private dependencies? | **None.** Zero packages with a non-`github.com` source URL | `composer.lock` scan |
| 9 | Custom git repositories? | Two `repositories` entries — `openemr/wkhtmltopdf-openemr` (vcs) and `openemr/oe-module-cqm` (git), **both public GitHub** | `composer.json:161-170` |
| 10 | Platform constraints that could fail on PHP 8.3? | **None.** Every dist is `type: zip`; no package caps below 8.3 | `composer.lock` scan |

## 4.1 The module installer plugin

`composer.json:206` allows the plugin `openemr/oe-module-installer-plugin`. `.gitignore:11-14`
records what it does:

> *"`oe-module-claimrev-connect` is installed as a Composer dependency
> (`claimrevolution/oe-module-claimrev-connect`) and relocated into this path by the
> `oe-module-installer-plugin` during `composer install`. Never commit the installed copy."*

**Deployment consequence:** `interface/modules/custom_modules/oe-module-claimrev-connect/` is
**gitignored and materialised by `composer install`**. This is one more reason `composer install`
must run on the server rather than shipping a git checkout alone.

## 4.2 Recommended command

```bash
composer install --no-dev --optimize-autoloader --no-interaction --no-progress
```

- `--no-dev` — none of the 61 dev packages are needed at runtime (§4, row 4).
- `--optimize-autoloader` — `composer.json:171-194` uses a `classmap` over `library/classes` plus 8
  eagerly-`files`-loaded legacy helpers; an optimised classmap measurably helps a legacy tree this
  size.
- **Do not run `composer update`.** The lockfile is the pinned, tested dependency set.

## 4.3 Caveat on `--no-dev`

`composer.json:195-201` puts `OpenEMR\Branding\` → `tools/branding/src` and `OpenEMR\Release\` →
`tools/release/src` in **`autoload-dev`**. With `--no-dev` those namespaces are not autoloaded.

- **Not a runtime problem:** the branding *module* (`OpenEMR\Modules\ThiqaBranding\`) registers its
  own namespace at bootstrap (`openemr.bootstrap.php:30-33`) and is unaffected.
- **It does disable** `composer branding-tokens-check` (`tools/branding/bin/generate-tokens.php`) on
  the server. That is a build-time governance check, appropriately run before deployment rather than
  on the demo host.

⚠ **Also note `.gitattributes:20`: `tools/ export-ignore`.** If the source is transferred by
`git archive` / a GitHub release tarball rather than `git clone`, **`tools/` is stripped entirely**.
Use `git clone` (or `rsync` of a working tree) — recorded as `DG-004`.

---

# 5 — Frontend / Node / asset build

## 5.1 The core finding

| Path | Tracked in git? | Present on dev disk? | Rule |
|---|---|---|---|
| `vendor/` | **No** (0 files) | Yes | `.gitignore:9` |
| `public/assets/` | **No** — except 10 files under `public/assets/modified/` | Yes (52 top-level dirs) | `.gitignore:16` + `!/public/assets/modified` |
| `public/themes/` | **No** (0 files) | Yes (18 `.css`) | `.gitignore:17` |
| `node_modules/` | **No** | Yes | `.gitignore:8` |
| `public/images/` | **Yes** — branding logos tracked | Yes | not ignored |

**A `git clone` of this branch produces a tree that cannot render a page.** No stylesheets, no
jQuery/Bootstrap/DataTables, no PHP dependencies. This is the single largest deployment fact in this
report and drives blockers `B-01` and `B-02`.

## 5.2 What produces those directories

`package.json:7-12`:

```json
"postinstall": "napa && node scripts/install-assets.js",
"build":       "npm run build:webpack:prod && npm run build:sync",
"build:sync":  "node scripts/sync-css.js"
```

- `public/assets/` is produced by the **`postinstall` hook of `npm ci`** —
  `scripts/install-assets.js:18` resolves `../public/assets` and copies from `node_modules/`.
- `public/themes/` is produced by **`npm run build`** (webpack → `build:sync`).

**Therefore `npm ci` alone is not enough, and `npm run build` alone is not enough. Both are required.**

## 5.3 Node version and network requirements

| Requirement | Value | Evidence |
|---|---|---|
| Declared engine | **`node >= 24.0.0`** | `package.json:28-30` |
| Upstream CI | `node-version: ['24']` | `.github/workflows/*.yml` |
| Dev host | v24.18.1 / npm 11.16.0 | `node -v`, `npm -v` |
| Lockfile | `lockfileVersion: 3` (npm ≥ 7) | `package-lock.json:4` |
| `.nvmrc` / `.npmrc` | **Neither exists** — `engines` is advisory, not enforced | `Test-Path` |

⚠ **Ubuntu 24.04's archive Node is far older than 24.** NodeSource, `nvm`, or a Node 24 tarball is
required. Recorded as blocker `B-02`.

⚠⚠ **`napa` fetches nine dependencies from outside the npm registry** (`package.json:112-121`):

```
bootstrap-rtl              github.com/PerseusTheGreat/bootstrap-4-rtl (zip)
jquery-creditcardvalidator github.com/PawelDecowski/... (tar.gz)
jquery-panelslider         github.com/eduardomb/... (tar.gz)
jquery-ui                  jqueryui.com/resources/download/jquery-ui-1.12.1.zip
jquery-ui-themes           jqueryui.com/resources/download/...
literallycanvas            github.com/literallycanvas/... (tar.gz)
react                      github.com/facebook/react/releases/... (zip)
lforms                     clinicaltables.nlm.nih.gov/lforms-versions/lforms-33.0.0.zip
```

`"napa-config": { "cache": false }` (`package.json:122-124`) **disables caching**, so every
`npm ci` re-downloads all nine. These are **not in `package-lock.json`** and are not integrity-pinned.
The build therefore requires outbound HTTPS to `github.com`, `jqueryui.com` and
`clinicaltables.nlm.nih.gov`, and is exposed to any of those hosts being unavailable. Recorded as
`DG-003`.

## 5.4 Answers

| Question | Answer |
|---|---|
| Node required only for development, or also deployment? | **Also for deployment** — unless prebuilt artefacts are shipped (§5.5) |
| Are compiled assets already committed? | **No** (except the 10 hand-modified files under `public/assets/modified/`) |
| Must production assets be rebuilt? | **Yes**, on the server or on a build host |
| Exact safe build command | `npm ci && npm run build` — run from the source root, **before** the tree is locked down to `www-data` read-only |
| Required Node version | **≥ 24** |
| Does the build modify tracked source files? | **No.** Webpack writes only to `public/themes/`; `install-assets.js` writes only to `public/assets/`; both are gitignored. Confirmed by `git status` being clean on a host where both have been built |
| Can the demo run without Node installed on the server? | **Yes — if and only if** `public/assets/` and `public/themes/` are built elsewhere and transferred as artefacts (§5.5) |

## 5.5 Q77 governance constraint on the theme build — do not skip this

`webpack.themes.js:151-197` is **Q77-pruned**: `style_light` and `style_dark` are emitted from
`oe-styles/style_thiqa_{light,dark}.scss`, and there are **no `solar` / `manila` / `cobalt_blue` /
`forest_green` entries at all**.

`docs/branding/runbook.md` §4 records the locked constraint: those four stylesheets **must not exist
in the deployed `public/themes/` directory** — not merely "must not be built" — because
`interface/globals.php:476` gates theme selection on `file_exists()`, so a surviving file stays
selectable from a stale `globals`/`user_settings` value.

**On a fresh Ubuntu deployment this is satisfied automatically**, because `public/themes/` starts
empty and webpack's pruned entry map cannot produce the forbidden files. The hazard only exists when
copying into a directory that already has content. Verified current state on the dev host — exactly
the 18 approved files, zero forbidden:

```
ajax_calendar_ie.css  compact_style_dark.css  compact_style_light.css  directional.css
jquery.autocomplete.css  rtl_compact_style_dark.css  rtl_compact_style_light.css
rtl_style_dark.css  rtl_style_light.css  rtl_style_pdf.css  rtl_tabs_style_compact.css
rtl_tabs_style_full.css  style.css  style_dark.css  style_light.css  style_pdf.css
tabs_style_compact.css  tabs_style_full.css
```

The automated gate is:

```bash
vendor/bin/phpunit -c phpunit-isolated.xml --filter 'BrandingGovernanceGuard' --no-coverage
```

It **skips** (rather than fails) when `public/themes/` has not been built, so it is safe on a fresh
checkout and meaningful after the build.

## 5.6 `ccdaservice` — a second, separate Node application

`ccdaservice/` contains its own `package.json` / `package-lock.json` and `serveccda.js`. It is a
standalone Node service for C-CDA generation, invoked via `ccdaservice/ccda_gateway.php`.

- `globals.ccda_alt_service_enable = 0` on the dev instance.
- The `Carecoordination` Laminas module **is** active (`modules` table, `mod_active=1`).

**Assessment:** not required for the smoke tests in §25.P, but C-CDA export will not function without
it. Classified `SHOULD BE DISABLED` for the demo (§14). Recorded as `DG-009`.

---

# 6 — Database discovery

All queries below were read-only `SELECT`/`SHOW` statements against the running local instance. **No
value was modified.**

| Item | Value | Evidence |
|---|---|---|
| Engine | MariaDB | `SELECT VERSION()` |
| Version | **11.8.8-MariaDB** | as above |
| Database name | `openemr` | `information_schema.SCHEMATA` |
| Host | loopback only | `CLAUDE.local.md` §3; `EV-048` §1.3 |
| Port | 3306 | as above |
| App DB username | `openemr` | `EV-048` §1.2 (already public — it is the upstream default) |
| App DB password | **PRESENT — REDACTED.** ⚠ It is the *unchanged upstream default*; see §19 | `EV-048` §1.2 |
| Server charset / collation | `utf8mb4` / `utf8mb4_general_ci` | `SELECT @@character_set_server, @@collation_server` |
| Schema charset / collation | `utf8mb4` / `utf8mb4_general_ci` | `information_schema.SCHEMATA` |
| Table collations in use | **`utf8mb4_general_ci` × 283 — one collation, no others** | `GROUP BY TABLE_COLLATION` |
| Column collations in use | `utf8mb4_general_ci` × 2241, `utf8mb4_bin` × 1 | `GROUP BY COLLATION_NAME` |
| Storage engines | **InnoDB × 283 — no MyISAM, no Aria** | `GROUP BY ENGINE` |
| Table count | **283** | `COUNT(*)` |
| Data + index size | **104.9 MB** | `SUM(DATA_LENGTH+INDEX_LENGTH)` |
| Sites / databases | **One** — `sites/default` only | `Get-ChildItem sites` |
| Schema version | `v_database = 541`, `v_acl = 13` | `openemr.version` |
| Recorded app version | **`8.3.0-dev`** ⚠ conflicts with code — §1.2 | `openemr.version` |
| Migration state | Doctrine Migrations available (`doctrine/migrations ^3.9`); **zero fork migrations** | `git diff … -- src/Migrations/` → empty |
| Custom tables | **None** | §1.4 |
| Custom columns | **None** | §1.4 |
| Custom indexes | **None** | §1.4 |
| Custom triggers | **0** | `information_schema.TRIGGERS` |
| Custom stored procedures / functions | **0** | `information_schema.ROUTINES` |
| Custom views | **0** | `information_schema.VIEWS` |
| Scheduled events | **0** | `information_schema.EVENTS` |
| Module-created schema objects | **None** — the branding module creates only `globals` rows | §1.4, §13 |

## 6.1 ⚠ MariaDB 11.8.8 → 10.11 is a *downgrade*. It was checked, and it is safe.

A dump from 11.8 does not import into 10.11 if it carries 11.x-only artefacts. Three specific
hazards were checked:

| Hazard | Finding | Evidence |
|---|---|---|
| `utf8mb4_uca1400_*` collations (MariaDB 11.4+, unknown to 10.11) | **Not used by the `openemr` schema.** All 283 tables and all 2,242 collated columns are `utf8mb4_general_ci` / `utf8mb4_bin`. (`uca1400` appears only on the `mysql` and `test` system schemas, which are never migrated.) | `information_schema` queries above |
| 11.x-only DDL in the shipped schema (`INVISIBLE` columns, functional indexes, `GENERATED ALWAYS`, `utf8mb4_0900_*`) | **None.** A repo-wide grep over `sql/` returned 2 hits, both of which are English prose in SQL comments (`8_1_0-to-8_1_1_upgrade.sql:122`, `8_0_0-to-8_1_0_upgrade.sql:299` — the word "invisible") | `rg -i 'uca1400\|INVISIBLE\|GENERATED ALWAYS\|JSON_TABLE\|ROW_NUMBER\|WITH RECURSIVE\|utf8mb4_0900' sql/` |
| Engine features | InnoDB only; no Aria, no MyISAM, no sequences | `GROUP BY ENGINE` |

**Verdict: MariaDB 10.11 on the target is compatible with this schema.** This is nonetheless a
secondary argument for the fresh-install strategy (§7), which sidesteps the question entirely by
building the schema from `sql/database.sql` with the 10.11 server itself.

## 6.2 Where database configuration lives

| Item | Location | Contents | Git |
|---|---|---|---|
| Connection settings | `sites/default/sqlconf.php` | `$host $port $login $pass $dbase $sqlconf[…] $config` — **PRESENT — REDACTED** | Tracked, `skip-worktree` (§19) |
| Site behaviour | `sites/default/config.php` (4,105 B) | print/fax commands, OFX bank IDs, `file_command_path` | Tracked |
| Runtime settings | `openemr.globals` table | ~1,000 rows incl. all branding and regional config | Database |
| Connection mechanism | Doctrine DBAL 4.x behind the legacy ADODB surface API; `QueryUtils` for new code | — | `composer.json:54`; `CLAUDE.md` |

---

# 7 — Fresh database or migrate the current one?

## 7.1 Customisation classification

| Class | Present? | What, specifically | Evidence |
|---|---|---|---|
| **A. Code-only** | **Yes — the bulk of the work** | The `oe-module-thiqa-branding` module (97 files), 7 Thiqa SCSS partials + 2 entry SCSS, the Q77 webpack entry map, tracked branding images under `public/images/**` and `sites/default/images/**`, 11 remediated report scripts, the login Twig partial, `MainMenuRole`, `front_office.json` | §1.3 |
| **B. DB schema** | **None** | Zero tables/columns/indexes/triggers/routines/views added | §1.4 |
| **C. Configuration data in DB** | **Yes — material** | ~33 branding `globals` keys + regional config (§11.2, §16) | `globals` query |
| **D. Demo/test data** | **Yes — and it is regenerable** | 30 patients / 72 encounters / 37 appointments / 12 prescriptions / 10 documents / 36 charges / 2 payers, produced by a committed deterministic seeder | §7.2 |
| **E. User / ACL config** | **Yes** | 10 `users` rows (6 named clinical accounts), 7 ACL groups with 8 memberships | §12 |
| **F. Module installation state** | **Yes — and it is invisible in the filesystem** | One row in `modules` enabling `oe-module-thiqa-branding` | §13.2 |
| **G. Branding stored in DB** | **Yes** | `openemr_name`, `login_tagline_text`, logo/support/manual links, `saas_branding_*` | §16 |
| **H. System settings in DB** | **Yes** | timezone, currency, phone country code, units, theme, acknowledgement suppression | §11.2 |

**The decisive observation is that class B is empty.** Every customisation is either code (which
travels in git) or a row in a stock table (which can be *reproduced by a documented, committed
command* rather than copied).

## 7.2 The demo dataset is generated, not hand-made

`interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/SeedDemoCommand.php` is a
committed Symfony console command, `thiqa-branding:seed-demo`, whose class docblock states
(`:33-53`):

> *"**Every value is synthetic and governed by `docs/evidence/EV-028-synthetic-data-control.md`.**
> … **Determinism.** A fixed random seed and fixed name tables mean two runs against the same
> pre-seed baseline produce the same dataset. … **Fail-closed.** Preconditions refuse to run rather
> than producing a half-seeded database."*

Its locked targets (`:70-85`) — and the live row counts measured this session — agree exactly:

| Target constant | Declared | Measured in live DB | Query |
|---|---:|---:|---|
| `TARGET_PATIENTS` | 30 | **30** | `COUNT(*) FROM patient_data` |
| `TARGET_ENCOUNTERS` | 72 | **72** | `COUNT(*) FROM form_encounter` |
| `TARGET_APPOINTMENTS` | 36 | 37 (36 + 1 pre-existing) | `COUNT(*) FROM openemr_postcalendar_events` |
| `TARGET_DOCUMENTS` | 10 | **10** | `COUNT(*) FROM documents` |
| `TARGET_PRESCRIPTIONS` | 12 | **12** | `COUNT(*) FROM prescriptions` |
| `TARGET_CHARGES` | 36 | **36** | `COUNT(*) FROM billing` |
| `TARGET_PAYERS` | 2 | **2** | `COUNT(*) FROM insurance_companies` |

**The demo dataset is reproducible from source.** That is what makes Option 3 viable rather than
aspirational.

## 7.3 ⚠ The seeder cannot run on Ubuntu unmodified

`SeedDemoCommand.php:101-103, 335-337`:

```php
private const BASELINE_SHA256 = '18564f74b01dc505a3bc70e5674837ae89b9f61061b728772235ad5933661e71';
private const BASELINE_PATH   = 'C:/openemr-stack/backups/protected/rdy0044a/'
                              . 'thiqa-rdy0044a-preseed-20260813-185745.sql';
...
if (!is_file(self::BASELINE_PATH)) {                       // L335 — precondition
} elseif (hash_file('sha256', self::BASELINE_PATH) !== self::BASELINE_SHA256) {   // L337
```

This is a **hard-coded Windows absolute path in a committed application file**, checked as a
fail-closed precondition. On Ubuntu `is_file()` returns `false` and the seeder refuses to run.

This is the **only** drive-letter path in application code (§18). It is a rollback-safety guard, not
business logic. Recorded as blocker `B-03` and gap `DG-005`.

## 7.4 The three options, assessed

### OPTION 1 — Fresh OpenEMR/demo database
Install stock 8.2.0 from `sql/database.sql`, then stop.

- ✅ Correct schema, correct `version` row, correct collations, no Windows paths, no dev audit log.
- ❌ **The application would not be branded.** ~33 `globals` rows carry the Thiqa identity; a stock
  install has upstream defaults.
- ❌ **The branding module would not load.** `ModulesApplication::bootstrapCustomModules()`
  (`src/Core/ModulesApplication.php:141`) selects `WHERE mod_active = 1 AND type != 1`. Without that
  row the bootstrap is never included and every branding listener is absent.
- ❌ No demo data, no clinical users, no roles.

**Rejected — it does not preserve required custom functionality.**

### OPTION 2 — Clone / sanitise the current development database
`mysqldump` the dev DB, import on the VM.

- ✅ Everything works immediately; branding, users, data and module state all arrive together.
- ❌ Imports `version = 8.3.0-dev` against 8.2.0 code (§1.2).
- ❌ Imports four Windows-path `globals` rows (§11.2).
- ❌ Imports `saas_branding_revision = 1` while the stylesheets that revision refers to are
  **gitignored** and will not be on the target — producing a self-inconsistent branding state (§16.3).
- ❌ Imports ~70,372 `log` rows which, per `EV-055`, contain base64-encoded SQL with interpolated
  patient values (surnames, identifiers, telephone numbers, clinical free text). All synthetic here —
  but it is dev history with no demo value, and it is the bulk of the 104.9 MB.
- ❌ Carries the unchanged default DB password unless explicitly rotated (`EV-048`).

**Rejected as the primary path** — it imports four known-wrong states that must then each be
un-done by hand, which is exactly the error-prone shape a runbook should avoid.

### ✅ OPTION 3 — Fresh database + controlled configuration/data migration — **RECOMMENDED**

**Ordered, and every step is a committed, documented command:**

1. **Fresh install** via `contrib/util/installScripts/InstallerAuto.php` against MariaDB 10.11.
   → correct 283-table schema, correct `utf8mb4_general_ci`, `version` row written from
   `version.php` = **8.2.0**, a **unique generated DB password** (`EV-047` §6), `$config = 1`.
2. **Register the branding module** — insert the `modules` row (§13.2), the only manual DB step.
3. **Apply the product identity** — `php bin/console thiqa-branding:apply-profile --site=default`
   (`docs/branding/runbook.md` §1.1). Applies ~33 `globals` from the committed
   `config/branding-profile.json` in one transaction; `--dry-run` previews; idempotent.
4. **Set the per-instance configuration** that the profile does not own — timezone, currency,
   `phone_country_code`, units, `mysql_bin_dir`, `temporary_files_dir`, facility record
   (`EV-047` §7, and §11.2 here).
5. **Provision the report ACL** — `php bin/console thiqa-branding:provision-report-acl`. **Mandatory
   and easy to miss** (§12.3).
6. **Create the demo users and roles** (§12.4).
7. **Seed the demo data** — `thiqa-branding:seed-demo`, after resolving `B-03` (§7.3).
8. **Materialise Tier-2 branding if wanted** — `thiqa-branding:materialise`; otherwise leave
   revision 0, which `BrandingHealthCheck` treats as a healthy state (`docs/branding/runbook.md` §6.1).
9. **Verify** — `thiqa-branding:verify --site=default` must report self-consistent.

**Why this is the right answer:** because class B (schema) is empty and class D (demo data) is
generated by a committed deterministic seeder, Option 3 costs little more than Option 2 while
producing an instance with **no inherited defects** — right version row, right paths, right
password, no dev audit history, and a provisioning process that is reproducible for the next
customer instance. It is also the strategy `EV-047` already specifies: *"A customer instance is
provisioned fresh from this runbook. It is never an upgrade of the demo instance."*

**Nothing was migrated. This is a recommendation only.**

---

# 8 — OpenEMR site configuration

`sites/` contains exactly one site, `sites/default` (`docs/branding/multi-tenant-white-label-readiness.md`
§1.8 makes the same statement; independently confirmed by directory listing).

## 8.1 Files and directories required on the Ubuntu demo server

| Path | Purpose | Tracked? | How it gets there |
|---|---|---|---|
| `sites/default/sqlconf.php` | DB connection + `$config` flag | Tracked (`skip-worktree` locally) | **Generated at install** by the installer; must be rewritten per instance (`EV-047` §6) |
| `sites/default/config.php` | Print/fax commands, OFX ids, `file_command_path` | Tracked | Ships in git; **edit per §8.3** |
| `sites/default/clickoptions.txt` | Clinical pick-lists | Tracked | git |
| `sites/default/faxcover.txt`, `faxtitle.eps` | Fax cover sheet | Tracked | git |
| `sites/default/referral_template.html` | Referral letter | Tracked | git |
| `sites/default/statement.inc.php` (54 KB) | Patient statement generator | Tracked | git |
| `sites/default/LBF/*.plugin.php` (6) | Layout-based-form plugins | Tracked | git |
| `sites/default/letter_templates/` | Letter templates | Tracked (structure) | git |
| `sites/default/images/` | **Branding logos — modified by this fork** | Tracked | git |
| `sites/default/documents/` | **Patient document store** — see §9 | Structure tracked; payloads gitignored | Created by installer; payloads per §20 |
| `sites/default/documents/certificates/*.key` | OAuth2 keypair | **Gitignored** (`.gitignore:83`) | **Generated at runtime** by `setupOAuthKeys()` |
| `sites/default/documents/logs_and_misc/methods/*` | Site encryption keys (AES + HMAC pair, per `KeyVersion`) | **Gitignored** (`.gitignore:84`) | **Generated at runtime** — see ⚠ below |
| `sites/default/edi/`, `era/` | X12 / ERA working dirs | Structure tracked | git + writable |
| `sites/default/docker-version` | Stack marker | Tracked | irrelevant here |

## 8.2 ⚠ Site encryption keys are generated, never copied

`.gitignore:77-88` documents this explicitly:

> *"Runtime-generated cryptographic key material. These are created by the running application, never
> by a developer, and must never enter version control. … Without these rules the files appear in
> `git status` as ordinary untracked files, indistinguishable from a document someone forgot to add,
> and a single `git add -A` commits live private keys (Phase 2B PB-033/PB-035, RDY-0048)."*

**Deployment consequence, and it is decisive for the DB strategy:** the site encryption keys under
`logs_and_misc/methods/` encrypt data stored *in the database*. **A cloned database without its
matching key files cannot decrypt what it holds.** Under Option 3 (fresh install) this problem does
not arise — the installer generates a fresh key set and the fresh database contains nothing encrypted
under the old one. Under Option 2 it becomes a hard dependency that the gitignore rule actively
prevents you from satisfying by copying the repo. Recorded as `DG-006`.

## 8.3 Site files that are environment-specific

`sites/default/config.php` — inspected, values below are the *current tracked* content:

| Line | Setting | Value | Ubuntu assessment |
|---|---|---|---|
| `:10` | `OPENEMR_PRINT_COMMAND` | `lpr -P HPLaserjet6P -o cpi=10 …` | **Improves on Ubuntu.** `lpr` exists with `cups-client`. Points at a printer that does not exist — harmless for a demo; statement printing simply fails |
| `:13` | `OPENEMR_HYLAFAX_ENSCRIPT` | `enscript -M Letter -B -e^ …` | Only used by HylaFAX, which is **disabled** (`enable_hylafax = 0`) |
| `:26` | `oer_config['documents']['file_command_path']` | `/usr/bin/file` | **Correct on Ubuntu** (it is a Unix path that was wrong on Windows). Provided by the `file` package |
| `:16,:19` | `oer_config['ofx']['bankid']`, `['acctid']` | `123456789` (placeholder) | Placeholder; OFX unused in demo |

`EV-047` §1 flags these as *"OS-specific commands … Unix paths on a Windows host (OD-04)"* — i.e. a
Windows defect. **Moving to Ubuntu resolves rather than creates this issue.**

## 8.4 Git status of site files

| Question | Answer |
|---|---|
| Ignored by git | `documents/[0-9]*/` (patient payloads), `documents/certificates/*.key`, `documents/logs_and_misc/methods/*` |
| Generated at install time | `sqlconf.php` (`$config` flipped to 1), the site key material, the OAuth2 keypair |
| Environment-specific | `sqlconf.php` (credentials, host), `config.php` (print/fax/OFX) |
| Development-machine-specific | **None under `sites/`.** No drive letters, no `C:\openemr-stack` reference — verified by grep |

---

# 9 — Filesystem writable directories

Derived from upstream's own permission scripts (the authoritative statement of what the running
application writes to) and from the cache implementation.

| Path | Purpose | Must be writable? | Recommended owner:group | Recommended perms | Evidence |
|---|---|---|---|---|---|
| `sites/` (tree) | Site config + all site runtime state | **Yes** | `www-data:www-data` | dirs `0700`, files `0600` | `docker/binary/openemr.sh:929-930`; `docker/flex/utilities/devtoolsLibrary.source:519-527` |
| `sites/default/` | Site root; installer writes `sqlconf.php` here | **Yes at install**, then may tighten | `www-data:www-data` | `0700` after install | `docker/binary/openemr.sh:852`; `Dockerfile:161` |
| `sites/default/sqlconf.php` | DB credentials | **Yes at install only** | `www-data:www-data` | `0400` after install | `openemr.sh:849` (`chmod 400`); `Dockerfile:159` |
| `sites/default/documents/` | **Patient documents, PDFs, exports, imports, generated reports** | **Yes** | `www-data:www-data` | dirs `0700`, files `0600` | `Dockerfile:163-164`; `openemr.sh:856-857` |
| `sites/default/documents/temp/` | Temporary document staging | **Yes** | `www-data:www-data` | `0700` | tracked `README`; directory listing |
| `sites/default/documents/smarty/` | Smarty compiled templates (legacy path) | **Yes** | `www-data:www-data` | dirs `0700`, files `0600` | `openemr.sh:856-857` |
| `sites/default/documents/certificates/` | OAuth2 keypair, written by `setupOAuthKeys()` | **Yes** | `www-data:www-data` | `0700` | `.gitignore:78-83` |
| `sites/default/documents/logs_and_misc/methods/` | Site AES + HMAC key pair | **Yes** | `www-data:www-data` | `0700` | `.gitignore:84` |
| `sites/default/documents/onsite_portal_documents/` | Portal-generated documents | **Yes** (if portal enabled) | `www-data:www-data` | `0700` | directory listing |
| `sites/default/documents/procedure_results/`, `edi/`, `era/`, `couchdb/`, `custom_menus/`, `letter_templates/` | Lab results, X12, ERA, menu overrides, letters | **Yes** | `www-data:www-data` | `0700` | directory listing; tracked READMEs |
| `sites/default/edi/`, `sites/default/era/` | X12 / ERA working dirs | **Yes** | `www-data:www-data` | `0700` | directory listing |
| `sites/default/images/` | Site logos; writable if logos are managed via the UI | Yes (if UI-managed) | `www-data:www-data` | `0700` | branding assets tracked here |
| `interface/modules/custom_modules/oe-module-thiqa-branding/public/branding/` | **Materialised per-tenant token stylesheets**, written by `thiqa-branding:materialise` | **Yes**, if Tier-2 materialisation is used | `www-data:www-data` | `0755` dirs / `0644` files (served over HTTP) | `.gitignore:66-73`; `src/Materialisation/TenantBrandingPaths.php` |
| `sys_get_temp_dir()` (→ `/tmp` on Ubuntu) | **General application cache** — Smarty compile dir and other tool caches | **Yes** | `www-data` | created `0700` by the app | `src/Services/Storage/CacheDirectory.php:58,74`; `gacl/admin/gacl_admin.inc.php:63` |
| PHP `session.save_path` | PHP session files | **Yes** | `www-data` | `0700` | PHP default; §3.3 |
| `globals.temporary_files_dir` | App temp scratch | **Yes** | `www-data` | `0700` | `globals` row; §11.2 |
| `globals.backup_log_dir` | Backup output | **Yes** (only if backup used) | `www-data` | `0700` | `globals` row; §11.2 |
| PHP `error_log` target | Diagnostics | **Yes** | `www-data` | `0640` | §3.3; `EV-047` §3 step 4 |
| `vendor/`, `public/assets/`, `public/themes/` | Build outputs | **Writable at build time only**, read-only at runtime | build user → then `www-data:www-data` read | §5 |

**No `chmod` or `chown` was executed. This table is a specification.**

## 9.1 Explicitly *not* writable

- `bin/` — CLI tools; `bin/.htaccess` denies web access outright.
- `src/`, `library/`, `interface/` (except the module branding output dir above), `templates/` —
  application code. `EV-047` §1 states the rule directly: *"If you find yourself editing a file under
  `src/`, `library/` or `interface/` to provision, **stop — that is a defect**."*

## 9.2 ⚠ Ubuntu-specific: `PrivateTmp` splits the cache between web and CLI

`CacheDirectory::__construct()` defaults its base directory to `sys_get_temp_dir()`
(`src/Services/Storage/CacheDirectory.php:57-58`) and creates each scope `0700`
(`:74`). Ubuntu's packaged `apache2.service` ships with `PrivateTmp=true`, giving Apache a private
`/tmp` namespace **that CLI processes do not share**.

**Consequences to plan for:**
- The Smarty compile cache the web SAPI writes is invisible to `bin/console`, and vice versa. This
  is *correct isolation*, not a fault — but it means "I cleared the cache from the CLI" will not
  clear the web cache.
- If `open_basedir` is ever set, it must include the temp path, or `CacheDirectory` throws.
- Because directories are created `0700` and owned by the creating user, a cache directory first
  created by `root` (e.g. a mis-run CLI command) will be **unreadable by `www-data`**. `bin/console`
  refusing to run as root (`bin/console:25`) already guards against the common way this happens.

Recorded as `DG-007`.

---

# 10 — Apache requirements

## 10.1 Required modules

| Module | Required? | Why | Evidence |
|---|---|---|---|
| `mod_php` **or** `mod_proxy_fcgi` + PHP-FPM | **REQUIRED** | PHP execution | current host uses `mod_php` (`httpd.conf:540`) |
| `rewrite` | **REQUIRED** | Five `.htaccess` files use `RewriteEngine On` | `apis/.htaccess`, `oauth2/.htaccess`, `portal/patient/.htaccess`, `meta/health/.htaccess`, `interface/modules/zend_modules/public/.htaccess` |
| `setenvif` | **REQUIRED** | `SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1` — without it Bearer tokens are invisible to PHP | `apis/.htaccess:4`, `oauth2/.htaccess:4` |
| **`access_compat`** | **REQUIRED as the tree stands** | Two `.htaccess` files use Apache **2.2** syntax — see §10.3 | `bin/.htaccess`, `sites/default/documents/.htaccess` |
| `authz_core` | **REQUIRED** | `Require all denied/granted` | `httpd.conf:236,277` |
| `dir` | **REQUIRED** | `DirectoryIndex index.php` | `httpd.conf:285` |
| `mime` | **REQUIRED** | Content types | standard |
| `ssl` | **REQUIRED for the HTTPS target** | `https://demo.<DOMAIN>` | §11 |
| `headers` | **RECOMMENDED** | HSTS and security headers for a public demo. **Not currently used by any `.htaccess`** — verified by reading all nine | grep of all `.htaccess` |
| `alias` | Only if serving from a subdirectory | — | §10.4 |
| `deflate`, `expires` | OPTIONAL | Performance | — |

## 10.2 `.htaccess` inventory (all nine read in full)

| File | Directives | Purpose |
|---|---|---|
| `apis/.htaccess` | `RewriteEngine`, `SetEnvIf Authorization`, rewrite to `dispatch.php?_REWRITE_COMMAND=$1` | REST/FHIR API front controller |
| `oauth2/.htaccess` | same shape → `authorize.php` | OAuth2 front controller |
| `portal/patient/.htaccess` | rewrite → `index.php?_REWRITE_COMMAND=$1` | Patient portal (Phreeze) |
| `interface/modules/zend_modules/public/.htaccess` | rewrite → `index.php` | Laminas module front controller |
| `meta/health/.htaccess` | rewrite → `index.php` | Health-check endpoints |
| `bin/.htaccess` | `order deny,allow` / `Deny From All` ⚠ **2.2 syntax** | Deny web access to CLI tools |
| `sites/default/documents/.htaccess` | `Allow From None` / `Deny From All` ⚠ **2.2 syntax** | Deny web access to patient documents |
| `interface/modules/custom_modules/oe-module-faxsms/.htaccess` | module-local | fax/SMS module |
| `portal/patient/fwk/libs/.htaccess` | library protection | Phreeze internals |

**There is no root `.htaccess`** (`Get-Content .htaccess` → empty; not in `git ls-files`).

## 10.3 ⚠ Apache 2.2 syntax in two `.htaccess` files

`bin/.htaccess`:
```apache
order deny,allow
Deny From All
```
`sites/default/documents/.htaccess`:
```apache
Allow From None
Deny From All
```

Under Apache 2.4 these directives exist **only** in `mod_access_compat`. Ubuntu's `apache2` package
enables `access_compat` by default, so this normally works — but:

- **If `access_compat` is disabled** (a common hardening step), every request under `/bin/` or
  `sites/*/documents/` returns **HTTP 500 `Invalid command 'order'`**, not a clean 403.
- The dev host does **not** load `access_compat` (§2.2), so those paths are currently 500-ing rather
  than 403-ing there. The *effect* is still denial, which is why it has gone unnoticed.

**Recommended (does not require editing the tracked files):** deny both paths in the VirtualHost with
2.4 syntax, which takes precedence and makes the `.htaccess` outcome irrelevant. This preserves
Invariant 4 (no core edits) — the deny lives in server configuration, not in the repository.

Recorded as `DG-002`.

## 10.4 DocumentRoot and URL shape

**Recommendation: serve the application at `/`, with DocumentRoot pointing at the application root.**

| Option | `$web_root` becomes | Assessment |
|---|---|---|
| **DocumentRoot = app root, URL `/`** ✅ | `""` | Matches the current dev topology exactly (`httpd.conf:251`), so every path already exercised on this host behaves identically. Simplest TLS/vhost. **Recommended** |
| DocumentRoot = parent, URL `/openemr` | `/openemr` | Supported — `interface/globals.php:188-202` documents this case explicitly — but changes every generated URL relative to what has been tested here |

`interface/globals.php:192` derives `$web_root` by stripping the common prefix between
`$webserver_root` and the server's DocumentRoot, so **both options work without code changes**. The
recommendation is about minimising delta from the tested configuration, not capability.

## 10.5 Minimum VirtualHost requirements for `https://demo.<DOMAIN>`

**Specification only — no Apache configuration was created or changed.**

Required elements, each traceable to evidence above:

| # | Requirement | Source |
|---|---|---|
| 1 | `<VirtualHost *:443>` with `ServerName demo.<DOMAIN>`, `SSLEngine on`, certificate + key | target definition |
| 2 | Port 80 vhost that redirects to HTTPS | public-demo hygiene |
| 3 | `DocumentRoot` = application root | §10.4 |
| 4 | `<Directory>` on that root: `Options -Indexes +FollowSymLinks`, `AllowOverride All` (or fold the five rewrite blocks into the vhost and use `AllowOverride None`), `Require all granted` | `httpd.conf:252-277`; §10.2 |
| 5 | `DirectoryIndex index.php index.html` | `httpd.conf:285` |
| 6 | Deny block for `sites/*/documents`, `contrib`, `tests` using **2.4 syntax** | model at `httpd.conf:547-548` |
| 7 | Deny block for `bin/` using **2.4 syntax** (§10.3) | `bin/.htaccess` |
| 8 | `<Files ".ht*"> Require all denied` | `httpd.conf:292-293` |
| 9 | Deny `acknowledge_license_cert.html` — **a branding governance requirement**, not optional | `CLAUDE.local.md` §10; `docs/branding-production/16-conflict-resolutions.md` §12 |
| 10 | `LimitRequestFieldSize` — **leave at the 8190 default.** No evidence was found in this repository of a requirement to raise it; the large-payload path is POST bodies, governed by `post_max_size`/`upload_max_filesize` (100M), not header size | grep found no such requirement — `DG-008` |
| 11 | Upload implication: `post_max_size` and `upload_max_filesize` at `100M` must be set in **PHP**, and if PHP-FPM is used, `client_max_body_size`-equivalent limits must not undercut it | §3.3 |
| 12 | PHP integration: `mod_php` (`libapache2-mod-php8.3`) mirrors the dev host most closely; PHP-FPM via `mod_proxy_fcgi` is also fine and is the more common production choice | §2.1 |
| 13 | Symlinks: `+FollowSymLinks` — the app does not require symlinks, but the default is worth stating | — |
| 14 | `ServerTokens Prod` / `ServerSignature Off` | public-demo hygiene |

---

# 11 — HTTP/HTTPS and base-URL dependencies

## 11.1 Code: nothing to change

Searched `interface/globals.php` (the bootstrap that establishes every URL) for `localhost`,
`127.0.0.1`, hard-coded schemes, hosts and ports. **Result: none.** As shown in §2.4, scheme comes
from `$_SERVER['REQUEST_SCHEME']`, host from `HTTP_X_FORWARDED_HOST` → `HTTP_HOST` → `SERVER_NAME` →
`SERVER_ADDR`, and roots from `__FILE__`. Backslashes are normalised at `:57`.

**Moving to `https://demo.<DOMAIN>` requires no application code change.**

## 11.2 Database: this is where the environment is hard-coded

Query run: `SELECT gl_name, gl_value FROM globals WHERE gl_value REGEXP '^[A-Za-z]:|xampp|localhost|127\.0\.0\.1|^https?://|/usr/|/tmp|C:/'`

| `gl_name` | Current value | Action for the Ubuntu demo | Severity |
|---|---|---|---|
| `mysql_bin_dir` | `C:/openemr-stack/mariadb/bin` | → `/usr/bin` | **P1** — backup silently breaks otherwise (`RDY-0080`, `docs/HISModulesUsers.md:3333`) |
| `perl_bin_dir` | `C:/xampp/perl/bin` | → `/usr/bin` | **P2** — upstream Windows default, path does not exist |
| `temporary_files_dir` | `C:/windows/temp` | → `/tmp` (or a dedicated dir) | **P1** |
| `backup_log_dir` | `C:/windows/temp` | → a real writable dir | **P2** |
| `portal_onsite_two_address` | `https://your_web_site.com/openemr/portal` | → `https://demo.<DOMAIN>/portal` **if** the portal is enabled; otherwise leave | **P2** (portal is disabled — §14) |
| `site_addr_oath` | **empty** | → `https://demo.<DOMAIN>` **if** OAuth2/FHIR is enabled | **P2** (APIs disabled — §14) |
| `main_menu_logo_link` | `https://skyeagle.uk/` | keep — intentional branding | — |
| `online_support_link` | `https://skyeagle.uk/support` | keep — intentional branding | — |
| `user_manual_link` | `https://skyeagle.uk/docs` | keep — intentional branding | — |
| `SMTP_HOST` | `localhost` | see §14 — **must not deliver mail** | **P1** |
| `couchdb_host` | `localhost` | CouchDB unused (`couchdb_dbase` empty) | P3 |
| `hylafax_server` | `localhost` | `enable_hylafax = 0` | P3 |
| `phimail_server_address` | `https://phimail.example.com:32541` | `phimail_enable = 0` — placeholder | P3 |
| `erx_newcrop_path` / `_soap` | **Real NewCrop production endpoints** | `erx_enable = 0`. See §14 | **P1 to keep disabled** |

**Every row above is a database value, not a file.** Under Option 3 they are set once at
provisioning (`EV-047` §7) and the Windows values never exist on the target. Under Option 2 each one
must be corrected by hand after import — five of them are P1/P2.

## 11.3 Callback / redirect / API URLs

| Category | Present? | Evidence |
|---|---|---|
| OAuth/OIDC redirect URIs | **Registered per client in the database**, not in configuration. `site_addr_oath` is the issuer base and is currently **empty** | `globals` query; `interface/globals.php:125-140` |
| SMART on FHIR launch URLs | Governed by the same `site_addr_oath` issuer | `interface/globals.php:125-128` |
| API base URLs | Derived from the request; `rest_api = 0` | §14 |
| Patient portal URL | `portal_onsite_two_address` — placeholder; `portal_onsite_two_enable = 0` | §14 |
| WebSocket URLs | **None found** | grep |
| External links | Three `skyeagle.uk` branding links (intentional) | §11.2 |

**Because every API and the portal are disabled (§14), no callback URL needs to be correct for the
demo.** If any is later enabled, `site_addr_oath` must be set to `https://demo.<DOMAIN>` **before**
issuing client registrations, because registered redirect URIs are stored per client and would have
to be re-registered afterwards.

---

# 12 — Authentication, users and ACL

## 12.1 Current users (usernames only; **no password is shown or derivable from this report**)

`SELECT id, username, active, LENGTH(COALESCE(password,'')) FROM users`

| id | username | active | Legacy `password` column length | Role (from §12.2) |
|---:|---|---:|---:|---|
| 1 | `admin` | 1 | 12 | Administrator |
| 2 | `phimail-service` | 0 | 7 | service account (inactive) |
| 3 | `portal-user` | 0 | 7 | service account (inactive) |
| 4 | `oe-system` | 0 | 7 | service account (inactive) |
| 5 | `n.alqahtani` | 1 | 0 | clinical |
| 6 | `y.alharbi` | 1 | 0 | Physician (`docs/ScreenShoots/HR-01-BrowserVerification-v4.md:4`) |
| 7 | `s.almutairi` | 1 | 0 | clinical |
| 8 | `r.aldosari` | 1 | 0 | clinical |
| 9 | `k.alotaibi` | 1 | 0 | clinical |
| 10 | `m.alzahrani` | 1 | 0 | clinical |

The six named accounts have an **empty legacy `password` column** — authentication uses the modern
`users_secure` table, which is correct. **Credentials are held outside the repository** in
`C:\openemr-stack\secrets\thiqa-demo-credentials.json` (`EV-061` §, `docs/PHASE-2B-CONTINUATION-PROMPT.md:30`),
and the governance rule is *"Never write a password into any document, output or screenshot."*
**This report complies.**

## 12.2 ACL groups and membership

`SELECT g.value, COUNT(m.aro_id) FROM gacl_aro_groups g LEFT JOIN gacl_groups_aro_map m …`

| ACL group | Members |
|---|---:|
| `admin` (Administrators) | 3 |
| `doc` (Physicians) | 2 |
| `clin` (Clinicians) | 1 |
| `front` (Front Office) | 1 |
| `back` (Back Office) | 1 |
| `breakglass` | 0 |
| `users` (Accounting) | 0 |

**Mapping to the roles named in the task:** Administrator → `admin`; Doctor → `doc`; Nurse/Lab/
Pharmacy → `clin`; Receptionist → `front`; Billing/Accounting → `back` / `users`. Note that
`users` (Accounting) and `breakglass` currently have **zero members** — an Accounting-role demo would
need an account created. Recorded as `DG-010`.

## 12.3 ⚠ A required ACL provisioning step that has *not* been run, even here

`EV-047` §8 states it as a mandatory, easily-missed step:

> *"The `patients|bulk_rep` ACO that gates `patient_list.php` and `unique_seen_patients_report.php`
> **exists only where this has been run**. On a fresh instance without it, the guard resolves against
> a non-existent ACO and **fails closed for every role including Administrators** — the two reports
> become unusable and it presents as a permissions mystery rather than a missing migration (PB-009)."*

**Independent verification during this pass:**

```sql
SELECT a.id, a.section_id, a.value, a.name FROM gacl_aco a WHERE a.value LIKE '%rep%';
→ (0 rows)
```

`gacl_aco_sections` contains `patients` (id 14), but **no `bulk_rep` ACO exists in the live
development database.** The command that creates it —
`src/Console/ProvisionReportAclCommand.php:27,57,66` (`'bulk_rep' => [...]`, *"bulk patient-identifying
reports with CSV/label export. Admin + Physician only"*) — **has apparently never been run on this
instance.**

**Consequence:** the two reports fail closed here *today*, and would fail closed on the demo under
**either** database strategy. `php bin/console thiqa-branding:provision-report-acl` is mandatory on
the target. The command is documented as idempotent (`ProvisionReportAclCommand.php:38` —
*"`addObjectAcl()` checks `get_object_id()` before inserting"*). Recorded as blocker `B-06`.

## 12.4 Can demo users be created after deployment?

**Yes.** Roles are stock OpenEMR `gacl_*` rows plus stock `users`/`users_secure` rows; **the fork adds
no ACL schema** (§1.4). Every account can be created through Administration → Users, or reproduced
by SQL, after deployment. There is **no external IdP dependency** — LDAP is available as an extension
but no LDAP configuration is present in `globals`.

**Requirement:** rotate every demo credential for the public instance rather than reusing the
development ones, and keep them in the secret store per `EV-047` §6 / `EV-048` R-1.

---

# 13 — Module inventory

## 13.1 Enabled modules (live `modules` table)

`SELECT mod_id, mod_name, mod_directory, mod_active, mod_ui_active, type FROM modules`

| Module | mod_dir | active | ui_active | type | Custom/Upstream | Required for demo? | PHP dep | DB dep | External svc | Deployment concern |
|---|---|---:|---:|---:|---|---|---|---|---|---|
| Immunization | `Immunization` | 1 | 0 | 1 (Laminas) | Upstream | Optional | none extra | stock tables | none | none |
| Syndromicsurveillance | `Syndromicsurveillance` | 1 | 0 | 1 | Upstream | Optional | none extra | stock tables | none | none |
| Documents | `Documents` | 1 | 0 | 1 | Upstream | **Yes** — document handling | none extra | stock tables | none | writable `documents/` (§9) |
| Ccr | `Ccr` | 1 | 0 | 1 | Upstream | Optional | none extra | stock tables | none | none |
| Carecoordination | `Carecoordination` | 1 | 0 | 1 | Upstream | Optional | none extra | stock tables | **`ccdaservice` Node app** for C-CDA | §5.6 / `DG-009` |
| **Thiqa Branding** | `oe-module-thiqa-branding` | **1** | 0 | **0 (custom)** | **CUSTOM** | **YES — mandatory** | `symfony/console`, `symfony/event-dispatcher`, `psr/log` (all already in root lock) | **`modules` row + `globals` rows** | none | **§13.2 — the row must exist** |

## 13.2 ⚠ The branding module is invisible without a database row

`src/Core/ModulesApplication.php:141`:

```php
$resultSet = sqlStatementNoLog("SELECT mod_name, mod_directory FROM modules
                                WHERE mod_active = 1 AND type != 1 ORDER BY `mod_ui_order`, `date`");
```

and `:145-146,188` then `include`s `<dir>/openemr.bootstrap.php` for each row. The module's bootstrap
(`openemr.bootstrap.php:30-35`) is what registers the `OpenEMR\Modules\ThiqaBranding\` namespace and
constructs `Bootstrap`, which attaches every branding listener.

**Therefore: deploying the files without inserting the `modules` row yields an unbranded application
with no error message.** The module directory would simply never be read.

Two further consequences worth knowing:

- `ModulesApplication.php:150-155` **auto-disables** a module whose bootstrap is unreadable after 3
  retries (`UPDATE modules SET mod_active = 0`). **A permissions mistake on the module directory is
  therefore self-persisting** — fixing the permissions later does not re-enable it; the row must be
  set back to 1.
- `checkModuleScriptPathForEnabledModule()` (`:98-99`) additionally gates *direct script access* into
  module directories on `(mod_active = 1 OR mod_ui_active = 1)`.

## 13.3 Custom modules present on disk

`interface/modules/custom_modules/`:

| Directory | Registered in `modules`? | Notes |
|---|---|---|
| `oe-module-thiqa-branding` | **Yes, active** | The fork's own module |
| `oe-module-claimrev-connect` | No | **Gitignored** — materialised by `composer install` (§4.1) |
| `oe-module-comlink-telehealth` | No | Upstream-shipped, not enabled |
| `oe-module-dashboard-context` | No | Upstream-shipped, not enabled |
| `oe-module-dorn` | No | Upstream-shipped, not enabled |
| `oe-module-ehi-exporter` | No | Upstream-shipped, not enabled |
| `oe-module-faxsms` | No | **Modified by this fork** (39 files, §1.3) but **not enabled**. External-service module — keep disabled (§14) |
| `oe-module-prior-authorizations` | No | Upstream-shipped, not enabled |
| `oe-module-weno` | No | Upstream-shipped, not enabled |

**Only one module needs enabling on the target.** The other eight ship as dormant directories.

## 13.4 Branding module surface (97 files)

Grouped by namespace, from the file listing:

`Token/` (11) — closed 11-key tenant allowlist, validation, contrast gates ·
`Materialisation/` (14) — atomic stage/verify/commit writer, transactional globals delta ·
`AssetIntake/` (12) — logo validation, SVG inspection, raster dimensions ·
`Config/` (10) — branding profile loader, globals registration listener ·
`Console/` (9) — **`apply-profile`, `materialise`, `verify`, `provision-report-acl`, `seed-demo`, `backup`** ·
`Observability/` (8) — health check, inconsistency detection, materialisation logging ·
`Listener/` (4) — login template, logo override, style injection, Twig override ·
`Service/`, `Asset/`, `Accessibility/`, `Theme/`, `Twig/`, `Tenant/` (the remainder) ·
`templates/api/smart/smart-style_{light,dark}.json.twig` · `config/branding-profile.json` ·
`public/branding-tokens.php` · `public/logos/dark/**`.

**Module migrations: none.** The module has no `install.sql`, no Doctrine migration, and adds no
schema (§1.4).

---

# 14 — External integrations

Classification below is derived from live `globals` enable-flags. **Credentials were checked only for
emptiness; no value was read or printed.**

| Integration | Enable flag | Value | Credentials | Classification |
|---|---|---|---|---|
| **REST API** | `rest_api` | **0** | — | `SHOULD BE DISABLED` — already off |
| **FHIR API** | `rest_fhir_api` | **0** | — | `SHOULD BE DISABLED` — already off |
| **Portal API** | `rest_portal_api` | **0** | — | `SHOULD BE DISABLED` — already off |
| **System-scope API** | `rest_system_scopes_api` | **0** | — | `SHOULD BE DISABLED` — already off |
| **Patient portal** | `portal_onsite_two_enable` | **0** | — | `SHOULD BE DISABLED` — already off. Address is a placeholder (§11.2) |
| **OAuth2 / OIDC** | `oauth_password_grant` 0; `oauth_app_manual_approval` 0; `site_addr_oath` **empty** | — | keypair generated at runtime | `SHOULD BE DISABLED` — inert while APIs are off |
| **SMART on FHIR** | gated by `rest_fhir_api` = 0 | `fhir_us_core_profile_version 8.0.0` | — | `SHOULD BE DISABLED` — already off |
| **eRx / NewCrop** | `erx_enable` **0** | ⚠ `erx_newcrop_path` = **real production** `secure.newcropaccounts.com` endpoints | `erx_account_password` **empty** | **`SHOULD BE DISABLED` — verify before each demo.** Real production URLs are present; only the enable flag stands between the demo and a live vendor endpoint |
| **phiMail (Direct)** | `phimail_enable` **0** | `phimail.example.com` placeholder | `phimail_password` empty | `SHOULD BE DISABLED` — already off. ⚠ its `background_services` row shows `running = -1` (§15) |
| **HylaFAX** | `enable_hylafax` **0** | `localhost`, `/var/spool/hylafax` | — | `SHOULD BE DISABLED` — already off |
| **Fax/SMS module** (`oe-module-faxsms`: Twilio, SignalWire, RingCentral, Clickatell, EtherFax) | not registered in `modules` | — | — | `SHOULD BE DISABLED` — module not enabled. **Modified by this fork**, so the code ships; only the `modules` row keeps it dormant |
| **SMS gateway** | — | `SMS_GATEWAY_USENAME/APIKEY/PASSWORD` all **empty**; `SMS_NOTIFICATION_HOUR = 50` (out of the 0–23 range → never fires) | empty | `SAFE — inert` |
| **Email / SMTP** | `EMAIL_METHOD = SMTP`, `SMTP_HOST = localhost`, `SMTP_PORT = 25`, `SMTP_USER`/`SMTP_PASS` **empty** | — | empty | ⚠ **`REQUIRES SANDBOX`.** `Email_Service` is an **active** background service (§15) and `rx_send_email = 1`. On a VM with a local MTA this could deliver. `EMAIL_NOTIFICATION_HOUR = 50` is out of range, which appears to neuter the scheduled path, but this was **not proven** — `DG-011` |
| **WhatsApp** | — | **no configuration found** | — | `NOT FOUND` |
| **Payment gateways** | `payment_gateway = InHouse`; `cc_front_payments 0`; `cc_stripe_terminal 0`; `portal_two_payments 0` | Stripe/Authorize.Net libraries present via Composer | `gateway_api_key`, `rainforest_*` all **empty** | `SAFE TO ENABLE IN DEMO` — "InHouse" performs no external transaction |
| **USPS address API** | — | `usps_apiv3_client_id/secret` **empty** | empty | `SAFE — inert` |
| **CouchDB** (document storage) | `couchdb_dbase` **empty**, `couchdb_user`/`pass` empty | `localhost:6984` | empty | `SAFE — inert`; documents stored on the filesystem |
| **C-CDA alt service** | `ccda_alt_service_enable` **0** | separate Node app | — | `SHOULD BE DISABLED` (§5.6) |
| **Google / cloud APIs** | `google/apiclient` is a Composer dependency | no configuration in `globals` | — | `SAFE — inert`; library present, unconfigured |
| **Clearinghouse / X12 SFTP** | `background_services.X12_SFTP` **inactive** | — | — | `SHOULD BE DISABLED` — already off |
| **Telemetry / product registration** | `src/Telemetry/TelemetryService.php` and `src/Services/ProductRegistrationService.php` are **both modified by this fork** | — | — | **`UNKNOWN` — `DG-012`.** These are the two outbound-reporting services in OpenEMR; their post-fork behaviour was not read line-by-line in this pass and must be confirmed before a public demo |
| **NPHIES / Saudi payer APIs** | — | **NOT FOUND** — no configuration, code or dependency | — | `NOT FOUND` |
| **Laboratory / radiology interfaces** | `procedure_order` = 0 rows; no lab configuration in `globals` | — | — | `SAFE — inert` |

**Overall posture: the instance is already close to demo-safe.** Every outbound integration is
disabled and every credential field is empty. The three items needing deliberate attention before a
public demo are **SMTP/`Email_Service`** (`DG-011`), **telemetry/product registration** (`DG-012`),
and **keeping `erx_enable` at 0** given that real vendor endpoints are configured.

---

## 14.1 `DG-011` / `B-12` — Email_Service: full execution-path analysis

**Added in Revision 2.** No email was sent and no database row was altered in producing this.

### The service definition (live `background_services` row)

```
name: Email_Service   title: Email Service   active: 1   running: 0
execute_interval: 2   function: emailServiceRun   require_once: /library/email_service_run.php
```

### What actually runs

`library/email_service_run.php:13-16` → `MyMailer::emailServiceRun()` →
`library/classes/postmaster.php:113`. The method's first act (`:117`) is:

```php
$res = sqlStatement("SELECT `id`, `sender`, `recipient`, `subject`, `body`, `template_name`
                       FROM `email_queue` WHERE `sent` = 0");
```

Everything else happens **inside `while ($ret = sqlFetchArray($res))`** (`:122-168`).

### The eight questions, answered

| # | Question | Answer | Evidence |
|---|---|---|---|
| 1 | What code runs when the service executes? | `MyMailer::emailServiceRun()` — drains `email_queue WHERE sent = 0`, renders a Twig body, sends via `MyMailer` | `postmaster.php:113-171` |
| 2 | Under what conditions does it send? | Only for a row in `email_queue` with `sent = 0`, **and** only when `isConfigured()` is true | `postmaster.php:117,121,130` |
| 3 | **Can an active service alone attempt outbound delivery?** | **No.** With an empty queue the `while` body never executes. Activity of the service is not sufficient; a queued row is required | `postmaster.php:117,122` |
| 4 | Does absence of a local MTA prevent delivery safely? | It prevents *delivery* but **not attempts**, and it creates a **permanent retry loop**. See ⚠ below | `postmaster.php:128,153-162` |
| 5 | **Does any queue already contain pending messages?** | **No.** `SELECT COUNT(*) FROM email_queue` → **0 rows, total** | live query |
| 6 | Is deactivating the service row sufficient? | **For the queued pathway, yes. For the demo overall, no** — see 7 and 8 | below |
| 7 | Must `rx_send_email` also be `0`? | **Yes.** The prescription email feature uses `new MyMailer()` **directly** (`controllers/C_Prescription.class.php:1118`), bypassing the queue and therefore bypassing the background service entirely. Deactivating the service does **not** neutralise it | `C_Prescription.class.php:1118` |
| 8 | Do other mail pathways remain active independently? | **Yes — six direct-send call sites**, all user-initiated rather than scheduled: `controllers/C_Document.class.php:1286`, `interface/usergroup/usergroup_admin.php:87`, `interface/billing/sl_eob_search.php:230`, `src/Easipro/Easipro.php:118,170`, `src/Services/PatientAccessOnsiteService.php:281`, plus the explicit admin test page `interface/usergroup/email_send_test.php` / `src/Services/Email/EmailTestService.php` | grep |

### ⚠ The retry loop, stated precisely — it is the real operational hazard

`emailServiceRun()` wraps the whole drain in `QueryUtils::startTransaction()` (`:115`). For each row
it sets `sent = 1` **first** (`:128`), then attempts the send. On failure it throws (`:156`, `:162`),
which rolls the transaction back — **including the `sent = 1` flag**. The row therefore returns to
`sent = 0` and is retried **on every 2-minute tick, indefinitely**, writing an `error_log` line each
time (`:155`).

So the honest answer to "is a missing MTA safe?" is: **safe from a disclosure standpoint, but it
produces unbounded error noise rather than a clean stop.**

### Why nothing is queued today, and why that is fragile

The queue is fed by `MyMailer::emailServiceQueue()` (`postmaster.php:100`), which returns `false`
without inserting if **any** of sender/recipient/subject/body is empty (`:102-104`). The two
security-notification callers — `src/Common/Auth/AuthUtils.php:1370` (IP-block) and `:1391`
(username-block) — pass `patient_reminder_sender_email` and `practice_return_email_path` as sender
and recipient, and **both globals are empty** on this instance. So nothing enqueues.

**That is a fortunate accident, not a control.** Those two callers fire on repeated failed logins —
exactly what a public demo on a static IPv4 attracts within hours of exposure. If either global were
populated at provisioning, a scan would begin queueing mail, and the retry loop above would begin.

### The decisive control

`MyMailer::isConfigured()` (`postmaster.php:49-66`): when `EMAIL_METHOD === 'SMTP'` it requires
`SMTP_HOST` and `SMTP_PORT` to be non-empty. **Blanking `SMTP_HOST` makes it return `false`**, at
which point the queued path logs `"Email method not configured"` and never constructs a mailer, and
every direct caller gets `$this->Host = ''` (`:204`) so PHPMailer's SMTP connect fails immediately
rather than reaching a real server.

⚠ **This control is specific to `EMAIL_METHOD = 'SMTP'`.** For `PHPMAIL` or `SENDMAIL`,
`$requiredKeys` stays empty and `isConfigured()` returns **`true`** unconditionally (`:51-65`), and
`emailMethod()` selects PHP's `mail()` or a sendmail binary (`:198-213`). **`EMAIL_METHOD` must
therefore be left at `SMTP`** for the blanking control to hold.

### `DG-011 CLOSED BY PROVISIONING CONTROL — NO SOURCE PATCH REQUIRED`

Changing `MyMailer` would modify generic upstream email behaviour for every pathway in every
deployment — disproportionate to a demo-safety requirement that four configuration values satisfy
completely. Exact provisioning state required:

```sql
-- 1. Stop the scheduled drain. Also raises the cron tick floor from 2 min to 240 min (§15.3).
UPDATE background_services SET active = 0 WHERE name = 'Email_Service';

-- 2. The decisive control: isConfigured() false, and every direct caller has nowhere to connect.
UPDATE globals SET gl_value = ''  WHERE gl_name = 'SMTP_HOST';

-- 3. Close the direct-send prescription pathway, which bypasses the queue entirely.
UPDATE globals SET gl_value = '0' WHERE gl_name = 'rx_send_email';

-- 4. Keep EMAIL_METHOD at SMTP — PHPMAIL/SENDMAIL would defeat control 2.
--    Leave patient_reminder_sender_email and practice_return_email_path EMPTY, so the
--    AuthUtils security notifications cannot enqueue under scan traffic.
```

Verification checks (all read-only):

```sql
SELECT name, active FROM background_services WHERE name = 'Email_Service';   -- expect active = 0
SELECT gl_name, gl_value FROM globals
 WHERE gl_name IN ('SMTP_HOST','rx_send_email','EMAIL_METHOD',
                   'patient_reminder_sender_email','practice_return_email_path');
SELECT COUNT(*) FROM email_queue WHERE sent = 0;                             -- expect 0
```

```bash
ss -lntp 'sport = :25' || true    # expect no listener on localhost:25
```

**Not executed. No database was modified.**

---

## 14.2 `DG-012` / `B-13` — Telemetry and product registration

**Added in Revision 2.** Both files are modified by this fork and both already carry governance
records — **PR-06** (`ProductRegistrationService`) and **PR-07** (`TelemetryService`) in
`docs/branding/adr/patch-records.md`. Those records describe intent at commit time; what follows
re-verifies the **current** state of the source and the live configuration.

### Product registration — the network call no longer exists

PR-06 records the removal of the cURL POST to `https://reg.open-emr.org/api/registration`.
**Re-verified this pass:** a grep of `src/Services/ProductRegistrationService.php` for
`curl|https?://|file_get_contents|Guzzle|HttpClient` returns **only the two documentation URLs in the
file header docblock** (`:7`, `:13`). There is no outbound call construct in the file. The method now
writes the operator's preference to the local `product_registration` table and returns.

**There is no endpoint to disable, because none is contacted.**

### Usage telemetry — endpoint still present, gated three independently sufficient ways

Unlike registration, this was consent-gated rather than removed, so the endpoint does still exist:
`https://reg.open-emr.org/api/usage` at `src/Telemetry/TelemetryService.php:202`, reached via
`executeCurlRequest()` (`:240`, `:317-334`).

Every caller funnels through `isTelemetryEnabled()` (`:57-101`):

| Caller | Guard | Line |
|---|---|---|
| `reportUsageData()` | `if (empty($this->isTelemetryEnabled())) { return ...; }` | `:161-163` |
| `trackApiRequestEvent()` | `if (!empty($this->isTelemetryEnabled()))` | `:267-269` |
| `reportClickEvent()` | same gate | `:112` |
| `GeoTelemetry` (its own outbound HTTP) | reached **only** from `reportUsageData()` at `:176`, i.e. **after** that method's guard | `:176`, `:303-305` |

`GeoTelemetry` deserves the explicit note: it is a second outbound caller with its own HTTP client
(`src/Telemetry/GeoTelemetry.php:230`) and would be easy to miss. It is unreachable unless
`reportUsageData()` passes its guard.

**The three independent reasons the gate is closed on a fresh instance:**

| # | Layer | State | Evidence |
|---|---|---|---|
| 1 | **The fork's consent global** `enable_usage_telemetry` | **Does not exist.** Not in the `globals` table, and — decisively — **not registered in `library/globals.inc.php` either**, so no admin UI can set it. `getBoolean()` on an absent key answers `false` | `git grep enable_usage_telemetry` returns only the gate itself (`TelemetryService.php:75,95`) and its tests; `SELECT … WHERE gl_name LIKE '%telemetry%'` → **no row** |
| 2 | **The upstream table logic** | `product_registration` holds **1 row with `telemetry_disabled = 1` and `opt_out = 1`**. The query at `:85` looks for `telemetry_disabled = 0`, matches nothing, so `$isEnabled = 0` — **before the fork's gate is even consulted** | live query |
| 3 | **The environment kill switch** | `OPENEMR_DISABLE_TELEMETRY` forces `$allowTelemetry = false` | `interface/main/tabs/main.php:43,59-64` |

Layers 1 and 2 are each individually sufficient. On a **fresh** Option 3 database layer 2 also holds,
because `product_registration` starts empty and an empty result likewise yields `$isEnabled = 0`.

### Is anything invoked automatically?

| Trigger | Reached? |
|---|---|
| **Background service** | **No.** `BackgroundTaskManager::modifyTelemetryTask()` (`src/Telemetry/BackgroundTaskManager.php:25-39`) can create a `Telemetry_Task` row — but **no such row exists**: `SELECT … WHERE name LIKE '%elemetry%'` returns nothing, and the table holds only the five services listed in §15.3 |
| **Page load** | `interface/main/tabs/main.php:157` calls `isTelemetryEnabled()` on every main-page render — but **only to set a JS variable**, `var telemetryEnabled = …`. With the gate closed it renders `0`, and `interface/main/tabs/js/tabs_view_model.js:357-358` skips sending |
| **Login / install / cron** | No other invocation found |

### What could be transmitted if it were enabled

Site UUID, software version, geo-derived locale, tab-navigation click events and API request events.
**No patient data is in the payload.** Recorded for completeness; the gate is closed.

### `DG-012 CLOSED BY VERIFIED DISABLED-BY-DEFAULT / PROVISIONING CONTROL`

**No source change made or required.** A fresh demo provision makes **zero** telemetry or
product-registration calls. Recommended belt-and-braces provisioning step, since it costs nothing:

```bash
# In the Apache environment, as an explicit third layer:
OPENEMR_DISABLE_TELEMETRY=1
```

Verification (read-only):

```sql
SELECT COUNT(*) FROM background_services WHERE name LIKE '%elemetry%';  -- expect 0
SELECT gl_name FROM globals WHERE gl_name = 'enable_usage_telemetry';   -- expect no row
SELECT telemetry_disabled, opt_out FROM product_registration;           -- expect empty, or 1 / 1
```

⚠ **One caveat carried forward, not resolved here.** `docs/branding/coverage-matrix.md` row 34
records an **open policy question**: *"consent-gated is not the same as never-contacted."* BRAND-113
("the product does not contact OpenEMR infrastructure") is satisfied for registration by removal, but
telemetry is satisfied by a gate. **That is a governance decision for the D-series owner, not an
engineering gap**, and closing `DG-012` does not close it.

---

# 15 — Cron / scheduled / background jobs

## 15.1 The single scheduled entry point

`EV-047` §9 specifies exactly one recurring trigger:

```bash
php <abs-path>/bin/console background:services run
```

with three rules, quoted:

> *"Tick interval ≤ the **shortest active** service interval … Use an **absolute** path to
> `bin/console` — schedulers frequently set no working directory … Run as an account that can see the
> application directory."*

## 15.2 ⚠ `bin/console` refuses to run as root

`bin/console:22-25`:

```php
// Refuse to run as root. The `--skip-globals` path below would bypass the
// guard in interface/globals.php; enforce here so all console commands are
// covered regardless of bootstrap mode.
OpenEMR\Common\Command\RootCliGuard::assertNotRoot();
```

**Every cron entry must therefore run as `www-data` (or another non-root account that can read the
application tree and write the paths in §9).** A `/etc/cron.d` entry without a user field, or a root
crontab, will fail on every tick. This also prevents the `CacheDirectory` ownership trap in §9.2.

## 15.3 Registered services (live `background_services` table)

| Service | Interval (min) | `active` | `running` | Next run | Assessment for the demo |
|---|---:|---:|---:|---|---|
| `Email_Service` | **2** | **1** | 0 | 2026-08-15 21:30:20 | ⚠ **Unsafe until §14/`DG-011` is settled.** Also sets the tick floor at 2 minutes |
| `UUID_Service` | 240 | **1** | 0 | 2026-08-16 01:23:13 | **Mandatory for core operation** — UUID backfill underpins FHIR/API identity |
| `phimail` | 5 | 0 | **-1** | 2026-08-07 05:26:06 | Inactive. ⚠ `running = -1` is a stuck/aborted marker — see below |
| `X12_SFTP` | 1 | 0 | 0 | 2021-01-18 | Inactive — **production-only**, keep off |
| `MedEx` | 0 | 0 | 0 | 2017-05-09 | Inactive — keep off |

**Tick interval implication:** with `Email_Service` at 2 minutes, the runbook's rule
("≤ the shortest **active** interval") mandates a **2-minute cron**. If `Email_Service` is
deactivated for the demo, the floor rises to `UUID_Service`'s 240 minutes and a much gentler schedule
suffices. This is a concrete reason to settle `DG-011` before writing the crontab.

**`phimail.running = -1`:** the service is inactive, so this is currently inert, but the stale marker
would travel with a cloned database. Under Option 3 the table is created fresh by the installer and
the marker does not exist. One more small point in favour of the fresh-install path.

## 15.4 Other scheduled/CLI surfaces found

| Item | Location | Assessment |
|---|---|---|
| `thiqa-branding:verify` | `src/Console/VerifyCommand.php:35-38` | **Optional but recommended.** Documented as *"safe to run against production on a schedule"*; structurally read-only (holds only `BrandingHealthCheck`, two read methods, no transaction — `BrandingHealthCheck.php:34-40`). Exit `1` makes it usable directly as a health probe |
| `thiqa-branding:backup` | `src/Console/BackupCommand.php` | Optional; backup policy is `RDY-0081`, **open** |
| OpenEMR native backup | `interface/main/backup.php` | Depends on `mysql_bin_dir` (§11.2). `EV-047` §11 smoke test **S-3** exercises it |
| Audit-log tamper report | referenced by `EV-047` §11 **S-5** | ⚠ *"Do not run it over a window containing an `api_log` row — PB-030's false positive is still open"* |
| `bin/command-runner` | `bin/` | CLI helper; `bin/.htaccess` denies web access |

**No `crontab`, `systemd` timer, `.service` unit or queue-worker definition exists in the
repository** — the scheduler is created at provisioning, per `EV-047` §9. Nothing to port.

---

# 16 — Rebranding deployment

**`rebranding.md` exists** at `docs/rebranding.md` and was read (it is the certified inventory:
**136 BRAND IDs, exactly one assigned action each** — SET-CONFIG, PATCH, SET-TRANSLATION, PRESERVE).

## 16.1 Governance hierarchy (authority order, highest first)

1. `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md` — Q1–Q77, Invariants 1–10.
   **Binding**; reopened only by a new ADR. *(Folder name is misspelled "Desicions" in the repo.)*
2. `Locked Desicions/OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md`
3. `docs/rebranding.md` §16.2 — the 136-ID certified inventory
4. `docs/RebrandingPlan.md` — Group 2 implementation plan
5. `docs/branding/` — as-built docs, 4 ADRs, patch records
6. `docs/RebrandingBugs.md` — audit + remediation record

Also present: `docs/branding-production/` (17 documents, 16 of them SHA-256-manifested) and
`Locked Desicions/OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt`.

## 16.2 What is implemented, and where it lives

| Layer | Where | Source-controlled? | Present after a git deploy? |
|---|---|---|---|
| Product identity (~33 `globals`) — `openemr_name = Thiqa`, tagline, logo widths, `skyeagle.uk` links, acknowledgement suppression | **Database** | The *source* is: `oe-module-thiqa-branding/config/branding-profile.json` (tracked) | **No — must be applied** via `thiqa-branding:apply-profile` |
| Branding module code (97 files) | `interface/modules/custom_modules/oe-module-thiqa-branding/` | **Yes** | Yes — but inert without the `modules` row (§13.2) |
| Theme SCSS (Thiqa palette) | `interface/themes/thiqa/*.scss`, `interface/themes/oe-styles/style_thiqa_{light,dark}.scss` | **Yes** | Yes (source); **compiled CSS is not** — §5 |
| Q77 theme pruning | `webpack.themes.js:151-197` | **Yes** | Yes — enforced by the build |
| Logos and favicons | `public/images/**`, `sites/default/images/**`, module `public/logos/**` | **Yes** | **Yes** |
| Brand masters, tokens, manifests | `brand/` — 111 tracked files | **Yes** | Yes |
| Generated token artefacts | `interface/themes/thiqa/_tokens-{light,dark}.scss`, `_css-variables.scss`, `_typography.scss`, `templates/api/smart/smart-style_{light,dark}.json.twig` | **Yes** — committed, byte-reproducible from `brand/tokens/*.json` via `tools/branding/bin/generate-tokens.php` | Yes |
| Login page Twig | `templates/login/partials/html/primary_logo.html.twig` | **Yes** | Yes |
| Arabic product name | `globals.saas_branding_product_name_ar = ثقة` | **Database** | No — set at provisioning |
| **Materialised Tier-2 stylesheets** | `…/oe-module-thiqa-branding/public/branding/default/tokens-{light,dark}.css` | ⚠ **NO — gitignored** (`.gitignore:73`) | **No** — §16.3 |

**Manifest integrity — verified this pass.** `brand/manifests/SHA256SUMS` lists **123** paths (107
under `brand/`, 16 under `docs/branding-production/`). Every one of the 123 is **tracked by git**
(checked against the full `git ls-files` index) and **present on disk**. The 111-vs-123 arithmetic
that looks like a discrepancy is simply that `brand/` holds 111 tracked files of which 107 are
manifested. **No brand asset is missing from version control.**

## 16.3 ⚠ Materialised branding state is recorded in the DB but not in git

Live `globals` values:

| Key | Value |
|---|---|
| `saas_branding_revision` | **`1`** |
| `saas_branding_materialised_at` | `2026-08-10T18:50:40+00:00` |
| `saas_branding_tokens_light` / `_dark` | *(empty)* |
| `saas_branding_product_name_ar` | `ثقة` |

The stylesheets that revision 1 refers to exist on the dev disk but are **gitignored**:

```
$ git check-ignore -v -- .../public/branding/default/tokens-light.css
.gitignore:73  interface/modules/custom_modules/oe-module-thiqa-branding/public/branding/
$ git ls-files .../public/branding  →  0 files
```

The gitignore comment states the intent (`.gitignore:66-72`):

> *"Per-tenant branding runtime state, written by `thiqa-branding:materialise`. This is one tenant's
> materialised output, not source … Without this rule a routine `git add -A` commits tenant runtime
> state into the source tree (docs/RebrandingBugs.md RB-04)."*

**Failure mode this creates for Option 2 (clone the DB):** the target would record
`saas_branding_revision = 1` while both stylesheets are absent. Per `docs/branding/runbook.md` §6.1,
`BrandingHealthCheck` would classify that as `RevisionWithoutStylesheet` — an **`Inconsistencies`
section and exit code 1** from `thiqa-branding:verify`. The runbook explicitly notes this state *"is
not covered by any command in this module"* and needs manual resolution.

**Under Option 3 the problem does not arise:** a fresh database has `saas_branding_revision = 0`,
which `BrandingHealthCheck::statusFor()` (lines 172-190) reports as
`NeverMaterialised` — *"a consistent, healthy state … this is by design, not a defect."*

Recorded as blocker `B-05`.

## 16.4 Branding facts verified live this session

Several of these supersede older notes:

| Claim | Verified value | Method |
|---|---|---|
| Login page is branded | `<title>Thiqa Login</title>`, HTTP 200, 9,165 B | live `Invoke-WebRequest` |
| `openemr_name` | `Thiqa` | `globals` |
| `login_tagline_text` | `Clinical confidence, connected care.` | `globals` |
| Acknowledgement links suppressed | `display_acknowledgements = 0`, `display_acknowledgements_on_login = 0` | `globals` |
| **Facility name** | **`Thiqa Demo Eye Clinic`**, `country_code = SA`, `state = Riyadh Region` | `facility` table — **this supersedes any earlier note that it was still the installer placeholder** |
| **Regional configuration** | `gbl_time_zone = Asia/Riyadh`, `gbl_currency_symbol = SAR`, `phone_country_code = 966`, `units_of_measurement = 2` (metric) | `globals` — **also supersedes earlier "untouched" notes** |
| Theme in use | `css_header = style_light.css`, `theme_tabs_layout = tabs_style_full.css` | `globals` |
| Deployed themes Q77-clean | 18 files, zero `solar`/`manila`/`cobalt_blue`/`forest_green` | directory listing |

## 16.5 Branding changes that must be present for the demo

1. The `modules` row enabling `oe-module-thiqa-branding` (§13.2) — **without it, nothing else matters**.
2. `thiqa-branding:apply-profile --site=default` — the ~33 identity `globals`.
3. `public/themes/` built from the Q77-pruned entry map (§5.5).
4. The tracked logo/favicon assets (arrive with git).
5. The Apache deny on `acknowledge_license_cert.html` (§10.5 item 9) — **a governance requirement**
   (constraint C7, BRAND-063/118 = PRESERVE), because the `globals` only hide the *links*; the static
   file stays reachable by direct URL.
6. Regional configuration (§16.4) — set at provisioning, not carried by code.

**No branding was modified in producing this report.**

---

# 17 — Other existing project documentation

Documents located and read (fully or in the sections relevant to deployment):

| Document | Relevance | Read |
|---|---|---|
| `docs/evidence/EV-047-deployment-runbook.md` | **The authoritative provisioning runbook** | **Full** |
| `docs/evidence/EV-048-secrets-handling.md` | Credential posture and its acceptance failure | **Full** |
| `docs/evidence/EV-055-audit-phi-determination.md` | PHI in the audit trail | §0–§1 |
| `docs/branding/runbook.md` | Branding CLI operations, exit codes, Q77 build | **Full** |
| `docs/evidence/EV-028-synthetic-data-control.md` | Synthetic-data conventions governing the seeder | Cited via `SeedDemoCommand.php:36` |
| `docs/evidence/EV-044-demo-reset-runbook.md` | Demo reset procedure | Located, paths inspected |
| `docs/evidence/EV-083-background-service-trigger.md` | Background-service scheduling evidence | Located, paths inspected |
| `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md` | **Binding** Q1–Q77, Invariants 1–10 | Located; Q12/Q17/Q25/Q76/Q77 and Invariants 4/9 applied throughout |
| `Locked Desicions/…-Implementation-Backlog-and-Acceptance-Criteria-….md` | MVP-010, MVP-014 acceptance | Located |
| `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` (797,661 B) | The RDY-#### readiness register | Targeted reads (RDY-0047/0048/0064/0080–0085) |
| `docs/rebranding.md` | 136-ID certified brand inventory | §16.2 structure |
| `docs/00-discovery/*.md` (18 files) | Prior architecture discovery | Inventoried |
| `docs/HISModulesUsers.md` | Capability/config register (`mysql_bin_dir` OD-01 at `:3333`) | Targeted reads |
| `docs/branding/{architecture,changes,coverage-matrix,remaining-dependencies,multi-tenant-white-label-readiness}.md` + 4 ADRs + `patch-records.md` | As-built branding | Located; cited via the runbook |
| `docs/branding-production/*` (17 docs) | Group 1.5B certification | Inventoried; `16-conflict-resolutions.md` §12 cited |
| `CLAUDE.md` / `CLAUDE.local.md` | Upstream standards / this host's runtime | **Full** |
| `SETUP-STATUS.md` | Host stack description (Windows-specific) | Read |
| `docs/docker-migration-from-devops.md` | ⚠ **Out of scope** — Docker path, explicitly excluded by the task | Not applied |

## 17.1 Documentation / implementation conflicts found

| # | Conflict | Documentation says | Code/live evidence says | Resolution |
|---|---|---|---|---|
| **C-1** | Application version | `version.php:18-20` → **8.2.0** | `openemr.version` row → **8.3.0-dev** | **Real conflict.** Cause: DB installed from a master checkout. Resolved by Option 3 (§7) |
| **C-2** | `patients\|bulk_rep` ACO | `EV-047` §8 implies it is provisioned by running the command | `SELECT … FROM gacl_aco WHERE value LIKE '%rep%'` → **0 rows** on the dev instance | **Not a doc error** — the command has simply never been run here. Blocker `B-06` |
| **C-3** | Tier-2 materialisation | `docs/branding/runbook.md` §6.1 records a live `verify` run showing *"never materialised, Revision 0, stylesheets absent"* (2026-08-10) | `saas_branding_revision = 1`, `materialised_at = 2026-08-10T18:50:40Z`, stylesheets **present on disk** | **Not a conflict — the runbook snapshot is simply earlier that day.** Current state is revision 1. Consequence in §16.3 |
| **C-4** | Runbook target platform | `EV-047` §2 P-1: *"Host meeting the target profile: **Windows Server**, PHP 8.3.x, MariaDB 11.x, Apache 2.4"* | This task targets **Ubuntu 24.04, MariaDB 10.11** | **Gap in the runbook, not an error.** `EV-047` was written for the Windows host. Its steps 2–8 are platform-neutral; steps 1 and 9 are Windows-specific. `DG-013` |
| **C-5** | Brand manifest count | `docs/branding/runbook.md` §6.3: *"123/123 verified"* | 123 manifest entries, all tracked and present; `brand/` itself holds 111 tracked files | **No conflict** — 107 of the 123 are under `brand/`, 16 under `docs/branding-production/`. Arithmetic reconciles |

---

# 18 — Windows → Ubuntu portability audit

Scans performed with ripgrep across the tracked tree, plus targeted `Select-String` on specific files.

| # | Finding | Location | Classification |
|---|---|---|---|
| **W-1** | **Hard-coded `C:/` path in application code** — `BASELINE_PATH = 'C:/openemr-stack/backups/protected/rdy0044a/…sql'`, checked with `is_file()` as a fail-closed precondition | `oe-module-thiqa-branding/src/Console/SeedDemoCommand.php:101-103, 335-337` | **BLOCKER** (for the seeder only; the web application is unaffected) |
| **W-2** | Drive-letter paths in evidence harnesses | `docs/evidence/harnesses/rdy0042-probe.php:14` (`G:/…front_office.json`, but `$argv[1] ?? …` — overridable); `rdy0016-matrix.php:16` (`C:/openemr-stack/secrets/…`, hard-coded) | **LIKELY SAFE** — not runtime code; `docs/` is not deployed |
| **W-3** | `tools/branding_production.py:11` — `Path("C:/Program Files").glob("ImageMagick-*/magick.exe")` | build-time Python tool | **LIKELY SAFE** — not deployed, not runtime |
| **W-4** | PowerShell-only scripts (`start-openemr.ps1`, `stop-openemr.ps1`) | `C:\openemr-stack\` — **outside the repository** | **LIKELY SAFE** — nothing to port; systemd replaces them |
| **W-5** | Windows path values in the **database** — `mysql_bin_dir`, `perl_bin_dir`, `temporary_files_dir`, `backup_log_dir` | `globals` table | **MUST CHANGE** if the DB is cloned; **not applicable** under Option 3 |
| **W-6** | `C:\windows` references in `php.ini` files under `docker/` and `ci/` (39 hits) | stock PHP `php.ini` **comments** | **LIKELY SAFE** — comment text in files not used by this deployment |
| **W-7** | Apache 2.2 directives (`order`, `Deny From`, `Allow From`) | `bin/.htaccess`, `sites/default/documents/.htaccess` | **MUST CHANGE or require `access_compat`** — §10.3 |
| **W-8** | `.exe` dependencies | Only in `docs/` and `SETUP-STATUS.md` prose, and `tools/branding_production.py` | **LIKELY SAFE** |
| **W-9** | Backslash filesystem assumptions | `interface/globals.php:57` **explicitly normalises** `\` → `/`; no other tracked source builds paths with `\` | **LIKELY SAFE** |
| **W-10** | Case-insensitive filename assumptions | **UNKNOWN — not exhaustively tested.** Upstream CI runs the full suite on `ubuntu-24.04` (case-sensitive) and passes, which is strong evidence for upstream files. Fork-added files were not individually case-audited | **UNKNOWN — `DG-014`** |
| **W-11** | CRLF in executable scripts | `bin/console` begins `#!/usr/bin/env php`. `.gitattributes` has no global `* text=auto`; git's default `core.autocrlf` on Windows can commit CRLF. **If `bin/console` carries CRLF, the shebang breaks on Linux** with `bad interpreter` | **MUST VERIFY — `DG-015`** |
| **W-12** | IIS / XAMPP / WAMP dependency | None. Apache Lounge + `mod_php` only; the sole `xampp` string is the stock `perl_bin_dir` **default value** from `library/globals.inc.php` | **LIKELY SAFE** |
| **W-13** | Windows services | None — Apache and MariaDB run as console processes here (`CLAUDE.local.md` §4a) | **LIKELY SAFE** |
| **W-14** | Windows symlinks / junctions / UNC paths | None found; the Drive mount **rejects** junctions (`CLAUDE.local.md` §8) | **LIKELY SAFE** |
| **W-15** | Windows-specific PHP configuration | `extension=php_imagick.dll`, `php_redis.dll`; ZTS build; `PHPIniDir` | **MUST CHANGE** — but it is host configuration, not repository content |
| **W-16** | File/folder capitalisation conflicts | Directory `Locked Desicions/` (space + misspelling) is tracked and legal on Linux. No two tracked paths differing only by case were found | **LIKELY SAFE** |
| **W-17** | `bin/console` refuses root | `bin/console:25` | **MUST CHANGE the cron design** (run as `www-data`), not the code — §15.2 |
| **W-18** | `sys_get_temp_dir()` + systemd `PrivateTmp` | `src/Services/Storage/CacheDirectory.php:57-58` | **MUST PLAN FOR** — §9.2, `DG-007` |

**Summary: the application source is portable.** Exactly **one** blocker (`W-1`) lives in
application code, and it is a rollback-safety precondition in a provisioning CLI, not in any
web-served path. The remaining "must change" items are database values, server configuration and
cron design — none of them source edits.

---

# 19 — Secret and configuration audit

**No secret value is printed below. Nothing was rotated, read into this document, or transmitted.**

| Secret/config type | File/location | Present? | Git tracked? | Must rotate/change for demo? |
|---|---|---|---|---|
| Database credentials (live) | `sites/default/sqlconf.php` (working tree) | **PRESENT — REDACTED** | Tracked, but `skip-worktree` — see below | **YES — mandatory** |
| Database credentials (committed blob) | `sites/default/sqlconf.php` at `HEAD` | **PRESENT — REDACTED** | **Tracked** | **YES — see §19.1** |
| Demo user passwords | `C:\openemr-stack\secrets\thiqa-demo-credentials.json` | **PRESENT — REDACTED** | **No — outside the repository** | **YES** — regenerate for the public instance |
| Site encryption keys (AES + HMAC) | `sites/default/documents/logs_and_misc/methods/*` | **PRESENT — REDACTED** | **No** — `.gitignore:84` | **Generated fresh at install** (§8.2) |
| OAuth2 keypair | `sites/default/documents/certificates/*.key` | **PRESENT — REDACTED** | **No** — `.gitignore:83` | **Generated fresh at runtime** |
| API tokens / OAuth client secrets | `globals` — `gateway_api_key`, `rainforest_*`, `usps_apiv3_*`, `SMS_GATEWAY_*` | **NOT FOUND** (all empty) | n/a | No |
| SMTP credentials | `globals.SMTP_USER` / `SMTP_PASS` | **NOT FOUND** (empty) | n/a | No — but see `DG-011` |
| eRx / NewCrop credentials | `globals.erx_account_password`, `erx_account_name` | **NOT FOUND** (empty) | n/a | No |
| phiMail credentials | `globals.phimail_username` / `phimail_password` | **NOT FOUND** (empty) | n/a | No |
| CouchDB credentials | `globals.couchdb_user` / `couchdb_pass` | **NOT FOUND** (empty) | n/a | No |
| GitHub tokens | repository | **NOT FOUND** | n/a | No |
| TLS certificates / private keys | repository | **NOT FOUND** — none tracked | n/a | **Provision on the VM** |
| `.env` | repository root | **NOT FOUND** (`Test-Path .env` → False) | `.gitignore:6` | No |

## 19.1 The one item that needs framing precisely

`sites/default/sqlconf.php` is git-tracked. This report **independently re-verified** the blob
identity claimed in `EV-048` §1.1:

```
git rev-parse HEAD:sites/default/sqlconf.php             → e6be8476dadc010e5bc07b00b7e851418e5d5abe
git rev-parse upstream/master:sites/default/sqlconf.php  → e6be8476dadc010e5bc07b00b7e851418e5d5abe   (identical)
git rev-parse <merge-base>:sites/default/sqlconf.php     → e6be8476dadc010e5bc07b00b7e851418e5d5abe   (identical)
git ls-files -v sites/default/sqlconf.php                → S sites/default/sqlconf.php   (skip-worktree)
$config in the committed blob                            → 0   ("not yet configured")
```

**Therefore: `SECURITY BLOCKER` does *not* apply in the "a secret was committed" sense.** This
project has committed nothing; the blob at `HEAD` is byte-identical to upstream's.

**But `EV-048` §1.2 identifies the real exposure, and it stands:**

> *"The live database password is `openemr`. It is the upstream placeholder, unchanged. … it ships in
> every clone of OpenEMR in the world … **the credential was never changed from a public default.**"*

**Classification for this deployment: `P1` — must resolve before public demo.** What limits it today
(`EV-048` §1.3) — loopback-only MariaDB, genuine least privilege proven by PB-007, no real patient
data — **is exactly what stops being true on a public VM with a static IPv4**. `EV-047` §6 already
makes a per-instance generated password a mandatory provisioning step; under Option 3 that step is
executed by construction.

Two further notes:
- **`skip-worktree` is not a security control.** `EV-048` R-3: *"It lives in one local git index,
  does not travel with a clone, is invisible to review."* **A `git clone` on the Ubuntu VM will
  produce the upstream blob with `$config = 0`** — which is exactly right, because the installer
  then writes the real one.
- Sensitive report/evidence paths under `docs/` reference the host secret store by path only; **no
  value is embedded in any tracked file** (verified by the greps in §18).

---

# 20 — Demo data readiness

**No patient value, name, identifier or clinical detail is reproduced in this report.**

## 20.1 Category inventory (counts only)

| Category | Rows | Source |
|---|---:|---|
| Patients | **30** | `patient_data` |
| Encounters | **72** | `form_encounter` |
| Appointments | **37** | `openemr_postcalendar_events` |
| Providers / users | **10** (6 named clinical, 1 admin, 3 inactive service accounts) | `users` |
| Receptionist / doctor / nurse / lab / pharmacy roles | 7 ACL groups, 8 memberships (§12.2) | `gacl_*` |
| Accounting role | **0 members** in the `users` (Accounting) group — `DG-010` | `gacl_*` |
| Insurance companies (payers) | **2** | `insurance_companies` |
| Insurance policies | **0** | `insurance_data` |
| Price lists / services | **250** code rows | `codes` |
| Billing / charges | **36** | `billing` |
| Claims | **0** | `claims` |
| Documents | **10** (30 files, **106,076 bytes total** on disk) | `documents`; directory measurement |
| Prescriptions | **12** | `prescriptions` |
| Procedure orders (lab) | **0** | `procedure_order` |
| Problem/allergy/medication lists | **12** | `lists` |
| Reports | generated on demand; no stored artefacts | — |
| Audit / log | **70,372** | `log` |
| `audit_master` | 0 | `audit_master` |

## 20.2 Is the data synthetic, production-like, or unknown?

### Verdict: **SYNTHETIC — with high confidence, from four independent lines of evidence.**

1. **It is machine-generated by a committed seeder.** `SeedDemoCommand.php:36` —
   *"Every value is synthetic and governed by `docs/evidence/EV-028-synthetic-data-control.md`"* —
   with `RANDOM_SEED = 20260813`, `MARKER = 'SYN-'`, and fixed name tables (`:62-114`). The declared
   targets match the live counts exactly (§7.2).
2. **The identifier field does not carry a valid real-world identifier.** Aggregate probe (no values
   read):
   ```sql
   SELECT SUM(ss REGEXP '^[0-9]{10}$'), SUM(ss REGEXP '^[0-9]{3}-[0-9]{2}-[0-9]{4}$'),
          SUM(ss LIKE '1%'), SUM(ss LIKE '2%'), COUNT(DISTINCT ss), COUNT(*)
     FROM patient_data;
   → 30, 0, 0, 0, 30, 30
   ```
   All 30 are 10-digit and distinct; **none is US-SSN-formatted; none begins with `1` or `2`**, the
   only two leading digits a real Saudi national ID can have. `EV-055` independently records them as
   *"national-ID-class value (`999…`)"*.
3. **Contact fields are empty.** `SUM(email IS NULL OR email='') = 30`,
   `SUM(phone_home IS NULL OR phone_home='') = 30` — no patient carries a reachable contact detail.
4. **Names come from the seeder's fixed tables** (`SeedDemoCommand.php:106-114`) — 15 distinct
   surnames across 30 patients, consistent with drawing from a 15-entry `FAMILY` array.

`EV-055` states the measurement basis plainly: *"Measured against: the **seeded** demo system
(Marketing MVP Seed v1), **never a real-patient system**."*

## 20.3 ⚠ The audit log is the one place that needs a decision

`EV-055` §0–§1 (independently consistent with the 70,372 `log` rows measured here):

> *"Bind parameters are appended verbatim to `log.comments` … base64-encoded. **Base64 is an
> encoding, not encryption.** … Contains a patient surname: 6,073 rows · a `SYN-` patient identifier:
> 30 · a national-ID-class value: 30 · a patient telephone number: 30 · clinical free text: 214."*

> ⚠ *"A plaintext search over base64 data cannot match. **Any prior assurance that the log is clean,
> if it was produced by a plaintext search, is void.**"*

**Because every value involved is synthetic, this is not a PHI exposure on this instance.** But the
`log` table is the bulk of the 104.9 MB, is pure development history with no demo value, and would
carry the dev instance's entire activity record onto a public host.

**`DO NOT COPY TO PUBLIC DEMO UNTIL SANITIZED` applies to `log` specifically**, and is another reason
Option 3 is preferred — a fresh install starts the table empty.

## 20.4 Bottom line

- **The dataset is safe to expose**, subject to the audit-log decision above and to credential
  rotation (§19).
- **It is fully reproducible** — a decisive advantage: the demo can be reset to a known state by
  re-running the seeder rather than restoring a dump, once `B-03` is resolved.
- **Patient document payloads total only 106 KB across 30 files**, so document transfer is trivial
  if that route is chosen. They are gitignored (`.gitignore:100`), so they do **not** travel with a
  git deploy — the seeder recreates them.

---

# 21 — Deployment artifact boundary

### MUST DEPLOY — required at runtime, and present in git

```
apis/  bin/  ccr/  config/  controllers/  custom/  gacl/  interface/  library/
meta/  oauth2/  portal/  public/images/  public/assets/modified/  sites/
sql/  src/  swagger/  templates/
brand/                          (111 tracked files — brand masters + tokens + manifests)
composer.json  composer.lock  package.json  package-lock.json
webpack.themes.js  scripts/  webpack/  interface/themes/
version.php  setup.php  sql_upgrade.php  sql_patch.php  admin.php  ippf_upgrade.php
.htaccess files (9, tracked)
```

### BUILD ON SERVER — regenerate; never copy from the Windows host

| Artefact | Command | Why not copy |
|---|---|---|
| `vendor/` | `composer install --no-dev --optimize-autoloader` | Resolved against Windows ZTS PHP; rebuild is deterministic from the lockfile |
| `interface/modules/custom_modules/oe-module-claimrev-connect/` | (same command — placed by `oe-module-installer-plugin`) | Gitignored; only Composer creates it |
| `node_modules/` | `npm ci` | Build-time only — **do not deploy** |
| `public/assets/` | `npm ci` (postinstall hook) | Gitignored; 52 dependency trees |
| `public/themes/` | `npm run build` | Gitignored; must come from the Q77-pruned entry map (§5.5) |

### DO NOT DEPLOY

```
.git/  .github/  .claude/  .vscode/  .idea/  .phpstan/  .webpack-cache/
tests/  docs/  Documentation/  ci/  docker/  contrib/  db/  sphere/
node_modules/  tmp/  tmp-phpstan/  scratchpad/
Locked Desicions/            (governance; not runtime)
tools/                       (build/governance tooling; also `export-ignore`)
CLAUDE.md  CLAUDE.local.md  SETUP-STATUS.md  *.md at root
sites/default/documents/[0-9]*/                  (dev document payloads)
sites/default/documents/certificates/*.key       (dev OAuth2 keys)
sites/default/documents/logs_and_misc/methods/*  (dev site encryption keys)
```

⚠ `contrib/` **must be present during installation** (`contrib/util/installScripts/InstallerAuto.php`)
and denied by Apache afterwards (`httpd.conf:547`). Deploy it, deny it, or remove it post-install.

### RECREATE ON SERVER — environment-specific, never copied

| Item | How |
|---|---|
| `sites/default/sqlconf.php` | Written by the installer with a **generated** password (`EV-047` §6) |
| Site encryption keys, OAuth2 keypair | Generated at install/runtime (§8.2) |
| The `modules` row for `oe-module-thiqa-branding` | Manual insert (§13.2) |
| ~33 branding `globals` | `thiqa-branding:apply-profile` |
| Regional config, `mysql_bin_dir`, `temporary_files_dir`, facility record | `EV-047` §7; §11.2 |
| `patients\|bulk_rep` ACO | `thiqa-branding:provision-report-acl` (§12.3) |
| Apache VirtualHost, TLS certificate, systemd units, cron entry | Server configuration (§10.5, §15) |
| PHP `php.ini` overrides | §3.3 |

### MIGRATE / SANITIZE

| Item | Recommendation |
|---|---|
| Database | **Do not migrate.** Fresh install + `apply-profile` + `provision-report-acl` + `seed-demo` (§7 Option 3) |
| Demo dataset | Regenerate via `thiqa-branding:seed-demo` — after `B-03` |
| `log` table | **Never copy** (§20.3) |
| Patient document payloads (106 KB) | Regenerated by the seeder; copy only if the seeder route is abandoned |
| Demo credentials | **Regenerate**; do not reuse dev values (§19) |

---

# 22 — Compatibility with the target VM

| Component | Target | Verdict | Basis |
|---|---|---|---|
| **Ubuntu 24.04 LTS x86_64** | — | **PASS** | Upstream CI runs the full suite on `runs-on: ubuntu-24.04` (`.github/workflows/*.yml`) — the exact target OS |
| **Apache 2.4** | 2.4.x | **PASS WITH CHANGE** | Dev host is 2.4.57. Requires `rewrite`, `setenvif`, `ssl`, and either `access_compat` or 2.4-syntax deny blocks (§10.3) |
| **PHP 8.3** | 8.3.x | **PASS** | Minimum enforced is 8.2.0 (`Checker.php:19`); no ceiling; dev host already runs 8.3.33; no dependency caps below 8.3 (§3.1) |
| **PHP extensions (33)** | — | **PASS WITH CHANGE** | All 33 verified loaded on the dev host. `imagick`, `redis`, `xsl`, `sodium` need explicit packages and **`composer install` will not catch a miss** (§3.2.1) — `DG-001` |
| **MariaDB 10.11** | 10.11.x | **PASS** | Schema is `utf8mb4_general_ci` + InnoDB only; **no `uca1400` collation, no 11.x-only DDL** anywhere in `sql/` (§6.1). Downgrade from the dev 11.8.8 is safe |
| **Composer** | 2.x | **PASS** | Lockfile `plugin-api 2.9.0`; all 247 packages public; no tokens; no private repos (§4) |
| **Node.js (build only)** | — | **PASS WITH CHANGE** | `engines: node >= 24`; Ubuntu 24.04's archive Node is older — NodeSource/nvm required. Build also needs outbound HTTPS for `napa` (§5.3) — `DG-003` |
| **No Docker** | — | **PASS** | The application has no container dependency. `docker/` is `export-ignore`d and unused by this path |
| **x86_64** | — | **PASS** | No architecture-specific code; `imagick`/`redis` ship for amd64 |
| **8 GB RAM** | — | **PASS** | `memory_limit 512M` per PHP worker; the DB is 104.9 MB and a fresh install is far smaller. Ample headroom |
| **100 GB disk** | — | **PASS** | Source + `vendor/` + `node_modules/` + `public/assets/` ≈ a few GB; DB ≈ 105 MB at dev scale; documents 106 KB |
| **Static IPv4 + HTTP/HTTPS firewall** | — | **PASS WITH CHANGE** | TLS is not yet designed — `RDY-0085` is **open** (`EV-047` §0.1: *"the demo instance is HTTP only"*). Certificate + vhost must be created |
| **App + MariaDB on one VM** | — | **PASS** | Current topology is the same shape. Keep MariaDB bound to `127.0.0.1` and grant on the one schema only (`EV-047` §4) |
| **Cron / background services** | — | **PASS WITH CHANGE** | Must run as `www-data`, not root (`bin/console:25`); tick interval driven by the shortest **active** service (§15.3) |
| **Branding module activation** | — | **BLOCKED until the `modules` row exists** | §13.2 |
| **Demo data seeding** | — | **BLOCKED** | Hard-coded `C:/` precondition (§7.3) — `B-03` |
| **Report ACL** | — | **BLOCKED until provisioned** | ACO absent even on dev (§12.3) — `B-06` |
| **Public-demo governance clearance** | — | **UNKNOWN** | `DG-016` — see §24 `B-11` |

---

# 23 — Deployment knowledge gap register

## 23.0 Revision 2 status — this table supersedes the detail table below

**Nine of the seventeen gaps are closed; eight remain open, one of those only partially.** The detail
table in §23.1 is retained unchanged as the record of what each gap asked and how it was to be
resolved.

| ID | Rev 1 state | **Rev 2 state** | Evidence | Source changed? |
|---|---|---|---|---|
| **DG-001** | OPEN — blocking | **PARTIALLY CLOSED — one check outstanding** | `OPERATOR-VERIFIED TARGET EVIDENCE`: the Apache PHP SAPI extension test returned **zero missing required extensions** on `demo-openemr`. The **CLI** SAPI check is still to be recorded — the two SAPIs load different `.ini` sets and can differ, which is the whole reason this gap specified "both" | No |
| **DG-002** | OPEN — blocking | **CLOSED** | `OPERATOR-VERIFIED TARGET EVIDENCE`: `access_compat`, `headers`, `rewrite`, `setenvif`, `ssl` all loaded; `apachectl configtest` → `Syntax OK`. The Apache 2.2 syntax in `bin/.htaccess` and `sites/default/documents/.htaccess` therefore resolves to a clean 403 | No |
| **DG-003** | OPEN — blocking | **CLOSED** | `OPERATOR-VERIFIED TARGET EVIDENCE`: build-time outbound HTTPS verified to `github.com`, `jqueryui.com`, `clinicaltables.nlm.nih.gov` — the three hosts `napa` fetches from (`package.json:112-121`) | No |
| **DG-004** | OPEN — non-blocking | **OPEN — decision required** | Unchanged. `.gitattributes:20` still `export-ignore`s `tools/`. Recommendation stands: transfer by `git clone`, not `git archive`. ⚠ Revision 2 adds a second reason to prefer `git clone` — see `DG-015` | No |
| **DG-005** | OPEN — blocking | **CLOSED** | Source patch. `--baseline-path` option added via new `BaselineOption` value object; SHA-256 gate unchanged and still fail-closed. Governance record **PR-17**. 18 new tests, 32 assertions, passing | **Yes** — §26 |
| **DG-006** | OPEN — non-blocking | **NOT APPLICABLE UNDER OPTION 3** | Closed by construction. A fresh install generates its own site encryption keys, so there is no key/database pairing to transfer. Only ever applied to the rejected Option 2 | No |
| **DG-007** | OPEN — non-blocking | **RESOLVED — documented non-blocker** | `OPERATOR-VERIFIED TARGET EVIDENCE`: `PrivateTmp=yes` confirmed on the VM's Apache unit. Consequence is unchanged and benign: the web SAPI and CLI see different `/tmp`, so `CacheDirectory` caches do not overlap. This is correct isolation. **The operational rule that follows is mandatory**: never run `bin/console` as root, or it creates `0700` cache dirs `www-data` cannot read — already enforced by `bin/console:25` | No |
| **DG-008** | OPEN — non-blocking | **OPEN — non-blocking, default accepted** | Unchanged. No evidence found in the repository requiring `LimitRequestFieldSize` above the 8190 default. Leave at default; revisit only if a large form returns 400 | No |
| **DG-009** | OPEN — non-blocking | **OPEN — decision required** | Unchanged. `ccdaservice` deployment depends on whether C-CDA export is in the demo script (`EV-040`). Expect out of scope | No |
| **DG-010** | OPEN — non-blocking | **OPEN — provisioning action** | Unchanged. The `users` (Accounting) ACL group has 0 members; a billing-role walkthrough needs an account created at provisioning | No |
| **DG-011** | OPEN — blocking | **CLOSED — PROVISIONING CONTROL** | Full execution-path analysis in §14.1. `email_queue` is empty (0 rows); an active service with an empty queue makes zero send attempts. Four provisioning controls specified, no source change needed | No |
| **DG-012** | OPEN — blocking | **CLOSED — VERIFIED DISABLED-BY-DEFAULT** | Full analysis in §14.2. Registration's network call was **removed entirely** (PR-06, re-verified: zero `curl`/URL constructs in the current file). Usage telemetry is gated **three independently sufficient ways**, and no `Telemetry_Task` service row exists | No |
| **DG-013** | OPEN — non-blocking | **OPEN — action required** | Unchanged and now imminent. `EV-047` P-1 still specifies **Windows Server**; this deployment is its first execution and its Ubuntu addendum is owed | No |
| **DG-014** | OPEN — non-blocking | **CLOSED** | Proven here. **9,689 tracked paths, zero case-fold collisions**; supplementary check over **1,785 distinct tracked directories**, zero directory-component collisions | No |
| **DG-015** | OPEN — blocking | **CLOSED** | Proven here. `git ls-files --eol bin/console` → **`i/lf`** — the index is LF, so a Linux clone gets LF. Repo-wide: **zero shebang scripts carry CRLF in the index**. The 22 files that are CRLF-in-index are deliberately `-text`-marked to protect SHA-256 manifests, and none is executable. **No `.gitattributes` change made — one would have broken the manifests** | No |
| **DG-016** | OPEN — blocking (public demo) | **OPEN — OWNER/GOVERNANCE DECISION REQUIRED** | Unchanged, and **explicitly not closed by engineering work.** D-3 (legal clearance of the product name), D-4 (native-Arabic proofreading), D-11 (counsel review of the acknowledgements page) remain open and blocking-for-release. Technical deployment may proceed **privately/restricted** ahead of this gate | No |
| **DG-017** | OPEN — blocking | **OPEN — awaiting operator action, now well-defined** | The artefact must be the **post-remediation** commit, not `4d09baef1`. Exact scope, message, tag and commands in §27. **Deliberately not executed** — no commit, no push, no tag | No |

### ⚠ One new discrepancy surfaced by the operator's VM evidence

| Item | Documented requirement | `OPERATOR-VERIFIED TARGET EVIDENCE` | Assessment |
|---|---|---|---|
| `post_max_size` / `upload_max_filesize` | **`100M`** — `CLAUDE.local.md` §5 and `EV-047` §3 step 3 both specify it, and the development host runs `100M` | **`30M`** on `demo-openemr` | **Not a blocker for a demo**, but a genuine deviation from the documented requirement. 30M accommodates every document in the synthetic dataset (the entire store is 106 KB), so nothing in the demo script can hit it. It *would* bite a real clinic uploading a large imaging PDF or a scanned batch. **Recorded as new P3 item `B-35`**; raise to `100M` before any pilot instance |

`memory_limit = 512M`, `max_input_vars = 3000` and timezone `Asia/Riyadh` are all confirmed correct
and match the requirement table in §3.3 — the timezone is in fact *better* than the development
host's `UTC`.

## 23.1 Original gap detail (Revision 1 — retained unchanged)

| ID | Question | Why it matters | Blocking? | Evidence available | Exact resolution step | Expected decision |
|---|---|---|---|---|---|---|
| **DG-001** | Are all 33 required PHP extensions (esp. `imagick`, `redis`, `xsl`, `sodium`) installable and loaded on the VM under **both** CLI and the web SAPI? | `composer.json:210-245` declares them satisfied, so `composer install` **cannot** detect a miss (§3.2.1). Failure surfaces as a runtime 500 | **BLOCKING** | All 33 loaded on the dev host (`php -m`) | On the VM: `php -m > cli.txt`, and a `<?php print_r(get_loaded_extensions());` page served through Apache; diff both against `composer.json:15-47` | Install any missing package; do **not** accept a green `composer install` as proof |
| **DG-002** | Is `access_compat` enabled on the VM's Apache? | `bin/.htaccess` and `documents/.htaccess` use 2.2 syntax → HTTP 500 (not 403) without it (§10.3) | **BLOCKING** | Dev host does **not** load it; those paths 500 there | `apache2ctl -M \| grep access_compat` | If absent, add 2.4-syntax deny blocks to the vhost (preferred — keeps Invariant 4 intact) rather than editing tracked `.htaccess` |
| **DG-003** | Can the VM reach `github.com`, `jqueryui.com` and `clinicaltables.nlm.nih.gov` at build time? | `napa` fetches 9 non-registry archives with caching disabled; without them `public/assets/` is incomplete (§5.3) | **BLOCKING** (for on-VM builds) | `package.json:112-124` | `for u in https://jqueryui.com https://github.com https://clinicaltables.nlm.nih.gov; do curl -sI -o /dev/null -w "%{http_code} $u\n" $u; done` | If blocked, build on a machine with access and transfer `public/assets` + `public/themes` as artefacts |
| **DG-004** | Will the source arrive by `git clone`/`rsync`, or by `git archive`/release tarball? | `.gitattributes:20` `export-ignore`s `tools/`, `tests/`, `ci/`, `.github/` — a tarball silently loses the branding tooling (§4.3) | Non-blocking | `.gitattributes:6-25` | `git archive HEAD \| tar -t \| grep '^tools/'` → expect **empty** | Use `git clone` or `rsync` of the working tree |
| **DG-005** | How is `SeedDemoCommand`'s `BASELINE_PATH` precondition satisfied on Ubuntu? | Hard-coded `C:/openemr-stack/...`; `is_file()` fails → seeder refuses (§7.3) | **BLOCKING** (for seeded demo data) | `SeedDemoCommand.php:101-103,335-337` | Decide among: (a) place a baseline dump at that literal path on the VM; (b) patch the constant to a configurable path — **requires a numbered patch record per Q1/Invariant 4**; (c) skip the seeder and transfer the seeded data another way | Recommend (b) with a patch record — a hard-coded developer path in a shipped CLI is a defect in its own right |
| **DG-006** | If the DB were cloned, are the matching site encryption keys transferable? | Keys under `logs_and_misc/methods/` decrypt DB content; they are gitignored and must never be copied casually (§8.2) | Non-blocking **under Option 3** | `.gitignore:77-88`; `KeyVersion` | Moot if Option 3 is adopted. If Option 2 is chosen: `ls sites/default/documents/logs_and_misc/methods/` on both hosts and confirm the version pair matches | Adopt Option 3 and close this gap by construction |
| **DG-007** | Does Ubuntu's `apache2.service` `PrivateTmp=true` cause a cache/session problem? | `CacheDirectory` uses `sys_get_temp_dir()` with `0700` dirs; web and CLI would see different `/tmp` (§9.2) | Non-blocking | `CacheDirectory.php:57-58,74` | `systemctl show apache2 -p PrivateTmp`; then confirm a page render and a `bin/console` run both succeed | Accept the split (it is correct isolation) but **document it**, and never run `bin/console` as root |
| **DG-008** | Does any workflow need `LimitRequestFieldSize` above the 8190 default? | Large OpenEMR forms are POST bodies (governed by `post_max_size`), not headers — but this was not proven exhaustively | Non-blocking | Repo-wide grep found **no** such requirement | Leave at default; if a 400 appears on a large form, raise to 16380 and retest | Expect the default to suffice |
| **DG-009** | Is `ccdaservice` needed for the demo? | `Carecoordination` is active; `ccda_alt_service_enable = 0`; C-CDA export will not work without the Node service (§5.6) | Non-blocking | `modules` table; `globals` | Decide whether C-CDA export is in the demo script (`EV-040-d7-demo-script.md`); if yes, deploy `ccdaservice` with its own `npm ci` | Expect **out of scope** — leave undeployed |
| **DG-010** | Which account demonstrates the Accounting/Billing role? | The `users` (Accounting) ACL group has **0 members** (§12.2), so a billing walkthrough has no actor | Non-blocking | `gacl_groups_aro_map` query | Decide whether to add an Accounting demo user, or demonstrate billing via `back` (Back Office) | Expect: add one account at provisioning |
| **DG-011** | Can `Email_Service` deliver mail from the VM? | It is **active at a 2-minute interval** with `SMTP_HOST=localhost`, and `rx_send_email = 1`. `EMAIL_NOTIFICATION_HOUR = 50` looks out of range but this was **not proven** (§14, §15.3) | **BLOCKING for a public demo** | `background_services`, `globals` | Read `src/…/EmailService` (or `library/` equivalent) for the send path; confirm no MTA listens on `localhost:25`; consider deactivating the row | Expect: deactivate `Email_Service`, which also relaxes the cron tick from 2 min to 240 min |
| **DG-012** | Do `TelemetryService` and `ProductRegistrationService` make outbound calls, and did the fork change that? | Both are **modified by this fork** (§1.3) and are OpenEMR's two outbound-reporting services; a public demo must not phone home unexpectedly | **BLOCKING for a public demo** | `git diff <merge-base>..HEAD -- src/Telemetry/ src/Services/ProductRegistrationService.php` | Read both diffs and both classes; check for any enabling `globals` row | Expect: confirm disabled-by-default, and record it |
| **DG-013** | Does `EV-047` need an Ubuntu variant? | Its P-1 specifies **Windows Server**; steps 1 and 9 are Windows-specific while 2–8 are platform-neutral (§17.1 C-4) | Non-blocking | `EV-047` §2, §3, §9 | Log the questions this deployment raises in `EV-047` §12, as that document requires | Expect: issue an Ubuntu addendum; **this deployment is `RDY-0047`'s first execution** |
| **DG-014** | Are there filename case collisions that break on a case-sensitive filesystem? | Windows is case-insensitive; Linux is not. Upstream CI on `ubuntu-24.04` covers upstream files, but fork-added files were not case-audited (§18 W-10) | Non-blocking | Upstream CI is `ubuntu-24.04` | `git ls-files \| tr 'A-Z' 'a-z' \| sort \| uniq -d` → expect empty | Expect clean |
| **DG-015** | Does `bin/console` (and any other shebang script) carry CRLF? | A CRLF shebang fails on Linux with `bad interpreter` (§18 W-11) | **BLOCKING** if present | `bin/console:1` is `#!/usr/bin/env php`; no global `text=auto` in `.gitattributes` | `file bin/console` (expect "ASCII text", **not** "with CRLF line terminators"); or `git ls-files --eol bin/console` | If CRLF: normalise on the server, or invoke as `php bin/console` (which is immune) — the runbook already uses that form |
| **DG-016** | Is there governance clearance to expose this brand publicly? | `EV-047` §0.1 and the branding D-register list **D-3 (legal clearance of the product name)**, **D-4 (native-Arabic proofreading)** and **D-11 (counsel review of the acknowledgements page)** as open and blocking-for-release; `RDY-0004` is a prohibited-claim control | **BLOCKING for a *public* demo** (not for deployment itself) | `docs/branding/remaining-dependencies.md` §4; `docs/evidence/EV-004-prohibited-claims-control.md` | Confirm current D-register status with the owner before the instance is reachable from the internet | Expect: deploy behind an IP allowlist until cleared |
| **DG-017** | Which of the 71 unpushed commits define the deployment artefact? | HEAD is 71 commits ahead of `origin/feat/thiqa-branding-foundation`; a deployment from the remote branch would be **71 commits stale** (§1.1) | **BLOCKING** | `git rev-list --count origin/feat/…..HEAD` → 71 | Push the branch, or tag HEAD (`4d09baef1`) and transfer that tag | Expect: push, then deploy a tag — never an ad-hoc working-tree copy |

---

# 24 — Deployment blockers

## 24.0 Revision 2 status — this table supersedes the detail below

**P0: 8 → 4. P1: 10 → 7.** The lists in §24.1 onward are retained as the record of what each
blocker was. Every remaining P0 is an action performed **on the VM**; none is a source defect.

| ID | Rev 1 | **Rev 2** | Why |
|---|---|---|---|
| **B-01** dependency trees not in git | P0 | **P0 — OPEN (by design)** | Unchanged and **not a defect**. `vendor/`, `public/assets/`, `public/themes/` are gitignored; the artefact is a build product. Closes when `composer install` + `npm ci && npm run build` run on the VM |
| **B-02** Node ≥ 24 | P0 | **P0 — OPEN, operator-scheduled** | `OPERATOR-VERIFIED`: Node/npm not yet installed; Node 24 to be installed after this source artefact is certified. Deliberate sequencing, not a gap |
| **B-03** seeder hard-coded `C:/` path | P0 | **✅ CLOSED IN SOURCE** | `--baseline-path` added; SHA-256 gate unchanged and still fail-closed; **PR-17**; 18 tests passing. Residual is a provisioning input (a baseline artefact matching `BASELINE_SHA256` must exist on the target), not a source defect |
| **B-04** missing extension undetectable by Composer | P0 | **P1 — LARGELY MITIGATED** | `OPERATOR-VERIFIED`: the Apache SAPI test reports **zero missing required extensions**. Downgraded from P0 because the web SAPI — the one that serves every page — is proven. Stays open at P1 only until the **CLI** `php -m` check is recorded (`DG-001`), since `bin/console` provisioning steps run under it |
| **B-05** DB strategy undecided | P0 | **✅ CLOSED BY DECISION** | **Option 3 — fresh database + controlled configuration + synthetic demo seeding — is locked** by operator decision. Cloning the development database is no longer a candidate path |
| **B-06** `bulk_rep` ACO absent | P0 | **P0 — OPEN, provisioning step** | Unchanged. Still absent on the development instance; `thiqa-branding:provision-report-acl` is mandatory on the target |
| **B-07** `modules` row required | P0 | **P0 — OPEN, provisioning step** | Unchanged. Files alone yield an unbranded app with no error |
| **B-08** artefact unidentified | P0 | **P1 — DEFINED, awaiting operator** | No longer unidentified: exact commit scope, message, tag name and commands are specified in §27. Downgraded because the remaining action is a reviewed operator command, not an open question. **Deliberately not executed** |
| **B-09** default DB password | P1 | **P1 — OPEN** | Unchanged. Closed by Option 3 step 18 (generated password at install) |
| **B-10** no TLS | P1 | **P1 — OPEN** | `OPERATOR-VERIFIED`: `mod_ssl` loaded and HTTP/HTTPS firewall configured. Certificate and vhost still to be issued |
| **B-11** governance clearance | P1 | **P1 — OPEN — OWNER/GOVERNANCE DECISION REQUIRED** | Explicitly **not** closed by engineering work. See `DG-016` |
| **B-12** `Email_Service` active | P1 | **✅ CLOSED — PROVISIONING CONTROL** | §14.1. Queue empty (0 rows); four provisioning controls specified; no source change needed |
| **B-13** telemetry unverified | P1 | **✅ CLOSED — VERIFIED DISABLED-BY-DEFAULT** | §14.2. Registration call removed outright; telemetry gated three independently sufficient ways; no `Telemetry_Task` row |
| **B-14** Windows-path globals | P1 | **✅ NOT APPLICABLE UNDER OPTION 3** | These are values in the *development* database. A fresh install never receives them. Retained as a §I provisioning checklist item |
| **B-15** CRLF risk on `bin/console` | P1 | **✅ CLOSED** | `git ls-files --eol bin/console` → **`i/lf`**. Zero shebang scripts carry CRLF in the index. See ⚠ below |
| **B-16** live NewCrop endpoints | P1 | **P1 — OPEN** | Unchanged. Keep `erx_enable = 0`; verify at every reset |
| **B-17** audit-log content | P1 | **✅ NOT APPLICABLE UNDER OPTION 3** | The `log` table starts empty on a fresh install. Retained as a "never migrate this table" rule |
| **B-18** regenerate demo credentials | P1 | **P1 — OPEN** | Unchanged, provisioning step |
| **B-19** Apache 2.2 `.htaccess` syntax | P2 | **✅ CLOSED** | `OPERATOR-VERIFIED`: `access_compat` loaded (`DG-002`) |
| **B-20** cron must not run as root | P2 | **P2 — OPEN** | Unchanged; enforced by `bin/console:25` and now reinforced by `PrivateTmp=yes` (`DG-007`) |
| **B-21** `PrivateTmp` cache split | P2 | **✅ RESOLVED — documented** | `OPERATOR-VERIFIED`: `PrivateTmp=yes`. Correct isolation; see `DG-007` |
| **B-22** `git archive` drops `tools/` | P2 | **P2 — OPEN** | Unchanged. Use `git clone`. ⚠ Revision 2 adds a second reason — see below |
| **B-23** `napa` non-registry downloads | P2 | **✅ CLOSED (reachability)** | `OPERATOR-VERIFIED`: all three hosts reachable (`DG-003`). The unpinned/uncached nature of those nine archives remains a standing supply-chain observation, not a deployment blocker |
| **B-24** Accounting group has 0 members | P2 | **P2 — OPEN** | Unchanged (`DG-010`) |
| **B-25** stale `phimail.running = -1` | P2 | **✅ NOT APPLICABLE UNDER OPTION 3** | Fresh install creates the table clean |
| **B-26** 418 commits behind upstream | P2 | **P2 — OPEN** | Unchanged; a patch pipeline is owed before this becomes a customer instance |
| **B-27** deny `contrib/` post-install | P2 | **P2 — OPEN** | Unchanged |
| **B-28** `EV-047` has no Ubuntu variant | P2 | **P2 — OPEN** (`DG-013`) | Unchanged and imminent — this deployment is its first execution |
| **B-29**…**B-34** | P3 | **P3 — OPEN** | Unchanged optimisations |
| **B-35** *(new in Rev 2)* | — | **P3 — OPEN** | `OPERATOR-VERIFIED`: the VM runs `post_max_size` / `upload_max_filesize` at **`30M`**, against the `100M` required by `CLAUDE.local.md` §5 and `EV-047` §3. Harmless for this demo (the entire synthetic document store is 106 KB) but a real constraint for a pilot. Raise to `100M` before any clinic instance |

### ⚠ Revision 2 adds a second, independent reason to transfer by `git clone`

`B-15` is closed **because the git index is LF**. That guarantee is a property of *git*, not of this
working tree: on this Windows host `core.autocrlf=true` means the working-tree copy of `bin/console`
is **CRLF** (`w/crlf`).

**Therefore `rsync`/`scp` directly from this Windows working tree would carry CRLF into every shebang
script**, reintroducing exactly the `bad interpreter` failure `B-15` was raised about. `git clone` on
the VM produces LF and is immune.

Revision 1 §21 offered "`git clone` (or `rsync` the working tree)". **Revision 2 withdraws the
`rsync`-from-Windows option**: use `git clone`, or `rsync` only from a checkout made on Linux.

## 24.1 Original blocker detail (Revision 1 — retained unchanged)

## P0 — cannot deploy

| ID | Blocker | Evidence | Resolution |
|---|---|---|---|
| **B-01** | **The runtime dependency trees are not in git.** `vendor/`, `public/assets/`, `public/themes/` are all gitignored. A `git clone` produces a tree that cannot render a page | `.gitignore:9,16,17`; `git ls-files` → 0 files for each | Run `composer install --no-dev --optimize-autoloader` **and** `npm ci && npm run build` on the server (or ship the three directories as build artefacts) |
| **B-02** | **Node ≥ 24 is required and Ubuntu 24.04's archive Node is older.** Without it `public/assets/` and `public/themes/` cannot be produced | `package.json:28-30`; CI `node-version: ['24']` | Install Node 24 via NodeSource/nvm, **or** build off-VM and transfer the artefacts |
| **B-03** | **The demo seeder cannot run on Ubuntu.** `SeedDemoCommand` fail-closes on a hard-coded `C:/openemr-stack/...` baseline path | `SeedDemoCommand.php:101-103, 335-337` | Resolve `DG-005` — recommended: make the constant configurable **with a numbered patch record** per Q1/Invariant 4 |
| **B-04** | **A missing PHP extension will not be detected at install time.** `config.platform` asserts all 33 present, so `composer install` exits 0 regardless | `composer.json:210-245`; `composer.lock` `platform-overrides` | Verify with `php -m` under **both** SAPIs (`DG-001`); never accept `composer install` as the check |
| **B-05** | **Deployment strategy is undecided, and the default (clone the DB) imports four known-wrong states** — version row `8.3.0-dev`, four Windows-path globals, `saas_branding_revision = 1` with gitignored stylesheets, and 70,372 dev audit rows | §1.2, §11.2, §16.3, §20.3 | **Adopt Option 3** (§7.4) |
| **B-06** | **The `patients\|bulk_rep` ACO does not exist** — not on the target, and **not even on the dev instance.** Two reports fail closed for every role including Administrators | `SELECT … gacl_aco WHERE value LIKE '%rep%'` → 0 rows; `EV-047` §8; `ProvisionReportAclCommand.php:27,57,66` | Run `php bin/console thiqa-branding:provision-report-acl` (idempotent) |
| **B-07** | **The branding module will not load without its `modules` row.** Files alone produce an unbranded app with no error | `ModulesApplication.php:141,145-146,188` | Insert the row at provisioning; verify the login page title afterwards |
| **B-08** | **The deployment artefact is not identified.** HEAD is 71 commits ahead of the remote branch; no tag marks a deployable point | `git rev-list --count origin/feat/…..HEAD` → 71 | Push the branch and tag HEAD (`4d09baef1`); deploy the tag (`DG-017`) |

## P1 — must resolve before a public demo

| ID | Blocker | Evidence | Resolution |
|---|---|---|---|
| **B-09** | **The database password is the unchanged upstream default.** Loopback binding and "no real data" are what limit it today — neither survives a public VM | `EV-048` §1.2, §1.3, R-1/R-4 | Generate a unique ≥20-char CSPRNG password at install (`EV-047` §6); store outside the repository |
| **B-10** | **TLS is not designed.** `RDY-0085` open; the demo instance is HTTP-only today | `EV-047` §0.1; `httpd.conf:60` (`Listen 8300`, no SSL module) | Provision a certificate for `demo.<DOMAIN>`; enable `mod_ssl`; redirect :80 → :443 |
| **B-11** | **Public exposure lacks governance clearance.** D-3 (legal clearance of the product name), D-4 (Arabic proofreading), D-11 (counsel review of acknowledgements) are open and blocking-for-release; `RDY-0004` governs prohibited claims | `docs/branding/remaining-dependencies.md` §4; `EV-004` | Confirm with the owner; until cleared, restrict access by IP allowlist |
| **B-12** | **`Email_Service` is active at a 2-minute interval** with `rx_send_email = 1`; a demo must not send mail | `background_services`; `globals` (`DG-011`) | Deactivate the service row and/or ensure no MTA listens on `localhost:25`; re-verify |
| **B-13** | **Telemetry / product-registration behaviour is unverified**, and both services were modified by this fork | `git diff` file list, §1.3 (`DG-012`) | Read both diffs; confirm no outbound call by default; record the finding |
| **B-14** | **Four `globals` carry Windows paths** — `mysql_bin_dir`, `perl_bin_dir`, `temporary_files_dir`, `backup_log_dir`. `mysql_bin_dir` silently breaks backup | `globals` query; `docs/HISModulesUsers.md:3333` (OD-01/RDY-0080) | Set at provisioning (`EV-047` §7). Not applicable under Option 3 |
| **B-15** | **Windows-only line endings could break `bin/console`** | §18 W-11 (`DG-015`) | `file bin/console`; invoke as `php bin/console` regardless |
| **B-16** | **Real NewCrop production endpoints are configured** while only `erx_enable = 0` prevents contact | `globals.erx_newcrop_path`, `_soap` | Keep `erx_enable = 0`; verify on every reset |
| **B-17** | **`log` carries 70,372 rows of base64-encoded SQL with interpolated (synthetic) patient values** | `EV-055` §0-§1; `COUNT(*)` | Do not migrate the table. Fresh install starts empty |
| **B-18** | **Demo credentials must be regenerated**, not reused from the development secret store | `EV-048` R-1; `docs/PHASE-2B-CONTINUATION-PROMPT.md:30` | Generate per-instance; store outside the repository |

## P2 — should resolve

| ID | Item | Evidence |
|---|---|---|
| **B-19** | Apache 2.2 syntax in `bin/.htaccess` and `documents/.htaccess` → HTTP 500 rather than 403 without `access_compat` (`DG-002`) | §10.3 |
| **B-20** | Cron must run as `www-data`; `bin/console` refuses root, and a root-created cache dir would be `0700`-unreadable by Apache | `bin/console:25`; §9.2 |
| **B-21** | `PrivateTmp` splits the cache between web and CLI (`DG-007`) | §9.2 |
| **B-22** | `git archive`/tarball transfer would drop `tools/` (`DG-004`) | `.gitattributes:20` |
| **B-23** | `napa`'s 9 non-registry downloads are uncached and unpinned — a fragile build dependency (`DG-003`) | `package.json:112-124` |
| **B-24** | The Accounting ACL group has **0 members**, so a billing role demo has no actor (`DG-010`) | §12.2 |
| **B-25** | `background_services.phimail` carries a stale `running = -1` marker | §15.3 |
| **B-26** | The branch is **418 commits behind** `upstream/master` — a security-patch pipeline must exist before this becomes a customer instance | §1.1 |
| **B-27** | Ensure `contrib/` is denied (or removed) after installation | `httpd.conf:547`; §21 |
| **B-28** | `EV-047` has no Ubuntu variant; **this deployment is its first execution and its acceptance test** (`DG-013`) | `EV-047` §0, §12, §13 |

## P3 — optimisation

| ID | Item |
|---|---|
| **B-29** | Enable `mod_headers` for HSTS and security headers; add `ServerTokens Prod` / `ServerSignature Off` |
| **B-30** | Tune OPcache for the VM (`revalidate_freq` 2–60); the dev host's `realpath_cache_size = 8M` was a Drive-latency mitigation and is unnecessary |
| **B-31** | Set an explicit `session.save_path` with a dedicated cleanup policy rather than relying on the PHP default |
| **B-32** | Plan the `log` table's growth policy — it reached 70k rows on a 30-patient dev instance |
| **B-33** | Enable `mod_deflate` / `mod_expires` for the static asset trees |
| **B-34** | Establish an off-instance backup target (`RDY-0081`) and prove a restore (`RDY-0082`) — both open |

---

# 25 — Final deployment readiness certification

## A. Application Identity

**OpenEMR 8.2.0**, on branch `feat/thiqa-branding-foundation` at commit
**`4d09baef135a3cf90dfd2e48f8163e3eb6d6b16e`**.

A **fork of the upstream `rel-820` line**, 92 commits ahead of and 418 commits behind
`upstream/master` (merge-base `b91c12aee`), no longer an ancestor of it. **71 of those commits exist
only on this machine** — `origin/feat/thiqa-branding-foundation` is at `203f24de5`.

Customisation state: **code-heavy, schema-free.** One custom module
(`oe-module-thiqa-branding`, 97 files), a complete Thiqa brand layer (theme SCSS, Q77-pruned webpack
entry map, tracked logos, 111 brand assets under a 123-entry SHA-256 manifest, all verified present
and tracked), 39 modified files in the dormant `oe-module-faxsms`, 11 remediated report scripts, and
a small set of deliberate core patches. **Zero database schema change** — no tables, columns,
indexes, triggers, routines or views (`git diff … -- sql/ src/Migrations/` is empty; corroborated
against the live 283-table schema). Working tree clean apart from one untracked `.docx` diagram.

⚠ The live development database records `8.3.0-dev`, not `8.2.0` — see §1.2.

## B. Current Development Runtime

Windows Server 2025 on a GCE VM with no nested virtualization (hence no Docker, ever). Apache
**2.4.57** Win64 VS16 with in-process **`mod_php`**, PHP **8.3.33 ZTS VS16**, MariaDB **11.8.8**,
Composer **2.10.2**, Node **24.18.1** / npm **11.16.0**. DocumentRoot is the repository root on the
Google Drive mount; the app is served at `/` on plain HTTP port 8300; there is no VirtualHost and no
TLS. Apache and MariaDB run as **session console processes** because Google Drive mounts `G:` per
user session. **Verified live during this pass:** `HTTP 200`, 9,165 bytes,
`<title>Thiqa Login</title>`.

## C. Target Runtime Compatibility

**Ubuntu 24.04 — PASS.** Upstream CI runs the full suite on `runs-on: ubuntu-24.04`, the exact target.
**PHP 8.3 — PASS.** Minimum enforced is 8.2.0; no ceiling anywhere; the dev host already runs 8.3.
**MariaDB 10.11 — PASS.** The 11.8 → 10.11 downgrade was specifically checked: the schema uses only
`utf8mb4_general_ci`/`utf8mb4_bin` and InnoDB, and `sql/` contains **no** `uca1400`, `INVISIBLE`,
`GENERATED ALWAYS` or `utf8mb4_0900_*` DDL.
**Apache 2.4 — PASS WITH CHANGE** (`rewrite`, `setenvif`, `ssl`, plus `access_compat` or 2.4-syntax
deny blocks).
**PHP extensions — PASS WITH CHANGE** (all 33 verified on dev; four need explicit packages and
Composer will not catch a miss).
**Node build — PASS WITH CHANGE** (Node 24 + outbound HTTPS).
**No Docker — PASS.** Nothing in the runtime path depends on a container.
**Hardware — PASS** with wide margin.

## D. Required Deployment Files

**MUST DEPLOY** (in git): `apis bin ccr config controllers custom gacl interface library meta oauth2
portal public/images public/assets/modified sites sql src swagger templates brand interface/themes
scripts webpack`, the root PHP entry points, `composer.json/.lock`, `package.json/-lock.json`,
`webpack.themes.js`, and the nine tracked `.htaccess` files.

**BUILD ON SERVER:** `vendor/`, `oe-module-claimrev-connect/`, `public/assets/`, `public/themes/`.

**DO NOT DEPLOY:** `.git .github .claude .vscode .phpstan .webpack-cache tests docs Documentation ci
docker contrib(post-install) db sphere node_modules tmp tmp-phpstan tools "Locked Desicions"`, all
root `*.md`, and every gitignored runtime secret/payload under `sites/default/documents/`.

**RECREATE ON SERVER:** `sqlconf.php`, site encryption keys, OAuth2 keypair, the `modules` row, ~33
branding `globals`, regional config, the `bulk_rep` ACO, the vhost, TLS, systemd units and cron.

## E. Dependency Build

```bash
composer install --no-dev --optimize-autoloader --no-interaction   # 186 packages, all public
npm ci                                                             # + postinstall: napa, install-assets
npm run build                                                      # webpack prod + sync-css
```

No credentials, no private repositories, no non-zip dists, no `post-install-cmd`. **Never run
`composer update`.** Node ≥ 24 and outbound HTTPS to `github.com`, `jqueryui.com` and
`clinicaltables.nlm.nih.gov` are required. **Verify extensions independently with `php -m` under both
SAPIs** — `composer install` cannot detect a missing one.

## F. Database Strategy

**OPTION 3 — fresh database plus controlled configuration and data migration.**

Justified by three findings: the fork adds **no schema** (so a stock schema is complete); the demo
dataset is produced by a **committed deterministic seeder** (so it is reproducible, not precious);
and a clone would import four independently-verified wrong states — the `8.3.0-dev` version row, four
Windows-path globals, `saas_branding_revision = 1` pointing at gitignored stylesheets, and 70,372
rows of development audit history.

Sequence: fresh install → insert the `modules` row → `thiqa-branding:apply-profile` →
per-instance configuration → `thiqa-branding:provision-report-acl` → create users/roles →
`thiqa-branding:seed-demo` (after `B-03`) → optional `thiqa-branding:materialise` →
`thiqa-branding:verify`.

## G. Filesystem and Permissions

Owner `www-data:www-data`. Writable: the whole `sites/` tree (dirs `0700`, files `0600`), with
`sites/default/documents/**` — including `temp`, `smarty`, `certificates`, `logs_and_misc/methods`,
`onsite_portal_documents`, `procedure_results`, `edi`, `era` — plus `sites/default/edi|era|images`,
the branding module's `public/branding/` (if Tier-2 materialisation is used), the PHP session path,
`sys_get_temp_dir()`, `temporary_files_dir`, `backup_log_dir` and the PHP `error_log` target.
`sqlconf.php` → `0400` after install; `sites/default` → `0700`. Application code (`src`, `library`,
`interface`, `templates`, `bin`) stays read-only. Build outputs are writable at build time only.

## H. Apache Configuration Requirements

DocumentRoot = application root, URL `/` (matches the tested topology; the `/openemr` subdirectory
form also works — `interface/globals.php:188-202`). Modules: `php`(or `proxy_fcgi`), `rewrite`,
`setenvif`, `authz_core`, `dir`, `mime`, `ssl`; plus `access_compat` **or** equivalent 2.4-syntax
deny blocks; `headers` recommended. `AllowOverride All` (or fold the five rewrite blocks into the
vhost). Deny: `sites/*/documents`, `contrib`, `tests`, `bin`, `.ht*`, and
`acknowledge_license_cert.html` (a branding governance requirement). `LimitRequestFieldSize` stays at
its default — no evidence was found requiring a raise. TLS vhost on 443 with an 80→443 redirect.

## I. Environment-Specific Configuration

Everything environment-specific is a **database value or server configuration** — not code. Set at
provisioning: a unique DB password; `gbl_time_zone = Asia/Riyadh`; `gbl_currency_symbol = SAR`;
`phone_country_code = 966`; `units_of_measurement = 2`; `mysql_bin_dir = /usr/bin`;
`perl_bin_dir = /usr/bin`; `temporary_files_dir = /tmp`; a real `backup_log_dir`; the facility record
(name, street, city, postal code, phone — `EV-047` §7 warns these are blank behind a placeholder);
`site_addr_oath` **only if** OAuth2/FHIR is enabled; `portal_onsite_two_address` **only if** the
portal is enabled. `sites/default/config.php`'s Unix commands (`lpr`, `enscript`, `/usr/bin/file`)
become **more** correct on Ubuntu than they were on Windows. **No base-URL code change is needed** —
scheme, host and roots are all derived at runtime.

## J. External Integrations

The instance is already close to demo-safe: **every** REST/FHIR/portal/system API is `0`, the portal
is `0`, eRx/phiMail/HylaFAX/CouchDB/X12-SFTP are off, the fax/SMS module is not registered, and
every credential field is empty. Keep all of that. Three items need deliberate action:
**deactivate `Email_Service`** (active at 2 minutes, `rx_send_email = 1` — `DG-011`); **verify
telemetry and product registration** (both modified by this fork — `DG-012`); and **keep
`erx_enable = 0`**, because real NewCrop production endpoints are configured and only that flag
stands between the demo and a live vendor. `ccdaservice` should stay undeployed unless C-CDA export
is in the demo script.

## K. Demo Data Safety

**Synthetic, with high confidence, on four independent lines of evidence:** a committed deterministic
seeder that declares every value synthetic and whose declared targets match the live counts exactly;
identifiers that are 10-digit and distinct but match **neither** US-SSN format **nor** any valid
Saudi national-ID leading digit; every patient email and phone empty; and surnames drawn from the
seeder's 15-entry fixed table. `EV-055` independently states its measurements were taken against the
seeded system, *"never a real-patient system."*

**One qualification: `DO NOT COPY TO PUBLIC DEMO UNTIL SANITIZED` applies to the `log` table.** Its
70,372 rows hold base64-encoded SQL with interpolated patient values — synthetic here, but pure
development history with no demo value. Option 3 starts it empty. Demo credentials must be
regenerated, not reused.

## L. Deployment Blockers

**Revision 2. P0: 8 → 4. P1: 10 → 7.** Full reasoning in §24.0.

**P0 — 4. All four are actions performed *on the VM*; none is a source defect:**
B-01 (dependency trees must be built on the server — by design, not a defect) ·
**B-02** (Node 24 — *already scheduled*: the operator is installing it after this artefact is
certified) ·
B-06 (`bulk_rep` ACO must be provisioned) ·
B-07 (`modules` row must be inserted).

B-02 is counted as P0 rather than discounted, because deployment cannot complete without it — that
it is scheduled makes it planned, not closed.

**P1 — 7:** B-04 (CLI-SAPI extension check outstanding) · B-08 (artefact defined, awaiting the
reviewed operator commands in §27) · B-09 (default DB password) · B-10 (TLS certificate/vhost) ·
B-11 (**governance clearance — owner decision, not engineering**) · B-16 (keep `erx_enable = 0`) ·
B-18 (regenerate demo credentials).

**Closed in Revision 2 — 9:** B-03 ✅ *(source patch, PR-17)* · B-05 ✅ *(Option 3 locked)* ·
B-12 ✅ *(provisioning control)* · B-13 ✅ *(verified disabled by default)* · B-15 ✅ *(index is LF)* ·
B-19 ✅ · B-21 ✅ · B-23 ✅ · plus B-14, B-17, B-25 **not applicable under Option 3**.

**P2 — 6:** B-20, B-22, B-24, B-26, B-27, B-28.  **P3 — 7:** B-29 … B-35.

## M. Knowledge Gap Register

**Revision 2: 9 of 17 closed, 8 open** (one of the eight only partially).

**Closed — 9:** DG-002 ✅ · DG-003 ✅ · DG-005 ✅ *(source patch, PR-17)* · DG-006 ✅ *(N/A under
Option 3)* · DG-007 ✅ *(resolved/documented)* · DG-011 ✅ *(provisioning control)* · DG-012 ✅
*(verified disabled by default)* · DG-014 ✅ · DG-015 ✅.

**Open — 8:** DG-001 *(**partial** — web SAPI proven by the operator; CLI `php -m` outstanding)* ·
DG-004 *(transfer method — use `git clone`)* · DG-008 *(default accepted)* · DG-009 *(`ccdaservice`
scope)* · DG-010 *(Accounting demo account)* · DG-013 *(`EV-047` Ubuntu addendum)* ·
DG-016 **(OWNER/GOVERNANCE — public exposure clearance)** · DG-017 *(artefact commit/tag — fully
specified in §27, awaiting the operator's reviewed action)*.

Of these, only **DG-016** is genuinely blocking a *public* demo, and it is not engineering work.

## N. Readiness Score

### Revision 2 scores (current)

| Dimension | Rev 1 | **Rev 2** | What moved, and what is still deducted |
|---|---:|---:|---|
| **Source deployment readiness** | 72 | **91** | **+19.** `B-03` closed in source with tests and a governance record (+8); `B-15` closed — the index is LF repo-wide, and the audit proved it rather than assuming it (+5); `B-08` downgraded, artefact fully specified in §27 (+4); `DG-014` closed clean across 9,689 paths (+2). **Still deducted:** −6 the three runtime trees remain gitignored so the artefact is a build product (`B-01`, by design, not a defect); −3 the commit and tag are specified but **not executed** — that is the operator's reviewed action, and until it happens no immutable artefact exists |
| **Database deployment readiness** | 68 | **84** | **+16.** `B-05` closed by the locked Option 3 decision (+12); the version-row mismatch and the `saas_branding_revision`/gitignored-stylesheet inconsistency both become **non-issues** on a fresh install (+4). **Still deducted:** −10 the `bulk_rep` ACO is still absent *even on the development instance*, so two reports fail closed today (`B-06`); −5 the `modules` row remains a manual, easily-missed step with a silent failure mode (`B-07`); −1 the seeder's baseline artefact must be present on the target — now a documented provisioning input rather than a hard-coded path |
| **Ubuntu compatibility** | 83 | **96** | **+13.** `B-03` fixed (+6); `access_compat`, the three build hosts and `PrivateTmp` all confirmed on the VM (+5); `B-15` closed (+2). **Still deducted:** −3 Node 24 not yet installed (`B-02`, operator-scheduled); −1 cron-as-`www-data` remains a design constraint the runbook must state explicitly |
| **Public demo safety readiness** | 55 | **74** | **+19.** `B-12` closed — the queue is empty and four provisioning controls are specified (+5); `B-13` closed — registration's call removed outright, telemetry gated three independently sufficient ways, no `Telemetry_Task` row (+6); the audit-log concern becomes moot on a fresh database (+3); `mod_ssl` present and firewall configured (+5). **Still deducted:** −15 **`B-11`/`DG-016` governance clearance is open**, and no amount of engineering closes it; −6 the DB password is still the public upstream default until the installer generates one; −5 no TLS certificate issued yet |
| **OVERALL** | 69 | **86** | The remaining deductions are now concentrated almost entirely in (a) actions that by definition happen *on the VM*, and (b) one governance gate that is not engineering work. **No open item is a source defect.** |

**Why the overall is 86 and not higher:** two things genuinely hold it down. `B-06` is a real defect
present *today* on the development instance — two reports fail closed for every role, and that has
never been provisioned anywhere. And `DG-016` is a hard gate on *public* exposure that this pass
cannot touch. Neither is a reason to delay the source commit.

### Revision 1 scores (retained for comparison)

| Dimension | Score | Deductions |
|---|---:|---|
| **Source deployment readiness** | **72 / 100** | −15 the three runtime trees are gitignored, so the artefact is a build product rather than a checkout (B-01); −8 the deployment artefact is unidentified — 71 unpushed commits, no tag (B-08); −3 `git archive` would silently drop `tools/` (B-22); −2 CRLF unverified on `bin/console` (B-15). **No credit lost for code quality**: the tree is clean, fully committed, and free of drive-letter paths outside one CLI constant |
| **Database deployment readiness** | **68 / 100** | −12 no strategy decision recorded (B-05); −10 the `bulk_rep` ACO is absent **even on the development instance**, so two reports fail closed today (B-06); −5 the `modules` row is an undocumented manual step (B-07); −3 the version row disagrees with `version.php` (§1.2); −2 `saas_branding_revision = 1` points at gitignored stylesheets (B-05/§16.3). **Large credit retained** for zero schema change and a committed deterministic seeder — the schema half of this dimension is genuinely excellent |
| **Ubuntu compatibility** | **83 / 100** | −6 seeder blocked by a hard-coded `C:/` path (B-03); −4 Node 24 not in the Ubuntu archive (B-02); −3 Apache 2.2 syntax needs `access_compat` or a vhost override (B-19); −2 four Windows-path globals if the DB is cloned (B-14); −2 cron/root and `PrivateTmp` design constraints (B-20, B-21). **High baseline** because upstream CI runs on `ubuntu-24.04`, the schema is 10.11-clean, and the bootstrap derives every path and URL at runtime |
| **Public demo safety readiness** | **55 / 100** | −15 governance clearance for public exposure is open — D-3/D-4/D-11 (B-11); −10 the DB password is a public upstream default (B-09); −8 no TLS design (B-10); −5 `Email_Service` active at 2 minutes (B-12); −4 telemetry/product-registration unverified (B-13); −3 audit log carries interpolated patient values (B-17). **Credit retained** for a genuinely disabled integration surface and demonstrably synthetic data |
| **OVERALL** | **69 / 100** | Weighted toward the two dimensions that gate a *public* demo. The application is in far better shape than this number suggests — the deductions are concentrated in provisioning steps that have never been executed and in governance items that are not engineering work |

## O. Verdict

# `READY AFTER LISTED CHANGES`

**Revision 2 — the verdict is unchanged, but its content is not.**

At Revision 1 this verdict meant *"eight P0 items remain, three of them unresolved in the source
itself."* At Revision 2 it means something materially different and much narrower:

> **No open item is a source defect.** Every remaining P0 is an action that happens *on the VM* —
> build the dependency trees, provision the report ACO, insert the `modules` row — and the one
> genuinely blocking item for a *public* demo (`B-11`/`DG-016`) is a governance clearance that no
> engineering work can close.

**Still not** `READY FOR DEMO DEPLOYMENT`, for one honest reason: three P0 provisioning steps have
**never been executed anywhere**, and one of them (`B-06`, the `bulk_rep` ACO) is a live defect on
the development instance today. A verdict of "ready" would be asserting that untested provisioning
works.

**Separately, and this is the question Revision 2 was asked:** the *source tree* verdict is
**`SOURCE READY FOR DEPLOYMENT COMMIT`** — see §26.7. The two verdicts are deliberately distinct.
Source readiness is a precondition for deployment readiness, not the same claim.

**Revision 1 rationale, retained:** eight P0 items remained, three of them (B-03, B-06, B-08)
unresolved on the development instance itself, not merely absent on the target.

**Not** `NOT READY — BLOCKERS REMAIN`: no blocker is architectural. Every P0 has a known, bounded
resolution — a build step, a documented command, a database row, or a decision that this report
makes for you. The application code is portable, the schema is stock and 10.11-clean, the demo data
is synthetic and regenerable, the integration surface is already disabled, and upstream CI runs on
the exact target OS.

**One qualification the reader must not lose:** `EV-047` §13 records its own acceptance as **NOT
MET** — *"A person who did not write it provisions from it without asking an unanswered question:
NOT MET — never executed."* **This deployment is that first execution.** Every question it fails to
answer should be logged in `EV-047` §12 as a defect in that runbook, exactly as it asks.

## P. Next Safe Deployment Sequence

**Do not execute this until §23 and §24 have been reviewed and the DG-001…DG-017 decisions are made.**

**Phase 0 — decisions (no machine work)**
1. Confirm **Option 3** (§7.4) as the database strategy.
2. Resolve **DG-005** (`B-03`) — how `SeedDemoCommand`'s baseline precondition is satisfied. If the
   constant is to be made configurable, raise the **numbered patch record** required by Q1/Invariant 4.
3. Resolve **DG-016** (`B-11`) — governance clearance for public exposure. Until cleared, plan an IP
   allowlist.
4. Resolve **DG-011** and **DG-012** — email and telemetry posture.

**Phase 1 — source preparation**
5. Push `feat/thiqa-branding-foundation` (71 unpushed commits) and **tag** the deployment point at
   `4d09baef1`.
6. `git ls-files | tr 'A-Z' 'a-z' | sort | uniq -d` → expect empty (**DG-014**);
   `git ls-files --eol bin/console` → expect LF (**DG-015**).

**Phase 2 — VM base**
7. Install Apache 2.4, PHP 8.3 + all 33 extensions, MariaDB 10.11, Composer, `file`, and Node 24.
8. **Verify `php -m` under CLI *and* the web SAPI against `composer.json:15-47`** (**DG-001**,
   `B-04`). Do not proceed on a green `composer install`.
9. `apache2ctl -M | grep -E 'rewrite|setenvif|ssl|access_compat'` (**DG-002**).
10. Apply the PHP settings in §3.3; point `error_log` at a real path.

**Phase 3 — source transfer**
11. `git clone` (or `rsync` the working tree) at the tag from step 5 — **not** `git archive`
    (**DG-004**).

**Phase 4 — dependencies and assets**
12. `composer install --no-dev --optimize-autoloader --no-interaction`.
13. Confirm connectivity for `napa` (**DG-003**), then `npm ci && npm run build`.
14. Confirm `public/themes/` holds exactly the 18 approved files and **zero**
    `solar`/`manila`/`cobalt_blue`/`forest_green` — run the Q77 guard:
    `vendor/bin/phpunit -c phpunit-isolated.xml --filter 'BrandingGovernanceGuard' --no-coverage`.

**Phase 5 — server directories and permissions**
15. Apply §9: `chown -R www-data:www-data sites/`, dirs `0700`, files `0600`; leave application code
    read-only; keep build outputs read-only at runtime.

**Phase 6 — database**
16. `CREATE DATABASE <db> CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;` create the app user
    **`@'127.0.0.1'`**, `GRANT ALL ON \`<db>\`.*` — **that one schema only, never `*.*`**
    (`EV-047` §4).
17. Bind MariaDB to `127.0.0.1`.
18. Run `InstallerAuto.php` with **`server=127.0.0.1`, not `localhost`**, and a **generated** ≥20-char
    CSPRNG password (`B-09`). Confirm **283 tables** and `$config = 1`.
19. `chmod 400 sites/default/sqlconf.php`; `chmod 700 sites/default`.

**Phase 7 — application configuration**
20. Insert the `modules` row for `oe-module-thiqa-branding` (`B-07`).
21. `php bin/console thiqa-branding:apply-profile --site=default --dry-run`, review, then apply.
22. Set the per-instance globals from §I and the facility record (**including street, city, postal
    code and phone** — `EV-047` §7).
23. `php bin/console thiqa-branding:provision-report-acl` (`B-06`) — **mandatory**.
24. Create demo users and role memberships with **fresh** credentials (`B-18`), including an
    Accounting-role account (**DG-010**).
25. `php bin/console thiqa-branding:seed-demo` — only after `B-03` is resolved.
26. Optionally `thiqa-branding:materialise`; otherwise leave revision 0 (a healthy state).
27. `php bin/console thiqa-branding:verify --site=default` → must report **self-consistent**.

**Phase 8 — web layer**
28. Create the VirtualHost per §10.5, including the 2.4-syntax deny blocks and the
    `acknowledge_license_cert.html` deny.
29. Point DNS for `demo.<DOMAIN>` at the static IPv4.
30. Issue the TLS certificate; enable `mod_ssl`; redirect 80 → 443 (`B-10`).
31. `apache2ctl configtest` → `Syntax OK`, then reload.

**Phase 9 — integrations and hardening**
32. Re-verify every flag in §14 is `0`; **deactivate `Email_Service`** (`B-12`); confirm no MTA on
    `localhost:25`; confirm telemetry posture (`B-13`).
33. Set `display_errors = Off`, `ServerTokens Prod`, `ServerSignature Off`.
34. Confirm `sites/*/documents`, `contrib`, `tests`, `bin` return **403** (not 500 — **DG-002**).

**Phase 10 — scheduling**
35. Create the cron entry as **`www-data`**, absolute path,
    `php /path/to/bin/console background:services run`, at an interval ≤ the shortest **active**
    service (240 min once `Email_Service` is off; 2 min if it stays on).

**Phase 11 — smoke tests (`EV-047` §11 — record all five)**
36. **S-1** login as a named non-`admin` account.
37. **S-2** register a patient **as the Front Office role** (where the `front_office.json` defect
    would surface).
38. **S-3** run a backup — verify `mysql_bin_dir` first (`B-14`).
39. **S-4** confirm `next_run` advances for every active service within one interval.
40. **S-5** audit-log tamper report returns clean — ⚠ **not** over a window containing an `api_log`
    row (PB-030's false positive is open).
41. Additionally: login page renders `Thiqa`; `patient_list.php` and
    `unique_seen_patients_report.php` are reachable (proves `B-06` closed); `thiqa-branding:verify`
    exits 0.
42. **Record the elapsed provisioning time** — `PRC-003`/`RDY-0069` need it.

**Phase 12 — role-based demo tests**
43. Walk Administrator, Doctor, Nurse/Clinician, Receptionist/Front Office and Billing/Back Office
    through the flows in `EV-040-d7-demo-script.md`.

**Phase 13 — backup and release**
44. Snapshot the VM disk and take a database dump; **treat the backup as unverified until a restore
    has been proven** into a disposable instance (`RDY-0081`/`RDY-0082`, both open).
45. Public release **only after `B-11` is cleared**. Until then, restrict by IP allowlist.
46. Complete `EV-047` §12 — provisioner, date, source SHA, elapsed time, S-1…S-5 results, and
    **every question this runbook failed to answer**.

---

# 26 — Source remediation record (Revision 2)

## 26.1 Baseline preserved and confirmed before any edit

```
git branch --show-current  → feat/thiqa-branding-foundation          ✓ matches expected
git rev-parse HEAD         → 4d09baef135a3cf90dfd2e48f8163e3eb6d6b16e ✓ matches expected
git rev-list --count origin/feat/thiqa-branding-foundation..HEAD → 71 ✓ matches expected
git status --short         → only the pre-existing untracked .docx (left untouched)
```

**No discrepancy. Source modification proceeded.** `HEAD` is unchanged by this pass — **nothing was
committed**, so the working tree is left as a reviewable diff, as instructed.

## 26.2 What changed

| File | Status | Lines | Purpose |
|---|---|---:|---|
| `interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/BaselineOption.php` | **new** | 135 | Value object: resolve `--baseline-path`, verify the baseline's SHA-256 |
| `interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/SeedDemoCommand.php` | modified | +11 −7 | Register, resolve and delegate to the option |
| `tests/Tests/Isolated/Modules/ThiqaBranding/Console/BaselineOptionTest.php` | **new** | 288 | 18 tests |
| `docs/branding/adr/patch-records.md` | modified | +167 | Governance record **PR-17** |
| `docs/demo-deployment-readiness.md` | modified | — | This revision |

**No other file was touched.** No core file, no database, no server, no dependency, no git state.

## 26.3 Why an explicit CLI option, and why a value object

The brief asked for the smallest backward-compatible design and for a rationale if another mechanism
were used. Both choices follow the repository's own architecture rather than convenience:

- **A CLI option, not an environment variable.** Provisioning is a scripted, auditable sequence. An
  option appears in `--help`, is captured verbatim in shell history and in the runbook, and cannot
  arrive unnoticed from an inherited environment. An env var would be invisible at the call site and
  would make *"which baseline did that run actually verify?"* unanswerable afterwards.
- **A value object, mirroring `SiteOption`.** `src/Console/SiteOption.php` is the module's existing
  pattern for a console option requiring validation — `define()` / `resolve()`, returning a
  never-throwing result carrying either a value or an operator-facing error, and never echoing the
  rejected input. `BaselineOption` is that pattern applied unchanged.
- **The decisive practical reason:** `SeedDemoCommand::checkPreconditions()` calls `QueryUtils`
  throughout, so it **cannot be instantiated without a database**, and the brief forbids running the
  seeder against the live development database. Logic left inline there is logic that **cannot be
  unit-tested at all**. Moving resolve-and-verify into a value object is what makes the ten required
  test cases possible.

## 26.4 The fail-closed guarantee, stated for review

| Property | Before | After |
|---|---|---|
| `BASELINE_SHA256` hard-coded, not configurable | ✓ | **✓ unchanged** |
| Baseline must exist | ✓ | **✓ unchanged** |
| Digest must match | ✓ | **✓ unchanged** |
| Failure → `$problems[]` → `checkPreconditions()` false → `FAILURE` exit | ✓ | **✓ unchanged** |
| Any way to skip verification | none | **none** |
| Omitting the new option | n/a | **byte-for-byte the previous behaviour** |

An operator can now say *where to look*. They still cannot say *what counts as acceptable* — the
accepted digest is a compiled-in constant. `testNoInputShapeSkipsVerification` enumerates seven
bypass shapes (omitted, empty, whitespace, directory, nonexistent, literal `skip`, null-byte
injection) and asserts **all seven refuse**.

## 26.5 Tests run and results

| Command | Result |
|---|---|
| `phpunit -c phpunit-isolated.xml --filter 'BaselineOption' --no-coverage` | **OK — 18 tests, 32 assertions** |
| `phpunit -c phpunit-isolated.xml --filter 'ThiqaBranding' --exclude-filter 'ThiqaBranding.Twig' --no-coverage` | **OK — 1298 tests, 3660 assertions** (no regression) |
| `phpunit -c phpunit-isolated.xml --filter 'BrandingGovernanceGuard' --no-coverage` | **OK — 31 tests, 66 assertions** |
| `php -l` on all three PHP files | **No syntax errors detected** ×3 |
| `phpcs` on all three PHP files | **clean** |
| `composer validate --no-check-publish` | **`./composer.json is valid`** |
| `git diff --check` | **clean** — no whitespace errors |
| `phpstan analyze` level 10, full codebase (local `tmpDir` override per `CLAUDE.local.md` §9) | **Completed cleanly** — grep for `Internal error` / `Result is incomplete` returns **none**, so the §9 silent-abort mode did not occur. Exit 1 = pre-existing errors found |

**PHPStan result, stated precisely.** Extra diligence beyond the brief's validation list, because the
project forbids new baseline entries.

- **`BaselineOption.php`: 0 errors.** **`BaselineOptionTest.php`: 0 errors.** The new code is clean
  at level 10, and **no baseline entry was added** — none was needed.
- `SeedDemoCommand.php` reports 44 errors — **all pre-existing.** Proven, not assumed:
  `SeedDemoCommand` has **zero entries in `.phpstan/baseline/`**, so these are unsuppressed errors
  that were already present before this pass; they are not baseline entries de-anchored by the
  `+2`-line shift my `configure()` addition caused.
- The only errors falling inside my edited line ranges are two at `:307`
  (`Cannot cast mixed to string`, `Direct access to $GLOBALS is forbidden`) — that is
  `$GLOBALS['OE_SITE_DIR']` at former line 305, displaced by two lines. **Not authored by this pass.**
- **My replaced baseline-check block (`:333-342`) reports no errors at all** — the delegated
  `$baseline->verify()` call is cleaner at level 10 than the inline `is_file()`/`hash_file()` pair it
  replaced.

**Net: this change introduces zero new PHPStan errors.** The 44 pre-existing ones in
`SeedDemoCommand.php` are untouched and out of scope for this remediation; they are worth a separate
cleanup pass.

The Twig render group is excluded per `CLAUDE.local.md` §9: it hangs on this host inside
`session_start()` under PHPUnit, which is **upstream behaviour unrelated to this change** (upstream's
own `TwigTemplateRenderTest` hangs identically). Those tests are untouched and are expected to pass
in CI.

**Not run, deliberately:** `composer update`, migrations, demo seeding against the live database,
destructive reset scripts, production integration tests, email sends, remote pushes.

## 26.6 Build assumptions re-confirmed from source (§10 of the brief)

| Assumption | Confirmed | Evidence |
|---|---|---|
| `vendor/` not tracked | ✓ | `git ls-files vendor` → 0; `.gitignore:9` |
| `public/assets/` generated assets not tracked | ✓ | `git ls-files public/assets` → **10 files, all under `public/assets/modified/`** (an explicit `!` un-ignore); `.gitignore:16` |
| `public/themes/` not tracked | ✓ | `git ls-files public/themes` → 0; `.gitignore:17` |
| Node requirement `>= 24` | ✓ | `package.json:28-30`; CI `node-version: ['24']` |
| `npm ci` postinstall invokes `napa` + asset install | ✓ | `package.json:7`; `scripts/install-assets.js:18` |
| `npm run build` creates the themes | ✓ | `package.json:8,12`; Q77-pruned entry map `webpack.themes.js:151-197` |
| `composer install --no-dev --optimize-autoloader --no-interaction` appropriate | ✓ | 61 dev packages are QA-only; no `post-install-cmd` |
| `composer update` must **not** be used | ✓ | Lockfile is the pinned, tested set |

**Nothing was regenerated.** No `composer install`, no `npm ci`, no build ran during this pass.

## 26.7 Source verdict

# `SOURCE READY FOR DEPLOYMENT COMMIT`

The one proven source defect in scope (`DG-005` / `B-03`) is fixed, tested, governance-recorded and
regression-checked. `DG-011`, `DG-012`, `DG-014` and `DG-015` were each investigated to the point of
evidence and **correctly required no source change** — in two cases a "fix" would have been actively
harmful: normalising line endings would have broken the SHA-256 manifests, and patching `MyMailer`
would have altered generic upstream email behaviour to solve a configuration problem.

**No new P0 or P1 source blocker was introduced.** The diff is 5 files, additive in structure, with
the only behavioural change being that a previously machine-locked path became an input.

**This is not a statement that the demo is ready to deploy** — see §25.O.

---

# 27 — Deployment artefact definition (`DG-017` / `B-08`)

**Nothing in this section was executed.** No commit, no tag, no push.

## 27.1 Current position

| Item | Value |
|---|---|
| Branch | `feat/thiqa-branding-foundation` |
| `HEAD` (unchanged by this pass) | `4d09baef135a3cf90dfd2e48f8163e3eb6d6b16e` |
| Remote branch | `origin/feat/thiqa-branding-foundation` = `203f24de5e8f6eae2f553f505cb4c5e7e512e225` |
| Local commits ahead of remote | **71** |
| Uncommitted remediation | 2 modified + 2 new source/test files + 1 modified governance doc |

⚠ **The tag must therefore point at a commit that does not yet exist.** `4d09baef1` **must not** be
the deployment tag: it predates the `DG-005` fix, so a demo built from it cannot seed its dataset.

## 27.2 Files that must be in the deployment commit

```
interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/BaselineOption.php   (new)
interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/SeedDemoCommand.php  (modified)
tests/Tests/Isolated/Modules/ThiqaBranding/Console/BaselineOptionTest.php                  (new)
docs/branding/adr/patch-records.md                                                         (modified)
docs/demo-deployment-readiness.md                                                          (new)
```

**Deliberately excluded — do not `git add -A`:**

| Path | Why excluded |
|---|---|
| `Documentation/EHI_Export/docs/diagrams/tables/lists_medication.2degrees.docx` | Pre-existing, untracked, unrelated to this work, left untouched as instructed |
| `docs/gap-inventory-and-fix-groups-2026-08-15.md` | ⚠ **Appeared in the working tree during this pass and was not created by it.** It was absent at the §26.1 baseline. Most likely the concurrent second agent working this repository. **Not reviewed, not validated, and not part of this remediation** — the operator should confirm its provenance before deciding whether it belongs in any commit |

The staging command in §27.5 lists the five paths explicitly for exactly this reason. **A `git add -A`
or `git add .` would sweep both of the above into the deployment commit.**

**No required runtime source remains outside git.** The three build-output trees (`vendor/`,
`public/assets/`, `public/themes/`) are gitignored **by design** and are produced on the server;
that is `B-01`, not a missing file.

## 27.3 Proposed commit message

```
fix(branding): make the demo seeder's rollback baseline path configurable

SeedDemoCommand hard-coded the RDY-0044-A baseline as an absolute Windows path
and checked it with a fail-closed is_file() precondition, so the deterministic
demo seeder could only ever run on one developer workstation. On the Ubuntu
demo target it refused before writing a row, which made the locked Option 3
database strategy unexecutable.

Add a --baseline-path option via a BaselineOption value object, mirroring the
existing SiteOption pattern. The location becomes an input; the integrity
requirement does not move: BASELINE_SHA256 stays a compiled-in constant, the
file must still exist and still hash correctly, and no flag, value or omission
reaches the seeding logic without verification passing. Omitting the option
reproduces the previous behaviour byte-for-byte.

The logic lives in a value object because checkPreconditions() cannot be
instantiated without a database, so anything left inline there is untestable.

Recorded as patch record PR-17. Closes DG-005 / B-03.

Tests: 18 new isolated tests (32 assertions); ThiqaBranding isolated suite
1298 tests / 3660 assertions green; BrandingGovernanceGuard 31/66 green.

Assisted-by: Claude Code
```

## 27.4 Proposed tag

```
demo-deploy-2026-08-15
```

An annotated, date-stamped deployment tag — not a semantic version, because this marks *"the source
the demo VM was built from"*, not a product release, and `version.php` already owns the product
version (8.2.0).

## 27.5 Exact commands for the operator to run **after review**

```bash
# 1. Stage exactly the remediation set (note the quoted paths).
git add \
  "interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/BaselineOption.php" \
  "interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/SeedDemoCommand.php" \
  "tests/Tests/Isolated/Modules/ThiqaBranding/Console/BaselineOptionTest.php" \
  "docs/branding/adr/patch-records.md" \
  "docs/demo-deployment-readiness.md"

# 2. Confirm the .docx is NOT staged.
git status --short

# 3. Commit (message from §27.3 — use an editor or a -F file to keep the body intact).
git commit

# 4. Record the new deployable SHA.
git rev-parse HEAD

# 5. Push the branch (72 commits will go up: the 71 existing + this one).
git push origin feat/thiqa-branding-foundation

# 6. Tag the post-remediation commit — NOT 4d09baef1.
git tag -a demo-deploy-2026-08-15 -m "Source artefact for the demo-openemr Ubuntu deployment (includes PR-17 / DG-005)"

# 7. Push the tag.
git push origin demo-deploy-2026-08-15
```

## 27.6 Then, on the VM

```bash
git clone --branch demo-deploy-2026-08-15 <repo-url> /var/www/openemr
```

**`git clone`, not `git archive` and not `rsync` from the Windows working tree** — the first would
drop `tools/` (`.gitattributes:20`, `B-22`), and the second would carry this host's CRLF working-tree
line endings into every shebang script (§24.0). Cloning the tag gives LF and the complete tree.

---

*Revision 1 was discovery only. Revision 2 modified source: 2 files added, 2 modified, 1 governance
record extended — enumerated in §26.2. No database, server, dependency or git state was modified; no
commit, tag or push was made; no credential was read or printed.*
