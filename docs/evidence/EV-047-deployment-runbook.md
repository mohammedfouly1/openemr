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

### 7.1 OD-04 in full — AGENT-OPS addendum, 2026-08-16 (PB-18x)

**Live-reconfirmed on this host, unchanged since the 2026-08-13/14 audit passes** (`sites/default/config.php`):

| Line | Current value | Problem on Windows |
|---|---|---|
| `OPENEMR_PRINT_COMMAND` | `lpr -P HPLaserjet6P -o cpi=10 ...` | `lpr` is a Unix line-printer client; no such binary ships or is reachable on this stack |
| `OPENEMR_HYLAFAX_ENSCRIPT` | `enscript -M Letter -B -e^ --margins=...` | `enscript` is a Unix PostScript converter for HylaFAX; not present |
| `$GLOBALS['oer_config']['documents']['file_command_path']` | `/usr/bin/file` | Absolute Unix path; does not exist on Windows |
| `$GLOBALS['oer_config']['ofx']['bankid']` / `['acctid']` | `123456789` (placeholder, ×2) | Not OS-specific, but the installer default and never replaced |

**Per RDY-0049's own acceptance criterion** — *"printing, faxing and MIME detection either work or
are documented as unsupported"* — the disjunction is satisfied by disclosure, and that is what this
runbook specifies rather than attempting non-native Windows print/fax tooling as part of a generic
provisioning step:

| Capability | Disposition on a Windows-hosted instance |
|---|---|
| Direct Unix-socket printing via `lpr` | **Unsupported.** Windows printing goes through the OS print spooler; if print support is contracted, implement via a Windows-native path (e.g., a configured default printer + a print-to-file/PDF flow) as a **separate, scoped** piece of work — not a `config.php` value |
| HylaFAX faxing via `enscript` | **Unsupported.** HylaFAX itself is a Unix daemon; faxing on a Windows deployment needs a different transport entirely (e.g., a fax API/gateway), which is its own scoping exercise, not a config edit |
| MIME detection via `/usr/bin/file` | **Replace, not disclose — this one has a native fix.** PHP's bundled `fileinfo` extension (`finfo_open`/`mime_content_type`) does the same job without shelling out to any external binary, Unix or Windows, and `fileinfo` is already required and loaded on this stack (`CLAUDE.local.md` §5, the 33-extension list). **Recommended runbook value:** leave `file_command_path` unset/empty on Windows deployments and confirm the document-upload code path falls back to `finfo` rather than shelling out — this is a code-level check for whoever executes this runbook's first provisioning, logged in §12's question table if the fallback does not already exist |
| OFX bank IDs | **Set per instance**, or leave the OFX/quickbooks-export feature undocumented-as-unused if no customer has requested it yet — never ship the placeholder `123456789` on an instance that claims financial export works |

**Not applied to the live demo host in this entry.** `sites/default/config.php` is shared,
concurrently read by other active sessions this run, and outside the RDY-0044-B reset scope (a
reset does not revert it) — an edit here is a persistent, cross-session change with no rollback via
the demo-reset mechanism. Per the same disclosure-over-silent-mutation standard RDY-0083 applied:
**this is now specified for the runbook (the customer-instance path), not applied to the shared demo
config**, and is recorded as a decision, not an oversight.

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

**Owner decision, 2026-08-19 — pilot-host trigger mechanism.** Given directly by the Owner (Mohammed
Elfouly) in conversation with the orchestrating session, 2026-08-19 (not relayed through any agent).
Of the three live options this section (and RDY-0083's register row) had left undecided — a proper
Windows Service, a `SYSTEM`-context scheduled task against a non-Drive-mounted deployment, or enabling
`rest_api` — **the Owner selected a proper Windows Service.**

**This is specified for the pilot runbook; it is explicitly NOT validated on this demo host.**
`EV-083`'s own PB-181 investigation (AGENT-OPS, 2026-08-16) already established, by testing the closely
related case, why: a non-interactive logon type (S4U/Batch/ServiceAccount) — the same execution context
a Windows Service typically runs under (`SYSTEM`/`LocalService`/`NetworkService`/a configured service
account) — runs outside the interactive window station, and **this host's Google Drive mount of `G:` is
per interactive user session**, so a task or service running outside that session cannot see the
application directory at all. That finding is preserved and confirmed here, extended by analogy from
"non-interactive scheduled task" to "Windows Service": **on this specific demo host, a proper Windows
Service would hit the identical `G:`-mount blindness** that ruled out the SYSTEM-scheduled-task path.

**This does not make the Owner's decision wrong for its actual target.** The decision is for a **real
pilot host**, provisioned under RDY-0064, which is not expected to run the application from a
per-session Google-Drive mount the way this dev machine does (the same reasoning already applied to the
TLS-renewal Scheduled Task at §10.5 above). A proper Windows Service is a normal, well-supported pattern
on an ordinary Windows Server filesystem. **Implementation and testing of this mechanism is deferred to
real pilot-host provisioning** — it cannot be built or verified on this host, and pilot-host provisioning
itself waits on RDY-0064 (hosting). Nothing here is implemented; this section records the specification
only, consistent with how the rest of this step and §10.5 already treat unexecuted, hosting-blocked
work.

---

## 10. Step 8 — Backup

Configure per RDY-0081 once an off-instance target exists. ~~Until a restore has been proven into a
disposable instance (RDY-0082), treat the backup as unverified~~ **Updated 2026-08-16 (PB-182,
`EV-082`): a restore has now been proven — disposable instance, 20.09 s, row counts and 283/283
table checksums matched, application starts, authenticated login succeeds, D-1 returns clean.**
6 of RDY-0082's 7 criteria are MET; only the browser-driven leg 6 remains, unrelated to whether the
backup/restore mechanism itself works. Off-instance copy and encryption at rest (RDY-0081) remain
open regardless — this update is about restore proof, not the full backup policy.

---

## 10.5 Step 8.5 — TLS, domain and DNS (RDY-0085) ⚠ specification only — not executed, no hosting yet

**AGENT-OPS addendum, 2026-08-16 (PB-18x).** RDY-0085 is blocked on RDY-0064 (hosting), which is
**DECIDED (Dammam, `me-central2`) but provisioning-BLOCKED — EXTERNAL** as of this document's own
tracking. Nothing here can be executed against a real customer instance until that unblocks. What
follows satisfies RDY-0085's *"specify certificate issuance and renewal in the runbook"* clause —
the specification, not the execution.

| Item | Specification |
|---|---|
| Domain | One subdomain per customer instance under a controlled parent domain (e.g. `<clinic>.<brand-domain>`), registered and DNS-delegated before provisioning starts — this is a prerequisite of this runbook's Step 1, not a Step 8.5 action |
| Certificate issuance | ACME (Let's Encrypt) via a Windows-native ACME client — **`win-acme` (`wacs.exe`)** is the standard choice for IIS/Apache-on-Windows hosts; it can target Apache's `conf/` directly via a manual/script install plugin. Avoid `certbot`'s Windows support, which upstream itself now recommends against in favour of WSL or `win-acme` |
| Renewal | `win-acme` self-registers a **Windows Scheduled Task** for renewal on install — ⚠ **on a customer host this is exactly the trigger pattern RDY-0083 already had to solve.** Unlike this demo host, a **customer instance's application directory is not expected to be a per-session Google-Drive mount** (`EV-083` §2.1, `EV-047` §9 above), so a certificate-renewal Scheduled Task run as a proper service account **should** work where the background-service trigger's demo-host limitation does not apply. **Verify this explicitly during the first real provisioning** — do not assume by analogy; log the result in §12 |
| HTTP → HTTPS | `httpd.conf`: a `<VirtualHost *:80>` block containing only `Redirect permanent / https://<domain>/`, with the application served exclusively from the `:443` vhost. **Acceptance criterion is explicit that HTTP requests redirect or are refused** — a bare redirect is the simpler of the two and is what is specified here |
| Verification (once executed) | `curl -I http://<domain>/` → redirect; `curl -I https://<domain>/` → `200`; certificate validity and issuer checked (`openssl s_client` or equivalent); renewal exercised once or its date placed under RDY-0084 monitoring, per RDY-0085's own acceptance text |

**Status: still NOT READY.** This section closes none of RDY-0085's acceptance criteria — *"the
pilot instance is reachable only over HTTPS"* requires a real pilot instance, which requires RDY-0064
to unblock first. What changed: the runbook no longer has a blank cell where the certificate
mechanism should be specified.

---

## 10.6 Step 8.6 — Outbound email (OD-05) ⚠ specification only — no live instance to configure yet

**AGENT-OPS addendum, 2026-08-16.** Live-reconfirmed unchanged on this host: `practice_return_email_path`,
`patient_reminder_sender_email`, `SMTP_USER` and `SMTP_PASS` are all empty; `EMAIL_METHOD = SMTP`,
`SMTP_HOST = localhost` — nothing is actually configured to send, so `Email_Service` (RDY-0083) runs
on schedule and silently no-ops every time.

| Setting | Per-instance value |
|---|---|
| `EMAIL_METHOD` | `SMTP` (unchanged) |
| `SMTP_HOST` / `SMTP_PORT` | The real outbound relay for the clinic's environment — never `localhost` unless a local MTA is actually configured and tested |
| `SMTP_USER` / `SMTP_PASS` | Generated/assigned per instance, stored per **Step 4**'s secret-handling rule — never a shared credential across instances |
| `practice_return_email_path`, `patient_reminder_sender_email` | The clinic's real sender address, not blank — a blank sender is indistinguishable from "email is disabled" until someone opens the queue and finds it empty |

**Verification:** after setting these, confirm at least one queued item in `email_queue` (or a
deliberately triggered reminder) is actually delivered — S-4 of the smoke test (§11) checks that the
service *runs*, not that mail *sends*; this is a distinct, additional check worth adding to a future
smoke-test revision, flagged here rather than silently assumed covered.

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
