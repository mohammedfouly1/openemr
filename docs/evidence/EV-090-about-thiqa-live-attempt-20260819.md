# EV-090 — About Thiqa reachable-leg live attempt, 2026-08-19

**Requirement:** RDY-0090 reachable leg only · **PB:** PB-452
**Attempt limit:** one click, bounded below 30 seconds; no retry; no Apache restart.

## Result

The visible built-in browser opened the authenticated user menu and clicked **About Thiqa once**.
The About frame completed in **21,807 ms**, within the requested limit. It rendered:

- `About Thiqa`
- version `8.2.0`
- the current installation UUID
- `https://skyeagle.uk/support`
- the branded User Manual target

Evidence: `captures/2026-08-19/RDY-0090-about-thiqa-live-20260819.jpg`.

No product-registration modal appeared because this installation did not request the registration
dialog during the attempt. Therefore this visual proves the reachable About page itself no longer
hangs and is branded, but it does not prove the modal's title by rendering the modal.

## Concurrent-work reconciliation

During this browser session, concurrent commit `5b64dd078` landed:
`fix(branding): use the branded app name in the product-registration modal title`. It replaces the
hardcoded `OpenEMR Product Registration` title with `applicationTitle + Product Registration` and
passes the configured `openemr_name` into the template render. The live About page then rendered
successfully without any visible stock OpenEMR identity.

This is a positive closure of the **specific reachable About/product-registration source leak** that
the task asked to re-attempt. It is **not closure of all RDY-0090**, whose register still requires the
remaining print/PDF/email and dormant OAuth surfaces to be observed/classified.

**Verdict:** reachable About leg **PASS**; no hang; no retry; full RDY-0090 remains **NOT READY —
inventory incomplete**.
