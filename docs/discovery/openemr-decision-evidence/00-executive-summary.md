# 00 — Executive Summary

**OpenEMR SaaS fork — repository evidence collection audit, Q1–Q75**

_Audit run 2 (2026-08-07), completing the package begun in run 1 (2026-07-21) against the same fork commit.
Mode: read-only. No application file was modified._

---

## 1. Repository baseline

| Field | Value |
|---|---|
| Fork remote | `https://github.com/mohammedfouly1/openemr` (no credentials in URL) |
| Branch | `master` |
| **Fork commit** | `631f2b38cf633769c305233f88cdf9c73ca80657` (2026-07-04) |
| **Upstream stable tag** | `v8_2_0` (2026-07-08) |
| **Upstream stable commit** | `6125a2fd8089c8bcc3848071c1293c60e27a7585` |
| Upstream master at audit | `dad282635495c98ea9ab9c3577277933725d13eb` |
| Merge base with stable | `b91c12aee3f6022954dd071c53917b2047eabf95` |
| Merge base with master | `631f2b38cf633769c305233f88cdf9c73ca80657` (**= fork HEAD**) |
| Working tree | Dirty (pre-existing; 1 deleted, 1 modified, 6 untracked) — untouched by this audit |
| Clone depth | Shallow (does not affect the ancestry finding — see below) |

### 1.1 The single most important finding

> **The fork has zero commits of its own.**
> `git rev-list --count upstream/master..HEAD` → **0**, and `git merge-base HEAD upstream/master` returns
> the fork SHA itself. The fork is an unmodified mirror of upstream master at 2026-07-04 — 373 commits
> behind current upstream master, 17 commits diverged from the v8_2_0 release cut.

Two consequences that reshape the whole decision set:

1. **`git diff HEAD upstream/master` shows what upstream added, not what we changed.** The usual
   downstream-fork mental model is inverted. Every one of the 431 differing paths is upstream moving
   forward. `likely_core_modification` is `FALSE` for all 431 rows in `07-core-modification-inventory.csv`.
2. **A "no core edits" policy costs nothing to adopt today and gets more expensive every day it is
   deferred.** The diff is provably empty right now. This is the cheapest moment in the project's life to
   lock that rule.

The tag was selected by date and corroborated against upstream release branches (`rel-820`,
`release-prep/rel-820`), not by lexical sort; the `v8_1_*-test.<sha>` tags are prereleases and were excluded.

---

## 2. Question results

**Total questions: 75**

| Outcome | Count | IDs |
|---|---:|---|
| Fully answered | **56** | — |
| Partially answered | **12** | Q3, Q13, Q14, Q15, Q21, Q28, Q32, Q39, Q56, Q60, Q62, Q67 |
| **Contradicted decisions** | **4** | **Q10, Q47, Q50, Q59** |
| Evidence-blocked | **1** | Q45 (legal question; no repository evidence can exist) |
| External decisions required | **3** (status) / **14** (need some external input) | Q2, Q22, Q45 |
| Action items | **6** | Q28, Q43, Q50, Q54, Q62, Q67 |

Confidence distribution: **CONFIRMED 59**, HIGH 10, MEDIUM 4, LOW 1, EVIDENCE-BLOCKED 1.

**Recommended status: 54 questions can be LOCKED now**, 21 remain PROVISIONAL.

### 2.1 The four contradicted decisions — read these first

These are cases where the repository **refutes** the premise the decision was built on.

| Q | Premise | What the repository actually shows |
|---|---|---|
| **Q50** | "Add `SECURITY.md` and `dependabot.yml`" | **Both already exist**, plus a `dependabot-auto-merge.yml` workflow. The real task is different: `SECURITY.md` points reporters at *OpenEMR's* security team, which is wrong for a fork holding Saudi tenant PHI. |
| **Q47** | "Upstream `drive_encryption` default is unverified" | It **already defaults to ON** (`library/globals.inc.php:1038`), as does `database_encryption`. No patch needed — only a provisioning guarantee. |
| **Q59** | "`sites/<tenant>/documents/theme/` is the per-site theme override path" | That path has **no runtime behaviour at all** — 0 matches in PHP, Twig and JS. It exists only in our own prior notes. Per-tenant branding is logos only. |
| **Q10** | "CapabilityStatement advertises standalone-encounter launch but it is unimplemented" | It does **not** advertise it — `context-standalone-encounter` is explicitly commented out. The real defect is narrower: `context-ehr-encounter` **is** advertised while the `launch/encounter` scope is absent from every grantable scope list. |

---

## 3. Highest-risk findings (15, ordered by severity)

| # | Finding | Q | Evidence | Severity |
|---|---|---|---|---|
| 1 | **Audit "tamper-proof" checksum is neither chained nor keyed.** `hash('sha3-512', implode('', array_values($logData)))` over the row's own fields. No previous-row hash, no HMAC key, and **zero scheduled verifier anywhere**. Anyone with UPDATE on `log` can edit a row and recompute a valid checksum. | Q68 | `src/Common/Logging/Audit/LogTablesSink.php:63-91` | **Critical** |
| 2 | **No column-level PHI encryption.** Of 36 `encryptStandard(` sites, the real application paths protect only credentials and tokens. `patient_data`, `form_encounter`, `billing`, `documents` metadata are plaintext columns. Claiming "OpenEMR encrypts PHI columns" to an auditor would be false. | Q69 | `evidence/raw/count-encryptstandard.txt` | **Critical** |
| 3 | **Unvalidated `X-Forwarded-For` reaches the audit log.** The entire client-supplied chain is concatenated unparsed; no trusted-proxy allowlist exists anywhere (0 matches). Lands in the `log` table and auth-failure comments. Audit-log forging. | Q42, Q6 | `library/sanitize.inc.php:29-46` → `LogTablesSink.php:70` | **High** |
| 4 | **No antivirus, and the only upload gate is switchable off.** Zero ClamAV references. `isWhiteFile()` is called from just 2 of 26 `createDocument(` paths, both behind the operator-disableable `secure_upload` global. No magic-byte MIME validation, no quarantine. | Q49 | `library/sanitize.inc.php:113`; `globals.inc.php:2125-2130` | **High** |
| 5 | **12 hardcoded GitHub tokens across 4 compose files**, in 3 obfuscation layers (raw, base64, decimal char codes) — so `ghp_`-prefix scanners miss two thirds of them. Upstream's tokens, inherited; containment is ours. | Q43 | `docker/development-easy*/docker-compose.yml`, `docker/flex/openemr.sh:766-790` | **High** |
| 6 | **Composer module installer resolves paths by package-name last segment only**, ignoring the vendor. A package named `<anyone>/oe-module-weno` installs over the tracked upstream module directory. | Q37 | `vendor/.../CustomModuleInstaller.php:13-20` | **High** |
| 7 | **No audit-log retention or pruner.** `log`/`api_log` grow unbounded; no maintenance service is seeded. Both a compliance floor and a cost ceiling are unmet. | Q48 | `git grep` → 0; `sql/database.sql:209-217` | **High** |
| 8 | **Billing has no extension surface, no transaction boundary, no idempotency.** Task selection is a hard-coded `if/elseif` on `$_POST['bn_*']`; claim status is written row-by-row with auto-commit. NPHIES cannot plug in without patching core, and there is nowhere to hang an outbox. | Q65, Q30 | `BillingProcessor.php:161-192`; `GeneratorX12.php:151,168` | **High** |
| 9 | **The tenant boundary is enforced by one regex.** Tenancy is "whichever `sqlconf.php` got loaded"; MySQL enforces nothing. `$_GET['site']` takes precedence over the session. A misrouted site id silently reads another tenant's database. | Q11, Q12 | `interface/globals.php:277-335`, `:304` | **High** |
| 10 | **Session cookie name is a global constant** (`CORE_SESSION_ID = "OpenEMR"`). Combined with subdomain routing (Q12) this is a cross-tenant session-confusion risk unless cookie scope is set per subdomain. | Q17, Q16 | `src/Common/Session/SessionUtil.php:81` | **Medium-High** |
| 11 | **Arabic readiness is roughly half-built and every Saudi-specific feature is greenfield.** 47.53% string coverage (6,290/13,234); zero Hijri; zero ZATCA; no Arabic-capable PDF font tracked; DWV ships 9 locales, none Arabic. | Q18–Q25, Q61 | `13-localization-arabic-evidence.md` | **Medium-High** |
| 12 | **`bootstrap-rtl` is a pinned single-commit archive of an unmaintained third-party fork.** If that URL disappears the RTL build breaks with no local copy — an availability risk on the critical path for Arabic. | Q24 | `package.json:113` | **Medium** |
| 13 | **React 15 (2016) cannot yet be declared dead.** Declaration and download path found; no confirmed consumer, but removal has not been proven not to change build output. 9+ years unsupported. | Q56 | `evidence/snippets/q56-react15-consumer-graph.md` | **Medium** |
| 14 | **SMART advertises a capability whose scope cannot be granted.** `context-ehr-encounter` is advertised while `launch/encounter` is missing from both scope lists; shipped docs show examples that would fail. | Q10 | `Capability.php:48`; `ServerScopeListEntity.php:53` | **Medium** |
| 15 | **No application-tier rate limiting at all**, and a 28.66% coverage baseline. A proxy cannot enforce per-OAuth-client fairness because it cannot see the `client_id`. | Q64, Q51 | `evidence/raw/count-rate_limit.txt`; Codecov API | **Medium** |

---

## 4. Questions that can now be locked (54)

**Foundational.** Q1 upstream remote added, drift measured, **zero fork commits** — peg to `v8_2_0`, adopt no-core-edit now.

**Identity.** Q4 Keycloak needs real RP code (no generic OIDC client exists) · Q5 no MFA-force global exists → enforce in IdP · Q6 no `REMOTE_USER` anywhere → don't build header trust · Q7 keep Google Sign-In as the RP template · Q8 DCR is live → no admin UI needed Day-1 · Q9 freeze `$v_acl` at 13 · Q10 fix the advertised-vs-grantable scope gap first.

**Tenancy.** Q11 **Model A locked** — shared-DB means 6,785 call sites · Q12 subdomain routing via a small `globals.php` patch · Q17 accept one-tenant-per-browser, scope cookies at the proxy.

**Saudi/Arabic.** Q18 extend the SQL catalogue (frontend shares it) · Q20 SAR only · Q23 site-wide TZ, per-user language via existing `user_settings` · Q24 vendor the RTL zip today · Q25 bundle Amiri/Noto Naskh · Q26 upstream-PR the USD hardcode.

**NPHIES.** Q27 Option C polling for MVP · Q29 new `saas_nphies_partners` · Q30 ship polling + upstream PR in parallel · Q31 keep claimrev in composer, disable per tenant.

**Frontend.** Q33 stay BS4/Twig, embed SPAs tab-by-tab · Q34 two RTL themes + CSS-variable tokens · Q35 wire the already-installed CKEditor Arabic package.

**Modules.** Q36 all seven modules pristine · Q37 installer algorithm documented + collision risk identified · Q38 enforce `@slug/` Twig namespaces · Q70 git is authoritative.

**Security.** Q42 strip and re-add XFF at the edge · Q43 treat tokens as burned · Q46 build the breakglass prompt as an overlay · Q47 already ON — assert in provisioning · Q49 AV + magic bytes required Day-1 · Q50 already done; rewrite `SECURITY.md` instead · Q68 rebuild as a real HMAC chain · Q69 state the posture accurately.

**Testing.** Q51 baseline 28.66% · Q52 fresh-per-job for our suites · Q53 mandate dev-in-container · Q74 integration tree is vestigial (directory absent) · Q75 isolated suite **is** a first-class CI gate.

**Misc.** Q54 `tools/` is release automation, accept · Q55 both packages dead (verified in `vendor/`) · Q57 leave both id columns alone; never share id-spaces · Q58 use `saas_` not `custom_` · Q59 branding = logos + tokens, never tenant CSS · Q61 author 117 Arabic DWV strings · Q63 version only new routes · Q64 proxy-only Day-1 · Q65 upstream-PR a task registry · Q66 reuse the polling pattern, not X12 code · Q71 DICOM is core · Q72 5,460-file inventory delivered · Q73 1,653 `QueryUtils::` sites.

---

## 5. Questions still open

Each entry gives exactly: missing evidence · why the repository was insufficient · who must answer · the exact question to ask.

| Q | Missing evidence | Why repo inspection was insufficient | Who answers | Exact question |
|---|---|---|---|---|
| **Q2** | Rebase cadence | Repo gives release frequency (3 in 5 months) and 373-commit drift, not team capacity | Eng leadership | "How many engineer-days per month can we commit to upstream merges, and what patch lag is acceptable?" |
| **Q3** | Orchestration platform | Zero k8s/Helm/Nomad artifacts exist — no in-repo signal either way | Ops/Platform | "Is production Kubernetes, and do we have managed-k8s budget in an in-Kingdom region?" |
| **Q13** | Tenant ceiling | Repo gives the binding constraints (connection math, per-site cron), not the target | Product | "What is the 24-month tenant forecast?" |
| **Q14** | Infra config location | Nothing per-tenant exists in-repo to migrate | Ops | "One repo or two, and who owns release coupling?" |
| **Q15** | Cross-tenant analytics timing | Under Model A this is an ETL build; repo cannot say if it is an MVP promise | Product | "Are HQ/multi-clinic dashboards an MVP commitment?" |
| **Q19** | Hijri scope | Zero Hijri code exists; repo cannot say which fields need it | Product + Saudi clinician | "Which fields must show Hijri: DOB, appointments, billing, labs?" |
| **Q21** | ZATCA phase | Zero ZATCA code; the binding deadline is regulatory | Legal/Finance | "Which ZATCA wave are we in and what is the compliance deadline?" |
| **Q22** | MSA vs dialect | Linguistic preference is not a repository fact | Product + Saudi reviewer | "MSA everywhere, or Saudi dialect for patient-facing screens?" |
| **Q32** | Portal strategy | Repo proves the portal is isolated and swappable; it cannot set the ambition | Product | "Is an app-like patient portal an MVP requirement?" |
| **Q39** | Own Docker images | Repo shows divergence is inevitable; registry choice is ops | Ops | "Which private registry, and do we require image signing?" |
| **Q40** | Inferno scope | US ONC conformance has no Saudi force; customer requirements are external | Product | "Does any customer or partner require ONC certification?" |
| **Q41** | Chart location | No charts exist | Ops | Same as Q14 |
| **Q45** | PDPL residency | **Purely legal** — no repository evidence can exist | Legal | "Which regions are approved, and what is our position on cross-border transfer for backups, logs and telemetry?" |
| **Q48** | Retention period | Repo proves no pruner exists; the floor is legal | Legal | "What is the binding minimum retention for audit logs and health records?" |

**Repository work still outstanding** (not blocked on anyone else):

| Q | Remaining gap | Action |
|---|---|---|
| Q56 | Build-output diff with/without React 15 not run | Build off-mount at `C:\openemr-stack\build` with and without the napa entry; diff `public/`. Remove only if byte-identical. |
| Q67 | 6,785 SQL sites and 408 JS sinks not individually triaged | Dedicated security sprint using the saved match lists as the worklist. |
| Q49 | 26 `createDocument` callers not triaged; DICOM write-order (`:154` vs `:243`) unresolved | Read `C_Document.class.php` end-to-end. |
| Q60 | Effective server collation unverified (no DB available on this host) | Run an Arabic sort test on a provisioned tenant. |
| Q62 | Per-resource SearchParameter catalogue not generated | Mechanical extraction from the 491 known registration sites. |
| Q28 | Data-class existence for EOB/Eligibility*/PaymentNotice unverified | Check `src/FHIR/R4/` before sizing. |

---

## 6. Recommended next actions

### BLOCKER — before any tenant data exists
1. **Fix the audit-integrity story (Q68).** A per-row unkeyed hash must not be described as tamper-evident. Rebuild as HMAC + previous-row chain + nightly verifier, as a `saas_` overlay.
2. **Neutralise `X-Forwarded-For` (Q42).** Strip inbound XFF at the edge; re-add exactly one hop from our own LB.
3. **Correct the encryption narrative (Q69).** Document that PHI protection is file-level + storage-level, never column-level, before any compliance claim is made.
4. **Contain the tokens (Q43).** Guarantee they never reach a published image or public fork; add a scanner that catches base64 and decimal-encoded variants.

### BEFORE MVP
5. **Lock the 54 decisions** in section 4 — especially Q1 (no core edits, while the diff is provably empty) and Q11 (Model A).
6. **Upload gate (Q49):** AV sidecar + magic-byte validation; pin `secure_upload=1`; triage all 26 `createDocument` callers.
7. **Tenant isolation hardening (Q11/Q12/Q17):** subdomain routing patch, per-subdomain cookie scope, and a guard making `$_GET['site']` non-authoritative.
8. **Vendor `bootstrap-rtl` (Q24)** — removes a live build-availability risk in one commit.
9. **Answer the 5 legal/product blockers:** Q45, Q21, Q48, Q19, Q13.
10. **Fix the SMART scope inconsistency (Q10)** — a two-line change that removes a conformance defect.

### BEFORE PRODUCTION
11. Audit-log retention job as a `background_service` (Q48).
12. Security sprint on the 408 JS sinks and identifier-interpolation SQL (Q67).
13. Breakglass justification prompt (Q46); rewrite `SECURITY.md` for our own channel (Q50).
14. CI assertion that no composer package collides with a tracked module directory (Q37/Q70).
15. Confirm collation with a live Arabic sort test (Q60).

### PHASE 2
16. NPHIES: upstream-PR the billing task registry + event (Q30/Q65); build the FHIR write surface (Q28).
17. Arabic completion: 6,944 missing constants, Amiri/Noto fonts, CKEditor `ar`, DWV Arabic overlay (Q18/Q25/Q35/Q61).
18. Hijri and ZATCA once Q19/Q21 are answered.
19. Per-tenant KMS custody wrapper (Q44) — isolation already exists; this adds rotation and escrow.

### OPTIONAL
20. Resolve React 15 (Q56); remove dead composer repository entries (Q55); delete `phpunit.integration.xml` upstream (Q74); build the SearchParameter catalogue (Q62).

---

## 7. How to use this package

- **`03-question-status-matrix.csv`** — one row per question; the fastest way to review all 75.
- **`04-question-evidence.json`** — full evidence objects with file/line/command per question.
- Both are generated from a single source (`tools/discovery/openemr-decision-evidence/build-question-matrix.py`) and cannot drift apart.
- Every count has a saved match list under `evidence/raw/` with a SHA-256 in `evidence/manifests/`.
- Reproduction steps: `24-reproduction-guide.md`.

**Caveat carried throughout:** this audit is static. No DB-backed, API, E2E or Selenium suite could run — this
machine is a GCE VM without nested virtualization, so the Docker engine cannot start. Findings are
repository-truth; runtime behaviour was not exercised. Where that matters, it is stated in the question's
`remaining_gap`.
