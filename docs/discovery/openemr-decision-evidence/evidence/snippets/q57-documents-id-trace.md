# Q57 — `documents.id` call-graph and disposition

_Fork: OpenEMR 8.3.0-dev @ `631f2b38`. Mode: static, READ-ONLY._

## 1. Schema definition (baseline)

`sql/database.sql:1391-1432`:

```sql
CREATE TABLE `documents` (
  `id` int(11) NOT NULL default '0',
  `uuid` binary(16) DEFAULT NULL,
  ...
  PRIMARY KEY  (`id`),
  UNIQUE KEY `drive_uuid` (`drive_uuid`),
  UNIQUE KEY `uuid` (`uuid`),
  ...
) ENGINE=InnoDB;
```

- `id` is **`int(11) NOT NULL default '0'`** — **no `AUTO_INCREMENT`**.
- `uuid binary(16)` present as UNIQUE shadow key (added by `sql/6_0_0-to-6_1_0_upgrade.sql:741`).
- Registered with `UuidRegistry` at `src/Common/Uuid/UuidRegistry.php:43` (`'documents' => ['table_name' => 'documents']`).

## 2. Upgrade history for `documents.id`

`Select-String 'ALTER TABLE.*documents' sql/*_upgrade.sql` yields 27 hits across 12 upgrade files (2.8.2 through 7.0.2) — **all `ADD COLUMN` / `MODIFY <non-id>` operations. Zero of them touch the `id` column.** Full list: `evidence/raw/q57-upgrade-documents-broad.txt`.

**Verdict: the `id` column definition has been stable and untouched since the earliest tracked schema.** The non-`AUTO_INCREMENT` design is deliberate — it has survived 12+ years of migrations without correction.

## 3. ID-assignment call graph

```
QueryUtils::generateId()                    src/Common/Database/QueryUtils.php:352-355
    └── ADODB $db->GenID('sequences')
            └── UPDATE sequences SET id=LAST_INSERT_ID(id+1); SELECT LAST_INSERT_ID()
                  (documented literally in src/BC/Database.php:161-174,
                   with in-source comment: "Caution: this _may_ have data
                   races in the current implementation, especially across
                   parallel requests.")

sequences table:  sql/database.sql:9559-9562
    CREATE TABLE `sequences` ( `id` int(11) unsigned NOT NULL default '0' ) ENGINE=InnoDB;
    -- single-row global counter; NOT per-table
```

### Callers that INSERT into `documents`

| Site | ID source |
|---|---|
| `library/classes/Document.class.php:855-864` — `Document::persist($fid='')` → `parent::persist()` | `ORDataObject::persist()` calls `QueryUtils::generateId()` when the PK field's getter returns empty (`src/Common/ORDataObject/ORDataObject.php:80-84`). Plain `INSERT INTO documents SET ...`. **No transaction wrapper** on this path (unlike `InsuranceCompany::persist()` which does wrap). |
| `interface/fax/fax_dispatch.php:164-174` — inbound fax → new document | Explicit: `$newid = QueryUtils::generateId(); INSERT INTO documents (id, ...) VALUES (?, ...)`. |
| `src/Services/DocumentService.php:152-153` — API upload path | Delegates to `(new \Document)->createDocument(...)` → `Document::persist()` → same generator. |
| `src/Services/Cda/CdaTemplateImportDispose.php:595` | Uses `generateId()` for `form_encounter`, not documents. Documents in CDA import go through `Document::persist()`. |
| `contrib/util/emr_scan_load.plx:433` | Perl helper, out of PHP scope. Provides its own id via Perl. |

**No `INSERT INTO documents (id, ...) VALUES (0, ...)` pattern found anywhere.** Every writer assigns a fresh id up-front.

### Concurrency reality

- `QueryUtils::generateId()` (`QueryUtils.php:352-355`) delegates straight to ADODB `GenID('sequences')`. The equivalent modern DBAL implementation in `src/BC/Database.php:168-174` (`generateSequentialId`) uses `UPDATE sequences SET id=LAST_INSERT_ID(id+1)` — an **atomic row-level increment** on InnoDB — followed by `SELECT LAST_INSERT_ID()` which is **session-scoped**.
- The atomic UPDATE prevents duplicate IDs across concurrent transactions. However, the in-source comment ("Caution: this _may_ have data races") flags that **transaction rollback burns the generated id** (like a real sequence — gaps are expected).
- No `GET_LOCK` / no retry loop / no `SELECT MAX(id)+1` anywhere in the path.
- `EventAuditLogger.php:439` explicitly excludes `sequences` writes from the audit log — confirming these are considered noise/high-frequency.

## 4. Test evidence

`Select-String tests/ -Pattern 'generateId|DocumentService|InsuranceCompanyService::insert'` — 15 hits (`evidence/raw` not saved for this narrow scan; see grep results in main §10 doc). Notable:

- `tests/Tests/Fixtures/BaseFixtureManager.php:190-191` — test fixtures call `QueryUtils::generateId()` to build sample rows.
- `tests/Tests/Services/AddressServiceTest.php:49-54` — uses the same pattern with commentary: *"Use the sequences table like InsuranceCompanyService does."*
- **No concurrency / race-condition test exists** for `generate_id()` or its callers. There is no test that would catch a regression if the row-level UPDATE atomicity were broken.

## 5. Foreign-key consumers

- `Select-String 'FOREIGN KEY' sql/database.sql` → 4 hits, **all inside `COMMENT` strings** (`sql/database.sql:15138-15156`). Zero enforced FKs against `documents.id` (confirms prior audit §6).
- Columns referencing `documents.id` by convention (bare `document_id`): `sql/database.sql:367, 10507, 15139` — 3 core tables (`categories_to_documents.document_id`, `procedure_result.document_id`, `form_clinical_notes_document_link.document_id`). Consumers care about the **value**, not the sequence mechanism.

## 6. Safety analysis for `documents.id` → `AUTO_INCREMENT`

| Risk vector | Assessment |
|---|---|
| Existing `INSERT INTO documents (id, ...) VALUES (0, ...)` breaks | **No such call site exists.** All writers assign an id before insert. Zero broken paths. |
| `INSERT ... SET id=?` with a non-zero value | Would still work — MySQL `AUTO_INCREMENT` honors explicit non-zero values. **Zero broken paths.** |
| `INSERT ... VALUES (0, ...)` (would trigger auto-assign) | No such call site exists in the codebase. |
| `generate_id()` / `QueryUtils::generateId()` becomes dead code for documents | It would remain live for `form_encounter`, `insurance_companies`, and the `ORDataObject` polymorphic path — cannot remove globally. But the `documents`-specific calls (fax_dispatch, Document::persist) would produce ids that no longer collide with the AUTO_INCREMENT sequence, unless MySQL's `AUTO_INCREMENT` bumps past the manually-set values (it does automatically). **Safe but wasteful** unless the app is also refactored to stop pre-allocating. |
| Import / migration paths (CCDA, ERA, patient import) | CCDA import at `CdaTemplateImportDispose.php:595` targets `form_encounter`, not `documents`. Document creation during import still flows through `Document::persist()`. **No breakage.** |
| FK consumer tables (`categories_to_documents`, `procedure_result`, `form_clinical_notes_document_link`) | Zero enforced FKs — consumers store the integer value. **Indifferent** to how the id was generated. |
| Value stability for legacy exports / `documents.hash` linkage | AUTO_INCREMENT would produce a strictly-increasing series; existing rows retain their ids because MySQL only auto-assigns on `NULL`/`0`/omitted. **Safe.** |

## 7. Direct answers

1. **Is the non-AUTO_INCREMENT deliberate?** — Effectively yes, by inertia. The pattern has survived 27 `ALTER TABLE documents` operations across 12 upgrade files without correction, and the sequence-table mechanism (`QueryUtils::generateId()` → `sequences`) is a first-class documented API with a PHPStan rule (`tests/PHPStan/Rules/ForbiddenFunctionsRule.php:36`) enforcing its use. The one-line `InsuranceCompanyService.php:433-436` comment (*"insurance companies need to use sequences table since they share the addresses table with pharmacies"*) hints at the historical reason: **globally-unique ids across sibling tables sharing satellite tables** (documents shared foreign_id semantics with categories_to_documents in older schemas). No modern justification for keeping documents on this pattern is documented.
2. **Which code assigns the value?** — `ORDataObject::persist()` (`src/Common/ORDataObject/ORDataObject.php:80-84`) for the OO path, or explicit `QueryUtils::generateId()` for procedural writers (`interface/fax/fax_dispatch.php:164`). Both flow through the same `sequences`-table generator (`QueryUtils.php:352-355`).
3. **Is conversion to AUTO_INCREMENT safe?** — **Yes, technically safe.** No writer inserts `id=0`; all writers pre-allocate. AUTO_INCREMENT accepts explicit non-zero values without conflict. Existing rows unchanged.
4. **What would break if it changed?** — Nothing at the write path. The `sequences` table would remain the id source (writers not refactored) — AUTO_INCREMENT would simply be unused for documents. Removing the pre-allocation code would be a separate, larger refactor touching `ORDataObject` (used by ~20 legacy classes). **Recommend: do not change upstream.** Any change must be paired with an application-layer refactor.
5. **What key strategy should new `custom_saas_*` tables use?** — **`bigint AUTO_INCREMENT PRIMARY KEY` + `uuid binary(16) NOT NULL UNIQUE`** shadow. Rationale:
   - Mirrors the modern convention seen on `patient_data`, `form_encounter`, `insurance_data`, `lists`, `users`, `prescriptions` (all listed in prior audit §5).
   - AUTO_INCREMENT eliminates the shared-`sequences`-table hot spot (a single global counter is a scaling ceiling in a multi-tenant SaaS).
   - `binary(16)` UUID as **shadow, not PK** — matches the FHIR/API pattern via `UuidRegistry`. If UUIDv7 is chosen, its lexicographic ordering keeps the UUID index tight without needing it to be the PK. Do **not** use UUID as the primary key on InnoDB — random UUIDs fragment the clustered index; UUIDv7 mitigates but does not eliminate that cost, and the `bigint` PK is what all existing joins use.
