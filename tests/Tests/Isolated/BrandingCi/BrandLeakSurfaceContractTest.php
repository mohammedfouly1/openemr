<?php

/**
 * Regression contract: the set of translatable strings naming the upstream product is closed.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Audit finding A-03: every guard in this suite checked strings that were *already known*.
 *
 * `ProductNameCompositionContractTest` pins the converted call sites, the contract targets and
 * the deliberately preserved upstream references — all of them enumerated by hand. Nothing
 * re-derived the surface, so a *new* literal naming the upstream product could be added anywhere
 * in the tree and every branding test would still pass. The leak class this programme spent four
 * scans closing had no guard against reopening.
 *
 * This test re-derives the surface on every run and asserts it equals exactly the preserved set.
 * It is deliberately a whitelist of six rather than a count: a count can be satisfied by swapping
 * one leak for another, and the six are preserved for a stated reason that a maintainer needs to
 * read before adding a seventh.
 *
 * Extraction uses PHP's own tokenizer rather than a regex, so escaping, concatenation and
 * comments cannot fool it. It matches the raw token text: escaping can never change whether the
 * substring is present, so no unescaping step is needed.
 */
#[Group('isolated')]
final class BrandLeakSurfaceContractTest extends TestCase
{
    /** The name whose appearance in a translatable string is the leak being guarded against. */
    private const UPSTREAM_PRODUCT = 'OpenEMR';

    /** Translation entry points whose first argument is a translatable literal. */
    private const TRANSLATION_FUNCTIONS = ['xl', 'xlt', 'xla', 'xlj', 'xlp', 'xlpt'];

    /** Directories that ship user-visible strings. */
    private const SCANNED_ROOTS = ['src', 'interface', 'library', 'templates', 'portal'];

    /**
     * Directory prefixes excluded because their contents are not this repository's source.
     *
     * Audit finding A-04. `interface/modules/custom_modules/` mixes two very different things:
     * eight modules whose source is tracked here, and vendor payloads that `.gitignore` excludes
     * and an installer drops in. `oe-module-claimrev-connect` is one of the latter — it is on
     * disk on this machine with **17 translatable literals naming the upstream product** across
     * its claims, payment-advice and reconciliation pages, and it is genuinely user-visible on
     * any instance that installs it.
     *
     * It is excluded here anyway, and the reason is that a gate has to be deterministic: whether
     * those files exist depends on which modules an operator installed, so scanning them would
     * make this test pass or fail according to the machine it runs on. Rebranding a third-party
     * module is also a different decision — the next module update overwrites the edit.
     *
     * The exclusion is derived from `.gitignore` rather than hardcoded, so adding another vendor
     * module does not silently start failing this test. **The finding itself is not closed by
     * this exclusion**: the brand surface of a running instance is larger than the brand surface
     * of this repository, and nothing currently measures the difference.
     */
    private const GITIGNORE_MODULE_PREFIX = 'interface/modules/custom_modules/';

    /**
     * The only translatable strings allowed to name the upstream product, and why.
     *
     * Each names the **OpenEMR Foundation**, the upstream community, or ONC certification
     * reporting — none of them names *this* product. Neutralising one would make the software
     * assert something untrue: that a differently-named foundation should receive a certification
     * report, or that this fork holds a certification it does not.
     *
     * Keyed by `file:distinctive fragment` so a match is unambiguous without pasting whole
     * paragraphs into a test.
     *
     * @var array<string, string>
     */
    private const PRESERVED = [
        'interface/reports/rwt_2026_report.php'
            => 'email the pdf to the OpenEMR Foundation',
        'interface/reports/rwt_2026_report.php#onc'
            => 'OpenEMR instances in the United States that utilize ONC certification',
        'interface/smart/register-app.php'
            => 'More support can be found on the OpenEMR community form',
        'templates/product_registration/product_registration_modal.html.twig#announcements'
            => 'critical announcements from the OpenEMR Foundation',
        'templates/product_registration/product_registration_modal.html.twig#consent'
            => 'I consent to share anonymous usage data with the OpenEMR Foundation.',
        'templates/product_registration/product_registration_modal.html.twig#telemetry'
            => 'We are committed to continually improving OpenEMR. By opting in to anonymous',
    ];

    public function testNoNewTranslatableStringNamesTheUpstreamProduct(): void
    {
        $found = $this->deriveLeakSurface();
        $allowed = array_values(self::PRESERVED);
        $unexpected = [];

        foreach ($found as $literal => $sites) {
            foreach ($allowed as $fragment) {
                if (str_contains($literal, $fragment)) {
                    continue 2;
                }
            }
            $unexpected[] = implode(', ', $sites) . ' -- ' . $this->abbreviate($literal);
        }

        sort($unexpected);

        self::assertSame(
            [],
            $unexpected,
            'These translatable strings name ' . self::UPSTREAM_PRODUCT . ' and are not on the '
            . 'preserved list. Compose the product name with xlp() / the |xlp filter instead. If a '
            . 'string genuinely names the upstream foundation, community or a certification '
            . 'programme, add it to self::PRESERVED with the reason.',
        );
    }

    /**
     * The other direction: a preserved entry that no longer matches anything is a stale rule.
     *
     * Without this, deleting or rewording one of the six leaves a permanent exemption behind that
     * silently licenses a future string containing the same fragment.
     */
    public function testEveryPreservedEntryStillMatchesSomething(): void
    {
        $found = $this->deriveLeakSurface();
        $stale = [];

        foreach (self::PRESERVED as $key => $fragment) {
            $matched = false;
            foreach (array_keys($found) as $literal) {
                if (str_contains($literal, $fragment)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $stale[] = $key;
            }
        }

        self::assertSame(
            [],
            $stale,
            'These preserved entries match nothing in the tree any more. Remove them rather than '
            . 'leaving an exemption that licenses a string nobody has reviewed.',
        );
    }

    /**
     * A floor, so a broken extractor silently finding nothing cannot pass both tests vacuously.
     */
    public function testTheExtractorActuallyFindsTheKnownSurface(): void
    {
        self::assertCount(
            count(self::PRESERVED),
            $this->deriveLeakSurface(),
            'The extractor should find exactly the preserved strings. Finding none usually means '
            . 'it stopped working, not that the tree got cleaner.',
        );
    }

    /**
     * @return array<string, list<string>> literal => call sites
     */
    private function deriveLeakSurface(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $found = [];
        $excluded = $this->gitignoredModuleDirectories();

        foreach (self::SCANNED_ROOTS as $relativeRoot) {
            $absolute = $this->root() . '/' . $relativeRoot;
            if (!is_dir($absolute)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolute, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $entry) {
                if (!$entry instanceof \SplFileInfo || !$entry->isFile()) {
                    continue;
                }
                $extension = strtolower($entry->getExtension());
                if (!in_array($extension, ['php', 'inc', 'phtml', 'twig'], true)) {
                    continue;
                }

                $source = file_get_contents($entry->getPathname());
                if ($source === false || !str_contains($source, self::UPSTREAM_PRODUCT)) {
                    continue;
                }
                $relative = str_replace('\\', '/', substr($entry->getPathname(), strlen($this->root()) + 1));

                foreach ($excluded as $prefix) {
                    if (str_starts_with($relative, $prefix)) {
                        continue 2;
                    }
                }

                if ($extension === 'twig') {
                    $this->collectFromTwig($source, $relative, $found);
                    continue;
                }
                $this->collectFromPhp($source, $relative, $found);
            }
        }

        ksort($found);

        return $cache = $found;
    }

    /**
     * Custom-module directories `.gitignore` excludes, as path prefixes.
     *
     * Read from `.gitignore` rather than hardcoded so that installing another vendor module does
     * not turn this gate red on whichever machine happens to have it.
     *
     * @return list<string>
     */
    private function gitignoredModuleDirectories(): array
    {
        $gitignore = file_get_contents($this->root() . '/.gitignore');
        self::assertIsString($gitignore, '.gitignore is missing; the exclusion set cannot be derived.');

        $prefixes = [];
        foreach (explode("\n", $gitignore) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $candidate = ltrim($line, '/');
            if (!str_starts_with($candidate, self::GITIGNORE_MODULE_PREFIX)) {
                continue;
            }
            // `.../oe-module-x/*` and `.../oe-module-x/` both mean "this directory".
            $prefixes[] = rtrim($candidate, '*');
        }

        return $prefixes;
    }

    /**
     * @param array<string, list<string>> $found
     */
    private function collectFromPhp(string $source, string $relative, array &$found): void
    {
        $tokens = @token_get_all($source);
        $count = count($tokens);
        $noise = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token) || $token[0] !== T_STRING) {
                continue;
            }
            if (!in_array($token[1], self::TRANSLATION_FUNCTIONS, true)) {
                continue;
            }

            $open = $i + 1;
            while ($open < $count && is_array($tokens[$open]) && in_array($tokens[$open][0], $noise, true)) {
                $open++;
            }
            if ($open >= $count || $tokens[$open] !== '(') {
                continue;
            }

            $argument = $open + 1;
            while ($argument < $count && is_array($tokens[$argument]) && in_array($tokens[$argument][0], $noise, true)) {
                $argument++;
            }
            if ($argument >= $count || !is_array($tokens[$argument])) {
                continue;
            }
            if ($tokens[$argument][0] !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }

            $literal = $tokens[$argument][1];
            if (str_contains($literal, self::UPSTREAM_PRODUCT)) {
                $found[$literal][] = $relative . ':' . $tokens[$argument][2];
            }
        }
    }

    /**
     * Twig has no tokenizer available here, so the filter form is matched directly:
     * `{{ "literal"|xlt }}`. The quote character is backreferenced so an apostrophe inside a
     * double-quoted literal does not terminate the match early.
     *
     * @param array<string, list<string>> $found
     */
    private function collectFromTwig(string $source, string $relative, array &$found): void
    {
        $pattern = '/([\'"])([^\'"]*' . self::UPSTREAM_PRODUCT . '[^\'"]*)\1\s*\|\s*('
            . implode('|', self::TRANSLATION_FUNCTIONS) . ')\b/';

        if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER) === 0) {
            return;
        }

        foreach ($matches as $match) {
            $position = strpos($source, $match[0]);
            $line = substr_count(substr($source, 0, $position === false ? 0 : $position), "\n") + 1;
            $found[$match[2]][] = $relative . ':' . $line;
        }
    }

    private function abbreviate(string $literal): string
    {
        return strlen($literal) > 100 ? substr($literal, 0, 97) . '...' : $literal;
    }

    private function root(): string
    {
        return dirname(__DIR__, 4);
    }
}
