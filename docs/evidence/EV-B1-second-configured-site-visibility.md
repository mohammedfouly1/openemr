# EV-B1 — A second configured site was invisible to the rename tooling

**Finding ID:** B1
**Branch:** `feat/thiqa-branding-foundation`
**Date:** 2026-08-25
**Status:** closed for *visibility*. The second tenant itself is untouched and remains an
Owner decision.

---

## 1. The finding

`sites/rdy0082restore/` is a complete, configured OpenEMR tenant:

- `sites/rdy0082restore/sqlconf.php` sets `$config = 1` and `$dbase = 'openemr_rdy0082_restore'`.
- It is gitignored (`.gitignore:131`), so it never appears in a diff, a `git status`, or a
  repo-wide grep.
- It serves HTTP 200 at
  `http://localhost:8300/interface/login/login.php?site=rdy0082restore`.
- Its database carries `openemr_name='Thiqa'`, `saas_branding_product_name_ar='ثقة'`, and
  its own `modules` row: `mod_id=6, mod_name='Thiqa Branding',
  mod_directory='oe-module-thiqa-branding', mod_active=1`.

`interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/SiteOption.php`
makes `--site` mandatory with deliberately no default, and **nothing anywhere enumerated
sites**. A branding or rename run against `--site=default` therefore exited `0` having
silently left a second, fully branded instance untouched.

The consequence is not cosmetic. `docs/PRE-SKYEAGLE-CONTINUATION-CHECKPOINT.md` §9 names
`mod_directory` the highest-severity rename hazard, because
`ModulesApplication.php:141-161` runs `UPDATE modules SET mod_active = 0` when the module
path stops resolving. Its persisted-state model records `modules` as **1 row**. There are
two such rows, in two databases, and only one of them was ever in view.

---

## 2. What was built

Visibility, not a fallback. `--site` still has no default and must not acquire one; see
`SiteOption`'s docblock for why that decision stands.

| File | Role |
|---|---|
| `…/oe-module-thiqa-branding/src/Tenant/SiteInventory.php` | Scans `sites/` for directories whose `sqlconf.php` establishes `$config = 1`. Filesystem only. |
| `…/oe-module-thiqa-branding/src/Tenant/SiteInventoryReport.php` | The scan result: configured site ids, a count of configured tenants whose directory name is outside `SiteId`'s character set, and whether the scan was readable at all. |
| `…/oe-module-thiqa-branding/src/Console/SiteScopeNotice.php` | Renders the notice: a loud warning block naming every tenant not acted on, or one quiet line on a single-tenant installation. |
| `…/oe-module-thiqa-branding/src/Console/TenantScopedBrandingCommand.php` | Abstract base for every branding command taking `--site`. `execute()` is `final` and renders the notice in a `finally`. |
| `…/oe-module-thiqa-branding/src/Console/{ApplyProfile,Materialise,Verify}Command.php` | Now extend that base; their `execute()` became `executeForSite()`. |
| `…/oe-module-thiqa-branding/src/Bootstrap.php` | Constructs one `SiteScopeNotice` and injects it into the three commands. |

### Design decisions worth recording

**No database connection, and `sqlconf.php` is parsed rather than included.** Including it
would define `$host`, `$login`, `$pass` and `$dbase` into the caller's scope and execute
whatever else the file contained, for every site, on every branding command. The parse
keeps exactly one integer. A tenant whose database is down still appears in the inventory —
that is the one most likely to be forgotten.

**The tokenizer, not a regular expression.** `token_get_all()` distinguishes a live
`$config = 0;` from a commented-out `// $config = 1;` above it. A line-oriented match fails
in the dangerous direction: it would report an abandoned site as configured, or, with the
operands reversed, hide a live one. The *last* assignment wins, as PHP itself would do.

**"Unreadable" is a distinct state, not an empty list.** If `sites/` cannot be enumerated,
the notice says the tenant population is unknown, loudly. Printing the reassuring
single-tenant line there would be the original defect wearing a friendly face.

**Site ids are printed; unsupported directory names are only counted.** `SiteOption`
declines to echo a rejected operator-supplied string into an error that may reach a shared
log. This notice prints names the process read off its own local filesystem, already
validated by `SiteId`, to the terminal of an operator who can list `sites/` themselves —
withholding them would make the notice useless. A directory name that failed `SiteId`
validation is arbitrary bytes, so it is counted and never printed; a name carrying ANSI
escapes would rewrite the very warning reporting it. A dotted site id is the real case
(RB-05), and dropping it silently would be the same invisibility bug in a new place.

**The notice is a required constructor argument and the `finally` is not optional.**
Nullable would mean a command wired without a notice prints nothing and looks entirely
healthy — invisible, exactly like the tenant it failed to mention. A missing wiring is
instead a `TypeError` at construction, before any tenant is touched. `execute()` is `final`
so no subclass can add a return path that skips the notice; the notice renders on every
exit code and on an exception on its way out.

**Exit codes are untouched on every path.** A tenant left unbranded is a decision for a
human, not a probe result.

---

## 3. Live verification against the real installation

Read-only probe, run on the native Windows stack with
`C:\openemr-stack\php\php.exe`, pointing `SiteInventory` at the real `G:\My Drive\OpenEMR\sites`:

```
readable            : yes
configured sites    : default, rdy0082restore
unsupported names   : 0
single tenant?      : no
------------------------------------------------------------------------------
What an operator running a branding command with --site=default now sees:

 [WARNING] This command acted on ONE tenant. Other configured tenants were NOT touched.

           1 other configured tenant on this installation:
             - rdy0082restore

           Branding commands act on exactly one tenant and have no default tenant. Rerun
           with --site for each tenant that must carry the same branding, or record the
           decision to leave it as it is.
```

`openemr_rdy0082_restore` is present in that site's `sqlconf.php` and does not appear in
the output; neither does the password or host.

---

## 4. Tests

```
C:\openemr-stack\php\php.exe vendor\bin\phpunit -c phpunit-isolated.xml --no-coverage ^
    tests\Tests\Isolated\Modules\ThiqaBranding\Tenant\
→ OK (38 tests, 53 assertions)   exit 0

C:\openemr-stack\php\php.exe vendor\bin\phpunit -c phpunit-isolated.xml --no-coverage ^
    tests\Tests\Isolated\Modules\ThiqaBranding\Console\
→ OK (125 tests, 1598 assertions)   exit 0
```

New coverage:

- `tests/Tests/Isolated/Modules/ThiqaBranding/Tenant/SiteInventoryTest.php` (15 tests) —
  two configured sites found; the acting site excluded from the others; an acting site
  outside the inventory hiding nothing; `$config = 0` not counted; a directory with no
  `sqlconf.php` not counted; a plain file in `sites/` ignored; a commented-out assignment
  not counted; last assignment wins; `'1'` counts; an unevaluatable assignment and a `==`
  comparison do not; an unsupported directory name counted rather than dropped; an absent
  sites directory reported as unknown rather than empty; the source containing no database
  symbol; no `sqlconf` variable defined into any scope.
- `tests/Tests/Isolated/Modules/ThiqaBranding/Console/SiteScopeNoticeTest.php` (9 tests) —
  the warning names every untouched tenant, excludes the acting one, pluralises, states the
  remedy without offering a default; the single-tenant case is one quiet line with no
  warning; an uninstalled second directory does not trigger it; an unreadable sites
  directory is loud; an unsupported name is reported but never printed; no credential from
  the fixture `sqlconf.php` reaches the output.
- `tests/Tests/Isolated/Modules/ThiqaBranding/Console/TenantScopedBrandingCommandTest.php`
  (12 tests) — the notice renders for exit codes 0, 1, 2 and 17 and when the subclass
  throws; it never changes the exit code; without `--site` the subclass never runs and no
  notice prints; `--site` still has no default; all three real commands extend the base;
  the notice constructor parameter is non-nullable; `execute()` is `final`.

---

## 5. Negative control

Both guards were deliberately broken, observed failing, and restored byte-identically.

Baseline SHA-256:

```
f6bb3c4fbc0761e4b6dfb88dd3d262af3d1c2576af05f40046e71410a52e48fe  src/Tenant/SiteInventory.php
c5e96441ce6e5ed9ec1764090d7accc0576a572698fc18109ce9c6276629c60e  src/Console/TenantScopedBrandingCommand.php
```

**Control A — the `$config = 1` test.** `private const INSTALLED = 1;` → `= 0;`.

```
Tests: 15, Assertions: 25, Failures: 10.
```

Failures included `testASiteWithConfigZeroIsNotConfigured`,
`testACommentedOutAssignmentDoesNotCount`, `testTheLastAssignmentWins`,
`testAQuotedOneStillCounts` and
`testAConfiguredSiteWithAnUnsupportedNameIsCountedNotDropped` — i.e. the guard is what
those tests are actually holding.

Restored; SHA-256 `f6bb3c4f…` matched the baseline; re-ran:
`OK (15 tests, 28 assertions)`, exit 0.

**Control B — the unskippable-notice guarantee.** The `try { … } finally { …render… }`
in `TenantScopedBrandingCommand::execute()` replaced with a plain return.

```
Tests: 12, Assertions: 29, Failures: 5.   (phpunit exit 1)
```

All four exit-code data sets and the throwing case failed with
`Failed asserting that ' spy ran\r\n' contains "rdy0082restore"` — the notice had vanished
from every path, which is precisely the regression the `finally` prevents.

Restored; SHA-256 `c5e96441…` matched the baseline; re-ran the full Console directory:
`OK (125 tests, 1598 assertions)`, exit 0.

---

## 6. Deliberately not done

- **`sites/rdy0082restore/` and its database are untouched.** Whether that instance is
  retired is an Owner decision that has not been taken. This work makes it visible and
  nothing more. No database was written; the only database-adjacent read was of
  `sqlconf.php` as text.
- **`--site` still has no default,** and nothing added here supplies a fallback tenant.
- **`thiqa-branding:backup`, `thiqa-branding:seed-demo` and
  `thiqa-branding:provision-report-acl` do not carry the notice.** They declare no `--site`
  option and act on whichever tenant `bin/console` was bootstrapped against, so the same
  hazard applies to them by a different route. Extending the notice there means giving them
  a tenant identity they do not currently have, which is a larger change than this finding
  called for — and all three were being edited concurrently by another agent. Recorded as an
  open gap, not fixed.
- **The `sites/` path is still supplied by `Bootstrap` from `getProjectDir()`.** No
  configuration knob was added for it; a second source of truth for where tenants live is
  the last thing this finding wants.
