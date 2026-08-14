# EV-040 — D-7 END-TO-END DEMO SCRIPT

**Requirement:** RDY-0040 · **Gate:** G2 · **Feeds:** RDY-0041 (two rehearsals)
**Dataset:** Marketing MVP Seed v1 · baseline `e45ad2e7c854d248…` + `338b122228a7c5d9…`
**Specification:** §15 of the readiness register. **This document instantiates that spec against the
actual seeded data** — real patient identifiers, real dates, the accounts to use, and what to say.

---

## 0. Before you start

1. **Reset from RDY-0044-B** (`EV-044-demo-reset-runbook.md`) and verify the state signature.
2. Log in once per segment with the account named for that segment — **do not run the whole demo as
   `admin`.** The role separation *is* the proof (§15.4).
3. Credentials: `C:\openemr-stack\secrets\thiqa-demo-credentials.json`. **Never display that file,
   and never demo with `admin` visible on screen.**
4. **If a "LOCKED by another user — take ownership?" dialog appears, click CANCEL.** OK writes to the
   record (PB-042/043).
5. **Do not open `/apis/*` during the demo window** — PB-030: an API-generated audit row makes the
   D-1 tamper report false-positive under Asia/Riyadh.

**Two segment-boundary moments are the point of the whole journey (§15.4).** Don't rush them:
after step 13, show that **Front Office cannot open the note the physician just wrote**; at step 14,
show that **Accounting can code the encounter but cannot read it.**

---

## 1. Reception segment — account: `r.aldosari` (Front Office)

| # | Screen | Data to use | Do this | Expected | Say / never say |
|---|---|---|---|---|---|
| 1 | Patient search | 30 patients seeded | Search `Al` — or `SYN-` to show the identifier scheme | Multiple matches, returned instantly | *"Finding an existing patient in seconds."* **Never** "master patient index" |
| 2 | **Add Patient** | — | Register a walk-in | New patient created **under a non-admin account** | *"Reception registers patients; no administrator involved."* ⚠ **See §5 — RDY-0042 is open** |
| 3 | Duplicate handling | **Hessa Alharthi** `SYN-0001` / `SYN-0029` (DOB 1948-01-01) · or **Talal Alsubaie** `SYN-0008` / `SYN-0030` | Search the surname; show two records, same name **and DOB** | Duplicate pair visible | *"Duplicate detection, then a merge."* **Never** "MPI" |
| 4 | Appointment booking | Current week, 36 appointments | Book into a provider column | Appointment created, **times render at +03** | *"Provider and facility calendars, recurring appointments."* **Never** "AI-optimised" or "theatre scheduling" |
| 5 | Arrival / check-in | **16 appointments dated today** | Advance a patient's status to Arrived | Status advances | *"Live flow, arrival through checkout."* |
| 6 | Flow board | Today's list at mixed statuses (`- None`, `@ Arrived`, `> Checked out`, `< In exam room`) | Open the flow board | The day at a glance, **varied statuses** | *"In-office status board."* **Never** "queue management with token display" |

**Why the statuses are mixed:** all-one-status looks synthetic. The seed deliberately varies them.

---

## 2. Clinical segment — account: `y.alharbi` (Physician, Ophthalmology)

| # | Screen | Data to use | Do this | Expected | Say / never say |
|---|---|---|---|---|---|
| 7 | Open chart | **`SYN-0006` Ziad Alghamdi, 37 M** | Open the chart | Populated panels — problems, allergies, encounters | *"18 clinical forms active; 16 more ship uninstalled."* State the count |
| 8 | Start encounter | Today's checked-in appointment | Create the encounter from the appointment | Encounter created | *"Continuity from reception to clinic."* |
| 9 | Vitals | 12 seeded | Record vitals | Recorded, structured | ⚠ **See §5 — RDY-0043 is open** |
| 10 | **Ophthalmology examination** | **`SYN-0006`, encounter 23** — moderate NPDR with macular oedema | Open the Eye Exam. Show: **SC 20/80 / 20/60 → MR 20/60 / 20/50**, **IOP 16/16**, **C/D 0.35/0.3**, macula OD *"Centre-involving oedema, hard exudates"*, vessels OD *"Dot-blot haemorrhages, venous beading"*, **CMT 412 / 268** | A complete, internally consistent specialty exam | **This is the beachhead proof.** *"The specialty depth the ICP rests on."* An ophthalmologist in the room will read these numbers — they are consistent |
| 10b | *(alternative)* | **`SYN-0003` Amal Albishi** — glaucoma | Open the Glaucoma Zone (chart icon, top-right of Tension): **Current Targets OD 16 / OS 16** | Target vs actual IOP 17/18 | Note the panel says **"Not documented"** for Visual Fields, Optic Nerve Analysis and Gonioscopy — **the form is honest about what was not done.** Do not claim perimetry |
| 11 | Problems / allergies / medications | 6 chronic problems, 5 allergy patients | Show the coded lists | Entries visible | ⚠ **See §5 — the allergy alert will NOT fire.** **Do not attempt this step until it is fixed.** Qualification if discussed: **exact name match only** — not ingredient-level, **not an interaction engine** |
| 12 | Prescription | 12 seeded, e.g. `Latanoprost 0.005% eye drops` | Record and print | Printable prescription | *"Recorded and printable."* **Never** imply eRx — it needs a vendor contract and is not enabled |
| 13 | Signature / lock | `esign_individual = 1`, `lock_esign_individual = 1` | Sign the note at **form level** | Note locked, signature logged | *"Electronic signature with record locking."* **Never** "legally binding digital signature." Note: whole-encounter signing (`esign_all`) is **off** — sign the individual form |

**→ SEGMENT BOUNDARY.** Log out. Log in as `r.aldosari` (Front Office) and attempt to open the note
just written. **It is denied.** That is Pillar 1 in narrative form — configuration, not claim.

---

## 3. Financial segment — account: `k.alotaibi` (Accounting)

| # | Screen | Data to use | Do this | Expected | Say / never say |
|---|---|---|---|---|---|
| 14 | Fee sheet / charge capture | 36 charges, 4 priced services | Show a charge attached to an encounter | Charge reconciles to the encounter | **Say this BEFORE the screen appears:** *"We do not issue your tax invoice and we do not submit insurance claims."* There is **no tax field anywhere** and **no ZATCA capability** |
| 15 | Reporting | The six locked reports | Run **RPT-0012 Appointments and Encounters** over **2026-05-16 … today** | 37-encounter cohort, **and it identifies encounter 36 / `SYN-0019` (Dalal Alshamrani) as having no charge** | *"The reconciliation finds the missing charge."* **This is the strongest financial moment in the demo.** Qualify: 10 of 55 reports disabled, **no BI layer** |
| 15b | Ledger | **`SYN-0001`** | RPT-0028 Patient Ledger — needs `?form=1&patient_id=1` | Codes 99213 + 92014, 250.00 + 350.00 = **600.00 SAR** | Totals reconcile to the manifest |
| 16 | **CSV export** | RPT-0009 Appointments | Export CSV, **open it in a spreadsheet on screen** | **38 rows, 7 columns**, no mojibake | *"This is also your exit path."* Qualify: export = CSV **and** database access — **not** a migration service to a named competitor |

**Also show at step 14/15:** Accounting **can** code and re-date the encounter but **cannot open the
clinical note.** Front Office and Clinical Assistant are **denied** the ledger and the reconciliation
outright (403). Verified: PB-037.

---

## 4. Role-separation matrix — the closing slide

| Report | Administrator | Physician | Front Office | Accounting | Clinical Assistant |
|---|---|---|---|---|---|
| Patient List (`patients\|bulk_rep`) | ALLOW | ALLOW | **DENY** | **DENY** | **DENY** |
| Encounters (`encounters\|coding_a`) | ALLOW | ALLOW | **DENY** | ALLOW | **DENY** |
| Appts & Encounters (`acct\|rep_a`) | ALLOW | **DENY** | **DENY** | ALLOW | **DENY** |
| Collections / Aging (`acct\|rep_a`) | ALLOW | **DENY** | **DENY** | ALLOW | **DENY** |
| Audit Tamper Report (`admin\|super`) | ALLOW | **DENY** | **DENY** | **DENY** | **DENY** |

All verified live (PB-037/PB-045). **Two entries do not match §24.3's written expectation** — see §5.

---

## 5. ⚠ OPEN ITEMS — read before rehearsing

| Step | Item | State | What it means for the run |
|---|---|---|---|
| **11** | **The allergy alert cannot fire.** `allergy_conflict()` (`library/clinical_rules.php:354`) matches with a literal SQL `IN`: `prescriptions.drug` must be **byte-identical** to a `lists.title` allergy. Seeded allergies are Penicillin, Sulfa drugs, Latex, Peanuts, Iodine contrast; seeded drugs are Latanoprost, Timolol, Artificial tears, Prednisolone acetate. **No pair matches.** | **BLOCKED** | **Skip step 11's alert, or fix first.** The fix is one row — add an allergy titled exactly `Timolol 0.5% eye drops` to `SYN-0002` (who already holds that prescription). It keeps `allergy_pts = 5` because it is a *second* allergy on an existing patient. **Not applied here:** it changes the dataset, which would invalidate the two sign-offs and the RDY-0044-B baseline. **Owner decision.** Both gating globals are already on |
| **2** | **RDY-0042** — `front_office.json` Add-Patient defect | **OPEN**, but absent on two browser rounds | Likely fine. Confirm in rehearsal 1 before relying on it |
| **9** | **RDY-0043** — `MainMenuRole` drops the first form in each category | **OPEN** | Confirm Vitals is reachable from the menu; if not, reach it from the encounter |
| **4, 15** | §24.3 says Clinician is denied RPT-0009 and Physician denied RPT-0028; **live, both are allowed** | **Owner decision pending** (HR-03) | Do not claim those two denials until decided |
| **D-1** | PB-030 API-row false positive | **OPEN, upstream** | Do not demo D-1 over a range containing an `api_log` row; make no `/apis/*` call during the window |

---

## 6. Rehearsal protocol (RDY-0041)

Two runs, both from a known reset state:

```
reset from RDY-0044-B → verify signature → D-7 rehearsal #1
  → reset from RDY-0044-B → verify signature → D-7 rehearsal #2
```

Record per run: **actual duration**, the step where anything hesitated, any failure condition from
§15 that occurred, and whether the two segment-boundary denials landed. Both runs must pass
independently — a second run that only works because of the first has proven nothing.

**Record the real elapsed time.** V-8 and PRC-003 both consume it, and no one has ever run this.
