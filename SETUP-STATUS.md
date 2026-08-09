# OpenEMR Local Setup — Native Windows (no Docker)

_Last updated: 2026-08-07. Supersedes the earlier Docker-based plan._

**Status: WORKING.** App runs at <http://localhost:8300/>, login `admin` / `pass`.

---

## Why not Docker

This machine is a Google Compute Engine VM **without nested virtualization**:

```
Manufacturer:                  Google Compute Engine
VirtualizationFirmwareEnabled: False
VMMonitorModeExtensions:       False
```

WSL2 and the Docker Desktop engine VM cannot boot without it. No reboot or
DISM flag fixes this — it requires recreating the instance with the
nested-virtualization licence on a machine type that supports it. The old
`fix-docker-virtualization.ps1` cannot work and is obsolete.

So OpenEMR runs natively instead. It is a plain PHP + MySQL app; Docker was
only ever packaging convenience.

## The stack

Everything is a no-install archive under `C:\openemr-stack` — **no admin was
required** (the VC++ 2015-2022 x64 runtime was already present). The app
itself stays in place on `G:\My Drive\OpenEMR`.

| Component | Version | Notes |
|-----------|---------|-------|
| Apache | 2.4.57 win64 **VS16** | Apache Lounge. VS16 chosen to match PHP so `mod_php` is ABI-compatible — current Lounge builds are VS18 and would not be. |
| PHP | 8.3.33 **TS VS16** x64 | Thread-safe build, required by `mod_php`. |
| MariaDB | 11.8.8 winx64 | Same version as the Docker compose stack. Loopback only. |
| Composer | latest stable | `C:\openemr-stack\composer.phar` |

PHP extensions: all 33 required by `composer.json` load, including
**imagick 3.8.1** and **redis 6.3.0** (PECL DLLs, the two not bundled with
the Windows build).

## Daily use

```powershell
C:\openemr-stack\start-openemr.ps1   # start Apache + MariaDB
C:\openemr-stack\stop-openemr.ps1    # clean shutdown (flushes InnoDB)
```

- App: <http://localhost:8300/> — `admin` / `pass`
- DB:  `127.0.0.1:3306`, root has no password, **bound to loopback only**
- App DB user: `openemr` / `openemr`, database `openemr`

### Two things that will bite you

1. **Apache and MariaDB run as console processes in your user session, not as
   Windows services.** This is required: Google Drive mounts `G:` per user
   session, so a LocalSystem service cannot see the app at all. They therefore
   **do not survive a logoff or reboot** — re-run `start-openemr.ps1`.

2. **`C:\openemr-stack\php` must be on `PATH` before `httpd.exe` starts.**
   `mod_php` runs inside `httpd.exe`, so PHP's own folder is not on the DLL
   search path and `openssl`, `curl`, `intl`, `ldap`, `sodium`, `imagick`
   silently fail to load. OpenEMR then dies with *"php openssl module is not
   installed"*. `start-openemr.ps1` handles this; it is also persisted to the
   user `PATH`.

## Front-end assets: built off the Drive mount

`npm ci` **cannot run on `G:`** — it fails with `EBADF` and `EPERM: rmdir`
because the Google Drive filesystem cannot service npm's nested
create/delete churn. It failed there after 26 minutes and left
`node_modules` wrecked.

`node_modules` is a *build-time* dependency only; the runtime needs just
`public/themes` and `public/assets`. So the build runs in a local NTFS
workspace and only the artifacts are copied back:

```powershell
cd C:\openemr-stack\build
npm ci          # ~53 s  (vs 26 min failure on G:)
npm run build   # ~48 s
robocopy C:\openemr-stack\build\public\themes "G:\My Drive\OpenEMR\public\themes" /E
robocopy C:\openemr-stack\build\public\assets "G:\My Drive\OpenEMR\public\assets" /E
```

The workspace mirrors the repo layout (`interface/themes`, `public/assets`,
`webpack/loaders`, `scripts` + the manifests) so webpack's `__dirname`-relative
paths resolve identically. **Re-run this whenever theme SCSS changes.**

## Performance note

Running in place on `G:` was a deliberate choice. Measured cost: ~28 KB/s
effective throughput with ~92% of I/O being Drive filesystem metadata
round-trips. `composer install` took **48.5 minutes** (55,313 files) versus
roughly 90 seconds on local disk, and page loads are correspondingly slow.
OPcache and an 8 MB realpath cache are enabled in `php.ini` to soften this.

Directory junctions are **not** an option — the Drive mount rejects them
(`Local NTFS volumes are required`). The only real fix would be moving the
app to local disk.

## Reinstalling the database

```powershell
$env:OPENEMR_ENABLE_INSTALLER_AUTO='1'
cd "G:\My Drive\OpenEMR"
C:\openemr-stack\php\php.exe -f contrib/util/installScripts/InstallerAuto.php `
    server=127.0.0.1 port=3306 root=root rootpass= `
    login=openemr pass=openemr dbname=openemr `
    iuser=admin iuname=Administrator iuserpass=pass igroup=Default
```

Reset `$config = 0` in `sites/default/sqlconf.php` first to re-run it.
