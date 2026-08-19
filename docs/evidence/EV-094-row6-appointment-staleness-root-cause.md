# EV-094 (§40 row 6) — appointment-date staleness: code-level root cause

**Author:** Orchestrator (main session), 2026-08-19, desk/code investigation only — no live
system changes, no reseed executed. Follow-up to PB-441's live reproduction (eighth
browser-check agent), which confirmed today's Flow Board shows `Total patients: 0` and all 37
seeded appointments are DB-anchored to the fixed date `2026-08-14`.

## The seeder is not the bug

Read `interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/SeedDemoCommand.php`,
`seedAppointments()` (lines 1083-1147). It is already written **relative to the run date**, not a
hardcoded constant:

```php
$monday = date('Y-m-d', strtotime('monday this week'));
$today  = date('Y-m-d');
...
$date = $today;   // for the block of appointments deliberately meant to populate "today"
```

"Today's slots are deliberately populated so the flow board has a list to show" (inline comment,
line 1098) — this was a conscious design decision, executed correctly, on 2026-08-14. Nothing in
this command needs to change.

## The actual gap: no re-seed cadence

Five real calendar days have passed since the one-time seed run. `$today`/`$monday` were
correctly resolved to 2026-08-14 *at that moment*, and have been frozen in the database ever
since — because nothing has re-invoked the seeder (or a lighter date-only refresh) since. This
matches PB-441's own conclusion exactly: "RDY-0022's own acceptance... has silently gone stale
purely from elapsed real time since seeding," and confirms it at the code level: this is a
**process/cadence gap, not a defect in the generator**.

## Fix options (not executed — needs sequencing, see below)

1. **Manual, immediate:** re-run `thiqa-branding:seed-demo` (or whatever narrower "refresh
   today's appointments only" mode it may support — not checked in this pass) so `$today`/
   `$monday` re-resolve against the current date.
2. **Systemic:** put the reseed on the same scheduler RDY-0083 already needs, so this can't go
   stale again without someone noticing within a day.

## Why this was not executed now

A separate browser-automation agent is, as of this writing, actively working against this same
local app instance and database (continuing RDY-0016/0041/0060/0061/0086). Re-seeding now would
very likely collide with or corrupt its in-progress patient/appointment work (it is deliberately
avoiding DB resets itself for the same reason — see PB-442's own reasoning about not triggering a
third reset mid-session). **Recommended sequencing: reseed only after that agent's dispatch
completes**, and recapture SS-07 (already flagged in PB-438 as shot against the stale
2026-08-14 date) in the same pass so it doesn't need a second recapture later.

**Not decided here:** who owns actually running the reseed, and whether it should happen before
every demo/capture or on a fixed schedule. Flagged for whoever owns RDY-0022/the demo-data
refresh process, per PB-441.
