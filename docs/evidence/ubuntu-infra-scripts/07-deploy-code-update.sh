#!/bin/bash
# Code update for demo-openemr -- refresh /var/www/openemr from the current
# tip of feat/thiqa-branding-foundation on GitHub.
#
# Prepared by Claude Code, 2026-08-20. NOT executed automatically -- the same
# boundary as every other script in this directory: remote mutating commands
# over SSH are blocked for this agent regardless of authorization. Run it
# yourself on the VM as a user with sudo.
#
#   gcloud compute ssh demo-openemr --zone=us-central1-a \
#       --project=project-c2365b97-e364-4ea0-bc2
#   # copy this file across (or paste it), then:
#   chmod +x 07-deploy-code-update.sh
#   sudo ./07-deploy-code-update.sh preflight   # read-only, changes nothing
#   sudo ./07-deploy-code-update.sh run         # the actual update
#   sudo ./07-deploy-code-update.sh verify      # re-run the checks any time
#   sudo ./07-deploy-code-update.sh rollback    # back to the previous commit
#
# Env switches for 'run':
#   ONLY_IF=<sha>    abort unless origin's tip is exactly this commit
#   RUN_COMPOSER=1   run composer install --no-dev (not needed, see below)
#   SKIP_BACKUP=1    skip the pre-update off-site backup
#   FORCE=1          proceed even if the deployed commit is not the expected one
#
# TRANSPORT: same as the original deployment -- git, from GitHub. The branch
# is already pushed; origin/feat/thiqa-branding-foundation is 6cfcaa9d9,
# byte-identical to the workstation's HEAD. Nothing needs pushing first, and
# nothing is copied off the Windows workstation (which would drag CRLF into
# every shebang -- see docs/demo-deployment-readiness.md around line 2018).
#
# WHAT THIS UPDATES, AND WHAT IT DELIBERATELY DOES NOT
#
# The deployed tree is a depth-1 shallow clone in detached HEAD at 6de7cdcc1
# (2026-08-16). Origin's branch tip is 6cfcaa9d9 (2026-08-20), 288 commits
# later. Of the 535 changed files, 514 are docs/, tests/, tools/, ci/ and
# .github/ -- none are runtime files. 21 are:
#
#   interface/main/tabs/main.php
#   interface/modules/custom_modules/oe-module-thiqa-branding/
#       src/Console/SeedDemoCommand.php
#   interface/patient_file/encounter/load_form.php
#   interface/patient_file/encounter/view_form.php
#   interface/patient_file/summary/add_edit_issue.php
#   interface/reports/pat_ledger.php
#   library/globals.inc.php
#   sites/default/config.php
#   src/Billing/EdiHistory/X12File.php
#   src/Common/Acl/AclMain.php
#   src/Common/Command/CreateReleaseChangelogCommand.php   (deleted)
#   src/Common/Command/ReleasePrep/Mutator/
#       BranchCutReleaseTargetsMutator.php
#   src/Common/Command/ReleasePrepCommand.php
#   src/Gacl/Gacl.php
#   src/RestControllers/AuthorizationController.php
#   src/RestControllers/SMART/SMARTAuthorizationController.php
#   src/Services/EncounterService.php
#   templates/oauth2/oauth2-login.html.twig
#   templates/oauth2/patient-select.html.twig
#   templates/oauth2/scope-authorize.html.twig
#   templates/product_registration/product_reg.js.twig
#
#   * NO composer install is required. composer.lock's "packages" (the
#     --no-dev production set) is identical between the two commits; the only
#     lock changes are require-dev additions (symfony/mime and its intl-idn
#     polyfill). Set RUN_COMPOSER=1 if you want it run anyway.
#   * NO front-end rebuild is required. Nothing under public/themes/,
#     public/assets/ or interface/themes/ changed, so the Q77 theme-purge
#     rule (CLAUDE.local.md section 6) is not in play. The deployed tree has
#     zero solar/manila/cobalt_blue/forest_green stylesheets today and this
#     update cannot reintroduce one. 'verify' re-checks that anyway.
#   * NO schema migration is required. sql/ is unchanged. The one globals
#     addition (audit_events_lab-order in library/globals.inc.php) is a
#     GLOBALS_METADATA entry with a default, not a schema change.
#   * sites/default/config.php IS in the change set. Its change is
#     PHP_OS_FAMILY-conditional (RDY-0049): Linux keeps the historical lpr /
#     enscript / /usr/bin/file values verbatim, so behaviour on this host is
#     unchanged. The file is 600 www-data:www-data and this script restores
#     that mode and ownership after checkout, because a root-run git checkout
#     would otherwise leave it root-owned 644 inside a 700 www-data directory.
#   * sites/default/sqlconf.php carries the rotated DB password from RDY-0048
#     and is NOT in the change set, so checkout leaves it alone. 'run'
#     re-verifies that before touching anything and aborts if it is wrong.

set -euo pipefail

REPO=/var/www/openemr
BRANCH=feat/thiqa-branding-foundation
TARGET_SHA=6cfcaa9d9ae846afbc5531e2f3a1b8428bd9e621
EXPECTED_OLD_SHA=6de7cdcc1195bc83c0b740d8616a0624b5f90b99
STATE_DIR=/var/lib/openemr-deploy
BACKUP_SCRIPT=/usr/local/bin/openemr-offsite-backup.sh
MONITOR_SCRIPT=/usr/local/bin/openemr-monitoring-checks.sh

g() { git -c safe.directory="$REPO" -C "$REPO" "$@"; }

need_root() {
  if [ "$EUID" -ne 0 ]; then echo "Run with sudo." >&2; exit 1; fi
}

# Code directories are owned by the login user, not www-data (only sites/ is
# www-data). Read the owner rather than hard-coding a username.
tree_owner() { stat -c '%U:%G' "$REPO"; }

# Restore ownership/mode after a root-run checkout: anything under sites/
# belongs to www-data at 600, everything else to the tree owner.
restore_perms() {
  local owner list f
  owner=$(tree_owner)
  list=$1
  while IFS= read -r f; do
    [ -n "$f" ] || continue
    [ -e "$REPO/$f" ] || continue
    case "$f" in
      sites/*)
        chown www-data:www-data "$REPO/$f"
        chmod 600 "$REPO/$f"
        ;;
      *)
        chown "$owner" "$REPO/$f"
        ;;
    esac
  done <<< "$list"

  # `git diff --name-only` lists FILES, so the loop above misses any
  # DIRECTORY git had to create for a new path -- those stay root-owned.
  # The 2026-08-20 run left 40 such directories (all under docs/, tests/,
  # .github/, .agents/; no runtime path, no functional impact, but wrong).
  # Sweep whatever the checkout left as root, excluding .git internals and
  # sites/ (which is legitimately www-data-owned).
  local n
  n=$(find "$REPO" -user root \
        -not -path "$REPO/.git/*" -not -path "$REPO/sites/*" \
        -printf . 2>/dev/null | wc -c)
  if [ "$n" -gt 0 ]; then
    find "$REPO" -user root \
      -not -path "$REPO/.git/*" -not -path "$REPO/sites/*" \
      -exec chown "$owner" {} +
    echo "  swept $n root-owned path(s) left by the checkout back to $owner"
  fi
}

availability_check() {
  # Same probe the live M-1 monitoring signal uses: hit the HTTPS vhost on
  # the origin directly (the port-80 vhost only redirects out through
  # Cloudflare), -k because localhost will not match the demo.skyeagle.uk CN.
  local code body_size tmpfile
  tmpfile=$(mktemp /tmp/deploy-verify.XXXXXX)
  code=$(curl -sk -o "$tmpfile" -w '%{http_code}' --max-time 15 \
      -H 'Host: demo.skyeagle.uk' \
      'https://localhost/interface/login/login.php?site=default' || echo 000)
  body_size=$(stat -c%s "$tmpfile" 2>/dev/null || echo 0)
  rm -f "$tmpfile"
  echo "  HTTP $code, ${body_size}B"
  [ "$code" = "200" ] && [ "$body_size" -gt 5120 ]
}

preflight_mode() {
  need_root
  echo "== deployed HEAD =="
  g rev-parse HEAD
  echo "== expected current == $EXPECTED_OLD_SHA"
  echo "== target           == $TARGET_SHA"
  echo
  echo "== origin branch tip (read-only, no fetch) =="
  g ls-remote origin "$BRANCH"
  echo
  echo "== local modifications in the deployed tree =="
  g status --porcelain || true
  echo "  (expected: sites/default/sqlconf.php and"
  echo "   sites/default/documents/custom_menus/patient_menus/Custom.json --"
  echo "   neither is in the incoming change set, so both survive checkout.)"
  echo
  echo "== shallow clone? =="
  if [ -f "$REPO/.git/shallow" ]; then
    echo "yes (depth 1) -- the update uses a depth-1 fetch"
  else
    echo "no"
  fi
  echo
  echo "== app availability right now =="
  if availability_check; then
    echo "  OK"
  else
    echo "  !! NOT healthy before the update -- fix that first"
  fi
  echo
  echo "Preflight only. Nothing was changed. Run 'run' to update."
}

run_mode() {
  need_root
  mkdir -p "$STATE_DIR"

  local old_sha
  old_sha=$(g rev-parse HEAD)
  echo "Currently deployed: $old_sha"
  if [ "$old_sha" != "$EXPECTED_OLD_SHA" ] && [ "${FORCE:-0}" != "1" ]; then
    echo "!! Expected $EXPECTED_OLD_SHA. The tree has moved since this" >&2
    echo "   script was written -- re-check the diff, then re-run FORCE=1." >&2
    exit 1
  fi

  echo "== 1/7 pre-update backup =="
  if [ "${SKIP_BACKUP:-0}" = "1" ]; then
    echo "  skipped (SKIP_BACKUP=1)"
  elif [ -x "$BACKUP_SCRIPT" ]; then
    if ! "$BACKUP_SCRIPT" run; then
      echo >&2
      echo "!! The pre-update backup FAILED. Stopping before any change." >&2
      echo "   If mysqldump reported 'Access denied for user openemr', the" >&2
      echo "   cause is /root/.my.cnf.openemr-backup holding a password from" >&2
      echo "   before the RDY-0048 rotation. Fix it with:" >&2
      echo "     sudo ./08-fix-backup-db-credentials.sh check" >&2
      echo "     sudo ./08-fix-backup-db-credentials.sh fix" >&2
      echo "   then re-run this script. Deploying without a backup is" >&2
      echo "   possible via SKIP_BACKUP=1, but fix the backup first --" >&2
      echo "   a broken backup is a bigger problem than a stale deploy." >&2
      exit 1
    fi
  else
    echo "  !! $BACKUP_SCRIPT not found -- continuing without a fresh" >&2
    echo "     backup. Code rollback still works via the tag below." >&2
  fi

  echo "== 2/7 tagging the current commit so rollback survives gc =="
  # The clone is shallow: once HEAD moves the old commit is unreachable and a
  # future gc could drop it. A tag keeps it alive.
  local tag
  tag="pre-update-$(date -u +%Y%m%dT%H%M%SZ)"
  g tag -f "$tag" "$old_sha"
  printf '%s\n' "$old_sha" > "$STATE_DIR/previous-sha"
  printf '%s\n' "$tag" > "$STATE_DIR/previous-tag"
  echo "  tagged $tag -> $old_sha"

  echo "== 3/7 fetching $BRANCH (depth 1) =="
  g fetch --depth=1 origin "$BRANCH"
  local new_sha
  new_sha=$(g rev-parse FETCH_HEAD)
  echo "  FETCH_HEAD = $new_sha"
  if [ "$new_sha" != "$TARGET_SHA" ]; then
    echo "  note: origin has moved past $TARGET_SHA, the tip this script was"
    echo "  written against. Expected -- committing this script itself"
    echo "  advances the branch. The runtime-file list printed below is the"
    echo "  authority on what is actually being deployed; review it."
  fi
  if [ -n "${ONLY_IF:-}" ] && [ "$new_sha" != "$ONLY_IF" ]; then
    echo "!! ONLY_IF=$ONLY_IF was set but origin's tip is $new_sha." >&2
    exit 1
  fi

  echo "== 4/7 reviewing the change set =="
  echo "  Runtime files changing (everything outside docs/ tests/ tools/"
  echo "  ci/ .github/ .phpstan/ .agents/ Documentation/ docker/):"
  g diff --name-status "$old_sha" "$new_sha" \
    | grep -Ev $'\t(docs|tests|tools|ci|\\.github|\\.phpstan|\\.agents|Documentation|docker)/' \
    | sed 's/^/    /' || true
  echo
  echo "  Dependency/schema impact:"
  if g diff --quiet "$old_sha" "$new_sha" -- sql/; then
    echo "    sql/          unchanged -- no migration"
  else
    echo "    !! sql/ CHANGED -- a migration may be required, review before continuing"
  fi
  if g diff --quiet "$old_sha" "$new_sha" -- public/themes/ public/assets/ interface/themes/; then
    echo "    themes/assets unchanged -- no front-end rebuild"
  else
    echo "    !! public/themes, public/assets or interface/themes CHANGED --"
    echo "       re-check the Q77 purge rule before continuing"
  fi
  if g diff --quiet "$old_sha" "$new_sha" -- composer.lock; then
    echo "    composer.lock unchanged -- no composer install"
  else
    echo "    composer.lock changed -- verified dev-only when this script was"
    echo "    written; set RUN_COMPOSER=1 if you want composer install run"
  fi
  echo

  echo "== 4b/7 checking local modifications are not in the change set =="
  local changed dirty clash n_changed n_dirty
  changed=$(g diff --name-only "$old_sha" "$new_sha")
  dirty=$(g status --porcelain | sed 's/^...//')
  clash=$(comm -12 <(printf '%s\n' "$changed" | sort -u) \
                   <(printf '%s\n' "$dirty" | sort -u) || true)
  if [ -n "$clash" ]; then
    echo "!! These locally-modified files also change upstream:" >&2
    printf '%s\n' "$clash" >&2
    echo "   Resolve by hand -- refusing to clobber local state." >&2
    exit 1
  fi
  n_changed=$(printf '%s\n' "$changed" | grep -c . || true)
  n_dirty=$(printf '%s\n' "$dirty" | grep -c . || true)
  echo "  no clash ($n_changed files change, $n_dirty locally modified)"

  echo "== 5/7 checking out $new_sha =="
  g checkout --detach "$new_sha"
  restore_perms "$changed"
  echo "  ownership/mode restored on the changed paths"

  if [ "${RUN_COMPOSER:-0}" = "1" ]; then
    echo "== 6/7 composer install (requested via RUN_COMPOSER=1) =="
    sudo -u "$(stat -c '%U' "$REPO")" composer --working-dir="$REPO" \
        install --no-dev --optimize-autoloader --no-interaction
  else
    echo "== 6/7 composer install SKIPPED =="
    echo "  composer.lock's production package set is unchanged between"
    echo "  $old_sha and $new_sha. Set RUN_COMPOSER=1 to run it anyway."
  fi

  echo "== 7/7 reloading apache (clears opcache) and verifying =="
  apache2ctl configtest
  systemctl reload apache2
  sleep 3
  verify_mode
}

verify_mode() {
  need_root
  local head
  head=$(g rev-parse HEAD)
  echo "Deployed HEAD: $head"
  if [ "$head" = "$TARGET_SHA" ]; then
    echo "  matches target $TARGET_SHA"
  else
    echo "  (target was $TARGET_SHA)"
  fi

  echo "Local modifications preserved:"
  g status --porcelain || true
  echo "sqlconf.php:"
  stat -c '  %a %U:%G %n' "$REPO/sites/default/sqlconf.php"
  echo "config.php:"
  stat -c '  %a %U:%G %n' "$REPO/sites/default/config.php"

  echo -n "Q77 forbidden themes present (expect 0): "
  ls "$REPO/public/themes" | grep -cE 'solar|manila|cobalt_blue|forest_green' || true

  echo "Availability:"
  if availability_check; then echo "  OK"; else echo "  !! FAILED"; fi

  if [ -x "$MONITOR_SCRIPT" ]; then
    echo "Monitoring run (all six signals):"
    "$MONITOR_SCRIPT" run || true
    tail -8 /var/log/openemr-monitoring.log 2>/dev/null || true
  fi

  echo -n "Background-services timer: "
  systemctl is-active openemr-background-services.timer || true
}

rollback_mode() {
  need_root
  local prev changed
  if [ -f "$STATE_DIR/previous-sha" ]; then
    prev=$(cat "$STATE_DIR/previous-sha")
  else
    prev=$EXPECTED_OLD_SHA
    echo "No state file; falling back to $prev"
  fi
  echo "Rolling back to $prev"
  changed=$(g diff --name-only HEAD "$prev")
  g checkout --detach "$prev"
  restore_perms "$changed"
  apache2ctl configtest
  systemctl reload apache2
  sleep 3
  verify_mode
}

case "${1:-}" in
  preflight) preflight_mode ;;
  run)       run_mode ;;
  verify)    verify_mode ;;
  rollback)  rollback_mode ;;
  *) echo "Usage: sudo $0 {preflight|run|verify|rollback}" >&2; exit 1 ;;
esac
