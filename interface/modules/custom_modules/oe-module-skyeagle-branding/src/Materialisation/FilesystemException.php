<?php

/**
 * A staging or placement step could not be completed on disk.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

use RuntimeException;

/**
 * Thrown by AtomicFileWriter, caught by BrandingMaterialiser, never surfaced to a user.
 *
 * Messages name the operation and the target path. Paths are server-side detail, which
 * is why the materialiser logs this exception through PSR-3 context and returns a
 * generic rejection line instead of `getMessage()`.
 */
final class FilesystemException extends RuntimeException
{
    public static function directoryNotCreatable(string $directory): self
    {
        return new self(sprintf('Cannot create branding directory "%s".', $directory));
    }

    public static function notADirectory(string $directory): self
    {
        return new self(sprintf('Branding path "%s" exists and is not a directory.', $directory));
    }

    public static function notWritten(string $path): self
    {
        return new self(sprintf('Cannot write staged branding file "%s".', $path));
    }

    public static function notRenamed(string $from, string $to): self
    {
        return new self(sprintf('Cannot rename "%s" to "%s".', $from, $to));
    }

    public static function notReadable(string $path): self
    {
        return new self(sprintf('Cannot read branding file "%s".', $path));
    }
}
