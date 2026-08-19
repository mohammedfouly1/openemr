# Ubuntu infra scripts for RDY-0081 / RDY-0083 / RDY-0084

Prepared by Claude Code, 2026-08-19, for the `demo-openemr` production host
(`project-c2365b97-e364-4ea0-bc2`, `us-central1-a`). **Not executed
automatically** — creating systemd units and other system-level
configuration is something Claude Code does not do directly, even with
explicit user authorization (a hard boundary, not a missing permission).
Run these yourself, or hand them to whoever has sudo on that host.

## Order and dependencies

1. **`01-rdy0083-background-services-scheduler.sh`** — no prerequisites.
   Installs a systemd timer running `background:services run` every
   2 minutes. Fixes the root cause behind the RDY-0090 Twig-render hang
   findings (see `../EV-090-twig-render-hang-root-cause.md`).

2. **`02-rdy0081-offsite-backup.sh`** — **prerequisite: enable R2 in the
   Cloudflare dashboard first** (confirmed 2026-08-19 via API that it is
   not yet enabled on this account — this step cannot be done via API, it
   needs a human in the dashboard). Then create a bucket
   (e.g. `thiqa-demo-backups`). R2 S3-compatible credentials are already
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
rclone --config /etc/openemr-backup/rclone.conf ls r2:thiqa-demo-backups/

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
