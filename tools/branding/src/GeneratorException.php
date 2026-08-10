<?php

/**
 * Raised when the branding token generator cannot produce complete, correct output.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

use RuntimeException;

/**
 * The generator never emits a partial file: any missing, malformed or unknown
 * token aborts the whole run with one of these.
 */
final class GeneratorException extends RuntimeException
{
}
