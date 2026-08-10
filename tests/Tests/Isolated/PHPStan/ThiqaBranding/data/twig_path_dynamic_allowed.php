<?php

// A dynamic method name is not statically resolvable, a namespace supplied
// through a variable still satisfies the two-argument form, and unrelated
// loader methods are none of the rule's business.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Listener;

use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader();
$method = 'addPath';
$templateNamespace = 'oe-module-thiqa-branding';

$loader->$method(__DIR__);
$loader->addPath(__DIR__, $templateNamespace);
$loader->setPaths([__DIR__], $templateNamespace);
