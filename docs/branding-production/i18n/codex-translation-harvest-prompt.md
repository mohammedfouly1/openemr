# Codex Task Prompt — Bilingual Term Harvest (EN ↔ AR) for D‑7 and RDY‑0090 Surfaces

**Purpose of this file:** a single, self-contained, executable brief for the Codex agent
driving its built-in browser against the local Thiqa/OpenEMR instance. Everything below
the horizontal rule is the prompt — hand it over verbatim.

**Environment facts in this brief were verified live on 2026-08-19** (running app on
:8300, `languageChoice` select markup, `lang_id=22` = Arabic, account list in the
secrets file, catalogue row counts). Re-verify before a run on a later date.

---

## ROLE

You are a professional English→Arabic translator specialising in **healthcare
terminology and Health Information System (HIS) software UI strings**. You are also
operating a browser to gather evidence. You are *not* changing any application code in
this task — you are producing an inventory.

## MISSION (one sentence)

Walk every screen listed in §6, **twice** — once with the UI in English and once with
the UI in Arabic — and produce one consolidated bilingual term inventory that pairs each
visible English term with the Arabic term the product actually renders today (or records
that none is rendered).

---

## 1. OUTPUT CONTRACT — build this exactly

One UTF‑8 **with BOM** CSV (BOM is required so Excel and Google Sheets open Arabic
correctly), written to:

```
G:\My Drive\OpenEMR\docs\branding-production\i18n\harvest\d7-rdy0090-term-inventory.csv
```

The **first three columns are mandatory and must appear in this order** — they are the
deliverable the requester asked for. Columns 4–11 are diagnostic and make the file
actionable; fill them, but never at the cost of columns 1–3.

| # | Column | Content |
|---|---|---|
| 1 | `screen_or_menu` | Visible screen/menu name **plus** its stable ID, e.g. `Flow Board (SCR-0019)`. For a menu item, the path: `Main menu → Reports → Appointments` |
| 2 | `english_term` | The exact English string as rendered, trimmed (see §4.4 normalization) |
| 3 | `arabic_term` | The exact Arabic string rendered in the Arabic pass at the *same* DOM node. **Empty if the Arabic pass still shows English** |
| 4 | `status` | One of `TRANSLATED` / `MISSING` / `IDENTICAL_BY_DESIGN` / `PARTIAL` — see §7 |
| 5 | `gap_class` | Root cause when `status` ≠ `TRANSLATED`: `NO_DB_ROW` / `DB_ROW_EMPTY` / `NOT_WRAPPED` / `UNKNOWN` — see §7. Empty when `TRANSLATED` |
| 6 | `priority` | `P0`–`P4` per §8 |
| 7 | `role` | Role ID used to reach it, e.g. `ROLE-FO` |
| 8 | `route` | Route/component from §6, e.g. `/interface/patient_tracker/patient_tracker.php` |
| 9 | `element_type` | `page_title` / `heading` / `menu` / `button` / `link` / `field_label` / `placeholder` / `table_header` / `status` / `message` / `empty_state` / `loading` / `denial` / `tooltip` / `alt_text` / `print_header` / `print_footer` / `email_subject` / `email_body` |
| 10 | `method` | `BROWSER` (seen live) or `SOURCE` (read from a template/PHP file because the surface is not reachable — §6D) |
| 11 | `evidence` | Screenshot filename, or `file:line` for `SOURCE` rows |

Alongside it, write two companions in the same folder:

- `d7-rdy0090-coverage-summary.md` — per-screen counts (terms found, translated,
  missing), per-role counts, per-priority counts, and any screen you could **not** reach
  with the reason.
- `d7-rdy0090-gaps-priority.csv` — the same schema, filtered to `status != TRANSLATED`,
  sorted `priority` ascending then `screen_or_menu`. **This is the file the translation
  work will actually be driven from.**

Screenshots: `…\i18n\harvest\captures\<screen-id>-<en|ar>-<role>.png`, one per pass per
screen. Reference the filename in column 11.

---

## 2. ENVIRONMENT (verified 2026-08-19)

| Item | Value |
|---|---|
| App | <http://localhost:8300/> (confirmed LISTENING) |
| Login | `http://localhost:8300/interface/login/login.php?site=default` |
| Stack | Native Windows — Apache + PHP 8.3 + MariaDB under `C:\openemr-stack`. **There is no Docker on this machine**; ignore every `openemr-cmd` / `docker compose` instruction in `CLAUDE.md` |
| Start/stop | `C:\openemr-stack\start-openemr.ps1` / `stop-openemr.ps1` |
| DB (read-only use here) | `C:\openemr-stack\mariadb\bin\mariadb.exe -u root --host=127.0.0.1 --port=3306 openemr` |
| Arabic language id | `lang_id = 22`, `lang_code = ar` (confirmed in `lang_languages`) |
| Arabic definitions present | 6,291 rows in `lang_definitions` where `lang_id = 22` |
| Existing catalogue | `docs\branding-production\i18n\arabic-translations.csv` — 13,234 rows, columns `cons_id,def_id,english,arabic` |

If the app does not answer, run `start-openemr.ps1` first. Do **not** attempt any Docker
path.

## 3. CREDENTIALS — read, never write

All demo passwords live in **`C:\openemr-stack\secrets\thiqa-demo-credentials.json`**
(top-level `accounts[]` array of `{username, password}`, plus `admin_account`). Read the
password for each account from that file at the moment you need it.

**Never** write a password into the CSV, the summary, a screenshot caption, a log, a
commit, or your own chat output.

| Role ID | Demo role | Account | Used for |
|---|---|---|---|
| ROLE-PUBLIC | Unauthenticated visitor | *(none)* | Login and public identity |
| ROLE-ADMIN | Administrator | `n.alqahtani` | ACL, audit, configuration, reports |
| ROLE-PHY-1 | Physician | `y.alharbi` | D‑7 clinical journey and Eye Exam |
| ROLE-PHY-2 | Physician | `s.almutairi` | Secondary physician verification |
| ROLE-FO | Front Office | `r.aldosari` | Registration, calendar, check‑in, Flow Board |
| ROLE-ACC | Accounting | `k.alotaibi` | Fee sheet, reconciliation, ledger, CSV |
| ROLE-CA | Clinical Assistant | `m.alzahrani` | Negative authorization checks |
| ROLE-OPERATOR | Site operator | *(no demo account)* | Multi-site / site administration |
| ROLE-PATIENT | Portal patient | *(disabled)* | Portal surfaces only when enabled |
| ROLE-SYSTEM | Background system | *(none)* | Rendered emails |

> **Use `n.alqahtani` for every administrator surface. Do not log in as `admin`.**
> Project rule RDY‑0011 requires that `admin` never appear in a demo, and the installer
> default password `pass` no longer authenticates (it was rotated under PB‑020).

## 4. THE TWO-PASS METHOD

### 4.1 How to switch the UI language — verified mechanism

The login form renders a language selector — `<select name="languageChoice">` — because
the `language_menu_showall` global is `1`. **Arabic is `<option value="22">Arabic</option>`**
(confirmed in the live page source).

So: **language is chosen at login, and changing it means logging out and logging back
in.** There is no reliable in-session toggle. Your loop per role is therefore:

```
for each role account:
    log in WITHOUT touching the language select   → English pass  → harvest + screenshot
    log out
    log in WITH languageChoice = 22 (Arabic)      → Arabic  pass  → harvest + screenshot
    log out
```

Visit the screens **in the same order** in both passes so rows line up node-for-node.

### 4.2 Arabic renders right-to-left

For RTL languages the app substitutes an RTL-adapted stylesheet
(`interface/globals.php` ≈ lines 541‑545) and sets `language_direction` in the session.
Expect mirrored layout, right-aligned text, and reordered columns.

**RTL reordering is not a content difference.** Pair terms by their DOM node / semantic
role (same field, same button, same column), **not** by left-to-right screen position.
If you cannot confidently pair a node across the two passes, emit the row with
`status = PARTIAL` and say why in `evidence` — do not guess.

### 4.3 Harvest depth per screen

On every screen, in both passes, open and record:

- the browser/page **title**;
- the main heading and every section heading;
- **every menu and submenu label** — expand collapsed menus, open every dropdown;
- buttons and links (including icon buttons' tooltips / `title` / `aria-label`);
- form field labels **and** placeholder text;
- table column headings;
- status names / state chips;
- empty-result messages (trigger one: search for something that returns nothing);
- loading messages;
- authorization-denial messages (§6B — these are deliberately shown in the demo);
- print/PDF headings, headers and footers (open the print/PDF action, capture it);
- for emails: subject, sender display name, header, body, action links, footer;
- image `alt` text and tooltips **only when user-visible**.

### 4.4 Normalization before writing a row

- Trim leading/trailing whitespace; collapse internal runs of whitespace to one space.
- Strip a single trailing `:` from field labels; keep the label otherwise verbatim.
- Keep original capitalisation. A `*` required-marker is an observation, not part of the term.
- Strip interpolated values: `Showing 1 to 25 of 340` → record the template
  `Showing {n} to {n} of {n}` and note the substitution in `evidence`.
- **Deduplicate globally by `english_term`**, but keep one row per *screen* where the same
  English term appears with a *different* Arabic rendering — divergent translations of one
  source string are a finding, not noise.

---

## 5. SCOPE — what to record and what to leave alone

**In scope:** user-visible labels, headings, buttons, messages and table headings.

**Explicitly out of scope — do not translate, do not propose replacements, do not include
as terms:**

- The **Thiqa** product name (unless an approved Arabic product form has been explicitly
  selected — it has not been for this task).
- Patient or provider names.
- `SYN-0001` and other synthetic identifiers.
- CPT, ICD and drug codes; clinical abbreviations; measurements and units.
- URLs and route identifiers.
- Facility / tenant data.
- Copyright, GPL, OpenEMR Foundation and any other required attribution text.
- Third-party component names.
- Disabled OAuth / portal text — **record it, but mark `priority = P4` and note
  `feature disabled` in `evidence`**; it is not to be prepared for enablement here.
- Internal PHP, JavaScript, CSS, session or database identifiers.

**Fix-channel rule (read even though this task makes no fixes):** corrections to `xlt()`
strings go through `tools/branding/brand-strings.json` and the project's SET‑TRANSLATION
mechanism. **Never change an English `xlt()` source literal to force a visible
translation.** This task produces an inventory only — make no code edits at all.

---

## 6. SCREEN INVENTORY

### 6A — D‑7 journey screens

| Step | Stable ID | Visible screen name | Route/component | Primary role |
|---|---|---|---|---|
| 0 | SCR-0002 | Login | `/interface/login/login.php?site=default` | All roles |
| 0 | SCR-0004 | Thiqa main application | `/interface/main/tabs/main.php` | All authenticated |
| 1 | SCR-0015 | Patient Finder | `/interface/main/finder/dynamic_finder.php` | ROLE-FO |
| 2 | SCR-0017 | Add New Patient | `/interface/new/new.php` | ROLE-FO |
| 2 | SCR-0018 | Comprehensive Patient Registration | `/interface/new/new_comprehensive.php` | ROLE-FO |
| 3 | SCR-0015 | Duplicate patient search | Patient Finder | ROLE-FO |
| 4 | SCR-0009 | Calendar | `/interface/main/main_info.php` | ROLE-FO |
| 4 | SCR-0010 | Add/Edit Appointment | `/interface/main/calendar/add_edit_event.php` | ROLE-FO |
| 4 | SCR-0011 | Find Patient | `/interface/main/calendar/find_patient_popup.php` | ROLE-FO |
| 5 | SCR-0020 | Appointment/visit status | `/interface/patient_tracker/patient_tracker_status.php` | ROLE-FO |
| 6 | SCR-0019 / CAP-0011 | Flow Board | `/interface/patient_tracker/patient_tracker.php` | ROLE-FO |
| 7 | SCR-0027 | Medical Record Dashboard | `patient_file/summary/demographics.php` | ROLE-PHY-1 |
| 7 | SCR-0028 | Demographics | `patient_file/summary/demographics_full.php` | ROLE-PHY-1 |
| 8 | D7-ENC | Start/Open Encounter | Patient chart encounter selector | ROLE-PHY-1 |
| 9 | FORM-0005 / CAP-0036 | Vitals | `/interface/forms/vitals/` | ROLE-PHY-1 |
| 10 | FORM-0014 / CAP-0045 | Eye Exam | `/interface/forms/eye_mag/` | ROLE-PHY-1 |
| 10b | FORM-0014 | Glaucoma Zone | Eye Exam → Tension section | ROLE-PHY-1 |
| 11 | D7-LISTS | Problems | Patient dashboard / problem list | ROLE-PHY-1 |
| 11 | D7-LISTS | Allergies | Patient dashboard / allergy list | ROLE-PHY-1 |
| 11 | CAP-0053 | Medications | Patient dashboard / medication list | ROLE-PHY-1 |
| 12 | CAP-0106 | Prescription | Prescription editor | ROLE-PHY-1 |
| 12 | CAP-0107 | Printable Prescription | `/templates/prescription/` output | ROLE-PHY-1 |
| 13 | CAP-0048 | Electronic Signature | Form-level signature controls | ROLE-PHY-1 |
| 13a | D7-ACL-NOTE | Clinical-note denial | Visit History / form view | ROLE-FO |
| 14 | FORM-0002 | Fee Sheet | Encounter → Fee Sheet | ROLE-ACC |
| 15 | RPT-0012 | Appointments and Encounters | `/interface/reports/appt_encounter_report.php` | ROLE-ACC |
| 15b | RPT-0028 / CAP-0139 | Patient Ledger by Date | `/interface/reports/pat_ledger.php` | ROLE-ACC |
| 16 | RPT-0009 | Appointments Report | `/interface/reports/appointments_report.php` | ROLE-ACC |
| 16a | RPT-0009 | CSV Export | Appointments Report → CSV Export | ROLE-ACC |

Use ROLE-PHY-2 (`s.almutairi`) to re-verify steps 7–13 only; record its rows **only where
they differ** from ROLE-PHY-1.

### 6B — Role-boundary screens (denials are part of the demo — translate them)

| Screen | Administrator | Physician | Front Office | Accounting | Clinical Assistant |
|---|---|---|---|---|---|
| Patient Finder / Dashboard | Allow | Allow | Allow | Usually limited | Limited |
| Patient List report | Allow | Allow | Deny | Deny | Deny |
| Visit History | Allow | Allow | Deny message | Restricted / redacted | Deny |
| Clinical note | Allow | Allow | Deny | Deny | Restricted |
| Appointments and Encounters | Allow | Deny | Deny | Allow | Deny |
| Patient Ledger | Allow | *varies by configuration* | Deny | Allow | Deny |
| Audit Tamper Report | Allow | Deny | Deny | Deny | Deny |

Deliberately provoke each **Deny** cell and harvest the denial string in both passes.
Known denial strings that must appear in the inventory (confirm each, add any others you
find):

`Not Authorized` · `Access Denied` · `Encounters not authorized` ·
`History not authorized` · `Documents Not Authorized` · `(No access)` ·
`Patient Ledger by Date — Not Authorized`

Every denial row gets `element_type = denial` and **`priority = P0`** — a denial the
demo shows on purpose must never appear untranslated.

### 6C — Additional RDY‑0090 screens

| ID | Visible screen/output | Route/component | Role |
|---|---|---|---|
| 0090-01 / SCR-0002 | Thiqa Login | `/interface/login/login.php?site=default` | ROLE-PUBLIC |
| 0090-02 / SCR-0004 | Thiqa main shell | `/interface/main/tabs/main.php` | every authenticated role |
| 0090-03 | About Thiqa | `/interface/main/about_page.php` | every authenticated role — **see §9 hazard** |
| 0090-04 | Thiqa Site Administration | `/admin.php` | ROLE-OPERATOR → §6D |
| 0090-05 | Access Control List Administration | ACL menu / `gacl/admin/acl_admin.php` | ROLE-ADMIN |
| 0090-06 / SCR-0009 | Calendar | Main calendar | ROLE-FO, ROLE-ADMIN |
| 0090-07 / SCR-0019 | Flow Board | Patient tracker | ROLE-FO, ROLE-ADMIN |
| 0090-08 / SCR-0015 | Patient Finder | Dynamic finder | ROLE-FO, ROLE-PHY-1, ROLE-ADMIN |
| 0090-09 / SCR-0027 | Medical Record Dashboard | Patient dashboard | authorized clinical/admin |
| 0090-10 | Visit History | `patient_file/history/encounters.php` | all roles, incl. denial states |
| 0090-11 | Documents | `/controller.php?document&list` | authorized roles + denial state |
| 0090-12 | Patient Report / Clinical Summary | `patient_file/report/patient_report.php` | ROLE-PHY-1, ROLE-ADMIN |
| 0090-13 / RPT-0028 | Patient Ledger by Date | `reports/pat_ledger.php` | ROLE-ACC, ROLE-ADMIN |
| 0090-14 / RPT-0012 | Appointments and Encounters | `reports/appt_encounter_report.php` | ROLE-ACC, ROLE-ADMIN |
| 0090-15 | Financial Summary by Service Code | Financial Reports menu | ROLE-ADMIN, ROLE-ACC |
| 0090-16 / RPT-0053 | Audit Log Tamper Report | `reports/audit_log_tamper_report.php` | ROLE-ADMIN |
| 0090-17 / FORM-0014 | Eye Exam | `forms/eye_mag/` | ROLE-PHY-1 |
| 0090-18 | Eye Exam printable / PDF output | Eye Exam print/PDF action | ROLE-PHY-1 |
| 0090-19 | Patient statement print | Statement / billing output | ROLE-ACC |
| 0090-20 | Superbill | Encounter billing output | ROLE-ACC |
| 0090-21 / CAP-0107 | Printable prescription | Prescription print output | ROLE-PHY-1 |
| 0090-22 | Product Registration modal | Main application registration template | ROLE-ADMIN / operator |
| 0090-23 | OAuth Login | `templates/oauth2/oauth2-login.html.twig` | disabled feature → §6D |
| 0090-24 | Patient Portal Login | `/portal/` | disabled patient role → §6D |
| 0090-25 | Appointment reminder email | see §6D | ROLE-SYSTEM |
| 0090-26 | Password-reset email | see §6D | ROLE-SYSTEM |
| 0090-27 | Portal invitation email | see §6D | ROLE-SYSTEM |
| 0090-28 | System notification email | see §6D | ROLE-SYSTEM |

### 6D — Surfaces that cannot be browsed: harvest from source (`method = SOURCE`)

Three groups have no reachable live UI. Do **not** enable a disabled feature and do
**not** create an account to reach them. Read the template/source, extract the visible
strings, and cite `file:line` in `evidence`.

| ID | Where the strings live |
|---|---|
| 0090-04 (site admin) | `admin.php` — no ROLE-OPERATOR demo account exists |
| 0090-23 (OAuth login) | `templates/oauth2/oauth2-login.html.twig` (siblings: `oauth2-base`, `scope-authorize`, `patient-select`) |
| 0090-24 (portal login) | `/portal/` — patient role disabled |
| 0090-26 (password reset) | `templates/emails/patient/reset_credentials/message.html.twig` + `.text.twig` |
| 0090-27 (portal invitation) | `templates/emails/patient/portal_login/message.html.twig` + `.text.twig` |
| 0090-28 (system notification) | `templates/emails/system/system-notification.html.twig` + `.text.twig` |
| 0090-25 (appointment reminder) | **No Twig template exists under `templates/emails/`.** Candidate sources: `interface/batchcom/batch_reminders.php`, `interface/batchcom/emailnotification.php`. Locate the actual rendered subject/body and cite `file:line`; if you cannot establish it, record the screen with zero terms and say so in the coverage summary rather than inventing rows |

Also present and possibly user-visible in this product — include if so:
`templates/emails/patient/verify_email/message-verify-{success,failed}.html.twig` and
`templates/emails/partials/patient/email-message-fhir-access.*`.

---

## 7. CLASSIFYING EVERY GAP — this is the high-value output

An English term that is still English in the Arabic pass has **three very different root
causes**, and telling them apart is what makes this inventory worth producing. After the
browser work, reconcile each `MISSING` row against the catalogue:

```powershell
# does an Arabic definition exist for this exact English source string?
C:\openemr-stack\mariadb\bin\mariadb.exe -u root --host=127.0.0.1 --port=3306 openemr -e `
  "SELECT lc.cons_id, ld.definition
     FROM lang_constants lc
     LEFT JOIN lang_definitions ld ON ld.cons_id = lc.cons_id AND ld.lang_id = 22
    WHERE lc.constant_name = '<english term>';"
```

| `gap_class` | Meaning | What it implies |
|---|---|---|
| `NO_DB_ROW` | No `lang_constants` row for this English string at all | The string was never in the translation catalogue — needs a new constant |
| `DB_ROW_EMPTY` | Constant exists, `lang_id = 22` definition missing or empty | **Pure translation work** — this is what the translator gets |
| `NOT_WRAPPED` | A non-empty Arabic definition **exists** yet English still renders | A code defect: the string is hard-coded or not wrapped in `xlt()`. **Report it; do not fix it here.** These are usually the fastest wins |
| `UNKNOWN` | Reconciliation inconclusive | Explain in `evidence` |

`status` values:

- `TRANSLATED` — Arabic pass shows Arabic text.
- `MISSING` — Arabic pass shows the English string.
- `PARTIAL` — string is part Arabic, part English (very common in composed sentences).
- `IDENTICAL_BY_DESIGN` — correctly identical in both languages (a §5 exclusion such as a
  code, unit, or the Thiqa product name). Column 3 repeats the English value here.

Cross-check the catalogue file too — `arabic-translations.csv` (13,234 rows) — so you can
report how many gaps already sit in the existing proofreading backlog versus how many are
new discoveries not represented in it at all.

## 8. PRIORITY TIERS

| Tier | Rule |
|---|---|
| **P0** | Safety-critical **and every §6B denial string**. Match `english_term` against: `allerg`, `dose`, `dosage`, `prescri`, `drug`, `medication`, `lot`, `batch`, `expiration`, `contraindication`, `interaction`, `critical`, `panic`, `normal`, `abnormal`, `reaction`, `adverse` |
| **P1** | Highest visibility: `login`, `sign in`, `password`, `username`, `dashboard`, `home`, `appointment`, `patient`, `record`, `search`, `message`, `billing` |
| **P2** | Legal/financial: `invoice`, `statement`, `claim`, `payment`, `refund`, `consent`, `insurance`, `balance`, `copay`, `charge` |
| **P3** | Clinical daily forms: `vital`, `blood pressure`, `pulse`, `temperature`, `weight`, `height`, `history`, `exam`, `encounter`, `complaint`, `assessment`, `plan`, `review of systems` |
| **P4** | Everything else — setup, deep admin, disabled features |

Apply the **highest** matching tier. A P0 keyword anywhere in the term wins.

## 9. HOST HAZARDS AND RECOVERY

- **`about_page.php` (0090‑03) can hang this host.** A known Twig-render/session-lock
  interaction wedges the PHP session file lock — the request never returns, *and the next
  navigation in the same browser session hangs too*. Signature: `httpd.exe` CPU flat, not
  spinning. **Recovery:** restart Apache, then re-login:
  ```powershell
  Stop-Process -Name httpd -Force
  $env:PATH = "C:\openemr-stack\php;$env:PATH"   # mandatory — omitting it 500s every page
  Start-Process -FilePath 'C:\openemr-stack\apache\bin\httpd.exe' `
      -ArgumentList '-f','C:/openemr-stack/apache/conf/httpd.conf' -WindowStyle Hidden
  ```
  Visit 0090‑03 **last within its role's pass** so a hang costs the least work, and save
  the CSV incrementally so nothing is lost.
- **The repo lives on a Google Drive mount and is slow** (~28 KB/s effective). Expect long
  page loads; raise timeouts rather than concluding a page is broken.
- **Directory listings on `G:` go stale** — a file can exist yet be invisible to `ls`. Never
  declare a file absent on a single check.
- **Do not commit `sites/default/sqlconf.php`** — it holds local DB credentials.

## 10. ACCEPTANCE CHECKS — run these before reporting done

1. Every screen in §6A, §6B, §6C appears in the coverage summary with either a term count
   or an explicit unreachable-reason.
2. Every screen has **both** an `-en` and an `-ar` screenshot, except §6D rows.
3. Every §6B `Deny` cell produced at least one `element_type = denial`, `priority = P0` row.
4. Zero passwords anywhere in any output file.
5. The CSV opens in Excel with Arabic rendering correctly (BOM present).
6. Every `status = MISSING` row has a non-empty `gap_class`.
7. No application file was modified — `git status` shows only new files under
   `docs/branding-production/i18n/harvest/`.

## 11. HARD PROHIBITIONS

- Make **no** code changes. No `xlt()` literal edits, no `brand-strings.json` edits, no
  theme rebuild. This task is read-only against the application.
- Make **no** database writes. All the SQL in §7 is `SELECT` only.
- Do not enable OAuth, the patient portal, or any disabled feature.
- Do not create, rename or delete user accounts; do not log in as `admin`.
- Do not invent an Arabic term. Column 3 records **what the product renders today** —
  proposing better Arabic is a later, separate task. To flag a rendered Arabic term as
  wrong or non-clinical, say so in the coverage summary, never by overwriting column 3.
- If a screen cannot be reached after two honest attempts, record it as unreachable with
  the reason and move on. Do not loop.
