<?php
// -----------------------------------------------------------------------------
// Source retrieved READ-ONLY from GitHub for evidence purposes.
// Package : openemr/oe-module-installer-plugin
// Version : 0.1.5
// Ref/SHA : 4803eb0e6ad07328bd0eac485d0b2ff9e075f9f6
// Source URL (composer.lock line 6545):
//   https://github.com/openemr/oe-module-installer-plugin.git
// Fetched via raw.githubusercontent.com on the date recorded in 22-command-log.txt.
// No credentials present in source URLs (verified — public repo, no auth used).
// DO NOT execute. This file is evidence-only.
// -----------------------------------------------------------------------------


// =============================================================================
// FILE 1 of 3 — src/Plugin.php  (blob SHA 0c8ea6bab24f2a5361456392d68cb975106bd9bd, 700 bytes)
// URL: https://raw.githubusercontent.com/openemr/oe-module-installer-plugin/4803eb0e6ad07328bd0eac485d0b2ff9e075f9f6/src/Plugin.php
// =============================================================================
/*
<?php

namespace OpenEMR\Composer\ModuleInstallerPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface {
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new CustomModuleInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);

        $installer = new ZendModuleInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
*/


// =============================================================================
// FILE 2 of 3 — src/CustomModuleInstaller.php  (blob SHA 84e45799179f23d132b9b98f58f4b0ac52ccba7c, 714 bytes)
// URL: https://raw.githubusercontent.com/openemr/oe-module-installer-plugin/4803eb0e6ad07328bd0eac485d0b2ff9e075f9f6/src/CustomModuleInstaller.php
// =============================================================================
/*
<?php

namespace OpenEMR\Composer\ModuleInstallerPlugin;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class CustomModuleInstaller extends LibraryInstaller
{
    public function getInstallPath(PackageInterface $package)
    {
        $packageName = $package->getPrettyName();
        $folderPaths = explode('/', $packageName);
        $moduleName = end($folderPaths);
        return 'interface' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'custom_modules'
            . DIRECTORY_SEPARATOR  . $moduleName;
    }

    public function supports($packageType)
    {
        return 'openemr-module' === $packageType;
    }
}
*/


// =============================================================================
// FILE 3 of 3 — src/ZendModuleInstaller.php  (blob SHA af16a92069c5f85e0fc7016249697cc1a486f056, 741 bytes)
// URL: https://raw.githubusercontent.com/openemr/oe-module-installer-plugin/4803eb0e6ad07328bd0eac485d0b2ff9e075f9f6/src/ZendModuleInstaller.php
// =============================================================================
/*
<?php

namespace OpenEMR\Composer\ModuleInstallerPlugin;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class ZendModuleInstaller extends LibraryInstaller
{
    public function getInstallPath(PackageInterface $package)
    {
        $packageName = $package->getPrettyName();
        $folderPaths = explode('/', $packageName);
        $moduleName = end($folderPaths);
        return 'interface' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'zend'
            . DIRECTORY_SEPARATOR  . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    }

    public function supports($packageType)
    {
        return 'openemr-zend-module' === $packageType;
    }
}
*/
