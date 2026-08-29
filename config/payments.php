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
    */

    'require_verified_email' => (bool) env('PAYMENTS_REQUIRE_VERIFIED_EMAIL', true),

];
