<?php

/**
 * Allowlist inspection of a candidate SVG, which is XML and therefore active content.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\AssetIntake;

use DomainException;
use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use Throwable;

/**
 * An SVG is not a picture, it is a program's input. Inside one you can put `<script>`,
 * an `onload=` handler, a `<foreignObject>` containing arbitrary HTML, an `<image>` or
 * `xlink:href` pointing at a remote origin, and a DOCTYPE whose entities read local
 * files (XXE) or expand exponentially. Served same-origin from the tenant's own site
 * directory, every one of those runs with the tenant's session.
 *
 * The response is a strict allowlist, not a blocklist of known-bad strings. The
 * certified masters (docs/branding-production/02-svg-validation.md) contain exactly
 * `svg`, `metadata` and `path` elements, so an allowlist wide enough for
 * gradient-and-shape artwork already has generous headroom. Anything else is refused.
 *
 * Four independent layers, in order:
 *   1. Byte-level pre-checks, before any parser touches the input.
 *   2. A libxml parse with network access and external entity resolution disabled.
 *   3. A full DOM walk over elements, attributes and node kinds.
 *   4. A root-geometry check, {@see SvgGeometry}. Layers 1-3 answer "can this file hurt
 *      the tenant"; layer 4 answers "can this file hurt the mark". A logo that a renderer
 *      is free to stretch is a trademark-integrity defect even though it is perfectly
 *      safe, so it is refused at the same gate rather than at a parallel one.
 */
final class SvgInspector
{
    private const SVG_NAMESPACE = 'http://www.w3.org/2000/svg';

    /**
     * Drawing primitives and structure only: no text, no style, no scripting, no
     * animation, no references to other documents.
     *
     * `style` is absent deliberately. It is a text node whose content is a second
     * language with its own `url()` fetches and historic `expression()` execution, and
     * nothing certified needs it since fills are attributes on the paths.
     */
    private const ALLOWED_ELEMENTS = [
        'svg',
        'g',
        'defs',
        'metadata',
        'title',
        'desc',
        'path',
        'rect',
        'circle',
        'ellipse',
        'line',
        'polyline',
        'polygon',
        'clipPath',
        'linearGradient',
        'radialGradient',
        'stop',
    ];

    /**
     * Elements refused with a reason of their own, so the operator report names the
     * actual attack rather than a generic "not on the allowlist".
     *
     * Keys are lower-cased local names.
     */
    private const NAMED_THREATS = [
        'script' => AssetRejectionReason::SvgScript,
        'handler' => AssetRejectionReason::SvgScript,
        'foreignobject' => AssetRejectionReason::SvgForeignObject,
        'image' => AssetRejectionReason::SvgExternalReference,
        'use' => AssetRejectionReason::SvgExternalReference,
        'iframe' => AssetRejectionReason::SvgExternalReference,
        'embed' => AssetRejectionReason::SvgExternalReference,
        'object' => AssetRejectionReason::SvgExternalReference,
        'audio' => AssetRejectionReason::SvgExternalReference,
        'video' => AssetRejectionReason::SvgExternalReference,
        'animate' => AssetRejectionReason::SvgDisallowedElement,
        'animatetransform' => AssetRejectionReason::SvgDisallowedElement,
        'animatemotion' => AssetRejectionReason::SvgDisallowedElement,
        'set' => AssetRejectionReason::SvgDisallowedElement,
        'style' => AssetRejectionReason::SvgDisallowedElement,
    ];

    /** Only a same-document fragment reference is ever an acceptable href. */
    private const LOCAL_FRAGMENT = '/\A#[A-Za-z_][A-Za-z0-9_.:-]*\z/';

    /** The one acceptable shape of `url(...)`: a paint server defined in this same file. */
    private const LOCAL_PAINT_REFERENCE = '/\Aurl\(#[A-Za-z_][A-Za-z0-9_.:-]*\)\z/i';

    /**
     * Nesting cap for the DOM walk.
     *
     * The walk is recursive, and the byte cap alone still permits tens of thousands of
     * nested `<g>` elements, which would exhaust the PHP stack rather than return a
     * rejection. Certified artwork nests three deep.
     */
    private const MAX_DEPTH = 64;

    /**
     * Inspect $xml and return the declared size.
     *
     * @throws AssetInspectionException on any hostile or non-allowlisted construct.
     */
    public function inspect(string $xml): ImageDimensions
    {
        $this->assertNoForbiddenBytes($xml);

        $document = $this->parse($xml);

        if ($document->doctype !== null) {
            throw AssetInspectionException::because(AssetRejectionReason::SvgDoctype);
        }

        $root = $document->documentElement;
        if (!$root instanceof DOMElement) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgNotWellFormed,
                'SVG has no root element.',
            );
        }

        if ($root->namespaceURI !== self::SVG_NAMESPACE || strtolower($root->localName ?? '') !== 'svg') {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgDisallowedElement,
                'Root element is not an svg element in the SVG namespace.',
            );
        }

        foreach ($document->childNodes as $child) {
            $this->walk($child, false, 0);
        }

        $dimensions = $this->readDimensions($root);

        // Deliberately last. An SVG that declares no size at all must keep reporting
        // SvgNoDimensions, which is the more specific failure; only a file that already
        // has a usable size can meaningfully be asked whether that size is renderable
        // without deformation. Parsing *is* the check: SvgGeometry cannot be constructed
        // from a root element whose geometry lets a renderer stretch the mark.
        SvgGeometry::fromRootElement($root);

        return $dimensions;
    }

    /**
     * Byte-level checks that run before libxml sees the input.
     *
     * Belt and braces: layer 2 also refuses a DOCTYPE, but a parser bug or a future
     * libxml default change should not be the only thing standing between a tenant and
     * an entity expansion. Matching on raw bytes cannot be defeated by parser quirks.
     */
    private function assertNoForbiddenBytes(string $xml): void
    {
        if (str_contains($xml, "\0")) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgNotWellFormed,
                'SVG contains a NUL byte.',
            );
        }

        if (stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
            throw AssetInspectionException::because(AssetRejectionReason::SvgDoctype);
        }

        if (stripos($xml, '<?php') !== false) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgProcessingInstruction,
                'SVG contains a PHP open tag.',
            );
        }
    }

    /**
     * Parse with entity resolution and network retrieval switched off.
     *
     * LIBXML_NONET blocks retrieval; the null-returning external entity loader means
     * even a resolver that ignored NONET gets nothing back. LIBXML_NOENT is
     * conspicuously *not* passed: despite its name it turns entity substitution on,
     * which is the opposite of what is wanted here.
     */
    private function parse(string $xml): DOMDocument
    {
        $previousErrorState = libxml_use_internal_errors(true);
        libxml_set_external_entity_loader(static fn (): null => null);

        try {
            $document = new DOMDocument();
            $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } catch (Throwable) {
            throw AssetInspectionException::because(AssetRejectionReason::SvgNotWellFormed);
        } finally {
            libxml_set_external_entity_loader(null);
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorState);
        }

        if ($loaded === false) {
            throw AssetInspectionException::because(AssetRejectionReason::SvgNotWellFormed);
        }

        return $document;
    }

    /**
     * Recursively vet one node and its subtree.
     *
     * @param bool $insideMetadata True once the walk is below a `<metadata>` element,
     *                             whose contents are never rendered and may therefore
     *                             carry foreign-namespace payloads such as the C2PA
     *                             provenance manifest the certified masters embed.
     */
    private function walk(DOMNode $node, bool $insideMetadata, int $depth): void
    {
        if ($depth > self::MAX_DEPTH) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgDisallowedElement,
                'SVG nests deeper than the permitted limit.',
            );
        }

        match ($node->nodeType) {
            XML_PI_NODE => throw AssetInspectionException::because(
                AssetRejectionReason::SvgProcessingInstruction,
            ),
            XML_DOCUMENT_TYPE_NODE, XML_ENTITY_NODE, XML_ENTITY_REF_NODE, XML_DTD_NODE
                => throw AssetInspectionException::because(AssetRejectionReason::SvgDoctype),
            // A CDATA section is the standard way to smuggle a script body past a naive
            // text scanner. Nothing certified uses one.
            XML_CDATA_SECTION_NODE => throw AssetInspectionException::because(
                AssetRejectionReason::SvgDisallowedElement,
                'SVG contains a CDATA section.',
            ),
            default => null,
        };

        if ($node instanceof DOMElement) {
            $insideMetadata = $this->vetElement($node, $insideMetadata);
        }

        foreach ($node->childNodes as $child) {
            $this->walk($child, $insideMetadata, $depth + 1);
        }
    }

    /** @return bool the metadata flag to propagate to $element's children */
    private function vetElement(DOMElement $element, bool $insideMetadata): bool
    {
        $localName = strtolower($element->localName ?? '');

        $threat = self::NAMED_THREATS[$localName] ?? null;
        if ($threat !== null) {
            // Angle brackets are deliberately omitted: a rejection message is written
            // into logs and reports, and must never reproduce markup from the candidate
            // file. Quoting the bare element name keeps the message inert.
            throw AssetInspectionException::because(
                $threat,
                'SVG contains a disallowed "' . $this->safeName($localName) . '" element.',
            );
        }

        if ($element->namespaceURI === self::SVG_NAMESPACE) {
            if (!in_array($element->localName, self::ALLOWED_ELEMENTS, true)) {
                throw AssetInspectionException::because(
                    AssetRejectionReason::SvgDisallowedElement,
                    'SVG element "' . $this->safeName($localName) . '" is not on the drawing allowlist.',
                );
            }

            // `svg` is the only allowlisted element on which preserveAspectRatio has a
            // defined effect, and the allowlist admits nested ones: a nested svg opens a
            // viewport of its own, so `none` deforms the artwork inside it exactly as it
            // would on the root. Checking here rather than only in readDimensions() is
            // what makes the clause document-wide instead of root-only.
            if ($localName === 'svg') {
                SvgGeometry::assertUniformScalingPermitted($element);
            }
        } elseif (!$insideMetadata) {
            // Foreign-namespace markup outside <metadata> is either an XHTML injection
            // or an attempt to reach a namespace the renderer treats specially.
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgDisallowedElement,
                'Foreign-namespace element "' . $this->safeName($localName)
                    . '" appears outside the metadata element.',
            );
        }

        // DOMNamedNodeMap over an element yields DOMAttr, so no instanceof guard is
        // needed here; PHPStan flags one as always-true.
        foreach ($element->attributes as $attribute) {
            $this->vetAttribute($attribute);
        }

        return $insideMetadata
            || ($element->namespaceURI === self::SVG_NAMESPACE && $localName === 'metadata');
    }

    private function vetAttribute(DOMAttr $attribute): void
    {
        $name = strtolower($attribute->localName ?? '');
        $value = $attribute->value;

        // Covers onload, onclick, onmouseover, onbegin, onerror and every future one.
        if (str_starts_with($name, 'on')) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgEventHandler,
                'SVG carries an inline event-handler attribute.',
            );
        }

        // href and xlink:href share the local name; both must stay in-document.
        if ($name === 'href' && preg_match(self::LOCAL_FRAGMENT, $value) !== 1) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgExternalReference,
                'SVG href is not a same-document fragment reference.',
            );
        }

        if (in_array($name, ['src', 'srcset', 'formaction', 'action', 'ping', 'data'], true)) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgExternalReference,
                'SVG carries a resource-loading attribute.',
            );
        }

        // Whitespace and control characters are stripped first because
        // "java\nscript:" and "java&#9;script:" are both live in some parsers.
        $compact = (string) preg_replace('/[\s\x00-\x1F]+/', '', $value);
        $collapsed = strtolower($compact);

        if (
            str_contains($collapsed, 'javascript:')
            || str_contains($collapsed, 'vbscript:')
            || str_contains($collapsed, 'data:text/html')
            || str_contains($collapsed, 'expression(')
            || str_contains($collapsed, '@import')
        ) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgExternalReference,
                'SVG attribute value contains a scripting or resource-fetching construct.',
            );
        }

        // `fill="url(#gradient)"` is how a paint server is referenced and must keep
        // working; `fill="url(https://evil.invalid/x)"` is a beacon. Only the exact
        // same-document form is allowed, so no other url() shape can slip through.
        if (
            str_contains($collapsed, 'url(')
            && preg_match(self::LOCAL_PAINT_REFERENCE, $compact) !== 1
        ) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgExternalReference,
                'SVG attribute references a resource outside this document.',
            );
        }
    }

    /**
     * Size from width/height, falling back to the viewBox.
     *
     * Only the root element is consulted; a nested svg cannot redefine the document's
     * intrinsic size for the purpose of the slot check.
     */
    private function readDimensions(DOMElement $root): ImageDimensions
    {
        $width = $this->readLength($root->getAttribute('width'));
        $height = $this->readLength($root->getAttribute('height'));

        if ($width === null || $height === null) {
            $viewBox = preg_split('/[\s,]+/', trim($root->getAttribute('viewBox')), -1, PREG_SPLIT_NO_EMPTY);
            if (is_array($viewBox) && count($viewBox) === 4) {
                $width ??= $this->readLength($viewBox[2]);
                $height ??= $this->readLength($viewBox[3]);
            }
        }

        if ($width === null || $height === null) {
            throw AssetInspectionException::because(AssetRejectionReason::SvgNoDimensions);
        }

        try {
            return new ImageDimensions($width, $height);
        } catch (DomainException) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgNoDimensions,
                'SVG declares an implausible pixel size.',
            );
        }
    }

    /**
     * A CSS length in absolute pixel units, rounded to the nearest whole pixel.
     *
     * Relative units (%, em, vh) are refused rather than guessed at: a percentage size
     * has no intrinsic pixel value to compare against the manifest.
     */
    private function readLength(string $raw): ?int
    {
        $trimmed = strtolower(trim($raw));
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/\A([0-9]+(?:\.[0-9]+)?)(px)?\z/', $trimmed, $matches) !== 1) {
            return null;
        }

        $pixels = (int) round((float) $matches[1]);

        return $pixels > 0 ? $pixels : null;
    }

    /** Element names come from the candidate file, so clip and sanitise before echoing. */
    private function safeName(string $name): string
    {
        $sanitised = preg_replace('/[^a-z0-9:_-]/', '', $name) ?? '';

        return substr($sanitised, 0, 32);
    }
}
