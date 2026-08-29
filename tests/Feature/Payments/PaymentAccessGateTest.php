<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\Exceptions\PaymentAccessDenied;
use App\Payments\PaymentAccessGate;
use Igniter\User\Models\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

final class PaymentAccessGateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_guest_may_not_enter_payment(): void
    {
        $gate = $this->gate();

        $this->assertFalse($gate->mayEnterPayment(null));

        try {
            $gate->assertMayEnterPayment(null);
            $this->fail('A guest must be refused.');
        } catch (PaymentAccessDenied $e) {
            $this->assertSame(PaymentAccessDenied::LOGIN_REQUIRED, $e->reason());
        }
    }

    public function test_a_disabled_customer_may_not_enter_payment(): void
    {
        $customer = Customer::factory()->create(['status' => false, 'is_activated' => true]);

        try {
            $this->gate()->assertMayEnterPayment($customer);
            $this->fail('A disabled customer must be refused.');
        } catch (PaymentAccessDenied $e) {
            $this->assertSame(PaymentAccessDenied::LOGIN_REQUIRED, $e->reason());
        }
    }

    public function test_an_unverified_customer_is_refused_while_verification_is_required(): void
    {
        config()->set('payments.require_verified_email', true);
        $customer = Customer::factory()->create(['status' => true, 'is_activated' => false]);

        try {
            $this->gate()->assertMayEnterPayment($customer);
            $this->fail('An unverified customer must be refused while the switch is on.');
        } catch (PaymentAccessDenied $e) {
            $this->assertSame(PaymentAccessDenied::VERIFICATION_REQUIRED, $e->reason());
        }
    }

    public function test_a_verified_enabled_customer_passes(): void
    {
        config()->set('payments.require_verified_email', true);
        $customer = Customer::factory()->create(['status' => true, 'is_activated' => true]);

        $this->gate()->assertMayEnterPayment($customer);
        $this->assertTrue($this->gate()->mayEnterPayment($customer));
    }

    public function test_the_verification_switch_can_be_turned_off_but_login_still_gates(): void
    {
        config()->set('payments.require_verified_email', false);

        $unverified = Customer::factory()->create(['status' => true, 'is_activated' => false]);
        $this->assertTrue($this->gate()->mayEnterPayment($unverified));

        $this->assertFalse($this->gate()->mayEnterPayment(null));
    }

    private function gate(): PaymentAccessGate
    {
        return $this->app->make(PaymentAccessGate::class);
    }
}
