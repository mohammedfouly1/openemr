# Committee output directory

One file per agent. **Agents write here and nowhere else** — see
[`../01-governance/CommitteeSystem.md`](../01-governance/CommitteeSystem.md) R9 and §6.4 (write isolation).

| File | Owner |
|---|---|
| `M0-decision-pack-<task>.md` | M0 Committee Head (Fable 5) |
| `M1-market-intelligence.md` | M1 Market & Competitor Intelligence |
| `M2-strategy-and-ideas.md` | M2 Strategy & Ideas |
| `M3-messaging-and-content.md` | M3 Messaging & Content |
| `M4-architecture-and-conversion.md` | M4 Website Architecture & Conversion |
| `M5-compliance-review-<task>.md` | M5 Claim Compliance & Evidence |
| `M0-dispatch-log.md` | M0 — single-agent dispatch log (§6.2) |
| `M6-architecture-audit.md` | M6 Technical Architecture Auditor |
| `M7-arabic-localisation.md` | M7 Arabic & Localisation |

No agent edits a shared or governed document, and no agent runs git. The Orchestrator commits.

Empty until the first dispatch.

## Mandatory proof when `Committee` or `Comittee` is invoked

A valid invocation produces all of the following with one matching `COM-YYYYMMDD-NNN` ID:

1. An entry in `M0-dispatch-log.md`.
2. One output file for every agent marked `FIRE`.
3. A terminal M5 review when the work feeds a decision or customer-facing artefact.
4. `M0-decision-pack-<task>.md` with all twelve sections and a final status.

The log records assigned and observed models, skills requested/available/audited/used, times,
failures, output paths and each agent's position. If these artefacts do not exist, the committee was
not dispatched regardless of what analysis appears in chat or shell history.
