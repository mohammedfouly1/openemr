<?php

/**
 * Isolated tests for the Tier 2 token overlay value object.
 *
 * These tests exist to hold locked constraint C1 in place. The overlay is the only
 * structured payload in the branding configuration model, so it is the only place where
 * a stylesheet, a script or any other free text could conceivably enter. Every rejection
 * case below is an attempt to make it do so.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Config;

use OpenEMR\Modules\SkyEagleBranding\Config\TokenOverlay;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TokenOverlayTest extends TestCase
{
    use ModuleAutoloadTrait;

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    public function testEmptyOverlayCarriesNothing(): void
    {
        $overlay = TokenOverlay::empty();

        $this->assertTrue($overlay->isEmpty());
        $this->assertSame(0, $overlay->count());
        $this->assertSame([], $overlay->all());
        $this->assertNull($overlay->get('link.default'));
        $this->assertFalse($overlay->has('link.default'));
    }

    public function testWellFormedOverlayParses(): void
    {
        $overlay = TokenOverlay::fromJson(
            '{"interactive.primary.default":"#0B1B4D","link.hover":"#1e4574"}'
        );

        $this->assertFalse($overlay->isEmpty());
        $this->assertSame(2, $overlay->count());
        $this->assertTrue($overlay->has('interactive.primary.default'));
        $this->assertSame('#0B1B4D', $overlay->get('interactive.primary.default'));
    }

    public function testHexValuesAreNormalisedToUpperCase(): void
    {
        $overlay = TokenOverlay::fromJson('{"link.default":"#2c5f94"}');

        $this->assertSame('#2C5F94', $overlay->get('link.default'));
    }

    public function testOverlayAtTheSizeLimitStillParses(): void
    {
        $entries = [];
        for ($i = 0; $i < 64; ++$i) {
            $entries["token.k{$i}"] = '#0B1B4D';
        }

        $overlay = TokenOverlay::fromJson((string) json_encode($entries));

        $this->assertSame(64, $overlay->count());
    }

    public function testOverlayBeyondTheSizeLimitIsRejected(): void
    {
        $entries = [];
        for ($i = 0; $i < 65; ++$i) {
            $entries["token.k{$i}"] = '#0B1B4D';
        }

        $this->assertTrue(TokenOverlay::fromJson((string) json_encode($entries))->isEmpty());
    }

    public function testRejectionIsAllOrNothing(): void
    {
        // One bad pair discards the document: a half-applied palette must never render.
        $overlay = TokenOverlay::fromJson('{"link.default":"#2C5F94","link.hover":"red"}');

        $this->assertTrue($overlay->isEmpty());
    }

    #[DataProvider('rejectedDocumentProvider')]
    public function testMalformedDocumentDegradesToTheEmptyOverlay(string $json): void
    {
        $this->assertTrue(
            TokenOverlay::fromJson($json)->isEmpty(),
            'A malformed overlay must degrade to Tier 1, never carry its payload through.'
        );
    }

    /**
     * Documents that must never produce a non-empty overlay.
     *
     * @return iterable<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rejectedDocumentProvider(): iterable
    {
        yield 'blank' => [''];
        yield 'whitespace only' => ["  \n\t "];
        yield 'empty object' => ['{}'];
        yield 'not json' => ['not json at all'];
        yield 'truncated json' => ['{"link.default":'];
        yield 'json list' => ['["#0B1B4D","#1E4574"]'];
        yield 'json string scalar' => ['"#0B1B4D"'];
        yield 'json number scalar' => ['42'];
        yield 'json null' => ['null'];
        yield 'nested object' => ['{"link":{"default":"#0B1B4D"}}'];
        yield 'numeric value' => ['{"link.default":11}'];
        yield 'boolean value' => ['{"link.default":true}'];
        yield 'null value' => ['{"link.default":null}'];
        yield 'named css colour' => ['{"link.default":"rebeccapurple"}'];
        yield 'rgb function' => ['{"link.default":"rgb(11,27,77)"}'];
        yield 'three digit hex' => ['{"link.default":"#abc"}'];
        yield 'eight digit hex' => ['{"link.default":"#0B1B4DFF"}'];
        yield 'hex without hash' => ['{"link.default":"0B1B4D"}'];
        yield 'hex with trailing semicolon' => ['{"link.default":"#0B1B4D;"}'];
        yield 'css declaration as value' => ['{"link.default":"#0B1B4D; background: url(x)"}'];
        yield 'css block as value' => ['{"link.default":"body{display:none}"}'];
        yield 'script tag as value' => ['{"link.default":"<script>alert(1)</script>"}'];
        yield 'javascript url as value' => ['{"link.default":"javascript:alert(1)"}'];
        yield 'css import as value' => ['{"link.default":"@import url(//evil.test/x.css)"}'];
        yield 'expression as value' => ['{"link.default":"expression(alert(1))"}'];
        yield 'custom property as key' => ['{"--link-default":"#0B1B4D"}'];
        yield 'selector as key' => ['{"body a:hover":"#0B1B4D"}'];
        yield 'key with space' => ['{"link default":"#0B1B4D"}'];
        yield 'key with brace' => ['{"link{}":"#0B1B4D"}'];
        yield 'key with slash' => ['{"link/default":"#0B1B4D"}'];
        yield 'key with trailing dot' => ['{"link.":"#0B1B4D"}'];
        yield 'key with leading dot' => ['{".link":"#0B1B4D"}'];
        yield 'key with double dot' => ['{"link..default":"#0B1B4D"}'];
        yield 'empty key' => ['{"":"#0B1B4D"}'];
        yield 'numeric key' => ['{"1":"#0B1B4D"}'];
        yield 'html comment as key' => ['{"<!--":"#0B1B4D"}'];
    }
}
