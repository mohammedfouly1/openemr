# EV-021 — OPHTHALMOLOGY EXAMINATION CLINICAL REVIEW PACK

**Requirement:** RDY-0021 · **Gate:** G2 · **Reviewer required:** a qualified clinician
**Dataset:** Marketing MVP Seed v1 · **Prepared:** 2026-08-13 · **Status:** AWAITING CLINICAL REVIEW

---

## 0. What is being asked of you, and what is not

You are asked to inspect **eight synthetic ophthalmology examinations** and record **PASS or FAIL for
each**, against four criteria set by the Owner:

1. **clinically plausible**
2. **internally consistent**
3. **not random filler**
4. **appropriate to the synthetic patient's age / history / problem context**

**No patient in this system is real.** Every name, identifier, date and finding is fabricated. You are
not making a care decision; you are judging whether this data would survive being shown to a
clinician during a product demonstration.

**RDY-0021 does not close without your signature.** Technical validation has passed — counts,
attribution, provenance scans — but none of that speaks to clinical credibility, and it is not
offered as though it does.

### 0.1 Disclosure: the first version of this data would have failed, and was rebuilt

The initial seed gave **all eight examinations identical visual acuity** (20/40, 20/50, 20/20,
20/25), identical empty findings, and no relationship to the patient's diagnosis — a glaucoma patient
and an asthma patient had the same eyes, and the patient with diabetic retinopathy had a normal
macula. Ages ran in a strict descending sequence (71, 70, 69, 68 …).

Assessed against criteria 3 and 4 above, that was **filler**, and sending it for review would have
wasted your time. It was rebuilt before this pack was issued: each examination now carries findings
that follow from its diagnosis, and dates of birth are scattered rather than sequential. **This is
disclosed rather than quietly corrected, because you should know the data was authored to satisfy the
criteria you are about to apply** — that is a reason to scrutinise it, not to trust it.

---

## 1. The eight examinations

Findings are recorded as OD (right) / OS (left). `SC` = uncorrected, `MR` = manifest refraction.

| # | Patient | Age / Sex | Recorded problem | Presenting complaint | Intended clinical picture |
|---|---|---|---|---|---|
| 1 | SYN-0001 Hessa Alharthi | 78 F | Type 2 diabetes mellitus | Diabetic eye screening, no visual complaint | Screening exam, **no retinopathy** |
| 2 | SYN-0002 Turki Alqarni | 61 M | Essential hypertension | Routine review, hypertension | Hypertensive retinopathy grade 1 |
| 3 | SYN-0003 Amal Albishi | 44 F | Primary open-angle glaucoma | Glaucoma follow-up, pressure check | POAG, stable on treatment |
| 4 | SYN-0004 Majed Alshamrani | 71 M | Hyperlipidaemia | Routine examination | Corneal arcus, no ocular sequelae |
| 5 | SYN-0005 Dalal Aldawsari | 55 F | Asthma | Gradual blurring of distance vision | Early cataract; **asthma incidental** |
| 6 | SYN-0006 Ziad Alghamdi | 37 M | Diabetic retinopathy | Blurred central vision, right worse than left | Moderate NPDR **with macular oedema** |
| 7 | SYN-0007 Huda Alzahrani | 66 F | none recorded | Glare at night, difficulty reading | Visually significant cataract |
| 8 | SYN-0008 Talal Alsubaie | 49 M | none recorded | Gritty, burning, worse in air conditioning | Dry eye disease |

### Findings as recorded

| # | SC VA | MR VA | Cup/disc | Macula OD / OS | Vessels OD | Lens OD / OS | Other |
|---|---|---|---|---|---|---|---|
| 1 | 20/25 / 20/25 | 20/20 / 20/20 | 0.3 / 0.3 | Flat, no oedema, no exudate / same | Normal calibre | Clear / Clear | — |
| 2 | 20/30 / 20/25 | 20/20 / 20/20 | 0.35 / 0.3 | Flat, no oedema / same | **Arteriolar narrowing, AV nicking** | Trace NS / Trace NS | — |
| 3 | 20/30 / 20/40 | 20/25 / 20/25 | **0.7 / 0.75** | Flat / Flat | Normal | Clear / Trace NS | — |
| 4 | 20/25 / 20/30 | 20/20 / 20/20 | 0.3 / 0.3 | Flat / Flat | Normal | Clear / Clear | **Cornea: arcus senilis** |
| 5 | 20/40 / 20/40 | 20/25 / 20/25 | 0.3 / 0.35 | Flat / Flat | Normal | **NS 1+ / NS 1+** | — |
| 6 | 20/80 / 20/60 | 20/60 / 20/50 | 0.35 / 0.3 | **Centre-involving oedema, hard exudates** / Microaneurysms, no oedema | **Dot-blot haemorrhages, venous beading** | NS 1+ / NS 1+ | **CMT 412 / 268 µm** |
| 7 | 20/100 / 20/80 | 20/60 / 20/50 | 0.3 / 0.3 | No view of detail / Flat | Normal | **NS 3+ / NS 2+** | — |
| 8 | 20/25 / 20/25 | 20/20 / 20/20 | 0.3 / 0.3 | Flat / Flat | Normal | Clear / Clear | **Schirmer 4/5 mm, TBUT 5/6 s** |

Anterior chamber deep and quiet, conjunctiva white and quiet, discs pink with distinct margins in all
eight unless stated.

---

## 2. Points the preparer flags for your specific attention

These are the judgements least defensible without a clinician, raised deliberately rather than left
for you to find:

| # | Point | Why it is flagged |
|---|---|---|
| 3 | **POAG at age 44** | Primary open-angle glaucoma is typically a diagnosis of >50s. It genuinely occurs at 44, but it is atypical, and a reviewer may prefer an older patient |
| 6 | **Diabetic retinopathy with macular oedema at age 37** | Entirely plausible for long-duration type 1 diabetes — but the problem list records only *"Diabetic retinopathy"* with **no diabetes type or duration**, so the record does not itself justify the age |
| 6 | **CMT 412 µm with acuity 20/60** | Intended as internally consistent (centre-involving oedema reducing acuity). Please confirm the pairing is realistic |
| 1 | **Type 2 diabetes at 78 with a completely normal exam** | Plausible for well-controlled disease, but a reviewer may expect *some* background change at that age |
| 5 | **"Asthma" as the only recorded problem on a cataract exam** | Deliberate: it demonstrates that an unrelated comorbidity does not drive the eye findings. Confirm this reads as intentional rather than as a mismatch |
| 7, 8 | **No recorded problem at all** | Cataract and dry eye are the ocular diagnoses, but neither is on the problem list. Should the eye diagnosis appear there? |
| all | **No intraocular pressure recorded anywhere** | IOP is not among the fields populated. For #3 (glaucoma follow-up, *"pressure check"*) its absence is conspicuous |
| all | **No visual fields, no OCT beyond CMT** | The glaucoma and oedema pictures would normally be supported by these |

**The IOP gap is the one most likely to be judged a FAIL**, because examination 3's own presenting
complaint promises a pressure check that the record does not contain.

---

## 3. How to inspect them in the application

1. Log in as a physician account (`y.alharbi` or `s.almutairi`; credentials in the protected store,
   deliberately not written here).
2. Open the patient by the `SYN-` identifier in the table above.
3. Open the encounter dated as listed, then the **Eye Exam** form.
4. The report view renders the full multi-table examination.

Reviewing in the application, not from this table, is preferable — it is what a demo audience sees.

---

## 4. Reviewer determination — one line per examination

| # | Patient | Plausible | Internally consistent | Not filler | Fits age/history | **PASS / FAIL** | Comment |
|---|---|:--:|:--:|:--:|:--:|:--:|---|
| 1 | SYN-0001 | ☐ | ☐ | ☐ | ☐ | ☐ | |
| 2 | SYN-0002 | ☐ | ☐ | ☐ | ☐ | ☐ | |
| 3 | SYN-0003 | ☐ | ☐ | ☐ | ☐ | ☐ | |
| 4 | SYN-0004 | ☐ | ☐ | ☐ | ☐ | ☐ | |
| 5 | SYN-0005 | ☐ | ☐ | ☐ | ☐ | ☐ | |
| 6 | SYN-0006 | ☐ | ☐ | ☐ | ☐ | ☐ | |
| 7 | SYN-0007 | ☐ | ☐ | ☐ | ☐ | ☐ | |
| 8 | SYN-0008 | ☐ | ☐ | ☐ | ☐ | ☐ | |

**Reviewer name and registration:** ☐
**Signature and date:** ☐

**Any FAIL keeps RDY-0021 open.** The seeder is deterministic and the rollback is one command, so a
FAIL costs a re-seed, not a rebuild — **please mark FAIL where you mean it rather than passing
something marginal.** A demo that a clinician in the audience quietly disbelieves is worse than one
that was corrected before it shipped.
