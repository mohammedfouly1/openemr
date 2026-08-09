# 05 — Auth & ACL

Discovery notes for OpenEMR 8.3.0-dev @ `D:\OpenEmr`. All claims are cited by
`file:line`; where the repo is silent the value is marked `UNKNOWN — requires
product-owner input`. Read-only pass; no code touched.

Prior context assumed: `CORE_SESSION_ID = "OpenEMR"` is a single global session
name (`src/Common/Session/SessionUtil.php:81`), i.e. cross-tenant session
isolation is NOT provided by the session layer.

---

## 1. Login flow

The login screen is `interface/login/login.php`. It sets an `App` marker cookie
(`login.php:49`), forces `$ignoreAuth = true` and pulls in `interface/globals.php`
(`login.php:51-54`), then renders Twig. The form submits back through the
standard OpenEMR frameset via `interface/main/main_screen.php` (not shown here),
which calls into `library/auth.inc.php` for credential handling. All password
checking is delegated to `OpenEMR\Common\Auth\AuthUtils` running in `'login'`
mode (`src/Common/Auth/AuthUtils.php:76-77`).

### `AuthUtils::confirmUserPassword()` — order of operations

| Step | File:Line | Purpose |
|---|---|---|
| IP-block check + counter | `AuthUtils.php:294-314` | Reject if IP over threshold |
| Empty user/pass guard | `AuthUtils.php:317-326` | With counter increment |
| `users` row lookup (BINARY cmp) | `AuthUtils.php:329-349` | Requires `active = 1` |
| Group check | `AuthUtils.php:352-363` | `UserService::getAuthGroupForUser` |
| ACL group check | `AuthUtils.php:366-375` | `AclExtended::aclGetGroupTitles` — user must be in a `gacl_aro_groups` row |
| Load `users_secure` hash | `AuthUtils.php:378-391` | |
| Per-user login-fail counter | `AuthUtils.php:394-408` | |
| LDAP branch | `AuthUtils.php:411-422` | if `useActiveDirectory()` true |
| Standard bcrypt/argon path | `AuthUtils.php:426-447` | `AuthHash::hashValid` + `passwordVerify` |
| Rehash-on-login | `AuthUtils.php:450-458` | `password_needs_rehash` semantics |
| Password-expiration check | `AuthUtils.php:461-470` | Skipped for LDAP |
| Reset counters | `AuthUtils.php:476-478` | |
| Session bootstrap | `AuthUtils.php:488` → `setUserSessionVariables` | See below |

### Password hashing

`OpenEMR\Common\Auth\AuthHash` (`src/Common/Auth/AuthHash.php`). Algorithm is
chosen at Admin→Globals→Security → `gbl_auth_hash_algo`
(`AuthHash.php:36`). Supported values:

| Value | Backend | Notes |
|---|---|---|
| `DEFAULT` | Resolves to whatever `PASSWORD_DEFAULT` is on the PHP build (currently `BCRYPT`) | `AuthHash.php:52-72` |
| `BCRYPT` | `password_hash($p, PASSWORD_BCRYPT, ['cost'=>…])` | `AuthHash.php:111-116` |
| `ARGON2I` | `password_hash(..., PASSWORD_ARGON2I, …)` | `AuthHash.php:87-110` |
| `ARGON2ID` | `password_hash(..., PASSWORD_ARGON2ID, …)` | `AuthHash.php:87-110` |
| `SHA512HASH` | `crypt($p, '$6$rounds=N$…$')` — bespoke, not through `password_hash` | `AuthHash.php:138-143`, `passwordVerify` `AuthHash.php:199-201` |

Legacy `$2a$05$` bcrypts (pre-5.0) are still accepted via a 21-char salt shim
that runs `crypt()` manually (`AuthHash.php:216-224`).

### `users_secure` (the credential table)

`sql/database.sql:9872-9890` — one row per user:

```
id, username, password, last_update_password, last_update,
password_history1..4, last_challenge_response, login_work_area,
total_login_fail_counter, login_fail_counter, last_login_fail,
auto_block_emailed
```

No separate salt column (bcrypt/argon2 embed the salt in the hash string).
`password_history1..4` gives 5-generation reuse prevention
(`AuthUtils.php:726-750`).

### Brute-force throttling

Two layers, both in `AuthUtils`:

- **Per-username counter**: `checkLoginFailedCounter` / `incrementLoginFailedCounter`
  (`AuthUtils.php:1163-1191`, `1260-1283`) writes to
  `users_secure.login_fail_counter` and `last_login_fail`. On block, admin
  email via `notifyUserBlock` (`AuthUtils.php:1381-1395`).
- **Per-IP counter**: `checkIpLoginFailedCounter` /
  `incrementIpLoginFailedCounter` (`AuthUtils.php:1194-1236`, `1285-1313`).
  Backing table `ip_tracking` — see admin UI `library/ajax/login_counter_ip_tracker.php`.
  Supports manual block, auto-block, notify-admin, skip-timing-attack flags
  (`AuthUtils.php:1315-1358`).

Timing-attack prevention: a dummy bcrypt is stored in
`globals.hidden_auth_dummy_hash` and verified for non-existent users
(`AuthUtils.php:94-113`, `preventTimingAttack` `AuthUtils.php:1406`).

### Session bootstrap on successful login

`AuthUtils::setUserSessionVariables()` (`AuthUtils.php:1526-1534`) writes:

| Session key | Value |
|---|---|
| `authUser` | username |
| `authPass` | the password **hash** (used later by `authCheckSession` to invalidate sessions when the DB hash changes — `AuthUtils.php:837-861`) |
| `authUserID` | `users.id` |
| `authProvider` | ACL group name (from `AclExtended::aclGetGroupTitles`) |

`site_id`, `launch`, `csrf_private_key` etc. are set elsewhere in the same
lifecycle (`AuthorizationController.php:590,1216-1220`; CSRF init at
`src/Common/Csrf/CsrfUtils.php:35-42`).

---

## 2. MFA / TOTP

`OpenEMR\Common\Auth\MfaUtils` (`src/Common/Auth/MfaUtils.php`) loads
registrations from `login_mfa_registrations` (`MfaUtils.php:41-45`):

```sql
CREATE TABLE `login_mfa_registrations` (
  user_id, name, last_challenge, method, var1, var2
)                                              -- sql/database.sql:14070-14078
```

`method` is a free-text string; the reader accepts `'U2F'` or `'TOTP'`
(`MfaUtils.php:43,47-56`). Constants: `TOTP`, `U2F`
(`MfaUtils.php:23-24`). WebAuthn/FIDO2 is **not** wired in — only the legacy
Google U2F flow is present (see `library/js/u2f-api.js`; composer requires
`yubico/u2flib-server`). Email-based MFA is **not** implemented (no
`method='EMAIL'` branch; `library/globals.inc.php:2113-2124` only defines idle
timeouts, no MFA-by-email option).

`isMfaRequired()` is per-user: it returns `true` iff the user has at least one
row in `login_mfa_registrations` (`MfaUtils.php:75-78`). There is **no**
`gbl_force_mfa`-style global; enrollment is opt-in per user. `UNKNOWN — requires
product-owner input`: is there an admin control to force MFA org-wide? (grep
finds none.)

Google Sign-In (Google Workspace OIDC) is a parallel path: 
`AuthUtils::verifyGoogleSignIn` (`AuthUtils.php:1443`), toggled by globals
`google_signin_enabled` / `google_signin_client_id`
(`library/globals.inc.php:2251,2258`; `library/auth.inc.php:36,53-59`).
Users are matched to `users.google_signin_email` (`AuthUtils.php:1476`).

---

## 3. OAuth2 server

### Entry

`oauth2/authorize.php` (32 lines) — thin shim: constructs `HttpRestRequest` and
runs `ApiApplication` (`oauth2/authorize.php:22-26`). Routing lives in
`src/RestControllers/Subscriber/OAuth2AuthorizationListener.php`; controller is
`src/RestControllers/AuthorizationController.php` (1916 lines).

### Grant types

All grants are OpenEMR-forked subclasses of `league/oauth2-server`, under
`src/Common/Auth/OpenIDConnect/Grant/`:

| Grant type | Class | Enabled | File:Line |
|---|---|---|---|
| `authorization_code` | `CustomAuthCodeGrant` | Always when `grantType == 'authorization_code'` | `AuthorizationController.php:709-723` |
| `refresh_token` | `CustomRefreshTokenGrant` | Always | `AuthorizationController.php:725-733` |
| `password` | `CustomPasswordGrant` | Only if global `oauth_password_grant` truthy | `AuthorizationController.php:736-747` |
| `client_credentials` | `CustomClientCredentialsGrant` | Always | `AuthorizationController.php:748-761` |
| `implicit` | — | **Not enabled** | (no `ImplicitGrant` reference in tree) |

TTLs: access token `PT1H`, refresh token `P3M` (3 months, "minimum per ONC"),
auth code `PT1M`, client-credentials access `PT300S`
(`AuthorizationController.php:104-111`).

### Client registration

**Dynamic Client Registration is the primary path** (RFC 7591 / SMART):
`AuthorizationController::clientRegistration()`
(`AuthorizationController.php:249-299`) accepts JSON POST with the standard
metadata keys (`redirect_uris`, `token_endpoint_auth_method`, `jwks`,
`jwks_uri`, `initiate_login_uri`, `scope`, `grant_types`, `subject_type`,
`dsi_type`, …). Routed by
`src/RestControllers/Subscriber/OAuth2AuthorizationListener.php:169`.

Supported token-endpoint auth methods:
`client_secret_basic`, `client_secret_post`, `private_key_jwt`
(`AuthorizationController.php:274`).

There is also a UI for managing already-registered clients:
`src/FHIR/SMART/ClientAdminController.php` (revoke tokens/refresh/trusted-user,
token tools) — but new-client creation is not done through it; it manages
existing rows only. `UNKNOWN — requires product-owner input`: whether a
tenant-admin UI for "generate a new confidential client" is expected on top of
DCR.

### Token storage

DB layer is bespoke (not the vanilla `oauth-server` migrations). Relevant
tables in `sql/database.sql`:

| Table | Purpose | File:Line |
|---|---|---|
| `oauth_clients` | client registry (client_id, secret, jwks, redirect_uri, grant_types, scope, is_enabled, is_confidential, `skip_ehr_launch_authorization_flow`, `dsi_type`, `site_id`) | `sql/database.sql:14102-14130` |
| `oauth_trusted_user` | Per-(user, client) grant record — persists scope consent, `session_cache`, `code`, `grant_type` | `sql/database.sql:14132-14146` |

Notably absent as tables: `oauth_access_tokens`, `oauth_refresh_tokens`,
`oauth_authorization_codes`, `oauth_scopes`. Access/refresh tokens are **JWTs**
signed with the site RSA key; the repositories under
`src/Common/Auth/OpenIDConnect/Repositories/` (`AccessTokenRepository`,
`RefreshTokenRepository`, `AuthCodeRepository`, `ScopeRepository`) implement
the league interfaces on top of this JWT-based model. Revocation is tracked via
`oauth_trusted_user.session_cache` and `revoke_date` on `oauth_clients`.

`oauth_clients.site_id` (`sql/database.sql:14117`) means client registrations
are per-tenant — significant for §10.

### JWT signing key

`OpenEMR\Common\Auth\OAuth2KeyConfig` (`src/Common/Auth/OAuth2KeyConfig.php`).

| Item | Location | File:Line |
|---|---|---|
| Private key file | `$OE_SITE_DIR/documents/certificates/oaprivate.key` | `OAuth2KeyConfig.php:63` |
| Public key file | `$OE_SITE_DIR/documents/certificates/oapublic.key` | `OAuth2KeyConfig.php:64` |
| Passphrase for private key | Encrypted, `keys.value` WHERE `name='oauth2passphrase'` | `OAuth2KeyConfig.php:130-147, 254` |
| Encryption key (for league server) | Encrypted, `keys.value` WHERE `name='oauth2key'` | `OAuth2KeyConfig.php:111-128, 253` |
| Algorithm | RSA-2048, `sha256` MD, `AES-256-CBC` on the encrypted PEM | `OAuth2KeyConfig.php:216-222` |
| Auto-generation | On first construction if any of the four pieces missing, `deleteKeys()` + regenerate all four atomically | `OAuth2KeyConfig.php:68-75, 194-256` |

The `keys` table (encrypted at rest via `CryptoInterface`) is thus a
per-tenant secret store gated by `$OE_SITE_DIR`. Keys are on the site's local
filesystem — **not** shared across cluster nodes unless the sites directory is
on shared storage.

---

## 4. SMART-on-FHIR

Central class: `src/FHIR/SMART/Capability.php`. Declared capabilities
(`Capability.php:27-129`):

| Capability constant | Value |
|---|---|
| `LAUNCH_EHR` | `launch-ehr` |
| `LAUNCH_STANDALONE` | `launch-standalone` |
| `CLIENT_PUBLIC` | `client-public` |
| `CLIENT_CONFIDENTIAL_SYMMETRIC` | `client-confidential-symmetric` |
| `CLIENT_CONFIDENTIAL_ASYMETRIC` | `client-confidential-asymmetric` (JWT auth) |
| `SSO_OPENID_CONNECTION` | `sso-openid-connect` |
| `CONTEXT_EHR_PATIENT` / `CONTEXT_EHR_ENCOUNTER` | EHR-launch patient/encounter context |
| `CONTEXT_STANDALONE_PATIENT` / `CONTEXT_STANDALONE_ENCOUNTER` | standalone launch context |
| `PERMISSION_ONLINE` / `PERMISSION_OFFLINE` | online_access / offline_access refresh |
| `PERMISSION_PATIENT` / `PERMISSION_USER` | `patient/*` and `user/*` scopes |
| `PERMISSION_V1` / `PERMISSION_V2` | SMART v1 + v2 scope syntaxes |
| `PERMISSION_AUTHORIZE_POST` | POST-based authorize |

Launch scope constants: `launch`, `launch/patient`
(`src/FHIR/SMART/SmartLaunchController.php:40-41`). Standalone-encounter is
declared as a capability but the runtime patient/encounter selectors only
cover patient (`SMARTAuthorizationController.php:59,61` — patient-select
routes; comment at `AuthorizationController.php:1844`: "for now we only handle
in-ehr launch for providers not patients").

**Supported launch flows:** EHR-launch (provider), standalone patient,
standalone provider. Standalone-encounter is advertised but not exercised.
Well-known: `/fhir/.well-known/smart-configuration` served by
`src/RestControllers/SMART/SMARTConfigurationController.php:35-41`.

Scope reservation set (non-resource scopes): `openid`, `fhirUser`,
`online_access`, `offline_access`, `launch`, `launch/patient`, `api:oemr`,
`api:fhir`, `api:port`
(`src/RestControllers/SMART/ScopePermissionParser.php:108`). The `api:*`
scopes are OpenEMR extensions gating access to the standard REST API
(`/apis/*/api`), FHIR API, and Patient Portal API from a single OAuth2
authorization.

Launch token: encrypted opaque blob via `CryptoGen`, contains patient uuid,
encounter uuid, appointment uuid, intent (`SMARTLaunchToken.php:19-33,132`).

---

## 5. ACL system

### Two layers

1. **Legacy php-gacl** at repo root `gacl/` (139 files — see doc 01).
2. **`OpenEMR\Common\Acl\AclMain`** (`src/Common/Acl/AclMain.php`) is a thin
   PHP wrapper. It **does not** implement an independent policy engine — it
   instantiates one `OpenEMR\Gacl\Gacl` object (static-cached) and calls
   `acl_query()` (`AclMain.php:132-138, 180-184`), then re-scans the returned
   ACL rows applying "deny takes precedence" (`AclMain.php:186-237`).

So the "modern" API is a shim; the truth is still in the `gacl_*` tables.

There is a *second, separate* module-ACL system for the Zend modules:
`AclMain::zhAclCheck()` (`AclMain.php:252-330`) uses tables
`module_acl_user_settings`, `module_acl_group_settings`, `module_acl_sections`
with precedence "user deny > user allow > group deny > group allow"
(`AclMain.php:324-329`). This is orthogonal to gacl and used only by installed
Zend modules.

### Call-site inventory

| API | Files with matches | Meaning |
|---|---|---|
| Legacy `acl_check(` bare function | 0 | Fully migrated in this fork |
| `AclMain::aclCheckCore` | 307 files across `src/`, `library/`, `interface/` | Single canonical entry point |

(Measured via `Select-String -Pattern … -List` over `*.php` in the three
roots.) The legacy procedural entry point has been eliminated repo-wide; only
`AclMain` remains. Prior-phase concern about "how much legacy acl_check(...)
usage" ⇒ **none**.

### gacl_* tables (24 tables)

All from `sql/database.sql`:

| Table | Line | Role |
|---|---|---|
| `gacl_acl` | 2487 | ACL rules (allow/deny + return value) |
| `gacl_acl_sections` | 2508 | ACL grouping |
| `gacl_acl_seq` | 2532 | Sequence table |
| `gacl_aco` | 2549 | Access Control Objects — "what is protected" (e.g. `patients`/`demo`) |
| `gacl_aco_map` | 2568 | ACO→ACL binding |
| `gacl_aco_sections` | 2582 | ACO sections (admin, patients, encounters, …) |
| `gacl_aco_sections_seq` | 2600 | |
| `gacl_aco_seq` | 2617 | |
| `gacl_aro` | 2635 | Access Request Objects — "who is asking" (users) |
| `gacl_aro_groups` | 2654 | User groups (Physicians, Front Office, …) |
| `gacl_aro_groups_id_seq` | 2674 | |
| `gacl_aro_groups_map` | 2691 | user↔group |
| `gacl_aro_map` | 2704 | ARO→ACL binding |
| `gacl_aro_sections` | 2718 | |
| `gacl_aro_sections_seq` | 2736 | |
| `gacl_aro_seq` | 2753 | |
| `gacl_axo` | 2770 | Access eXtension Objects — optional 3rd axis (unused by OpenEMR — "squads" only, per header comment `AclMain.php:64-66`) |
| `gacl_axo_groups` | 2789 | |
| `gacl_axo_groups_map` | 2809 | |
| `gacl_axo_map` | 2822 | |
| `gacl_axo_sections` | 2836 | |
| `gacl_groups_aro_map` | 2854 | |
| `gacl_groups_axo_map` | 2868 | |
| `gacl_phpgacl` | 2882 | Version stamp — holds `v_acl` |

Model: subject (user via `gacl_aro`) → group (`gacl_aro_groups_map` →
`gacl_aro_groups`) → ACL rule (`gacl_groups_aro_map` → `gacl_acl`) → object
(`gacl_aco_map` → `gacl_aco` in `gacl_aco_sections`). Rule carries `allow`
(bool) and a `return_value` (used for `write`/`wsome`/`addonly` sub-modes).

### Granularity — section-level only

Signature is `aclCheckCore(section, value, user='', return_value='')`
(`AclMain.php:166`). Both `section` and `value` are **fixed identifiers** from
the enumerated header list at `AclMain.php:11-108` (`admin/super`,
`patients/demo`, `patients/docs`, `encounters/notes_a`, `acct/bill`, …). The
`return_value` sub-flags (`view`/`write`/`wsome`/`addonly`) are also
enumerated.

**There is no per-record (per-patient, per-encounter) ACL.** Object-level
control is delegated to two ad-hoc mechanisms:

- **Sensitivity** on encounters: `AclMain.php:67-70` — `sensitivities/normal`,
  `sensitivities/high` ACOs, applied at query time.
- **Squads** (sports team use only): `AclMain.php:64-66`.
- **Patient-list assignment** in Zend "PatientFilter" module — separate
  `acl_upgrade.php` at
  `interface/modules/zend_modules/module/PatientFilter/acl/acl_upgrade.php`.

Fine-grained "user X can see patients A/B/C but not D" is **not** a first-class
concept in the core ACL engine.

### ACL versioning (`v_acl = 13`)

- Current version constant is read from `gacl_phpgacl` row (`v_acl` column).
- The upgrade runner is `acl_upgrade.php` (846 lines) at repo root.
- Model: `$acl_version` reads current DB version, and each `if ($acl_version <
  $upgrade_acl)` block adds new ACOs / groups / ACLs, then bumps
  `$acl_version = $upgrade_acl` (`acl_upgrade.php:14-40` template docs it).
- Zend modules ship their own additive `acl_upgrade.php` files (found: the
  PatientFilter one). `UNKNOWN — requires product-owner input`: whether custom
  fork changes to ACLs need to become version 14+.

---

## 6. Session handling

`src/Common/Session/SessionUtil.php` + `SessionConfigurationBuilder.php`
implement four distinct sessions, all bound to `HOST` (no per-tenant cookie
scoping):

| Session | Name | Path | httpOnly | SameSite | Secure | Where | File:Line |
|---|---|---|---|---|---|---|---|
| Core EHR | `OpenEMR` | `$webRoot/` | **false** (JS needs it for `restoreSession()`) | Strict | inherit | `SessionConfigurationBuilder.php:15-23` + `SessionUtil.php:8-14` |
| OAuth2 | `authserverOpenEMR` | `$webRoot/oauth2/` | true | **None** | **true** | `SessionConfigurationBuilder.php:25-33` |
| API | `apiOpenEMR` | `$webRoot/apis/` | true | Strict | **true** | `SessionConfigurationBuilder.php:35-42` |
| Portal | `PortalOpenEMR` | `$webRoot/` | true | Strict | inherit | `SessionConfigurationBuilder.php:44-51` |
| Setup | `setupOpenEMR` | `/` | true | Strict | inherit | `SessionConfigurationBuilder.php:53-59` |

`gc_maxlifetime` default: **14400 s (4 h)**
(`SessionUtil.php:96`, `SessionConfiguration.php:20`). This is a floor for the
PHP session; the *idle-logout* is a separate app-layer check (below).

Locking: the core session uses `read_and_close` (readOnly default true in
`forCore(..., $readOnly=true)`, `SessionConfigurationBuilder.php:21`) with
`withWritableSession()` re-opening for explicit writes
(`SessionUtil.php:179-198`). Comment `SessionUtil.php:37-46` documents this is
to avoid session locks under concurrent AJAX. Redis Sentinel storage backend
supported via `SESSION_STORAGE_MODE=predis-sentinel` env var
(`SessionUtil.php:283-294`).

### Lifecycle

The core session is started by `interface/globals.php` (invoked from every
authenticated page including `interface/login/login.php:54`).
`library/auth.inc.php` then runs on every request to enforce login state and
timeout (`auth.inc.php:75-118`).

### Idle timeout

- Global key: **`timeout`**, default **7200 s** (2 h)
  (`library/globals.inc.php:2113-2117`).
- Portal key: `portal_timeout`, default 1800 s
  (`library/globals.inc.php:2119-2123`).
- Enforced by `SessionTracker::isSessionExpired()` in
  `library/auth.inc.php:108-116`; each request refreshes the timer unless the
  URL sets `skip_timeout_reset=1` (used by background pollers, e.g.
  `library/ajax/dated_reminders_counter.php:5`).
- No `gbl_time_out_idle` key exists; the setting is simply `timeout`.

### Per-site session separation (confirmation)

`CORE_SESSION_ID = "OpenEMR"` is a hard-coded constant, unqualified by
`site_id` (`SessionUtil.php:81`). The cookie name is the same for every
tenant on the same host. Cross-tenant isolation therefore relies on:

- `cookie_path = $webRoot . '/'` (`SessionConfigurationBuilder.php:19`) —
  works only if tenants live under different URL paths, not different sites at
  the same webroot;
- `$_SESSION['site_id']` chosen at login and used by
  `AuthorizationController.php:700,703` etc. to scope DB access.

**Consequence**: A browser can hold only one active core-EHR login at a time
per host regardless of how many sites are configured. This confirms the
prior-phase Multisite finding (doc 10).

---

## 7. API auth alternatives (non-OAuth2)

Three strategies implement `IAuthorizationStrategy`
(`src/RestControllers/Authorization/`):

| Strategy | Class | When used | Credentials |
|---|---|---|---|
| Bearer JWT (OAuth2) | `BearerTokenAuthorizationStrategy` | All external REST/FHIR calls | `Authorization: Bearer <JWT>`, verified against local `oapublic.key` — `BearerTokenAuthorizationStrategy.php:144-184, 327` |
| Local session | `LocalApiAuthorizationController` | Same-origin AJAX from a logged-in browser session | Reuses core session; **requires CSRF token** with subject `'api'` — `LocalApiAuthorizationController.php:56` |
| Skip | `SkipAuthorizationStrategy` | Explicitly whitelisted routes only | e.g. `/fhir/metadata`, `/fhir/.well-known/smart-configuration`, `/api/version`, `/api/product` — `AuthorizationListener.php:95-99` |

**No HTTP Basic auth. No static API key.** Bearer is the only external
authentication path. Local-session strategy is not available cross-origin
because the core session cookie is SameSite=Strict.

---

## 8. CSRF (auth-adjacent piece only)

`src/Common/Csrf/CsrfUtils.php`. Strategy (`CsrfUtils.php:6-13`):

- On successful login, a 32-byte random `csrf_private_key` is stored in the
  session (`CsrfUtils.php:35-42`).
- Tokens are HMAC-SHA256 of a subject string (default `'default'`, or `'api'`
  for the internal API strategy) with the private key, truncated to 40 hex
  chars (`CsrfUtils.php:49-56`).
- Verification is constant-time via `hash_equals` (`CsrfUtils.php:96`).
- `checkCsrfInput()` (`CsrfUtils.php:71-86`) reads from `filter_input()` and
  either throws `CsrfInvalidException` or hard-403 exits, depending on the
  `$dieOnFail` flag (legacy call sites use the exit form).

**Scope**: all state-changing HTML form POSTs in the EHR are CSRF-protected
(subject `default`). Internal fetch/AJAX calls that use the
`LocalApiAuthorizationController` path require subject `api`
(`LocalApiAuthorizationController.php:56`). External OAuth2 API calls
(Bearer-authed) are exempt — CSRF is not applicable because they don't ride
on a cookie session.

Note: `csrf_private_key` is also used, unrelated to the CSRF machinery, to
seed the OAuth2 authorization flow session cache
(`AuthorizationController.php:1216-1220`).

---

## 9. External identity providers

### LDAP / Active Directory

Enabled by global `gbl_ldap_enabled` (`AuthUtils.php:874`). Implementation:
`AuthUtils::activeDirectoryValidation` (`AuthUtils.php:898-…`) using
`ldap_connect($GLOBALS['gbl_ldap_host'])` (`AuthUtils.php:909`). Supports
mutual-TLS via three PEM files under
`$OE_SITE_DIR/documents/certificates/ldap-{ca,cert,key}`
(`AuthUtils.php:914-931`). Users listed in `gbl_ldap_exclusions` bypass LDAP
and fall back to `users_secure` (`AuthUtils.php:880-885`). Password-expiry,
password-history, and password-strength checks are all bypassed for LDAP users
(`AuthUtils.php:460-470, 648, 656, 664`).

### Google Sign-In (as OIDC client)

The only OIDC-*client* integration. Uses `google/apiclient` (`Google_Client`).
Global toggle `google_signin_enabled` + `google_signin_client_id`
(`library/globals.inc.php:2251-2258`). Matches by `users.google_signin_email`
(`AuthUtils.php:1476`). Bypasses login counters and password expiry
(`AuthUtils.php:16-17` header).

### SAML

**None.** `grep -R "saml\|Keycloak\|oidc_client"` over `src/` returns zero
matches. No SAML SP, no generic OIDC client, no Keycloak integration in this
fork.

### Reverse-proxy trust (mod_auth_openidc, `REMOTE_USER`)

**None.** No code reads `REMOTE_USER`, `HTTP_X_AUTH_USER`, or trusts pre-auth
proxy headers. `UNKNOWN — requires product-owner input`: whether a
reverse-proxy trust mode is expected to be added.

---

## 10. Findings for a central-identity (Keycloak-style) SaaS story

**Findings only, no recommendation. The decision is Document 0's.**

### Reusable as-is

| Piece | Why it fits |
|-------|-------------|
| OAuth2/OIDC server (`AuthorizationController`, `league/oauth2-server`, `steverhoades/oauth2-openid-connect-server`) | The app is already a full OIDC provider with SMART-on-FHIR. A central IdP would federate *to* it or replace it, but the token-parsing / scope-enforcement pipeline (`BearerTokenAuthorizationStrategy.php`) is agnostic to who mints the tokens as long as the JWT verifies against a known key. |
| SMART capability & scope machinery (`src/FHIR/SMART/*`, `ScopePermissionParser`) | Standards-compliant; not tied to the local user store. |
| Bcrypt/Argon2 hashing (`AuthHash`) | Migrations off it are one-way trivial (password change), and Argon2ID is available today. Nothing blocks moving hashes to Keycloak. |
| Session-locking mitigation (`read_and_close` + `withWritableSession`) | Independent of identity source. |

### Needs a thin wrapper (small isolated change)

| Piece | Wrapper needed |
|-------|----------------|
| `library/auth.inc.php` login flow | Add a "trust upstream OIDC" branch analogous to `verifyGoogleSignIn` (`AuthUtils.php:1443`) that accepts a validated Keycloak ID token, matches by email or a new `users.external_sub` column, and calls `setUserSessionVariables()` unchanged. Google Sign-In is the working template. |
| LDAP config surface | Same shape as an OIDC-client config would be — `gbl_ldap_*` globals could be paralleled by `gbl_oidc_*`. |
| CSRF issuance | Independent of login mechanism; already works with Google Sign-In. |
| OAuth2 client-credentials backend flows | Can continue to use OpenEMR-issued JWTs for machine-to-machine even if human logins go through Keycloak. |

### Conflicts with a central IdP

| Piece | Nature of conflict |
|-------|--------------------|
| **`users` and `users_secure` tables are per-tenant DBs** (from Phase 10) | A central IdP wants ONE user identity; OpenEMR wants a per-tenant `users.id` for foreign keys everywhere (audit log, encounter authorship, `authProvider`, ACL group membership). SSO can populate a shadow row but cannot eliminate it — every tenant DB must still hold a user row for referential integrity. |
| **ACL grants live in `gacl_aro`/`gacl_aro_groups_map` in the tenant DB** | Keycloak roles/groups have no path into these tables. Either (a) sync Keycloak groups → gacl on login, (b) build a new `AclMain` strategy that reads Keycloak claims directly and short-circuits the gacl query, or (c) accept that authorization stays local while authentication centralises. The `AclMain::aclCheckCore` shim (`AclMain.php:166-238`) is the natural seam for (b) but changing it affects **307 call sites**. |
| **`CORE_SESSION_ID = "OpenEMR"`** (`SessionUtil.php:81`) | A single browser cookie name across all tenants means "log in once at Keycloak, get simultaneous access to tenants A and B in the same browser" is not achievable without renaming the cookie per site — which is exactly the change flagged in doc 10 for Multisite. Central identity magnifies the need. |
| **`authPass` (password hash) in session** (`AuthUtils.php:1531`, `AuthUtils::authCheckSession` `AuthUtils.php:837-861`) | The session-integrity check compares the *live* DB hash to the hash captured at login. For federated identity there is no password hash. This check must be replaced with something like a Keycloak session-state check or an `iat`/`sid` on the ID token, or gated off for federated users. |
| **OAuth2 signing keys are per-site** (`OAuth2KeyConfig.php:63-64`, on local filesystem) | Fine for single-node deploy; for a horizontally-scaled SaaS the site directory (including `documents/certificates/*.key` and `keys` table entries) must be on shared storage or migrated to a KMS. Keycloak-issued JWTs would remove *most* of the requirement, but OpenEMR still needs its own keys for the SMART app-facing server role. |
| **`oauth_clients` is per-tenant with `site_id` column** (`sql/database.sql:14117`) | A SMART app that wants to talk to tenant A and tenant B must register twice. This is inherent to the OpenEMR OAuth model, not fixable in a wrapper. |
| **MFA state in `login_mfa_registrations`** (`sql/database.sql:14070`) | If Keycloak handles MFA, this table becomes orphaned per-tenant duplication. Can be tolerated (dead code) or the MFA prompt (`MfaUtils::isMfaRequired`) shorted out for federated users. |
| **No SAML, no reverse-proxy trust, no generic OIDC client today** | Any central-IdP path is greenfield in this fork. Google Sign-In is the only working template. |

### Neutral / informational

- The 8 `oauth2_*` grant TTL constants and DCR endpoint are the standards
  contract that any SMART app expects; a federated model cannot change these
  without breaking third-party apps that already registered.
- The `AclMain::zhAclCheck` module-ACL system (`AclMain.php:252-330`) is a
  parallel, unrelated authorization mechanism used only by Zend modules. Any
  identity redesign must decide whether to unify it with gacl or leave it as a
  second track.

---

## UNKNOWNs — require product-owner input

1. Is there (or should there be) an admin control to **force MFA org-wide**?
   None found in `library/globals.inc.php`.
2. Beyond DCR, does the product need a **tenant-admin UI to hand-provision
   OAuth2 clients** (create/edit `oauth_clients` rows directly)? Present UI at
   `src/FHIR/SMART/ClientAdminController.php` only revokes/inspects; does not
   create.
3. Is **reverse-proxy trust** (`mod_auth_openidc`, `REMOTE_USER` header) a
   requirement for the SaaS deployment target?
4. For custom ACL changes in this fork, should a **new `v_acl = 14`** upgrade
   block be authored, or is the plan to freeze at 13 and layer central-identity
   authz *above* gacl?
5. **Standalone-encounter** SMART launch is advertised in `Capability` but
   patient/encounter selectors only implement patient — is completing this an
   intended deliverable?
6. Are the `google_signin_*` code paths (Google Workspace login) an active
   feature to preserve, or dead weight to remove in favour of a central IdP?

---

## 5-line summary

- **Login:** `AuthUtils` (login/api/portal-api/other modes) verifies against
  `users_secure` using PHP `password_hash` (Bcrypt/Argon2/SHA512-crypt), with
  per-user + per-IP brute-force counters, timing-attack shim, LDAP branch,
  Google Sign-In branch; on success writes `authUser`/`authPass`/`authUserID`/
  `authProvider` to a single browser-global session `OpenEMR`.
- **OAuth2:** Full DCR-driven OIDC + SMART-on-FHIR provider on
  `league/oauth2-server`; grants `authorization_code`/`refresh_token`/
  `client_credentials` always, `password` opt-in; signing keys RSA-2048
  per-site under `documents/certificates/oa{private,public}.key`; access/
  refresh tokens are JWTs, only clients and trusted-user grants are in DB
  (`oauth_clients`, `oauth_trusted_user`).
- **ACL:** Single `AclMain::aclCheckCore` entry (307 files) that shims to
  legacy php-gacl in 24 `gacl_*` tables; version 13; section-level only
  (no per-record); no legacy `acl_check()` calls remain in this fork.
- **Session:** Four separate cookies (`OpenEMR`/`authserverOpenEMR`/
  `apiOpenEMR`/`PortalOpenEMR`), 4-hour `gc_maxlifetime`, 2-hour idle logout
  (`timeout` global), core cookie is httpOnly=false + SameSite=Strict and
  **not** namespaced by tenant — a browser can only hold one tenant login at
  a time.
- **Central-identity fit:** authentication can federate cleanly (Google
  Sign-In is a working template); authorization cannot — every tenant DB
  still requires `users` rows and `gacl_aro*` grants for referential
  integrity, and the global `CORE_SESSION_ID` blocks simultaneous
  multi-tenant browser sessions.
