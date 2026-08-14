<?php

/**
 * RDY-0016 — §23.4 authorization matrix, executed over real authenticated HTTP.
 *
 * Method mirrors PB-012/PB-013: login POST to main_screen.php?auth=login with a
 * per-role cookie jar, then direct-URL GET with redirects disabled.
 *
 * NO PASSWORD IS PRINTED. Credentials are read from the protected store and used
 * only inside the login POST body.
 */

declare(strict_types=1);

const BASE = 'http://localhost:8300';
const STORE = 'C:/openemr-stack/secrets/thiqa-demo-credentials.json';

$store = json_decode((string) file_get_contents(STORE), true, 512, JSON_THROW_ON_ERROR);
$accounts = [];
foreach ($store['accounts'] as $a) {
    $accounts[$a['username']] = $a['password'];
}

function jar(string $user): string
{
    return sys_get_temp_dir() . '/rdy0016-' . preg_replace('/[^a-z0-9]/i', '_', $user) . '.jar';
}

/** Log in and keep the session cookie. Returns true on success. */
function login(string $user, string $pass): bool
{
    $jar = jar($user);
    @unlink($jar);
    $ch = curl_init(BASE . '/interface/main/main_screen.php?auth=login&site=default');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'new_login_session_management' => '1',
            'authUser' => $user,
            'clearPass' => $pass,
            'languageChoice' => '1',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = (string) curl_exec($ch);
    curl_close($ch);
    // A successful login sets a session and does not return the login form again.
    return !str_contains($body, 'name="clearPass"') && !str_contains($body, 'Invalid');
}

/** Direct-URL GET under an established session. */
function probe(string $user, string $path): array
{
    $ch = curl_init(BASE . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE => jar($user),
        CURLOPT_COOKIEJAR => jar($user),
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'len' => strlen($body), 'body' => $body];
}

/** Was this a denial? 403, or a body carrying the ACL denial marker. */
function denied(array $r): bool
{
    if ($r['code'] === 403) {
        return true;
    }
    // Tightened: no loose substring heuristics. A denial is a 403, or the
    // application's own denial wording. "acl" as a bare substring was removed --
    // it would match ordinary page content and manufacture a false PASS.
    $b = strtolower($r['body']);
    return str_contains($b, 'not authorized')
        || str_contains($b, 'access denied');
}

echo str_repeat('=', 78) . "\nRDY-0016 §23.4 AUTHORIZATION MATRIX\n" . str_repeat('=', 78) . "\n\n";

// ---- establish sessions -----------------------------------------------------
$sessions = [];
foreach ($accounts as $u => $p) {
    $ok = login($u, $p);
    $sessions[$u] = $ok;
    printf("login %-14s : %s\n", $u, $ok ? 'OK' : 'FAILED');
}
echo "\n";

$FO   = 'r.aldosari';   // Front Office
$DOC  = 'y.alharbi';    // Physician
$DOC2 = 's.almutairi';  // Physician 2
$ACC  = 'k.alotaibi';   // Accounting
$CLIN = 'm.alzahrani';  // Clinical Assistant
$ADM  = 'n.alqahtani';  // Administrator

$pass = 0;
$fail = 0;
$rows = [];

function check(string $row, string $actor, string $desc, bool $ok, string $detail): void
{
    global $pass, $fail, $rows;
    $ok ? $pass++ : $fail++;
    $rows[] = [$row, $actor, $desc, $ok ? 'PASS' : 'FAIL', $detail];
    printf("%-5s %-14s %-52s %s  %s\n", $row, $actor, substr($desc, 0, 52), $ok ? 'PASS' : 'FAIL', $detail);
}

// ---- A-1 negative: Front Office must not reach clinical surfaces -------------
$a1 = [
    'clinical note (encounter forms)' => '/interface/patient_file/encounter/load_form.php?formname=soap',
    'prescriptions (controller)'      => '/controller.php?prescription&action=list',
    'lab results'                     => '/interface/orders/procedure_stats.php',
    'patient report'                  => '/interface/patient_file/report/patient_report.php?pid=1',
];
foreach ($a1 as $what => $url) {
    $r = probe($FO, $url);
    check('A-1', $FO, "cannot open $what", denied($r), "HTTP {$r['code']}, {$r['len']}B");
}

// ---- A-3 / A-4: the eleven reports + amc, direct URL -------------------------
$reports = [
    'patient_list', 'unique_seen_patients_report', 'patient_flow_board_report',
    'chart_location_activity', 'charts_checked_out', 'cdr_log', 'destroyed_drugs_report',
    'patient_edu_web_lookup', 'external_data', 'services_by_category', 'amc_full_report',
];
foreach ($reports as $rep) {
    $r = probe($FO, "/interface/reports/{$rep}.php");
    $row = $rep === 'amc_full_report' ? 'A-4' : 'A-3';
    // The three operational reports are a documented business exception (PB-008).
    $operational = in_array($rep, ['patient_flow_board_report', 'chart_location_activity', 'charts_checked_out'], true);
    $ok = $operational ? !denied($r) : denied($r);
    $note = $operational ? 'operational — Reception SHOULD reach it (PB-008)' : '';
    check($row, $FO, ($operational ? 'CAN reach ' : 'cannot reach ') . $rep, $ok, "HTTP {$r['code']} $note");
}

// ---- A-5: x12_partner controller --------------------------------------------
$r = probe($FO, '/controller.php?x12_partner&action=list');
check('A-5', $FO, 'cannot reach ?x12_partner&action=list', denied($r), "HTTP {$r['code']}");

// ---- A-9: admin AJAX endpoint, all non-admin ---------------------------------
foreach ([$FO, $DOC, $ACC, $CLIN] as $u) {
    $r = probe($u, '/interface/super/layout_listitems_ajax.php');
    check('A-9', $u, 'cannot reach layout_listitems_ajax.php', denied($r), "HTTP {$r['code']}");
}

// ---- A-6 negative: Physician must not reach admin/financial surfaces ---------
$a6 = [
    'practice settings'  => '/controller.php?practice_settings&action=list',
    'user administration' => '/interface/usergroup/usergroup_admin.php',
    'ACL administration' => '/interface/usergroup/adminacl.php',
    'payment posting'    => '/interface/billing/new_payment.php',
];
foreach ($a6 as $what => $url) {
    $r = probe($DOC, $url);
    check('A-6', $DOC, "cannot open $what", denied($r), "HTTP {$r['code']}");
}

// ---- A-8: Accounting positive (financial) and negative (clinical) ------------
$r = probe($ACC, '/interface/reports/pat_ledger.php?form=1&patient_id=1');
check('A-8+', $ACC, 'CAN run the patient ledger', !denied($r) && $r['code'] === 200, "HTTP {$r['code']}, {$r['len']}B");
$r = probe($ACC, '/interface/patient_file/report/patient_report.php?pid=1');
check('A-8-', $ACC, 'cannot open a clinical patient report', denied($r), "HTTP {$r['code']}");

// ---- A-11: non-ordinal — a permission not granted is denied ------------------
// Clinical Assistant holds patients|med but not admin|super; admin|super must not
// be implied by "having some permission".
$r = probe($CLIN, '/interface/super/edit_layout.php');
check('A-11', $CLIN, 'patients|med does not imply admin|super', denied($r), "HTTP {$r['code']}");
$r = probe($FO, '/interface/usergroup/usergroup_admin.php');
check('A-11', $FO, 'patients|demo does not imply admin|users', denied($r), "HTTP {$r['code']}");

// ---- POSITIVE CONTROL: the Administrator reaches what the others cannot ------
// Without this, "every probe returned 403" is equally consistent with a broken
// application. These prove the denials are role-specific.
$controls = [
    'patient_list'                  => '/interface/reports/patient_list.php',
    'amc_full_report'               => '/interface/reports/amc_full_report.php',
    '?x12_partner&list (working form)' => '/controller.php?x12_partner&list',
    'user administration'           => '/interface/usergroup/usergroup_admin.php',
];
foreach ($controls as $what => $url) {
    $r = probe($ADM, $url);
    check('CTRL', $ADM, "CAN reach $what", !denied($r) && $r['code'] === 200, "HTTP {$r['code']}, {$r['len']}B");
}

// ---- summary ----------------------------------------------------------------
echo "\n" . str_repeat('-', 78) . "\n";
printf("EXECUTED: %d   PASS: %d   FAIL: %d\n", $pass + $fail, $pass, $fail);
echo str_repeat('-', 78) . "\n";

foreach ($accounts as $u => $_) {
    @unlink(jar($u));
}
