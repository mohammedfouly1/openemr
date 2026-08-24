# Deterministic branding CI gates

Run the same required checks locally and in CI with:

```sh
composer branding-ci
```

The `Isolated Tests` workflow runs this command on its PHP 8.2 matrix leg before the full isolated suite. It
needs only the checked-out repository and Composer dependencies; it does not use secrets, a database, Apache,
a browser, or network access at test time.

The command enforces three boundaries in sequence:

1. `composer branding-tokens-check` runs the token generator in `--check` mode. It returns 3 for generated or
   deployed artifact drift and does not regenerate files. Regenerate deliberately and review the resulting diff;
   never accept a manual edit as the generated source of truth.
2. `php tools/branding/verify-brand-manifest.php` verifies every entry in
   `brand/manifests/SHA256SUMS`. A mismatch or missing target returns nonzero. Manifest-covered changes require
   the approved hash re-issue procedure; entries must not be deleted to make verification pass.
3. A targeted PHPUnit suite exercises the four PHPStan branding rules, their registration, their documented
   identifiers, the mandatory core-string contract, and this CI wiring. It uses
   `--fail-on-empty-test-suite`, `--fail-on-incomplete`, and `--fail-on-risky`, so a stale path or zero-test
   selection cannot be green.

The guardrail RuleTestCase suites run PHPStan's analyzer against both allowed and deliberately violating fixtures.
Analyzer exceptions are PHPUnit errors and therefore fail the command. The separate repository `PHPStan`
workflow continues to run complete codebase analysis by invoking `vendor/bin/phpstan` directly; no pipe or
wrapper masks its exit code, so analysis errors, internal errors, termination, and incomplete results remain
failures.

Do not pipe these commands through `tail`, `tee`, or another formatter unless the shell is explicitly configured
to preserve the first failing exit status. CI intentionally invokes the canonical Composer command directly.
