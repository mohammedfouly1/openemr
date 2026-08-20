# 15 — Upgrade & Patch Strategy

_Read-only synthesis. Draws exclusively from prior Phase 0–14 reports under
`docs/00-discovery/`. Fork HEAD `631f2b38` = OpenEMR **8.3.0-dev**, `$v_database=541`,
`$v_acl=13` (`01-repo-inventory.md:28-29`). No `upstream` remote configured
(`01-repo-inventory.md:8`)._

**Phase 5 (`06-api-surface.md`) did not yet exist** in `docs/00-discovery/` at
time of writing. All REST/FHIR facts below cite Phase 2 (`03-directory-map.md`),
Phase 6 (`07-modules-and-extensibility.md`), and Phase 7
(`08-billing-claims-insurance.md §4`) which re-derived the surface from source.
**`06-api-surface.md` has since been added and is now present in this directory** —
the "re-integrate" instruction below and the FHIR paragraph in §6 predate it and
should be read alongside that file rather than as still-pending work.

---

## 1. Upgrade Mechanisms Restated

Four distinct upgrade paths coexist. Only the first two are authoritative today.

| # | Mechanism | Authoritative? | Version stamp | Driver | Cite |
|--:|---|---|---|---|---|
| 1 | **`sql/*_upgrade.sql` chain** via `SQLUpgradeService` | ✅ Yes | `version.v_database` = 541 | `sql_upgrade.php` at repo root; scans `sql/` for `<from>-to-<to>_upgrade.sql`, sorts, applies each; parser at `src/Services/Utils/SQLUpgradeService.php:223-339+` | `04-database-schema.md §7a`, §7b |
| 2 | **`acl_upgrade.php` chain** | ✅ Yes | `gacl_phpgacl.v_acl` = 13 | `acl_upgrade.php` (846 lines) at repo root; `if ($acl_version < $upgrade_acl) { … bump }` template | `05-auth-and-acl.md §5 (v13); §5 "ACL versioning"` (lines 364-373) |
| 3 | **Per-module SQL** via `AbstractModuleActionListener::install_sql` / `upgrade_sql` hooks | ✅ Yes (per-module) | `modules.sql_version`, `modules.acl_version` columns | `src/Core/AbstractModuleActionListener.php:123-136`; invoked by Module Manager UI at `interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php:267-311` | `07-modules-and-extensibility.md §1.3`, `04-database-schema.md §4 (modules table)` |
| 4 | Doctrine Migrations under `db/Migrations/` | ❌ **Scaffolded but NOT yet authoritative** | Would use `migrations` table (unwritten) | `db/README.md:10-12`: _"NOT fully integrated into OpenEMR yet. Don't make database changes using this until #10708 is completed"_ | `04-database-schema.md §7c` |

### Upgrade-macro vocabulary (from `sql/8_2_0-to-8_3_0_upgrade.sql:1-60`, cited in `04-database-schema.md §7a`)

```
#IfNotTable <t>               #IfTable <t>
#IfColumn <t> <c>             #IfMissingColumn <t> <c>
#IfNotColumnType <t> <c> <ty> #IfNotColumnTypeDefault <t> <c> <ty> <dflt>
#IfNotRow / #IfNotRow2D / #IfNotRow3D / #IfNotRow4D / #IfNotRow2Dx2
#IfRow / #IfRow2D / #IfRow3D
#IfDocumentNamingNeeded  #IfUpdateEditOptionsNeeded  #IfVitalsDatesNeeded
#IfCareTeamsV1MigrationNeeded
#EndIf
```

Every upgrade file is written idempotent — guards use `information_schema`
lookups by literal name and skip already-applied changes
(`04-database-schema.md §7a`, `SQLUpgradeService.php:277-339+`,
`tableExists()` at `:887`).

---

## 2. What Keeps the Fork Upgradeable — Sanctioned Practices

| Practice | Why upgrade-safe | Cite (report + file:line) |
|---|---|---|
| Custom modules under `interface/modules/custom_modules/oe-module-<ours>-<name>/` | Upstream never touches an `oe-module-<vendor-slug>-*` dir it doesn't ship; boot loader reads `modules` DB table not the filesystem (`ModulesApplication.php:132-164`); Composer installer plugin filters on `"type": "openemr-module"` and always drops packages here | `03-directory-map.md §2.1` (rows 5, `03-directory-map.md:24`, `157`); `07-modules-and-extensibility.md §1.2, §3` |
| Symfony EventDispatcher subscribers registered from a module's `openemr.bootstrap.php` | Bootstrap file is `include`d with `$eventDispatcher` in scope; subscribing to existing events does not touch any core file (`ModulesApplication.php:179-192`); 79 event classes across 22 subject areas cover most extension points | `07-modules-and-extensibility.md §2.3, §4` (event catalog), `03-directory-map.md §2.3` |
| REST/FHIR endpoint extension via `RestApiCreateEvent::EVENT_HANDLE` (subscriber calls `addToRouteMap()` / `addToFHIRRouteMap()` / `addToPortalRouteMap()`) | Route finders dispatch the event at request time; upstream `apis/routes/*` files never change | `07-modules-and-extensibility.md §4.1` (RestApiCreateEvent row) & `§7.1` (row 3); `03-directory-map.md §2.7`; `src/Events/RestApiExtend/RestApiCreateEvent.php:50-71`; `src/RestControllers/Finder/StandardRouteFinder.php:37`, `FhirRouteFinder.php:31`, `PortalRouteFinder.php:33` |
| Menu additions via `MenuEvent::MENU_UPDATE` subscriber (main menu) or `PatientMenuEvent` | `MainMenuRole::dispatch()` mutates the parsed menu array in-memory; upstream `interface/main/tabs/menu/menus/*.json` untouched | `09-frontend-ui.md §7`; `src/Menu/MenuEvent.php:17,28,34`; `src/Menu/MainMenuRole.php:67,72` |
| Twig template additions via `TwigContainer::addPath()` from module bootstrap | Loader is an append-list; module-local `templates/` dir is picked up without editing upstream template files | `07-modules-and-extensibility.md §2.7` (last paragraph) & `§7.1` row 5; `src/Common/Twig/TwigContainer.php:55,59`. **Caveat:** no `@namespace` convention exists — order-dependent; verify no upstream template name collides. Flagged UNKNOWN in `07-modules-and-extensibility.md §7 UNKNOWNs`. |
| `custom_*` DB table prefix + per-module `sql/install.sql` and `sql/upgrade/*.sql` | Upgrade parser only touches literally-named tables (see §4 below); `custom_` prefix is unclaimed by upstream (36 upgrade files reviewed, single hit `customlists` is a non-prefixed legacy singular name) | `04-database-schema.md §10`; `07-modules-and-extensibility.md §7.2` row "New DB table" |
| Per-site `sites/<tenant>/` overrides — `sqlconf.php`, `config.php`, `LBF/`, `documents/`, letterhead, `statement.inc.php`, `referral_template.html`, `faxcover.txt`, custom logos, and `sites/<tenant>/documents/custom_menus/*.json` | Upstream ships only the `sites/default/` skeleton; anything under a non-`default` site dir is user data by design; site-selection resolves to `OE_SITE_DIR = OE_SITES_BASE . "/" . site_id` | `03-directory-map.md §2.5`; `10-multisite-multitenant.md §1, §5, §6` (~40 `OE_SITE_DIR` call sites); `09-frontend-ui.md §1` — custom menus loaded from `OE_SITE_DIR/documents/custom_menus/<file>.json` at `src/Menu/MainMenuRole.php:53-58` |
| Composer-installed OpenEMR modules via `openemr/oe-module-installer-plugin` (allow-plugin) | Plugin filters on `"type": "openemr-module"`; drops package under `interface/modules/custom_modules/`; `.gitignore:13`/`phpstan.neon.dist:17`/`phpcs.xml.dist:68`/`rector.php:40`/`codecov.yml:3` all exclude this path from analysis — upstream tooling expects it to be Composer-populated | `03-directory-map.md §2.6`; `07-modules-and-extensibility.md §3`; `composer.json:201-208` |
| New encounter form under `interface/forms/<newsubdir>/` (5-file skeleton: `info.txt`, `new.php`, `save.php`, `view.php`, `report.php`, `print.php`, `table.sql`) | Adding a **new** subdirectory does not touch shipped ones; upstream owns the ~45 existing subdirs | `03-directory-map.md §2.2`; `07-modules-and-extensibility.md §7.1` row "New encounter form" |
| Kept-in-sync fork tracking upstream via `git rebase` / `git merge` — **contingent on adding an `upstream` remote first** | Phase 0 established no `upstream` remote is configured; the recommendation to add one and measure drift is captured in §5 below | `01-repo-inventory.md:8, 138-144` |

### Notes on `custom/` (evaluated honestly)

`custom/` at repo root is **NOT** an extension surface, despite its name.
20 upstream-shipped scripts already track there (`BillingExport.csv.php`,
`chart_tracker.php`, `code_types.inc.php`, `export_qrda_xml.php`,
`qrda_category1.inc.php`, `download_qrda.php`, plus 14 others). Any addition
or edit conflicts with upstream on every rebase (`03-directory-map.md:40`,
`§3` last-two rows; `07-modules-and-extensibility.md §7.1` row "Adding files
under `custom/`").

---

## 3. What BREAKS Upgrades — Anti-Practices

| Anti-practice | Failure mode | Cite |
|---|---|---|
| Editing any file under `src/`, `library/`, `interface/`, `templates/`, `sql/`, `apis/`, `oauth2/`, `gacl/`, `portal/`, `ccdaservice/`, `public/`, `controllers/`, `config/`, `bin/`, `sphere/`, `meta/`, `ccr/`, `_rest_routes.inc.php` | Every rebase yields a merge conflict on those files; upstream author/copyright headers show upstream owns them; churn is continuous | `03-directory-map.md §3` last row; `07-modules-and-extensibility.md §7.1` last row |
| Renaming or dropping any upstream table | `SQLUpgradeService` will `#IfMissingColumn`/`#IfTable` blocks silently skip changes, then a later `INSERT`/`ALTER` on the renamed table fatals; state diverges permanently | `04-database-schema.md §7a, §10` (macro parser semantics) |
| Adding columns to upstream tables without an idempotent `#IfMissingColumn` guard applied in an upstream-compatible way | Next upstream upgrade file will `#IfMissingColumn <table> <same_col>` and duplicate-add or skip, leaving semantics unclear. **Safer pattern:** put custom columns on a `custom_<table>_ext` side table joined by PK | `04-database-schema.md §7a` (macro list), `§10` (unclaimed prefix argument) |
| Changing the `modules` schema, or `users`/`groups`/`gacl_*` schemas directly | `modules` is core; `users` FK'd from ~everywhere (`04-database-schema.md §9` `users` row: `form_encounter.provider_id`, `billing.provider_id`, `log.user`, `api_log.user_id`, `audit_master.user_id`, `gacl_aro.value`); 24 `gacl_*` tables are managed by `acl_upgrade.php` | `04-database-schema.md §4 (Modules, ACL — legacy phpGACL), §9`; `05-auth-and-acl.md §5` |
| Editing `interface/main/tabs/menu/menus/*.json` (upstream owns them) | Every rebase conflict; upstream ships menu changes with every minor release | `09-frontend-ui.md §1` (menu JSON files table); safe alternative: `sites/<tenant>/documents/custom_menus/*.json` overrides (`MainMenuRole.php:53-58`) |
| Editing `composer.json` to remove upstream deps | Breaks feature-flags — many code paths conditionally use packages (e.g. `openemr/mustache`, `google/apiclient` for Google Sign-In, `yubico/u2flib-server` for U2F MFA, `moneyphp/money` for Rainforest & FHIR Money) | `02-tech-stack.md §5.3, §9`; `05-auth-and-acl.md §2, §9`; `13-i18n-localization.md §6` |
| Bumping `$v_database` (in `version.php` + `sql/*_upgrade.sql`) or `$v_acl` (in `acl_upgrade.php`) for downstream-only reasons | Upstream's next release bumps the same counter and both sides collide; DB will be in "already upgraded to 542" state that upstream's 541→542 file has not actually executed. Header comment on `sql/database.sql` L4-6 explicitly warns "Keep v_database in sync with $v_database in version.php. CI will fail if they don't match." | `04-database-schema.md §5, §7b`; `05-auth-and-acl.md §5` UNKNOWN #4 |
| Direct edits to `sites/default/` | Upstream ships `sites/default/` as an **example / template** that new sites are copied from; edits leak into every future `setup.php` clone | `10-multisite-multitenant.md §1, §4` (Installer clones `sites/<source>/` recursively); `03-directory-map.md §2.5` |
| Fork-specific `sql/*_upgrade.sql` files | Upstream will not accept them; `SQLUpgradeService` sorts alphabetically by from-version so name collisions with upstream files corrupt the chain; the correct path is per-module `sql/install.sql` + `sql/upgrade/*.sql` under an `oe-module-*` dir | `04-database-schema.md §7a` (discovery regex + sort); `07-modules-and-extensibility.md §7.2` row "New DB table" |
| Adding real `FOREIGN KEY` constraints from `custom_*` into core tables | OpenEMR baseline has **zero enforced FKs** (`04-database-schema.md §6`); adding cascade behavior changes platform semantics — soft references (bare integer + index) match the existing style | `04-database-schema.md §6, §10` |
| Editing `interface/forms/<existing>/` (any shipped form) | Upstream owns the ~45 shipped subdirs; new subdirs only | `03-directory-map.md §2.2` |

---

## 4. How the Upgrade Scripts Treat Unknown Tables / Columns

This is the load-bearing safety guarantee for downstream `custom_*` / `mod_*`
namespaces.

**Cited findings from `04-database-schema.md §10`:**

> "Every DDL statement in every `sql/*_to_*_upgrade.sql` file names its target
> table literally (e.g., `ALTER TABLE patient_data ADD COLUMN ...`,
> `#IfMissingColumn form_encounter uuid`). The parser in
> `SQLUpgradeService::upgradeFromSqlFile()`
> (`src/Services/Utils/SQLUpgradeService.php:223-339+`) is a straight-line
> executor with these decision paths:
>
> 1. Line starts with `--` → comment, skip.
> 2. Line matches `#IfNotTable X` / `#IfTable X` / `#IfColumn X Y` / etc. → set
>    `$skipping` flag by querying `information_schema` for that literal name
>    (`SqlUpgradeService.php:277+`).
> 3. Otherwise, if not `$skipping`, append line to `$query` and execute at `;`
>    boundary.
>
> There is **no wildcard, no table enumeration, no schema-diff, no 'drop
> unknown tables' branch** anywhere in the legacy path. Grepping the file
> confirms: `Select-String 'DROP TABLE|dropTable'` in `SQLUpgradeService.php`
> returns 0 hits. `information_schema` is only queried by name via
> `tableExists($tblname)` (`SqlUpgradeService.php:887`) — a
> `SHOW TABLES LIKE '<literal>'` — and `columnExists()` with a specific column."

**Confirmed guarantee (both current and future paths):**

- Legacy `SQLUpgradeService`: **unknown tables are never dropped, only
  literally-named tables are touched.**
- Doctrine Migrations (once activated, §7c of Phase 3): `CreateTableTrait`
  is deliberately used to **avoid** schema introspection — see
  `db/Migrations/README.md:29-32` cited in `04-database-schema.md §10`:
  _"The standard Doctrine approach requires schema introspection to diff
  current vs desired state. This adds overhead and complexity, and will cause
  migrations to get steadily slower (minutes vs milliseconds) as more exist."_
  Consequence: even under the future Doctrine path, migrations only touch
  tables their PHP code names literally.
- The upgrade code **never** runs `SHOW TABLES` and deletes anything not in
  the manifest.

**Therefore:** any table whose name does not appear literally in
`sql/database.sql` or any past-or-future `sql/*_upgrade.sql` file survives
upgrade untouched. `custom_*` and `mod_<module>_*` prefixes are safe by
construction (single caveat: upstream is free to introduce a `custom_*` name
in a future upgrade — mitigation is `custom_saas_*` or tenant-specific
prefix; residual risk noted in `04-database-schema.md §10`).

---

## 5. Upstream Tracking Recommendations (Actionable — NOT Executed)

Per Phase 0, no `upstream` remote is configured (`01-repo-inventory.md:8`).
Drift from `openemr/openemr` is **UNKNOWN** until this is remedied.

### 5.1 Recommended one-time setup (operator to run)

```
git remote add upstream https://github.com/openemr/openemr.git
git fetch upstream --tags
git branch --set-upstream-to=origin/master master   # keep tracking our fork's master
```

### 5.2 Measure current drift (safe, read-only after fetch)

```
git log --oneline HEAD..upstream/master | wc -l    # commits upstream is ahead
git log --oneline upstream/master..HEAD | wc -l    # our fork-only commits
git diff --stat upstream/master...HEAD             # file-level divergence
```

Both numbers are captured today as **UNKNOWN — requires product-owner
approval to add the remote** (`01-repo-inventory.md:15, 138-144`).

### 5.3 Monthly cadence (proposed)

| Cadence | Action |
|---|---|
| First working day of each month | `git fetch upstream --tags`; open a `chore/rebase-YYYY-MM` PR |
| Same day | Run `openemr-cmd cst` (clean-sweep-tests), `openemr-cmd pst` (PHPStan), `openemr-cmd pr` (PSR-12), `openemr-cmd rd` (Rector dry-run) — all per `11-devops-docker-ci.md §6` |
| Same day | Rebuild `openemr` Docker image from `openemr/openemr:flex-edge` base (`11-devops-docker-ci.md §1.1` — flex image is the upgrade unit) |
| Same day | Deploy to staging; smoke-test each `oe-module-<ours>-*` |

### 5.4 Branch policy (proposed)

- **`upstream-mirror`** — local tracking branch: `upstream/master`, never
  committed to.
- **`fork-master`** — the fork's integration branch, branched off a specific
  upstream tag (e.g. `v7.0.3` or the current `8.3.0-dev` HEAD `631f2b38`).
  All our commits land here.
- Monthly: rebase `fork-master` onto `upstream-mirror` (or merge, if the
  fork-only commit count grows past a rebase-friendly threshold). Conflicts
  in files outside `interface/modules/custom_modules/oe-module-<ours>-*/`,
  `interface/forms/<new>/`, `sites/`, and `docs/` are **red flags** — see
  §7 commit-classification.

### 5.5 Sixty-second sanity check before every rebase

```
git diff --name-only upstream/master...HEAD | Where-Object {
    $_ -notmatch '^(interface/modules/custom_modules/oe-module-|interface/forms/[^/]+/|sites/|docs/|composer\.(json|lock)$|package(-lock)?\.json$)'
}
```

Any file that survives this filter is a candidate anti-practice (§3) — either
a bugfix that should be **PR'd upstream**, a hotfix that must be **tracked
and removed**, or a genuine local-only commit that shouldn't be there.

---

## 6. Per-Domain Patch Policies

Each row: the safe upgrade path in one paragraph, cited to prior reports.

### Auth / OAuth2

**Google Sign-In is the working template for federating identity**
(`05-auth-and-acl.md §2, §9, §10` "Needs a thin wrapper" row).
`AuthUtils::verifyGoogleSignIn` (`src/Common/Auth/AuthUtils.php:1443,1476`)
matches users by `users.google_signin_email` and calls
`setUserSessionVariables()` unchanged. **Adding Keycloak / SAML / any new
SignIn provider = add a new provider class + a branch in `library/auth.inc.php`
mirroring the Google branch, ideally via a custom module that subscribes to
whatever pre-auth event exists.** Do NOT touch `AuthUtils::confirmUserPassword`
directly — the 12-step order-of-operations at `AuthUtils.php:294-488` (per
Phase 4 §1 table) is upstream-owned. Gap flagged in
`07-modules-and-extensibility.md §4.2`: **no `LoginEvent` / `LogoutEvent`
exists** — the ideal path is an upstream PR adding these events; the
temporary path is a targeted patch to `library/auth.inc.php` tracked as a
type-(c) hotfix per §7.

### ACL

**307 files call `AclMain::aclCheckCore`** (`05-auth-and-acl.md §5.2`) —
replacement is infeasible. Add roles by shipping additive
`gacl_aro_groups`/`gacl_aco`/`gacl_acl` rows in a module's SQL (either
`sql/install.sql` executed via the `install_sql` hook, or a dedicated
per-module `acl_upgrade.php` in the pattern of
`interface/modules/zend_modules/module/PatientFilter/acl/acl_upgrade.php`
cited in `05-auth-and-acl.md §5.2`). **Do NOT bump `$v_acl`** — that counter
is upstream-owned and colliding on it (§3) leaves both sides in a broken
state. The 24 `gacl_*` tables and the section-level granularity model are
strict constraints (`05-auth-and-acl.md §5.3, §5.4`). Fine-grained
per-record authorization is not in scope of the core ACL engine.

### Billing / NPHIES

**No billing events exist in core** — grep for
`billing\.claim|claim\.submit|ClaimEvent|BillingEvent` returns zero hits
(`08-billing-claims-insurance.md §4 Option B`, §7 Gaps).
`BillingProcessor` (`src/Billing/BillingProcessor/BillingProcessor.php`) and
`GeneratorX12` (`src/Billing/BillingProcessor/Tasks/GeneratorX12.php:63-171`)
dispatch nothing. Two upgrade-safe integration patterns:

1. **Filesystem-poll pattern** (proven by
   `claimrevolution/oe-module-claimrev-connect` —
   `08-billing-claims-insurance.md §5.2-5.5`): register N rows in
   `background_services`, read `billing WHERE bill_process=1 AND billed=0`,
   project to FHIR NPHIES, and write back through `ar_session`/`ar_activity`
   + shadow tracker table `mod_nphies_claims`. Zero core edits.
2. **Upstream-PR pattern**: add a `ClaimAboutToBeSubmittedEvent` to
   `src/Events/Billing/` dispatched from `BillingProcessor::process()`
   before generator fires, then subscribe from `oe-module-nphies`. Do NOT
   fork-patch `GeneratorX12` — that is a permanent merge burden
   (`08-billing-claims-insurance.md §4 Option B, §8.3`).

### FHIR

Extend the REST/FHIR surface **only** by subscribing to
`RestApiCreateEvent::EVENT_HANDLE` from a custom module's
`Bootstrap::subscribeToEvents()`, and calling
`$e->addToFHIRRouteMap()` / `addToRouteMap()` / `addToPortalRouteMap()`
(`07-modules-and-extensibility.md §4.1, §7.1` row 3;
`src/Events/RestApiExtend/RestApiCreateEvent.php:50-71`). **Do NOT edit
`apis/routes/_rest_routes_*.inc.php` or `_rest_routes.inc.php`** — those
are core. Also `RestApiResourceServiceEvent` (override an existing resource
service) and `RestApiScopeEvent` (extend OAuth2 scope list) are the
sanctioned extension seams (`07-modules-and-extensibility.md §4`).

> **Phase 5 gap (closed):** Precise FHIR resource-per-verb inventory (which resources
> are read-only like `Coverage`, which have create/update/delete) was not
> yet catalogued in a `06-api-surface.md` at time of this synthesis. Phase 8
> (`08-billing-claims-insurance.md §4 Option A, §7 UNKNOWN #1`) re-derived
> that `Claim` is not routed at all and `Coverage` is read-only. `06-api-surface.md`
> has since been added — see its §4 and §12 for the full per-resource table.

### Multi-tenant

**DB-per-tenant + `sites/<tenant>/` is native and upgrade-safe** — the entire
site-selection mechanism (`interface/globals.php:277-335`), per-site
`sqlconf.php`, and per-site `OE_SITE_DIR` documents tree are core-supported
(`10-multisite-multitenant.md §1-6, §10 Model A`). Automation layer
(provisioning API, subdomain routing patch, per-site workers, backup
tooling, cookie-per-site) is buildable **outside** core — small `globals.php`
patch to lift `HTTP_HOST` fallback out of the `$ignoreAuth` gate is the
only core change contemplated (`10-multisite-multitenant.md §11`), and even
that should be routed as an upstream PR rather than a fork patch.
**Shared-DB refactor with `tenant_id` column is NOT upgrade-safe** —
lower-bound 1875 `sqlStatement(` call sites across 453 files would need
tenant scoping (`10-multisite-multitenant.md §10 Model B`) and every future
upstream file would need re-scoping. Do not attempt.

### i18n / Arabic

Ship translations by **loading rows into `lang_custom`** (the user-override
table — `sql/database.sql:3578`, per Phase 12 §2 table row 4). **Do NOT edit
`contrib/util/language_translations/currentLanguage_utf8.sql`** — that is
upstream's master dump, regenerated by upstream's Perl toolchain
(`13-i18n-localization.md §3`). Loading the filtered `lang_id=22` (Arabic)
rows post-install is upgrade-safe; overriding specific constants via
`lang_custom` is also safe. **`bootstrap-rtl` is an upgrade cliff**
(`13-i18n-localization.md §4, §10 UNKNOWN #9`): the napa dep pins a
single-commit archive of an unmaintained third-party fork
(`package.json:112-113`). Recommended action: fork it into our own org and
repin — treat as a fork-only vendoring exercise, not a core edit. Hijri
calendar (`13-i18n-localization.md §5, §10 row 4`) and Arabic PDF fonts
(`09-frontend-ui.md §6`) are net-new module work, not core patches.

### Frontend

Theme via new SCSS entrypoint. Two upgrade-safe surfaces:

1. **Per-tenant CSS override** at
   `sites/<tenant>/documents/theme/` — **UNKNOWN whether this exact path is
   supported today**; what is documented is that `interface/globals.php:474-495`
   resolves `css_header` from `globals` table + per-user `user_settings`
   override, and served CSS is under `public/themes/<name>.css`. A per-tenant
   theme file can be added as a new named `.scss` entrypoint (see below) and
   the tenant's `globals.css_header` pointed at the resulting bundle.
   `13-i18n-localization.md UNKNOWN` and `09-frontend-ui.md §2, §10` do not
   confirm a `sites/<tenant>/documents/theme/` convention specifically —
   flagged UNKNOWN.

   **Resolved 2026-08-19 — the path does not exist**, see
   `docs/discovery/openemr-decision-evidence/14-frontend-ui-evidence.md` and
   `21-recommended-decision-updates.md` (Q59): `sites/<tenant>/documents/theme/` has
   **zero runtime references** anywhere in tracked PHP, Twig or JS — it existed only in
   this discovery corpus's own prior notes. Per-tenant branding today is logos only, via
   `LogoService` (`src/Services/LogoService.php:75-108`). The recommended path forward is
   CSS-variable design tokens over the shared immutable theme bundle, not a per-tenant
   `.css`/`.js` override — the latter would introduce XSS, data-exfiltration and
   cross-tenant asset-leakage risk that the evidence file details in full.
2. **New SCSS entrypoint under `interface/themes/`** — Webpack builds
   `interface/themes/*.scss` 1:1 into `public/themes/*.css`
   (`09-frontend-ui.md §2` — `webpack.themes.js:70-72`). Adding a new
   `oemr_saas_<brand>.scss` file survives upgrades if it doesn't collide with
   an upstream name. **However**, `interface/themes/` is upstream-tracked
   (row 2 of Phase 2 tree) — safer path is a **new SCSS file inside the
   module's own dir**, wired into the shell by a `StyleFilterEvent`
   subscriber (`07-modules-and-extensibility.md §4` — `Core/StyleFilterEvent`).

Menu via `MenuEvent` subscriber (§2 above). **No core template edits.** New
UI screens ship as Twig or PHP templates inside the module's `templates/`
and are web-callable from `interface/modules/custom_modules/oe-module-<name>/public/<page>.php`
(`07-modules-and-extensibility.md §7.2` row "New UI screen"; path-gated by
`ModulesApplication::checkModuleScriptPathForEnabledModule()` at
`src/Core/ModulesApplication.php:86-106`).

### Docker

Base off `openemr/openemr:flex-edge` (or pin a release tag) —
`docker/flex/Dockerfile` is the upgrade unit (`11-devops-docker-ci.md §2.1,
§7`). Layer our modules via Composer (`openemr/oe-module-installer-plugin`
drops them into `interface/modules/custom_modules/`) + entrypoint scripts
that run `OPENEMR_SETTING_<gl_name>=<value>` post-install (per
`docker/flex/utilities/devtoolsLibrary.source:134-164`, cited in
`11-devops-docker-ci.md §2.4`). **Do NOT fork the base Dockerfile** — the
365-line `docker/flex/Dockerfile` receives continuous upstream churn
(Alpine version, PHP extensions, kcov build). Build our image as
`FROM openemr/openemr:flex-edge` + `COPY` our modules + `RUN composer install`.

---

## 7. Fork-vs-Upstream Commit Policy

Every commit on our fork should be classifiable as exactly one of:

| Class | Definition | Rebase treatment | Recommended conventional-commit scope |
|---|---|---|---|
| **(a) local-only** | Custom module code, deployment configs, per-tenant assets, docs. Lives outside core paths per §5.5 filter. | Always carried forward. | `feat(nphies)`, `feat(saudi)`, `feat(saas)`, `feat(tenant)`, `chore(deploy)`, `docs(discovery)` |
| **(b) upstream-PR candidate** | Bugfix, missing event (e.g. `LoginEvent`, `ClaimAboutToBeSubmittedEvent` — Phase 6 §4.2, Phase 7 §4 Option B), security patch, translation string fix. Must be genuinely useful upstream. | PR to `openemr/openemr`. Once merged upstream, the commit disappears from our fork on next rebase. | `fix(auth)`, `fix(billing)`, `feat(events)`, `fix(security)` — with no `saas`/`nphies`/`saudi` scope |
| **(c) temporary hotfix** | Core-file edit that we need immediately (security, blocking bug) but cannot wait for upstream. **Must be tracked in `docs/00-discovery/hotfixes.md` (or similar) with revert conditions.** | Revert as soon as upstream absorbs the fix (or ships an equivalent). Each rebase re-evaluates. | `hotfix(core)` — deliberately distinct scope so `git log --grep='hotfix('` finds them all |

### Enforcement

- **Pre-commit hook** (via `openemr-cmd prek-install` per CLAUDE.md's
  "Pre-commit hooks" section): the conventional-commits check
  (`.github/workflows/conventional-commits.yml` per `11-devops-docker-ci.md
  §4.1 (b)`) already runs. Extend it locally with an allowed-scope
  allowlist including `saas`, `nphies`, `saudi`, `tenant`, `deploy`, `core`
  (for hotfix), plus the standard upstream scopes.
- **Monthly hotfix census:** `git log --grep='hotfix(' upstream-mirror..fork-master`
  — every entry should have an open upstream PR link in its body, or be
  explicitly re-approved.
- **AI-assist trailer:** every AI-authored commit adds
  `Assisted-by: <tool>` per CLAUDE.md.

---

## 8. Concrete Upgrade Playbook (for Document 0)

Execute in order. Each step is an `openemr-cmd` invocation (see
`11-devops-docker-ci.md §6`) unless otherwise noted.

| # | Upgrade step | Command / action | Owner |
|--:|---|---|---|
| 1 | Fetch upstream | `git fetch upstream --tags` (from host) | Release engineer |
| 2 | Read the diff | `git log --oneline HEAD..upstream/master`; `git diff --stat upstream/master...HEAD` | Release engineer |
| 3 | Create rebase branch | `git checkout -b chore/rebase-YYYY-MM` off `fork-master` | Release engineer |
| 4 | Rebase or merge | `git rebase upstream/master` (or `git merge upstream/master` if commit count is large) | Release engineer |
| 5 | **Zero-core-conflict check** | Any conflict outside `interface/modules/custom_modules/oe-module-<ours>-*/`, `interface/forms/<new>/`, `sites/`, or `docs/` is a **red flag** — surface immediately (§5.5 filter) | Release engineer + module owners |
| 6 | Rebuild vendor / node tree | `openemr-cmd dev-reset-install` (or, for a clean rebuild without data wipe: `docker compose build openemr` + `composer install` inside container) | Release engineer |
| 7 | Run schema upgrade against staging DB | Web UI: `sql_upgrade.php`; or CLI equivalent per `04-database-schema.md §7a` | Release engineer |
| 8 | Run ACL upgrade | Web UI: `acl_upgrade.php` (per `05-auth-and-acl.md §5.4`) | Release engineer |
| 9 | Run per-module upgrades | Module Manager UI → each `oe-module-<ours>-*` → "Upgrade SQL" button (invokes `upgrade_sql` hook per `07-modules-and-extensibility.md §1.3`) | Module owners |
| 10 | **Clean-sweep tests** | `openemr-cmd cst` (`clean-sweep-tests` — unit + api + e2e + services per `11-devops-docker-ci.md §6`) | Release engineer |
| 11 | Isolated tests | `openemr-cmd pit` (`phpunit-isolated` — host-runnable per `12-testing-infrastructure.md §2`) | Release engineer |
| 12 | PHPStan level 10 | `openemr-cmd pst` (`11-devops-docker-ci.md §6`, `12-testing-infrastructure.md §8`) | Release engineer |
| 13 | PSR-12 style | `openemr-cmd pr` (`psr12-report`) | Release engineer |
| 14 | Rector dry-run | `openemr-cmd rd` (`rector-dry-run`) | Release engineer |
| 15 | Semgrep + Codespell + syntax | `openemr-cmd cq` (`code-quality` bundle) | Release engineer |
| 16 | JS tests | `npm test` (Jest — 4 files today per `12-testing-infrastructure.md §11`) | Frontend owner |
| 17 | Rebuild Docker image | `docker build -t <our-registry>/openemr:<version>-<rebase-date>` **from a Dockerfile that `FROM openemr/openemr:flex-edge`** (per §6 Docker paragraph) | Release engineer |
| 18 | Deploy to staging | Per infra runbook (out of tree — `11-devops-docker-ci.md UNKNOWN #1`) | Ops |
| 19 | Smoke-test each SaaS surface | (a) login flow (Google Sign-In + local + LDAP); (b) create/update/read patient via UI and via FHIR API with `patient/*` scope; (c) one billing flow (fee sheet → claim queue → NPHIES/X12 dispatch); (d) one Arabic-locale screen render; (e) `openemr-cmd worktree exec … e '<Panther selenium debug script>'` per CLAUDE.md's "Browser debugging" section | QA |
| 20 | Deploy to production | Per infra runbook; publish release notes with cited upstream commits (`git log upstream-mirror-prev..upstream-mirror-current`) | Ops |
| 21 | Post-deploy hotfix census | `git log --grep='hotfix(' <prev-fork-master>..fork-master` — verify each still-outstanding hotfix is either still needed or reverted | Release engineer |

---

## Summary

- OpenEMR's four upgrade drivers (`sql/*_upgrade.sql` chain + `acl_upgrade.php`
  chain + per-module `install_sql`/`upgrade_sql` hooks + not-yet-live Doctrine
  Migrations) all touch only literally-named tables — the `custom_*` / `mod_*`
  prefix is safe by construction (`04-database-schema.md §10`).
- Upgrade-safe extension surfaces: custom modules with Symfony event
  subscribers, `RestApiCreateEvent` for REST/FHIR routes, `MenuEvent` for
  navigation, `TwigContainer::addPath()` for templates, per-site
  `sites/<tenant>/` overrides including `documents/custom_menus/*.json` —
  none of which requires editing any file upstream owns.
- **This fork has no `upstream` remote configured** (`01-repo-inventory.md:8`)
  — drift from `openemr/openemr` is unmeasurable until operator runs the
  §5.1 commands. Recommended cadence: monthly rebase from
  `openemr/openemr` master, with a fork-only branch policy segregating
  local-only / upstream-PR-candidate / temporary-hotfix commits via
  conventional-commit scopes.
- Domain-specific verdicts: Auth federates cleanly via Google-Sign-In-shaped
  provider modules; ACL cannot be replaced (307 call sites) but can be
  extended additively; Billing/NPHIES follows `claimrev-connect`'s
  filesystem-poll + `mod_*` shadow-table pattern; FHIR extends via
  `RestApiCreateEvent`; multi-tenant sticks with DB-per-tenant; i18n loads
  translations into `lang_custom`, not `currentLanguage_utf8.sql`; Docker
  layers `FROM openemr/openemr:flex-edge`, never forks the base Dockerfile.
- Playbook is 21 steps, gated at step 5 by the zero-core-conflict check that
  distinguishes "clean rebase" from "an anti-practice snuck in."

## UNKNOWNs — require product-owner input or later-phase data

1. **Actual drift from `openemr/openemr` master** — unmeasurable until an
   `upstream` remote is added; §5.1 gives the exact commands but they were
   not executed (read-only mandate). Carried from `01-repo-inventory.md:15`.
2. **Phase 5 (`06-api-surface.md`) not yet written at time of writing** — precise FHIR
   resource-per-verb table (which resources are read-only vs full-CRUD)
   was re-derived by Phase 7 (`08-billing-claims-insurance.md §4, §7 UNKNOWN #1`)
   but not comprehensively catalogued. **`06-api-surface.md` now exists** in this
   directory and comprehensively catalogues the resource-per-verb table at its §4;
   §6's FHIR paragraph above can be read alongside it.
3. **`sites/<tenant>/documents/theme/` convention** — not verified in prior
   reports; `09-frontend-ui.md §2` documents `interface/themes/*.scss`
   compilation and `interface/globals.php:634` runtime default, but does
   not confirm the exact per-site theme override path. Requires
   product-owner input or a follow-up grep.
4. **Whether the six non-`claimrev-connect` in-tree `oe-module-*` are
   byte-identical to upstream** — carried from `03-directory-map.md:182`
   and `07-modules-and-extensibility.md UNKNOWN #4`. Blocks a clean
   fork-vs-upstream diff on module code; resolvable once §5.1 remote is
   added.
5. **Missing core events** — `LoginEvent`/`LogoutEvent`,
   `EncounterCreatedEvent`/`EncounterSignedEvent`, claim state
   transitions, user-role-changed all absent (`07-modules-and-extensibility.md
   §4.2`). Product decision needed: open upstream PRs (class-b commits) or
   temporary hotfix (class-c commits)?
6. **`openemr/oe-module-installer-plugin` internal class name and
   target-path algorithm** — `07-modules-and-extensibility.md UNKNOWN #1`.
   Affects confidence in the "Composer install alone is not activation"
   claim (which relies on Register + Install SQL + Enable buttons through
   Module Manager UI).
7. **Custom fork ACL policy** — should we ever author `$v_acl = 14`, or
   freeze at 13 and layer authz above `gacl`? Carried from
   `05-auth-and-acl.md UNKNOWN #4`. Impacts whether §3's
   "never bump `$v_acl`" rule is absolute or negotiable.
8. **Hardcoded `GITHUB_COMPOSER_TOKEN*` in `docker/development-easy/docker-compose.yml:75-77`
   to rotate** (`14-security-compliance.md §9`) — two live-shaped `ghp_*`
   tokens committed. Independent of upgrade strategy but flagged since
   any rebase will preserve them. **Re-scoped 2026-08-19** — see
   `docs/discovery/openemr-decision-evidence/15-security-compliance-code-evidence.md §2`:
   the actual footprint is 12 token values across 4 compose files (3 obfuscation layers
   each), not 2 values in 1 file. Also, per that evidence file, these tokens are
   committed in upstream `openemr/openemr` (the fork has zero own commits), so rotation
   is upstream's action; the fork's obligation is containment.
