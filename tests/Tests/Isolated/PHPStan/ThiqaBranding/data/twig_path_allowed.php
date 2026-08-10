<?php

// A namespaced addPath() is the one approved registration form (Q38/CR-17).

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Listener;

use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader();
$directory = __DIR__;

$loader->addPath($directory, 'oe-module-thiqa-branding');
$loader->addPath($directory, namespace: 'oe-module-thiqa-branding');
$loader->getPaths('oe-module-thiqa-branding');
