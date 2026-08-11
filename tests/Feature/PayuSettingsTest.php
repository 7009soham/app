<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Services\PayuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PayuSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function configureProperty(): void
    {
        SiteSetting::set('payu_property_merchant_key', 'PROPKEY', 'payment');
        SiteSetting::set('payu_property_merchant_salt', 'propsalt', 'payment');
        SiteSetting::set('payu_property_merchant_id', 'MID-PROPERTY', 'payment');
    }

    private function configureWater(): void
    {
        SiteSetting::set('payu_water_merchant_key', 'WATERKEY', 'payment');
        SiteSetting::set('payu_water_merchant_salt', 'watersalt', 'payment');
        SiteSetting::set('payu_water_merchant_id', 'MID-WATER', 'payment');
    }

    public function test_each_tax_head_uses_its_own_merchant_account(): void
    {
        $this->configureProperty();
        $this->configureWater();

        $property = PayuService::forTaxType('property');
        $water = PayuService::forTaxType('water');

        $this->assertSame('PROPKEY', $property->getMerchantKey());
        $this->assertSame('MID-PROPERTY', $property->getMerchantId());

        $this->assertSame('WATERKEY', $water->getMerchantKey());
        $this->assertSame('MID-WATER', $water->getMerchantId());
    }

    public function test_stored_and_request_tax_type_spellings_both_resolve(): void
    {
        $this->configureWater();

        // "water" comes from the request, "water_tax" is what is persisted.
        $this->assertSame('WATERKEY', PayuService::forTaxType('water')->getMerchantKey());
        $this->assertSame('WATERKEY', PayuService::forTaxType('water_tax')->getMerchantKey());
        $this->assertSame('WATERKEY', PayuService::forTaxType('Water Tax')->getMerchantKey());
    }

    public function test_an_unknown_tax_type_throws_rather_than_guessing_an_account(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Falling back to a default here would settle money into the wrong
        // department's bank account.
        PayuService::forTaxType('sewage');
    }

    public function test_hashes_differ_between_heads_for_identical_payment_details(): void
    {
        $this->configureProperty();
        $this->configureWater();

        $params = [
            'txnid' => 'TXN123',
            'amount' => '100.00',
            'productinfo' => 'Tax',
            'firstname' => 'Asha',
            'email' => 'asha@example.com',
        ];

        $this->assertNotSame(
            PayuService::forTaxType('property')->generateRequestHash($params),
            PayuService::forTaxType('water')->generateRequestHash($params),
            'Two heads signing with different salts must never produce the same hash.'
        );
    }

    public function test_one_head_cannot_validate_the_other_heads_response(): void
    {
        $this->configureProperty();
        $this->configureWater();

        $response = [
            'status' => 'success',
            'email' => 'asha@example.com',
            'firstname' => 'Asha',
            'productinfo' => 'Water Tax',
            'amount' => '100.00',
            'txnid' => 'TXN123',
        ];

        // Signed by the water account.
        $response['hash'] = strtolower(hash('sha512', implode('|', [
            'watersalt', 'success', '', '', '', '', '', '', '', '', '', '',
            'asha@example.com', 'Asha', 'Water Tax', '100.00', 'TXN123', 'WATERKEY',
        ])));

        $this->assertTrue(PayuService::forTaxType('water')->verifyResponseHash($response));
        $this->assertFalse(
            PayuService::forTaxType('property')->verifyResponseHash($response),
            'Property salt must reject a water-signed response.'
        );
    }

    public function test_response_is_attributed_to_a_head_by_signature_not_by_payload(): void
    {
        $this->configureProperty();
        $this->configureWater();

        $response = [
            'status' => 'success',
            'email' => 'asha@example.com',
            'firstname' => 'Asha',
            // Payload claims property tax, but it is signed by the water account.
            'productinfo' => 'Property Tax',
            'amount' => '250.00',
            'txnid' => 'TXN999',
        ];

        $response['hash'] = strtolower(hash('sha512', implode('|', [
            'watersalt', 'success', '', '', '', '', '', '', '', '', '', '',
            'asha@example.com', 'Asha', 'Property Tax', '250.00', 'TXN999', 'WATERKEY',
        ])));

        $resolved = PayuService::resolveFromResponse($response);

        $this->assertNotNull($resolved);
        $this->assertSame('water', $resolved->getTaxType(), 'Attribution must follow the signature.');
    }

    public function test_an_unsigned_or_forged_response_resolves_to_nothing(): void
    {
        $this->configureProperty();
        $this->configureWater();

        $this->assertNull(PayuService::resolveFromResponse([
            'status' => 'success',
            'txnid' => 'TXN123',
            'amount' => '100.00',
            'hash' => str_repeat('a', 128),
        ]));

        $this->assertNull(PayuService::resolveFromResponse(['status' => 'success']));
    }

    public function test_enabling_payu_does_not_enable_a_head_that_has_no_credentials(): void
    {
        SiteSetting::set('payu_enabled', '1', 'payment', 'boolean');
        $this->configureProperty();

        $this->assertTrue(PayuService::forTaxType('property')->isEnabled());
        $this->assertFalse(
            PayuService::forTaxType('water')->isEnabled(),
            'Water tax has no MID yet, so it must not be considered live.'
        );
    }

    /**
     * isReady() reports whether the checkout flow exists in the code, which it
     * now does. It stays separate from isEnabled() so the registry can
     * distinguish "not set up by the admin" from "not built yet".
     */
    public function test_the_checkout_flow_is_implemented(): void
    {
        SiteSetting::set('payu_enabled', '1', 'payment', 'boolean');
        $this->configureProperty();

        $this->assertTrue(PayuService::forTaxType('property')->isReady());
    }

    public function test_amount_is_formatted_once_so_it_matches_the_signed_value(): void
    {
        $this->configureProperty();

        // PayU compares the posted amount byte for byte against the hash.
        $service = PayuService::forTaxType('property');

        $this->assertSame('400.00', $service->formatAmount(400));
        $this->assertSame('400.50', $service->formatAmount(400.5));
        $this->assertSame('1000.00', $service->formatAmount(1000.004));
    }

    public function test_checkout_fields_carry_the_tax_head_and_are_self_consistent(): void
    {
        $this->configureWater();

        $fields = PayuService::forTaxType('water')->buildCheckoutFields([
            'txnid' => 'TXN-1',
            'amount' => 250,
            'productinfo' => 'Water Tax',
            'firstname' => 'Asha',
            'email' => 'asha@example.com',
            'record_id' => 42,
            'return_url' => 'https://example.test/return',
        ]);

        $this->assertSame('WATERKEY', $fields['key']);
        $this->assertSame('water', $fields['udf1']);
        $this->assertSame('42', $fields['udf2']);
        // Success and failure both come back to the same handler.
        $this->assertSame($fields['surl'], $fields['furl']);
        $this->assertSame(
            PayuService::forTaxType('water')->generateRequestHash($fields),
            $fields['hash']
        );
    }

    public function test_environment_is_shared_and_selects_the_right_endpoints(): void
    {
        $this->configureProperty();

        SiteSetting::set('payu_env', 'sandbox', 'payment');
        $this->assertSame('https://test.payu.in/_payment', PayuService::forTaxType('property')->getPaymentUrl());

        SiteSetting::set('payu_env', 'production', 'payment');
        $this->assertSame('https://secure.payu.in/_payment', PayuService::forTaxType('water')->getPaymentUrl());
    }

    public function test_setting_keys_are_namespaced_per_head(): void
    {
        $this->assertSame([
            'key' => 'payu_property_merchant_key',
            'salt' => 'payu_property_merchant_salt',
            'mid' => 'payu_property_merchant_id',
        ], PayuService::settingKeysFor('property'));
    }

    public function test_transaction_ids_are_stripped_to_payu_safe_characters(): void
    {
        $this->configureProperty();

        $this->assertSame(
            'TXN_123-abc',
            PayuService::forTaxType('property')->sanitizeTransactionId('TXN_123-abc!@#$%')
        );
    }
}
