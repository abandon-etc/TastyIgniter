<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\Exceptions\PaymentAccessDenied;
use App\Payments\PaymentAccessGate;
use App\Payments\PaymentGateConfiguration;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

final class PaymentAccessGateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_guest_may_not_enter_payment(): void
    {
        $gate = $this->gate();

        $this->assertFalse($gate->mayEnterPayment());

        try {
            $gate->assertMayEnterPayment();
            $this->fail('A guest must be refused.');
        } catch (PaymentAccessDenied $e) {
            $this->assertSame(PaymentAccessDenied::LOGIN_REQUIRED, $e->reason());
        }
    }

    public function test_the_gate_reads_the_authenticated_customer_not_an_argument(): void
    {
        config()->set('payments.require_verified_email', true);
        $customer = $this->customer(activated: true);

        Auth::login($customer);

        $this->assertTrue($this->gate()->mayEnterPayment());
        $this->assertTrue($this->gate()->assertMayEnterPayment()->is($customer));
    }

    public function test_an_authenticated_but_unverified_customer_is_refused(): void
    {
        config()->set('payments.require_verified_email', true);
        Auth::login($this->customer(activated: false));

        try {
            $this->gate()->assertMayEnterPayment();
            $this->fail('An unverified customer must be refused while the switch is on.');
        } catch (PaymentAccessDenied $e) {
            $this->assertSame(PaymentAccessDenied::VERIFICATION_REQUIRED, $e->reason());
        }
    }

    public function test_a_disabled_customer_may_not_enter_payment(): void
    {
        try {
            $this->gate()->assertCustomerMayEnterPayment($this->customer(activated: true, enabled: false));
            $this->fail('A disabled customer must be refused.');
        } catch (PaymentAccessDenied $e) {
            $this->assertSame(PaymentAccessDenied::LOGIN_REQUIRED, $e->reason());
        }
    }

    public function test_the_verification_switch_can_be_turned_off_but_login_still_gates(): void
    {
        config()->set('payments.require_verified_email', false);

        $this->gate()->assertCustomerMayEnterPayment($this->customer(activated: false));

        $this->assertFalse($this->gate()->mayEnterPayment());
    }

    public function test_production_refuses_to_boot_with_verification_disabled(): void
    {
        $config = $this->app['config'];
        $config->set('app.env', 'production');
        $config->set('payments.require_verified_email', false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/refusing to start/');
        PaymentGateConfiguration::assert($config);
    }

    public function test_production_boots_with_verification_required(): void
    {
        $config = $this->app['config'];
        $config->set('app.env', 'production');
        $config->set('payments.require_verified_email', true);

        PaymentGateConfiguration::assert($config);
        $this->assertTrue(true, 'A correctly configured production environment boots.');
    }

    private function customer(bool $activated, bool $enabled = true): Customer
    {
        return Customer::factory()->create([
            'password' => 'test-only-password',
            'status' => $enabled,
            'is_activated' => $activated,
        ]);
    }

    private function gate(): PaymentAccessGate
    {
        return $this->app->make(PaymentAccessGate::class);
    }
}
