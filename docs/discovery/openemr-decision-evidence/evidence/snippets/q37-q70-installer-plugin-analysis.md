# Q37 & Q70 — `openemr/oe-module-installer-plugin` install algorithm and in-tree module authority

_Auditor: opencode. Mode: READ-ONLY. No composer install performed. Evidence retrieved from local git objects and from GitHub raw HTTPS (public repo, no auth) per §1.5._

## 0. Provenance

| Item | Value | Source |
|------|-------|--------|
| Plugin package | `openemr/oe-module-installer-plugin` | `composer.lock:6541` |
| Version | `0.1.5` | `composer.lock:6542` |
| Package type | `composer-plugin` | `composer.lock:6560` |
| `extra.class` | `OpenEMR\Composer\ModuleInstallerPlugin\Plugin` | `composer.lock:6562` |
| `autoload.psr-4` | `OpenEMR\Composer\ModuleInstallerPlugin\` → `src/` | `composer.lock:6564-6567` |
| Source URL | `https://github.com/openemr/oe-module-installer-plugin.git` | `composer.lock:6545` (no credentials — public) |
| Source ref (SHA) | `4803eb0e6ad07328bd0eac485d0b2ff9e075f9f6` | `composer.lock:6546` |
| Dist URL | `https://api.github.com/repos/openemr/oe-module-installer-plugin/zipball/4803eb0e6ad07328bd0eac485d0b2ff9e075f9f6` | `composer.lock:6550` |
| Dist shasum | (empty) | `composer.lock:6552` |
| Allow-listed in fork | `true` | `composer.json:205` |
| Committed date (upstream) | `2021-05-03T14:22:43+00:00` | `composer.lock:6589` |
| Local retrieval of source | Full — see `evidence/snippets/oe-module-installer-plugin-source.php` | Option A (SAFE) executed successfully |

`vendor/` is empty on this host (baseline §2), so the plugin was not extracted locally. The three source files (`Plugin.php`, `CustomModuleInstaller.php`, `ZendModuleInstaller.php`) were fetched read-only from `raw.githubusercontent.com` pinned to the exact `source.reference` SHA in `composer.lock`, guaranteeing byte-identity with what a `composer install` on this host would resolve.

---

## 1. Plugin class + algorithm

The composer plugin entry point is `OpenEMR\Composer\ModuleInstallerPlugin\Plugin` (`extra.class`, `composer.lock:6562`). Its only meaningful method is `activate()`, which registers TWO installers with the Composer InstallationManager:

**`src/Plugin.php:9-17`** (blob `0c8ea6bab24f2a5361456392d68cb975106bd9bd`):

```php
public function activate(Composer $composer, IOInterface $io)
{
    $installer = new CustomModuleInstaller($io, $composer);
    $composer->getInstallationManager()->addInstaller($installer);

    $installer = new ZendModuleInstaller($io, $composer);
    $composer->getInstallationManager()->addInstaller($installer);
}
```

Both installers extend `Composer\Installer\LibraryInstaller` and override exactly two methods: `getInstallPath(PackageInterface $package)` and `supports($packageType)`. They do **NOT** override `install()`, `update()`, `uninstall()`, `installCode()`, `getPackageBasePath()`, or any prompt / confirmation hook. All actual file-copy behaviour is inherited unchanged from `LibraryInstaller`.

Because they extend `LibraryInstaller`, on install/update Composer:
1. Calls `getInstallPath()` to compute the target directory.
2. Removes any existing directory at that path via `$this->filesystem->removeDirectory($downloadPath)` (standard `LibraryInstaller::installCode` behaviour).
3. Downloads the dist zip and extracts it in place.

There is no symlink mode, no dry-run guard, no "skip if directory exists" check, and no interactive confirmation.

---

## 2. Install path algorithm

### `CustomModuleInstaller` — handles `type: openemr-module`

**`src/CustomModuleInstaller.php:13-20`** (blob `84e45799179f23d132b9b98f58f4b0ac52ccba7c`):

```php
public function getInstallPath(PackageInterface $package)
{
    $packageName = $package->getPrettyName();
    $folderPaths = explode('/', $packageName);
    $moduleName = end($folderPaths);
    return 'interface' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'custom_modules'
        . DIRECTORY_SEPARATOR . $moduleName;
}

public function supports($packageType)
{
    return 'openemr-module' === $packageType;
}
```

Algorithm in one sentence: **the install path is `interface/modules/custom_modules/{basename(package.name)}`** — the vendor prefix (e.g. `claimrevolution/`, `openemr/`) is **stripped**. Path is relative to composer's working directory (project root). No sanitisation is performed on the basename; whatever comes after the last `/` in the pretty name is used verbatim.

Concrete resolutions for the packages present:
- `claimrevolution/oe-module-claimrev-connect` → `interface/modules/custom_modules/oe-module-claimrev-connect`
- (hypothetical) `openemr/oe-module-cqm` → `interface/modules/custom_modules/oe-module-cqm`
- (hypothetical) `openemr/oe-module-weno` → `interface/modules/custom_modules/oe-module-weno`

### `ZendModuleInstaller` — handles `type: openemr-zend-module`

**`src/ZendModuleInstaller.php:13-20`** (blob `af16a92069c5f85e0fc7016249697cc1a486f056`):

Returns `interface/modules/zend/modules/{basename}`. Note this is `zend/modules/`, **NOT** the fork's `interface/modules/zend_modules/` (underscore, singular level). No composer package in `composer.lock` currently declares `type: openemr-zend-module` (verified: `Select-String composer.lock '"type": "openemr-zend-module"'` → zero matches), so this installer is dormant in the current fork.

---

## 3. Overlay risk analysis

**Cross-referencing the 7 git-tracked custom_modules directories with composer.lock:**

None of the seven tracked module basenames appears as a package `name` in `composer.lock`:

| Tracked basename | Root composer.lock package? | Result |
|------------------|----------------------------|--------|
| `oe-module-comlink-telehealth` | absent | git-tracked-only, no overlay |
| `oe-module-dashboard-context` | absent (its internal `composer.json` declares `openemr/oe-module-dashboard-context` but the root project does NOT require it) | git-tracked-only, no overlay |
| `oe-module-dorn` | absent | git-tracked-only, no overlay |
| `oe-module-ehi-exporter` | absent (same story — internal `composer.json` blob `30aa0397` declares the package but root does not require it) | git-tracked-only, no overlay |
| `oe-module-faxsms` | absent | git-tracked-only, no overlay |
| `oe-module-prior-authorizations` | absent | git-tracked-only, no overlay |
| `oe-module-weno` | absent (internal `composer.json` blob `ff088f0d` even declares `conflict: openemr/openemr >=7.0.1`, which is a hint it was historically composer-installed on older releases and has since been vendored in-tree) | git-tracked-only, no overlay |

**And the one composer package that WOULD be installed:**

| Composer package | Basename | Currently tracked at `interface/modules/custom_modules/oe-module-claimrev-connect`? |
|------------------|----------|-------------------------------------------------------------------------------------|
| `claimrevolution/oe-module-claimrev-connect` v2.1.6 | `oe-module-claimrev-connect` | **NO** — verified via `git ls-tree HEAD interface/modules/custom_modules/oe-module-claimrev-connect` returning empty. Composer install would CREATE this directory. |

**Conclusion — Overlay risk is ZERO in the current fork state.** No path is simultaneously git-tracked and composer-authoritative. However, this is a fragile invariant:

- If any of the seven in-tree modules were ever re-added to root `composer.json`'s `require` (or transitively pulled in), Composer would call `getInstallPath()` → same path → `LibraryInstaller::installCode()` → **`removeDirectory()`** of the tracked directory → replace with the dist zip contents. Silent overwrite. Git would then show the entire module as modified/deleted files.
- The three modules with their own internal `composer.json` (`dashboard-context`, `ehi-exporter`, `weno`) are one require-line away from this fate.

---

## 4. Q37 — direct answer

**Q37: How does the OpenEMR module installer plugin decide where to write a module during `composer install`?**

The plugin (`openemr/oe-module-installer-plugin` v0.1.5, pinned at commit `4803eb0e`) registers two `LibraryInstaller` subclasses at composer plugin `activate()` time (`src/Plugin.php:9-17`). For any package declaring `type: openemr-module`, `CustomModuleInstaller::getInstallPath()` (`src/CustomModuleInstaller.php:13-20`) returns

```
interface/modules/custom_modules/{basename-of-package-name-after-last-slash}
```

For `type: openemr-zend-module`, `ZendModuleInstaller::getInstallPath()` (`src/ZendModuleInstaller.php:13-20`) returns

```
interface/modules/zend/modules/{basename}
```

There is no manifest lookup, no `extra.installer-name` support, no `extra.openemr.directory` support (the field seen in the fork's `oe-module-dashboard-context/composer.json` `extra.openemr.directory` is **not read by this plugin version** — verified by absence in the plugin source). The algorithm is purely `end(explode('/', $package->getPrettyName()))`.

File-write semantics are inherited unchanged from `Composer\Installer\LibraryInstaller`: destructive replace via `Filesystem::removeDirectory()` followed by zip extraction from the dist URL.

---

## 5. Q70 — direct answer

**Q70: For each in-tree module under `interface/modules/custom_modules/`, is git or composer authoritative in production?**

Enumerated modules (see `08-dependency-runtime-inventory.csv` for the full row-level detail):

| # | Module | Authority | Justification |
|---|--------|-----------|---------------|
| 1 | `oe-module-comlink-telehealth` | **git-tracked** | 100 tracked files; not in composer.lock |
| 2 | `oe-module-dashboard-context` | **git-tracked** | 23 tracked files (incl. own `composer.json` blob `a4147394`); root composer.lock does NOT require it |
| 3 | `oe-module-dorn` | **git-tracked** | 38 tracked files; not in composer.lock |
| 4 | `oe-module-ehi-exporter` | **git-tracked** | 35 tracked files (incl. own `composer.json` blob `30aa0397`); not in root composer.lock |
| 5 | `oe-module-faxsms` | **git-tracked** | 74 tracked files; not in composer.lock |
| 6 | `oe-module-prior-authorizations` | **git-tracked** | 15 tracked files; not in composer.lock |
| 7 | `oe-module-weno` | **git-tracked** | 42 tracked files (incl. own `composer.json` blob `ff088f0d`); not in root composer.lock |
| — | `oe-module-claimrev-connect` | **composer-installed-only** | Required at `composer.json:52` (`^2.1`), resolved to `v2.1.6` at `composer.lock:426-473`; directory `interface/modules/custom_modules/oe-module-claimrev-connect` is **absent** from `git ls-tree HEAD` |

**Recommendation for production source-of-truth:**

- For all seven in-tree `oe-module-*` directories: **git is authoritative**. Deploy them by checking out the ref, not by `composer install`. Any move to re-composer-manage them would require adding `require` entries, at which point overlay risk becomes real.
- For `oe-module-claimrev-connect`: **composer is authoritative** and there is no in-tree copy to conflict with. Running `composer install --no-dev` in production is the correct provisioning path; the installer plugin will create the module directory.
- The three tracked modules that carry their OWN `composer.json` (`dashboard-context`, `ehi-exporter`, `weno`) are a latent trap: any developer who runs `composer require openemr/oe-module-weno` (or ehi-exporter, or dashboard-context) in the root project would trigger the destructive overlay described in §3. Consider either (a) removing the internal `composer.json` files from the tracked tree, or (b) making the root project explicitly require them from a path repo pinned to the tracked commit, to close the ambiguity.

---

## 6. Byte-safety note

Per `02-repository-baseline.md:95-98`, the fork HEAD (`631f2b38…`) is an ancestor of `upstream/master` (`608f9ae3…`). This means every tracked file blob at fork HEAD is byte-identical to the same path at upstream (assuming no intervening rename). The 7 in-tree `oe-module-*` directories are therefore upstream-vendored code, not fork-local modifications — cross-references §5 / Q36 module-byte-identity investigation (parallel work, results in that report; not re-verified here to avoid duplication).

---

## 7. What is required to close remaining uncertainty

| Uncertainty | Status | Closure requirement |
|-------------|--------|---------------------|
| Whether newer plugin versions (>0.1.5) honour `extra.openemr.directory` from module `composer.json` | Not required for THIS fork — pinned to 0.1.5 | Only relevant if the fork upgrades the plugin. Re-fetch source at the new tag. |
| Whether Composer's actual `LibraryInstaller::installCode()` behaves as described (destructive `removeDirectory`) | HIGH confidence (well-documented Composer behaviour) — not re-verified from Composer source | Fetch `composer/composer` source at v2.x and inspect `src/Composer/Installer/LibraryInstaller.php::installCode()` if a formal proof is needed. |
| What actually happens on `composer install` in this fork | UNVERIFIED at runtime (no composer install performed, per §1.2) | **External-decision-required.** A sandbox reproduction would be: `git clone` this fork into a scratch dir NOT bind-mounted to any workspace, run `composer install --no-scripts --dry-run`, capture output. Would confirm (a) the 7 in-tree modules are untouched, (b) `oe-module-claimrev-connect` is scheduled for install into the expected path. **Not executed here; requires operator approval per §1.2.** |
| Whether `oe-module-cqm` VCS repo declaration at `composer.json:166-169` is a live indirect dependency | Resolved — grep of composer.lock shows zero references to `oe-module-cqm` as a package name → declared-but-unused | None. |
| Whether `wkhtmltopdf-openemr` VCS repo at `composer.json:161-165` installs to a module path | Resolved — GitHub raw `composer.json` has no `type` field → defaults to `library` → would install to `vendor/`, not `interface/modules/`; and it is not in composer.lock anyway | None. |

---

## 8. Cited artefacts

- `composer.json` — allow-list at line 205; VCS repos at 161-169; claimrev require at line 52.
- `composer.lock` — claimrev-connect block 426-473; installer-plugin block 6541-6590.
- `evidence/snippets/oe-module-installer-plugin-source.php` — full retrieved source of all three plugin files, with blob SHAs recorded inline.
- `08-dependency-runtime-inventory.csv` — 11-row inventory (7 git-tracked custom modules, claimrev composer-only, cqm declared-unused, wkhtmltopdf declared-unused, installer-plugin itself).
- `22-command-log.txt` — appended log of every command and HTTPS GET used to build this report.
