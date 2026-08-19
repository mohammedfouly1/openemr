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
