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

**Skills — OPTIONAL, none installed. The brief above is self-sufficient without them:**
`competitor-intel`, `competitors`, `seo-audit`, `geo-seo-auditor`, `competitor-analysis`.
*(`keyword-research` and `serp-analyzer` were removed 2026-08-20 — they exist in none of the three
packs assessed in `MarketingSkills.md` §3.1–§3.3, and were assigned in error.)*

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

**Skills — OPTIONAL, none installed. The brief above is self-sufficient without them:**
`marketing-ideas`, `product-marketing`, `marketing-psychology`, `offers`, `launch-strategy`,
`plg-funnel-analyzer`.
*(`growth-strategy` was removed 2026-08-20 — it exists in none of the three packs assessed in
`MarketingSkills.md` §3.1–§3.3. The nearest real item is `growth-strategist`, an **orchestrator
agent** in `ekinciio`, not a skill.)*

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

**Skills — OPTIONAL, none installed. The brief above is self-sufficient without them:**
`copywriting`, `write-landing`, `copy-editing`, `product-marketing`. *(All four verified present in
the assessed packs, `MarketingSkills.md` §3.1/§3.3.)*

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

**Skills — OPTIONAL, none installed. The brief above is self-sufficient without them:**
`site-architecture`, `cro`, `page-cro`, `landing-page-cro`, `saas-landing-builder`,
`plg-funnel-analyzer`, `schema`, `schema-markup`. *(All eight verified present in the assessed
packs, `MarketingSkills.md` §3.1–§3.3.)*

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

**Skills — none. No installable skill exists for this lane**, in this environment or in any of the
three packs assessed in `MarketingSkills.md` §3. M7 works from localisation practice and
Arabic-language search behaviour as *bodies of knowledge* (`MarketingWebsite.md` §11.3), not from
tooling. Stated plainly so the absence is not read as an omission.

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
| Competitor marketing surfaces | **`docs/OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.txt`** — read this. 59 pages, 3,480 lines, UTF-8. The `.md.pdf` beside it remains the authoritative original; the `.txt` exists because the PDF's subset fonts defeat text extraction in some runtimes (§11.3 F-11). Re-derive with `pdftotext -layout -enc UTF-8 <pdf> <txt>` — verified byte-identical on re-run, 2026-08-20 |
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

---

## 10. Independent strict review and audit — 2026-08-20

### 10.1 Scope and verdict

This audit reviewed this charter against `MarketingSkills.md`, `MarketingWebsite.md`, the locked
GTM strategy, all 59 pages of the competitor-intelligence PDF, `HISModulesUsers.md`, and the
directly governing readiness/evidence material referenced here, principally
`Marketing-MVP-and-Launch-Readiness-Requirements.md` and
`EV-034-website-proof-readiness-resync-20260819.md`. It tested currency, traceability, internal
consistency, authority, feasibility, independence, closure and privacy/security ownership. It did
not reopen a decision, close an RDY item, validate an unavailable model or retest the live demo.

> **VERDICT: SOUND INTENT, NOT OPERATIONALLY READY.**

Evidence-first governance, preserved dissent, write isolation, Owner-only decision authority and
same-unit qualifications are strong. The current charter nevertheless fails its own
`dispatch-ready` claim. No customer-facing or decision-feeding output should be relied on until
the four P0 findings are resolved or explicitly accepted by the Owner in writing.

### 10.2 Findings register

| ID | Severity | Finding and evidence | Required correction | Falsifier | Confidence |
|---|---|---|---|---|---|
| **AUD-COM-01** | **P0 — BLOCK** | **The self-serve premise is treated as authorised.** `MarketingWebsite.md` §0 says an unattended trial **reverses** the locked `DEM-001` deferral. Task 1 proceeds to implementation and pre-declares the answer without first requiring an Owner ruling. M2's reopening tag does not authorise the premise. | Before Task 1, M0 must classify the premise, cite `DEM-001`, and obtain the Owner's written `REOPEN / DO NOT REOPEN` ruling. On `DO NOT REOPEN`, use a guided/requested demo. | Produce a prior dated Owner decision explicitly reopening `DEM-001` for this trial. | **High** |
| **AUD-COM-02** | **P0 — BLOCK** | **Task 1 has no technical-security adversary.** It leaves M6 idle while exposing shared credentials and a shared mutable clinical UI, scheduling resets and possibly automating credential issuance. M4 owns conversion and M5 claims; neither owns abuse, sessions, credential leakage, reset atomicity, concurrency, rate limits or rollback. `MarketingWebsite.md` §13.3 already identifies missing edge rate-limiting. | Fire M6 for Task 1 or add a security/privacy/abuse role. Require a threat model, least-privilege review, synthetic-data invariant, concurrency test, rate-limit plan, failure-safe reset, monitoring and rollback verdict. | Produce a binding review covering this exact architecture and all listed controls. | **High** |
| **AUD-COM-03** | **P0 — BLOCK** | **The parity gate cannot prevent the silent fallback it names.** D3 runs before dispatch and sees intended models. Actual models are knowable only later and may be `UNOBSERVED`. No post-run gate or quarantine exists. | Split D3 into preflight and post-flight. Quarantine output until observed models satisfy parity. Treat `UNOBSERVED` as `INVALID` absent written Owner acceptance. | The runtime verifiably fixes/exposes model identities before execution and prevents fallback. | **High** |
| **AUD-COM-04** | **P0 — BLOCK** | **“Dispatch-ready” contradicts `MarketingSkills.md` §§2–3.** It says no marketing skill/plugin/agent is installed and named external packs are uninstalled and unaudited. This charter assigns those skills while §6.3 says nothing further is required. Authorisation also does not prove model availability. | Add a per-role preflight: model available; skill audited/installed or `NOT USED`; required access available; path writable. Label skills optional unless required. Never auto-install third-party skills during dispatch. | A current inventory proves every required capability and distinguishes required from optional aids. | **High** |
| **AUD-COM-05** | **P1 — MAJOR** | **M5's scope contradicts itself.** §2 says M5 fires on every task without exception; §6.2 makes it optional for pure internal research. | State the precise rule in both places: mandatory for customer-facing and decision-feeding work; optional only for stamped internal research. Name who decides when research starts feeding a decision. | A higher-priority referenced rule already resolves the conflict. | **High** |
| **AUD-COM-06** | **P1 — MAJOR** | **The M0/M5 evaluation loop has no termination rule.** D9 evaluates M5; §M0-E requires M5 to review that evaluation. The nine-section pack has no place for the second review or a final authority when they disagree. Re-evaluating it recurses. | Add one `10. META-REVIEW`: M5 reviews M0's evaluation once; M0 may correct objective errors but does not evaluate the meta-review; unresolved disagreement goes to the Owner. Define when the pack is final. | Produce a binding procedure already defining this terminal step and record location. | **High** |
| **AUD-COM-07** | **P1 — MAJOR** | **Templates cannot prove Rule G compliance.** Rule G requires basis, falsifier and confidence for every finding, but the M0 pack, M5 verdict and single-agent log templates do not require those fields. E1 detects omissions only afterward. | Add mandatory `BASIS`, `FALSIFIER`, and `CONFIDENCE + rationale` fields to every finding/proposal/review template. Define generated copy separately: trace IDs plus claim-verification record. | Existing enforced schemas consistently supply all fields. | **High** |
| **AUD-COM-08** | **P1 — MAJOR** | **M5 is incentivised to manufacture objections.** It must find a substantive objection or defend finding none; two clean passes become a finding about the reviewer. This creates a false-positive quota. | Score coverage, correct detection and false-positive avoidance. Permit zero findings with complete evidence. Test reviewers using seeded defects, not objection quotas. | Blind quality-control data shows the rule does not increase false or performative objections. | **Medium-High** |
| **AUD-COM-09** | **P1 — MAJOR** | **Task 1 pre-decides a supposedly open deliberation.** Rule A/B and M2 require genuine alternatives, but the task says the “correct output” is a cron entry, form, notices and two credentials. This anchors the silent round. Several answers require implementation despite the blanket warning against development. | Label the pattern an incumbent hypothesis to attack, not the correct answer. Require lifecycle cost and operational risk. Distinguish minimal site/operations work from product-feature development. | Blind agents converge on this design and show every material alternative is dominated. | **High** |
| **AUD-COM-10** | **P1 — MAJOR** | **M1 can contaminate the silent round.** Task 1 says M1 informs, M2/M4 answer silently, and M1 supplies context without a defined sequence. Beforehand creates an ungoverned framing input; afterward cannot inform independent proposals. | Run M1 first, validate and freeze a common evidence annex, then give the identical annex to M2/M4. Or publish M1 afterward as critique, not input. | Logs prove an unambiguous sequence and identical frozen evidence for all generators. | **High** |
| **AUD-COM-11** | **P1 — MAJOR** | **The date-state briefing is superseded.** §7.8 says all 37 appointments remain on `2026-08-14`. The current RDY-0094 record says PB-454 shifted the 37 non-recurring events and PB-461…467 visually reverified populated calendar/Flow Board screens. The enduring problem is an unscheduled reset restoring a frozen baseline. | Separate and timestamp baseline behaviour, latest state per host, and unresolved scheduled reset/rebase risk. Do not call volatile state “true today” without host and observation time. | A current recheck shows the relevant instance is exactly in the stated condition and later evidence concerned another host. | **High** for staleness; **Medium** for current host state |
| **AUD-COM-12** | **P1 — MAJOR** | **§7.5 is not a controlled copy of canonical §32.** It regroups the rules in prose, while M5 must check all 30 canonical rows verbatim and cite item numbers. The readiness document calls §32 the single canonical list and warns against duplicate drift; Rule F encourages use of this summary instead. | Label §7.5 non-authoritative. Attach a version/hash-checked canonical §32 snapshot and GTM §14 register to each dispatch, failing closed on drift. | A deterministic check proves §7.5 is a current one-to-one rendering of all 30 rows and blocks drift. | **High** |
| **AUD-COM-13** | **P2 — MODERATE** | **The website-readiness breakdown is incomplete.** §7.7 lists 21 ready, 5 partial, 2 model-only and 1 blocked across 30 rows; these sum to 29. `EV-034` supports 21 of 30 ready but not this unreconciled remainder. | Cite/reproduce all 30 mutually exclusive rows and add a total check. | One category intentionally overlaps and the missing row is identified. | **High** for arithmetic; **Medium** for intended categories |
| **AUD-COM-14** | **P2 — MODERATE** | **Policy and evidence are not distinguished.** “No form over five fields,” Latin digits and other rules may be sensible but are presented as hard constraints without classifying them as locked, evidence-derived, Owner policy or hypothesis. | Tag every constraint `LOCKED`, `EVIDENCE`, `OWNER POLICY`, or `TEST HYPOTHESIS`, with source and change authority. Treat heuristics as testable defaults unless locked. | Each rule traces to a binding decision or recorded Owner instruction. | **Medium-High** |
| **AUD-COM-15** | **P2 — MODERATE** | **No freshness policy governs volatile evidence.** URLs, competitor pages, live-host state, models/skills, gates and demo data change. The PDF is not certified complete: 17 competitors have verified page evidence and 9 remain Unknown/Limited. Dates are recorded but no expiry or recheck trigger exists. | Define freshness classes: infrastructure immediately before use; competitors before publication; models/skills each dispatch; gates from the current register; code claims pinned to commit/hash. Mark expired evidence `STALE`. | Produce a binding project-wide freshness standard. | **High** |

### 10.3 Minimum amendments before first dispatch

1. Resolve the `DEM-001` self-serve reversal before Task 1.
2. Add technical security/privacy/abuse ownership to Task 1.
3. Add post-flight parity validation and quarantine for `UNOBSERVED` or downgraded runs.
4. Add actual model/skill/tool capability preflight; mark external skills optional and uninstalled.
5. Attach version-controlled canonical §32 and GTM §14 inputs; demote §7.5 to summary.
6. Make Rule G fields mandatory and add a terminating meta-review section.
7. Freeze M1 evidence before the independent round; remove the pre-decided “correct output.”
8. Correct the appointment-state statement and reconcile the 30-row readiness arithmetic.

After amendment, run a no-consequence dry dispatch with seeded defects, one simulated silent model
downgrade and one failed agent. Confirm that M5/M6 find the defects, invalid output is quarantined,
failure is recorded and the pack terminates cleanly.

### 10.4 Strengths to preserve

- Evidence and locked decisions outrank committee fluency.
- Correlated agents do not vote.
- Generators work independently before cross-exposure.
- Dissent, failures and retries remain visible.
- Generators and claim reviewers are separated.
- M5's veto has an Owner-only written override.
- Shared-document writes and commits remain isolated to the Orchestrator.
- Mandatory qualifications stay in the same visual unit as claims.
- Competitor work retains `Observed / Inferred / Unknown` labels and never turns Unknowns into
  publishable frequency claims.

### 10.5 Disposition

| Item | Disposition |
|---|---|
| Charter as a governance concept | **APPROVE WITH REQUIRED CHANGES** |
| “Active and dispatch-ready” status | **REJECT until AUD-COM-01 through AUD-COM-04 are resolved** |
| Task 1 | **BLOCKED pending Owner authorisation and technical-security ownership** |
| Task 2 | **CONDITIONALLY USABLE after parity/capability preflight and template repair** |
| Single-agent internal research | **CONDITIONALLY USABLE only with `NOT CLAIM-REVIEWED`, an explicit no-decision-use boundary and capability preflight** |
| Customer-facing or decision-feeding output | **BLOCKED until canonical-control, parity and meta-review defects are repaired** |

This audit is an independent review record. It does not amend the preceding charter, authorise the
self-serve trial, close an RDY item or override the Owner.

---

## 11. Second independent review and audit — 2026-08-20

**Appended 2026-08-20 by the Orchestrator session, at the Owner's request.** Not produced by any
committee agent, and not itself a committee deliverable — this is an outside read of the charter
against the corpus it claims to sit on top of.

**This section is subordinate to the same Standing Rule it audits.** Where a finding below and
`docs/HISModulesUsers.md` disagree, the audit wins and the finding is wrong.

### 11.0 Relationship to §10 — two audits, run blind of each other

**This review was conducted independently of §10 and did not see it.** §10 was written to this
file by a concurrent session while this audit was in progress; the two were reconciled only at
append time. Neither reviewer read the other's findings before forming its own.

**That makes the overlap evidence rather than duplication** — this is Rule B's silent independent
round, occurring by accident, on the charter itself. Recorded per duty D7, which requires
convergence and divergence to be separated explicitly rather than merged.

**Convergent — found independently by both, and therefore the strongest findings in either audit:**

| §10 | §11 | The shared finding |
|---|---|---|
| AUD-COM-01 | **F-1** | The self-serve premise reverses a locked decision and is treated as authorised |
| AUD-COM-09 | **F-1** (second half) | §5 Task 1 pre-declares the answer and anchors the classifier |
| AUD-COM-10 | **F-9** | M1's position in Task 1 either contaminates the silent round or informs nobody |
| AUD-COM-11 | **F-3** | §7.8's appointment-date state is superseded by PB-454 / PB-461…467 |
| AUD-COM-12 | **F-6** | §7.5 is a non-authoritative compression, yet M5 must cite canonical §32 items |
| AUD-COM-13 | **F-13** | §7.7's page-row categories sum to 29, not 30 |
| AUD-COM-04 | **F-10** | *"Dispatch-ready"* is not met — no capability preflight, no agent/skill mapping |

Seven independent agreements, including both audits' top-severity finding. Under Rule C that is
**not a vote** — it carries weight only because the two reviews used different methods and
different evidence and arrived at the same place.

**Divergent — raised by one audit and not the other. Reported as divergence, not merged:**

| Raised only in §10 | Raised only in §11 |
|---|---|
| **AUD-COM-02** — Task 1 has no technical-security/abuse adversary (M6 idle) | **F-4** — the roster is 5 generate : 2 adversary, stated as 4 : 2 |
| **AUD-COM-03** — D3 is a preflight gate against a runtime failure; no post-flight quarantine | **F-5** — §2.2's invariant rests on an ordering §2.1 disclaims |
| **AUD-COM-06** — the M0/M5 evaluation loop has no termination rule | **F-7** — M3's banned-adjective filter omits `cheapest` (§32 item 30) |
| **AUD-COM-07** — the record templates do not carry Rule G's own fields | **F-8** — M6's audit is the only deliverable with no adversary |
| **AUD-COM-08** — M5's acceptance criterion creates a false-positive quota | **F-11** — the competitor PDF is unreadable on this host |
| **AUD-COM-05** — M5's "every task" scope contradicts §6.2's optional case | **F-12** — Rule E is unenforced |
| **AUD-COM-14 / 15** — constraints untagged by authority; no evidence-freshness policy | **F-2** — Task 1's acceptance criteria contradict duty D2 |
| | **F-14…F-17** — brand question presented as settled; §8/§2.1 model mismatch; RDY-0088 cited though closed; the 16-vs-17 uninstalled-forms discrepancy |

**Two divergences are worth the Owner's attention specifically**, because each audit missed
something the other caught and the pair is stronger than either:

- **AUD-COM-02 and F-8 are the same hole seen from opposite sides.** §10 says M6 should *fire* on
  Task 1; §11 says nothing *reviews* M6 on Task 2. Both are true, and together they say M6 is
  mis-placed in the roster rather than merely mis-scheduled.
- **AUD-COM-03 and F-5 both attack the parity invariant, on different grounds** — §10 that it
  gates the wrong moment, §11 that it gates on an ordering the charter disclaims. Neither
  invalidates §2.2's intent; together they mean the mechanism needs rebuilding, not tightening.

**No contradiction was found between the two audits.** Where both addressed an item, they agreed
on direction and on severity to within one band.

---

### 11.1 Scope and method

**Read in full:** `MarketingCommittee.md` (852 lines) · `MarketingSkills.md` (178) ·
`MarketingWebsite.md` (619) · `committee/README.md`.

**Read selectively, against every factual assertion in §7:**
`Product-Positioning-and-GTM-Locked-Strategy.md` §14, §17, §29, §30, §31, §32 ·
`Marketing-MVP-and-Launch-Readiness-Requirements.md` §1.2–§1.4, §7 register rows, §32, §33, §40,
PB-441, PB-454, PB-471…477 · `HISModulesUsers.md` (capability figures) ·
`docs/evidence/EV-095-licence-attribution-pack.md` · `sql/database.sql`.

**Method.** Every number, section reference, RDY ID and quotation in §7 was traced to its source
and either confirmed or contradicted. Each finding below carries **Basis · Falsifier · Confidence**
per the charter's own Rule G. Findings are ranked most-severe first.

**Standard applied:** the charter is judged as an *operating instrument* — could a dispatch be run
from it tomorrow, and would the record it produces be trustworthy — not as a piece of writing.

---

### 11.2 What checks out

Stated first, because it is the larger part of the result, and because a review that leads with
defects misrepresents the artefact.

| Assertion | Source | Verdict |
|---|---|---|
| §32 carries **30** prohibited categories | `Marketing-MVP…md:12022-12056` | **Confirmed** — exactly 30 rows |
| Competitive frequency figures = §32 **item 26** | `…md:12048` | **Confirmed** |
| GTM §14.1 = **15** Safe Now (MC-01…15); §14.2 = **10** Safe With Qualification (MC-16…25) | `Product-Positioning…md:601,620` | **Confirmed**, and MSG-002 states the same split |
| Every mandatory qualification quoted in §7.6 | GTM §14.1/§14.2 rows | **Confirmed** verbatim or as a faithful compression — incl. hash-not-HMAC, zero layout forms, 47.5% chrome-only, voluntary 2FA, DB-per-site |
| Roles: **4 levels × 65 objects** | `HISModulesUsers.md:309,705` — 13 ACO sections / 65 ACOs | **Confirmed** |
| Arabic **47.5%** | `HISModulesUsers.md:746,2574` — 6,290 of 13,234 constants | **Confirmed** |
| **55 reports**, 10 disabled, no BI layer | `HISModulesUsers.md:3117` — 55 / 44 active / 10 disabled | **Confirmed** |
| R-02 *High likelihood / Severe impact*, the top drift risk | GTM §30 | **Confirmed** |
| R-08 *High impact* on the Arabic site | GTM §30 (Medium likelihood / High impact) | **Confirmed** |
| Self-disqualification is a **success metric** | GTM §29, row 2 | **Confirmed verbatim** |
| **9 open P0** — 0004, 0047, 0065, 0069, 0073, 0075, 0076, 0077, 0085 | `Marketing-MVP…md:319, 3574` | **Confirmed — exact match, both derivations** |
| G6 NOT READY; binding shortfall is **G3**, not proof | `Marketing-MVP…md:3583-3590` | **Confirmed verbatim** |
| Registers **47 / 27 / 18 / 60** | `Marketing-MVP…md:757,1091,6406` | **Confirmed** |
| Demo data 30 patients / 72 encounters / 37 appointments / 6 role accounts | `Marketing-MVP…md` §1.2 | **Confirmed** |
| SS-01…SS-12 captured; integrity recording **170,671 bytes** | `Marketing-MVP…md:3565-3570`; `ls docs/evidence/captures/2026-08-19/publication-ready/` → **13 files** | **Confirmed** |
| Nine unverified dossiers, **7 in the tier we will meet** | GTM §31, R-12 | **Confirmed** |
| RDY-0064 Dammam / `me-central2` CLOSED · RDY-0089 Arabic message design PROVISIONAL/BLOCKED · RDY-0087 native review closed | `…md:1083, 1133`, PB-416 | **Confirmed** |
| The `EV-095` quotation | `docs/evidence/EV-095-licence-attribution-pack.md:36-38` | **Confirmed verbatim** |

**This matters more than it looks.** Twenty-plus load-bearing facts, compressed from a corpus of
this size into one page, with **zero fabrications and one stale item** (F-3 below). That is a
better error rate than briefing packs in this repository have previously achieved, and §7 is the
charter's strongest component. The findings that follow are about structure, not about honesty.

**Also genuinely well-designed, and worth protecting from any future amendment:**

- **§2.2's observed-vs-assigned model rule.** The dispatch tooling does not report which model a
  subagent actually ran on. The charter anticipates exactly this and mandates `UNOBSERVED` rather
  than an assumption. That is the correct engineering answer to an unobservable, and it is rare.
- **Rule B step 1 (silent independent round)** and **Rule D (dissent recorded, never edited away)**
  are the two rules doing the most work, and both are reasoned from the correlated-model problem
  rather than borrowed from human committee practice.
- **M2's reopening tag.** Making novelty *permissible but labelled* is a better control than
  banning it, and it is the only mechanism in the charter that lets R-02 be managed rather than
  merely feared.
- **M5's prohibition on proposing replacement copy.** Correct, and the reason given — it would make
  the auditor the author of what it audits — is the right reason.

---

### 11.3 Findings

#### F-1 · BLOCKING — the committee's founding premise reopens four locked decisions, and the charter records one

**The self-serve trial is not a new problem to solve. It is a reversal of four locked GTM
decisions, three of which the charter never names.**

| Locked decision | What it locks | Named in this charter? |
|---|---|---|
| **DEM-001** | *"Screenshots + recordings + live guided demo + paid pilot. **No free trial.**"* Alternatives rejected: *"Self-service trial; sandbox"* | **Yes** — via `MarketingWebsite.md` §0 |
| **WEB-001** | *"**Book a walkthrough.** One primary CTA everywhere."* Confidence **High**. **Revisit trigger: none — the field is empty** | **No** |
| **GTM-001** | Founder-led, demo-led, pilot-first. Alternatives rejected: *"**Product-led**; sales-led; partner-led now"* | **No** |
| **GTM-003** | The §20 funnel. Alternative rejected: *"**Free trial funnel**"* | **No** |

GTM §18 is more explicit still: product-led / self-service was **"Rejected. GAP-0043 (not
multi-tenant), L-07 (manual per-site provisioning), no data, no trial artefact. It is a platform
programme, not a motion."** GTM §31 lists *"Free self-service trial at launch"* under **Decisions
NOT Taken**.

**Why this is blocking rather than academic.** Task 1 dispatches **M4** to design *"the trial
funnel"* — while M4's own brief says *"Out of scope: redesigning the locked IA"*, and M4 holds
**no reopening licence**; §4 grants that licence to **M2 alone**. M4 is therefore instructed to
produce a deliverable that, under Rule A, is **constraint-determined**, using an agent explicitly
forbidden to touch constraints. Under Rule A the correct handling of a constraint-determined item
is *"M5 holds a veto, not a vote. Only the Owner overrides, in writing."* The honest first output
of Task 1 is not a funnel design — it is a reopening request naming all four decision IDs.

Worse, §5 Task 1 pre-supplies M0's answer: *"(Expected: Challenge 1 is genuinely open…)"*.
Challenge 1 is not genuinely open. It is governed by DEM-001, WEB-001, GTM-001 and GTM-003. This
also sits badly with Rule B's own anti-anchoring rationale — the classifier is anchored in writing,
before it classifies.

- **Basis:** GTM §32 rows DEM-001, WEB-001, GTM-001, GTM-003 (`Product-Positioning…md:1211-1247`);
  GTM §18 (`:841`); GTM §31 (`:1191`); this file §4 M4 brief and §5 Task 1.
- **Falsifier:** an Owner instruction, in writing, reopening DEM-001 / WEB-001 / GTM-001 / GTM-003 —
  or a demonstration that a *time-boxed evaluation credential issued after a qualifying form* is not
  a free trial and leaves "Book a walkthrough" as the primary CTA. **That second reading is genuinely
  available and may well be the right one** — but it has to be *made*, by the Owner, not assumed.
- **Confidence: High.** The decision IDs and their rejected alternatives are explicit and unhedged.

**Recommended remedy — cheapest first.** Add to §5 Task 1 a **step 0**: M0 issues a reopening
request naming DEM-001, WEB-001, GTM-001, GTM-003, with the two readings above, and the dispatch
does not proceed until the Owner answers. This costs one paragraph and one Owner decision, and it
converts the charter's largest exposure into its Rule A showcase.

---

#### F-2 · BLOCKING — Task 1's acceptance criteria contradict Rule A and duty D2

Duty **D2**: *"An item classified evidence-determined convenes nobody; it is answered and closed."*
Rule **A**, for evidence-determined items: *"**No discussion.** The evidence decides."*

§5 Task 1 acceptance then requires: *"**Every challenge** has ≥3 ranked options with cost, time and
dependency"* — and M2's deliverable spec requires *"at least three candidate solutions"* per
challenge. But Task 1's own method predicts Challenges 2 and 3 are *"largely evidence-determined"*
and Challenge 4 *"constraint-determined by §40 and §32"*.

**So the task is accepted only if the committee does the thing D2 forbids.** No agent can satisfy
both. In practice the acceptance criterion wins, because it is what the deliverable is graded
against — which means the classification step gets performed and then ignored, and R-02 drift
enters through the acceptance criteria rather than through anyone's opinion.

- **Basis:** this file §4 duty D2; §3 Rule A; §5 Task 1 Method step 1 and Acceptance bullet 1;
  §4 M2 Deliverable.
- **Falsifier:** rewording the acceptance to *"every challenge classified **genuinely open** has ≥3
  ranked options; every challenge classified evidence- or constraint-determined carries its
  determining `file:line` and the answer it determines"*.
- **Confidence: High.** Both texts are unambiguous and directly opposed.

---

#### F-3 · BLOCKING — Rule G exempts the one artefact every agent inherits, and that artefact already carries a superseded fact

**Rule G:** *"Every finding carries three things or it is not accepted: Basis · Falsifier ·
Confidence."*
**Rule F:** *"Every agent reads §7 of this file **instead of** the 19,000-line corpus."*

§7 contains roughly forty load-bearing factual assertions. **Not one carries a basis citation.**
There is no `file:line`, no command, no date-accessed anywhere in §7 except the bare filenames in
§7.9. So seven agents are held to an evidence standard that the evidence they are handed is exempt
from — and Rule F simultaneously removes their ability to check it.

**This is already not theoretical.** §7.8 Challenge 2 presents `Total patients: 0` as current
state. It is not:

> **PB-454 (2026-08-19, Orchestrator, Owner-approved): the appointment-date staleness is FIXED** —
> the 37 non-recurring `openemr_postcalendar_events` rows were shifted forward 5 days … Verified:
> `SELECT COUNT(*) WHERE DATE(pc_eventDate) = CURDATE()` now returns **16** (was 0).

and **SS-06 / SS-07 were then captured live against the re-anchored data** (PB-461…467), which is
what let RDY-0094 close 14-of-14 rows. `Marketing-MVP…md:1143`.

The charter's substantive point survives — the fix was a **one-off manual `UPDATE`, not a
schedule** — so the structural gap Challenge 2 names is real. But the *evidence* offered for it is
five days stale, and seven cold-start agents would each inherit it as current, unable to check.
That is precisely the failure Rule F was written to prevent, arriving *through* Rule F.

- **Basis:** this file §3 Rule F, Rule G, §7.8; `Marketing-MVP…md:1143` (PB-454, inside the RDY-0094
  row), `:3318` (PB-441).
- **Falsifier:** adding a basis citation to every factual row in §7 — they exist; this audit
  recovered all of them in under an hour — plus a `Facts current as of <date>` stamp on §7 and a
  re-verification duty in D1.
- **Confidence: High** for the rule gap; **High** for the staleness — PB-454 and the SS-06/SS-07
  captures both sit in the RDY-0094 row that §7.9 itself points agents toward.

**Note the compounding effect with F-1.** §7 is where the four challenges are framed as *challenges
to solve* rather than as *locked decisions to reopen*. An agent that cannot read behind §7 cannot
discover DEM-001, WEB-001, GTM-001 or GTM-003 for itself. F-1 is not merely a missing citation; it
is a missing citation that suppresses the constraint.

---

#### F-4 · MATERIAL — the balance is 5 generate : 2 adversary, stated as 4 : 2 in two places

§2's own composition table types **M1, M2, M3, M4 and M7** as *Generate* — five — and **M5, M6** as
*Adversary* — two. The prose immediately beneath reads *"Balance is deliberately 4 generate : 2
adversary"*, and §M0-E E2 makes M0 evaluate *"the **4 generate : 2 adversary** ratio"* on every
task.

This is not a typo without consequence: E2 asks M0 to assess a ratio that does not describe the
roster, and the paragraph's argument — *"the binding constraint is the filter, not the generator"* —
is a quarter weaker than stated. The likely origin is that M7 was added late; §M0-E E2 says so
outright (*"This is how M7 was found"*), and the ratio was never re-derived.

- **Basis:** this file §2 composition table (7 rows, types as listed); §2 prose; §M0-E E2.
- **Falsifier:** re-typing M7 as something other than *Generate* — which its mandate does not
  support, since M7 produces Arabic message hierarchy, tone and CTA wording.
- **Confidence: High.** Arithmetic on the document's own table.

---

#### F-5 · MATERIAL — the parity invariant rests on an ordering the charter explicitly disclaims

§2.2's premise is a **capability** claim: *"A reviewer weaker than the thing it reviews
rubber-stamps."* Its mechanism is `tier(M5) ≥ max(tier(generators))`.

§2.1 then says of that very ladder: *"**an authorisation ordering, not a claim about relative
capability**."*

If the ordering does not encode capability, `tier(M5) ≥ max(tier(generators))` guarantees nothing
about whether M5 can catch what a generator produced — only that both were equally *authorised*.
The invariant is then a governance formality wearing the language of a capability control, which is
worse than either alone, because §8 lists it as **the** control for both *"Silent model downgrade"*
and *"Rubber-stamp review"*.

The disclaimer is also doing real work elsewhere and should not simply be deleted: it is what lets
**Fable 5** and **Opus 5** share a tier without the charter asserting they are equivalent.

**Second-order.** Remedy **R1** (*"downgrade every generator to Sonnet 5, parity holds at Tier B"*)
preserves the *ratio* while lowering the *absolute* strength of the review. If the concern is
relative, R1 is right. If the concern is that M5 must be strong enough in absolute terms to catch a
§32 violation, R1 does not address it and **R2 (hold the dispatch)** is the only real remedy. The
charter never says which concern it holds.

- **Basis:** this file §2.1 tier-ladder note; §2.2 premise, formula and remedy table; §8 control
  column.
- **Falsifier:** either (a) state that the ladder *is* capability-ordered for review purposes and
  own the assertion, or (b) restate the invariant as *"M5 runs at the highest tier available in the
  dispatch, and never below any generator"* — enforceable without any capability claim.
- **Confidence: Medium-High.** Both texts are explicit; the practical consequence depends on how far
  apart the tiers actually are, which is not knowable from documents.

---

#### F-6 · MATERIAL — M5 is instructed to check §32 verbatim, and Rule F denies it the verbatim text

M5's method, check 1: *"**§32** — all 30 prohibited categories, **verbatim**"*.
Rule F: agents read §7 *instead of* the corpus.
§7.5 is a **compression** of §32 — accurate, as §11.2 confirms, but reorganised into four prose
groups with the item numbers stripped.

Three concrete consequences:

1. M5 must cite *"the exact §32 item"* for every **BLOCK** (§4, M5 Deliverable). §7.5 carries no
   item numbers. **M5 cannot produce a compliant BLOCK from its permitted reading.**
2. §7.5 drops each item's *per-phase scope*. §32 distinguishes Phase 3 / Phase 4 / Phase 5 impact
   per row — item 5, for instance, permits *"Reporting", never "analytics"* as **copy** while
   prohibiting the **page**. A reviewer working from §7.5 cannot make that distinction.
3. **F-7 is a direct instance**: a term present in §32 is absent from the operative filter.

- **Basis:** this file §3 Rule F; §4 M5 Checks item 1 and Deliverable line;
  `Marketing-MVP…md:12026-12056` (30 numbered rows with per-phase columns).
- **Falsifier:** amend Rule F to *"every agent reads §7; **M5 additionally reads §32 in full**"* —
  one exception, one agent, and the veto becomes citable.
- **Confidence: High.**

---

#### F-7 · MATERIAL — M3's banned-adjective filter omits `cheapest`

M3's hard constraint lists the filter as: *best · leading · complete · comprehensive ·
enterprise-grade · AI-powered · seamless · fully integrated · end-to-end · hospital-grade · secure
(unqualified) · affordable · unlimited · all-inclusive.*

§32 **item 30** reads: *"Affordable", "**cheapest**", "unlimited", "all-inclusive"*. §7.5's own
banned-adjective line carries it. **M3's list does not.**

M3's list is the operative one — it is the filter pasted into the dispatch that writes the copy. So
the single term most likely to surface in price-adjacent copy, for a product whose entire pricing
page is a **model without figures**, is missing from the filter that would catch it. GTM §31
independently flags *"Lead on price / 'affordable'"* under Decisions NOT Taken.

- **Basis:** this file §4 M3 Hard constraints; §7.5 banned-adjective line;
  `Marketing-MVP…md:12056` (§32 item 30); GTM §31.
- **Falsifier:** none available — the term is present in two sources and absent from the third.
- **Confidence: High.**

---

#### F-8 · MATERIAL — M6's audit is the only deliverable in the charter with no adversary

Every generating agent's output goes to M5. **M6's goes to no one.**

In Task 2, M6 *leads*; M4 answers one narrow question; M5 checks only *"whether any architectural
choice creates a claim risk"* — explicitly not M6's technical reasoning. M6's per-layer
**APPROVE / APPROVE WITH CHANGES / REJECT AND REPLACE** verdicts travel into
`M0-decision-pack-task2.md` unchallenged, and M0 is barred from resolving or contesting them.

§8's own failure-mode table names this exactly — *"Rubber-stamp review: reviewer weaker than the
reviewed, **or reviewer authored it**"* — and offers the control *"M5 never generates"*, which has
no M6 equivalent.

**The mitigation partly exists and deserves credit:** M6's *"state what would have to be true for a
different choice to win"* requirement is a self-adversarial device, and a good one. It is not a
substitute for a second reader, because an agent that has already reached a verdict writes the
change-my-mind condition to fit the verdict.

- **Basis:** this file §4 M6 brief and Acceptance; §5 Task 2 Method steps 1–4; §8 row 4.
- **Falsifier:** naming a reviewer for M6 — cheapest is **M4 on Tier A**, given a genuinely
  adversarial brief against M6's verdicts, since M4 is already in the Task 2 firing set and already
  holds the IA and funnel context.
- **Confidence: Medium-High.** Structural, and visible in the task spec.

---

#### F-9 · MATERIAL — Task 1's method makes M1's contribution arrive too late to contribute

§5 Task 1 Method, in order: **(2)** silent independent round, M2 and M4 answer without seeing each
other → **(3)** *"M1 supplies acquisition context where a challenge touches conversion."*

By step 3 the answers already exist. So either M1's context reaches M2 and M4 during step 2 — in
which case a **single shared input anchors both generators**, the correlated-anchoring failure Rule
B step 1 exists to prevent, applied to the evidence rather than to the opinions — or it arrives at
step 3 and informs nobody, in which case §5's *"M1 informs"* is decorative.

This is not unresolvable: a shared *factual* input differs in kind from a shared *proposal*, and
supplying it to both generators before step 2 is defensible. But the charter has to say which, and
if the answer is "before", M0's D4 record must show both briefs carried identical M1 material —
otherwise a divergence at step 7 cannot be attributed to the agents rather than the briefs, which is
D4's stated purpose.

- **Basis:** this file §5 Task 1 Method steps 2–3; §3 Rule B step 1; §4 duty D4 (*"Record the exact
  brief issued to each — verbatim … so a reader can tell whether a difference in answers came from
  the agents or from the briefs"*).
- **Falsifier:** re-ordering so M1 fires first as a **pre-brief input**, with D4 recording the
  identical M1 attachment in both briefs.
- **Confidence: Medium-High.**

---

#### F-10 · MATERIAL — §6.3 claims dispatch-readiness, but no brief maps to a dispatchable agent

§6.3: *"All eight agents are **briefed and dispatch-ready now**. Nothing further is required before
a dispatch except the request itself and M0's D1/D3 pass."*

Nothing in §2, §4 or §6 names an **agent type**. The types available in this environment are
`claude`, `claude-code-guide`, `Explore`, `general-purpose`, `Plan`, `statusline-setup` — and
`MarketingSkills.md` §2 states the position plainly: *"**There is no marketing skill, plugin or
agent installed in this project. Not one.**"* M1–M7 must therefore be realised as generic agents
carrying the §4 brief as prompt text, and the charter never says so.

Three consequences a dispatcher hits immediately:

| Gap | Consequence |
|---|---|
| No `subagent_type` per agent | The Orchestrator picks one per dispatch. Two dispatches of "the same" agent are then not the same agent, and D9's per-agent evaluation is not comparable across tasks |
| No model-parameter mapping | §2.1 assigns *Fable 5 / Opus 5 / Sonnet 5*; the dispatch surface takes `fable / opus / sonnet`. Trivial — but it is the D3 gate's own input, and it is unwritten |
| **`Explore` is read-only** | Rule E requires each agent to *write* its own file. An agent dispatched as `Explore` cannot satisfy its own deliverable |

- **Basis:** this file §6, §6.3; `MarketingSkills.md` §2 (installed-agent inventory); the session's
  available agent-type list.
- **Falsifier:** a one-row addition to §6's mechanics table — *agent type · model parameter* per
  agent — after which the readiness claim is true.
- **Confidence: High.** §6.3 makes a completeness claim the file does not meet.

---

#### F-11 · **RESOLVED 2026-08-20** — the primary competitor source could not be read by the agents that depend on it

> **RESOLUTION, applied 2026-08-20 on Owner instruction.** `pdftotext` **is** present on this host
> (`/mingw64/bin/pdftotext` — Git Bash ships poppler); only `pdftoppm`, which the file-reader needs
> for rendering, is absent. That distinction was missed on the first pass, and the finding below
> overstated the obstacle as a result.
>
> `pdftotext -layout -enc UTF-8` produced **222,670 bytes · 3,480 lines · 59 pages · 0 replacement
> characters**, and a second run **diffed byte-identical** — the extraction is deterministic and
> re-derivable, not a one-off artefact. Committed as
> `docs/OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.txt`; the `.pdf` remains authoritative. §7.9 now
> points at the `.txt` and carries the re-derivation command.
>
> **Two things this settles.** `MarketingSkills.md` §1's load-bearing *"technique, not for content"*
> quotation is **confirmed present** (extraction line 39), closing the corresponding row in §11.6;
> and *"26 dossiers, 17 verified, 9 Unknown"* is confirmed in the report's own status header,
> independently of the GTM cross-citations §11.3 had to rely on.
>
> **What remains true from the finding:** the charter still does not *check* that a dispatched agent
> can read its primary source. §11.8.7's preflight line for M1 and M7 is still worth adding — it now
> guards a `.txt` that any runtime can read, which makes it near-costless rather than load-bearing.
>
> The original finding is left unedited below, per Rule D.

§7.9 directs agents to `docs/OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.md.pdf` for *"competitor
marketing surfaces"*. **M1's entire mandate is defined against it** (*"Extend competitor
intelligence **beyond** the existing report"*), and **M7's first job** is the Arabic review *"that
has never been done"* — which requires knowing what the English review covered.

The file is a **2.28 MB PDF with subset-embedded fonts**. Text extraction returns **0 characters**;
`pdftoppm` / `pdftotext` are not installed on this host; **no `.md` source was found in the
repository** (`docs/` holds only the `.pdf`). Every quotation from it in this folder — including
`MarketingSkills.md` §1's load-bearing *"technique, not content"* rule, cited to §1.1 — is therefore
**unverifiable from the repository as it stands.**

**Those quotations are almost certainly accurate.** The derived figures (26 dossiers, 17 verified,
9 unknown, 7 in-tier, 0/16, 1/16 WhatsApp, 8/12 GCC Arabic) reconcile exactly across GTM §31,
§32 COMP-001 and R-12 — strong indirect corroboration. But M1 cannot cite a page it cannot open, and
Rule G forbids citing one it has not read.

- **Basis:** extraction attempted on this host — 5,666,737 bytes of decompressed content streams,
  4,551 hex-string tokens, **0 extractable text characters**; `Read` on the PDF returns
  *"pdftoppm is not installed"*; `ls docs/ | grep -i competitor` returns the `.pdf` only.
- **Falsifier:** locating the original `.md`, or rendering the PDF on a host with poppler. Both are
  cheap; neither has been done.
- **Confidence: High** for unreadability *with the tooling available to this session*; **Medium**
  for repository-wide absence of an `.md` source — the local-environment caveat about stale `G:`
  directory listings applies, and this was checked once.

**Counter-evidence, added at reconciliation (see §11.0).** The concurrent §10 audit states it read
*"all 59 pages of the competitor-intelligence PDF"*, and AUD-COM-15 reports page-level detail from
it (*"17 competitors have verified page evidence and 9 remain Unknown/Limited"*). **So the file is
readable — by some tooling, not by this session's.** That narrows the finding rather than
withdrawing it: the risk is no longer *"nobody can read it"* but *"whether a dispatched agent can
read it is a property of the agent, and the charter neither states the requirement nor checks it."*
An M1 or M7 dispatched onto a runtime without PDF extraction fails silently on its single most
important source. The remedy is unchanged and now cheaper to justify: **extract the report to
text once, commit it beside the PDF, and point §7.9 at the text.**

---

#### F-12 · MATERIAL — Rule E is a policy, not a control, and §3 describes it as a mechanism

Rule E: agents write *only* their own file; *"No agent edits `MarketingWebsite.md`,
`Marketing-MVP…md`, or any `EV-*` file. This **implements** Concurrency Rule 2 and **prevents** the
collision class that has already occurred twice in this repository (Rule 4a, Rule 6)."*

A `general-purpose` or `claude` subagent holds `Write`, `Edit` and `Bash`. **Nothing enforces the
restriction** — no path allowlist, no hook, no permission scope. Rule E is an instruction in a
prompt, and a prompt instruction is not a control; it is a hope with a rule number.

This matters more here than in most projects, precisely because Rule E cites a collision class that
has **already occurred twice**, and because M0's record duty (*"a dispatch that partly failed is
recorded as partly failed"*) provides no way to notice a stray write after the fact.

- **Basis:** this file §3 Rule E wording; the tool surface available to the `general-purpose` and
  `claude` agent types.
- **Falsifier:** a `PreToolUse` hook restricting `Write`/`Edit` to
  `docs/Marketing Website/committee/` for the duration of a dispatch — after which Rule E's
  *"prevents"* becomes literally true.
- **Confidence: High** on the mechanism; **Medium** on realised risk — no dispatch has run yet (§9),
  so the exposure is prospective.

---

### 11.4 Minor findings

| # | Finding | Basis | Note |
|---|---|---|---|
| **F-13** | §7.7's website-readiness row drops a category. Source reads *"21 Ready · 5 Partial · 2 model-only · **1 qualified-text-only** · 1 BLOCKED"* = 30. §7.7 lists 21 + 5 + 2 + 1 = **29**. One page row is invisible to every agent | `Marketing-MVP…md:3581` vs this file §7.7 | Minor — but it is a *page*, and M4 designs pages |
| **F-14** | §7.1 asserts *"**Thiqa** (ثقة) by **SkyEagle**"* as settled. `MarketingWebsite.md` §13.8 Q2 lists *"Is the site branded Thiqa or SkyEagle?"* as **open and undecided**. M3, M4 and M7 would each build on a resolution nobody made | This file §7.1 vs `MarketingWebsite.md` §13.8 | Minor now, expensive later — it propagates into every page, both languages |
| **F-15** | §8's control for *"Rubber-stamp review"* reads *"**All agents on Opus 5**"*. M0 runs on **Fable 5** (§2.1). The control statement and the roster disagree | This file §8 row 4 vs §2.1 | Cosmetic — but §8 is the failure-mode table a reader trusts |
| **F-16** | M1's brief cites the frequency-figure ban as *"This is **RDY-0088** and it is not negotiable."* **RDY-0088 is CLOSED** (PB-372, 2026-08-19). The prohibition genuinely survives — *"RDY-0088's closure recorded the hold and passed claim review; **it did not lift the prohibition**"* — but an agent that looks the ID up finds `CLOSED` and may draw the opposite conclusion. Cite **§32 item 26**, which is live | `Marketing-MVP…md:2185` (PB-372), `:3588` | Minor, high blast radius if misread |
| **F-17** | Inherited discrepancy, surfaced because the charter makes `HISModulesUsers.md` the authority: GTM MC-12 and §33.1 say *"a further **16** forms ship uninstalled"*; `HISModulesUsers.md:115` records **35 on disk, 18 registered → 17 unregistered**. Under §1's Standing Rule the audit wins and **17** is correct | `HISModulesUsers.md:115,337` vs GTM §14.1 MC-12 | Minor — and a useful live demonstration that the Standing Rule bites |

---

### 11.5 Carried forward to Task 1 — a hazard in the reference fix, not a defect in this charter

Task 1's expected output is *"a cron entry, a form, four on-screen notices, and two credentials"*,
and §7.8 points at PB-454's date-shift as the pattern to schedule. **Check the pattern before
scheduling it.**

PB-454 ran:

```sql
UPDATE openemr_postcalendar_events
   SET pc_eventDate = DATE_ADD(pc_eventDate, INTERVAL 5 DAY),
       pc_startTime = DATE_ADD(pc_startTime, INTERVAL 5 DAY)
 WHERE pc_recurrtype = 0;
```

**`pc_startTime` is a `TIME` column**, not a datetime:

```
sql/database.sql:8261  CREATE TABLE `openemr_postcalendar_events` (
sql/database.sql:8281    `pc_startTime` time default NULL,
```

`DATE_ADD(<TIME>, INTERVAL 5 DAY)` does not shift a date — it adds 120 hours to a duration, and
MariaDB's `TIME` accepts values to `838:59:59`. A `09:00:00` appointment becomes `129:00:00`, or
`NULL`. Either way the column stops carrying a wall-clock start time.

**Counter-evidence, stated so this is not over-read:** SS-06 and SS-07 were captured *after* the
shift and show a populated calendar and a 16-row Flow Board (PB-461…467). That is hard to reconcile
with wholesale time corruption, so this is a **flagged hazard, not a confirmed defect** — the local
stack is down (`ERROR 2002 (HY000) … 127.0.0.1`), so it could not be settled here.

**Second issue, independent of the first, and more certain.** The shift was a fixed `+5 DAY`, not a
re-base to `CURDATE()`. It moved the seed from `2026-08-14` to `2026-08-19`. **Today is
2026-08-20.** The seed decays one day per day and has already decayed by one. Challenge 2 is
therefore *currently live again*, for a different reason than §7.8 gives — which strengthens the
charter's argument while invalidating its evidence, exactly as F-3 describes.

**Re-runnable check, once the stack is up:**

```powershell
C:\openemr-stack\start-openemr.ps1
C:\openemr-stack\mariadb\bin\mariadb.exe -u root --host=127.0.0.1 --port=3306 openemr -e `
  "SELECT CURDATE() today,
          SUM(DATE(pc_eventDate)=CURDATE()) today_events,
          MIN(pc_startTime) min_t, MAX(pc_startTime) max_t,
          SUM(pc_startTime IS NULL) null_times
     FROM openemr_postcalendar_events WHERE pc_recurrtype = 0;"
```

`max_t` above `23:59:59`, or a non-zero `null_times`, confirms the hazard. **And per §7.8's own
correct caution, the check that decides anything is on `demo.skyeagle.uk`, not on this host.**

- **Basis:** `sql/database.sql:8261,8281`; `Marketing-MVP…md:1143` (PB-454's SQL and PB-461…467);
  connection attempt to `127.0.0.1:3306` → `ERROR 2002 (HY000)`.
- **Falsifier:** the query above returning `max_t ≤ 23:59:59` and `null_times = 0`.
- **Confidence: High** that `pc_startTime` is `TIME` and that the seed has decayed; **Medium** that
  the `DATE_ADD` produced bad data, given the SS-06/SS-07 counter-evidence.

---

### 11.6 What this audit could not verify

Stated because Rule G makes *"unverified"* an acceptable answer and *"probably fine"* not.

| Item | Why not | What would settle it |
|---|---|---|
| ~~Every quotation attributed to the competitor intelligence report~~ **RESOLVED 2026-08-20** | ~~Subset-font PDF, 0 extractable characters, no poppler on this host~~ — **wrong: `pdftotext` was present all along; only `pdftoppm` was missing** | **Done.** Extracted to `docs/OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.txt`, 59 pages, byte-identical on re-run. The §1.1 *"technique, not for content"* quotation is confirmed at extraction line 39. See F-11's resolution note |
| The live state of `demo.skyeagle.uk` | No browser session and no outbound check performed in this review | A real `navigate` + `read_page`, or `curl -I` from a host with egress |
| Whether the date-shift corrupted `pc_startTime` | Local MariaDB not running | §11.5's query |
| Whether Fable 5 and Opus 5 are comparable for adversarial review | Not knowable from documents, and §2.1 disclaims the question | Run F-5's falsifier: one dispatch, M5 at each tier, same input, compare the objection sets |
| That no `.md` competitor source exists anywhere in the repository | A recursive `find` over `G:` timed out at 120 s; checked via `ls docs/` only | One `find` run with a longer budget |

---

### 11.7 Verdict

**APPROVE WITH REQUIRED CHANGES — do not dispatch Task 1 until F-1, F-2 and F-3 are resolved.**

| Aspect | Assessment |
|---|---|
| **Factual accuracy of §7** | **Strong.** ~40 assertions traced; 20+ confirmed exactly; **1 stale** (F-3), **1 incomplete** (F-13), **0 fabricated** |
| **Governance design** | **Strong precisely in the parts that are unusual** — silent independent round, recorded dissent, observed-vs-assigned models, M2's reopening tag, M5's no-replacement-copy rule. Reasoned from the correlated-model problem, not borrowed |
| **Internal consistency** | **Weak in three places** — F-2 (acceptance vs D2), F-4 (5:2 stated as 4:2), F-6 (Rule F vs M5's verbatim mandate). Each is a one-line fix |
| **Constraint traceability** | **The material weakness.** F-1: three of the four locked decisions the committee's premise reverses are named nowhere in the charter, and the agent tasked with the reversal holds no licence to make it |
| **Executability** | **Not yet.** §6.3's readiness claim is not met — no agent-type mapping (F-10), the primary competitor source is unreadable (F-11), write isolation is unenforced (F-12) |
| **Adversarial design** | **Sound, with one hole** — F-8: M6 is the only agent whose deliverable nobody attacks |

**The charter's own §1 standard, applied to itself.** It asks whether committee output would
*strengthen* the property that every claim traces to an audited capability with its limitation
attached. On the evidence above: **yes with F-1 fixed, and no without it.** With F-1 unfixed, the
first dispatch produces a well-governed, fully-recorded, adversarially-reviewed design for a funnel
that four locked decisions prohibit — and the quality of the record would make that outcome *more*
persuasive, not less. That is R-02 arriving through the front door carrying a decision pack.

**Cheapest sufficient remedy set — six edits, no restructuring:**

| # | Edit | Fixes |
|---|---|---|
| 1 | Add **step 0** to §5 Task 1: reopening request to the Owner naming DEM-001, WEB-001, GTM-001, GTM-003, with both readings from F-1. No dispatch until answered | **F-1** |
| 2 | Reword Task 1 acceptance so *"≥3 ranked options"* applies to **genuinely-open** items only | **F-2** |
| 3 | Add a basis citation to every factual row in §7, a `Facts current as of` stamp, and correct §7.8 Challenge 2 to record PB-454 and its decay | **F-3, F-13, F-16** |
| 4 | Fix `4 generate : 2 adversary` → `5 : 2` in §2 and §M0-E E2; add `cheapest` to M3's filter; add *"M5 additionally reads §32 in full"* to Rule F; fix §8's *"All agents on Opus 5"* | **F-4, F-6, F-7, F-15** |
| 5 | Add an *agent type · model parameter* column to §6's mechanics table; name **M4** as M6's adversary in Task 2; move M1 ahead of the silent round in Task 1 with a D4 identical-attachment note | **F-8, F-9, F-10** |
| 6 | Extract the competitor report to readable text before dispatching M1 or M7; flag §7.1's Thiqa/SkyEagle question as **open** | **F-11, F-14** |

F-5 and F-12 are left as **recorded Owner-decision items** rather than edits: F-5 needs a ruling on
whether the invariant is a relative or an absolute control, and F-12 needs a hook rather than a
sentence.

---

### 11.8 Remedies — solutions, in applicable form

**Added 2026-08-20 at the Owner's request**, to convert §11.3–§11.7 from findings into fixes.
Everything below is drop-in text or an executable control, not a description of one.

**Every remedy remains PROPOSED — Owner decision required**, except the five in §11.8.1 that are
plain factual errors and need no decision at all.

---

#### 11.8.1 Triage — most of this does not need the Owner

Of seventeen findings, **five are simply wrong numbers or wrong words.** They require no ruling, no
reopening and no deliberation; correcting them is maintenance.

| Finding | The error | The fix | Authority needed |
|---|---|---|---|
| **F-4** | *"4 generate : 2 adversary"* | → `5 generate : 2 adversary`, in §2 prose and §M0-E E2 | **None** — arithmetic |
| **F-7** | M3's filter omits `cheapest` | Insert `cheapest` after `affordable` in §4 M3 Hard constraints | **None** — §32 item 30 |
| **F-13** | §7.7's page rows sum to 29 | → `21 ready · 5 partial · 2 model-only · 1 qualified-text-only · 1 blocked` | **None** — arithmetic |
| **F-15** | §8 says *"All agents on Opus 5"* | → *"M5 and M6 on Opus 5; M0 on Fable 5"* | **None** — contradicts §2.1 |
| **F-16** | M1 cites closed RDY-0088 as the live constraint | → *"This is **§32 item 26**, which RDY-0088's closure explicitly did not lift"* | **None** — `…md:3588` |

**Apply these first.** They cost minutes, they remove five ways for an agent to inherit a wrong
fact, and none of them touches a decision.

Everything remaining splits three ways:

| Class | Findings | Route |
|---|---|---|
| **Owner ruling required** | F-1, F-5 | §11.8.2, §11.8.6 |
| **Editorial, but changes how the committee behaves** | F-2, F-3, F-6, F-8, F-9, F-10, F-14, F-17 | §11.8.3–§11.8.5 |
| **Needs a control, not a sentence** | F-11, F-12 | §11.8.7 |

---

#### 11.8.2 F-1 — the blocker dissolves if the work is sequenced correctly

**This is the most important remedy in the section, and it is cheaper than the finding implied.**

Re-reading the four locked rows for their **revisit triggers** — the column §11.3 did not weigh
heavily enough — changes the answer:

| Decision | Revisit trigger, verbatim | Has it fired? |
|---|---|---|
| **DEM-001** | **"Multi-tenancy or seeding automation ships"** | **Not yet — but Challenges 2 and 3 *are* seeding automation.** Ship the scheduled reset + date re-base and **this trigger fires by its own terms** |
| **GTM-001** | "3 paying customers or a repeatable runbook" | No |
| **GTM-003** | "Motion change" | Only if GTM-001 changes |
| **WEB-001** | **"—"** *(the field is empty)* | Never fires. Any change is a reopening |

`Product-Positioning…md:1230, 1233, 1236, 1238`.

**So the order of work decides whether this is a reopening at all.** `MarketingWebsite.md` §6
already sequences the scheduled job *before* the access decision — steps 1–2 before step 4 — but
nobody noticed that doing so **discharges DEM-001's reopening requirement rather than working
around it.** The strategy anticipated this exact path and wrote the trigger for it.

##### The three readings, priced

| | **Reading A — open self-serve trial** | **Reading B — gated evaluation credential** | **Reading C — published shared credential** |
|---|---|---|---|
| What it is | Anyone reaches the demo, unqualified, untimed | A time-boxed, non-admin credential issued **as the fulfilment of a booked walkthrough**, after a qualifying form | Credentials printed on the website |
| DEM-001 | **Reopen** — this is the rejected *"self-service trial; sandbox"* | **Not reopened** *if the discriminator below holds*; trigger fired by seeding automation | **Reopen** |
| WEB-001 | **Reopen** — no trigger exists | **Not reopened** — "Book a walkthrough" stays the single primary CTA | **Reopen** |
| GTM-001 | **Reopen** — this is *"product-led"* | **Not reopened** — founder-led/demo-led/pilot-first intact; the credential is a fulfilment artefact inside the demo step | **Reopen** |
| GTM-003 | **Reopen** — the rejected *"free trial funnel"* | **Not reopened** — §20's funnel shape is unchanged | **Reopen** |
| Abuse exposure | High | Bounded by qualification + expiry | **Severe** — invites credential-stuffing and scanners on a shared clinical UI |
| **Cost** | **4 reopenings, 3 with unfired triggers** | **0 reopenings, 1 trigger legitimately fired** | 4 reopenings *and* the worst security posture |

> **RECOMMENDATION — Reading B, sequenced behind the scheduled job.** It is the only option that
> preserves every locked decision, it is what `MarketingWebsite.md` §6 already proposed to build,
> and it keeps §5's two-credential advantage entirely intact. **Labelled a recommendation, not an
> outcome** — the ruling is the Owner's.

##### The discriminator — four conditions, all of which must hold

Reading B is not a label one can apply to Reading A. It is testable, and **M5 can enforce it**:

| # | Condition | Fails if |
|---|---|---|
| **D-a** | Access is issued **only** after a qualifying form applying GTM §5.1 / §5.2 | The form collects contact details but gates nothing |
| **D-b** | Access is **time-boxed**, and the expiry is stated **before** the form | Access is open-ended, or the expiry is disclosed after submission |
| **D-c** | **Neither issued role is Administrator** — Front Office and Physician only | An admin credential is issued, which also destroys the differentiator (`MarketingWebsite.md` §5) |
| **D-d** | **"Book a walkthrough" remains the single primary CTA on every page**; the credential is offered *within* that booking, never instead of it | The trial becomes its own CTA, or appears on a page without the walkthrough |

**If any of the four fails, it is Reading A** and all four decisions must be reopened in writing
before anything is designed. Add D-a…D-d to M5's §4 checklist as check 6.

##### The qualifying form — five fields, and it does three jobs at once

WEB-001 caps forms at five fields. Five is enough, and the right five discharge more than
qualification:

| # | Field | Serves |
|---|---|---|
| 1 | Payer mix — *mostly self-pay / mostly insurance* | GTM §5.1 positive signal · §5.2 negative signal |
| 2 | Setting — *outpatient only / includes beds or inpatient* | §5.4 disqualifier; §32 item 1 |
| 3 | Providers — *1–2 / 3–15 / 16+* | §5 ICP band |
| 4 | **"Do you need this system to issue your tax invoice or submit insurance claims?"** | §5.2 · **and Challenge 4's invoicing boundary** |
| 5 | Clinic name + work email | Contact |

**Field 4 is the one to notice.** §40 row 7 requires the invoicing boundary to be *spoken before any
billing screen appears*, and Challenge 4 exists because self-serve has no speaker. Asking it as a
qualifying question **puts the boundary in front of the visitor before they ever log in** — earlier
than a presenter would have said it, and in a form the visitor has to answer rather than hear. One
field closes the hardest part of Challenge 4.

**Three further consequences, all of them wanted:**

- **RDY-0065 gains its consumer.** RDY-0065 (*"Qualification checklist operationalising GTM §5.1 /
  §5.2"*) is **one of the nine open P0 items** and gates **G3 and G6** (`…md:1089`). The form *is*
  that checklist, operationalised — so Challenge 1's solution advances a P0 rather than adding work
  beside it.
- **Self-disqualification becomes measurable.** A visitor answering *"mostly insurance"* or *"yes"*
  to field 4 routes to an honest not-a-fit page. That is GTM §29's **success metric**, instrumented
  — and M4's hard constraint forbids treating it as a leak.
- **Nothing here needs development.** A form, a routing rule, an expiry, and two existing accounts.

##### Drop-in text — insert as step 0 of §5 Task 1

```
0. OWNER RULING — required before any agent is dispatched.
   M0 issues a reopening request naming DEM-001, WEB-001, GTM-001 and GTM-003
   (Product-Positioning…md:1230,1233,1236,1238), presenting the three readings and
   the D-a…D-d discriminator in §11.8.2, and recording:

     RULING:  [ A — reopen all four ] [ B — gated credential, no reopening ]
              [ C — published credential ] [ NEITHER — remain guided-demo only ]
     SEQUENCE: Challenges 2 and 3 ship BEFORE Challenge 1 is designed, so that
              DEM-001's own revisit trigger ("seeding automation ships") has fired.
              [ CONFIRMED ] [ OVERRIDDEN — reason: ................ ]
     SIGNED:  Owner ................  DATE ..........

   No dispatch proceeds until this is recorded. On ruling B, M5 adds D-a…D-d to
   its §4 checklist as check 6 and BLOCKs any deliverable failing one.
   On NEITHER, Challenges 2 and 3 still proceed — they are demo hygiene and
   depend on no ruling.
```

##### DIVERGENCE from §12.5 — recorded, not smoothed

**§12 reaches a different answer on this exact question, and the Owner should see both.** §12.5
frames the decision as a reopening and recommends **`LIMITED REOPEN`**; §11.8.2 argues **no
reopening is required** under Reading B with correct sequencing.

| | §12.5 | §11.8.2 |
|---|---|---|
| Framing | A reversal of DEM-001 that must be authorised | Inside DEM-001, once its own revisit trigger fires |
| Basis | The four decisions are locked and the premise conflicts with them | **DEM-001's revisit trigger is *"multi-tenancy or seeding automation ships"*** (`Product-Positioning…md:1230`), and Challenges 2–3 **are** seeding automation |
| Owner is asked for | `REOPEN` / `DO NOT REOPEN` / `LIMITED REOPEN` | A ruling among three readings, plus confirmation of sequence |
| Cost if right | One authorisation | Zero reopenings, one trigger fired as designed |
| Cost if wrong | A reopening that was not needed — cheap, and reversible | **A locked decision reversed without authorisation — the R-02 failure this whole charter exists to prevent** |

**The asymmetry decides how to treat the disagreement.** §12.5 is wrong-safe; §11.8.2 is
wrong-expensive. **So put §11.8.2's reading to the Owner as an argument, and take §12.5's decision
instrument as the default if the Owner does not accept it.** The two step-0 blocks are compatible:
add §11.8.2's three readings and the D-a…D-d discriminator as options *inside* §12.5's decision
record, rather than running two gates.

**Where the two converge is more striking than where they differ.** Both recommend the same
artefact — manually or form-gated, time-limited, **Front Office + Physician, never Administrator**,
small cohort first. They disagree only about whether building it needs permission.

**One improvement adopted from §12.11.** Its suggested form includes *"confirmation that no real
patient data will be entered"* — a field §11.8.2 missed and a shared demo database genuinely needs.
**Add it as field 6**, and accept that this takes the form to six. §12.11 is right that WEB-001's
five-field cap should be treated as a **test hypothesis rather than a hard constraint** where a
safety confirmation is what exceeds it; that reading is §12's AUD-COM-14 applied correctly. The
reverse trade — dropping a qualification field to stay at five — would sacrifice RDY-0065's purpose
to a formatting rule.

**Where §11.8.2 does not defer:** §12.11's other five fields (name, clinic, email/phone, role) are a
*contact* form, not a *qualification* form. They collect; they do not qualify, and GTM §20 puts
qualification before the demo while RDY-0065 requires the checklist to exist. The payer-mix,
setting, provider-band and invoicing questions are the ones doing that work — field 4 in particular,
which relocates §40 row 7's spoken boundary to before the visitor logs in.

---

**Note what remains true under every ruling.** Challenges 2 and 3 are unaffected: a demo that shows
an empty calendar is broken whether or not anyone self-serves into it. **They should be scheduled
regardless of the answer**, which is why they are the right work to start.

---

#### 11.8.3 F-2 — replacement acceptance block for §5 Task 1

Replace the Acceptance list in §5 Task 1 with this, which makes the classification binding instead
of decorative:

```
Acceptance.
- Every challenge carries M0's D1 classification and the reason for it.
- Each challenge classified GENUINELY OPEN has ≥3 ranked options with cost,
  time, dependency, what it would break, and a confidence.
- Each challenge classified EVIDENCE-DETERMINED carries the file:line, command or
  document section that determines it, and the answer it determines. It is
  answered and closed. Producing options for it is a failed deliverable, not a
  thorough one.
- Each challenge classified CONSTRAINT-DETERMINED carries the §32 item number or
  locked decision ID that governs, and the reopening request if one is required.
- Challenge 2 carries an explicit instruction to re-verify on demo.skyeagle.uk
  first, and to check pc_startTime before scheduling anything (§11.5).
- Challenges 2 and 3 are treated as ONE scheduled job.
- No option requires a capability the audit does not record as Active or Disabled.
- M5 returns zero unresolved BLOCKs.
```

The removed sentence is *"Every challenge has ≥3 ranked options"*. The replacement says the same
thing for the class where it is correct, and says the opposite where D2 requires it.

**Delete, in the same edit, the parenthetical in Method step 1** — *"(Expected: Challenge 1 is
genuinely open; 2 and 3 are largely evidence-determined…)"*. A classifier given the answer in
writing is not classifying. Move the sentence into §11.8-style commentary if it is worth keeping,
never into the brief.

---

#### 11.8.4 F-3 — restoring checkability to §7 without restoring the reading cost

Rule F's economy is worth keeping. The fix is not "make agents read the corpus" — it is **make §7
carry its own receipts**, which costs one page.

##### (a) Add a stamp at the head of §7

```
FACTS CURRENT AS OF 2026-08-20. Every row in §7.5–§7.8 carries a basis in §7.10.
An agent finding a §7 fact it can disprove reports that as a finding — §7 is
evidence, and Rule G applies to it exactly as to any other finding.
```

##### (b) Add §7.10 — the basis block. These are the citations; they exist

| §7 fact | Basis |
|---|---|
| §32 = 30 prohibited categories | `Marketing-MVP…md:12022-12056` |
| Frequency-figure ban | `…md:12048` (§32 item 26); hold survives RDY-0088's closure, `…md:3588` |
| 15 Safe Now / 10 Safe With Qualification | `Product-Positioning…md:601, 620`; MSG-002 at `:1225` |
| Roles 4 × 65 | `HISModulesUsers.md:309, 705` |
| Arabic 47.5% | `HISModulesUsers.md:746, 2574` |
| 55 reports, 10 disabled, no BI | `HISModulesUsers.md:3117` |
| 18 forms registered — **and 17, not 16, unregistered** | `HISModulesUsers.md:115, 337` *(F-17)* |
| 9 open P0 + the nine IDs | `Marketing-MVP…md:319, 3574` |
| G6 NOT READY, bottleneck is G3 | `…md:3583-3590` |
| Registers 47 / 27 / 18 / 60 | `…md:757, 1091, 6406` |
| Demo data 30 / 72 / 37 / 6 | `…md` §1.2 |
| SS-01…SS-12; recording 170,671 B | `…md:3565-3570`; `ls docs/evidence/captures/2026-08-19/publication-ready/` → 13 files |
| Page rows 21/5/2/1/1 of 30 | `…md:3581` *(F-13)* |
| Hosting Dammam / `me-central2` | `…md:1083` (RDY-0064 CLOSED) |
| Arabic message design PROVISIONAL | `…md:1133` (RDY-0089); GTM §17.6 |
| Nine dossiers unverified, 7 in-tier | GTM §31; §32 COMP-001; R-12 |
| R-02 High/Severe · R-08 Medium/High | GTM §30 |
| Self-disqualification a success metric | GTM §29 row 2 |
| Appointment date state | **See the corrected §7.8 below** |

##### (c) Replace §7.8's Challenge 2 with this — it is the one stale item

```
2. Seeded data is date-anchored, and the anchor is manual.

   The seed dated all 37 non-recurring appointments to a single day, 2026-08-14.
   Today-filtered screens rendered empty and the Flow Board showed
   `Total patients: 0` — observed on the LOCAL instance, 2026-08-19
   (Marketing-MVP…md:3318, PB-441).

   That symptom was then fixed, manually. PB-454 shifted the 37 rows forward five
   days; `SELECT COUNT(*) … = CURDATE()` returned 16; SS-06 and SS-07 were then
   captured live against the re-anchored data (Marketing-MVP…md:1143, PB-461…467).

   The structural gap is unchanged, and IT is the item: the shift was a one-off
   `+5 DAY` UPDATE — not a re-base to CURDATE(), and not on a schedule — so the
   anchor decays one day per day. It had already decayed by 2026-08-20.
   §16.2's requirement (re-base on every reset) remains unwired.

   Two checks before anything is scheduled:
     (i)  re-verify on demo.skyeagle.uk, not the local instance;
     (ii) confirm PB-454's statement did not damage `pc_startTime`, a TIME column
          (sql/database.sql:8281) — see §11.5.
```

##### (d) Add one line to duty D1

```
D1 also re-verifies any §7 fact the task depends on, against its §7.10 basis, and
records the result. A fact that has moved is recorded as moved, not silently used.
```

This is the whole fix. It restores Rule G to the evidence base, keeps Rule F's cost saving intact,
and converges with §10's AUD-COM-11 and AUD-COM-15 — which reach the same place by way of a
freshness policy. **Both are worth having: §11.8.4 makes the current facts checkable; AUD-COM-15
stops them going stale again.**

---

#### 11.8.5 F-6, F-8, F-9, F-10, F-14, F-17 — exact edits

| Finding | Locate | Replace with |
|---|---|---|
| **F-6** | §3 Rule F, first sentence | *"Every agent reads §7 of this file instead of the 19,000-line corpus. **One exception: M5 additionally reads `Marketing-MVP…md` §32 in full, because it must cite item numbers a summary does not carry.**"* |
| **F-8** | §5 Task 2, Method | Insert as step 3: *"**M4 attacks M6's per-layer verdicts adversarially** — brief is 'find why this audit is wrong', on the same standard M5 applies to generators. M4 runs at a tier ≥ M6's."* Add to §8's row 4 control: *"M6 reviewed by M4."* |
| **F-9** | §5 Task 1, Method | Re-order: *"**1. M1 fires first** and produces a frozen evidence annex. **2.** The identical annex is attached to both M2's and M4's briefs — D4 records that it was identical. **3.** Silent independent round."* (This is §10's AUD-COM-10 remedy; the two audits agree on it) |
| **F-10** | §6 mechanics table | Add three rows: *Agent type — `general-purpose` for M0–M7 · Model parameter — `fable` (M0), `opus` (M1–M7), fallback `sonnet` · **Never `Explore`** — it is read-only and cannot write its own deliverable, breaching Rule E* |
| **F-14** | §7.1, after the first sentence | *"**Open:** whether the site is branded Thiqa or SkyEagle is undecided — `MarketingWebsite.md` §13.8 Q2. No agent may assume a resolution; a deliverable depending on one states the assumption."* |
| **F-17** | §7.6 / any forms claim | Use **17**, not 16, uninstalled forms — `HISModulesUsers.md:115` records 35 on disk, 18 registered. Under §1's Standing Rule the audit wins over GTM MC-12. **Flag the discrepancy to the GTM owner; do not edit the GTM from here** (Rule E) |

---

#### 11.8.6 F-5 — the parity invariant, rebuilt so it does not need a capability claim

The invariant's *intent* is right and should survive. Its *formulation* depends on an ordering §2.1
disclaims. Both problems go away with a restatement that ranks by availability rather than by
asserted capability:

```
INVARIANT (replacement for §2.2).

  M5 — and M6 where technical work is reviewed — runs on the strongest model
  authorised AND available at dispatch time, and never on a model below any
  generating agent in the same dispatch.

  The comparison is made on the ordering in §2.1, which is an authorisation
  ordering. It is used here as a FLOOR, not as a capability claim: it establishes
  that the reviewer was not deliberately under-resourced relative to what it
  reviews. It does not establish that the reviewer is strong enough in absolute
  terms, and no ordering in this document can establish that.

REMEDIES when the strongest model is unavailable:
  R2  HOLD the dispatch.  ← default
  R1  Downgrade every generator to match M5, preserving the floor.
      PERMITTED ONLY for items M0 classified as neither customer-facing nor
      decision-feeding. R1 preserves the ratio while lowering the review in
      absolute terms; that is acceptable for internal research and not for
      anything that reaches a page or a decision.
  R3  Generators above M5.  PROHIBITED, without exception.
```

**The Owner ruling this needs is one sentence:** is §2.2 a *relative* control (nobody is
deliberately under-resourced) or an *absolute* one (M5 must be strong enough)? The text above
implements the relative reading explicitly and says so — which is honest, and better than the
current text implying the absolute reading while delivering the relative one.

**§10's AUD-COM-03 attacks the same invariant from the other side** — it gates before dispatch, and
the failure it names happens during. Both remedies are needed and they compose: add AUD-COM-03's
post-flight quarantine, and this restatement fixes what the gate compares.

---

#### 11.8.7 F-11 and F-12 — the two that need a control, not a sentence

##### F-11 · make the competitor report readable, once

The report is the defining input for M1 and M7, and whether an agent can read it is a property of
that agent's runtime (§11.3 F-11, as amended). Do not leave that to chance:

```
1. Extract once, on any host with poppler:
     pdftotext -layout OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.md.pdf \
               OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.txt
2. Commit the .txt beside the .pdf.
3. Point §7.9 at the .txt; keep the .pdf as the authoritative original.
4. Add to §6.2's single-agent preflight, for M1 and M7 only:
     "Confirm the competitor source is readable in this runtime before dispatch.
      If it is not, the dispatch is HELD, not attempted."
```

One extraction, committed once, removes the failure mode permanently and makes every quotation in
this folder re-checkable by anyone.

##### F-12 · make write isolation detectable today, preventable later

**Detective control — works now, needs nothing installed.** The Orchestrator brackets every
dispatch:

```bash
# before dispatch
git status --porcelain > /tmp/pre-dispatch.txt

# after dispatch
git status --porcelain > /tmp/post-dispatch.txt
diff /tmp/pre-dispatch.txt /tmp/post-dispatch.txt \
  | grep '^>' | grep -v 'docs/Marketing Website/committee/' \
  && echo "RULE E BREACH — files changed outside committee/"
```

Any output is a Rule E breach, named, with the file. M0 records it in §9 of the pack as a dispatch
that partly failed — which is exactly what the template already requires and currently has no way
to detect.

**Preventive control — install when convenient.** A `PreToolUse` hook matching `Write|Edit` that
rejects any path outside `docs/Marketing Website/committee/` for the duration of a dispatch. Wire it
through the `update-config` skill rather than hand-editing `settings.json`; the exact matcher shape
belongs to that skill, not to this document.

**Amend Rule E's wording either way.** As written it claims to *implement* and *prevent*. Until a
hook exists it does neither, and a rule that overstates its own force is worse than one that admits
it is a convention:

> *"Rule E is a convention enforced by the Orchestrator's pre/post-dispatch diff, not by the
> runtime. Until the `PreToolUse` control is installed, a breach is **detected**, not prevented."*

---

#### 11.8.8 Order of application, and how to tell it worked

| Step | Action | Blocked by | Verification |
|---|---|---|---|
| **1** | Apply §11.8.1's five error fixes | Nothing | The five strings are gone |
| **2** | Apply §11.8.4 (a)–(d): stamp, §7.10 basis block, corrected Challenge 2, D1 duty | Nothing | Every §7 fact resolves to a basis; Challenge 2 names PB-454 |
| **3** | Apply §11.8.3 and §11.8.5 | Nothing | Task 1 acceptance is class-conditional; the "Expected:" parenthetical is gone |
| **4** | Extract the competitor report (§11.8.7) | A host with poppler | `wc -l` on the `.txt` is non-zero |
| **5** | Adopt the pre/post-dispatch diff (§11.8.7) | Nothing | A deliberate stray write is caught in a dry run |
| **6** | **Owner ruling on §11.8.2** and on §11.8.6's one sentence | **Owner** | The step-0 block is filled in and signed |
| **7** | Ship the scheduled reset + date re-base — **after §11.5's `pc_startTime` check, on `demo.skyeagle.uk`** | Step 6 only if the ruling is NEITHER | `today_events > 0` on two consecutive days without manual action |
| **8** | Dry dispatch with seeded defects, per §10.3 | Steps 1–7 | M5 finds the seeded defects; the diff catches a seeded stray write |

**Steps 1–5 need no decision from anyone and can start immediately.** Only step 6 requires the
Owner, and step 7 is the work that fires DEM-001's trigger and therefore should run whatever the
ruling is.

---

#### 11.8.9 What these remedies deliberately do not do

- **They do not authorise the self-serve trial.** §11.8.2 supplies the instrument and a
  recommendation; the ruling is the Owner's and is unmade.
- **They do not close RDY-0065.** The five-field form is a *design* for the checklist that RDY-0065
  requires; the item closes on its own acceptance criteria — used on three consecutive calls with a
  recorded in/out decision each time — not on a form existing.
- **They do not settle §11.5's `pc_startTime` hazard.** That needs the query run against
  `demo.skyeagle.uk`, and this audit could not run it.
- **They do not amend the charter.** No text above has been applied to §1–§9. Every edit is
  proposed, located and quoted so the Orchestrator can apply it in one pass once the Owner rules.
- **They do not supersede §10.** Where §10 proposed a remedy for a shared finding — AUD-COM-10 on
  M1's sequencing, AUD-COM-11 on the date state, AUD-COM-03 on the parity gate — the two are
  compatible and §11.8 says so at each point rather than offering a competing version.

---

**Status of this section.** Independent review, appended to the record. **It closes no RDY item,
alters no locked decision, and enacts no rule change.** Every remedy above is **PROPOSED — Owner
decision required**, on the same standing as §M0-E's own amendment mechanism. Consistent with Rule
D, it is appended rather than merged into the sections it critiques, so the charter as originally
written stays legible beside it.

---

## 12. Consolidated audit disposition — final section

Two independent audit records were appended during the same review window. They use different
finding IDs and emphasis, but their material conclusions converge. This final section preserves
both records and states the controlling combined disposition without rewriting either one.

### 12.1 Convergent blocking findings

1. **Task 1 is not authorised to proceed as written.** The self-serve premise reverses or conflicts
   with locked/deferred decisions and requires an explicit, written Owner ruling before dispatch.
2. **The charter is not dispatch-ready.** Runtime model/agent mapping, model-observation/parity
   handling, skill/tool availability and write-isolation enforcement require executable preflight
   controls rather than prose assurances.
3. **Task 1's firing set is incomplete.** Public shared credentials, a mutable shared database,
   scheduled resets, abuse prevention and rate-limiting require technical-security ownership; M6
   cannot remain idle unless another named role owns and signs those risks.
4. **The common briefing needs controlled, current inputs.** Volatile demo facts require dated
   host-specific evidence; canonical §32 and GTM §14 controls must be attached/version-checked;
   the competitor source needs a reliably readable working representation for M1/M7.
5. **The decision process needs repair.** Do not pre-state the “correct output” for an open round;
   freeze M1 evidence before isolated generation; make Rule G fields mandatory; add a terminating
   meta-review step; and remove incentives for performative objections.

### 12.2 Final disposition

| Item | Consolidated result |
|---|---|
| Governance concept | **APPROVE WITH REQUIRED CHANGES** |
| Current `active and dispatch-ready` claim | **REJECT** |
| Task 1 | **BLOCKED** until Owner authorisation, security ownership and firing-set/process repairs |
| Task 2 | **CONDITIONALLY USABLE** after executable capability/parity controls and adversarial-review closure |
| Internal research | **CONDITIONALLY USABLE** only when stamped `NOT CLAIM-REVIEWED`, barred from decision/customer use and run through capability preflight |
| Customer-facing or decision-feeding output | **BLOCKED** until the P0 controls in both appended audits are resolved or explicitly accepted by the Owner in writing |

### 12.3 Record status

This consolidated disposition and the proposed solutions below form the final audit chapter. They
close no RDY item, change no locked decision, authorise no self-serve trial and enact no proposed
rule amendment. The two preceding audit records remain intact as the evidence and dissent trail.

### 12.4 Recommended blocker-resolution plan

The following is the cheapest safe sequence. Each gate has a binary exit test so “addressed” cannot
be confused with “resolved.”

| Order | Blocker | Proposed solution | Owner / executor | Exit test |
|---|---|---|---|---|
| **1** | Self-serve motion lacks authority | Issue the decision record in §12.5. Do not design automatic access before the ruling | Owner decides; M0 records | A dated `REOPEN` or `DO NOT REOPEN` decision names `DEM-001`, `WEB-001`, `GTM-001` and `GTM-003` and states scope |
| **2** | Capability/readiness is asserted, not proven | Run the preflight in §12.6 before every dispatch | Orchestrator runs; M0 records | Every required row is `PASS`; a `FAIL` prevents dispatch |
| **3** | Silent model fallback defeats parity | Replace D3 with the two-stage gate in §12.7 | Orchestrator supplies observations; M0 enforces | Both preflight and post-flight are `PASS`; all used outputs have observed models |
| **4** | Task 1 lacks technical-security ownership | Add M6 and use the security gate in §12.8 | M6 leads; M5 checks claim effects; Owner accepts residual risk | M6 returns `APPROVE` or `APPROVE WITH COMPLETED CHANGES`; no unresolved critical/high risk |
| **5** | Inputs and process can drift or anchor | Freeze the evidence bundle and use the revised sequence in §12.9 | M0 freezes; M5 verifies hash/control completeness | Every generator receives the same bundle; hashes and timestamps appear in the pack |
| **6** | Review templates do not enforce Rule G or terminate | Adopt §12.10's schema and one-pass meta-review | M0 and M5 | Schema validation passes and the pack reaches `FINAL` without recursive review |

### 12.5 Proposed Owner decision gate for Task 1

Add this as **Task 1, Step 0**, before classification of the four implementation challenges:

```text
STEP 0 — AUTHORISATION OF THE MOTION

Question: May the project reverse the DEM-001 deferral and expose the shared seeded demo as an
unattended self-serve trial?

Affected decisions: DEM-001 · WEB-001 · GTM-001 · GTM-003.

Owner decision (choose one):
[ ] REOPEN — permit design of an unattended trial, subject to M6 security approval and all gates.
[ ] DO NOT REOPEN — retain founder-led/guided access; redesign Task 1 as a request-and-schedule flow.
[ ] LIMITED REOPEN — permit only: ____________________, until: ____________________.

Rationale:
Date · Owner identity · route received:
```

**Recommendation:** choose `LIMITED REOPEN` first: manually issued, time-limited Front Office and
Physician credentials for a small invitation-only cohort. This tests demand and operational load
without immediately publishing reusable credentials or automating account issuance. Promotion to
open self-service should require the §12.8 security tests and measured evidence from that cohort.

### 12.6 Executable dispatch preflight

Replace the unconditional “dispatch-ready” status with this per-dispatch checklist:

| Check | PASS condition | Failure action |
|---|---|---|
| Model availability | Requested model is selectable now | Use an Owner-authorised fallback, then recompute parity; otherwise hold |
| Agent mapping | Each M-role maps to an available runtime agent type | Hold and record `NO EXECUTOR` |
| Skill status | Each named skill is `INSTALLED + AUDITED`, `OPTIONAL / NOT USED`, or replaced by a stated manual method | Never install during dispatch; hold if a required skill is absent |
| Tool access | Required repository, PDF, web/browser and test access is available | Narrow the brief or hold; do not imply verification |
| Canonical controls | Current §32 and GTM §14 snapshots are attached with hash and source timestamp | Regenerate bundle; no dispatch on mismatch |
| Evidence freshness | Live-state evidence meets §12.9 freshness rules | Recheck or mark `STALE`; stale facts cannot determine a decision |
| Write isolation | Per-agent output path exists and is uniquely assigned | Create/assign safely before dispatch; shared writes remain prohibited |
| Decision authority | Any reopening/override is present in writing | Hold until Owner decision |

The M0 record should contain the checklist, evidence and `PASS / FAIL`; a prose statement that the
environment “should” support a model or tool is not a pass.

### 12.7 Replacement parity control

Replace current D3 with two gates:

```text
D3a — PRE-FLIGHT PARITY
Record intended model and tier for every generator and adversary. Compute the invariant. Dispatch
only on PASS. This is provisional and does not authorise use of output.

D3b — POST-FLIGHT PARITY
After execution, record the model actually observed for every fired agent and recompute the
invariant. Until D3b passes, all output is QUARANTINED and may not be shown to another agent, cited,
merged, or used in a decision pack.

UNOBSERVED = INVALID by default. A written Owner exception may release it only as explicitly
unverified evidence; it may not be represented as parity-compliant.
```

If a generator ran above the reviewer tier, the cheapest remedy is to discard that generator output
and rerun the whole independent generation set at the reviewer's available tier. Do not merely rerun
M5 after it has seen the stronger generator's output; that does not recreate independent parity.

### 12.8 Technical-security solution for Task 1

Add M6 to Task 1 and require these tests before any external visitor receives access:

1. **Credentials:** dedicated non-admin demo accounts; least privilege; no credential in screenshots,
   repository history or analytics; rotation and revocation procedure tested.
2. **Access mode:** start with manual, time-limited issuance. Do not publish a permanent shared
   credential. Automatic issuance is a later option after abuse/load evidence exists.
3. **Edge controls:** rate-limit login and credential-request endpoints; bot protection; bounded
   retries; alerting that does not expose clinical-screen content.
4. **Shared-data disclosure:** prominent notice before entry that the environment is shared,
   synthetic, reset periodically and unsuitable for real personal/clinical data.
5. **Synthetic-data invariant:** automated pre- and post-reset checks for prohibited real data and
   expected seed counts. Any failure closes access automatically.
6. **Reset safety:** single-instance lock, transactional/idempotent reset where possible, date rebase
   in the same job, health check before reopening, rollback baseline, and logged result.
7. **Concurrency:** test two users in different roles editing/viewing the same seeded patient during
   reset boundaries; document leakage, collision and stale-session behaviour.
8. **Route controls:** block administrative/global-configuration routes not required by the two-role
   proof; convert §40 spoken warnings into pre-route notices or hard restrictions.
9. **Telemetry boundary:** no session recording or clinical-UI analytics on the demo. Collect only
   minimal operational/security events until privacy review approves more.
10. **Kill switch:** one documented action disables new access and revokes issued credentials without
    waiting for a deployment.

M6's verdict must be `APPROVE`, `APPROVE WITH CHANGES`, or `REJECT`. “Synthetic data” alone is not a
security approval: abuse, reputational damage, configuration mutation and credential compromise
remain possible without real PHI.

### 12.9 Controlled evidence bundle and revised Task 1 sequence

M0 should generate one immutable dispatch bundle containing:

- canonical readiness §32 and GTM §14, each with file hash and last-modified time;
- the exact locked decisions implicated by the task;
- a text-searchable export of the competitor PDF that preserves page numbers, plus the original PDF;
- current, host-specific demo observations with observer and UTC timestamp;
- installed model/agent/skill/tool inventory from the preflight;
- task-specific evidence only, clearly separated from hypotheses and proposed policy.

Freshness defaults: live demo state rechecked within 24 hours and immediately before public traffic;
model/tool availability at each dispatch; competitor facts rechecked before publication; readiness
status taken from the current canonical register; code facts pinned to a commit. M5 may shorten a
window when consequences are high. Expired evidence is labelled `STALE` and cannot be
evidence-determining.

Use this revised Task 1 order:

1. Owner authorisation gate (§12.5).
2. Capability and parity preflight (§12.6–§12.7 D3a).
3. M1 research only; M5 checks source quality and separates observations from inferences.
4. M0 freezes and hashes the common evidence bundle.
5. M2 and M4 receive the identical bundle and answer independently. The existing cheap pattern is
   labelled `INCUMBENT HYPOTHESIS`, not “the correct output.”
6. M6 independently threat-models every viable option before seeing M2/M4 preferences.
7. Post-flight parity gate (§12.7 D3b).
8. Simultaneous publication, then M5 claim/constraint review and M6 technical adversarial review.
9. M0 reports convergence and divergence without choosing; Owner decides open items.
10. One-pass meta-review and finalisation (§12.10).

### 12.10 Rule G schema, M5 incentives and review termination

Every proposed option, finding, objection and recommendation should use:

```text
ID:
TYPE: OBSERVED | INFERRED | PROPOSED | UNKNOWN
STATEMENT:
BASIS: file:line, rerunnable command, or dated URL
FALSIFIER: the specific observation that would overturn it
CONFIDENCE: High | Medium | Low — with reason
AUTHORITY: LOCKED | EVIDENCE-DETERMINED | OWNER POLICY | TEST HYPOTHESIS
IMPACT IF WRONG:
STATUS: PASS | REQUIRED CHANGE | BLOCK | OPEN OWNER DECISION
```

Customer copy is not forced into that prose format; each sentence instead carries an `MC-*` or
`CLM-*` trace, while its separate review record uses the schema.

Replace M5's objection quota with: **“M5 must demonstrate complete test coverage. Zero findings is
valid when supported. Reviewer performance is tested with known seeded defects and later escaped
defects, not the number of objections raised.”**

Add a terminal pack section:

```text
10. META-REVIEW — M5 reviews M0's §9 evaluation once.
M0 may correct an objective transcription/citation error but does not evaluate this meta-review.
Any substantive disagreement is recorded and routed to the Owner. After that record, the pack is
FINAL; no recursive review is created.
```

### 12.11 Practical solution options for the self-serve challenges

These are candidates for the repaired Task 1, not enacted decisions:

| Challenge | Recommended first option | Safer fallback | Do not use initially |
|---|---|---|---|
| Login wall | Manual approval; issue two time-limited least-privilege accounts from a short qualification form | Booked guided walkthrough with the same two-role proof | Permanent credentials printed publicly |
| Frozen dates + shared DB | One locked scheduled job: close access, reset baseline, rebase date-relative data, validate counts/screens, reopen only on PASS | Daily supervised reset during invitation-only pilot | Independent reset and date jobs that can drift or overlap |
| Visitor collisions | Small invitation cohort, short access windows, clear shared-environment notice and frequent verified reset | Guided single-prospect windows | Claiming isolation or multi-tenancy |
| Missing presenter | Restrict dangerous routes; display mandatory qualification before the relevant route; link to a concise guided checklist | Recorded walkthrough plus request-access CTA | Relying on notices where a route can mutate global configuration |
| Lead data | Store the minimum fields in an Owner-approved location; publish retention, access and deletion rules; do not place lead data in analytics events | Email/phone request handled manually until the location is approved | Sending identifiable lead data to an unreviewed global form/analytics service |

Suggested qualification form: name, clinic/organisation, work email or phone, role, and confirmation
that no real patient data will be entered. The “five fields” rule should remain a test hypothesis,
not a hard charter constraint; privacy minimisation and qualification value govern the final form.

### 12.12 Closure checklist and resulting status

| Finding group | Closed only when |
|---|---|
| Authorisation | The Owner record in §12.5 is complete and cited in Task 1 |
| Security ownership | M6 is in the firing set and its required tests have evidence and a usable verdict |
| Dispatch capability | Every §12.6 preflight row passes for the actual dispatch |
| Parity | D3a and D3b pass using observed runtime models; no used output remains `UNOBSERVED` |
| Canonical inputs | Hash-checked §32/GTM §14 and current evidence bundle are attached |
| Independence | M2/M4 received identical frozen evidence; no prior proposal was exposed |
| Rule G | Schema check reports zero missing basis/falsifier/confidence fields |
| Review closure | M5/M6 blocks are resolved or explicitly overridden in writing; one-pass meta-review is final |
| Demo readiness | Reset/rebase, two-role access, route controls, concurrency, rate limits and kill switch pass |

When every row passes, the charter status may move from **NOT OPERATIONALLY READY** to
**READY FOR A CONTROLLED INVITATION-ONLY PILOT**. It should not move directly to unrestricted public
self-service. That later step requires pilot evidence on abuse, support load, reset reliability,
conversion quality and whether shared-state interference remains acceptable.
