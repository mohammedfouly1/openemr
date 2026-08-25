<?php

/**
 * Registers the Thiqa branding module's PSR-4 prefix for isolated tests.
 *
 * The module lives under interface/modules/custom_modules and is not in the root
 * composer.json autoload map; at runtime the module manager registers the prefix when
 * the module is enabled in the database. The isolated suite has no database, so the
 * tests register it themselves before touching any module class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config;

use Composer\Autoload\ClassLoader;

trait ModuleAutoloadTrait
{
    private const MODULE_SOURCE = '/interface/modules/custom_modules/oe-module-skyeagle-branding/src/';

    /**
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    public static function registerModuleAutoload(): void
    {
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }

        $loader->addPsr4(
            'OpenEMR\\Modules\\SkyEagleBranding\\',
            dirname(__DIR__, 6) . self::MODULE_SOURCE
        );
    }
}
