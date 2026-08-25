<?php

/**
 * Verifies the artefacts the application actually loads, not just the preview copies.
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
 * `OutputWriter::verify()` compares the generator's output against
 * `tools/branding/output-preview/`, which nothing at runtime reads. The files the product
 * actually loads live in the theme directory and inside the branding module, and until this
 * class existed **nothing checked them** -- a token edit could leave the deployed SCSS and
 * the SMART contract stale with no failure, while the SMART templates' own headers claimed
 * "the generator is the authority and CI fails on a diff".
 *
 * Two artefact kinds, compared differently:
 *
 *  - **SCSS** is byte-compared. The deployed file is the generator's output verbatim.
 *  - **Twig** is compared on payload only, with the leading `{#- … -#}` comment stripped
 *    from both sides. The deployed SMART templates deliberately carry a fuller header than
 *    the preview banner -- the Q38/CR-17 rationale, the wire-format note -- and that prose
 *    is worth keeping. What must not drift is the JSON body.
 */
final readonly class DeployedArtefacts
{
    /**
     * Generated artefact name => repo-relative path the application loads it from.
     */
    private const DEPLOYMENT_MAP = [
        '_tokens-light.scss' => 'interface/themes/thiqa/_tokens-light.scss',
        '_tokens-dark.scss' => 'interface/themes/thiqa/_tokens-dark.scss',
        '_css-variables.scss' => 'interface/themes/thiqa/_css-variables.scss',
        '_typography.scss' => 'interface/themes/thiqa/_typography.scss',
        'smart-style_light.json.twig' =>
            'interface/modules/custom_modules/oe-module-skyeagle-branding/templates/api/smart/smart-style_light.json.twig',
        'smart-style_dark.json.twig' =>
            'interface/modules/custom_modules/oe-module-skyeagle-branding/templates/api/smart/smart-style_dark.json.twig',
    ];

    public function __construct(private string $repoRoot)
    {
    }

    /**
     * @param list<GeneratedFile> $files
     *
     * @return list<string> deployed paths that are missing, unreadable or stale
     */
    public function verify(array $files): array
    {
        $drifted = [];

        foreach ($files as $file) {
            $relative = self::DEPLOYMENT_MAP[$file->name] ?? null;
            if ($relative === null) {
                // An artefact with no deployed destination is preview-only by design.
                continue;
            }

            $target = rtrim($this->repoRoot, "/\\") . '/' . $relative;

            if (!is_file($target)) {
                $drifted[] = $relative . ' (missing)';
                continue;
            }

            $onDisk = file_get_contents($target);
            if ($onDisk === false) {
                $drifted[] = $relative . ' (unreadable)';
                continue;
            }

            $comparable = str_ends_with($file->name, '.twig')
                ? self::payloadOf(...)
                : static fn (string $contents): string => $contents;

            if ($comparable($onDisk) !== $comparable($file->contents)) {
                $drifted[] = $relative . ' (differs from generated output)';
            }
        }

        return $drifted;
    }

    /**
     * The document with any leading Twig comment removed and edge whitespace trimmed.
     */
    private static function payloadOf(string $contents): string
    {
        return trim((string) preg_replace('/\{#-?.*?-?#\}/s', '', $contents, 1));
    }

    /**
     * @return list<string> the repo-relative paths this class guards
     */
    public static function deployedPaths(): array
    {
        return array_values(self::DEPLOYMENT_MAP);
    }
}
