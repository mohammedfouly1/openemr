# EV-RB14 — mPDF GPOS incompatibility: reproduction, scope correction, and the four options

**Author:** Agent D (Claude Code), 2026-08-16. Written as its own evidence file rather than edited into
`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` deliberately — that file is Agent C's active
surface this session, and this finding is large enough to deserve its own artefact rather than a PB
entry racing a concurrent editor.

**Status: DECISION PENDING WITH THE OWNER.** Nothing below picks a path. §3 lays out four options with
costs and blast radii; the Owner decides.

---

## 1. The most useful sentence in this file

**This is an mPDF capability gap, not an Amiri defect.** RB-14's recorded resolution (2026-08-10)
diagnosed the vendored Amiri font as incompatible with the bundled mPDF and recommended, as "the
cheapest correct path," switching to Noto Naskh Arabic — the other font `Q25` names, requiring no ADR
because it was already inside the locked decision. **That recommended fix does not work.** Noto Naskh
Arabic fails against the same mPDF build with the identical error. Q25's *entire* named set of
solutions — both fonts it lists — is incompatible with the PDF engine actually vendored in this
product. The problem is not "which Q25 font", it is "mPDF 8.3.1's TTF/OpenType parser does not
implement this GPOS lookup format."

---

## 2. Reproduction

**Method**: the identical instrument RB-14's original finding used — register the font in mPDF's
`fontdata`, render a short RTL Arabic string, sweep `useOTL` across the same four values, in the same
order, with cache cleared between runs. Reused deliberately so the two results are directly comparable,
not two different tests reaching a coincidentally similar conclusion.

**Environment**: mPDF `v8.3.1` (confirmed via `composer.lock`), this host's native PHP 8.3.33 stack.

### 2.1 Amiri (RB-14's original finding, 2026-08-10 — restated here for the side-by-side)

| `useOTL` | Result |
|---|---|
| `0xFF` (full OpenType layout) | **FAIL** — `GPOS Lookup Type 5, Format 3 not supported (ttfontsuni.php)` |
| `0x03` | **FAIL** — same |
| `0x01` | **FAIL** — same |
| `0x00` (OTL disabled) | OK, 17,826 bytes — but Arabic unshaped |

### 2.2 Noto Naskh Arabic (this session, 2026-08-16)

Font sourced from Google Fonts' canonical repository — `github.com/google/fonts`,
`ofl/notonaskharabic/NotoNaskhArabic[wght].ttf` (a variable font, 307,592 bytes) plus its `OFL.txt`,
served under the SIL Open Font License, matching the licensing basis `Q25` already assumes for this
family.

| `useOTL` | Result |
|---|---|
| `0xFF` (full OpenType layout) | **FAIL** — `GPOS Lookup Type 5, Format 3 not supported (ttfontsuni.php)` |
| `0x03` | **FAIL** — same |
| `0x01` | **FAIL** — same |
| `0x00` (OTL disabled) | OK, 14,076 bytes — but Arabic unshaped |

**Identical error text, identical pattern across all four settings, on a font from a different foundry,
different license steward, different build toolchain than Amiri.** Two independent fonts hitting the
exact same unsupported-lookup-format exception is strong evidence the gap is in mPDF's parser, not in
either font.

**Verified against mPDF's own current source, not assumed to be fixed upstream**: the identical
unsupported-format `throw` (same message template, same file reference) is present unchanged in
`mpdf/mpdf`'s `development` branch on GitHub as of this session — i.e., the parser gap has not been
closed in mPDF's own most recent code, let alone in any tagged release. This bears directly on Option A
in §3.

### 2.3 Why `useOTL=0x00` is not a usable fallback, restated with more precision

`useOTL=0x00` produces a PDF with no exception — 14,076–17,826 bytes, a plausible-looking file size.
**But the Arabic text renders as disconnected, unshaped letterforms** — each Arabic letter has multiple
positional forms (isolated/initial/medial/final) that OpenType shaping selects and joins; disabling OTL
skips that entirely, so every letter renders in its isolated form with no joining. **This is worse than
producing no PDF at all**: a missing feature reads as "not built yet"; a document that opens, has a
plausible file size, and displays broken, unjoined Arabic reads as **a defective, embarrassing product**
to any Arabic-literate reader — a category of failure explicitly worse for a clinical document than an
absent one, because a clinician or patient may still try to read it.

### 2.4 The `fonttools`-instanced static font — NOT ISOLATED, DO NOT BUILD ON

To test whether the *variable*-font wrapper itself (rather than the underlying glyph-positioning
tables) was the trigger, this session used `fonttools varLib.instancer` to extract static Regular
(`wght=400`) and Bold (`wght=700`) instances from the Noto Naskh variable font, then re-ran the same
mPDF probe against them.

**Result: the probe hung indefinitely** (no exception, no output, no completion) rather than failing
cleanly or succeeding. Stopped manually after ~90 seconds of no progress rather than left running.

**This is explicitly not evidence of anything and must not be cited as such.** Three candidate causes,
none ruled out:

1. **An artefact of the instancing process** — `fonttools`'s instancer log showed it "Instantiating GDEF
   and GPOS tables" while producing the static file, which could have produced a technically-valid but
   structurally unusual table that a naive parser loops on rather than rejects.
2. **A `fonttools` version/behaviour issue** — this session installed `fonttools` fresh via `pip` with
   no version pin and no prior validation against mPDF-class parsers.
3. **An mPDF hang on some class of static Arabic font unrelated to the above** — mPDF's TTF parser
   could have a genuine infinite-loop defect distinct from the clean-exception gap in §2.1–2.2.

**None of these was isolated.** Do not treat "static instances hang" as a finding equivalent to the
clean, reproduced, cross-font failure in §2.1–2.2. If a future session wants to pursue static Arabic
faces, it should start by re-testing with a static face obtained directly from a font vendor (not
instanced from a variable font by this session's ad hoc tooling) before drawing any conclusion.

---

## 3. What was NOT touched, and why that matters

- **`src/Pdf/Config_Mpdf.php` is unchanged.** It is the config for every mPDF document in OpenEMR —
  statements, reports, prescriptions, letterheads. Registering a font face that throws on the shaping
  settings this product would actually use means the **first** Arabic PDF anyone renders becomes a fatal
  error, not a degraded one. RB-14's original caution (2026-08-10) not to ship a written-but-untested
  registration was correct then and is preserved here.
- **The font files live outside the repository**, at `C:\openemr-stack\scratch-fonts\` (this host's
  local stack directory, not under version control).
- **The probe script lives in `scratchpad/`** (gitignored per `.gitignore:/scratchpad/`), so it cannot
  be committed by accident and carries no claim of being a tracked, re-runnable harness.
- **No dependency was changed.** `composer.json`/`composer.lock` are untouched; `mpdf/mpdf` stays at its
  currently locked `v8.3.1`.

---

## 4. DECISION — 2026-08-16 (Owner)

**ADOPTED: Option C — accept open through pilot.**

> Rationale: the GTM already excludes Arabic PDF from every demo and RDY-0087 already requires the
> limitation be stated in Arabic. This decision does not add a constraint; it replaces an inherited
> claim (Source B L-10) with a reproduced mechanism. Option A is a fork-owned parser patch — not a
> version bump — against a dependency touching all 55 reports, prescriptions and letterheads, for a
> feature we do not demonstrate. That regression risk is not purchasable by any current commercial
> need.
>
> **Revisit trigger: a customer contract requiring Arabic PDF output. None exists.**

**Option B — time-boxed parallel test, executed same session, now CLOSED (tested-and-negative).**

Two OFL faces chosen for being more traditional/established Naskh designs than Amiri or Noto Naskh
Arabic, on the hypothesis that an older or simpler-construction face might predate the unsupported GPOS
table: **Scheherazade New** and **Lateef**, both sourced the same way as §2.2 (Google Fonts' canonical
`google/fonts` repository, OFL-licensed, static Regular/Bold faces — no `fonttools` instancing involved
this time, so the §2.4 uncertainty does not apply to these results).

Same instrument, same `useOTL` sweep, same test string (containing initial/medial/final joining forms
and a mandatory lam-alef ligature, `لا`):

| Font | `0xFF` | `0x03` | `0x01` | `0x00` |
|---|---|---|---|---|
| **Scheherazade New** | FAIL — `GPOS Lookup Type 5, Format 3 not supported` | FAIL — same | FAIL — same | OK, 40,178 bytes, unshaped |
| **Lateef** | FAIL — `GPOS Lookup Type 5, Format 3 not supported` | FAIL — same | FAIL — same | OK, 36,998 bytes, unshaped |

**Both candidates fail on shaping quality — identically to Amiri and Noto Naskh Arabic.** Per the
instruction, this is reported as a failure regardless of the clean `0x00` output, because `0x00`
produces unshaped Arabic (§2.3), not a passing result. **Four of four independently-sourced Arabic
OpenType fonts now fail this mPDF build the same way** — stronger evidence than the two-font minimum
requested, and it raises confidence that this is close to a universal limitation of mPDF 8.3.1's GPOS
parser against modern Arabic fonts generally, not a property of any specific four fonts sampled.

One methodology note, recorded honestly: a visual rendered sample of the unshaped output (requested as
part of this test) could not be produced — `magick.exe` is present on this host but has no Ghostscript
PDF delegate installed, so PDF-to-image rasterization failed (`gswin64c.exe` not found). The unshaped
finding rests on the documented mechanics of Arabic joining behaviour (disabling OTL skips
positional-form substitution entirely, per §2.3) and on the qualitative description already recorded in
RB-14's original 2026-08-10 finding, not on a screenshot from this pass. Flagged rather than silently
omitted.

**Per the instruction: `Config_Mpdf.php` was not touched under this outcome.** Neither candidate passed,
so there is no amendment to propose to Option C's disclosure; C stands as adopted above.

---

## 5. The four options — costs and blast radii (retained for the record; A and D remain deferred, not chosen)

### Option A — Upgrade `mpdf/mpdf`

**Checked before pricing, per the instruction not to price an option that doesn't exist**: the exact
unsupported-lookup-format code path that produces this error is present, unchanged, in `mpdf/mpdf`'s
current `development` branch on GitHub — i.e. ahead of every tagged release including whatever the next
release will be. **There is no version of mPDF, released or in development, currently known to fix
this.** This is not confirmed by reading every changelog entry line by line — it is confirmed by reading
the actual parser source at the tip of the project's own repository and finding the same `throw`
statement.

- **Commits us to**: waiting on an upstream mPDF fix that does not exist yet, with no visibility into
  whether or when it will, **or** authoring and maintaining a fork-owned patch to mPDF's own TTF/OpenType
  parser — putting a project-maintained patch on the critical path of every PDF the product generates.
- **Cost**: unbounded if waiting on upstream; high and ongoing if forking mPDF's parser (a complex
  binary-format parser, not a small patch surface).
- **Blast radius**: every PDF in the product — all 55 reports, prescriptions, letterheads, CCDA exports.
  Any version bump (even one that *did* fix this) needs a full PDF regression pass, not just an Arabic
  one, because mPDF's output for every existing English/Latin document could shift with a major version
  change.
- **Forecloses**: nothing directly, but consumes the engineering budget this decision is meant to bound.
- **Verdict as an option, not a recommendation**: **not currently viable as "upgrade and it's fixed."**
  Only viable as "fork mPDF's parser," which is a materially different and larger commitment than the
  phrase "upgrade mpdf" suggests.

### Option B — A third font family

**Honestly, this is sampling the same population.** Two independent, well-established Arabic OpenType
families (Amiri — a widely used, actively maintained naskh revival; Noto Naskh Arabic — Google's own
reference Arabic face) have now both failed identically. A font that happens to work is most likely one
built with older, simpler OpenType tables that predate the specific GPOS construct mPDF's parser lacks —
not a font chosen for design merit, but for **accidentally avoiding a table format**.

- **Commits us to**: testing candidate fonts one at a time against the exact probe in §2, each one
  costing a licence check and a shaping-quality read (not just "no exception" — a font that parses but
  shapes Arabic poorly is not a win; see §2.3 on why unshaped/malformed output is worse than none).
- **This session tested exactly two** (§2.1–2.2), both failed. **No third candidate has been tested.**
  If this option is chosen, the next step is naming and testing specific candidates — e.g. an older,
  simpler-construction face (something SIL-licensed and static-only, not a modern variable font, is the
  most promising class based on this session's reasoning, but this is a hypothesis, not evidenced) —
  before any claim that "font C works" is made.
- **Cost**: bounded per font (a probe run costs minutes), but the number of fonts needed to find a
  compatible one is unknown — could be the third try, could be the tenth.
- **Blast radius**: contained to the Arabic PDF path if the eventual font is wired carefully (per the
  same caution as §3) — does not touch English/Latin PDF output at all.
- **Forecloses**: nothing.

### Option C — Accept open through pilot, disclosed

- **Commits us to**: Arabic PDF output entering `RDY-0094`'s demo no-go register, the limitation stated
  explicitly in Arabic (per `RDY-0087`'s own requirement — the disclosure must be in the language it
  concerns, not just English), and `RB-14`/`D-9` staying open with a named owner rather than silently
  dropped.
- **Cost**: near-zero engineering cost now. Real commercial cost if the ICP treats Arabic PDF output
  (prescriptions, statements) as table-stakes rather than a disclosed gap — this is a GTM/market
  judgement this file does not make.
- **Blast radius**: none to the product. Reputational/commercial risk is bounded by how clearly and how
  early the limitation is disclosed to a prospect or pilot customer, which is exactly what `RDY-0087`'s
  existing requirement already demands.
- **Forecloses**: nothing technically — Option A, B, or D can still be pursued later, on the same
  disclosed-and-open basis RB-14 has held since 2026-08-10.

### Option D — A non-mPDF path for Arabic PDF specifically

The `dompdf` half of `Q25` is separately, entirely untouched — RB-14's original resolution already
noted it has exactly one call site (`EncountermanagerTable.php:201`, CCDA→PDF) with no `Options`
constructed at all, so it currently has zero Arabic capability of its own, tested or not.

- **Commits us to**: testing whether `dompdf` (a different rendering engine with a different, CSS-driven
  text-layout model, not the same binary TTF/GPOS parser mPDF uses) can shape Arabic at all, and — if
  so — scoping which PDF outputs would move to it. This is **not** a drop-in swap; every Arabic PDF
  output currently assumed to go through mPDF would need to be re-routed, and dompdf's own Arabic/RTL
  support has not been evaluated by this session or, as far as this file's evidence goes, by any prior
  one.
- **Cost**: unknown until dompdf is actually tested against Arabic shaping — this session did not test
  it. Likely non-trivial: dompdf is architected differently from mPDF and re-routing specific report
  types is real integration work, not a config change.
- **Blast radius**: scoped to whichever specific PDF outputs move, if the approach works at all — could
  be genuinely narrow (only new Arabic-specific templates) or could require touching every report that
  might ever need Arabic output, depending on how "Arabic PDF" is scoped.
- **Forecloses**: nothing that Option A/B don't also leave open, but adds a second PDF-generation code
  path to maintain if adopted only partially.

**The strongest argument against Option C** (the option this file's author would lean toward only
because it costs nothing and forecloses nothing else) **is that it has already been the status quo since
2026-08-10, and the product's Arabic PDF story has not moved in the six days since** — "stays open,
disclosed" is easy to keep choosing indefinitely, and at some point that stops being a deferred decision
and starts being the de facto answer without anyone having actually decided it.

---

## 6. Draft text for `RDY-0094` and `RDY-0087` — prepared for Agent C to adopt, NOT signed

**Neither text below is authoritative.** Both are drafted here so Agent C (or whoever next syncs the
register) has ready language rather than a blank requirement. The Arabic text specifically **has not
been reviewed by HR-09** (Mohammed Elfouly, appointed 2026-08-14 per PB-061, Arabic/RTL reviewer,
"awaiting review" — competence basis not yet stated, per HR-04's own flag). **Preparing this text is not
reviewing it.** It must carry HR-09's review before any customer or demo-facing use, exactly as HR-04's
governing rule already requires for every other item in that register.

### 6.1 `RDY-0094` no-go register entry (operator-facing, English only — internal instrument)

> **Arabic PDF output (statements, prescriptions, letterheads, any Arabic-language export) — DO NOT
> ATTEMPT.** The bundled PDF engine (mPDF 8.3.1) cannot shape Arabic text: reproduced across four
> independently-sourced Arabic OpenType fonts (Amiri, Noto Naskh Arabic, Scheherazade New, Lateef), all
> four fail identically with `GPOS Lookup Type 5, Format 3 not supported`. **Forcing shaping off is not a
> fallback** — it produces a PDF that opens normally but renders Arabic as disconnected, unjoined
> letters, which reads as a broken product to any Arabic-literate viewer and is worse than declining the
> feature outright. If a prospect or pilot user asks for an Arabic PDF, state plainly that Arabic PDF
> export is not yet supported and is a known, tracked limitation (`RB-14`/`D-9`), not an oversight.
> **See `EV-RB14-mpdf-gpos.md` for the full evidence.**

### 6.2 `RDY-0087` Arabic disclosure text — spoken before any Arabic-surface switch is shown

**English (drafting basis for HR-09's review):**

> Thiqa's interface supports Arabic and right-to-left layout on the screens used in this demo. PDF
> documents — statements, prescriptions and printed letterheads — are not yet available in Arabic; this
> is a known limitation in the underlying PDF engine, not an incomplete translation, and we are tracking
> it openly rather than working around it in a way that would produce broken-looking output.

**Arabic — ⚠ DRAFT, MACHINE-ASSISTED, UNREVIEWED. Do not use in any demo, pilot, or customer-facing
context until HR-09 reviews and either approves or replaces it. Recorded here only so HR-09 has a
starting point rather than a blank page, per the instruction to prepare and not sign:**

> تدعم واجهة ثقة اللغة العربية والتخطيط من اليمين إلى اليسار في الشاشات المستخدمة في هذا العرض. مستندات
> PDF — مثل الفواتير والوصفات الطبية والترويسات المطبوعة — غير متاحة بعد باللغة العربية؛ هذا قيد معروف في
> محرك PDF المستخدم، وليس ترجمة غير مكتملة، ونحن نسجل هذا القيد بشكل صريح بدلاً من التحايل عليه بطريقة قد
> تُنتج مخرجات تبدو معطوبة.

**Why this text specifically needs native review, stated plainly rather than left implicit**: this
session's Arabic is machine-assisted, not a native or professionally translated rendering. Technical
terms (*"PDF engine limitation," "known limitation" vs. "incomplete translation"*) carry a distinction
this draft is trying to preserve — that the gap is engineering, not effort — and getting that framing
wrong in Arabic would undercut the exact disclosure it's meant to make. **Per G6's Arabic-parity
precondition, this text must appear at equal prominence to the English version, not as a smaller or
later addition** — the draft above is written as a direct equivalent for that reason, not a summary.

---

## 7. Downstream reach — this is bigger than one ticket

- **`RDY-0087`** — the Arabic PDF limitation now has **a proven mechanism** (a specific mPDF parser gap,
  reproduced across four independently-sourced fonts, not once but four times), not an inherited claim
  from RB-14's audit trail. **Stated plainly so this doesn't read as bad news only: this is a
  strengthening of RDY-0087's evidence basis, not a new problem.** Before this session, RDY-0087 rested
  on Source B's finding that "no Arabic-shaping font exists in the tree" — true at the time, but
  imprecise about *why* the gap exists, and vulnerable to someone later "fixing" it by adding a font and
  believing the requirement closed. That imprecise claim is now replaced with a specific, reproduced,
  falsifiable mechanism (§2, §4): fonts exist, were tested, and the engine — not the font tree — is
  what's missing the capability. A requirement resting on a reproduced mechanism is stronger evidence
  than one resting on an absence, and is harder to accidentally mark closed by a future session that
  adds a font without re-running this probe.
- **`RDY-0086`** — the Arabic/RTL coverage and parity plan. Whatever coverage percentage is eventually
  published, Arabic PDF output specifically needs to be scoped out of any implied "Arabic works" claim
  until one of Options A–D actually closes it.
- **`RDY-0063`** — Arabic/RTL screenshot captures. If any planned capture includes a PDF render (a
  prescription, a statement), that capture will show unshaped/broken Arabic (§2.3) unless it is
  explicitly excluded or the limitation is captured *as* the disclosed finding rather than hidden.
- **`RDY-0094`** — the demo no-go register. Arabic PDF generation should be added as its own named
  no-go item, with the specific reason (mPDF GPOS parser gap) rather than a generic "Arabic incomplete"
  entry, so a demo operator knows exactly what not to attempt and why.
- **Gate G6's Arabic-parity precondition** — the requirement that the 47.5% English-coverage limitation
  (and by extension this PDF gap) be stated **at equal prominence in Arabic**, not buried in an English
  footnote, applies directly to whatever disclosure text results from whichever option is chosen.

**Agent C has not been told the requirements-document edits this implies** — that is deliberately left
to whoever next syncs the register, per this file's own opening note. A one-line pointer only:
`docs/evidence/EV-RB14-mpdf-gpos.md` exists and touches RDY-0087, RDY-0086, RDY-0063, RDY-0094, G6.
