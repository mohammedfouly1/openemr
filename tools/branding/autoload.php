<?php

/**
 * Standalone PSR-4 autoloader for the OpenEMR\Branding\ generator classes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

/*
 * The generator must run from a bare checkout, before `composer install` has
 * had a chance to complete, so it cannot depend on vendor/autoload.php. Once
 * `"OpenEMR\\Branding\\": "tools/branding/src"` is added to composer.json's
 * autoload-dev section this file becomes a harmless no-op: the guard below
 * skips registration when the classes already resolve.
 */

if (!class_exists(\OpenEMR\Branding\TokenGenerator::class, true)) {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'OpenEMR\\Branding\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $file = __DIR__ . '/src/' . strtr($relative, '\\', '/') . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}
