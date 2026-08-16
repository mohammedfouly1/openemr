# EV-AUDIT — Agent A (Orchestrator-turned-Auditor) session, 2026-08-16

**Role of this file.** This session was instructed to orchestrate Phase 2B via subagents, discovered
a second live session ("Agent C") already claiming that role, was told to stand down from
orchestration and audit Agent C's work instead. This file is that audit's record. It exists only in
this conversation until this commit — nothing below was written to the requirements document by this
session, per the Owner's explicit instruction to hold all writes to a new, low-collision file (§0.0
Rule 2).

**Author:** the "Agent A" session referred to in this file's own name — one of at least three
concurrent Claude Code processes on this host today (see §5). Not to be confused with the "Agent A"
identity in `docs/evidence/AGENT-CLAIMS.md` (PB-001…PB-069), which is a different, earlier session.

---

## 1. Independent gate-count derivation — audit of commit `2e86a214b` (PB-140, Agent C)

Before Agent C's commit landed, this session hand-derived the canonical open-P0 gate count directly
from the (then only working-tree, now committed) §7.2–§7.18 register — walking all 71 P0 rows,
recording each one's `Blocks` field and closure status independently, not by trusting PB-140's
summary table.

**Closed-ID count: CONFIRMED exactly.** 24 P0 IDs closed (0001, 0010, 0011, 0012, 0014, 0015, 0017,
0020, 0021, 0022, 0024, 0026, 0027, 0028, 0032, 0036, 0040, 0046, 0050, 0051, 0052, 0058, 0059, 0080)
+ P1s 0053/0054 (closed later, see §2) = 30 total closed IDs at that point. 71 − 24 = 47 open P0,
matching PB-140's published figure.

**Per-gate mechanical re-derivation, independently, from `Blocks` fields:**

| Gate | PB-140's figure | This session's independent count | Match? |
|---|---|---|---|
| G0 | 3 | 3 | Yes |
| G1 | 16 | 16 | Yes |
| G2 | 12 | 13 | **No — see below** |
| G3 | 17 | 17 | Yes |
| G4 | 3 | 3 | Yes |
| G5 | 13 | 13 | Yes |
| G6 | 21 | 21 | Yes |

**6 of 7 gates matched on independent derivation, before any coordination between sessions.** This is
the strongest evidence this document has produced about its own gate counts — two derivations,
computed separately, agreeing on 6 of 7 without either being shown the other's working.

### The one disagreement, and its resolution

`RDY-0083`'s `Blocks` field reads `G2(disclose) G3` — a qualified annotation, not a bare gate name.
This session's first (literal) reading of canonical rule 3 ("the `Blocks` field explicitly names that
gate") counted it toward G2, giving 13. PB-140 excluded it, giving 12.

**The Owner resolved this explicitly** (this session's conversation, 2026-08-16): a qualified `Blocks`
entry — `G2(disclose)`, `G2(no-go)`, or similar — indicates the gate's acceptance can be met **with
that qualification stated**, not that closure is withheld; it does not count toward the gate's open-P0
total, but it does create a mandatory disclosure obligation that must appear in the gate's acceptance
record. Proposed as new **§47 rule 8**. Under that rule, **PB-140's G2 = 12 is correct**, and this
session's 13 was the pre-clarification literal reading. Recorded here so both derivations are
preserved, per the Owner's instruction not to collapse the round-trip into a single "final" number.

**This session did not write rule 8, the resulting obligations table, or the G2 correction into the
requirements document.** That edit belongs wherever the sync-owning session (Agent C, or whoever next
holds §0.0 Rule 3) applies it, so it lands as one coherent change rather than two sessions racing to
amend the same rule.

### PB-020 primary-source verification (0011/0017 closure)

This session read the actual `### PB-020` log entry (2026-08-13, line ~6226 as of this writing), not
just the register row citing it. It holds up: real before/after authentication test (68,634-byte
authenticated shell on the new credential vs. a 476-byte session-timeout stub on the old installer
default), six demo accounts unaffected, rotation timestamp recorded in the protected store. **It also
discloses a self-caught false-PASS**: the first HTTP check reported the old credential still accepted,
because the detector keyed on a login-form field (`clearPass`) that is *also* absent from the
session-timeout stub, making a rejected login look like a successful one. Re-run on the correct basis
(can the session reach an authenticated page), old = rejected, new = accepted. Recorded because a bad
detector produces false PASSes as readily as false FAILs — the kind of disclosure a closure record is
supposed to carry and often doesn't.

---

## 2. PB-141 audit — RDY-0013/0037/0038/0053/0054, and a correction to this session's own earlier verdict

**This session gave an incorrect verdict on RDY-0013 earlier in this same conversation.** Before
discovering Agent C, this session read PB-028's own summary line ("VERIFIED READY — CLOSED") for
RDY-0013, confirmed the two evidence files existed on disk with the right sizes and screenshot counts,
and reported RDY-0013 as legitimately closed. **That was wrong**, and the error was trusting a closing
agent's self-declared verdict instead of checking the requirement's own §8 acceptance criteria clause
by clause — precisely the failure mode §0.0 Rule 5 exists to prevent, and this session fell into it by
proxy (reading an old self-closure, not writing a new one).

PB-141 (Agent C, committed after this session's incorrect verdict) caught this. **This session
independently re-verified PB-141's specific claim against primary sources** rather than accepting it
because it committed first, per the Owner's instruction:

- **RDY-0013's §8 acceptance card** (verified directly, line 6503 of the requirements document as
  committed): *"Each account's rendered top-level navigation matches the intended node set; **the
  reception account can complete registration**."* Two clauses, both required.
- **`docs/ScreenShoots/BrowserVervication.md`, test T6.5** (verified directly): `EXPECTED: Cancel the
  form; confirm no patient created.` `OBSERVED: "Create New Patient" button was NEVER clicked. The
  form was only inspected. No patient was created.` `RESULT: PASS`.
- **`docs/ScreenShoots/BrowserRetest.md`, test R7.3** (the Round-2 re-test, verified directly):
  `EXPECTED: Cancel; no patient created.` `OBSERVED: The "Create New Patient" button was NEVER
  clicked. Form was only inspected. No patient created.` `RESULT: PASS`. The file's own closing summary
  states explicitly: *"No patient created (Add Patient form inspected then abandoned)."*

**Both browser-verification rounds deliberately never completed a registration** — T6.5/R7.3 were
scoped as cancel/negative tests, and PASS on a cancel test says nothing about whether registration
*can* be completed. The second clause of RDY-0013's acceptance criteria has never been tested, in
either run. **PB-141 is correct. This session's earlier "CLOSED" verdict on RDY-0013 was wrong**, and
is retracted here.

**RDY-0037 (SAR display) and RDY-0038 (locale seeds)** — this session independently confirmed PB-141's
underlying evidence citations:
- T5 (SAR) checks only `/interface/super/edit_globals.php` → Locale tab — the same Globals-only check
  PB-016 (2026-08-13, predating PB-028) had already stated was insufficient ("not yet observed in a
  fee/charge context, which is where the claim actually matters"). PB-028 re-ran the exact test PB-016
  had already ruled inadequate and reported it as a full closure. **This is the one genuine false-pass
  risk found across both audits.**
- T6.4 (phone hint) is recorded in the source file itself as `RESULT: PARTIAL / FAIL of the visible
  hint requirement` — not a clean pass, and no test anywhere in either screenshot file addresses
  `units_of_measurement` rendering on the registration screen.

**RDY-0053/0054**: confirmed correctly closed at PB-013 (same 127/127-probe run already verified for
0050–0052) — a genuine register-hygiene fix, same bug class as PB-140 (evidence existed, register row
was never updated).

**RDY-0014/0015**: PB-141's "no change, already correct" verdict re-confirmed by this session against
PB-029's negative control (`k.alotaibi` shows a different taxonomy than the two intended accounts).
**One residual, unchased item, flagged rather than resolved**: RDY-0014's §8 acceptance criteria has a
second clause — *"the NPI decision is recorded with its reason"* — which this session has not located
independent evidence for (no `EV-014` file found in `docs/evidence/`). Not asserted as a defect; just
not verified. Worth a follow-up read of `EV-014 provider-identity.md` if it exists, or a note that it
doesn't.

**Net effect confirmed**: no change to Open P0 = 47 (0013/0037/0038 were already counted open in
PB-140; 0053/0054 are P1). Both commits' arithmetic holds.

---

## 3. RDY-0083 regression — found live this session, not yet in any PB entry

**Observed directly** (2026-08-16, this session, `SELECT name, next_run, active FROM
background_services`): both `Email_Service` and `UUID_Service` showed `next_run` around 05:23–05:25
that morning, while the database clock read 15:17 — roughly **10 hours overdue**. Before that, the
stack (`httpd.exe`, `mariadbd.exe`) was found **fully stopped** — `Get-Process httpd,mariadbd` returned
nothing — and had to be restarted via `start-openemr.ps1` to run any further checks.

**Why this matters**: PB-080/PB-081 (2026-08-14) proved the background-service trigger "self-heals"
after a restore, because the console-session process kept running through the test. But the trigger
**runs as the logged-on user and dies when that console session ends** (this limitation is already
named in `AGENT-CLAIMS.md`'s own notes, PB-077's item). At some point after 05:25 this morning, the
console session ended — unobserved, no PB entry records it — and the trigger died with it. It had been
down for roughly 10 hours before this session happened to check.

**This is not a data-integrity issue** (RDY-0083's underlying fix — the 13 UUID rows — was separately
confirmed still `0` NULL in both `form_vitals` and `insurance_companies` when checked live). It is an
**operational reliability finding**: the self-heal property proven at PB-080 does not survive the host
being logged off, rebooted, or the console window closed, and nothing currently detects or alerts on
that gap.

**Recommendation, not yet actioned**: RDY-0083 should not close on the self-heal basis alone. Its
`G2(disclose)` qualifier (see §1 above) should carry this specific text: *"The background-service
trigger runs as the logged-on user and does not survive a logoff. On a demo instance the stack may be
found down and services hours overdue at session start — check and restart before any demo, and state
this limitation before opening the diagnostics screen (RDY-0094's no-go register)."* The same
limitation must be written into `RDY-0047`'s deployment runbook with the console-session dependency
stated explicitly — it must never reach a customer/pilot environment silently, since a pilot host is
far more likely to be rebooted or logged off unattended than a developer's own machine.

---

## 4. RDY-0045 drift — measured live this session

| Measure | Document's figure (2026-08-13/14) | Measured live, this session (2026-08-16) |
|---|---|---|
| Behind upstream/master | 418 | **482** |
| Ahead of upstream/master | 33 (§3.1) / 92–94 (later PB entries) | **94** |
| Unpushed to `origin/feat/thiqa-branding-foundation` | 71 (2026-08-15, per the untracked gap-inventory doc) | **1**, as of this session's first check |

**The unpushed-count change is the notable finding**: `origin/feat/thiqa-branding-foundation` was
found at commit `6de7cdcc1` when this session first fetched it — meaning roughly 70 commits were
pushed to that branch by some actor between 2026-08-15 (when the gap-inventory doc measured 71
unpushed) and 2026-08-16 (this session's check). **This session did not identify who pushed them** —
`6de7cdcc1`'s own author is `mohammedfouly1`, consistent with the Owner's own git identity, but that
does not distinguish which of the (at least three) concurrent sessions on this host did the push, or
whether it was a manual `git push` outside any session. Worth asking directly rather than inferring.

The upstream-behind/ahead drift (418→482, 33→94) is ordinary forward drift from real time passing and
upstream continuing to land commits — not a finding, just a refreshed measurement superseding the
document's stale figures.

---

## 5. Process identification — three concurrent `claude.exe` processes, this host, 2026-08-16

```
Id     StartTime               CPU(s)   Path
7252   2026-08-16 14:56:20     120.25   ...\npm\node_modules\@anthropic-ai\claude-code\bin\claude.exe
10164  2026-08-16 15:03:23     134.84   ...\npm\node_modules\@anthropic-ai\claude-code\bin\claude.exe
9148   2026-08-16 15:05:08      32.25   ...\npm\node_modules\@anthropic-ai\claude-code\bin\claude.exe
```

All three: identical binary path (the standard global npm install location), identical bare
`claude.exe` command line (no flags, no arguments visible — `Win32_Process.CommandLine` shows nothing
beyond the executable path for any of the three). `Get-CimInstance Win32_Process -Filter
"Name='node.exe'"` returned no results — `claude.exe` does not spawn a separately-visible `node.exe`
child on this build, or none was running at query time.

**What this does and does not establish**: three interactive sessions, started 9 minutes apart
(14:56, 15:03, 15:05), same user, same host, same install — consistent with three terminal windows
opened by the same person in short succession. **Nothing in this data distinguishes a deliberately
opened second window from a scheduled/automated launch** — there is no working-directory or argument
evidence either way, since bare `claude.exe` with no arguments looks identical in both cases. This
session cannot determine its own PID from inside its sandboxed tool calls, so which of the three rows
above corresponds to this session, which to "Agent C," and whether a third, unaccounted session also
exists, is left for the Owner to determine directly (e.g., by checking which terminal windows are
actually open).

---

## 6. Correction round-trip on the gap-inventory doc's F-1 finding — recorded in full, not collapsed

Recorded because the Owner asked that all three steps be kept, not just the final answer:

1. **`docs/gap-inventory-and-fix-groups-2026-08-15.md` (untracked, 2026-08-15) stated**: "Only
   `RDY-0001` and `RDY-0080` carry `CLOSED` in §7.2–§7.18." This was correct for the then-committed
   baseline.
2. **This session's orchestration brief repeated a stronger claim** ("the register is known-stale...
   §7 carries CLOSED on only TWO rows"), and this session, auditing that brief, ran `grep "CLOSED BY
   PHASE 2B"` against the file on disk and found **eight** rows already marked closed — reporting this
   as a correction to the gap-inventory doc's F-1 finding.
3. **That "correction" was itself wrong.** The `git status` check that preceded the grep showed a
   clean tree, but real wall-clock time passed afterward (this session paused for two rounds of user
   interaction), during which Agent C wrote its in-progress, uncommitted PB-140 edit directly to the
   same file on disk. The later grep read Agent C's half-finished edit, not committed history, and
   this session mistook it for pre-existing baseline. **The gap-inventory doc's original claim was
   right all along**; this session's "correction" to it was the actual error, caused by reading a
   working tree without checking `git status` immediately before the read that mattered, not just at
   session start.

**Standing lesson, as the Owner framed it**: a `git status` check is only evidence for the instant it
runs. Before asserting any current-state fact about a shared file mid-session, re-check status (or
read via `git show HEAD:<path>`) immediately before the read that will be reported, not once at the
top of a long tool sequence.

---

## 7. What this session did and did not do

**Did**: independent read-only verification throughout (SQL `SELECT`s, `git log`/`fetch`/`diff`,
file reads, starting the already-designed Apache/MariaDB stack). Wrote this one new file. Nothing
else.

**Did not**: claim a PB range, write to `AGENT-CLAIMS.md`, edit the requirements document, spawn any
subagent, or commit anything other than this file.

**Left for the Owner or the sync-owning session**: applying §47 rule 8 (qualified `Blocks` entries)
and the resulting obligations table; registering the RDY-0083 regression as its own PB entry; deciding
whether/how to reconcile the RDY-0045 unpushed-commit history; confirming which of the three `claude`
processes is which.
