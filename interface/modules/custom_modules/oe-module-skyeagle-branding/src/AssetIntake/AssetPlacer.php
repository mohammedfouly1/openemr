<?php

/**
 * Atomic placement of a validated logo into a tenant's site image directory.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\AssetIntake;

use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Writes to `<sitesRoot>/<site>/images/logos/<slot>/<name>`, which is where
 * `LogoService::getLogo()` looks (`OE_SITE_DIR . "/images/logos/" . $type . "/"`).
 *
 * Two properties matter more than anything else here.
 *
 * **Nothing the caller typed contributes a path segment.** The site comes from a parsed
 * {@see SiteId}, the slot subdirectory is `LogoSlot`'s backed value from a closed enum,
 * and the filename is derived by {@see ValidatedAsset::targetFilename()} from the slot's
 * own pattern plus the format proven by the magic bytes. There is no concatenation of an
 * untrusted string anywhere in this class, and a `realpath()` containment check catches
 * the remaining case a pure-string analysis cannot: a symlinked site directory pointing
 * out of the tree.
 *
 * **The write is staged then renamed.** `rename()` within one directory is atomic, so a
 * reader never sees a half-written logo, and a failure at any earlier step leaves the
 * previous revision's file untouched (plan section 4.4).
 *
 * Modes are fixed at 0644 for files and 0755 for directories. The directory serves
 * static content only; nothing written here is ever executable.
 */
final readonly class AssetPlacer
{
    private const DIRECTORY_MODE = 0o755;
    private const FILE_MODE = 0o644;

    /**
     * Every name this class is ever allowed to create.
     *
     * A closed regex rather than a derivation, so that a future change to `LogoSlot` or
     * `ImageFormat` that could yield `logo.php` fails loudly here instead of writing it.
     */
    private const PERMITTED_TARGET_NAME = '/\A(logo\.(png|svg|gif|ico)|favicon\.ico)\z/';

    public function __construct(
        private string $sitesRoot,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return string absolute path of the placed file
     *
     * @throws AssetPlacementException when the asset is acceptable but cannot be written
     */
    public function place(ValidatedAsset $asset, SiteId $site): string
    {
        $filename = $asset->targetFilename();
        if (preg_match(self::PERMITTED_TARGET_NAME, $filename) !== 1) {
            throw AssetPlacementException::illegalTargetName();
        }

        $root = realpath($this->sitesRoot);
        if ($root === false || !is_dir($root)) {
            throw AssetPlacementException::unusableRoot();
        }

        $directory = $this->prepareSlotDirectory($root, $site, $asset);
        $target = $directory . DIRECTORY_SEPARATOR . $filename;
        $staged = $this->stage($directory, $filename, $asset);

        if (!rename($staged, $target)) {
            $this->discard($staged);
            throw AssetPlacementException::renameFailed();
        }

        $this->logger->info('Tenant logo placed', [
            'site' => $site->value,
            'slot' => $asset->slot->value,
            'brand_id' => $asset->slot->brandId(),
            'filename' => $filename,
            'bytes' => $asset->byteLength(),
            'sha256' => $asset->sha256,
        ]);

        return $target;
    }

    /**
     * Create the slot directory if needed and prove the result is inside the root.
     *
     * The containment check runs on `realpath()` output, i.e. after every symlink and
     * `..` in the *existing* tree has been resolved. That is what makes it a real check
     * rather than string hygiene: a site directory symlinked to `/var/www` resolves
     * outside the root and is refused, even though its name passed {@see SiteId}.
     */
    private function prepareSlotDirectory(string $root, SiteId $site, ValidatedAsset $asset): string
    {
        $siteDirectory = $root . DIRECTORY_SEPARATOR . $site->value;
        $slotDirectory = $siteDirectory . DIRECTORY_SEPARATOR . 'images'
            . DIRECTORY_SEPARATOR . 'logos'
            . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $asset->slot->value);

        if (!is_dir($slotDirectory) && !mkdir($slotDirectory, self::DIRECTORY_MODE, true) && !is_dir($slotDirectory)) {
            throw AssetPlacementException::directoryNotCreated();
        }

        $resolved = realpath($slotDirectory);
        if ($resolved === false) {
            throw AssetPlacementException::directoryNotCreated();
        }

        if (!$this->isInside($resolved, $root)) {
            $this->logger->error('Refused a logo placement that resolved outside the sites root', [
                'site' => $site->value,
                'slot' => $asset->slot->value,
            ]);

            throw AssetPlacementException::containmentBreach();
        }

        return $resolved;
    }

    /** True when $candidate is $root itself or lies beneath it. */
    private function isInside(string $candidate, string $root): bool
    {
        $normalisedRoot = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($candidate . DIRECTORY_SEPARATOR, $normalisedRoot);
    }

    /**
     * Write the bytes to a unique neighbour of the target and verify them.
     *
     * The staging file shares the target's directory so the later `rename()` cannot
     * cross a filesystem boundary, which is the case where `rename()` silently
     * degrades to a non-atomic copy. The digest is re-read from disk because a short
     * write on a full volume is otherwise indistinguishable from success once the
     * process has moved on.
     */
    private function stage(string $directory, string $filename, ValidatedAsset $asset): string
    {
        try {
            $suffix = bin2hex(random_bytes(8));
        } catch (Throwable) {
            throw AssetPlacementException::stagingFailed();
        }

        $staged = $directory . DIRECTORY_SEPARATOR . '.' . $filename . '.' . $suffix . '.tmp';

        $written = file_put_contents($staged, $asset->contents, LOCK_EX);
        if ($written !== $asset->byteLength()) {
            $this->discard($staged);
            throw AssetPlacementException::stagingFailed();
        }

        if (!chmod($staged, self::FILE_MODE)) {
            $this->discard($staged);
            throw AssetPlacementException::stagingFailed();
        }

        $readBack = file_get_contents($staged);
        if ($readBack === false || !hash_equals($asset->sha256, hash('sha256', $readBack))) {
            $this->discard($staged);
            throw AssetPlacementException::integrityFailed();
        }

        return $staged;
    }

    /** Best-effort cleanup; a leftover dotfile must never mask the real failure. */
    private function discard(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
