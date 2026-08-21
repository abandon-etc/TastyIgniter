# D3C Progress

Snapshot of where Delivery D3C acceptance stands. Overwritten as work moves;
it is not a history. `CHANGELOG_AI.md` keeps the history.

Last updated: 2026-08-21.

Delivery is being switched on for testing only, on a copy of the staging site
that receives no visitors. The live site is untouched throughout.

## Acceptance checks

This table is the acceptance record. Every row must pass before anyone may ask
to switch Delivery on for real.

| What is being checked | Status |
| --- | --- |
| Address provider unavailable: order refused safely, basket kept, pickup still offered, nothing technical shown | 4 of 5 passed; basket-kept outstanding |
| Nothing private leaks into pages or logs: addresses, keys, database text, file paths, raw errors | Passed for the provider-unavailable case |
| Opening hours: Monday to Friday 12:00-21:00, closed weekends | Partly checked; closed-before-noon confirmed 2026-08-21 |
| Delivery area: addresses inside, outside, and exactly on the boundary | Not started |
| Delivery fee of CA$5.00 below the free threshold | Not started |
| Free delivery at CA$80.00 and the CA$20.00 minimum, tested just below, exactly at, and just above | Not started |
| Addresses that are unrecognised, incomplete, or out of area; and changing an address | Not started |
| Switching between pickup and delivery, both directions | Not started |
| Old sessions cleared, basket kept, totals correct, resistant to tampering, API rules enforced | Not started |
| Desktop, mobile at 390px, keyboard use, clean browser console | Not started |
| Nothing else broken: pickup, birthday, reservations, admin, media, health | Not started |
| No real orders, customers, reservations, payments, or emails created | Holding; nothing real created so far |
| Test data cleaned up afterwards | Not started |

## Test copies of the site currently deployed

All receive 0% of visitors. The live site stays on its own revision throughout.

| Name | Purpose |
| --- | --- |
| `d3c-g-1f8f0c75` | Main testing. Address lookups pinned to Google only, so a boundary result can be attributed to one coordinate source |
| `d3c-pu-1f8f0c75` | Provider-unavailable testing. No address provider configured at all |
| `d3c-25f9813b` | Earlier check of log redaction |
| `d3c-a2ee559c` | First D3C copy |
| `d3c-e9a4f7ca` | Failed to start. Delete this one first when cleaning up |

Cleaning any of these up needs explicit confirmation and is not done yet.

## Outstanding

| Item | Owner | When |
| --- | --- | --- |
| Create the budget alert | User | Today. It is the only spend alarm available while the account is on trial |
| Upgrade to a paid account, and in the same sitting: set the Geocoding daily cap to 500, create the budget alert, split the browser key from the server key | User | Mid-September 2026. See `CLAUDE_HANDOFF.md` section 11 |
| Weekend-closed check needs an actual weekend, or a different method | Agent | Before acceptance is complete |
| Static check that stops an exception being written into a log context | Agent | Deferred; not blocking |

## Known problem found during testing

The Google Maps key is visible in the public page source and has no restriction
on where it may be used from. The same key also authorises paid address
lookups. This is **not new and not caused by this testing**: the live site has
always exposed it. It is accepted for now because the staging site gets almost
no traffic, and it is fixed by splitting the key during the September upgrade.

## Next action

After 12:00 Montreal time, finish the basket-kept check on `d3c-pu-1f8f0c75`,
then run the main acceptance table on `d3c-g-1f8f0c75`.
