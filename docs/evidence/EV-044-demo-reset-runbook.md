# EV-044 — DEMO RESET RUNBOOK (RDY-0044-B)

> ### ⚠ BASELINE v2 — updated 2026-08-14 (PB-058)
>
> **The baseline was rebuilt** after the Owner authorised two data fixes: the constructed
> allergy-alert case and the facility/provider identity fields behind the prescription letterhead.
>
> **The previous set is retained on disk with a `SUPERSEDED-` prefix and MUST NOT be restored** — it
> predates both fixes. Every hash and filename below is the **v2** set. If you are holding a copy of
> this runbook citing `e45ad2e7…` or `338b1222…`, it is out of date.

**Baseline:** Marketing MVP Seed v1 · profile `marketing-mvp-seed-v1` · deterministic seed `20260813`
**Status:** PROVEN — two consecutive resets from deliberately-damaged states produced byte-identical
accepted state (PB-045).

---

## 1. The baseline has TWO components. Restoring one is not a reset.

| Component | File | SHA-256 |
|---|---|---|
| **Database** | `thiqa-rdy0044b-v2-baseline-20260814-064532.sql` (71,857,993 bytes, 283 tables) | `4048e65c12d6e1527618719e16b45977aa5fc1dd4204c75225928002dd4002d4` |
| **Document payloads** | `rdy0044b-v2-document-payloads.zip` (7,009 bytes, 10 files) | `c0a8d0dc797e40a89167c01a815044d080e6625e8c9b92e296c3d3133c2abe6e` |

Both live in `C:\openemr-stack\backups\protected\rdy0044b\`, **outside the repository, outside the
retention glob, read-only** (write blocked, verified).

> **Why two.** `mysqldump` captures the `documents` table rows but **not the files they point at** —
> those sit on disk under `sites/<site>/documents/<pid>/`. A database-only restore returns 10
> document *records* pointing at 10 files that are gone. The demo then shows a document list where
> nothing opens. **Always restore both.**

## 2. Reset procedure

```powershell
$mysql = 'C:\openemr-stack\mariadb\bin\mariadb.exe'
$prot  = 'C:\openemr-stack\backups\protected\rdy0044b'
$db    = "$prot\thiqa-rdy0044b-v2-baseline-20260814-064532.sql"
$zip   = "$prot\rdy0044b-v2-document-payloads.zip"
$docs  = 'G:\My Drive\OpenEMR\sites\default\documents'

# 1. Verify both components BEFORE trusting them. Stop on any mismatch.
(Get-FileHash $db  -Algorithm SHA256).Hash.ToLower()   # must be 4048e65c12d6e152...
(Get-FileHash $zip -Algorithm SHA256).Hash.ToLower()   # must be c0a8d0dc797e40a8...

# 2. Stop writes.
Stop-Process -Name httpd -Force

# 3. Restore the database.
& $mysql -u root --host=127.0.0.1 --port=3306 -e `
  "DROP DATABASE IF EXISTS openemr; CREATE DATABASE openemr CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
cmd /c "`"$mysql`" -u root --host=127.0.0.1 --port=3306 openemr < `"$db`""

# 4. Restore the document payloads.
Expand-Archive -LiteralPath $zip -DestinationPath $docs -Force

# 5. Restart Apache. PATH first, or every page 500s (CLAUDE.local.md 4b).
$env:PATH = "C:\openemr-stack\php;$env:PATH"
Start-Process -FilePath 'C:\openemr-stack\apache\bin\httpd.exe' `
  -ArgumentList '-f','C:/openemr-stack/apache/conf/httpd.conf' -WindowStyle Hidden
```

Roughly 20 seconds end to end.

## 3. Post-reset verification — required, not optional

Run the state signature and compare against the accepted value:

```
patients=30 | encounters=72 | appts=36 | recurring=1 | docs=10 | rx=12 | charges=36 | payers=2 |
soap=18 | vitals=12 | eyeexams=8 | allergy_pts=5 | problem_pts=6 | payments=12 | adjustments=4 |
prices=4 | users=10 | aclgroups=7 | aclrules=19 | facility=Thiqa Demo Eye Clinic | globals=495 |
duppairs=2 | cohort=37 | cohort_charged=36 | planted=36/SYN-0019 | createdby0=0 | unattr_forms=0 |
unattr_soap=0 | ev028_ids=0 | ev028_phone=0 | ev028_phrase=0 | ev028_marker=0
```

Query: `scratchpad/state-signature.sql`. **Any difference means the reset did not complete.**

Also confirm on disk: **10** payload files under `sites/default/documents/<pid>/`.

## 4. What is reset, and what is not

| Reset | Not reset |
|---|---|
| All clinical, scheduling, financial and document data | The `admin` credential (rotated, RDY-0017 — lives only in the protected store) |
| Users, roles, ACL groups and grants | Apache / PHP / MariaDB configuration |
| Facility, globals, locale seeds | The protected baselines themselves (read-only) |
| Document payload files | Anything outside the `openemr` schema |
| **Eye-exam edit locks** — cleared by the restore | |

**Credential policy:** the six demo accounts' credentials are part of the baseline and are restored
with it, so they keep working after every reset. They are **never written into this or any other
document** — they live only in `C:\openemr-stack\secrets\thiqa-demo-credentials.json`.

## 5. Audit-trail handling — the decision §16.3 requires, made explicitly

**The audit log is RESET along with everything else**, because it is inside the same schema.

This was a real choice, and the alternative was considered: preserving the log across resets would
keep D-1's material accumulating, but it would then contain entries referring to patients the reset
has removed — which an alert IT gatekeeper would notice, and which would undermine the very demo the
log exists to support. Resetting keeps the log internally consistent with the data.

**Consequence to know before demonstrating D-1:** the log is restored to its baseline size at every
reset, so activity generated during a rehearsal does not persist. If a demo needs a large log, run
D-1 **before** the reset, not after.

## 6. Known state after a reset

- **D-1 audit-integrity returns a clean result** — verified 7,316 bytes, *"No audit log tampering
  detected"*.
- **All six demo accounts authenticate** — verified 6 of 6.
- **Today's flow board is populated** — 16 appointments dated today.
- **The planted missing-charge case is encounter 36 / SYN-0019** — the reconciliation demo's target.
- **PB-030 constraint still applies:** do not demonstrate D-1 over a date range containing an
  API-generated `api_log` row.

## 7. Proof this works (PB-045)

Not asserted — executed:

| Step | Result |
|---|---|
| Accepted state signature recorded | baseline |
| **Perturbation 1** — added a patient, deleted 3 charges and 2 prescriptions, renamed the facility, corrupted an acuity value, **deleted a document file from disk** | signature diverged on 7 fields; payloads 10 → 9 |
| **Reset 1** | signature **identical to accepted**; payloads back to 10 |
| **Perturbation 2** (different damage) — deleted 5 patients, 10 appointments incl. the recurring series, 3 allergies, changed the timezone, corrupted an IOP | signature diverged again |
| **Reset 2** | signature **identical to accepted and to reset 1** |
| Full EV-028 + cohort validation after reset 2 | **all PASS, zero FAIL** |

**accepted == reset#1 == reset#2.** That is the determinism RDY-0044-B requires, demonstrated
against two *different* kinds of damage rather than one repeated no-op.

---

## 8. Proof for the v2 baseline (PB-058)

The v1 proof in §7 stands as the record for the superseded baseline. **The v2 baseline was proven the
same way, independently:**

| Step | Result |
|---|---|
| Accepted v2 signature recorded | baseline |
| **Perturbation 1** — deleted 4 patients, wiped the facility street, **removed both new fixes** (the allergy-alert row and every `users.facility` value) | signature diverged |
| **Reset 1** | **identical to accepted** — and both fixes restored |
| **Perturbation 2**, different damage — deleted 6 prescriptions, reverted the facility phone to the installer placeholder, deleted 8 appointments | diverged again |
| **Reset 2** | **identical to accepted and to reset 1** |
| Full EV-028 + cohort validation | **all PASS, zero FAIL** |
| Document payloads | **10** after each reset |

**`accepted == reset#1 == reset#2`.** Perturbation 1 deliberately removed the two new fixes, so the
proof also demonstrates that **a reset restores them** — they are baseline state, not a manual step.

## 9. Known state after a v2 reset — additions

Beyond §6:

- **The constructed allergy-alert case is present** — `SYN-0002` carries an allergy titled exactly
  `Timolol 0.5% eye drops` and holds the matching active prescription, so **D-7 step 11's alert
  fires**. Distinct allergy patients remain **5**.
- **The prescription letterhead renders** — clinic name, `3100 Fictional Boulevard, Riyadh, Riyadh
  Region 00000` and `+966 11 000 000`, verified on three printed prescriptions.
- **The eight ophthalmology examinations are byte-identical to the set Dr Taha reviewed** — CLINHASH
  `fab7947785d853d04b431932cf5c45ab`, unchanged across the re-seed. **The re-baseline did not touch
  the clinical content that was signed off.**

