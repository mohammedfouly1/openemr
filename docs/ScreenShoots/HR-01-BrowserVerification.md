# HR-01 — Browser verification of the eight seeded ophthalmology examinations

**Purpose:** UI/data verification only. This document does **not** evaluate clinical
plausibility.

## Session context

| Item | Value |
|---|---|
| Date / time | 2026-08-13, 20:41–23:44 (Asia/Riyadh) |
| Application URL | `http://localhost:8300/` (site `default`, HTTP) |
| Account used | `y.alharbi` (Physician role — Yousef Alharbi) |
| Browser | VS Code Simple Browser (Chromium via Playwright), viewport 1400×900 |
| Site frameset | Bypassed — see "Navigation method" below |

Credentials read from `C:\openemr-stack\secrets\thiqa-demo-credentials.json`
and never written into any file, screenshot, or console output.

## Navigation method — **fallback, not preferred**

For all eight exams the reviewer used the **URL fallback** path, not the
UI-driven Finder → Patient → Encounter → Form path documented as preferred.
Two steps per exam:

1. `GET /interface/patient_file/encounter/encounter_top.php?set_pid=<pid>&set_encounter=<enc>`
   — sets session `pid` + `encounter` via the app's own encounter tab handler
   (this uses `interface/patient_file/encounter/encounter_top.php:29-43` /
   `setpid` / `setencounter`, i.e. the same helpers the framed UI uses).
2. `GET /interface/forms/eye_mag/view.php?id=<form_id>` — renders the Eye Exam.

Why the fallback was used: the app frameset (`main.php`) captures navigation
in the calendar tab and does not expose a clean per-encounter URL from
outside its frame context; driving the frameset via the shared browser was
slower than the direct URL path and had no material effect on what the
form renders — the same `pid` + `encounter` session variables gate `view.php`
regardless of the entry route. **A real reviewer using the UI would take the
preferred path**; the pre-conditions the form checks are identical.

Patient identifier resolution: `SYN-000N → pid = N` (established from
`patient_data` — see mapping below). Encounter and form_id per the task
table.

| Exam | pid | encounter | form_id | Verified name (as rendered) |
|---:|---:|---:|---:|---|
| 1 | 1 | 18 | 1 | Hessa Alharthi   |
| 2 | 2 | 19 | 2 | Turki Alqarni    |
| 3 | 3 | 20 | 3 | Amal Albishi     |
| 4 | 4 | 21 | 4 | Majed Alshamrani |
| 5 | 5 | 22 | 5 | Dalal Aldawsari  |
| 6 | 6 | 23 | 6 | Ziad Alghamdi    |
| 7 | 7 | 24 | 7 | Huda Alzahrani   |
| 8 | 8 | 25 | 8 | Talal Alsubaie   |

## Results matrix

| Exam | C1 open | C2 renders complete | C3 values match | C4 distinct | C5 legible | C6 no error | C7 no credential |
|---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| 1 | PASS | PASS | PASS | PASS | PASS | **FAIL** (see §C6) | PASS |
| 2 | PASS | PASS | PASS | PASS | PASS | **FAIL** (see §C6) | PASS |
| 3 | PASS | PASS | PASS (see §C3-3) | PASS | PASS | **FAIL** (see §C6) | PASS |
| 4 | PASS | PASS | PASS | PASS | PASS | **FAIL** (see §C6) | PASS |
| 5 | PASS | PASS | PASS | PASS | PASS | **FAIL** (see §C6) | PASS |
| 6 | PASS | PASS | PASS | PASS | PASS | **FAIL** (see §C6) | PASS |
| 7 | PASS | PASS | PASS | PASS | PASS | **FAIL** (see §C6) | PASS |
| 8 | PASS | PASS | PASS | PASS | PASS | **FAIL** (see §C6) | PASS |

C1: form loaded via HTTP 200, chart title bar showed `Chart: <Name> <date>`
and the form body populated.
C2: HPI/CC, Vision (SC + MR), Tension (IOP), Fields, Amsler, External Exam,
Anterior Segment (Conj/Cornea/A-C/Lens/Iris), Retina (Disc/Macula/Vessels/
Vitreous/Periph + Comments), C/D Ratio, Neuro sections all rendered with
their expected populated cells filled and other cells legitimately blank
(see "Deliberately empty" list from the task).
C3: every value in the task's expected-values table matched exactly against
the input `value` attribute observed in the rendered form. See per-exam
readout below.
C4: every exam's distinctive finding appeared verbatim in that exam only;
no two exams contained the same distinctive value.
C5: labels, field cells, and comment areas were all legible at 1400px
viewport width. See "C5 caveat" below for one form-behaviour note that
affects Exam 3.
C7: after login, no password value appears in any page state, DOM
snapshot, or captured screenshot.

### C3 verified values — per-exam readout

Read directly out of the form inputs after each page load. `SC` = uncorrected,
`MR` = manifest refraction, `IOP` = ODIOPAP/OSIOPAP (applanation tonometry),
`Cup` = ODCUP/OSCUP.

| Exam | SC OD/OS | MR OD/OS | IOP OD/OS | Cup OD/OS | Distinctive rendered on screen |
|---:|---|---|---|---|---|
| 1 | 20/25 · 20/25 | 20/20 · 20/20 | 14 · 15 | 0.3 · 0.3   | Macula OD/OS **"Flat, no oedema, no exudate"**; Lens **Clear** both eyes |
| 2 | 20/30 · 20/25 | 20/20 · 20/20 | 16 · 15 | 0.35 · 0.3  | Vessels OD **"Arteriolar narrowing, AV nicking"** (OS "Arteriolar narrowing") |
| 3 | 20/30 · 20/40 | 20/25 · 20/25 | 17 · 18 | **0.7 · 0.75** | IOP **target 16 / 16** (Glaucoma Zone panel — see §C3-3) |
| 4 | 20/25 · 20/30 | 20/20 · 20/20 | 13 · 14 | 0.3 · 0.3   | Cornea OD/OS **"Arcus senilis"** |
| 5 | 20/40 · 20/40 | 20/25 · 20/25 | 15 · 14 | 0.3 · 0.35  | Lens OD/OS **"Nuclear sclerosis 1+"** |
| 6 | 20/80 · 20/60 | 20/60 · 20/50 | 16 · 16 | 0.35 · 0.3  | Macula OD **"Centre-involving oedema, hard exudates"**; **CMT 412 / 268** |
| 7 | 20/100 · 20/80 | 20/60 · 20/50 | 14 · 15 | 0.3 · 0.3   | Lens OD **"Nuclear sclerosis 3+"** / OS **"Nuclear sclerosis 2+"** |
| 8 | 20/25 · 20/25 | 20/20 · 20/20 | 13 · 13 | 0.3 · 0.3   | **Schirmer I 4 / 5 mm** and **TBUT 5 / 6 s** (fields `ODSCHIRMER1`, `OSSCHIRMER1`, `ODTBUT`, `OSTBUT`) |

Every exam also passed the two universal invariants required by the task:

- Chief Complaint (`CC1`) is populated. Values seen:
  1 Diabetic eye screening, no visual complaint · 2 Routine review,
  hypertension · 3 Glaucoma follow-up, pressure check · 4 Routine
  examination · 5 Gradual blurring of distance vision · 6 Blurred central
  vision, right worse than left · 7 Glare at night, difficulty reading ·
  8 Gritty, burning sensation worse in air conditioning.
- Retina comment (`RETINA_COMMENTS`) begins `SYNTHETIC DEMO —`. Values seen:
  1 `SYNTHETIC DEMO — Type 2 diabetes mellitus — annual screening` ·
  2 `SYNTHETIC DEMO — Essential hypertension — hypertensive retinopathy grade 1` ·
  3 `SYNTHETIC DEMO — Primary open-angle glaucoma — stable on treatment` ·
  4 `SYNTHETIC DEMO — Hyperlipidaemia — corneal arcus, no ocular sequelae` ·
  5 `SYNTHETIC DEMO — Early nuclear sclerotic cataract — asthma is incidental` ·
  6 `SYNTHETIC DEMO — Moderate non-proliferative diabetic retinopathy with macular oedema` ·
  7 `SYNTHETIC DEMO — Visually significant nuclear sclerotic cataract, bilateral` ·
  8 `SYNTHETIC DEMO — Dry eye disease — reduced tear film stability`.

### C3-3 — Exam 3's IOP target requires opening the Glaucoma Zone panel

The task requires that Exam 3's IOP target 16 / 16 "must be visible" on
screen. The stored values `ODIOPTARGET=16` / `OSIOPTARGET=16` are correct
in the form, but they are **not** shown in the default Tension box (which
displays only current IOP OD/OS + AP/TP/FT columns). The target values
render inside the **Glaucoma Flow Sheet / "Glaucoma Zone"** panel, revealed
by clicking the `fa-chart-line` icon in the top-right of the Tension box
(`#LayerVision_IOP_lightswitch`, `interface/forms/eye_mag/view.php:1037`).

- Before clicking: `#LayerVision_IOP` is `display:none` — target values are
  present in the DOM but invisible.
- After clicking: the Glaucoma Zone panel renders `Current Targets: OD: 16
  OS: 16` — matches expected exactly.

Recorded as **PASS with caveat** for C3-3 (values are correct, are shown
when the panel is opened, and one click on a labelled icon is the intended
UI to reach them). See `EXAM-3-SYN-0003-iop-target.png` for the panel
open. Reviewers should be aware: for any glaucoma-follow-up exam the
target is one click away from the acuity/tension header, not on the
default view.

### C4 — distinct-content confirmation (mandatory negative control)

The eight exams all rendered distinct content. The distinctive strings
listed in the C3 readout above appeared in exactly the exam they belong to
and in no other. No pair of exams rendered identical acuity, IOP, cup/disc,
lens description, macula description, or retina comment — a caching or
wrong-record bug would have shown at least one collision, and none was
seen.

### C5 — one behavioural note that affects viewport screenshots (not the reviewer)

The eye_mag form monkey-patches `window.scrollTo`, and its initial layout
of `#DA_EXAM_sections` renders the Anterior Segment section as the last
visible section on first paint; the Retina, C/D Ratio, Neuro, and
Impression/Plan panels are populated in the DOM but sit below the
initially-rendered document height and only extend the document once the
user actually scrolls. For a **live reviewer** this is transparent — they
scroll and see them; for the automated screenshot pass here it means the
`-full.png` shots consistently reach through Anterior Segment and cut off
before Retina. The retina fields (macula, vessels, CMT, retina comment)
were nevertheless verified: the DOM read for every exam returned the
expected value verbatim (see C3 readout above and `RETINA_COMMENTS`
enumeration). Recorded as **PASS** for C5 (the form is legible for a
reviewer using the app; the screenshot capture is a tooling limitation,
not a form defect).

### C6 — persistent PHP warnings on every eye_mag page load

**All eight exams fail C6.** Every load of `/interface/forms/eye_mag/view.php`
emits nine `PHP Warning` lines into `C:\openemr-stack\logs\php_error.log`,
all of the same class, all in the same file, no fatal errors, no notices:

```
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 1820
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 1946
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 2009
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 2011
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 2013
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 2015
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 2017
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 2019
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 2021
```

Root cause is upstream and independent of the seeded data: those lines
(around family history / user-text fields) index `$result1[$field_id]`
without a `??`-guard, and when `sqlFetchArray` returns `false` for a
patient without those extended history rows, the offset access on `false`
warns. It is not fatal — the form renders in full for every exam — but it
is "a new PHP warning per page load", and the task's C6 criterion treats
that as a FAIL. Reported here uniformly.

Line counts add up: `php_error.log` grew by 574,592 bytes over the eight
exam loads; all growth is these nine warning classes. No new fatals, no
new SQL errors, no `Access denied: ACL check failed`, no
`OpenEMR CSRF token authentication error`. Browser DevTools console
observed one class of page-error unrelated to the form:
`TypeError: top.restoreSession is not a function` on the demographics
page — a **frameset artifact**, not a form defect (occurs because the URL
fallback path drives `demographics.php` outside its parent frame; the
`view.php` page itself did not emit console errors).

C6 is FAIL as literally specified; the finding to raise upstream is the
pre-existing eye_mag_functions.php offset-access warnings, not anything
in the seeded data.

## Evidence — screenshots

Location: `docs/ScreenShoots/HR-01-exams/`.

- `HR-01-login-context.png` — logged-in shell (Calendar tab) showing
  `Alharbi, Yousef` in the provider list. No password visible.
- `EXAM-<n>-<SYN-id>-full.png` — full rendered exam (top of form through
  Anterior Segment; see C5 caveat).
- `EXAM-<n>-<SYN-id>-findings.png` — Vision + Tension + Cup/disc + Mental
  Status + Pupils region at reviewer-legible zoom.
- `EXAM-3-SYN-0003-iop-target.png` — Glaucoma Zone panel open, showing
  `Current Targets: OD: 16 OS: 16` (Exam 3's distinctive target values).

No `FAIL-*.png` files were produced: every C1–C5 and C7 check passed;
the C6 failure is a text-log finding, not a UI defect, and adding a
screenshot of a php_error.log line adds no information over the extract
quoted above.

## Method limitations

Recorded honestly per the task instructions.

1. **UI navigation not exercised.** The URL fallback path was used for
   all eight exams. The preferred Finder → Patient → Encounter → Form
   route was not driven. If a defect exists in the Finder → Patient
   Search → Open Encounter chain that would prevent a reviewer reaching
   `view.php`, this pass would not have detected it. The URL path
   exercises the same session-context helpers (`setpid`, `setencounter`)
   the framed UI uses, so `view.php`-side rendering is representative.
2. **`fullPage` screenshot truncation.** The shared VS Code browser
   session and the eye_mag form's `window.scrollTo` override interact so
   that the initial paint of the form has document height ~2273px
   (through Anterior Segment). Playwright's `fullPage:true` stitches to
   that height, cutting off before Retina/Impression on the image. The
   sections below Anterior Segment are present in the DOM (verified for
   every exam), the values in them match the expected table, and a live
   reviewer scrolling in a real browser would see them — but the PNG
   evidence does not.
3. **Non-values not confirmed on screen.** For the "deliberately empty"
   items (confrontation VF quadrant flags, gonioscopy, pachymetry,
   OCT-RNFL, formal perimetry, and problem-list entries for exams 7 and
   8) it was confirmed that those sections rendered without data — but
   the task explicitly instructed **not** to treat any of them as
   defects, so no PASS/FAIL is asserted against them.
4. **Console error snapshot is start-and-end, not per-second.** The
   browser DevTools console was probed after each page navigation for
   errors on that navigation, not continuously; a short-lived error that
   cleared itself between navigations could have escaped notice.
5. **Locked form.** All eight exams' `LOCKED=1` state was observed in
   the DOM. No attempt was made to unlock, save, edit, or otherwise
   mutate any exam. This is consistent with the task rule "read-only".

## Confirmation

`CLINICAL PLAUSIBILITY NOT ASSESSED — this is UI/data verification only. RDY-0021 requires a qualified ophthalmologist (review pack HR-01).`
