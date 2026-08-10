<?php

// The runtime plane may read the globals table and the local filesystem.
// Nothing here reaches the network, so the rule must stay silent.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Asset;

$siteDirectory = __DIR__;

file_get_contents($siteDirectory . '/documents/logos/tokens.css');
file_get_contents('/var/www/sites/default/documents/logos/tokens.css');
fopen($siteDirectory . '/documents/logos/primary.svg', 'rb');
