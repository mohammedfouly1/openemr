# EV-033 / EV-034 — PRODUCT IDENTITY STRINGS AND VENDOR LINKS

**Requirements:** RDY-0033 (identity strings), RDY-0034 (vendor links) · **Gates:** G1, G2
**Verified:** 2026-08-14 · **Agent B**, Phase 2B · **Method:** live `globals` reads + unauthenticated HTTP fetch of the demo surface

---

## 1. RDY-0033 — product identity strings

**Acceptance:** *"The login page shows the product identity Phase 3 specifies (or a neutral
placeholder until then) with no upstream product name in a position implying it is ours or ours to
rename; **the licence determination from RDY-0095 is attached**."*

| Field | Audited (2026-08-09) | Live (2026-08-14) | Verified by |
|---|---|---|---|
| `openemr_name` | `'OpenEMR'` | **`'Thiqa'`** | `SELECT gl_value FROM globals WHERE gl_name='openemr_name'` |
| `login_tagline_text` | *"The most popular open-source Electronic Health Record…"* | **`'Clinical confidence, connected care.'`** | same |
| Login page `<title>` | *OpenEMR Login* | **`<title>Thiqa Login</title>`** | `curl http://localhost:8300/interface/login/login.php?site=default` |
| Tagline rendered | upstream | **"Clinical confidence, connected care." present in the served HTML** | same fetch |

**Occurrences of `open-emr.org` on the login page: 0.**
**Occurrences of `OpenEMR` on the login page: 1** — and it is not what it looks like. See §3.

---

## 2. RDY-0034 — vendor links

**Acceptance:** *"No donation or review link renders in the demo surface; the logo link points
nowhere harmful; **the licence determination is attached**."*

| Field | Audited | Live | Rendered in the demo surface? |
|---|---|---|---|
| `display_donations_link` | `1` | **`0`** | **No** — 0 occurrences of `donate`/`Donate` in the served login HTML |
| `display_review_link` | `1` | **`0`** | **No** |
| `main_menu_logo_link` | `https://www.open-emr.org/` | **`https://skyeagle.uk/`** | Points to the vendor's own site — **not harmful** |
| `main_menu_logo_title` | *(empty)* | **`'Thiqa Health Information System'`** | — |
| `online_support_link` | `http://open-emr.org/` | **`https://skyeagle.uk/support`** | — |
| `user_manual_link` | *(empty)* | **`https://skyeagle.uk/docs`** | — |
| `display_acknowledgements` / `_on_login` | `1` / `1` | **`0` / `0`** | **No link rendered** — 0 occurrences of `acknowledg` in the served HTML |

**Direct-URL check:** `GET /acknowledge_license_cert.html` → **HTTP 403** (Apache `<Files>` deny).
So the page is unreachable by link *and* by direct URL. **That is the point that needs legal
review** — see `EV-095` §3, where it is flagged as the change most likely to require reversal.

---

## 3. ⚠ One residual `OpenEMR` string on the login page — the session cookie name

The single remaining occurrence is **not** visible product identity:

```
interface/login/login.php served HTML, line 43:
document.cookie = "OpenEMR=f81g9quvf54g20onlaqcprk1fa; path=/; domain=; expires=…; SameSite=Strict";
```

**It is the PHP session cookie name.** Nothing on screen shows it.

**It is still worth recording, because of who sees it.** Persona **P-3 is the prospect's IT
contractor — the gatekeeper**, and the first thing a competent one does is open developer tools.
A cookie named `OpenEMR` on a product presented as Thiqa is exactly the kind of detail that
persona notices, and D-1's whole premise is that we say it before they find it.

**Deliberately NOT changed.** Renaming the session cookie invalidates every live session and touches
authentication; it is a configuration decision with a blast radius, not a branding tidy-up. Recorded
here and routed to **RDY-0090** (branding surface inventory, Agent A) for classification, with a
suggested class of **B — change before the guided demo**, since it is trivially discoverable but
harmless.

**Consistent with the GTM's own position:** POS-002 discloses the open-source origin deliberately and
R-04 rates discovery as *Certain / Low impact if we said it first*. This is not something to hide —
it is something to have an answer for.

---

## 4. Status — neither is closed, for the same single reason

| Requirement | Configuration work | Rendered-surface verification | Licence determination attached | Status |
|---|---|---|---|---|
| **RDY-0033** | **Done** | **Done** — title, tagline, 0 upstream links | **MISSING — RDY-0095** | **NOT CLOSED** |
| **RDY-0034** | **Done** | **Done** — 0 donation, 0 review, 0 acknowledgements links; logo repointed | **MISSING — RDY-0095** | **NOT CLOSED** |

**Both acceptance criteria end with the same clause**, and RDY-0033's card is explicit that it is
*"blocked behind"* RDY-0095. **No further engineering closes either item.** The determination pack
that makes RDY-0095 answerable is `EV-095-licence-attribution-pack.md`.

**`Blocks`: G1 G2** for both. No gate count moved (§0.0 Rule 3).

---

## 5. Reproduce

```bash
mariadb -u root -h 127.0.0.1 openemr -N -B -e "SELECT gl_name,gl_value FROM globals WHERE gl_name IN \
 ('openemr_name','login_tagline_text','main_menu_logo_link','main_menu_logo_title', \
  'online_support_link','user_manual_link','display_donations_link','display_review_link', \
  'display_acknowledgements','display_acknowledgements_on_login')"

curl -s "http://localhost:8300/interface/login/login.php?site=default" > /tmp/login.html
grep -c "open-emr.org" /tmp/login.html   # expect 0
grep -c -i "donate\|acknowledg" /tmp/login.html  # expect 0
grep -oE "<title>[^<]*</title>" /tmp/login.html  # expect <title>Thiqa Login</title>
curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:8300/acknowledge_license_cert.html"  # expect 403
```
