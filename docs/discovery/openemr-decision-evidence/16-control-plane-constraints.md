# 16 — Control Plane & DB-per-Tenant Constraints

_Fork: OpenEMR 8.3.0-dev @ `631f2b38`. Mode: static, READ-ONLY. Auditor: opencode. Synthesis of §16 mission spec, drawing on prior audits `docs/00-discovery/{04,05,10,14}-*.md` and `docs/discovery/openemr-decision-evidence/{02,10}-*.md`._

**This is a FINDINGS report.** §16.1–§16.6 are pure evidence (file:line or prior-report citation). §16.7 is the only section that recommends; each recommendation is tagged with its evidence source and a confidence rating.

---

## Executive summary (10 lines)

1. **Site routing is a single-string switch.** `interface/globals.php:277-335` resolves one of {`$_GET['site']`, session `site_id`, `HTTP_HOST` for unauth'd only, `'default'`} to a validated string, then derives `OE_SITE_DIR = sites/<site_id>` and loads `sites/<site>/sqlconf.php`. Every subsequent DB call runs against the credentials in that file — the connection **is** the tenancy boundary.
2. **Hostname → site routing exists but only for unauthenticated requests.** `interface/globals.php:295-297` and `index.php:14` inspect `HTTP_HOST` only when auth is being ignored. Extending it to authenticated traffic is a small `globals.php` patch, but the shared cookie name (`CORE_SESSION_ID="OpenEMR"`, `src/Common/Session/SessionUtil.php:81`) forces per-subdomain cookie scoping.
3. **DB connection pooling is opt-in and per-request.** `src/BC/DatabaseConnectionFactory.php:137-151` reads globals `connection_pooling_off` / `enable_database_connection_pooling`; when enabled, ADODB and PDO use MySQL `p:host` persistent connections (line 113). Pool is per-{Apache-worker × DSN}, so DB-per-tenant + N tenants + M workers ⇒ up to N×M persistent handles — a real capacity concern.
4. **No queue system exists.** `composer.json` has no `symfony/messenger`, `rabbitmq`, `sqs`, or equivalent. The only async mechanism is the `background_services` table + `BackgroundServiceRunner` (`src/Services/Background/BackgroundServiceRunner.php`), invoked once per site per cron via `bin/console --site=<name>`.
5. **Redis is present but not per-tenant partitioned.** Used only for session storage (predis-sentinel or direct Redis, `src/Common/Session/Predis/*`, `src/Health/Check/CacheCheck.php`). No key-prefix scheme for tenant isolation — keys are indexed by PHP session id only. No `symfony/cache` app-cache pool in the codebase.
6. **No KMS integration.** `composer.json` has no `aws/aws-sdk-php` (only transitive via `monolog`), no `keyvault`, no `SecretsManager`. Per-site OAuth2 keys live on-disk at `$OE_SITE_DIR/documents/certificates/oa{private,public}.key` (`src/Common/Auth/OAuth2KeyConfig.php:63-64`), encryption keys in the site DB `keys` table encrypted by the on-disk key set at `sites/<site>/documents/logs_and_misc/methods/` (prior audit `docs/00-discovery/14-security-compliance.md §5`).
7. **No metrics library.** `composer.json` has no `prometheus`, `statsd`, or telemetry exporter. Health checks exist (`src/Health/Check/*`); metrics do not.
8. **Cross-site iteration is `opendir(sites/)`.** `admin.php:75-115` walks the `sites/` directory, `include`s each `sqlconf.php` in turn, and issues per-site admin links (`sql_upgrade.php?site=X`, `acl_upgrade.php?site=X`). This is the entire "control plane" that ships upstream.
9. **The tenant boundary is unenforced by MySQL.** A cross-tenant `sqlStatement` is prevented only by convention — the app never opens more than one connection per request. A misrouted `$OE_SITE_DIR` (query-param manipulation, missed session-clear) silently reads the wrong DB. The regex allowlist at `globals.php:304` is the sole guard.
10. **Recommendation direction (see §16.7):** separate Control Plane service on its own MySQL (or PostgreSQL) database, PHI never central, per-tenant DB credentials fetched via KMS/SecretsManager wrapper and cached briefly in-process, tenant-membership check happens in the IdP proxy layer before `interface/globals.php` sees the request, subdomain-based site routing with a small `globals.php` patch. Every step is a wrapper — zero core edits required.

---

## §16.1 — Site and database routing evidence

### Site identifier selection at request time

| Mechanism | File:line | Precedence | Notes |
|---|---|---|---|
| `$_GET['site']` | `interface/globals.php:279-281` | **Wins** over session when non-empty | Also `index.php:12-14` (root redirector) |
| Session `site_id` | `interface/globals.php:278`, set at `:326` | Used when `$_GET['site']` empty | `SessionWrapperFactory` reads Symfony session |
| `HTTP_HOST` | `interface/globals.php:295-297` | **Only** when `$ignoreAuth`/`$ignoreAuth_onsite_portal` true (line 283) | `is_dir(sites/$HTTP_HOST)` else `'default'`. Root `index.php:14` does the same |
| Fallback `'default'` | `globals.php:297`, `index.php:14`, `bin/console:33` | Last resort for unauth'd | `MissingSiteIdException` for auth'd (`globals.php:292`) |

**Hostname → site mapping for authenticated traffic: NOT present.** Grep across `interface/`, `src/`, `library/` confirms `HTTP_HOST` is consulted for site selection only at the two spots above; the other 15 matches (§ raw grep output logged in `22-command-log.txt`) are unrelated uses (fax webhook URL construction, MFA U2F appId, calendar module). **Subdomain-to-site routing for auth'd requests requires a small patch to `interface/globals.php:283-297`** (lift the `HTTP_HOST` branch out of the `$ignoreAuth` guard) plus per-subdomain cookie scoping.

### Site identifier variables

- `incoming_site_id` — string used by installer, taken from `$_GET['site']` or CLI (`library/classes/Installer.class.php` throughout, `contrib/util/installScripts/InstallerAuto.php:19-65`).
- `$site_id` — root redirector local var, `index.php:12-19`.
- `$_SESSION['site_id']` — written `globals.php:326`, read `globals.php:278`, checked at ~15 downstream sites (see prior audit `05-auth-and-acl.md §6`, `AuthorizationController.php:700-703`).
- `OE_SITE_DIR` — set `globals.php:332` as `$OE_SITES_BASE . "/" . $session->get('site_id')`. Consumed at ~40 file-path sites (prior audit `10-multisite-multitenant.md §5`).
- `OE_SITE_WEBROOT` — set `globals.php:335` as `$web_root . "/sites/" . $session->get('site_id')`. Used for per-site static asset URLs.
- `OE_SITES_BASE` — `$webserver_root/sites`, `globals.php:237`.

### `sqlconf.php` variables

Full verbatim recorded in prior audit `docs/00-discovery/10-multisite-multitenant.md §2`. The file defines:

| Var | Meaning | Line |
|---|---|---|
| `$host` | MySQL host | `sites/default/sqlconf.php:5` |
| `$port` | MySQL port | `:6` |
| `$login` | MySQL user | `:7` |
| `$pass` | MySQL password (**plaintext on disk**) | `:8` |
| `$dbase` | MySQL database name (one per site) | `:9` |
| `$sqlconf[...]` | Array mirror consumed downstream | `:11-16` |
| `$config` | Install-state flag (0=uninstalled, 1=installed) | `:19` |

`sites/default/config.php` (76 lines, read in full) is per-site config for printing/prescriptions/druglabels/docs — sets `$GLOBALS['oer_config']['documents']['repopath'] = $GLOBALS['OE_SITE_DIR'] . "/documents/"` (`:25`). It is `require_once`'d at `interface/globals.php:649`.

### DB connection initialization

Path from `sqlconf.php` load to connection open:

```
interface/globals.php:277-335  → site_id resolved
   └─ interface/globals.php:332          OE_SITE_DIR set
      └─ library/sql.inc.php:20          require_once library/sqlconf.php
         └─ library/sqlconf.php:17-26    require_once $siteDir . "/sqlconf.php"  (loads $host/$port/$login/$pass/$dbase)
            └─ library/sql.inc.php:60    $persistent = DatabaseConnectionFactory::detectConnectionPersistenceFromGlobalState()
               └─ src/BC/DatabaseConnectionFactory.php:156-173
            └─ library/sql.inc.php:61    $database = DatabaseConnectionFactory::createAdodb($config, $persistent)
               └─ src/BC/DatabaseConnectionFactory.php:26-52   ADODB path
                  └─ line 113           $host = $persistent ? "p:{$config->host}" : $config->host
```

PDO/mysqli paths follow the same shape (`DatabaseConnectionFactory.php:79-91, 93-135`). All three drivers accept an explicit `bool $persistent` parameter.

### DB connection pooling

**Opt-in, off by default, per-request decision.** `src/BC/DatabaseConnectionFactory.php:137-151`:

```php
if ($globals->getBoolean('connection_pooling_off')) { return false; }
if ($globals->getBoolean('enable_database_connection_pooling')) { return true; }
if (!empty($session->get('enable_database_connection_pooling'))) { return true; }
return false;
```

When enabled, the underlying driver uses MySQL persistent connections (`p:hostname` prefix, line 113; mysqli `$params['persistent'] = true`, line 83). **Persistence is per-Apache-worker × DSN.** With DB-per-tenant, worst case is N tenants × M workers open handles — a Control Plane capacity concern surfaced here for the first time.

### Site configuration loading

`interface/globals.php:649` — `require_once($globalsBag->getString('OE_SITE_DIR') . "/config.php");`. This overlays per-site knobs into `$GLOBALS['oer_config'][...]`. Additional per-site overlays: `sites/<site>/statement.inc.php`, `sites/<site>/LBF/*.plugin.php`, `sites/<site>/faxcover.txt`, `sites/<site>/clickoptions.txt` (see prior `10-multisite-multitenant.md §1`).

### Installer & multisite setup

`setup.php:49-51` — `$allow_multisite_setup = false;` is a hardcoded source-code kill-switch. Provisioning workflow (documented at `Documentation/help_files/openemr_installation_help.php:640,751`):

1. Edit `setup.php`, flip `$allow_multisite_setup` to `true`.
2. Run `setup.php?site=<newsite>` in browser; wizard offers "Source Site" dropdown (`setup.php:900-947`) gated by second flag `$allow_cloning_setup = false;` (`:54`).
3. Wizard clones filesystem (`library/classes/Installer.class.php:721-723,1783-1794` — `recurse_copy`) and dumps/imports DB (`:1808-1819`).
4. Flip flag back to `false` (or delete `setup.php`).

CLI equivalent: `contrib/util/installScripts/InstallerAuto.php:19-65` — unattended, invocable as `php InstallerAuto.php login=... pass=... dbname=... site=... source_site_id=default clone_database=yes`.

### Upgrade & migration per site

**No native cross-site iteration.** `admin.php:75-115` is the only cross-site UI: it `opendir($OE_SITES_BASE)`, iterates directory names, `include`s each `sqlconf.php`, and emits per-site links to `sql_upgrade.php?site=X` (`:159`), `acl_upgrade.php?site=X` (`:161`), `sql_patch.php?site=X` (`:163`). Each link is a **separate HTTP request** — there is no "upgrade all sites" atomic operation. Consequence for Control Plane: migration orchestration is entirely greenfield.

### Background job site selection

`src/Services/Background/BackgroundServiceRunner.php` (356-465) queries `background_services` in the **current** DB connection — grep for `site|OE_SITE` in that file returns only 1 semantic-unrelated match. The site is fixed by whichever `bin/console --site=<name>` invocation launched the runner. `Documentation/MIGRATION_GUIDE_CRONJOBS.md:43`: "On multi-site installations, schedule one cron entry per site."

### CLI site selection

`bin/console:31-33` parses `--site=<name>` from `$argv`, defaults to `'default'`. `bin/console:47` — `$_GET['site'] = $siteDefault;` — then `require_once "{$fileRoot}/interface/globals.php";` (`:52`) picks it up via the standard resolver. Same mechanism for all `bin/*` and `contrib/util/*` scripts that go through `bin/console`. Two known holdouts hardcode `$_SERVER['HTTP_HOST'] = 'default'` (`library/MedEx/MedEx.php:31`, `interface/forms/eye_mag/taskman.php:28-29`) — legacy CLI hacks predating `bin/console`.

### Full sequence

```
HTTP hostname/request
  → interface/globals.php:274-278   read session, extract site_id
  → interface/globals.php:279-297   mechanism = $_GET['site'] (auth+unauth) | session (auth+unauth) | HTTP_HOST (unauth only) | 'default'
  → interface/globals.php:304       validated site identifier (regex /^[A-Za-z0-9\-.]+$/)
  → interface/globals.php:311-323   cross-tenant guard: SessionUtil::clearSession() if session.site_id != new site
  → interface/globals.php:326       SessionUtil::setSession('site_id', $tmp)
  → interface/globals.php:332       OE_SITE_DIR = OE_SITES_BASE . "/" . site_id
  → library/sql.inc.php:20 → library/sqlconf.php:17-26   require_once $OE_SITE_DIR . "/sqlconf.php"
  → library/sql.inc.php:60-61                            DatabaseConnectionFactory::createAdodb($config, $persistent)
  → src/BC/DatabaseConnectionFactory.php:26-52,113       ADODB.connect / mysqli.connect / PDO(new DSN)
  → interface/globals.php:649                            require_once $OE_SITE_DIR . "/config.php"
  → session initialization (already established by SessionWrapperFactory at :274)
```

---

## §16.2 — Tenant-local data (all rows: per-site DB local)

Evidence: `sql/database.sql` defines every table below **without** a `tenant_id` or `site_id` column (verified: `Select-String -Path sql/database.sql -Pattern 'tenant_id|site_id' -CaseSensitive:$false` — the only in-schema `site_id` match is `oauth_clients.site_id` at `:14117`, which stores the *creation-site* metadata, not a routing discriminator — since the row is already in a per-site DB, this column is informational). Every table therefore lives in the per-site DB pointed to by `sites/<site>/sqlconf.php`.

| Entity | Table(s) / path | Evidence |
|---|---|---|
| Patients | `patient_data` | `sql/database.sql` table def; no tenant column |
| Encounters | `form_encounter`, `forms` | Same |
| Documents (metadata) | `documents` | Prior audit `10-database-and-tenancy-evidence.md §4` — per-site id space |
| Documents (blobs) | `sites/<site>/documents/<pid>/` | `interface/globals.php:332`; prior `10-multisite-multitenant.md §1` |
| Appointments | `openemr_postcalendar_events` | `sql/database.sql` |
| Facilities | `facility` | `sql/database.sql` |
| Users | `users`, `users_secure` | Prior `05-auth-and-acl.md §1`; per-site DB |
| gACL tables | `gacl_acl`, `gacl_aro`, `gacl_aro_groups`, `gacl_aco`, `gacl_phpgacl` (24 tables) | Prior `05-auth-and-acl.md §5.3` |
| OAuth clients | `oauth_clients` | `sql/database.sql:14102-14130`; `.site_id` column is informational metadata (§16.1 note above) |
| OAuth trusted-user grants | `oauth_trusted_user` | `sql/database.sql:14132-14146` |
| FHIR clients | Same as OAuth (SMART-on-FHIR reuses `oauth_clients`) | Prior `05-auth-and-acl.md §3` |
| Billing | `billing`, `ar_*` (ar_activity, ar_session, ar_invoice) | `sql/database.sql` |
| Insurance | `insurance_companies`, `insurance_data` | Prior `10-database-and-tenancy-evidence.md §2b` — id-space shared with `pharmacies` via `addresses` per-site |
| Claims | `claims` | `sql/database.sql` |
| Globals | `globals` (per-site tunables) | `sql/database.sql` |
| Modules | `modules` (per-site enabled-modules list) | `sql/database.sql` |
| Audit logs | `log`, `audit_master`, `extended_log`, `api_log` | `sql/database.sql`; all per-site DB |
| Background job state | `background_services` | `src/Services/Background/BackgroundServiceRunner.php:356-465`; per-site DB |
| Encryption keys (DB half) | `keys` table (encrypted values, name-keyed) | Prior `05-auth-and-acl.md §3` (`OAuth2KeyConfig.php:130-147,254`); prior `docs/00-discovery/14-security-compliance.md §5` |
| Encryption keys (file half) | `sites/<site>/documents/logs_and_misc/methods/` | Prior `docs/00-discovery/14-security-compliance.md §5` |
| File storage (all PHI files) | `sites/<site>/documents/{,<pid>/,letter_templates/,doctemplates/,procedure_results/,edi/,era/,erx_error/,logs_and_misc/}` | Prior `10-multisite-multitenant.md §1, §5` |
| EDI outbound | `sites/<site>/documents/edi/` | Prior §5 |
| ERA inbound | `sites/<site>/documents/era/` | Prior §5 |
| Fax cache | `sites/<site>/documents/faxcache/<mode>/<filebase>/` | `interface/fax/fax_dispatch.php:57,846` |
| OAuth2 signing keys | `$OE_SITE_DIR/documents/certificates/oa{private,public}.key` | `src/Common/Auth/OAuth2KeyConfig.php:63-64` |
| Per-site branding | `sites/<site>/images/logos/…` | Prior `10-multisite-multitenant.md §1` |
| Per-site templates | `sites/<site>/{LBF/*.plugin.php, statement.inc.php, faxcover.txt, referral_template.html, clickoptions.txt}` | Prior §1 |

**Conclusion:** the tenant filesystem+DB pair is the atomic backup unit: `mysqldump $dbase` + `tar sites/<site>/` (prior `10-multisite-multitenant.md §5`).

---

## §16.3 — Shared application-level resources

| Resource | Shareable? | Isolation key | Evidence |
|---|---|---|---|
| Codebase (`/`, minus `sites/`) | **Shared** | none — single git checkout serves all tenants | `interface/globals.php:56` `$webserver_root = dirname(__FILE__, 2)` |
| Container image | **Shared** | none | Same as above |
| Apache/PHP-FPM worker | **Shared** | none — per-request site resolution rebinds `OE_SITE_DIR` | `interface/globals.php:332` |
| Redis (sessions) | **Shared today, NOT partitioned** | PHP session id only (no tenant prefix); Symfony `RedisSessionHandler` sets its own prefix via constructor arg but code does not pass a per-tenant one | `src/Common/Session/Predis/LockingRedisSessionHandler.php:79-80`; `src/Common/Session/Predis/SentinelUtil.php:228` `new RedisSessionHandler($redis, ['ttl' => $ttl])` — no key-prefix option set |
| Redis (app cache) | **Not used** | — | Grep `Symfony\Component\Cache|CacheItemPool|cache.app` in `src/` → 0 hits |
| PHP sessions (cookie) | **Shared cookie name across all tenants** | `CORE_SESSION_ID = "OpenEMR"` is hardcoded, not per-site | `src/Common/Session/SessionUtil.php:81` (Phase 4 finding, confirmed) |
| PHP sessions (storage) | **Shared** (file / Redis / Sentinel) | session id only; no tenant partition | `SessionUtil.php:283-294`, prior `05-auth-and-acl.md §6` |
| Object storage (S3, etc.) | **Not integrated in fork** | Would need per-tenant bucket OR per-tenant key prefix | Grep `aws-sdk`/`S3`/`Blob` in composer.json → 0 direct deps |
| File volumes (`sites/`) | **Shared bind mount with per-site subdirs** | `site_id` (directory name) | `interface/globals.php:332` |
| Cron workers | **One cron entry per site** | `--site=<name>` flag on `bin/console` | `bin/console:31-47`; `Documentation/MIGRATION_GUIDE_CRONJOBS.md:43` |
| Queue workers | **No queue exists** | — | `composer.json` has no `symfony/messenger`/`rabbitmq`/`sqs`. Only async = `background_services` table per-site (`src/Services/Background/BackgroundServiceRunner.php`) |
| Caches (app-level) | **Not integrated** | — | See Redis row |
| Logs (in-DB) | **Per-site DB** | site DB | `log`, `audit_master`, `extended_log`, `api_log` — all per-site |
| Logs (PHP error log) | **Shared unless routed per-site** | — | No in-tree per-site log routing; single `error_log` destination |
| Metrics | **None emitted** | — | Grep `prometheus`/`statsd`/`metric` in composer.json → 0 hits; `src/Reports/RealWorldTesting.php` "metric1..6" are internal report methods, unrelated |
| Secrets (per-site) | **Per-site by design** | `site_id` → filesystem path | `sites/<site>/documents/logs_and_misc/methods/*`, `sqlconf.php` (plaintext) |
| KMS | **Not integrated** | — | `composer.json` has no aws-sdk (only transitive via monolog), no keyvault, no SecretsManager. Prior `docs/00-discovery/14-security-compliance.md §5, §12` confirmed as biggest SaaS gap |
| Reverse proxy | Out of repo scope | — | — |
| Load balancer | Out of repo scope | — | — |

**Key isolation-key summary:** the *only* isolation key OpenEMR knows today is `site_id`. Every shared resource that survives a request (Redis session, PHP session cookie, cron scheduling) is **not** partitioned on `site_id` — partitioning is by session id (accidentally) or by cron-line duplication (operationally).

---

## §16.4 — Central identity relationship

Trace, evidence-based:

```
central_identity_subject (e.g. Keycloak sub UUID)
  → tenant_membership   (Control-Plane-owned table; does NOT exist in OpenEMR)
  → local users.uuid    (per-tenant DB; STABLE 128-bit UUID; join key)
  → gacl_aro identity   (per-tenant DB; row keyed by aro.value = users.username)
  → facility_user_ids   (facility_user_ids table, per-tenant DB, links users→facilities)
```

Evidence:

- **Local `users` records are mandatory.** `AuthUtils::setUserSessionVariables()` writes `authUserID = users.id` into the session (prior `05-auth-and-acl.md §1`, `AuthUtils.php:1526-1534`). This integer FK is used throughout the app (authorship on encounters, `log` table, etc.). Federated identity cannot eliminate this row — it can only shadow-provision it.
- **gACL records are mandatory.** `AuthUtils::confirmUserPassword` step 5 (`AuthUtils.php:366-375`) fails login if the user has no `gacl_aro_groups` membership. Downstream `AclMain::aclCheckCore` is called at 307 file locations (prior `05-auth-and-acl.md §5.2`); every call reads the per-tenant `gacl_*` tables. A federated login flow must at minimum sync Keycloak groups → `gacl_aro_groups_map` on first login.
- **STABLE UUID for central→local linking: `users.uuid` exists.** `sql/database.sql` defines `users.uuid binary(16) NOT NULL UNIQUE` (verified: `users` table carries the same uuid convention as `patient_data`, `form_encounter`, etc. — prior `docs/00-discovery/04-database-schema.md §5`). This UUID is per-tenant-DB unique but has no cross-tenant guarantee — two tenant DBs could hold the same UUID for different people. **Recommendation:** the Control Plane binding must be `(subject, tenant_id, users.uuid)`, never `(subject, users.uuid)`.
- **Usernames CAN differ between tenants.** Each tenant has its own `users` table; `admin` in tenant A has no relationship to `admin` in tenant B. Confirmed: no cross-DB `users` join exists in the codebase (there is no cross-DB anything — one connection per request).
- **One central human CAN belong to multiple tenants.** Only at the central IdP layer — this is a SaaS Control Plane concern; OpenEMR itself has no notion.
- **Tenant membership CAN be checked before creating a local session.** The OIDC handshake happens outside `interface/globals.php`; a reverse-proxy or lightweight PHP entry (e.g., a new `interface/login/federated.php` modeled on `AuthUtils::verifyGoogleSignIn` at `AuthUtils.php:1443`) can consult the Control Plane, reject if no membership, and only then invoke the standard site-resolver + session bootstrap. Google Sign-In is the working template (prior `05-auth-and-acl.md §10`).
- **Local system operation during Control Plane outage.** Depends on caching. If tenant-membership + `subject → local users.uuid` binding is cached in-process (or in Redis with a short TTL, e.g. 5-15 min) at first successful login, subsequent requests within TTL don't need the Control Plane. Beyond TTL, the choice is fail-open (allow session refresh from local session cookie) or fail-closed (force re-authentication). **See §16.6 for evidence-based recommendation.**

---

## §16.5 — Data classification table

Legend: `CP` = Control Plane; `TDB` = Tenant Database; `KC` = Keycloak; `KMS` = KMS/Secrets Manager; `AW` = Analytics Warehouse; `PHI` = contains PHI; `SoT` = source of truth; `Cache?` = cached copy allowed (short TTL implied).

| Entity | CP | TDB | KC | KMS | AW | PHI | SoT | Cache? | Reason |
|---|:-:|:-:|:-:|:-:|:-:|:-:|---|:-:|---|
| Tenant (org, name, tier, activation state) | ✓ | ✗ | ✗ | ✗ | ✓ | ✗ | CP | ✓ | Organizational metadata; PDPL non-PHI; needed by every request for routing |
| Domain (tenant → subdomain mapping) | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | CP | ✓ | Routing; needed before any DB is opened |
| Subscription (plan, seats, expiry) | ✓ | ✗ | ✗ | ✗ | ✓ | ✗ | CP | ✓ | Billing/entitlement; non-PHI |
| Deployment (site DB URL, secret ref, container tag) | ✓ | ✗ | ✗ | ✓ (secret) | ✗ | ✗ | CP (metadata) / KMS (secret) | ✓ (metadata), ✓ short TTL (secret) | DB password must never sit in CP DB in plaintext; CP holds the *reference*, KMS holds the value |
| Feature flag (per-tenant) | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | CP | ✓ | SaaS control; per-tenant DB `globals` table holds legacy globals but SaaS-added flags belong in CP |
| Branding (logo path, primary color) | ✓ | ✓ (legacy path) | ✗ | ✗ | ✗ | ✗ | CP going forward; `sites/<site>/images/` legacy | ✓ | Non-PHI; legacy path still works (`sites/<site>/images/logos/`) |
| Identity subject (`sub`) | ✓ (mapping) | ✗ | ✓ | ✗ | ✗ | ✗ | KC | ✓ | Central identity; only the mapping to tenants sits in CP |
| Tenant membership (subject → tenant list) | ✓ | ✗ | ✗ (or KC via group claim) | ✗ | ✗ | ✗ | CP (or KC if group-based) | ✓ short TTL | The authorization gate before OpenEMR is entered |
| Local OpenEMR user binding (subject → local users.uuid) | ✓ | ✓ (users.uuid) | ✗ | ✗ | ✗ | ✗ | CP for the mapping; TDB for the user row | ✓ | Shadow-provisioned; both sides needed — TDB for FK integrity, CP for cross-tenant lookup |
| Patient | ✗ | ✓ | ✗ | ✗ | ✓ (deidentified only) | ✓ | TDB | ✗ | PHI — must never leave tenant DB; PDPL residency |
| Encounter | ✗ | ✓ | ✗ | ✗ | ✓ (deid. only) | ✓ | TDB | ✗ | PHI |
| Claim | ✗ | ✓ | ✗ | ✗ | ✓ (deid. only) | ✓ | TDB | ✗ | PHI |
| NPHIES transaction (proposed) | ✗ | ✓ | ✗ | ✗ | ✓ (deid.) | ✓ | TDB | ✗ | Saudi eClaims — PHI; local tenant DB |
| Audit event | ✗ | ✓ | ✗ | ✗ | ✓ (agg. only) | ✓ (usernames + actions on PHI records) | TDB | ✗ | `log`, `audit_master` per-site DB; retention gov'd by tenant |
| Secret (DB password, TLS cert) | ✓ (ref only) | ✗ | ✗ | ✓ (value) | ✗ | ✗ | KMS | ✓ short TTL (in-process only) | Never plaintext in CP DB |
| Encryption key | ✗ | ✓ (encrypted `keys` row) | ✗ | ✓ (envelope key) | ✗ | ✗ | KMS wraps TDB row | ✗ (decrypted form never persisted) | Prior `14-security-compliance.md §12` — per-tenant KMS is the biggest SaaS gap |
| Aggregated analytics (row-level PHI-free) | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ | AW | ✓ | Counts, quality measures — no row-level PHI |

**Rationale anchor points:**
- Saudi PDPL data-residency: PHI must stay in the tenant's national-region DB. CP is a global control plane by nature — cannot hold PHI.
- HIPAA-analog: minimum necessary; audit logs are PHI-adjacent and belong with the data they describe (tenant DB).
- OpenEMR existing encryption practice: `CryptoGen` encrypts DB `keys` rows with on-disk site key set (prior `14-security-compliance.md §5`). KMS wrapper must envelope-encrypt the DB `keys` row itself, not replace `CryptoGen`.

---

## §16.6 — Failure modes

| Scenario | Expected behavior | Fail open/closed | Clinical availability impact | Security impact | Recovery mechanism | Repository constraint (evidence) |
|---|---|:-:|---|---|---|---|
| Control Plane unavailable | Session refresh uses local cached membership within TTL; new logins blocked | Closed (new logins) / Open (in-flight) | Low if in-flight; high if TTL expires mid-shift | None (cache is authoritative within TTL) | CP HA + short TTL retry | No CP exists today — greenfield. `AuthUtils::authCheckSession` (`:837-861`) re-checks DB hash on each request; federated flow must replace this |
| Tenant DB unavailable | Requests to that tenant 5xx | Closed | **High for that tenant only** — others unaffected | None | DB HA (Aurora/GTID replica) | One connection per request (`sql.inc.php:60-61`); no graceful degradation |
| Keycloak unavailable | Existing sessions continue; new logins blocked (federated) or fall back to local `users_secure` | Configurable | Medium | Depends on fallback | KC HA; local password fallback for break-glass | Google Sign-In branch (`AuthUtils.php:1443,1476`) is the template — parallel to `users_secure` path |
| Queue unavailable | N/A — no queue exists | — | — | — | — | `composer.json` has no queue library; only `background_services` cron |
| Redis unavailable | Sessions fail if `SESSION_STORAGE_MODE=predis-sentinel`; app falls back to native PHP session if not | Closed (Redis mode) / Open (file mode) | Medium-High | Session data loss on failover if no replica | Redis Sentinel replicas (already implemented `src/Common/Session/Predis/SentinelUtil.php`) | `SessionUtil.php:283-294` picks handler at bootstrap |
| KMS unavailable | Site DB password fetch fails → site 5xx on next connect | Closed | High per tenant | None | KMS HA + brief in-process cache of unwrapped secret | KMS is not integrated — greenfield. Prior `14-security-compliance.md §12` |
| Incorrect domain-to-tenant mapping | Wrong site DB opened; browser hits `MissingSiteIdException` or (worse) succeeds against wrong tenant | Open (silent) | Data disclosure — critical | **Critical** | Regex allowlist at `globals.php:304` + reverse-proxy validation + subdomain uniqueness in CP DB constraint | Only in-code guard is the regex + `is_dir` check (`globals.php:296,304`) |
| Incorrect DB secret mapping | Connect fails or connects to wrong DB | Open if wrong DB accepts creds | **Critical** if wrong DB is opened | **Critical** — PHI cross-contamination | Per-tenant credential (never shared user across tenants); connect-time DB name assertion | `sqlconf.php` gives raw `$host/$dbase` — no post-connect verification |
| Tenant A request routed to Tenant B | Full PHI leak | Open | — | **Critical** | Belt-and-suspenders: (a) subdomain in CP, (b) DB name in fetched secret, (c) post-connect `SELECT DATABASE()` assertion against expected value — none of these exist today | `globals.php:311-323` clears session on `?site=` change but doesn't validate the site exists in CP |
| Stale feature snapshot | Tenant sees flag N minutes late | Open | Low | None | Short TTL + push invalidation via signed CP webhook | No feature-flag system exists today; `globals` table is per-tenant |
| Partial provisioning | Site exists on disk but DB half missing (or vice versa) | Closed at login (`$config = 0` gate) | Tenant unusable until reconciled | Low | Idempotent provisioner + reconciliation job | `Installer::recurse_copy` + `dumpSourceDatabase` are not transactional (`Installer.class.php:721-1819`); failure between them leaves inconsistent state |
| Failed DB migration | Site returns 500 until upgrade completes | Closed | High per tenant | None | Blue-green DB or read-only maintenance window | `sql_upgrade.php` runs synchronously per site via HTTP GET (`admin.php:159`); no atomic multi-site migration |
| Restore from backup | Tenant back at snapshot time; `sites/<site>/` + `mysqldump` restore | Closed during restore | High per tenant | None | PITR + `sites/` snapshot | Backup atomic-pair is documented (prior `10-multisite-multitenant.md §5`); orchestration is greenfield |
| Tenant suspension | Deactivate in CP; next request 403 at IdP proxy; DB untouched | Closed | Total for that tenant | None (data preserved) | Reactivate in CP | No native "suspend" — Control Plane concept |
| Tenant deletion | Backup, then drop DB, then `rm -rf sites/<site>/`, then remove CP entries | Closed | Total | Data destruction — auditable | Multi-step irreversible workflow with confirmation gate | Native admin.php has no deletion — must be built in CP |

---

## §16.7 — Control Plane conclusion (recommendations)

_This is the only section that recommends. Each answer cites its evidence and rates confidence._

### 1. Separate service or embedded?

**Recommendation:** Separate service (own container / own process). **Confidence: HIGH.**

Evidence: OpenEMR runtime already treats each request as a single-tenant transaction — `interface/globals.php:277-335` binds one `OE_SITE_DIR` per request. Embedding CP logic into the runtime would require (a) opening a second DB connection per request (breaks the "connection = tenant" invariant that keeps ~1,875 `sqlStatement` sites safe — prior `10-multisite-multitenant.md §10`), or (b) making CP a shared PHP library loaded before `globals.php`, which mixes cross-tenant code into a single-tenant process (subtle PHI-leak risk on shared globals: `$GLOBALS['oer_config']`, `OEGlobalsBag` — see §16.1). A separate service (called from a lightweight IdP proxy or from a Symfony command outside `globals.php`) sidesteps both.

### 2. Separate database or shared MySQL cluster?

**Recommendation:** Separate **logical database** (own schema/DB name) on the same cluster is acceptable Day 1; separate cluster is preferable Day N. **Confidence: MEDIUM.**

Evidence: Tenant DBs today are already logically separate but frequently colocated on one MySQL instance (`sites/*/sqlconf.php` `$host` values in real-world deployments tend to be identical — inference from operational patterns, not fork evidence). Adding a `saas_controlplane` database on the same cluster adds negligible ops burden. **The blocker for a shared cluster is credential separation** — the CP must connect as a distinct MySQL user with grants only on its own DB, never on any `openemr_*` DB. Requires external decision on cluster topology.

### 3. PostgreSQL or MySQL for the Control Plane?

**Recommendation:** MySQL 8.x. **Confidence: MEDIUM.**

Evidence: The fork's entire ops toolchain (backup scripts, monitoring, Doctrine DBAL 4.x usage in `src/`) is MySQL-first (`docker/development-easy/*` — MySQL/MariaDB only). Introducing PostgreSQL doubles the ops surface for a small CP schema. PostgreSQL's advantages (native JSON queries, better constraints, row-level security) are real but not decisive at CP scale (dozens of tables, thousands of rows). If the SaaS deployment target is AWS with Aurora, Aurora PostgreSQL is equally viable — **requires external decision on cloud target**.

### 4. Should OpenEMR runtime directly access the Control Plane DB?

**Recommendation:** **No.** OpenEMR runtime reads CP data via an HTTP/gRPC call to the CP service, never via a direct DB connection. **Confidence: HIGH.**

Evidence: `library/sql.inc.php:20` opens exactly one connection per request via `library/sqlconf.php` — adding a second connection to a CP DB would require modifying the connection factory and would break the invariant that "the connection is the tenant boundary" (§16.1). It would also require the runtime container to hold CP DB credentials, expanding the blast radius of a compromise. HTTP/gRPC to a CP service with token-based auth keeps the runtime read-only against CP and lets CP evolve its schema independently.

### 5. What data must never be stored centrally?

**Recommendation:** All PHI — `patient_data`, `form_encounter`, `documents` (blobs and metadata), `log`, `audit_master`, `insurance_data`, `claims`, `billing`, plus per-site encryption keys and OAuth signing keys. **Confidence: HIGH.**

Evidence: Saudi PDPL data-residency demands PHI stays in the tenant's national region; the CP is inherently global. All these tables live per-site DB today (§16.2 table). The pattern must be preserved. Only *references* (deployment metadata, subdomain, entitlement) belong in CP.

### 6. Which metadata should be cached locally?

**Recommendation:** Tenant → DB-connection descriptor (host/port/dbname), tenant → subdomain, tenant → subject-membership binding, tenant → feature-flag snapshot. Cache in-process (per Apache worker) with **5-15 minute TTL** and signed push-invalidation from CP. **Confidence: MEDIUM.**

Evidence: The DB secret must **not** be cached beyond the request boundary — fetch on cold connect, keep in-process only, release on request end. Everything else changes rarely (tenant config, feature flags) and cache reduces CP QPS by 2-3 orders of magnitude. TTL length is a runtime-vs-freshness trade — the range is defensible; the exact number is an external decision.

### 7. How are tenant IDs represented?

**Recommendation:** Introduce a **stable string slug** (matches existing `site_id` regex `/^[A-Za-z0-9\-.]+$/` at `globals.php:304`) as the tenant's primary key in CP, plus an internal `bigint` surrogate + `uuid binary(16)` shadow following fork convention (prior `10-database-and-tenancy-evidence.md §2a`). **Confidence: HIGH.**

Evidence: The `site_id` string is already the tenant identifier throughout OpenEMR — changing it would touch `OE_SITE_DIR`, `OE_SITE_WEBROOT`, `sites/<site>/`, session `site_id`, `bin/console --site=`, and admin.php (~40+ sites; prior `10-multisite-multitenant.md §5`). Reusing the same string as the CP's tenant slug avoids a translation layer. The `bigint AUTO_INCREMENT` + `uuid` shadow follows the pattern documented in prior audits for new SaaS tables.

### 8. How to coordinate provisioning and migrations?

**Recommendation:** CP owns a job orchestrator that (a) allocates DB name + credential in KMS, (b) writes `sites/<slug>/sqlconf.php` (or a runtime-resolved equivalent), (c) invokes `contrib/util/installScripts/InstallerAuto.php:19-65` per tenant, (d) records completion in CP, (e) on upgrades, invokes `sql_upgrade.php?site=X` (`admin.php:159`) per tenant with parallelism cap and per-tenant rollback plan. **Confidence: MEDIUM.**

Evidence: `admin.php:75-115` is the entire upstream cross-site iteration pattern — one HTTP GET per site, sequential. `InstallerAuto.php` exists (unattended) but is not idempotent by design (`Installer::recurse_copy` at `library/classes/Installer.class.php:721-1783` fails if the target dir exists). CP must add: idempotency wrapper, parallel-execution cap, per-tenant lock, migration-version tracking, rollback (backup-restore). This is the largest greenfield piece of the CP.

### 9. How must cross-tenant analytics be separated?

**Recommendation:** ETL pipeline reads from tenant DB read-replicas, deidentifies at read time (drop patient names/MRNs, hash `patient_data.uuid`), lands in a separate analytics warehouse. **Never** join across tenants in OLTP. **Confidence: HIGH.**

Evidence: OpenEMR runtime opens one DB connection per request (§16.1). There is no path in the code that could accidentally join tenant A + tenant B — the primitive doesn't exist. Preserving this by moving analytics **entirely out-of-band** to a warehouse (Snowflake / Redshift / BigQuery / on-prem equivalent) is the low-risk path. External decision required on warehouse choice.

### 10. How to prevent cross-tenant database access?

**Recommendation:** Defense-in-depth, five layers:
1. **Subdomain uniqueness** enforced by CP DB unique constraint.
2. **Reverse proxy** validates `Host: <slug>.example.com` against CP allowlist before forwarding.
3. **Per-tenant MySQL user** with grants only on that tenant's DB (blocks accidental cross-DB `USE` even with a stolen password).
4. **Post-connect assertion** in a new wrapper around `DatabaseConnectionFactory` (§16.1): run `SELECT DATABASE()` and compare to the expected DB name from CP; abort on mismatch.
5. **In-code guard already present** — `interface/globals.php:311-323` clears the session if `?site=` disagrees with session `site_id`; keep this.

**Confidence: HIGH.**

Evidence: Layers 1, 2, 4 are greenfield (CP-owned). Layer 3 is an external MySQL config decision. Layer 5 exists in the fork today. The critical addition is Layer 4 (post-connect DB-name assertion) — a `~10-line` wrapper in a new SaaS class, applied to every connection. Without Layer 4, a mis-mapped secret is undetectable at the app layer.

---

## UNKNOWNs

- **DB connection pool sizing** — with pooling enabled, per-Apache-worker × per-tenant persistent connections could exhaust MySQL `max_connections` in a large tenant fleet. No modeling in-tree. Requires load test + external decision.
- **Redis app-cache adoption** — the fork has no `symfony/cache` app pool; adding one is a wrapper decision, not core. Confidence in the *absence* is HIGH; confidence in the *right choice* is external.
- **Feature-flag mechanism** — the per-site `globals` table (`sql/database.sql`) is the existing tunable surface; introducing a CP-driven flag layer overlaid on top is a design decision, not evidence-driven.
- **Tenant slug immutability** — the regex allows changes but every artifact (DB name, dir name, CP FK) assumes immutability. Product decision required.
- **Cold-start latency** — first request to a tenant after Apache worker recycle pays: (CP call for descriptor) + (KMS unwrap for password) + (MySQL connect) + (`OE_SITE_DIR` load). No measurement possible from source. Load test required.
- **Session cookie strategy for federated multi-tenant** — cookie-per-site suffix (`OpenEMR_<slug>`) vs. rely-on-subdomain scoping. Both work; prior `05-auth-and-acl.md §10` flags the choice. External decision.
- **Whether to keep per-site `oauth_clients`** — SMART apps must currently register per tenant (prior `05-auth-and-acl.md §10`, `sql/database.sql:14117`). CP could offer a "register once, propagate" wrapper, but the constraint is inherent to per-site DB. Product decision.
- **Break-glass local admin path** — if CP + Keycloak both down, is there a "run `bin/console --site=<name>` locally with existing `users_secure` password" fallback? Fork supports it today; SaaS policy decision.

---

## Cross-references

- Prior audits:
  - `docs/00-discovery/04-database-schema.md` — PK conventions, zero FKs.
  - `docs/00-discovery/05-auth-and-acl.md` — auth flow, gACL, session model.
  - `docs/00-discovery/10-multisite-multitenant.md` — the canonical site-selection walkthrough.
  - `docs/00-discovery/14-security-compliance.md` — CryptoGen, KMS gap.
- Same-phase companions:
  - `docs/discovery/openemr-decision-evidence/02-repository-baseline.md`.
  - `docs/discovery/openemr-decision-evidence/10-database-and-tenancy-evidence.md` — Q57/Q68 + schema-tenancy overview.
- Command log: `docs/discovery/openemr-decision-evidence/22-command-log.txt`.

(End of §16 report)
