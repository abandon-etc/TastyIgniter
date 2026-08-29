<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\CheckoutStateStore;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class CheckoutStateStoreTest extends TestCase
{
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

    private function store(): CheckoutStateStore
    {
        return $this->app->make(CheckoutStateStore::class);
    }
}
