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

### 2.1 Model authorisation, and why the assignment matters

| Role | Model | Rationale |
|---|---|---|
| M0 Committee Head | **Fable 5** | Owner-authorised |
| M1–M7 | **Opus 5** | Owner-authorised |
| Fallback, any agent | **Sonnet 5** | Owner-authorised where Opus 5 is unavailable |
| M0 fallback | Opus 5, then Sonnet 5 | *Orchestrator's reading of the instruction — not stated explicitly. Correct it if wrong* |

**One consequence worth stating.** M5 is the adversary that can block a deliverable. **A reviewer
weaker than the thing it reviews rubber-stamps.** Because every agent runs on Opus 5, M5 is never
outmatched by what it audits. If a fallback ever puts a generator on Opus 5 while M5 drops to
Sonnet 5, **that dispatch is invalid** — either raise M5 or lower the generator. This is exactly
the failure `EV-095` named about itself: *"a determination that only ever agreed with the
commissioner is weak evidence precisely when it is most needed."*

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

**Mandate.** Chair the committee. Classify decisions under Rule A, run the rounds under Rule B,
compile the decision pack, record dissent.

**In scope:** classification; sequencing; convening the right subset of agents; challenging weak
reasoning; producing the decision pack for the Owner.
**Out of scope:** generating marketing content; overriding M5's veto; editing any shared document;
deciding a genuinely-open question itself.

**Deliverable:** `committee/M0-decision-pack-<task>.md` containing —
- the Rule A classification of every item, with the reason
- each agent's independent answer, **verbatim and unedited**
- M5's and M6's objections, verbatim
- convergences and divergences, clearly separated
- **a recommendation, explicitly labelled as a recommendation**, and the open question put to the Owner

**Hard constraints.** M0 never edits another agent's words. M0 never resolves a divergence by
choosing. M0 never presents a compromise position that no agent proposed.

**Acceptance:** the Owner can read the pack and make the decision without opening any other file,
and every dissent survives intact.

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

**CHARTER — proposed, not yet exercised.** No agent has been dispatched. Model authorisation is
recorded in §2.1 as received. Nothing here closes an RDY item or alters a locked decision.
