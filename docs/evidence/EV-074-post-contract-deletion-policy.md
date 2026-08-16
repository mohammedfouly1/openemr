# EV-074 — POST-CONTRACT DATA DELETION AND BACKUP-HANDLING POLICY

**Requirement:** RDY-0074 · **Gates:** G3 · **Deps:** RDY-0073, RDY-0081 · **Owner:** Legal / Compliance
**Acceptance:** *"Covers backups taken during the engagement, with a stated timeframe."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

**This is the open question `EV-073` §3 named and deliberately left unfilled** rather than answered
with *"a plausible-sounding '90 days'"*. This artefact answers it with a stated rule and states, where
it cannot answer, exactly what remains a Legal decision.

---

## 1. Why this cannot be a simple "delete everything at T+N days" policy

Three facts compound, and all three were already established elsewhere in this document:

1. **`RDY-0055` (audit-PHI determination):** every event logged to `log`/`log_comment_encrypt` since
   the engagement began is retained there — meaning **PHI exists inside the audit trail itself**, not
   only inside `patient_data` and its satellites. Deleting the clinical tables does not delete the
   patient's data if the audit log survives.
2. **`RDY-0081` (backup policy):** every backup taken during the engagement is a **complete copy** of
   the database at that point in time — clinical tables *and* the audit log. A database deletion after
   contract end that does not also address backup rotation leaves the deleted data recoverable from
   the most recent backup, indefinitely, until that backup itself expires.
3. **`RDY-0073` T-9 (deletion step):** the termination procedure names deletion as the final step but
   explicitly defers its timeframe to this document.

**The three must be decided together, which is why `EV-073` left T-9 open rather than filling it.**

## 2. The policy

| Rule | Value | Basis |
|---|---|---|
| **D-1** | The **live database and document store** are deleted **within 30 calendar days of contract termination**, measured from `EV-073` step **T-8** (access revocation), not from the customer's notice (T-1) — deletion happens after the handover is confirmed received and readable (T-6), never before | Mirrors the T-6-before-T-8 sequencing already locked in `EV-073`; 30 days is a stated choice, not a regulatory citation — **no Saudi data-retention regulation has been read for this determination (see §4)** |
| **D-2** | **Backups taken during the engagement** are retained no longer than the **shorter of**: (a) the backup policy's own retention window (`RDY-0081`, default `--keep=7` most-recent copies at the time of writing), or (b) **90 calendar days past D-1's deletion date** | Backups roll off their own retention window in the normal course; D-2's 90-day ceiling exists only to bound the case where a backup was taken close to termination and would otherwise outlive D-1 by nearly a full retention cycle |
| **D-3** | **A deletion certificate is issued** naming: what was deleted, from where (live instance + each backup generation), the date, and who performed it | This is `EV-073` T-9's evidence artefact — it did not exist as a defined deliverable before this document |
| **D-4** | **The customer may request written confirmation that no copy remains**, and is entitled to receive it | Closes the loop `O-3`/`O-11` promise — *"we do not hold the schema, the format or the credentials hostage"* extends to *"and we do not quietly keep a copy either"* |
| **D-5** | **Off-instance/cloud backup copies** (the `RDY-0081` leg still blocked on `RDY-0064` hosting provisioning) are deleted under the **same D-1/D-2 timeframe**, once that leg exists — this document's rule applies prospectively, it does not wait for a second decision later | So the policy does not need re-issuing the day the off-instance leg becomes real |

## 3. What this does NOT cover, named rather than silently omitted

| Not covered | Why |
|---|---|
| **A regulatory-mandated minimum retention** (e.g., a Saudi medical-records retention requirement that might *require* the customer, not us, to retain records for a period) | This document governs **our** copies, made for hosting/backup purposes, not the customer's own regulatory retention obligation, which is theirs to hold and is out of scope for a hosting vendor's deletion policy |
| **Legal hold** (litigation, investigation, or a regulator's request to preserve rather than delete) | Not addressed — no such event has occurred, and the policy does not attempt to anticipate the legal mechanism for one |
| **The specific technical means of deletion** (secure wipe vs. standard `DROP DATABASE`) | An implementation detail belonging with `RDY-0047`'s runbook or a future security review, not a policy decision |

## 4. ⚠ Explicitly not decided here, and why guessing would be worse than leaving it open

**D-1's 30-day figure and D-2's 90-day ceiling are this document's proposed defaults, not a legal
determination.** No primary Saudi source on medical-record retention or data-protection deletion
timeframes has been read for this artefact — the same discipline `RDY-0078` already applies to ZATCA
and NPHIES applies here: **do not convert an unverified assumption into a stated policy fact.** These
two numbers are marked below as requiring Legal/Compliance review before this policy is treated as
binding, exactly as `RDY-0074`'s own `Owner` field already says (Legal / Compliance).

## 5. Acceptance

| Criterion | Result |
|---|---|
| Covers backups taken during the engagement | **MET** — D-2, D-5 |
| States a timeframe | **MET** — D-1 (30 days), D-2 (90-day ceiling) |
| Reflects RDY-0055 (PHI in the audit log) | **MET** — §1.1 |
| Legal/Compliance review of the stated timeframes | **NOT MET** — §4; the numbers are proposed defaults pending review, not yet a binding policy |

### Status: **RDY-0074 — NOT CLOSED.** Policy structure and rule are written and internally
consistent with RDY-0055/0073/0081; the specific timeframes need Legal/Compliance sign-off before
they can be called a decision rather than a proposal. **This is a named human-blocked item, not an
engineering gap** — the review is Legal/Compliance's, the same reviewer role RDY-0028 and RDY-0066
already need.

**`Blocks`:** G3. No gate count moved (§0.0 Rule 3).

**Unblocks on Legal/Compliance sign-off of §2:** `EV-073`'s T-9 step gains its timeframe, closing one
of `EV-073`'s two remaining acceptance gaps referenced from that side (the other is T-6, the customer
readability confirmation, which is unrelated to this document).
