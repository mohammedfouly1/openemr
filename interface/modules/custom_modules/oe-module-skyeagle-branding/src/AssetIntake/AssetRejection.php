<?php

/**
 * One refused candidate logo, in a form that is safe to log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\AssetIntake;

use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;

/**
 * Mirrors {@see \OpenEMR\Modules\SkyEagleBranding\Token\TokenRejection}: the refused
 * *content* is never carried here. A rejected asset is untrusted binary and this object
 * ends up in logs and operator reports, so only the slot (an enum literal) and a
 * sanitised, clipped fragment of the declared filename are echoed back.
 *
 * `$detail` is likewise built only from fixed sentences and integers.
 */
final readonly class AssetRejection
{
    /** Longest filename fragment ever echoed back. */
    public const NAME_PREVIEW_LENGTH = 64;

    private function __construct(
        public string $slot,
        public string $sourceName,
        public AssetRejectionReason $reason,
        public string $detail,
    ) {
    }

    /**
     * @param string $rawSourceName Untrusted; only a sanitised preview is retained.
     * @param string|null $detail Must be caller-built from fixed text and integers only.
     */
    public static function forSlot(
        LogoSlot $slot,
        string $rawSourceName,
        AssetRejectionReason $reason,
        ?string $detail = null,
    ): self {
        return new self(
            $slot->value,
            self::clip($rawSourceName),
            $reason,
            $detail ?? $reason->summary(),
        );
    }

    /** One human-readable line, safe to write straight into a log or a report. */
    public function message(): string
    {
        return sprintf('%s (%s): %s', $this->slot, $this->sourceName, $this->detail);
    }

    /**
     * Structured form for a PSR-3 context array.
     *
     * @return array{slot: string, source: string, reason: string, detail: string}
     */
    public function toContext(): array
    {
        return [
            'slot' => $this->slot,
            'source' => $this->sourceName,
            'reason' => $this->reason->value,
            'detail' => $this->detail,
        ];
    }

    private static function clip(string $rawSourceName): string
    {
        $printable = preg_replace('/[^\x20-\x7E]/', '?', $rawSourceName) ?? '';

        return strlen($printable) > self::NAME_PREVIEW_LENGTH
            ? substr($printable, 0, self::NAME_PREVIEW_LENGTH) . '...'
            : $printable;
    }
}
