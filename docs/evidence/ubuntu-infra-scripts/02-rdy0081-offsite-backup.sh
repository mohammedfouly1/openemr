#!/bin/bash
# RDY-0081 — off-instance backup to Cloudflare R2, for demo-openemr
#
# Prepared by Claude Code, 2026-08-19. NOT executed automatically -- same
# reasoning as the RDY-0083 script (system-configuration change). Run
# yourself as a user with sudo.
#
# PREREQUISITE (DONE, 2026-08-19): R2 is enabled and the bucket
# "skyeaglebucket" exists on this Cloudflare account, confirmed by the
# Owner. R2 API credentials are the same ones already saved locally at
# C:\openemr-stack\secrets\cloudflare-api-token.json (r2_s3_compatible
# block) -- rotate/reissue if it's been long enough that you no longer
# trust it (it was pasted into a chat session, see that file's own note).
#
# Usage on the VM (after the prerequisite above):
#   chmod +x 02-rdy0081-offsite-backup.sh
#   sudo ./02-rdy0081-offsite-backup.sh install     # one-time setup
#   sudo ./02-rdy0081-offsite-backup.sh run         # manual test run
#
# "install" writes /etc/openemr-backup/rclone.conf (mode 600, root-only),
# the backup script itself to /usr/local/bin/, and a systemd service+timer
# that runs it daily at 03:00 server time. "run" just runs the backup once,
# for testing before relying on the timer.

set -euo pipefail

BACKUP_DIR=/etc/openemr-backup
BACKUP_SCRIPT=/usr/local/bin/openemr-offsite-backup.sh
DB_CNF=/root/.my.cnf.openemr-backup
RETENTION_DAYS=14

install_mode() {
  if [ "$EUID" -ne 0 ]; then echo "Run with sudo." >&2; exit 1; fi

  mkdir -p "$BACKUP_DIR"
  chmod 700 "$BACKUP_DIR"

  # --- DB credentials, no password on any command line ---
  # Fill in the real values from sites/default/sqlconf.php before running.
  cat > "$DB_CNF" << 'CNFEOF'
[client]
user=openemr
password=REPLACE_ME_FROM_sqlconf.php
host=127.0.0.1
CNFEOF
  chmod 600 "$DB_CNF"
  echo "!! Edit $DB_CNF and replace REPLACE_ME_FROM_sqlconf.php with the real DB password"
  echo "   (read it from /var/www/openemr/sites/default/sqlconf.php, do not paste it anywhere else)"

  # --- rclone config for R2, S3-compatible ---
  # Fill in access_key_id / secret_access_key / account_id from
  # C:\openemr-stack\secrets\cloudflare-api-token.json's r2_s3_compatible block.
  if ! command -v rclone >/dev/null; then
    echo "Installing rclone..."
    curl https://rclone.org/install.sh | bash
  fi
  cat > "$BACKUP_DIR/rclone.conf" << 'RCLONEEOF'
[r2]
type = s3
provider = Cloudflare
access_key_id = REPLACE_ME
secret_access_key = REPLACE_ME
endpoint = REPLACE_ME.r2.cloudflarestorage.com
acl = private
RCLONEEOF
  chmod 600 "$BACKUP_DIR/rclone.conf"
  echo "!! Edit $BACKUP_DIR/rclone.conf with the real R2 S3 credentials"

  cp "$0" "$BACKUP_SCRIPT"
  chmod 700 "$BACKUP_SCRIPT"

  cat > /etc/systemd/system/openemr-offsite-backup.service << UNITEOF
[Unit]
Description=OpenEMR off-instance backup to R2
After=network.target mariadb.service

[Service]
Type=oneshot
ExecStart=$BACKUP_SCRIPT run
UNITEOF

  cat > /etc/systemd/system/openemr-offsite-backup.timer << 'TIMEREOF'
[Unit]
Description=Daily OpenEMR off-instance backup

[Timer]
OnCalendar=*-*-* 03:00:00
Persistent=true
AccuracySec=5min

[Install]
WantedBy=timers.target
TIMEREOF

  systemctl daemon-reload
  systemctl enable openemr-offsite-backup.timer
  echo ""
  echo "Installed. Edit the two REPLACE_ME files above, then run:"
  echo "  sudo $BACKUP_SCRIPT run     # test it once"
  echo "  sudo systemctl start openemr-offsite-backup.timer"
}

run_mode() {
  STAMP=$(date +%Y%m%d-%H%M%S)
  WORKDIR=$(mktemp -d)
  trap 'rm -rf "$WORKDIR"' EXIT

  echo "[$STAMP] Dumping database..."
  mysqldump --defaults-extra-file="$DB_CNF" openemr | gzip > "$WORKDIR/openemr-db-$STAMP.sql.gz"

  echo "[$STAMP] Archiving documents..."
  tar czf "$WORKDIR/openemr-documents-$STAMP.tar.gz" -C /var/www/openemr/sites default/documents 2>/dev/null || true

  echo "[$STAMP] Recording deployed commit/tag..."
  git -c safe.directory=/var/www/openemr -C /var/www/openemr describe --tags --always > "$WORKDIR/deployed-ref-$STAMP.txt" 2>&1 || true

  echo "[$STAMP] Checksums..."
  sha256sum "$WORKDIR"/* > "$WORKDIR/checksums-$STAMP.txt"

  TABLE_COUNT=$(zcat "$WORKDIR/openemr-db-$STAMP.sql.gz" | grep -c "^CREATE TABLE" || true)
  echo "[$STAMP] $TABLE_COUNT tables in dump (compare against a known-good baseline before trusting this backup)."

  echo "[$STAMP] Uploading to R2..."
  rclone --config "$BACKUP_DIR/rclone.conf" copy "$WORKDIR" "r2:skyeaglebucket/$STAMP/" --progress

  echo "[$STAMP] Verifying upload..."
  rclone --config "$BACKUP_DIR/rclone.conf" ls "r2:skyeaglebucket/$STAMP/"

  echo "[$STAMP] Pruning backups older than $RETENTION_DAYS days..."
  CUTOFF=$(date -d "-$RETENTION_DAYS days" +%Y%m%d)
  rclone --config "$BACKUP_DIR/rclone.conf" lsf "r2:skyeaglebucket/" | while read -r dir; do
    d="${dir%%-*}"
    if [[ "$d" =~ ^[0-9]{8}$ ]] && [ "$d" -lt "$CUTOFF" ]; then
      echo "  pruning $dir"
      rclone --config "$BACKUP_DIR/rclone.conf" purge "r2:skyeaglebucket/$dir"
    fi
  done

  mkdir -p /var/lib/openemr-monitoring
  touch /var/lib/openemr-monitoring/last-backup-success

  echo "[$STAMP] Backup complete."
}

case "${1:-}" in
  install) install_mode ;;
  run) run_mode ;;
  *) echo "Usage: $0 {install|run}"; exit 1 ;;
esac
