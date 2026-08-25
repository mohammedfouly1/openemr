#!/usr/bin/env php
<?php

/**
 * CLI entry point for the pre-database product identity artefact generator.
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
 * Reads the authoritative branding profile and writes `library/product_identity.generated.php`,
 * the file `setup.php`, `interface/globals.php` and `library/globals.inc.php` read the product
 * name and product URLs from before any database exists.
 *
 * Output is byte-identical across runs: no timestamps, no absolute paths, no environment
 * reads, LF endings throughout. `--check` is what CI runs; it writes nothing and exits 3
 * when the artefact on disk has drifted from the profile.
 *
 * Exit codes: 0 success, 1 profile/validation failure, 2 bad usage, 3 --check found drift.
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

    // dirname(__DIR__, 3) is <repo>/tools/branding/bin -> <repo>. Derived from the script
    // location rather than the cwd so the default is stable wherever it runs.
    $defaultRepoRoot = dirname(__DIR__, 3);

    try {
        $options = ProductIdentityCliOptions::parse($arguments, $defaultRepoRoot);
    } catch (GeneratorException $e) {
        fwrite(STDERR, $e->getMessage() . "\n");
        fwrite(STDERR, ProductIdentityCliOptions::usage());

        return 2;
    }

    if ($options->help) {
        fwrite(STDOUT, ProductIdentityCliOptions::usage());

        return 0;
    }

    // The banner names the canonical repo-relative profile whenever the default is in use,
    // so the shipped artefact never records the path of the machine that generated it.
    $defaultProfile = $options->repoRoot . '/' . ProductIdentityGenerator::PROFILE_PATH;
    $label = $options->profilePath === $defaultProfile
        ? ProductIdentityGenerator::PROFILE_PATH
        : $options->profilePath;

    try {
        $files = (new ProductIdentityGenerator($options->profilePath, $label))->generate();
    } catch (GeneratorException $e) {
        fwrite(STDERR, 'Product identity generation failed: ' . $e->getMessage() . "\n");

        return 1;
    }

    $artefact = $files[0];

    if ($options->check) {
        $drift = static function () use ($options, $artefact): ?string {
            if (!is_file($options->outputFile)) {
                return 'missing';
            }
            $onDisk = file_get_contents($options->outputFile);
            if ($onDisk === false) {
                return 'unreadable';
            }

            return $onDisk === $artefact->contents ? null : 'differs from the generated output';
        };

        $reason = $drift();
        if ($reason !== null) {
            fwrite(STDERR, sprintf("Product identity artefact is out of date:\n  %s (%s)\n", $options->outputFile, $reason));
            fwrite(STDERR, 'Re-run ' . GeneratedHeader::PRODUCT_IDENTITY_GENERATOR . " and commit the result.\n");

            return 3;
        }

        fwrite(STDOUT, sprintf("Product identity artefact is up to date (%s).\n", $artefact->sha256()));

        return 0;
    }

    $directory = dirname($options->outputFile);
    if (!is_dir($directory) && !mkdir($directory, 0o755, true) && !is_dir($directory)) {
        fwrite(STDERR, sprintf("Output directory \"%s\" could not be created.\n", $directory));

        return 1;
    }

    if (file_put_contents($options->outputFile, $artefact->contents) === false) {
        fwrite(STDERR, sprintf("Product identity artefact \"%s\" could not be written.\n", $options->outputFile));

        return 1;
    }

    fwrite(STDOUT, sprintf("%s  %s\n", $artefact->sha256(), $options->outputFile));

    return 0;
};

if (!isset($argv)) {
    fwrite(STDERR, "register_argc_argv must be enabled to run this script.\n");

    exit(2);
}

exit($run($argv));
