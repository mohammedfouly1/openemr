<?php

/**
 * Shared skeleton for every branding command that acts on exactly one tenant.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Finding B1: the tenant-scope notice has to be unskippable, so it is not left to each
 * command to remember.
 *
 * Three commands previously opened with the same eight lines — build a SymfonyStyle,
 * resolve `--site`, print the error, return INVALID — and each would have needed the same
 * closing lines added by hand. A notice that only prints on the paths somebody remembered
 * to decorate is the same defect it was written to close, so `execute()` is `final` here
 * and the subclass supplies {@see executeForSite()} instead.
 *
 * **The `finally` is the point.** The notice renders after the command's own output, on
 * every return path the subclass has, including the failure paths and including an
 * exception on its way out. A subclass cannot add a return that skips it, because there is
 * no return in a subclass that is not inside that `try`.
 *
 * **The notice is a required constructor argument on purpose.** Nullable would mean a
 * command wired without one prints nothing and looks entirely healthy — invisible, exactly
 * like the tenant it failed to mention. A missing wiring is instead a TypeError at
 * construction, in {@see \OpenEMR\Modules\ThiqaBranding\Bootstrap}, before any tenant is
 * touched.
 *
 * `--site` stays mandatory with no default. Nothing here supplies a fallback tenant, and
 * nothing here should ever be changed to; see {@see SiteOption} for why.
 */
abstract class TenantScopedBrandingCommand extends Command
{
    public function __construct(private readonly SiteScopeNotice $siteScopeNotice)
    {
        parent::__construct();
    }

    /**
     * The command's real work, with the tenant already resolved and validated.
     *
     * Return the exit code the command wants; it is passed through untouched.
     */
    abstract protected function executeForSite(InputInterface $input, SymfonyStyle $io, SiteId $site): int;

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $resolved = SiteOption::resolve($input);
        if (!$resolved->site instanceof SiteId) {
            $io->error($resolved->error ?? 'A tenant site id is required.');

            // No tenant was resolved, so there is no "acted on one, skipped the others" to
            // report — the command acted on none. The error already says so.
            return Command::INVALID;
        }

        try {
            return $this->executeForSite($input, $io, $resolved->site);
        } finally {
            $this->siteScopeNotice->render($io, $resolved->site);
        }
    }
}
