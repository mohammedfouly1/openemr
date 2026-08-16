# EV-068 / EV-073 — LEGAL/COMPLIANCE REVIEWER PACKS

**Requirements:** RDY-0068 (pilot agreement requirements), RDY-0073 (termination and handover)
**Gates:** G3 (both), G6 (0073 also) · **Owner (both):** Legal / Compliance (jointly with
Sales/Pilot Owner for 0068, DevOps for 0073)
**Issued:** 2026-08-16 · **AGENT-COMMERCIAL**, Phase 2B — **CONTINUATION**

`EV-068-pilot-requirements.md` (AGENT-DOC, 2026-08-16) and `EV-073-termination-and-handover.md`
(Agent B, 2026-08-14) are **requirements specifications** — what the eventual agreement and
procedure must contain. Neither is written as something a reviewer can act on directly. **This
document is not a third requirements analysis and does not restate their content.** It is the
review packaging both items' acceptance criteria require ("legal/compliance review recorded" for
0068; the deletion-timing question in 0073 explicitly says "that is Legal's" decision): for each
item, what the reviewer must actually decide, which specific clauses carry real risk if the review
gets them wrong, what a FAIL verdict means operationally, and a blank verdict block.

**No verdict is pre-filled anywhere in this document.** Filling one in without an actual reviewer
having reviewed it would be fabricating a sign-off — forbidden under this project's closure
discipline (§0.0 Rule 5) regardless of how confident a draft answer might look.

---

# PART A — RDY-0068: PILOT AGREEMENT REQUIREMENTS

Full content under review: `EV-068-pilot-requirements.md` (14 elements, §1; success gate, §2; exit
criteria, §3; hypercare, §4; PHI reflection, §5).

## A.1 What the reviewer must actually decide

This is not a proofreading pass. The reviewer is being asked to make six substantive calls:

1. **Is the success gate (EV-068 §2, four conditions, no partial credit) something we can hold a
   customer to, and something a customer could reasonably be expected to agree to in writing?**
   In particular, condition 3 requires a **witnessed backup restore against the customer's own live
   data** — decide whether that test itself needs its own liability carve-out if the restore fails
   or interacts badly with live data.
2. **Is the "no early-exit penalty, 30 days' notice, either party, any reason" term (EV-068 §3)
   commercially acceptable** — i.e., is the company willing to be exposed to a customer walking away
   at day 31 of a 60–90 day paid pilot with no penalty, in exchange for the disclosure positioning
   the whole strategy is built on?
3. **Is the on-premise variant's risk transfer sufficient** — EV-068 §1 element 8 states the
   customer "takes backup responsibility, stated in writing" for on-premise deployments. Decide
   whether "stated in writing" in a scope document is adequate risk transfer, or whether it needs
   indemnification or limitation-of-liability language in the actual agreement.
4. **Is the PHI/audit-log disclosure (EV-068 §5) legally sufficient disclosure before signature?**
   RDY-0055 established that bound SQL parameters land in `log.comments` as base64-encoded text —
   effectively unencrypted PHI in an audit table — the moment a pilot begins (a pilot is, by
   definition, the first point real patient data enters the system). Decide whether stating this in
   the agreement is adequate, or whether it requires a more specific risk acknowledgement, a
   data-processing addendum, or a KSA PDPL-specific clause.
5. **Can the agreement be signed with support/escalation/hypercare specifics still marked "not yet
   published"** (EV-068 §1 elements 5, 7 and §4, all deferred to RDY-0096, which is itself an open
   Owner decision — see `EV-096-options.md`)? Decide whether that is an acceptable gap to sign
   around, or a blocking dependency the first pilot cannot be offered without.
6. **Does the agreement need language addressing what happens to work product created during the
   pilot** — custom forms, layout configuration, fee schedules built during implementation — that is
   not addressed anywhere in the 14 elements as currently specified.

## A.2 Clauses that carry real risk if the review gets them wrong

| Clause | Location | Risk if wrong |
|---|---|---|
| Success-gate condition 3 (witnessed restore against live customer data) | EV-068 §2 | Data-loss or corruption exposure during a test performed on a real customer's live PHI, with no stated liability allocation if it goes wrong |
| No-penalty 30-day exit | EV-068 §3 | Revenue/collectability exposure — every pilot is effectively cancellable at will for the customer's full term minus 30 days |
| On-premise backup-responsibility transfer | EV-068 §1 element 8 | If a clinic loses its own on-premise data and disputes that responsibility was actually transferred, "stated in writing" in a scope document may not hold up as a liability shield without proper contract language |
| PHI/audit-log disclosure paragraph | EV-068 §5 | Regulatory exposure if the disclosure is judged inadequate under applicable KSA data-protection law, discovered only after real patient data is already in the system |
| Data-exit incorporation by reference | EV-068 §1 element 11 → `EV-073` | `EV-073` itself has an undecided deletion-timing question (its §3) — signing a pilot agreement that incorporates an incomplete procedure by reference could commit the company to a handover process that isn't fully defined yet |
| Support/hypercare terms marked "not yet published" | EV-068 §1 elements 5, 7, §4 | A signed agreement referencing a term that does not exist yet is either an unenforceable placeholder or a future unilateral commitment — the reviewer should decide which, and whether that is acceptable to sign around |

## A.3 What a FAIL verdict means in practice

- RDY-0068 stays **NOT READY**; the pilot-requirements pack cannot be used to draft an actual
  agreement as-is.
- **RDY-0069** (cost instrumentation) stays blocked — it depends on RDY-0068 producing an actual
  signed pilot, not merely a requirements pack.
- **No design partner can be offered a pilot.** GTM Phase 4's own gate (*"at least 2 pilots reach
  go-live and renew"*) cannot begin to be measured.
- The reviewer should name, specifically, which clause(s) in §A.2 must change and why, so the next
  revision addresses the actual objection rather than being resubmitted unchanged.

## A.4 Verdict block — BLANK

| Field | Entry |
|---|---|
| Reviewer name | |
| Role / capacity (Legal / Compliance / outside counsel) | |
| Date reviewed | |
| Elements reviewed (list, or "all 14 per EV-068 §1") | |
| **VERDICT** | ☐ PASS ☐ PASS WITH CONDITIONS ☐ FAIL |
| If PASS WITH CONDITIONS — conditions | |
| If FAIL — which clause(s), and what must change | |
| Signature | |

---

# PART B — RDY-0073: TERMINATION AND HANDOVER PROCEDURE

Full content under review: `EV-073-termination-and-handover.md` (steps T-1…T-9, §1; delivery
format, §2; the open deletion-timing question, §3; dry-run results, §4).

## B.1 What the reviewer must actually decide

1. **T-9 deletion timing is explicitly left open in `EV-073` §3** — it depends on RDY-0074 (a
   separate, not-yet-written retention policy) and on RDY-0055's finding that every backup taken
   during the engagement contains PHI (via the audit log). **The reviewer is the named decision
   owner for this** (`EV-073` states "that is Legal's" outright). Decide the retention period, the
   backup rotation policy, and the form of the deletion certificate — together, not piecemeal,
   since they depend on each other.
2. **Is the T-1 termination-notice standard ("in writing, by any channel, no reason required")
   adequate authentication for triggering a data-handover-and-eventual-deletion process?** Decide
   whether "any channel" is too permissive — could someone impersonating the customer's
   representative trigger T-1 and set the deletion clock running, or redirect the handover package
   to the wrong recipient?
3. **Is completion defined correctly?** The obligation is defined to end at T-6 (customer confirms
   the package is *readable*, not merely delivered) with **10 business days to confirm** and no
   stated consequence if the customer never responds. Decide whether an unresponsive customer should
   have a defined fallback (e.g., deemed acceptance after a stated period) so T-8 (access revocation)
   and T-9 (deletion) are not held open indefinitely by customer silence.
4. **Does "explicitly not delivered: the application... our hosting configuration" (EV-073 §2)
   adequately address ownership of work product** — custom configuration, forms, layouts, fee
   schedules built during the engagement — or does that need its own clause?
5. **Is the dry-run evidence in `EV-073` §4 sufficient to call the procedure "publishable"** given
   that T-5 (external delivery) was only partially exercised and T-6 (an outside reviewer confirming
   readability) has never been performed at all? Decide whether "publishable before signature" (the
   GTM O-3 standard this item exists to satisfy) requires a completed, not merely partial, dry run.

## B.2 Clauses that carry real risk if the review gets them wrong

| Clause | Location | Risk if wrong |
|---|---|---|
| T-9 deletion timing (currently undecided) | EV-073 §3, §1 | Indefinite retention of a former customer's PHI-containing backups is itself a regulatory exposure — the gap this review is supposed to close |
| T-1 "any channel, no reason required" notice | EV-073 §1 | A social-engineering vector: an unauthenticated notice could misdirect a full database-and-document handover package to the wrong party |
| T-6 "10 business days to confirm," no defined consequence for silence | EV-073 §1 | Procedure can stay open indefinitely if a customer simply never responds, blocking T-8 access revocation and T-9 deletion — meaning offboarding a non-responsive customer may never legally complete |
| "Not delivered: the application... hosting configuration" (silent on custom work product ownership) | EV-073 §2 | A dispute over who owns configuration/customisation built during the engagement has no contractual answer today |
| T-4/T-5 5-business-day delivery window against real PHI volumes | EV-073 §1 | Untested at scale — the dry run was against the seeded demo system, not a live customer's full dataset; the reviewer should decide if that gap is acceptable to publish against |

## B.3 What a FAIL verdict means in practice

- RDY-0073 stays **NOT READY**, and — because GTM O-3 requires this procedure be **published before
  a customer signs anything** — no scope template or pilot agreement referencing it (as both
  `EV-066` and `EV-068` already do, by design) can be represented to a prospect as a settled,
  reviewed procedure.
- **RDY-0066 and RDY-0068 do not become false on a FAIL** — they correctly reference `EV-073` by
  name and by design do not restate its content — but both should be flagged as citing an
  unapproved procedure until this review passes, and neither should close in the meantime.
- The whole commercial motion stalls at the same point: GTM Phase 4 cannot begin, because the
  termination procedure a design partner is promised in writing does not yet have a Legal verdict.
- The reviewer should name, specifically, which step(s) in §B.2 must change, and — if the answer to
  §B.1 item 1 is "we need RDY-0074 written first" — say so explicitly, since that reopens a
  currently-separate, unstarted requirement as a hard blocking dependency of this one.

## B.4 Verdict block — BLANK

| Field | Entry |
|---|---|
| Reviewer name | |
| Role / capacity (Legal / Compliance) | |
| Date reviewed | |
| T-9 retention period decided (if PASS) | |
| Backup rotation policy decided (if PASS) | |
| Deletion certificate form decided (if PASS) | |
| **VERDICT** | ☐ PASS ☐ PASS WITH CONDITIONS ☐ FAIL |
| If PASS WITH CONDITIONS — conditions | |
| If FAIL — which step(s), and what must change | |
| Signature | |

---

## Status summary

| RDY | Reviewer pack | Verdict |
|---|---|---|
| **0068** | Complete — §A.1–A.3 | **BLANK — not reviewed.** Nothing in this document constitutes a review. |
| **0073** | Complete — §B.1–B.3 | **BLANK — not reviewed.** Nothing in this document constitutes a review. |

**Neither item closes from this pack.** Both require an actual Legal/Compliance reviewer to read the
underlying requirements documents (`EV-068-pilot-requirements.md`, `EV-073-termination-and-handover.md`),
read this pack's risk framing, and fill in a verdict block by hand. **`Blocks`:** 0068 → G3 · 0073 →
G3, G6 (per each item's own card). No gate count recalculated here (§0.0 Rule 3).
