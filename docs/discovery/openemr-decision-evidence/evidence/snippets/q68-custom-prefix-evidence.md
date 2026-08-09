# Q68 — `custom_` table-name prefix reservation: evidence

_Fork: OpenEMR 8.3.0-dev @ `631f2b38`. Mode: static, READ-ONLY._

## Question

Prior audit (`docs/00-discovery/04-database-schema.md:271`) marked as UNKNOWN whether the `custom_` prefix is formally reserved for downstream forks/customization, inferring safety from "36 upgrade files with zero prefixed counter-examples." This snippet verifies (or refutes) that inference across all six evidence channels.

## Channel 1 — Baseline schema

`Select-String -Path sql/database.sql -Pattern '^CREATE TABLE .custom' -CaseSensitive:$false`:

```
12456: CREATE TABLE `customlists` (
```

**One hit. Zero tables use the `custom_` prefix (with underscore).** The single match is `customlists` — a **singular, unprefixed** name from the LBF system (a table where each row is a user-defined dropdown list). It is **not** a namespaced prefix, and it predates any notion of a downstream-fork convention (present since at least schema v< 5.0.0).

Raw output: `evidence/raw/q68-baseline-custom.txt`.

## Channel 2 — Upgrade files (all 36)

`Select-String -Path sql/*_upgrade.sql -Pattern '(CREATE|ALTER|DROP) TABLE\s+.?custom_' -CaseSensitive:$false`:

**0 hits.** Not a single upgrade file across the entire 2.6.0 → 8.3.0 history has ever created, altered, or dropped a table whose name begins with `custom_`.

Raw output: `evidence/raw/q68-upgrade-custom.txt` (empty file).

## Channel 3 — Application code references

`grep -R 'FROM\s+custom_|INTO\s+custom_|UPDATE\s+custom_'` across `src/`, `library/`, `interface/`:

**0 hits.** No PHP code queries any table whose name starts with `custom_`. Confirms Channel 1 & 2 — upstream carries zero `custom_`-prefixed tables and references none.

## Channel 4 — Shipped module SQL conventions

Enumeration of `interface/modules/custom_modules/oe-module-*/sql/*.sql`:

| Module | Table naming convention |
|---|---|
| `oe-module-weno` | `weno_pharmacy`, `weno_assigned_pharmacy`, `weno_download_log` — **vendor-slug prefix** (`weno_`) |
| `oe-module-comlink-telehealth` | `comlink_telehealth_auth`, `comlink_telehealth_person_settings`, `comlink_telehealth_appointment_session` — **vendor+domain slug prefix** (`comlink_telehealth_`) |
| `oe-module-dashboard-context` | `user_dashboard_context`, `user_dashboard_context_config`, `dashboard_context_definitions`, `dashboard_context_assignments`, `dashboard_context_role_defaults`, `dashboard_context_audit_log` — **bare descriptive names**, no vendor prefix |
| `oe-module-faxsms` | (sql files present but they `ALTER TABLE` core tables; no new `CREATE TABLE`) |

**Key observation:** Note that the directory `interface/modules/custom_modules/` uses the word "custom" as a directory-level convention meaning "third-party / non-Zend module." **The word "custom" is a directory naming convention, not a table naming convention.** No shipped module uses `custom_` as a table prefix.

## Channel 5 — Documentation search

`Get-ChildItem D:\OpenEmr\Documentation -Recurse -Include *.md,*.txt` and grep for `'reserved prefix'|'custom.*prefix'|'table naming'|'custom naming'` (case-insensitive):

**0 matches.**

`Get-ChildItem D:\OpenEmr\contrib -Recurse -Include README*,*.md` and grep for `'custom_'|'reserved prefix'`:

**0 matches.**

**No formal in-tree documentation reserves the `custom_` prefix or specifies any table naming policy for downstream forks.**

## Channel 6 — Upgrade parser behavior (from prior audit §10)

Even absent a formal policy, `SQLUpgradeService::upgradeFromSqlFile()` (`src/Services/Utils/SQLUpgradeService.php:223-339+`) is a straight-line executor that only touches tables it names literally. There is no wildcard, no drop-unknown branch, no schema-diff step. **A table whose name upstream never mentions cannot be touched by upstream migrations by construction.** This is the load-bearing safety guarantee, and it applies to any prefix (or any name) upstream does not use — not just `custom_`.

## Direct answers

1. **Is `custom_` a formally reserved prefix?**
   **No.** There is no upstream policy statement, no CI check, no documentation, and no code enforcing the reservation. It is **de facto unclaimed** — upstream has never used it in 36 upgrade files, 281 baseline tables, and 8 module SQL files — but this is inferential safety, not a contract.

2. **What is the actual naming convention used by existing modules?**
   Two patterns coexist: **(a) `<vendor-slug>_<domain>_` prefixes** (weno, comlink_telehealth) and **(b) bare descriptive names** (user_dashboard_context, dashboard_context_*). There is no prescriptive convention; module authors choose. Neither pattern uses `custom_`.

3. **Recommendation for our SaaS modules (naming pattern).**
   Adopt **`saas_<domain>_` as the tenant-agnostic prefix** (e.g., `saas_billing_invoice`, `saas_tenancy_subscription`). Reasoning:
   - `custom_` is technically unclaimed but *semantically overloaded* — `interface/modules/custom_modules/` uses the same word to mean "third-party module directory," creating cognitive collision.
   - `saas_` is (a) unused by upstream (verify: `Select-String '^CREATE TABLE .saas' sql/*.sql` → 0 hits), (b) semantically distinctive from any upstream extensibility mechanism, and (c) short enough to keep table names readable within MySQL's 64-char identifier limit.
   - For any table that is genuinely tenant-scoped (per-tenant rows in a shared table), keep the `saas_` prefix and add a `tenant_id bigint NOT NULL` column with an index. Do not use per-tenant table suffixes (e.g., `saas_billing_tenant42`) — that path leads to the schema explosion problem that killed multi-tenant Rails apps a decade ago.
   - Cross-check the chosen prefix on each rebase from upstream: `git diff v8_2_0..upstream/master --stat -- sql/ | Select-String '^CREATE TABLE .saas'` (part of the routine drift-audit checklist).

## Residual UNKNOWNs

- No CI mechanism prevents an upstream commit from claiming `saas_` (or any other prefix) in the future. Mitigation is procedural (rebase-time diff review), not technical.
- No verification against **submodules** (`ci/inferno/*`) was attempted — they carry test/certification code, not schema migrations, but a full audit would include them.
