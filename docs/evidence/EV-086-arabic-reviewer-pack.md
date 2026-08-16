# EV-086 — HR-09 ARABIC / RTL REVIEWER PACK (consolidated: RDY-0086, RDY-0087, RDY-0063)

**Prepared by:** Agent D (AGENT-ARABIC), 2026-08-16, per `docs/evidence/AGENT-CLAIMS.md` row
"0086, 0087, 0063 | Agent D (AGENT-ARABIC) | HELD — CONTINUATION."

**This is a consolidation, not new research.** It assembles existing measured evidence
(`EV-086-arabic-rtl-coverage.md`, Agent B, 2026-08-14) and an existing drafted disclosure
(`EV-RB14-mpdf-gpos.md` §6, Agent D, 2026-08-16) into the single reviewer-facing shape the Owner
asked for, with the reviewer-identity/basis-of-authority field HR-02 already establishes as the
pattern. No coverage number, no font test, and no disclosure text below was newly measured or
newly written by this pack — every figure and every English disclosure sentence is cross-referenced
to its source file, not restated from memory.

**Reviewer:** Mohammed Elfouly — **HR-09**, appointed 2026-08-14 (PB-061). Covers four items
(RDY-0086, RDY-0087, RDY-0063, RDY-0089) under one appointment; **this pack covers the first
three only**. RDY-0089 (Arabic *message design* / competitor review) is a separate, unrelated
research task and is out of scope here — see requirements doc line ~9861.

---

## CLOSURE DISCIPLINE — read this before anything else

- **Nothing in this pack closes RDY-0086, RDY-0087, or RDY-0063.** All three remain **NOT READY**
  until HR-09 completes an actual review and records a verdict below.
- **Every Arabic string in this pack is machine-assisted, not native or professionally
  translated.** Each one is individually marked:

  > **PREPARED, NOT REVIEWED. Awaiting HR-09.**

  Do not use any Arabic text in this pack in a demo, pilot, or customer-facing context until
  HR-09 has reviewed and either approved or replaced it.
- **HR-04's own rule applies here too** (requirements doc, HR-04 row): naming a reviewer is not
  a review, and preparing text is not reviewing it.

---

## 0. Reviewer identity — to be completed by HR-09

Mirrors the field set HR-02 §2 already uses for this exact purpose (requirements doc
line ~7256-7269). **"Basis of authority / responsibility" is a required field, not optional** —
per the Owner's own instruction recorded in the requirements doc (PB-061 sync, HR-09 row):
*"the reviewer should state their Arabic-language basis of authority in the first review record,
exactly what HR-02 already asks for."* HR-04 flags this pack's predecessor state as **"Competence
not evidenced by this document"** — this field is how that gap gets closed, not by asserting
competence on the reviewer's behalf.

| Field | Entry |
|---|---|
| Reviewer name | |
| **Basis of authority / responsibility** (native speaker / professional linguist / other — state which, and how) | |
| Role | |
| Organization | |
| Review date | |
| Items reviewed this pass (0086 / 0087 / 0063 — mark each) | |
| Verdict per item (PASS / FAIL / CONDITIONAL, with reasoning) | |
| Evidence reference | |

---

## 1. Arabic parity plan (RDY-0086)

### 1.1 What "parity" means here — stated explicitly because no numeric target exists

**No source document sets a numeric Arabic coverage target for the product.** The only
"parity" requirement found by grepping the full requirements document for `parity` is Phase 5
precondition #6 (§43.1, line ~10586):

> **"Arabic parity plan — with the 47.5 % limitation equally prominent in Arabic."**

That is a **disclosure-prominence requirement**, not a coverage threshold — it asks that the
known limitation be *stated* in Arabic as prominently as in English, not that coverage reach any
particular percentage. The document draws this distinction explicitly at line ~9858-9861:

| Layer | Requirement | Status |
|---|---|---|
| Website Arabic | Full Arabic/English parity **from launch** (WEB-003) | LOCKED FOR MVP, Phase 5 deliverable |
| **Product Arabic** | Application's actual localisation capability **and its limits**, disclosed | **READY WITH MANDATORY QUALIFICATION** — this phase (RDY-0086) |

So: the **website** carries a hard parity requirement (separate phase, not this pack). The
**product** carries no coverage target at all — its bar is accurate, equally-prominent
disclosure of whatever coverage exists. **If HR-09 believes a coverage target should exist, that
is a gap in the source documents to flag, not a number for this pack to invent.**

### 1.2 Measured coverage — which figure this pack uses, and why

The requirements document contains **two different coverage figures at two different layers**:

- **47.5 %** — UI chrome only (`lang_constants`, 6,291 of 13,235 defined) — the headline figure
  cited throughout the document (e.g. MC-06, D-5, §43.1).
- **16.1 %** — picklists (`list_options`, 700 of 4,346 distinct titles) — measured for the first
  time by `EV-086-arabic-rtl-coverage.md` (Agent B, 2026-08-14) §1, §2.1. The document's own prior
  text (§3.7, RDY-0086) asserted the picklist gap was "larger than 47.5 % implies" without ever
  measuring it; EV-086 supplies the actual number.

**This pack uses EV-086's full three-layer table**, not the 47.5 % figure alone, because EV-086
itself is explicit that 47.5 % is *"the best of the three layers, not the average, and a demo
audience sees all three at once"* (§1). Reproduced directly from `EV-086-arabic-rtl-coverage.md`
§1 (measured 2026-08-14, SQL in its §5):

| Layer | Total | With Arabic definition | Coverage |
|---|---:|---:|---:|
| UI chrome (`lang_constants`) | 13,235 | 6,291 | **47.5 %** |
| Picklists (`list_options`, distinct titles) | 4,346 | 700 | **16.1 %** |
| Layout field labels (`layout_options`, distinct titles) | 157 | 124 | **79.0 %** |

Plus: RTL ships as 7 prebuilt stylesheets / 4 RTL theme sources (EV-086 §2.3 — corrected from a
prior "13" figure; the change is the deliberate Q77 theme pruning, not a regression). RTL has
~20 code consumers; most of `patient_file/`, `reports/` and `billing/` uses hard-coded
left-alignment/float/table layout the RTL stylesheet does not fully invert (EV-086 §1, citing
Source B).

**The picklist layer (16.1 %) is the one a demo audience actually notices**, per EV-086 §2.1 —
picklists are what a user clicks (specialties, remit codes, statuses), so a screen that is 47.5 %
translated in its chrome can still hand the user an entirely English dropdown.

### 1.3 What is NOT done — outstanding before RDY-0086 can close

Per EV-086 §4 and its own §6 acceptance table, unchanged by this pack:

1. **The per-screen walk of D-1…D-5 and D-7 in Arabic has not been performed.** The coverage
   table above is a data-layer measurement (exhaustive, reproducible) — it is not a substitute
   for the screen-by-screen record RDY-0086's acceptance criteria require, and does not answer
   where RTL visually breaks on specific screens.
2. **No native Arabic reviewer has confirmed the qualification script.** This is HR-09's task.
   The qualification script itself (spoken before any Arabic switch, in English) is drafted at
   EV-086 §3 and is not reproduced here — read it there before reviewing.

---

## 2. Measured coverage limitation — stated explicitly in Arabic, equal prominence to English

Per §43.1's own wording ("equally prominent in Arabic") and RDY-0086's acceptance criteria. The
English statement is a direct restatement of §1.2 above; the Arabic statement is written as a
direct equivalent, not a summary, matching the convention EV-RB14 §6.2 already used for this
exact kind of dual-language disclosure.

**English:**

> Thiqa's Arabic interface is genuine, human-quality translation — not machine output — and
> covers 47.5 % of interface text (6,291 of 13,235 strings), with working right-to-left layout.
> That figure describes the interface frame only. Clinical picklists — specialties, statuses,
> remit codes — are the layer a user actually clicks, and only 16.1 % of those have an Arabic
> definition; 83.9 % remain English. Form field labels are better covered, at 79.0 %. Diagnosis
> and procedure descriptions (ICD/CPT/SNOMED) are English throughout, because those code sets are
> English by definition. Right-to-left layout is not uniform: it works on the main screens but
> roughly twenty code paths — mostly in patient records, reports and billing — still use
> hard-coded left-alignment that the RTL stylesheet does not fully invert.

**Arabic — ⚠ PREPARED, NOT REVIEWED. Awaiting HR-09.** Machine-assisted, not native or
professionally translated. Do not use in any demo, pilot, or customer-facing context until HR-09
reviews and either approves or replaces it:

> واجهة "ثقة" باللغة العربية ترجمة بشرية حقيقية وعالية الجودة — وليست ترجمة آلية — وتغطي 47.5% من
> نصوص الواجهة (6,291 من أصل 13,235 عبارة)، مع تخطيط يعمل من اليمين إلى اليسار بشكل صحيح. هذا الرقم
> يصف إطار الواجهة فقط. القوائم المنسدلة السريرية — التخصصات، الحالات، رموز المطالبات — هي الطبقة
> التي يتفاعل معها المستخدم فعليًا بالنقر، ولا تغطي الترجمة العربية منها سوى 16.1%؛ أي أن 83.9% منها
> لا تزال باللغة الإنجليزية. تسميات حقول النماذج مغطاة بشكل أفضل، بنسبة 79.0%. أما أوصاف التشخيصات
> والإجراءات (ICD/CPT/SNOMED) فهي بالإنجليزية بالكامل، لأن هذه الترميزات نفسها إنجليزية بطبيعتها.
> التخطيط من اليمين إلى اليسار غير موحّد: يعمل بشكل صحيح في الشاشات الرئيسية، لكن نحو عشرين مسارًا
> برمجيًا — معظمها في ملفات المرضى والتقارير والفوترة — لا يزال يستخدم محاذاة يسارية ثابتة لا تعكسها
> ورقة أنماط RTL بالكامل.

**Why this needs HR-09 specifically and not just any Arabic speaker**: the distinction between
"interface coverage" (47.5 %) and "what a user actually clicks" (16.1 %) is a framing choice that
determines whether the disclosure reads as honest or as minimizing — exactly the same risk
EV-RB14 §6.2 flags for its own draft text. Getting that framing wrong in Arabic undercuts the
disclosure it's meant to make.

---

## 3. RDY-0087 — Arabic PDF limitation (pulled directly from `EV-RB14-mpdf-gpos.md` §6, not rewritten)

**Source of record:** `docs/evidence/EV-RB14-mpdf-gpos.md` §6 ("Draft text for `RDY-0094` and
`RDY-0087`"), Agent D, 2026-08-16. That file's own header states this text is **"prepared for
Agent C to adopt, NOT signed"** and that the Arabic half specifically **"has not been reviewed by
HR-09."** This pack reproduces it verbatim for HR-09's convenience — it is cross-referenced here,
not re-drafted, per the task instruction not to rewrite from scratch.

**Underlying finding, for context (EV-RB14 §1-§2, §4):** the bundled PDF engine, mPDF 8.3.1,
cannot shape Arabic text — reproduced across **four independently-sourced Arabic OpenType fonts**
(Amiri, Noto Naskh Arabic, Scheherazade New, Lateef), all four failing identically with
`GPOS Lookup Type 5, Format 3 not supported`. Disabling OpenType layout avoids the exception but
produces disconnected, unjoined Arabic letterforms — assessed as **worse than no PDF at all**,
because it looks like a defective product rather than an absent feature. The Owner's decision
(EV-RB14 §4, 2026-08-16): **Option C — accept open through pilot, disclosed**, revisit only on a
customer contract requiring Arabic PDF output. This is a proven mechanism, not the earlier
"no font in the tree" characterization.

### 3.1 English (drafting basis for HR-09's review) — verbatim from EV-RB14 §6.2

> Thiqa's interface supports Arabic and right-to-left layout on the screens used in this demo. PDF
> documents — statements, prescriptions and printed letterheads — are not yet available in Arabic; this
> is a known limitation in the underlying PDF engine, not an incomplete translation, and we are tracking
> it openly rather than working around it in a way that would produce broken-looking output.

### 3.2 Arabic — ⚠ DRAFT, MACHINE-ASSISTED, UNREVIEWED — verbatim from EV-RB14 §6.2

**PREPARED, NOT REVIEWED. Awaiting HR-09.** Do not use in any demo, pilot, or customer-facing
context until HR-09 reviews and either approves or replaces it:

> تدعم واجهة ثقة اللغة العربية والتخطيط من اليمين إلى اليسار في الشاشات المستخدمة في هذا العرض. مستندات
> PDF — مثل الفواتير والوصفات الطبية والترويسات المطبوعة — غير متاحة بعد باللغة العربية؛ هذا قيد معروف في
> محرك PDF المستخدم، وليس ترجمة غير مكتملة، ونحن نسجل هذا القيد بشكل صريح بدلاً من التحايل عليه بطريقة قد
> تُنتج مخرجات تبدو معطوبة.

### 3.3 No-go register text (operator-facing, English only — verbatim from EV-RB14 §6.1)

> **Arabic PDF output (statements, prescriptions, letterheads, any Arabic-language export) — DO NOT
> ATTEMPT.** The bundled PDF engine (mPDF 8.3.1) cannot shape Arabic text: reproduced across four
> independently-sourced Arabic OpenType fonts (Amiri, Noto Naskh Arabic, Scheherazade New, Lateef), all
> four fail identically with `GPOS Lookup Type 5, Format 3 not supported`. **Forcing shaping off is not a
> fallback** — it produces a PDF that opens normally but renders Arabic as disconnected, unjoined
> letters, which reads as a broken product to any Arabic-literate viewer and is worse than declining the
> feature outright. If a prospect or pilot user asks for an Arabic PDF, state plainly that Arabic PDF
> export is not yet supported and is a known, tracked limitation (`RB-14`/`D-9`), not an oversight.
> **See `EV-RB14-mpdf-gpos.md` for the full evidence.**

**This is on RDY-0094's demo no-go register scope** (EV-RB14 §7) — HR-09's review of §3.1/§3.2
above is the input the no-go register's Arabic line depends on, but registering the no-go item
itself is a separate task, not this pack's.

---

## 4. RTL capture specification (RDY-0063)

### 4.1 Card wording, verified against the requirements document

Requirements doc, §8 item table, line ~1029 (verbatim):

> **RDY-0063** — *"Arabic/RTL equivalents of the capture set, with the untranslated elements
> visible rather than cropped out"* · Source: GTM WEB-003, D-5; audit §22.2 · Priority P1 ·
> Gates G6 · Owner: Arabic Reviewer · Depends on: 0060, 0086 · Status: **NOT READY**

Confirmed identical framing appears at line ~8991 ("Captures show untranslated elements rather
than cropping them out") and is restated as a rule in `EV-086-arabic-rtl-coverage.md` §3.1:
*"Do not crop untranslated elements out of screenshots — RDY-0063's rule. Cropping is the
concealment R-04 warns turns a certainty into damage."*

**The exact phrase in the task brief — "untranslated elements visible not cropped" — matches
this card's wording** ("untranslated elements visible rather than cropped out"); confirmed, not
paraphrased.

### 4.2 What needs Arabic/RTL screenshots

Per RDY-0086's own "Required action" (requirements doc line ~8882) — the same screen set that
governs the coverage walk in §1.3 above governs the capture set here, since RDY-0063 depends on
RDY-0086 (line ~1029, `Depends on: 0060, 0086`):

- **The D-1…D-5 and D-7 demo screen set** (the guided-demo path), captured in Arabic with RTL
  active.
- Every capture must show whatever untranslated picklists, labels, or code descriptions actually
  appear on that screen — **not cropped, not staged to hide them**. This is the direct
  operational form of the "16.1 % picklist gap" finding in §1.2: if a screen's picklist is
  English, the capture shows English.
- **Excluded from this capture set: any PDF render** (prescription, statement, letterhead).
  Per §3 above, mPDF cannot shape Arabic — a PDF capture would show broken, unjoined letterforms,
  which EV-RB14 §2.3 rates as a worse outcome than omitting the capture. If a PDF-adjacent screen
  must be captured for completeness, the broken PDF output itself should be captured **as the
  disclosed finding**, not silently substituted with an English PDF or omitted without note
  (EV-RB14 §7, RDY-0063 downstream-reach note).
- Per RDY-0086's required action, do **not** attempt remediation of any RTL breakage found during
  capture — Arabic completion is P1 (RDY-0098), out of scope here.

### 4.3 What is NOT done

**No Arabic/RTL captures have been taken.** This section is a specification for the capture
session (which screens, what rule governs cropping, what to exclude), not a completed capture
set. The capture session and RDY-0086's per-screen walk (§1.3) are naturally the same session —
EV-086 §4 already notes both are "blocked on the same manual browser session already outstanding
for RDY-0013/0014/0015, RDY-0042 and RDY-0016's UI legs."

---

## 5. What HR-09 needs to do first

1. **Complete §0** — name, and specifically the **basis of authority** field (native speaker /
   professional linguist / other, stated plainly). This is required before any verdict below
   carries weight, per the Owner's own instruction and HR-02's existing pattern.
2. **Review §2's Arabic coverage-limitation text** for accuracy and tone — does it read as
   disclosure or as apology (RDY-0086's own acceptance bar), and does the 16.1 %/47.5 %/79.0 %
   distinction come through correctly in Arabic.
3. **Review §3.2's Arabic PDF-limitation text** (from EV-RB14) on the same basis.
4. Separately from this pack — **the per-screen Arabic/RTL walk (§1.3) and the RTL capture
   session (§4.3) still need to happen**; HR-09's review pack covers the disclosure text, not the
   screen walk itself, which requires a live browser session.
5. **Record a verdict per item** (0086 / 0087 / 0063) in §0 — PASS, FAIL, or CONDITIONAL, with
   reasoning. None of the three closes without it.

---

## 6. Cross-reference index

| Source | What was pulled | Section here |
|---|---|---|
| `EV-086-arabic-rtl-coverage.md` §1, §2.1, §2.3 (Agent B, 2026-08-14) | Three-layer coverage table, RTL stylesheet count | §1.2, §2 |
| `EV-086-arabic-rtl-coverage.md` §3.1, §4 | Cropping rule, outstanding screen walk | §1.3, §4.1, §4.3 |
| `EV-RB14-mpdf-gpos.md` §1, §2, §4 (Agent D, 2026-08-16) | mPDF Arabic-shaping finding, Owner's Option C decision | §3 (context) |
| `EV-RB14-mpdf-gpos.md` §6.1, §6.2 | Draft no-go and disclosure text, verbatim | §3.1-3.3 |
| Requirements doc line ~1029 | RDY-0063 card wording | §4.1 |
| Requirements doc line ~1083 | RDY-0087 card wording | §3 |
| Requirements doc line ~8877-8886 | RDY-0086 card, required action | §1.3, §4.2 |
| Requirements doc line ~10586, §9858-9861 | Phase 5 parity precondition — no numeric target | §1.1 |
| Requirements doc line ~7256-7269 (HR-02 §2) | Reviewer-identity field pattern, incl. basis of authority | §0 |
| Requirements doc line ~4524-4526, 7549 (HR-09 row / PB-061 sync) | Basis-of-authority instruction, HR-09 appointment | §0 |

**`Blocks`:** RDY-0086 → G1 G5 · RDY-0087 → G1 G5 · RDY-0063 → G6. No gate count moved by this
pack (§0.0 Rule 3 — gate counts are recalculated only in a dedicated sync pass).

**Status: RDY-0086, RDY-0087, RDY-0063 all remain NOT CLOSED.** This pack assembles what HR-09
needs; it performs none of HR-09's review.
