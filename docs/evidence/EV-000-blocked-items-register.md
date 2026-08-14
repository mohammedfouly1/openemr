# EV-000 — WHAT IS ACTUALLY BLOCKING THE REGISTER

**Produced:** 2026-08-14 · **Agent B**, Phase 2B
**Purpose:** after working every P0 that engineering can move, record what remains and **exactly what
would clear it**. Not a status summary — a list of decisions, each with its owner and its cost.

---

## 1. The headline

**Three human decisions unblock most of the remaining register.** They are not large, none needs
engineering, and none has a technical predecessor. Everything else is downstream of them.

| # | Decision | Who | Unblocks | Cost |
|---|---|---|---|---|
| **D-1** | **Name one claim reviewer** (RDY-0003) | Product Marketing | **RDY-0067, 0056, 0057, 0088** — and every future customer-facing artefact | One name, one written review step |
| **D-2** | **Commission the licence determination** (RDY-0095) — 8 closed-form questions, pack ready | Legal / Compliance | **RDY-0033, 0034, 0090, and gate G4 entirely** | One legal review of a prepared questionnaire |
| **D-3** | **Authorise three dataset changes together** | Owner + Track D | **RDY-0083, 0016, and the D-7 allergy step** | One re-baseline of RDY-0044-B |

**D-3's three changes are separate findings that happen to need the same permission**, so doing them
together costs one re-baseline instead of three:

1. Populate **13 missing UUIDs** — 12 `form_vitals`, 1 `insurance_companies` (`EV-083` §4).
2. Seed **one sensitivity-flagged encounter** and **one clinician-authored form** (`EV-016` §4).
3. Add the one allergy row that makes the D-7 allergy alert fire (`EV-040` §5, Agent A).

> **Why none was applied unilaterally.** The dataset carries two named human sign-offs. PB-046 set
> the precedent — *"quietly re-seeding an accepted artefact to fix a demo step is exactly the kind of
> churn the closure contract exists to prevent."* The same reasoning binds all three.

---

## 2. Blocked on a named human, with the exact action

| RDY | Blocked on | The single action required |
|---|---|---|
| **0002** | Founder / Product Owner | Record acceptance of the GTM's VERDICT B and its seven provisional items |
| **0003** | Product Marketing | **Name one claim reviewer.** See D-1 |
| **0004** | Product Marketing | Issue §32 to Phases 3/4/5, naming the RDY-0003 reviewer |
| **0056 0057 0067 0088** | RDY-0003 | Qualifications defined, scans executed, artefacts written. **Only the sign-off is missing** |
| **0021 0028** | *(closed)* | — |
| **0033 0034 0090 0095** | Legal / Compliance | See D-2 |
| **0065** | Sales / Pilot Owner | Use the checklist on **three consecutive calls** and record each in/out decision |
| **0066** | Legal / Compliance | Review and record the scope template |
| **0068** | Sales + Legal | Draft the pilot agreement; it must cite RDY-0073, which exists |
| **0069** | — | Needs **a pilot to exist**. Not startable |
| **0075 0076 0077** | Founder | **V-1, V-2, V-3 interviews.** No engineering. No technical predecessor |
| **0078** | Founder | Read **primary** ZATCA and NPHIES sources and record the date accessed |
| **0096** | Sales / Pilot Owner | Decide published hours and response targets — a staffing decision |
| **0016 0083** | Owner + Track D | See D-3 |
| **0013 0014 0015 0042** | — | **One manual browser session** discharges all four (curl cannot reach `main.php` — PB-016) |
| **0041** | Founder / presenter | Two D-7 rehearsals **driven by a person**, with real elapsed time. V-8 and PRC-003 both consume that number |
| **0071** | An outside reviewer | Open the export package cold and confirm it is readable. **Must not be its author** |
| **0084** | Owner | One word: does *"owner"* mean a role or a named individual? |

---

## 3. Blocked on something external or absent

| RDY | Blocker |
|---|---|
| **0047** | Needs **a person who did not write the runbook** to provision from it |
| **0064** | Hosting provisioning — **EXTERNAL**, decision made (Dammam), provisioning blocked |
| **0081** | Needs an **off-instance backup target**, which does not exist until hosting does |
| **0082** | One remaining leg needs a browser |
| **0085** | TLS needs a domain and certificate — downstream of hosting |
| **0045** | Upstream maintenance target — Agent A holds this (`EV-045`) |

---

## 4. Items where the honest answer is "the criterion is not met", not "nearly"

Recorded separately because these are the ones most likely to be quietly rounded up.

| RDY | Why it is genuinely not met |
|---|---|
| **0048** | The live DB password is `openemr` — an **unchanged upstream default**. The Phase 2A *candidate closure* should be **withdrawn** (`EV-048`) |
| **0016** | 32/32 executed probes pass, but **4 rows cannot be executed at all** on the current dataset |
| **0071** | **1 of 8** CSV-capable reports exported |
| **0057** | **Sensitivity gating has never been exercised in either direction on any dataset** — all 72 encounters are `normal`. The claim is currently an assertion from source reading |
| **0073** | Two of four criteria require RDY-0066 and RDY-0068 to exist and cite it |

---

## 5. The pattern worth naming

**Almost nothing left is engineering.** Of the P0 rows still open, the overwhelming majority are
waiting on a name, a decision, a phone call or a person opening a browser. The items that *were*
engineering — report authorization, the controller gate, the menu defects, the backup path, the
seeder, the service trigger — are done or specified.

**RDY-0075 (V-1) deserves a specific mention.** §45.2 recorded months ago that *"there is no
technical reason V-1 has not already started"*, and that is still true. It validates the wedge the
entire plan serves, it requires no engineering, and it sits at the root of the validation branch.

---

## 6. What Agent B did not touch

- **The seeded dataset and the RDY-0044-B baseline** — Track D's, and signed off by two named humans.
- **Gate counts** — Agent A holds the §0.0 Rule 3 sync.
- **RDY-0045, 0046, 0055, 0090, 0043** — Agent A's, published first.
- **Whole-file sweeps of the readiness document** — high collision while another agent is working in
  it. The stale assertions found are listed in `EV-083` §7 and `EV-056-057-088` §4.4 rather than
  swept.
