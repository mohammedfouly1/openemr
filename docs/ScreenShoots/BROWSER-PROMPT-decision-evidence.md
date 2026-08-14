# BROWSER AGENT PROMPT — evidence for two pending Owner decisions

> Hand this file to the browser agent verbatim. It is self-contained.

---

## 0. Scope — read this first

You are producing **evidence that lets a human decide**. You are **not** deciding anything, and you
are **not** fixing anything.

Two of the four pending items **cannot be done in a browser at all**. Do not attempt them, do not
comment on them:

| Item | Why it is out of scope |
|---|---|
| RDY-0045 upstream target | `git fetch` — a command-line operation needing Owner authorisation |
| 66 unpushed commits | `git push` — same |

**Your scope is exactly two tasks: A and B below.**

---

## 1. Environment

| | |
|---|---|
| Start the stack | `C:\openemr-stack\start-openemr.ps1` (Apache + MariaDB; they do not survive a logoff) |
| Application | <http://localhost:8300/> |
| Login page | `http://localhost:8300/interface/login/login.php?site=default` |
| Health check | that URL returns **200**, roughly 9 KB |

If every page returns **HTTP 500**, Apache was started without `C:\openemr-stack\php` on `PATH`.
Stop `httpd`, prepend the path, start it again. Do not attempt any other repair.

## 2. Credentials — handling is a hard requirement

**Credentials live only in `C:\openemr-stack\secrets\thiqa-demo-credentials.json`.** Read them from
there.

**You must never:**

- write a password into your report, your notes, a filename, or any chat message
- leave a password visible in a screenshot, including the contents of that JSON file
- use the stock **`admin`** account. It is rotated and must never appear on screen or in any
  captured asset. **Use `n.alqahtani` when you need an administrator.**

### The six accounts

| Username | Display name | Role |
|---|---|---|
| `n.alqahtani` | Nadia Alqahtani | **Administrators** |
| `s.almutairi` | Sara Almutairi | Physicians |
| `y.alharbi` | Yousef Alharbi | Physicians |
| `m.alzahrani` | Maha Alzahrani | Clinicians (Clinical Assistant) |
| `r.aldosari` | Rana Aldosari | Front Office |
| `k.alotaibi` | Khalid Alotaibi | Accounting |

## 3. Read-only discipline — three browser runs have already corrupted this dataset

Attempts 1–3 of an earlier review **destroyed clinical records** and had to be rolled back. The two
causes are known and both are yours to avoid:

1. **A "take ownership" / record-lock confirmation dialog was accepted.** Accepting it took the lock
   and wrote defaults over real values.
2. **Opening a form's edit view persisted its JS defaults**, silently overwriting seeded data.

**Therefore, absolutely:**

- **Never click** Save, Submit, Update, Sign, or Delete — on any screen, for any reason.
- **Never accept a dialog.** If a confirm/lock/ownership dialog appears, **dismiss it**, screenshot
  it, and record it. Do not auto-accept dialogs globally.
- **Prefer read/print views over edit views.** If the only route to a record is an edit view, open
  it, capture, and **navigate away without saving**.
- Do not create, edit or delete any patient, encounter, appointment, form or user.
- **Running a report is safe** — reports are read-only. Task B is mostly reports.

### Integrity gate — mandatory, before and after

Capture the dataset signature **before you start** and **after you finish**:

```powershell
"C:/openemr-stack/mariadb/bin/mariadb.exe" -u root --host=127.0.0.1 --port=3306 openemr -N -B -e "SELECT CONCAT('patients=',(SELECT COUNT(*) FROM patient_data),' encounters=',(SELECT COUNT(*) FROM form_encounter),' appts=',(SELECT COUNT(*) FROM openemr_postcalendar_events),' vitals=',(SELECT COUNT(*) FROM form_vitals),' eyeexams=',(SELECT COUNT(*) FROM form_eye_base),' charges=',(SELECT COUNT(*) FROM billing WHERE activity=1));"
```

**The two must be identical.** If they differ, **stop immediately**, report it, and change nothing
further — a reset runbook exists at `docs/evidence/EV-044-demo-reset-runbook.md`, but restoring is
**not** your call.

---

## 4. TASK A — prove no growth chart can render (RDY-0023)

### What is being decided

RDY-0023 requires *"at least one record renders a growth chart."* Source analysis and a SQL age
query say this is impossible: the vitals form gates the entire paediatric block on **age ≤ 20**, and
the youngest seeded patient is **36**.

**That conclusion has never been confirmed through a browser.** The standing rule is *never infer a
browser result from a database value.* Your job is to confirm or refute it **on screen**.

### Steps

1. Log in as **`s.almutairi`** (Physician).
2. Choose **three** seeded patients (`SYN-` prefix) spanning the age range — include the youngest you
   can find and the oldest.
3. For each, open an encounter containing a **Vitals** form, in a **read/view** mode.
4. Capture the region of the form where growth-chart controls would appear — **per-section capture,
   not `fullPage`**.

### The positive control that stops this being vacuous

"I saw no growth-chart button" is worthless if the page never rendered or you were looking at the
wrong panel. **In the same screenshot, you must also show known-present vitals fields** — height,
weight, BP, pulse. That proves the form rendered and that you were looking in the right place.

**Record honestly:** a true positive control for the growth chart itself would need a paediatric
patient, and **none exists** — that is precisely the decision pending. So the correct finding is
**"absent, with page render proven"**, *not* "the feature is broken."

### Result to record

| Patient | Age | Vitals fields visible? | Growth-chart control present? |
|---|---|---|---|

**PASS** = 3 of 3 show vitals fields rendered **and** no growth-chart control.
**REFUTED** = a growth-chart control appears anywhere — capture it; that changes the decision.

---

## 5. TASK B — independently confirm two contested authorization verdicts (HR-03)

### What is being decided

Two reports were expected to **deny** certain roles. HTTP-level probing found they **allow** them.
The Product Owner must decide whether the grants change or the document changes — and should not
decide on a probe alone.

| Report | File | Documented expectation | Measured (probe) |
|---|---|---|---|
| **RPT-0009** Appointments Report | `interface/reports/appointments_report.php` | Clinician **DENY** | **ALLOW** |
| **RPT-0028** Patient Ledger by Date | `interface/reports/pat_ledger.php` | Physician **DENY** | **ALLOW** |

**RPT-0028 requires two GET parameters** — `form=` and `patient_id=` — or it returns an empty shell
that looks like a denial but is not one. Use a seeded patient id.

### Steps

For **each of the six accounts**, against **both reports**:

1. Log in.
2. **Menu route** — is the report reachable through the navigation menu? Record yes/no.
3. **Direct URL route** — navigate directly. Record the **HTTP status**, and the **verbatim on-screen
   message** if access is refused.
4. Screenshot each outcome.
5. Log out fully before the next account.

**Menu-hidden is not denied.** A report absent from the menu but reachable by direct URL is
**ALLOW** — that distinction is the entire point of this task.

### The negative control that stops this being vacuous

If your method cannot detect a denial at all, twelve ALLOW results prove nothing.

**So you must also perform one check that is expected to DENY:** as **`r.aldosari`** (Front Office),
request an administrator-only screen, e.g. `interface/super/edit_globals.php`.

- If that produces a **visible denial**, your harness can detect denials, and the ALLOW findings
  stand.
- If it **also allows**, **stop** — you have found something more serious than HR-03, and the twelve
  results above cannot be interpreted until it is explained.

### Result to record

| Account | Role | Report | In menu? | Direct URL status | Verdict | Screenshot |
|---|---|---|---|---|---|---|

Twelve rows, plus the negative control row.

---

## 6. Evidence to produce

Write **`docs/ScreenShoots/HR-03-BrowserVerification.md`** containing:

1. Dataset signature **before** and **after**, shown side by side.
2. Task A table + the three screenshots.
3. Task B table (12 rows) + the negative control row + screenshots.
4. **Method limits, stated plainly** — anything you could not test, any route you could not reach,
   any result you are unsure of.
5. Every screenshot referenced by filename.

**Screenshot naming:**

- Task A — `GC-<patient>-vitals.png`
- Task B — `HR03-<account>-<RPT>-<ALLOW|DENY>.png`
- Negative control — `HR03-negctl-r.aldosari-globals.png`

Store them under `docs/ScreenShoots/HR-03-evidence/`.

## 7. Prohibitions

- **Do not change any ACL grant, user, role or global setting.**
- **Do not record a verdict on behalf of the Product Owner**, and do not recommend which option to
  choose. You supply evidence; a human decides.
- **Do not mark anything CLOSED.** You cannot close a requirement.
- Do not write any password anywhere.
- Do not use the `admin` account.
- **Do not report a result you did not observe.** If something is untested, write "not tested".

## 8. Final response format

Report in this order:

1. **Integrity** — signature before/after, identical yes/no.
2. **Task A** — PASS / REFUTED, with the one-line reason.
3. **Task B** — the count of ALLOW vs DENY, and whether the negative control denied.
4. **Anything that surprised you.**
5. **What you could not do**, and why.

**If either integrity signature check fails, report that first and treat everything else as
unreliable.**
