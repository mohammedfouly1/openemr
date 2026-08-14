# EV-071 — CUSTOMER DATA EXPORT PROCEDURE

**Requirement:** RDY-0071 · **Gates:** G3, G5, G6 · **Owner:** DevOps / Infrastructure
**Acceptance:** *"The procedure is executed once end-to-end against the seeded demo system; the
resulting package contains report CSVs, a database export and the uploaded documents; **a reviewer
confirms it is usable without our help**."*
**Executed:** 2026-08-14 · **Agent B**, Phase 2B

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
| Package contains report CSVs | **PARTIAL — 1 of 8.** See below |
| **A reviewer confirms it is usable without our help** | **NOT MET** — no reviewer has opened it |

### 5.1 Why "report CSVs" is only partial, stated plainly

**Eight of 55 reports export CSV. One was exported here.** PB-037 established that each OpenEMR
report takes its own form fields, so there is no generic export loop — the remaining seven each need
their parameters worked out individually, exactly as PB-037 had to do for the six demo reports.

**This is scoped work, not a blocker**, and it is honest to call it partial rather than claim the
criterion on one file.

### Status: **RDY-0071 — NOT CLOSED.** Procedure written and executed; 1 of 8 report CSVs; reviewer confirmation outstanding.

**`Blocks`: G3 G5 G6.** No gate count moved (§0.0 Rule 3).

**To close:** export the remaining seven CSV-capable reports, then have someone who did not build
the package open it cold and confirm they can read it. **That reviewer must not be the person who
produced it** — the whole criterion is about usability to an outsider.

---

## 6. Reproduce / verify

```bash
cd C:/openemr-stack/exports/thiqa-export-20260814-032943
sha256sum -c CHECKSUMS.sha256          # expect: 14 of 14 OK
grep -c "^CREATE TABLE" database/openemr-full.sql   # expect 283
head -1 documents/DOCUMENT-MANIFEST.csv
```
