# EV-090 — Twig-render session-lock hang: code-level root cause

**Author:** Orchestrator (main session), 2026-08-19, desk/code investigation only — no live
system changes, no browser or DB actions taken. Follow-up to PB-440's live reproduction
(eighth browser-check agent), which found the hang documented in `CLAUDE.local.md` §9 as
"PHPUnit-only" also reproduces in live Apache, and wedges the whole browser-tab session, not
just one request.

## Mechanism, traced through the actual code

1. `interface/main/about_page.php` (reached via the app's own "About Thiqa" menu item) renders
   through Twig. `TwigExtension::getGlobals()` (`src/Common/Twig/TwigExtension.php:73-92`) calls
   `SessionWrapperFactory::getInstance()->getActiveSession()->all()` on every such render.

2. The app's own JS fires a `POST apis/default/api/background_service/$run` call automatically
   on every page navigation (`src/RestControllers/BackgroundServiceRestController.php:181-185`,
   `runAllDueIsolated()`). When one or more services are due, each is spawned as a **separate PHP
   CLI subprocess** via Symfony `Process` (`src/Services/Background/SymfonyBackgroundServiceSpawner.php:195`,
   `$process->run($callback)`), which **blocks synchronously**. Timeout floor is
   `MIN_LEASE_MINUTES = 60` minutes per service (`BackgroundServiceRunner.php:45`), and due
   services are processed **sequentially**, not in parallel
   (`BackgroundServiceRunner::runAllDueIsolatedUnlocked()`, lines 179-199).

3. The main app session (`SessionConfigurationBuilder::forCore()`,
   `src/Common/Session/SessionConfigurationBuilder.php:15-23`) defaults to `read_and_close =
   true` — briefly acquiring and releasing the session file lock per read. But
   `HttpSessionFactory`'s `readOnly` flag (`src/Common/Http/HttpSessionFactory.php:35`) defaults
   to `false`, and it is the *caller* that decides which value to pass for a given route. If the
   `$run` endpoint's request is authenticated via the browser's own `OpenEMR` session cookie
   (confirmed as the live cookie name — see the Ubuntu server's own `Set-Cookie: ... OpenEMR=...`
   header captured during this session's separate infra audit) rather than OAuth, and opens that
   session in normal (non-read-and-close) locking mode, the request holds an **exclusive file
   lock on the session for its entire blocking duration** — up to the 60+ minute subprocess
   timeout, sequentially per due service.

4. Any other request on the same session (e.g. the next page navigation, which needs to
   `session_start()` to render) then blocks waiting for that same file lock. This is exactly
   PB-440's observed symptom: the next navigation in the same tab hangs too, `httpd.exe` CPU
   stays near-flat (blocked, not busy — a lock wait, not a runaway loop), and only killing the
   stuck worker (Apache restart) releases the lock. Login/cookie state survives the restart,
   consistent with PB-423's session-persistence fix.

## Why this manifests on this host specifically

Booting a fresh PHP CLI subprocess (`bin/console background:services run --name=<X> --json`)
means re-bootstrapping the entire Symfony/OpenEMR autoloader and DI container from disk. On this
machine's Google-Drive-mounted `G:` filesystem, that is exactly the I/O pattern `CLAUDE.local.md`
§6/§8 already documents as pathological — thousands of small file stats/reads at "~28 KB/s
effective throughput, ~92% of I/O being Drive filesystem metadata round-trips" (`composer
install` alone measured at 48.5 minutes there). A subprocess boot that would take well under a
second on normal local disk plausibly takes long enough here to look indistinguishable from a
hang, especially compounded across multiple simultaneously-due services processed sequentially.

**Assessment: substantially a Drive-mount artifact of this specific dev host, not necessarily a
universal OpenEMR defect** — but the underlying architecture (a long, synchronous,
possibly-lock-holding subprocess spawn triggered by ordinary page navigation) is real on any
host, and the trigger condition — multiple services simultaneously "due" — is exactly what
**RDY-0083** (no automated cron/systemd background-service scheduler, confirmed absent on both
this local machine and the Ubuntu `demo-openemr` production host during this session's separate
infra audit) allows to happen: without a scheduler keeping `next_run` current in the background,
services accumulate backlog between sessions, and the first page load of a new session pays for
all of them at once, synchronously, on the critical path of a user-facing request.

## Recommendation

Implementing RDY-0083's scheduler (a real cron/systemd timer running `bin/console
background:services run --json` on a short interval, on whichever host — this local machine has
no viable path per PB-181's SYSTEM/Drive-mount finding, but the Ubuntu production host does) would
prevent the backlog-buildup precondition on that host, making this hang far less likely to ever
trigger there, independent of whether the Drive-mount-specific slowness is also a factor. This is
not a fix for RDY-0090 by itself (the reachable-leg visual check still needs to happen) but it
removes the likely trigger condition for the hang blocking it.

**Not decided or executed here:** whether to implement the Ubuntu-side scheduler is an
infrastructure change against the production demo host and needs its own explicit approval,
tracked separately from this write-up. No code, config, or live system was touched in producing
this analysis — read-only source inspection only.
