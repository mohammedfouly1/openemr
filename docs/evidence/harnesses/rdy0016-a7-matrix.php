<?php

/**
 * RDY-0016 — §23.4 row A-7 supplementary probes: "cannot amend another
 * clinician's note; cannot sign lab results."
 *
 * Kept separate from rdy0016-matrix.php (whose documented "EXECUTED: 32" in
 * EV-016 §6 must stay stable) because this probe targets a specific existing
 * form record (id 115, y.alharbi's SOAP note on encounter 117 / pid 32) that
 * did not exist when the original 32-probe harness was written.
 *
 * Method mirrors rdy0016-matrix.php: login POST + direct-URL GET with
 * redirects disabled, per-role cookie jar. NO PASSWORD IS PRINTED.
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
    return sys_get_temp_dir() . '/rdy0016a7-' . preg_replace('/[^a-z0-9]/i', '_', $user) . '.jar';
}

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
    return !str_contains($body, 'name="clearPass"') && !str_contains($body, 'Invalid');
}

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

function denied(array $r): bool
{
    if ($r['code'] === 403) {
        return true;
    }
    $b = strtolower($r['body']);
    return str_contains($b, 'not authorized') || str_contains($b, 'access denied');
}

echo str_repeat('=', 78) . "\nRDY-0016 A-7 SUPPLEMENTARY PROBES\n" . str_repeat('=', 78) . "\n\n";

$DOC  = 'y.alharbi';    // Physician (provider_id 6) -- the note's author
$DOC2 = 's.almutairi';  // Physician (provider_id 7) -- NOT the note's author

foreach ([$DOC, $DOC2] as $u) {
    $pw = $accounts[$u] ?? null;
    if ($pw === null) {
        printf("login %-14s : NO CREDENTIAL IN STORE\n", $u);
        continue;
    }
    $ok = login($u, $pw);
    printf("login %-14s : %s\n", $u, $ok ? 'OK' : 'FAILED');
}
echo "\n";

// Target: form id 115, SOAP note authored by y.alharbi on encounter 117 / pid 32
// (created live 2026-08-19 during RDY-0041's second D-7 run, PB-442).
$url = '/interface/patient_file/encounter/view_form.php?formname=soap&id=115&pid=32&encounter=117';

$r1 = probe($DOC, $url);
printf(
    "A-7   %-14s %-52s HTTP %d, %dB\n",
    $DOC,
    'CAN view own note (positive control)',
    $r1['code'],
    $r1['len']
);

$r2 = probe($DOC2, $url);
printf(
    "A-7   %-14s %-52s HTTP %d, %dB %s\n",
    $DOC2,
    'view another clinician\'s note (id 115)',
    $r2['code'],
    $r2['len'],
    denied($r2) ? '-- DENIED' : '-- NOT DENIED (see note below)'
);

echo "\n" . str_repeat('-', 78) . "\n";
echo "Interpretation: view_form.php (interface/patient_file/encounter/view_form.php:41)\n";
echo "gates solely on AclMain::aclCheckForm(\$_GET['formname']) -- a FORM-TYPE ACL check,\n";
echo "not a per-record author/provider check. If s.almutairi's probe is NOT DENIED, this\n";
echo "is the code confirming why: there is no ownership gate at this layer for any\n";
echo "physician-group account, consistent with PB-410/EV-016 section 4.1's finding that\n";
echo "the physicians ACL group is not gated per-record at all in this configuration.\n";
echo str_repeat('-', 78) . "\n";

foreach ([$DOC, $DOC2] as $u) {
    @unlink(jar($u));
}
