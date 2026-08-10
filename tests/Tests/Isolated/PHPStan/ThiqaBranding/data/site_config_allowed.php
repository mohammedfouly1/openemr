<?php

// Reading approved logo binaries from the per-site documents tree is
// permitted; only the config.php seam is prohibited.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Config;

$siteId = 'default';

$logoDirectory = 'sites/' . $siteId . '/documents/logos';
$themeDirectory = 'sites/default/documents/theme';
$globalsKey = 'thiqa_branding_revision';
$notTheSeam = 'sites/default/documents/branding/config.php.dist';
