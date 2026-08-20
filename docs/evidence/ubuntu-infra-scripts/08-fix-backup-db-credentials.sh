#!/bin/bash
# Repair /root/.my.cnf.openemr-backup after a DB password rotation
#
# Prepared by Claude Code, 2026-08-20, after the 07 deploy run surfaced this.
#
# WHAT BROKE, AND WHEN
#
# RDY-0048 (2026-08-19, script 04) rotated the OpenEMR app DB password off
# the upstream public default. It updated sites/default/sqlconf.php, which is
# what the application reads -- the app has been fine throughout. It did NOT
# update /root/.my.cnf.openemr-backup, which is the credentials file that
# BOTH other pieces of infrastructure read:
#
#   * 02-rdy0081-offsite-backup.sh -> mysqldump --defaults-extra-file=...
#   * 03-rdy0084-monitoring-checks.sh -> M-4's mariadb ... -e "SELECT 1"
#
# Observed 2026-08-20 10:00 on demo-openemr:
#   * the backup's mysqldump fails with
#     'Access denied for user openemr@localhost (using password: YES)'
#   * M-4 database reports "FAILED 619 consecutive checks" -- 619 minutes
#     back from 10:00 is ~23:41 on 2026-08-19, matching the rotation
#   * M-5 backup still reported OK only because its window is 24h and the
#     last good backup was 2026-08-19T20:21, before the rotation. The 03:00
#     backup on 2026-08-20 failed silently. M-5 would have started paging
#     at ~20:21 on 2026-08-20.
#
# Nothing paged, because RDY-0084 still has no paging destination wired up --
# send_alert() only writes to /var/log/openemr-monitoring.log. That is the
# open item this incident is evidence for.
#
# This script re-derives the credentials from sqlconf.php (the single source
# of truth, and what the app itself uses) and rewrites the cnf file. The
# password is never printed, never passed on a command line, and never
# leaves the VM.
#
# Usage:
#   chmod +x 08-fix-backup-db-credentials.sh
#   sudo ./08-fix-backup-db-credentials.sh check    # read-only diagnosis
#   sudo ./08-fix-backup-db-credentials.sh fix      # rewrite + verify
#
# 'fix' also clears M-4's fail counter, re-runs the monitoring checks, and
# runs a real backup so M-5's success marker is refreshed and the whole path
# is proven end to end rather than assumed.

set -euo pipefail

REPO=/var/www/openemr
SQLCONF="$REPO/sites/default/sqlconf.php"
DB_CNF=/root/.my.cnf.openemr-backup
BACKUP_SCRIPT=/usr/local/bin/openemr-offsite-backup.sh
MONITOR_SCRIPT=/usr/local/bin/openemr-monitoring-checks.sh
MONITOR_STATE=/var/lib/openemr-monitoring

need_root() {
  if [ "$EUID" -ne 0 ]; then echo "Run with sudo." >&2; exit 1; fi
}

# Emit shell-quoted assignments read out of sqlconf.php. Using php itself
# means no regex guessing about quoting, and escapeshellarg keeps any
# special characters in a generated password intact.
read_sqlconf() {
  php -r '
    $host = $port = $login = $pass = $dbase = null;
    include $argv[1];
    printf("SQL_HOST=%s\nSQL_PORT=%s\nSQL_USER=%s\nSQL_PASS=%s\nSQL_DB=%s\n",
      escapeshellarg((string)$host), escapeshellarg((string)$port),
      escapeshellarg((string)$login), escapeshellarg((string)$pass),
      escapeshellarg((string)$dbase));
  ' "$SQLCONF"
}

# Does the cnf file currently authenticate?
cnf_works() {
  [ -f "$DB_CNF" ] || return 1
  mariadb --defaults-extra-file="$DB_CNF" -e "SELECT 1" openemr >/dev/null 2>&1
}

check_mode() {
  need_root
  echo "== sqlconf.php =="
  stat -c '  %a %U:%G  mtime %y  %n' "$SQLCONF"
  echo "== $DB_CNF =="
  if [ -f "$DB_CNF" ]; then
    stat -c '  %a %U:%G  mtime %y  %n' "$DB_CNF"
  else
    echo "  MISSING"
  fi
  echo "== does the cnf authenticate? =="
  if cnf_works; then
    echo "  YES -- nothing to fix"
  else
    echo "  NO -- this is the fault. Run 'fix'."
  fi
  echo "== do sqlconf's own credentials authenticate? =="
  local vals
  vals=$(read_sqlconf)
  eval "$vals"
  if MYSQL_PWD="$SQL_PASS" mariadb -u "$SQL_USER" -h "${SQL_HOST:-localhost}" \
        -e "SELECT 1" "$SQL_DB" >/dev/null 2>&1; then
    echo "  YES -- sqlconf.php is correct, only the cnf copy is stale"
  else
    echo "  NO -- sqlconf.php itself does not authenticate. Do NOT run 'fix';"
    echo "  something larger is wrong (wrong DB, wrong host, or the app is"
    echo "  broken too -- but the app is serving 200, so check host/socket)."
  fi
  echo "== M-4 fail counter =="
  cat "$MONITOR_STATE/m4-fail-count" 2>/dev/null || echo "  (none)"
  echo "== M-5 last backup success marker =="
  stat -c '  %y %n' "$MONITOR_STATE/last-backup-success" 2>/dev/null \
    || echo "  (none)"
}

fix_mode() {
  need_root

  local vals
  vals=$(read_sqlconf)
  eval "$vals"

  # Refuse to write a cnf we already know is wrong.
  if ! MYSQL_PWD="$SQL_PASS" mariadb -u "$SQL_USER" -h "${SQL_HOST:-localhost}" \
        -e "SELECT 1" "$SQL_DB" >/dev/null 2>&1; then
    echo "!! The credentials in $SQLCONF do not authenticate either." >&2
    echo "   Refusing to copy known-bad values into $DB_CNF." >&2
    echo "   Run 'check' and investigate before retrying." >&2
    exit 1
  fi

  if [ -f "$DB_CNF" ]; then
    cp -a "$DB_CNF" "$DB_CNF.bak-$(date -u +%Y%m%dT%H%M%SZ)"
    echo "Backed up the old cnf alongside it (600, root-only)."
  fi

  # umask first so the file is never briefly world-readable.
  ( umask 077
    printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' \
      "$SQL_USER" "$SQL_PASS" "${SQL_HOST:-localhost}" > "$DB_CNF" )
  chmod 600 "$DB_CNF"
  chown root:root "$DB_CNF"
  echo "Rewrote $DB_CNF from $SQLCONF (600 root:root)."

  echo "== verifying =="
  if cnf_works; then
    echo "  SELECT 1 via the cnf: OK"
  else
    echo "!! Still failing. Check host vs socket in $DB_CNF." >&2
    exit 1
  fi

  echo "== verifying mysqldump specifically (what the backup uses) =="
  if mysqldump --defaults-extra-file="$DB_CNF" --no-data openemr \
        >/dev/null 2>&1; then
    echo "  mysqldump: OK"
  else
    echo "!! mysqldump still fails even though SELECT 1 works." >&2
    exit 1
  fi

  echo "== clearing M-4's fail counter =="
  rm -f "$MONITOR_STATE/m4-fail-count"

  if [ -x "$MONITOR_SCRIPT" ]; then
    echo "== re-running all six monitoring signals =="
    "$MONITOR_SCRIPT" run || true
    tail -6 /var/log/openemr-monitoring.log
  fi

  if [ "${SKIP_BACKUP:-0}" = "1" ]; then
    echo "== skipping the proving backup run (SKIP_BACKUP=1) =="
  elif [ -x "$BACKUP_SCRIPT" ]; then
    echo "== running a real backup to prove the path end to end =="
    "$BACKUP_SCRIPT" run
    echo "== M-5 marker now =="
    stat -c '  %y %n' "$MONITOR_STATE/last-backup-success" 2>/dev/null || true
  fi

  echo
  echo "Done. Re-run 07-deploy-code-update.sh now -- its step 1/7 backup"
  echo "should succeed and the deploy will continue."
}

case "${1:-}" in
  check) check_mode ;;
  fix)   fix_mode ;;
  *) echo "Usage: sudo $0 {check|fix}" >&2; exit 1 ;;
esac
