# THIQA — MARKETING METHOD, TOOLING AND SOURCES

*Added 2026-08-20. Records the marketing expertise, tooling and bodies of practice that will be
used to plan the messages and the site content — what is available locally, what exists
externally, which frameworks govern which decision, and who the work is indebted to.*

## 9. Marketing tooling — moved

**The tooling inventory now lives in its own file: [`Tools-and-Skills.md`](Tools-and-Skills.md).**

It records what marketing skills, plugins and agents are installed here (none), the five external
MIT-licensed skill packs assessed, a skill-to-deliverable mapping, and the install discipline that
governs their use.

**The one rule from that file that binds this one**, carried over from the competitor
intelligence report §1.1 — *the benchmark set must be used for **technique, not for content**.*
Extended to tooling: import structure, sequence, research method and testing discipline; never
import copy. Anything an external skill generates is a draft that passes the claim review
(`EV-003`, RDY-0003) against §32 before it becomes site content.

---

## 10. The method — which framework governs which decision

Named so the reasoning is inspectable, and mapped to the decision each one already shapes. **Most
of these are already in force in the locked strategy**; naming them here makes the inheritance
visible rather than implicit.

| Framework | Origin | What it governs here | Already applied? |
|---|---|---|---|
| **Positioning as a deliberate act** — competitive alternatives → unique attributes → value → best-fit customers → market category | April Dunford, *Obviously Awesome* (2019); *Sales Pitch* (2023) | The whole of GTM §10–§12. §23.1's strategic groups **are** the "competitive alternatives" step; §7's category ruling **is** the market-category step | **Yes — GTM §12** |
| **The positioning statement template** — *For [target] who [need], [product] is a [category] that [benefit]. Unlike [alternative], we [differentiator]* | Geoffrey Moore, *Crossing the Chasm* | GTM §12's internal statement is literally this shape, with a *because* clause appended for evidence | **Yes** |
| **Jobs-to-be-Done** — buy an outcome, not a product | Clayton Christensen; Tony Ulwick (ODI) | GTM §8's primary problem is written in the customer's words, not ours — *"cannot rely on its own clinical record"*. The job is **control**, not software | **Yes — GTM §8** |
| **Value Proposition Canvas** — jobs / pains / gains vs. relievers / creators | Alexander Osterwalder, *Value Proposition Design* | GTM §5.5's must-have / nice-to-have / **cannot-provide** table is a VPC with an added honesty column | **Yes — GTM §5.5** |
| **StoryBrand** — the customer is the hero, the vendor is the guide with a plan | Donald Miller, *Building a StoryBrand* | **Structure only.** Use the guide-not-hero framing and the "plan" device (implementation stages). **Do not** import its stakes-and-transformation copy patterns — they collide directly with §32's banned adjectives | **Partly — use with care** |
| **Van Westendorp Price Sensitivity Meter** | Peter van Westendorp (1976) | The instrument for PRC-003 / V-7 / RDY-0069 | **Named, not yet run** |
| **Message-market fit testing** — variant landing pages, measure the CTA | Standard CRO practice | GTM **V-4**: disclosure-led vs capability-led homepage, both languages | **Specified, not yet run** |
| **One primary CTA per page; qualification before capture** | CRO practice | GTM **WEB-001** already locks this: one CTA, pricing and exclusions *before* the form, no form over 5 fields | **Yes — WEB-001** |
| **Self-disqualification as a success metric** | Uncommon, and deliberate here | GTM §29 tracks it as a metric, not a leak. This is the mechanism §17.3's "what we don't do" section exists to serve | **Yes — GTM §29** |
| **WCAG 2.2 AA** | W3C | Contrast, focus order, RTL. The Thiqa token set is already validated — 38 pairs, **0 failures** | **Yes — `brand/qa/`** |

---

## 11. Domain practice — four bodies of knowledge that apply specifically

Generic SaaS marketing is not enough here. Four narrower fields apply, and in three of them the
standard advice must be **inverted or adapted** because of what this product may not claim.

### 11.1 Marketing regulated healthcare software

**What the field says:** every word, claim and CTA must survive compliance scrutiny; buyers are
multi-stakeholder (clinician, IT, procurement, owner) with long cycles; buyers weight peer
validation and regulatory signals above polished marketing; compliance-focused lead magnets and
stakeholder-specific material outperform generic content.

**Where it fits:** the multi-stakeholder finding matches GTM §6 exactly — six personas, and the
IT contractor holds a veto. Persona-specific content is therefore not a refinement, it is the
structure.

**Where it must be inverted.** The field's central advice — *lead with compliance signals and
certifications* — is **prohibited** here. §32 item 14 bans "certified", "compliant", "HIPAA",
"ONC certified"; item 12 bans everything Saudi-regulatory; item 25 bans certification badges.
**The substitution is verifiability in place of certification**: instead of a badge asserting a
third party checked us, publish the claim with its limitation attached and invite the buyer to
check it themselves. That is differentiator D-1, and it is the only version of "trust signalling"
available to this product.

### 11.2 Commercial open source

**What the field says:** move the offer from licence to services; customers pay for outcomes and
responsibility, not for code they could compile themselves; authenticity and transparency
outperform polish in this segment; freemium conversion is typically under 1%.

**Where it fits:** GTM-006 is exactly this model, and GTM §24 already carries the canonical answer
to the question every open-source-derived vendor must answer —

> *"Downloading it is the easiest 2% of the job. What we sell is the other 98%."*

**What it adds:** the sub-1% freemium benchmark is a caution for Challenge 1. If the trial becomes
a de-facto free tier rather than an evaluation step with a defined end, the funnel gets volume and
no conversions. **Time-box the trial.**

### 11.3 Arabic / RTL bilingual web — practice, not translation

The most actionable external research of this pass, because GTM WEB-003 mandates full parity but
does not say *how*. Consolidated practice:

| Practice | Why it matters here |
|---|---|
| **Native URL structure per language** — not a translation plugin | A plugin-translated site breaks direction and hurts load speed; native structure is what ranks on `google.sa` |
| **Modern Standard Arabic for B2B** | Universally read across the Arab world; conveys professionalism. Not dialect |
| **Latin digits (012), not Arabic-Indic (٠١٢)**, for Saudi B2B | Saudi business audiences generally expect Latin numerals in a professional context |
| **Equal content depth in both languages** | Saudi buyers toggle between Arabic and English *mid-session, on the same device*. A thinner Arabic site is visible immediately. **This independently confirms GTM R-08** and raises its practical stakes |
| **`dir="rtl"` + `lang="ar"` + CSS logical properties** | Flexbox/Grid mirror much automatically; edge cases are mixed AR/EN strings, form alignment and image-based navigation |
| **Flip what encodes direction** — back/forward arrows, chevrons, list indentation. **Do not flip** logos, media, clock icons, checkmarks, brand imagery | Standard RTL mirroring rule; wrong flips read as broken |

**The project-specific hazard, restated:** the *website* being fully bilingual must never imply the
*product* is. Product Arabic is **47.5%, chrome only**, with untranslated picklists and
unrenderable Arabic PDF. That limitation appears on the Arabic site **with equal prominence**
(GTM R-08), and a native speaker signs it off — which for the product disclosure text has already
happened (RDY-0087 CLOSED).

### 11.4 Self-serve demo and trial design

Directly relevant to Challenges 1–4. Consolidated practice:

| Practice | Application to the Thiqa trial |
|---|---|
| **Anchor to one outcome, never a feature tour** | Matches GTM §16.2's own rule. The outcome for the unaccompanied visitor is **§5's two-credential comparison** |
| **Remove useless friction, keep diagnostic friction** | The access form should qualify (payer mix, outpatient, provider count) — that is diagnostic. Do not add fields that merely collect |
| **Seed data that resembles the prospect's own** | Already specified — Saudi-configured facility, transliterated names, ophthalmology depth. **Challenge 2 is what breaks this**, and it breaks it on the first screen |
| **Pair a low-commitment path with the high-intent one** | "Watch the audit-integrity run" (the recording, exists today) beside "Try it yourself". Serves the early-stage visitor without spending a trial credential |
| **Publish a self-serve link alongside every guided session** | Multi-stakeholder deals need a version absent stakeholders can revisit — precisely GTM §6's six personas |
| **Benchmark: 3–5% trial-to-paid is healthy self-serve; 7%+ top quartile** | Context only. **No benchmark may be published as our own figure** — nothing has been measured (§32 item 25) |

---

## 12. Acknowledgements and sources

This work stands on other people's. Recorded so any claim in this file can be traced, and so
credit sits with its authors.

### 12.1 Project-internal sources — authoritative for anything about the product

| Source | Role |
|---|---|
| `docs/HISModulesUsers.md` | The audited capability catalogue. **Nothing about the product is asserted anywhere unless it traces here** |
| `docs/Product-Positioning-and-GTM-Locked-Strategy.md` | The locked positioning, ICP, pillars, claim register, website strategy |
| `docs/OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.md.pdf` | 26 competitors, 17 verified. The source of the technique-not-content rule |
| `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` | Readiness gates, §32 prohibited claims, §34 proof matrix, §40 demo no-go register |
| `docs/evidence/EV-067-published-registers.md` | The four status registers, publication-approved |
| `docs/branding-production/`, `brand/` | The Thiqa identity, tokens, typography, WCAG validation |

### 12.2 Frameworks and their authors

- **April Dunford** — *Obviously Awesome* (2019), *Sales Pitch* (2023). Positioning methodology.
  <https://www.aprildunford.com/books>
- **Geoffrey Moore** — *Crossing the Chasm*. The positioning statement template.
- **Clayton Christensen** and **Tony Ulwick** — Jobs-to-be-Done / Outcome-Driven Innovation.
- **Alexander Osterwalder** — *Value Proposition Design*. The Value Proposition Canvas.
- **Donald Miller** — *Building a StoryBrand*. Narrative structure (structure only, see §10).
- **Peter van Westendorp** — Price Sensitivity Meter (1976).
- **W3C** — WCAG 2.2.

### 12.3 Open-source skill packs

**Moved.** Full inventory, licences and assessment: [`Tools-and-Skills.md`](Tools-and-Skills.md) §3
and §7. All MIT-licensed, all third-party, none installed.

### 12.4 Practitioner writing consulted for §11

Positioning frameworks: [Twenty-One-Twelve](https://www.twenty-one-twelve.com/post/positioning-frameworks-for-saas-companies) · [Genesys Growth](https://genesysgrowth.com/blog/product-positioning-frameworks-complete-guide) · [PitchKitchen](https://www.pitchkitchen.com/blog/best-strategic-messaging-frameworks-for-b2b-saas-companies) · [The Starr Conspiracy](https://www.thestarrconspiracy.com/insights/faqs/b2b-messaging-positioning-framework-faq)

Regulated-healthcare marketing: [SaaS Hero](https://www.saashero.net/strategy/healthcare-saas-marketing-strategies/) · [Insivia](https://www.insivia.com/expertise/saas-marketing-agency-consultant/guide/founder-insights/building-saas-for-complex-regulated-or-high-stakes-markets/) · [HTD Health](https://htdhealth.com/insights/healthcare-saas-market-overview-and-implementation-strategies/)

Commercial open source: [TODO Group](https://todogroup.org/resources/guides/marketing-open-source-projects/) · [Product Marketing Hive](https://www.productmarketinghive.com/go-to-market-strategy-for-open-source-products/) · [Cognidox](https://www.cognidox.com/blog/2011/06/pricing-and-the-open-source-business-model)

Arabic / RTL practice: [namla](https://namla.sa/en/resources/blogs/bilingual-arabic-english-app-rtl/) · [Linguidoor](https://linguidoor.com/arabic-website-localization-rtl-design-guide/) · [Bycom Solutions](https://bycomsolutions.com/blog/arabic-rtl-web-design-best-practices/) · [Element8](https://www.element8.sa/blogs/arabic-website-localization) · [VadeCom](https://vadecom.net/blog/b2b-website-design-for-companies-in-saudi-arabia/)

Self-serve demo and CRO: [Arcade](https://www.arcade.software/post/saas-demo-best-practices) · [Raze Growth](https://razegrowth.com/blog/saas-conversion-rate-optimization-demo) · [NerdCow](https://nerdcow.co.uk/blog/dos-and-donts-of-b2b-saas-product-demos/) · [SaaS Hero benchmarks](https://www.saashero.net/strategy/b2b-saas-demo-conversion-benchmarks/)

### 12.5 Standing caveat on everything in §12.3 and §12.4

**These are third-party sources, consulted for method.** None was used to assert a fact about the
Thiqa product, none supplies a publishable figure, and none overrides §32. Benchmark numbers
quoted in §11 (conversion rates, freemium rates) are **context for internal decisions only** and
may not appear in customer-facing material — no such figure has been measured for this product.

---
