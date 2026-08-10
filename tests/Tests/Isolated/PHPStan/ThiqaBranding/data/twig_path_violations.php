<?php

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Listener;

use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader();
$directory = __DIR__;

$loader->prependPath($directory);
$loader->prependPath($directory, 'oe-module-thiqa-branding');
$loader->addPath($directory);
