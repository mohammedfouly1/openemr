# EV-045 — UPSTREAM MAINTENANCE TARGET ANALYSIS

**Requirement:** RDY-0045 · **Gates:** G3 G6 · **Date:** 2026-08-14
**Method:** analysis only. **No fetch, merge, rebase, pull or reset was performed.** Every figure
below comes from refs already in the local object store.

---

## 0. The headline, stated first because it changes a risk rating

The register has recorded, since Phase 2A, that the product is **"418 commits behind and divergent"**
and R-03 rates that **severe — security and reputational**.

**418 is the distance to `upstream/master`. `master` is the wrong target for this branch.**

Measured against the correct target, `upstream/rel-820`:

| | vs `upstream/master` | vs **`upstream/rel-820`** |
|---|---:|---:|
| Merge-base | `b91c12aee` (2026-07-01) | **`6125a2fd8` — `chore(release): prep 8.2.0 (#12742)`, 2026-07-08** |
| We are ahead | 53 | **37** |
| We are behind | **418** | **83** |
| HEAD an ancestor? | No | No |

**And of those 83 commits, the entire runtime-code content is three lines.**

---

## 1. The branch is genuinely rel-820-based — this is now proven, not assumed

The merge-base with `upstream/rel-820` is **`6125a2fd8 chore(release): prep 8.2.0 (#12742)`** — the
8.2.0 release-preparation commit itself. The branch forks from the 8.2.0 release line, which matches
`version.php` (`$v_major=8, $v_minor=2, $v_patch=0, $v_tag=''` → 8.2.0 production).

**So the product is not "an old master". It is 8.2.0 plus 37 Thiqa commits.**

## 2. What the 83-commit gap actually contains

By conventional-commit type:

| Type | Count |
|---|---:|
| `ci` | **61** |
| `chore` | 10 |
| `docs` | 8 |
| `feat` | 2 |
| `test` | 1 |
| `fix` | **1** |

**Security-relevant commits matching `security|vuln|CVE|XSS|SQL inject|CSRF|auth|escape|sanitiz`:
zero.**

### The only four non-CI/chore/docs commits, examined individually

| Commit | Subject | Files touched |
|---|---|---|
| `dd5ebc069` | `fix(php86): drop return statements from constructors (#12978)` | `src/Billing/EdiHistory/X12File.php`, `src/Gacl/Gacl.php`, one PHPStan baseline — **3 lines of runtime code** |
| `e9af1d52b` | `feat(release): finalize job updates release-finalize PR post-tag` | `.github/workflows/release-prep.yml`, `docs/RELEASE_PROCESS.md` — **CI and docs only** |
| `9b9276d80` | `feat(release): openemr-tag dispatch now carries prev_release` | `tools/release/`, release test fixtures — **release tooling only** |
| `0a4680b76` | `test(release): drop leftover 8.1.0 refs from dispatch fixtures` | test fixtures — **no runtime impact** |

**The complete runtime delta between this deployment and upstream `rel-820` is a three-line PHP 8.6
forward-compatibility cleanup in two files.** Nothing else in the gap ships to a customer.

## 3. What this means, and what it does not

**It does mean** the patch-currency position is far better than the register states, and R-03's
severity should be re-derived. Selling patch currency while 1 fix and 79 CI/docs commits behind a
release branch is an ordinary maintenance posture, not a contradiction.

**It does not mean patching is solved:**

- **The local refs are stale.** `upstream/rel-820` is at `87dcd0fbc`, dated **2026-08-04**; today is
  2026-08-14. **83 is a floor against a ten-day-old snapshot**, and no fetch was performed because
  the brief forbids it. The true figure needs `git fetch upstream` — a read-only operation, but one
  outside this analysis's authority.
- **The branch is still divergent.** HEAD is not an ancestor of `rel-820`, so adopting upstream
  changes is a merge or rebase, not a fast-forward. 37 commits of Thiqa work would have to survive it.
- **13 core files are patched** (`docs/branding/adr/patch-records.md`, PR-01…PR-14). Those are the
  conflict surface. Risk R-1 names them, and the V-09 dry-run has only ever examined six.
- **`rel-820` will stop receiving fixes** when 8.3.0 ships. The target decision is therefore not
  permanent — it defers the master question rather than answering it.

## 4. A version inconsistency this analysis confirms

`version.php` reads **8.2.0**; the `version` **table in the database** reads **8.3.0-dev** (PB-031).
The schema is `$v_database = 541` in both, so nothing is broken, but the deployment is not cleanly
either release: **8.2.0 source on a database stamped by an 8.3.0-dev codebase.** Any migration
decision must account for it — a future upgrade path keyed on the `version` table will believe it is
upgrading from 8.3.0-dev.

## 5. Recommendation

**Adopt `upstream/rel-820` as the maintenance target**, on this evidence:

1. The branch demonstrably forks from the 8.2.0 release-prep commit — rel-820 is where it already lives.
2. The gap is **83 commits, of which 3 lines are runtime code**, versus 418 against master.
3. There are **no outstanding security patches** on that line.
4. `master` is 8.3.0-dev — a development branch. Tracking it would mean shipping a customer a
   pre-release, which contradicts the pilot posture.

**Then, separately, decide the 8.3.0 upgrade** as a planned migration when 8.3.0 releases, rather
than conflating it with routine patching now.

### Immediate next actions, in order

| # | Action | Authority needed |
|---|---|---|
| 1 | `git fetch upstream` — read-only, replaces the ten-day-old floor with the real number | **Owner** — the brief forbids fetch without it |
| 2 | Owner confirms rel-820 as the target | **Owner decision** |
| 3 | Re-run the V-09 conflict dry-run against **all 14 patch records**, not the six it has covered | Engineering |
| 4 | Apply `dd5ebc069` (3 lines, 2 files) as the only runtime-relevant catch-up | Engineering, after 2 and 3 |
| 5 | Re-derive R-03's severity from the corrected figure | Product Marketing |

**RDY-0045 is not closed by this document.** Its acceptance requires an established update method, a
rollback approach, a regression check and a named cadence. This settles only the first and most
blocking question — *which upstream* — and it settles it with measurements rather than assumption.

---

# ADDENDUM — V-09 conflict dry-run, re-run against ALL patch records (2026-08-14)

**This is action 3 of §5, now done.** No fetch, no merge, no working-tree change —
`git merge-tree --write-tree --name-only HEAD upstream/rel-820`, which computes the merge in memory.

## Result: one conflict in the entire merge, and it is not a patched file

```
exit=1 (conflicts present)
conflicted files: 1
  composer.json
```

**Cross-referenced against every file in `patch-records.md` (PR-01 … PR-16): zero conflict.**

| Patched core file | Conflicts with `rel-820`? |
|---|---|
| `admin.php` · `interface/globals.php` · `setup.php` · `sql_patch.php` · `sql_upgrade.php` · `ippf_upgrade.php` | **No** |
| `FhirMetaDataRestController.php` · `OAuth2AuthorizationListener.php` · `ProductRegistrationService.php` · `TelemetryService.php` | **No** |
| `EncounterService.php` (PR-14) · `MainMenuRole.php` (PR-15) | **No** |
| `primary_logo.html.twig` · `templates/error/*.twig` | **No** |
| `front_office.json` (PR-16) | **No** |

## Risk R-1 is measurably unfounded against this target

R-1 reads *"upstream rebase conflicts in the 6 patched core files"*, and `patch-records.md` flags that
**V-09 had only ever examined six of them** — leaving eleven unchecked, including `setup.php` and
`sql_upgrade.php`, described as the two most upstream-churned files in the set.

**All sixteen are now checked. None conflicts.** The earlier caveat is discharged.

## The single conflict is mechanical, and both sides are simply kept

`composer.json`. Both branches edited adjacent lines of the same JSON blocks:

| Side | Adds |
|---|---|
| `upstream/rel-820` | `symfony/mime` dev dependency · `OpenEMR\Tests\Acceptance\` autoload namespace · an `acceptance` script |
| This branch | `OpenEMR\Branding\` autoload namespace · `@branding-tokens-check` in the code-quality chain |

**No semantic disagreement — neither side removes or redefines anything the other needs.** Resolution
is to keep both. It is a one-file, few-line manual merge.

**This corroborates EV-046's decision not to remove `oe-module-claimrev-connect`.** `composer.json` is
*already* the single conflict file; deliberately adding a further divergence there — to delete an
inert package — would turn a trivial merge into a recurring one.

## Caveats, unchanged

- **Still measured against a ten-day-old local ref** (`87dcd0fbc`, 2026-08-04). A fetch could add
  commits and therefore conflicts. **This is a floor.**
- **A clean `merge-tree` is not a passing test suite.** It proves the *text* merges, not that the
  result runs. The regression check RDY-0045 requires is still outstanding.
- Nothing here was applied. HEAD is unchanged.

## Revised recommendation

The catch-up to `rel-820` is now characterised end to end: **83 commits behind, three lines of runtime
code, no security patches, and a single mechanical `composer.json` conflict.** That is a small,
well-understood operation — not the *"severe, security and reputational"* risk the register carries.

**R-03 and R-1 should both be re-derived.** The remaining unknown is not the conflict surface; it is
the ten-day gap and the regression check.
