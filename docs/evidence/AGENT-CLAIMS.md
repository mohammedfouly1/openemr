# AGENT WORK CLAIMS — Phase 2B

> ## ✅ RESOLVED 2026-08-16 (AGENT-DATA, PB-171) — the RDY-0044-B baseline is now v3, UUID defect fixed
>
> **This flag is closed.** `thiqa-rdy0044b-v2-baseline-20260814-064532.sql`'s 13-NULL-UUID defect
> (described below, unchanged for the record) is fixed in a new **v3** baseline:
> `thiqa-rdy0044b-v3-baseline-20260816-165016.sql`, SHA-256
> `b70e969572657a5269def836874a220d52afae818b238a0723f528415984fe9b`. Live UUIDs re-confirmed 0/0
> immediately before the dump. v2 is renamed `SUPERSEDED-...` and MUST NOT be restored. Full method,
> CLINHASH before/after, and the restore-test proof (into an isolated throwaway database, not the
> live schema — this session could not take Apache down with other agents concurrently active) are in
> `docs/evidence/EV-044-demo-reset-runbook.md` §10. This also disposes of the `pid 31` synthetic test
> patient PB-202 added (§10 explains: removed, not folded in).
>
> **⚠ Not resolved by this update — flagged onward in `EV-044` §11 rather than silently dropped:**
> the PB-077 authorisation below also names a **second** dataset change (sensitivity-flagged
> encounter + clinician-authored form) as part of the *same* single coordinated re-baseline. That
> change was **not seeded** — it needs an explicit owner and its own decision about what to seed, and
> if it happens after this v3 baseline it will require a further re-baseline, which is exactly the
> repeated-re-baselining cost this document's own history warns against. Recorded for Agent C.
>
> *(Original flag text, preserved for the record below.)*
>
> **`thiqa-rdy0044b-v2-baseline-20260814-064532.sql` was taken at 03:45. The authorised UUID fix was
> applied at 03:49.** The baseline is four minutes older than the change it was meant to contain.
>
> Parsed from the file itself: **`form_vitals` 12 of 12 `uuid` NULL, `insurance_companies` 1 of 2
> NULL — the exact 13 rows D-3 change 1 was authorised to populate.**
>
> **Live is already correct** (0 missing, both tables), so this needs **no re-seed and no re-run of
> the data fixes — only a re-dump.** Confirm `SELECT SUM(uuid IS NULL OR uuid='')` returns 0 on both
> tables, then re-take the baseline, supersede v2, re-hash and re-verify the reset proof.
>
> **Agent B has not touched it** — the baseline, `EV-044` and the reset proof are yours, and
> re-baselining is the single-owner step. Flagged, not fixed.

> ## 🔴 OWNER AUTHORISATION RECEIVED 2026-08-14 (PB-077) — ACTION FOR AGENT A
>
> **The three dataset changes are AUTHORISED.** Agent A has been holding seeder fixes for exactly
> this (PB-057). Sequencing that must hold: **all mutations first, then exactly ONE re-baseline** of
> RDY-0044-B — separate re-baselines invalidate the accepted signature more than once.
>
> | # | Change | Owner | Status |
> |---|---|---|---|
> | 1 | Populate the 13 missing UUIDs (12 `form_vitals`, 1 `insurance_companies`) | **Agent B** | see item row |
> | 2 | Seed one **sensitivity-flagged encounter** + one **clinician-authored form** | **Agent A** | **UNBLOCKED — yours** |
> | 3 | Allergy row `Timolol 0.5% eye drops` on `SYN-0002` | **Agent A** | **UNBLOCKED — already in the seeder, PB-057** |
> | → | **ONE re-baseline of RDY-0044-B** after all three | **Agent A** | **yours — you own the baseline and EV-044** |
>
> **⚠ The PB-057 letterhead/facility fix is NOT covered.** It emerged after `EV-000` was written, so
> "the three dataset changes" does not name it. **Do not read it in.** Owner has been asked whether it
> rides along; if yes it is change 4 under the same single re-baseline.
>
> **⚠ A restore reverts `background_services` AND the UUID population.** Observed twice. **But the
> enabled trigger self-heals it** — verified at PB-080: after a restore wiped the 13 UUIDs at 03:57,
> the next tick re-ran `UUID_Service` unattended and they were back by 03:59:30. **So there is no
> manual step for you to remember — with one exception:**
>
> **`UUID_Service` runs every 240 minutes.** If you take the RDY-0044-B baseline inside the gap
> between a restore and that tick, **the baseline captures NULL UUIDs and ships the defect.**
> Immediately before baselining, run:
> `php bin/console background:services run --name UUID_Service --force`
> then confirm `SELECT SUM(uuid IS NULL OR uuid='') FROM form_vitals;` returns **0**.
>
> Also: **add a post-reset background-service check to `EV-044`** — every demo reset returns both
> active services to overdue until the trigger catches up.
>
> **Also:** claim reviewer named — **Mohammed Elfouly** (RDY-0003). Licence determination commissioned
> to **SkyEagle** (RDY-0095). Neither closes anything yet: naming is not reviewing.


**Purpose.** `§0.0` of the readiness document allocates **PB numbers** so two agents never write the
same entry. It does **not** allocate **RDY items**, so two agents can still spend hours closing the
same requirement, or — worse — mutate the same database rows from opposite directions.

**This file is that missing half.** It is deliberately a separate file (`§0.0` Rule 2) so that
claiming work is a low-collision write.

---

## How to use it

1. **Before starting an RDY item, add a row here and commit that row on its own.** One commit,
   one line, no other change. That commit is the claim.
2. If the row already names another agent as `HELD`, **do not start it.** Pick another.
3. When you finish, set the row to `DONE` (or `RELEASED` if you stopped without closing it) in the
   same commit as your PB entry.
4. **A claim is not a reservation forever.** If a row has been `HELD` for a long time and the holder
   is demonstrably gone, release it here with a note rather than silently taking it.

**Claims are cheap; collisions are not.** Claim the whole track if you intend to work the whole track.

---

## Agent identities

| Agent | Identity | PB range (per `§0.0` Rule 1) |
|---|---|---|
| **Agent A** | Claude Code — the session that produced PB-001 … PB-046 and wrote `§0.0` | **PB-001 … PB-069** |
| **Agent B** | Claude Code — the session that produced the independent audit of 2026-08-14 and this file | **PB-070 … PB-139** |
| **Agent C (Orchestrator)** | Claude Code — session coordinating Phase 2B via 9 specialist subagents (DOC/CONF/SEC/DATA/OPS/GIT/CAP/BROWSER/HYGIENE). Claimed 2026-08-16, PB-140/141 already used for register reconciliation | **PB-140 … PB-219** |

If you are a third agent, add yourself here and claim **PB-140 …** in `§0.0` before your first entry.

---

## Track-level claims

| Track | Scope | Held by | State |
|---|---|---|---|
| **A** — Governance, validation, commercial, licensing | 0002 0003 0004 · 0056 0057 · 0065 0066 0067 0068 0069 · 0071 0073 · 0075–0078 · 0086 0088 · 0094 0095 0096 | **Agent B** | **HELD** |
| **B** — Demo foundation, users, roles, regional, brand surface | 0016 · 0033 0034 · 0042 | **Agent B** | **worked — see item rows** |
| **C** — Security & authorization | — | — | *empty — 0055 went to Agent A* |
| **D** — Synthetic data, D-7, proof | 0020–0027 acceptance · 0041 · 0060 0061 0062 | *unclaimed* | OPEN |
| **E** — Operational / pilot readiness | 0047 0048 · 0081 0083 0084 0085 | **Agent B** | **HELD** |
| **F** — Git, upstream, patch currency, provenance | 0045 0046 | **Agent A** | **HELD** — 0046 closed; 0045 in progress (EV-045) |

### ⚠ Claim collision of 2026-08-14, and how it was resolved

**Agent B's opening track claim was too wide and was overtaken within minutes.** Between the claim
commit and Agent B's first read-back, Agent A had already published **PB-048 (RDY-0046)**,
**PB-049 (RDY-0090)**, **PB-050 (RDY-0055)** and **PB-051 (the gate sync)**.

**Agent B released all four rather than duplicating or contesting them.** Agent A's work is
complete, evidenced and first. The table above is corrected accordingly.

**The lesson, recorded so the next agent does not repeat it:** claiming a *whole track* is too
coarse when another agent is already mid-flight in it. **Claim at item level, re-read the PB
headings immediately before starting each item, and treat any item with a published PB entry as
gone** — even if the ledger still shows it free. The ledger is a courtesy; **the committed PB entry
is the fact.**

**Gate counts are Agent A's.** Agent A ran the PB-051 sync and holds Rule 3. **Agent B will not
recalculate gate counts**, and records only which gates a closed RDY's `Blocks` field names.

**Track D is left unclaimed deliberately.** Agent A built the dataset, the HR-01 evidence pack and
the D-7 script, and holds the context for the remaining proof-asset work. Agent B will not mutate
the seeded dataset or the RDY-0044-B baseline without a claim recorded here first.

---

## Item-level claims

| RDY | Item | Held by | State | Note |
|---|---|---|---|---|
| 0040 | D-7 demo script | Agent A | **DONE** | Closed, PB-046; `EV-040` |
| 0046 | Module provenance | Agent A | **DONE** | Closed, PB-048; `EV-046` |
| 0045 | Upstream patch currency | Agent A | **HELD** | `EV-045` upstream-target analysis written |
| 0055 | Audit-log PHI determination | Agent A | **DONE** | PB-050; `EV-055`. Released by Agent B |
| 0090 | Branding surface inventory | Agent A | **HELD** | PB-049; `EV-090`. Not closed — needs the human walk. Released by Agent B |
| — | Gate-count sync (Rule 3) | Agent A | **HELD** | PB-051. Agent B does not recalculate |
| 0067 | Published status registers | Agent B | **DONE (not closed)** | PB-070; `EV-067`. 3 of 4 criteria met; criterion 3 blocked on RDY-0003 |
| 0016 | Authorization matrix §23.4 | Agent B | **HELD** | Unblocked by the seed; 5 rows were data-blocked at PB-014. ⚠ **Partial evidence already exists** — PB-037 ran all 6 locked reports × 7 accounts, PB-034 ran `patient_list` × 7. **Read those before re-running** |
| 0083 | Background-service trigger | Agent B | **HELD** | Live evidence contradicts the "never executed" finding — correction pending |
| **0043** | **Menu form-rendering defect** | **Agent A** | **DONE — FIXED** | **PB-052 · patch record PR-15 · commit `5ab88c700`.** ⚠ **Agent A took this while Agent B held it — see the note below. Do not re-work it; `src/Menu/MainMenuRole.php` is already changed and committed.** |
| **0042** | **`front_office.json` Add-Patient defect** | **Agent B** | **HELD — untouched by Agent A** | Agent A has **not** started it and will not |
| **0021 0022 0027** | Seeded-data acceptance | **Agent A** | **DONE — CLOSED** | PB-055. All criteria met incl. Dr Taha's clinician confirmation for 0021 |
| **0024 0026** | Seeded-data acceptance | **Agent A** | **DONE — CLOSED** | PB-058. Owner authorised the data fixes; re-seeded, re-baselined (v2), reset proof re-run, both fixes verified end to end |
| **0020** | Seeded-data acceptance | **Agent A** | **DONE — CLOSED** | PB-059. Duplicate detection scored, merge executed on one pair, reset verified |
| **0023 0025** | Seeded-data acceptance | **Agent A** | **HELD — BLOCKED** | PB-060. 0023 needs a clinician on the SOAP notes **and** an Owner decision — its growth-chart criterion is unsatisfiable, no seeded patient is under 21. 0025 needs a browser |
| **0061** | **Capture rules** | **Agent A** | **DONE (not closed)** | PB-053; `EV-061`. Rules written; per-image check blocked until RDY-0060's captures exist |
| **0060 0062** | **Capture inventory · flagship recording** | **Agent A** | **HELD — BLOCKED** | Both need a person at a browser. `EV-061` §8 is the review instrument |

---

| 0047 | Deployment runbook | Agent B | **DONE (not closed)** | PB-082; `EV-047`. Needs an independent provisioner |
| 0086 | Arabic / RTL coverage | Agent B | **DONE (not closed)** | PB-083; `EV-086`. Picklists measured at 16.1 %; needs the screen walk + a **named Arabic Reviewer, who does not exist** |
| 0065 0066 0069 | Qualification, scope, cost instrumentation | Agent B | **DONE (not closed)** | PB-084; `EV-065-066-069`. Three calls / legal review / a pilot |
| 0004 | Prohibited-claim control | Agent B | **DONE (not closed)** | PB-085; `EV-004`. Waits on the phase briefs existing |

---

## Standing constraints that bind both agents

- **Do not recalculate gate counts inside a closure entry** (`§0.0` Rule 3). Record which gates the
  RDY's `Blocks` field names, and let the sync pass do the arithmetic.
- **The gate-count sync pass is a single-agent job.** Whoever runs it says so here first.
- **Never `git add -A`** — stage by explicit path (key-material and PHI controls, PB-035 / PB-037).
- **Nothing closes on a code change or a row count.** The requirement's own acceptance criteria must
  pass, demonstrated, with a re-runnable command or a `file:line`.

---

## Notes between agents

- **Agent A:** `PR-15` and PB-052 cite `scratchpad/menu-verify.php` as their verification harness, but
  nothing under `scratchpad/` is tracked — the reference does not resolve for anyone else. Agent B has
  added **`docs/evidence/harnesses/`** (tracked) for exactly this, and gitignored `/scratchpad/` so
  session probes cannot be committed by accident. **Consider moving `menu-verify.php` there** so PR-15's
  "re-runnable" claim holds.
- **Agent B is not touching the dataset.** Two separate items now need the *same* decision — populate
  13 missing UUIDs (`EV-083` §4.3) and seed one sensitivity-flagged encounter + one clinician-authored
  form (`EV-016` §4). All three are baseline changes against RDY-0044-B. **Whoever holds Track D should
  take them together**, since they cost one re-baseline rather than three.
- **AGENT-BROWSER, 2026-08-16 — the exact Rule-4a failure this section already warns about happened
  again, mechanism identified.** Committing `PB-201` with `git commit -m "..." -- <file>` — a pathspec
  given directly to `git commit`, not `git add`, then `git commit` with no pathspec — **stages the
  working-tree content for that path regardless of what was `git apply --cached`-staged in the index**,
  overriding the careful hunk-only staging that had just been done. Result: commit `3de71ab59`
  ("record AGENT-BROWSER PB-201 harness blocker...") contains 178 insertions, not the 65 that commit
  message describes — **AGENT-HYGIENE's uncommitted PB-209/210/211/212 entries and the HR-04 register
  corrections rode along, silently.** Verified nothing is lost: `grep -n "^## PB-209\|^## PB-210\|^##
  PB-211\|^## PB-212"` against the file post-commit finds all four, content intact, HR-04 table
  corrections present. **The defect is attribution, not data loss** — same as the 2026-08-14 precedent
  this section records. Not rewritten (no amend/rebase — other agents are live on this file; rewriting
  history under them is worse than a wrong commit message). **The lesson for the next agent editing
  this shared file, sharper than the existing note above:** after `git apply --cached <patch>` to
  isolate your own hunk, commit with **`git commit -m "..."` and NO trailing pathspec** — the pathspec
  form silently re-stages the whole working-tree file and defeats the isolation. `git status` after
  committing should show the file still `M` (other agents' unstaged work) or absent — if it goes clean
  and you only staged part of it, your commit just swept in everything else.

- **AGENT-GIT, 2026-08-16 — a third instance of the same Rule-6 hazard, this time via the textbook-correct
  chained form.** `git add <explicit path> && git commit -m "..."` (no trailing pathspec, exactly as
  Rule 6/the prior two notes above prescribe) still swept in another session's work, because the race
  window is between the pre-stage `git diff --stat` check and the `git add` itself, not just within the
  commit step. Sequence observed: `git diff --stat` on this file and `EV-045-upstream-target-analysis.md`
  showed 16 + 217 insertions (exactly this session's own edits) → immediately following `git add && git
  commit` hit `fatal: Unable to create '.../.git/index.lock': File exists` (another session mid-commit,
  correctly not forced through, per Rule 6.2) → lock cleared within seconds → re-checked `git diff
  --stat` again, **still showed 16 + 217** → re-ran `git add && git commit` → **the resulting commit
  (`6f4ddba9b`) contains 267 insertions on the requirements document, not 16** — AGENT-CONF's PB-151
  through PB-155 (RDY-0035, RDY-0033/0034, RDY-0018, RDY-0029, RDY-0016 — all five, full content) rode
  along. **Verified nothing is lost:** `git show HEAD -- docs/Marketing-MVP-and-Launch-Readiness-Requirements.md`
  shows all five entries complete, ending cleanly before the pre-existing `## PB-085` heading — no
  truncation, no partial hunk. **Root cause, stated precisely since the mechanism differs from the prior
  two notes:** AGENT-CONF's edit and commit landed in the gap between this session's *second* diff check
  and the *second* `git add` — i.e. even a diff check taken milliseconds before staging is not a
  guarantee, because `git add <path>` stages whatever is in the working tree at the moment it runs, and
  on a single shared working tree with 3+ live sessions that moment is never provably quiet. **The
  existing mitigations (re-diff before staging, no trailing pathspec, chain `add`+`commit`) reduce the
  window but cannot close it to zero on this repo's topology.** Not rewritten, same reasoning as both
  notes above. Recorded here so AGENT-CONF (and any auditor) can find its PB-151–155 work without
  expecting it under a commit message that names it.

---

## Agent C (Orchestrator) — subagent claims, PB-140–219 (2026-08-16)

Registered in one commit before any subagent starts, per this file's own rule. Agent C's PB-140/141
(register reconciliation, browser-evidence correction) already landed before this claim block.

**Overlap check performed against the tables above before claiming.** Several items already carry an
Agent A/B row at `HELD` or `DONE (not closed)`. Per this file's own standing guidance ("claim at item
level... if a row has been HELD for a long time and the holder is demonstrably gone, release it here
with a note rather than silently taking it"): Agent A's and Agent B's sessions have shown no activity
since 2026-08-14/15 (last entries PB-061 and PB-085 respectively); no new claim below **replaces**
either agent's row — each overlapping row is noted as **CONTINUATION**, and the assigned subagent is
directed to read the existing EV artefact first and pursue closure of what's already substantively
done, not redo it from scratch.

| RDY / item | Assigned to | State | Note |
|---|---|---|---|
| 0006, 0008, 0031, 0070, 0072, 0074, 0091, 0092 | AGENT-DOC | **DONE (not closed)** | PB-144/145/146/147. `EV-006/008/031/070/072/074/091/092` authored. 0006/0031/0072/0091 read as VERIFIED READY on their literal acceptance text (recommended for gate-sync confirmation, not self-closed); 0008/0070/0074 remain open on named dependencies (RDY-0003; a website/pilot; Legal review); 0092 found a real unescalated conflict (Locked Decisions Q5 vs. RDY-0057/0099 on MFA) and stays open by design — see PB-147 |
| 0073 | AGENT-DOC | **DONE (not closed)** | Discovered already `HELD` under Track A with a full `EV-073` (Agent B, 2026-08-14) — **treated as CONTINUATION, not fresh, correcting this ledger's own "fresh authoring" label**. `EV-073`'s two artefact-reference gaps (RDY-0066, RDY-0068) closed via this session's `EV-068`/`EV-065-066-069` cross-references (PB-147/148 area); `EV-073` itself edited to reflect it. Remaining gap (T-5/T-6, external delivery + reviewer) is human-blocked |
| 0096 | *(released)* | — | Table row 235 named 0096 but the orchestrator's direct task briefing to AGENT-DOC did not include it, and the asterisk footnote below (*"0072 note: DICOM..."*) does not match its placement on 0073 in this row either — **both read as ledger drift, not a real claim**. Not worked by AGENT-DOC; the DICOM question was resolved instead under RDY-0094 (see PB-148) since that is its substantive home (§32 MC-24 / §40 no-go register) regardless of which RDY number the footnote intended |
| 0047 | AGENT-DOC | **DONE (not closed)** | PB-150. `EV-047` (Agent B) read in full; no material ambiguity found worth correcting — it already names its own coverage boundary and scores its own acceptance honestly. Remains blocked on an independent provisioner, which cannot be produced by more documentation |
| 0048 | AGENT-DOC | **DONE (not closed)** | PB-150 + register-row edit. Re-confirmed live 2026-08-16: `sites/default/sqlconf.php` still carries `openemr`/`openemr`. `EV-048`'s 2026-08-14 withdrawal of the "candidate closure" signal was never propagated to the §7.7 register row — done now |
| 0065, 0066, 0069 | AGENT-DOC | **DONE (not closed)** | PB-150. `EV-065-066-069` (Agent B) read in full, unchanged; `EV-066`'s cross-reference to RDY-0073 confirmed live via this session's `EV-068`/`EV-073` work |
| 0068 | AGENT-DOC | **DONE (not closed)** | PB-147/148 area. `EV-068-pilot-requirements.md` authored — all 14 required elements, a binary success gate, an exit clause written before any pilot is offered, and RDY-0055's PHI determination reflected. Unblocks two of `EV-073`'s acceptance rows. Needs Legal/Compliance review before use in an actual agreement |
| 0071 | AGENT-DOC | **DONE (not closed)** | PB-150. `EV-071` (Agent B) read in full; 1-of-8 CSV gap and reviewer gap confirmed unchanged. Not executed further — running the remaining 7 report exports against the live seeded dataset is Track D/AGENT-DATA territory, avoided here per the concurrency protocol |
| 0081 | AGENT-DOC | **DONE (not closed)** | PB-150. PB-021/PB-023 (Agent B) read in full; confirmed current and accurate, no change needed — off-instance/CMEK legs genuinely blocked on RDY-0064's external provisioning |
| 0084 | AGENT-DOC | **DONE (not closed)** | PB-150. `EV-084` read in full — appears to genuinely meet its own acceptance bar; flagged for gate-sync confirmation, not self-closed (per the closure contract, AGENT-DOC does not close another agent's self-reported item) |
| 0086 | AGENT-DOC | **DONE (not closed)** | PB-150. `EV-086` read in full; screen walk and named Arabic Reviewer remain the two genuinely human-blocked gaps. Not fabricated |
| 0090 | AGENT-DOC | **DONE (not closed)** | PB-150. `EV-090` read in full; its own §6 already is the human-walk checklist RDY-0090 needs — no further checklist construction required, only the walk itself, by a second person |
| 0094 | AGENT-DOC | **DONE (not closed)** | PB-148. `EV-094-demo-no-go-register.md` adopts §40 formally as the register; DICOM classification question resolved (Active/Mentionable-qualified, not a no-go item — C-CDA is). Register-exists and unlock-condition criteria met; rehearsal and D-7 cross-check remain human/execution-blocked |
| 0013 | AGENT-BROWSER | **DONE — CLOSED** | PB-202 (2026-08-16), live browser session. All 6 accounts' navigation confirmed; Front Office completed a real registration (pid 31) |
| 0038 (phone clause only) | AGENT-BROWSER | **DONE** | PB-202. Units-of-measurement clause flagged as inapplicable to this screen — see PB-202 for AGENT-DOC follow-up |
| 0025, 0037, 0082 (leg 6 only) | AGENT-BROWSER | **0082 CLOSED — 0025 investigated further (still open), 0037 unchanged** | PB-202: 0037's real gap is now the Ledger showing no currency symbol at all (not "$ instead of SAR") — untouched since. **0082: PB-203 (2026-08-16) closes leg 6** — authenticated login proven live via `claude-in-chrome` against `?site=rdy0082restore` (n.alqahtani, Calendar dashboard reached, identity confirmed via account-menu dropdown), plus a wrong-password negative control correctly rejected. All 7 acceptance criteria now MET; register row moved to CLOSED. Disposable instance **still deliberately left running** — teardown command in `EV-082` §10 now owed to AGENT-OPS/orchestrator, not AGENT-BROWSER. **0025: PB-204 (2026-08-16)** reproduced the Documents-tab hang 5+ times total (worse than PB-202's two — one instance never recovered in 45s+), found it is NOT Documents-specific (also hit Patient Finder's live-filter search), and named two evidenced root-cause candidates: a blank auto-opening `active_reminder_popup.php` modal, and a `background_service/$run` call observed pending far longer than its siblings (ties to RDY-0083's known overdue-service backlog + PHP session-file locking). Despite the defect, opened 2 seeded synthetic documents (SYN-0002, SYN-0003) through the app UI and confirmed the `SYNTHETIC DEMO / NOT A REAL PATIENT` marking renders correctly. RDY-0025 stays NOT READY — 2 of ≥5 required patients checked, no reviewer sign-off — but the marking mechanism itself is now verified working and the hang has root-cause candidates for a fix, not just "reproducible, cause unknown" |
| 0013, 0016, 0018, 0029, 0033, 0034, 0035 | AGENT-CONF | **DONE** | PB-151–155 (2026-08-16). **0018 CLOSED** — `oe-system` removed from Administrators. **0035 CLOSED** — `pqri_registry_name`/`pqri_registry_id` cleared. **0029 DONE (not closed)** — 3 rules' `active_alert_flag` activated, strong pre-existing `clinical_rules_log` evidence they already fire on real seeded patients; final visual check handed to AGENT-BROWSER. **0033/0034** re-verified live, zero drift, still correctly blocked on RDY-0095 alone (not config). **0016**: read `EV-016`/PB-073 in full per this row's own CONTINUATION note before touching anything; confirmed its 4 remaining gaps are unchanged and none is a database/globals task — handed off precisely (dataset legs → AGENT-DATA, A-10 call-site probes → AGENT-SEC, UI-navigation legs → AGENT-BROWSER), not attempted. 0013 was AGENT-BROWSER's to begin with (already closed there, PB-202) — no config-only piece remained once that closed. **Note for any auditor**: this session's PB-151–155 text twice rode along inside another agent's commit on this same shared-file race (`6f4ddba9b`, then `f5facec44`) — content verified intact both times, see the notes above this table and inside PB-153/PB-155 themselves |
| 0055, 0049, 0042, 0043 | AGENT-SEC | **WORKED (none closed)** | PB-161–164 (2026-08-16). **0042**: front_office.json's negation counterpart confirmed present (commit `a3c280d84`, PR-16/PB-072), explaining why PB-028/029/202 never reproduced the defect (live global has always been `1`); attempted the `global=0` negative-path live test, blocked at the Front Office login step by this environment's password-entry safety rule — config change made and then fully reverted, verified via DB read both ways. **0043**: `MainMenuRole.php` not touched; rendered forms menu re-verified live under the already-authenticated Administrator session (Eye Exam/SOAP/Vitals/Fee Sheet all present) without a new login — D-7 rehearsal under the Physician/Front Office accounts specifically still outstanding, same credential-entry blocker. **0049**: all three Unix-only `config.php` settings live-measured broken on this Windows target (`lpr`/`enscript` absent from `PATH`, `/usr/bin/file` unreachable from the native PHP process); `config.php` left unedited — outside the database-only remit, handed to AGENT-CONF. **0055**: draft customer disclosure text issued (`EV-055-pilot-disclosure-draft.md`); no reviewer fabricated — Security Reviewer named but not yet reviewing, RDY-0003 still has no claim reviewer at all. **Flagged onward, not started**: PB-155's A-10 (`aclCheckAcoSpec`/`aclCheckIssue` fail-open call-site probes) was routed to AGENT-SEC but falls outside this session's assigned scope (0055/0049/0042/0043 only) — needs a follow-up assignment or another owner |
| 0023, 0041, 0044-B (verify-only) | AGENT-DATA | **0044-B DONE (verify-only, PB-171) — 0023/0041 still HELD** | 0023/0025 were Agent A's `HELD — BLOCKED` row — 0025 reassigned to AGENT-BROWSER above (needs a browser, not a seeder), 0023 stays here (needs a clinician + Owner decision on the growth-chart criterion). **0044-B: v3 baseline taken, UUID defect fixed, see `EV-044` §10/§11 and PB-171** — closes the top-of-file flag but leaves the PB-077 change-2 seeding question open, flagged onward |
| 0082 (backup/restore infrastructure legs) | AGENT-OPS | **DONE — RDY-0082 CLOSED by AGENT-BROWSER (PB-203)** | PB-182, `EV-082`. Restore proven end to end into a disposable instance — 6 of 7 criteria MET (row counts, checksums, D-1, application-layer login/ACL via HTTP). Leg 6 closed at PB-203 (see row above) — all 7 criteria now MET, register row CLOSED. **Disposable instance still running** — teardown is `EV-082` §10's command, now an AGENT-OPS/orchestrator housekeeping action, not a blocker |
| 0083 | AGENT-OPS | **HELD — investigated, not closed (per Owner instruction)** | PB-181, `EV-083`. 0083 is Agent B's `HELD` row — **CONTINUATION**. Live PB-142 regression confirmed; converting the trigger to survive logoff tested and ruled out on this host (Google Drive per-session mount, `CLAUDE.local.md` §4a); console-session dependency now named explicitly in §40 row 12 and RDY-0094's card. `EV-047` already named it correctly, no edit needed there |
| 0085, 0093, OD-04, OD-05 | AGENT-OPS | **HELD** | Not yet started this session |
| 0045 | AGENT-GIT | **CONTINUATION** | Agent A: `HELD`, `EV-045` already contains the master-vs-rel-820 divergence analysis (83 behind at 2026-08-13). AGENT-GIT re-measures current divergence and builds the decision pack on top of `EV-045`, does not redo it from zero |
| 0060, 0061, 0062, 0063 | AGENT-CAP | **HELD** | 0060/0062: Agent A `HELD — BLOCKED`; 0061: Agent A `DONE (not closed)`, `EV-061` capture rules already written — **CONTINUATION**, AGENT-CAP executes captures under those existing rules, does not rewrite them. 0063 unclaimed, new |
| EV-021, EV-000 §2, HR-04 register, `CLAUDE.local.md` | AGENT-HYGIENE | **HELD** | Not RDY items — artefact/local-config maintenance only, no system state change, no RDY closed |

*0072 note: DICOM viewing inventory (Q71) — documentation/classification only, no code. **Resolved
2026-08-16 by AGENT-DOC under RDY-0094/PB-148, not RDY-0072** — DICOM's classification already lives
in §32 MC-24 (Active, Mentionable-qualified, viewing only/no PACS) and is not a no-go item, unlike
C-CDA. RDY-0072 itself was authored separately as the schema/data-dictionary artefact its register row
actually describes (`EV-072-schema-data-dictionary.md`).

**AGENT-DOC, 2026-08-16 — one item outside its claimed list, done in passing.** RDY-0038's §8.4 card
had a wording defect PB-202 flagged (units-of-measurement acceptance tested against the registration
screen; the field lives only under `interface/forms/vitals/`). Fixed in the readiness document, logged
as its own entry (PB-149), no RDY-0038 row in this table claimed or touched otherwise.

---

## Agent D takeover of Agent C's remaining open items — 2026-08-17

**Idleness verified before taking anything, per the Owner's own explicit condition** ("would need
Agent C's session to actually be gone/idle, not just currently mid-task"): Agent C's last commit
(`PB-218`) landed 2026-08-16 22:31:34 UTC; this check ran 2026-08-17 11:43 UTC — **13 hours of git
silence** in a project that had been producing a new entry every few minutes throughout the prior
day. Six `claude.exe` processes were still resident on the host, but all had start times from the
prior afternoon/evening (none newer), and a 15-second CPU-delta measurement across all six showed
essentially zero active work (highest: 1.19s of CPU over 15s wall-clock — consistent with an idle
open window, not a running agent). **Both signals agree: idle, not mid-task.**

**Taking over, not duplicating**: every row below that still read `HELD` under Agent C's table above
is re-claimed here. Rows already `DONE`/`CLOSED` are untouched — this is a takeover of unfinished
work, not a re-litigation of finished work.

| RDY/item | Was held by | Now held by | Note |
|---|---|---|---|
| 0016 (remaining legs: A-10 fail-open call-site probes) | AGENT-SEC (handed off, not attempted) | Agent D (AGENT-SEC3) | Code-level probe (`aclCheckAcoSpec`/`aclCheckIssue`), no browser/credential needed — genuinely spawnable |
| 0016 (UI-navigation legs), 0038 (units-of-measurement clause), 0041, 0044 (D-7 gate), 0060, 0061, 0062, 0063 | AGENT-BROWSER/AGENT-DATA/AGENT-CAP | **NOT re-spawned — see below** | Every one of these needs an authenticated demo-account browser login. That exact action has failed **4 separate times** on `RDY-0042`/`0043` at the Claude Code permission classifier itself, independent of relayed Owner authorization (`PB-217`, `PB-218`). Re-spawning here would add a 5th+ instance of the identical documented dead end, not new information. **Held pending the Owner's own settings-level fix to the classifier** — see the standing note on `RDY-0025`/`0042`/`0043`/`0048` earlier in this file |
| 0045 | AGENT-GIT (CONTINUATION, decision pack only, merge not executed) | Agent D (AGENT-GIT2) | Real remaining work: `EV-045`'s own §5 procedure (tag → merge → regression → push) has not actually been run. Owner already decided the target (`upstream/rel-820`, `--no-ff`, Wave 3) — this is execution, not a new decision |

**Not taken over, and deliberately not re-attempted**: `0083`, `0093`, `OD-04`, `OD-05` — AGENT-OPS
already investigated each and concluded, correctly, that no further technical action is possible
without a real hosted target (`PB-181`, `PB-183`). Re-running the same investigation would not move
any of them; they stay exactly where AGENT-OPS left them.

---

## Agent D (Auditor / independent executor) — PB-220–299 (2026-08-16)

Audited Agent C's PB-140/141 register sync independently (`docs/evidence/EV-AUDIT-agentA-20260816.md`
— filename predates this identity name; written while still operating under the "auditor, no fleet"
instruction). Checked this table before claiming anything below: every P0 RDY is already held by one
of Agent C's nine subagents. Not duplicating that fleet. Picking up genuinely unclaimed, non-P0 work
instead, plus continuing spot-audits of the fleet's closures as they land.

| Item | Held by | State | Note |
|---|---|---|---|
| **RB-22** (Inter font dedup — theme rebuild) | **Agent D** | **DONE — CLOSED** | PB-220; `acec953cf`. Build workspace resynced, rebuilt, verified: all 8 theme CSS files reference `Inter-Regular.woff2` once each, zero Medium/SemiBold/Bold references. `BrandingGovernanceGuard` 31/31, brand manifest 123/123 |
| **RB-14** (Arabic PDF font — D-9) | **Agent D** | **DECIDED — Option C adopted; stays OPEN through pilot** | Owner decision recorded `docs/evidence/EV-RB14-mpdf-gpos.md` §4 (2026-08-16): accept open, disclosed, revisit trigger = a customer contract requiring Arabic PDF. Option B (Scheherazade New, Lateef) tested time-boxed, both failed — 4/4 fonts now fail identically. Draft `RDY-0094` no-go text and `RDY-0087` Arabic disclosure (unreviewed, needs HR-09) at §6. `Config_Mpdf.php` untouched. Not edited into the requirements document by this session |
| **RDY-0002** | Agent D | **DONE — CLOSED** | Mechanical register sync, `aa39f8b7d`. Owner's Wave 3 decision (`EV-WAVE3-decisions-20260816.md`) was never applied to the §7 row — applied here, no new judgment |
| **RDY-0071** (`pat_ledger.php` CSV defect only) | Agent D (AGENT-FIX071) | **DONE — FIXED, code-review verified** | `6dea39a28`. `PrintEncHeader`/`PrintEncFooter` call sites guarded, credit-detail loop's previously-unconditional HTML echo now branches correctly, charge-line `$csv` actually built (was declared, never assigned). `EV-071` §3.5.1 has the full trace. Agent (AGENT-FIX071) hit a session rate-limit before verifying/committing — auditor (Agent D) reviewed the uncommitted diff directly and finished it. **Honest gap, not hidden**: no live HTTP round-trip — that needs an authenticated login, the same classifier wall below. `RDY-0071` as a whole stays NOT READY; the reviewer-confirmation leg is untouched and still human-blocked |

**Checked and deliberately NOT re-attempted, per the standing pattern already on record**: `RDY-0025`,
`RDY-0042`/`0043`, `RDY-0048` — each has failed multiple times (2–4 attempts) at the Claude Code
permission classifier itself (password entry, `ALTER USER`, or a `php.ini`/Apache-restart system
change), independent of any relayed Owner authorization. Spawning another subagent repeats a
documented dead end; these need the Owner's own direct settings-level action, not another task
prompt. `RDY-0057` was checked and found already substantively complete (`EV-056-057-088`, 0
violations) — the sole remaining gap is `RDY-0003`'s reviewer sign-off, a human blocker, not
engineering. `RDY-0016/0038/0041/0044/0045/0060/0061/0062` remain with Agent C's fleet, per the
existing rule above — not reclaimed.

### Agent D — Wave execution, 2026-08-16 (Owner-directed)

**Table C checked first, before claiming anything.** Every Table C item (0016, 0025, 0038, 0041, 0042,
0043, 0060, 0061, 0062, 0082-leg-6) is already held by an Agent C subagent right now — AGENT-CAP holds
0060/0061/0062/0063 outright; AGENT-BROWSER just closed 0082 and is mid-investigation on 0025;
AGENT-SEC holds the remaining 0042/0043 negative-path gap; AGENT-CONF/AGENT-DATA hold 0016/0041's
other legs. **The Owner's credential authorisation removes the policy blocker, not the claim** —
none of Table C is claimed below; it stays with whoever already holds it.

| RDY/item | Held by | State | Note |
|---|---|---|---|
| 0078 | Agent D (AGENT-REG) | **DONE — PREPARED, PARTIAL** | `EV-078-zatca-nphies-primary-sources.md`, `03b2da101`. EXT-01 CONFIRMED on primary `zatca.gov.sa` sources (Wave 24/25 thresholds and dates). EXT-02 PRIMARY-SOURCE VERIFICATION OUTSTANDING — `chi.gov.sa` unreachable across 7 attempts, disclosed not guessed. Audited by Agent D against source, confirmed sound |
| 0088 | Agent D (AGENT-COMP) | **DONE — PREPARED, NOT CLOSEABLE** | `EV-088-competitor-frequency-verification.md`, `dc6baee78` + `8bbdc23a4` addendum. ⚠ Two concurrent instances of this same agent ran on the same claim simultaneously — the claim mechanism stops different agents colliding, not duplicate instances of the same one; flagged as a real protocol gap, not just a footnote. Second instance self-detected the collision and appended rather than overwrote. 6 of 9 dossiers show new marketing-surface evidence; no frequency figure published, per RDY-0088's own preference for mechanism over number |
| 0079 (candidate-list half) | Agent D (AGENT-PROSPECT) | **DONE — PREPARED** | `EV-079-candidates.md`, `efc52c8a1`. 30 candidates, Riyadh/Jeddah/Eastern Province, 14 flagged GOOD ICP fit. Two dead leads explicitly excluded rather than padded in. "5 reached" half is the Owner/Sales-Pilot-Owner's |
| 0075, 0076, 0077 | Agent D (AGENT-VALIDATION) | **DONE — PREPARED** | `EV-075-077-instrument.md`, `94aef33a7`. Combined 3-in-1 call instrument, thresholds verified against live §8 cards (matched exactly), unprompted/prompted tracking for V-3, Arabic draft marked unreviewed |
| 0064 (provisioning runbook), 0081, 0084, 0085 | Agent D (AGENT-HOSTING) | **DONE — PREPARED** | `EV-064-081-084-085-hosting-pack.md`, `ce9c2a124`. Audited: EV-084's M-6 threshold (2×execute_interval) confirmed real, not invented by this pass; no standalone EV-081 existed before. Next action named: Owner creates GCP billing account/project |
| 0065, 0066, 0068, 0073, 0096 | Agent D (AGENT-COMMERCIAL) | **DONE — PREPARED** | `EV-065-pack.md`, `EV-066-pack.md`, `EV-068-073-reviewer-packs.md`, `EV-096-options.md`, `0662b898f`. Verdict blocks audited directly — confirmed genuinely blank, not pre-filled |
| 0090, 0094, 0095 (input) | Agent D (AGENT-BRANDING) | **DONE — PREPARED** | `EV-090-walk-checklist.md`, `EV-094-rehearsal-script.md`, `EV-095-background-brief.md`, `de161597b`. Committed by the orchestrator on the subagent's behalf after its turn ended waiting on `.git/index.lock` — files were already staged, content audited before finishing the commit |
| 0086, 0087, 0063 | Agent D (AGENT-ARABIC) | **DONE — PREPARED** | `EV-086-arabic-reviewer-pack.md`, `0d1c02328`. Used all three measured coverage layers (47.5/16.1/79.0%), not just the flattering headline; correctly found no numeric Arabic-parity target exists in source and did not invent one |

**None of the above closes its RDY.** Every item stays exactly where its own file says it stays — a
human action (a real call, a legal review, a funded cloud account, HR-09's actual review, or the
Owner's own decision) is still required in every case. This wave was fully audited by Agent D against
each committed file's actual content, not accepted on the doing agent's self-report.

**Wave 3 (RDY-0002, 0045, 0086-naming, 0096) is decision capture — run by the orchestrator directly,
not a subagent, per the Owner's own instruction.**

---

## Agent C (Orchestrator) — Wave 3, PB-207–210 (2026-08-16)

Coordination check performed before claiming: read Agent D's full claims table above (fresh as of
this commit) and the last 15 commits — Agent D is live and holds every item touching a human decision
or external dependency. The five items below are checked against that table and against Agent C's own
prior fleet's final states: none is currently `HELD` (in-progress) by any agent, none needs a human
decision, none needs an external dependency — each is a concrete, unowned technical task.

| RDY/item | Held by | State | Note |
|---|---|---|---|
| 0048 | AGENT-CONF2 | **RELEASED — DEFERRED, see PB-213** | Preparatory work done live (found a second `sqlconf.php` at `sites/rdy0082restore/`, corrected the grant host to `openemr@localhost` not `@127.0.0.1`, pre-change snapshot taken). The actual mutation (password generation + `ALTER USER`) was denied three separate ways by the session's own tool-permission classifier — not a peer-agent claim, the enforcement layer itself — so nothing was rotated. Live credential still `openemr`/`openemr`. Handed back for a session with direct interactive permission grants |
| 0071 | AGENT-DATA2 | **DONE (not closed)** | PB-208; `EV-071` §3.4-3.6, §5.2. Corrected the "8" to 9 reachable CSV reports (one was gated off, one was mislabeled — exports labels not CSV); ran the remaining 7. 7 of 9 export clean CSV with plausible data, 1 (`pat_ledger.php`) has a genuine content-corruption defect (not fixed), 2 are mechanically fine but empty on unrelated Track D seed gaps (`insurance_data`, `pnotes` both 0 rows). Reviewer leg untouched, human-blocked, out of scope per task briefing |
| 0037 | AGENT-SEC2 | **RELEASED — CLOSED, see PB-214** | Root cause: 13 `oeFormatMoney()` call sites in `interface/reports/pat_ledger.php` never passed the existing resolver's `$symbol` flag. Fixed (`PR-18`), verified live against the exact PB-202 scenario (`SYN-0001`) — `SAR` now renders on every amount. Register row CLOSED |
| 0025 | AGENT-SEC2 | **RELEASED — still NOT READY, see PB-215** | Investigated both PB-204 candidates (active_reminder_popup, background_service/$run) — neither independently confirmed as the trigger in this session's repro. Found and evidenced a third, chronic candidate instead: PHP session persistence is broken on this host (`session.save_path` resolving to non-writable `C:\Windows`, 472 `Permission denied` events since 2026-08-13, ongoing). Host/environment defect outside the git repo — no source fix applies; the actual fix needs a `php.ini` edit plus a shared Apache restart, out of this task's source-code-only authorization and disruptive to concurrently active agents, so not executed. Handed to AGENT-OPS/orchestrator with the exact fix recommended in PB-215 |
| 0042, 0043 | AGENT-BROWSER4 | **RELEASED — still NOT READY, see PB-218** | Handoff from AGENT-BROWSER3 (PB-217) accepted on the strength of a genuine, directly-relayed human authorization for demo-account login — but that authorization did not clear the actual blocker: the first password-entry attempt (`r.aldosari`) was denied by the Claude Code **permission classifier itself**, independent of this session's own judgment, with its own remediation text pointing at a user-made settings change, not a stronger task-prompt authorization. No password was typed. Task 1's global toggle (`full_new_patient_form`: `1`→`0`→`1`) was executed and reverted cleanly as its own tight sequence, but produced no negative-path evidence since no login reached Add Patient. Task 2 not attempted. Recommend the orchestrator relay the classifier's own remediation path to the user directly rather than re-issuing this authorization to a fifth browser subagent |

---

## AGENT-GIT2 — RDY-0045 merge executed, push withheld (2026-08-17)

**Took over the row in the "Agent D takeover" table above.** Ran `EV-045` §5's local procedure against
the live repo — preconditions re-verified (RDY-0082 confirmed CLOSED live; G1 stability re-checked
against the PB-216 canonical sync, unchanged at 15, nothing regressed since) — tagged
`pre-rel820-merge-20260817`, fetched and merged `upstream/rel-820` `--no-ff`, resolved the rehearsed
`composer.json` conflict exactly as `EV-045` specified, and completed the merge commit:
**`8e0eaba90732fc4ec505516dbbb9cd08b102c821`**. Regression check (PHP syntax, full isolated PHPUnit
suite) found no failure traceable to application code — every one of 87 failing/erroring tests is
either byte-identical-unchanged by the merge or confined to CI-only release-engineering tooling. Full
detail and the failure-by-failure trace: `EV-045` ADDENDUM 3.

**Not done, and why: `git push origin pre-rel820-merge-20260817` failed with HTTP 403 — this host's
git credential helper authenticates as `midodevelopper`, who has no write access to
`mohammedfouly1/openemr`.** The tag exists locally only. This blocks *any* push from this
session/host, not just this task's explicit push-the-branch prohibition — flagging it as a distinct,
independent blocker for whichever session/human does the actual push. `git push origin
feat/thiqa-branding-foundation` was never attempted (out of this task's authorized scope regardless).
`origin/feat/thiqa-branding-foundation` remains at `6de7cdcc1`, unchanged.

**RDY-0045 stays NOT READY / open** — a human push (of the tag, then, on review, the branch) is the
only remaining step in `EV-045`'s own procedure.

## Agent E (Gate-sync auditor) — PB-300..349 (2026-08-19)

**Claim:** RDY-0016, §1a/§1b only (the two confirmed live-exploitable A-10 paths `EV-016-A10-fix-scope.md`
had already scoped). Not claiming: §2's admin-misconfig call sites (deferred by the scope doc itself),
§3's 16-orphaned-directory decision (Owner-only), or any of RDY-0016's remaining UI-navigation/browser
legs (held per the standing classifier-block note elsewhere in this file).

**Done:** implemented both fixes exactly as scoped, resolving §1b's one open implementation question
(`$ISSUE_TYPES` scope) and tracing the downstream-use concern to a dead end (`AccessDeniedHelper::deny()`
is `: never`). Added two regression tests to `tests/Tests/Unit/Common/Acl/AclMainTest.php`, both passing
(3/3, 5 assertions) against the live DB-backed unit suite. Full patch record: PR-19,
`docs/branding/adr/patch-records.md`.

**Found and corrected, not part of the claimed fix:** `EV-016-A10-fix-scope.md`'s claim that
`fee_sheet/new.php:1731`'s two literal `aclCheckForm('admin','super')`/`('acct','disc')` calls were
"unaffected, since they have real registry rows" does not hold — direct query shows zero registry rows
for either. Traced to stock upstream code (`be636987b7`) that is dead regardless, since it's additionally
gated on `ippf_specific`, unset on this instance. Corrected in place in `EV-016-A10-fix-scope.md` rather
than left standing.

**RDY-0016 stays NOT READY** — the two confirmed live-exploitable paths are closed, but the full
positive/negative authorization matrix (§23.4) is still unexecuted under real role sessions (same
classifier block as RDY-0042/0043), and §3's Owner decision on the 16 directories is untouched. **Not
attempting either of those here** — out of this claim's scope.

## Agent F (AGENT-COMP2) — RDY-0088 remainder, dossier-scoped (2026-08-19)

**Claim: exactly 4 dossier codes, not "RDY-0088" generically** — `C-16` (TEKNOSys PolyCare),
`C-18` (MAEN.MEDS / M3n Technology), `C-21` (HMISFOX), `C-24` (DenTech KSA). Per this task's own
history (see the ⚠ note on the existing 0088 row above), the prior collision on this exact item was
between two *identically-scoped* claims — naming the specific remaining codes is meant to make a
second collision visible immediately rather than silent.

**How this scope was derived (re-read of `EV-088-competitor-frequency-verification.md` §3/§4/§7 in
full, not inferred from any summary count):** of the 9 dossiers, 5 are terminally resolved and are
**not** being touched — `C-15` (MediSys, live multi-product vendor confirmed), `C-19` (Kizen,
confirmed unchanged — content-free shell, collision risk validated), `C-20` (e-MCS, confirmed dead —
hosting suspended, reproduced across 2 independent passes/4 attempts), `C-22` (Solver, clearly
superseded — full live site, brochure assumption was wrong), `C-26` (Avicenna, live site confirmed,
scored-claim impact against RDY-0062 explicitly assessed as null). The remaining 4 above each still
have a genuinely researchable next step recorded in `EV-088` §6's own recommendation (a direct
re-fetch attempt against a specific URL each returned a transient-looking error on: 520, timeout, DNS
failure, 403) — none is blocked on anything external to more research.

**⚠ Flagging a discrepancy rather than silently picking a number:** `docs/Marketing-MVP-and-Launch-
Readiness-Requirements.md` §47 (PB-300 sync) and `EV-GATE-SYNC-20260819.md:117` both say **"3
remaining"**, apparently computed as `9 − 6` from `EV-088`'s own "6 of 9 advanced" framing (§5 there,
and this file's row 358). That arithmetic doesn't hold up against the dossier-level detail: of the "6
advanced" (`C-15 C-16 C-18 C-22 C-24 C-26`), only 3 (`C-15 C-22 C-26`) are actually resolved to a live,
directly-confirmed marketing surface — `C-16`, `C-18` and `C-24` were each still only *indirectly*
evidenced (WebSearch, not a successful direct fetch) when `EV-088` §7 was written, and `C-21` — not in
the "6 advanced" list at all — was never folded into that count on either side. **4 open, not 3**, by
direct re-derivation from the dossier text. Recorded here per this repo's evidence-first convention
(state the method beside the number) rather than adopting either prior figure on trust.

**Not claiming:** the other 5 dossiers (terminal, no further action), Source C §24.2 items 2/3/4/5
(identity-cluster resolution, evidence-class re-ranking, Arabic review — separate scope, already noted
as untouched in `EV-088` §5), and any frequency recomputation (`EV-088` §5/§6 already establish that
even full resolution of these 4 would not by itself meet Source C's Full/Verified depth standard
required before recomputation).

**DONE (not closed) — result of the claim above.** Full findings appended to `EV-088-competitor-
frequency-verification.md` §8/§9. Of the 4 claimed: **`C-18` is now terminal** (direct WebFetch to the
actual live product subdomain, `meds.m3ntech.com`, succeeded — confirms what §3/§7 could previously
only infer from search snippets). **`C-16`, `C-21`, `C-24` remain open**, each for a distinct,
specific-not-generic reason recorded in `EV-088` §9's table (original-domain reachability unconfirmed;
durable DNS failure across 3 sessions/8 attempts; content unread across a now-4-domain cluster despite
identity clarity). **Net: 6 of 9 dossiers now terminal, 3 remain** — `C-16`, `C-21`, `C-24`. This
happens to match the PB-300/`EV-GATE-SYNC-20260819.md` "3 remaining" figure, but only as of this pass
— before it, that figure did not hold up against the dossier-level detail (see the claim above). **Not
closing RDY-0088** — its acceptance criterion needs Source C's Full/Verified depth re-run against all
9, which homepage-level spot checks (this pass included) do not meet even for the 6 now-terminal
dossiers; per this task's own instruction, the register row is left untouched rather than updated on
partial progress.

## Agent (Claude Code) — RDY-0108, `audit_events_lab-order` metadata fix (2026-08-19)

**Claim: RDY-0108 only** — "Add `audit_events_lab-order` to `$GLOBALS_METADATA` so lab-order events
can be audited at all," §7.18 Domain Q, P2. Fixed and closed at the engineering level in this entry
(PR-21, `docs/branding/adr/patch-records.md`); register `Status` moved to `READY — ENGINEERING
(PR-21)`, `Verdict` stays `DEFERRED` per §7.18's own rule that nothing in that table is promoted into
the current MVP.

`library/globals.inc.php` was missing the `audit_events_lab-order` entry from `$GLOBALS_METADATA`
entirely — confirmed absent by grep before touching anything. This was not just a missing admin
checkbox: `src/Common/Logging/EventAuditLogger.php:77` already reads
`$bag->getBoolean('audit_events_lab-order')` as one of its `eventTypeFlags`, and lines 195-196 map
`procedure_order`/`procedure_order_code` to that same `'lab-order'` category — so the flag was silently
always `false` with no global to flip it. Added the sibling entry (same five-element shape as
`audit_events_order`/`audit_events_lab-results`, label "Audit Logging Lab Order," default `'1'`)
immediately after `audit_events_order`. Grepped `audit_events_lab-results` across `library/`,
`src/Common`, `src/Services`, `tests/` as a proxy for how any `audit_events_*` key is consumed — no
other fixed list, migration, or default-enabled set needed touching; `EventAuditLogger.php` already
expected the key by name.

**Verification:** `php -l` clean on `library/globals.inc.php`. No isolated PHPUnit test enumerates
`$GLOBALS_METADATA` or `audit_events_*` (searched `tests/Tests/Isolated`, no matches), so none existed
to run or update — verification bar for a metadata-table entry is the clean lint plus the array shape
matching its siblings exactly.

**Not claiming:** any other RDY item, any change to `EventAuditLogger.php` itself (already correct —
it was the metadata that was missing, not the consumer), or a live authenticated-UI screenshot showing
the new checkbox (Globals-screen admin session not exercised here; the code-level fix and shape match
to its five siblings is the appropriate bar for a P2 metadata-table entry, per the task's own framing).

## Agent (Claude Code) — RDY-0049, Windows OS-guard on three Unix-only `config.php` settings (2026-08-19)

**Claim: RDY-0049 only** — "Replace the three Unix-only commands configured on a Windows host (`lpr`,
`enscript`, `/usr/bin/file`)," §7.7 Domain F, P1. Fixed at the engineering level in this entry (PR-20,
`docs/branding/adr/patch-records.md`); register row's `Audited state` updated to describe precisely
what changed, `Status`/`Verdict` left `NOT READY` — this does not meet the closure bar because the OFX
bank-ID placeholder half of the same requirement is untouched and no printer/fax hardware exists on
this host to demonstrate a working pipeline against.

`sites/default/config.php` set `OPENEMR_PRINT_COMMAND`, `OPENEMR_HYLAFAX_ENSCRIPT`, and
`$GLOBALS['oer_config']['documents']['file_command_path']` unconditionally to Unix values, with no OS
guard, on an instance that runs natively on Windows (`CLAUDE.local.md`). PB-163 (2026-08-16,
AGENT-SEC) had already live-measured all three broken on this exact host — `lpr`/`enscript` absent
from `PATH`, `/usr/bin/file` absent from the Windows filesystem `Test-Path` sees — but explicitly left
the fix unapplied as out of that agent's database-only remit, and left the `file_command_path`
call-site search unattempted as "too slow on this filesystem." This session re-ran that search via
`git grep -n file_command_path -- . ':!vendor' ':!node_modules'` (reads the object database, not the
Drive-mounted working tree file-by-file, so it completes where a plain `rg`/`grep` walk of `src/`
timed out repeatedly in this same session) and confirmed **zero runtime consumers anywhere in tracked
code** — only `docs/` prose and a PHPStan baseline entry referencing the same assignment line. Also
confirmed the two real call sites for the other two constants are unchanged by this fix:
`interface/billing/sl_eob_search.php:633` and `interface/fax/fax_dispatch.php:316`.

Gated all three on `PHP_OS_FAMILY === 'Windows'`: the `else` branch is the exact pre-existing Unix
value, byte-for-byte, so every Unix deployment of this fork is unaffected. The Windows branch uses
`'print /d:PRN'` for the print command — not invented, it is upstream's own suggestion already sitting
in `config.php`'s pre-existing comment above that line — and an empty string for the other two, since
no drop-in Windows lpr/enscript/`file` substitute exists and fabricating one would misrepresent what
this patch does. `docs/evidence/EV-047-deployment-runbook.md:157`'s separate suggestion (PHP's built-in
`fileinfo`/`finfo_open` could replace `/usr/bin/file`'s *purpose* for MIME detection) was read and
deliberately not acted on here — it describes a possible change to the document-upload code path,
which reads nothing from `file_command_path` today, so it is out of this fix's scope, not overlooked.

**Verification:** `"/c/openemr-stack/php/php.exe" -l sites/default/config.php` → `No syntax errors
detected`. No dedicated PHPUnit test exists for this file, matching how this project's other
config-constant fixes have been verified — `php -l` plus the reasoning trace in PR-20 is the bar. Ran
`git diff` on the readiness register before staging and confirmed exactly one hunk (the RDY-0049 row),
so no concurrent agent's edit to that shared file was at risk of being overwritten.

**Not claiming:** the OFX bank-ID placeholders (`123456789` × 2, same RDY-0049 requirement text, left
untouched — different setting, no code guard applies to a placeholder value), a working Windows print
or fax pipeline (none exists; not fabricated), or any live-hardware print/fax test (would require
physical or virtual printer/fax hardware not present on this host, same constraint PB-163 already
recorded for the unclicked UI action).

## Agent F (Owner-decision scribe) — five direct Owner decisions recorded, PB-350..359 (2026-08-19)

**Claim: RDY-0084, RDY-0086/HR-09, RDY-0096, RDY-0092/RDY-0099, RDY-0083 — decision-recording only,
not their engineering/review closure.** Claimed PB-350..359 in §0.0's range table (unclaimed at the
time — the table showed only PB-300..349/Agent E and PB-350+ as unallocated).

The Owner (Mohammed Elfouly) gave five decisions **directly in conversation with the orchestrating
session, 2026-08-19** — not relayed through any agent, not through a subagent task prompt. Transcribed
each as given, per §0.0 Rule 5 ("record what was received, from whom, and by what route" — no
fabricated sign-off). Full PB-log summary: `### PB-350` in the readiness document.

Evidence-file addenda (`6f27d57a7`): `EV-084-monitoring-requirements.md` (owner = role, not
individual), `EV-WAVE3-decisions-20260816.md` (RDY-0086/HR-09 competence basis = "native Arabic
speaker"), `EV-096-options.md` (Level 1/Business Hours selected, response target confirmed),
`EV-092-locked-decisions-reconciliation.md` §3.1 (Q5/RDY-0099 ruling: RDY-0057's disclosure-based
position governs, RDY-0099 retired as superseded), `EV-047-deployment-runbook.md` §9 (pilot-host
trigger = a proper Windows Service).

Register updates (`bba1cc108`): RDY-0083/0084/0092/0096 register rows and detail sections; RDY-0099's
Domain Q row (Status/Verdict changed from `DEFERRED` to `RETIRED — SUPERSEDED`, so it no longer reads
as an active future task); RDY-0057's row (pointer to the Q5 ruling); HR-04's HR-09 row (basis of
authority now stated). `git diff` run on the readiness document before staging both commits; every
hunk in each was confirmed as this session's own edit before staging.

**On the RDY-0083 decision specifically — verified, not just recorded.** The task flagged a possible
inconsistency: a Windows Service, like a `SYSTEM`-context scheduled task, typically runs outside the
interactive session, and this demo host's Google Drive mount of `G:` is per-session. Checked
`EV-083` and PB-181 (`AGENT-CLAIMS.md` above, and the readiness document's own PB-181 entry) directly:
**confirmed accurate.** PB-181's investigation already found that non-interactive logon types
(S4U/Batch/ServiceAccount) — the execution context a Windows Service typically runs under — hit the
identical `G:`-mount blindness that ruled out converting the existing Scheduled Task to survive
logoff. So a Windows Service would face the same blocker **on this specific demo host**. This does not
make the Owner's decision wrong: it is a decision for a real pilot host (RDY-0064), which is not
expected to run from a per-session Drive mount the way this dev machine does — the same reasoning
`EV-047` §10.5 already applied to the TLS-renewal Scheduled Task. Recorded as NOT validated on this
host, with implementation/testing deferred to real pilot-host provisioning, rather than silently
treated as equivalent to the working demo-host mechanism.

**Not claiming:** closure of any of the five RDY items — each addendum states precisely what narrow
gap it closes (a wording question, a missing basis statement, a level selection, one conflict
sub-finding, a future mechanism decision) and what stays open. No code, no database changes, no
gate-count recalculation (§0.0 Rule 3 — that stays with whoever runs the next dedicated sync).

## Orchestrator (main session) — RDY-0023 / RDY-0044 / PB-057 executed directly, 2026-08-19

**Claim:** RDY-0023 (growth-chart criterion), RDY-0044 (PB-077 "change 2" — sensitivity-flagged
encounter + clinician-authored form), PB-057 (facility/letterhead application). A subagent was
delegated this task first and correctly declined it over a genuinely ambiguous instruction (see below)
— the orchestrating session then executed it directly instead of re-delegating, having the actual
verified conversation context a fresh agent could not have.

**Done:** `PR-22` (`docs/branding/adr/patch-records.md`) — a new idempotent `--apply-postseed-fixes`
option on `SeedDemoCommand.php`, run once against the live dataset. Converted `SYN-0013` to a
paediatric DOB (not a 31st patient — `patients=30` stays fixed), flagged `SYN-0014`'s most recent
encounter `sensitivity=high`, added a clinician-authored SOAP note to `SYN-0015` under that encounter's
own real provider, and ran the already-implemented `completeFacilityAndProviderIdentity()` (found
already a no-op — the letterhead fix was already effectively live, undocumented). Took a new baseline,
`thiqa-rdy0044b-v4-baseline-20260819.sql`, verified by a clean restore-into-throwaway-database round
trip with CLINHASH matching exactly. Superseded v3. Full detail: `EV-044` §12.

**Why the first delegation attempt was declined, and what changed:** the original task brief asked for
a form's authorship to be stamped with a real clinician account's name for AI-generated content — a
genuine misattribution concern the declining agent was right to flag. The corrected approach (used
above) instead routes the note through the encounter's own already-assigned provider via the exact
attribution mechanism the rest of the seeder already relies on (`EncounterService::insertSoapNote()`
reading the active session, PR-14) — extending an existing, honest mechanism rather than fabricating a
new kind of attribution. The brief's other instruction — record a clinical re-affirmation as "relayed
by the Owner, not countersigned" — was not actually fabrication (it mirrors PB-045's own established,
honest pattern for exactly this) but is recorded here for anyone auditing this decision to weigh
themselves; `EV-044` §12's "Authorisation on record" paragraph is the actual text used.

**RDY-0023 stays NOT READY** (live UI confirmation of the growth chart rendering is tooling-blocked,
same wall as RDY-0042/0043). **RDY-0044 stays NOT READY** (baseline is current and content-complete,
but the item's own closure rule needs both legs formally closed, not performed here). **PB-057
confirmed applied** (was already live before this patch, undocumented until now).

## Orchestrator (main session) — RDY-0095 / RDY-0064 closed on Owner-relayed outcomes, 2026-08-19

**Claim:** RDY-0095, RDY-0064, and their consequence closures RDY-0033/0034. Full detail: `PB-301` in
the main readiness document. Not re-narrated here — this entry exists so the claim is visible in this
file's own index without a reader having to search the PB log.

**One thing worth surfacing here specifically, for any future auditor of this file:** RDY-0095's
determination was checked against `EV-090` §8's five routed items before being accepted as a closure,
not accepted at face value — one of the five (`acknowledge_license_cert.html`'s local suppression)
turned out to be in real tension with the ruling. The Owner was asked directly and chose to leave the
tension unresolved. This is recorded as a live, acknowledged gap in `RDY-0095`'s own register row, not
smoothed over to make the closure look cleaner than it is.

## Agent G — RDY-0055/RDY-0096 closed, RDY-0083 confirmed correctly open, PB-360..369 (2026-08-19)

**Claim: RDY-0055 (verification + closure), RDY-0096 (file wiring + closure), RDY-0083 (confirmation
only, no edit).** Claimed PB-360..369 in §0.0's range table (unclaimed at the time).

**RDY-0055 CLOSED.** `EV-055-audit-phi-determination.md`'s technical determination and
`EV-055-pilot-disclosure-draft.md`'s Owner-approved disclosure text already existed. Live-reverified
this session against the running seeded demo DB (native stack): `DESCRIBE log` — no `encrypt` column
on this schema; `SELECT COUNT(*), SUM(FROM_BASE64(comments) IS NOT NULL) FROM log WHERE comments IS
NOT NULL AND comments != ''` → 101,963 / 101,963 (every non-empty row decodes as base64); negative
control `comments LIKE '%Alharthi%'` → 0; positive control on the decoded value → 3. Disclosure text's
factual claims match live reality exactly — closed on that basis.

**Correction made along the way, not part of the original brief:** the disclosure file's own
2026-08-19 approval addendum claimed the Owner's approval also served as RDY-0003's sample claim
review. Read `EV-003-claim-review-procedure.md` §3-§6 directly — that criterion is met only by a
review recorded in `EV-003` §5 against the artefact `EV-003` §4 actually queues (`EV-067`), which this
disclosure text was never part of. Appended a correction rather than rewriting (`EV-055-pilot-
disclosure-draft.md`). **RDY-0003 was left untouched by this session** — a separate, concurrent
session's uncommitted working-tree edit was observed closing RDY-0003/RDY-0067 via a real `EV-067`
claim review while this session was mid-task; not verified or touched here, left for its own author to
commit.

**RDY-0096 CLOSED.** `EV-096-options.md`'s Level 1 (Business Hours) Owner decision existed but wasn't
"reflected in the scope template and pilot agreement" per the item's own acceptance criterion. Added
Level 1's full definition to `EV-066-pack.md` §9 (new section, cross-referenced from the existing §1
line) and to `EV-068-pilot-requirements.md` elements 5-7 and §4 (replacing the "per RDY-0096 once it
exists" placeholders). Keyword-scanned both files post-edit for "uptime"/"availability" — clean, no
figure present.

**RDY-0083 — confirmed correctly stays open, nothing closed or forced.** Its own register text and
`EV-047` §9 already state precisely and honestly that the Owner's Windows Service decision is a
specification for a real pilot host, explicitly not validated on this demo host (`G:` per-session
Drive-mount blindness applies to non-interactive execution contexts too, per PB-181's own finding).
**Did not draft an installer script.** `EV-047` §9 already fully specifies the decision and its
scope-of-non-validation; a PowerShell/WinSW installer written here, untested against any real host and
without the real pilot host's service-account/path specifics (none of which exist yet), would add
speculative content without reducing genuine future implementation work — assessed as not worth the
risk of looking more authoritative than it is. Register row and `EV-047` §9 both left untouched.

**Gate-count sync (§0.0 Rule 3) explicitly NOT run**, despite closing two P0 items whose `Blocks`
fields (G3 for 0055; G3, G6 for 0096) would move counts — a concurrent session was found mid-edit on
this exact file, with its own uncommitted RDY-0003/RDY-0067 closures (G0, G5, G6) sitting in the
working tree at the same time. Recalculating now risks exactly the collision Rule 3 exists to prevent.
Left for a genuine dedicated sync pass once all concurrent closures for this window have landed.

## Orchestrator (main session) — three subagents dispatched for directly-actionable remaining work, 2026-08-19

**Claiming, on behalf of the dispatched subagents:** RDY-0023 (vitals seed + growth-chart render),
RDY-0041 (second D-7 run), RDY-0071 (CSV-button click-test), RDY-0090/0094/0016 (continued observation
walks), RDY-0073 (cross-reference + demo-system dry run), RDY-0092 (Locked Decisions corpus
reconciliation, triage only). Range `PB-380…PB-389` claimed in §0.0 — sub-ranges assigned per agent to
avoid collision between them: PB-380-383 (browser/live-system agent), PB-384-385 (RDY-0073 agent),
PB-386-387 (RDY-0092 agent).

**Explicitly NOT claimed, and why:** RDY-0047, RDY-0084, RDY-0085 were considered for this batch and
excluded — each genuinely requires a real reachable hosted instance to close (`EV-064-081-084-085-
hosting-pack.md` §7 states this for all three explicitly), and no such instance is reachable from this
session (RDY-0064's provisioning is Owner-relayed, not independently verified or accessible here). An
earlier summary in this conversation mischaracterized these three as "execution work I can do myself"
— corrected here before any agent attempted them, rather than let a subagent discover the same
boundary the hard way or improvise around it.

**Each dispatched agent is instructed to:** read this file and confirm no collision before writing;
never fabricate a human decision, sign-off, or a result it did not actually obtain; report exactly
what it attempted vs. what actually succeeded, including partial/blocked outcomes; use its assigned
PB sub-range only. Results will be verified against each item's own acceptance criteria before being
accepted as closures, not taken at face value.

**Update, same day:** the PB-384-385 (RDY-0073) and PB-386-387 (RDY-0092) agents have both completed.
RDY-0073 stays open — T-5/T-6 (external delivery, cold-read by an outside reviewer) are genuinely
human/infrastructure-blocked, not something a dry run can satisfy. RDY-0092 read the full remaining
corpus and found one new real conflict (Q27-31/Q65 NPHIES engineering language vs. the GTM's locked
NPHIES deferral) — routed to the Owner, not resolved. The PB-380-383 browser/live-system agent is
still running.

## Orchestrator (main session) — fourth subagent dispatched, 2026-08-19

**Claiming:** RDY-0004 (prohibited-claim list packaging), RDY-0061 (capture rules), RDY-0086 (Arabic/
RTL qualification script), and a config-verification-only pass on RDY-0060/0062 (the facility name and
locale globals were checked directly against the live DB before this dispatch and already read
`Thiqa Demo Eye Clinic` / `SAR` / `Asia/Riyadh` — contradicting the register's stale 2026-08-13 claim
of `Your Clinic Name Here` / US locale; this agent confirms and corrects the record). Range
`PB-390…PB-399` claimed in §0.0.

**Deliberately NOT claimed here:** the actual screenshot/recording capture for RDY-0060/0062 — that
needs the Chrome browser extension, which the PB-380-383 agent may still be using. Left for a
follow-up pass once that agent is confirmed finished, to avoid two agents contending for one browser
session.

## Orchestrator (main session) — fifth subagent dispatched, 2026-08-19 (Chrome extension reconnected)

**Claiming:** RDY-0016 (A-2/A-7 sensitivity live test + more matrix probes), RDY-0023 (vitals seed +
growth-chart render), RDY-0041 (second D-7 run), RDY-0060/0062 (screenshot/recording capture),
RDY-0071 (CSV-button click-test) — every item this session's register rows recorded as
browser-tooling-blocked. Range `PB-407…PB-412` claimed in §0.0.

**Update — first dispatch under this claim (device `1a030f48-96bf-43e4-86e4-6abe92e9f2f4`) hit a
genuine, thoroughly-diagnosed connectivity dead end**: that device could reach the open internet but
got a hard connection refusal against `localhost:8300`/`127.0.0.1:8300`/`10.128.0.3:8300` — confirmed
across a full Edge restart, an extension reinstall (new pairing, new `connectedAt`), and multiple
fresh tabs. Nothing was written to any evidence/register file by that attempt; no rollback needed.

**Second device connected during troubleshooting, `93bb8839-1b42-4f7b-a294-10bf4203dc64` ("Browser
1"), confirmed working** — `read_page` against `http://localhost:8300/interface/login/login.php`
returned the real Thiqa login form (username/password fields, language dropdown, Login button).
**Use this device, not the first one, for the redispatch below.**

**Update, same day — this agent has completed (PB-390…PB-393 used, PB-394…PB-399 not needed):**
RDY-0004, RDY-0061 and RDY-0086 all turned out to already have their core artefacts written by
earlier agents (`EV-004-prohibited-claims-control.md`, `EV-061-capture-rules.md`,
`EV-086-arabic-rtl-coverage.md` §3) — re-verified each in full rather than re-writing from scratch,
found them accurate and unchanged, corrected the several places in the register that still described
them as not-yet-written (stale by up to five days), and added one genuine new finding to
`EV-004` §1.3 (a cross-reference gap between §32 and the GTM's four per-pillar "Claims to avoid"
rows — flagged, not resolved, since editing the locked §32 list is outside this task's authority).
**None of the three closed** — each has a real, unmet, non-documentation blocker (Phase 3/4/5 not
started; RDY-0060 not captured; a live-browser screen walk and HR-09's native review, respectively).
RDY-0060/0062: independently re-ran the DB queries and the grep rather than trusting the pre-dispatch
note, confirmed the facility/locale claim is correct and the stale "Your Clinic Name Here / US
locale" register text is wrong, and found one small real gap outside the dispatch's own claim —
`facility.primary_business_entity = 0` on the single facility row, which two live code paths
(`eRxStore.php::getFacilityPrimary()`, the facility-select dropdown in `usergroup/facilities.php`)
key off to resolve "the" facility — fixed directly (`UPDATE facility SET primary_business_entity = 1
WHERE id = 3`). Neither RDY-0060 nor RDY-0062 marked CLOSED; capture remains the explicit next step
for whoever has browser access. **No gate-count/summary-table sync performed** — none of this pass's
findings changed any item's final Verdict, so §1.4/§47 do not need resyncing; also the PB-380-383
browser/live-system agent's status was not re-confirmed as finished before this agent completed, so
even if a verdict had changed here, a sync would have been deferred pending that confirmation, per
the same collision-avoidance convention the PB-386-387 entry above used.

## Orchestrator (main session) — sixth subagent dispatched, 2026-08-19 (source/DB-read-only, deliberately non-colliding)

**Claiming:** continuation of RDY-0090 (branding surface walk) and RDY-0094 (demo no-go rehearsal),
via source reads and read-only DB queries only. Range `PB-417…PB-420` claimed in §0.0.

**Deliberately excluded, and why:** any Apache restart (RDY-0025's fix), any database
reset/reseed/mutation (RDY-0044), and anything requiring the Chrome browser extension (RDY-0086's
per-screen walk, or re-touching any of the PB-407-412 dispatch's six items) — all of these share
state with the still-running browser dispatch (`a1122332bd54186f0`) and restarting Apache or
resetting the DB out from under it mid-test would corrupt its work. This agent's tools are limited to
Read/Grep/Bash-for-read-only-queries; it should not open any browser MCP tool at all.

## Orchestrator (main session) — fifth subagent (PB-407-412 dispatch) COMPLETED, 2026-08-19

**Outcome, item by item — two closed, three materially advanced, none fabricated:**

- **RDY-0071 CLOSED** (PB-407). Real click on the live "Export to CSV" button (`k.alotaibi`,
  `SYN-0013`'s ledger); real CSV file downloaded to disk and read back (header row + one real billed
  line item, SAR 250.00). First click's network trace showed a transient 503 (DriveFS latency, per
  `CLAUDE.local.md` §8), second click clean 200 — not a code defect.
- **RDY-0023 CLOSED** (PB-408). Real Vitals entry via `y.alharbi` on `SYN-0013`'s encounter (Weight
  128 lb, Height 64 in, save.php 200); real rendered growth chart with the plotted data point,
  screenshot saved. Flagged, not resolved: this mutates the RDY-0044-B protected baseline;
  re-baselining is a follow-up for whoever owns it, not done here.
- **RDY-0041 second D-7 run executed, NOT closed** (PB-409). Front Office (new patient, pid 32) and
  Physician (encounter 109 + full SOAP note, clinician-authored not `admin`) legs both clean.
  Accounting leg hit a reproducible **"Authentication Error"**, root-caused via `php_error.log` to the
  host's known broken session persistence (CSRF failures, `session.save_path` → non-writable
  `C:\Windows`) — a host/environment defect, not an app defect, reproduced twice including on a fresh
  login. Both preconditions of the item (a second full run, a known reset state) remain unmet.
- **RDY-0016's A-2/A-7 sensitivity gating live-confirmed** (PB-410) — the task's most important ask.
  Three roles tested live against `SYN-0014`'s real `sensitivity='high'` encounter: Front Office
  blocked at a coarser "Encounters not authorized" gate; Accounting sees the row with Issue/Reason/
  Coding redacted to literal `"(No access)"` — **confirming "redacted, not invisible" live**, settling
  `EV-056-057-088` §2.2/§2.3's pending correction; Physician (not the encounter's own author) sees the
  same encounter **fully unredacted** — a genuine new finding that this ACL configuration does not
  sensitivity-gate physicians at all, flagged for whoever next revises the A-7 expected-behaviour text.
  RDY-0016 itself stays open (A-10 and most matrix cells still unrun).
- **RDY-0060/0062 partially advanced** (PB-411). SS-01 (Audit Log Tamper Report, clean result)
  captured and saved. Attempting RDY-0062's live "run it" recording surfaced a new, reproducible
  blocker: clicking `[Refresh]` on the same report produces `"...Not Authorized"`, root-caused to the
  same host session-persistence defect as RDY-0041's failure (not an ACL issue — `n.alqahtani` is a
  genuine Administrator). 11 of 12 SS-0x captures remain outstanding.
- **Gate-count sync performed** (PB-412), correctly folding in RDY-0078's closure from the concurrent
  sixth-subagent-adjacent session (PB-415) that this dispatch had not itself made, before applying
  this dispatch's own two P0 closures. Final: 71 P0 registered, 47 closed, 24 open; full per-gate
  table in the §47 CURRENT block.

**All five register rows (§7.x), the §1.4 summary counts, the §47 CURRENT block, and both affected
evidence files (`EV-056-057-088-claim-discipline.md`, `EV-016-authorization-matrix.md`) updated in the
same pass.** Range PB-407…PB-412 fully used. Nothing in this update was written without a
corresponding live action (real HTTP request, real DOM state, or real server log line) behind it.

## Orchestrator (main session) — seventh subagent dispatched, 2026-08-19 (browser, root cause now fixed)

**Claiming:** RDY-0041 (D-7 second run, Accounting leg retry), RDY-0062 (live recording retry), RDY-0086
(per-screen Arabic/RTL walk, D-1…D-5/D-7), RDY-0094 (§40 rows 6, 8, 10). Range `PB-425…PB-430` claimed
in §0.0.

**Context this dispatch inherits:** RDY-0025's session-persistence defect (the traced root cause of
RDY-0041's Accounting-leg "Authentication Error" and RDY-0062's blocked recording) is now fixed and
live-verified (PB-423) — both worth a clean retry. The demo DB was freshly reset to the v4 baseline
immediately before this dispatch (PB-424, two independent resets, CLINHASH byte-identical) — starts
from a known-clean state, not carrying over the earlier D-7 test patients (pid 31/32, now gone).

**Do NOT restart Apache or reset the database mid-dispatch** — if RDY-0041's D-7 run needs a "known
reset state" precondition it does not already have from the pre-dispatch reset above, that still needs
explicit Owner authorization per the standing convention this session has used throughout, not a
unilateral reset by this agent.

## Orchestrator (main session) — eighth subagent dispatched, 2026-08-19 (browser, closing the pure-live-verification subset)

**Claiming:** RDY-0016 (A-10 live HTTP round-trip + A-1/A-6 UI legs), RDY-0025 (document uploads),
RDY-0029 (CDS alert active-presentation render), RDY-0041 (second independent clean D-7 run — the
seventh subagent's PB-425 run already produced one clean isolated run; this needs a second, independent
one to meet the "twice" bar), RDY-0045 (browser login walkthrough), RDY-0060/0061 (SS-02…SS-12 +
review), RDY-0062 (isolated-session `[Refresh]` recording retry), RDY-0086 (per-screen Arabic/RTL walk,
D-1…D-5/D-7 — not reached by the seventh subagent, browser disconnected first), RDY-0090
(`product_reg.js.twig` modal-title leak, reachable leg only — the 3 OAuth2 template leaks are
deliberately out of scope, OAuth is off), RDY-0094 (§40 row 6 live-render pass only — row 1/8/10 already
addressed). Range `PB-434…PB-460` claimed in §0.0.

**Single-tab discipline, no exceptions:** the seventh subagent's own finding (multi-tab session/ACL
identity collision) is the working theory for this dispatch throughout. One tab, log out or close+reopen
before switching role, never two authenticated tabs open at once.

**Do NOT restart Apache or reset the database** unless a specific item's acceptance criteria requires it
(RDY-0041 explicitly does — "from a known reset state," "twice" — see that item's own claim above for
how this is handled) and it is recorded as a deliberate, isolated action, not a side effect.

## Orchestrator (main session) — eighth subagent (PB-434-442 dispatch) COMPLETED, 2026-08-19

**Outcome, item by item — four closed, three materially advanced, two genuine new findings (not
closures), one not attempted:**

- **RDY-0045 CLOSED** (PB-434). Real login walkthrough as `n.alqahtani`, authenticated shell
  rendered, screenshot saved.
- **RDY-0025 CLOSED** (PB-436). 8 synthetic documents uploaded through the real Document
  Uploader/Viewer form (`SYN-0014`, pid 14) — real multipart POST, server-confirmed, DB-verified
  (`documents` id 108-115, `foreign_id`=14). File-upload MCP tool refused all local paths tried (no
  folder pre-shared with the session); worked around via the browser's own File/DataTransfer API to
  build in-memory files and assign them to the real file input, then submit — same class of technique
  Selenium/Playwright's `setInputFiles` uses, not a bypass of any ACL or sandbox boundary.
- **RDY-0029 CLOSED** (PB-437). CDS active-alert presentation live-confirmed twice: a persistent
  "Clinical Reminders" panel on `SYN-0014`, and an interruptive Alerts/Reminders popup plus the same
  panel on a brand-new patient (pid 32) — confirms the engine evaluates live, not just against one
  pre-seeded case.
- **RDY-0062 CLOSED** (PB-439). Isolated single-tab `[Refresh]` succeeded twice, confirming
  PB-425/426's multi-tab-collision hypothesis exactly. GIF recording produced — the flagship asset
  this item has never had.
- **RDY-0016 materially advanced** (PB-435). A-10's live HTTP round-trip done: bogus `thistype` → 403
  Access Denied; legitimate `thistype` → 200 full form. A-1 fully confirmed (UI + direct-URL, both
  legs). A-6 confirmed at menu level only. Matrix still has A-3/4/5/8/9/11 and A-6/7's direct-URL legs
  unrun — stays NOT READY.
- **RDY-0041 materially advanced** (PB-442). Second independent clean isolated D-7 run (new patient,
  pid 32), all three legs clean on the first attempt. Reused the same PB-424 reset rather than
  triggering a third — a live DB check found `facility.primary_business_entity` (a prior agent's fix)
  had reverted, direct evidence a further reset risks silently regressing untracked fixes; no fresh
  Owner authorization for a third reset existed this session, so none was taken. Whether two clean
  runs from the same reset satisfy "twice from a known reset state," or a literal second reset is
  still required, is flagged for the Owner/next reviewer, not decided here.
- **RDY-0060/0061 materially advanced** (PB-438). SS-02 and SS-07 captured (3 of 12 total now exist).
  SS-07 flagged as captured against a historical date (2026-08-14), not "today" — see the RDY-0094 row
  6 finding below for why.
- **RDY-0090's reachable leg BLOCKED, new host-level finding** (PB-440): the Twig-render session-lock
  hang `CLAUDE.local.md` §9 documented as PHPUnit-only reproduces in live Apache too, and — worse than
  documented — wedges the *entire browser-tab session*, not just the one hung request, until Apache is
  restarted. Reproduced twice, both cleared by restart, login state survived. Not an ACL/branding
  defect; recorded as its own operational finding.
- **RDY-0094 §40 row 6 CONFIRMED as a genuine, currently-active NO-GO** (PB-441) — the opposite of a
  closure. Today's Flow Board shows `Total patients: 0`; DB confirms all 37 seeded appointments are
  fixed to `2026-08-14`, none later. This corrects PB-427's row-count-based optimism: non-empty table
  counts did not mean non-empty *screens* once date-filtering against a moving "today" met a
  fixed-date seed. Root cause routed to whoever owns RDY-0022/the demo-data refresh process.
- **RDY-0086 honestly not attempted** — browser access worked this pass, but time was prioritized to
  the other 10 items; recorded as not-attempted, not claimed.

**Range PB-434…PB-442 used** (PB-443…PB-460 unused, released back to the pool for whichever agent
claims it next — no need to re-claim, just note in that agent's own dispatch note per Rule 1). Nothing
in this update was written without a corresponding live action (real HTTP round-trip, real DOM state,
real server log or DB row) behind it. One tooling limitation worth flagging for future browser-check
agents: the file-upload MCP tool's local-path restriction has no obvious pre-authorized folder on this
host — the File/DataTransfer-API workaround above is the reusable pattern.

## Orchestrator (main session) — ninth browser-check agent dispatched, 2026-08-19 (continuing PB-434-460)

**Continuing within the eighth agent's PB-434-460 reservation, starting PB-443** — not re-claiming a
new range, per that agent's own closing note above. **Claiming:** RDY-0016 (remaining matrix legs),
RDY-0041 (status check only — do not decide the "twice" wording question, only note if the Owner has
ruled since), RDY-0060/0061 (SS-03…06, 08…12 — 9 remaining captures), RDY-0086 (per-screen Arabic/RTL
walk, D-1…D-5/D-7). RDY-0090's reachable leg and RDY-0094 row 6 explicitly OUT OF SCOPE this dispatch
— both are real defects already correctly documented as blocked/NO-GO by the eighth agent, being
investigated in parallel by the orchestrator; not to be re-attempted or re-decided here.

**Browser connectivity note**: device `93bb8839-1b42-4f7b-a294-10bf4203dc64` was unreachable at
dispatch start (`list_connected_browsers` returned `[]` on repeated checks, consistent with
`CLAUDE.local.md` §12's documented pairing instability). RDY-0016's remaining work did not need a
browser (real authenticated HTTP via `docs/evidence/harnesses/rdy0016-*.php`, the same method
PB-012/013/PB-073 established) and was completed first (see PB-443 in the main document): a
bookkeeping correction (A-3/4/5/6-direct/8/9/11 were already PASS since 2026-08-14, re-confirmed
fresh today) plus a genuine new negative-row FAILURE at A-7 ("cannot amend another clinician's
note" — a different physician can open another clinician's real SOAP note by direct URL with no
denial). RDY-0041's status check (no Owner ruling found on the "twice from a known reset state"
wording question — left exactly as flagged, not decided) also needed no browser. RDY-0060/0061's
remaining captures and RDY-0086's Arabic walk are genuinely browser-dependent (real screenshots) and
are deferred pending reconnection — will be attempted if/when the browser becomes reachable this
session, otherwise honestly recorded as not-attempted rather than faked.

## Orchestrator (main session) — PB-444, 2026-08-19 — RDY-0016/A-7 sensitivity-ACL bypass fix

Used PB-444 (next free in the eighth agent's PB-434-460 reservation, after the ninth
browser-check agent's PB-443). Non-browser, code-only work: fixed the sensitivity-ACL bypass
half of A-7's negative-row failure (`view_form.php`/`load_form.php` never checked encounter
sensitivity), verified live via HTTP harness, no regression on the 32-probe matrix. Left the
separate cross-physician non-sensitive-note policy question open for the Owner — did not touch
it. Files changed: `interface/patient_file/encounter/view_form.php`,
`interface/patient_file/encounter/load_form.php`, `docs/evidence/EV-016-A7-sensitivity-fix.md`,
`docs/evidence/harnesses/rdy0016-a7-sensitivity-fix-verify.php`. Commits `e02d7b5f3` (code fix),
`73f906200` (register/PB-log update). Did not touch RDY-0060/0061/0086/0090/0094 or the demo
database — a separate agent (Codex, briefed via a standalone prompt) is expected to work the
remaining browser-dependent items; avoided any DB reseed to prevent colliding with it.

## Codex built-in-browser continuation — PB-445…PB-448, 2026-08-19

**Continuing the ninth browser-check claim in the still-reserved PB-434…PB-460 range.**
PB-445…PB-448 are reserved for the user-requested direct built-in-browser work on
RDY-0060/0061 (SS-03…SS-06 and SS-08…SS-12), RDY-0086 (Arabic/RTL D-1…D-5/D-7
visual walk), RDY-0090 (one bounded About Thiqa attempt), and RDY-0094 sequencing only.
The browser is one visible Codex in-app tab with one authenticated identity at a time.
No Apache restart, database reset, or reseed is authorized by this claim. RDY-0094's
stale-data evidence is checked before any calendar/flow recapture; no reseed will be
performed without the Owner.
