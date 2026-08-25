<?php

// Core OpenEMR and other modules keep their existing Twig loader calls; the
// rule is scoped to the branding module namespace only.

declare(strict_types=1);

namespace OpenEMR\Common\Twig\Example;

use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader();
$directory = __DIR__;

$loader->prependPath($directory);
$loader->addPath($directory);
