<?php

/**
 * A materialisation payload read from a JSON file on the operator's disk.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use JsonException;
use OpenEMR\Modules\ThiqaBranding\Asset\LogoSlot;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\ThiqaBranding\Materialisation\GlobalsDelta;
use OpenEMR\Modules\ThiqaBranding\Materialisation\MaterialisationJob;

/**
 * The file format, all keys optional:
 *
 * ```json
 * {
 *   "light":   { "link.default": "#1E4574" },
 *   "dark":    { "link.default": "#B7D9F5" },
 *   "strings": { "openemr_name": "Thiqa", "portal_primary_menu_logo_height": 30 },
 *   "assets":  [ { "slot": "core/login/primary", "path": "/var/staging/logo.png" } ]
 * }
 * ```
 *
 * **The two overlays are passed through untouched.** They are typed `array<array-key,
 * mixed>` all the way into MaterialisationJob because that is the trust boundary: the
 * tenant-side TokenValidator is the one component whose rejections are auditable
 * (plan §3.9), so narrowing or normalising a colour here would move validation somewhere
 * it cannot be audited. This class checks that they are arrays and nothing more.
 *
 * **`strings` is narrowed here**, because GlobalsDelta can only hold BrandingGlobalKey
 * cases: a name outside the layer's closed registry has no representation to be passed
 * along in, so it is reported now rather than silently dropped later. Numbers and booleans
 * are accepted and normalised to the `varchar` forms the globals table stores, since JSON
 * has no reason to spell a logo height as a string.
 *
 * **`assets` names a slot and a source path, nothing more.** This class narrows `slot` to
 * a known {@see LogoSlot} and confirms `path` is a non-empty string -- it does not read the
 * file, does not touch its bytes and cannot build an AssetPlacement. That is deliberate:
 * turning a path into a placeable asset requires the real LogoValidator, which this static
 * parser has no way to construct (it needs a raster reader, an SVG inspector and a
 * logger). {@see \OpenEMR\Modules\ThiqaBranding\Console\MaterialiseCommand} owns a
 * validator instance and is the only place these requests are resolved into
 * AssetPlacements, so naming a path here can never bypass validation -- it can only ask
 * for a file to be validated (audit finding AR-P2-002, closed at the AssetPlacement type
 * boundary; this class must not reopen it by accepting bytes or a trusted checksum).
 *
 * Parse failures are collected, not thrown: the command reports every problem in the file
 * at once instead of making an operator rerun to discover the second one.
 */
final readonly class JobPayload
{
    /** Cap on how much of a rejected key is echoed back, in characters. */
    private const ECHO_LIMIT = 64;

    /**
     * @param array<array-key, mixed>   $light  untrusted; validated tenant-side by the materialiser
     * @param array<array-key, mixed>   $dark   untrusted; validated tenant-side by the materialiser
     * @param list<string>              $errors operator-facing, one line per problem
     * @param list<AssetIntakeRequest>  $assets unresolved; MaterialiseCommand runs each
     *                                          through LogoValidator before it can become
     *                                          an AssetPlacement
     */
    private function __construct(
        public array $light,
        public array $dark,
        public GlobalsDelta $strings,
        public array $errors,
        public array $assets = [],
    ) {
    }

    /** No overlays, no strings, no assets: the job becomes a bare revision bump. */
    public static function empty(): self
    {
        return new self([], [], GlobalsDelta::empty(), []);
    }

    public static function fromFile(string $path): self
    {
        if ($path === '' || !is_file($path)) {
            return self::rejected(['The payload file does not exist, or is not a regular file.']);
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return self::rejected(['The payload file could not be read.']);
        }

        try {
            $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // The parser's own message names byte offsets in a file the operator can read
            // for themselves; the useful information is simply that it did not parse.
            return self::rejected(['The payload file is not valid JSON.']);
        }

        if (!is_array($decoded)) {
            return self::rejected(['The payload must be a JSON object.']);
        }

        $errors = [];

        $light = self::overlay($decoded, 'light', $errors);
        $dark = self::overlay($decoded, 'dark', $errors);
        $strings = self::strings($decoded, $errors);
        $assets = self::assets($decoded, $errors);

        return new self($light, $dark, $strings, $errors, $assets);
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /** True when the payload would change nothing beyond the revision itself. */
    public function isEmpty(): bool
    {
        return $this->light === [] && $this->dark === [] && $this->strings->isEmpty() && $this->assets === [];
    }

    /**
     * Fold the overlays and strings into a bare job. Call only on a valid payload.
     *
     * Deliberately does not touch `$assets`: those are unvalidated requests, and folding
     * them in here would let a job reach the materialiser with unverified bytes attached.
     * The caller resolves `$assets` through a real LogoValidator first (see
     * {@see \OpenEMR\Modules\ThiqaBranding\Console\MaterialiseCommand::resolveAssets()})
     * and adds the resulting AssetPlacements with `MaterialisationJob::withAssets()`.
     */
    public function applyTo(MaterialisationJob $job): MaterialisationJob
    {
        return $job
            ->withOverlays($this->light, $this->dark)
            ->withStrings($this->strings);
    }

    /**
     * @param list<string> $errors
     */
    private static function rejected(array $errors): self
    {
        return new self([], [], GlobalsDelta::empty(), $errors);
    }

    /**
     * @param array<array-key, mixed> $decoded
     * @param list<string>            $errors
     *
     * @return array<array-key, mixed>
     */
    private static function overlay(array $decoded, string $key, array &$errors): array
    {
        $value = $decoded[$key] ?? [];
        if (is_array($value)) {
            return $value;
        }

        $errors[] = sprintf('payload "%s": expected an object of token overrides.', $key);

        return [];
    }

    /**
     * @param array<array-key, mixed> $decoded
     * @param list<string>            $errors
     */
    private static function strings(array $decoded, array &$errors): GlobalsDelta
    {
        $raw = $decoded['strings'] ?? [];
        if (!is_array($raw)) {
            $errors[] = 'payload "strings": expected an object of branding globals.';

            return GlobalsDelta::empty();
        }

        $delta = GlobalsDelta::empty();
        foreach ($raw as $name => $value) {
            if (!is_string($name)) {
                $errors[] = 'payload "strings": every entry must be named by a branding globals key.';
                continue;
            }

            $key = BrandingGlobalKey::tryFrom($name);
            if (!$key instanceof BrandingGlobalKey) {
                $errors[] = sprintf(
                    'payload "strings": "%s" is not a branding global this layer owns.',
                    self::echoable($name),
                );
                continue;
            }

            $text = self::scalarToGlobalValue($value);
            if ($text === null) {
                $errors[] = sprintf(
                    'payload "strings": "%s" must be a string, a number or a boolean.',
                    self::echoable($name),
                );
                continue;
            }

            $delta = $delta->with($key, $text);
        }

        return $delta;
    }

    /**
     * @param array<array-key, mixed> $decoded
     * @param list<string>            $errors
     *
     * @return list<AssetIntakeRequest>
     */
    private static function assets(array $decoded, array &$errors): array
    {
        $raw = $decoded['assets'] ?? [];
        if (!is_array($raw)) {
            $errors[] = 'payload "assets": expected a list of {slot, path} objects.';

            return [];
        }

        $requests = [];
        foreach ($raw as $index => $entry) {
            $position = is_int($index) ? $index : -1;

            if (!is_array($entry)) {
                $errors[] = sprintf('payload "assets[%d]": expected an object with "slot" and "path".', $position);
                continue;
            }

            $slotValue = $entry['slot'] ?? null;
            if (!is_string($slotValue) || $slotValue === '') {
                $errors[] = sprintf('payload "assets[%d]": "slot" must be a non-empty string.', $position);
                continue;
            }

            $slot = LogoSlot::tryFrom($slotValue);
            if (!$slot instanceof LogoSlot) {
                $errors[] = sprintf(
                    'payload "assets[%d]": "%s" is not a branding logo slot this layer owns.',
                    $position,
                    self::echoable($slotValue),
                );
                continue;
            }

            $path = $entry['path'] ?? null;
            if (!is_string($path) || $path === '') {
                $errors[] = sprintf('payload "assets[%d]": "path" must be a non-empty string.', $position);
                continue;
            }

            $requests[] = new AssetIntakeRequest($slot, $path, basename($path));
        }

        return $requests;
    }

    /**
     * The `globals.gl_value` spelling of a JSON scalar, or null when it is not one.
     *
     * Booleans become `'1'` / `'0'` because that is what BrandingValueType::Flag reads
     * back; floats are refused rather than formatted, since no branding global is a
     * fractional value and a locale-dependent rendering is not worth inventing.
     */
    private static function scalarToGlobalValue(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return null;
    }

    /**
     * A rejected key, made safe to print.
     *
     * It came out of a file, so it can carry terminal control sequences; anything outside
     * the character set a globals name can legitimately use becomes `?`.
     */
    private static function echoable(string $name): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_.-]/', '?', $name);
        if ($safe === null) {
            return '?';
        }

        return substr($safe, 0, self::ECHO_LIMIT);
    }
}
