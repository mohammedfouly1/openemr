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

namespace OpenEMR\Modules\SkyEagleBranding\Observability;

/**
 * The materialisation transaction (plan §4.4) makes several facts move together: the
 * tenant token overlay, the `saas_branding_materialised_at` stamp, the
 * `saas_branding_revision` global written last, and the generated token stylesheets on
 * disk. Any state where one has moved without the others means a run did not complete, or
 * something outside the layer edited one of them by hand.
 *
 * Each case is one such disagreement, named after what is present versus what is missing.
 * Backed by a string because the value is reported in machine-readable output and in log
 * context, where a stable identifier matters more than the prose. **The existing values
 * are wire identifiers and do not change** — S2-P1-18 reclassified two of them by plane
 * rather than renaming them, so any monitor already matching on `stylesheet_without_revision`
 * keeps working and merely stops seeing it under a failing status.
 *
 * Every case carries a BrandingObservationPlane, and that is what decides severity: a
 * served-plane case changes what a user sees and fails the check; a static-artefact case
 * describes generated files no page links (D-8) and is reported as an advisory.
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
     * A tenant overlay is being served while the revision says nothing was materialised.
     *
     * The revision is the overlay's cache key, so this state serves tenant colours under
     * `?rev=0` — the value a never-materialised tenant also uses. The overlay reached the
     * globals outside the materialisation transaction.
     */
    case OverlayWithoutRevision = 'overlay_without_revision';

    /**
     * An overlay global holds something, but nothing renderable comes out of it.
     *
     * TokenOverlay::fromJson() is deliberately total — a malformed overlay degrades to the
     * Tier 1 palette rather than breaking the login page — so without this case the tenant
     * silently renders product defaults while the record says an overlay is applied. That
     * silence is the fault: the degradation is correct, being unable to see it is not.
     */
    case UnrenderableTokenOverlay = 'unrenderable_token_overlay';

    /**
     * Which delivery plane this finding is about, and therefore whether it can fail a
     * health check. Exhaustive match with no default branch: a new case must state its
     * plane or static analysis fails, which is the point.
     */
    public function plane(): BrandingObservationPlane
    {
        return match ($this) {
            self::StateUnreadable,
            self::MissingMaterialisationStamp,
            self::UnreadableMaterialisationStamp,
            self::StampWithoutRevision,
            self::StampInTheFuture,
            self::OverlayWithoutRevision,
            self::UnrenderableTokenOverlay => BrandingObservationPlane::Served,
            self::RevisionWithoutStylesheet,
            self::StylesheetWithoutRevision => BrandingObservationPlane::StaticArtefact,
        };
    }

    /**
     * One operator-facing line, safe for CLI output and for a log context array.
     *
     * No tenant data and no filesystem path appears in any of these: the report carries
     * the site id as its own field, and a path in a health-check line is an information
     * leak for no diagnostic gain.
     *
     * The two static-artefact lines say outright that the files are not served. An
     * operator who reads "a token stylesheet is missing" and does not know that nothing
     * loads it will go looking for a rendering fault that cannot exist.
     */
    public function description(): string
    {
        return match ($this) {
            self::StateUnreadable =>
                'The tenant\'s branding state could not be read.',
            self::RevisionWithoutStylesheet =>
                'A revision is recorded but at least one generated token stylesheet is missing '
                    . 'from disk. These files are not served to any page, so rendering is unaffected.',
            self::StylesheetWithoutRevision =>
                'A generated token stylesheet is on disk but no revision is recorded. These files '
                    . 'are not served to any page, so rendering is unaffected.',
            self::MissingMaterialisationStamp =>
                'A revision is recorded but no materialisation timestamp was written with it.',
            self::UnreadableMaterialisationStamp =>
                'The materialisation timestamp is not a readable ISO-8601 instant.',
            self::StampWithoutRevision =>
                'A materialisation timestamp exists but no revision is recorded.',
            self::StampInTheFuture =>
                'The materialisation timestamp is in the future, so staleness cannot be measured.',
            self::OverlayWithoutRevision =>
                'A tenant token overlay is being served but no revision is recorded, so it is '
                    . 'served under the cache key of a tenant that has none.',
            self::UnrenderableTokenOverlay =>
                'A tenant token overlay is stored but nothing renderable parses out of it, so the '
                    . 'tenant is silently rendering the product palette.',
        };
    }
}
