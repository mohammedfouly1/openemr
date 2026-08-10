<?php

/**
 * PSR-4 shim for the Thiqa branding module inside the isolated suite.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

/*
 * oe-module-thiqa-branding carries its own composer.json and is installed by the
 * module installer plugin, so `OpenEMR\Modules\ThiqaBranding\` is not in the root
 * autoloader when the isolated suite runs from a plain checkout. Map the prefix by
 * hand, exactly as tests/Tests/Isolated/Modules/FaxSMS does.
 *
 * Included with require_once from each test file, so the registration happens once.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'OpenEMR\\Modules\\ThiqaBranding\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    $file = __DIR__
        . '/../../../../../../interface/modules/custom_modules/oe-module-thiqa-branding/src/'
        . $relative;

    if (is_file($file)) {
        require_once $file;
    }
});
