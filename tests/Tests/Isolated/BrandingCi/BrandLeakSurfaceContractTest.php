<?php

/**
 * Regression contract: the set of translatable strings naming the upstream product is closed.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use OpenEMR\Common\Branding\ProductIdentity;
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
 * comments cannot fool it.
 *
 * ## Finding B2 — what this test did not cover, and now does
 *
 * Three separate holes were found together, and each one alone was enough to let a leak through:
 *
 * 1. **The repository root was not scanned at all.** `admin.php`, `sql_patch.php` and
 *    `ippf_upgrade.php` all answer an *unauthenticated* HTTP request and all three printed a
 *    hardcoded product name. `setup.php`, whose ten literals were hand-converted to close finding
 *    S3-P1-33, had no regression guard behind it either — a literal reintroduced there passed CI
 *    silently. The root is now scanned as a fourteen-file, non-recursive list of entry points.
 *
 * 2. **Both extractors had false negatives**, documented on
 *    {@see self::collectFromPhp()} and {@see self::collectFromTwig()}. In the Twig case the
 *    docblock asserted a property the pattern did not implement.
 *
 * 3. **Nothing guarded the *current* product name.** Every assertion here looks for the
 *    *upstream* name, so it could never have caught `<title>` printing the configured brand as a
 *    literal — which is exactly what the three root pages did.
 *    {@see self::testNoRepositoryRootEntryPointHardcodesTheProductName()} closes that, and it
 *    reads the name from the generated identity artefact rather than naming a brand itself, so
 *    it keeps working across a rename.
 */
#[Group('isolated')]
final class BrandLeakSurfaceContractTest extends TestCase
{
    /** The name whose appearance in a translatable string is the leak being guarded against. */
    private const UPSTREAM_PRODUCT = 'OpenEMR';

    /** Translation entry points whose arguments are translatable literals. */
    private const TRANSLATION_FUNCTIONS = ['xl', 'xlt', 'xla', 'xlj', 'xlp', 'xlpt'];

    /** Directories that ship user-visible strings, scanned recursively. */
    private const SCANNED_ROOTS = ['src', 'interface', 'library', 'templates', 'portal'];

    /**
     * Extensions treated as source that can carry a translatable literal.
     *
     * @var list<string>
     */
    private const SCANNED_EXTENSIONS = ['php', 'inc', 'phtml', 'twig'];

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
     * Finding B2. No repository-root entry point may print the configured product name literally.
     *
     * This is the half of B2 that the upstream-name scan above can never reach. `admin.php`,
     * `sql_patch.php` and `ippf_upgrade.php` each answer an anonymous HTTP request and each
     * printed the configured brand as a hardcoded literal, so after a rename all three showed the
     * *previous* brand to unauthenticated visitors indefinitely. Nothing failed, because every
     * existing assertion looks for the upstream name and these strings did not contain it.
     *
     * The root is where this class of defect concentrates, because these scripts run outside the
     * normal bootstrap: `admin.php` and `setup.php` have no database and no autoloader, so the
     * usual `xl()` / `xlp()` route is genuinely unavailable and hardcoding is the path of least
     * resistance. `ProductIdentity` (ADR-BRAND-005) exists to make it unnecessary.
     *
     * **The test names no brand.** It reads the current product name from the generated identity
     * artefact, which is the same authority the converted call sites read, so the guard survives
     * the rename it exists to protect.
     *
     * Scope is deliberately strings and inline HTML — the two things that can reach a response
     * body. Comments are not scanned: a comment cannot leak to a user, and the conversion
     * comments in these very files would otherwise trip their own guard.
     */
    public function testNoRepositoryRootEntryPointHardcodesTheProductName(): void
    {
        $productName = ProductIdentity::name();

        // Guard the guard. If the artefact is missing, ProductIdentity degrades to the upstream
        // name by design (it must never abort an installer), and scanning for that here would
        // both duplicate the test above and fire on every preserved upstream reference. Either
        // way the result would not mean what the assertion below claims it means.
        self::assertNotSame(
            '',
            $productName,
            'The generated product identity resolved to an empty name, so this scan would be '
            . 'vacuous. Re-run tools/branding/bin/generate-product-identity.php.',
        );
        self::assertNotSame(
            self::UPSTREAM_PRODUCT,
            $productName,
            'The generated product identity resolved to the upstream fallback, which means the '
            . 'artefact is missing or unusable and this scan would be measuring the wrong name.',
        );

        $offences = [];
        foreach ($this->repositoryRootEntryPoints() as $path) {
            $source = file_get_contents($path);
            if ($source === false || !str_contains($source, $productName)) {
                continue;
            }
            $relative = $this->relativePath($path);

            foreach ($this->emittableTokens($source) as [$text, $line]) {
                if (str_contains($text, $productName)) {
                    $offences[] = $relative . ':' . $line . ' -- ' . $this->abbreviate($text);
                }
            }
        }

        sort($offences);

        self::assertSame(
            [],
            $offences,
            'These repository-root entry points emit the configured product name as a literal, so '
            . 'a rename leaves them showing the previous brand — to anonymous visitors, in the '
            . 'case of the pages that set $ignoreAuth. Read the name instead: '
            . 'ProductIdentity::name() where there is no database or autoloader (admin.php, '
            . 'setup.php), xl_product_name() / xlp() everywhere interface/globals.php has run.',
        );
    }

    /**
     * Finding B2. Each shape below walked straight past the previous PHP extractor.
     *
     * A widened scan that happens to match nothing new proves nothing, and the tree is currently
     * clean, so there is no natural specimen to point at. These synthetic sources are the
     * negative control, kept rather than run once and deleted: they fail the moment the extractor
     * regresses to the single-first-argument form, which a transient injection could not do.
     *
     * The last two cases are the other direction — a method call and a function *declaration*
     * named `xl` are not translation call sites, and a scan that reported them would train
     * maintainers to ignore it.
     */
    public function testThePhpExtractorSeesTheShapesThatUsedToEvadeIt(): void
    {
        $source = <<<'SRC'
            <?php
            xl('Concatenated ' . 'OpenEMR copy');
            \xlt('Fully qualified OpenEMR call');
            xl('Plain heading', 0, 'OpenEMR prepended argument');
            xla(<<<'TXT'
                Nowdoc OpenEMR body
                TXT);
            xlt("Interpolated OpenEMR value $unused");
            $object->xl('Method OpenEMR call');
            function xlp($OpenEMR)
            {
                return $OpenEMR;
            }
            SRC;

        $found = [];
        $this->collectFromPhp($source, 'synthetic.php', $found);
        $literals = array_keys($found);

        // Containment, not equality, because that is how the gate itself matches: a heredoc body
        // arrives from the tokenizer with its source indentation intact, and pinning that here
        // would be pinning a tokenizer detail rather than the property under test.
        foreach (
            [
                'Concatenated OpenEMR copy',
                'Fully qualified OpenEMR call',
                'OpenEMR prepended argument',
                'Nowdoc OpenEMR body',
                'Interpolated OpenEMR value ',
            ] as $expected
        ) {
            self::assertTrue(
                $this->anyLiteralContains($literals, $expected),
                sprintf(
                    'The PHP extractor no longer sees "%s", so a leak written that way would pass '
                    . 'CI silently. It found: %s',
                    $expected,
                    implode(' | ', array_map($this->abbreviate(...), $literals)),
                ),
            );
        }

        self::assertFalse(
            $this->anyLiteralContains($literals, 'Method OpenEMR call'),
            'A method call is not a translation call site; reporting one trains maintainers to '
            . 'ignore this gate.',
        );
    }

    /**
     * @param list<string> $literals
     */
    private function anyLiteralContains(array $literals, string $fragment): bool
    {
        foreach ($literals as $literal) {
            if (str_contains($literal, $fragment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Finding B2. The previous Twig pattern excluded both quote characters from the literal body,
     * so a literal containing the *other* delimiter never matched — while its docblock claimed
     * the opposite. These are the two specimens that claim describes.
     */
    public function testTheTwigExtractorSeesLiteralsContainingTheOtherQuote(): void
    {
        $source = <<<'SRC'
            {{ "OpenEMR's own manual"|xlt }}
            {{ 'The "OpenEMR" project page'|xla }}
            {{ "Plain OpenEMR literal"|xlt }}
            SRC;

        $found = [];
        $this->collectFromTwig($source, 'synthetic.html.twig', $found);

        self::assertSame(
            [
                'OpenEMR\'s own manual',
                'The "OpenEMR" project page',
                'Plain OpenEMR literal',
            ],
            array_keys($found),
            'The Twig extractor must see a literal that contains the delimiter it was not opened '
            . 'with; the previous pattern excluded both quotes and silently skipped these.',
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

        foreach ($this->scannedFiles() as $path) {
            $source = file_get_contents($path);
            if ($source === false || !str_contains($source, self::UPSTREAM_PRODUCT)) {
                continue;
            }
            $relative = $this->relativePath($path);

            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'twig') {
                $this->collectFromTwig($source, $relative, $found);
                continue;
            }
            $this->collectFromPhp($source, $relative, $found);
        }

        ksort($found);

        return $cache = $found;
    }

    /**
     * Every file the leak scan reads: the recursive roots, plus the repository-root entry points.
     *
     * The root is listed non-recursively and on purpose. Recursing from the repository root would
     * pull in `vendor/`, `node_modules/`, `sites/`, `Documentation/` and the whole test tree, and
     * a gate that scans machine-dependent directories stops being deterministic — the same
     * reasoning that keeps the vendor modules out via {@see self::GITIGNORE_MODULE_PREFIX}. What
     * matters for finding B2 is the handful of scripts a web server will execute on an anonymous
     * request, and those all sit directly in the root.
     *
     * @return list<string> absolute paths
     */
    private function scannedFiles(): array
    {
        $excluded = $this->gitignoredModuleDirectories();
        $files = [];

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
                if (!in_array(strtolower($entry->getExtension()), self::SCANNED_EXTENSIONS, true)) {
                    continue;
                }
                $relative = $this->relativePath($entry->getPathname());
                foreach ($excluded as $prefix) {
                    if (str_starts_with($relative, $prefix)) {
                        continue 2;
                    }
                }
                $files[] = $entry->getPathname();
            }
        }

        foreach ($this->repositoryRootEntryPoints() as $path) {
            $files[] = $path;
        }

        return $files;
    }

    /**
     * The PHP files a web server can execute directly from the document root.
     *
     * @return list<string> absolute paths, sorted so failures are reported deterministically
     */
    private function repositoryRootEntryPoints(): array
    {
        $files = [];
        foreach (new \DirectoryIterator($this->root()) as $entry) {
            if (!$entry->isFile()) {
                continue;
            }
            if (!in_array(strtolower($entry->getExtension()), self::SCANNED_EXTENSIONS, true)) {
                continue;
            }
            $files[] = $entry->getPathname();
        }
        sort($files);

        // A floor: this list is the whole point of the widening, so an empty or implausibly short
        // one means the scan silently stopped covering the root rather than that the root emptied.
        self::assertGreaterThan(
            5,
            count($files),
            'The repository root yielded almost no entry points; the scan is not covering it.',
        );

        return $files;
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
     * Collects the literal text of every translatable argument passed to a translation function.
     *
     * ## What the previous version missed (finding B2)
     *
     * It looked for a bare `T_STRING` function name followed by a single
     * `T_CONSTANT_ENCAPSED_STRING` in first-argument position, and stopped. Four shapes walked
     * straight past it, and all four are shapes real code in this tree uses:
     *
     *  - **Concatenation.** `xl('Welcome to ' . 'OpenEMR')` — the commonest way long English copy
     *    gets wrapped in a source file. Now handled: every literal fragment at the argument's own
     *    nesting level is joined, so a name split across a concatenation is still one string.
     *  - **Heredoc and nowdoc arguments**, and double-quoted strings with interpolation, whose
     *    text arrives as `T_ENCAPSED_AND_WHITESPACE` rather than `T_CONSTANT_ENCAPSED_STRING`.
     *  - **Fully-qualified calls.** `\xl(...)` tokenises as `T_NAME_FULLY_QUALIFIED`; the old
     *    check compared against `T_STRING` only, so prefixing one backslash disabled the guard.
     *  - **Arguments after the first.** Upstream's `xl($constant, $mode, $prepend, $append)` puts
     *    caller-supplied text in `$prepend` / `$append`, and composition is this rebrand's own
     *    idiom, so that is precisely where a leak would land. Every top-level argument is now
     *    scanned independently, which costs nothing and needs no signature knowledge.
     *
     * It also now refuses to treat `->xl(`, `::xl(`, `new xl(` or `function xl(` as a call, which
     * the old scan did not check at all.
     *
     * ## What is still out of reach, stated rather than implied
     *
     * A literal is only decidable when it is literal. `xl($heading)`, `xl(self::COPY)` and
     * `xl('Welcome to ' . BRAND)` cannot be resolved by a tokenizer, and this test does not
     * pretend to. Concatenation *outside* the call — `xlt('Welcome to') . ' OpenEMR'` — is also
     * not caught, because the trailing fragment is not an argument to anything.
     *
     * Quote delimiters are stripped before joining so a concatenation reads as one string, but
     * nothing is unescaped: an escape sequence cannot change whether the substring is present,
     * and unescaping would only add a way for the extractor to be wrong.
     *
     * @param array<string, list<string>> $found
     */
    private function collectFromPhp(string $source, string $relative, array &$found): void
    {
        $tokens = @token_get_all($source);
        $count = count($tokens);
        $noise = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];
        $notACall = [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW];

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token)) {
                continue;
            }
            if ($token[0] !== T_STRING && $token[0] !== T_NAME_FULLY_QUALIFIED) {
                continue;
            }
            if (!in_array(ltrim($token[1], '\\'), self::TRANSLATION_FUNCTIONS, true)) {
                continue;
            }

            $previous = $i - 1;
            while ($previous >= 0 && is_array($tokens[$previous]) && in_array($tokens[$previous][0], $noise, true)) {
                $previous--;
            }
            if ($previous >= 0 && is_array($tokens[$previous]) && in_array($tokens[$previous][0], $notACall, true)) {
                continue;
            }

            $open = $i + 1;
            while ($open < $count && is_array($tokens[$open]) && in_array($tokens[$open][0], $noise, true)) {
                $open++;
            }
            if ($open >= $count || $tokens[$open] !== '(') {
                continue;
            }

            foreach ($this->argumentLiterals($tokens, $open, $count) as [$literal, $line]) {
                if (str_contains($literal, self::UPSTREAM_PRODUCT)) {
                    $found[$literal][] = $relative . ':' . $line;
                }
            }
        }
    }

    /**
     * Splits one argument list into its top-level arguments and returns each one's literal text.
     *
     * @param array<int, array{int, string, int}|string> $tokens
     * @return list<array{string, int}> literal text and the line its first fragment sits on
     */
    private function argumentLiterals(array $tokens, int $open, int $count): array
    {
        // `{` from string interpolation (`"{$a}"`, `"${a}"`) opens without a token the closing
        // `}` can be paired against by name, so both forms are counted here. Missing one would
        // let the closing brace drop the depth below the argument list and end the walk early.
        $openers = ['(', '[', '{'];
        $closers = [')', ']', '}'];
        $interpolationOpeners = [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES];

        $arguments = [];
        $fragments = [];
        $line = 0;
        $depth = 0;

        for ($j = $open; $j < $count; $j++) {
            $token = $tokens[$j];

            if (!is_array($token)) {
                if (in_array($token, $openers, true)) {
                    $depth++;
                    continue;
                }
                if (in_array($token, $closers, true)) {
                    $depth--;
                    if ($depth === 0) {
                        break;
                    }
                    continue;
                }
                if ($token === ',' && $depth === 1) {
                    $arguments[] = [implode('', $fragments), $line];
                    $fragments = [];
                    $line = 0;
                }
                continue;
            }

            if (in_array($token[0], $interpolationOpeners, true)) {
                $depth++;
                continue;
            }
            if ($depth !== 1) {
                continue;
            }
            if ($token[0] !== T_CONSTANT_ENCAPSED_STRING && $token[0] !== T_ENCAPSED_AND_WHITESPACE) {
                continue;
            }

            $fragments[] = $token[0] === T_CONSTANT_ENCAPSED_STRING
                ? substr($token[1], 1, -1)
                : $token[1];
            if ($line === 0) {
                $line = $token[2];
            }
        }

        $arguments[] = [implode('', $fragments), $line];

        return array_values(array_filter($arguments, static fn(array $a): bool => $a[0] !== ''));
    }

    /**
     * Matches the Twig filter form `{{ "literal"|xlt }}`.
     *
     * Finding B2: the previous pattern's docblock claimed "the quote character is backreferenced
     * so an apostrophe inside a double-quoted literal does not terminate the match early". The
     * backreference was there, but the body was `[^\'"]*`, which excludes **both** quote
     * characters — so `{{ "OpenEMR's manual"|xlt }}` never matched, and the docblock described an
     * intent the pattern did not implement. The body is now a tempered dot, `(?:(?!\1).)*`, which
     * excludes only the delimiter that actually opened the literal. That makes the original claim
     * true; it cannot over-run the closing quote either, since the delimiter is still the one
     * character the body may not consume.
     *
     * Two limits, stated rather than implied: a Twig-escaped delimiter inside a literal
     * (`'it\'s'`) still ends the body early, and `{{ "a" ~ "b"|xlt }}` binds the filter to the
     * last operand only, so a name split across a Twig concatenation is not seen.
     *
     * @param array<string, list<string>> $found
     */
    private function collectFromTwig(string $source, string $relative, array &$found): void
    {
        $pattern = '/([\'"])((?:(?!\1).)*' . self::UPSTREAM_PRODUCT . '(?:(?!\1).)*)\1\s*\|\s*('
            . implode('|', self::TRANSLATION_FUNCTIONS) . ')\b/';

        if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) === 0) {
            return;
        }

        foreach ($matches as $match) {
            $line = substr_count(substr($source, 0, $match[0][1]), "\n") + 1;
            $found[$match[2][0]][] = $relative . ':' . $line;
        }
    }

    /**
     * Every token of a PHP file whose text can reach a response body.
     *
     * Inline HTML counts, and matters most here: `admin.php` closes its `<?php` block before it
     * prints anything, so its title and heading were plain `T_INLINE_HTML` and no scan that
     * looked only at string literals would have seen them.
     *
     * @return list<array{string, int}> token text and its line
     */
    private function emittableTokens(string $source): array
    {
        $emittable = [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_INLINE_HTML];
        $out = [];

        foreach (@token_get_all($source) as $token) {
            if (!is_array($token) || !in_array($token[0], $emittable, true)) {
                continue;
            }
            $out[] = [$token[1], $token[2]];
        }

        return $out;
    }

    private function relativePath(string $absolute): string
    {
        return str_replace('\\', '/', substr($absolute, strlen($this->root()) + 1));
    }

    private function abbreviate(string $literal): string
    {
        $collapsed = trim((string) preg_replace('/\s+/', ' ', $literal));

        return strlen($collapsed) > 100 ? substr($collapsed, 0, 97) . '...' : $collapsed;
    }

    private function root(): string
    {
        return dirname(__DIR__, 4);
    }
}
