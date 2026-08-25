<?php

/**
 * TenantScopedBrandingCommand: the site-scope notice must be unskippable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Console;

use OpenEMR\Modules\ThiqaBranding\Console\ApplyProfileCommand;
use OpenEMR\Modules\ThiqaBranding\Console\MaterialiseCommand;
use OpenEMR\Modules\ThiqaBranding\Console\SiteOption;
use OpenEMR\Modules\ThiqaBranding\Console\SiteScopeNotice;
use OpenEMR\Modules\ThiqaBranding\Console\TenantScopedBrandingCommand;
use OpenEMR\Modules\ThiqaBranding\Console\VerifyCommand;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteInventory;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Tenant\SitesFixtureTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';
require_once __DIR__ . '/SpyTenantScopedCommand.php';

final class TenantScopedBrandingCommandTest extends TestCase
{
    use SitesFixtureTrait;

    protected function tearDown(): void
    {
        $this->removeSites();
    }

    // ------------------------------------------------------------ the notice cannot be skipped

    /**
     * Every exit code a subclass can return still gets the notice, because there is no
     * return inside a subclass that is not inside the base class's `try`.
     *
     * @param int $code
     */
    #[DataProvider('exitCodes')]
    public function testTheNoticeIsRenderedWhateverTheSubclassReturns(int $code): void
    {
        $tester = $this->tester(SpyTenantScopedCommand::returning($code, $this->notice()));

        $tester->execute(['--site' => 'default'], ['interactive' => false]);

        self::assertSame($code, $tester->getStatusCode());
        self::assertStringContainsString('rdy0082restore', $tester->getDisplay());
    }

    /**
     * @return array<string, array{int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function exitCodes(): array
    {
        return [
            'success' => [Command::SUCCESS],
            'failure' => [Command::FAILURE],
            'invalid' => [Command::INVALID],
            'other' => [17],
        ];
    }

    /** A subclass that throws still leaves the operator knowing what it did not touch. */
    public function testTheNoticeIsRenderedWhenTheSubclassThrows(): void
    {
        $tester = $this->tester(SpyTenantScopedCommand::throwing($this->notice()));

        try {
            $tester->execute(['--site' => 'default'], ['interactive' => false]);
            self::fail('Expected the subclass exception to propagate.');
        } catch (RuntimeException) {
            self::assertStringContainsString('rdy0082restore', $tester->getDisplay());
        }
    }

    /** The notice never rescues or fails a command; the subclass's code is passed through. */
    public function testTheNoticeDoesNotChangeTheExitCode(): void
    {
        $tester = $this->tester(SpyTenantScopedCommand::returning(Command::SUCCESS, $this->notice()));

        $tester->execute(['--site' => 'default'], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    // --------------------------------------------------------------------- --site still binds

    public function testWithoutASiteTheSubclassNeverRunsAndNoNoticeIsPrinted(): void
    {
        $command = SpyTenantScopedCommand::returning(Command::SUCCESS, $this->notice());
        $tester = $this->tester($command);

        $tester->execute([], ['interactive' => false]);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertFalse($command->ran);
        self::assertStringContainsString('--site', $tester->getDisplay());
        self::assertStringNotContainsString('rdy0082restore', $tester->getDisplay());
    }

    public function testTheSiteOptionStillHasNoDefault(): void
    {
        $command = SpyTenantScopedCommand::returning(Command::SUCCESS, $this->notice());

        $option = $command->getDefinition()->getOption(SiteOption::NAME);

        self::assertTrue($option->isValueRequired());
        self::assertNull($option->getDefault());
    }

    // ------------------------------------------------------------------------- the wiring itself

    /**
     * The point of the base class: a command declaring `--site` that forgot to report its
     * unacted tenants would be finding B1 all over again, so membership is asserted rather
     * than trusted.
     *
     * @param class-string $class
     */
    #[DataProvider('tenantScopedCommands')]
    public function testEveryTenantScopedBrandingCommandInheritsTheNotice(string $class): void
    {
        self::assertTrue(
            is_subclass_of($class, TenantScopedBrandingCommand::class),
            $class . ' takes --site, so it must extend TenantScopedBrandingCommand.',
        );

        // Nullable would mean a command wired without a notice prints nothing and looks
        // healthy. It must be a hard constructor requirement.
        $parameter = (new ReflectionClass(TenantScopedBrandingCommand::class))
            ->getConstructor()?->getParameters()[0] ?? null;

        self::assertNotNull($parameter);
        self::assertFalse($parameter->allowsNull());
        self::assertSame(SiteScopeNotice::class, (string) $parameter->getType());
    }

    /**
     * @return array<string, array{class-string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function tenantScopedCommands(): array
    {
        return [
            'apply-profile' => [ApplyProfileCommand::class],
            'materialise' => [MaterialiseCommand::class],
            'verify' => [VerifyCommand::class],
        ];
    }

    /** `execute()` is final so no subclass can reintroduce a path that skips the notice. */
    public function testExecuteIsFinal(): void
    {
        $method = (new ReflectionClass(TenantScopedBrandingCommand::class))->getMethod('execute');

        self::assertTrue($method->isFinal());
    }

    // ----------------------------------------------------------------------------- fixtures

    private function notice(): SiteScopeNotice
    {
        return new SiteScopeNotice(
            new SiteInventory($this->makeSites(['default' => 1, 'rdy0082restore' => 1])),
        );
    }

    private function tester(SpyTenantScopedCommand $command): CommandTester
    {
        return new CommandTester($command);
    }
}
