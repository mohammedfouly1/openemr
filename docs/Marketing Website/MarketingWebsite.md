# THIQA — MARKETING WEBSITE

**Working document.** Started 2026-08-20. This is the working file for the Thiqa marketing
website. It begins with the four challenges the site must solve and the one advantage it should
be built around, because those four constraints shape every later decision — information
architecture, page inventory, copy, and the conversion mechanism itself.

Nothing here is a locked decision yet. Later sections (IA, page specifications, copy decks,
bilingual/RTL handling, proof-asset placement, build choices) will be added to this file as they
are produced.

---

## 0. Context this file assumes

| Fact | Value |
|---|---|
| Product | **Thiqa** (ثقة) — outpatient clinic management system and EMR, implemented, hosted and supported. Built on open-source OpenEMR, disclosed |
| Vendor | **SkyEagle** · `skyeagle.uk` |
| Live demo instance | **`https://demo.skyeagle.uk`** — verified 2026-08-19: valid TLS, HTTP→HTTPS 301, HSTS, `X-Content-Type-Options: nosniff`, Cloudflare in front, `<title>Thiqa Login</title>`, no user-facing stock OpenEMR identity |
| Demo host | Ubuntu `demo-openemr`, with systemd-managed backup, background-service scheduler and monitoring already running |
| Decision that creates these challenges | The marketing website will drive prospects **into the live demo as a self-serve trial**, rather than only into a booked, presenter-guided walkthrough |

**Why that decision matters.** The locked GTM strategy (DEM-001) deliberately **deferred** a
self-service seeded demo, on the grounds that it *"requires seeding plus per-visitor isolation;
GAP-0043 makes isolation manual."* Using the running demo as a customer trial reverses that
deferral. That is a legitimate call to make — but it moves four problems from *"the presenter
handles it"* to *"the product and the website must handle it."* Those four are §1–§4 below.

---

## 1. Challenge 1 — The gap: it's a login wall, not a trial

A prospect clicking through from the marketing site hits a username/password box. Nothing lets
them in.

So *"demo trial for customers"* needs a decision that has not yet been made: **do they get a
credential automatically after a short form, or do you issue one by hand?**

### Why it matters

This is the single point at which the whole marketing funnel either converts or stops. Every
other page on the site exists to get a qualified prospect to this door. Today the door is shut,
and the site has nothing to say about how it opens.

### What has to be decided

| Question | Options | Consequence |
|---|---|---|
| How does a visitor get in? | Automatic credential after a short form · manually issued by the founder · a published shared credential on the site | Automatic maximises reach and is the only version that works while you sleep; manual keeps qualification control but caps volume; published invites automated abuse |
| Is access time-boxed? | Yes / no | Untimed access to a shared instance compounds Challenge 3 |
| Is the request form a qualification step? | Yes / no | The GTM's funnel puts qualification *before* the demo; a self-serve trial inverts that unless the form does the work |

### Status

**OPEN — decision required before the website can specify its primary call to action.**

---

## 2. Challenge 2 — The seeded data is date-frozen, and it's already showing empty screens

This is the big one.

All 37 seeded appointments are dated **2026-08-14**. Anything the app filters by *"today"*
therefore renders empty. Confirmed live at **PB-441**: the Flow Board showed
**`Total patients: 0`**, and the current-week calendar was nearly bare.

In a guided demo you talk over it. In a self-serve trial, **the first thing a prospect sees in a
clinic system is an empty calendar and an empty patient board — and it gets worse every day.**

### Verification status — read this before acting

This was verified on the **local instance**. **It needs re-checking on `demo.skyeagle.uk`
specifically before you send any traffic there.** The two instances are not the same system, and
this file does not assume the local finding transfers.

### The fix

Cheap, and already specified. §16.2 of the readiness document requires appointment data to be
**re-based relative to today on every reset**:

> Date-relative data | Appointments are "the current week". **Re-base on every reset**, or the
> demo shows last month.

The requirement exists. It simply **is not wired to a schedule.** Wiring it is the fix.

### Status

**OPEN — highest-impact item. Re-verify on `demo.skyeagle.uk`, then schedule the re-base.**

---

## 3. Challenge 3 — One database, shared by every visitor

The product is **not multi-tenant** (GAP-0043 / L-07). That is precisely why the strategy
deferred a self-serve sandbox in the first place.

Visitor A registers a patient; Visitor B sees it. Visitor A edits something; Visitor B's trial is
degraded.

### What already exists

A **proven reset** — two byte-identical repeat resets from the v4 baseline, recorded at
**PB-424**. The mechanism works and has been demonstrated twice.

### What is missing

It **needs to run on a schedule, not on demand.** A reset that only fires when someone remembers
to fire it does not survive contact with unattended public traffic.

### Status

**OPEN — the reset exists and is proven; the schedule does not.** Interacts directly with
Challenge 2: the same scheduled job should perform both the reset and the date re-base, because a
reset that restores the frozen dates re-creates Challenge 2 every time it runs.

---

## 4. Challenge 4 — Nobody is there to say the things a presenter says

The demo no-go register (§40) assumes a human is in the room. Two examples:

1. **Opening Module Manager auto-registers three modules** — a state change a curious visitor
   will trigger, and one the register currently handles by having the presenter mention it before
   the prospect notices.
2. **The invoicing boundary** — *"we do not issue your tax invoice and we do not submit insurance
   claims"* — is specified as **spoken before any billing screen appears** (§40 row 7, and the
   D-7 script's step 14). It is a permanent discipline, not a temporary caveat.

**Self-serve has no speaker.**

### What this requires

Those spoken lines have to become **on-screen notices or restricted routes**. Every no-go item in
§40 needs re-classifying for an unaccompanied visitor:

| Handling | Meaning |
|---|---|
| **Restrict the route** | The visitor cannot reach it at all |
| **On-screen notice** | The visitor reaches it, and the qualification is displayed before or beside it |
| **Accept and disclose** | The visitor reaches it, and the site says so in advance |

### Status

**OPEN — §40 needs a self-serve column.** Note that this is not only a UX task: several of these
lines exist to keep the product inside the §32 prohibited-claims control. A billing screen reached
with no boundary statement is a claim-discipline failure, not merely an awkward moment.

---

## 5. The marketing unique advantage — issue two credentials, not one

**Issue two credentials, not one — a Front Office login and a Physician login — and tell the
visitor on the marketing page:**

> **"Log in as both, open the same patient, and see the difference."**

### Why this is the strongest move available

That turns the trial into the **Pillar 1 proof, unaccompanied.**

| Reason | Detail |
|---|---|
| It is the strongest thing the product owns | Demonstrated role modelling is marketed by **0 of 16** scored competitors in any comparable form; audit integrity by **0 of 16**. This is measured white space, not an assumption |
| It needs **zero development** | The role accounts exist. The permission model exists. Nothing has to be built |
| The proof already exists as stills | **SS-03** (Front Office — the clinical area absent) and **SS-04** (Physician — the same chart, fuller) are captured, reviewed and publication-ready. The trial simply lets the prospect reproduce them live |
| It converts the constraint into the pitch | The visitor does not read a claim about access control. They *perform* it |

### The inverse, stated so it does not happen by accident

**A single Administrator credential would do the opposite.** It hides the differentiator — an
administrator sees everything, so there is no boundary to discover — and it lets visitors change
globals, ACLs and data.

### Status

**PROPOSED — recommended as the design principle for the trial's access model.** Resolves
Challenge 1's "which credential" question and constrains Challenge 4's restriction design, because
neither issued role is an administrator.

---

## 6. How these five relate

The four challenges are not independent, and solving them in the wrong order wastes work.

```
Challenge 2 (frozen dates)  ─┐
                             ├─► one scheduled job fixes both
Challenge 3 (shared DB)     ─┘

Advantage 5 (two credentials) ─► answers Challenge 1's "which credential"
                              └─► and bounds Challenge 4, since neither role is admin

Challenge 1 (the door)      ─► still needs its own decision: automatic, manual, or published
Challenge 4 (no presenter)  ─► needs §40 re-classified for an unaccompanied visitor
```

**Suggested order of work:**

1. **Re-verify Challenge 2 on `demo.skyeagle.uk`.** Everything else is guesswork until the live
   state of that specific host is known.
2. **Schedule reset + date re-base as one job** — closes Challenges 2 and 3 together.
3. **Adopt the two-credential model** (§5) — closes the *which* half of Challenge 1.
4. **Decide the access mechanism** — the *how* half of Challenge 1.
5. **Re-classify §40 for self-serve** — Challenge 4.

---

## 7. What this file does not yet contain

To be added as it is produced:

- Information architecture and page inventory
- Per-page specifications and copy decks
- Proof-asset placement — where SS-01…SS-12 and the audit-integrity recording go
- The claim-discipline review pass against §32

*(Method, bilingual/RTL practice and acknowledgements are in Part II, §10–§12. Tooling has moved
to [`MarketingSkills.md`](MarketingSkills.md). Technical architecture is Part III, §13.)*

---

## 8. Constraints that already bind everything above

Carried forward, not re-litigated here:

- **No price figure** may be published — PRC-003 is BLOCKED.
- **No competitive frequency figure** ("0 of 16" and similar) may appear on any page — §32
  item 26. The mechanism may be described; the number may not be printed. *The internal figures
  quoted in §5 above are internal reasoning, not publishable copy.*
- **No uptime, performance, ROI or implementation-time figure** — none has been measured.
- **Nothing Saudi-regulatory** in either language — NPHIES, ZATCA, CHI, VAT, Hijri, Iqama, SFDA.
- **Every mandatory qualification travels in the same visual unit as its claim**, never as a
  footnote.
- **The open-source origin is disclosed**, not obscured.
- **The `admin` credential appears in no material, ever.**

**These constraints also bind Parts II and III below, and `MarketingSkills.md` in full.** They
outrank every external framework, skill pack, industry convention and technical recommendation
named anywhere in this folder. Where a marketing best practice and §32 disagree, **§32 wins**.

---

# PART II — METHOD, TOOLING AND ACKNOWLEDGEMENTS

*Added 2026-08-20. Records the marketing expertise, tooling and bodies of practice that will be
used to plan the messages and the site content — what is available locally, what exists
externally, which frameworks govern which decision, and who the work is indebted to.*

## 9. Marketing tooling — moved

**The tooling inventory now lives in its own file: [`MarketingSkills.md`](MarketingSkills.md).**

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

**Moved.** Full inventory, licences and assessment: [`MarketingSkills.md`](MarketingSkills.md) §3
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

# PART III — TECHNICAL ARCHITECTURE

*Added 2026-08-20. The advised stack for the marketing website, and how it relates to the demo
that already exists. Status: **advised, not locked** — §13.7 lists what still needs a decision.*

## 13. Technical architecture

### 13.1 The rule everything follows: three separate environments

**Do not build the marketing website inside OpenEMR.** The marketing site and the clinical
application have opposite requirements — one wants pre-rendered static pages on a global CDN, the
other wants PHP, a persistent filesystem, sessions and cron.

```
                        skyeagle.uk
                             │
        ┌────────────────────┼────────────────────┐
        │                    │                    │
  www.skyeagle.uk     demo.skyeagle.uk      app.skyeagle.uk
   MARKETING SITE        LIVE DEMO         FUTURE PRODUCTION
        │                    │                    │
   Next.js / React        OpenEMR             (not built)
   TypeScript             PHP · Apache
   Tailwind               MariaDB
        │                    │
     Vercel              Ubuntu VM
   Global CDN            Cloudflare in front
        │                    │
   No patient data     Synthetic data only     Real PHI — later
```

**Marketing website ≠ demo ≠ future production.** Three deployments, three data classes, three
security postures. The separation is what makes the route from today's MVP to a real SaaS platform
clean rather than tangled.

### 13.2 The advised stack

| Layer | Recommendation | Note |
|---|---|---|
| Framework | **Next.js** | Locale-aware routing and static generation are the two features this project specifically needs |
| Language | **TypeScript** | Not plain JavaScript |
| UI | **React** | — |
| Styling | **Tailwind CSS** | Driven by the existing `brand/tokens/thiqa-tokens.json`, not hand-rolled values |
| Components | shadcn/ui-style reusable set | See §13.5 for the corrected component list |
| Languages | **English + Arabic from day one** | Not English first, Arabic later |
| RTL | Native, at component level | `/en/` and `/ar/` routes, `dir` and `lang` on `<html>`, CSS logical properties |
| Content | **MDX / JSON in the Git repository** | No CMS initially |
| CMS | Headless, later | Only when non-technical staff need to edit frequently |
| Hosting | **Vercel** | Automatic deploys from Git, preview builds, global CDN, HTTPS |
| Source control | **GitHub** | — |
| Analytics | GA4 + Search Console | Marketing site only — never on the demo |
| Domains | `www.skyeagle.uk` · `demo.skyeagle.uk` · `staging.skyeagle.uk` | `app.skyeagle.uk` reserved for future production |

**Framework ranking, for the record:** Next.js **95** · Astro **88** · WordPress **73** · custom
PHP **55**. Astro would be excellent for a *pure* marketing site and is genuinely simpler. Next.js
wins because this site will not stay pure — it grows a demo launcher, lead capture, interactive
product content, and possibly a customer area. WordPress is rejected on maintenance surface and
because the interactive demo entry deserves better than a plugin.

### 13.3 The demo half is already built

Most of the demo-side recommendation is **already implemented and verified live 2026-08-19**:

| Recommended | Status |
|---|---|
| Ubuntu + Apache + PHP + MariaDB | **Running** |
| HTTPS | **Valid cert**, HTTP→HTTPS 301, HSTS `max-age=15552000` |
| Cloudflare in front | **In place** (`Server: cloudflare`) |
| Daily backup | **Running** — systemd-managed on `demo-openemr` |
| Firewall, monitoring | **Running** |
| Demo reset mechanism | **Exists and is proven** (PB-424) — **but is not scheduled**, see Challenge 3 |
| Synthetic data only, never real PHI | **Holds** — and is a permanent rule |

**Two corrections to the generic advice, because decisions here have already been taken:**

- **Do not move the demo to AWS Lightsail or DigitalOcean.** The hosting residency decision is
  **Dammam / `me-central2`** (RDY-0064, closed 2026-08-19). Those suggestions predate it.
- **Cloudflare rate-limiting on the login endpoint is the one genuinely missing control.** A
  public demo attracts scanners and credential-stuffing. OpenEMR has its own per-IP brute-force
  protection, but that fires *after* the request reaches Apache. Rate-limit at the edge.

### 13.4 Bilingual routing — build both from the start

```
/en/                          /ar/
   clinical-documentation/       clinical-documentation/
   scheduling/                   scheduling/
   roles-permissions/            roles-permissions/
   security-audit/               security-audit/
   configure-it-yourself/        configure-it-yourself/
   your-data-your-exit/          your-data-your-exit/
   whats-included/               whats-included/
   pricing/                      pricing/
   demo/                         demo/
```

`<html lang="en" dir="ltr">` and `<html lang="ar" dir="rtl">`. Native routes per language — **not
a translation plugin**, which breaks direction and hurts both speed and ranking on `google.sa`
(§11.3).

**The Arabic site carries the same depth as the English one.** Saudi buyers toggle languages
mid-session on the same device; a thinner Arabic site is noticed immediately. That includes the
product's Arabic limitation — 47.5%, chrome only — displayed with **equal prominence** in Arabic
(GTM R-08).

### 13.5 Repository structure

```
marketing-website/
├── app/
│   └── [locale]/
│       ├── page.tsx
│       ├── who-its-for/
│       ├── ophthalmology/
│       ├── clinical-documentation/
│       ├── scheduling/
│       ├── reporting-export/
│       ├── roles-permissions/         ★ flagship
│       ├── security-audit/            ★ flagship
│       ├── configure-it-yourself/     ★ flagship
│       ├── your-data-your-exit/       ★ flagship
│       ├── whats-included/
│       ├── pricing/
│       ├── implementation/
│       ├── demo/
│       ├── resources/
│       ├── about/
│       └── contact/
├── components/
│   ├── Hero.tsx           Navbar.tsx        Footer.tsx
│   ├── FeatureCard.tsx    RoleCard.tsx      SegmentCard.tsx
│   ├── ProductScreenshot.tsx   ← carries its qualification inline
│   ├── QualifiedClaim.tsx      ← claim + limitation as ONE unit
│   ├── StatusRegister.tsx      ← the Disabled / Uninstalled / RI / Missing tables
│   ├── WorkflowJourney.tsx     DemoCTA.tsx  FAQ.tsx
│   └── MilestoneFeed.tsx
├── content/{en,ar}/
├── public/{screenshots,videos,icons,brand}/
└── lib/
```

**`QualifiedClaim` is the most important component on the site.** §32 requires every mandatory
qualification to travel in the *same visual unit* as its claim, never as a footnote. Making that a
component rather than an editorial habit is what stops it eroding over time.

### 13.6 ⚠ Where the generic architecture conflicts with the locked IA

The advised stack is sound. **Its example sitemap and component list are not** — they were written
generically and contain pages and elements this project has explicitly prohibited. Corrected here
so they are not built by accident.

| Generic suggestion | Ruling | Why |
|---|---|---|
| `billing/` page | **Must not exist** | The product produces **US** claim formats only, and for a Saudi audience any billing page implies invoicing capability that does not exist (no tax field anywhere in the billing chain). §32 items 4 and 12 |
| `insurance/` page | **Must not exist** | Implies NPHIES / claims / eligibility. §32 item 12 — the highest-drift-risk prohibition |
| `orders-results/` page | **Sub-section only** | Real, but transmission and result receipt need a lab interface the customer must contract. A full page over-signals |
| `Testimonial` component | **Must not exist** | §32 item 25 — no testimonials, logos, customer counts. There are no customers |
| `ComparisonTable` (named competitors) | **Deferred** | COMP-001: named-competitor comparison waits for the 9 unverified dossiers and a first customer. Comparison against *paper* and *self-installed OpenEMR* is allowed |
| Pricing page with figures | **Model only** | PRC-003 is BLOCKED. Publish the model, the inclusions and the exclusions; publish no number |
| "Solo practice" segment page | Drop | Not the ICP |
| Certification / trust badges | **Must not exist** | §32 items 14 and 25 |

**The positive form of the same rule:** the four flagship pages are *Roles & Permissions*,
*Security & Audit*, *Configure it yourself* and *Your data, your exit* — because those are the four
differentiators, and because all four now have publication-ready proof (§34 of the readiness doc,
resynced 2026-08-19).

### 13.7 How the demo connects — the launcher pattern

Do not embed OpenEMR in an iframe. Route to it.

```
Marketing page  →  /demo  →  choose an experience  →  demo.skyeagle.uk
                                     │
                        ┌────────────┴────────────┐
                        │  Front Office           │
                        │  Physician              │
                        └─────────────────────────┘
                         the two-credential proof — §5
```

The `/demo` page is where §5's advantage is delivered: two credentials, one instruction — *"log in
as both, open the same patient, and see the difference."* It is also where the four challenges get
their answers: the credential mechanism (Challenge 1), the on-screen notices that replace the
presenter (Challenge 4), and the honest statement about shared demo data (Challenge 3).

**Pair it with a low-commitment path.** "Watch the audit-integrity run" — the recording that
already exists — sitting beside "Try it yourself" serves the early-stage visitor without spending
a trial credential.

### 13.8 Open questions — not decided by this section

| # | Question | Why it matters |
|---|---|---|
| 1 | **Where does lead-form data land?** | Vercel is a global CDN, which is fine for pages carrying no personal data. But a contact form collects names, phones and emails of Saudi clinic owners. The residency question that was answered for the *application* (Dammam / `me-central2`) has not been asked for *lead data*, and Saudi PDPL is the relevant regime |
| 2 | **Is the site branded Thiqa or SkyEagle?** | The product is Thiqa; the domain and the product's own support links point at `skyeagle.uk`. Both are defensible — vendor-branded site marketing a named product is common — but it should be a decision, not an accident |
| 3 | **Analytics on the demo host: yes or no?** | GA4 on the marketing site is straightforward. On the demo, session recording or analytics touching a clinical UI is a different conversation, even with synthetic data. Default answer: **no analytics on `demo.skyeagle.uk`** |
| 4 | **Who operates the marketing site?** | Vercel deploys from Git. That implies whoever owns the repository owns publication — including publication of claims. The claim review (`EV-003`) has to sit *before* merge, not after deploy |

### 13.9 Status

**ADVISED — not locked.** The stack recommendation in §13.2 is sound and consistent with
everything decided so far. §13.6's corrections are **binding regardless of stack**, because they
come from §32 rather than from architecture. §13.8's four questions need answers before build
starts.
