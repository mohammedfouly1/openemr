# EV-092 — RECONCILIATION OF THE `Locked Desicions/` CORPUS AGAINST THE GTM

**Requirement:** RDY-0092 · **Gates:** G0 · **Deps:** RDY-0001 · **Owner:** Founder / Product Owner
**Acceptance:** *"Reconciled against the GTM; any decision not reflected there is escalated, not
silently adopted."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

---

## 1. What was found

`Locked Desicions/` (repo root, three files, present per §7.21) is **not** a branding or MVP-scoped
corpus. It is a **separate, dated (2026-08-09) locked-decisions register for a full SaaS multi-tenant
platform** — Keycloak-centralised auth, DB-per-tenant with subdomain routing, ZATCA Phase-2-ready
architecture, NPHIES/FHIR claims submission, Hijri calendar, MFA enforcement, and more — covering at
least **77 decisions (`Q1`…`Q77`+)** across categories A–through (Foundational/Upstream, Identity &
Authorization, Multi-Tenancy, Saudi Localization, NPHIES/Claims, and further sections not enumerated
here). The companion file (`OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria`, 848 lines)
tracks implementation against it under `MVP-*`/`BLK-*` IDs cited from the decisions.

**This is a materially larger and differently-scoped programme than the readiness document's locked
MVP** (Saudi self-pay outpatient, single-clinic-per-database, no NPHIES, no ZATCA, no enforced MFA,
no multi-tenant SaaS — GTM §31 "Decisions NOT Taken" explicitly rejects several of these). **The two
corpora were evidently produced for different scopes of ambition, at overlapping dates, by different
processes**, and nothing in either document states which one governs where they diverge.

## 2. Scope of this reconciliation — read before relying on it

**Given the corpus's size (≥77 decisions, 2,687 lines across both files), this is a sampled
reconciliation, not an exhaustive one.** Categories A (Foundational), B (Identity & Auth), C
(Multi-Tenancy) and the opening of D (Localization/ZATCA) were read in full (`Q1`–`Q22`). Categories
E onward (NPHIES/Claims, and whatever follows `Q27`) were **not** read. This is stated so the
"reconciled" claim is honest about its own boundary, per the closure contract's "nothing closes on an
assertion" rule.

## 3. Findings that must be escalated, not silently adopted

### 3.1 ⚠ Q5 — direct conflict with the current MFA position

| | |
|---|---|
| **Locked Decisions Q5** | *"Enforce MFA centrally in Keycloak for all clinical, administrative, privileged, support, and platform roles. Do not add a downstream OpenEMR core `force_mfa` global."* — **Status: LOCKED** |
| **Readiness document's current position (RDY-0057, RDY-0099, O-12)** | MFA **"cannot be mandated"** (L-03) — enrolment is voluntary, and the honest disclosure to a security-review persona is exactly that it cannot be enforced. RDY-0099 (P1, deferred) proposes adding a `force_mfa` **OpenEMR core global** — the opposite mechanism Q5 forbids |

**This is a genuine architectural fork, not a wording difference.** Q5 assumes a centralised Keycloak
IdP fronting the deployment (consistent with the corpus's whole multi-tenant SaaS model); the readiness
document's locked GTM has no IdP layer in scope anywhere and treats MFA non-enforceability as a
**disclosed limitation to sell around**, not a defect to fix via Keycloak. **Neither document
references the other's MFA position.** Escalated here, not resolved — this is the Founder/Product
Owner's decision on which locked corpus governs the current MVP phase.

#### ADDENDUM 2026-08-19 — RULING GIVEN

**Given directly by the Owner (Mohammed Elfouly) in conversation with the orchestrating session,
2026-08-19** (not relayed through any agent).

**Ruling:** The readiness plan's disclosure-based position **governs for this MVP phase**.
RDY-0099's `force_mfa` proposal is **retired as superseded by Q5's centralized-Keycloak direction**,
not merely left deferred.
**Rationale:** NONE STATED BEYOND THE RULING ITSELF.
**Decided by:** Owner (Mohammed Elfouly)
**Date:** 2026-08-19
**Conditions:** NONE STATED.
**What this resolves:** the specific §3.1 conflict above — RDY-0057's disclosure-based MFA handling
stands for the current MVP; RDY-0099 (the `force_mfa` core-global proposal) does not proceed as an
active deferred item, because it is the exact mechanism Q5 forbids, not because it is merely
lower-priority. If Q5's centralized-Keycloak direction is ever built, MFA enforcement would be
addressed there, not via a core OpenEMR global.
**What this does NOT resolve:** the broader question of §4's table — whether `Locked Desicions/` is a
future-phase roadmap, a superseded exploration, or a live parallel programme — remains open, as do
categories `Q23`+ (unreviewed, §2). This ruling is scoped to the Q5/RDY-0099 conflict only.
**Evidence path:** this file. Register rows for RDY-0092, RDY-0099 and RDY-0057 updated separately to
carry this ruling.

### 3.2 Q21 — ZATCA: a possible primary-source lead for the still-open RDY-0078

Locked Decisions Q21 records: *"External verification (ZATCA, 2026-07-24): Wave 25 covers
VAT-taxable revenue above SAR 187,500 in 2022–2025, with integration by 2027-02-01 for notified
taxpayers; Phase 2 requires Fatoora integration…"* — dated **before** RDY-0078's own audit, which
states plainly that *"no primary regulator document has been read"* for ZATCA (EXT-01).

**Not confirmed here as primary-source** — the note does not state whether "External verification"
means a ZATCA.gov.sa document was read directly or a secondary/vendor summary, and confirming that
distinction is exactly RDY-0078's job, not this one's. **Flagged for whoever holds RDY-0078**: this
citation may already answer part of what that item is blocked on, or may turn out to be exactly the
kind of secondary-source citation RDY-0078 is designed to catch. Either way, it should not go
unchecked now that it is known to exist.

Separately: Q21's decision — *design the architecture so ZATCA Phase 2 does not require a later
redesign* — is an **architecture-readiness** decision, not a scope-inclusion one, and does not on its
face contradict the GTM's "we do not do invoicing" positioning (O-4). It is nonetheless **a decision
about a topic the current locked MVP has deliberately deferred (RDY-0097, P1)**, made without
reference to that deferral. Recorded as a gap in cross-referencing, not a contradiction.

### 3.3 Q11, Q12 — multi-tenancy: partial agreement, partial silence

Q11 (DB-per-tenant) **agrees** with the GTM's own locked position (*"each clinic gets its own
database… a deliberate isolation choice, not a platform"*, O-6/O-8). **No conflict.**

Q12 (subdomain-per-tenant routing, authoritative hostname→tenant mapping, `?site=` tenant-switching
prohibited) is **not addressed anywhere in the readiness document or the GTM** — the current
single-instance-per-clinic deployment model (RDY-0047's runbook) has no subdomain-routing concept at
all. **Not a conflict — a decision the GTM scope has simply never had reason to reach.** Recorded as
present-but-unreferenced rather than adopted or rejected.

## 4. What this reconciliation explicitly does NOT resolve

| Item | Why |
|---|---|
| **Whether the `Locked Desicions/` corpus is a future-phase roadmap, a superseded exploration, or a live parallel programme** | Nowhere in either document is that relationship stated. This is the single most consequential open question and it is a **Founder/Product Owner decision**, not something derivable from the text |
| **Categories E onward (`Q27`+, NPHIES/Claims, and whatever follows)** | Not read — §2 |
| **The 848-line implementation backlog's own conformance** | Out of scope for a decision-level reconciliation |

## 5. Acceptance

| Criterion | Result |
|---|---|
| Reconciled against the GTM | **PARTIAL — MET for the sampled categories (A–D opening, `Q1`–`Q22`)**, explicitly not for the remainder (§2) |
| Any decision not reflected in the GTM is escalated, not silently adopted | **MET** — §3.1 (Q5, direct conflict), §3.2 (Q21, unreferenced but not contradictory + a lead for RDY-0078), §3.3 (Q12, unreferenced, no conflict) |

### Status: **RDY-0092 — NOT CLOSED.** A real, load-bearing conflict was found (§3.1) and escalated
rather than resolved — this item cannot close by writing a reconciliation that resolves it unilaterally.
**The remaining ~55 decisions (`Q23`+) are unreviewed** and are recorded as open scope, not assumed
clean.

**`Blocks`:** G0. No gate count moved (§0.0 Rule 3).

**Required next step, named rather than left implicit:** the Founder / Product Owner states which
corpus governs for the current MVP phase, and — if `Locked Desicions/` is a live parallel programme —
whether Q5's MFA position should override RDY-0057/0099's current disclosure-based handling before
any security-review conversation cites it.

**UPDATE 2026-08-19 — the §3.1 sub-finding is now resolved, RDY-0092 as a whole is not.** The Owner's
ruling (§3.1 addendum above) answers the Q5/RDY-0099 conflict specifically: the readiness plan's
disclosure-based position governs for this MVP phase, and RDY-0099 is retired as superseded rather than
left deferred. **This is one resolved sub-finding, not a resolution of the whole reconciliation.** The
broader "which corpus governs, roadmap vs. live programme" question (§4's first row) and categories
`Q23`+ (§2, never read) remain exactly as open as before this ruling. **RDY-0092 stays NOT CLOSED** —
its own acceptance criterion needs the full corpus reconciled, and this ruling reconciles one category-B
finding, not the remaining ~55 decisions.

---

## 6. UPDATE 2026-08-19 (second pass) — categories `Q23`–`Q77` now read in full (triage, not closure)

**Dispatched as the "RDY-0092 agent"** under the Orchestrator's three-subagent batch
(`docs/evidence/AGENT-CLAIMS.md`, "three subagents dispatched" entry), narrative log range
`PB-386`–`PB-387`. Scope given: read categories `Q23` onward in `Locked Desicions/OpenEMR-SaaS-Locked-
Decisions-UPDATED-2026-08-09.md` (categories D opening through L: `Q23`–`Q77`, 55 decisions — every
decision the §2 boundary above left unread) and cross-check each against the GTM
(`docs/Product-Positioning-and-GTM-Locked-Strategy.md`), the current branding/implementation record
(`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md`, `docs/branding/`, `docs/rebranding.md`),
and the already-reconciled `Q1`–`Q22` findings above. **Explicit instruction: triage, not closure — do
not resolve any conflict found, escalate it.**

**Method.** Read the full text (locked decision, evidence/rationale, primary-repository-evidence,
cross-references) of every decision `Q23` through `Q77` directly from
`Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md:578-1826`. Cross-checked each
against a full-text grep of the GTM document for the decision's subject matter (NPHIES, ZATCA,
multi-tenant, Kubernetes, data residency, KMS, breakglass, rate limiting, patient portal, Control
Plane, themes), then against the readiness document's own RDY register rows and `docs/branding/`
where the grep surfaced a match. This is a **decision-level** reconciliation only, per the same scope
line drawn in §4 — the 848-line implementation backlog's own conformance was not audited.

**Coverage: all 55 decisions in `Q23`–`Q77` read.** Combined with the original `Q1`–`Q22` pass, this
means every locked decision in the register (`Q1`–`Q77`) has now been read at least once across the
two reconciliation passes. **This does not mean RDY-0092 can close** — see §6.4.

### 6.1 New finding requiring Owner attention — `Q27`–`Q31`/`Q65` (NPHIES architecture) vs. the locked NPHIES deferral

| | |
|---|---|
| **Locked Decisions `Q27`** (category E, NPHIES/Claims) | *"For the **NPHIES MVP**, use a background polling/worker architecture decoupled from the synchronous BillingProcessor flow."* — **Status: LOCKED** |
| **Locked Decisions `Q30`** | *"**Ship NPHIES initially** with controlled polling, while pursuing a narrow upstream-compatible BillingProcessor extension... Do not implement a large downstream billing-core fork."* — **Status: LOCKED** |
| **Readiness document's current position** | RDY-0100: *"NPHIES pathway... **Core patching programme**... P1... **DEFERRED**"* (`Marketing-MVP-and-Launch-Readiness-Requirements.md:1127`). GTM O-5: *"Does it support NPHIES? **No.** It is on the roadmap and it is a substantial engineering programme, not a switch — we will tell you when it exists, not before"* (`Product-Positioning-and-GTM-Locked-Strategy.md:945`). GTM §5.4 explicitly excludes *"insurance-heavy clinics until NPHIES exists"* from the current ICP, and the prohibited-claims list bans any NPHIES-capability claim outright (`:588`, `:338`). |

**Not a clean Q5-style contradiction — flagged as a cross-referencing gap that reads ambiguously
enough to need the Owner's eyes.** Read charitably, `Q27`/`Q30`'s "NPHIES MVP" and "ship NPHIES
initially" describe the *minimum viable version of the NPHIES feature itself*, for whenever that
future engineering programme starts — not a claim that NPHIES ships inside the current locked MVP.
Under that reading, `Q27`–`Q31`/`Q65` are pre-work architecture analysis for the **same** deferred
item RDY-0100 already tracks (`Q65`'s own evidence describes exactly the `BillingProcessor` hard-coded
dispatch-ladder gap RDY-0100's audited-state column cites almost verbatim), and this is the same class
of finding as §3.2's `Q21`/ZATCA gap: present, relevant, not cross-referenced, not actually
contradictory.

**But it is not obviously that reading either.** Five consecutive decisions (`Q27`–`Q31`) use present-
tense delivery language — "ship," "for the … MVP," "keep `claimrev-connect`… it is not the NPHIES
implementation" (`Q31`, implying an implementation is imminently being built) — with no sentence
anywhere in the section stating that this whole category is itself deferred to a later phase the way
the GTM states it. Nothing in `Q27`–`Q31` cites RDY-0100, GAP-0046, GAP-P1, or the GTM's NPHIES
deferral, and nothing in RDY-0100 or the GTM cites `Q27`–`Q31`/`Q65`. **Neither document is aware the
other exists on this specific point**, which is the same underlying defect §3.1's Q5 finding escalated
— only the severity differs (there, two documents actively disagreed on a mechanism; here, one
document's phrasing could be misread as scope creep into the current MVP if anyone builds from `Q27`–
`Q31` directly without checking RDY-0100 first).

**Escalated, not resolved.** Recommended for the Owner: either (a) add an explicit note to `Q27`–`Q31`
stating they describe the future NPHIES programme's own internal architecture, not current-MVP scope,
and cross-link RDY-0100/GAP-P1; or (b) if `Q27`–`Q31` were written with near-term delivery genuinely
intended, that is a direct conflict with the locked GTM's NPHIES-deferral position and needs the same
kind of ruling §3.1 got for Q5.

### 6.2 Confirmed consistent, not a conflict — noted for completeness

- **`Q45`** (Saudi PDPL data-residency default, KSA-resident production data) **is consistent with**
  RDY-0064's closed outcome: *"CLOSED 2026-08-19 — Owner-relayed, region decided (Dammam /
  `me-central2`)"* (`Marketing-MVP-and-Launch-Readiness-Requirements.md:9323`). Both land on
  Kingdom-resident hosting; `Q45`'s per-tenant framing simply doesn't apply to the current
  single-deployment-per-clinic model, and nothing in that difference contradicts either document. This
  is the same relationship the original pass gave `Q21` (§3.2) — a real, useful cross-reference that
  should be linked, not a conflict.
- **`Q53`** (native Windows Panther/ChromeDriver E2E fallback, no Docker required for developer
  E2E) **matches the actual environment this session runs in** — `CLAUDE.local.md` documents this exact
  machine as Docker-incapable (no nested virtualization) and running a native Apache/PHP/MariaDB stack.
  `Q53` was written as a locked SaaS decision independently of this machine's constraint but happens to
  describe precisely the supported path already in use. No conflict; a confirming data point.
- **`Q44`** (per-tenant KMS-backed key custody, "Day-1") is **directionally consistent** with RDY-0081's
  adopted target (CMEK via Cloud KMS, per `Marketing-MVP-and-Launch-Readiness-Requirements.md:10476`),
  once "per-tenant" is read as "per-deployment" for the current single-tenant-per-database model. Not a
  conflict — RDY-0081 is an unimplemented gap against its own acceptance criteria, not a contradiction
  of `Q44`.
- **`Q46`** (breakglass workflow requiring "strong re-authentication/MFA") sits on the same fork §3.1
  already escalated and the Owner already ruled on: MFA enforcement of any kind, including inside a
  breakglass flow, currently requires the centralized-Keycloak direction Q5 assumes and the readiness
  document does not have. This is not a **new** conflict — it's the same Q5 fork reappearing in a
  narrower feature — so it is noted here rather than escalated a second time. Whoever eventually
  implements `Q46`'s breakglass workflow (RDY-0019 tracks the plain assignment/alert-email gap only,
  with no MFA requirement recorded) should re-read the Q5 ruling first.
- **`Q76`/`Q77`** (Control-Plane branding-token materialisation boundary; two-variant Saudi theme
  surface) are **already implemented and cited directly** in `docs/branding/architecture.md:156`
  ("Matches `Q77` and is guarded by `BrandingGovernanceGuardTest`") and
  `docs/branding/multi-tenant-white-label-readiness.md:34-36` (citing `Q11`, already reconciled in the
  original pass). No conflict — this is the one part of `Q23`+ that was already reconciled by name
  before this pass, confirmed still consistent.

### 6.3 No conflict, not applicable — GTM/branding scope never reaches these

The remaining decisions in `Q23`–`Q77` are engineering/technical-hygiene decisions (data-model choices,
CI strategy, dependency hygiene, module packaging conventions, security-engineering scoping) that the
GTM, the readiness document, and `docs/branding/` have no occasion to reference either way. Recorded
briefly rather than analysed individually, per the triage instruction's carve-out for items where "a
conflict is not possible":

`Q23` (per-user timezone/language), `Q24` (bootstrap-rtl vendoring), `Q25` (Arabic PDF fonts), `Q26`
(hardcoded USD in FHIR Coverage), `Q32` (patient portal — consistent with CLM-0028 "included but off,"
no tension), `Q33` (Bootstrap 4/Twig main UI — matches the current stack), `Q34` (two theme variants —
superseded/restated by `Q77`, already reconciled §6.2), `Q35` (CKEditor Arabic), `Q36`–`Q38` (module
byte-identity, installer internals, Twig namespacing), `Q39` (OCI image publishing — already
self-amended in-corpus by `ADR-DEV-001`), `Q40` (Inferno/ONC certification explicitly *not* required —
consistent with the GTM's "certified" prohibition), `Q41` (Helm charts in a separate infra repo — no
infra repo exists yet; present-but-unreferenced like `Q12`, not a conflict), `Q42` (X-Forwarded-For
handling), `Q43` (rotate exposed GitHub PATs), `Q47` (drive_encryption/database_encryption already
default on — a factual correction, not a policy), `Q48` (audit-log retention matrix — an unimplemented
gap against the current "retention is indefinite by default" disclosure at
`Marketing-MVP-and-Launch-Readiness-Requirements.md:9226`, not a decision-level conflict), `Q49` (AV
scanning), `Q50` (SECURITY.md/dependabot already exist), `Q51`–`Q52` (coverage baseline, CI DB
strategy), `Q54`–`Q75` (tools/ directory scope, dead Composer entries, React 15 freeze, ID-generation
policy, `custom_` prefix reservation, per-site theme-override path — already refuted and consistent
with `Q59`'s own text, charset/collation, DWV Arabic locale, FHIR SearchParameter cataloguing, API
versioning, rate limiting, BillingProcessor hooks, ClaimRev reuse boundary, SQL/XSS triage priority,
audit-checksum chaining, encryption column inventory, module authority partition, DICOM ownership,
responsiveness QA worklist, QueryUtils census, vestigial integration-test tree, isolated-test CI gate).
None of these touch positioning, pricing, claims discipline, or the locked MVP boundary; none contradict
anything in the readiness document, the GTM, or `docs/branding/`.

### 6.4 Why RDY-0092 still does not close

**Every decision in the register (`Q1`–`Q77`) has now been read across the two reconciliation passes.**
That is not the same as "reconciliation complete": two things the acceptance criterion needs are still
open —

1. **§6.1's `Q27`–`Q31`/`Q65` finding is escalated, not resolved** — exactly the posture that kept
   RDY-0092 open after the original Q5 finding, and for the same reason: this item cannot close by
   writing a reconciliation that resolves its own escalations unilaterally.
2. **§4's first row — "is `Locked Desicions/` a future-phase roadmap, a superseded exploration, or a
   live parallel programme" — is still unanswered.** Nothing in this second pass touched it; it is a
   Founder/Product Owner call, not a text-reconciliation finding. Until it is answered, every
   "consistent, not a conflict" note in §6.2 is provisional on that relationship being "future roadmap"
   rather than something requiring immediate reconciliation with the live MVP.

**Status: RDY-0092 — NOT CLOSED.** Full-corpus decision text is now read; one new finding is escalated
(§6.1); the governing-relationship question (§4) remains open. Register row updated separately to
reflect this pass's coverage.

**What a future pass would still need to do, named rather than left implicit:** get the Owner's
reading on §6.1 (scope-creep risk or a documentation-linking fix); get the Owner's ruling on §4's
governing-relationship question; audit the 848-line implementation backlog's own conformance (out of
scope for both reconciliation passes so far); and only then can RDY-0092's acceptance criterion — "any
decision not reflected [in the GTM] is escalated, not silently adopted" — be called fully met rather
than met-so-far.
