<?php

/**
 * SiteId: the tenant scope that cannot widen into a path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Tenant;

use InvalidArgumentException;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

final class SiteIdTest extends TestCase
{
    public function testABlankSiteIdIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SiteId('');
    }

    public function testTheRejectedValueIsNeverEchoedBack(): void
    {
        try {
            new SiteId('../../etc/passwd');
            self::fail('Expected the traversal attempt to be refused.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringNotContainsString('passwd', $exception->getMessage());
        }
    }

    #[DataProvider('refusedProvider')]
    public function testAValueThatCouldWidenAPathIsRefused(string $candidate): void
    {
        self::assertNull(SiteId::tryFrom($candidate));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedProvider(): array
    {
        return [
            'empty' => [''],
            'single dot' => ['.'],
            'parent' => ['..'],
            'forward traversal' => ['../other'],
            'backward traversal' => ['..\\other'],
            'nested path' => ['alpha/beta'],
            'windows path' => ['alpha\\beta'],
            'drive letter' => ['C:'],
            'leading dot' => ['.hidden'],
            'dotted name' => ['alpha.beta'],
            'null byte' => ["alpha\0beta"],
            'newline' => ["alpha\nbeta"],
            'space' => ['alpha beta'],
            'url' => ['http://example.test'],
            'too long' => [str_repeat('a', 64)],
        ];
    }

    #[DataProvider('acceptedProvider')]
    public function testARealSiteDirectoryNameIsAccepted(string $candidate): void
    {
        self::assertSame($candidate, (new SiteId($candidate))->value);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function acceptedProvider(): array
    {
        return [
            'default' => ['default'],
            'hyphenated' => ['clinic-one'],
            'underscored' => ['clinic_one'],
            'numeric' => ['12345'],
            'maximum length' => [str_repeat('a', 63)],
        ];
    }

    public function testEqualityIsByValue(): void
    {
        self::assertTrue((new SiteId('alpha'))->equals(new SiteId('alpha')));
        self::assertFalse((new SiteId('alpha'))->equals(new SiteId('beta')));
        self::assertSame('alpha', (string) new SiteId('alpha'));
    }
}
