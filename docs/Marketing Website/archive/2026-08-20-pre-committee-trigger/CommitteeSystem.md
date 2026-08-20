# THIQA — COMMITTEE SYSTEM

**One roster. Four committees. One set of rules.**
Charter, agent briefs, briefing pack, dispatch controls and task specifications.
Created 2026-08-20. Companion to `../MarketingWebsite.md`, `../03-website-plan/Challenges-and-Demo.md`, `../06-technical/Architecture.md` and `../02-strategy/Tools-and-Skills.md`.

**What this file replaces.** `../archive/2026-08-20-pre-restructure/MarketingCommittee.md` §1–§12 — its charter *and* the two independent
audits appended to it. Every finding from both audits (AUD-COM-01…15, F-1…F-17) is either **applied
as design below** or **listed as an open Owner ruling in §15**. No finding is dropped, and no audit
narrative is carried over: the old file is retained, unedited, as the Rule D evidence trail and is
no longer an operating instrument. Where the two audits disagreed — the DEM-001 reopening question —
the disposition is recorded once, in §13 step 0, and the divergence is stated there rather than
duplicated across two sections.

**Model authorisation, Owner, in conversation, 2026-08-20:** Chair on **Fable 5**; all other agents
on **Opus 5**, falling back to **Sonnet 5** where Opus 5 is unavailable.

---

# PART A — THE SYSTEM

## 1. Purpose and the one standing rule

Thiqa's positioning is defensible for one reason: **every claim traces to an audited capability with
its limitation attached.** A committee is useful only if it strengthens that property, and dangerous
the moment it produces fluent prose that out-volumes the evidence base.

> **STANDING RULE.** Committee output is **subordinate to the audit, never parallel to it.** Where a
> committee recommendation and `docs/HISModulesUsers.md` disagree, **the audit wins and the
> recommendation is wrong.** No exceptions, no majority override, no seniority override.

Two inherited corollaries:

- **Technique, not content.** Every external framework and skill pack was written for products that
  may freely claim *"leading, comprehensive, HIPAA-compliant, trusted by 500+ clinics."* Thiqa may
  claim none of it. Import structure, sequence and method. Never import copy.
- **Nothing on assertion.** Every finding carries a re-runnable command, a `file:line`, or a URL with
  the date accessed (§7.1).

---

## 2. The roster

Eight agents. Every committee in §3 is a **convening of this one roster** — there is no second
roster, no per-committee charter, and no role defined twice.

| # | Agent | Model | Type | Owns |
|---|---|---|---|---|
| **M0** | **Chair** | **Fable 5** | Chair | Classification · gates · the record · evaluation |
| **M1** | Market & Competitor Intelligence | Opus 5 | Generate | Acquisition mechanics, SERP, competitor surfaces |
| **M2** | Strategy & Ideas | Opus 5 | Generate | Options, ranked cheapest-first; the reopening licence |
| **M3** | Messaging & Content | Opus 5 | Generate | Message hierarchy, page copy |
| **M4** | Website Architecture & Conversion | Opus 5 | Generate | IA extension, funnel, forms — **and adversary to M6** |
| **M5** | Claim Compliance & Evidence | Opus 5 | **Adversary — veto** | Every artefact, against §32 / GTM §14 / the audit |
| **M6** | Technical & Security Auditor | Opus 5 | **Adversary** | Stack, exposure, abuse, privacy, operations |
| **M7** | Arabic & Localisation | Opus 5 | Generate | The Arabic surface, in both directions |

**Balance: 5 generate : 2 adversary**, plus a chair that generates nothing. Unusual for a marketing
team and correct here — the scarce resource is not ideas, it is **permissible** ideas. §32 removes
most of the standard playbook, so the binding constraint is the filter, not the generator.

**The Orchestrator** — the main session. Not a member, holds no opinion, casts no vote. It
dispatches, runs the §6.3 preflight and the §6.4 write-isolation diff, and is the **only** party
that edits shared or governed documents or makes commits.

**Two adversary rules that close the "who audits the auditor" gap:**

- **M4 attacks M6.** M6's per-layer verdicts are the only generated-style output in the system with
  no natural reviewer. M4 is briefed adversarially against them — *"find why this audit is wrong"* —
  and runs at a model no lower than M6's.
- **M5's own evaluation terminates at the Owner**, once, via the meta-review in §7.3. It does not
  recurse.

---

## 3. The committees

Each committee is fully specified by six fields. Nothing else about a committee is written anywhere.

### 3.1 C-1 · Marketing Committee — standing

| Field | Value |
|---|---|
| **Convenes on** | Positioning surface, message hierarchy, page copy, IA extension, funnel, competitor work |
| **Chair** | M0 |
| **Generators** | M1 · M2 · M3 · M4 · M7 — the subset the classification requires, never all five by habit |
| **Adversary** | **M5, mandatory, without exception** |
| **Decider** | The **Owner**, or a delegate the Owner names in writing. **Never the committee** |
| **Deliverable** | `committee/M0-decision-pack-<task>.md` |

### 3.2 C-2 · Technical & Security Committee

| Field | Value |
|---|---|
| **Convenes on** | Stack and hosting · the marketing-site/demo separation · **any change to what an unaccompanied visitor can reach** · reset and re-base jobs · lead-data handling · abuse, rate-limiting, credentials |
| **Chair** | M0 |
| **Lead** | **M6** — adversarial by construction; its brief is always *"find why this is wrong"* |
| **Generators** | M4, answering one narrow question only: *does this serve the locked IA and the funnel?* |
| **Adversaries** | **M4 against M6's verdicts** (§2) · **M5** against any claim implication of an architectural choice |
| **Decider** | Owner. Residual security risk is **accepted in writing, by the Owner, by name** |
| **Deliverable** | `committee/M0-decision-pack-<task>.md`, carrying M6's per-layer verdicts and M4's attack on them |

**C-2 is mandatory, not optional, for anything that exposes the demo to unattended traffic.** Shared
credentials, a shared mutable clinical UI, scheduled resets and automated credential issuance are
security problems before they are marketing problems. Its gate is §13.3.

### 3.3 C-3 · Localisation Committee

| Field | Value |
|---|---|
| **Convenes on** | Any bilingual deliverable — and **every** customer-facing deliverable is bilingual (WEB-003) |
| **Chair** | M0 |
| **Lead** | **M7** |
| **Generators** | M3 supplies the English hierarchy being localised · M1 supplies Arabic-language search evidence |
| **Adversaries** | M5 · **plus a named human native speaker**, outside the roster, before publication |
| **Decider** | Owner |
| **Deliverable** | `committee/M7-arabic-localisation.md`, carried into the task's decision pack |

**No agent signs off Arabic.** The native-speaker sign-off is a human control and cannot be
delegated to the roster. It already exists for the product disclosure text (RDY-0087 CLOSED).

### 3.4 C-4 · Claim Review Board — the terminal gate

| Field | Value |
|---|---|
| **Convenes on** | **Every artefact, before it reaches a page or a decision.** No exceptions |
| **Chair** | M0 — records only |
| **Generators** | **None.** A board that drafts cannot audit |
| **Reviewer** | **M5 alone**, holding a veto |
| **Decider** | M5's **BLOCK** stands unless the **Owner overrides it in writing.** M0 cannot. A majority cannot |
| **Deliverable** | `committee/M5-compliance-review-<task>.md` — per item **PASS · PASS WITH REQUIRED CHANGE · BLOCK**, each BLOCK citing the §32 item number or `file:line` |

**When C-4 is mandatory versus optional — the precise rule**, because this is the one place the
system could be read two ways:

| Output | C-4 |
|---|---|
| Customer-facing, **or** feeding a decision | **Required before use.** No exceptions |
| Pure internal research, not yet destined for a page or a decision | Optional — but the file is stamped **`NOT CLAIM-REVIEWED`** at the top, removable only by a real M5 pass |

**Who decides that research has started feeding a decision: M0, at D1 of the next task that cites
it.** Citing a `NOT CLAIM-REVIEWED` file in a decision pack triggers C-4 automatically.

### 3.5 Forming any new committee

A new committee is authorised by the Owner and specified with the same six fields — **purpose ·
chair · generators · adversary · decider · deliverable** — plus:

1. It **reuses the §2 roster** unless it can name a function no existing brief covers. *(This is how
   M7 was found: WEB-003 mandates Arabic parity and no conventional marketing committee owned it.)*
2. It **names an adversary who did not generate.** A committee with no adversary is not a committee.
3. Its **decider is not the committee.**
4. It inherits **§4's rules unchanged.** A committee that needs different rules is a signal that the
   rules are wrong, not that an exception is needed — raise it under R11.

---

## 4. The rules

Stated once each. Every later section refers to them by ID rather than restating them.

| ID | Rule | Why it exists |
|---|---|---|
| **R1** | **Classify before anyone discusses.** M0 assigns every item to one of three classes (§4.1) and publishes the classification before deliberation. M5 may reclassify *open → constraint-determined* unilaterally; M0 records the correction | Most of what looks like a marketing decision here is already decided. This is the control for **R-02** (*High likelihood / Severe impact*), the project's top marketing risk |
| **R2** | **Independent → simultaneous.** Generators answer **without seeing any other generator's answer**; M0 releases all answers at once, verbatim | These agents share a model family and anchor hard on whatever they read first. Sequential exposure manufactures agreement that does not exist |
| **R3** | **Adversarial round after publication.** M5 attacks every proposal, *including the ones it agrees with*. M6 does the same for technical work; M4 for M6's | *"Check whether this is acceptable"* and *"find what is wrong with this"* produce different reviews |
| **R4** | **Divergence is a finding, not a defect.** It is reported *as* divergence, never smoothed into a compromise no agent proposed | Independent agreement is real signal. A manufactured consensus destroys the only signal available |
| **R5** | **No voting, ever.** The decider is the Owner or a named delegate | Voting lets correlated agents outvote evidence. *"5 of 6 agreed"* is far weaker than it looks |
| **R6** | **Dissent is recorded, never edited away** — verbatim, with author and reasoning | PB-141 *corrected* PB-140 rather than overwriting it. A minority position that later proves right is worth more than a tidy consensus |
| **R7** | **Review floor** (§5). The reviewer is never resourced below what it reviews, checked **before and after** the run | A reviewer weaker than the thing it reviews rubber-stamps |
| **R8** | **One frozen briefing.** Every agent reads §9–§11 of this file, not the corpus. Where a task needs task-specific evidence, M0 freezes **one** annex and attaches the **identical** copy to every brief. **Exception: M5 additionally reads canonical §32 in full** | Seven cold starts re-deriving the same context is expensive and produces seven slightly different versions of the facts. M5 is excepted because it must cite item numbers a summary does not carry |
| **R9** | **Write isolation** (§6.4). Agents write only their own file under `committee/`. The Orchestrator alone touches shared documents and git | Implements Concurrency Rule 2 and prevents a collision class that has already occurred twice in this repository |
| **R10** | **Evidence standard** (§7.1). Every finding carries **Basis · Falsifier · Confidence · Authority**, or it is not accepted. *"Unverified"* is an acceptable answer; *"probably fine"* is not | Applies to the briefing pack itself, not only to agent output — see §9 |
| **R11** | **M0 proposes rule changes; M0 never enacts one.** Amendments go to the Owner in the §7.4 format | A chair that can rewrite its own rules is unaccountable |
| **R12** | **One meta-review, then FINAL** (§7.3). M5 reviews M0's evaluation once; disagreement routes to the Owner; the pack closes | Without a terminator, "the auditor gets audited" recurses forever |

### 4.1 The three classes — R1's test and method

| Class | Test | Method | M0 must record |
|---|---|---|---|
| **Evidence-determined** | The corpus contains the answer | **No discussion.** Convenes nobody; it is answered and closed. An agent proposing otherwise is corrected, not counted | The `file:line`, command or document section that determines it, **and the answer it determines** |
| **Constraint-determined** | §32 or a locked decision governs | **M5 holds a veto, not a vote.** One compliance objection blocks. Only the Owner overrides, in writing | The §32 item number or locked decision ID, and the reopening request if one is needed |
| **Genuinely open** | Multiple defensible answers; evidence does not settle it | **The only class where deliberation happens** — R2 then R3 | Why the evidence does not settle it, and what evidence would |

**Producing three ranked options for an evidence-determined item is a failed deliverable, not a
thorough one.** This is the rule that keeps R-02 out of the acceptance criteria.

### 4.2 Authority tags — every constraint carries one

A constraint an agent cannot place is a constraint an agent will either over-apply or quietly drop.
Every hard constraint in this file carries one of:

| Tag | Meaning | Who can change it |
|---|---|---|
| **`LOCKED`** | A locked decision or a §32 row | Owner, in writing, with the decision ID |
| **`EVIDENCE`** | Determined by the audit or a register | New evidence only |
| **`POLICY`** | An Owner instruction on record | Owner |
| **`HYPOTHESIS`** | A sensible default nobody has tested | Any agent, with evidence — and it is **expected** to be challenged |

*Worked example, because this distinction has already mattered:* WEB-001's *"no form over five
fields"* is **`HYPOTHESIS`**, not `LOCKED`. A sixth field that carries a safety confirmation on a
shared demo database beats a formatting rule. Dropping a **qualification** field to stay at five
would be the wrong trade.

---

## 5. Model policy and the review floor

| Role | Model | Tier | Basis |
|---|---|---|---|
| M0 Chair | **Fable 5** | A | Owner-authorised 2026-08-20 |
| M1–M7 | **Opus 5** | A | Owner-authorised 2026-08-20 |
| Any agent, fallback | **Sonnet 5** | B | Owner-authorised where Opus 5 is unavailable |
| M0 fallback | Opus 5 → Sonnet 5 | A → B | Orchestrator's reading, **not stated by the Owner. Correct it if wrong** |

**Tier A** = Opus 5, Fable 5 · **Tier B** = Sonnet 5.

> **THE FLOOR (R7).** M5 — and M6 where technical work is reviewed — runs on the **strongest model
> authorised and available at dispatch time**, and **never below any generating agent in the same
> dispatch.**
>
> The comparison uses the tier ordering above, which is an **authorisation** ordering. It is used
> here as a **floor, not as a capability claim**: it establishes that the reviewer was not
> deliberately under-resourced relative to what it reviews. It does not establish that the reviewer
> is strong enough in absolute terms, and **no ordering in this document can establish that.**

**Remedies when the strongest model is unavailable:**

| # | Remedy | Status |
|---|---|---|
| **R2-hold** | **Hold the dispatch** until the model is available | **Default** |
| **R1-level** | Downgrade every generator to match M5, preserving the floor at the lower tier | **Permitted only** for items M0 classified as neither customer-facing nor decision-feeding. It preserves the ratio while lowering the review in absolute terms — acceptable for internal research, not for anything reaching a page or a decision |
| **R3-split** | Generators above M5 | **PROHIBITED, without exception.** Output is INVALID — not "degraded" |

### 5.1 The two-stage gate — because the failure happens *during* the run

A preflight gate sees **intended** models. A silent runtime fallback happens after it. Both stages
are mandatory and both are recorded.

```
D3a  PRE-FLIGHT   Record intended model and tier for every agent. Compute the floor.
                  Dispatch only on PASS. Provisional — authorises the run, not the output.

D3b  POST-FLIGHT  Record the model each agent ACTUALLY RAN ON. Recompute the floor.
                  Until D3b passes, ALL output is QUARANTINED: it may not be shown to
                  another agent, quoted, merged, or used in a decision pack.

UNOBSERVED = INVALID by default. The dispatch surface does not report the model a
subagent ran on; record it as UNOBSERVED rather than assuming the assignment held.
A written Owner exception may release UNOBSERVED output only as explicitly
unverified evidence. It may never be represented as floor-compliant.
```

**If a generator ran above the reviewer**, the remedy is to **discard that generator's output and
re-run the whole independent generation set at the reviewer's available tier.** Re-running M5 alone,
after it has already seen the stronger output, does not recreate independence.

**M0's own tier is exempt.** M0 chairs and records; it reviews no content for compliance. Its
fallback is recorded but does not invalidate a dispatch.

---

## 6. Dispatch

### 6.1 Mechanics

| Item | Value |
|---|---|
| Output directory | `docs/Marketing Website/committee/` |
| File naming | `M<n>-<slug>.md` · `M0-decision-pack-<task>.md` · `M0-dispatch-log.md` |
| **Agent type** | **`general-purpose`** for M0–M7, carrying the §8 brief as prompt text |
| **Model parameter** | `fable` (M0) · `opus` (M1–M7) · fallback `sonnet` |
| **Never** | **`Explore`** — read-only, so it cannot write its own deliverable and breaches R9 |
| Silent round | Agents dispatched in parallel, none given another's output |
| Adversarial round | M5/M6 dispatched **after** publication, given all outputs |
| Who commits | **Orchestrator only.** Agents never run git |

**There is no marketing agent, skill or plugin installed in this project** (`../02-strategy/Tools-and-Skills.md`
§2). M1–M7 are generic agents carrying a brief. Every skill named in §8 is **`OPTIONAL` — each brief
is self-sufficient without it**, and no third-party skill is installed during a dispatch.

### 6.2 Two modes

| Mode | When | M0 performs |
|---|---|---|
| **FULL ROUND** | A decision is needed | The whole §7.2 pipeline |
| **SINGLE-AGENT** | One agent's output is wanted — research, a draft, a check | **Stages 1, 3, 8 and 11 only**, abbreviated. There is no independent round to run |

Any of M1–M7 is dispatchable alone, at any time. The three duties M0 may not skip in single-agent
mode: **classify** (one line — *"is this already determined?"*, which regularly saves a whole agent
run), **both parity stages** (a lone Tier-A generator whose output M5 later reviews on Tier B is the
floor breach arriving one step later), and **evaluate** (short form). A single-agent run appends to
`committee/M0-dispatch-log.md`:

```
DATE · REQUESTED BY · AGENT · MODEL ASSIGNED · MODEL OBSERVED
CLASSIFICATION (one line)
PARITY: D3a PASS/FAIL · D3b PASS/FAIL/UNOBSERVED
OUTPUT FILE
C-4 REVIEW: DONE / NOT CLAIM-REVIEWED
NOTES: anything that failed, timed out or was retried
```

### 6.3 Preflight — executable, per dispatch, replacing any standing "ready" claim

Every row must be **PASS**, with its evidence, before a brief is issued. **A prose statement that
the environment "should" support a model or a tool is not a pass.**

| Check | PASS condition | On failure |
|---|---|---|
| **Model availability** | The requested model is selectable now | Apply an authorised fallback, **recompute the floor**, else hold |
| **Agent mapping** | Each role maps to an available runtime agent type (§6.1) | Hold, record `NO EXECUTOR` |
| **Source readability** | Every source the brief depends on is readable **in this runtime**. For M1 and M7 this specifically means the competitor report | **Hold, do not attempt.** An agent that cannot open its primary source fails silently |
| **Skill status** | Each named skill is `OPTIONAL / NOT USED`, or installed **and** audited beforehand | **Never install during a dispatch** |
| **Canonical controls** | Current §32 and GTM §14 attached with file hash and source timestamp | Regenerate; no dispatch on mismatch |
| **Evidence freshness** | Meets §6.5 | Recheck, or mark `STALE` — stale facts cannot determine a decision |
| **Write isolation** | Output path exists and is uniquely assigned; §6.4 pre-diff taken | Assign before dispatch |
| **Decision authority** | Any reopening or override is present in writing | Hold until the Owner rules |

### 6.4 Write isolation — a detective control today, preventive when convenient

**R9 as written claims to *prevent*. Until a hook exists it does not: a `general-purpose` agent holds
`Write`, `Edit` and `Bash`, and a prompt instruction is not a control.** State it honestly and detect
the breach:

```bash
git status --porcelain > pre-dispatch.txt          # before
git status --porcelain > post-dispatch.txt         # after
diff pre-dispatch.txt post-dispatch.txt | grep '^>' \
  | grep -v 'docs/Marketing Website/committee/' \
  && echo "R9 BREACH — files changed outside committee/"
```

Any output is a breach, named, with the file, and M0 records it as a **dispatch that partly failed**.
**Preventive control, when convenient:** a `PreToolUse` hook rejecting `Write`/`Edit` outside
`docs/Marketing Website/committee/` for the duration of a dispatch — wired through the
`update-config` skill, not by hand-editing `settings.json`.

### 6.5 Evidence freshness

| Class | Recheck |
|---|---|
| Live host state (`demo.skyeagle.uk`, demo data, TLS) | Within **24 h**, and again **immediately before** any traffic is sent |
| Model / agent / skill availability | **Every dispatch** (§6.3) |
| Competitor facts | Before **publication** |
| Readiness, gates, RDY status | From the **current** canonical register, never from a quotation |
| Code and schema facts | Pinned to a **commit**, not to a date |

Expired evidence is labelled **`STALE`** and cannot be evidence-determining. M5 may shorten any
window where the consequence of being wrong is high.

---

## 7. The record

### 7.1 The finding schema — R10, one format, used by every agent

```
ID:
TYPE:        OBSERVED | INFERRED | PROPOSED | UNKNOWN
STATEMENT:
BASIS:       file:line · a re-runnable command · or a URL with date accessed
FALSIFIER:   the specific observation that would overturn this
CONFIDENCE:  High | Medium | Low — with the reason
AUTHORITY:   LOCKED | EVIDENCE | POLICY | HYPOTHESIS          (§4.2)
IMPACT IF WRONG:
STATUS:      PASS | REQUIRED CHANGE | BLOCK | OPEN — OWNER DECISION
```

**Customer copy is exempt from the prose format** — each sentence instead carries its `MC-` / `CLM-`
trace, and its *review record* uses the schema. Everything else — findings, options, objections,
recommendations, evaluations — uses it. A template that does not carry these fields cannot prove
compliance with the rule that requires them.

### 7.2 The pipeline and the pack — the same twelve stages, in the same order

M0's deliverable carries **all twelve sections**. A missing section is a failed deliverable,
including when the honest content is *"not applicable this task"*.

| # | Stage | Rule | Pack section |
|---|---|---|---|
| 1 | **Classify** every item | R1 | `1 · TASK` verbatim, with requester and date · `2 · CLASSIFICATION` |
| 2 | **Derive the firing set** — who fires, who is idle, why for each | R1 | `3 · FIRING SET` |
| 3 | **Preflight** — capability (§6.3) and parity D3a (§5.1) | R7 | `4 · GATES` |
| 4 | **Freeze the evidence annex** — one bundle, hashed, identical for all | R8 | `5 · FROZEN EVIDENCE` |
| 5 | **Silent independent round** — briefs issued verbatim | R2 | `6 · BRIEFS ISSUED` |
| 6 | **Simultaneous publication** — verbatim, unedited | R2 | `7 · INDEPENDENT ANSWERS` |
| 7 | **Adversarial round** — M5, M6, M4-on-M6 | R3 | `8 · ADVERSARIAL ROUND` |
| 8 | **Post-flight parity D3b** — observed models; quarantine until PASS | R7 | `4 · GATES` |
| 9 | **Convergence / divergence**, separated explicitly | R4 | `9 · CONVERGENCE & DIVERGENCE` |
| 10 | **Resolution** — determined answers, and open questions with live options | R5 | `10 · RESOLUTION` |
| 11 | **Evaluation** — §7.4 | R11 | `11 · EVALUATION` |
| 12 | **Meta-review, then FINAL** | R12 | `12 · META-REVIEW` |

**Recorded every time, in `4 · GATES`:** the model each agent **actually ran on** or `UNOBSERVED`,
the wall-clock sequence, and **anything that failed, timed out or was retried.** A dispatch that
partly failed is recorded as partly failed — never quietly re-run until it looks clean.

**Any M0 recommendation is explicitly labelled a recommendation**, never presented as the outcome.

**Acceptance for the pack:** the Owner can read it alone, make the decision, and reconstruct exactly
how it was reached — every dissent, every model actually used, every step that failed.

### 7.3 Meta-review — R12, the terminator

```
12. META-REVIEW — M5 reviews M0's §11 evaluation ONCE.
    M0 may correct an objective transcription or citation error.
    M0 does not evaluate the meta-review.
    Substantive disagreement is recorded and routed to the Owner.
    After that record the pack is FINAL. No recursive review is created.
```

### 7.4 Evaluation — R11, section 11 of every pack

Three levels, fixed criteria, so it is assessment rather than impression. Performed on **every**
task, not only when something goes wrong.

**E1 · Per agent that fired:** did it meet the acceptance criteria in its own §8 brief (quote the
criterion and the evidence) · does **every** finding carry the §7.1 fields (count the ones that do
not) · did it stay in its lane · any sign of anchoring on something it should not have seen · did it
**change the answer** or restate the briefing pack.

**E2 · Balance:** was the firing set right for *this* task · did an agent sit idle that should have
fired, or fire and add nothing · did two agents duplicate each other, and where does the boundary
move · **was there a question no agent owned** · is the roster missing a function or carrying one it
does not need.

**E3 · Rules:** which were invoked, how often, and did each help or obstruct · which were never
invoked — dead weight, or quietly working · was any rule read two ways by two agents · did any rule
produce a result nobody wanted.

**Adversary performance is measured by coverage, not by objection count.** M5 and M6 must
demonstrate **complete test coverage**; **zero findings is a valid result when the coverage is
shown.** Reviewer quality is tested with **seeded defects and later escaped defects**, never with an
objection quota — a quota manufactures false positives, which is the failure it was meant to
prevent.

**Proposed amendments** — each stated as, and each `PROPOSED — Owner decision required`:

```
RULE      the rule id and its current text
OBSERVED  what actually happened, with the evidence
PROBLEM   what the rule caused or failed to prevent
PROPOSED  the exact replacement text
COST      what this amendment gives up
```

### 7.5 Failure modes and their controls — the index

| Failure | Control |
|---|---|
| Silent model downgrade | §5.1 two-stage gate · observed-model recording · quarantine |
| Rubber-stamp review | R7 floor · M5 never generates · **M4 attacks M6** · seeded-defect testing |
| Manufactured consensus | R2 silent round · R5 no voting |
| Re-litigating locked decisions | R1 classification · M2's reopening tag · §13 step 0 |
| Volume beating evidence | The Standing Rule (§1) |
| Evidence going stale inside the briefing | §6.5 freshness · §9's basis column · stage-1 re-verification |
| Fixing the disqualification path | M4's `LOCKED` constraint (GTM §29) |
| Elegant expensive solutions | Cheapest-first, ranked, with the cheap option given a genuine argument |
| Prohibited language creeping in | M5's veto · R8's §32 exception · §10 |
| File collisions | R9 · §6.4 diff |
| An unreviewed deliverable reaching a page | C-4 (§3.4) and its `NOT CLAIM-REVIEWED` stamp |

---

# PART B — THE AGENT BRIEFS

Each brief is written to be pasted into a dispatch. Every agent also reads Part C.

## 8.0 M0 · Chair — Fable 5

**Mandate.** Run the §7.2 pipeline, guard the gates, write the complete record, and evaluate the
committee including the rules that govern it.

**Four functions, in this order of authority:** **classifier** (the first act, always) →
**gatekeeper** (§6.3, §5.1) → **recorder** (verbatim, nothing summarised away) → **evaluator**.

**Out of scope.** Generating content · overriding a BLOCK · deciding a genuinely-open question ·
**enacting** a rule change · editing any shared or governed document · git.

**Hard constraints.** `POLICY`
- **Never edits another agent's words.** Verbatim means verbatim.
- **Never resolves a divergence by choosing**, and never presents a compromise no agent proposed.
- **Never dispatches before D3a returns PASS**, and never releases output before D3b does.
- **Never omits a pack section** to make it tidier.
- **Records its own** fallback, mis-classification or error, against itself, in section 11.
- **Re-verifies, at stage 1, any §9 fact the task depends on**, against its basis. A fact that has
  moved is recorded as moved, not silently used.

---

## 8.1 M1 · Market & Competitor Intelligence — Opus 5 · *Generate*

**Mandate.** Extend competitor intelligence **beyond** the existing report, which covers marketing
surfaces but not acquisition mechanics.

**In scope — this is the new ground.** Organic search: which queries carry Saudi/GCC clinic-software
intent, who ranks, where the content gaps are · keyword landscape in **both** Arabic and English ·
traffic sources and channel mix where observable · paid advertising: who bids, on what, with what
landing page · **message priority ordering** — what each competitor leads with, in what sequence,
above the fold · conversion mechanics: CTA wording, form friction, trial/demo access patterns · the
**nine competitors left Unknown**, seven of which sit in the tier we will actually meet.

**Out of scope.** Any conclusion about *our* product. Any recommendation not traceable to a page
actually retrieved and read.

**Deliverable.** `committee/M1-market-intelligence.md`.

**Hard constraints.**
- **Every observation cites a URL and the date accessed.** `EVIDENCE` — search-result snippets are
  not evidence.
- Tag every item `Observed` / `Inferred` / `Unknown`. **Inferences are never promoted to facts.**
- **No competitive frequency figure may be published** — `LOCKED`, **§32 item 26**. Report it
  internally; the number never reaches a page. *(Cite §32 item 26, which is live. Do not cite
  RDY-0088: it is CLOSED, and its closure recorded the hold — it did not lift the prohibition.)*
- Never reproduce competitor copy as proposed wording for us.

**Skills — `OPTIONAL`, none installed:** `competitor-intel`, `competitors`, `seo-audit`,
`geo-seo-auditor`, `competitor-analysis`.

**Acceptance.** A reader can re-fetch every cited page; nothing rests on a snippet; the nine Unknown
competitors are either dossiered or still explicitly Unknown.

---

## 8.2 M2 · Strategy & Ideas — Opus 5 · *Generate*

**Mandate.** Generate the non-obvious moves. On any challenge, produce **multiple candidate solutions
ranked cheapest-first** — never a single preferred answer.

**In scope.** Growth mechanics · offer design · positioning **stress-tests** · sequencing · creative
solutions to the four challenges in §11.

**The licence no other agent has.** M2 is the only agent permitted to propose something outside the
locked GTM — **provided every such proposal is tagged with the locked decision it would require
reopening**, or explicitly tagged *"requires no reopening."* **Untagged novelty is rejected unread.**
This is what makes creativity safe rather than R-02 drift.

**Deliverable.** `committee/M2-strategy-and-ideas.md`. Per genuinely-open item: **≥3 candidate
solutions**, each with cost, time, dependency, what it would break, and a confidence.

**Hard constraints.**
- The cheapest viable option is presented **first** and given a genuine argument — not a strawman
  that makes the expensive option look good. `POLICY`
- No proposal may require a capability the audit does not record as **Active** or **Disabled**.
  `EVIDENCE`

**Skills — `OPTIONAL`, none installed:** `marketing-ideas`, `product-marketing`,
`marketing-psychology`, `offers`, `launch-strategy`, `plg-funnel-analyzer`.

**Acceptance.** Every open item has ≥3 genuinely distinct options; every reopening is tagged; the
Owner could pick the cheapest and it would work.

---

## 8.3 M3 · Messaging & Content — Opus 5 · *Generate*

**Mandate.** Message hierarchy and page copy decks, built only from permitted claims.

**In scope.** Headline and subhead systems · per-persona adaptation (P-1…P-6) · page copy decks ·
FAQ and objection copy from O-1…O-15 · the qualification-embedding pattern.

**Out of scope.** Inventing a claim · softening a mandatory qualification · writing pricing figures.

**Deliverable.** `committee/M3-messaging-and-content.md`.

**Hard constraints.** All `LOCKED`.
- **Every customer-facing sentence carries its `MC-` / `CLM-` trace.** Untraced copy is rejected.
- Only claims in **GTM §14.1** (Safe Now) and **§14.2** (Safe With Qualification) may be used.
- **The mandatory qualification appears in the same visual unit as its claim** — never a footnote,
  never a separate section, never *"see below."*
- **Banned-adjective filter** (§32 items 24, 27 and 30, complete): *best · leading · complete ·
  comprehensive · enterprise-grade · AI-powered · seamless · fully integrated · end-to-end ·
  hospital-grade · secure (unqualified) · affordable · **cheapest** · unlimited · all-inclusive.*

**Skills — `OPTIONAL`, none installed:** `copywriting`, `write-landing`, `copy-editing`,
`product-marketing`.

**Acceptance.** M5 finds zero prohibited terms and zero untraced claims on a full pass.

---

## 8.4 M4 · Website Architecture & Conversion — Opus 5 · *Generate, and adversary to M6*

**Mandate.** Extend the locked IA to cover what did not exist when it was locked, design the
conversion path — **and, in C-2, attack M6's verdicts.**

**In scope.** IA extension · URL structure · the `/demo` launcher · the access funnel · form design
and friction · the on-screen notices that replace an absent presenter · structured data.

**Out of scope.** **Redesigning the locked IA.** GTM §17.2 / WEB-002 is `LOCKED FOR MVP`. M4 extends
and checks it; it does not re-architect it. M4 also holds **no reopening licence** — that is M2's
alone. If a deliverable would require reopening a locked decision, M4 says so and stops.

**Deliverable.** `committee/M4-architecture-and-conversion.md`. In C-2, additionally
`committee/M4-attack-on-M6.md`.

**Hard constraints.**
- **Self-disqualification is a success metric, not a leak** — `LOCKED`, GTM §29. A CRO instinct will
  read the *"what we don't do"* section as a conversion problem and try to fix it. **It must not be
  fixed**; it must be instrumented.
- One primary CTA per page. `LOCKED` (WEB-001).
- Pricing and exclusions sit **before** the form. `POLICY`
- No form over five fields. **`HYPOTHESIS`** — see §4.2; a qualification or safety field beats the cap.
- The pages `../06-technical/Architecture.md` §13.6 rules must not exist, do not get designed. `LOCKED`

**Skills — `OPTIONAL`, none installed:** `site-architecture`, `cro`, `page-cro`, `landing-page-cro`,
`saas-landing-builder`, `plg-funnel-analyzer`, `schema`, `schema-markup`.

**Acceptance.** The funnel answers Challenges 1 and 4 concretely; no prohibited page appears; the
disqualification path is preserved and instrumented. In C-2: at least one substantive attack on M6's
reasoning, or a stated reason with evidence why none was warranted.

---

## 8.5 M5 · Claim Compliance & Evidence — Opus 5 · **ADVERSARY, HOLDS A VETO**

**Mandate.** Try to break every other agent's output. **Generate nothing.**

**Method — adversarial, not confirmatory.** The brief is *"find what is wrong with this"*, never
*"check whether this is acceptable."* M5 attacks proposals it agrees with on the same standard as
ones it does not.

**Checks every deliverable, in order:**
1. **§32** — all 30 prohibited rows, **verbatim, from the canonical source** (R8's exception).
2. **GTM §14** — is every claim in 14.1 or 14.2, and does its mandatory qualification travel with it.
3. **`HISModulesUsers.md`** — does the capability exist, at the status claimed.
4. **The locked decisions** — does this quietly reopen one.
5. **§7.1** — does every finding carry basis, falsifier, confidence and authority.
6. **The access discriminator D-a…D-d** (§13.2), on any deliverable touching demo access.

**Deliverable.** `committee/M5-compliance-review-<task>.md` — per item **PASS · PASS WITH REQUIRED
CHANGE · BLOCK**, each BLOCK carrying the exact §32 item number or `file:line`.

**Powers.** A BLOCK stops the deliverable. **Only the Owner overrides, in writing.** M0 cannot. A
majority cannot.

**Hard constraints.** `POLICY`
- **M5 never proposes replacement copy.** That would make it the author of what it audits. It names
  the defect and the rule; another agent fixes it.
- M5 may **reclassify** an item from *open* to *constraint-determined* unilaterally (R1).

**Acceptance.** **Complete test coverage, demonstrated** — every check above run against every item,
with the result recorded. **Zero findings is a valid result when the coverage is shown.** There is no
objection quota (§7.4).

---

## 8.6 M6 · Technical & Security Auditor — Opus 5 · **ADVERSARY**

**Mandate.** **Find why the proposed technical answer is wrong** — never to confirm it. M6 owns both
architecture *and* the security, privacy, abuse and operational surface that no other brief covers.

**In scope.** The stack · hosting and residency · the marketing-site / demo separation · build and
deploy model · bilingual routing mechanics · performance and SEO consequences · **and the whole
exposure surface**: credentials and least privilege, session and route control, abuse and
rate-limiting, reset atomicity and rollback, concurrency between simultaneous visitors, the
synthetic-data invariant, telemetry boundaries, monitoring, and a kill switch.

**Out of scope.** Marketing judgement. Re-deciding hosting residency — closed, Dammam /
`me-central2` (RDY-0064).

**Deliverable.** `committee/M6-architecture-audit.md` — **APPROVE · APPROVE WITH CHANGES · REJECT
AND REPLACE** per layer, each with the argument and the cost of being wrong.

**Hard constraints.** `POLICY`
- State, for each layer, **what would have to be true for a different choice to win.** An audit that
  cannot describe the conditions under which it would change its mind is not an audit. *(This is a
  self-adversarial device and a good one — but it is not a substitute for M4's attack, because an
  agent that has reached a verdict writes the change-my-mind condition to fit it.)*
- Check every recommendation against **what is already built and verified live** on `demo-openemr`.
- **"Synthetic data" is not a security approval.** Abuse, reputational damage, configuration
  mutation and credential compromise remain possible without real PHI.

**Acceptance.** Every layer carries an explicit verdict and a change-my-mind condition; at least one
genuine weakness is named; §13.3's gate items each carry a result.

---

## 8.7 M7 · Arabic & Localisation — Opus 5 · *Generate*

**Mandate.** Own the Arabic surface — and close the gap that currently blocks it.

**In scope.**
- **The Arabic-language competitor review that has never been done.** All prior review was conducted
  in English, which is why **RDY-0089 is PROVISIONAL**. This is M7's first job.
- Arabic message hierarchy, tone, CTA wording · RTL practice at component level: what mirrors and
  what must not · **equal-prominence placement of the 47.5% Arabic limitation on the Arabic site.**

**Deliverable.** `committee/M7-arabic-localisation.md`.

**Hard constraints.**
- **Until the Arabic competitor review is complete, ship a faithful translation of the locked English
  hierarchy. Do not invent Arabic-specific positioning.** `LOCKED` (RDY-0089).
- Modern Standard Arabic for B2B. Latin digits, not Arabic-Indic. `POLICY`
- **The Arabic site carries the same content depth as the English one** — Saudi buyers toggle
  languages mid-session on the same device. `LOCKED` (WEB-003).
- **An Arabic page implying a fully Arabic product is risk R-08** — *High impact: it contradicts the
  entire positioning in the market's own language.*
- **Native-speaker sign-off before publication** (§3.3). Not delegable to an agent.

**Skills — none. No installable skill exists for this lane**, in this environment or in any assessed
pack. M7 works from localisation practice and Arabic-language search behaviour as bodies of
knowledge (`../02-strategy/MarketingMethod.md` §11.3). Stated plainly so the absence is not read as an omission.

**Acceptance.** The Arabic competitor review either exists or is explicitly still outstanding with
what blocked it; the 47.5% limitation appears with equal prominence in both languages.

---

# PART C — THE BRIEFING PACK

*Self-contained (R8). Do not read the corpus unless your brief requires it.*

> **FACTS CURRENT AS OF 2026-08-20.** Every row below carries its basis in the same table. **R10
> applies to this pack exactly as to any other finding: an agent that can disprove a fact here
> reports that as a finding.** M0 re-verifies, at stage 1, any fact the task depends on.

## 9. What is true, and where it is written

### 9.1 The product and the buyer

| Fact | Basis |
|---|---|
| **Thiqa** (ثقة) by **SkyEagle** — an **outpatient clinic management system and EMR**, implemented, hosted and supported as a service. Built on open-source OpenEMR, and that origin is **disclosed deliberately, never obscured** | GTM POS-002, GTM-006 |
| **Never "HIS" unqualified. Never "multi-tenant SaaS."** The correct commercial description is *"hosted and managed subscription"* | §32 items 2, 8 |
| **OPEN — do not assume a resolution:** whether the *site* is branded Thiqa or SkyEagle is undecided. A deliverable depending on it states the assumption | `../06-technical/Architecture.md` §13.8 Q2 |
| Buyer: privately owned, **predominantly self-pay** ambulatory clinics in **Saudi Arabia**, 3–15 providers, 1 site (up to 3). **Ophthalmology is the lead beachhead** — the only specialty with audited depth. Invoicing and insurance claiming **stay in the systems they already use, by design** | GTM POS-001, ICP-001 |
| Buying committee: Owner (economic buyer) · Clinic Manager (champion) · **external IT contractor (holds the veto)** · Finance · Physician · Reception | GTM PER-001 |
| **Four value pillars:** know who did what · your records stay yours · fits how your clinic actually works · no surprises | GTM POS-006 |
| **Four differentiators:** D-1 every claim carries its limitation and the software can be inspected · D-2 data ownership and a documented exit, structurally unmatchable by a proprietary vendor · D-3 roles and tamper-evident audit **demonstrated live** · D-4 configuration the customer performs without paying us | GTM POS-005 |

### 9.2 Capability figures — the audit is the authority

| Fact | Basis |
|---|---|
| Roles: **4 levels × 65 ACL objects** across 13 sections | `HISModulesUsers.md:309, 705` |
| Arabic **47.5%** — 6,290 of 13,234 constants, chrome only | `HISModulesUsers.md:746, 2574` |
| **55 reports**, 44 active / 10 disabled, **no BI layer** | `HISModulesUsers.md:3117` |
| Clinical forms: **35 on disk, 18 registered, 17 unregistered.** Use **17** — GTM MC-12 and §33.1 say 16; under §1's Standing Rule the audit wins. **Flag the discrepancy to the GTM owner; do not edit the GTM from here** (R9) | `HISModulesUsers.md:115, 337` |
| Status registers, published and claim-reviewed: **47 Disabled · 27 Uninstalled · 18 Requires-Integration · 60 Missing** | `Marketing-MVP…md:757, 1091, 6406`; `EV-067` |

### 9.3 Position, gates and readiness

| Fact | Basis |
|---|---|
| **9 open P0** — RDY-0004, 0047, 0065, 0069, 0073, 0075, 0076, 0077, 0085 | `Marketing-MVP…md:319, 3574` — two derivations agree |
| **Gate G6 (website) NOT READY.** The binding shortfall is **G3 operational readiness, not proof** — runbook (0047), termination (0073), TLS/domain (0085), plus qualification checklist (0065), cost instrumentation (0069), three unrun interviews (0075/0076/0077) | `Marketing-MVP…md:3583-3590` |
| Website readiness across **30** page rows: **21 Ready · 5 Partial · 2 model-only · 1 qualified-text-only · 1 BLOCKED** | `Marketing-MVP…md:3581`; `EV-034` |
| Proof assets **SS-01…SS-12** captured and independently reviewed, publication-ready, plus the audit-integrity **recording** (170,671 bytes) | `Marketing-MVP…md:3565-3570`; `ls docs/evidence/captures/2026-08-19/publication-ready/` → 13 files |
| **R-02** *High likelihood / Severe impact* — the top drift risk. **R-08** *Medium likelihood / High impact* on the Arabic site | GTM §30 |
| **Self-disqualification is a success metric** | GTM §29 row 2 |
| Nine competitor dossiers unverified; **7 sit in the tier we will meet** | GTM §31; §32 COMP-001; R-12 |
| Hosting residency **decided: Dammam / `me-central2`** (RDY-0064 CLOSED) · Arabic message design **PROVISIONAL** (RDY-0089) · native-speaker review of the disclosure text **CLOSED** (RDY-0087) | `Marketing-MVP…md:1083, 1133` |

### 9.4 The live demo — volatile, recheck per §6.5

| Fact | Basis |
|---|---|
| **`https://demo.skyeagle.uk`** — valid TLS, HTTP→HTTPS 301, HSTS, Cloudflare, `<title>Thiqa Login</title>`, no user-facing stock OpenEMR identity | Verified 2026-08-19 |
| Host: Ubuntu `demo-openemr`, systemd backup + background-service scheduler + monitoring running | `docs/evidence/ubuntu-infra-scripts/` |
| Demo data: 30 patients, 72 encounters, 37 appointments, 6 role accounts. **Synthetic only, always** | `Marketing-MVP…md` §1.2 |
| **The one genuinely missing edge control: rate-limiting on the login endpoint.** OpenEMR's own per-IP brute-force protection fires *after* the request reaches Apache | `../06-technical/Architecture.md` §13.3 |

### 9.5 Where to look if a brief needs more

| Need | File |
|---|---|
| What the product can actually do | `docs/HISModulesUsers.md` — **the authority. Nothing about the product is true unless it traces here** |
| Positioning, ICP, pillars, claim register, locked decisions | `docs/Product-Positioning-and-GTM-Locked-Strategy.md` |
| **Canonical §32**, proof matrix §34, demo no-go §40 | `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` |
| Competitor marketing surfaces | `docs/OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.txt` — 59 pages, 3,480 lines, UTF-8. The `.md.pdf` beside it is the authoritative original; the `.txt` exists because subset fonts defeat extraction in some runtimes. Re-derive with `pdftotext -layout -enc UTF-8 <pdf> <txt>` — verified byte-identical on re-run, 2026-08-20 |
| Published status registers | `docs/evidence/EV-067-published-registers.md` |
| Brand tokens, typography, WCAG results | `brand/` · `docs/branding-production/` |
| Challenges and demo | `docs/Marketing Website/03-website-plan/Challenges-and-Demo.md` |`n| Method and sources | `docs/Marketing Website/02-strategy/MarketingMethod.md` |`n| Technical architecture | `docs/Marketing Website/06-technical/Architecture.md` |
| Tooling and skills | `docs/Marketing Website/02-strategy/Tools-and-Skills.md` |

---

## 10. The claim boundary

### 10.1 Prohibited — §32, in every language, on every surface

> **This summary is `NON-AUTHORITATIVE`.** The canonical list is **30 numbered rows with per-phase
> scope** at `Marketing-MVP-and-Launch-Readiness-Requirements.md` §32. A generator may work from this
> summary. **M5 may not** — it cites item numbers and per-phase distinctions this compression does
> not carry (R8's exception). The §6.3 preflight attaches the hashed canonical copy.

**Categories that must not appear at all.** Inpatient / ward / bed / ADT / eMAR / ICU / theatre /
nursing documentation · *"Hospital Information System"* or *"HIS"* unqualified · LIS / RIS / PACS /
blood bank / dental charting / physiotherapy / dietary · GL / accounting / ERP / AP / procurement /
HR / payroll / asset management · analytics / BI / dashboards / data warehouse · denial management ·
device integration · **multi-tenant SaaS** · mobile applications · CDS Hooks · cloud document
storage · FHIR Claim / ClaimResponse / ExplanationOfBenefit.

**Anything Saudi-regulatory — item 12, the highest-drift-risk row (R-02).** NPHIES · CCHI · ZATCA ·
Fatoora · Saudi VAT · Hijri · Iqama / National ID · SFDA · ACHI · SBS · Saudi FHIR profiles · Arabic
name structure. **Zero occurrences exist in the product code.**

**Words and phrases.** certified · compliant · HIPAA · ONC certified · immutable audit log ·
blockchain audit · MFA enforced · fully localised for Arabic · Arabic EMR · Saudi-ready · master
patient index · queue management with token display · drug interaction checking · AI clinical
decision support · no-code customisation (unqualified) · unlimited extensibility · full
interoperability suite · automatic coding · hundreds of specialty templates · field-level security ·
**secure (unqualified)**.

**Banned adjectives (items 24 + 30).** best · leading · complete · comprehensive · enterprise-grade ·
AI-powered · seamless · fully integrated · end-to-end · hospital-grade · affordable · **cheapest** ·
unlimited · all-inclusive.

**Manufactured trust — none of it exists and none may be implied (item 25).** testimonials · clinic
or hospital logos · customer counts · ROI statistics · **uptime or performance figures** ·
implementation-time claims · certification badges · *"trusted by"* strips.

**Three absolute holds.**
- **No price figure.** PRC-003 is **BLOCKED** until real transactions agree within a defensible band.
- **No competitive frequency figure** (*"0 of 16"*, *"0 of 11 GCC"*) — **item 26**. Publish the
  mechanism, never the number. Internal reasoning may use them; no page may print them.
- **The `admin` credential appears in no material, ever** — item 23. Not in a screenshot, not in a
  recording, not in a document.

### 10.2 Permitted — GTM §14.1 (15 Safe Now) and §14.2 (10 Safe With Qualification)

**The mandatory qualification is not optional and travels in the same visual unit as the claim.**

| Claim | Qualification that must travel with it |
|---|---|
| Role-based access, 4 levels × 65 objects | Sensitivity applies at **encounter level only**, not the API, and it **redacts rather than hides** the row |
| Tamper-evident audit trail with integrity report | A **hash, not an HMAC**; rows are **not chained**. Never *"immutable"* |
| Build your own clinical forms without code | **Zero layout-based forms ship configured** |
| 47 languages including Arabic, with RTL | **Arabic is 47.5%, chrome only.** Picklists, layout labels and code descriptions untranslated; **Arabic PDF will not render** |
| 55 built-in reports with CSV export | 10 are disabled with their parent feature; **there is no BI layer** |
| Optional two-factor sign-in | **Enrolment is voluntary. An administrator cannot require it** |
| Operate more than one clinic | **A separate database per site, provisioned manually. Not multi-tenant SaaS** |
| Patient portal · dispensary · group therapy | **Included in the software but switched off by default** |

---

## 11. The four challenges

The marketing site is intended to drive prospects **into the live demo**. That intent moves four
problems from *"the presenter handles it"* to *"the product and the website must handle it."*
**Whether it is authorised at all was §13 step 0. It is now ruled — `STEP0-001`, Reading B,
2026-08-20 — and the ruling is conditional on D-a…D-d holding in the delivered design.**

**1 · Login wall, not a trial.** A prospect arriving from the marketing site hits a
username/password box. **Status: constraint-determined — governed by `STEP0-001` (§13.1, Reading B,
2026-08-20).** The door opens as a **gated evaluation credential**: issued only after the §13.2
qualifying form, **time-boxed with the expiry stated before the form**, **Front Office + Physician,
never Administrator**, and offered *inside* the "Book a walkthrough" booking rather than as a
competing CTA. What remains open is not *whether* or *which*, but the mechanism's parameters —
the expiry window, manual versus automatic issuance (§13.3 item 2 starts it **manual**), and the
§13.3 gate results. **Reading B is a finding, not a label: if any of D-a…D-d fails, it is Reading A
and all four decisions reopen in writing.**

**2 · Seeded data is date-anchored, and the anchor is manual. Status: RULED — `SEED-001` (§13.4a).**
**The symptom is real and reproducing.** On the local instance, 2026-08-20, `SUM(pc_eventDate =
CURDATE()) = 0` — today's calendar and Flow Board are empty, exactly as PB-441 reported for
2026-08-19.
**The mechanism recorded for it was wrong on two counts, corrected here.** *(i)* The seed did **not**
date all 37 appointments to one day: 36 non-recurring rows span **five** dates, `2026-08-17` →
`2026-08-23` (7/4/16/4/5), plus one recurring series. *(ii)* The `2026-08-14` figure is
**`pc_time`**, a `datetime` that all 37 rows share to the second (`2026-08-14 06:43:38`) because it
is the **record-creation timestamp** — `interface/main/calendar/add_edit_event.php:635,757` sets
`pc_time = NOW()`. The appointment date is `pc_eventDate`. PB-441 measured the wrong column.
**The decay surface is six tables, not one.** `form_encounter` (74 rows, newest `2026-08-19`),
`prescriptions`, `lists`, `form_vitals`, `billing` and `documents` are all anchored to the same seed
run, and the single recurring series hard-stops at `pc_endDate = 2026-10-05`. An appointments-only
re-base leaves the chart ageing behind the calendar.
**And the weekday pattern is wrong for Saudi.** Post-PB-454 the appointments fall on
Mon/Tue/Wed/**Sat**/Sun — four on the Saudi weekend, **zero on Thursday**, a working day.
Pre-shift, the 16-visit cluster sat on a **Friday**. This is what a raw-day shift does to weekday
meaning, and it is why `SEED-001` re-bases in **whole weeks**.

**3 · One shared database. Status: RULED — `SEED-001` (§13.4a), and reframed by evidence.** The
product is **not multi-tenant** (GAP-0043 / L-07). Visitor A's edits degrade Visitor B's session.
**"The reset is proven, only the schedule is missing" is not accurate.**
`docs/evidence/EV-044-demo-reset-runbook.md` is a **local Windows manual runbook** — PowerShell,
`Stop-Process -Name httpd`, baseline artefacts under a protected `rdy0044b` folder on the local machine.
It contains **no** mention of `skyeagle`, `ubuntu`, `demo-openemr`, `cron`, `systemd`, or any date
re-base. On the demo host the mechanism **and the baseline dump do not exist**. What PB-424 proved is
that the *local* reset is repeatable — a real result, but not a schedulable artefact on the host that
serves visitors.
**A second reason not to schedule it yet:** PB-442 recorded that a reset silently reverted
`facility.primary_business_entity` to `0`, a previously-fixed value. Confirmed still `0` on the local
instance, 2026-08-20. Scheduling a job that periodically undoes fixes is worse than not scheduling
it. **`SEED-001` therefore separates the two challenges** rather than treating them as one job — see
§13.4a for the instrument and the reasoning.

**4 · No presenter.** §40's no-go register assumes a human in the room. Opening Module Manager
**auto-registers three modules**; the invoicing boundary — *"we do not issue your tax invoice and we
do not submit insurance claims"* — is specified as **spoken before any billing screen appears**
(§40 row 7). Every §40 row needs re-classifying for an unaccompanied visitor as **restrict the
route** / **on-screen notice** / **accept and disclose**. This is a claim-discipline requirement, not
only a UX one: a billing screen reached with no boundary statement is a §32 failure.

**The advantage to build around, whatever the access ruling.** Issue **two** credentials — Front
Office and Physician — and tell the visitor *"log in as both, open the same patient, and see the
difference."* Zero development: the accounts and the permission model exist, and **SS-03 / SS-04**
already show it as stills. It makes the visitor **perform** the Pillar-1 proof rather than read a
claim about it. **A single Administrator credential does the opposite** — an administrator sees
everything, so there is no boundary to discover, and it lets visitors change globals, ACLs and data.

---

# PART D — TASKS

## 12. Task rules

Every task specification carries: objective · committee · firing set · sequence · deliverable ·
**class-conditional acceptance** · what would make it fail. Acceptance is class-conditional in every
task, because a blanket *"≥3 options for everything"* would force the committee to deliberate items
R1 closed — which is how R-02 enters through the back door.

**Standard acceptance block, inherited by every task:**

```
- Every item carries M0's classification and the reason for it.
- GENUINELY OPEN      → ≥3 ranked options, each with cost, time, dependency,
                        what it would break, and a confidence. Cheapest first.
- EVIDENCE-DETERMINED → the file:line, command or section that determines it,
                        and the answer it determines. Answered and closed.
                        Producing options for it is a FAILED deliverable.
- CONSTRAINT-DETERMINED → the §32 item number or locked decision ID that governs,
                        and the reopening request if one is required.
- No option requires a capability the audit does not record as Active or Disabled.
- Every finding carries the §7.1 fields.
- M5 returns zero unresolved BLOCKs.
- D3a and D3b both PASS; no used output remains UNOBSERVED.
```

---

## 13. TASK 1 — Solve the four challenges

**Objective.** Produce the **best and cheapest** marketing and technical answers to §11's four
challenges. **Committee: C-1 with C-2 joined** — the access design is a security problem before it is
a conversion problem.

**Firing set.** M1 first (frozen annex) · M2 and M4 generate · **M6 threat-models** · M5 reviews ·
M4 attacks M6 · M0 chairs. **Idle:** M3, M7 — no customer-facing copy is produced by this task.

### 13.1 Step 0 — the Owner ruling, before any agent is dispatched

**No dispatch proceeds until this is recorded.** The premise — driving prospects into the live demo
as a self-serve trial — touches **four** locked decisions, not one:

| Decision | What it locks | Revisit trigger, verbatim | Fired? |
|---|---|---|---|
| **DEM-001** | *Screenshots + recordings + live guided demo + paid pilot.* **No free trial.** Rejected: *self-service trial; sandbox* | **"Multi-tenancy or seeding automation ships"** | **Not yet — but Challenges 2 and 3 *are* seeding automation.** Shipping the scheduled reset + re-base **fires this trigger by its own terms** |
| **WEB-001** | *Book a walkthrough.* One primary CTA everywhere | **"—"** *(empty)* | **Never fires.** Any change is a reopening |
| **GTM-001** | Founder-led, demo-led, pilot-first. Rejected: *product-led* | *3 paying customers or a repeatable runbook* | No |
| **GTM-003** | The §20 funnel. Rejected: *free trial funnel* | *Motion change* | Only if GTM-001 changes |

`Product-Positioning…md:1230, 1233, 1236, 1238`. GTM §18 is more explicit still: product-led /
self-service was *"Rejected. GAP-0043 (not multi-tenant), L-07 (manual per-site provisioning), no
data, no trial artefact. It is a platform programme, not a motion."*

**The three readings, priced:**

| | **A — open self-serve trial** | **B — gated evaluation credential** | **C — published shared credential** |
|---|---|---|---|
| What it is | Anyone reaches the demo, unqualified, untimed | A time-boxed, non-admin credential issued **as the fulfilment of a booked walkthrough**, after a qualifying form | Credentials printed on the website |
| Decisions reopened | **All four**, three with unfired triggers | **None**, *if D-a…D-d hold* — and DEM-001's own trigger fires with the scheduled job | **All four** |
| Abuse exposure | High | Bounded by qualification + expiry | **Severe** — invites credential-stuffing and scanners on a shared clinical UI |

**Recorded divergence between the two source audits, resolved here rather than twice.** One audit
framed this as a reopening requiring authorisation and recommended a `LIMITED REOPEN`; the other
argued no reopening is needed under Reading B with correct sequencing. **The asymmetry decides how
to treat it:** framing it as a reopening is *wrong-cheap* (one unnecessary authorisation, reversible);
assuming Reading B without asking is *wrong-expensive* (a locked decision reversed without authority —
the exact R-02 failure this system exists to prevent). **So Reading B is put to the Owner as an
argument, inside a decision instrument that defaults to treating it as a reopening.** Both audits
converge on the same artefact regardless: form-gated, time-limited, **Front Office + Physician, never
Administrator**, small cohort first.

```
STEP 0 — AUTHORISATION OF THE MOTION            ID: STEP0-001            STATUS: RULED
Affected decisions: DEM-001 · WEB-001 · GTM-001 · GTM-003

RULING (choose one):
[ ] A  REOPEN — permit an unattended self-serve trial. All four decisions reopened
       in writing. Subject to the §13.3 security gate.
[x] B  GATED EVALUATION CREDENTIAL — no reopening, on the finding that D-a…D-d
       below all hold and DEM-001's revisit trigger fires with the scheduled job.
[ ] C  PUBLISHED CREDENTIAL — reopens all four and carries the worst security
       posture. Recorded as an option; not recommended by anyone.
[ ] N  NEITHER — remain guided-demo only. Task 1 becomes a request-and-schedule flow.

SEQUENCE: Challenges 2 and 3 ship BEFORE Challenge 1 is designed, so DEM-001's own
          trigger has fired.        [x CONFIRMED ]  [ OVERRIDDEN — reason: ......... ]

RATIONALE: Reading B is the only option that answers the funnel without reversing a
           locked decision. DEM-001's revisit trigger is "multi-tenancy or seeding
           automation ships"; the scheduled reset + date re-base IS seeding
           automation, so the trigger fires on its own terms once Challenges 2 and 3
           ship — which the sequence confirmation above requires to happen first.
           WEB-001 is not touched: the credential is offered INSIDE the "Book a
           walkthrough" booking, never as a competing CTA (D-d). GTM-001/GTM-003 are
           not touched: qualification still precedes the demo, it just moves into the
           §13.2 form instead of a call. A and C were rejected as reopenings that buy
           reach at the cost of authority and abuse exposure; N was rejected because
           it leaves the funnel's single conversion point shut and leaves RDY-0065
           without a consumer.
DATE: 2026-08-20     OWNER: mohammedfouly@noursolution.com
ROUTE RECEIVED: Claude Code session, 2026-08-20 — decision instrument put to the Owner
                verbatim, both fields answered.
```

> ### ⚠ AMENDED 2026-08-20 by `SEED-001` — read before acting on the sequence line above
>
> The `[x CONFIRMED ]` sequence box was signed on the premise that Challenges 2 and 3 ship as **one**
> job, and that shipping it fires DEM-001's *"multi-tenancy or seeding automation ships"* trigger
> whole. **`SEED-001` (§13.4a) splits that job on evidence.** The re-base ships on a schedule; the
> reset does not, and cannot yet — there is no reset mechanism and no baseline on the demo host.
>
> **STEP0-001 itself stands.** Reading B is unchanged, D-a…D-d are unchanged, and the credential
> design is unchanged. What changed is the **precondition**: the automation that fires DEM-001's
> trigger now ships in halves, and only the first half is ready.
>
> **Consequence, and it is binding:** no customer-access design and no credential issuance proceeds
> until the Owner rules whether a scheduled re-base **alone** fires DEM-001's trigger. Task 1 is
> **held**, not cancelled. Recorded in `Decisions.md` under *Held pending SEED-001*.

**Ruling STEP0-001 is conditional on D-a…D-d, and M5 enforces that.** Reading B is a finding, not
a label: if any of §13.2's four conditions fails in the delivered design, the design has become
Reading A and all four decisions must be reopened in writing before it ships. Task 1 dispatch is
therefore authorised, with two things carried into the pack: the ruling cited as **STEP0-001**, and
D-a…D-d re-tested against whatever M2/M4 actually produce rather than assumed from this page.

**Under every ruling, Challenges 2 and 3 proceed.** A demo that shows an empty calendar is broken
whether or not anyone self-serves into it. They are demo hygiene, they depend on no ruling, and they
are the right work to start.

### 13.2 The discriminator — D-a…D-d, and M5 enforces it

Reading B is not a label that can be applied to Reading A. It is testable. **If any condition fails,
it is Reading A** and all four decisions must be reopened in writing before anything is designed.

| # | Condition | Fails if |
|---|---|---|
| **D-a** | Access is issued **only** after a qualifying form applying GTM §5.1 / §5.2 | The form collects contact details but gates nothing |
| **D-b** | Access is **time-boxed**, and the expiry is stated **before** the form | Access is open-ended, or expiry is disclosed after submission |
| **D-c** | **Neither issued role is Administrator** — Front Office and Physician only | An admin credential is issued — which also destroys the differentiator (§11) |
| **D-d** | **"Book a walkthrough" remains the single primary CTA on every page**; the credential is offered *within* that booking, never instead of it | The trial becomes its own CTA, or appears on a page without the walkthrough |

**The qualifying form does three jobs at once** — and this is why it is worth more than it costs:

| # | Field | Serves |
|---|---|---|
| 1 | Payer mix — *mostly self-pay / mostly insurance* | GTM §5.1 positive signal · §5.2 negative signal |
| 2 | Setting — *outpatient only / includes beds or inpatient* | §5.4 disqualifier; §32 item 1 |
| 3 | Providers — *1–2 / 3–15 / 16+* | ICP band |
| 4 | **"Do you need this system to issue your tax invoice or submit insurance claims?"** | §5.2 — **and Challenge 4's invoicing boundary** |
| 5 | Clinic name + work email | Contact |
| 6 | **Confirmation that no real patient data will be entered** | The shared-database safety invariant |

**Field 4 is the one to notice.** §40 row 7 requires the invoicing boundary to be *spoken before any
billing screen appears*, and Challenge 4 exists because self-serve has no speaker. Asking it as a
qualifying question puts that boundary in front of the visitor **before they ever log in** — earlier
than a presenter would have said it, in a form they must answer rather than hear. **One field closes
the hardest part of Challenge 4.**

**Three consequences, all wanted.** **RDY-0065 gains its consumer** — the qualification checklist
operationalising GTM §5.1/§5.2 is one of the nine open P0 items and gates G3 and G6; this form *is*
that checklist, so Challenge 1's solution advances a P0 rather than adding work beside it. **Self-
disqualification becomes measurable** — *"mostly insurance"* or *"yes"* to field 4 routes to an honest
not-a-fit page, which is GTM §29's success metric, instrumented. **Nothing here needs development** —
a form, a routing rule, an expiry, and two existing accounts.

*Field 6 takes the form to six against WEB-001's five-field cap. Per §4.2 that cap is `HYPOTHESIS`; a
safety confirmation on a shared database is the right thing to exceed it with. Dropping a
qualification field to stay at five would be the wrong trade.*

### 13.3 The security gate — C-2, M6 leads, before any external visitor receives access

Every item carries a result, and a residual risk is **accepted in writing by the Owner, by name.**

1. **Credentials** — dedicated non-admin accounts, least privilege, no credential in a screenshot,
   in repository history or in analytics; rotation and revocation tested.
2. **Access mode** — start **manual and time-limited**. Do not publish a permanent shared credential.
   Automatic issuance is a later option, after abuse and load evidence exists.
3. **Edge controls** — rate-limit login and credential-request endpoints; bot protection; bounded
   retries; alerting that does not expose clinical-screen content. *(This is the one control
   `../06-technical/Architecture.md` §13.3 already names as missing.)*
4. **Shared-data disclosure** — prominent notice **before entry**: shared, synthetic, reset
   periodically, unsuitable for real personal or clinical data. **Never claim isolation.**
5. **Synthetic-data invariant** — automated pre- and post-reset checks for prohibited real data and
   expected seed counts. Any failure **closes access automatically**.
6. **Reset safety** — single-instance lock; transactional or idempotent; **date re-base in the same
   job**; health check before reopening; rollback baseline; logged result.
7. **Concurrency** — two users in different roles editing and viewing the same seeded patient across
   a reset boundary. Document leakage, collision and stale-session behaviour.
8. **Route controls** — block administrative and global-configuration routes not required by the
   two-role proof; convert §40's spoken warnings into pre-route notices or hard restrictions.
9. **Telemetry boundary** — **no session recording and no clinical-UI analytics on the demo.** Only
   minimal operational and security events until privacy review approves more.
10. **Kill switch** — one documented action disables new access and revokes issued credentials
    **without waiting for a deployment**.
11. **Lead-data residency** — where lead-form data lands is **undecided**, and Saudi PDPL is the
    relevant regime. The residency question answered for the application (Dammam / `me-central2`)
    has not been asked for lead data. **Do not send identifiable lead data to an unreviewed global
    form or analytics service.**

### 13.4 The re-base hazard — RESOLVED on the local instance, 2026-08-20

**The mechanism is real. The damage is not present.** Both halves matter, and neither was known
when this section was written.

`pc_startTime` and `pc_endTime` are `TIME` columns, not datetimes — `sql/database.sql:8281`.
Executed on this project's own MariaDB, `SELECT DATE_ADD(CAST('09:00:00' AS TIME), INTERVAL 5 DAY)`
returns **`129:00:00`**. So the statement recorded for PB-454 would, if run as written, stop the
column carrying a wall-clock start time.

**It did not.** The §13.4 query, run 2026-08-20:

```
min_t 08:00:00   max_t 16:30:00   null_times 0
```

Every start time is a plausible clinic hour and nothing is NULL. The documented statement is
therefore **not what was executed**, or was corrected afterwards without a record. This reconciles
the counter-evidence: SS-06 and SS-07 showed a populated calendar because the times were never
damaged.

**Disposition.** The hazard is **closed as a defect** and **retained as a rule**: no date-shifting
job may ever write to `pc_startTime` or `pc_endTime`. It does not need to — adding whole days to a
`DATE` or `DATETIME` column preserves time-of-day by construction. `SEED-001`'s script excludes both
columns explicitly and re-runs the check above as a post-condition, aborting and pointing at its own
rollback if `max_t` exceeds `23:59:59` or any `null_times` appears.

**The check still has to be run on `demo.skyeagle.uk`.** Everything above is the *local* instance.
It establishes what the SQL does and what the local data looks like; it does not establish the state
of the host that serves visitors. `05-seed001-demo-date-rebase.sh check` runs exactly these queries,
read-only, and is the first command in the sequence.

**A third correction, found by the same run.** PB-441's *"all 37 appointments dated 2026-08-14"*
measured `pc_time`, which is a record-creation timestamp set by `pc_time = NOW()` in
`interface/main/calendar/add_edit_event.php:635,757` — all 37 rows carry `2026-08-14 06:43:38`, the
second the seed ran. The appointment date is `pc_eventDate`, and it spans five dates. Any future
query about *when appointments happen* must read `pc_eventDate`.

### 13.4a `SEED-001` — the re-base and the reset are separate jobs

```
SEED-001 — SCOPE OF THE SEEDED-DATA JOB          ID: SEED-001          STATUS: RULED
Amends: the sequence confirmation inside STEP0-001 (§13.1)
Governs: Challenge 2 (§11.2) and Challenge 3 (§11.3)

RULING (chosen):
[x] SPLIT — the date re-base is scheduled; the database reset stays manual and
       Owner-authorised. The re-base may not be scheduled until it has been
       verified on demo.skyeagle.uk with backup and rollback both exercised on
       that host. The reset may not be scheduled until a clean demo-host
       baseline exists, folds in all post-baseline fixes, and passes restore
       verification.
[ ] ONE JOB, BUILD THE RESET FIRST — port EV-044 to Linux, take and re-bake a
       demo-host baseline, then schedule both together.
[ ] ONE JOB FROM A FRESH DUMP — dump the demo host as-is and schedule both.
       Rejected: freezes whatever is currently wrong on that host into the
       baseline.

RE-BASE POLICY (chosen):
[x] WHOLE-WEEK SHIFT + one-time correction of the seed to the Saudi working
       week. Offset is always a multiple of 7 days, so weekday meaning is
       preserved. Friday and Saturday carry no routine clinic appointments.
       Every seeded clinical date moves by the same offset, preserving
       time-of-day and relative chronology. Recurring-event end dates included.

RATIONALE: The two halves have opposite risk profiles and were bundled on an
       assumption the evidence does not support. The re-base is additive,
       reversible and closes the observed symptom. The reset is destructive,
       has no mechanism or baseline on the demo host, and is evidenced to
       revert fixes (PB-442, facility.primary_business_entity, confirmed still
       0 locally on 2026-08-20). Bundling them imports the reset's risk into
       the safe half and delays the safe half behind work that has not started.
DATE: 2026-08-20     OWNER: mohammedfouly@noursolution.com
ROUTE RECEIVED: Claude Code session, 2026-08-20 — put to the Owner with the
       evidence above; safety conditions added by the Owner in the ruling.
```

**This is a deliberate deviation from §11's "Challenges 2 and 3 are one job", recorded as such.**
The original reasoning — *a reset that restores the frozen dates re-creates Challenge 2 every time it
runs* — is **still correct**, and the split does not discard it. It becomes a **binding condition on
the reset**: whenever the reset is eventually scheduled, the re-base must run inside the same job,
after the restore. Until then there is no reset to bundle with.

**Why whole weeks, in one line of evidence.** Today's computed offset is **0** — the seed already
sits in the current week — yet today is empty. The cause is not the anchor, it is that the seed has
**no Thursday**. So the weekday correction is what closes the symptom now, and the whole-week
re-base is what keeps it closed as weeks pass. Ordering them the other way accomplishes nothing
today.

| Verified 2026-08-20, local instance, in a rolled-back transaction | Result |
|---|---|
| Seven-table shift executes against the live schema | Clean; dates +7, `pc_startTime` untouched at `08:00:00`–`16:30:00`, zero NULLs |
| Weekday correction (Sat −2, Fri −1, both landing on Thursday) | Sun 5 · Mon 7 · Tue 4 · Wed 16 · Thu 4 · **Fri 0 · Sat 0** |
| Offset formula, Sunday-anchored, `FLOOR(/7)*7` | `0` today; idempotent on re-run |
| Rollback | Restored to `2026-08-17`…`2026-08-23`, encounters to `2026-08-19` |

**Prepared, not executed:**
`docs/evidence/ubuntu-infra-scripts/05-seed001-demo-date-rebase.sh` — modes `check` (read-only),
`fix-weekdays` (one-time), `run --dry-run`, `run --yes` (backs up first), `rollback`, `install`.
**`install` refuses** until a real `run` and a real `rollback` have both completed on that host,
which is this ruling's condition expressed as code rather than as a note someone has to remember.

**Two things the split does not close, and they are Owner questions, not agent questions.**
*(i)* The one-time weekday correction must also be applied to the clean baseline when it is created,
or the first reset re-introduces the weekend bookings. *(ii)* Whether the Friday/Saturday rule
extends beyond the appointment calendar to `form_encounter` and the other clinical tables is
unresolved; `check` reports the distribution so it can be ruled on with data.

### 13.5 Sequence

```
0.  Owner ruling (§13.1). No dispatch before it is recorded and signed.
1.  Preflight §6.3 + parity D3a.
2.  M1 fires ALONE and produces the evidence annex. M5 checks its source quality
    and that Observed / Inferred / Unknown are separated.
3.  M0 freezes and hashes the annex.
4.  M2 and M4 receive the IDENTICAL frozen annex and answer independently (R2).
    The known cheap pattern — a scheduled job, a form, on-screen notices, two
    credentials — is labelled INCUMBENT HYPOTHESIS to be attacked. It is NOT
    "the correct output": a classifier or a generator handed the answer in
    writing is not classifying or generating.
5.  M6 threat-models every viable option, BEFORE seeing M2/M4 preferences.
6.  Parity D3b. Quarantine holds until it passes.
7.  Simultaneous publication → M5 claim review → M4 attacks M6.
8.  M0 reports convergence and divergence without choosing. Owner decides.
9.  One-pass meta-review → FINAL.
```

**Deliverable.** `committee/M0-decision-pack-task1.md`.

**Acceptance.** The §12 standard block, plus: Challenge 2 carries an explicit instruction to
re-verify on `demo.skyeagle.uk` **and** to run §13.4's query before scheduling anything · Challenges
2 and 3 are treated as **one** scheduled job · every §13.3 gate item carries a result · the step-0
ruling is cited by ID in the pack.

**What would make this task fail.** Four elegant solutions that each need development. The right
output is boring and cheap. **And the second failure mode, equally real:** producing that boring
cheap output *because it was written down in advance* rather than because it survived attack.

---

## 14. TASK 2 — Audit the advised architecture

**Objective.** Audit the architecture advised in `../06-technical/Architecture.md` §13. Return **approve / deny /
change**, per layer. **Committee: C-2.**

**Firing set.** M6 leads · M4 informs, then attacks · M5 checks claim implications · M0 chairs.
**Idle:** M1, M2, M3, M7.

**Sequence.** M6 audits adversarially, layer by layer → M4 independently answers one question only,
*"does this stack serve the locked IA and the funnel?"* → **M4 then attacks M6's verdicts**, briefed
*"find why this audit is wrong"*, at a model no lower than M6's → M5 checks whether any
architectural choice creates a claim risk, for example analytics or session recording touching a
clinical UI, or a component that would encourage prohibited content → M0 compiles.

**Deliverable.** `committee/M0-decision-pack-task2.md`, carrying M6's per-layer verdicts and M4's
attack on them.

**Acceptance.** The §12 standard block, plus: every layer in §13.2 carries an explicit verdict **and**
a *"what would change my mind"* condition · the audit distinguishes **what is already built and
verified** from **what is proposed** · §13.8's four open questions are addressed, especially
**lead-data residency under PDPL** · §13.6's prohibited-page corrections are confirmed or challenged
with reasoning · M4's attack names at least one substantive weakness in M6's reasoning, or states
with evidence why none was warranted.

**What would make this task fail.** Approving the stack because it is conventional and modern. The
question is not whether Next.js is good; it is whether **this** project's constraints — bilingual
parity, a separately-hosted PHP demo, a locked IA, and a binding claim register — are best served by
it.

---

## 15. Status

**SYSTEM — specified. Not yet exercised. Not yet dispatch-ready, and the gap is now named rather
than asserted.**

| Item | State |
|---|---|
| Roster, committees, rules, briefs | **Complete** — §2, §3, §4, §8 |
| Briefing pack with per-fact basis | **Complete** — §9–§11 |
| Executable preflight, two-stage parity, write-isolation diff | **Specified** — §5.1, §6.3, §6.4. **Not yet run** |
| Competitor source readable in any runtime | **Done** — `.txt` committed beside the `.pdf` |
| Task 1 | **HELD by `SEED-001`** — step-0 is ruled (`STEP0-001`), but the sequence premise changed: the seeding automation now ships in halves and only the re-base is ready. No access design and no credential issuance until the Owner rules whether a scheduled re-base alone fires DEM-001's trigger |
| Challenges 2 and 3 | **RULED `SEED-001`, implementation prepared.** Re-base script written and validated locally; awaiting a `check` run on `demo.skyeagle.uk`, then backup/rollback exercise, then `install`. Reset stays manual |
| Task 2 | **Usable** once a §6.3 preflight passes |
| Single-agent dispatch | **Available for M1–M7**, under §6.2 |
| Agents dispatched to date | **None** |

### 15.1 Open — Owner decisions, nothing else blocks

| # | Decision | Where |
|---|---|---|
| **1** | ~~**The step-0 ruling** — A / B / C / NEITHER, and the sequence confirmation~~ **CLOSED 2026-08-20 — `STEP0-001`, Reading B, sequence CONFIRMED.** Successor decisions, now open: the expiry window; manual vs automatic issuance; the §13.3 residual-risk acceptances | §13.1 |
| **1a** | **Does a scheduled re-base *alone* satisfy DEM-001's "multi-tenancy or seeding automation ships" trigger?** Opened by `SEED-001`, which splits the automation in two and ships only the first half. **Task 1 is held until this is ruled.** | §13.4a |
| **1b** | **When may the reset be scheduled?** Requires a demo-host baseline that does not yet exist, re-baked to include post-baseline fixes, plus restore verification | §13.4a |
| **2** | **Is the review floor relative or absolute?** §5 implements the **relative** reading explicitly — nobody is deliberately under-resourced — and says so, which is honest. The absolute reading (*M5 must be strong enough in itself*) cannot be delivered by any ordering in this document; under it, `R2-hold` is the only real remedy | §5 |
| **3** | **Is the site branded Thiqa or SkyEagle?** Undecided, and it propagates into every page in both languages | §9.1 |
| **4** | **Where does lead-form data land**, under Saudi PDPL? | §13.3 item 11 |
| **5** | **M0's own fallback ladder** — Opus 5 then Sonnet 5 is the Orchestrator's reading, not the Owner's instruction | §5 |

### 15.2 Before the first consequential dispatch

Run a **no-consequence dry dispatch** carrying **seeded defects**, **one simulated silent model
downgrade**, and **one failed agent**. Confirm that M5 finds the seeded defects, that D3b quarantines
the downgraded output, that the §6.4 diff catches a deliberate stray write, that the failure is
recorded as a failure, and that the pack reaches **FINAL** without recursing.

### 15.3 What this file does not do

It **closes no RDY item**, **alters no locked decision**, **authorises no self-serve trial**, and
**enacts no rule change**. It supersedes `../archive/2026-08-20-pre-restructure/MarketingCommittee.md` as the operating instrument; that
file is retained unedited as the audit and dissent trail (R6).
