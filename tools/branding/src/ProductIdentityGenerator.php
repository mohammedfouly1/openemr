<?php

/**
 * Derives the pre-database product identity artefact from the branding profile.
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
 * `setup.php`, `interface/globals.php`'s pre-bootstrap fatal path and the
 * `$GLOBALS_METADATA` defaults in `library/globals.inc.php` all have to name the product
 * *before* a database, a globals bag, a translation catalogue or the module autoloader
 * exists. Until this generator, each of them named it by carrying a hardcoded literal,
 * which is why `setup.php` could say "Thiqa Setup Tool" and "Congratulations! OpenEMR is
 * now installed." on the same page (finding S3-P1-33).
 *
 * The repair is one deterministic build step: read the authoritative branding profile,
 * validate every value against {@see ProductIdentityKey}, and emit a PHP file that does
 * nothing but `return` an array of strings. The consumers `require` it. No database, no
 * network, no autoloader, no environment reads, no timestamps -- so the bytes are stable
 * across runs and CI can fail on a diff.
 *
 * **Derived, not duplicated.** Every value is read out of
 * `config/branding-profile.json`, which is already the single authority the
 * `thiqa-branding:apply-profile` command writes into `globals` once the database exists.
 * The artefact is therefore the same identity the running product will hold, made
 * readable one bootstrap phase earlier -- not a second copy that can disagree with it.
 */
final readonly class ProductIdentityGenerator
{
    /** Repo-relative source, and the label used in error messages and the banner. */
    public const PROFILE_PATH =
        'interface/modules/custom_modules/oe-module-thiqa-branding/config/branding-profile.json';

    /** The artefact's file name, both in the preview directory and at its deployed path. */
    public const ARTEFACT_NAME = 'product_identity.generated.php';

    /**
     * @param string $profileAbsolutePath the profile file actually read
     * @param string $profileLabel        the repo-relative label recorded in the banner and in
     *                                    error messages, so neither carries a host path
     */
    public function __construct(
        private string $profileAbsolutePath,
        private string $profileLabel = self::PROFILE_PATH,
    ) {
    }

    /**
     * @return non-empty-list<GeneratedFile>
     */
    public function generate(): array
    {
        $profile = JsonDocument::fromFile($this->profileAbsolutePath, $this->profileLabel);

        $values = [];
        foreach (ProductIdentityKey::emissionOrder() as $key) {
            $values[$key->value] = $this->resolve($profile, $key);
        }

        return [new GeneratedFile(self::ARTEFACT_NAME, $this->render($values))];
    }

    /**
     * Reads one key's value out of the profile and refuses anything the key will not carry.
     */
    private function resolve(JsonDocument $profile, ProductIdentityKey $key): string
    {
        $value = match ($key->sourceKind()) {
            ProductIdentitySourceKind::DocumentMember => $profile->requireString($key->sourceName()),
            ProductIdentitySourceKind::GlobalsRow => $this->globalsRowValue($profile, $key->sourceName()),
        };

        $reason = $key->rejectionReason($value);
        if ($reason !== null) {
            throw new GeneratorException(sprintf(
                'Product identity value for "%s" (from %s in %s) %s',
                $key->value,
                $key->sourceName(),
                $profile->origin(),
                $reason,
            ));
        }

        return $value;
    }

    /**
     * The `value` of the `globals` entry whose `key` matches, searched by scanning rather
     * than by index: the profile's row order is editorial (it tracks the string replacement
     * map's row numbers) and must not become load-bearing here.
     */
    private function globalsRowValue(JsonDocument $profile, string $wantedKey): string
    {
        $count = $profile->requireListCount('globals');
        for ($index = 0; $index < $count; $index++) {
            if ($profile->requireString('globals.' . $index . '.key') === $wantedKey) {
                return $profile->requireString('globals.' . $index . '.value');
            }
        }

        throw new GeneratorException(sprintf(
            'The branding profile %s has no "globals" row for "%s", which the product identity '
                . 'artefact needs. Add the row, or drop the key from ProductIdentityKey.',
            $profile->origin(),
            $wantedKey,
        ));
    }

    /**
     * Emits the artefact.
     *
     * Every value goes through `var_export()`, never string interpolation, so the emitted
     * token is a PHP literal whatever the input was. Combined with
     * {@see ProductIdentityKey::rejectionReason()} refusing quotes and backslashes outright,
     * the literal is byte-identical to the profile value and there is nothing for an
     * escaping bug to get wrong.
     *
     * @param non-empty-array<string, string> $values
     */
    private function render(array $values): string
    {
        $lines = [
            '<?php',
            '',
            '/**',
            ' * Product identity for code that runs before the database exists.',
            ' *',
            ' * @package   OpenEMR',
            ' * @link      https://www.open-emr.org',
            ' * @author    mohammedfouly1 <mselfouly2008@yahoo.com>',
            ' * @copyright Copyright (c) 2026 mohammedfouly1',
            ' * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3',
            ' */',
            '',
            'declare(strict_types=1);',
            '',
        ];

        $banner = GeneratedHeader::php(
            'Pre-database product identity. Read through OpenEMR\\Common\\Branding\\ProductIdentity,'
                . ' never required directly.',
            [$this->profileLabel],
            GeneratedHeader::PRODUCT_IDENTITY_GENERATOR,
        );

        $lines[] = rtrim($banner, "\n");
        $lines[] = '';
        $lines[] = 'return [';
        foreach ($values as $key => $value) {
            $lines[] = sprintf('    %s => %s,', var_export($key, true), var_export($value, true));
        }
        $lines[] = '];';

        return implode("\n", $lines) . "\n";
    }
}
