<?php

/**
 * The closed set of fields the pre-database product identity artefact carries.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * Every case names one artefact key, where that key's value is read from in the
 * authoritative branding profile, and the shape a value must have to be emitted.
 *
 * The enum is closed on purpose. A generated file that any profile row could add a key
 * to would be a growing, unreviewed surface inside a file that core bootstrap scripts
 * `require`; a fixed set means the artefact's shape is decided here, in source, and the
 * profile only supplies values for it.
 *
 * The validation is *not* defence against a hostile profile author -- anyone who can edit
 * `branding-profile.json` can edit this file too. It is defence against the artefact
 * becoming an injection channel once values start arriving from somewhere else (a control
 * plane export, a tenant-supplied name), and against a plausible typo shipping a broken
 * installer page. Rejecting rather than escaping is the choice throughout: an identity
 * string that needs escaping to be safe in HTML is not an identity string anyone wants on
 * an installer page.
 */
enum ProductIdentityKey: string
{
    /** The product's own name, as the installer and the pre-bootstrap fatal handlers print it. */
    case ProductName = 'product_name';

    /** Where the main menu logo points; the upstream default is `https://www.open-emr.org/`. */
    case WebsiteUrl = 'product_website_url';

    /** Where "Online Support" points; the upstream default is `http://open-emr.org/`. */
    case SupportUrl = 'product_support_url';

    /** The user-manual override; the upstream default is blank, which auto-generates a wiki URL. */
    case DocumentationUrl = 'product_documentation_url';

    /**
     * Emission order for the artefact, fixed here rather than taken from the profile
     * document, so the output cannot reorder when the profile is edited.
     *
     * @return non-empty-list<self>
     */
    public static function emissionOrder(): array
    {
        return [self::ProductName, self::WebsiteUrl, self::SupportUrl, self::DocumentationUrl];
    }

    /**
     * How this key's value is located in `config/branding-profile.json`.
     *
     * Both kinds are plain data reads. Nothing in the profile can name a source that is
     * not one of these two, so no profile edit can widen what the generator reads.
     */
    public function sourceKind(): ProductIdentitySourceKind
    {
        return match ($this) {
            self::ProductName => ProductIdentitySourceKind::DocumentMember,
            self::WebsiteUrl, self::SupportUrl, self::DocumentationUrl => ProductIdentitySourceKind::GlobalsRow,
        };
    }

    /** The document member name, or the `globals` row key, that carries this value. */
    public function sourceName(): string
    {
        return match ($this) {
            self::ProductName => 'product_name',
            self::WebsiteUrl => 'main_menu_logo_link',
            self::SupportUrl => 'online_support_link',
            self::DocumentationUrl => 'user_manual_link',
        };
    }

    /** Longest value accepted, in characters. `globals.gl_value` is `varchar(255)`. */
    public function maximumLength(): int
    {
        return match ($this) {
            self::ProductName => 64,
            self::WebsiteUrl, self::SupportUrl, self::DocumentationUrl => 255,
        };
    }

    /**
     * Rejects a value this key must not carry, or returns null when the value is acceptable.
     *
     * Shared rules, applied before the per-key rules:
     *
     *  - **Valid UTF-8**, checked first because every later `preg_match` with the `u`
     *    modifier returns `false` rather than `0` on invalid input, which a `=== 1` test
     *    would read as "no match found" and wave through.
     *  - **No C0/C1 control characters**, including NUL, CR and LF. A newline inside a
     *    single-quoted PHP literal is legal and would survive `var_export()`, so it is the
     *    one class of character that could make the emitted file differ structurally from
     *    what a reviewer reads on one line.
     *  - **No `<`, `>`, `"`, `'`, `&`, backslash or backtick.** The artefact's consumers are
     *    HTML (`setup.php`), HTML attributes, and a bare `echo` on `interface/globals.php`'s
     *    pre-bootstrap fatal path. Those call sites escape -- but a value that cannot carry
     *    a metacharacter cannot be mis-escaped by a *future* call site either. The backslash
     *    and single-quote exclusions additionally mean `var_export()` has nothing to escape,
     *    so the emitted literal is always the input verbatim.
     */
    public function rejectionReason(string $value): ?string
    {
        if ($value === '') {
            return 'is empty; the artefact carries no blank identity values.';
        }

        if (preg_match('//u', $value) !== 1) {
            return 'is not valid UTF-8.';
        }

        // Counted in characters, matching varchar(255); a byte count would reject
        // legitimate non-Latin names that fit the column perfectly well.
        $length = mb_strlen($value, 'UTF-8');
        if ($length > $this->maximumLength()) {
            return sprintf('is %d characters long; the maximum for this key is %d.', $length, $this->maximumLength());
        }

        if (preg_match('/[\x00-\x1F\x7F]/u', $value) === 1) {
            return 'contains a control character.';
        }

        if (preg_match('/[<>"\'&\\\\`]/u', $value) === 1) {
            return 'contains one of < > " \' & \\ `, which an identity value may never carry.';
        }

        return match ($this) {
            self::ProductName => $this->rejectProductName($value),
            self::WebsiteUrl, self::SupportUrl, self::DocumentationUrl => $this->rejectUrl($value),
        };
    }

    private function rejectProductName(string $value): ?string
    {
        if (trim($value) !== $value) {
            return 'has leading or trailing whitespace.';
        }

        return null;
    }

    private function rejectUrl(string $value): ?string
    {
        if (!str_starts_with($value, 'https://')) {
            // http:// is refused, not upgraded. One of the upstream defaults this artefact
            // replaces is a plain-HTTP support link, and silently rewriting a scheme would
            // hide that the profile still carried one.
            return 'must be an absolute https:// URL.';
        }

        if (preg_match('/\s/u', $value) === 1) {
            return 'contains whitespace.';
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return 'is not a well-formed URL.';
        }

        return null;
    }
}
