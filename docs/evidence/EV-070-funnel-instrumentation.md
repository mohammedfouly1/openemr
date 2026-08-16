# EV-070 — FUNNEL INSTRUMENTATION AND METRIC DEFINITIONS

**Requirement:** RDY-0070 · **Gates:** G6 · **Deps:** RDY-0065 · **Owner:** Sales / Pilot Owner
**Acceptance:** *"Each of the 13 metrics has a definition and a place where it is recorded; baselines,
no targets."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

**No target is asserted for any metric below.** GTM §29 is explicit — *"No targets are asserted; none
is supportable without a baseline"* — and none exists yet because no funnel activity has run.

---

## 1. Source

GTM §20 (funnel diagram, GTM-003, locked) and §29 (Measurement Framework) list **14 metric rows**;
the readiness document's condensed table (§9.3) rounds this to "13" — the discrepancy is the two
bolded cost metrics (implementation hours, support hours) sometimes counted as one instrumentation
line since both feed `RDY-0069`/PRC-003 directly. All 14 are defined below so nothing is dropped by
the rounding.

## 2. Metric definitions and recording location

| # | Metric | Definition (GTM §29, verbatim where quoted) | Recorded where | Recorded by |
|---|---|---|---|---|
| 1 | Qualified conversations / month | Meets §5.1, fails none of §5.2 | `EV-065` call sheet (§A.4), tallied monthly | Sales / Pilot Owner |
| 2 | Self-disqualification rate | *"Prospects who correctly rule themselves out before a call — a success metric, not a leak"* | Website analytics event on the qualification-checklist self-serve page (does not exist yet — **instrumentation point named, not built**) | Sales / Pilot Owner |
| 3 | Walkthrough requests / 100 sessions | Website → primary CTA | Website analytics (not yet instrumented — depends on the website existing) | Product Marketing |
| 4 | Qualification → demo | Conversion between funnel steps 3 and 4 (§20 diagram) | Derived from #1 and demo-scheduling records | Sales / Pilot Owner |
| 5 | Demo → written scope | Conversion between funnel steps 5 and 6 | Derived from demo log and `EV-066` scope-template issuance log | Sales / Pilot Owner |
| 6 | Scope → paid pilot | *"The real commercial signal"* | Derived from `EV-066` issuance vs `EV-068` pilot-agreement signature | Sales / Pilot Owner |
| 7 | Pilot → annual subscription | *"The only conversion that matters in Phase 4"* | Derived from pilot exit records (`EV-068` §exit criteria) vs subscription signature | Founder / Product Owner |
| 8 | Sales-cycle length | First contact → signed pilot | Timestamp delta between the qualification call sheet's first field and the pilot agreement's signature date | Sales / Pilot Owner |
| 9 | **Implementation hours per clinic** | Actual, recorded — *"feeds PRC-003 directly"* | `EV-065-066-069-commercial-artefacts.md` §C.1, code **C-1** | Implementer, per session |
| 10 | **Support hours per clinic per month** | Actual, recorded — *"feeds PRC-003 directly"* | Same file, code **C-6** | Support |
| 11 | Activation | % of seeded roles logging in weekly by week 4 | Derived from `users_secure.last_login_time` per pilot instance, queried monthly | DevOps / Infrastructure |
| 12 | Adoption depth | Encounters documented per provider per week | Derived from `form_encounter` grouped by `provider_id` and week, per pilot instance | DevOps / Infrastructure |
| 13 | Retention | Annual renewal | Contract record | Founder / Product Owner |
| 14 | Objection frequency | *"Which of O-1…O-15 appears, and where the deal stalls"*, reviewed monthly | A field on the `EV-065` call sheet and on the pilot exit record, tallied by objection ID | Sales / Pilot Owner, reviewed monthly per GTM §29 |

## 3. What is instrumented today vs. what is a named intention

Stated honestly because most of this funnel has never run once:

| State | Metrics |
|---|---|
| **Instrumentable today, no pilot needed** | #9's sibling in `EV-065-066-069` §C.1 code **C-10** (provisioning time from `EV-047`) is already capturable and feeds the same cost picture. Metric #1 and #14 have a recording field the moment the qualification checklist is used on a real call |
| **Depends on a website existing** | #2, #3 — no instrumentation can be wired to a page that has not been built |
| **Depends on a pilot existing (RDY-0068)** | #6, #7, #9, #10, #11, #12, #13 |
| **Depends on at least one completed sales cycle** | #4, #5, #8 |

## 4. Acceptance

| Criterion | Result |
|---|---|
| Each of the metrics has a definition | **MET** — §2, all 14 |
| Each has a place where it is recorded | **MET** — §2, third column; several are "named but not yet built" (§3), stated as such rather than glossed over |
| Baselines, no targets | **MET** — no target appears anywhere in this document, matching GTM §29's own instruction |

### Status: **RDY-0070 — NOT CLOSED.** Definitions and recording points are complete; the
instrumentation itself is unbuilt for any metric depending on a website or a pilot, both of which are
separately blocked (RDY-0068, and no website work is in scope for this phase).

**`Blocks`:** G6. No gate count moved (§0.0 Rule 3).
