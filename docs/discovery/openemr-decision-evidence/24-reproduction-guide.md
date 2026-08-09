# 24 — Reproduction Guide

How another engineer re-runs every part of this audit and gets the same answers.

---

## 0. Prerequisites

| Requirement | Needed for | Notes |
|---|---|---|
| `git` ≥ 2.30 | everything | Must have the `upstream` remote (see §1) |
| Python ≥ 3.9 | matrix, index, module and UI scanners | No third-party packages required |
| Bash | `collect-remaining-evidence.sh` | Git Bash on Windows is fine |
| Network (read-only) | upstream fetch, Codecov, npm registry | Optional; failures are recorded, not fatal |
| PHP ≥ 8.2 + populated `vendor/` | Q31, Q37, Q55, Q70 verification | **Optional but strongly recommended** — without it these four fall back to inference from `composer.json` / `composer.lock` |
| Docker | *nothing in this audit* | The audit ran on a host where Docker cannot start; every step below works without it |

### 0.1 The one environment trick that matters

This checkout lives on a Google Drive mount where a recursive filesystem grep takes **minutes and often times
out** (~92% of I/O is Drive metadata round-trips). Every search in this audit therefore uses:

```bash
git grep <pattern> HEAD -- <pathspec>
```

which reads blobs from the packfiles — about **1 second** for the same query. It has a second benefit that
applies on any filesystem: results are pinned to an exact commit, which is what the evidence schema requires.

**If you reproduce this on fast local storage, keep the `HEAD` argument anyway** so your line numbers match
the citations. `ripgrep` against the working tree will drift if the tree is dirty.

---

## 1. Repository baseline

```bash
git rev-parse --show-toplevel
git rev-parse HEAD                       # expect 631f2b38cf633769c305233f88cdf9c73ca80657
git branch --show-current                # expect master
git remote -v                            # redact credentials before recording
git status --short --branch
git log -1 --format=fuller
git rev-parse --is-shallow-repository
```

**Expected output:** `01-run-metadata.json`, `02-repository-baseline.md`.

Do **not** stage, stash, reset or clean anything. The working tree is expected to be dirty; the audit is
read-only.

---

## 2. Upstream fetch and stable-tag selection

```bash
git remote add upstream https://github.com/openemr/openemr.git   # only if absent
git fetch upstream --tags --prune

# Rank tags by DATE, never lexically:
git for-each-ref --sort=-creatordate \
    --format='%(creatordate:iso8601) %(refname:short) %(objecttype) %(objectname:short)' refs/tags | head -25

# Corroborate against release branches:
git ls-remote --heads upstream | grep -E 'rel-[0-9]+'
```

**Selection rule:** newest tag that is **not** a prerelease. The `v8_1_*-test.<sha>` tags are
release-candidate builds and must be excluded. `v8_2_0` (2026-07-08) is corroborated by the existence of
`rel-820` and `release-prep/rel-820`.

```bash
git rev-parse v8_2_0^{commit}            # 6125a2fd8089c8bcc3848071c1293c60e27a7585
git rev-parse upstream/master
git merge-base HEAD v8_2_0
git merge-base HEAD upstream/master
```

### 2.1 The central check — run this first, everything else depends on it

```bash
git merge-base --is-ancestor HEAD upstream/master && echo "fork is ancestor"
git rev-list --count upstream/master..HEAD     # expect 0  <-- zero fork-only commits
git rev-list --count HEAD..upstream/master     # upstream commits since fork
```

If the first two do not return "fork is ancestor" and `0`, the fork has acquired local commits since this
audit and **every conclusion in `05-upstream-fork-drift.md` must be re-derived.**

---

## 3. Q36 — module byte identity

```bash
git ls-tree -d --name-only HEAD            interface/modules/custom_modules/
git ls-tree -d --name-only v8_2_0          interface/modules/custom_modules/
git ls-tree -d --name-only upstream/master interface/modules/custom_modules/

git diff --no-renames --name-status v8_2_0 HEAD -- interface/modules/custom_modules/
git diff --no-renames --stat upstream/master HEAD -- interface/modules/custom_modules/
git status --porcelain -- interface/modules/custom_modules/    # expect empty
```

**Expected:** 7 tracked module directories in all three refs; exactly **one** differing file vs `v8_2_0`
(`oe-module-comlink-telehealth/tests/bootstrap.php`). Attribute it before concluding anything:

```bash
git log --oneline v8_2_0..HEAD -- \
  interface/modules/custom_modules/oe-module-comlink-telehealth/tests/bootstrap.php
# -> 0ec6697e0 feat(bc): add internal deprecation utility (#12753)  == an UPSTREAM commit
```

Blob-level manifests and the per-module verdict table:

```bash
python tools/discovery/openemr-decision-evidence/module-byte-identity.py
```

**Expected output:** `06-module-drift-inventory.csv`, `17-q36-module-byte-identity.md`,
`evidence/manifests/{fork,upstream}-module-blobs*.txt`.

---

## 4. Dependency and module-runtime audit (Q31, Q37, Q55, Q70)

Requires a populated `vendor/`. **Never run `composer install` over a working tree you care about** — use a
scratch clone if you must populate it.

```bash
php -r '$j=json_decode(file_get_contents("vendor/composer/installed.json"),true);
foreach($j["packages"] as $p){ if(($p["type"]??"")==="openemr-module"){
  echo $p["name"]," ",$p["version"]," -> ",$p["install-path"],"\n"; } }'
```

**Expected:** exactly one row — `claimrevolution/oe-module-claimrev-connect v2.1.6` →
`../../interface/modules/custom_modules/oe-module-claimrev-connect`.

Read the installer algorithm from source (this is the Q37 answer):

```bash
cat vendor/openemr/oe-module-installer-plugin/src/Plugin.php                  # class + registration
cat vendor/openemr/oe-module-installer-plugin/src/CustomModuleInstaller.php   # getInstallPath()
```

**Key line:** `getInstallPath()` returns `interface/modules/custom_modules/<last segment of package name>` —
**the vendor is discarded**, which is the overlay risk recorded in Q37/Q70.

Confirm the tracked modules are untouched by composer, and that claimrev is gitignored for that reason:

```bash
grep -n "claimrev" .gitignore
git check-ignore -v interface/modules/custom_modules/oe-module-claimrev-connect/composer.json
```

---

## 5. All remaining counts (Q11, Q49, Q62–Q64, Q67–Q69, Q73, and more)

One script produces every count with a saved match list and checksums:

```bash
bash tools/discovery/openemr-decision-evidence/collect-remaining-evidence.sh
```

**Expected output:**

- `evidence/raw/count-<slug>.txt` — full match list per sink, each with a header recording the exact
  command, the ref and the timestamp.
- `evidence/raw/remaining-counts.tsv` — slug / count / description.
- `evidence/manifests/remaining-counts-sha256.txt` — checksums.
- `evidence/manifests/q3-deployment-artifacts.txt`, `q39-workflow-inventory.txt`,
  `q40-inferno-artifacts.txt`, `q54-tools-inventory.txt`, `q60-charset-collation.txt`,
  `q62-fhir-service-files.txt`.

**Headline numbers to expect:** `sqlStatement(` 2,025 · `QueryUtils::` 1,653 · `sqlQuery(` 1,454 ·
`sqlFetchArray(` 1,354 · `sqlInsert(` 251 · Doctrine 48 → **6,785 total** (Q11/Q73). Escaping:
`echo xlt(` 9,476 · `echo attr(` 2,060 · `echo text(` 2,054 · Smarty `nofilter` **0** ·
`innerHTML` 390 (Q67). `isWhiteFile(` 3 vs `createDocument(` 26 (Q49). `helm/k8s` **0** (Q3/Q41).

---

## 6. Q56 — React 15 consumer search

Grep-only tracing is already captured in `evidence/snippets/q56-react15-consumer-graph.md`. The **decisive**
test was not run and is the remaining gap:

```bash
# Off the Drive mount (npm ci fails on G: with EBADF/EPERM):
cd C:/openemr-stack/build
npm ci && npm run build && cp -r public public.with-react
# remove the napa react entry from package.json, then:
npm ci && npm run build
diff -r public.with-react public
```

**Decision rule:** remove React 15 only if the build output is byte-identical. Do **not** declare it dead on
grep evidence alone.

---

## 7. Q57 — ID generation trace

```bash
git grep -n "generateId"                  HEAD -- 'src/*' 'library/*'
git grep -n "class Document"              HEAD -- 'library/classes/Document.class.php'
git grep -n -A4 "id-space"                HEAD -- 'src/Services/InsuranceCompanyService.php'
git grep -n "FOREIGN KEY"                 HEAD -- 'sql/database.sql'   # expect 0 outside comments
```

**Expected:** `insurance_companies.id` shares an id-space with `pharmacies` via `addresses.foreign_id`
(documented at `InsuranceCompanyService.php:433-436`). Full traces in
`evidence/snippets/q57-*-id-trace.md`.

---

## 8. Q59 — theme / branding runtime trace

```bash
git grep -n "documents/theme" HEAD -- '*.php' '*.twig' '*.js'     # expect 0 — the path is inert
git grep -n "css_header"      HEAD -- 'interface/globals.php'
git grep -n "themes_static_relative\|OE_SITE_WEBROOT" HEAD -- 'interface/globals.php'
git grep -n "class LogoService" HEAD
```

**Expected:** zero runtime references to `sites/<tenant>/documents/theme/`. Per-tenant branding is logos via
`LogoService` (`src/Services/LogoService.php:75-108`) plus `sites/<site>/config.php`
(`interface/globals.php:649`). Table: `evidence/snippets/q59-theme-runtime-path-table.md`.

---

## 9. Q61 / Q71 — DWV locales

```bash
python tools/discovery/openemr-decision-evidence/count-arabic-translations.py
```

For upstream DWV locales, inspect the published package **in a temp directory** — never install into the app:

```bash
npm pack dwv@0.27.1 --pack-destination "$TMPDIR"
tar -tzf "$TMPDIR"/dwv-0.27.1.tgz | grep locales
```

**Expected:** 9 locales (de, en, es, fr, it, jp, ro, ru, zh) and **no Arabic**. Per-locale key counts in
`evidence/manifests/dwv-locales.csv`.

---

## 10. Q65 — billing extension points

```bash
git grep -n "class\|interface" HEAD -- 'src/Billing/BillingProcessor/*.php'
git grep -n "buildProcessingTaskFromPost" HEAD
git grep -n "dispatch\|EventDispatcher" HEAD -- 'src/Billing/'    # expect 0
git grep -n "BillingExport" HEAD
```

**Expected:** hard-coded `if/elseif` ladder at `BillingProcessor.php:161-192`; **zero** event dispatch in
`src/Billing/`. Call graph: `evidence/snippets/q65-billing-call-graph.md`.

---

## 11. Q66 — ClaimRev protocol analysis

Requires the composer-installed module on disk.

```bash
find interface/modules/custom_modules/oe-module-claimrev-connect -type f | sort
grep -rn "FHIR\|ClaimResponse\|CoverageEligibility\|837\|835\|270\|271" \
     interface/modules/custom_modules/oe-module-claimrev-connect/src/
```

**Discipline:** the word "Claim" alone proves nothing. For each hit, classify it as a FHIR R4 resource, a
generic insurance claim, an X12 claim, a UI label, or plain REST transport. Result matrix:
`evidence/snippets/q66-claimrev-reuse-matrix.csv`.

---

## 12. Q72 — UI responsiveness inventory

```bash
python tools/discovery/openemr-decision-evidence/scan-ui-inventory.py
```

**Expected output:** `18-q72-ui-responsiveness-inventory.csv` (5,460 rows, 17 columns),
`19-q72-ui-responsiveness-summary.md`, `evidence/raw/q72-{scanner-output.json,file-list.txt,scanner-exclusions.txt}`.

**Verify the scanned set before trusting the totals:**

```bash
sha256sum docs/discovery/openemr-decision-evidence/evidence/raw/q72-file-list.txt
# expect eeaee99e60392dff40a968d5961e552812904bdbee842d5715f4d50f359d776f
```

A different hash means the exclusion rules or the tree changed — re-derive the totals rather than reusing them.

---

## 13. Control-plane constraints

```bash
git grep -n "site_id\|OE_SITE_DIR\|OE_SITES_BASE" HEAD -- 'interface/globals.php'
git grep -n "HTTP_HOST"        HEAD -- 'interface/globals.php' 'index.php'
git grep -n "CORE_SESSION_ID"  HEAD -- 'src/Common/Session/SessionUtil.php'
git grep -n "connection_pooling_off\|enable_database_connection_pooling" HEAD
git grep -n "opendir"          HEAD -- 'admin.php'
```

**Expected:** site resolution at `interface/globals.php:277-335`; `HTTP_HOST` consulted **only** when auth is
ignored (`:283-297`); cookie name a global constant (`SessionUtil.php:81`). Full analysis:
`16-control-plane-constraints.md`.

---

## 14. Auth / security spot checks

```bash
git grep -n "REMOTE_USER"        HEAD          # expect 0
git grep -n "mod_auth_openidc"   HEAD          # expect 0
git grep -n "force_mfa\|mfa_required" HEAD     # expect 0
git grep -n "mfa\|MFA"           HEAD -- library/globals.inc.php   # expect 0
git grep -n "launch/encounter"   HEAD          # expect 4, ALL documentation
git grep -n "CONTEXT_STANDALONE_ENCOUNTER" HEAD  # expect 1, the unused declaration
git grep -n "collectIpAddresses" HEAD
git grep -n "checksum"           HEAD -- 'src/Common/Logging/**/*.php'
git grep -in "clamav\|antivirus" HEAD          # expect 0
git ls-files | grep -iE "SECURITY\.md|dependabot"   # expect 3 files — both ALREADY exist
git grep -n -B4 "certain data stored in the database" HEAD -- library/globals.inc.php
```

**Secrets discipline:** to locate the hardcoded tokens without printing them:

```bash
git grep -n "GITHUB_COMPOSER_TOKEN" HEAD | awk -F: '{print $2":"$3}'
```

Never write a token value to any file.

---

## 15. Q51 — live coverage

```bash
curl -s https://api.codecov.io/api/v2/github/openemr/repos/openemr/ | \
  python -c "import json,sys; t=json.load(sys.stdin)['totals']; print(t)"
```

**Recorded during this run:** 28.66% (files 4028, lines 428660, hits 122880), updatestamp
2026-08-07T06:02:21Z. Captured at `evidence/raw/q51-codecov-api-response.txt`.

**Caveat to preserve:** this measures **upstream** `openemr/openemr`. The fork is not onboarded to Codecov;
the number is valid only as a proxy because fork HEAD is a strict ancestor of upstream master. Never present
it as the fork's own measured coverage.

---

## 16. Full Q1–Q75 matrix generation

```bash
python tools/discovery/openemr-decision-evidence/build-question-matrix.py
python tools/discovery/openemr-decision-evidence/build-file-index.py
```

`build-question-matrix.py` holds the **single source of truth** for all 75 questions and emits both
`03-question-status-matrix.csv` and `04-question-evidence.json`, so they cannot disagree. It self-validates
that exactly `Q1`…`Q75` are present and exits non-zero otherwise.

**Expected tallies:** answered 56 · partially answered 12 · contradicted 4 · external decision required 3.
Confidence: CONFIRMED 59 · HIGH 10 · MEDIUM 4 · LOW 1 · EVIDENCE-BLOCKED 1.

To amend a finding, edit the `add(q(...))` entry in that script and re-run — never hand-edit the CSV or JSON.

---

## 17. Archive

```bash
python tools/discovery/openemr-decision-evidence/build-archive.py
```

Scans generated files for secret-shaped strings, builds
`openemr-decision-evidence-<SHORT_SHA>.zip`, and writes the archive hash to
`evidence/manifests/archive-sha256.txt`. Excludes `.git`, `vendor`, `node_modules` and any nested archive.

---

## 18. What could NOT be reproduced on this host

| Step | Blocker | Consequence |
|---|---|---|
| DB-backed, API, E2E, Selenium suites | GCE VM without nested virtualization — the Docker engine cannot start | Runtime behaviour was never exercised; all findings are static |
| `npm ci` in the repo | Drive mount cannot service npm's create/delete churn (EBADF / EPERM) | Q56's decisive build-diff is outstanding |
| Live DB collation check (Q60) | No provisioned tenant on this host | Q60 remains `partially_answered` |

If you reproduce on a Docker-capable host, the highest-value additions are: the Q56 build diff, the Q60
Arabic sort test, and a live run of the isolated suite to confirm the CI gate locally.
