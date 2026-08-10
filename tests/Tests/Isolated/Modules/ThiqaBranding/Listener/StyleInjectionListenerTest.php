<?php

/**
 * Isolated tests for the Tier 2 stylesheet injection listener.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Listener;

use OpenEMR\Modules\ThiqaBranding\Listener\StyleInjectionListener;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Config\ModuleAutoloadTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * The claim being tested is narrow and load-bearing: on the overwhelming majority of
 * requests -- every tenant with no token overlay -- this listener must cost nothing and
 * change nothing, and on the minority that do have one it must add its <link> WITHOUT
 * disturbing the stylesheets other modules already contributed.
 */
final class StyleInjectionListenerTest extends TestCase
{
    use ModuleAutoloadTrait;

    private const TOKEN_STYLESHEET =
        '/openemr/interface/modules/custom_modules/oe-module-thiqa-branding/public/branding-tokens.php?rev=7';

    /** Stylesheets another module contributed before this listener ran. */
    private const EXISTING_STYLES = [
        '/openemr/interface/modules/custom_modules/oe-module-example/public/one.css',
        '/openemr/interface/modules/custom_modules/oe-module-example/public/two.css',
    ];

    public static function setUpBeforeClass(): void
    {
        self::registerModuleAutoload();
    }

    /** The default and common case: no overlay, so the event is never written to. */
    public function testAnUnconfiguredTenantLeavesTheEventCompletelyUntouched(): void
    {
        $branding = new StubBrandingService();
        $event = new RecordingStyleFilterEvent('/interface/login/login.php', self::EXISTING_STYLES);

        $this->listener($branding)->onStyleFilter($event);

        $this->assertSame([], $event->setStylesCalls, 'setStyles() must not be called at all.');
        $this->assertSame(self::EXISTING_STYLES, $event->getStyles());
    }

    /** An empty string is treated exactly as null: nothing to add. */
    public function testAnEmptyStylesheetUrlIsTreatedAsAbsent(): void
    {
        $branding = new StubBrandingService();
        $branding->tokenStylesheetUrl = '';
        $event = new RecordingStyleFilterEvent('/interface/login/login.php', self::EXISTING_STYLES);

        $this->listener($branding)->onStyleFilter($event);

        $this->assertSame([], $event->setStylesCalls);
        $this->assertSame(self::EXISTING_STYLES, $event->getStyles());
    }

    /** Append, never replace: every pre-existing entry survives, in its original order. */
    public function testTheTokenStylesheetIsAppendedAfterTheExistingStyles(): void
    {
        $branding = new StubBrandingService();
        $branding->tokenStylesheetUrl = self::TOKEN_STYLESHEET;
        $event = new RecordingStyleFilterEvent('/interface/login/login.php', self::EXISTING_STYLES);

        $this->listener($branding)->onStyleFilter($event);

        $this->assertSame(
            [self::EXISTING_STYLES[0], self::EXISTING_STYLES[1], self::TOKEN_STYLESHEET],
            $event->getStyles(),
        );
    }

    /** An empty starting array is still an append, not a replacement of a known list. */
    public function testTheTokenStylesheetIsAddedWhenNoOtherModuleContributed(): void
    {
        $branding = new StubBrandingService();
        $branding->tokenStylesheetUrl = self::TOKEN_STYLESHEET;
        $event = new RecordingStyleFilterEvent('/interface/login/login.php');

        $this->listener($branding)->onStyleFilter($event);

        $this->assertSame([self::TOKEN_STYLESHEET], $event->getStyles());
    }

    /** setupHeader() can run twice on a composed page; the <link> must not double. */
    public function testTheStylesheetIsNotAddedTwice(): void
    {
        $branding = new StubBrandingService();
        $branding->tokenStylesheetUrl = self::TOKEN_STYLESHEET;
        $event = new RecordingStyleFilterEvent('/interface/login/login.php', self::EXISTING_STYLES);
        $listener = $this->listener($branding);

        $listener->onStyleFilter($event);
        $listener->onStyleFilter($event);

        $this->assertCount(1, $event->setStylesCalls);
        $this->assertSame(
            [self::EXISTING_STYLES[0], self::EXISTING_STYLES[1], self::TOKEN_STYLESHEET],
            $event->getStyles(),
        );
    }

    /** A branding failure must degrade the page, never break it. */
    public function testAFailingBrandingServiceDoesNotPropagateOrMutate(): void
    {
        $branding = new StubBrandingService();
        $branding->tokenStylesheetUrl = self::TOKEN_STYLESHEET;
        $branding->failure = new RuntimeException('branding is unavailable');
        $event = new RecordingStyleFilterEvent('/interface/login/login.php', self::EXISTING_STYLES);

        $this->listener($branding)->onStyleFilter($event);

        $this->assertSame([], $event->setStylesCalls);
        $this->assertSame(self::EXISTING_STYLES, $event->getStyles());
    }

    private function listener(StubBrandingService $branding): StyleInjectionListener
    {
        return new StyleInjectionListener($branding, new NullLogger());
    }
}
