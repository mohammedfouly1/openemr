# EV-065 / EV-066 / EV-069 — QUALIFICATION, SCOPE AND COST INSTRUMENTATION

**Requirements:** RDY-0065 (qualification checklist), RDY-0066 (scope template),
RDY-0069 (pilot cost instrumentation) · **Gates:** G3, G6 · **Owner:** Sales / Pilot Owner
**Issued:** 2026-08-14 · **Agent B**, Phase 2B

Three artefacts in one file because they run in sequence on a single deal:
**qualify → scope → instrument.**

---

# PART A — RDY-0065: QUALIFICATION CHECKLIST

**Used on a 15-minute call. Payer mix is asked FIRST**, because it is the one answer that ends the
conversation on its own.

## A.1 The disqualifiers — every one cites its source

**Any single YES disqualifies.** Say so in the call, and say why.

| # | If they need… | Source ID | What we say |
|---|---|---|---|
| **D-1** | **NPHIES claims / eligibility / pre-auth** | **GAP-0046**, L-26 | *"We don't submit insurance claims and we don't connect to NPHIES. That stays where it is today."* |
| **D-2** | **ZATCA-compliant invoices from the same system** | **GAP-0052, GAP-0053**, L-11 | *"We don't issue your tax invoice. There is no tax field anywhere in our billing chain — I'd rather say that than discover it at go-live."* |
| **D-3** | **Inpatient / ancillary** (LIS, RIS, PACS, pharmacy-as-a-business, dental charting) | **GAP-0001…0014** | *"This is an outpatient clinical record. Inpatient isn't switched off — it isn't there."* |
| **D-4** | **Enforced MFA to pass a security review** | **L-03**, CAP-0218 | *"Two-factor is supported and enrolment is voluntary. An administrator cannot mandate it. If your security review requires enforcement, we fail that test today."* |
| **D-5** | **Multi-tenant SaaS / automated provisioning** | **GAP-0043**, L-07 | *"Each clinic is its own database, provisioned by hand. That's a deliberate isolation choice, not a platform."* |
| **D-6** | **Analytics / dashboards / KPIs** | **GAP-0040, GAP-0041** | *"Fifty-five reports with CSV export, and no BI layer. If you want dashboards, we are the wrong system."* |
| **D-7** | **Arabic as the complete working interface** | **CLM-0030** | *"About half the interface, and roughly one in six dropdown values. An Arabic frame around a largely English clinical vocabulary."* (EV-086) |
| **D-8** | **A patient mobile app** | **GAP-0023, GAP-0024** | *"There isn't one, and there isn't one planned in this phase."* |

## A.2 Positive-fit signals (GTM §5.1) — count them

Self-pay dominant · owner present and decides · already invoicing electronically through a **separate**
system · **has had a data-access incident, a departure, or an inspection** · opening a second site ·
ophthalmology or a specialty our 18 active forms cover.

> **The strongest single signal is the incident.** POS-003's whole premise is loss of control over
> the clinical record — but **A-05 (is that a *felt* pain?) is unvalidated until V-3 runs.** If a
> prospect raises access, traceability or ownership **unprompted**, record it verbatim: those are
> V-3's data points arriving free (RDY-0077).

## A.3 Negative-fit signals (GTM §5.2) — not automatic disqualifiers, but count them too

Insurance is the majority of revenue · **asks about NPHIES in the first meeting** · expects the system
to issue the tax invoice · wants dashboards and KPIs · wants a patient mobile app · wants inpatient,
beds or a theatre · wants one system for everything.

**Three or more → out**, even with no hard disqualifier.

## A.4 Call sheet

| Field | |
|---|---|
| Clinic · date · who was on the call | |
| **Payer mix — self-pay %** *(asked first)* | |
| Providers · sites | |
| Specialty | |
| Disqualifiers triggered (D-1…D-8) | |
| Positive signals (count) | |
| Negative signals (count) | |
| **Did they raise access / traceability / ownership unprompted?** | ☐ yes ☐ no — *verbatim:* |
| **DECISION** | ☐ IN ☐ OUT |
| **Reason, in one sentence** | |

## A.5 Acceptance

| Criterion | Result |
|---|---|
| The checklist exists | **MET** |
| Every disqualifier cites a source ID | **MET** — D-1…D-8 all cite a GAP/L/CAP/CLM ID |
| **Used on three consecutive calls, each producing a recorded in/out decision and reason** | **NOT MET** — no calls held |

**RDY-0065: NOT CLOSED.** Needs three real calls. **`Blocks`: G3 G6.**

---

# PART B — RDY-0066: SCOPE TEMPLATE

## B.1 What is included

Implementation and configuration · facility and branding setup · role and ACL design ·
form building from the 18 active forms · list and layout configuration · fee-schedule setup ·
hosting, patching, backup and support for the term · training at implementation ·
**a documented exit** (`EV-071`, `EV-073`).

## B.2 What is EXCLUDED — in customer-facing language, in the contract

> **This system does not issue your tax invoice, and it does not submit your insurance claims.**
>
> Specifically **excluded**, and not deliverable at any price in this engagement:
>
> - **Invoicing, VAT and ZATCA e-invoicing.** There is no tax field anywhere in the billing chain.
> - **Insurance claims, NPHIES, eligibility checking and pre-authorisation.**
> - **Inpatient, beds, theatre, and ancillary systems** — LIS, RIS, PACS, dental charting.
> - **Analytics, dashboards and KPI reporting.** Fifty-five reports with CSV export, no BI layer.
> - **A patient mobile application.**
> - **Enforced multi-factor authentication.** Supported, voluntary, **cannot be mandated**.
> - **Migration into another vendor's system on exit.** Exit means **CSV and full database access**.
> - **Third-party integrations** — lab, eRx, payment gateway, fax/SMS. Each needs a contract **you**
>   hold, and is priced as a separate project **after** you hold it.
>
> **The four status registers are attached** (`EV-067`) and list every capability that is switched
> off, uninstalled, integration-dependent or absent. **Read them before signing. They are part of
> this agreement.**

## B.3 Two required steps before signature

| # | Step | Why |
|---|---|---|
| **B-1** | **A signed scope acknowledgement**, separate from the contract signature | So "we thought it did invoicing" cannot survive the first month |
| **B-2** | **The P-4 finance conversation is held and recorded** | P-4 is the finance persona. **They must hear D-1 and D-2 from us, in their own meeting, before signature** — not from their accountant afterwards |

**Data migration is quoted after inspection, never fixed-price sight-unseen** (PRC-002).

## B.4 Acceptance

| Criterion | Result |
|---|---|
| Names invoicing/VAT/ZATCA and claims/NPHIES as excluded, in customer-facing language | **MET** — §B.2 |
| Requires a signed scope acknowledgement | **MET** — B-1 |
| The P-4 finance conversation is a required step before signature | **MET** — B-2 |
| **Legal/compliance review recorded** | **✅ MET 2026-08-19** |

> **✅ APPROVED AS WRITTEN — 2026-08-19, Owner, given directly in conversation with the orchestrating
> session.** §B.1's inclusions, §B.2's exclusions (in the customer-facing language quoted above), and
> §B.3's two required pre-signature steps (B-1 scope acknowledgement, B-2 finance-persona conversation)
> are accepted with no change requested. Relayed by the Owner in this conversation, not a
> countersigned document — same convention as every other Owner-relayed decision this session.

**RDY-0066: CLOSED 2026-08-19.** It is also cited by **RDY-0073**,
whose acceptance requires the scope template to reference the termination procedure — **§B.1 does,
via `EV-073`.**

---

# PART C — RDY-0069: PILOT COST INSTRUMENTATION

**PRC-003 is BLOCKED and no price appears anywhere.** This builds the instrument that would
eventually unblock it. **It cannot run until a pilot exists** (RDY-0068).

## C.1 What is captured, from day one

**Recorded as it happens. Reconstructed hours are estimates wearing a timestamp.**

| Code | Measure | Unit | Captured by | Why separately |
|---|---|---|---|---|
| **C-1** | Implementation hours | h | Implementer, per session | Non-recurring |
| **C-2** | Configuration hours | h | Implementer | **Recurs** — implementation does not |
| **C-3** | Migration hours | h | Implementer | Quoted after inspection, so it needs its own basis |
| **C-4** | Hosting cost | SAR/month | DevOps | RDY-0064 |
| **C-5** | Backup / storage cost | SAR/month | DevOps | Grows with retention (RDY-0081) |
| **C-6** | **Support hours per clinic per month** | h/month | Support | **One of the two highest-value figures in the plan** |
| **C-7** | Patch burden | h/cycle | Engineer | From the first real cycle (RDY-0045) |
| **C-8** | Training effort | sessions + h | Implementer | |
| **C-9** | Third-party spend | SAR | Owner | Certificates, integrations, licences |
| **C-10** | **Provisioning time from the runbook** | h | The follower | `EV-047` §12. **Already capturable — it does not need a pilot** |

## C.2 The rule that makes it worth doing

**Record actuals, never estimates.** V-8's risk is that implementation effort exceeds the estimate,
and the only defence is a number that came from a clock. **An entry with no recorded date is
discarded rather than kept**, because a plausible wrong number is worse than a missing one — it will
be used.

## C.3 Acceptance

| Criterion | Result |
|---|---|
| An instrument exists capturing all nine named measures | **MET** — C-1…C-9, plus C-10 |
| **Instrumented against pilots #1 and #2** | **NOT MET — no pilot exists** |

**RDY-0069: NOT CLOSED, and not startable.** It depends on RDY-0068 (pilot agreement), which depends
on RDY-0065, RDY-0066 and RDY-0073. **`Blocks`: G6.**

**C-10 is the exception worth acting on now:** the provisioning time is capturable the moment someone
executes `EV-047`, needs no customer, and feeds PRC-003 directly.

---

## Status summary

| RDY | Artefact | Blocked on |
|---|---|---|
| **0065** | Qualification checklist | **Three real calls** |
| **0066** | Scope template | **Legal/compliance review** |
| **0069** | Cost instrumentation | **A pilot existing** (RDY-0068) |

**None closes. No gate count moved** (§0.0 Rule 3).
