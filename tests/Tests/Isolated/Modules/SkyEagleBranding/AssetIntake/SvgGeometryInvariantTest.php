<?php

/**
 * The production-logo geometry invariant: a logo may never ship able to be deformed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\AssetIntake;

use FilesystemIterator;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\AssetRejectionReason;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\LogoValidator;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\RasterImageReader;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\SvgGeometry;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\SvgInspector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

require_once __DIR__ . '/asset_intake_autoloader.php';

/**
 * Everything here is driven through {@see LogoValidator}, the package's public boundary,
 * rather than through {@see SvgGeometry} directly. That is deliberate: the property worth
 * proving is not "the rule returns the right answer" but "no SVG reaches a tenant's site
 * directory without the rule having run". Calling the rule in isolation would still pass
 * if somebody removed the call site.
 *
 * No test names a brand, a product or a colour. Every candidate is judged on what the
 * slot is -- a production logo slot -- and nothing else.
 */
final class SvgGeometryInvariantTest extends TestCase
{
    use AssetFixtureTrait;

    /**
     * A slot with no certified pixel size, so a fixture's dimensions can never be the
     * thing that decides a case. Only the geometry rule can.
     */
    private const OPEN_SLOT = LogoSlot::CoreMenuPrimary;

    private LogoValidator $validator;

    protected function setUp(): void
    {
        $this->makeFixtureDirectory();
        $this->validator = new LogoValidator(
            new RasterImageReader(),
            new SvgInspector(),
            new NullLogger(),
        );
    }

    protected function tearDown(): void
    {
        $this->removeFixtureDirectory();
    }

    /**
     * Geometry a renderer cannot deform is accepted, unchanged.
     *
     * These are the cases most at risk of a false positive, so each one is a shape a real
     * exporter emits rather than a contrived minimum.
     */
    #[DataProvider('compliantGeometryProvider')]
    public function testCompliantGeometryIsAccepted(string $svg): void
    {
        $path = $this->writeFixture('logo.svg', $svg);

        $result = $this->validator->validate(self::OPEN_SLOT, $path, 'logo.svg');

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
        self::assertNotNull($result->asset());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function compliantGeometryProvider(): array
    {
        return [
            // The SVG lacuna value is xMidYMid meet, so an absent attribute is already
            // the correct behaviour and must not be forced to be written out.
            'preserveAspectRatio absent' => [self::geometrySvg('0 0 100 50', 'width="100" height="50"')],
            'preserveAspectRatio xMidYMid meet' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'xMidYMid meet'),
            ],
            // "meet" is implied when the keyword is omitted.
            'alignment without a meetOrSlice keyword' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'xMidYMid'),
            ],
            // slice crops but still scales uniformly, so the mark keeps its shape.
            'xMidYMid slice' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'xMidYMid slice'),
            ],
            'a non-centre alignment' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'xMinYMax meet'),
            ],
            'defer before a uniform alignment' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'defer xMidYMid meet'),
            ],
            // An unrecognised token falls back to the lacuna value in every renderer, so
            // it is safe; refusing it would be style enforcement, not distortion control.
            'an unrecognised alignment token' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'xMidYMidd meet'),
            ],
            'viewBox only, no declared size' => [self::geometrySvg('0 0 100 50', '')],
            'declared size in explicit px units' => [
                self::geometrySvg('0 0 100 50', 'width="100px" height="50px"'),
            ],
            // A percentage carries no intrinsic ratio, so there is nothing to contradict.
            'percentage width and height' => [
                self::geometrySvg('0 0 100 50', 'width="100%" height="100%"'),
            ],
            // Whole-pixel rounding of a fractional export: inside the 1% band by design.
            'declared size rounded from a fractional viewBox' => [
                self::geometrySvg('0 0 101.4 100.2', 'width="101" height="100"'),
            ],
            'viewBox with comma separators and a negative origin' => [
                self::geometrySvg('-10,-10,100,50', 'width="100" height="50"'),
            ],
            'viewBox in scientific notation' => [
                self::geometrySvg('0 0 1e2 5e1', 'width="100" height="50"'),
            ],
            // A nested svg is ordinary structural markup. G2 and G3 must not follow it
            // down: omitting its viewBox is how an author asks for a 1:1 sub-viewport.
            'nested svg with no viewBox of its own' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', null, '<svg><path d="M0 0"/></svg>'),
            ],
            'nested svg with a uniform alignment' => [
                self::geometrySvg(
                    '0 0 100 50',
                    'width="100" height="50"',
                    null,
                    '<svg viewBox="0 0 10 10" preserveAspectRatio="xMidYMid meet"><path d="M0 0"/></svg>',
                ),
            ],
        ];
    }

    /**
     * Geometry that lets a renderer deform the mark is refused, and refused *for* the
     * right reason. A case that fails for some earlier reason would prove nothing.
     */
    #[DataProvider('deformableGeometryProvider')]
    public function testDeformableGeometryIsRejected(string $svg, AssetRejectionReason $expected): void
    {
        $path = $this->writeFixture('logo.svg', $svg);

        $result = $this->validator->validate(self::OPEN_SLOT, $path, 'logo.svg');

        self::assertFalse($result->isValid(), 'Deformable geometry was accepted.');
        self::assertNull($result->asset());
        self::assertSame($expected, $result->rejections()[0]->reason);
    }

    /**
     * @return array<string, array{string, AssetRejectionReason}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function deformableGeometryProvider(): array
    {
        return [
            // The defect that started this: an exporter's default, silently shipped.
            'preserveAspectRatio none on the root' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'none'),
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
            'none with an explicit meet keyword' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'none meet'),
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
            'none behind the defer keyword' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'defer none'),
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
            // Mis-cased: either the renderer honours it and distorts, or it is an invalid
            // value whose fallback is renderer-dependent. Neither belongs in a logo.
            'None in mixed case' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'None'),
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
            'NONE in upper case' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'NONE'),
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
            'none padded with whitespace' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="50"', "  none \n meet  "),
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
            // Without a viewBox, preserveAspectRatio has nothing to act on, so the rule
            // above could be evaded simply by deleting the attribute.
            'declared size but no viewBox' => [
                self::geometrySvg(null, 'width="100" height="50"'),
                AssetRejectionReason::SvgViewBoxMissing,
            ],
            'viewBox with only three values' => [
                self::geometrySvg('0 0 100', 'width="100" height="50"'),
                AssetRejectionReason::SvgViewBoxMissing,
            ],
            'viewBox with five values' => [
                self::geometrySvg('0 0 100 50 20', 'width="100" height="50"'),
                AssetRejectionReason::SvgViewBoxMissing,
            ],
            'viewBox with a zero width' => [
                self::geometrySvg('0 0 0 50', 'width="100" height="50"'),
                AssetRejectionReason::SvgViewBoxMissing,
            ],
            'viewBox with a negative height' => [
                self::geometrySvg('0 0 100 -50', 'width="100" height="50"'),
                AssetRejectionReason::SvgViewBoxMissing,
            ],
            'viewBox with a non-numeric extent' => [
                self::geometrySvg('0 0 100 auto', 'width="100" height="50"'),
                AssetRejectionReason::SvgViewBoxMissing,
            ],
            'viewBox carrying css units' => [
                self::geometrySvg('0 0 100px 50px', 'width="100" height="50"'),
                AssetRejectionReason::SvgViewBoxMissing,
            ],
            // 2:1 artwork declared as a square box: every sizing mode downstream then
            // disagrees about the shape of the slot the mark is drawn into.
            'declared size contradicts the viewBox' => [
                self::geometrySvg('0 0 100 50', 'width="100" height="100"'),
                AssetRejectionReason::SvgAspectRatioConflict,
            ],
            'declared size just outside the rounding band' => [
                // 100/48 = 2.0833 against 2.0000: 4.2% out, four times the tolerance.
                self::geometrySvg('0 0 100 50', 'width="100" height="48"'),
                AssetRejectionReason::SvgAspectRatioConflict,
            ],
            // The hole a root-only rule would have left: a nested svg is allowlisted
            // structural markup and opens a viewport of its own, so `none` deforms the
            // artwork inside it exactly as it would on the root.
            'preserveAspectRatio none on a nested svg' => [
                self::geometrySvg(
                    '0 0 100 50',
                    'width="100" height="50"',
                    null,
                    '<svg viewBox="0 0 10 10" preserveAspectRatio="none"><path d="M0 0"/></svg>',
                ),
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
            'preserveAspectRatio none on an svg nested two deep inside a group' => [
                self::geometrySvg(
                    '0 0 100 50',
                    'width="100" height="50"',
                    null,
                    '<g><g><svg viewBox="0 0 10 10" preserveAspectRatio="none"><path d="M0 0"/></svg></g></g>',
                ),
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
        ];
    }

    /**
     * Ordering: a file with no size at all keeps reporting the more specific failure.
     *
     * The geometry rule runs after the size is read precisely so that adding it did not
     * silently re-label an existing rejection, which would break the operator reports and
     * the Control Plane's stable wire values.
     */
    public function testSvgWithNoSizeAtAllStillReportsNoDimensions(): void
    {
        $path = $this->writeFixture('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>');

        $result = $this->validator->validate(self::OPEN_SLOT, $path, 'logo.svg');

        self::assertFalse($result->isValid());
        self::assertSame(AssetRejectionReason::SvgNoDimensions, $result->rejections()[0]->reason);
    }

    /**
     * A developer must be able to fix the file without reading this code.
     *
     * The message has to carry three things: which file, which attribute, and what the
     * value should have been.
     */
    public function testRejectionNamesTheFileTheAttributeAndTheExpectedValue(): void
    {
        $path = $this->writeFixture(
            'logo.svg',
            self::geometrySvg('0 0 100 50', 'width="100" height="50"', 'none'),
        );

        $result = $this->validator->validate(self::OPEN_SLOT, $path, 'company-mark.svg');

        $message = $result->messages()[0];
        self::assertStringContainsString('company-mark.svg', $message, 'Message does not name the file.');
        self::assertStringContainsString('preserveAspectRatio', $message, 'Message does not name the attribute.');
        self::assertStringContainsString('xMidYMid meet', $message, 'Message does not give the expected value.');
    }

    /** The conflict message must quote both ratios, so the fix is arithmetic, not guesswork. */
    public function testAspectConflictMessageQuotesBothRatios(): void
    {
        $path = $this->writeFixture('logo.svg', self::geometrySvg('0 0 100 50', 'width="100" height="100"'));

        $result = $this->validator->validate(self::OPEN_SLOT, $path, 'logo.svg');

        $message = $result->messages()[0];
        self::assertStringContainsString('1.0000', $message, 'Message omits the declared ratio.');
        self::assertStringContainsString('2.0000', $message, 'Message omits the viewBox ratio.');
    }

    /**
     * The scope argument, pinned to the behaviour it rests on.
     *
     * There are eight SVG elements other than the root on which `preserveAspectRatio` has
     * a defined effect. The claim this safeguard rests on is that carrying `none` on any
     * of them is impossible in an accepted logo -- seven because the inspector's element
     * allowlist refuses them outright, and the eighth, a nested svg, because the geometry
     * clause follows the walk into it. Asserting each one's refusal is what stops a future
     * widening of that allowlist from quietly opening a distortion hole.
     */
    #[DataProvider('geometryHonouringElementProvider')]
    public function testNoElementCanSmuggleNonUniformScalingPastTheGate(
        string $element,
        AssetRejectionReason $expected,
    ): void {
        $path = $this->writeFixture(
            'logo.svg',
            self::geometrySvg('0 0 100 50', 'width="100" height="50"', null, $element),
        );

        $result = $this->validator->validate(self::OPEN_SLOT, $path, 'logo.svg');

        self::assertFalse(
            $result->isValid(),
            'An element that honours preserveAspectRatio="none" was accepted, so the '
                . 'geometry safeguard no longer covers every element that can deform the mark.',
        );
        self::assertSame($expected, $result->rejections()[0]->reason);
    }

    /**
     * Every SVG element on which `preserveAspectRatio` has a defined effect, other than
     * the root itself, paired with the layer that must refuse it.
     *
     * @return array<string, array{string, AssetRejectionReason}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function geometryHonouringElementProvider(): array
    {
        return [
            // Refused by name: these are external-reference threats in their own right.
            'image' => [
                '<image href="#a" preserveAspectRatio="none" width="1" height="1"/>',
                AssetRejectionReason::SvgExternalReference,
            ],
            'use' => [
                '<use href="#a" preserveAspectRatio="none"/>',
                AssetRejectionReason::SvgExternalReference,
            ],
            // Refused by the drawing allowlist: nothing certified needs them.
            'symbol' => [
                '<symbol id="s" viewBox="0 0 1 1" preserveAspectRatio="none"><path d="M0 0"/></symbol>',
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'pattern' => [
                '<pattern id="p" viewBox="0 0 1 1" preserveAspectRatio="none"><path d="M0 0"/></pattern>',
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'marker' => [
                '<marker id="m" viewBox="0 0 1 1" preserveAspectRatio="none"><path d="M0 0"/></marker>',
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'view' => [
                '<view id="v" viewBox="0 0 1 1" preserveAspectRatio="none"/>',
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'feImage' => [
                '<feImage href="#a" preserveAspectRatio="none"/>',
                AssetRejectionReason::SvgDisallowedElement,
            ],
            // The one that is allowlisted, and therefore the one the geometry clause has
            // to reach on its own.
            'nested svg' => [
                '<svg viewBox="0 0 1 1" preserveAspectRatio="none"><path d="M0 0"/></svg>',
                AssetRejectionReason::SvgAspectRatioNotPreserved,
            ],
        ];
    }

    /**
     * The other half of the scope decision, stated as behaviour.
     *
     * On an allowlisted drawing element the attribute has no defined effect and every
     * renderer ignores it. Refusing it would be a false positive on inert markup, so the
     * rule deliberately does not, and that choice is recorded here rather than left to be
     * rediscovered by whoever next sees the attribute in a diff.
     */
    #[DataProvider('inertPreserveAspectRatioProvider')]
    public function testPreserveAspectRatioIsIgnoredOnInertDrawingElements(string $element): void
    {
        $path = $this->writeFixture('logo.svg', self::geometrySvg('0 0 100 50', 'width="100" height="50"', null, $element));

        $result = $this->validator->validate(self::OPEN_SLOT, $path, 'logo.svg');

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function inertPreserveAspectRatioProvider(): array
    {
        return [
            'path' => ['<path d="M0 0 L1 1" preserveAspectRatio="none"/>'],
            'group' => ['<g preserveAspectRatio="none"><path d="M0 0"/></g>'],
            'rect' => ['<rect width="10" height="10" preserveAspectRatio="none"/>'],
        ];
    }

    /**
     * Malformed input must produce a rejection, never an escaped throwable.
     *
     * The geometry rule reads attributes off a DOM element, so it can only ever see input
     * that already parsed. These cases prove that the ones that do not parse still land on
     * the result-object path rather than tearing out through the new code.
     */
    #[DataProvider('malformedInputProvider')]
    public function testMalformedInputFailsSafely(string $contents): void
    {
        $path = $this->writeFixture('logo.svg', $contents);

        $result = $this->validator->validate(self::OPEN_SLOT, $path, 'logo.svg');

        self::assertFalse($result->isValid());
        self::assertNull($result->asset());
        self::assertNotSame([], $result->rejections());
        // Reason unasserted on purpose: which layer catches these is not this test's
        // subject. That no exception escaped is.
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedInputProvider(): array
    {
        return [
            'truncated root element' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"'],
            'unclosed root element' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"><path d="M0 0">'],
            'plain text that is not markup' => [str_repeat('not an image at all. ', 8)],
            'xml that is not svg' => [
                '<?xml version="1.0"?><catalogue xmlns="urn:example"><entry preserveAspectRatio="none"/></catalogue>',
            ],
            'svg root outside the svg namespace' => [
                '<svg xmlns="urn:example" viewBox="0 0 100 50" width="100" height="50"><path d="M0 0"/></svg>',
            ],
            'binary noise wearing an xml preamble' => ['<?xml version="1.0"?>' . "\x01\x02\x03\x04\x05\x06\x07\x08"],
            'empty root with nothing else' => ['<?xml version="1.0"?>'],
        ];
    }

    /**
     * The shipping half of the safeguard.
     *
     * The rule above stops a *tenant* from supplying a deformable logo at runtime. This
     * sweep stops the *product* from committing one: every SVG under brand/ is pushed
     * through the same gate a tenant upload goes through, so a distortion-enabling export
     * cannot enter the repository unnoticed either.
     *
     * The sweep is directory-driven, not name-driven. It enumerates whatever SVG files the
     * brand asset tree contains, so it keeps working when the tree is renamed, re-themed or
     * handed to a different brand entirely.
     */
    #[DataProvider('shippedSvgProvider')]
    public function testEveryShippedSvgSatisfiesTheGeometryInvariant(string $relativePath): void
    {
        $path = self::repositoryRoot() . '/' . $relativePath;
        self::assertFileExists($path);

        $result = $this->validator->validate(self::OPEN_SLOT, $path, basename($relativePath));

        self::assertTrue(
            $result->isValid(),
            $relativePath . ' fails the production logo geometry invariant: '
                . implode(' | ', $result->messages()),
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function shippedSvgProvider(): array
    {
        $cases = [];
        foreach (self::shippedSvgPaths() as $relativePath) {
            $cases[$relativePath] = [$relativePath];
        }

        return $cases;
    }

    /**
     * A sweep that silently found nothing would pass forever.
     *
     * The floor is the count of vector masters and derivatives the brand tree carried when
     * this safeguard was written. It is a floor, not an equality: adding artwork must not
     * break the build, losing the whole tree must.
     */
    public function testTheShippedSweepActuallyFoundTheBrandAssetTree(): void
    {
        self::assertGreaterThanOrEqual(
            20,
            count(self::shippedSvgPaths()),
            'The shipped SVG sweep found almost no files, so it is no longer guarding anything.',
        );
    }

    /**
     * Every declared root actually contributed, which a total count cannot tell you.
     *
     * S4D-02 again, in its general form. `public/images` contributes 4 SVGs and the module's
     * deployed logos 3, against 21 under `brand/` — so either could vanish entirely and a
     * count floor of 20 would still pass while the sweep silently stopped covering a
     * production logo. The floor answers "is this sweeping anything"; this answers "is this
     * sweeping everything it says it does", and only the second would have caught the defect.
     */
    public function testEveryDeclaredShippedRootContributesAtLeastOneSvg(): void
    {
        $paths = self::shippedSvgPaths();

        foreach (self::SHIPPED_SVG_ROOTS as $relativeRoot) {
            $found = array_filter(
                $paths,
                static fn (string $path): bool => str_starts_with($path, $relativeRoot . '/'),
            );

            self::assertNotSame(
                [],
                $found,
                sprintf(
                    'The shipped SVG sweep found nothing under "%s". Either the tree moved and this '
                        . 'list is stale, or a production logo tree has stopped being guarded.',
                    $relativeRoot,
                ),
            );
        }
    }

    /** The derivation behind the tolerance, asserted so a future edit has to justify itself. */
    public function testToleranceAbsorbsWholePixelRoundingAtTheSmallestCertifiedEdge(): void
    {
        $smallestCertifiedEdge = 100.0;
        $worstCaseRoundingDrift = 0.5 / ($smallestCertifiedEdge + 1.0) + 0.5 / $smallestCertifiedEdge;

        self::assertGreaterThanOrEqual(
            $worstCaseRoundingDrift,
            SvgGeometry::ASPECT_TOLERANCE,
            'The tolerance no longer covers whole-pixel rounding at the smallest certified slot edge.',
        );
        self::assertLessThan(
            0.02,
            SvgGeometry::ASPECT_TOLERANCE,
            'The tolerance has widened past the point where a reader can see the deformation.',
        );
    }

    /**
     * The three trees this product ships SVGs from.
     *
     * S4D-02: this sweep used to walk `brand/` alone while the closure it evidenced claimed
     * "all 27 shipped SVGs under brand/, public/images/ and the module's dark marks were
     * checked". Twenty-one were. The six that were not included
     * `public/images/logos/core/menu/primary/logo.svg` — the production menu logo the claim
     * itself named. No asset was actually deformable, so nothing shipped broken; what was
     * wrong was a closure resting on a corpus larger than any standing test swept, which is
     * the false-green shape this whole programme exists to catch.
     *
     * The source tree alone is not sufficient coverage, because the source and the deployed
     * copy are separate files. The manifest guarantees byte-equality for FONTS only
     * (a MirroredTree rule); for images it records hashes independently and its own re-issue
     * discipline explicitly contemplates a deployed hash diverging from its source. A
     * deployed logo can therefore differ from the brand original, and only sweeping the
     * deployed path catches that.
     *
     * @var list<string>
     */
    private const SHIPPED_SVG_ROOTS = [
        'brand',
        'public/images/logos',
        'interface/modules/custom_modules/oe-module-skyeagle-branding/public/logos',
    ];

    /**
     * Every SVG this product ships, as repository-relative forward-slash paths.
     *
     * The roots are whole trees this product OWNS, swept wholesale with no per-file exclusion
     * list — a curated list is one more thing that has to stay correct, and the way a real logo
     * eventually gets missed.
     *
     * `public/images` is deliberately narrowed to its `logos/` subtree rather than swept entire.
     * The three SVGs directly under `public/images` are upstream core assets, not this product's
     * branding: `login-logo.svg` opens with the comment "OpenEMR Logo Vectorized",
     * `review-logo.svg` is its sibling, and `ub04.svg` is a UB-04 claim form. Judging a claim
     * form against a logo slot's size cap would be a category error, and it is the validator's
     * *whole* contract that runs here, not the geometry clause alone. Their upstream-brand
     * content is a separate concern tracked as its own finding; it is not this sweep's business.
     *
     * @return list<string>
     */
    private static function shippedSvgPaths(): array
    {
        $paths = [];
        $prefix = str_replace('\\', '/', self::repositoryRoot()) . '/';

        foreach (self::SHIPPED_SVG_ROOTS as $relativeRoot) {
            $root = self::repositoryRoot() . '/' . $relativeRoot;
            if (!is_dir($root)) {
                continue;
            }

            $walker = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($walker as $entry) {
                if (!$entry instanceof SplFileInfo || !$entry->isFile()) {
                    continue;
                }

                if (strtolower($entry->getExtension()) !== 'svg') {
                    continue;
                }

                $absolute = str_replace('\\', '/', $entry->getPathname());
                $paths[] = str_starts_with($absolute, $prefix)
                    ? substr($absolute, strlen($prefix))
                    : $absolute;
            }
        }

        sort($paths);

        return $paths;
    }

    /**
     * A path-only SVG whose root geometry is exactly what the case under test needs.
     *
     * Kept local rather than pushed into {@see AssetFixtureTrait} because that trait's
     * `svgBytes()` is shared with the security tests, which must keep emitting artwork
     * whose geometry is beyond question.
     *
     * @param string|null $viewBox             viewBox value, or null to omit the attribute.
     * @param string      $sizeAttributes      Ready-made width/height markup, possibly empty.
     * @param string|null $preserveAspectRatio Attribute value, or null to omit it.
     * @param string      $injected            Extra markup spliced inside the root element.
     */
    private static function geometrySvg(
        ?string $viewBox,
        string $sizeAttributes,
        ?string $preserveAspectRatio = null,
        string $injected = '',
    ): string {
        $attributes = 'xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"';

        if ($viewBox !== null) {
            $attributes .= ' viewBox="' . $viewBox . '"';
        }

        if ($sizeAttributes !== '') {
            $attributes .= ' ' . $sizeAttributes;
        }

        if ($preserveAspectRatio !== null) {
            $attributes .= ' preserveAspectRatio="' . $preserveAspectRatio . '"';
        }

        return '<?xml version="1.0" encoding="utf-8" ?>'
            . '<svg ' . $attributes . '>'
            . $injected
            . '<path d="M0 0 L10 10 Z" fill="#123456"/>'
            . '</svg>';
    }
}
