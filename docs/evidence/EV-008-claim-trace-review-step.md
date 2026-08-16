# EV-008 — CLAIM-TRACE REVIEW STEP

**Requirement:** RDY-0008 · **Gates:** G5 · **Deps:** RDY-0003 · **Owner:** Product Marketing
**Acceptance:** *"A sample artefact shows a trace for every customer-facing sentence."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

---

## 1. Dependency check

RDY-0008 depends on RDY-0003 (claim reviewer + review step adopted). §7.2 records RDY-0003 as
**NOT READY** — a reviewer is named (Mohammed Elfouly, per AGENT-CLAIMS.md's 2026-08-14 note) but the
document's own register row has not been updated to reflect that naming, and no review has been
logged against `EV-003-claim-review-procedure.md`. **RDY-0008 cannot close while RDY-0003 is open** —
this artefact defines the mechanism RDY-0008 asks for; it does not close RDY-0008's dependency.

## 2. The mechanism

A **trace** is a footnote-style citation attached to every sentence in a customer-facing artefact
(website copy, sales collateral, the pilot agreement, the scope template) that asserts a fact about
the product. It answers one question: *if a prospect asked "how do you know that", what is the
citation?*

| Rule | Detail |
|---|---|
| **Format** | `<sentence> [CLM-nnnn or CAP-nnnn]` inline, or a numbered footnote list at the artefact's end |
| **What must be traced** | Any sentence describing a capability, a limitation, a number, a comparison, or a claim of what the product does or does not do |
| **What is exempt** | Pure narrative connective tissue ("Here's how it works:"), and legal/contractual boilerplate not asserting a product fact |
| **Source IDs** | Drawn from the GTM's own `CLM-*` (claim) and `CAP-*` (capability) registers, or a `GAP-*`/`L-*` ID from the capability audit when the sentence describes an absence |
| **Failure mode this catches** | A sentence that sounds right but traces to nothing — the exact failure GTM R-02 names as catastrophic ("one prohibited claim destroys D-1 permanently") |
| **Who runs it** | The RDY-0003 claim reviewer, before an artefact is published — not the artefact's author |

## 3. Worked sample — every customer-facing sentence in `EV-065`'s call sheet disqualifiers traced

Chosen because it is already the artefact under the heaviest external use (the qualification call is
the funnel's first customer-facing moment) and its disqualifier table (`EV-065-066-069-commercial-artefacts.md`
§A.1) already carries source IDs inline. Reproduced here as the worked example the acceptance
criterion asks for, with the trace made explicit rather than merely adjacent:

| Customer-facing sentence spoken on the call | Trace |
|---|---|
| *"We don't submit insurance claims and we don't connect to NPHIES."* | `[GAP-0046, L-26]` |
| *"We don't issue your tax invoice. There is no tax field anywhere in our billing chain."* | `[GAP-0052, GAP-0053, L-11]` |
| *"This is an outpatient clinical record. Inpatient isn't switched off — it isn't there."* | `[GAP-0001…0014]` |
| *"Two-factor is supported and enrolment is voluntary. An administrator cannot mandate it."* | `[L-03, CAP-0218]` |
| *"Each clinic is its own database, provisioned by hand."* | `[GAP-0043, L-07]` |
| *"Fifty-five reports with CSV export, and no BI layer."* | `[GAP-0040, GAP-0041]` |
| *"About half the interface, and roughly one in six dropdown values."* | `[CLM-0030]`, and the 16.1% picklist figure traces further to `EV-086 §1` |
| *"There isn't a patient mobile app, and there isn't one planned in this phase."* | `[GAP-0023, GAP-0024]` |

**Result: 8 of 8 customer-facing sentences in the sample trace to a source ID.** Zero untraced
sentences found in this artefact. This is one artefact, not a claim about every artefact in the
document — see §4.

## 4. Scope of what this proves, stated plainly

This demonstrates the **mechanism works and produces a clean result on one artefact that was already
disciplined.** It is not a claim-review pass over every customer-facing document in the repository —
that is RDY-0003's ongoing job, once a reviewer is active. Untraced sentences may exist elsewhere
(§17.7 marketing copy, the website draft, etc.); none of that surface has been checked here.

## 5. Acceptance

| Criterion | Result |
|---|---|
| A sample artefact shows a trace for every customer-facing sentence | **MET** — §3, 8/8 traced |
| Mechanism is repeatable and documented | **MET** — §2 |
| RDY-0003's reviewer runs this on every future publish | **NOT MET — RDY-0003 is not closed**; the naming exists (per AGENT-CLAIMS.md) but no review step has been logged |

### Status: **RDY-0008 — mechanism defined and demonstrated; NOT CLOSED.** Closure requires RDY-0003
to close first (its own dependency), and a broader sweep beyond the one sample artefact.

**`Blocks`:** G5. No gate count moved (§0.0 Rule 3).
