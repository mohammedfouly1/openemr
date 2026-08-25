<?php

/**
 * A throwaway `sites/` tree for the tenant-inventory tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Tenant;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/**
 * The inventory is a filesystem scanner, so it is tested against a real filesystem.
 *
 * `sqlconfFor()` reproduces the exact shape `Installer::write_configuration_file()` emits,
 * credentials and all — the credentials matter, because one of the tests asserts that a
 * password present in the fixture never reaches the rendered output.
 */
trait SitesFixtureTrait
{
    private string $sitesRoot = '';

    /**
     * Builds a `sites/` directory.
     *
     * Each entry maps a directory name to what that site's `sqlconf.php` should contain:
     * an `int` writes a normal installer-shaped file with that `$config` value, a `string`
     * is written verbatim, and `null` creates the directory with no `sqlconf.php` at all.
     *
     * @param  array<string, int|string|null> $sites
     *
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    private function makeSites(array $sites): string
    {
        $root = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/')
            . '/thiqa-sites-' . bin2hex(random_bytes(8));

        if (!mkdir($root, 0o777, true) && !is_dir($root)) {
            throw new RuntimeException('Unable to create the temporary sites tree.');
        }

        $this->sitesRoot = $root;

        foreach ($sites as $name => $contents) {
            $directory = $root . '/' . $name;
            if (!mkdir($directory, 0o777, true) && !is_dir($directory)) {
                throw new RuntimeException('Unable to create the temporary site directory.');
            }

            if ($contents === null) {
                continue;
            }

            file_put_contents(
                $directory . '/sqlconf.php',
                is_int($contents) ? $this->sqlconfFor($name, $contents) : $contents,
            );
        }

        return $root;
    }

    /** The password written into every generated fixture; must never appear in output. */
    private function fixturePassword(): string
    {
        return 'n0t-f0r-pr1nt1ng';
    }

    /** Byte-for-byte the shape Installer::write_configuration_file() produces. */
    private function sqlconfFor(string $site, int $config): string
    {
        return "<?php\n"
            . "//  OpenEMR\n"
            . "//  MySQL Config\n\n"
            . "\$host\t= '127.0.0.1';\n"
            . "\$port\t= '3306';\n"
            . "\$login\t= 'openemr';\n"
            . "\$pass\t= '" . $this->fixturePassword() . "';\n"
            . "\$dbase\t= 'openemr_" . $site . "';\n"
            . "\n\$sqlconf = array();\nglobal \$sqlconf;\n"
            . "\$sqlconf[\"host\"]= \$host;\n"
            . "\$sqlconf[\"port\"] = \$port;\n"
            . "\$sqlconf[\"login\"] = \$login;\n"
            . "\$sqlconf[\"pass\"] = \$pass;\n"
            . "\$sqlconf[\"dbase\"] = \$dbase;\n\n"
            . "//////////////////////////\n"
            . "//////DO NOT TOUCH THIS///\n"
            . "\$config = " . $config . "; /////////////\n"
            . "//////////////////////////\n"
            . "?>\n";
    }

    /**
     * @codeCoverageIgnore Fixture wiring; runs after coverage attribution.
     */
    private function removeSites(): void
    {
        if ($this->sitesRoot === '' || !is_dir($this->sitesRoot)) {
            $this->sitesRoot = '';

            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->sitesRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if (!$item instanceof SplFileInfo) {
                continue;
            }

            if ($item->isDir()) {
                @rmdir($item->getPathname());
                continue;
            }

            @unlink($item->getPathname());
        }

        @rmdir($this->sitesRoot);
        $this->sitesRoot = '';
    }
}
