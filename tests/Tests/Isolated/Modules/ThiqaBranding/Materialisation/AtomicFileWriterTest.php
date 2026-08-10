<?php

/**
 * AtomicFileWriter: stage, verify, commit, revert — no observable half-written file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation;

use OpenEMR\Modules\ThiqaBranding\Materialisation\AtomicFileWriter;
use OpenEMR\Modules\ThiqaBranding\Materialisation\FilesystemException;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/materialisation_autoloader.php';

final class AtomicFileWriterTest extends TestCase
{
    use TemporaryTreeTrait;

    private AtomicFileWriter $writer;

    private string $tree = '';

    protected function setUp(): void
    {
        $this->tree = $this->makeTree();
        $this->writer = new AtomicFileWriter();
    }

    protected function tearDown(): void
    {
        $this->removeTree();
    }

    public function testStagingCreatesMissingDirectoriesAndLeavesTheTargetAbsent(): void
    {
        $target = $this->tree . '/deeply/nested/tokens.css';

        $staged = $this->writer->stage($target, 'body{}');

        self::assertFileExists($staged->temporaryPath);
        self::assertFileDoesNotExist($target, 'Staging must not publish anything.');
        self::assertSame(dirname($target), dirname($staged->temporaryPath), 'The temp file must share the directory.');
    }

    public function testVerifyDetectsAStagedFileThatChangedUnderneathUs(): void
    {
        $staged = $this->writer->stage($this->tree . '/tokens.css', 'original');
        self::assertTrue($this->writer->verify($staged));

        file_put_contents($staged->temporaryPath, 'tampered');

        self::assertFalse($this->writer->verify($staged));
    }

    public function testCommitPublishesTheContentAndRemovesTheTemporaryName(): void
    {
        $target = $this->tree . '/tokens.css';
        $staged = $this->writer->stage($target, 'new');

        $committed = $this->writer->commit($staged);

        self::assertSame('new', (string) file_get_contents($target));
        self::assertFileDoesNotExist($staged->temporaryPath);
        self::assertNull($committed->previousPath, 'There was nothing to displace.');
    }

    public function testCommitDisplacesRatherThanOverwritesTheCurrentFile(): void
    {
        $target = $this->tree . '/tokens.css';
        file_put_contents($target, 'previous');

        $committed = $this->writer->commit($this->writer->stage($target, 'next'));

        self::assertSame('next', (string) file_get_contents($target));
        self::assertNotNull($committed->previousPath);
        self::assertSame('previous', (string) file_get_contents((string) $committed->previousPath));
    }

    public function testRevertRestoresThePreviousContentByteForByte(): void
    {
        $target = $this->tree . '/tokens.css';
        file_put_contents($target, 'revision-one');

        $committed = $this->writer->commit($this->writer->stage($target, 'revision-two'));
        $this->writer->revert($committed);

        self::assertSame('revision-one', (string) file_get_contents($target));
        self::assertSame(['tokens.css'], $this->filesUnder($this->tree));
    }

    public function testRevertRemovesTheFileEntirelyWhenThereWasNoPrevious(): void
    {
        $target = $this->tree . '/tokens.css';

        $committed = $this->writer->commit($this->writer->stage($target, 'first ever'));
        $this->writer->revert($committed);

        self::assertFileDoesNotExist($target);
        self::assertSame([], $this->filesUnder($this->tree));
    }

    public function testFinaliseDropsTheDisplacedOriginal(): void
    {
        $target = $this->tree . '/tokens.css';
        file_put_contents($target, 'previous');

        $committed = $this->writer->commit($this->writer->stage($target, 'next'));
        $this->writer->finalise($committed);

        self::assertSame(['tokens.css'], $this->filesUnder($this->tree));
    }

    public function testDiscardRemovesTheStagedFileAndIsSafeTwice(): void
    {
        $staged = $this->writer->stage($this->tree . '/tokens.css', 'never published');

        $this->writer->discard($staged);
        $this->writer->discard($staged);

        self::assertSame([], $this->filesUnder($this->tree));
    }

    public function testAFileWhereTheDirectoryShouldBeIsRefused(): void
    {
        $blocker = $this->tree . '/branding';
        file_put_contents($blocker, 'not a directory');

        $this->expectException(FilesystemException::class);

        $this->writer->stage($blocker . '/tokens.css', 'body{}');
    }

    public function testConcurrentStagingOfTheSameTargetDoesNotCollide(): void
    {
        $target = $this->tree . '/tokens.css';

        $first = $this->writer->stage($target, 'one');
        $second = $this->writer->stage($target, 'two');

        self::assertNotSame($first->temporaryPath, $second->temporaryPath);
        self::assertSame('one', (string) file_get_contents($first->temporaryPath));
        self::assertSame('two', (string) file_get_contents($second->temporaryPath));
    }
}
