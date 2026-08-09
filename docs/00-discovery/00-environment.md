# Phase 0 — Environment Report

_Read-only audit. No packages installed, no repo mutations performed._

## Detected Host Environment

| Item | Value | Evidence |
|------|-------|----------|
| OS | Windows 10 (10.0.19045.0 / build 19041) | `[System.Environment]::OSVersion` |
| Shell used for audit | Windows PowerShell 5.1 (`5.1.19041.7548`) | `$PSVersionTable.PSVersion` |
| Working directory | `D:\OpenEmr` | tool env |
| PHP CLI | **NOT installed on host** | `php` unrecognized command |
| Composer | **NOT installed on host** | `composer` unrecognized command |
| Node.js | v24.11.0 | `node --version` |
| npm | 11.12.1 | `npm --version` |
| MySQL/MariaDB client | **NOT installed on host** | `mysql` unrecognized command |
| Docker | Docker version 29.6.1 (build 8900f1d) | `docker --version` |
| Docker Desktop / WSL2 | Present per `SETUP-STATUS.md`; engine status not verified in this audit (audit is read-only, `docker info` not run) | `SETUP-STATUS.md` |

### Interpretation

The host is Windows and has **no PHP toolchain installed locally**. Per project docs (`CLAUDE.md`, `CONTRIBUTING.md`, and the developer-authored `SETUP-STATUS.md` at repo root), OpenEMR development is expected to run entirely inside the `docker/development-easy` stack via `openemr-cmd`. This means:

- All PHP/Composer/PHPUnit/PHPStan/Rector/PHPCS operations must be invoked through the container (`openemr-cmd <alias>` or `docker compose exec openemr /root/devtools <cmd>`), not on the host.
- No host-side `composer install` or `npm install` has been run (confirmed by `vendor/` and `node_modules/` directories being present but empty — see `01-repo-inventory.md`).
- Any static-analysis or test invocations required later in this audit will need Docker Desktop's engine to be actively running. Since this discovery phase is source-code + config only, that has not been required so far.

## Notes / Non-blocking Observations

- `SETUP-STATUS.md` (a local, uncommitted scratch file at the repo root) explicitly states its own disposability. It is NOT part of the fork; it was created by a previous local setup session. See `01-repo-inventory.md` for how it appears in `git status`.
- The `docker/development-easy` stack is the canonical dev environment per `CLAUDE.md`. Production Docker artifacts and CI workflows will be catalogued in Phase 10 (`11-devops-docker-ci.md`).
- No mutating command was executed. All observations come from `git`, PowerShell filesystem enumeration, and reading tracked files.
