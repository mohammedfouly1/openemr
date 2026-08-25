# ADR-BRAND-005: A generated, brand-neutral pre-database product identity artefact

**Status:** Accepted (`feat/thiqa-branding-foundation`). Supersedes nothing; closes finding **S3-P1-33**.

## Context

`setup.php` is a mixed-brand installer. Scan-3 found **10** hardcoded product literals at
`:145`, `:160`, `:356`, `:452`, `:522`, `:524`, `:526`, `:976`, `:1530`, `:1747`, alongside **32**
surviving `OpenEMR` occurrences, so the same page tells an installing operator both
`Thiqa Setup Tool` and `Congratulations! OpenEMR is now installed.`

The reason this is not a mechanical find-and-replace is the whole of the problem. `setup.php` runs
**before the database exists**. Every mechanism this programme built for product identity — `xl()`,
`xlp()`, `xl_product_name()`, `saas_branding_*` globals, the `OEGlobalsBag` — resolves through the
database or through a populated globals bag, and none of them is available there. The same is true of
the two `openssl` guards near the top of `interface/globals.php`, which run before the bag is populated
and end in `exit(1)`, and of the `Branding` group defaults in `library/globals.inc.php`, which
`Installer::insert_globals()` (`library/classes/Installer.class.php:827`) requires **during
installation** and writes straight into the `globals` table.

Making the installer merely *consistent* by hardcoding the tenant's name into ~30 more places is the
wrong repair for a programme whose entire purpose is to make the next rename a single edit. It would
also multiply the surface that S2-P1-26 and A-01/A-03 exist to keep re-derivable.

Constraint **C1** (no free-text tenant input may become executable output) and the security lesson of
S3-P0-28 (a failure that aborts an installer or upgrade mid-run is categorically worse than a cosmetic
one) both bear directly on whatever mechanism is chosen.

### Inventory, classified by bootstrap phase

Re-derived on disk 2026-08-25, not inherited:

| Class | Meaning | Disposition |
|---|---|---|
| **(a) Pre-database product identity** | User-facing installer copy naming the running product | **Convert** to read the artefact |
| **(b) Database available** | Reached only after the schema exists | Leave to `xl()` / `xlp()` / `xl_product_name()` |
| **(c) Preserve** | Namespaces, docblocks, and factual references to the upstream OpenEMR project, Foundation, community or ONC certification | **Never rebrand** (locked constraint C7) |

Class (c) in `setup.php` includes `use OpenEMR\…` imports and the `OpenEMR\Common\Compatibility\Checker`
call, the `@package OpenEMR` docblock, and the genuinely factual project references at `:513-515` — the
project home page at `open-emr.org`, and the grant-funding paragraph, which is about the OpenEMR project
and not about this product. These stay exactly as they are. Class (a) is the ~17 user-facing strings
that name the running product, plus the 10 already-hardcoded literals.

## Decision

Introduce a **brand-neutral, deterministically generated, PHP-readable identity artefact**, and read it
through a single accessor.

```text
config/branding-profile.json          the one authority
        │
        │  tools/branding/bin/generate-product-identity.php     (deterministic, offline)
        ▼
library/product_identity.generated.php        `return [...]`   — never required directly
        │
        │  OpenEMR\Common\Branding\ProductIdentity              — the only reader
        ▼
setup.php · interface/globals.php · library/globals.inc.php
```

### An immutable returned array, not `define()`d constants

Both were weighed. Constants were rejected on three counts: they are process-global and cannot be
re-resolved for a second site in the same process, a redefinition warning is emitted where the file is
reached twice, and a constant name is itself a global identifier that could collide with an upstream
addition. A `return [...]` file is a value the caller owns, is re-readable, and composes with the
memoisation in `ProductIdentity`.

### The artefact can never become a code-injection vector

The generator does not template PHP. Every value is emitted through `var_export()` after schema
validation, so profile content cannot escape its string literal — the property is guaranteed by the
serializer, not by the escaping discipline of whoever edits the generator next. A profile that fails
validation **fails the build and writes nothing**, rather than emitting a partial artefact. The output
is byte-identical for identical input, which is what makes `--check` a meaningful drift gate.

At the point of *use*, `setup.php` and `interface/globals.php` emit HTML, so every read is escaped with
`text()` at the call site. `text()` is a composer `autoload.files` entry, so it is available from
`vendor/autoload.php` onward — verified by execution in a cold CLI process, because a helper that were
*not* loaded there would turn the openssl error path into a fatal instead of a message.

### Failure behaviour: degrade, never abort

A missing, unreadable, malformed or schema-invalid artefact resolves to a compiled-in neutral fallback
and logs the reason; it does not throw. This is the deliberate inverse of a normal parse-don't-validate
posture, and S3-P0-28 is why. Every consumer is an installer, an upgrade path, or a fatal-error reporter
— the three places where raising is *worse* than being slightly wrong. An installer that dies because a
branding artefact is absent is a far more serious defect than one that renders a neutral name.

**One failure mode is genuinely not guardable, and is recorded rather than papered over.** A *syntax
error* inside the artefact is a compile-time fatal at `require`, and PHP offers no way for a caller to
guard against that — no `try`, no error handler, no `include` variant. Every failure a `return`ed value
*can* have is checked (absent file, unreadable file, non-array return, missing key, non-string value,
empty string), but a corrupt-to-unparseable artefact will still take the process down. The mitigations
are that nothing hand-edits the file, `composer php-syntax-check` lints it with every other PHP file,
and `--check` fails CI the moment its bytes stop matching the generator's output. This is a real
residual risk, not a closed one.

### Drift detection

`--check` re-generates in memory and exits **3** when the artefact on disk differs. This is wired into
the canonical `composer branding-ci` gate, so an artefact hand-edited in place cannot survive CI, and a
profile change that was not regenerated cannot merge.

## Consequences

- The next rename edits **one JSON file** and re-runs one generator. That is the property this ADR
  exists to buy, and the reason hardcoding was rejected.
- `library/product_identity.generated.php` is **committed**, not built at install time. It must be
  readable on a bare checkout before `composer install`, so the generator itself takes no Composer
  dependency — it is plain PHP with no Symfony Console.
- A new obligation on maintainers: changing `branding-profile.json` requires re-running the generator in
  the same change. CI enforces it rather than trusting it.
- **Payload during PRE remains the current pre-SkyEagle identity.** The artefact carries whatever the
  profile carries. Note that `product_domain` in the committed profile has been `skyeagle.uk` since
  `b3b821ffa`, well before this ADR; the generator reflects existing configuration and introduces no
  new identity. Nothing here is a Category-B change.
- Class (c) references are now protected by construction as well as by policy: they are literals that no
  generator feeds, so a future rename cannot reach them by accident.

## References

- Finding **S3-P1-33**, `docs/PRE-SKYEAGLE-CONTINUATION-CHECKPOINT.md` §15C, §15D
- Owner decision **SKY-Q11** (architecture) and **SKY-Q12** (compatibility preservation)
- **S3-P0-28** — the install-versus-upgrade divergence that motivates degrade-never-abort
- `ADR-BRAND-001` (five-plane architecture), `ADR-BRAND-003` (closed token allowlist) — the same
  "one authority, generated downstream, verified in CI" shape
- Locked constraint **C7** / BRAND-063/118 — upstream notices are PRESERVE
