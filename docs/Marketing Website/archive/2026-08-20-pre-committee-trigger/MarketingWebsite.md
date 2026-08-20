# THIQA — MARKETING WEBSITE HUB

**Start here.** This file is the short operating map for preparing the website. Detailed material is split by purpose so this file does not grow into another monolith.

## Current decision

`STEP0-001` — **Gated evaluation credential (Reading B)**, ruled 2026-08-20. Access is time-boxed and form-qualified, uses Front Office + Physician, never Administrator, and remains inside the **Book a walkthrough** route.

`SEED-001` — **The date re-base and the database reset are separate jobs**, ruled 2026-08-20. The re-base is scheduled, non-destructive and whole-week; the reset stays **manual and Owner-authorised** until a clean demo-host baseline exists and passes restore verification.

> **Task 1 is HELD.** `SEED-001` amends STEP0-001's sequence premise: the seeding automation now ships in halves, and only the re-base is ready. **No customer-access design and no credential issuance** until the Owner rules whether a scheduled re-base *alone* fires DEM-001's revisit trigger. STEP0-001 itself stands unchanged.

## Work map

| Need | Authoritative file |
|---|---|
| Committee rules, agents, gates and task dispatch | [`01-governance/CommitteeSystem.md`](01-governance/CommitteeSystem.md) |
| Decisions and unresolved Owner questions | [`01-governance/Decisions.md`](01-governance/Decisions.md) |
| Claim controls | [`01-governance/ClaimsRegister.md`](01-governance/ClaimsRegister.md) |
| Positioning, audience and message strategy | [`02-strategy/README.md`](02-strategy/README.md) |
| Challenges, demo access and order of work | [`03-website-plan/Challenges-and-Demo.md`](03-website-plan/Challenges-and-Demo.md) |
| Sitemap, journeys, funnel and forms | [`03-website-plan/README.md`](03-website-plan/README.md) |
| English and Arabic page content | [`04-content/README.md`](04-content/README.md) |
| Images, video, diagrams and approval state | [`05-media/README.md`](05-media/README.md) |
| Technical architecture | [`06-technical/Architecture.md`](06-technical/Architecture.md) |
| Security, demo operations and deployment | [`06-technical/README.md`](06-technical/README.md) |
| Readiness, open work and launch checklist | [`07-delivery/README.md`](07-delivery/README.md) |
| Committee outputs | [`committee/README.md`](committee/README.md) |
| Historical originals | [`archive/README.md`](archive/README.md) |

## Current order of work

1. Run `05-seed001-demo-date-rebase.sh check` on `demo.skyeagle.uk` — read-only; confirms the `pc_startTime` hazard and the decay state on the host that actually serves visitors.
2. Apply the one-time Saudi working-week correction, then exercise `run` and `rollback` on that host, then `install` the re-base timer. **The script refuses to install until both have run.**
3. Rule on the DEM-001 trigger question opened by `SEED-001`. **Task 1 stays held until this is answered.**
4. Pass the technical/security gate and accept residual risks in writing.
5. Build the demo-host reset baseline, re-baked to include post-baseline fixes, before any reset is scheduled.
6. Design Challenge 1 under `STEP0-001` and D-a…D-d — only after step 3.
7. Prepare the sitemap, page briefs and bilingual content.
8. Attach approved media and complete claim review before publication.

## Authority rule

The audit and locked-decision register outrank marketing copy. A convenient statement without evidence is not publishable.
