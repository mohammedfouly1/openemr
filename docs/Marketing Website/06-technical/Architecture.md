# THIQA — WEBSITE TECHNICAL ARCHITECTURE

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
