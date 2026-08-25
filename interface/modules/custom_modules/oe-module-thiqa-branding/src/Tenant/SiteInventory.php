<?php

/**
 * Enumerates the tenants configured on this installation, from the filesystem alone.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Tenant;

/**
 * Finding B1: a second configured tenant was invisible to the whole branding toolchain.
 *
 * `--site` is mandatory and has no default — deliberately, and that decision stands (see
 * {@see \OpenEMR\Modules\ThiqaBranding\Console\SiteOption}). But nothing anywhere
 * enumerated sites, so a branding run against one tenant exited 0 having silently left a
 * second, fully branded tenant untouched. The rename hazard is real: a `modules` row per
 * database carries `mod_directory`, and `ModulesApplication` deactivates a module whose
 * directory stops resolving. Two databases, two rows, one of them unknown to the operator.
 *
 * This class does not fix that by acting on more tenants. It fixes it by making the ones
 * *not* acted on impossible to overlook.
 *
 * **Filesystem only, by contract.**
 *
 *  - No database connection is opened, here or transitively. A tenant whose database is
 *    down, moved or mid-restore must still appear in the inventory: an instance you cannot
 *    connect to is exactly the one most likely to be forgotten.
 *  - `sqlconf.php` is *parsed*, never `include`d. Including it would define `$host`,
 *    `$login`, `$pass` and `$dbase` into the caller's scope, and would run whatever else
 *    the file contains, for every site, on every branding command.
 *  - The parse keeps one integer. The bytes of the file, credentials included, are read
 *    into memory because there is no way to read a file without reading it, and are then
 *    discarded; nothing derived from them but that integer leaves this method, so no
 *    credential can reach an output stream. {@see SiteInventoryReport} carries site ids
 *    and counts, and nothing else.
 *
 * **Why the tokenizer rather than a regular expression.** `$config = 1;` is written by
 * `Installer::write_configuration_file()` in a fixed shape today, but a commented-out
 * `// $config = 1;` above a live `$config = 0;` would fool any line-oriented match — and
 * would fool it in the dangerous direction, reporting an abandoned site as configured, or
 * (with the operands the other way round) hiding a live one. `token_get_all()` knows what
 * a comment is. The *last* assignment wins, which is what PHP itself would do.
 */
final readonly class SiteInventory
{
    /**
     * The value `$config` must hold for a site to count as installed.
     *
     * `src/Health/Check/InstallationCheck.php` uses the same test on the same variable:
     * "0 means not installed, 1 means installed".
     */
    private const INSTALLED = 1;

    private const CONFIG_VARIABLE = '$config';

    private const SQLCONF_FILENAME = 'sqlconf.php';

    /**
     * Refuse to tokenize anything implausibly large.
     *
     * A real `sqlconf.php` is well under a kilobyte. The cap costs nothing and stops a
     * stray multi-megabyte file under `sites/` from turning a branding command into a
     * memory incident.
     */
    private const MAX_SQLCONF_BYTES = 262144;

    /** @param string $sitesDirectory absolute path to the installation's `sites/` directory */
    public function __construct(private string $sitesDirectory)
    {
    }

    public function take(): SiteInventoryReport
    {
        if (!is_dir($this->sitesDirectory) || !is_readable($this->sitesDirectory)) {
            return SiteInventoryReport::unreadable();
        }

        $entries = scandir($this->sitesDirectory);
        if ($entries === false) {
            return SiteInventoryReport::unreadable();
        }

        $configured = [];
        $unsupported = 0;

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $this->sitesDirectory . DIRECTORY_SEPARATOR . $entry;
            if (!is_dir($path)) {
                continue;
            }

            if (!$this->isConfigured($path . DIRECTORY_SEPARATOR . self::SQLCONF_FILENAME)) {
                continue;
            }

            $site = SiteId::tryFrom($entry);
            if (!$site instanceof SiteId) {
                // A configured tenant this layer cannot name. Counted, never printed.
                $unsupported++;
                continue;
            }

            $configured[] = $site;
        }

        return SiteInventoryReport::of($configured, $unsupported);
    }

    /** True when this site's sqlconf.php establishes `$config = 1`. */
    private function isConfigured(string $sqlconf): bool
    {
        if (!is_file($sqlconf) || !is_readable($sqlconf)) {
            return false;
        }

        $size = filesize($sqlconf);
        if ($size === false || $size > self::MAX_SQLCONF_BYTES) {
            return false;
        }

        $source = file_get_contents($sqlconf);
        if ($source === false) {
            return false;
        }

        return $this->lastConfigAssignment($source) === self::INSTALLED;
    }

    /**
     * The integer the final top-level `$config = …` assignment would leave behind.
     *
     * Null when the file never assigns `$config`, or assigns it something this parser
     * declines to evaluate — an expression, a constant, a concatenation. Null is not
     * "installed", which is the safe direction: an unparseable site is reported as absent
     * from the inventory rather than asserted into it.
     */
    private function lastConfigAssignment(string $source): ?int
    {
        $tokens = token_get_all($source);
        $count = count($tokens);
        $found = null;

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token) || $token[0] !== T_VARIABLE || $token[1] !== self::CONFIG_VARIABLE) {
                continue;
            }

            $operator = $this->nextSignificant($tokens, $i + 1);
            // A bare `=`, not `==`, `===` or `.=`: those arrive as array tokens, so an
            // identity comparison against the string is already the whole test.
            if ($operator === null || $tokens[$operator] !== '=') {
                continue;
            }

            $value = $this->nextSignificant($tokens, $operator + 1);
            $found = $value === null ? null : $this->literalInt($tokens[$value]);
        }

        return $found;
    }

    /**
     * @param array<int, array{int, string, int}|string> $tokens
     */
    private function nextSignificant(array $tokens, int $from): ?int
    {
        $count = count($tokens);

        for ($i = $from; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token)) {
                return $i;
            }

            if (!in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                return $i;
            }
        }

        return null;
    }

    /**
     * `1` and `'1'` both count: the installer writes the integer, but a hand-edited site
     * quoting it is still plainly saying the same thing.
     *
     * @param array{int, string, int}|string $token
     */
    private function literalInt(array|string $token): ?int
    {
        if (!is_array($token)) {
            return null;
        }

        if ($token[0] === T_LNUMBER) {
            return ctype_digit($token[1]) ? (int) $token[1] : null;
        }

        if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
            $literal = substr($token[1], 1, -1);

            return ctype_digit($literal) ? (int) $literal : null;
        }

        return null;
    }
}
