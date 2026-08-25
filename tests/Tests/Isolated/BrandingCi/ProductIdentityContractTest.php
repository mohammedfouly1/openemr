<?php

/**
 * Contract tests for the pre-database product identity artefact (ADR-BRAND-005).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use OpenEMR\Common\Branding\ProductIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Finding S3-P1-33. `setup.php` runs before the database exists, so every other product-name
 * mechanism in this repository is unavailable to it. This artefact is what replaced ten
 * hardcoded literals, and these are the properties the installer is now relying on.
 *
 * The tests deliberately exercise BEHAVIOUR rather than the shape of the generated file. A test
 * that asserted the artefact contains a particular line would pass just as happily if the
 * accessor stopped reading it, which is the false-green shape this programme keeps finding.
 */
final class ProductIdentityContractTest extends TestCase
{
    private string $scratch = '';

    protected function setUp(): void
    {
        $scratch = sys_get_temp_dir() . '/oe-product-identity-' . bin2hex(random_bytes(6));
        self::assertTrue(mkdir($scratch, 0o777, true), 'Could not create scratch directory.');
        $this->scratch = $scratch;
    }

    protected function tearDown(): void
    {
        if ($this->scratch === '' || !is_dir($this->scratch)) {
            return;
        }

        foreach (glob($this->scratch . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->scratch);
    }

    /** The shipped artefact resolves with no database, no globals bag and no network. */
    public function testTheShippedArtefactResolvesWithoutADatabase(): void
    {
        $identity = ProductIdentity::load(ProductIdentity::defaultArtefactPath());

        self::assertArrayHasKey('product_name', $identity);
        self::assertNotSame('', $identity['product_name']);
        self::assertSame($identity['product_name'], ProductIdentity::name());
    }

    /** A valid artefact is read, not merely tolerated. */
    public function testAValidArtefactSuppliesItsOwnValues(): void
    {
        $path = $this->writeArtefact([
            'product_name' => 'Contract Fixture',
            'product_website_url' => 'https://example.invalid/',
            'product_support_url' => 'https://example.invalid/support',
            'product_documentation_url' => 'https://example.invalid/docs',
        ]);

        $identity = ProductIdentity::load($path);

        self::assertSame('Contract Fixture', $identity['product_name']);
        self::assertSame('https://example.invalid/docs', $identity['product_documentation_url']);
    }

    /**
     * A missing artefact degrades; it does not throw.
     *
     * This is the inverse of the usual parse-don't-validate posture and S3-P0-28 is why: every
     * consumer is an installer, an upgrade path or a fatal-error reporter, and in all three
     * aborting is strictly worse than rendering a neutral name.
     */
    public function testAMissingArtefactFallsBackInsteadOfThrowing(): void
    {
        $identity = ProductIdentity::load($this->scratch . '/does-not-exist.php');

        self::assertNotSame('', $identity['product_name']);
    }

    /** A file that returns something other than an array is refused, not unpacked. */
    public function testAnArtefactThatDoesNotReturnAnArrayFallsBack(): void
    {
        $path = $this->scratch . '/not-an-array.php';
        file_put_contents($path, "<?php\n\nreturn 'a string';\n");

        $identity = ProductIdentity::load($path);

        self::assertNotSame('', $identity['product_name']);
        self::assertNotSame('a string', $identity['product_name']);
    }

    /** A key present but empty is treated as absent rather than rendered as nothing. */
    public function testAnEmptyValueFallsBackRatherThanRenderingBlank(): void
    {
        $path = $this->writeArtefact(['product_name' => '']);

        self::assertNotSame('', ProductIdentity::load($path)['product_name']);
    }

    /** A key of the wrong type never reaches a caller that has declared a string return. */
    public function testANonStringValueFallsBack(): void
    {
        $path = $this->scratch . '/wrong-type.php';
        file_put_contents($path, "<?php\n\nreturn ['product_name' => 42];\n");

        self::assertIsString(ProductIdentity::load($path)['product_name']);
        self::assertNotSame('42', ProductIdentity::load($path)['product_name']);
    }

    /** Every documented key resolves to a non-empty string, whatever the artefact omits. */
    public function testEveryKeyAlwaysResolvesToANonEmptyString(): void
    {
        $identity = ProductIdentity::load($this->writeArtefact([]));

        self::assertNotSame([], $identity);
        foreach ($identity as $key => $value) {
            self::assertIsString($value, $key . ' must be a string.');
            self::assertNotSame('', $value, $key . ' must never resolve empty.');
        }
    }

    /**
     * Generation is deterministic, which is what makes `--check` a meaningful drift gate.
     *
     * Asserted by regenerating in a subprocess and comparing bytes, rather than by trusting
     * the generator's own report about itself.
     */
    public function testGenerationIsByteDeterministic(): void
    {
        $repoRoot = dirname(__DIR__, 4);
        $artefact = $repoRoot . '/' . ProductIdentity::ARTEFACT_RELATIVE_PATH;
        self::assertFileExists($artefact);

        $before = file_get_contents($artefact);
        self::assertIsString($before);

        $exitCode = $this->runGenerator($repoRoot, ['--check']);

        self::assertSame(0, $exitCode, 'The committed artefact does not match what the generator produces.');
        self::assertSame($before, file_get_contents($artefact), '--check must not write.');
    }

    /**
     * The drift gate fails on a hand-edited artefact.
     *
     * S4B-12 / S4E-04. This test used to tamper with the REAL tracked artefact in place and
     * restore it in a `finally`. That was a genuine hazard, not a theoretical one, and two
     * independent auditors flagged it — one refused to run this suite at all because of it.
     * `finally` does not survive a signal, a fatal, an OOM kill or a closed terminal, and this
     * runs inside `composer branding-ci`, on developer machines and release boxes. The residue
     * it would leave is the quiet kind: a tampered `'XThiqa'` is a non-empty string, so
     * `ProductIdentity::resolve()` accepts it and every consumer serves it with no runtime
     * signal whatsoever.
     *
     * The generator already takes `--out-file`, so the whole exercise runs against a COPY in the
     * scratch directory and the tracked file is never opened for writing. The real artefact's
     * hash is asserted unchanged at the end — the safety property is now itself a tested
     * invariant rather than a promise in a docblock.
     */
    public function testTheDriftGateRejectsAHandEditedArtefact(): void
    {
        $repoRoot = dirname(__DIR__, 4);
        $tracked = $repoRoot . '/' . ProductIdentity::ARTEFACT_RELATIVE_PATH;

        $original = file_get_contents($tracked);
        self::assertIsString($original);
        $trackedHashBefore = hash('sha256', $original);

        $copy = $this->scratch . '/product_identity.generated.php';
        self::assertNotFalse(file_put_contents($copy, $original));

        // The untouched copy must verify, or the tamper below would prove nothing.
        self::assertSame(
            0,
            $this->runGenerator($repoRoot, ['--check', '--out-file=' . $copy]),
            'A faithful copy of the artefact must verify clean.',
        );

        $tampered = str_replace("'product_name' => '", "'product_name' => 'X", $original);
        self::assertNotSame($original, $tampered, 'The tamper did not change the artefact.');
        self::assertNotFalse(file_put_contents($copy, $tampered));

        self::assertSame(
            3,
            $this->runGenerator($repoRoot, ['--check', '--out-file=' . $copy]),
            '--check must exit 3 on drift.',
        );

        self::assertSame(
            $trackedHashBefore,
            hash('sha256', (string) file_get_contents($tracked)),
            'This test must never write to the tracked artefact.',
        );
    }

    /**
     * The tracked artefact is not the compiled-in fallback.
     *
     * S4B-12's second half. `ProductIdentity::FALLBACK` names the upstream product, `resolve()`
     * degrades to it on any unusable key with nothing but an `error_log()`, and
     * `library/globals.inc.php` writes that value straight into the `globals` table at install
     * time. So a silently-degraded artefact would install an upstream-branded product and every
     * other test here would still pass, because they all assert only non-emptiness.
     */
    public function testTheShippedArtefactIsNotSilentlyTheFallback(): void
    {
        $identity = ProductIdentity::load(ProductIdentity::defaultArtefactPath());

        $reflection = new \ReflectionClass(ProductIdentity::class);
        /** @var array<string, string> $fallback */
        $fallback = $reflection->getConstant('FALLBACK');

        self::assertNotSame(
            $fallback['product_name'],
            $identity['product_name'],
            'The shipped artefact resolves to the compiled-in fallback, which means it is missing, '
                . 'unreadable or malformed and nothing else in this suite would have noticed.',
        );
    }

    /** @param array<string, mixed> $values */
    private function writeArtefact(array $values): string
    {
        $path = $this->scratch . '/artefact-' . count($values) . '-' . bin2hex(random_bytes(4)) . '.php';
        file_put_contents($path, "<?php\n\nreturn " . var_export($values, true) . ";\n");

        return $path;
    }

    /** @param list<string> $arguments */
    private function runGenerator(string $repoRoot, array $arguments): int
    {
        $command = array_merge(
            [PHP_BINARY, $repoRoot . '/tools/branding/bin/generate-product-identity.php'],
            $arguments,
        );

        $escaped = implode(' ', array_map(escapeshellarg(...), $command));
        exec($escaped . ' 2>&1', $output, $exitCode);

        return $exitCode;
    }
}
