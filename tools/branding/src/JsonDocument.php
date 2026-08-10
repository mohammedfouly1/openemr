<?php

/**
 * Narrowing reader for a decoded JSON token document.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

use JsonException;

/**
 * JSON is genuinely polymorphic, so the decoded tree is the one place `mixed`
 * is tolerated. It is confined to this class: every public accessor narrows to
 * a native type or throws, so no `mixed` escapes into the renderers.
 *
 * Paths are dot-separated (`interactive.primary.default`). Numeric segments
 * address list elements (`files.0.path`).
 */
final readonly class JsonDocument
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(private array $data, private string $origin)
    {
    }

    /**
     * @param string $absolutePath filesystem path actually read
     * @param string $origin       repo-relative label used in error messages
     */
    public static function fromFile(string $absolutePath, string $origin): self
    {
        if (!is_file($absolutePath)) {
            throw new GeneratorException(sprintf('Token file "%s" does not exist.', $origin));
        }

        $raw = file_get_contents($absolutePath);
        if ($raw === false) {
            throw new GeneratorException(sprintf('Token file "%s" could not be read.', $origin));
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new GeneratorException(sprintf('Token file "%s" is not valid JSON.', $origin), 0, $e);
        }

        if (!is_array($decoded)) {
            throw new GeneratorException(sprintf('Token file "%s" must contain a JSON object.', $origin));
        }

        $normalised = [];
        foreach ($decoded as $key => $value) {
            $normalised[(string) $key] = $value;
        }

        return new self($normalised, $origin);
    }

    public function origin(): string
    {
        return $this->origin;
    }

    public function has(string $path): bool
    {
        return $this->locate($path) !== null;
    }

    public function requireString(string $path): string
    {
        $value = $this->locate($path);
        if (!is_string($value)) {
            throw new GeneratorException($this->missing($path, 'string'));
        }
        if ($value === '') {
            throw new GeneratorException(sprintf('Token "%s" in %s is an empty string.', $path, $this->origin));
        }

        return $value;
    }

    public function optionalString(string $path): ?string
    {
        return $this->locate($path) === null ? null : $this->requireString($path);
    }

    public function requireInt(string $path): int
    {
        $value = $this->locate($path);
        if (!is_int($value)) {
            throw new GeneratorException($this->missing($path, 'integer'));
        }

        return $value;
    }

    /**
     * Keys of the object at `$path`, in document order.
     *
     * @return list<string>
     */
    public function requireObjectKeys(string $path): array
    {
        $value = $this->locate($path);
        if (!is_array($value)) {
            throw new GeneratorException($this->missing($path, 'object'));
        }

        $keys = [];
        foreach (array_keys($value) as $key) {
            $keys[] = (string) $key;
        }

        return $keys;
    }

    /**
     * Number of elements in the list at `$path`.
     */
    public function requireListCount(string $path): int
    {
        $value = $this->locate($path);
        if (!is_array($value) || !array_is_list($value)) {
            throw new GeneratorException($this->missing($path, 'list'));
        }

        return count($value);
    }

    /**
     * Every scalar leaf beneath `$path`, as dot paths relative to `$path`,
     * in document order. Used to detect tokens the generator does not know about.
     *
     * @return list<string>
     */
    public function leafPaths(string $path): array
    {
        $value = $this->locate($path);
        if (!is_array($value)) {
            throw new GeneratorException($this->missing($path, 'object'));
        }

        return self::collectLeaves($value, '');
    }

    /**
     * @param array<array-key, mixed> $node
     *
     * @return list<string>
     */
    private static function collectLeaves(array $node, string $prefix): array
    {
        $leaves = [];
        foreach ($node as $key => $value) {
            $childPath = $prefix === '' ? (string) $key : $prefix . '.' . (string) $key;
            if (is_array($value)) {
                foreach (self::collectLeaves($value, $childPath) as $leaf) {
                    $leaves[] = $leaf;
                }
                continue;
            }
            $leaves[] = $childPath;
        }

        return $leaves;
    }

    private function missing(string $path, string $expected): string
    {
        return sprintf(
            'Required token "%s" is missing from %s or is not a %s.',
            $path,
            $this->origin,
            $expected,
        );
    }

    /**
     * Walks the decoded tree. Private because it is the only `mixed` surface;
     * every caller narrows the result immediately.
     */
    private function locate(string $path): mixed
    {
        $cursor = $this->data;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return null;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }
}
