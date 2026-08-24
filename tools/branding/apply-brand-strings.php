<?php

/**
 * Applies the branding string catalogue (SET-TRANSLATION items) to one tenant.
 *
 * This is the mechanism `docs/rebranding.md` §16.2 assigns to BRAND-127, BRAND-128 and
 * BRAND-129, and that `docs/RebrandingPlan.md` §5.5 (WS-D) describes as "a reviewed EN/AR
 * constant->value list plus an idempotent import script". It exists because editing the
 * literal inside `xlt()` changes the catalogue KEY and orphans every translation of that
 * string -- see `docs/RebrandingBugs.md` RB-01, which measured 59 translations destroyed
 * that way.
 *
 * Three operations, all idempotent:
 *
 *  - `retired_english_overrides` -- remove only an exact lang_id=1 value previously
 *    managed by this catalogue. A missing row is already clean; a different value is
 *    operator/tenant data and is preserved. Constants and non-English definitions are
 *    never deleted.
 *
 *  - `english_overrides` -- write a lang_id=1 ("English (Standard)") definition for an
 *    existing constant. `xl()` consults lang_id=1 exactly like any other language
 *    (`library/translation.inc.php:39-77`; there is no English short-circuit), so this
 *    rebrands the English UI while leaving all other locales untouched.
 *  - `carry_forward` -- copy every existing translation from an old constant onto a new
 *    one, substituting the product proper noun. Used where a PATCH-classified item
 *    legitimately renamed a source literal that happened to be translated.
 *
 * Usage:
 *   php tools/branding/apply-brand-strings.php --site=default [--dry-run]
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

require_once __DIR__ . '/src/RetiredEnglishOverrideDecision.php';

/** English (Standard). OpenEMR seeds this language id in every install. */
const ENGLISH_LANG_ID = 1;

$options = getopt('', ['site:', 'dry-run', 'help']);

if (isset($options['help'])) {
    fwrite(STDOUT, "Usage: php tools/branding/apply-brand-strings.php --site=<site> [--dry-run]\n");
    exit(0);
}

$site = $options['site'] ?? null;
if (!is_string($site) || preg_match('/\A[A-Za-z0-9][A-Za-z0-9_.-]{0,62}\z/', $site) !== 1) {
    fwrite(STDERR, "The --site option is required and must be a valid site id. Branding tools never assume a tenant.\n");
    exit(2);
}

$dryRun = isset($options['dry-run']);

$repoRoot = dirname(__DIR__, 2);
$catalogue = $repoRoot . '/tools/branding/brand-strings.json';

$raw = file_get_contents($catalogue);
if ($raw === false) {
    fwrite(STDERR, "Cannot read {$catalogue}\n");
    exit(1);
}

/**
 * @var array{
 *     english_overrides: list<array<string, mixed>>,
 *     retired_english_overrides?: list<array<string, mixed>>,
 *     carry_forward: list<array<string, mixed>>
 * } $document
 */
$document = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

// Bootstrap OpenEMR far enough to get a tenant-scoped database handle. $ignoreAuth is
// required because this runs from the CLI with no session; the site is taken from --site
// and never from a request parameter.
$_GET['site'] = $site;
$ignoreAuth = true;
$sessionAllowWrite = true;
require_once $repoRoot . '/interface/globals.php';

$planned = [];
$applied = 0;
$skipped = 0;

/**
 * The cons_id for a constant, creating the constant row when it does not exist.
 */
function constantId(string $name, bool $dryRun): ?int
{
    $row = sqlQuery('SELECT cons_id FROM lang_constants WHERE constant_name = ? LIMIT 1', [$name]);
    if (!empty($row['cons_id'])) {
        return (int) $row['cons_id'];
    }

    if ($dryRun) {
        return null;
    }

    sqlStatement('INSERT INTO lang_constants (constant_name) VALUES (?)', [$name]);
    $created = sqlQuery('SELECT cons_id FROM lang_constants WHERE constant_name = ? LIMIT 1', [$name]);

    return empty($created['cons_id']) ? null : (int) $created['cons_id'];
}

echo 'Thiqa branding string catalogue', PHP_EOL;
echo 'Site:     ', $site, PHP_EOL;
echo 'Mode:     ', $dryRun ? 'DRY RUN (no writes)' : 'APPLY', PHP_EOL, PHP_EOL;

// ---------------------------------------------------------------------------
// 1. Retire obsolete managed English overrides
// ---------------------------------------------------------------------------
foreach ($document['retired_english_overrides'] ?? [] as $entry) {
    $constant = (string) $entry['constant'];
    $managedEnglish = (string) $entry['managed_english'];

    $constantRow = sqlQuery(
        'SELECT cons_id FROM lang_constants WHERE BINARY constant_name = ? LIMIT 1',
        [$constant]
    );
    if (empty($constantRow['cons_id'])) {
        echo "  OK    retired constant absent: {$constant}", PHP_EOL;
        $skipped++;
        continue;
    }

    $consId = (int) $constantRow['cons_id'];
    $existing = sqlQuery(
        'SELECT def_id, definition FROM lang_definitions WHERE cons_id = ? AND lang_id = ? LIMIT 1',
        [$consId, ENGLISH_LANG_ID]
    );
    $existingDefinition = empty($existing['def_id'])
        ? null
        : (string) ($existing['definition'] ?? '');

    $decision = RetiredEnglishOverrideDecision::forDefinition($existingDefinition, $managedEnglish);
    if ($decision === RetiredEnglishOverrideDecision::AlreadyAbsent) {
        echo "  OK    retired override already absent: {$constant}", PHP_EOL;
        $skipped++;
        continue;
    }

    if ($decision === RetiredEnglishOverrideDecision::PreserveDifferent) {
        echo "  KEEP  retired override differs from managed value: {$constant}", PHP_EOL;
        $skipped++;
        continue;
    }

    echo "  DELETE retired managed override: {$constant} -> {$managedEnglish}", PHP_EOL;
    $planned[] = "DELETE retired en: {$constant}";
    if ($dryRun) {
        continue;
    }

    sqlStatement(
        'DELETE FROM lang_definitions'
        . ' WHERE def_id = ? AND cons_id = ? AND lang_id = ? AND BINARY definition = ?',
        [(int) $existing['def_id'], $consId, ENGLISH_LANG_ID, $managedEnglish]
    );
    $remaining = sqlQuery(
        'SELECT def_id FROM lang_definitions WHERE def_id = ? LIMIT 1',
        [(int) $existing['def_id']]
    );
    if (!empty($remaining['def_id'])) {
        throw new \RuntimeException("Retired managed override was not deleted: {$constant}");
    }
    $applied++;
}

// ---------------------------------------------------------------------------
// 2. Active English overrides
// ---------------------------------------------------------------------------
foreach ($document['english_overrides'] as $entry) {
    $constant = (string) $entry['constant'];
    $english = (string) $entry['english'];

    $consId = constantId($constant, $dryRun);
    if ($consId === null) {
        echo "  SKIP  constant absent from lang_constants: {$constant}", PHP_EOL;
        $skipped++;
        continue;
    }

    $existing = sqlQuery(
        'SELECT def_id, definition FROM lang_definitions WHERE cons_id = ? AND lang_id = ? LIMIT 1',
        [$consId, ENGLISH_LANG_ID]
    );

    if (!empty($existing['def_id']) && ($existing['definition'] ?? null) === $english) {
        echo "  OK    already correct: {$constant} -> {$english}", PHP_EOL;
        $skipped++;
        continue;
    }

    $verb = empty($existing['def_id']) ? 'INSERT' : 'UPDATE';
    echo "  {$verb} {$constant} -> {$english}", PHP_EOL;
    $planned[] = "{$verb} en: {$constant}";

    if ($dryRun) {
        continue;
    }

    if (empty($existing['def_id'])) {
        sqlStatement(
            'INSERT INTO lang_definitions (cons_id, lang_id, definition) VALUES (?, ?, ?)',
            [$consId, ENGLISH_LANG_ID, $english]
        );
    } else {
        sqlStatement(
            'UPDATE lang_definitions SET definition = ? WHERE def_id = ?',
            [$english, (int) $existing['def_id']]
        );
    }

    $applied++;
}

// ---------------------------------------------------------------------------
// 3. Carry-forward of translations onto a renamed constant
// ---------------------------------------------------------------------------
foreach ($document['carry_forward'] as $entry) {
    $from = (string) $entry['from_constant'];
    $to = (string) $entry['to_constant'];
    /** @var array<string, string> $substitutions */
    $substitutions = $entry['substitute'] ?? [];

    $fromRow = sqlQuery('SELECT cons_id FROM lang_constants WHERE constant_name = ? LIMIT 1', [$from]);
    if (empty($fromRow['cons_id'])) {
        echo "  SKIP  source constant absent: {$from}", PHP_EOL;
        $skipped++;
        continue;
    }

    $toId = constantId($to, $dryRun);
    if ($toId === null) {
        echo "  PLAN  would create constant: {$to}", PHP_EOL;
        $planned[] = "CREATE constant: {$to}";
        continue;
    }

    $source = sqlStatement(
        'SELECT lang_id, definition FROM lang_definitions WHERE cons_id = ?',
        [(int) $fromRow['cons_id']]
    );

    while ($row = sqlFetchArray($source)) {
        $langId = (int) $row['lang_id'];
        $definition = (string) ($row['definition'] ?? '');
        if ($definition === '') {
            continue;
        }

        $rebranded = strtr($definition, $substitutions);

        $target = sqlQuery(
            'SELECT def_id, definition FROM lang_definitions WHERE cons_id = ? AND lang_id = ? LIMIT 1',
            [$toId, $langId]
        );

        if (!empty($target['def_id']) && ($target['definition'] ?? null) === $rebranded) {
            $skipped++;
            continue;
        }

        $verb = empty($target['def_id']) ? 'INSERT' : 'UPDATE';
        echo "  {$verb} {$to} [lang {$langId}] -> {$rebranded}", PHP_EOL;
        $planned[] = "{$verb} lang {$langId}: {$to}";

        if ($dryRun) {
            continue;
        }

        if (empty($target['def_id'])) {
            sqlStatement(
                'INSERT INTO lang_definitions (cons_id, lang_id, definition) VALUES (?, ?, ?)',
                [$toId, $langId, $rebranded]
            );
        } else {
            sqlStatement(
                'UPDATE lang_definitions SET definition = ? WHERE def_id = ?',
                [$rebranded, (int) $target['def_id']]
            );
        }

        $applied++;
    }
}

echo PHP_EOL;
echo $dryRun
    ? 'DRY RUN complete. ' . count($planned) . ' change(s) would be made, ' . $skipped . ' already correct.'
    : 'Applied ' . $applied . ' change(s); ' . $skipped . ' already correct.';
echo PHP_EOL;

exit(0);
