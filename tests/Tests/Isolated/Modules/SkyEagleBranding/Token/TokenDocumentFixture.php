<?php

/**
 * Access to the real brand/tokens/thiqa-tokens.json from the isolated suite.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Token;

use RuntimeException;

/**
 * The tests read the shipped document rather than a copy so that the allowlist and
 * the design source cannot drift apart silently — that drift is precisely what the
 * parser's reject-unknown-keys behaviour exists to catch.
 */
final class TokenDocumentFixture
{
    public const RELATIVE_PATH = '/../../../../../../brand/tokens/thiqa-tokens.json';

    public static function path(): string
    {
        return __DIR__ . self::RELATIVE_PATH;
    }

    public static function json(): string
    {
        $contents = file_get_contents(self::path());
        if ($contents === false) {
            throw new RuntimeException('Unable to read brand/tokens/thiqa-tokens.json.');
        }

        return $contents;
    }

    /**
     * The decoded document.
     *
     * @return array<array-key, mixed>
     */
    public static function decoded(): array
    {
        $decoded = json_decode(self::json(), true, 16, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('brand/tokens/thiqa-tokens.json is not an object.');
        }

        return $decoded;
    }

    /**
     * One variant section, e.g. "light".
     *
     * @return array<array-key, mixed>
     */
    public static function section(string $variant): array
    {
        $section = self::decoded()[$variant] ?? null;
        if (!is_array($section)) {
            throw new RuntimeException(sprintf('Variant section "%s" is missing.', $variant));
        }

        return $section;
    }

    /**
     * Every dot path defined anywhere in the document, deduplicated.
     *
     * @return list<string>
     */
    public static function dotPaths(): array
    {
        $paths = [];
        foreach (['light', 'dark'] as $variant) {
            foreach (self::flatten(self::section($variant), []) as $dotPath) {
                $paths[$dotPath] = true;
            }
        }

        return array_keys($paths);
    }

    /**
     * @param array<array-key, mixed> $data
     * @param list<string>            $path
     *
     * @return list<string>
     */
    private static function flatten(array $data, array $path): array
    {
        $paths = [];
        foreach ($data as $segment => $node) {
            $childPath = [...$path, (string) $segment];

            if (is_array($node)) {
                foreach (self::flatten($node, $childPath) as $nested) {
                    $paths[] = $nested;
                }
                continue;
            }

            $paths[] = implode('.', $childPath);
        }

        return $paths;
    }
}
