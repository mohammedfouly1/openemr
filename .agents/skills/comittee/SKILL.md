---
name: comittee
description: Convene the OpenEMR marketing committee when explicitly invoked with $comittee or when a request begins with Committee, Comittee, or committe; enforce M0-first selection, real agent dispatch, model and skill registration, adversarial review, and a verifiable final decision pack.
---

# Comittee

Convene the project committee as an auditable multi-agent workflow. Do not use this skill merely
because a user discusses committees; use it on explicit invocation under the project `AGENTS.md`.

## Authority

Read `docs/Marketing Website/01-governance/CommitteeSystem.md` completely before acting. It is the
operating authority. Do not operate from the archived charters.

The Owner's task and existing signed rulings are authoritative. Agents produce evidence, positions,
attacks, and verdicts; they do not inherit Owner decision authority.

## Mandatory workflow

1. Preserve the Owner's request verbatim and derive a safe task slug.
2. Inspect `docs/Marketing Website/committee/M0-dispatch-log.md` if it exists and allocate the next
   unused ID in the form `COM-YYYYMMDD-NNN`.
3. **Dispatch M0 first.** M0 creates or appends the log and opens
   `M0-decision-pack-<task>.md` with `STATUS: IN PROGRESS`, the dispatch ID, task, classification,
   proposed firing set, and preflight sections. Wait for this artefact before substantive work.
4. M0 marks every M0–M7 role `FIRE` or `IDLE` with a reason. Use the smallest sufficient firing set,
   but enforce mandatory adversaries from the committee system. `FULL ROUND` is the default.
5. Run preflight. Per firing agent record:
   - selected authorised runtime and available quality/reasoning setting when exposed;
   - runtime/model identifier reported by the platform, or `NOT REPORTED BY RUNTIME`;
   - source readability and frozen-evidence hash;
   - unique output path;
   - skill requested, installed/available, audited, and `USED` or `NOT USED`, including source/version
     when used;
   - D3a result before dispatch.
6. Do not install or fetch a third-party skill during the dispatch. An optional unavailable skill is
   recorded `NOT USED`; a genuinely required unavailable skill causes `HELD`.
7. Dispatch generators independently. Do not expose one generator's answer to another before
   simultaneous publication. Use additional batches when concurrency is limited; preserve independence.
8. Dispatch the required adversarial round only after publication: M5 for claim/evidence review, M6
   for technical/security work, and M4 to attack M6 where required.
9. Record D3b using the runtime/model identifier reported by the platform, or
   `NOT REPORTED BY RUNTIME`. Missing telemetry is valid with disclosure and does not by itself
   quarantine output. Quarantine only an affirmative runtime-policy or independence breach under
   the committee system; never claim unreported identity or verified parity.
10. Require one file per firing agent. Each file carries the dispatch ID and the finding schema from
    §7.1. Record each agent's concise position/verdict and acceptance result in the M0 pack.
11. M0 completes all twelve pack sections, separates convergence from divergence, identifies Owner
    decisions, evaluates the run, obtains the one-pass meta-review, and marks the true final status.
12. Before reporting success, verify the proof set below.

## Scope and budget controls

Default to `STANDARD`. M0 records the Owner's exact questions and an explicit `OUT OF SCOPE` list
before dispatch. Do not expand a narrow request into a complete programme audit merely because the
source contains adjacent issues.

Apply CommitteeSystem.md §6.6 exactly. The principal `STANDARD` limits are hard character caps:

- M0 opening record: 8,000; dispatch log total: 12,000; M0 final pack total: 20,000.
- Ordinary generator output: 20,000; M6 audit: 30,000.
- M4 attack: 12,000; M5 terminal review: 15,000.
- Frozen annex: 30,000; whole dispatch: 100,000.

An agent approaching a cap stops and writes `BUDGET INCREASE REQUEST` with current use, requested
increase, reason, new findings that cannot fit, and what will be omitted if denied. Wait for explicit
Owner approval. No response is denial. Approval must state the added characters and whether they are
for the main file or an annex.

Limits supporting the character budget:

- maximum 10 findings per ordinary agent, 15 for M6, 5 substantive challenges in M4's attack, and
  10 BLOCK/objection rows for M5; overflow is a one-line deferred register;
- maximum five external sources per agent unless the Owner approves expansion;
- write each evidence item once in `COM-<id>-evidence-index.md`; later files cite the evidence ID;
- the frozen annex contains only relevant extracts and hashes, not whole large files;
- M0 final pack references agent finding IDs and does not reproduce their reasoning;
- stop early on a decision-blocking finding and defer dependent analysis;
- all twelve pack sections remain, but `N/A` and short references are valid content.

Before accepting an output and before `FINAL`, run
`scripts/check_dispatch_budget.ps1 -CommitteeDir "docs/Marketing Website/committee" -DispatchId <id>`.
An unapproved overage is `HELD`. Record actual characters, words, approved extension and deferred
findings at the end of every output.

For `COM-20260820-001`, completed M4/M6 files are grandfathered evidence. Any undispatched or
unfinished stage follows compact closure limits: M4 attack 12,000 characters, M5 15,000, and M0 adds
no more than 12,000 closure characters. Do not expand those completed files.

## Proof set

`COMMITTEE COMPLETE` is permitted only when all of these exist and agree on the dispatch ID:

- `committee/M0-dispatch-log.md`;
- `committee/M0-decision-pack-<task>.md` with all twelve sections and `STATUS: FINAL`;
- one output for every role marked `FIRE`;
- M5 terminal review when the work feeds a decision or customer-facing artefact;
- reported runtime identifier or disclosed absence, skill, timing, retry/failure, and output-path
  records for every firing agent.

If the set is incomplete, report exactly one of `NOT DISPATCHED`, `HELD`, or `FAILED`, name the
missing evidence, and do not substitute chat prose, shell history, or orchestrator conclusions.

## User-facing opening

Before substantive work, state:

```text
COMMITTEE TRIGGER ACCEPTED
Dispatch ID: <COM-YYYYMMDD-NNN>
M0: DISPATCHING
No substantive work has started.
```

If an ID cannot yet be allocated, state `M0: HELD` and the reason rather than inventing one.
