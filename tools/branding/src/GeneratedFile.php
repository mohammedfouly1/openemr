<?php

/**
 * One artefact produced by the branding token generator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * Contents are held in memory rather than streamed so a failure part-way
 * through a run leaves no half-written artefact on disk.
 */
final readonly class GeneratedFile
{
    public function __construct(public string $name, public string $contents)
    {
    }

    public function sha256(): string
    {
        return hash('sha256', $this->contents);
    }
}
