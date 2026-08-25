<?php

/**
 * The root-element geometry a logo must declare in order to scale without deforming.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\AssetIntake;

use DOMElement;

/**
 * A logo is a trademark, and a trademark that has been stretched or squashed is no
 * longer the mark. Every {@see \OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot} is a
 * production logo slot, so this invariant is expressed once here and enforced for every
 * SVG that reaches {@see SvgInspector}. It is keyed on what the slot *is*, never on what
 * a file is called: no filename, brand name or colour appears anywhere in this class.
 *
 * Three clauses, with two different scopes:
 *
 *   G1  `preserveAspectRatio` must not select the `none` alignment, on *any* svg element
 *       in the document. `none` is the one value that tells the renderer to scale x and y
 *       independently, which is exactly the deformation this class exists to prevent.
 *       Absent is fine -- the SVG lacuna value is `xMidYMid meet` -- and so is every other
 *       alignment. Enforced from {@see SvgInspector::vetElement()}, which already visits
 *       every element, so the root and every nested svg are covered by one pass.
 *
 *   G2  The root must declare a usable `viewBox`. `preserveAspectRatio` is *defined* in
 *       terms of the viewBox-to-viewport mapping, so without one G1 has nothing to
 *       constrain on the root and could be evaded simply by deleting the attribute; the
 *       artwork would also map 1:1 into its slot and clip rather than scale.
 *
 *   G3  When the root declares both `width` and `height` as absolute lengths, their
 *       ratio must agree with the viewBox ratio. Browsers take an SVG's *intrinsic*
 *       aspect ratio from those two attributes, so a pair that contradicts the viewBox
 *       makes every downstream sizing mode disagree about the shape of the box the mark
 *       is drawn into.
 *
 * Why G1 is document-wide but G2 and G3 are root-only. `preserveAspectRatio` has a defined
 * effect on `image`, `use`, `symbol`, a nested `svg`, `pattern`, `marker`, `view` and
 * `feImage`. Seven of those eight are either a {@see SvgInspector} named threat or absent
 * from its drawing allowlist, so an SVG containing one never reaches this class. The
 * eighth, a nested `svg`, *is* allowlisted -- it is ordinary structural markup -- and it
 * establishes a new viewport of its own, so `none` on a nested svg deforms the artwork
 * inside it just as surely as it does on the root. Scoping G1 to the root would therefore
 * have left a real hole, and the invariant test pins each of the eight to the behaviour
 * that makes this reasoning true. G2 and G3 stay on the root because they are statements
 * about the *document's* intrinsic size, which only the root has: a nested svg legitimately
 * omits its viewBox to get a 1:1 viewport, so requiring one there would be a false positive.
 * On the remaining allowlisted elements -- `path`, `g`, `rect`, the gradients -- the
 * attribute has no defined effect and every renderer ignores it, so it is left alone.
 */
final readonly class SvgGeometry
{
    /**
     * Permitted relative disagreement between the declared and viewBox aspect ratios.
     *
     * Derived, not chosen for roundness. The smallest edge any slot certifies is 100 px
     * (`LogoSlot::CoreLoginSmallPrimary`, 101x100). An exporter that rounds both declared
     * lengths to whole pixels moves the ratio by at most about 0.5/101 + 0.5/100, i.e.
     * 0.99%. One percent therefore admits every honestly-rounded export at the smallest
     * size this product ships, and refuses anything wider, which at those sizes is a
     * deformation a reader can see rather than a rounding artefact. Every SVG currently
     * shipped under brand/ agrees exactly, so nothing legitimate is riding on the band.
     */
    public const ASPECT_TOLERANCE = 0.01;

    /** The one `preserveAspectRatio` alignment that disables uniform scaling. */
    private const NON_UNIFORM_ALIGNMENT = 'none';

    /** An SVG number: optional sign, decimal, optional exponent. */
    private const NUMBER = '/\A[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?\z/';

    /**
     * @param float      $viewBoxWidth   Third viewBox value; always greater than zero.
     * @param float      $viewBoxHeight  Fourth viewBox value; always greater than zero.
     * @param float|null $declaredWidth  Root `width` in absolute px, or null when it is
     *                                   absent, relative (`%`, `em`) or non-positive.
     * @param float|null $declaredHeight Root `height`, same rule.
     */
    private function __construct(
        public float $viewBoxWidth,
        public float $viewBoxHeight,
        public ?float $declaredWidth,
        public ?float $declaredHeight,
    ) {
    }

    /**
     * Parse, don't validate: this object cannot exist for a root element whose geometry
     * would let a renderer deform the artwork.
     *
     * @throws AssetInspectionException when G1, G2 or G3 is violated.
     */
    public static function fromRootElement(DOMElement $root): self
    {
        self::assertUniformScalingPermitted($root);

        [$viewBoxWidth, $viewBoxHeight] = self::readViewBox($root);

        $geometry = new self(
            $viewBoxWidth,
            $viewBoxHeight,
            self::readAbsoluteLength($root->getAttribute('width')),
            self::readAbsoluteLength($root->getAttribute('height')),
        );

        $geometry->assertDeclaredSizeAgreesWithViewBox();

        return $geometry;
    }

    /** Width over height, from the viewBox. Never divides by zero: G2 guarantees positive. */
    public function viewBoxAspectRatio(): float
    {
        return $this->viewBoxWidth / $this->viewBoxHeight;
    }

    /** The intrinsic ratio a browser would read from `width`/`height`, or null when it cannot. */
    public function declaredAspectRatio(): ?float
    {
        if ($this->declaredWidth === null || $this->declaredHeight === null) {
            return null;
        }

        return $this->declaredWidth / $this->declaredHeight;
    }

    /**
     * G1, callable on any svg element rather than only on the root.
     *
     * {@see SvgInspector} calls this for every svg element it walks, which is what makes
     * the clause document-wide; `fromRootElement()` calls it again for the root so the
     * value object's own contract holds independently of who constructs it.
     *
     * The comparison is case-insensitive even though SVG keywords are case-sensitive.
     * Lower-case `none` is honoured and distorts; any other casing is an invalid value
     * whose treatment is renderer-dependent (SVG 1.1 calls the document in error, SVG 2
     * falls back to the lacuna value). Neither is something a production logo should be
     * relying on, and both record the same author intent, so both are refused.
     *
     * Only `none` is refused. An unrecognised alignment token is left alone on purpose:
     * every renderer falls back to `xMidYMid meet` for it, which is the safe behaviour,
     * so refusing it would be style enforcement rather than distortion prevention.
     *
     * @throws AssetInspectionException
     */
    public static function assertUniformScalingPermitted(DOMElement $svgElement): void
    {
        $raw = trim($svgElement->getAttribute('preserveAspectRatio'));
        if ($raw === '') {
            return;
        }

        // $raw is trimmed and non-empty, so splitting it on whitespace with NO_EMPTY always
        // yields at least one token; only the false return needs guarding.
        $tokens = preg_split('/\s+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($tokens)) {
            return;
        }

        // The optional `defer` keyword may precede the alignment.
        $alignment = strtolower($tokens[0]) === 'defer' ? ($tokens[1] ?? '') : $tokens[0];

        if (strtolower($alignment) !== self::NON_UNIFORM_ALIGNMENT) {
            return;
        }

        throw AssetInspectionException::because(
            AssetRejectionReason::SvgAspectRatioNotPreserved,
            'An svg element sets preserveAspectRatio to the "none" alignment, which permits '
                . 'the renderer to scale width and height independently and deform the mark. '
                . 'Set preserveAspectRatio="xMidYMid meet", or remove the attribute so the '
                . 'SVG default applies.',
        );
    }

    /**
     * G2.
     *
     * @return array{float, float} the viewBox width and height, both greater than zero
     *
     * @throws AssetInspectionException
     */
    private static function readViewBox(DOMElement $root): array
    {
        $tokens = preg_split('/[\s,]+/', trim($root->getAttribute('viewBox')), -1, PREG_SPLIT_NO_EMPTY);

        if (!is_array($tokens) || count($tokens) !== 4) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgViewBoxMissing,
                'SVG root declares no usable viewBox. A logo must carry a viewBox of four '
                    . 'numbers so it can be scaled into a slot of any size; without one, '
                    . 'preserveAspectRatio has nothing to act on.',
            );
        }

        $width = self::readNumber($tokens[2]);
        $height = self::readNumber($tokens[3]);

        if ($width === null || $height === null || $width <= 0.0 || $height <= 0.0) {
            throw AssetInspectionException::because(
                AssetRejectionReason::SvgViewBoxMissing,
                'SVG root declares a viewBox whose third and fourth values are not both '
                    . 'positive numbers, so it defines no area to scale from.',
            );
        }

        return [$width, $height];
    }

    /**
     * G3.
     *
     * Skipped unless both lengths are absolute. A percentage or font-relative length
     * carries no intrinsic ratio of its own -- the viewBox governs on its own -- so
     * there is nothing for it to contradict.
     *
     * @throws AssetInspectionException
     */
    private function assertDeclaredSizeAgreesWithViewBox(): void
    {
        $declared = $this->declaredAspectRatio();
        if ($declared === null) {
            return;
        }

        $expected = $this->viewBoxAspectRatio();
        if (abs($declared - $expected) / $expected <= self::ASPECT_TOLERANCE) {
            return;
        }

        // Every interpolated value is a number this class parsed and formatted itself,
        // so the detail carries no byte of the candidate file.
        throw AssetInspectionException::because(
            AssetRejectionReason::SvgAspectRatioConflict,
            'SVG root declares width and height with an aspect ratio of '
                . number_format($declared, 4)
                . ' but a viewBox with an aspect ratio of ' . number_format($expected, 4) . '. '
                . 'A logo must declare width and height matching its viewBox ratio to within '
                . (int) round(self::ASPECT_TOLERANCE * 100) . ' percent, or omit them.',
        );
    }

    /**
     * A CSS length in absolute pixels, kept as a float so G3 compares the declared
     * numbers rather than the whole-pixel rounding {@see SvgInspector} applies.
     */
    private static function readAbsoluteLength(string $raw): ?float
    {
        $trimmed = strtolower(trim($raw));
        if (str_ends_with($trimmed, 'px')) {
            $trimmed = rtrim(substr($trimmed, 0, -2));
        }

        $value = self::readNumber($trimmed);

        return $value !== null && $value > 0.0 ? $value : null;
    }

    private static function readNumber(string $raw): ?float
    {
        $trimmed = trim($raw);

        return preg_match(self::NUMBER, $trimmed) === 1 ? (float) $trimmed : null;
    }
}
