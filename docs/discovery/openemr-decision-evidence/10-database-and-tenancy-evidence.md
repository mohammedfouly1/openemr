# 10 — Database & Tenancy Evidence (Q57, Q58, Q68 + schema-tenancy notes)

_Fork: OpenEMR 8.3.0-dev @ `631f2b38`. Mode: static, READ-ONLY. Auditor: opencode._

**Scope of this file:** §10 questions Q57 (documents.id & insurance_companies.id application-assigned IDs), Q68 (`custom_` prefix reservation), plus a brief schema-level tenancy setup section that will be augmented by the §16 control-plane agent.

**Companion snippets** (call graphs, per-table detail):

- `evidence/snippets/q57-documents-id-trace.md`
- `evidence/snippets/q57-insurance-companies-id-trace.md`
- `evidence/snippets/q68-custom-prefix-evidence.md`

---

## 1. Executive summary (5 lines)

1. **Q57 / `documents.id`** — Non-`AUTO_INCREMENT` is deliberate-by-inertia; every writer allocates an id via `QueryUtils::generateId()` → global `sequences` table (`UPDATE sequences SET id=LAST_INSERT_ID(id+1)`, atomic on InnoDB, race-caveated in source at `src/BC/Database.php:165`). Conversion to AUTO_INCREMENT is technically safe (zero `INSERT ... VALUES (0, ...)` sites) but requires no upstream change — recommend leaving it alone.
2. **Q57 / `insurance_companies.id`** — Deliberate and load-bearing; documented at `src/Services/InsuranceCompanyService.php:433-436` as sharing an id-space with `pharmacies` through the `addresses` satellite table. Conversion to AUTO_INCREMENT in isolation would silently corrupt address lookups. **Do not touch.**
3. **Q58 (schema-level tenancy)** — Answered in §4 below: OpenEMR is site-partitioned at the filesystem+database layer (`sites/<site>/sqlconf.php` per tenant), NOT row-partitioned. `documents` and `insurance_companies` have no `tenant_id` / `site_id` column — each site owns a complete, isolated copy of every core table. This is fundamental context for the §16 control-plane work.
4. **Q68 (`custom_` prefix)** — **Not formally reserved.** Zero baseline tables use the prefix, zero upgrade files touch it, zero module SQL files use it. Prior audit's inference is verified but remains inferential — no upstream policy statement exists. Recommend `saas_` prefix instead (semantically distinct from `interface/modules/custom_modules/` directory name).
5. **Load-bearing artifact for new SaaS tables:** `bigint AUTO_INCREMENT PRIMARY KEY` + shadow `uuid binary(16) NOT NULL UNIQUE`, per-table (private) sequences, no shared id-spaces, no enforced FKs into core tables (zero FKs exist upstream — `Select-String 'FOREIGN KEY' sql/database.sql` outside comments = 0).

---

## 2. Q57 — Direct answers

### 2a. `documents.id`

| Sub-question | Answer | Primary evidence |
|---|---|---|
| Deliberate? | Yes, by inertia. Unchanged across 12 upgrade files touching this table. | `sql/database.sql:1391-1432`; `evidence/raw/q57-upgrade-documents-broad.txt` (27 ALTERs, none on `id`) |
| Who assigns the id? | `ORDataObject::persist()` for `Document::persist()`; explicit `QueryUtils::generateId()` for procedural writers. | `src/Common/ORDataObject/ORDataObject.php:80-84`; `interface/fax/fax_dispatch.php:164`; `library/classes/Document.class.php:855-864` |
| Safe to convert to AUTO_INCREMENT? | Yes — zero `INSERT ... VALUES (0, ...)` call sites; MySQL accepts explicit non-zero ids. | Grep results in snippet §3 |
| What breaks? | Nothing at write path. The `sequences` counter would remain the id source until app refactored. | See snippet §6 table |
| Key strategy for new SaaS tables | `bigint AUTO_INCREMENT PRIMARY KEY` + `uuid binary(16) NOT NULL UNIQUE` shadow, per-table private sequence. | Mirrors modern core convention (`patient_data`, `form_encounter`, `insurance_data`, `users`) per prior audit §5 |

Full trace: `evidence/snippets/q57-documents-id-trace.md`.

### 2b. `insurance_companies.id`

| Sub-question | Answer | Primary evidence |
|---|---|---|
| Deliberate? | **Yes, explicitly documented.** | `src/Services/InsuranceCompanyService.php:433-436` in-source comment |
| Who assigns the id? | `InsuranceCompanyService::insert()` at line 438 and `ORDataObject::persist()` via `InsuranceCompany::persist()`. | `src/Services/InsuranceCompanyService.php:438`; `library/classes/InsuranceCompany.class.php:338-375` (transaction-wrapped) |
| Safe to convert to AUTO_INCREMENT in isolation? | **No.** Shared id-space with `pharmacies` via `addresses.foreign_id` — bare `int foreign_id` column with no type discriminator (see `Address::factory_address()` at `InsuranceCompany.class.php:321` and `Pharmacy.class.php:201`). | Snippet §6 |
| What breaks? | Silent data corruption in address lookups once `pharmacies.id` and `insurance_companies.id` numeric ranges overlap. | Same |
| Key strategy for new SaaS tables | Same as documents recommendation, **plus explicit rule: never share id-space across tables. Use `(source_table, source_id)` tuple columns for cross-table references, not a bare shared foreign_id.** | Snippet §7 |

Full trace: `evidence/snippets/q57-insurance-companies-id-trace.md`.

---

## 3. Q68 — Direct answer (3 lines)

- **Reserved?** No — zero baseline tables, zero upgrade-file operations, zero code references, zero documentation entries use the `custom_` prefix (Channels 1-5 in `evidence/snippets/q68-custom-prefix-evidence.md`). Reservation is de facto, not de jure.
- **Actual convention** used by shipped modules: **`<vendor-slug>_<domain>_`** (weno_*, comlink_telehealth_*) or **bare descriptive names** (user_dashboard_context, dashboard_context_*). Neither uses `custom_`. The word "custom" appears in the *directory* `interface/modules/custom_modules/` to mean "third-party module directory" — a semantic collision with any hypothetical `custom_` table prefix.
- **Recommendation:** adopt **`saas_<domain>_`** as the SaaS-layer table prefix (verify each rebase: `git diff v8_2_0..upstream/master --stat -- sql/ | grep 'saas_'` → must remain empty). Rationale, alternatives, and residual risks in the snippet.

---

## 4. Schema-level tenancy notes (feeds §16)

**This section documents only what the schema and static configuration reveal about tenancy. The §16 control-plane agent will append a control-plane analysis in a separate section.**

OpenEMR is **not** a row-tenant application at the schema layer:

- No `tenant_id`, `site_id`, or equivalent discriminator column exists on `documents`, `insurance_companies`, `patient_data`, `users`, or any core table (verified: `Select-String -Path sql/database.sql -Pattern 'tenant_id|site_id' -CaseSensitive:$false` returns 0 hits in table definitions; the string appears only in FHIR JSON payloads).
- Tenant separation is achieved **at the site level**: each tenant is a **separate physical database** with its own `sites/<site>/sqlconf.php` (documented convention throughout `sql_upgrade.php:1-90`, `interface/globals.php`, and the installer at `library/classes/Installer.class.php:603+`). Every core table therefore exists **once per tenant**, and the entire audit above (Q57 answers, sequences-table behavior, `uuid_registry` contents) applies **per site** — the `sequences` counter, the id-space collisions, the UUID registry are all site-local.
- Consequence for `documents` and `insurance_companies` specifically: **the id-collision risk analyzed in Q57 is a per-site concern.** Two sites can (and do) have `documents.id = 1` referring to entirely different records. No global lookup path exists.
- Consequence for a SaaS control plane: a control-plane database that spans tenants (e.g., a `saas_tenancy_subscription` table) **must not** use raw site-local ids as foreign keys — it needs `(site_name, entity_id)` tuples or globally-scoped UUIDs. This is a decision point the §16 agent should own.
- The `interface/modules/custom_modules/oe-module-dashboard-context/sql/install.sql` module is the closest existing precedent for a cross-user (but still per-site) admin table with its own naming — worth studying if a similar shape is needed at the tenancy layer.

Reminder: the fork carries **zero enforced FK constraints** (prior audit §6 verified; re-verified this pass: `Select-String 'FOREIGN KEY' sql/database.sql` → 4 hits, all inside `COMMENT` strings). Any tenancy-layer schema we ship should follow this convention (bare integer + index) rather than introducing `ADD CONSTRAINT` DDL against core tables.

---

## 5. UNKNOWNs

- **Q57 concurrency proof** — the `sequences`-table atomicity claim (`UPDATE ... SET id=LAST_INSERT_ID(id+1)` is an atomic InnoDB row-level UPDATE) is a MySQL-documented behavior, not a proven property of this codebase. No test in `tests/` exercises parallel writers. If we depend on strict monotonicity under load, we need our own integration test — evidence-blocked without a running DB.
- **Q57 `documents.id` historical reason** — the "why never fixed" is inferential. The InsuranceCompany comment documents its own reason (addresses id-space sharing); no analogous comment exists for documents. Possibly the same shared-satellite-table motivation from an older schema era, but not verifiable from source alone.
- **Q68 upstream policy** — no CI check or documentation exists that would prevent a future upstream commit from claiming `saas_` (or any other prefix we adopt). This is a **procedural risk** owned by our rebase workflow, not a technical one.
- **Q68 submodule coverage** — `ci/inferno/onc-certification-g10-test-kit` and `ci/inferno/inferno-files` were not scanned (submodules not initialized per `docs/discovery/openemr-decision-evidence/02-repository-baseline.md:27`). These are ONC test kits, not schema carriers, so the omission is low-risk but formally unverified.
- **Upstream drift check on the `saas_` prefix** — should be re-run at every rebase; not automatable without a repo-side pre-rebase hook.

---

## 6. Cross-references

- Prior audit: `docs/00-discovery/04-database-schema.md` §5 (PK conventions), §6 (zero FKs), §10 (custom-namespace inference).
- Baseline: `docs/discovery/openemr-decision-evidence/02-repository-baseline.md`.
- Companion snippets: `evidence/snippets/q57-documents-id-trace.md`, `evidence/snippets/q57-insurance-companies-id-trace.md`, `evidence/snippets/q68-custom-prefix-evidence.md`.
- Raw grep outputs: `evidence/raw/q57-alter-documents.txt`, `q57-alter-insurance_companies.txt`, `q57-upgrade-documents-broad.txt`, `q57-upgrade-insurance-companies-all.txt`, `q57-generate_id-calls.txt`, `q57-consumer-columns.txt`, `q68-baseline-custom.txt`, `q68-upgrade-custom.txt`.
- Command log: `22-command-log.txt`.
- **Reserved for §16 agent**: a separate section titled "§16 Control-Plane Tenancy" will be appended to this file (or as a sibling `16-control-plane-tenancy.md`) — the row-tenant vs database-per-tenant decision, the `saas_tenancy_*` table shapes, and the credential-scoping strategy are all §16 scope, not §10 scope.
