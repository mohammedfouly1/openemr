# Patch records — core files edited outside the branding module (F6)

Per `docs/RebrandingPlan.md` §7.1 row F6 ("one numbered patch record per core edit") and Q1 ("any
unavoidable core change requires a numbered ADR/patch record and an upstream-first path"). Scope: files
**outside** `interface/modules/custom_modules/oe-module-thiqa-branding/` that this project's commits
actually modified. Module-internal files are not "core edits" and are not recorded here.

**How this list was built.** Not copied from `docs/RebrandingPlan.md` §5.4's own table — that table was
cross-checked against `git show --stat` on every commit in `git log --oneline -15` that plausibly touches
core files (`df3cc18f2`, `b866c5358`), and the file set below is what those commits actually changed, not
what the plan predicted they would change. **This surfaced a real discrepancy, recorded in PR-07.**

## Discrepancy found: the plan's own "7 files" count is incomplete

`docs/RebrandingPlan.md` §5.4 states, after its own K-21 correction: *"Residual mandatory core edits: 10
BRAND IDs / 12 strings / 7 files"* and lists exactly 7 files in its table (§5.4, lines 978-986): `admin.php`,
`FhirMetaDataRestController.php`, `ProductRegistrationService.php`, `OAuth2AuthorizationListener.php`,
`interface/globals.php`, the Zend Installer `index.phtml`, and
`templates/login/partials/html/primary_logo.html.twig`.

**The commit that actually did this work (`df3cc18f2`) touched an 8th file the plan's table omits:
`src/Telemetry/TelemetryService.php`.** Confirmed by `git show --stat df3cc18f2` (this session) and by the
commit's own message, which describes the `TelemetryService` change in the same breath as the other seven
("Ten BRAND IDs across seven files" — the commit message itself under-counts by one, matching the plan's
stale figure rather than correcting it). See PR-07 below.

**Separately, a 9th core-edit location exists that neither the plan's §5.4 PATCH table nor its file count
was ever meant to cover**: `templates/error/*.twig` (5 files), patched in a **different** commit
(`b866c5358`) under BRAND-101, which the plan classifies as SET-TRANSLATION (WS-D), not PATCH (WS-C) — see
`docs/RebrandingPlan.md` §5.5 and `docs/branding/changes.md`'s own note that this was "achieved... by
direct edit, not by catalog," diverging from how §15.3 says the SET-TRANSLATION workstream was meant to
work. It is a genuine core-file edit outside the module regardless of which workstream label it carries, so
it is recorded here as PR-09.

**Net effect:** the real count of core files edited by this project is **9**, not the 7 the plan's own §5.4
table states, once TelemetryService.php and the error templates are both counted. This document does not
silently reconcile that difference — a future editor of `RebrandingPlan.md` should update §5.4's file count
and table to match.

> **Update 2026-08-10 (`docs/RebrandingBugs.md` RB-02).** The paragraph above was written when this
> document ended at PR-09. It is now out of date in two ways, both corrected below rather than rewritten
> here (the reasoning above is still the reasoning that found the discrepancy):
>
> - The conditional installer/upgrade set (BRAND-007…012 — `setup.php`, `sql_patch.php`, `sql_upgrade.php`,
>   `ippf_upgrade.php`) has since been patched and had **no patch record at all**. It now has PR-10…PR-13.
> - The current total is **17 files**, not 9 or 13. See the reconciliation table at the end of this
>   document, which supersedes every earlier figure including this paragraph's.

---

## PR-01 — `admin.php`

**BRAND ID:** 005, 006. **Commit:** `df3cc18f2`. **Locked decision satisfied:** Invariant 4 residual-edit
exception (site title is unauthenticated and runtime-verified, per §5.4: "Must patch").

**What changed:** Two literal strings in the `<title>` and `<h2>` of the unauthenticated multi-site
administration screen.

```diff
-    <title>OpenEMR Site Administration</title>
+    <title>Thiqa Site Administration</title>
...
-                    <h2>OpenEMR Multi Site Administration</h2>
+                    <h2>Thiqa Multi-Site Administration</h2>
```

**Why no extension point could reach it:** `admin.php` is a top-level, un-templated PHP script that emits
its own `<html>`/`<head>` inline — there is no Twig render, no `TemplatePageEvent`, and no filter event on
this page for a module to hook.

**Rebase risk:** **Low**, per `docs/RebrandingPlan.md` §5.4's own table. Independently confirmed by this
session's citation of `docs/branding/remaining-dependencies.md` V-09: a `git merge-tree HEAD upstream/master`
dry run found **zero conflicts** in this file against current `upstream/master`.

**Upstream-PR intent:** Yes — replace the literal with text derived from `$openemr_name`, per §5.4's table.

---

## PR-02 — `interface/globals.php`

**BRAND ID:** 135, 136. **Commit:** `df3cc18f2`. **Locked decision satisfied:** Invariant 4 residual-edit
exception (messages emitted before translation/bootstrap exists, so no `xlt()` catalog mechanism can reach
them).

**What changed:** Two pre-bootstrap fatal-error strings, shown when required PHP extensions
(`openssl`, the `aes-256-cbc` cipher) are missing.

```diff
-    echo "OpenEMR Error : OpenEMR is not working since the php openssl module is not installed.";
+    echo "Thiqa Error: Thiqa is not working since the php openssl module is not installed.";
...
-    echo "OpenEMR Error : OpenEMR is not working since the openssl aes-256-cbc cipher is not available.";
+    echo "Thiqa Error: Thiqa is not working since the openssl aes-256-cbc cipher is not available.";
```

**Why no extension point could reach it:** these lines execute before the module system, the event
dispatcher, or the translation catalog are available — this is literally the earliest possible failure
point in the bootstrap.

**Rebase risk: Medium** per `docs/RebrandingPlan.md` §5.4's own table ("hot bootstrap file" — this file
receives frequent upstream churn unrelated to branding). **However**, the actual rebase dry run in
`docs/branding/remaining-dependencies.md` V-09 found this specific file **conflict-free** against current
`upstream/master` — better than the plan's own risk prediction, not a contradiction of it (a Medium-risk
rating describes exposure over the file's *history* of churn, not a guarantee that any one rebase attempt
will conflict).

**Upstream-PR intent:** Yes — per §5.4: "messages should use a constant" rather than a literal product
name, which is a genuine upstream improvement independent of this fork's branding.

---

## PR-03 — Zend Module Installer `index.phtml`

**File:** `interface/modules/zend_modules/module/Installer/view/installer/installer/index.phtml`.
**BRAND ID:** 130. **Commit:** `df3cc18f2`. **Locked decision satisfied:** Invariant 4 residual-edit
exception (hardcoded external wiki URL in an admin-only screen).

**What changed:** Two hardcoded links to `open-emr.org/wiki`, replaced with the product's own docs URL, and
the surrounding help copy's product-name references.

```diff
 $help_items = [
-    xlt('') . '<a  class="text-dark" href="https://www.open-emr.org/wiki/index.php/OpenEMR_Modules" target="_blank">' . xlt("Visit additional modules for OpenEMR developed and listed by third party vendors.") . '</a>',
+    xlt('') . '<a  class="text-dark" href="https://skyeagle.uk/docs/installer" target="_blank">' . xlt("Visit additional modules for Thiqa developed and listed by third party vendors.") . '</a>',
...
-        <a class="btn btn-outline-info btn-sm btn-show bg-light text-dark my-2" href="https://www.open-emr.org/wiki/index.php/OpenEMR_Modules" target="_blank"><?php echo xlt('Visit Third Party Modules Wiki'); ?></a>
+        <a class="btn btn-outline-info btn-sm btn-show bg-light text-dark my-2" href="https://skyeagle.uk/docs/installer" target="_blank"><?php echo xlt('Visit Third Party Modules Wiki'); ?></a>
```

**Why no extension point could reach it:** this is a Zend/Laminas MVC view script rendered by the legacy
module framework's own admin controller, outside the Twig render pipeline the branding module's `Plane 4b`
attaches to.

**Rebase risk:** **Low** per `docs/RebrandingPlan.md` §5.4's own table. Not one of the 6 files
`docs/branding/remaining-dependencies.md` V-09's rebase dry run specifically enumerated as checked (that
finding named `admin.php`, `interface/globals.php`, `FhirMetaDataRestController.php`,
`OAuth2AuthorizationListener.php`, `ProductRegistrationService.php`, `TelemetryService.php` — this file was
not one of the six re-verified against current `upstream/master` in that pass), so its conflict-free status
against current upstream is **not independently confirmed** by this session, only asserted by the plan.

**Upstream-PR intent:** Configurable docs URL, per §5.4's table — a reasonable ask (make the wiki link a
setting) but lower urgency than the FHIR/a11y fixes below.

---

## PR-04 — `src/RestControllers/FHIR/FhirMetaDataRestController.php`

**BRAND ID:** 087, 126. **Commit:** `df3cc18f2`. **Locked decision satisfied:** Invariant 4 residual-edit
exception, with the plan's own **"Strong"** upstream-PR-intent rating (§5.4 table).

**What changed:** Constructor now accepts an optional `OEGlobalsBag` (constructor-injected per this
project's DI standard), and both `implementation.description` and `software.name` in the FHIR
`CapabilityStatement` now read the `openemr_name` global instead of a hardcoded `"OpenEMR"` literal. One
literal survives as a fallback (`?: 'Thiqa'`) because `software.name` is a required FHIR element that must
not serialise empty.

```diff
+use OpenEMR\Core\OEGlobalsBag;
 ...
 class FhirMetaDataRestController
 {
     private readonly RestControllerHelper $restHelper;
     private readonly ServerConfig $config;
+    private readonly OEGlobalsBag $globalsBag;

-    public function __construct()
+    public function __construct(?OEGlobalsBag $globalsBag = null)
     {
         $this->config = new ServerConfig();
         $this->restHelper = new RestControllerHelper($this->config->getFhirUrl());
+        $this->globalsBag = $globalsBag ?? OEGlobalsBag::getInstance();
     }
 ...
+        $productName = $this->globalsBag->getString('openemr_name') ?: 'Thiqa';
         $implementation = new FHIRCapabilityStatementImplementation();
         $implementation->setUrl($resturl);
-        $implementation->setDescription(new FHIRString("OpenEMR FHIR API"));
+        $implementation->setDescription(new FHIRString($productName . " FHIR API"));
 ...
         $software = new FHIRCapabilityStatementSoftware();
-        $software->setName(new FHIRString("OpenEMR"));
+        $software->setName(new FHIRString($productName));
```

**Why no extension point could reach it:** the FHIR `CapabilityStatement` is built by a single method on a
core REST controller with no event dispatch or template layer in between.

**Rebase risk:** **Low** per `docs/RebrandingPlan.md` §5.4's own table, and **confirmed conflict-free**
against current `upstream/master` directly in this session's predecessor pass
(`docs/branding/remaining-dependencies.md` V-09).

**Upstream-PR intent: Strong**, per the plan's own table — `software.name` honouring a configurable
`openemr_name` global is a genuine improvement independent of this fork, not a branding-only concern.

---

## PR-05 — `src/RestControllers/Subscriber/OAuth2AuthorizationListener.php`

**BRAND ID:** 134. **Commit:** `df3cc18f2`. **Locked decision satisfied:** Invariant 4 residual-edit
exception (raw, non-translatable JSON error string).

**What changed:** One hardcoded literal in an exception thrown when the API is disabled.

```diff
-            throw new NotFoundHttpException("OpenEMR Error: API is disabled");
+            throw new NotFoundHttpException("Thiqa Error: API is disabled");
```

**Why no extension point could reach it:** this string is the message of a thrown `NotFoundHttpException`,
constructed inline inside an event subscriber with no template or translation call in the path.

**Rebase risk:** **Low** per `docs/RebrandingPlan.md` §5.4's own table, and **confirmed conflict-free**
against current `upstream/master` in this session's predecessor pass (`docs/branding/remaining-dependencies.md`
V-09).

**Upstream-PR intent:** "Message should not embed the product name" per §5.4's table — a generic,
reasonable upstream ask.

---

## PR-06 — `src/Services/ProductRegistrationService.php`

**BRAND ID:** 113. **Commit:** `df3cc18f2`. **Locked decision satisfied:** D-10 ("repoint/disable product
registration endpoint") — resolved by **removal**, per the plan's own recommendation
(`docs/RebrandingPlan.md` §1.2, row 4: "Recommend **disabling** registration outright rather than
repointing: it is opt-in telemetry to a vendor you are replacing").

**What changed:** The entire cURL call to `https://reg.open-emr.org/api/registration` inside
`optInStrategy()` is removed. The method now writes the operator's registration preference directly to the
local `product_registration` table with no network call at all — no endpoint to repoint, none to disable
via configuration, because none is contacted.

```diff
-        $httpVerifySsl = (bool) (OEGlobalsBag::getInstance()->get('http_verify_ssl') ?? true);
-        $curl = curl_init('https://reg.open-emr.org/api/registration');
-        curl_setopt($curl, CURLOPT_POST, true);
-        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($info));
-        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
-        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $httpVerifySsl);
-        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
-        $responseBodyRaw = curl_exec($curl);
-        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
-        curl_close($curl);
-
         $currentVersion = (string) (new VersionService())->getSoftwareVersion();
-        switch ($responseCode) {
-            case 201:
-                $entry = $this->entryExist();
-                ...
-                return $email;
-                break;
-            default:
-                throw new \GenericProductRegistrationException(xl("Server error: try again later"));
+        $entry = $this->entryExist();
+        if ($entry) {
+            sqlStatement("UPDATE `product_registration` SET ... ", [...]);
+        } else {
+            sqlStatement("INSERT INTO `product_registration` ... ", [...]);
         }
+        return $email;
```

The commit also drops 5 now-unmatched PHPStan baseline entries in
`.phpstan/baseline/openemr.forbiddenCurlFunction.php` and 2 in `.phpstan/baseline/{deadCode.unreachable,empty.notAllowed}.php`
— a direct consequence of the code they suppressed no longer existing, not a separate change requiring its
own record.

**Why no extension point could reach it:** the network call was inline inside a private method on a core
service class; no filter or listener sat between the method and `curl_exec()`.

**Rebase risk:** **Low** per `docs/RebrandingPlan.md` §5.4's own table, and **confirmed conflict-free**
against current `upstream/master` in this session's predecessor pass (`docs/branding/remaining-dependencies.md`
V-09).

**Upstream-PR intent:** "Configurable endpoint / disable switch" per §5.4's table — though the shipped
implementation went further than a switch (outright removal), which is arguably a stronger and simpler
change to propose upstream as an opt-in build flavour rather than a runtime toggle.

**Caveat carried from `docs/branding/coverage-matrix.md` row 34:** this closes BRAND-113 for the
*registration* call specifically. It does not, by itself, make "the product contacts no OpenEMR
infrastructure" true at the product level — see PR-07, which is the second, independent caller to the same
host.

---

## PR-07 — `src/Telemetry/TelemetryService.php` (the file the plan's own count omits)

**BRAND ID:** 113 (same decision, D-10 — the plan's §5.4 table does not list this file separately, which is
the discrepancy flagged at the top of this document). **Commit:** `df3cc18f2`.

**What changed:** `isTelemetryEnabled()` gains a consent gate. Usage telemetry
(`reportUsageData()`/`reportClickEvent()`/`trackApiRequestEvent()`, all reaching `reg.open-emr.org/api/usage`
per the commit message) is a **second, independent caller** to the same host PR-06 disconnected the
registration flow from. The gate is applied at the *end* of the method, after the existing
`product_registration`-table lookup runs unchanged, rather than as an early return — deliberately, so the
pre-existing upstream lookup and its tests are not altered, only the final result is withheld absent
consent:

```diff
     public function isTelemetryEnabled(): int
     {
+        // BRAND-113 / decision D-10: this product does not contact OpenEMR infrastructure.
+        // [rationale comment, ~20 lines, see full diff]
+
         // Check if telemetry is disabled in the product registration table.
         $result = $this->fetchRecords("SELECT `telemetry_disabled` FROM `product_registration` WHERE `telemetry_disabled` = 0", []);
         $isEnabled = !empty($result) ? $result[0]['telemetry_disabled'] ?? null : null;
         ...
+        if ($isEnabled === 1 && !OEGlobalsBag::getInstance()->getBoolean('enable_usage_telemetry')) {
+            // BRAND-113: the operator has not opted this deployment in, so nothing is sent.
+            return 0;
+        }
+
         return $isEnabled;
     }
```

**Why this is genuinely a different mechanism from PR-06, not a duplicate record:** PR-06 is a **removal**
(the code path to the network no longer exists at all). This is a **consent gate** (the code path still
exists, and would still run, but its result is withheld unless an operator has explicitly set
`enable_usage_telemetry`, which is absent — i.e. false — by default). `docs/branding/coverage-matrix.md`
row 34 records this distinction as a live, still-open policy question: "consent-gated is not the same as
never-contacted... Needs an owner and a decision record in the D-series." This patch record does not
resolve that open question; it only documents the mechanism as shipped.

**Why no extension point could reach it:** the gate needed to intercept the return value of an existing
core method with no filter/event hook of its own.

**Rebase risk:** Not rated in `docs/RebrandingPlan.md` §5.4 (the file is absent from that table — see the
discrepancy note at the top of this document). **Confirmed conflict-free** against current
`upstream/master` in this session's predecessor pass, however —
`docs/branding/remaining-dependencies.md` V-09 explicitly names this file among the "6 recorded... core
files" it checked and found conflict-free, even though the plan's own §5.4 table only ever listed 7 (soon
to be shown as inconsistent with itself: V-09's "6 files" list and §5.4's "7 files" list are two different
sets of six/seven — compare the file names in each; they overlap in five files but disagree on the
remaining one or two). Treat this file's rebase risk as **Low**, consistent with its sibling registration
change (PR-06), pending a maintainer reconciling the plan's own internal file-count inconsistency.

**Upstream-PR intent:** Weak-to-none as a generic upstream ask — consent-gating a specific vendor's
telemetry endpoint behind a fork-specific global (`enable_usage_telemetry`) is Thiqa-specific policy, not a
general OpenEMR improvement in the way PR-04's FHIR fix is.

---

## PR-08 — `templates/login/partials/html/primary_logo.html.twig`

**BRAND ID:** 053. **Commit:** `df3cc18f2`. **Locked decision satisfied:** Invariant 4 residual-edit
exception, discovered *after* an initial (incorrect) plan to deliver this via a module template override —
see the plan's own **K-21 correction** (`docs/RebrandingPlan.md` §5.4, lines 948-972).

**What changed:** The primary/secondary login logo `<img>` tags read an `alt` attribute from
`primaryLogoAlt`/`secondaryLogoAlt` (already supplied by `LoginTemplateListener`, which needed no change)
instead of a hardcoded empty string.

```diff
-        <img src="{{ primaryLogo|attr }}" class="img-fluid" alt="">
+        <img src="{{ primaryLogo|attr }}" class="img-fluid" alt="{{ primaryLogoAlt|default('')|attr }}">
...
-            <img src="{{ secondaryLogo|attr }}" alt="" class="img-fluid">
+            <img src="{{ secondaryLogo|attr }}" alt="{{ secondaryLogoAlt|default('')|attr }}" class="img-fluid">
```

**Why the module override that was originally planned could not work (K-21):** two independent reasons,
both confirmed by direct source reading during this project's audit pass, not merely asserted. First,
`TwigOverrideListener::onTemplatePage()` returns early for every page except the SMART style contract —
the login page deliberately keeps the core layout. Second, and decisive even without the first:
`primary_logo.html.twig` is a **partial**, pulled in via `{% include %}` from five core parent templates.
`{% include %}` resolves by template *name* through the Twig loader; `TemplatePageEvent` name-rewriting
only substitutes a **top-level** rendered template, not an included partial — there is no seam at an
include boundary, and the one construct that would create one (`prependPath()` into the main Twig
namespace) is prohibited outright by locked `Q38`. The module's own copy of this partial was consequently
dead code — included by nothing, exercised only by the module's own render tests, which is exactly what
kept the gap invisible until a live page render showed `alt=""` with the module fully active.

**Rebase risk:** **Low** per `docs/RebrandingPlan.md` §5.4's own table. Not one of the 6 files
`docs/branding/remaining-dependencies.md` V-09's rebase dry run specifically re-verified against current
`upstream/master` (that finding's file list does not include this template) — its conflict-free status is
**not independently confirmed** by this session, though its small, self-contained diff (two `alt=""` →
`alt="{{ ...|default('')|attr }}"` substitutions) is inherently low-conflict-surface regardless.

**Upstream-PR intent: Strong**, per §5.4's own table and K-21's correction text — a hardcoded empty `alt`
on a logo `<img>` is an accessibility defect in upstream OpenEMR itself, independent of this fork's
branding, and the fix defaults to today's (empty) behaviour when the new Twig variables are unset. This is
one of the clearest "should genuinely go upstream" candidates of the whole patch set.

---

## PR-09 — `templates/error/*.twig` (5 files — not part of the plan's own PATCH accounting)

**Files:** `templates/error/400.html.twig`, `400.json.twig`, `404.html.twig`, `404.json.twig`,
`general_http_error.html.twig`. **BRAND ID:** 101. **Commit:** `b866c5358`
("fix(branding): rebrand the HTTP error page titles"). **Locked decision satisfied:** none directly — this
is filed by the plan under **SET-TRANSLATION (WS-D)**, not PATCH (WS-C), because the strings stay
`xlt()`/`xl()`-wrapped (§5.5). It is recorded here anyway because it is, mechanically, exactly the same
kind of thing as PR-01 through PR-08: a tracked core file outside the module, edited directly.

**What changed:** Five `"OpenEMR ... Error"` translation-source strings become `"Thiqa ... Error"`.

```diff
 {% extends 'error/general_http_error.html.twig' %}
 {% set statusCode = 400 %}
-{% block title %}{{ "OpenEMR 400 Error"|xlt }}{% endblock %}
+{% block title %}{{ "Thiqa 400 Error"|xlt }}{% endblock %}
```
(and equivalently for `400.json.twig`'s `|xl|json_encode`, `404.html.twig`, `404.json.twig`, and
`general_http_error.html.twig`'s own `"OpenEMR Error"` base title.)

**Why this is a discrepancy worth naming, not silently folding into the SET-TRANSLATION workstream:**
`docs/branding/changes.md`'s own row for BRAND-101 flags this precisely: "§15.3 says this should have been
achieved purely via the translation catalog (source string never touched), but the actual implementation
edited the source literal directly. Functionally equivalent, mechanically different from the classified
action." That means this is, in fact, a tracked-core-file diff — the same category `docs/RebrandingPlan.md`
§7.1 F6 asks patch records to cover — even though the plan's own workstream taxonomy never routed it
through the PATCH-specific §5.4 accounting (7-file table) that produced PR-01 through PR-08 above.

**Rebase risk:** Not rated by `docs/RebrandingPlan.md` §5.4 (out of that table's scope) and not covered by
`docs/branding/remaining-dependencies.md` V-09's specific rebase dry run either. Assessed here directly: the
diff is a single-word substitution inside an `{% block title %}`, in files whose `git log` history (checked
this session) shows only occasional, unrelated touches (a typo-fix pass, a whitespace-consistency pass) —
**Low**, but explicitly **not confirmed by the same rebase dry-run evidence** PR-01/PR-04/PR-05/PR-06/PR-07
have.

**Upstream-PR intent:** None claimed — these are Thiqa-specific product-name strings in translation source,
not a generic defect fix.

---

## PR-10 — `setup.php`

**BRAND ID:** 007, 008, 009. **Commit:** `2ec72e6ff`. **Locked decision satisfied:** Invariant 4 residual-edit exception. `docs/rebranding.md` §16.2
classifies all three as **PATCH**; `docs/rebranding.md` §15.2 additionally marks them *conditional*
(operator-only screens), and `docs/RebrandingPlan.md` §5.4 recommends patching them "before customer-facing
installs, as one grouped commit".

**What changed:** 10 literal strings across the installer's own inline HTML — two `<title>`s, two
`navbar-brand` anchors, the initial-user copy (×2), the "restart Apache before following below … link"
sentence, the `Initial User Details` legend, the PHP-settings sentence, and the theme-selection prompt.

```diff
-        <title>OpenEMR Setup Tool</title>
+        <title>Thiqa Setup Tool</title>
-           <a class="navbar-brand" href="#">OpenEMR Setup</a>
+           <a class="navbar-brand" href="#">Thiqa Setup</a>
-                    echo "<p><b>The initial OpenEMR user is …
+                    echo "<p><b>The initial Thiqa user is …
```

**Why no extension point could reach it:** `setup.php` is a top-level bootstrap script that runs *before*
the application exists — no module system, no event dispatcher, no Twig. It emits its own `<html>` inline.
There is by construction nothing to hook.

**Not a translation concern:** every string changed here is raw inline HTML or a raw `echo`. None is
`xl()`/`xlt()`-wrapped, so no catalogue key moved and no translation was orphaned — verified by
`git diff -U0 -- setup.php | grep -E "xl\(|xlt\("` returning nothing. This is what distinguishes it from
the BRAND-127/128/129 case (`docs/RebrandingBugs.md` RB-01).

**Rebase risk: Medium.** Not rated by `docs/RebrandingPlan.md` §5.4 (the file is absent from that table).
`setup.php` receives upstream churn on most release cycles. **Not covered** by the V-09 rebase dry-run,
which only examined the 6 files the plan listed.

**Upstream-PR intent:** Weak. The installer naming its own product is reasonable upstream behaviour; a
configurable installer brand is a larger change than this fork needs.

---

## PR-11 — `sql_patch.php`

**BRAND ID:** 010. **Commit:** `2ec72e6ff`. **Locked decision satisfied:** Invariant 4 residual-edit
exception (operator-only upgrade screen, conditional per §15.2).

**What changed:** three occurrences of the product name in the patch screen's title, banner and version line.

```diff
-<title>OpenEMR <?php echo attr($EMRversion) ?> <?php echo xlt('Database Patch'); ?></title>
+<title>Thiqa <?php echo attr($EMRversion) ?> <?php echo xlt('Database Patch'); ?></title>
```

**Worth copying elsewhere:** this file already keeps the product name **outside** the `xlt()` call —
`xlt('Database Patch')` and `xlt('Version')` are untouched. The catalogue key therefore never moved and no
translation was orphaned. This is the pattern the OAuth2/Zend call sites should have followed; it is the
structural reason PR-11 is safe where a naive rename was not (`docs/RebrandingBugs.md` RB-01).

**Why no extension point could reach it:** same as PR-10 — a pre-application bootstrap script with inline
HTML.

**Rebase risk: Low.** Small file, infrequent upstream change. Not covered by the V-09 dry-run.

**Upstream-PR intent:** Weak, as PR-10.

---

## PR-12 — `sql_upgrade.php`

**BRAND ID:** 011. **Commit:** `2ec72e6ff`. **Locked decision satisfied:** Invariant 4 residual-edit
exception (operator-only upgrade screen, conditional per §15.2).

**What changed:** the `<title>` (raw HTML) and the `<h2>` (translation-wrapped).

```diff
-    <title>OpenEMR Database Upgrade</title>
+    <title>Thiqa Database Upgrade</title>
-                    <h2><?php echo xlt("OpenEMR Database Upgrade"); ?></h2>
+                    <h2><?php echo xlt("Thiqa Database Upgrade"); ?></h2>
```

**This one needed a catalogue migration, and got one.** The `<h2>` literal is `xlt()`-wrapped and the old
constant `"OpenEMR Database Upgrade"` carried **28 translations** — the single largest translated constant
in the whole branding surface. Renaming the literal alone would have orphaned all 28
(`docs/RebrandingBugs.md` RB-01). Because BRAND-011's action is legitimately **PATCH**, the source edit
stands and the translations were **carried forward** instead: `tools/branding/brand-strings.json`
`carry_forward` entry, applied by `tools/branding/apply-brand-strings.php`, copies each of the 28 onto the
new constant with the product proper noun substituted. Locales whose translation never contained "OpenEMR"
(Arabic `ترقية قاعدة بيانات البرنامج`, Turkish, Portuguese, Chinese Traditional) carry forward unchanged,
which is correct.

Verified: `SELECT COUNT(*)` for `Thiqa Database Upgrade` = **28**, and `OpenEMR Database Upgrade` still =
28 (the old constant is left intact rather than deleted, so an un-migrated deployment degrades to the
upstream string rather than to nothing).

**Why no extension point could reach it:** same as PR-10/PR-11.

**Rebase risk: Medium.** `sql_upgrade.php` changes on essentially every release cycle (the branch's own
base carries three recent upstream commits touching it). **Not covered** by the V-09 dry-run. This is the
highest-conflict-probability file in the whole residual set and should be re-checked at every rebase.

**Upstream-PR intent:** Weak for the literal; **moderate** for the underlying shape — upstream would
benefit from keeping the product name outside `xlt()` here exactly as `sql_patch.php` already does, which
would make this file rebrandable with no patch at all.

---

## PR-13 — `ippf_upgrade.php`

**BRAND ID:** 012. **Commit:** `2ec72e6ff`. **Locked decision satisfied:** Invariant 4 residual-edit
exception (operator-only, IPPF-specific upgrade screen, conditional per §15.2).

**What changed:** three raw strings — `<title>`, `<h2>`, and one sentence of body copy.

```diff
-    <title>OpenEMR IPPF Upgrade</title>
+    <title>Thiqa IPPF Upgrade</title>
-                <h2>OpenEMR IPPF Upgrade</h2>
+                <h2>Thiqa IPPF Upgrade</h2>
-                This converts your OpenEMR database to UTF-8 encoding …
+                This converts your Thiqa database to UTF-8 encoding …
```

**Not a translation concern:** all three are raw HTML, none `xl()`-wrapped.

**Why no extension point could reach it:** same as PR-10.

**Rebase risk: Low.** This is a legacy IPPF-specific script that upstream rarely touches. Not covered by
the V-09 dry-run.

**Upstream-PR intent:** None claimed. The script is specific to an IPPF deployment path this product does
not target; the more likely long-term disposition is deletion, not an upstream PR.

---

## Summary table

| # | File | BRAND ID(s) | In plan §5.4's 7-file table? | Rebase-risk source |
|---|---|---|---|---|
| PR-01 | `admin.php` | 005, 006 | Yes | Plan: Low. V-09: conflict-free (confirmed) |
| PR-02 | `interface/globals.php` | 135, 136 | Yes | Plan: Medium. V-09: conflict-free (confirmed) |
| PR-03 | Zend Installer `index.phtml` | 130 | Yes | Plan: Low. Not in V-09's specific 6-file check |
| PR-04 | `FhirMetaDataRestController.php` | 087, 126 | Yes | Plan: Low ("Strong" PR intent). V-09: conflict-free (confirmed) |
| PR-05 | `OAuth2AuthorizationListener.php` | 134 | Yes | Plan: Low. V-09: conflict-free (confirmed) |
| PR-06 | `ProductRegistrationService.php` | 113 | Yes | Plan: Low. V-09: conflict-free (confirmed) |
| PR-07 | `TelemetryService.php` | 113 | **No — plan's table omits this file** | Not rated by plan. V-09 names it conflict-free directly, inconsistently with the plan's own 7-file list |
| PR-08 | `primary_logo.html.twig` | 053 | Yes (added by K-21 correction) | Plan: Low ("Strong" PR intent). Not in V-09's specific 6-file check |
| PR-09 | `templates/error/*.twig` (5 files) | 101 | **No — filed under SET-TRANSLATION, not PATCH** | Not rated by plan or V-09; assessed here as Low |
| PR-10 | `setup.php` | 007, 008, 009 | **No — conditional set, never in §5.4's table** | Not rated by plan. **Medium** (high upstream churn). Not in V-09's check |
| PR-11 | `sql_patch.php` | 010 | **No — conditional set** | Not rated by plan. Low. Not in V-09's check |
| PR-12 | `sql_upgrade.php` | 011 | **No — conditional set** | Not rated by plan. **Medium — highest conflict probability of the whole set.** Not in V-09's check |
| PR-13 | `ippf_upgrade.php` | 012 | **No — conditional set** | Not rated by plan. Low. Not in V-09's check |

**Total distinct core files edited by this project, outside the module: 17** (PR-01…PR-08 = 8 files,
PR-09 = 5 files, PR-10…PR-13 = 4 files).

### Reconciliation of the file count (updated 2026-08-10, `docs/RebrandingBugs.md` RB-02)

| Source | Figure | Assessment |
|---|---:|---|
| `RebrandingPlan.md` §5.9 exit criteria | "exactly the 6 recorded files" | Stale — never updated after K-21 |
| `RebrandingPlan.md` §5.4 corrected table | "7 files" | Counts mandatory WS-C patches only |
| This document, previous revision | 13 | Correct at the time; predated the conditional set landing |
| **This document, current** | **17** | PR-01…PR-13 |

**The count reached 23 briefly and was brought back down to 17.** An earlier working tree also edited
`templates/oauth2/*.twig` (3 files) and the three Zend `*.phtml` layouts for BRAND-127/128/129. Those six
edits were **reverted**: those BRAND IDs carry the action **SET-TRANSLATION**, not PATCH, and editing the
literal inside `xl()`/`xlt()` orphaned 59 existing translations because the English source string is the
catalogue key. They are now delivered as catalogue data via `tools/branding/brand-strings.json`, so they
are correctly **absent** from this document — a SET-TRANSLATION item that needs a patch record is a
SET-TRANSLATION item done wrong. Full analysis: `docs/RebrandingBugs.md` RB-01.

---

## PR-14 — `src/Services/EncounterService.php`

**BRAND ID:** none — **this is not a branding edit.** It is a **correctness/defect fix**, the first
non-branding core edit in this document, recorded here because Invariant 4 / Q1 governs *every* core
edit regardless of motive. **Readiness ref:** Phase 2B PB-037, RDY-0021. **Commit:** see below.

**Locked decision satisfied:** Invariant 4 residual-edit exception. **No extension point can reach
this.** The defect is a missing pair of columns inside a hand-built `INSERT` in a service method.
There is no event, no filter and no override surface between the caller and that statement — a module
cannot repair it, and the only alternatives were to leave the product defective or to correct every
row afterwards, which is a workaround, not a fix.

**Classification:** `SOURCE-CONFIRMED REL-820 SERVICE-LAYER ATTRIBUTION DEFECT`.

**What was wrong.** `EncounterService::insertSoapNote()` registers its `forms` row with a hand-built
`INSERT` that omits `user` and `groupname`. Every other form-registration path in the product goes
through `addForm()` → `FormService::addForm()`, which fills both from the session. A SOAP note created
through this service is therefore **unattributed in `forms`** — the audit trail shows a clinical note
authored by nobody.

```diff
+        $session = SessionWrapperFactory::getInstance()->getActiveSession();
+
         $formSql = "INSERT INTO forms SET";
         $formSql .= "     date=NOW(),";
         $formSql .= "     encounter=?,";
         $formSql .= "     form_name='SOAP',";
         $formSql .= "     authorized='1',";
         $formSql .= "     form_id=?,";
         $formSql .= "     pid=?,";
+        $formSql .= "     user=?,";
+        $formSql .= "     groupname=?,";
         $formSql .= "     formdir='soap'";
 
         $formResults = sqlInsert(
             $formSql,
             [
                 $eid,
                 $soapResults,
                 $pid,
+                $session->get('authUser'),
+                $session->get('authProvider'),
             ]
         );
```

**The fix mirrors the canonical path exactly rather than inventing behaviour.**
`FormService::addForm()` (lines 76-83) defaults these same two columns from
`$session->get('authUser')` and `$session->get('authProvider')`. This uses the identical keys, so a
SOAP note registered through the service is now attributed the same way as one registered through any
other form — no new convention is introduced.

### Reachability analysis — why this had to be fixed rather than worked around

| Caller | Reaches the defect? | Evidence |
|---|---|---|
| **Normal staff UI** | **NO** | `interface/forms/soap/save.php` → `C_FormSOAP::default_action_process()` → `addForm()` at `C_FormSOAP.class.php:78`. Correctly attributed |
| **REST / API** | **YES — live, registered route** | `POST /api/patient/:pid/encounter/:eid/soap_note`, registered at `apis/routes/_rest_routes_standard.inc.php:173-179`, gated `encounters\|notes`, dispatching to `EncounterRestController::postSoapNote()` (`:737`) → `insertSoapNote()` (`:747`) |
| **Module / service callers** | Only the Thiqa seeder | `insertSoapNote` has exactly two callers in the tree: the REST controller and `SeedDemoCommand` |
| **Thiqa seeder** | YES (by design) | Now redundant — the service is correct, so the seeder's compensating update is removed |

**The REST route is the finding that forced the fix.** Had the path been seeder-only, §3 permits a
deterministic seeder-side correction plus recorded technical debt. It is not seeder-only: it is a
registered, authenticated, ACL-gated endpoint in the shipping API surface, so **any pilot customer
writing SOAP notes over the API would silently accumulate unattributed clinical notes.** That is a
data-integrity defect in the product, not a demo inconvenience, and it is exactly the kind of thing
the audit-trail claim (MC-01, D-1) cannot afford.

**Upstream-first path (Q1).** This is an upstream `rel-820` defect, not Thiqa-introduced — the omission
is present in unmodified upstream source. **It should be contributed upstream**, which would also
retire this patch record. Not done in this phase: the branch is 418 commits behind and divergent, and
the upstream maintenance target (`master` vs `rel-820`) is still undecided under RDY-0045. Recorded as
the intended disposition rather than left implicit.

**Verification:** the accepted demo dataset must contain **zero unattributed seeded SOAP form
registrations**, verified by query, not by inspection — see PB-037.

---

## PR-15 — `src/Menu/MainMenuRole.php`

**BRAND ID:** none — **a correctness/defect fix, not branding.** Second non-branding entry in this
document, recorded here because Invariant 4 / Q1 governs every core edit regardless of motive.
**Readiness ref:** Phase 2B PB-052, RDY-0043. **Gates:** G1 G2.

**Locked decision satisfied:** Invariant 4 residual-edit exception. **No extension point can reach
it** — the defect is an inverted guard inside a `protected` method that builds the menu object
in-memory. There is no event, filter or override between the loop and the `array_push`.

**Classification:** `SOURCE-CONFIRMED UPSTREAM MENU DEFECT — present in rel-820 AND master`.

### What was wrong

`updateVisitForms()` creates each category with `$catEntry->children = []` and then pushes each form
**only if `children` is already non-empty**:

```diff
-            if (!empty($catEntry->children)) {
-                array_push($catEntry->children, $formEntry);
-            }
+            array_push($catEntry->children, $formEntry);
```

Because `children` starts empty, the guard is false on the **first** iteration — and since nothing is
ever pushed, it stays empty and the guard is false on **every** subsequent iteration too.

**The result is not "the first form is dropped". No form is ever added to any category.**

### Measured impact, before and after

Verified by invoking `updateVisitForms()` directly through reflection against the live registry:

| | Categories rendered | Forms rendered | Missing |
|---|---:|---:|---:|
| **Before** | 4 | **0** | **16 of 16** |
| **After** | 4 | **16** | **0** |

Every active encounter form was unreachable from the menu, including **Eye Exam** — the ophthalmology
form the entire beachhead demo rests on — plus **Vitals**, **SOAP**, **Fee Sheet**, **Procedure
Order** and **New Questionnaire**.

> **This corrects the audit.** Audit §14.4 and RDY-0043 both describe the defect as *"silently drops
> the first form in every category."* That understates it by a factor of sixteen. The audit read the
> code correctly for one iteration and did not carry the consequence forward to the next.

### Why this is upstream's, and why no workaround was possible

`src/Menu/MainMenuRole.php` is **byte-identical to both `upstream/rel-820` and `upstream/master`** —
this fork had not modified it. **The defect is present in both**, so RDY-0043's hope that *"the fix
may already exist in the 418 commits not yet taken"* is false: taking every upstream commit would not
fix it.

RDY-0043 offers *"work around by category placement"* as the fallback. **That cannot work.** With no
form ever pushed, there is no ordering, priority or category arrangement that produces a non-empty
menu. The guard has to go.

**`updateBlankForms()` in this same class performs the equivalent push with no guard** (line ~214),
which is what the correct behaviour looks like and is strong evidence the guard is an error rather
than an intent.

**Upstream-first path (Q1):** this is an upstream defect affecting every OpenEMR installation and
**should be contributed upstream**, which would retire this patch record. Not done in this phase —
the upstream maintenance target is still undecided (RDY-0045, EV-045). Recorded as the intended
disposition.

**Verification:** `scratchpad/menu-verify.php` builds the menu through the real code path and
reconciles it against `registry`; before/after runs are quoted above. `php -l` and `phpcs` clean.

---

## PR-16 — `interface/main/tabs/menu/menus/front_office.json`

**BRAND ID:** none — **a correctness/defect fix, not branding.** Third non-branding entry, recorded
because Invariant 4 / Q1 governs every core edit regardless of motive.
**Readiness ref:** Phase 2B PB-072, RDY-0042. **Gates:** G1 G2. **Agent B.**

**Locked decision satisfied:** Invariant 4 residual-edit exception. **No extension point can reach
it** — the menu is a static JSON data file read by `MenuRole::menuApplyRestrictions()`. There is no
event, filter or override that can add a missing entry; the only alternative is a whole-file
replacement menu, which is a larger and more fragile divergence than a two-entry data fix.

**Classification:** `SOURCE-CONFIRMED UPSTREAM MENU DEFECT — present in rel-820 AND master`.

### What was wrong

`front_office.json` declares `Add Patient` gated on `full_new_patient_form` and — unlike
`standard.json`, which carries a matched pair — ships **no negated counterpart**:

```diff
                 "global_req": "full_new_patient_form"
             },
+            {
+                "label": "Add Patient",
+                ... identical acl_req / url / target ...
+                "global_req": "!full_new_patient_form"
+            },
```

`MenuRole.php:129-132` skips an entry whose non-negated global is unset or false. So with
`full_new_patient_form = 0`, **a Front Office user has no menu route to register a patient at all** —
the first action of the D-7 reception segment, performed by the account that exists to perform it.

`standard.json` gets this right with two entries (`New/Search` for the full form, `New` for the
short one). The fix mirrors that pattern exactly; **no new convention is introduced.**

### Measured impact, before and after

`docs/evidence/harnesses/rdy0042-probe.php` applies `MenuRole`'s own `global_req` rule, transcribed from source,
to the real JSON at both global values, and carries a negative control (a label that must never
match, proving the collector can return 0).

| `full_new_patient_form` | Before (HEAD) | After |
|---|---:|---:|
| `1` | 1 entry visible — PASS | 1 entry visible — PASS |
| `0` | **0 entries visible — FAIL** | **1 entry visible — PASS** |
| negative control | 0 — PASS | 0 — PASS |

Exit status `1` before, `0` after.

### Live exposure today, stated precisely

**The live global is `full_new_patient_form = 1`**, so the current demo instance is **not** affected
and no rehearsal would have caught this. The fix removes a **latent** hazard that fires on any
instance where the short registration form is preferred — which is a plausible pilot configuration,
since the full form is the longer of the two.

**Upstream-first path (Q1):** an upstream defect affecting every OpenEMR installation using the
`front_office` menu role, and it **should be contributed upstream**, which would retire this record.
Not done in this phase — the upstream maintenance target is undecided (RDY-0045, EV-045). Recorded
as the intended disposition.

**Verification:** JSON re-parsed and validated (`json_last_error_msg()` → *No error*); both
counterparts confirmed present; before/after probe quoted above.

---

## PR-17 — `oe-module-thiqa-branding/src/Console/SeedDemoCommand.php` (+ new `Console/BaselineOption.php`)

**BRAND ID:** none — **a portability/defect fix, not branding.** Fourth non-branding entry.
**Readiness ref:** demo-deployment discovery `DG-005` / blocker `B-03`
(`docs/demo-deployment-readiness.md` §7.3, §18 W-1). **Gates:** G3. **Date:** 2026-08-15.

### ⚠ A scope note this record should not paper over

**Invariant 4 / Q1 do not strictly compel this record.** Every other entry in this document is a
*core* file — one outside the branding module — which is exactly the trigger Invariant 4 names. The
file changed here lives **inside** `oe-module-thiqa-branding`, so the module's own code is being
changed, which is what a module is for.

It is recorded anyway, deliberately, for two reasons: the change alters a **fail-closed safety
control** (the gate that stops the seeder running without a proven rollback artefact), and it
**establishes the provisioning interface the Ubuntu deployment runbook will depend on**. A change
with either property should be reviewable from the register regardless of which side of the module
boundary it sits on. **No core file is touched by this patch.**

### The problem

`SeedDemoCommand` refused to run anywhere except one developer workstation.

```php
private const BASELINE_PATH = 'C:/openemr-stack/backups/protected/rdy0044a/'
                            . 'thiqa-rdy0044a-preseed-20260813-185745.sql';
...
if (!is_file(self::BASELINE_PATH)) {                                              // precondition
    $problems[] = 'RDY-0044-A baseline not found at its recorded path.';
} elseif (hash_file('sha256', self::BASELINE_PATH) !== self::BASELINE_SHA256) {
    $problems[] = 'RDY-0044-A baseline hash MISMATCH — ...';
}
```

The path was a **hard-coded Windows absolute path compiled into a committed application file**, and
it was consumed by a fail-closed precondition. On any host that is not that workstation — the Ubuntu
demo target above all — `is_file()` answers false, `checkPreconditions()` returns false, and the
command exits `FAILURE` without writing a row.

### Evidence

- The constant and its two consumers: `SeedDemoCommand.php:101-103`, `:335`, `:337` (pre-patch line
  numbers).
- It is the **only** drive-letter path in application code anywhere in the repository. A repo-wide
  scan for `[A-Za-z]:[\\/](openemr-stack|Users|Program Files|xampp|wamp|inetpub|windows)` returns
  hits only in `docs/`, in two evidence harnesses, in `tools/branding_production.py`, and in stock
  PHP `php.ini` *comments* under `docker/` and `ci/` — none of them runtime code.
  (`docs/demo-deployment-readiness.md` §18.)
- Consequence recorded as **P0 blocker `B-03`**: without it the deterministic demo dataset — the
  substrate of the whole Option 3 database strategy — is unreachable on the target.

### Affected files

| File | Change |
|---|---|
| `oe-module-thiqa-branding/src/Console/SeedDemoCommand.php` | 4 edits: register the option; resolve it; pass it to `checkPreconditions()`; replace the inline check with a delegated one |
| `oe-module-thiqa-branding/src/Console/BaselineOption.php` | **New.** Value object holding the resolve-and-verify logic |
| `tests/.../ThiqaBranding/Console/BaselineOptionTest.php` | **New.** 18 tests |

### Original behaviour → new behaviour

| | Original | New |
|---|---|---|
| Where the baseline is looked for | The compiled-in constant, always | `--baseline-path=<path>` if supplied, **otherwise the same compiled-in constant** |
| Baseline must exist | Yes | **Yes — unchanged** |
| SHA-256 must match `BASELINE_SHA256` | Yes | **Yes — unchanged, same constant, same digest** |
| Behaviour when either check fails | Appended to `$problems` → `checkPreconditions()` false → `execute()` returns `FAILURE` | **Identical** |
| Way to skip the check | None | **Still none** |
| Invocation with no new flag | — | **Byte-for-byte the previous behaviour** |

### Why this is necessary for Ubuntu portability

The demo database strategy is locked to **Option 3 — fresh database + controlled configuration +
synthetic demo seeding**. That strategy's step 7 *is* `thiqa-branding:seed-demo`. A seeder that
cannot execute outside one Windows workstation makes the locked strategy unexecutable on the target,
which is why this is a P0 rather than a tidy-up. The alternatives were worse: placing a file at a
literal `C:/...` path on an Ubuntu server is absurd, and abandoning the seeder would mean hand-moving
30 synthetic patients and their 72 encounters — losing the reproducibility that made Option 3
defensible in the first place.

### Security implications

**The integrity guarantee is unchanged; only the search location became an input.** Assessed
explicitly:

- **The digest is still hard-coded.** `BASELINE_SHA256` remains a class constant and is *not*
  configurable. An operator can say *where* to look; they cannot say *what counts as acceptable*.
  Pointing the option at an attacker-supplied file therefore fails the hash check and refuses.
- **No new bypass exists.** `BaselineOption::verify()` returns a refusal for every input shape that
  is not "a real file whose digest matches" — proven by a data-provider test that enumerates the
  omitted, empty, whitespace-only, directory, nonexistent, literal-`skip` and null-byte-injection
  cases and asserts **all seven refuse**.
- **Null-byte injection is safe.** `is_file()` returns `false` for a path containing a null byte and
  does not throw (verified directly on PHP 8.3.33), so the refusal path is taken rather than an
  exception escaping the precondition block.
- **Unreadable files fail closed.** `hash_file()` returns `false` on an unreadable file; the
  comparison is guarded with `is_string()` and `hash_equals()`, so a `false` becomes a MISMATCH
  refusal. The original `!==` comparison reached the same outcome; this is a clearer expression of
  it, not a change of behaviour.
- **The rejected path is never echoed**, matching `SiteOption`'s established handling and `EV-071`
  §4's finding that filesystem paths disclose host layout.
- **Attack surface is CLI-only.** `bin/.htaccess` denies web access to `bin/`, and `bin/console`
  refuses to run as root (`bin/console:25`). The option is reachable only by a non-root shell user
  who can already read the application tree.

### Rollback

Self-contained and low-risk. Revert the commit: `BaselineOption.php` and its test are new files
(deleting them removes the whole feature), and the four edits in `SeedDemoCommand.php` restore the
inline `is_file()`/`hash_file()` pair verbatim. **No database state, no migration, no generated
artefact and no configuration is involved**, so there is nothing to unwind beyond the source. An
operator who reverts is returned to the pre-patch situation: the seeder works on the recorded
workstation and refuses everywhere else.

### Tests

`tests/Tests/Isolated/Modules/ThiqaBranding/Console/BaselineOptionTest.php` — **18 tests, 32
assertions, all passing**:

```
vendor/bin/phpunit -c phpunit-isolated.xml --filter 'BaselineOption' --no-coverage
→ OK (18 tests, 32 assertions)
```

Covering, per the remediation brief: option absent (falls back to the recorded default) · the
default still failing closed when absent · nonexistent path · **hash mismatch on a real file** ·
empty value · whitespace-only value · valid path with the accepted digest · **path containing
spaces**, end to end · **Linux-style forward-slash path accepted verbatim**, plus a real
forward-slash file verifying normally · the seven-case no-bypass provider · option declared
`VALUE_REQUIRED` with the caller-supplied default · `define()` embedding no host path.

**Why the logic moved into a value object to be testable at all:** `SeedDemoCommand` cannot be
instantiated without a database (`QueryUtils` calls throughout `checkPreconditions()`), and the
remediation brief forbids running the seeder against the live development database. Logic left
inline in that method is logic that cannot be unit-tested. `BaselineOption` mirrors `SiteOption`,
the pattern this module already uses for a console option needing validation.

**No regression:** the full isolated ThiqaBranding suite (Twig render group excluded per
`CLAUDE.local.md` §9, which hangs on this host for reasons unrelated to this change) —
**1298 tests, 3660 assertions, OK**. `BrandingGovernanceGuard` — **31 tests, 66 assertions, OK**.
`phpcs` clean on all three files; `php -l` clean on all three.

### Relationship to DG-005 / B-03

This patch is the entirety of the remediation for both. `DG-005` asked *"How is
`SeedDemoCommand`'s `BASELINE_PATH` precondition satisfied on Ubuntu?"* and offered three options:
(a) place a baseline at the literal Windows path, (b) make the constant configurable **with a patch
record**, (c) abandon the seeder. **Option (b) was taken, and this is that record.** `B-03` moves
from P0-open to closed-in-source; it stays operationally dependent on the operator having a
baseline artefact whose digest matches `BASELINE_SHA256` present on the target — which is a
provisioning input, not a source defect.

### Confirmation: SHA-256 verification remains fail-closed

Stated explicitly because it is the one property a reviewer must not have to infer.
**`BASELINE_SHA256` is unchanged, still compiled in, still compared on every run.** There is no
flag, no value, and no code path — including omitting the option entirely — that reaches the
seeding logic without `BaselineOption::verify()` first returning `null`. A refusal is still appended
to `$problems`, still short-circuits `checkPreconditions()`, and still produces a non-zero exit
before the first row is written. `testNoInputShapeSkipsVerification` exists specifically to make a
future regression of this property fail the suite.

**Upstream-first path (Q1):** not applicable — the file is fork-owned module code with no upstream
counterpart.

---

### Required before release

**V-09 must be re-run against all 17 files.** The existing dry-run examined only the six the plan listed
and concluded "no conflicts in the recorded core files"; that conclusion does not transfer to eleven files
it never looked at — including `setup.php` and `sql_upgrade.php`, the two most upstream-churned files in
the set. Until that re-run happens, `RebrandingPlan.md` risk R-1 ("upstream rebase conflicts in the 6
patched core files") understates the exposure.

**Commit status — resolved 2026-08-10.** PR-10…PR-13 landed in `2ec72e6ff`
(*fix(branding): rebrand operator screens and deliver SET-TRANSLATION via the catalogue*) and their
references above are filled in. Every one of the 17 core files now has a numbered record naming a real
commit, which is what `Q1` asks for — a patch record citing no commit satisfies its letter but not its
purpose.

---

## PR-18 — `interface/reports/pat_ledger.php`

**BRAND ID:** none — **a correctness/defect fix, not branding.** Fifth non-branding entry, recorded
because Invariant 4 / Q1 governs every core edit regardless of motive. **Readiness ref:** Phase 2B
PB-214, RDY-0037. **Gates:** G2. **Agent:** AGENT-SEC2.

**Locked decision satisfied:** Q26 (tenant/site-aware currency resolver — "replace runtime hard-coded
currency assumptions... do not global-search-and-replace"). This patch does not introduce a resolver;
it **wires an existing one** (`OpenEMR\Common\Utils\FormatMoney::getFormattedMoney()`, called via the
legacy `oeFormatMoney()` wrapper in `library/formatting.inc.php`) through to a screen that was calling
it with the symbol parameter omitted. `FormatMoney` already reads `gbl_currency_symbol` from
`OEGlobalsBag` — confirmed live in the `globals` table as `SAR` (set per PB-016/PB-028) — so no new
resolver, no hardcoded string, and no other tenant's non-Saudi configuration is affected: any site
with `gbl_currency_symbol` unset renders identically to before (the symbol branch is a no-op on an
empty string, per `FormatMoney::getFormattedMoney()` line 43).

**Locked-decision exception used:** Invariant 4 residual-edit exception. **No extension point can
reach it** — this is a legacy, un-templated report script (`interface/reports/pat_ledger.php`) that
builds its own HTML inline via string concatenation; there is no Twig render, filter, or event on this
page a module could hook to alter the money-formatting call sites.

### What was wrong

`interface/reports/pat_ledger.php` is the server-rendered "Patient Ledger" screen (reached from the
patient menu's `Ledger` item, `interface/main/tabs/menu/menus/patient_menus/standard.json:86-89`, and
from `Reports > Financial > Pat Ledger`). Every one of its 13 `oeFormatMoney(...)` call sites omitted
the optional `$symbol` argument, which defaults to `false`:

```diff
-    echo "<td class='detail text-center'>" . text(oeFormatMoney($enc_chg)) . "</td>";
+    echo "<td class='detail text-center'>" . text(oeFormatMoney($enc_chg, true)) . "</td>";
```

(and identically for `$enc_pmt`, `$enc_adj`, `$enc_bal` in `PrintEncFooter()`; `$pmt_amt`, `$adj_amt`,
`$uac_appl`, `$uac_bal` in `PrintCreditDetail()`; the per-line-item `$erow['fee']`; and the grand-total
row's `$total_chg`, `$total_pmt`, `$total_adj`, `$total_bal`). Every monetary amount on the screen
rendered as a bare number — no `SAR`, no `$`, no currency indicator of any kind.

**Not touched:** the CSV-export code path in the same file (`$_REQUEST['form_csvexport']`). That path
has an unrelated, pre-existing content-corruption defect (`$csv` is initialized but never populated in
the render loop) found independently by AGENT-DATA2 (PB-208, `EV-071` §3.4-3.6) — out of this patch's
scope; fixing it would touch different lines for a different reason and is left to whoever owns that
finding.

### Measured impact, before and after

Live browser verification (`claude-in-chrome`, logged in as `k.alotaibi` / Accounting, credentials
read from `C:\openemr-stack\secrets\thiqa-demo-credentials.json`), same patient and figures PB-202
originally flagged — `SYN-0001` (Hessa Alharthi, pid 1): two encounters, charges 350.00 and 250.00, a
payment of 150.00, balance 450.00.

| | Before (PB-202, 2026-08-16) | After (PB-214, this patch) |
|---|---|---|
| Line-item charge | `350.00` | `SAR 350.00` |
| Encounter Balance row | `350.00 · 0.00 · 0.00 · 350.00` | `SAR 350.00 · SAR 0.00 · SAR 0.00 · SAR 350.00` |
| Payment row | `150.00` | `SAR 150.00` |
| Grand Total row | `600.00 · 150.00 · 0.00 · 450.00` | `SAR 600.00 · SAR 150.00 · SAR 0.00 · SAR 450.00` |

`text()` extraction of the live page (`GET /interface/reports/pat_ledger.php?form=1&patient_id=1&form_refresh=1`,
HTTP 200) shows `SAR` prepended to all 7 distinct amount instances on the page (line-item charges,
both `Encounter Balance` rows, the payment row shown twice — once as a credit, once under `{Pay
History}` — and the `Grand Total` row), with figures identical to PB-202's pre-patch numbers,
confirming this is additive formatting, not a data change.

**Negative control:** the `gbl_currency_symbol` global was independently confirmed set to `SAR` by
direct MariaDB query (`SELECT gl_value FROM globals WHERE gl_name = 'gbl_currency_symbol'` → `SAR`)
*before* editing any code, so the live rendering change is attributable to the code path now reading
that value, not to a coincidental global change made at the same time.

**Upstream-first path (Q1):** not filed. Omitting the `$symbol` argument is not itself an upstream
defect — `oeFormatMoney()`'s symbol-off default is deliberate elsewhere in the codebase (e.g. figures
embedded in prose where a symbol would read oddly). This screen's specific omission is a plain
oversight on a screen whose entire purpose is to display money, not a defect present across the
function's other 190+ call sites, so there is no single upstream fix that would apply generally.

**Verification:** `php -l` clean. Live HTTP 200 render inspected via `fetch()` in the authenticated
browser session and via DOM text extraction (`get_page_text`), both showing `SAR` on every amount;
figures cross-checked against `ar_activity`/`billing` table contents for `pid=1` and against PB-202's
own pre-patch numbers as the negative-control baseline.
