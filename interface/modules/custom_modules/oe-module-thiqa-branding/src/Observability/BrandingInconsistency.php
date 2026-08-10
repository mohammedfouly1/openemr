<?php

/**
 * A specific way a tenant's materialised branding can contradict itself.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Observability;

/**
 * The materialisation transaction (plan §4.4) makes three facts move together: the token
 * stylesheets on disk, the `saas_branding_materialised_at` stamp, and the
 * `saas_branding_revision` global written last. Any state where one of the three has moved
 * without the others means a run did not complete, or something outside the layer edited
 * one of them by hand.
 *
 * Each case is one such disagreement, named after what is present versus what is missing.
 * Backed by a string because the value is reported in machine-readable output and in log
 * context, where a stable identifier matters more than the prose.
 */
enum BrandingInconsistency: string
{
    /** The tenant's branding state could not be read at all. */
    case StateUnreadable = 'state_unreadable';

    /** The revision says a materialisation completed, but a token stylesheet is missing. */
    case RevisionWithoutStylesheet = 'revision_without_stylesheet';

    /** A token stylesheet exists, but the revision global still says nothing was materialised. */
    case StylesheetWithoutRevision = 'stylesheet_without_revision';

    /** The revision moved but no materialisation stamp was written alongside it. */
    case MissingMaterialisationStamp = 'missing_materialisation_stamp';

    /** A stamp is present but is not the ISO-8601 instant the materialiser writes. */
    case UnreadableMaterialisationStamp = 'unreadable_materialisation_stamp';

    /** A stamp is present while the revision still says nothing was materialised. */
    case StampWithoutRevision = 'stamp_without_revision';

    /** The stamp is ahead of the clock, so age and staleness cannot be trusted. */
    case StampInTheFuture = 'stamp_in_the_future';

    /**
     * One operator-facing line, safe for CLI output and for a log context array.
     *
     * No tenant data and no filesystem path appears in any of these: the report carries
     * the site id as its own field, and a path in a health-check line is an information
     * leak for no diagnostic gain.
     */
    public function description(): string
    {
        return match ($this) {
            self::StateUnreadable =>
                'The tenant\'s branding state could not be read.',
            self::RevisionWithoutStylesheet =>
                'A revision is recorded but at least one generated token stylesheet is missing.',
            self::StylesheetWithoutRevision =>
                'A generated token stylesheet exists but no revision is recorded.',
            self::MissingMaterialisationStamp =>
                'A revision is recorded but no materialisation timestamp was written with it.',
            self::UnreadableMaterialisationStamp =>
                'The materialisation timestamp is not a readable ISO-8601 instant.',
            self::StampWithoutRevision =>
                'A materialisation timestamp exists but no revision is recorded.',
            self::StampInTheFuture =>
                'The materialisation timestamp is in the future, so staleness cannot be measured.',
        };
    }
}
