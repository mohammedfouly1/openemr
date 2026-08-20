# THIQA — MARKETING SKILLS, PLUGINS AND AGENTS

**Companion file to `MarketingWebsite.md`.** Created 2026-08-20 by transferring the tooling
inventory out of that document, so the website plan stays about the website and the tooling
question has one place to live.

**Scope:** what marketing tooling exists in this environment, what exists externally, what is
worth installing, and how any of it is allowed to be used on this project.

---

## 1. The rule that governs everything in this file

The competitor intelligence report reached this conclusion independently, and it is the single
most important sentence for anyone importing outside marketing tooling here:

> *"A website built by imitating the market convention would consist almost entirely of claims
> this product is forbidden to make. The benchmark set must therefore be used for **technique,
> not for content**."*
> — `OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.md` §1.1

**Extend that rule from benchmarks to tooling.** Every skill pack below was written for products
that may freely say *"the leading, comprehensive, all-in-one platform, HIPAA-compliant, trusted by
500+ clinics."* Thiqa may say none of it — §32 of the readiness document prohibits the adjectives,
the compliance language, the manufactured trust and the customer counts, and there are no
customers to count.

**So: import structure, sequence, research method and testing discipline. Never import copy.**

Anything an external skill produces is a **draft**. It passes through the claim-review procedure
(`EV-003`, RDY-0003) against §32 before it becomes site content. No skill in existence knows §32
exists; every one of them will generate prohibited language by default.

---

## 2. Installed in this environment — the honest inventory

Enumerated 2026-08-20.

| Scope | What is present | Marketing-relevant? |
|---|---|---|
| Session skills | `design`, `dataviz`, `artifact-design`, `artifact-diagramming`, `artifact-capabilities`, `frontend-design`, `code-review`, `security-review`, `run`, `claude-in-chrome`, `schedule`, `loop`, `init`, others | **Indirectly.** `frontend-design` and `design` help *build* the site; `dataviz` helps present evidence. None does marketing strategy |
| User skills (`~/.claude/skills`) | `developing-with-streamlit` | No |
| Installed plugins | `example-skills@anthropic-agent-skills`, `frontend-design`, `chrome-devtools-mcp`, `playwright` — all scoped to a **different** project (`whatsapp project`) | Partly |
| Plugin marketplaces registered | `anthropics/claude-plugins-official`, `anthropics/skills` | Neither carries a marketing pack |
| Available agents | `claude`, `claude-code-guide`, `Explore`, `general-purpose`, `Plan`, `statusline-setup` | **No marketing agent exists here** |

### 2.1 Two findings worth stating plainly

**There is no marketing skill, plugin or agent installed in this project. Not one.** Every
marketing decision made so far — the positioning, the ICP, the claim register, the competitor
scorecard, the four value pillars — was produced without any, from primary research and the
audited capability set. The absence has not been the bottleneck.

**The cached `brand-guidelines` skill is a trap here.** It sounds exactly right and is not: it
applies *Anthropic's own* brand identity — Poppins/Lora typography, `#d97757` orange. Thiqa has a
validated identity of its own (`brand/tokens/thiqa-tokens.json`, Inter + IBM Plex Sans Arabic,
38 contrast pairs at **0 WCAG failures**). Running it would overwrite a validated system with an
unrelated one. **Do not install it for this project.**

---

## 3. External skill packs — assessed

Located by search of the public GitHub ecosystem, 2026-08-20. **None is installed. None is
audited. All are third-party.**

| Pack | Scale | Licence | Install | Assessment |
|---|---|---|---|---|
| **`coreyhaines31/marketingskills`** | 60+ skills | MIT | `npx skills add coreyhaines31/marketingskills` | **Strongest candidate.** Authored by a known B2B SaaS marketing practitioner. `product-marketing` is a foundation skill the others read |
| **`ekinciio/saas-growth-marketing-skills`** | 15 skills + 3 orchestrator agents | MIT | curl script, or git clone + `pip install -r requirements.txt` | **Second strongest — and one skill maps onto an open blocker.** See §4 |
| **`OpenClaudia/openclaudia-skills`** | 34 skills | MIT | `npx openclaudia install --all` | Useful overlap; broader and shallower than the first two |
| **`thatrebeccarae/claude-marketing`** | Per-tool skill packs | — | — | **Not applicable.** Built around Klaviyo, Shopify, GA4, Looker Studio and paid media — a stack this project does not have, and GTM §19 deliberately deprioritises paid channels until the pricing page carries figures |
| **`hesreallyhim/awesome-claude-code`** | Index | — | — | The catalogue where the above are listed. Worth monitoring for what appears next |

**On forks.** `ayrshare/marketingskills`, `franciscocp2/marketing-skills` and
`syntax-syndicate/marketing-skills` carry byte-identical descriptions to the Haines pack and
appear to be forks. **Install the origin, not a fork** — a fork is an unaudited copy that drifts.

### 3.1 `coreyhaines31/marketingskills` — the relevant subset

| Skill | What it does |
|---|---|
| `product-marketing` | Positioning, messaging, battlecards, GTM. **Foundation skill the others read** |
| `copywriting` | Write, rewrite or improve marketing copy for any page |
| `copy-editing` | Edit and refine existing copy |
| `cro` | Optimise conversions on a page or form |
| `site-architecture` | Plan, map or restructure page hierarchy, navigation, URL structure |
| `offers` | Design and frame value propositions |
| `competitors` | Build competitor comparison pages |
| `customer-research` | Conduct and synthesise customer insight |
| `marketing-psychology` | Behavioural science applied to marketing |
| `seo-audit`, `schema` | Technical/on-page SEO, structured data |
| `marketing-ideas` | Strategy and inspiration for SaaS |

### 3.2 `ekinciio/saas-growth-marketing-skills` — full list

**Skills (15):** `geo-seo-auditor` · `aso-optimizer` · `landing-page-cro` · `subscription-metrics`
· `local-seo-optimizer` · `review-sentiment` · `plg-funnel-analyzer` · `onboarding-optimizer` ·
`retention-playbook` · `pricing-analyzer` · `competitor-intel` · `web-app-growth-engine` ·
`saas-landing-builder` · `reddit-opportunity-finder` · `brand-mention-scanner`

**Orchestrator agents (3):** `growth-strategist` · `launch-planner` · `metrics-analyst`

### 3.3 `OpenClaudia/openclaudia-skills` — the relevant subset

`write-landing` · `copywriting` · `product-marketing` · `page-cro` · `icp-builder` ·
`launch-strategy` · `content-strategy` · `schema-markup` · `programmatic-seo` ·
`competitor-analysis` · `pricing-strategy`

---

## 4. The one external skill that maps onto an open blocker

`ekinciio/saas-growth-marketing-skills` → **`pricing-analyzer`**, which performs
**Van Westendorp Price Sensitivity Meter** analysis.

That is not a loose capability match. The GTM names that exact instrument:

> *"8–10 buyer conversations including a direct pricing question and a **van-Westendorp-style
> range**."* — GTM §15.4, the minimum evidence needed to unblock **PRC-003**

PRC-003 (exact price points) is **BLOCKED**, and RDY-0069 (cost instrumentation) is one of nine
open P0 items. **The skill does not produce the missing evidence — only real buyer conversations
do.** What it does is standardise the analysis once those conversations happen, at exactly the
point where a hand-rolled spreadsheet usually goes wrong.

---

## 5. Skill-to-deliverable mapping

Which skill would actually serve which piece of work, if installed.

| Website deliverable | Candidate skill | Caution |
|---|---|---|
| Information architecture, URL structure | `site-architecture` (Haines) | The IA is **already locked** — GTM §17.2. Use to *check* the structure, not to redesign it |
| Homepage and flagship page copy | `copywriting`, `write-landing`, `saas-landing-builder` | Output is a draft. Every claim must trace to §14.1/§14.2 of the GTM and survive §32 |
| Page conversion review | `cro`, `landing-page-cro`, `page-cro` | Genuinely useful. But *self-disqualification is a success metric here* (GTM §29) — a CRO tool will read it as a leak and try to fix it. Do not let it |
| Pricing evidence analysis | `pricing-analyzer` | Only after real buyer calls exist. See §4 |
| Competitor comparison pages | `competitors`, `competitor-intel` | **Deferred by COMP-001.** Named-competitor content waits for the 9 unverified dossiers and a first customer |
| Trial funnel design | `plg-funnel-analyzer`, `onboarding-optimizer` | Directly relevant to the four self-serve challenges in `MarketingWebsite.md` §1–§4 |
| SEO and structured data | `seo-audit`, `schema`, `schema-markup` | Safe. Target the disqualification queries per GTM §19, not "HIS Saudi Arabia" |
| Buyer research synthesis | `customer-research`, `icp-builder` | The ICP is locked. Use for **V-1/V-2/V-3 interview synthesis** (RDY-0075/0076/0077) |
| Email sequences, ads, social | various | **Not yet.** GTM §19 deprioritises these until pricing figures exist |

---

## 6. Install discipline

1. **Install per-project, never globally.** A marketing pack must not leak into unrelated
   repositories.
2. **Read each `SKILL.md` before installing.** A skill is instructions that execute in this
   repository. MIT-licensed does not mean reviewed.
3. **Start with two, not five** — `coreyhaines31/marketingskills` for structure and copy craft;
   `ekinciio/saas-growth-marketing-skills` for `saas-landing-builder`, `landing-page-cro` and
   `pricing-analyzer`.
4. **Wrap every output in the claim review** (`EV-003`, RDY-0003) before it becomes site content.
5. **Do not install the analytics/ads packs yet** — GTM §19 deprioritises those channels for now.
6. **Never install `brand-guidelines` for this project** — §2.1.

---

## 7. Acknowledgements and licences

All packs below are **MIT-licensed** and independently authored. Listed with credit; none is
installed, and none has been audited by this project.

- Corey Haines — [`coreyhaines31/marketingskills`](https://github.com/coreyhaines31/marketingskills)
- [`ekinciio/saas-growth-marketing-skills`](https://github.com/ekinciio/saas-growth-marketing-skills)
- OpenClaudia — [`OpenClaudia/openclaudia-skills`](https://github.com/OpenClaudia/openclaudia-skills)
- [`thatrebeccarae/claude-marketing`](https://github.com/thatrebeccarae/claude-marketing) — assessed, not applicable
- [`hesreallyhim/awesome-claude-code`](https://github.com/hesreallyhim/awesome-claude-code) — the index, incl. [issue #1033](https://github.com/hesreallyhim/awesome-claude-code/issues/1033)
- Anthropic — [`anthropics/skills`](https://github.com/anthropics/skills) and
  [`anthropics/claude-plugins-official`](https://github.com/anthropics/claude-plugins-official), both cached locally

**The frameworks and practitioner sources** these skills encode — Dunford, Moore, JTBD,
Osterwalder, van Westendorp, and the healthcare / open-source / Arabic-RTL / self-serve-demo
literature — are credited in `MarketingWebsite.md` §10–§12, where the method itself lives.
