<?php

/**
 * A LogoService test double that never touches the filesystem.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Asset;

use OpenEMR\Services\LogoService;

/**
 * The real LogoService reaches for OE_SITE_DIR, the kernel and a Finder walk of the site
 * image directories, none of which exist in the isolated suite. The constructor is
 * overridden deliberately and the parent's is not called: no inherited state is used,
 * because getLogo() is fully replaced.
 *
 * The double also records what it was asked for, so the tests can assert that the resolver
 * passes each slot's own type fragment and filename pattern straight through rather than
 * inventing a path of its own.
 */
final class FakeLogoService extends LogoService
{
    /** @var list<array{type: string, filename: string}> */
    public array $calls = [];

    /**
     * @param array<string, string> $paths        slot type fragment => path core would return
     * @param string                $fallbackPath returned for a type not present in $paths
     */
    public function __construct(
        private readonly array $paths = [],
        private readonly string $fallbackPath = '',
    ) {
        // Intentionally does not call parent::__construct(): see the class docblock.
    }

    public function getLogo(string $type, string $filename = "logo.*"): string
    {
        $this->calls[] = ['type' => $type, 'filename' => $filename];

        return $this->paths[$type] ?? $this->fallbackPath;
    }
}
