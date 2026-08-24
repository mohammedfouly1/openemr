<?php

/**
 * Exact-value decision for retiring a managed English translation override.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenAI
 * @copyright Copyright (c) 2026 OpenAI
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * Keeps cleanup idempotent and prevents a retired branding row from deleting a
 * tenant/operator customization that no longer equals the value this tool wrote.
 */
enum RetiredEnglishOverrideDecision
{
    case AlreadyAbsent;
    case DeleteManaged;
    case PreserveDifferent;

    public static function forDefinition(?string $existing, string $managed): self
    {
        if ($existing === null) {
            return self::AlreadyAbsent;
        }

        return hash_equals($managed, $existing)
            ? self::DeleteManaged
            : self::PreserveDifferent;
    }
}
