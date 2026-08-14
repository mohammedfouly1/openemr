# EV-073 — TERMINATION AND HANDOVER PROCEDURE

**Requirement:** RDY-0073 · **Gates:** G3, G6 · **Owner:** Legal / Compliance + DevOps
**Acceptance:** *"The procedure exists and is publishable; it is referenced by the scope template
(RDY-0066) and the pilot agreement (RDY-0068); a dry run has been performed against the demo
system."*
**Produced:** 2026-08-14 · **Agent B**, Phase 2B

**GTM O-3 promises this is published *before you sign*.** That makes it a pre-sale artefact, not a
wind-down formality — which is why it is a P0 rather than something drafted when a customer leaves.

---

## 1. The procedure

| # | Step | Owner | Timeframe | Evidence produced |
|---|---|---|---|---|
| **T-1** | Customer gives notice, in writing, by any channel. **No reason is required and none is asked for.** | Customer | — | Dated acknowledgement issued within **1 business day** |
| **T-2** | Confirm the handover contact and the delivery channel | Sales / Pilot Owner | 2 business days | Written confirmation naming the recipient |
| **T-3** | **Final backup taken and verified restorable** before anything is changed | DevOps | Before any other step | Backup log + restore verification (RDY-0082) |
| **T-4** | **Produce the export package** per `EV-071` — database, report CSVs, document payloads, document manifest, README, checksums | DevOps | **5 business days from T-1** | The package + `CHECKSUMS.sha256` |
| **T-5** | Deliver the package by the agreed channel; customer verifies checksums | DevOps → Customer | Within the same 5 days | Delivery record |
| **T-6** | **Customer confirms receipt AND readability**, in writing. Not "delivered" — **readable** | Customer | 10 business days to confirm | Signed/emailed confirmation |
| **T-7** | Remediate anything the customer reports unreadable or missing | DevOps | 5 business days | Re-issued package |
| **T-8** | Service is terminated; access is revoked | DevOps | After T-6 | Access-revocation record |
| **T-9** | **Data and backup deletion** per the retention decision | DevOps + Legal | See §3 | Deletion certificate |

**The obligation does not end at delivery — it ends at T-6, when the customer confirms they can
*read* it.** That is the difference between an exit and a handover, and it is the clause worth
publishing.

---

## 2. What is delivered, in what format

Exactly the `EV-071` package. Restated here because the termination procedure is customer-facing and
must stand alone:

| Item | Format | Readable without Thiqa software? |
|---|---|---|
| Complete database | SQL dump, all 283 tables | **Yes** — any MySQL/MariaDB |
| Report extracts | CSV | **Yes** — any spreadsheet |
| Uploaded documents | Original files in per-patient folders | **Yes**, *with the manifest* |
| Document manifest | CSV mapping every file to patient, original filename and type | **Yes** |
| Verification | SHA-256 per file | **Yes** |

**Explicitly not delivered:** the application (GPL-3.0-or-later, obtainable independently), our
hosting configuration, or a migration into another vendor's system.

---

## 3. ⚠ Deletion timing is NOT decided here — and it interacts with RDY-0055

Step **T-9** has no timeframe because two inputs are missing, and inventing either would be worse
than leaving it open:

1. **RDY-0074** (post-contract deletion and backup-handling policy) is a separate open requirement.
2. **RDY-0055 established that PHI is present in the audit log**, so *every backup taken during the
   engagement contains PHI*. Deleting "the database" does not delete the customer's data if backups
   survive. **The retention period, the backup rotation and the deletion certificate must be decided
   together**, and that is a Legal decision.

**Recorded as the open question rather than filled with a plausible-sounding "90 days".**

---

## 4. Dry run — performed

**The `EV-071` execution of 2026-08-14 is this procedure's dry run for steps T-3 to T-5.**

| Step | Dry-run result |
|---|---|
| T-3 final backup | **Performed** — `pre-rdy0083-20260814-030150.sql`, 283 tables, SHA-256 recorded |
| T-4 package produced | **Performed** — 15 files, 79 MB, `sha256sum -c` → **14 of 14 OK** |
| T-5 delivery + verification | **Partially** — checksums verify locally; no external delivery channel exists yet |
| T-6 customer confirms readability | **Not performed** — no reviewer has opened the package cold |
| T-9 deletion | **Not performed** — no policy (§3) |

**The dry run found the same defect `EV-071` records:** the first package's document payloads were
UUID-named with no extension and **would have failed T-6**. The manifest step exists because the dry
run produced an unreadable package first.

---

## 5. Acceptance

| Criterion | Result |
|---|---|
| The procedure exists and is publishable | **MET** |
| A dry run has been performed against the demo system | **PARTIAL** — T-3/T-4 fully; T-5 partially; T-6 and T-9 not performed |
| Referenced by the scope template (**RDY-0066**) | **NOT MET** — RDY-0066 does not exist yet |
| Referenced by the pilot agreement (**RDY-0068**) | **NOT MET** — RDY-0068 does not exist yet |

### Status: **RDY-0073 — NOT CLOSED.** Two of four criteria are references to artefacts that do not yet exist.

**`Blocks`: G3 G6.** No gate count moved (§0.0 Rule 3).

**Dependency note:** RDY-0073's card lists deps `0071`; its *acceptance* additionally requires 0066
and 0068 to reference it. **0068 in turn depends on 0073** (its card lists `0065, 0066, 0073`).
That is not a cycle in practice — 0073 is written first and 0068 cites it — but 0073 cannot *close*
until 0068 exists. **Recorded so the ordering is explicit rather than discovered later**, in the same
spirit as T0-3's Cluster 3 ↔ RDY-0044 resolution.
