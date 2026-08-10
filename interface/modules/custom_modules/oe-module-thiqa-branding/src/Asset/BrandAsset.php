<?php

/**
 * A resolved branding image: where it is, which slot it fills, and how it is announced.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Asset;

/**
 * The unit templates receive instead of a bare path string (plan §3.8.1).
 *
 * Three things travel together deliberately:
 *
 *  - `$webPath` is the FINAL URL, already carrying every cache identifier. It is composed
 *    once, by BrandAssetResolver, so no template can emit a half-keyed URL;
 *  - `$revision` is retained even though it is already inside `$webPath`, because the
 *    SMART style contract and the observability surfaces report it separately;
 *  - the accessible name is carried in both English and Arabic, so an RTL surface does not
 *    have to fall back to a Latin alt attribute.
 *
 * An unresolved slot is represented by `missing()` — an asset whose `$webPath` is the empty
 * string — rather than by null or by a substituted default path. A tenant that has not
 * supplied a logo renders nothing at all; it must never render another scope's image
 * (plan §3.9, cross-tenant bleed).
 */
final readonly class BrandAsset
{
    /**
     * @param string $webPath       final, cache-keyed web path; empty when the slot has no
     *                              asset in this tenant's scope
     * @param string $altText       accessible name in English; empty means decorative
     * @param string $altTextArabic accessible name in Arabic; empty falls back to $altText
     */
    public function __construct(
        public LogoSlot $slot,
        public BrandingRevision $revision,
        public string $webPath,
        public string $altText,
        public string $altTextArabic,
    ) {
    }

    /**
     * The slot has no asset in this tenant's scope.
     *
     * Deliberately not a null object with a placeholder path: there is no safe placeholder,
     * because every candidate would belong to some other scope or to upstream branding.
     */
    public static function missing(LogoSlot $slot, BrandingRevision $revision): self
    {
        return new self($slot, $revision, '', '', '');
    }

    /** The URL to emit. Empty string when the slot is unresolved — callers check isResolved(). */
    public function url(): string
    {
        return $this->webPath;
    }

    public function isResolved(): bool
    {
        return $this->webPath !== '';
    }

    /**
     * True when the image carries no information and must be announced as decorative.
     *
     * The favicon is the only slot in the closed set that is decorative by nature; an
     * unresolved asset is also decorative because there is nothing to announce.
     */
    public function isDecorative(): bool
    {
        return $this->altText === '' && $this->altTextArabic === '';
    }

    /** The accessible name for the requested language, with a defined fallback. */
    public function alt(bool $arabic = false): string
    {
        if ($arabic && $this->altTextArabic !== '') {
            return $this->altTextArabic;
        }

        return $this->altText;
    }

    /**
     * The certified pixel dimensions for this slot, or null where the slot has no fixed size.
     *
     * @return array{width: int, height: int}|null
     */
    public function expectedDimensions(): ?array
    {
        return $this->slot->expectedDimensions();
    }

    public function equals(self $other): bool
    {
        return $this->slot === $other->slot
            && $this->revision->equals($other->revision)
            && $this->webPath === $other->webPath
            && $this->altText === $other->altText
            && $this->altTextArabic === $other->altTextArabic;
    }
}
