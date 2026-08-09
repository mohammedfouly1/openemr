# Q65 — BillingProcessor class + call graph

_Read-only evidence artefact. Fork SHA `631f2b38…`. Every claim cites `file:line`._

## Class inventory — `src/Billing/BillingProcessor/`

`git ls-tree -r HEAD -- src/Billing/BillingProcessor/` → 24 files.

| # | File | Kind | Extends / implements | Extension-point? |
|--:|------|------|----------------------|------------------|
| 1 | `BillingProcessor.php:49` | concrete class | none | ❌ orchestrator; hard-coded if/elseif on `$_POST` keys (lines 161-192) |
| 2 | `BillingClaim.php:20` | concrete class | `\JsonSerializable` | ❌ POD-ish DTO parsed from POST |
| 3 | `BillingClaimBatch.php` | concrete class | none | ❌ file-writer helper |
| 4 | `BillingClaimBatchControlNumber.php` | concrete class | none | ❌ counter |
| 5 | `BillingLogger.php` | concrete class | `LoggerInterface` (local, not PSR-3) | ❌ screen buffer |
| 6 | `ProcessingTaskInterface.php:15` | **interface** | — | ✅ 3 methods: `setup(array)`, `execute(BillingClaim)`, `complete(array)` |
| 7 | `GeneratorInterface.php:18` | **interface** | `extends ProcessingTaskInterface` | ✅ adds `setAction()`, `generate(BillingClaim)`, `completeToFile(array)` |
| 8 | `GeneratorCanValidateInterface.php` | **interface** | — | ✅ marker interface for validation-capable generators |
| 9 | `LoggerInterface.php` | **interface** (local — NOT `Psr\Log\LoggerInterface`) | — | ✅ log injection contract used by tasks |
| 10 | `X12RemoteTracker.php` | concrete class | none | ❌ SFTP status tracker |
| 11 | `Tasks/AbstractProcessingTask.php:18` | **abstract class** | — | ✅ base for non-generator tasks; provides `clearClaim()` (line 47) |
| 12 | `Tasks/AbstractGenerator.php:27` | **abstract class** | `extends AbstractProcessingTask implements GeneratorInterface` | ✅ base for file-generator tasks; implements action-branching `execute()` (44) and `complete()` (76) |
| 13 | `Tasks/GeneratorX12.php:35` | concrete | `extends AbstractGenerator implements GeneratorInterface, GeneratorCanValidateInterface, LoggerInterface` | (leaf) |
| 14 | `Tasks/GeneratorX12Direct.php:34` | concrete | same triple | (leaf) — ins-co-scoped variant |
| 15 | `Tasks/GeneratorHCFA.php:29` | concrete | `extends AbstractGenerator …` | (leaf) — CMS-1500 text |
| 16 | `Tasks/GeneratorHCFA_PDF.php:30` | concrete | same | (leaf) |
| 17 | `Tasks/GeneratorHCFA_PDF_IMG.php` | concrete | same | (leaf) |
| 18 | `Tasks/GeneratorUB04X12.php:30` | concrete | same | (leaf) — institutional X12 |
| 19 | `Tasks/GeneratorUB04NoForm.php:28` | concrete | `extends AbstractGenerator implements GeneratorInterface, LoggerInterface` | (leaf) |
| 20 | `Tasks/GeneratorUB04Form_PDF.php:28` | concrete | same | (leaf) |
| 21 | `Tasks/GeneratorExternal.php:21` | concrete | `extends AbstractGenerator implements GeneratorInterface, LoggerInterface` | ⚠ legacy custom-include (see below) |
| 22 | `Tasks/TaskReopen.php:21` | concrete | `extends AbstractProcessingTask implements ProcessingTaskInterface, LoggerInterface` | (leaf) — reopens claim |
| 23 | `Tasks/TaskMarkAsClear.php:20` | concrete | same | (leaf) — marks billed |
| 24 | `Traits/WritesToBillingLog.php` | trait | — | helper mixin |

No class in the tree is `final`. No class in the tree is `readonly`.

## Legacy custom-include mechanism — `GeneratorExternal`

`src/Billing/BillingProcessor/Tasks/GeneratorExternal.php:27-36`:

```php
public function setup($context = null)
{
    global $webserver_root;
    $EXPORT_INC = "$webserver_root/custom/BillingExport.php";
    if (file_exists($EXPORT_INC)) {
        include_once($EXPORT_INC);
        $BILLING_EXPORT = true;
    }
    $this->be = new \BillingExport();
}
```

- Uses `global $webserver_root` (baseline entry `.phpstan/baseline/openemr.forbiddenGlobalKeyword.php:1742`).
- Include target `custom/BillingExport.php` is **not present** in the repo (`Test-Path` → False). The shipped stub is `custom/BillingExport.csv.php` whose header (line 16) reads: `"To implement this feature, rename this file to BillingExport.php."` and declares `class BillingExport` (line 20).
- Triggered only when POST contains `bn_external` (`BillingProcessor.php:190`).
- Category: **legacy custom-include, not a supported module extension mechanism.** No PSR-4 namespace, no autoloader hook, no service-locator; deployment requires file rename in the webroot. Unfit as a NPHIES attachment surface.

## Call graph — Fee-sheet submit → EDI file on disk

```
1. HTTP POST  interface/billing/billing_process.php
   ├─ auth/session:  require_once("../globals.php")                       interface/billing/billing_process.php:21
   ├─ CSRF:          CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true)                          :29
   └─ inputs:        $_POST['claims'][<pid-enc>]['bill'|'partner'|'payer'],
                     $_POST['bn_*']   (bn_x12, bn_x12_encounter, bn_hcfa_txt_file,
                                        bn_process_hcfa, bn_process_hcfa_form,
                                        bn_ub04_x12, bn_process_ub04, bn_process_ub04_form,
                                        bn_reopen, bn_mark, bn_external),
                     $_POST['btn-clear' | 'btn-validate' | 'btn-continue']

2. new BillingProcessor($_POST)                                            interface/billing/billing_process.php:32
   └─ ctor:  $this->logger = new BillingLogger()                           src/Billing/BillingProcessor/BillingProcessor.php:72

3. $billingProcessor->execute()                                            interface/billing/billing_process.php:33
   └─ BillingProcessor::execute()                                          src/Billing/BillingProcessor/BillingProcessor.php:78

   3.1 $processing_task = $this->buildProcessingTaskFromPost($_POST)       :81
       └─ HARDCODED if/elseif ladder on $_POST['bn_*']                     :161-192
           • bn_reopen           → new Tasks\TaskReopen                    :162
           • bn_mark             → new Tasks\TaskMarkAsClear               :164
           • bn_x12 + gen_x12_based_on_ins_co global    → GeneratorX12Direct   :165-167
           • bn_x12_encounter + gen_x12_based_on_ins_co → GeneratorX12Direct   :168-170
           • bn_x12              → new Tasks\GeneratorX12                  :171-173
           • bn_x12_encounter    → new Tasks\GeneratorX12 (encounter_claim=true) :174-176
           • bn_hcfa_txt_file    → new Tasks\GeneratorHCFA                 :177-178
           • bn_process_hcfa     → new Tasks\GeneratorHCFA_PDF             :179-180
           • bn_process_hcfa_form→ new Tasks\GeneratorHCFA_PDF_IMG         :181-182
           • bn_ub04_x12         → new Tasks\GeneratorUB04X12              :183-185
           • bn_process_ub04     → new Tasks\GeneratorUB04NoForm           :186-187
           • bn_process_ub04_form→ new Tasks\GeneratorUB04Form_PDF         :188-189
           • bn_external         → new Tasks\GeneratorExternal             :190-191
       └─ optional: $processing_task->setLogger($this->logger)             :197-199

   3.2 $claims = $this->prepareClaims($processing_task->getAction())      :84
       └─ foreach $_POST['claims'] where isset(['bill'])                  :106-121
           - new BillingClaim($claimId, $partner_and_payor, $action)      :112
           - drops unassigned partners (unless bn_x12 set — see :113-117)

   3.3 processClaims($processing_task, $claims)                            :89 → :126
       ├─ $processing_task->setup(['claims'=>…, 'post'=>…])                :130
       │   e.g. GeneratorX12::setup() → new BillingClaimBatch('.txt', $ctx) src/Billing/BillingProcessor/Tasks/GeneratorX12.php:96
       │
       ├─ foreach $claims:  $processing_task->execute($claim)              :139
       │   ├─ AbstractGenerator::execute()                                 src/Billing/BillingProcessor/Tasks/AbstractGenerator.php:44
       │   │   ├─ if VALIDATE_ONLY       → $this->validateOnly($claim)     :47-48
       │   │   ├─ if VALIDATE_AND_CLEAR  → $this->validateAndClear($claim) :49-50
       │   │   └─ else (NORMAL/null)     → $this->generate($claim)         :54-59
       │   │
       │   └─ GeneratorX12::generate($claim)                               src/Billing/BillingProcessor/Tasks/GeneratorX12.php:149
       │       ├─ BillingUtilities::updateClaim(true,…,STATUS_MARK_AS_BILLED,BILL_PROCESS_IN_PROGRESS)  :151-162
       │       │   → SQL UPDATE billing SET billed=1, bill_process=1,…
       │       ├─ updateBatchFile($claim)                                  :165
       │       │   ├─ X125010837P::genX12837P(pid,enc,partner,…)           :70    → src/Billing/X125010837P.php:40
       │       │   ├─ $this->batch->append_claim($segs)                    :81
       │       │   └─ $this->batch->addClaim($claim)                       :85
       │       └─ BillingUtilities::updateClaim(false,…,2,2,batFilename)   :168
       │           → SQL UPDATE billing SET process_file=<batFilename>
       │
       └─ $processing_task->complete(['claims'=>…, 'post'=>…])             :144
           └─ AbstractGenerator::complete()                                src/Billing/BillingProcessor/Tasks/AbstractGenerator.php:76
               ├─ if VALIDATE_* → $this->completeToScreen($ctx)            :78-84
               └─ else         → $this->completeToFile($ctx)              :87-92
                   └─ GeneratorX12::completeToFile()                       src/Billing/BillingProcessor/Tasks/GeneratorX12.php:199
                       ├─ $this->batch->append_claim_close()               :201
                       ├─ $this->batch->write_batch_file()                 :202
                       │   → writes sites/<site>/edi/<file>.txt
                       └─ if !global 'auto_sftp_claims_to_x12_partner':
                             logger callback prints JS to download file    :211-217

4. Return path
   └─ return $processing_task->getLogger()                                 src/Billing/BillingProcessor/BillingProcessor.php:94
      → $logger consumed by interface/billing/billing_process.php:33
      → screen rendered inline in same PHP page (no redirect); :52-56 loop $logger->bill_info()
      → $logger->onLogComplete() at :63 (invokes any deferred download-JS)
```

### Zero-dispatch and zero-transaction observations

- `grep -R "->dispatch(\|EventDispatcher\|->hook\|invokeHook" src/Billing/` → **0 hits.** No Symfony event, no `oe_hook_dispatch`, no PSR-14 dispatch anywhere in the `src/Billing/` tree — before, during, or after claim generation.
- `grep -R "beginTransaction\|->commit(\|rollBack\|StartTransaction" src/Billing/` → **0 hits.** No transactional boundary wraps the claim loop. Every `BillingUtilities::updateClaim()` mutation is auto-committed row-by-row. If the PHP process is killed mid-loop, `billing` rows already updated remain `billed=1, bill_process=1` while later rows remain `bill_process=0` and the file may or may not have been written. There is no outbox pattern and no compensating action.
- `grep -R "class .*(Factory|Registry)\|registerTask\|addTask\|registerGenerator" src/Billing/` → **0 hits.** No factory, no registry, no task-map array. Task selection is exclusively the string-matching if/elseif chain quoted above.

### Idempotency observations

- No claim-attempt ID or nonce is stored on the `billing` row per attempt. `grep -R "idempoten\|claim_attempt" src/Billing/` → 0 hits.
- The `claims` table (see prior audit `docs/00-discovery/08-billing-claims-insurance.md` §1 stage 3-4) versions on re-open via composite key `(pid, encounter, version)` but does not carry a per-submission UUID.
- Consequence: a network-retry of the exact same POST re-runs the whole pipeline; the only guard is the `billed=1` filter in `prepareClaims()` upstream selecting the claim set — the processor itself is not idempotent by construction.

### Dependency-injection observations

- `grep -R "BillingProcessor" src/Core/ config/` → **0 hits.** The class is not container-registered; it is directly `new`-ed at `interface/billing/billing_process.php:32`.
- `BillingProcessor::__construct(protected $post)` takes only the raw POST — no logger, no dispatcher, no clock, no partner repository injected. All collaborators (`BillingLogger`, tasks, `X125010837P`, `BillingUtilities`, `SessionWrapperFactory`, `OEGlobalsBag`) are located via `new` or static calls inside the class body.

### PHPStan-baseline debt in this subtree

`.phpstan/baseline/offsetAccess.nonOffsetAccessible.php` has **17 entries** targeting `BillingProcessor.php`, plus 5 for `X12RemoteTracker.php`, 4 for `GeneratorHCFA_PDF.php`, 4 for `GeneratorX12Direct.php`, and 2 each for `BillingClaim.php`, `BillingClaimBatch.php`, `GeneratorHCFA_PDF_IMG.php`. Combined with entries in `return.type`, `missingType.return`, `openemr.deprecatedSqlFunction`, `postInc.type`, and `openemr.forbiddenGlobalKeyword`, this subtree is one of the more heavily-baselined areas in the codebase. Any upstream PR touching it will need to fix (not append to) the baseline, per project standards.

### Test coverage in this subtree

`tests/Tests/Isolated/Billing/`:
- `BillingClaimTest.php` — covers the DTO (POST parsing).
- `BillingClaimBatchTest.php` — covers the batch file assembler.
- `BillingLoggerTest.php` — covers the log buffer.
- **No `BillingProcessorTest.php`.** The orchestrator itself — the class where a registry or event dispatch would land — has zero unit tests.
- No test covers any `Tasks/Generator*` class.
