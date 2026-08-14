# EV-055 — PHI IN THE AUDIT TRAIL: DETERMINATION

**Requirement:** RDY-0055 · **Gate:** G3 · **Owner:** Security Reviewer
**Date:** 2026-08-14 · **Measured against:** the **seeded** demo system (Marketing MVP Seed v1),
never a real-patient system, exactly as the acceptance criteria require.

---

## 0. Finding, stated first

**The audit's latent finding is now active, and it is measured rather than predicted.**

Source B recorded that bind parameters are appended verbatim to `log.comments`, and that on a system
with real data *"PHI — and any bound secret — lands in the audit table in plaintext base64."* At the
time there were zero patients, so it could not be demonstrated.

**There are now 30 synthetic patients, and it is demonstrated:**

| Probe against **decoded** `log.comments` | Rows |
|---|---:|
| Contains a patient surname | **6,073** |
| Contains a `SYN-` patient identifier | **30** |
| Contains a national-ID-class value (`999…`) | **30** |
| Contains a patient telephone number | **30** |
| Contains clinical free text | **214** |

Total log: **74,397 rows**, of which **74,042 are base64**.

**Base64 is an encoding, not encryption.** Every figure above was produced with one built-in SQL
function — `CONVERT(FROM_BASE64(comments) USING utf8mb4)`. Anyone who can read the table can read the
PHI, with no key and no tooling.

> ### ⚠ A false negative that anyone repeating this will hit
>
> The first pass searched `log.comments` directly with `LIKE '%Alharthi%'` and returned **0 rows for
> every probe**. That looks exactly like "no PHI in the audit log" — and it is wrong. **A plaintext
> search over base64 data cannot match.** The correct query decodes first. **Any prior assurance that
> the log is clean, if it was produced by a plaintext search, is void.**

## 1. What appears in `log.comments`

The stored value is the **raw SQL statement with its bind parameters interpolated**, base64-encoded.
A decoded sample:

```
REPLACE INTO `form_eye_mag_wearing` (`ENCOUNTER`, `FORM_ID`, `PID`, `RX_NUMBER`,
  `ODSPH`, `ODCYL`, `ODAXIS`, `ODVA`, ...
```

Because the statement text carries the values, **anything written to a patient record transits the
audit log**: names, identifiers, contact details, and clinical free text. Event mix confirms the
reach — `patient-record-select` 2,810, `patient-record-replace` 1,295, `patient-record-insert` 385,
and **3,249 rows carrying a `patient_id`**.

**A schema correction.** The audit cited an `encrypt` column and reported *"all 4,280 rows are
`encrypt='No'`"*. **That column does not exist in this schema** (8.2.0): `log` is
`id, date, event, category, user, groupname, comments, user_notes, patient_id, success, checksum,
crt_user, log_from, menu_item_id, ccda_doc_id`. The audited observation described a different
version. **The substance is unchanged and arguably worse** — there is now no encryption flag at all,
because the encryption code path was removed.

## 2. Who can read the log tables

| Route | Who | Control |
|---|---|---|
| **Application** — log viewer and tamper report | `admin\|super`, held by exactly **two** ACL groups: **Administrators** and **Emergency Login (breakglass)** | ACL-gated; verified denied for Physician, Front Office, Accounting and Clinical Assistant (PB-037) |
| **Database** | **Anyone with the `openemr` DB credential** — the application user has full rights on the schema | **No table-level restriction exists.** This is the wider exposure |
| **Backups** | Anyone with a backup file. Backups are **unencrypted** (`thiqa-branding:backup`; encryption at rest is an open RDY-0081 item) | **None** |

**Application-layer access is properly narrow. Database and backup access are not restricted at
all**, and that is where the real exposure sits.

## 3. Retention

**There is none.** No purge, retention or pruning global is configured — the query for
`%log%` combined with `%purge%|%retain%|%days%|%prune%` returns nothing.

The log currently spans **2026-08-07 → 2026-08-14 — seven days — and already holds 74,397 rows.** It
grows without bound, and every row is a permanent plaintext-recoverable copy of whatever SQL produced
it. **On a pilot, log volume and PHI exposure both grow linearly with use and nothing ever ages out.**

## 4. Determination

**Recommended handling: RESTRICT AND DISCLOSE. Do not accept silently, and do not claim it is
encrypted.**

| # | Measure | Rationale |
|---|---|---|
| 1 | **Never describe the audit log as encrypted.** It is base64-encoded. Prohibit "encrypted audit trail" in all material | The word is factually wrong and would not survive a technical reviewer decoding one row in front of you |
| 2 | **Encrypt backups at rest** (open RDY-0081 item) | Today a backup file is a plaintext-recoverable PHI export. This is the cheapest large risk reduction available |
| 3 | **Restrict DB credentials** to the smallest set of people, and record who holds them | The application ACL is already correct; the database is the unguarded route |
| 4 | **Set a retention policy** and implement pruning | Unbounded retention of plaintext-recoverable PHI has no defensible justification, and pruning conflicts with the tamper-verification design — **that conflict must be resolved deliberately, not by leaving retention unset** |
| 5 | **Disclose in the pilot agreement** (RDY-0068): what the log records, that values are encoded rather than encrypted, who can read it, and the retention | Disclosure-led positioning cannot be surprised by this; the customer's IT contractor will find it |
| 6 | Treat upstream redaction of bind parameters as a **contribution candidate**, not a local patch | It is upstream behaviour in `EventAuditLogger`; a local fix worsens the RDY-0045 divergence measured in EV-045 |

## 5. Acceptance status

| Criterion | State |
|---|---|
| Determination made **against a seeded system** | ✅ 30 patients, figures above |
| States **what appears** in `log.comments` | ✅ §1 |
| States **who can read** the log tables | ✅ §2 |
| States **the retention** | ✅ §3 — there is none |
| States **what the customer is told** | ⚠ **§4 measure 5 defines the content; the disclosure text does not exist yet** |
| If acceptance, disclosure passes **RDY-0003 claim review** | ❌ **RDY-0003 is open — no claim reviewer is named** |

**RDY-0055 is NOT closed.** The technical determination is complete and evidenced; the two remaining
items are a written disclosure and a claim reviewer to approve it — both governance, neither
engineering.

**One line that must reach RDY-0056 immediately:** the audit-integrity qualification must never imply
the log is encrypted. It is not. It is base64, and this document decodes it in one function call.
