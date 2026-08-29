<?php

declare(strict_types=1);

namespace App\Payments;

use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/**
 * Server-side checkout-state restore (step F, design §14): the selection
 * a customer built before authenticating (packages, add-ons, slot,
 * contact draft) is kept in the server session, which Laravel migrates —
 * data preserved — when login regenerates the session id. After login
 * the flow restores from here and creates the Booking against the
 * authenticated customer; nothing client-side is ever authoritative, and
 * no Booking row exists before authentication (the Booking service
 * requires the customer). Payment amounts are never stored here — they
 * are recomputed server-side from the restored selection.
 */
class CheckoutStateStore
{
    private const string KEY = 'payments.checkout_state';

    /** @param array<string, mixed> $selection */
    public function remember(array $selection): void
    {
        if ($selection === []) {
            throw ValidationException::withMessages([
                'checkout_state' => 'An empty checkout selection cannot be remembered.',
            ]);
        }

        Session::put(self::KEY, [
            'selection' => $selection,
            'remembered_at' => now('UTC')->toIso8601String(),
        ]);
    }

    /** @return array<string, mixed>|null The selection, leaving it stored. */
    public function peek(): ?array
    {
        $state = Session::get(self::KEY);

        return is_array($state) ? ($state['selection'] ?? null) : null;
    }

    /** @return array<string, mixed>|null The selection, forgetting it. */
    public function pull(): ?array
    {
        $selection = $this->peek();
        $this->forget();

        return $selection;
    }

    public function forget(): void
    {
        Session::forget(self::KEY);
    }
}
