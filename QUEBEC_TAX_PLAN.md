# Quebec Tax Plan (GST + QST)

Date: 2026-08-24
Environment: repository source inspection and official web pages, read-only
Status: investigation and plan only. **No tax setting was changed.** The tax
settings are shared by every revision: switching them on changes what real
Pickup customers pay on the live storefront at that instant. Implementation
is a separately approved change — the first in this project that touches
real payment amounts — and its timing is agreed with the owner first.

## 1. The decision this plan implements

From the decision table in `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md`
(accountant conclusion relayed by the user on 2026-08-23): the Delivery fee
is its own taxable line inside the pre-tax subtotal, taxed under the same
rules as ordinary items, GST plus QST; menu prices stay tax-exclusive and
tax is added at checkout. Rates are read from official pages at
implementation time; no relayed figure is accepted.

## 2. Official rates, read directly on 2026-08-24

| Tax | Rate | Base | Source (read 2026-08-24) |
| --- | --- | --- | --- |
| GST | 5% | the selling price | canada.ca, "Charge and collect the GST/HST — Which rate to charge": the province table lists Quebec at GST 5%, PST 9.975% (https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/gst-hst-businesses/charge-collect-which-rate.html) |
| QST | 9.975% | the selling price **excluding the GST** | Revenu Québec, "Basic Rules for Applying the GST/HST and QST": "the goods and services tax (GST), which is calculated at a rate of 5% on the selling price; and the Québec sales tax (QST), which is calculated at a rate of 9.975% on the selling price excluding the GST" (https://www.revenuquebec.ca/en/businesses/consumption-taxes/gsthst-and-qst/basic-rules-for-applying-the-gsthst-and-qst/) |

Both taxes are therefore computed on the same pre-tax base; QST does not
compound on GST. Combined effective burden 14.975% of the pre-tax total.
Re-read both pages at implementation time; these figures date from this
reading.

The same canada.ca page's receipt section requires showing the rate that
applies and the tax amount as separate lines, or a clear statement that the
total includes it. (For HST provinces it forbids splitting federal/provincial
parts; Quebec is not an HST province — GST and QST are two taxes.)

## 3. What the installed vendor provides

Read from `vendor/tastyigniter/ti-ext-cart` (v4.2.3 installed).

- Settings (admin Order Settings, tax tab; stored in the shared settings
  table): `tax_mode` (off today), `tax_percentage` (number; 0 today),
  `tax_menu_price` (0 = prices include tax, 1 = apply tax on price; the
  decision is tax-exclusive = 1), `tax_delivery_charge` (off today).
- One cart condition, `Igniter\Cart\CartConditions\Tax`, priority 300
  (after the Delivery fee at 100 and coupons at 200). With
  `tax_delivery_charge` **on**, the Delivery fee stays inside the taxed
  base — exactly the accountant's conclusion. With it off, the fee is
  subtracted before the tax and added back after.
- **The vendor condition cannot express QST.** `Tax::onLoad()` casts the
  rate to an integer: `$this->taxRate = (int)setting('tax_percentage', 0)`
  (and the label likewise). 9.975 becomes 9; a combined 14.975 becomes 14.
  The admin field accepts a decimal, but the condition truncates it. There
  is also only one rate and one line, while GST and QST are two taxes.
- Conditions are registered per extension through
  `registerCartConditions()` and collected by the condition manager, so the
  project's own extension can register additional conditions. Which
  conditions are enabled, and in what order, is itself stored in the shared
  Cart settings — enabling a new condition is a shared-settings step with
  the same live-site reach as `tax_mode`.
- Each applied condition renders its own totals line and persists its own
  `order_totals` row; that is what puts TPS and TVQ on the customer's
  totals and the order record as separate lines.

## 4. Options

- **Vendor condition as shipped (rate 15 or 14.975): rejected.** 14.975
  truncates to 14 (wrong amount, silently); 15 is the wrong rate. Either
  misstates Quebec tax on every order.
- **Option A — one project-owned combined condition at 14.975%,
  decimal-safe.** Smallest change; produces a single "tax" line. Doubtful
  against the receipt guidance (one line cannot show "the rate that
  applies" for two distinct taxes, and Quebec practice shows TPS and TVQ
  separately with registration numbers). Keep only if the accountant
  explicitly accepts a combined line.
- **Option B — two project-owned conditions, TPS (GST) 5% and TVQ (QST)
  9.975% — the recommended direction, pending the accountant's view of the
  receipt format.** Both compute on the same pre-tax base (items plus
  Delivery fee, per the decision; the second condition must not compound on
  the first — conditions stack, so the TVQ condition must derive the
  pre-GST base rather than taxing the running total; a test pins
  non-compounding). Decimal-safe rates held in project configuration, two
  labelled lines in the cart, checkout, order totals, and mail, carrying
  the registration numbers once the owner supplies them. This is the
  project's established pattern for vendor behaviour it cannot accept
  (`DeliveryOrderType`, `WeekdayScheduleCorrection`).

Worked example under Option B (Delivery, pre-tax base 23.99 + 5.00 fee =
28.99): GST 1.4495 → 1.45; QST 2.8918 → 2.89; total 33.33. Rounding is per
tax line to the cent; confirm the rounding convention with the accountant
rather than assuming this arithmetic.

## 5. Open questions for the owner and accountant

1. Receipt format: TPS and TVQ as two lines with both registration numbers
   — confirm, and supply the numbers (they are entered in the provider UI /
   admin, never in chat or this repository).
2. **Mandatory billing (WEB-SRM).** Revenu Québec's "Mandatory Billing"
   page (read 2026-08-24,
   https://www.revenuquebec.ca/en/businesses/sector-specific-measures/mandatory-billing/)
   lists restaurants among the covered sectors: prescribed equipment,
   bills within prescribed times, prescribed information sent to the
   WEB-SRM. Whether this online-ordering site's orders fall under those
   obligations, and how the site's bill relates to the certified billing
   device, is a compliance question this plan does not answer. It needs the
   accountant's answer **before** the tax display work is finalized.
3. Whether any menu item is zero-rated or exempt. The accountant's
   conclusion ("taxed like ordinary items") is recorded and this plan
   follows it; classification stays their call.
4. Timing of enablement on the live site, since Pickup customers start
   paying tax at that moment.

## 6. Implementation and verification path (for the separately approved change)

1. **Code (Level 1):** project-owned condition(s) per the chosen option,
   with tests: decimal rates survive (9.975 stays 9.975), non-compounding
   (QST on the pre-GST base), Delivery-fee inclusion on Delivery orders and
   correct behaviour on Pickup, rounding per line, label text, and the
   before/after suite comparison the workflow requires. French labels (TPS/
   TVQ) belong to the localization workstream and must be present before
   launch.
2. **Enumerate the consumers** (workflow section 8b) before merging: cart
   box totals, checkout summary and the theme's server-side checks,
   `order_totals` persistence, admin order detail, mail templates, the API,
   and anything reading the vendor `tax` condition by name.
3. **Isolated verification:** a 0%-traffic copy with the conditions
   enabled against the shared settings staged for it — note the structural
   limit recorded in `D3C_PROGRESS.md`: settings are shared, so even the
   staged enablement writes shared state. Plan the verification window with
   the owner, verify a basket matrix (pickup and delivery, below/at/above
   the free threshold), read back `order_totals` rows for a synthetic
   order if one is authorized, and restore/confirm state after.
4. **Enablement (separate explicit approval, owner-agreed time):** switch
   `tax_mode` on with the conditions configured; FP-1 pair around the
   runtime work; before/after read-back of every touched setting into
   `ADMIN_CONFIGURATION_TRACKER.md`; immediate storefront read-back of a
   Pickup total. **Rollback:** `tax_mode` off restores pre-tax totals at
   once; record the exact prior values before enabling.

## 7. What this round did not do

No tax setting, cart-condition setting, code, schema, or runtime state was
changed. No rate was entered anywhere. This document and its changelog entry
are the only outputs.
