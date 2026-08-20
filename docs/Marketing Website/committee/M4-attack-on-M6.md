# M4 — ADVERSARIAL ATTACK ON M6's ARCHITECTURE AUDIT

**Dispatch:** `COM-20260820-001` · TASK 2 · Committee **C-2** · **Adversarial round (§7.2 stage 7)**
**Agent:** M4, acting under §2's adversary-on-adversary rule — *"M4 attacks M6 … briefed adversarially
against them — 'find why this audit is wrong'"* (`CommitteeSystem.md:95`, and §7.5's control for
**rubber-stamp review**, `CommitteeSystem.md:613`).
**Object under attack:** `docs/Marketing Website/committee/M6-architecture-audit.md`, 1,729 lines,
38 findings.
**Parity:** M6 self-declared `claude-opus-5`; this agent declares `claude-opus-5`. Floor holds; the
attacker is not below what it attacks.
**Date:** 2026-08-20.
**Source state declared (see M4A-01):** every `CommitteeSystem.md:<line>` pointer below is resolved
against that file as it stands at **mtime 2026-08-20 05:36:50 UTC, 1,566 lines** — the file moved
during this dispatch and line numbers from earlier agents will not match. `Architecture.md` is at the
pinned SHA-256 `938ff387…`, re-verified; the readiness register (2026-08-19 23:34:57) and the GTM
(2026-08-10 23:47:10) have not moved at all.

**Write isolation (R9):** this agent wrote exactly one file — this one. **No git command was run.**
M6's file was read and **not edited**.

---

## 0 · METHOD, AND A DISCLOSURE MADE BEFORE THE ATTACKS

**Method.** I did not accept a single one of M6's empirical claims. Every header, DNS record,
`robots.txt`, cookie flag and `file:line` pointer that carries weight in M6's audit was **re-run by
me**, from this host, on 2026-08-20 between 05:24 and 05:32 UTC. Where M6 was right I say so and name
the attack that failed. Where the pointer did not resolve I say that too, and I distinguish
**wrong-pointer** from **wrong-fact**, because they have different consequences and collapsing them
would be its own dishonesty.

**Verdict vocabulary, per the dispatch:**

- **SUSTAINED** — M6 is wrong, with the evidence.
- **WEAKENED** — M6's conclusion may hold; its stated reasoning does not support it.
- **HOLDS** — attacked and survived. The attack I ran is named, and why it failed.

**A withdrawn attack, disclosed first because it was to have been the heaviest finding.** I prepared
a SUSTAINED attack on M6's `file:line` discipline: its `CommitteeSystem.md` pointers do not resolve,
and its own §8.3 certifies that they do. Before shipping it I checked whether *my own* round-one
pointers still resolved — I had already found and corrected the same offset in a pre-submission pass —
and they no longer did either. **The file had moved again, while this attack was being written.**
`CommitteeSystem.md` is at **1,566 lines / mtime 05:36:50 UTC**, having been **1,471** earlier in
this same session, and its mtime is later than M6's audit, my round-one file and M0's pack alike.
Every other frozen source is untouched. So the offsets in M6's audit are evidence about the
**dispatch**, not about M6, and the attack is withdrawn against M6 and re-targeted (M4A-01).
I record the reversal rather than deleting the attack, because R6 keeps dissent and correction on the
record and because an adversary that quietly drops a wrong accusation is not more trustworthy than one
that never checked. **What survives as a genuine M6 citation defect is M4A-02**, which sits in files
that have not moved since 2026-08-19 and 2026-08-10.

**A consequence I must state and cannot fix in this dispatch.** My own round-one file,
`committee/M4-architecture-and-conversion.md`, carries ~40 `CommitteeSystem.md` pointers resolved
against the *earlier* state. They are now stale by the same ~95 lines. **I am instructed to write only
this file and not to edit my round-one deliverable, and R9 bars me from doing so anyway**, so I flag
it here for M0 rather than silently correcting it. Every citation **in this file** is resolved against
the 05:36:50 / 1,566-line state and declared as such at the head.

**Convergence handled explicitly (R4, `CommitteeSystem.md:193`).** I reached several of M6's
conclusions independently in round one. Those are the dangerous ones and I attack them hardest. §5
audits the convergence itself and finds that in the one place where three agents agreed, the agreement
was **not independent** — all three read the same pointer.

---

## 1 · SUMMARY — EVERY M6 VERDICT ENGAGED

Every one of M6's 16 layer/rule verdicts, its 10 Vercel findings, its 3 briefing-pack challenges, its
2 built-half findings, its 4 open-question answers and its 3 option sets is engaged below or in §2/§4.

| M6 finding | M6's verdict | My result | Attack |
|---|---|---|---|
| M6-R01 PDPL grep | REQUIRED CHANGE | **HOLDS** | Re-ran; 4 matches, `Q45` real. I found the same in round one |
| M6-R02 RDY-0064 evidence quality | OPEN | **HOLDS** | Both quotations verified verbatim at :1083 and :9635 |
| M6-R03 iframe reclassification | REQUIRED CHANGE | **WEAKENED** | M4A-11 — one URL tested; headers absent on two other responses |
| M6-L01 Framework | APPROVE WITH CHANGES | **SUSTAINED (condition) · WEAKENED (basis)** | M4A-03, M4A-05, M4A-06 |
| M6-L02 TypeScript | APPROVE | **WEAKENED** | M4A-18b — a claim-compliance argument made in M5's lane |
| M6-L03 React | APPROVE (weak basis) | **HOLDS** | Honest self-marking; attacked as a non-condition, see §2 |
| M6-L04 Tailwind/tokens | APPROVE WITH CHANGES | **HOLDS** | Token file verified, 2,706 bytes; condition self-cancelling (§2) |
| M6-L05 Components | APPROVE WITH CHANGES | **HOLDS (a),(b) · SUSTAINED on citation** | M4A-02 |
| M6-L06 EN+AR | APPROVE | **SUSTAINED (condition)** | M4A-23 |
| M6-L07 RTL | APPROVE WITH CHANGES | **HOLDS** | Best-covered layer in the audit; condition (i) defective (§2) |
| M6-L08 MDX-in-Git | APPROVE | **HOLDS** | I reached the same independently; §5 tests whether that is signal |
| M6-L09 CMS trigger | REJECT AND REPLACE | **SUSTAINED on authority tag** | M4A-18 |
| M6-L10a Vercel/pages | APPROVE WITH CHANGES | **WEAKENED** | M4A-04 |
| M6-L10b Vercel/lead form | **REJECT AND REPLACE / BLOCK** | **SUSTAINED on the BLOCK · HOLDS on the facts** | M4A-07 |
| M6-L10c Candidate set of one | REQUIRED CHANGE | **SUSTAINED (recursive)** | M4A-24 |
| M6-L11 GitHub | APPROVE WITH CHANGES | **SUSTAINED (condition falsified)** | M4A-19 |
| M6-L12 Analytics | APPROVE WITH CHANGES | **HOLDS** | Attacked via M4A-10's premise tension |
| M6-L13 Domains | APPROVE WITH CHANGES | **HOLDS** | NXDOMAIN ×3 independently reproduced (M4A-27) |
| M6-S01 Three environments | APPROVE, "could not find a way to attack" | **SUSTAINED (scope ambiguity)** | M4A-24b |
| M6-S02 Repo structure | APPROVE WITH CHANGES | **WEAKENED** | M4A-04, M4A-02 |
| M6-S03 Demo launcher | APPROVE, "nothing available" | **SUSTAINED** | M4A-12 |
| M6-V01 Render mode | "EVIDENCE-DETERMINED" | **WEAKENED** | M4A-04 |
| M6-V02 Region/residency | REQUIRED CHANGE | **HOLDS** | No attack succeeded; middleware point is well made |
| M6-V03 Lead-form path | OPEN, heaviest | **HOLDS on substance · WEAKENED on its legal sources** | M4A-09; its BASIS pointer is covered by M4A-01, not chargeable to M6 |
| M6-V04 Secrets | REQUIRED CHANGE | **HOLDS** | The two-independent-paths observation is the audit's best original insight |
| M6-V05 Previews | REQUIRED CHANGE | **HOLDS** | Attacked for over-claiming; it under-claimed instead |
| M6-V06 DNS/TLS | REQUIRED CHANGE | **HOLDS** | HSTS header re-verified; no `includeSubDomains` |
| M6-V07 i18n mechanics | REQUIRED CHANGE | **HOLDS** | The strongest technical finding in the audit |
| M6-V08 Logging | REQUIRED CHANGE | **WEAKENED** | M4A-10 — premise may contradict M6-V01 |
| M6-V09 Cost/lock-in/exit | REQUIRED CHANGE | **HOLDS** | (c) is genuinely good; no attack landed |
| M6-V10 / Q04 Who operates | REQUIRED CHANGE | **HOLDS with one counter-argument M6 denied existed** | M4A-19 |
| M6-Q01 §13.8 Q1 | OPEN | **HOLDS** | Correct to leave open; M4A-09 attacks its costing |
| M6-Q02 Branding | OPEN | **HOLDS** | Verified M6's claim that §13 assumes no brand |
| M6-Q03 §13.8 Q3 | ANSWERED, CLOSED | **HOLDS** | M4A-13 — three attacks run, all failed |
| M6 Part 3 gate, 11 items | 2P/1P+/3F/5O | **SUSTAINED on item 10's scope** | M4A-21 |
| M6 Part 5 §13.6, 7 rows | 5 confirmed / 2 challenged | **SUSTAINED ×3** | M4A-14, M4A-15, M4A-16 |
| M6 Part 4 built-vs-proposed | ledger | **HOLDS** | M4A-26, M4A-27 reproduced two entries |
| M6-O1 host options | 3 ranked | **SUSTAINED (coverage) · HOLDS (IA fitness)** | M4A-24, M4A-25 |
| M6-O2 lead-data options | 4 ranked | **SUSTAINED on option 1** | M4A-08 |
| M6-O3 framework options | 3 ranked | **SUSTAINED on the ordering** | M4A-06 |
| M6 §8.3 "no finding rests on recollection" | self-certified | **WEAKENED** | M4A-02 only. **M4A-01 withdrawn** — the briefing file moved during the dispatch; see M4A-01 |

**Score: 11 SUSTAINED · 8 WEAKENED · 21 HOLDS** — after M4A-01 was withdrawn against M6 and re-targeted at the dispatch. A majority of M6's audit survived attack. That is
the honest result and I state it rather than manufacturing objections to balance the table (§7.4,
`CommitteeSystem.md:592-593`: coverage, not objection count).

---

## 2 · EVERY CHANGE-MY-MIND CONDITION, TESTED

§8.6 requires one per layer (`CommitteeSystem.md:827`) and warns in the same breath that *"an agent
that has reached a verdict writes the change-my-mind condition to fit it."* The dispatch names two
failure shapes: **a condition that could never be met**, and **a condition trivially already met where
the verdict did not move.** I add a third that M6's own preamble promised to avoid and did not:
**a condition M6 states and then closes in the same paragraph.**

| Layer | The condition | Test result |
|---|---|---|
| L01 | *"Show me one page in the locked IA (§13.5's 17 routes) that cannot be produced at build time."* | **DEFECTIVE — could not be met as constructed.** The parenthesis equates the locked IA with §13.5's route list. They are not the same set: §13.5 omits 8 locked-IA nodes and both funnel routes (M4A-03). The one candidate that could falsify the condition — the booking/form surface — is excluded by the parenthesis |
| L02 | *"A team that does not know TypeScript…"* | **WEAK BUT HONEST.** Not an observation anyone can make from the repository, which M6's own preamble said it would avoid; but it names a real fact the Owner holds |
| L03 | *"If the framework verdict moves to Astro, this row disappears."* | **DEFECTIVE — not a condition.** It is a dependency note. No observation would change the L03 verdict. M6 marked the verdict "weak basis", which is the honest half |
| L04 | *"If the site is small enough and single-authored, the check is over-engineering"* — followed immediately by *"But this site is explicitly multi-locale and explicitly outlives its first author"* | **DEFECTIVE — self-cancelling.** Stated and closed in consecutive sentences |
| L05 | *"If the numbers are generated at build time from `EV-067` itself … I withdraw it."* | **SOUND.** A real, checkable observation with a stated withdrawal |
| L06 | *"if the content set stays under ~20 files and one person owns both languages, a manual checklist is defensible."* | **DEFECTIVE — arguably already met, verdict did not move.** 17 routes per locale is under 20; 34 across both is over. M6 never resolved which it meant, and required the gate either way (M4A-23) |
| L07 | (i) *"dropped if the project adopts Tailwind's logical utilities as a lint rule"* | **DEFECTIVE — implementing the recommendation is not falsifying it.** (iii)'s *"nothing short of the Owner reversing the policy"* is correct for a POLICY item |
| L08 | *"If the volume of content edits ever exceeds what one reviewer can gate…"* | **SOUND** |
| L09 | *"A CMS with hard editorial workflow genuinely satisfies the requirement."* | **SOUND, and generous to the opposing case** |
| L10a | *"If the site ever needs ISR, server-rendered personalisation or an authenticated area on the SAME deployment"* — followed by *"§13.1 forbids exactly that"* | **DEFECTIVE — self-cancelling** |
| L10b | *"A written Owner/legal ruling that lead data may lawfully be processed outside the Kingdom."* | **SOUND — and the strongest in the audit**, because M6 volunteers that it is "entirely available", i.e. names a condition it expects to be met. See M4A-07 on the *status* attached to it |
| L10c | *"A written reason why two edge vendors is preferable to one."* | **SOUND.** M6 supplies the respectable version of the counter-argument itself |
| L11 | ***"Nothing** — a required review is the cheapest control in this entire audit and there is no argument against it."* | **DEFECTIVE — and falsified.** M6's own preamble said it would mark a verdict weak rather than write an unfalsifiable condition. It did not, and an argument against does exist (M4A-19) |
| L12 | *"a ruling that GA4's data is not personal data for this purpose…"* | **SOUND** |
| L13 | *"(ii) is withdrawn if staging is documented as a preview surface"* | **PARTIAL.** Covers one of three legs; (i) NXDOMAIN and (iii) staging-indexability get no condition, and (iii) carries the REQUIRED CHANGE |
| S01 | *"A demonstrated need to share sessions or authentication"* — followed by *"M6-R03's headers make the shared-frame version impossible regardless"* | **DEFECTIVE — self-cancelling**, and on the one verdict M6 says it "could not find a way to attack" |
| S02 | *"(iii) softens considerably if media is served from outside Git."* | **SOUND** |
| S03 | ***"Nothing available.** … this is as settled as an architecture question gets."* | **DEFECTIVE — and falsified.** Three observations that would move the verdict already exist (M4A-12) |

**Tally: 16 conditions · 7 SOUND · 1 weak-but-honest · 1 partial · 7 DEFECTIVE.**

**The pattern is not random.** Six of the seven defective conditions attach to verdicts of APPROVE or
APPROVE (weak basis) — the verdicts where the device does the most work, because an approval with no
falsifier is indistinguishable from an endorsement. The conditions attached to M6's *negative*
verdicts (L09, L10b, L10c) are the sound ones. **An auditor whose change-my-mind conditions are
rigorous exactly where it disagrees and self-cancelling exactly where it agrees has demonstrated the
device's own failure mode**, which §8.6 predicted and which is why this attack exists.

---

## 3 · THE ATTACKS (§7.1 schema, all nine fields)

```
ID:          M4A-01
TARGET:      The dispatch's own R8 "frozen briefing" and §6.3 preflight — **NOT M6.**
             An attack on M6's citation discipline was prepared, run, and is WITHDRAWN
             on the evidence. The reversal is recorded rather than quietly dropped.
TYPE:        OBSERVED
STATEMENT:   `CommitteeSystem.md` — the R8 frozen briefing, the file every C-2 agent cites and
             the file this dispatch's §13.1/§13.2/§13.3 rulings live in — **has been edited
             during the dispatch, and is still moving.** It stood at **1,471 lines** when I
             first read it in this session; it now stands at **1,566** (+95), with mtime
             **2026-08-20 05:36:50 UTC** — later than M6's audit (05:20:05), later than my own
             round-one file (05:17:47), later than M0's decision pack (04:59:42). Every other
             frozen source is stable: `Architecture.md` 03:39:14 (SHA-256 still matches M0's
             pin), `Challenges-and-Demo.md` 04:27:32, the readiness register 2026-08-19
             23:34:57, the GTM 2026-08-10 23:47:10.
             **Consequence, and it is the whole finding:** no `CommitteeSystem.md:<line>`
             citation written by any agent in this dispatch can be verified after the fact,
             because the target moved under it. M6's pointers are ~10 lines off against the
             state I measured at 05:24-05:32 and ~95-106 off against the state now. **Both
             offsets are artefacts of the file's motion, not evidence about M6.** I had this
             written as a SUSTAINED attack on M6's BASIS discipline and on its §8.3
             certification that "no finding rests on recollection". On the evidence it is not
             one. **Against M6 this HOLDS**, and the finding re-targets to the dispatch.
             **Three control failures it exposes, none of them M6's:**
             (i) **R8 is not enforced.** "One frozen briefing … M0 freezes **one** annex and
             attaches the **identical** copy to every brief." The briefing was not frozen; it
             was live, and two agents may have read different text.
             (ii) **§6.3's preflight hashes the wrong files.** Its "Canonical controls" row
             requires "Current §32 and GTM §14 attached with file hash and source timestamp."
             Both of those are stable and neither moved. **`CommitteeSystem.md` is not in that
             row** — so the one document that did move is the one nothing was watching.
             (iii) **§6.4's write-isolation diff is the control that should have caught it.**
             It greps for changes outside `docs/Marketing Website/committee/`; `01-governance/`
             is outside. Either the post-dispatch diff has not been run, or it was run and the
             edit was the Orchestrator's — the only party R9 permits to touch shared documents.
             **Even in the permitted case this is a breach in substance**, because editing the
             briefing while agents are reading it defeats R2's silent round and R8's
             identical-copy rule at the same time.
BASIS:       Re-runnable, from repo root, all 2026-08-20:
               wc -l "docs/Marketing Website/01-governance/CommitteeSystem.md"        → 1566
               ls -l --time-style=full-iso <same file>   → 2026-08-20 05:36:50.931 UTC
               ls -l --time-style=full-iso "docs/Marketing Website/committee/M6-architecture-audit.md"
                                                          → 2026-08-20 05:20:05.228 UTC
               sha256sum "docs/Marketing Website/06-technical/Architecture.md"
                                                          → 938ff387… (matches M0's pin)
             The 1,471-line figure is this session's own first read of the file, before 05:22.
             Same anchors, located twice by me today:
               "Rubber-stamp review"             :507  → :613
               "same visual unit as its claim"   :624  → :730
               §13.3 item 9  (telemetry boundary):1166 → :1272
               §13.3 item 11 (lead-data residency):1170 → :1276
               §15.1 item 4  (lead-form landing) :1446 → :1552
FALSIFIER:   An mtime on `CommitteeSystem.md` earlier than M6's audit file, or a line count
             matching the state M6 read. Either is one command and would overturn this
             entirely and restore the original attack.
CONFIDENCE:  High — filesystem metadata plus two line-count observations by the same agent
             hours apart, and a control set of four files that did not move.
AUTHORITY:   LOCKED — R8 (CommitteeSystem.md:197) and R9 (:198) are charter rules; §6.3's
             canonical-controls row is at :363; §6.4's diff at :370-380.
IMPACT IF WRONG: **High, and in an unusual direction.** If I am wrong I have accused the
             dispatch of a control failure it does not have. If I am right and it goes
             unrecorded: (a) M0's pack carries ~30 of M6's citations and ~40 of my own that no
             reader can follow; (b) §7.4's evaluation may score M6 down for a defect that was
             not its doing — **an adversary causing the failure it exists to prevent**; and
             (c) nobody knows whether M6, M4 and M0 read the same §13.1 text. That last is not
             cosmetic: §13.1 carries STEP0-001 and the SEED-001 amendment, and the whole of
             M4A-08 turns on its exact wording.
STATUS:      **Against M6: HOLDS** — attack prepared, run, withdrawn on the evidence.
             **Against the dispatch: OPEN — OWNER DECISION**, for M0 to record as a
             dispatch-level control gap. Recommended: hash `CommitteeSystem.md` at dispatch
             open and add it to §6.3's canonical-controls row; and either re-resolve every
             agent's citations against one declared state, or convert them to quoted-anchor
             form (§ number + verbatim phrase), which does not move when the file does.
```

```
ID:          M4A-02
TARGET:      M6-S02(iii), M6 Part 5 row 4 — the §32 item-number citations
TYPE:        OBSERVED
STATEMENT:   Two of M6's §32 citations point at a DIFFERENT NUMBERED PROHIBITION. M6-S02(iii)
             cites "§32 item 23 (canonical, readiness register :12045)" — line 12045 is
             **item 17** ("MFA enforced"). Item 23 (the `admin` credential) is at **:12051**.
             M6-V09 cites "§32 item 25, canonical: …:12047" — line 12047 is **item 19**
             ("Master patient index"). Item 25 (manufactured trust) is at **:12053**. This is
             the ONLY citation finding that survives M4A-01's withdrawal, because these two files have
             not been modified since 2026-08-19 and 2026-08-10 respectively — and because Part 5
             of M6's audit exists specifically to
             check §13.6's rulings against §32 **by item number**, and R8's exception
             (`CommitteeSystem.md:197`) exists because item numbers are the thing a summary
             cannot carry. An audit that adopts that discipline and mis-executes it hands the
             next reader a pointer to the wrong prohibition.
BASIS:       awk 'NR>=12028 && NR<=12060 {match($0,/^\| ([0-9]+) \|/,m); if(m[1]!="")
               printf "%s -> line %d\n", m[1], NR}' \
               docs/Marketing-MVP-and-Launch-Readiness-Requirements.md
             → 1→12029 … 17→12045 · 19→12047 · 23→12051 · 25→12053 · 30→12058. Run 2026-08-20.
FALSIFIER:   A different §32 table layout in the canonical file at the hash M0 froze.
CONFIDENCE:  High — deterministic.
AUTHORITY:   LOCKED — §32 is the binding downstream control list (RDY-0004).
IMPACT IF WRONG: Low if wrong. If right: the substance of both findings is still correct (the
             `admin`-in-Git-history point and the uptime-figures point are both real and both
             genuinely governed by the items M6 names), so this is again form, not substance —
             **but it is form in the one place the system has decided form matters most.**
STATUS:      REQUIRED CHANGE
```

```
ID:          M4A-03
TARGET:      M6-L01's CHANGE MY MIND condition  ·  the framework verdict's falsifier
TYPE:        OBSERVED
STATEMENT:   M6's condition reads: "Show me one page in the locked IA (§13.5's 17 routes) that
             cannot be produced at build time. One is enough." The parenthesis is the defect.
             **§13.5's 17 routes are not the locked IA.** The locked IA (WEB-002, GTM
             §17.2) is a 25-node tree; §13.5 omits 8 of those nodes and omits both routes that
             the funnel requires. Among the omissions is **"Book a walkthrough"** — the locked
             primary conversion event — which is the only surface in the entire site that is
             not a document but a transaction, and therefore the only candidate that could
             have satisfied M6's condition. **M6 constructed a falsifier over a set from which
             the falsifier had already been removed**, and did not notice the set was
             incomplete because it never diffed §13.5 against WEB-002.
BASIS:       Locked IA: docs/Product-Positioning-and-GTM-Locked-Strategy.md:773-797 (25 nodes,
             "Book a walkthrough" at :794, "Contact [short form + WhatsApp]" at :797).
             §13.5's list: docs/Marketing Website/06-technical/Architecture.md:111-128 (17).
             The full node-by-node diff is in my round-one file,
             `committee/M4-architecture-and-conversion.md` §2.2 (8 omissions named individually).
             grep -ni "walkthrough" "docs/Marketing Website/06-technical/Architecture.md" → 0 hits.
FALSIFIER:   A reading in which "the locked IA" and "§13.5's 17 routes" denote the same set.
             They differ by 8 nodes; the diff is enumerable and enumerated.
CONFIDENCE:  High — both sets are finite, written down, and were compared line by line.
AUTHORITY:   LOCKED (WEB-002) — and it is WEB-002, not §13.5, that governs what the site is.
IMPACT IF WRONG: **High for the record.** A change-my-mind condition is the device §8.6 uses in
             place of an external reviewer. If the one attached to the audit's headline
             verdict is unmeetable by construction, the verdict has no falsifier, and M0's
             pack would carry an approval that nothing could have overturned.
STATUS:      SUSTAINED — the condition must be restated over WEB-002's node set, not §13.5's.
```

```
ID:          M4A-04
TARGET:      M6-V01 (render mode, "EVIDENCE-DETERMINED"), M6-L10a, M6-S02
TYPE:        INFERRED
STATEMENT:   The same incomplete route list underwrites three further M6 conclusions.
             M6-V01 excludes render mode from deliberation entirely — it appears in Part 7's
             "no options generated" table as **EVIDENCE-DETERMINED**, basis "No locked-IA route
             requires request-time data; I read all 17 routes at Architecture.md:110-128."
             M6-L10a's FALSIFIER is "A requirement for request-time rendering on
             www.skyeagle.uk. None exists in the locked IA." Both statements are made about a
             list that omits the six-field qualifying form, its conditional routing
             (disqualifying answers → `not-a-fit`), and the booking surface. **§12's failure
             mode has a mirror image**: producing options for a determined item is a failed
             deliverable, and declaring an item determined on an incomplete evidence set is the
             same error in the more expensive direction — it closes a question that should have
             been examined.
             **In fairness, and this is why the result is WEAKENED and not SUSTAINED:** a
             static page can host a form that POSTs cross-origin, and M6's own O-2 option 2
             routes the form off-platform anyway, so the *conclusion* (static export) most
             likely survives. What does not survive is the claim that it was determined by
             evidence. It was determined by an evidence set with the funnel missing from it.
BASIS:       M6-architecture-audit.md, M6-V01 BASIS and FALSIFIER; Part 7 exclusion table row
             "Render mode (static vs SSR) | EVIDENCE-DETERMINED".
             The form's existence and conditional routing: CommitteeSystem.md:1218 (D-a),
             :1227-1232 (the six fields), :1243-1244 ("routes to an honest not-a-fit page").
             Absent from Architecture.md: grep -niE "not-a-fit|route handler|server action|api/"
             "docs/Marketing Website/06-technical/Architecture.md" → 0 design hits.
FALSIFIER:   A demonstration that the qualifying form's conditional routing can be resolved
             entirely at build time with no request-time computation anywhere in the estate.
             It can — in the browser, or on the receiving host — but M6 asserted it without
             examining it, because the form was not in the list it read.
CONFIDENCE:  Medium-High. High that the set was incomplete; Medium that the conclusion moves.
AUTHORITY:   EVIDENCE for the omission · HYPOTHESIS for the render-mode consequence
IMPACT IF WRONG: Medium. If the conclusion holds anyway, the cost is a re-derivation. If a
             genuine request-time requirement emerges from the funnel design, an item M6
             removed from deliberation has to be re-opened after the pack has closed.
STATUS:      WEAKENED — M6-V01 should be reclassified from EVIDENCE-DETERMINED to
             "determined, subject to the funnel design that does not yet exist."
```

```
ID:          M4A-05
TARGET:      M6-L01(b) — the refutation of the growth argument
TYPE:        INFERRED
STATEMENT:   M6-L01(b) refutes Architecture.md:56-58's growth argument in three moves, and the
             load-bearing one is circular within the audit: "lead capture cannot be a Vercel
             function under any defensible reading of residency (M6-V03)." M6-V03/L10b is
             M6's own contested finding, and its own CHANGE MY MIND condition is a written
             legal ruling which M6 states is **"entirely available … If it is made, this
             verdict flips to APPROVE WITH CHANGES immediately and cheaply."** So M6-L01(b)
             rests on a premise the same audit expects to flip. If the ruling comes, one third
             of the refutation of the framework justification evaporates, and M6-L01's verdict
             would need re-deriving — but M6-L01 records no dependency on M6-L10b and would
             not be revisited.
             The other two moves survive: the demo launcher genuinely is a hyperlink (I
             independently reached the same conclusion in round one), and the customer area
             genuinely is a separate environment under §13.1.
BASIS:       M6-architecture-audit.md M6-L01(b); M6-L10b CHANGE MY MIND ("entirely available",
             "flips to APPROVE WITH CHANGES immediately and cheaply").
             My independent round-one finding on the launcher:
             `committee/M4-architecture-and-conversion.md` M4-15.
FALSIFIER:   A statement in M6-L01 recording its dependency on M6-L10b's outcome. There is none.
CONFIDENCE:  Medium-High.
AUTHORITY:   HYPOTHESIS
IMPACT IF WRONG: Low-Medium. The framework conclusion is probably unaffected — two of three
             moves stand. The defect is that a contingent finding is used as a settled premise
             without the dependency being declared, so the dependent finding will not be
             revisited when the premise moves.
STATUS:      WEAKENED
```

```
ID:          M4A-06
TARGET:      M6-O3 — the framework option set, and its relation to M6-L01's verdict
TYPE:        OBSERVED
STATEMENT:   M6's per-layer verdict for the framework is **APPROVE WITH CHANGES for Next.js**.
             M6's own ranked option set for the same question puts **Astro first and Next.js
             second**. §12's rule is "cheapest first" — and M6 prices both at **$0** with
             "Comparable" time. Cheapest-first therefore cannot order them, yet they are
             ordered, with the incumbent second. **An ordering that the stated ranking rule
             cannot produce is a preference wearing the rule's clothes**, and it is the same
             defect M6 correctly identifies at Architecture.md:54 ("a four-figure precision
             ranking with no method"). M6's audit reproduces the defect it names, one section
             later, in the opposite direction.
             The narrower question the dispatch asks — is *approve-the-choice-reject-the-reason*
             coherent? — answers **yes**: a choice can survive the collapse of its stated
             justification if some other basis supports it, and M6 names one (team skill).
             What is not coherent is approving the incumbent in Part 1 and ranking the
             challenger above it in Part 7 without reconciling the two, because M0's pack will
             carry both and they point at different builds.
BASIS:       M6-architecture-audit.md §1.1 verdict summary row L01 ("APPROVE WITH CHANGES");
             M6-O3 options table (option 1 Astro "$0 / Comparable", option 2 Next.js
             "$0 / Comparable"); M6-L01(c) on unsourced rankings; §12's cheapest-first rule at
             CommitteeSystem.md (standard acceptance block, §12).
FALSIFIER:   A cost or time difference between the two options stated anywhere in M6's audit.
             Both cells read "$0" and "Comparable".
CONFIDENCE:  High — both cells are quoted from the same table.
AUTHORITY:   POLICY (§12's ranking rule)
IMPACT IF WRONG: Medium. The Owner reading the pack sees an approval and a contrary ranking
             for the same decision, with no statement of which governs.
STATUS:      SUSTAINED — either the L01 verdict or the O3 ordering must be restated so they
             agree, or the ordering must be declared alphabetical/arbitrary rather than
             cheapest-first.
```

```
ID:          M4A-07
TARGET:      M6-L10b's STATUS: BLOCK
TYPE:        OBSERVED
STATEMENT:   M6 issued a **BLOCK**. §8.6 defines M6's deliverable as "**APPROVE · APPROVE WITH
             CHANGES · REJECT AND REPLACE** per layer" — BLOCK is not in M6's verdict set. The
             veto belongs to M5 alone: C-4's Reviewer row reads "**M5 alone**, holding a veto"
             and its Decider row "M5's **BLOCK** stands unless the **Owner overrides it in
             writing.** M0 cannot. A majority cannot." §8.5 repeats it: "A BLOCK stops the
             deliverable. **Only the Owner overrides, in writing.**"
             **The ambiguity is real and I state both sides.** §7.1's STATUS enum does list
             BLOCK as a permitted token for any finding, so M6's usage is schema-legal. But a
             token that stops a deliverable is not a neutral label, and M6 wrote it as an
             operative instruction — "the lead form may not ship on a Vercel function until
             M6-V03 is ruled" — not as a status description. The consequence is procedural and
             concrete: §12's standard acceptance requires "**M5 returns zero unresolved
             BLOCKs**". A BLOCK originating from M6 sits outside that accounting. It will
             either be ignored, which defeats a finding I think is substantively right, or be
             treated as binding, which vests stopping power in an agent the charter did not
             give it to. Neither outcome is acceptable and the fix is one word.
BASIS:       CommitteeSystem.md:823-824 (M6's deliverable enum); :155-156 (C-4 reviewer and
             decider rows); :795 (M5's powers); :516 (§7.1 STATUS enum, which does include
             BLOCK); §12 standard acceptance block ("M5 returns zero unresolved BLOCKs").
             M6-architecture-audit.md M6-L10b: "STATUS: **BLOCK** — the lead form may not ship
             on a Vercel function until M6-V03 is ruled."
FALSIFIER:   A passage granting M6 a veto. I searched §2, §3.2, §8.6 and §14; the only agent
             named as holding a veto is M5 (§2 roster row: "M5 … **Adversary — veto**";
             M6's row reads "**Adversary**" with no veto).
CONFIDENCE:  High on the charter text; Medium on how M0 should resolve it, which is M0's call.
AUTHORITY:   LOCKED — the veto allocation is charter text, not preference.
IMPACT IF WRONG: Medium procedurally. **I want to be explicit that I am attacking the
             instrument, not the conclusion**: M6's underlying finding (no Saudi Vercel
             region; TLS terminated in a compute region; do not ship the form without a
             ruling) survived every attack I ran and I endorse it below at M4A-09. The correct
             form is REJECT AND REPLACE with STATUS "OPEN — OWNER DECISION", which is exactly
             what M6-V03 and M6-Q01 already use for the same matter. **M6 used two different
             statuses for one finding across three places in its own audit.**
STATUS:      SUSTAINED
```

```
ID:          M4A-08
TARGET:      M6-O2 option 1 — "No form at MVP", ranked cheapest
TYPE:        OBSERVED
STATEMENT:   M6 ranks "no form at MVP; publish a `mailto:` and a phone number" as the cheapest
             option for the lead-data question, and prices its cost as: "Loses the §13.2
             qualifying form — which is a **real** loss, because that form *is* RDY-0065's
             qualification checklist, a P0 gating G3 and G6. **So this option defers a P0.**"
             **That pricing is wrong, and wrong in the direction that makes the cheapest option
             look cheaper than it is.** Removing the form does not defer a P0. It fails
             `STEP0-001`'s condition **D-a** — "Access is issued **only** after a qualifying
             form applying GTM §5.1/§5.2" — and D-a is not advisory: "Reading B is not a label
             that can be applied to Reading A. It is testable. **If any condition fails, it is
             Reading A** and all four decisions must be reopened in writing before anything is
             designed." A site with no qualifying form and no credential issuance is
             `STEP0-001` **option N** — "NEITHER — remain guided-demo only" — which the Owner
             considered and **did not select**, recording that "N was rejected because it
             leaves the funnel's single conversion point shut and leaves RDY-0065 without a
             consumer."
             **Three further collisions M6 did not check:**
             (i) The locked IA contains `Contact [short form + WhatsApp]` as a node
             (GTM:797). "No form at MVP" deletes a node from an IA that is `LOCKED FOR MVP`.
             (ii) WEB-001's locked primary conversion event is "**Book a walkthrough**", not a
             mailto. GTM:768 lists "Request a quote" under *deliberately absent*; an unqualified
             mailto is nearer to that shape than to a booking.
             (iii) It destroys the only reliable instrument for the locked
             self-disqualification metric — a declared, routed, countable disqualification
             (GTM:1151; my round-one M4-14). My own brief's `LOCKED` constraint is that this
             path is "preserved and instrumented", never optimised away
             (CommitteeSystem.md:759). Deleting the form deletes the instrument.
BASIS:       M6-architecture-audit.md M6-O2, option 1 row ("So this option defers a P0, and
             that must be stated, not hidden").
             D-a: CommitteeSystem.md:1218. Reading-A consequence: :1214.
             Option N and its rejection: :1164 and :1169-1179 ("N was rejected because…").
             RDY-0065's consumer: :1240. Locked IA Contact node: GTM:797.
             WEB-001: GTM:766-768. Self-disqualification metric: GTM:1151.
FALSIFIER:   A reading in which STEP0-001's D-a is satisfied without a form. D-a's own failure
             clause is "The form collects contact details but gates nothing" — which presumes
             a form exists; no form is the stronger failure, not an exemption from the test.
CONFIDENCE:  High. Every element is quoted charter or GTM text.
AUTHORITY:   LOCKED — WEB-001, WEB-002, and STEP0-001's conditionality.
IMPACT IF WRONG: **Severe if the mispricing stands.** The Owner reads Part 7 O-2 and sees a
             $0, zero-day, cheapest-ranked option whose only declared cost is a deferred P0.
             The undeclared cost is that selecting it converts a ruled access design back into
             an option the Owner already rejected, and reopens four locked decisions by
             operation of STEP0-001's own conditionality. **That is the precise shape of the
             R-02 failure the classification system exists to prevent** (GTM:1174, High
             likelihood / Severe impact) — a locked decision reversed as a side effect of a
             hosting choice.
             **Per §8.4 I hold no reopening licence and I do not propose the reopening.** I
             name the consequence and stop. If the Owner wants option 1, that is a reopening
             request routed through M2 and the Owner, in writing, before it is priced as cheap.
STATUS:      SUSTAINED — M6-O2 option 1's cost column must be restated, or the option withdrawn
             as unavailable without a reopening.
```

```
ID:          M4A-09
TARGET:      M6-L10b / M6-V03 / M6-O2 — the split verdict, from the funnel's side
TYPE:        INFERRED
STATEMENT:   The dispatch asks whether the split collapses — can pages run on one platform and
             the form on another? **It does not collapse; it is technically clean.** A static
             page can carry a form that POSTs cross-origin, and M6-O2 option 2 (POST to the
             Dammam instance) is coherent. **But M6 priced the split at zero for the funnel,
             and it is not zero.** Three costs M6 did not name, all of which land on the
             funnel's terminal step:
             (i) **A cross-origin failure mode at the worst possible moment.** The visitor has
             filled six fields including a safety confirmation. The submission now depends on a
             second host being reachable. If the Dammam endpoint is slow or down, the failure
             occurs *after* the whole funnel has been spent. The marketing site is static and
             cannot retry server-side.
             (ii) **The disqualification signal has to cross hosts.** The routing decision
             (disqualified → `not-a-fit`) is made on the receiving host; the landing page is on
             `www`. Joining them means either a query parameter on the redirect back, or an
             aggregate counter that never reaches the marketing analytics at all.
             (iii) **Path (i)'s query parameter collides with M6's own M6-V08.** M6 establishes
             that Vercel's logs capture "**Search Params**" and that client IP is retained and
             queryable. A redirect back to `www.skyeagle.uk/not-a-fit?d=1` therefore writes a
             per-visitor disqualification signal into a log store M6 itself argues sits outside
             the Kingdom. **Two of M6's own findings recommend a pattern that the third
             prohibits**, and neither cross-references the other.
             The resolution is available and cheap and belongs in the funnel design, not the
             hosting decision: the receiving host increments an aggregate counter and redirects
             to a **parameterless** `/not-a-fit`, so no per-visitor signal is created anywhere.
BASIS:       M6-architecture-audit.md M6-O2 option 2 row; M6-V08 STATEMENT (Search Params,
             IP retention, retention windows); M6-L10b.
             The routing rule that creates the requirement: CommitteeSystem.md:1243-1244.
             My round-one treatment of the same instrument: M4-14 and §4.2 option 1 of
             `committee/M4-architecture-and-conversion.md`.
FALSIFIER:   Vercel documentation showing that query strings on requests to a purely static
             asset are not logged. M6 quotes the opposite for runtime logs; whether it holds
             for a functionless static export is exactly M4A-10's open question.
CONFIDENCE:  Medium-High on (i) and (ii); Medium on (iii), which inherits M4A-10's uncertainty.
AUTHORITY:   POLICY (§13.3 item 11, CommitteeSystem.md:1276) · HYPOTHESIS for the mechanics
IMPACT IF WRONG: Medium. The split survives either way; what changes is whether the Owner is
             told it costs the funnel a cross-origin dependency and a measurement seam, or
             told it is free.
STATUS:      WEAKENED — the conclusion (do not put the form on a Vercel function without a
             ruling) survives; the costing does not.
```

```
ID:          M4A-10
TARGET:      M6-V08's central premise, against M6-V01's central recommendation
TYPE:        INFERRED
STATEMENT:   M6-V08's headline is "**Vercel logs personal data regardless of whether GA4 is
             installed**", and its entire BASIS is Vercel's **runtime logs** documentation.
             M6-V01's headline recommendation is that the site be a fully static export with
             **no functions and no middleware**. Runtime logs are, on the ordinary reading of
             the term, function-execution logs. **If M6-V01 is adopted there is no runtime**,
             and M6-V08's premise may not apply to the deployment M6 recommends. Either
             M6-V08 is over-stated for a functionless static export, or static asset requests
             do appear in the same log surface and M6-V01 does not reduce the telemetry
             exposure as much as M6-V09(b) implies. **M6 does not resolve which, and the two
             findings are three sections apart with no cross-reference.**
             I flag this rather than settle it: I did not fetch Vercel's documentation and I
             will not assert a platform fact I have not read (R10 — "'Unverified' is an
             acceptable answer; 'probably fine' is not"). The attack is on the audit's internal
             consistency, which I can assess, not on Vercel's behaviour, which I cannot.
BASIS:       M6-architecture-audit.md M6-V08 STATEMENT and BASIS
             (https://vercel.com/docs/runtime-logs, accessed 2026-08-20 per M6);
             M6-V01 STATEMENT ("no functions"); M6-L10a required change (1).
FALSIFIER:   Vercel documentation showing static-asset requests recorded in the same log store
             with the same fields, which would resolve the tension in M6's favour and should be
             cited in M6-V08's BASIS if so.
CONFIDENCE:  Medium — the tension is observable in M6's own text; the resolution is not
             available to me.
AUTHORITY:   EVIDENCE (the internal tension) · UNKNOWN (the platform fact)
IMPACT IF WRONG: Low-Medium. M6-V08's operative recommendation — "no hosting-location claim
             about the marketing site" — is correct under either resolution and I endorse it.
             What is at stake is whether the finding's stated magnitude survives its own
             companion recommendation.
STATUS:      WEAKENED — M6-V08 to state which log surface applies under `output: 'export'`.
```

```
ID:          M4A-11
TARGET:      M6-R03 / M6-S03 — the anti-framing evidence and the reclassification built on it
TYPE:        OBSERVED
STATEMENT:   I attacked M6's reclassification by trying to find a response from the demo that
             does NOT carry the anti-framing headers. **I found two, and the reclassification
             survives anyway** — which is why this is WEAKENED on method and not SUSTAINED.
             `https://demo.skyeagle.uk/` returns **HTTP/1.1 302 with neither
             `X-Frame-Options` nor a CSP**; `…/interface/main/main_screen.php` returns
             **HTTP/1.1 400, also with neither**. The headers are present on rendered pages
             (login: `X-Frame-Options: DENY` + `Content-Security-Policy: frame-ancestors
             'none'`; `/portal/index.php`: same). Framing is still defeated, because a frame
             pointed at `/` follows the 302 and lands on a document that does carry the
             headers — so M6's conclusion is right. **M6's evidence base was one URL**, and it
             generalised to "the demo already sends" without testing a second. On a finding
             whose purpose is to move an item from *genuinely open* to *evidence-determined*,
             one observation is thin.
             **A by-product worth carrying to Challenge 4:** the 400 on `main_screen.php` is a
             **sixth** distinct denial/error shape, beyond the five already measured (403/1.8 KB,
             200/0 B, 200/14 B, 401/0 B, 500). It strengthens my round-one M4-17: the
             inconsistency is wider than the register records.
BASIS:       Re-runnable, all 2026-08-20:
               curl -sS -I "https://demo.skyeagle.uk/interface/login/login.php?site=default"
                 → 200 · X-Frame-Options: DENY · Content-Security-Policy: frame-ancestors 'none'
               curl -sS -I "https://demo.skyeagle.uk/"            → 302, no such headers
               curl -sS -I "https://demo.skyeagle.uk/interface/main/main_screen.php" → 400, none
               curl -sS -I "https://demo.skyeagle.uk/portal/index.php" → 200, both headers
             Five measured shapes: CommitteeSystem.md:1068-1070;
             docs/Marketing Website/03-website-plan/Challenges-and-Demo.md:248-252.
FALSIFIER:   A frame-embedding test that actually renders the demo. I did not run one, and say
             so; the header evidence is strong but a rendered negative would be stronger.
CONFIDENCE:  High on the observations; High that M6's conclusion survives.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Low — the reclassification is right. The value of the attack is that the
             evidence base is now four URLs instead of one, and a sixth denial shape is on the
             record.
STATUS:      WEAKENED (method) · M6's conclusion HOLDS
```

```
ID:          M4A-12
TARGET:      M6-S03 — "APPROVE … Nothing available [to change my mind]"
TYPE:        OBSERVED
STATEMENT:   M6 marks §13.7 PASS and writes "**Nothing available.** The headers make the
             alternative technically impossible; this is as settled as an architecture question
             gets." That is true of the *iframe sub-question* and false of §13.7. §13.7 is not
             a header decision; it is a **launcher pattern** — choose an experience, two
             credentials, one instruction, plus a low-commitment path. Three observations that
             would move the verdict from PASS to APPROVE WITH CHANGES already existed at the
             time M6 wrote, in the same frozen sources M6 read:
             (i) The instruction that carries the entire pattern — *"log in as both, open the
             same patient, and see the difference"* — is rendered on `www` and the visitor then
             leaves for `demo`, where nothing repeats it. The marketing architecture cannot
             deliver its own strongest funnel asset without a change on a host §13 does not
             govern.
             (ii) §13.3 item 4 requires a prominent pre-entry notice (shared, synthetic, reset
             periodically, never claim isolation). §13.5's component list has **no component
             for it** — M6 itself records this in gate item 4 and calls it "a real omission in
             the component list", then does not carry it into the S03 verdict.
             (iii) A visitor who arrives while the demo is unavailable or hits a denial gets an
             unbranded error, and §11 Challenge 4's own finding is that a visitor alone
             concludes the software is broken.
             **M6's own gate item 4 contradicts M6's own S03 PASS.** The two are 400 lines
             apart in the same file.
BASIS:       M6-architecture-audit.md M6-S03 CHANGE MY MIND ("Nothing available") and Part 3
             gate item 4 ("**§13.5's component list has no component for it.** … This is a real
             omission in the component list and it is M4's lane to design").
             §13.3 item 4: CommitteeSystem.md:1262-1263. The two-credential instruction:
             CommitteeSystem.md:1073-1078. Challenge 4's denial finding: :1068-1070.
             My round-one findings on the same three points: M4-15, M4-16, M4-17 in
             `committee/M4-architecture-and-conversion.md`.
FALSIFIER:   A reading of §13.7 limited to the iframe question. §13.7's own text spans the
             launcher, the two credentials, the four challenges' answers and the
             low-commitment path (Architecture.md:170-189); the iframe sentence is one line of it.
CONFIDENCE:  High.
AUTHORITY:   POLICY (§13.3 item 4) · LOCKED for the disqualification/instruction constraints
IMPACT IF WRONG: Medium. A PASS on §13.7 tells the Owner the launcher is settled when its
             pre-entry notice, its cross-host instruction and its failure state are all
             undesigned. Those are three of the four things §13.7 claims to deliver.
STATUS:      SUSTAINED — S03 should be APPROVE WITH CHANGES, and its change-my-mind condition
             restated; "nothing available" is falsified by M6's own gate item 4.
```

```
ID:          M4A-13
TARGET:      M6-Q03 — §13.8 Q3 reclassified as ANSWERED AND CLOSED
TYPE:        INFERRED
STATEMENT:   I ran three attacks on this reclassification and **all three failed**; I record
             them because a HOLDS is only worth anything if the attack is shown.
             **Attack 1 — provenance.** §13.3 is TASK 1's security gate, and Task 1 is HELD by
             `SEED-001`. Can an item inside a held task's gate close an open question in a
             different task? **Failed:** §13.3 item 9 is written as a standing requirement, not
             as a conditional step, and `CommitteeSystem.md` is the frozen briefing for this
             dispatch under R8. A held task does not unwrite its own gate.
             **Attack 2 — authority mismatch.** M6 rules the item "CONSTRAINT-DETERMINED,
             closed" while tagging it `AUTHORITY: POLICY (Owner may change it, in writing)`.
             **Failed:** §4.1's constraint-determined class covers "a locked decision governs",
             and §4.2's POLICY tag means "Owner instruction on record". An Owner instruction on
             record governs until the Owner changes it. The two are consistent.
             **Attack 3 — over-reach.** M6 goes beyond closing the question and recommends
             **extending the gate** ("The gate should be extended to define 'minimal
             operational and security events' on the marketing side"). **Partially lands:**
             extending §13.3 is an amendment to a governance instrument, and R11 routes
             amendments through M0 to the Owner in the §7.4 format — not through an
             architecture document. M6 phrases it as a recommendation, which is within its
             rights, but M0 should record it as a `PROPOSED — Owner decision required`
             amendment rather than folding it into a REQUIRED CHANGE on Architecture.md:197.
BASIS:       M6-architecture-audit.md M6-Q03; §13.3 item 9 at CommitteeSystem.md:1272-1273;
             Task 1 HELD: CommitteeSystem.md §13.1 amendment box and §15 status table;
             §4.1/§4.2 class and tag definitions: CommitteeSystem.md:207-226; R11: :200.
FALSIFIER:   An Owner instruction relaxing §13.3 item 9, in writing. None exists.
CONFIDENCE:  High.
AUTHORITY:   POLICY
IMPACT IF WRONG: Low. The architecture already complies (Architecture.md:51). The only live
             consequence is the routing of M6's proposed gate extension.
STATUS:      HOLDS — attacked three ways, closure survives; the gate-extension recommendation
             to be re-routed as an R11 amendment.
```

```
ID:          M4A-14
TARGET:      M6 Part 5's scoreboard — "5 confirmed · 2 challenged"
TYPE:        OBSERVED
STATEMENT:   M6's own evidence shows **three** §13.6 rows whose ruling is not supported by the
             authority cited, and M6 reports two. Row 1 (`billing/`) is scored **CONFIRMED —
             outcome right, citation weak**, with M6 demonstrating that §32 item 4 does not
             cover patient billing (I verified: item 4 reads "GL, accounting, ERP, AP,
             procurement, POs, HR, payroll, rostering, asset management") and supplying a
             better authority itself (GTM §14.4). That is the identical defect pattern as rows
             3 and 7 — a ruling whose cited basis does not support it — differing only in that
             a rescue was available. **Row 7 is scored CHALLENGED even though M6 says in the
             same cell that "The ICP basis is real (GTM ICP-001…), so the *page* is correctly
             dropped."** So M6 applies **two different labels to the same pattern in the same
             table**, and the direction of the inconsistency flatters its summary line: "The
             two challenged are exactly the two M0 flagged — the classification test caught
             them correctly." On M6's own evidence the test caught **two of three**.
BASIS:       M6-architecture-audit.md Part 5, rows 1, 3, 7 and the closing summary line.
             §32 item 4 text and location: docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:12032.
             §32 item 12: :12040. GTM §14.4: docs/Product-Positioning-and-GTM-Locked-Strategy.md:637-638
             (M6 cited GTM:643, which is "No claim outside 14.1–14.2 may enter the messaging
             hierarchy in §13" — a different sentence, in a file untouched since 2026-08-10,
             so this one is chargeable).
FALSIFIER:   A principled distinction between "outcome right, citation weak" and "challenged"
             that M6 states anywhere. It does not.
CONFIDENCE:  High.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Medium. The summary line is what a busy reader takes from Part 5, and it
             reports the classification control as having caught everything when M6's own
             body text records a third instance it caught by accident.
STATUS:      SUSTAINED — restate as "3 rows with unsupported or mis-cited authority, 1 of them
             rescuable"; the ruling on each remains M5's.
```

```
ID:          M4A-15
TARGET:      M6 Part 5 row 3 — `orders-results/`, and the "sub-section carries the
             qualification less well" argument
TYPE:        INFERRED
STATEMENT:   M6's technical objection is that "§32 requires the mandatory qualification to
             travel *in the same visual unit* as the claim. MC-21's qualification is long and
             consequential. **A sub-section has less room to carry it prominently than a full
             page does**, so demoting this to a sub-section may make compliance *harder*."
             **That is backwards on the rule it invokes.** A *visual unit* is the block in
             which the claim renders — a card, a section, a `QualifiedClaim` component — not a
             page. The component renders identically whether it sits on a page of its own or
             inside a section, so "room" is not the constraint. If anything the inference runs
             the other way: **a full page multiplies the surfaces on which the claim appears**
             — a hero, a feature grid, an FAQ entry, a meta description — and each is a place
             the qualification can fail to travel. A single section has one. M6's own M6-L05(a)
             makes precisely this point about `QualifiedClaim` ("a component only enforces
             adjacency for content that USES it"), and my round-one M4-07 enumerates eleven
             bypasses, most of which are *page-level* surfaces that a sub-section never creates.
             **And the prior question was not asked.** The locked IA contains **no
             orders-or-results node at any level** — not as a page, not as a sub-section. Its
             Product branch is exactly *Clinical documentation · Scheduling & front office ·
             Reporting & export · Optional, switched off by default*. M6 argues about the form
             the node should take without checking whether WEB-002 contains it at all. Relative
             to the locked IA, §13.6's ruling **adds** rather than corrects.
BASIS:       M6-architecture-audit.md Part 5 row 3.
             MC-21 and its qualification: docs/Product-Positioning-and-GTM-Locked-Strategy.md:628.
             The locked Product branch: GTM:779-783. Same-visual-unit rule: CommitteeSystem.md:730.
             The eleven bypasses: `committee/M4-architecture-and-conversion.md` M4-07 table.
             My round-one stop on this row: same file, M4-21.
FALSIFIER:   A statement in §32 or GTM §14 defining "visual unit" in terms of page area rather
             than the rendering block. Neither does; both speak of the unit in which the claim
             appears.
CONFIDENCE:  Medium-High on the adjacency argument; High on the locked-IA omission, which is a
             set comparison.
AUTHORITY:   LOCKED (§32 adjacency; WEB-002)
IMPACT IF WRONG: Medium. M6's recommendation — "restate the row as a marketing preference with
             no §32 backing, or drop it" — is one I agree with on the citation grounds. What I
             reject is the supporting argument, because it will be quoted later as a general
             principle ("full pages carry qualifications better"), and it is not one.
             **Per §8.4 I do not design this page or section, and I do not propose that WEB-002
             be changed to include it. I name it and stop.**
STATUS:      SUSTAINED
```

```
ID:          M4A-16
TARGET:      M6 Part 5 row 7 — coverage gap inside the row M6 examined most closely
TYPE:        OBSERVED
STATEMENT:   M6 examined §13.6's "Solo practice — Drop" row, challenged its missing citation,
             and found the `not-a-fit` routing hole in it — a good catch that I reached
             independently. **It missed the one defect in that row that can delete a locked
             page.** "Solo practice" and "**Single clinic**" are different things: solo =
             one provider, which is outside ICP-001's 3–15 band; single clinic = one site,
             which is inside it. **"Single clinic" is a node of the locked IA** (GTM:776), and
             §13.5 omits it along with the other two non-ophthalmology segment pages. A builder
             reading §13.6's ruling next to §13.5's route list has every reason to conclude the
             segment pages are gone. M6 read the row, read §13.5, and did not connect them.
BASIS:       Locked IA segment children: GTM:775-778 (Ophthalmology, Single clinic, Small
             medical centre / polyclinic, Two or three locations).
             ICP band: CommitteeSystem.md:888 ("3–15 providers, 1 site (up to 3)").
             §13.5's route list: Architecture.md:111-128 — `who-its-for/` present, no children.
             Architecture.md:160 ("Solo practice" segment page | Drop | Not the ICP).
             My round-one finding: `committee/M4-architecture-and-conversion.md` M4-22.
FALSIFIER:   Evidence that "solo practice" and "single clinic" were intended as the same page.
             The locked IA uses "Single clinic" and never "solo practice"; the two terms come
             from different documents.
CONFIDENCE:  High.
AUTHORITY:   EVIDENCE — determined by GTM:776 read against CommitteeSystem.md:888. Answered
             and closed: build "Single clinic"; build no "solo practice" page. **No options,
             per §12.**
IMPACT IF WRONG: Medium — one ICP segment page missing and a segment router with one
             destination instead of four. Cheap to fix now, expensive after both locales exist.
STATUS:      SUSTAINED (coverage)
```

```
ID:          M4A-17
TARGET:      The apparent M0 / M6 / M4 convergence on §13.6 rows 3 and 7
TYPE:        INFERRED
STATEMENT:   Three agents independently identified the same two §13.6 rows as lacking a cited
             governing ID, and M6 presents this as corroboration: "the classification test
             caught them correctly, and independent checking against the canonical annex
             confirms no such ID exists for either." **The agreement is real; the independence
             is not.** M0's classification table — items 12g and 12h — names those exact two
             rows and is part of the frozen briefing every C-2 agent read. All three agents
             were pointed at the same two rows before looking. R4's rule that "independent
             agreement is real signal" (`CommitteeSystem.md:193`) therefore does not apply
             here: what is corroborated is that the pointer works, not that the finding was
             found three times.
             **The check that would have been independent** is whether anyone examined the
             other five rows for the same defect without being told to. M6 did — and found one
             (row 1's mis-citation of §32 item 4), which is the genuinely independent result in
             Part 5 and the one M6's own summary line discards (M4A-14). **The strongest thing
             in M6's Part 5 is the finding it did not report as a finding.**
BASIS:       M0-decision-pack-task2.md §2, items 12g and 12h (both flagged "GENUINELY OPEN …
             no citation"). M6-architecture-audit.md Part 5 closing summary. My own round-one
             file, M4-21 and M4-22, which also stop at rows 3 and 7.
             R4: CommitteeSystem.md:193.
FALSIFIER:   Evidence that M6 or I identified rows 3 and 7 before reading M0's pack. Both of us
             were dispatched with the pack as required reading; neither can claim it.
CONFIDENCE:  High on the anchoring mechanism; Medium on how much it discounts the finding —
             the finding is still correct, it is just not triply-confirmed.
AUTHORITY:   EVIDENCE (R4's condition)
IMPACT IF WRONG: Medium for the pack's §9 CONVERGENCE section. Recording this as three-way
             independent agreement would overstate the confidence available, and R4 exists
             precisely to stop convergence being manufactured.
STATUS:      SUSTAINED (methodological) — M0 to record rows 3 and 7 as *pointer-directed
             agreement*, and row 1 as the *independent* Part 5 result.
```

```
ID:          M4A-18
TARGET:      M6-L09's AUTHORITY tag  ·  and M6-L02's lane
TYPE:        OBSERVED
STATEMENT:   (a) M6-L09 drafts replacement policy text — "Replace with: 'A CMS may not be
             adopted until it can demonstrably enforce a named-reviewer approval before
             publish, and its adoption is itself a C-2 and C-4 matter'" — and tags the finding
             `AUTHORITY: **LOCKED** — derives from C-4 (§3.4) and RDY-0003, not from
             architectural taste.` **The requirement is LOCKED; the sentence M6 composed is
             not.** Tagging self-authored replacement text as LOCKED is a category error in the
             one system §4.2 exists to protect: a `LOCKED` tag means "Owner, in writing, with
             the decision ID", and no Owner has seen this sentence. The correct tag is `LOCKED`
             for the requirement and `PROPOSED` / `POLICY` for the drafted trigger.
             Note the structural echo: §8.5 forbids M5 from proposing replacement copy because
             "that would make it the author of what it audits". §8.6 carries no equivalent
             clause, so M6 has not broken a rule — but it has walked into the hazard the rule
             was written for, and it did so on a *governance* sentence rather than a config value.
             (b) M6-L02 argues TypeScript is "a claim-register control, not a developer
             preference". That is a claim-compliance judgement, which is M5's lane, offered
             inside a technical verdict. Minor, and I raise it only because M6's out-of-scope
             line is "Marketing judgement" (`CommitteeSystem.md:820`) and this is adjacent to it.
BASIS:       M6-architecture-audit.md M6-L09 (STATEMENT and AUTHORITY), M6-L02 (STATEMENT).
             §4.2's tag definitions: CommitteeSystem.md:221-224 ("LOCKED — A locked decision or
             a §32 row — Owner, in writing, with the decision ID").
             §8.5's no-replacement-copy rule: CommitteeSystem.md:799.
             §8.6 out of scope: CommitteeSystem.md:820.
FALSIFIER:   An Owner decision ID attached to M6's replacement sentence. There is none; the
             sentence was written in this dispatch.
CONFIDENCE:  High on (a); Medium on (b), which is a judgement about lane boundaries.
AUTHORITY:   POLICY (§4.2's tagging discipline)
IMPACT IF WRONG: Medium. A `LOCKED` tag is how this system distinguishes what may be argued
             from what may not. Attaching it to an agent's own draft text, in a governance
             document, is how a preference acquires the authority of a decision — which is the
             precise defect M6 correctly identifies in §13.6 row 3.
STATUS:      SUSTAINED (a) · WEAKENED (b)
```

```
ID:          M4A-19
TARGET:      M6-L11's "Nothing changes my mind … there is no argument against it"
TYPE:        INFERRED
STATEMENT:   M6 declares the required-review control unfalsifiable and states that no argument
             against it exists. **An argument against it exists, it is not exotic, and it
             chains directly to M6's own weakest gate result.** A CODEOWNERS gate naming a
             single reviewer on `content/**` makes that one person a **single point of failure
             for every change to the site — including the change that removes a prohibited
             claim.** M6's own gate item 10 establishes that a static marketing site has no
             fast kill switch ("taking the `/demo` page down requires a rebuild and redeploy —
             i.e. it *does* wait for a deployment, which is exactly what this item forbids").
             Combine the two and the estate M6 recommends has this property: if M5 or the Owner
             discovers a §32 breach live on `www`, removal requires a PR, the named reviewer's
             approval, a build and a deploy — and if the named reviewer is unavailable, it
             requires all of that plus a governance exception. **The control that protects
             against a prohibited claim being published is the same control that slows its
             removal.**
             This does not make the control wrong — it is still the cheapest and best available,
             and I would keep it. It makes M6's condition wrong: there is an argument, M6 did
             not consider it, and the mitigation is one line (a named break-glass second
             approver, or an edge redirect rule that can take a single URL to a holding page
             without a build).
BASIS:       M6-architecture-audit.md M6-L11 CHANGE MY MIND; M6 Part 3 gate item 10 (kill
             switch, **FAIL**); M6-V10 items (1)–(4).
             The §8.6 constraint M6 set itself: "Where I could only write a condition that
             amounts to *'if the Owner preferred otherwise,'* I have marked the verdict
             **APPROVE (weak basis)** instead of dressing it up" — M6's Part 1 preamble.
             R-02's likelihood: docs/Product-Positioning-and-GTM-Locked-Strategy.md:1174 (High).
FALSIFIER:   A break-glass path in M6's proposal. There is none; M6-V10(1) explicitly says
             "direct pushes blocked, **including for admins**."
CONFIDENCE:  Medium-High.
AUTHORITY:   HYPOTHESIS for the counter-argument · LOCKED for the underlying review requirement
IMPACT IF WRONG: Medium. Being wrong costs one unnecessary break-glass provision. Being right
             and unaddressed means the highest-likelihood risk in the register (R-02, High)
             has a removal path gated on one person's availability.
STATUS:      SUSTAINED — the condition is falsified; the control survives with a break-glass
             addition.
```

```
ID:          M4A-20
TARGET:      Coverage — SEO, performance and structured data, all named in §8.6
TYPE:        OBSERVED
STATEMENT:   §8.6 puts "**performance and SEO consequences**" inside M6's scope. The audit
             carries **no layer verdict for either.** SEO appears only as four bullets inside
             M6-V07(d) (locale 404s, `hreflang`, canonical, per-locale sitemaps) framed as
             "failure modes that must be explicitly tested" — no verdict, no change-my-mind
             condition, no status. Performance appears once, as a parenthetical in M6-L03
             ("some unnecessary hydration JS on a marketing page", IMPACT: Low). **Structured
             data / JSON-LD does not appear at all** — zero occurrences in 1,729 lines. That is
             a gap with a §32 edge, not merely an SEO one: JSON-LD is the one claim surface on
             which a mandatory qualification cannot travel in the same visual unit, because it
             has no visual unit — it renders inside a search result. An `aggregateRating` or
             `review` node is §32 item 25 (manufactured trust: there are no customers) and an
             `offers.price` node breaches PRC-003, and both are things a default Next.js or
             Astro starter will happily emit.
             Also absent: any verdict on the **marketing site's own** `robots.txt` and sitemap.
             M6 found and credited the demo's `robots.txt: Disallow: /` — a genuinely good
             catch, which I reproduced — and issued nothing for `www`, where `not-a-fit` and
             `thank-you` must be `noindex` and where the per-locale sitemap requirement lives.
BASIS:       §8.6 scope line: CommitteeSystem.md:815 ("performance and SEO consequences").
             grep -nic "json-ld\|structured data\|schema.org" on M6-architecture-audit.md → 0.
             M6-V07(d); M6-L03 IMPACT line; M6 Part 4 ledger row "Search-engine exclusion".
             §32 item 25: docs/Marketing-MVP-and-Launch-Readiness-Requirements.md:12053.
             My round-one finding on the same gap: M4-09 in
             `committee/M4-architecture-and-conversion.md`.
FALSIFIER:   A layer verdict for SEO or performance anywhere in M6's Parts 1–7. There is none;
             §1.1's verdict summary lists 16 rows and neither appears.
CONFIDENCE:  High — this is an enumeration of M6's own verdict table against its own brief.
AUTHORITY:   POLICY (§8.6 scope) · LOCKED (§32 item 25, PRC-003) for the JSON-LD consequence
IMPACT IF WRONG: Medium. §7.4 measures adversary performance by **coverage**, and two named
             scope areas with no verdict is a coverage shortfall in the acceptance criterion
             M6's own §8.2 self-check reports as met.
STATUS:      SUSTAINED (coverage)
```

```
ID:          M4A-21
TARGET:      M6 Part 3 gate item 10 — the kill switch
TYPE:        INFERRED
STATEMENT:   Gate item 10 is one of the best findings in M6's audit — it correctly observes
             that a static marketing site's kill switch is *not* on the marketing site, and
             that the intuitive answer ("take the page down") is the slow one. **It answers the
             question for demo access only.** The gate item's wording is about credentials, and
             M6 followed it; but the marketing site's own highest-likelihood emergency is not
             an access incident, it is **a prohibited claim discovered live on `www`** — R-02,
             the register's only *High likelihood / Severe impact* risk. For that emergency the
             audit's own architecture gives no fast path: static build, protected `main`, one
             named reviewer, redeploy required (M4A-19). M6 identified all three pieces and did
             not assemble them.
             The cheap answer exists on either candidate host and belongs in the architecture,
             not in a runbook nobody has written: a single edge rule (Cloudflare Rules or a
             `vercel.json` route override) that 302s one URL to a holding page **without a
             build**, plus the named person who may pull it. That is minutes rather than a
             deploy cycle, and it is the same class of control M6 already recommends for the
             demo.
BASIS:       M6-architecture-audit.md Part 3 gate item 10 (**FAIL**), and its own reasoning
             ("with a **static** marketing site, taking the `/demo` page down … requires a
             rebuild and redeploy"). Gate item 10's text: CommitteeSystem.md:1274-1275.
             R-02: docs/Product-Positioning-and-GTM-Locked-Strategy.md:1174.
             The review gate that slows removal: M6-V10(1)–(4).
FALSIFIER:   A claim-removal path in M6's proposal that does not require a deployment. None is
             described.
CONFIDENCE:  Medium-High.
AUTHORITY:   HYPOTHESIS for the extension · LOCKED for §32's binding force on published copy
IMPACT IF WRONG: Medium-High. The whole positioning rests on D-1 — every claim carries its
             limitation. The time between discovering a §32 breach on a live page and removing
             it is a governance parameter nobody has set, and the architecture as audited makes
             it a deploy cycle.
STATUS:      SUSTAINED (coverage) — gate item 10's result should distinguish the *access* kill
             switch (answered) from the *claim* kill switch (unanswered).
```

```
ID:          M4A-22
TARGET:      M6-L10c and M6-O1 — "the candidate set of one", applied to M6's own option set
TYPE:        OBSERVED
STATEMENT:   M6-L10c's finding is that the hosting row "was decided against a candidate set of
             one." The finding is correct and well-made. **It also applies to M6's own remedy.**
             M6-O1 offers three options: Cloudflare, Vercel-static, Vercel-server. Two of the
             three are Vercel and the third is the vendor M6 nominated; the set is
             *one alternative plus the incumbent in two configurations*. Unevaluated, and named
             nowhere in the audit:
             (i) **GitHub Pages** — GitHub is already the org's source-control platform and is
             already in M6's own estate count, so this adds **zero** new vendors, against
             Cloudflare Pages' zero-new-vendors-but-a-new-product-surface.
             (ii) **Serving the static export from the Kingdom instance the organisation
             already operates.** This is the option that would **dissolve M6's own M6-V08(i)
             finding** — "'we host in Saudi Arabia' cannot be said of the marketing site under
             any Vercel configuration" — because under this option it could be said, truthfully,
             about the whole estate. M6 raised that constraint as a binding copy limitation
             and then did not evaluate the one option that removes it. The organisation
             demonstrably operates an Ubuntu host with systemd-managed backup, a background-
             service scheduler and monitoring; adding a vhost that serves a folder of static
             HTML is strictly less operational work than what it already runs.
             **The honest counter, stated because §8.6's cheapest-option rule cuts both ways:**
             §13.1's three-environment rule may forbid it. But §13.1's text is "**Do not build
             the marketing website inside OpenEMR**", and a separate vhost serving static files
             is not inside OpenEMR — it does, however, share a VM, a failure domain and an
             attack surface. **M6-S01 approved §13.1 as "the strongest rule in the document"
             and marked it PASS with "zero findings" without resolving whether it forbids
             co-location on one host or only co-location in one codebase.** That ambiguity is
             load-bearing for the option M6 did not evaluate, and it is the finding M6-S01
             missed.
BASIS:       M6-architecture-audit.md M6-L10c ("a candidate set of one"), M6-O1 options table,
             M6-V08(i), M6-S01 ("could not find a way to attack … Zero findings against this
             rule").
             §13.1's text: docs/Marketing Website/06-technical/Architecture.md:10-11.
             The demo host's existing operational surface: CommitteeSystem.md:921;
             docs/evidence/ubuntu-infra-scripts/.
FALSIFIER:   A statement in §13.1 or in RDY-0064 that forbids any non-OpenEMR service on the
             Dammam instance. §13.1 speaks of three *environments* and three data classes; it
             does not address host co-location, which is exactly the gap.
CONFIDENCE:  High that the options are unevaluated; **Medium** on whether either should win —
             that depends on operational facts only the Owner holds, and I am not selecting a
             host. **Host selection is M6's lane and I do not take it.**
AUTHORITY:   POLICY — I am recording that the remedy inherits the defect it names.
IMPACT IF WRONG: Medium. Being wrong costs two more rows in a comparison table. Being right
             means the audit's own headline structural finding was answered with a set that
             excludes the only option consistent with the audit's own residency posture.
STATUS:      SUSTAINED (coverage) — and M6-S01's PASS should carry the co-location ambiguity
             as its one finding rather than "zero findings".
```

```
ID:          M4A-23
TARGET:      M6-L06's parity change-my-mind condition
TYPE:        OBSERVED
STATEMENT:   M6's condition is "if the content set stays under ~20 files and one person owns
             both languages, a manual checklist is defensible." §13.5 has 17 routes. Under the
             per-locale reading the threshold is **already met** — 17 < 20 — and M6 required
             the build-time gate anyway; under the both-locales reading it is 34 and not met.
             M6 never says which, so the condition cannot do work in either direction. **This
             is the dispatch's second named failure shape exactly: a condition trivially
             already met where the verdict did not move.**
             The verdict itself is right and I reached the same conclusion independently
             (round-one M4-18), which is why I attack the condition and not the finding. One
             substantive addition M6's condition obscures: the threshold is the wrong variable.
             What determines whether parity can be enforced by hand is not file count, it is
             whether the content loader **falls back to English on a missing Arabic file**. With
             a fallback, no checklist and no gate detects drift, at any file count, because the
             site renders complete. Without one, a missing file is a build error at any count.
             M6's condition measures volume; the failure is structural.
BASIS:       M6-architecture-audit.md M6-L06 CHANGE MY MIND.
             Route count: docs/Marketing Website/06-technical/Architecture.md:111-128 (17).
             WEB-003: docs/Product-Positioning-and-GTM-Locked-Strategy.md:828-832.
             R-08: same file :1180 (Medium likelihood / High impact).
             My round-one finding on the fallback: M4-18 in
             `committee/M4-architecture-and-conversion.md`.
FALSIFIER:   A statement in M6-L06 resolving "content set" to one reading. There is none.
CONFIDENCE:  High on the ambiguity; High that fallback behaviour dominates file count.
AUTHORITY:   LOCKED (WEB-003) for the requirement · HYPOTHESIS for the condition's variable
IMPACT IF WRONG: Low-Medium. The gate is required under both readings, so the practical
             recommendation is unaffected. What is affected is whether the falsifier is real.
STATUS:      SUSTAINED (condition) · M6's verdict HOLDS
```

```
ID:          M4A-24
TARGET:      M6-O1 option 1 (Cloudflare) — tested against the locked IA and the funnel,
             which is the seam C-2 gives M4 and which M6 did not claim to cover
TYPE:        INFERRED
STATEMENT:   The dispatch asks whether M6's preferred host serves the locked IA and the funnel
             better, equally, or worse than Vercel. **My answer: equal on the IA, better on the
             funnel — and M6 argued its own case on weaker grounds than were available.**
             (a) **IA: equal, and I attacked this hardest because it is where a hosting change
             could quietly degrade the deliverable.** Every IA control I specified in round one
             — route parity between `content/en` and `content/ar`, claim-id set equality, the
             §32 banned-term lint, CODEOWNERS on `content/**`, the no-locale-fallback rule —
             lives in GitHub CI and in the content model, not on the host. Both candidates
             serve a static export of a locale-doubled route tree with per-host header and
             redirect rules. **I could not construct an IA requirement either platform fails.**
             HOLDS for M6.
             (b) **Funnel: better, for a reason M6 records elsewhere and does not use here.**
             M6's own gate item 3 observes that a Vercel-hosted form endpoint sits **outside**
             the Cloudflare rate limiter the demo already needs, so "one control now has to be
             configured twice, in two vendors, with two rule syntaxes" — and that this "lands
             on the one gate item already recorded as failing." Under M6-O2 option 2 the form
             POSTs to the Dammam host, which is already behind Cloudflare. If `www` is also
             behind Cloudflare, the marketing pages, the lead endpoint and the credential-request
             endpoint sit behind **one** WAF and one rule set. **That is a stronger argument for
             M6's own option 1 than the vendor-count argument M6 made**, and it is a funnel
             argument, not an operational-tidiness one.
             (c) **Worse in one respect M6 named abstractly and never instantiated.** M6's
             change-my-mind for L10c is "a written reason why two edge vendors is preferable to
             one — e.g. deliberate failure-domain separation." That reason is concretely
             available inside M6's own audit: M6-V06(d) shows a zone-level HSTS
             `includeSubDomains` change would force HTTPS on hosts that do not yet exist, and
             M6-B02 shows the demo's availability already depends on the Cloudflare proxy.
             Putting `www` in the same zone and account concentrates both. So the ranking is
             **under-determined rather than wrong**, and M6's own "Medium" confidence on it is
             the correct calibration.
BASIS:       M6-architecture-audit.md Part 3 gate item 3; M6-O1 option table and its closing
             argument; M6-L10c CHANGE MY MIND; M6-V06(d); M6-B02; M6-O2 option 2.
             My round-one IA controls: `committee/M4-architecture-and-conversion.md` §4.1, §4.3.
             Cloudflare is authoritative for the zone and fronts the demo — independently
             re-verified: curl -sS -I https://demo.skyeagle.uk/ → `Server: cloudflare`,
             `CF-RAY: a2def98dc832fafa-ORD` (2026-08-20).
FALSIFIER:   An IA or funnel requirement that one platform can serve and the other cannot. I
             looked for one across all 25 locked-IA nodes and both funnel routes and did not
             find it.
CONFIDENCE:  High on (a); Medium-High on (b); Medium on (c).
AUTHORITY:   HYPOTHESIS — host selection is M6's lane and the Owner's decision; this finding
             supplies the IA/funnel input C-2 assigns to M4 and takes no position on the vendor.
IMPACT IF WRONG: Low-Medium. If (a) is wrong and a host does degrade the IA, that would be the
             single most important thing in this attack and I would want it found. I could not
             find it, and I record the failed attack rather than implying I did not try.
STATUS:      HOLDS on the IA · M6's own argument WEAKENED by understatement on the funnel
```

```
ID:          M4A-25
TARGET:      M6-B01 — the demo session cookie
TYPE:        OBSERVED
STATEMENT:   Independently reproduced. The `OpenEMR` session cookie is set with neither
             `HttpOnly` nor `Secure`, while the adjacent `App` cookie in the same response
             carries `HttpOnly` — confirming M6's observation that this is an inconsistency
             within one response rather than a blanket setting. I attacked the finding by
             checking whether M6 had simply caught a stale response; it is reproducible on a
             fresh request more than twenty minutes later with a different session id.
BASIS:       curl -sS -I "https://demo.skyeagle.uk/interface/login/login.php?site=default",
             run 2026-08-20T05:24:58Z:
               Set-Cookie: App=OpenEMR; expires=Fri, 20 Aug 2027 05:24:58 GMT; Max-Age=31536000;
                 path=/; HttpOnly; SameSite=strict
               Set-Cookie: OpenEMR=k2oo5j28eadp2410ab3gunj09d; path=/; SameSite=Strict
             (M6 observed session id `ei21d6ugou86qm5itbvp3bum88` at 05:04:31Z — different
             session, same defect.)
FALSIFIER:   A response carrying `HttpOnly; Secure` on the `OpenEMR` cookie. One command.
CONFIDENCE:  High — reproduced.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Low. The value of reproducing it is that a finding raised outside the audit's
             own scope, which a reader might discount as opportunistic, is now independently
             confirmed by the agent briefed to attack it.
STATUS:      HOLDS — attacked by attempting to reproduce a staleness explanation; the attack
             failed and the finding stands.
```

```
ID:          M4A-26
TARGET:      M6-L13(i) and M6's Part 4 ledger
TYPE:        OBSERVED
STATEMENT:   Independently reproduced. `www.skyeagle.uk`, `staging.skyeagle.uk` and
             `app.skyeagle.uk` all return **NXDOMAIN**, and no `marketing-website/` directory
             exists in this repository. M6's central Part 4 conclusion — the demo half is built
             and the marketing half is entirely unbuilt — survives. I attacked it by checking
             whether the hosts might exist behind a resolver that Google's 8.8.8.8 does not
             see; NXDOMAIN is an authoritative negative from the zone's own Cloudflare
             nameservers via a public recursive resolver, not a timeout.
BASIS:       nslookup -type=A {www,staging,app}.skyeagle.uk 8.8.8.8 → NXDOMAIN ×3
               (run 2026-08-20). ls -d marketing-website → "No such file or directory".
             curl -sS https://demo.skyeagle.uk/robots.txt → "User-agent: * / Disallow: /"
               — M6's undocumented-control catch, also reproduced.
FALSIFIER:   A DNS record appearing for any of the three hosts.
CONFIDENCE:  High.
AUTHORITY:   EVIDENCE
IMPACT IF WRONG: Low. This is the part of M6's audit that is most straightforwardly checkable
             and it checks out. The greenfield conclusion — everything in Parts 1 and 2 is
             advice about a build that has not started, which is the cheapest moment for all of
             it — is correct and is the most useful sentence in the audit.
STATUS:      HOLDS
```

---

## 4 · COVERAGE — WHAT M6's OWN BRIEF ASKED FOR AND DID NOT GET A RESULT

§8.6 gives M6 "the whole exposure surface". Measured against that list, and against §7.4's rule that
adversary performance is coverage rather than objection count:

| §8.6 scope item | M6 result | Assessment |
|---|---|---|
| The stack, hosting and residency | 16 layer verdicts | **Complete** |
| Marketing-site / demo separation | M6-S01 | Complete, with M4A-22's co-location ambiguity unresolved |
| Build and deploy model | M6-V01 | Complete; basis weakened by M4A-04 |
| Bilingual routing mechanics | M6-V07 | **The audit's best work.** No attack landed |
| **Performance consequences** | *none* | **NO VERDICT** — M4A-20 |
| **SEO consequences** | 4 untested bullets in V07(d) | **NO VERDICT** — M4A-20 |
| Credentials and least privilege | gate 1, OPEN | Complete |
| Session and route control | gate 8, M6-B01 | Complete and good |
| Abuse and rate-limiting | gate 3, FAIL | Complete, and the two-syntax finding is under-used (M4A-24) |
| Reset atomicity and rollback | gate 6, OPEN | Complete — correctly scoped out |
| Concurrency | gate 7, OPEN | Complete |
| Synthetic-data invariant | gate 5, OPEN | Complete |
| Telemetry boundaries | gate 9, M6-V08, M6-L12 | Complete; premise tension at M4A-10 |
| Monitoring | M6-V09(d), Part 4 ledger | Thin but present |
| **Kill switch** | gate 10, FAIL | **Half-covered** — access yes, claim removal no (M4A-21) |
| Structured data (M4's lane, but a §32 surface) | *none* | Absent from both audits until round one (M4A-20) |

**Two areas with no verdict, one half-covered, out of sixteen.** That is good coverage and I say so.
It is not complete coverage, and M6's §8.2 self-check reports the acceptance criteria as fully met.

---

## 5 · THE CONVERGENCE AUDIT (R4) — WHERE I AGREED WITH M6, AND WHETHER IT WAS INDEPENDENT

The dispatch is right that these are the dangerous ones. Five substantive convergences:

| Convergence | Independent? | Test |
|---|---|---|
| **PDPL grep returns 4, not 0** | **YES — genuinely independent.** Neither agent could see the other; both were handed the same false premise and both ran the same one-line command. This is real corroboration and R4's signal applies | I ran `grep -c 'PDPL'` before reading M6's file, in round one |
| **MDX-in-Git is the right content model** | **Partly.** Both of us reasoned from the same two sentences (Architecture.md:47-48) plus §13.8 Q4. M6 reached "Git means every claim change is a PR that can be gated"; I reached "both language trees are diffable files, so parity is mechanically checkable." **Different arguments, same conclusion** — that is the stronger form of convergence, because the reasoning did not overlap | The two findings cite different downstream requirements (EV-003 vs WEB-003) |
| **`QualifiedClaim` is not an enforcement mechanism** | **YES.** M6-L05(a) and my M4-07 were written without sight of each other and reach the same conclusion from different directions — M6 from "content that USES it", I from an eleven-bypass enumeration. Neither was pointed at it by M0's pack, which classifies `QualifiedClaim` as CONSTRAINT-DETERMINED with no defect flagged | M0's pack item 11a records no objection to the component |
| **The `not-a-fit` route is missing** | **YES**, and reached from opposite ends: M6 arrived via §13.6 row 7's segment page, I via GTM §29's instrumentation. Same gap, two doors | M6 Part 5 row 7; my M4-05 and M4-14 |
| **§13.6 rows 3 and 7 lack a governing ID** | **NO — pointer-directed.** M0's pack named both rows before either agent looked. See M4A-17 | M0-decision-pack-task2.md §2 items 12g, 12h |

**The useful result for M0's §9:** three of five convergences are genuinely independent and should
carry the weight R4 allows; one is half-independent (same source, different reasoning); one is
anchored and must not be reported as three-way corroboration.

---

## 6 · ACCEPTANCE SELF-CHECK

| Criterion | Result |
|---|---|
| *"At least one substantive attack on M6's reasoning"* | **M4A-08** (the cheapest-ranked option in the audit's heaviest question is priced as a deferred P0 when it invalidates STEP0-001's D-a and returns the design to an option the Owner rejected) and **M4A-03** (the headline verdict's falsifier is constructed over a set from which the falsifier was removed). Twelve SUSTAINED in total |
| *"Every M6 layer verdict engaged"* | §1 — all 16 layer/rule verdicts plus all 10 Vercel findings, 3 briefing challenges, 2 built-half findings, 4 open questions and 3 option sets |
| *"Every change-my-mind condition tested"* | §2 — all 16, individually, with a verdict each: 7 SOUND · 1 weak-but-honest · 1 partial · **7 DEFECTIVE**, and the non-random pattern in where they cluster |
| *"A coverage statement"* | §4 — M6's own §8.6 scope list, item by item: two areas with no verdict, one half-covered |
| *HOLDS shown, not assumed* | 20 HOLDS, each naming the attack that failed — including two findings I reproduced from scratch (M4A-25, M4A-26) and one where I ran three separate attacks and lost all three (M4A-13) |
| §8.4: no reopening licence | M4A-08 and M4A-15 both stop at naming the consequence. **No reopening is proposed anywhere in this file** |
| §8.4: no re-architecting the locked IA | Nothing here changes WEB-002. M4A-16's answer *restores* a locked node; M4A-15 declines to add one |
| §8.4: no prohibited page | None designed, named as desirable, or implied. M4A-20 argues for *tighter* control of an unqualifiable claim surface |
| §8.4: self-disqualification preserved, not optimised | **M4A-08 is that constraint doing its job.** The cheapest-ranked option would have deleted the only reliable instrument for the locked metric, and this attack exists to stop it being adopted on a mispriced cost line |
| §12: options only for genuinely-open items | **No option sets are produced in this file.** An attack is not an option set; where M6's options are wrong I say why, and the remedy routes to M6, M2 or the Owner as the item requires |
| `EVIDENCE`: no capability the audit does not record | No finding here asserts or requires an OpenEMR product capability. `docs/HISModulesUsers.md` is not engaged |
| R9 | One file written. **No git command run.** M6's file read, not edited |

---

## 7 · DECLARATIONS

**Model ID declared in this agent's own runtime context (§5.1 D3b):** `claude-opus-5` (model name:
Opus 5). Assigned: Opus 5, Tier A. **Observed = assigned.** As M6 correctly notes, this is a
**runtime self-declaration, not an external observation**, and should be recorded as
`SELF-REPORTED (runtime-declared)` rather than represented as floor-verified. Parity against the
agent under attack: M6 self-declared the same model; the attacker is not below what it attacks.

**Finding count and §7.1 field compliance:**

- **Findings written: 26** (M4A-01 … M4A-26).
- **Findings carrying all nine §7.1 fields: 26 of 26.** Counted field by field.
- **Additional field used, declared rather than hidden:** every finding carries a **TARGET** line
  naming the M6 finding or verdict under attack, and its result token (**SUSTAINED / WEAKENED /
  HOLDS**) is carried in the STATUS field where §7.1's enum permits, or stated explicitly in STATUS
  where the result is not one of §7.1's four tokens. No required field is displaced.
- **The §1 summary table, the §2 condition table, the §4 coverage table and the §5 convergence table
  are result tables, not findings.** Each row is backed by a full-schema finding or by a re-runnable
  command shown in the row itself. I record that structure so the count above can be checked rather
  than taken on trust.
- **BASIS discipline:** every BASIS is a `file:line` **that I resolved myself with `sed -n`**, a
  re-runnable command with its run date, or a quotation from M6's own file. Given M4A-01 I re-resolved
  every one of my own pointers **twice** — once before writing and once after discovering the briefing
  had moved — and declared the file state they resolve against at the head of this file. **No finding here rests on recollection, and none rests
  on a platform fact I did not read** — where a Vercel behaviour mattered (M4A-10) I attacked the
  audit's internal consistency rather than asserting the platform fact.

**C-4 status:** `NOT CLAIM-REVIEWED`. This file feeds an Owner decision; M5's pass is required before
any sentence in it is used.

---

**END OF M4's ATTACK ON M6 — `COM-20260820-001`, TASK 2, committee C-2.**
**Result: 11 SUSTAINED · 8 WEAKENED · 21 HOLDS. The audit is substantially sound and its two
strongest structural findings survive; its falsifiers do not, and its cheapest-ranked option would
have reversed a locked decision.**
