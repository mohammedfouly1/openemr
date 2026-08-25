<?php

/**
 * Tells the operator which tenants a branding command did NOT act on.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Console;

use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteInventory;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Finding B1, the operator-facing half: `--site` still has no default, and still never
 * will — but an operator who names one tenant is now told, in the same breath, about every
 * other tenant they have just left behind.
 *
 * **Why this prints site ids when {@see SiteOption} refuses to.** The two are not the same
 * act. SiteOption declines to echo a *rejected operator-supplied string* back into an
 * error that may reach a shared log — the value came from outside the process and had
 * already failed validation. What is printed here came from the local filesystem the
 * command is already running against, was validated by {@see SiteId} before being carried,
 * and goes to the terminal of an operator who can list `sites/` for themselves. Withholding
 * it would leave the notice useless: "there is another tenant, and I shan't say which" does
 * not let anyone act. Directory names that failed validation are still never printed — see
 * {@see \OpenEMR\Modules\SkyEagleBranding\Tenant\SiteInventoryReport}.
 *
 * **Why the unreadable case is loud.** If the sites directory cannot be enumerated, the
 * honest report is that the tenant population is unknown. Printing the single-tenant line
 * in that case would be the original defect wearing a reassuring face.
 *
 * **Exit codes are untouched.** This renders and returns; it never fails a command, and it
 * never rescues one. A tenant left unbranded is a decision for a human, not a probe result.
 */
final readonly class SiteScopeNotice
{
    public function __construct(private SiteInventory $inventory)
    {
    }

    /** Renders the notice for a command that acted on exactly one tenant. */
    public function render(SymfonyStyle $io, SiteId $acting): void
    {
        $report = $this->inventory->take();

        if (!$report->readable) {
            $io->warning(
                'The sites directory could not be read, so this command cannot say whether other '
                . 'configured tenants exist on this installation. Treat the tenant population as '
                . 'unknown and check it by hand before treating this run as complete.',
            );

            return;
        }

        if ($report->isSingleTenant($acting)) {
            // Deliberately a plain line, not a block: on a single-tenant installation there
            // is nothing to warn about, and a warning that fires every time is a warning
            // nobody reads by the second week.
            $io->text(
                'Tenant scope: this installation has no other configured tenant, '
                . 'so nothing was left untouched.',
            );

            return;
        }

        $io->warning($this->lines($report->othersThan($acting), $report->unsupportedNameCount));
    }

    /**
     * The block, as paragraphs.
     *
     * One element per paragraph rather than one per line, because SymfonyStyle inserts a
     * blank line between array elements: a list built element-per-line would come out
     * double-spaced and stop looking like a list at about three tenants.
     *
     * @param  list<SiteId>  $others
     * @return list<string>
     */
    private function lines(array $others, int $unsupportedNameCount): array
    {
        $paragraphs = ['This command acted on ONE tenant. Other configured tenants were NOT touched.'];

        if ($others !== []) {
            $paragraphs[] = sprintf(
                "%d other configured %s on this installation:\n%s",
                count($others),
                count($others) === 1 ? 'tenant' : 'tenants',
                implode("\n", array_map(static fn (SiteId $site): string => '  - ' . $site->value, $others)),
            );
        }

        if ($unsupportedNameCount > 0) {
            $paragraphs[] = sprintf(
                '%d further configured %s a directory name outside the supported site-id character '
                . 'set, so it cannot be named here or targeted with --site. Inspect the sites '
                . 'directory by hand.',
                $unsupportedNameCount,
                $unsupportedNameCount === 1 ? 'tenant has' : 'tenants have',
            );
        }

        $paragraphs[] = 'Branding commands act on exactly one tenant and have no default tenant. '
            . 'Rerun with --site for each tenant that must carry the same branding, or record the '
            . 'decision to leave it as it is.';

        return $paragraphs;
    }
}
