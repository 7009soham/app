<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use App\Models\SiteSetting;
use App\Models\TaxPayment;
use App\Models\TaxType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * How the flow behaves when things go wrong, which is most of the time in a
 * payment system. A citizen must always be able to tell whether they were
 * charged.
 */
class PaymentFailurePathsTest extends TestCase
{
    use RefreshDatabase;

    private Citizen $citizen;
    private PropertyTaxRecord $record;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        SiteSetting::set('payu_enabled', '1', 'payment', 'boolean');
        SiteSetting::set('payu_property_merchant_key', 'PROPKEY', 'payment');
        SiteSetting::set('payu_property_merchant_salt', 'propsalt', 'payment');

        TaxType::firstOrCreate(['slug' => 'property-tax'], ['name' => 'Property Tax', 'is_active' => true]);

        $this->citizen = Citizen::create([
            'customer_no' => 'C-001', 'name' => 'Asha', 'phone' => '9425551234',
        ]);

        $this->record = PropertyTaxRecord::create([
            'a_no' => 1, 'customer_no' => 'C-001', 'property_no' => '3500/1',
            'property_type' => 'Default', 'customer_name' => 'Asha',
            'balance' => 1000, 'citizen_id' => $this->citizen->id,
        ]);
    }

    private function payment(): TaxPayment
    {
        return TaxPayment::create([
            'transaction_id' => 'TXNFAIL1',
            'citizen_id' => $this->citizen->id,
            'citizen_name' => 'Asha',
            'citizen_phone' => '9425551234',
            'tax_type' => 'property_tax',
            'tax_type_id' => TaxType::where('slug', 'property-tax')->value('id'),
            'record_id' => $this->record->id,
            'amount' => 400,
            'period_type' => 'yearly',
            'period_start' => now()->startOfYear()->toDateString(),
            'period_end' => now()->endOfYear()->toDateString(),
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'payu',
            'payment_data' => ['tax_amount' => 400, 'convenience_fee' => 0],
        ]);
    }

    private function signedFailure(array $extra = []): array
    {
        $response = array_merge([
            'status' => 'failure',
            'txnid' => 'TXNFAIL1',
            'amount' => '400.00',
            'productinfo' => 'Property Tax',
            'firstname' => 'Asha',
            'email' => 'asha@example.com',
            'udf1' => 'property',
            'udf2' => (string) $this->record->id,
        ], $extra);

        $response['hash'] = strtolower(hash('sha512', implode('|', [
            'propsalt', $response['status'], '', '', '', '', '',
            '', '', '', $response['udf2'], $response['udf1'],
            $response['email'], $response['firstname'], $response['productinfo'],
            $response['amount'], $response['txnid'], 'PROPKEY',
        ])));

        return $response;
    }

    /**
     * Post a PayU result and return the rendered outcome page, lowercased.
     *
     * The return handler runs without a session (see the route), so the outcome
     * travels in a signed URL rather than a flash message.
     */
    private function outcomePageFor(array $response): string
    {
        $location = $this->post(route('citizen.payment.payu-return'), $response)
            ->headers->get('Location');

        return strtolower($this->get($location)->getContent());
    }

    /**
     * The original bug: PayU reports cancellation in unmappedstatus, which was
     * never inspected, so someone who pressed Cancel was told there had been a
     * technical problem and might pay again out of doubt.
     */
    public function test_a_user_cancellation_is_reported_as_a_cancellation(): void
    {
        $this->payment();

        $body = $this->outcomePageFor($this->signedFailure(['unmappedstatus' => 'userCancelled']));

        $this->assertStringContainsString('cancelled', $body);
        $this->assertStringContainsString('nothing has been charged', $body);
        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
    }

    /**
     * The info flash was not rendered by the citizen layout, so a cancellation
     * message was set and then silently dropped.
     */
    public function test_the_cancellation_message_is_actually_visible_to_the_citizen(): void
    {
        $this->payment();

        $body = $this->outcomePageFor($this->signedFailure(['unmappedstatus' => 'userCancelled']));

        $this->assertStringContainsString('payment cancelled', $body);
    }

    public function test_cancellation_wording_is_also_detected_from_the_bank_message(): void
    {
        $this->payment();

        $body = $this->outcomePageFor($this->signedFailure(['field9' => 'Transaction is cancelled by user']));

        $this->assertStringContainsString('cancelled', $body);
    }

    public function test_insufficient_funds_says_so_plainly(): void
    {
        $this->payment();

        $body = $this->outcomePageFor($this->signedFailure(['error_Message' => 'Insufficient funds in account']));

        $this->assertStringContainsString('insufficient funds', $body);
    }

    public function test_a_bank_decline_is_distinguished_from_a_technical_fault(): void
    {
        $this->payment();

        $body = $this->outcomePageFor($this->signedFailure(['error_Message' => 'Transaction declined by issuing bank']));

        $this->assertStringContainsString('declined', $body);
        $this->assertStringContainsString('nothing has been charged', $body);
    }

    /**
     * An unrecognised failure must never claim the citizen was not charged,
     * because we do not know that.
     */
    public function test_an_unknown_failure_does_not_promise_that_nothing_was_charged(): void
    {
        $this->payment();

        $body = $this->outcomePageFor($this->signedFailure());

        $this->assertStringNotContainsString('nothing has been charged', $body);
        $this->assertStringContainsString('contact the gram panchayat office', $body);
    }

    public function test_an_unverifiable_response_tells_the_citizen_not_to_pay_again(): void
    {
        $this->payment();

        $forged = $this->signedFailure();
        $forged['hash'] = str_repeat('f', 128);

        // A forged hash never reaches the result page. The route has no
        // session, so the warning is rendered directly.
        $body = strtolower($this->post(route('citizen.payment.payu-return'), $forged)->getContent());

        $this->assertStringContainsString('could not be verified', $body);
        $this->assertStringContainsString('do not attempt the payment again', $body);
        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
    }

    /**
     * Tapping Pay twice used to create a second pending row for the same bill,
     * which makes reconciliation ambiguous.
     */
    public function test_a_repeated_initiation_reuses_the_pending_payment(): void
    {
        SiteSetting::set('phonepe_enabled', '0', 'payment', 'boolean');

        $params = [
            'tax_type' => 'property',
            'record_id' => $this->record->id,
            'amount' => 400,
            'convenience_fee' => 0,
            'payment_method' => 'payu',
        ];

        $this->actingAs($this->citizen, 'citizen')->post(route('citizen.payment.initiate'), $params);
        $this->actingAs($this->citizen, 'citizen')->post(route('citizen.payment.initiate'), $params);

        $this->assertSame(1, TaxPayment::where('record_id', $this->record->id)->count());
    }

    public function test_a_different_amount_starts_a_separate_payment(): void
    {
        SiteSetting::set('phonepe_enabled', '0', 'payment', 'boolean');

        $base = [
            'tax_type' => 'property',
            'record_id' => $this->record->id,
            'convenience_fee' => 0,
            'payment_method' => 'payu',
        ];

        $this->actingAs($this->citizen, 'citizen')
            ->post(route('citizen.payment.initiate'), $base + ['amount' => 400]);
        $this->actingAs($this->citizen, 'citizen')
            ->post(route('citizen.payment.initiate'), $base + ['amount' => 250]);

        $this->assertSame(2, TaxPayment::where('record_id', $this->record->id)->count());
    }

    public function test_reconciliation_leaves_a_fresh_pending_payment_alone(): void
    {
        $this->payment();

        $this->artisan('payments:reconcile', ['--minutes' => 15])
            ->expectsOutputToContain('Nothing pending to reconcile')
            ->assertExitCode(0);

        $this->assertSame('pending', TaxPayment::first()->payment_status);
    }

    public function test_reconciliation_reports_a_stale_payment_without_writing_on_a_dry_run(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['created_at' => now()->subHour()])->saveQuietly();

        $this->artisan('payments:reconcile', ['--minutes' => 15, '--dry-run' => true])
            ->assertExitCode(0);

        // Dry run must not change anything, even when the gateway is unreachable.
        $this->assertSame('pending', TaxPayment::first()->payment_status);
    }

    /**
     * A gateway that cannot be reached must leave the payment pending for the
     * next run, never guess at an outcome.
     */
    public function test_an_unreachable_gateway_leaves_the_payment_pending(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['created_at' => now()->subHour()])->saveQuietly();

        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response('', 500),
        ]);

        $this->artisan('payments:reconcile', ['--minutes' => 15])->assertExitCode(0);

        $this->assertSame('pending', TaxPayment::first()->payment_status);
        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
    }
}
