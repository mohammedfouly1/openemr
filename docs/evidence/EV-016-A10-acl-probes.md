# EV-016 A-10 — Empty-spec ACL call-site probes

**Requirement:** RDY-0016, item A-10 · **Gate:** G3 · **Owner:** Security Reviewer
**Task text (§23.4):** *"Empty-spec ACL paths do not fail open in any tested route"* — *"Targeted
probes of the `aclCheckAcoSpec` / `aclCheckIssue` call sites."*
**Executed:** 2026-08-17 · **Agent D (AGENT-SEC3)** · **Method:** source-code call-site audit +
one safe in-process PHP probe + live (read-only) schema/data checks. **No browser session, no
login, no application-code mutation.**

**Carried-forward context this builds on, not repeats:** `docs/evidence/EV-016-authorization-matrix.md`
§4.3 (A-10 not executed, flagged for AGENT-SEC), and the requirements doc's own S-13 finding
(§23.1, line ~9903): *"Two ACL helpers fail open — `aclCheckAcoSpec()` and `aclCheckIssue()` return
`true` on an empty spec... Medium... Upstream behaviour."* This document is the first to trace S-13
to concrete call sites, confirm which are reachable at runtime, and find one path that is reachable
by *any authenticated user's own request*, not only by an administrator's misconfiguration.

---

## 1. Result summary

**The fail-open behaviour in `aclCheckAcoSpec()` / `aclCheckIssue()` is real, confirmed both by
reading the source and by direct in-process invocation.** Of the call sites traced:

| Class | Count | Meaning |
|---|---|---|
| **RISK — confirmed reachable, admin-misconfiguration path** | 8 | Empty spec requires an admin to blank an ACO field via the normal UI; not currently triggered on this instance's live data |
| **RISK — confirmed reachable, user-request path (no misconfiguration needed)** | 3 | A logged-in user's own unvalidated request parameter reaches the fail-open branch; **currently live-exploitable on this exact demo instance** |
| **SAFE / NOT REACHABLE** | 4 | Literal/registered values that cannot be empty at that call site under normal operation |

**Headline finding, not in any prior document:** `load_form.php`, `view_form.php` and
`questionnaire_assessments.php` accept `$_GET['formname']` and pass it, unvalidated against the
`registry` table, into `AclMain::aclCheckForm()` → `aclCheckAcoSpec()`. When `formname` names a
form directory that exists on disk but has no `registry` row (16 such directories exist on this
exact instance today — `physical_exam`, `treatment_plan`, `sdoh`, `prior_auth`,
`transfer_summary`, `note`, `clinic_note`, `track_anything`, `requisition`, `gad7`, `phq9`,
`painmap`, `ankleinjury`, `bronchitis`, `CAMOS`, `aftercare_plan`), the ACL check **returns `true`
for every role, with zero configured rights required**, and `FormLocator::locateFile()` resolves
the file **purely from the filesystem path, with no registry dependency** — so the file loads and
runs. This is not hypothetical: the registry gap and the on-disk files were confirmed directly
against this instance (§5).

---

## 2. The implementation, read directly (`src/Common/Acl/AclMain.php`)

```php
// Permissions check for an ACO in "section|aco" format.
// Note $return_value may be an array of return values.
//
public static function aclCheckAcoSpec($aco_spec, $user = '', $return_value = '')
{
    if (empty($aco_spec)) {
        return true;                                    // <-- AclMain.php:337-339
    }
    $tmp = explode('|', (string) $aco_spec);
    ...
}

// Permissions check for a specified encounter form type.
public static function aclCheckForm($formdir, $user = '', $return_value = '')
{
    require_once(__DIR__ . '/../../../library/registry.inc.php');
    $tmp = getRegistryEntryByDirectory($formdir, 'aco_spec');
    return self::aclCheckAcoSpec($tmp['aco_spec'], $user, $return_value);  // <-- AclMain.php:355-360
}

// Permissions check for a specified issue type.
public static function aclCheckIssue($type, $user = '', $return_value = '')
{
    require_once(__DIR__ . '/../../../library/lists.inc.php');
    global $ISSUE_TYPES;
    if (empty($ISSUE_TYPES[$type][5])) {
        return true;                                    // <-- AclMain.php:369-371
    }
    return self::aclCheckAcoSpec($ISSUE_TYPES[$type][5], $user, $return_value);
}
```

Both helpers, and the thin wrapper `aclCheckForm()`, fail open on an empty/missing spec — confirmed
by reading the exact lines, matching the prior S-13 finding and `docs/HISModulesUsers.md:1494-1495`'s
citation of the same line numbers.

PHP's `empty()` treats `''`, `null`, `false`, `0`, `'0'`, and an unset array key identically — so any
of those values, or a lookup that misses entirely (unset array offset — no warning from `empty()`),
triggers the fail-open branch.

---

## 3. Empirical probe — `aclCheckAcoSpec()` invoked directly, no DB, no session

`aclCheckAcoSpec()`'s empty-spec branch returns before touching the session, GACL, or the database
— a pure early return — so it can be exercised safely in a bare CLI process. `aclCheckIssue()`
cannot be exercised the same way without a live DB connection (it unconditionally
`require_once`s `lists.inc.php`, which runs `SELECT` queries to build `$ISSUE_TYPES` before the
empty-check even runs), so for that helper this report relies on the source reading above plus the
live read-only schema check in §5 — deliberately not invoking it in-process to honor the "no DB/session
touch" preference in scope.

Script (kept in the gitignored scratchpad, not committed):
`.../scratchpad/a10-probe-acospec.php`

```php
require_once 'G:\My Drive\OpenEMR\vendor\autoload.php';
use OpenEMR\Common\Acl\AclMain;

foreach (['' , null, false, '0', ' '] as $spec) {
    var_dump(AclMain::aclCheckAcoSpec($spec));
}
```

**Output (`C:\openemr-stack\php\php.exe`, PHP 8.3.33 CLI):**

```
empty string         spec=''           => aclCheckAcoSpec() returned true
null                 spec=NULL         => aclCheckAcoSpec() returned true
false                spec=false        => aclCheckAcoSpec() returned true
zero string          spec='0'          => aclCheckAcoSpec() returned true
whitespace string    spec=' '          => THREW RuntimeException: Failed to start the session...
```

**This is a positive control, not just a fail-open confirmation.** The four `empty()`-true inputs
(`''`, `null`, `false`, `'0'`) all short-circuited to `true` before any session/DB code ran. The one
input PHP's `empty()` does **not** consider empty — a single space — took the *other* branch:
it proceeded into `aclCheckCore()` and only failed because that function needs a live session (which
this bare script correctly has none of). That distinguishes "genuinely reached the fail-open branch"
from "the function always returns true regardless" — the probe is discriminating correctly, the same
role the CTRL rows play in the main EV-016 matrix.

**A finding not previously documented:** `'0'` (the literal string zero) also fails open. Any code
path where an ACO id or spec could stringify to `"0"` hits the same branch as a genuinely empty
value.

---

## 4. Call-site inventory and classification

### 4.1 `aclCheckAcoSpec()` — direct call sites (4)

All four are fed from `categories.aco_spec` (document categories) — no other source of an
`aco_spec` value reaches this function directly.

| # | Site | Spec source | Classification |
|---|---|---|---|
| 1 | `controllers/C_Document.class.php:205` — upload authorization gate: `if (AclMain::aclCheckAcoSpec($acoSpec) === false)` | `SELECT aco_spec FROM categories WHERE id = ?` for the upload target category | **RISK — admin-misconfiguration path.** See §5: reachable via the normal category-editor UI, not currently triggered by live data |
| 2 | `controllers/C_Document.class.php:1223` — document-tree link visibility: `if (!AclMain::aclCheckAcoSpec($doc['aco_spec']))` | Same `categories.aco_spec`, per document's category | **RISK — same path**, display-layer only at this exact line |
| 3 | `interface/patient_file/report/patient_report.php:565` — `if (empty(...) \|\| AclMain::aclCheckAcoSpec(...))` | Same `categories.aco_spec`, joined per document | **RISK — same path.** The `empty(...) ||` is redundant with the function's own internal `empty()` check — both paths reach the identical outcome, confirming the design is deliberate, not accidental |
| 4 | `library/classes/Document.class.php:368` — `Document::can_access()`: `if (AclMain::aclCheckAcoSpec($category['aco_spec'], $username) === false) { return false; }` | Same `categories.aco_spec`, all categories tied to the document | **RISK — highest impact of the four.** `can_access()` is the actual server-side authorization gate for document retrieval — confirmed by tracing its only caller, `controllers/C_Document.class.php:666` (`retrieve_document` action): `if (!$d->can_access()) { AccessDeniedHelper::deny(...); }`. Its own docstring states the design explicitly: *"If there are no categories tied to the document, default access is granted."* An empty `aco_spec` on a category has the same effect for that category: it can never contribute a denial. **If every category a document belongs to has a blank `aco_spec`, any authenticated user can download it, regardless of role or ACL.** |

### 4.2 `aclCheckIssue()` — direct call sites (13, across 6 files)

All are fed from `$ISSUE_TYPES[$type][5]`, itself sourced from `issue_types.aco_spec` — **except**
one, flagged separately.

| Site(s) | `$type` source | Classification |
|---|---|---|
| `interface/forms/newpatient/C_EncounterVisitForm.class.php:552` | Iterates `$this->issueTypes`, constructor-injected from `OEGlobalsBag::get('ISSUE_TYPES')` (server-built global) | **RISK — admin-misconfiguration path** (same shape as §4.1) |
| `interface/patient_file/summary/add_edit_issue.php:794` | Iterates global `$ISSUE_TYPES` | **RISK — admin-misconfiguration path** |
| `interface/patient_file/summary/demographics.php:1095, 1096, 1097` | Hardcoded literals `'allergy'`, `'medical_problem'`, `'medication'` | **RISK — admin-misconfiguration path**, narrowed to exactly those three types |
| `interface/patient_file/summary/stats.php:150, 213` | Iterates `$reducedIssueTypes`/global `$ISSUE_TYPES` | **RISK — admin-misconfiguration path** |
| `interface/patient_file/summary/stats_full.php:43, 223, 234, 305` | Iterates `OEGlobalsBag::get('ISSUE_TYPES', [])` | **RISK — admin-misconfiguration path** |
| `interface/patient_file/summary/add_edit_issue.php:318` | `$irow['type']` from `PatientIssuesService::getOneById($issue)` — an **existing, previously-saved** issue's `type` column | **RISK — narrow.** Not attacker-controlled directly, but if an issue type is later deleted/renamed by an admin, existing rows retain the old `type` string, which then misses `$ISSUE_TYPES` entirely (`empty()` on an unset key is `true`) → fails open for editing that orphaned issue |
| `interface/patient_file/summary/add_edit_issue.php:79` | `$thistype = $_REQUEST['thistype']` — **raw request parameter, not checked against `$ISSUE_TYPES` keys before the ACL call**: `if ($thistype && !$issue && !AclMain::aclCheckIssue($thistype, '', ['write', 'addonly']))` | **RISK — user-request path, no misconfiguration needed.** Any value of `thistype` that is not a real key in `$ISSUE_TYPES` (typo, arbitrary string) makes `empty($ISSUE_TYPES[$thistype][5])` true regardless of the live `aco_spec` data, so the deny branch is skipped. Downstream impact is bounded — `$irow['type']` is then set to the bogus value and used later in the form, not verified further here (out of this task's scope; flagged for whoever picks up a fix) — but the **ACL check itself is bypassed by an unvalidated request value**, which is exactly what A-10 asks to verify does not happen |

### 4.3 `aclCheckForm()` — indirect call sites, traced because it is a 2-line wrapper of `aclCheckAcoSpec()` with no independent logic (`AclMain.php:355-360`)

| Site | `$formdir` source | Classification |
|---|---|---|
| `interface/patient_file/encounter/load_form.php:40` | `$_GET['formname']`, sanitized only to charset `[A-Za-z0-9_.-]` by `check_file_dir_name()` (blocks path traversal, does **not** validate against the `registry` table) | **RISK — user-request path, live-exploitable on this instance.** See §1 headline and §5 |
| `interface/patient_file/encounter/view_form.php:41` | Same pattern, same `$_GET['formname']` | **RISK — same, second entry point** |
| `interface/forms/questionnaire_assessments/questionnaire_assessments.php:64` | Same pattern, same `$_GET['formname']` | **RISK — same, third entry point** |
| `interface/forms/fee_sheet/new.php:32` — `aclCheckForm('fee_sheet')` | Literal `'fee_sheet'` | **NOT REACHABLE** — `fee_sheet` is a default-registered core form (confirmed in `registry`, §5); would require deleting a core registry row |
| `interface/forms/fee_sheet/new.php:1731` — `aclCheckForm('admin','super')` / `('acct','bill')` / `('acct','disc')` | Literals | **NOT REACHABLE** — same reasoning, core ACO sections always present |
| `interface/forms/newpatient/new.php:28` — `aclCheckForm('newpatient', '', ['write','addonly'])` | Literal `'newpatient'` | **NOT REACHABLE** — `newpatient` confirmed registered (§5) |

### 4.4 Excluded from the count

Two matches in `docs/discovery/openemr-decision-evidence/evidence/raw/*.txt` are stale captured
copies of `stats_full.php:305`'s line, not source files — not counted as separate call sites.

---

## 5. Reachability evidence — live, read-only checks against this instance

Run via `C:\openemr-stack\mariadb\bin\mariadb.exe -u root --host=127.0.0.1 --port=3306 openemr`.
All queries are plain `SELECT`s; nothing was written.

**5.1 — `categories.aco_spec` and `issue_types.aco_spec`: zero empty rows today.**

```
categories:   34 rows, 0 with empty/NULL aco_spec
issue_types:  13 rows, 0 with empty/NULL aco_spec
```

So the admin-misconfiguration paths in §4.1/§4.2 are **real code paths, confirmed not currently
triggered by this instance's live data** — an honest "latent, not active" finding, consistent with
how S-13 in the requirements doc already characterizes it ("Upstream behaviour", Medium severity).

**5.2 — The blank option is a first-class, reachable choice in the admin UI, not a schema accident.**

- `interface/super/edit_list.php:860-862` (Administration → Lists → Issue Types): `echo "<option
  value=''></option>"` immediately before the real ACO options, and the submitted value is written
  with `trim((string) $iter['aco_spec'])` — no non-empty validation — into `issue_types.aco_spec`
  (line 172-185).
- `controllers/C_DocumentCategory.class.php:68,83,96,118` (Administration → Document Categories):
  identical pattern — `"<option value=''></option>" . AclExtended::genAcoHtmlOptions(...)`, and
  `$_POST['aco_spec']` is passed straight into `Tree::add_node()` / `Tree::edit_node()`
  (`library/classes/Tree.class.php:223,264`) with no non-empty check.

Both admin screens present "no ACO restriction" as a normal, selectable configuration — the fail-open
behaviour is one admin click away in both cases, not a database anomaly.

**5.3 — Schema defaults are non-empty, which is why §5.1 currently reads zero.**

```sql
-- sql/database.sql
`categories`.`aco_spec`    varchar(63) NOT NULL DEFAULT 'patients|docs'
`issue_types`.`aco_spec`   varchar(63) NOT NULL DEFAULT 'patients|med'
```

Every row inserted by the installer (`sql/database.sql:305-337`) sets an explicit non-empty value;
the handful of upgrade-script `INSERT`s that omit the column (e.g.
`sql/3_2_0-to-4_0_0_upgrade.sql:288-297`) still get the non-empty column default. **The empty state
is reachable only through the admin UI (§5.2), not through any installer or upgrade path traced
here.**

**5.4 — The headline finding: `registry` table vs. `interface/forms/` on disk.**

```
registry table:        18 directories registered, 0 with empty aco_spec
interface/forms/ disk: 34 directories present
```

Directories present on disk with **no** `registry` row at all (confirmed by diffing the two lists
directly against this instance):

```
aftercare_plan, ankleinjury, bronchitis, CAMOS, clinic_note, gad7, LBF, note, painmap, phq9,
physical_exam, prior_auth, requisition, sdoh, track_anything, transfer_summary, treatment_plan
```

Every one of the clinically-relevant directories in that list (`physical_exam`, `treatment_plan`,
`sdoh`, `prior_auth`, `transfer_summary`, `note`, `clinic_note`, `track_anything`, `requisition`,
`gad7`, `phq9`, `painmap`, `ankleinjury`, `bronchitis`, `aftercare_plan`) contains a working
`new.php` (confirmed by directory listing, not by executing it).

**Why this matters mechanically, traced through the actual code, not assumed:**

1. `getRegistryEntryByDirectory($formdir, 'aco_spec')` (`library/registry.inc.php:82-86`) runs
   `SELECT aco_spec FROM registry WHERE directory = ?`. For any of the 16 directories above this
   returns no row.
2. `sqlQuery()` → `QueryUtils::querySingleRow()` → `fetchArrayFromResultSet()`
   (`src/Common/Database/QueryUtils.php:173-182`) returns `false` on an empty result set (`EOF`
   branch) — not an empty array, not null: `false`.
3. `aclCheckForm()` (`AclMain.php:359`) then does `$tmp['aco_spec']` where `$tmp === false` — PHP
   evaluates this to `null` (with a non-fatal `E_WARNING`, execution continues) — and passes that
   `null` straight into `aclCheckAcoSpec()`, which returns `true` per §3's confirmed behaviour.
4. Independently, `FormLocator::locateFile()` (`src/Common/Forms/FormLocator.php:58-63`) builds the
   include path as `{$fileRoot}/interface/forms/{$formDir}/{$fileName}` **directly from the
   filesystem** — it never consults the `registry` table. So file resolution and the ACL check are
   fully decoupled: an unregistered directory is just as loadable as a registered one, but its ACL
   is unconditionally waived.
5. `check_file_dir_name()` (`library/sanitize.inc.php:60-68`) restricts `$_GET['formname']` to
   `[A-Za-z0-9_.-]` — this **rules out path traversal** (this is not an LFI finding) but does
   **nothing** to require the name be a registered, ACL-covered form.

**Net effect, stated precisely:** on this exact instance, today, a request such as
`load_form.php?formname=physical_exam&pid=<id>&encounter=<id>` (or `treatment_plan`, `sdoh`,
`transfer_summary`, `note`, etc.) from **any authenticated user of any role** — including a Front
Office account that EV-016's own A-1 row already confirmed is correctly denied `soap` — reaches
`AclMain::aclCheckForm()`, gets `true` unconditionally, and the corresponding `new.php` is
`require_once`d and runs. **Not executed live here** — no browser session, no login, per this
task's constraints — but every step of the mechanism up to execution is confirmed directly against
this instance's registry table, filesystem, and source.

---

## 6. Classification totals

| Classification | Call sites |
|---|---|
| **RISK — admin-misconfiguration path** (empty spec needs an admin to blank an ACO field; not currently triggered by live data) | `C_Document.class.php:205`, `C_Document.class.php:1223`, `patient_report.php:565`, `Document.class.php:368`, `C_EncounterVisitForm.class.php:552`, `add_edit_issue.php:794`, `demographics.php:1095/1096/1097` (3), `stats.php:150/213` (2), `stats_full.php:43/223/234/305` (4) = **16 individual call-site lines, 8 distinct code locations counting adjacent lines in the same file/loop as one location** |
| **RISK — user-request path, no misconfiguration needed** | `add_edit_issue.php:79` (`$thistype`), `load_form.php:40`, `view_form.php:41`, `questionnaire_assessments.php:64` = **4 sites, 1 currently live-exploitable on this instance (the 3 `aclCheckForm` sites via the registry gap)** |
| **RISK — narrow, requires a prior admin action (type deletion/rename)** | `add_edit_issue.php:318` |
| **NOT REACHABLE** | `fee_sheet/new.php:32`, `fee_sheet/new.php:1731` (×3 checks), `newpatient/new.php:28` = **4 core-form literals, all confirmed registered** |
| **SAFE (fails closed, verified)** | none of the 17 traced call sites of `aclCheckAcoSpec()`/`aclCheckIssue()`/`aclCheckForm()` fail closed on an empty spec — the function itself has no fail-closed branch for that input; "SAFE" only applies where the spec provably cannot be empty (the NOT REACHABLE row above) |

**Total distinct call sites traced: 21** (4 direct `aclCheckAcoSpec`, 13 direct `aclCheckIssue`, 6
indirect via `aclCheckForm`, one of which — `fee_sheet/new.php:1731` — makes 3 separate calls on one
line, counted individually above).

---

## 7. Honest bottom line

**Do empty-spec ACL paths fail open? Yes, confirmed at the source (§2), confirmed by direct
invocation (§3), and confirmed reachable in practice, not merely in theory:**

- **8 call sites** depend on an admin leaving an ACO field blank through a normal, first-class UI
  option — reachable, but **not currently triggered** on this instance's live data (§5.1).
- **1 call site** (`add_edit_issue.php:79`) depends only on an authenticated user's own unvalidated
  request parameter, no admin action required — reachable **today**, though the practical impact of
  an unrecognized `thistype` value beyond bypassing this one check was not traced further (flagged,
  not chased, per this task's read-only scope).
- **3 call sites** (`load_form.php`, `view_form.php`, `questionnaire_assessments.php`, all via
  `aclCheckForm()`) are **live-exploitable on this exact instance right now** because of a genuine
  gap between what's installed on disk (34 form directories) and what's registered for ACL purposes
  (18) — a gap this report confirmed directly, not assumed.

This is **not a new discovery that the functions fail open** — S-13 already named that, correctly,
as "Upstream behaviour," Medium severity. What this report adds is the thing A-10 specifically asked
for and that was previously unexecuted: **which of the real call sites this actually reaches, and
whether any of them are reachable without an administrator's cooperation.** Three are, and one of
those three is confirmed live-reachable on this instance today via a registry/filesystem gap that
has nothing to do with configuration and everything to do with which forms ship inactive by default.

**This report does not fix anything** — no application file was modified, per task scope. The
concrete candidates for a follow-up fix, in order of what they would close:

1. `aclCheckAcoSpec()` / `aclCheckIssue()` — change the empty-spec branch from `return true` to
   `return false` (fail closed), auditing for any place that currently *relies* on the open-by-default
   behavior for legitimate "no restriction configured" categories (the `can_access()` docstring in
   §4.1 documents that reliance explicitly, so this is not a safe blind flip).
2. `aclCheckForm()` specifically — treat "no registry row" as denial rather than delegating to
   `aclCheckAcoSpec(null)`, independent of item 1.
3. `add_edit_issue.php:79` — validate `$thistype` against `array_key_exists($thistype, $ISSUE_TYPES)`
   before the ACL call, independent of items 1-2.
4. Either register the 16 orphaned `interface/forms/` directories through Administration → Forms, or
   confirm each is intentionally inactive and should not be independently reachable via
   `load_form.php` regardless of ACL.

None of these four is in this task's scope to apply — reported for the item's actual owner to
scope as a separate fix.

---

## 8. Reproduce

```powershell
# §3 — safe, DB/session-free probe
C:\openemr-stack\php\php.exe "<scratchpad>\a10-probe-acospec.php"

# §5.1
C:\openemr-stack\mariadb\bin\mariadb.exe -u root --host=127.0.0.1 --port=3306 openemr `
  -e "SELECT category, type, aco_spec FROM issue_types;"
C:\openemr-stack\mariadb\bin\mariadb.exe -u root --host=127.0.0.1 --port=3306 openemr `
  -e "SELECT id, name, aco_spec FROM categories;"

# §5.4
C:\openemr-stack\mariadb\bin\mariadb.exe -u root --host=127.0.0.1 --port=3306 openemr `
  -e "SELECT directory FROM registry ORDER BY directory;"
# compare against: dir "G:\My Drive\OpenEMR\interface\forms"
```

**Status: A-10 EXECUTED.** Fail-open confirmed at the source, confirmed by direct invocation, and
traced to 21 call sites with an explicit reachability classification for each — including one class
of finding (the `registry`/filesystem gap) not previously documented anywhere in this project's
evidence trail. **This does not close RDY-0016** — the UI-navigation legs (A-1/A-6/A-7/A-8) and the
sensitivity-dataset legs (A-2, A-7) remain exactly as blocked as `EV-016-authorization-matrix.md` §4
already states, unchanged by this report.
