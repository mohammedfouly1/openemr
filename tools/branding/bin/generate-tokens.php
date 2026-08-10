#!/usr/bin/env php
<?php

/**
 * CLI entry point for the deterministic Thiqa branding token generator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

require_once dirname(__DIR__) . '/autoload.php';

/**
 * Reads brand/tokens/thiqa-tokens.json and brand/typography/*, writes the SCSS
 * token partials, the CSS custom-property partial, the typography partial and
 * both SMART style templates. Output is byte-identical across runs: no
 * timestamps, no absolute paths, no environment reads, LF endings throughout.
 *
 * Exit codes: 0 success, 1 token/validation failure, 2 bad usage,
 * 3 --check found drift.
 *
 * @param array<array-key, mixed> $rawArgv
 */
$run = static function (array $rawArgv): int {
    $arguments = [];
    foreach (array_slice(array_values($rawArgv), 1) as $argument) {
        if (is_scalar($argument)) {
            $arguments[] = (string) $argument;
        }
    }

    // dirname(__DIR__, 3) is <repo>/tools/branding/bin -> <repo>. Derived from the
    // script location rather than the cwd so the default is stable wherever it runs.
    $defaultRepoRoot = dirname(__DIR__, 3);

    try {
        $options = CliOptions::parse($arguments, $defaultRepoRoot);
    } catch (GeneratorException $e) {
        fwrite(STDERR, $e->getMessage() . "\n");
        fwrite(STDERR, CliOptions::usage());

        return 2;
    }

    if ($options->help) {
        fwrite(STDOUT, CliOptions::usage());

        return 0;
    }

    try {
        $files = (new TokenGenerator($options->repoRoot, $options->fontUrlBase))->generate();
        $writer = new OutputWriter($options->outputDirectory);

        if ($options->check) {
            // Two checks, deliberately. verify() covers the preview copies; DeployedArtefacts
            // covers the files the application actually loads. Checking only the preview left
            // the real theme SCSS and the SMART contract unguarded, which is what audit
            // finding F-04 recorded.
            $drifted = array_merge(
                $writer->verify($files),
                (new DeployedArtefacts($options->repoRoot))->verify($files),
            );

            if ($drifted !== []) {
                fwrite(STDERR, "Generated branding artefacts are out of date:\n");
                foreach ($drifted as $entry) {
                    fwrite(STDERR, '  ' . $entry . "\n");
                }
                fwrite(STDERR, "Re-run " . GeneratedHeader::GENERATOR . " and commit the result.\n");

                return 3;
            }

            fwrite(STDOUT, sprintf(
                "%d branding artefacts are up to date (%d preview, %d deployed).\n",
                count($files) + count(DeployedArtefacts::deployedPaths()),
                count($files),
                count(DeployedArtefacts::deployedPaths()),
            ));

            return 0;
        }

        $writer->write($files);
    } catch (GeneratorException $e) {
        fwrite(STDERR, 'Branding token generation failed: ' . $e->getMessage() . "\n");

        return 1;
    }

    foreach ($files as $file) {
        fwrite(STDOUT, sprintf("%s  %s\n", $file->sha256(), $file->name));
    }
    fwrite(STDOUT, sprintf("Wrote %d artefacts to %s\n", count($files), $options->outputDirectory));

    return 0;
};

if (!isset($argv)) {
    fwrite(STDERR, "register_argc_argv must be enabled to run this script.\n");

    exit(2);
}

exit($run($argv));
