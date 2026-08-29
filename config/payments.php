<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registration gate (payment workstream step F)
    |--------------------------------------------------------------------------
    |
    | No customer enters a payment flow without an account, and — while this
    | switch is on — without a verified (activated) e-mail address. The gate
    | is enforced server-side by App\Payments\PaymentAccessGate; storefront
    | UI may hint earlier, but the gate is the authority.
    |
    | The switch exists for test revisions, where creating verified customers
    | is friction. CLAUDE_HANDOFF.md §7 states verification is REQUIRED in the
    | final public flow, so App\Payments\PaymentAccessGate refuses to boot a
    | production environment with it off rather than letting a deployment
    | silently downgrade the rule — the same fail-closed shape as
    | App\Mail\MailTestRedirect.
    |
    */

    'require_verified_email' => (bool) env('PAYMENTS_REQUIRE_VERIFIED_EMAIL', true),

];
