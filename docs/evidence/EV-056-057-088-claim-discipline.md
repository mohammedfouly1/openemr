# EV-056 / EV-057 / EV-088 — CLAIM DISCIPLINE CONTROLS

**Requirements:** RDY-0056 (audit-integrity), RDY-0057 (sensitivity / MFA), RDY-0088 (competitive
frequencies) · **Gates:** G1, G5, G6 · **Owner:** Product Marketing
**Produced and scans executed:** 2026-08-14 · **Agent B**, Phase 2B

All three requirements share the same two-part acceptance: **(a)** a mandatory qualification travels
with the claim, and **(b)** a **keyword scan** of every artefact finds no prohibited term, signed off
by the claim reviewer (RDY-0003).

**Part (b) is executable and was executed. Part (a)'s sign-off is not — RDY-0003 has no named
reviewer.** That is the only reason none of the three closes.

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
> problem lists, notes, documents, or the API. Where an encounter is gated it is invisible, not
> redacted."**

**Prohibited:** *"field-level security"*, *"granular data-level permissions"*, *"record-level
encryption"*.

### 2.3 ⚠ A material weakness in the sensitivity claim, found while writing this

**Sensitivity gating has never been exercised, in either direction, on any dataset.**
`SELECT sensitivity, COUNT(*) FROM form_encounter` → **`normal 72`**. There is no
sensitivity-flagged encounter in the system, so the mechanism behind this qualification is **entirely
unverified at runtime** (`EV-016` §4.1, which is why three matrix rows are unexecutable).

**Consequence for claim discipline:** until one sensitivity-flagged encounter exists and A-2 passes,
**any statement about sensitivity behaviour is an assertion from source reading, not a demonstrated
capability.** It must be phrased accordingly, and it must not be demonstrated live. Recorded here
rather than left to be discovered when a prospect asks to see it.

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
| **RDY-0056** | **Yes** | **Yes — 0 violations** | **MISSING** | **NOT CLOSED** |
| **RDY-0057** | **Yes** | **Yes — 0 violations** | **MISSING** | **NOT CLOSED** |
| **RDY-0088** | **Yes** | **Yes — 1 violation found and fixed** | **MISSING** | **NOT CLOSED** |

All three name *"claim-review sign-off (RDY-0003)"* in their acceptance. **RDY-0003 requires one
named individual to be recorded as claim reviewer, and no name exists.** No further engineering
clears it — the same single blocker as RDY-0067.

**`Blocks`:** 0056 → G1 G5 · 0057 → G1 G5 · 0088 → G5 G6. No gate count moved (§0.0 Rule 3).
