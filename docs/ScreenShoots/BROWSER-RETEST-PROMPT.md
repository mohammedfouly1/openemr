# THIQA / OPENEMR — BROWSER RE-TEST RUN (Phase 2B, Round 2)

You are a QA verification agent with a built-in browser. **Observe and report only.**
Do not fix, configure, or create anything.

This is a **re-test** after two defects found in Round 1 were fixed. Your job is to confirm the
fixes, unblock what Round 1 could not reach, and re-check that nothing regressed.

---

## 0. CREDENTIALS

Read from `C:\openemr-stack\secrets\thiqa-demo-credentials.json`:
- `accounts[]` → `username`, `password`, `role`
- `admin_account` → the rotated `admin` credential

If you cannot read files, the operator will paste them:

```
admin        : ____________________   (rotated — installer default must NOT work)
n.alqahtani  : ____________________   Administrator
y.alharbi    : ____________________   Physician
s.almutairi  : ____________________   Physician
r.aldosari   : ____________________   Front Office
k.alotaibi   : ____________________   Accounting
m.alzahrani  : ____________________   Clinical Assistant
```

**Never write a password into your report, a screenshot, or any file.**

---

## 1. ENVIRONMENT

| | |
|---|---|
| Login URL | `http://localhost:8300/interface/login/login.php?site=default` |
| Screenshots | `G:\My Drive\OpenEMR\docs\ScreenShoots` |
| Naming | `R<test>-<step>-<slug>.png` — the `R` prefix keeps Round 2 separate from Round 1 |

If the login page does not load, report `ENVIRONMENT DOWN` and stop.

---

## 2. WHAT CHANGED SINCE ROUND 1 (context, do not re-fix)

1. **Product Registration / telemetry modal suppressed.** The `product_registration` record now has
   `opt_out = 1`, `telemetry_disabled = 1`. **It must no longer appear.**
2. **`OPENSSL_CONF` set and Apache restarted.** OAuth2 key generation now works; `oaprivate.key`
   and `oapublic.key` exist. `/apis/dispatch.php` no longer returns HTTP 500.
   **This is the suspected root cause of the Round-1 "second admin page returns HTTP 400" defect —
   R2 exists to prove or disprove that.**

---

## 3. SAFETY RULES (unchanged)

1. **Do NOT save or create a patient.** Inspect forms, then Cancel.
2. **Do NOT change any configuration, global, user, ACL, facility or list value.**
3. **Do NOT delete anything. Do NOT run Backup. Do NOT open Module Manager.**
4. **Do NOT accept any database upgrade prompt** — screenshot it and report.
5. **Do NOT click Submit on any telemetry/registration dialog** if one appears.
6. Log out between roles, or use a fresh private window.

---

## 4. REPORTING FORMAT

```
TEST:        R1
ROLE:        Administrator (n.alqahtani)
URL:         <exact URL>
EXPECTED:    <restate>
OBSERVED:    <what you actually saw>
SCREENSHOT:  R1-01-....png
RESULT:      PASS | FAIL | BLOCKED | DEFERRED
NOTES:       <anything unexpected, even on a PASS>
```

**FAIL is a valuable finding. Do not soften it. Do not infer any result from a database value.**

---

# TEST R1 — Telemetry modal must be GONE  *(verifies FIX-1)*

**R1.1** Log in as **`n.alqahtani`** (Administrator). Wait ~10 seconds on the landing screen.
- Screenshot the full landing screen: `R1-01-admin-landing-no-modal.png`
- **EXPECTED:** **NO** "Product Registration" modal. No "OpenEMR Foundation" text. No email box. No telemetry consent checkbox.
- **PASS/FAIL QUESTION:** *Is the OpenEMR Product Registration modal absent?*
- **If it still appears: screenshot it, click NOTHING, and mark FAIL.**

**R1.2** Log out, log back in as Administrator a second time. Screenshot: `R1-02-admin-relogin-no-modal.png`
- **EXPECTED:** still no modal.
- **PASS/FAIL QUESTION:** *Does it stay absent across logins?*

---

# TEST R2 — The HTTP 400 defect  ⚠ HIGHEST-VALUE  *(verifies FIX-2's knock-on effect)*

Round 1: the **first** admin page in a session worked; the **second** returned HTTP 400 with an
empty body. Do **not** log out between these steps — that is the whole point.

**In ONE unbroken Administrator session (`n.alqahtani`), open these in order:**

| Step | Navigate to | Screenshot |
|---|---|---|
| R2.1 | Admin → Users (`/interface/usergroup/usergroup_admin.php`) | `R2-01-users-list.png` |
| R2.2 | Admin → Clinic → Facilities (`/interface/usergroup/facilities.php`) | `R2-02-facilities.png` |
| R2.3 | Admin → Config → Globals (`/interface/super/edit_globals.php`) | `R2-03-globals.png` |
| R2.4 | Admin → System → Logs (`/interface/logview/logview.php`) | `R2-04-logview.png` |
| R2.5 | Admin → Users again (back to the list) | `R2-05-users-list-again.png` |

- **EXPECTED:** **all five load normally.** No HTTP 400. No empty body. No blank modal.
- **PASS/FAIL QUESTION for each:** *Did the page render, with no 400 and no blank frame?*
- **Report the exact step number where any 400 first appears**, if it does.
- Open the browser devtools **Network** tab and note any request returning **400** or **500**. Screenshot the network panel: `R2-06-network-errors.png`

---

# TEST R3 — RDY-0014 · Ophthalmology specialty  *(was BLOCKED in Round 1)*

As **`n.alqahtani`** (Administrator).

**R3.1** Admin → Users → click **`y.alharbi`**.
- Screenshot the opened user record, showing the specialty/taxonomy field: `R3-01-alharbi-taxonomy.png`
- **EXPECTED:** the record **opens** (Round 1 it returned HTTP 400 empty) and taxonomy reads **Ophthalmology / `207W00000X`** — *not* Family Medicine / `207Q00000X`.
- **PASS/FAIL QUESTION:** *Does y.alharbi's record open, and does it show an Ophthalmology taxonomy?*

**R3.2** Same for **`s.almutairi`**. Screenshot: `R3-02-almutairi-taxonomy.png`

**R3.3** On either physician record, check the **NPI** field. Screenshot: `R3-03-npi-empty.png`
- **EXPECTED:** NPI **empty** — intentional for a Saudi deployment.
- **PASS/FAIL QUESTION:** *Is NPI empty?* (Empty = PASS. **Do not fill it in.**)

**R3.4** Check a **non-physician** — open **`k.alotaibi`** (Accounting). Screenshot: `R3-04-accounting-taxonomy.png`
- **EXPECTED:** taxonomy is **not** Ophthalmology (only the two physicians were changed).
- **PASS/FAIL QUESTION:** *Is Accounting's taxonomy something other than 207W00000X?*

---

# TEST R4 — RDY-0015 · Per-user facility assignment  *(was BLOCKED in Round 1)*

As **`n.alqahtani`** (Administrator).

**R4.1** Open each of the six demo users in turn and read the **Facility** field.
- Screenshot two of them: `R4-01-user-facility-1.png`, `R4-02-user-facility-2.png`
- **EXPECTED:** every one shows **Thiqa Demo Eye Clinic**.
- **PASS/FAIL QUESTION:** *Do all six demo accounts show the Thiqa Demo Eye Clinic facility?* State how many of 6 you could confirm.

**R4.2** Admin → Clinic → Facilities. Screenshot: `R4-03-facility-list.png`
- **EXPECTED:** exactly one facility, **Thiqa Demo Eye Clinic**; **`Your Clinic Name Here` appears nowhere**.
- **PASS/FAIL QUESTION:** *Is the facility correctly named with no installer default present?*

---

# TEST R5 — Audit log results  *(was PARTIAL in Round 1)*

As **`n.alqahtani`** (Administrator).

**R5.1** Admin → System → Logs. Set a date range covering **today**, then click **Submit**.
- Screenshot the **results table**: `R5-01-audit-log-results.png`
- **EXPECTED:** results render — actual log rows, including this session's logins. Round 1 the submit returned 400 and the table stayed empty.
- **PASS/FAIL QUESTION:** *Does the audit log return visible rows?*

**R5.2** Reports → Audit Log Tamper Report (`/interface/reports/audit_log_tamper_report.php`).
- Screenshot: `R5-02-tamper-report.png`
- **EXPECTED:** **"No audit log tampering detected."**
- **PASS/FAIL QUESTION:** *Is the tamper report clean?*

**R5.3** Widen the tamper report's date range to cover **the last 7 days**, submit again.
- Screenshot: `R5-03-tamper-report-7day.png`
- **EXPECTED:** still clean, over a larger row set.
- **PASS/FAIL QUESTION:** *Still clean across the wider range?*

---

# TEST R6 — API / OAuth2  *(verifies FIX-2 directly)*

**R6.1** In the browser address bar, open: `http://localhost:8300/apis/default/api/version`
- Screenshot: `R6-01-api-version.png`
- **EXPECTED:** **HTTP 200** with a JSON version payload. Round 1 this area returned HTTP 500 with an OpenSSL key error.
- **PASS/FAIL QUESTION:** *Does the API return 200 JSON rather than a 500 error?*

**R6.2** **Record the exact version numbers shown in that JSON.**
- **CONTEXT:** a known mismatch is under investigation — the code is **8.2.0** but the database may report **8.3.0-dev**.
- **REPORT (not pass/fail):** write down exactly what `v_major`, `v_minor`, `v_patch`, `v_tag` and `v_database` say.

**R6.3** Check the footer/About for a displayed product version. Screenshot: `R6-03-about-version.png`
- **REPORT:** what version does the UI show a user? Does it match R6.2?

---

# TEST R7 — RDY-0042 · Front Office "Add Patient" deliberate re-check

Round 1 found this defect **absent**, contradicting the original audit. Confirm carefully.

**R7.1** Log in as **`r.aldosari`** (Front Office). Screenshot the Patient menu expanded: `R7-01-fo-patient-menu.png`
- **EXPECTED:** an "Add Patient" / "New Patient" item is present.
- **PASS/FAIL QUESTION:** *Can Front Office see a way to add a patient?*

**R7.2** Click it. Screenshot the loaded form: `R7-02-fo-addpatient-form.png`
- **EXPECTED:** the registration form loads with no error.
- **PASS/FAIL QUESTION:** *Does the Add Patient form load for Front Office?*

**R7.3** **CANCEL. Do not save.** Confirm no patient was created.

---

# TEST R8 — Regression check on Round-1 passes

Confirm the three closed requirements still hold after the fixes.

**R8.1** As **`r.aldosari`** — count top-level menu items. Screenshot: `R8-01-fo-menu.png`
- **EXPECTED:** still the reduced front_office menu, **no Administration / Procedures / Fees**.

**R8.2** On the Add Patient form — open **State/Region** and **Country** dropdowns. Screenshots: `R8-02-state.png`, `R8-03-country.png`
- **EXPECTED:** still **13 Saudi regions, no US states**; Saudi Arabia still default.

**R8.3** As **`n.alqahtani`** — Globals → Locale. Screenshot: `R8-04-locale.png`
- **EXPECTED:** Time Zone **Asia/Riyadh**, Currency Designator **SAR**, Telephone Country Code **966**.

**R8.4** Direct-URL spot check — as **`r.aldosari`**, paste:
`http://localhost:8300/interface/reports/patient_list.php`
- Screenshot: `R8-05-fo-patientlist-denied.png`
- **EXPECTED:** still **denied**, no patient list, no CSV.
- **⚠ If this now OPENS, that is a CRITICAL REGRESSION — report immediately.**

---

# TEST R9 — Observation sweep (round 2)

Note and screenshot any of:

1. Any **"OpenEMR"** string visible in UI chrome, About, footer, or a modal → `R9-01-openemr-string.png`
   *(One occurrence on the login page is expected and intentional. Report anything beyond that.)*
2. Any **telemetry, registration, donation, review or acknowledgement** prompt → `R9-02-vendor-prompt.png`
3. Any **`$`** currency symbol → `R9-03-dollar.png`
4. Any **US state** in any dropdown → `R9-04-us-state.png`
5. Any **PHP error, warning, stack trace, blank page, HTTP 400/500** → `R9-05-error.png`
   **Include the devtools Network panel** for any 400/500.
6. Any **database upgrade prompt** → `R9-06-upgrade-prompt.png` (**do not accept**)

---

# FINAL SUMMARY

```
ENVIRONMENT:        UP / DOWN
TESTS RUN:          __ of 9
PASS:  __    FAIL:  __    BLOCKED:  __    DEFERRED:  __
CRITICAL FAILURES:  <list, or NONE>

FIX-1  telemetry modal gone .............. PASS / FAIL
FIX-2  API 200 (no OpenSSL 500) .......... PASS / FAIL
R2     admin pages, 5 in one session ..... PASS / FAIL   (first failing step: ____)
RDY-0014 ophthalmology taxonomy .......... PASS / FAIL / BLOCKED
RDY-0015 per-user facility ............... PASS / FAIL / BLOCKED   (__ of 6 confirmed)
R5     audit log returns rows ............ PASS / FAIL
R5     tamper report clean ............... PASS / FAIL
RDY-0042 Front Office Add Patient ........ PRESENT / ABSENT
R8     regression (menu/lists/locale/auth)  PASS / FAIL

VERSION REPORTED BY API:   v_major=__ v_minor=__ v_patch=__ v_tag=____ v_database=____
VERSION SHOWN IN UI:       ____________

SCREENSHOTS SAVED:  __ files (R-prefixed) in G:\My Drive\OpenEMR\docs\ScreenShoots
ANYTHING CREATED/CHANGED:  must be NONE — confirm explicitly
```

**Report only what the browser actually displayed. Never infer a result from a database value,
a config file, or an assumption.**
