<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Services\PayuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayuSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_payu_service_reads_credentials_from_site_settings(): void
    {
        SiteSetting::set('payu_merchant_key', 'gtKFFx', 'payment');
        SiteSetting::set('payu_merchant_salt', 'eCwWELxi', 'payment');
        SiteSetting::set('payu_merchant_id', '1234567', 'payment');

        $service = new PayuService();

        $this->assertSame('gtKFFx', $service->getMerchantKey());
        $this->assertSame('1234567', $service->getMerchantId());
    }

    public function test_payu_is_disabled_until_enabled_and_credentials_present(): void
    {
        $service = new PayuService();
        $this->assertFalse($service->isEnabled(), 'PayU must be off when nothing is configured.');

        SiteSetting::set('payu_enabled', '1', 'payment', 'boolean');
        $this->assertFalse((new PayuService())->isEnabled(), 'Enabling without credentials must not switch PayU on.');

        SiteSetting::set('payu_merchant_key', 'gtKFFx', 'payment');
        SiteSetting::set('payu_merchant_salt', 'eCwWELxi', 'payment');
        $this->assertTrue((new PayuService())->isEnabled());
    }

    public function test_payu_is_never_ready_while_checkout_flow_is_unimplemented(): void
    {
        SiteSetting::set('payu_enabled', '1', 'payment', 'boolean');
        SiteSetting::set('payu_merchant_key', 'gtKFFx', 'payment');
        SiteSetting::set('payu_merchant_salt', 'eCwWELxi', 'payment');

        $this->assertFalse((new PayuService())->isReady(), 'PayU must not accept payments before the checkout flow exists.');
    }

    public function test_environment_selects_the_correct_endpoints(): void
    {
        SiteSetting::set('payu_env', 'sandbox', 'payment');
        $this->assertSame('https://test.payu.in/_payment', (new PayuService())->getPaymentUrl());

        SiteSetting::set('payu_env', 'production', 'payment');
        $this->assertSame('https://secure.payu.in/_payment', (new PayuService())->getPaymentUrl());
    }

    public function test_request_hash_matches_the_documented_payu_sequence(): void
    {
        SiteSetting::set('payu_merchant_key', 'gtKFFx', 'payment');
        SiteSetting::set('payu_merchant_salt', 'eCwWELxi', 'payment');

        $params = [
            'txnid' => 'TXN123',
            'amount' => '100.00',
            'productinfo' => 'Property Tax',
            'firstname' => 'Asha',
            'email' => 'asha@example.com',
        ];

        $expected = strtolower(hash('sha512', implode('|', [
            'gtKFFx', 'TXN123', '100.00', 'Property Tax', 'Asha', 'asha@example.com',
            '', '', '', '', '', '', '', '', '', '', 'eCwWELxi',
        ])));

        $this->assertSame($expected, (new PayuService())->generateRequestHash($params));
    }

    public function test_response_hash_verification_rejects_a_tampered_payload(): void
    {
        SiteSetting::set('payu_merchant_key', 'gtKFFx', 'payment');
        SiteSetting::set('payu_merchant_salt', 'eCwWELxi', 'payment');

        $service = new PayuService();

        $response = [
            'status' => 'success',
            'email' => 'asha@example.com',
            'firstname' => 'Asha',
            'productinfo' => 'Property Tax',
            'amount' => '100.00',
            'txnid' => 'TXN123',
        ];

        $response['hash'] = strtolower(hash('sha512', implode('|', [
            'eCwWELxi', 'success', '', '', '', '', '', '', '', '', '', '',
            'asha@example.com', 'Asha', 'Property Tax', '100.00', 'TXN123', 'gtKFFx',
        ])));

        $this->assertTrue($service->verifyResponseHash($response));

        // A tampered amount must invalidate the signature.
        $tampered = $response;
        $tampered['amount'] = '1.00';
        $this->assertFalse($service->verifyResponseHash($tampered));
    }

    public function test_transaction_ids_are_stripped_to_payu_safe_characters(): void
    {
        $this->assertSame('TXN_123-abc', (new PayuService())->sanitizeTransactionId('TXN_123-abc!@#$%'));
    }
}
