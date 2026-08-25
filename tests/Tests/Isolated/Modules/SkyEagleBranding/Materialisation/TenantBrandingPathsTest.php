<?php

/**
 * TenantBrandingPaths: every derived path stays inside the tenant's own scope.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation;

use InvalidArgumentException;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\TenantBrandingPaths;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/materialisation_autoloader.php';

final class TenantBrandingPathsTest extends TestCase
{
    use CertifiedAssetTrait;

    private TenantBrandingPaths $paths;

    protected function setUp(): void
    {
        $this->paths = new TenantBrandingPaths('/srv/module/public/branding', '/srv/openemr/sites');
    }

    public function testEachVariantHasItsOwnStylesheetUnderTheTenantsDirectory(): void
    {
        $site = new SiteId('alpha');

        self::assertSame(
            '/srv/module/public/branding/alpha/tokens-light.css',
            $this->paths->tokenCssFile($site, ThemeVariant::Light),
        );
        self::assertSame(
            '/srv/module/public/branding/alpha/tokens-dark.css',
            $this->paths->tokenCssFile($site, ThemeVariant::Dark),
        );
    }

    public function testLogosResolveIntoTheTenantsOwnSiteTree(): void
    {
        $site = new SiteId('alpha');
        $asset = $this->certifiedPlacement(LogoSlot::CoreLoginPrimary);

        self::assertSame(
            '/srv/openemr/sites/alpha/images/logos/core/login/primary/logo.png',
            $this->paths->logoFile($site, $asset),
        );
    }

    public function testTwoTenantsNeverShareAPath(): void
    {
        $alpha = $this->paths->tokenCssFile(new SiteId('alpha'), ThemeVariant::Light);
        $beta = $this->paths->tokenCssFile(new SiteId('beta'), ThemeVariant::Light);

        self::assertNotSame($alpha, $beta);
        self::assertFalse($this->paths->isWithinTenantScope(new SiteId('beta'), $alpha));
        self::assertTrue($this->paths->isWithinTenantScope(new SiteId('alpha'), $alpha));
    }

    public function testAPrefixMatchDoesNotCountAsTheSameTenant(): void
    {
        // "alpha" must not be judged to own "/…/alpha-two/…".
        $alphaTwo = $this->paths->tokenCssFile(new SiteId('alpha-two'), ThemeVariant::Light);

        self::assertFalse($this->paths->isWithinTenantScope(new SiteId('alpha'), $alphaTwo));
    }

    public function testTrailingSeparatorsInTheRootsAreNormalised(): void
    {
        $paths = new TenantBrandingPaths('/srv/branding///', 'C:\\srv\\sites\\');

        self::assertSame('/srv/branding/alpha', $paths->tokenCssDirectory(new SiteId('alpha')));
        self::assertSame(
            'C:/srv/sites/alpha/images/logos/core/menu/primary',
            $paths->logoDirectory(new SiteId('alpha'), LogoSlot::CoreMenuPrimary),
        );
    }

    #[DataProvider('blankRootProvider')]
    public function testAnEmptyRootIsRefused(string $tokenCssRoot, string $sitesRoot): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TenantBrandingPaths($tokenCssRoot, $sitesRoot);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function blankRootProvider(): array
    {
        return [
            'blank css root' => ['', '/srv/sites'],
            'blank sites root' => ['/srv/branding', ''],
            'slash-only css root' => ['///', '/srv/sites'],
        ];
    }
}
