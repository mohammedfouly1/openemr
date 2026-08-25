<?php

// PHP method names are case-insensitive, so the guardrail must match them
// case-insensitively too.

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Listener;

use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader();

$loader->PREPENDPATH(__DIR__);
$loader->AddPath(__DIR__);
