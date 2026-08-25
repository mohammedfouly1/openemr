<?php

/**
 * The accessibility obligation attached to one token key.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Token;

/**
 * Binds a foreground key to the key it is rendered against, plus the gate that pair
 * must clear. TokenValidator evaluates these against the *merged* token set, so an
 * override is judged on the palette it actually produces (plan §3.4.1, MVP-010 AC-2).
 */
final readonly class ContrastRule
{
    public function __construct(
        public TokenKey $foreground,
        public TokenKey $background,
        public ContrastGate $gate,
    ) {
    }

    /** True when either side of the pair is one of the supplied keys. */
    public function involves(TokenKey $key): bool
    {
        return $this->foreground === $key || $this->background === $key;
    }
}
