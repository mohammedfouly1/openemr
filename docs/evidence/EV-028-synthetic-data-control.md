# EV-028 — SYNTHETIC-DATA SAFETY CONTROL

**Requirement:** RDY-0028 · **Gates:** G1 G2 G3 · **Owner:** Legal / Compliance
**Issued:** 2026-08-13 · **Status of this document:** ISSUED — awaiting the signed check in §6
**Applies to:** every row, file, image and string seeded into the Thiqa demo instance under
RDY-0020…RDY-0027, and every artefact derived from it (captures, recordings, decks, the website).

---

## 0. Read this before you seed anything

This is a **checklist for the moment of seeding**, not background reading. The prohibition list it
carries previously existed only inside a strategy document, which is no use to the person typing the
`INSERT`.

**One sentence of context, because it changes how carefully you will read the rest:** real patient
data entering a demo system is the single error this positioning cannot absorb. It is
**unrecoverable reputationally**, it is a regulatory event, and Risk **R-18** rates its impact
*severe and unrecoverable* precisely because there is no remediation that undoes disclosure.

**There is now a way back.** RDY-0044-A's pre-seed baseline exists, is protected, and its rollback
has been *rehearsed* (PB-031). If provenance is ever in doubt, §7 destroys the dataset and returns to
a known-empty foundation in about fifteen seconds of restore time. **Use it early.** The cost of an
unnecessary reseed is minutes; the cost of shipping one real record is the company's credibility.

---

## 1. ABSOLUTELY PROHIBITED

Nothing on this list may enter the demo dataset, in any table, file, filename, image, comment,
free-text note, or capture — **including in data that is never displayed.**

| # | Prohibited | Why it is on this list |
|---|---|---|
| P-01 | **Real PHI of any kind, from any source** | The core prohibition. Everything else here is a specific way people breach it by accident |
| P-02 | **Real patient names, or names that could identify a living person** | Includes a real person who is not a patient. A recognisable name on a fake chart is its own harm |
| P-03 | **Real Iqama or National ID numbers** | See §3 — these must be *structurally invalid*, not merely unfamiliar |
| P-04 | **Real customer medical records**, in whole or in part, however de-identified | "De-identified" is a claim requiring a method and a reviewer. We are not making that claim |
| P-05 | **Real insurer contracts**, rates, policy numbers or scheme names | Contract terms are confidential to the counterparty even when they are not PHI |
| P-06 | **Real customer logos, brands or clinic names** | Implies an endorsement or a relationship that does not exist |
| P-07 | **Real phone numbers** | See §3. A demo that dials a stranger is a live incident |
| P-08 | **Real employee credentials**, or any credential reused from another system | Also covers API keys, tokens and certificates |
| P-09 | **Any figure implying real volume** — patient counts, revenue, visit throughput, uptime | These become claims the moment they are on a screen. §32 governs, and T-23 scans for them |
| P-10 | **The phrase "our customer"**, or any wording implying an existing customer | We have none. Saying otherwise is a false statement of fact, not marketing licence |

**P-09 and P-10 apply to the surrounding narration too** — the demo script, the voiceover, the slide
beneath the screenshot. A prohibited claim is prohibited wherever it is made.

### 1.1 Two failure modes that pass a casual reading of the list

- **Copy-paste provenance.** A "realistic" clinical note pasted from a real record, a genuine
  referral letter with the names swapped, a real radiology image with the header cropped. All are
  P-01 or P-04. **If you did not author it or generate it, do not seed it.**
- **The plausible identifier.** An Iqama number invented by typing digits will occasionally be a
  *valid, issued* number belonging to a real person. Invented does not mean safe. §3 exists for this.

---

## 2. REQUIRED MARKINGS

| Where | Marking | Acceptance |
|---|---|---|
| **Every specimen document** (uploaded PDFs, scans, letters, referrals, insurance cards, consent forms) | **`SYNTHETIC DEMO`** — visible on the rendered face of the document, legible at normal viewing size, not only in metadata | RDY-0028 acceptance criterion, verbatim: *"specimen documents visibly carry the SYNTHETIC DEMO marking"* |
| Every seeded patient record | A provenance marker recorded in the seed manifest (EV-020…EV-027) tying the row to its generator | §5 |
| Every capture or recording published externally | The marking must be **visible in the frame**, not cropped out for tidiness | RDY-0060/0061 |

**A marking in the file's metadata does not satisfy this.** The control exists so that a screenshot
which escapes into a deck, a forwarded email, or a prospect's phone camera still announces what it
is. Metadata does not survive a screenshot.

---

## 3. SAFE-VALUE CONVENTIONS — use these, do not improvise

### 3.1 Identifiers (Iqama / National ID)

Saudi national IDs (leading `1`) and Iqama numbers (leading `2`) are 10 digits and carry a
**Luhn-style check digit**. Randomly typed digits have roughly a 1-in-10 chance of being
*structurally valid*, and a valid number may be an issued one.

> **Convention: every seeded identifier must be a 10-digit value that FAILS the check-digit
> validation**, so that any value escaping into the wild is provably not an issued identifier.

Prefer a reserved leading digit that is neither `1` nor `2` (e.g. `9`), which is both invalid and
visibly not a real ID class.

**⚠ Verify before relying on this.** The check-digit algorithm must be confirmed against a primary
source, and against whatever validation OpenEMR itself applies to the field, **before** the dataset
is accepted. Until that confirmation is recorded here, treat §3.1 as the intended convention rather
than a verified control. *(Open item — assign with the §6 signature.)*

### 3.2 Phone numbers

**Saudi Arabia publishes no drama/fiction reserved range** equivalent to the North American `555`
block. There is therefore **no phone number that is guaranteed unassigned**, and a control that
pretends otherwise would be false comfort.

> **Convention: seeded phone numbers must be structurally invalid for the E.164 Saudi plan** — e.g.
> `+966 5` followed by a digit count that cannot be dialled, or an operator prefix not in use.
> **Never** a well-formed `+9665XXXXXXXX` mobile number.

**Test the rule, not the intention:** before acceptance, confirm a sample of seeded numbers cannot be
dialled. A number that *looks* fake and *is* connectable is exactly the failure this prevents.

### 3.3 Names

Generated Arabic and English names drawn from a name generator or an author's invention. Cross-check
the final patient list against public figures — a demo chart for a well-known name is a P-02 breach
even though nobody's real record was touched.

### 3.4 Addresses, employers, insurers

Fictional. Insurer names must not match a real Saudi insurer (P-05/P-06). If a plausible-sounding
insurer is needed, invent one and record it in the manifest.

---

## 4. PRE-SEED CHECK — run before the first `INSERT`

| # | Check | Required state |
|---|---|---|
| 1 | RDY-0044-A baseline exists, hash verified, rollback rehearsed | ✅ **PB-031** — `18564f74…`, protected, restore proven |
| 2 | The seeding operator has read §1 **in this document**, not in summary | Confirmed at §6 |
| 3 | Every data source for the seed is generated or authored — none copied from a real system | Confirmed at §6 |
| 4 | Specimen-document template carries the `SYNTHETIC DEMO` marking on its face | Verified visually |
| 5 | Identifier and phone conventions (§3) are implemented in the generator, not applied by hand afterwards | Verified in the generator |

**Check 5 matters more than it looks.** Conventions applied by hand after generation are applied
inconsistently, and the one row that gets missed is the one that ends up in the screenshot.

---

## 5. POST-SEED VERIFICATION — machine-checkable, run before acceptance

Run against the seeded database and attach the output to the manifest. **A visual review of a few
hundred rows is not a control**; these scans read every row.

```sql
-- 5.1 Structurally VALID Saudi-class identifiers must not exist (see §3.1 caveat).
SELECT COUNT(*) AS valid_class_ids FROM patient_data
 WHERE ss REGEXP '^[12][0-9]{9}$';                       -- expect 0

-- 5.2 Well-formed, dialable Saudi mobile numbers must not exist.
SELECT COUNT(*) AS dialable FROM patient_data
 WHERE REPLACE(REPLACE(REPLACE(phone_cell,' ',''),'-',''),'+','') REGEXP '^9665[0-9]{8}$'
    OR REPLACE(REPLACE(REPLACE(phone_home,' ',''),'-',''),'+','') REGEXP '^9665[0-9]{8}$';  -- expect 0

-- 5.3 Prohibited phrases anywhere in free text (extend the list per §32).
SELECT COUNT(*) AS prohibited FROM patient_data
 WHERE CONCAT_WS(' ', fname, lname, street, city, occupation)
       REGEXP 'our customer|Your Clinic Name Here';       -- expect 0

-- 5.4 Every seeded patient is attributable to the generator.
SELECT COUNT(*) AS unattributed FROM patient_data
 WHERE pubpid IS NULL OR pubpid = '';                     -- expect 0
```

Then, **the check that is not machine-checkable and must not be skipped:** open a sample of the
uploaded specimen documents *as a viewer sees them* and confirm the `SYNTHETIC DEMO` marking is
visible on the rendered face.

> **Negative control.** Before trusting these scans, seed one deliberately-bad row into a
> **disposable** copy and confirm each query catches it. A scan returning `0` on data it cannot
> actually read returns `0` for the wrong reason — the vacuous-pass failure that PB-027 and PB-031
> both had to design against.

### 5.1 The negative control has been run — these scans are proven to fire

Executed 2026-08-13 in a disposable database (`ev028_scanproof`, a `CREATE TABLE LIKE` copy of
`patient_data`, dropped afterwards; the authoritative instance was never touched). Five rows: four
each violating exactly one scan, one compliant.

| Scan | Planted violation | Result |
|---|---|---|
| 5.1 valid-class identifier | `ss = 1234567890` | **1 — fired** |
| 5.2 dialable mobile | `phone_cell = +966 55 123 4567` | **1 — fired** |
| 5.3 prohibited phrase | `occupation = our customer liaison` | **1 — fired** |
| 5.4 unattributed row | `pubpid = ''` | **1 — fired** |
| *(compliant row)* | invalid ID, undialable number, clean text, `pubpid` set | **flagged by none** |

Each scan returned **exactly one** hit: they detect their own violation and do not over-match the
compliant row.

> **⚠ This control caught its own vacuous pass on the first attempt, which is why it is written up
> rather than quietly re-run.** The initial run reported all four scans returning `0` and printed
> "5 rows planted". **No rows had been planted** — the `INSERT` had failed on a duplicate primary
> key, and the success message printed unconditionally regardless. Four clean zeros, from an empty
> table, under a banner announcing the data was there.
>
> **This is precisely the failure mode §5 exists to prevent, reproduced by accident on the very run
> intended to prove the scans work.** It is recorded because it is the strongest available argument
> for the rule: the fix was to assert the planted row count *before* trusting the scan output, and
> **any future run of §5 must do the same.** A scan result is meaningless unless you have
> independently confirmed there was something there to find.

---

## 6. SIGNED CHECK — required before the dataset is accepted

**RDY-0028 does not close until this section is completed by a named person.** An unsigned control
is a document, not a control.

| Field | Entry |
|---|---|
| Dataset / manifest reference | ☐ |
| Row counts by category | ☐ |
| §1 prohibitions — confirmed none present | ☐ |
| §2 markings — confirmed on every specimen document | ☐ |
| §3.1 check-digit algorithm — **confirmed against a primary source** | ☐ |
| §5 scans — attached, with the negative control | ☐ |
| **Reviewer name and role** | ☐ |
| **Signature and date** | ☐ |

**Reviewer must be a named individual, not a role.** RDY-0028's owner is Legal / Compliance, and
§7 of the readiness register records that the *role* is assigned while the *person* is not. **That
unassigned person is currently the binding blocker on this requirement**, not any engineering task.

---

## 7. ROLLBACK — if provenance is ever in doubt

**Destroy the dataset and reseed. Do not investigate first, and do not attempt a surgical delete.**
A targeted deletion assumes you already know the full extent of the contamination, which is the one
thing a provenance doubt means you do not know.

Follow `ROLLBACK.md` in `C:\openemr-stack\backups\protected\rdy0044a\` — verify the hash, snapshot
the contaminated state for the incident record **before** discarding it, restore, and confirm
`patients=0`.

**Then record it.** A reseed triggered by a provenance doubt is an incident with a cause, and the
cause belongs in this document's revision history so the next seeding run does not repeat it.

---

## 8. Status against RDY-0028's acceptance criteria

| Criterion | State |
|---|---|
| The control exists | ✅ **This document** |
| A named reviewer has signed it against the completed dataset | ❌ **BLOCKED** — requires (a) a named Legal/Compliance individual and (b) a completed dataset, neither of which exists |
| Specimen documents visibly carry the `SYNTHETIC DEMO` marking | ❌ **NOT YET APPLICABLE** — no documents seeded |

**RDY-0028 remains OPEN.** One of three criteria is met. The control is issued so that seeding may
proceed *under* it; the requirement closes when the dataset it governs exists and a named person has
signed §6 against it.

**One open technical item is carried in §3.1** — the Saudi check-digit algorithm must be verified
against a primary source before the identifier convention can be described as a verified control
rather than an intended one.
