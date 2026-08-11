<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Services\PaymentGatewayRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayRegistryTest extends TestCase
{
    use RefreshDatabase;

    private function registry(): PaymentGatewayRegistry
    {
        // Resolved fresh each time: the services read settings in their
        // constructors, so a cached instance would hold stale credentials.
        return app()->makeWith(PaymentGatewayRegistry::class, []);
    }

    private function enablePhonePe(): void
    {
        SiteSetting::set('phonepe_enabled', '1', 'payment', 'boolean');
        SiteSetting::set('phonepe_merchant_id', 'MERCHANTUAT', 'payment');
        SiteSetting::set('phonepe_salt_key', 'salt-key', 'payment');
    }

    private function enableRazorpay(): void
    {
        SiteSetting::set('razorpay_enabled', '1', 'payment', 'boolean');
        SiteSetting::set('razorpay_key_id', 'rzp_test_x', 'payment');
        SiteSetting::set('razorpay_key_secret', 'secret', 'payment');
    }

    private function configurePayu(): void
    {
        SiteSetting::set('payu_enabled', '1', 'payment', 'boolean');
        SiteSetting::set('payu_property_merchant_key', 'PROPKEY', 'payment');
        SiteSetting::set('payu_property_merchant_salt', 'propsalt', 'payment');
    }

    public function test_nothing_is_offered_when_no_gateway_is_configured(): void
    {
        $this->assertSame([], $this->registry()->available());
        $this->assertFalse($this->registry()->hasAny());
        $this->assertNull($this->registry()->default());
    }

    public function test_only_configured_gateways_are_offered(): void
    {
        $this->enablePhonePe();

        $available = $this->registry()->available();

        $this->assertArrayHasKey('phonepe', $available);
        $this->assertArrayNotHasKey('razorpay', $available);
        $this->assertArrayNotHasKey('payu', $available);
    }

    public function test_both_gateways_are_offered_when_both_are_configured(): void
    {
        $this->enablePhonePe();
        $this->enableRazorpay();

        $this->assertSame(['phonepe', 'razorpay'], array_keys($this->registry()->available()));
    }

    /**
     * The whole point of the registry: a configured-but-unimplemented gateway
     * must never be shown, or the citizen reaches a checkout that does not
     * exist and the payment is lost.
     */
    public function test_payu_is_not_offered_while_its_checkout_flow_is_unimplemented(): void
    {
        $this->configurePayu();

        $all = $this->registry()->all('property');

        $this->assertFalse($all['payu']['available']);
        $this->assertStringContainsString('not implemented', $all['payu']['reason']);
        $this->assertArrayNotHasKey('payu', $this->registry()->available('property'));
    }

    public function test_payu_reports_missing_credentials_per_tax_head(): void
    {
        $this->configurePayu(); // property only

        $water = $this->registry()->all('water')['payu'];

        $this->assertFalse($water['available']);
        $this->assertStringContainsString('merchant key and salt', $water['reason']);
    }

    public function test_the_configured_default_is_used_when_it_is_available(): void
    {
        $this->enablePhonePe();
        $this->enableRazorpay();
        SiteSetting::set('active_payment_gateway', 'razorpay', 'payment');

        $this->assertSame('razorpay', $this->registry()->default());
    }

    /**
     * Selecting an unready gateway in the admin must not take checkout down for
     * every citizen.
     */
    public function test_the_default_falls_back_when_the_configured_gateway_is_not_available(): void
    {
        $this->enablePhonePe();
        SiteSetting::set('active_payment_gateway', 'payu', 'payment');

        $this->assertSame('phonepe', $this->registry()->default());
    }

    public function test_a_citizen_may_only_select_an_available_gateway(): void
    {
        $this->enablePhonePe();
        $this->configurePayu();

        $registry = $this->registry();

        $this->assertTrue($registry->isSelectable('phonepe'));
        // Tampering with the form must not route a payment to PayU.
        $this->assertFalse($registry->isSelectable('payu', 'property'));
        $this->assertFalse($registry->isSelectable('razorpay'));
        $this->assertFalse($registry->isSelectable(null));
        $this->assertFalse($registry->isSelectable('nonsense'));
    }

    public function test_an_unknown_tax_type_does_not_blow_up_payu_detection(): void
    {
        $this->configurePayu();

        $entry = $this->registry()->all('sewage')['payu'];

        $this->assertFalse($entry['available']);
        $this->assertStringContainsString('Unknown tax type', $entry['reason']);
    }

    public function test_every_entry_carries_what_the_view_needs(): void
    {
        foreach ($this->registry()->all('property') as $gateway) {
            foreach (['key', 'label', 'description', 'icon', 'colour', 'available'] as $field) {
                $this->assertArrayHasKey($field, $gateway);
            }
        }
    }
}
