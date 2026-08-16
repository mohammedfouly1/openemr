# EV-068 — PILOT AGREEMENT REQUIREMENTS PACK

**Requirement:** RDY-0068 · **Gates:** G3 · **Deps:** RDY-0065, 0066, 0073 · **Owner:** Sales / Pilot Owner + Legal
**Acceptance:** *"Every one of the fourteen elements above is addressed; the success gate is binary
and measurable; the exit clause is written before the first pilot is offered, not after; RDY-0055's
PHI determination is reflected."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

**This document does not draft the legal agreement — it specifies what the agreement must reflect**,
exactly as RDY-0068's card requires. It builds directly on `EV-065-066-069` (qualification/scope/cost)
and `EV-073` (termination/handover), both already written — this is not a competing artefact, it is
the piece those two were waiting on to close their own cross-references.

---

## 1. The fourteen elements

| # | Element | Requirement |
|---|---|---|
| **1** | **Pilot scope** | One clinic, the `EV-066` scope template governs inclusions/exclusions verbatim — this document does not restate them, it incorporates them by reference |
| **2** | **Success gate** | See §2 — binary, measurable |
| **3** | **Exit criteria** | See §3 |
| **4** | **Data-migration boundary** | Quoted **after inspection**, never fixed-price sight-unseen (PRC-002, `EV-066` §B.3) — the pilot agreement states this as a contract term, not a sales note |
| **5** | **Support channel and hours** | Per `RDY-0096`'s definition once it exists; until then, the agreement states explicitly that hours and channel are **not yet published** rather than inventing a figure |
| **6** | **Training** | At implementation, included; further training at a published day rate (per GTM §15.3 model) |
| **7** | **Escalation** | Per `RDY-0096`; same "not yet published" discipline applies if that item is still open when the first pilot is offered |
| **8** | **Hosting boundary** | Depends on `RDY-0064` (hosting decision — Dammam, `me-central2`, per the register). The agreement states the hosting model and explicitly the **on-premise variant's** term: the customer takes backup responsibility, **stated in writing** (PRC-002) |
| **9** | **Integration boundary** | Third-party integration contracts are the **customer's** to hold, never ours (`EV-066` §B.2, O-10) |
| **10** | **Claims/invoicing exclusion** | Verbatim from `EV-066` §B.2 — no tax field anywhere in the billing chain; no NPHIES connection |
| **11** | **Customer-data exit** | `EV-073`'s termination and handover procedure, incorporated by reference — **not restated**, so the two documents cannot drift apart |
| **12** | **Hypercare period** | See §4 |
| **13** | **Measurement** | `EV-065-066-069` Part C (cost instrumentation, C-1…C-10) runs from pilot day 1 — the agreement states that operational data is captured for this purpose and names what (implementation/support/config hours) |
| **14** | **PHI/audit-log reflection** | See §5 |

## 2. The success gate — binary, measurable, stated now rather than left for negotiation

Drawn directly from GTM §25 Phase 4's own gate (*"at least 2 pilots reach go-live and renew"*) applied
to a single engagement:

> **The pilot succeeds if, and only if, all of the following are true at the end of the pilot term:**
> 1. The clinic's demo-role-equivalent accounts have been in **live clinical use** (not training use)
>    for at least the final 30 days of the term.
> 2. **Zero P0/P1 defects** (as defined in the support agreement) remain open and unresolved at term end.
> 3. A **backup has run successfully and been restored once** against the customer's own live data,
>    witnessed, per the `RDY-0082` procedure.
> 4. The customer has **not invoked the exit clause** (§3) before the term ends.
>
> **Any single failure is a "did not reach go-live" outcome.** There is no partial-credit reading.

This is deliberately more concrete than GTM §25's phase-level gate because a single-pilot agreement
needs an unambiguous yes/no the customer can also read and agree to — not an aggregate statistic.

## 3. Exit criteria — written now, not after a dispute exists

Either party may exit before term end, without cause, on **30 days' written notice**. On exit (for
any reason, at any point):

- `EV-073`'s termination and handover procedure runs in full, including its T-6 confirmation step.
- **No early-exit penalty attaches to the customer** for exiting during the design-partner term —
  the whole premise of a paid pilot at a design-partner discount (GTM §15.3) is that the customer is
  taking a risk on an unproven engagement, and penalising an early, honest exit would contradict the
  disclosure positioning this entire strategy is built on.

## 4. Hypercare period

**30 days immediately following go-live** (the point where Success Gate condition 1's 30-day live-use
clock also starts — they are the same window, not two separate periods). During hypercare: elevated
support responsiveness (specific target deferred to `RDY-0096`), a named point of contact, and daily
review of any error-rate or backup-failure signal defined under `RDY-0084`'s monitoring requirements
once a hosted instance exists to monitor.

## 5. RDY-0055's PHI determination, reflected

`RDY-0055` establishes that bound SQL parameters are logged to `log.comments` as base64 and that, on
a system holding real patient data, **this places PHI in the audit table** in a form the current
architecture does not encrypt. This is not latent once a pilot begins — a pilot is, by definition,
the first point real patient data enters the system. **The pilot agreement must therefore state,
before signature:**

- The audit-log-PHI condition exists and is disclosed, not concealed (consistent with the whole
  positioning's disclosure discipline).
- Backup handling (`RDY-0081`, `EV-074`) — including deletion timing after contract end — applies to
  the audit log's PHI content as much as to the clinical tables, because every backup is a complete
  copy of both.
- RDY-0068's own dependency on RDY-0055 (§8.11's card lists it explicitly) is satisfied by this
  paragraph existing in the agreement, not merely by RDY-0055 existing as a separate document.

## 6. Acceptance

| Criterion | Result |
|---|---|
| Every one of the 14 elements is addressed | **MET** — §1, with cross-references rather than duplication where another artefact already governs |
| The success gate is binary and measurable | **MET** — §2, four conditions, no partial-credit reading |
| The exit clause is written before the first pilot is offered | **MET** — §3, written now; **no pilot has been offered yet, so this ordering is satisfied by construction** |
| RDY-0055's PHI determination is reflected | **MET** — §5 |

### Status: **RDY-0068 — requirements COMPLETE. NOT CLOSED** — pending Legal/Compliance review (per
the item's own dual ownership, Sales/Pilot Owner **+ Legal**) before this pack is used to draft an
actual agreement. **Not closed by this agent.**

**`Blocks`:** G3. No gate count moved (§0.0 Rule 3).

**Consequence for `RDY-0073` and `RDY-0069`:** `EV-073`'s acceptance table records *"Referenced by the
pilot agreement (RDY-0068): NOT MET — RDY-0068 does not exist yet."* **RDY-0068 exists as of this
document, and §1 element 11 references `EV-073` by name.** `EV-073`'s own row should be re-read
against this when its file is next touched — this document does not edit `EV-073` itself, consistent
with the closure contract's instruction to build on existing evidence rather than rewrite it.
`RDY-0069` remains blocked on an actual pilot existing (a signed agreement, not merely a requirements
pack) — this document does not change that.
