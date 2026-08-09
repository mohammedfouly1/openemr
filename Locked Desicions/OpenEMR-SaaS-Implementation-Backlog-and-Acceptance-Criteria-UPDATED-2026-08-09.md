# OpenEMR SaaS — Implementation Backlog & Acceptance Criteria

**Derived from:** `OpenEMR-SaaS-Locked-Decisions.md`  
**Revision date:** 2026-08-09  
**Purpose:** implementation, verification and acceptance work only. This file does not reopen Q1–Q75 except where the authoritative decision register explicitly records `ADR-DEV-001`; this backlog implements that locked amendment.

## Priority definitions

- **BLOCKER** — must be complete before any real tenant/PHI is allowed.
- **BEFORE MVP** — required for the intended Saudi SaaS MVP scope.
- **BEFORE PRODUCTION** — may follow MVP development but must pass before production Go-Live.
- **PHASE 2** — intentionally deferred product/modernization work.
- **OPTIONAL / HYGIENE** — improves maintainability/upstream cleanliness but does not block product delivery.

## Global Definition of Done

Every completed item must include: code/config change, automated test where technically possible, security/tenant-isolation review where relevant, documentation, reproducible deployment/migration instructions, rollback plan for production-impacting changes, and a link to the relevant Q/ADR. Development instructions must not assume a working local Docker Engine unless the item is explicitly optional/CI-only.

# BLOCKER

## BLK-001 — Build tamper-evident SaaS audit chain

**Related decisions:** Q68  
**Priority:** BLOCKER

Implement a SaaS audit overlay using HMAC with key material not readable by the tenant DB user, previous-record/batch chaining, key versioning and automated verification.

### Acceptance criteria

- [ ] Changing any protected historical event without the HMAC key is detected.
- [ ] Verifier reports chain break position and tenant without exposing PHI.
- [ ] Verification runs automatically and emits an operational alert on failure.
- [ ] Key rotation is supported without invalidating historical verification.
- [ ] Design and threat model are documented.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## BLK-002 — Harden trusted proxy/client-IP handling

**Related decisions:** Q42,Q6  
**Priority:** BLOCKER

Strip client-supplied forwarding headers at the edge and construct an authoritative chain only from trusted platform hops.

### Acceptance criteria

- [ ] Requests from the public internet cannot inject an audit-visible fake client IP through XFF.
- [ ] Trusted-proxy list is explicit and environment-controlled.
- [ ] Automated tests cover direct, single-proxy and multi-proxy cases.
- [ ] Audit logs record one normalized client address/chain according to policy.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## BLK-003 — Contain inherited exposed tokens

**Related decisions:** Q43  
**Priority:** BLOCKER

Prevent inherited upstream tokens/encoded variants from reaching SaaS repositories, CI logs or published images; report upstream exposure.

### Acceptance criteria

- [ ] Secret scanner detects raw, base64 and decimal/encoded token forms used by the audited files.
- [ ] Published OCI image scan contains no discovered token value/variant.
- [ ] CI fails on a seeded representative token fixture.
- [ ] Incident/upstream-report record exists without storing token values.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## BLK-004 — Implement secure upload pipeline

**Related decisions:** Q49  
**Priority:** BLOCKER

Add fail-closed file validation and malware scanning before documents become available to users/workflows.

### Acceptance criteria

- [ ] `secure_upload=1` is asserted by provisioning/health checks.
- [ ] Magic-byte/MIME mismatch is rejected.
- [ ] Malware test sample is quarantined/rejected and audited.
- [ ] Size/path/filename controls are tested.
- [ ] All 26 audited `createDocument` call sites are triaged and either routed through the gate or explicitly justified.
- [ ] DICOM upload write-order question is resolved with a regression test.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## BLK-005 — Enforce tenant routing boundary

**Related decisions:** Q11,Q12,Q17  
**Priority:** BLOCKER

Implement authoritative hostname→tenant resolution and prevent request/query/session manipulation from switching tenant context.

### Acceptance criteria

- [ ] Public `?site=` cannot change tenant.
- [ ] Tenant A hostname cannot load Tenant B database/config even with crafted request/session data.
- [ ] Cookie scope is tenant-specific.
- [ ] Negative cross-tenant routing tests run in CI/E2E.
- [ ] Audit identifies tenant_id on each request.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## BLK-006 — Enforce and document encryption baseline

**Related decisions:** Q47,Q69  
**Priority:** BLOCKER

Make inherited encryption configuration explicit and document what is and is not encrypted.

### Acceptance criteria

- [ ] Provisioning explicitly sets drive/database encryption on.
- [ ] Tenant health check fails closed or marks tenant unhealthy if disabled.
- [ ] Security documentation explicitly states that core clinical PHI columns are not automatically column-encrypted.
- [ ] Backup/object/block-storage encryption is verified in infrastructure controls.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

# BEFORE MVP

## MVP-001 — Establish native development and production runtime split

**Related decisions:** Q3,Q39,Q41,Q53,ADR-DEV-001  
**Priority:** BEFORE MVP

The primary developer setup runs OpenEMR directly on Apache + PHP + MariaDB/MySQL without Docker. Docker/Compose is optional developer/CI tooling only. Production delivery uses remotely built, signed OCI images and managed Kubernetes/Helm.

### Acceptance criteria

- [ ] A clean developer Windows Server/VM can install and run the audited OpenEMR baseline natively using Apache + PHP + MariaDB/MySQL without Docker Engine or Docker Compose.
- [ ] Native setup instructions pin/document required PHP extensions/settings, Apache settings, MariaDB/MySQL settings, filesystem permissions, and the repository-required Composer + Node.js/npm build toolchain.
- [ ] `composer install`/approved dependency install, frontend asset build, OpenEMR bootstrap/install and a documented application smoke test succeed on the native developer runtime.
- [ ] Required non-browser SaaS tests can run without local Docker; isolated tests remain a first-class gate.
- [ ] At least one representative DB-backed SaaS integration test can run against a disposable/native MariaDB test database without local Docker. CI may continue to use ephemeral database service containers.
- [ ] Panther/browser smoke testing works through the native non-Grid Chrome/Chromium + ChromeDriver path on the development host; Dockerized Selenium is not required locally.
- [ ] Docker Engine/Compose remains optional where available and is not a prerequisite documented by the SaaS developer bootstrap.
- [ ] CI/CD can build the production OCI image remotely (initially Google Cloud Build and/or approved hosted CI) and publish it to the private registry without Docker Engine on the developer VM.
- [ ] Native-development configuration is checked for functional parity with the release image for PHP extensions, required application dependencies, database charset/collation assumptions and SaaS configuration that can materially affect behavior.
- [ ] No staging/production manifest invokes Docker Engine or Docker Compose.
- [ ] Kubernetes nodes use an approved CRI runtime (for example containerd).
- [ ] Release artifact is referenced by immutable OCI digest.
- [ ] Helm release is versioned and linked to the application release.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-002 — Implement Keycloak OIDC federation

**Related decisions:** Q4,Q5,Q6,Q7,Q16  
**Priority:** BEFORE MVP

Build the RP flow, local-user mapping and tenant membership gate while retaining local authorization.

### Acceptance criteria

- [ ] Clinical/admin login requires Keycloak MFA.
- [ ] External subject maps deterministically to the correct tenant-local OpenEMR user.
- [ ] A Keycloak identity not entitled to the tenant cannot establish a tenant session.
- [ ] REMOTE_USER/header authentication is not accepted.
- [ ] Break-glass native path is disabled for normal use and separately audited.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-003 — Correct vulnerability disclosure routing

**Related decisions:** Q50  
**Priority:** BEFORE MVP

Replace upstream disclosure contact text with the SaaS project's own security process before public exposure.

### Acceptance criteria

- [ ] SECURITY.md names the approved project channel/process.
- [ ] No instruction routes project-specific vulnerability reports solely to upstream OpenEMR.
- [ ] Disclosure contact/process is tested/reviewed by security ownership.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-004 — Deliver Arabic baseline

**Related decisions:** Q18,Q22,Q23,Q24,Q25,Q35  
**Priority:** BEFORE MVP

Complete the initial MSA/RTL localization baseline and deterministic Arabic PDF/editor behavior.

### Acceptance criteria

- [ ] Arabic catalogue coverage reaches the agreed MVP list; missing strings are tracked.
- [ ] RTL light/dark themes render without layout-breaking defects on priority screens.
- [ ] Bootstrap-RTL build dependency is vendored/controlled.
- [ ] Arabic PDF sample renders connected glyphs correctly using bundled fonts.
- [ ] CKEditor enters/displays Arabic RTL correctly.
- [ ] Site timezone is Asia/Riyadh and language remains per-user.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-005 — Saudi money/charset baseline

**Related decisions:** Q20,Q26,Q60  
**Priority:** BEFORE MVP

Make SAR and Arabic-safe database behavior explicit.

### Acceptance criteria

- [ ] Saudi tenant displays/persists currency as SAR where operational currency is required.
- [ ] Runtime FHIR currency no longer depends on a hard-coded USD assumption for Saudi tenants.
- [ ] `saas_*` DDL uses utf8mb4/utf8mb4_unicode_ci.
- [ ] Arabic sort, search and unique-index integration tests pass on the selected production DB engine.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-006 — Correct SMART capability/scope consistency

**Related decisions:** Q10  
**Priority:** BEFORE MVP

Align advertised SMART encounter capability with grantable scopes/context behavior.

### Acceptance criteria

- [ ] Capability statement and grantable scope lists are mutually consistent.
- [ ] A conformance test proves the advertised launch mode works, or the unsupported capability is absent.
- [ ] Documentation examples use a scope combination that can actually be granted.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-007 — Create NPHIES MVP worker boundary

**Related decisions:** Q27,Q29,Q31,Q65,Q66  
**Priority:** BEFORE MVP

Implement polling/worker orchestration with a dedicated NPHIES partner model and no ClaimRev semantic reuse.

### Acceptance criteria

- [ ] `saas_nphies_partners` exists with secret references rather than secrets.
- [ ] Worker is idempotent under retry/restart.
- [ ] Submission/reconciliation state is queryable and auditable.
- [ ] ClaimRev is disabled by default for Saudi tenants.
- [ ] No X12 transport is treated as NPHIES FHIR.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-008 — Enforce module/table namespace safety

**Related decisions:** Q37,Q38,Q58,Q70  
**Priority:** BEFORE MVP

Prevent module-install overlays, Twig collisions and downstream schema-name collisions.

### Acceptance criteria

- [ ] CI detects a Composer install path colliding with a tracked module.
- [ ] All SaaS Twig templates resolve through `@<module_slug>/`.
- [ ] All new SaaS DB objects use the `saas_` prefix.
- [ ] Upstream rebase check reports any new upstream `saas_` collision.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-009 — Set CI quality gates

**Related decisions:** Q51,Q52,Q75  
**Priority:** BEFORE MVP

Establish SaaS coverage and isolated-test gates without blocking on inherited whole-tree coverage.

### Acceptance criteria

- [ ] SaaS PR patch coverage >=80% unless an approved exception exists.
- [ ] SaaS module total coverage >=60% for measured module scope.
- [ ] DB tests use a fresh database per job.
- [ ] Non-DB SaaS tests execute through the isolated suite.
- [ ] Coverage is reported separately from upstream aggregate.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-010 — Implement safe tenant branding

**Related decisions:** Q34,Q59,Q76,Q77  
**Priority:** BEFORE MVP

Build validated design-token/logo branding without tenant CSS/JS execution, materialised from the Control Plane per `Q76`, on the two-variant Saudi theme surface per `Q77`.

### Acceptance criteria

- [ ] Tenant can change approved logo and tokenized palette only.
- [ ] Invalid CSS/value payloads are rejected.
- [ ] No tenant-uploaded CSS/JS is executed.
- [ ] Cache keys/revisions prevent one tenant's branding from appearing in another tenant.
- [ ] (Q76) No Control Plane network request occurs during ordinary OpenEMR page rendering.
- [ ] (Q76) Branding values reach the tenant only by tenant-scoped, idempotent materialisation; `globals` is a materialisation target, never the source of truth.
- [ ] (Q76) With the Control Plane unavailable, the tenant continues rendering its last successfully materialised branding state; a failed materialisation leaves the previous revision fully intact.
- [ ] (Q77) The deployed `public/themes/` contains only the Saudi Light and Saudi Dark user-selectable variants (plus required non-selectable shell/RTL/PDF artifacts); `solar`, `manila`, `cobalt_blue` and `forest_green` are absent and unselectable, including via stale `globals`/`user_settings` values.
- [ ] (R-SMART-DARK) The SMART style token endpoint returns dark tokens for a dark theme; it currently returns light tokens for every theme.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-011 — Define new SaaS key conventions

**Related decisions:** Q57  
**Priority:** BEFORE MVP

Document and enforce new-table PK/UUID/reference rules while leaving legacy IDs unchanged.

### Acceptance criteria

- [ ] No migration changes `documents.id` or `insurance_companies.id` semantics.
- [ ] New `saas_*` tables follow the documented independent key strategy.
- [ ] Cross-table/cross-tenant references use typed source identifiers or UUIDs, never accidental shared numeric id spaces.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-012 — Add KMS custody wrapper

**Related decisions:** Q44  
**Priority:** BEFORE MVP

Introduce per-tenant key custody/rotation around inherited site-key semantics.

### Acceptance criteria

- [ ] Tenant key is referenced/versioned through KMS/secret manager.
- [ ] Tenant A key cannot decrypt Tenant B protected material.
- [ ] Rotation and restore are exercised in a non-production tenant.
- [ ] Deletion protection and break-glass key recovery procedure are documented.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-013 — Implement ZATCA-ready invoice data model

**Related decisions:** Q21  
**Priority:** BEFORE MVP

Persist the tax/invoice identity and security data required so Phase-2 integration does not require a schema redesign.

### Acceptance criteria

- [ ] Invoice/tax model supports the fields needed by the selected ZATCA specification.
- [ ] QR/Phase-1 output can be generated from the same canonical invoice model.
- [ ] Phase-2 integration identifiers/status can be persisted.
- [ ] ZATCA technical conformance test plan exists before production.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## MVP-014 — Build Control Plane foundation

**Related decisions:** Control Plane,Q11-Q17,Q76  
**Priority:** BEFORE MVP

Create the separate control-plane service/database and tenant provisioning state model.

### Acceptance criteria

- [ ] Managed PostgreSQL 18 current minor is provisioned.
- [ ] No clinical PHI tables exist in the control-plane schema.
- [ ] Tenant/domain/subscription/membership/deployment/feature/branding/provisioning models exist.
- [ ] OpenEMR runtime has no direct control-plane DB credential.
- [ ] Secrets stored as references only.
- [ ] Tenant runtime continues essential local operation during a simulated control-plane outage.
- [ ] (Q76) The branding model stores authoritative tokens, approved logo references and a per-tenant branding revision, and exposes a tenant-scoped idempotent materialisation path that runs outside the OpenEMR request path.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

# BEFORE PRODUCTION

## PROD-001 — Implement retention matrix and pruner/archive service

**Related decisions:** Q48  
**Priority:** BEFORE PRODUCTION

Turn retention into a governed data-class matrix and background process.

### Acceptance criteria

- [ ] Matrix explicitly covers medical records, security logs, NPHIES payloads, API bodies, backups, technical logs and legal hold.
- [ ] Deletion/archival is tenant-aware and audited.
- [ ] Legal hold prevents disposal.
- [ ] Retention jobs are testable in dry-run mode.
- [ ] Periods are approved by Legal/DPO/records governance rather than hard-coded from an assumption.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## PROD-002 — Implement break-glass workflow

**Related decisions:** Q46  
**Priority:** BEFORE PRODUCTION

Add justification, re-authentication, minimal privilege, expiry and review.

### Acceptance criteria

- [ ] Reason and incident/reference are mandatory.
- [ ] Strong re-authentication/MFA occurs immediately before activation.
- [ ] Privilege is time-bounded and minimal.
- [ ] Every activation/action is audit-visible.
- [ ] Post-use notification/review is generated.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## PROD-003 — Run focused application security sprint

**Related decisions:** Q67  
**Priority:** BEFORE PRODUCTION

Triage the saved high-risk JS/SQL worklists and remediate material findings.

### Acceptance criteria

- [ ] All 408 identified JS sinks receive classification or remediation.
- [ ] Dynamic/identifier SQL sinks are triaged with parameterization/allowlisting evidence.
- [ ] All Critical/High findings are closed or exceptional risk acceptance is signed by accountable security leadership.
- [ ] Regression tests cover remediated classes of defect.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## PROD-004 — Add partner-aware application rate limiting

**Related decisions:** Q64  
**Priority:** BEFORE PRODUCTION

Add tenant/client-aware quotas once partner/OAuth integrations are live.

### Acceptance criteria

- [ ] Limits key on authenticated tenant/client rather than IP alone.
- [ ] Burst and sustained quotas are documented.
- [ ] Internal jobs/health checks have controlled exemptions.
- [ ] Redis/backend failure behavior is explicitly fail-open/fail-closed per endpoint class.
- [ ] 429 responses include usable retry semantics.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## PROD-005 — Complete data-residency and transfer-control implementation

**Related decisions:** Q45  
**Priority:** BEFORE PRODUCTION

Enforce KSA-resident default and formal approval path for exceptions.

### Acceptance criteria

- [ ] Production tenant DB, PHI documents, PHI backups and production key custody are in approved KSA regions.
- [ ] Telemetry/logging inventory identifies any potential cross-border processor.
- [ ] Cross-border route is disabled by default.
- [ ] Any exception has legal basis, DPO approval, transfer risk assessment and safeguards documented.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## PROD-006 — Complete NPHIES FHIR write surface and conformance

**Related decisions:** Q28,Q30,Q62,Q65  
**Priority:** BEFORE PRODUCTION

Implement required NPHIES resource mapping/services and generated conformance catalogue.

### Acceptance criteria

- [ ] Required Claim/ClaimResponse/Coverage/Eligibility/EOB/PaymentNotice flows are mapped to the applicable NPHIES profiles.
- [ ] Existing generated R4 model classes are reused where compatible.
- [ ] SearchParameter catalogue is generated/diffable.
- [ ] Polling retry/reconciliation tests cover duplicate, timeout, partial failure and late response.
- [ ] Conformance test artifacts are retained per release.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## PROD-007 — Production supply-chain hardening

**Related decisions:** Q41,Q39  
**Priority:** BEFORE PRODUCTION

Sign, scan and pin application/infra artifacts.

### Acceptance criteria

- [ ] OCI image referenced by digest, not mutable tag.
- [ ] Image signature verification is enforced before deployment.
- [ ] SBOM and vulnerability scan are attached to the release.
- [ ] Helm/chart provenance/version is linked to the app release.
- [ ] No production node requires Docker Engine.
- [ ] Release-image build evidence comes from approved CI/CD; production acceptance does not depend on a developer-local Docker build.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## PROD-008 — Capacity and operational isolation tests

**Related decisions:** Q13,Q14  
**Priority:** BEFORE PRODUCTION

Validate multi-tenant operational assumptions under representative load.

### Acceptance criteria

- [ ] Load test covers projected tenant count/active-user profile.
- [ ] DB connection/worker ceilings are documented and alerts exist.
- [ ] Background jobs are consolidated before scale threshold is reached.
- [ ] Observability data is tagged by tenant without leaking PHI.
- [ ] Tenant failure does not cascade to unrelated tenants beyond documented shared dependencies.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## PROD-009 — RTL/mobile regression qualification

**Related decisions:** Q72  
**Priority:** BEFORE PRODUCTION

Use the generated file-level inventory as the test scope for priority inherited UI.

### Acceptance criteria

- [ ] Legacy iframe population and custom module screens have assigned QA status.
- [ ] Critical clinical/billing/admin workflows pass desktop + mobile-width + RTL tests.
- [ ] Known defects are linked to backlog with severity/owner.
- [ ] Non-UI exclusions are reproducible from the saved inventory.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

# PHASE 2

## P2-001 — Greenfield patient-portal SPA / selective modern UI

**Related decisions:** Q32,Q33  
**Priority:** PHASE 2

Introduce the new portal and modern module surfaces only behind stable APIs and incremental cutover.

### Acceptance criteria

- [ ] Existing portal remains functional until tenant-specific cutover.
- [ ] New SPA does not require direct DB access.
- [ ] API authorization is tenant-aware.
- [ ] Rollback/cutover plan exists.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## P2-002 — Expand Hijri UX

**Related decisions:** Q19  
**Priority:** PHASE 2

Expand dual-calendar support based on validated Saudi workflows after the MVP surfaces are stable.

### Acceptance criteria

- [ ] Field/surface inventory defines where Hijri input/display is allowed.
- [ ] Canonical Gregorian persistence remains unchanged.
- [ ] Round-trip tests cover Umm al-Qura boundary dates and original-input audit metadata.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## P2-003 — Build cross-tenant analytics warehouse

**Related decisions:** Q15  
**Priority:** PHASE 2

Create governed ETL/warehouse rather than querying tenant DBs directly.

### Acceptance criteria

- [ ] Warehouse keys include tenant identity.
- [ ] No dashboard query joins live tenant databases.
- [ ] PHI minimization/role model is documented.
- [ ] Tenant deletion/retention obligations propagate to warehouse data.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## P2-004 — Modernize RTL/DICOM dependencies

**Related decisions:** Q24,Q61  
**Priority:** PHASE 2

Evaluate Bootstrap native RTL modernization and a supported DWV upgrade separately from localization patches.

### Acceptance criteria

- [ ] Upgrade compatibility matrix exists.
- [ ] Arabic locales remain complete after upgrade.
- [ ] Regression tests cover DICOM launch/view and core RTL workflows.
- [ ] Dependency upgrade can be reverted independently of translation content.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

# OPTIONAL / HYGIENE

## OPT-001 — Remove dead Composer repository entries upstream-first

**Related decisions:** Q55  
**Priority:** OPTIONAL / HYGIENE

Submit an upstream hygiene PR; carry locally only if there is an actual maintenance/security benefit.

### Acceptance criteria

- [ ] Composer install/update behavior is unchanged in a clean build.
- [ ] No package resolution depends on the removed repository entries.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## OPT-002 — Prove/remove React 15

**Related decisions:** Q56  
**Priority:** OPTIONAL / HYGIENE

Run a dedicated off-branch/clean build with and without the Napa React entry.

### Acceptance criteria

- [ ] Build succeeds both ways.
- [ ] Generated asset diff is understood and acceptable.
- [ ] UI smoke suite passes without React 15 before removal PR is merged.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## OPT-003 — Propose upstream deletion of vestigial integration config

**Related decisions:** Q74  
**Priority:** OPTIONAL / HYGIENE

Remove `phpunit.integration.xml` upstream only after confirming current upstream still has no consumers.

### Acceptance criteria

- [ ] Fresh upstream search finds no referenced directories or workflow invocation.
- [ ] Upstream CI passes after deletion.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## OPT-004 — Request upstream documentation reservation for `saas_`

**Related decisions:** Q58  
**Priority:** OPTIONAL / HYGIENE

Ask upstream to document downstream/custom prefix guidance without blocking the product.

### Acceptance criteria

- [ ] PR/issue clearly states collision rationale.
- [ ] Local CI collision check remains authoritative regardless of upstream response.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

## OPT-005 — Periodic Inferno regression run

**Related decisions:** Q40  
**Priority:** OPTIONAL / HYGIENE

Run optionally when useful as a FHIR regression signal.

### Acceptance criteria

- [ ] Result is informational and does not block Saudi release unless a later contract changes Q40.

### Closure evidence

- [ ] PR/commit or infrastructure change reference recorded.
- [ ] Automated/manual test evidence attached.
- [ ] Relevant security/tenant-isolation impact reviewed.
- [ ] Documentation/runbook updated.

# Appendix — Verification items explicitly moved out of the decision register

The following are verification tasks, not open decisions: React 15 removal proof (Q56), production Arabic collation test (Q60), complete upload caller triage (Q49), security sink triage (Q67), generated FHIR SearchParameter catalogue (Q62), runtime capacity testing (Q13), and static-audit gaps requiring DB/API/E2E execution.
