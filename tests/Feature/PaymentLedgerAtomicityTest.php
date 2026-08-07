<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use App\Models\TaxPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The payment callback credits a citizen's tax ledger. Getting it wrong means
 * either money paid that is still owed, or a bill cleared twice.
 */
class PaymentLedgerAtomicityTest extends TestCase
{
    use RefreshDatabase;

    private PropertyTaxRecord $record;
    private Citizen $citizen;
    private \App\Models\TaxType $taxType;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->taxType = \App\Models\TaxType::create([
            'name' => 'Property Tax',
            'slug' => 'property-tax',
            'is_active' => true,
        ]);

        $this->citizen = Citizen::create([
            'customer_no' => 'C-001',
            'name' => 'Asha',
            'phone' => '9425551234',
        ]);

        $this->record = PropertyTaxRecord::create([
            'a_no' => 1,
            'customer_no' => 'C-001',
            'property_no' => '3500/1',
            'property_type' => 'Default',
            'customer_name' => 'Asha',
            'balance' => 1000,
            'citizen_id' => $this->citizen->id,
        ]);
    }

    private function payment(array $overrides = []): TaxPayment
    {
        return TaxPayment::create(array_merge([
            'transaction_id' => 'TXN-TEST-1',
            'citizen_id' => $this->citizen->id,
            'citizen_name' => 'Asha',
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

    /**
     * Reach the protected method the callback funnels into.
     */
    private function applyPayment(TaxPayment $payment, string $status = 'COMPLETED'): void
    {
        $controller = app(\App\Http\Controllers\Citizen\PaymentController::class);

        $method = new \ReflectionMethod($controller, 'updatePaymentStatus');
        $method->setAccessible(true);
        $method->invoke($controller, $payment, $status, ['data' => ['state' => 'COMPLETED']], true);
    }

    public function test_a_confirmed_payment_reduces_the_balance_once(): void
    {
        $payment = $this->payment();

        $this->applyPayment($payment);

        $this->assertSame('600.00', (string) $this->record->fresh()->balance);
        $this->assertSame('completed', $payment->fresh()->payment_status);
    }

    public function test_a_repeated_callback_does_not_credit_the_payment_twice(): void
    {
        $payment = $this->payment();

        // Gateways retry, and the citizen's own status poll can arrive too.
        $this->applyPayment($payment);
        $this->applyPayment($payment->fresh());
        $this->applyPayment($payment->fresh());

        $this->assertSame(
            '600.00',
            (string) $this->record->fresh()->balance,
            'Balance must reflect a single 400 credit, not three.'
        );
    }

    public function test_the_ledger_guard_is_written_in_the_same_transaction_as_the_credit(): void
    {
        $payment = $this->payment();

        $this->applyPayment($payment);

        // If these two ever diverge, a retry re-credits the payment.
        $fresh = $payment->fresh();
        $this->assertNotEmpty($fresh->payment_data['ledger_updated_at'] ?? null);
        $this->assertSame('600.00', (string) $this->record->fresh()->balance);
    }

    public function test_a_failure_while_crediting_rolls_back_the_payment_status(): void
    {
        $payment = $this->payment();

        // Force the ledger write to blow up mid-transaction. Identifier quoting
        // differs between sqlite and MySQL, so match on the bare table name.
        DB::listen(function ($query) {
            if (str_starts_with(ltrim($query->sql), 'update')
                && str_contains($query->sql, 'property_tax_records')) {
                throw new \RuntimeException('simulated ledger failure');
            }
        });

        try {
            $this->applyPayment($payment);
        } catch (\Throwable $e) {
            // expected
        }

        $fresh = $payment->fresh();

        // The whole unit rolled back: no half-state where the payment reads as
        // completed but the citizen still owes the money.
        $this->assertNotSame('completed', $fresh->payment_status);
        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
    }

    public function test_a_pending_callback_leaves_the_balance_alone(): void
    {
        $payment = $this->payment();

        $this->applyPayment($payment, 'PENDING');

        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
    }

    public function test_a_failed_callback_leaves_the_balance_alone(): void
    {
        $payment = $this->payment();

        $this->applyPayment($payment, 'FAILED');

        $this->assertSame('1000.00', (string) $this->record->fresh()->balance);
        $this->assertSame('failed', $payment->fresh()->payment_status);
    }

    public function test_a_late_failure_callback_cannot_undo_a_completed_payment(): void
    {
        $payment = $this->payment();

        $this->applyPayment($payment);
        $this->applyPayment($payment->fresh(), 'FAILED');

        $fresh = $payment->fresh();
        $this->assertSame('completed', $fresh->payment_status);
        $this->assertSame('600.00', (string) $this->record->fresh()->balance);
    }

    public function test_the_confirmation_email_is_not_sent_when_the_credit_is_skipped(): void
    {
        $payment = $this->payment();

        $this->applyPayment($payment);
        Mail::fake();

        // Second callback is a no-op, so no duplicate receipt.
        $this->applyPayment($payment->fresh());

        Mail::assertNothingSent();
    }
}
