#!/bin/bash
# RDY-0048 -- rotate the OpenEMR application DB password away from the
# upstream default ("openemr"/"openemr"), for demo-openemr.
#
# Prepared by Claude Code, 2026-08-19. NOT executed automatically -- rotating
# a live credential is the same category of change as the systemd-unit
# scripts (01/02/03 in this directory): Claude Code does not run it directly,
# even with explicit authorization. Run it yourself as a user with sudo.
#
# What it does, in order:
#   1. Reads the CURRENT db user/pass/name straight out of the live
#      sites/default/sqlconf.php (never printed to the terminal).
#   2. Generates a new random 32-char password (never printed either --
#      written straight into files with 600 perms).
#   3. Backs up sqlconf.php with a timestamp before touching it.
#   4. Runs ALTER USER in MariaDB for every host grant that username
#      actually has (covers 127.0.0.1/localhost/% without guessing).
#   5. Rewrites sqlconf.php in place with the new password.
#   6. Restarts apache2 so mod_php picks up the new value.
#   7. Verifies: DB login with the new password succeeds, the app's own
#      login page still returns 200, and (best-effort) the OLD password no
#      longer authenticates.
#
# Usage on the VM:
#   chmod +x 04-rdy0048-rotate-db-password.sh
#   sudo ./04-rdy0048-rotate-db-password.sh
#
# Safe to re-run: it always re-reads whatever is currently in sqlconf.php,
# so a second run just rotates again from the (now-rotated) value.

set -euo pipefail

OPENEMR_DIR=/var/www/openemr
SQLCONF="$OPENEMR_DIR/sites/default/sqlconf.php"
DB_HOST_DEFAULT=127.0.0.1

if [ "$EUID" -ne 0 ]; then echo "Run with sudo." >&2; exit 1; fi
if [ ! -f "$SQLCONF" ]; then echo "Not found: $SQLCONF" >&2; exit 1; fi

# --- 1. Read current values out of sqlconf.php without ever echoing them ---
CUR_USER=$(php -r "require '$SQLCONF'; echo \$login;")
CUR_DB=$(php -r "require '$SQLCONF'; echo \$dbase;")
CUR_HOST=$(php -r "require '$SQLCONF'; echo \$host ?: '$DB_HOST_DEFAULT';")

if [ -z "$CUR_USER" ] || [ -z "$CUR_DB" ]; then
  echo "Could not parse \$login / \$dbase out of $SQLCONF -- aborting, nothing changed." >&2
  exit 1
fi

echo "Rotating password for DB user '$CUR_USER' on database '$CUR_DB' ($CUR_HOST)."

# --- 2. Generate a new password, never printed ---
NEW_PASS=$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 32)
if [ -z "$NEW_PASS" ]; then echo "Password generation failed." >&2; exit 1; fi

# --- 3. Backup sqlconf.php ---
STAMP=$(date +%Y%m%d-%H%M%S)
BACKUP_DIR=/root/rdy0048-backups
mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"
cp -p "$SQLCONF" "$BACKUP_DIR/sqlconf.php.pre-rotate-$STAMP"
chmod 600 "$BACKUP_DIR/sqlconf.php.pre-rotate-$STAMP"
echo "Backed up current sqlconf.php to $BACKUP_DIR/sqlconf.php.pre-rotate-$STAMP"

# --- 4. ALTER USER for every host grant this user actually has ---
# Uses the MariaDB root socket auth already relied on by scripts 01-03 in
# this directory (mariadb -u root, no password, per this host's setup).
HOSTS=$(mariadb -u root -N -e "SELECT Host FROM mysql.user WHERE User='$CUR_USER';")
if [ -z "$HOSTS" ]; then
  echo "No mysql.user rows found for '$CUR_USER' -- aborting, nothing changed." >&2
  exit 1
fi
while IFS= read -r H; do
  [ -z "$H" ] && continue
  mariadb -u root -e "ALTER USER '$CUR_USER'@'$H' IDENTIFIED BY '$NEW_PASS'; FLUSH PRIVILEGES;"
  echo "Rotated '$CUR_USER'@'$H' in MariaDB."
done <<< "$HOSTS"

# --- 5. Rewrite sqlconf.php in place ---
php -r "
\$path = '$SQLCONF';
\$content = file_get_contents(\$path);
\$new = preg_replace(
    '/\\\$pass\\s*=\\s*[\'\"].*?[\'\"]\\s*;/',
    '\\\$pass  = \'$NEW_PASS\';',
    \$content,
    1,
    \$count
);
if (\$count !== 1) {
    fwrite(STDERR, \"PATTERN NOT FOUND -- aborting, file unchanged\n\");
    exit(1);
}
file_put_contents(\$path, \$new);
"
chmod 640 "$SQLCONF"
chown www-data:www-data "$SQLCONF" 2>/dev/null || true
echo "Rewrote $SQLCONF with the new password."

# --- 6. Restart apache2 ---
systemctl restart apache2
sleep 2

# --- 7. Verify ---
echo
echo "--- Verification ---"

# 7a. New password authenticates directly against MariaDB
if mariadb -u "$CUR_USER" -p"$NEW_PASS" -h "$CUR_HOST" "$CUR_DB" -e "SELECT 1;" >/dev/null 2>&1; then
  echo "OK: new password authenticates against MariaDB as '$CUR_USER'."
else
  echo "FAIL: new password does NOT authenticate. Restore from backup:"
  echo "  cp $BACKUP_DIR/sqlconf.php.pre-rotate-$STAMP $SQLCONF && systemctl restart apache2"
  exit 1
fi

# 7b. Old default password (if it really was "openemr") no longer works
if mariadb -u "$CUR_USER" -p'openemr' -h "$CUR_HOST" "$CUR_DB" -e "SELECT 1;" >/dev/null 2>&1; then
  echo "WARNING: the old value still authenticates too -- check for a second grant/host."
else
  echo "OK: the old password no longer authenticates."
fi

# 7c. App still comes up over HTTPS
HTTP_CODE=$(curl -s -o /dev/null -w '%{http_code}' -H 'Host: demo.skyeagle.uk' \
  "https://127.0.0.1/interface/login/login.php?site=default" -k || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
  echo "OK: login page returns HTTP 200 after restart."
else
  echo "WARNING: login page returned HTTP $HTTP_CODE -- check apache2 error log:"
  echo "  tail -50 /var/log/apache2/demo-skyeagle-error.log"
fi

echo
echo "Done. The new password is in $SQLCONF (mode 640, www-data-owned) and nowhere else on"
echo "disk except the timestamped backup above (mode 600, root-only). It was never printed"
echo "to this terminal."
