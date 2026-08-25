<?php

/**
 * Repository-level guardrails for locked Thiqa branding invariants.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Guardrail;

use OpenEMR\Common\Session\SessionUtil;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\SkippedTest;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class BrandingGovernanceGuardTest extends TestCase
{
    private const EXPECTED_SESSION_IDENTITIES = [
        'CORE_SESSION_ID' => 'OpenEMR',
        'OAUTH_SESSION_ID' => 'authserverOpenEMR',
        'API_SESSION_ID' => 'apiOpenEMR',
        'PORTAL_SESSION_ID' => 'PortalOpenEMR',
        'SETUP_SESSION_ID' => 'setupOpenEMR',
    ];

    private const REQUIRED_THEME_ENTRIES = [
        'style_light',
        'style_dark',
        'compact_style_light',
        'compact_style_dark',
        'rtl_style_light',
        'rtl_style_dark',
        'rtl_compact_style_light',
        'rtl_compact_style_dark',
    ];

    /**
     * The four inherited themes locked Q77 excludes from the Saudi product.
     *
     * Held separately from FORBIDDEN_THEME_ENTRIES because that list is entry NAMES (a
     * build-config concern) while this one is matched against compiled FILENAMES in the
     * deployed directory (what Q77 actually constrains).
     *
     * @var list<string>
     */
    private const FORBIDDEN_THEME_NAMES = ['solar', 'manila', 'cobalt_blue', 'forest_green'];

    private const FORBIDDEN_THEME_ENTRIES = [
        'style_solar',
        'style_manila',
        'style_cobalt_blue',
        'style_forest_green',
        'compact_style_solar',
        'compact_style_manila',
        'compact_style_cobalt_blue',
        'compact_style_forest_green',
        'rtl_style_solar',
        'rtl_style_manila',
        'rtl_style_cobalt_blue',
        'rtl_style_forest_green',
        'rtl_compact_style_solar',
        'rtl_compact_style_manila',
        'rtl_compact_style_cobalt_blue',
        'rtl_compact_style_forest_green',
    ];

    /**
     * Explicit signal that this environment is REQUIRED to have built public/themes/.
     *
     * S3-P2-36: the deployed-theme guard used to decide between "check" and "skip" purely on
     * whether the directory happened to exist. `/public/themes/*` is gitignored, CI never ran
     * a theme build, so the answer in every CI leg was "absent" and the locked Q77 check
     * skipped itself while the job reported green. The environment must therefore *declare*
     * its obligation rather than have it inferred: with this set to '1' an absent directory
     * is a hard failure; without it, the skip survives for developer hosts that legitimately
     * build off-tree (see CLAUDE.local.md section 6 — on the Windows host the build runs in
     * C:\openemr-stack\build and only the artefacts are copied back).
     */
    private const DEPLOYED_THEMES_REQUIRED_ENV = 'OPENEMR_DEPLOYED_THEMES_REQUIRED';

    private const ISOLATED_TESTS_WORKFLOW = '.github/workflows/isolated-tests.yml';

    /**
     * Temporary fixture directories created by the negative-control tests.
     *
     * @var list<string>
     */
    private array $fixtureDirectories = [];

    protected function tearDown(): void
    {
        foreach ($this->fixtureDirectories as $directory) {
            self::removeDirectory($directory);
        }

        $this->fixtureDirectories = [];

        parent::tearDown();
    }

    /**
     * Locked Q12: "Public `?site=` tenant selection is prohibited."
     *
     * The Tier 2 token endpoint is an $ignoreAuth entry point, and interface/globals.php
     * gives $_GET['site'] precedence over the session on every such page. It must therefore
     * drop the parameter BEFORE bootstrap, or it becomes both a tenant selector and — via
     * globals.php's clearSession()+redirect on a site mismatch — a cross-origin logout
     * vector, since a stylesheet URL is a natural <link> embed target.
     *
     * Asserted on source order rather than behaviour because the endpoint cannot be
     * exercised without a full application bootstrap, which the isolated suite has no
     * database for. See docs/RebrandingBugs.md RB-06.
     */
    public function testTokenEndpointRefusesASiteParameterBeforeBootstrap(): void
    {
        $source = self::tokenEndpointSource();

        $guardAt = strpos($source, "filter_input(INPUT_GET, 'site') !== null");
        $bootstrapAt = strpos($source, "require_once __DIR__ . '/../../../../globals.php';");

        self::assertNotFalse($guardAt, 'The token endpoint must reject a `site` query parameter (locked Q12).');
        self::assertNotFalse($bootstrapAt, 'The token endpoint must bootstrap through interface/globals.php.');
        self::assertLessThan(
            $bootstrapAt,
            $guardAt,
            'The `site` guard must run BEFORE globals.php, or globals.php has already resolved the '
            . 'tenant from the query parameter and the guard is worthless.',
        );
        self::assertStringContainsString(
            'http_response_code(400)',
            $source,
            'A `site` parameter must be refused outright, not silently ignored.',
        );
    }

    /**
     * An anonymous stylesheet fetch must not be handed a session cookie.
     *
     * ini_set('session.use_cookies', '0') does not achieve this — SessionConfiguration
     * passes 'use_cookies' => true directly to session_start(), and per-call options beat
     * ini settings — so the endpoint suppresses the response header instead, and only when
     * the request arrived without a session. See docs/RebrandingBugs.md RB-06.
     */
    public function testTokenEndpointSuppressesSessionCookiesForAnonymousRequests(): void
    {
        $source = self::tokenEndpointSource();

        self::assertStringContainsString(
            "header_remove('Set-Cookie');",
            $source,
            'The token endpoint must not hand a session cookie to an anonymous fetch.',
        );
        self::assertStringContainsString(
            '$arrivedWithSession',
            $source,
            'Cookie suppression must be conditional on the request having arrived without a session, '
            . 'so a real authenticated request is left untouched.',
        );
    }

    private static function tokenEndpointSource(): string
    {
        $path = self::repositoryRoot()
            . '/interface/modules/custom_modules/oe-module-skyeagle-branding/public/branding-tokens.php';

        $contents = file_get_contents($path);
        self::assertNotFalse($contents, 'branding-tokens.php must be readable.');

        return $contents;
    }

    /**
     * The dark-variant marks LogoOverrideListener resolves must actually be installed.
     *
     * Extension point E3 is wired to `<module>/public/logos/dark/<slot>/logo.*`. That
     * directory did not exist, so `firstExistingAsset()` returned null for every slot and
     * the listener was a permanent no-op — the Saudi Dark theme rendered light-optimised
     * marks (a navy wordmark on a #0B1220 surface). The failure was silent because
     * declining is the listener's correct behaviour when it has nothing to offer.
     * Recorded as docs/RebrandingBugs.md RB-10.
     *
     * Asserted on presence rather than behaviour: the listener's own logic is covered by
     * LogoOverrideListenerTest, and what regressed here was the *assets*, not the code.
     *
     * @param non-empty-string $slot
     */
    #[DataProvider('darkVariantSlotProvider')]
    public function testDarkVariantMarksAreInstalled(string $slot): void
    {
        $path = self::repositoryRoot()
            . '/interface/modules/custom_modules/oe-module-skyeagle-branding/public/logos/dark/'
            . $slot . '/logo.svg';

        self::assertFileExists(
            $path,
            'Dark-variant mark missing for slot "' . $slot . '". LogoOverrideListener silently '
            . 'declines when the asset is absent, so the dark theme falls back to the light mark. '
            . 'Reinstall with: php tools/branding/install-assets.php --site=<site>',
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function darkVariantSlotProvider(): array
    {
        return [
            'navbar symbol' => ['core/menu/primary'],
            'login wordmark' => ['core/login/primary'],
            'login secondary' => ['core/login/secondary'],
        ];
    }

    public function testMachineFacingSessionIdentitiesRemainByteExact(): void
    {
        $reflection = new \ReflectionClass(SessionUtil::class);

        foreach (self::EXPECTED_SESSION_IDENTITIES as $name => $expected) {
            self::assertSame($expected, $reflection->getConstant($name), $name . ' must remain byte-exact.');
        }
    }

    #[DataProvider('requiredThemeEntryProvider')]
    public function testSaudiBuildRetainsEveryApprovedThemeEntry(string $entry): void
    {
        self::assertMatchesRegularExpression(
            '/^\s*' . preg_quote($entry, '/') . '\s*:/m',
            self::webpackThemeMap(),
        );
    }

    #[DataProvider('forbiddenThemeEntryProvider')]
    public function testSaudiBuildExcludesEveryForbiddenThemeEntry(string $entry): void
    {
        self::assertDoesNotMatchRegularExpression(
            '/^\s*' . preg_quote($entry, '/') . '\s*:/m',
            self::webpackThemeMap(),
        );
    }

    // -----------------------------------------------------------------------------------
    // Layer 1 — projection. Runs in EVERY environment, built or not.
    //
    // Exactly two things deposit a stylesheet in public/themes/:
    //   1. webpack.themes.js's `entry:` map      → public/themes/<entry-name>.css
    //      (output.filename is "[name].css"; MiniCssExtractPlugin filename "[name].css";
    //      a "misc/x" entry name therefore lands at public/themes/misc/x.css)
    //   2. scripts/sync-css.js                   → copies interface/themes/*.css verbatim
    //      (and webpack's output.clean.keep whitelist is exactly those three filenames)
    //
    // Projecting that set and constraining it gives real Q77 coverage with no build at all.
    // It cannot replace the on-disk check below, because a stale artefact from an older
    // entry map is by definition invisible to the current entry map.
    // -----------------------------------------------------------------------------------

    /**
     * No configuration the build reads can produce one of Q77's four excluded themes.
     */
    public function testTheProjectedDeployedThemeSetCanContainNoForbiddenStylesheet(): void
    {
        $projected = self::projectedDeployedStylesheets();

        self::assertGreaterThanOrEqual(
            20,
            count($projected),
            'Implausibly few projected stylesheets. An empty projection would satisfy the forbidden '
            . 'check vacuously, so the parse itself has to be non-vacuous before the check means '
            . 'anything.',
        );

        self::assertSame(
            [],
            self::forbiddenAmong($projected),
            'Locked Q77: the build configuration itself would deposit an excluded theme in '
            . 'public/themes/. Prune it from webpack.themes.js (entry map) or from '
            . 'interface/themes/*.css (copied verbatim by scripts/sync-css.js).',
        );
    }

    /**
     * The projection is only meaningful if it still contains the themes the product needs.
     */
    public function testTheProjectedDeployedThemeSetContainsEveryRequiredTheme(): void
    {
        $projected = self::projectedDeployedStylesheets();

        foreach (self::REQUIRED_THEME_ENTRIES as $entry) {
            self::assertContains(
                $entry . '.css',
                $projected,
                'The build no longer produces ' . $entry . '.css, which interface/globals.php resolves '
                . 'via file_exists(). Losing it degrades the product silently rather than loudly.',
            );
        }
    }

    // -----------------------------------------------------------------------------------
    // Layer 2 — the real deployed directory. Mandatory wherever the CI signal says so.
    // -----------------------------------------------------------------------------------

    /**
     * Locked Q77 constrains the DEPLOYED directory, not the entry map.
     *
     * Its words are: the surplus themes' "user-selectable CSS artifacts MUST NOT **exist**
     * in the deployed `public/themes/` directory". Pruning `webpack.themes.js` is how that
     * is achieved, but the two are not the same claim — webpack's output cleaning applies
     * to the build workspace, and the documented deploy step copies without deleting, so a
     * stylesheet built before the entry map was pruned can survive at the destination
     * indefinitely. `interface/globals.php` gates theme selection only on `file_exists()`
     * (line 483 at the time of writing), so a stale `globals`/`user_settings` value would
     * then resolve it. See docs/RebrandingBugs.md RB-07 and RB-08.
     *
     * This asserts the thing Q77 actually says. It is check **V-04**'s first half.
     */
    public function testDeployedThemeDirectoryContainsNoForbiddenStylesheet(): void
    {
        $audit = self::auditThemeDirectory(self::deployedThemeDirectory(), self::projectedDeployedStylesheets());

        self::assertSame(
            [],
            $audit['forbidden'],
            'Locked Q77: these stylesheets must not exist in the deployed public/themes/ directory. '
            . 'Webpack no longer builds them, so their presence means a stale artefact survived a deploy '
            . 'that copied without purging — see docs/branding/runbook.md section 4.',
        );
    }

    /**
     * The stale-artefact failure mode is not limited to Q77's four names.
     *
     * `robocopy /E` deletes nothing at the destination and webpack's `output.clean` only
     * tidies the build workspace, so ANY stylesheet the current configuration cannot produce
     * is a survivor of an older build — and `interface/globals.php`'s `file_exists()` gate
     * will resolve any of them from a stale `globals`/`user_settings` value, not merely the
     * four named ones.
     */
    public function testDeployedThemeDirectoryContainsNoStaleOrSurplusStylesheet(): void
    {
        $audit = self::auditThemeDirectory(self::deployedThemeDirectory(), self::projectedDeployedStylesheets());

        self::assertSame(
            [],
            $audit['surplus'],
            'These stylesheets exist in public/themes/ but nothing in webpack.themes.js or '
            . 'interface/themes/*.css can produce them, so they are stale artefacts of an older '
            . 'build. Purge public/themes/ before re-copying — see CLAUDE.local.md section 6.',
        );
    }

    /**
     * The mirror direction: a deploy that dropped a required stylesheet is equally broken.
     */
    public function testDeployedThemeDirectoryContainsEveryStylesheetTheBuildProduces(): void
    {
        $audit = self::auditThemeDirectory(self::deployedThemeDirectory(), self::projectedDeployedStylesheets());

        self::assertSame(
            [],
            $audit['missing'],
            'The build configuration produces these stylesheets but they are absent from '
            . 'public/themes/, so the deploy is incomplete and interface/globals.php will fall '
            . 'back for every user whose stored theme is one of them.',
        );
    }

    // -----------------------------------------------------------------------------------
    // Layer 3 — the CI wiring itself.
    //
    // The signal only helps if CI actually raises it, on the same leg that actually built
    // the directory, before the tests run. Nothing else in the repo asserts that, and the
    // original defect was precisely "the check runs in no job while reporting green".
    // -----------------------------------------------------------------------------------

    public function testCiBuildsTheDeployedThemesAndDeclaresThemMandatory(): void
    {
        $parsed = Yaml::parseFile(self::repositoryRoot() . '/' . self::ISOLATED_TESTS_WORKFLOW);
        self::assertIsArray($parsed);

        $jobs = $parsed['jobs'] ?? null;
        self::assertIsArray($jobs);

        $job = $jobs['isolated-tests'] ?? null;
        self::assertIsArray($job, 'The isolated-tests job must still exist.');

        $strategy = $job['strategy'] ?? null;
        self::assertIsArray($strategy);

        $matrix = $strategy['matrix'] ?? null;
        self::assertIsArray($matrix);

        $rawLegs = $matrix['php-version'] ?? null;
        self::assertIsArray($rawLegs);

        $legs = [];
        foreach ($rawLegs as $leg) {
            self::assertIsString($leg, 'Matrix php-version legs must be quoted strings.');
            $legs[] = $leg;
        }

        $steps = $job['steps'] ?? null;
        self::assertIsArray($steps);

        $buildIndex = null;
        $buildLeg = null;
        $testIndex = null;
        $testEnv = null;

        foreach (array_values($steps) as $index => $step) {
            if (!is_array($step)) {
                continue;
            }

            $run = $step['run'] ?? null;
            if (!is_string($run)) {
                continue;
            }

            if (str_contains($run, 'npm run build')) {
                $buildIndex = $index;
                $condition = $step['if'] ?? null;
                self::assertIsString(
                    $condition,
                    'The theme build must be pinned to a single matrix leg with `if:`, or it costs '
                    . 'one npm install per leg.',
                );
                $buildLeg = self::pinnedLeg($condition);
            }

            if (str_contains($run, 'phpunit -c phpunit-isolated.xml')) {
                $testIndex = $index;
                $testEnv = $step['env'] ?? null;
            }
        }

        self::assertIsInt(
            $buildIndex,
            'No step in the isolated-tests job builds public/themes/. Without it the deployed-theme '
            . 'guard has nothing to inspect and locked Q77 is enforced by nothing in CI.',
        );
        self::assertIsString($buildLeg);
        self::assertIsInt($testIndex, 'The isolated-tests job must still run the isolated suite.');
        self::assertLessThan(
            $testIndex,
            $buildIndex,
            'The theme build must run BEFORE the isolated suite, or the guard inspects a directory '
            . 'that does not exist yet.',
        );
        self::assertContains(
            $buildLeg,
            $legs,
            sprintf(
                'The theme build is pinned to PHP %s, which is not in the matrix (%s), so it never '
                . 'runs and every leg reports green.',
                $buildLeg,
                implode(', ', $legs),
            ),
        );

        self::assertIsArray(
            $testEnv,
            'The isolated-test step must declare an `env:` block raising '
            . self::DEPLOYED_THEMES_REQUIRED_ENV . '.',
        );

        $signal = $testEnv[self::DEPLOYED_THEMES_REQUIRED_ENV] ?? null;
        self::assertIsString(
            $signal,
            'Without ' . self::DEPLOYED_THEMES_REQUIRED_ENV . ' the deployed-theme guard falls back '
            . 'to skipping itself, which is exactly the false green this wiring exists to close.',
        );

        $matches = [];
        self::assertSame(
            1,
            preg_match("~matrix\.php-version == '([^']+)'\s*&&\s*'1'~", $signal, $matches),
            'The signal must be raised by an explicit matrix-leg comparison yielding \'1\'.',
        );
        self::assertSame(
            $buildLeg,
            $matches[1],
            'The leg told public/themes/ is mandatory must be the same leg that built it; otherwise '
            . 'one leg fails on an absent directory while the leg that has one never checks it.',
        );
    }

    // -----------------------------------------------------------------------------------
    // Negative controls. These drive the SAME helpers the live checks above use, against
    // synthetic directories under sys_get_temp_dir(), so a gate that stopped discriminating
    // fails here rather than sailing through on an empty input set.
    // -----------------------------------------------------------------------------------

    public function testTheAuditFlagsAForbiddenStylesheet(): void
    {
        $directory = $this->fixtureThemeDirectory(['style_light.css', 'misc/rules.css', 'style_solar.css']);

        $audit = self::auditThemeDirectory($directory, ['misc/rules.css', 'style_light.css']);

        self::assertSame(['style_solar.css'], $audit['forbidden']);
        self::assertSame(['style_solar.css'], $audit['surplus']);
        self::assertSame([], $audit['missing']);
    }

    public function testTheAuditFlagsAStaleStylesheetThatIsNotOneOfQ77sFourNames(): void
    {
        $directory = $this->fixtureThemeDirectory(['style_light.css', 'style_gilded_lily.css']);

        $audit = self::auditThemeDirectory($directory, ['style_light.css']);

        self::assertSame([], $audit['forbidden'], 'It is not one of the four names, by construction.');
        self::assertSame(
            ['style_gilded_lily.css'],
            $audit['surplus'],
            'Any artefact the current configuration cannot produce is stale, and file_exists() '
            . 'resolves it just as happily as one of the four named themes.',
        );
    }

    public function testTheAuditFlagsAMissingRequiredStylesheet(): void
    {
        $directory = $this->fixtureThemeDirectory(['style_light.css']);

        $audit = self::auditThemeDirectory($directory, ['style_dark.css', 'style_light.css']);

        self::assertSame(['style_dark.css'], $audit['missing']);
        self::assertSame([], $audit['surplus']);
        self::assertSame([], $audit['forbidden']);
    }

    public function testTheAuditAcceptsAFaithfullyDeployedDirectory(): void
    {
        $directory = $this->fixtureThemeDirectory(['style_light.css', 'style_dark.css', 'misc/rules.css']);

        $audit = self::auditThemeDirectory($directory, ['misc/rules.css', 'style_dark.css', 'style_light.css']);

        self::assertSame(['forbidden' => [], 'surplus' => [], 'missing' => []], $audit);
    }

    /**
     * The whole point of S3-P2-36: "required but absent" must be red, not green-with-a-skip.
     */
    public function testAnAbsentThemeDirectoryFailsRatherThanSkipsWhenDeclaredMandatory(): void
    {
        $absent = $this->fixtureThemeDirectory([]) . '/never-created';
        self::assertDirectoryDoesNotExist($absent);

        try {
            self::resolveDeployedThemeDirectory($absent, '1');
        } catch (AssertionFailedError $error) {
            self::assertNotInstanceOf(
                SkippedTest::class,
                $error,
                'A mandatory-but-absent public/themes/ must FAIL. Skipping is how this check reported '
                . 'green in every CI leg while enforcing nothing.',
            );
            self::assertStringContainsString(self::DEPLOYED_THEMES_REQUIRED_ENV, $error->getMessage());

            return;
        }

        self::fail('An absent public/themes/ was tolerated even though the environment declared it mandatory.');
    }

    /**
     * ...and the developer-host skip has to survive, or nobody can run the suite locally.
     */
    public function testAnAbsentThemeDirectoryStillSkipsWithoutTheSignal(): void
    {
        $absent = $this->fixtureThemeDirectory([]) . '/never-created';
        self::assertDirectoryDoesNotExist($absent);

        try {
            self::resolveDeployedThemeDirectory($absent, null);
        } catch (SkippedWithMessageException $skipped) {
            self::assertStringContainsString(self::DEPLOYED_THEMES_REQUIRED_ENV, $skipped->getMessage());

            return;
        }

        self::fail(
            'Without the signal an absent public/themes/ must skip: CLAUDE.local.md section 6 builds '
            . 'the themes off-tree on the Windows host, so the directory is legitimately unbuilt there.',
        );
    }

    public function testAPresentDirectoryIsUsedRegardlessOfTheSignal(): void
    {
        $directory = $this->fixtureThemeDirectory(['style_light.css']);

        self::assertSame($directory, self::resolveDeployedThemeDirectory($directory, null));
        self::assertSame($directory, self::resolveDeployedThemeDirectory($directory, '1'));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function requiredThemeEntryProvider(): array
    {
        return self::provider(self::REQUIRED_THEME_ENTRIES);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function forbiddenThemeEntryProvider(): array
    {
        return self::provider(self::FORBIDDEN_THEME_ENTRIES);
    }

    /**
     * @param list<string> $entries
     * @return array<string, array{string}>
     */
    private static function provider(array $entries): array
    {
        $cases = [];
        foreach ($entries as $entry) {
            $cases[$entry] = [$entry];
        }

        return $cases;
    }

    private static function repositoryRoot(): string
    {
        return dirname(__DIR__, 6);
    }

    /**
     * The matrix leg a GitHub Actions `if:` expression pins a step to.
     */
    private static function pinnedLeg(string $condition): string
    {
        $matches = [];
        self::assertSame(
            1,
            preg_match("~matrix\.php-version == '([^']+)'~", $condition, $matches),
            'Expected the step to be pinned with `matrix.php-version == \'<leg>\'`, got: ' . $condition,
        );

        return $matches[1];
    }

    private static function webpackThemeMap(): string
    {
        $contents = file_get_contents(self::repositoryRoot() . '/webpack.themes.js');
        self::assertNotFalse($contents, 'webpack.themes.js must be readable.');

        return $contents;
    }

    /**
     * Resolve the deployed theme directory, or decide loudly why there is none.
     *
     * Split from {@see self::deployedThemeDirectory()} so the negative controls can exercise
     * the actual decision with a directory and a signal of their choosing.
     */
    private static function resolveDeployedThemeDirectory(string $directory, ?string $signal): string
    {
        if (is_dir($directory)) {
            return $directory;
        }

        if ($signal === '1') {
            self::fail(sprintf(
                '%s=1 declares this environment builds the deployed themes, so an absent %s is a '
                . 'build failure rather than a reason to skip. Locked Q77 constrains what exists in '
                . 'that directory, and a skipped check constrains nothing while reporting green.',
                self::DEPLOYED_THEMES_REQUIRED_ENV,
                $directory,
            ));
        }

        self::markTestSkipped(sprintf(
            'public/themes/ is gitignored build output and has not been generated here. Any '
            . 'environment required to have built it must set %s=1, which turns this skip into a '
            . 'failure.',
            self::DEPLOYED_THEMES_REQUIRED_ENV,
        ));
    }

    private static function deployedThemeDirectory(): string
    {
        $signal = getenv(self::DEPLOYED_THEMES_REQUIRED_ENV);

        return self::resolveDeployedThemeDirectory(
            self::repositoryRoot() . '/public/themes',
            $signal === false ? null : $signal,
        );
    }

    /**
     * @param list<string> $projected
     * @return array{forbidden: list<string>, surplus: list<string>, missing: list<string>}
     */
    private static function auditThemeDirectory(string $directory, array $projected): array
    {
        $deployed = self::deployedStylesheets($directory);

        return [
            'forbidden' => self::forbiddenAmong($deployed),
            'surplus' => array_values(array_diff($deployed, $projected)),
            'missing' => array_values(array_diff($projected, $deployed)),
        ];
    }

    /**
     * Every .css actually present under a deployed theme directory, as posix relative paths.
     *
     * @return list<string>
     */
    private static function deployedStylesheets(string $directory): array
    {
        $prefixLength = strlen($directory) + 1;
        $found = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $entry) {
            if (!$entry instanceof \SplFileInfo || !$entry->isFile()) {
                continue;
            }

            if (!str_ends_with($entry->getFilename(), '.css')) {
                continue;
            }

            $found[] = str_replace('\\', '/', substr($entry->getPathname(), $prefixLength));
        }

        sort($found);

        return $found;
    }

    /**
     * @param list<string> $files
     * @return list<string>
     */
    private static function forbiddenAmong(array $files): array
    {
        $hits = [];

        foreach ($files as $file) {
            foreach (self::FORBIDDEN_THEME_NAMES as $name) {
                if (str_contains($file, $name)) {
                    $hits[] = $file;
                    break;
                }
            }
        }

        return $hits;
    }

    /**
     * Every stylesheet the build is CAPABLE of depositing in public/themes/.
     *
     * @return list<string>
     */
    private static function projectedDeployedStylesheets(): array
    {
        $projected = [];

        foreach (self::webpackEntryNames() as $entry) {
            $projected[] = $entry . '.css';
        }

        foreach (self::staticallySyncedStylesheets() as $file) {
            $projected[] = $file;
        }

        $projected = array_values(array_unique($projected));
        sort($projected);

        return $projected;
    }

    /**
     * scripts/sync-css.js copies interface/themes/*.css into public/themes/ verbatim.
     *
     * @return list<string>
     */
    private static function staticallySyncedStylesheets(): array
    {
        $paths = glob(self::repositoryRoot() . '/interface/themes/*.css');
        self::assertNotFalse($paths, 'interface/themes/ must be readable.');
        self::assertNotSame(
            [],
            $paths,
            'scripts/sync-css.js has no sources at all, which almost certainly means this projection '
            . 'is reading the wrong directory.',
        );

        return array_map(basename(...), $paths);
    }

    /**
     * The entry names in webpack.themes.js — the thing that actually decides what gets built.
     *
     * Comments are stripped first so a commented-out entry is correctly read as "not built".
     *
     * @return list<string>
     */
    private static function webpackEntryNames(): array
    {
        $source = self::stripJsComments(self::webpackThemeMap());

        self::assertSame(
            1,
            substr_count($source, 'entry: {'),
            'webpack.themes.js must declare exactly one `entry: {` map; the parser cannot tell which '
            . 'of several is the theme build.',
        );

        $openAt = strpos($source, 'entry: {') + strlen('entry: ');
        $block = self::balancedBraceBlock($source, $openAt);

        $matches = [];
        preg_match_all(
            '/^[ \t]*(?:"([^"]+)"|\'([^\']+)\'|([A-Za-z_$][A-Za-z0-9_$]*))[ \t]*:/m',
            $block,
            $matches,
            // UNMATCHED_AS_NULL so the two alternatives that did not fire come back as null
            // rather than as an empty string that has to be told apart from a real capture.
            PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL,
        );

        $names = [];
        foreach ($matches as $set) {
            foreach ([1, 2, 3] as $group) {
                $candidate = $set[$group] ?? null;
                if (is_string($candidate)) {
                    $names[] = $candidate;
                }
            }
        }

        self::assertGreaterThanOrEqual(
            20,
            count($names),
            'Parsed implausibly few webpack entries. The parser has drifted from webpack.themes.js, '
            . 'and an empty entry list would satisfy every forbidden-theme assertion vacuously.',
        );

        return $names;
    }

    /**
     * Contents between the brace at $openAt and its match, ignoring braces inside strings.
     */
    private static function balancedBraceBlock(string $source, int $openAt): string
    {
        $depth = 0;
        $quote = null;
        $length = strlen($source);

        for ($i = $openAt; $i < $length; $i++) {
            $character = $source[$i];

            if ($quote !== null) {
                if ($character === '\\') {
                    $i++;
                    continue;
                }
                if ($character === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($character === '"' || $character === "'" || $character === '`') {
                $quote = $character;
                continue;
            }

            if ($character === '{') {
                $depth++;
                continue;
            }

            if ($character === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $openAt + 1, $i - $openAt - 1);
                }
            }
        }

        self::fail('Unbalanced braces in webpack.themes.js `entry:` map.');
    }

    private static function stripJsComments(string $source): string
    {
        $out = '';
        $length = strlen($source);
        $quote = null;

        for ($i = 0; $i < $length; $i++) {
            $character = $source[$i];

            if ($quote !== null) {
                $out .= $character;
                if ($character === '\\' && $i + 1 < $length) {
                    $out .= $source[++$i];
                    continue;
                }
                if ($character === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($character === '"' || $character === "'" || $character === '`') {
                $quote = $character;
                $out .= $character;
                continue;
            }

            if ($character === '/' && $i + 1 < $length && $source[$i + 1] === '/') {
                while ($i < $length && $source[$i] !== "\n") {
                    $i++;
                }
                $out .= "\n";
                continue;
            }

            if ($character === '/' && $i + 1 < $length && $source[$i + 1] === '*') {
                $end = strpos($source, '*/', $i + 2);
                $i = $end === false ? $length : $end + 1;
                continue;
            }

            $out .= $character;
        }

        return $out;
    }

    /**
     * @param list<string> $relativePaths posix-relative stylesheet paths to materialise
     */
    private function fixtureThemeDirectory(array $relativePaths): string
    {
        $directory = sys_get_temp_dir() . '/openemr-themes-fixture-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directory, 0o777, true), 'Could not create fixture directory.');
        $this->fixtureDirectories[] = $directory;

        foreach ($relativePaths as $relativePath) {
            $target = $directory . '/' . $relativePath;
            $parent = dirname($target);
            if (!is_dir($parent)) {
                self::assertTrue(mkdir($parent, 0o777, true), 'Could not create fixture subdirectory.');
            }

            self::assertNotFalse(
                file_put_contents($target, "/* fixture */\n"),
                'Could not write fixture stylesheet ' . $relativePath . '.',
            );
        }

        return $directory;
    }

    private static function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $entry) {
            if (!$entry instanceof \SplFileInfo) {
                continue;
            }

            if ($entry->isDir()) {
                rmdir($entry->getPathname());
                continue;
            }

            unlink($entry->getPathname());
        }

        rmdir($directory);
    }
}
