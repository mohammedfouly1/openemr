<?php

/**
 * The do-not-edit banner shared by every generated artefact.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * Carries no timestamp, no absolute path and no host detail, because CI
 * re-runs the generator and compares bytes.
 */
final readonly class GeneratedHeader
{
    private const RULE = '// -----------------------------------------------------------------------------';

    public const GENERATOR = 'tools/branding/bin/generate-tokens.php';

    /**
     * @param list<string> $sources repo-relative token sources for this artefact
     */
    public static function scss(string $summary, array $sources): string
    {
        $lines = [
            self::RULE,
            '// GENERATED FILE — DO NOT EDIT.',
            '//',
            '// ' . $summary,
            '//',
            '// Produced by ' . self::GENERATOR . ' from:',
        ];
        foreach ($sources as $source) {
            $lines[] = '//   ' . $source;
        }
        $lines[] = '//';
        $lines[] = '// Change the token sources and re-run the generator. CI re-runs it and fails on';
        $lines[] = '// any diff, so an edit made here cannot survive.';
        $lines[] = self::RULE;

        return implode("\n", $lines) . "\n";
    }
}
