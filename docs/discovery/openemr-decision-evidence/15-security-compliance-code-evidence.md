# 15 — Security & Compliance Code Evidence (Q43–Q50, Q67–Q69)

_Fork: OpenEMR 8.3.0-dev @ `631f2b38cf633769c305233f88cdf9c73ca80657`. Mode: READ-ONLY static analysis.
No secret value appears anywhere in this file or in any generated artifact — see §1._

Every count in this report has a saved match list under `evidence/raw/count-<slug>.txt`, each carrying a
header recording the exact command, the ref, and the generation timestamp. Checksums for all of them are
in `evidence/manifests/remaining-counts-sha256.txt`. Generator:
`tools/discovery/openemr-decision-evidence/collect-remaining-evidence.sh`.

---

## 1. Secrets handling policy for this report

Hardcoded credentials **were found** (§2). In accordance with the audit rules:

- Only the file path, line number, and variable name are recorded.
- Every value is rendered `[REDACTED]`.
- No value was written to any file under `docs/discovery/` or `tools/discovery/`.
- The generated ZIP was scanned before archiving (§10).

---

## 2. Q43 — Hardcoded GitHub tokens *(scope is larger than previously recorded)*

The prior discovery note recorded "two hardcoded PATs at `docker/development-easy/docker-compose.yml:75-77`."
The actual footprint is **four compose files × three variables = 12 hardcoded token values**:

| File | Lines | Variables |
|---|---|---|
| `docker/development-easy/docker-compose.yml` | 75–77 | `GITHUB_COMPOSER_TOKEN`, `GITHUB_COMPOSER_TOKEN_ENCODED`, `GITHUB_COMPOSER_TOKEN_ENCODED_ALTERNATE` |
| `docker/development-easy-light/docker-compose.yml` | 75–77 | same three |
| `docker/development-easy-redis/docker-compose.yml` | 180–182 | same three |
| `docker/development-insane/docker-compose.yml` | 224–226 | same three |

All values `[REDACTED]`. Consumer logic lives at `docker/flex/openemr.sh:173-175, 766-790`, which tries the
raw token, then base64-decodes the `_ENCODED` form, then decodes the `_ENCODED_ALTERNATE` form (a
space-separated decimal character-code encoding).

**Three obfuscation layers are used for the same secret class.** Obfuscation is not encryption: the shell
script decodes all three inline, so anyone with the repo has the plaintext. Naive secret scanners keyed on
the `ghp_` prefix will miss the two encoded variants — which is likely why only one file was reported before.

**Ownership caveat — decision-relevant.** These tokens are committed in **upstream `openemr/openemr`**, not
introduced by the fork (the fork has zero own commits — see `05-upstream-fork-drift.md` §1). Rotation is
upstream's action, not the fork's. The fork's obligations are: (a) treat them as burned, (b) ensure they
never reach a published image or a public fork, (c) add a scanner that catches the encoded variants, not
just `ghp_`.

Evidence: `git grep -n "GITHUB_COMPOSER_TOKEN" HEAD` → 7 files (4 compose + `openemr.sh` + 2 docs).
Confidence: **CONFIRMED**.

---

## 3. Q50 — `SECURITY.md` / `dependabot.yml` — **DECISION CONTRADICTED**

Both files **already exist upstream** and are therefore already in the fork:

| File | Status | Evidence |
|---|---|---|
| `.github/SECURITY.md` | **Present** | Documents two disclosure routes: GitHub private advisory (preferred) and `security@open-emr.org` with a published PGP key |
| `.github/dependabot.yml` | **Present** | `version: 2`; composer ecosystem weekly (Mondays 08:00), `open-pull-requests-limit: 15`, `rebase-strategy: disabled`, grouped updates for `symfony/*`, `laminas/*`, and a `development` group (phpunit, phpstan, rector, shipmonk, friendsofphp) |
| `.github/workflows/dependabot-auto-merge.yml` | **Present** (bonus) | Auto-merge automation for Dependabot PRs |

Command: `git ls-files | grep -iE "SECURITY\.md|dependabot"` → 3 files.

**Q50 requires no work.** The remaining decision is narrower and different from the one asked: whether the
fork *keeps* Dependabot enabled (it will raise PRs against the fork, interacting with the Q2 rebase cadence)
and whether `SECURITY.md` should be **rewritten** — as shipped it directs reporters to the *OpenEMR* security
team, which is wrong for a fork holding Saudi tenant PHI. That rewrite is a genuine action item.

Confidence: **CONFIRMED**.

---

## 4. Q49 — Upload protection and AV scanning

### 4.1 ClamAV / antivirus

```
Command: git grep -iln "clamav|virus_scan|antivirus" HEAD   ->  0 matches
```

**No antivirus integration of any kind exists.** Confidence: **CONFIRMED**.

### 4.2 The extension allow-list, and where it is *not* applied

| Element | Location |
|---|---|
| Allow-list function | `library/sanitize.inc.php:113` — `function isWhiteFile($file)` |
| Caller 1 | `controllers/C_Document.class.php:243` |
| Caller 2 | `src/Services/DocumentService.php:130` |
| Gate global | `secure_upload`, `library/globals.inc.php:2125-2130`, **default `'1'` (ON)** |

Both call sites are identically shaped:

```php
OEGlobalsBag::getInstance()->getBoolean('secure_upload') && !isWhiteFile($fileData["tmp_name"])
```

Three findings follow:

1. **The allow-list is operator-disableable.** Setting `secure_upload = 0` removes the *only* content gate
   in the codebase. Default is ON, which is good, but a SaaS operator must pin it.
2. **Coverage is narrow.** `createDocument(` has **26 call sites** but only **2** consult `isWhiteFile()`.
   The remaining paths are internal/programmatic rather than raw user uploads, but this has **not** been
   verified call-by-call — flagged `manual_review_required`.
3. **One upload path writes before the check.** `controllers/C_Document.class.php:154` calls
   `move_uploaded_file($_FILES['dicom_folder']['tmp_name'][$i], $zfn)` for the DICOM-ZIP flow at line 154,
   whereas the `isWhiteFile` gate sits at line 243. Whether the write at :154 is reachable without passing
   the later gate requires reading the full method — **not resolved here**. Confidence on this specific item:
   **MEDIUM**, `manual_review_required = TRUE`.

Other `move_uploaded_file` site: `interface/main/backup.php:971` (admin-only restore path).

**What is absent everywhere:** MIME validation from magic bytes on the upload gate (`finfo_` appears at 22
sites but not in the two gate call sites), size limits beyond PHP ini, quarantine, and AV. Extension
allow-listing alone is the weakest of the standard controls.

---

## 5. Q67 — SQL interpolation and output escaping

### 5.1 Data-access call-site census (also answers Q73)

All lists saved; counts reproducible.

| Sink | Count | Match list |
|---|---:|---|
| `sqlStatement(` | 2,025 | `evidence/raw/count-sqlstatement.txt` |
| `QueryUtils::` | **1,653** | `evidence/raw/count-queryutils.txt` |
| `sqlQuery(` | 1,454 | `evidence/raw/count-sqlquery.txt` |
| `sqlFetchArray(` | 1,354 | `evidence/raw/count-sqlfetcharray.txt` |
| `sqlInsert(` | 251 | `evidence/raw/count-sqlinsert.txt` |
| `Doctrine\DBAL` | 48 | `evidence/raw/count-doctrine_dbal.txt` |
| **Total data-access surface** | **6,785** | — |

**Q73 is now answered** (it was previously uncounted): `QueryUtils::` alone is 1,653 sites. The prior
estimate that sized a shared-DB refactor used only the 1,875 `sqlStatement(` figure. The true surface is
**6,785 call sites — roughly 3.6× larger**. See §5.3 for what this means for Q11.

Supporting file-path surface: `OE_SITE_DIR` 202 sites, `OE_SITE_WEBROOT` 14 sites.

### 5.2 Output escaping ratio

| Pattern | Count |
|---|---:|
| `echo xlt(` (translate + escape) | 9,476 |
| `echo attr(` (attribute escape) | 2,060 |
| `echo text(` (HTML-text escape) | 2,054 |
| **Escaped output subtotal** | **13,590** |
| `htmlspecialchars(` (raw PHP escape) | 369 |
| Twig `\|raw` | 32 |
| Smarty `nofilter` | **0** |
| `innerHTML` | 390 |
| `document.write` | 18 |

**Finding.** The escaping discipline is genuinely strong: 13,590 uses of the project's own escaping
helpers, zero Smarty `nofilter`, only 32 Twig `|raw`. The residual XSS risk concentrates in the **390
`innerHTML`** and **18 `document.write`** JavaScript sinks, which the PHP-side helpers do not protect.
Those 408 sites are the correct target for a focused review; they are **not** individually triaged here.

Confidence: **CONFIRMED** for counts; **MEDIUM** for "low XSS risk overall" (sampling, not exhaustive triage).

### 5.3 SQL injection posture

The prior audit's raw captures (`evidence/raw/security-sql-*.txt`, from the first pass) sampled
interpolation-shaped patterns (`sqlStatement("…$"`, `WHERE $`, `ORDER BY $`, `LIMIT $`). The dominant
codebase idiom is **parameter binding** — `sqlStatement($sql, [$a, $b])`. The residual risk class is
**identifier interpolation** (table/column/ORDER BY names cannot be bound as parameters).

**This audit does not claim the codebase is free of SQL injection.** A full triage of 6,785 call sites was
not performed. Status: **partially_answered**, `manual_review_required = TRUE`. Q67's own suggested default
(a dedicated pre-Go-Live security sprint) remains the right call, and the match lists in `evidence/raw/`
are the worklist for it.

---

## 6. Q42 / audit-integrity input — unvalidated `X-Forwarded-For`

Detailed in `11-authentication-authorization-evidence.md` §3. Summary:

- `library/sanitize.inc.php:29-46` appends the **entire client-supplied** `HTTP_X_FORWARDED_FOR` chain to
  `ip_string` with no parsing and no trusted-proxy allowlist.
- Sinks: `src/Common/Logging/Audit/LogTablesSink.php:70` (→ `log` table) and
  `src/Common/Logging/EventAuditLogger.php:265-266` (→ auth-failure `comments`).
- `git grep -in "trustedproxies|trusted_proxy" HEAD` → **0 matches**.
- Mitigation already present: `REMOTE_ADDR` is preserved separately in the `ip` key.

**Impact:** audit-log forging. **Confidence: CONFIRMED.**

---

## 7. Q68 — `log.checksum` is a per-row hash, not a chain

`[fork:631f2b38cf633769c305233f88cdf9c73ca80657]` `src/Common/Logging/Audit/LogTablesSink.php:63-91`

```php
$checksum = hash('sha3-512', implode('', array_values($logData)));      // :63
...
$checksumGenerateApi = hash('sha3-512', implode('', array_values($apiLogData)));  // :83
...
    'checksum'     => $checksum,        // :90
    'checksum_api' => $checksumGenerateApi,  // :91
```

Answering the mandated sub-questions exactly:

| Sub-question | Answer |
|---|---|
| What fields enter the checksum? | Only the **current row's own** `$logData` values, concatenated positionally |
| Algorithm | `sha3-512` |
| Is a secret key used? | **No** — plain `hash()`, not `hash_hmac()` |
| Is the previous record's hash included? | **No** |
| Is it a chain? | **No** |
| Recomputable after DB compromise? | **Yes** — algorithm is public, unkeyed, and inputs are the row itself |
| Scheduled verifier? | **No** — `git grep -in "verifyChecksum\|checksum.*verif\|verif.*checksum" HEAD` over `src/`, `library/`, `bin/` → **0 matches** |
| External immutable storage? | **No** |

Storage note: `src/Common/Logging/EventAuditLogger.php:670-671` records that `log.checksum` itself is no
longer used from OpenEMR 6.0 onward; the operative checksum lives in `log_comment_encrypt`, and the legacy
column is retained only as an input to that newer value.

**Conclusion — stated plainly, per the audit rules:** this is an **integrity checksum against accidental
corruption, not a tamper-evident chain**. An attacker with UPDATE on the `log` tables can edit a row and
recompute a valid checksum with a one-line script. Describing it as tamper-proof would be incorrect.

**To make tamper-evidence real requires all three:** (1) `hash_hmac` with a key the DB user cannot read,
(2) inclusion of the previous row's hash to form a chain, (3) a scheduled verifier. None exists today.

Confidence: **CONFIRMED**.

---

## 8. Q69 — Encryption inventory: what is *actually* encrypted

| API | Sites | List |
|---|---:|---|
| `encryptStandard(` | 36 | `evidence/raw/count-encryptstandard.txt` |
| `decryptStandard(` | 54 | `evidence/raw/count-decryptstandard.txt` |
| `CryptoGen` references | 289 | `evidence/raw/count-cryptogen.txt` |
| `…ForFilesystem` | 63 | `evidence/raw/count-encrypt_fs.txt` |

**The headline count is misleading, and this is the most decision-relevant finding in this report.**
Excluding tests, PHPStan baselines, and the crypto library's own interface/implementation, the *actual
application* call sites are:

| Real write path | Location | What it protects |
|---|---|---|
| SMART launch token | `src/FHIR/SMART/SMARTLaunchToken.php:132` (read `:162`) | ephemeral launch params |
| Key wrapping | `src/Common/Crypto/CryptoGen.php:482` | the `keys` table |
| Fax module media token | `oe-module-faxsms/src/Controller/SignalWireClient.php:363` (read `library/faxMedia.php:44`) | PHI handout token |
| Fax/voice credentials | `oe-module-faxsms/src/RCVoice/VoiceFunctionsTrait.php:76` (read `:102`) | vendor credentials |
| Voice credentials read | `oe-module-faxsms/src/Controller/VoiceClient.php:110` | vendor credentials |
| Gateway password | `library/reminders.php:399` | `phone_gateway_password` global |

**No core clinical PHI column is encrypted at rest through this API.** `patient_data`, `form_encounter`,
`billing`, and `documents` metadata are stored as plaintext columns. `encryptStandard` protects
**credentials and tokens**, not patient data.

What *does* protect PHI at rest:

| Control | Global | Default | Evidence |
|---|---|---|---|
| Document/file encryption on disk | `drive_encryption` | **`'1'` (ON)** | `library/globals.inc.php:1035-1040` |
| Database-sourced-key encryption | `database_encryption` | **`'1'` (ON)** | `library/globals.inc.php:1028-1032`; consumed at `src/BC/Crypto/Crypto.php:65`, `src/Common/Crypto/CryptoGen.php:82` |

**Q47 is answered as a side effect, and its premise is corrected:** `drive_encryption` **already defaults to
ON** upstream (`globals.inc.php:1038`). The prior note recorded this as "unverified." No patch is needed —
only a provisioning guarantee that tenants never turn it off. The deprecated `couchdb_encryption` global
(`:1043-1048`) is explicitly a no-op.

**PDPL consequence (technical statement only, not a legal conclusion):** encryption-at-rest for clinical
PHI depends on `drive_encryption` for documents plus **storage/volume-level encryption supplied by the
deployment**, not on column-level encryption in the application. Any claim that "PHI columns are encrypted
by OpenEMR" would be false.

Confidence: **CONFIRMED** for the call-site classification; **HIGH** for "no core PHI column encryption"
(exhaustive over the two API names — a bespoke encryption path under a different name would not be caught).

---

## 9. Q48 — Audit-log retention

```
Command: git grep -iln "log_pruner|prune.*audit|retention" HEAD -- src/ library/ bin/
   -> 3 files, all unrelated (BillingUtilities.php, two FHIR R4 generated classes)
```

**No retention policy, no pruner, no archival job exists** for `log` / `api_log`. The tables grow without
bound. Seeded `background_services` rows (`sql/database.sql:209-217`) contain no log-maintenance service.

Confidence: **CONFIRMED** (absence).

---

## 10. Q44 — KMS / per-tenant key isolation

Established in `16-control-plane-constraints.md` §16.1 item 6 and re-verified here: no KMS SDK is present
in `composer.json`. Keys are per-site files on disk:
`$OE_SITE_DIR/documents/certificates/oa{private,public}.key` and
`sites/<site>/documents/logs_and_misc/methods/`.

**Consequence:** DB-per-tenant already gives per-tenant key isolation *by construction* (each site has its
own key files). A KMS adds rotation, escrow, and HSM custody — not isolation. This narrows Q44 from "build
isolation" to "add managed custody," which is a materially smaller Day-1 scope.

Confidence: **HIGH**.

---

## 11. Q45 / Q46 — brief factual status

- **Q46 (breakglass prompt):** `gbl_force_log_breakglass` exists at `library/globals.inc.php:2851-2856`,
  default `'1'` (ON). It **logs** emergency-user activity; `git grep` finds no justification-prompt UI. The
  premise is confirmed: logging without a reason prompt. **CONFIRMED.**
- **Q45 (PDPL data residency):** no repository evidence can answer this — it is a legal/deployment
  decision. Routed to `20-unresolved-external-inputs.md`. **external_decision_required.**

---

## 12. Reproduction

```bash
bash tools/discovery/openemr-decision-evidence/collect-remaining-evidence.sh
# writes evidence/raw/count-*.txt, evidence/raw/remaining-counts.tsv,
#        evidence/manifests/remaining-counts-sha256.txt
```

Point checks used in this report:

```bash
git grep -n "GITHUB_COMPOSER_TOKEN" HEAD          # never print values
git ls-files | grep -iE "SECURITY\.md|dependabot"
git grep -iln "clamav|virus_scan|antivirus" HEAD
git grep -n "checksum" HEAD -- 'src/Common/Logging/**/*.php'
git grep -n -B4 "certain data stored in the database" HEAD -- library/globals.inc.php
git grep -in "trustedproxies\|trusted_proxy" HEAD
```
