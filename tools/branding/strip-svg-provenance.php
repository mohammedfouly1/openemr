<?php

/**
 * Removes embedded C2PA provenance metadata from the brand SVG masters.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

/**
 * Every SVG in the brand kit shipped with an embedded `<c2pa:manifest>` block carrying
 * generation provenance. On the navbar mark — the one served on every authenticated page
 * — that block is 12,742 of 14,714 bytes, so 86% of the asset is metadata rather than
 * artwork.
 *
 * Two reasons to remove it, recorded so the change is not mistaken for tidying:
 *
 *  1. Weight. ~13 KB per page load for the navbar alone, and ~396 KB across the kit.
 *  2. Disclosure. The blocks are machine-readable statements about how the marks were
 *     produced, embedded in the visual identity of a medical product. Whether to publish
 *     that is a brand-owner decision, and the decision taken was to remove it.
 *
 * The strip is conservative. It removes a `<metadata>` element ONLY when that element
 * contains nothing but a c2pa manifest, so an SVG carrying legitimate metadata (licence,
 * author, RDF) is left alone and reported rather than silently altered. Drawing content —
 * `<path>`, `<svg>` attributes, `viewBox` — is never touched, and the script verifies that
 * the path count and viewBox are identical before and after.
 *
 * Usage:
 *   php tools/branding/strip-svg-provenance.php [--dry-run] [--root=DIR]
 *
 * Exit: 0 success, 1 a file failed verification (nothing written for that file), 2 usage.
 */

final class SvgProvenanceStripper
{
    /** Matches a whole metadata element, non-greedy, any namespace prefix. */
    private const METADATA_PATTERN = '/\s*<(?:[\w.-]+:)?metadata\b[^>]*>.*?<\/(?:[\w.-]+:)?metadata>/is';

    private int $stripped = 0;

    private int $skipped = 0;

    private int $failed = 0;

    private int $bytesSaved = 0;

    public function __construct(
        private readonly string $root,
        private readonly bool $dryRun,
    ) {
    }

    public function run(): int
    {
        $brandDir = $this->root . '/brand';
        if (!is_dir($brandDir)) {
            fwrite(STDERR, "Brand directory not found: {$brandDir}\n");
            return 2;
        }

        foreach ($this->svgFiles($brandDir) as $path) {
            $this->process($path);
        }

        printf(
            "\n  %s: %d stripped, %d skipped, %d failed. %s bytes saved.\n",
            $this->dryRun ? 'Dry run' : 'Summary',
            $this->stripped,
            $this->skipped,
            $this->failed,
            number_format($this->bytesSaved),
        );

        return $this->failed > 0 ? 1 : 0;
    }

    /** @return list<string> */
    private function svgFiles(string $dir): array
    {
        $found = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $entry) {
            if ($entry instanceof SplFileInfo && strtolower($entry->getExtension()) === 'svg') {
                $found[] = $entry->getPathname();
            }
        }

        sort($found);

        return $found;
    }

    private function process(string $path): void
    {
        $relative = str_replace([$this->root . DIRECTORY_SEPARATOR, '\\'], ['', '/'], $path);
        $original = (string) file_get_contents($path);

        if (!preg_match(self::METADATA_PATTERN, $original, $match)) {
            printf("  skip   %-58s no metadata element\n", $relative);
            $this->skipped++;
            return;
        }

        // Conservative: only strip when the block is purely provenance. Anything else is
        // left in place and reported, because silently deleting licence or author
        // metadata would be a worse defect than the one being fixed.
        //
        // Detection is by NAMESPACE, not by prefix. The kit uses both `<c2pa:manifest>`
        // and `<ns1:manifest>` for the identical payload, with the prefix bound to
        // http://c2pa.org/manifest on the svg root — so a prefix match alone silently
        // misses two of the twenty-one files.
        if (!self::isProvenanceOnly($match[0], $original)) {
            printf("  skip   %-58s metadata present but not provenance-only\n", $relative);
            $this->skipped++;
            return;
        }

        $stripped = (string) preg_replace(self::METADATA_PATTERN, '', $original, 1);

        // Drop the now-orphaned namespace declaration on the root. Once the manifest is
        // gone nothing binds that prefix, so leaving it would keep a c2pa.org reference in
        // an asset that no longer contains any provenance data — misleading to anyone
        // grepping the kit, and it is what the verification step below refuses.
        $stripped = (string) preg_replace('/\s+xmlns:[\w.-]+="[^"]*c2pa\.org[^"]*"/i', '', $stripped);

        if (!$this->verify($original, $stripped, $relative)) {
            $this->failed++;
            return;
        }

        $saved = strlen($original) - strlen($stripped);
        $this->bytesSaved += $saved;
        $this->stripped++;

        printf(
            "  %-6s %-58s %7d -> %6d bytes (-%d%%)\n",
            $this->dryRun ? 'would' : 'strip',
            $relative,
            strlen($original),
            strlen($stripped),
            (int) round($saved * 100 / strlen($original)),
        );

        if (!$this->dryRun && file_put_contents($path, $stripped) === false) {
            fwrite(STDERR, "  FAILED to write {$relative}\n");
            $this->failed++;
        }
    }

    /**
     * The artwork must be provably untouched: same drawing elements, same geometry.
     *
     * A byte-count reduction alone would not prove that, so this compares the structural
     * facts a renderer actually uses.
     */
    private function verify(string $before, string $after, string $relative): bool
    {
        foreach (['path', 'circle', 'rect', 'ellipse', 'polygon', 'polyline', 'g'] as $element) {
            $countBefore = preg_match_all('/<' . $element . '\b/i', $before);
            $countAfter = preg_match_all('/<' . $element . '\b/i', $after);

            if ($countBefore !== $countAfter) {
                fwrite(STDERR, "  FAIL   {$relative}: <{$element}> count changed {$countBefore} -> {$countAfter}\n");
                return false;
            }
        }

        foreach (['viewBox', 'width', 'height'] as $attribute) {
            $valueBefore = self::attribute($before, $attribute);
            $valueAfter = self::attribute($after, $attribute);

            if ($valueBefore !== $valueAfter) {
                fwrite(STDERR, "  FAIL   {$relative}: {$attribute} changed\n");
                return false;
            }
        }

        if (stripos($after, 'c2pa') !== false) {
            fwrite(STDERR, "  FAIL   {$relative}: c2pa content survived the strip\n");
            return false;
        }

        // Must still parse as XML with an <svg> root.
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $parsed = $document->loadXML($after, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$parsed || $document->documentElement?->localName !== 'svg') {
            fwrite(STDERR, "  FAIL   {$relative}: result is not a well-formed SVG\n");
            return false;
        }

        return true;
    }

    /**
     * True when the metadata block contains nothing but a C2PA manifest.
     *
     * Resolves the element's prefix against the document's xmlns declarations, so the
     * check is on the namespace URI rather than on whatever prefix the exporter happened
     * to emit. A block holding any element outside that namespace is not provenance-only
     * and is left alone.
     */
    private static function isProvenanceOnly(string $block, string $document): bool
    {
        preg_match_all('/<([\w.-]+):([\w.-]+)\b/', $block, $matches, PREG_SET_ORDER);

        if ($matches === []) {
            return false;
        }

        foreach ($matches as [, $prefix, $localName]) {
            if (strtolower($localName) !== 'manifest') {
                return false;
            }

            $pattern = '/xmlns:' . preg_quote($prefix, '/') . '="([^"]*)"/i';
            if (preg_match($pattern, $document, $namespace) !== 1) {
                return false;
            }

            if (stripos($namespace[1], 'c2pa.org') === false) {
                return false;
            }
        }

        return true;
    }

    private static function attribute(string $svg, string $name): ?string
    {
        return preg_match('/<svg\b[^>]*\b' . $name . '="([^"]*)"/i', $svg, $m) === 1 ? $m[1] : null;
    }
}

$exit = (static function (array $argv): int {
    $root = dirname(__DIR__, 2);
    $dryRun = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--dry-run') {
            $dryRun = true;
        } elseif (str_starts_with($argument, '--root=')) {
            $root = rtrim(substr($argument, 7), '/\\');
        } else {
            fwrite(STDERR, "Unknown option: {$argument}\n");
            fwrite(STDERR, "Usage: php strip-svg-provenance.php [--dry-run] [--root=DIR]\n");
            return 2;
        }
    }

    return (new SvgProvenanceStripper($root, $dryRun))->run();
})($argv);

exit($exit);
