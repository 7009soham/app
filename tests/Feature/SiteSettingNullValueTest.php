<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Services\PaymentGatewayRegistry;
use App\Services\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Production had razorpay_key_id stored as a row with a NULL value rather than
 * absent. SiteSetting::get() only applied its default when the row was missing,
 * so it returned null, and RazorpayService::$keyId is a typed string - which
 * threw a TypeError the moment anything constructed the service.
 */
class SiteSettingNullValueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function insertNullRow(string $key): void
    {
        // set() would coerce; this reproduces what is actually in the database.
        SiteSetting::create(['key' => $key, 'value' => null, 'group' => 'payment', 'type' => 'text']);
    }

    public function test_a_null_value_falls_back_to_the_default(): void
    {
        $this->insertNullRow('razorpay_key_id');

        $this->assertSame('', SiteSetting::get('razorpay_key_id', ''));
    }

    public function test_the_resolved_value_is_cached_per_key_not_per_default(): void
    {
        $this->insertNullRow('razorpay_key_id');

        $this->assertSame('first', SiteSetting::get('razorpay_key_id', 'first'));

        // Documenting a real quirk rather than pretending it does not exist:
        // the cache is keyed on the setting name alone, so the default from the
        // first caller wins for the rest of the hour. Harmless because callers
        // use one default per key, but worth knowing when debugging.
        $this->assertSame('first', SiteSetting::get('razorpay_key_id', 'second'));

        Cache::forget('setting_razorpay_key_id');
        $this->assertSame('second', SiteSetting::get('razorpay_key_id', 'second'));
    }

    public function test_a_missing_row_still_falls_back_to_the_default(): void
    {
        $this->assertSame('', SiteSetting::get('never_set_key', ''));
    }

    public function test_a_real_value_is_returned_unchanged(): void
    {
        SiteSetting::set('razorpay_key_id', 'rzp_test_abc', 'payment');

        $this->assertSame('rzp_test_abc', SiteSetting::get('razorpay_key_id', ''));
    }

    public function test_an_empty_string_is_not_replaced_by_the_default(): void
    {
        // Empty is a deliberate "cleared" value, distinct from never set.
        SiteSetting::set('razorpay_key_id', '', 'payment');

        $this->assertSame('', SiteSetting::get('razorpay_key_id', 'fallback'));
    }

    public function test_razorpay_service_constructs_with_null_settings(): void
    {
        $this->insertNullRow('razorpay_key_id');
        $this->insertNullRow('razorpay_key_secret');

        $service = new RazorpayService();

        $this->assertFalse($service->isEnabled());
        $this->assertSame('', $service->getKeyId());
    }

    public function test_the_pay_bill_gateway_lookup_survives_null_settings(): void
    {
        $this->insertNullRow('razorpay_key_id');
        $this->insertNullRow('phonepe_merchant_id');
        $this->insertNullRow('payu_property_merchant_key');

        // This is what the pay-bill page calls; it must not throw.
        $registry = app(PaymentGatewayRegistry::class);

        $this->assertSame([], $registry->available('property'));
        $this->assertNull($registry->default('property'));
    }
}
