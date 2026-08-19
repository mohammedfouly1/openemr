<?php

/**
 * Fast, targeted Twig compile-only check for a single template.
 *
 * Verifies syntax and filter/function/test references WITHOUT rendering
 * (which triggers this host's documented session-lock hang, CLAUDE.local.md
 * §9) and WITHOUT the full PHPUnit isolated suite's TwigTemplateCompilationTest
 * (which compiles every .twig file in the app and can take 20+ minutes on
 * this Drive-mounted host, per the 2026-08-19 orchestrator session that
 * wrote this file).
 *
 * Usage: php twig-compile-only-check.php <repo-relative-template-path>
 * Example: php twig-compile-only-check.php templates/product_registration/product_reg.js.twig
 */

declare(strict_types=1);

const FILEROOT = 'G:/My Drive/OpenEMR';

require FILEROOT . '/vendor/autoload.php';

$GLOBALS['fileroot'] = FILEROOT;
$GLOBALS['date_display_format'] = 0;

use OpenEMR\Common\Twig\TwigContainer;
use Twig\Source;
use Twig\TwigFunction;

$relative = $argv[1] ?? null;
if ($relative === null) {
    fwrite(STDERR, "Usage: php twig-compile-only-check.php <repo-relative-template-path>\n");
    exit(2);
}

$twigContainer = new TwigContainer();
$twig = $twigContainer->getTwig();
// Stub registered dynamically by C_EncounterVisitForm at runtime; needed for
// some form templates to compile standalone. Harmless no-op here.
$twig->addFunction(new TwigFunction('displayOptionClass', fn () => ''));

$path = FILEROOT . '/' . ltrim($relative, '/');
$code = file_get_contents($path);
if ($code === false) {
    fwrite(STDERR, "Could not read: $path\n");
    exit(2);
}

// Twig template name resolution mirrors TwigTemplateCompilationTest: strip
// the templates/ prefix if present, otherwise pass the relative path as-is
// (works for this harness's purpose — the loader only needs a name for
// error messages here, since compileSource() takes the source directly).
$templateName = str_starts_with($relative, 'templates/')
    ? substr($relative, strlen('templates/'))
    : $relative;

$source = new Source($code, $templateName, $path);

try {
    $twig->compileSource($source);
    echo "COMPILE OK: $relative\n";
} catch (\Throwable $e) {
    echo "COMPILE FAILED: $relative -- " . $e->getMessage() . "\n";
    exit(1);
}
