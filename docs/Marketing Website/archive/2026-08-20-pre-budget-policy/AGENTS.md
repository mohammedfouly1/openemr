# OpenEMR project agent instructions

## Mandatory committee trigger

Treat a request as an explicit committee invocation when either condition is true:

- the request begins with `Committee:`, `Comittee:`, or `committe:` (case-insensitive); or
- the user explicitly invokes `$comittee`.

On a committee invocation, load and follow the project skill at
`.agents/skills/comittee/SKILL.md` before substantive analysis, shell/database queries, edits, or
recommendations. This rule is strict and applies even when the requested answer appears obvious.

A valid committee invocation must:

1. Dispatch M0 first and create a matching `COM-YYYYMMDD-NNN` record in
   `docs/Marketing Website/committee/M0-dispatch-log.md` and the initial M0 decision pack.
2. Have M0 classify the task and register every M0–M7 role as `FIRE` or `IDLE` with a reason.
3. Run and record preflight before dispatching the selected agents, including assigned and observed
   models, source access, output paths, and each skill requested, available, audited, and actually used.
4. Actually dispatch every agent marked `FIRE`. Direct work by the orchestrator, shell commands,
   database queries, and edited governance files are evidence or orchestration, not agent dispatches.
5. Produce one registered output per firing agent, the required adversarial and M5 reviews, and an
   M0 final pack. Keep Owner decisions separate from agent positions.
6. Verify that the log, agent outputs, and final pack carry the same dispatch ID before reporting
   completion.

If M0 or any mandatory selected agent cannot be dispatched, stop the committee workflow and record
`HELD` or `FAILED` with the reason. Never silently replace a missing agent with orchestrator work,
never call ordinary analysis a committee result, and never report `COMMITTEE COMPLETE` without the
required proof files.

The default is `FULL ROUND`. Use single-agent mode only when the Owner explicitly requests it and
the task does not feed a decision or customer-facing output and does not touch a locked decision,
demo access, security, privacy, reset/re-base, or architecture approval.

The active committee authority is
`docs/Marketing Website/01-governance/CommitteeSystem.md`. Historical files under
`docs/Marketing Website/archive/` are evidence only and must not be used as operating instructions.

