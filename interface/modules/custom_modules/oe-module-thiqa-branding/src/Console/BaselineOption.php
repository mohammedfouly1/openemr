<?php

/**
 * The --baseline-path option: where this host keeps the RDY-0044-A rollback baseline.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Locates and integrity-checks the rollback baseline the demo seeder refuses to run without.
 *
 * **Why this class exists.** `SeedDemoCommand` used to hard-code the baseline's absolute path
 * as a class constant. The constant named a developer workstation
 * (`C:/openemr-stack/backups/protected/...`), so the `is_file()` precondition could only ever
 * be satisfied on that one machine: on any other host — notably the Ubuntu demo target — the
 * seeder fail-closed and the deterministic demo dataset was unreachable. That is a portability
 * defect (`DG-005` / `B-03`), not a policy, and it is fixed by making the *location*
 * configurable while leaving the *integrity guarantee* exactly where it was.
 *
 * **What deliberately did not change.** The baseline must still exist and must still hash to
 * the accepted SHA-256 before a single row is written. `verify()` is the same two checks the
 * command performed inline, in the same order, returning the same refusal messages. There is
 * no flag that skips them, no "warn and continue" branch, and no default that makes a missing
 * baseline acceptable. Supplying a path only tells the seeder *where to look*; it never tells
 * it *whether to care*.
 *
 * **Why an explicit CLI option rather than an environment variable.** Provisioning is a
 * scripted, auditable sequence, and the runbook step for it has to be copy-pasteable and
 * self-describing. An option appears in `--help`, is captured verbatim in shell history and
 * in the runbook, and cannot leak in from an inherited environment that nobody inspected. An
 * env var would be invisible at the call site and would make "which baseline did that run
 * actually verify?" unanswerable after the fact.
 *
 * **Why a value object rather than a bare `addOption()` call.** It mirrors `SiteOption`, the
 * pattern this module already uses for a console option that needs validating, and it keeps
 * the resolve-and-verify logic reachable from the isolated test suite — `SeedDemoCommand`
 * itself cannot be exercised without a database, so logic left inline in it is logic that
 * cannot be unit-tested.
 *
 * **The rejected value is never echoed back**, matching `SiteOption`. A filesystem path
 * discloses host layout, which `EV-071` §4 already records as something not to put in an
 * output stream.
 */
final readonly class BaselineOption
{
    public const NAME = 'baseline-path';

    private function __construct(
        public ?string $path,
        public ?string $error,
    ) {
    }

    /**
     * The option declaration to add to a command's definition.
     *
     * Unlike {@see SiteOption}, this option *does* carry a default: omitting it must keep
     * behaving exactly as it did before the option existed, so an operator on the original
     * development host — and any script written against the previous interface — is
     * unaffected. The default is supplied by the caller rather than baked in here, so this
     * class never itself names a machine.
     *
     * @param string $default The host-recorded baseline path to fall back on.
     */
    public static function define(string $default): InputOption
    {
        return new InputOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Filesystem path to the RDY-0044-A rollback baseline dump. '
                . 'Defaults to the recorded development path; supply this host\'s path on any other machine. '
                . 'The SHA-256 of whatever is supplied is still verified before seeding.',
            $default,
        );
    }

    /** Read the option. Never throws: a bad value is an operator error, not an exception. */
    public static function resolve(InputInterface $input): self
    {
        $raw = $input->getOption(self::NAME);

        if (!is_string($raw) || trim($raw) === '') {
            return new self(
                null,
                'The --baseline-path option needs a filesystem path to the RDY-0044-A baseline as its value.',
            );
        }

        // Passed through verbatim, deliberately un-trimmed and un-normalised: a legitimate
        // path may contain spaces, and both '/' and '\' separators are accepted by PHP's
        // filesystem functions on the platforms this runs on.
        return new self($raw, null);
    }

    /**
     * The fail-closed integrity gate. Returns null when the baseline is present and accepted,
     * or an operator-facing refusal describing which of the two checks failed.
     *
     * @param string $expectedSha256 The accepted baseline digest, lower-case hex.
     */
    public function verify(string $expectedSha256): ?string
    {
        if ($this->error !== null) {
            return $this->error;
        }

        if ($this->path === null || !is_file($this->path)) {
            return 'RDY-0044-A baseline not found at the configured path '
                . '(pass --baseline-path=<path> if it does not live at this host\'s recorded default).';
        }

        $actual = hash_file('sha256', $this->path);

        // hash_file() returns false on an unreadable file. Comparing that to the expected
        // digest fails, which is the correct fail-closed outcome: an unreadable rollback
        // artefact is no more usable than an absent one.
        if (!is_string($actual) || !hash_equals($expectedSha256, $actual)) {
            return 'RDY-0044-A baseline hash MISMATCH — the rollback artefact is not the accepted one.';
        }

        return null;
    }
}
