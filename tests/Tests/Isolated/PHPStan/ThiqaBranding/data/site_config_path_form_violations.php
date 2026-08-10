<?php

// The seam is the same file however it is spelled: backslash separators, a
// query or fragment tail, and mixed case all name sites/<site>/config.php.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Loader;

$windowsPath = 'sites\\default\\config.php';
$queryTail = 'sites/default/config.php?site=default';
$fragmentTail = 'sites/default/config.php#branding';
$mixedCase = 'sites/default/Config.PHP';
