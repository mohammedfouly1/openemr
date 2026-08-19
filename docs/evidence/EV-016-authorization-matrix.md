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

### 4.1 ✅ RESOLVED 2026-08-19 (PB-410) — sensitivity-flagged encounter seeded and live-tested

*Historical (as originally written): all 72 encounters were `sensitivity = 'normal'`; RDY-0030 not
yet seeded.* **Both preconditions since resolved.** A sensitivity-flagged encounter was added
2026-08-19 (`SYN-0014` pid 14, encounter 31, 2026-07-12, `sensitivity='high'`; encounter 61,
2026-04-28, `sensitivity='normal'`, same patient, for a same-patient control), and a live browser
session exercised it the same day (PB-410, fifth browser-check agent):

| Row | Result |
|---|---|
| **A-2** | Front Office (`r.aldosari`) — Visit History renders `"(Encounters not authorized)"` for the whole screen: blocked at a coarser gate than sensitivity, for either encounter, regardless of flag. **Not the redaction path** — a stronger, screen-level denial |
| **A-7** | Physician (`y.alharbi`, not the encounter's own author — provider_id 6, encounter's is 7) — the `sensitivity='high'` encounter (2026-07-12) renders **fully unredacted**: Reason/Form "Ophthalmology consultation (SYNTHETIC DEMO) / SOAP", Coding "CPT4 - 99214", no "(No access)" anywhere. **New finding: this account's `physicians` ACL group is not gated by sensitivity at all**, even viewing a different clinician's patient — the original expected-behaviour text ("cannot see `high`-sensitivity encounters") does not hold for this role in this configuration. Flagged for whoever next revises §23.4's expected-behaviour column; not resolved here |
| **A-8** | Accounting (`k.alotaibi`, holds no `sensitivities` ACL grant) — **both** encounters (the `sensitivity='high'` 2026-07-12 row and the `sensitivity='normal'` 2026-04-28 row) render with date/provider/insurance visible and Issue/Reason/Coding showing literal `"(No access)"`. **This is the live confirmation that the row is redacted, not invisible** — matching `encounters.php:506-511/533-536` exactly. The identical redaction on both rows (regardless of sensitivity value) shows Accounting's denial is a blanket per-role restriction on those columns, not a per-record differential — a same-patient control the original static-code read could not establish |

**This is the sharpest finding in this entry, now closed out.** Sensitivity gating is a named
limitation in the claim register (L-28, MC-16, RDY-0057) and is the mechanism behind a Pillar 1
statement. **It has now been exercised live, in three directions, on real data.** The
redaction-not-invisibility qualification (`EV-056-057-088` §2.2/§2.3) is confirmed for the
non-clinical roles it describes; the Physician finding is a new, separate nuance not yet reflected
in the qualification text.

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

### 4.3 A-10 — the two live-exploitable paths are fixed (updated 2026-08-19)

*"Empty-spec ACL paths do not fail open"* requires targeted probes of the `aclCheckAcoSpec` /
`aclCheckIssue` call sites rather than HTTP probes. **This section previously said "not attempted
here, and not claimed" — stale as of 2026-08-19.** `PR-19` (`docs/branding/adr/patch-records.md`)
landed the two fixes `EV-016-A10-fix-scope.md` §1a/§1b scoped, before this document was last
touched: `AclMain::aclCheckForm()` now denies when no registry row exists (was: fail open), and
`add_edit_issue.php`'s `$thistype` is validated against `$ISSUE_TYPES` before the ACL check (same
fail-open mechanism, different call site). Directly verified live in source 2026-08-19 — both diffs
present exactly as documented — and the two DB-backed unit tests (`AclMainTest.php`,
`testAclCheckFormDeniesWhenNoRegistryRow` / `testAclCheckFormStillResolvesRegisteredForm`)
independently re-run, still passing (3/3, 5 assertions). **Not yet done**: a live authenticated HTTP
round-trip (needs browser access — see `patch-records.md` PR-19's own "Not verified" note), and the
Owner decision on the 16 now-blocked form directories (`EV-016-A10-fix-scope.md` §3).

### 4.4 The UI-navigation legs

A-1, A-6, A-7 and A-8 each specify *"UI navigation **and** direct URL"*. **The direct-URL half is
evidenced above. The UI half for A-7/A-8's sensitivity legs is now also evidenced (§4.1, PB-410) —
real UI navigation to Visit History under Front Office, Accounting and Physician accounts.** A-1's
and A-6's UI halves remain unexercised.

---

## 5. Acceptance

| Requirement | State |
|---|---|
| Every positive row succeeds | **Partial** — A-8 positive ✅, CTRL ✅, A-7/A-8 sensitivity legs now evidenced via real UI (§4.1); A-1/A-6 positive legs still need the UI walk |
| Every negative row is denied | **32 of 32 originally executed probes denied correctly, plus the 3 live sensitivity-role probes in §4.1 (PB-410) all behaved as denials or redactions, none as an unintended positive** — A-10's two live-exploitable paths fixed and unit-verified (§4.3, 2026-08-19); live HTTP round-trip still pending |
| A single negative-row failure fails the matrix | **No failure occurred.** No row was *skipped silently* either |

### Status: **RDY-0016 — NOT CLOSED. 32/32 originally executed probes pass; A-2/A-7/A-8's sensitivity legs live-confirmed (PB-410, 2026-08-19); A-10's two live-exploitable paths fixed, unit-verified, and their orphaned-directory decision resolved (2026-08-19); only A-1/A-6's UI halves, most other matrix cells, and A-10's own live HTTP confirmation remain.**

**`Blocks`: G1 G3 G5.** No gate count moved (§0.0 Rule 3) — the item's own closure still needs the
remaining UI legs and matrix cells before the matrix as a whole can close.

### What would close it

1. ~~Seed one sensitivity-flagged encounter~~ **DONE 2026-08-19** — `SYN-0014` encounter 31,
   `sensitivity='high'`.
2. **Author at least one form as a clinician** (not `admin`) — **also incidentally satisfied
   2026-08-19**: RDY-0041's second D-7 run (PB-409) had `y.alharbi` author a real SOAP note on
   encounter 109, giving A-7 a genuine clinician-authored positive case for future re-runs of this
   specific leg.
3. ~~Execute the A-10 call-site probes~~ **FIXED AND UNIT-VERIFIED 2026-08-19** (`PR-19`) — live HTTP
   round-trip confirmation still outstanding, needs browser access.
4. **The remaining UI-navigation halves** (A-1, A-6) — the A-7/A-8 halves are now done (§4.1).
5. ~~`EV-016-A10-fix-scope.md` §3's Owner decision~~ **RESOLVED 2026-08-19** — Option A confirmed
   (all 16 directories stay blocked, a deliberate decision now, not just the fix's default).

---

## 6. Reproduce

```bash
C:/openemr-stack/php/php.exe docs/evidence/harnesses/rdy0016-matrix.php
# expect: EXECUTED: 32   PASS: 32   FAIL: 0
```

The harness deletes every cookie jar on exit and prints no credential.
