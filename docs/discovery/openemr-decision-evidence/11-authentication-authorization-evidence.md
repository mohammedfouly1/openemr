# 11 — Authentication & Authorization Evidence (Q4–Q10)

_Fork: OpenEMR 8.3.0-dev @ `631f2b38cf633769c305233f88cdf9c73ca80657`. Mode: READ-ONLY static analysis.
Evidence method: `git grep <pattern> HEAD` (reads blobs from the packfiles, pinning every citation to the
exact commit) plus manual reads of the cited files. Machine: native Windows stack, no Docker._

Citation format: `[fork:631f2b38cf633769c305233f88cdf9c73ca80657] path:Lstart-Lend`.

---

## 1. Executive summary

1. **There is no `REMOTE_USER` support anywhere in the tracked tree.** `git grep REMOTE_USER HEAD` returns
   **zero matches across all file types** — not just PHP. `mod_auth_openidc` likewise returns zero. Q6 is
   therefore not a "do we trust it" question but a "we would have to build it" question.
2. **MFA exists but cannot be enforced org-wide.** `MfaUtils`, `Totp`, and U2F ship, but `git grep` for
   `force_mfa|mfa_required|gbl_force_mfa` returns **zero matches**, and `library/globals.inc.php` contains
   **no MFA global at all**. Enforcement is per-user enrolment only.
3. **Google Sign-In is a complete, working federation path** — two globals, a dedicated `users` column, a
   login-page branch, and full admin CRUD. It is a usable reference implementation for Q4.
4. **Dynamic Client Registration is live**, advertised at `$base_url/registration`. A hand-provisioning
   admin UI (Q8) is genuinely optional, not a gap.
5. **Q10's premise is partly incorrect.** The CapabilityStatement does *not* falsely advertise standalone
   encounter launch — `context-standalone-encounter` is explicitly **commented out** of the advertised
   list. The real inconsistency is different and narrower (see §7).
6. **ACL version is 13**, upgraded by a sequential `$upgrade_acl` ladder in `acl_upgrade.php` keyed on
   `AclExtended::getAclVersion()`. The collision mechanics behind Q9 are confirmed.

---

## 2. Q4 — Central IdP choice: what the code already provides

| Capability | Present | Evidence |
|---|---|---|
| Native username/password login | Yes | `src/Common/Auth/AuthUtils.php` (login/validation entry points at `:153`, `:290`, `:518`, `:1447`) |
| OpenEMR **as** an OIDC/OAuth2 **provider** | Yes | `src/Common/Auth/OpenIDConnect/` tree; `src/RestControllers/AuthorizationController.php` |
| OpenEMR **as** an OIDC **relying party** (generic) | **No** | zero `mod_auth_openidc`, zero `REMOTE_USER` (§3) |
| OpenEMR as an RP for **Google specifically** | Yes | §4 |
| SMART-on-FHIR app launch | Yes (patient context) | §7 |

**Reading.** OpenEMR ships a full OAuth2/OIDC **authorization server** (`league/oauth2-server` based) but
**no generic OIDC client**. Fronting it with Keycloak therefore does not "turn on a setting" — it requires
either (a) trusting a proxy header, which needs new code (§3), or (b) writing a real OIDC RP callback that
establishes an OpenEMR session. Google Sign-In (§4) is the only shipped example of pattern (b) and is the
natural template.

Confidence: **CONFIRMED** for the presence/absence facts; the "Keycloak needs new code" conclusion is
**HIGH** (derived from those facts, not directly asserted anywhere in the tree).

---

## 3. Q6 — Reverse-proxy trust for `REMOTE_USER`

```
Command: git grep -n "REMOTE_USER"    HEAD    -> 0 matches (all file types)
Command: git grep -n "mod_auth_openidc" HEAD  -> 0 matches (all file types)
```

Both saved in `evidence/raw/` via the audit command log (`22-command-log.txt`).

**Finding.** OpenEMR has **no** `REMOTE_USER` / trusted-header authentication path. There is nothing to
"enable" and nothing to accidentally trust today — which is a *security positive* for the current state
and a *cost* for the Q4 = external-IdP direction.

**Related, and more urgent:** the one place the app *does* consume a proxy-supplied header, it does so
without validation. `collectIpAddresses()` reads `HTTP_X_FORWARDED_FOR` verbatim:

`[fork:631f2b38cf633769c305233f88cdf9c73ca80657]` `library/sanitize.inc.php:29-46`

```php
function collectIpAddresses(): array
{
    $mainIp = $_SERVER['REMOTE_ADDR'] ?? '';
    $stringIp = $mainIp;
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwardIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        $stringIp .= " (" . $forwardIp . ")";   // whole client-controlled chain, unparsed
    }
    ...
}
```

The unvalidated value reaches the audit trail at two sinks:

- `src/Common/Logging/Audit/LogTablesSink.php:70` — `collectIpAddresses()['ip_string']` → `log` table IP column.
- `src/Common/Logging/EventAuditLogger.php:265-266` — embedded into the auth-failure `comments` field.

`git grep -in "trustedproxies|trusted_proxy" HEAD` → **0 matches**: there is no trusted-proxy allowlist
anywhere. **Mitigating factor:** the true socket peer (`REMOTE_ADDR`) is retained separately in the `ip`
key, so the authoritative address is not lost — only the appended `(…)` segment is attacker-controlled.

**Impact:** audit-log poisoning / log-forging into the `log` table. See `15-security-compliance-code-evidence.md` §6.
Confidence: **CONFIRMED**.

---

## 4. Q7 — Google Sign-In: retire or keep?

Full wiring, all confirmed by direct citation:

| Layer | Evidence |
|---|---|
| Globals (2) | `library/globals.inc.php:2251` (`google_signin_enabled`), `:2258` (`google_signin_client_id`) |
| Login page branch | `interface/login/login.php:243-244` — renders the button only when `google_signin_enabled` **and** a client ID is set |
| Per-user identity column | `users.google_signin_email`, admin-edited at `interface/usergroup/user_admin.php:483`; persisted at `interface/usergroup/usergroup_admin.php:307` |
| Create-user form | `interface/usergroup/usergroup_admin_add.php:413` |
| Uniqueness | `interface/usergroup/usergroup_admin.php:397-399` — empty stored as `NULL` because of a unique-key constraint |
| Client-side validation | `user_admin.php:131`, `usergroup_admin_add.php:119` |

**Finding.** This is not vestigial code — it is a complete external-IdP integration: a federated subject
(Google email) mapped onto a **local `users` row**. That mapping shape is exactly what a Keycloak
integration would need, which makes it the cheapest available template for Q4.

**Default-off:** the login button requires both globals to be set, so leaving the code in place costs
nothing at runtime.

Confidence: **CONFIRMED**.

---

## 5. Q5 — Force MFA org-wide

```
Command: git grep -n "force_mfa|mfa_required|gbl_force_mfa" HEAD          -> 0 matches
Command: git grep -n "mfa|MFA" HEAD -- library/globals.inc.php            -> 0 matches
```

MFA implementation that *does* ship:

- `src/Common/Auth/MfaUtils.php` — MFA orchestration.
- `library/classes/Totp.class.php` — TOTP.
- `library/js/u2f-api.js` — U2F/hardware key.
- `src/Common/Auth/OpenIDConnect/Repositories/UserRepository.php`, `src/RestControllers/AuthorizationController.php` — MFA in the OAuth2 flow.

`src/Common/Auth/AuthUtils.php` contains **no** MFA enforcement branch — the only occurrence of the string
is a documentation comment at `:12`.

**Finding.** MFA is **per-user opt-in enrolment with no administrative enforcement switch**. Forcing MFA
org-wide requires either (a) a core patch adding a global plus a login-flow gate — a class-c fork patch
carried across every rebase — or (b) moving enforcement into the Q4 IdP, where it is configuration rather
than code.

**Decision impact:** this is a strong, evidence-backed argument for Q4 = external IdP: it converts Q5 from
a permanent fork patch into an IdP policy setting.

Confidence: **CONFIRMED** (absence proven by exhaustive grep over the tracked tree).

---

## 6. Q8 — Tenant-admin UI for OAuth2 clients vs DCR

| Mechanism | Status | Evidence |
|---|---|---|
| Dynamic Client Registration endpoint | **Live** | `src/RestControllers/Authorization/OAuth2DiscoveryController.php:72` advertises `"registration_endpoint": "$base_url/registration"` |
| DCR request routing | Present | `src/RestControllers/Subscriber/OAuth2AuthorizationListener.php:167` (`str_contains($end_point, '/registration')`) |
| SMART discovery advertisement | Present | `src/RestControllers/SMART/SMARTConfigurationController.php:60` |
| Client persistence | Present | `src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php` |
| Existing admin surface | Revoke/inspect only | `src/FHIR/SMART/ClientAdminController.php:46` |

**Finding.** DCR is fully wired, so partner integrators can self-register. The only gap is
**hand-provisioning** for partners that cannot self-register. The suggested default (DCR-only for Day 1)
is supported by the evidence.

Confidence: **CONFIRMED**.

---

## 7. Q10 — Standalone-encounter SMART launch — *premise corrected*

The prior discovery note stated the CapabilityStatement "advertises it, only patient selector exists."
**The repository does not support that claim.** Actual state:

`[fork:631f2b38cf633769c305233f88cdf9c73ca80657]` `src/FHIR/SMART/Capability.php:38-59`

```php
        , self::PERMISSION_AUTHORIZE_POST
        // additional capabilities for SMART v2
        , self::CONTEXT_EHR_ENCOUNTER          // <- line 48: ADVERTISED
        // client-confidential-asymmetric - JWT authentication
        // context-standalone-encounter        // <- line 50: COMMENTED OUT, not advertised
        // permission-v2
    ];

    const FHIR_SUPPORTED_CAPABILITIES = [
        self::LAUNCH_EHR, self::CONTEXT_BANNER_PASSTHROUGH, self::CONTEXT_EHR_PATIENT
        , ... self::CONTEXT_STANDALONE_PATIENT, self::LAUNCH_STANDALONE, ...
    ];                                          // <- no CONTEXT_STANDALONE_ENCOUNTER
```

- `CONTEXT_STANDALONE_ENCOUNTER` is **declared at `:103` and never referenced anywhere else**
  (`git grep CONTEXT_STANDALONE_ENCOUNTER HEAD` → exactly 1 hit, the declaration). It is dead.
- Therefore **standalone-encounter launch is honestly not advertised.** No conformance lie.

**The real inconsistency** is narrower and still worth fixing:

- `CONTEXT_EHR_ENCOUNTER` **is** advertised (`:48`, consumed by `.well-known/smart-configuration`).
- But the `launch/encounter` scope is **absent from every grantable scope list**:
  - `src/Common/Auth/OpenIDConnect/Entities/ServerScopeListEntity.php:53` lists `launch/patient` only.
  - `src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php:248` likewise.
  - `git grep "launch/encounter" HEAD` → 4 hits, **all documentation**: `API_README.md:198`,
    `Documentation/api/SMART_ON_FHIR.md:793,845`, and the comment at `Capability.php:102`.

So: the server tells clients it supports EHR-launch **with encounter context**, while the scope a client
must request to receive that context cannot be granted. Documentation (`SMART_ON_FHIR.md:793`) shows
example requests using `launch/encounter`, which would fail.

**Corrected Q10 scope:** the deliverable is (a) add `launch/encounter` to the two scope lists, (b) wire
encounter context into the launch/token response, (c) build the encounter picker, and (d) decide whether to
also enable `context-standalone-encounter` (line 50). Item (a) is small; (c) is the bulk.

Confidence: **CONFIRMED** for every citation above; **HIGH** for the effort characterisation.

---

## 8. Q9 — ACL version policy

- Current value: `$v_acl = 13` — `[fork:631f2b38cf633769c305233f88cdf9c73ca80657] version.php:42`
  (alongside `$v_database = 541` at `:34`).
- Upgrade mechanism: `acl_upgrade.php` reads the installed version via `AclExtended::getAclVersion()`
  (`acl_upgrade.php:65-67`, defaulting to `0`) and then runs a **sequential ladder** of
  `$upgrade_acl = N; if ($acl_version < $upgrade_acl) { …; $acl_version = $upgrade_acl; }` blocks
  (documented in the file header at `:14-16,39`; first block at `:70-72`; ladder continues to `:268`).

**Finding.** The ladder is keyed purely on a monotonic integer with no namespace. If the fork authors a
downstream `$v_acl = 14` block and upstream later ships its *own* 14, a rebased site that already recorded
version 14 will **silently skip upstream's block** — the same failure class as `$v_database`. This
confirms the "never bump" rule as mechanically necessary, not merely stylistic.

Confidence: **CONFIRMED**.

---

## 9. Cross-cutting: what a Control Plane must not assume

- A **local `users` row is mandatory** for every human — Google Sign-In (§4) maps a federated subject onto
  one rather than replacing it. Expect the same for Keycloak.
- **Authorization stays in per-tenant `gacl_*` tables**; no code path consults an external authorization
  service. Central identity can federate *authentication* only; *authorization* remains tenant-local.
- These constraints are developed further in `16-control-plane-constraints.md` §16.4.

---

## 10. Reproduction

```bash
git grep -n "REMOTE_USER" HEAD
git grep -n "mod_auth_openidc" HEAD
git grep -n "force_mfa\|mfa_required\|gbl_force_mfa" HEAD
git grep -n "mfa\|MFA" HEAD -- library/globals.inc.php
git grep -n "google_signin" HEAD
git grep -n "launch/encounter" HEAD
git grep -n "CONTEXT_STANDALONE_ENCOUNTER\|CONTEXT_EHR_ENCOUNTER" HEAD
git grep -n "registration_endpoint" HEAD -- 'src/*'
git grep -n "v_acl\|v_database" HEAD -- version.php
git grep -n "collectIpAddresses" HEAD
```

All commands are pinned to `HEAD` = `631f2b38cf633769c305233f88cdf9c73ca80657` and are logged in
`22-command-log.txt`.
