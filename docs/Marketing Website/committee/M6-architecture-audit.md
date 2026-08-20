# M6 — TECHNICAL & SECURITY AUDIT — TASK 2

**Dispatch:** `COM-20260820-001` · **Committee:** C-2 · **Agent:** M6, Technical & Security Auditor,
**ADVERSARY by construction** (§8.6) · **Round:** R2, silent independent.

**Object under audit:** `docs/Marketing Website/06-technical/Architecture.md`, SHA-256 as frozen at
dispatch `938ff387cfaa239c29c89f41b8d11ef22e6ceac24b9569d362cc0dc18c8c0c1a`, 206 lines.

**Mandate (§8.6):** find why the proposed technical answer is **wrong**. Never to confirm it.
**Failure condition (§14):** approving the stack because it is conventional and modern.

**Write isolation (R9):** this agent wrote exactly one file — this one. **No git command was run**
at any point in this dispatch.

**Evidence freshness (§6.5):** every live-host observation below was taken **2026-08-20, 05:04–05:07
UTC**, inside the 24 h window. Every external platform fact carries a URL and the date accessed
(2026-08-20). Nothing here rests on recollection of a platform's capabilities.

---

## PART 0 — THREE CHALLENGES TO THE BRIEFING PACK ITSELF (R10)

R10 applies to the briefing pack exactly as to any other finding. Three of the facts I was handed do
not survive checking. I report them first because two of them change the shape of the task.

---

```
ID:          M6-R01
TYPE:        OBSERVED
STATEMENT:   The dispatch premise that "Saudi PDPL appears NOWHERE in the 1.2 MB readiness
             register — grep -n 'PDPL' returns zero matches" is FALSE. It returns four
             matches. More consequentially, the corpus already contains a dedicated,
             named decision on exactly this question — `Q45`, Saudi PDPL data residency —
             carrying a written "safe provisional default" that, if applied, decides the
             lead-form question against a global form service.
BASIS:       Re-runnable, from repo root:
               grep -c "PDPL" "docs/Marketing-MVP-and-Launch-Readiness-Requirements.md"
             → 4  (run 2026-08-20). Matching lines 2475, 9205, 9370, 9631.
             `docs/discovery/openemr-decision-evidence/20-unresolved-external-inputs.md:16-27`
             — "Q45 — Saudi PDPL data residency · EVIDENCE-BLOCKED … Safe provisional
             default: Assume **no** cross-border transfer of anything derived from tenant
             data, including logs, metrics and crash reports. Kingdom-only region. This is
             the most restrictive posture and can only be relaxed later, never tightened
             cheaply."  Same file:189 — summary table, Q45, Blocking? **Yes**.
             `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:2475` — "`Q45` (Saudi
             PDPL data-residency default) matches RDY-0064's closed Dammam/`me-central2`
             decision".
FALSIFIER:   A grep over the current file returning 0, or a demonstration that `Q45`'s text
             says something other than the quoted default.
CONFIDENCE:  High — the grep is deterministic and the quoted text is verbatim.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: If I were wrong and PDPL truly were absent, the lead-data question would be
             a blank-slate open question. Because it is not absent, an agent answering §13.8
             Q1 with three fresh options would be re-deliberating something the corpus has
             already given a written default for — the R-02 failure mode §4.1 exists to stop.
STATUS:      REQUIRED CHANGE — the classification of §13.8 Q1 must record `Q45` and its
             default as an input, not treat the question as unexamined.
```

**Important qualification, so this is not over-read.** An Owner ruling recorded at
`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:1141` (RDY-0092, 2026-08-19) holds that the
`Locked Desicions/` corpus — which is where `Q45` lives — is **"a future-phase roadmap, not binding
on the current MVP"**, with the default rule *"GTM/readiness governs now, corpus governs later."* So
`Q45`'s default is **not** a locked constraint on this decision. It is a **documented,
already-reasoned position by the same organisation on the identical question**, and the honest
classification of §13.8 Q1 is therefore *genuinely open, with a strong written prior that the Owner
has previously found persuasive* — not *unexamined*. That is a materially different starting point
from the one the dispatch brief describes.

---

```
ID:          M6-R02
TYPE:        OBSERVED
STATEMENT:   "Hosting residency: Dammam / me-central2 (RDY-0064 CLOSED)" is being carried
             into this dispatch as settled fact. The register row that closes it records
             the provisioning as OWNER-RELAYED AND NOT INDEPENDENTLY VERIFIED, and a second
             passage in the same file still reads "RDY-0064 status: the decision is made and
             recorded. NOT CLOSED." The DECISION is closed. The IMPLEMENTATION is unverified,
             and the document contradicts itself about the status.
BASIS:       `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:1083` — "Provisioning
             reported complete 2026-08-19, relayed by the Owner directly in conversation
             (**not independently verified by this session — no access to the actual
             instance/account exists here**)" … "**VERIFIED READY — CLOSED 2026-08-19,
             Owner-relayed**".
             Same file:9635-9638 — "**RDY-0064 status:** the *decision* is made and recorded.
             **NOT CLOSED** — its acceptance also requires the region to be provisioned and a
             residency position written. That leg is **BLOCKED — EXTERNAL PROVISIONING**".
             Per §6.5 ("RDY status: from the current canonical register, never from a
             quotation") the register row at :1083 governs and the status is CLOSED; :9635 is
             a stale narrative passage that was never synced to PB-301.
FALSIFIER:   Evidence that an instance in `me-central2` exists and serves `demo.skyeagle.uk`,
             independently observed rather than relayed — e.g. an origin IP in a Saudi
             allocation, or a cloud-console screenshot.
CONFIDENCE:  High for the two quotations; the origin's actual location is **unverifiable from
             outside** because Cloudflare proxies it (see M6-B02).
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Residency is out of scope to re-decide (§8.6) and I do not re-decide it. But
             every residency argument I make below about Vercel applies with equal force to
             an origin whose location nobody in this repository has verified. Stating the
             asymmetry is the honest position; suppressing it would let a strict standard be
             applied to the new component and a loose one to the existing component.
STATUS:      OPEN — OWNER DECISION (evidence quality only; the residency decision itself is
             not reopened by this finding)
```

---

```
ID:          M6-R03
TYPE:        OBSERVED
STATEMENT:   M0's classification item 13 records §13.7's "route, do not iframe" as
             "GENUINELY OPEN, pending M6 verification", and names the settling evidence as
             "M6 checking demo.skyeagle.uk's actual response headers." I have checked them.
             The item is EVIDENCE-DETERMINED, not open: the demo already sends
             `X-Frame-Options: DENY` and `Content-Security-Policy: frame-ancestors 'none'`.
             Iframing it is not inadvisable — it is impossible in every current browser.
BASIS:       curl -sS -I "https://demo.skyeagle.uk/interface/login/login.php?site=default"
             — run 2026-08-20T05:04:31Z. Response includes:
               X-Frame-Options: DENY
               Content-Security-Policy: frame-ancestors 'none'
FALSIFIER:   The headers disappearing from the origin, or a decision to strip them at the
             Cloudflare edge.
CONFIDENCE:  High — directly observed, twice, on the live host.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Producing ≥3 ranked options for an evidence-determined item is a **FAILED
             deliverable** under §12. This reclassification prevents that failure for item 13.
STATUS:      REQUIRED CHANGE — reclassify item 13 EVIDENCE-DETERMINED; no options are to be
             generated for it.
```

---

## PART 1 — PER-LAYER VERDICTS

Every row of `Architecture.md` §13.2, plus the three structural rules (§13.1, §13.5, §13.7). Each
carries an explicit verdict and the §8.6-mandatory **change-my-mind condition**.

**A note on the change-my-mind device, written before the verdicts rather than after.** §8.6 itself
warns that "an agent that has reached a verdict writes the change-my-mind condition to fit it," and
M4 is briefed to attack exactly that. My own guard is that **each condition below is stated as an
observation someone could actually go and make** — a command, a document, a measurement — and not as
a change of opinion. Where I could only write a condition that amounts to *"if the Owner preferred
otherwise,"* I have marked the verdict **APPROVE (weak basis)** instead of dressing it up.

### 1.1 Verdict summary

| # | Layer | Verdict | Governing finding |
|---|---|---|---|
| L01 | Framework — Next.js | **APPROVE WITH CHANGES** · *the choice survives; the stated reasons do not* | M6-L01 |
| L02 | Language — TypeScript | **APPROVE** | M6-L02 |
| L03 | UI — React | **APPROVE (weak basis)** | M6-L03 |
| L04 | Styling — Tailwind, driven by `thiqa-tokens.json` | **APPROVE WITH CHANGES** | M6-L04 |
| L05 | Components — shadcn/ui-style set | **APPROVE WITH CHANGES** | M6-L05 |
| L06 | Languages — EN + AR from day one | **APPROVE** | M6-L06 |
| L07 | RTL — native, component level | **APPROVE WITH CHANGES** | M6-L07 |
| L08 | Content — MDX/JSON in Git | **APPROVE** — *and the document under-states its own best argument* | M6-L08 |
| L09 | CMS — headless, later | **REJECT AND REPLACE** *(the trigger condition, not the deferral)* | M6-L09 |
| L10a | **Hosting — Vercel, for static page delivery** | **APPROVE WITH CHANGES** | M6-L10a |
| L10b | **Hosting — Vercel, for lead-form data** | **REJECT AND REPLACE** | M6-L10b |
| L10c | **Hosting — the option never evaluated** | **REQUIRED CHANGE to the evaluation** | M6-L10c |
| L11 | Source control — GitHub | **APPROVE WITH CHANGES** | M6-L11 |
| L12 | Analytics — GA4 + Search Console | **APPROVE WITH CHANGES** | M6-L12 |
| L13 | Domains — www / demo / staging / app | **APPROVE WITH CHANGES** | M6-L13 |
| S01 | §13.1 three-environment separation | **APPROVE** — the strongest rule in the document | M6-S01 |
| S02 | §13.5 repository structure | **APPROVE WITH CHANGES** | M6-S02 |
| S03 | §13.7 demo launcher pattern | **APPROVE** — *and it is evidence-determined, not advisory* | M6-S03 |

---

### 1.2 The verdicts in full

```
ID:          M6-L01   LAYER: Framework — Next.js
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE WITH CHANGES. Next.js can deliver this site correctly. But
             BOTH reasons the document gives for choosing it are wrong on the evidence, and
             the numeric ranking that presents the choice as measured is unsourced.
             (a) Architecture.md:40 — "Locale-aware routing and static generation are the two
             features this project specifically needs" — is the load-bearing justification.
             The Next.js App Router has **no built-in i18n routing at all**; locale routing is
             hand-built from an `app/[lang]/` dynamic segment plus `generateStaticParams`.
             Astro, ranked second, DOES ship built-in i18n configuration (`locales`,
             `defaultLocale`, `routing`, `prefixDefaultLocale`, `redirectToDefaultLocale`,
             `fallback`) and helpers such as `getRelativeLocaleUrl()`. On the single stated
             differentiator, the ranking is backwards.
             (b) Architecture.md:56-58 — "Next.js wins because this site will not stay pure —
             it grows a demo launcher, lead capture, interactive product content, and possibly
             a customer area" — is refuted by the same document. The demo launcher is a LINK
             (§13.7, and M6-R03 makes it mandatory); lead capture cannot be a Vercel function
             under any defensible reading of residency (M6-V03); and a customer area is
             `app.skyeagle.uk`, which §13.1 requires to be a SEPARATE environment. Every
             growth item cited is either a hyperlink or explicitly out of this deployment.
             (c) The scores "Next.js 95 · Astro 88 · WordPress 73 · custom PHP 55" carry no
             derivation, no criteria, and no weights. In a project whose Standing Rule is
             "nothing on assertion" (§1), a four-figure precision ranking with no method is
             the exact defect the rule exists to catch.
BASIS:       https://nextjs.org/docs/app/guides/internationalization (accessed 2026-08-20) —
             the entire documented App Router approach is a hand-written `proxy.js` matcher
             plus `app/[lang]/`; there is no i18n config key.
             https://docs.astro.build/en/guides/internationalization/ (accessed 2026-08-20) —
             built-in config keys and `getRelativeLocaleUrl()` confirmed.
             Architecture.md:40, 54-58, 170; §13.1 diagram at Architecture.md:14-30.
FALSIFIER:   A Next.js release note showing built-in App Router i18n routing config; OR a
             derivation for the 95/88/73/55 scores; OR a concrete MVP requirement that needs
             server-rendered request-time logic on `www.skyeagle.uk` (none appears anywhere
             in `Architecture.md`, the locked IA, or the four challenges).
CONFIDENCE:  High on (a) and (b) — both are documentation facts checked today against the
             document's own text. High on (c) — the absence of a derivation is observable.
CHANGE MY MIND: Show me one page in the locked IA (§13.5's 17 routes) that cannot be produced
             at build time. One is enough. Absent that, Next.js and Astro are functionally
             interchangeable here and the decision should be made on **team skill**, which is
             a legitimate basis nobody has written down — not on two reasons that are false.
AUTHORITY:   HYPOTHESIS — Architecture.md §13.9 says "ADVISED — not locked", and I tested that
             status rather than accepting it: no locked decision, no §32 row and no
             `HISModulesUsers.md` fact governs framework choice. The status claim holds.
IMPACT IF WRONG: Low if I am wrong about the framework (both work). **High if the unsourced
             ranking is left standing**, because it converts an undocumented preference into
             an apparently-measured decision inside a governance document, and the next reader
             will cite "95 vs 88" as evidence.
STATUS:      REQUIRED CHANGE — (i) delete the 95/88/73/55 scores or publish their derivation;
             (ii) strike or correct the i18n justification at Architecture.md:40;
             (iii) record team skill as the actual basis, if that is the actual basis.
```

```
ID:          M6-L02   LAYER: Language — TypeScript
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE. On a site whose central component (`QualifiedClaim`) must
             structurally prevent a claim being rendered without its qualification, a type
             system that can make `qualification` a non-optional prop is a claim-register
             control, not a developer preference. That is a stronger argument than
             "Not plain JavaScript" (Architecture.md:41), which is the only one given.
BASIS:       Architecture.md:41, 142-144 ("`QualifiedClaim` is the most important component
             on the site … Making that a component rather than an editorial habit is what
             stops it eroding over time").
FALSIFIER:   A demonstration that the qualification-adjacency rule can be enforced as
             reliably without static types — e.g. a runtime assertion plus a CI check that
             fails the build. This is genuinely possible; it is just weaker.
CONFIDENCE:  High — no cost to TypeScript at this size, and a real compliance benefit.
CHANGE MY MIND: A team that does not know TypeScript and would ship slower and buggier in it.
             That is a real trade and I would take plain JS plus the CI check over a
             half-typed codebase with `any` in the claim components.
AUTHORITY:   HYPOTHESIS
IMPACT IF WRONG: Negligible.
STATUS:      PASS
```

```
ID:          M6-L03   LAYER: UI — React
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE (weak basis). Architecture.md:42 gives React a table row and a
             dash — literally no argument. React is entailed by the Next.js choice, so this
             row carries no independent decision. I mark it weak rather than manufacturing a
             justification the document does not make. For a 17-route × 2-locale static
             brochure with no application state, React is more machinery than the job needs;
             it is not, however, harmful.
BASIS:       Architecture.md:42 (the Note column is "—"); §13.5's route list,
             Architecture.md:110-128 (17 routes).
FALSIFIER:   Evidence of client-side interactive state at MVP beyond a nav toggle, a language
             switcher and a form — none appears in the locked IA or the four challenges.
CONFIDENCE:  Medium — the conclusion is safe but the row is content-free, so there is little
             to audit.
CHANGE MY MIND: If the framework verdict moves to Astro, this row disappears (Astro ships
             zero JS by default and can still use React islands). It is not separately
             decidable.
AUTHORITY:   HYPOTHESIS
IMPACT IF WRONG: Low — some unnecessary hydration JS on a marketing page.
STATUS:      PASS
```

```
ID:          M6-L04   LAYER: Styling — Tailwind CSS driven by `brand/tokens/thiqa-tokens.json`
TYPE:        OBSERVED
STATEMENT:   VERDICT: APPROVE WITH CHANGES. The token-driven instruction is right and the
             token file really exists. The change required is mechanical: "driven by the
             tokens, not hand-rolled values" (Architecture.md:43) is an instruction, and an
             instruction is not a control. It needs a CI check that fails the build on a raw
             hex colour outside the token file — the same lesson the branding work already
             learned when it built `BrandingGovernanceGuard` rather than trusting a note.
BASIS:       ls -la brand/tokens/ → `thiqa-tokens.json`, 2,706 bytes (run 2026-08-20).
             Architecture.md:43. The existing precedent is
             `CLAUDE.local.md` §6, which records a governance guard test enforcing a theme
             constraint mechanically after a "just don't do it" rule proved insufficient.
FALSIFIER:   A stylelint/ESLint configuration in the marketing repo enforcing token-only
             colour values (the repo does not exist yet — see M6-B01).
CONFIDENCE:  High on the file's existence; High on the general principle, which this project
             has already paid to learn once.
CHANGE MY MIND: If the site is small enough and single-authored, the check is over-engineering.
             But this site is explicitly multi-locale and explicitly outlives its first author
             (§13.5's "stops it eroding over time"), so the argument for the check is the
             document's own.
AUTHORITY:   EVIDENCE (the token file) · POLICY (the CI check is my recommendation)
IMPACT IF WRONG: Low — drift in brand colours, cosmetic.
STATUS:      REQUIRED CHANGE — add the lint gate alongside the instruction.
```

```
ID:          M6-L05   LAYER: Components — shadcn/ui-style reusable set
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE WITH CHANGES, and one of the two named "most important"
             components is specified in a way that will silently go stale.
             (a) `QualifiedClaim` is correct and is the best idea in the document — but a
             component only enforces adjacency for content that USES it. MDX authors can
             write a claim as plain prose in the same file. The mechanical control is a CI
             check that fails on any `MC-`/`CLM-` token appearing outside a `<QualifiedClaim>`
             block, not the component's existence.
             (b) `StatusRegister` must render 47 Disabled / 27 Uninstalled / 18
             Requires-Integration / 60 Missing. If those numbers are hardcoded in a `.tsx`
             file they will drift from `EV-067` the first time a register changes and nobody
             will notice. They must be imported from one JSON in the repo, with a CI check
             against the published register.
             (c) shadcn/ui is copy-in source, not a runtime dependency — this is a genuine
             point in its favour for a project that must be able to audit everything it ships,
             and the document does not make it.
BASIS:       Architecture.md:129-144; CommitteeSystem.md:805 and `EV-067` for the four
             register figures; CommitteeSystem.md:635 for the same-visual-unit rule.
FALSIFIER:   A CI configuration performing either check.
CONFIDENCE:  High — (b) is a straightforward staleness argument and this repository has
             several recorded instances of exactly that failure (e.g. §40 row 10's status
             going stale against RDY-0050's closure, readiness register :12577 ff.).
CHANGE MY MIND: If the numbers are generated at build time from `EV-067` itself, (b) is
             already solved and I withdraw it.
AUTHORITY:   LOCKED for the adjacency requirement (§32, mandatory-qualification rule) ·
             POLICY for the enforcement mechanism.
IMPACT IF WRONG: **High.** A stale published register figure is a false claim about the
             product, which is a §32-class defect, not a bug.
STATUS:      REQUIRED CHANGE
```

```
ID:          M6-L06   LAYER: Languages — English + Arabic from day one
TYPE:        OBSERVED
STATEMENT:   VERDICT: APPROVE. This is not a stack decision to audit — it is
             constraint-determined by WEB-003, and Architecture.md complies. I record the
             verdict and do not deliberate it. The one technical consequence worth naming:
             "the Arabic site carries the same depth as the English one" plus "MDX in Git,
             no CMS" means every English page change creates an Arabic debt that no tool
             tracks. That needs a build-time parity check (every `content/en/*` has a
             `content/ar/*` counterpart, and the build FAILS otherwise), or the parity
             requirement will be enforced by memory.
BASIS:       CommitteeSystem.md:138, 761 (WEB-003); Architecture.md:45, 101-104.
FALSIFIER:   A CI parity check existing in the (not-yet-created) repo.
CONFIDENCE:  High.
CHANGE MY MIND: Nothing about the requirement — it is locked. On the parity check: if the
             content set stays under ~20 files and one person owns both languages, a manual
             checklist is defensible. Above that it is not.
AUTHORITY:   LOCKED (WEB-003) for the requirement · POLICY for the check.
IMPACT IF WRONG: **High** — R-08, "an Arabic page implying a fully Arabic product," is rated
             Medium likelihood / High impact (CommitteeSystem.md:815). A thinner Arabic site
             is the specific failure mode.
STATUS:      REQUIRED CHANGE — add the build-time locale-parity gate.
```

```
ID:          M6-L07   LAYER: RTL — native, at component level
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE WITH CHANGES. `dir`/`lang` on `<html>` and CSS logical
             properties is the correct approach and is stated correctly. Three concrete
             mechanics are missing and each is a real failure mode:
             (i) **Tailwind and logical properties.** Tailwind's `ps-*`/`pe-*`/`ms-*`/`me-*`
             logical utilities must be used; the far more commonly written `pl-*`/`pr-*`
             physical utilities silently produce a correct-looking LTR page and a broken
             RTL one. This is the single highest-frequency RTL defect and it is invisible in
             review because the English page looks right. It needs a lint rule.
             (ii) **Arabic font loading.** No Arabic webfont is named anywhere in
             `Architecture.md`. An unsubsetted Arabic face is a large download; the wrong one
             renders Arabic in a browser fallback that will not match the brand. This is a
             decision, not a detail, and it interacts with §13.9's silence.
             (iii) **Numerals.** M7's brief locks Latin digits, not Arabic-Indic
             (CommitteeSystem.md:759). Nothing in the CSS/typography layer enforces that; a
             font with Arabic-Indic defaults or an `Intl.NumberFormat` with an `ar` locale
             will produce Arabic-Indic digits automatically.
BASIS:       Architecture.md:46, 97-99; CommitteeSystem.md:759 (Latin digits, POLICY);
             CommitteeSystem.md:761 (WEB-003 depth parity).
FALSIFIER:   An RTL visual-regression check, a named subsetted Arabic face in the config, and
             a numeral-format test — none of which can exist yet, as the repo does not exist.
CONFIDENCE:  High on (i) — it is a well-established, mechanical failure mode. High on (iii) —
             it follows from the locked policy. Medium on (ii) — a font choice is a decision
             the document simply defers, which is defensible at this stage.
CHANGE MY MIND: (i) is dropped if the project adopts Tailwind's logical utilities as a lint
             rule from commit one, which costs one config line. Nothing changes my mind on
             (iii) short of M7 or the Owner reversing the Latin-digits policy.
AUTHORITY:   POLICY (Latin digits) · HYPOTHESIS (the mechanics)
IMPACT IF WRONG: Medium-High. A broken Arabic layout on a site whose Arabic parity IS the
             positioning is worse than no Arabic site — it demonstrates the opposite of the
             claim. **This is M7's lane in substance and I flag it rather than settle it**
             (M0 recorded a proposed amendment to consult M7 here — CommitteeSystem.md:112;
             I support that amendment on the evidence of (ii) and (iii)).
STATUS:      REQUIRED CHANGE
```

```
ID:          M6-L08   LAYER: Content — MDX / JSON in the Git repository
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE — and the document under-states its own strongest argument by a
             wide margin. Architecture.md:47-48 justifies MDX-in-Git as a convenience ("No
             CMS initially"). The real argument is a governance one: **content in Git means
             every claim change is a pull request, and a pull request can be gated by a
             required review.** That is the only mechanism in the entire architecture capable
             of putting the EV-003 claim review physically before publication, which §13.8 Q4
             says it must be. MDX-in-Git is not a starter choice to be outgrown; it is the
             enforcement substrate.
BASIS:       Architecture.md:47-48, 198 ("The claim review (`EV-003`) has to sit *before*
             merge, not after deploy"); `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:977`
             — RDY-0003 CLOSED 2026-08-19, named claim reviewer Mohammed Elfouly, six review
             gates C-1…C-6 defined in `EV-003` §3.
FALSIFIER:   A demonstration that a headless CMS can enforce a named-reviewer gate as
             reliably as a GitHub required review — some can, via editorial workflow, but
             none does it by default and none is auditable from the repository.
CONFIDENCE:  High.
CHANGE MY MIND: If the volume of content edits ever exceeds what one reviewer can gate
             through PRs, the bottleneck becomes real. At 17 routes × 2 locales, it is not.
AUTHORITY:   EVIDENCE — RDY-0003's closure establishes the reviewer and the procedure exist.
IMPACT IF WRONG: Low as stated; the finding strengthens rather than reverses the row.
STATUS:      PASS (with a recommendation to restate the rationale)
```

```
ID:          M6-L09   LAYER: CMS — headless, later
TYPE:        INFERRED
STATEMENT:   VERDICT: REJECT AND REPLACE — not the deferral, which is right, but the
             **trigger condition**, which is wrong and dangerous. Architecture.md:48 says a
             CMS arrives "Only when non-technical staff need to edit frequently." That
             condition describes exactly the circumstance under which a CMS would do the most
             damage: a non-technical editor publishing a claim without passing the EV-003
             gate. As written, the trigger for adopting the CMS IS the trigger for defeating
             the claim control. Replace with: "A CMS may not be adopted until it can
             demonstrably enforce a named-reviewer approval before publish, and its adoption
             is itself a C-2 and C-4 matter." This is not a preference — it is the direct
             consequence of §13.8 Q4 and C-4's terminal gate.
BASIS:       Architecture.md:48, 198; CommitteeSystem.md:153-169 (C-4: "Every artefact,
             before it reaches a page or a decision. No exceptions").
FALSIFIER:   A named headless CMS whose default configuration blocks publish without an
             approval from a specific named account, with the approval recorded immutably.
CONFIDENCE:  High — this follows directly from C-4's own text.
CHANGE MY MIND: A CMS with hard editorial workflow (draft → review → publish, publish
             permission held only by the named reviewer) genuinely satisfies the requirement.
             My objection is to the trigger as written, not to CMSs as a category.
AUTHORITY:   LOCKED — derives from C-4 (§3.4) and RDY-0003, not from architectural taste.
IMPACT IF WRONG: **Severe.** This is the single quietest path by which an unreviewed claim
             reaches a published page, and the document currently invites it in a sentence
             that reads like a scheduling note.
STATUS:      REQUIRED CHANGE
```

```
ID:          M6-L10a  LAYER: Hosting — Vercel, for STATIC PAGE DELIVERY
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE WITH CHANGES. Vercel can serve this site correctly, and the
             Owner's question "can it be deployed and operated correctly on Vercel" is
             answered YES for the pages — but only under a configuration the document does
             not specify and would not arrive at by default. The required changes, each
             evidenced in Part 2: (1) build as a fully static site with no functions and no
             routing middleware (M6-V01); (2) set Deployment Protection to Standard
             Protection with Vercel Authentication (M6-V05); (3) do NOT put Cloudflare's
             proxy in front of it (M6-V06); (4) move security headers and the `/` → `/en/`
             redirect into `vercel.json`, because `output: 'export'` supports neither
             (M6-V01); (5) handle the lead form off-platform (M6-L10b). Under those five,
             the operational surface is close to that of a static file host, which is what
             this site actually is.
BASIS:       See M6-V01, V05, V06 for the primary-source citations.
FALSIFIER:   A requirement for request-time rendering on `www.skyeagle.uk`. None exists in
             the locked IA.
CONFIDENCE:  High.
CHANGE MY MIND: If the site ever needs ISR, server-rendered personalisation or an
             authenticated area on the SAME deployment, the static-only constraint breaks and
             the whole region/residency analysis in Part 2 has to be redone. §13.1 forbids
             exactly that by putting the authenticated area on `app.skyeagle.uk`.
AUTHORITY:   HYPOTHESIS
IMPACT IF WRONG: Medium — a misconfigured Vercel project is recoverable; a lead-data leak is
             not, which is why the form is split out below.
STATUS:      REQUIRED CHANGE
```

```
ID:          M6-L10b  LAYER: Hosting — Vercel, for LEAD-FORM DATA
TYPE:        OBSERVED
STATEMENT:   VERDICT: REJECT AND REPLACE. A lead form processed by a Vercel Function cannot
             be given a Saudi-residency posture, because **Vercel has no region in Saudi
             Arabia.** Its 20 compute regions do not include one; the nearest is `dxb1`
             (me-central-1, Dubai, UAE). The default for all new projects is `iad1`
             (Washington, D.C.). Further, per Vercel's own documentation, "TLS termination:
             The Vercel region the request is routed to handles TLS encryption and
             decryption" — so a Saudi clinic owner's name, phone and email are decrypted
             outside the Kingdom on every submission, under every configuration, with no
             setting that changes it.
BASIS:       https://vercel.com/docs/regions (accessed 2026-08-20) — full region table
             reproduced: arn1 Stockholm · bom1 Mumbai · cdg1 Paris · cle1 Cleveland · cpt1
             Cape Town · dub1 Dublin · **dxb1 Dubai, UAE** · fra1 Frankfurt · gru1 São Paulo ·
             hkg1 Hong Kong · hnd1 Tokyo · iad1 Washington D.C. · icn1 Seoul · kix1 Osaka ·
             lhr1 London · pdx1 Portland · sfo1 San Francisco · sin1 Singapore · syd1 Sydney ·
             yul1 Montréal. **No Saudi region.** Same page, PoP section: "TLS termination: The
             Vercel region the request is routed to handles TLS encryption and decryption."
             https://vercel.com/docs/functions/configuring-functions/region (accessed
             2026-08-20) — "Vercel Functions execute in Washington, D.C., USA (`iad1`) **for
             all new projects**"; region limits: Hobby single region, Pro 5, Enterprise all.
FALSIFIER:   Vercel announcing a Saudi Arabian region, or documentation showing TLS
             terminating at a PoP inside the Kingdom rather than at a compute region.
CONFIDENCE:  High — read from Vercel's own current documentation today, not from memory. The
             brief specifically warned that this is where an outdated recollection does real
             damage; this is why I fetched it.
CHANGE MY MIND: **A written Owner/legal ruling that lead data — as distinct from tenant
             clinical data — may lawfully be processed outside the Kingdom.** That ruling is
             entirely available: PDPL Art. 29 permits transfer under conditions, and lead data
             is not health data. It is a legal call, not a technical one. If it is made, this
             verdict flips to APPROVE WITH CHANGES immediately and cheaply. What I reject is
             **shipping the form without the ruling**, which is what "Vercel · global CDN"
             in a one-line table row invites.
AUTHORITY:   EVIDENCE for the region facts · OPEN for the legal question (see M6-V03).
IMPACT IF WRONG: **Severe and asymmetric.** If I am wrong, the cost is one unnecessary legal
             question. If the opposite call is wrong, identifiable personal data of Saudi
             clinic owners has been exported without a lawful basis, by a vendor whose entire
             pitch is "we tell you the limitation before you find it."
STATUS:      BLOCK — the lead form may not ship on a Vercel function until M6-V03 is ruled.
```

```
ID:          M6-L10c  LAYER: Hosting — the option that was never evaluated
TYPE:        OBSERVED
STATEMENT:   The hosting row was decided against a candidate set of one. The framework
             ranking (Architecture.md:54) compares four FRAMEWORKS; no comparable ranking
             exists for HOSTS. Vercel appears with four words of justification — "Automatic
             deploys from Git, preview builds, global CDN, HTTPS" — every one of which is
             also true of the platform this organisation ALREADY OPERATES and which already
             appears in the document's own architecture diagram: **Cloudflare**. Cloudflare
             holds authoritative DNS for the entire `skyeagle.uk` zone (verified today), is
             already in front of the demo, is where the one missing §13.3 edge control
             (login rate-limiting) has to be configured anyway, and offers static hosting
             with Git deploys, preview builds and automatic TLS. Choosing Vercel gives this
             organisation **two CDNs, two edge WAFs, two TLS issuers, two log stores, two
             accounts and two bills** — for a company whose binding gate shortfall is
             explicitly **G3 OPERATIONAL readiness, not proof**.
BASIS:       Resolve-DnsName -Name skyeagle.uk -Type NS -Server 8.8.8.8 (run 2026-08-20) →
               amalia.ns.cloudflare.com, ajay.ns.cloudflare.com
             curl -sS -I https://demo.skyeagle.uk/ (2026-08-20T05:04:15Z) → `Server:
               cloudflare`, `cf-cache-status: DYNAMIC`, `CF-RAY: a2dedb2f5816a8aa-ORD`
             Architecture.md:26-27 (the diagram already names Cloudflare), :49, :78-80.
             CommitteeSystem.md:812 — "**Gate G6 (website) NOT READY.** The binding shortfall
             is **G3 operational readiness, not proof**."
FALSIFIER:   A documented evaluation of Cloudflare Pages / Workers Static Assets against
             Vercel for this project. I searched `Architecture.md` in full: the string
             "Cloudflare" appears only as the demo's existing edge (:27, :68, :79) and never
             as a hosting candidate for `www`.
CONFIDENCE:  High that the evaluation is absent. **Medium** that a single-vendor estate is the
             better answer — that is a judgement about operational capacity I cannot settle
             from here, and it depends on facts only the Owner holds (who operates this, what
             they already know).
CHANGE MY MIND: A written reason why two edge vendors is preferable to one — e.g. deliberate
             failure-domain separation between the marketing site and the demo, which is a
             genuinely respectable argument and would flip this finding. But it has to be
             WRITTEN, because right now the two-vendor estate is an accident of a one-line
             table row, not a decision.
AUTHORITY:   POLICY — I am not selecting a host; I am recording that the selection had no
             candidate set.
IMPACT IF WRONG: Medium. Being wrong here costs one unnecessary comparison. Not doing the
             comparison costs a permanent second vendor relationship in an organisation whose
             own register says operational capacity is the binding constraint.
STATUS:      REQUIRED CHANGE — evaluate at least one single-vendor option before the hosting
             row is promoted from ADVISED to locked. Ranked options are at Part 7, O-1.
```

```
ID:          M6-L11   LAYER: Source control — GitHub
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE WITH CHANGES. GitHub is right and is already the org's platform.
             The change is that the row is silent on the thing that makes it consequential:
             with Git-driven deploys, **repository write access IS publication authority over
             claims** (Architecture.md:198 says so, then stops). A GitHub repo with no branch
             protection means any collaborator can publish an unreviewed claim to production
             by pushing to `main`. The control is `main` protected, a required PR review from
             the named EV-003 reviewer via CODEOWNERS on `content/**`, and required status
             checks — all free on GitHub, all configured once.
BASIS:       Architecture.md:50, 198; `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:977`
             (RDY-0003 CLOSED; named reviewer; six gates).
FALSIFIER:   Branch protection and a CODEOWNERS file existing on the marketing repo (which
             does not exist yet — M6-B01).
CONFIDENCE:  High.
CHANGE MY MIND: Nothing — a required review is the cheapest control in this entire audit and
             there is no argument against it. If someone proposes a solo-maintainer repo with
             no review, that is C-4 being bypassed and M5 should say so.
AUTHORITY:   LOCKED — derives from C-4 (§3.4) and RDY-0003.
IMPACT IF WRONG: **Severe** — this is the mechanism §13.8 Q4 asks for, and without it the
             answer to Q4 is "nobody, mechanically."
STATUS:      REQUIRED CHANGE
```

```
ID:          M6-L12   LAYER: Analytics — GA4 + Search Console, marketing site only
TYPE:        OBSERVED
STATEMENT:   VERDICT: APPROVE WITH CHANGES, and the row contains a fact-moved defect.
             (a) "never on the demo" is correct and is now stronger than the row states:
             `CommitteeSystem.md` §13.3 item 9 has hardened it from Architecture.md §13.8 Q3's
             "default answer: no" into a **mandatory gate item** — "no session recording and
             no clinical-UI analytics on the demo. Only minimal operational and security
             events until privacy review approves more." M0 flagged this (pack item 7a);
             I confirm it independently and rule the item CONSTRAINT-DETERMINED, closed.
             Architecture.md:197 should be corrected to point at the gate.
             (b) GA4 itself has never been privacy-analysed anywhere in the corpus. GA4
             collects IP-derived location and a client identifier from Saudi visitors and
             processes them on Google infrastructure. That is the same cross-border question
             as the lead form, one degree less identifiable, and it is not covered by §13.8
             Q1, which asks only about the lead form. M0 flagged this as an undocumented gap
             (pack item 7b); I confirm the gap is real.
             (c) The row is silent on the fact that **Vercel logs personal data regardless of
             whether GA4 is installed** — see M6-V08. Turning GA4 off does not make the
             marketing site telemetry-free.
BASIS:       Architecture.md:51, 197; CommitteeSystem.md:1176-1177 (§13.3 item 9);
             https://vercel.com/docs/runtime-logs (accessed 2026-08-20) — logged fields
             include Request Path, Search Params, **Request User Agent**, Region, and the
             browser filter "works by matching your **IP address** and User Agent against
             incoming requests."
FALSIFIER:   A privacy review of GA4 for Saudi visitors existing in the corpus. I grepped:
             PDPL appears 4 times in the readiness register (M6-R01), none about analytics.
CONFIDENCE:  High on (a) and (c); High that (b) is unanalysed.
CHANGE MY MIND: On (b): a ruling that GA4's data is not personal data for this purpose, or
             that cross-border analytics is acceptable while cross-border lead data is not.
             That is a coherent position — but it must be stated, because right now the
             architecture is stricter about one than the other for no recorded reason.
AUTHORITY:   POLICY (§13.3 item 9, mandatory gate) · OPEN for GA4's own residency.
IMPACT IF WRONG: Medium for GA4; **High** if analytics ever reaches the demo, because that
             would put telemetry on a clinical UI and is a named gate failure.
STATUS:      REQUIRED CHANGE — correct Architecture.md:197 to cite the gate; open GA4
             residency as its own question alongside §13.8 Q1.
```

```
ID:          M6-L13   LAYER: Domains — www · demo · staging, with app reserved
TYPE:        OBSERVED
STATEMENT:   VERDICT: APPROVE WITH CHANGES. Three defects.
             (i) Three of the four hosts **do not exist**: `www.skyeagle.uk`,
             `staging.skyeagle.uk` and `app.skyeagle.uk` all return NXDOMAIN today; the apex
             `skyeagle.uk` has SOA only and no A record. Only `demo` resolves. The row is a
             plan, and Part 4 records it as such.
             (ii) `staging.skyeagle.uk` is introduced in §13.2 but does not appear in §13.1's
             three-environment diagram, which shows only www / demo / app. A fourth
             environment that the governing separation rule does not mention is exactly where
             an unreviewed page ends up.
             (iii) **`staging.skyeagle.uk` is the single most specific claim-register risk in
             this architecture**, and it is created by a Vercel default: Vercel applies
             `X-Robots-Tag: noindex` to generated `*.vercel.app` preview URLs, but **NOT to
             custom preview domains** — "Vercel assumes intentional staging environments and
             omits the header." A staging host carrying unapproved claim copy would therefore
             be indexable by default. See M6-V05.
BASIS:       Resolve-DnsName -Server 8.8.8.8 (run 2026-08-20): `www.skyeagle.uk`,
             `staging.skyeagle.uk`, `app.skyeagle.uk` → "DNS name does not exist";
             `skyeagle.uk` → SOA only; `demo.skyeagle.uk` → 172.67.155.245, 104.21.58.38
             (Cloudflare anycast).
             https://vercel.com/kb/guide/are-vercel-preview-deployment-indexed-by-search-engines
             (accessed 2026-08-20).
             Architecture.md:14-30 (diagram, no staging), :52.
FALSIFIER:   DNS records appearing for the three hosts; or a `vercel.json`/`next.config`
             header rule adding `noindex` on the staging host.
CONFIDENCE:  High — all three legs directly observed or read from primary documentation today.
CHANGE MY MIND: (ii) is withdrawn if staging is documented as a preview surface of the www
             environment rather than a fourth environment — which is what it should be.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: **High** for (iii). An indexed staging page carrying a prohibited claim is a
             §32 breach that is publicly citable and outside the project's control once
             cached.
STATUS:      REQUIRED CHANGE
```

```
ID:          M6-S01   RULE: §13.1 — three separate environments
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE. This is the strongest and most load-bearing rule in the
             document and I could not find a way to attack it. "Do not build the marketing
             website inside OpenEMR" is correct on technical grounds (opposite runtime
             requirements), on security grounds (three data classes, three postures), and on
             claim grounds (a marketing page inside the clinical app would inherit the app's
             session, its ACL surface and its abuse profile). It is already true in practice —
             the demo is a wholly separate Ubuntu/Apache/PHP/MariaDB host. **Zero findings
             against this rule, with coverage shown** (§7.4: zero findings is a valid result
             when the coverage is demonstrated). The only amendment is L13(ii): the rule says
             THREE environments and §13.2 lists FOUR hosts.
BASIS:       Architecture.md:8-34; CommitteeSystem.md:824-827 (the demo host as a separate
             Ubuntu instance with its own systemd services).
FALSIFIER:   A requirement that the marketing site read live application state — e.g. a
             real-time availability widget. None exists, and §32 item 25 would prohibit
             publishing uptime figures anyway.
CONFIDENCE:  High.
CHANGE MY MIND: A demonstrated need to share sessions or authentication between the marketing
             site and the demo. §13.7's launcher pattern deliberately avoids that, and
             M6-R03's headers make the shared-frame version impossible regardless.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Severe if the rule were dropped; negligible as it stands.
STATUS:      PASS
```

```
ID:          M6-S02   RULE: §13.5 — repository structure
TYPE:        INFERRED
STATEMENT:   VERDICT: APPROVE WITH CHANGES. The layout is conventional and fine. Four points.
             (i) The tree is `app/[locale]/...` — which is precisely the hand-rolled i18n
             pattern that M6-L01 shows is NOT a Next.js built-in feature, so the tree itself
             is quiet evidence against Architecture.md:40's justification.
             (ii) `content/{en,ar}/` is the right shape and is what makes the L06 parity check
             a two-line script.
             (iii) `public/{screenshots,videos,...}` will hold SS-01…SS-12 and the
             170,671-byte audit-integrity recording. Video in Git is a repository-bloat
             problem and, more importantly, §32 item 23 forbids the `admin` credential
             appearing in any material ever — so every asset in `public/` needs a
             pre-publication check, and once committed to Git history a bad frame is
             permanent. **Assets should be checked before the commit, not before the deploy.**
             (iv) The tree omits the two files that actually govern operation on Vercel:
             `vercel.json` (headers, redirects, and — with a static export — the only place
             they can live) and a CI workflow. Their absence from the tree is consistent with
             the document's broader silence on operations.
BASIS:       Architecture.md:106-140; CommitteeSystem.md:814 (SS-01…SS-12 plus the recording,
             170,671 bytes); §32 item 23 (canonical, readiness register :12045).
FALSIFIER:   A pre-commit asset check existing; a `vercel.json` appearing in the tree.
CONFIDENCE:  High on (iii) — Git history permanence is not in dispute; High on (iv).
CHANGE MY MIND: (iii) softens considerably if media is served from outside Git. That is a
             reasonable alternative and it is not in the document either.
AUTHORITY:   LOCKED for the §32 item 23 consequence · HYPOTHESIS for the layout.
IMPACT IF WRONG: High for (iii) — an `admin` credential visible in a committed screenshot is a
             §32 item 23 breach that survives deletion of the file.
STATUS:      REQUIRED CHANGE
```

```
ID:          M6-S03   RULE: §13.7 — the demo launcher pattern ("do not iframe, route to it")
TYPE:        OBSERVED
STATEMENT:   VERDICT: APPROVE — and upgrade it from advice to a recorded technical fact. The
             demo already refuses to be framed: `X-Frame-Options: DENY` and
             `Content-Security-Policy: frame-ancestors 'none'`. An iframe implementation
             would not degrade; it would render a blank box in every browser. §13.7 is
             therefore correct AND evidence-determined (M6-R03), which also means it is
             robust against a future designer deciding an embed would look nicer.
             Two additions the pattern needs:
             (i) The link must carry `rel="noopener noreferrer"` if it opens in a new tab, and
             should be `target="_blank"` — the visitor is being sent from a marketing page into
             an authenticated application, and losing the marketing page behind them is a
             funnel defect as well as a UX one.
             (ii) `Referrer-Policy` should be set on the marketing site so the demo's access
             logs do not accumulate the exact marketing URL each visitor arrived from. Minor,
             but free.
BASIS:       curl -sS -I "https://demo.skyeagle.uk/interface/login/login.php?site=default"
             (2026-08-20T05:04:31Z) — both headers present, quoted verbatim in M6-R03.
             Architecture.md:170-190.
FALSIFIER:   Removal of either header from the origin or at the Cloudflare edge.
CONFIDENCE:  High — directly observed.
CHANGE MY MIND: Nothing available. The headers make the alternative technically impossible;
             this is as settled as an architecture question gets.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Low — the correct answer is already enforced by the origin.
STATUS:      PASS
```

---

## PART 2 — VERCEL DEPLOYMENT AND OPERATIONS, IN DEPTH

This is the Owner's explicit second question. `Architecture.md` answers it in **one table row of
four words** (line 49: *"Automatic deploys from Git, preview builds, global CDN, HTTPS"*) and in
**one open question** (§13.8 Q1). Ten findings follow; each is a §7.1 finding, and each external
fact was fetched from primary documentation on **2026-08-20**, not recalled.

---

```
ID:          M6-V01   BUILD AND RENDER MODEL
TYPE:        OBSERVED
STATEMENT:   The site should be a FULLY STATIC EXPORT (`output: 'export'`), and doing so
             removes almost the entire operational surface the rest of this audit is about.
             With MDX-in-Git content and no CMS, nothing on `www.skyeagle.uk` needs
             request-time computation: there is no data source that changes between builds.
             SSR, ISR and edge runtime are all solutions to problems this site does not have,
             and each one imports a region question, a cold-start question, a cost question
             and a log-retention question that a static file does not.
             Three concrete consequences the architecture must absorb, because static export
             is not free:
             (a) `output: 'export'` does NOT support `Redirects`, `Headers`, `Proxy`
             (middleware), Route Handlers that read the Request, Cookies, ISR, Server Actions,
             or default-loader Image Optimization. So the `/` → `/en/` locale redirect and ALL
             security headers must move to `vercel.json`, which is platform config, not Next
             config. That works on Vercel and does not work on a bare static host without
             equivalent config — a real, if small, coupling to Vercel.
             (b) No Route Handler means **no form endpoint on this deployment**, which is
             consistent with M6-L10b and is a feature, not a limitation.
             (c) `next/image`'s default loader is unavailable; images must be pre-optimised at
             build or served through a custom loader.
BASIS:       https://nextjs.org/docs/app/guides/static-exports (accessed 2026-08-20) —
             "Unsupported Features … Route Handlers that rely on Request · Cookies · Rewrites ·
             Redirects · Headers · Proxy · Incremental Static Regeneration · Image
             Optimization with the default loader · Draft Mode · Server Actions · Intercepting
             Routes"; and "Route Handlers will render a static response when running
             `next build`. Only the `GET` HTTP verb is supported."
             Architecture.md:47-48 (MDX/JSON in Git, no CMS) — i.e. no request-time data.
FALSIFIER:   Any locked-IA page requiring per-request data. I read all 17 routes at
             Architecture.md:110-128; none does.
CONFIDENCE:  High — the unsupported list is quoted verbatim from current Next.js docs.
AUTHORITY:   HYPOTHESIS — no locked decision governs render mode; this is an engineering
             conclusion from the content model the document itself specifies.
IMPACT IF WRONG: If a genuine dynamic requirement emerges later, the migration from static
             export back to a server build is straightforward and low-risk — this is the
             cheap direction to be wrong in. The expensive direction is starting with SSR
             "just in case" and inheriting the whole region/logging/cost surface for pages
             that never change.
STATUS:      REQUIRED CHANGE — §13.2's Hosting row must specify the render mode. "Vercel"
             without a render mode is not an architecture.
```

```
ID:          M6-V02   REGION AND RESIDENCY
TYPE:        OBSERVED
STATEMENT:   There is no Vercel region in Saudi Arabia, and there is a second, subtler fact
             that matters more than the region list: **Routing Middleware is deployed to ALL
             regions by default, regardless of the project's region setting.** So the moment
             this project uses the conventional Next.js locale-detection middleware, it has
             deployed request-processing code to every Vercel region worldwide — including
             for a `/ar/` request from Riyadh — and no `regions` setting in `vercel.json`
             constrains it. This is the strongest single technical argument for M6-V07's
             recommendation to route locales WITHOUT middleware.
             Separately: a static export has no functions at all, so under M6-V01 the region
             question reduces to CDN cache placement, which carries no personal data beyond
             what M6-V08 covers. The residency exposure on Vercel is therefore almost entirely
             a function of the render mode, and the document never chose one.
BASIS:       https://vercel.com/docs/functions/configuring-functions/region (accessed
             2026-08-20) — "Vercel deploys Routing Middleware to all regions by default,
             regardless of your region settings"; region limits Hobby 1 / Pro 5 /
             Enterprise all; default `iad1` for all new projects.
             https://vercel.com/docs/regions (accessed 2026-08-20) — 20 compute regions,
             126 PoPs, no Saudi region, nearest `dxb1` Dubai; "TLS termination: The Vercel
             region the request is routed to handles TLS encryption and decryption."
             RDY-0064 residency decision: Dammam / `me-central2` — CommitteeSystem.md:818,
             readiness register :1083. **Not re-decided here (§8.6 out of scope); tested only
             for consistency with a Vercel-hosted front end.**
FALSIFIER:   Vercel opening a Saudi region; or documentation showing middleware honouring
             the project `regions` setting.
CONFIDENCE:  High.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: **High.** If middleware is used and someone believes `regions: ["dxb1"]`
             constrains it, they hold a false residency belief that survives review because
             the config file looks correct.
STATUS:      REQUIRED CHANGE — no routing middleware on this project, and the reason recorded.
```

```
ID:          M6-V03   LEAD-FORM DATA PATH — §13.8 Q1  ·  THE HEAVIEST ITEM
TYPE:        OBSERVED
STATEMENT:   Where a submitted name, phone and email physically lands is undecided, and every
             plausible implementation lands it outside Saudi Arabia unless one is chosen
             deliberately. The four candidate paths and where the data actually goes:
             (1) **Vercel Function** → decrypted and processed in a Vercel compute region;
                 nearest is `dxb1` (UAE), default `iad1` (USA). Never in the Kingdom.
             (2) **Third-party form service** (Formspree, Basin, HubSpot, Google Forms, etc.)
                 → the vendor's own infrastructure, typically US/EU, plus that vendor's
                 subprocessors. Also creates a second data controller relationship nobody has
                 reviewed. §13.3 item 11 explicitly forbids this: "Do not send identifiable
                 lead data to an unreviewed global form or analytics service."
             (3) **Email** (SMTP/transactional relay) → the mail provider's region, plus
                 wherever the mailbox is hosted. Usually the least examined and most exported.
             (4) **POST to an endpoint on the Dammam instance** → the only path that keeps the
                 data in the Kingdom end to end. It is also the only one that requires
                 building something, and it puts a public write endpoint on the same host as
                 the demo, which is its own security question (rate limiting, spam, DoS).
             **The corpus is not silent on this, contrary to the dispatch premise.** `Q45`
             records the organisation's own safe provisional default: "Assume **no**
             cross-border transfer of anything derived from tenant data, including logs,
             metrics and crash reports. Kingdom-only region. This is the most restrictive
             posture and can only be relaxed later, never tightened cheaply." Lead data is
             arguably NOT "derived from tenant data" — a prospect is not yet a tenant — so
             Q45 does not decide this. But it establishes the house position, and the burden
             should sit on relaxing it, not on applying it.
             **The legal frame.** Saudi PDPL Article 29 permits transfer outside the Kingdom
             only for specified purposes and subject to conditions including that the
             destination provides an adequate level of protection as assessed by the competent
             authority (SDAIA) and that only the minimum necessary data is transferred. This
             is a real regime with real conditions, not a formality — and it is a **legal
             determination, not an architectural one.** M6 does not make it and cannot.
BASIS:       grep -c "PDPL" "docs/Marketing-MVP-and-Launch-Readiness-Requirements.md" → 4
               (run 2026-08-20; the dispatch brief's "zero matches" is falsified — M6-R01).
             `docs/discovery/openemr-decision-evidence/20-unresolved-external-inputs.md:16-27`
               (Q45, quoted above) and :189 (Q45, Blocking? **Yes**).
             CommitteeSystem.md:1180-1183 (§13.3 item 11) and :1456 (§15.1 item 4 — still open).
             Architecture.md:195 (§13.8 Q1).
             https://vercel.com/docs/regions (accessed 2026-08-20) — no Saudi region.
             PDPL Art. 29 conditions: https://www.kslaw.com/news-and-insights/international-personal-data-transfers-under-saudi-arabias-data-protection-law
               and https://securiti.ai/regulation-on-personal-data-transfer-outside-the-kingdom/
               (both accessed 2026-08-20). **These are law-firm and vendor summaries, not the
               statute.** I flag that explicitly: a decision of this class should rest on the
               SDAIA text and Saudi counsel, not on a secondary source an agent fetched.
FALSIFIER:   A written legal opinion that lead data may be processed outside the Kingdom; or
             a ruling that the qualifying form collects no personal data (it does — §13.2
             field 5 is "Clinic name + work email").
CONFIDENCE:  High that the question is open and unanswered. **Low on the legal answer, and I
             decline to guess it** — "Unverified" is an acceptable answer under R10;
             "probably fine" is not.
AUTHORITY:   OPEN — Owner/legal. Also POLICY: §13.3 item 11 already forbids the unreviewed
             global-form path, so option (2) is closed by an existing gate item today.
IMPACT IF WRONG: **Severe.** This is the only finding in the audit with a legal edge. It also
             carries a positioning consequence larger than the legal one: a vendor whose D-1
             differentiator is "every claim carries its limitation" and whose D-2 is "your
             data stays yours" exporting its own prospects' contact details to a US region by
             default is not a compliance defect — it is a **demonstration against the pitch**,
             and a competitor or a prospect's IT contractor (who holds the veto, GTM PER-001)
             could observe it from outside.
STATUS:      **OPEN — OWNER DECISION.** Ranked options at Part 7, O-2. No lead form ships
             until this is ruled.
```

```
ID:          M6-V04   SECRETS AND ENVIRONMENT
TYPE:        INFERRED
STATEMENT:   A correctly-scoped static marketing site needs **almost no secrets** — and the
             architecture should be designed to keep it that way, because the blast radius is
             asymmetric. Under M6-V01 the full secret inventory is: a GA4 measurement ID
             (public by design, not a secret), a Search Console verification token (public),
             and — only if the form is self-hosted — one form-endpoint credential. That is it.
             The real exposure is not secrets; it is **authority**:
             (a) **GitHub compromise** = publication authority over every claim on the site
             (M6-L11). No secret is needed to do damage; a push is enough.
             (b) **Vercel account compromise** = the ability to change the production domain's
             target, add environment variables, disable Deployment Protection, and read every
             runtime log. Vercel's Git integration means a Vercel-account attacker generally
             does NOT need GitHub, and a GitHub attacker does not need Vercel — **two
             independent paths to the same outcome**, which is the cost of the two-vendor
             estate M6-L10c describes.
             (c) The mitigations are all free and all administrative: mandatory 2FA on both
             GitHub and Vercel, no long-lived deploy tokens, `NEXT_PUBLIC_`-prefixed variables
             audited (they are bundled into client JS and are not secret), and no secret ever
             placed in a preview environment where M6-V05's protections are the only barrier.
BASIS:       Architecture.md:49-51; https://vercel.com/docs/deployment-protection (accessed
             2026-08-20) for the account-level control surface; M6-V01 for the render mode
             that determines the inventory.
FALSIFIER:   A requirement that adds a real secret — a CRM API key, a payment key, a CMS
             token. Each such requirement should be treated as a C-2 trigger, because it
             changes this finding.
CONFIDENCE:  High on the inventory being near-empty under a static export; High on the
             two-path authority observation.
AUTHORITY:   POLICY
IMPACT IF WRONG: Medium. The genuine risk is not credential theft but **quiet publication** —
             an attacker with repo write who edits one qualification out of one claim would
             likely not be noticed by any control now proposed except the required review.
STATUS:      REQUIRED CHANGE — record the secret inventory explicitly, and treat any addition
             to it as a C-2 trigger.
```

```
ID:          M6-V05   PREVIEW DEPLOYMENTS
TYPE:        OBSERVED
STATEMENT:   The named risk is real but **cheaper to close than the brief implies**, and the
             residual risk sits somewhere other than where it is usually assumed.
             (a) **Generated preview URLs are already protected twice over by default.**
             Vercel applies `X-Robots-Tag: noindex` automatically to `*.vercel.app` preview
             URLs, and Deployment Protection's **Standard Protection with Vercel
             Authentication is available on ALL plans, including Hobby** — it restricts
             previews to logged-in Vercel team members at **zero additional cost**. The
             expensive options (Password Protection, Trusted IPs, Passport, and protecting the
             production domain) are Enterprise or a **$150/month** Pro add-on, and **none of
             them is needed** for the stated risk.
             (b) **The residual risk is the custom staging domain, not the preview URL.**
             Vercel does NOT apply the `noindex` header to custom preview domains — it
             "assumes intentional staging environments and omits the header." So
             `staging.skyeagle.uk` (Architecture.md:52) is precisely the case that is
             indexable by default, and it is the one the architecture explicitly plans to
             create. The fix is a `vercel.json` host-conditioned header rule or the
             `VERCEL_ENV !== 'production'` check in `next.config`, both free.
             (c) One further consequence worth naming: Deployment Protection "requires
             authentication for all requests, including those to Routing Middleware" — which
             is a second, independent reason not to build locale routing on middleware
             (M6-V07): protected previews and middleware-based routing interact.
BASIS:       https://vercel.com/docs/deployment-protection (accessed 2026-08-20) — protection
             methods and plan availability quoted; "Advanced Deployment Protection features
             are available to Enterprise customers by default. Pro plan customers can access
             these features for an additional $150 per month"; "Deployment Protection requires
             authentication for all requests, including those to Routing Middleware."
             https://vercel.com/kb/guide/are-vercel-preview-deployment-indexed-by-search-engines
             (accessed 2026-08-20) — "preview deployments are **not indexed by default**.
             Vercel applies the header `X-Robots-Tag: noindex`"; "**Custom preview domains:
             Not protected** — Vercel assumes intentional staging environments and omits the
             header."
FALSIFIER:   Observing a `*.vercel.app` preview URL without the `noindex` header, or a
             changed default in Vercel's project settings.
CONFIDENCE:  High — read from Vercel's current documentation today. I specifically did not
             rely on the widely-repeated claim that "every preview is public"; it is out of
             date for the generated URLs and true only for custom preview domains.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: **High if (b) is missed.** An indexed staging page carrying an unapproved
             claim is publicly citable and, once cached, outside the project's control — the
             exact failure a binding claim register exists to prevent. **Low cost to close**:
             one settings toggle and one header rule, both free.
STATUS:      REQUIRED CHANGE — enable Standard Protection + Vercel Authentication (free) at
             project creation, and add the `noindex` header rule for the staging host.
             **Do not buy the $150/month add-on for this risk; it does not address it.**
```

```
ID:          M6-V06   CUSTOM DOMAIN, DNS AND TLS
TYPE:        OBSERVED
STATEMENT:   Cloudflare holds authoritative DNS for the whole `skyeagle.uk` zone, which makes
             this a genuine architectural interaction rather than a setup step — and Vercel's
             own documentation advises against the configuration this estate would naturally
             fall into.
             (a) **Nameserver authority.** `skyeagle.uk` NS = `amalia.ns.cloudflare.com`,
             `ajay.ns.cloudflare.com` (verified 2026-08-20). Vercel's recommendation for a
             custom domain is to move DNS to Vercel — which would mean moving authority for
             the zone that also serves `demo.skyeagle.uk` and its Cloudflare edge controls.
             **That should not be done**, and the alternative (keep Cloudflare NS, add records
             pointing at Vercel) forces the proxied/unproxied choice in (b).
             (b) **Cloudflare proxy in front of Vercel: Vercel says do not.** "Vercel does not
             recommend using a reverse proxy in front of Vercel … Vercel no longer has full
             traffic visibility, which prevents the Vercel Firewall and our threat
             intelligence products from working to their full potential," plus stated latency,
             cache-reliability and bot-detection degradation. If proxied anyway, Cloudflare's
             SSL/TLS mode must be **Full (strict)** — the default "Flexible" causes a redirect
             loop and, worse, means Cloudflare→origin traffic is plaintext. **Recommendation:
             `www` as DNS-only (grey cloud) in Cloudflare, pointing at Vercel; `demo` stays
             proxied (orange) because its rate-limiting requirement lives there.** This gives
             a split-mode zone, which is workable but must be documented, because a future
             operator flipping `www` to orange to "add protection" would degrade it.
             (c) **Apex vs www.** The apex `skyeagle.uk` has SOA only and no A record today.
             Choose one canonical host and 301 the other; do not serve both. Note that
             `skyeagle.uk` is a `.uk` domain — an apex CNAME is not possible, so Vercel's
             A-record path or a Cloudflare CNAME-flattening record is required.
             (d) **HSTS.** `demo.skyeagle.uk` sends `Strict-Transport-Security:
             max-age=15552000` with **no `includeSubDomains` and no `preload`** — verified
             today, twice. That is the safe configuration for this estate: the demo's HSTS is
             host-scoped and therefore does NOT force HTTPS on `www`, `staging` or `app`.
             **If `includeSubDomains` were ever added at the apex, every sibling host would be
             HTTPS-only for 180 days from each visit, including hosts that do not yet exist.**
             That would be a good end state and a dangerous transition — it must not be
             switched on before all four hosts serve valid TLS.
             (e) **Certificates.** Vercel issues its own certificate for a custom domain; if
             the record is Cloudflare-proxied, Cloudflare intercepts the ACME validation and
             Vercel's issuance can fail — the single most common failure mode in this exact
             topology. DNS-only avoids it entirely.
BASIS:       Resolve-DnsName -Name skyeagle.uk -Type NS -Server 8.8.8.8 (2026-08-20) →
               amalia.ns.cloudflare.com, ajay.ns.cloudflare.com.
             curl -sS -I https://demo.skyeagle.uk/ (2026-08-20T05:04:15Z) → `Server:
               cloudflare`, `Strict-Transport-Security: max-age=15552000` (no
               `includeSubDomains`), `X-Content-Type-Options: nosniff`, TLS verify=0.
             curl -sS -I http://demo.skyeagle.uk/ (2026-08-20T05:06:57Z) → `HTTP/1.1 301
               Moved Permanently`, `Location: https://demo.skyeagle.uk/` — the §9.4 claim of
               an HTTP→HTTPS 301 re-verified today.
             https://vercel.com/kb/guide/cloudflare-with-vercel (accessed 2026-08-20) —
               quoted above.
FALSIFIER:   A change of nameservers; Vercel changing its reverse-proxy guidance; observing
             `includeSubDomains` appear on the demo's HSTS header.
CONFIDENCE:  High on every leg — all directly observed or quoted from primary documentation
             on 2026-08-20.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Medium-High. The failure modes here (certificate issuance failure, redirect
             loop, plaintext origin leg under Flexible SSL) are all recoverable but are all
             classic, and RDY-0085 — "TLS/HTTPS, domain and DNS for any instance a customer
             touches" — is a **P0 item still recorded NOT READY**, gating G3 and G6. The
             marketing site's TLS/DNS is therefore not a greenfield task; it lands on top of
             an already-open P0.
STATUS:      REQUIRED CHANGE — record the DNS-only/proxied split decision and the HSTS
             sequencing rule before any record is created.
```

```
ID:          M6-V07   I18N ROUTING MECHANICS
TYPE:        OBSERVED
STATEMENT:   The claimed benefit that justifies the whole framework choice — "locale-aware
             routing and static generation are the two features this project specifically
             needs" (Architecture.md:40) — **does not distinguish Next.js from the
             alternative it ranks second.** It is closer to being an argument for Astro.
             (a) The Next.js App Router has **no built-in i18n routing**. The documented
             approach is a `[lang]` dynamic segment plus `generateStaticParams`, with an
             optional hand-written `proxy.js` (middleware) for `Accept-Language` detection.
             (b) Astro ships i18n as configuration: `locales`, `defaultLocale`, `routing`,
             `prefixDefaultLocale`, `redirectToDefaultLocale`, `fallback`, plus helpers such
             as `getRelativeLocaleUrl()` — and a `fallback` behaviour for partially-translated
             sites, which is directly relevant to WEB-003's parity risk.
             (c) **The concrete recommendation, which matters more than the framework
             question:** implement locale routing with `app/[lang]/` + `generateStaticParams`
             and **no middleware at all**. Serve `/` as a static redirect (or a language
             chooser) declared in `vercel.json`, not detected at request time. Middleware
             would force dynamic rendering, deploy to all regions regardless of the `regions`
             setting (M6-V02), interact with Deployment Protection (M6-V05c), and add a
             request-time execution surface to a site that has none — all to save a visitor
             one click on the first visit.
             (d) The failure modes that must be explicitly tested, none of which the document
             mentions: locale-prefixed 404s (`/ar/pricingg` must render an ARABIC 404, in RTL
             — a default 404 is an LTR English page); `hreflang` reciprocal annotations
             (`en`↔`ar`, plus `x-default`); a `<link rel="canonical">` per locale that does
             NOT cross-point; **per-locale sitemaps** (a single sitemap with mixed locales is
             the common bug); and `lang`/`dir` present on `<html>` at the SERVER-rendered
             HTML level, not applied by client JS after paint.
BASIS:       https://nextjs.org/docs/app/guides/internationalization (accessed 2026-08-20) —
               the whole documented approach; `generateStaticParams` static-rendering section.
             https://docs.astro.build/en/guides/internationalization/ (accessed 2026-08-20) —
               config keys and helpers confirmed.
             https://vercel.com/docs/functions/configuring-functions/region (accessed
               2026-08-20) — middleware deploys to all regions regardless of settings.
             https://nextjs.org/docs/app/guides/static-exports (accessed 2026-08-20) — `Proxy`
               and `Redirects` unsupported under `output: 'export'`, hence `vercel.json`.
             Architecture.md:40, 46, 84-99.
FALSIFIER:   A Next.js version shipping built-in App Router i18n config; or a demonstrated
             requirement for request-time locale detection (SEO does not require it — search
             engines crawl the prefixed URLs directly).
CONFIDENCE:  High.
AUTHORITY:   EVIDENCE for the framework facts · HYPOTHESIS for the no-middleware
             recommendation, which is an engineering judgement.
IMPACT IF WRONG: Medium technically; **High for the record.** Architecture.md:40 is currently
             the load-bearing justification for a stack choice, and it is not true. Leaving it
             in place means the next reader inherits a false premise.
STATUS:      REQUIRED CHANGE
```

```
ID:          M6-V08   ANALYTICS AND LOGGING
TYPE:        OBSERVED
STATEMENT:   **Vercel logs personal data regardless of whether GA4 is installed**, and this is
             nowhere in the architecture. Runtime log details include Request Path, Search
             Params, **Request User Agent**, Region, Host, and status; the dashboard's
             "logs from your browser" filter "works by matching your **IP address** and User
             Agent against incoming requests" — i.e. client IP is retained and queryable.
             Retention: **Hobby 1 hour · Pro 1 day · Pro with Observability Plus 30 days ·
             Enterprise 3 days · Enterprise with Observability Plus 30 days.** There is no
             documented control over the geography of log storage, and **no option to disable
             logging.**
             Two consequences:
             (i) An IP address plus a request path plus a timestamp is personal data under a
             broad reading of PDPL, and it leaves the Kingdom by construction. It is
             substantially less sensitive than the lead form, and the retention windows are
             short — which is a genuine mitigating fact and I state it rather than inflating
             the finding. But it means "we host in Saudi Arabia" cannot be said of the
             marketing site under any Vercel configuration, and §32-adjacent copy about
             hosting must not imply otherwise.
             (ii) **Cross-check against §13.3 item 9, as instructed.** Item 9 — "no session
             recording and no clinical-UI analytics on the demo. Only minimal operational and
             security events until privacy review approves more" — is a firm mandatory gate,
             and it is *stricter* than Architecture.md:197's "default answer: **no** analytics
             on `demo.skyeagle.uk`". M0 recorded this as a fact-moved flag; I confirm it
             independently and the item is **CLOSED, constraint-determined**: no analytics on
             the demo, no reopening requested. Architecture.md:197's framing as an open
             question is stale and should be corrected to cite the gate. Note also that the
             demo is a separate Ubuntu host and Vercel logs nothing about it — the two
             telemetry surfaces are genuinely separate, which is §13.1 working correctly.
             (iii) A binding claim constraint that touches this directly: the readiness
             register carries "**Saudi hosting alone does not prove regulatory compliance.**
             'Hosted in Saudi Arabia (Google Cloud, Dammam `me-central2`)' is a statement of
             *architecture*, not of PDPL, CHI, NPHIES or ZATCA conformance. Any copy implying
             otherwise is a prohibited claim under §32." That constraint applies to the
             marketing site's own copy about itself.
BASIS:       https://vercel.com/docs/runtime-logs (accessed 2026-08-20) — log detail fields
               and retention table quoted verbatim above.
             CommitteeSystem.md:1176-1177 (§13.3 item 9); Architecture.md:197.
             `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:9629-9633` — the claim
               constraint, quoted verbatim.
FALSIFIER:   Vercel documenting a region-pinned or disableable log store; or a ruling that
             request logs are not personal data for this purpose.
CONFIDENCE:  High on the platform facts; **Medium** on the legal characterisation of IP+path
             as personal data under PDPL specifically — that is a legal question and I mark
             it as such rather than asserting it.
AUTHORITY:   EVIDENCE (platform facts) · POLICY (§13.3 item 9) · LOCKED (the §32 claim
             constraint at register :9629).
IMPACT IF WRONG: Medium. The realistic harm is not the logs themselves — it is a page saying
             or implying "Saudi-hosted" while the front end demonstrably is not, which is a
             claim defect on the site's own most inspectable property.
STATUS:      REQUIRED CHANGE — record the log surface in the architecture; correct
             Architecture.md:197; add "no hosting-location claim about the marketing site"
             to the copy constraints.
```

```
ID:          M6-V09   COST, LOCK-IN AND THE EXIT PATH
TYPE:        OBSERVED
STATEMENT:   (a) **Cost is not the issue.** Vercel Pro is a **$20/month platform fee** with 1
             deploying seat, $20/month usage credit, 1 TB Fast Data Transfer and 10M Edge
             Requests included; additional deploying seats $20/month; viewer seats free. A
             Saudi outpatient-clinic marketing site will not approach those allocations. The
             add-ons that matter here are Advanced Deployment Protection **$150/month** (not
             needed — M6-V05), Observability Plus (only if 30-day log retention is wanted —
             note that WANTING longer retention is the opposite of the residency posture), and
             Speed Insights $10/month/project. Hobby is free but single-region and cannot
             protect a production domain; Pro is the realistic tier. **Cost is not a reason to
             reject Vercel and I will not pretend it is.**
             (b) **Lock-in is real but small — IF the static-export decision is taken.** A
             static export is a folder of HTML/CSS/JS that "can be deployed and hosted on any
             web server that can serve HTML/CSS/JS static assets." The exit path is then:
             point DNS elsewhere, upload `out/`. The Vercel-specific residue is `vercel.json`
             (headers and redirects), which must be re-expressed in the new host's config —
             a genuine but bounded cost, perhaps an afternoon. **If instead the project uses
             ISR, Server Actions, Vercel-hosted middleware or Vercel storage, the exit
             becomes a rewrite**, because those are Vercel-shaped primitives with no
             portable equivalent. **The exit cost is therefore a direct function of the
             render-mode decision at M6-V01, which the document never made.**
             (c) **The positioning consistency test, run explicitly as instructed.** D-2 is
             "data ownership and a documented exit, structurally unmatchable by a proprietary
             vendor." A vendor selling a documented exit whose own marketing site cannot be
             moved off a proprietary hosting platform is a positioning exposure, not only a
             technical one. Under (b)'s static-export path, **the exposure closes** — the exit
             is trivially documentable and could even be published as a small proof of the
             practice. Under the SSR/ISR path it does not close. This is the clearest case in
             the audit where a technical decision and the positioning point the same way, and
             it is a second independent argument for M6-V01.
             (d) **§32 item 25 applies to publication, not measurement.** Uptime and
             performance figures may not be published. The existing monitoring on
             `demo-openemr` measures six signals including availability — that is correct and
             must continue. Vercel Speed Insights would likewise be fine to run and prohibited
             to publish. **No number from either may reach a page**, in either language.
BASIS:       https://vercel.com/docs/plans/pro (accessed 2026-08-20) — platform fee, credit,
               included allocations, seat pricing, and the add-on price list quoted above.
             https://nextjs.org/docs/app/guides/static-exports (accessed 2026-08-20) — "can be
               deployed and hosted on any web server that can serve HTML/CSS/JS static assets."
             §32 item 25, canonical: `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:12047`
               — "Manufactured trust: … **uptime or performance figures** … **No such section
               may exist**."
             CommitteeSystem.md:795 (D-2); readiness register :1123 (the six monitoring
               signals live on `demo-openemr`).
FALSIFIER:   A Vercel pricing change; or a decision to use ISR/Server Actions, which would
             invalidate (b) and (c).
CONFIDENCE:  High on pricing and on the exit mechanics; High on (d).
AUTHORITY:   LOCKED for (d) (§32 item 25) · EVIDENCE for (a) and (b) · POLICY for (c).
IMPACT IF WRONG: Low on cost. **Medium-High on (c)** — a positioning inconsistency that a
             prospect's IT contractor could notice is worth more than the hosting bill.
STATUS:      REQUIRED CHANGE — write the exit path down, in one paragraph, before build
             starts. It is the cheapest possible proof of D-2 and it costs nothing to author
             while the answer is still "point DNS elsewhere and upload a folder."
```

```
ID:          M6-V10   WHO OPERATES IT — §13.8 Q4
TYPE:        INFERRED
STATEMENT:   Architecture.md:198 states the requirement correctly — "The claim review
             (`EV-003`) has to sit *before* merge, not after deploy" — and then provides no
             mechanism. Today the answer to "what enforces it" is **nothing**: the repository
             does not exist, so there is no branch protection, no CODEOWNERS and no required
             check. The good news is that all the human parts already exist and are closed:
             RDY-0003 is CLOSED with a **named** claim reviewer and a written six-gate review
             procedure. What is missing is purely mechanical and is four settings:
             (1) `main` protected; direct pushes blocked, including for admins.
             (2) CODEOWNERS assigning `content/**` and any `.mdx` to the named reviewer, with
                 "require review from Code Owners" on.
             (3) A required status check running the mechanical scans that already exist as
                 procedures — the §32 banned-term scan, the `MC-`/`CLM-` trace scan, the
                 locale-parity check (M6-L06), and the token check (M6-L04). The scans are
                 already written as regexes in the closure records for RDY-0056/0057; making
                 them a CI job is transcription, not invention.
             (4) Vercel's production deploy sourced ONLY from `main`, so there is no path from
                 a branch to the production domain.
             **The distinction that matters:** (1), (2) and (4) enforce *who*; (3) enforces
             *what*. A procedural rule ("the reviewer checks before merge") is what the
             document has now, and §6.4's own honesty about R9 — "a prompt instruction is not
             a control" — is the precedent for why that is insufficient.
BASIS:       Architecture.md:198; `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:977`
               (RDY-0003 CLOSED, named reviewer, `EV-003` §3 six gates C-1…C-6, sample review
               performed);  :1060 and :1061 (RDY-0056/0057 closures each record a re-runnable
               mechanical scan regex);
             CommitteeSystem.md:384-395 (§6.4 — "R9 as written claims to *prevent*. Until a
               hook exists it does not … a prompt instruction is not a control").
FALSIFIER:   The four settings existing on the marketing repository once it is created.
CONFIDENCE:  High.
AUTHORITY:   LOCKED — C-4 (§3.4) plus RDY-0003 make the review mandatory; only the mechanism
             is open.
IMPACT IF WRONG: **Severe.** With Git-driven deploys, an unenforced review means the claim
             register is protected by habit. This project has already written down, in its own
             governance file, why that is not a control.
STATUS:      REQUIRED CHANGE
```

---

## PART 3 — §13.3 SECURITY GATE: EVERY ITEM CARRIES A RESULT

`CommitteeSystem.md` §13.3 (lines 1155–1183) is C-2's gate. Per §8.6's acceptance criterion, every
item gets a result from me. **Scope note:** this gate was written for TASK 1 (demo access). Several
items are about the demo, not the marketing site. I answer each anyway, and mark clearly where the
architecture under audit does or does not affect it — an item being out of this task's scope is not
the same as an item having no result.

**Standing caveat applied throughout (§8.6): "synthetic data is not a security approval."** Abuse,
reputational damage, configuration mutation and credential compromise all remain possible on
`demo.skyeagle.uk` without any real PHI, and none of the results below rest on the data being fake.

| # | Gate item | RESULT | Basis / reasoning |
|---|---|---|---|
| **1** | Credentials — dedicated non-admin, least privilege, none in screenshots/repo history/analytics; rotation and revocation tested | **OPEN — not closed by this task, and one leg is newly at risk from the architecture** | Six demo role accounts exist and are non-admin (CommitteeSystem.md:1344 — `gacl_aro_groups` places `r.aldosari` in Front Office, `y.alharbi` in Physicians, neither in Administrators). Rotation/revocation **not evidenced as tested** anywhere I could find. **New exposure created by the architecture:** §13.5 puts `public/screenshots` and `public/videos` in Git (M6-S02(iii)). §32 item 23 forbids the `admin` credential in any material ever, and a committed frame is permanent in Git history. |
| **2** | Access mode — start manual and time-limited; no published permanent shared credential | **PASS (unaffected)** — and the architecture must not weaken it | `STEP0-001` Reading B, D-b (time-boxed) and CommitteeSystem.md:1161. The marketing site's `/demo` page must present the credential as the fulfilment of a booking, never print one. §13.7's launcher pattern is consistent with this. **Note Task 1 is HELD by `SEED-001`** (CommitteeSystem.md:1440), so no credential issues yet regardless. |
| **3** | Edge controls — rate-limit login and credential-request endpoints; bot protection; bounded retries; alerting that does not expose clinical-screen content | **FAIL — still open, and the architecture makes it marginally harder** | Confirmed still missing (Architecture.md:78-80; CommitteeSystem.md:1163-1165). It must be configured **at Cloudflare**, in front of the demo. Cloudflare's **Free plan includes one rate-limiting rule** and Bot Fight Mode; Pro (~$20-25/mo) raises it to ~10 rules — so the control is available at or near zero cost and there is no budget excuse. **Architecture interaction:** the marketing site adds a *second* endpoint class needing rate limiting — the lead form and the credential-request form. If those live on Vercel, they are **outside** the Cloudflare rate limiter that the demo sits behind, so one control now has to be configured twice, in two vendors, with two rule syntaxes. That is a concrete operational cost of the two-vendor estate (M6-L10c) and it lands on the one gate item already recorded as failing. |
| **4** | Shared-data disclosure — prominent notice before entry: shared, synthetic, reset periodically, unsuitable for real data. **Never claim isolation** | **OPEN — and it is the marketing site's job, which the architecture does not assign** | The notice must appear on `/demo` (Architecture.md:172-190) *before* the visitor leaves for the demo host, because after the redirect they are in OpenEMR's own login page which the project does not want to modify. **§13.5's component list has no component for it.** `QualifiedClaim` is for claims; this is a safety notice. This is a real omission in the component list and it is M4's lane to design. **"Never claim isolation" also constrains copy**: the product is not multi-tenant (§32 item 8) and the demo is one shared database. |
| **5** | Synthetic-data invariant — automated pre/post-reset checks; any failure closes access automatically | **OPEN — unaffected by this architecture** | Belongs to `SEED-001`'s scheduled job on the demo host. Nothing in `Architecture.md` touches it. Recorded so the item is not silently dropped: the reset is **not** scheduled and has no demo-host mechanism (CommitteeSystem.md:940-947). |
| **6** | Reset safety — single-instance lock; transactional/idempotent; date re-base in the same job; health check; rollback baseline; logged result | **OPEN — unaffected** | `SEED-001` splits re-base from reset (CommitteeSystem.md:1231-1236); the reset has no mechanism on the demo host. Out of this task's scope; recorded, not dropped. |
| **7** | Concurrency — two users in different roles across a reset boundary; document leakage, collision, stale-session behaviour | **OPEN — unaffected** | Demo-host behaviour. One architecture-adjacent note: the marketing site's `/demo` page will send **multiple simultaneous visitors** into one shared database, and §13.7's two-credential pattern doubles the sessions per visitor. The site should therefore not encourage simultaneous group access. |
| **8** | Route controls — block admin/global-config routes not required by the two-role proof; convert §40 warnings into pre-route notices or hard restrictions | **PASS with one open row — unaffected by this architecture** | `NOGO-001`: 12 of 14 §40 rows already enforced by D-c; row 9 restricted (script prepared, `apply` untested); row 7 accepted and disclosed (CommitteeSystem.md:1313, 1332). The **denial-UX inconsistency** (five shapes including a 500 — CommitteeSystem.md:973-975) remains open and is partly a *website* job: the site can set the visitor's expectation before they leave. |
| **9** | **Telemetry boundary — no session recording and no clinical-UI analytics on the demo** | **PASS — and I confirm the hardening M0 flagged** | The architecture complies: Architecture.md:51 says "Marketing site only — never on the demo." Independently ruled **CONSTRAINT-DETERMINED and closed**: §13.3 item 9 is a mandatory gate and supersedes Architecture.md:197's "default answer" framing (M6-V08(ii)). **One addition:** the boundary is about the demo, and it is silent on the marketing site's own telemetry — where Vercel logs IP and User Agent whether or not GA4 exists (M6-V08). The gate should be extended to say what "minimal operational and security events" means on the *marketing* side too. |
| **10** | Kill switch — one documented action disables new access and revokes issued credentials without waiting for a deployment | **FAIL — and the architecture introduces a new, unaddressed dependency** | No such documented action exists that I could find. **Architecture interaction, and it is a real one:** with a **static** marketing site, taking the `/demo` page down or removing the CTA **requires a rebuild and redeploy** — i.e. it *does* wait for a deployment, which is exactly what this item forbids. The correct kill switch is therefore **not** on the marketing site at all: it is (a) revoking the credentials in OpenEMR and (b) a Cloudflare rule on `demo.skyeagle.uk`, both of which act in seconds and neither of which needs a build. **That must be written down**, because the intuitive answer ("take the page down") is the slow one. |
| **11** | **Lead-data residency — undecided; Saudi PDPL is the relevant regime; do not send identifiable lead data to an unreviewed global form or analytics service** | **FAIL — this is the audit's heaviest open item** | See M6-V03 in full. The item as written **already closes** the third-party-form-service option (path 2), which is the cheapest and most commonly chosen one — so the gate is doing real work today and should be cited when that option is proposed. The Vercel-function option is closed on the facts (no Saudi region, TLS terminated abroad — M6-L10b). What remains is an Owner/legal ruling. **Ranked options at Part 7, O-2.** Note the second half of the item — "or analytics service" — is not currently honoured by the GA4 recommendation at Architecture.md:51 (M6-L12(b)). |

**Coverage statement (§7.4).** All 11 items were read from the canonical source and each carries a
result: **2 PASS · 1 PASS-with-open-row · 3 FAIL · 5 OPEN.** Adversary performance is measured by
coverage, not objection count; the coverage is complete and is shown above.

---

## PART 4 — ALREADY BUILT AND VERIFIED vs PROPOSED

§14's acceptance requires this distinction. `Architecture.md` §13.3 claims "the demo half is already
built." **I tested that claim rather than accepting it (§8.6: "'the demo half is already built' is a
status claim you should test").** It is substantially true, with two corrections.

### 4.1 The ledger

| Component | Claimed | **Verified by me, 2026-08-20** | Verdict |
|---|---|---|---|
| Ubuntu + Apache + PHP + MariaDB | Running | Serving; `302 → interface/login/login.php?site=default`, then `200` on the login page | **BUILT** |
| HTTPS, valid cert | Valid cert | `ssl_verify_result=0`; chain valid | **BUILT** |
| HTTP → HTTPS 301 | 301 | `curl -I http://demo.skyeagle.uk/` → `HTTP/1.1 301`, `Location: https://demo.skyeagle.uk/` | **BUILT** |
| HSTS | `max-age=15552000` | Confirmed, **and no `includeSubDomains`, no `preload`** — a detail the claim omits and M6-V06(d) needs | **BUILT, claim incomplete** |
| Cloudflare in front | In place | `Server: cloudflare`, `cf-cache-status: DYNAMIC`, anycast A records `172.67.155.245` / `104.21.58.38` | **BUILT** |
| Anti-framing | not claimed | `X-Frame-Options: DENY` + `CSP: frame-ancestors 'none'` — **stronger than the document knew** | **BUILT, undocumented** |
| Search-engine exclusion | not claimed | `robots.txt` → `User-agent: * / Disallow: /` (HTTP 200) — a real control, undocumented | **BUILT, undocumented** |
| Daily backup, firewall, monitoring | Running | Not re-verified by me (no host access); readiness register :1120-1123 records live-verified systemd timers for off-instance backup (R2) and six monitoring signals | **BUILT — relayed, not re-observed here** |
| Demo reset mechanism | Exists and proven (PB-424), not scheduled | Contradicted by the newer governing text: `EV-044` is a **local Windows** runbook; on the demo host "the mechanism **and the baseline dump do not exist**" | **NOT BUILT on the demo host** |
| Synthetic data only | Holds | Not re-verified by me | **Relayed** |
| Login rate-limiting | Missing | Confirmed still open in the current gate | **NOT BUILT** |
| **`www.skyeagle.uk`** | Planned | **NXDOMAIN** | **NOT BUILT** |
| **`staging.skyeagle.uk`** | Planned | **NXDOMAIN** | **NOT BUILT** |
| **`app.skyeagle.uk`** | Reserved | **NXDOMAIN** — *not even reserved in DNS* | **NOT BUILT** |
| Apex `skyeagle.uk` | — | SOA only, no A record | **NOT BUILT** |
| The marketing repository | Proposed | No `marketing-website/` directory exists in this repo | **NOT BUILT** |
| `brand/tokens/thiqa-tokens.json` | Referenced | Exists, 2,706 bytes | **BUILT** |

**Net:** the demo half is genuinely built and I recommend rebuilding **none** of it (§8.6: "do not
recommend rebuilding something that exists and works"). **The marketing half is entirely
unbuilt** — zero of its four hosts resolve and its repository does not exist. Everything in Parts 1
and 2 is therefore advice about a greenfield build, which is the cheapest moment for all of it.

### 4.2 Two security findings on the already-built half

```
ID:          M6-B01
TYPE:        OBSERVED
STATEMENT:   The demo's PHP session cookie is set **without `HttpOnly` and without `Secure`**.
             The neighbouring `App` cookie in the same response carries `HttpOnly`, so this is
             an inconsistency in the same response rather than a blanket configuration.
             Practical exposure is bounded — HSTS (`max-age=15552000`) stops a browser that
             has visited before from sending it in plaintext, and `SameSite=Strict` blocks
             cross-site sending — but the missing `HttpOnly` means **any XSS on the demo can
             read the session identifier**, and a stolen demo session is a live authenticated
             session on a shared clinical UI. **"Synthetic data" does not answer this**: the
             consequence is configuration mutation, data corruption for other concurrent
             visitors, and a reputational event, none of which needs real PHI.
BASIS:       curl -sS -I "https://demo.skyeagle.uk/interface/login/login.php?site=default"
             — 2026-08-20T05:04:31Z:
               Set-Cookie: App=OpenEMR; expires=...; path=/; HttpOnly; SameSite=strict
               Set-Cookie: OpenEMR=ei21d6ugou86qm5itbvp3bum88; path=/; SameSite=Strict
             The second line has neither `HttpOnly` nor `Secure`.
FALSIFIER:   A later response carrying `HttpOnly; Secure` on the `OpenEMR` cookie — re-run
             the command above; it is one line.
CONFIDENCE:  High for the observation. **Medium** for the root cause: this is most likely
             upstream OpenEMR's `session.cookie_httponly` / `cookie_secure` PHP settings on
             that host, not a Thiqa change — I could not verify the host's `php.ini` and do
             not assert it.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Low if wrong. If right and unfixed, it is a standing session-theft path on
             the one host this project will send unaccompanied prospects to.
STATUS:      REQUIRED CHANGE — set `session.cookie_httponly=1` and `session.cookie_secure=1`
             on the demo host. Two lines of config, no code change, no deployment risk.
             **This is outside TASK 2's architecture scope and I raise it because §8.6 gives
             M6 the whole exposure surface, not because Architecture.md mentions it.**
```

```
ID:          M6-B02
TYPE:        OBSERVED
STATEMENT:   The already-built demo ALREADY terminates TLS outside Saudi Arabia. It is
             Cloudflare-proxied, so the certificate presented to the visitor is Cloudflare's
             and decryption happens at a Cloudflare PoP — for my request today, `CF-RAY:
             a2dedb2f5816a8aa-**ORD**`, i.e. **Chicago**. A Saudi visitor would hit a nearer
             PoP, but the mechanism is identical: **credentials POSTed to the demo login form
             are decrypted by Cloudflare before reaching the Dammam origin, in whatever
             country that PoP sits in.** This does not make the Vercel residency finding wrong;
             it makes it **consistent**, and it means a strict residency standard cannot be
             applied to the marketing site while the existing demo is exempted without a
             stated reason. Additionally, because Cloudflare proxies the origin, the origin's
             actual location is **not verifiable from outside** — which is the same gap
             M6-R02 records in RDY-0064's own closure note.
BASIS:       curl -sS -I https://demo.skyeagle.uk/ — 2026-08-20T05:04:15Z: `Server:
             cloudflare`, `cf-cache-status: DYNAMIC`, `CF-RAY: a2dedb2f5816a8aa-ORD`.
             Resolve-DnsName demo.skyeagle.uk → 172.67.155.245, 104.21.58.38 (Cloudflare
             anycast, not an origin address).
             https://vercel.com/docs/regions (accessed 2026-08-20) for the parallel Vercel
             behaviour.
FALSIFIER:   Turning Cloudflare's proxy off for `demo` (grey cloud), which would expose the
             origin IP directly and remove the intermediate termination — and would also
             remove the place where the §13.3 item 3 rate limiter has to live. That trade is
             the finding.
CONFIDENCE:  High.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Medium. The value of this finding is that it **prevents an inconsistent
             standard**. Residency is out of scope to re-decide (§8.6) and I do not re-decide
             it; I record that whatever standard the Owner sets must be applied to both hosts
             or the difference must be written down.
STATUS:      OPEN — OWNER DECISION (consistency of standard, not the residency decision)
```

---

## PART 5 — §13.6 CONFIRMED OR CHALLENGED

§14 requires §13.6's seven prohibited-page corrections to be confirmed or challenged **with
reasoning**. I checked all seven against the **canonical §32 annex** at
`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:12022-12071` (all 30 rows plus the "Pages
that must not exist" list at :12066-12069) and against GTM §14 at
`docs/Product-Positioning-and-GTM-Locked-Strategy.md:597-646`.

**Lane note, as instructed:** this is primarily M5's lane. I give the **technical / architectural**
reading — what the ruling means for how the site is built and what would enforce it — and leave the
claim ruling itself to M5.

| # | §13.6 row | Ruling as written | **My result** | Reasoning |
|---|---|---|---|---|
| 1 | `billing/` page | Must not exist · §32 items 4, 12 | **CONFIRMED — outcome right, citation weak** | §32 item 4 covers *GL, accounting, ERP, AP, procurement, POs, HR, payroll, rostering, asset management* — a patient-billing page is not obviously in it. Item 12 (anything Saudi: ZATCA, Fatoora, Saudi VAT) is the load-bearing one and it holds. **The precise governing text the document does not cite** is GTM **§14.4**: "US revenue cycle claims (CLM-0014…0018) — real and deep, but **US-only formats and irrelevant to this ICP; keep off the Saudi site entirely**" (`Product-Positioning…md:643`). That is stronger and more exact than item 4. Also relevant: §32 item 13 (FHIR Claim/ClaimResponse/EOB) and §40 row 7's permanent discipline. **Architectural consequence:** the ban applies to the route AND to any nav entry or sitemap row; a `sitemap.xml` generated from the filesystem enforces it automatically, which is a point in favour of file-based routing. |
| 2 | `insurance/` page | Must not exist · §32 item 12 | **CONFIRMED, exactly as cited** | Item 12 verbatim names NPHIES, CCHI, and is flagged "**the highest-drift-risk item (R-02)**". The canonical "Pages that must not exist" list independently names "NPHIES/ZATCA/Saudi-readiness". Two independent bases. **Architectural consequence:** this is the row most likely to be reintroduced by an SEO instinct ("Saudi clinics search for insurance"), so it needs the CI banned-term scan (M6-V10(3)), not just a note. |
| 3 | `orders-results/` | **Sub-section only** · *no citation given* | **CHALLENGED** | M0 flagged the missing citation; I checked and **no §32 row or locked decision supports "sub-section only."** §32 item 3 prohibits "LIS" — which is a *term*, not a page — and GTM §14.2 **MC-21** expressly permits the claim: *"Lab and procedure ordering with a test compendium and a results review and sign-off queue,"* with the leading qualification *"Transmission and result receipt require a lab interface to be established. Never 'LIS'."* So the capability is publishable. **The "sub-section only" ruling is an editorial judgement dressed as a §32 prohibition**, and it sits in a table whose other six rows are constraint-derived — which is precisely how an editorial preference acquires the authority of a locked rule. **My technical objection is stronger than the classification one:** §32 requires the mandatory qualification to travel *in the same visual unit* as the claim. MC-21's qualification is long and consequential. **A sub-section has less room to carry it prominently than a full page does**, so demoting this to a sub-section may make compliance *harder*, not easier. **Ruling is M5's**; my recommendation is that the row be restated as a marketing preference with no §32 backing, or dropped. |
| 4 | `Testimonial` component | Must not exist · §32 item 25 | **CONFIRMED, exactly as cited** | Item 25 verbatim: "Manufactured trust: testimonials · clinic or hospital logos · customer counts · ROI statistics · uptime or performance figures · implementation-time claims · certification badges · 'trusted by' strips … **No such section may exist**." The canonical list also names "a 'customers' page with no customers." **Architectural consequence, and it is the useful one:** deleting the component is necessary but not sufficient — a `FeatureCard` or `Hero` can carry a testimonial as content. The enforcement is the CI banned-term scan plus a review gate, not the component list. |
| 5 | `ComparisonTable` (named competitors) | Deferred · COMP-001 | **CONFIRMED** | COMP-001 verified at `Marketing-MVP…md:782`: "compare only against paper and self-installed OpenEMR … Named-competitor content withheld until 9 dossiers complete **and** a customer exists … **DEFERRED**." §32 item 26 independently prohibits competitive frequency figures. **One technical caution:** the permitted comparison against *self-installed OpenEMR* must not slide into implying our software is different from it — §32 item 15 prohibits "any proprietary or differentiated feature; 'our technology'", basis "**Zero fork divergence**." The permitted comparison is about the *service*, not the *software*, and the component's props should make that structurally hard to get wrong. |
| 6 | Pricing page: model only, no figures | PRC-003 BLOCKED | **CONFIRMED** | PRC-003 BLOCKED verified at `Product-Positioning…md:682, 693`. §32's three absolute holds include "No price figure." **Architectural consequence:** a `content/{en,ar}/pricing.mdx` is one `git commit` away from carrying a number. The CI scan should include a numeric-currency pattern (`SAR`, `ر.س`, `$`, digits adjacent to a currency word) on `pricing` content in **both** languages — the Arabic form is the one an English-only regex misses. |
| 7 | "Solo practice" segment page — Drop | *"Not the ICP"* · *no citation given* | **CHALLENGED — and the architecture creates a live gap** | The ICP basis is real (GTM ICP-001: 3–15 providers), so the *page* is correctly dropped. But **dropping it leaves a routing hole the architecture must fill.** §13.2's qualifying form, field 3, offers "Providers — **1–2** / 3–15 / 16+", and §13.2 states that a disqualifying answer "routes to an honest not-a-fit page, which is GTM §29's success metric, instrumented." **§13.5's route list contains no such page.** So the form has a documented disqualification branch and no destination. Separately, **GTM §29 makes self-disqualification a success metric** (CommitteeSystem.md:816), and M4's brief warns explicitly that a CRO instinct will try to fix the disqualification path and that **"it must not be fixed; it must be instrumented"** (CommitteeSystem.md:665). Deleting the segment page is right; **deleting the destination is not.** Recommend a `not-a-fit/` route in both locales. **M4's lane to design; M5's to rule on the copy.** |

**Summary: 5 confirmed · 2 challenged.** The two challenged are exactly the two M0 flagged as lacking
a cited governing ID — the classification test caught them correctly, and independent checking
against the canonical annex confirms no such ID exists for either.

---

## PART 6 — §13.8's FOUR OPEN QUESTIONS

```
ID:          M6-Q01   §13.8 Q1 — WHERE DOES LEAD-FORM DATA LAND?  (§14: "especially")
TYPE:        UNKNOWN
STATEMENT:   **LEFT OPEN — and it must be, because it is a legal determination.** What is now
             settled and no longer needs deliberation: (i) it cannot be a Vercel Function
             under a Kingdom-only posture, because Vercel has no Saudi region and terminates
             TLS in a compute region (M6-L10b, M6-V02); (ii) it cannot be an unreviewed
             third-party form service, because §13.3 item 11 already forbids that today;
             (iii) the organisation has a written safe default on the adjacent question —
             `Q45`, "Kingdom-only; no cross-border for anything derived from tenant data" —
             which is advisory here rather than binding (RDY-0092's ruling that the corpus is
             a future-phase roadmap), but which establishes the house position.
             **What would settle it:** a written ruling from the Owner, on legal advice,
             answering *"may identifiable lead data — a prospect's name, clinic, work email
             and phone — be processed outside the Kingdom, and if so under which PDPL Art. 29
             basis?"* That is one question to counsel, not a research programme.
BASIS:       M6-V03 in full, including all primary citations.
FALSIFIER:   The ruling being made.
CONFIDENCE:  High that it is open. **I decline to state a confidence on the legal answer.**
AUTHORITY:   OPEN — Owner / legal.
IMPACT IF WRONG: Severe — the only item in this audit with a legal edge and a positioning
             consequence at the same time.
STATUS:      OPEN — OWNER DECISION.  Ranked options: Part 7, O-2.
```

```
ID:          M6-Q02   §13.8 Q2 — IS THE SITE BRANDED THIQA OR SKYEAGLE?
TYPE:        UNKNOWN
STATEMENT:   **LEFT OPEN — out of my lane in substance (marketing judgement, §8.6
             out-of-scope), and I do not answer it.** What I can contribute is the technical
             cost of deciding it late, which is the part an architecture audit owns: the
             decision propagates into the domain (`skyeagle.uk` is already the zone),
             `<title>`/OG metadata in **two** locales, the logo asset set, the token file's
             semantics, the GA4 property name, the Search Console property, and every
             `hreflang`/canonical URL. **Deciding it after content exists costs roughly a
             day of find-and-replace across two locales plus a re-review of every affected
             page under C-4; deciding it before costs nothing.** The architecture correctly
             does not assume a resolution (Architecture.md:196), and I confirm it does not
             quietly assume one anywhere else in §13.
FALSIFIER:   A place in Architecture.md §13 that assumes one brand. I checked the domain
             table, the repo tree and the component list; none does.
BASIS:       Architecture.md:196; CommitteeSystem.md:791, 1455 (§15.1 item 3, still open).
CONFIDENCE:  High on the cost characterisation; no opinion on the answer.
AUTHORITY:   OPEN — Owner.
IMPACT IF WRONG: Low technically; the cost is entirely a function of *when*.
STATUS:      OPEN — OWNER DECISION (recommend ruling it **before** the repo is created).
```

```
ID:          M6-Q03   §13.8 Q3 — ANALYTICS ON THE DEMO HOST?
TYPE:        OBSERVED
STATEMENT:   **ANSWERED AND CLOSED — no. Constraint-determined, not open.** Architecture.md:197
             frames this as an open question with a "default answer: no." The governing text
             has since hardened it into a mandatory gate: §13.3 item 9 reads "**no session
             recording and no clinical-UI analytics on the demo.** Only minimal operational
             and security events until privacy review approves more." A gate item is not a
             default. M0 flagged the discrepancy (pack item 7a); I confirm it independently
             and rule the question closed. No reopening request is needed — the architecture
             already complies (Architecture.md:51: "Marketing site only — never on the demo").
             **One extension required:** the gate governs the demo and is silent on the
             marketing site, where Vercel logs client IP and User Agent regardless of GA4
             (M6-V08). The gate should be extended to define "minimal operational and security
             events" on the marketing side as well.
BASIS:       CommitteeSystem.md:1176-1177; Architecture.md:51, 197;
             https://vercel.com/docs/runtime-logs (accessed 2026-08-20).
FALSIFIER:   An Owner instruction relaxing §13.3 item 9, in writing.
CONFIDENCE:  High.
AUTHORITY:   POLICY (§13.3 item 9) — Owner may change it, in writing.
IMPACT IF WRONG: High if reversed silently — telemetry on a clinical UI is a named gate
             failure and a privacy question even with synthetic data.
STATUS:      PASS (closed) · REQUIRED CHANGE to Architecture.md:197's stale framing.
```

```
ID:          M6-Q04   §13.8 Q4 — WHO OPERATES THE MARKETING SITE?
TYPE:        INFERRED
STATEMENT:   **PARTLY ANSWERED.** The *principle* is closed and constraint-determined: C-4
             makes claim review mandatory before any artefact reaches a page, and RDY-0003 is
             CLOSED with a **named** reviewer and a written six-gate procedure. The *mechanism*
             is entirely open, and today the honest answer to "what enforces it" is
             **nothing** — because the repository does not exist. Four free settings close it:
             protected `main`; CODEOWNERS on `content/**` and `**/*.mdx` requiring the named
             reviewer; required status checks running the §32 banned-term scan, the
             `MC-`/`CLM-` trace scan, the locale-parity check and the token check; and Vercel's
             production deploy sourced only from `main`. **The scans already exist as written
             regexes** in the RDY-0056/0057 closure records — turning them into a CI job is
             transcription, not invention. See M6-V10.
BASIS:       Architecture.md:198; `Marketing-MVP…md:977` (RDY-0003 CLOSED, named reviewer,
             `EV-003` §3), :1060, :1061 (the existing scan regexes);
             CommitteeSystem.md:153-169 (C-4), :384-395 (§6.4's own "a prompt instruction is
             not a control").
FALSIFIER:   The four settings existing once the repo is created.
CONFIDENCE:  High.
AUTHORITY:   LOCKED for the principle · POLICY/open for the mechanism.
IMPACT IF WRONG: Severe — this is the mechanism that makes the whole claim register real at
             publication time rather than at review time.
STATUS:      REQUIRED CHANGE (mechanism) · PASS (principle)
```

---

## PART 7 — RANKED OPTIONS FOR THE GENUINELY-OPEN ITEMS ONLY

Per §12, **≥3 ranked options, cheapest first**, each with cost, time, dependency, what it would
break, and a confidence — **but only for items classified genuinely open.** Producing options for an
evidence-determined item is a FAILED deliverable, so I classified first.

**Items I did NOT generate options for, and why:**

| Item | Class | Determining basis |
|---|---|---|
| §13.7 route-vs-iframe | **EVIDENCE-DETERMINED** | `X-Frame-Options: DENY` + `frame-ancestors 'none'`, observed 2026-08-20 (M6-R03) |
| Analytics on the demo | **CONSTRAINT-DETERMINED** | §13.3 item 9, mandatory gate (M6-Q03) |
| Claim review before merge (principle) | **CONSTRAINT-DETERMINED** | C-4 §3.4 + RDY-0003 CLOSED (M6-Q04) |
| The six §13.6 rows I confirmed | **CONSTRAINT-DETERMINED** | §32 items 4/12/25/26, COMP-001, PRC-003, GTM §14.4 (Part 5) |
| Render mode (static vs SSR) | **EVIDENCE-DETERMINED** | No locked-IA route requires request-time data; MDX-in-Git has no changing source (M6-V01) |
| Hosting residency (Dammam) | **CLOSED, out of scope** | RDY-0064; §8.6 |
| Preview protection method | **EVIDENCE-DETERMINED** | Standard Protection + Vercel Authentication is free on all plans and addresses the risk; the $150 add-on does not (M6-V05) |

---

```
ID:          M6-O1   GENUINELY OPEN: which host serves www.skyeagle.uk
TYPE:        PROPOSED
STATEMENT:   Three ranked options, cheapest first. The cheapest is given a genuine argument,
             not a strawman (§8.6).
BASIS:       M6-L10a, M6-L10c, M6-V01, M6-V06, M6-V09; DNS and header observations 2026-08-20.
FALSIFIER:   A written requirement that forces server-side rendering, which would eliminate
             options 1 and 2 equally and make this a different question.
CONFIDENCE:  High that all three are viable; **Medium** on the ranking, which depends on facts
             only the Owner holds (who operates this, and what they already know).
AUTHORITY:   HYPOTHESIS
IMPACT IF WRONG: Medium — all three deliver the same pages; they differ in vendor count,
             operational surface and exit cost.
STATUS:      OPEN — OWNER DECISION
```

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1 — cheapest** | **Static export served by Cloudflare** (Pages / Workers Static Assets), single-vendor estate | **$0** on the plan already in use; ~$20-25/mo only if Cloudflare Pro is wanted for the §13.3 item 3 rate-limiter rule count | ~1 day of setup | Cloudflare account (**exists** — authoritative NS for the zone, verified 2026-08-20) | Nothing yet built. Loses Vercel's preview-comment UX and its per-branch preview polish, which are real conveniences | Medium-High |
| **2** | **Static export on Vercel** — `output: 'export'`, no functions, no middleware, Standard Protection on, `www` DNS-only in Cloudflare | **$0** on Hobby / **$20/mo** Pro (Pro needed to protect the production domain and for >1 region, neither of which a static site uses — so Hobby is genuinely arguable here) | ~1 day | Vercel account (**does not exist yet**); the `vercel.json` headers/redirects work | Adds a second edge vendor, a second log store and a second place to configure rate limiting (§13.3 item 3) | High |
| **3 — most expensive** | **Next.js server build on Vercel** with middleware and functions, as the document's phrasing implies | $20/mo Pro minimum, plus function/observability usage; **and a materially larger operational and residency surface** | ~1 day to build, ongoing to operate | Vercel; and a PDPL ruling before any function touches lead data | Breaks the D-2 exit story (M6-V09c); imports the middleware-in-all-regions problem (M6-V02); makes the exit a rewrite rather than a folder copy | High that it is the worst fit here |

**The genuine argument for option 1, stated properly.** It is not merely cheapest. It collapses the
estate to **one** DNS authority, one edge, one WAF, one rate-limiter, one log store, one account to
secure and one bill — in an organisation whose own register says the binding shortfall on the
website gate is **G3 operational readiness, not proof**, and whose §13.3 item 3 already requires a
Cloudflare rule to be written regardless. It also removes the two-independent-paths-to-publication
problem at M6-V04(b)'s (b). **The honest argument against it:** Vercel's Next.js developer
experience is better, its preview workflow is better, and if whoever builds this already knows Vercel
and does not know Cloudflare Pages, option 2 will genuinely ship faster and more correctly — and a
site that ships is worth more than a marginally tidier estate. That is a real trade and the Owner
holds the facts that decide it.

---

```
ID:          M6-O2   GENUINELY OPEN: where lead-form data lands  (§13.8 Q1 / §13.3 item 11)
TYPE:        PROPOSED
STATEMENT:   Four ranked options, cheapest first. **Option 1 is not a technical option and is
             deliberately ranked first**, because the cheapest correct action here is to ask
             one question rather than build anything.
BASIS:       M6-V03; §13.3 item 11 (CommitteeSystem.md:1180-1183); `Q45`
             (`20-unresolved-external-inputs.md:16-27`); https://vercel.com/docs/regions
             (accessed 2026-08-20).
FALSIFIER:   A legal ruling, which converts this from open to determined.
CONFIDENCE:  High on the option set being complete; **no confidence offered on which is
             lawful** — that is the ruling's job, not mine.
AUTHORITY:   OPEN — Owner / legal. §13.3 item 11 already closes option 4 today.
IMPACT IF WRONG: Severe.
STATUS:      OPEN — OWNER DECISION
```

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1 — cheapest** | **No form at MVP.** The single primary CTA is already "Book a walkthrough" (WEB-001, LOCKED). Publish a `mailto:` and a phone number; capture leads in the mailbox the business already runs | **$0** | **0 days** | A monitored business mailbox | Loses the §13.2 qualifying form — which is a **real** loss, because that form *is* RDY-0065's qualification checklist, a P0 gating G3 and G6. **So this option defers a P0, and that must be stated, not hidden** | High that it works; Medium that it is acceptable |
| **2** | **Form POSTs to an endpoint on the Dammam instance** (the demo host or a small sibling service) | ~$0 incremental; a few hours of work | 1–2 days | RDY-0064's instance; Cloudflare rate-limiting in front (needed anyway for §13.3 item 3) | Puts a public write endpoint on the same host as the demo — needs its own rate limit, spam control and input validation. **Also: the browser's TLS still terminates at Cloudflare (M6-B02), so this is Kingdom-resident *at rest*, not end-to-end** — that nuance must be stated in any residency position | Medium-High |
| **3** | **Reviewed third-party form/CRM with a documented Kingdom or acceptable-transfer posture, plus a DPA** | vendor fee + legal review time | 1–2 weeks, mostly waiting on review | Legal review; a vendor that can evidence its posture | Adds a second data controller relationship. **§13.3 item 11 permits this only if reviewed** — "do not send identifiable lead data to an **unreviewed** global form or analytics service." Unreviewed, it is closed today | Medium |
| **4 — closed today** | **Vercel Function or unreviewed global form service** | $0 | hours | none | **Closed by §13.3 item 11 (unreviewed service) and by M6-L10b (no Saudi region, TLS terminated abroad).** Listed for completeness and because it is the default anyone would reach for | High that it is closed absent a ruling |

---

```
ID:          M6-O3   GENUINELY OPEN: framework, if the ranking at Architecture.md:54 is withdrawn
TYPE:        PROPOSED
STATEMENT:   Three options. This item is open only because M6-L01 shows the two stated reasons
             for Next.js are false; withdrawing a false justification reopens the choice, it
             does not decide it against the incumbent.
BASIS:       M6-L01, M6-V07; https://nextjs.org/docs/app/guides/internationalization and
             https://docs.astro.build/en/guides/internationalization/ (both accessed
             2026-08-20).
FALSIFIER:   One locked-IA route that cannot be built at build time.
CONFIDENCE:  High that both 1 and 2 deliver the site; **Low** on which is better, because the
             deciding fact — what the builder already knows — is not in this repository.
AUTHORITY:   HYPOTHESIS
IMPACT IF WRONG: Low. Both work. The finding that matters is the false justification, not the
             framework.
STATUS:      OPEN — OWNER DECISION (and legitimately decidable on team skill alone)
```

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1 — cheapest** | **Astro, static** — built-in i18n config (`locales`, `defaultLocale`, `routing`, `fallback`), zero JS by default, React islands available if needed | $0 | Comparable to option 2 | Team willing to learn Astro | Nothing built. Loses Next.js ecosystem familiarity; if the site ever genuinely needs SSR, the migration is real | Medium |
| **2** | **Next.js with `output: 'export'`** — `app/[lang]/` + `generateStaticParams`, no middleware | $0 | Comparable | Team knows React/Next | Nothing. Requires discipline to *stay* static — the framework makes adding a Server Action trivially easy, and that one line silently reopens the whole residency analysis | High |
| **3 — most expensive** | **WordPress or a CMS-first stack** | hosting + maintenance + patching | longer | a maintained host | **Breaks M6-L08/L09**: a CMS lets a non-technical editor publish a claim without the EV-003 gate. Also imports a patching surface into an org whose G3 readiness is already the binding shortfall. The document rejects it (Architecture.md:57-58) and I agree — for a better reason than the one given | High that it is wrong here |

---

## PART 8 — CLOSING RECORD

### 8.1 At least one genuine weakness, named (§8.6 acceptance)

More than one was found; these are the three I would defend hardest, and none of them is "Next.js is
a bad framework":

1. **The load-bearing justification for the stack choice is factually false.** Architecture.md:40
   says locale-aware routing is a Next.js feature this project needs. The Next.js App Router has no
   built-in i18n routing; Astro — ranked second — does. The document's own growth argument
   (Architecture.md:56-58) is refuted by its own architecture: the demo launcher is a link, lead
   capture cannot live on Vercel under a residency posture, and the customer area is a separate
   environment by §13.1. **(M6-L01, M6-V07)**

2. **The Owner's second question — can it be operated correctly on Vercel — has an answer the
   document does not contain, and it is "yes for pages, no for the lead form."** Vercel has no Saudi
   region; TLS is terminated in a compute region; the nearest is Dubai and the default is Washington
   D.C. The lead form is the one part of this site that carries personal data, and it is the one part
   that cannot be given a Kingdom posture on this platform. **(M6-L10b, M6-V02, M6-V03)**

3. **The hosting decision had a candidate set of one, and the option not considered is the vendor
   already in the diagram.** Cloudflare holds authoritative DNS for the whole zone, already fronts the
   demo, and is where the one missing §13.3 edge control must be configured regardless. Choosing
   Vercel buys a second edge vendor, a second log store and a second place to write a rate-limiting
   rule, for an organisation whose own register says the binding gate shortfall is **operational
   readiness**. **(M6-L10c)**

And one finding outside the architecture that I raise because §8.6 gives M6 the whole exposure
surface: **the demo's PHP session cookie is served without `HttpOnly` or `Secure`** (M6-B01).

### 8.2 Acceptance self-check against §8.6 and §14

| Criterion | Result |
|---|---|
| Every §13.2 layer carries an explicit verdict | **YES** — 13 layers + 3 structural rules = 16 verdicts (Hosting split into L10a/L10b/L10c) |
| Every layer carries a change-my-mind condition | **YES** — 16 of 16 |
| At least one genuine weakness named | **YES** — §8.1, three plus one |
| Every §13.3 gate item carries a result | **YES** — 11 of 11 (2 PASS · 1 PASS-with-open-row · 3 FAIL · 5 OPEN) |
| Built-vs-proposed distinguished | **YES** — Part 4, with the demo half re-verified live today and the marketing half shown to be entirely NXDOMAIN |
| §13.8's four questions addressed, PDPL especially | **YES** — Part 6; Q1 given the deepest treatment and left open with what would settle it |
| §13.6 confirmed or challenged with reasoning | **YES** — Part 5, all 7: 5 confirmed, 2 challenged |
| Recommendations checked against what is already built | **YES** — Part 4; **no rebuild of any working component is recommended** |
| "Synthetic data is not a security approval" honoured | **YES** — stated in Part 3 and applied in M6-B01 |
| Cheapest option given a genuine argument | **YES** — Part 7 O-1 option 1 and O-2 option 1, each with its honest counter-argument |
| Options generated only for genuinely-open items | **YES** — 3 option sets; the 7 items excluded are tabulated with their determining basis at the head of Part 7 |
| Out of scope respected | **YES** — hosting residency not re-decided; marketing judgement deferred to M4/M5/M7 where it arose (Part 5 rows 3 and 7, M6-L07, M6-Q02) |
| Write isolation (R9) | **YES** — one file written, this one |
| **No git command run** | **YES** — zero git invocations in this dispatch |
| The Standing Rule (§1) | No conflict arose between any recommendation here and `docs/HISModulesUsers.md`; this audit makes no product-capability claim |

### 8.3 Finding count and §7.1 field compliance (self-count, as required)

| Group | IDs | Count |
|---|---|---|
| Part 0 — briefing-pack challenges | M6-R01…R03 | 3 |
| Part 1 — layer and rule verdicts | M6-L01…L13 (with L10a/b/c) + M6-S01…S03 | 16 |
| Part 2 — Vercel operations | M6-V01…V10 | 10 |
| Part 4 — already-built security findings | M6-B01, M6-B02 | 2 |
| Part 6 — §13.8 questions | M6-Q01…Q04 | 4 |
| Part 7 — option sets | M6-O1…O3 | 3 |
| **Total findings in §7.1 schema blocks** | | **38** |

**Field compliance:** **38 of 38** carry all nine §7.1 fields — ID · TYPE · STATEMENT · BASIS ·
FALSIFIER · CONFIDENCE · AUTHORITY · IMPACT IF WRONG · STATUS. **Findings missing one or more
fields: 0.**

**One deliberate deviation, declared rather than hidden:** the 16 Part-1 blocks carry a **tenth**
field, `CHANGE MY MIND`, which §8.6 requires of M6 and which §7.1 does not define. It is additive;
no required field is displaced. The Part-3 gate table and the Part-5 §13.6 table are **result
tables**, not findings — each row carries a result and its basis, and each is backed by a
full-schema finding elsewhere in the document. I record that structure explicitly so the count above
can be checked rather than taken on trust.

**BASIS discipline:** every BASIS field is a `file:line`, a re-runnable command with its run date,
or a URL with the date accessed. **No finding rests on recollection.** Every Vercel and Next.js
capability claim was fetched from primary documentation on **2026-08-20**, per the brief's specific
warning that this is where a confident-sounding but outdated memory does real damage.

### 8.4 D3b post-flight record (§5.1)

**Model ID declared in this agent's own runtime context: `claude-opus-5`** (model name: Opus 5).

Per §5.1 this is a **runtime self-declaration, not an external observation**, exactly as M0 recorded
at pack §4 — the dispatch surface does not externally report the model a subagent ran on. It should
be recorded as `SELF-REPORTED (runtime-declared)` and **not represented as floor-verified** without
the written Owner exception M0 identified as necessary.

**Assigned model:** Opus 5 (Tier A) · **Observed (self-reported):** `claude-opus-5`, Tier A ·
**Floor:** M5 reviews this output at Tier A; no generator in this dispatch ran above the reviewer.
**No fallback occurred. Nothing in this dispatch failed, timed out or was retried.**

---

**END OF M6 AUDIT — `COM-20260820-001`, TASK 2, committee C-2.**
**Status: complete. Submitted for M4's adversarial attack (§2, §14) and M5's claim review (C-4).**
