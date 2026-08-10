<?php

/**
 * The mandatory --site option shared by every branding console command.
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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Tenant scoping is a locked constraint, and this is where the CLI honours it.
 *
 * **Why an option and not a positional argument.** `bin/console` reads `--site=` out of
 * `argv` itself and bootstraps `interface/globals.php` against that site, which is what
 * binds the database connection. A positional `site` argument could therefore name one
 * tenant while the process was connected to another's database — a cross-tenant write in
 * the making. Spelling the tenant the one way the bootstrap already understands keeps the
 * connection and the intent from ever disagreeing.
 *
 * **Why no default.** Every other OpenEMR command declares `--site` with a `'default'`
 * fallback. Branding must not: a materialisation run that silently targets whichever
 * tenant happens to be the fallback is exactly the accident tenant scoping exists to
 * prevent. Omitting the option is refused, loudly, with a non-zero exit.
 *
 * The rejected value is never echoed back. SiteId's constructor takes the same care, for
 * the same reason: the string came from outside and belongs in no output stream.
 */
final readonly class SiteOption
{
    public const NAME = 'site';

    private function __construct(
        public ?SiteId $site,
        public ?string $error,
    ) {
    }

    /** The option declaration to add to a command's definition. Deliberately no default. */
    public static function define(): InputOption
    {
        return new InputOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Tenant site id to act on. Mandatory: branding commands have no default tenant.',
        );
    }

    /** Read and validate the option. Never throws: a bad value is an operator error. */
    public static function resolve(InputInterface $input): self
    {
        $raw = $input->getOption(self::NAME);

        if ($raw === null) {
            return new self(null, 'The --site option is required. Branding commands never assume a tenant.');
        }

        if (!is_string($raw) || $raw === '') {
            return new self(null, 'The --site option needs a tenant site id as its value.');
        }

        $site = SiteId::tryFrom($raw);
        if (!$site instanceof SiteId) {
            return new self(
                null,
                'The --site value is not a valid site id: expected 1 to 63 characters of [A-Za-z0-9_-].',
            );
        }

        return new self($site, null);
    }
}
