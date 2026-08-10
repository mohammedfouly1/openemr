<?php

/**
 * A throwaway directory tree for the filesystem half of the materialisation tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/**
 * Atomicity is a property of a real filesystem — temp file, rename, displaced original —
 * so these tests use one rather than a virtual filesystem. The tree is created per test
 * and removed afterwards, and `filesUnder()` is what lets a test assert the *absence* of
 * a partial write, which is the actual claim being made.
 */
trait TemporaryTreeTrait
{
    private string $root = '';

    /**
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    private function makeTree(): string
    {
        $root = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/')
            . '/thiqa-branding-' . bin2hex(random_bytes(8));

        if (!mkdir($root, 0o777, true) && !is_dir($root)) {
            throw new RuntimeException('Unable to create the temporary branding tree.');
        }

        $this->root = $root;

        return $root;
    }

    /**
     * @codeCoverageIgnore Fixture wiring; runs after coverage attribution.
     */
    private function removeTree(): void
    {
        if ($this->root === '' || !is_dir($this->root)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS),
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

        @rmdir($this->root);
        $this->root = '';
    }

    /** The temporary root every relative path in filesUnder() is measured from. */
    private function treeRoot(): string
    {
        return $this->root;
    }

    /**
     * Every file under a directory, as paths relative to the temporary root, sorted.
     *
     * @return list<string>
     */
    private function filesUnder(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $found = [];
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($items as $item) {
            if ($item instanceof SplFileInfo && $item->isFile()) {
                $found[] = ltrim(
                    str_replace('\\', '/', substr($item->getPathname(), strlen($this->root))),
                    '/',
                );
            }
        }

        sort($found);

        return $found;
    }
}
