# M5 · CLAIM COMPLIANCE & EVIDENCE — TASK 2 TERMINAL REVIEW (C-4)

`COM-20260820-001` · C-2, terminal gate C-4 · §8.5 veto. **Budget** 15,000 chars, ≤10 BLOCK rows,
**delta review only** (`COM-20260820-001-budget-directive.md:15-16`): I cite finding IDs, repeat no
evidence, re-run no audit. **§8.5: I generate nothing.** **Scope (§14):** one question — *does any
architectural choice create a claim risk?* Whether Next.js is right is M6's and M4's lane. Other
agents' `CommitteeSystem.md` cites are stale ~95 lines (amended twice mid-dispatch — a dispatch
defect, not theirs); every line below was re-resolved today.

## 1 · COVERAGE — six mandated checks, each with a result

| # | Check (§8.5) | Result |
|---|---|---|
| 1 | **§32, 30 rows, canonical** `Marketing-MVP…md:12022-12071` read in full (R8), each row tested against Architecture.md §13.1-13.9 and all three files | **13 engaged, 17 not engaged** — §2 |
| 2 | **GTM §14** `Product-Positioning…md:597-646`; every claim implied by an architectural choice traced to MC-01…MC-25 | **PASS + 2 changes** — M5-05, M5-06 |
| 3 | **`HISModulesUsers.md`** — capabilities implied by §13.7's launcher and §13.6's flagships | **PASS.** CAP-0224 Active/Ready, "200/200 checksums matched" (`:708`); Arabic 47.5% chrome-only (`:156`) = Architecture.md:103. **None implied above status** |
| 4 | **Locked decisions** — STEP0-001, WEB-001/002, ICP-001, COMP-001, PRC-003, RDY-0064 | **ONE BREACH** — M5-B01 |
| 5 | **§7.1 nine fields** — `grep -c "^ID:"` etc. for each of the nine field names, per file | **PASS.** M6 **40/40**, M4 23/23, attack 26/26 — zero gaps. M6's *self-count* is wrong (M5-08) |
| 6 | **D-a…D-d** (`CommitteeSystem.md:1214-1221`) on every deliverable touching demo access | **D-a BREACHED** by M6-O2 opt 1 (M5-B01); **D-b/c/d PASS** (M5-10) |

**Instrument ruling — M4A-07 SUSTAINED.** §8.6 gives M6 APPROVE / APPROVE WITH CHANGES / REJECT AND
REPLACE; §3.4 gives the veto to **M5 alone** (`CommitteeSystem.md:154-156`), and §12's *"M5 returns
zero unresolved BLOCKs"* cannot account for a BLOCK originating elsewhere. **M6-L10b's verdict stands
as REJECT AND REPLACE; its STATUS corrects to `OPEN — OWNER DECISION`.** Merits: M5-B02.

## 2 · §32 SWEEP

**Engaged (13):** 3, 4, 5, 8, 12, 13, 14, 15, 18, 23, 25, 26, 30. **Checked, not engaged (17):** 1, 2,
6, 7, 9-11, 16, 17, 19-22, 24, 27-29 — Architecture.md carries no customer copy, so the vocabulary and
capability-wording rows have no surface in it.

**Item 5 — PASS, with a distinction the record must keep.** Item 5 prohibits *analytics as a product
claim*; GA4 as the project's own site instrumentation is not one, so item 5 does not reach it (its
residency is separate — M5-07). **M4-03 confirmed independently:** I re-checked all 17 routes and 13
components against the *"Pages that must not exist"* list (`:12066-12069`). **Zero smuggling — PASS.**

## 3 · RULINGS ON THE SURFACED COLLISIONS

### M5-B01 · **BLOCK** — M6-O2 option 1 breaches STEP0-001 D-a

```
ID:          M5-B01
TYPE:        OBSERVED
STATEMENT:   M4A-08 SUSTAINED, and I go further. M6-O2 ranks "no form at MVP; publish a mailto:
             and a phone number" cheapest at $0/0 days, pricing it as a deferred P0. That is
             not its cost. D-a: "Access is issued **only** after a qualifying form"; §13.2: "If
             any condition fails, it is Reading A and **all four decisions must be reopened in
             writing**." No form is the stronger failure, not an exemption.
             **The claim consequence — my lane, named by neither agent:** form
             field 4 ("Do you need this system to issue your tax invoice or submit insurance
             claims?") is the ONLY mechanism delivering §40 row 7's invoicing boundary before a
             visitor reaches a billing screen. §32 items 4, 12 and 13 make invoicing, ZATCA/VAT
             and claims/eligibility prohibited; deleting the form leaves that boundary with no
             delivery mechanism. **I reclassify M6-O2 option 1 from GENUINELY OPEN to
             CONSTRAINT-DETERMINED, unilaterally, under §8.5's R1 power** — a locked decision
             governs it, so ranking it is a §4.1 failure.
BASIS:       CommitteeSystem.md:1218 (D-a), :1214, :1230, :1234; Marketing-MVP…md:12030, 12038,
             12039; M6-O2 opt 1; M4A-08.
FALSIFIER:   A written Owner reopening of STEP0-001, or a mechanism other than the form putting
             the invoicing boundary in front of the visitor before access is issued.
CONFIDENCE:  High — every element is quoted charter or canonical §32 text.
AUTHORITY:   LOCKED — STEP0-001 conditionality; §32 items 4/12/13.
IMPACT IF WRONG: Severe. A $0 option delivers a rejected access design plus a §32 delivery gap
             as a side effect of a hosting choice — R-02's exact shape.
STATUS:      **BLOCK** — option 1 withdrawn, or STEP0-001 reopened in writing first. Per §8.5 I
             name the defect and stop; no reopening or replacement proposed.
```

### M5-B02 · **BLOCK** — lead-data hosting (instrument ruled in §1; merits here)

```
ID:          M5-B02
TYPE:        OBSERVED
STATEMENT:   No lead-capture form may ship to any host — Vercel function, third-party service or
             the Dammam instance — until §13.3 item 11's residency question is ruled in writing.
             Item 11 is a gate, not a default: "Do not send identifiable lead data to an
             unreviewed global form or analytics service." It already closes M6-O2 option 4 and
             conditions option 3 on a completed review. M6's facts survive and M4A-09 confirms
             the split does not collapse. What is missing is a ruling; until it exists the
             correct state is stopped, not ranked.
BASIS:       CommitteeSystem.md:1276-1279; M6-L10b, M6-V03, M6-Q01, M4A-07/09.
FALSIFIER:   A written Owner or legal ruling on lead-data residency under PDPL.
CONFIDENCE:  High on the gate; none offered on what is lawful — not my ruling.
AUTHORITY:   POLICY — §13.3 item 11, mandatory gate item.
IMPACT IF WRONG: Severe — identifiable Saudi clinic-owner data under an unruled regime.
STATUS:      **BLOCK**, clearing on the written ruling. Nothing else is held: static pages, the
             demo launcher and the bilingual routes proceed.
```

### M5-B03 · **BLOCK** — preview exposure precedes its own review gate

```
ID:          M5-B03
TYPE:        INFERRED
STATEMENT:   §13.8 Q4 answers publication control with "the claim review (EV-003) has to sit
             *before* merge, not after deploy." **On Vercel that is structurally inverted, so
             EV-003 is not sufficient.** A preview deploys from a *branch* — before merge — so
             exposure happens on the unreviewed side of the gate, for exactly the content class
             the gate exists to control. M6-V05(b) makes it consequential: Vercel omits
             `X-Robots-Tag: noindex` on **custom** preview domains, and `staging.skyeagle.uk`
             (Architecture.md:52) is that case. §10.1 binds §32 to every surface in every
             language, and an indexed, cached, unapproved claim is outside the project's
             control. M6-L13(ii) confirmed on the same facts.
BASIS:       Architecture.md:52, 198, 200-205; M6-V05(b); M6-L13(ii); CommitteeSystem.md:942.
FALSIFIER:   A preview mechanism unreachable before merge, or a claim review gating deployment.
CONFIDENCE:  High — branch-before-merge is a property of the platform, not a habit.
AUTHORITY:   LOCKED (§32 binds every surface) · POLICY (EV-003).
IMPACT IF WRONG: High and irreversible once cached; M6-V05 records the close as free.
STATUS:      **BLOCK** on creating any custom preview domain until the exposure is closed and
             the review gate restated to cover deployment, not merge alone. M6-V05's REQUIRED
             CHANGE is the technical half; the gate restatement is the compliance half.
```

### M5-04 · §13.6 — all seven rows ruled, with the governing ID or none

| # | Row | Governing ID **I** find · result |
|---|---|---|
| 1 | `billing/` | **§32 items 12 + 13** and **GTM §14.4** (`:643`, "US-only formats … keep off the Saudi site entirely"). **Item 4 does not govern it** — it is GL/ERP/AP/payroll, not patient billing. **CONFIRMED · citation corrected** |
| 2 | `insurance/` | **Item 12** verbatim (R-02), independently `:12066`. **CONFIRMED as cited** |
| 3 | `orders-results/` sub-section only | **NONE.** No §32 row, no locked decision: item 3 prohibits the *term* LIS, not a page, and **MC-21 expressly permits the claim** (`:637`). **CHALLENGE SUSTAINED** |
| 4 | `Testimonial` | **Item 25** verbatim ("No such section may exist") + `:12068`. **CONFIRMED as cited** |
| 5 | `ComparisonTable` deferred | **COMP-001 + item 26.** **Add item 15** — "zero fork divergence" confines the permitted self-installed-OpenEMR comparison to the *service*, never the software. **CONFIRMED · 1 citation to add** |
| 6 | Pricing: model only | **PRC-003 BLOCKED** (`Product-Positioning…md:682, 693`). **Add item 30**, which governs that page's copy. **CONFIRMED · 1 citation to add** |
| 7 | "Solo practice" drop | **ICP-001** (`Product-Positioning…md:178, 1216`, LOCKED FOR MVP) — **not §32.** M4-22 stands: this does not authorise dropping WEB-002's locked **"Single clinic"** node. **CONFIRMED on a different authority than implied** |

**The finding that follows is mine.** §13.9 says *"§13.6's corrections are binding regardless of stack,
**because they come from §32**."* **False for rows 3 and 7** — row 7 comes from ICP-001, row 3 from
nothing. Under §4.2 an unsourced editorial preference inside a table declared binding is how a
preference acquires `LOCKED` authority. **REQUIRED CHANGE:** row 3 re-tagged `HYPOTHESIS` and removed
from the binding set; row 7 re-cited to ICP-001; §13.9's blanket sentence qualified. **Row 3's merits,
my lane only:** MC-21's qualification *leads* and is long, so a sub-section has less room to carry it —
the demotion may raise claim risk and nothing requires it. What to build is M4's lane.

**M4A-17 SUSTAINED.** Rows 3 and 7 are **pointer-directed agreement** (M0 pack items 12g/12h named both
before either agent looked); row 1 is the independent Part-5 result.

### M5-05 · **PASS WITH REQUIRED CHANGE** — two qualifications have no page

M4-02 §2.2 rows 2 and 5 sustained; the consequence exceeds an IA gap. **"Two or three locations"**
carries MC-15, whose qualification — *"A separate database per site, provisioned manually. **Not
multi-tenant SaaS**"* (`GTM:618`) — keeps the multi-site claim clear of **§32 item 8** (`:12036`).
**"Optional, switched off by default"** carries MC-19/20/25, whose qualification *is* that phrase
(`GTM:626, 627, 632`). Both nodes sit in WEB-002 `LOCKED FOR MVP` (`Product-Positioning…md:770, 837`)
and §13.5 omits both. A published claim whose §32-governing qualification has no home is a live
exposure. **Reclassified open → constraint-determined (R1, §8.5):** restoring a locked-IA node is not a
design question. `whats-included/` is a register of figures, a different node — §17.2 lists both.
Which surface carries them is M4's lane; I propose none.

### M5-06 · **PASS WITH REQUIRED CHANGE** — `QualifiedClaim` is not an adequate R-02 control

M4-07's eleven bypasses and M4-08's mis-designation are sustained, not restated. **The defect I add is
in my lane:** GTM §14.2's column is *"**Leading** qualification"* — for all ten §14.2 claims the
limitation must **lead**, not merely sit adjacent. A component rendering claim-then-qualification
satisfies §32's same-visual-unit rule and still fails §14.2's ordering rule, so even correct universal
use would not discharge the requirement. **REQUIRED CHANGE:** Architecture.md:142-144's claim that the
component "is what stops it eroding over time" is withdrawn or re-tagged `HYPOTHESIS` (§4.2); no §32
adjacency control may be recorded as *in place* until a check exists that fails a build or a merge.
**I propose no mechanism** — M4's lane.

### M5-07 · **PASS (closed)** — analytics: M6-Q03 / M6-L12(a) confirmed

§13.3 item 9 (`CommitteeSystem.md:1272-1273`) is a mandatory gate, not a default; **I confirm the
reclassification to CONSTRAINT-DETERMINED and the closure.** Architecture.md:51 complies; :197's
"default answer: no" framing is stale — **REQUIRED CHANGE** to cite the gate. GA4's own residency
(M6-L12(b)) stays **genuinely open**: item 11 reaches identifiable *lead* data, which GA4 does not
receive.

---

## 4 · SECONDARY RESULTS

| ID | Statement | Basis | Status |
|---|---|---|---|
| M5-08 | M6's §7.1 self-count of **38** is wrong — Part 1 is 18 blocks (L01-L09, L10a/b/c, L11-L13, S01-S03), recorded as 16; actual **40**, all nine fields present, so compliance is *better* than claimed. §6.6 control 10 wants the number accurate | `M6-architecture-audit.md:1685-1697` | **REQUIRED CHANGE**, record only |
| M5-09 | All three files record D3b as `claude-opus-5` from **agent self-description**, and `M4-attack-on-M6.md:9` asserts "Floor holds". §5 forbids inferring a model name from self-description; D3b takes the platform identifier or `NOT REPORTED BY RUNTIME`, and unverifiable parity is `PARITY NOT VERIFIABLE`. Pre-amendment files; **§5.1 makes this no quarantine trigger** | `CommitteeSystem.md:241, 258-269` | **REQUIRED CHANGE** — M0 restates all three |
| M5-10 | **D-b, D-c, D-d PASS.** §13.7 issues Front Office and Physician only, so D-c holds and **§32 item 23** (the `admin` credential, ever) is engaged by no architectural choice. M4-10 correctly flags site-wide `DemoCTA` as a D-d *hazard*; the frozen architecture does not breach D-d | `Architecture.md:174-179`; M4-10 | **PASS** |

**Deferred (P2):** M4-13 is a measurement, not a claim, defect. `not-a-fit/` and the form surface
(M4-02 §2.3) are M4's lane; their *copy* returns to C-4.

## 5 · VERDICT

**3 BLOCKs · 5 REQUIRED CHANGEs · 3 PASSes** — 11 ruled items, **3 BLOCK rows against a cap of 10**.
Each clears on one named act: withdraw one option or reopen STEP0-001 in writing (M5-B01); a written
residency ruling (M5-B02); one header rule plus a restated review gate (M5-B03). **Nothing else is
held** — §13.1's three-environment rule, §13.7's launcher, §13.4's bilingual routing and §13.2's
MDX-in-Git create **no claim risk**, a genuine PASS rather than an absence of effort.

**Runtime:** `NOT REPORTED BY RUNTIME` — the platform reports no model identifier for a subagent, and
§5 forbids substituting a self-description. **Characters:** **14,854 of the 15,000 cap** (`wc -m`;
more bytes by `wc -c` — §6.6 makes characters the enforceable unit); no increase requested.
**Coverage:** all six §8.5 checks run against all 30 §32 rows, 25 GTM §14 claims, seven §13.6
rulings, four §13.8 questions and 89 findings across the three deliverables; every check carries a
recorded result and no item is untested.

---

# META-REVIEW OF M0 §11 (R12)

**Scope.** M0's `11 · EVALUATION` only, with §9/§10 as context for fidelity. My terminal review above
is not reopened; the architecture, M4 and M6 are not re-audited. One pass, then FINAL. **Separate
budgets:** the review above is 14,854 of 15,000; this section is capped at 5,000.

| # | Test | Result |
|---|---|---|
| 1 | E1 honest per agent | **ACCEPTED**, with **C1**, **D1** |
| 2 | E2 balance real | withdrawal **ACCEPTED**; criterion gap **D2** |
| 3 | M0's own failures recorded | **ACCEPTED** |
| 4 | Amendments five-field, not enacted | **ACCEPTED** |
| 5 | §9 divergence preserved (R4) | **ACCEPTED** |
| 6 | §10 answers both questions; BLOCKs attributed | **ACCEPTED**, with **C2** |
| 7 | Status claim truthful | **ACCEPTED** |

**1.** M6 is **not** penalised for stale pointers — §4 states "Do not score M6 or M4 down for stale
pointers" and E1 raises only the self-count, correctly called "not a coverage gap". Right call, and
I record it as sound. M6's and M5's gradings enumerate their real §8.6 / §8.5 criteria verifiably.

**3.** All three self-errors are present and unsoftened: the `git remote -v` breach (restated, not
edited away, R6); the pointer-directed §13.6 rows 3/7 convergence, stated "against the chair, not the
agents who read the pack as instructed"; the withdrawn D3b/Owner-exception framing, superseded in
§4's closure addendum. §8.0 satisfied.

**4.** Both amendments carry RULE / OBSERVED / PROBLEM / PROPOSED / COST under `PROPOSED — Owner
decision required, not enacted (R11)`, and each addresses its named failure — the R8 gap (the charter
was never in §6.3's hashed set) and the §6.4 blind spot (an in-place edit inside an already-untracked
directory produces no porcelain line). **M0 proposed; it did not enact.**

**5.** Both divergences stay recognisable as disagreements — (a) sets M6's ranking beside M4's
locked-decision objection with no middle option invented; (b) keeps the instrument/merits split. The
convergence caveat *discounts* signal rather than inflating it. No smoothing.

**7.** `STATUS: IN PROGRESS`; terminal named `FINAL — PRE-POLICY OVERAGE DISCLOSED`; "Not `COMMITTEE
COMPLETE`; not `STANDARD BUDGET PASS`" at the pack head; "**Budget checker: FAIL, disclosed,
pre-policy … Not budget-compliant**" verbatim, not characterised. Truthful.

## Objective errors — M0 may correct these

**C1 · TRANSCRIPTION ERROR — layer count.** E1 credits M6 with "16/16 verdicts"; §10 says "13 of 16
layers APPROVE or APPROVE WITH CHANGES". M6's §1.1 table carries **18** rows (L01-L09, L10a/b/c,
L11-L13, S01-S03), of which **15** are APPROVE or APPROVE WITH CHANGES and 3 are not (L09, L10b,
L10c). Correct: **18/18** and **15 of 18**. This propagates M6's own slip, already corrected at
M5-08; the substance — three non-approvals — is unaffected. Re-runnable:
`sed -n '150,170p' M6-architecture-audit.md | grep -c "^| L"` → 18.

**C2 · CITATION ERROR — M5-B03's clearing condition.** §10 states the preview BLOCK clears when the
custom-preview-domain exposure closes. My STATUS carries **two** conditions: that exposure closes
**and** the review gate is restated to cover *deployment*, not merge alone. The second is the actual
finding — EV-003 sits after the exposure it exists to prevent. As written, §10 reads as though
M6-V05's free header fix alone clears it.

## Substantive disagreements — recorded, routed to the Owner; M0 does not answer

**D1 · E1 did not grade M4 against its own acceptance.** §7.4 E1 requires the criterion **quoted**
with its evidence. §8.4's acceptance is "the funnel answers Challenges 1 and 4 concretely; **no
prohibited page appears**; **the disqualification path is preserved and instrumented**", plus the C-2
attack clause. E1 grades only the last — "met its narrow question and its attack mandate". The two
`LOCKED` elements go ungraded, though M4 met both (M4-03; M4-14/M4-23). E1 also counts §7.1 field
compliance for M6 alone; the others (23/23, 26/26, my check 5) are not carried. The grading is not
wrong; the method §7.4 fixes was not applied evenly.

**D2 · E2 omits a mandated criterion that has a real answer.** §7.4 E2 asks, among fixed items,
"**was there a question no agent owned**", and where two agents' boundary moves. E2 answers the firing
set and the M7 question, then stops. There **was** such a question: **§13.8 Q2** (Thiqa or SkyEagle) is
positioning, M2 and M3 were idle, M6-Q02 offered only cost-of-deciding-late, and §10 records "no
opinion offered" — yet §14's acceptance required all four §13.8 questions addressed. **GA4's own
residency** (M6-L12(b), my M5-07) is a second: technical for M6, not a claim for M5, legal for
neither. Both belong in E2.

**On E2's withdrawal, separately: ACCEPTED.** M5-05 finds MC-15 and MC-19/20/25 have no page in
*either* language, so the defect is missing content, not asymmetric content, and an equal-prominence
check has nothing to run against until the WEB-002 nodes return. Premature is the right word, and it
is M4's lane first.

**No other objection** — coverage, not objection count (§7.4).

**Runtime:** `NOT REPORTED BY RUNTIME`. **Characters, this section:** 4,9xx of 5,000 (`wc -m` from
the `# META-REVIEW` heading).
