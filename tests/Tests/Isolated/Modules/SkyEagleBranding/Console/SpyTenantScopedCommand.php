<?php

/**
 * A stand-in subclass used to prove the site-scope notice cannot be skipped.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Console;

use OpenEMR\Modules\SkyEagleBranding\Console\SiteOption;
use OpenEMR\Modules\SkyEagleBranding\Console\SiteScopeNotice;
use OpenEMR\Modules\SkyEagleBranding\Console\TenantScopedBrandingCommand;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The real commands each do real work; this one does nothing but record that it ran and
 * then leave by whichever exit the test asked for — including by exception. That is
 * exactly the surface the base class's `finally` has to cover.
 */
final class SpyTenantScopedCommand extends TenantScopedBrandingCommand
{
    public bool $ran = false;

    private function __construct(
        private readonly ?int $code,
        SiteScopeNotice $notice,
    ) {
        parent::__construct($notice);
    }

    public static function returning(int $code, SiteScopeNotice $notice): self
    {
        return new self($code, $notice);
    }

    public static function throwing(SiteScopeNotice $notice): self
    {
        return new self(null, $notice);
    }

    protected function configure(): void
    {
        $this->setName('skyeagle-branding:spy');
        $this->getDefinition()->addOption(SiteOption::define());
    }

    protected function executeForSite(InputInterface $input, SymfonyStyle $io, SiteId $site): int
    {
        $this->ran = true;
        $io->text('spy ran');

        if ($this->code === null) {
            throw new RuntimeException('spy exploded');
        }

        return $this->code;
    }
}
