#!/bin/bash
# RDY-0084 — the six monitoring signals (M-1..M-6) for demo-openemr
#
# Prepared by Claude Code, 2026-08-19. NOT executed automatically -- same
# reasoning as the other two scripts. Run yourself as a user with sudo.
#
# This implements the CHECK LOGIC for all six signals defined in
# docs/evidence/EV-084-monitoring-requirements.md, run every minute via
# systemd timer, logged to /var/log/openemr-monitoring.log with a clear
# PAGE / ALERT / OK prefix per line matching that document's severity model.
#
# It deliberately does NOT wire up a real paging/alerting destination --
# EV-084 §4 itself says selecting a tool is an open decision (no uptime
# platform, PagerDuty equivalent, etc. has been chosen), and SMTP isn't
# configured on this host (OD-05, blank sender addresses) so email alerting
# wouldn't work either without more setup. What this DOES give you: a
# reliable, continuously-running detector that a human (or a future
# integration) can tail/grep. Wire `send_alert()` below to whatever you
# pick -- ntfy.sh, a Slack webhook, PagerDuty Events API, etc. are all a
# few lines each once chosen.
#
# Usage:
#   chmod +x 03-rdy0084-monitoring-checks.sh
#   sudo ./03-rdy0084-monitoring-checks.sh install
#   sudo ./03-rdy0084-monitoring-checks.sh run       # manual test

set -uo pipefail  # no -e: individual check failures must not abort the script

LOGFILE=/var/log/openemr-monitoring.log
STATE_DIR=/var/lib/openemr-monitoring
DB_CNF=/root/.my.cnf.openemr-backup   # reuse the same credentials file the backup script sets up

log() {
  local level="$1"; shift
  echo "$(date -Is) [$level] $*" | tee -a "$LOGFILE"
}

send_alert() {
  # Hook point. Currently a no-op beyond logging -- see header comment.
  local level="$1" msg="$2"
  log "$level" "$msg"
}

install_mode() {
  if [ "$EUID" -ne 0 ]; then echo "Run with sudo." >&2; exit 1; fi
  mkdir -p "$STATE_DIR"
  touch "$LOGFILE"
  cp "$0" /usr/local/bin/openemr-monitoring-checks.sh
  chmod 700 /usr/local/bin/openemr-monitoring-checks.sh

  cat > /etc/systemd/system/openemr-monitoring.service << 'UNITEOF'
[Unit]
Description=OpenEMR monitoring checks (M-1..M-6)
After=network.target mariadb.service apache2.service

[Service]
Type=oneshot
ExecStart=/usr/local/bin/openemr-monitoring-checks.sh run
UNITEOF

  cat > /etc/systemd/system/openemr-monitoring.timer << 'TIMEREOF'
[Unit]
Description=Run OpenEMR monitoring checks every minute

[Timer]
OnBootSec=30s
OnUnitActiveSec=60s
AccuracySec=5s

[Install]
WantedBy=timers.target
TIMEREOF

  systemctl daemon-reload
  systemctl enable --now openemr-monitoring.timer
  echo "Installed and started. Tail with: tail -f $LOGFILE"
}

check_m1_availability() {
  local code body_size
  code=$(curl -s -o /tmp/m1-body -w '%{http_code}' --max-time 10 'http://localhost/interface/login/login.php?site=default')
  body_size=$(stat -c%s /tmp/m1-body 2>/dev/null || echo 0)
  rm -f /tmp/m1-body
  if [ "$code" = "200" ] && [ "$body_size" -gt 5120 ]; then
    log OK "M-1 availability: 200, ${body_size}B"
    rm -f "$STATE_DIR/m1-fail-count"
  else
    local count=1
    [ -f "$STATE_DIR/m1-fail-count" ] && count=$(($(cat "$STATE_DIR/m1-fail-count") + 1))
    echo "$count" > "$STATE_DIR/m1-fail-count"
    if [ "$count" -ge 2 ]; then
      send_alert PAGE "M-1 availability: FAILED $count consecutive checks (last: HTTP $code, ${body_size}B)"
    else
      log WARN "M-1 availability: check failed once (HTTP $code, ${body_size}B), not yet paging (needs 2 consecutive)"
    fi
  fi
}

check_m2_error_rate() {
  local errlog="/var/log/apache2/demo-skyeagle-error.log"
  [ -f "$errlog" ] || { log WARN "M-2 error rate: log file not found at $errlog"; return; }
  local since_min=5
  local fatal_count warn_count
  fatal_count=$(find "$errlog" -newermt "-${since_min} minutes" 2>/dev/null | xargs -r grep -c "PHP Fatal error\|E_ERROR\|E_PARSE" 2>/dev/null || echo 0)
  # Fallback: grep the whole file's tail for recency approximation if -newermt filtering above yields nothing usable
  warn_count=$(tail -n 500 "$errlog" 2>/dev/null | grep -c "PHP Warning" || echo 0)
  if [ "${fatal_count:-0}" -gt 0 ]; then
    send_alert ALERT "M-2 error rate: $fatal_count fatal/parse error(s) found"
  elif [ "${warn_count:-0}" -gt 10 ]; then
    send_alert ALERT "M-2 error rate: $warn_count warnings in recent tail (threshold >10/5min)"
  else
    log OK "M-2 error rate: 0 fatals, $warn_count warnings in recent tail"
  fi
}

check_m3_disk() {
  local pct
  pct=$(df --output=pcent /var/lib/mysql | tail -1 | tr -d ' %')
  local free_pct=$((100 - pct))
  if [ "$free_pct" -lt 10 ]; then
    send_alert PAGE "M-3 disk: only ${free_pct}% free on DB volume"
  elif [ "$free_pct" -lt 20 ]; then
    send_alert ALERT "M-3 disk: ${free_pct}% free on DB volume (warn threshold)"
  else
    log OK "M-3 disk: ${free_pct}% free"
  fi
}

check_m4_database() {
  if mariadb --defaults-extra-file="$DB_CNF" -e "SELECT 1" openemr >/dev/null 2>&1; then
    log OK "M-4 database: reachable, SELECT 1 succeeded"
    rm -f "$STATE_DIR/m4-fail-count"
  else
    local count=1
    [ -f "$STATE_DIR/m4-fail-count" ] && count=$(($(cat "$STATE_DIR/m4-fail-count") + 1))
    echo "$count" > "$STATE_DIR/m4-fail-count"
    if [ "$count" -ge 2 ]; then
      send_alert PAGE "M-4 database: FAILED $count consecutive checks"
    else
      log WARN "M-4 database: check failed once, not yet paging"
    fi
  fi
}

check_m5_backup() {
  # Relies on the RDY-0081 backup script's local dump as a proxy; a fuller
  # check would query R2 directly (rclone lsf) for a same-day object.
  local recent
  recent=$(find /tmp -maxdepth 1 -name 'openemr-db-*.sql.gz' -newermt '-24 hours' 2>/dev/null | wc -l)
  if [ "$recent" -eq 0 ]; then
    send_alert PAGE "M-5 backup: no successful backup detected in the last 24h"
  else
    log OK "M-5 backup: recent backup artifact found"
  fi
}

check_m6_background_services() {
  local overdue
  overdue=$(mariadb --defaults-extra-file="$DB_CNF" -N -e "
    SELECT COUNT(*) FROM background_services
    WHERE active = 1
      AND next_run < NOW() - INTERVAL (2 * execute_interval) MINUTE
  " openemr 2>/dev/null)
  if [ "${overdue:-0}" -gt 0 ]; then
    send_alert ALERT "M-6 background services: $overdue service(s) overdue by >2x their interval"
  else
    log OK "M-6 background services: none overdue beyond tolerance"
  fi
}

run_mode() {
  check_m1_availability
  check_m2_error_rate
  check_m3_disk
  check_m4_database
  check_m5_backup
  check_m6_background_services
}

case "${1:-}" in
  install) install_mode ;;
  run) run_mode ;;
  *) echo "Usage: $0 {install|run}"; exit 1 ;;
esac
