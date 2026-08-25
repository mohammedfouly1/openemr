<?php

/**
 * One refused entry from a proposed tenant overlay.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Token;

/**
 * The rejected *value* is deliberately never carried here: it is untrusted input and
 * this object ends up in logs and operator-facing reports. Only the key is echoed, and
 * only after being clipped to a length that cannot be used to flood a log line.
 */
final readonly class TokenRejection
{
    /** Longest key fragment ever echoed back. */
    public const KEY_PREVIEW_LENGTH = 64;

    private function __construct(
        public string $key,
        public RejectionReason $reason,
        public string $detail,
    ) {
    }

    /** For an entry whose key never resolved to a TokenKey; $rawKey is untrusted. */
    public static function forRawKey(string $rawKey, RejectionReason $reason, ?string $detail = null): self
    {
        return new self(
            self::clip($rawKey),
            $reason,
            $detail ?? $reason->summary(),
        );
    }

    /** For an entry whose key did resolve; the echoed name is then an enum literal. */
    public static function forKey(TokenKey $key, RejectionReason $reason, ?string $detail = null): self
    {
        return new self($key->value, $reason, $detail ?? $reason->summary());
    }

    private static function clip(string $rawKey): string
    {
        $printable = preg_replace('/[^\x20-\x7E]/', '?', $rawKey) ?? '';

        return strlen($printable) > self::KEY_PREVIEW_LENGTH
            ? substr($printable, 0, self::KEY_PREVIEW_LENGTH) . '...'
            : $printable;
    }
}
