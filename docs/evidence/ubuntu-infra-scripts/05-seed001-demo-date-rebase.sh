#!/bin/bash
# SEED-001 — whole-week date re-base for the OpenEMR demo seed, on demo-openemr
#
# Prepared by Claude Code, 2026-08-20. NOT executed automatically — same
# reasoning as scripts 01-04 (installs a systemd unit; system-configuration
# change). Run it yourself, as a user with sudo on demo-openemr.
#
# WHY THIS EXISTS
#   The synthetic demo dataset is anchored to the moment the seed ran
#   (2026-08-14 06:43). Every date-filtered screen therefore decays one day
#   per day. Verified on the LOCAL instance 2026-08-20: zero appointments
#   dated today, and form_encounter's newest row already 1 day stale.
#
#   Decision SEED-001 splits this from the database reset. This script does
#   the SAFE half only: it moves seeded dates forward, it never deletes,
#   truncates, drops or restores anything. The destructive reset (EV-044)
#   stays manual and Owner-authorised.
#
# WHAT "WHOLE-WEEK" MEANS, AND WHY
#   The offset is always a multiple of 7 days, so every appointment keeps its
#   weekday. A raw-day shift does not: PB-454's "+5 DAY" moved the 16-visit
#   cluster off a Friday and pushed 4 visits onto a Saturday. In Saudi Arabia
#   the weekend is Friday-Saturday and the clinic week is Sunday-Thursday, so
#   a rotated seed reads as a clinic that books patients on its weekend.
#
# THE HAZARD THIS SCRIPT DOES NOT REPEAT
#   pc_startTime and pc_endTime are TIME columns, not datetimes.
#   DATE_ADD(<TIME>, INTERVAL 5 DAY) returns 129:00:00 — confirmed on a live
#   MariaDB, 2026-08-20. This script NEVER touches them. Adding whole days to
#   a date column already preserves time-of-day; the time columns need no
#   shift at all.
#
# Usage on the VM:
#   chmod +x 05-seed001-demo-date-rebase.sh
#   sudo ./05-seed001-demo-date-rebase.sh check          # read-only, safe, run this first
#   sudo ./05-seed001-demo-date-rebase.sh fix-weekdays   # ONE TIME: Sat/Fri appts -> Thursday
#   sudo ./05-seed001-demo-date-rebase.sh run --dry-run  # show the SQL, change nothing
#   sudo ./05-seed001-demo-date-rebase.sh run --yes      # backup, then re-base
#   sudo ./05-seed001-demo-date-rebase.sh rollback       # restore the last backup
#   sudo ./05-seed001-demo-date-rebase.sh install        # systemd timer (GATED, see below)
#
# THE INSTALL GATE IS DELIBERATE. "install" refuses until BOTH a real "run"
# and a real "rollback" have completed on THIS host. That is Owner decision
# SEED-001's condition: nothing gets scheduled before backup and restore have
# been exercised on demo.skyeagle.uk itself, not on a local instance.

set -euo pipefail

DB_CNF=/root/.my.cnf.openemr-backup      # written by 02-rdy0081-offsite-backup.sh
DB_NAME=openemr
STATE_DIR=/var/lib/openemr-rebase
BACKUP_DIR=/var/backups/openemr-rebase
LOG=/var/log/openemr-rebase.log
REBASE_SCRIPT=/usr/local/bin/openemr-date-rebase.sh
RETENTION=10                              # keep the last N pre-change dumps

need_root() { [ "$EUID" -eq 0 ] || { echo "Run with sudo." >&2; exit 1; }; }

need_db() {
  [ -f "$DB_CNF" ] || {
    echo "Missing $DB_CNF." >&2
    echo "It is created by 02-rdy0081-offsite-backup.sh install, and holds the" >&2
    echo "DB credentials so no password ever appears on a command line." >&2
    exit 1
  }
}

# mysql/mariadb client name differs by release
MYSQL=$(command -v mariadb || command -v mysql)
DUMP=$(command -v mariadb-dump || command -v mysqldump)

q()  { "$MYSQL"  --defaults-file="$DB_CNF" -N -B "$DB_NAME" -e "$1"; }
qt() { "$MYSQL"  --defaults-file="$DB_CNF"       "$DB_NAME" -e "$1"; }

# ---------------------------------------------------------------------------
# The offset. Both operands are the Sunday on or before their date, so the
# difference is a multiple of 7 by construction; the FLOOR(/7)*7 is a belt-and-
# braces guard. Re-running after a successful run yields 0 — it is idempotent.
# ---------------------------------------------------------------------------
OFFSET_SQL="
SELECT COALESCE(7 * FLOOR(DATEDIFF(
         DATE_SUB(CURDATE(), INTERVAL DAYOFWEEK(CURDATE())-1 DAY),
         DATE_SUB(a,         INTERVAL DAYOFWEEK(a)-1 DAY)
       ) / 7), 0)
  FROM (SELECT MIN(pc_eventDate) a
          FROM openemr_postcalendar_events
         WHERE pc_recurrtype = 0 AND pc_eventDate > '1900-01-01') t;"

# ---------------------------------------------------------------------------
# Columns that move. Uniform offset across every table, so relative chronology
# between an appointment, its encounter, its prescription and its bill is
# preserved exactly.
#
# The IF(col > '1900-01-01', ...) guard matters: DATE_ADD(NULL) is NULL and
# DATE_ADD('0000-00-00') is NULL, so an unguarded UPDATE would silently
# destroy zero-dates. This form leaves NULL as NULL and zero as zero.
#
# DELIBERATELY EXCLUDED, do not "fix" these by adding them:
#   pc_time                  record-creation timestamp, not a clinical date.
#                            This is the column PB-441 measured; it is why the
#                            "all 37 appointments on 2026-08-14" finding was
#                            wrong. It must stay where it is.
#   pc_startTime/pc_endTime  TIME columns — see the hazard note in the header.
#   documents.revision,      auto-maintained audit timestamps; not displayed,
#   form_encounter.last_update,   and shifting them would misrepresent when
#   lists.modifydate,             the record was actually written.
#   form_vitals.last_updated
#   patient_data.DOB and every other demographic date — ages must not drift.
# ---------------------------------------------------------------------------
_col() { echo "\`$1\` = IF(\`$1\` > '1900-01-01', DATE_ADD(\`$1\`, INTERVAL $2 DAY), \`$1\`)"; }

shift_sql() {
  local n="$1"
  g() { _col "$1" "$n"; }
  cat <<SQL
UPDATE openemr_postcalendar_events SET $(g pc_eventDate), $(g pc_endDate);
UPDATE form_encounter  SET $(g date), $(g onset_date), $(g last_stmt_date), $(g date_end);
UPDATE prescriptions   SET $(g date_added), $(g date_modified), $(g start_date), $(g filled_date), $(g datetime), $(g end_date), $(g txDate);
UPDATE lists           SET $(g date), $(g begdate), $(g enddate), $(g returndate);
UPDATE form_vitals     SET $(g date);
UPDATE billing         SET $(g date), $(g bill_date), $(g process_date);
UPDATE documents       SET $(g date), $(g date_expires), $(g docdate);
SQL
}

# The one recurring series has a hard pc_endDate. A whole-week shift moves it
# too, but if it was already close to expiry it stays close; give it runway.
EXTEND_RECURRING_SQL="
UPDATE openemr_postcalendar_events
   SET pc_endDate = DATE_ADD(CURDATE(), INTERVAL 180 DAY)
 WHERE pc_recurrtype <> 0
   AND pc_endDate IS NOT NULL
   AND pc_endDate > '1900-01-01'
   AND pc_endDate < DATE_ADD(CURDATE(), INTERVAL 90 DAY);"

log() { echo "$(date -Is) $*" | tee -a "$LOG"; }

# ---------------------------------------------------------------------------
check_mode() {
  need_root; need_db
  local off; off=$(q "$OFFSET_SQL")
  echo "=== SEED-001 check — $(date -Is) ==="
  echo "Today: $(q 'SELECT CURDATE();')   computed whole-week offset: ${off} days"
  echo
  echo "--- Appointments by weekday (pc_eventDate, non-recurring) ---"
  qt "SELECT pc_eventDate, DAYNAME(pc_eventDate) dow, COUNT(*) n
        FROM openemr_postcalendar_events
       WHERE pc_recurrtype = 0 GROUP BY pc_eventDate ORDER BY pc_eventDate;"
  echo "--- Saudi weekend violations (Fri/Sat appointments; want 0) ---"
  qt "SELECT DAYNAME(pc_eventDate) dow, COUNT(*) n
        FROM openemr_postcalendar_events
       WHERE pc_recurrtype = 0 AND DAYOFWEEK(pc_eventDate) IN (6,7)
       GROUP BY dow;"
  echo "--- Is today populated? (the symptom this decision exists to fix) ---"
  qt "SELECT CURDATE() today, DAYNAME(CURDATE()) dow,
             SUM(pc_eventDate = CURDATE()) appointments_today
        FROM openemr_postcalendar_events WHERE pc_recurrtype = 0;"
  echo "--- Section 13.4 hazard check (max_t > 23:59:59 or any NULL = damaged) ---"
  qt "SELECT MIN(pc_startTime) min_t, MAX(pc_startTime) max_t,
             SUM(pc_startTime IS NULL) null_times
        FROM openemr_postcalendar_events WHERE pc_recurrtype = 0;"
  echo "--- Decay surface: newest seeded row per table ---"
  qt "SELECT 'form_encounter' t, COUNT(*) n, MAX(date) newest FROM form_encounter
      UNION ALL SELECT 'prescriptions', COUNT(*), MAX(date_added) FROM prescriptions
      UNION ALL SELECT 'lists',         COUNT(*), MAX(date)       FROM lists
      UNION ALL SELECT 'form_vitals',   COUNT(*), MAX(date)       FROM form_vitals
      UNION ALL SELECT 'billing',       COUNT(*), MAX(date)       FROM billing
      UNION ALL SELECT 'documents',     COUNT(*), MAX(date)       FROM documents;"
  echo "--- Recurring series runway ---"
  qt "SELECT pc_eid, pc_title, pc_eventDate, pc_endDate,
             DATEDIFF(pc_endDate, CURDATE()) days_left
        FROM openemr_postcalendar_events WHERE pc_recurrtype <> 0;"
  echo
  echo "Gate state: run-tested=$([ -f $STATE_DIR/run-tested ] && echo yes || echo NO)" \
       " rollback-tested=$([ -f $STATE_DIR/rollback-tested ] && echo yes || echo NO)"
}

# ---------------------------------------------------------------------------
backup_now() {
  mkdir -p "$BACKUP_DIR"; chmod 700 "$BACKUP_DIR"
  local f="$BACKUP_DIR/pre-rebase-$(date +%Y%m%d-%H%M%S).sql"
  "$DUMP" --defaults-file="$DB_CNF" --single-transaction --quick \
          --routines --triggers "$DB_NAME" > "$f"
  [ -s "$f" ] || { echo "Backup is empty — aborting before any change." >&2; exit 1; }
  log "backup written: $f ($(stat -c%s "$f") bytes)"
  ls -1t "$BACKUP_DIR"/pre-rebase-*.sql | tail -n +$((RETENTION + 1)) | xargs -r rm -f
  echo "$f"
}

run_mode() {
  need_root; need_db
  local dry=0 yes=0
  for a in "$@"; do
    case "$a" in --dry-run) dry=1 ;; --yes) yes=1 ;; esac
  done

  local off; off=$(q "$OFFSET_SQL")
  if [ "$off" -eq 0 ]; then
    log "offset is 0 — seed already sits in the current week, nothing to do"
    "$MYSQL" --defaults-file="$DB_CNF" "$DB_NAME" -e "$EXTEND_RECURRING_SQL"
    exit 0
  fi

  if [ "$dry" -eq 1 ]; then
    echo "-- DRY RUN. Offset would be ${off} days (always a multiple of 7)."
    shift_sql "$off"
    echo "$EXTEND_RECURRING_SQL"
    exit 0
  fi

  [ "$yes" -eq 1 ] || { echo "Refusing to write without --yes (or use --dry-run)." >&2; exit 1; }

  local bk; bk=$(backup_now)
  log "re-base starting: offset=${off} days, backup=${bk}"
  { echo "START TRANSACTION;"; shift_sql "$off"; echo "$EXTEND_RECURRING_SQL"; echo "COMMIT;"; } \
    | "$MYSQL" --defaults-file="$DB_CNF" "$DB_NAME"

  # Post-condition: the time columns must be untouched by anything we did.
  local bad; bad=$(q "SELECT COALESCE(SUM(pc_startTime IS NULL),0)
                           + COALESCE(SUM(pc_startTime > '23:59:59'),0)
                        FROM openemr_postcalendar_events WHERE pc_recurrtype = 0;")
  if [ "${bad:-0}" -ne 0 ]; then
    log "POST-CHECK FAILED: pc_startTime damaged ($bad rows). Restore with: $0 rollback"
    exit 1
  fi
  mkdir -p "$STATE_DIR"; touch "$STATE_DIR/run-tested"
  log "re-base complete, post-check clean"
  check_mode
}

# ---------------------------------------------------------------------------
rollback_mode() {
  need_root; need_db
  local f; f=$(ls -1t "$BACKUP_DIR"/pre-rebase-*.sql 2>/dev/null | head -1 || true)
  [ -n "$f" ] || { echo "No backup found in $BACKUP_DIR." >&2; exit 1; }
  echo "About to restore $f into database '$DB_NAME'. This overwrites current data."
  read -r -p "Type RESTORE to continue: " ans
  [ "$ans" = "RESTORE" ] || { echo "Aborted."; exit 1; }
  "$MYSQL" --defaults-file="$DB_CNF" "$DB_NAME" < "$f"
  mkdir -p "$STATE_DIR"; touch "$STATE_DIR/rollback-tested"
  log "rollback complete from $f"
}

# ---------------------------------------------------------------------------
# One-time content correction. Saudi weekend is Friday-Saturday; the clinic
# week is Sunday-Thursday. The seed currently books 4 visits on a Saturday and
# none on Thursday. Saturday -2 days and Friday -1 day both land on Thursday,
# which is the working day that has no visits — so this both clears the
# weekend and fills the gap, in one move, without inventing any new rows.
#
# Apply this to the CLEAN BASELINE as well when that is created, or the next
# reset re-introduces the weekend bookings.
# ---------------------------------------------------------------------------
fix_weekdays_mode() {
  need_root; need_db
  local n; n=$(q "SELECT COUNT(*) FROM openemr_postcalendar_events
                   WHERE pc_recurrtype = 0 AND DAYOFWEEK(pc_eventDate) IN (6,7);")
  if [ "$n" -eq 0 ]; then echo "No Friday/Saturday appointments. Nothing to do."; exit 0; fi
  echo "$n appointment(s) fall on the Saudi weekend. They will move to Thursday."
  read -r -p "Type FIX to continue: " ans
  [ "$ans" = "FIX" ] || { echo "Aborted."; exit 1; }
  backup_now >/dev/null
  "$MYSQL" --defaults-file="$DB_CNF" "$DB_NAME" <<'SQL'
START TRANSACTION;
UPDATE openemr_postcalendar_events SET pc_eventDate = DATE_SUB(pc_eventDate, INTERVAL 2 DAY)
 WHERE pc_recurrtype = 0 AND DAYOFWEEK(pc_eventDate) = 7;   -- Saturday -> Thursday
UPDATE openemr_postcalendar_events SET pc_eventDate = DATE_SUB(pc_eventDate, INTERVAL 1 DAY)
 WHERE pc_recurrtype = 0 AND DAYOFWEEK(pc_eventDate) = 6;   -- Friday   -> Thursday
COMMIT;
SQL
  log "weekday correction applied to $n row(s)"
  check_mode
}

# ---------------------------------------------------------------------------
install_mode() {
  need_root; need_db
  if [ ! -f "$STATE_DIR/run-tested" ] || [ ! -f "$STATE_DIR/rollback-tested" ]; then
    cat >&2 <<'GATE'
REFUSING TO INSTALL THE TIMER.

Owner decision SEED-001 schedules nothing until the re-base and its rollback
have both been exercised on THIS host. Do this first, on demo-openemr:

  sudo ./05-seed001-demo-date-rebase.sh check
  sudo ./05-seed001-demo-date-rebase.sh run --dry-run
  sudo ./05-seed001-demo-date-rebase.sh run --yes        # sets run-tested
  sudo ./05-seed001-demo-date-rebase.sh rollback         # sets rollback-tested
  sudo ./05-seed001-demo-date-rebase.sh run --yes        # re-apply

Then re-run install.
GATE
    exit 1
  fi

  install -m 700 "$0" "$REBASE_SCRIPT"

  cat > /etc/systemd/system/openemr-date-rebase.service << UNITEOF
[Unit]
Description=OpenEMR demo seed whole-week date re-base (SEED-001)
After=network.target mariadb.service

[Service]
Type=oneshot
ExecStart=$REBASE_SCRIPT run --yes
UNITEOF

  # 02:30, ahead of the 03:00 off-site backup, so the backup captures the
  # re-based state rather than the state it is about to replace.
  cat > /etc/systemd/system/openemr-date-rebase.timer << 'UNITEOF'
[Unit]
Description=Run the OpenEMR demo date re-base daily

[Timer]
OnCalendar=*-*-* 02:30:00
Persistent=true

[Install]
WantedBy=timers.target
UNITEOF

  systemctl daemon-reload
  systemctl enable --now openemr-date-rebase.timer
  systemctl list-timers openemr-date-rebase.timer --no-pager
  log "timer installed and enabled"
}

case "${1:-}" in
  check)        shift; check_mode "$@" ;;
  run)          shift; run_mode "$@" ;;
  rollback)     shift; rollback_mode "$@" ;;
  fix-weekdays) shift; fix_weekdays_mode "$@" ;;
  install)      shift; install_mode "$@" ;;
  *) sed -n '2,50p' "$0"; exit 1 ;;
esac
