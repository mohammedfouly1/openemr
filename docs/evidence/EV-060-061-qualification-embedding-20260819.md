# EV-060/061 — qualification/annotation embedding, 2026-08-19

**Author:** Orchestrator (main session), read-only against the browser-verified content
Codex produced (PB-450…PB-467). No browser used — this is post-processing on already-captured
images, exactly the step I told Codex to leave to me throughout its dispatches.

## Method

`docs/evidence/harnesses/` doesn't cover image work, so this used ImageMagick (`magick`,
already installed on this host) to append a solid caption band above each image, containing the
**exact "Required qualification" text from the SS-01…SS-12 table** in
`Marketing-MVP-and-Launch-Readiness-Requirements.md` §17.1 (line ~11315), auto-wrapped to the
image's own width so nothing is cropped or illegible. This satisfies EV-061 §5's requirement that
"the qualification travels with the image... the same visual unit," not a linked page or footnote.

Output: `docs/evidence/captures/2026-08-19/publication-ready/` — 12 files, one per SS-01…SS-12,
each the correct final-content capture (per PB-450…PB-467's resolution) with its qualification
burned in.

## Source file used per shot, and any interpretation applied

| Shot | Source capture | Qualification text used | Verbatim from §17.1? |
|---|---|---|---|
| SS-01 | `SS-01-audit-log-tamper-report.jpg` | Hash not HMAC; unchained | Yes |
| SS-02 | `SS-02-acl-administration-matrix.jpg` | Sensitivity is encounter-level only | Yes |
| SS-03 | `SS-03-front-office-visit-history-permission-boundary-SYN-0006.png` (the corrected-surface retake, not the earlier `-CANDIDATE.jpg`) | Sensitivity is encounter-level only | Yes — table says "As above" (= SS-02's text) |
| SS-04 | `SS-04-physician-visit-history-SYN-0006-retake.png` | Sensitivity is encounter-level only | Yes — "As above" |
| SS-05 | `SS-05-layout-editor-draft-marker-fully-visible.png` | Zero layout forms ship configured; billing generators not pluggable | Yes |
| SS-06 | `SS-06-calendar-current-week-two-providers-20260819.png` | Standard scheduling -- not AI-optimized or automated scheduling | **No — interpreted.** §17.1's text is the negative instruction "Never 'theatre scheduling'," written as guidance for a copywriter, not literal caption text. Converted to an affirmative disclosure carrying the same meaning. **Flag for Product Marketing to confirm wording.** |
| SS-07 | `SS-07-flow-board-today-mixed-statuses-20260819.png` | Status board, not queue/token display | Yes |
| SS-08 | `SS-08-completed-soap-note-SYN-0006-retake.png` | 18 active forms; 16 ship uninstalled | Yes |
| SS-09 | `SS-09-ophthalmology-retina-panel-encounter-23-retake.png` | 6-8 ophthalmology exams seeded in this demo | **No — interpreted.** §17.1's text is "State the count" (an instruction, not a number). Used the row's own "Data required" column value (6–8 seeded) to satisfy it. **Flag for Product Marketing to confirm wording.** |
| SS-10 | `SS-10-patient-ledger-csv-control.jpg` (original candidate, retained — reviewer's PB-450 note said content-correct, no retake needed) | 10 of 55 reports disabled; no BI layer | Yes |
| SS-11 | `SS-11-exported-csv-open-in-spreadsheet.png` (original candidate, retained) | Export = CSV and DB access, not a migration service | Yes |
| SS-12 | `SS-12-arabic-rtl-demographics-direct-navigation.png` (PB-464…467's direct-navigation fix) | 47.5% Arabic coverage, chrome only; Arabic PDF will not render | Yes (rephrased from "47.5 %, chrome only; PDF will not render" for standalone clarity — same facts, no number changed) |

## What this does and does not close

**Closes the "qualification embedding" gap** that PB-450's independent review flagged as the
reason every candidate was marked RETAKE, for all 12 shots. Combined with PB-461…PB-467's content
fixes, every SS-01…SS-12 slot now has a candidate that is both content-correct and
qualification-embedded.

**Does not by itself close RDY-0061.** EV-061 §8 requires review "by someone other than the
capturer" — I produced these, so I should not be the one who signs them off as PASS. The two
interpreted captions (SS-06, SS-09) specifically need a human confirmation of wording before
either is truly publication-ready. A genuine second-party pass through §8's review sheet is still
the actual closing action for RDY-0061.

## Not verified

- Whether these specific images, once actually placed into any marketing artifact, will need
  further cropping/format/resolution work — that's downstream production work, not this item's
  acceptance criterion.
- P-3 (stock OpenEMR identity, browser chrome) — Codex's own reports noted the in-app browser
  capture tool doesn't include outer browser chrome, so this specific check remains open per
  PB-461's note on SS-04.
