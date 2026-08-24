# Ubuntu infra scripts for RDY-0081 / RDY-0083 / RDY-0084

Prepared by Claude Code, 2026-08-19, for the `demo-openemr` production host
(`project-c2365b97-e364-4ea0-bc2`, `us-central1-a`). **Not executed
automatically** — creating systemd units and other system-level
configuration is something Claude Code does not do directly, even with
explicit user authorization (a hard boundary, not a missing permission).
Run these yourself, or hand them to whoever has sudo on that host.

## Operational identity classification (S1-P1-11, 2026-08-24)

This directory mixes reusable scripts with captured deployment evidence. Identity-bearing strings are
therefore classified before any future rename:

| Occurrence | Classification | Disposition |
|---|---|---|
| Date-rebase source comment and generated systemd descriptions | Safe-to-neutralize operator labels | Source now says **OpenEMR demo**. This repository edit does not alter an already-installed remote unit; reconcile that unit only during a separately authorized infrastructure migration. |
| `feat/thiqa-branding-foundation` in `07-deploy-code-update.sh` and this README | Branch name in a historical, SHA-pinned deployment transaction | Preserve exactly. The script records the branch/commit that was actually deployed; it is not a reusable product-name setting. Generate a new reviewed update script for a future branch. |
| `oe-module-thiqa-branding` in the captured change inventory | Production compatibility identifier | Preserve exactly until an explicitly authorized module migration changes the real directory and all consumers together. |
| Former `thiqa-demo-backups` README example | Stale hypothetical resource name | Removed. Verification now requires the operator to supply the existing bucket name rather than minting or assuming an identity-bearing resource. |
| `skyeaglebucket` in script 02 | Pre-existing external bucket identifier | Preserve; do not rename, copy, or migrate it in PRE-SKYEAGLE work. It is infrastructure state, not authorization to begin product rebranding. |
| Product names in handoff/readiness history outside this directory | Historical evidence | Preserve. Historical records are not reusable configuration. |

The classification deliberately does not rename a live service, branch, module, bucket, backup object, or
historical record.

## Order and dependencies

1. **`01-rdy0083-background-services-scheduler.sh`** — no prerequisites.
   Installs a systemd timer running `background:services run` every
   2 minutes. Fixes the root cause behind the RDY-0090 Twig-render hang
   findings (see `../EV-090-twig-render-hang-root-cause.md`).

2. **`02-rdy0081-offsite-backup.sh`** — R2 and its current external bucket
   were subsequently confirmed present by the Owner (the script records the
   exact existing identifier). Do not create or rename a bucket merely to run
   this evidence procedure. R2 S3-compatible credentials are already
   saved at `C:\openemr-stack\secrets\cloudflare-api-token.json`
   (`r2_s3_compatible` block) — reissue if you no longer trust that value
   (it was pasted into a chat session).

   `install` mode writes two files you must edit before it works:
   - `/root/.my.cnf.openemr-backup` — the real DB password (read it from
     `sites/default/sqlconf.php` on the VM itself, don't copy it elsewhere)
   - `/etc/openemr-backup/rclone.conf` — the real R2 access key / secret /
     account-id endpoint

3. **`03-rdy0084-monitoring-checks.sh`** — depends on script 2's DB
   credentials file existing (reuses `/root/.my.cnf.openemr-backup`) and,
   loosely, on script 2 having run at least once (M-5's check looks for a
   recent backup artifact). Implements all six signals from
   `../EV-084-monitoring-requirements.md` (M-1 availability, M-2 error
   rate, M-3 disk, M-4 database, M-5 backup success, M-6 background-service
   health), logging to `/var/log/openemr-monitoring.log` with PAGE/ALERT/OK
   severity matching that document. **Does not page anyone** — no
   alerting tool has been chosen yet (EV-084's own open reservation); the
   `send_alert()` function is the hook point for whichever tool gets
   picked (ntfy.sh, Slack webhook, PagerDuty, etc. are all a few lines).

## Verifying after install

```bash
# 0083
systemctl list-timers openemr-background-services.timer
journalctl -u openemr-background-services.service -n 20
mariadb -u root openemr -e "SELECT name, next_run FROM background_services WHERE active=1;"

# 0081
sudo /usr/local/bin/openemr-offsite-backup.sh run   # or wait for the daily timer
R2_BUCKET=REPLACE_WITH_EXISTING_BUCKET
rclone --config /etc/openemr-backup/rclone.conf ls "r2:${R2_BUCKET}/"

# 0084
tail -f /var/log/openemr-monitoring.log
```

## What still isn't closed by these scripts alone

- RDY-0081: the *policy* pieces (retention beyond the 14-day default this
  script uses, encryption-at-rest specifics, access control on the bucket)
  should be reconciled against whatever the Owner already approved in
  `EV-064-081-084-085-hosting-pack.md` §3.2 before calling this closed.
- RDY-0084: no real paging destination is wired up. Selecting one is a
  product decision, not something to default silently.
- Both need at least one real restore test (0081) and one real fired-alert
  test (0084) before either item can honestly move to CLOSED — a script
  existing is not the same as it being proven to work end to end.

## `07-deploy-code-update.sh` — routine code updates (added 2026-08-20)

Not an RDY item; this is the ordinary "the workstation has moved ahead of
the VM, bring the VM up to date" procedure, written down so it is
repeatable instead of improvised each time.

The deployed tree at `/var/www/openemr` is a **depth-1 shallow clone in
detached HEAD**, cloned from `https://github.com/mohammedfouly1/openemr.git`
(`origin`). It has no branch and no history, so `git pull` does not work
there — the update is `git fetch --depth=1 origin <branch>` followed by
`git checkout --detach FETCH_HEAD`, which is what this script does.

Prerequisite: **the branch must already be pushed to GitHub.** The VM pulls
from `origin`, never from the Windows workstation — an `rsync`/`scp` from
Windows would carry CRLF into every shebang (see
`../../demo-deployment-readiness.md` §24). Confirm with
`git ls-remote origin refs/heads/feat/thiqa-branding-foundation` on the
workstation and check it matches local `HEAD`.

```bash
sudo ./07-deploy-code-update.sh preflight   # read-only; changes nothing
sudo ./07-deploy-code-update.sh run
sudo ./07-deploy-code-update.sh verify      # re-runnable any time
sudo ./07-deploy-code-update.sh rollback    # to the pre-update commit
```

What `run` does, in order: off-site backup (reuses script 02 if installed) →
tags the current commit so the shallow clone's old commit survives `gc` →
depth-1 fetch → prints the runtime-file change set plus `sql/`, theme and
`composer.lock` impact → refuses to continue if a locally-modified file is
also modified upstream → checkout → restores ownership/modes (`sites/*` back
to `www-data` 600, everything else to the tree owner, because a root-run
checkout leaves new files root-owned) → optional `composer install` →
`apache2ctl configtest` + `systemctl reload apache2` → verify.

Two files in the deployed tree are locally modified and **must survive every
update**: `sites/default/sqlconf.php` (the rotated RDY-0048 DB password) and
`sites/default/documents/custom_menus/patient_menus/Custom.json`. Step 4b is
the mechanical guard — it aborts rather than clobbering either.

`verify` checks: HEAD sha, the two local modifications still present,
`sqlconf.php`/`config.php` mode and owner, the Q77 forbidden-theme count
(expect 0), an M-1-style availability probe against the origin's HTTPS vhost,
a full monitoring run, and the background-services timer state.

## `08-fix-backup-db-credentials.sh` — incident fix (added 2026-08-20)

**Incident, found 2026-08-20 10:00 by the first `07` run.** RDY-0048's
password rotation (script `04`, 2026-08-19 ~23:41) updated
`sites/default/sqlconf.php` — which is what the application reads, so the
app never broke — but did **not** update `/root/.my.cnf.openemr-backup`.
Both other pieces of infrastructure read that file:

| Reader | Command | Effect |
|---|---|---|
| `02-rdy0081-offsite-backup.sh` | `mysqldump --defaults-extra-file` | daily 03:00 backup failing since the rotation |
| `03-rdy0084-monitoring-checks.sh` | M-4's `mariadb -e "SELECT 1"` | 619 consecutive failed checks by 10:00 |

Evidence: `mysqldump: Got error: 1045 "Access denied for user
'openemr'@'localhost'"`, and `[PAGE] M-4 database: FAILED 619 consecutive
checks` — 619 minutes back from 10:00:23 lands at ~23:41 on 2026-08-19,
matching `sqlconf.php`'s mtime.

Three things this incident is real evidence for, beyond the fix:

1. **RDY-0084's missing paging destination is not a theoretical gap.** M-4
   detected this correctly and immediately, and paged 619 times into a log
   file nobody was reading. The detection worked; the delivery does not exist.
2. **M-5 was still green and would have stayed green until ~20:21 that
   evening**, because its window is 24h and the last good backup predated
   the rotation. A backup that silently stops working is invisible for a
   whole day by design here.
3. **Any future credential rotation must update both files**, or `04`'s
   rotation script should be extended to rewrite the cnf itself. It was not,
   and the RDY-0048 closure note does not mention the dependency.

```bash
sudo ./08-fix-backup-db-credentials.sh check   # read-only diagnosis
sudo ./08-fix-backup-db-credentials.sh fix     # rewrite, verify, prove
```

`fix` re-derives the credentials from `sqlconf.php` (never printing them,
never putting them on a command line), refuses to write if those credentials
do not themselves authenticate, backs up the old cnf, verifies both
`SELECT 1` and `mysqldump`, clears M-4's fail counter, re-runs all six
monitoring signals, and runs a real backup so M-5's marker is refreshed and
the whole path is proven rather than assumed.

### Deployment record — 2026-08-20

First use of `07`. `demo-openemr` moved `6de7cdcc1` → `987a38c44`
(288 commits, 537 files, 21 of them runtime). Verified afterwards, live and
independently of the script's own output: HEAD correct; both local
modifications (`sqlconf.php`, `Custom.json`) preserved; `config.php` back to
600 `www-data`; the deleted `CreateReleaseChangelogCommand.php` gone and the
new `AGENTS.md` present; `library/globals.inc.php` carrying the new
`audit_events_lab-order` entry; 0 Q77 forbidden themes; HTTP 200/7693B; all
six monitoring signals OK; rollback tag `pre-update-20260820T070530Z` in
place. No `composer install` and no front-end rebuild were needed, as
predicted.

One defect found in `07` by that run: `git diff --name-only` lists files, so
`restore_perms()` missed **directories** git created for new paths, leaving
40 of them root-owned (all under `docs/`, `tests/`, `.github/`, `.agents/` —
no runtime path, no functional impact). Fixed by a sweep that chowns anything
left as root outside `.git/` and `sites/`. To repair a tree deployed before
that fix:

```bash
sudo find /var/www/openemr -user root \
  -not -path '/var/www/openemr/.git/*' -not -path '/var/www/openemr/sites/*' \
  -exec chown myriamviens2:myriamviens2 {} +
```
