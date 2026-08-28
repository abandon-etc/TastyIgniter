# Quebec Refund and Cancellation Research (Step H)

Date: 2026-08-28
Environment: official web pages read in the browser, read-only
Status: research record, same discipline as `QUEBEC_TAX_PLAN.md`: findings
are recorded from official sources with URL and retrieval date; **this
document adds no legal interpretation of its own**, and every conclusion
that requires one is listed as an open question for the owner's
professional advisor. Implementation (step I of
`BIRTHDAY_PAYMENT_ARCHITECTURE_DESIGN.md`) starts only after those answers
and its own approval.

## 1. Sources read on 2026-08-28

| Source | URL | Page's own last update |
| --- | --- | --- |
| OPC, "Online Purchases" (index) | https://www.opc.gouv.qc.ca/en/consumer/topic/purchase/online-purchase | 2026-07-30 |
| OPC, "Contents of the Contract" | https://www.opc.gouv.qc.ca/en/consumer/topic/purchase/online-purchase/contract | 2026-07-02 |
| OPC, "How to Cancel an Online Purchase" | https://www.opc.gouv.qc.ca/en/consumer/topic/purchase/online-purchase/cancelling | 2026-02-26 |
| OPC, "Consumer Tips: Before Buying Online" | https://www.opc.gouv.qc.ca/en/consumer/topic/purchase/online-purchase/advice | 2026-08-04 |
| LegisQuébec, Consumer Protection Act, CQLR c. P-40.1 (official consolidated text; distance-contract provisions from s. 54.1) | https://www.legisquebec.gouv.qc.ca/en/document/cs/P-40.1 | consolidated statute |

Every OPC page carries its own caveat that the plain-language content "does
not replace the texts of the laws and regulations"; the statute is the
authority, and reading it onto this business is the professional advisor's
job.

## 2. What the official pages state

### Pre-contract disclosure (merchant obligations before the purchase)

Before the transaction is completed, the merchant must provide, clearly,
expressly brought to the consumer's attention, and in a form the consumer
can easily save and print: the merchant's name, address, telephone number
(fax/e-mail if applicable); a detailed description of the goods or
services; the price, related fees, and applicable taxes; the total cost;
any third-party fees that cannot reasonably be calculated; the payment
terms; the currency if not Canadian dollars; the delivery time or date and,
if applicable, delivery method, carrier, and place; **the cancellation,
return, exchange and refund policy, if applicable**; and any other
restrictions or conditions. The merchant must offer the option to accept,
modify or refuse the offer before the purchase completes. (OPC "Before
Buying Online", section "Getting All the Required Information".)

### Contract copy

A copy of the contract must reach the consumer within 15 days of the
purchase, saveable and printable, containing the required information.
**Exception list includes "goods that may perish quickly, for example,
food products."** (OPC "Contents of the Contract".)

### Statutory cancellation and chargeback

Where the regime applies, the consumer can cancel for listed reasons
(goods/services not received, contract not received, required information
missing or poorly presented, discrepancies discovered on receipt of the
contract, item, or card statement). Procedure and clocks as published: the
consumer sends a cancellation notice; the merchant has **15 days to
reimburse**; failing that the consumer may file a **chargeback request
with the credit-card issuer within the following 60 days**; the issuer
acknowledges within 30 days and credits within 90 days or two complete
statement periods, whichever comes first; delivered goods go back at the
merchant's expense. (OPC "How to Cancel an Online Purchase".)

**The same page's exception list — purchases these rules do not apply
to — includes: goods that may perish quickly, such as food products;
auction goods; lottery tickets; credit contracts; and "service contracts
for training, teaching or assistance (defined as 'contracts of successive
performance')", with examples including day camps and daycare services.**

## 3. Read onto this project's two payables — as questions, not conclusions

- **Restaurant food orders (pickup/delivery).** The OPC pages exempt
  quickly-perishing goods such as food from both the contract-copy duty
  and the statutory cancellation regime. Whether that exemption covers
  this storefront's orders in full, and which obligations (notably the
  pre-contract disclosure block above) still apply regardless, is for the
  advisor to confirm against the statute.
- **Birthday venue booking.** A prepaid venue/service booking is not
  perishable food, and it resembles neither cleanly: the exception list
  names successive-performance service contracts (day camps among the
  examples), while a one-time party booking may or may not classify with
  them. Whether the distance-contract cancellation regime (and therefore
  the statutory chargeback exposure) applies to Birthday bookings is the
  central legal question for step I, together with whatever deposit and
  prepayment rules apply.

## 4. Operational implications that hold under any legal reading

1. **Refund tooling is needed regardless.** Card-network chargebacks exist
   contractually even where the statutory regime does not apply, and the
   OPC chargeback procedure shows the clocks a card issuer works to. The
   `payment_refunds` ledger and refund interfaces of step D are justified
   independent of the legal classification; only the *policy* awaits
   advice.
2. **The checkout must carry the disclosure block.** The pre-contract list
   (identity, description, totals with taxes, payment terms, delivery
   time, cancellation/refund policy, accept/modify/refuse) is a concrete
   checkout-page checklist for steps E/F/J, and the policy text it
   requires is a business artifact the owner must approve.
3. **French.** Contracts and pre-contract information for a Quebec
   storefront fall under the French-language requirements already recorded
   (`CLAUDE_HANDOFF.md` §11, 板块二): the disclosure block and policy text
   must exist in French from the start, English alongside.
4. **A 15-day reimbursement clock** is the published merchant-side tempo
   where cancellation applies; whatever policy the advisor lands on,
   refund operations should be able to meet it.

## 5. Open questions for the owner's professional advisor

1. Does the perishable-food exemption cover this storefront's pickup and
   delivery orders entirely; what residue of the distance-contract rules
   still applies to them?
2. How is the Birthday venue booking classified — successive-performance
   service, ordinary distance service contract, or otherwise — and which
   cancellation/refund rights follow?
3. What deposit/prepayment and cancellation-window terms are lawful and
   advisable for Birthday bookings, and what must the policy wording say
   (both languages)?
4. Are there sector rules for restaurants beyond the CPA (the WEB-SRM
   mandatory-billing question is already open in `QUEBEC_TAX_PLAN.md`)?
5. Confirmation of the exact policy text to publish at checkout per the
   disclosure block.

Step I implements only after these answers; nothing in this document
changes code, settings, or policy today.
