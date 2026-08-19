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
| Referenced by the scope template (**RDY-0066**) | **MET (2026-08-16, AGENT-DOC)** — `EV-065-066-069-commercial-artefacts.md` §B.1 lists *"a documented exit (`EV-071`, `EV-073`)"* under what is included, and its own §B.4 note already recorded the cross-reference |
| Referenced by the pilot agreement (**RDY-0068**) | **MET (2026-08-16, AGENT-DOC)** — `EV-068-pilot-requirements.md` §1 element 11 ("Customer-data exit") incorporates this document by reference |

### Status (2026-08-16): **RDY-0073 — NOT CLOSED.** Both artefact-reference criteria are now met; the
outstanding gap is T-5 (delivery confirmation) and T-6 (customer readability confirmation), which need
an actual delivery channel and an external reviewer — neither of which this correction supplies. **See
§6 (2026-08-19) for the demo-system dry run and the current status.**

**`Blocks`: G3 G6.** No gate count moved (§0.0 Rule 3).

**Dependency note:** RDY-0073's card lists deps `0071`; its *acceptance* additionally requires 0066
and 0068 to reference it. **0068 in turn depends on 0073** (its card lists `0065, 0066, 0073`).
That is not a cycle in practice — 0073 is written first and 0068 cites it — but 0073 cannot *close*
until 0068 exists. **Recorded so the ordering is explicit rather than discovered later**, in the same
spirit as T0-3's Cluster 3 ↔ RDY-0044 resolution.

---

## 6. Cross-reference confirmation and demo-system dry run — 2026-08-19 (RDY-0073 completion agent, PB-384/385)

### 6.1 Cross-reference confirmation — re-verified directly against both target files, not re-asserted

- **RDY-0066 (scope template).** `EV-065-066-069-commercial-artefacts.md` §B.1 lists *"a documented
  exit (`EV-071`, `EV-073`)"* among what the pilot scope template includes, and its closure line
  states: *"RDY-0066: CLOSED 2026-08-19. It is also cited by RDY-0073, whose acceptance requires the
  scope template to reference the termination procedure — §B.1 does, via `EV-073`."* **CONFIRMED
  ADEQUATE** — names this document by ID, incorporates by reference, does not restate the procedure,
  same convention as this document's own §2 header note.
- **RDY-0068 (pilot agreement).** `EV-068-pilot-requirements.md` §1 element 11 ("Customer-data exit")
  reads: *"`EV-073`'s termination and handover procedure, incorporated by reference — **not
  restated**, so the two documents cannot drift apart."* §5's closure addendum additionally commits
  the pilot to running *"`EV-073`'s termination and handover procedure... in full, including its T-6
  confirmation step."* **CONFIRMED ADEQUATE** — same incorporate-by-reference pattern.

Both citations were already in place before this session (2026-08-16, AGENT-DOC, per this document's
own §5 table and `AGENT-CLAIMS.md` rows 255/259). This pass is a direct re-read of both source files,
confirming the citations are real, correctly named, and follow the project's own "incorporate by
reference, don't duplicate" convention rather than taking the earlier self-report at face value. **No
edit was needed to either file** — both already reference this document adequately.

### 6.2 Dry run against the demo system — all nine steps walked, today, non-destructively

Performed against the live seeded demo system (`openemr` database on `127.0.0.1:3306`,
`sites/default/`), not simulated and not merely re-cited from the 2026-08-14 partial dry run in §4.
**No real seeded data, account, or backup was deleted, revoked, or destroyed by this pass.**

| Step | Result | How verified |
|---|---|---|
| **T-1** notice | Well-defined, no system dependency | Pure process step (any channel, no reason required) — nothing in the demo system to exercise |
| **T-2** confirm contact/channel | Well-defined, no system dependency | Pure process step (Sales / Pilot Owner) |
| **T-3** final backup, verified restorable | **Executable — demonstrated non-destructively, today** | `EV-082`'s backup→restore→verify chain (283/283 table-checksum match, two authenticated logins, correct ACL enforcement, clean D-1 tamper report) is still live on this host: re-confirmed today, read-only — `SHOW DATABASES LIKE '%rdy0082%'` returns `openemr_rdy0082_restore`; `sites/rdy0082restore/` is populated on disk. The authoritative `openemr` schema was only read (`SHOW DATABASES`), never written |
| **T-4** produce export package | **Executable — already demonstrated** | Cited, not re-run, per this task's briefing: `EV-071`'s 2026-08-14 execution (15 files, 79 MB, 14/14 checksums OK) plus its 2026-08-16 extension (7 of 9 reachable CSV reports export clean, §5.2). **Register row 0071 (line 1070 as of this read) records a 2026-08-19 update from a parallel browser-check agent, ahead of the `EV-071` file itself**: `pat_ledger.php`'s live HTTP round-trip was obtained (real authenticated session, genuine comma-separated data, fix confirmed live not just by code review) — but that same check found a *new* gap: no UI button existed to trigger the export (only a hand-crafted request parameter reached it), unlike the other 8 CSV reports; a button was added same day, `php -l` clean, **but not yet click-tested live** (browser extension disconnected before it could be exercised). Cited from the register row since `EV-071.md` had not yet been updated with this addendum as of this read — a genuine race between two concurrent sessions, not fabricated by either |
| **T-5** deliver + customer verifies checksums | **PARTIAL — genuine, unchanged gap** | Local checksum verification (`sha256sum -c`) is proven (T-4). **No external delivery channel exists on this system.** Recorded plainly, not papered over |
| **T-6** customer confirms receipt AND readability | **NOT PERFORMED — cannot be satisfied by any dry run** | By definition this step is an external human opening the package cold and confirming it is readable *without our help*. No such reviewer exists in this session — the same class of human-blocked criterion as `EV-071`'s still-open "reviewer confirms usable" leg. Not fabricated |
| **T-7** remediate unreadable/missing items | **Well-defined, verified conceptually** | Mechanism = re-run the T-4 export procedure, already proven executable. Not exercised live — T-6 has not run, so nothing has been reported to remediate |
| **T-8** terminate service; revoke access | **DESTRUCTIVE — verified conceptually, NOT executed** | Read-only `DESCRIBE users` confirms `active` (`tinyint(1)`, default `1`) and `authorized` are real, standard OpenEMR account-gating columns. Read-only `SELECT` confirms all six seeded demo accounts (`r.aldosari`, `y.alharbi`, `s.almutairi`, `n.alqahtani`, `k.alotaibi`, `m.alzahrani`) exist and remain `active=1` today — untouched. Confirmed the admin UI a real Administrator would use to flip this exists (`interface/usergroup/user_admin.php`, `interface/usergroup/usergroup_admin.php`). **No account was deactivated by this pass.** |
| **T-9** data and backup deletion | **DESTRUCTIVE — mechanism now specified, NOT executed; one genuine gap found** | `EV-074` (post-contract deletion and backup-handling policy) closed 2026-08-19 with Owner sign-off, giving T-9 a timeframe (D-1: 30 days from T-8) and a defined content spec for its evidence artefact (D-3: what was deleted, from where, date, who). **Finding: no deletion-certificate template file exists yet** — `docs/evidence/templates/` holds only `export-README.txt`. D-3 specifies the required content; no template document has been produced from it. The deletion mechanism itself (`DROP DATABASE` + document-store removal) is standard and already demonstrated, in the reversible "tear down a disposable copy" sense, by `EV-082` §10's own teardown command — **not exercised against the live demo system here**, correctly, since that action is irreversible |

### 6.3 Honest summary

Of the register's three-part acceptance criterion: **the two cross-reference legs are genuinely met**
(§6.1). **The dry-run leg was performed today, across all nine steps** — five (T-1, T-2, T-3, T-4,
T-7) are executable and well-defined; two (T-8, T-9) are destructive and were verified conceptually,
not executed, per this task's own instruction (T-9's mechanism is now specified via `EV-074`, but its
certificate *template* does not yet exist — a genuine, newly-found gap); and **two (T-5, T-6) remain
unchanged, genuine gaps**: no external delivery channel exists, and no external reviewer has confirmed
the package readable. **T-6 in particular cannot be closed by any dry run**, against this system or
any other — it requires an actual customer, or an outside reviewer standing in for one, neither of
which this session has access to.

### Status (2026-08-19): **RDY-0073 — STILL NOT CLOSED.** Both cross-reference criteria are met. The
dry-run criterion is now substantially and honestly exercised (all nine steps walked, five confirmed
executable, two destructive steps conceptually verified with one new gap named), but two legs remain
genuinely unperformed and human/infrastructure-blocked: **T-5** (no external delivery channel) and
**T-6** (no external reviewer). Closing RDY-0073 needs both, not a further internal dry run.

**`Blocks`: G3 G6.** No gate count moved (§0.0 Rule 3) — this entry advances evidence, it does not
close the requirement.
