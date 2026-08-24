<?php

/**
 * Prevents the human-readable WCAG evidence summary drifting from its machine record.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenAI
 * @copyright Copyright (c) 2026 OpenAI
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use PHPUnit\Framework\TestCase;

final class WcagEvidenceContractTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';

    public function testPublishedSummaryMatchesCurrentMachineEvidence(): void
    {
        $results = $this->results();
        $counts = array_count_values(array_column($results, 'status'));
        $pairs = count($results);
        $pass = $counts['PASS'] ?? 0;
        $advisory = $counts['ADVISORY'] ?? 0;
        $fail = $counts['FAIL'] ?? 0;

        $statuses = array_keys($counts);
        sort($statuses);
        self::assertSame(['ADVISORY', 'PASS'], $statuses);
        self::assertSame(38, $pairs);
        self::assertSame(35, $pass);
        self::assertSame(3, $advisory);
        self::assertSame(0, $fail);

        $document = $this->read('docs/branding-production/08-wcag-contrast.md');
        self::assertStringContainsString("— **{$pairs}** evaluated pairs.", $document);
        self::assertStringContainsString("| PASS | {$pass} |", $document);
        self::assertStringContainsString("| FAIL | **{$fail}** |", $document);
        self::assertStringContainsString("**{$pass} PASS, {$advisory} ADVISORY, {$fail} FAIL, {$pairs} pairs**", $document);
    }

    public function testEveryMachineResultHasACurrentDocumentRow(): void
    {
        $document = $this->read('docs/branding-production/08-wcag-contrast.md');

        foreach ($this->results() as $index => $result) {
            self::assertIsString($result['label'] ?? null, "Result #{$index} has no label.");
            self::assertIsString($result['fg'] ?? null, "Result #{$index} has no foreground.");
            self::assertIsString($result['bg'] ?? null, "Result #{$index} has no background.");
            self::assertIsNumeric($result['ratio'] ?? null, "Result #{$index} has no ratio.");
            self::assertIsNumeric($result['required'] ?? null, "Result #{$index} has no threshold.");
            self::assertIsString($result['status'] ?? null, "Result #{$index} has no status.");

            $row = sprintf(
                '| %s | `%s` | `%s` | %.2f |',
                $result['label'],
                $result['fg'],
                $result['bg'],
                $result['ratio']
            );
            self::assertStringContainsString(
                $row,
                $document,
                sprintf('Published table is missing machine result #%d (%s).', $index, $result['label'])
            );
        }
    }

    public function testMachineVerdictsAreInternallyConsistent(): void
    {
        foreach ($this->results() as $index => $result) {
            $ratio = (float) $result['ratio'];
            $required = (float) $result['required'];
            $status = (string) $result['status'];

            if ($status === 'PASS') {
                self::assertGreaterThanOrEqual($required, $ratio, "PASS result #{$index} misses its threshold.");
                continue;
            }

            self::assertSame('ADVISORY', $status, "Unexpected result status at #{$index}.");
            self::assertLessThan($required, $ratio, "ADVISORY result #{$index} does not miss its threshold.");
            self::assertMatchesRegularExpression(
                '/advisory|exempt/i',
                (string) ($result['criterion'] ?? ''),
                "ADVISORY result #{$index} has no advisory/exemption rationale."
            );
        }
    }

    /** @return non-empty-list<array<string, mixed>> */
    private function results(): array
    {
        $decoded = json_decode(
            $this->read('brand/qa/wcag-contrast-results.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        self::assertIsArray($decoded);
        self::assertIsArray($decoded['results'] ?? null);
        self::assertNotEmpty($decoded['results']);

        return $decoded['results'];
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(self::PROJECT_ROOT . '/' . $relativePath);
        self::assertIsString($contents, sprintf('Unable to read %s', $relativePath));

        return $contents;
    }
}
