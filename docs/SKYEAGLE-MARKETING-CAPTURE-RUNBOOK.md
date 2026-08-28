# SkyEagle Marketing Capture Runbook

**Purpose:** a concrete, sequenced shot list for producing marketing screenshots and a short
demo video from `https://demo.skyeagle.uk`, built from the same live-proven flow as
`docs/SKYEAGLE-SALES-DEMO-RUNBOOK.md`. Every shot below reuses a step already verified live
during this programme's certification stages — nothing here requires new, unverified
UI interaction to be discovered at capture time.

**Status as of this writing: capture has NOT yet been performed.** This document is the plan
(Stages 19-20); the actual capture pass (Stage 21) should happen only after the two
pre-conditions in §0 are met.

---

## 0. Pre-conditions before actual capture (Stage 21)

Do not run the actual capture pass until:

1. **The fee-schedule `code_type` fix and the vitals unit-storage fix are applied** (both
   staged, evidence in `tmp/skyeagle-migration-2026-08-27/evidence/10-...sql` and
   `11-...sql`, confirmed still unapplied as of Stage 18). Capturing the Fee Sheet with a
   working code search, and any vitals card, looks materially more polished once these land —
   marketing assets are a poor place to bake in "avoid this view" caveats the way the sales
   runbook can.
2. **A final demo data reset to a clean golden state has happened** (Stage 22) — screenshots
   should show a deliberately-curated state, not mid-certification artifacts (test claims,
   the `SYNTH-DEMO-001` check-number placeholder, etc. are fine functionally but should be
   swapped for more presentable placeholder values before anything goes in front of a
   customer or on a marketing page).

## 1. Screenshot shot list

Each row: what to capture, from where, and which prior stage proves the view actually renders
correctly (so no shot list item is guesswork).

| # | Shot | Screen/state | Proven in |
|---|---|---|---|
| 1 | Branded login page | `interface/login/login.php?site=default`, English | Stage 2 (visual gaps closed), Stage 1 |
| 2 | Branded login page, Arabic/RTL | Same page, language switched | Stage 9 (RTL layout confirmed clean) |
| 3 | Clinical dashboard, golden patient | pid 3 (Amal Albishi), Physicians-role login | Stage 8 |
| 4 | Problem list + linked encounter | pid 3, "Link/Add Issues to This Visit" state | Stage 8 |
| 5 | Vitals entry with live unit conversion | pid 3, new vitals form (post-fix ideally, see §0) | Stage 8 |
| 6 | Prescriptions widget | pid 3, showing Latanoprost + Artificial Tears | Stage 6 |
| 7 | Scheduling / Calendar | Ophthalmological Services appointment | Stage 6 |
| 8 | Fee Sheet, E/M-level picker | pid 3 encounter, "Established Patient" grid | Stage 8 |
| 9 | CMS-1500 claim PDF | Generated for pid 3's encounter | Stage 6/8 |
| 10 | Reports > Sales by Item | Showing the new charge | Stage 8 |
| 11 | Role-appropriate menu, Front Office | `r.aldosari` login, no clinical widgets | Stage 7 |
| 12 | Role-appropriate menu, Accounting | `k.alotaibi` login, back-office Fees menu | Stage 7 |
| 13 | Dark theme variant of the dashboard | Settings > General Theme toggle | Stage 2 |
| 14 | Arabic clinical dashboard | pid 3 or similar, Arabic session | Stage 9 |

**Explicitly excluded from the shot list, with reasons:** anything requiring X12/EDI (not
installed, would misrepresent capability), the Portal (kept disabled per Stage 11, showing it
would misrepresent current state), any patient other than pid 3 for the primary narrative
(pid 2 has a data contradiction; several others have duplicate name/DOB pairs) — pid 3 is the
only patient confirmed clean end-to-end.

## 2. Short demo video plan (Stage 20)

A single continuous narrative, not a stitched collection of the screenshots above — reuses
the same proven sequence as the sales runbook §3, condensed to fit a 2-3 minute format:

1. **Open** (10s): branded login, language switch to show bilingual capability, log in.
2. **Patient story** (45s): open pid 3, show the existing problem/prescription/insurance
   context — "a patient already under this practice's care."
3. **Clinical moment** (40s): new vitals entry with the live unit-conversion display, link
   today's visit to the standing diagnosis.
4. **Billing moment** (35s): E/M-level Fee Sheet pick, generate the CMS-1500, record payment.
5. **Close** (20s): Reports > Sales by Item, then a quick role-switch cut (Front Office vs.
   Physician) to land the "the system enforces who sees what" point from a fresh angle.

Total: ~2 minutes 30 seconds. Every beat maps directly to a step already proven live in Stage
8 — no new interaction sequence needs to be improvised during recording.

**Recording mechanics (not yet executed):** use the same Selenium/Panther-driven browser
session pattern documented in `CLAUDE.md`'s "Browser debugging via Selenium" section for
screenshot capture, or the Claude-in-Chrome `gif_creator`/screenshot tools for an interactive
capture pass — whichever is live and connected at actual capture time. Given this session's
own documented history of intermittent Claude-in-Chrome pairing failures on this host, budget
for the Selenium/Panther fallback path rather than assuming the Chrome extension will be
available.

## 3. Stage 21 (actual capture) — status

**Not performed as part of this stage.** Per §0, capture is deliberately sequenced after the
two data-quality fixes and the final golden-state reset (Stage 22), so marketing assets don't
need to be recaptured after those land. This is a plan, ready to execute once those
pre-conditions are met — not a decision to skip Stage 21 outright.
