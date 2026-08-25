<?php

// A namespaced addPath() is the one approved registration form (Q38/CR-17).

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Listener;

use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader();
$directory = __DIR__;

$loader->addPath($directory, 'oe-module-skyeagle-branding');
$loader->addPath($directory, namespace: 'oe-module-skyeagle-branding');
$loader->getPaths('oe-module-skyeagle-branding');
