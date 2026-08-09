# 21 — Recommended Decision Updates

Where this audit's evidence **changes** a previously-suggested decision. Evidence and recommendation are
kept separate throughout, per the audit rules.

Two classes:

- **§1 Contradicted** — the repository refutes the premise. The decision must change.
- **§2 Materially re-scoped** — the decision direction survives, but its cost, scope or sequencing changes
  enough to affect planning.

Decisions whose evidence simply *confirmed* the suggested default are not repeated here; see
`03-question-status-matrix.csv`.

---

## §1 Contradicted decisions (4)

### Q50 — "Add `SECURITY.md` and `dependabot.yml`" → **already done; the real task is different**

| | |
|---|---|
| **Recommended option** | Close the original item as satisfied. Open a new one: rewrite `SECURITY.md` for the fork's own disclosure channel. |
| **Why** | Both files already ship, plus a `dependabot-auto-merge.yml` workflow. As shipped, `SECURITY.md` directs vulnerability reporters to **OpenEMR's** security team and PGP key — wrong, and actively harmful, for a fork holding Saudi tenant PHI. A reporter following it would disclose our vulnerability to a third party. |
| **Evidence** | `.github/SECURITY.md`, `.github/dependabot.yml`, `.github/workflows/dependabot-auto-merge.yml` |
| **Alternatives rejected** | Leaving it as-is (mis-routes disclosures); deleting it (worse than a wrong address). |
| **Trade-offs** | Rewriting forks a file from upstream — a small permanent rebase conflict surface. Accepted: correct disclosure routing outweighs one trivial conflict. |
| **Cost** | ~1 hour. |
| **Upgrade/drift impact** | One small recurring conflict in `.github/SECURITY.md`. |
| **Security impact** | **High positive** — closes a mis-routed disclosure path. |
| **Tenant-isolation impact** | None. |
| **MVP impact** | Should land before the fork is public. |
| **Migration path** | None needed. |
| **Confidence** | CONFIRMED |

### Q47 — "Force `drive_encryption` on" → **it is already on by default**

| | |
|---|---|
| **Recommended option** | No code change. Pin `drive_encryption=1` and `database_encryption=1` in provisioning defaults and assert both in a tenant health check. |
| **Why** | `library/globals.inc.php:1035-1040` shows `drive_encryption` defaulting to `'1'`; `:1028-1032` shows the same for `database_encryption`. The prior note recorded the upstream default as unverified. Converts a proposed core patch into a configuration assertion. |
| **Evidence** | `library/globals.inc.php:1028-1040`; consumed at `src/BC/Crypto/Crypto.php:65`, `src/Common/Crypto/CryptoGen.php:82` |
| **Alternatives rejected** | Patching the default (redundant — it is already the default). |
| **Trade-offs** | An operator can still turn it off; hence the health-check assertion rather than trusting the default. |
| **Cost** | Near zero. |
| **Upgrade/drift impact** | **Zero** — no patch to carry. |
| **Security impact** | Positive (makes an implicit guarantee explicit). |
| **MVP impact** | Trivial; do it with provisioning. |
| **Confidence** | CONFIRMED |
| **Caution** | Do **not** conclude PHI columns are encrypted. See Q69 in §2. |

### Q59 — "`sites/<tenant>/documents/theme/` is the override path" → **the path does not exist**

| | |
|---|---|
| **Recommended option** | Deliver per-tenant branding as **CSS-variable design tokens over a shared immutable bundle**, plus per-site logos via `LogoService`. Never per-tenant CSS or JS files. |
| **Why** | That path has **zero** runtime references across PHP, Twig and JS — it existed only in our own prior notes. No per-site `.css`/`.js` is included at runtime anywhere. The theme selector is a filename chosen from `public/themes/`, guarded by a `file_exists()` check (`interface/globals.php:476`). |
| **Evidence** | `git grep 'documents/theme'` → 0; `src/Services/LogoService.php:75-108`; `interface/globals.php:474-483,649` |
| **Alternatives rejected** | Per-tenant CSS files — the loader does not exist, and building one would introduce tenant-supplied content into the page: an XSS, data-exfiltration and cross-tenant asset-leakage vector. |
| **Trade-offs** | Tokens constrain branding to a validated palette rather than arbitrary CSS. That constraint is the security property, not a limitation to work around. |
| **Cost** | Small — a token layer plus a validated per-tenant palette record. |
| **Upgrade/drift impact** | Low; tokens live in our own bundle. |
| **Security impact** | **High positive** — forecloses arbitrary tenant CSS/JS by design. |
| **Tenant-isolation impact** | Positive — shared immutable bundle cannot leak between tenants; revision the bundle URL to avoid cache poisoning. |
| **MVP impact** | **Unblocks Q34**, which was waiting on this. |
| **Confidence** | CONFIRMED |

### Q10 — "CapabilityStatement lies about standalone-encounter launch" → **it does not; a different defect exists**

| | |
|---|---|
| **Recommended option** | Split into two items. **(a)** Immediately resolve the advertised-vs-grantable inconsistency: either add `launch/encounter` to both scope lists or stop advertising `context-ehr-encounter`. **(b)** Treat the encounter picker as a separate deliverable tied to NPHIES. |
| **Why** | `context-standalone-encounter` is explicitly **commented out** at `Capability.php:50` and its constant (`:103`) is never referenced — so there is no conformance lie. The genuine defect is narrower: `CONTEXT_EHR_ENCOUNTER` **is** advertised (`:48`) while the `launch/encounter` scope is absent from both grantable scope lists. Shipped documentation (`SMART_ON_FHIR.md:793,845`) shows requests that would fail. |
| **Evidence** | `src/FHIR/SMART/Capability.php:48,50,103`; `ServerScopeListEntity.php:53`; `ScopeRepository.php:248`; `API_README.md:198` |
| **Alternatives rejected** | Building the full picker first — item (a) is a two-line fix that removes a real conformance defect immediately. |
| **Trade-offs** | Adding the scope without context wiring would advertise more than is delivered; hence "add scope **and** wire context, or stop advertising". |
| **Cost** | (a) hours; (b) 1–2 sprints. |
| **Security impact** | Neutral. |
| **MVP impact** | (a) before any SMART app onboards; (b) with NPHIES. |
| **Confidence** | CONFIRMED |

---

## §2 Materially re-scoped decisions (7)

### Q11 — Model A vs Model B: the cost gap is **3.6× larger** than assumed

| | |
|---|---|
| **Recommended option** | **Lock Model A (DB-per-tenant).** |
| **Why** | The Model B estimate rested on 1,875 `sqlStatement(` sites. The measured data-access surface is **6,785**: `sqlStatement(` 2,025 + `QueryUtils::` **1,653** (previously uncounted, closing Q73) + `sqlQuery(` 1,454 + `sqlFetchArray(` 1,354 + `sqlInsert(` 251 + Doctrine DBAL 48 — plus 202 `OE_SITE_DIR` file-path sites. No core table has a tenant discriminator. |
| **Evidence** | `evidence/raw/remaining-counts.tsv`; per-sink match lists; `evidence/manifests/remaining-counts-sha256.txt` |
| **Alternatives rejected** | Model B — a multi-quarter rewrite of 6,785 call sites plus schema migration plus every service-layer join, against a runtime that already implements Model A. |
| **Trade-offs** | Model A costs per-tenant ops burden (N cron jobs, N backups, N connection pools) and makes cross-tenant analytics an ETL (Q15). |
| **Cost** | Model A: provisioning automation. Model B: multi-quarter. |
| **Upgrade/drift impact** | Model A requires only a small `globals.php` routing patch; Model B would fork the data layer permanently. |
| **Tenant-isolation impact** | Model A gives physical isolation — the strongest available. |
| **Migration path** | A→B remains theoretically possible later; B→A is not. Choosing A preserves optionality. |
| **Confidence** | CONFIRMED |

### Q69 — Encryption inventory: **the headline count is misleading**

| | |
|---|---|
| **Recommended option** | State the encryption posture accurately: **file-level plus storage-level, never column-level.** If column-level PHI encryption is legally required, scope it as a new build, not a configuration change. |
| **Why** | Of 36 `encryptStandard(` sites, most are tests, PHPStan baselines and the crypto library's own interface. Real application paths protect **only** credentials and tokens: SMART launch tokens (`SMARTLaunchToken.php:132`), key wrapping (`CryptoGen.php:482`), fax media tokens and vendor credentials, and the phone gateway password. `patient_data`, `form_encounter`, `billing` and `documents` metadata are **plaintext columns**. |
| **Evidence** | `evidence/raw/count-encryptstandard.txt`, `count-decryptstandard.txt`; `library/globals.inc.php:1028-1040` |
| **Alternatives rejected** | Reporting "36 encryption sites" as PHI coverage — that would misrepresent the posture to an auditor. |
| **Trade-offs** | Accurate reporting may surface a compliance gap that was previously assumed closed. That is the point. |
| **Security impact** | **Critical clarification.** Prevents a false compliance claim. |
| **Confidence** | CONFIRMED for classification; HIGH for "no core PHI column encryption" (a bespoke path under a different function name would not be caught by a two-name grep). |

### Q68 — Audit checksum: the gap is **construction, not cadence**

| | |
|---|---|
| **Recommended option** | Do **not** schedule a verifier for a non-chain. Rebuild as a `saas_` overlay with all three properties — `hash_hmac` keyed by material the DB user cannot read, inclusion of the previous row's hash, and a nightly verifier — then schedule it. |
| **Why** | `LogTablesSink.php:63` computes `hash('sha3-512', implode('', array_values($logData)))` over the row's own fields. No previous-row hash (not a chain), no key (not an HMAC). Anyone with UPDATE on `log` can edit a row and recompute a valid checksum in one line. |
| **Evidence** | `src/Common/Logging/Audit/LogTablesSink.php:63-91`; `EventAuditLogger.php:670-671`; verifier grep → 0 |
| **Alternatives rejected** | Adding a nightly verifier alone — it would validate hashes an attacker can trivially regenerate: security theatre. |
| **Trade-offs** | Key custody becomes a new operational requirement (ties to Q44). |
| **Cost** | Medium — overlay table, write hook, verifier job, key custody. |
| **Upgrade/drift impact** | Low if built as an overlay rather than a core patch. |
| **Security impact** | **High** — converts a corruption checksum into genuine tamper-evidence. |
| **Confidence** | CONFIRMED |

### Q43 — Token exposure is **6× wider** and defeats naive scanners

| | |
|---|---|
| **Recommended option** | Treat all as burned; report upstream; guarantee containment; deploy a scanner that detects base64 and decimal-encoded variants, not just `ghp_`. |
| **Why** | Not 2 tokens in 1 file but **12 values across 4 compose files**, in three obfuscation layers decoded inline by `docker/flex/openemr.sh:766-790`. A `ghp_`-prefix scanner catches one third — which is exactly why only one file was previously found. |
| **Evidence** | `docker/development-easy{,-light,-redis}/docker-compose.yml`, `docker/development-insane/docker-compose.yml`, `docker/flex/openemr.sh` (all values `[REDACTED]`) |
| **Alternatives rejected** | Rotating them ourselves — they are **upstream's** tokens; the fork has zero own commits. Rotation is upstream's action; containment is ours. |
| **Trade-offs** | Scrubbing them in-fork creates a permanent rebase conflict in four files. Recommend scrubbing at **image-build** time instead, keeping the tree upstream-identical. |
| **Security impact** | High — prevents leaked credentials reaching a published image or public fork. |
| **Confidence** | CONFIRMED |

### Q37 / Q70 — Module installer: **new overlay risk identified**

| | |
|---|---|
| **Recommended option** | Name our packages so the **last path segment is globally unique** (e.g. `saas/oe-module-saas-nphies`), and add a CI assertion that no composer package's install path collides with a tracked module directory. |
| **Why** | `CustomModuleInstaller::getInstallPath()` returns `interface/modules/custom_modules/<LAST SEGMENT OF PACKAGE NAME>` — **the vendor is discarded**. Any package named `<anyvendor>/oe-module-weno` resolves onto the tracked upstream module directory. Verified from installed source, which the prior audit could not read. |
| **Evidence** | `vendor/openemr/oe-module-installer-plugin/src/CustomModuleInstaller.php:13-20`, `Plugin.php:9-17`; `vendor/composer/installed.json` |
| **Alternatives rejected** | Relying on convention — the collision is silent and only manifests after `composer install` in production. |
| **Trade-offs** | Slightly more verbose package names. |
| **Cost** | One naming rule plus a short CI check. |
| **Upgrade/drift impact** | Prevents a class of silent production divergence. |
| **Confidence** | CONFIRMED |
| **Note** | Today's authority is cleanly partitioned — the seven tracked modules are absent from `composer.lock`, so composer never touches them; only claimrev-connect is composer-managed and it is gitignored for exactly that reason. The risk is **prospective**, arising when we ship our own packages. |

### Q51 — Coverage baseline is **~29%**, not ~4%

| | |
|---|---|
| **Recommended option** | Targets of 80% patch / 60% total for our modules. Report the three numbers separately and never conflate them. |
| **Why** | Live Codecov v2 API during this run (updatestamp 2026-08-07T06:02:21Z): **28.66%** (files 4028, lines 428660, hits 122880). Prior run measured 27.53% on 2026-07-21. The `~4%` figure in `codecov.yml:24` is stale by roughly 25 points. |
| **Evidence** | `evidence/raw/q51-codecov-api-response.txt` |
| **Alternatives rejected** | Setting targets against the stale 4% (meaningless) or demanding 60% immediately across the whole tree (would block every PR). |
| **Trade-offs** | 60% total is a large uplift from 29% and should apply to *our* modules, not the inherited tree. |
| **Confidence** | CONFIRMED — retrieved from the official API during this run. |
| **Caveat** | This measures **upstream** `openemr/openemr`; the fork is not onboarded to Codecov. Valid as a proxy only because fork HEAD is a strict ancestor of upstream master. |

### Q49 — Upload protection: coverage is **narrower than the gate suggests**

| | |
|---|---|
| **Recommended option** | Day-1 AV sidecar **plus** magic-byte validation; pin `secure_upload=1` in provisioning; triage all 26 `createDocument` callers; resolve the DICOM write-order question. |
| **Why** | `isWhiteFile()` is called from only **2** sites, both gated behind the operator-disableable `secure_upload` global — while `createDocument(` has **26** call sites. Zero AV. No magic-byte MIME check on the gate. Additionally `C_Document.class.php:154` performs `move_uploaded_file` for the DICOM-ZIP flow while the gate sits at `:243`; whether the write is reachable without passing the gate is **unresolved**. |
| **Evidence** | `library/sanitize.inc.php:113`; `controllers/C_Document.class.php:154,243`; `src/Services/DocumentService.php:130`; `library/globals.inc.php:2125-2130` |
| **Alternatives rejected** | Relying on the extension allow-list — the weakest standard control, switchable off, with unverified coverage. |
| **Trade-offs** | An AV sidecar adds a container and upload latency. Standard for healthcare SaaS. |
| **Security impact** | High. |
| **Confidence** | CONFIRMED for the gate topology; **MEDIUM** for the DICOM ordering (`manual_review_required = TRUE`). |

---

## §3 Decisions strengthened, not changed

Briefly, where evidence **reinforced** a suggested default and is worth citing when the decision is ratified:

- **Q1** — zero fork commits makes the no-core-edit policy free to adopt *today*.
- **Q5 / Q6** — no MFA-force global and no `REMOTE_USER` anywhere: both independently argue for an external IdP (Q4).
- **Q7** — Google Sign-In is a complete working federation path, making it the cheapest template for Q4.
- **Q36** — all seven tracked modules are pristine; no hotfix ledger is needed.
- **Q57** — `insurance_companies.id` is documented as sharing an id-space with `pharmacies`; changing it would silently corrupt address lookups. Leave alone, and never share id-spaces in new tables.
- **Q74 / Q75** — the integration tree is genuinely absent (not merely thin), while the isolated suite is a first-class CI gate across the broadest PHP matrix in the project.
