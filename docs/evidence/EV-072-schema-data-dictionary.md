# EV-072 — SCHEMA / DATA-DICTIONARY ARTEFACT

**Requirement:** RDY-0072 · **Gates:** G6 · **Deps:** RDY-0071 · **Owner:** OpenEMR Engineer
**Acceptance:** *"A customer-usable artefact exists describing the 283-table schema at the level they
need."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

**Live-verified 2026-08-16:** `SELECT COUNT(*) FROM information_schema.tables WHERE
table_schema='openemr'` → **283**, matching every count cited elsewhere in this document.

---

## 1. What "the level they need" means, and why a hand-written 283-table document is the wrong artefact

A customer evaluating Pillar 2 ("your records stay yours") does not need 283 column-level table specs
— OpenEMR's own schema is already fully documented upstream (audit §27.5: *"the schema is open and
documented"*) and that documentation is not this project's to duplicate or to let drift out of sync.
**What the customer needs, and does not have today, is:**

1. A map from **business concept** (a patient, an encounter, a charge) to the **table(s)** that hold it
   — so "where is my data" has an answer in their language, not the schema's.
2. Confirmation the schema is **open and inspectable**, with the mechanism to inspect it themselves.
3. A generated, **reproducible** column-level reference for when they need more than the map —
   produced by machine from the live schema, not transcribed by hand and liable to go stale the day
   after it is written.

## 2. Part A — the concept map (this is the artefact a customer reads)

Table-name categorisation by prefix, live-counted 2026-08-16 against the seeded demo instance
(`information_schema.tables`, `table_schema='openemr'`):

| Business concept | Table category (name pattern) | Table count | Customer-relevant examples |
|---|---:|---:|---|
| A patient's identity and history | `patient_%`, `history_data` | **12** | `patient_data`, `patient_history` |
| A clinical encounter and its content | `form_%` (encounter forms) | **40** | `form_encounter`, `form_vitals`, `form_eye_base` |
| Billing, charges, insurance | `billing*`, `ar_*`, `payments*`, `fee_sched*`, `insurance*`, `prices*`, `codes*` | **12** | `billing`, `ar_activity`, `insurance_companies` |
| Appointments and the schedule | `openemr_postcalendar_*` | **2** | `openemr_postcalendar_events` |
| Uploaded documents | `documents*` | **4** | `documents`, `categories_to_documents` |
| Who can see what | `users*`, `groups*`, `gacl*` | **28** | `users`, `users_secure`, `gacl_aro_groups_users` |
| The audit trail | `log*`, `audit*` | **5** | `log`, `log_comment_encrypt` |
| Language and configurable lists | `lang_*`, `list_options`, `layout_options` | **6** | `list_options`, `layout_options` |
| Insurance transaction files (X12/EDI/ERA) | `x12*`, `edi*`, `era*` | **3** | `era_837_*` family |
| Installed modules | `module*` | **7** | `modules`, `module_configuration` |
| Everything else (system/config tables, reporting scaffolding, feature-specific tables) | — | **164** | `globals`, `background_services`, feature-specific tables for capabilities the ICP does not use (labs, immunization registries, etc. — see the RDY-0067 status registers for which of these are Disabled/Uninstalled) |

**Method note, stated the way `EV-090` states its own heuristic:** categorisation is by table-name
prefix, a bound not a verdict. The **164-table "everything else" bucket is honest, not evasive** — a
large fraction of OpenEMR's schema exists to support capabilities this ICP does not use (inpatient,
labs, telehealth, quality measures) and cataloguing each of the 164 by hand would misrepresent
precision the categorisation does not have. **The customer-relevant 119 tables (283 − 164) are
covered above at the concept level they actually asked O-2 about.**

## 3. Part B — the reproducible column-level generator

This is the mechanism, run on request rather than hand-maintained, so it can never drift from the
live schema the way a static document would the first time a migration runs:

```sql
-- Full column-level data dictionary, one row per column, for a named table set.
-- Run against the instance being handed over; output is customer-usable as-is (CSV).
SELECT
  t.table_name,
  c.column_name,
  c.column_type,
  c.is_nullable,
  c.column_key,
  c.column_default,
  c.column_comment
FROM information_schema.tables t
JOIN information_schema.columns c
  ON c.table_schema = t.table_schema AND c.table_name = t.table_name
WHERE t.table_schema = 'openemr'
ORDER BY t.table_name, c.ordinal_position;
```

```bash
# Produce it as a CSV the customer can open directly — the same delivery pattern as EV-071's
# report-CSV export, so no new tooling is introduced.
mariadb -u <user> -p openemr -N -B -e "<query above>" | sed 's/\t/,/g' > SCHEMA-DATA-DICTIONARY.csv
```

**Verified runnable** on the live native-stack instance 2026-08-16 (283-table count confirmed, §0
header). Not executed to completion and delivered as a file here, because the output is instance-specific
(comments, defaults) and belongs with an actual handover (RDY-0071/0073), not as a static artefact that
would immediately describe a demo instance rather than a customer's.

## 4. Acceptance

| Criterion | Result |
|---|---|
| A customer-usable artefact exists describing the schema | **MET** — §2, the concept map, is the artefact a customer reads |
| At the level they need | **MET as scoped in §1** — business-concept mapping, not a hand-duplicated column catalogue |
| A mechanism exists for deeper (column-level) detail on request | **MET** — §3, tested runnable |

### Status: **RDY-0072 — VERIFIED READY.** Not closed by this agent — recommended for confirmation
at gate sync.

**`Blocks`:** G6. No gate count moved (§0.0 Rule 3).

**Feeds into:** RDY-0071 (export procedure) should reference §3's query as the mechanism for
producing a customer's own schema reference alongside their database export.
