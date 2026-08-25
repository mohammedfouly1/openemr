<?php

// Near misses that merely contain the substring "config.php" without naming
// the prohibited seam. Flagging these would make the rule unusable.

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Loader;

$ownFileName = 'myconfig.php';
$distTemplate = 'config.php.dist';
$longerWord = 'sites/default/configuration.php';
$otherExtension = 'config.phpx';
$unrelated = 'sites/default/documents/logos/primary.svg';
