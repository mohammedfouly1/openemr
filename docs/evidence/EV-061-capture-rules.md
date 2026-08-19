# EV-061 — CAPTURE RULES AND REVIEW SHEET

**Requirement:** RDY-0061 · **Gates:** G1 G5 · **Owner:** Product Marketing
**Governs:** every capture in RDY-0060 (SS-01 … SS-12) and every video in RDY-0062
**Date:** 2026-08-14

> **Why this exists.** §17.2 already carries four rules. This turns them into something a capturer
> can work from and a reviewer can sign: what to shoot, on which account, what must not be in frame,
> and a per-image record. **Redaction decided per-capture is redaction that eventually gets
> forgotten** — that is RDY-0061's stated gap, and a review sheet is the answer to it.

---

## 1. ABSOLUTE PROHIBITIONS — if any appears, the capture is destroyed and retaken

Not redacted afterwards. **Retaken.** A blurred box tells a viewer something was there.

| # | Never in frame | Why it is a hard rule now |
|---|---|---|
| P-1 | **The `admin` username** | RDY-0017 rotated it and the demo has six named accounts. There is no reason to be logged in as `admin`, so its appearance means the wrong account was used |
| P-2 | **Any credential** — password field with content, session token, API key, connection string | — |
| P-3 | **Stock OpenEMR identity** — the name, logo, donation or review link, `open-emr.org` URL | Most are configured away, but **`main_screen.php` has no `<title>`** and the Zend module favicon is still stock (EV-090). **Check browser chrome, not just page content** |
| P-4 | **A `$` currency symbol or a US state list** | Regional config is Saudi; these indicate a screen that escaped it |
| P-5 | **A real name, identifier, phone or address** | Everything seeded is synthetic — see §2 |
| P-6 | **A customer logo, or the phrase "our customer"** | We have none |
| P-7 | **Any figure implying real volume** — patient counts, revenue, uptime | §32 prohibits these as claims; a screenshot is a claim |
| P-8 | **An empty required panel** | Reads as an unfinished product |
| P-9 | **An error state, unless the capture is deliberately about one** | Applies especially to SS-03, whose whole point is that an *absence* must not read as a fault |

## 2. Synthetic identifiers are SAFE to show — and should be

**Do not redact `SYN-0001`, `Hessa Alharthi`, `9990000001` or `+966 5 000 001`.** Every one is
fabricated, and the dataset carries a signed synthetic-data determination (RDY-0028, Mohammed
Elfouly). The identifiers are *designed* to be visible: `SYN-` announces the record's status, the
national-ID values are a non-issued class, and the phone numbers are structurally undialable.

**Redacting them would be worse than showing them** — it implies there is something real underneath.

**The one thing to check:** the capture must not include the **two duplicate pairs** presented as
though they were distinct patients unless the capture is *about* duplicate detection (SS-03 area).
Duplicates are `SYN-0001`/`SYN-0029` and `SYN-0008`/`SYN-0030`.

## 3. Which account to use — this is now decidable, so decide it

Per-capture accounts, from the persona column of §17.1 and the roles proven in PB-037:

| Capture | Account | Reason |
|---|---|---|
| SS-01 audit tamper | `n.alqahtani` (Administrator) | Requires `admin\|super`; **use the named administrator, never `admin`** |
| SS-02 ACL admin | `n.alqahtani` | Same |
| SS-03 negative half | **`r.aldosari` (Front Office)** | Proven denied `patients\|bulk_rep` |
| SS-04 positive half | **`y.alharbi` (Physician)** | Proven allowed; **same patient, same moment as SS-03** |
| SS-05 layout editor | `n.alqahtani` | Admin surface |
| SS-06 calendar · SS-07 flow board | `r.aldosari` | Front-office workflow |
| SS-08 SOAP · SS-09 ophthalmology | `y.alharbi` | Physician |
| SS-10 reporting · SS-11 CSV | `k.alotaibi` (Accounting) | Proven allowed the financial reports |
| SS-12 Arabic | any of the above | — |

Credentials live only in `C:\openemr-stack\secrets\thiqa-demo-credentials.json`.

## 4. Per-capture notes that only exist because the work has been done

| Capture | Note |
|---|---|
| **SS-01** | ⚠ **PB-030: do not capture a date range containing an API-generated `api_log` row** — the tamper report false-positives on them under Asia/Riyadh. Make no `/apis/*` call before capturing. The clean result is **7,316 bytes**, *"No audit log tampering detected"*. **The qualification must not say "encrypted"** — EV-055 proves the log is base64-encoded and decodable in one function call |
| **SS-03 / SS-04** | The pairing is proven: Front Office gets a hard 403 on `patient_list.php`, Physician gets 200 with rows (PB-034/PB-037). **Frame the 403 as a permission boundary, not an error** (P-9) |
| **SS-06** | 36 appointments across the current week, two provider columns, mixed types — **the week already reads as a real clinic's**, which is SS-06's acceptance |
| **SS-07** | **16 appointments dated today at mixed statuses** (`- None`, `@ Arrived`, `> Checked out`, `< In exam room`). More than one status is visible, which is the acceptance |
| **SS-08** | 18 SOAP notes exist; each is attributed (PR-14 fixed the missing author). A clinician must confirm it reads plausibly |
| **SS-09** | **Capture `SYN-0006` encounter 23** — moderate NPDR with macular oedema, the strongest exam. **Dr Mohamed Taha has passed all 8 (PB-045)**, so SS-09's "an ophthalmologist confirms plausibility" acceptance is already met. **Include the retina panel** — macula, vessels, CMT 412/268 — not just the header |
| **SS-10** | Use **RPT-0012** (finds the planted missing charge, encounter 36 / `SYN-0019`) or **RPT-0028** (reconciles to 600.00 SAR for `SYN-0001`). Both are non-empty and reconcile to the manifest |
| **SS-11** | The verified export is **38 rows, 7 columns**, parsed by a real CSV reader (PB-045). Names are Latin-script so mojibake risk is low — **but SS-11's acceptance says "transliterated names render correctly", so if any Arabic name is added later this must be re-checked** |
| **SS-12** | **Do not crop the untranslated gaps.** An untranslated picklist must be deliberately in frame. 47.5 % chrome-only translation is the claim, and hiding the gaps would make the capture dishonest |

## 5. The qualification travels with the image

**Not a footnote. Not a linked page. The same visual unit.**

Every capture in §17.1 carries a *Required qualification* column. If the image is used anywhere —
website, deck, PDF, social — the qualification goes with it. **A cropped screenshot that leaves the
qualification behind is a prohibited claim**, and §32/T-23 scan for exactly that.

The two most easily lost:

- **SS-01** — *hash, not HMAC; rows not chained*. **And never "encrypted."**
- **SS-12** — *47.5 %, chrome only; PDF will not render Arabic*.

## 6. Annotation

One idea per image, in the *Annotation concept* column. Annotations may **point at** what is already
on screen; they may not **assert** anything the screen does not show. An arrow saying *"run this on
your own data"* is fine. A caption saying *"tamper-proof"* is not — it is both a claim and false.

## 7. Real product only

No mockups, no composites, no cleaned-up screens. Source C's clearest negative benchmark is a
competitor whose screenshot slots resolve to placeholder files. **If a screen is not good enough to
photograph, fix the screen.**

## 8. REVIEW SHEET — one row per capture, signed by someone other than the capturer

**§17.2 requires review by a second person.** The capturer does not sign their own work.

| Capture | Taken by | Date | P-1…P-9 all clear | Synthetic IDs left visible | Correct account used | Qualification attached | Annotation asserts nothing extra | Reviewer | Verdict |
|---|---|---|---|:--:|:--:|:--:|:--:|---|---|
| SS-01 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-02 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-03 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-04 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-05 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-06 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-07 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-08 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-09 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-10 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-11 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |
| SS-12 | | | ☐ | ☐ | ☐ | ☐ | ☐ | | ☐ PASS ☐ RETAKE |

**Videos (RDY-0062)** are reviewed against the same rules, plus: branded surface, non-`admin`
account, **qualification spoken before the result appears**, and claim-reviewed before publication.

## 9. Acceptance status

| Criterion | State |
|---|---|
| The rules exist | ✅ **This document** |
| Every RDY-0060 capture checked against them, recorded per image | ⏳ **Blocked — the captures do not exist yet.** §8 is the instrument; it fills in as RDY-0060 proceeds |

**RDY-0061 is NOT closed.** The rules are complete and usable; closure needs RDY-0060's captures to
exist and be reviewed through §8. **Nothing further can be done on 0061 until 0060 runs.**

**Re-verified 2026-08-19 (PB-39x):** independently confirmed §1-§8 unchanged and still accurate.
Also independently confirmed, against the live DB, that the facility/locale prerequisites this
document's rules govern (P-3 stock identity, P-4 currency/locale) are now correct — see the
RDY-0060 register row for the DB values and the `facility.primary_business_entity` fix. This
removes one class of risk from a future capture session (an escaped-US-locale screen) but does not
change this document's own status: §8 is still empty because RDY-0060 has not run.
