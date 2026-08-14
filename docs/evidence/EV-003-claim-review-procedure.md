# EV-003 — CLAIM REVIEW PROCEDURE

**Requirement:** RDY-0003 · **Gates:** G0, G5 · **Owner:** Product Marketing
**Acceptance:** *"A named individual is recorded as claim reviewer; a written review step exists;
**one sample artefact has passed through it and the review is recorded**."*
**Issued:** 2026-08-14 · **Agent B**, Phase 2B

---

## 1. The named claim reviewer

| Field | Value |
|---|---|
| **Claim reviewer** | **Mohammed Elfouly** |
| Recorded | 2026-08-14, by the Owner (PB-077) |
| Route | Relayed by the Owner, not a countersigned acceptance |
| Also holds | **Legal / Compliance reviewer for RDY-0028** (HR-02, `ASSIGNED — AWAITING REVIEW` since 2026-08-13) |

> **Recorded honestly as to form.** The appointment reached this document **through the Owner**. No
> acceptance signature from Mr Elfouly exists, and none has been invented. A countersigned acceptance
> should replace the relay when available — a documentation improvement, not a re-appointment.

> **⚠ One observation, recorded once and not pressed.** The same individual now holds **both** review
> roles in this phase, and HR-02 has been awaiting his review since 2026-08-13. **The constraint on
> RDY-0028 and on every claim-review-dependent item is therefore the same person's time.** That is the
> Owner's call and it is recorded, not queried — but it should be a known single point of dependency
> rather than a discovered one.

---

## 2. GTM R-02 rates drift back to prohibited language as **High**

That is why this exists. **A binding register with nobody enforcing it is advisory**, and one
prohibited claim destroys D-1 — the differentiator everything else rests on — permanently and
publicly.

---

## 3. The review step

**Every artefact that will be seen by anyone outside this project passes through this step before
it is used.** That includes: customer-facing documents, screenshots and captures, demo scripts,
website copy, sales collateral, pricing artefacts, and every Phase 3/4/5 output.

**It does not include:** internal engineering evidence (`EV-*` files, PB entries, patch records),
which are working records, not claims to a customer.

### 3.1 What the reviewer checks — the six gates

| # | Gate | Source | What fails it |
|---|---|---|---|
| **C-1** | **Every customer-facing sentence traces to a `CLM`/`CAP` ID** | Audit §27.4; MSG-001 L5 | Any assertion about the product with no traceable evidence behind it |
| **C-2** | **No prohibited term or claim** | §32; GTM §14.5 | The §32 list — including everything Saudi/compliance, inpatient, analytics, mobile, ERP, LIS/RIS/PACS, multi-tenant SaaS, certifications |
| **C-3** | **Audit-integrity discipline** | RDY-0056 | *"immutable"*, *"blockchain"*, *"tamper-proof"*, *"cannot be altered"*. Correct word: **tamper-evident**. The hash-not-HMAC and unchained qualification must sit **in the same visual unit** as the claim |
| **C-4** | **Sensitivity / MFA discipline** | RDY-0057 | *"MFA enforced"*, *"mandatory two-factor"*, *"field-level security"*. Enrolment is per-user and voluntary; sensitivity is encounter-level only and not applied to the API |
| **C-5** | **No competitive frequency figure** | RDY-0088 | Any *"N of 16"*, *"N of 11 GCC"*, or the product-visibility mean. **Publish the mechanism, not the number** |
| **C-6** | **Every status claim names its register** | RDY-0067 | Describing a Disabled / Uninstalled / Requires-Integration / Missing capability as available **without saying so in the same sentence** |

### 3.2 How it runs

1. The author submits the artefact and states which claims it makes.
2. The reviewer runs the **scan commands in `EV-056-057-088` §5** — `C-3`, `C-4` and `C-5` are
   mechanical and should never be eyeballed.
3. The reviewer works `C-1`, `C-2` and `C-6` by reading.
4. **One of three verdicts** is recorded, with the artefact's version identified:
   **APPROVED** · **APPROVED WITH CORRECTIONS** (listed) · **REJECTED** (with reasons).
5. The record is appended to §5 of this file. **No artefact is used before its verdict is recorded.**

### 3.3 ⚠ The scan that must not be run as specified

RDY-0088's acceptance says *"keyword and numeral scan"*. **A bare numeral scan does not work here.**
`[0-9]+ of (16|11|26)` also matches *"0 of 16 forms"* and *"16 of 16 installed cleanly"*, which are
legitimate engineering phrases in this project — 16 is simultaneously the number of scored
competitors, dormant clinical forms, and encounter forms.

**Use the competitor-scoped pattern in `EV-056-057-088` §5.** A reviewer handed the naive pattern
sees three hits, correctly dismisses two, and — pattern fatigue being what it is — may dismiss the
third. **That exact failure has already happened once in this project**, which is why it is called
out here rather than left to be rediscovered.

---

## 4. Queue — artefacts awaiting first review

**Nothing below has been reviewed. No verdict is pre-filled.**

| Artefact | Requirement it unblocks | Why it needs review |
|---|---|---|
| **`EV-067-published-registers.md`** | **RDY-0067** | Criterion 3 is *"it passes claim review"*. **This is the recommended sample artefact** — it is customer-facing, it exercises **all six** gates, and it already contains one caught-and-corrected C-5 violation to check the reviewer's eye against |
| `EV-056-057-088-claim-discipline.md` | **RDY-0056, 0057, 0088** | Each names *"claim-review sign-off"* in its acceptance |
| `docs/evidence/templates/export-README.txt` | RDY-0071 | Customer-facing text delivered at termination |
| `EV-073-termination-and-handover.md` | RDY-0073 | GTM O-3 publishes it **before signature** |

---

## 5. Review record

*Appended per review. Empty until the first review is performed.*

| # | Artefact | Version / hash | Reviewer | Date | C-1 | C-2 | C-3 | C-4 | C-5 | C-6 | Verdict | Corrections |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| — | *(none yet)* | | | | | | | | | | | |

---

## 6. Acceptance

| Criterion | Result |
|---|---|
| A named individual is recorded as claim reviewer | **MET** — Mohammed Elfouly, PB-077 |
| A written review step exists | **MET** — §3, with six gates and a mechanical scan set |
| **One sample artefact has passed through it and the review is recorded** | **NOT MET** — §5 is empty |

### Status: **RDY-0003 — NOT CLOSED. Two of three criteria met.**

**The third needs Mohammed Elfouly to review one artefact.** `EV-067` is queued as the recommended
sample. **No further engineering clears this**, and nothing downstream of it — RDY-0067, 0056, 0057,
0088, and by extension RDY-0004 — closes until it happens.

**Naming a reviewer is not a review.** That distinction is the PB-041 precedent, and it is applied
here to an appointment the Owner has just made rather than only to verdicts.

**`Blocks`: G0 G5.** No gate count moved (§0.0 Rule 3).
