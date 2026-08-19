# EV-016 A-10 — Fix scope for the ACL fail-open finding

**Builds on:** `docs/evidence/EV-016-A10-acl-probes.md` (the finding — 21 call sites traced, 2 confirmed
live-exploitable on this instance today). **This document does not implement anything.** It scopes
what should be fixed, in what order, and what still needs your decision before any code changes.

---

## 1. The two confirmed live-exploitable paths — recommend fixing both now

These are the only findings in `EV-016-A10-acl-probes.md` that require **no admin misconfiguration**
— a logged-in user's own request reaches the fail-open branch directly.

### 1a. `aclCheckForm()` — the registry/filesystem gap (3 call sites: `load_form.php`, `view_form.php`,
`questionnaire_assessments.php`)

**Root cause**: `aclCheckForm()` looks up a form directory in the `registry` table and, if there's no
row, passes `null` straight into `aclCheckAcoSpec()`, which fails open. Separately,
`FormLocator::locateFile()` resolves the file purely from the filesystem, with no dependency on the
registry — so an unregistered directory is exactly as loadable as a registered one, but its ACL check
is unconditionally waived.

**Recommended fix**: change `aclCheckForm()` itself to treat "no registry row" as **denial**, not as
"defer to `aclCheckAcoSpec(null)`." Concretely:

```php
public static function aclCheckForm($formdir, $user = '', $return_value = '')
{
    require_once(__DIR__ . '/../../../library/registry.inc.php');
    $tmp = getRegistryEntryByDirectory($formdir, 'aco_spec');
    if ($tmp === false || $tmp === null) {
        return false;   // no registry row at all -- deny, don't defer to the fail-open helper
    }
    return self::aclCheckAcoSpec($tmp['aco_spec'], $user, $return_value);
}
```

**Blast radius**: narrow and specific to this one function. Does **not** touch
`aclCheckAcoSpec()`/`aclCheckIssue()` themselves, so every other call site the probe report traced is
unaffected. The only behavior change: a form directory with **no** registry row now denies instead of
allowing. Per §5.4 of the probe report, the 3 confirmed-registered core forms (`fee_sheet`,
`newpatient`, and the two literal `aclCheckForm('admin','super')`/`('acct','disc')` calls) are
unaffected, since they have real registry rows.

> **CORRECTION (2026-08-19, PR-19 implementation pass):** the claim above about the two literal calls
> is wrong. `SELECT directory, aco_spec FROM registry WHERE directory IN ('admin','acct')` returns
> **zero rows** on this instance — `fee_sheet/new.php:1731`'s
> `AclMain::aclCheckForm('admin', 'super') || AclMain::aclCheckForm('acct', 'bill') ||
> AclMain::aclCheckForm('acct', 'disc')` is not a registered-form lookup at all; it appears to be
> upstream code that meant to call `aclCheckAcoSpec()` with an ACO-spec string but calls
> `aclCheckForm()` (a directory lookup) instead — `stock upstream (be636987b7, 2026-03-09), not
> introduced by this project`. Before the fix, this made the block **fail open unconditionally**
> (any true value from any of the three always-missing-registry-row calls); after the fix, it
> **denies unconditionally**. **Verified currently inert either way**: it is additionally gated by
> `OEGlobalsBag::get('ippf_specific')`, which has **zero rows in `globals`** on this instance (off by
> default, `library/globals.inc.php:4450`'s own `!empty(...)` guard), so the block does not render
> before or after this fix. Flagged because the fix-scope's own "unaffected, since they have real
> registry rows" claim did not hold up under direct verification — the block is unaffected in practice
> today, but for a different reason (dead code) than originally stated. See PR-19 in
> `docs/branding/adr/patch-records.md` for the implementation.

**One thing this fix does NOT resolve on its own**: the 16 directories that exist on disk with no
registry row (`physical_exam`, `treatment_plan`, `sdoh`, `prior_auth`, `transfer_summary`, `note`,
`clinic_note`, `track_anything`, `requisition`, `gad7`, `phq9`, `painmap`, `ankleinjury`,
`bronchitis`, `CAMOS`, `aftercare_plan`, `LBF`) would, after this fix, correctly **deny everyone**,
including administrators — not "correctly ACL-gated," just uniformly blocked. That's the safe default,
but it may make some of those forms unusable where they were previously (unintentionally) reachable.
**This is exactly why §3 below needs your input** — some of these may be forms you want active and
properly registered, not just blocked.

### 1b. `add_edit_issue.php:79` — unvalidated `$thistype`

**Root cause**: `$thistype = $_REQUEST['thistype']` is passed to `aclCheckIssue($thistype, ...)`
without first checking it's a real key in `$ISSUE_TYPES`. Any value that isn't a real issue type makes
`empty($ISSUE_TYPES[$thistype][5])` true (unset array key), which fails open.

**Recommended fix**: validate before the ACL call.

```php
// before:
if ($thistype && !$issue && !AclMain::aclCheckIssue($thistype, '', ['write', 'addonly'])) {

// after:
if ($thistype && !$issue && (!array_key_exists($thistype, $ISSUE_TYPES) || !AclMain::aclCheckIssue($thistype, '', ['write', 'addonly']))) {
```

(Exact syntax depends on `$ISSUE_TYPES`'s scope at that line — needs a direct read of the surrounding
function to confirm the variable is in scope, or whether it needs to be fetched via
`OEGlobalsBag::get('ISSUE_TYPES')` as it is elsewhere in the same file. Not fully resolved here —
flagged for whoever implements this, one function-scope check away from a working patch.)

**Blast radius**: single line, single file, no shared-function impact at all.

**Report notes this needs a further check not yet made**: what `$irow['type']` is later used for once
set to a bogus value — the probe report explicitly flagged this as "not traced further, per scope."
Whoever implements this fix should trace that downstream use before considering the fix complete, not
just add the validation and stop.

---

## 2. The 8 admin-misconfiguration-path call sites — recommend NOT fixing individually, NOT fixing
the shared helper globally either, without more information

These are `aclCheckAcoSpec()`/`aclCheckIssue()`'s **direct** call sites (§4.1/§4.2 of the probe
report) — `C_Document.class.php` (×2), `patient_report.php`, `Document.class.php::can_access()`,
`C_EncounterVisitForm.class.php`, `add_edit_issue.php:794`, `demographics.php` (×3), `stats.php` (×2),
`stats_full.php` (×4).

**Why not the same "just deny on empty" fix as §1**: the probe report's own §5.1 already confirms
these are **latent, not active** — zero of 34 `categories` rows and zero of 13 `issue_types` rows
currently have an empty `aco_spec` on this instance. Fixing something not currently triggered is lower
urgency than §1's two live paths.

**Why not a global flip of `aclCheckAcoSpec()`/`aclCheckIssue()` to fail closed either, at least not
yet**: I checked one of the report's own cited concerns directly — `Document::can_access()`'s
documented "no categories tied to the document, default access is granted" behavior turns out to be
guarded by its **own separate early return** (`Document.class.php:362-364`, `if (empty($categories))
{ return true; }`), which runs *before* `aclCheckAcoSpec()` is ever called. So that specific
documented reliance would **survive** a global fail-closed flip untouched — the blast radius there is
smaller than the probe report worried about. **But I have not checked the other 15 admin-misconfig
call sites for similar independent guards**, and the probe report's own point stands generally: an
admin who deliberately selects the blank "no ACO restriction" option in the category-editor or
issue-type-editor UI (§5.2 — a first-class, selectable option in both screens, not a schema accident)
is currently relying on fail-open to mean "no restriction." Flipping the shared helper globally would
silently turn every one of those deliberate "no restriction" configurations into "deny everyone,"
including administrators, with no warning and no migration path. **That is a real behavior change for
whoever set up their instance that way**, even if it doesn't affect this demo instance's own data
today.

**Recommendation**: leave `aclCheckAcoSpec()`/`aclCheckIssue()` as fail-open for now. If this is worth
fixing at the shared-function level later, it needs: (a) a full audit of all 21 call sites'
surrounding logic for independent guards like `can_access()`'s, (b) a decision on whether "blank ACO
spec" should still mean "no restriction" going forward (a product/security policy call, not an
engineering one), and (c) if the meaning changes, a migration path for any admin instance that already
has legitimate blank specs (none exist on this instance, but this is upstream OpenEMR behavior — other
deployments may rely on it).

---

## 3. Owner decision needed: what happens to the 16 orphaned form directories

This is the one piece of §1a's fix that isn't purely technical. After the `aclCheckForm()` fix, these
16 directories become unreachable to everyone (the safe default) rather than reachable to everyone
(the current bug). Three options:

| Option | What it means | Cost |
|---|---|---|
| **A — Leave them blocked** | Simplest. If these forms were never meant to be part of this product's demo/pilot surface, blocking them is correct and needs no further work | Zero cost, but if any of these forms (e.g. `physical_exam`, `sdoh`, `treatment_plan` — clearly clinically relevant for an ophthalmology/outpatient ICP) were meant to be usable, they silently stop being reachable at all, including by administrators |
| **B — Register the clinically-relevant ones with real ACO specs** | Adds proper `registry` rows (per the existing pattern other forms use) for whichever of the 16 you actually want active — likely a subset, not all 16 (`LBF`, `CAMOS`, `track_anything` look more like generic/legacy scaffolding than ICP-relevant forms; `physical_exam`, `sdoh`, `prior_auth`, `transfer_summary`, `note`, `gad7`, `phq9` look more plausibly relevant) | Needs your judgment on which forms belong in the product surface — an engineer can't make that call. Each registered form also needs a real `aco_spec` decision (which roles should access it) |
| **C — Explicitly confirm all 16 are dead/legacy and should stay permanently blocked** | Same effect as A, but as a recorded decision rather than a default, so nobody re-opens this later as a mystery gap | Zero engineering cost; just needs your explicit sign-off so it's a decision, not an oversight |

**I have not looked inside any of the 16 directories' `new.php` files to judge their actual
completeness/quality** — only confirmed they exist and have working entry points. Before choosing B
for any specific form, whoever implements it should open that form and confirm it's not a half-built
stub.

> **✅ RESOLVED 2026-08-19 — Owner decision, given directly in conversation with the orchestrating
> session.** **Option A confirmed: all 16 directories stay blocked.** This is now a deliberate
> decision, not merely the fix's safe-default behaviour — no further engineering action needed. If
> any of these forms are needed for the product later, registering the specific ones required is a
> separate future task, not implied or scheduled by this decision.

---

## 4. What I'm NOT scoping here

- **The 15 unchecked admin-misconfig call sites for independent guards** (§2) — out of scope for this
  pass; would need the same line-by-line tracing this document did for `Document::can_access()`,
  repeated 15 more times.
- **Implementation itself** — this is a scope, not a patch. Per this project's own closure discipline,
  a security-relevant code change like §1's two fixes should go in as its own commit with its own
  verification (ideally the same negative-control-style testing this project has used throughout —
  confirm the fix denies the previously-open case, confirm it doesn't break the 3 confirmed-registered
  core forms).

---

## 5. Recommended order, if authorized

1. **§1a** (`aclCheckForm()` deny-on-no-registry-row) — highest value, narrowest blast radius, closes
   the confirmed live-exploitable multi-form bypass.
2. **§1b** (`add_edit_issue.php` `$thistype` validation) — small, independent, closes the second
   confirmed live-exploitable path.
3. **§3's decision** (Owner) — needed before or alongside #1, since the fix's *effect* (blocking the
   16 directories) depends on what you want to happen to them.
4. **§2** — deferred, needs a larger audit before any global change; not blocking §1/§3.
