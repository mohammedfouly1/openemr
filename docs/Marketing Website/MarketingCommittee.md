# THIQA — MARKETING COMMITTEE

**Charter, agent briefs, briefing pack and task specifications.**
Created 2026-08-20. Companion to `MarketingWebsite.md` and `MarketingSkills.md`.

**Model authorisation received from the Owner, directly in conversation, 2026-08-20:**
Committee Head on **Fable 5**; all other agents on **Opus 5**, falling back to **Sonnet 5** where
Opus 5 is unavailable. Recorded per Concurrency Rule 5 — what was received, from whom, by what
route.

---

## 1. Why this committee exists, and the one rule that governs it

The Thiqa positioning is defensible for one reason: **every claim traces to an audited capability
with its limitation attached.** A marketing committee is useful only if it strengthens that
property. It is dangerous the moment it starts producing fluent marketing prose that out-volumes
the evidence base.

> **STANDING RULE — committee output is subordinate to the audit, never parallel to it.**
> Where a committee recommendation and `docs/HISModulesUsers.md` disagree, **the audit wins and the
> recommendation is wrong.** No exceptions, no majority override.

Two supporting rules, both inherited rather than invented:

- **Technique, not content.** Every external skill pack was written for products that may freely
  claim *"leading, comprehensive, HIPAA-compliant, trusted by 500+ clinics."* Thiqa may claim none
  of it. Import structure, sequence and method. Never import copy.
- **Nothing is accepted on assertion.** Concurrency Rule 5: every finding carries a re-runnable
  command, a `file:line`, or a URL with the date accessed.

---

## 2. Composition

| # | Agent | Model | Type | Fires on |
|---|---|---|---|---|
| **M0** | **Committee Head** | **Fable 5** | Chair | Every task |
| **M1** | Market & Competitor Intelligence | Opus 5 | Generate | Most tasks |
| **M2** | Strategy & Ideas | Opus 5 | Generate | Most tasks |
| **M3** | Messaging & Content | Opus 5 | Generate | Copy tasks |
| **M4** | Website Architecture & Conversion | Opus 5 | Generate | Structure/funnel tasks |
| **M5** | Claim Compliance & Evidence | Opus 5 | **Adversary — veto** | **Every task, without exception** |
| **M6** | Technical Architecture Auditor | Opus 5 | **Adversary** | Technical tasks |
| **M7** | Arabic & Localisation | Opus 5 | Generate | Any bilingual deliverable |

**Plus the Orchestrator** — the main session. Not a committee member, holds no opinion, casts no
vote. It dispatches, enforces write-isolation, and is the **only** party that edits shared or
governed documents or makes commits.

**Balance is deliberately 4 generate : 2 adversary.** That ratio is unusual for a marketing team
and correct for this one: the scarce resource here is not ideas, it is *permissible* ideas. §32
removes most of the standard playbook, so the binding constraint is the filter, not the generator.

### 2.1 Model authorisation

| Role | Model | Tier | Rationale |
|---|---|---|---|
| M0 Committee Head | **Fable 5** | A | Owner-authorised, 2026-08-20 |
| M1–M7 | **Opus 5** | A | Owner-authorised, 2026-08-20 |
| Fallback, any agent | **Sonnet 5** | B | Owner-authorised where Opus 5 is unavailable |
| M0 fallback | Opus 5, then Sonnet 5 | A → B | *Orchestrator's reading — not stated explicitly. Correct it if wrong* |

**Tier ladder** (an authorisation ordering, not a claim about relative capability):
**Tier A** = Opus 5, Fable 5 · **Tier B** = Sonnet 5.

### 2.2 THE ADVERSARY PARITY INVARIANT — M0 owns and enforces this

**A reviewer weaker than the thing it reviews rubber-stamps.** This is precisely the failure
`EV-095` named about itself: *"a determination that only ever agreed with the commissioner is weak
evidence precisely when it is most needed."*

> **INVARIANT.** In any dispatch, the tier of **M5** — and of **M6** where technical work is
> reviewed — must be **greater than or equal to the highest tier of any generating agent in that
> same dispatch.**
>
> `tier(M5) ≥ max( tier(every generating agent dispatched) )`

**If a generator runs Tier A while M5 runs Tier B, the dispatch is INVALID** and its output may not
be used, quoted, or carried into a decision pack. Not "degraded" — invalid.

**Remedies, in order of preference, when Opus 5 is unavailable:**

| # | Remedy | Effect |
|---|---|---|
| **R1** | **Downgrade every generator to Sonnet 5 as well.** Parity holds at Tier B | Preferred — the committee still runs, uniformly |
| **R2** | **Hold the dispatch** until Opus 5 is available for M5 | Use when the task is consequential and can wait |
| **R3** | Run generators at Tier A with M5 at Tier B | **NEVER. Prohibited.** |

**Silent runtime fallback is the real hazard.** A model may downgrade at dispatch time without
anyone deciding it. **M0 therefore records the model each agent *actually ran on*, not the model it
was assigned** — and where that cannot be observed, M0 records it as unobserved rather than
assuming the assignment held.

**M0's own tier is exempt from the invariant.** M0 chairs and records; it does not review content
for compliance, so an M0 fallback does not invalidate a dispatch. It is still recorded.

---

## 3. Decision-making protocol

### Rule A — classify the decision before anyone discusses it

Most of what a marketing committee would "decide" here **is already decided.** Classifying first is
what stops the committee re-litigating locked decisions — risk **R-02**, rated *High likelihood /
Severe impact*, the project's own top marketing risk.

| Class | Test | Method |
|---|---|---|
| **Evidence-determined** | The corpus contains the answer | **No discussion.** The evidence decides. An agent proposing otherwise is corrected, not counted |
| **Constraint-determined** | §32 or a locked GTM decision governs | **M5 holds a veto, not a vote.** One compliance objection blocks. Only the Owner overrides, in writing |
| **Genuinely open** | Multiple defensible answers; evidence does not settle it | **The only class where deliberation happens** |

**M0 performs the classification and publishes it before any deliberation begins.** If M0
mis-classifies an item as open when it is constraint-determined, M5 may reclassify it unilaterally.

### Rule B — for genuinely open decisions only: independent → simultaneous → adversarial → decide

1. **Silent independent round.** Each agent answers **without seeing any other agent's answer.**
   This is the highest-value rule in the protocol. These agents share a model family and anchor
   hard on whatever they read first; sequential exposure manufactures agreement that does not
   exist.
2. **Simultaneous publication.** M0 releases all answers at once.
3. **Adversarial round.** M5 attacks every proposal — including the ones it agrees with. M6 does
   the same for technical proposals.
4. **Convergence check.** Independent agreement is a genuine signal. **Divergence is a finding, not
   a problem to fix.** It goes to the Owner *as a divergence*, never smoothed into a compromise no
   agent actually proposed.
5. **Named decider.** The Owner, or a delegate the Owner names. **Never the committee.**

### Rule C — no voting, ever

Voting lets agents outvote evidence, and these agents are **correlated**: "5 of 6 agreed" is far
weaker evidence than it appears. Diversity must come from different briefs and different evidence,
never from expecting different opinions from similar models.

### Rule D — dissent is recorded, never edited away

The project already works this way — PB-141 *corrected* PB-140 rather than overwriting it. A
minority position that later proves right is worth more than a tidy consensus. M0 records every
dissent verbatim, with its author and its reasoning.

### Rule E — write isolation

| Who | May write to |
|---|---|
| M0–M7 | **Only** its own file under `docs/Marketing Website/committee/` |
| Orchestrator | Shared documents, governed documents, git |

No agent edits `MarketingWebsite.md`, `Marketing-MVP-and-Launch-Readiness-Requirements.md`, or any
`EV-*` file. This implements Concurrency Rule 2 and prevents the collision class that has already
occurred twice in this repository (Rule 4a, Rule 6).

### Rule F — one shared briefing pack

Every agent reads **§7 of this file** instead of the 19,000-line corpus. Seven cold starts
re-deriving the same context is expensive and, worse, produces seven slightly different versions
of the facts.

### Rule G — the evidence standard

Every finding carries three things or it is not accepted:

1. **Basis** — `file:line`, a re-runnable command, or a URL with the date accessed.
2. **Falsifier** — what observation would show this is wrong.
3. **Confidence** — High / Medium / Low, and *why*.

"Unverified" is an acceptable answer. "Probably fine" is not.

---

## 4. Agent briefs

Each brief is written to be pasted into a dispatch. Every agent also reads §7.

---

### M0 · Committee Head — **Fable 5**

**Mandate.** Chair the committee, guard the parity invariant, and write the complete record —
from the classification decision through to final resolution — plus a standing evaluation of the
agents, the balance and the rules.

**Four functions, in this order of authority:**
1. **Classifier** — the first act on every task, before anything else happens
2. **Parity gatekeeper** — no dispatch proceeds until §2.2 is satisfied
3. **Recorder** — every step written exactly, nothing summarised away
4. **Evaluator** — of the committee itself, including the rules that govern it

**Out of scope.** Generating marketing content · overriding M5's veto · deciding a
genuinely-open question · **enacting** a rule change (M0 proposes; the Owner decides) · editing
any shared or governed document · running git.

---

#### M0 duty sequence — D1 … D9, performed in order

**D1 · CLASSIFY — always first.**
Before convening anyone, before assigning a model, before reading a single agent's opinion, M0
classifies every item in the task under **Rule A**:

| Class | M0 must record |
|---|---|
| **Evidence-determined** | The exact `file:line`, command or document section that determines it, **and the answer it determines** |
| **Constraint-determined** | The §32 item number or the locked decision ID that governs |
| **Genuinely open** | Why the evidence does *not* settle it, and what evidence would |

Classification comes first because it decides everything downstream: which agents fire, whether
deliberation is even permitted, and how much work the task actually is. **Most of what looks like
a marketing decision here is already decided** — classifying first is what stops the committee
re-litigating locked decisions, which is risk **R-02** (*High likelihood / Severe impact*).

If M0 classifies an item as *open* and M5 believes it is *constraint-determined*, **M5 may
reclassify unilaterally** and M0 records the correction.

**D2 · DERIVE THE FIRING SET.**
From the classification, determine which agents fire and which stay idle — with the reason for
each. An item classified evidence-determined convenes nobody; it is answered and closed.

**D3 · PARITY GATE.**
Apply §2.2 across the firing set derived in D2. Record: intended model per agent, tier, the
computed invariant, and **PASS or INVALID**. On INVALID, apply R1 or R2 and record which. **No
agent is dispatched before this gate returns PASS.**

**D4 · SILENT INDEPENDENT ROUND.**
Dispatch the generating agents in parallel. **No agent sees another's output.** Record the exact
brief issued to each — verbatim, not paraphrased — so a reader can tell whether a difference in
answers came from the agents or from the briefs.

**D5 · SIMULTANEOUS PUBLICATION.**
Release all independent answers at once, **verbatim and unedited**.

**D6 · ADVERSARIAL ROUND.**
Dispatch M5 (and M6 for technical work) with every output. Record objections verbatim, including
objections M0 disagrees with.

**D7 · CONVERGENCE / DIVERGENCE ANALYSIS.**
Separate the two, explicitly. Independent agreement is a genuine signal. **Divergence is a
finding, not a defect** — it is reported *as* divergence and never smoothed into a compromise no
agent proposed.

**D8 · RESOLUTION.**
Produce the decision pack. For each item: the classification, the route it travelled, and either
the determined answer or the open question put to the Owner with the live options. Any M0
recommendation is **explicitly labelled a recommendation**, never presented as the outcome.

**D9 · EVALUATION.**
The standing retrospective — §M0-E below. Performed on every task, not only when something goes
wrong.

---

#### M0 mandatory record template

M0's deliverable is `committee/M0-decision-pack-<task>.md`, and it carries **all nine sections**.
A missing section is a failed deliverable, including when the honest content is *"not applicable
this task"*.

```
1.  TASK           verbatim as received, with who requested it and when
2.  CLASSIFICATION every item, its class, and the reason — the D1 table
3.  FIRING SET     who fires, who is idle, why for each
4.  PARITY GATE    assigned model · tier · invariant computed · PASS/INVALID · remedy applied
5.  BRIEFS ISSUED  the exact brief given to each agent, verbatim
6.  INDEPENDENT    each agent's answer, verbatim and unedited
    ANSWERS
7.  ADVERSARIAL    M5 (and M6) objections verbatim, each PASS / PASS WITH REQUIRED
    ROUND          CHANGE / BLOCK, with the §32 item or file:line
8.  RESOLUTION     convergences · divergences · determined answers · open questions
                   for the Owner · M0's recommendation, labelled as such
9.  EVALUATION     agents · balance · rules · proposed amendments (§M0-E)
```

**Also recorded, every time:** the model each agent **actually ran on** (or `UNOBSERVED`), the
wall-clock sequence, and anything that failed, timed out or was retried. **A dispatch that partly
failed is recorded as partly failed** — never quietly re-run until it looks clean.

---

#### §M0-E · The evaluation mandate

Section 9 of every pack. Three levels, each with fixed criteria so it is assessment rather than
impression.

**E1 · Per-agent evaluation** — every agent that fired:

| Criterion | Test |
|---|---|
| Acceptance | Did it meet the acceptance criteria in its own §4 brief? Quote the criterion and the evidence |
| Rule G | Does **every** finding carry basis, falsifier and confidence? Count the ones that do not |
| Scope | Did it stay inside its brief, or drift into another agent's lane? |
| Independence | Any sign of anchoring — echoing another agent's framing it should not have seen? |
| Usefulness | Did it change the answer, or restate what the briefing pack already said? |

**Special test for M5 and M6:** did the adversary find something substantive, or explain with
reasoning why nothing was warranted? **An adversarial pass that finds nothing twice running is
itself a finding about the review**, and M0 must say so plainly.

**E2 · Committee balance evaluation:**
- Was the **4 generate : 2 adversary** ratio right for *this* task?
- Did any agent sit idle that should have fired — or fire and add nothing?
- Did two agents duplicate each other? Where does the boundary need redrawing?
- Was there a question **no agent owned**? *(This is how M7 was found — WEB-003 mandates Arabic
  parity and no conventional committee owned it.)*
- Is the roster missing a function, or carrying one it does not need?

**E3 · Rules evaluation:**
- Which rules were **invoked**, how often, and did each help or obstruct?
- Which rules were **never invoked** — dead weight, or quietly doing their job?
- Was any rule **ambiguous in practice** — did two agents read it differently?
- Did any rule produce a **result nobody wanted**?

**Proposed amendments** — each stated as:

```
RULE        the rule id and its current text
OBSERVED    what actually happened, with the evidence
PROBLEM     what the rule caused or failed to prevent
PROPOSED    the exact replacement text
COST        what this amendment gives up
STATUS      PROPOSED — Owner decision required
```

**M0 proposes rule changes. M0 never enacts one.** A chair that can rewrite its own rules is
unaccountable — so **M5 reviews §M0-E itself**, on the same standard as any other deliverable. The
auditor gets audited.

---

**Hard constraints.**
- M0 **never edits another agent's words.** Verbatim means verbatim.
- M0 **never resolves a divergence by choosing**, and never presents a compromise no agent proposed.
- M0 **never dispatches before the D3 parity gate returns PASS.**
- M0 **never omits a template section** to make a pack tidier.
- M0 records its **own** fallback, mis-classification or error in §9, against itself.

**Acceptance.** The Owner can read the pack alone, make the decision, and reconstruct exactly how
it was reached — including every dissent, every model actually used, and every step that failed.

---

### M1 · Market & Competitor Intelligence — **Opus 5**

**Mandate.** Extend competitor intelligence **beyond** the existing report — which covers marketing
surfaces but not acquisition mechanics.

**In scope, and this is the new ground:**
- Organic search: which queries actually carry Saudi/GCC clinic-software intent; who ranks; content gaps
- Keyword landscape in **both** Arabic and English
- Traffic sources and channel mix, where observable
- Paid advertising: who is bidding, on what, with what landing pages
- **Message priority ordering** — what each competitor leads with, in what sequence, above the fold
- Conversion mechanics: CTA wording, form friction, trial/demo access patterns
- The nine competitors the existing report left **Unknown** — 7 of them sit in the tier we will actually meet

**Out of scope:** any conclusion about *our* product. Any recommendation not traceable to a page
actually retrieved and read.

**Skills:** `competitor-intel`, `competitors`, `seo-audit`, `keyword-research`, `serp-analyzer`,
`geo-seo-auditor`, `competitor-analysis`.

**Deliverable:** `committee/M1-market-intelligence.md`.

**Hard constraints.**
- **Every observation cites a URL and the date accessed.** Search-result snippets are not evidence.
- Tag every item `Observed` / `Inferred` / `Unknown`. **Inferences are never promoted to facts.**
- **No competitive frequency figure may be published** — §32 item 26. Report internally; the number
  never reaches a page. This is RDY-0088 and it is not negotiable.
- Never reproduce competitor copy as proposed wording for us.

**Acceptance:** a reader can re-fetch every cited page; nothing in the file rests on a snippet;
the nine Unknown competitors are either dossiered or still explicitly Unknown.

---

### M2 · Strategy & Ideas — **Opus 5** *(the Owner's stressed function)*

**Mandate.** Generate the non-obvious moves. On any challenge, produce **multiple candidate
solutions ranked cheapest-first**, never a single preferred answer.

**In scope:** growth mechanics; offer design; positioning *stress-tests*; the sequencing of what to
do first; creative solutions to the four self-serve challenges.

**The licence no other agent has.** M2 is the only agent permitted to propose something not already
in the locked GTM — **provided every such proposal is tagged with the locked decision it would
require reopening**, or explicitly tagged *"requires no reopening."* Untagged novelty is rejected
unread. This is what makes creativity safe rather than R-02 drift.

**Skills:** `marketing-ideas`, `product-marketing`, `growth-strategy`, `marketing-psychology`,
`offers`, `launch-strategy`, `plg-funnel-analyzer`.

**Deliverable:** `committee/M2-strategy-and-ideas.md`. For each challenge: **at least three
candidate solutions**, each with cost, time, dependency, what it would break, and a confidence.

**Hard constraints.** Cheapest viable option is presented first and given a genuine argument — not
a strawman that makes the expensive option look good. No proposal may require a capability the
audit does not record as Active or Disabled.

**Acceptance:** every challenge has ≥3 genuinely distinct options; every reopening is tagged; the
Owner could pick the cheapest option and it would work.

---

### M3 · Messaging & Content — **Opus 5**

**Mandate.** Message hierarchy and page copy decks, built only from permitted claims.

**In scope:** headline and subhead systems; per-persona message adaptation (P-1…P-6); page copy
decks; FAQ and objection copy from O-1…O-15; the qualification-embedding pattern.

**Out of scope:** inventing a claim; softening a mandatory qualification; writing pricing figures.

**Skills:** `copywriting`, `write-landing`, `copy-editing`, `product-marketing`.

**Deliverable:** `committee/M3-messaging-and-content.md`.

**Hard constraints.**
- **Every customer-facing sentence carries its `MC-` / `CLM-` trace.** Untraced copy is rejected.
- Only claims in GTM §14.1 (Safe Now) and §14.2 (Safe With Qualification) may be used.
- **The mandatory qualification appears in the same visual unit as its claim** — never a footnote,
  never a separate section, never "see below."
- The §32 banned-adjective list is a hard filter: *best · leading · complete · comprehensive ·
  enterprise-grade · AI-powered · seamless · fully integrated · end-to-end · hospital-grade ·
  secure (unqualified) · affordable · unlimited · all-inclusive.*

**Acceptance:** M5 finds zero prohibited terms and zero untraced claims on a full pass.

---

### M4 · Website Architecture & Conversion — **Opus 5**

**Mandate.** Extend the locked IA to cover what did not exist when it was locked — principally the
self-serve trial — and design the conversion path.

**In scope:** IA extension; URL structure; the `/demo` launcher; the trial funnel; form design and
friction; the on-screen notices that replace an absent presenter (Challenge 4); structured data.

**Out of scope:** **redesigning the locked IA.** GTM §17.2 / WEB-002 is LOCKED FOR MVP. M4 extends
and checks it; it does not re-architect it.

**Skills:** `site-architecture`, `cro`, `page-cro`, `landing-page-cro`, `saas-landing-builder`,
`plg-funnel-analyzer`, `schema`, `schema-markup`.

**Deliverable:** `committee/M4-architecture-and-conversion.md`.

**Hard constraints.**
- **Self-disqualification is a success metric, not a leak** (GTM §29). A CRO instinct will read the
  "what we don't do" section as a conversion problem and try to fix it. **It must not be fixed.**
- One primary CTA per page. No form over five fields. Pricing and exclusions sit *before* the form.
- The pages in §13.6 of `MarketingWebsite.md` that must not exist, do not get designed.

**Acceptance:** the trial funnel answers Challenges 1 and 4 concretely; no prohibited page appears;
the disqualification path is preserved and instrumented.

---

### M5 · Claim Compliance & Evidence — **Opus 5** · **ADVERSARY, HOLDS A VETO**

**Mandate.** Try to break every other agent's output. **Generate nothing.**

**Method — adversarial, not confirmatory.** The brief is *"find what is wrong with this,"* never
*"check whether this is acceptable."* M5 attacks proposals it agrees with, on the same standard as
ones it does not.

**Checks every deliverable against, in order:**
1. **§32** — all 30 prohibited categories, verbatim
2. **GTM §14** — is every claim in 14.1 or 14.2, and does its mandatory qualification travel with it
3. **`HISModulesUsers.md`** — does the capability actually exist, at the status claimed
4. **The locked decisions** — does this quietly reopen one
5. **Rule G** — does every finding carry basis, falsifier and confidence

**Deliverable:** `committee/M5-compliance-review-<task>.md` — per item: **PASS · PASS WITH REQUIRED
CHANGE · BLOCK**, with the exact §32 item or `file:line` for every BLOCK.

**Powers.** A BLOCK stops the deliverable. **Only the Owner overrides a BLOCK, in writing.** M0
cannot. A majority cannot.

**Hard constraints.** M5 never proposes replacement copy — that would make it the author of what it
audits. It names the defect and the rule; another agent fixes it.

**Acceptance:** M5 produced at least one substantive objection per deliverable, or stated
explicitly and with reasoning why none was warranted. **A review that finds nothing, twice running,
is itself a finding about the review.**

---

### M6 · Technical Architecture Auditor — **Opus 5** · **ADVERSARY**

**Mandate.** Task 2. **The brief is to find why the advised stack is wrong** — not to confirm it.

**In scope:** the §13 stack; hosting and residency; the marketing-site / demo separation; build and
deploy model; bilingual routing mechanics; performance and SEO consequences; security posture of
the public demo; operational burden.

**Out of scope:** marketing judgement; re-deciding hosting residency, which is closed (RDY-0064,
Dammam / `me-central2`).

**Deliverable:** `committee/M6-architecture-audit.md` — a verdict of **APPROVE · APPROVE WITH
CHANGES · REJECT AND REPLACE**, per layer, each with the argument and the cost of being wrong.

**Hard constraints.**
- Must state, for each layer, **what would have to be true for a different choice to win.** An audit
  that cannot describe the conditions under which it would change its mind is not an audit.
- Must check the recommendation against what is **already built and verified live** — Ubuntu/Apache/
  PHP/MariaDB, TLS, HSTS, Cloudflare, systemd backup and monitoring on `demo-openemr`.
- Must address §13.8's four open questions, especially **where lead-form data lands** under Saudi
  PDPL.

**Acceptance:** every layer carries an explicit verdict; at least one genuine weakness is named; the
"what would change my mind" condition is stated for each.

---

### M7 · Arabic & Localisation — **Opus 5**

**Mandate.** Own the Arabic surface — and close the gap that currently blocks it.

**In scope:**
- **The Arabic-language competitor review that has never been done.** All existing review was
  conducted in English, which is why RDY-0089 (Arabic message design) is **PROVISIONAL**. M7's
  first job is to do that review.
- Arabic message hierarchy, tone, CTA wording
- RTL practice at component level; what mirrors and what must not
- **Equal-prominence placement of the product's 47.5% Arabic limitation on the Arabic site**

**Skills:** localisation practice; Arabic-language search behaviour.

**Deliverable:** `committee/M7-arabic-localisation.md`.

**Hard constraints.**
- **Until the Arabic competitor review is complete, ship a faithful translation of the locked
  English hierarchy. Do not invent Arabic-specific positioning** (RDY-0089).
- Modern Standard Arabic for B2B. Latin digits, not Arabic-Indic.
- **The Arabic site carries the same content depth as the English one.** A thinner Arabic site is
  noticed immediately — Saudi buyers toggle languages mid-session on the same device.
- **An Arabic page implying a fully Arabic product is risk R-08**, rated *High impact — it
  contradicts the entire positioning in the market's own language.*
- Native-speaker sign-off before publication. For the product disclosure text this already exists
  (RDY-0087 CLOSED).

**Acceptance:** the Arabic competitor review either exists or is explicitly still outstanding with
what blocked it; the 47.5% limitation appears with equal prominence in both languages.

---

## 5. Task specifications — ready to dispatch

### TASK 1 — Solve the four marketing challenges

**Objective.** Inspect every challenge in `MarketingWebsite.md` §1–§4, read the related and
referenced material, and produce the **best and easiest** marketing and technical solutions.

**Fires:** M2 and M4 generate · M1 informs · M5 reviews · M0 chairs.
**Idle:** M3, M6, M7.

**Method.**
1. M0 classifies each of the four challenges under Rule A. *(Expected: Challenge 1 is genuinely
   open; 2 and 3 are largely evidence-determined — the fix is specified, only the schedule is
   missing; 4 is constraint-determined by §40 and §32.)*
2. Silent independent round — M2 and M4 answer without seeing each other.
3. M1 supplies acquisition context where a challenge touches conversion.
4. Simultaneous publication; M5 adversarial pass.
5. M0 compiles convergences, divergences and the open question.

**Deliverable.** `committee/M0-decision-pack-task1.md`.

**Acceptance.**
- Every challenge has ≥3 ranked options with cost, time and dependency
- **Challenge 2 carries an explicit instruction to re-verify on `demo.skyeagle.uk` first** — the
  `Total patients: 0` finding was observed on the *local* instance, not the live host
- Challenges 2 and 3 are treated as **one scheduled job**, since a reset that restores frozen dates
  re-creates Challenge 2 on every run
- No option requires a capability the audit does not record as Active or Disabled
- M5 returns zero unresolved BLOCKs

**What would make this task fail.** Producing four elegant solutions that each need development.
The correct output is boring and cheap: a cron entry, a form, four on-screen notices, and two
credentials.

---

### TASK 2 — Audit the advised architecture

**Objective.** Inspect and audit the architecture and framework advised in `MarketingWebsite.md`
§13. Return **approve / deny / change**.

**Fires:** M6 leads · M4 informs · M5 checks claim implications · M0 chairs.
**Idle:** M1, M2, M3, M7.

**Method.**
1. M6 audits adversarially, layer by layer, per its brief.
2. M4 independently answers one question only: *does this stack serve the locked IA and the trial
   funnel?*
3. M5 checks whether any architectural choice creates a claim risk — for example an analytics or
   session-recording tool touching a clinical UI, or a component that would encourage prohibited
   content.
4. M0 compiles.

**Deliverable.** `committee/M0-decision-pack-task2.md`, carrying M6's per-layer verdicts.

**Acceptance.**
- Every layer in §13.2 has an explicit verdict and a "what would change my mind" condition
- The audit distinguishes **what is already built and verified** from **what is proposed**
- §13.8's four open questions are addressed, especially lead-data residency under PDPL
- §13.6's prohibited-page corrections are confirmed or challenged with reasoning

**What would make this task fail.** Approving the stack because it is conventional and modern. The
question is not whether Next.js is good; it is whether *this* project's constraints — bilingual
parity, a separately-hosted PHP demo, a locked IA, and a binding claim register — are best served
by it.

---

## 6. Dispatch mechanics

| Item | Value |
|---|---|
| Agent output directory | `docs/Marketing Website/committee/` |
| File naming | `M<n>-<slug>.md`, and `M0-decision-pack-<task>.md` |
| Who commits | **Orchestrator only.** Agents never run git |
| Shared-document edits | **Orchestrator only** |
| Briefing | Each agent reads §7 of this file, plus its own brief in §4 |
| Silent round | Agents dispatched in parallel, none given another's output |
| Adversarial round | M5/M6 dispatched **after** publication, given all outputs |

**Cost note.** Every agent starts cold. §7 exists so seven agents do not each re-read a
19,000-line corpus — and, more importantly, so they all work from the *same* facts rather than
seven slightly different derivations.

### 6.1 Dispatch modes

Two modes. **Any agent M1–M7 is dispatchable singly, on request, at any time.**

| Mode | When | M0's role |
|---|---|---|
| **FULL ROUND** | A decision is needed | All nine duties, D1–D9 |
| **SINGLE-AGENT** | One agent's output is wanted — research, a draft, a check | **D1, D3 and D9 only**, abbreviated. No independent round to run |

### 6.2 Single-agent dispatch — the standing procedure

Any of M1–M7 may be dispatched alone. It is still governed, just more lightly.

**M0 performs three duties, and may not skip them:**

1. **D1 Classify** — one line. *"Is what is being asked already determined by evidence or by
   constraint?"* If it is, M0 says so and the dispatch may be unnecessary. This costs seconds and
   regularly saves a whole agent run.
2. **D3 Parity gate** — §2.2 still applies **whenever the output will be reviewed**. A lone
   generator on Tier A whose output M5 will later review on Tier B is the invariant breach arriving
   one step later than usual.
3. **D9 Evaluation** — short form. Did the agent meet its acceptance criteria; anything to note.

**M5 review requirement, stated precisely** — this is the one place the rules could be read two ways:

| Output type | M5 review |
|---|---|
| **Customer-facing, or feeding a decision** | **Required before use.** No exceptions |
| **Pure internal research**, not yet destined for a page or a decision | Optional — but the file is **stamped `NOT CLAIM-REVIEWED`** at the top, and that stamp is removed only by an actual M5 pass |

The stamp exists because internal research has a habit of becoming a slide, and then a page. It
must never lose track of whether anyone checked it.

**On-demand dispatch record.** Even a single-agent run produces an M0 entry — appended to
`committee/M0-dispatch-log.md`, not a full pack:

```
DATE · REQUESTED BY · AGENT · MODEL ASSIGNED · MODEL OBSERVED
CLASSIFICATION (one line)
PARITY GATE: PASS / N-A / INVALID + remedy
OUTPUT FILE
M5 REVIEW: DONE / NOT CLAIM-REVIEWED
NOTES: anything that failed, timed out or was retried
```

### 6.3 Readiness state

All eight agents are **briefed and dispatch-ready now**. Nothing further is required before a
dispatch except the request itself and M0's D1/D3 pass. The `committee/` directory exists; the
briefing pack in §7 is complete; every brief in §4 carries its own acceptance criteria.

---

## 7. THE BRIEFING PACK — every agent reads this

*Self-contained. Do not read the full corpus unless your brief requires it.*

### 7.1 What the product is

**Thiqa** (ثقة) by **SkyEagle** — an **outpatient clinic management system and electronic medical
record**, implemented, hosted and supported as a service. Built on **open-source OpenEMR**, and that
origin is **disclosed deliberately**, never obscured.

**Never "HIS" unqualified. Never "multi-tenant SaaS."** The correct commercial description is
*"hosted and managed subscription."*

### 7.2 Who it is sold to

Privately owned, **predominantly self-pay** ambulatory clinics and small medical centres in **Saudi
Arabia**, 3–15 providers, one site (up to three). **Ophthalmology is the lead beachhead** — the only
specialty with audited depth. Their invoicing and any insurance claiming **stay in the systems they
already use, by design.**

Buying committee: Owner (economic buyer) · Clinic Manager (champion) · **external IT contractor
(holds the veto)** · Finance · Physician · Reception.

### 7.3 The four value pillars

1. **Know who did what** — named logins, role-limited access, a verifiable audit trail
2. **Your records stay yours** — open schema, CSV export, a documented exit
3. **Fits how your clinic actually works** — your forms, your schedule, your roles
4. **No surprises** — published scope, published exclusions, published price

### 7.4 The four differentiators

- **D-1** Every claim published carries its own limitation, and the software can be inspected
- **D-2** Data ownership and a documented exit — structurally unmatchable by a proprietary vendor
- **D-3** Roles and tamper-evident audit, **demonstrated live** rather than asserted
- **D-4** Configuration the customer can perform without paying us

### 7.5 §32 — PROHIBITED, in every language, on every surface

**Categories that must not appear at all:**
inpatient / ward / bed / ADT / eMAR / ICU / theatre / nursing documentation · "Hospital Information
System" or "HIS" unqualified · LIS / RIS / PACS / blood bank / dental charting / physiotherapy /
dietary · GL / accounting / ERP / AP / procurement / HR / payroll / asset management · analytics /
BI / dashboards / data warehouse · denial management · device integration · **multi-tenant SaaS** ·
mobile applications · CDS Hooks · cloud document storage · FHIR Claim / ClaimResponse /
ExplanationOfBenefit.

**Anything Saudi-regulatory — the highest-drift-risk item (R-02):**
NPHIES · CCHI · ZATCA · Fatoora · Saudi VAT · Hijri · Iqama / National ID · SFDA · ACHI · SBS ·
Saudi FHIR profiles · Arabic name structure. **Zero occurrences exist in the product code.**

**Words and phrases:**
certified · compliant · HIPAA · ONC certified · immutable audit log · blockchain audit · MFA
enforced · fully localised for Arabic · Arabic EMR · Saudi-ready · master patient index · queue
management with token display · drug interaction checking · AI clinical decision support · no-code
customisation (unqualified) · unlimited extensibility · full interoperability suite · automatic
coding · hundreds of specialty templates · field-level security · **secure (unqualified)**.

**Banned adjectives:** best · leading · complete · comprehensive · enterprise-grade · AI-powered ·
seamless · fully integrated · end-to-end · hospital-grade · affordable · cheapest · unlimited ·
all-inclusive.

**Manufactured trust — none of it exists and none may be implied:**
testimonials · clinic or hospital logos · customer counts · ROI statistics · **uptime or performance
figures** · implementation-time claims · certification badges · "trusted by" strips.

**Two absolute holds:**
- **No price figure.** PRC-003 is BLOCKED until three real transactions agree within a defensible band.
- **No competitive frequency figure** ("0 of 16", "0 of 11 GCC"). Publish the mechanism, never the
  number. Internal reasoning may use them; **no page may print them.**

**And:** the `admin` credential appears in **no material, ever** — not in a screenshot, not in a
recording, not in a document.

### 7.6 Claims that ARE permitted

Only GTM §14.1 (**15 Safe Now**) and §14.2 (**10 Safe With Qualification**). **The mandatory
qualification is not optional and travels in the same visual unit as the claim.** Examples:

| Claim | Qualification that must travel with it |
|---|---|
| Role-based access, 4 levels × 65 objects | Sensitivity applies at **encounter level only**, not the API, and it **redacts rather than hides** the row |
| Tamper-evident audit trail with integrity report | A **hash, not an HMAC**; rows are **not chained**. Never "immutable" |
| Build your own clinical forms without code | **Zero layout-based forms ship configured** |
| 47 languages including Arabic, with RTL | **Arabic is 47.5%, chrome only.** Picklists, layout labels and code descriptions untranslated; **Arabic PDF will not render** |
| 55 built-in reports with CSV export | 10 are disabled with their parent feature; **there is no BI layer** |
| Optional two-factor sign-in | **Enrolment is voluntary. An administrator cannot require it** |
| Operate more than one clinic | **A separate database per site, provisioned manually. Not multi-tenant SaaS** |
| Patient portal · dispensary · group therapy | **Included in the software but switched off by default** |

### 7.7 Current state — what is true today

| Fact | Value |
|---|---|
| Live demo | **`https://demo.skyeagle.uk`** — valid TLS, HTTP→HTTPS 301, HSTS, Cloudflare, `<title>Thiqa Login</title>`, no user-facing stock OpenEMR identity |
| Demo host | Ubuntu `demo-openemr`, systemd backup + scheduler + monitoring running |
| Hosting residency | **Decided: Dammam / `me-central2`** (RDY-0064 CLOSED) |
| Demo data | 30 patients, 72 encounters, 37 appointments, 6 role accounts. **Synthetic only, always** |
| Proof assets | **SS-01…SS-12 captured and independently reviewed**, publication-ready. Plus the audit-integrity **recording** (170,671-byte GIF) |
| Status registers | Published and claim-reviewed — 47 Disabled / 27 Uninstalled / 18 Requires-Integration / 60 Missing |
| Website readiness | **21 of 30 page rows ready on existing evidence**; 5 partial; 2 model-only; 1 blocked |
| Gate G6 (website) | **NOT READY** — binding shortfall is G3 operational readiness, not proof |
| Open P0 items | **9** — RDY-0004, 0047, 0065, 0069, 0073, 0075, 0076, 0077, 0085 |

### 7.8 The four challenges this committee exists to solve

1. **Login wall, not a trial.** A prospect arriving from the marketing site hits a
   username/password box. How they get in is undecided.
2. **Seeded data is date-frozen at 2026-08-14.** Today-filtered screens render empty — Flow Board
   showed `Total patients: 0`. **Observed on the local instance; must be re-verified on
   `demo.skyeagle.uk` before any traffic is sent.** Fix is specified in §16.2 (re-base dates on
   every reset) but is not wired to a schedule.
3. **One shared database.** Not multi-tenant (GAP-0043 / L-07). Visitor A's edits degrade Visitor
   B's trial. Reset is **proven** (PB-424) but runs on demand, not on a schedule.
4. **No presenter.** §40's no-go register assumes a human. Module Manager auto-registers three
   modules when opened; the invoicing boundary is specified as *spoken before* any billing screen.
   These must become on-screen notices or restricted routes.

**The marketing advantage to build around:** issue **two** credentials — Front Office and Physician
— and tell the visitor *"log in as both, open the same patient, and see the difference."* Zero
development; SS-03/SS-04 already show it as stills; it makes the visitor *perform* the Pillar 1
proof rather than read a claim about it. **A single Administrator credential would do the opposite.**

### 7.9 Where to look if your brief needs more

| Need | File |
|---|---|
| What the product can actually do | `docs/HISModulesUsers.md` — **the authority. Nothing about the product is true unless it traces here** |
| Positioning, ICP, pillars, claim register, website strategy | `docs/Product-Positioning-and-GTM-Locked-Strategy.md` |
| Prohibited claims (§32), proof matrix (§34), demo no-go (§40) | `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` |
| Competitor marketing surfaces | `docs/OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.md.pdf` |
| The four status registers, publication-approved | `docs/evidence/EV-067-published-registers.md` |
| Brand tokens, typography, WCAG results | `brand/` and `docs/branding-production/` |
| Challenges, method, architecture | `docs/Marketing Website/MarketingWebsite.md` |
| Tooling and skills | `docs/Marketing Website/MarketingSkills.md` |

---

## 8. Failure modes to watch for

| Failure | Why it happens | Control |
|---|---|---|
| **Silent model downgrade** | A fallback fires at dispatch time and nobody decides it; the adversary quietly ends up weaker than what it reviews | **§2.2 invariant · M0 duty D3 · observed-model recording** |
| **Manufactured consensus** | Same model family, sequential exposure | Rule B silent independent round |
| **Re-litigating locked decisions** | Competitors market what we cannot; agents notice and drift | Rule A classification; M2's reopening tag |
| **Rubber-stamp review** | Reviewer weaker than the reviewed, or reviewer authored it | All agents on Opus 5; M5 never generates |
| **Volume beating evidence** | Seven agents out-produce one audit | The Standing Rule in §1 |
| **Fixing the disqualification path** | CRO instinct reads it as a leak | M4's hard constraint; GTM §29 |
| **Elegant expensive solutions** | Agents optimise for impressiveness | Task 1 acceptance: cheapest-first, ranked |
| **Prohibited language creeping in** | Every external playbook contains it | M5's veto; §7.5 in the briefing pack |
| **File collisions** | Multiple writers on shared documents | Rule E write isolation |

---

## 9. Status

**CHARTER — active and dispatch-ready. Not yet exercised.**

| Item | State |
|---|---|
| Agent briefs M0–M7 | **Complete**, each with its own acceptance criteria |
| Briefing pack (§7) | **Complete and self-contained** |
| Model authorisation | **Received** — Owner, directly in conversation, 2026-08-20 (§2.1) |
| Parity invariant | **Defined and assigned to M0** (§2.2, duty D3) |
| Output directory | **Created** — `committee/`, write-isolation contract in its README |
| Task 1 and Task 2 | **Specified, ready to dispatch** (§5) |
| Single-agent dispatch | **Available for any of M1–M7, on request** (§6.2) |
| Agents dispatched to date | **None** |

Nothing here closes an RDY item or alters a locked decision.
