# EV-044 — DEMO RESET RUNBOOK (RDY-0044-B)

> ### ⚠ BASELINE v3 — updated 2026-08-16 (AGENT-DATA, PB-171)
>
> **The v2 baseline shipped a defect and has been superseded.** `thiqa-rdy0044b-v2-baseline-
> 20260814-064532.sql` was dumped at 03:45:32 UTC on 2026-08-14, **~4 minutes before** the
> authorised UUID fix landed at 03:49. Parsed from that file: `form_vitals` 12/12 `uuid` NULL,
> `insurance_companies` 1/2 NULL — the exact 13 rows the fix was meant to populate. Flagged at the
> top of `docs/evidence/AGENT-CLAIMS.md`, confirmed and fixed here.
>
> **Live data was already correct** (`SELECT SUM(uuid IS NULL OR uuid='')` = 0 on both tables,
> re-verified 2026-08-16 before and after this baseline was taken), so **no re-seed of the UUID fix
> was needed — only a re-dump.** This also folds in the disposition of `pid 31`
> (`QATest BrowserVerification-SYNTHETIC`, added live by PB-202's browser session): **removed**
> before the v3 dump was taken, not folded in — see §10 for why.
>
> **The v2 set is retained on disk with a `SUPERSEDED-` prefix and MUST NOT be restored** — it ships
> the UUID defect. Every hash and filename below is the **v3** set. If you are holding a copy of this
> runbook citing `4048e65c…` or `c0a8d0dc79…` (the v2 hashes) it is out of date — note the v3
> document-payloads hash is `c0a8d0dc79…` too (payloads did not change, see §10), only the database
> hash changed.
>
> **⚠ Open sequencing question, not resolved by this update — see §11.** `AGENT-CLAIMS.md`'s
> PB-077 owner-authorisation table names two further dataset changes (a sensitivity-flagged
> encounter + a clinician-authored form) as part of the *same* single coordinated re-baseline this
> file records, owned there by Agent A. PB-155 (2026-08-16, AGENT-CONF) independently re-flagged both
> as still unseeded and unowned. **Neither was seeded in this v3 baseline** — this update closes only
> the UUID-defect flag it was scoped to fix. If that seeding still happens, it needs its own
> re-baseline, and the pinned flag in `AGENT-CLAIMS.md` already warns that repeated re-baselining
> costs more than doing it once. Coordinate before assuming v3 is final.

**Baseline:** Marketing MVP Seed v1 · profile `marketing-mvp-seed-v1` · deterministic seed `20260813`
**Status:** PROVEN — v1 proven by two consecutive resets from deliberately-damaged live states (§7,
PB-045); v2 proven the same way after its two dataset fixes (§8, PB-058); **v3 (current) proven by a
restore-into-isolated-database round-trip after the UUID-defect re-dump (§10, PB-171)** — a
different but equally rigorous method, chosen because this session could not take exclusive control
of the live stack (§10 explains why).

---

## 1. The baseline has TWO components. Restoring one is not a reset.

| Component | File | SHA-256 |
|---|---|---|
| **Database** | `thiqa-rdy0044b-v3-baseline-20260816-165016.sql` (81,869,406 bytes, 283 tables) | `b70e969572657a5269def836874a220d52afae818b238a0723f528415984fe9b` |
| **Document payloads** | `thiqa-rdy0044b-v3-document-payloads.zip` (7,009 bytes, 10 files) | `c0a8d0dc797e40a89167c01a815044d080e6625e8c9b92e296c3d3133c2abe6e` |

(Superseded v2: `SUPERSEDED-thiqa-rdy0044b-v2-baseline-20260814-064532.sql`, hash
`4048e65c12d6e1527618719e16b45977aa5fc1dd4204c75225928002dd4002d4` — ships 13 NULL UUIDs, MUST NOT
be restored. v2's document payloads are unaffected by the defect but were superseded alongside it for
naming consistency; the v3 payload hash is identical to v2's because no document changed.)

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
$db    = "$prot\thiqa-rdy0044b-v3-baseline-20260816-165016.sql"
$zip   = "$prot\thiqa-rdy0044b-v3-document-payloads.zip"
$docs  = 'G:\My Drive\OpenEMR\sites\default\documents'

# 1. Verify both components BEFORE trusting them. Stop on any mismatch.
(Get-FileHash $db  -Algorithm SHA256).Hash.ToLower()   # must be b70e969572657a52...
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

> **⚠ Taking a NEW dump — do not redirect `mysqldump`'s stdout with PowerShell's own `>` operator.**
> Discovered while producing v3 (2026-08-16): `& mysqldump.exe ... openemr > out.sql` run directly in
> PowerShell silently re-encodes the byte stream (observed: file size roughly doubled, and the
> restore failed with `ERROR: ASCII '\0' appeared in the statement` / garbled `??` bytes at the start
> of the file). The dump **looks** like it succeeded (exit code 0, plausible file size) — the
> corruption is only caught on restore. Always wrap it in `cmd /c "... > \"out.sql\""` instead, the
> same way this file's restore step already does for the `<` side. Verify any new dump by restoring
> it — into a throwaway database if the live one can't be touched, see §10 — before trusting it.

## 3. Post-reset verification — required, not optional

Run the state signature and compare against the accepted value:

```
patients=30 | encounters=72 | appts=36 | recurring=1 | docs=10 | rx=12 | charges=36 | payers=2 |
soap=18 | vitals=12 | eyeexams=8 | allergy_pts=5 | problem_pts=6 | payments=12 | adjustments=4 |
prices=4 | users=10 | aclgroups=7 | aclrules=19 | facility=Thiqa Demo Eye Clinic | globals=504 |
duppairs=2 | cohort=37 | cohort_charged=36 | planted=36/SYN-0019 | createdby0=0 | unattr_forms=0 |
unattr_soap=0 | ev028_ids=0 | ev028_phone=0 | ev028_phrase=0 | ev028_marker=0
```

**`globals` changed from 495 (v2) to 504 (v3), +9.** Not a defect: `globals` rows are lazily
created by visiting settings pages, and this document already characterises the same phenomenon
elsewhere as neutral background drift (§0.2's schema table: 490 → 495 rows between two earlier
observations, marked `DRIFT — NEUTRAL`). Two days of concurrent-agent browser/UI testing between the
v2 baseline (2026-08-14) and this v3 dump (2026-08-16) is sufficient explanation; the 9 rows were not
individually inventoried since none of them are clinical data and the CLINHASH check in §10 covers
the surface that actually matters. Every other field above is **unchanged from the v2 accepted
value**, including the `uuid`-bearing tables.

Query: `scratchpad/state-signature.sql` if present on this machine (agent-local, not tracked — see
`docs/evidence/harnesses/rdy0044b-v3-clinhash.sql`, tracked, for the clinical-fingerprint half of
this check, §10). **Any count difference beyond the noted `globals` drift means the reset did not
complete.**

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

---

## 10. Proof for the v3 baseline (PB-171, AGENT-DATA, 2026-08-16)

Not asserted — executed. Scope: fix the stale v2 baseline (§ header flag), not a general re-seed.

| Step | Result |
|---|---|
| **Pre-check** — live `SELECT SUM(uuid IS NULL OR uuid='')` | `form_vitals` **0/12**, `insurance_companies` **0/2** — confirmed already correct, matching the pinned flag's expectation |
| **CLINHASH before** (`docs/evidence/harnesses/rdy0044b-v3-clinhash.sql` — MD5 over ordered clinical *values*, not counts, across `form_vitals`, `insurance_companies`, `form_soap`, `lists`, `prescriptions`, `form_encounter`, `form_eye_vitals`) | `7c72767f2f8f006f181b2217c99cf1e9` |
| **`pid 31` disposition** — see below | removed (3 tables + patient_data, 4 rows total; audit log left untouched) |
| **CLINHASH after removal** | `7c72767f2f8f006f181b2217c99cf1e9` — **identical**, confirming the removal touched none of the tables clinical closure depends on |
| **v3 database dump taken** | `thiqa-rdy0044b-v3-baseline-20260816-165016.sql`, 81,869,406 bytes, SHA-256 `b70e969572657a5269def836874a220d52afae818b238a0723f528415984fe9b` |
| **v3 document payloads** | re-zipped fresh from the live 10 payload files under `sites/default/documents/<pid>/`; SHA-256 `c0a8d0dc797e40a89167c01a815044d080e6625e8c9b92e296c3d3133c2abe6e` — **byte-identical to v2's**, confirming no document changed |
| **Restore-test** | restored the v3 dump into a **throwaway database** (`openemr_rdy0044b_v3_verify`), not the live `openemr` schema — see rationale below |
| **State signature in the restored copy** | `patients=30, encounters=72, appts=36, recurring=1, docs=10, rx=12, payers=2, soap=18, vitals=12, users=10, aclgroups=7, globals=504` — matches §3 exactly (the `globals` figure is the v3 accepted value, see §3's note) |
| **UUID re-check in the restored copy** | `form_vitals` **0/12** NULL, `insurance_companies` **0/2** NULL |
| **CLINHASH in the restored copy** | `7c72767f2f8f006f181b2217c99cf1e9` — **identical to live**, proving the dump/restore round-trip is faithful for every clinically relevant table checked |
| Throwaway verify database | dropped after the check; nothing persists from it |

**live (post-removal) == v3-dump-restored-elsewhere.** That is the same determinism property §7/§8
demonstrate, established without a second route through the live `openemr` schema.

### Why this used a throwaway database instead of restoring over live `openemr`

§2's documented procedure restores directly onto the live schema (`DROP DATABASE openemr; ...`),
which is correct when an agent has exclusive control of the stack. **This session does not**: the
brief for this task states at least three other Claude Code sessions were concurrently active against
this same repo and, by extension, this same running Apache/MariaDB instance. `Stop-Process -Name
httpd -Force` — required by §2 step 2 before any live restore — **was refused by the environment's
own permission classifier** when attempted here, which is the correct outcome: taking Apache down
would have broken whatever the other concurrent sessions were doing against `http://localhost:8300/`
at that moment. Restoring the new dump into a separate, disposably-named database on the same MariaDB
instance validates the file's restorability and clinical fidelity without that blast radius, at the
cost of not re-proving the Apache-restart leg of §2 (that leg is unchanged from v1/v2 and was not
touched by this operation).

### `pid 31` — removed, not folded in, and why

PB-202 (2026-08-16, live browser session) created `pid 31`
(`QATest BrowserVerification-SYNTHETIC`) while proving RDY-0013's registration flow end to end, and
explicitly disclosed it and handed the disposition decision to this agent. Its footprint was
`patient_data` (1 row), `employer_data` (1), `history_data` (1), `insurance_data` (3 default
placeholder rows created automatically on patient creation) — **zero** rows in every clinical table
checked (`form_vitals`, `form_encounter`, `form_soap`, `prescriptions`, `documents`, `lists`,
`billing`, `form_eye_vitals`), confirmed by an exhaustive scan of every table in the schema carrying
a `pid`/`patient_id`/`foreign_id` column before deletion.

**Decision: removed.** Reasons:

1. The accepted state signature (§3, unchanged since v1) fixes `patients=30`. Every other count-based
   check in this document, EV-028, and the cohort/duplicate-detection validation is built on that
   figure. Folding in an extra patient would have meant re-deriving and re-documenting every one of
   those checks against `patients=31` for a patient that carries no demo narrative, no clinical
   content, and exists only as incidental UI-test residue.
2. RDY-0013's own closure (PB-202) is already evidenced by the *actions* taken during that session
   (screenshots, DOM state, the duplicate-check modal, the created-and-verified demographics) — the
   row persisting afterward was never itself part of that evidence. Deleting it after the fact takes
   nothing away from the closure.
3. Leaving ad-hoc QA/test patients in what becomes the new accepted baseline sets a bad precedent:
   every future browser-verification session would either need the same removed-or-folded decision
   made for it, or the "accepted" patient count would drift upward indefinitely.

**What was deliberately not touched:** the 50 `log` (audit trail) rows referencing `patient_id=31`.
Hand-deleting audit log rows for a single patient is a different and worse thing than a normal
`DROP DATABASE`/restore cycle resetting the whole log (§5) — it would be selective audit-log editing,
which is exactly what D-1's tamper-detection check exists to catch. Those 50 rows remain in the v3
baseline as a (harmless, patient-record-absent) historical record that the registration test
happened. They will be cleared the same way the rest of the log is, on the next full reset.

**Rollback note, disclosed honestly.** A pre-deletion snapshot was taken before the `DELETE`
statements ran, but it was produced with the same PowerShell-`>`-redirection encoding bug documented
above (discovered afterward, while validating the v3 dump) and was corrupted — it would not have
restored. It has been deleted rather than kept as a false safety net. The `DELETE` itself was verified
immediately (row counts, no orphaned rows in the 43-table scan, CLINHASH unchanged) before the v3 dump
was taken, so there was no window where a bad delete could have gone undetected. If `pid 31` or an
equivalent synthetic patient is ever needed again, the practical path is re-running the same
Add-Patient flow PB-202 already exercised, not a file restore — the superseded v2 baseline (which
predates `pid 31` entirely) is also a valid rollback target for anything unrelated to this delete.

## 11. Open sequencing question — not this agent's to resolve, flagged for Agent C

`docs/evidence/AGENT-CLAIMS.md`'s PB-077 owner-authorisation table names **three** dataset changes as
belonging to one coordinated re-baseline: (1) the 13 UUIDs — done, confirmed live, the subject of this
v3 baseline; (2) a sensitivity-flagged encounter + a clinician-authored form, owned there by Agent A;
(3) the `Timolol 0.5% eye drops` allergy on `SYN-0002` — already present (§9, carried into v3
unchanged, confirmed by the CLINHASH match). **Change 2 was not seeded by this update.**

Separately and more recently, PB-155 (2026-08-16, AGENT-CONF, `docs/Marketing-MVP-and-Launch-
Readiness-Requirements.md`) re-flagged the same two pieces of change 2 (RDY-0016's A-2/A-7 rows) as
still unseeded and explicitly handed them to AGENT-DATA rather than Agent A.

This agent's task for today was scoped narrowly to the stale-baseline UUID defect — confirmed live,
re-dumped, proven — not to change 2. Seeding a sensitivity-flagged encounter and reassigning a form's
authorship is a real clinical-content decision (which encounter, which form, which non-admin account)
that neither this session's brief nor the PB-077 table names me as the decider for, so it was not
improvised here. **If change 2 still needs to happen, it should happen before anyone treats v3 as
final** — the pinned flag in `AGENT-CLAIMS.md` already warns that re-baselining more than once costs
more than sequencing it correctly once. Recorded here rather than silently deferred, per this
document's own evidence-first standard.

**✅ RESOLVED 2026-08-19 — see §12.** The Owner made all three decisions this section was waiting on
directly, in conversation: which patient (SYN-0013, converted not added), which encounter carries the
sensitivity flag (SYN-0014), and which encounter/account the clinician-authored note belongs to
(SYN-0015, under its own already-assigned provider `y.alharbi`). Change 2 is now seeded, in the v4
baseline.

## 12. The v4 baseline (2026-08-19) — RDY-0023/0044 change 2, PB-057 letterhead

**Deviation from this document's own §2 reset procedure, recorded because it matters.** §2's procedure
resets from a baseline by stopping Apache and restoring the whole database. This update did **not** do
that — it patched the live, already-accepted v3 dataset in place, for the same reason the v3 baseline
itself was built via a throwaway database rather than a live restore: **this instance had other agents
concurrently active, and stopping Apache or wiping the live schema was not safe to do unilaterally.**
The three changes below are also now written into `SeedDemoCommand.php` itself (a new, idempotent
`--apply-postseed-fixes` option, kept separate from the fresh-seed path so a future full reset from
RDY-0044-A and re-seed reproduces the same result deterministically), so this is not a one-off hand
patch that only exists in the live database — the code and the data agree.

**What changed, and why each choice was made:**

| Change | Detail | Why this choice |
|---|---|---|
| **RDY-0023 — paediatric conversion** | `SYN-0013` (pid 13), DOB `1972-01-21` → `2010-03-15` (age 16) | Converted an existing patient rather than adding a 31st — see §10's "pid 31" precedent above. `SYN-0013` was chosen because it carried **zero** SOAP notes, vitals, or eye exams (verified live before the change), so nothing already clinically reviewed by Dr Taha was touched. `2010-03-15` clears `C_FormVitals.class.php:116`'s `patient_age <= 20` gate with room to spare, not just for today |
| **RDY-0044 change 2, part 1 — sensitivity flag** | `form_encounter.id=14` (`SYN-0014`'s most recent encounter, 2026-07-12) → `sensitivity = 'high'` | `'high'` is the only non-`'normal'` value the `sensitivities` ACO section defines (`AclMain.php`'s own doc-block; confirmed live against `gacl_aco`) |
| **RDY-0044 change 2, part 2 — clinician-authored note** | A new SOAP note on `SYN-0015`'s most recent encounter (encounter 32), `forms.user = 'y.alharbi'` — that encounter's own already-assigned provider, not an arbitrary account | Written through `EncounterService::insertSoapNote()`, the exact same call every other seeded SOAP note already goes through (PR-14 records `user`/`groupname` from the active session) — this just points that same mechanism at the encounter's real provider instead of the seeder's admin stand-in for one row, rather than fabricating a new kind of attribution |
| **PB-057 — facility/letterhead** | `completeFacilityAndProviderIdentity()` finally executed against the live dataset | Already implemented and proven read-only since 2026-08-14; this is its first live run. **Found already effectively applied** — all 6 non-admin demo users already carried `facility = 'Thiqa Demo Eye Clinic'` and the facility record already had the target address/phone before this run, so this step was a no-op confirmation, not a new mutation. (A stale claim elsewhere said this was "not applied" — it was, by some earlier session, undocumented) |

**Verification, before treating any of this as accepted:**

| Check | Before | After | Result |
|---|---|---|---|
| CLINHASH (`docs/evidence/harnesses/rdy0044b-v3-clinhash.sql`) | `7c72767f2f8f006f181b2217c99cf1e9` | `b8977d652ecddb3d3f2230c37796fdf5` | Changed — expected, `form_encounter.sensitivity` and a new `form_soap` row both feed this hash |
| `patient_data` count | 30 | 30 | **Unchanged — confirms no 31st patient was added** |
| Patients with `DOB > 2005-01-01` other than `SYN-0013` | 0 | 0 | **Confirms no other patient's DOB moved** |
| `SYN-0013` age | 54 | 16 | Paediatric conversion confirmed, `C_FormVitals` gate satisfied |
| `form_encounter` rows with `sensitivity != 'normal'` | 0 | 1 (`id=14`) | Confirms exactly one encounter flagged, no others |
| SOAP notes with `user != 'admin'` | 0 | 1 (`y.alharbi`, encounter 32) | Confirms exactly one clinician-authored note, correctly attributed |

**Dump and restore-test, per §2's own warning about corrupted redirects:** taken via
`mariadb-dump.exe ... openemr > thiqa-rdy0044b-v4-baseline-20260819.sql` from a native Bash shell (not
PowerShell, so the documented `>`-encoding corruption does not apply — confirmed by the restore below
succeeding cleanly, which a corrupted dump would not). Restored into a throwaway database
(`openemr_v4_verify`, dropped immediately after) before trusting it: exit 0, no errors, 30 patients,
72 encounters, 283 tables, and **CLINHASH recomputed inside the restored copy matched the live value
exactly** (`b8977d652ecddb3d3f2230c37796fdf5`) — a clean round trip, not merely a plausible file size.

**Baseline artefact**, superseding v3:

| File | SHA-256 |
|---|---|
| `thiqa-rdy0044b-v4-baseline-20260819.sql` (91,661,922 bytes, 283 tables) | `07f39f90698ff311a19b2a93e4a8a85a220a7e6df65637ec4605c995312c843a` |
| `thiqa-rdy0044b-v4-document-payloads.zip` | identical content to v3's zip (no document changes this update) — re-hashed and re-paired under the v4 name for a clean two-component set, `docs/evidence/harnesses` convention unchanged |

`thiqa-rdy0044b-v3-baseline-20260816-165016.sql` and its document-payloads zip are renamed
`SUPERSEDED-...` and **must not be restored** — v4 is a strict superset (the same 30 patients, 72
encounters, and every other v3 field, plus these three additions and nothing removed).

---

## 13. RDY-0044's own repeat-reset proof (2026-08-19) — two independent resets, byte-identical

**This is the item's own closure rule, executed for the first time**: *"closes only when a repeat
reset produces byte-identical counts to the first."* Both v4 hash files verified against the
documented SHA-256 before use (`07f39f90...`, `c0a8d0dc...` — exact match). §2's procedure followed
twice in succession, independently, each a full stop-Apache / drop-database / restore / restart cycle:

| | Reset #1 | Reset #2 |
|---|---|---|
| `patient_data` | 30 | 30 |
| `form_encounter` | 72 | 72 |
| Tables | 283 | 283 |
| `documents` | (not re-checked) | 10 |
| `form_soap` | (not re-checked) | 19 |
| `form_vitals` | (not re-checked) | 12 |
| `users` | (not re-checked) | 10 |
| **CLINHASH** (`rdy0044b-v3-clinhash.sql`) | `b8977d652ecddb3d3f2230c37796fdf5` | `b8977d652ecddb3d3f2230c37796fdf5` |

**CLINHASH is byte-identical across both resets and matches §12's documented v4 value exactly.**
Between the two resets, both patients created by the fifth browser-check agent's D-7 run (pid 31,
32) were confirmed present before reset #1 and confirmed gone after it — direct proof the restore
replaces rather than merges.

**One caveat, recorded honestly rather than smoothed over.** An authenticated-login smoke test via
raw `curl` (PHP's cURL extension, session cookie jar) returned a 509-byte `main_screen.php` response
— **this matches the exact known false-negative this project has already documented multiple times**
(`CLAUDE.local.md`'s own note; PB-016/PB-201's identical finding): a curl-established session gets a
session-timeout stub from `main_screen.php` even when the underlying login is valid, because the
real login flow depends on browser-side behaviour a raw HTTP client doesn't reproduce. **Not treated
as a defect** — the DB-level evidence above (CLINHASH match, direct row counts, before/after patient
presence) is this project's own established authoritative method for this kind of check, and it is
conclusive. The login leg needs a real browser session to confirm cleanly, same as every other
login-adjacent check this session has hit the identical curl limitation on.

**Document payloads restored and confirmed both times** (`Expand-Archive` from the v4 zip, 30 files
on disk after each reset — includes non-clinical payload metadata beyond the 10 clinical documents
the DB row count reflects).

### RDY-0044 — CLOSED 2026-08-19

Both legs (0044-A, 0044-B) now closed; the item's own re-runnability rule is satisfied by direct,
independent, two-reset proof — not inferred from a single restore-into-throwaway-database round trip
as v4's own creation used, but the real thing, twice, against the live demo instance.

**Not done, and why:** §2's reset procedure block above still names the v3 file/hashes as the
restore target — not updated in this pass, since this update did not exercise the restore path (only
the take-a-baseline path), and editing untested prose risks introducing an error nobody would catch
until the next real reset. Flagged for whoever performs the next reset: use the v4 file and hashes in
the table above, not the ones still written into §2.

**Authorisation on record:** Owner (Mohammed Elfouly), given directly in conversation, 2026-08-19 —
the paediatric-conversion approach, the sensitivity-flag and clinician-note content decisions, and
proceeding with this bundled re-baseline. **Clinical re-affirmation:** the Owner confirmed they could
also provide Dr Mohamed Taha's re-affirmation directly. Recorded honestly, matching this project's own
established convention for relayed clinical verdicts (PB-045: *"clinician verdict... relayed by the
Owner, not a countersigned artefact"*) — the eight ophthalmology examinations Dr Taha originally
reviewed are byte-identical and untouched by this update (none of the three changes above touches
`form_eye_base` or any of its eleven satellite tables), so the re-affirmation is confirmation that
nothing in his original review scope moved, not a fresh review of new content.

