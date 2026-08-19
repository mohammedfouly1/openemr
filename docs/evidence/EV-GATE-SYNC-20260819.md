# EV-GATE-SYNC-20260819 — G0-G6 open-blocker reconciliation, full evidence trail

**Produced by:** Agent E (Gate-sync auditor), range PB-300…349 (claimed in
`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` §0.0 Rule 1 table, commit `b8fb75c90`).
**Purpose:** back `docs/gap-inventory-and-fix-groups-2026-08-19.md` with full citations, per Rule 5
(no vacuous closures, negative controls where relevant).

**Method.** Three read-only subagents ran in parallel, each scoped to a non-overlapping domain, then their
findings were spot-checked against primary sources (register rows, `git show`, live queries) before being
relied on. This file preserves each subagent's report verbatim (§1–§3) plus the spot-checks and the
register `Blocks`-field derivation that produced the corrected gate counts (§4–§5).

---

## 1. Subagent report — recent-history reconciliation (PB-201 through HEAD)

Full scope: establish the true current status of every RDY/DG/D/RB item touched between PB-201
(2026-08-16) and the commits following PB-220 (RDY-0002 closure, RDY-0071 code fix, RDY-0045 merge
execution, RDY-0016 A-10 probes/fix-scope).

**Headline finding:** only RDY-0002 among items touched in this window genuinely closed and is not yet
reflected in the §1.4/§47 gate-count sync. Full per-item table:

| ID | Status @ PB-216 baseline | TRUE current status | Evidence | Gate(s) blocked | Blocker nature |
|---|---|---|---|---|---|
| RDY-0002 | NOT READY | CLOSED — register row (`:927`) updated `aa39f8b7d`; Owner's Wave-3 acceptance in `EV-WAVE3-decisions-20260816.md:10-26` | G0 (not yet decremented) | none — only the doc-wide sync was outstanding |
| RDY-0013 | CLOSED (PB-202) | CLOSED, unchanged | `:945`, PB-202 | G1 G2 | none |
| RDY-0037 | CLOSED (PB-214) | CLOSED, unchanged | `:979`, PR-18, PB-214 | G2 | none |
| RDY-0082 | CLOSED (PB-203/205) | CLOSED, unchanged | `:1074`, PB-203/205 | G3 G6 | none |
| RDY-0038 | NOT READY | NOT READY, register row stale relative to PB-202's phone-clause finding (a sync gap, not new defect evidence) | `:980`, PB-141, PB-202 | G1 G2 | none-fully-closed |
| RDY-0025 | NOT READY | NOT READY — PB-215 found a third, better-evidenced, host-level root cause (broken PHP session persistence, `session.save_path` → unwritable `C:\Windows`, 472 denied events since 2026-08-13); neither of PB-204's two candidates confirmed | `:962`, PB-204, PB-215 | G2 | external-dependency (host `php.ini`, out of source-only task scope) |
| RDY-0048 | NOT READY (deferred) | NOT READY — PB-213: three separate mutation attempts, each denied by the tool-permission classifier itself | `:1000`, PB-213 | G3 | tooling-limitation |
| RDY-0071 | NOT READY (PB-208: 7/9 clean) | STILL NOT READY, but the named code defect is now fixed and code-reviewed (not live-verified): `6dea39a28`+`6ce9979f5` fix `PrintEncHeader/Footer` CSV branching, `csvEscape()` wiring, `PrintCreditDetail()`'s unconditional HTML echo, and the Content-Disposition filename bug. Register row `:1053` still literally says "unfixed" | `6dea39a28`, `6ce9979f5`, `AGENT-CLAIMS.md:334`, `EV-071` §3.5.1 | G3 G5 G6 | register-row drift + human-blocked live check |
| RDY-0016 | NOT READY | NOT READY — `f37ead1fa` (`EV-016-A10-acl-probes.md`) found a live-exploitable ACL fail-open path (`load_form.php`/`view_form.php`/`questionnaire_assessments.php` pass unvalidated `$_GET['formname']` to `aclCheckForm()`; 16 orphaned form directories with no registry row). `7588b42e5` (`EV-016-A10-fix-scope.md`) scoped but did not implement a fix | `:948`, `f37ead1fa`, `7588b42e5` | G1 G3 G5 | needs-code-change (scoped, unwritten) + one Owner decision (orphaned dirs) |
| RDY-0045 | NOT READY | NOT READY — merge commit `8e0eaba90` confirmed ancestor of HEAD (`git merge-base --is-ancestor 8e0eaba90 HEAD`). Tag `pre-rel820-merge-20260817` exists locally, absent on origin (`git ls-remote origin refs/tags/pre-rel820-merge-20260817` empty). `git push` failed HTTP 403 — credential helper authenticates as `midodevelopper`, no write access to `mohammedfouly1/openemr` | `3affc15c7`, `15eb33f8b`, `EV-045` ADDENDUM 3, `AGENT-CLAIMS.md:394-415` | G3 G6 | human-decision/external-dependency — push credentials only |
| RDY-0042 / RDY-0043 | NOT READY | NOT READY across two further attempts. PB-217: identical no-password-entry rule self-applied. PB-218: orchestrator relayed **direct human authorization**; first password-entry attempt still denied by the Claude Code permission classifier itself, a layer distinct from task-relayed authorization | `:989-990`, PB-217, PB-218 | G1 G2 | tooling-limitation, needs human decision |
| RB-22 (branding ledger) | OPEN | CLOSED — PB-220: build workspace resynced, rebuilt, verified `Inter-Regular.woff2` referenced exactly once across all 8 theme CSS files, zero Medium/SemiBold/Bold references, `BrandingGovernanceGuard` 31/31, brand manifest 123/123 | PB-220, `RebrandingBugs.md` RB-22 row | none (not gate-blocking) | none |

Other items advanced without moving a gate: RDY-0086 (naming re-affirmed, competence-basis statement still
declined — AMBIGUOUS), RDY-0096 (deliberately deferred), and roughly a dozen R2/R3/R7/R9-domain items
marked "DONE — PREPARED (not closed)" in `AGENT-CLAIMS.md`'s Agent D sections, each still needing a
distinct human action.

Direct confirmations: RDY-0082 leg 6 fully closed (7/7 acceptance criteria met, PB-203); RDY-0025 still
open on a *different*, better-evidenced cause than previously logged; PB-206 confirmed as the mechanical
gate baseline that PB-216 then single-item-decremented; PB-208's "7 of 9 clean" figure confirmed accurate;
PB-217/218 confirmed as tooling-limitation, not product defect.

---

## 2. Subagent report — live-instance re-verification (2026-08-19 vs. 2026-08-15 baseline)

| # | Check | 2026-08-15 baseline | 2026-08-19 live result | Verdict |
|---|---|---|---|---|
| 1 | Core counts (patients/enc/appt/docs/rx/payers) | 30/72/37/10/12/2 | 30/72/37/10/12/2 | unchanged |
| 2a | `users`/`users_secure` | 10/7 | 10/7 | unchanged |
| 2b | Breakglass group members | 0 | 0 | unchanged — RDY-0019 open |
| 2c | Accounting group members | 0 | **1** (`k.alotaibi`) | drifted-better, undocumented |
| 2d | `bulk_rep`/`op_rep` ACOs exist | yes | yes, reconfirmed | unchanged, reconfirms 08-15's F-7 |
| 3a | `pqri_registry_name`/`_id` | fake placeholders | **both empty string** | drifted-better (RDY-0035) |
| 3b | `portal_onsite_two_address` | placeholder | unchanged | D-2 still open |
| 3c | `gbl_time_zone`/currency/phone code | Riyadh/SAR/966 | unchanged | unchanged |
| 3d | `display_acknowledgements(_on_login)` | `0`/`0` per CLAUDE.local.md | both **empty string**, not literal `'0'` | functionally equivalent, worth a note |
| 4 | `Email_Service`/`UUID_Service` | active, ticking | active, ticking (next_run 2026-08-19 02:37 / 06:35) | unchanged — both current |
| 5 | `log` row count | 70,638 | 95,762 (+25,124/4 days) | growing at an organic-looking rate |
| 6 | `Emergency_Login_email_id` | empty | empty | unchanged |
| 7a | Amiri/NotoNaskh in `src/`,`interface/`,`library/` | zero matches | **one match**: `interface/themes/thiqa/_typography.scss` (naming only, not PDF engine wiring); `src/Pdf/` still zero | unchanged in substance — RB-14/D-9 still open |
| 7b | Inter font weights in compiled theme CSS | all 4 referenced | only `Inter-Regular.woff2` in `style_light.css`/`style_dark.css` | drifted-better, RB-22 rebuild verified at the artifact level |
| 7c | `config.php` Unix commands / `perl_bin_dir` | `lpr`/`enscript`/`/usr/bin/file` present; `perl_bin_dir` → nonexistent XAMPP path | `lpr`/`enscript`/`/usr/bin/file` still present, same lines; `perl_bin_dir` **removed from the repo entirely** | partially drifted-better (RDY-0049) |
| 8a/8b | Unpushed commits | 71 (upstream/master basis) / 418 behind, 92 ahead | `origin/feat/thiqa-branding-foundation`: 0 behind, 166 ahead; `upstream/master...HEAD`: 484 behind, 259 ahead | divergence grew, consistent with the rel-820 merge landing |
| 8c | master/rel-820 merge decision | "has not been made" | **merge already executed**: `8e0eaba90 merge: adopt upstream/rel-820` | contradicts the current doc's Group-10 prose — corrected in the new inventory |
| 8d | Safety tag pushed | n/a (didn't exist) | tag exists locally, absent on origin | RDY-0045 push still blocked |
| 9 | DB root auth / bind_address | passwordless, loopback-only | unchanged | unchanged |

Highest-value contradictions found: (1) RDY-0045/Group 10 — the merge is done, only the push is
outstanding, opposite of what the 08-15 inventory's prose implies; (2) RB-22 is genuinely closed at the
compiled-artifact level, not just claimed; (3) DG-010/Accounting actor resolved but undocumented; (4)
RDY-0049 half-fixed.

---

## 3. Subagent report — human/dependency/legal blocker audit (Groups 6-9, R5/RDY-0092)

**Note from the subagent, preserved:** at the time this ran, the only trace of this gate-sync task was
the PB-300 range-claim commit (`b8fb75c90`) — no PB-300+ entries or evidence file had yet landed. That
gap is what this file and the new inventory now fill.

| ID | Group | Status 08-15 | Status 08-19 | Still needs |
|---|---|---|---|---|
| RDY-0002 | 6 | NOT READY | CLOSED 2026-08-16, direct Owner acceptance, `EV-WAVE3-decisions-20260816.md` names who/when/what was declined (RDY-0003) | nothing |
| RDY-0003 | 6 | Named, not reviewing | unchanged | reviewer must actually run the review |
| RDY-0056/0057/0067/0088 | 6 | Waiting on RDY-0003 | unchanged | RDY-0003 |
| RDY-0004 | 6 | Instrument issued | unchanged — no Phase 3/4/5 briefs exist | briefs must exist first |
| RDY-0023 decision | 6 | Unsatisfiable, open | unchanged since PB-060 | Owner pick |
| RDY-0084 decision | 6 | Open wording question | unchanged | one word from Owner |
| RDY-0096 | 6 | Deferred to options card | options card ready since 08-16 19:24; Wave 3 (22:35) explicitly deferred again | Owner pick |
| RDY-0055 | 6 | Measured, undetermined | draft disclosure issued, awaiting RDY-0003's review | RDY-0003 |
| RB-17, D-12, D-14, D-15, D-16 | 6 | Open since 08-10 | no movement, files last touched 08-16 | Owner ratification |
| PB-057 letterhead | 6 | Open | unchanged | Owner scope statement |
| RDY-0095/U-3/D-3 | 7 | Commissioned to SkyEagle, outstanding | still outstanding — `EV-095-background-brief.md` explicitly labeled background research only, determination block blank; `grep -rn "SkyEagle" docs/` shows commissioning only | the actual determination |
| D-11 | 7 | Access-suppressed only | unchanged | counsel review |
| D-4 | 7 | Open | unchanged | native-Arabic proofreader |
| RDY-0078/U-4/U-5 | 7 | Secondary sources only | unchanged; `EV-092` §3.2 flags a possible primary-source lead (Locked Decisions Q21), not yet pulled forward | someone to read primary sources |
| B-11/B-16 | 7 | IP-allowlisted only | unchanged | D-3/D-4/D-11 clearance |
| RDY-0064 | 8 | Region decided, provisioning not started | unchanged — hosting pack (`ce9c2a124`) is pre-account checklist only | Owner opens cloud account |
| RDY-0081 | 8 | Blocked on hosting | unchanged | RDY-0064 |
| RDY-0082 | 8 | 5/6 legs pass | **leg 6 closed at PB-203** | nothing — closed |
| RDY-0085/B-10 | 8 | Needs domain/cert | unchanged | domain + provisioning |
| RDY-0084/B-29 | 8 | Requirements complete, needs host | unchanged | hosting, then implementation |
| B-09/B-18/RDY-0048 | 8 | Should be withdrawn, unrotated | attempted + DEFERRED at PB-213 — tool-permission system blocked every step | Owner rotates directly |
| B-05, B-06/B-07 | 8 | Adopted in spec, not executed | unchanged | hosting provisioning event |
| RDY-0092 | R5 | Open | substantively advanced, not closed — `EV-092` (PB-147) found a real conflict (Locked Decisions Q5 forbids `force_mfa`, contradicting RDY-0057/0099); BLK-001…006 still fully untraced | Owner resolves the Q5 conflict |
| RDY-0075 (V-1) | 9 | Not started | unchanged — zero calls; `EV-079-candidates.md` is desk research only | someone picks up a phone |
| RDY-0079 (V-9) | 9 | 30 named, 0 reached (implied) | confirmed: 30 named, 0 reached | the 5 outreach calls |
| RDY-0076/0077 | 9 | Not started | unchanged, zero calls | same as 0075 |
| RDY-0065/0066/0068/0069/0070 | 9 | Various held states | packs prepared, none executed; 0069 explicitly "not startable" without a pilot | real calls / legal review / a pilot |
| RDY-0088/0089 | 9 | In progress | 6 of 9 dossiers advanced | 3 remaining |
| RDY-0086 residual | 9 | Reviewer doesn't exist | reviewer re-affirmed, competence-basis statement still declined | Owner statement |

**Ranking check (subagent's own conclusion, adopted as-is):** RDY-0095 still holds the #1 spot, unchanged
in substance — the background brief explicitly disclaims being a determination. The Group 4 browser
session moved partially (RDY-0082 leg 6 closed) but RDY-0042/0043 are now understood as tooling-blocked
rather than merely unattempted. RDY-0075 has had literally zero movement. A close second, newly urgent
because it is *actively* tooling-blocked rather than merely unstarted: RDY-0048 (DB password rotation).

---

## 4. Spot-checks run before relying on the above

1. **RDY-0002 register drift** (found independently of the subagents): the §7.2 summary-table row
   (`:929`) was correctly updated by `aa39f8b7d`. The item's own detailed spec section (`:1249-1261`) was
   **not** — its `Status:` field still read `NOT READY` before this audit's correction. Confirmed via
   direct `Read` of both locations and `git show aa39f8b7d` (diff touches only line 929's table row).
2. **No collision on the PB-300 range claim**: `grep -n "^## PB-3[0-9][0-9]"` returned no hits before this
   audit's own entry was added; `git log --oneline -3` showed `b8fb75c90` (this audit's claim commit) at
   HEAD with no intervening commits from another agent.
3. **Merge commit on-branch**: `git log --oneline --merges -3` independently confirms `8e0eaba90 merge:
   adopt upstream/rel-820 (EV-045/PB-191 decision pack)` is present in branch history, corroborating both
   subagent reports (§1, §2) that reached the same conclusion via different methods (`merge-base
   --is-ancestor` vs. `git log`).
4. **RDY-0016 evidence files exist**: `EV-016-A10-acl-probes.md`, `EV-016-A10-fix-scope.md`,
   `EV-016-authorization-matrix.md` all present in `docs/evidence/`.
5. **RDY-0002 decision record**: `EV-WAVE3-decisions-20260816.md:10` opens with `## DECISION RECORD —
   RDY-0002`, confirming the subagents' characterization of it as a real, attributed decision record
   rather than an inferred closure.

---

## 5. Register `Blocks`-field derivation — how the corrected per-gate counts were built

Read `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` §7.2–§7.17 in full (all P0/P1/P2 rows,
domains A through P — lines 924-1099 approximately) and applied the document's own locked counting rule
(§47 "GATE COUNTING METHOD"): a P0 item counts toward a gate's open total iff its `Pri` = P0, its current
state is OPEN/NOT READY/BLOCKED, and its `Blocks` field explicitly names that gate.

Per-gate open-P0 ID sets derived this way (RDY- prefix omitted):

- **G0**: {0003, 0004} = 2 (was 3 before RDY-0002's closure)
- **G1**: {0016, 0033, 0034, 0038, 0042, 0043, 0056, 0057, 0060, 0061, 0062, 0086, 0090, 0094, 0095} = 15
- **G2**: {0023, 0025, 0033, 0034, 0038, 0041, 0042, 0043, 0044, 0094} = 10
- **G3**: {0016, 0045, 0047, 0048, 0055, 0064, 0065, 0066, 0068, 0071, 0073, 0081, 0083, 0084, 0085, 0096} = 16
- **G4**: {0004, 0090, 0095} = 3
- **G5**: {0003, 0004, 0016, 0056, 0057, 0060, 0061, 0062, 0067, 0071, 0078, 0086, 0088} = 13
- **G6**: {0004, 0041, 0045, 0047, 0060, 0062, 0064, 0065, 0066, 0067, 0069, 0071, 0073, 0075, 0076, 0077, 0078, 0085, 0088, 0096} = 20

Every count except G0 matches the PB-216 (2026-08-16) baseline exactly, confirming the register's own
`Blocks` fields are internally consistent even though the summary dashboards referencing them had drifted.
G0's count moves from 3 to 2 solely because RDY-0002 (whose `Blocks` field names only G0) is now closed.

This derivation is mechanical and reproducible: re-run by reading §7.2–§7.17's `Pri`/`Status`/`Blocks`
columns for any RDY ID and checking it against the three criteria above.
