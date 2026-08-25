<?php

/**
 * A PSR-3 logger that keeps every record so a test can assert on its shape.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Observability;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * Not a mock. The claim under test is about the *shape* of what is logged — a constant
 * message plus a structured context array — and a mock configured with `->with(...)`
 * would only prove the arguments a test already decided to expect. Keeping the records
 * lets a test walk every call the code made and assert a property over all of them,
 * which is what "never interpolates" actually means.
 */
final class RecordingLogger extends AbstractLogger
{
    /** @var list<array{level: string, message: string, context: array<array-key, mixed>}> */
    public array $records = [];

    /**
     * @param mixed                   $level
     * @param array<array-key, mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => is_string($level) ? $level : 'unknown',
            'message' => (string) $message,
            'context' => $context,
        ];
    }

    /** @return list<array{level: string, message: string, context: array<array-key, mixed>}> */
    public function withEvent(string $event): array
    {
        return array_values(array_filter(
            $this->records,
            static fn (array $record): bool => ($record['context']['event'] ?? null) === $event,
        ));
    }

    /** @return list<string> */
    public function messages(): array
    {
        return array_map(
            static fn (array $record): string => $record['message'],
            $this->records,
        );
    }
}
