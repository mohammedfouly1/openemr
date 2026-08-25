<?php

/**
 * Writes the declarative branding profile to one tenant's `globals` table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Console;

use InvalidArgumentException;
use LogicException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingProfile;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingProfileEntry;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingProfileLoader;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The config half of the branding layer's CLI, sitting beside
 * {@see MaterialiseCommand}: that command ships *assets* and bumps a revision, this one
 * asserts *product identity strings* and bumps nothing.
 *
 * Keeping them apart is deliberate. Applying the profile is a statement about what the
 * product is called and where its links point; it is safe to run on a live tenant at any
 * time, converges to the same state however many times it runs, and has no artefacts on
 * disk to unwind. A materialisation is none of those things. Folding the profile into it
 * would drag a cheap, reversible operation behind an expensive, staged one, and would
 * make every branding-string fix consume a revision number.
 *
 * **What it does.** Reads the profile, reads the tenant's current values for exactly the
 * globals the profile names, prints a before/after row for each, and writes only the rows
 * that actually differ — inside a single {@see QueryUtils::inTransaction()} call, so a
 * failure part-way leaves the tenant on the identity it already had rather than a mixture
 * of two.
 *
 * **Idempotence** is not a claim, it is the diff: a second run finds no differing rows,
 * opens no transaction at all, and says so. That is what makes the command safe in a
 * provisioning loop and safe to hand to an operator who is not sure whether it already
 * ran.
 *
 * **Exit codes**, matching MaterialiseCommand so callers can branch uniformly:
 *
 * | code | meaning |
 * |------|---------|
 * | `0`  | applied, already up to date, or a dry run that completed |
 * | `1`  | the database refused the write; nothing was committed |
 * | `2`  | the invocation or the profile was refused; nothing was attempted |
 */
#[AsCommand(
    name: 'skyeagle-branding:apply-profile',
    description: 'Apply the declarative Thiqa branding profile to one tenant (requires --site).',
)]
final class ApplyProfileCommand extends TenantScopedBrandingCommand
{
    /** Every branding global is a scalar setting, never one row of a multi-valued list. */
    private const GL_INDEX = 0;

    private const DRY_RUN = 'dry-run';

    private const PROFILE = 'profile';

    /**
     * @param SiteId $bootstrappedSite the tenant `bin/console` bound this process to.
     *        Passed in rather than read from `$GLOBALS`: the command must be able to
     *        refuse a `--site` that disagrees with the live database connection, and it
     *        cannot do that if its only source of truth is the same ambient state.
     */
    public function __construct(
        private readonly BrandingProfileLoader $loader,
        private readonly string $profilePath,
        private readonly SiteId $bootstrappedSite,
        SiteScopeNotice $siteScopeNotice,
    ) {
        parent::__construct($siteScopeNotice);
    }

    protected function configure(): void
    {
        $this->getDefinition()->addOption(SiteOption::define());

        $this
            ->addOption(
                self::DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Print the before/after diff and exit without writing anything.',
            )
            ->addOption(
                self::PROFILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to an alternative profile file. Defaults to the profile shipped with the module.',
            )
            ->setHelp(
                'Applies config/branding-profile.json to one tenant, all-or-nothing, and prints a '
                . 'per-key before/after table.' . PHP_EOL . PHP_EOL
                . 'Safe to rerun: only rows whose value differs are written, so a second run reports '
                . 'no changes and touches nothing.' . PHP_EOL . PHP_EOL
                . 'The tenant is never implied. --site is mandatory, has no default, and must name the '
                . 'same site bin/console was bootstrapped against.',
            );
    }

    protected function executeForSite(InputInterface $input, SymfonyStyle $io, SiteId $site): int
    {
        if (!$site->equals($this->bootstrappedSite)) {
            // The site is not echoed back. This message can reach a shared log, and which
            // tenants exist is topology detail (plan §3.8.1 rule 4).
            $io->error(
                'The --site value names a different tenant from the one this process is connected to. '
                . 'Rerun bin/console with a matching --site so the intent and the database agree.',
            );

            return Command::INVALID;
        }

        $path = $this->profileOption($input);

        try {
            $profile = $this->loader->load($path);
        } catch (InvalidArgumentException $exception) {
            // Narrow on purpose: this repo's ForbiddenCatchTypeRule refuses any catch that
            // would also swallow \Error. The loader's messages describe a file in this
            // repository, not user input, so showing them is what makes the failure fixable.
            $io->error('The branding profile was refused; nothing has been changed.');
            $io->text($exception->getMessage());

            return Command::INVALID;
        }

        try {
            $current = $this->readCurrent($profile);
        } catch (SqlQueryException) {
            $io->error(
                'The tenant\'s current branding values could not be read, so no change was attempted.',
            );

            return Command::FAILURE;
        }

        $rows = [];
        $changes = [];
        foreach ($profile->entries() as $entry) {
            $before = $current[$entry->globalName()] ?? null;
            $rows[] = $this->row($entry, $before);

            if ($before !== $entry->value) {
                $changes[] = $entry;
            }
        }

        $io->definitionList(
            ['Site' => $site->value],
            ['Profile' => $profile->name],
            ['Source' => $profile->sourceDocument],
            ['Globals declared' => (string) count($profile)],
            ['Rows differing' => (string) count($changes)],
            ['Mode' => $this->isDryRun($input) ? 'dry run (no write)' : 'apply'],
        );

        $io->table(['Global', 'Provenance', 'Before', 'After', 'Status'], $rows);

        if ($changes === []) {
            $io->success('No changes: every global already holds its profile value.');

            return Command::SUCCESS;
        }

        if ($this->isDryRun($input)) {
            $io->note(
                sprintf('Dry run: %d row(s) would be written. Nothing was changed.', count($changes)),
            );

            return Command::SUCCESS;
        }

        try {
            $this->write($changes);
        } catch (SqlQueryException) {
            // The exception carries the statement text; it is logged by QueryUtils and never
            // surfaced here.
            $io->error(
                'The write failed and the transaction was rolled back. The tenant still holds its '
                . 'previous branding values; the same invocation can be retried.',
            );

            return Command::FAILURE;
        }

        $io->success(sprintf('Applied %d branding global(s) to the tenant.', count($changes)));

        return Command::SUCCESS;
    }

    /**
     * The tenant's current value for every global the profile names.
     *
     * Scoped to the profile's own key list rather than reading the whole table: the
     * command has no business seeing globals it will not write.
     *
     * @return array<string, string>
     *
     * @throws SqlQueryException
     */
    private function readCurrent(BrandingProfile $profile): array
    {
        $names = $profile->globalNames();
        if ($names === []) {
            throw new LogicException('A parsed branding profile cannot be empty.');
        }

        $placeholders = implode(', ', array_fill(0, count($names), '?'));
        $records = QueryUtils::fetchRecords(
            'SELECT `gl_name`, `gl_value` FROM `globals` WHERE `gl_index` = ? AND `gl_name` IN ('
                . $placeholders . ')',
            [self::GL_INDEX, ...$names],
        );

        $values = [];
        foreach ($records as $record) {
            $name = $record['gl_name'] ?? null;
            $value = $record['gl_value'] ?? null;
            if (!is_string($name) || !is_string($value)) {
                continue;
            }

            $values[$name] = $value;
        }

        return $values;
    }

    /**
     * One transaction for the whole delta.
     *
     * `inTransaction()` commits when the callable returns and rolls back and rethrows on
     * any throwable, which is the all-or-nothing semantic this needs. The deprecated
     * `startTransaction`/`commit`/`rollback` trio is not used anywhere in this layer.
     *
     * @param list<BrandingProfileEntry> $changes
     *
     * @throws SqlQueryException
     */
    private function write(array $changes): void
    {
        QueryUtils::inTransaction(static function () use ($changes): void {
            foreach ($changes as $entry) {
                QueryUtils::sqlStatementThrowException(
                    'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, ?, ?) '
                        . 'ON DUPLICATE KEY UPDATE `gl_value` = ?',
                    [$entry->globalName(), self::GL_INDEX, $entry->value, $entry->value],
                );
            }
        });
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string}
     */
    private function row(BrandingProfileEntry $entry, ?string $before): array
    {
        $status = match (true) {
            $before === null => 'create',
            $before === $entry->value => 'unchanged',
            default => 'update',
        };

        return [
            $entry->globalName(),
            $entry->provenance(),
            $before === null ? '(absent)' : $this->display($before),
            $this->display($entry->value),
            $status,
        ];
    }

    /**
     * Values reach the terminal, so blanks are made visible and long strings are clipped
     * rather than wrapping the table into unreadability.
     */
    private function display(string $value): string
    {
        if ($value === '') {
            return '(blank)';
        }

        if (mb_strlen($value, 'UTF-8') <= 58) {
            return $value;
        }

        return mb_substr($value, 0, 57, 'UTF-8') . '…';
    }

    private function isDryRun(InputInterface $input): bool
    {
        return $input->getOption(self::DRY_RUN) === true;
    }

    private function profileOption(InputInterface $input): string
    {
        $raw = $input->getOption(self::PROFILE);

        return is_string($raw) && $raw !== '' ? $raw : $this->profilePath;
    }
}
