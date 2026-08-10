<?php

/**
 * Verifies every entry in brand/manifests/SHA256SUMS against the working tree.
 *
 * This is check **V-06** of `docs/RebrandingPlan.md` §6.4, and part of the release gate
 * in §7.3. It exists as a script rather than a hand-run one-liner because V-06 was being
 * satisfied by ad-hoc verification, and an ad-hoc check is only as current as the last
 * person who remembered to run it.
 *
 * Two things worth knowing before you edit anything the manifest covers:
 *
 *  1. **The manifest covers documents, not just binaries.** 16 of its 123 entries are the
 *     `docs/branding-production/*.md` design documents. Editing one of those breaks the
 *     release gate exactly as surely as tampering with a logo would — which is the point,
 *     but it surprises people. `docs/RebrandingBugs.md` RB-25 records an instance where an
 *     uncommitted documentation edit left the gate red and nothing noticed.
 *  2. **Paths in the manifest are repo-root-relative**, so this must be run from the repo
 *     root (it resolves the root itself, so the working directory does not matter).
 *
 * When a mismatch is legitimate — you meant to change that document — re-issue the entry
 * rather than deleting it, and say so in the change record.
 *
 * Usage:
 *   php tools/branding/verify-brand-manifest.php            # verify, exit 1 on any problem
 *   php tools/branding/verify-brand-manifest.php --quiet    # only report problems
 *
 * Exit codes: 0 = every entry verifies; 1 = at least one missing/mismatched/unparsable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

$options = getopt('', ['quiet', 'help']);

if (isset($options['help'])) {
    fwrite(STDOUT, "Usage: php tools/branding/verify-brand-manifest.php [--quiet]\n");
    exit(0);
}

$quiet = isset($options['quiet']);
$repoRoot = dirname(__DIR__, 2);
$manifestPath = $repoRoot . '/brand/manifests/SHA256SUMS';

$raw = file($manifestPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($raw === false) {
    fwrite(STDERR, "Cannot read {$manifestPath}\n");
    exit(1);
}

$verified = 0;
$problems = [];

foreach ($raw as $lineNumber => $line) {
    // Accept both the plain `<hash>  <path>` form and coreutils' binary-mode `*<path>`.
    if (preg_match('/\A([0-9a-f]{64})\s+\*?(.+)\z/', trim($line), $matches) !== 1) {
        $problems[] = sprintf('line %d: unparsable manifest entry', $lineNumber + 1);
        continue;
    }

    [, $expected, $relativePath] = $matches;
    $absolutePath = $repoRoot . '/' . $relativePath;

    if (!is_file($absolutePath)) {
        $problems[] = $relativePath . ' (missing)';
        continue;
    }

    $actual = hash_file('sha256', $absolutePath);
    if ($actual === false) {
        $problems[] = $relativePath . ' (unreadable)';
        continue;
    }

    if (!hash_equals($expected, $actual)) {
        $problems[] = sprintf('%s (mismatch: expected %s, got %s)', $relativePath, $expected, $actual);
        continue;
    }

    $verified++;
}

$total = count($raw);

if ($problems === []) {
    if (!$quiet) {
        echo "brand/manifests/SHA256SUMS: {$verified}/{$total} verified.", PHP_EOL;
    }
    exit(0);
}

fwrite(STDERR, "brand/manifests/SHA256SUMS: {$verified}/{$total} verified, " . count($problems) . " problem(s):\n");
foreach ($problems as $problem) {
    fwrite(STDERR, '  ' . $problem . PHP_EOL);
}
fwrite(
    STDERR,
    "\nIf a change was intentional, re-issue that entry's hash and record why. "
    . "Do not delete the entry.\n"
);

exit(1);
