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

**Two rows below have been reviewed — see §5. The remaining two have no verdict pre-filled.**

| Artefact | Requirement it unblocks | Why it needs review |
|---|---|---|
| ~~`EV-067-published-registers.md`~~ | ~~RDY-0067~~ | **Reviewed 2026-08-19 — §5 row 1, APPROVED FOR PUBLICATION** |
| ~~`EV-056-057-088-claim-discipline.md`~~ | ~~RDY-0056, 0057, 0088~~ | **Reviewed 2026-08-19 — §5 row 2, APPROVED FOR PUBLICATION** |
| `docs/evidence/templates/export-README.txt` | RDY-0071 | Customer-facing text delivered at termination |
| `EV-073-termination-and-handover.md` | RDY-0073 | GTM O-3 publishes it **before signature** |

---

## 5. Review record

*Appended per review.*

| # | Artefact | Version / hash | Reviewer | Date | C-1 | C-2 | C-3 | C-4 | C-5 | C-6 | Verdict | Corrections |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `EV-067-published-registers.md` | commit `df335a2c3` (current at review time) | Mohammed Elfouly | 2026-08-19 | PASS | PASS | PASS | PASS | PASS | PASS | **APPROVED FOR PUBLICATION** | none |
| 2 | `EV-056-057-088-claim-discipline.md` | current at review time (PB-372) | Mohammed Elfouly | 2026-08-19 | PASS | PASS | PASS | PASS | PASS | N/A | **APPROVED FOR PUBLICATION** | none |

**Review conducted (row 1):** the orchestrating session ran the §3.2/§3.3 mechanical scans (the
competitor-scoped C-5 pattern, not the naive one) against `EV-067` and found zero hits on all
three (C-3/C-4/C-5); read the document for C-1 (every entry traces to a CAP/GAP ID), C-2 (no §32
prohibited term used as a claim), and C-6 (every register carries its mandatory phrasing — flag
named for Disabled, registration step + external dependency named for Uninstalled/Requires
Integration, no roadmap implication for Missing). Findings were put to Mohammed Elfouly directly,
who reviewed and gave the verdict above — the reviewer's judgment, not inferred or assumed by the
session that prepared the findings. Recorded per this document's own Rule 5 standard: what was
received, from whom, by what route.

**This is RDY-0003's required sample.** Per §4, `EV-067` was the recommended artefact and is now
reviewed. Per §6 below, this closes the third and final acceptance criterion.

**Review conducted (row 2):** the same session re-ran the three §5 mechanical scans from
`EV-056-057-088` itself against the current state of `docs/` (not just cited the 2026-08-14 run) —
C-3 and C-4 reconfirmed zero genuine violations; C-5 (competitor-scoped, not the naive numeral scan)
reconfirmed the one already-known, already-fixed `EV-067` violation and surfaced one new observation:
extensive unguarded "N of 16"-pattern text in `docs/Product-Positioning-and-GTM-Locked-Strategy.md`,
judged out of scope under this document's own §3 exemption for internal working records (that file is
internal strategy/planning material, not itself shown to a customer — it even marks such figures
"BLOCKED" for publication at its own line 1259). Read the artefact for C-1 (its qualifications trace
to CLM-0024, CLM-0025 and L-23) and C-2 (the prohibited terms in it appear only inside its own
"prohibited, absolutely" framing, never used as a claim). **C-6 does not apply** — this artefact
defines claim qualifications and prohibitions, not capability status registers, so there is no
Disabled/Uninstalled/Missing phrasing to check. The Product-Positioning-and-GTM-Locked-Strategy.md
observation was put to Mohammed Elfouly alongside the verdict question rather than decided silently;
his recorded answer approved without an additional correction. Closes RDY-0056, RDY-0057 and
RDY-0088 together — the shared artefact named in all three acceptance criteria.

---

## 6. Acceptance

| Criterion | Result |
|---|---|
| A named individual is recorded as claim reviewer | **MET** — Mohammed Elfouly, PB-077 |
| A written review step exists | **MET** — §3, with six gates and a mechanical scan set |
| **One sample artefact has passed through it and the review is recorded** | **MET 2026-08-19** — §5 row 1, `EV-067`, APPROVED FOR PUBLICATION |

### Status: **RDY-0003 — CLOSED 2026-08-19. All three criteria met.**

**Correcting this document's own earlier claim before it propagates further:** the line originally
here said closing this item would close "RDY-0067, 0056, 0057, 0088, and by extension RDY-0004" as a
group. **That overstated it.** Only **RDY-0067** closes as a direct consequence — `EV-067` *is* the
artefact both items share. **RDY-0056, RDY-0057 and RDY-0088 each name their own artefact**
(`EV-056-057-088-claim-discipline.md`), which has not itself been put through this review step —
its qualifications are defined and its scans are clean (§6 of that file), but the review-record
step (this file's §5) has not run against it. **RDY-0004 packages the prohibited-claim list
downstream and needs its own check**, not an inherited one. None of these three should be marked
closed on the strength of RDY-0003 alone; each needs the same treatment `EV-067` just received,
against its own artefact.

**Naming a reviewer is not a review.** That distinction is the PB-041 precedent, and it held all
the way through — the review that closes this item is a real one, recorded in §5 above with the
reviewer's own verdict, not inferred from his having been named.

**`Blocks`: G0 G5.** Gate-count decrement recorded in the main readiness document's next PB-3xx
sync, per §0.0 Rule 3 — not recalculated here.
