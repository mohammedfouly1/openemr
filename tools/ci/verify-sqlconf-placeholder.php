<?php

declare(strict_types=1);

/**
 * Pre-commit guard for sites/*\/sqlconf.php (RDY-0048, EV-048 R-3).
 *
 * sites/default/sqlconf.php is tracked by upstream OpenEMR itself and must
 * stay physically present for every fresh clone/worktree -- library/sqlconf.php
 * does a hard require_once on it, so removing it from tracking breaks the app
 * boot on any checkout that doesn't already have a locally-installed copy.
 *
 * The file is also where every local installation writes its real DB
 * credentials, directly into this same tracked path. `skip-worktree` hides
 * that local modification from `git status` on the machine where it was set,
 * but that is per-clone, invisible in code review, and does not stop a
 * different clone -- or the same one with skip-worktree cleared -- from
 * staging and committing a real credential.
 *
 * This script is the durable, clone-independent backstop: it runs on every
 * commit that touches a sites/*\/sqlconf.php path and fails the commit unless
 * the staged content is byte-for-byte the known-safe upstream placeholder
 * (login/pass/dbase all 'openemr', $config = 0). A real credential -- from
 * this repo, from a customer's provisioned instance, from anyone's laptop --
 * can never enter git history through this file again, regardless of what
 * skip-worktree happens to be set to on the machine doing the committing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$files = array_slice($argv, 1);
if ($files === []) {
    exit(0);
}

$exitCode = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        fwrite(STDERR, "verify-sqlconf-placeholder: could not read {$file}\n");
        $exitCode = 1;
        continue;
    }

    $isPlaceholder =
        preg_match('/\$login\s*=\s*\'openemr\'\s*;/', $content) === 1
        && preg_match('/\$pass\s*=\s*\'openemr\'\s*;/', $content) === 1
        && preg_match('/\$dbase\s*=\s*\'openemr\'\s*;/', $content) === 1
        && preg_match('/\$config\s*=\s*0\s*;/', $content) === 1;

    if (!$isPlaceholder) {
        fwrite(STDERR, <<<MSG
            verify-sqlconf-placeholder: BLOCKED -- {$file} does not match the
            known-safe upstream placeholder (login/pass/dbase = 'openemr',
            \$config = 0).

            This file must never carry a real database credential in git
            history. If you are committing a genuine upstream update to this
            file's template content, verify the new content against
            upstream/rel-* first and update this check's expected pattern in
            the same commit. If your working copy has real local credentials
            in it (normal after installing), that is expected and fine --
            just do not stage this file. Set skip-worktree on it locally so
            git stops offering to:

                git update-index --skip-worktree {$file}


            MSG);
        $exitCode = 1;
    }
}

exit($exitCode);
