<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\MonthlyTaxBill;
use App\Models\PropertyTaxAnnualBill;
use App\Models\PropertyTaxRecord;
use App\Models\TaxPayment;
use App\Models\TaxType;
use App\Models\WaterTaxRecord;
use App\Services\BillResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * A citizen paid Rs 2.06 of property tax, the gateway reported success, and the
 * bill still read Pending. The office was told "bill maddhe add nai zala".
 *
 * The cause was that a payment never recorded which bill it settled. Settlement
 * re-derived it from the clock - this month for water, this financial year for
 * property - and the update was wrapped in `if ($bill)`, so any mismatch took
 * the money, moved the master record, and left the bill untouched in silence.
 *
 * These tests pin the behaviour that has to hold on a government ledger:
 * the right bill is settled, both sides of every balance move, nothing is
 * credited twice, and a payment is never invisible.
 */
class BillSettlementTest extends TestCase
{
    use RefreshDatabase;

    private Citizen $citizen;
    private TaxType $propertyType;
    private TaxType $waterType;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->propertyType = TaxType::firstOrCreate(
            ['slug' => 'property-tax'],
            ['name' => 'Property Tax', 'is_active' => true]
        );
        $this->waterType = TaxType::firstOrCreate(
            ['slug' => 'water-tax'],
            ['name' => 'Water Tax', 'is_active' => true]
        );

        $this->citizen = Citizen::create([
            'customer_no' => 'C-100',
            'name' => 'Asha Patil',
            'phone' => '9425551234',
        ]);
    }

    private function settle(TaxPayment $payment): void
    {
        $controller = app(\App\Http\Controllers\Citizen\PaymentController::class);
        $method = new \ReflectionMethod($controller, 'updatePaymentStatus');
        $method->setAccessible(true);
        $method->invoke($controller, $payment, 'COMPLETED', ['data' => ['state' => 'COMPLETED']], true);
    }

    private function propertyRecord(float $balance = 1000): PropertyTaxRecord
    {
        return PropertyTaxRecord::create([
            'a_no' => 1,
            'customer_no' => 'C-100',
            'property_no' => '3500/1',
            'property_type' => 'Default',
            'customer_name' => 'Asha Patil',
            'balance' => $balance,
            'citizen_id' => $this->citizen->id,
        ]);
    }

    private function waterRecord(float $balance = 500): WaterTaxRecord
    {
        return WaterTaxRecord::create([
            'a_no' => 1,
            'customer_no' => 'C-100',
            'customer_name' => 'Asha Patil',
            'monthly_bill' => 100,
            'balance' => $balance,
            'citizen_id' => $this->citizen->id,
        ]);
    }

    private function annualBill(PropertyTaxRecord $record, string $fy, float $amount): PropertyTaxAnnualBill
    {
        [$start] = explode('-', $fy);

        return PropertyTaxAnnualBill::create([
            'citizen_id' => $this->citizen->id,
            'record_id' => $record->id,
            'customer_no' => 'C-100',
            'customer_name' => 'Asha Patil',
            'financial_year' => $fy,
            'bill_period_start' => $start . '-04-01',
            'bill_period_end' => ((int) $start + 1) . '-03-31',
            'bill_amount' => $amount,
            'paid_amount' => 0,
            'balance' => $amount,
            'status' => 'pending',
        ]);
    }

    private function monthlyBill(WaterTaxRecord $record, int $year, int $month, float $amount): MonthlyTaxBill
    {
        return MonthlyTaxBill::create([
            'citizen_id' => $this->citizen->id,
            'tax_type' => 'water_tax',
            'record_id' => $record->id,
            'customer_no' => 'C-100',
            'customer_name' => 'Asha Patil',
            'bill_year' => $year,
            'bill_month' => $month,
            'bill_amount' => $amount,
            'paid_amount' => 0,
            'balance' => $amount,
            'status' => 'pending',
            'due_date' => sprintf('%04d-%02d-28', $year, $month),
        ]);
    }

    private function payment(array $overrides): TaxPayment
    {
        return TaxPayment::create(array_merge([
            'transaction_id' => 'TXN-' . uniqid(),
            'citizen_id' => $this->citizen->id,
            'citizen_name' => 'Asha Patil',
            'citizen_phone' => '9425551234',
            'period_type' => 'yearly',
            'period_start' => now()->startOfYear()->toDateString(),
            'period_end' => now()->endOfYear()->toDateString(),
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'payu',
        ], $overrides));
    }

    // ---------------------------------------------------------------- property

    public function test_a_property_payment_settles_the_pinned_bill(): void
    {
        $record = $this->propertyRecord(1000);
        $bill = $this->annualBill($record, PropertyTaxAnnualBill::currentFinancialYear(), 1000);

        $this->settle($this->payment([
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->propertyType->id,
            'record_id' => $record->id,
            'bill_id' => $bill->id,
            'bill_type' => BillResolver::PROPERTY,
            'amount' => 400,
            'payment_data' => ['tax_amount' => 400, 'convenience_fee' => 0],
        ]));

        $bill->refresh();
        $this->assertSame('400.00', (string) $bill->paid_amount);
        // The old code never decremented this, so the outstanding stayed at the
        // full bill amount for ever.
        $this->assertSame('600.00', (string) $bill->balance);
        $this->assertSame('partial', $bill->status);
        $this->assertSame('600.00', (string) $record->fresh()->balance);
    }

    /**
     * The exact regression. A bill from a previous financial year could never be
     * settled, because the lookup was pinned to currentFinancialYear().
     */
    public function test_a_payment_against_an_older_financial_year_still_settles(): void
    {
        $record = $this->propertyRecord(1000);
        $oldBill = $this->annualBill($record, '2019-20', 1000);

        $this->settle($this->payment([
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->propertyType->id,
            'record_id' => $record->id,
            'bill_id' => $oldBill->id,
            'bill_type' => BillResolver::PROPERTY,
            'amount' => 1000,
            'payment_data' => ['tax_amount' => 1000, 'convenience_fee' => 0],
        ]));

        $oldBill->refresh();
        $this->assertSame('paid', $oldBill->status, 'An arrears bill must be settleable.');
        $this->assertSame('0.00', (string) $oldBill->balance);
    }

    public function test_a_property_payment_with_no_bill_still_credits_the_record(): void
    {
        // The live state: 3,320 property records and zero annual bills, because
        // bill generation is a manual admin action nobody had run.
        $record = $this->propertyRecord(1000);

        $this->settle($this->payment([
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->propertyType->id,
            'record_id' => $record->id,
            'amount' => 250,
            'payment_data' => ['tax_amount' => 250, 'convenience_fee' => 0],
        ]));

        $this->assertSame('750.00', (string) $record->fresh()->balance);
    }

    // ------------------------------------------------------------------- water

    public function test_a_water_payment_moves_both_the_bill_and_the_record(): void
    {
        $record = $this->waterRecord(500);
        $bill = $this->monthlyBill($record, (int) date('Y'), (int) date('n'), 500);

        $this->settle($this->payment([
            'tax_type' => 'water_tax',
            'tax_type_id' => $this->waterType->id,
            'record_id' => $record->id,
            'bill_id' => $bill->id,
            'bill_type' => BillResolver::WATER,
            'amount' => 200,
            'period_type' => 'monthly',
            'payment_data' => ['tax_amount' => 200, 'convenience_fee' => 0],
        ]));

        $bill->refresh();
        $record->refresh();

        $this->assertSame('200.00', (string) $bill->paid_amount);
        $this->assertSame('300.00', (string) $bill->balance);
        $this->assertSame('200.00', (string) $record->amount_paid);
        // hasPendingBalance() reads balance, and this was never decremented, so
        // a fully paid water record reported Pending for ever.
        $this->assertSame('300.00', (string) $record->balance);
    }

    public function test_a_water_payment_for_a_previous_month_still_settles(): void
    {
        $record = $this->waterRecord(500);
        $lastMonth = now()->subMonths(3);
        $bill = $this->monthlyBill($record, (int) $lastMonth->format('Y'), (int) $lastMonth->format('n'), 500);

        $this->settle($this->payment([
            'tax_type' => 'water_tax',
            'tax_type_id' => $this->waterType->id,
            'record_id' => $record->id,
            'bill_id' => $bill->id,
            'bill_type' => BillResolver::WATER,
            'amount' => 500,
            'period_type' => 'monthly',
            'payment_data' => ['tax_amount' => 500, 'convenience_fee' => 0],
        ]));

        $this->assertSame('paid', $bill->fresh()->status);
        $this->assertSame('0.00', (string) $record->fresh()->balance);
    }

    public function test_a_fully_paid_water_record_no_longer_reports_pending(): void
    {
        $record = $this->waterRecord(300);
        $bill = $this->monthlyBill($record, (int) date('Y'), (int) date('n'), 300);

        $this->settle($this->payment([
            'tax_type' => 'water_tax',
            'tax_type_id' => $this->waterType->id,
            'record_id' => $record->id,
            'bill_id' => $bill->id,
            'bill_type' => BillResolver::WATER,
            'amount' => 300,
            'period_type' => 'monthly',
            'payment_data' => ['tax_amount' => 300, 'convenience_fee' => 0],
        ]));

        $this->assertFalse($record->fresh()->hasPendingBalance());
    }

    // ------------------------------------------------------------- correctness

    public function test_a_repeated_callback_does_not_credit_the_bill_twice(): void
    {
        $record = $this->propertyRecord(1000);
        $bill = $this->annualBill($record, PropertyTaxAnnualBill::currentFinancialYear(), 1000);

        $payment = $this->payment([
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->propertyType->id,
            'record_id' => $record->id,
            'bill_id' => $bill->id,
            'bill_type' => BillResolver::PROPERTY,
            'amount' => 400,
            'payment_data' => ['tax_amount' => 400, 'convenience_fee' => 0],
        ]);

        // Gateways retry, and the citizen's own status poll arrives too.
        $this->settle($payment);
        $this->settle($payment->fresh());
        $this->settle($payment->fresh());

        $this->assertSame('400.00', (string) $bill->fresh()->paid_amount, 'One credit, not three.');
        $this->assertSame('600.00', (string) $bill->fresh()->balance);
    }

    public function test_an_overpayment_cannot_drive_a_balance_negative(): void
    {
        $record = $this->propertyRecord(100);
        $bill = $this->annualBill($record, PropertyTaxAnnualBill::currentFinancialYear(), 100);

        $this->settle($this->payment([
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->propertyType->id,
            'record_id' => $record->id,
            'bill_id' => $bill->id,
            'bill_type' => BillResolver::PROPERTY,
            'amount' => 500,
            'payment_data' => ['tax_amount' => 500, 'convenience_fee' => 0],
        ]));

        $this->assertSame('0.00', (string) $bill->fresh()->balance);
        $this->assertSame('0.00', (string) $record->fresh()->balance);
        $this->assertSame('paid', $bill->fresh()->status);
    }

    public function test_the_convenience_fee_is_not_credited_against_the_bill(): void
    {
        $record = $this->propertyRecord(1000);
        $bill = $this->annualBill($record, PropertyTaxAnnualBill::currentFinancialYear(), 1000);

        $this->settle($this->payment([
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->propertyType->id,
            'record_id' => $record->id,
            'bill_id' => $bill->id,
            'bill_type' => BillResolver::PROPERTY,
            'amount' => 412,
            'payment_data' => ['tax_amount' => 400, 'convenience_fee' => 12],
        ]));

        $this->assertSame('400.00', (string) $bill->fresh()->paid_amount, 'Only the tax portion clears the bill.');
    }

    public function test_the_receipt_names_the_gateway_actually_used(): void
    {
        $record = $this->propertyRecord(1000);

        $payment = $this->payment([
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->propertyType->id,
            'record_id' => $record->id,
            'amount' => 100,
            'payment_method' => 'payu',
            'payment_data' => ['tax_amount' => 100, 'convenience_fee' => 0],
        ]);

        $this->settle($payment);

        $receipt = \App\Models\Payment::where('transaction_id', $payment->transaction_id)->first();
        $this->assertNotNull($receipt);
        $this->assertStringContainsString('PayU', $receipt->remarks);
        $this->assertStringNotContainsString('PhonePe', $receipt->remarks, 'Receipts named PhonePe for every gateway.');
    }

    // ----------------------------------------------------------- bill resolver

    public function test_the_resolver_picks_the_oldest_open_bill(): void
    {
        $record = $this->propertyRecord(3000);
        $older = $this->annualBill($record, '2019-20', 1000);
        $this->annualBill($record, '2024-25', 1000);

        $resolved = BillResolver::outstandingFor('property', $record->id);

        $this->assertNotNull($resolved);
        $this->assertSame($older->id, $resolved->id, 'Arrears clear oldest first.');
    }

    public function test_the_resolver_skips_bills_that_are_already_paid(): void
    {
        $record = $this->propertyRecord(1000);
        $paid = $this->annualBill($record, '2019-20', 1000);
        $paid->update(['status' => 'paid', 'balance' => 0, 'paid_amount' => 1000]);
        $open = $this->annualBill($record, '2024-25', 1000);

        $this->assertSame($open->id, BillResolver::outstandingFor('property', $record->id)->id);
    }

    public function test_the_resolver_returns_null_when_no_bills_exist(): void
    {
        $record = $this->propertyRecord(1000);

        $this->assertNull(BillResolver::outstandingFor('property', $record->id));
    }

    // ------------------------------------------------------------ billing page

    public function test_the_billing_page_lists_both_taxes(): void
    {
        $property = $this->propertyRecord(1000);
        $fy = PropertyTaxAnnualBill::currentFinancialYear();
        $propertyBill = $this->annualBill($property, $fy, 1000);

        $water = $this->waterRecord(500);
        $waterBill = $this->monthlyBill($water, (int) date('Y'), (int) date('n'), 500);

        $response = $this->actingAs($this->citizen, 'citizen')->get(route('citizen.billing.index'))->assertOk();

        // Asserting on the tax NAME is useless here: the citizen sidebar already
        // links to "Property Tax" and "Water Tax", so it matches whether or not
        // the bill is on the page. These are unique to an actual bill row.
        $response->assertSee(route('citizen.billing.property-invoice', $propertyBill->id), false);
        $response->assertSee(route('citizen.billing.invoice', $waterBill->record_id), false);
        $response->assertSee($fy, false);
    }

    /**
     * The page printed `bill_no` and `period` straight off the imported master
     * row, which rendered as "#-" and "23".
     */
    public function test_the_billing_page_does_not_print_raw_import_fields(): void
    {
        $water = $this->waterRecord(500);
        $water->update(['period' => '23', 'bill_no' => null]);
        $this->monthlyBill($water, (int) date('Y'), (int) date('n'), 500);

        $response = $this->actingAs($this->citizen, 'citizen')->get(route('citizen.billing.index'))->assertOk();

        $response->assertDontSee('#-', false);
        // A real month and year, not the free-text import column.
        $response->assertSee(now()->translatedFormat('F Y'), false);
    }

    public function test_a_citizen_with_payments_but_no_bills_still_sees_the_payment(): void
    {
        $record = $this->propertyRecord(1000);

        $payment = $this->payment([
            'tax_type' => 'property_tax',
            'tax_type_id' => $this->propertyType->id,
            'record_id' => $record->id,
            'amount' => 2.06,
            'payment_data' => ['tax_amount' => 2.06, 'convenience_fee' => 0],
        ]);
        $this->settle($payment);

        $this->actingAs($this->citizen, 'citizen')
            ->get(route('citizen.billing.index'))
            ->assertOk()
            // An empty page here is what read as "my payment vanished".
            ->assertSee($payment->transaction_id, false)
            ->assertSee(__('messages.payments_received'), false);
    }
}
