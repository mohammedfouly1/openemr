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

---

# ADDENDUM 2 — AGENT-GIT decision pack (2026-08-16)

**PB-191. Owner: AGENT-GIT (Phase 2B, Track F, `CONTINUATION` of Agent A's `EV-045`).** Method:
`git fetch upstream master rel-820 --no-tags` and `git fetch origin feat/thiqa-branding-foundation
--no-tags`, both read-only; no merge, no rebase, no branch or ref created on the live repo. All
figures below are live, re-run at the timestamps shown. **At least one other session (AGENT-OPS) was
observed committing to this same shared working tree during this analysis** — see §4 for the direct
evidence and why it matters for the recommendation.

## 1. Re-measured divergence — reconciled against both prior figures

Fetched 2026-08-16 ~16:40 UTC. `upstream/master` = `58618a3cd` (2026-08-16 12:34 EDT);
`upstream/rel-820` = `47d966dbc` (2026-08-16 07:38 PDT) — both now current-day, not the 10-day-stale
`87dcd0fbc` EV-045's addendum flagged as a floor.

| | vs `upstream/master` | vs **`upstream/rel-820`** |
|---|---:|---:|
| Merge-base (rel-820 unchanged from original EV-045) | `b91c12aee` | **`6125a2fd8`** (same commit — the branch's fork point from rel-820 has not moved) |
| `origin/feat/thiqa-branding-foundation` (`6de7cdcc1`) ahead / behind | 93 ahead / 483 behind | **77 ahead / 91 behind** |
| Local `HEAD` (`cf82fe8f7`, moved twice during this analysis — see §4) ahead / behind | 117 ahead / 483 behind | **101 ahead / 91 behind** |

**Reconciliation with EV-045's original 83-behind-rel-820 (2026-08-13):** now 91 behind — consistent
forward drift, +8 over 3 days of upstream `rel-820` commits landing, nothing anomalous.

**Reconciliation with PB-142/`EV-AUDIT-agentA-20260816.md` §4's 482 behind / 94 ahead of
`upstream/master`:** that session measured 483−1=482 behind (one fewer upstream commit had landed
yet) and 94 ahead (its own local HEAD at check time, between my 93-ahead origin figure and my
117-ahead current-HEAD figure — consistent with a HEAD that had accumulated a few more local commits
than origin but fewer than today's). **No discrepancy to explain — both figures are the same
measurement taken a few hours and a few commits apart on a branch three-plus sessions are actively
writing to.** This is expected instability of a live figure, not drift in the underlying method.

**Conflict surface, re-run against current `rel-820` with 101 commits ahead (vs EV-045's 37):**

```
git merge-tree --write-tree --name-only origin/feat/thiqa-branding-foundation upstream/rel-820
git merge-tree --write-tree --name-only HEAD upstream/rel-820
```

**Identical result both times: exactly one conflicted file, `composer.json`. Every one of the 16
`patch-records.md` files (PR-01…PR-16) still conflict-free.** The conflict surface has not grown even
though the branch has nearly tripled its ahead-count since EV-045. This is a meaningful finding on its
own: the growth since 2026-08-13 is almost entirely `docs/` (readiness register, evidence files,
claims ledger) plus a handful of code fixes (RB-22 theme rebuild, RDY-0043 menu fix, seeder changes)
that don't touch any upstream-churned file.

## 2. The ~70-commit mystery — resolved, not just narrowed

PB-142 flagged: gap-inventory (2026-08-15) measured **71 unpushed**; `EV-AUDIT-agentA-20260816.md`
measured **1 unpushed** the next day; actor unidentified. Direct evidence, gathered this session:

**`origin/feat/thiqa-branding-foundation`'s tip `6de7cdcc1` (2026-08-16 00:04:35 UTC) has exactly ONE
commit on top of `4d09baef1` (2026-08-14 04:26:14 UTC) — `git log
4d09baef1..6de7cdcc1 --oneline` returns one line.** So origin's own history contains no unexplained
new commits in the 2026-08-15/16 window — there is no batch of ~70 freshly-authored commits to
account for.

**What actually happened: a pre-existing local backlog was pushed, not new work created.** Listing
every commit reachable from origin's current tip dated 2026-08-13 through 2026-08-16
(`git log origin/feat/thiqa-branding-foundation --format="%H|%ai|%an|%ae|%s"` filtered to those dates)
returns **60 commits**, all of which:

- have a single author identity: `mohammedfouly1 <mselfouly2008@yahoo.com>` — matching the Owner's own
  configured git user on this machine (`git config user.name` reports the same), not any of the
  agents' own commits made moments before under the same identity (this repo's convention is that
  every session commits as the Owner's identity — see `AGENT-CLAIMS.md` PB-201's note on the
  Rule-4a incident, which describes the same single-identity pattern)
- follow strict Conventional-Commits format with no exceptions (`grep -vE
  "^(feat|fix|docs|chore|test|refactor|style|perf|build|ci|revert)\("` on the subject lines returns
  zero non-conforming rows)
- distribute as **16 on 2026-08-13, 43 on 2026-08-14, 0 on 2026-08-15, 1 on 2026-08-16** — i.e. the
  bulk (43) were committed on 2026-08-14, sat locally unpushed through all of 2026-08-15 (the day
  gap-inventory measured 71 unpushed against an even-older origin position), and were pushed to
  `origin` sometime before `6de7cdcc1` was created at 00:04 on 2026-08-16.

**Conclusion: the "70 commits" are not new, not foreign, and not from an unidentified actor's
authorship — they are Agent A's/Agent B's own Phase 2A work (PB-047 through PB-085-range entries,
`EV-045`, `EV-046`, the RDY-0044-B baseline fixes, etc., all matching known PB entries already read
in this analysis) that had simply never been pushed. The open question was never "who wrote this
code" — it is "who ran `git push`," and that remains genuinely unresolved: the single shared commit
identity gives no session-level attribution, and no evidence collected here or in `EV-AUDIT` narrows
it further.** What this session adds beyond `EV-AUDIT`'s finding is the content proof: every commit in
the pushed range is accounted for, on-brand, and non-anomalous. There is nothing in the push itself
that needs investigating as a security or provenance concern — only the mechanical question of which
console ran the command, which is a "check with the Owner directly" item, not a git-forensics one.

## 3. Merge-vs-rebase, re-assessed at 101-ahead (vs EV-045's 37-ahead)

**Recommendation: merge, not rebase — the case is stronger now than at EV-045's time, not weaker.**

- **Rebase would rewrite ~93–117 commits'** SHAs and require a force-push. §4 below shows this branch
  had a new commit land *during this analysis*, from a different concurrent session. A rebase run
  against a branch under live, unpredictable concurrent write load has a concrete failure mode beyond
  the usual "force-push stomps a collaborator's fetch": if another session runs `git commit` on this
  *same shared working tree* while a rebase is mid-flight (detached HEAD, `.git/rebase-merge/`
  present), the two operations are not merely racing refs — they are racing the same on-disk working
  tree and index. That is a stronger hazard than the standard rebase-on-a-shared-branch warning; it is
  specific to this repo's setup (single native checkout, multiple sessions, no per-agent worktrees).
- **Merge preserves every existing SHA.** No force-push, no rewritten history, no window in which the
  branch is in a rebase-in-progress state that a concurrent `git commit` could corrupt.
- **The conflict surface is one file and is now rehearsed clean** (§5) — a rebase would hit that same
  `composer.json` conflict on whichever of the 93-117 replayed commits first touches it, repeatedly, if
  any earlier commit in the replay sequence also touches the file (not checked — out of scope while a
  merge resolves it exactly once, which is itself a reason to prefer merge here).

**Counter-argument, stated because a recommendation without one is not permitted here:** a merge commit
folding in ~91 upstream commits produces a less linear history — `git bisect` and `git blame` across
the merge boundary become messier, and if this branch is ever intended to feed back into an upstream
contribution or undergo a compliance audit that expects a clean, rebased commit sequence, rebase would
have produced a more reviewable artifact. That is a real, permanent cost merge does not avoid. It is
outweighed here only because of the *current* operational fact — active concurrent writers — not
because merge is intrinsically better; once this branch reaches a quiescent, single-writer state (e.g.
a pre-release freeze), a squash-and-rebase cleanup pass remains a legitimate future option, just not
now and not as the mechanism for adopting `rel-820`.

## 4. Direct, live evidence of concurrent writes to this exact branch (not hypothetical)

`git rev-parse HEAD` returned `a7f50df98` early in this analysis and **`cf82fe8f7` roughly 15 minutes
later, unprompted by this session** — `git log a7f50df98..HEAD` shows the new commit is
`cf82fe8f7 docs(readiness): PB-181 -- RDY-0083 logoff-survival ruled out...`, i.e. AGENT-OPS's own
work landing live, in this same working tree, while this document was being written. This is offered
as direct support for §3's rebase-risk argument, not as a new finding to investigate — AGENT-OPS's
work is in scope for that session, not this one.

## 5. Rehearsed rollback plan — the conflict resolution was actually executed, not just described

**What was rehearsed, concretely, against extracted blobs in `scratchpad/composer-merge-rehearsal/`
(no live-repo state touched):**

```bash
BASE=$(git merge-base origin/feat/thiqa-branding-foundation upstream/rel-820)   # 6125a2fd8
git show $BASE:composer.json                              > base.json
git show origin/feat/thiqa-branding-foundation:composer.json > ours.json
git show upstream/rel-820:composer.json                   > theirs.json
cp ours.json merged.json && git merge-file merged.json base.json theirs.json   # exit 1, one conflict
```

Conflict landed exactly where EV-045 predicted — the `autoload-dev.psr-4` block, where this branch
added `OpenEMR\Branding\` and upstream added `OpenEMR\Tests\Acceptance\` adjacent to a shared,
unmodified `OpenEMR\Release\` line. Resolved by hand (keep all three lines, dedupe the shared one),
then verified:

- `python3 -c "import json; json.load(open('merged.json'))"` → **valid JSON**
- `diff ours.json merged.json` → only upstream's additions appear as new (`symfony/mime`, the
  `Acceptance` namespace, the `acceptance` script) — nothing from our side dropped
- `diff theirs.json merged.json` → only our additions appear as new (`Branding` namespace, the
  `@branding-tokens-check` quality-chain hook, the `branding-tokens-check` script) — nothing from
  upstream's side dropped

**This confirms EV-045's "no semantic disagreement — keep both" call by construction, not assertion.**
The full merge (steps below) has not been run against the live repo — that remains gated on RDY-0082 +
G1 per the standing constraint.

**The live-repo procedure, precise and ready to run when the gate clears (not yet executed):**

```bash
# 0. Preconditions
git status --short                       # must be clean
git rev-parse HEAD origin/feat/thiqa-branding-foundation > pre-merge-shas.txt

# 1. Snapshot BEFORE touching anything — additive, non-destructive, safe even mid-analysis
git tag pre-rel820-merge-20260816 HEAD
git push origin pre-rel820-merge-20260816     # survives even if local state is lost

# 2. Refresh and merge
git fetch upstream rel-820
git merge upstream/rel-820 --no-ff -m "merge: adopt upstream/rel-820 (EV-045/PB-191 decision pack)"
# stops on composer.json — resolve exactly as rehearsed in §5 above (keep all three psr-4 lines,
# keep symfony/mime, keep both new autoload namespaces, keep both new scripts)
git add composer.json
git status                                # confirm composer.json is the ONLY path needing `add`
git commit                                # completes the merge

# 3. Regression check BEFORE push (RDY-0045's own acceptance criterion — still outstanding)
#    — full suite per CLAUDE.md/CLAUDE.local.md, not skipped

# 4. Push only after 3 passes
git push origin feat/thiqa-branding-foundation
```

**Rollback — two branches depending on whether the merge commit reached `origin`:**

```bash
# A. Merge committed locally, NOT yet pushed — instant, zero data loss, tag pins the exact point:
git reset --hard pre-rel820-merge-20260816

# B. Merge already pushed and shared — do NOT reset --hard + force-push (strands anything another
#    session pushed on top in the interim, a concrete risk per §4). Revert forward instead:
git revert -m 1 <merge-commit-sha>
git push origin feat/thiqa-branding-foundation
```

`pre-rel820-merge-20260816` was **not** created on the live repo by this session — tag-name
availability was confirmed (`git tag -l "pre-rel820*"` → empty) so the command is ready to run
verbatim, but creating it now would be a live-repo mutation this brief's "no branch changes" excludes.

## 6. Recommendation, with its strongest counter-argument

**Recommend: adopt `upstream/rel-820` via a single `--no-ff` merge, executed only after RDY-0082 and
G1 are confirmed stable, using the exact procedure in §5.** Basis: fork point unchanged since EV-045,
conflict surface unchanged at one mechanical file across 2.7x more ahead-commits, zero security-patch
content in the gap (per EV-045 §2, unchanged), and the resolution has now been executed and verified
byte-for-byte rather than merely predicted.

**Strongest argument against this recommendation:** *timing, not method.* The branch is being written
to by multiple uncoordinated sessions in real time (§4), `origin` and local `HEAD` are each moving
targets even within a single measurement pass, and RDY-0082's restore-proof — the thing this merge is
explicitly gated on — is still in progress. Merging into a branch whose own baseline is unstable risks
having to redo the regression check against a HEAD that no longer matches what was validated, or
discovering post-merge that a commit which landed between validation and push (exactly the AGENT-OPS
race observed in §4) changes the merge result. **The strongest mitigation is procedural, not technical:
merge from a HEAD confirmed idle for some minimum quiet window, immediately after the gate clears,
with the tag-then-merge-then-regression-then-push sequence in §5 run as one uninterrupted block** —
but that is a scheduling discipline this document cannot itself enforce, only recommend.
