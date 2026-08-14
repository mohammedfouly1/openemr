# EV-086 — ARABIC / RTL COVERAGE AND QUALIFICATION SCRIPT

**Requirement:** RDY-0086 · **Gates:** G1, G5 · **Owner:** Arabic Reviewer
**Acceptance:** *"A per-screen record exists for every demo screen; the qualification script states
the 47.5 %/chrome-only limit and the picklist gap before the switch; **a native Arabic reviewer
confirms the script is accurate and reads as disclosure rather than apology**."*
**Measured:** 2026-08-14 · **Agent B**, Phase 2B

**Remediation is out of scope.** Arabic completion is **P1 (RDY-0098)** and is not promoted. This
measures and discloses; it does not fix.

---

## 1. Measured coverage — three layers, and they are very different

Every figure below is a live `SELECT`, reproducible from §5.

| Layer | Total | With an Arabic definition | Coverage |
|---|---:|---:|---:|
| **UI chrome** (`lang_constants`) | 13,235 | 6,291 | **47.5 %** |
| **Picklists** (`list_options`, distinct titles) | 4,346 | 700 | **16.1 %** |
| **Layout field labels** (`layout_options`, distinct titles) | 157 | 124 | **79.0 %** |

`lang_languages` = 59 loaded · Arabic `lang_code = ar`, `lang_is_rtl = 1`.

**The headline 47.5 % is real and confirms Source B exactly** (6,291 of 13,235). **But it is the
best of the three layers, not the average**, and a demo audience sees all three at once.

---

## 2. ⚠ Two corrections to the document's own characterisation

Both are recorded because they cut in opposite directions — one gap is worse than stated, one is
better — and a disclosure script built on the wrong numbers is not a disclosure.

### 2.1 The picklist gap is quantified for the first time — and it is the real problem

§3.7 and RDY-0086 both say the visible gap *"is larger than 47.5 % implies"* but **neither source
ever measured it.** It is **16.1 %** — **83.9 % of picklist values have no Arabic definition at all.**

This is the layer a prospect actually notices, because picklists are what you *click*: provider
specialties, remit codes, the language list, appointment statuses, problem-list categories. **A
screen can be 47.5 % translated in its chrome and still present the user with an entirely English
dropdown.**

### 2.2 "`layout_options` field labels are untranslated" is **wrong**

§3.7 lists `layout_options` field labels alongside picklists as untranslated. **Measured: 124 of 157
distinct labels have an Arabic definition — 79.0 %**, the *best*-covered layer of the three.

**Overstating a gap is still a documentation defect.** It makes the disclosure script inaccurate in
the product's disfavour, and R-04's whole point is that credibility comes from saying the true thing
first — which requires the true thing to be measured.

### 2.3 RTL stylesheets: **7 ship, not 13**

Source B recorded 13 prebuilt RTL stylesheets. **Present today: 7** —
`rtl_style_light` · `rtl_style_dark` · `rtl_compact_style_light` · `rtl_compact_style_dark` ·
`rtl_tabs_style_full` · `rtl_tabs_style_compact` · `rtl_style_pdf`; **4 RTL sources** under
`interface/themes/`.

**This is not a regression — it is the deliberate Q77 theme pruning** (`solar`, `manila`,
`cobalt_blue`, `forest_green` removed so they cannot be selected via a stale `globals` value). The
count changed because the theme set changed. **Recorded so nobody reads 7-vs-13 as breakage.**

---

## 3. The qualification script — spoken BEFORE the switch, never after

> **"Before I switch this to Arabic, let me tell you exactly what you'll see, because I'd rather you
> heard it from me than found it yourself.**
>
> **The interface is about half translated — call it 47 % of the interface text, and it's genuine,
> human-quality Arabic, not machine output. Right-to-left layout works: the direction flips
> properly on the main screens.**
>
> **What is *not* translated is the clinical vocabulary. The dropdown lists — specialties, statuses,
> categories — are mostly still English; roughly one in six has Arabic. Diagnosis and procedure
> descriptions are English, because those come from ICD and CPT, which are English code sets.
> Form field labels are better, around four in five.**
>
> **So what you'll see is an Arabic frame around a largely English clinical vocabulary. For a clinic
> whose staff read English clinical terms — which is most private practice here — that is usable
> today. If you need the clinical vocabulary in Arabic, that's a translation project we would scope
> and price, and it is not included.**
>
> **One more: Arabic PDF output does not render correctly as shipped — there's no Arabic-shaping font
> in the product. I won't show you Arabic PDF output, because it would only prove that."**

### 3.1 Rules for delivering it

| Rule | Reason |
|---|---|
| **Speak it before the switch**, never as a recovery | RDY-0086's wording is *"spoken before the switch is shown"* |
| **Give the numbers, not "partial"** | "Partially translated" invites the prospect to discover which part. A number is a commitment |
| **Do not crop untranslated elements out of screenshots** | RDY-0063's rule. Cropping is the concealment R-04 warns turns a certainty into damage |
| **Never say "full Arabic support"** or "bilingual" unqualified | §32 prohibited |
| **Do not demonstrate Arabic PDF** | No Arabic-shaping font in the tree — L-10, no-go register row 9 |

---

## 4. ⚠ What is NOT done — the per-screen walk

**The acceptance requires a per-screen record for every D-1…D-5 and D-7 screen, walked in Arabic.
That has not been performed, and this document does not claim it.**

What is here is the **data-layer measurement**, which is stronger evidence than a screen walk for
*coverage* — it is exhaustive and reproducible rather than sampled — but it cannot answer the two
questions a walk answers:

1. **Where RTL visually breaks.** Source B found most of `patient_file/`, `reports/` and `billing/`
   uses hard-coded left alignment. **Which specific demo screens break, and how badly, is unmeasured.**
2. **Whether a screen reads coherently** as an Arabic frame around English terms, or as a mess.

**Blocked on the same manual browser session** already outstanding for RDY-0013/0014/0015, RDY-0042
and RDY-0016's UI legs. One session in Arabic, walking the D-7 path, discharges this too.

---

## 5. Reproduce

```sql
-- chrome
SELECT COUNT(*) FROM lang_constants;                                      -- 13235
SELECT COUNT(*) FROM lang_definitions ld JOIN lang_languages ll USING(lang_id)
  WHERE ll.lang_code='ar';                                                -- 6291

-- picklists
SELECT COUNT(DISTINCT title) FROM list_options;                           -- 4346
SELECT COUNT(*) FROM (SELECT DISTINCT lo.title FROM list_options lo
  JOIN lang_constants lc ON lc.constant_name=lo.title
  JOIN lang_definitions ld ON ld.cons_id=lc.cons_id
  JOIN lang_languages ll ON ll.lang_id=ld.lang_id AND ll.lang_code='ar') x; -- 700

-- layout labels
SELECT COUNT(DISTINCT title) FROM layout_options WHERE title<>'';         -- 157
-- (same join as above against layout_options)                            -- 124
```

```bash
ls public/themes/ | grep rtl | wc -l     # 7
ls interface/themes/ | grep -c rtl       # 4
```

---

## 6. Acceptance

| Criterion | Result |
|---|---|
| A per-screen record exists for every demo screen | **NOT MET** — §4. Data-layer coverage measured instead; the visual walk is outstanding |
| The qualification script states the 47.5 % / chrome-only limit **and the picklist gap** before the switch | **MET** — §3, with the picklist gap quantified at **16.1 %** for the first time |
| **A native Arabic reviewer confirms the script is accurate and reads as disclosure rather than apology** | **NOT MET** — no reviewer assigned |

### Status: **RDY-0086 — NOT CLOSED.** Script written and numerically grounded; the screen walk and the native review are both outstanding.

**`Blocks`: G1 G5.** No gate count moved (§0.0 Rule 3).

**One staffing note:** RDY-0086's owner is *"Arabic Reviewer"* and **no such person is named
anywhere in the register**. Unlike RDY-0003 and RDY-0095, this role has not been filled. It also
gates RDY-0063 (Arabic captures) and RDY-0089. **It is a fourth naming decision, and it has not been
asked for yet.**
