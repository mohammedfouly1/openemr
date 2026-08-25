<?php

/**
 * The two places a product identity value may be read from in the branding profile.
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
 * Unit enum rather than a backed one: this is purely runtime dispatch inside the
 * generator. It is never persisted, serialised or exchanged, so a backing value would
 * only invite someone to write it into a file.
 */
enum ProductIdentitySourceKind
{
    /** A top-level member of the profile document, e.g. `product_name`. */
    case DocumentMember;

    /** The `value` of the `globals` list entry whose `key` matches. */
    case GlobalsRow;
}
