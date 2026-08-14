<?php

/**
 * RDY-0042 probe — does the front_office menu offer "Add Patient" for BOTH
 * values of full_new_patient_form?
 *
 * Applies MenuRole's own global_req rule (src/Menu/MenuRole.php:122-133),
 * read from source, against the real front_office.json. Read-only: it never
 * touches the database and never writes a global.
 */

declare(strict_types=1);

$json = $argv[1] ?? 'G:/My Drive/OpenEMR/interface/main/tabs/menu/menus/front_office.json';
echo "target: {$json}\n";

$tree = json_decode((string) file_get_contents($json), false, 512, JSON_THROW_ON_ERROR);

/** MenuRole::menuApplyRestrictions global_req rule, transcribed exactly. */
function includeEntry(object $e, array $globals): bool
{
    if (empty($e->global_req)) {
        return true;
    }
    $req = (string) $e->global_req;
    if (str_starts_with($req, '!')) {
        $name = substr($req, 1);
        // "If the setting is both set and true, then skip this entry"
        return !(array_key_exists($name, $globals) && $globals[$name]);
    }
    // "If the global isn't set at all, or if it is false then skip the entry"
    return array_key_exists($req, $globals) && (bool) $globals[$req];
}

function collect(array $nodes, array $globals, string $label): int
{
    $n = 0;
    foreach ($nodes as $e) {
        if (!includeEntry($e, $globals)) {
            continue;
        }
        if (($e->label ?? null) === $label) {
            $n++;
        }
        if (!empty($e->children)) {
            $n += collect($e->children, $globals, $label);
        }
    }
    return $n;
}

$fail = 0;
foreach ([1, 0] as $value) {
    $visible = collect($tree, ['full_new_patient_form' => $value], 'Add Patient');
    $ok = ($visible === 1);
    $fail += $ok ? 0 : 1;
    printf(
        "full_new_patient_form=%d  ->  \"Add Patient\" entries visible: %d   %s\n",
        $value,
        $visible,
        $ok ? 'PASS' : 'FAIL (expected exactly 1)'
    );
}

// Negative control: a label that must never appear, proving the collector can return 0.
$control = collect($tree, ['full_new_patient_form' => 1], 'No Such Menu Item');
printf("negative control (bogus label)      ->  entries visible: %d   %s\n", $control, $control === 0 ? 'PASS' : 'FAIL');
$fail += ($control === 0) ? 0 : 1;

echo $fail === 0 ? "\nALL PASS\n" : "\n{$fail} FAILURE(S)\n";
exit($fail === 0 ? 0 : 1);
