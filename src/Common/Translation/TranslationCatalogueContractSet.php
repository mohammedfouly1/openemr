<?php

/**
 * Every durable translation contract the repository ships, in a deterministic order.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * Before this class the contracts directory had exactly one file in it, and its path was written
 * out four separate times — in `sql_upgrade.php`, in the migration command, in the release-prep
 * mutator, and in the SQL renderer's header comment. Adding a second contract would have meant
 * finding all four, and missing one would have meant a contract that generates into the installer
 * supplement but never runs on upgrade, or the reverse. Both fail silently: the tenant simply
 * renders an untranslated key.
 *
 * So the directory is the unit, not the file. Every `*.json` in it is a contract, they are loaded
 * in sorted filename order so the generated supplement is byte-stable across machines, and each
 * one keeps its own journal row (the journal is keyed on `contract->id`), so contracts apply,
 * roll back and re-apply independently of each other.
 *
 * Ordering matters for one real reason beyond reproducibility: the supplement is a single SQL file
 * executed top to bottom, so two contracts deriving from each other would be order-dependent.
 * `assertNoDerivationChain()` refuses that outright rather than relying on filenames to sort it
 * out.
 */
final readonly class TranslationCatalogueContractSet
{
    public const RELATIVE_DIRECTORY = 'contrib/util/language_translations/contracts';

    /**
     * @param list<TranslationCatalogueContract> $contracts
     */
    private function __construct(public array $contracts)
    {
    }

    /**
     * Load every contract under the given project root.
     */
    public static function fromProjectDirectory(string $projectDirectory): self
    {
        return self::fromDirectory(rtrim($projectDirectory, '/\\') . '/' . self::RELATIVE_DIRECTORY);
    }

    public static function fromDirectory(string $directory): self
    {
        if (!is_dir($directory)) {
            throw new \RuntimeException('Translation contract directory not found: ' . $directory);
        }

        $paths = glob(rtrim($directory, '/\\') . '/*.json');
        if ($paths === false) {
            throw new \RuntimeException('Cannot list translation contract directory: ' . $directory);
        }

        // Sorted by path so the generated supplement is byte-identical regardless of the
        // filesystem's own directory ordering.
        sort($paths);

        $contracts = [];
        $seenIds = [];
        $seenTargets = [];
        foreach ($paths as $path) {
            $contract = TranslationCatalogueContract::fromFile($path);

            if (isset($seenIds[$contract->id])) {
                throw new \RuntimeException('Duplicate translation contract id: ' . $contract->id);
            }
            if (isset($seenTargets[$contract->targetKey])) {
                throw new \RuntimeException('Two translation contracts target the same key: ' . $contract->targetKey);
            }

            $seenIds[$contract->id] = true;
            $seenTargets[$contract->targetKey] = true;
            $contracts[] = $contract;
        }

        if ($contracts === []) {
            throw new \RuntimeException('No translation contracts found in ' . $directory);
        }

        $set = new self($contracts);
        $set->assertNoDerivationChain();

        return $set;
    }

    /**
     * @return list<TranslationCatalogueContract>
     */
    public function all(): array
    {
        return $this->contracts;
    }

    public function count(): int
    {
        return count($this->contracts);
    }

    /**
     * A contract may not derive from a key another contract creates.
     *
     * Allowing it would make the result depend on the order the contracts happened to run in, and
     * the two execution paths do not share an order guarantee: the installer supplement is one SQL
     * file executed top to bottom, while the upgrade migration runs each contract in its own
     * transaction. A chain would produce different catalogues on install and on upgrade, which is
     * precisely the fresh-install-versus-upgrade divergence S2-P0-21 was raised about.
     */
    private function assertNoDerivationChain(): void
    {
        $targets = [];
        foreach ($this->contracts as $contract) {
            $targets[$contract->targetKey] = true;
        }

        foreach ($this->contracts as $contract) {
            $derivation = $contract->derivation;
            if ($derivation instanceof TranslationDerivation && isset($targets[$derivation->sourceKey])) {
                throw new \RuntimeException(
                    'Translation contract ' . $contract->id
                    . ' derives from a key another contract creates: ' . $derivation->sourceKey,
                );
            }
        }
    }
}
