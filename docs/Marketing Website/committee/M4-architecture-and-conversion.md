# M4 — WEBSITE ARCHITECTURE & CONVERSION

**Dispatch:** `COM-20260820-001` · TASK 2 · Committee **C-2** · Round **R2 (silent, independent)**
**Agent:** M4 · Website Architecture & Conversion — *generator in C-2, answering one question only*
**Question answered (§14 sequence, §3.2 Generators row):** **Does this stack serve the locked IA and the funnel?**
**Audited artefact:** `docs/Marketing Website/06-technical/Architecture.md` §13
**Frozen SHA-256, re-verified by this agent:** `938ff387cfaa239c29c89f41b8d11ef22e6ceac24b9569d362cc0dc18c8c0c1a`
(`sha256sum "docs/Marketing Website/06-technical/Architecture.md"`, run 2026-08-20 — matches the pinned value)
**Date:** 2026-08-20

**What this file is not.** It is not a technical audit (M6's lane), not a claim review (M5's veto), not
an attack on M6's verdicts (a separate later dispatch — M6's output has not been read and no position
of M6's is anticipated, agreed with, or disagreed with anywhere below). It contains no re-architecture
of the locked IA (`WEB-002` is `LOCKED FOR MVP`) and **proposes no reopening of any locked decision** —
M4 holds no reopening licence (§8.4). Two items below would require a change to a locked decision;
both are named and stopped at, not carried further.

**Status stamp:** this file feeds an Owner decision, so C-4 applies. It is **not yet claim-reviewed**;
M5's pass is required before any sentence here reaches a page.

---

## 1 · THE ANSWER, IN ONE PARAGRAPH

**The stack serves the locked information architecture well and the funnel not at all — and the two
findings are separable, which is the useful part.** `app/[locale]/…` with MDX-in-Git and static
generation is a good structural fit for a 25-node bilingual tree of mostly-static pages whose hardest
requirement is not rendering but *mechanically provable parity*, and MDX-in-Git is better at that than
any CMS would be. That verdict holds **conditionally**, on two design instructions the document does
not contain (forbid locale fallback; hold claims as data). The funnel is a different result:
Architecture.md contains **no representation of the funnel whatsoever**. The word *"walkthrough"* — the
locked primary conversion event of `WEB-001` — appears **zero times** in the file. There is no form,
no submit target, no data sink, no post-submit routing, no `not-a-fit` page, and no control that keeps
one page to one primary CTA. The funnel is not designed badly here; it is **absent**. And §13.5's
proposed control for §32 drift, `QualifiedClaim`, is a convenience, not an enforcement mechanism, and
it is pointed at the wrong component.

**Layer-level summary of the one question asked of M4** (M6 owns approve/deny/change per layer; these
are IA-and-funnel fitness readings only, offered as input to M6's and M0's work, not as verdicts):

| §13 element | Serves the locked IA? | Serves the funnel? | Finding |
|---|---|---|---|
| Framework (Next.js) | Yes | Capable, unused | M4-01 |
| Repository route list (§13.5) | **No — 8 locked nodes omitted** | **No — conversion route absent** | M4-02, M4-04, M4-05 |
| Bilingual routing (§13.4) | **No — a second, shorter, contradictory list** | n/a | M4-06 |
| Content model (MDX/JSON in Git) | **Yes, and it is the best part of the stack** | n/a | M4-18 |
| Component list (§13.5) | Partly | **No** — no form, no CTA control | M4-07…M4-11 |
| Prohibited-page corrections (§13.6) | Yes — clean, with two caveats | n/a | M4-03, M4-21, M4-22 |
| `/demo` launcher (§13.7) | n/a | **Right pattern, incomplete funnel** | M4-15…M4-17 |
| Hosting (Vercel) + GA4 | Neutral | **Actively harmful to one locked metric** | M4-13, M4-14 |

---

## 2 · THE §13.5-VS-LOCKED-IA PAGE DIFF

**Method.** The locked IA is `docs/Product-Positioning-and-GTM-Locked-Strategy.md:773-797` (§17.2,
WEB-002, `LOCKED FOR MVP`), read as a node tree: 14 top-level nodes and 11 children = **25 nodes**.
§13.5's route list is `docs/Marketing Website/06-technical/Architecture.md:111-128` = **17 routes**
under `app/[locale]/`. Every node was matched by name, then by claim trace where the name differed.

### 2.1 Present in §13.5, absent from the locked IA

**Zero.** No route in §13.5 introduces a page the locked IA does not have. **§13.5 smuggles nothing
back in under a different name** — checked line by line against §17.2's *"pages that must not exist"*
list (`Marketing-MVP-and-Launch-Readiness-Requirements.md:12067-12070`, §32's copy of the same list)
and against §13.6's seven rulings. There is no `billing/`, `insurance/`, `compliance/`,
`certifications/`, `customers/`, `testimonials/`, `analytics/`, `mobile/`, `nphies/`, `zatca/`,
`saudi-readiness/`, `inpatient/`, `dental/`, `lis/`, `ris/`, `pacs/` or `erp/` route, and no
`Testimonial`, `ComparisonTable`, `LogoStrip` or `TrustBadge` component. This is a genuine PASS and it
should be recorded as one.

**Three position/name mismatches, not new pages:**

| §13.5 route | Locked IA node | Mismatch |
|---|---|---|
| `ophthalmology/` (top level) | *Who it's for → Ophthalmology & eye clinics* ★ lead segment | Hoisted out of its parent. URL-structure question — M4-20 |
| `pricing/` | *How pricing works* ★ flagship | Slug promises a figure the page is forbidden to carry (PRC-003 BLOCKED) — M4-20 |
| `whats-included/` | *What's included — **and what isn't*** | The slug drops the half that does the disqualifying work — M4-20 |

### 2.2 Present in the locked IA, omitted by §13.5 — **8 nodes**

| # | Locked node | Claim traces it carries | Why the omission matters |
|---|---|---|---|
| 1 | **Product** (hub) | — | The four Product children are hoisted to top level; three survive, one does not (row 2). Low stakes on its own; it is how row 2 went missing |
| 2 | **Optional, switched off by default** | CLM-0028, CLM-0013, CLM-0032 → MC-19, MC-20, MC-25 (`GTM:626, 627, 632`) | **The most consequential omission.** Three claims whose mandatory qualification *is* "included but switched off by default". With no page, those qualifications have no home at product level. `whats-included/` overlaps (the Disabled register) but is not the same node — §17.2 lists both, and the registers are figures, not the "one honest page" §17.2 calls for |
| 3 | **Single clinic** (segment) | — | ICP segment page. See M4-22 — must not be confused with the "solo practice" page §13.6 drops |
| 4 | **Small medical centre / polyclinic** (segment) | — | ICP segment page |
| 5 | **Two or three locations** (segment) | CLM-0029 → MC-15, qualification *"A separate database per site, provisioned manually. **Not multi-tenant SaaS**"* (`GTM:618`) | Omitting the page does not remove the claim — it removes the surface its §32-item-8 qualification was supposed to sit on |
| 6 | **Demo → Watch: audit integrity verification (recorded)** | The single strongest proof asset owned (`GTM:807`) | §13.7 gestures at it in prose ("Watch the audit-integrity run") but gives it no route |
| 7 | **Demo → Watch: what each role sees (recorded)** | SS-03 / SS-04 | Same |
| 8 | **Demo → Book a walkthrough** | **WEB-001's locked primary conversion event** | See M4-04. This is the funnel's only conversion point and it has no route |

### 2.3 Required by a ruled decision, absent from **both** lists — 2 routes

| Route | Required by | Consequence of absence |
|---|---|---|
| the **qualifying form** surface (6 fields) | `STEP0-001` Reading B, D-a/D-b (`CommitteeSystem.md:1107-1126`) | The mechanism the whole access ruling rests on has no page |
| **`not-a-fit`** | `CommitteeSystem.md:1138` — *"routes to an honest not-a-fit page, which is GTM §29's success metric, instrumented"* | The only high-quality self-disqualification instrument has no landing surface — M4-14 |

**These two are IA *extension*, not IA *change*** — they are destinations of routing rules the
committee has already ruled, for a mechanism that did not exist when WEB-002 was locked. That is
exactly what §8.4 puts in M4's lane. Both should be `noindex` and neither should appear in navigation.

---

## 3 · FINDINGS (§7.1 schema — all nine fields, every finding)

### M4-01 — headline verdict

```
ID:          M4-01
TYPE:        INFERRED
STATEMENT:   The advised stack serves the locked IA (conditionally, on M4-07 and M4-18) and does
             not serve the funnel at all: Architecture.md §13 contains no form, no submit target,
             no data sink, no post-submit routing, no conversion route, and no CTA-singleton
             control. The framework is capable of all of it; the document designs none of it.
BASIS:       grep -ni "walkthrough" "docs/Marketing Website/06-technical/Architecture.md" -> 0 hits.
             grep -ni "form" (same file) -> 4 hits, all non-design: :33 "platform", :154 "claim
             formats", :163 "positive form of the same rule", :195 the residency question itself.
             grep -niE "api/|route handler|server action|middleware|not-a-fit|thank" -> 0 design hits.
             Component list Architecture.md:130-136 contains no form component.
FALSIFIER:   A form component, a submit target, an `app/api/` or server-action path, or the string
             "Book a walkthrough" appearing anywhere in Architecture.md at the pinned SHA-256.
CONFIDENCE:  High — this is a grep result on a frozen file, not a judgement.
AUTHORITY:   LOCKED (WEB-001, GTM:764-768: "Book a walkthrough. One primary CTA everywhere")
IMPACT IF WRONG: If the funnel is in fact designed elsewhere, this report over-states a gap and
             wastes an Owner decision. Checked: it is not in Challenges-and-Demo.md, whose §7 lists
             "Information architecture and page inventory" as still to be produced (:344-350).
STATUS:      REQUIRED CHANGE
```

### M4-02 — §13.5 omits eight locked-IA nodes

```
ID:          M4-02
TYPE:        OBSERVED
STATEMENT:   §13.5's 17-route list omits 8 of the locked IA's 25 nodes, including one whole content
             surface ("Optional, switched off by default") that carries the mandatory "switched off
             by default" qualification for MC-19, MC-20 and MC-25, and one segment page ("Two or
             three locations") that carries MC-15's "not multi-tenant SaaS" qualification (§32 item 8).
BASIS:       Locked IA: Product-Positioning-and-GTM-Locked-Strategy.md:773-797. §13.5 list:
             Architecture.md:111-128. Claim traces: GTM:618 (MC-15), :625-627, :630 (MC-19/20/25).
             Full node-by-node diff at §2.2 of this file.
FALSIFIER:   A reading of §13.5 as an illustrative fragment rather than a route inventory. Rejected:
             §13.6's header (Architecture.md:148) calls the list authoritative enough to correct
             ("Its example sitemap and component list are not [sound] ... Corrected here so they are
             not built by accident"), i.e. the document treats the list as buildable.
CONFIDENCE:  High — both lists are enumerable and were enumerated.
AUTHORITY:   LOCKED (WEB-002, LOCKED FOR MVP)
IMPACT IF WRONG: If §13.5 is only illustrative, the correction is cheap (add the 8 nodes). If it is
             treated as the build spec as written, three mandatory qualifications lose their page
             and the ICP segment router loses three of its four destinations.
STATUS:      REQUIRED CHANGE
```

### M4-03 — no prohibited page is present, and none is designed here

```
ID:          M4-03
TYPE:        OBSERVED
STATEMENT:   §13.5's route list and component list contain zero prohibited pages and zero prohibited
             components, checked against §17.2's "pages that must not exist" list and §13.6's seven
             rulings. M4 confirms it has designed none: nothing in this file proposes a billing,
             insurance, compliance, Saudi-readiness, analytics/BI, mobile, inpatient, dental,
             LIS/RIS/PACS, ERP, certification, customer-logo or "customers" page, or a Testimonial
             or named-competitor ComparisonTable component.
BASIS:       Architecture.md:110-140 (route + component lists); Architecture.md:154-161 (§13.6);
             Marketing-MVP-and-Launch-Readiness-Requirements.md:12067-12070 (§32's copy of the list);
             GTM:800.
FALSIFIER:   Any route or component in §13.5, or any route proposed in this file, matching a
             prohibited category under a synonym. Two candidates were examined and cleared:
             `security-audit/` is the locked IA's own flagship name, not an unqualified "secure"
             claim (§32 item 27 governs copy, not a slug — M5's call, not M4's); `resources/` is an
             open container whose four locked children are buyer's checklist, FAQ, milestone feed
             and brochure PDF (GTM:795) — see the residual risk below.
CONFIDENCE:  High for the enumerated lists; Medium for `resources/`, which is a container with
             unspecified contents and is therefore where a "certifications" or "compliance" page
             would most plausibly grow later.
AUTHORITY:   LOCKED (§32 items 1-14, 25; GTM §17.2)
IMPACT IF WRONG: A prohibited page reaching build is the R-02 failure the whole control system
             exists to prevent — High likelihood / Severe impact (GTM:1174).
STATUS:      PASS — with one residual: `resources/` should be enumerated to its four locked children
             in the route list rather than left as an open container.
```

### M4-04 — the locked primary conversion event has no route

```
ID:          M4-04
TYPE:        OBSERVED
STATEMENT:   "Book a walkthrough" is WEB-001's locked primary conversion event and the fulfilment
             vehicle for STEP0-001's evaluation credential. It appears zero times in Architecture.md
             and has no route, no component and no submit target in §13.5.
BASIS:       grep -ni "walkthrough" "docs/Marketing Website/06-technical/Architecture.md" -> no output.
             WEB-001: GTM:766-768. STEP0-001 D-d: CommitteeSystem.md:1115.
             Challenges-and-Demo.md:46 — the credential is "issued as the fulfilment of a booked
             walkthrough".
FALSIFIER:   Finding the booking surface designed in any other frozen source for this dispatch.
CONFIDENCE:  High.
AUTHORITY:   LOCKED (WEB-001)
IMPACT IF WRONG: None if it is merely unwritten. Severe if it is built as designed: a site whose
             every primary CTA points at a route that does not exist, and an access ruling
             (STEP0-001) whose D-a and D-b conditions cannot be met because the form they govern has
             no page. D-a/D-b failing means Reading B is Reading A and four locked decisions reopen
             (CommitteeSystem.md:1107-1108).
STATUS:      REQUIRED CHANGE
```

### M4-05 — the disqualification landing route does not exist

```
ID:          M4-05
TYPE:        OBSERVED
STATEMENT:   The committee has already ruled that disqualifying answers to the qualifying form
             "route to an honest not-a-fit page". No such route exists in §13.4, §13.5, or anywhere
             in Architecture.md.
BASIS:       CommitteeSystem.md:1138. grep -niE "not-a-fit" Architecture.md -> 0 hits.
             Architecture.md:111-128 (route list).
FALSIFIER:   A `not-a-fit`, `not-for-you`, or equivalent route appearing in the frozen file.
CONFIDENCE:  High.
AUTHORITY:   LOCKED (GTM §29 row 2, GTM:1151 — self-disqualification is a success metric)
IMPACT IF WRONG: The one instrument that can measure the locked success metric by declaration
             rather than inference has nowhere to land — see M4-13 and M4-14.
STATUS:      REQUIRED CHANGE
```

### M4-06 — Architecture.md contains two contradictory route lists, and the shorter one is the bilingual one

```
ID:          M4-06
TYPE:        OBSERVED
STATEMENT:   §13.4's bilingual route diagram lists 9 named routes per locale; §13.5's repository
             structure lists 17. Seven English routes in §13.5 have no Arabic counterpart in §13.4:
             who-its-for, ophthalmology, reporting-export, implementation, resources, about, contact.
             On the face of the document that is a WEB-003 parity breach in the source of truth for
             bilingual routing.
BASIS:       Architecture.md:84-95 (§13.4, 9 routes) vs Architecture.md:111-128 (§13.5, 17 routes).
             WEB-003: GTM:828-832; CommitteeSystem.md:137.
FALSIFIER:   Reading §13.4 as an abbreviated illustration ("..." implied). It carries no ellipsis and
             no "for example"; the surrounding prose at :97-104 treats it as the routing spec and
             states the parity requirement in the very next paragraph.
CONFIDENCE:  High that the two lists differ; Medium that §13.4 was intended as exhaustive.
AUTHORITY:   LOCKED (WEB-003, LOCKED FOR MVP)
IMPACT IF WRONG: Low if it is a drafting artefact — but it is exactly the class of drift M4-18's
             mechanical parity gate exists to catch, and it has already occurred once, inside the
             architecture document itself, before a single page was built. That is the strongest
             available argument that parity must be enforced by a build gate rather than by care.
STATUS:      REQUIRED CHANGE
```

### M4-07 — `QualifiedClaim` is convenience, not enforcement

```
ID:          M4-07
TYPE:        INFERRED
STATEMENT:   §13.5's argument — that making the claim+qualification pairing a component "rather than
             an editorial habit is what stops it eroding over time" — does not hold as written. A
             React component makes the correct pattern convenient. It enforces nothing, because it is
             opt-in, because its qualification text is author-supplied, and because "same visual
             unit" is a rendering property the component type does not constrain.
BASIS:       Architecture.md:133 (the component), :142-144 (the argument). Content model is
             "MDX / JSON in the Git repository" (Architecture.md:47), i.e. arbitrary markdown and
             arbitrary JSX are both available to an author on every page.
             The requirement it is meant to satisfy: CommitteeSystem.md:624 and GTM:599 — "the
             qualification is not optional and must travel with it", same visual unit, never a
             footnote.
FALSIFIER:   Any mechanism in Architecture.md that makes a bypass fail a build or a merge. None is
             present: grep -niE "lint|CI|check|gate|CODEOWNERS|test" Architecture.md returns no
             build-control design.
CONFIDENCE:  High. Every bypass below is available with the stack exactly as specified.
AUTHORITY:   LOCKED (§32 adjacency rule) — the requirement. The enforcement *mechanism* is
             GENUINELY OPEN: no corpus item specifies one. Options at §4.1.
IMPACT IF WRONG: If the component is treated as the control, the project believes R-02 (High
             likelihood / Severe impact, GTM:1174) is mitigated when it is not. That is worse than
             no control, because it retires the vigilance that was doing the actual work.
STATUS:      REQUIRED CHANGE
```

**The bypasses, enumerated — every one of these is open with `QualifiedClaim` present and used:**

| # | Bypass | Why the component does not close it |
|---|---|---|
| 1 | Raw MDX prose — a `##` heading, a `<p>`, a list item | MDX renders arbitrary markdown. Nothing requires a claim to be expressed through a component |
| 2 | A different component — `FeatureCard`, `Hero`, `RoleCard`, `SegmentCard` | None has a qualification slot. See M4-08 |
| 3 | Author supplies the wrong qualification | The component takes strings. Nothing checks the string against GTM §14.2's mandatory text for that claim |
| 4 | Author supplies an empty, softened or truncated qualification | Same — `qualification=""` type-checks fine |
| 5 | Author supplies the right qualification, styled away | `text-xs opacity-40`, a `<details>` disclosure, a collapsed accordion, `sr-only`, or below-the-fold within the card. All are "in the same component" and none is the same *visual unit* |
| 6 | `ProductScreenshot` caption | Architecture.md:132 says it "carries its qualification inline". Nothing requires the caption to be non-empty, or the qualification in it to be the right one |
| 7 | Page metadata — `<title>`, meta description, Open Graph | These are claims that appear in SERP and in social previews *detached from the page entirely*. There is no visual unit to qualify within |
| 8 | Structured data / JSON-LD | Same, worse — see M4-09 |
| 9 | Image `alt` text | A claim in alt text is read aloud by a screen reader with no qualification anywhere near it |
| 10 | The Arabic file drifting from the English one | The `ar` MDX is a separate file. An author can use the component in `en` and prose in `ar` — and nothing notices (M4-18) |
| 11 | A future refactor | The component's internals can be changed by anyone with repo write. The pairing is a property of one file's JSX, not of the build |

**What would actually enforce it:** a control that *fails*, at build or at merge, when the pairing is
absent — not one that is available when the author remembers. Ranked options at §4.1.

### M4-08 — the "most important component" designation is on the wrong component

```
ID:          M4-08
TYPE:        INFERRED
STATEMENT:   The highest §32-drift-risk surfaces in §13.5's component list are `FeatureCard`, `Hero`
             and page metadata — not `QualifiedClaim`. `QualifiedClaim` is the component an author
             reaches for when they already know a qualification is required. `FeatureCard` and
             `Hero` are the components an author reaches for when writing a benefit, and neither has
             anywhere to put a qualification. A feature grid is a claim surface with structurally
             zero room for the thing §32 requires.
BASIS:       Architecture.md:130-136 lists Hero, FeatureCard, RoleCard, SegmentCard with no
             qualification affordance; :142 designates QualifiedClaim "the most important component
             on the site". Claims that will land in a feature grid and carry a mandatory
             qualification: MC-06 (Arabic 47.5%, GTM:609), MC-07 (55 reports, no BI layer, GTM:610),
             MC-16 (2FA voluntary, GTM:623), MC-19/20/25 (switched off by default, GTM:626, 627, 632).
FALSIFIER:   A design note requiring `FeatureCard` to compose `QualifiedClaim`, or forbidding
             `FeatureCard` on any page carrying a §14.2 claim. Neither exists in the frozen file.
CONFIDENCE:  Medium-High — this is an inference about author behaviour, but the structural half (no
             qualification slot) is observed, not inferred.
AUTHORITY:   LOCKED (§32 adjacency rule)
IMPACT IF WRONG: If authors do reliably reach for QualifiedClaim, the cost of this finding is one
             extra prop on three components. If they do not, the site's most-repeated element is
             the one place a qualification cannot go.
STATUS:      REQUIRED CHANGE — either give `FeatureCard` / `Hero` / `RoleCard` / `SegmentCard` a
             required qualification slot, or forbid them on pages carrying §14.2 claims. The
             cheaper of the two is the slot.
```

### M4-09 — structured data is unaddressed and is an unqualifiable claim surface

```
ID:          M4-09
TYPE:        OBSERVED
STATEMENT:   Structured data is explicitly in M4's scope (§8.4) and appears nowhere in
             Architecture.md. It is the one claim surface on which a mandatory qualification cannot
             travel in the same visual unit, because it has no visual unit — it renders inside
             Google's SERP, detached from the page.
BASIS:       grep -niE "schema|json-ld|structured data|sitemap|robots|noindex" Architecture.md -> 0 hits.
             M4 scope: CommitteeSystem.md:643 ("... the on-screen notices that replace an absent
             presenter · structured data").
FALSIFIER:   Any JSON-LD, sitemap or robots design in the frozen file.
CONFIDENCE:  High for the absence; Medium-High for the risk characterisation.
AUTHORITY:   LOCKED (§32 items 25 and 30; PRC-003)
IMPACT IF WRONG: Low if JSON-LD is never added. If it is added by default (many Next.js starters do),
             `aggregateRating` and `review` are §32 item 25 (manufactured trust — there are no
             customers), and `offers.price` breaches PRC-003.
STATUS:      REQUIRED CHANGE — restrict JSON-LD to `Organization` and `WebSite`; explicitly prohibit
             `aggregateRating`, `review`, `offers`, `award` and any `Product` type carrying a price.
             Add this as a build-lint rule alongside §4.1's options, not as a style note.
```

### M4-10 — `DemoCTA` as a site-wide component is a structural D-d hazard

```
ID:          M4-10
TYPE:        INFERRED
STATEMENT:   §13.5 lists a `DemoCTA.tsx` component and no "book a walkthrough" component. STEP0-001's
             condition D-d requires that "Book a walkthrough" remains the single primary CTA on every
             page and that the evaluation credential is offered *within* that booking, never instead
             of it. A component named and shaped as a demo call-to-action, dropped onto pages, is the
             exact shape of the D-d failure. Nothing in the stack prevents two primary CTAs on one page.
BASIS:       Architecture.md:135 (`DemoCTA.tsx`). D-d: CommitteeSystem.md:1115 — "Fails if: the trial
             becomes its own CTA, or appears on a page without the walkthrough". WEB-001: GTM:766.
FALSIFIER:   Evidence that `DemoCTA` is intended only as the in-page control on `demo/` itself, and a
             design note limiting it to that route. Neither is present.
CONFIDENCE:  Medium — the hazard is in the naming and the absence of a control, not in a decision
             anyone has taken.
AUTHORITY:   LOCKED (WEB-001, and STEP0-001 D-d, which is a condition on a ruling)
IMPACT IF WRONG: If D-d fails in the delivered design, STEP0-001 Reading B becomes Reading A and
             DEM-001, WEB-001, GTM-001 and GTM-003 must all be reopened in writing before anything
             ships (CommitteeSystem.md:1107-1108). This is the highest-consequence item in this file.
STATUS:      REQUIRED CHANGE — rename to a single `PrimaryCTA` component whose target is fixed to the
             booking route, and add a build check asserting at most one `PrimaryCTA` per rendered
             page. This makes D-d mechanically true rather than a habit.
```

### M4-11 — there is no data sink of any kind; the funnel's terminal step is undesigned

```
ID:          M4-11
TYPE:        OBSERVED
STATEMENT:   The advised stack (§13.2) lists framework, language, UI, styling, components, languages,
             RTL, content, CMS, hosting, source control, analytics and domains. It lists no backend,
             no database, no email provider, no CRM and no form service. §13.5 lists no `app/api/`
             path, no server action and no form component. There is therefore nowhere for a form
             submission to go — and §13.8 Q1 confirms lead-data landing is undecided. A funnel whose
             terminal step is undesigned is a funnel with a hole in it, and this one is that.
BASIS:       Architecture.md:38-52 (the stack table), :110-140 (repo structure), :195 (§13.8 Q1,
             undecided). CommitteeSystem.md:1170-1173 (§13.3 item 11, "Do not send identifiable lead
             data to an unreviewed global form or analytics service"). CommitteeSystem.md:1446
             (§15.1 item 4, still open, same-day register).
FALSIFIER:   A submit target named anywhere in the frozen file.
CONFIDENCE:  High.
AUTHORITY:   POLICY on where it lands (Owner decision, §15.1 item 4) · LOCKED that it must exist
             (STEP0-001 D-a makes the form load-bearing). The *IA placement and post-submit routing*
             are GENUINELY OPEN — options at §4.4. **Residency is not M4's to option** and no
             residency option is offered below.
IMPACT IF WRONG: The access ruling's load-bearing component cannot be built; RDY-0065's
             qualification checklist has no consumer after all; and the self-disqualification metric
             loses its only reliable instrument (M4-14).
STATUS:      OPEN — OWNER DECISION (residency) · REQUIRED CHANGE (IA placement and routing)
```

### M4-12 — correction to this dispatch's own briefing: the PDPL grep does not return zero

```
ID:          M4-12
TYPE:        OBSERVED
STATEMENT:   M4's brief asserts that `grep -n 'PDPL' docs/Marketing-MVP-and-Launch-Readiness-
             Requirements.md` returns zero matches, and instructs M4 to verify it. It returns four:
             lines 2475, 9205, 9370, 9631. The substantive point the brief was making nonetheless
             stands: none of the four establishes a PDPL control for lead data. :2475 records that
             Q45's PDPL data-residency default matches RDY-0064's closed Dammam/me-central2 decision
             for the *application*; :9205, :9370 and :9631 are scope disclaimers stating that the
             work does *not* constitute PDPL certification.
BASIS:       grep -n 'PDPL' docs/Marketing-MVP-and-Launch-Readiness-Requirements.md ; grep -c same
             -> 4. Re-run 2026-08-20. Contexts read at :2470-2482 and :9203-9207.
FALSIFIER:   A different result on re-run at the same commit.
CONFIDENCE:  High — a re-runnable command.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: The distinction matters for how the gap is described to the Owner. "PDPL is never
             mentioned" and "PDPL is mentioned four times, three of them to disclaim conformance and
             once to confirm the application's residency, and never for lead data" support the same
             conclusion but the second is defensible under challenge and the first is not. R10
             applies to the briefing pack as to any other finding (CommitteeSystem.md:199, 780-782).
STATUS:      PASS — recorded as a correction to the brief, not as a defect in the architecture.
```

### M4-13 — GA4 as advised records the locked success metric as a bounce

```
ID:          M4-13
TYPE:        INFERRED
STATEMENT:   Self-disqualification is a locked success metric. A visitor who reads homepage section 8
             ("What we don't do") or the exclusions registers, concludes correctly that Thiqa is not
             for them, and closes the tab is — in a default GA4 configuration on a static site —
             indistinguishable from a bounce, and is counted inside a metric named for failure. The
             advised analytics layer therefore reports the success metric as its opposite. That is
             precisely the pressure that produces the CRO instinct M4's brief warns about: whoever
             reads the dashboard will be told, week after week, that the disqualification section is
             costing conversions.
BASIS:       GTM:1151 — "Self-disqualification rate | Prospects who correctly rule themselves out
             before a call — a success metric, not a leak | Baseline first". Homepage section 8:
             GTM:812 and :817, "the disqualification is the feature". Analytics as advised: Architecture.md:51
             ("GA4 + Search Console"), with no event design anywhere in the file.
FALSIFIER:   An event design in the frozen file that produces a positive disqualification signal.
             None exists.
CONFIDENCE:  Medium-High — the mechanism is certain; whether anyone would act on the bad number is
             a behavioural inference, but the brief itself predicts exactly that behaviour.
AUTHORITY:   LOCKED (GTM §29) — the metric and its preservation. The *instrumentation mechanism* is
             GENUINELY OPEN; options at §4.2.
IMPACT IF WRONG: The specific failure mode is named in the system's own failure index — "Fixing the
             disqualification path", controlled by "M4's LOCKED constraint (GTM §29)"
             (CommitteeSystem.md:512). Being wrong here means the control did not fire.
STATUS:      REQUIRED CHANGE — the instrumentation must produce a *positive* signal, and the metric
             must be reported beside walkthrough requests, never inside bounce rate. Explicitly
             prohibited on the exclusion surfaces: A/B tests on the "what we don't do" section,
             exit-intent modals, and "are you sure?" interstitials. The section is not to be
             optimised; it is to be counted.
```

### M4-14 — the only reliable disqualification instrument depends on the one undecided thing

```
ID:          M4-14
TYPE:        INFERRED
STATEMENT:   The only instrument that measures self-disqualification by *declaration* rather than
             inference is the qualifying form's own disqualifying answers (payer mix "mostly
             insurance"; setting "includes beds or inpatient"; field 4 "yes" to tax invoice or
             insurance claims), routed to `not-a-fit` and recorded server-side. That instrument
             requires (a) the form to exist — it does not (M4-04), (b) a `not-a-fit` route — it does
             not exist (M4-05), and (c) a submit target — undecided (M4-11). Vercel + GA4 makes this
             worse rather than neutral: on a statically generated CDN-hosted site there is no
             server-side per-visitor record by default, so the fallback is client-side GA4, which
             ad-blockers and browser tracking prevention silently under-count. The cohort being
             under-counted is exactly the one that must not look like a loss.
BASIS:       Form fields: CommitteeSystem.md:1119-1126. Routing rule: CommitteeSystem.md:1138.
             Metric definition "before a call": GTM:1151. Hosting/analytics as advised:
             Architecture.md:49, :51. Residency undecided: Architecture.md:195; CommitteeSystem.md:1446.
             Constraint on sending identifiable lead data to a global analytics service:
             CommitteeSystem.md:1170-1173.
FALSIFIER:   A server-side record of disqualifying submissions existing independently of the
             undecided lead sink — e.g. an aggregate counter with no personal data, which would
             decouple the metric from the residency decision. That is a real possibility and is
             option 1 in §4.2; it has not been designed.
CONFIDENCE:  Medium-High.
AUTHORITY:   LOCKED (GTM §29) · the mechanism GENUINELY OPEN
IMPACT IF WRONG: If the metric is only ever measured in GA4, its number will be wrong in the
             direction that invites someone to "fix" the disqualification section.
STATUS:      OPEN — OWNER DECISION (it inherits M4-11's dependency) · options at §4.2
```

### M4-15 — routing off-site to the demo terminates funnel measurement at the click, and that is correct

```
ID:          M4-15
TYPE:        INFERRED
STATEMENT:   Route-don't-iframe is the right pattern for the funnel and does not break it, provided
             the conversion event is understood correctly. Measurement stops at the click by design,
             because §13.3 item 9 forbids clinical-UI analytics and session recording on the demo,
             and §13.8 Q3's default answer is no analytics on demo.skyeagle.uk. That is not a funnel
             defect: under STEP0-001 the conversion event is the *booking*, which happens on the
             marketing site, not demo usage, which happens after conversion. What the site must
             therefore stop doing is treating demo arrival as the conversion — and what the *demo
             host* can legitimately supply, without breaching item 9, is minimal operational
             evidence that an issued credential was used at all.
BASIS:       Architecture.md:170-189 (§13.7 launcher). Telemetry boundary: CommitteeSystem.md:1166-1167
             (§13.3 item 9 — "Only minimal operational and security events"). §13.8 Q3:
             Architecture.md:197. Conversion event: GTM:766. Credential as fulfilment of a booking:
             Challenges-and-Demo.md:46.
FALSIFIER:   A requirement anywhere that demo *usage* be a measured conversion step. GTM §29's ladder
             (GTM:1148-1163) measures "Qualification -> demo" and "Demo -> written scope" as human-stage
             transitions, not as web analytics — consistent with this finding, not against it.
CONFIDENCE:  Medium-High.
AUTHORITY:   POLICY (§13.3 item 9) on the boundary · HYPOTHESIS on the specific launcher mechanics below
IMPACT IF WRONG: If someone later "closes the measurement gap" by adding cross-subdomain GA4 to
             demo.skyeagle.uk, that breaches §13.3 item 9 and puts analytics on a clinical UI. The
             gap should be documented as intentional so it is not filled by accident.
STATUS:      PASS — with three mechanical instructions: (i) open the demo in a new tab with
             `rel="noopener"`, which preserves the marketing tab as the return path, keeps the
             two-credential instruction on screen behind it, and avoids a back-button walk through
             OpenEMR's internal navigation; (ii) never `iframe` — independently of embedding
             headers, an iframe would place a clinical UI inside a page carrying marketing claims,
             which is a claim-adjacency problem as well as a technical one; (iii) record the demo
             hand-off as an event named for what it is (`demo_handoff`), not as a conversion.
```

### M4-16 — the two-credential instruction does not survive the jump

```
ID:          M4-16
TYPE:        INFERRED
STATEMENT:   §13.7's whole value is the instruction "log in as both, open the same patient, and see
             the difference". That instruction is rendered on www.skyeagle.uk and the visitor then
             leaves for demo.skyeagle.uk, where nothing repeats it. If the demo opens in the same
             tab, the instruction is gone at the moment it is needed. The marketing architecture
             cannot deliver its own strongest funnel asset without a change on the demo host — a
             pre-login notice on demo.skyeagle.uk naming the two roles and the instruction. That
             notice is outside the marketing repository, outside Vercel, and outside anything §13
             designs.
BASIS:       Architecture.md:170-185 (§13.7). The advantage: CommitteeSystem.md:967-972;
             Challenges-and-Demo.md:274-298 (§5). Demo host is a separate Ubuntu/Apache/PHP/MariaDB
             deployment: Architecture.md:14-34; CommitteeSystem.md:814-815.
FALSIFIER:   The new-tab instruction in M4-15 fully mitigating it. It mitigates but does not close it:
             a visitor who returns to the login screen an hour later, or lands there from a bookmark
             or an email containing the credential, never sees the marketing tab.
CONFIDENCE:  Medium-High.
AUTHORITY:   HYPOTHESIS — no locked decision governs where the instruction is rendered.
IMPACT IF WRONG: Low cost if wrong (one unnecessary line on a login page). High cost if right and
             unaddressed: the single differentiating proof the funnel is built around is delivered
             as a sentence the visitor must have memorised.
STATUS:      REQUIRED CHANGE — but the change is demo-side. The credential values themselves must not
             appear on that page (§13.3 item 2 keeps issuance manual and non-public; §32 item 23
             bars `admin` from any material). Naming the two *roles* is not naming a credential; M5
             should confirm that reading.
```

### M4-17 — a broken-looking demo is a demo problem with a one-paragraph marketing mitigation

```
ID:          M4-17
TYPE:        INFERRED
STATEMENT:   Two distinct failures were conflated in the brief's question and they have different
             owners. (a) *Demo unavailable* — the visitor sees a Cloudflare or Apache error page with
             no Thiqa identity, no explanation and no way back. (b) *Denial UX* — five measured
             shapes for a blocked route including an HTTP 500 on the Fee Sheet, behind a login, inside
             OpenEMR. The marketing site can reach neither: once the visitor is on demo.skyeagle.uk,
             www has no signal (by §13.3 item 9's own rule), no measurement and no ability to
             intervene. Both fixes are therefore demo-side, and both are cheaper there. The marketing
             site's only cheap lever is expectation-setting *before* the jump — one paragraph on
             `/demo`, which also discharges §13.3 item 4's pre-entry disclosure obligation.
BASIS:       Denial UX measurement, enumerated: Challenges-and-Demo.md:248-252 — "403 with a 1.8 KB
             page, 200 with 0 bytes, 200 with 14 bytes, 401 with 0 bytes, and HTTP 500 on the Fee
             Sheet ... a visitor alone concludes the software is broken". Same finding in the frozen
             briefing, unenumerated: CommitteeSystem.md:962-964.
             Cloudflare already in front of the demo: Architecture.md:68; CommitteeSystem.md:814.
             Pre-entry disclosure requirement: CommitteeSystem.md:1156-1157 (§13.3 item 4).
FALSIFIER:   A marketing-side control that is cheaper than a Cloudflare custom error page. The only
             candidate is a pre-flight health check on the `/demo` route, which is option 4 in §4.5
             and is more expensive on every axis.
CONFIDENCE:  Medium-High for the cost comparison; High that the marketing site is structurally blind
             to the failure.
AUTHORITY:   EVIDENCE (the five denial shapes are measured) · the fix ranking is HYPOTHESIS
IMPACT IF WRONG: A visitor who was qualified, booked, and issued a credential concludes the product
             is broken at the exact moment the funnel has spent everything to reach. The cost of
             being wrong is asymmetric and falls entirely on the highest-value cohort.
STATUS:      REQUIRED CHANGE — options ranked at §4.5. Which side is cheaper: **the demo side,
             decisively, for both halves.**
```

### M4-18 — MDX-in-Git makes parity checkable; a locale fallback would silently defeat it

```
ID:          M4-18
TYPE:        INFERRED
STATEMENT:   `app/[locale]/` with `content/{en,ar}/` is structurally the right choice for WEB-003,
             and better than a CMS, because both language trees are files in one repository and a
             script can compare them. But the architecture only makes parity *possible*; it specifies
             no check, so drift stays invisible until someone notices — and §13.4-vs-§13.5 (M4-06)
             shows drift arriving before the first page is written. One design decision determines
             whether parity is checkable at all: **if the content loader falls back to English when
             an Arabic file is missing, every parity check is defeated silently and the site ships a
             half-Arabic surface that looks complete.** The loader must throw at build time on a
             missing `ar` file. That is free to implement and impossible to retrofit meaningfully.
BASIS:       Architecture.md:47 (MDX/JSON in Git), :110-140 (content/{en,ar}), :84-104 (§13.4 native
             routes and the equal-prominence requirement). WEB-003: GTM:828-832. R-08: GTM:1180 /
             CommitteeSystem.md:752. Arabic limitation with equal prominence: Architecture.md:101-104;
             §32 item 18 (Marketing-MVP...md:12046).
FALSIFIER:   A parity check specified anywhere in the frozen file. grep -niE "parity" Architecture.md
             returns only the prose requirement at :101, never a mechanism.
CONFIDENCE:  High on the structural argument; Medium-High that a fallback would be added by default
             (it is a common i18n convenience and Next.js does not forbid it).
AUTHORITY:   LOCKED (WEB-003) — the requirement. The *check mechanism* is GENUINELY OPEN; §4.3.
IMPACT IF WRONG: R-08 is Medium likelihood / High impact — an Arabic page that misrepresents the
             product's Arabic support "contradicts the entire positioning in the market's own
             language" (CommitteeSystem.md:752).
STATUS:      REQUIRED CHANGE — forbid locale fallback; add the parity gate (§4.3).
```

### M4-19 — "pricing and exclusions before the form" is a DOM-order requirement, not a layout one

```
ID:          M4-19
TYPE:        INFERRED
STATEMENT:   The POLICY constraint that pricing and exclusions sit *before* the form is a document-
             order requirement, not a visual-position one, because the same page renders LTR in
             English and RTL in Arabic. Satisfying it with CSS (`order`, `flex-direction`, absolute
             positioning, or a two-column layout that reads differently by direction) produces a page
             that is compliant in one language and not in the other. The architecture must therefore
             place `<PricingModel/>`, `<Exclusions/>` and `<QualifyingForm/>` in that DOM order in a
             single route, and forbid CSS reordering of that group.
BASIS:       The constraint: M4 brief hard constraints (CommitteeSystem.md:657). RTL at component
             level with CSS logical properties: Architecture.md:46, :97. Both locales render the same
             components: Architecture.md:111-128.
FALSIFIER:   A design that keeps the form on a separate route reached only after the pricing page.
             That satisfies the policy differently and is option 1's alternative in §4.4 — but it
             adds a navigation step to the funnel's most fragile moment, so it is not preferred.
CONFIDENCE:  Medium-High.
AUTHORITY:   POLICY (pricing and exclusions before the form)
IMPACT IF WRONG: An Arabic booking page on which the exclusions render after the form, breaching the
             policy in the language the buyer is more likely reading it in.
STATUS:      REQUIRED CHANGE
```

### M4-20 — URL structure: three mismatches between §13.5's slugs and the locked node names

```
ID:          M4-20
TYPE:        OBSERVED
STATEMENT:   `ophthalmology/` is hoisted out of "Who it's for"; `pricing/` renames "How pricing
             works"; `whats-included/` truncates "What's included — and what isn't". The first is a
             genuinely open URL-structure question (§4.6). The second and third are not neutral: a
             `pricing/` URL sets an expectation of a figure the page is forbidden to carry
             (PRC-003 BLOCKED), and `whats-included/` drops the half of the page name that does the
             disqualifying work.
BASIS:       Architecture.md:114, :121, :122 vs GTM:773-797 (locked node names). PRC-003 BLOCKED:
             Architecture.md:159; CommitteeSystem.md:870.
FALSIFIER:   Evidence that slugs are treated as independent of node names by the locked IA. §17.2
             gives names, not URLs, so slugs are formally open — but "open" does not make a
             misleading slug harmless.
CONFIDENCE:  High for the observation; Medium for the harm characterisation on `pricing/`.
AUTHORITY:   HYPOTHESIS (slugs are not locked) — but PRC-003 is LOCKED and constrains what the page
             may contain.
IMPACT IF WRONG: Minor and cheap either way, but slugs are expensive to change after indexing and
             after both locales are linked. Decide before build, not after.
STATUS:      REQUIRED CHANGE (low cost) — recommend `how-pricing-works/` and
             `whats-included-and-what-isnt/` (or `included-and-excluded/`). `ophthalmology/`
             position: options at §4.6.
```

### M4-21 — `orders-results/` is not in the locked IA at all, so §13.6's ruling adds rather than subtracts

```
ID:          M4-21
TYPE:        OBSERVED
STATEMENT:   §13.6 rules `orders-results/` down from a full page to a "sub-section only". The locked
             IA contains no orders-or-results node at any level. §13.6's ruling is therefore not a
             correction of a generic sitemap against the locked IA — relative to WEB-002 it is an
             *addition*. M0 flagged that this row cites no §32 item or decision ID; the IA reading
             adds a second, independent reason to leave it alone.
BASIS:       Architecture.md:156. Locked IA: GTM:773-797 — the Product branch is exactly Clinical
             documentation · Scheduling & front office · Reporting & export · Optional, switched off
             by default. M0's flag: M0-decision-pack-task2.md item 12g.
FALSIFIER:   Reading a sub-section of `clinical-documentation/` as inside the locked page rather than
             as a new node. That is a defensible reading and may well be the right one — it is not
             M4's to settle.
CONFIDENCE:  High for the observation; the disposition is not M4's.
AUTHORITY:   LOCKED (WEB-002)
IMPACT IF WRONG: Adding a node to a locked IA without authority is the R-02 failure mode in its
             quietest form.
STATUS:      OPEN — OWNER DECISION. **M4 has not designed it and stops here.** M4 holds no reopening
             licence (§8.4); if this needs to become part of the IA, that routes to M2 and the Owner,
             not to M4. The claim question — whether MC-21's "transmission and result receipt require
             a lab interface to be established" qualification is satisfiable in a sub-section — is
             M5's, not M4's.
```

### M4-22 — "solo practice" is not "Single clinic"; dropping the first must not drop the second

```
ID:          M4-22
TYPE:        OBSERVED
STATEMENT:   §13.6 rules that a "Solo practice" segment page is dropped as "Not the ICP". The locked
             IA contains a **"Single clinic"** segment page, which is in the ICP. These are different
             things: solo = one provider (outside ICP-001's 3-15 providers); single clinic = one
             site (inside ICP-001's 1 site, up to 3). §13.5 omits all three non-ophthalmology segment
             pages (M4-02 rows 3-5), so the risk that the §13.6 ruling is read as authorising that
             omission is live rather than hypothetical.
BASIS:       Architecture.md:160 ("Solo practice" segment page — Drop). Locked IA: GTM:776 ("Single
             clinic"). ICP: CommitteeSystem.md:782 — "3-15 providers, 1 site (up to 3)".
             §13.5's omissions: Architecture.md:111-128.
FALSIFIER:   Evidence that "solo practice" and "single clinic" were intended as the same page. The
             locked IA lists "Single clinic" and never "solo practice", so the two terms come from
             different documents and the collision is in §13.6's wording.
CONFIDENCE:  High.
AUTHORITY:   EVIDENCE — determined by GTM:776 read against CommitteeSystem.md:782. This is
             evidence-determined and closed: build "Single clinic"; build no "solo practice" page.
             **No options are offered, per §12.**
IMPACT IF WRONG: One ICP segment page missing, and the segment router (`SegmentCard`) left with one
             destination instead of four.
STATUS:      PASS on §13.6's ruling as written · REQUIRED CHANGE to §13.5's omission of the node.
```

### M4-23 — the six-field form stands; do not cut it to five

```
ID:          M4-23
TYPE:        OBSERVED
STATEMENT:   WEB-001's "no form longer than 5 fields" is HYPOTHESIS, not LOCKED, and the committee
             has already ruled the qualifying form at six fields with the sixth being the safety
             confirmation that no real patient data will be entered. Nothing in this file proposes
             cutting a field, and any later proposal to reach five by dropping the safety
             confirmation or a qualification field should be refused on the record.
BASIS:       Cap is HYPOTHESIS: CommitteeSystem.md:226-230 and :658. Six fields with field 6 as the
             safety confirmation: CommitteeSystem.md:1119-1126, :1160-1162 — "a safety confirmation
             on a shared database is the right thing to exceed it with. Dropping a qualification
             field to stay at five would be the wrong trade."
FALSIFIER:   Evidence that the cap was later made LOCKED. GTM:768 lists "any form longer than 5
             fields" under "Deliberately absent" within WEB-001, which is LOCKED as a decision —
             this is the strongest counter-argument available and it deserves stating plainly rather
             than being suppressed. The committee's own §4.2 worked example nonetheless classifies
             the cap explicitly as HYPOTHESIS, and §4.2 is the governing text for authority tags.
             The tension is recorded here rather than resolved by M4.
CONFIDENCE:  Medium-High — Medium on the authority tag given the GTM:768 tension; High that dropping
             the safety field would be the wrong trade regardless of which tag applies.
AUTHORITY:   HYPOTHESIS per §4.2 · the tension with GTM:768 (LOCKED WEB-001) is flagged for M5
IMPACT IF WRONG: If the cap is genuinely LOCKED, the six-field form needs an Owner exception in
             writing before the booking page is built — a cheap, one-line authorisation, but one
             that must be obtained rather than assumed.
STATUS:      PASS — with the GTM:768 tension flagged to M5 and the Owner. **No field is cut.**
```

---

## 4 · RANKED OPTIONS — GENUINELY-OPEN ITEMS ONLY

**Classification discipline (§12).** Options are produced **only** for items classified genuinely
open above. No options are produced for M4-02 (evidence-determined by WEB-002 — build the locked
IA), M4-03, M4-04, M4-05, M4-06, M4-12, M4-19, M4-21 (stopped, not optioned) or M4-22
(evidence-determined and closed). Producing options for those would be a failed deliverable.

**Capability compliance (`EVIDENCE`).** No option below asserts, requires, or implies any OpenEMR
product capability. They are website build-and-deploy controls and one demo-host presentation
change. The single option touching the product (§4.5 option 3) *restricts* an existing surface
already ruled by `NOGO-001`; it claims nothing new. `docs/HISModulesUsers.md` is therefore not
engaged by any option here, and no option needs a capability at a status the audit does not record.

### 4.1 · Enforcing claim + qualification adjacency (M4-07, M4-08, M4-09)

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1** | **`CODEOWNERS` + branch protection on `content/**` and `components/**`** — no merge without the claim reviewer's approval | ~1 hour | Same day | GitHub (already the org's platform); a named reviewer | Nothing. Adds one human to the merge path, which §13.8 Q4 already requires ("claim review has to sit *before* merge, not after deploy") | **High** that it catches a *novel* claim, which no automated check can. **Low** that it survives volume — a human reviewing every content PR degrades to rubber-stamping, which is the exact failure R7 exists to name |
| **2** | **Claims as data + id-only component.** `content/claims/*.json` transcribed from GTM §14.1/§14.2; `<QualifiedClaim id="MC-06"/>` renders claim and mandatory qualification from the registry. The author cannot supply, soften, truncate or omit the qualification | ~1 day build + one M5 pass over the registry | 2 days | GTM §14 stable; a named owner for registry updates | Per-page rewording of a claim becomes a governed act requiring a registry entry. That is a real loss of copywriting flexibility and should be stated as such — it is also, arguably, the point | **High** that it closes bypasses 3, 4 and 6 of M4-07's table. **It closes none of 1, 2, 5, 7-11** |
| **3** | **Build-time content lint.** CI over `content/**/*.mdx` and `components/**/*.tsx`: fail on any §32 banned adjective or prohibited phrase (both languages), and fail on any registry claim's distinctive phrase appearing outside a `QualifiedClaim`. Extend to JSON-LD (M4-09): reject `aggregateRating`, `review`, `offers`, `award` | ~1 day + ongoing term-list maintenance | 2-3 days | Option 2 for the second half | False positives ("complete the form", "best practice") need an allowlist with a reason per entry — which becomes its own governance artefact and must not become a dumping ground | **Medium-High** for banned terms (literal matching). **Medium** for the prose-claim half — phrase matching across two languages is fuzzy and will both miss and over-fire |
| **4** | **Rendered-DOM adjacency assertion.** Post-build, over the static output: for each rendered claim id, assert the qualification string is inside the same block-level container, not `aria-hidden`, not inside `<details>`, not zero-height or visually hidden | ~2 days | 1 week | Options 2 + 3 | Constrains visual design — a compact card face with a reveal becomes disallowed; brittle across component refactors | **Medium** — the only option that mechanically tests "same visual *unit*" rather than "same component". "Visual unit" is not fully expressible in a DOM query and this should not be sold as if it were |

**Recommended floor: 1 + 2.** Together they cost roughly a day and a half and close the two failure
classes that matter most (a novel unauthorised claim; an eroded or absent qualification on an
authorised one). 3 next. 4 only if drift is actually observed — it is the most expensive and the
least certain, and buying it first would be the "elegant expensive solution" failure mode
(CommitteeSystem.md:513).

**Not an option, a precondition:** give `FeatureCard`, `Hero`, `RoleCard` and `SegmentCard` a
required qualification slot (M4-08), or none of the above reaches the components authors actually use.

### 4.2 · Instrumenting self-disqualification (M4-13, M4-14) — preserved, never optimised

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1** | **Count the qualifying form's disqualifying answers, server-side, as an aggregate with no personal data.** Fields 1, 2 and 4 already ask the disqualifying questions; route the disqualifying pattern to `/not-a-fit` and increment a counter. Because the counter carries no identifiers, **it can be decoupled from the undecided lead-residency question** and shipped before it is answered | ~half a day once a submit target exists | 1 day | The submit target (M4-11) — but only a counter, not a lead store | Nothing | **High.** This is GTM §29's definition measured literally — "prospects who correctly rule themselves out **before a call**" — by declaration, at the moment it happens |
| **2** | **GA4 events on the exclusion surfaces**, no identifiers: `exclusions_viewed`, `register_expanded`, `what_we_dont_do_reached`, `not_a_fit_reached` | ~2 hours | Same day | GA4 (already advised) | Nothing, but it must never be *reported* as the disqualification rate | **Medium-Low** as a disqualification measure — it measures reading, not deciding, and client-side blocking under-counts. **Medium** as an engagement measure |
| **3** | **A one-click "does this rule us out?" control** on the exclusions page — two buttons, no fields, recorded server-side as an aggregate | ~half a day + a WEB-001 ruling | 2 days | Option 1's counter; an Owner/M5 confirmation that a non-navigating feedback control is not a competing CTA | Risks reading as a second CTA if styled like one. Must be visually subordinate and must not navigate. If M5 or the Owner reads it as a CTA, it fails D-d and WEB-001 and must be dropped | **Medium** on the instrument; **Low-Medium** on the WEB-001 clearance, which M4 cannot grant itself |
| **4** | **Server-side request logging for the exclusion routes**, replacing GA4 for this one metric so ad-block and tracking-prevention loss does not distort the number that must not look like a loss | Unknown on Vercel — a log-drain/processor question | Unknown | **M6's answer on Vercel logging and its processor/residency posture** | Adds a data processor and possibly a residency question for visitor IPs | **Low** without M6's answer. Recorded so it is evaluated, not assumed unavailable |

**Reporting rule, binding on all four (`LOCKED`, GTM §29):** self-disqualification is reported
**beside** walkthrough requests as a positive count, **never inside bounce rate**, and the exclusion
surfaces are **not** subject to A/B testing, exit-intent modals or confirmation interstitials. The
section is counted, not optimised. Vercel changes none of this; GA4 changes only *which* option is
trustworthy, and the answer is that option 1 is and option 2 is not.

### 4.3 · The bilingual parity gate (M4-18)

**Precondition, zero cost, not optional:** the content loader **throws at build time** on a missing
`ar` file. No English fallback. With a fallback present, every option below passes while the site is
half-Arabic.

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1** | **File parity check** — enumerate `content/en/**` and `content/ar/**`, fail CI on any asymmetry | ~1 hour | Same day | None | Forces an `ar` stub to exist for every `en` file — which is *worse* than a build failure if stubs may be empty. Pair with a minimum-structure rule or do not ship it alone | **High** for missing pages; **none** for thin ones |
| **2** | **Structural parity** — per file, require identical claim-id sets, identical heading counts, identical `QualifiedClaim`/`StatusRegister`/`ProductScreenshot` invocation counts, and word count inside a wide band | ~1 day | 2 days | Option 1; §4.1 option 2 (claims as data) for the claim-id half | Legitimate divergence (an Arabic page needing an extra explanatory heading) needs a per-file waiver with a reason. **Do not test word-count equality** — Arabic renders the same content in materially fewer words, so an equality test would fail correct translations and train everyone to ignore the gate | **High** for the claim-id half — this is the part that actually enforces WEB-003's substance. **Medium** for heading counts |
| **3** | **Prominence parity for the 47.5% limitation (R-08)** — assert MC-06's claim id appears in the Arabic file at the same section index as in the English one | ~half a day on top of option 2 | 3 days | Option 2 | Pins Arabic page ordering to English ordering. Costs nothing *now*, because RDY-0089 already requires a faithful translation of the locked English hierarchy and forbids inventing Arabic-specific positioning; it will need relaxing when the Arabic competitor review lands | **Medium-High.** Presence-at-the-same-structural-position is most of "equal prominence" and all of it that is mechanical |

**Direct answer to the question asked:** `app/[locale]/` with MDX-in-Git makes parity **structurally
checkable** — better than any CMS would, because both trees are diffable files in one repository.
It does **not** make parity checked. As specified, drift stays invisible until someone notices, and
M4-06 demonstrates drift arriving inside the architecture document itself.

### 4.4 · The form's placement and post-submit routing in the IA (M4-11)

**Scope note:** these are IA-and-funnel options. **Lead-data residency is not optioned here** — it is
`§15.1 item 4`, an open Owner decision, and M6's technical lane. Option 4 exists to price the
consequence of launching without an answer, not to answer it.

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1** | **One booking route, both locales.** `book-a-walkthrough/` renders `<PricingModel/>`, `<Exclusions/>`, `<QualifyingForm/>` in that DOM order (M4-19). Every primary CTA site-wide targets it. Post-submit: `/thank-you` (qualified) or `/not-a-fit` (disqualified), both `noindex`, neither in navigation | 3 routes + 1 form component | 2-3 days | A submit target (residency decision) | Nothing. Preserves D-d structurally — one CTA with one target site-wide — and satisfies the pricing-and-exclusions-before-the-form policy in document order, which survives RTL | **High** |
| **2** | **Option 1 plus a separate, lower-intent `contact/`** — the locked IA's own node, "[short form + WhatsApp]". Keep it to three fields, treat it as correspondence, and **never** issue a credential from it | 1 more route | +1 day | Option 1 | Risks two form surfaces reading as two CTAs. Mitigate by making Contact's CTA secondary on every page it appears. This is an addition to option 1, not an alternative to it | **Medium-High** |
| **3** | **Embedded third-party booking/form widget** (Calendly-class) | Lowest build cost | Hours | A third-party processor | Sends identifiable Saudi clinic-owner data to an unreviewed global processor — directly contrary to §13.3 item 11 (CommitteeSystem.md:1170-1173). Also injects third-party script into pages that must carry qualifications, and adds CSP and consent surfaces to a page whose §32 discipline depends on controlling what renders | **High that it is the wrong choice here.** Listed because it is what a team under launch pressure reaches for, and refusing it later is cheaper than removing it later |
| **4** | **No form: WhatsApp and email only** | Zero | Zero | None | Destroys D-a — the form *is* RDY-0065's qualification checklist, not a second artefact beside it — and removes §4.2 option 1, the only reliable self-disqualification instrument. If D-a fails, STEP0-001 is Reading A and four locked decisions reopen | **High that it fails the ruled design.** Recorded as the honest floor: this is what launching before the residency question is answered actually means, and it should be chosen deliberately if it is chosen |

### 4.5 · The visitor meets a demo that looks broken (M4-17)

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1** | **A branded edge error page on `demo.skyeagle.uk`.** Cloudflare is already in front. One HTML artefact served for 5xx and maintenance: "the demo is being reset", how long, and links back to `www` and to the recorded audit-integrity run | 1 HTML file + 1 Cloudflare setting | Hours | Cloudflare access (M6/ops). Copy is M3's, cleared by M5 | Nothing | **High.** Demo-side, and cheaper than anything the marketing site can do — because once the visitor has left, `www` cannot see the failure at all |
| **2** | **Expectation-setting on `/demo` before the jump** — one paragraph: shared synthetic data, reset periodically, some areas closed in an evaluation role, what to do if a screen is unavailable | Copy only | Hours | M3 writes, M5 clears | Nothing. Also discharges §13.3 item 4's pre-entry disclosure (CommitteeSystem.md:1156-1157), so it is required anyway | **High** that it is required; **Medium** that it mitigates — it manages perception, it does not fix the 500 |
| **3** | **One consistent denial response inside OpenEMR** — the remaining `NOGO-001` item: one branded "not included in this evaluation" response replacing five shapes | Real development on the demo host | Days | Demo host; `NOGO-001 apply`; must be folded into the clean baseline or the first reset re-grants it (the PB-442 regression shape) | Touching error handling risks masking real errors from monitoring — it must log while showing a branded page. The HTTP 500 on the Fee Sheet is a **defect**, not a UX choice, and papering over it hides a fault | **Medium** on cost; **High** that it is required eventually. Challenges-and-Demo.md:249-251 already scopes it as "one notice, not fourteen" |
| **4** | **Pre-flight health check on the marketing `/demo` route** before showing the launcher | Runtime compute + cache | Days | M6's answer on Vercel functions and regions | Turns a static page dynamic; adds a failure mode of its own (a false negative hides a working demo); adds a cross-origin request from `www` to `demo` on every visit | **Low.** Most expensive, least benefit. Listed so it is not proposed later as though new |

**Which side is cheaper, answered directly:** **the demo side, for both halves.** Options 1 and 3 are
demo-side; the marketing site's only cheap lever is option 2, which is copy it must write anyway.
The marketing site is *structurally blind* to both failures — no signal, no measurement (by §13.3
item 9's own rule), no ability to intervene once the visitor has left `www`.

### 4.6 · URL structure for the segment tree (M4-20)

| # | Option | Cost | Time | Dependency | What it would break | Confidence |
|---|---|---|---|---|---|---|
| **1** | **Flat** — `/{locale}/ophthalmology` as §13.5 has it | Zero | — | None | Detaches the lead segment page from the segment router whose job is to route the right buyer and repel the wrong one; breadcrumbs and parent links must be hand-built and hand-maintained in both locales | **Medium** |
| **2** | **Nested** — `/{locale}/who-its-for/ophthalmology` | ~2 hours | Same day | None | Longer URL, marginally weaker exact-match signal for the lead segment's highest-intent query. Matches the locked tree exactly; breadcrumbs and hierarchy come free | **Medium-High** |
| **3** | **Nested canonical + flat alias with a 308 to the nested form** | ~half a day | 1 day | None | One redirect rule per locale that must be maintained and must be mirrored exactly in `ar`, or route parity (§4.3 option 1) fires on it | **Medium** |

**Binding on all three:** whichever is chosen must be **identical in both locales**, or route parity
checking breaks on its first run.

---

## 5 · ACCEPTANCE SELF-CHECK (§8.4)

| Criterion | Result |
|---|---|
| *"A concrete, checkable answer to 'does this stack serve the locked IA and the funnel?'"* | §1 — serves the IA conditionally, does not serve the funnel at all; per-element table; the funnel finding rests on a grep of a SHA-pinned file |
| *"the §13.5-vs-locked-IA page diff produced explicitly"* | §2 — 25 locked nodes vs 17 routes; **zero additions**, **8 omissions** named individually with their claim traces, plus 3 position/name mismatches and 2 ruling-required routes absent from both lists |
| *"the `QualifiedClaim` enforcement argument tested rather than repeated"* | M4-07 — tested and rejected as written; 11 bypasses enumerated; M4-08 argues the designation is on the wrong component; 4 ranked enforcement options at §4.1 |
| *"no prohibited page designed"* | M4-03 — §13.5 checked line by line against §17.2 and §13.6: clean. M4 confirms it has designed none. Residual named: `resources/` as an open container |
| *"the disqualification path preserved and its instrumentation addressed"* | M4-13, M4-14, §4.2 — preserved without exception; the CRO instinct named and refused; a binding reporting rule and an explicit prohibition on A/B testing the exclusions; 4 ranked instruments with the honest confidence on each |
| *"the funnel answers Challenges 1 and 4 concretely"* | Challenge 1: M4-04, M4-05, M4-11, §4.4 — the booking route, the form's placement, DOM-order compliance, the two missing routes. Challenge 4: M4-16, M4-17, §4.5 — the instruction that does not survive the jump, and the denial/unavailability UX with the cheaper side named |
| *"In C-2: at least one substantive attack on M6's reasoning"* | **Not applicable to this dispatch and deliberately not attempted.** This is R2, the silent independent round; M6's output has not been read. The attack is a separate later dispatch (`M4-attack-on-M6.md`), and writing it now would be fabrication |
| *Out of scope respected — no re-architecture of the locked IA; no reopening proposed* | M4-21 stops at the flag and routes onward rather than proposing a change to WEB-002. §2.3's two additions are extensions (destinations of already-ruled routing rules), not changes to the locked tree. No reopening is proposed anywhere in this file |
| *§12 classification discipline* | Options produced for 6 genuinely-open items only. M4-22 answered and closed as evidence-determined with no options. M4-02/03/04/05/06/12/19/21 carry no options |
| *Capability constraint (`EVIDENCE`)* | §4 preamble — no option asserts or requires an OpenEMR product capability; `HISModulesUsers.md` is not engaged |

---

## 6 · DECLARATIONS

**Model ID declared in this agent's runtime context (for the §5.1 D3b record):** `claude-opus-5`
(model name: Opus 5). Assigned model per §14/§5: Opus 5, Tier A. **Observed = assigned.**

**Finding count and §7.1 field compliance:**

- **Findings written: 23** (M4-01 … M4-23).
- **Findings carrying all nine §7.1 fields (ID · TYPE · STATEMENT · BASIS · FALSIFIER · CONFIDENCE ·
  AUTHORITY · IMPACT IF WRONG · STATUS): 23 of 23.** Counted field by field.
- **BASIS compliance:** every BASIS is a `file:line`, a re-runnable command, or both. Four findings
  (M4-01, M4-05, M4-09, M4-12) rest on grep results reproduced verbatim so they can be re-run against
  the pinned SHA-256.
- **Option sets: 6**, covering 6 genuinely-open items; every option carries cost, time, dependency,
  what it would break, and a confidence; cheapest first in every set, with a genuine argument for the
  cheapest (§4.1 option 1 and §4.5 option 1 are both argued *for*, including where the argument is
  partly against them).

**R9 compliance:** this agent wrote exactly one file — this one — and ran no git command.

**C-4 status:** `NOT CLAIM-REVIEWED`. M5's pass is required before any sentence here is used in a
decision or reaches a page.
