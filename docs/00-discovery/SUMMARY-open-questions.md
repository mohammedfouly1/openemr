# Discovery Phase 0 — Consolidated Open Questions

Synthesised from all 16 prior reports (`00-environment.md` through `15-upgrade-and-patch-strategy.md`). Each item is a decision or unknown that must be resolved before Document 0 (the locked-decisions summary) can ship. Numbering is continuous across sections.

Answer format: append an `### A<n>` fenced block under each question with author + date.

---

## A — Foundational locks (must be answered before Document 0 ships)

### Q1 — Add `upstream` remote and measure drift
**Source:** `01-repo-inventory.md` §Fork Identity (L15, L138–145); reinforced in `15-upgrade-and-patch-strategy.md` UNKNOWN #1 (L443).
**Question:** Do we add `upstream = https://github.com/openemr/openemr.git` to the fork and, if so, what upstream ref do we peg to — `master` (rolling) or the latest release tag (e.g. `v7.0.3`)?
**Trade-off context:** Without an upstream remote we cannot compute drift, cannot diff the seven in-tree `oe-module-*` against upstream, and cannot run the rebase procedure sketched in `15 §5.1`. Pegging to `master` maximizes freshness but accepts pre-release churn; pegging to release tags gives a stable base at the cost of delayed security fixes. This blocks every "class-b upstream PR vs class-c local hotfix" call in the patch strategy.
**Suggested default (if PO does not decide):** SAFE-DEFAULT — add `upstream` remote pointing at `openemr/openemr`, track the latest release tag, and re-peg per rebase cycle.

### Q2 — Upstream rebase cadence
**Source:** `15-upgrade-and-patch-strategy.md` §3, §5 (L441+).
**Question:** How often do we rebase the fork onto upstream — monthly, per upstream release, or opportunistic (security-driven only)?
**Trade-off context:** Faster cadence = smaller merge conflicts per rebase but higher steady-state engineering cost; slower cadence = larger, riskier merges but fewer interruptions. This decision drives CI budget, on-call rota, and whether we can afford class-c hotfixes (which must be re-applied every rebase).
**Suggested default:** PROVISIONAL — rebase per upstream point release (roughly quarterly) plus out-of-band for any upstream security advisory.

### Q3 — Target production orchestration platform
**Source:** `11-devops-docker-ci.md` UNKNOWN #1 (L299); `15 §5.1`; `10 §7`.
**Question:** Is production Kubernetes (with Helm), bare Docker Compose, Docker Swarm, or Nomad?
**Trade-off context:** No Helm/k8s artifacts exist in the repo today (`11 §k8s row`). K8s gives autoscaling and multi-node HA at the cost of ops complexity; Compose is what the dev-easy stack already uses and what every in-tree runbook assumes. This gates whether we own a `charts/` tree in-fork or in a separate infra repo (see Q31), and whether `meta/` k8s probes get first-class support.
**Suggested default:** PROVISIONAL — Kubernetes with Helm, charts in a **separate** infra repo.

---

## B — Identity & authorization

### Q4 — Central IdP choice
**Source:** `05-auth-and-acl.md` §5-line summary L609–613; UNKNOWN #3 (L575).
**Question:** Do we adopt Keycloak, Authentik, Azure AD B2C, or stay on OpenEMR's native `AuthUtils` login?
**Trade-off context:** OpenEMR's own OIDC provider (`league/oauth2-server` at `src/FHIR/SMART/`) can federate authentication cleanly but authorization still needs `users` rows + `gacl_aro*` per tenant DB (Q10). A central IdP moves password/MFA policy out of the app; staying native avoids introducing a new SPOF. Keycloak is the most common OSS choice; Azure AD B2C is Saudi-cloud friendly.
**Suggested default:** PROVISIONAL — Keycloak fronting OpenEMR (OpenEMR remains an OIDC RP), keep `AuthUtils` as break-glass fallback.

### Q5 — Force MFA org-wide
**Source:** `05-auth-and-acl.md` UNKNOWN #1 (L569); §MFA row L129.
**Question:** Must we enforce MFA for every user, or leave enrollment opt-in as OpenEMR ships today?
**Trade-off context:** Today MFA is per-user opt-in (no `gbl_force_mfa` global). Saudi PDPL and general SaaS hygiene push toward mandatory MFA. If Q4 elects an external IdP, MFA is enforced there; if native, we own a small `globals.php` patch plus a rollout plan for existing users.
**Suggested default:** SAFE-DEFAULT — force MFA for all clinical/admin roles from Day 1; patients optional.

### Q6 — Reverse-proxy trust for `REMOTE_USER`
**Source:** `05-auth-and-acl.md` UNKNOWN #3 (L575); §L515.
**Question:** Do we trust `REMOTE_USER` / `mod_auth_openidc` headers from an upstream proxy?
**Trade-off context:** This is the mechanism that lets an external IdP short-circuit `AuthUtils`. Trusting proxy headers is powerful but is a total-compromise vector if the proxy is bypassable; needs strict network segmentation. Ties directly to Q4.
**Suggested default:** PROVISIONAL — yes, but only if Q4 = external IdP and the app tier is only reachable via the proxy (private network / mTLS).

### Q7 — Retire Google Sign-In path?
**Source:** `05-auth-and-acl.md` UNKNOWN #6 (L583).
**Question:** Do we keep the `google_signin_*` code paths (Google Workspace login) or remove them in favour of the central IdP chosen in Q4?
**Trade-off context:** Google Sign-In is a working federation template — useful to keep as a reference implementation. Saudi customers are unlikely to use it. Removing it shrinks attack surface; keeping it costs almost nothing.
**Suggested default:** SAFE-DEFAULT — keep in code, disable via global, remove only after Q4 IdP is proven.

### Q8 — Tenant-admin UI for OAuth2 clients
**Source:** `05-auth-and-acl.md` UNKNOWN #2 (L571).
**Question:** Do we need an admin UI to create/edit `oauth_clients` rows directly (beyond the current revoke/inspect at `ClientAdminController.php`), or is Dynamic Client Registration (DCR) sufficient?
**Trade-off context:** DCR covers most partner-integrator flows. Hand-provisioning is needed for regulated integrations that cannot self-register. Building the UI is a small module; skipping it forces support staff into DB edits.
**Suggested default:** PROVISIONAL — DCR only for Day 1; build the UI when the first hand-provisioned partner appears.

### Q9 — Fork ACL policy: bump `$v_acl=14` or freeze at 13?
**Source:** `05-auth-and-acl.md` UNKNOWN #4 (L577); `15-upgrade-and-patch-strategy.md` UNKNOWN #7 (L471).
**Question:** For downstream-only ACL changes, do we ever author a `$v_acl = 14` upgrade block, or freeze at 13 and layer central-identity authz above `gacl`?
**Trade-off context:** Bumping the counter for downstream reasons collides on rebase when upstream bumps 13→14 for its own reasons (same class of failure as `$v_database`). Freezing at 13 forces us to model tenant/role changes outside gacl — cleaner long-term, but requires a shim layer. `15 §3` currently marks the never-bump rule as "absolute."
**Suggested default:** SAFE-DEFAULT — freeze at 13; all new authorization goes into a `custom_saas_*` layer above gacl.

### Q10 — Complete standalone-encounter SMART launch
**Source:** `05-auth-and-acl.md` UNKNOWN #5 (L580).
**Question:** Is finishing the advertised-but-unimplemented standalone-encounter SMART launch (CapabilityStatement advertises it, only patient selector exists) a deliverable?
**Trade-off context:** Missing this puts us out of spec with any SMART app that needs encounter context. Cost is bounded (an encounter picker + launch context wiring). Skipping it means the CapabilityStatement claim is a lie — which some conformance testers flag.
**Suggested default:** PROVISIONAL — implement in the same phase we tackle NPHIES claim flows (Q19), since both touch encounter binding.

---

## C — Tenancy model

### Q11 — DB-per-tenant vs shared-DB
**Source:** `10-multisite-multitenant.md` §Model A/B (L441–442).
**Question:** Do we adopt Model A (native multisite + provisioning automation, one DB per tenant) or Model B (shared DB with `tenant_id` column)?
**Trade-off context:** Model A is what the runtime already supports — small `globals.php` patch for subdomain routing, per-site workers, per-site backups. Model B is a **~1,875 `sqlStatement(` call-site rewrite** across 453 files (lower bound; `QueryUtils::` uncounted), plus schema migration, plus every service-layer join. Model A costs ops burden per tenant; Model B costs a multi-quarter refactor.
**Suggested default:** SAFE-DEFAULT — Model A (DB-per-tenant).

### Q12 — Tenant routing scheme
**Source:** `10-multisite-multitenant.md` §L344, §L404; `14-security-compliance.md` UNKNOWN L725.
**Question:** Subdomain routing (`<tenant>.example.sa`), path-based (`/tenant/<name>`), or the raw `?site=<name>` query param OpenEMR ships?
**Trade-off context:** `?site=` works out of the box but leaks tenant into every URL and interacts badly with cookie scope. Subdomains give clean isolation, per-tenant TLS, and are what most SaaS peers do — but require wildcard DNS + wildcard cert + a small `globals.php` patch. Path-based avoids DNS complexity but complicates cookie scoping (see Q17).
**Suggested default:** SAFE-DEFAULT — subdomain-per-tenant.

### Q13 — Tenant-count ceiling
**Source:** `10-multisite-multitenant.md` UNKNOWN L446.
**Question:** What is the design ceiling for concurrent tenants — 50, 500, 5,000?
**Trade-off context:** Under Model A the practical bounds are (a) filesystem entries under `sites/`, (b) MySQL connection pool, (c) N-cronjobs × N-tenants ops burden. 50 tenants is trivial; 500 needs a jobs consolidator; 5,000 pushes us toward Model B or per-shard cron. Setting the ceiling early prevents late-stage re-architecture.
**Suggested default:** PROVISIONAL — design for 500 tenants, revisit at 200.

### Q14 — Per-tenant Apache/TLS/observability config location
**Source:** `10-multisite-multitenant.md` UNKNOWN L447.
**Question:** Do per-tenant vhost / TLS / log-shipping configs live in this fork or a separate infra repo?
**Trade-off context:** Keeping them in-fork couples app and infra release cadence — simpler for small teams. A separate infra repo lets Ops iterate independently and is standard for k8s (Q3). Related to Q31.
**Suggested default:** PROVISIONAL — separate infra repo, referenced by SHA from this fork's release notes.

### Q15 — Cross-tenant analytics: Day-1 or Day-N?
**Source:** `10-multisite-multitenant.md` UNKNOWN L449.
**Question:** Do we need aggregated dashboards across tenants at launch, or can it wait?
**Trade-off context:** Under Model A, cross-tenant analytics = building an ETL that pulls from N DBs into a warehouse. Under Model B it's a `GROUP BY tenant_id`. If it's Day-1 and Q11 = Model A, we need the ETL scaffolding in the MVP.
**Suggested default:** SAFE-DEFAULT — Day-N; ship a per-tenant reports page at launch.

### Q16 — Central identity across tenants at Day-1?
**Source:** `10-multisite-multitenant.md` UNKNOWN L450; `05-auth-and-acl.md` L613.
**Question:** Must a single human be able to log in once and access multiple tenants (SSO across tenants) at launch?
**Trade-off context:** OpenEMR's session cookie (`CORE_SESSION_ID = "OpenEMR"`) is a global constant — one browser can only hold one tenant login at a time (see Q17). Cross-tenant SSO on top of that requires patching cookie names per site AND a central-identity mapping table. Skipping this is fine for tenants staffed by disjoint users; painful for MSO/HQ users.
**Suggested default:** SAFE-DEFAULT — no cross-tenant SSO Day-1; single-tenant SSO via Q4 IdP.

### Q17 — Per-tenant cookie names?
**Source:** `05-auth-and-acl.md` §L604–608; `10-multisite-multitenant.md` §L440.
**Question:** Accept the "one browser = one tenant at a time" constraint, or patch `SessionUtil` to namespace cookie names per site?
**Trade-off context:** Accepting is zero-cost but a bad UX for support/HQ users; patching is a small class-c fork patch (`SessionUtil.php`, `globals.php:310–323`) that we'd carry across rebases. Directly interacts with Q16.
**Suggested default:** SAFE-DEFAULT — accept the constraint Day-1; patch only if Q16 flips.

---

## D — Saudi market / Arabic / regulatory

### Q18 — Arabic UI baseline: extend existing or greenfield?
**Source:** `13-i18n-localization.md` UNKNOWN #1 (L261); §L77.
**Question:** Do we finish `contrib/util/language_translations/currentLanguage_utf8.sql` (~9,400 constants, completion % unmeasured) or restart with an i18next-style JSON catalog?
**Trade-off context:** Extending gets us to "usable Arabic" fastest and inherits every legacy screen. Greenfield JSON is developer-friendlier, works for React/Vue, but forces us to re-translate everything and to bridge Smarty/Twig lookups. The existing dataset is bundled MSA of unknown fidelity.
**Suggested default:** PROVISIONAL — extend existing SQL catalog for legacy screens; use i18next JSON for anything we build new.

### Q19 — Hijri calendar policy
**Source:** `13-i18n-localization.md` UNKNOWN #6 (L269).
**Question:** Do we surface Hijri dates in the UI (Hijri-primary, Gregorian-primary, or dual-display), and on which fields (DOB, appointments, labs, billing)?
**Trade-off context:** Saudi users expect Hijri at least for administrative dates (invoicing) and often DOB. Clinical timestamps (lab results) typically stay Gregorian. Dual-display everywhere is safest but visually cluttered.
**Suggested default:** PROVISIONAL — dual-display on appointment/billing screens; Gregorian-only on clinical timestamps.

### Q20 — Currency policy
**Source:** `13-i18n-localization.md` UNKNOWN L277; `08-billing-claims-insurance.md` L432.
**Question:** SAR only, or multi-currency (SAR / USD / AED)?
**Trade-off context:** SAR-only lets us hard-code and skip FX. Multi-currency requires schema (currency columns on `billing`), FX-rate table, and every report to be currency-aware. Ties to Q26 (hardcoded USD in FHIR Coverage).
**Suggested default:** SAFE-DEFAULT — SAR only Day-1; add multi-currency as a Phase-2 feature.

### Q21 — ZATCA e-invoicing scope
**Source:** `13-i18n-localization.md` UNKNOWN #5 (L267); §L214.
**Question:** ZATCA Phase 1 (unstructured e-invoice + QR code) or Phase 2 (structured XML, signature, Fatoora clearance integration)?
**Trade-off context:** Phase 1 is a PDF-generation change + a QR encoder. Phase 2 is a full XML-signing pipeline with real-time Fatoora API integration, cryptographic device onboarding, and audit trail — a multi-sprint deliverable. Regulatory reality: healthcare invoices are already in Phase-2 scope by ZATCA's calendar.
**Suggested default:** PROVISIONAL — Phase 2 (structured + Fatoora); if timeline blocks, ship Phase 1 as an interim milestone.

### Q22 — MSA vs Saudi-dialect terminology
**Source:** `13-i18n-localization.md` UNKNOWN #7 (L271).
**Question:** Do we standardize on Modern Standard Arabic (MSA) or Saudi-dialect medical/administrative terminology?
**Trade-off context:** MSA is what the bundled translations use and what most healthcare workers can read. Saudi dialect is more natural for patient-facing screens but forces us to maintain a Saudi-specific glossary. Mixing yields the worst of both.
**Suggested default:** SAFE-DEFAULT — MSA everywhere; revisit patient-portal only if user testing demands it.

### Q23 — Per-user timezone/language preference
**Source:** `13-i18n-localization.md` UNKNOWN #8 (L273); §L184.
**Question:** Do users need per-user timezone/language, or is site-wide (`gbl_time_zone`, session `language_choice`) enough?
**Trade-off context:** Site-wide is what ships. Per-user requires a `users` column plus a login-time hook. Saudi is single timezone (AST), so timezone can stay site-wide; per-user language matters if bilingual staff share tenants.
**Suggested default:** SAFE-DEFAULT — site-wide timezone; per-user language toggle.

### Q24 — `bootstrap-rtl` sustainability
**Source:** `13-i18n-localization.md` UNKNOWN #9 (L275); `09-frontend-ui.md` §L488.
**Question:** Adopt a maintained `bootstrap-rtl` fork, replace it with Bootstrap 5 RTL native, or vendor-in the current pinned zip?
**Trade-off context:** The current build pins a single-commit GitHub archive of a third-party fork — if that URL disappears the build breaks. Bootstrap 5 has native RTL but is a major-version bump (BS4→BS5) touching every theme SCSS. Vendoring the zip locally is the cheapest hedge.
**Suggested default:** SAFE-DEFAULT — vendor the current zip under `public/vendor/` immediately; plan BS5 migration as Phase-2.

### Q25 — PDF Arabic fonts
**Source:** `09-frontend-ui.md` §L488; `13-i18n-localization.md` §L255.
**Question:** Do we bundle Amiri + Noto Naskh Arabic in mPDF/wkhtmltopdf configs?
**Trade-off context:** Only mPDF's transitive DejaVu Sans covers Arabic today — insufficient for polished output. Bundling adds ~5–10 MB but is a one-time win for every Arabic PDF (invoices, prescriptions, ZATCA outputs). No downside beyond package size.
**Suggested default:** SAFE-DEFAULT — bundle both.

### Q26 — Hardcoded USD in FHIR Coverage
**Source:** `13-i18n-localization.md` UNKNOWN #10 (L277); `08-billing-claims-insurance.md` L432.
**Question:** Do we patch `src/Services/FHIR/FhirCoverageService.php:294` (and the 5 other USD hard-codes), wrap them in a fork-local currency adapter, or accept?
**Trade-off context:** Upstream PR is the cleanest fix and helps every fork. A local wrapper avoids waiting for upstream review. Accepting is only tenable if Q20 = USD-optional.
**Suggested default:** SAFE-DEFAULT — upstream PR the fix; local wrapper as bridge.

---

## E — NPHIES / claims

### Q27 — FHIR Claim submission architecture
**Source:** `08-billing-claims-insurance.md` §4 Options A/B/C (L437+); `15 §6`.
**Question:** Option A (extend FHIR Coverage/Claim controllers), Option B (custom module reading the `billing` table — but no billing event exists), or Option C (parallel background service polling `billing`, mirroring claimrev-connect)?
**Trade-off context:** Option A is architecturally clean but Coverage is currently read-only and Claim/ClaimResponse have **no HTTP surface at all** (Phase 5). Option B blocks on missing core events (Q30). Option C works today with zero core changes but is polling-based and has scale questions.
**Suggested default:** PROVISIONAL — Option C for MVP (fastest to production), plan migration to Option A once Q30's events land.

### Q28 — Ownership of FHIR write-surface build
**Source:** `06-api-surface.md` §L245–248, UNKNOWNs; `08-billing-claims-insurance.md` L439.
**Question:** Which SaaS deliverable owns building `Coverage` (write), `Claim`, `ClaimResponse`, `EOB`, `CoverageEligibilityRequest/Response`, `PaymentNotice` — all currently absent as REST controllers?
**Trade-off context:** These are prerequisites for NPHIES conformance. Building each is 1–2 sprints of controller + `FhirService` mapping. Data classes for `FHIRClaim`/`FHIRClaimResponse` exist under `src/FHIR/R4/`, so the type surface is partially free; `EOB`/`Eligibility*`/`PaymentNotice` need data class verification too.
**Suggested default:** PROVISIONAL — folded into the "NPHIES module" deliverable that owns Q27.

### Q29 — `x12_partners` reuse vs new `nphies_partners` table
**Source:** `08-billing-claims-insurance.md` UNKNOWN #5 (L450).
**Question:** Reuse the existing `x12_partners` OAuth columns (`x12_token_endpoint`, `x12_client_id`, `x12_client_secret`) for NPHIES, or create a new `nphies_partners` table?
**Trade-off context:** Reuse minimizes schema surface but overloads the name and confuses the US-EDI vs Saudi-FHIR distinction. New table is cleaner but adds a `custom_saas_nphies_partners` (or similar) with its own admin UI. Both are `custom_*` per Q37.
**Suggested default:** SAFE-DEFAULT — new `custom_saas_nphies_partners` table.

### Q30 — Missing billing/core events: upstream PR or polling emulation?
**Source:** `07-modules-and-extensibility.md` UNKNOWN #3 (L408); `15 §UNKNOWN #5` (L461); `08-billing-claims-insurance.md`.
**Question:** For missing core events (Encounter created/signed/closed, Auth login/logout, Claim state, Role changed), do we open upstream PRs (class-b) or emulate via `background_services` polling (class-c)?
**Trade-off context:** Upstream PRs are the sanctioned path and benefit everyone but have unbounded merge timelines. Polling works today (that's what `claimrev-connect` does) but is inelegant and has cost at scale. This decision determines whether the NPHIES module can be event-driven.
**Suggested default:** PROVISIONAL — do both in parallel: ship with polling, open the upstream PRs in the same sprint.

### Q31 — Keep `claimrev-connect` in Saudi deployments?
**Source:** `07-modules-and-extensibility.md`; `08-billing-claims-insurance.md`.
**Question:** Does `claimrevolution/oe-module-claimrev-connect` (US clearinghouse) stay enabled in Saudi tenants, or do we ship without it?
**Trade-off context:** It's the only in-tree `oe-module-*` that is a runtime composer require. Saudi tenants have no use for a US clearinghouse. Removing it means one less module to maintain; keeping it disabled costs nothing but adds surface for confusion.
**Suggested default:** SAFE-DEFAULT — keep in composer for compatibility with upstream tests, but disable in Saudi tenant provisioning defaults.

---

## F — Frontend

### Q32 — Patient portal strategy
**Source:** `09-frontend-ui.md` §L487.
**Question:** Rebrand the existing Smarty-based `portal/` app or build a greenfield SPA on top of the 5 portal REST endpoints (`/apis/default/portal/*`)?
**Trade-off context:** Rebrand is a week of theming; SPA is a multi-sprint build but future-proof and mobile-friendly. Saudi patients increasingly expect app-like portals. Portal has separate credentials (`patient_access_onsite`) so a swap doesn't disturb the main UI.
**Suggested default:** PROVISIONAL — rebrand for MVP, greenfield SPA in Phase-2.

### Q33 — Main-UI strategy
**Source:** `09-frontend-ui.md` §L488+.
**Question:** Stay on Bootstrap-4 themes + Twig module screens (current), embed a SPA in a specific tab, or replace the shell?
**Trade-off context:** Shell replacement is a 6–12 month project touching every iframe/tab (611 grid-using files, Phase 9). Embedding a SPA in one tab is safe and lets us prove modern-frontend patterns before committing. Staying on BS4 is stable but ages daily.
**Suggested default:** SAFE-DEFAULT — stay on BS4/Twig; embed SPAs tab-by-tab as new modules require them.

### Q34 — Themes: how many Saudi variants?
**Source:** `09-frontend-ui.md` UNKNOWN L492.
**Question:** How many user-selectable themes must ship for Saudi customers (dark, light, RTL variants, hospital-branded)?
**Trade-off context:** Upstream ships 17 SCSS entrypoints (~3 RTL). Each additional theme is a small SCSS file but multiplies QA. Most Saudi customers will want their own hospital brand palette — this can be a config, not a full theme.
**Suggested default:** SAFE-DEFAULT — ship 2 (light-RTL, dark-RTL); provide a "brand color override" mechanism for per-tenant palette.

### Q35 — CKEditor 5 Arabic bundling
**Source:** `09-frontend-ui.md` UNKNOWN L493.
**Question:** Must the built CKEditor bundle include Arabic UI translations?
**Trade-off context:** Adds ~50 KB. Without it, the WYSIWYG toolbar is English-only even in Arabic tenants — poor UX for clinical note authoring. Trivial webpack config change.
**Suggested default:** SAFE-DEFAULT — include Arabic (and en-US).

---

## G — Modules & extensibility

### Q36 — Byte-identity of 6 in-tree `oe-module-*` vs upstream
**Source:** `03-directory-map.md` L182; `07-modules-and-extensibility.md` UNKNOWN #4 (L409); `15 UNKNOWN #4` (L456).
**Question:** Are the six non-`claimrev-connect` `oe-module-*` directories byte-identical to upstream, or has the fork modified them?
**Trade-off context:** Blocks a clean fork-vs-upstream diff. Resolved automatically once Q1 (`upstream` remote) is done via `git diff upstream/master -- interface/modules/custom_modules/oe-module-*`. If modified, each becomes a class-c hotfix to track.
**Suggested default:** Resolve immediately after Q1.

### Q37 — `openemr/oe-module-installer-plugin` internals
**Source:** `07-modules-and-extensibility.md` UNKNOWN #1 (L406, L217); `15 UNKNOWN #6` (L466).
**Question:** What is the plugin's class name and target-path resolution algorithm — needed to package our own modules with confidence?
**Trade-off context:** Source lives out-of-tree in `openemr/oe-module-installer-plugin` on GitHub; we can either read that repo or run `composer install` locally to inspect `vendor/`. Without this, packaging is guess-and-check.
**Suggested default:** SAFE-DEFAULT — inspect the upstream plugin repo out-of-band; document class name + algorithm in a follow-up note.

### Q38 — Twig namespacing convention for custom modules
**Source:** `07-modules-and-extensibility.md` UNKNOWN #2 (L407); `15 §2` caveat (L55).
**Question:** Do we adopt a `@moduleName` Twig namespace prefix convention, and enforce it in module scaffolding?
**Trade-off context:** `TwigContainer::addPath()` supports namespaces but no convention exists in-tree — template resolution is order-dependent, so two modules with a `layout.twig` collide silently. Enforcing `@saas_nphies/layout.twig` costs a scaffolding line; skipping it invites a rebase-time production bug.
**Suggested default:** SAFE-DEFAULT — enforce `@<module_slug>/` prefix in our module template.

---

## H — DevOps

### Q39 — Do we publish our own Docker images?
**Source:** `11-devops-docker-ci.md` UNKNOWN #2 (L300).
**Question:** Do we publish `saas/openemr:*` images to our own registry, or consume upstream `openemr/openemr` images?
**Trade-off context:** Publishing our own means we own the ~15 upstream `docker-build-*` / `docker-release-*` workflows (keep/adapt). Consuming upstream means we bake tenant-specific config at deploy time — simpler CI, but no way to bundle fork-only patches into an image. Given fork-only Arabic assets and NPHIES module, we almost certainly need to publish.
**Suggested default:** SAFE-DEFAULT — publish our own images to a private registry.

### Q40 — Inferno ONC certification in scope?
**Source:** `11-devops-docker-ci.md` UNKNOWN #3 (L301).
**Question:** Is Inferno ONC certification (`inferno-test.yml`, `ci/inferno/`) in scope for this Saudi-focused fork?
**Trade-off context:** Inferno tests US ONC-specific FHIR conformance — no direct Saudi regulatory value. Keeping it in CI adds ~15 min per run but validates that our FHIR surface hasn't regressed against US Core. Removing it saves CI cost and lets us diverge freely from US Core.
**Suggested default:** SAFE-DEFAULT — remove from required-checks list; keep the config file so re-enabling is one line.

### Q41 — k8s/Helm charts — in-fork or infra repo?
**Source:** `11-devops-docker-ci.md` §k8s row (L285); `10 §7`.
**Question:** If Q3 = k8s, do the Helm charts live in this fork or a separate infra repo?
**Trade-off context:** In-fork keeps everything atomic per release. Separate infra repo lets Ops iterate on rollout config without touching app code. Directly parallels Q14.
**Suggested default:** PROVISIONAL — separate infra repo (same as Q14).

### Q42 — Production `X-Forwarded-For` handling
**Source:** `14-security-compliance.md` UNKNOWN L724; §11.
**Question:** How is `X-Forwarded-For` handled in production — trust one hop, trust the whole chain, or ignore?
**Trade-off context:** Trusting client-supplied X-F-F is a spoofable audit-log poison. Trusting only the LB's added header (rightmost hop) is standard. This is deployment posture, not code, but the answer must be documented before Go-Live.
**Suggested default:** SAFE-DEFAULT — trust only the rightmost X-F-F entry (added by our own LB).

---

## I — Security / compliance

### Q43 — Rotate exposed GitHub PATs (tracked, not asked)
**Source:** `14-security-compliance.md` §L710; `15 UNKNOWN #8` (L475).
**Question:** Rotate/revoke the two hardcoded PATs at `docker/development-easy/docker-compose.yml:75-77` — treat as burned regardless of live status. (Action item, not a decision.)
**Trade-off context:** Any rebase preserves them. Rotation is out-of-band via GitHub. This must be closed before the fork goes public.
**Suggested default:** SAFE-DEFAULT — revoke immediately; add a pre-commit hook / secret scanner to prevent recurrence.

### Q44 — Per-tenant KMS key isolation Day-1?
**Source:** `14-security-compliance.md` §5.6, UNKNOWNs.
**Question:** Do we build a wrapper on top of `CryptoGen` that isolates encryption keys per tenant at launch?
**Trade-off context:** Today `CryptoGen` uses a per-site key file. Per-tenant KMS (AWS KMS, HashiCorp Vault, or cloud-native) gives cryptographic isolation and rotation. Day-1 is expensive; Day-N risks retrofitting encryption over live data.
**Suggested default:** PROVISIONAL — Day-1 for envelope keys (KEK per tenant); leave DEKs local for MVP.

### Q45 — Saudi PDPL data-residency plan
**Source:** `14-security-compliance.md` UNKNOWN L726; §12.
**Question:** Are we deploying into a Kingdom-only region (STC Cloud / Oracle Jeddah / AWS Bahrain-with-data-in-KSA)?
**Trade-off context:** Saudi PDPL requires personal health data to stay in-Kingdom unless a specific cross-border approval is obtained. This drives cloud provider, region, backup destinations, and observability endpoints. Non-negotiable for a Saudi SaaS.
**Suggested default:** PROVISIONAL — STC Cloud KSA region; confirm with legal before Go-Live.

### Q46 — Breakglass justification-prompt UI
**Source:** `14-security-compliance.md` UNKNOWN L721; §7.
**Question:** Do we require a text-justification prompt at breakglass login (linked to `gbl_force_log_breakglass`)?
**Trade-off context:** Currently `gbl_force_log_breakglass` logs the event but doesn't prompt for reason. Building the prompt is small; regulators/auditors typically expect it. Low cost, high compliance value.
**Suggested default:** SAFE-DEFAULT — build the prompt.

### Q47 — Default value of `drive_encryption` for Saudi tenants
**Source:** `14-security-compliance.md` UNKNOWN L717; §5.5.
**Question:** Do we force `drive_encryption` on for all Saudi tenants (regardless of upstream default)?
**Trade-off context:** Upstream default is currently unverified (needs `library/globals.inc.php` inspection). PDPL essentially demands encryption at rest; forcing it on removes an operator footgun. Small CPU cost.
**Suggested default:** SAFE-DEFAULT — force on for Saudi tenants.

### Q48 — Audit-log retention (PDPL 5+ years)
**Source:** `14-security-compliance.md` UNKNOWN L720; §6.5.
**Question:** Do we build a background pruner for `log` / `api_log` that enforces a ≥5-year retention floor (Saudi PDPL for health records)?
**Trade-off context:** No existing pruner. Without one, logs grow unboundedly until manual cleanup. Retention floor is a compliance requirement; ceiling is a cost/perf concern. Ties to Q45 (residency of archived logs).
**Suggested default:** SAFE-DEFAULT — build pruner with 7-year retention default (safety margin over 5).

### Q49 — AV scanning for uploads (ClamAV)
**Source:** `14-security-compliance.md` UNKNOWN L724 area; §5 (upload gating).
**Question:** Is ClamAV integration for uploads required Day-1?
**Trade-off context:** `Document::createDocument` uses `isWhiteFile()` (extension allow-list) but no AV scan. For a healthcare SaaS accepting patient/lab/scan uploads, AV is table stakes. ClamAV via `php-clamav` or sidecar is well-trodden.
**Suggested default:** SAFE-DEFAULT — required Day-1; sidecar container in the compose stack.

### Q50 — Add `.github/SECURITY.md` and `.github/dependabot.yml`
**Source:** `14-security-compliance.md` UNKNOWN L722; §10–11.
**Question:** Do we add `SECURITY.md` (disclosure policy) and `dependabot.yml` (dependency PRs) to the fork?
**Trade-off context:** Both are free wins. SECURITY.md gives responsible-disclosure clarity. Dependabot generates a lot of PR noise but catches CVEs early. Ties to Q2 (rebase cadence — Dependabot PRs need routing).
**Suggested default:** SAFE-DEFAULT — add both immediately.

---

## J — Testing / quality

### Q51 — Current numeric coverage % baseline
**Source:** `12-testing-infrastructure.md` UNKNOWN L253.
**Question:** What is the current PHPUnit coverage % on Codecov, and what target do we set for our modules?
**Trade-off context:** Historically ~4%; `codecov.yml` implies drift upward but the real number lives on Codecov. Setting a target requires knowing the baseline; setting it too high blocks all PRs, too low is meaningless.
**Suggested default:** PROVISIONAL — pull actual number from Codecov this week; set our-module target at 80% patch coverage, 60% total.

### Q52 — CI DB strategy: fresh-per-job or persistent?
**Source:** `12-testing-infrastructure.md` UNKNOWN L252.
**Question:** Does the DB test suite run against a fresh DB per CI job or a persistent one?
**Trade-off context:** Determines severity of the prefix-cleanup gap (`TransactionalTestCase` doesn't exist). Fresh-per-job is safe but slow. Persistent is fast but exposes test interdependency bugs. Needs a `.github/workflows/` audit.
**Suggested default:** SAFE-DEFAULT — fresh-per-job for our-module suites; leave upstream suites as-is.

### Q53 — Panther local ChromeDriver fallback on Windows
**Source:** `12-testing-infrastructure.md` UNKNOWN L254.
**Question:** Do we need `symfony/panther`'s local ChromeDriver fallback to work on Windows hosts, or is dev-in-container acceptable?
**Trade-off context:** `BaseTrait.php:79` has a fallback path but its Windows behavior is unverified. If devs on Windows use only the openemr-container Selenium (already working), we can ignore. If we want `phpunit --filter` from a Windows terminal to just work, we need to test.
**Suggested default:** SAFE-DEFAULT — mandate dev-in-container; document as such.

---

## K — Miscellaneous

### Q54 — Review `tools/` directory (19 files)
**Source:** `03-directory-map.md` UNKNOWN L183.
**Question:** Do we do a per-file review of `tools/`, or accept the `[BUILD/TOOLING]` classification and move on?
**Trade-off context:** 19 files, classified by directory name only in Phase 2. Could contain useful helpers, dead scripts, or committed developer footguns. Review is 1 sprint-hour.
**Suggested default:** SAFE-DEFAULT — 1-hour review pass; document findings inline.

### Q55 — `openemr/wkhtmltopdf-openemr` and `openemr/oe-module-cqm` transitive owners
**Source:** `02-tech-stack.md` UNKNOWN L448.
**Question:** Are these packages transitively pulled by some dependency, or dead entries in `composer.json`'s `repositories` block?
**Trade-off context:** They're declared as `repositories` but not `require`d at top level. If dead, remove them (one less rebase-conflict surface). Resolved by inspecting `vendor/` post-install.
**Suggested default:** SAFE-DEFAULT — remove after confirming no transitive require.

### Q56 — React 15 (2016) via napa — who consumes it?
**Source:** `02-tech-stack.md` UNKNOWN L450; `09-frontend-ui.md`; `15 §L305`.
**Question:** Which OpenEMR UI code loads the napa-fetched `react/react-15.1.0.zip`, and can we retire it?
**Trade-off context:** React 15 is 9+ years out of support and has known CVEs. Removing it depends on finding zero consumers or migrating them to a modern React. Requires a `public/`-tree grep for `react.min.js`/`ReactDOM`.
**Suggested default:** SAFE-DEFAULT — grep this sprint; if unused, remove; if used, plan React 18 migration.

### Q57 — `documents.id` / `insurance_companies.id` without `auto_increment`
**Source:** `04-database-schema.md` §L116.
**Question:** Are these app-assigned IDs (deliberate) or legacy artifacts to be fixed?
**Trade-off context:** Currently `NOT NULL default '0'` with no `auto_increment` — application code assigns manually. Changing to `auto_increment` is a schema migration + all-call-site audit. Leaving alone is safe if legacy code correctly generates IDs.
**Suggested default:** SAFE-DEFAULT — leave alone; document as known anomaly.

### Q58 — Formal upstream policy on `custom_` prefix
**Source:** `04-database-schema.md` §L271.
**Question:** Do we seek a written commitment from OpenEMR maintainers reserving the `custom_` table prefix for downstream forks?
**Trade-off context:** Today it's empirically unused (36 upgrade files, zero counter-examples), but no policy. A written reservation prevents future collisions. Alternative: use a tenant-specific prefix like `custom_saas_` (paranoid but safe).
**Suggested default:** SAFE-DEFAULT — use `custom_saas_` prefix; open a docs PR upstream requesting the reservation but do not block on it.

### Q59 — `sites/<tenant>/documents/theme/` per-site theme override path
**Source:** `15 UNKNOWN #3` (L451); `09-frontend-ui.md` §2.
**Question:** Is `sites/<tenant>/documents/theme/` the exact per-site theme override path, or is it something else?
**Trade-off context:** Q34 (per-tenant brand palette) depends on knowing the exact override path. Resolvable by a targeted grep or a small runtime test in a scratch tenant.
**Suggested default:** Verify via grep this sprint; block Q34 until answered.

### Q60 — DB DEFAULT CHARSET / COLLATION at CREATE TABLE level
**Source:** `02-tech-stack.md` UNKNOWN L453.
**Question:** Does the production installer set per-database `utf8mb4_general_ci` vs `utf8mb4_unicode_ci`, or rely on connection-time server flag?
**Trade-off context:** `sql/database.sql` ships without explicit per-table `DEFAULT CHARSET`. If prod deploys onto a MySQL with a different server default, we silently get the wrong collation — Arabic sort order and case-folding subtly break. Resolvable by reading the `Installer` class.
**Suggested default:** SAFE-DEFAULT — pin `utf8mb4_unicode_ci` explicitly in `Installer` and in `custom_saas_*` DDL.

### Q61 — DWV DICOM viewer locale JSON
**Source:** `13-i18n-localization.md` UNKNOWN #3 (L265); `09 UNKNOWN` L494.
**Question:** Where do DWV (DICOM viewer) locale JSON files live, and do they include Arabic?
**Trade-off context:** DICOM view is a diagnostic tool clinicians use daily. English-only DICOM controls in an otherwise-Arabic UI are jarring. Small QA task.
**Suggested default:** SAFE-DEFAULT — locate + add Arabic in Phase-2 alongside Q18.

### Q62 — Full FHIR SearchParameter enumeration
**Source:** `06-api-surface.md` UNKNOWN L267.
**Question:** Do we produce an exhaustive per-resource `FhirService` SearchParameter catalog (36 files) beyond the runtime `GET /fhir/metadata`?
**Trade-off context:** Runtime CapabilityStatement is authoritative but not diff-able across releases. A static catalog helps rebase reviews and NPHIES conformance discussions. 1–2 days of work.
**Suggested default:** PROVISIONAL — build it once we start NPHIES conformance testing (Q28).

### Q63 — Numeric API versioning strategy
**Source:** `06-api-surface.md` UNKNOWN L265.
**Question:** How do we handle breaking API changes given there's no `/v1/` segment?
**Trade-off context:** Today all versioning is implicit in the pinned US Core route-map filename. Introducing `/v1/` retroactively requires router changes + client migration windows. Alternatives: header-based versioning, or freeze the surface and never break.
**Suggested default:** PROVISIONAL — freeze current surface; introduce `/v1/` when the first breaking change actually ships.

### Q64 — Application-tier rate limiting
**Source:** `06-api-surface.md` UNKNOWN L266.
**Question:** Do we implement app-tier rate limits, or rely on the reverse proxy?
**Trade-off context:** No app-tier limiter today. Proxy-only is standard but misses per-OAuth-client fairness. App-tier is a mid-size add (Redis token bucket).
**Suggested default:** SAFE-DEFAULT — proxy-only Day-1; add app-tier per-client limits when abuse is observed.

### Q65 — `BillingProcessor` hookable extension points
**Source:** `08-billing-claims-insurance.md` UNKNOWN #2 (L443).
**Question:** Does `BillingProcessor` contain hookable extension points other than the `GeneratorInterface` polymorphism?
**Trade-off context:** Determines whether NPHIES claim-generation can plug in as a `Generator` implementation or requires deeper surgery. Resolvable by a focused read of the class + `AbstractGenerator`.
**Suggested default:** Perform the focused read in the same sprint that starts Q27.

### Q66 — `claimrev-connect` FHIR emission
**Source:** `08-billing-claims-insurance.md` UNKNOWN #3 (L446).
**Question:** Does `claimrev-connect` emit any FHIR internally, or is it 100% X12?
**Trade-off context:** If it emits FHIR, we can crib patterns for NPHIES. If pure X12, we learn nothing NPHIES-relevant from it. One-time trace.
**Suggested default:** Trace during Q27 scoping.

### Q67 — Full audit of SQL interpolation sinks and echo-vs-text ratio
**Source:** `14-security-compliance.md` UNKNOWNs L714, L715, L716.
**Question:** Do we complete the full audits deferred by tool truncation (33 `sqlStatement("...$"` sites, echo-vs-text ratio, every `Document::createDocument` caller gating through `isWhiteFile()`)?
**Trade-off context:** Sampling suggests low SQLi risk but audit was truncated. Full audit is 2–3 days of grep + read. Skipping leaves latent injection/XSS risk that surfaces during penetration testing.
**Suggested default:** SAFE-DEFAULT — schedule as a dedicated security-audit sprint before Go-Live.

### Q68 — `log.checksum` chain verification cadence
**Source:** `14-security-compliance.md` UNKNOWN L719.
**Question:** How often does a background job verify the `log.checksum` tamper-detection chain?
**Trade-off context:** No verifier is scheduled today. Without periodic verification, tamper detection is theatre — nobody sees the break until manual audit. Small cron script.
**Suggested default:** SAFE-DEFAULT — nightly verifier writing to `api_log`, alerting on mismatch.

### Q69 — Complete `encryptStandard` column inventory
**Source:** `14-security-compliance.md` UNKNOWN L718.
**Question:** Do we enumerate every column encrypted at rest via `encryptStandard`, and confirm PDPL/PHI coverage is complete?
**Trade-off context:** Partial inventory today. Missing columns = plaintext PHI = PDPL violation. Resolvable by a targeted grep in `library/` + `src/Services/`.
**Suggested default:** SAFE-DEFAULT — enumerate in the same sprint as Q47.

### Q70 — Vendored-vs-composer-installed `oe-module-*` runtime model
**Source:** `03-directory-map.md` UNKNOWN L184.
**Question:** For the seven `oe-module-*` dirs tracked in git, is the intent to also composer-install them on top (overlaying tracked files), or to treat the tracked copies as authoritative?
**Trade-off context:** Only `claimrev-connect` is a runtime `require` today. If prod does `composer install`, the tracked copies of the other six are shadowed or ignored depending on the installer plugin (Q37). Wrong assumption = live modules diverge silently from git.
**Suggested default:** Resolve alongside Q37.

### Q71 — Which module owns DICOM viewer include chain?
**Source:** `09-frontend-ui.md` UNKNOWN L494.
**Question:** Which PHP entry point includes `dicom_launcher.js`, and does DICOM viewing belong to a dedicated module?
**Trade-off context:** Ties to Q61 (DICOM locale). Small grep pass.
**Suggested default:** Bundle with Q61 investigation.

### Q72 — File-level vs line-level responsiveness coverage
**Source:** `09-frontend-ui.md` UNKNOWN L495.
**Question:** Of the 611 files using grid classes, how many are inside legacy tab iframes vs modernized shell code (needed to size RTL/mobile QA)?
**Trade-off context:** Line-count doesn't tell us file coverage. Determines the RTL/mobile QA matrix. 1-day audit.
**Suggested default:** Audit during Q34 theme scoping.

### Q73 — Scripted count of `QueryUtils::` call sites
**Source:** `10-multisite-multitenant.md` UNKNOWN L448.
**Question:** Do we run a scripted count of `QueryUtils::` (plus Doctrine and `OE_SITE_DIR` file-path sites) across the whole tree to size Model B accurately?
**Trade-off context:** Only relevant if Q11 flips to Model B. `sqlStatement(` alone is 1,875 — the true refactor surface is likely 3–5× that. Small script; big estimate delta.
**Suggested default:** Run only if Q11 = Model B.

### Q74 — `tests/Tests/Integration/` tree scope
**Source:** `12-testing-infrastructure.md` UNKNOWN L255.
**Question:** Is the `tests/Tests/Integration/` tree (referenced by `phpunit.integration.xml`) substantive or vestigial?
**Trade-off context:** Determines whether we invest in extending it or ignore it. 15-minute enumeration.
**Suggested default:** Enumerate during Q52 CI audit.

### Q75 — `phpunit-isolated.xml` actually run in CI?
**Source:** `12-testing-infrastructure.md` UNKNOWN L251.
**Question:** Does CI actually execute `phpunit-isolated.xml`?
**Trade-off context:** If yes, isolated tests are a first-class gate. If no, our Twig compile/render tests aren't enforced on PRs. Answered by inspecting `.github/workflows/`.
**Suggested default:** Bundle with Q52 audit.

---

## Answering workflow

For each question above, add answer as a fenced block starting with `### A<n>` and record the answer date + author.
