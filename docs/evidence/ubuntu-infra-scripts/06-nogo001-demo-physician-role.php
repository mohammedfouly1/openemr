<?php

/**
 * NOGO-001 — demo-only Physician role without Patient Reports, for demo-openemr
 *
 * Prepared by Claude Code, 2026-08-20. NOT executed automatically, and NOT yet
 * run against any database — unlike SEED-001's SQL, which was validated in a
 * rolled-back transaction, this script's `apply` path has never been executed.
 * Run `check` first, then `apply` on a host you can restore.
 *
 * WHY THIS EXISTS
 *   §40 row 9: Arabic PDF output cannot be shaped by mPDF. Measured 2026-08-20
 *   on the local instance, the Physician role REACHES
 *   /interface/patient_file/report/custom_report.php (HTTP 200, 4,114 bytes)
 *   while Front Office is denied it (HTTP 403). Custom Report is the live route
 *   to that broken PDF output, and an unaccompanied visitor has no presenter to
 *   qualify it.
 *
 *   Owner ruling NOGO-001, 2026-08-20: restrict Custom Report for the DEMO
 *   Physician role ONLY. Production-role defaults must not change. Revisit when
 *   Arabic PDF shaping is fixed, or when a render/export permission split makes
 *   the on-screen report available without the export.
 *
 * WHY IT CLONES A GROUP INSTEAD OF EDITING ONE
 *   The permission lives in a single ACL row. Verified on the local instance:
 *
 *     acl_id 11 | return_value "view" | ACOs: patients/pat_rep ONLY | mapped to 1 group (doc)
 *     acl_id 12 | addonly  | placeholder/filler
 *     acl_id 13 | wsome    | placeholder/filler
 *     acl_id 14 | write    | 30 ACOs — the bulk of the Physician role
 *
 *   Dropping acl 11 would be one line, but acl 11 IS the stock Physicians
 *   group's permission — editing it changes the production default for every
 *   real physician, which the ruling forbids. So this creates a separate
 *   "Demo Physician" group mapped to acls 12/13/14 but not 11, and moves only
 *   the demo account into it. The Physicians group is never touched.
 *
 * Usage on the VM, from the OpenEMR root:
 *   php docs/evidence/ubuntu-infra-scripts/06-nogo001-demo-physician-role.php check
 *   php docs/evidence/ubuntu-infra-scripts/06-nogo001-demo-physician-role.php apply  --user=y.alharbi
 *   php docs/evidence/ubuntu-infra-scripts/06-nogo001-demo-physician-role.php revert --user=y.alharbi
 *
 * AFTER APPLYING: restart the web server, then re-probe the route as the demo
 * Physician. A cached ACL decision in a live session will not reflect the change.
 *
 * THIS MUST ALSO GO INTO THE CLEAN BASELINE. A database reset restores the
 * baseline's ACL tables, so a reset performed before this change is folded in
 * will silently re-grant Patient Reports to the demo Physician — the same
 * regression shape PB-442 recorded for facility.primary_business_entity.
 */

declare(strict_types=1);

$ignoreAuth = true;
$sessionAllowWrite = true;
$_GET['site'] = 'default';
require_once __DIR__ . '/../../../interface/globals.php';

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Database\QueryUtils;

const DEMO_GROUP_NAME  = 'demodoc';
const DEMO_GROUP_TITLE = 'Demo Physician';
const STOCK_GROUP_NAME = 'doc';
const WITHHELD_SECTION = 'patients';
const WITHHELD_ACO     = 'pat_rep';

/**
 * Every ACL row mapped to a group, with the ACOs it carries.
 *
 * @return list<array{acl_id: int, return_value: string, acos: string}>
 */
function aclRowsForGroup(string $groupName): array
{
    return QueryUtils::fetchRecords(
        "SELECT a.id AS acl_id, a.return_value,
                GROUP_CONCAT(CONCAT(m.section_value, '/', m.value)
                             ORDER BY m.section_value, m.value SEPARATOR ', ') AS acos
           FROM gacl_acl a
           JOIN gacl_aro_groups_map gm ON gm.acl_id = a.id
           JOIN gacl_aro_groups g      ON g.id = gm.group_id AND g.value = ?
           JOIN gacl_aco_map m         ON m.acl_id = a.id
          GROUP BY a.id, a.return_value",
        [$groupName]
    );
}

function groupId(string $groupName): ?int
{
    $id = QueryUtils::fetchSingleValue(
        "SELECT id FROM gacl_aro_groups WHERE value = ?",
        'id',
        [$groupName]
    );
    return $id === null ? null : (int)$id;
}

/** True when this ACL row grants the permission being withheld. */
function carriesWithheldAco(string $acos): bool
{
    return str_contains($acos, WITHHELD_SECTION . '/' . WITHHELD_ACO);
}

function reportCheck(string $user): void
{
    echo "=== NOGO-001 check — " . date('c') . " ===" . PHP_EOL;
    echo "Stock group '" . STOCK_GROUP_NAME . "' ACL rows:" . PHP_EOL;
    foreach (aclRowsForGroup(STOCK_GROUP_NAME) as $r) {
        $mark = carriesWithheldAco((string)$r['acos']) ? ' <-- WITHHELD from the demo role' : '';
        printf("  acl %-4s %-8s %s%s%s", $r['acl_id'], $r['return_value'],
            substr((string)$r['acos'], 0, 90), $mark, PHP_EOL);
    }

    $demoId = groupId(DEMO_GROUP_NAME);
    echo "Demo group '" . DEMO_GROUP_NAME . "': " . ($demoId === null ? 'DOES NOT EXIST' : "id {$demoId}") . PHP_EOL;
    if ($demoId !== null) {
        foreach (aclRowsForGroup(DEMO_GROUP_NAME) as $r) {
            printf("  acl %-4s %-8s %s%s", $r['acl_id'], $r['return_value'],
                substr((string)$r['acos'], 0, 90), PHP_EOL);
        }
    }
    echo "Groups holding '{$user}': " . implode(', ', AclExtended::aclGetGroupTitles($user)) . PHP_EOL;
    echo PHP_EOL . "Route to re-probe after applying, as {$user}:" . PHP_EOL;
    echo "  /interface/patient_file/report/custom_report.php  — expect a denial, not HTTP 200" . PHP_EOL;
}

function apply(string $user): void
{
    $stockRows = aclRowsForGroup(STOCK_GROUP_NAME);
    if ($stockRows === []) {
        throw new RuntimeException('No ACL rows found for the stock group; refusing to guess.');
    }

    $keep = array_values(array_filter(
        $stockRows,
        static fn(array $r): bool => !carriesWithheldAco((string)$r['acos'])
    ));
    $withheld = count($stockRows) - count($keep);
    if ($withheld !== 1) {
        // Either the permission is not granted at all, or it is bundled with
        // others. Both mean the clone would not do what the ruling intends.
        throw new RuntimeException(
            "Expected exactly 1 ACL row carrying " . WITHHELD_SECTION . '/' . WITHHELD_ACO
            . ", found {$withheld}. Re-run check and re-derive before applying."
        );
    }

    if (groupId(DEMO_GROUP_NAME) === null) {
        // Goes through the phpGACL API so the group's nested-set bounds are
        // maintained. A hand-written INSERT into gacl_aro_groups corrupts them.
        AclExtended::addNewACL(DEMO_GROUP_TITLE, DEMO_GROUP_NAME, 'write', 'Demo evaluation physician (NOGO-001)');
    }
    $demoId = groupId(DEMO_GROUP_NAME);
    if ($demoId === null) {
        throw new RuntimeException('Demo group creation failed.');
    }

    foreach ($keep as $r) {
        QueryUtils::sqlStatementThrowException(
            "INSERT IGNORE INTO gacl_aro_groups_map (acl_id, group_id) VALUES (?, ?)",
            [(int)$r['acl_id'], $demoId]
        );
    }

    AclExtended::addUserAros($user, DEMO_GROUP_NAME);
    AclExtended::removeUserAros($user, STOCK_GROUP_NAME);

    echo "Applied. '{$user}' now holds " . DEMO_GROUP_TITLE
        . " (" . count($keep) . " ACL rows, withholding "
        . WITHHELD_SECTION . '/' . WITHHELD_ACO . ")." . PHP_EOL;
    echo "Restart the web server, then re-probe custom_report.php as {$user}." . PHP_EOL;
    echo "Fold this into the clean baseline, or the next reset undoes it." . PHP_EOL;
}

function revert(string $user): void
{
    AclExtended::addUserAros($user, STOCK_GROUP_NAME);
    AclExtended::removeUserAros($user, DEMO_GROUP_NAME);
    echo "Reverted. '{$user}' is back in the stock Physicians group." . PHP_EOL;
    echo "The '" . DEMO_GROUP_TITLE . "' group is left in place; it holds no users." . PHP_EOL;
}

$mode = $argv[1] ?? 'check';
$user = 'y.alharbi';
foreach (array_slice($argv, 2) as $arg) {
    if (str_starts_with($arg, '--user=')) {
        $user = substr($arg, 7);
    }
}

match ($mode) {
    'check'  => reportCheck($user),
    'apply'  => apply($user),
    'revert' => revert($user),
    default  => print("Usage: {$argv[0]} check|apply|revert [--user=NAME]" . PHP_EOL),
};
