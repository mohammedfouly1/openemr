<?php

/**
 * A settable SessionLanguageInterface double for the listener tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Listener;

use OpenEMR\Modules\SkyEagleBranding\Language\SessionLanguageInterface;

/**
 * Named after what it answers rather than what it is set to, because the whole point of
 * SKY-F01 is that "Arabic" and "right-to-left" are different questions and the tests have to
 * be able to set them independently. StubBrandingService::$rtl carries the direction; this
 * carries the language; a Hebrew session is the case where they disagree.
 */
final class StubSessionLanguage implements SessionLanguageInterface
{
    public function __construct(private bool $arabic = false)
    {
    }

    public function isArabic(): bool
    {
        return $this->arabic;
    }
}
