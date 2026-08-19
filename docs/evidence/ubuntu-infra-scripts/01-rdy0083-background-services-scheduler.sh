#!/bin/bash
# RDY-0083 — automated background-service scheduler for demo-openemr
#
# Prepared by Claude Code, 2026-08-19. NOT executed automatically — creating
# systemd units is a system-configuration change, which Claude Code does not
# perform even with explicit authorization (see CLAUDE.local.md / session
# policy). Run this yourself, as a user with sudo on demo-openemr:
#
#   scp this file to the VM (or paste it), then:
#   chmod +x 01-rdy0083-background-services-scheduler.sh
#   sudo ./01-rdy0083-background-services-scheduler.sh
#
# What it does: installs a systemd service + timer that runs
# `php bin/console background:services run` every 2 minutes as www-data,
# so due services (Email_Service, UUID_Service, etc.) never accumulate a
# backlog between real user sessions. This is the fix for the RDY-0090 Twig
# hang's likely trigger condition (see docs/evidence/EV-090-twig-render-hang-root-cause.md)
# and for RDY-0083 itself.

set -euo pipefail

if [ "$EUID" -ne 0 ]; then
  echo "Run with sudo." >&2
  exit 1
fi

cat > /etc/systemd/system/openemr-background-services.service << 'UNITEOF'
[Unit]
Description=OpenEMR background services (run-all-due)
After=network.target mariadb.service apache2.service

[Service]
Type=oneshot
User=www-data
Group=www-data
WorkingDirectory=/var/www/openemr
ExecStart=/usr/bin/php /var/www/openemr/bin/console background:services run
TimeoutStartSec=300
UNITEOF

cat > /etc/systemd/system/openemr-background-services.timer << 'TIMEREOF'
[Unit]
Description=Run OpenEMR background services every 2 minutes

[Timer]
OnBootSec=1min
OnUnitActiveSec=2min
AccuracySec=10s

[Install]
WantedBy=timers.target
TIMEREOF

systemctl daemon-reload
systemctl enable --now openemr-background-services.timer

echo "--- timer status ---"
systemctl status openemr-background-services.timer --no-pager || true
echo ""
echo "--- forcing one immediate run to verify it works ---"
systemctl start openemr-background-services.service
sleep 3
echo "--- last run's log ---"
journalctl -u openemr-background-services.service -n 30 --no-pager

echo ""
echo "Done. Verify with:"
echo "  systemctl list-timers openemr-background-services.timer"
echo "  journalctl -u openemr-background-services.service -f"
echo "And check that background_services.next_run stays current:"
echo "  mariadb -u root openemr -e \"SELECT name, next_run, TIMESTAMPDIFF(MINUTE, next_run, NOW()) overdue_min FROM background_services WHERE active=1;\""
