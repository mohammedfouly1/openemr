<?php
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
    return sys_get_temp_dir() . '/verifysens-' . preg_replace('/[^a-z0-9]/i', '_', $user) . '.jar';
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

echo "Verifying: view_form.php now enforces sensitivity ACL (SYN-0014 pid=14 encounter=31, sensitivity=high, form id=86 SOAP)\n\n";

$roles = [
    'k.alotaibi' => 'Accounting -- redacted via encounters.php per EV-016 4.1, should now ALSO be denied here',
    'y.alharbi' => 'Physician -- per EV-016 4.1, physicians group already holds the sensitivities grant, EXPECT still allowed (not a regression, matches encounters.php behavior for this role)',
];

foreach ($roles as $u => $note) {
    $pw = $accounts[$u] ?? null;
    if ($pw === null) {
        printf("login %-14s : NO CREDENTIAL\n", $u);
        continue;
    }
    login($u, $pw);
    $r = probe($u, '/interface/patient_file/encounter/view_form.php?formname=soap&id=86&pid=14&encounter=31');
    $denied = $r['code'] === 403 || str_contains(strtolower($r['body']), 'not authorized') || str_contains(strtolower($r['body']), 'access denied');
    printf("%-14s HTTP %d, %dB -- %s\n  (%s)\n\n", $u, $r['code'], $r['len'], $denied ? 'DENIED' : 'NOT DENIED', $note);
    @unlink(jar($u));
}
