# EV-083 — BACKGROUND SERVICE TRIGGER

**Requirement:** RDY-0083 · **Gates:** G2 (disclosure), G3 · **Owner:** DevOps / Infrastructure
**Acceptance (T-18):** *"`next_run` advances for both active services within one interval;
diagnostics shows no overdue active service."*
**Produced:** 2026-08-14 · **Agent B**, Phase 2B · **Host:** native Windows stack (no Docker)

---

## 1. ⚠ FIRST — a current-state correction. The runner has already executed.

**The readiness document asserts, in at least twelve places, that the background-service runner
"has never executed" and that both active services are "stuck at `next_run` = 2021-01-18".
That is false, and has been false since 2026-08-13.**

Measured directly, before any change was made in this entry:

```
$ mariadb -u root -h 127.0.0.1 openemr -e "SELECT name,active,next_run,execute_interval FROM background_services"

Email_Service   active=1  next_run=2026-08-13 13:15:21  interval=2 min
UUID_Service    active=1  next_run=2026-08-13 17:02:38  interval=240 min
X12_SFTP        active=0  next_run=2021-01-18 11:25:10  interval=1 min
MedEx           active=0  next_run=2017-05-09 17:39:10  interval=0
phimail         active=0  next_run=2026-08-07 05:26:06  interval=5 min
```

**Only `X12_SFTP` — which is inactive — still sits at 2021-01-18.** Both *active* services carry
2026-08-13 timestamps.

**Why that proves execution.** `next_run` is written in exactly one place:

```php
// src/Services/Background/BackgroundServiceRunner.php:402
next_run = NOW() + INTERVAL ? MINUTE
```

…inside the claim statement, which runs only when the runner takes a service. Nothing else in the
tree writes the column. Working backwards from the intervals, both services were claimed in a
single window at **≈13:02–13:13 on 2026-08-13** — almost certainly a side-effect of the
authenticated HTTP sessions used for the PB-012/PB-013 authorization acceptance.

**The corrected finding.** The defect was never "the runner cannot execute". It is narrower and
more precise:

> **There is no *recurring* trigger.** The runner works, and has run. It runs only when something
> happens to invoke it, which so far has been an accident of other testing. Both active services
> are currently **overdue**, which is the observable symptom.

This matters beyond bookkeeping: **§40 row 12 instructs the presenter to tell a prospect
"the runner has never been triggered on this build"** — a statement that is now untrue and would
be said aloud in a demo.

---

## 2. The trigger

**Mechanism: the application's own supported CLI**, not a bespoke script.

```
bin/console background:services run
```

`BackgroundServicesCommand` (`src/Common/Command/BackgroundServicesCommand.php`) ships upstream with
`list`, `run`, `unlock` and `crontab` actions. `run` without `--name` runs every service that is
**due**, respecting each service's own interval. **No core file was edited and nothing was
invented** — this is the mechanism upstream provides for exactly this purpose.

### 2.1 Registered scheduled task

```
TaskName:      \OpenEMR-Thiqa-BackgroundServices
Task To Run:   "C:\openemr-stack\php\php.exe" "G:\My Drive\OpenEMR\bin\console" background:services run
Schedule:      every 2 minutes
Run As User:   sakthivelsakthivel89
State:         DISABLED   <-- deliberate; see §4
```

Created with:

```powershell
schtasks /Create /TN "OpenEMR-Thiqa-BackgroundServices" /SC MINUTE /MO 2 /F /TR `
  '"C:\openemr-stack\php\php.exe" "G:\My Drive\OpenEMR\bin\console" background:services run'
schtasks /Change /TN "OpenEMR-Thiqa-BackgroundServices" /DISABLE
```

**Two host-specific decisions, both forced by this environment and both worth recording:**

| Decision | Why |
|---|---|
| **Absolute path to `bin\console`**, not a relative one | `schtasks` sets no working directory (`Start In: N/A`). The first attempt used `bin\console` and would have failed on every tick. Verified: the absolute form runs correctly from an unrelated cwd (`cd C:\openemr-stack` → `background:services list` returns the table) |
| **Runs as the logged-on user, not as `SYSTEM`** | Google Drive mounts `G:` **per user session**. A task running as `LocalSystem` cannot see the application directory at all. This is the same constraint that forces Apache and MariaDB to run as session processes, and it means **the trigger does not survive a logoff** — it is correct for this host and **must not be copied into the pilot runbook unchanged** (RDY-0047) |

**Tick interval is 2 minutes because that is the shortest active interval** (`Email_Service`).
A longer tick would mean `Email_Service` advances later than its own interval, which fails T-18's
"within one interval" wording. `UUID_Service` at 240 minutes is unaffected.

---

## 3. Proof the trigger works — executed, with a negative control

| # | Step | Result |
|---|---|---|
| 0 | Pre-change safety snapshot | `C:/openemr-stack/backups/pre-rdy0083-20260814-030150.sql`, 79,417,080 B, 283 `CREATE TABLE`, SHA-256 `be2182a9ecf3f055…` |
| 1 | `background:services run --name Email_Service --force` | **`[OK] Service 'Email_Service' executed successfully.`** `next_run` **2026-08-13 13:15:21 → 2026-08-14 03:04:03**; `running` returned to `0` (lock released cleanly) |
| 2 | **Negative control** — task run at 03:03:28, while `Email_Service.next_run` was 03:04:03 (*not yet due*) | Task `Last Result: 0`, and **`next_run` did not move.** The runner correctly declined a service that was not due. A trigger that fired regardless of interval would have passed step 3 vacuously |
| 3 | Task run at 03:04:26, when `Email_Service` **was** due | **`next_run` 03:04:03 → 03:06:26** — advanced by exactly the 2-minute interval, **through the Windows Scheduled Task**, not by a direct invocation |
| 4 | Collateral check after every step | `patient_data` 30 · `form_encounter` 72 · appointments 37 · `billing` 36 · `globals` 495 — **all unchanged** |

**The chain is proven end-to-end:** Scheduled Task → `php.exe` → `bin/console` →
`BackgroundServiceRunner` → `next_run` advanced → lock released.

**Step 2 is the leg that makes step 3 mean anything.** Without it, "the task ran and the timestamp
moved" would not distinguish a working interval check from a trigger that simply runs everything
every time.

---

## 4. ⚠ Why the task is registered DISABLED — and the decision that is needed

**Enabling it would write into the RDY-0044-B demo baseline, which two named humans signed off.**

`UUID_Service` runs `UuidRegistry::populateAllMissingUuids()`, which writes UUIDs across ~25 tables
including `patient_data`, `form_encounter`, `lists`, `documents` and
`openemr_postcalendar_events`. It is **overdue**, so it fires on the first enabled tick.

Measured, read-only:

| Table | Rows | Missing `uuid` |
|---|---:|---:|
| `form_vitals` | 12 | **12** |
| `insurance_companies` | 2 | **1** |
| `patient_data`, `form_encounter`, `lists`, `documents`, appointments, `prescriptions`, `users`, `facility` | — | **0** |

**13 rows would be written on the first tick.**

### 4.1 This is a seed defect, and it was found by trying to close a different requirement

Both gaps trace to the documented raw-SQL exceptions in the seeder — `form_vitals` because
`VitalsService::create()` is an empty stub in this release, and the payer row similarly. Those paths
bypass the service layer that would otherwise mint a UUID. **The rows are not wrong; they are
incomplete**, and the FHIR/API surface needs those UUIDs.

### 4.2 The interaction with RDY-0044-B, stated precisely

- RDY-0044-B's acceptance is *"a second reset produces identical **counts**"*. Row counts do not
  change when a UUID is populated, so **this does not invalidate that closure.**
- But the dataset is byte-stable only until the runner fires. After a reset the 13 rows are NULL
  again, and the next tick repopulates them with **different random UUIDs**. The baseline is
  reproducible in counts, **not** in bytes.

### 4.3 The decision — Owner's, not the engineer's

| Option | Consequence |
|---|---|
| **A. Populate the 13 UUIDs, then re-baseline RDY-0044-B** | Correct and permanent. Costs one re-baseline of an artefact signed off by Dr Mohamed Taha and Mohammed Elfouly. **Their sign-off is clinical and legal content, which UUIDs do not touch**, so a re-baseline should not require re-review — but that is the Owner's call to state, not mine to assume |
| **B. Fix the seeder to mint UUIDs, re-seed, re-baseline** | Cleanest root-cause fix; largest blast radius; re-runs the whole dataset |
| **C. Enable the trigger and accept the drift** | RDY-0083 closes immediately; the baseline silently stops being byte-reproducible |

**Recommendation: A.** It is the smallest change that makes both requirements true at once.

**Nothing was applied.** This is precisely the situation PB-046 set the precedent for — *"quietly
re-seeding an accepted artefact to fix a demo step is exactly the kind of churn the closure contract
exists to prevent."* The same reasoning applies to mutating it to close RDY-0083.

---

## 5. Acceptance against T-18

| Criterion | Result |
|---|---|
| A recurring trigger exists | **MET** — registered Windows Scheduled Task, 2-minute tick, absolute paths verified |
| The trigger actually invokes the runner | **MET** — proven through the task itself (§3 step 3), with a negative control (step 2) |
| `next_run` advances for **`Email_Service`** within one interval | **MET** — 03:04:03 → 03:06:26 |
| `next_run` advances for **`UUID_Service`** within one interval | **NOT MET — deliberately not attempted.** Blocked on the §4.3 decision |
| Diagnostics shows no overdue active service | **NOT MET** — `UUID_Service` is still overdue, and will remain so until the trigger is enabled |

### Status: **RDY-0083 — NOT CLOSED. Trigger built, proven, and held disabled pending one Owner decision.**

**`Blocks`: G2 (disclosure), G3.** No gate count is moved (§0.0 Rule 3).

---

## 6. To enable, once §4.3 is decided

```powershell
# Option A first — populate the 13 rows, then re-baseline RDY-0044-B per EV-044.
& "C:\openemr-stack\php\php.exe" "G:\My Drive\OpenEMR\bin\console" background:services run --name UUID_Service --force

# Then enable the recurring trigger:
schtasks /Change /TN "OpenEMR-Thiqa-BackgroundServices" /ENABLE

# Verify within ~4 minutes:
& "C:\openemr-stack\php\php.exe" "G:\My Drive\OpenEMR\bin\console" background:services list
#   expect: no active service with next_run in the past
```

**Rollback:** `schtasks /Change /TN "OpenEMR-Thiqa-BackgroundServices" /DISABLE`, or
`schtasks /Delete /TN "OpenEMR-Thiqa-BackgroundServices" /F`. Database rollback for the whole entry:
`C:/openemr-stack/backups/pre-rdy0083-20260814-030150.sql`.

---

## 7. Outstanding: the stale assertions in the readiness document

The correction in §1 has **not** been swept through the document — that is a high-collision
whole-file edit while another agent is working in it. The assertions needing correction are at
§3.6, §3.9 (OD-03), §4.4 (GTM-006), §7.15 (RDY-0083), §7.21, §22.1, §40 row 12, §45.2, §47 (G3),
§48.B and *PHASE 2 IN 20 LINES* item 12.

**§40 row 12 is the urgent one** — it scripts a presenter to say something false to a prospect.
