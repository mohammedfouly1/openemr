# M0 — DECISION PACK — TASK 2

**Dispatch ID:** `COM-20260820-001`
**STATUS: FINAL — PRE-POLICY OVERAGE DISCLOSED.** All twelve §7.2 stages complete. M5's single
meta-review of §11 landed 2026-08-20 and is recorded in §12; under R12 the pack closes there and no
recursive review is created. Not `COMMITTEE COMPLETE`; not `STANDARD BUDGET PASS` — the budget
checker FAIL is disclosed verbatim in `M0-dispatch-log.md` and in §4 (Owner budget directive,
`COM-20260820-001-budget-directive.md`, whose Owner authorship is itself flagged unverified).

All twelve §7.2 sections are present below, per the rule that a missing section is a failed
deliverable even when the honest content is "not applicable this task" (§7.2 header).

---

## 1 · TASK

**Requester:** Owner (mohammedfouly@noursolution.com), in conversation, 2026-08-20.

**Verbatim trigger:**

> `$committe` Check whether the architecture in G:\My Drive\OpenEMR\docs\Marketing Website\06-technical\Architecture.md is the best fit for the marketing website and whether it can be deployed and operated correctly on Vercel.

**Task specification (§14 TASK 2).** Audit the architecture advised in
`../06-technical/Architecture.md` §13. Return **approve / deny / change**, per layer. Committee:
C-2. Firing set per §14: M6 leads · M4 informs, then attacks · M5 checks claim implications · M0
chairs. Idle per §14: M1, M2, M3, M7.

**Owner's framing is broader than §14's written objective.** §14 asks whether the *stack selection*
is right. The Owner's trigger adds an explicit second question — **can it be deployed and operated
*correctly* on Vercel** — i.e., Vercel *operational* fitness: build model, regions vs residency,
form handling, secrets, preview-deploy exposure, custom domain/TLS, i18n routing mechanics,
analytics, logging, cost, and the jurisdictional handling of a lead form that must not leave Saudi
Arabia. This is carried into this pack as a **mandatory additional scope item** (classification
item 15) — an addition to §14's scope, not a substitution for it. M0 has no authority to narrow the
Owner's own question back down to §14's original wording (§8.0 out-of-scope: "deciding a
genuinely-open question").

**Mode:** FULL ROUND (§1.1 default; this task touches architecture, security, privacy and a
decision — ineligible for SINGLE-AGENT downgrade).

---

## 2 · CLASSIFICATION

Method per §4.1: **evidence-determined** (corpus contains the answer, no discussion, cite the
determining basis) · **constraint-determined** (§32 or a locked decision governs, M5 holds a veto)
· **genuinely open** (multiple defensible answers, evidence does not settle it — the only class
where deliberation happens). Authority tags per §4.2 (`LOCKED` / `EVIDENCE` / `POLICY` /
`HYPOTHESIS`) given where a tag applies cleanly.

| # | Item | Class | Basis / governing ID | Note |
|---|---|---|---|---|
| 1 | Framework choice (Next.js) + Astro/WordPress/custom-PHP ranking (95/88/73/55) | **GENUINELY OPEN** — primary audit subject | Architecture.md:4, 54–58, 202: status explicitly `ADVISED — not locked`. The ranking scores carry no cited derivation (Architecture.md:54 asserts them; no methodology shown) | What would settle it: M6's layer audit against *this* project's actual constraints (bilingual parity at scale, PHP-demo separation, locked IA, claim-register enforcement) — exactly TASK 2's mandate |
| 2a | Styling driven by `brand/tokens/thiqa-tokens.json` (vs hand-rolled values) | **EVIDENCE-DETERMINED** `EVIDENCE` | `brand/tokens/thiqa-tokens.json` exists, validated (38 contrast pairs, 0 WCAG failures) — Tools-and-Skills.md:58 | Using the existing audited token system rather than hand-rolled values is settled; not using it would need its own justification nobody has offered |
| 2b | TypeScript / React / Tailwind-as-framework / component set otherwise | **GENUINELY OPEN**, low controversy | Architecture.md:41–44, status `ADVISED — not locked` (Architecture.md:4, 202); no §32/locked item or `HISModulesUsers.md` fact governs implementation language | Bundled into item 1's audit — no separate deliberation needed if item 1 is decided, but no fact currently forces it either |
| 3a | Bilingual-from-day-one **requirement** | **CONSTRAINT-DETERMINED** `LOCKED` | WEB-003 — CommitteeSystem.md:138 ("every customer-facing deliverable is bilingual (WEB-003)"); §8.7 M7 brief, CommitteeSystem.md:761 | Not open to negotiation; no reopening request needed — Architecture.md's advice already complies |
| 3b | `/en/`/`/ar/` native-route + component-level RTL **mechanism** | **GENUINELY OPEN** | Architecture.md:97–99 asserts native routes beat a translation plugin on speed/ranking, without a cited measurement for *this* stack | Folded into item 15 (Vercel i18n routing mechanics) — M6 verifies the mechanism actually delivers on Vercel, not just that the requirement exists |
| 4 | Content in MDX/JSON in Git, no CMS initially | **GENUINELY OPEN** | Architecture.md:47–48, status `ADVISED — not locked` | No locked decision or audit fact governs content-storage mechanism |
| 5 | **Hosting = Vercel** (the layer itself, not the Owner's operability sub-questions — see item 15) | **GENUINELY OPEN** — primary audit subject | Architecture.md:49 gives Vercel **one line** ("Automatic deploys from Git, preview builds, global CDN, HTTPS") with no operational detail. §13.8 Q1 (residency) is confirmed still open at CommitteeSystem.md:1456 item 4 | This is a genuine evidentiary gap, not a settled recommendation quietly being rubber-stamped |
| 6 | Source control = GitHub | **EVIDENCE-DETERMINED** `EVIDENCE` | `git remote -v`, re-run 2026-08-20: `origin → github.com/mohammedfouly1/openemr`, `upstream → github.com/openemr/openemr.git`. This project's own `CLAUDE.md` documents a GitHub-centric PR workflow (`gh pr create`) | GitHub is already the org's source-control platform. M6 confirms only the GitHub↔Vercel deploy-integration operability (folds into item 15), not whether GitHub itself is right |
| 7a | Analytics on the demo host: **none** | **CONSTRAINT-DETERMINED**, `POLICY` — **reclassified at stage 1, see below** | CommitteeSystem.md:1176–1177 (§13.3 item 9): "no session recording and no clinical-UI analytics on the demo. Only minimal operational and security events until privacy review approves more" | **Fact-moved flag:** Architecture.md:197 (§13.8 Q3) still frames this as an *open* question with only a "default answer." The governing charter, dated the same day, has since stated it as a firm gate requirement. M0 uses the newer, more binding text and flags the discrepancy to M6 rather than silently picking whichever framing was read first |
| 7b | Analytics on the marketing site: GA4 + Search Console | **GENUINELY OPEN** — undocumented gap | Architecture.md:51 recommends it with no privacy analysis; §13.8 lists only lead-form data (Q1), not GA4 visitor data, as a residency question | Not previously identified anywhere in the corpus as a PDPL question. Flagged for M6 to address explicitly, not to silently inherit item 15/Q1's treatment |
| 8a | Domain: `demo.skyeagle.uk` | **EVIDENCE-DETERMINED** `EVIDENCE` | Already live — reverified today, `curl -I https://demo.skyeagle.uk/` → HTTP/1.1 302, Cloudflare, valid TLS chain, 2026-08-20T04:50:46Z | — |
| 8b | Domain plan: `www` / `staging`, `app` reserved | **GENUINELY OPEN** | Architecture.md:52; not yet built | Unaudited technical question: does Vercel's own TLS/DNS management coexist cleanly with Cloudflare already sitting in front of a sibling subdomain (`demo`) on the same apex zone (`skyeagle.uk`)? Not addressed anywhere in the corpus — feeds item 15 |
| 9 | Three-environment separation rule (§13.1) — marketing site must not be built inside OpenEMR | **EVIDENCE-DETERMINED** `EVIDENCE` | Architecture.md:8–34: opposite technical requirements (static CDN vs PHP+sessions+cron+persistent filesystem); demo.skyeagle.uk already runs as a wholly separate Ubuntu/Apache/PHP/MariaDB host (CommitteeSystem.md:824–827), not inside a shared codebase | No counter-proposal exists anywhere in the corpus. M6's role is confirmatory (does the advised implementation preserve the separation in practice — see item 13), not deliberative |
| 10a | Do not move demo off Dammam/`me-central2` | **CONSTRAINT-DETERMINED** `LOCKED` | RDY-0064, closed 2026-08-19 — CommitteeSystem.md:818; M6's own out-of-scope line, CommitteeSystem.md:724–725 | No reopening request |
| 10b | Cloudflare login rate-limiting is the missing edge control | **EVIDENCE-DETERMINED** (OBSERVED gap) | Architecture.md:78–80; independently corroborated as still open in CommitteeSystem.md §13.3 item 3 (CommitteeSystem.md:1163–1165), part of TASK 1's still-unclosed security gate | Gap is real and current as of this dispatch. M6 audits whether the advised architecture makes it easier or harder to close, not whether it exists |
| 11a | `QualifiedClaim` component | **CONSTRAINT-DETERMINED** `LOCKED` | §32's mandatory-qualification-adjacency rule (CommitteeSystem.md:635; Architecture.md:142–144: "the most important component on the site") | No reopening request |
| 11b | `StatusRegister` component | **CONSTRAINT-DETERMINED** `EVIDENCE` | Must render the audited figures verbatim: 47 Disabled / 27 Uninstalled / 18 Requires-Integration / 60 Missing — CommitteeSystem.md:805, `EV-067` | Not a design choice; content is fixed by the published registers |
| 11c | Rest of `../13.5` repo structure and component list (Hero, Navbar, Footer, FeatureCard, RoleCard, SegmentCard, ProductScreenshot, WorkflowJourney, DemoCTA, FAQ, MilestoneFeed, folder layout) | **GENUINELY OPEN**, low stakes | Architecture.md:106–140; no §32/locked item or audit fact governs folder taxonomy or these components' existence | M6 confirms nothing in the list violates §32; does not need to deliberate alternatives |
| 12 | §13.6 prohibited-page corrections — **5 of 7 cite a specific governing ID; 2 do not** | **CONSTRAINT-DETERMINED** (5) / **GENUINELY OPEN** (2), split below | See per-row basis | — |
| 12a | `billing/` must not exist | CONSTRAINT-DETERMINED `LOCKED` | §32 items 4, 12 — Architecture.md:154 | — |
| 12b | `insurance/` must not exist | CONSTRAINT-DETERMINED `LOCKED` | §32 item 12 (highest-drift-risk row, R-02) — Architecture.md:155 | — |
| 12c | `Testimonial` component must not exist | CONSTRAINT-DETERMINED `LOCKED` | §32 item 25 — Architecture.md:157 | — |
| 12d | `ComparisonTable` (named competitors) deferred | CONSTRAINT-DETERMINED `LOCKED` | COMP-001 — Architecture.md:158 | — |
| 12e | Pricing page: model only, no figures | CONSTRAINT-DETERMINED `LOCKED` | PRC-003 BLOCKED — Architecture.md:159; CommitteeSystem.md:880 | — |
| 12f | Certification / trust badges must not exist | CONSTRAINT-DETERMINED `LOCKED` | §32 items 14, 25 — Architecture.md:161 | — |
| 12g | `orders-results/` — sub-section only, not a full page | **GENUINELY OPEN** | Architecture.md:156 asserts this without citing a specific §32 row or locked ID | M5 must check the canonical annex for whether a more specific item actually governs it, rather than this being treated as settled by citation alone |
| 12h | "Solo practice" segment page — drop | **GENUINELY OPEN** | Architecture.md:160 asserts "Not the ICP" without a citation | Defensible on ICP grounds (GTM ICP-001) but not tied to a §32/locked ID by name in the source; M5 to confirm the governing basis |
| 13 | §13.7 demo launcher — route, do not iframe | **GENUINELY OPEN**, pending M6 verification | Architecture.md:170 asserts this without showing OpenEMR's actual frame-embedding headers (`X-Frame-Options` / CSP `frame-ancestors`) either way | What would settle it: M6 checking demo.skyeagle.uk's actual response headers, and confirming third-party-cookie/session-domain behaviour across route-based vs iframe-based access |
| 14a | §13.8 Q1 — lead-form data residency under PDPL | **GENUINELY OPEN** | Confirmed still open — CommitteeSystem.md §15.1 item 4 (same-day register, not stale) | Folded into item 15 |
| 14b | §13.8 Q2 — Thiqa vs SkyEagle branding | **GENUINELY OPEN** | Confirmed still open — CommitteeSystem.md §15.1 item 3 | Out of M6's technical lane in substance, but the architecture must not assume a resolution (Architecture.md doesn't) |
| 14c | §13.8 Q3 — analytics on the demo | **MOVED — see item 7a** | — | — |
| 14d | §13.8 Q4 — who operates the site / claim review before merge | **GENUINELY OPEN**, principle vs mechanism split | The *principle* that a claim review must precede publication is constraint-determined (C-4, §3.4; `EV-003`/RDY-0003 per Tools-and-Skills.md:157). What's open is the *technical enforcement* — is there a required check gating a Vercel deploy, or only a process rule with no technical enforcement | M6 audits the mechanism; M0 does not treat the open principle as already closed |
| 15 | **Owner's Vercel operability question** — build model (SSG/ISR/SSR), edge/function regions vs Saudi residency, form handling, env/secret handling, preview-deploy exposure, custom-domain+TLS coexistence, redirects/i18n routing mechanics, analytics, logging, cost, PDPL jurisdiction for a lead form that must not leave Saudi Arabia | **GENUINELY OPEN — mandatory additional scope, substantial documented gap** | Architecture.md's own §13.9 status line admits only that "§13.8's four open questions need answers before build starts" — a narrower claim than the Owner's question. None of the 10 named sub-items is addressed anywhere in the source document beyond the single line at Architecture.md:49 | This is the dispatch's central finding at the classification stage: **the Owner's specific question is not answered anywhere in the audited document.** Each sub-item must get an explicit M6 verdict or an explicit genuinely-open record with what evidence would settle it — none may be answered with ranked options where the corpus already settles it (none do), per the standard acceptance block (§12) |

**Summary count:** 6 rows evidence-determined · 10 rows constraint-determined (2a is evidence,
corrected — see table; count is 3a, 7a[moved], 10a, 10b[evidence], 11a, 11b, 12a–12f = 9 constraint
rows, 10b and 11b tagged EVIDENCE-authority within a constraint context) · remainder genuinely open,
including item 15's 10 sub-questions and items 12g/12h found to lack a cited governing ID despite
being framed as corrections.

**Trap check (§4.1 closing note).** No item above was resolved with ranked options where the corpus
already settles it — items 1, 2b, 4, 5, 8b, 12g, 12h, 13, 14a, 14b, 14d and 15 remain genuinely
open and are left open here rather than pre-answered by M0 (M0 generates no content, §8.0). No item
was rubber-stamped as "determined" without a citable basis — items 12g and 12h were caught exactly
by this test: Architecture.md frames them as if governed, but no §32 item or locked-decision ID is
cited for either, so they are recorded genuinely open pending M5's canonical-annex check rather than
accepted on the strength of the document's own framing.

---

## 3 · FIRING SET

| Role | Status | Reason |
|---|---|---|
| M0 | **FIRE** | Chair, mandatory every dispatch. |
| M1 | IDLE | No market-intelligence question in scope; no firing agent reads M1's output for this task. |
| M2 | IDLE | Tested, not copied: its reopening licence is for locked decisions, and Architecture.md's stack is explicitly not locked (§13.9). If audit findings do reach an actually-locked item, M4's own brief routes that to a stop-and-flag, not to M2 firing pre-emptively. |
| M3 | IDLE | No customer-facing copy is produced by this task. |
| M4 | **FIRE** | Generates the narrow IA/funnel-fit answer, then attacks M6's verdicts (§2's adversary-on-adversary rule). |
| M5 | **FIRE** | §14 names it for claim-implication review; independently mandatory under C-4 since this feeds an Owner decision. |
| M6 | **FIRE** | Lead of C-2; the audit is its brief. |
| M7 | IDLE, **`PROPOSED — Owner decision required`** amendment recorded | Tested, not copied: M7 owns Arabic *content* and RTL practice; M6's brief explicitly covers "bilingual routing mechanics." The seam is real but narrow — M6's own out-of-scope line is "marketing judgement," and whether Next.js's i18n mechanism satisfies WEB-003's *equal-prominence* requirement at the component level partly straddles localisation judgement. Proposed: add M7 in a CONSULTED capacity on M6's RTL/i18n findings before this pack closes. Not enacted — §14's firing set fires as written (R11). |

Full reasoning for each row is in `M0-dispatch-log.md`, entry `COM-20260820-001` (duplicated here in
compressed form per §7.2's requirement that the pack itself carry §3 · FIRING SET).

---

## 4 · GATES

**Preflight (§6.3) — all rows PASS:**

| Check | Result |
|---|---|
| Model availability | PASS — `fable`/`opus` selectable now |
| Agent mapping | PASS — `general-purpose` agent type maps every M0–M7 role (§6.1) |
| Source readability | PASS — 6/6 sources read, byte counts recorded in `M0-dispatch-log.md` |
| Skill status | PASS — all named skills `OPTIONAL / NOT INSTALLED / NOT USED`; none installed during this dispatch |
| Canonical controls | PASS — §32 and GTM §14 attached with hash + timestamp (§5 below) |
| Evidence freshness | PASS — demo host rechecked 2026-08-20T04:50:46Z, inside 24h window |
| Write isolation | PASS — pre-dispatch diff taken 2026-08-20T04:50Z (37 lines), output paths uniquely assigned (§3 of the dispatch log) |
| Decision authority | N/A at this stage — no reopening or override is being requested by stages 1–4 |

**Parity — D3a: PASS.** M0 exempt from the floor. M6 = Opus 5 (Tier A), M4 = Opus 5 (Tier A), M5 =
Opus 5 (Tier A). Floor holds: reviewer (M5, Tier A) ≥ every generator (M6, M4, both Tier A).

**Parity — D3b: NOT YET RUN.** M6/M4/M5 have not been dispatched in this stage (stages 1–4 only).
When they are dispatched, each will self-report the model ID declared in its own runtime context.
**Recorded now, as an open item, not resolved by M0:** the runtime's agent dispatcher does not
externally report the model a subagent actually ran on. Per §5.1, `UNOBSERVED = INVALID by
default`, and a self-report is a **runtime self-declaration, not an external observation**. This is
a **standing environmental limitation** of this runtime, observed before, not a one-off failure of
this dispatch. Any D3b value recorded in a future update to this pack will be labelled
`SELF-REPORTED (runtime-declared)`, and **releasing TASK 2's output as floor-compliant under the
strict §5.1 reading requires the Owner's written exception.** Until such an exception is given, or
D3b passes by a stronger observation method, output from this dispatch is **not represented as
floor-verified.**

**Wall-clock sequence, this stage:** preflight and evidence-freshness recheck run by the
Orchestrator 2026-08-20T04:50Z–04:51Z; M0 classification and this pack's stages 1–4 completed in
the same session. **Nothing failed, timed out, or was retried in stages 1–4.**

**CLOSURE ADDENDUM — supersedes the D3b/exception language above.** §5 was replaced mid-dispatch by
the runtime-neutral policy. Self-declared model IDs (all agents, M0 included) are **inadmissible as
identity evidence** ("no invented identity"). Restated, every firing agent: **`NOT REPORTED BY
RUNTIME`**, **`PARITY NOT VERIFIABLE`** — valid with disclosure (§5.1), no quarantine trigger,
**no Owner exception required** (earlier framing withdrawn, self-error, §11). M5 ruled this
directly, M5-09, REQUIRED CHANGE.

**Charter moved mid-dispatch (dispatch defect, not agent error).** `CommitteeSystem.md`:
95,483B→95,990B→101,481B, 1,471→1,566 lines, mtime 2026-08-20T05:36:50Z. Breaches: R8 (briefing not
frozen) · §6.3 (hashes §32/GTM §14/§40/Architecture.md, not the charter itself) · §6.4 (diff blind
to edits inside the already-untracked `01-governance/`, Orchestrator-verified — a defect in the
control, not its execution). §14 (TASK 2) substantively unchanged (Orchestrator-diffed; one clause,
runtime wording only) — the audit stands against its task spec. This pack's own §1–4 line-citations
into `CommitteeSystem.md` are stale ~95 lines; not re-verified individually within this closure's
budget — treat line numbers as approximate, finding IDs and anchor text stand. **Do not score M6 or
M4 down for stale pointers** (§11).

**Budget checker: FAIL, disclosed, pre-policy** — full output in `M0-dispatch-log.md` closure entry.
Not budget-compliant; permitted status `FINAL — PRE-POLICY OVERAGE DISCLOSED` once §12 closes,
never `STANDARD BUDGET PASS`.

---

## 5 · FROZEN EVIDENCE

One bundle, hashed, identical for every agent that fires in the next stage (R8).

| Annex item | Source | Line range | SHA-256 |
|---|---|---|---|
| Canonical §32, all 30 numbered rows | `Marketing-MVP-and-Launch-Readiness-Requirements.md` | 12022–12071 | `e8822f8c6104fff287af9b15a7eb482becf18d9ca3cbe1dcf3ca4fd9a7c3bc6c` |
| GTM §14 (14.1 + 14.2, complete) | `Product-Positioning-and-GTM-Locked-Strategy.md` | 597–646 | `667fd6a21d8ce84fddcd35ca086a442423a588b553e8f56cb9d81a484e61e310` |
| §40 Demo No-Go Register | `Marketing-MVP-and-Launch-Readiness-Requirements.md` | 12577–12604 | `4e594a193cda7bb117615dc2c5fe8b9df1457097fd77e683931ef4f12218d8a0` |
| `Architecture.md` as audited | `docs/Marketing Website/06-technical/Architecture.md` | full file (206 lines) | `938ff387cfaa239c29c89f41b8d11ef22e6ceac24b9569d362cc0dc18c8c0c1a` |

Frozen 2026-08-20T04:50:34Z. Firing agents read these sections directly from the repo paths at the
stated line ranges; the hashes pin what was current at freeze time. Per R8's exception, M5
additionally reads canonical §32 in full (already satisfied by row 1 above, not the §10.1 summary).

**Live-state addendum, not part of the hashed annex (volatile, §6.5):**

```
curl -I https://demo.skyeagle.uk/  — 2026-08-20T04:50:46Z
HTTP/1.1 302 Found · Location: interface/login/login.php?site=default
Server: cloudflare · Strict-Transport-Security: max-age=15552000
X-Content-Type-Options: nosniff · cf-cache-status: DYNAMIC
TLS verify result: 0 (valid chain) · 0.255s
```

---

## 6 · BRIEFS ISSUED

Done. M6, M4 and M5 each received the identical frozen annex (§5) and their §8.4/8.5/8.6 briefs
verbatim, issued silently (R2). M4's second brief (the attack) issued after M6's publication, per
§14 sequence. **Defect carried from §4:** the underlying `CommitteeSystem.md` text moved between
briefs being issued and being read by later agents — not a briefing-issuance failure by M0, but the
reason R8's guarantee did not hold in practice.

---

## 7 · INDEPENDENT ANSWERS

M6's 16 layer/rule verdicts (`M6-architecture-audit.md` Part 1, IDs M6-L01–L13/S01–S03) and M4's
independent answer to "does this stack serve the locked IA and the funnel?"
(`M4-architecture-and-conversion.md`, 23 findings) published simultaneously, verbatim, unedited
(R2) — neither saw the other before publication. **Not restated here** (§6.6 control 5); cited by
ID throughout §9–§11.

---

## 8 · ADVERSARIAL ROUND

M4's attack on M6 (`M4-attack-on-M6.md`, 26 findings, dispatched after M6's publication per §2/§14):
**11 SUSTAINED · 8 WEAKENED · 21 HOLDS** against M6's 40 findings; 16 change-my-mind conditions
tested, 7 sound / 7 defective (six of the seven defective attached to APPROVE verdicts — a
self-adversarial-device weakness M4 names, not one M4 manufactured). M5's terminal C-4 review
(`M5-compliance-review-task2.md`): **3 BLOCK (M5-B01, M5-B02, M5-B03) · 5 REQUIRED CHANGE (M5-04,
M5-05, M5-06, M5-08, M5-09) · 3 PASS (M5-07, M5-10, GTM §14 check)**, 14,854 chars, within its
15,000 cap. One instrument-level ruling: M5 sustains M4A-07 — M6's `BLOCK` on M6-L10b exceeded
M6's own verdict enum (§8.6: APPROVE / APPROVE WITH CHANGES / REJECT AND REPLACE only; the veto is
M5's alone, §3.4) — restated `REJECT AND REPLACE`, `STATUS: OPEN — OWNER DECISION`; M5 then issues
its own **BLOCK** on the same substance, M5-B02, on the merits.

---

## 9 · CONVERGENCE & DIVERGENCE

**Two live divergences, recorded as divergence, not smoothed (R4):**

**(a) M4A-08 vs M6-O2.** M6 ranked "no form at MVP; mailto + phone" cheapest for the lead-data
question, priced at $0/deferred-P0. M4 (M4A-08) sustained: the option fails STEP0-001's D-a,
returning the design to Option N, which the Owner explicitly rejected — not a priced trade-off but
a locked-decision breach mispriced as free. **M5 (M5-B01) sustained M4 and extended it on a
claim-side basis neither M4 nor M6 named:** form field 4 is the *only* mechanism delivering §40 row
7's invoicing boundary before a billing screen; §32 items 4/12/13 make its absence a live exposure,
not just an IA gap. M5 reclassifies M6-O2 option 1 open→constraint-determined and **BLOCKs** it.

**(b) M4A-07 vs M6's BLOCK instrument.** M4 attacked the *token* M6 used (BLOCK, outside M6's §8.6
verdict enum) while endorsing the underlying finding. **M5 sustained the instrument objection**
(restating M6-L10b to REJECT AND REPLACE / OPEN — OWNER DECISION) **and separately issued its own
BLOCK on the merits** (M5-B02) — a structural finding correctly caught by M6, correctly stopped
only by M5.

**Convergence caveat, recorded against M0 (chair conduct, not an agent finding):** §13.6 rows 3 and
7 (`orders-results/`, "solo practice") were flagged by three files as lacking a governing §32/locked
ID. **M4A-17, sustained by M5**, established this is **pointer-directed agreement, not independent
convergence** — M0's own opening classification (§2, items 12g/12h) named exactly these two rows
before M6 or M4 looked. The only genuinely independent result in that area is M6's own (row 1's §32
item mis-citation, corrected by M5-04). Recorded here as R4 requires — real signal discounted to
what it actually is, not inflated.

---

## 10 · RESOLUTION

**The Owner's two questions, answered directly:**

**(a) Is this the best-fit architecture for the marketing site? Mostly yes, with one load-bearing
justification wrong.** 13 of 16 layers APPROVE or APPROVE WITH CHANGES; the CMS-adoption trigger
(L09) REJECT AND REPLACE on authority grounds (a non-technical editor could bypass claim review —
LOCKED, C-4/RDY-0003); Next.js's stated reason (built-in i18n routing) is factually false per M6's
own audit (M6-L01/M6-V07, HOLDS under attack) — the framework choice survives on other grounds
(growth path, single-vendor surface) but the document's own reasoning does not.

**(b) Can it be deployed and operated correctly on Vercel? Yes for pages, no for the lead form, as
ruled.** Static-page delivery: APPROVE WITH CHANGES under five conditions (M6-L10a — static export,
Deployment Protection, no Cloudflare proxy in front, headers/redirects via `vercel.json`, form
handled off-platform). Lead-form data: **BLOCKED** (M5-B02) — Vercel has no Saudi compute region
(nearest `dxb1` Dubai; default `iad1` Washington D.C.), TLS terminates in the compute region on
every request, no configuration changes this (M6-L10b, HOLDS under attack, M4A-09). Clears on one
written Owner/legal ruling on PDPL Art. 29 lead-data residency (M6-Q01) — a legal call, not a
technical one. **Preview deploys additionally BLOCKED** (M5-B03) until the custom-preview-domain
exposure gap closes (`staging.skyeagle.uk` is not `noindex`-protected by default).

**Open, Owner decision required, not resolved here:** lead-data residency ruling (clears M5-B02,
M6-Q01); STEP0-001 D-a status re the deferred cheapest lead-capture option (clears M5-B01); §13.8
Q2 branding (M6-Q02, cost-of-deciding-late only, no opinion offered); the budget directive's own
Owner authorship (flagged, not verified — §4).

---

## 11 · EVALUATION

**E1 — per firing agent against its own §8 acceptance:**
- **M6** — met §8.6 (16/16 verdicts + change-my-mind conditions, ≥1 genuine weakness ×3, all 11
  gate items, built-vs-proposed distinguished, §13.8 addressed). Self-count error (38 vs actual 40,
  M5-08) understates its own compliance; not a coverage gap.
- **M4** — met its narrow question and its attack mandate (twelve SUSTAINED; M4A-08 and M4A-03
  named strongest). Withdrew one prepared attack on discovering the charter had moved (M4A-01),
  re-aimed it at the dispatch — the correct call, not scored against M4.
- **M5** — met §8.5 (all six checks run, coverage shown, 3 BLOCK ≤10 cap). Went beyond delta review
  once, on the merits (M5-B01), where the claim-side consequence was undiscovered by either
  generator — within its adversarial mandate, not scope creep.

**E2 — balance:** firing set was right; M4's inform-then-attack split worked as designed, reaching
complementary conclusions. **Revisiting the M7-idle `PROPOSED` amendment: withdrawn, not
escalated.** M5-05 found two mandatory qualifications (MC-15, MC-19/20/25) have **no page in either
language** — §13.5 omits the WEB-002 nodes that would carry them. This is upstream of any Arabic
equal-prominence question: content must exist before localisation-parity can be checked against it.
The gap is M4's (IA restoration) first; an M7 consultation is premature until it closes.

**E3 — rules:** R2/R3/R4/R7 worked as designed. **R8 failed** — see §4. §6.3 and §6.4 carry the
named coverage gaps.

**Proposed amendments, `PROPOSED — Owner decision required`, not enacted (R11):**
```
RULE      §6.3 canonical controls: hashes §32, GTM §14, §40, Architecture.md.
OBSERVED  CommitteeSystem.md, the document every role's brief is drawn from,
          was not in the hashed set and moved twice mid-dispatch undetected.
PROBLEM   R8's frozen-briefing guarantee has no check on the one file all
          agents' briefs come from.
PROPOSED  Add CommitteeSystem.md (full-file hash) to §6.3's canonical-controls
          row; freeze at dispatch open; a mid-dispatch mismatch halts new
          agent starts until M0 re-freezes and logs the delta.
COST      One more hash to compute and compare per dispatch; negligible.
```
```
RULE      §6.4 write-isolation diff: greps post-dispatch porcelain for new
          lines outside committee/.
OBSERVED  01-governance/ is untracked (`??`); an in-place edit inside an
          already-untracked directory produces no new porcelain line.
PROBLEM   §6.4 cannot detect modification of untracked governed files —
          exactly what happened here. Defect in the control, not its run.
PROPOSED  Track CommitteeSystem.md's hash across the dispatch window; diff
          the hash, not only `git status --porcelain`.
COST      One additional hash-diff step; no new dependency.
```

**Self-errors, against M0, this closure:** (1) earlier framing that D3b required an Owner exception
— superseded by the runtime-neutral amendment, withdrawn. (2) §13.6 rows 3/7 "convergence": M0's
own opening classification pointer-directed the later agents (M4A-17, sustained by M5) — against
the chair, not the agents who read the pack as instructed. (3) The opening-stage `git remote -v`
run — already recorded, restated here per R6 (never edited away), not re-litigated.

---

## 12 · META-REVIEW

**COMPLETE — R12 satisfied. Recorded by the Orchestrator; not written or answered by M0.**

M5's single meta-review of §11 is at `M5-compliance-review-task2.md`, appended section
`META-REVIEW OF M0 §11 (R12)` (4,997 chars of a 5,000 cap). **Five of seven tests ACCEPTED**,
including that M6 was correctly not penalised for the stale-pointer dispatch defect, that all three
of M0's self-recorded errors are present and unsoftened, that both amendments carry the five §7.4
fields as `PROPOSED — Owner decision required` without being enacted (R11), that §9's two
divergences were preserved with no invented middle option (R4), and that the status claim is
truthful. M5 accepted M0's withdrawal of the M7 proposal as correctly reasoned.

**Two objective errors — M0 may correct these, and only these (R12):**

| # | Error | Correction |
|---|---|---|
| **C1** | §11 E1's "16/16 verdicts" and §10's "13 of 16 layers" | M6 §1.1 carries **18** rows: **15 of 18** APPROVE / APPROVE WITH CHANGES, 3 not. Propagates M6's own slip (already M5-08). Substance unaffected |
| **C2** | §10 states only the `noindex` exposure as M5-B03's clearing condition | M5-B03 carries **two**: the second is the review gate restated to cover *deployment* rather than merge — the actual finding |

**Two substantive disagreements — routed to the Owner, unanswered. M0 does not get to reply (R12):**

- **D1 — E1 did not grade M4 against its own acceptance criteria.** §8.4's two `LOCKED` elements
  ("no prohibited page appears"; "the disqualification path is preserved and instrumented") went
  ungraded; only the C-2 attack clause was scored, and §7.1 field counts were carried for M6 alone.
  M4 met both criteria — the defect is uneven application of §7.4's "quote the criterion and the
  evidence", not a bad outcome.
- **D2 — E2 omits a mandated criterion that has a real answer.** §7.4 E2 requires *"was there a
  question no agent owned"*; it is unanswered, and there was one. **§13.8 Q2 (Thiqa vs SkyEagle) is
  positioning**, M2 and M3 were idle, M6-Q02 offered only cost-of-deciding-late, and §10 records "no
  opinion offered" — against §14's requirement that all four §13.8 questions be addressed. **GA4's
  own residency** is a second unowned question.

**M5 recorded no manufactured objection**; both disagreements cite fixed §7.4 criteria with evidence,
consistent with §7.4's rule that coverage, not objection count, measures adversary performance.

---

**End of record for `COM-20260820-001`. Pack is FINAL.** Twelve of twelve §7.2 stages complete.
Live for Owner decision: the PDPL lead-data residency ruling (clears M5-B02), the STEP0-001 D-a
status (clears M5-B01), the preview-exposure fix (clears M5-B03), §13.8 Q2 branding (D2 above), the
two proposed §6.3/§6.4 amendments, and confirmation of the budget directive's authorship.
