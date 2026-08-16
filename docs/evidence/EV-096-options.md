# EV-096 — SUPPORT, ESCALATION AND TRAINING: THREE SERVICE-LEVEL OPTIONS (Owner decision card)

**Requirement:** RDY-0096 · **Gates:** G3, G6 · **Deps:** RDY-0064, RDY-0084 · **Owner:** Sales / Pilot
Owner (this decision specifically needs the **Owner's** real staffing capacity)
**Acceptance:** *"All six elements are defined and are reflected in the scope template and pilot
agreement; no uptime or performance figure appears anywhere; the response target is one the team has
agreed it can meet with current staffing."*
**Issued:** 2026-08-16 · **AGENT-COMMERCIAL**, Phase 2B — **CONTINUATION**

## Why this is a decision card, not a filled-in definition

RDY-0096's own acceptance criterion requires the response target to be **"one the team has agreed it
can meet with current staffing."** This document does not have the Owner's actual staffing capacity —
how many people can answer WhatsApp/email/phone, on what hours, with what backup coverage. **Inventing
a response target and presenting it as settled would fail the acceptance criterion by construction**:
it would be a target nobody on the team has actually agreed they can meet. Instead, this card lays out
three concrete, differently-scoped service levels — each with its channels, hours, response target,
escalation path and training plan fully specified — **and the staffing commitment each one implies**,
so the Owner can pick the one the real team can deliver, or say none of them fit and specify a fourth.

**No uptime or availability figure appears anywhere below**, consistent with GTM §15.3's rule that
none has ever been measured (RDY-0084 territory, not this item).

---

## The three options

### Level 1 — Business Hours

| Element | Definition |
|---|---|
| **Channels** | Email / ticket portal only |
| **Published hours** | Sun–Thu, standard clinic business hours (e.g. 09:00–17:00 AST) |
| **Response target** | *Candidate:* first response within 1 business day. **Not yet agreed — needs Owner confirmation against real staffing.** |
| **Escalation path** | Single tier: implementer handles the ticket; unresolved after [X] days escalates directly to the founder. No after-hours path. |
| **Training plan** | One session per role at implementation. No scheduled refresher; further training available at a published day rate. |
| **Staffing commitment implied** | **One person, part-time**, covering the published window only. No on-call rotation, no weekend coverage. The lightest commitment of the three. |

### Level 2 — Extended Hours

| Element | Definition |
|---|---|
| **Channels** | Email / ticket portal **+ WhatsApp** (GTM §15.3 names WhatsApp explicitly as an expected channel) |
| **Published hours** | Sun–Thu, extended window (e.g. 08:00–20:00 AST) |
| **Response target** | *Candidate:* first response within 4 business hours during published hours. **Not yet agreed — needs Owner confirmation.** |
| **Escalation path** | Two tier: implementer → a named on-call lead for anything unresolved or flagged P0, with the on-call lead empowered to loop in the founder directly for P0 only. |
| **Training plan** | Implementation session + one scheduled refresher at 30 days post-go-live. Further training at a published day rate. |
| **Staffing commitment implied** | **At least two people**, shift-able across the extended window, covering WhatsApp response inside business hours, plus a defined (even if informal) on-call designation for P0 issues. A real increment over Level 1 — requires someone other than the founder able to own tickets. |

### Level 3 — Priority / Design-Partner Hypercare

| Element | Definition |
|---|---|
| **Channels** | Email + WhatsApp **+ a direct phone line to a named contact** |
| **Published hours** | Sun–Thu extended window, **plus defined weekend P0-only coverage** (e.g. Fri/Sat, P0 issues only) |
| **Response target** | *Candidate:* P0 within 1 hour during published hours; all other severities within 4 hours. **Not yet agreed — needs Owner confirmation.** |
| **Escalation path** | Three tier, named individuals: implementer → on-call lead → founder, with the founder escalation explicitly documented (this is the level that matches the hypercare period already specified in `EV-068-pilot-requirements.md` §4 — 30 days immediately following go-live). |
| **Training plan** | Implementation session + 30-day refresher + 90-day refresher, role-based tracks (clinician / front-desk / admin). Further training at a published day rate. |
| **Staffing commitment implied** | **The heaviest of the three** — a real on-call rotation covering weekends, realistically 2–3 people with defined backup coverage so no single person is the entire escalation path. Appropriate specifically for the design-partner pilot phase (ties directly to the hypercare period a pilot agreement already promises), not necessarily sustainable as the steady-state offering for every future customer without additional hires. |

---

## Side-by-side

| | Level 1 — Business Hours | Level 2 — Extended Hours | Level 3 — Priority / Hypercare |
|---|---|---|---|
| Channels | Email only | Email + WhatsApp | Email + WhatsApp + phone |
| Hours | Standard business hours | Extended weekday hours | Extended weekday + weekend P0 |
| Response target (candidate) | 1 business day | 4 business hours | P0: 1 hour · other: 4 hours |
| Escalation tiers | 1 | 2 | 3, named individuals |
| Training | At implementation only | + 30-day refresher | + 30-day + 90-day, role-based |
| Staffing implied | 1 person, part-time | 2 people, shiftable | 2–3 people, weekend on-call |
| Relative cost to deliver | Lowest | Middle | Highest |

**No SAR figures are attached to any level.** Pricing is governed by PRC-003, which is BLOCKED
pending the cost instrumentation this project's RDY-0069 exists to build — support-hours-per-clinic
(C-6) is explicitly one of the two highest-value figures that instrumentation is meant to capture.
Attaching a price here, before that instrumentation runs, would assert a figure nobody has measured —
the same discipline RDY-0069's own acceptance criteria already enforce elsewhere in this project.

---

## What the Owner needs to do with this

1. **Pick one of the three levels as-is**, or specify a hybrid (e.g. Level 2 channels/hours with
   Level 1 escalation depth) — this card is a menu, not an exhaustive set.
2. **Confirm the candidate response target against real staffing.** Every response target above is
   marked *candidate* precisely because this document cannot verify "the team has agreed it can meet
   it with current staffing" — only the Owner, who knows actual headcount and availability, can make
   that statement true.
3. **State the staffing commitment the chosen level requires**, explicitly, so it is recorded rather
   than assumed — this is what makes the eventual pilot agreement's support clause (`EV-068
   §1 element 5) something the team can actually deliver rather than a promise made on paper.

**This document does not choose a level.** Doing so would invent a staffing commitment nobody has
actually made — exactly what the closure discipline for this project forbids.

## Downstream wiring, once chosen

- The chosen level's channels/hours/response target/escalation/training feed directly into:
  - `EV-066-pack.md` §1 ("what is included" — hosting/patching/backup/support for the term)
  - `EV-068-pilot-requirements.md` §1 elements 5–7 (support channel/hours, training, escalation),
    currently marked "not yet published" pending this decision
- **RDY-0069's C-6 (support hours per clinic per month) should be instrumented from day one against
  whichever level is chosen**, so the response target's real cost is measured rather than assumed.

## Acceptance

| Criterion | Result |
|---|---|
| Channels, hours, response target, escalation path, training plan all defined per level | **MET** — three full definitions above |
| The boundary of what support covers vs. a priced project (integrations, custom dev, migration) | **MET** — governed by `EV-066-pack.md` §3/§4 (integration and migration boundaries), referenced not restated |
| No uptime or performance figure appears anywhere | **MET** |
| **The response target is one the team has agreed it can meet with current staffing** | **NOT MET — no target is agreed; each is marked candidate pending Owner input** |
| **A level is chosen** | **NOT MET — Owner decision, not made here** |

### Status: **RDY-0096 — NOT CLOSED.** This card supplies the missing shape (three fully-specified,
differently-committed options) but explicitly withholds the one thing only the Owner can supply: which
one the real team can staff. **`Blocks`:** G3, G6 (per RDY-0096's own card). No gate count recalculated
here (§0.0 Rule 3).
