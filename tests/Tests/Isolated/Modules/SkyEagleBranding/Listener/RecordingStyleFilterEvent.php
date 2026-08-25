<?php

/**
 * A StyleFilterEvent that records what a listener hands it, without core's safety filter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Listener;

use OpenEMR\Events\Core\StyleFilterEvent;

/**
 * The real StyleFilterEvent::setStyles() runs every path through
 * ModulesApplication::filterSafeLocalModuleFiles(), which reads OEGlobalsBag, the project
 * directory and the web root, and realpaths against the modules tree. None of that exists
 * in the isolated suite, and none of it is the property under test here: what is under
 * test is whether the listener APPENDS to the array it was given or replaces it.
 *
 * So the filter is stood aside and the raw argument is captured instead. That the Tier 2
 * URL survives core's filter is a separate claim, and it is a structural one rather than a
 * runtime one -- the URL is this module's own public/branding-tokens.php, which is inside
 * interface/modules/ by construction (see StyleInjectionListener's docblock).
 *
 * `$setStylesCalls` is the part that makes "genuine no-op" provable: an empty call log
 * means the listener did not merely hand back an unchanged array, it never wrote at all.
 */
final class RecordingStyleFilterEvent extends StyleFilterEvent
{
    /**
     * Every array handed to setStyles(), in order.
     *
     * @var list<array<array-key, mixed>>
     */
    public array $setStylesCalls = [];

    /** @var array<array-key, mixed> */
    private array $recordedStyles = [];

    /**
     * @param array<array-key, mixed> $initialStyles styles another module already contributed
     */
    public function __construct(string $pageName, array $initialStyles = [])
    {
        parent::__construct($pageName);

        $this->recordedStyles = $initialStyles;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getStyles(): array
    {
        return $this->recordedStyles;
    }

    /**
     * The parameter type stays as wide as the parent's bare `array`, because narrowing an
     * overridden parameter breaks contravariance.
     *
     * @param array<array-key, mixed> $styles
     */
    public function setStyles(array $styles): StyleFilterEvent
    {
        $this->setStylesCalls[] = $styles;
        $this->recordedStyles = $styles;

        return $this;
    }
}
