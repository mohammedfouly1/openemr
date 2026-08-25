<?php

/**
 * Why a candidate tenant logo was refused.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\AssetIntake;

/**
 * Backed because the reason is reported to the Control Plane and written to structured
 * logs (plan section 4.3, WP-2.12), so the wire value has to be stable.
 *
 * Every summary is a fixed operator-facing sentence built from no untrusted input, so a
 * rejection can be logged or shown without any further escaping.
 */
enum AssetRejectionReason: string
{
    /** The source path is missing, unreadable, or vanished between stat and read. */
    case UnreadableSource = 'unreadable_source';

    /** Zero bytes on disk. */
    case EmptyFile = 'empty_file';

    /** Over the per-slot byte cap. */
    case TooLarge = 'too_large';

    /** Too small to contain even a header for any permitted format. */
    case TooSmall = 'too_small';

    /** The leading bytes match no permitted format. */
    case UnsupportedFormat = 'unsupported_format';

    /** The declared filename extension disagrees with the actual bytes. */
    case ExtensionMismatch = 'extension_mismatch';

    /** The bytes satisfy more than one format signature: a polyglot. */
    case AmbiguousFormat = 'ambiguous_format';

    /** Bytes remain after the image payload's own terminator. */
    case TrailingData = 'trailing_data';

    /** The container structure does not parse as the format it claims. */
    case MalformedImage = 'malformed_image';

    /** A script or interpreter marker was found inside the image payload. */
    case EmbeddedCode = 'embedded_code';

    /** The pixel dimensions are not the ones the slot's manifest entry certifies. */
    case DimensionMismatch = 'dimension_mismatch';

    /** The slot fixes its filename, and this format cannot produce that name. */
    case SlotFormatNotPermitted = 'slot_format_not_permitted';

    /** The SVG is not well-formed XML. */
    case SvgNotWellFormed = 'svg_not_well_formed';

    /** The SVG carries a DOCTYPE or entity declaration: the XXE vector. */
    case SvgDoctype = 'svg_doctype';

    /** The SVG contains a script element. */
    case SvgScript = 'svg_script';

    /** The SVG carries an inline event-handler attribute. */
    case SvgEventHandler = 'svg_event_handler';

    /** The SVG contains foreignObject, which reintroduces arbitrary HTML. */
    case SvgForeignObject = 'svg_foreign_object';

    /** The SVG points at something outside itself, or at a scripting URI. */
    case SvgExternalReference = 'svg_external_reference';

    /** The SVG uses an element outside the path-only allowlist. */
    case SvgDisallowedElement = 'svg_disallowed_element';

    /** The SVG contains an XML processing instruction, e.g. a smuggled <?php block. */
    case SvgProcessingInstruction = 'svg_processing_instruction';

    /** The SVG declares no usable width/height or viewBox. */
    case SvgNoDimensions = 'svg_no_dimensions';

    /** The SVG root permits non-uniform scaling, so a renderer may deform the mark. */
    case SvgAspectRatioNotPreserved = 'svg_aspect_ratio_not_preserved';

    /** The SVG root carries no usable viewBox, so it cannot be scaled into a slot. */
    case SvgViewBoxMissing = 'svg_view_box_missing';

    /** The SVG root's declared width/height contradict its viewBox aspect ratio. */
    case SvgAspectRatioConflict = 'svg_aspect_ratio_conflict';

    public function summary(): string
    {
        return match ($this) {
            self::UnreadableSource => 'Candidate file could not be read.',
            self::EmptyFile => 'Candidate file is empty.',
            self::TooLarge => 'Candidate file exceeds the byte cap for this slot.',
            self::TooSmall => 'Candidate file is too small to be a valid image.',
            self::UnsupportedFormat => 'File content is not PNG, SVG, GIF or ICO.',
            self::ExtensionMismatch => 'Filename extension disagrees with the actual file content.',
            self::AmbiguousFormat => 'File content satisfies more than one image format.',
            self::TrailingData => 'Extra data follows the end of the image payload.',
            self::MalformedImage => 'Image container structure is invalid.',
            self::EmbeddedCode => 'Image payload contains a script or interpreter marker.',
            self::DimensionMismatch => 'Pixel dimensions do not match the certified size for this slot.',
            self::SlotFormatNotPermitted => 'This slot does not accept the supplied image format.',
            self::SvgNotWellFormed => 'SVG is not well-formed XML.',
            self::SvgDoctype => 'SVG declares a DOCTYPE or entity, which is never permitted.',
            self::SvgScript => 'SVG contains a script element.',
            self::SvgEventHandler => 'SVG contains an inline event-handler attribute.',
            self::SvgForeignObject => 'SVG contains a foreignObject element.',
            self::SvgExternalReference => 'SVG references a resource outside itself.',
            self::SvgDisallowedElement => 'SVG uses an element outside the permitted drawing allowlist.',
            self::SvgProcessingInstruction => 'SVG contains an XML processing instruction.',
            self::SvgNoDimensions => 'SVG declares neither usable width/height nor a viewBox.',
            self::SvgAspectRatioNotPreserved
                => 'SVG root permits non-uniform scaling, which would deform the logo.',
            self::SvgViewBoxMissing => 'SVG root declares no usable viewBox to scale from.',
            self::SvgAspectRatioConflict
                => 'SVG root declares a width and height that contradict its viewBox aspect ratio.',
        };
    }

    /**
     * True when the reason indicates deliberate hostility rather than a mistake.
     *
     * Used to pick the log level: an operator sending a 900x400 logo is noise, an
     * operator sending an SVG with an onload handler is an incident.
     */
    public function isHostile(): bool
    {
        return match ($this) {
            self::AmbiguousFormat,
            self::TrailingData,
            self::EmbeddedCode,
            self::SvgDoctype,
            self::SvgScript,
            self::SvgEventHandler,
            self::SvgForeignObject,
            self::SvgExternalReference,
            self::SvgProcessingInstruction => true,
            self::UnreadableSource,
            self::EmptyFile,
            self::TooLarge,
            self::TooSmall,
            self::UnsupportedFormat,
            self::ExtensionMismatch,
            self::MalformedImage,
            self::DimensionMismatch,
            self::SlotFormatNotPermitted,
            self::SvgNotWellFormed,
            self::SvgDisallowedElement,
            self::SvgNoDimensions,
            // Geometry defects are production mistakes, not attacks: an SVG exported with
            // preserveAspectRatio="none" is a tool's default, not an operator's intent.
            self::SvgAspectRatioNotPreserved,
            self::SvgViewBoxMissing,
            self::SvgAspectRatioConflict => false,
        };
    }
}
