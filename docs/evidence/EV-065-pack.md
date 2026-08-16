# EV-065 — QUALIFICATION CALL PACK (call script + 3-call recording log)

**Requirement:** RDY-0065 · **Gates:** G3, G6 · **Owner:** Sales / Pilot Owner
**Acceptance:** *"The checklist exists; every disqualifier cites a source ID; it has been used on
three consecutive calls, each producing a recorded in/out decision and a recorded reason."*
**Issued:** 2026-08-16 · **AGENT-COMMERCIAL**, Phase 2B — **CONTINUATION**

**Builds on `EV-065-066-069-commercial-artefacts.md` Part A (Agent B, 2026-08-14), which already
contains the disqualifier table with source citations and a single-call sheet template. That
content is the source of truth for the disqualifier wording and citations and is not restated here
except where the caller needs it in hand during a live call.** This pack adds two things Part A does
not: (1) the checklist reshaped as a **timed, sequential call script** — usable in real time on a
15-minute call, payer mix asked before anything else, rather than a reference table — and (2) the
**3-call recording log** that RDY-0065's acceptance criterion specifically requires (three
*consecutive* calls, each with its own recorded decision and reason), plus a rollup tracker showing
progress against that criterion.

---

## 1. The 15-minute call script

Order matters. **Payer mix is asked first, before introductions finish**, because GTM identifies it
as the ICP's defining attribute and A-02 (majority self-pay) as the highest-risk assumption in the
strategy — a payer-mix answer alone can end the call. Timings are a guide, not a hard stop.

| Min | Step | What to ask / say | Source |
|---:|---|---|---|
| **0:00–0:30** | Open | One-line intro, confirm who's on the call | — |
| **0:30–2:30** | **Payer mix — FIRST** | *"Before anything else — roughly what share of your revenue is self-pay versus insurance?"* Record the % verbatim. | GTM §5.1, A-02 |
| **2:30–7:00** | Disqualifiers (D-1…D-8) — ask, don't assume | Run each line below. **Any single YES disqualifies.** Say the quoted sentence when it fires. | see §2 |
| **7:00–10:00** | Positive-fit signals | Self-pay dominant confirmed? Owner present/deciding? Already invoicing electronically elsewhere? Had a data-access incident, departure, or inspection? Opening a second site? Specialty covered by our 18 active forms? Count how many. | GTM §5.1 |
| **10:00–12:30** | Negative-fit signals | Insurance-majority? Asked about NPHIES unprompted? Expects us to issue the tax invoice? Wants dashboards/KPIs? Wants a patient app? Wants inpatient/beds/theatre? Wants "one system for everything"? **Three or more → out**, even with no hard disqualifier. | GTM §5.2 |
| **12:30–13:30** | **Unprompted signal capture** | Did they raise access, traceability, or ownership of their own data **without being asked**? If so, write down what they said, verbatim. This is free V-3 data (RDY-0077) — A-05 is still unvalidated. | RDY-0077 |
| **13:30–15:00** | Decision + reason | State the decision on the call if a hard disqualifier fired. Otherwise: "we'll follow up within one business day." Record IN/OUT and the one-sentence reason **before ending the call**, not afterward from memory. | — |

## 2. Disqualifiers — the script line for each (full rationale in `EV-065-066-069` §A.1)

| # | Trigger | Source ID | Say this |
|---|---|---|---|
| **D-1** | Needs NPHIES claims / eligibility / pre-auth | GAP-0046, L-26 | *"We don't submit insurance claims and we don't connect to NPHIES. That stays where it is today."* |
| **D-2** | Needs ZATCA-compliant invoices from this system | GAP-0052, GAP-0053, L-11 | *"We don't issue your tax invoice. There is no tax field anywhere in our billing chain."* |
| **D-3** | Needs inpatient / ancillary (LIS, RIS, PACS, pharmacy-as-business, dental charting) | GAP-0001…0014 | *"This is an outpatient clinical record. Inpatient isn't switched off — it isn't there."* |
| **D-4** | Needs enforced MFA for a security review | L-03, CAP-0218 | *"Two-factor is supported and voluntary. An administrator cannot mandate it."* |
| **D-5** | Needs multi-tenant SaaS / automated provisioning | GAP-0043, L-07 | *"Each clinic is its own database, provisioned by hand. That's a deliberate isolation choice."* |
| **D-6** | Needs analytics / dashboards / KPIs | GAP-0040, GAP-0041 | *"Fifty-five reports with CSV export, no BI layer. If you want dashboards, we're the wrong system."* |
| **D-7** | Needs Arabic as the complete working interface | CLM-0030 | *"About half the interface, roughly one in six dropdown values. An Arabic frame around a largely English clinical vocabulary."* |
| **D-8** | Needs a patient mobile app | GAP-0023, GAP-0024 | *"There isn't one, and none is planned in this phase."* |

## 3. Three-call recording log

**This is the specific instrument RDY-0065's acceptance criterion requires: three *consecutive*
calls, each producing its own recorded in/out decision and reason.** One blank sheet per call. Do
not reuse or overwrite a prior call's sheet — each is separate evidence.

### Call 1 of 3

| Field | Entry |
|---|---|
| Clinic / date / caller / who was on the call | |
| **Payer mix — self-pay %** *(asked first)* | |
| Providers · sites · specialty | |
| Disqualifiers triggered (D-1…D-8, list any) | |
| Positive signals (count / which) | |
| Negative signals (count / which) | |
| Unprompted access/traceability/ownership remark? | ☐ yes ☐ no — verbatim: |
| **DECISION** | ☐ IN ☐ OUT |
| **Reason (one sentence)** | |

### Call 2 of 3

| Field | Entry |
|---|---|
| Clinic / date / caller / who was on the call | |
| **Payer mix — self-pay %** *(asked first)* | |
| Providers · sites · specialty | |
| Disqualifiers triggered (D-1…D-8, list any) | |
| Positive signals (count / which) | |
| Negative signals (count / which) | |
| Unprompted access/traceability/ownership remark? | ☐ yes ☐ no — verbatim: |
| **DECISION** | ☐ IN ☐ OUT |
| **Reason (one sentence)** | |

### Call 3 of 3

| Field | Entry |
|---|---|
| Clinic / date / caller / who was on the call | |
| **Payer mix — self-pay %** *(asked first)* | |
| Providers · sites · specialty | |
| Disqualifiers triggered (D-1…D-8, list any) | |
| Positive signals (count / which) | |
| Negative signals (count / which) | |
| Unprompted access/traceability/ownership remark? | ☐ yes ☐ no — verbatim: |
| **DECISION** | ☐ IN ☐ OUT |
| **Reason (one sentence)** | |

### Rollup tracker — fill in only after each call actually happens

| Call | Date held | Decision recorded? | Reason recorded? | Running total toward acceptance |
|---|---|---|---|---|
| 1 | — | ☐ | ☐ | 0 / 3 |
| 2 | — | ☐ | ☐ | 0 / 3 |
| 3 | — | ☐ | ☐ | 0 / 3 |

**Acceptance criterion is met only when the rollup reads 3 / 3, with all three sheets above filled
from real calls.** A sheet completed from a hypothetical or a role-play does not count — the
criterion says "consecutive calls," which presumes real prospects.

---

## 4. Acceptance

| Criterion | Result |
|---|---|
| The checklist exists | **MET** — §1/§2 here, plus the underlying table in `EV-065-066-069` §A.1 |
| Every disqualifier cites a source ID | **MET** — D-1…D-8, §2 |
| **Used on three consecutive calls, each producing a recorded in/out decision and reason** | **NOT MET** — the log in §3 is blank; no calls have been held |

### Status: **RDY-0065 — NOT CLOSED.**

**What this pack does not and cannot do:** hold the three calls. That requires real prospects on the
phone, which is Sales/Pilot Owner's action, not a document. This pack only removes the excuse that
no instrument exists — the checklist and the recording log are both ready to use today.

**`Blocks`:** G3, G6 (per RDY-0065's own card). No gate count recalculated here (§0.0 Rule 3).
