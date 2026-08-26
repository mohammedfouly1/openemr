<?php

/**
 * Bootstrap for the SkyEagle branding module.
 *
 * Registers the module namespace and hands control to Bootstrap, which attaches
 * the branding layer to OpenEMR through published extension points only. No core
 * file is modified by this module (locked Invariant 4).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Modules\SkyEagleBranding\Bootstrap;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Supplied by ModulesApplication::loadCustomModule().
 *
 * @var ModulesClassLoader $classLoader
 * @var EventDispatcherInterface $eventDispatcher
 */

$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\SkyEagleBranding\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);

(new Bootstrap($eventDispatcher, __DIR__))->register();
