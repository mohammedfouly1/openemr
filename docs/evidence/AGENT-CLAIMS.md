# AGENT WORK CLAIMS — Phase 2B

> ## 🔴🔴 ACTION FOR AGENT A — the RDY-0044-B v2 baseline ships the UUID defect (PB-081)
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
| 0025, 0037, 0082 (leg 6 only) | AGENT-BROWSER | **HELD — 0037 gap re-located, 0025 blocked, 0082 not started** | PB-202: 0037's real gap is now the Ledger showing no currency symbol at all (not "$ instead of SAR"). 0025 blocked by a new reproducible defect — Documents tab hangs the browser tab completely, twice reproduced, needs its own investigation before retry. 0082 leg 6 still waits on AGENT-OPS producing a restored instance |
| 0013, 0016, 0018, 0029, 0033, 0034, 0035 | AGENT-CONF | **HELD** | 0016 is Agent B's `HELD` row (Track B) — **CONTINUATION**, partial evidence at PB-037/PB-034 already exists, read before re-running. 0013 also claimed by AGENT-BROWSER above for its browser leg — AGENT-CONF holds any remaining config-only piece, AGENT-BROWSER does the screen verification; same item, two legs, coordinate |
| 0055, 0049, 0042, 0043 | AGENT-SEC | **HELD** | 0042 is Agent B's `HELD` row (Track B) — **CONTINUATION**, Agent B has done no work on it yet, clean handoff. 0043 already closed in code (Agent A, PB-052, commit `5ab88c700`) — AGENT-SEC's job is the D-7-rehearsal acceptance check only, **not** touching `MainMenuRole.php` again |
| 0023, 0041, 0044-B (verify-only) | AGENT-DATA | **HELD** | 0023/0025 were Agent A's `HELD — BLOCKED` row — 0025 reassigned to AGENT-BROWSER above (needs a browser, not a seeder), 0023 stays here (needs a clinician + Owner decision on the growth-chart criterion) |
| 0082 (backup/restore infrastructure legs), 0083, 0085, 0093, OD-04, OD-05 | AGENT-OPS | **HELD** | 0083 is Agent B's `HELD` row — **CONTINUATION**, live evidence contradicts "never executed," correction in progress; AGENT-OPS adds the console-session-dependency finding (see PB-14x) |
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
