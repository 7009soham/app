<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use App\Models\SiteSetting;
use App\Models\TaxPayment;
use App\Models\TaxType;
use App\Services\PayuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PayuCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private Citizen $citizen;
    private PropertyTaxRecord $record;
    private TaxType $taxType;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        SiteSetting::set('payu_enabled', '1', 'payment', 'boolean');
        SiteSetting::set('payu_env', 'sandbox', 'payment');
        SiteSetting::set('payu_property_merchant_key', 'PROPKEY', 'payment');
        SiteSetting::set('payu_property_merchant_salt', 'propsalt', 'payment');

        // Seeded by the ensure_required_tax_types migration.
        $this->taxType = TaxType::firstOrCreate(
            ['slug' => 'property-tax'],
            ['name' => 'Property Tax', 'is_active' => true]
        );

        $this->citizen = Citizen::create([
            'customer_no' => 'C-001',
            'name' => 'Asha Kulkarni',
            'phone' => '9425551234',
            'email' => 'asha@example.com',
        ]);

        $this->record = PropertyTaxRecord::create([
            'a_no' => 1,
            'customer_no' => 'C-001',
            'property_no' => '3500/1',
            'property_type' => 'Default',
            'customer_name' => 'Asha Kulkarni',
            'balance' => 1000,
            'citizen_id' => $this->citizen->id,
        ]);
    }

    private function payment(array $overrides = []): TaxPayment
    {
        return TaxPayment::create(array_merge([
            'transaction_id' => 'TXNPAYU1',
            'citizen_id' => $this->citizen->id,
            'citizen_name' => 'Asha Kulkarni',
            'citizen_phone' => '9425551234',
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->taxType->id,
            'record_id' => $this->record->id,
            'amount' => 400,
            'period_type' => 'yearly',
            'period_start' => now()->startOfYear()->toDateString(),
            'period_end' => now()->endOfYear()->toDateString(),
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_data' => ['tax_amount' => 400, 'convenience_fee' => 0],
        ], $overrides));
    }

    /** Build a genuinely signed PayU response for the property head. */
    private function signedResponse(array $overrides = []): array
    {
        $response = array_merge([
            'status' => 'success',
            'txnid' => 'TXNPAYU1',
            'amount' => '400.00',
            'productinfo' => 'Property Tax',
            'firstname' => 'Asha Kulkarni',
            'email' => 'asha@example.com',
            'udf1' => 'property',
            'udf2' => (string) $this->record->id,
        ], $overrides);

        $response['hash'] = strtolower(hash('sha512', implode('|', [
            'propsalt', $response['status'], '', '', '', '', '',
            '', '', '', $response['udf2'], $response['udf1'],
            $response['email'], $response['firstname'], $response['productinfo'],
            $response['amount'], $response['txnid'], 'PROPKEY',
        ])));

        return $response;
    }

    public function test_the_checkout_page_renders_a_signed_auto_submitting_form(): void
    {
        $payment = $this->payment();

        $response = $this->actingAs($this->citizen, 'citizen')
            ->withSession(['pending_payment_id' => $payment->id])
            ->get(route('citizen.payment.payu-checkout'));

        $response->assertOk()
            ->assertSee('https://test.payu.in/_payment', false)
            ->assertSee('name="hash"', false)
            ->assertSee('name="txnid" value="TXNPAYU1"', false)
            ->assertSee('name="amount" value="400.00"', false);
    }

    public function test_the_form_hash_matches_what_payu_will_recompute(): void
    {
        $payment = $this->payment();

        $fields = PayuService::forTaxType('property')->buildCheckoutFields([
            'txnid' => $payment->transaction_id,
            'amount' => $payment->amount,
            'productinfo' => 'Property Tax',
            'firstname' => 'Asha Kulkarni',
            'email' => 'asha@example.com',
            'record_id' => $this->record->id,
            'return_url' => route('citizen.payment.payu-return'),
        ]);

        $expected = strtolower(hash('sha512', implode('|', [
            'PROPKEY', 'TXNPAYU1', '400.00', 'Property Tax', 'Asha Kulkarni',
            'asha@example.com', 'property', (string) $this->record->id, '', '', '',
            '', '', '', '', '', 'propsalt',
        ])));

        $this->assertSame($expected, $fields['hash']);
    }

    public function test_another_citizens_payment_cannot_be_opened(): void
    {
        $payment = $this->payment();

        $intruder = Citizen::create(['customer_no' => 'C-002', 'name' => 'Other', 'phone' => '9000000000']);

        $this->actingAs($intruder, 'citizen')
            ->withSession(['pending_payment_id' => $payment->id])
            ->get(route('citizen.payment.payu-checkout'))
            ->assertRedirect(route('citizen.payment-history'));
    }

    public function test_a_valid_success_return_credits_the_ledger_once(): void
    {
        $this->payment();

        $this->post(route('citizen.payment.payu-return'), $this->signedResponse())
            ->assertRedirect();

        $this->assertSame('600.00', (string) $this->record->fresh()->balance);
        $this->assertSame('completed', TaxPayment::first()->payment_status);
    }

    public function test_a_repeated_return_does_not_credit_twice(): void
    {
        $this->payment();

        $this->post(route('citizen.payment.payu-return'), $this->signedResponse());
        $this->post(route('citizen.payment.payu-return'), $this->signedResponse());

        $this->assertSame('600.00', (string) $this->record->fresh()->balance);
    }

    /** The signature is the only thing standing between a forged post and a
     *  cleared tax bill. */
    public function test_a_forged_hash_is_rejected_and_nothing_is_credited(): void
    {
        $this->payment();

        $forged = $this->signedResponse();
        $forged['hash'] = str_repeat('a', 128);

        // Rendered directly rather than redirected: the return route has no
        // session to flash a warning through.
        $this->post(route('citizen.payment.payu-return'), $forged)
            ->assertOk()
            ->assertSee('could not be verified', false);

        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
        $this->assertNotSame('completed', TaxPayment::first()->payment_status);
    }

    public function test_tampering_with_the_amount_invalidates_the_signature(): void
    {
        $this->payment();

        $tampered = $this->signedResponse();
        $tampered['amount'] = '1.00';

        $this->post(route('citizen.payment.payu-return'), $tampered);

        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
    }

    /** A correctly signed response whose amount does not match our record must
     *  still be refused. */
    public function test_a_signed_response_for_a_different_amount_is_refused(): void
    {
        $this->payment();

        $mismatched = $this->signedResponse(['amount' => '1.00']);

        $this->post(route('citizen.payment.payu-return'), $mismatched);

        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
        $this->assertNotSame('completed', TaxPayment::first()->payment_status);
    }

    public function test_a_failed_payment_leaves_the_balance_alone(): void
    {
        $this->payment();

        $this->post(route('citizen.payment.payu-return'), $this->signedResponse(['status' => 'failure']));

        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
        $this->assertSame('failed', TaxPayment::first()->payment_status);
    }

    public function test_the_return_route_needs_no_session_or_csrf_token(): void
    {
        $this->payment();

        // PayU posts from its own domain: no cookie, no token.
        $this->post(route('citizen.payment.payu-return'), $this->signedResponse())
            ->assertRedirect();

        $this->assertSame('completed', TaxPayment::first()->payment_status);
    }

    public function test_payu_is_now_offered_once_credentials_exist(): void
    {
        $registry = app(\App\Services\PaymentGatewayRegistry::class);

        $this->assertArrayHasKey('payu', $registry->available('property'));
        // Water has no credentials, so it stays unavailable.
        $this->assertArrayNotHasKey('payu', $registry->available('water'));
    }
}
