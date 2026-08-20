# 10 — Multisite / Multitenant Discovery

Read-only audit of OpenEMR's built-in "multisite" mechanism and its
implications for a SaaS tenancy decision. Every claim is cited to
`path:line`. Findings only — decisions belong in Document 0.

---

## 1. `sites/` layout

`git ls-files sites/` returns 43 tracked files, all under `sites/default/`.
The tree has one canonical skeleton — `sites/default/` — which is copied
whenever a new site is provisioned (see §4).

Per-site skeleton (evidence: paths from `git ls-files sites/`):

| Path (relative to `sites/<site>/`) | Purpose |
|---|---|
| `sqlconf.php` | Per-site MySQL connection config. `sites/default/sqlconf.php:1-27` |
| `config.php` | Per-site printing, prescription, druglabel, docs config. `sites/default/config.php:1-76` |
| `statement.inc.php` | Per-site patient-statement generator (customizable). `sites/default/statement.inc.php` |
| `referral_template.html` | Per-site referral letter template. |
| `faxcover.txt`, `faxtitle.eps` | Per-site fax templates. |
| `clickoptions.txt` | Per-site clinical clickoption customization. |
| `docker-version` | Marker file for docker-provisioned sites. |
| `LBF/*.plugin.php` | Per-site Layout-Based-Form plugins (6 stock files: `LBFathbf`, `LBFathv`, `LBFfms`, `LBFgcac`, `LBFvbf`, `LBTref`). |
| `documents/` | Root for **all** user-generated content for the site. |
| `documents/certificates/` | SSL/signing certs (README only). |
| `documents/couchdb/` | Optional CouchDB doc-store staging. |
| `documents/custom_menus/{,patient_menus/}Custom.json` | Per-site menu overrides. |
| `documents/edi/` | Outbound X12 EDI claim files. `sites/default/documents/edi/README.txt` |
| `documents/era/` | Inbound X12 835 ERA remittance files. `sites/default/documents/era/README.txt` |
| `documents/letter_templates/` | Per-site letter templates. `sites/default/documents/letter_templates/sample` |
| `documents/logs_and_misc/methods/` | Payment-method logs. |
| `documents/onsite_portal_documents/templates/*.tpl` | Portal document templates (5 stock: `Help`, `Hipaa_Document_Oemr`, `Insurance_Info`, `Medical_History`, `Privacy_Document`). |
| `documents/procedure_results/` | Inbound HL7 lab result files (`interface/orders/receive_hl7_results.inc.php:1549,1596,1728,1860`). |
| `documents/temp/` | Per-site scratch/tmp dir (`interface/patient_file/report/custom_report.php:555,629`). |
| `documents/.htaccess` | Blocks direct web access to the entire per-site documents tree. |
| `images/` | Per-site branding: `login_logo.gif`, `logo_1.png`, `logo_2.png`, plus `logos/core/{favicon,login,menu}/…` and `logos/portal/…`. |
| `images/visa_mc_disc_credit_card_logos_176x35.gif` | Payment logos. |

Runtime-created (not in git) per-site dirs, referenced by code but with no
tracked stub:

- `faxcache/<mode>/<filebase>/` — outbound fax staging (`interface/fax/fax_dispatch.php:57,846`).
- `documents/<pid>/`, `documents/<pid>/encounters/` — patient documents (`interface/fax/fax_dispatch.php:124,241`; `interface/patient_file/merge_patients.php:458-459`).
- `documents/education/` — patient-education uploads (`interface/super/manage_site_files.php:30`; `interface/patient_file/education.php:23`).
- `documents/doctemplates/` — document templates (`interface/super/manage_document_templates.php:35`; `interface/patient_file/download_template.php:357`).
- `documents/labs/<lab_name>/logs/` — lab order logs (`interface/forms/procedure_order/common.php:265,497`).
- `documents/erx_error/` — eRx error logs (`interface/eRx_xml.php:1000-1004`).
- `documents/logs_and_misc/weno/` — Weno pharmacy sync logs (`interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php:74-75`).

There is **no** top-level `sites/<x>/logs/` in the tracked skeleton;
runtime logs sprawl inside `documents/logs_and_misc/…` and per-feature
subtrees under `documents/`.

---

## 2. `sites/default/sqlconf.php` verbatim

Full file (27 lines):

```php
<?php
//  OpenEMR
//  MySQL Config

$host   = 'localhost';
$port   = '3306';
$login  = 'openemr';
$pass   = 'openemr';
$dbase  = 'openemr';

$sqlconf = [];
global $sqlconf;
$sqlconf["host"]= $host;
$sqlconf["port"] = $port;
$sqlconf["login"] = $login;
$sqlconf["pass"] = $pass;
$sqlconf["dbase"] = $dbase;

//////DO NOT TOUCH THIS///
$config = 0; /////////////
```

Variables (`sites/default/sqlconf.php:6-24`):

| Var | Meaning |
|---|---|
| `$host` | MySQL hostname. |
| `$port` | MySQL TCP port. |
| `$login` | MySQL username. |
| `$pass` | MySQL password (plaintext on disk). |
| `$dbase` | MySQL **database name** — one per site. |
| `$sqlconf` | Array mirror of the above five for downstream consumers. |
| `$config` | Install-state flag: `0`=uninstalled, `1`=installed. `index.php:26` uses this to route to `login` vs `setup.php`. |

`library/sqlconf.php:17-26` is the router that resolves
`OE_SITE_DIR` and `require_once`s the site-specific `sqlconf.php`;
`library/sql.inc.php:20` includes that router before any DB call.
`library/sql.inc.php:512-514` warns that the historical
`secure_sqlconf.php` variant is **no longer supported**.

---

## 3. Site-selection mechanism

### Primary entry point: `interface/globals.php`

Site resolution happens once per request, before any DB access, in
`interface/globals.php:277-327`:

- `interface/globals.php:277` — `$session = SessionWrapperFactory::getInstance()->getActiveSession();`
- `interface/globals.php:278` — `$siteId = $session->get('site_id');`
- `interface/globals.php:279-281` — **precedence rule**: if `$_GET['site']` is present it wins over the session, otherwise the existing session value is kept.
- `interface/globals.php:284-293` — if there is no session **and** no `?site=`, and auth is required, throw `MissingSiteIdException`.
- `interface/globals.php:295-297` — **fallback for unauth'd requests**: `$tmp = $_SERVER['HTTP_HOST']`; if `sites/<HTTP_HOST>/` does not exist as a directory, coerce to `"default"`. This is the only host-header-based selection in the core stack, and it is *only* reached when auth is being ignored (portal / anonymous endpoints).
- `interface/globals.php:301-308` — regex allowlist `/^[A-Za-z0-9\-.]+$/`; anything else → HTTP 400.
- `interface/globals.php:310-323` — if the incoming `?site=` disagrees with the session's `site_id`, clear the session (`SessionUtil::clearSession()`) and redirect to `login.php?site=<new>`. Comment at line 311: "to prevent using session to penetrate other OpenEMR instances within same multisite module".
- `interface/globals.php:325-326` — first write of `site_id`: `SessionUtil::setSession('site_id', $tmp);`
- `interface/globals.php:332` — `OE_SITE_DIR = OE_SITES_BASE . "/" . site_id`.
- `interface/globals.php:335` — `OE_SITE_WEBROOT = $web_root . "/sites/" . site_id`.
- `interface/globals.php:649` — `require_once($GLOBALS['OE_SITE_DIR'] . "/config.php");`

`OE_SITES_BASE` is fixed at `$webserver_root/sites` (`interface/globals.php:237`).

### Secondary entry point: root `index.php`

`index.php:10-24` is a lightweight redirector with the *same* algorithm:

```php
if (!empty($_GET['site'])) {
    $site_id = $_GET['site'];
} elseif (is_dir("sites/" . ($_SERVER['HTTP_HOST'] ?? 'default'))) {
    $site_id = ($_SERVER['HTTP_HOST'] ?? 'default');
} else {
    $site_id = 'default';
}
```

Then `require_once "sites/$site_id/sqlconf.php"` and redirect to either
`interface/login/login.php?site=$site_id` (installed) or
`setup.php?site=$site_id` (not installed) based on `$config`
(`index.php:24-29`).

### CLI entry point: `bin/console`

`bin/console:31-47` extracts `--site=<name>` from `$argv` (default:
`"default"`), sets `$_GET['site']` for the downstream include chain, and
loads `interface/globals.php`. `Documentation/MIGRATION_GUIDE_CRONJOBS.md:35-43`
documents the pattern: **one cron entry per site**.

### Default site name

`"default"` is the hard-coded fallback throughout: `index.php:14,17`,
`interface/globals.php:297`, `bin/console:33`, `meta/health/index.php:39`,
`ccdaservice/ccda_gateway.php:55`, `library/MedEx/MedEx.php:31`
(`$_SERVER['HTTP_HOST'] = 'default'; //change for multi-site`).

**Behaviour if no site is specified**: for authenticated requests →
`MissingSiteIdException` (`interface/globals.php:290`). For unauth'd
requests → tries `HTTP_HOST`, falls back to `"default"`
(`interface/globals.php:295-297`).

---

## 4. Setup / site provisioning

### Web-based provisioning: `setup.php`

`setup.php:1-32` documents a 7-state install wizard. Multisite is
**gated behind a source-code flag** (`setup.php:49-51`):

```php
// Warning. If you set $allow_multisite_setup to true, this is a potential security vulnerability.
$allow_multisite_setup = false;
```

`Documentation/help_files/openemr_installation_help.php:640,751` and
`Documentation/api/DEVELOPER_GUIDE.md:226` document the workflow:
edit `setup.php`, flip `$allow_multisite_setup` to `true`, run
`setup.php?site=<newsite>`, then flip back to `false`.

The wizard offers a "Source Site" dropdown (`setup.php:900-947`) that
lists every existing directory under `sites/` so the operator can pick a
template. The cloning path is gated by a separate flag
`$allow_cloning_setup = false;` (`setup.php:54`) with its own kill-switch
(`setup.php:67-71`).

### Provisioning mechanics

The actual work is done by `library/classes/Installer.class.php`:

- `Installer::source_site_id` (`library/classes/Installer.class.php:66,104`) — the site to clone from.
- Constructor pulls it from `$cgi_variables['source_site_id']`
  (`library/classes/Installer.class.php:104`).
- `Installer::recurse_copy()` (`library/classes/Installer.class.php:1783-1794`) copies the entire
  `$OE_SITES_BASE/<source>` tree into `$OE_SITES_BASE/<newsite>`.
- Called at `library/classes/Installer.class.php:721-723`:
  `$source_directory = $GLOBALS['OE_SITES_BASE'] . "/" . $this->source_site_id;`
- Database is dumped and reimported via `Installer::dumpSourceDatabase()`
  (`library/classes/Installer.class.php:1808-1819`), which
  `include`s the source site's `sqlconf.php` to obtain credentials.

### CLI provisioner

`contrib/util/installScripts/InstallerAuto.php:19-65` provides an
unattended installer:

```
php -f InstallerAuto.php login=openemr2 pass=openemr2 dbname=openemr2 \
    site=default2 source_site_id=default clone_database=yes
```

Docker one-shot equivalents live in `docker/release/auto_configure.php:114`,
`docker/flex/auto_configure.php:123`, `docker/binary/auto_configure.php:114`.

### What must be created per new site

**On disk**: full recursive copy of `sites/<source>/` → `sites/<new>/`
(`library/classes/Installer.class.php:721-723,1783`). Per
`Documentation/help_files/openemr_installation_help.php:737`,
"the non-database data located in the 'sites' directory is copied into
the newly created sub-directory for that site" — the docs warn that
this can leak PHI between sites if the source site contains patient data
(same file, lines 745-747).

**In DB**: a brand-new MySQL database is created (or cloned from source),
schema imported from `sql/database.sql`, and the initial admin user
seeded. See `Installer.class.php` throughout.

---

## 5. Per-site file storage (recap for tenant-isolation view)

Every artefact that could contain PHI or per-tenant customization lives
under `$OE_SITE_DIR = sites/<site_id>/` (`interface/globals.php:332`).
The grep in §1 shows ~40 distinct call sites resolving paths through
`OEGlobalsBag::getInstance()->get('OE_SITE_DIR')` for: patient documents,
EDI claims, 835 ERAs, fax cache, letter templates, doc templates,
education uploads, procedure results, portal templates, prescription
error logs, custom PDF generators, LBF plugins, per-site logos, per-site
CouchDB logs, Weno pharmacy sync data, and eye-mag form artefacts.

Backup of a single tenant therefore requires **two artefacts**:
1. `sites/<site>/` filesystem tree (git-tracked skeleton + everything created at runtime).
2. The MySQL database named in `sites/<site>/sqlconf.php:10` (`$dbase`).

---

## 6. Shared code vs isolated data — confirmed

Evidence:

- Single Apache DocumentRoot: `$webserver_root = dirname(__FILE__, 2);`
  (`interface/globals.php:56`); the entire PHP tree serves every site.
- Include-path mapping: `interface/globals.php:332` sets `OE_SITE_DIR`
  from `$_GET['site']`/session; `library/sqlconf.php:26`
  (`require_once $siteDir . "/sqlconf.php";`) is the sole place that
  resolves per-site DB credentials; `interface/globals.php:649`
  (`require_once($globalsBag->getString('OE_SITE_DIR') . "/config.php");`)
  loads per-site config.
- Root redirector `index.php:24` also does
  `require_once "sites/$site_id/sqlconf.php";`.

So: **one code tree + one Apache + N sites, each with its own DB and
its own on-disk directory.** No shared session, no shared data — the
tenancy boundary is site_id → (OE_SITE_DIR, $dbase).

---

## 7. Session isolation

### Cookie name is NOT suffixed by site

`src/Common/Session/SessionUtil.php:81-88` defines cookie names as
**constants**, not site-derived:

```php
public const CORE_SESSION_ID   = "OpenEMR";
public const OAUTH_SESSION_ID  = 'authserverOpenEMR';
public const API_SESSION_ID    = 'apiOpenEMR';
public const PORTAL_SESSION_ID = 'PortalOpenEMR';
public const SETUP_SESSION_ID  = 'setupOpenEMR';
```

`src/Common/Session/SessionConfigurationBuilder.php:15-59` uses these
constants verbatim as PHP `session_name()`. **All sites on one host
share the same cookie name.**

Isolation between sites relies entirely on the runtime check at
`interface/globals.php:310-323`: if `$_GET['site']` disagrees with the
in-session `site_id`, `SessionUtil::clearSession()` wipes the session and
forces re-login. This means:

- A browser can only be logged into **one site at a time per cookie scope**.
- Two concurrent tabs on `site=A` and `site=B` will trample each other's
  session (each visit clears the other).
- The comment at `interface/globals.php:311` explicitly acknowledges this
  is a defense against "using session to penetrate other OpenEMR
  instances within same multisite module".

Session storage backend is shared PHP session (file / Redis /
`ReadAndCloseNativeSessionStorage`) with **no per-site partition** —
records are keyed only by PHP session id.

---

## 8. Multi-database vs multi-schema

Per-site `sqlconf.php` sets `$dbase` to a single MySQL database name
(`sites/default/sqlconf.php:10`). The connect chain
(`library/sql.inc.php:20` → `library/sqlconf.php:26`) opens one
connection to that database. There is **no table-prefix mechanism**, no
`tenant_id` column, and no cross-database joins in stock code.

Two sites = two MySQL databases (or two MySQL servers — `$host` and
`$port` are per-site too). Confirmed: **multi-database, not
multi-schema.**

---

## 9. Known limits (documented)

From tracked documentation and code comments:

- **Cronjobs**: "On multi-site installations, schedule one cron entry
  per site." (`Documentation/MIGRATION_GUIDE_CRONJOBS.md:43`). There is
  no scheduler that iterates sites automatically — each site needs its
  own crontab line invoking `bin/console --site=<name> <command>`.
- **PHI cross-contamination on provisioning**:
  `Documentation/help_files/openemr_installation_help.php:737-747` warns
  that copying a source site with patient data will leak PHI into the
  new site; the recommendation is to keep `default/` empty and always
  clone from it.
- **`$allow_multisite_setup` is a security hole**: `setup.php:49-51`
  and `Documentation/help_files/openemr_installation_help.php:749-751`
  both instruct that the flag must be flipped back to `false` (or
  `setup.php` deleted) after each provisioning.
- **CLI hack**: `library/MedEx/MedEx.php:31` literally hard-codes
  `$_SERVER['HTTP_HOST'] = 'default'; //change for multi-site` — a
  known pain point for CLI/service code that predates `bin/console`.
- **Shared Apache config**: no per-site vhost, TLS cert, or HTTP header
  tuning is documented in-tree. UNKNOWN — requires product-owner input
  whether ops has this externally.
- **Session collision**: not called out in docs; only in the code
  comment at `interface/globals.php:311`. Two tabs on different sites
  fight over one cookie.
- **Site-count ceiling**: no documented limit in the repo. UNKNOWN —
  practical ceiling is filesystem, MySQL connection pool, and the
  N-cronjobs-per-N-sites operational burden.
- **eye_mag CLI**: `interface/forms/eye_mag/taskman.php:28-29` also
  hard-codes `HTTP_HOST = 'default'`.

---

## 10. SaaS tenancy candidate models — findings

### Model (A): DB-per-tenant using native multisite + automation layer

**What already works out of the box:**

| Capability | Evidence |
|---|---|
| Per-tenant DB (own host/port/user/dbname) | `sites/default/sqlconf.php:6-18` |
| Per-tenant filesystem for all PHI (documents, EDI, ERA, faxcache, letter templates, logos) | §5, `interface/globals.php:332`, ~40 `OE_SITE_DIR` call sites |
| Per-tenant branding (logo, favicon, portal images) | `sites/default/images/logos/…`; `interface/globals.php:688-692` |
| Per-tenant `config.php` for feature knobs | `interface/globals.php:649` |
| Per-tenant LBF plugin code | `sites/default/LBF/*.plugin.php`; `interface/patient_file/transaction/add_transaction.php:39` |
| Per-tenant printing / fax / statement templates | `sites/default/{faxcover.txt,statement.inc.php,referral_template.html}` |
| Session cross-tenant defense (forces re-login on `?site=` switch) | `interface/globals.php:310-323` |
| CLI tenant scoping | `bin/console --site=<name>` (`bin/console:31-47`) |
| Programmable install / clone | `library/classes/Installer.class.php:721-1819`; `contrib/util/installScripts/InstallerAuto.php:19-65` |
| URL-parameter site selection (`?site=X`) natively supported | `interface/globals.php:279-281`, `index.php:12-14` |
| Host-header site selection (subdomain-style) — **implemented for unauth'd path only** | `interface/globals.php:295-297`; `index.php:14` |

**What would need to be built:**

- **Tenant provisioning API** — wrap `Installer` / `InstallerAuto.php` in an HTTP or queue-driven service; `setup.php` in its current form is a security-toggle wizard, not an API.
- **Subdomain-based site resolution for authenticated traffic** — today `HTTP_HOST` is only consulted when `$ignoreAuth` is true (`interface/globals.php:284-297`). Auth'd requests without `?site=` throw `MissingSiteIdException`. Extending host-header resolution to auth'd traffic is a small patch in `globals.php` but must be paired with cookie-scope changes (§7) or every login will thrash sessions.
- **Cookie-per-site or path-scoped cookies** — `SessionUtil::CORE_SESSION_ID = "OpenEMR"` is a global constant (`src/Common/Session/SessionUtil.php:81`). To allow concurrent tenant logins from one browser, either suffix the cookie with `site_id` or scope each tenant to its own subdomain and rely on the host to partition cookies.
- **Per-site background workers** — no in-tree scheduler; `MIGRATION_GUIDE_CRONJOBS.md:43` requires one cron line per tenant. A shared worker that iterates `sites/*/sqlconf.php` and dispatches per-tenant jobs would need to be built.
- **Shared central identity** — nothing today; every tenant has its own `users` table and admin. Would need an IdP layer above the site-selection step.
- **Per-site backup automation** — no cron/backup hooks in tree; each tenant has independent DB + directory (§5), so backup is `mysqldump $dbase` + `tar sites/<site>/`.
- **Cross-tenant analytics aggregation** — every query today runs against a single connection; no code aggregates across sites. Would need an out-of-band ETL that walks `sites/*/sqlconf.php`.
- **Ops per-site hardening** — TLS cert lifecycle per subdomain, per-site rate limiting, per-site Apache log rotation — all out of scope for the codebase.
- **PHI-safe cloning** — `openemr_installation_help.php:737-747` documents the current foot-gun; a supported provisioner must clone from a curated blank template, not a live site.

### Model (B): Shared-DB with `tenant_id` column

**What would break:**

- **Schema**: no table in `sql/database.sql` has a `tenant_id` column. Every migration in `sql/` and every Doctrine migration in `src/` assumes single-DB context.
- **Every query**: `sqlStatement(…)` alone appears **1,875 times across 453 files** in `library/`, `src/`, and `interface/` (measured 2026-07-20 by `Select-String` over the three trees). None of them scopes by tenant — they can't, because the connection itself *is* the tenant boundary today (`library/sql.inc.php:20`; `library/sqlconf.php:17-26`). Add `QueryUtils::…` call sites and the actual scope is larger; 1,875 is a lower bound on `sqlStatement` alone.
- **ACL / groups**: `groups`, `users`, `acl_*` tables are per-DB; no tenant partition (`interface/usergroup/*.php` throughout).
- **File paths**: all PHI files live under `sites/<site>/documents/…` (§5). Moving to shared DB would still leave file paths tied to a per-tenant directory *or* require a full rewrite of ~40 `OE_SITE_DIR` call sites (see grep in §1).
- **Sessions**: `site_id` in the session is the tenant key (`interface/globals.php:326`). Would need to remain, but semantics diverge from "which DB do I connect to" (today's meaning) to "which `tenant_id` do I filter by" (new meaning) — a subtle refactor affecting every query.
- **Modules**: `interface/modules/custom_modules/oe-module-*/` each carry their own DB access, most going straight to `sqlStatement`/`QueryUtils`. Every module would need tenant-scoping too.
- **Auth**: OAuth2 clients, refresh tokens, MFA registrations all live in per-site DBs (`login_mfa_registrations`, `oauth_*` tables). Consolidating means schema migration + ID reconciliation across all existing tenants.

**Scope estimate (lower bound)**: ~1,875 `sqlStatement(` call sites across
453 files (§ measurement above). Adding `QueryUtils::` and Doctrine-based
access, plus the ~40 `OE_SITE_DIR` file-path sites in `interface/`,
brings the surface easily above 2,000 code sites. UNKNOWN —
`QueryUtils::` occurrence count was not measured to a single number; the
grep tool's JSON output exceeded 64 KiB on ripgrep runs. Order of
magnitude is confirmed: thousands, not hundreds.

_Resolved 2026-08-19, see `docs/discovery/openemr-decision-evidence/21-recommended-decision-updates.md`
(Q11) and `15-security-compliance-code-evidence.md §5.1`: the full data-access surface
was counted at **6,785** call sites (`sqlStatement(` 2,025 + `QueryUtils::` 1,653 +
`sqlQuery(` 1,454 + `sqlFetchArray(` 1,354 + `sqlInsert(` 251 + Doctrine DBAL 48) —
roughly **3.6× larger** than the 1,875-site estimate this section used to size Model B.
The conclusion (Model A / DB-per-tenant) is unchanged and, if anything, reinforced; only
the magnitude of what Model B would cost to abandon changes._

---

## 11. URL / subdomain routing feasibility

**Where `$_GET['site']` is consumed**:

- `interface/globals.php:280-281` — first read; wins over session.
- `index.php:12-14` — root redirector's first read.
- `bin/console:31-47` — CLI reads `--site=` and assigns to `$_GET['site']`.
- `library/sqlconf.php:17-26` — resolves `OE_SITE_DIR` from the resulting session/globals state.

**Where `$_SERVER['HTTP_HOST']` is consumed for site selection**:

- `interface/globals.php:295-297` — the only host-header check, gated on `$ignoreAuth || $ignoreAuth_onsite_portal` (line 284). If `sites/<HTTP_HOST>/` exists on disk, that host is used; otherwise `"default"`.
- `index.php:14` — root redirector uses the same `is_dir("sites/" . $HTTP_HOST)` check.

**Feasibility**:

- **Pure-PHP subdomain routing** is already there in skeletal form. To make `tenant1.example.com` map to `sites/tenant1/` for **all** requests (not just unauth'd), the change is roughly: in `interface/globals.php:284-297`, lift the host-header fallback out of the `$ignoreAuth` branch and apply it whenever the session is empty. No Apache rewrite required.
- **Caveats**:
  - The regex allowlist `/^[A-Za-z0-9\-.]+$/` (`interface/globals.php:301`) already permits `.`, so `tenant1.example.com` passes character validation but `is_dir("sites/tenant1.example.com/")` requires you to *name the on-disk directory* `tenant1.example.com`, or add a mapping layer (e.g. parse the leftmost DNS label). A mapping layer is the sensible path — take `explode('.', $_SERVER['HTTP_HOST'])[0]` and look that up in `sites/`.
  - Cookie scoping must move to per-subdomain (i.e. **don't** set the cookie domain to `.example.com`) or the session-collision defence at `interface/globals.php:310-323` will still clear the session on every cross-tenant redirect. `SessionConfiguration.cookiePath` is currently set to `$webRoot . '/'` (`SessionConfigurationBuilder.php:19,48`); a subdomain deployment naturally partitions by host so cookies segregate without code changes **if** the cookie's `Domain` attribute is left unset.
  - `HTTP_X_FORWARDED_HOST` is already respected by `$ResolveServerHost` (`interface/globals.php:212`) — front-end proxies can inject the tenant host.
- **Apache/nginx rewrite is NOT strictly required** — the code has enough hooks. A rewrite would only be needed if the deployment wanted `example.com/tenant1/...` path-based routing without a `?site=tenant1` query string; PHP's site-resolver doesn't parse `PATH_INFO` for site.

---

## Summary (5 lines)

1. OpenEMR's built-in "multisite" is **DB-per-tenant + directory-per-tenant** wired through a single `$_GET['site']`/session value; one code tree, N tenants (`interface/globals.php:277-335`; `sites/default/sqlconf.php:6-24`).
2. Provisioning today is a **manual `setup.php` wizard** gated behind `$allow_multisite_setup = false` (a security kill-switch), with an unattended CLI counterpart in `contrib/util/installScripts/InstallerAuto.php`.
3. Session cookies are **not** per-site — `SessionUtil::CORE_SESSION_ID = "OpenEMR"` is a global constant; cross-tenant navigation in the same browser force-clears the session (`interface/globals.php:310-323`).
4. Model (A) — DB-per-tenant + automation layer — is the low-risk path: the runtime already supports it; work is provisioning API, subdomain routing (small `globals.php` patch), per-site workers, backups, and identity federation.
5. Model (B) — shared-DB with `tenant_id` — is a full-schema-and-query rewrite: ~1,875 `sqlStatement(` call sites across 453 files is a **lower bound** on the query-scoping surface, not counting `QueryUtils::`, Doctrine, and ~40 file-path sites tied to `OE_SITE_DIR`.

## UNKNOWNs

- **Site-count ceiling**: no documented limit. Practical bounds are filesystem, MySQL connection pool, and N-cronjobs-per-N-sites ops burden. UNKNOWN — requires product-owner input.
- **Per-site Apache/TLS/observability config**: not tracked in-tree. UNKNOWN — requires product-owner input (may exist in an infra repo).
- **Precise scope of shared-DB refactor**: `sqlStatement(` count of 1,875 is measured; `QueryUtils::` count could not be measured in a single ripgrep run (JSON record size limit). UNKNOWN — needs a scripted count over the whole tree.
- **Cross-tenant analytics requirement**: is aggregation a Day-1 need or Day-N? UNKNOWN — requires product-owner input.
- **Central identity requirement**: SSO / IdP across tenants — mentioned as a "would need to be built" but no product intent captured. UNKNOWN — requires product-owner input.
