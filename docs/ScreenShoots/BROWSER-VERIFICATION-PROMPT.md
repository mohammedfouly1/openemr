# THIQA / OPENEMR — BROWSER VERIFICATION RUN (Phase 2B)

You are a QA verification agent with a built-in browser. Your job is to **observe and report**,
not to fix, configure, or create anything.

---

## 0. CREDENTIALS

Read them from this file on the same machine:

`C:\openemr-stack\secrets\thiqa-demo-credentials.json`

- `accounts[]` → each has `username`, `password`, `role`
- `admin_account` → the rotated `admin` credential

If you cannot read files, the operator will paste them below:

```
admin        : ____________________   (rotated — the installer default no longer works)
n.alqahtani  : ____________________   Administrator
y.alharbi    : ____________________   Physician
s.almutairi  : ____________________   Physician
r.aldosari   : ____________________   Front Office
k.alotaibi   : ____________________   Accounting
m.alzahrani  : ____________________   Clinical Assistant
```

**Never write a password into your report, a screenshot annotation, or any file.**
If a password is visible on screen, do not screenshot that frame.

---

## 1. ENVIRONMENT

| | |
|---|---|
| Login URL | `http://localhost:8300/interface/login/login.php?site=default` |
| Screenshots | Save to `G:\My Drive\OpenEMR\docs\ScreenShoots` |
| Naming | `T<test>-<step>-<role>-<slug>.png` — e.g. `T1-01-frontoffice-menu.png` |

If the login page does not load, stop and report `ENVIRONMENT DOWN` — the stack may need
`C:\openemr-stack\start-openemr.ps1`.

---

## 2. ABSOLUTE SAFETY RULES

1. **Do NOT save or create a patient.** Some tests open the registration form — inspect it, then **Cancel**.
2. **Do NOT change any configuration, global, user, ACL, facility or list value.**
3. **Do NOT delete anything.**
4. **Do NOT run Administration → Backup.**
5. **Do NOT open Module Manager** (it can auto-register modules).
6. **Do NOT send email, SMS, eRx, lab or claim transmissions.**
7. Log out between roles, or use a fresh private window, so sessions never mix.
8. If a screen offers a database upgrade/migration prompt, **do not accept it** — screenshot and report.

---

## 3. HOW TO REPORT

For **every** test return exactly this block:

```
TEST:        T1
ROLE:        Front Office (r.aldosari)
URL:         <exact URL you were on>
EXPECTED:    <restate the expected result>
OBSERVED:    <what you actually saw>
SCREENSHOT:  T1-01-frontoffice-menu.png
RESULT:      PASS | FAIL | BLOCKED | DEFERRED
NOTES:       <anything unexpected, even if it passed>
```

- **PASS** — expected result seen exactly.
- **FAIL** — expected result not seen. **Do not soften this.** A FAIL is a valuable finding.
- **BLOCKED** — could not reach the screen (error, permission, crash). Screenshot the error.
- **DEFERRED** — cannot be judged yet for a stated reason (e.g. no data exists).

---

## TEST T1 — Menu role (RDY-0013)

**T1.1** Log in as **`r.aldosari`** (Front Office).
- Screenshot the full main menu bar: `T1-01-frontoffice-menu.png`
- **EXPECTED:** a reduced **front_office** menu. **No Administration. No Procedures. No Fees.**
- **PASS/FAIL QUESTION:** *Is the Administration menu absent for Front Office?*

**T1.2** Log out. Log in as **`y.alharbi`** (Physician).
- Screenshot: `T1-02-physician-menu.png`
- **EXPECTED:** a visibly **larger** menu than Front Office's.
- **PASS/FAIL QUESTION:** *Does the Physician menu contain more top-level items than Front Office's?*

**T1.3** Log out. Log in as **`n.alqahtani`** (Administrator).
- Screenshot: `T1-03-admin-menu.png`
- **EXPECTED:** Administration menu **present**.
- **PASS/FAIL QUESTION:** *Is Administration visible for the Administrator?*

---

## TEST T2 — Ophthalmology specialty (RDY-0014)

As **`n.alqahtani`** (Administrator).

**T2.1** Go to **Administration → Users**. Open user **`y.alharbi`**.
- Screenshot the specialty/taxonomy field: `T2-01-alharbi-taxonomy.png`
- **EXPECTED:** taxonomy shows **Ophthalmology** / **`207W00000X`** — *not* Family Medicine / `207Q00000X`.
- **PASS/FAIL QUESTION:** *Does y.alharbi show an Ophthalmology taxonomy?*

**T2.2** Repeat for **`s.almutairi`**. Screenshot: `T2-02-almutairi-taxonomy.png`

**T2.3** On either physician record, look at the **NPI** field.
- Screenshot: `T2-03-npi-empty.png`
- **EXPECTED:** NPI is **empty**. This is intentional for a Saudi deployment.
- **PASS/FAIL QUESTION:** *Is NPI empty?* (Empty = PASS. **Do not fill it in.**)

---

## TEST T3 — Facility identity and assignment (RDY-0015, RDY-0032)

As **`n.alqahtani`** (Administrator).

**T3.1** Go to **Administration → Facilities**.
- Screenshot: `T3-01-facility-list.png`
- **EXPECTED:** facility named **`Thiqa Demo Eye Clinic`**. The string **`Your Clinic Name Here` must NOT appear anywhere.**
- **PASS/FAIL QUESTION:** *Does the facility read "Thiqa Demo Eye Clinic", with no "Your Clinic Name Here" present?*

**T3.2** Go to **Administration → Users**. Open any two demo accounts.
- Screenshot: `T3-02-user-facility.png`
- **EXPECTED:** each shows the **Thiqa Demo Eye Clinic** facility.
- **PASS/FAIL QUESTION:** *Is the facility assigned on the user records?*

---

## TEST T4 — Timezone (RDY-0036)

As **`n.alqahtani`**.

**T4.1** Open any screen showing a current date/time (Calendar, or Administration → Globals → Locale).
- Screenshot: `T4-01-timezone.png`
- **EXPECTED:** time consistent with **Asia/Riyadh (UTC+03)**, and `Asia/Riyadh` set in Globals → Locale.
- **PASS/FAIL QUESTION:** *Is the timezone Asia/Riyadh and does displayed time match +03?*

---

## TEST T5 — SAR currency display (RDY-0037)

As **`n.alqahtani`**.

**T5.1** Open a screen showing money — Administration → Globals → Appearance/Locale (currency symbol), or a fee/price-level screen.
- Screenshot: `T5-01-currency.png`
- **EXPECTED:** currency renders as **SAR**, not `$`.
- **PASS/FAIL QUESTION:** *Does the currency display as SAR?*
- **If no money value is visible anywhere because there are zero patients/charges → mark DEFERRED**, not FAIL, and say so.

> **Note for the report:** this is *display configuration only*. There is no ISO 4217 field
> and no currency column. Never describe the product as supporting multi-currency.

---

## TEST T6 — Saudi registration lists (RDY-0038) ⚠ HIGHEST-VALUE TEST

As **`r.aldosari`** (Front Office) — the role that actually registers patients.

**T6.1** Start **Patient → New / Add Patient**.
- If the menu item is missing for Front Office, screenshot and mark **BLOCKED**, noting the known `front_office.json` defect (RDY-0042).

**T6.2** Open the **State / Region** dropdown. Screenshot it **expanded**: `T6-02-state-dropdown.png`
- **EXPECTED:** the **13 Saudi regions** (Riyadh, Makkah, Madinah, Eastern Province, Qassim, Hail, Tabuk, Northern Borders, Jazan, Najran, Al Bahah, Al Jawf, Asir) and **NO US states**.
- **PASS/FAIL QUESTION:** *Does the dropdown show Saudi regions and no US states?*
- **⚠ If Alabama / Alaska / Arizona still appear → this is a FAIL and must be reported loudly.** It would mean the form ignores the `activity = 0` flag, which is an explicitly untested assumption.

**T6.3** Open the **Country** dropdown. Screenshot expanded: `T6-03-country-dropdown.png`
- **EXPECTED:** **Saudi Arabia** present and selected by default.
- **PASS/FAIL QUESTION:** *Is Saudi Arabia the default country?*

**T6.4** Look at the phone field. Screenshot: `T6-04-phone.png`
- **EXPECTED:** hints at country code **+966**.
- **PASS/FAIL QUESTION:** *Does the phone field reflect +966?*

**T6.5** **CANCEL the form. Do not save the patient.** Confirm no patient was created.

---

## TEST T7 — Direct-URL authorization (RDY-0050, 0051, 0054)

Paste each URL **directly into the address bar** while logged in as the stated role.
This is a *direct-URL* test — do not navigate via menus.

| # | Log in as | URL | EXPECTED | Screenshot |
|---|---|---|---|---|
| T7.1 | `r.aldosari` (Front Office) | `http://localhost:8300/interface/reports/patient_list.php` | **Access denied. No patient list. No CSV.** | `T7-01-fo-patientlist-denied.png` |
| T7.2 | `k.alotaibi` (Accounting) | same URL | **Access denied** | `T7-02-acct-patientlist-denied.png` |
| T7.3 | `m.alzahrani` (Clinical Assistant) | same URL | **Access denied** | `T7-03-clin-patientlist-denied.png` |
| T7.4 | `y.alharbi` (Physician) | same URL | **Report OPENS** | `T7-04-phys-patientlist-allowed.png` |
| T7.5 | `r.aldosari` | `http://localhost:8300/interface/reports/unique_seen_patients_report.php` | **Access denied** | `T7-05-fo-uniqueseen-denied.png` |
| T7.6 | `r.aldosari` | `http://localhost:8300/interface/reports/amc_full_report.php` | **Access denied** | `T7-06-fo-amc-denied.png` |
| T7.7 | `k.alotaibi` (Accounting) | `http://localhost:8300/interface/reports/charts_checked_out.php` | **Access denied** | `T7-07-acct-chartsout-denied.png` |
| T7.8 | `r.aldosari` (Front Office) | same URL as T7.7 | **Report OPENS** (Reception legitimately needs chart tracking) | `T7-08-fo-chartsout-allowed.png` |
| T7.9 | `y.alharbi` (Physician) | `http://localhost:8300/controller.php?controller=hl7` | **Access denied** | `T7-09-phys-hl7-denied.png` |
| T7.10 | `n.alqahtani` (Administrator) | same URL as T7.9 | **Allowed** | `T7-10-admin-hl7-allowed.png` |

**PASS/FAIL QUESTION for each row:** *Did the page behave exactly as EXPECTED?*

**⚠ Any row where a "denied" case instead shows report content, a table of patients, or triggers a
CSV download is a CRITICAL FAIL — report it immediately and stop that sub-test.**

---

## TEST T8 — Audit log + tamper report (D-1 flagship)

As **`n.alqahtani`** (Administrator).

**T8.1** Open **Reports → Audit Log Tamper Report**
(direct: `http://localhost:8300/interface/reports/audit_log_tamper_report.php`).
- Screenshot: `T8-01-tamper-report.png`
- **EXPECTED:** **"No audit log tampering detected."**
- **PASS/FAIL QUESTION:** *Does the tamper report return a clean result?*

**T8.2** Open **Administration → Logs** (audit log viewer). Screenshot: `T8-02-audit-log.png`
- **EXPECTED:** the log loads and shows recent events, including your own logins from this run.
- **PASS/FAIL QUESTION:** *Does the audit log load and show this session's activity?*

---

## TEST T9 — Login hygiene

**T9.1** At the login page, attempt `admin` with the password **`pass`** (the old installer default).
- Screenshot the result: `T9-01-old-admin-rejected.png`
- **EXPECTED:** **login REJECTED.**
- **PASS/FAIL QUESTION:** *Is the old installer default refused?*
- **⚠ If it is accepted, that is a CRITICAL SECURITY FAIL — report immediately.**

**T9.2** Confirm each of the six demo accounts can log in. Screenshot one successful post-login screen: `T9-02-login-ok.png`
- **PASS/FAIL QUESTION:** *Do all six accounts authenticate?* State how many of 6.

---

## TEST T10 — General observation sweep

While moving through the above, note and screenshot anything matching:

1. The word **"OpenEMR"** visible anywhere in the UI chrome (title bar, header, footer, About).
   → `T10-01-openemr-string.png`. *Note: one occurrence on the login page is expected and intentional.*
2. Any **donation**, **review**, or **acknowledgement** link. → `T10-02-vendor-link.png`
3. Any **`$`** currency symbol. → `T10-03-dollar.png`
4. Any **US state list** anywhere. → `T10-04-us-state.png`
5. Any PHP error, warning, stack trace, or blank page. → `T10-05-error.png`
6. Any screen offering a **database upgrade**. → `T10-06-upgrade-prompt.png` (**do not accept it**)

---

## FINAL SUMMARY (return at the end)

```
ENVIRONMENT:        UP / DOWN
TESTS RUN:          __ of 10
PASS:               __
FAIL:               __
BLOCKED:            __
DEFERRED:           __
CRITICAL FAILURES:  <list, or NONE>

RDY-0013 menu role .................. PASS / FAIL / BLOCKED
RDY-0014 ophthalmology specialty .... PASS / FAIL / BLOCKED
RDY-0015 facility assignment ........ PASS / FAIL / BLOCKED
RDY-0032 facility identity .......... PASS / FAIL / BLOCKED
RDY-0036 timezone ................... PASS / FAIL / BLOCKED
RDY-0037 SAR display ................ PASS / FAIL / DEFERRED
RDY-0038 Saudi registration lists ... PASS / FAIL / BLOCKED
RDY-0050/0051/0054 direct-URL auth .. PASS / FAIL
D-1 audit tamper report ............. PASS / FAIL
Old admin credential rejected ....... PASS / FAIL

SCREENSHOTS SAVED:  __ files in G:\My Drive\OpenEMR\docs\ScreenShoots
ANYTHING CREATED/CHANGED: must be NONE — confirm explicitly
```

**Do not infer any result from a database value, a config file, or an assumption.
Report only what the browser actually displayed.**
