# HR-01 Browser Verification — Attempt 3 (v3) — **BLOCKED**

- **Date / time:** 2026-08-13, ~23:37 UTC (2026-08-14 02:37 Asia/Riyadh)
- **Account:** `y.alharbi` (role: Physician, display: Yousef Alharbi) — password never entered into any tool call, image, log, or file (session-cookie login path via `Invoke-WebRequest` from local `C:\openemr-stack\secrets\thiqa-demo-credentials.json`)
- **Browser:** Chromium via Playwright (VS Code integrated browser), viewport 1600×900
- **App URL:** http://localhost:8300/ (site `default`)
- **Dataset fingerprint:** `ad6ea86d64440478fe2ab4ada466aa516b0a58250aceaed099e4be1fe1858ce2` (marketing-mvp-seed-v1)

---

## 1. Outcome — **BLOCKED after Exam 1**

The **fail-fast rule (§2)** fired. Per-exam CLINHASH was re-run immediately after Exam 1 finished, and it did **not** match the pre-flight value. No further exams were opened. No repair or reset was attempted (§10).

- Pre-flight CLINHASH:      `7dcc4a6175f58801235f0627fa271689` — **MATCH** with required value
- Post-Exam-1 CLINHASH:     `6b23b44ce7e3fc2249fa17553d324b96` — **MISMATCH**
- Final CLINHASH:           `6b23b44ce7e3fc2249fa17553d324b96` (unchanged since Exam 1 close; browser navigated to `about:blank` after Exam 1 to prevent further writes)

Screenshots: [HR-01-v3-integrity-before.png](HR-01-exams-v3/HR-01-v3-integrity-before.png), [HR-01-v3-integrity-after.png](HR-01-exams-v3/HR-01-v3-integrity-after.png).

---

## 2. Dialog handler used — the point of §0

The **first executable line** on the Playwright page, before any navigation, was:

```js
page.removeAllListeners('dialog');
page.on('dialog', async d => { try { await d.dismiss(); } catch (e) {} });
```

`dismiss()`, never `accept()`. Handler was re-installed at the start of every `run_playwright_code` block as belt-and-braces. **Zero dialog events fired** during the entire Exam 1 sequence (`dialogEvents = []` returned from the capture block). The confirm() ownership prompt was **not** raised, and neither was `beforeunload`.

**The corruption was therefore not triggered by a dialog.** It was triggered by loading `interface/forms/eye_mag/view.php` itself. See §5.

---

## 3. Integrity — CLINHASH and LOCKSTATE

### 3a. CLINHASH

| Checkpoint            | Value                              | Vs required                       |
|-----------------------|------------------------------------|-----------------------------------|
| Pre-flight (§1)       | `7dcc4a6175f58801235f0627fa271689` | MATCH                             |
| After Exam 1          | `6b23b44ce7e3fc2249fa17553d324b96` | **MISMATCH — RUN HALTED HERE**    |
| Exams 2–8             | not run                            | —                                 |
| Final (post-halt)     | `6b23b44ce7e3fc2249fa17553d324b96` | unchanged since Exam 1            |

Nine hashes were expected (pre-flight + 8). Two were produced (pre-flight + Exam 1). The **first mismatch was at Exam 1**. Exams 2–8 were deliberately not opened.

### 3b. LOCKSTATE (`form_eye_locking`)

Before:
```
+----+--------+----------+
| id | locked | lockedby |
+----+--------+----------+
|  1 | NULL   | NULL     |
|  2 | NULL   | NULL     |
|  3 | NULL   | NULL     |
|  4 | NULL   | NULL     |
|  5 | NULL   | NULL     |
|  6 | NULL   | NULL     |
|  7 | NULL   | NULL     |
|  8 | NULL   | NULL     |
+----+--------+----------+
```

After:
```
+----+--------+------------+
| id | locked | lockedby   |
+----+--------+------------+
|  1 | 1      | 2121801105 |   <-- edit lock now held by y.alharbi's session
|  2 | NULL   | NULL       |
|  3 | NULL   | NULL       |
|  4 | NULL   | NULL       |
|  5 | NULL   | NULL       |
|  6 | NULL   | NULL       |
|  7 | NULL   | NULL       |
|  8 | NULL   | NULL       |
+----+--------+------------+
```

Per §3 rule, this lock change is **reported, not treated as failure, and not cleared**.

---

## 4. Delta on the corrupted record (form_id = 1, pid = 1, encounter 18, SYN-0001 Hessa Alharthi)

```
+----+---------+---------+-------------+-------------+----------+-------+-------+----------+
| id | ODIOPAP | OSIOPAP | ODIOPTARGET | OSIOPTARGET | AMSLEROD | ODVF1 | alert | oriented |
+----+---------+---------+-------------+-------------+----------+-------+-------+----------+
|  1 | 14      | 15      | 21          | 21          |    0     |   0   |   1   |    1     |
+----+---------+---------+-------------+-------------+----------+-------+-------+----------+
```

This is **exactly the corruption pattern §0 warned about**:

> "writing a target IOP of **21** and zeroed visual-field flags into patient records."

- `ODIOPTARGET` / `OSIOPTARGET` — forced from NULL to **21** on both eyes.
- `AMSLEROD` / `ODVF1` — set to `0` (was previously NULL).
- `alert` / `oriented` — set to `1` (was previously empty/NULL).
- `ODIOPAP`/`OSIOPAP` (14/15) and other seeded acuity/postseg/antseg values were **not** changed on Exam 1.

---

## 5. Root cause — why §0's fix is necessary but not sufficient

§0 identifies `confirm() → accept()` as the write trigger. That is correct for the case where a **prior lock is present** (the confirm dialog is only raised then). The seed dataset has `LOCKED = NULL` for all eight rows, so on the first open the confirm is **not** raised.

But `view.php` still executes a write path on initial open (the lock-acquire path). It persists the form's in-memory representation of "defaults" — including `ODIOPTARGET = 21` and zeroed visual-field flags — over the previously-NULL columns. This happened here despite:

- `dismiss()` handler installed as the first executable line,
- no `confirm()` raised,
- no `beforeunload` raised,
- no click, no Enter, no focus on any input,
- no visit to the save endpoint,
- only `page.goto()` navigation.

**Consequence for future attempts:** dismissing the dialog is necessary but **not** sufficient. A safe capture path must either

- capture from `report.php` (read-only view) instead of `view.php`, or
- capture directly from a rendered snapshot produced server-side without invoking `view.php`'s lock-acquire branch, or
- open the form only after having verified that the write path can be short-circuited (e.g. a URL parameter or a role-gated read-only view).

This is not a fix I attempted; it is a recommendation for whoever plans attempt 4.

---

## 6. Capture matrix — 8 exams × 9 sections + 3 extras

Legend: **CAPTURED** — file written to disk during this run. **NOT-RUN** — exam was not opened because the fail-fast rule halted the run.

| Exam | Section          | File                                     | Status              |
|------|------------------|------------------------------------------|---------------------|
| 1    | header           | `EXAM-1-SYN-0001-header.png`             | CAPTURED (see §7)   |
| 1    | hpi              | `EXAM-1-SYN-0001-hpi.png`                | CAPTURED (see §7)   |
| 1    | vision           | `EXAM-1-SYN-0001-vision.png`             | CAPTURED (see §7)   |
| 1    | tension          | `EXAM-1-SYN-0001-tension.png`            | CAPTURED (see §7)   |
| 1    | external         | `EXAM-1-SYN-0001-external.png`           | CAPTURED (see §7)   |
| 1    | antseg           | `EXAM-1-SYN-0001-antseg.png`             | CAPTURED (see §7)   |
| 1    | retina           | `EXAM-1-SYN-0001-retina.png`             | CAPTURED (see §7)   |
| 1    | impression       | `EXAM-1-SYN-0001-impression.png`         | CAPTURED (see §7)   |
| 1    | defaults         | `EXAM-1-SYN-0001-defaults.png`           | CAPTURED (see §7)   |
| 2    | all              | —                                        | NOT-RUN (halted)    |
| 3    | all + glaucoma-zone | —                                     | NOT-RUN (halted)    |
| 4    | all              | —                                        | NOT-RUN (halted)    |
| 5    | all              | —                                        | NOT-RUN (halted)    |
| 6    | all + macula-detail | —                                     | NOT-RUN (halted)    |
| 7    | all              | —                                        | NOT-RUN (halted)    |
| 8    | all + tearfilm   | —                                        | NOT-RUN (halted)    |

Extras (not run): `EXAM-3-SYN-0003-glaucoma-zone.png`, `EXAM-6-SYN-0006-macula-detail.png`, `EXAM-8-SYN-0008-tearfilm.png`.

Other artifacts produced: `HR-01-v3-login-context.png`, `HR-01-v3-integrity-before.png`, `HR-01-v3-integrity-after.png`.

---

## 7. Warning about the Exam 1 image set — do not treat as evidence

The nine Exam 1 PNGs were written to disk **but they represent state observed after** `view.php` had already mutated the record. The screen values they show for Exam 1 are the **post-mutation** values, not the seed dataset. Specifically:

- The `tension` / `vision` panels visible in Exam 1 images may show the post-mutation IOP targets (21 / 21) even though the seed had them NULL.
- Two of the section images (`external`, `retina`) came out with identical file sizes (22,758 bytes) — spot-checking `EXAM-1-SYN-0001-retina.png` shows it visually contains the HPI panel content, which suggests the `#RETINA_1` bounding-box clip did not resolve to the retina area during full-page capture. Attempt-2's proven pattern (§6) explicitly warns against `fullPage: true`; this attempt did use `fullPage: true` on the clip screenshot, which is likely why the retina image is wrong.

**Recommendation:** the Exam 1 image set should be treated as unusable both because (a) the underlying record was corrupted at open time, and (b) at least the retina file is visually mis-labelled. Do not rely on them for downstream verification.

---

## 8. Values-visible matrix (§7 of the prompt)

Because Exams 2–8 were not run and Exam 1's captured images reflect a mutated record, this matrix cannot be honestly filled in for any exam.

| Exam | Value                       | Status                                                                 |
|------|-----------------------------|------------------------------------------------------------------------|
| 1    | SC VA 20/25 · 20/25         | NOT-VISIBLE (image set unreliable per §7)                              |
| 1    | MR VA 20/20 · 20/20         | NOT-VISIBLE                                                            |
| 1    | IOP 14 · 15                 | NOT-VISIBLE                                                            |
| 1    | Cup/disc 0.3 · 0.3          | NOT-VISIBLE (retina image is misaligned; §7)                           |
| 1    | Macula flat / vessels normal| NOT-VISIBLE (retina image is misaligned; §7)                           |
| 2–8  | (all §7 values)             | NOT-CAPTURED — exam not opened                                         |

No value is recorded as CONFIRMED-IN-IMAGE. §10 rule ("never record CONFIRMED for a value you did not see") observed.

---

## 9. Defaulted "Normal" note (§8 of the prompt)

Not observable in this run — the `defaults` section was captured for Exam 1 only, and Exam 1's underlying record was mutated on open, so what the `defaults` screenshot for Exam 1 shows cannot be trusted as either "seeded finding" or "form default rendering".

The important point from §8 still stands as a general observation for downstream reviewers: on eye_mag/view.php, checkbox groups such as **Fields FTCF**, **Amsler Normal**, **Pupils Normal**, and **Mental Status Alert/Oriented/Mood-Affect Nml** render as if they were positive findings, but on a clean seed record they are the form's default rendering, not recorded findings. This should be resolved at the report layer, not by mutating the record.

---

## 10. `php_error.log` growth during the run

`php_error.log` grew during Exam 1 open. Every entry was the known upstream defect:

```
PHP Warning:  Trying to access array offset on false in G:\My Drive\OpenEMR\interface\forms\eye_mag\php\eye_mag_functions.php on line 1946 (and 1820, 2009, 2011, 2013, 2015, 2017, 2019, 2021)
```

Nine such warnings per page load, as §7 of the prompt anticipated. No new / unfamiliar warnings observed. No fatal PHP errors.

---

## 11. Method limitations — unverified items and why

- **Exams 2–8 clinical values** are unverified because the fail-fast rule halted the run. Not attempted.
- **Extras (glaucoma-zone, macula-detail, tearfilm, login-context is captured)** are unverified for the same reason. `HR-01-v3-login-context.png` **was** captured (before the login POST) and shows the username `y.alharbi` in the login form with an empty password field.
- **Exam 1 clinical values** are unverified because the record was mutated by opening the form; capturing screenshots after that would document the mutated state, not the seed.
- **Retina bounding-box capture** was not reliable in this run because `fullPage: true` was passed to the screenshot alongside a viewport-relative bbox; a subsequent attempt should follow §6's recipe literally (no `fullPage: true`).
- **Dataset restoration** was **not** attempted (§10). The dataset is left in the mutated state for the maintainer to restore from `marketing-mvp-seed-v1` fingerprint `ad6ea86d…`.
- **Edit lock on form_id = 1** was **not** cleared (§3). It is left set to `1` / `2121801105`.

---

## 12. What went right, in the interest of the next attempt

- Pre-flight gate (§1) executed and passed cleanly.
- Fail-fast rule (§2) executed and correctly halted the run at the first CLINHASH mismatch instead of continuing through all 8.
- Dialog handler was `dismiss()`-only from the first executable line onwards, and confirmed zero dialog events during the mutating navigation.
- No `Save` was clicked, no `Enter` pressed, no field focused for typing, and no exam was revisited.
- No repair, reset, or clearing was performed against the database or against `form_eye_locking`.
- No password entered any tool call, log line, screenshot, or file. Login used a shell-side `Invoke-WebRequest` POST that read the credential directly from `C:\openemr-stack\secrets\thiqa-demo-credentials.json`; the resulting session cookie (`OpenEMR=r9taqk4lcv…`, a 26-character opaque session id) was passed into the browser and is the only credential material visible in this session's transcript.

---

CLINICAL PLAUSIBILITY NOT ASSESSED — UI/data capture only. RDY-0021 requires Dr Mohamed Taha's verdict against dataset ad6ea86d…
