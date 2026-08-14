# EV-047 — DEPLOYMENT RUNBOOK: PROVISIONING A FRESH CLINIC INSTANCE

**Requirement:** RDY-0047 · **Gates:** G3, G6 · **Owner:** DevOps / Infrastructure
**Acceptance:** *"A person who did not write the runbook provisions a fresh instance by following it,
**without asking a question that the runbook does not answer**; the resulting instance passes a
defined smoke test."*
**Issued:** 2026-08-14 · **Agent B**, Phase 2B

---

## 0. Read this first

**This runbook has never been executed by anyone.** It is written from the provisioning knowledge
accumulated across Phase 2B, not from a witnessed provisioning. **Its acceptance criterion is
specifically that someone else follows it and does not have to ask a question it fails to answer** —
so the first execution is the test, and every question the follower has to ask is a defect in this
document.

**Log those questions.** §9 has a place for them. A runbook that produced no questions on first use
is more likely under-tested than perfect.

### 0.1 What this does not cover

| Not covered | Why | Tracked as |
|---|---|---|
| **Where the instance runs** | Hosting region decided (Dammam), **provisioning blocked — EXTERNAL** | RDY-0064 |
| **TLS, domain, DNS** | Downstream of hosting; the demo instance is HTTP only | RDY-0085 |
| **Off-instance backup target** | Does not exist until hosting does | RDY-0081 |
| **Migrating a clinic's existing data** | Quoted after inspection, never fixed-price sight-unseen | RDY-0066 |

**A customer instance is provisioned fresh from this runbook. It is never an upgrade of the demo
instance** — §27.1, and there is no path between them.

---

## 1. Environment separation — the gap this runbook must close

RDY-0047's own statement of the problem: *"environment-specific configuration is **not** separated
from code."* Two concrete instances, both of which this runbook fixes at provisioning:

| Item | Problem | Handled at |
|---|---|---|
| Database credentials in `sites/default/sqlconf.php` | Git-tracked file; **on the demo instance the password is still the upstream default `openemr`** (`EV-048`) | **Step 4** — a unique password is generated per instance |
| OS-specific commands in `sites/default/config.php` | `lpr`, `enscript`, `/usr/bin/file` — Unix paths on a Windows host (OD-04) | **Step 7** |

**The rule for this runbook:** every value that differs between two instances is set in **Step 4** or
**Step 7** and nowhere else. If you find yourself editing a file under `src/`, `library/` or
`interface/` to provision, **stop — that is a defect, record it in §9.**

---

## 2. Prerequisites

| # | Item | Notes |
|---|---|---|
| P-1 | Host meeting the target profile | Windows Server, PHP 8.3.x, MariaDB 11.x, Apache 2.4 |
| P-2 | **PHP and Apache built with the same MSVC toolchain** | `mod_php` requires it. Mismatched builds fail at load, not at install |
| P-3 | The 33 PHP extensions `composer.json` requires | Includes `imagick` and `redis`, which are not bundled on Windows |
| P-4 | A password manager or secret store | For Step 4. **Never a text file in the repository** |
| P-5 | The application source at a known commit | Record the SHA in §8 |

---

## 3. Step 1 — Stack

1. Install PHP, Apache and MariaDB per P-1/P-2.
2. **Put PHP's own directory on `PATH` before Apache starts.** `mod_php` runs inside the Apache
   process, so PHP's directory is not on the DLL search path; without it `openssl`, `curl`, `intl`,
   `ldap`, `sodium` and `imagick` silently fail to load and **every page returns HTTP 500** with
   `Unable to load dynamic library` in the PHP error log.
3. Set `memory_limit = 512M`, `max_execution_time = 300`, `max_input_vars = 3000`,
   `post_max_size` / `upload_max_filesize = 100M`.
4. Point `error_log` at a known path. **This is the first place to look on any 500.**

**Verify before continuing:** `php -m` lists all 33 extensions, under **both** CLI and the web SAPI.
They can differ.

---

## 4. Step 2 — Database

```sql
CREATE DATABASE <db> CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER '<user>'@'127.0.0.1' IDENTIFIED BY '<generated-password>';
GRANT ALL PRIVILEGES ON `<db>`.* TO '<user>'@'127.0.0.1';
```

**Bind MariaDB to `127.0.0.1`.** **Grant on that one schema only** — not `*.*`. This is not
box-ticking: PB-007's restore test failed its first attempt because the app user could not
`CREATE DATABASE`, which is **correct least privilege** and is cited as evidence in the security
pitch. Do not widen it.

---

## 5. Step 3 — Application install

Run the CLI installer with the values from Step 4. **`server=127.0.0.1`, not `localhost`** —
`localhost` may resolve to `::1` first, which a loopback-bound MariaDB refuses.

Confirm afterwards: 283 tables, and `sites/<site>/sqlconf.php` now has `$config = 1`.

---

## 6. Step 4 — Secrets ⚠ mandatory, and the demo instance gets this wrong

**Generate a unique database password for every instance.** Never reuse, never share between
instances, and **never leave the upstream default `openemr`** — which is what the demo instance still
runs (`EV-048` §1.2). It ships in every OpenEMR clone in the world.

| Rule | Detail |
|---|---|
| Generation | CSPRNG, ≥ 20 characters, mixed classes |
| Storage | The secret store from P-4. **Outside the repository** — "outside" is a hard boundary, not a `.gitignore` promise |
| In the repository | **Nothing.** `sqlconf.php` on a customer instance is generated here, never taken from git |
| `skip-worktree` | **Not a security control.** It lives in one developer's local index, does not travel with a clone, and is invisible to review. Do not rely on it |

**This step satisfies RDY-0048's third criterion** (*"the runbook contains the handling"*).

---

## 7. Step 5 — Application configuration

Set at provisioning, per instance:

| Setting | Value |
|---|---|
| `gbl_time_zone` | The clinic's zone (`Asia/Riyadh` for Saudi) |
| `gbl_currency_symbol` | `SAR`. **Display only — there is no ISO 4217 field and no currency column. Multi-currency must never be claimed** |
| `phone_country_code`, `units_of_measurement` | `966`, metric |
| `mysql_bin_dir` | The **real** MariaDB `bin` directory. The upstream Windows default points at an absent XAMPP path and silently breaks backup (RDY-0080) |
| `openemr_name`, `login_tagline_text`, logo/support/manual links | Per the brand |
| Facility record | The clinic's real name, **and its street, city, postal code and phone** — PB-057 found these blank behind the installer placeholder `000-000-0000`, which prints a blank letterhead |

**Then, in `config.php`:** replace or clear the Unix-only commands (`lpr`, `enscript`,
`/usr/bin/file`) and the placeholder OFX bank IDs (OD-04).

---

## 8. Step 6 — ACL provisioning ⚠ easy to miss, fails closed and looks like a mystery

```bash
php bin/console thiqa-branding:provision-report-acl
```

**This is not optional.** The `patients|bulk_rep` ACO that gates `patient_list.php` and
`unique_seen_patients_report.php` **exists only where this has been run**. On a fresh instance without
it, the guard resolves against a non-existent ACO and **fails closed for every role including
Administrators** — the two reports become unusable and it presents as a permissions mystery rather
than a missing migration (PB-009).

It is idempotent; re-running changes nothing.

---

## 9. Step 7 — Background-service trigger

Create a recurring trigger invoking:

```bash
php <abs-path>/bin/console background:services run
```

| Rule | Reason |
|---|---|
| Tick interval ≤ the **shortest active** service interval | Otherwise a service advances later than its own interval |
| Use an **absolute** path to `bin/console` | Schedulers frequently set no working directory |
| Run as an account that can see the application directory | ⚠ **On the demo host this must be the logged-on user**, because Google Drive mounts `G:` per session and a `SYSTEM` task cannot see the app at all — so the trigger there **does not survive a logoff**. **That is a demo-host artefact. A customer instance must use a service account and must survive reboot.** Do not copy the demo arrangement |

---

## 10. Step 8 — Backup

Configure per RDY-0081 once an off-instance target exists. **Until a restore has been proven into a
disposable instance (RDY-0082), treat the backup as unverified** — a backup nobody has restored is an
assumption with a file attached.

---

## 11. The smoke test — all five must pass

Defined by RDY-0047's own acceptance. **Record the result of each.**

| # | Check | Pass condition |
|---|---|---|
| **S-1** | **Login succeeds** | A named non-`admin` account authenticates at the staff login URL |
| **S-2** | **A patient can be registered** | Registration completes and the record is retrievable. **Do this as the Front Office role**, which is where the `front_office.json` Add-Patient defect (RDY-0042, fixed PR-16) would surface |
| **S-3** | **A backup runs** | Completes without error and produces a file with the expected `CREATE TABLE` count |
| **S-4** | **The service runner executes** | `next_run` advances for every active service within one interval; no active service overdue by more than 2× its own interval |
| **S-5** | **D-1 returns clean** | The audit-log tamper report returns *"No audit log tampering detected"*. ⚠ Do not run it over a window containing an `api_log` row — PB-030's false positive is still open |

**Record the elapsed provisioning time.** PRC-003 needs it, and it is one of the two highest-value
cost figures in the plan (RDY-0069).

---

## 12. First-execution log — to be completed by the follower

**The follower must not be the author.**

| Field | Value |
|---|---|
| Provisioned by | |
| Date | |
| Source commit SHA | |
| **Elapsed time** | |
| S-1 login | ☐ pass ☐ fail |
| S-2 register a patient | ☐ pass ☐ fail |
| S-3 backup | ☐ pass ☐ fail |
| S-4 service runner | ☐ pass ☐ fail |
| S-5 D-1 clean | ☐ pass ☐ fail |

**Questions this runbook failed to answer** *(every entry is a defect in it)*:

| # | Question | Step | Fix applied |
|---|---|---|---|
| | | | |

---

## 13. Acceptance

| Criterion | Result |
|---|---|
| A repeatable runbook exists | **MET** |
| Environment-specific configuration separated from code | **MET as specification** — Steps 4 and 7; unproven until executed |
| **A person who did not write it provisions from it without asking an unanswered question** | **NOT MET** — never executed |
| Smoke test defined | **MET** — S-1…S-5 |
| Smoke test passes on the provisioned instance | **NOT MET** — no instance provisioned |
| Time recorded | **NOT MET** |

### Status: **RDY-0047 — NOT CLOSED.** Runbook written; its acceptance is an execution by someone else.

**`Blocks`: G3 G6.** No gate count moved (§0.0 Rule 3).

**Also unblocks on execution:** RDY-0048's third criterion (*"the runbook contains the handling"*) is
**satisfied by §6 as written** — but RDY-0048 stays open on its first criterion regardless, because
the demo instance still runs the default password.
