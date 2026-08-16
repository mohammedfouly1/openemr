# EV-071 — CUSTOMER DATA EXPORT PROCEDURE

**Requirement:** RDY-0071 · **Gates:** G3, G5, G6 · **Owner:** DevOps / Infrastructure
**Acceptance:** *"The procedure is executed once end-to-end against the seeded demo system; the
resulting package contains report CSVs, a database export and the uploaded documents; **a reviewer
confirms it is usable without our help**."*
**Executed:** 2026-08-14 · **Agent B**, Phase 2B
**Extended:** 2026-08-16 · **AGENT-DATA2**, PB-208 — remaining 7 (of the 8-9 CSV-capable reports
actually reachable) report exports executed and verified. See §5.2.

This is **Pillar 2** — *your records stay yours* — turned from an architecture note into a procedure
someone can run. GTM O-3's supporting message is the register to keep: *"leaving should be a
procedure, not a negotiation."*

---

## 1. The procedure

**Run as:** an operator with database access and one administrator application login.
**Output:** a single directory, checksummed, that the customer keeps.

```bash
EXPORT="<target>/thiqa-export-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$EXPORT"/{reports,database,documents}

# 1. Database — the authoritative copy. --single-transaction avoids locking a live instance.
mysqldump -u <user> -p --single-transaction --routines --events <db> \
  > "$EXPORT/database/openemr-full.sql"

# 2. Document payloads — mysqldump does NOT capture these (see §3).
cp -r sites/<site>/documents/[0-9]*/ "$EXPORT/documents/"

# 3. Document manifest — without this the payloads are unreadable (see §3).
mysql -u <user> -p <db> -B -e "
  SELECT d.id AS document_id, d.foreign_id AS patient_pid, p.pubpid AS patient_ref,
         CONCAT(p.fname,' ',p.lname) AS patient_name, d.type, d.mimetype,
         d.name AS original_filename, d.url AS stored_path, d.date AS uploaded
  FROM documents d LEFT JOIN patient_data p ON p.pid = d.foreign_id ORDER BY d.id;" \
  | sed 's/\t/,/g' > "$EXPORT/documents/DOCUMENT-MANIFEST.csv"

# 4. Report CSVs — authenticated session, CSRF token harvested from the report page,
#    then POST with form_csvexport=true. One call per CSV-capable report.
#    Harness: docs/evidence/harnesses/ (see §5 for the caveat on coverage).

# 5. Customer-facing README and checksums.
cp docs/evidence/templates/export-README.txt "$EXPORT/README.txt"
cd "$EXPORT" && find . -type f ! -name CHECKSUMS.sha256 -exec sha256sum {} \; > CHECKSUMS.sha256
```

---

## 2. Executed — the package that was actually produced

`C:/openemr-stack/exports/thiqa-export-20260814-032943` — **15 files, 79 MB.**

| Component | Result |
|---|---|
| **Database export** | `database/openemr-full.sql`, **82,438,080 bytes**, **283 `CREATE TABLE`** — matches the live schema exactly |
| **Report CSV** | `reports/RPT-0009-appointments.csv` — HTTP 200, `Content-Type: application/Csv`, `Content-Disposition: attachment; filename=appts.Csv`, 4,065 bytes |
| **CSV parses as CSV** | Verified with a real CSV reader, not by eye: **7 named columns** (Provider, Date, Time, Patient, DOB, Type, Status), **40 populated data rows** |
| **Document payloads** | **10 files** across 10 per-patient directories |
| **Document manifest** | `documents/DOCUMENT-MANIFEST.csv` — 10 rows, maps every payload to patient, `pubpid`, name, mimetype and original filename |
| **README** | Customer-facing, plain text, no product jargon |
| **Checksums** | `CHECKSUMS.sha256`, 14 entries — **`sha256sum -c` → 14 of 14 OK** |

---

## 3. ⚠ Two findings the execution produced that writing the procedure would not have

### 3.1 `mysqldump` does not export the documents, and the failure is silent

The `documents` table holds **rows**; the files live on disk under
`sites/<site>/documents/<pid>/`. A database-only export restores a document list where **nothing
opens**, and it looks complete until someone clicks.

*(PB-045 hit the same thing from the other direction when building the RDY-0044-B baseline. It is
recorded here too because the export procedure is the surface where a **customer** would meet it,
and they would meet it after we had stopped being responsible for them.)*

### 3.2 The exported documents are unreadable without a manifest — this is the "usable without our help" test failing

The payloads on disk are named with internal UUIDs and **carry no file extension**:

```
documents/6/a27f08b3-e2d6-4b72-aaee-0943d95c23a0
```

A customer receiving that has no way to know it is
`SYNTHETIC-DEMO-specimen-06.txt`, `text/plain`, belonging to `SYN-0006`.

**The first export produced exactly this**, and it satisfied the letter of the acceptance criterion —
"contains the uploaded documents" — while failing its intent. `DOCUMENT-MANIFEST.csv` was added for
this reason and is now a **required** step, not an optional nicety.

**This is the single most valuable thing this execution found.** A procedure written and never run
would have shipped the unreadable version.

### 3.3 One open question the manifest raises

`stored_path` in the manifest reproduces the absolute server path
(`file://G:/My Drive/OpenEMR/sites/default/documents/...`), which discloses our infrastructure
layout. It is genuine provenance and is useful for verification, but it is our path, not theirs.
**Recorded as an open choice for the customer-facing template**: keep it for traceability, or strip
it to a relative path. Not decided here.

---

### 3.4 — 2026-08-16 (AGENT-DATA2, PB-208): the "8 CSV reports" figure is off by one, and one of the 8 is not a CSV export at all

Before running the remaining exports, the exact 8 had to be identified — this document names the
count but never the reports. `grep -rl form_csvexport interface/reports/` finds **10** files.
Cross-checked against `interface/main/tabs/menu/menus/standard.json`:

- `ippf_cyp_report.php` sits inside the "Statistics" menu group, which the JSON gates on
  `"global_req": "ippf_specific"` (line 1911). That global defaults to `false`
  (`interface/globals.php:405`) and is not set in this instance's `globals` table — confirmed
  live (`SELECT gl_value FROM globals WHERE gl_name='ippf_specific'` returns no row). **Unreachable
  on this instance**, so it does not count as one of the "8 of 55" a user could actually invoke.
- `unique_seen_patients_report.php` is described at line 1566 of this document's parent
  (`Marketing-MVP-and-Launch-Readiness-Requirements.md`) as "CSV + mailing labels" — checked
  directly (`grep -i csv interface/reports/unique_seen_patients_report.php`, zero matches). It
  exports **labels only** (`form_labels`, `Content-Disposition: attachment; filename=labels.txt`,
  `text/plain`-shaped output), not CSV. The "CSV" half of that description is wrong; corrected here
  since the source document is this file's own author, not a third party.

That leaves **9**, not 8, genuinely CSV-exporting and menu-reachable reports on this instance:
`appointments_report.php` (RPT-0009, already covered), `collections_report.php`,
`pat_ledger.php` (RPT-0028), `svc_code_financial_report.php`, `insurance_allocation_report.php`,
`sales_by_item.php`, `patient_list.php`, `patient_list_creation.php`, `message_list.php`. Recorded
as a documentation discrepancy, not resolved by picking one to drop — all 9 were tested below.

### 3.5 — 2026-08-16 (AGENT-DATA2, PB-208): a third real defect — `pat_ledger.php`'s CSV export is not CSV

Requested via `GET pat_ledger.php?form=1&patient_id=1&form_csvexport=true` (patient SYN-0001,
`pid=1`), authenticated as `n.alqahtani` (Administrator). HTTP 200, `Content-Disposition:
attachment; filename=svc_financial_report_2025-08-16--2026-08-16.csv` — **already wrong**: that
filename belongs to a different report entirely (`svc_code_financial_report.php:62` sets the exact
same string; `pat_ledger.php:343` copy-pastes it verbatim).

The body is worse. Only the header line is real CSV (`csvEscape()`-quoted). Every data line is raw
HTML:

```
<tr class='bg-white'><td colspan='4'><span class='font-weight-bold'>Encounter Dt / Rsn: </span>...
<tr style='background-color:#FFDDDD;'><td class='detail'>&nbsp;</td><td class='detail' colspan='2'>SYN-PAY-0001: SYNTHETIC DEMO payment...
```

Root cause, read from the source: `PrintEncHeader()` (`library/global_functions.inc.php:691`)
unconditionally `echo`s a `<tr>...` string with no `$_REQUEST['form_csvexport']` check at all — it
is shared between the HTML and CSV render paths and only the HTML path was ever written. Separately,
in `pat_ledger.php`'s own charge-line loop (lines 753-828), a `$csv` variable is declared per row
(`$csv = '';`) but **never assigned** before the branch at line 824-828 that does
`if ($_REQUEST['form_csvexport']) { echo $csv; } else { echo $print; }` — so the actual charge/
payment detail lines are silently dropped from the CSV (empty string), while the encounter-header
lines leak HTML markup into the file instead.

**Net effect: this "CSV" does not open as tabular data.** A spreadsheet would show one clean header
row, then cells containing literal `<tr>`/`<td>` markup and `&nbsp;` entities, with the actual
charge/payment amounts missing entirely (the numbers were only ever in `$print`, never copied into
`$csv`). This fails the RDY-0071/RDY-0059 acceptance bar ("opens in a spreadsheet ... plausible
content") outright — it is not a data-emptiness issue like §3.6, it is broken output for a report
that has data. **Not fixed by this session** (a proper fix needs someone to build `$csv` for the
charge-line and credit-detail branches and give `PrintEncHeader()`/`PrintCreditDetail()`/
`PrintEncFooter()` — all shared, non-CSV-aware — a CSV-mode path, which is more than a data-export
task should do unreviewed). Filed here as a genuine, reproducible defect in `pat_ledger.php`
(RPT-0028), not fabricated as a pass.

### 3.6 — 2026-08-16 (AGENT-DATA2, PB-208): a fourth, narrower defect — `patient_list_creation.php` throws an uncaught `TypeError` on a blank search option

`POST patient_list_creation.php` with `form_csvexport=true` and `srch_option=` (empty string, as
opposed to omitting the field or sending one of the seven valid option keys) returns **HTTP 500**.
`php_error.log`:

```
OpenEMR.CRITICAL: Uncaught exception! {"exception":"[object] (TypeError(code: 0): array_keys():
Argument #1 ($array) must be of type array, null given at
.../interface/reports/patient_list_creation.php:838)
```

Cause: `$srch_option_pointer = $search_options[$srch_option]["copy"] ?? $srch_option;`
(line 600) with `$srch_option=''` resolves to `''` again (no `demos`/`allergs`/etc. key exists for
the empty string), and downstream code at line 838 assumes `$search_options[$srch_option_pointer]`
is a populated array. **Not reachable through the normal UI** — the report's own `<select
name='srch_option'>` always carries a real option value, so an ordinary user driving the form
cannot produce this state; it only surfaces when the field is sent blank directly (as a raw request,
or a future front-end regression that fails to select a default). Retried with
`srch_option=demos` (Demographics, the first defined option): **HTTP 200**, clean CSV, 30 rows —
see §5.2. Recorded because it is a genuine unhandled-input crash, not because it blocks the export
capability itself.

## 4. What the package deliberately does not contain

Stated in the README in the customer's own terms, because Pillar 2 is only credible if the
boundaries are stated by us first:

- **The application itself.** It is GPL-3.0-or-later and obtainable independently of us.
- **Our hosting configuration.**
- **A migration into another vendor's system.** Export means **CSV and database access** — it is
  **not** a migration service, and there is **no BI layer**. That qualification travels with every
  discussion of this capability (RDY-0056 discipline, §24.4).

---

## 5. Acceptance

| Criterion | Result |
|---|---|
| Procedure executed once end-to-end against the seeded demo system | **MET** |
| Package contains a database export | **MET** — 283 tables |
| Package contains the uploaded documents | **MET** — 10 payloads **+ the manifest that makes them readable** |
| Package contains report CSVs | **7 of 9 reachable reports export clean, plausible CSV; 1 exports a broken (non-tabular) CSV; 2 export a structurally valid but empty CSV due to unrelated seed-data gaps.** See §5.2 |
| **A reviewer confirms it is usable without our help** | **NOT MET** — no reviewer has opened it. Human-blocked; not attempted by this session |

### 5.1 Why "report CSVs" was only partial as of 2026-08-14, stated plainly

**Eight of 55 reports export CSV. One was exported here.** PB-037 established that each OpenEMR
report takes its own form fields, so there is no generic export loop — the remaining seven each need
their parameters worked out individually, exactly as PB-037 had to do for the six demo reports.

**This was scoped work, not a blocker**, and it was honest to call it partial rather than claim the
criterion on one file. Superseded by §5.2 — the "8" figure itself needed correcting (§3.4) before it
could be completed.

### 5.2 — 2026-08-16 (AGENT-DATA2, PB-208): the remaining reports executed

All 9 (§3.4) driven the same way as RPT-0009's original proof: authenticated as `n.alqahtani`
(Administrator — holds every ACL these 9 reports require, including the `patients|bulk_rep`
PROTECTED gate on `patient_list.php`), CSRF token harvested from a GET of the report page, then a
POST (or, for `pat_ledger.php`, a GET — that report reads its identifying params from `$_GET` only)
carrying `form_csvexport=true`. Downloaded via `curl` with a session cookie jar rather than through
`claude-in-chrome`: the browser extension hit repeated transient `chrome-error://` connection resets
on this host under concurrent multi-agent load (server-side `curl` from this same host confirmed
Apache itself was answering normally throughout, 1.5-2.9 s per request), and `curl` reproduces the
exact "harvest CSRF, POST `form_csvexport=true`" method this document's own §1 step 4 already
prescribes. Files verified with `wc`/direct read, not by eye only.

| # | Report | Result |
|---|---|---|
| 1 | **RPT-0009 Appointments** (`appointments_report.php`) | **PASS** — prior evidence (PB-045), unchanged |
| 2 | **Collections and Aging** (`collections_report.php`) | **PASS** — HTTP 200, `filename=collections_report.csv`, 5,856 bytes, **36 data rows**, 19 columns, quoted CSV. Content: synthetic patient names, `pubpid` `SYN-00xx`, SSN column `999000xxxx` (matches EV-028's non-valid-Iqama convention), fictional payers |
| 3 | **Patient Ledger by Date** (`pat_ledger.php`, RPT-0028) | **DEFECT — CSV is not usable.** See §3.5. Header row is valid CSV; every data row is unescaped HTML with no dollar amounts. Does not meet the acceptance bar |
| 4 | **Financial Summary by Service Code** (`svc_code_financial_report.php`) | **PASS** — HTTP 200, `filename=svc_financial_report_2026-01-01--2026-08-16.csv`, 279 bytes, **4 data rows** (CPT codes `92014`/`92083`/`99213`/`99214`, billed/paid/adjustment/balance all populated). Note: ticking "financial reporting only" returns zero rows because no `codes` row has `financial_reporting=1` set in this seed — a parameter choice, not a defect; retested without it |
| 5 | **Patient Insurance Distribution** (`insurance_allocation_report.php`) | **PASS mechanically, trivial content.** HTTP 200, `filename=insurance_distribution.csv`, valid CSV, but only 1 row: `"-- No Insurance --",11600.00,36,29,100.0`. Root cause confirmed at the DB: `SELECT COUNT(*) FROM insurance_data` = **0** — the table this report's query depends on (`type='primary'` per-patient policy assignment) was never populated by the seeder, even though `insurance_companies` (2 synthetic payers) and billing/`ar_session.payer_id` are populated. **A Track D seed-data gap, not an export defect** — flagged onward, not fixed here (out of this session's remit) |
| 6 | **Sales by Item** (`sales_by_item.php`) | **PASS** — HTTP 200, `filename=sales_by_item.csv`, 3,451 bytes, **36 data rows**, CPT descriptions, synthetic patient names, dollar amounts populated |
| 7 | **Patient List** (`patient_list.php`, PROTECTED — `patients\|bulk_rep`) | **PASS** — HTTP 200, `filename=patient_list.csv`, 3,357 bytes, **30 data rows**. Confirms the PROTECTED classification's CSV path still works post-RDY-0050 remediation (Option A+, PB-008): Administrator allowed, addresses read `"NNNN Fictional Street"`, `pubpid` `SYN-00xx` |
| 8 | **Patient List Creation** (`patient_list_creation.php`) | **PASS with a valid `srch_option`** — HTTP 200, `filename=patient_list_custom.csv`, 2,071 bytes, **30 data rows** (Demographics: name, PID, age, gender). **Also found the §3.6 crash** on a blank `srch_option`, not reachable through normal UI use |
| 9 | **Message List** (`message_list.php`) | **PASS mechanically, empty content.** HTTP 200, `filename=message_list.csv`, 94 bytes, header row only. Root cause confirmed at the DB: `SELECT COUNT(*) FROM pnotes` = **0** — no patient messages/notes were ever seeded. **A Track D seed-data gap, not an export defect** |

**Updated completion count: 7 of 9 reachable CSV reports export cleanly and open as valid tabular
CSV with plausible synthetic content (RPT-0009, Collections, Financial Summary, Sales, Patient List,
Patient List Creation, plus the mechanically-valid-but-data-starved Insurance Distribution and
Message List if "exports cleanly" is read as "the export mechanism itself works" rather than "has
rich content"). 1 of 9 (`pat_ledger.php`) has a genuine content-corruption defect and does not meet
the acceptance bar. Read narrowly (clean export + non-trivial plausible data, the RPT-0009 bar this
document originally set): 6 of 9.** None of the emptiness findings are PHI leaks — the opposite
problem, verified absence of data — and no exported file contains anything that fails EV-028's
synthetic-data control (spot-checked: `SYN-` prefixes, `999...`-leading SSNs, `(SYNTHETIC)`-suffixed
payer names, "Fictional Street" addresses throughout).

### Status: **RDY-0071 — STILL NOT CLOSED**, but the export-completeness gap this session owned has moved as far as it genuinely can.

**`Blocks`: G3 G5 G6.** No gate count moved (§0.0 Rule 3) — this entry advances evidence, it does not
close the requirement.

**What remains, named precisely:**
1. **`pat_ledger.php`'s CSV export needs an actual code fix** (§3.5) — not attempted here, flagged
   for whoever owns report defects next.
2. **Two seed-data gaps** (`insurance_data`, `pnotes` both empty) make 2 of the 9 exports
   mechanically-fine-but-empty — Track D territory, not this session's to seed.
3. **The reviewer leg is still entirely untouched** — human-blocked, as it was on 2026-08-14, and not
   this session's to resolve (task scope explicitly excludes it).

**To close RDY-0071 fully:** fix `pat_ledger.php` (or accept/disclose the defect), decide whether the
two seed-data gaps get filled before customers see this capability, and — separately and
unavoidably — get an outsider to open the package cold and confirm they can read it without help.

---

## 6. Reproduce / verify

```bash
cd C:/openemr-stack/exports/thiqa-export-20260814-032943
sha256sum -c CHECKSUMS.sha256          # expect: 14 of 14 OK
grep -c "^CREATE TABLE" database/openemr-full.sql   # expect 283
head -1 documents/DOCUMENT-MANIFEST.csv
```
