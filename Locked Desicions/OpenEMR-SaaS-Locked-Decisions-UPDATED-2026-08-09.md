# OpenEMR SaaS — Locked Decisions

**Document status:** FINAL / LOCKED  
**Decision coverage:** Q1–Q77 = 77/77 CLOSED  
**Revision date:** 2026-08-09  
**Supersedes:** all earlier provisional/open-decision drafts for Q1–Q75; incorporates `ADR-DEV-001` (native Docker-free development amendment) and adopts `Q76`–`Q77` (branding governance, Section L)

## 0. Purpose and governance

This document is the authoritative architecture/product decision register for the OpenEMR-based Saudi SaaS program. It separates **what is decided** from **how/when it is implemented**. All implementation work, verification work and acceptance criteria are maintained in the separate `OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria.md`.

A decision below may be reopened only by a new ADR that: (1) identifies the affected Q-number, (2) presents new evidence or a changed business/regulatory requirement, (3) describes migration cost and security/tenant-isolation impact, and (4) is explicitly approved.

### 0.1 Evidence baseline

- Audit mode: read-only; no application source file was modified by the evidence audit.
- Audited fork commit: `631f2b38cf633769c305233f88cdf9c73ca80657`.
- Audited branch: `master`.
- Stable upstream baseline discovered by the audit: `v8_2_0`, commit `6125a2fd8089c8bcc3848071c1293c60e27a7585`.
- The audit established that the fork had zero fork-owned commits at that snapshot; this is why the no-core-edit policy is locked now.
- The evidence audit was static: DB-backed/API/E2E/Selenium runtime behavior was not exercised on that audit host. Runtime checks are therefore represented as acceptance criteria, not left as open architecture questions.

### 0.2 External verification used to close non-repository decisions

- **OpenEMR:** official release notes/download page identify OpenEMR 8.2.0 as the stable release dated 2026-07-08. Official Windows and Linux installation guidance explicitly supports a native stack using MySQL/MariaDB + Apache (or another PHP-capable webserver) + PHP; Docker is recommended by the project but is not technically required to install or develop OpenEMR.
- **Google Cloud Build:** official documentation supports remote container-image builds from source/Dockerfile and publishing to Artifact Registry. Therefore a developer VM does not need a working local Docker Engine in order to produce the production OCI artifact.
- **Kubernetes:** current Kubernetes documentation requires a CRI-compatible runtime and documents removal of the in-tree Docker `dockershim`; production therefore remains Kubernetes/Helm with an approved CRI runtime such as containerd and does not depend on Docker Engine.
- **ZATCA:** Wave 25 notice dated 2026-07-24 states Phase-2/Fatoora integration requirements and the 2027-02-01 integration date for notified taxpayers above the stated revenue threshold.
- **SDAIA/PDPL:** the regulation on transfer outside the Kingdom provides for risk assessments and appropriate safeguards in applicable cross-border transfers; therefore KSA-only is a stricter company default, not a claim that every transfer is categorically prohibited.
- **PostgreSQL:** PostgreSQL 18 is a supported current major version; the control plane shall track the current supported minor release.

### 0.3 Global architecture invariants

1. **Native development is first-class; Docker is optional.** The primary supported developer runtime is a direct Apache + PHP + MariaDB/MySQL installation. Docker Engine/Compose may be used where available, but cannot be a prerequisite for local development, local source builds or developer E2E. Docker Engine and Docker Compose must not be staging/production runtimes.
2. **Production runtime = managed Kubernetes + Helm + CRI runtime (for example containerd).**
3. **DB-per-tenant** is the clinical tenancy model.
4. **No core edits by default.** Prefer modules, overlays, configuration and upstream PRs.
5. **Keycloak authenticates; tenant-local OpenEMR authorizes.**
6. **Saudi production data is KSA-resident by company default.**
7. **The Control Plane is not a clinical data warehouse.**
8. **NPHIES is a dedicated SaaS module/domain**, not an X12/ClaimRev repurposing.
9. **Arbitrary tenant CSS/JS is prohibited.**
10. **Security/compliance claims must describe actual controls, not inferred capabilities.**

### 0.4 ADR-DEV-001 — Native Docker-free development runtime amendment

**Status:** LOCKED AMENDMENT  
**Affected decisions:** Q3, Q39, Q53; implementation item MVP-001  
**Change trigger:** the active Google Cloud development VM cannot run the required local Docker virtualization path reliably, while the project must continue toward MVP without weakening the production architecture.

#### Locked amendment

1. **Primary developer runtime:** Apache + PHP + MariaDB/MySQL installed directly on the development host/VM. For the current Windows Server development host, this is the required supported path.
2. **Docker is optional in development:** Docker Engine and Docker Compose may still be used by another developer or CI environment where available, but no local development, source build, unit/integration test or required developer E2E acceptance criterion may depend exclusively on Docker.
3. **Native source build:** Composer and the repository-required Node.js/npm toolchain run directly on the development host to build OpenEMR and SaaS assets.
4. **Native E2E path:** Panther/Chrome/Chromium/ChromeDriver is a supported local browser-test path. The existing Panther non-Grid fallback shall be qualified rather than requiring Dockerized Selenium locally.
5. **CI/CD image build:** release OCI images are built by approved remote CI/CD infrastructure (initially Google Cloud Build and/or GitHub-hosted CI) and published to the private registry. A developer VM does not require Docker Engine to produce a release.
6. **Production unchanged:** Staging and Production remain managed Kubernetes + Helm with signed/pinned OCI artifacts and a CRI runtime such as containerd. No production node requires Docker Engine or Docker Compose.
7. **CI containers remain allowed:** GitHub Actions/service containers or other ephemeral CI containers remain permitted for isolated test jobs; this does not create a local-Docker requirement.

#### Migration cost and security/tenant-isolation impact

- Migration cost is limited to developer bootstrap/runbooks, native Apache/PHP/MariaDB configuration, native E2E qualification, and CI image-build wiring.
- No tenant database model, hostname routing rule, Keycloak boundary, control-plane separation, KMS rule, NPHIES architecture or production Kubernetes decision is changed.
- Native development must preserve the same application configuration, database charset/collation expectations, secret-handling rules and tenant-isolation tests used by CI/production.
- Developer machines must use synthetic/non-PHI data unless all applicable BLOCKER controls have been completed.

#### External support for the amendment

- OpenEMR 8.2.x installation documentation supports native MySQL/MariaDB + Apache/PHP installation on Windows and Linux.
- Google Cloud Build can build and publish container images remotely from source/Dockerfile.
- Kubernetes production nodes require a CRI-compatible runtime; Docker Engine is not required by the production Kubernetes design.

## Cross-cutting locked architecture — SaaS Control Plane

The following is a locked architecture derived primarily from Q11–Q17 and the dedicated control-plane evidence audit:

1. **Separate service and database.** The SaaS Control Plane is a separate service with a dedicated **managed PostgreSQL 18** database, always on the current supported minor release.
2. **What the Control Plane stores.** Tenant registry, domains, subscription/plan status, tenant membership, deployment/image/chart versions, feature flags, branding tokens, provisioning workflow state, database registry metadata, secret references and platform audit events.
3. **What it does not store.** It is not a clinical warehouse. Patient records, encounters, diagnoses, prescriptions, clinical documents, billing line detail and NPHIES clinical/financial payloads remain tenant-local unless intentionally exported to a separately governed analytics platform.
4. **Tenant databases.** Each tenant has its own isolated OpenEMR database/site and its own file/document scope. There are no cross-tenant SQL joins or cross-database foreign keys.
5. **Identity.** Keycloak is the source of truth for authentication/MFA. Tenant-local OpenEMR `users`/gACL remain the source of truth for clinical authorization.
6. **Logical linkage only.** Control Plane ↔ tenant data uses `tenant_id` UUID and explicit mappings; no distributed ACID transaction is attempted across PostgreSQL and tenant databases.
7. **Secrets.** The Control Plane stores secret references, never raw database passwords/private keys. Actual secret/key material lives in the approved secret manager/KMS.
8. **Runtime credential isolation.** An OpenEMR tenant runtime receives credentials only for its own tenant database. It never receives a credential capable of reading all tenant databases.
9. **Provisioning privilege.** A dedicated provisioning service may hold tightly scoped infrastructure/database-creation privileges; those privileges are not inherited by normal tenant runtime workloads.
10. **Local resilience.** Tenant runtime-critical configuration is cached/snapshotted locally so an outage of the Control Plane does not unnecessarily block already-authenticated clinical work.
11. **Analytics separation.** Cross-tenant analytics uses a separately governed warehouse/ETL pipeline keyed by tenant + source identifier/UUID; the Control Plane database is never used as the clinical analytics warehouse.
12. **Development/production runtime split.** Developer workstations/VMs may run OpenEMR natively on Apache + PHP + MariaDB/MySQL without Docker. OCI images are built by approved CI/CD infrastructure. Control Plane and OpenEMR staging/production workloads run on managed Kubernetes/Helm using a CRI runtime such as containerd; Docker Engine/Compose are not staging/production runtimes.


# A — Foundational / Upstream / Deployment

## Q1 — Add upstream remote and measure drift

**Status:** LOCKED

### Locked decision

Production baselines shall be pinned to an official OpenEMR stable release tag, beginning with `v8_2_0` for the current baseline. `upstream/master` shall be fetched only for drift/security awareness. Adopt a strict **no OpenEMR core edits by default** policy; any unavoidable core change requires a numbered ADR/patch record and an upstream-first path.

### Evidence/rationale

upstream remote added (openemr/openemr) and drift measured. The fork has ZERO own commits: `git merge-base HEAD upstream/master` returns HEAD itself and `git rev-list --count upstream/master..HEAD` returns 0. The fork is an unmodified mirror of upstream master at 2026-07-04, 373 commits behind current upstream master and 17 commits diverged from the v8_2_0 release cut.

**Primary repository evidence:**

- `(git)` — 0 fork-only commits; HEAD is a strict ancestor of upstream/master
- `(git)` — merge-base equals HEAD, proving ancestry
- `(git)` — v8_2_0 is the newest non-prerelease tag, dated 2026-07-08

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q2 — Upstream rebase cadence

**Status:** LOCKED

### Locked decision

Perform an upstream drift review **monthly**, integrate every official stable/point release through a controlled upgrade branch, and handle critical security advisories out-of-band. Published release branches shall not be history-rewritten. Each upgrade must produce a fresh fork-vs-upstream drift report before merge.

### Evidence/rationale

Repository establishes the inputs but not the answer. Upstream cut 3 releases in ~5 months (v8_0_0_3 2026-03-25, v8_1_0 2026-06-01, v8_2_0 2026-07-08) and lands ~373 commits per ~5 weeks of master. Fork currently carries no patches, so rebase cost is presently zero.

**Primary repository evidence:**

- `(git)` — 3 stable releases in 5 months; 373 commits accumulated since fork HEAD

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q3 — Development runtime and target production orchestration platform

**Status:** LOCKED — amended by `ADR-DEV-001`

### Locked decision

The **primary supported development runtime is native Apache + PHP + MariaDB/MySQL**, installed directly on the developer host/VM. Docker Engine and Docker Compose are **optional development conveniences**, not prerequisites for local development, source builds, required tests or developer E2E. Composer and the required Node.js/npm build toolchain run directly on the development host.

Staging and Production shall continue to use **managed Kubernetes + Helm** with a CRI runtime such as `containerd`; Docker Engine and Docker Compose are prohibited as staging/production runtimes. Production manifests/charts live in the infrastructure repository. Release OCI images are produced by approved remote CI/CD infrastructure rather than requiring Docker Engine on the developer VM.

### Evidence/rationale

Repository contains ZERO Kubernetes, Helm, Nomad or Swarm artifacts (no Chart.yaml, no values.yaml, no kind: Deployment). It ships 26 docker-compose files, all for CI matrices and developer stacks; these artifacts do not make Docker a functional prerequisite of the OpenEMR PHP application itself. The active GCE development host cannot run the prior Docker-dependent path reliably. Official OpenEMR installation guidance supports native MySQL/MariaDB + Apache/PHP installation, so adopting a native development runtime removes an environmental blocker without changing the production orchestration model.

**External/reconciliation note:** External verification (2026-08-09): OpenEMR 8.2.0 Windows/Linux installation guidance supports native MariaDB/MySQL + Apache/PHP. Google Cloud Build supports remote OCI/container-image builds and registry publication. Kubernetes requires a CRI-compatible runtime and no longer contains the former in-tree Docker dockershim integration.

**Primary repository evidence:**

- `evidence/manifests/q3-deployment-artifacts.txt` — 0 helm/k8s/nomad/swarm files; 26 docker-compose files, all under ci/ or docker/

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-001`.

# B — Identity & Authorization

## Q4 — Central IdP choice

**Status:** LOCKED

### Locked decision

Use **Keycloak** as the central Identity Provider. OpenEMR shall act as an OIDC relying party through a proper callback/integration modeled on the existing Google federation path. OpenEMR native `AuthUtils` remains a tightly controlled break-glass fallback.

### Evidence/rationale

OpenEMR ships a full OAuth2/OIDC authorization SERVER (league/oauth2-server under src/Common/Auth/OpenIDConnect/) but NO generic OIDC relying-party client. The only shipped federation example is Google Sign-In, which maps an external subject onto a LOCAL users row rather than replacing it.

**Primary repository evidence:**

- `src/Common/Auth/OpenIDConnect/` — OpenEMR acts as an OIDC provider
- `interface/login/login.php:L243-L244` — Google Sign-In branch - the only shipped RP-style federation
- `(git)` — no generic RP: zero REMOTE_USER and zero mod_auth_openidc matches tree-wide

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-002`.

## Q5 — Force MFA org-wide

**Status:** LOCKED

### Locked decision

Enforce MFA centrally in Keycloak for all clinical, administrative, privileged, support, and platform roles. Do not add a downstream OpenEMR core `force_mfa` global.

### Evidence/rationale

MFA implementation ships (MfaUtils, Totp.class.php, u2f-api.js) but there is NO enforcement global: `git grep force_mfa|mfa_required|gbl_force_mfa` returns 0 matches, and library/globals.inc.php contains no MFA global at all. AuthUtils.php has no MFA gate (only a doc comment at :12). Enrolment is per-user opt-in.

**Primary repository evidence:**

- `library/globals.inc.php` — no MFA global exists
- `src/Common/Auth/MfaUtils.php` — MFA implementation present but not centrally enforceable
- `src/Common/Auth/AuthUtils.php:L12-L12` — no MFA enforcement branch; only a doc comment

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-002`.

## Q6 — Reverse-proxy trust for REMOTE_USER

**Status:** LOCKED

### Locked decision

Do **not** build authentication on `REMOTE_USER` or trusted identity headers. Use OIDC. Separately, only platform-owned reverse proxies/load balancers may establish client-IP headers; inbound client-supplied `X-Forwarded-For` must be stripped/normalized.

### Evidence/rationale

There is no REMOTE_USER support anywhere: 0 matches tree-wide, all file types. mod_auth_openidc: 0 matches. Separately, the ONE proxy header the app does consume is trusted blindly - collectIpAddresses() appends the entire client-supplied X-Forwarded-For chain unparsed, with no trusted-proxy allowlist anywhere (0 matches).

**Primary repository evidence:**

- `(git)` — zero REMOTE_USER matches tree-wide
- `library/sanitize.inc.php:L29-L46` — raw XFF chain concatenated without validation
- `src/Common/Logging/Audit/LogTablesSink.php:L70-L70` — unvalidated value reaches the log table
- `src/Common/Logging/EventAuditLogger.php:L265-L266` — and the auth-failure comments field

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-002`, `MVP-002`.

## Q7 — Retire Google Sign-In path?

**Status:** LOCKED

### Locked decision

Keep the existing Google Sign-In code in the upstream-derived tree but disable it by default. Use it only as an implementation reference for external-subject → local-user mapping. Reconsider removal after Keycloak is stable in production.

### Evidence/rationale

Google Sign-In is a complete, working federation path, not dead code: 2 globals (google_signin_enabled :2251, google_signin_client_id :2258), a users.google_signin_email column with a unique constraint, a login-page branch, and full admin CRUD across 3 screens. It is default-off (requires both globals).

**Primary repository evidence:**

- `library/globals.inc.php:L2251-L2258` — enable + client-id globals
- `interface/login/login.php:L243-L244` — login button rendered only when both globals set
- `interface/usergroup/usergroup_admin.php:L397-L399` — persists users.google_signin_email; NULL when empty

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-002`.

## Q8 — Tenant-admin UI for OAuth2 clients

**Status:** LOCKED

### Locked decision

Use OpenEMR Dynamic Client Registration (DCR) for Day-1 OAuth client onboarding. Do not build a tenant-admin manual OAuth-client UI until a real integration partner cannot use DCR or an operational requirement justifies it.

### Evidence/rationale

Dynamic Client Registration is LIVE, not merely possible. The discovery document advertises "registration_endpoint": "$base_url/registration" and a listener routes requests containing '/registration'. The existing ClientAdminController provides revoke/inspect only.

**Primary repository evidence:**

- `src/RestControllers/Authorization/OAuth2DiscoveryController.php:L72-L72` — registration_endpoint advertised
- `src/RestControllers/Subscriber/OAuth2AuthorizationListener.php:L167-L167` — DCR request routing
- `src/FHIR/SMART/ClientAdminController.php:L46-L46` — existing admin surface is revoke/inspect only

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q9 — Fork ACL policy: bump v_acl=14 or freeze at 13?

**Status:** LOCKED

### Locked decision

Freeze downstream ACL schema/versioning at upstream `$v_acl = 13`. Never create a downstream ACL upgrade number. New SaaS authorization concepts belong in `saas_*` data and services layered above tenant-local gACL.

### Evidence/rationale

$v_acl = 13 at version.php:42 (alongside $v_database = 541 at :34). acl_upgrade.php reads the installed version via AclExtended::getAclVersion() and runs a SEQUENTIAL integer ladder of `$upgrade_acl = N; if ($acl_version < $upgrade_acl) {...}` blocks. The counter has no namespace.

**Primary repository evidence:**

- `version.php:L34-L42` — $v_acl = 13, $v_database = 541
- `acl_upgrade.php:L14-L39` — sequential integer ladder keyed on a namespace-free counter
- `acl_upgrade.php:L65-L67` — installed version read, defaults to 0

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q10 — Complete standalone-encounter SMART launch

**Status:** LOCKED

### Locked decision

Correct the SMART capability inconsistency first: `context-ehr-encounter` must not be advertised unless `launch/encounter` is actually grantable and wired. Either implement both scope + context correctly, or stop advertising the capability. A richer encounter picker is a separate NPHIES/SMART deliverable, not the prerequisite for correcting the advertised capability.

### Evidence/rationale

PREMISE CORRECTED. The CapabilityStatement does NOT falsely advertise standalone-encounter launch - context-standalone-encounter is explicitly COMMENTED OUT at Capability.php:50, and the CONTEXT_STANDALONE_ENCOUNTER constant (:103) is declared but never referenced. The real defect is different: CONTEXT_EHR_ENCOUNTER IS advertised (:48) while the launch/encounter scope is absent from every grantable scope list (ServerScopeListEntity.php:53 and ScopeRepository.php:248 list launch/patient only). All 4 launch/encounter matches are documentation.

**External/reconciliation note:** Repository correction: standalone encounter was not falsely advertised; the actual inconsistency is `context-ehr-encounter` versus the missing grantable `launch/encounter` scope.

**Primary repository evidence:**

- `src/FHIR/SMART/Capability.php:L48-L52` — CONTEXT_EHR_ENCOUNTER advertised; standalone commented out
- `src/FHIR/SMART/Capability.php:L103-L103` — CONTEXT_STANDALONE_ENCOUNTER declared and never used
- `src/Common/Auth/OpenIDConnect/Entities/ServerScopeListEntity.php:L53-L53` — launch/patient only
- `Documentation/api/SMART_ON_FHIR.md:L793-L845` — docs show launch/encounter examples that cannot be granted

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-006`.

# C — Multi-Tenancy & Isolation

## Q11 — DB-per-tenant vs shared-DB

**Status:** LOCKED

### Locked decision

Lock **Model A: one physically separate OpenEMR database per tenant**. No shared clinical database with a `tenant_id` retrofit. Tenant isolation is primarily the database/site boundary.

### Evidence/rationale

The true shared-DB refactor surface is 6,785 data-access call sites, not the 1,875 previously estimated: sqlStatement( 2,025 + QueryUtils:: 1,653 + sqlQuery( 1,454 + sqlFetchArray( 1,354 + sqlInsert( 251 + Doctrine DBAL 48, plus 202 OE_SITE_DIR file-path sites. No core table has a tenant_id/site_id column. Tenancy is enforced by which sqlconf.php is loaded - the connection IS the boundary.

**Primary repository evidence:**

- `evidence/raw/remaining-counts.tsv` — 6,785 total data-access call sites, per-sink lists saved
- `interface/globals.php:L277-L335` — site id resolved then sqlconf.php loaded; connection is the tenancy boundary
- `sql/database.sql` — no tenant_id/site_id discriminator on any core table

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-005`.

## Q12 — Tenant routing scheme

**Status:** LOCKED

### Locked decision

Use **subdomain-per-tenant routing**. Hostname → tenant/site mapping must be authoritative and validated before OpenEMR bootstrap. Public `?site=` tenant selection is prohibited; the platform must prevent a query parameter from switching tenant context.

### Evidence/rationale

Hostname->site routing EXISTS but only for unauthenticated requests: interface/globals.php:295-297 and index.php:14 consult HTTP_HOST only when $ignoreAuth is set. Authenticated traffic resolves site from $_GET['site'] (wins) or session site_id. A regex allowlist at globals.php:304 is the sole guard.

**Primary repository evidence:**

- `interface/globals.php:L283-L297` — HTTP_HOST consulted only when auth is ignored
- `interface/globals.php:L277-L281` — $_GET['site'] takes precedence over session
- `interface/globals.php:L304-L304` — regex allowlist is the only validation of the site string

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-005`.

## Q13 — Tenant-count ceiling

**Status:** LOCKED

### Locked decision

Design the initial platform for **up to 500 tenants**, with a mandatory capacity/architecture review at 200 tenants. Introduce consolidated background-job orchestration before 100 tenants and control connection-pool/worker multiplication from the beginning.

### Evidence/rationale

Repository establishes the binding constraints, not the number. Connection pooling is per-{Apache worker x DSN} (DatabaseConnectionFactory.php:113,137-151), so DB-per-tenant yields up to N_tenants x M_workers persistent handles. Cross-site iteration is an opendir(sites/) walk (admin.php:75-115). Background jobs run once per site per cron via bin/console --site=<name>.

**Primary repository evidence:**

- `src/BC/DatabaseConnectionFactory.php:L113-L151` — persistent p:host connections, per worker per DSN
- `admin.php:L75-L115` — cross-site control plane is an opendir walk over sites/

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-008`.

## Q14 — Per-tenant Apache/TLS/observability config location

**Status:** LOCKED

### Locked decision

Keep tenant routing, TLS, ingress, load-balancer, observability, Kubernetes, Helm, and environment configuration in a **separate infrastructure repository**. `sites/<site>/config.php` is operator-controlled application configuration, not infrastructure-as-code.

### Evidence/rationale

No per-tenant vhost/TLS/log-shipping config exists in the repo. The only per-site executable seam is sites/<site>/config.php, require_once'd at interface/globals.php:649.

**Primary repository evidence:**

- `interface/globals.php:L649-L649` — per-site arbitrary PHP require_once - the only per-site executable seam

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-008`.

## Q15 — Cross-tenant analytics: Day-1 or Day-N?

**Status:** LOCKED

### Locked decision

Cross-tenant analytics is **Day-N, not MVP**. From Day-1, all export/warehouse contracts must use globally unambiguous identifiers such as `(tenant_id, entity_id)` and/or stable UUIDs so later ETL is possible without re-keying clinical databases.

### Evidence/rationale

Under the locked Model A there is no cross-tenant query path: each site is a physically separate database with no shared identifiers, and site-local ids collide by design (two sites both have documents.id = 1). Cross-tenant analytics therefore requires an ETL into a warehouse keyed on (site_name, entity_id) or UUIDs.

**Primary repository evidence:**

- `sql/database.sql` — site-local ids collide across tenants; no global identifier space
- `src/Services/InsuranceCompanyService.php:L433-L436` — documented shared id-space; ids are site-local

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `P2-003`.

## Q16 — Central identity across tenants at Day-1?

**Status:** LOCKED

### Locked decision

No seamless multi-tenant OpenEMR session is required Day-1. Keycloak may authenticate the same human to multiple tenants, but each tenant retains a local OpenEMR `users` row and tenant-local gACL authorization. A user switches tenant contexts through the platform and establishes a separate tenant session.

### Evidence/rationale

The session cookie name is a global constant - CORE_SESSION_ID = 'OpenEMR' at src/Common/Session/SessionUtil.php:81 - so one browser holds one tenant login at a time. Additionally every human needs a LOCAL users row plus gacl_aro records per tenant DB; no code path consults an external authorization service.

**Primary repository evidence:**

- `src/Common/Session/SessionUtil.php:L81-L81` — cookie name is a global constant
- `interface/login/login.php:L243-L244` — Google Sign-In maps external subject onto a local users row

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-002`.

## Q17 — Per-tenant cookie names?

**Status:** LOCKED

### Locked decision

Accept one active OpenEMR tenant context per browser session for Day-1. Scope cookies to the tenant subdomain/path at the edge and do not broaden cookie domains across tenant subdomains. Patch the hard-coded session name only if future multi-tenant simultaneous sessions become a product requirement.

### Evidence/rationale

CORE_SESSION_ID is a hardcoded constant 'OpenEMR' (SessionUtil.php:81). Namespacing it per site is a small, well-localised class-c patch, but it is a permanent carry across every rebase.

**Primary repository evidence:**

- `src/Common/Session/SessionUtil.php:L81-L81` — hardcoded global cookie name

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-005`.

# D — Saudi Localization, Arabic & Regulatory Product Scope

## Q18 — Arabic UI baseline: extend existing or greenfield?

**Status:** LOCKED

### Locked decision

Extend the existing OpenEMR SQL translation catalogue as the primary localization source for inherited UI. Do not create a parallel translation system for the legacy UI. New standalone SaaS frontends may use their framework-native i18n only if translation ownership and synchronization are explicit.

### Evidence/rationale

Arabic coverage measured at 47.53%: 6,290 Arabic rows out of 13,234 unique lang_constants in contrib/util/language_translations/currentLanguage_utf8.sql. The default installer ships EN-only (sql/database.sql:3569) so Arabic requires a post-install import. The frontend i18next layer reads the SAME lang_* tables (interface/main/tabs/main.php:335-349) - there is no separate JSON catalogue to maintain.

**Primary repository evidence:**

- `contrib/util/language_translations/currentLanguage_utf8.sql` — 6,290/13,234 = 47.53% Arabic coverage
- `sql/database.sql:L3569-L3569` — installer ships EN-only
- `interface/main/tabs/main.php:L335-L349` — i18next fetches from the same lang_* tables

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-004`.

## Q19 — Hijri calendar policy

**Status:** LOCKED

### Locked decision

Store canonical dates/timestamps in Gregorian/ISO form. Provide Hijri (Umm al-Qura) display/input where product workflows require it, beginning with DOB, appointments and administrative/billing dates. When a user enters a Hijri date, preserve the original entered value/calendar metadata for audit while storing the canonical Gregorian value. Clinical event timestamps remain canonical Gregorian.

### Evidence/rationale

Zero Hijri support exists: no matches for 'hijri', 'IntlCalendar', or 'moment-hijri' anywhere in tracked files. library/date_functions.php:43-54 is hard-Gregorian.

**Primary repository evidence:**

- `library/date_functions.php:L43-L54` — hard-Gregorian date handling
- `(git)` — zero hijri/IntlCalendar/moment-hijri matches

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `P2-002`.

## Q20 — Currency policy

**Status:** LOCKED

### Locked decision

Saudi launch is **SAR-only**. Currency is tenant/config aware, but the Saudi product defaults to SAR and does not expose general multi-currency workflows until another country is intentionally supported.

### Evidence/rationale

No multi-currency schema exists: no currency column on billing (sql/database.sql:245-278) or any financial table. Display symbol is a single global gbl_currency_symbol defaulting to '$' (library/globals.inc.php:820).

**Primary repository evidence:**

- `sql/database.sql:L245-L278` — no currency column on billing
- `library/globals.inc.php:L820-L820` — single global currency symbol, defaults to $

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-005`.

## Q21 — ZATCA e-invoicing scope

**Status:** LOCKED

### Locked decision

Design for **ZATCA Phase 2 (Integration/Fatoora) as the production target from the start**. Phase-1 QR generation may exist as a compatibility subset, but architecture, persisted tax data, invoice identity, XML/security controls and integration boundaries must not require a later redesign for Phase 2.

### Evidence/rationale

Zero ZATCA support: no matches for zatca, fatoora, invoice_hash, qr_code_invoice, or e-invoice. Tax infrastructure is a rates registry only (list_options list_id='taxrate' at sql/database.sql:4354; codes.taxrates colon-list at :1135). There is NO tax-amount column on billing.

**External/reconciliation note:** External verification (ZATCA, 2026-07-24): Wave 25 covers VAT-taxable revenue above SAR 187,500 in 2022–2025, with integration by 2027-02-01 for notified taxpayers; Phase 2 requires Fatoora integration, prescribed format and additional fields.

**Primary repository evidence:**

- `sql/database.sql:L4354-L4354` — tax rates registry exists but no tax-amount column on billing
- `(git)` — zero zatca/fatoora matches

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-013`.

## Q22 — MSA vs Saudi-dialect terminology

**Status:** LOCKED

### Locked decision

Use **Modern Standard Arabic (MSA)** as the official Arabic product language for clinical, administrative and billing UI. Patient-facing copy may be simplified after user testing, but dialect-specific terminology is not the system baseline.

### Evidence/rationale

The bundled catalogue is MSA of unmeasured fidelity (47.53% coverage per Q18). The repository cannot adjudicate dialect preference.

**Primary repository evidence:**

- `contrib/util/language_translations/currentLanguage_utf8.sql` — bundled catalogue is MSA

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-004`.

## Q23 — Per-user timezone/language preference

**Status:** LOCKED

### Locked decision

Use `Asia/Riyadh` as the Saudi tenant/site timezone baseline. Language is a per-user preference using the existing user-settings mechanism; do not add a new user-language schema solely for SaaS.

### Evidence/rationale

Timezone is site-wide: gbl_time_zone (library/globals.inc.php:777) applied at interface/globals.php:520, with a UTC default at bootstrap.php:30. Language is already per-session via language_choice, and a per-user override mechanism exists generically through user_settings (proven for css_header at globals.php:437-450).

**Primary repository evidence:**

- `library/globals.inc.php:L777-L777` — site-wide timezone global
- `interface/globals.php:L520-L520` — timezone applied per request
- `interface/globals.php:L437-L450` — user_settings overlay pattern already used for per-user globals

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-004`.

## Q24 — bootstrap-rtl sustainability

**Status:** LOCKED

### Locked decision

Vendor the currently required Bootstrap-RTL dependency inside the controlled source/build supply chain so builds do not depend on an external archive remaining available. Treat migration to a newer native-RTL Bootstrap generation as a separate future modernization.

### Evidence/rationale

bootstrap-rtl is fetched by napa as a pinned single-commit GitHub archive of an unmaintained third-party fork (package.json:113). If that URL disappears the build breaks with no local copy.

**Primary repository evidence:**

- `package.json:L113-L113` — napa-pinned single-commit archive of an unmaintained fork

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-004`, `P2-004`.

## Q25 — PDF Arabic fonts

**Status:** LOCKED

### Locked decision

Bundle approved Arabic-capable PDF fonts, specifically Amiri and/or Noto Naskh Arabic, and explicitly configure the PDF engines used by the product. Arabic PDFs may not rely on host-font availability.

### Evidence/rationale

No Arabic-capable font is tracked: git ls-files finds no amiri*, noto*naskh*, noto*sans*arabic*, or dejavu* font files. After composer install, mPDF's transitive DejaVuSans is the only fallback - a fallback, not a professional Arabic typeface. interface/themes/rtl_style_pdf.css does ship.

**Primary repository evidence:**

- `(git)` — no Arabic font files tracked
- `interface/themes/rtl_style_pdf.css` — RTL PDF stylesheet ships

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-004`.

## Q26 — Hardcoded USD in FHIR Coverage

**Status:** LOCKED

### Locked decision

Replace runtime hard-coded USD assumptions with a tenant/site-aware currency resolver. Do not global-search-and-replace every `USD` occurrence because fixtures, historical data and non-Saudi functionality may legitimately use USD. Prefer an upstream fix for inherited OpenEMR runtime hard-codes.

### Evidence/rationale

src/Services/FHIR/FhirCoverageService.php:294 hard-codes 'USD', alongside 5 other USD hardcodes identified in the prior localization audit.

**Primary repository evidence:**

- `src/Services/FHIR/FhirCoverageService.php:L294-L294` — hardcoded USD currency

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-005`.

# E — NPHIES, Claims & Insurance Interoperability

## Q27 — FHIR Claim submission architecture

**Status:** LOCKED

### Locked decision

For the NPHIES MVP, use a **background polling/worker architecture** decoupled from the synchronous BillingProcessor flow. It must be idempotent, observable and recoverable. Migrate toward event-driven dispatch after the billing extension surface in Q30/Q65 exists.

### Evidence/rationale

Option A is blocked today: Coverage is read-only and Claim/ClaimResponse have NO HTTP surface. Option B is blocked by missing billing events (Q30) - src/Billing/ contains zero event dispatches. Option C (polling) works with zero core changes and is exactly what the shipped claimrev-connect module does.

**Primary repository evidence:**

- `src/Billing/BillingProcessor/BillingProcessor.php:L161-L192` — hard-coded if/elseif task selection; no events
- `src/Services/FHIR/FhirCoverageService.php` — Coverage is read-only
- `evidence/raw/count-rest_controllers.txt` — 90 REST controllers; no Claim/ClaimResponse HTTP surface

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-007`.

## Q28 — Ownership of FHIR write-surface build

**Status:** LOCKED

### Locked decision

The **NPHIES module owns the Saudi insurance FHIR write surface**: Coverage write requirements, Claim, ClaimResponse, ExplanationOfBenefit, CoverageEligibilityRequest/Response, PaymentNotice and NPHIES-specific orchestration as required by the applicable NPHIES implementation guide. Reuse the generated OpenEMR R4 model classes; do not fork/regenerate them unless profile incompatibility is demonstrated.

### Evidence/rationale

The type surface is partially free but the HTTP surface is absent. 103 FHIR service files and 90 REST controllers exist, with 491 FhirSearchParameterDefinition registrations - but Coverage(write), Claim, ClaimResponse, EOB, CoverageEligibilityRequest/Response and PaymentNotice have no controllers. FHIRClaim/FHIRClaimResponse data classes do exist under src/FHIR/R4/.

**Primary repository evidence:**

- `evidence/manifests/q62-fhir-service-files.txt` — 103 FHIR service files
- `evidence/raw/count-rest_controllers.txt` — 90 REST controllers, none for Claim/ClaimResponse
- `src/FHIR/R4/` — FHIRClaim / FHIRClaimResponse data classes present

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-006`.

## Q29 — x12_partners reuse vs new nphies_partners table

**Status:** LOCKED

### Locked decision

Create a dedicated `saas_nphies_partners` domain model/table. Do not overload `x12_partners`. Store NPHIES partner identifiers, environment, certificate/secret references, capability/configuration metadata, activation state and rotation metadata; actual secrets remain in the secret manager.

### Evidence/rationale

x12_partners carries OAuth columns (x12_token_endpoint, x12_client_id, x12_client_secret) that would technically fit NPHIES. But the custom_ prefix is NOT reserved by upstream (zero baseline tables, zero upgrade files, zero module SQL use it), and shipped modules use vendor-slug prefixes instead.

**Primary repository evidence:**

- `evidence/snippets/q68-custom-prefix-evidence.md` — custom_ prefix unused and unreserved upstream
- `sql/database.sql` — x12_partners OAuth columns exist but are X12-semantic

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-007`.

## Q30 — Missing billing/core events: upstream PR or polling emulation?

**Status:** LOCKED

### Locked decision

Ship NPHIES initially with controlled polling, while pursuing a narrow upstream-compatible BillingProcessor extension: a task registry/factory plus a pre-dispatch extension event. Do not implement a large downstream billing-core fork. Introduce transactional outbox semantics only where a reliable transaction boundary exists.

### Evidence/rationale

src/Billing/ contains NO event dispatch, no factory, no registry and no service-locator lookup. Task selection is a hard-coded if/elseif ladder keyed on $_POST['bn_*'] strings at BillingProcessor.php:161-192. There is no transaction boundary and no idempotency key; claim status is written row-by-row with auto-commit (GeneratorX12.php:151,168).

**Primary repository evidence:**

- `src/Billing/BillingProcessor/BillingProcessor.php:L161-L192` — hard-coded task ladder, no dispatch
- `src/Billing/BillingProcessor/Tasks/GeneratorX12.php:L151-L168` — row-by-row auto-commit, no transaction/idempotency

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-006`.

## Q31 — Keep claimrev-connect in Saudi deployments?

**Status:** LOCKED

### Locked decision

Keep `claimrev-connect` in the inherited Composer dependency set to preserve upstream parity, but disable it in Saudi tenant provisioning by default. It is not the NPHIES implementation.

### Evidence/rationale

Verified empirically on a populated vendor/ tree (not available to the prior audit): claimrevolution/oe-module-claimrev-connect v2.1.6 is the ONLY openemr-module type package in vendor/composer/installed.json. Composer installs it as a REAL directory (not a symlink) of 134 files at interface/modules/custom_modules/oe-module-claimrev-connect, which .gitignore:15 excludes from tracking.

**Primary repository evidence:**

- `vendor/composer/installed.json` — only openemr-module package; v2.1.6; install-path into custom_modules
- `.gitignore:L15-L15` — module directory excluded from tracking because composer owns it
- `composer.json:L52-L52` — runtime require ^2.1

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-007`.

# F — Frontend & User Experience

## Q32 — Patient portal strategy

**Status:** LOCKED

### Locked decision

For MVP, rebrand and harden the existing patient portal. A greenfield SPA patient portal is **Phase 2** and must cut over behind stable APIs rather than rewriting the portal during MVP.

### Evidence/rationale

The portal is a separate Smarty-based app with its own credentials (patient_access_onsite) and its own logo resolution (portal/index.php:62-64, portal/home.php:87,362), fronted by portal REST endpoints. Swapping it does not disturb the main UI.

**Primary repository evidence:**

- `portal/index.php:L62-L64` — portal has independent logo/theme resolution
- `interface/globals.php:L486-L495` — portal theme selected separately via patient_settings

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `P2-001`.

## Q33 — Main-UI strategy

**Status:** LOCKED

### Locked decision

Keep the inherited main UI on Bootstrap 4/Twig for MVP. Add modern SPA surfaces only module-by-module/tab-by-tab where they provide clear value. Do not replace the whole OpenEMR shell in the initial SaaS program.

### Evidence/rationale

Q72's scan corrects the '611 grid files' figure: 5,460 first-party UI files scanned, of which 575 are legacy iframe entry points or files they include, 1,098 shared templates, and 416 custom-module screens. Shell replacement means touching the legacy iframe population, not 611 arbitrary files.

**Primary repository evidence:**

- `18-q72-ui-responsiveness-inventory.csv` — 5,460 files classified; 575 legacy iframe entry/included files
- `19-q72-ui-responsiveness-summary.md` — reconciled classification totals

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `P2-001`.

## Q34 — Themes: how many Saudi variants?

**Status:** LOCKED

### Locked decision

Ship two Saudi SaaS visual variants: light and dark, both RTL-capable. Per-tenant branding is limited to validated design tokens/CSS variables plus tenant logos over a shared immutable bundle. Arbitrary tenant-supplied CSS/JavaScript is prohibited.

### Evidence/rationale

Themes are shared immutable CSS under public/themes/, chosen by FILENAME via globals.css_header with a per-user user_settings override, and gated by a file_exists() check at interface/globals.php:476 that blocks arbitrary filenames. The dropdown is a filesystem scan (edit_globals.php:714-731), not a DB list. RTL is a filename substitution (rtl_<name>.css) at globals.php:551-611.

**Primary repository evidence:**

- `interface/globals.php:L474-L483` — file_exists gate blocks arbitrary theme filenames
- `interface/globals.php:L551-L611` — RTL variant selected by filename substitution
- `interface/super/edit_globals.php:L714-L731` — theme list is a filesystem scan

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-010`.

## Q35 — CKEditor 5 Arabic bundling

**Status:** LOCKED

### Locked decision

Enable CKEditor Arabic (`ar`) and RTL content direction in the controlled OpenEMR editor configurations used by the Saudi product. Arabic editor behavior is part of the localization baseline.

### Evidence/rationale

CKEditor 5 is configured at library/js/nncustom_config.js:198 and library/js/limitedcustom_config.js:259, and grep for language/direction/rtl/ltr in both returns ZERO hits. The @ckeditor/ckeditor5-language 47.6.2 package IS already present in package-lock.json:1173-1175 but is never wired.

**Primary repository evidence:**

- `library/js/nncustom_config.js:L198-L198` — no language/direction config
- `package-lock.json:L1173-L1175` — ckeditor5-language already a dependency

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-004`.

# G — Modules & Extensibility

## Q36 — Byte identity of in-tree oe-module directories

**Status:** LOCKED

### Locked decision

There are **zero fork-owned modifications** to the tracked upstream `oe-module-*` modules at the audited fork commit. Treat tracked modules as upstream-owned and unmodified. Re-run blob-level module drift checks on every upstream upgrade; any future difference becomes an explicit downstream patch record.

### Evidence/rationale

SEVEN in-tree module directories exist (not six), and ZERO have fork-only modifications. Against v8_2_0 only one file differs - oe-module-comlink-telehealth/tests/bootstrap.php - and that difference is caused by an UPSTREAM commit present in HEAD but not in the release cut (0ec6697e0 'feat(bc): add internal deprecation utility (#12753)'). Working tree is clean: git status over custom_modules returns 0 entries. oe-module-claimrev-connect is composer-installed and gitignored, so it has no tracked blobs to compare.

**External/reconciliation note:** Wording precision: 'zero fork-owned changes' is the locked claim; do not overstate this as every module being byte-identical to every upstream reference.

**Primary repository evidence:**

- `(git)` — single differing file vs v8_2_0, attributable to an upstream commit
- `(git)` — the differing file's change comes from upstream commit 0ec6697e0
- `(git)` — no untracked or modified module files

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q37 — openemr/oe-module-installer-plugin internals

**Status:** LOCKED

### Locked decision

Because the OpenEMR Composer module installer derives the target directory from the package-name last segment, SaaS module package names must have globally unique final segments (for example `saas/oe-module-saas-nphies`). CI must reject a Composer install path that collides with a Git-tracked module directory.

### Evidence/rationale

Read from installed source (vendor/ populated on this machine; unavailable to the prior audit). Plugin class: OpenEMR\Composer\ModuleInstallerPlugin\Plugin (Plugin.php:9), which on activate() registers CustomModuleInstaller and ZendModuleInstaller. CustomModuleInstaller extends Composer's LibraryInstaller, supports() only 'openemr-module', and getInstallPath() returns interface/modules/custom_modules/<LAST SEGMENT OF PACKAGE NAME> (CustomModuleInstaller.php:13-20). Confirmed in practice: installed.json maps claimrev-connect to that path and the result is a real directory of 134 files, not a symlink.

**Primary repository evidence:**

- `vendor/openemr/oe-module-installer-plugin/src/Plugin.php:L9-L17` — plugin class and installer registration
- `vendor/openemr/oe-module-installer-plugin/src/CustomModuleInstaller.php:L13-L20` — install path = custom_modules/<last name segment>; vendor ignored
- `vendor/composer/installed.json` — claimrev-connect v2.1.6 installed to that path as a real directory

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-008`.

## Q38 — Twig namespacing convention for custom modules

**Status:** LOCKED

### Locked decision

Every custom SaaS module must use a unique Twig namespace `@<module_slug>/...`; module scaffolding and CI must enforce the convention. Unnamespaced cross-module template resolution is prohibited.

### Evidence/rationale

TwigContainer::addPath() supports namespaces but no convention is enforced in-tree, so template resolution is order-dependent and two modules shipping layout.twig collide silently.

**Primary repository evidence:**

- `src/Common/Twig/TwigContainer.php` — addPath supports namespaces; no convention enforced

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-008`.

# H — DevOps, Runtime & Networking

## Q39 — Do we publish our own OCI application images?

**Status:** LOCKED — amended by `ADR-DEV-001`

### Locked decision

Publish **SaaS-owned OCI-compatible application images** to a private registry, pinned by immutable digest and signed. Local Docker Engine/Compose is not required. Release images shall be built by approved remote CI/CD infrastructure (initially Google Cloud Build and/or GitHub-hosted CI), while Docker/Compose may remain optional developer/CI tooling where available. CI image builds must not require Docker Engine on staging/production nodes; Kubernetes consumes the images through its CRI runtime.

### Evidence/rationale

59 GitHub workflows ship, including the docker-build/docker-release family. Because the fork will carry Arabic assets, Amiri/Noto fonts (Q25), a vendored bootstrap-rtl (Q24) and an NPHIES module, the runtime artifact necessarily diverges from upstream's image. The ownership/pinning/signing requirement therefore remains unchanged; only the place where the image is built changes.

**External/reconciliation note:** Google Cloud Build can build from a Dockerfile and publish directly to Artifact Registry, so a Docker-capable developer VM is not required for the release artifact.

**Primary repository evidence:**

- `evidence/manifests/q39-workflow-inventory.txt` — 59 workflows including docker build/release family

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-001`, `PROD-007`.

## Q40 — Inferno ONC certification in scope?

**Status:** LOCKED

### Locked decision

Inferno/US ONC certification is not a required Saudi production gate. Keep the upstream capability/configuration available and run it optionally or periodically as a FHIR-regression signal. Re-open only if a customer, partner or market expansion makes ONC certification contractual.

### Evidence/rationale

Inferno ships as two git submodules (ci/inferno/onc-certification-g10-test-kit and ci/inferno/inferno-files) plus inferno-test.yml. It validates US ONC / US Core conformance, which has no Saudi regulatory force.

**Primary repository evidence:**

- `evidence/manifests/q40-inferno-artifacts.txt` — inferno submodules and workflow present

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `OPT-005`.

## Q41 — k8s/Helm charts - in-fork or infra repo?

**Status:** LOCKED

### Locked decision

Kubernetes and Helm artifacts live in the separate infrastructure repository. Application releases reference the compatible infrastructure/chart version or commit SHA, and infrastructure releases reference the compatible application image digest.

### Evidence/rationale

Zero Helm/k8s artifacts exist in the repository (same evidence as Q3). Nothing to migrate either way.

**Primary repository evidence:**

- `evidence/manifests/q3-deployment-artifacts.txt` — no chart/values/k8s manifests tracked

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-001`, `PROD-007`.

## Q42 — Production X-Forwarded-For handling

**Status:** LOCKED

### Locked decision

At the edge, strip any inbound `X-Forwarded-For` and set the authoritative client IP chain from trusted platform components only. Application code must parse the trusted chain deterministically. Existing historical values are not to be treated as cryptographically trustworthy.

### Evidence/rationale

The application trusts XFF blindly. collectIpAddresses() (library/sanitize.inc.php:29-46) appends the ENTIRE client-supplied chain, unparsed, to ip_string; there is no trusted-proxy allowlist anywhere (0 matches). The value reaches the log table (LogTablesSink.php:70) and auth-failure comments (EventAuditLogger.php:265-266). Mitigation: the real socket peer is preserved separately in the 'ip' key.

**Primary repository evidence:**

- `library/sanitize.inc.php:L29-L46` — entire client-controlled XFF chain appended unvalidated
- `src/Common/Logging/Audit/LogTablesSink.php:L70-L70` — flows into the log table IP column
- `(git)` — no trusted-proxy configuration exists

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-002`.

# I — Security & Compliance Engineering

## Q43 — Rotate exposed GitHub PATs

**Status:** LOCKED

### Locked decision

Treat all discovered hard-coded GitHub tokens as compromised/burned. They must never enter a SaaS public fork or published build artifact. Upstream exposure must be reported, and secret scanning must detect raw, base64 and encoded variants, not only obvious `ghp_` strings.

### Evidence/rationale

SCOPE IS LARGER THAN RECORDED: 4 compose files x 3 variables = 12 hardcoded token values, not 2 in one file. Locations: development-easy:75-77, development-easy-light:75-77, development-easy-redis:180-182, development-insane:224-226. Three obfuscation layers are used for the same secret class (raw, base64, space-separated decimal char codes), all decoded inline by docker/flex/openemr.sh:766-790. All values REDACTED and never written to any artifact.

**Primary repository evidence:**

- `docker/development-easy/docker-compose.yml:L75-L77` — 3 hardcoded token variables [REDACTED]
- `docker/development-easy-redis/docker-compose.yml:L180-L182` — same 3 variables [REDACTED]
- `docker/flex/openemr.sh:L766-L790` — decodes all three obfuscation layers inline

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-003`.

## Q44 — Per-tenant KMS key isolation Day-1?

**Status:** LOCKED

### Locked decision

Use **per-tenant KMS-backed key custody and rotation from Day-1**, while preserving OpenEMR/CryptoGen semantics instead of redesigning all encryption. Tenant-specific key material remains isolated; the KMS wrapper manages custody, rotation, versioning and recovery.

### Evidence/rationale

No KMS SDK exists in composer.json. Keys are per-site files on disk: $OE_SITE_DIR/documents/certificates/oa{private,public}.key (OAuth2KeyConfig.php:63-64) and sites/<site>/documents/logs_and_misc/methods/.

**Primary repository evidence:**

- `src/Common/Auth/OAuth2KeyConfig.php:L63-L64` — per-site key files on disk
- `composer.json` — no KMS/Vault/SecretsManager SDK required

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-012`.

## Q45 — Saudi PDPL data-residency plan

**Status:** LOCKED

### Locked decision

Company default policy is **Saudi-resident production health data**: tenant databases, PHI documents, PHI-bearing backups, audit data and production key custody remain inside approved Kingdom regions. Cross-border transfer is disabled by default and requires documented legal basis, DPO/Legal approval, risk assessment and appropriate safeguards under PDPL transfer rules.

### Evidence/rationale

No repository evidence can answer this. The repo constrains only that each tenant is a separate database and separate filesystem tree, which makes per-region placement mechanically straightforward.

**External/reconciliation note:** External verification (SDAIA/PDPL transfer regulation): cross-border transfer is not an absolute prohibition, but continuous/large-scale sensitive-data transfer can require risk assessment and appropriate safeguards. KSA-only is therefore a deliberately stricter company default.

**Primary repository evidence:**

- `interface/globals.php:L277-L335` — per-tenant DB + filesystem tree enables per-region placement

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-005`.

## Q46 — Breakglass justification-prompt UI

**Status:** LOCKED

### Locked decision

Provide a break-glass workflow with mandatory justification, strong re-authentication/MFA, minimal emergency permissions, explicit incident/audit identifiers, automatic expiry and post-use review. Implement it as a SaaS/module overlay rather than a broad OpenEMR core patch.

### Evidence/rationale

gbl_force_log_breakglass exists at library/globals.inc.php:2851-2856 with default '1' (ON). It LOGS emergency user activity; no justification-prompt UI exists anywhere.

**Primary repository evidence:**

- `library/globals.inc.php:L2851-L2856` — breakglass logging global, default ON, no prompt

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-002`.

## Q47 — Default value of drive_encryption for Saudi tenants

**Status:** LOCKED

### Locked decision

Do not patch OpenEMR defaults: `drive_encryption` and `database_encryption` already default on in the audited code. **Provisioning must explicitly set/assert both enabled**, and tenant health checks must fail when either is disabled. This decision does not imply clinical PHI columns are encrypted.

### Evidence/rationale

PREMISE CORRECTED: drive_encryption ALREADY DEFAULTS TO ON ('1') at library/globals.inc.php:1035-1040. The prior audit recorded this as unverified. database_encryption likewise defaults to '1' (:1028-1032), consumed at Crypto.php:65 and CryptoGen.php:82. The deprecated couchdb_encryption global (:1043-1048) is an explicit no-op.

**External/reconciliation note:** Repository correction: this supersedes the earlier premise that the upstream default was unknown/off.

**Primary repository evidence:**

- `library/globals.inc.php:L1035-L1040` — drive_encryption default '1' (ON)
- `library/globals.inc.php:L1028-L1032` — database_encryption default '1' (ON)
- `src/Common/Crypto/CryptoGen.php:L82-L82` — database_encryption consumed here

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-006`.

## Q48 — Audit-log retention (PDPL 5+ years)

**Status:** LOCKED

### Locked decision

Build a configurable retention/archival/final-disposition service; **do not hard-code one universal 5- or 7-year period for every data class**. Retention is defined by a governed matrix for medical records, security audit logs, NPHIES transactions, API payloads, backups, technical logs and legal holds.

### Evidence/rationale

No retention policy, pruner or archival job exists for log / api_log. The seeded background_services rows (sql/database.sql:209-217) contain no log-maintenance service. Tables grow without bound.

**Primary repository evidence:**

- `(git)` — no pruner/retention job
- `sql/database.sql:L209-L217` — seeded background services contain no log maintenance

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-001`.

## Q49 — AV scanning for uploads (ClamAV)

**Status:** LOCKED

### Locked decision

Malware and upload protection is mandatory before production: AV scanning, magic-byte/MIME validation, secure filename/path handling, size limits, quarantine or fail-closed handling, authorization and audit. `secure_upload` must be pinned enabled; extension-only allowlists are insufficient.

### Evidence/rationale

Zero antivirus integration exists (0 matches for clamav/virus_scan/antivirus). The only content gate is isWhiteFile() (library/sanitize.inc.php:113), called from just 2 sites (C_Document.class.php:243, DocumentService.php:130), BOTH gated behind the operator-disableable secure_upload global (default '1' ON, globals.inc.php:2125-2130). createDocument( has 26 call sites but only 2 consult the allow-list. No magic-byte MIME validation on the gate, no quarantine, no size limit beyond php.ini.

**Primary repository evidence:**

- `library/sanitize.inc.php:L113-L113` — isWhiteFile allow-list definition
- `controllers/C_Document.class.php:L243-L243` — gate call site, conditional on secure_upload
- `library/globals.inc.php:L2125-L2130` — secure_upload default ON but operator-disableable
- `controllers/C_Document.class.php:L154-L154` — DICOM zip written at :154, gate at :243 - ordering needs review

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-004`.

## Q50 — Add .github/SECURITY.md and .github/dependabot.yml

**Status:** LOCKED

### Locked decision

The upstream-derived repository already contains `SECURITY.md` and Dependabot configuration. Do not create duplicates. Rewrite/override the security disclosure instructions for the SaaS fork's own security channel before the fork is public, while retaining an explicit dependency-update policy compatible with Q2.

### Evidence/rationale

DECISION CONTRADICTED: BOTH FILES ALREADY EXIST. .github/SECURITY.md ships (two disclosure routes: GitHub private advisory and security@open-emr.org with a PGP key), .github/dependabot.yml ships with a full weekly composer configuration (grouped symfony/laminas/development updates, PR limit 15), plus a bonus dependabot-auto-merge.yml workflow.

**External/reconciliation note:** Repository correction: this supersedes the earlier instruction to create files that already exist.

**Primary repository evidence:**

- `.github/SECURITY.md` — disclosure policy present, points at OpenEMR's team
- `.github/dependabot.yml` — weekly composer updates with grouping already configured
- `.github/workflows/dependabot-auto-merge.yml` — auto-merge automation present

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-003`.

# J — Testing & Quality

## Q51 — Current numeric coverage % baseline

**Status:** LOCKED

### Locked decision

Use the upstream online coverage only as an inherited baseline (28.66% at the audit). Quality gates apply primarily to SaaS-owned code: **minimum 80% patch/diff coverage and 60% total line coverage** for ordinary SaaS modules; security/tenant-isolation/NPHIES critical components should target higher coverage where practical. Do not impose the SaaS target retroactively on the entire inherited tree.

### Evidence/rationale

Retrieved LIVE during this audit run from the official Codecov v2 API (https://api.codecov.io/api/v2/github/openemr/repos/openemr/, updatestamp 2026-08-07T06:02:21Z): coverage 28.66%, files 4028, lines 428660, hits 122880, misses 305780, branch master. The prior run measured 27.53% on 2026-07-21 (+1.13 pp in ~17 days). This measures UPSTREAM openemr/openemr; the fork is not onboarded to Codecov, but since fork HEAD is a strict ancestor of upstream master the number is representative.

**Primary repository evidence:**

- `evidence/raw/q51-codecov-api-response.txt` — live Codecov v2 API response captured this run
- `codecov.yml:L24-L24` — the ~4% figure in-repo is stale

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-009`.

## Q52 — CI DB strategy: fresh-per-job or persistent?

**Status:** LOCKED

### Locked decision

SaaS module CI tests that require a database must use a fresh disposable database per job. Preserve upstream suites unless a separate upstream-compatible improvement is needed; no shared persistent CI test database. CI service containers remain permitted; this decision does **not** require Docker Engine on developer workstations/VMs.

### Evidence/rationale

TWO divergent patterns coexist. integration-tests.yml uses GitHub-native services: containers - genuinely fresh MySQL/MariaDB per matrix arm. test.yml (used by test-all.yml and test-scheduled.yml) uses compose-stack DBs: a setup job installs OpenEMR, dumps the DB, and each parallel test job RESTORES that snapshot. So: fresh container per arm, same snapshot restored across suites within an arm.

**Primary repository evidence:**

- `.github/workflows/integration-tests.yml` — GitHub services: containers, fresh per arm
- `.github/workflows/test.yml` — dump/restore snapshot shared across suites within an arm

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-009`.

## Q53 — Panther native ChromeDriver fallback on Windows

**Status:** LOCKED — amended by `ADR-DEV-001`

### Locked decision

Qualify and support **native Windows E2E/browser testing** using Panther with local Chrome/Chromium + ChromeDriver (or Panther-managed compatible driver behavior). Do not require Dockerized Selenium for developer E2E. The existing non-Grid Panther fallback is the preferred starting point and shall receive an explicit smoke/regression test. Containerized Selenium remains permitted in CI where available. Staging/production runtime remains Kubernetes/CRI, not Docker.

### Evidence/rationale

symfony/panther ^2.0 (composer.json:159). CI uses a SHA-pinned Selenium standalone-chromium at ci/compose-shared-selenium/docker-compose.yml:2-3, reached at http://selenium:4444/wd/hub (BaseTrait.php:26,37,72). A local-ChromeDriver fallback already exists at BaseTrait.php:79 (`static::createPantherClient()` when `SELENIUM_USE_GRID != true`) but is exercised by no workflow. The active GCE development machine cannot run Docker, so making the already-present native path a supported developer path removes a real blocker without creating a new browser-testing architecture from scratch.

**Primary repository evidence:**

- `composer.json:L159-L159` — symfony/panther ^2.0
- `tests/Tests/E2e/Base/BaseTrait.php:L79-L79` — untested local ChromeDriver fallback
- `ci/compose-shared-selenium/docker-compose.yml:L2-L3` — SHA-pinned Selenium image

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-001`.

# K — Miscellaneous Technical Decisions

## Q54 — Review tools/ directory

**Status:** LOCKED

### Locked decision

Accept `tools/` as upstream release/engineering tooling. Do not treat it as application runtime code. Reuse relevant release tooling when producing SaaS releases, without moving unrelated runtime logic into `tools/`.

### Evidence/rationale

19 tracked files, fully enumerated. 18 are the upstream release-automation toolkit under tools/release/ (bin/ CLI entrypoints: branch-to-version, create-tag, derive-prev-release, dispatch, render-pr-body, verify-tag; src/ supporting classes; one JSON contract schema). The 19th is tools/ci/analyze-flaky-tests.sh. No secrets, no dead scripts, no developer footguns.

**Primary repository evidence:**

- `evidence/manifests/q54-tools-inventory.txt` — complete 19-file listing

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q55 — wkhtmltopdf-openemr and oe-module-cqm transitive owners

**Status:** LOCKED

### Locked decision

The `wkhtmltopdf-openemr` and `oe-module-cqm` Composer repository entries are dead/non-installed in the audited dependency tree. They may be removed only through an upstream PR or a separately justified hygiene change; leaving them is acceptable because they have no runtime owner/cost today.

### Evidence/rationale

Verified against a populated vendor/ tree (unavailable to the prior audit): NEITHER package appears in vendor/composer/installed.json (247 packages installed). Both are declared only as VCS repositories (composer.json:161-165 and :166-169), are not in require, and are not in composer.lock. They are DEAD entries.

**Primary repository evidence:**

- `vendor/composer/installed.json` — neither package installed among 247 packages
- `composer.json:L161-L169` — declared as repositories only, never required

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `OPT-001`.

## Q56 — React 15 via napa - who consumes it?

**Status:** LOCKED

### Locked decision

Freeze inherited React 15 as a **legacy dependency with no new consumers**. Do not migrate it merely because it is old. Remove it only after a dedicated clean build and UI smoke test proves the build/runtime output remains correct without the Napa entry.

### Evidence/rationale

Consumer-graph tracing (evidence/snippets/q56-react15-consumer-graph.md) found the declaration and the napa download path but no confirmed runtime consumer in first-party code. Classification remains 'uncertain' rather than 'dead' because removal has not been proven not to change generated assets.

**Primary repository evidence:**

- `package.json` — React 15 declared via napa
- `evidence/snippets/q56-react15-consumer-graph.md` — declared -> downloaded -> no confirmed consumer

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `OPT-002`.

## Q57 — documents.id / insurance_companies.id without auto_increment

**Status:** LOCKED

### Locked decision

Do not convert `documents.id` or `insurance_companies.id` to `AUTO_INCREMENT`; their legacy application-assigned/shared-ID behavior is deliberate enough that changing it risks corruption. New `saas_*` tables use modern independent keys: numeric PK where appropriate plus a unique binary UUID, and never share ID spaces across unrelated tables.

### Evidence/rationale

TWO DIFFERENT ANSWERS. documents.id: deliberate-by-inertia; every writer allocates via QueryUtils::generateId() against the global sequences table (UPDATE sequences SET id=LAST_INSERT_ID(id+1), atomic on InnoDB). Conversion is technically safe (zero INSERT ... VALUES (0,...) sites). insurance_companies.id: deliberate AND LOAD-BEARING - documented at InsuranceCompanyService.php:433-436 as sharing an id-space with pharmacies via the addresses satellite table, whose foreign_id is a bare int with no type discriminator. Converting it in isolation would silently corrupt address lookups.

**Primary repository evidence:**

- `src/Services/InsuranceCompanyService.php:L433-L436` — documented shared id-space with pharmacies
- `src/Common/ORDataObject/ORDataObject.php:L80-L84` — id allocation via generateId on persist
- `src/BC/Database.php:L165-L165` — sequences-table allocation, race caveat in source

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-011`.

## Q58 — Formal upstream policy on custom_ prefix

**Status:** LOCKED

### Locked decision

Reserve the downstream prefix `saas_<domain>_` for SaaS-owned tables/objects. Do not use generic `custom_`. CI/rebase checks must detect any future upstream collision with `saas_`; an upstream documentation reservation request is desirable but not a blocker.

### Evidence/rationale

NOT RESERVED. Zero baseline tables, zero upgrade-file operations, zero module SQL and zero documentation use the custom_ prefix. Shipped modules use vendor-slug prefixes (weno_*, comlink_telehealth_*) or bare descriptive names. Reservation is de facto, never de jure. Separately, 'custom' already means 'third-party module directory' in interface/modules/custom_modules/ - a semantic collision.

**Primary repository evidence:**

- `evidence/snippets/q68-custom-prefix-evidence.md` — 5 independent channels all return zero custom_ usage
- `sql/database.sql` — no baseline table uses the prefix

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-008`, `OPT-004`.

## Q59 — sites/<tenant>/documents/theme/ per-site theme override path

**Status:** LOCKED

### Locked decision

`sites/<tenant>/documents/theme/` is **not** a runtime theme override. Per-tenant branding is shared immutable CSS/assets + validated CSS-variable design tokens + per-site logos through supported logo/site mechanisms. Arbitrary per-tenant CSS/JS is prohibited.

### Evidence/rationale

PREMISE REFUTED: sites/<tenant>/documents/theme/ has NO runtime behaviour whatsoever - 0 matches across **/*.php, **/*.twig and **/*.js. The string exists only in prior discovery documents. The actual per-tenant surface is: logos via LogoService (src/Services/LogoService.php:75-108) reading sites/<site>/images/logos/<type>/logo.*, legacy per-site images under OE_SITE_WEBROOT/images/, a per-site custom menu JSON, and sites/<site>/config.php (arbitrary PHP, interface/globals.php:649). NO per-site .css or .js is included at runtime anywhere.

**External/reconciliation note:** Repository correction: the previously assumed per-site theme path has no runtime behavior.

**Primary repository evidence:**

- `(git)` — zero runtime references to the theme override path
- `src/Services/LogoService.php:L75-L108` — actual per-tenant branding surface is logos only
- `interface/globals.php:L649-L649` — sites/<site>/config.php is the only per-site executable seam

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-010`.

## Q60 — DB DEFAULT CHARSET / COLLATION at CREATE TABLE level

**Status:** LOCKED

### Locked decision

Pin Saudi SaaS tenant schema and all `saas_*` DDL to `utf8mb4` / `utf8mb4_unicode_ci` unless a later explicit database ADR replaces it. Arabic sort/search/unique-index behavior must be verified against the actual provisioned production database engine before Go-Live.

### Evidence/rationale

Charset/collation evidence collected into evidence/manifests/q60-charset-collation.txt (per-table DEFAULT CHARSET occurrence counts in sql/database.sql plus the Installer's utf8mb4/COLLATE handling). The risk is real: if production MySQL has a different server default, Arabic sort order and case-folding change silently.

**Primary repository evidence:**

- `evidence/manifests/q60-charset-collation.txt` — charset/collation declarations and Installer handling

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-005`.

## Q61 — DWV DICOM viewer locale JSON

**Status:** LOCKED

### Locked decision

Provide an Arabic DWV locale overlay containing the required translation and overlay keys, with English fallback and RTL QA. Keep the translation change separate from any DWV version upgrade and contribute it upstream where feasible.

### Evidence/rationale

DWV 0.27.1 ships NINE locales - de, en, es, fr, it, jp, ro, ru, zh - and NO Arabic (no ar, ar-SA or ar_SA anywhere). Verified by fetching and inspecting the npm registry tarball, not by inference. Per-locale key counts are in evidence/manifests/dwv-locales.csv. An Arabic overlay needs 114 translation leaves plus 3 overlay leaves.

**Primary repository evidence:**

- `evidence/manifests/dwv-locales.csv` — 9 locales enumerated with key counts; no Arabic
- `evidence/raw/dwv-0.27.1.tgz` — upstream package inspected directly

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `P2-004`.

## Q62 — Full FHIR SearchParameter enumeration

**Status:** LOCKED

### Locked decision

Generate the full FHIR SearchParameter catalogue mechanically from source when NPHIES conformance work begins. The catalogue is a generated/diffable artifact across OpenEMR upgrades, not a manually maintained spreadsheet.

### Evidence/rationale

Structural census complete: 103 FHIR service files (evidence/manifests/q62-fhir-service-files.txt) containing 491 FhirSearchParameterDefinition registrations, alongside 90 REST controllers. The per-resource catalogue itself was not generated.

**Primary repository evidence:**

- `evidence/manifests/q62-fhir-service-files.txt` — 103 FHIR service files
- `evidence/raw/count-search_params.txt` — 491 SearchParameter registration sites

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-006`.

## Q63 — Numeric API versioning strategy

**Status:** LOCKED

### Locked decision

Do not retrofit numeric versions onto inherited OpenEMR REST/FHIR routes. New SaaS/NPHIES non-FHIR APIs use an explicit `/v1/` namespace; standard FHIR route/version semantics remain governed by their FHIR implementation.

### Evidence/rationale

There is NO numeric version segment: zero /v1/ matches across REST controllers and the route map. Versioning today is implicit in the pinned US Core route-map filename.

**Primary repository evidence:**

- `evidence/raw/count-api_version_seg.txt` — zero /v1/ segments in controllers or route map

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q64 — Application-tier rate limiting

**Status:** LOCKED

### Locked decision

Use edge/proxy rate limiting for volumetric abuse from Day-1. When OAuth partner integrations go live, add application-tier rate limiting keyed to authenticated client/tenant identity because the edge alone cannot enforce fair per-client quotas.

### Evidence/rationale

NO inbound rate limiting exists. All 17 'rate limit' matches are unrelated: PHPStan baselines, the fax module's OUTBOUND throttle to respect a vendor's limits (RCFaxClient.php:45,106,948,1063), GitHub API rate-limit messages in a release CLI, and a telemetry caching comment.

**Primary repository evidence:**

- `evidence/raw/count-rate_limit.txt` — 17 matches, none an inbound limiter
- `interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php:L45-L45` — outbound vendor throttle, not inbound limiting

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-004`.

## Q65 — BillingProcessor hookable extension points

**Status:** LOCKED

### Locked decision

Do not pretend BillingProcessor currently has an external module hook. Pursue a narrow upstream task-registry/factory + pre-dispatch extension point. Until available, the NPHIES MVP uses polling/worker orchestration outside the synchronous BillingProcessor.

### Evidence/rationale

Well-factored INTERNAL OOP hierarchy (ProcessingTaskInterface -> GeneratorInterface; AbstractProcessingTask -> AbstractGenerator) but ZERO external-module extension surface. Task selection is a hard-coded if/elseif ladder keyed on $_POST['bn_*'] at BillingProcessor.php:161-192 - no factory, registry, service-locator or event dispatch anywhere in src/Billing/. GeneratorExternal is a legacy include of custom/BillingExport.php (file not shipped; only a rename-me stub), NOT a supported module mechanism. No transaction boundary and no idempotency key: GeneratorX12.php:151,168 auto-commit per row.

**Primary repository evidence:**

- `src/Billing/BillingProcessor/BillingProcessor.php:L161-L192` — hard-coded task selection ladder
- `src/Billing/BillingProcessor/Tasks/GeneratorX12.php:L151-L168` — row-by-row auto-commit, no transaction
- `src/Billing/BillingProcessor/GeneratorInterface.php:L18-L18` — clean internal contract exists

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-007`, `PROD-006`.

## Q66 — claimrev-connect FHIR emission

**Status:** LOCKED

### Locked decision

Reuse ClaimRev only as a design pattern for polling, retries, state tracking, reconciliation and notifications. Do not reuse its X12 semantic/transport code as the NPHIES FHIR implementation.

### Evidence/rationale

Analysed from the actually-installed v2.1.6 source (134 files). Reuse matrix recorded in evidence/snippets/q66-claimrev-reuse-matrix.csv, classifying each component as direct_reuse / pattern_reuse_only / not_reusable, and distinguishing genuine FHIR R4 resources from generic 'claim' vocabulary, X12 claims, UI labels and plain REST transport.

**Primary repository evidence:**

- `evidence/snippets/q66-claimrev-reuse-matrix.csv` — per-component reuse classification
- `evidence/snippets/oe-module-claimrev-connect-source-inventory.md` — full installed source inventory

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-007`.

## Q67 — Full audit of SQL interpolation sinks and echo-vs-text ratio

**Status:** LOCKED

### Locked decision

A dedicated pre-production security triage is mandatory, prioritized around the identified JavaScript DOM sinks and dynamic/identifier SQL construction rather than an undifferentiated review of every call site. Critical/High findings must be remediated before production or receive explicit exceptional risk acceptance.

### Evidence/rationale

Census complete, triage NOT complete. Data-access surface: 6,785 sites (see Q11). Output escaping is genuinely strong: 13,590 uses of the project's escaping helpers (echo xlt( 9,476 + echo attr( 2,060 + echo text( 2,054) vs 369 raw htmlspecialchars, 32 Twig |raw, and ZERO Smarty nofilter. Residual XSS risk concentrates in 390 innerHTML and 18 document.write JavaScript sinks, which PHP-side helpers do not protect.

**Primary repository evidence:**

- `evidence/raw/remaining-counts.tsv` — full escaping and data-access census with saved match lists
- `evidence/raw/count-js_innerhtml.txt` — 390 innerHTML sinks - the actual worklist
- `evidence/raw/count-smarty_nofilter.txt` — zero Smarty nofilter

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-003`.

## Q68 — log.checksum chain verification cadence

**Status:** LOCKED

### Locked decision

Do not rely on OpenEMR's current per-row unkeyed checksum as tamper evidence. SaaS audit integrity requires a keyed HMAC using key material unavailable to the DB user, chaining to the previous record/batch, and an automated verifier. Implement this as a `saas_` audit overlay where possible.

### Evidence/rationale

It is NOT A CHAIN. LogTablesSink.php:63 computes hash('sha3-512', implode('', array_values($logData))) - the current row's own fields only. No previous-row hash (not a chain), no secret key (plain hash(), not hash_hmac). Zero scheduled verifier exists anywhere. EventAuditLogger.php:670-671 notes log.checksum is unused since 6.0, with the operative value in log_comment_encrypt.

**Primary repository evidence:**

- `src/Common/Logging/Audit/LogTablesSink.php:L63-L91` — unkeyed per-row hash, no chaining
- `src/Common/Logging/EventAuditLogger.php:L670-L671` — log.checksum unused since 6.0
- `(git)` — no verifier exists

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-001`.

## Q69 — Complete encryptStandard column inventory

**Status:** LOCKED

### Locked decision

Describe encryption accurately: inherited OpenEMR provides file/storage/credential/token encryption patterns, **not blanket column-level encryption of clinical PHI**. Pin inherited encryption globals on. If regulation/risk assessment requires column-level PHI encryption, scope it as a new feature with explicit field inventory and key-management design.

### Evidence/rationale

THE HEADLINE COUNT IS MISLEADING. Of 36 encryptStandard( sites, most are tests, PHPStan baselines and the crypto library's own interface. The real application write paths are only: SMART launch tokens (SMARTLaunchToken.php:132), key wrapping (CryptoGen.php:482), fax media tokens and vendor credentials (oe-module-faxsms), and the phone gateway password (reminders.php:399). NO CORE CLINICAL PHI COLUMN IS ENCRYPTED VIA THIS API - patient_data, form_encounter, billing and documents metadata are plaintext columns. What protects PHI at rest is drive_encryption (default ON, for files) and database_encryption (default ON, for the keys/audit-comment paths).

**Primary repository evidence:**

- `evidence/raw/count-encryptstandard.txt` — 36 sites; application paths are credentials/tokens only
- `src/FHIR/SMART/SMARTLaunchToken.php:L132-L132` — ephemeral launch token encryption
- `library/globals.inc.php:L1028-L1040` — drive_encryption + database_encryption both default ON

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `BLK-006`.

## Q70 — Vendored-vs-composer-installed oe-module-* runtime model

**Status:** LOCKED

### Locked decision

Git-tracked in-tree modules are authoritative; `claimrev-connect` is Composer-managed. CI must ensure no Composer package install path overlays a tracked module. Do not maintain duplicate copies of the same module under Git and Composer.

### Evidence/rationale

Resolved empirically on a populated vendor/ tree. The seven tracked modules are NOT in composer.lock, so composer install never touches them - git is authoritative. Only claimrev-connect is composer-managed, and .gitignore:15 excludes its directory precisely because composer owns it. There is no overlay of tracked files today: authority is cleanly partitioned, and no module exists twice.

**Primary repository evidence:**

- `vendor/composer/installed.json` — only claimrev-connect is an openemr-module package
- `.gitignore:L15-L15` — claimrev directory excluded because composer owns it
- `08-dependency-runtime-inventory.csv` — per-module authority table

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-008`.

## Q71 — Which module owns DICOM viewer include chain?

**Status:** LOCKED

### Locked decision

Treat DICOM viewing as inherited core document-viewer functionality, not a separate SaaS module. Arabic DWV assets from Q61 are delivered as a controlled asset overlay without pretending there is a dedicated DICOM module ownership boundary.

### Evidence/rationale

DICOM viewing is CORE document-viewer functionality, not a dedicated module. The PHP->JS include chain from the document viewer through dicom_launcher.js to DWV is traced in 13-localization-arabic-evidence.md, along with how the DWV runtime locale is selected.

**Primary repository evidence:**

- `library/js/dwv/` — DWV assets live in core, not in a module
- `13-localization-arabic-evidence.md` — full PHP->JS include chain and locale selection

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q72 — File-level vs line-level responsiveness coverage

**Status:** LOCKED

### Locked decision

Use the generated file-level RTL/mobile inventory as the QA worklist. Prioritize the legacy iframe population and custom-module screens; exclude the classified non-UI/backend population from visual QA unless a manual review reclassifies it.

### Evidence/rationale

The '611 files' figure is CORRECTED and replaced by a reproducible file-level inventory: 5,460 first-party UI files scanned (fingerprint sha256(q72-file-list.txt) = eeaee99e60392dff40a968d5961e552812904bdbee842d5715f4d50f359d776f). Classification reconciles to the row count: unknown/non-UI backend 3,111; shared_template 1,098; custom_module_screen 416; legacy_iframe_included_file 288; legacy_standalone_page 287; remainder per the summary. Exclusions are documented in evidence/raw/q72-scanner-exclusions.txt.

**Primary repository evidence:**

- `18-q72-ui-responsiveness-inventory.csv` — one row per file, 17 columns, 5,460 rows
- `19-q72-ui-responsiveness-summary.md` — reconciled totals with formulas
- `evidence/raw/q72-file-list.txt` — exact scanned set, sha256 fingerprinted

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `PROD-009`.

## Q73 — Scripted count of QueryUtils:: call sites

**Status:** LOCKED

### Locked decision

Accept the measured `QueryUtils::` and broader data-access census as the authoritative baseline for architecture sizing. The measured surface supports the locked DB-per-tenant decision and must be regenerated after major upstream upgrades if reconsidering tenancy.

### Evidence/rationale

QueryUtils:: = 1,653 call sites (previously uncounted). Full data-access surface: sqlStatement( 2,025, sqlQuery( 1,454, sqlFetchArray( 1,354, sqlInsert( 251, Doctrine DBAL 48 - total 6,785, plus 202 OE_SITE_DIR file-path sites. Every count has a saved match list and a SHA-256.

**Primary repository evidence:**

- `evidence/raw/count-queryutils.txt` — 1,653 QueryUtils:: sites with full match list
- `evidence/raw/remaining-counts.tsv` — all sink counts in one table
- `evidence/manifests/remaining-counts-sha256.txt` — checksums for reproducibility

### Implementation separated from this decision

No dedicated implementation item is required beyond normal enforcement/review of this locked rule.

## Q74 — tests/Tests/Integration/ tree scope

**Status:** LOCKED

### Locked decision

Treat `phpunit.integration.xml` as **vestigial in the audited baseline** because its referenced integration directories do not exist and no CI/script invokes it. Do not invest in reviving it. Create a clean SaaS integration-test tier when DB/API integration coverage is needed; propose upstream deletion separately.

### Evidence/rationale

VESTIGIAL - the directory DOES NOT EXIST. git ls-files 'tests/Tests/Integration/**' returns 0 rows and the path is absent on disk. phpunit.integration.xml:34,37 references two nonexistent directories. Zero invocations anywhere: 0 hits in .github/workflows/, composer.json scripts, package.json, devtools, bin/ and tools/. The 11 *IntegrationTest.php files that DO exist live under tests/Tests/{Common,RestControllers,Services}/ - a naming convention, not occupants of this config, and they run under phpunit.xml's existing suites.

**Primary repository evidence:**

- `(git)` — directory absent from the index
- `phpunit.integration.xml:L34-L37` — references two nonexistent directories
- `(git)` — zero invocations across workflows, composer, npm, devtools

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `OPT-003`.

## Q75 — phpunit-isolated.xml actually run in CI?

**Status:** LOCKED

### Locked decision

Treat `phpunit-isolated.xml` as a first-class CI gate. Add SaaS tests that require no DB/browser to the isolated suite and preserve its broad PHP-version matrix unless an upstream release changes supported PHP versions.

### Evidence/rationale

YES - a first-class gate. .github/workflows/isolated-tests.yml:50 runs `vendor/bin/phpunit -c phpunit-isolated.xml --coverage-clover=clover.xml --log-junit=junit.xml` on every push and PR to master and rel-*, across PHP 8.2/8.3/8.4/8.5/8.6 (the BROADEST matrix of any suite - only isolated tests cover 8.6), on ubuntu-24.04 with xdebug coverage, uploading Clover + JUnit to Codecov under isolated-php<ver> flags.

**Primary repository evidence:**

- `.github/workflows/isolated-tests.yml:L30-L52` — exact command, matrix and coverage upload
- `phpunit-isolated.xml:L32-L32` — no bootstrap, no DB, no browser required

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-009`.

# L — Branding Governance

Decisions Q76–Q77 were opened by the Group 1B–1D branding discovery audit
(`docs/rebranding.md`), which established that the Control-Plane-to-tenant branding materialisation
boundary and the Saudi theme surface were specified in intent but not in mechanism. Both are adopted here
so that implementation cannot select a divergent architecture.

## Q76 — Branding token materialisation boundary

**Status:** LOCKED

### Locked decision

The SaaS Control Plane PostgreSQL database is the authoritative source of tenant branding tokens and branding revisions.

Branding changes are validated in the Control Plane and then asynchronously/idempotently materialised into the target tenant's OpenEMR runtime representation.

OpenEMR MUST NOT perform a Control Plane network request during ordinary page rendering.

For OpenEMR values already represented by validated `globals`, the tenant MariaDB `globals` table is the runtime materialisation target, not the source of truth.

The materialisation process MUST be tenant-scoped and idempotent and MUST use the tenant's existing Control-Plane-managed provisioning/administrative credential boundary.

Shared theme CSS remains an immutable common artifact. Per-tenant arbitrary CSS, JavaScript or PHP MUST NOT be generated, stored, injected or executed.

Tenant design tokens are materialised only through the approved token schema and consumed through controlled CSS-variable/token surfaces.

Tenant logos remain isolated tenant assets; the Control Plane stores their approved metadata/reference and branding revision.

Every successful branding change increments or replaces a tenant branding revision. Cache keys for branding resources MUST incorporate a tenant-safe revision or asset-specific immutable/cache-busting identifier sufficient to prevent stale or cross-tenant branding.

If the Control Plane becomes unavailable, the tenant continues rendering the most recently successfully materialised branding state.

A failed materialisation MUST NOT partially apply a branding revision. The last known-good tenant branding state remains active and the failed revision is retryable/auditable.

### Evidence/rationale

OpenEMR reads its entire configuration with a single `SELECT gl_name, gl_index, gl_value FROM globals` once per request (`interface/globals.php:457`) and `OEGlobalsBag` implements no cache. Materialising validated values into `globals` therefore rides the existing query at zero additional per-request cost, whereas a runtime read-through design would add a cross-service call to a request path that has no cache layer to attach to, would require a change to OpenEMR's bootstrap (contrary to Invariant 4), and would place a Control Plane credential inside tenant request processing (contrary to the credential-isolation intent of Control Plane §8). Deploy-time-only materialisation was rejected because it cannot satisfy the `MVP-010` criterion that a tenant can change an approved logo and tokenised palette without a redeploy.

Logo resolution is already per-site and per-request through `LogoService` reading `OE_SITE_DIR`, with a per-file `?t=<mtime>` cache-busting parameter, so tenant logo isolation and cache-busting are achieved without new runtime dependencies.

**Primary repository evidence:**

- `interface/globals.php:L457-L457` — entire globals set read once per request; no cache layer exists
- `src/Services/LogoService.php:L75-L159` — per-site logo resolution with per-file cache-busting
- `interface/globals.php:L310-L322` — session is cleared when the requested site differs from the session site, preventing cross-tenant carry-over
- `interface/globals.php:L649-L649` — `sites/<site>/config.php` is the only per-site executable seam and is prohibited as a branding mechanism by Invariant 9 and `MVP-010`

**Cross-references:** Control Plane §2 (Control Plane stores branding tokens), Control Plane §8 (runtime credential isolation), Control Plane §10 (local resilience during Control Plane outage), Q34, Q59, Invariant 4, Invariant 9, `MVP-010`, `MVP-014`.

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-010` and `MVP-014`.

## Q77 — Saudi theme surface

**Status:** LOCKED

### Locked decision

The Saudi MVP ships exactly two supported application theme variants:

- Saudi Light;
- Saudi Dark.

Both MUST support RTL.

Required supporting non-user-selectable CSS artifacts such as tabs/shell, directional/RTL and PDF styles remain part of the build as technically required.

The inherited user-selectable themes `solar`, `manila`, `cobalt_blue`, and `forest_green` MUST NOT be included in the Saudi deployment build output.

Their user-selectable CSS artifacts MUST NOT exist in the deployed `public/themes/` directory.

They therefore MUST NOT appear in the Administration theme selector and MUST NOT remain selectable through stale `globals`, `user_settings`, manual DB values or direct filenames.

Runtime fallback for an invalid or unavailable theme MUST resolve to the approved Saudi Light theme, or the existing approved safe fallback explicitly selected by the implementation design.

The solution SHOULD be implemented at the build/deployment layer rather than by adding a recurring OpenEMR core selector patch.

Upstream source theme files may remain in the repository when needed for maintainability/rebase compatibility; the restriction applies to the Saudi product build/deployment output and supported product surface.

### Evidence/rationale

The Administration theme selector is a filesystem scan of `public/themes/` with an already-hardcoded exclusion list (`interface/super/edit_globals.php:714-732`), so suppressing a theme in the selector would require patching a core admin file on every rebase. More importantly, hiding a theme does not make it unselectable: the only runtime gate on a theme value is `file_exists()` (`interface/globals.php:476`), so a stale `globals` or `user_settings` value, or a direct database edit, would still resolve a hidden-but-present stylesheet. Omitting the artifacts from the build makes that same existing gate enforce the two-variant surface with no core patch, and `public/themes/*` is already build output rather than tracked source.

**Primary repository evidence:**

- `interface/super/edit_globals.php:L714-L732` — selector is a filesystem scan with a hardcoded exclusion list
- `interface/globals.php:L474-L483` — `file_exists()` is the only runtime gate; invalid values fall back
- `webpack.themes.js` — theme entry map is the build-layer control point for which variants compile

**Cross-references:** Q34, Q59, Invariant 4, `MVP-010`.

### Implementation separated from this decision

Implementation/verification is tracked outside this decision register under: `MVP-010`.

# Appendix A — Reconciled corrections to earlier drafts

- **Q10:** corrected from a supposed `context-standalone-encounter` advertising defect to the actual `context-ehr-encounter` / `launch/encounter` consistency problem.
- **Q47:** corrected because `drive_encryption` and `database_encryption` already default enabled; the locked action is provisioning assertion/health checking, not a core-default patch.
- **Q50:** corrected because `SECURITY.md` and Dependabot configuration already exist; the fork must correct the disclosure destination/process instead of creating duplicate files.
- **Q59:** corrected because `sites/<tenant>/documents/theme/` has no runtime theme-override behavior; branding is shared assets + validated tokens + site logos.
- **Q36 wording:** the locked claim is zero fork-owned tracked-module modifications, not an overbroad claim that every module is byte-identical against every possible upstream ref.
- **Docker/native-development amendment (ADR-DEV-001, 2026-08-09):** Native Apache + PHP + MariaDB/MySQL is the primary supported developer runtime; Docker Engine/Compose are optional in development/CI and cannot be required for local source builds or developer E2E. Release OCI images are built remotely in CI/CD. Staging/Production continue to use managed Kubernetes/Helm with a CRI runtime and signed/pinned OCI images.
- **Branding governance additions (Q76–Q77, Section L, 2026-08-09):** the Group 1B–1D branding discovery audit established that Control Plane §2 assigns branding-token authority to the Control Plane, and that Q34/Q59 define the permitted per-tenant branding surface, but that neither specified the materialisation mechanism nor the disposition of the four surplus inherited themes. `Q76` locks Control-Plane-authoritative push/sync materialisation with no per-request Control Plane dependency; `Q77` locks the two-variant Saudi theme surface enforced at the build/deployment layer. Neither reopens nor reinterprets Q34 or Q59; both extend them with the previously missing mechanism.

# Appendix B — Decision vs. implementation rule

Items such as React-removal build tests, Arabic collation tests, upload-call-site triage, SQL/DOM-sink review, SearchParameter catalogue generation, security scans, runtime capacity tests and legal retention-period values are **not open architectural questions**. The architecture is locked above; those items are implementation or acceptance work in the separate backlog.
