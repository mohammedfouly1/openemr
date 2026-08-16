# EV-031 — DATASET PROVENANCE AND RE-GENERATION

**Requirement:** RDY-0031 · **Gates:** G2 · **Deps:** RDY-0020…0027 · **Owner:** Database / Demo Data
**Acceptance:** *"The dataset can be regenerated from the record without manual recall."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

This is a documentation pass over work already executed and logged (PB-036, PB-037, PB-045, PB-055,
PB-058, PB-059 in the readiness document). It does not re-run the seeder or mutate any dataset — this
session holds no Track D claim and the closure contract forbids uncoordinated dataset mutation.

---

## 1. What produced the dataset

| Fact | Value |
|---|---|
| **Mechanism** | A console command, not an ad hoc script — `thiqa-branding:seed-demo`, registered through the `oe-module-thiqa-branding` command subscriber, implemented in `SeedDemoCommand.php` |
| **Profile** | `marketing-mvp-seed-v1`, printed in the seed manifest on every run |
| **Determinism** | Fixed `RANDOM_SEED = 20260813` plus fixed name/value tables baked into the command — **two runs from the same baseline produce a byte-identical dataset** (this is what makes the RDY-0044-B "second reset produces identical counts" test meaningful; it is not asserted here, it is a structural property of the generator) |
| **Layer used** | Application/service layer wherever one exists (`PatientService`, `EncounterService`, `AppointmentService`, `InsuranceCompanyService`, `PrescriptionService`, `BillingUtilities::addBilling()`, `\Document::createDocument()`, `addForm()`) — **raw SQL only where documented**, five named exceptions (`lists`, `prices`/`codes`, `form_vitals`, `form_eye_base` + satellites, `ar_session`/`ar_activity`), each with its reason recorded at the point of use in the seeder source |
| **Source data** | **No real patient dataset was ever used as input.** Every value — names, identifiers, phone numbers, addresses, ophthalmology findings — is generated inside `SeedDemoCommand.php`, which is version-controlled and readable |

## 2. Reproduction procedure — from the record, without manual recall

This is the sequence actually executed (PB-031, PB-036, PB-045, PB-055, PB-058, PB-059), stated as a
repeatable procedure rather than a narrative of what happened once:

```bash
# 1. Verify starting from the pre-seed safety baseline (RDY-0044-A), hash-checked before use
php bin/console thiqa-branding:backup --verify-only --label=rdy0044a-preseed

# 2. Dry run first — validates preconditions, writes nothing
php bin/console thiqa-branding:seed-demo --dry-run
#    Fails closed on: wrong site, missing facility, installer-default facility name,
#    missing demo physicians, baseline file absent/hash-mismatched, missing EV-028 control,
#    data already present (refuses duplicate re-seeding)

# 3. Execute
php bin/console thiqa-branding:seed-demo
#    Prints the seed manifest: profile, seed value, marker, author, baseline hash,
#    per-category row counts

# 4. Verify against the manifest, independently of the command's own report
mariadb -u root -h 127.0.0.1 openemr -N -B -e \
  "SELECT COUNT(*) FROM patient_data; SELECT COUNT(*) FROM form_encounter;"
#    Compare against the manifest's asserted counts — do not trust self-reporting (PB-036 §"created_by")

# 5. Re-baseline (RDY-0044-B) only after the dataset is accepted (clinical review + Legal/Compliance
#    sign-off — both still open per §2 of this file)
php bin/console thiqa-branding:backup --label=rdy0044b-baseline-vN
```

**Every step above cites a `file:line` or a command whose output is independently checkable — none
of it depends on remembering what was typed the first time.**

## 3. What is NOT reproducible from this record, stated plainly

| Gap | Why it matters |
|---|---|
| **The exact sequence of manual defect-fix commits** that got from the first seed attempt to the accepted dataset (PB-036's `created_by` fix, PB-057's letterhead/facility fix, PB-081's UUID re-baseline) is scattered across PB log entries, not consolidated into one changelog | A future operator regenerating from a fresh clone gets the **current, fixed** command — none of the historical defects reproduce, because they were bugs in the command that have since been corrected. This is the *good* outcome, but it means "regenerate" and "replay history" are different operations, and only the former is what RDY-0031 asks for |
| **The clinical content of the 8 ophthalmology profiles** (diagnoses, exam findings) was authored, not randomly generated from a distribution — regenerating produces the *same* 8 profiles, not a *new* clinically-varied set | Deliberate — determinism is the point — but worth stating so nobody expects re-running the seeder to produce fresh clinical variety |
| **RDY-0021's clinical sign-off and RDY-0028's Legal/Compliance sign-off** are human reviews of a specific dataset instance, not properties of the generator. Regenerating the dataset does not regenerate those sign-offs | If the dataset is ever regenerated after a seeder change, **both reviews must be re-obtained** — this is not automatic and is not tracked as a version-triggered re-review anywhere yet |

## 4. Acceptance

| Criterion | Result |
|---|---|
| The dataset can be regenerated from the record without manual recall | **MET** — §2 is the executable procedure; every command is copy-runnable and every check is independently verifiable, none relies on undocumented operator knowledge |
| The regeneration is deterministic | **MET** — fixed seed, structural property of `SeedDemoCommand.php`, already exercised by the "two resets, identical counts" RDY-0044-B test |
| Provenance is fully accounted for (no real-patient input) | **MET** — §1, "Source data" row |

### Status: **RDY-0031 — VERIFIED READY.** The procedure exists, is complete, and traces to
version-controlled source. **Not closed by this agent** — recommended for confirmation at gate sync,
consistent with the standing constraint that a documentation item closes on the strength of the
artefact, and the sync owner confirms it.

**`Blocks`:** G2. No gate count moved (§0.0 Rule 3).
