# 12 — FHIR / NPHIES Billing Evidence

_Auditor: opencode (senior software-archaeologist mode). Read-only. Fork SHA `631f2b38cf633769c305233f88cdf9c73ca80657`. Every claim cites `file:line`._

This report covers **two** mission-spec sections. The Q65 agent (this file) owns §13; the Q66 agent will append §14 below without editing §13.

- **§13 Q65 — BillingProcessor extension points** → this section.
- **§14 Q66 — ClaimRev protocol analysis** → placeholder header at bottom; Q66 agent appends there.

Prerequisite reading confirmed present in tree: `docs/discovery/openemr-decision-evidence/02-repository-baseline.md`, `docs/00-discovery/08-billing-claims-insurance.md`.

Evidence artefact accompanying this file: `evidence/snippets/q65-billing-call-graph.md` (class inventory + full call graph, cited file:line throughout).

---

## §13 Q65 — BillingProcessor extension points

### 1. Executive Q65 answer

The `BillingProcessor` orchestrator has a well-factored **internal OOP** interface hierarchy (`ProcessingTaskInterface` → `GeneratorInterface`; `AbstractProcessingTask` → `AbstractGenerator`) that a subclass **inside `src/Billing/BillingProcessor/Tasks/`** can extend cleanly. It has **zero external-module extension surface**: task selection is a hard-coded `if/elseif` ladder in `BillingProcessor::buildProcessingTaskFromPost()` at `src/Billing/BillingProcessor/BillingProcessor.php:161-192`, keyed on `$_POST['bn_*']` strings; there is no factory, registry, service-locator lookup, or event dispatch anywhere in `src/Billing/`. `GeneratorExternal` is a legacy `include` of `custom/BillingExport.php` (the referenced file is not shipped; the stub `custom/BillingExport.csv.php` instructs the operator to rename it) — not a supported module mechanism. Therefore **a NPHIES module cannot register a new billing generator without patching core** today; the cleanest upstream-friendly change is a small refactor of the `if/elseif` chain into a task-registry (POST-key → factory-callable map) plus one pre-dispatch Symfony event, both introduced in `BillingProcessor.php` alone.

### 2. BillingProcessor class inventory

Full 24-file table with kind, extends/implements, and extension-point verdict is in `evidence/snippets/q65-billing-call-graph.md` §1. Summary:

| Group | Count | Extension-suitable |
|-------|------:|--------------------|
| Interfaces | 4 (`ProcessingTaskInterface`, `GeneratorInterface`, `GeneratorCanValidateInterface`, local `LoggerInterface`) | ✅ contract exists |
| Abstract classes | 2 (`AbstractProcessingTask`, `AbstractGenerator`) | ✅ good base classes |
| Concrete task/generator leaves | 10 (`GeneratorX12`, `GeneratorX12Direct`, `GeneratorHCFA*` ×3, `GeneratorUB04*` ×3, `GeneratorExternal`, `TaskReopen`, `TaskMarkAsClear`) | leaf only |
| Orchestrator + support | 6 (`BillingProcessor`, `BillingClaim`, `BillingClaimBatch`, `BillingClaimBatchControlNumber`, `BillingLogger`, `X12RemoteTracker`) | orchestrator has no plugin hook |
| Trait | 1 (`WritesToBillingLog`) | mixin only |
| Legacy custom-include | 1 (`GeneratorExternal` → `custom/BillingExport.php`, file absent; stub only) | ⚠ non-standard |

No class is `final`; none is `readonly`. The local `LoggerInterface` is **not** `Psr\Log\LoggerInterface` — logging is screen-buffered via `BillingLogger`.

### 3. Call graph

Full step-by-step trace with `file:line` cites — from HTTP POST at `interface/billing/billing_process.php:32` through `BillingProcessor::execute()` → hard-coded task selection → `AbstractGenerator::execute()` action-branching → `GeneratorX12::generate()` → `X125010837P::genX12837P()` (`src/Billing/X125010837P.php:40`) → `BillingClaimBatch::write_batch_file()` (writes `sites/<site>/edi/<file>.txt`) → inline HTML render at `interface/billing/billing_process.php:52-56` — is in `evidence/snippets/q65-billing-call-graph.md` §2.

Key mutation sites (from that trace):
- Row-by-row auto-commit: `BillingUtilities::updateClaim(true,…,STATUS_MARK_AS_BILLED, BILL_PROCESS_IN_PROGRESS,…)` at `src/Billing/BillingProcessor/Tasks/GeneratorX12.php:151`.
- Second UPDATE writing `process_file` at `GeneratorX12.php:168`.
- File write: `BillingClaimBatch::write_batch_file()` at `GeneratorX12.php:202`.
- No dispatch, no transaction, no idempotency key — see §4.

### 4. Extension mechanism catalog

| mechanism | present | file:line | category |
|-----------|---------|-----------|----------|
| `ProcessingTaskInterface` | ✅ | `src/Billing/BillingProcessor/ProcessingTaskInterface.php:15` | internal_OOP |
| `GeneratorInterface extends ProcessingTaskInterface` | ✅ | `src/Billing/BillingProcessor/GeneratorInterface.php:18` | internal_OOP |
| `GeneratorCanValidateInterface` (marker) | ✅ | `src/Billing/BillingProcessor/GeneratorCanValidateInterface.php` | internal_OOP |
| local `LoggerInterface` (not PSR-3) | ✅ | `src/Billing/BillingProcessor/LoggerInterface.php` | internal_OOP |
| `AbstractProcessingTask` | ✅ | `src/Billing/BillingProcessor/Tasks/AbstractProcessingTask.php:18` | internal_OOP |
| `AbstractGenerator extends AbstractProcessingTask` | ✅ | `src/Billing/BillingProcessor/Tasks/AbstractGenerator.php:27` | internal_OOP |
| Task/generator factory | ❌ not found | `grep -R "class .*Factory" src/Billing/` → 0 hits | — |
| Task/generator registry | ❌ not found | `grep -R "class .*Registry\|registerTask\|addTask\|registerGenerator" src/Billing/` → 0 hits | — |
| Symfony EventDispatcher usage inside billing | ❌ not found | `grep -R "->dispatch(\|EventDispatcher" src/Billing/` → 0 hits | — |
| OpenEMR hook system (`->hook`, `invokeHook`) | ❌ not found | `grep -R "->hook\|invokeHook" src/Billing/` → 0 hits | — |
| `BillingProcessor` DI-registered in container | ❌ not found | `grep -R "BillingProcessor" src/Core/ config/` → 0 hits; instantiated directly at `interface/billing/billing_process.php:32` | — |
| Custom include on non-vendor path (from entrypoint) | ⚠ indirect | `GeneratorExternal.php:27-36` includes `custom/BillingExport.php` (file absent; stub `custom/BillingExport.csv.php:16` says "rename this file to BillingExport.php") | legacy_custom_file |
| Configuration-driven task registration | ❌ not found | no array/YAML/JSON task map anywhere | — |
| Subclass points actually used | ✅ | 11 hits, ALL inside `src/Billing/BillingProcessor/Tasks/`; **zero external module extends these classes** (`grep -R "extends AbstractGenerator\|extends BillingProcessor\|extends AbstractProcessingTask\|implements ProcessingTaskInterface\|implements GeneratorInterface" -- *.php`) | internal_OOP |
| Hardcoded task selection | ✅ present | `src/Billing/BillingProcessor/BillingProcessor.php:161-192` — 12-arm `if/elseif` on `isset($post['bn_*'])` (verbatim quote in call-graph §2 step 3.1) | — |
| Transaction boundaries around claim loop | ❌ none | `grep -R "beginTransaction\|->commit(\|rollBack" src/Billing/` → 0 hits | — |
| Idempotency keys / claim-attempt IDs | ❌ none | `grep -R "idempoten\|claim_attempt" src/Billing/` → 0 hits | — |

### 5. Q65 direct answers — 8 sub-questions

**Q65.1 — Can a custom module register a new billing processing task without changing core?**
**NO.** Task selection is the hard-coded ladder at `src/Billing/BillingProcessor/BillingProcessor.php:161-192`, keyed on well-known `$_POST['bn_*']` strings. Registering a new task requires editing this method to add another `elseif` arm, plus editing the billing-manager UI (`interface/billing/billing_process.php` and its form-render callers under `interface/billing/`) to submit a new `bn_*` key. No registry, no factory, no dispatch.

**Q65.2 — Can NPHIES be added through `GeneratorInterface` alone?**
Partially. The interface contract (`GeneratorInterface.php:18` + `ProcessingTaskInterface.php:15`: `setup(array)`, `execute(BillingClaim)`, `generate(BillingClaim)`, `completeToFile(array)`, `setAction($action)`) is granular enough to host a NPHIES bundle producer — a `GeneratorNphiesFhir` subclass of `AbstractGenerator` could serialize FHIR JSON in place of an X12 segment stream and POST to a NPHIES endpoint in `completeToFile()`. But implementing the interface is **insufficient by itself** because the class must still be selected by `BillingProcessor::buildProcessingTaskFromPost()` — which means adding an `elseif (isset($post['bn_nphies']))` arm to that method. So: yes on the class contract, no on end-to-end wiring without a core patch.

**Q65.3 — Is `GeneratorExternal` a supported extension mechanism or only a legacy custom include?**
**Only a legacy custom include.** `src/Billing/BillingProcessor/Tasks/GeneratorExternal.php:27-36` `include_once`s `"$webserver_root/custom/BillingExport.php"` (using `global $webserver_root` — flagged in `.phpstan/baseline/openemr.forbiddenGlobalKeyword.php:1742`). That file is **not present** in the repository. The shipped stub is `custom/BillingExport.csv.php`, whose header comment at line 16 reads `"To implement this feature, rename this file to BillingExport.php."` and declares `class BillingExport` at line 20. There is no PSR-4 namespace, no autoloader wiring, no versioned module manifest. This is a pre-Composer era escape hatch, not a modern extension mechanism.

**Q65.4 — Is there an event before or after claim clearing?**
**No.** `grep -R "->dispatch(\|EventDispatcher\|->hook\|invokeHook" src/Billing/` returns **zero hits**. Neither `BillingProcessor::execute()`/`processClaims()` nor any `Tasks/Generator*` class dispatches a Symfony event, PSR-14 event, or `oe_hook_*` hook before, during, or after the `BillingUtilities::updateClaim(…, STATUS_MARK_AS_BILLED, …)` calls at `GeneratorX12.php:151` and `:168`. Consistent with prior audit finding `docs/00-discovery/08-billing-claims-insurance.md` §4 Option B.

**Q65.5 — Is there a transaction boundary suitable for an outbox?**
**No.** `grep -R "beginTransaction\|->commit(\|rollBack\|StartTransaction" src/Billing/` returns **zero hits**. The claim loop at `BillingProcessor::processClaims()` (`BillingProcessor.php:126-148`) runs `$processing_task->execute($claim)` per claim; each `execute()` performs one or two auto-committed `BillingUtilities::updateClaim()` UPDATEs on the `billing` table. A crash mid-loop leaves partial state — some claims marked `billed=1`, some not — with no compensating action. There is no outbox table (the `billing` table itself is the state), and no wrapping DB transaction into which an outbox INSERT could be enlisted.

**Q65.6 — Where can idempotency be introduced?**
No native slot exists. `grep -R "idempoten\|claim_attempt\|uuid" src/Billing/BillingProcessor/` → only unrelated `unique_x12_partners` uses in `BillingClaimBatch.php:174-199`. The `billing` row has a unique PK (`id`) but no per-submission attempt UUID; the `claims` table versions on re-open but not per submission attempt. The nearest anchors are (a) `billing.process_file` (currently used to record batch-file name at `GeneratorX12.php:168`) — could be repurposed to carry a claim-attempt UUID stamped at `execute()` entry; (b) a new column `billing.claim_attempt_uuid`; or (c) a new sidecar table `billing_submission_attempts (id, billing_id, attempt_uuid, submitted_at, response_json)` written by a new task. Any of these is a schema change gated on core acceptance.

**Q65.7 — Which exact core changes would be required for a registry or event?**

Minimal, single-file change to introduce **both** a task registry and a pre-dispatch event:

- **`src/Billing/BillingProcessor/BillingProcessor.php`** — inject an `EventDispatcherInterface` and a `TaskRegistry` (or plain `array<string, callable>`) via the constructor (currently only `$post` is injected, line 69); replace the 12-arm `if/elseif` at lines 161-192 with a loop over registered `$_POST`-key → factory-callable mappings; before line 89 (`processClaims`), dispatch a new `ClaimAboutToBeProcessedEvent($processing_task, $claims)` and after line 89 dispatch a `ClaimProcessedEvent($processing_task, $claims)`. Preserve the current core-shipped keys as default registrations to avoid breaking `interface/billing/billing_process.php` callers.
- **`src/Events/Billing/` (new)** — two new event classes; namespace convention already established (`OpenEMR\Events\*`, per claimrev bootstrap imports listed at prior audit §5.1).
- **`interface/billing/billing_process.php`** — one line to fetch the container-registered `BillingProcessor` (currently `new BillingProcessor($_POST)` at line 32) so tests and modules can substitute.

A companion `src/Core/` change (or module bootstrap subscription) registers new tasks via the dispatcher's `RegisterBillingTasksEvent`, mirroring claimrev's existing subscription pattern at `interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php:96-109`. This is ~150 LoC across ~4 files including PHPDoc, and it leaves every existing shipped task's behaviour byte-identical (default registrations).

**Q65.8 — Could the change be submitted upstream cleanly?**

Medium risk. Positive signals: (a) the abstraction is already there — `ProcessingTaskInterface` + `GeneratorInterface` + `AbstractGenerator` — so a registry only formalises what the ladder does implicitly; (b) the existing tests in `tests/Tests/Isolated/Billing/` (`BillingClaimTest.php`, `BillingClaimBatchTest.php`, `BillingLoggerTest.php`) cover the collaborators but not `BillingProcessor` itself, so refactoring the orchestrator does not break existing tests. Negative signals: (a) no `BillingProcessorTest.php` exists — a PR must add one; (b) this subtree is heavily PHPStan-baselined (17 entries in `offsetAccess.nonOffsetAccessible.php` for `BillingProcessor.php` alone, plus entries in 8 other baseline files), and the project standard (`CLAUDE.md` "Avoid baselines") requires fixing baseline entries when touching a file, expanding the PR scope; (c) upstream (`openemr/openemr`) has never accepted a Symfony event in the billing subsystem — prior audit §4 Option B confirms zero `Billing\*` or `Claim\*` events across the codebase — so it would be a first-of-its-kind for reviewers; (d) the file has 6 named authors (`BillingProcessor.php:28-33`) and a claimed lineage back to legacy `billing_process.php` refactor, so a rewrite invites review from multiple maintainers.

Risk-per-file: `BillingProcessor.php` **HIGH** (heavily baselined, no tests, multi-author); new event classes **LOW** (greenfield); `billing_process.php` **LOW** (single-line change).

### 6. Recommended NPHIES attachment strategy — findings only

Three options were catalogued in the prior audit (`docs/00-discovery/08-billing-claims-insurance.md` §4). Re-evaluated against the code inspected here:

| Option | Code support today | Verdict |
|--------|--------------------|---------|
| **A — FHIR Claim controller ADD** (new `FhirClaimRestController` + `FhirClaimService`, module submits Claim resources server-to-server) | Zero. Prior audit §4 Option A: no `FhirClaim*` file in tree; `FhirCoverageRestController` is read-only (no `create/update`); currency hard-coded to USD at `src/Services/FHIR/FhirCoverageService.php:294`. New surface must be built greenfield, and it does not integrate with the `billing` state machine unless additionally reconciled back. | Greenfield; does not reuse `BillingProcessor` at all. |
| **B — Custom module POLLING like ClaimRev** (background service reads `billing WHERE bill_process=1 AND billed=0`, projects to NPHIES FHIR, POSTs, writes `ar_session`/`ar_activity` back) | HIGH. `background_services` scheduling is proven by claimrev's six inserts (prior audit §5.2). The `billing` state machine is queryable from SQL and its mutation vocabulary (`BillingClaim::STATUS_*`, `BILL_PROCESS_*` constants at `BillingClaim.php:22-29`) is publicly stable. No core change needed to *read* state. | **Most supported by the code as it stands today.** Requires zero patches to `src/Billing/BillingProcessor/`. |
| **C — PARALLEL SERVICE reading billing table** (essentially B, but without the background_services wrapper — e.g. cron) | Same as B minus the scheduler. Same code-support level. | Equivalent to B. |

**Conclusion (findings only):** The code most-supports **Option B** — a background-service module that treats `BillingProcessor` as opaque, produces NPHIES output out-of-band, and writes results back through `ar_session`/`ar_activity`. This is what claimrev did, and it is what the current `BillingProcessor` design permits without any core edit. Adding a native `GeneratorNphies` (Option A-variant that plugs into `BillingProcessor`) is architecturally cleaner but blocked by Q65.1 = NO: it requires the small registry-plus-event refactor described in Q65.7 to be accepted upstream first. In the meantime, Option B is unblocked.

### 7. UNKNOWNs (§13-scope)

1. Whether `GeneratorX12Direct`'s `gen_x12_based_on_ins_co` global (`BillingProcessor.php:165, 168`) implies additional undocumented insurance-company-scoped state that a NPHIES generator would also need — not read in this pass (deferred; `GeneratorX12Direct.php` shows 4 `offsetAccess.nonOffsetAccessible` baseline entries suggesting non-trivial DB shape).
2. Whether `BillingUtilities::updateClaim()` — called from every generator's `generate()` — has any hook or callback surface that could be used *in lieu of* a `BillingProcessor`-level event. Not inspected here (`src/Billing/BillingUtilities.php`); prior audit §1 stage 8 references it at lines 1492-1519 as documenting state transitions.
3. Whether the `interface/billing/` form-render pages that submit `bn_*` keys use a shared form-config table or are hard-coded per-file — determines the UI surface a new task-key would need to touch.
4. Whether there is a menu/ACL entry gate that would also need to be extended for a new `bn_nphies` button (ACL v13 per baseline).
5. Whether an upstream PR to add a task registry would need to preserve exact `$_POST` key names for backward compatibility with third-party themes/UI customisations — an ecosystem question, not answerable from this repo alone.

---

## §14 Q66 — ClaimRev protocol analysis

_Placeholder for the Q66 agent. Q66's investigation, findings, and evidence-cites will be appended below this line — do not edit above._
