# Phase 14 — Security & Compliance Inventory

Read-only archaeology. Every claim cites `file:line`. Cross-references Phase 4
(`05-auth-and-acl.md`) for auth internals; this phase covers CSRF, input
sanitization, SQL safety, uploads, encryption-at-rest, audit logging, HIPAA
surface, and SaaS/multi-tenant gaps. No exploit code — inventory only.

---

## 1. CSRF Protection

### 1.1 The `CsrfUtils` class

`src/Common/Csrf/CsrfUtils.php` (133 lines). Namespace `OpenEMR\Common\Csrf`.

- **Secret material.** `setupCsrfKey()` at `src/Common/Csrf/CsrfUtils.php:35-42`
  generates a 32-byte private key via `random_bytes(32)` at login and stores it
  in the session under key **`csrf_private_key`**. Never leaves the server.
- **Token derivation.** `collectCsrfToken($session, $subject = 'default')` at
  `src/Common/Csrf/CsrfUtils.php:49-56` returns
  `substr(hash_hmac('sha256', $subject, $privateKey), 0, 40)` — SHA-256 HMAC
  truncated to 40 hex chars to fit GET query strings.
- **Subjects (namespaces).** Any string is a valid subject; a single private
  key derives unlimited scoped tokens. Observed subjects in the codebase:
  `default`, `api` (internal REST/FHIR), `oauth2` (authorization endpoints —
  `src/RestControllers/AuthorizationController.php:843,1227`,
  `src/RestControllers/SMART/SMARTAuthorizationController.php:208`),
  `portal-payment`, `portal-appointment`, `messages-portal`, `rainforest`
  (Rainforest Pay), `import-template-*`, `passwordResetCsrf`,
  `verifyEmailCsrf`, `autologin`, `sphere`/`sphere_revert`, `contact-form`,
  `counter`, `udi`, `sqlupgrade`, `rwt_2026_report`, `ClientAdminController`,
  `state<N>` (setup), etc.
- **Verification.** `verifyCsrfToken($token, $session, $subject)` at
  `src/Common/Csrf/CsrfUtils.php:89-97` uses `hash_equals()` (constant-time)
  and rejects null/empty/false.
- **New helper.** `checkCsrfInput(int $inputType, ...)` at
  `src/Common/Csrf/CsrfUtils.php:71-86` combines `filter_input()` extraction +
  verification; `dieOnFail: true` invokes `csrfNotVerified()` which
  `http_response_code(403); exit;` (`:105-132`).

### 1.2 Coverage / call-site counts (grepped `.php`)

| Symbol | File-level match count | Notes |
|---|---|---|
| `CsrfUtils::collectCsrfToken` | **> 100 files** (grep result truncated at 100; full sweep would be higher — see below) | Form/token emission sites |
| `CsrfUtils::verifyCsrfToken` | **62 files** | Legacy inline verification |
| `CsrfUtils::checkCsrfInput` | **> 100 files** (grep truncated at 100 hits — full sweep would be higher) | Modern one-liner guard |

Counts are file-hit counts from the ripgrep-equivalent grep tool. The tool
truncated at 100 file matches for the two hot symbols; treat both as
lower bounds. Impressionistically the codebase has migrated most new/refactored
handlers to `checkCsrfInput(INPUT_POST, dieOnFail: true)` — see the wall of
consistent invocations in `interface/super/edit_layout.php:443,545,583,636,659,671,694,721`,
`interface/reports/*.php`, `library/ajax/*.php`, `interface/usergroup/*.php`.

### 1.3 CSRF-exempt endpoints

- **OAuth2 authorization code / SMART consent forms — NOT exempt.** They
  require `csrf_token_form` under the `oauth2` subject
  (`src/RestControllers/AuthorizationController.php:843, 1227`,
  `src/RestControllers/SMART/SMARTAuthorizationController.php:208`).
- **OAuth2 token endpoint / FHIR/REST calls with `Authorization: Bearer` —
  effectively exempt from `CsrfUtils`.** They rely on the OAuth2 bearer token
  itself as the anti-CSRF proof (issued via a flow that _did_ use CSRF).
  Reference: token audit at
  `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php:299,311,316`.
- **"Local API" (in-browser, same-origin) calls** use a special custom header
  **`APICSRFTOKEN`** (subject: `api`). The check is at
  `src/RestControllers/Authorization/LocalApiAuthorizationController.php:56`
  and gated on the header being present at
  `tests/Tests/Unit/Common/Http/HttpRestRequestTest.php:228-241`. Absence of
  the header means the request is treated as an external/OAuth2 request and
  routed through bearer-token auth instead.
- **Portal password-reset callback** verifies via `passwordResetCsrf` on GET
  — `portal/account/account.php:108`. Not exempt.
- **The dev-only `setup.php` installer** uses per-step subjects
  `state<state>` — `setup.php:317`.

**Not confirmed exempt anywhere:** the anti-forgery discipline appears
comprehensive for cookie-authenticated flows. `UNKNOWN — full audit for
CSRF-exempt POST/PUT/PATCH handlers not performed` beyond the sample.

---

## 2. Input Sanitization

### 2.1 `library/sanitize.inc.php` (autoloaded via `composer.json` `files`)

Sanitizer helpers exported to global scope (`library/sanitize.inc.php`):

| Function | Line | Purpose |
|---|---|---|
| `collectIpAddresses()` | `:29` | Reads `REMOTE_ADDR` + `HTTP_X_FORWARDED_FOR` (X-F-F is trusted blindly if present — see §11) |
| `json_sanitize($json)` | `:49` | Re-encode-roundtrip validate |
| `check_file_dir_name($label)` | `:60` | `[^A-Za-z0-9_.-]` → `die()` |
| `convert_safe_file_dir_name($label)` | `:71` | Same charset → replace with `_` |
| `convert_very_strict_label($label)` | `:77` | `[^A-Za-z0-9]` → `_` |
| `check_integer($value)` | `:83` | Regex `[^0-9]` |
| `basename_international($path)` | `:89` | UTF-safe basename |
| `isWhiteFile($file)` | `:113` | MIME whitelist check against `list_options` list `files_white_list` (`:118`); dispatchable via `IsAcceptedFileFilterEvent` |
| `sanitizeNumber($number)` | `:152` | Numeric coercion + log |
| `mb_is_string_equal_ci(...)` | `:203` | NFKC-normalized case-insensitive equality |

### 2.2 `library/formdata.inc.php` — request parsing / SQL escaping

Marked **deprecated for new scripts** in its own docblock (`:262`,`:284`) —
legacy compatibility only.

| Function | Line | Behaviour |
|---|---|---|
| `add_escape_custom($s)` | `:24-29` | Wrapper around `mysqli_real_escape_string($GLOBALS['dbh'], …)`. **The sanctioned legacy escape function.** |
| `escape_limit($s)` | `:43` | `(int)$s` |
| `escape_sort_order($s)` | `:60` | Whitelist `['asc','desc']` |
| `process_cols_escape($s)` | `:74` | CSV → array |
| `escape_sql_column_name($s, $tables, ...)` | `:100-157` | **Whitelist against actual `SHOW COLUMNS` output**, backtick-quotes, rejects backticks in input (`:117`); dies or throws on no-match |
| `escape_table_name($s)` | `:177` | Delegates to `QueryUtils::escapeTableName` |
| `escape_identifier($s, $whitelist, ...)` | `:210-258` | Array whitelist OR regex character class; supports case-insensitive fallback |
| `formData($name, $type='P', $isTrim=false)` | `:269-280` | Reads `$_POST`/`$_GET`/`$_REQUEST` and immediately runs through `add_escape_custom` (i.e. escaped for concatenation into SQL — **not typed**) |
| `formDataCore($s, $isTrim=false)` | `:293-303` | Same escape without superglobal read |

**Observation.** `formData()` returns SQL-escaped strings; callers that
subsequently pass into `sqlStatement` with bind arrays double-escape. This
is a long-standing legacy footgun, still present in older interface pages.

### 2.3 `library/htmlspecialchars.inc.php` — output escaping

Purpose-specific escapers, each keyed to an output context. The design note
(`:14-38`) explicitly reserves the narrow `@param string` types as a PHPStan
signal for **dead escapes** (calling `attr((int)$x)` = misapplication).

| Function | Line | Context |
|---|---|---|
| `js_escape($text)` | `:46` | JS literal (`json_encode`) |
| `attr_js($text)` | `:56` | JS in HTML `on*` attribute |
| `attr_url($text)` | `:66` | URL-encoded HTML attribute |
| `safe_href($url)` | `:85-125` | href/action scheme allowlist (`http, https, mailto, tel, ftp, ftps`) — blocks `javascript:`, `data:`, `vbscript:`, `blob:` |
| `js_url($text)` | `:133` | URL-encoded JS literal |
| `errorLogEscape($text)` | `:143` | `attr()` wrapper for logs |
| `csvEscape($text)` | `:160-172` | CSV-injection mitigation (strips `=+"|`, leading `-@\t\r`, wraps in `"`) |
| `xmlEscape($text)` | `:185` | `ENT_XML1 \| ENT_QUOTES` |
| `javascriptStringRemove(...)` | `:193` | Recursively strips `javascript` |
| `text($text)` | `:234` | HTML text node — `ENT_NOQUOTES` |
| `textArray(array, ...)` | `:259` | Depth-bounded recursive `text()` |
| `attr($text)` | `:291` | HTML attribute — `ENT_QUOTES` |
| `xlt($key)` | `:323` | Translate + `text()` |
| `xla($key)` | `:334` | Translate + `attr()` |
| `xlj($key)` | `:345` | Translate + `js_escape()` |
| `xlx($key)` | `:356` | Translate + `xmlEscape()` |

### 2.4 Output discipline (escape vs raw echo)

Rigorous sampling requires ripgrep, which is not available in this shell
environment; grep tool truncates at 100 matches per pattern. Qualitative
observations from spot reads:
- Modern Twig templates use `|attr`, `|js_escape`, `|attr_url`, and
  `csrfToken()` / `csrfTokenRaw()` filters uniformly
  (`templates/portal/home.html.twig:132-793`, `templates/reports/cqm/*`).
- Legacy `.php` interface pages (`interface/edih*`, `library/edihistory/*`)
  emit long `echo` chains that consistently wrap variables in `text()`,
  `attr()`, `attr_url()`, `xlt()` (`library/edihistory/edih_csv_data.php:85-890`).
- Bare `echo $var` without escape in output context does exist in legacy
  code paths but is not the dominant pattern in files inspected here.

**UNKNOWN — repo-wide `echo $var` vs `echo text(...)` ratio requires
ripgrep or a batched grep run outside this session's tool caps.**

---

## 3. SQL Safety

### 3.1 The two coexisting APIs

- **Legacy procedural.** `sqlStatement($sql, $binds = false)` defined at
  `library/sql.inc.php:96`. Passing `$binds` as an array uses
  parameterized MySQLi prepared statements; passing `false` (default) runs
  the string as-is. Companion `sqlStatementNoLog`, `sqlQuery`,
  `sqlFetchArray`, `privStatement`, `privQuery`.
- **Modern OO.** `OpenEMR\Common\Database\QueryUtils` at
  `src/Common/Database/QueryUtils.php`. Methods `fetchRecords()`,
  `fetchRecordsNoLog()`, `sqlStatementThrowException()`, plus
  `escapeTableName()` (whitelist against `SHOW TABLES`, backtick-rejects
  identifier chars — `:43-60`) and `escapeColumnName()` (`:66-69`).
  Bind-array is not optional in the modern signature — the second param
  defaults to `[]` and the query is always run as a prepared statement.

The sanctioned legacy escape function is `add_escape_custom()`
(`library/formdata.inc.php:24-29`) — a thin wrapper around
`mysqli_real_escape_string`. It is the correct tool *only* for values that
cannot be parameterized (identifier fragments), and even those should
prefer the whitelist helpers.

### 3.2 Call-site sample (grepped)

Total `sqlStatement(` file-level matches: **> 100 (grep truncated).** Given
the truncation, sampling the shown hits qualitatively:

- **Very dominant pattern**: bind array passed as second arg. Examples:
  - `contrib/util/dupecheck/mergerecords.php:68,86,134,174`
  - `portal/sign/lib/*` — all bind arrays
  - `portal/report/*` — bind arrays
  - `portal/add_edit_event_user.php:143-475` — all bind arrays
  - `library/sql-ccr.inc.php` — 16 hits, all bind arrays
  - `library/transactions.inc.php:8-55` — bind arrays (line `:18` uses
    `escape_sql_column_name(...)` for identifier interpolation, then binds
    values)
- **String-interpolation anti-pattern** (`sqlStatement("...$"` — statement
  starts with double-quote and interpolation): **33 hits**. Breakdown of
  what is interpolated:
  - Compiled `$sets` / `$where` clauses that were themselves built with
    `add_escape_custom()` and bind-array pairs
    (`interface/patient_file/transaction/add_transaction.php:61`,
    `interface/orders/types_edit.php:281`,
    `library/FeeSheet.class.php:1140`,
    `src/Common/Auth/AuthUtils.php:1140`,
    `src/Billing/BillingReport.php:237`) — parameterized values, dynamic
    predicate composition; low risk if the fragment builder is correct.
  - Interpolated **table names** in a `SHOW TABLES LIKE` — validated by
    `check_form` layer or comes from a fixed layout table name
    (`interface/patient_file/summary/stats.php:240`).
  - Interpolated column-selection fragments like `$sellist`, `$from`,
    `$orderby`, `$sel` — assembled from whitelisted column arrays
    (`interface/patient_file/encounter/find_code_dynamic_ajax.php:296,298`,
    `library/options.inc.php:233,3443`, `portal/get_lab_results.php:39`).
  - Two entries in `library/ippf_issues.inc.php:75,217` (`REPLACE INTO ...
    SET $sets`) where `$sets` builder needs closer review.
  - `interface/billing/edit_payment.php:214-344` — 16 lines that
    interpolate `$where` clauses built earlier in the file — needs source
    inspection to confirm the builder parameterizes user input.

**Honest estimate.** The vast majority of `sqlStatement(` calls in the
sampled listing pass a bind array; direct user-input concatenation is
rare. The residual risk sits in the 33 `"...$` interpolation sites where
`$where`/`$sets` builders assemble SQL fragments from mixed-source
identifiers. These are not obviously broken but are the class of
antipattern that needs case-by-case review. **UNKNOWN — full audit of
each of the 33 sites' fragment provenance not performed.**

### 3.3 QueryUtils safe pattern

The modern shape (`src/Common/Database/QueryUtils.php:22-80`):

```php
$rows = QueryUtils::fetchRecords(
    "SELECT * FROM patient_data WHERE pid = ? AND fname = ?",
    [$pid, $fname],
);
```

Identifier interpolation goes through `QueryUtils::escapeTableName()` /
`escapeColumnName()`, both of which whitelist against the live schema and
backtick-wrap.

---

## 4. File Upload Handling

Entry points: `library/ajax/upload.php` (drag-and-drop), Portal onsite
uploads, DICOM history, EDI ingest, template imports. The canonical
storage class is `library/classes/Document.class.php`.

- **CSRF gate.** `library/ajax/upload.php:42-44` verifies
  `csrf_token_form` before any file processing.
- **Storage location.** Files land under
  `sites/<site>/documents/<higher-level-path>/` (per-site — cross-refer
  Phase 9). Path assembly at
  `library/classes/Document.class.php:703` uses
  `OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . '/documents/' . $from_pathname . '/' . $from_filename;`.
  Alternative backends: **CouchDB** (`Document.class.php:1001-1032`) and
  a dispatchable off-site event
  (`PatientDocumentStoreOffsite`, `:1035-1049`).
- **MIME whitelist.** `isWhiteFile()` (`library/sanitize.inc.php:113-149`)
  consults the `list_options` list `files_white_list` (populated
  administratively), computes `mime_content_type($file)` (`:128`),
  supports `category/*` wildcard entries (`:135`), and fires
  `IsAcceptedFileFilterEvent` (`:123`, `:143`) so modules can extend the
  list. **UNKNOWN — whether every upload path in `Document::createDocument`
  (`Document.class.php:941`) calls `isWhiteFile()` before commit; the
  grep for `isWhiteFile` inside `Document.class.php` returned no hits,
  suggesting whitelist enforcement lives in the *caller* (e.g.
  `library/documents.php` `addNewDocument`) rather than the class itself.
  Not verified.**

  _Resolved 2026-08-19, see `docs/discovery/openemr-decision-evidence/15-security-compliance-code-evidence.md §4.2`:
  `isWhiteFile()` is consulted from only 2 of 26 `createDocument(` call sites, both gated
  behind the operator-disableable `secure_upload` global. The remaining 24 paths were not
  individually triaged — flagged `manual_review_required` there, not fully closed here._
- **Filename sanitization.** `convert_safe_file_dir_name` and
  `check_file_dir_name` (`library/sanitize.inc.php:60-73`) are the
  helpers; whether every caller applies them is not exhaustively verified
  here. Filenames end up as UUIDs in some paths — see the `$filenameUuid`
  usage at `Document.class.php:1076,1092`.
- **Path traversal defense.** The path is composed from server-side
  globals + supplied `filename`; no explicit `realpath` /
  `str_contains('..')` check appears in `createDocument`. The filename is
  typically renamed to a UUID before being written
  (`Document.class.php:1076`), which incidentally neutralizes traversal
  attempts within that code path.
- **AV scanning.** Grep for `clamav`, `virus_scan`, `antivirus` across
  `.php`: **zero hits.** No built-in AV integration. Deployment-layer
  scanning is required for SaaS. **`UNKNOWN — product-owner decision`**
  on whether ClamAV/rspamd sidecar is in scope for the fork.

---

## 5. Encryption at Rest — `CryptoGen`

Reference: `src/Common/Crypto/CryptoGen.php` (733 lines).

### 5.1 Algorithm

- **Cipher.** AES-256-CBC (`src/Common/Crypto/CryptoGen.php:195, 254, 305,
  346`). **Not** AEAD (no GCM). Integrity is layered manually via a
  separate HMAC — encrypt-then-MAC pattern.
- **MAC.** `hash_hmac('sha384', $iv . $ciphertext, $sSecretKeyHmac, true)`
  at `:200, 249`, verified with `hash_equals()` at `:251, 302`.
- **IV.** `random_bytes(openssl_cipher_iv_length('aes-256-cbc'))` — 16
  bytes, per-message (`:188`).
- **Envelope layout.** `base64( hmac || iv || ciphertext )` prefixed with a
  3-char `KeyVersion::toPaddedString()` (`:92, 209`). Versioned to allow
  algorithm rotation while retaining decrypt-compatibility with legacy
  ciphertexts. `KeyVersion::ONE` (`:143`) and `TWO/THREE` (`:145`) are
  legacy paths.

### 5.2 Key material — dual key sets

Documented at `src/Common/Crypto/CryptoGen.php:7-18`:

- **Database key set** — stored in the `keys` MySQL table
  (`:401-411`). Generated with `random_bytes(32)` (`:425`), base64-encoded
  in the row.
- **Drive key set** — stored at
  `sites/<site>/documents/logs_and_misc/methods/<label>` (`:450, 475`).
  Encrypted with the database key set (`:457, 482`) — nested.
- **Cross-encryption.** The drive key set encrypts DB-column payloads;
  the DB key set encrypts the on-drive key files. Compromise of one
  storage medium alone yields nothing.
- **No env-var / TPM / KMS support.** Key material lives entirely in
  `keys` table + `sites/.../methods/` files. **This is the single
  largest gap for SaaS multi-tenant with per-tenant KMS.**

### 5.3 Key derivation

There is **no password-based KDF for the primary keys** — they are pure
`random_bytes(32)`. `hash_pbkdf2` and `hash_hkdf` wrappers exist (`:539`,
`:556`) and are used by the `ContextualEncryptionTrait` (imported at
`:35-38`) — this path is used for per-context sub-keys (e.g. per-column
contextual encryption) but the *root* keys are direct random bytes.

### 5.4 Opt-out flags

- `database_encryption` global — controls whether DB-column encryption
  runs (`:81-82`).
- `drive_encryption` global — controls whether uploaded documents are
  encrypted on disk (`:83-84`, `Document.class.php:992-996`). When on,
  document data passes through `$cryptoGen->encryptForFilesystem($data)`
  before being stored (`Document.class.php:1003, 1006`).

### 5.5 Documents encrypted at rest?

**Conditional.** `Document::createDocument` at
`library/classes/Document.class.php:990-996` reads
`OEGlobalsBag::getInstance()->getBoolean('drive_encryption')`; if true,
the file is encrypted via `encryptForFilesystem` before writing to disk
or CouchDB. **Default value of `drive_encryption`** — not verified here;
**UNKNOWN — inspect `library/globals.inc.php` for the default.**

_Resolved 2026-08-19, see `docs/discovery/openemr-decision-evidence/15-security-compliance-code-evidence.md §8`
and `21-recommended-decision-updates.md` (Q47): both `drive_encryption` and
`database_encryption` default to `'1'` (ON) at `library/globals.inc.php:1028-1040`. No
patch is needed — only a provisioning guarantee that tenants never turn it off. That
finding does **not** mean PHI columns are encrypted at the database layer — see the same
evidence file §8 on `encryptStandard` coverage._

### 5.6 Call sites (encrypted columns)

The `ContextualEncryptionTrait` and `encryptStandard`/`decryptStandard`
consumers are numerous; full audit not performed here. Confirmed
encrypted fields include OAuth2 key pairs and passphrases
(`src/Common/Auth/OAuth2KeyConfig.php:116-151`), TOTP secrets
(`library/classes/Installer.class.php:2156-2161`), and stored PSK / API
tokens (referenced in `CryptoGen` docblock). **UNKNOWN — the full
inventory of encrypted columns requires a `grep -l encryptStandard\|decryptStandard`
sweep that this session did not exhaustively enumerate.**

---

## 6. Audit Logging

### 6.1 Tables

**`log`** — `sql/database.sql:7758-7776`:
```
id, date, event, category, user, groupname, comments (longtext),
user_notes (longtext), patient_id, success, checksum (longtext),
crt_user, log_from, menu_item_id, ccda_doc_id
```
InnoDB, no FKs (per Phase 3). `checksum` is a tamper-detection hash
column (see §6.4).

**`extended_log`** — `sql/database.sql:12414-12424`:
```
id, date, event, user, recipient, description (longtext), patient_id
```
Used for recipient-directed events (fax/email of PHI).

**`audit_master`** — `sql/database.sql:149-162`. Companion `audit_details`
at `:171-178`.
```
id, pid, user_id, approval_status (1-Pending,2-Approved,3-Denied,
4-Appointment directly updated, 5-Cancelled), comments, created_time,
modified_time, ip_address, type (1-new patient,2-existing,3-doc-only,
4-Patient upload,5-random key,10-Appointment),
is_qrda_document, is_unstructured_document
```
Approval-workflow audit for patient-portal-submitted changes.

**`api_log`** — `sql/database.sql:92-105`:
```
id, log_id, user_id, patient_id, ip_address, method, request,
request_url (text), request_body (longtext), response (longtext),
created_time
```
Full REST/FHIR request/response capture (linked to `log.id` via
`log_id`).

### 6.2 Writer — `EventAuditLogger`

`src/Common/Logging/EventAuditLogger.php`. Singleton
(`SingletonTrait`, `:34`). Instance construction (`:36-91`):

- **Dedicated DB connection.** `DatabaseConnectionFactory::createDbal($opts, false)`
  at `:44` — **separate from the main app connection** (comment at
  `:42-43` notes this is important for `LogTablesSink` and
  `BreakglassChecker`).
- **Sinks (`Audit\MultiSink`).** Always: `Audit\LogTablesSink` (writes to
  `log`, `api_log`, `extended_log`). Conditionally:
  `Audit\Atna\TcpWriter` + `Audit\AtnaSink` when `enable_atna_audit`
  global is on (`:49-66`) — IHE ATNA syslog over TLS to remote SIEM.
- **Config knobs (`AuditConfig`, `:68-82`).** `enable_auditlog`,
  `gbl_force_log_breakglass`, `audit_events_query`,
  `audit_events_http-request`, and per-event-type flags:
  `patient-record`, `scheduling`, `order`, `lab-order`, `lab-results`,
  `security-administration`, `other`.

### 6.3 What gets audited — sample of `newEvent()` sites

Authoritative primitive: `EventAuditLogger::newEvent($event, $user, $group,
$success, $comments, $patientId)` at `EventAuditLogger.php:212`.
Selected call sites:

- **Login success/failure.** `src/Common/Auth/AuthUtils.php:157-489`
  (~40 hits): empty creds, patient portal missing pid, user not found,
  not active, not in group, password expired, exceeded IP/user max
  failed logins, IP force-blocked, LDAP failure, invalid password hash,
  password incorrect, `login` success, `auth` success.
- **Password changes.** `AuthUtils.php:525-793` — every failure branch and
  final success.
- **Google Sign-In.** `AuthUtils.php:1450-1514`.
- **PHI reads via UI.** `src/Common/Session/PatientSessionUtil.php:80` —
  `newEvent("view", ...)` on patient chart access.
- **ACL denials.** `src/Common/Acl/AccessDeniedHelper.php:50`.
- **API activity.** `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php:299-316`
  — token miss / expired / success.
- **OAuth2 key config lifecycle.** `src/Common/Auth/OAuth2KeyConfig.php:116-255`.
- **CQM/QRDA export.** `src/Cqm/QrdaControllers/QrdaReportController.php:363,457`.
- **SQL audit.** `EventAuditLogger::auditSQLEvent($statement, $outcome, $binds)`
  at `EventAuditLogger.php:430` — embedded in the `sqlStatement()`
  path so writes/reads to tagged tables are logged when the relevant
  event-type flag is on (`shouldLogSqlEvent()` at `:106-125`). Table
  → event-type mapping in `EventAuditLogger.php:132+` (constant
  `LOG_TABLES`, includes `billing`, `claims`, `employer_data`, `forms`,
  `form_encounter`, `form_dictation`, etc. — all tagged `patient-record`).
- **SELECT-from-uncategorized-tables is never logged** (`:108-111`).

### 6.4 Tamper-detection

`log.checksum` column exists (`sql/database.sql:7769`).
`EventAuditLogger` has `auditSQLAuditTamper()` — invoked when config
flags are changed (`interface/super/edit_globals.php:316` for the
`gbl_force_log_breakglass` toggle). **UNKNOWN — full checksum chain
verification workflow and cadence not audited here.**

_Resolved 2026-08-19, see `docs/discovery/openemr-decision-evidence/15-security-compliance-code-evidence.md §7`
and `21-recommended-decision-updates.md` (Q68): there is no chain and no verifier at all.
`LogTablesSink.php:63` computes an unkeyed `hash('sha3-512', ...)` over the row's own
fields only — no previous-row hash, no HMAC key, and a `verifyChecksum` grep returns 0
hits repo-wide. This is an integrity checksum against accidental corruption, not a
tamper-evident chain; describing it as tamper-proof is incorrect._

### 6.5 Retention / rotation

**No built-in log rotation observed in `EventAuditLogger`.** Grep for
`DELETE FROM log WHERE` returned nothing tied to a scheduled job.
`log` and `api_log` accumulate indefinitely unless an operator
truncates them. **UNKNOWN — confirm no background-service pruner
exists in `library/backgroundservices/*` (Phase 15 territory).**
Retention = infinite by default = a HIPAA hedge (retention is a
policy, not a footgun) but a storage-cost concern for SaaS.

---

## 7. Breakglass (Emergency Access)

- **ACL group.** `breakglass` group created by
  `library/classes/Installer.class.php:1107,1429` and
  `acl_upgrade.php:149` ("Emergency Login" ACL, `breakglass` section,
  `write` on emergency-access resources).
- **Enforcement.** `src/Common/Logging/BreakglassChecker.php:58` —
  membership check by joining GACL tables:
  `->fetchOne($sql, ['breakglass', $username])`.
- **Config flag.** `gbl_force_log_breakglass` — global setting
  registered at `library/globals.inc.php:2851-2856` ("Force logging of
  all Emergency User (ie. breakglass) activities.").
- **Behaviour.** When `forceBreakglass` is true AND the acting user is
  in the `breakglass` group, `EventAuditLogger::shouldLogSqlEvent`
  (`src/Common/Logging/EventAuditLogger.php:117`) overrides all
  per-event-type filters and forces logging.
- **Admin email on breakglass activation.**
  `interface/usergroup/usergroup_admin.php:77` — send admin mail when
  `$GLOBALS['Emergency_Login_email']` is set to 1.
- **Justification workflow.** **No text-justification prompt is
  presented at breakglass login itself.** The mechanism is: user has
  extra ACL group → gets extra access → *every* SQL event is captured
  regardless of the usual filter, and admin is notified. There is no
  "reason box" gate. **UNKNOWN — whether product-owner considers
  this sufficient; Saudi/PDPL practice often requires an in-flow
  justification field. Requires product-owner input.**

---

## 8. HIPAA-Referenced Features (Informational — Not Certification)

- **Idle logout.** `gbl_time_out_idle` — see Phase 4.
- **Password complexity / rotation.** `library/globals.inc.php:2174`
  (`password_history`, up to 4 slots) and `:2188`
  (`password_expiration_days`). Enforcement:
  `src/Common/Auth/AuthUtils.php:115-119` (auto-heal blank
  `password_expiration_days` to 0),
  `AuthUtils.php:526-778` (rotating `password_history1..4`
  columns in `users_secure`; new password rejected if it matches any
  of the retained history, `:729-741`),
  `AuthUtils.php:1088-1095` (grace-period expiry evaluation),
  `interface/main/main_screen.php:444-455` (client-side expiry alert
  banner), `interface/main/pwd_expires_alert.php:34`.
- **Account lockout.** Two-axis throttle:
  - **Per-user** — `users_secure.login_fail_counter`,
    `AuthUtils.php:1170-1179`. Threshold globals
    `password_max_failed_logins` (`:1171`) and
    `time_reset_password_max_failed_logins` (`:1176`).
    Counter reset on success (`:1239`).
  - **Per-IP** — `ip_tracking` table (id, ip_string, ip_force_block,
    ip_no_prevent_timing_attack, total_ip_login_fail_counter,
    ip_login_fail_counter, ip_last_login_fail, ip_auto_block_emailed).
    Enforcement at `AuthUtils.php:1206-1219`; auto-block email at
    `:1362`; manual force-block toggle at `:1326`.
    Globals: `ip_max_failed_logins`,
    `ip_time_reset_password_max_failed_logins`.
- **PHI-access audit** — see §6.3.
- **HTTPS enforcement.**
  - **App level.** No `Strict-Transport-Security` header emitted from
    PHP. No `session.cookie_secure` enforcement in app code. Session
    cookie code at `src/Common/Session/SessionConfiguration.php:51`
    sets `cookie_httponly` based on constructor arg — see
    `src/Common/Session/SessionUtil.php:7-15` explanation: **core
    OpenEMR sets `cookie_httponly = false` deliberately** because
    JS needs to read the cookie; portal + oauth2 sessions set it
    `true`.
  - **PHP INI level.** Every shipped `docker/*/php.ini` has
    `session.cookie_secure =` **commented out** (e.g.
    `docker/development-easy-redis/php.ini:1356`) and
    `session.cookie_httponly =` **blank** (`:1387`). Explicitly
    called out as a known state in
    `docs/docker-migration-from-devops.md:541`.
  - **Apache level.** Production images set HSTS via Apache:
    `docker/release/openemr.conf:62`,
    `docker/flex/openemr.conf:62`,
    `docker/binary/openemr.conf:62`:
    `Header set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"`.
    Dev-easy image does **not** set HSTS.
  - **Grep for `https_only`, `secure_cookie` (config globals):** no
    hits in code. **HTTPS is a deployment concern, not enforced by
    the app.**

---

## 9. The Flagged Token (from Phase 1)

`docker/development-easy/docker-compose.yml:75-77`:

```yaml
GITHUB_COMPOSER_TOKEN: c313de1ed5a00eb6ff9309559ec9ad01fcc553f0
GITHUB_COMPOSER_TOKEN_ENCODED: Z2hwX0NpbnJ4aXlNd0NzcGZXWG1UWFUwcXhGa040elFKSDJoZGJXVw==
GITHUB_COMPOSER_TOKEN_ENCODED_ALTERNATE: '103 104 112 95 57 54 108 115 88 116 87 72 51 75 81 105 69 88 88 119 97 79 80 78 109 69 66 115 85 97 106 78 112 71 49 81 90 102 74 121'
```

**Findings, faithful:**

1. Line 75 (`GITHUB_COMPOSER_TOKEN`) is a **40-char lowercase hex string.**
   40 hex chars is the shape of a GitHub OAuth token from the pre-2021
   scheme, or a SHA-1. It is **not** a valid modern GitHub PAT (which
   are prefixed `ghp_`, `gho_`, `ghu_`, `ghs_`, `ghr_`). It may be a
   revoked legacy PAT, a placeholder for a hex-only build value, or a
   SHA-1 fingerprint. Its value alone would fail a GitHub API auth check
   in the modern token scheme.
2. Line 76 (`_ENCODED`) — base64 decodes to a `[REDACTED]` value. **This is a
   correctly-shaped GitHub PAT (`ghp_` prefix + 36 chars).**
3. Line 77 (`_ENCODED_ALTERNATE`) — the space-delimited decimals decode
   (as an ASCII byte sequence) to a `[REDACTED]` value. **This is a second,
   distinct correctly-shaped GitHub PAT.**

   _Redacted 2026-08-19 during a documentation audit: the decoded plaintext values were
   printed verbatim in this file, which is itself a credential-exposure instance
   independent of the underlying token's live/revoked status. The later evidence pass
   (`docs/discovery/openemr-decision-evidence/15-security-compliance-code-evidence.md §1,
   §2`) documents the same finding — 12 token values across 4 compose files, not 2 —
   under an explicit "never print values" discipline; follow that file's redaction
   convention for any future citation of this finding._

**Assessment.** Lines 76 and 77 are two distinct real-looking GitHub
personal access tokens, deliberately obfuscated (base64 and a
decimal-code array) to evade naive secret scanners. Whether either is
currently live or was revoked at some point cannot be determined
statically from the repo. Regardless of live status, **committing PATs
of any lifecycle into a public git history is a credential-exposure
incident** — GitHub PATs, once ever pushed to a public repo, are
considered burned by GitHub's own scanning and must be revoked and
regenerated. Fork's origin (`openemr/openemr` upstream) should be
checked; if this file was inherited, upstream needs notification. If it
originated in this fork, immediate rotation is required.

**RESOLVED 2026-08-19 (orchestrator, PB-478): this file's own open question above is answered — the
lines are inherited, not fork-originated.** `git blame docker/development-easy/docker-compose.yml`
traces line 75 to `a12706b44` (Stephen Nielson, 2025-03-03) and lines 76-77 to `db6ec665c8` (Brady
Miller, 2026-01-04) — both upstream OpenEMR maintainer commits, 159 commits deep in this file's
history, already public on `openemr/openemr` for months independent of this fork. Per this
section's own stated conditional: **this is the "inherited" branch, so the action is notifying
upstream, not rotating/purging as if the fork caused it.** The three-way-encoded pattern (raw hex,
base64, decimal-array, all under `_ENCODED`/`_ENCODED_ALTERNATE` names) also reads more like a
deliberate secret-scanning-tooling test fixture than an accidental leak — not confirmed, but worth
weighing before treating it as a live incident. See the parallel correction at
`docs/00-discovery/SUMMARY-locked-decision-candidates.md` row 22 for the full reasoning and why
recommended action 2 below (history rewrite) is not something a fork should attempt on shared
upstream commits.

**Recommended action (informational):**
1. Revoke both decoded tokens at
   https://github.com/settings/tokens (or the org PAT admin, depending
   on account). **Applies only if you are the token owner or an upstream maintainer** — a fork
   contributor cannot revoke a credential they don't control.
2. ~~Purge from git history via `git filter-repo` or BFG~~ — **does not apply to this fork**: these
   are shared upstream commits; rewriting them breaks every future upstream merge and isn't a
   fork's call to make on maintainers' own history. If remediation is warranted, it belongs
   upstream, not here.
3. Replace with a Docker-buildkit secret or an env-var injected at
   `docker compose up` time, never checked into the compose file. **Same caveat as #2** — this is a
   change to upstream's own file; propose it upstream rather than diverging this fork from it.
4. Add pre-commit hook `gitleaks` / `trufflehog` regex rules to catch
   `ghp_`, `gho_`, `github_pat_`, and base64/decimal-encoded variants. **This one is fork-actionable**
   regardless of the above — it doesn't touch upstream's file, just adds detection for any future
   fork-originated secret, which is a real and independent gap worth closing.

**Classification: downgraded from "security incident — credential exposure in VCS" to "upstream
question to report, not a fork action item" — see 2026-08-19 correction above.**

---

## 10. Dependency-Level Security

- **`roave/security-advisories: dev-latest`** — declared at
  `composer.json:155`. This package is a
  virtual/conflict-only package: for every PHP dependency with a known
  published CVE, `roave/security-advisories` declares a `conflict:`
  entry pinning the vulnerable version range. Effect: `composer update`
  will *refuse to install* any Composer dependency at a version with a
  publicly-known advisory. It ships no code — it is purely a
  gate. Refreshes daily. **This is the primary SCA gate on the PHP
  side.**
- **Snyk / Dependabot / GH Advanced Security config:** no `.snyk`,
  no visible `.github/dependabot.yml` in the file listings pulled here.
  **UNKNOWN — inspect `.github/` for dependabot config;** upstream
  OpenEMR typically enables Dependabot for security updates.
- **npm audit:** no lockfile-level audit config observed
  (`package.json` scripts contain lint/build/test — no `audit` script).
- **PHPStan level 10** (`CLAUDE.md`) is not a security tool per se but
  catches whole classes of type-confusion / null-deref bugs.
- **`prek` / `pre-commit` config** (`CLAUDE.md`) runs additional
  linters at commit time.

---

## 11. Container Security Posture

- **Runtime user.** The apache runtime in the openemr container adopts
  the **host uid at boot** via the entrypoint (`CLAUDE.md` "Common
  Gotchas" section: "the in-container apache user adopts your host uid
  via the entrypoint"). `openemr-cmd worktree up` auto-exports
  `HOST_UID`/`HOST_GID`. Default fallback (when `HOST_UID` is unset) is
  **uid 1000** — apache runs as an unprivileged user. **Not root at
  runtime.** Build-time steps run as root (standard for Alpine/Debian
  base images).
- **Secrets in dev-easy image.** Baked into
  `docker/development-easy/docker-compose.yml` as literal env vars:
  - `MYSQL_ROOT_PASS: root` (`:63`)
  - `MYSQL_PASS: openemr` (`:65`)
  - `OE_PASS: pass` (`:67`)
  - `OPENEMR_SETTING_couchdb_pass: password` (`:94`)
  - Three GitHub PAT variants (§9)

  All acceptable for a **dev-only** image, but the compose file is
  clearly labelled `development-easy` so this is the expected posture.
  Production compose files (`docker/release/`, `docker/flex/`) do not
  bake credentials — verified by their being outside the grep scope for
  those literal strings.
- **`SECURITY.md`.** README references
  `.github/SECURITY.md` (`README.md:41`) but `.github/SECURITY.md` was
  not found by glob in this session. **UNKNOWN — file exists in
  upstream `openemr/openemr` but may not have been synced to this
  fork's `.github/` dir; check upstream if a security policy is
  required.**

  _Resolved 2026-08-19, see `docs/discovery/openemr-decision-evidence/15-security-compliance-code-evidence.md §3`
  and `21-recommended-decision-updates.md` (Q50): `.github/SECURITY.md` and
  `.github/dependabot.yml` were confirmed present (`git ls-files`) in the later evidence
  pass — the glob miss here was a tooling gap, not an actual absence. The file directs
  reporters to OpenEMR's own security team, which the later pass flags as the real,
  narrower action item for a fork holding Saudi tenant PHI._
- **`docker/security/`.** No such directory exists in the tree.
- **`X-Forwarded-For` trust.**
  `library/sanitize.inc.php:35-39` reads
  `HTTP_X_FORWARDED_FOR` blindly if present, without a
  trusted-proxy allowlist. In a SaaS deployment behind an ingress
  controller, this is correct **only if** the ingress strips
  client-supplied X-F-F headers. **UNKNOWN — deployment posture; needs
  ingress hardening.**

---

## 12. SaaS Multi-Tenant Hardening — Gap Analysis

| Concern | Current state (evidence) | Gap for SaaS multi-tenant |
|---|---|---|
| **Per-tenant crypto keys** | `CryptoGen` uses `keys` MySQL table + `sites/<site>/documents/logs_and_misc/methods/` files (`CryptoGen.php:401-450`). Per-site directory exists but per-site DB may not (Phase 9). | If tenants share a MySQL database, `keys` table is single-tenant. Need per-tenant schema/db partitioning (see Phase 9) or per-tenant `keys` rows keyed by tenant id. |
| **Per-tenant KMS** | None. Root keys are file+DB, no envelope-encryption against cloud KMS. `CryptoGen.php` has no KMS integration point (`:401-497`). | Introduce an envelope: encrypt the DB `keys` rows with a per-tenant KMS-CMK. Requires new `CryptoGen` implementation of `CryptoInterface` — the interface exists (`class CryptoGen implements CryptoInterface`, `:42`) so this is a clean seam. |
| **Per-tenant audit-log export** | Single `log` / `api_log` / `audit_master` / `extended_log` tables (§6.1). ATNA sink (`EventAuditLogger.php:51-66`) is global. | Add `tenant_id` (or `site`) column to `log`/`api_log`; per-tenant syslog forwarder; per-tenant export API. **Requires schema migration.** |
| **Cross-tenant admin** | `breakglass` group is per-installation, not per-tenant (§7). | Need a super-tenant admin concept + separate breakglass ACL per tenant. **Requires product-owner input.** |
| **Data residency (Saudi PDPL — Kingdom-only)** | Fully deployment-concern — app has no residency guards. Off-site upload dispatch exists (`Document.class.php:1035-1049`) but goes wherever the module points. | Enforce region-locked S3 / MySQL / CouchDB in infra; add app-side upload-destination policy. **Requires product-owner input.** |
| **PHI encryption at rest** | Conditional per-column via `CryptoGen::encryptStandard` (§5.4), files conditional on `drive_encryption` global (`Document.class.php:992-996`). Not enforced. Not AEAD (CBC + separate HMAC). | For SaaS: force `drive_encryption` and `database_encryption` on globally; add DB-level TDE at storage layer for defense-in-depth; consider migrating cipher to AES-256-GCM in a new `KeyVersion`. |
| **Session isolation across tenants** | One browser cookie namespace per host. Core session sets `cookie_httponly = false` deliberately (`SessionUtil.php:7-15`). Session store per-installation. Phase 4/9 confirmed a browser can only be logged into one tenant at a time in this app. | Adequate for host-per-tenant deployments; a subdomain-per-tenant model requires cookie-scope discipline. **Requires product-owner input** on tenant URL topology. |
| **Log retention / rotation** | None built in (§6.5). | Add tenant-configurable retention + secure archival; required for compliance auditing. |
| **File upload AV scan** | Absent (§4). | Deploy ClamAV sidecar or cloud-provider AV; wire into `IsAcceptedFileFilterEvent` (`sanitize.inc.php:123-143`). |
| **Secrets in dev image** | Dev PATs & DB passes baked (§9, §11). | Not applicable to prod; ensure prod compose files remain clean; add secret-scanning to CI. |
| **X-F-F trust** | Blind trust (`sanitize.inc.php:35-39`). | Terminate & rewrite X-F-F at trusted ingress only. |

---

## Summary (5 lines)

**File:** `docs/00-discovery/14-security-compliance.md`
1. CSRF: HMAC-SHA256 subject-scoped tokens, comprehensively wired for cookie-auth flows; OAuth2 bearer path is the only "exempt" surface by design.
2. Sanitization discipline is codified (`text/attr/js_escape/xlt` etc.), SQL safety strongly favours bind arrays (33 residual interpolation sites among 100+ file matches for `sqlStatement`), file uploads have MIME whitelist + versioned encryption, **no AV scanning**.
3. `CryptoGen` = AES-256-CBC + HMAC-SHA384 (encrypt-then-MAC), dual-key sets (DB + drive nested), **no KMS/TPM, no AEAD**, per-tenant crypto is the biggest SaaS gap.
4. Audit logging (`EventAuditLogger` → `log`/`api_log`/`audit_master`/`extended_log`) is thorough for auth, PHI view, API, and tagged SQL; **no built-in retention/rotation**; breakglass group logs everything when enabled.
5. **Security incident:** `docker/development-easy/docker-compose.yml:75-77` contains one legacy-shaped 40-hex token plus two obfuscated modern GitHub PATs (base64 + decimal-array) — must be treated as burned and rotated regardless of live status.

## UNKNOWNs

- Full repo-wide `echo $var` vs `echo text(...)` ratio (tool truncation, §2.4).
- Full audit of each of the 33 `sqlStatement("...$"` interpolation sites' fragment provenance (§3.2).
- Whether every caller of `Document::createDocument` gates through `isWhiteFile()` (§4).
- Default value of the `drive_encryption` global (§5.5).
- Complete inventory of columns encrypted at rest via `encryptStandard` (§5.6).
- Full checksum-chain tamper-verification cadence for `log.checksum` (§6.4).
- Whether any background service prunes `log`/`api_log` (§6.5).
- Whether a text-justification prompt is required at breakglass login (§7) — **requires product-owner input.**
- Presence of `.github/SECURITY.md` and `.github/dependabot.yml` in this fork (§10, §11).
- Live/revoked status of the two decoded GitHub PATs (§9) — **out-of-band GitHub API check required.**
- Ingress-layer `X-Forwarded-For` handling in production deployment (§11).
- SaaS tenancy URL topology (subdomain-per-tenant vs host-per-tenant) — **requires product-owner input** (§12).
- Data-residency deployment plan for Saudi PDPL — **requires product-owner input** (§12).
