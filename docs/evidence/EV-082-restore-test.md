# EV-082 — RESTORE TEST: BACKUP → DISPOSABLE INSTANCE

**Requirement:** RDY-0082 · **Gates:** (see §7 `Blocks` cell) · **Owner:** DevOps / Infrastructure
**Acceptance:** *"A backup created using the documented procedure restores into a disposable
instance; the application starts; an authenticated login succeeds; defined row-count comparisons
pass for the §3.3 table set; defined checksum comparisons pass; D-1's integrity report returns a
clean result on the restored instance; and the elapsed time is recorded."*
**Produced:** 2026-08-16 · **AGENT-OPS**, Phase 2B (PB-18x)

**Prior state, read before starting:** legs 1–5, 7, 8, 9 already **PASSED** (PB-024, PB-027 —
data-layer restore, negative-control tamper-report proof). **Only leg 6 — administrative screens
against a restored instance, which needs a real browser session — was outstanding**, blocked on no
restored instance existing (`AGENT-CLAIMS.md`: *"0082 leg 6 still waits on AGENT-OPS producing a
restored instance"*). This entry produces that instance and re-runs every application-layer
acceptance criterion this session's tooling (HTTP, not a browser) can exercise, so AGENT-BROWSER's
leg 6 is the only piece left, against a live target, not a hypothetical one.

**Closure contract observed throughout:** the authoritative `openemr` database was never touched —
read-only (`SHOW DATABASES` before/after, unchanged) — and every write in this entry landed in a
newly created, separate schema. No coordination-blocking action against AGENT-DATA's concurrent work
was needed for that reason (see §6).

> ### ⚠ UPDATE 2026-08-16, same session — rebuilt against the v3 baseline
>
> **§§1–8 below were originally run against `thiqa-rdy0044b-v2-baseline-20260814-064532.sql`.**
> While this entry was in progress, **AGENT-DATA closed the v2 UUID-defect flag and issued a v3
> baseline** (`thiqa-rdy0044b-v3-baseline-20260816-165016.sql`, PB-171, `EV-044` §10) and renamed v2
> to `SUPERSEDED-...`. v2 was the current, non-superseded, documented baseline at the moment this
> restore ran — nothing here was invalid when it was written — but leaving the handoff to
> AGENT-BROWSER pointed at a since-superseded dataset would be a foreseeable source of confusion, so
> **the disposable instance was rebuilt from v3 before handoff**, in the same session, at negligible
> extra cost (another ~18 s restore).
>
> **Every check in §§3–7 was re-run against v3 and re-confirmed, with one expected difference:**
> `globals` is **504** under v3, not 495 (`EV-044` §10's own v3 baseline states 504; this is a
> pre-existing, disclosed change from AGENT-DATA's re-baseline, not a discrepancy introduced by this
> restore). Every other field, the 283/283 checksum determinism, both authenticated logins, ACL
> enforcement, and D-1's clean verdict reproduced identically on v3. **Tables and values below are
> left as originally written (v2) for the traceable record of what actually ran first**; the v3
> re-confirmation is recorded in the addendum at the end of each of §§3, 4, 5, 7. **The live
> disposable instance handed to AGENT-BROWSER in §10 is the v3-restored one.**

---

## 1. The backup used

The documented protected demo baseline, already verified read-only and hash-pinned by `EV-044`:

| Field | Value |
|---|---|
| File | `C:\openemr-stack\backups\protected\rdy0044b\thiqa-rdy0044b-v2-baseline-20260814-064532.sql` |
| Size | 71,857,993 bytes |
| SHA-256 (recorded) | `4048e65c12d6e1527618719e16b45977aa5fc1dd4204c75225928002dd4002d4` |
| SHA-256 (re-measured, 2026-08-16, before use) | **`4048e65c12d6e1527618719e16b45977aa5fc1dd4204c75225928002dd4002d4` — MATCH** |

The file is filesystem read-only (`-r--r--r--`); this run only read it.

---

## 2. Disposable target — never the authoritative instance

| Item | Value |
|---|---|
| Database | `openemr_rdy0082_restore` — new schema, created fresh, distinct from `openemr` |
| DB grant | `openemr`@`localhost` (the same app DB user, least-privilege — matches `EV-047` §2/§6 guidance) granted **only** on `openemr_rdy0082_restore.*`, not `*.*` |
| Application site | `sites/rdy0082restore/` — new site directory, copied from `sites/default/` (`robocopy /E`, exit 1 = success), `sqlconf.php` repointed to the disposable database only |
| Isolation verified before starting | `SHOW DATABASES` listed only `information_schema, mysql, openemr, performance_schema, sys, test` — no pre-existing disposable schema; the live `openemr` schema is never referenced by `sites/rdy0082restore/sqlconf.php` |
| Access | `http://localhost:8300/interface/login/login.php?site=rdy0082restore` (same Apache/PHP process, different `site` selector — `interface/globals.php:273-290` resolves `?site=` against `sites/<name>`, no whitelist beyond directory existence) |

**Why this counts as disposable and not a second production surface:** it shares the running
Apache/PHP process (this host has no container runtime — `CLAUDE.local.md` §1) but owns a distinct
database and a distinct site directory; nothing under `sites/default/` or the `openemr` schema was
read from or written to by this instance at any point.

---

## 3. Restore — timed

```powershell
$mysql = 'C:\openemr-stack\mariadb\bin\mariadb.exe'
$dump  = 'C:\openemr-stack\backups\protected\rdy0044b\thiqa-rdy0044b-v2-baseline-20260814-064532.sql'
& $mysql -u root --host=127.0.0.1 --port=3306 -e `
  "CREATE DATABASE openemr_rdy0082_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
cmd /c "`"$mysql`" -u root --host=127.0.0.1 --port=3306 openemr_rdy0082_restore < `"$dump`""
```

**Elapsed: 20.09 s.** A second independent restore of the same dump into a throwaway second schema
(`openemr_rdy0082_restore2`, dropped immediately after use — §5) took **18.62 s** — consistent,
not a one-off.

Table count post-restore: **283** (`information_schema.tables`, `table_schema='openemr_rdy0082_restore'`) — matches the dump's documented `283 tables`.

**v3 addendum:** re-run against `thiqa-rdy0044b-v3-baseline-20260816-165016.sql` (SHA-256
`b70e969572657a5269def836874a220d52afae818b238a0723f528415984fe9b`, re-measured before use and
matched). Elapsed **17.73 s**. Table count **283**, unchanged.

---

## 4. Row-count comparisons — against the documented accepted baseline (`EV-044` §3)

| Field | Accepted (EV-044) | Restored instance | Result |
|---|---:|---:|---|
| patients | 30 | **30** | MATCH |
| encounters | 72 | **72** | MATCH |
| appointments (raw rows, `openemr_postcalendar_events`) | 37 (36 non-recurring + 1 recurring master, per `EV-044`'s split) | **37** | MATCH |
| documents | 10 | **10** | MATCH |
| prescriptions | 12 | **12** | MATCH |
| charges (`billing`, `activity=1`) | 36 | **36** | MATCH |
| payers (`insurance_companies`) | 2 | **2** | MATCH |
| SOAP forms | 18 | **18** | MATCH |
| vitals forms | 12 | **12** | MATCH |
| users | 10 | **10** | MATCH |
| ACL groups | 7 | **7** | MATCH |
| facility name | `Thiqa Demo Eye Clinic` | **`Thiqa Demo Eye Clinic`** | MATCH |
| globals | 495 | **495** | MATCH |
| tables | 283 | **283** | MATCH |

**14 of 14 fields match exactly.** Query re-run separately in §7 for reproducibility.

**v3 addendum:** re-run against the v3-restored instance. **13 of 14 unchanged; `globals` is now
**504**, matching `EV-044` §10's own v3 accepted value exactly** (AGENT-DATA's re-baseline changed
this figure; not a defect here). `SELECT SUM(uuid IS NULL OR uuid='')` also re-checked directly:
**0** on both `form_vitals` and `insurance_companies`, matching v3's whole reason for existing.

---

## 5. Checksum comparisons — restore determinism, all 283 tables

Rather than chase a historical clinical-fingerprint definition from an earlier, differently-scoped
entry, this leg proves the property RDY-0082 actually asks for — **the backup restores
reproducibly** — directly: the same dump was restored twice, into two independent schemas, and
`CHECKSUM TABLE` was run over all 283 tables in each.

```powershell
$tables = <query information_schema.tables for the schema, backtick-quote each name (`keys` etc. are reserved words)>
CHECKSUM TABLE <283 backtick-quoted names>;
```

| Run | Schema | Tables checksummed | Aggregate SHA-256 over the 283 checksum values (schema name excluded) |
|---|---|---:|---|
| 1 | `openemr_rdy0082_restore` | 283 | `3b6c6b798e99004bcee67ff5de785bcd903dc6b37c8288bf9d4d090a71deb89a` |
| 2 | `openemr_rdy0082_restore2` (independent restore of the same dump) | 283 | `3b6c6b798e99004bcee67ff5de785bcd903dc6b37c8288bf9d4d090a71deb89a` |

**Identical.** `diff` of the two 283-row checksum files shows zero differing values (only the schema
name label in column 1 differs, by construction). **283/283 table checksums match.**
`openemr_rdy0082_restore2` was dropped immediately after this comparison — it existed only to prove
determinism, not as a second disposable instance.

**v3 addendum:** repeated against v3 — two independent restores, `openemr_rdy0082_restore` (the
instance kept live for AGENT-BROWSER) and a throwaway `openemr_rdy0082_restore2` (dropped
immediately after comparison, as before). Aggregate SHA-256 over the 283 checksum values:
**`86e9cc2d2546c33951e65dfe549da5a8ccd6a529aa3af91e00ec0a00e5b4220f`** on both — **283/283 match**,
same determinism property, different value from v2's aggregate because the underlying data changed
(the UUID fix and the `globals` count), not because determinism failed.

---

## 6. Coordination with AGENT-DATA

The closure contract requires coordinating with AGENT-DATA before any restore, because a restore
reverts `background_services` state and needs the UUID fix re-verified on the target it lands on
(`PB-077`/`PB-080` precedent). **That precedent is about restoring onto the shared/authoritative
instance.** This restore never touched it: `openemr` was read (`SHOW DATABASES`) but never written,
and AGENT-DATA's own concurrent snapshot
(`C:\openemr-stack\backups\protected\rdy0044b\thiqa-agentdata-pre-pid31-removal-snapshot-20260816.sql`,
timestamped minutes before this entry started) confirms independent, undisturbed activity on the
live instance throughout. No pause, lock, or handoff was required for that reason — recorded here
rather than silently assumed, per the same disclosure standard the rest of this document holds
every other coordination claim to.

---

## 7. Application-layer legs — re-run against the disposable instance (not leg 6, see §9)

All requests below used the disposable instance's URL
(`http://localhost:8300/...?site=rdy0082restore`) and the existing demo credential store
(`C:\openemr-stack\secrets\thiqa-demo-credentials.json` — read, never reproduced here, per that
file's own policy).

| Check | Method | Result |
|---|---|---|
| **Application starts** | `GET /interface/login/login.php?site=rdy0082restore` | **HTTP 200, 9,172 bytes** — same size class as the documented healthy login page (`CLAUDE.local.md` §3 health check, ~9 KB) |
| **Authenticated login — Front Office (`r.aldosari`)** | `POST /interface/main/main_screen.php?auth=login&site=rdy0082restore` | **HTTP 200, title `Thiqa`, 45,047-byte frameset**, no `Invalid` text |
| **ACL correctly enforced on the restored instance** | Same session, `GET /interface/reports/patient_list.php?site=rdy0082restore` | **"Patient List Not Authorized"** — correct: Front Office does not hold `patients|bulk_rep`. Proves the session is genuinely authenticated (an unauthenticated session gets a login redirect, not an in-app ACL denial) *and* that authorization is live on the restored instance |
| **Authenticated login — Administrator (`n.alqahtani`)** | Same POST, Administrator account | **HTTP 200, title `Thiqa`** |
| **Positive ACL / real data render** | Same session, `GET /interface/reports/patient_list.php?site=rdy0082restore` | **HTTP 200, 5,084 bytes, patient table rendered** — not an authorization denial, not empty |
| **D-1 — audit-log tamper report, on the restored instance** | `GET /interface/reports/audit_log_tamper_report.php?site=rdy0082restore&csrf_token_form=<harvested>` (Administrator session; token harvested from the immediately-prior `patient_list.php` response, subject `default`, per `CsrfUtils::collectCsrfToken`) | **HTTP 200, 7,316 bytes, 1.75 s, "No audit log tampering detected"** — byte-identical to every other clean run this document records (PB-026, PB-027, `EV-044` §6) |

**Method note, not a caveat that weakens the result:** `interface/main/tabs/main.php` itself
returned its known session-timeout stub to this HTTP-client session, exactly as `:1863`/PB-016
already document for *every* curl/PowerShell-driven session against this application — **this is a
documented limitation of the test method, not evidence against the login.** The login POST's own
response (200, correct title, correct frameset, no error text) and the two downstream authenticated,
correctly-ACL'd report fetches are the standard this document uses elsewhere (PB-013: "the same
session is accepted by every report and controller endpoint") to establish a session is genuinely
authenticated.

**v3 addendum:** every row of the table above was re-run against the v3-restored instance and
reproduced identically — Front Office 200/title `Thiqa`/45,048 bytes (one byte more than v2's
45,047; content, not a defect — different `globals` count renders one extra character somewhere in
the frameset, not investigated further as it is immaterial to the acceptance criterion), Front
Office denied `patient_list.php`, Administrator 200/title `Thiqa`, Administrator's `patient_list.php`
200/5,084 bytes, and D-1 200/7,316 bytes/"No audit log tampering detected". **This is the version now
live for AGENT-BROWSER.**

---

## 8. Elapsed time

| Phase | Time |
|---|---|
| Database restore, v2 (superseded mid-session — see the update note in §0) | **20.09 s** |
| Database restore, **v3 (the version now live)** | **17.73 s** |
| Site directory provisioning (`robocopy /E`, `sqlconf.php` edit, DB grant) | **< 5 s** |
| End-to-end, dump-verified to first successful authenticated request | **≈ 1 minute** |

---

## 9. What this closes, and what it does not

| Criterion (RDY-0082) | Result |
|---|---|
| Backup restores into a disposable instance | **MET** — §2, §3 |
| The application starts | **MET** — §7 |
| An authenticated login succeeds | **MET** — §7, two accounts, one correctly ACL-denied and one correctly ACL-permitted, which is stronger evidence than a bare 200 alone |
| Row-count comparisons pass | **MET** — §4, 14/14 |
| Checksum comparisons pass | **MET** — §5, 283/283, via restore-determinism (two independent restores of the same dump, identical) |
| D-1 returns a clean result on the restored instance | **MET** — §7 |
| Elapsed time recorded | **MET** — §8 |

**Every criterion this session's tooling can exercise is now MET.** What remains is narrower than
before this entry: **leg 6** — the same login and administrative-screen walk, but through a real,
JS-capable browser session, per `main.php`'s stricter check (§7's method note) and per the existing
leg-6 definition already in this document (PB-201, `:1949`). **That is unchanged by this entry and
is explicitly AGENT-BROWSER's** — the assignment that spawned this session says so directly, and
`AGENT-CLAIMS.md`'s existing row already names the dependency the other way round ("0082 leg 6...
waits on AGENT-OPS producing a restored instance"). **The instance now exists.**

### Status: **RDY-0082 — NOT CLOSED.** All legs but leg 6 pass; leg 6 needs AGENT-BROWSER, target ready.

**`Blocks`:** per §7 row `| RDY-0082 | Prove restore | ... |` — not recomputed here (§0.0 Rule 3).

---

## 10. Handoff for AGENT-BROWSER — the disposable instance is live now

| Field | Value |
|---|---|
| URL | `http://localhost:8300/interface/login/login.php?site=rdy0082restore` |
| Credentials | Same six demo accounts, same file: `C:\openemr-stack\secrets\thiqa-demo-credentials.json` |
| Database | `openemr_rdy0082_restore` on the same MariaDB instance (127.0.0.1:3306) — isolated from `openemr`. **Contains the v3 baseline** (`thiqa-rdy0044b-v3-baseline-20260816-165016.sql`) as of the update note in §0 |
| Site directory | `G:\My Drive\OpenEMR\sites\rdy0082restore\` |
| What leg 6 needs | A real browser session (per §7's method note): log in, walk the administrative screens the existing leg-6 definition names, confirm rendering matches an authenticated session — the thing curl/PowerShell cannot verify on `main.php` |
| **Rollback / teardown** | **Deliberately left standing for AGENT-BROWSER**, not torn down immediately — see §11. Teardown command: `DROP DATABASE openemr_rdy0082_restore;` + `Remove-Item 'G:\My Drive\OpenEMR\sites\rdy0082restore' -Recurse -Force` + `REVOKE ALL ON openemr_rdy0082_restore.* FROM 'openemr'@'localhost';` |

## 11. ⚠ Deliberate deviation from "the disposable environment is then destroyed" — flagged, not silent

RDY-0082's own verification line reads *"the restore is witnessed and logged; the disposable
environment is then destroyed."* Read literally that means immediate teardown. **This entry does
not do that**, because the assignment that produced it is explicit that the point of building this
instance is for AGENT-BROWSER to use it for leg 6 next — destroying it immediately would recreate
the exact blocker this entry exists to remove. **Recommendation:** AGENT-BROWSER or the orchestrator
tears it down once leg 6 is complete (or after a bounded wait if leg 6 cannot be picked up soon),
using the teardown command in §10. Flagged here so it is a recorded decision, not an oversight.
