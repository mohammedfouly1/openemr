# Phase 0 — Modules & Extensibility (Deep Dive)

_Read-only audit. Fork HEAD `631f2b38` (OpenEMR 8.3.0-dev). Extends Phase 2 (`03-directory-map.md` §2.1–2.7)._

Scope: DB registration + install/enable/disable lifecycle for `oe-module-*`, file-by-file walk of a representative module (`oe-module-claimrev-connect`), how `openemr/oe-module-installer-plugin` places modules on disk, an event catalog under `src/Events/**`, dispatch-site census, comparative index of the 7 custom + 14 zend bundled modules, and a hard-line "no-core-edit" verdict.

---

## 1. Module lifecycle

### 1.1 `modules` DB table (canonical registry)

Schema — `sql/database.sql:7786-7808`:

| Column | Type | Notes |
|---|---|---|
| `mod_id` | INT PK (auto-inc) | |
| `mod_name` | VARCHAR(64) | Human name, first line of `info.txt` (`InstModuleTable::register()`, `interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php:240-243`). |
| `mod_directory` | VARCHAR(64) | Directory name under `interface/modules/{custom,zend}_modules/` (part of PK). |
| `mod_parent`, `mod_type` | VARCHAR(64) | Legacy — usually empty. |
| `mod_active` | TINYINT | **The enable/disable flag.** Boot filter is `WHERE mod_active = 1` (`src/Core/ModulesApplication.php:141`). |
| `mod_ui_name`, `mod_relative_link`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `mod_nick_name`, `mod_enc_menu` | | Module Manager UI + placement. `mod_ui_active=1` also lets a disabled module surface its config button (`AbstractModuleActionListener::setModuleActiveState`, `src/Core/AbstractModuleActionListener.php:158-174`). |
| `permissions_item_table` | CHAR(100) | Optional. |
| `directory` | VARCHAR(255) | Redundant with `mod_directory`. |
| `date` | DATETIME | Set to `NOW()` on `register()`. |
| `sql_run` | TINYINT | 0=not run, 1=install SQL applied. Drives Module Manager's "Install SQL" button. |
| `type` | TINYINT | `MODULE_TYPE_ZEND=1` vs `MODULE_TYPE_CUSTOM` (any non-1). Boot uses `type != 1` for custom modules (`ModulesApplication.php:141`). |
| `sql_version`, `acl_version` | VARCHAR(150) | Applied-migration markers. |

Seed rows (`sql/database.sql:7814-7818`) preload the 5 baseline zend modules (Immunization, Syndromicsurveillance, Documents, Ccr, Carecoordination) with `mod_active=1, type=1, sql_run=1`. **Custom `oe-module-*` are not seeded** — they are inserted the first time an admin clicks "Register" in the Module Manager UI.

### 1.2 Registration / discovery flow

There is **no directory scan on Kernel boot**. Registration is explicit — the Module Manager admin UI (`interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php`) walks `interface/modules/custom_modules/` on demand, and `InstModuleTable::register($directory, $rel_path, $state=0, $base="custom_modules")` (`InstModuleTable.php:228-289`) INSERTs a row with `mod_active=0` (state=0 by default). The admin then clicks Install → Enable to transition the row.

**Boot-time module loading** — `src/Core/ModulesApplication.php:132-164` (`bootstrapCustomModules`):

```
SELECT mod_name, mod_directory FROM modules
  WHERE mod_active = 1 AND type != 1
  ORDER BY mod_ui_order, date;                   -- :141
foreach row:
    path = interface/modules/custom_modules/<mod_directory>/openemr.bootstrap.php
    if !is_readable(path): retry x3, then UPDATE modules SET mod_active=0  -- :146,155
    include path                                  -- :188 (loadCustomModule)
dispatch ModuleLoadEvents::MODULES_LOADED         -- :163
```

Bootstrap file constant: `const CUSTOM_MODULE_BOOSTRAP_NAME = 'openemr.bootstrap.php';` (`ModulesApplication.php:39`). The bootstrap is `include`d in a scope that already exposes `$classLoader` (`ModulesClassLoader`) and `$eventDispatcher` (Symfony) as locals — this is the sole documented contract, see `src/Core/ModulesClassLoader.php:4-22` and `src/Events/Messaging/SendSmsEvent.php:25` (which references `oe-module-faxsms/openemr.bootstrap.php` as canonical example).

Zend/Laminas modules follow a parallel path via `ModulesApplication::oemr_zend_load_modules_from_db()` (`ModulesApplication.php:119-130`) with `mod_active=1 AND type=1`.

### 1.3 Lifecycle hooks (`ModuleManagerListener`)

The Manager UI invokes hooks by including `ModuleManagerListener.php` at the module root and calling `moduleManagerAction($methodName, $modId, $currentActionStatus)` — dispatched in `InstallerController::notifyModuleListener()`, `interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php:267-311`.

Every listener extends `OpenEMR\Core\AbstractModuleActionListener` (`src/Core/AbstractModuleActionListener.php:34-175`). The abstract class defines the full hook surface as private `mixed` methods with no-op defaults; subclasses override any subset:

| Hook method | When called | Default | Evidence |
|---|---|---|---|
| `install($modId, $status)` | Register button | no-op | `AbstractModuleActionListener.php:83-86` |
| `enable($modId, $status)` | Enable button | no-op | `:93-96`, override example `oe-module-claimrev-connect/ModuleManagerListener.php:98-114` (flips 6 `background_services` rows to active) |
| `disable($modId, $status)` | Disable button | no-op | `:103-106`, override `oe-module-claimrev-connect/ModuleManagerListener.php:121-130` |
| `unregister($modId, $status)` | Unregister button | no-op | `:113-116`, override `oe-module-claimrev-connect/ModuleManagerListener.php:137-145` (DELETEs the `background_services` rows) |
| `install_sql($modId, $status)` | Install SQL button (runs `sql/install.sql` or `table.sql`) | no-op | `:123-126` |
| `upgrade_sql($modId, $status)` | Upgrade SQL button (runs versioned `sql/upgrade/*.sql`) | no-op | `:133-136` |
| `help_requested($modId, $status)` | Help button (module-defined) | not in abstract | `oe-module-claimrev-connect/ModuleManagerListener.php:83-91` shows the pattern — module includes `show_help.php` |

Required overrides on every listener:

- `public static function getModuleNamespace(): string` — returned namespace is auto-registered by `InstallerController::notifyModuleListener()` (`InstallerController.php:285-293`) so config/help pages that run outside `openemr.bootstrap.php` still autoload the module's `src/`.
- `public static function initListenerSelf(): static` — factory the controller uses to instantiate the class.

Return value: on error the hook returns an **error string**; the UI displays it as an alert (`AbstractModuleActionListener.php:56-59`). The action already happened in the Manager — the hook only *reports*.

**There is no separate `onInstall`/`onEnable` interface** — the mechanism is duck-typed via `method_exists(self::class, $methodName)` (`oe-module-claimrev-connect/ModuleManagerListener.php:45`). The button label in the UI is passed verbatim as `$methodName`.

---

## 2. Module skeleton — `oe-module-claimrev-connect` file-by-file

Full top-level inventory:

```
oe-module-claimrev-connect/
├── .gitignore
├── CHANGELOG.md, LICENSE, README.md
├── cleanup.sql              # counterpart to table.sql (uninstall SQL)
├── composer.json            # PSR-4 map + installer-plugin declaration
├── helpdocs/                # bundled HTML help pages (help_requested hook)
├── info.txt                 # single line: "ClaimRev Clearinghouse Connector"
├── moduleConfig.php         # Config button entry point
├── ModuleManagerListener.php# enable/disable/unregister hooks
├── openemr.bootstrap.php    # runtime entry — registers event subscribers
├── public/                  # 30 web-exposed .php scripts + assets/
├── src/                     # 63 PSR-4 classes under OpenEMR\Modules\ClaimRevConnector
├── table.sql                # install-time schema
└── templates/               # 21 .php partials (rendered by controllers in src/)
```

### 2.1 `info.txt` (`oe-module-claimrev-connect/info.txt`)

One line. First line becomes `modules.mod_name` (`InstModuleTable.php:240-243`).

### 2.2 `composer.json` (`oe-module-claimrev-connect/composer.json`, 24 lines)

Verbatim essentials:

```json
{
  "name": "claimrevolution/oe-module-claimrev-connect",
  "type": "openemr-module",
  "autoload": { "psr-4": {"OpenEMR\\Modules\\ClaimRevConnector\\": "src/"} },
  "require": {
    "openemr/oe-module-installer-plugin": "^0.1.0",
    ...
  }
}
```

The `"type": "openemr-module"` is the string the installer plugin filters on (see §3). Same string in all 7 in-tree modules (grep `type.*openemr-module` — 8 hits across `interface/modules/custom_modules/*/composer.json` + 2 test-fixture modules under `tests/eventdispatcher/`).

### 2.3 `openemr.bootstrap.php` (`oe-module-claimrev-connect/openemr.bootstrap.php`, 30 lines)

Full contract:

```php
declare(strict_types=1);
namespace OpenEMR\Modules\ClaimRevConnector;

/** @var \OpenEMR\Core\ModulesClassLoader $classLoader */
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\ClaimRevConnector\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);
/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
```

Both `$classLoader` and `$eventDispatcher` are injected by `ModulesApplication::loadCustomModule()` (`src/Core/ModulesApplication.php:179-192`) — the module code sees them as pre-existing locals in the include scope. All event wiring for the module lives in `src/Bootstrap.php::subscribeToEvents()`.

### 2.4 `ModuleManagerListener.php` (`oe-module-claimrev-connect/ModuleManagerListener.php`, 146 lines)

Class **not namespaced** — the abstract's docblock explicitly forbids it (`src/Core/AbstractModuleActionListener.php:22-23`). Overrides `enable` (line 98), `disable` (121), `unregister` (137), plus module-specific `help_requested` (83). Returns `$currentActionStatus` on success.

### 2.5 `moduleConfig.php` (`oe-module-claimrev-connect/moduleConfig.php`, 31 lines)

Entry point wired to the Manager's "Config" button. Bootstraps `interface/globals.php` (`:20`), re-registers the module namespace (`:26-27` — because config runs outside `openemr.bootstrap.php` include scope), sets `$module_config = 1`, then usually includes a per-module admin page. Runs even when the module is `mod_active=0` if `mod_ui_active=1`.

### 2.6 `sql/` and `table.sql`

This module uses a flat `table.sql` at module root (applied by the Manager's "Install SQL" button; per `sql_run` flag on the `modules` row). No `sql/upgrade/` in this module. Other modules use richer layouts — e.g. `oe-module-weno` has both `table.sql` and `sql/` directories.

`cleanup.sql` at module root is the paired "drop" script, run on Unregister when a module ships one.

### 2.7 `public/`, `templates/`, `src/`

- `public/` — 30 web-callable `.php` files (`aging_report.php`, `claims.php`, `era.php`, `payment_advice.php`, `setup.php`, …) plus an `assets/` sub-tree. Hit directly via `/interface/modules/custom_modules/oe-module-claimrev-connect/public/<page>.php`. Access gated by `ModulesApplication::checkModuleScriptPathForEnabledModule()` (`src/Core/ModulesApplication.php:86-106`) — a disabled module's scripts throw `AccessDeniedException`.
- `templates/` — 21 `.php` partials (**not** Twig; this module uses raw PHP includes). No repo-wide Twig template-namespace convention was found for custom modules (grep on `@openemr`/`@core` under `templates/` returns nothing; the only `addPath` API is `OpenEMR\Common\Twig\TwigContainer::addPath()` at `src/Common/Twig/TwigContainer.php:55,59`, which appends to the loader search path with no namespace).
- `src/` — 63 PSR-4 classes under `OpenEMR\Modules\ClaimRevConnector\`. Contains `Bootstrap.php` (event subscriptions), service classes (`ClaimRevApi`, `EligibilitySweepService`, `PaymentAdvicePostingService`, background services `ClaimRev_Watchdog_Service`, `ClaimRev_Notification_Service`, …), controllers (`ClaimsPage`, `EraPage`, `PaymentAdvicePage`), DTOs (`Dto/`), and OpenEMR-7.x compat shims (`Compat/compat.php`).

### 2.8 Minimum viable module — distilled skeleton

```
oe-module-<name>/
├── info.txt                    # single line: human-readable name
├── composer.json               # "type": "openemr-module", PSR-4 map, require openemr/oe-module-installer-plugin
├── openemr.bootstrap.php       # register namespace on $classLoader; instantiate Bootstrap; subscribe to events
├── ModuleManagerListener.php   # extends OpenEMR\Core\AbstractModuleActionListener; override enable/disable/install_sql
├── moduleConfig.php            # optional — Config button target
├── src/
│   ├── Bootstrap.php           # subscribeToEvents(): $ed->addListener(...)
│   └── ...                     # services, controllers, event listeners
├── public/                     # web-callable pages (path-gated by mod_active)
├── templates/                  # PHP partials or Twig (module must addPath its own dir)
├── sql/
│   ├── install.sql             # applied by "Install SQL"
│   └── upgrade/                # applied by "Upgrade SQL"
└── README.md
```

---

## 3. Composer-based module installer (`openemr/oe-module-installer-plugin`)

Vendored: **no** — `vendor/` is not committed. Referenced by `composer.lock:6541-6587` (version `0.1.5`, source `https://github.com/openemr/oe-module-installer-plugin`).

Registration in the root repo, `composer.json:201-208`:

```json
"config": {
    "allow-plugins": {
        "openemr/oe-module-installer-plugin": true,
        ...
    }
}
```

Modules opt in via their own `composer.json` — `"type": "openemr-module"` (verified across 5 of 7 in-tree modules that ship a `composer.json`; the plugin's Composer `InstallerInterface` implementation matches on this type and drops the package under `interface/modules/custom_modules/<vendor-suffix-or-package-suffix>/`).

**Runtime require** — root `composer.json` currently pulls exactly one runtime module (`docs/00-discovery/03-directory-map.md:138`): `claimrevolution/oe-module-claimrev-connect: ^2.1`. Additional installability declared as VCS repo: `openemr/oe-module-cqm` (`03-directory-map.md:139`). The other six `oe-module-*` dirs in this fork are **tracked in git** (see 03 §2.6 note).

Enforcement references in tooling — the plugin's install destination is coordinated with these files that all know the directory is populated at `composer install` time:

- `.gitignore:13` — banner comment that the plugin drops modules here; `.gitignore` excludes them.
- `phpstan.neon.dist:17`, `phpcs.xml.dist:68`, `rector.php:40` — exclude the installed-module dir from static analysis.
- `codecov.yml:3` — exclude from coverage.

**Flow: `composer require openemr/oe-module-<name>`** →
1. Composer resolves the package.
2. Because plugin is allow-listed (`composer.json:205`), it loads.
3. Plugin inspects `type` of installed package; `openemr-module` triggers custom install path.
4. Package is placed at `interface/modules/custom_modules/oe-module-<name>/` (path convention verified by all 7 in-tree examples).
5. Admin still has to run **Register → Install SQL → Enable** through the Module Manager UI to insert the `modules` row and set `mod_active=1`. Composer install alone does not activate a module.

**UNKNOWN — requires product-owner input:** the exact plugin class name (`OpenEMR\Composer\ModuleInstaller` or similar) and its target-path algorithm cannot be cited from repo alone — the plugin source lives out-of-tree in `openemr/oe-module-installer-plugin` GitHub repo. Access to `vendor/openemr/oe-module-installer-plugin/` (empty here) would resolve this.

---

## 4. Event catalog (`src/Events/**`)

Full inventory via `git ls-files src/Events/` — 79 files across 22 subject-area subdirectories:

| Subject area | Event classes |
|---|---|
| **root** | `AbstractBoundFilterEvent`, `BoundFilter` |
| `Appointments/` | `AppointmentDialogCloseEvent`, `AppointmentJavascriptEventNames`, `AppointmentRenderEvent`, `AppointmentSetEvent`, `AppointmentsFilterEvent`, `CalendarFilterEvent`, `CalendarUserGetEventsFilter` |
| `Billing/Payments/` | `DeletePayment`, `PostFrontPayment` |
| `CDA/` | `CDAPostParseEvent`, `CDAPreParseEvent` |
| `Codes/` | `CodeTypeInstalledEvent`, `ExternalCodesCreatedEvent` |
| `Command/` | `CommandRunnerFilterEvent` |
| `Core/` | `ModuleLoadEvents`, `SQLUpgradeEvent`, `ScriptFilterEvent`, `StyleFilterEvent`, `TemplatePageEvent`, `TwigEnvironmentEvent`, `Sanitize/IsAcceptedFileFilterEvent` |
| `Encounter/` | `EncounterButtonEvent`, `EncounterFormsListRenderEvent`, `EncounterMenuEvent`, `LoadEncounterFormFilterEvent` |
| `Facility/` | `FacilityCreatedEvent`, `FacilityUpdatedEvent` |
| `Globals/` | `GlobalsInitializedEvent` |
| `Main/Tabs/` | `RenderEvent` |
| `Messaging/` | `SendNotificationEvent`, `SendSmsEvent` |
| `Patient/` | `BeforePatientCreatedEvent`, `BeforePatientUpdatedEvent`, `PatientBeforeCreatedAuxEvent`, `PatientCreatedEvent`, `PatientCreatedEventNotifier`, `PatientUpdatedEvent`, `PatientUpdatedEventAux` |
| `Patient/Summary/` (+`Card/`) | `PortalCredentialsTemplateDataFilterEvent`, `PortalCredentialsUpdatedEvent`, `Card/CardInterface`, `Card/CardModel`, `Card/RenderEvent`, `Card/RenderInterface`, `Card/RenderModel`, `Card/SectionEvent` |
| `PatientDemographics/` | `RenderEvent`, `RenderPharmacySectionEvent`, `UpdateEvent`, `ViewEvent` |
| `PatientDocuments/` | `PatientDocumentCreateCCDAEvent`, `PatientDocumentEvent`, `PatientDocumentStoreOffsite`, `PatientDocumentTreeViewFilterEvent`, `PatientDocumentViewCCDAEvent`, `PatientRetrieveOffsiteDocument` |
| `PatientFinder/` | `ColumnFilter`, `PatientFinderFilterEvent` |
| `PatientPortal/` | `AppointmentFilterEvent`, `RenderEvent` |
| `PatientReport/` | `PatientReportEvent`, `PatientReportFilterEvent` |
| `PatientSelect/` | `PatientSelectFilterEvent` |
| **`RestApiExtend/`** | `RestApiCreateEvent`, `RestApiResourceServiceEvent`, `RestApiScopeEvent`, `RestApiSecurityCheckEvent` |
| `Services/` | `DornLabEvent`, `LogoFilterEvent`, `QuestLabTransmitEvent`, `ServiceDeleteEvent`, `ServiceSaveEvent` |
| `User/` | `UserCreatedEvent`, `UserEditRenderEvent`, `UserUpdatedEvent` |
| `UserInterface/` | `ActionButtonInterface`, `BaseActionButtonHelper`, `PageHeadingRenderEvent` |

Menu events live outside `src/Events/`: `src/Menu/MenuEvent.php:17` (main menu) and `src/Menu/PatientMenuEvent.php:19` (patient tab strip). Called from `src/Menu/MainMenuRole.php:67,72` and `src/Menu/PatientMenuRole.php:81,86`.

### 4.1 Key events for SaaS layers

| Event class | Event name (string) | Dispatched at | Payload |
|---|---|---|---|
| `Patient\BeforePatientCreatedEvent` | `patient.before-created` | `src/Services/PatientService.php:189` | Mutable patient data array pre-INSERT. |
| `Patient\PatientCreatedEvent` | `patient.created` | `src/Services/PatientService.php:201` | New patient row + generated `pid`. |
| `Patient\BeforePatientUpdatedEvent` | `patient.before-updated` | `src/Services/PatientService.php:268,323` | Mutable patient data array pre-UPDATE. |
| `Patient\PatientUpdatedEvent` | `patient.updated` | `src/Services/PatientService.php:291,354` | Updated patient row. |
| `Patient\PatientUpdatedEventAux` | `patient.updated.aux` | (aux path — see class `src/Events/Patient/PatientUpdatedEventAux.php:27`) | Secondary updater trigger. |
| `Encounter\EncounterMenuEvent` | `menu.render` | `interface/patient_file/encounter/forms.php:648` | Encounter action-menu array (mutate to add buttons). |
| `Encounter\EncounterFormsListRenderEvent` | `forms.encounter.list.render.pre` / `.post` | `interface/patient_file/encounter/forms.php:673,1038` | Rendering hooks around the forms list inside an encounter. |
| `Encounter\LoadEncounterFormFilterEvent` | `encounter.load_form_filter` | (grep — used in form-loader) | Filter/redirect form module loads. |
| **Encounter created / signed / closed** | — | **NOT FOUND** | No dedicated event class. Encounter save goes through direct SQL in `library/forms.inc.php` and `src/Services/EncounterService.php`; nearest hook is `ServiceSaveEvent` if the service extends `BaseService`. See §Gaps. |
| `Billing\Payments\PostFrontPayment` | `billing.payment.action.post.front.payment` | `interface/patient_file/front_payment.php:1875` | Fires after a patient-facing payment is posted. |
| `Billing\Payments\DeletePayment` | `billing.payment.action.delete.payment` | `src/Events/Billing/Payments/DeletePayment.php:17` (constructor takes `$paymentId`) | Payment deletion trigger. |
| **Claim state transitions** | — | **NOT FOUND** as core events | Claim/x12 flow is handled inside `oe-module-claimrev-connect` (its own internal service classes); no core-level `ClaimSubmittedEvent`/`ClaimAcceptedEvent`. |
| `Menu` (main) — `src/Menu/MenuEvent.php` | `menu.update`, `menu.restrict` | `src/Menu/MainMenuRole.php:67,72` | Main-menu tree array; mutate to inject nav items. |
| `Menu` (patient tabs) — `src/Menu/PatientMenuEvent.php` | `patient.menu.update`, `patient.menu.restrict` | `src/Menu/PatientMenuRole.php:81,86` | Per-patient tab strip. |
| **`RestApiExtend\RestApiCreateEvent`** | `restConfig.route_map.create` | `src/RestControllers/Finder/StandardRouteFinder.php:37`, `src/RestControllers/FHIR/Finder/FhirRouteFinder.php:31`, `src/RestControllers/Finder/PortalRouteFinder.php:33` | Passes three route-map arrays (`route_map`, `fhir_route_map`, `portal_route_map`) + `HttpRestRequest`. Listeners call `addToRouteMap()` / `addToFHIRRouteMap()` / `addToPortalRouteMap()` (`src/Events/RestApiExtend/RestApiCreateEvent.php:50-71`). |
| `RestApiExtend\RestApiResourceServiceEvent` | (see class) | — | For overriding FHIR resource services. |
| `RestApiExtend\RestApiScopeEvent` | (see class) | — | For extending OAuth2 scope list. |
| `RestApiExtend\RestApiSecurityCheckEvent` | (see class) | — | For custom auth checks on REST calls. |
| `User\UserCreatedEvent` | `user.created` | `interface/usergroup/usergroup_admin.php:482` | New user row (post-INSERT). |
| `User\UserUpdatedEvent` | `user.updated` | `interface/usergroup/usergroup_admin.php:328` | Updated user row. |
| `User\UserEditRenderEvent` | `user.edit.render.before` / `.after` | `interface/usergroup/user_admin.php:301,648`; `usergroup_admin_add.php:245,547` | Render hooks in user-edit form. |
| **Auth login / logout** | — | **NOT FOUND** | No `LoginEvent` / `LogoutEvent` in `src/Events/**` (grep on `Login|Logout|Auth.*Event` returns only `PortalCredentialsUpdatedEvent` getter/setter — unrelated). Login is handled by `src/Common/Auth/*` + `interface/main/main_screen.php` with no event dispatch. **This is a documented gap for us.** |
| **User role changed** | — | **NOT FOUND** as a distinct event | ACL/role edits go through direct writes in `gacl/` + `library/acl.inc.php`; only `UserUpdatedEvent` broadly covers user changes. |
| `Core\ModuleLoadEvents::MODULES_LOADED` | (see class const) | `src/Core/ModulesApplication.php:163` | Fires once after all custom-module bootstraps run. Useful for cross-module coordination. |
| `Globals\GlobalsInitializedEvent` | (see const `EVENT_HANDLE`) | `library/globals.inc.php:4561` | Fires after `$GLOBALS`/`OEGlobalsBag` initialization. |
| `Services\ServiceSaveEvent` | `service.save.pre` / `service.save.post` | `src/Services/InsuranceService.php:269,310,360,407`; `QuestionnaireResponseService.php:493,531`; `SDOH/HistorySdohService.php:422,432,449,461`; `PatientTrackerService.php:277` | Generic pre/post-save hook for any service that extends `BaseService`. **Best generic write-side hook.** |
| `Services\ServiceDeleteEvent` | `service.delete.pre` / `service.delete.post` | (`src/Events/Services/ServiceDeleteEvent.php:24,26`) | Generic pre/post-delete hook. |
| `Core\SQLUpgradeEvent` | — | (fired during `sql_upgrade.php` cycle) | Extension hook for modules to react to core schema upgrades. |
| `Core\TwigEnvironmentEvent` | — | `src/Common/Twig/TwigContainer.php` | Handle to add Twig paths/extensions from a module. |

### 4.2 Gaps (relative to the request)

- **Encounter created / signed / closed** — no dedicated events. Nearest hooks: `EncounterMenuEvent`, `EncounterFormsListRenderEvent`, or `ServiceSaveEvent` from an encounter-related service that extends `BaseService`.
- **Auth login / logout** — no core event. Would require patching upstream (`src/Common/Auth/*`) and PR'ing back, or wrapping via a session-cleanup listener (`src/RestControllers/Subscriber/SessionCleanupListener.php`) for REST context.
- **Claim state transitions** — no core event; claim flow is module-owned by `oe-module-claimrev-connect`.
- **User role changed** — subsumed under `UserUpdatedEvent`; no dedicated role-change event.

---

## 5. Event dispatch site census

Method: `Get-ChildItem -Recurse -Include *.php` excluding `vendor/`, `node_modules/`, then `Select-String -Pattern '->dispatch\('` counting all matches.

**Total dispatch calls: 223** across the repo (includes non-Symfony dispatches — e.g. Laminas MVC controller `->dispatch()` in `controllers/C_*.class.php`, Rainforest webhook `Dispatcher->dispatch()` in `interface/webhooks/`, and REST route handler tests).

Top 12 files by dispatch count:

| # | File | Count | What it dispatches |
|---|---|---|---|
| 1 | `interface/patient_file/summary/demographics.php` | 22 | Patient summary card render events (`CardRenderEvent` per section: note, reminder, disclosure, amendment, lab, vital_sign, demographics, patient_photo, advance_directive, clinical_reminders, recall, appointment, track_anything), plus `SectionEvent`, `RenderEvent`. Highest-leverage extension surface for patient-facing UI. |
| 2 | `library/standard_tables_capture.inc.php` | 10 | `CodeTypeInstalledEvent::EVENT_INSTALLED_PRE` / `EVENT_INSTALLED_POST` around ICD10/RxNorm/SNOMED loaders. |
| 3 | `tests/Tests/Unit/PaymentProcessing/Rainforest/Webhooks/DispatcherTest.php` | 9 | Test-only. |
| 4 | `controllers/C_PracticeSettings.class.php` | 7 | Laminas MVC controller dispatch (not Symfony events). |
| 5 | `tests/Tests/Isolated/Release/DispatcherTest.php` | 6 | Test-only. |
| 6 | `src/Services/PatientService.php` | 6 | `BeforePatientCreatedEvent`, `PatientCreatedEvent`, `BeforePatientUpdatedEvent` (x2), `PatientUpdatedEvent` (x2). **Canonical write-side hook site for patient CRUD.** |
| 7 | `interface/main/calendar/*` (2 files) | 6 + 4 | Calendar-render + appointment events. |
| 8 | `tests/Tests/Unit/FHIR/SMART/ClientAdminControllerTest.php` | 6 | Test-only. |
| 9 | `src/RestControllers/*` (Route finders + friends) | 4 | `RestApiCreateEvent::EVENT_HANDLE` from Standard, FHIR, Portal finders. |
| 10 | `interface/forms/<various>` | 4 | Form-level render/save hooks. |
| 11 | `src/Services/InsuranceService.php` | 4 | `ServiceSaveEvent::EVENT_PRE_SAVE` / `EVENT_POST_SAVE`. |

Deducting test-only dispatches (rows 3, 5, 8 = 21 calls) leaves ~202 runtime dispatch sites. Real event-system leverage is concentrated in **`interface/patient_file/summary/demographics.php` (card/section rendering)**, **`src/Services/PatientService.php` (patient CRUD)**, and **REST finders**.

---

## 6. Bundled modules — reference implementations

### 6.1 `interface/modules/custom_modules/oe-module-*` (7 modules)

| Module | Demonstrates |
|---|---|
| `oe-module-claimrev-connect` | **Clearinghouse / claims integration.** Background-service scheduler flip in `enable`/`disable`; SFTP + REST clearinghouse client; ERA/EOB parsing; module-owned schema (`table.sql`); OpenEMR 7.x compat shims (`src/Compat/compat.php`). Reference for §2 above and for our claims/billing SaaS layer. |
| `oe-module-comlink-telehealth` | **Telehealth / video call**. Deep integration with legacy `portal/` too (has both `public/index.php` and `public/index-portal.php`, `oe-module-comlink-telehealth/public/index-portal.php:14`). Reference for portal-side extension. |
| `oe-module-dashboard-context` | **Dashboard widgets.** Cleanest small module — see `moduleConfig.php:45` for admin URL wiring and `src/Controller/ContextWidgetController.php:57,716` for module-URL resolution pattern. Reference for adding admin/config pages. |
| `oe-module-dorn` | **Lab-order integration (DORN).** Subscribes to `Services\DornLabEvent::GEN_BARCODE` and `SEND_ORDER` (`src/Events/Services/DornLabEvent.php:19-20`). Reference for domain-specific event pair. |
| `oe-module-ehi-exporter` | **EHI (Electronic Health Information) export bulk-flow.** Menu injection via constant `MODULE_INSTALLATION_PATH` pattern (`src/Bootstrap.php:34`). Reference for bulk-export SaaS features. |
| `oe-module-faxsms` | **Fax/SMS/Email/Voice messaging.** Cited by core as canonical example (`src/Events/Messaging/SendSmsEvent.php:25`). Subscribes to `SendSmsEvent`, `SendNotificationEvent`. Reference for multi-provider comms abstraction. |
| `oe-module-prior-authorizations` | **Prior-auth workflow.** Menu injection in `openemr.bootstrap.php:37,64` (adds two menu items directly in the bootstrap — pattern for quick nav additions). |
| `oe-module-weno` | **eRx (Weno EZ).** Largest module. Has `src/Bootstrap.php`, `scripts/`, extensive `templates/`, menu injection with three items (`src/Bootstrap.php:257,267,277`). Reference for large multi-page modules. |

### 6.2 `interface/modules/zend_modules/module/*` (14 modules — legacy Laminas MVC)

| Module | Demonstrates |
|---|---|
| `Acl` | ACL admin UI wired as Laminas module. |
| `Application` | Laminas app skeleton — shared config for all zend modules. |
| **`Carecoordination`** | **C-CDA (Consolidated CDA) import/export + Direct messaging + Referral network.** Highest-value zend module for interop. |
| `Ccr` | Continuity of Care Record (predecessor to C-CDA). Older format. |
| `CodeTypes` | External code-type admin. |
| `Documents` | Document category / template admin. |
| **`FHIR`** | Baseline FHIR admin (kept in zend for historical reasons; new FHIR work is under `src/RestControllers/FHIR/`). |
| `Immunization` | Immunization registry submission (HL7 v2.5.1). One of 5 seeded active zend modules. |
| **`Installer`** | **The Module Manager itself** — controller for install/enable/disable/register UI. Source for §1.3 lifecycle hook dispatch. `InstallerController::notifyModuleListener` at `src/Installer/Controller/InstallerController.php:267-311`. |
| `PatientFilter` | Filter-rule engine. |
| **`PatientFlowBoard`** | **Kanban-style patient flow / room-status board.** Reference for real-time UI within an encounter workflow. |
| `Patientvalidation` | Patient-record validation rules. |
| `PrescriptionTemplates` | Rx template CRUD. |
| `Syndromicsurveillance` | Public-health syndromic-surveillance submission (HL7 v2.5.1). |

Note: Phase 2 (`03-directory-map.md:25`) tags all `zend_modules/` as **[LEGACY] — extension surface, discouraged**. New SaaS modules must use `custom_modules/`.

---

## 7. Conclusion — "no-core-edit" policy

### 7.1 Sanctioned extension mechanisms ranked

| Mechanism | Upgrade-safe? | Best for | Evidence |
|---|---|---|---|
| Custom module + Symfony event listener (`oe-module-<name>/openemr.bootstrap.php` subscribing via `$eventDispatcher->addListener()`) | ✅ | 90 % of extensions — patient CRUD hooks, menu injection, service save/delete, CDA parse hooks, module UI screens, background services (`enable`/`disable`). | 7 in-tree modules; 79 event classes under `src/Events/**`; `src/Core/ModulesApplication.php:132-164` loads them; `src/Services/PatientService.php:189,201,268,291,323,354` are the write-side hooks. |
| Custom module + Composer install (`composer require openemr/oe-module-<name>` → plugin drops it under `interface/modules/custom_modules/`) | ✅ | Distribution of tenant-neutral modules. | `composer.json:205` allow-plugin; `composer.lock:6541-6587`; all 7 in-tree modules ship `"type": "openemr-module"`. |
| REST/FHIR endpoint via `RestApiCreateEvent` subscriber | ✅ | Any net-new REST or FHIR endpoint. Adds to route map at request time — no touch of `apis/routes/*` or `_rest_routes.inc.php`. | `src/Events/RestApiExtend/RestApiCreateEvent.php:10,50-71`; dispatched in `StandardRouteFinder.php:37`, `FhirRouteFinder.php:31`, `PortalRouteFinder.php:33`. |
| `sites/<tenant>/` overrides (`sqlconf.php`, `config.php`, `LBF/`, letterhead, statement templates, `documents/`) | ✅ | Per-tenant config, credentials, doc storage, LBF, letterhead. Never code. | `03-directory-map.md` §2.5; `sites/default/` inventory. |
| Twig `addPath` from a module (`OpenEMR\Common\Twig\TwigContainer::addPath()`) | ✅ (best-effort) | Module-owned Twig templates. **No `@namespace` convention exists** — modules append paths to the loader, and the first matching template wins. Order-dependent. Verify no upstream template name collides before using. | `src/Common/Twig/TwigContainer.php:55,59`; **no repo-wide `@openemr` / `@core` namespace found** (grep returns 0). |
| Laminas/Zend module under `interface/modules/zend_modules/module/<name>/` | ⚠️ Discouraged | Only when extending an already-Zend subsystem. | `03-directory-map.md:25`. |
| New encounter form under `interface/forms/<new>/` | ✅ (new subdir only) | Clinical-form additions. | `03-directory-map.md` §2.2 — verified 5-file skeleton. |
| Direct edit of `src/`, `library/`, `interface/`, `apis/`, `_rest_routes.inc.php`, `sql/`, `controllers/`, `templates/`, `custom/`, `config/`, `bin/`, `oauth2/`, `sphere/`, `meta/`, `ccdaservice/`, `ccr/`, `gacl/` | ❌ | Only when contributing a patch back upstream. | `03-directory-map.md` §3 last row. |
| Doctrine migrations under `db/Migrations/` | ⚠️ Not yet | Do not use. | `db/README.md:10-12`. |
| Adding files under `custom/` | ❌ (name is a trap) | Do not use. | `03-directory-map.md:40` — 20 upstream files already tracked here. |

### 7.2 Strict "no-core-edit" verdict

Every SaaS-layer feature MUST be implemented as follows:

| Requirement | Mechanism | Reference |
|---|---|---|
| New UI screen | Custom module controller renders a Twig or PHP template from the module's `templates/` dir. Web-callable via `interface/modules/custom_modules/oe-module-<name>/public/<page>.php` (path-gated by `mod_active`). | `src/Core/ModulesApplication.php:86-106` |
| New business logic | Service class in the module's PSR-4 namespace under `src/`. If it does DB writes, extend `OpenEMR\Services\BaseService` so `ServiceSaveEvent` fires automatically. | `src/Services/BaseService.php` (per Phase 2); `src/Events/Services/ServiceSaveEvent.php:24,30` |
| New REST/FHIR endpoint | Listener on `RestApiCreateEvent::EVENT_HANDLE` calling `$e->addToRouteMap()` / `addToFHIRRouteMap()` / `addToPortalRouteMap()` in the module's `Bootstrap::subscribeToEvents()`. Never edit `apis/routes/*.inc.php` or `_rest_routes.inc.php`. | `src/Events/RestApiExtend/RestApiCreateEvent.php:50-71` |
| New DB table | Prefix `custom_` or `mod_<module>_`. Ship as `sql/install.sql` in the module. Run via Module Manager's "Install SQL" button (backed by `install_sql` hook — `AbstractModuleActionListener.php:123-126`). Version upgrades: `sql/upgrade/<from>-to-<to>.sql`, applied via `upgrade_sql` hook (`:133-136`). | `AbstractModuleActionListener` |
| Change to existing core behavior | Symfony event subscriber. If no event exists at the needed injection point (§4.2 gaps: encounter created/signed/closed, login/logout, claim state, role change), the correct action is: **patch upstream `openemr/openemr` and PR back**. Do NOT fork-patch. Fork-patching guarantees a permanent merge burden and forfeits upgrade safety. |
| Global config | New keys in `OEGlobalsBag` layer only via `library/globals.inc.php` — **which is core**. So instead: DB-driven config in your module's own table, exposed via `moduleConfig.php`. Or per-site override in `sites/<tenant>/config.php` (site-scoped, editable). | `03-directory-map.md` §2.5 |

**Bright-line test for any PR touching this fork:** if the change lives outside `interface/modules/custom_modules/oe-module-<ours>/`, `interface/forms/<new-subdir>/`, `sites/<tenant>/`, or `docs/` — it is a core edit and requires upstream contribution + revert of the fork patch when upstream ships the same feature.

---

## Report Summary

- Produced: `docs/00-discovery/07-modules-and-extensibility.md`.
- **Module lifecycle** — `modules` DB table columns cited (`sql/database.sql:7786-7808`); boot loader scans DB (not filesystem) at `src/Core/ModulesApplication.php:132-164` and `include`s each `openemr.bootstrap.php` after injecting `$classLoader` and `$eventDispatcher` locals. Hooks are duck-typed methods on `ModuleManagerListener extends OpenEMR\Core\AbstractModuleActionListener` — `install`, `enable`, `disable`, `unregister`, `install_sql`, `upgrade_sql` (all `src/Core/AbstractModuleActionListener.php:83-136`), invoked by `interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php:267-311`.
- **Skeleton walkthrough** — `oe-module-claimrev-connect` fully mapped (10 top-level entries; 30-line `openemr.bootstrap.php`; 146-line `ModuleManagerListener` overriding `enable`/`disable`/`unregister`/`help_requested`). Distilled minimum-viable module skeleton at §2.8.
- **Composer installer plugin** — filters on `"type": "openemr-module"`; allow-listed at `composer.json:205`; version 0.1.5 in `composer.lock:6541-6587`; drops packages under `interface/modules/custom_modules/oe-module-<name>/`. Composer install alone is not activation — admin still runs Register → Install SQL → Enable through the Module Manager UI. Exact plugin class name is UNKNOWN (source out-of-tree).
- **Event catalog + dispatch census** — 79 event classes across 22 subject areas fully enumerated; 223 total `->dispatch(` sites, ~202 runtime after excluding tests; hottest single file is `interface/patient_file/summary/demographics.php` (22 dispatches — patient summary cards). `RestApiCreateEvent` cited as the sole safe path to add REST/FHIR endpoints.
- **No-core-edit verdict** — a strict-mode policy is fully feasible for CRUD/UI/REST/data-layer work; **gaps requiring upstream PRs**: no core events for encounter created/signed/closed, login/logout, claim state, or role change.

## UNKNOWNs

- **`openemr/oe-module-installer-plugin` internal class name and target-path resolution algorithm** — vendor dir is empty; plugin source is out-of-tree. All I can cite is registration (`composer.json:205`), lockfile pin (`composer.lock:6541-6587`), and downstream consequences (`.gitignore:13`, `phpstan.neon.dist:17`, `phpcs.xml.dist:68`, `rector.php:40`, `codecov.yml:3`). To confirm the copy-into-place logic, either `composer install` (out of scope — read-only) or product-owner grants access to the plugin's github repo.
- **Twig template-namespacing convention for custom modules** — none was found in-tree. `TwigContainer::addPath()` exists (`src/Common/Twig/TwigContainer.php:55,59`) but grep on `@openemr`/`@core` under `templates/` returns no hits. Whether modules are expected to namespace their Twig paths, and how collisions are resolved, is UNKNOWN — requires product-owner input or upstream doc.
- **Encounter created/signed/closed events, login/logout events, claim-state-transition events, user-role-changed events** — verified absent from `src/Events/**` and `src/Menu/`. If our SaaS layers need these hooks, the sanctioned path is an upstream PR adding them. Product-owner input needed on whether to open those PRs or take a temporary fork-patch.
- **Whether the 6 non-`claimrev-connect` in-tree `oe-module-*` are byte-identical to their upstream counterparts** — carried from Phase 2 UNKNOWN (`03-directory-map.md:182`). Blocks a clean fork-vs-upstream diff on the module code specifically.
