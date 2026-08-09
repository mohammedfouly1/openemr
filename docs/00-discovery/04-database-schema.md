# 04 — Database Schema Audit (Read-Only, Source-of-Truth: `sql/`)

**Fork:** OpenEMR `8.3.0-dev` (repo HEAD).
**Method:** Static inspection only. No DB connection, no SQL executed. All findings cite files under `D:\OpenEmr\`.
**Baselines fixed by repo:** DB schema version `541`, ACL schema version `13` (per `sql/database.sql` header L4-6 and `version.php`).

---

## 1. Files Inventory (`sql/`)

`git ls-files sql/` returns **46 files** (Windows `Measure-Object -Line`). Grouped:

| Group | Count | Files / Pattern |
|---|---:|---|
| Baseline schema | 1 | `sql/database.sql` (15,395 lines, **281** `CREATE TABLE` statements) |
| Version-to-version upgrades | 36 | `sql/<from>-to-<to>_upgrade.sql` — from `2_6_0-to-2_6_1_upgrade.sql` through `8_2_0-to-8_3_0_upgrade.sql` |
| Point-release patch | 1 | `sql/patch.sql` |
| Seed / example data | 3 | `sql/example_patient_data.sql`, `sql/example_patient_users.sql`, `sql/official_additional_users.sql` |
| Code-set seed | 1 | `sql/cvx_codes.sql` (CVX immunization codes) |
| Localization seed | 1 | `sql/ins_lang_def_nl.sql` (Dutch language pack) |
| IPPF fork variants | 2 | `sql/ippf_layout.sql`, `sql/ippf_upgrade.sql` |
| Ea/mixed seed | 1 | `sql/openemr-ea-mixed-complete.sql` |

**Last 5 upgrade files** (chronological):
`7_0_4-to-8_0_0_upgrade.sql`, `8_0_0-to-8_1_0_upgrade.sql`, `8_1_0-to-8_1_1_upgrade.sql`, `8_1_1-to-8_2_0_upgrade.sql`, `8_2_0-to-8_3_0_upgrade.sql`.

**Baseline table count:** **281** (`Select-String "^CREATE TABLE" sql\database.sql`).

---

## 2. DB Engine, Charset, Collation

Extracted from `sql/database.sql`:

| Directive | Occurrences | Value |
|---|---:|---|
| `ENGINE=InnoDB` | **255** | (only value seen) |
| `ENGINE=` non-InnoDB | 0 | — |
| Table-level `DEFAULT CHARSET=` | 0 | (none written at table level) |
| Table-level `COLLATE=` | 0 | (none written at table level) |

- 255 tables have explicit `ENGINE=InnoDB`; the remaining ~26 `CREATE TABLE` statements omit the ENGINE directive (inherit server default). No MyISAM in the baseline.
- **Charset/collation are set at the CONNECTION / server layer, not per-table.** Dev docker compose runs MariaDB with `--character-set-server=utf8mb4` (`docker/development-easy/docker-compose.yml`). New migrations use `CreateTableTrait` which "sets appropriate charset/collation" (`db/Migrations/README.md:36`).
- `sql/database.sql` contains **no literal `utf8mb4`** — the string does not appear at all in the baseline schema. UTF8MB4 is applied via server config, not DDL. This is a known OpenEMR upgrade concern (utf8 → utf8mb4 migration is handled per-upgrade script; see `sql/4_2_2-to-5_0_0_upgrade.sql:89` "changing all *TEXT fields to…").

---

## 3. MySQL / MariaDB Version Expected

No `MINIMUM_MYSQL_VERSION` constant exists in the codebase (`src/Common/Compatibility/Checker.php` only enforces `minimumPhpVersion`). Version support is expressed via the **CI matrix**:

| Source | Value |
|---|---|
| `docker/development-easy/docker-compose.yml` (dev image) | `mariadb:11.8.8` |
| `.github/workflows/integration-tests.yml` | MySQL 5.7, 8.4 LTS, 9.7 LTS; MariaDB 10.6, 10.11, 11.4, 11.8 LTS, 12 |
| `ci/README.md:18` | MariaDB/MySQL v11.6 |
| `Documentation/EHI_Export/docs/openemr.openemr.xml:1` (last EHI export snapshot) | MariaDB 11.4.9 |
| `tests/PHPStan/Rules/Sql/snapshots/mariadb.tsv` | MariaDB 12.3.2 (reserved-word snapshot target) |
| `tests/PHPStan/Rules/Sql/snapshots/mysql.tsv` | MySQL 9.7.1 |

**De facto floor** (from CI passing configurations): **MySQL 5.7 / MariaDB 10.6**. **Dev target:** MariaDB 11.8. There is no hard runtime version check; older engines will fail on specific DDL (e.g., utf8mb4 features, JSON columns) rather than a preflight gate. `UNKNOWN — minimum supported engine version is not codified in any Checker class; requires product-owner input if we need a hard floor.`

---

## 4. Table Domain Grouping (from baseline `sql/database.sql`)

Numbers are from `Select-String "^CREATE TABLE"` extraction of the 281 tables.

| Domain | Count (approx.) | Key Tables |
|---|---:|---|
| **Patient / demographics** | ~5 | `patient_data`, `patient_history`, `patient_access_offsite`, `patient_access_onsite`, `patient_tracker`, `patient_tracker_element` |
| **Encounters & forms shell** | 2 + | `form_encounter`, `forms` (dispatcher table listing which per-encounter form rows exist) |
| **Clinical form_* tables** | **39** | `form_soap`, `form_vitals`, `form_ros`, `form_reviewofs`, `form_clinical_notes`, `form_clinical_instructions`, `form_care_plan`, `form_history_sdoh_health_concerns`, `form_functional_cognitive_status`, `form_observation`, `form_dictation`, `form_misc_billing_options`, `form_groups_encounter`, `form_group_attendance`, `form_taskman`, plus 12 `form_eye_*` and specialty forms |
| **Problems / meds / allergies** | 1 shared | `lists` (single polymorphic table with `type` column: problem / allergy / medication / etc.), `lists_touch` |
| **Prescriptions & orders** | ~4 | `prescriptions`, `procedure_order`, `procedure_order_code`, `procedure_report`, `procedure_result`, `procedure_answers`, `procedure_providers`, `procedure_questions`, `procedure_type` |
| **Immunizations** | 1 | `immunizations` (+ seeded `cvx_codes.sql`) |
| **Billing / A-R** | ~12 | `billing`, `ar_activity`, `ar_session`, `claims`, `x12_partners`, `x12_remote_tracker`, `insurance_data`, `insurance_companies`, `insurance_numbers`, `insurance_type_codes`, `payments`, `payment_gateway_details`, `payment_processing_audit`, `fee_sheet_options` |
| **Scheduling** | 2 | `openemr_postcalendar_events`, `openemr_postcalendar_categories` (Postcalendar-derived) |
| **Users** | 1 | `users` (single table, no separate `staff`/`provider`; role encoded via ACL) |
| **ACL — legacy phpGACL** | **24** | `gacl_acl`, `gacl_acl_sections`, `gacl_acl_seq`, `gacl_aco`, `gacl_aco_map`, `gacl_aco_sections`, `gacl_aco_sections_seq`, `gacl_aco_seq`, `gacl_aro`, `gacl_aro_groups`, `gacl_aro_groups_id_seq`, `gacl_aro_groups_map`, `gacl_aro_map`, `gacl_aro_sections`, `gacl_aro_sections_seq`, `gacl_aro_seq`, `gacl_axo`, `gacl_axo_groups`, `gacl_axo_groups_map`, `gacl_axo_map`, `gacl_axo_sections`, `gacl_groups_aro_map`, `gacl_groups_axo_map`, `gacl_phpgacl` |
| **ACL — module-scoped** | 3 | `module_acl_group_settings`, `module_acl_sections`, `module_acl_user_settings` |
| **Code sets** | 2 | `codes`, `code_types`. Seeded coding systems (per `INSERT INTO code_types` in `sql/database.sql:10618-10635`): **ICD9, CPT4, HCPCS, CVX, DSMIV, ICD10, SNOMED, CPTII, ICD9-SG, ICD10-PCS, SNOMED-CT, SNOMED-PR, RXCUI, LOINC, PHIN Questions, NCI-CONCEPT-ID, VALUESET, OID.** RXNORM present as `RXCUI` (ct_id 109). |
| **Documents** | 7 | `documents`, `document_templates`, `document_template_profiles`, `documents_legal_master`, `documents_legal_detail`, `documents_legal_categories`, `categories` |
| **Audit / logging** | 10 | `log`, `api_log`, `audit_master`, `audit_details`, `extended_log`, `clinical_rules_log`, `direct_message_log`, `erx_rx_log`, `notification_log`, `payment_processing_audit` |
| **Layout / LBF** | 4 | `layout_options`, `layout_group_properties`, `list_options`, `lbf_data` |
| **FHIR / UUID** | 2 | `uuid_registry`, `uuid_mapping`. No `smart_*` tables in baseline. |
| **OAuth2** | 2 | `oauth_clients`, `oauth_trusted_user`. (OAuth2 access/refresh tokens are persisted via PHP-League storage; no dedicated `oauth2_access_tokens` table in baseline.) |
| **Modules** | 5 | `modules`, `modules_hooks_settings`, `modules_settings`, `module_configuration`, plus the 3 `module_acl_*` tables above |
| **Version tracking** | 1 | `version` — see §5 |

Note: some tables span multiple domains; counts are indicative, not disjoint. Total reconciles to 281 baseline tables.

---

## 5. PK Conventions — Top Core Tables

Extracted from `sql/database.sql` (line numbers noted):

| Table | Line | PK column definition | PK type |
|---|---:|---|---|
| `patient_data` | 8334 | `id bigint(20) NOT NULL auto_increment` (PK declared later in table body; also carries `uuid binary(16)`) | AI bigint + shadow UUID |
| `form_encounter` | 2022 | `PRIMARY KEY (id)` — `id bigint(20) auto_increment`, also `uuid binary(16)`, `pid`, `encounter` | AI bigint + shadow UUID |
| `forms` | 2460 | `PRIMARY KEY (id)` — `id bigint(20) auto_increment` | AI bigint |
| `billing` | 245 | `PRIMARY KEY (id)` — `id int(11) auto_increment` | AI int |
| `insurance_data` | 3306 | `PRIMARY KEY (id)` — `id bigint(20) auto_increment`, `uuid binary(16)` | AI bigint + shadow UUID |
| `insurance_companies` | 3279 | `PRIMARY KEY (id)` — `id int(11) NOT NULL default 0` (**not auto_increment**), `uuid binary(16)` | Manual int + shadow UUID |
| `lists` | 7671 | `PRIMARY KEY (id)` — `id bigint(20) auto_increment`, `uuid`, `pid`, `type` | AI bigint (polymorphic via `type`) |
| `users` | 9786 | `id bigint(20) auto_increment`, `uuid binary(16)`, `username varchar(255)` | AI bigint + shadow UUID + unique `username` |
| `prescriptions` | 8698 | `PRIMARY KEY (id)` — `id int(11) auto_increment`, `uuid binary(16)` | AI int + shadow UUID |
| `documents` | 1391 | `PRIMARY KEY (id)` — `id int(11) NOT NULL default 0` (**not auto_increment**), `uuid binary(16)` | Manual int + shadow UUID |

**Pattern:** Every core clinical/business table has an auto-increment integer surrogate PK (mostly `bigint`, some legacy `int`). A parallel `uuid binary(16)` column is added on tables that participate in FHIR/API surface, populated by `UuidRegistry` (see `src/Common/Uuid/UuidRegistry.php`, referenced from `sql_upgrade.php:70`). UUID is **not** the PK — it is a shadow identifier for external referencing, with reverse-lookup via `uuid_registry`.

**Composite PKs** exist on lookup / join tables: `list_options` (`list_id`,`option_id`), `layout_options` (`form_id`,`field_id`,`seq`), `lbf_data` (`form_id`,`field_id`), `modules` (`mod_id`,`mod_directory`).

**Anomaly:** `documents.id` and `insurance_companies.id` are declared `NOT NULL default '0'` without `auto_increment` — the application assigns IDs manually. `UNKNOWN — reason for this deviation; likely legacy artifact.`

---

## 6. Foreign-Key Reality

`Select-String -Pattern "^\s*FOREIGN KEY|^\s*CONSTRAINT" sql\database.sql` → **0 matches**.

Broader search `"FOREIGN KEY|CONSTRAINT"` → 5 matches, **all inside `COMMENT` strings** describing "Foreign key to X.id" (e.g., `sql/database.sql:15138-15156`, referencing `form_clinical_notes.id`, `documents.id`, `procedure_result.procedure_result_id`).

**There are literally zero enforced foreign-key constraints in the OpenEMR baseline schema.** Referential integrity is entirely application-managed. This is a critical fact for the SaaS layer:

- Adding a real `FOREIGN KEY` from `custom_*` tables into core tables is technically possible on InnoDB but breaks the platform assumption that any row can be deleted/reassigned without cascade behavior. Recommend soft references (bare integer columns with an index) matching the existing style.
- Any Doctrine ORM entity we introduce must NOT generate `ADD CONSTRAINT` DDL against core tables without an explicit product-owner decision.

---

## 7. Schema Upgrade Mechanism

### 7a. Legacy driver: `sql_upgrade.php` + `SQLUpgradeService`

- Entry point: `sql_upgrade.php` at repo root. Runs via web UI or CLI (`--from=VERSION`). Reads `sites/<site>/sqlconf.php`, boots `interface/globals.php`, then delegates to `OpenEMR\Services\Utils\SQLUpgradeService` (`src/Services/Utils/SQLUpgradeService.php:30`).
- Discovers upgrade files by scanning `sql/` for filenames matching `/^(\d+)_(\d+)_(\d+)-to-\d+_\d+_\d+_upgrade\.sql$/` (`sql_upgrade.php:90`), sorts them by starting version, and applies each in order using `SQLUpgradeService::upgradeFromSqlFile()` (`src/Services/Utils/SQLUpgradeService.php:223`).
- The service is a **line-oriented parser** for a set of OpenEMR-specific `#`-prefixed macros. Every upgrade file begins with a documentation header enumerating them (canonical copy: `sql/8_2_0-to-8_3_0_upgrade.sql:1-60`, implementation: `SQLUpgradeService.php:277-339`+).

Macro syntax (verbatim from `sql/8_2_0-to-8_3_0_upgrade.sql` header):

```
#IfNotTable <table>              -- skip block if table exists
#IfTable <table>                 -- skip block if table missing
#IfColumn <table> <col>          -- run only if table AND column exist
#IfMissingColumn <table> <col>   -- run only if table exists but column does not
#IfNotColumnType <table> <col> <type>
#IfNotColumnTypeDefault <table> <col> <type> <default>
#IfNotRow  <table> <col> <val>
#IfNotRow2D / #IfNotRow3D / #IfNotRow4D / #IfNotRow2Dx2
#IfRow / #IfRow2D / #IfRow3D
#IfDocumentNamingNeeded
#IfUpdateEditOptionsNeeded
#IfVitalsDatesNeeded
#IfCareTeamsV1MigrationNeeded
#EndIf                            -- terminates every block
```

Each upgrade file is designed to be **idempotent** — you can rerun it and the guards skip already-applied changes. This is essential context for the "custom_ namespace" rule (§10).

### 7b. `version` table

Structure (`sql/database.sql:12433-12441`):

```sql
CREATE TABLE version (
  v_major     int(11)     NOT NULL DEFAULT 0,
  v_minor     int(11)     NOT NULL DEFAULT 0,
  v_patch     int(11)     NOT NULL DEFAULT 0,
  v_realpatch int(11)     NOT NULL DEFAULT 0,
  v_tag       varchar(31) NOT NULL DEFAULT '',
  v_database  int(11)     NOT NULL DEFAULT 0,   -- 541 in this fork
  v_acl       int(11)     NOT NULL DEFAULT 0    -- 13 in this fork
) ENGINE=InnoDB;
```

Single-row table; `sql/database.sql:12447` seeds `(0,0,0,0,'',0,0)`. Each `_upgrade.sql` ends with an `UPDATE version SET v_database=<n>` bumping the counter. `sql/database.sql` header L4-6 explicitly warns "Keep v_database in sync with $v_database in version.php. CI will fail if they don't match."

### 7c. New driver: Doctrine Migrations (partially wired)

- `composer.json:55` → `doctrine/migrations: ^3.9`.
- Config lives in DI factories at `config/database.php:73-92`:
  - `migrations_paths` → `OpenEMR\Core\Migrations => db/Migrations`
  - `table_storage.table_name` → `migrations` (an unwritten new table, not present in `sql/database.sql`)
  - `custom_template` → `db/migration-template.php.tpl`
  - `DependencyFactory::fromEntityManager(...)` bound so migrations can subscribe to events.
- CLI wrapper: `./cli migrate`, `./cli migration:generate`, `./cli migrate prev` (`db/README.md:16-42`).
- Current migration files: **only one placeholder** — `db/Migrations/Version00000000000000.php`.
- **`db/README.md:10-12` explicitly warns:** *"The Doctrine Migrations system is NOT fully integrated into OpenEMR yet. Don't make database changes using this until #10708 is completed (at minimum)."*
- Table-creation helper for future migrations: `src/Core/Migrations/CreateTableTrait.php` (`use CreateTableTrait;` + `$this->createTable($table)` — sidesteps schema-diff introspection; see `db/Migrations/README.md`).

**Authoritative today:** the legacy `sql/<from>-to-<to>_upgrade.sql` chain via `SQLUpgradeService`. Doctrine Migrations is opt-in scaffolding. Any new schema PR must still land as an entry in the current `8_2_0-to-8_3_0_upgrade.sql` file (or the successor once opened) **until issue #10708 declares Doctrine authoritative.**

### 7d. Database connection factory & QueryUtils

Location: `src/Common/Database/` (paths):
- `src/Common/Database/ConnectionManager.php` — named DBAL `Connection` registry with lazy factories (`ConnectionType` enum keys). Class `OpenEMR\Common\Database\ConnectionManager`.
- `src/Common/Database/ConnectionType.php` — enum of connection kinds (e.g., `Main`).
- `src/Common/Database/QueryUtils.php` — canonical query helper (documented in `CLAUDE.md` as the sanctioned entry point for queries).
- `src/Common/Database/DbUtils.php` — misc DBAL helpers.
- `src/Common/Database/DatabaseQueryTrait.php` — trait for services needing query methods.
- `src/Common/Database/QueryPagination.php`, `src/Common/Database/SqlQueryException.php`, `src/Common/Database/TableTypes.php`.

Bound in `config/database.php:64-65`: `Connection::class => ConnectionManager::get(ConnectionType::Main)`. `CLAUDE.md` mandates: *"Do not instantiate database connections directly — use the centralized `DatabaseConnectionFactory`"* (naming aside, this is the manager above).

---

## 8. LBF (Layout-Based Forms)

Four tables cooperate to allow adding "custom fields" without new physical tables:

| Table | Purpose |
|---|---|
| `layout_options` (PK `form_id, field_id, seq`) | Field metadata: label, data type, list source, size, position, permissions. `form_id` identifies which layout (e.g., `DEM` = demographics, `HIS` = history, or a user-defined LBF id `LBF*`). |
| `layout_group_properties` | Field grouping / tab / section metadata for a `form_id`. |
| `lbf_data` (PK `form_id, field_id`) | The actual per-instance field values, EAV-style. `form_id` here is `forms.id` (encounter form instance), `field_id` matches `layout_options.field_id`, `field_value` is `LONGTEXT`. |
| `list_options` (PK `list_id, option_id`) | Enumerated option values referenced by `layout_options.list_id`. |

For a "core" layout like Demographics, `layout_options` defines which columns of `patient_data` (or additional columns added ad-hoc) map to displayed fields. For a fully custom form (LBF), all values land in `lbf_data` with no per-form table.

**Implication for the SaaS layer:** OpenEMR already offers an EAV extension mechanism inside the application. A significant amount of "custom per-tenant field" work can be modeled via **new `layout_options` rows + values in `lbf_data`** without introducing any DDL. New physical `custom_*` tables should be reserved for: (a) cross-encounter aggregate state, (b) integration/queue tables, (c) tenant/subscription/billing metadata that has no clinical mapping, or (d) performance-critical data where EAV would be too slow.

---

## 9. Core Tables the SaaS Layer Will Reference

| Table | PK column | PK type | Purpose | Referenced by |
|---|---|---|---|---|
| `patient_data` | `id` (+`pid` also unique via legacy semantics) | bigint AI + `uuid` shadow | The patient record. `pid` is the historic external identifier used across nearly every other clinical table. | `form_encounter.pid`, `forms.pid`, `billing.pid`, `insurance_data.pid`, `lists.pid`, `prescriptions.patient_id`, `documents` (via `categories_to_documents`), `openemr_postcalendar_events.pc_pid`, `audit_master.pid`, `immunizations.patient_id`, hundreds of others |
| `form_encounter` | `id` | bigint AI + `uuid` | One clinical encounter per row (`encounter` value); date, provider, facility, reason. | `forms.encounter`, `billing.encounter`, `ar_activity.encounter`, `lists` (indirectly), all `form_*.encounter` |
| `forms` | `id` | bigint AI | Dispatcher/registry of per-encounter form rows: `(pid, encounter, formdir, form_id, deleted)`. The `formdir` column names the physical `form_*` table and `form_id` is that table's PK. | Read by every encounter view; written by every form save |
| `billing` | `id` | int AI | Per-encounter line items (diagnoses + procedures). | `ar_activity.code_type/code`, `claims`, `x12_partners` output |
| `insurance_data` | `id` | bigint AI + `uuid` | Per-patient insurance coverage rows (primary/secondary/tertiary). | `billing`, `claims`, `x12_partners` |
| `insurance_companies` | `id` | int (manual, no AI) + `uuid` | Payer directory. | `insurance_data.provider`, `x12_partners` |
| `users` | `id` (also unique `username`, `uuid`) | bigint AI + `uuid` | Every principal: staff, providers, admins. Role via ACL, not a column. | `form_encounter.provider_id`, `billing.provider_id`, `log.user`, `api_log.user_id`, `audit_master.user_id`, ACL `gacl_aro.value` |
| `uuid_registry` | `uuid` | `binary(16)` (natural PK) | Reverse index UUID → (`table_name`, `table_id`) for FHIR/API. | Every API/FHIR service; `UuidRegistry` service |
| `list_options` | (`list_id`,`option_id`) | composite varchar | Enumerated dropdown values used across the UI. | `layout_options.list_id`, hundreds of Smarty/Twig `xl_list_label()` calls |
| `codes` | `id` | int AI | Local overlay of external code sets (ICD10, CPT4, SNOMED, etc.); `code_type` joins to `code_types.ct_id`. | `billing`, `lists.diagnosis`, `procedure_order_code`, `form_diagnosis` |

**Note on `pid`:** `patient_data.pid` is *not* the same as `patient_data.id` in general. `pid` is the historic external key (patient number visible to users, unique per install); `id` is the modern surrogate. The vast majority of clinical FK-by-convention references use `pid`, not `id`. This is a subtle trap for a SaaS layer — reference `pid` when interoperating with existing tables.

---

## 10. Custom-Schema Safety Rules

This is the load-bearing finding for Document 0's "custom_ namespace" candidate.

**The upgrade mechanism cannot touch a table it does not name.** Every DDL statement in every `sql/*_to_*_upgrade.sql` file names its target table literally (e.g., `ALTER TABLE patient_data ADD COLUMN ...`, `#IfMissingColumn form_encounter uuid`). The parser in `SQLUpgradeService::upgradeFromSqlFile()` (`src/Services/Utils/SQLUpgradeService.php:223-339+`) is a straight-line executor with these decision paths:

1. Line starts with `--` → comment, skip.
2. Line matches `#IfNotTable X` / `#IfTable X` / `#IfColumn X Y` / etc. → set `$skipping` flag by querying `information_schema` for that literal name (`SqlUpgradeService.php:277+`).
3. Otherwise, if not `$skipping`, append line to `$query` and execute at `;` boundary.

There is **no wildcard, no table enumeration, no schema-diff, no "drop unknown tables" branch** anywhere in the legacy path. Grepping the file confirms: `Select-String "DROP TABLE|dropTable"` in `SQLUpgradeService.php` returns 0 hits. `information_schema` is only queried by name via `tableExists($tblname)` (`SqlUpgradeService.php:887`) — a `SHOW TABLES LIKE '<literal>'` — and `columnExists()` with a specific column.

Doctrine Migrations (once activated per §7c) uses `CreateTableTrait` deliberately to **avoid** schema introspection: *"The standard Doctrine approach requires schema introspection to diff current vs desired state. This adds overhead and complexity, and will cause migrations to get steadily slower (minutes vs milliseconds) as more exist"* (`db/Migrations/README.md:29-32`). Consequence: even under the future Doctrine path, migrations only touch tables their PHP code names literally. Tables not enumerated in any migration are invisible.

**Therefore, the following is safe by construction:**

- Any table whose name does not collide with a name that appears in `sql/database.sql` or any `sql/*_upgrade.sql` file (past OR future upstream) will survive `sql_upgrade.php` and `./cli migrate` untouched.
- A `custom_*` prefix (or any prefix upstream does not use) is a defensible naming convention because upstream OpenEMR does not now, and by review of 36 upgrade files has never, used a `custom_` prefix on its own tables. Grep of `sql/*.sql` for `CREATE TABLE.*custom_` returns one hit: `customlists` (`sql/database.sql:12452`) — a legacy singular-name table, not prefix-namespaced. The `custom_` **prefixed** namespace is genuinely unclaimed by upstream today.
- Backup/restore, `example_patient_data.sql` seed, and the CI matrix all operate on `information_schema` enumerations that include user tables — so `custom_*` tables will be dumped and restored correctly by standard `mysqldump`.

**Residual risks the SaaS layer still owns:**

- Upstream is free to introduce `custom_*` names in a future upgrade. Detection: watch for `CREATE TABLE.*custom_` diffs on rebase. Mitigation: pin the prefix to `custom_saas_` or a tenant-specific prefix at a higher level of paranoia.
- The `modules` and `module_configuration` tables (§4) are the sanctioned extensibility path if the goal is "shipped module." Direct `custom_*` tables bypass module registration — that is a design decision, not a technical blocker.
- Adding real `FOREIGN KEY` constraints from `custom_*` into core tables would change platform behavior (§6). Recommend soft references + indexed columns.

`UNKNOWN — no formal upstream policy statement guarantees the "custom_" prefix will remain reserved for downstream forks; this is inferred from 36 upgrade files with zero counter-examples of the prefixed form.`

---

## Cross-References

- Prior: `docs/00-discovery/00-environment.md`, `docs/00-discovery/01-repo-inventory.md`, `docs/00-discovery/03-directory-map.md`.
- Next likely: an entities/services catalog documenting `src/Services/*Service.php` and `src/Entities/`, and a FHIR-surface map keyed to `uuid_registry`.
