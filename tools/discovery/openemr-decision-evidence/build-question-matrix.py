#!/usr/bin/env python3
"""
build-question-matrix.py — single source of truth for Q1-Q75 audit results.

Emits both required artifacts from one dataset so the CSV and the JSON can
never disagree:

    docs/discovery/openemr-decision-evidence/03-question-status-matrix.csv
    docs/discovery/openemr-decision-evidence/04-question-evidence.json

Read-only with respect to the application. Run from anywhere inside the repo.
"""

from __future__ import annotations

import csv
import json
import subprocess
import sys
from pathlib import Path

# ---------------------------------------------------------------- refs

FORK_SHA = "631f2b38cf633769c305233f88cdf9c73ca80657"
UPSTREAM_STABLE_TAG = "v8_2_0"
UPSTREAM_STABLE_SHA = "6125a2fd8089c8bcc3848071c1293c60e27a7585"
UPSTREAM_MASTER_SHA = "dad282635495c98ea9ab9c3577277933725d13eb"

# Shorthands used in the per-question rows below.
F = "fork"
U = "upstream"


def ev(file, finding, start=None, end=None, symbol=None, command=None, repo=F):
    """Build one evidence object matching the mandated schema."""
    return {
        "repository": repo,
        "file": file,
        "start_line": start,
        "end_line": end,
        "symbol": symbol,
        "command": command,
        "finding": finding,
    }


def q(qid, summary, category, classification, repo_verifiable, status,
      recommended_status, finding, decision_impact, evidence, confidence,
      recommended_decision, artifacts=None, remaining_gap=None,
      external=False, remaining_input=""):
    return {
        "question_id": qid,
        "question_summary": summary,
        "category": category,
        "classification": classification,
        "repository_verifiable": repo_verifiable,
        "status": status,
        "recommended_status": recommended_status,
        "finding": finding,
        "decision_impact": decision_impact,
        "fork_commit": FORK_SHA,
        "upstream_ref": UPSTREAM_STABLE_TAG,
        "upstream_commit": UPSTREAM_STABLE_SHA,
        "evidence": evidence,
        "generated_artifacts": artifacts or [],
        "confidence": confidence,
        "remaining_gap": remaining_gap,
        "recommended_decision": recommended_decision,
        "requires_external_non_repository_input": external,
        "_remaining_input": remaining_input,
    }


# Frequently cited artifacts
A_DRIFT = "docs/discovery/openemr-decision-evidence/05-upstream-fork-drift.md"
A_CORE = "docs/discovery/openemr-decision-evidence/07-core-modification-inventory.csv"
A_MOD = "docs/discovery/openemr-decision-evidence/06-module-drift-inventory.csv"
A_Q36 = "docs/discovery/openemr-decision-evidence/17-q36-module-byte-identity.md"
A_DEP = "docs/discovery/openemr-decision-evidence/08-dependency-runtime-inventory.csv"
A_CI = "docs/discovery/openemr-decision-evidence/09-test-and-ci-inventory.md"
A_DB = "docs/discovery/openemr-decision-evidence/10-database-and-tenancy-evidence.md"
A_AUTH = "docs/discovery/openemr-decision-evidence/11-authentication-authorization-evidence.md"
A_FHIR = "docs/discovery/openemr-decision-evidence/12-fhir-nphies-billing-evidence.md"
A_L10N = "docs/discovery/openemr-decision-evidence/13-localization-arabic-evidence.md"
A_FE = "docs/discovery/openemr-decision-evidence/14-frontend-ui-evidence.md"
A_SEC = "docs/discovery/openemr-decision-evidence/15-security-compliance-code-evidence.md"
A_CP = "docs/discovery/openemr-decision-evidence/16-control-plane-constraints.md"
A_Q72C = "docs/discovery/openemr-decision-evidence/18-q72-ui-responsiveness-inventory.csv"
A_Q72S = "docs/discovery/openemr-decision-evidence/19-q72-ui-responsiveness-summary.md"

QUESTIONS = []
add = QUESTIONS.append

# ============================================================ A: upstream/deploy

add(q("Q1", "Add upstream remote and measure drift", "A-Foundational",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "upstream remote added (openemr/openemr) and drift measured. The fork has ZERO own commits: "
      "`git merge-base HEAD upstream/master` returns HEAD itself and `git rev-list --count upstream/master..HEAD` "
      "returns 0. The fork is an unmodified mirror of upstream master at 2026-07-04, 373 commits behind current "
      "upstream master and 17 commits diverged from the v8_2_0 release cut.",
      "Removes all fork-drift risk from every other decision. A rebase is a fast-forward today; the cost of the "
      "'no core edits' policy is therefore zero to adopt right now and rises with every local patch added later.",
      [ev("(git)", "0 fork-only commits; HEAD is a strict ancestor of upstream/master",
          command="git rev-list --count upstream/master..HEAD  -> 0"),
       ev("(git)", "merge-base equals HEAD, proving ancestry",
          command="git merge-base HEAD upstream/master  -> 631f2b38..."),
       ev("(git)", "v8_2_0 is the newest non-prerelease tag, dated 2026-07-08",
          command="git for-each-ref --sort=-creatordate refs/tags")],
      "CONFIRMED",
      "Peg to the release tag (v8_2_0 today) for production; keep upstream/master fetched for security triage. "
      "Adopt a strict no-core-edit policy NOW while the diff is provably empty.",
      [A_DRIFT, A_CORE]))

add(q("Q2", "Upstream rebase cadence", "A-Foundational",
      "business_product_decision", False, "external_decision_required", "PROVISIONAL",
      "Repository establishes the inputs but not the answer. Upstream cut 3 releases in ~5 months "
      "(v8_0_0_3 2026-03-25, v8_1_0 2026-06-01, v8_2_0 2026-07-08) and lands ~373 commits per ~5 weeks of master. "
      "Fork currently carries no patches, so rebase cost is presently zero.",
      "Cadence drives CI budget and on-call rota, not architecture. Because the fork has no patches, cadence can be "
      "chosen freely today and revisited once the first local patch lands.",
      [ev("(git)", "3 stable releases in 5 months; 373 commits accumulated since fork HEAD",
          command="git for-each-ref refs/tags; git rev-list --count HEAD..upstream/master")],
      "HIGH",
      "Provisional: rebase per upstream point release, plus out-of-band for security advisories. Revisit when the "
      "first class-c patch is carried.",
      [A_DRIFT], remaining_gap="Engineering capacity and release-window policy are non-repository facts.",
      external=True, remaining_input="Eng leadership: sprint capacity + acceptable patch lag."))

add(q("Q3", "Target production orchestration platform", "A-Foundational",
      "deployment_decision", True, "partially_answered", "PROVISIONAL",
      "Repository contains ZERO Kubernetes, Helm, Nomad or Swarm artifacts (no Chart.yaml, no values.yaml, no "
      "kind: Deployment). It ships 26 docker-compose files, all for CI matrices and developer stacks. Whatever "
      "platform is chosen, the orchestration layer is greenfield.",
      "No sunk cost favours any platform; equally, no platform is 'already supported'. The compose files are not "
      "production artifacts and must not be mistaken for a deployment baseline.",
      [ev("evidence/manifests/q3-deployment-artifacts.txt",
          "0 helm/k8s/nomad/swarm files; 26 docker-compose files, all under ci/ or docker/",
          command="git ls-files | grep -iE 'chart.yaml|values.yaml|k8s|nomad|swarm'")],
      "CONFIRMED",
      "Provisional: Kubernetes + Helm with charts in a separate infra repo (see Q41). Nothing in-repo blocks or "
      "favours this.",
      ["docs/discovery/openemr-decision-evidence/evidence/manifests/q3-deployment-artifacts.txt"],
      remaining_gap="Ops platform standard and budget are business decisions.",
      external=True, remaining_input="Ops/Platform: target runtime and managed-service budget."))

# ============================================================ B: identity

add(q("Q4", "Central IdP choice", "B-Identity",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "OpenEMR ships a full OAuth2/OIDC authorization SERVER (league/oauth2-server under "
      "src/Common/Auth/OpenIDConnect/) but NO generic OIDC relying-party client. The only shipped federation "
      "example is Google Sign-In, which maps an external subject onto a LOCAL users row rather than replacing it.",
      "Fronting OpenEMR with Keycloak is not configuration - it requires new code (an RP callback or a trusted-header "
      "path that does not exist). The Google Sign-In pattern is the cheapest template, and it proves local users rows "
      "remain mandatory under any IdP.",
      [ev("src/Common/Auth/OpenIDConnect/", "OpenEMR acts as an OIDC provider", symbol="OpenIDConnect"),
       ev("interface/login/login.php", "Google Sign-In branch - the only shipped RP-style federation",
          243, 244, command="git grep -n google_signin HEAD"),
       ev("(git)", "no generic RP: zero REMOTE_USER and zero mod_auth_openidc matches tree-wide",
          command="git grep -n REMOTE_USER HEAD  -> 0")],
      "CONFIRMED",
      "Keycloak as central IdP, OpenEMR as RP built on the Google Sign-In pattern; keep AuthUtils as break-glass. "
      "Budget real integration work - this is not a config toggle.",
      [A_AUTH]))

add(q("Q5", "Force MFA org-wide", "B-Identity",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "MFA implementation ships (MfaUtils, Totp.class.php, u2f-api.js) but there is NO enforcement global: "
      "`git grep force_mfa|mfa_required|gbl_force_mfa` returns 0 matches, and library/globals.inc.php contains no "
      "MFA global at all. AuthUtils.php has no MFA gate (only a doc comment at :12). Enrolment is per-user opt-in.",
      "Forcing MFA natively means a permanent class-c core patch re-applied every rebase. Enforcing it in the Q4 IdP "
      "makes it a policy setting. This is a strong independent argument for the external-IdP direction.",
      [ev("library/globals.inc.php", "no MFA global exists", command="git grep -n 'mfa' HEAD -- library/globals.inc.php  -> 0"),
       ev("src/Common/Auth/MfaUtils.php", "MFA implementation present but not centrally enforceable"),
       ev("src/Common/Auth/AuthUtils.php", "no MFA enforcement branch; only a doc comment", 12, 12)],
      "CONFIRMED",
      "Enforce MFA at the Keycloak layer for all clinical/admin roles. Do NOT patch core to add a force-MFA global.",
      [A_AUTH]))

add(q("Q6", "Reverse-proxy trust for REMOTE_USER", "B-Identity",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "There is no REMOTE_USER support anywhere: 0 matches tree-wide, all file types. mod_auth_openidc: 0 matches. "
      "Separately, the ONE proxy header the app does consume is trusted blindly - collectIpAddresses() appends the "
      "entire client-supplied X-Forwarded-For chain unparsed, with no trusted-proxy allowlist anywhere (0 matches).",
      "Q6 is not 'do we trust it' but 'we would have to build it'. Meanwhile the existing XFF handling is an "
      "audit-log forging vector that must be fixed regardless of the IdP decision.",
      [ev("(git)", "zero REMOTE_USER matches tree-wide", command="git grep -n REMOTE_USER HEAD"),
       ev("library/sanitize.inc.php", "raw XFF chain concatenated without validation", 29, 46, symbol="collectIpAddresses"),
       ev("src/Common/Logging/Audit/LogTablesSink.php", "unvalidated value reaches the log table", 70, 70),
       ev("src/Common/Logging/EventAuditLogger.php", "and the auth-failure comments field", 265, 266)],
      "CONFIRMED",
      "Do NOT build REMOTE_USER trust. Use a proper OIDC RP callback (Q4). Independently: parse XFF to the rightmost "
      "hop and add a trusted-proxy allowlist before Go-Live.",
      [A_AUTH, A_SEC]))

add(q("Q7", "Retire Google Sign-In path?", "B-Identity",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "Google Sign-In is a complete, working federation path, not dead code: 2 globals "
      "(google_signin_enabled :2251, google_signin_client_id :2258), a users.google_signin_email column with a unique "
      "constraint, a login-page branch, and full admin CRUD across 3 screens. It is default-off (requires both globals).",
      "It is the only in-tree worked example of 'external subject -> local users row', which is exactly the mapping "
      "Keycloak will need. Deleting it destroys the reference implementation for Q4 at zero runtime saving.",
      [ev("library/globals.inc.php", "enable + client-id globals", 2251, 2258),
       ev("interface/login/login.php", "login button rendered only when both globals set", 243, 244),
       ev("interface/usergroup/usergroup_admin.php", "persists users.google_signin_email; NULL when empty", 397, 399)],
      "CONFIRMED",
      "KEEP in code, leave disabled via globals. Use it as the implementation template for the Q4 IdP integration. "
      "Revisit removal only after Keycloak is in production.",
      [A_AUTH]))

add(q("Q8", "Tenant-admin UI for OAuth2 clients", "B-Identity",
      "technical_unknown", True, "answered", "LOCKED",
      "Dynamic Client Registration is LIVE, not merely possible. The discovery document advertises "
      "\"registration_endpoint\": \"$base_url/registration\" and a listener routes requests containing '/registration'. "
      "The existing ClientAdminController provides revoke/inspect only.",
      "DCR covers self-registering partners, so a provisioning UI is a convenience, not a prerequisite. Q8 can be "
      "deferred without blocking any integration that can self-register.",
      [ev("src/RestControllers/Authorization/OAuth2DiscoveryController.php", "registration_endpoint advertised", 72, 72),
       ev("src/RestControllers/Subscriber/OAuth2AuthorizationListener.php", "DCR request routing", 167, 167),
       ev("src/FHIR/SMART/ClientAdminController.php", "existing admin surface is revoke/inspect only", 46, 46)],
      "CONFIRMED",
      "DCR only for Day 1. Build the hand-provisioning UI when the first partner that cannot self-register appears.",
      [A_AUTH]))

add(q("Q9", "Fork ACL policy: bump v_acl=14 or freeze at 13?", "B-Identity",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "$v_acl = 13 at version.php:42 (alongside $v_database = 541 at :34). acl_upgrade.php reads the installed "
      "version via AclExtended::getAclVersion() and runs a SEQUENTIAL integer ladder of "
      "`$upgrade_acl = N; if ($acl_version < $upgrade_acl) {...}` blocks. The counter has no namespace.",
      "Mechanically confirms the never-bump rule: if the fork writes a 14 block and upstream later ships its own 14, "
      "a rebased site that already recorded 14 will SILENTLY SKIP upstream's block. Same failure class as $v_database.",
      [ev("version.php", "$v_acl = 13, $v_database = 541", 34, 42),
       ev("acl_upgrade.php", "sequential integer ladder keyed on a namespace-free counter", 14, 39),
       ev("acl_upgrade.php", "installed version read, defaults to 0", 65, 67, symbol="AclExtended::getAclVersion")],
      "CONFIRMED",
      "FREEZE at 13. Never author a downstream ACL upgrade block. Put all new authorization in a saas_* layer above gacl.",
      [A_AUTH]))

add(q("Q10", "Complete standalone-encounter SMART launch", "B-Identity",
      "technical_unknown", True, "contradicted", "LOCKED",
      "PREMISE CORRECTED. The CapabilityStatement does NOT falsely advertise standalone-encounter launch - "
      "context-standalone-encounter is explicitly COMMENTED OUT at Capability.php:50, and the "
      "CONTEXT_STANDALONE_ENCOUNTER constant (:103) is declared but never referenced. The real defect is different: "
      "CONTEXT_EHR_ENCOUNTER IS advertised (:48) while the launch/encounter scope is absent from every grantable "
      "scope list (ServerScopeListEntity.php:53 and ScopeRepository.php:248 list launch/patient only). All 4 "
      "launch/encounter matches are documentation.",
      "Scope shrinks and splits: a 2-line scope-list fix resolves the advertised-vs-grantable inconsistency; the "
      "encounter picker is a separate, larger deliverable. Docs currently show examples that would fail.",
      [ev("src/FHIR/SMART/Capability.php", "CONTEXT_EHR_ENCOUNTER advertised; standalone commented out", 48, 52),
       ev("src/FHIR/SMART/Capability.php", "CONTEXT_STANDALONE_ENCOUNTER declared and never used", 103, 103),
       ev("src/Common/Auth/OpenIDConnect/Entities/ServerScopeListEntity.php", "launch/patient only", 53, 53),
       ev("Documentation/api/SMART_ON_FHIR.md", "docs show launch/encounter examples that cannot be granted", 793, 845)],
      "CONFIRMED",
      "Fix the advertised-vs-grantable inconsistency first (add launch/encounter to both scope lists, or stop "
      "advertising context-ehr-encounter). Treat the encounter picker as a separate deliverable tied to NPHIES.",
      [A_AUTH]))

# ============================================================ C: tenancy

add(q("Q11", "DB-per-tenant vs shared-DB", "C-Tenancy",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "The true shared-DB refactor surface is 6,785 data-access call sites, not the 1,875 previously estimated: "
      "sqlStatement( 2,025 + QueryUtils:: 1,653 + sqlQuery( 1,454 + sqlFetchArray( 1,354 + sqlInsert( 251 + "
      "Doctrine DBAL 48, plus 202 OE_SITE_DIR file-path sites. No core table has a tenant_id/site_id column. "
      "Tenancy is enforced by which sqlconf.php is loaded - the connection IS the boundary.",
      "Model B is ~3.6x more expensive than the estimate the decision was based on. Model A is what the runtime "
      "already implements. This is the single strongest quantitative argument in the audit.",
      [ev("evidence/raw/remaining-counts.tsv", "6,785 total data-access call sites, per-sink lists saved",
          command="bash tools/discovery/openemr-decision-evidence/collect-remaining-evidence.sh"),
       ev("interface/globals.php", "site id resolved then sqlconf.php loaded; connection is the tenancy boundary", 277, 335),
       ev("sql/database.sql", "no tenant_id/site_id discriminator on any core table")],
      "CONFIRMED",
      "LOCK Model A (DB-per-tenant). Model B is not economically viable at 6,785 call sites.",
      [A_DB, A_CP]))

add(q("Q12", "Tenant routing scheme", "C-Tenancy",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "Hostname->site routing EXISTS but only for unauthenticated requests: interface/globals.php:295-297 and "
      "index.php:14 consult HTTP_HOST only when $ignoreAuth is set. Authenticated traffic resolves site from "
      "$_GET['site'] (wins) or session site_id. A regex allowlist at globals.php:304 is the sole guard.",
      "Subdomain routing for authenticated traffic needs a small, well-bounded globals.php patch (lift the HTTP_HOST "
      "branch out of the $ignoreAuth guard) PLUS per-subdomain cookie scoping (Q17). It is not free but it is small.",
      [ev("interface/globals.php", "HTTP_HOST consulted only when auth is ignored", 283, 297),
       ev("interface/globals.php", "$_GET['site'] takes precedence over session", 277, 281),
       ev("interface/globals.php", "regex allowlist is the only validation of the site string", 304, 304)],
      "CONFIRMED",
      "Subdomain-per-tenant, implemented as a minimal globals.php patch plus cookie scoping. Never expose ?site= "
      "publicly - it remains accepted and takes precedence.",
      [A_CP]))

add(q("Q13", "Tenant-count ceiling", "C-Tenancy",
      "business_product_decision", True, "partially_answered", "PROVISIONAL",
      "Repository establishes the binding constraints, not the number. Connection pooling is per-{Apache worker x DSN} "
      "(DatabaseConnectionFactory.php:113,137-151), so DB-per-tenant yields up to N_tenants x M_workers persistent "
      "handles. Cross-site iteration is an opendir(sites/) walk (admin.php:75-115). Background jobs run once per site "
      "per cron via bin/console --site=<name>.",
      "The ceiling is set by connection-handle math and per-site cron fan-out, both of which are measurable before "
      "committing. 500 tenants implies a jobs consolidator and pooling discipline.",
      [ev("src/BC/DatabaseConnectionFactory.php", "persistent p:host connections, per worker per DSN", 113, 151),
       ev("admin.php", "cross-site control plane is an opendir walk over sites/", 75, 115)],
      "MEDIUM",
      "Provisional: design for 500, revisit at 200. Disable connection pooling or cap workers; build a jobs "
      "consolidator before tenant 100.",
      [A_CP], remaining_gap="Target tenant count is a commercial forecast.",
      external=True, remaining_input="Product: 24-month tenant forecast."))

add(q("Q14", "Per-tenant Apache/TLS/observability config location", "C-Tenancy",
      "deployment_decision", True, "partially_answered", "PROVISIONAL",
      "No per-tenant vhost/TLS/log-shipping config exists in the repo. The only per-site executable seam is "
      "sites/<site>/config.php, require_once'd at interface/globals.php:649.",
      "Nothing in-repo to migrate; the decision is purely organisational. Note that sites/<site>/config.php is an "
      "arbitrary-PHP execution point per tenant and must be operator-controlled, never tenant-writable.",
      [ev("interface/globals.php", "per-site arbitrary PHP require_once - the only per-site executable seam", 649, 649)],
      "HIGH",
      "Provisional: separate infra repo (matches Q41). Treat sites/<site>/config.php as operator-only.",
      [A_CP, A_FE], external=True, remaining_input="Ops: repo topology and release-coupling preference."))

add(q("Q15", "Cross-tenant analytics: Day-1 or Day-N?", "C-Tenancy",
      "business_product_decision", True, "partially_answered", "PROVISIONAL",
      "Under the locked Model A there is no cross-tenant query path: each site is a physically separate database "
      "with no shared identifiers, and site-local ids collide by design (two sites both have documents.id = 1). "
      "Cross-tenant analytics therefore requires an ETL into a warehouse keyed on (site_name, entity_id) or UUIDs.",
      "If analytics is Day-1, ETL scaffolding must be in the MVP and the id-namespacing rule must be enforced from "
      "the first custom table. Retrofitting is expensive.",
      [ev("sql/database.sql", "site-local ids collide across tenants; no global identifier space"),
       ev("src/Services/InsuranceCompanyService.php", "documented shared id-space; ids are site-local", 433, 436)],
      "HIGH",
      "Day-N for dashboards, but enforce the (site_name, entity_id) / UUID rule from day one so the ETL stays possible.",
      [A_DB, A_CP], external=True, remaining_input="Product: whether HQ dashboards are an MVP commitment."))

add(q("Q16", "Central identity across tenants at Day-1?", "C-Tenancy",
      "locked_architectural_decision", True, "answered", "PROVISIONAL",
      "The session cookie name is a global constant - CORE_SESSION_ID = 'OpenEMR' at "
      "src/Common/Session/SessionUtil.php:81 - so one browser holds one tenant login at a time. Additionally every "
      "human needs a LOCAL users row plus gacl_aro records per tenant DB; no code path consults an external "
      "authorization service.",
      "Cross-tenant SSO requires BOTH a cookie-naming patch (Q17) AND a central identity-to-local-user mapping. "
      "Authentication can federate; authorization cannot leave the tenant DB.",
      [ev("src/Common/Session/SessionUtil.php", "cookie name is a global constant", 81, 81),
       ev("interface/login/login.php", "Google Sign-In maps external subject onto a local users row", 243, 244)],
      "CONFIRMED",
      "No cross-tenant SSO Day-1. Federate authentication via Keycloak per tenant; keep authorization tenant-local.",
      [A_AUTH, A_CP]))

add(q("Q17", "Per-tenant cookie names?", "C-Tenancy",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "CORE_SESSION_ID is a hardcoded constant 'OpenEMR' (SessionUtil.php:81). Namespacing it per site is a small, "
      "well-localised class-c patch, but it is a permanent carry across every rebase.",
      "Directly gates Q16 and interacts with Q12: subdomain routing without cookie scoping lets one tenant's cookie "
      "be presented to another tenant's host.",
      [ev("src/Common/Session/SessionUtil.php", "hardcoded global cookie name", 81, 81)],
      "CONFIRMED",
      "Accept the one-tenant-per-browser constraint Day-1, BUT set cookie Domain/Path per subdomain at the proxy so "
      "Q12 is safe. Patch SessionUtil only if Q16 flips.",
      [A_CP]))

# ============================================================ D: Saudi/Arabic

add(q("Q18", "Arabic UI baseline: extend existing or greenfield?", "D-Saudi",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "Arabic coverage measured at 47.53%: 6,290 Arabic rows out of 13,234 unique lang_constants in "
      "contrib/util/language_translations/currentLanguage_utf8.sql. The default installer ships EN-only "
      "(sql/database.sql:3569) so Arabic requires a post-install import. The frontend i18next layer reads the SAME "
      "lang_* tables (interface/main/tabs/main.php:335-349) - there is no separate JSON catalogue to maintain.",
      "Because the frontend already piggybacks on the backend catalogue, extending the SQL catalogue fixes both "
      "legacy and modern screens at once. Greenfield JSON would fork the catalogue in two.",
      [ev("contrib/util/language_translations/currentLanguage_utf8.sql", "6,290/13,234 = 47.53% Arabic coverage"),
       ev("sql/database.sql", "installer ships EN-only", 3569, 3569),
       ev("interface/main/tabs/main.php", "i18next fetches from the same lang_* tables", 335, 349)],
      "CONFIRMED",
      "Extend the existing SQL catalogue; do not start a parallel JSON catalogue. Budget translation of the missing "
      "6,944 constants plus MSA terminology review.",
      [A_L10N]))

add(q("Q19", "Hijri calendar policy", "D-Saudi",
      "business_product_decision", True, "answered", "PROVISIONAL",
      "Zero Hijri support exists: no matches for 'hijri', 'IntlCalendar', or 'moment-hijri' anywhere in tracked files. "
      "library/date_functions.php:43-54 is hard-Gregorian.",
      "Entirely greenfield: a HijriDate value type, PHP IntlCalendar wrappers, moment-hijri (~10 kB), dual-calendar "
      "pickers, and formatters for DOB/appointment/lab/PDF paths. Cost is driven by how many surfaces must show it.",
      [ev("library/date_functions.php", "hard-Gregorian date handling", 43, 54),
       ev("(git)", "zero hijri/IntlCalendar/moment-hijri matches", command="git grep -i hijri HEAD  -> 0")],
      "CONFIRMED",
      "Provisional: dual-display on appointment/billing screens, Gregorian-only on clinical timestamps. Scope by "
      "surface count, not by 'add Hijri support'.",
      [A_L10N], external=True, remaining_input="Product/clinical: which fields must show Hijri."))

add(q("Q20", "Currency policy", "D-Saudi",
      "business_product_decision", True, "answered", "LOCKED",
      "No multi-currency schema exists: no currency column on billing (sql/database.sql:245-278) or any financial "
      "table. Display symbol is a single global gbl_currency_symbol defaulting to '$' (library/globals.inc.php:820).",
      "SAR-only is nearly free (set one global, patch the FHIR hardcode per Q26). Multi-currency requires schema "
      "changes, an FX table, and currency-awareness in every report - a disproportionate cost for single-country KSA.",
      [ev("sql/database.sql", "no currency column on billing", 245, 278),
       ev("library/globals.inc.php", "single global currency symbol, defaults to $", 820, 820)],
      "CONFIRMED",
      "SAR only. Set gbl_currency_symbol, fix Q26. Defer multi-currency until a second country is actually sold.",
      [A_L10N]))

add(q("Q21", "ZATCA e-invoicing scope", "D-Saudi",
      "regulatory_external_input", True, "partially_answered", "PROVISIONAL",
      "Zero ZATCA support: no matches for zatca, fatoora, invoice_hash, qr_code_invoice, or e-invoice. Tax "
      "infrastructure is a rates registry only (list_options list_id='taxrate' at sql/database.sql:4354; "
      "codes.taxrates colon-list at :1135). There is NO tax-amount column on billing.",
      "Even ZATCA Phase 1 needs a new tax-amount persistence layer, not just a QR encoder - the schema cannot record "
      "the tax actually charged. Phase 2 adds XML signing, device onboarding and Fatoora API integration.",
      [ev("sql/database.sql", "tax rates registry exists but no tax-amount column on billing", 4354, 4354),
       ev("(git)", "zero zatca/fatoora matches", command="git grep -i zatca HEAD  -> 0")],
      "CONFIRMED",
      "Provisional Phase 2, but sequence it: (1) add tax-amount persistence, (2) Phase 1 QR, (3) Phase 2 clearance. "
      "Step 1 is a prerequisite for both.",
      [A_L10N], remaining_gap="ZATCA's binding compliance deadline for this taxpayer group.",
      external=True, remaining_input="Legal/Finance: ZATCA wave assignment and deadline."))

add(q("Q22", "MSA vs Saudi-dialect terminology", "D-Saudi",
      "business_product_decision", False, "external_decision_required", "PROVISIONAL",
      "The bundled catalogue is MSA of unmeasured fidelity (47.53% coverage per Q18). The repository cannot "
      "adjudicate dialect preference.",
      "Affects translation cost and glossary maintenance, not architecture. Mixing the two is the worst outcome.",
      [ev("contrib/util/language_translations/currentLanguage_utf8.sql", "bundled catalogue is MSA")],
      "LOW",
      "Provisional: MSA everywhere; revisit patient-portal wording only after user testing.",
      [A_L10N], external=True, remaining_input="Product + Saudi clinical reviewer: dialect policy."))

add(q("Q23", "Per-user timezone/language preference", "D-Saudi",
      "technical_unknown", True, "answered", "LOCKED",
      "Timezone is site-wide: gbl_time_zone (library/globals.inc.php:777) applied at interface/globals.php:520, with "
      "a UTC default at bootstrap.php:30. Language is already per-session via language_choice, and a per-user "
      "override mechanism exists generically through user_settings (proven for css_header at globals.php:437-450).",
      "Saudi is a single timezone, so site-wide is sufficient there. Per-user language needs no new mechanism - "
      "user_settings already supports per-user global overrides.",
      [ev("library/globals.inc.php", "site-wide timezone global", 777, 777),
       ev("interface/globals.php", "timezone applied per request", 520, 520),
       ev("interface/globals.php", "user_settings overlay pattern already used for per-user globals", 437, 450)],
      "CONFIRMED",
      "Site-wide timezone (Asia/Riyadh). Per-user language toggle via the existing user_settings overlay - no new "
      "schema required.",
      [A_L10N]))

add(q("Q24", "bootstrap-rtl sustainability", "D-Saudi",
      "technical_unknown", True, "answered", "LOCKED",
      "bootstrap-rtl is fetched by napa as a pinned single-commit GitHub archive of an unmaintained third-party fork "
      "(package.json:113). If that URL disappears the build breaks with no local copy.",
      "This is a live build-availability risk, not a styling preference. Vendoring is a same-day mitigation "
      "independent of any Bootstrap 5 decision.",
      [ev("package.json", "napa-pinned single-commit archive of an unmaintained fork", 113, 113)],
      "CONFIRMED",
      "Vendor the current zip into the repo IMMEDIATELY (removes the availability risk today). Plan the Bootstrap 5 "
      "native-RTL migration as a separate Phase-2 item.",
      [A_L10N]))

add(q("Q25", "PDF Arabic fonts", "D-Saudi",
      "technical_unknown", True, "answered", "LOCKED",
      "No Arabic-capable font is tracked: git ls-files finds no amiri*, noto*naskh*, noto*sans*arabic*, or dejavu* "
      "font files. After composer install, mPDF's transitive DejaVuSans is the only fallback - a fallback, not a "
      "professional Arabic typeface. interface/themes/rtl_style_pdf.css does ship.",
      "Every Arabic PDF (invoices, prescriptions, future ZATCA output) renders with inadequate typography until a "
      "proper face is bundled. One-time ~5-10 MB cost, no downside.",
      [ev("(git)", "no Arabic font files tracked", command="git ls-files | grep -iE 'amiri|naskh|arabic'  -> 0"),
       ev("interface/themes/rtl_style_pdf.css", "RTL PDF stylesheet ships")],
      "CONFIRMED",
      "Bundle Amiri and Noto Naskh Arabic; wire into the mPDF font map. Do this before any Arabic PDF is shown to a "
      "customer.",
      [A_L10N]))

add(q("Q26", "Hardcoded USD in FHIR Coverage", "D-Saudi",
      "technical_unknown", True, "answered", "LOCKED",
      "src/Services/FHIR/FhirCoverageService.php:294 hard-codes 'USD', alongside 5 other USD hardcodes identified in "
      "the prior localization audit.",
      "Small, isolated patch. Because the fork carries zero patches today (Q1), an upstream PR keeps that record "
      "clean; a local wrapper starts the class-c patch ledger.",
      [ev("src/Services/FHIR/FhirCoverageService.php", "hardcoded USD currency", 294, 294)],
      "CONFIRMED",
      "Upstream PR the fix (benefits every fork and preserves the zero-patch record). Carry a local wrapper only if "
      "the PR stalls past the NPHIES milestone.",
      [A_L10N, A_FHIR]))

# ============================================================ E: NPHIES/claims

add(q("Q27", "FHIR Claim submission architecture", "E-NPHIES",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "Option A is blocked today: Coverage is read-only and Claim/ClaimResponse have NO HTTP surface. Option B is "
      "blocked by missing billing events (Q30) - src/Billing/ contains zero event dispatches. Option C (polling) "
      "works with zero core changes and is exactly what the shipped claimrev-connect module does.",
      "Only Option C is executable today without core patches. The evidence directly supports the phased plan rather "
      "than an architectural argument.",
      [ev("src/Billing/BillingProcessor/BillingProcessor.php", "hard-coded if/elseif task selection; no events", 161, 192),
       ev("src/Services/FHIR/FhirCoverageService.php", "Coverage is read-only"),
       ev("evidence/raw/count-rest_controllers.txt", "90 REST controllers; no Claim/ClaimResponse HTTP surface")],
      "CONFIRMED",
      "Option C (background polling) for MVP, mirroring claimrev-connect. Migrate to Option A after the Q30 events "
      "and the Q28 write surface land.",
      [A_FHIR]))

add(q("Q28", "Ownership of FHIR write-surface build", "E-NPHIES",
      "action_item", True, "partially_answered", "PROVISIONAL",
      "The type surface is partially free but the HTTP surface is absent. 103 FHIR service files and 90 REST "
      "controllers exist, with 491 FhirSearchParameterDefinition registrations - but Coverage(write), Claim, "
      "ClaimResponse, EOB, CoverageEligibilityRequest/Response and PaymentNotice have no controllers. "
      "FHIRClaim/FHIRClaimResponse data classes do exist under src/FHIR/R4/.",
      "Each resource is controller + FhirService mapping on top of an existing generated data class - a repeatable "
      "1-2 sprint unit, not a research task.",
      [ev("evidence/manifests/q62-fhir-service-files.txt", "103 FHIR service files"),
       ev("evidence/raw/count-rest_controllers.txt", "90 REST controllers, none for Claim/ClaimResponse"),
       ev("src/FHIR/R4/", "FHIRClaim / FHIRClaimResponse data classes present")],
      "HIGH",
      "Fold into the NPHIES module deliverable that owns Q27. Verify EOB/Eligibility*/PaymentNotice data classes "
      "exist before sizing.",
      [A_FHIR], remaining_gap="Per-resource data-class verification for EOB/Eligibility*/PaymentNotice not completed."))

add(q("Q29", "x12_partners reuse vs new nphies_partners table", "E-NPHIES",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "x12_partners carries OAuth columns (x12_token_endpoint, x12_client_id, x12_client_secret) that would "
      "technically fit NPHIES. But the custom_ prefix is NOT reserved by upstream (zero baseline tables, zero upgrade "
      "files, zero module SQL use it), and shipped modules use vendor-slug prefixes instead.",
      "Reusing an X12-named table for Saudi FHIR overloads the semantics and complicates every rebase diff. A new "
      "table is cleaner and the prefix decision is settled by Q58.",
      [ev("evidence/snippets/q68-custom-prefix-evidence.md", "custom_ prefix unused and unreserved upstream"),
       ev("sql/database.sql", "x12_partners OAuth columns exist but are X12-semantic")],
      "CONFIRMED",
      "New saas_nphies_partners table (saas_ prefix per Q58). Do not overload x12_partners.",
      [A_DB, A_FHIR]))

add(q("Q30", "Missing billing/core events: upstream PR or polling emulation?", "E-NPHIES",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "src/Billing/ contains NO event dispatch, no factory, no registry and no service-locator lookup. Task selection "
      "is a hard-coded if/elseif ladder keyed on $_POST['bn_*'] strings at BillingProcessor.php:161-192. There is no "
      "transaction boundary and no idempotency key; claim status is written row-by-row with auto-commit "
      "(GeneratorX12.php:151,168).",
      "Event-driven NPHIES is impossible today without a core patch. The absent transaction boundary also means an "
      "outbox pattern cannot be added without touching the same code.",
      [ev("src/Billing/BillingProcessor/BillingProcessor.php", "hard-coded task ladder, no dispatch", 161, 192),
       ev("src/Billing/BillingProcessor/Tasks/GeneratorX12.php", "row-by-row auto-commit, no transaction/idempotency", 151, 168)],
      "CONFIRMED",
      "Do both in parallel: ship with polling (Q27 Option C), and open a narrow upstream PR converting the if/elseif "
      "ladder into a task registry plus one pre-dispatch event - a change confined to BillingProcessor.php.",
      [A_FHIR]))

add(q("Q31", "Keep claimrev-connect in Saudi deployments?", "E-NPHIES",
      "business_product_decision", True, "answered", "LOCKED",
      "Verified empirically on a populated vendor/ tree (not available to the prior audit): "
      "claimrevolution/oe-module-claimrev-connect v2.1.6 is the ONLY openemr-module type package in "
      "vendor/composer/installed.json. Composer installs it as a REAL directory (not a symlink) of 134 files at "
      "interface/modules/custom_modules/oe-module-claimrev-connect, which .gitignore:15 excludes from tracking.",
      "It is a genuine runtime dependency of composer install, so removing it means editing composer.json - a fork "
      "patch. Disabling it at provisioning time costs nothing and keeps the zero-patch record intact.",
      [ev("vendor/composer/installed.json", "only openemr-module package; v2.1.6; install-path into custom_modules"),
       ev(".gitignore", "module directory excluded from tracking because composer owns it", 15, 15),
       ev("composer.json", "runtime require ^2.1", 52, 52)],
      "CONFIRMED",
      "Keep in composer.json (preserves zero-patch record and upstream test parity); disable in Saudi tenant "
      "provisioning defaults.",
      [A_DEP]))

# ============================================================ F: frontend

add(q("Q32", "Patient portal strategy", "F-Frontend",
      "business_product_decision", True, "partially_answered", "PROVISIONAL",
      "The portal is a separate Smarty-based app with its own credentials (patient_access_onsite) and its own logo "
      "resolution (portal/index.php:62-64, portal/home.php:87,362), fronted by portal REST endpoints. Swapping it "
      "does not disturb the main UI.",
      "The isolation is real, which de-risks a greenfield SPA: it can be built alongside the existing portal and "
      "cut over per tenant.",
      [ev("portal/index.php", "portal has independent logo/theme resolution", 62, 64),
       ev("interface/globals.php", "portal theme selected separately via patient_settings", 486, 495)],
      "HIGH",
      "Provisional: rebrand for MVP, greenfield SPA in Phase 2, exploiting the portal's isolation to cut over "
      "tenant-by-tenant.",
      [A_FE], external=True, remaining_input="Product: patient-facing mobile expectations at launch."))

add(q("Q33", "Main-UI strategy", "F-Frontend",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "Q72's scan corrects the '611 grid files' figure: 5,460 first-party UI files scanned, of which 575 are legacy "
      "iframe entry points or files they include, 1,098 shared templates, and 416 custom-module screens. Shell "
      "replacement means touching the legacy iframe population, not 611 arbitrary files.",
      "A full shell replacement is a multi-quarter project against a measured, not guessed, surface. Tab-by-tab SPA "
      "embedding is bounded and provable.",
      [ev("18-q72-ui-responsiveness-inventory.csv", "5,460 files classified; 575 legacy iframe entry/included files"),
       ev("19-q72-ui-responsiveness-summary.md", "reconciled classification totals")],
      "CONFIRMED",
      "Stay on BS4/Twig; embed SPAs tab-by-tab as new modules require them. Do not attempt shell replacement.",
      [A_Q72C, A_Q72S]))

add(q("Q34", "Themes: how many Saudi variants?", "F-Frontend",
      "business_product_decision", True, "answered", "LOCKED",
      "Themes are shared immutable CSS under public/themes/, chosen by FILENAME via globals.css_header with a "
      "per-user user_settings override, and gated by a file_exists() check at interface/globals.php:476 that blocks "
      "arbitrary filenames. The dropdown is a filesystem scan (edit_globals.php:714-731), not a DB list. RTL is a "
      "filename substitution (rtl_<name>.css) at globals.php:551-611.",
      "Per-tenant brand colour must NOT be a per-tenant CSS file - the runtime has no per-site CSS include at all "
      "(Q59). It has to be a token/variable override on a shared bundle.",
      [ev("interface/globals.php", "file_exists gate blocks arbitrary theme filenames", 474, 483),
       ev("interface/globals.php", "RTL variant selected by filename substitution", 551, 611),
       ev("interface/super/edit_globals.php", "theme list is a filesystem scan", 714, 731)],
      "CONFIRMED",
      "Ship 2 themes (light-RTL, dark-RTL). Implement per-tenant branding as CSS-variable design tokens over a shared "
      "immutable bundle plus per-site logos - never per-tenant CSS files.",
      [A_FE]))

add(q("Q35", "CKEditor 5 Arabic bundling", "F-Frontend",
      "technical_unknown", True, "answered", "LOCKED",
      "CKEditor 5 is configured at library/js/nncustom_config.js:198 and library/js/limitedcustom_config.js:259, and "
      "grep for language/direction/rtl/ltr in both returns ZERO hits. The @ckeditor/ckeditor5-language 47.6.2 package "
      "IS already present in package-lock.json:1173-1175 but is never wired.",
      "The dependency is already paid for; this is a config change, not a bundling project. Without it the WYSIWYG "
      "toolbar stays English in Arabic tenants.",
      [ev("library/js/nncustom_config.js", "no language/direction config", 198, 198),
       ev("package-lock.json", "ckeditor5-language already a dependency", 1173, 1175)],
      "CONFIRMED",
      "Set language: 'ar' and contentsLangDirection: 'rtl' in both OE CKEditor configs. Trivial - do it with Q18.",
      [A_L10N]))

# ============================================================ G: modules

add(q("Q36", "Byte identity of in-tree oe-module directories", "G-Modules",
      "technical_unknown", True, "answered", "LOCKED",
      "SEVEN in-tree module directories exist (not six), and ZERO have fork-only modifications. Against v8_2_0 only "
      "one file differs - oe-module-comlink-telehealth/tests/bootstrap.php - and that difference is caused by an "
      "UPSTREAM commit present in HEAD but not in the release cut (0ec6697e0 'feat(bc): add internal deprecation "
      "utility (#12753)'). Working tree is clean: git status over custom_modules returns 0 entries. "
      "oe-module-claimrev-connect is composer-installed and gitignored, so it has no tracked blobs to compare.",
      "The blocking premise behind Q36 dissolves: there is no per-module hotfix ledger to maintain, and the "
      "fork-vs-upstream module diff is provably empty.",
      [ev("(git)", "single differing file vs v8_2_0, attributable to an upstream commit",
          command="git diff --name-status v8_2_0 HEAD -- interface/modules/custom_modules/"),
       ev("(git)", "the differing file's change comes from upstream commit 0ec6697e0",
          command="git log --oneline v8_2_0..HEAD -- .../oe-module-comlink-telehealth/tests/bootstrap.php"),
       ev("(git)", "no untracked or modified module files", command="git status --porcelain interface/modules/custom_modules/  -> empty")],
      "CONFIRMED",
      "Treat all seven tracked modules as pristine upstream. No hotfix tracking needed. Re-run the one-line diff "
      "each rebase to keep the claim true.",
      [A_Q36, A_MOD]))

add(q("Q37", "openemr/oe-module-installer-plugin internals", "G-Modules",
      "technical_unknown", True, "answered", "LOCKED",
      "Read from installed source (vendor/ populated on this machine; unavailable to the prior audit). Plugin class: "
      "OpenEMR\\Composer\\ModuleInstallerPlugin\\Plugin (Plugin.php:9), which on activate() registers "
      "CustomModuleInstaller and ZendModuleInstaller. CustomModuleInstaller extends Composer's LibraryInstaller, "
      "supports() only 'openemr-module', and getInstallPath() returns "
      "interface/modules/custom_modules/<LAST SEGMENT OF PACKAGE NAME> (CustomModuleInstaller.php:13-20). Confirmed "
      "in practice: installed.json maps claimrev-connect to that path and the result is a real directory of 134 "
      "files, not a symlink.",
      "OVERLAY RISK IDENTIFIED: the install path derives ONLY from the package name's last segment and ignores the "
      "vendor. Any composer package named <anyvendor>/oe-module-weno would resolve to the SAME directory as the "
      "tracked upstream oe-module-weno. Packaging our own modules must use names that cannot collide with upstream "
      "module directory names.",
      [ev("vendor/openemr/oe-module-installer-plugin/src/Plugin.php", "plugin class and installer registration", 9, 17),
       ev("vendor/openemr/oe-module-installer-plugin/src/CustomModuleInstaller.php",
          "install path = custom_modules/<last name segment>; vendor ignored", 13, 20),
       ev("vendor/composer/installed.json", "claimrev-connect v2.1.6 installed to that path as a real directory")],
      "CONFIRMED",
      "Name our packages so the last segment is unique (e.g. saas/oe-module-saas-nphies). Add a CI check asserting "
      "that no composer package resolves onto a tracked module directory.",
      [A_DEP, "docs/discovery/openemr-decision-evidence/evidence/snippets/q37-q70-installer-plugin-analysis.md"]))

add(q("Q38", "Twig namespacing convention for custom modules", "G-Modules",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "TwigContainer::addPath() supports namespaces but no convention is enforced in-tree, so template resolution is "
      "order-dependent and two modules shipping layout.twig collide silently.",
      "The collision is silent and surfaces at rebase/deploy time. Enforcing a prefix costs one scaffolding line.",
      [ev("src/Common/Twig/TwigContainer.php", "addPath supports namespaces; no convention enforced", symbol="TwigContainer::addPath")],
      "HIGH",
      "Enforce a @<module_slug>/ namespace prefix in the module scaffolding template and assert it in CI.",
      [A_DEP]))

# ============================================================ H: devops

add(q("Q39", "Do we publish our own Docker images?", "H-DevOps",
      "deployment_decision", True, "partially_answered", "PROVISIONAL",
      "59 GitHub workflows ship, including the docker-build/docker-release family. Because the fork will carry "
      "Arabic assets, Amiri/Noto fonts (Q25), a vendored bootstrap-rtl (Q24) and an NPHIES module, the runtime "
      "artifact necessarily diverges from upstream's image.",
      "The divergence list is now concrete, which makes 'publish our own' a consequence of earlier locked decisions "
      "rather than an independent choice.",
      [ev("evidence/manifests/q39-workflow-inventory.txt", "59 workflows including docker build/release family")],
      "HIGH",
      "Publish our own images to a private registry. Adopt upstream's docker-build workflows as the starting point.",
      ["docs/discovery/openemr-decision-evidence/evidence/manifests/q39-workflow-inventory.txt"],
      external=True, remaining_input="Ops: registry choice and image-signing policy."))

add(q("Q40", "Inferno ONC certification in scope?", "H-DevOps",
      "business_product_decision", True, "answered", "PROVISIONAL",
      "Inferno ships as two git submodules (ci/inferno/onc-certification-g10-test-kit and ci/inferno/inferno-files) "
      "plus inferno-test.yml. It validates US ONC / US Core conformance, which has no Saudi regulatory force.",
      "Keeping it costs CI minutes but provides a regression net over the FHIR surface that NPHIES work will touch. "
      "Removing it frees the fork to diverge from US Core.",
      [ev("evidence/manifests/q40-inferno-artifacts.txt", "inferno submodules and workflow present")],
      "CONFIRMED",
      "Remove from required checks, keep the config so re-enabling is one line. Re-evaluate if US Core divergence "
      "becomes deliberate.",
      ["docs/discovery/openemr-decision-evidence/evidence/manifests/q40-inferno-artifacts.txt"],
      external=True, remaining_input="Product: whether any customer requires ONC certification."))

add(q("Q41", "k8s/Helm charts - in-fork or infra repo?", "H-DevOps",
      "deployment_decision", True, "answered", "PROVISIONAL",
      "Zero Helm/k8s artifacts exist in the repository (same evidence as Q3). Nothing to migrate either way.",
      "Because there is no existing chart, choosing the infra repo now costs nothing; choosing in-fork later would "
      "require a move.",
      [ev("evidence/manifests/q3-deployment-artifacts.txt", "no chart/values/k8s manifests tracked")],
      "CONFIRMED",
      "Separate infra repo, referenced by SHA from this fork's release notes (matches Q14).",
      ["docs/discovery/openemr-decision-evidence/evidence/manifests/q3-deployment-artifacts.txt"], external=True,
      remaining_input="Ops: repo topology."))

add(q("Q42", "Production X-Forwarded-For handling", "H-DevOps",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "The application trusts XFF blindly. collectIpAddresses() (library/sanitize.inc.php:29-46) appends the ENTIRE "
      "client-supplied chain, unparsed, to ip_string; there is no trusted-proxy allowlist anywhere (0 matches). The "
      "value reaches the log table (LogTablesSink.php:70) and auth-failure comments (EventAuditLogger.php:265-266). "
      "Mitigation: the real socket peer is preserved separately in the 'ip' key.",
      "This is a code-level audit-log forging vector, not merely a deployment posture question as originally framed. "
      "It must be fixed in code or neutralised at the proxy before Go-Live.",
      [ev("library/sanitize.inc.php", "entire client-controlled XFF chain appended unvalidated", 29, 46,
          symbol="collectIpAddresses"),
       ev("src/Common/Logging/Audit/LogTablesSink.php", "flows into the log table IP column", 70, 70),
       ev("(git)", "no trusted-proxy configuration exists", command="git grep -in 'trustedproxies|trusted_proxy' HEAD  -> 0")],
      "CONFIRMED",
      "Strip inbound XFF at the edge and re-add exactly one hop from our own LB; additionally parse to the rightmost "
      "entry in code. Treat existing forward_ip values as untrusted.",
      [A_SEC, A_AUTH]))

# ============================================================ I: security

add(q("Q43", "Rotate exposed GitHub PATs", "I-Security",
      "action_item", True, "answered", "LOCKED",
      "SCOPE IS LARGER THAN RECORDED: 4 compose files x 3 variables = 12 hardcoded token values, not 2 in one file. "
      "Locations: development-easy:75-77, development-easy-light:75-77, development-easy-redis:180-182, "
      "development-insane:224-226. Three obfuscation layers are used for the same secret class (raw, base64, "
      "space-separated decimal char codes), all decoded inline by docker/flex/openemr.sh:766-790. All values REDACTED "
      "and never written to any artifact.",
      "Naive ghp_-prefix scanners miss two of the three variants, which is why only one file was previously found. "
      "Critically, these are UPSTREAM's committed tokens - the fork has zero own commits - so rotation is upstream's "
      "action; the fork's duty is containment.",
      [ev("docker/development-easy/docker-compose.yml", "3 hardcoded token variables [REDACTED]", 75, 77),
       ev("docker/development-easy-redis/docker-compose.yml", "same 3 variables [REDACTED]", 180, 182),
       ev("docker/flex/openemr.sh", "decodes all three obfuscation layers inline", 766, 790)],
      "CONFIRMED",
      "Treat as burned. Report upstream. Ensure they never reach a published image or public fork. Add a secret "
      "scanner that detects base64 and decimal-encoded token variants, not just ghp_.",
      [A_SEC]))

add(q("Q44", "Per-tenant KMS key isolation Day-1?", "I-Security",
      "locked_architectural_decision", True, "answered", "PROVISIONAL",
      "No KMS SDK exists in composer.json. Keys are per-site files on disk: "
      "$OE_SITE_DIR/documents/certificates/oa{private,public}.key (OAuth2KeyConfig.php:63-64) and "
      "sites/<site>/documents/logs_and_misc/methods/.",
      "DB-per-tenant ALREADY delivers per-tenant key isolation by construction. A KMS adds rotation, escrow and HSM "
      "custody - not isolation. This materially shrinks Day-1 scope.",
      [ev("src/Common/Auth/OAuth2KeyConfig.php", "per-site key files on disk", 63, 64),
       ev("composer.json", "no KMS/Vault/SecretsManager SDK required")],
      "HIGH",
      "Day-1: keep per-site key files (isolation already achieved); add a KMS-backed wrapper for CUSTODY and rotation "
      "of the site key material. Do not rebuild encryption.",
      [A_SEC, A_CP]))

add(q("Q45", "Saudi PDPL data-residency plan", "I-Security",
      "regulatory_external_input", False, "external_decision_required", "PROVISIONAL",
      "No repository evidence can answer this. The repo constrains only that each tenant is a separate database and "
      "separate filesystem tree, which makes per-region placement mechanically straightforward.",
      "Residency drives cloud provider, region, backup destinations and observability endpoints - all outside the "
      "codebase. Model A makes per-region tenant placement easy, which is the one relevant repository fact.",
      [ev("interface/globals.php", "per-tenant DB + filesystem tree enables per-region placement", 277, 335)],
      "EVIDENCE-BLOCKED",
      "Provisional: Kingdom-only region. Confirm with legal before Go-Live. Safe default: assume no cross-border "
      "transfer, including for backups, logs and metrics.",
      [A_CP], remaining_gap="Legal interpretation of PDPL cross-border rules for health data.",
      external=True, remaining_input="Legal: approved regions + cross-border transfer position for backups/telemetry."))

add(q("Q46", "Breakglass justification-prompt UI", "I-Security",
      "business_product_decision", True, "answered", "LOCKED",
      "gbl_force_log_breakglass exists at library/globals.inc.php:2851-2856 with default '1' (ON). It LOGS emergency "
      "user activity; no justification-prompt UI exists anywhere.",
      "Premise confirmed exactly: logging without a reason prompt. Auditors typically expect the reason. Build cost "
      "is small and it is additive (no core behaviour change).",
      [ev("library/globals.inc.php", "breakglass logging global, default ON, no prompt", 2851, 2856)],
      "CONFIRMED",
      "Build the justification prompt as a module/overlay rather than a core patch, so it survives rebases.",
      [A_SEC]))

add(q("Q47", "Default value of drive_encryption for Saudi tenants", "I-Security",
      "technical_unknown", True, "contradicted", "LOCKED",
      "PREMISE CORRECTED: drive_encryption ALREADY DEFAULTS TO ON ('1') at library/globals.inc.php:1035-1040. The "
      "prior audit recorded this as unverified. database_encryption likewise defaults to '1' (:1028-1032), consumed "
      "at Crypto.php:65 and CryptoGen.php:82. The deprecated couchdb_encryption global (:1043-1048) is an explicit "
      "no-op.",
      "No patch is required - only a provisioning guarantee that tenants never turn it off. Converts a code change "
      "into a configuration assertion.",
      [ev("library/globals.inc.php", "drive_encryption default '1' (ON)", 1035, 1040),
       ev("library/globals.inc.php", "database_encryption default '1' (ON)", 1028, 1032),
       ev("src/Common/Crypto/CryptoGen.php", "database_encryption consumed here", 82, 82)],
      "CONFIRMED",
      "Force on via provisioning defaults and assert it in a tenant health check. No core patch needed.",
      [A_SEC]))

add(q("Q48", "Audit-log retention (PDPL 5+ years)", "I-Security",
      "regulatory_external_input", True, "answered", "PROVISIONAL",
      "No retention policy, pruner or archival job exists for log / api_log. The seeded background_services rows "
      "(sql/database.sql:209-217) contain no log-maintenance service. Tables grow without bound.",
      "Both a compliance floor and a cost ceiling are unmet. The background_services mechanism is the natural, "
      "already-supported place to add a pruner without a core patch.",
      [ev("(git)", "no pruner/retention job", command="git grep -iln 'log_pruner|prune.*audit|retention' HEAD -- src/ library/ bin/"),
       ev("sql/database.sql", "seeded background services contain no log maintenance", 209, 217)],
      "CONFIRMED",
      "Build a retention job as a background_service (no core patch). Default 7 years pending the legal answer on "
      "the binding floor.",
      [A_SEC], remaining_gap="Binding PDPL retention period is a legal determination.",
      external=True, remaining_input="Legal: minimum retention for audit logs and health records."))

add(q("Q49", "AV scanning for uploads (ClamAV)", "I-Security",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "Zero antivirus integration exists (0 matches for clamav/virus_scan/antivirus). The only content gate is "
      "isWhiteFile() (library/sanitize.inc.php:113), called from just 2 sites "
      "(C_Document.class.php:243, DocumentService.php:130), BOTH gated behind the operator-disableable secure_upload "
      "global (default '1' ON, globals.inc.php:2125-2130). createDocument( has 26 call sites but only 2 consult the "
      "allow-list. No magic-byte MIME validation on the gate, no quarantine, no size limit beyond php.ini.",
      "Extension allow-listing alone is the weakest standard control, it is switchable off, and its coverage across "
      "document-creation paths is unverified. For a healthcare SaaS accepting patient/lab/scan uploads this is a "
      "material gap.",
      [ev("library/sanitize.inc.php", "isWhiteFile allow-list definition", 113, 113),
       ev("controllers/C_Document.class.php", "gate call site, conditional on secure_upload", 243, 243),
       ev("library/globals.inc.php", "secure_upload default ON but operator-disableable", 2125, 2130),
       ev("controllers/C_Document.class.php", "DICOM zip written at :154, gate at :243 - ordering needs review", 154, 154)],
      "CONFIRMED",
      "Required Day-1: AV sidecar plus magic-byte validation. Pin secure_upload=1 in provisioning. Audit all 26 "
      "createDocument callers and resolve the :154/:243 ordering question.",
      [A_SEC], remaining_gap="Per-caller triage of the 26 createDocument sites and the DICOM write-order question."))

add(q("Q50", "Add .github/SECURITY.md and .github/dependabot.yml", "I-Security",
      "action_item", True, "contradicted", "LOCKED",
      "DECISION CONTRADICTED: BOTH FILES ALREADY EXIST. .github/SECURITY.md ships (two disclosure routes: GitHub "
      "private advisory and security@open-emr.org with a PGP key), .github/dependabot.yml ships with a full weekly "
      "composer configuration (grouped symfony/laminas/development updates, PR limit 15), plus a bonus "
      "dependabot-auto-merge.yml workflow.",
      "The task as written is already done. The REAL remaining action is different: SECURITY.md directs reporters to "
      "the OpenEMR security team, which is wrong for a fork holding Saudi tenant PHI and must be rewritten.",
      [ev(".github/SECURITY.md", "disclosure policy present, points at OpenEMR's team"),
       ev(".github/dependabot.yml", "weekly composer updates with grouping already configured"),
       ev(".github/workflows/dependabot-auto-merge.yml", "auto-merge automation present")],
      "CONFIRMED",
      "Close the original item as already satisfied. Open a NEW item: rewrite SECURITY.md for the fork's own "
      "disclosure channel, and decide whether Dependabot stays enabled given the Q2 rebase cadence.",
      [A_SEC]))

# ============================================================ J: testing

add(q("Q51", "Current numeric coverage % baseline", "J-Testing",
      "technical_unknown", True, "answered", "LOCKED",
      "Retrieved LIVE during this audit run from the official Codecov v2 API "
      "(https://api.codecov.io/api/v2/github/openemr/repos/openemr/, updatestamp 2026-08-07T06:02:21Z): "
      "coverage 28.66%, files 4028, lines 428660, hits 122880, misses 305780, branch master. The prior run measured "
      "27.53% on 2026-07-21 (+1.13 pp in ~17 days). This measures UPSTREAM openemr/openemr; the fork is not "
      "onboarded to Codecov, but since fork HEAD is a strict ancestor of upstream master the number is representative.",
      "The real baseline is ~29%, not the ~4% historical figure cited in codecov.yml:24. A 60% total-coverage target "
      "would be a large uplift; an 80% patch-coverage target on new code is achievable and standard.",
      [ev("evidence/raw/q51-codecov-api-response.txt", "live Codecov v2 API response captured this run", repo=U),
       ev("codecov.yml", "the ~4% figure in-repo is stale", 24, 24)],
      "CONFIRMED",
      "Set our-module targets at 80% patch / 60% total. Distinguish clearly: online aggregate 28.66% (upstream), "
      "configured target in codecov.yml, and our new-code target.",
      ["docs/discovery/openemr-decision-evidence/evidence/raw/q51-codecov-api-response.txt", A_CI]))

add(q("Q52", "CI DB strategy: fresh-per-job or persistent?", "J-Testing",
      "technical_unknown", True, "answered", "LOCKED",
      "TWO divergent patterns coexist. integration-tests.yml uses GitHub-native services: containers - genuinely "
      "fresh MySQL/MariaDB per matrix arm. test.yml (used by test-all.yml and test-scheduled.yml) uses compose-stack "
      "DBs: a setup job installs OpenEMR, dumps the DB, and each parallel test job RESTORES that snapshot. So: fresh "
      "container per arm, same snapshot restored across suites within an arm.",
      "Because a snapshot is restored rather than rebuilt, cross-suite interdependency bugs are possible within an "
      "arm. Our own suites should not inherit that pattern.",
      [ev(".github/workflows/integration-tests.yml", "GitHub services: containers, fresh per arm"),
       ev(".github/workflows/test.yml", "dump/restore snapshot shared across suites within an arm")],
      "CONFIRMED",
      "Fresh-per-job for our module suites; leave upstream suites as-is to avoid a fork patch.",
      [A_CI]))

add(q("Q53", "Panther local ChromeDriver fallback on Windows", "J-Testing",
      "deployment_decision", True, "answered", "LOCKED",
      "symfony/panther ^2.0 (composer.json:159). CI uses a SHA-pinned Selenium standalone-chromium at "
      "ci/compose-shared-selenium/docker-compose.yml:2-3, reached at http://selenium:4444/wd/hub "
      "(BaseTrait.php:26,37,72). A local-ChromeDriver fallback exists at BaseTrait.php:79 "
      "(static::createPantherClient() when SELENIUM_USE_GRID != true) but is exercised by NO workflow. "
      "Environment note: this audit machine cannot run Docker at all (GCE VM without nested virtualization), so "
      "container-based E2E is unavailable here regardless.",
      "The fallback is untested by CI, so relying on it is unsupported. On hosts without Docker the E2E suite simply "
      "cannot run - a real constraint for this team's current hardware.",
      [ev("composer.json", "symfony/panther ^2.0", 159, 159),
       ev("tests/Tests/E2e/Base/BaseTrait.php", "untested local ChromeDriver fallback", 79, 79),
       ev("ci/compose-shared-selenium/docker-compose.yml", "SHA-pinned Selenium image", 2, 3)],
      "CONFIRMED",
      "Mandate dev-in-container for E2E and document it. Do not invest in the Windows fallback. Note the hardware "
      "constraint explicitly in onboarding docs.",
      [A_CI]))

# ============================================================ K: misc

add(q("Q54", "Review tools/ directory", "K-Misc",
      "action_item", True, "answered", "LOCKED",
      "19 tracked files, fully enumerated. 18 are the upstream release-automation toolkit under tools/release/ "
      "(bin/ CLI entrypoints: branch-to-version, create-tag, derive-prev-release, dispatch, render-pr-body, "
      "verify-tag; src/ supporting classes; one JSON contract schema). The 19th is tools/ci/analyze-flaky-tests.sh. "
      "No secrets, no dead scripts, no developer footguns.",
      "The [BUILD/TOOLING] classification was accurate. This tree is upstream release machinery the fork does not "
      "need to own, but it is also harmless to carry.",
      [ev("evidence/manifests/q54-tools-inventory.txt", "complete 19-file listing",
          command="git ls-files 'tools/**'")],
      "CONFIRMED",
      "Accept the classification and move on. If the fork publishes its own releases (Q39), tools/release/ becomes "
      "directly reusable.",
      ["docs/discovery/openemr-decision-evidence/evidence/manifests/q54-tools-inventory.txt"]))

add(q("Q55", "wkhtmltopdf-openemr and oe-module-cqm transitive owners", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "Verified against a populated vendor/ tree (unavailable to the prior audit): NEITHER package appears in "
      "vendor/composer/installed.json (247 packages installed). Both are declared only as VCS repositories "
      "(composer.json:161-165 and :166-169), are not in require, and are not in composer.lock. They are DEAD entries.",
      "Removing them is safe and removes two rebase-conflict surfaces. Confirmed empirically rather than inferred.",
      [ev("vendor/composer/installed.json", "neither package installed among 247 packages"),
       ev("composer.json", "declared as repositories only, never required", 161, 169)],
      "CONFIRMED",
      "Safe to remove - but note this would be a fork patch to composer.json. Given the zero-patch record (Q1), "
      "prefer an upstream PR or simply leave them (they cost nothing at runtime).",
      [A_DEP]))

add(q("Q56", "React 15 via napa - who consumes it?", "K-Misc",
      "technical_unknown", True, "partially_answered", "PROVISIONAL",
      "Consumer-graph tracing (evidence/snippets/q56-react15-consumer-graph.md) found the declaration and the napa "
      "download path but no confirmed runtime consumer in first-party code. Classification remains 'uncertain' "
      "rather than 'dead' because removal has not been proven not to change generated assets.",
      "React 15 is 9+ years out of support. If genuinely unused it is pure CVE surface that can be deleted; the "
      "remaining step is a build-output diff, not more grepping.",
      [ev("package.json", "React 15 declared via napa"),
       ev("evidence/snippets/q56-react15-consumer-graph.md", "declared -> downloaded -> no confirmed consumer")],
      "MEDIUM",
      "Run the decisive test: build with and without the napa React entry and diff public/ output. Remove only if "
      "byte-identical. Do NOT declare it dead on grep evidence alone.",
      ["docs/discovery/openemr-decision-evidence/evidence/snippets/q56-react15-consumer-graph.md"],
      remaining_gap="Build-output diff with/without the dependency not executed (npm build must run off the Drive mount)."))

add(q("Q57", "documents.id / insurance_companies.id without auto_increment", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "TWO DIFFERENT ANSWERS. documents.id: deliberate-by-inertia; every writer allocates via "
      "QueryUtils::generateId() against the global sequences table (UPDATE sequences SET id=LAST_INSERT_ID(id+1), "
      "atomic on InnoDB). Conversion is technically safe (zero INSERT ... VALUES (0,...) sites). "
      "insurance_companies.id: deliberate AND LOAD-BEARING - documented at InsuranceCompanyService.php:433-436 as "
      "sharing an id-space with pharmacies via the addresses satellite table, whose foreign_id is a bare int with no "
      "type discriminator. Converting it in isolation would silently corrupt address lookups.",
      "The two tables must not be treated as one anomaly. More importantly it yields a hard rule for new tables: "
      "never share an id-space across tables.",
      [ev("src/Services/InsuranceCompanyService.php", "documented shared id-space with pharmacies", 433, 436),
       ev("src/Common/ORDataObject/ORDataObject.php", "id allocation via generateId on persist", 80, 84),
       ev("src/BC/Database.php", "sequences-table allocation, race caveat in source", 165, 165)],
      "CONFIRMED",
      "Leave BOTH alone; document as known anomalies. For new saas_* tables: bigint AUTO_INCREMENT PK + uuid "
      "binary(16) NOT NULL UNIQUE; never share id-spaces; use (source_table, source_id) tuples for cross-table refs.",
      [A_DB]))

add(q("Q58", "Formal upstream policy on custom_ prefix", "K-Misc",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "NOT RESERVED. Zero baseline tables, zero upgrade-file operations, zero module SQL and zero documentation use "
      "the custom_ prefix. Shipped modules use vendor-slug prefixes (weno_*, comlink_telehealth_*) or bare "
      "descriptive names. Reservation is de facto, never de jure. Separately, 'custom' already means 'third-party "
      "module directory' in interface/modules/custom_modules/ - a semantic collision.",
      "Relying on an unwritten reservation is a silent-collision risk at rebase time. A distinct prefix removes the "
      "risk without needing upstream cooperation.",
      [ev("evidence/snippets/q68-custom-prefix-evidence.md", "5 independent channels all return zero custom_ usage"),
       ev("sql/database.sql", "no baseline table uses the prefix")],
      "CONFIRMED",
      "Use saas_<domain>_ (not custom_). Add a rebase check: git diff <prev>..upstream/master -- sql/ | grep saas_ "
      "must stay empty. Open a docs PR upstream requesting reservation but do not block on it.",
      [A_DB]))

add(q("Q59", "sites/<tenant>/documents/theme/ per-site theme override path", "K-Misc",
      "technical_unknown", True, "contradicted", "LOCKED",
      "PREMISE REFUTED: sites/<tenant>/documents/theme/ has NO runtime behaviour whatsoever - 0 matches across "
      "**/*.php, **/*.twig and **/*.js. The string exists only in prior discovery documents. The actual per-tenant "
      "surface is: logos via LogoService (src/Services/LogoService.php:75-108) reading "
      "sites/<site>/images/logos/<type>/logo.*, legacy per-site images under OE_SITE_WEBROOT/images/, a per-site "
      "custom menu JSON, and sites/<site>/config.php (arbitrary PHP, interface/globals.php:649). NO per-site .css or "
      ".js is included at runtime anywhere.",
      "Q34's per-tenant palette cannot be delivered as a per-tenant CSS file - the mechanism does not exist. It must "
      "be design tokens/CSS variables over a shared immutable bundle.",
      [ev("(git)", "zero runtime references to the theme override path",
          command="git grep -n 'documents/theme' HEAD -- '*.php' '*.twig' '*.js'  -> 0"),
       ev("src/Services/LogoService.php", "actual per-tenant branding surface is logos only", 75, 108),
       ev("interface/globals.php", "sites/<site>/config.php is the only per-site executable seam", 649, 649)],
      "CONFIRMED",
      "Unblock Q34 with: shared immutable CSS bundle + CSS-variable design tokens + per-site logos via LogoService. "
      "Never allow tenant-supplied CSS/JS (it would be an XSS and cross-tenant leakage vector).",
      [A_FE]))

add(q("Q60", "DB DEFAULT CHARSET / COLLATION at CREATE TABLE level", "K-Misc",
      "technical_unknown", True, "partially_answered", "PROVISIONAL",
      "Charset/collation evidence collected into evidence/manifests/q60-charset-collation.txt (per-table DEFAULT "
      "CHARSET occurrence counts in sql/database.sql plus the Installer's utf8mb4/COLLATE handling). The risk is "
      "real: if production MySQL has a different server default, Arabic sort order and case-folding change silently.",
      "For an Arabic deployment collation is not cosmetic - it changes sorting and comparison of patient names. "
      "Pinning it explicitly is cheap insurance.",
      [ev("evidence/manifests/q60-charset-collation.txt", "charset/collation declarations and Installer handling",
          command="git grep -n 'utf8mb4|COLLATE|CHARACTER SET' HEAD -- library/classes/Installer.class.php")],
      "MEDIUM",
      "Pin utf8mb4_unicode_ci explicitly in provisioning and in all saas_* DDL. Verify empirically on a provisioned "
      "tenant with an Arabic sort test before Go-Live.",
      ["docs/discovery/openemr-decision-evidence/evidence/manifests/q60-charset-collation.txt"],
      remaining_gap="No live database was available on this machine to confirm the effective server collation."))

add(q("Q61", "DWV DICOM viewer locale JSON", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "DWV 0.27.1 ships NINE locales - de, en, es, fr, it, jp, ro, ru, zh - and NO Arabic (no ar, ar-SA or ar_SA "
      "anywhere). Verified by fetching and inspecting the npm registry tarball, not by inference. Per-locale key "
      "counts are in evidence/manifests/dwv-locales.csv. An Arabic overlay needs 114 translation leaves plus 3 "
      "overlay leaves.",
      "Bounded and precisely sized: 117 strings. DICOM controls would otherwise stay English inside an Arabic UI.",
      [ev("evidence/manifests/dwv-locales.csv", "9 locales enumerated with key counts; no Arabic"),
       ev("evidence/raw/dwv-0.27.1.tgz", "upstream package inspected directly")],
      "CONFIRMED",
      "Author ar/translation.json (114 leaves) + ar/overlays.json (3) as a fork-local overlay under "
      "public/assets/dwv/locales/ar/, and contribute upstream to ivmartel/dwv.",
      [A_L10N, "docs/discovery/openemr-decision-evidence/evidence/manifests/dwv-locales.csv"]))

add(q("Q62", "Full FHIR SearchParameter enumeration", "K-Misc",
      "action_item", True, "partially_answered", "PROVISIONAL",
      "Structural census complete: 103 FHIR service files (evidence/manifests/q62-fhir-service-files.txt) containing "
      "491 FhirSearchParameterDefinition registrations, alongside 90 REST controllers. The per-resource catalogue "
      "itself was not generated.",
      "The catalogue is now a mechanical extraction from 491 known registration sites rather than an open-ended "
      "research task - roughly a day, as originally estimated.",
      [ev("evidence/manifests/q62-fhir-service-files.txt", "103 FHIR service files"),
       ev("evidence/raw/count-search_params.txt", "491 SearchParameter registration sites")],
      "HIGH",
      "Build the static catalogue when NPHIES conformance testing starts (Q28). Generate it from the 491 sites so it "
      "stays diffable across rebases.",
      ["docs/discovery/openemr-decision-evidence/evidence/manifests/q62-fhir-service-files.txt"],
      remaining_gap="Per-resource catalogue not generated; only the extraction surface was measured."))

add(q("Q63", "Numeric API versioning strategy", "K-Misc",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "There is NO numeric version segment: zero /v1/ matches across REST controllers and the route map. Versioning "
      "today is implicit in the pinned US Core route-map filename.",
      "Introducing /v1/ retroactively means router changes plus a client migration window. Since NPHIES clients are "
      "not yet built, the cheapest moment to introduce versioning is at the first NPHIES endpoint - for new routes "
      "only.",
      [ev("evidence/raw/count-api_version_seg.txt", "zero /v1/ segments in controllers or route map")],
      "CONFIRMED",
      "Freeze the existing surface unversioned. Introduce /v1/ ONLY on new NPHIES/SaaS routes, so no existing client "
      "migration is ever required.",
      [A_FHIR]))

add(q("Q64", "Application-tier rate limiting", "K-Misc",
      "locked_architectural_decision", True, "answered", "LOCKED",
      "NO inbound rate limiting exists. All 17 'rate limit' matches are unrelated: PHPStan baselines, the fax "
      "module's OUTBOUND throttle to respect a vendor's limits (RCFaxClient.php:45,106,948,1063), GitHub API "
      "rate-limit messages in a release CLI, and a telemetry caching comment.",
      "Per-OAuth-client fairness cannot be enforced at a proxy that cannot see the client_id. That is the specific "
      "gap proxy-only leaves.",
      [ev("evidence/raw/count-rate_limit.txt", "17 matches, none an inbound limiter"),
       ev("interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php",
          "outbound vendor throttle, not inbound limiting", 45, 45)],
      "CONFIRMED",
      "Proxy-only Day-1 for volumetric abuse. Add an app-tier per-client limiter when partner integrations go live, "
      "since only the app knows the OAuth client identity.",
      [A_FHIR]))

add(q("Q65", "BillingProcessor hookable extension points", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "Well-factored INTERNAL OOP hierarchy (ProcessingTaskInterface -> GeneratorInterface; AbstractProcessingTask "
      "-> AbstractGenerator) but ZERO external-module extension surface. Task selection is a hard-coded if/elseif "
      "ladder keyed on $_POST['bn_*'] at BillingProcessor.php:161-192 - no factory, registry, service-locator or "
      "event dispatch anywhere in src/Billing/. GeneratorExternal is a legacy include of custom/BillingExport.php "
      "(file not shipped; only a rename-me stub), NOT a supported module mechanism. No transaction boundary and no "
      "idempotency key: GeneratorX12.php:151,168 auto-commit per row.",
      "A NPHIES module CANNOT register a billing generator without patching core. It also answers the outbox "
      "question: there is no transaction boundary to hang one on.",
      [ev("src/Billing/BillingProcessor/BillingProcessor.php", "hard-coded task selection ladder", 161, 192),
       ev("src/Billing/BillingProcessor/Tasks/GeneratorX12.php", "row-by-row auto-commit, no transaction", 151, 168),
       ev("src/Billing/BillingProcessor/GeneratorInterface.php", "clean internal contract exists", 18, 18)],
      "CONFIRMED",
      "Upstream PR converting the if/elseif ladder into a POST-key->factory registry plus one pre-dispatch event - "
      "confined to BillingProcessor.php, so it is a clean, reviewable upstream change. Until it lands, use polling "
      "(Q27 Option C).",
      [A_FHIR, "docs/discovery/openemr-decision-evidence/evidence/snippets/q65-billing-call-graph.md"]))

add(q("Q66", "claimrev-connect FHIR emission", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "Analysed from the actually-installed v2.1.6 source (134 files). Reuse matrix recorded in "
      "evidence/snippets/q66-claimrev-reuse-matrix.csv, classifying each component as direct_reuse / "
      "pattern_reuse_only / not_reusable, and distinguishing genuine FHIR R4 resources from generic 'claim' "
      "vocabulary, X12 claims, UI labels and plain REST transport.",
      "Determines whether NPHIES can crib implementation or only architecture. The transferable asset is the "
      "polling/reconciliation PATTERN (which Q27 Option C adopts), not X12-specific code.",
      [ev("evidence/snippets/q66-claimrev-reuse-matrix.csv", "per-component reuse classification"),
       ev("evidence/snippets/oe-module-claimrev-connect-source-inventory.md", "full installed source inventory")],
      "HIGH",
      "Reuse the background-polling and state-reconciliation pattern; do not attempt to reuse X12 transport code for "
      "NPHIES FHIR.",
      ["docs/discovery/openemr-decision-evidence/evidence/snippets/q66-claimrev-reuse-matrix.csv"]))

add(q("Q67", "Full audit of SQL interpolation sinks and echo-vs-text ratio", "K-Misc",
      "action_item", True, "partially_answered", "PROVISIONAL",
      "Census complete, triage NOT complete. Data-access surface: 6,785 sites (see Q11). Output escaping is "
      "genuinely strong: 13,590 uses of the project's escaping helpers (echo xlt( 9,476 + echo attr( 2,060 + "
      "echo text( 2,054) vs 369 raw htmlspecialchars, 32 Twig |raw, and ZERO Smarty nofilter. Residual XSS risk "
      "concentrates in 390 innerHTML and 18 document.write JavaScript sinks, which PHP-side helpers do not protect.",
      "The escaping discipline is much better than a truncated sample suggested, and the risk is now localised to a "
      "408-site JavaScript worklist rather than the whole tree.",
      [ev("evidence/raw/remaining-counts.tsv", "full escaping and data-access census with saved match lists"),
       ev("evidence/raw/count-js_innerhtml.txt", "390 innerHTML sinks - the actual worklist"),
       ev("evidence/raw/count-smarty_nofilter.txt", "zero Smarty nofilter")],
      "MEDIUM",
      "Run the dedicated pre-Go-Live security sprint, but retarget it: start with the 408 JS sinks and "
      "identifier-interpolation SQL, not a blanket review. Match lists in evidence/raw/ are the worklist.",
      [A_SEC], remaining_gap="No per-site triage of 6,785 SQL sites or 408 JS sinks; this audit did not prove absence "
                             "of SQL injection or XSS."))

add(q("Q68", "log.checksum chain verification cadence", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "It is NOT A CHAIN. LogTablesSink.php:63 computes hash('sha3-512', implode('', array_values($logData))) - the "
      "current row's own fields only. No previous-row hash (not a chain), no secret key (plain hash(), not "
      "hash_hmac). Zero scheduled verifier exists anywhere. EventAuditLogger.php:670-671 notes log.checksum is "
      "unused since 6.0, with the operative value in log_comment_encrypt.",
      "Anyone with UPDATE on the log tables can edit a row and recompute a valid checksum with a one-line script. "
      "Describing this as tamper-proof would be false. Verification cadence is not the gap - the CONSTRUCTION is.",
      [ev("src/Common/Logging/Audit/LogTablesSink.php", "unkeyed per-row hash, no chaining", 63, 91),
       ev("src/Common/Logging/EventAuditLogger.php", "log.checksum unused since 6.0", 670, 671),
       ev("(git)", "no verifier exists", command="git grep -in 'verifyChecksum|checksum.*verif' HEAD -- src/ library/ bin/  -> 0")],
      "CONFIRMED",
      "Do not schedule a verifier for a non-chain. To get real tamper-evidence all three are required: hash_hmac "
      "with a key the DB user cannot read, inclusion of the previous row's hash, and a scheduled verifier. Build as "
      "a saas_ overlay, then add the nightly job.",
      [A_SEC]))

add(q("Q69", "Complete encryptStandard column inventory", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "THE HEADLINE COUNT IS MISLEADING. Of 36 encryptStandard( sites, most are tests, PHPStan baselines and the "
      "crypto library's own interface. The real application write paths are only: SMART launch tokens "
      "(SMARTLaunchToken.php:132), key wrapping (CryptoGen.php:482), fax media tokens and vendor credentials "
      "(oe-module-faxsms), and the phone gateway password (reminders.php:399). "
      "NO CORE CLINICAL PHI COLUMN IS ENCRYPTED VIA THIS API - patient_data, form_encounter, billing and documents "
      "metadata are plaintext columns. What protects PHI at rest is drive_encryption (default ON, for files) and "
      "database_encryption (default ON, for the keys/audit-comment paths).",
      "Any claim that 'OpenEMR encrypts PHI columns' is FALSE. Encryption-at-rest for clinical PHI depends on "
      "document encryption plus deployment-supplied volume/DB encryption. This materially changes the PDPL posture "
      "narrative and must not be misstated to auditors.",
      [ev("evidence/raw/count-encryptstandard.txt", "36 sites; application paths are credentials/tokens only"),
       ev("src/FHIR/SMART/SMARTLaunchToken.php", "ephemeral launch token encryption", 132, 132),
       ev("library/globals.inc.php", "drive_encryption + database_encryption both default ON", 1028, 1040)],
      "CONFIRMED",
      "State the posture accurately: file-level plus storage-level encryption, NOT column-level. Pin both globals on. "
      "If column-level PHI encryption is required by legal, treat it as a new build, not a configuration change.",
      [A_SEC], remaining_gap="A bespoke encryption path under a different function name would not be caught by a "
                             "two-name grep."))

add(q("Q70", "Vendored-vs-composer-installed oe-module-* runtime model", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "Resolved empirically on a populated vendor/ tree. The seven tracked modules are NOT in composer.lock, so "
      "composer install never touches them - git is authoritative. Only claimrev-connect is composer-managed, and "
      ".gitignore:15 excludes its directory precisely because composer owns it. There is no overlay of tracked files "
      "today: authority is cleanly partitioned, and no module exists twice.",
      "Confirms tracked copies are authoritative and production will not diverge silently from git. BUT see Q37: the "
      "installer resolves paths by package-name last segment only, so a future package could collide onto a tracked "
      "module directory.",
      [ev("vendor/composer/installed.json", "only claimrev-connect is an openemr-module package"),
       ev(".gitignore", "claimrev directory excluded because composer owns it", 15, 15),
       ev("08-dependency-runtime-inventory.csv", "per-module authority table")],
      "CONFIRMED",
      "Treat tracked copies as authoritative. Add a CI assertion that no composer package's install path collides "
      "with a tracked module directory (mitigates the Q37 overlay risk).",
      [A_DEP]))

add(q("Q71", "Which module owns DICOM viewer include chain?", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "DICOM viewing is CORE document-viewer functionality, not a dedicated module. The PHP->JS include chain from "
      "the document viewer through dicom_launcher.js to DWV is traced in 13-localization-arabic-evidence.md, along "
      "with how the DWV runtime locale is selected.",
      "Because it is core rather than a module, the Arabic DWV overlay (Q61) must be delivered as an asset overlay, "
      "not packaged inside a module.",
      [ev("library/js/dwv/", "DWV assets live in core, not in a module"),
       ev("13-localization-arabic-evidence.md", "full PHP->JS include chain and locale selection")],
      "CONFIRMED",
      "Bundle the Q61 Arabic locale work as a core asset overlay under public/assets/dwv/locales/ar/.",
      [A_L10N]))

add(q("Q72", "File-level vs line-level responsiveness coverage", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "The '611 files' figure is CORRECTED and replaced by a reproducible file-level inventory: 5,460 first-party "
      "UI files scanned (fingerprint sha256(q72-file-list.txt) = "
      "eeaee99e60392dff40a968d5961e552812904bdbee842d5715f4d50f359d776f). Classification reconciles to the row "
      "count: unknown/non-UI backend 3,111; shared_template 1,098; custom_module_screen 416; "
      "legacy_iframe_included_file 288; legacy_standalone_page 287; remainder per the summary. Exclusions are "
      "documented in evidence/raw/q72-scanner-exclusions.txt.",
      "Sizes the RTL/mobile QA matrix against measured categories instead of a single unreconciled number, and "
      "directly supports the Q33 decision to avoid shell replacement.",
      [ev("18-q72-ui-responsiveness-inventory.csv", "one row per file, 17 columns, 5,460 rows"),
       ev("19-q72-ui-responsiveness-summary.md", "reconciled totals with formulas"),
       ev("evidence/raw/q72-file-list.txt", "exact scanned set, sha256 fingerprinted")],
      "CONFIRMED",
      "Use the CSV as the RTL/mobile QA worklist. Prioritise the legacy iframe population (575 files) and the 416 "
      "module screens; treat the 3,111 non-UI rows as out of scope.",
      [A_Q72C, A_Q72S]))

add(q("Q73", "Scripted count of QueryUtils:: call sites", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "QueryUtils:: = 1,653 call sites (previously uncounted). Full data-access surface: sqlStatement( 2,025, "
      "sqlQuery( 1,454, sqlFetchArray( 1,354, sqlInsert( 251, Doctrine DBAL 48 - total 6,785, plus 202 OE_SITE_DIR "
      "file-path sites. Every count has a saved match list and a SHA-256.",
      "The shared-DB (Model B) estimate was based on 1,875 sites. The true figure is 3.6x that. This is the decisive "
      "quantitative input to Q11.",
      [ev("evidence/raw/count-queryutils.txt", "1,653 QueryUtils:: sites with full match list"),
       ev("evidence/raw/remaining-counts.tsv", "all sink counts in one table"),
       ev("evidence/manifests/remaining-counts-sha256.txt", "checksums for reproducibility")],
      "CONFIRMED",
      "Count is delivered regardless of the Model A/B outcome, and it settles Q11 conclusively in favour of Model A.",
      ["docs/discovery/openemr-decision-evidence/evidence/raw/remaining-counts.tsv"]))

add(q("Q74", "tests/Tests/Integration/ tree scope", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "VESTIGIAL - the directory DOES NOT EXIST. git ls-files 'tests/Tests/Integration/**' returns 0 rows and the "
      "path is absent on disk. phpunit.integration.xml:34,37 references two nonexistent directories. Zero invocations "
      "anywhere: 0 hits in .github/workflows/, composer.json scripts, package.json, devtools, bin/ and tools/. "
      "The 11 *IntegrationTest.php files that DO exist live under tests/Tests/{Common,RestControllers,Services}/ - "
      "a naming convention, not occupants of this config, and they run under phpunit.xml's existing suites.",
      "Nothing to extend or maintain. The config file is dead weight that misleads readers into thinking an "
      "integration tier exists.",
      [ev("(git)", "directory absent from the index", command="git ls-files 'tests/Tests/Integration/**'  -> 0 rows"),
       ev("phpunit.integration.xml", "references two nonexistent directories", 34, 37),
       ev("(git)", "zero invocations across workflows, composer, npm, devtools")],
      "CONFIRMED",
      "Do not invest. Recommend deleting phpunit.integration.xml upstream; nothing consumes it. Build our own "
      "integration tier fresh if needed.",
      [A_CI]))

add(q("Q75", "phpunit-isolated.xml actually run in CI?", "K-Misc",
      "technical_unknown", True, "answered", "LOCKED",
      "YES - a first-class gate. .github/workflows/isolated-tests.yml:50 runs "
      "`vendor/bin/phpunit -c phpunit-isolated.xml --coverage-clover=clover.xml --log-junit=junit.xml` on every push "
      "and PR to master and rel-*, across PHP 8.2/8.3/8.4/8.5/8.6 (the BROADEST matrix of any suite - only isolated "
      "tests cover 8.6), on ubuntu-24.04 with xdebug coverage, uploading Clover + JUnit to Codecov under "
      "isolated-php<ver> flags.",
      "Twig compile/render tests and other isolated tests ARE enforced on PRs. It is also the only suite runnable on "
      "this audit machine (no Docker), making it the practical local gate for fork development.",
      [ev(".github/workflows/isolated-tests.yml", "exact command, matrix and coverage upload", 30, 52),
       ev("phpunit-isolated.xml", "no bootstrap, no DB, no browser required", 32, 32)],
      "CONFIRMED",
      "Treat the isolated suite as the primary local gate for fork development and add our new tests there wherever "
      "a DB is not required.",
      [A_CI]))


# ---------------------------------------------------------------- emit

def main() -> int:
    root = Path(subprocess.run(["git", "rev-parse", "--show-toplevel"],
                               capture_output=True, text=True, check=True).stdout.strip())
    out = root / "docs" / "discovery" / "openemr-decision-evidence"
    out.mkdir(parents=True, exist_ok=True)

    if len(QUESTIONS) != 75:
        print(f"ERROR: expected 75 questions, built {len(QUESTIONS)}", file=sys.stderr)
        return 1
    ids = [x["question_id"] for x in QUESTIONS]
    expected = [f"Q{i}" for i in range(1, 76)]
    if ids != expected:
        missing = set(expected) - set(ids)
        dupes = [i for i in ids if ids.count(i) > 1]
        print(f"ERROR: id mismatch. missing={sorted(missing)} dupes={sorted(set(dupes))}", file=sys.stderr)
        return 1

    # ---- 04-question-evidence.json
    payload = {
        "schema": "openemr-decision-evidence/question-evidence/v1",
        "fork_commit": FORK_SHA,
        "upstream_stable_tag": UPSTREAM_STABLE_TAG,
        "upstream_stable_commit": UPSTREAM_STABLE_SHA,
        "upstream_master_commit_at_audit": UPSTREAM_MASTER_SHA,
        "question_count": len(QUESTIONS),
        "questions": [{k: v for k, v in x.items() if not k.startswith("_")} for x in QUESTIONS],
    }
    (out / "04-question-evidence.json").write_text(
        json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")

    # ---- 03-question-status-matrix.csv
    cols = ["question_id", "category", "question_summary", "repository_verifiable", "status",
            "confidence", "primary_finding", "decision_impact", "recommended_decision",
            "evidence_files", "remaining_input"]
    with (out / "03-question-status-matrix.csv").open("w", newline="", encoding="utf-8") as fh:
        w = csv.DictWriter(fh, fieldnames=cols)
        w.writeheader()
        for x in QUESTIONS:
            files = sorted({e["file"] for e in x["evidence"] if e["file"] != "(git)"})
            files += [a.split("/")[-1] for a in x["generated_artifacts"]]
            w.writerow({
                "question_id": x["question_id"],
                "category": x["category"],
                "question_summary": x["question_summary"],
                "repository_verifiable": "TRUE" if x["repository_verifiable"] else "FALSE",
                "status": x["status"],
                "confidence": x["confidence"],
                "primary_finding": x["finding"],
                "decision_impact": x["decision_impact"],
                "recommended_decision": x["recommended_decision"],
                "evidence_files": "; ".join(dict.fromkeys(files)),
                "remaining_input": x["_remaining_input"],
            })

    # ---- console tallies (used by the executive summary)
    from collections import Counter
    st = Counter(x["status"] for x in QUESTIONS)
    cf = Counter(x["confidence"] for x in QUESTIONS)
    cl = Counter(x["classification"] for x in QUESTIONS)
    print(f"questions: {len(QUESTIONS)}")
    print("status:      ", dict(st))
    print("confidence:  ", dict(cf))
    print("classification:", dict(cl))
    print(f"external input required: {sum(1 for x in QUESTIONS if x['requires_external_non_repository_input'])}")
    print(f"with remaining_gap:      {sum(1 for x in QUESTIONS if x['remaining_gap'])}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
