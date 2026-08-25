<?php

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Listener;

use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader();
$directory = __DIR__;

$loader->prependPath($directory);
$loader->prependPath($directory, 'oe-module-skyeagle-branding');
$loader->addPath($directory);
