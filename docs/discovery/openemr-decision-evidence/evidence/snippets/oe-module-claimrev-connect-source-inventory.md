# oe-module-claimrev-connect — source inventory

**Package:** `claimrevolution/oe-module-claimrev-connect`
**Version:** `v2.1.6` (composer.lock:427)
**Source:** `https://github.com/claimrevolution/oe-module-claimrev-connect.git` (composer.lock:430)
**Pinned SHA:** `978b0dd498e0e166992259926d6fa77bf56266d4` (composer.lock:431)
**Zipball:** `https://api.github.com/repos/claimrevolution/oe-module-claimrev-connect/zipball/978b0dd498e0e166992259926d6fa77bf56266d4` (composer.lock:435)
**License:** GPL-3.0 (composer.lock:453)
**Author:** Brad Sharp <brad.sharp@claimrev.com> (composer.lock:457-459)
**Package time:** 2026-06-23T16:07:14+00:00 (composer.lock:473)
**PHP require:** `>=7.1`; PSR-4 root `OpenEMR\Modules\ClaimRevConnector\` → `src/` (composer.lock:442,448)
**Deps:** `nyholm/psr7 ^1.4`, `openemr/oe-module-installer-plugin ^0.1.0`, `symfony/event-dispatcher ^4.4|^5|^6|^7` (composer.lock:440-443)

**Vendor state on this fork:** `vendor/` is empty (see `docs/discovery/openemr-decision-evidence/02-repository-baseline.md`). `interface/modules/custom_modules/.gitignore:15` ignores `oe-module-claimrev-connect` — module is composer-installed only, source is not tracked in the fork. Source acquisition here is via GitHub API at the pinned SHA, per §1.5 read-only rule.

## Tree summary (134 blob entries)

- Total files fetched from GitHub tree API: 134 (see `evidence/raw/claimrev-source-tree-listing.txt`).
- Top-level: `openemr.bootstrap.php`, `ModuleManagerListener.php`, `moduleConfig.php`, `composer.json`, `info.txt`, `README.md`, `CHANGELOG.md`, `table.sql`, `cleanup.sql` (empty).
- `src/`: 66 PHP classes — API client, background-service entry points, services, DTOs, compat shims, UI page controllers.
- `public/`: 33 PHP admin/API endpoints (claims, eligibility, ERA, denial analytics, payment advice, x12 tracker, etc.) + assets.
- `templates/`: 22 PHP partials (eligibility rendering, chat UI, etc.).

## Key files fetched to `evidence/raw/claimrev-source/`

Path-mapping: `/` in the repo → `__` in the local filename.

### Bootstrap / setup / config
- `openemr.bootstrap.php` (887 B) — module entry called by OpenEMR core; instantiates `Bootstrap` and calls `subscribeToEvents()`.
- `ModuleManagerListener.php` (5,325 B) — install/enable/disable/uninstall hooks.
- `moduleConfig.php` (959 B) — Zend module descriptor.
- `src/Bootstrap.php` (13,241 B) — event wiring, menu item, globals section, template overrides.
- `src/GlobalConfig.php` (16,435 B) — typed accessors for OEGlobalsBag settings.
- `src/ClaimRevModuleSetup.php` (12,881 B) — creates `x12_partners` row, activates `X12_SFTP` background service, resets stuck services.

### Protocol / API
- `src/ClaimRevApi.php` (20,998 B) — Guzzle-based REST client, OAuth2 client-credentials token acquisition, all API verbs.
- `src/ClaimRevApiException.php`, `src/ClaimRevAuthenticationException.php`, `src/ClaimRevException.php` — typed exceptions.
- `src/RevenueToolsRequest.php`, `src/RevenueToolsPayer.php` — request DTOs for the SharpRevenue eligibility endpoint.
- `src/UploadEdiFileContentModel.php` — DTO wrapping raw EDI file contents for upload.

### Background services (`background_services` rows)
- `src/Billing_Claimrev_Service.php` — global fns `start_X12_Claimrev_send_files` / `start_X12_Claimrev_get_reports`.
- `src/Eligibility_ClaimRev_Service.php` — global fn `start_send_eligibility`.
- `src/Eligibility_Sweep_Service.php` — global fn `start_eligibility_sweep` → `EligibilitySweepService::run()`.
- `src/ClaimRev_Notification_Service.php` — global fn `start_claimrev_notifications` → `NotificationPollService::run()`.
- `src/ClaimRev_Watchdog_Service.php` — global fn `start_claimrev_watchdog` → `ClaimRevModuleSetup::resetStuckServices()`.
- `src/SFTP_Mock_Service.php` — global fn `start_X12_SFTP` (mock retained for compat with core's `X12_SFTP` service row).

### Claim/ERA/eligibility workhorses
- `src/ClaimUpload.php` (4,930 B) — reads `x12_remote_tracker` rows in `waiting`, POSTs raw EDI to `/api/InputFile/v1`.
- `src/ReportDownload.php` — pulls report files back down.
- `src/ClaimStatusSyncService.php` (8,984 B) — maps ClaimRev status IDs to OpenEMR `claims.status`.
- `src/ClaimTrackingService.php` (26,554 B) — writes `mod_claimrev_claims` + `mod_claimrev_claim_events`.
- `src/EligibilitySweepService.php` (4,083 B) — nightly proactive queueing.
- `src/NotificationPollService.php` (5,039 B) — polls `/api/NotificationMgmt/v1/GetPortalNotifications`, mirrors to `pnotes`.
- `src/PaymentAdvicePostingService.php` (36,073 B) — largest single file; posts adjudication results into `ar_session`/`ar_activity` via `OpenEMR\Billing\SLEOB`.
- `src/ReconciliationService.php` (16,365 B) — reconciles ERA payments against posted activity.
- `src/EligibilityObjectCreator.php` (11,042 B) — constructs the SharpRevenue eligibility request payload.
- `src/EligibilityTransfer.php` — send loop for waiting eligibility rows.

### SQL / schema
- `table.sql` (8,909 B) — creates `mod_claimrev_eligibility`, `mod_claimrev_notifications`, `mod_claimrev_claims`, `mod_claimrev_claim_events`, `mod_claimrev_patient_statements`, `mod_claimrev_version_check`; inserts five `background_services` rows.
- `cleanup.sql` (0 B) — empty stub.

### Compat shims (bridge for older OpenEMR cores)
- `src/Compat/compat.php`, `src/Compat/KernelCompat.php`, `src/Compat/CryptoInterfaceShim.php`, `src/Compat/OEGlobalsBagShim.php`, `src/Compat/ServiceContainerShim.php`.
