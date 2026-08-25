<?php

/**
 * The tenant-side security boundary for an untrusted logo binary.
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
use Psr\Log\LoggerInterface;

/**
 * Runs unconditionally, even though the Control Plane validated the same file already.
 *
 * That duplication is the design (plan section 3.9): the tenant runtime does not trust
 * the Control Plane blindly, so a compromised or buggy CP cannot push a hostile asset
 * into a tenant's site directory. This class is the last gate before bytes land
 * somewhere a browser can fetch them same-origin.
 *
 * What is deliberately *not* trusted:
 *   - the filename extension (compared against the content, never used to decide it),
 *   - any client-supplied MIME type (not accepted as a parameter at all),
 *   - the fact that upstream said the file was fine.
 */
final readonly class LogoValidator
{
    /**
     * Byte cap for a logo slot.
     *
     * The plan's guidance is "under about 50 KB", but the certified
     * `login-primary-1053x390.png` is 54,480 bytes, so a literal 50 KB cap would refuse
     * the product's own artwork. 128 KiB clears the largest certified asset by 2.4x
     * while still being far too small to hide a meaningful payload alongside a real
     * image, and it bounds the memory this class holds per candidate.
     */
    public const DEFAULT_MAX_LOGO_BYTES = 131072;

    /**
     * Byte cap for the favicon slot.
     *
     * The certified multi-resolution `favicon.ico` is 15,086 bytes; 32 KiB is the same
     * kind of headroom applied to the plan's "under about 20 KB" guidance.
     */
    public const DEFAULT_MAX_FAVICON_BYTES = 32768;

    /** Below this nothing can carry even a header for any permitted format. */
    private const MIN_BYTES = 22;

    public function __construct(
        private RasterImageReader $rasterReader,
        private SvgInspector $svgInspector,
        private LoggerInterface $logger,
        private int $maxLogoBytes = self::DEFAULT_MAX_LOGO_BYTES,
        private int $maxFaviconBytes = self::DEFAULT_MAX_FAVICON_BYTES,
    ) {
    }

    /**
     * @param string $sourcePath Path to the staged candidate. Read only; never echoed.
     * @param string $declaredFilename Untrusted name the intake job supplied. Only its
     *                                 extension is examined, and only to check that it
     *                                 agrees with the bytes. It never reaches the
     *                                 filesystem: the placed name comes from the slot.
     */
    public function validate(LogoSlot $slot, string $sourcePath, string $declaredFilename): AssetValidationResult
    {
        try {
            return $this->run($slot, $sourcePath, $declaredFilename);
        } catch (AssetInspectionException $inspection) {
            return $this->refuse($slot, $declaredFilename, $inspection->reason, $inspection->detail);
        }
        // There is deliberately no catch-all safety net here. run() declares
        // AssetInspectionException as its only throw, and PHPStan confirms no
        // LogicException or RuntimeException escapes it, so any broader catch is dead
        // code. A wider net is not available either: ForbiddenCatchTypeRule refuses
        // \Throwable and \Exception because they would suppress \Error. The
        // consequence, stated rather than hidden: an \Error (for example a TypeError
        // from a future refactor) propagates to the caller instead of degrading into a
        // rejection. That is the guardrail's chosen trade-off, not an oversight.
    }

    /** @throws AssetInspectionException */
    private function run(LogoSlot $slot, string $sourcePath, string $declaredFilename): AssetValidationResult
    {
        $bytes = $this->readCandidate($slot, $sourcePath);
        if (!is_string($bytes)) {
            return $bytes;
        }

        $format = $this->detectFormat($bytes);

        $slotCheck = $this->checkSlotAcceptsFormat($slot, $declaredFilename, $format);
        if ($slotCheck !== null) {
            return $slotCheck;
        }

        $declared = ImageFormat::fromExtension(pathinfo($declaredFilename, PATHINFO_EXTENSION));
        if ($declared !== $format) {
            return $this->refuse(
                $slot,
                $declaredFilename,
                AssetRejectionReason::ExtensionMismatch,
                'File content is ' . $format->value . ', which the declared extension does not match.',
            );
        }

        $dimensions = $format->isVector()
            ? $this->svgInspector->inspect($bytes)
            : $this->rasterReader->read($format, $bytes);

        $expected = ImageDimensions::forSlot($slot);
        if ($expected !== null && !$expected->equals($dimensions)) {
            return $this->refuse(
                $slot,
                $declaredFilename,
                AssetRejectionReason::DimensionMismatch,
                'Slot requires ' . $expected->describe() . ' but the file is ' . $dimensions->describe() . '.',
            );
        }

        $asset = ValidatedAsset::certified($slot, $format, $bytes, $dimensions);

        $this->logger->info('Tenant logo accepted for placement', [
            'slot' => $slot->value,
            'brand_id' => $slot->brandId(),
            'format' => $format->value,
            'dimensions' => $dimensions->describe(),
            'bytes' => $asset->byteLength(),
            'sha256' => $asset->sha256,
        ]);

        return AssetValidationResult::accepted($asset);
    }

    /**
     * Stat, cap, then read.
     *
     * The size is checked from the stat result before any read, so an enormous file is
     * refused without being pulled into memory.
     *
     * @return string|AssetValidationResult the bytes, or the refusal that ended it
     */
    private function readCandidate(LogoSlot $slot, string $sourcePath): string|AssetValidationResult
    {
        $name = basename($sourcePath);

        if ($sourcePath === '' || str_contains($sourcePath, "\0") || !is_file($sourcePath) || !is_readable($sourcePath)) {
            return $this->refuse($slot, $name, AssetRejectionReason::UnreadableSource);
        }

        $size = filesize($sourcePath);
        if ($size === false) {
            return $this->refuse($slot, $name, AssetRejectionReason::UnreadableSource);
        }

        if ($size === 0) {
            return $this->refuse($slot, $name, AssetRejectionReason::EmptyFile);
        }

        $cap = $this->capFor($slot);
        if ($size > $cap) {
            return $this->refuse(
                $slot,
                $name,
                AssetRejectionReason::TooLarge,
                'File is ' . $size . ' bytes; the cap for this slot is ' . $cap . '.',
            );
        }

        if ($size < self::MIN_BYTES) {
            return $this->refuse($slot, $name, AssetRejectionReason::TooSmall);
        }

        $bytes = file_get_contents($sourcePath, false, null, 0, $cap + 1);
        if ($bytes === false || $bytes === '') {
            return $this->refuse($slot, $name, AssetRejectionReason::UnreadableSource);
        }

        // The file could have grown between the stat and the read.
        if (strlen($bytes) > $cap) {
            return $this->refuse($slot, $name, AssetRejectionReason::TooLarge);
        }

        return $bytes;
    }

    /**
     * Decide the format from the bytes alone.
     *
     * More than one signature match means the file is a deliberate polyglot. The four
     * permitted signatures are pairwise disjoint today, so this branch is unreachable
     * with the current enum; it stays because the enum is the thing most likely to gain
     * a case, and a silent "first match wins" would then become a real bypass.
     *
     * @throws AssetInspectionException
     */
    private function detectFormat(string $bytes): ImageFormat
    {
        $matches = ImageFormat::signatureMatches(substr($bytes, 0, 16));

        if (count($matches) > 1) {
            throw AssetInspectionException::because(AssetRejectionReason::AmbiguousFormat);
        }

        if (count($matches) === 1) {
            return $matches[0];
        }

        // No binary signature. The only remaining candidate is XML, and it must open as
        // XML: a byte-order mark and leading whitespace are tolerated, but no HTML
        // preamble, no shebang, nothing else before the declaration or the root tag.
        $head = ltrim(ltrim(substr($bytes, 0, 512), "\xEF\xBB\xBF"));
        if (str_starts_with($head, '<?xml') || str_starts_with($head, '<svg')) {
            return ImageFormat::Svg;
        }

        throw AssetInspectionException::because(AssetRejectionReason::UnsupportedFormat);
    }

    /**
     * Some slots fix their filename, which in turn fixes the format.
     *
     * `LogoSlot::CoreFavicon` is requested by `Header::getFavIcon()` as the literal name
     * `favicon.ico`, so a PNG placed there would simply never be served. Accepting it
     * would leave a tenant convinced their favicon was applied.
     */
    private function checkSlotAcceptsFormat(
        LogoSlot $slot,
        string $declaredFilename,
        ImageFormat $format,
    ): ?AssetValidationResult {
        $pattern = $slot->filenamePattern();
        if (str_ends_with($pattern, '.*')) {
            return null;
        }

        $required = ImageFormat::fromExtension(pathinfo($pattern, PATHINFO_EXTENSION));
        if ($required === $format) {
            return null;
        }

        return $this->refuse(
            $slot,
            $declaredFilename,
            AssetRejectionReason::SlotFormatNotPermitted,
            'Slot is served as ' . $pattern . ' and therefore only accepts '
                . ($required->value ?? 'no known format') . '.',
        );
    }

    private function capFor(LogoSlot $slot): int
    {
        return $slot === LogoSlot::CoreFavicon ? $this->maxFaviconBytes : $this->maxLogoBytes;
    }

    /** Build the refusal and log it, picking the level from how hostile the reason is. */
    private function refuse(
        LogoSlot $slot,
        string $declaredFilename,
        AssetRejectionReason $reason,
        ?string $detail = null,
    ): AssetValidationResult {
        $rejection = AssetRejection::forSlot($slot, $declaredFilename, $reason, $detail);

        if ($reason->isHostile()) {
            $this->logger->warning('Tenant logo refused as hostile', $rejection->toContext());
        } else {
            $this->logger->notice('Tenant logo refused', $rejection->toContext());
        }

        return AssetValidationResult::rejected($rejection);
    }
}
