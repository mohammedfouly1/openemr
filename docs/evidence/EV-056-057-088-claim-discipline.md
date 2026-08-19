# EV-056 / EV-057 / EV-088 — CLAIM DISCIPLINE CONTROLS

**Requirements:** RDY-0056 (audit-integrity), RDY-0057 (sensitivity / MFA), RDY-0088 (competitive
frequencies) · **Gates:** G1, G5, G6 · **Owner:** Product Marketing
**Produced and scans executed:** 2026-08-14 · **Agent B**, Phase 2B

All three requirements share the same two-part acceptance: **(a)** a mandatory qualification travels
with the claim, and **(b)** a **keyword scan** of every artefact finds no prohibited term, signed off
by the claim reviewer (RDY-0003).

**Part (b) is executable and was executed. Part (a)'s sign-off was, for a while, the only thing
missing — see the ✅ CLOSED addendum in §6: reviewed 2026-08-19 by the now-named reviewer, verdict
APPROVED FOR PUBLICATION, all three closed.**

---

## 1. RDY-0056 — audit integrity: hash, not HMAC; rows unchained

### 1.1 The mandatory qualification, in the exact words that must travel with MC-02

> **"The audit trail is tamper-evident, and we can demonstrate that live. To be precise about what
> that means: it is a SHA3-512 hash over the record's own columns, not an HMAC, and the rows are not
> chained to each other. So it detects a row being altered. It does not detect a row being deleted
> along with its partner record. We would rather tell you that than let you find it."**

**This must appear in the same visual unit as the claim** — same slide, same paragraph, same caption.
Not a footnote, not a later page.

### 1.2 What is genuinely strong, and should be said

The mechanism is **SHA3-512 over 13 mutable columns**, recomputed and compared by the report. It is
**the only capability in the entire 270-item catalogue proven end-to-end at runtime** — 200/200
matched, 0 mismatches at audit, re-verified clean at 41,613 rows (PB-026) and again post-reset
(PB-045: HTTP 200, 7,316 bytes, *"No audit log tampering detected"*).

**Understating this would be as wrong as overstating it.** The qualification exists so the strength
survives scrutiny.

### 1.3 Prohibited, absolutely

**"Immutable"** · **"blockchain"** · **"tamper-proof"** · **"cannot be altered"**.
The correct word is **tamper-evident**.

### 1.4 One live caveat that must reach the D-1 script

**PB-030's API-row false positive is still open.** The tamper report false-positives on rows written
via `/apis/*`. **The D-1 demo window must not contain an `api_log` row**, and no `/apis/*` call may
be made during the run. `EV-040` carries this; it belongs to RDY-0056's discipline too, because a
false positive during the flagship demo would discredit the exact claim being made.

---

## 2. RDY-0057 — sensitivity and MFA disclosure

### 2.1 MFA — the qualification, stated before the topic is discussed

> **"Two-factor is supported — TOTP and U2F, at browser login. Enrolment is per-user and voluntary.
> An administrator cannot require it: there is no setting that mandates enrolment, so an unenrolled
> user completes login with a password alone."**

**Prohibited:** *"MFA enforced"*, *"MFA required"*, *"mandatory two-factor"*.
**Say it before MFA is discussed, not after it is asked about.** CAP-0218 is in the **Missing**
register for exactly this reason (`EV-067` §5.1).

### 2.2 Sensitivity — the qualification

> **"Encounter sensitivity is enforced at the encounter level. It is not applied to demographics,
> problem lists, notes, documents, or the API. Where an encounter is gated, the reason/notes text is
> redacted to '(No access)' — the encounter row itself (date, encounter number) still appears in the
> list. This gating applies to non-clinical roles such as Front Office and Accounting. Clinical roles
> (Physicians) are not sensitivity-gated against each other in this configuration — a physician can
> see the full detail of a sensitivity-flagged encounter belonging to a different clinician's
> patient."**

> **⚠ ADDED 2026-08-19 (Owner decision, given directly in conversation with the orchestrating
> session).** The physician-vs-physician sentence above is new. Live test (PB-410, §2.3 below) found
> a physician role, viewing a different clinician's `sensitivity='high'` encounter, saw it fully
> unredacted — no prior version of this qualification stated this. **Owner chose to disclose it as an
> added qualification** rather than treat it as a defect to fix, consistent with this project's
> disclose-don't-hide pattern (RDY-0055's PHI determination, RDY-0057's MFA disclosure). This does not
> change product behaviour — it makes the claim accurate to what the live test found.

> **⚠ CORRECTED 2026-08-19 (PB-380 browser/live-system agent + independent orchestrator re-read,
> Owner-directed).** This qualification previously read *"it is invisible, not redacted."* Two
> independent static-code reads of `interface/patient_file/history/encounters.php:506-511/533-536`
> found the opposite of that claim: the encounter row is echoed unconditionally (line 533/536); only
> `$reason_string` is replaced with `"(No access)"` when `AclMain::aclCheckCore('sensitivities', ...)`
> denies. A second layer, `src/Services/EncounterService.php:450-452` (FHIR/REST), similarly returns an
> explicit denial string rather than omitting the record. **Corrected to the direction the code
> actually supports — redaction, not invisibility — because the prior wording overstated the privacy
> protection in customer-facing material.** **✅ CONFIRMED against live rendered output 2026-08-19
> (PB-410) — see §2.3 below.** The Accounting role's Visit History screen for `SYN-0014`'s real
> `sensitivity='high'` encounter renders the row (date, provider, insurance) with Issue/Reason/Coding
> showing the literal text `"(No access)"` — exactly as this qualification now states.

**Prohibited:** *"field-level security"*, *"granular data-level permissions"*, *"record-level
encryption"*, and (as of this correction) **"invisible"** in connection with sensitivity gating.

### 2.3 ✅ RESOLVED 2026-08-19 (PB-410, fifth browser-check agent) — live-confirmed, redacted not invisible

**Sensitivity gating has now been exercised live**, closing the gap this section originally flagged.
Live test against the real data (`SYN-0014` pid 14, encounter 31, `sensitivity='high'`, 2026-07-12):

- **Front Office** (`r.aldosari`) — the encounters screen renders `"(Encounters not authorized)"`
  for the whole tab; a coarser gate than sensitivity, applying regardless of any encounter's flag.
- **Accounting** (`k.alotaibi`, holds no `sensitivities` ACL grant) — the encounter row **renders**
  (date, provider, insurance visible) with Issue/Reason/Coding showing literal `"(No access)"` text.
  **This is the live confirmation that the qualification's wording ("redacted to '(No access)', the
  row itself still appears") is correct** — matching the `encounters.php:506-511/533-536` code read
  exactly. Control check on `SYN-0013`'s `sensitivity='normal'` encounters under the same login showed
  the identical "(No access)" pattern — Accounting's redaction is a blanket per-role denial of those
  columns, not a per-record differential keyed to the specific sensitivity value.
- **Physician** (`y.alharbi`, not the encounter's own author) — the same `sensitivity='high'`
  encounter renders **fully unredacted** (Reason/Form and Coding both visible, no "(No access)"
  anywhere). New finding, not previously documented: the `physicians` ACL group in this configuration
  is not gated by sensitivity at all, even across different clinicians' patients.

**Consequence for claim discipline:** the qualification at the top of §2.2 is now a demonstrated
capability, not merely an assertion from source reading, for the Accounting/Front-Office-style
non-clinical roles it was written to describe. **The Physician finding is now folded into §2.2's own
qualification text** (Owner decision, same day) rather than left as a separate flagged nuance — the
qualification a presenter uses now states both halves: non-clinical roles are gated, clinical roles
are not gated against each other. Full detail: `docs/Marketing-MVP-and-Launch-Readiness-
Requirements.md` PB-410; `EV-016-authorization-matrix.md` §4.1 (updated in the same pass).

---

## 3. RDY-0088 — competitive frequency figures: publish the mechanism, not the number

**Every white-space figure in the strategy is computed over 16 scored competitors while 9 of 26
dossiers remain unverified.** Publishing one and being corrected by a competitor from the missing
nine would damage precisely the asset — verifiability — that the correction would be about.

**Held from publication:** every figure of the form *"N of 16"*, *"N of 11 GCC"*, and the product-
visibility mean.

**Publish the mechanism instead:**

> *"We publish what the product does not do, before you ask. You can check that against the software
> itself, because it is open source."*

That claim is about **us**, is verifiable **today**, and does not decay when a dossier is completed.

---

## 4. The scans — executed, and one of them caught this author

### 4.1 Results

| Requirement | Pattern | Result |
|---|---|---|
| RDY-0056 | `\bimmutable\b`, `\bblockchain\b` | **0 unqualified uses.** The only hit is §32's own prohibition row, which is the control, not a violation |
| RDY-0057 | `MFA enforced`, `field-level security`, `mandate MFA`, `force MFA` | **0 unqualified uses** |
| RDY-0088 | `[0-9]+ of (16\|11\|26)` scoped to competitor context | **1 violation found — in `EV-067`, written by this author hours earlier.** See §4.3 |

### 4.2 ⚠ The naive scan is not a usable control — and that matters for RDY-0003

RDY-0088's acceptance specifies a *"keyword and numeral scan"*. **A bare numeral scan does not work.**
Run unscoped, `[0-9]+ of (16|11|26)` returns:

| Hit | Verdict |
|---|---|
| `EV-040:96` — *"the menu rendered **0 of 16** forms… Now **16 of 16**"* | **False positive.** Encounter forms, not competitors |
| `EV-067:105` — *"**16 of 16** installed cleanly"* | **False positive.** Dormant clinical forms |
| `EV-067:17` — *"**2 of 16** competitors publish exclusions"* | **TRUE violation** |

**"N of 16" is a legitimate engineering phrase in this project**, and 16 happens to be both the
number of scored competitors *and* the number of dormant forms *and* the number of encounter forms.
**A reviewer handed the naive pattern would see three hits, dismiss the first two, and — pattern
fatigue being what it is — plausibly dismiss the third.**

**The scan must be competitor-scoped.** The working pattern is recorded in §5.

### 4.3 The violation, and the fix

`EV-067-published-registers.md:17` read:

> *"Source C found that **2 of 16** competitors publish exclusions at all."*

That is exactly what RDY-0088 forbids, in an artefact whose entire purpose is disclosure discipline,
written by the same author who then ran the scan. **Corrected in place** to state the mechanism and
name the hold:

> *"Source C found that **very few competitors publish exclusions at all** — the exact frequency is
> withheld under **RDY-0088**… **Publish the mechanism, not the number.**"*

**Recorded rather than quietly fixed**, because the useful finding is not the typo — it is that the
control caught its own author within hours, which is the only real evidence that a control works.

### 4.4 Unqualified uses remaining in the readiness document itself

Three internal uses remain in `Marketing-MVP-and-Launch-Readiness-Requirements.md` — the RDY-0062
register row (**"0 of 16 competitors have an equivalent"**), the RDY-0067 card (**"2 of 16"**), and
§34's rationale. **These are internal working text in the governing document, not published
artefacts**, and two of them are the rationale for the prohibition itself.

**Not swept**, for two reasons: it is a whole-file edit while another agent is working in the file,
and whether the governing document must itself comply is a call for the RDY-0003 reviewer. **Flagged
rather than decided.** If the answer is yes, each needs a `PROVISIONAL — RDY-0088` marker.

---

## 5. Reproduce

```bash
# RDY-0056
grep -rniE "\bimmutable\b|\bblockchain\b|tamper-proof" docs/ \
  | grep -viE "prohibit|never|must not|appear nowhere"

# RDY-0057
grep -rniE "MFA enforced|MFA required|mandatory two-factor|field-level security" docs/ \
  | grep -viE "prohibit|cannot|never|must not|no way"

# RDY-0088 -- competitor-scoped, NOT a bare numeral scan (see §4.2)
grep -rniE "[0-9]+ of (16|11|26)[^0-9]{0,60}(competitor|vendor|GCC|market|publish|demonstrat|discuss)" docs/ \
  | grep -viE "prohibit|must not|never|withheld|until|provisional|hold"
```

---

## 6. Status

| Requirement | Qualification defined | Scan executed | Reviewer sign-off (RDY-0003) | Status |
|---|---|---|---|---|
| **RDY-0056** | **Yes** | **Yes — 0 violations (re-run 2026-08-19, same result)** | **✅ Mohammed Elfouly, 2026-08-19** | **CLOSED** |
| **RDY-0057** | **Yes** | **Yes — 0 violations (re-run 2026-08-19, same result)** | **✅ Mohammed Elfouly, 2026-08-19** | **CLOSED** |
| **RDY-0088** | **Yes** | **Yes — 1 violation found and fixed (re-run 2026-08-19, confirmed fixed)** | **✅ Mohammed Elfouly, 2026-08-19** | **CLOSED** |

> **✅ CLOSED 2026-08-19 — see `EV-003` §5 row 2.** This artefact went through the RDY-0003 review step
> as its own named reviewer, Mohammed Elfouly. The orchestrating session re-ran the §5 mechanical
> scans against the current state of `docs/` (not just cited the 2026-08-14 run) and read the artefact
> for traceability (C-1: qualifications trace to CLM-0024/CLM-0025/L-23) and prohibited-term use
> (C-2: the prohibited terms appear only inside this document's own "prohibited, absolutely" framing,
> never as a claim). Findings were put to the reviewer directly; verdict **APPROVED FOR PUBLICATION**,
> his judgment, not inferred. One observation surfaced during the re-run and put to him alongside the
> verdict rather than decided silently: `docs/Product-Positioning-and-GTM-Locked-Strategy.md` carries
> extensive unguarded "N of 16"-pattern text, judged out of scope on the same reasoning §4.4 already
> gives for the readiness document's own internal uses (internal strategy material, not a
> customer-facing artefact — `EV-003` §3's exemption). Approved without additional correction.

All three named *"claim-review sign-off (RDY-0003)"* in their acceptance. That sign-off is now
recorded, closing the same single blocker that RDY-0067 had.

> **⚠ Post-closure correction, same day (2026-08-19).** §2.2's sensitivity qualification text was
> amended after this table's closures were recorded (see the addendum in §2.2 above) — "invisible, not
> redacted" corrected to "redacted, not invisible," on two independent static-code reads. **Not
> reopening RDY-0057**: the closure criteria (qualification defined, scan executed, reviewer sign-off)
> are about the *discipline process* existing and working, and they still hold — the correction makes
> the qualification's *content* more accurate, it doesn't undo the process that produced it. Recorded
> here so a future reader of this status table isn't misled by a stale "0 violations" scan result that
> ran against the old wording — the scans (C-3/C-4/C-5) never checked this particular sentence's
> factual accuracy, only whether prohibited terms appeared as unqualified claims elsewhere.

**`Blocks`:** 0056 → G1 G5 (closed) · 0057 → G1 G5 (closed) · 0088 → G5 G6 (closed). Gate-count
decrement recorded in the main readiness document's PB-372 sync, per §0.0 Rule 3 — not recalculated
here.
