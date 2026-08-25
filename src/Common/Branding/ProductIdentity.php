<?php

/**
 * Product identity for code that runs before the database exists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Branding;

/**
 * The read side of the pre-database identity seam (finding S3-P1-33).
 *
 * Three call sites need to name the product before anything the normal branding layer
 * depends on exists:
 *
 *  - `setup.php` runs with no database at all, so `xl()`, `xlp()`, `xl_product_name()` and
 *    every `globals` read are unavailable to it by construction.
 *  - `interface/globals.php`'s openssl checks run before the globals bag is populated and
 *    `exit(1)` if they fail, so they cannot wait for one.
 *  - `library/globals.inc.php` declares the *default* value of `openemr_name` and the
 *    branding URLs. `Installer::insert_globals()` requires that file during installation
 *    and writes those defaults into the `globals` table -- so the default decides what a
 *    freshly installed product calls itself, and it is chosen strictly before the row it
 *    populates exists.
 *
 * Each of those used to carry a hardcoded literal, which is how `setup.php` came to print
 * "Thiqa Setup Tool" and "Congratulations! OpenEMR is now installed." on the same page.
 * They now read this class, which reads one generated artefact derived from the
 * authoritative branding profile by `tools/branding/bin/generate-product-identity.php`.
 *
 * **This class is deliberately brand-neutral.** It contains no product name of its own
 * beyond the upstream fallbacks below, so a future rename is a profile edit plus a
 * generator run, not a hunt through bootstrap code.
 *
 * ### Why the artefact returns an array rather than defining constants
 *
 * `define()` was the obvious alternative and was rejected on four counts, all of which
 * are properties of *this* codebase rather than general preference:
 *
 *  1. **A constant cannot be redefined.** The artefact is reachable from more than one
 *     entry point in a single request (`setup.php` requires `Installer.class.php`, which
 *     requires `library/globals.inc.php`), so every consumer would need a `defined()`
 *     guard, and a missed guard is a warning in the middle of an installer page.
 *  2. **Process-global, first-write-wins.** A per-tenant identity -- the direction the
 *     five-plane architecture is heading -- cannot be expressed by a constant at all,
 *     because the first site loaded in a process would fix the value for every later one.
 *  3. **Untestable on this host.** Exercising missing / corrupt / valid artefacts in one
 *     PHPUnit process requires loading several different artefacts. With constants the
 *     first load poisons the process, and the usual escape hatch --
 *     `#[RunInSeparateProcess]` -- hangs on the project's documented native-Windows
 *     development host. A returned array has value semantics, so each load is independent
 *     and the memoisation below is keyed by path.
 *  4. **Global namespace pollution**, which this project's own PHPStan rules treat as a
 *     defect class in every other form it takes.
 *
 * ### Escaping
 *
 * Values are returned raw. Every consumer is responsible for escaping at its own point of
 * use -- `text()` in HTML body copy, `attr()` in an attribute, `attr_url()` in an href --
 * because the correct escaping depends on the sink, not on the source. The generator
 * additionally *refuses* to emit a value containing `< > " ' & \` or a backtick
 * (`OpenEMR\Branding\ProductIdentityKey::rejectionReason()`), so a call site that forgets
 * is a defect to fix rather than an injection.
 */
final class ProductIdentity
{
    /** Repo-relative location of the generated artefact. */
    public const ARTEFACT_RELATIVE_PATH = 'library/product_identity.generated.php';

    /**
     * What this class answers with when the artefact is absent or unusable.
     *
     * These are upstream OpenEMR's own values, and that is the correct answer rather than
     * a placeholder: a checkout with no branding artefact *is* an unbranded OpenEMR, and
     * saying so is both accurate and the behaviour the GPL notices elsewhere in the tree
     * assume. Failing hard was considered and rejected -- the artefact is read on the
     * installer's first page and on the pre-bootstrap fatal path, so a missing branding
     * file must never be the thing that stops an install or masks an openssl misconfig.
     * The fallback is logged, once per path per process, so it is visible rather than
     * silent.
     *
     * @var array<string, string>
     */
    private const FALLBACK = [
        'product_name' => 'OpenEMR',
        'product_website_url' => 'https://www.open-emr.org/',
        'product_support_url' => 'https://www.open-emr.org/',
        'product_documentation_url' => 'https://www.open-emr.org/wiki/',
    ];

    /**
     * Resolved identity per artefact path, so the file is read at most once per path in a
     * request. Keyed by path rather than held in a single slot precisely so that tests --
     * and, later, a multi-tenant caller -- can load more than one artefact in a process.
     *
     * @var array<string, array<string, string>>
     */
    private static array $resolved = [];

    /** The product's own name, e.g. for a page title or an installer heading. */
    public static function name(): string
    {
        return self::value('product_name');
    }

    /** The product website, as the main menu logo links to it. */
    public static function websiteUrl(): string
    {
        return self::value('product_website_url');
    }

    /** The product support page. */
    public static function supportUrl(): string
    {
        return self::value('product_support_url');
    }

    /** The product user manual / documentation root. */
    public static function documentationUrl(): string
    {
        return self::value('product_documentation_url');
    }

    /**
     * The whole identity, for callers that need several values at once.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::load(self::defaultArtefactPath());
    }

    /**
     * Loads and validates one artefact, falling back to {@see self::FALLBACK} for anything
     * missing or malformed.
     *
     * Exposed so the isolated suite can exercise the missing, corrupt and valid cases
     * against fixture files instead of the shipped artefact. Production callers use the
     * accessors above.
     *
     * @return array<string, string>
     */
    public static function load(string $artefactPath): array
    {
        if (isset(self::$resolved[$artefactPath])) {
            return self::$resolved[$artefactPath];
        }

        return self::$resolved[$artefactPath] = self::resolve($artefactPath);
    }

    /** Absolute path of the artefact this installation ships. */
    public static function defaultArtefactPath(): string
    {
        // src/Common/Branding -> src/Common -> src -> <application root>.
        return dirname(__DIR__, 3) . '/' . self::ARTEFACT_RELATIVE_PATH;
    }

    /**
     * @return array<string, string>
     */
    private static function resolve(string $artefactPath): array
    {
        if (!is_file($artefactPath) || !is_readable($artefactPath)) {
            self::reportFallback($artefactPath, 'the artefact is missing or unreadable');

            return self::FALLBACK;
        }

        // The artefact is generated, contains a single `return` of an array of string
        // literals, and is verified byte-for-byte by the generator's --check mode in CI.
        // A syntax error in it would be an uncatchable compile-time fatal -- PHP offers no
        // way to guard an include against that -- so the guard is that nothing hand-edits
        // the file and `composer php-syntax-check` lints it along with every other PHP
        // file in the tree. Everything a `return`ed value *can* get wrong is checked here.
        $loaded = require $artefactPath;

        if (!is_array($loaded)) {
            self::reportFallback($artefactPath, 'the artefact did not return an array');

            return self::FALLBACK;
        }

        $identity = [];
        foreach (self::FALLBACK as $key => $default) {
            $value = $loaded[$key] ?? null;
            if (!is_string($value) || $value === '') {
                self::reportFallback($artefactPath, sprintf('"%s" is missing or is not a non-empty string', $key));
                $identity[$key] = $default;
                continue;
            }
            $identity[$key] = $value;
        }

        return $identity;
    }

    private static function value(string $key): string
    {
        $identity = self::load(self::defaultArtefactPath());

        // load() always returns every FALLBACK key, so this coalesce is unreachable in
        // practice; it is here so the return type is honest without a cast.
        return $identity[$key] ?? self::FALLBACK[$key] ?? '';
    }

    /**
     * PSR-3 is unavailable here by definition: this class is read before the container,
     * the logger and the database exist, and `interface/globals.php` calls it on a path
     * that has already decided to `exit(1)`. `error_log()` is the only sink guaranteed to
     * be there, and is what the surrounding bootstrap code already uses.
     */
    private static function reportFallback(string $artefactPath, string $reason): void
    {
        error_log(sprintf(
            'Product identity artefact "%s" is unusable (%s); falling back to upstream identity. '
                . 'Re-run tools/branding/bin/generate-product-identity.php.',
            $artefactPath,
            $reason,
        ));
    }
}
