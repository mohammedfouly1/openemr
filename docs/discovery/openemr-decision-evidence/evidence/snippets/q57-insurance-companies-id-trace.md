# Q57 — `insurance_companies.id` call-graph and disposition

_Fork: OpenEMR 8.3.0-dev @ `631f2b38`. Mode: static, READ-ONLY._

## 1. Schema definition (baseline)

`sql/database.sql:3279-3297`:

```sql
CREATE TABLE `insurance_companies` (
  `id` int(11) NOT NULL default '0',
  `uuid` binary(16)   DEFAULT NULL,
  `name` varchar(255) default NULL,
  ...
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB;
```

- `id` is **`int(11) NOT NULL default '0'`** — **no `AUTO_INCREMENT`**.
- `uuid binary(16)` UNIQUE shadow (added by `sql/6_0_0-to-6_1_0_upgrade.sql:108-113`).
- Registered with `UuidRegistry` at `src/Common/Uuid/UuidRegistry.php:57`.

## 2. Upgrade history for `insurance_companies.id`

`Select-String 'insurance_companies' sql/*_upgrade.sql` yields 25 hits across 8 upgrade files (2.6.0 through 7.0.3). Every hit either **adds a new column** (`x12_default_partner_id`, `alt_cms_id`, `inactive`, `eligibility_id`, `x12_default_eligibility_id`, `uuid`, `cqm_sop`, `date_created`, `last_updated`) or modifies a non-id column type. **Zero of them touch the `id` column.** Full list: `evidence/raw/q57-upgrade-insurance-companies-all.txt`.

## 3. ID-assignment call graph

```
InsuranceCompanyService::insert($data)           src/Services/InsuranceCompanyService.php:431-469
    ├── if empty($data['id']): $data['id'] = QueryUtils::generateId()
    ├── source comment (431-436):
    │       "insurance companies need to use sequences table since they share the
    │        addresses table with pharmacies"
    │       "I don't like actually inserting a raw id... yet if we don't allow
    │        for this it makes it very hard for any kind of data import that
    │        needs to maintain the same id."
    └── QueryUtils::sqlInsert("INSERT INTO insurance_companies SET id=?, name=?, ...", [...])

InsuranceCompany::persist()                       library/classes/InsuranceCompany.class.php:338-375
    └── QueryUtils::inTransaction(function () {
            parent::persist();                    // ORDataObject::persist -> generateId + INSERT
            $this->address->persist($this->id);
            DELETE FROM phone_numbers WHERE foreign_id = ?
            foreach phone: $phoneService->insert(...)
        });
    // Note: this IS wrapped in a transaction (unlike Document::persist()).
```

`ORDataObject::persist()` (`src/Common/ORDataObject/ORDataObject.php:80-84`):

```php
if (in_array($field, $pkeys) && empty($val)) {
    $last_id = QueryUtils::generateId();
    $this->{"set_" . $field}($last_id);
    $val = $last_id;
}
```

### Callers that INSERT into `insurance_companies`

| Site | ID source |
|---|---|
| `src/Services/InsuranceCompanyService.php:445` — the ONLY raw `INSERT INTO insurance_companies` in the repo | `QueryUtils::generateId()` at `:438` if caller didn't supply one. Explicit `id=?` bind. |
| `library/classes/InsuranceCompany.class.php:347` via `parent::persist()` → ORDataObject | Same generator. Wrapped in `QueryUtils::inTransaction()`. |

Grep-verified: `INSERT INTO insurance_companies` returns exactly **1 hit** in the entire codebase (the service). All other writers go through `InsuranceCompany::persist()` (ORDataObject) or `InsuranceCompanyService::insert()`.

### Concurrency reality

Identical to `documents` (they share the same generator):

- `QueryUtils::generateId()` → `GenID('sequences')` → `UPDATE sequences SET id=LAST_INSERT_ID(id+1); SELECT LAST_INSERT_ID()` (atomically documented at `src/BC/Database.php:161-174` with in-source race-caveat comment).
- **`InsuranceCompany::persist()` uniquely wraps the id-generation + INSERT in `QueryUtils::inTransaction()`** — meaning a rollback burns the generated id (gap in the sequence). Not a correctness problem, just a naming-quirk.
- No `GET_LOCK`, no `SELECT MAX(id)+1`, no retry loop.

## 4. Test evidence

- `tests/Tests/Services/AddressServiceTest.php:49-54`: *"Use the sequences table like InsuranceCompanyService does. $id = QueryUtils::generateId();"* — canonical pattern.
- `tests/Tests/Fixtures/BaseFixtureManager.php:190-191` — fixtures call `QueryUtils::generateId()` for insurance company test rows.
- **No concurrency test proves the atomicity of `sequences` under parallel `insert()` calls.** The single-connection PHPUnit runner cannot exercise the race window.

## 5. Foreign-key consumers

- Zero enforced FKs into `insurance_companies` (`Select-String 'FOREIGN KEY' sql/database.sql` → 0 matches outside comments).
- Columns referencing `insurance_companies.id` by convention: `insurance_data.provider` (documented as `int(11) — references insurance_companies.id`, `sql/2_9_0-to-3_0_0_upgrade.sql:48` calls it out explicitly), `x12_partners`, plus 2 more (`sql/database.sql:1926, 3356`). All soft references.

## 6. Safety analysis for `insurance_companies.id` → `AUTO_INCREMENT`

Same analysis as `documents`, with one additional consideration:

| Risk vector | Assessment |
|---|---|
| Existing `INSERT INTO insurance_companies (id, ...) VALUES (0, ...)` | **No such pattern.** The single INSERT site always binds an explicit non-zero id. |
| Data-import parity requirement | The in-source comment (`InsuranceCompanyService.php:435-436`) explicitly calls out that import paths **rely on the ability to insert an explicit id**. AUTO_INCREMENT preserves this ability (MySQL accepts explicit non-zero values). |
| `insurance_companies.id` sharing an id-space with anything | The comment at `:433-434` says "they share the addresses table with pharmacies" — meaning the historical concern was that `addresses.foreign_id` referenced ids from multiple foreign tables (companies + pharmacies + insurance_companies), so a global counter avoided collisions. **This concern is still active** — `pharmacies` and `insurance_companies` both use ids from `sequences` today. If we convert `insurance_companies.id` to AUTO_INCREMENT independently, we break the invariant that "no two rows in {companies, pharmacies, insurance_companies} share an id". Address rows keyed by `(foreign_id, ...)` without a type tag would become ambiguous. |
| Migration path breakage | See above — the addresses-table sharing is the load-bearing reason. Any conversion would require simultaneous conversion of all sequence-sharing peers or a `type` column addition to `addresses`. |

**Verdict: `insurance_companies.id` is a HARDER conversion than `documents.id`** — it participates in a cross-table id-space with `pharmacies` (and possibly `companies`, per `library/classes/Company.class.php`). Recommend leaving it as-is unless a broader refactor of `addresses.foreign_id` is undertaken.

## 7. Direct answers

1. **Is the non-AUTO_INCREMENT deliberate?** — **Yes, deliberately.** The in-source comment at `src/Services/InsuranceCompanyService.php:433-436` documents the reasoning: shared address-table id-space with `pharmacies`, plus explicit support for import paths that need to preserve external ids.
2. **Which code assigns the value?** — `InsuranceCompanyService::insert()` (`src/Services/InsuranceCompanyService.php:438`) and `ORDataObject::persist()` (`src/Common/ORDataObject/ORDataObject.php:80-84`), both via `QueryUtils::generateId()` → `sequences` table.
3. **Is conversion to AUTO_INCREMENT safe?** — **No, not without a coordinated refactor.** The `addresses` table's `foreign_id` column holds ids from multiple source tables in a shared id-space. Converting one participant to a private AUTO_INCREMENT sequence would create the possibility of an `addresses.foreign_id` collision between a `pharmacies` row and an `insurance_companies` row.
4. **What would break if it changed in isolation?** — `Address::factory_address($this->id)` lookups (used at `InsuranceCompany.class.php:321` and `Pharmacy.class.php:201`) would eventually return the wrong row once ids overlap. Silent data corruption, not a crash.
5. **What key strategy should new `custom_saas_*` tables use?** — Same as Q57-documents recommendation: **`bigint AUTO_INCREMENT PRIMARY KEY` + `uuid binary(16) NOT NULL UNIQUE`** shadow. Additionally, **do NOT share id-spaces across tables** — each SaaS-layer table gets its own private AUTO_INCREMENT. If cross-table references are needed, use `(source_table, source_id)` tuple columns, not a shared bare-integer `foreign_id`. This avoids replicating the addresses-table entanglement.
