# EV-016 — §23.4 AUTHORIZATION MATRIX

**Requirement:** RDY-0016 · **Gates:** G1, G3, G5 · **Owner:** Security Reviewer
**Pass condition (§23.4):** *"every positive row succeeds **and** every negative row is denied. A
single negative-row failure fails the matrix."*
**Executed:** 2026-08-14 · **Agent B** · **Harness:** `docs/evidence/harnesses/rdy0016-matrix.php`

---

## 1. Result

**32 probes executed. 32 PASS. 0 FAIL.**

Real authenticated HTTP under each role's **own** account — login `POST` to
`main_screen.php?auth=login`, per-role cookie jar, then **direct-URL `GET`** with redirects
disabled. Administrator evidence is never accepted for a role's own row, per §23.4.

**No password appears in this document, in the harness output, or in any log.** Credentials are
read from the protected store and used only inside the login POST body.

| Row | Actor | Probe | Result |
|---|---|---|---|
| A-1 | Front Office | cannot open clinical note (`load_form.php?formname=soap`) | **403** ✅ |
| A-1 | Front Office | cannot open prescriptions (`?prescription&action=list`) | **403** ✅ |
| A-1 | Front Office | cannot open lab results (`procedure_stats.php`) | **403** ✅ |
| A-1 | Front Office | cannot open patient report (`patient_report.php?pid=1`) | **403** ✅ |
| A-3 | Front Office | cannot reach `patient_list` · `unique_seen_patients_report` · `cdr_log` · `destroyed_drugs_report` · `patient_edu_web_lookup` · `external_data` · `services_by_category` | **403 ×7** ✅ |
| A-3 | Front Office | **CAN** reach `patient_flow_board_report` · `chart_location_activity` · `charts_checked_out` | **200 ×3** ✅ — documented business exception (PB-008): chart tracking and flow are Reception work |
| A-4 | Front Office | cannot reach `amc_full_report.php` | **403** ✅ |
| A-5 | Front Office | cannot reach `?x12_partner&action=list` | **403** ✅ — see §3 |
| A-9 | Front Office, Physician, Accounting, Clinical Asst | cannot reach `layout_listitems_ajax.php` | **403 ×4** ✅ |
| A-6 | Physician | cannot open practice settings · user administration · ACL administration · payment posting | **403 ×4** ✅ |
| A-8 | Accounting | **CAN** run the patient ledger | **200, 19,741 B** ✅ |
| A-8 | Accounting | cannot open a clinical patient report | **403** ✅ |
| A-11 | Clinical Asst | `patients\|med` does not imply `admin\|super` (`edit_layout.php`) | **403** ✅ |
| A-11 | Front Office | `patients\|demo` does not imply `admin\|users` (`usergroup_admin.php`) | **403** ✅ |
| **CTRL** | Administrator | **CAN** reach `patient_list` · `amc_full_report` · `?x12_partner&list` · user administration | **200 ×4** ✅ |

---

## 2. The two controls that make the result mean something

A matrix of denials is worthless without evidence that the harness *can* report a failure and that
the denials are *role-specific*. Both were established, and one of them changed a result.

**Positive control (`CTRL` rows).** The Administrator reaches every surface the other roles are
denied — `patient_list` 200/5,084 B, `amc_full_report` 200/1,783 B, user administration
200/7,933 B. **Without this, "every probe returned 403" would be equally consistent with a broken
application.** It is not: the denials discriminate by role.

**The harness demonstrably reports FAIL.** An earlier run of this same harness returned
**3 FAIL** — three probes pointed at URLs that did not exist and returned HTTP 404. They were
**not** recorded as authorization failures, because a 404 is not a denial; the URLs were corrected
and re-run. That episode is retained here as proof the checker is not stuck on PASS.

**A loose heuristic was removed before publication.** `denied()` originally also matched the bare
substring `acl` anywhere in the body, which would have manufactured a PASS on any ordinary page
containing that string. It now recognises only HTTP 403 or the application's own denial wording.
**All results above are from the tightened version.**

---

## 3. ⚠ §23.4's A-5 URL is malformed — the denial is real, the route is not

The matrix specifies `?x12_partner&action=list`. Probed across three roles and three URL forms:

| URL form | Administrator | Accounting (holds `acct\|bill`) | Front Office |
|---|---|---|---|
| `?x12_partner&action=list` — **as §23.4 writes it** | **404** | **404** | **403** |
| `?x12_partner&list` — true positional form | **200** | **200** | **403** |
| `?controller=x12_partner` — explicit form | **200** | **200** | **403** |

**Cause, from source.** `Controller::act()` (`library/classes/Controller.class.php:198-220`) takes
the **first** query key as the controller and the **second** as the action. For
`?x12_partner&action=list` the second key is the literal string `action`, so it dispatches
action `"action"` — which is invalid, hence 404. The intended action never runs.

**Two conclusions, and the security one is the reassuring one:**

1. **Front Office is denied `403` on all three forms.** The ACL gate fires **before** dispatch in
   every routing path, so A-5 passes more robustly than §23.4 asked. This is the RDY-0052
   fail-closed change (PB-011) working.
2. **§23.4's A-5 URL should be corrected to `?x12_partner&list`.** As written it tests a route that
   404s even for an administrator — a tester who only ran the admin leg could conclude the
   controller is unreachable by anyone. **Recorded, not silently edited**: §23.4 is the
   authoritative matrix and changing it is the Owner's call.

---

## 4. ⚠ Why RDY-0016 is NOT CLOSED — four rows cannot be executed on the current dataset

PB-014 predicted five rows were blocked on Track D seeding. **Track D has since seeded 30 patients
and 72 encounters, and four of those rows are still blocked** — for reasons the seed did not
address.

### 4.1 No sensitivity-flagged encounter exists

```sql
SELECT sensitivity, COUNT(*) FROM form_encounter GROUP BY sensitivity;
-- normal   72
```

**All 72 encounters are `sensitivity = 'normal'`.** RDY-0030 (*"an encounter with sensitivity
set"*) is **P1** and was not seeded.

| Row | Leg blocked |
|---|---|
| **A-2** | Front Office *"cannot see any encounter carrying a non-empty sensitivity value — invisible, not redacted"* **(⚠ this expected-behavior wording corrected 2026-08-19 — see `EV-056-057-088` §2.2 addendum: two independent static-code reads found the row is redacted, not invisible; re-read this row's own expected behavior as "redacted" accordingly)**. **No such encounter existed at the time this row was written.** A sensitivity-flagged encounter now exists (`SYN-0014` encounter 31, added 2026-08-19) but a live browser session to actually exercise this row has not yet been available |
| **A-7** | Clinician *"cannot see `high`-sensitivity encounters"* |
| **A-8** | Accounting *"cannot see sensitivity-flagged encounters"* |

**This is the sharpest finding in this entry.** Sensitivity gating is a named limitation in the
claim register (L-28, MC-16, RDY-0057) and is the mechanism behind a Pillar 1 statement. **It has
never been exercised, in either direction, on any dataset.** One seeded encounter closes three
matrix legs at once.

### 4.2 Every clinical form is authored by `admin`

```sql
SELECT user, COUNT(*) FROM forms GROUP BY user;
-- admin  110
```

Encounters are correctly split across two physicians (`provider_id` 6 = `y.alharbi` 36,
7 = `s.almutairi` 36), so *encounter* ownership is testable. But **no form is authored by a
clinician**, so **A-7's *"cannot amend another clinician's note"* has no positive case to deny** —
there is no clinician-authored note anywhere in the system.

This traces to PB-036: the seeder ran under the `admin` session, so `forms.user` is `admin` on all
110 rows. The attribution is *present* (the PR-14 fix ensured that) but it is uniform.

### 4.3 A-10 not executed

*"Empty-spec ACL paths do not fail open"* requires targeted probes of the `aclCheckAcoSpec` /
`aclCheckIssue` call sites rather than HTTP probes. **Not attempted here**, and not claimed.

### 4.4 The UI-navigation legs

A-1, A-6, A-7 and A-8 each specify *"UI navigation **and** direct URL"*. **Only the direct-URL half
is evidenced above.** The UI half needs the manual browser session already outstanding for
RDY-0013/0014/0015 and RDY-0042 — one session can discharge all of them.

---

## 5. Acceptance

| Requirement | State |
|---|---|
| Every positive row succeeds | **Partial** — A-8 positive ✅, CTRL ✅; A-1/A-6/A-7 positive legs need the UI walk |
| Every negative row is denied | **32 of 32 executed probes denied correctly**, but **A-2 entirely, and the sensitivity legs of A-7 and A-8, could not be executed** |
| A single negative-row failure fails the matrix | **No failure occurred.** No row was *skipped silently* either — the four unexecutable rows are named above |

### Status: **RDY-0016 — NOT CLOSED. 32/32 executed probes pass; 4 rows unexecutable on the current dataset.**

**`Blocks`: G1 G3 G5.** No gate count moved (§0.0 Rule 3).

### What would close it

1. **Seed one sensitivity-flagged encounter** (RDY-0030) → unblocks A-2 and the sensitivity legs of
   A-7 and A-8. *Track D / dataset owner — Agent B has not mutated the dataset.*
2. **Author at least one form as a clinician** (not `admin`) → gives A-7 a positive case.
3. **Execute the A-10 call-site probes.**
4. **One manual browser session** for the UI-navigation halves, shared with RDY-0013/0014/0015/0042.

Items 1 and 2 are dataset changes against the RDY-0044-B baseline and need the same decision as
EV-083 §4.3. **Deliberately not applied.**

---

## 6. Reproduce

```bash
C:/openemr-stack/php/php.exe docs/evidence/harnesses/rdy0016-matrix.php
# expect: EXECUTED: 32   PASS: 32   FAIL: 0
```

The harness deletes every cookie jar on exit and prints no credential.
