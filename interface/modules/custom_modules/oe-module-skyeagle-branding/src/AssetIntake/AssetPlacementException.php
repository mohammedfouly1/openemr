<?php

/**
 * A validated asset could not be written into the tenant's site directory.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\AssetIntake;

use RuntimeException;

/**
 * Distinct from a validation rejection. A rejection means "this file is not acceptable";
 * this means "the file was acceptable and the filesystem would not take it", which the
 * materialiser must treat as a retryable job failure that leaves revision n-1 intact
 * (plan section 4.4 step 6).
 *
 * Messages here describe the operation, never the underlying errno text, and are for
 * logs — a user sees a generic failure.
 */
final class AssetPlacementException extends RuntimeException
{
    public static function containmentBreach(): self
    {
        return new self('Resolved target path escapes the configured sites root.');
    }

    public static function unusableRoot(): self
    {
        return new self('Configured sites root does not resolve to a directory.');
    }

    public static function directoryNotCreated(): self
    {
        return new self('Could not create the slot directory beneath the site.');
    }

    public static function stagingFailed(): self
    {
        return new self('Could not stage the asset next to its target.');
    }

    public static function integrityFailed(): self
    {
        return new self('Staged bytes did not match the validated digest.');
    }

    public static function renameFailed(): self
    {
        return new self('Atomic rename of the staged asset into place failed.');
    }

    public static function illegalTargetName(): self
    {
        return new self('Derived target filename is outside the permitted set.');
    }
}
