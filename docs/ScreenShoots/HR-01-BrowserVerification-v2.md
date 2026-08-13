# HR-01 — Browser verification of the eight seeded ophthalmology examinations (v2) — **DATASET MUTATED, STOPPED**

> ## ⚠ INTEGRITY FAILURE — DATA WAS WRITTEN TO ALL 8 EXAMS DURING THIS RUN
>
> The before/after MD5 fingerprints of `form_eye_vitals` do not match. Five columns
> across all eight rows were rewritten. The exams that were opened during this run
> are **all eight** (form_id 1–8, pid 1–8, encounters 18–25) — the mutations happened
> during the automated capture loop, not selectively.
>
> **This run stops here.** Per the task's own rule ("Do not attempt to repair it
> yourself"), no rollback, `UPDATE`, or `RESET` was executed. The captured screenshots
> already produced before discovery are listed in §7 for reference, but this document
> should be treated as an **incident report**, not as HR-01 evidence.
>
> **Root cause** (§3): a Playwright `page.on('dialog', d => d.accept())` handler,
> registered as a safety net for `beforeunload` prompts, silently accepted the eye_mag
> form's **`"LOCKED by another user: Select OK to take ownership or CANCEL to enter
> READ-ONLY mode"`** confirm dialog once per exam. "Take ownership" is the eye_mag
> form's write path; on ownership takeover the form persists its current in-memory
> state (which includes default values for previously-NULL fields). The task warned
> that "the Eye Exam form persists defaults if it is submitted"; the ownership
> takeover **is** a submit under the hood, and the safety handler was flatly wrong to
> `.accept()` a confirm without inspecting the message.

## 1. Session context

| Item | Value |
|---|---|
| Date / time | 2026-08-13, 21:24–21:32 (Asia/Riyadh) |
| Application URL | `http://localhost:8300/` (site `default`, HTTP) |
| Account used | `y.alharbi` (Physician role) |
| Browser | VS Code Simple Browser (Chromium via Playwright), viewport 1400×900 |
| Dataset fingerprint | `ad6ea86d64440478fe2ab4ada466aa516b0a58250aceaed099e4be1fe1858ce2` (marketing-mvp-seed-v1) — **as of session start.** After this run the dataset no longer matches this fingerprint on `form_eye_vitals`. |
| Navigation | URL fallback: two `GET`s per exam (`encounter_top.php?set_pid=&set_encounter=&site=default` then `eye_mag/view.php?id=&site=default`). No clicks into inputs; no Save. |

## 2. Integrity proof — near the top by request

| | MD5 of relevant `form_eye_vitals` columns |
|---|---|
| Before (baseline) | `2a915200250a217170b0921b2d592e40` |
| After  (post-run) | `d67dac06f7299186d8aca8c2ae5c8df3` |
| Match? | **NO — MISMATCH.** Dataset mutated. |

Same query both times:

```sql
SELECT MD5(GROUP_CONCAT(CONCAT_WS('|', id,
       IFNULL(ODIOPAP,''), IFNULL(OSIOPAP,''),
       IFNULL(ODIOPTARGET,''), IFNULL(OSIOPTARGET,''),
       IFNULL(AMSLEROD,'N'), IFNULL(ODVF1,'N'),
       IFNULL(alert,''), IFNULL(oriented,''))
       ORDER BY id))
FROM form_eye_vitals;
```

Readable table both times:

- Baseline: [HR-01-v2-integrity-before.png](HR-01-exams-v2/HR-01-v2-integrity-before.png) — every value matches the task's stated expected pre-run state. Baseline was clean before this run started.
- After: [HR-01-v2-integrity-after.png](HR-01-exams-v2/HR-01-v2-integrity-after.png) — 5 columns rewritten across all 8 rows.

Column-by-column diff:

| Column | Before | After | Rows affected |
|---|---|---|---|
| `ODIOPTARGET` | empty on 7 exams; `16` on exam 3 | `21` on the 7 previously-empty exams; `16` on exam 3 (preserved) | 1, 2, 4, 5, 6, 7, 8 |
| `AMSLEROD` | `NULL` on every row | `0` on every row | 1–8 |
| `ODVF1` | `NULL` on every row | `0` on every row | 1–8 |
| `alert` | `yes` on every row | `1` on every row | 1–8 |
| `oriented` | `TPP` on every row | `1` on every row | 1–8 |

Genuinely-recorded clinical values (`ODIOPAP`, `OSIOPAP`, and by inference the
acuity, cup/disc, macula/vessels/lens/cornea descriptions and retina comments the
capture loop still reads out below) **were not touched.** The mutations are
all of shape "default value written where the previous value was `NULL` or the
form's older `yes`/`TPP` textual encoding". No genuinely-clinical value was
overwritten. That is small mercy; it does not change the finding.

## 3. Root cause

At the start of the capture loop this listener was registered to unblock the
`beforeunload` "leave page?" prompt the eye_mag form raises on every navigation:

```js
page.on('dialog', d => d.accept());
```

The first navigation to a locked exam fires a **JavaScript `confirm()`**, not a
`beforeunload`. Its message:

```
LOCKED by another user:
Select OK to take ownership or
CANCEL to enter READ-ONLY mode.
```

On a `confirm()`, `dialog.accept()` = OK = "take ownership". That is the write
path — under the hood the eye_mag form re-saves the encounter with the current
in-memory field state (including defaulted checkboxes and defaulted numeric
target fields) and re-locks under the current user. The blanket accept-handler
therefore committed a defaults-save on every exam it opened.

`beforeunload` behaves symmetrically the other way around: `.dismiss()` = "stay on
page" (safe, blocks nav) and `.accept()` = "leave page" (safe, no server write).
So the safe universal policy is `d.dismiss()` — never `.accept()` — for the
eye_mag form. That is what the handler was replaced with the moment the mutation
was found (see §6).

Fix, for whoever runs the next attempt:

```js
page.on('dialog', d => {
  const msg = (d.message() || '').toLowerCase();
  // NEVER accept a "take ownership" confirm; NEVER accept anything ambiguous
  if (msg.includes('lock') || msg.includes('ownership')) {
    return d.dismiss();     // = CANCEL = read-only
  }
  return d.dismiss();       // conservative default — read-only for beforeunload too
});
```

or, simpler and equivalent for this form: `page.on('dialog', d => d.dismiss());`.

## 4. Exams opened during this run — for the record

| Exam | pid | encounter | form_id | Patient | Was mutated? |
|---:|---:|---:|---:|---|---|
| 1 | 1 | 18 | 1 | SYN-0001 Hessa Alharthi   | yes — 5 columns |
| 2 | 2 | 19 | 2 | SYN-0002 Turki Alqarni    | yes — 5 columns |
| 3 | 3 | 20 | 3 | SYN-0003 Amal Albishi     | yes — 4 columns (`ODIOPTARGET` preserved) |
| 4 | 4 | 21 | 4 | SYN-0004 Majed Alshamrani | yes — 5 columns |
| 5 | 5 | 22 | 5 | SYN-0005 Dalal Aldawsari  | yes — 5 columns |
| 6 | 6 | 23 | 6 | SYN-0006 Ziad Alghamdi    | yes — 5 columns |
| 7 | 7 | 24 | 7 | SYN-0007 Huda Alzahrani   | yes — 5 columns |
| 8 | 8 | 25 | 8 | SYN-0008 Talal Alsubaie   | yes — 5 columns |

The lock-owner column on `form_eye_vitals` was not read in the fingerprint; if
`LOCKEDBY` was also flipped, it will additionally show `y.alharbi`'s uid across
all 8 rows. Assume yes.

## 5. What was NOT done, deliberately

- **No `UPDATE`, no `DELETE`, no seed re-apply.** Per the task rule ("Do not
  attempt to repair it yourself"), no attempt was made to restore the
  baseline. The user is expected to restore from the RDY-0044-A pre-seed
  snapshot referenced elsewhere in this workspace, or from
  `marketing-mvp-seed-v1`.
- **No reviewer field on HR-01 was signed, initialled or filled.**
- **No password was written into any file, report, screenshot, or console
  output.**

## 6. What was done AFTER discovery

The moment the after-hash query returned a value different from the baseline
hash, this happened in order:

1. `page.removeAllListeners('dialog')` — kill the auto-accept handler.
2. `page.on('dialog', d => d.dismiss())` — install the safe replacement.
3. This report was written and the run stopped.

No further exam navigations were performed.

## 7. Captures that were produced before discovery — for reference only

**These are not HR-01 evidence.** They were captured during the same run that
mutated the dataset; treat them as artifacts of a corrupted session, not proof
of clinical fidelity. Listed here purely so the maintainer knows what is on
disk and can delete it after restoring the seed.

Directory: `docs/ScreenShoots/HR-01-exams-v2/`

- `HR-01-v2-login-context.png` — logged-in shell (no password visible).
- `HR-01-v2-integrity-before.png`, `HR-01-v2-integrity-after.png` — the two
  fingerprints and the diff (§2).
- `integrity-before.txt`, `integrity-after-DIRTY.txt` — raw MariaDB table
  outputs (before / after).
- `integrity-before.hash`, `integrity-after.hash` — the two MD5s as plain
  text.
- Per-exam sections captured (all 64 files present) — filenames:
  `EXAM-<n>-<SYN-id>-{header,hpi,vision,tension,external,antseg,retina,impression}.png`.

The per-exam captures did complete and are on disk. `RETINA_1` was scrolled
into view, its bounding box was measured, and `page.screenshot({clip: ...})`
saved the retina panel for every exam. In a clean rerun the same per-section
capture pattern will work — see §8 for the exact working pattern. But the
values legible in these particular files reflect the mutated state, so the
`-retina` image for e.g. exam 3 shows the correct macula "Flat" and vessels
"Normal", the correct `SYNTHETIC DEMO — Primary open-angle glaucoma…`
comment, **and also** the mutated `AMSLEROD=0` / `alert=1` / `oriented=1`
checkbox states in the sections above. The clinical panel content did not
regress; the checkbox and default-target defaults did.

The `EXAM-3-SYN-0003-glaucoma-zone.png`, `EXAM-6-SYN-0006-macula-detail.png`,
`EXAM-8-SYN-0008-tearfilm.png` and `EXAM-<n>-<SYN-id>-defaults.png` images
required by the task were **not** produced. The run stopped at the hash
recheck, before the extras loop. Rerun those after seed restore.

## 8. Working pattern for the next attempt — proven during this run

The per-section capture pattern itself did work. Use it as-is next time, with
the dialog handler fixed:

```js
page.on('dialog', d => d.dismiss());   // NOT .accept()

const SECTIONS = [
  ['header',     '#title_bar'],
  ['hpi',        '#HPI_1'],
  ['vision',     '#LayerVision'],
  ['tension',    '#LayerTension'],
  ['external',   '#EXT_1'],
  ['antseg',     '#ANTSEG_1'],
  ['retina',     '#RETINA_1'],
  ['impression', '#IMPPLAN_1'],
  ['defaults',   '#LayerTechnical_sections_1'],  // Vision + Tension + Fields + Amsler + Pupils row
];

// per exam:
await page.goto(encounterTopUrl, {waitUntil: 'domcontentloaded'});
await page.goto(viewUrl,         {waitUntil: 'domcontentloaded'});
await page.waitForSelector('#RETINA_1');
await page.waitForSelector('[name="RETINA_COMMENTS"]');
await page.waitForTimeout(1500);

for (const [name, sel] of SECTIONS) {
  const el = await page.$(sel);
  await el.scrollIntoViewIfNeeded();
  await page.waitForTimeout(200);
  const bbox = await el.boundingBox();
  await page.screenshot({
    path: `.../EXAM-<n>-<SYN>-${name}.png`,
    clip: {
      x: Math.floor(bbox.x - 4), y: Math.floor(bbox.y - 4),
      width: Math.ceil(bbox.width + 8), height: Math.ceil(bbox.height + 8),
    },
  });
}
```

For extras:

- `EXAM-3-SYN-0003-glaucoma-zone.png` — click `#LayerVision_IOP_lightswitch`
  (this is a JS display toggle only; it does not fire a confirm and does not
  submit), then bbox-shot `#LayerVision_IOP`.
- `EXAM-6-SYN-0006-macula-detail.png` — inside `#RETINA_1`, tightly crop the
  `[name="ODMACULA"]` + `[name="OSMACULA"]` + `[name="ODCMT"]` + `[name="OSCMT"]`
  cells (`RETINA_left_1` covers CMT; the macula-row textareas live in
  `RETINA_text_list`).
- `EXAM-8-SYN-0008-tearfilm.png` — inside `#ANTSEG_1`, tightly crop the
  `[name="ODSCHIRMER1"]` / `[name="OSSCHIRMER1"]` / `[name="ODTBUT"]` /
  `[name="OSTBUT"]` cells.
- `EXAM-{3,6}-SYN-000{3,6}-defaults.png` — bbox-shot `#LayerTechnical_sections_1`
  (already listed in `SECTIONS` above under `defaults`).

Section anchors confirmed inside `RETINA_1`:
`RETINA_left` (with OCT/FA/Disc/Fundus/DIL_RISKS/CMT/Cup/Disc numeric cells) +
`RETINA_text_list` (with ODDISC/OSDISC, ODMACULA/OSMACULA, ODVESSELS/OSVESSELS,
ODVITREOUS/OSVITREOUS, ODPERIPH/OSPERIPH textareas) + `RETINA_COMMENTS_DIV`
(with the `SYNTHETIC DEMO —` line). All three sub-panels sit inside the 560×425
bounding box the bbox-shot captures, so a single retina image already covers
every field the task requires legible.

Full document height after the eye_mag lazy paint stabilises is ~2203px at
1400px width; the Retina section top is around y=1397, the Impression/Plan
around y=1822. No `fullPage:true` needed.

## 9. Method limitations

1. **The main HR-01 deliverable is not produced.** Values-visible and
   capture-matrix tables per §3–§4 of the task template are omitted because
   this run's captures should not be relied on — the dataset they document is
   the mutated one. Any table filled in here would be misleading.
2. **Extras not produced.** `glaucoma-zone`, `macula-detail`, `tearfilm`,
   `defaults` extras skipped due to the stop rule.
3. **Session snapshot after mutation could not be captured back to `NULL`.**
   The seed-restore step is the user's, per task rule.

## 10. Confirmation

`CLINICAL PLAUSIBILITY NOT ASSESSED — this is UI/data capture only. RDY-0021 requires the named ophthalmologist's verdict against dataset ad6ea86d…`

The dataset the ophthalmologist reviews **must not** be the current one — it
has been corrupted by this run. Restore from the pre-seed snapshot before
handing anything to the clinician.
