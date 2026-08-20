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
   - assigned model and tier;
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
