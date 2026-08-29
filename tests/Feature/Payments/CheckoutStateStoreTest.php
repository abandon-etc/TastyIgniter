<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\CheckoutStateStore;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class CheckoutStateStoreTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_selection_survives_the_session_regeneration_login_performs(): void
    {
        $store = $this->store();
        $store->remember(['package_id' => 7, 'addons' => [['id' => 2, 'qty' => 1]], 'slot' => 'afternoon']);

        // Logging in regenerates the session id while migrating the data —
        // this is the exact mechanism the restore relies on.
        Session::regenerate();

        $this->assertSame(7, $store->peek()['package_id']);
        $this->assertSame('afternoon', $store->peek()['slot']);
    }

    public function test_pull_restores_once_and_forgets(): void
    {
        $store = $this->store();
        $store->remember(['package_id' => 7]);

        $this->assertSame(['package_id' => 7], $store->pull());
        $this->assertNull($store->pull());
        $this->assertNull($store->peek());
    }

    public function test_an_empty_selection_is_refused(): void
    {
        $this->expectException(ValidationException::class);
        $this->store()->remember([]);
    }

    public function test_forget_clears_without_reading(): void
    {
        $store = $this->store();
        $store->remember(['package_id' => 7]);
        $store->forget();

        $this->assertNull($store->peek());
    }

    public function test_a_state_written_by_another_customer_is_discarded(): void
    {
        $store = $this->store();

        Auth::login($this->customer());
        $store->remember(['package_id' => 7]);

        Auth::login($this->customer());

        $this->assertNull($store->peek(), 'Another customer must not read the first one\'s draft.');
        $this->assertNull(Session::get('payments.checkout_state'), 'The foreign state is dropped, not merely hidden.');
    }

    public function test_a_guest_draft_is_still_readable_after_the_guest_authenticates(): void
    {
        $store = $this->store();
        $store->remember(['package_id' => 7]);

        Auth::login($this->customer());

        $this->assertSame(['package_id' => 7], $store->peek());
    }

    public function test_a_malformed_state_reads_as_nothing(): void
    {
        Session::put('payments.checkout_state', ['selection' => 'not-an-array']);

        $this->assertNull($this->store()->peek());
    }

    public function test_logging_out_clears_the_draft(): void
    {
        $store = $this->store();

        Auth::login($this->customer());
        $store->remember(['package_id' => 7]);

        Auth::logout();

        $this->assertNull(Session::get('payments.checkout_state'),
            'Auth::logout() leaves the session alive, so the listener must clear the draft.');
    }

    private function customer(): Customer
    {
        return Customer::factory()->create([
            'password' => 'test-only-password',
            'status' => true,
            'is_activated' => true,
        ]);
    }

    private function store(): CheckoutStateStore
    {
        return $this->app->make(CheckoutStateStore::class);
    }
}
