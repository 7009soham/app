<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyTaxBill;
use App\Models\Payment;
use App\Models\TaxPayment;
use App\Models\TaxType;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use App\Models\PropertyTaxAnnualBill;
use App\Models\Citizen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TaxCollectionController extends Controller
{
    private static array $taxPaymentsColumnCache = [];

    public function index(Request $request)
    {
        $year    = $request->input('year', date('Y'));
        $month   = $request->input('month', date('n'));
        $status  = $request->input('status');
        $taxType = $request->input('tax_type');
        $search  = $request->input('search');

        $demands = \App\Models\Demand::all();
        $demandId = $request->input('demand_id') ?? $request->input('demand_number');

        // ── Water Tax: monthly bills ──────────────────────────────────────────
        $waterQuery = MonthlyTaxBill::query()
            ->with(['citizen'])
            ->where('tax_type', 'water_tax')
            ->where('bill_year', $year)
            ->where('bill_month', $month);

        // Enforce Permissions
        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $canViewWater    = $user->isSuperAdmin() || $user->hasPermission('water_tax.view');
        $canViewProperty = $user->isSuperAdmin() || $user->hasPermission('property_tax.view');

        if (!$canViewWater && !$canViewProperty) {
            abort(403, 'Unauthorized');
        }

        if ($status) {
            $waterQuery->where('status', $status);
        }
        if ($search) {
            $waterQuery->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_no', 'like', "%{$search}%");
            });
        }
        if (!empty($demandId)) {
            $waterQuery->whereHas('citizen', function ($q) use ($demandId) {
                $q->where('demand_id', $demandId);
            });
        }

        $waterBills = $canViewWater
            ? $waterQuery->latest()->paginate(20, ['*'], 'water_page')->withQueryString()
            : collect();

        // Add penalty info to water bills
        foreach ($waterBills as $bill) {
            $penaltyData = \App\Models\PenaltySetting::calculatePenalty($bill);
            $bill->penalty_amount    = $penaltyData['penalty_amount'];
            $bill->days_overdue      = $penaltyData['days_overdue'];
            $bill->penalty_percentage = $penaltyData['penalty_percentage'];
            $bill->total_with_penalty = $bill->balance + $penaltyData['penalty_amount'];
        }

        // ── Property Tax: annual bills ────────────────────────────────────────
        // Use financial year derived from the selected calendar year
        $fy = PropertyTaxAnnualBill::currentFinancialYear();
        $propertyQuery = PropertyTaxAnnualBill::query()
            ->with(['citizen', 'propertyTaxRecord'])
            ->where('financial_year', $fy);

        if ($status) {
            $propertyQuery->where('status', $status);
        }
        if ($search) {
            $propertyQuery->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_no', 'like', "%{$search}%");
            });
        }
        if (!empty($demandId)) {
            $propertyQuery->whereHas('citizen', function ($q) use ($demandId) {
                $q->where('demand_id', $demandId);
            });
        }

        $propertyBills = $canViewProperty
            ? $propertyQuery->latest()->paginate(20, ['*'], 'property_page')->withQueryString()
            : collect();

        return view('admin.tax-collection.index', compact(
            'waterBills', 'propertyBills',
            'year', 'month', 'fy',
            'canViewWater', 'canViewProperty',
            'demands'
        ));
    }

    // ─── Water Tax Bill Generation (monthly) ─────────────────────────────────

    public function generateMonthlyBills()
    {
        return view('admin.tax-collection.generate');
    }

    public function storeMonthlyBills(Request $request)
    {
        $year  = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));

        // Check if water tax bills already exist for this month
        $exists = MonthlyTaxBill::where('tax_type', 'water_tax')
            ->where('bill_year', $year)
            ->where('bill_month', $month)
            ->exists();

        if ($exists) {
            return back()->with('error', "Water tax bills for {$month}/{$year} already exist.");
        }

        DB::transaction(function () use ($year, $month) {
            WaterTaxRecord::chunk(100, function ($records) use ($year, $month) {
                foreach ($records as $record) {
                    MonthlyTaxBill::create([
                        'citizen_id'    => $record->citizen_id ?? null,
                        'tax_type'      => 'water_tax',
                        'record_id'     => $record->id,
                        'customer_no'   => $record->customer_no,
                        'customer_name' => $record->customer_name,
                        'bill_year'     => $year,
                        'bill_month'    => $month,
                        'bill_amount'   => $record->monthly_bill,
                        'balance'       => $record->monthly_bill,
                        'status'        => 'pending',
                        'due_date'      => Carbon::create($year, $month, 15)->addMonth(),
                    ]);
                }
            });
        });

        return redirect()->route('admin.tax-collection.index')
            ->with('success', 'Water tax monthly bills generated successfully.');
    }

    // ─── Property Tax Bill Generation (annual) ────────────────────────────────

    public function generateAnnualPropertyBills(Request $request)
    {
        $fy    = PropertyTaxAnnualBill::currentFinancialYear();
        $start = PropertyTaxAnnualBill::financialYearStart($fy);
        $end   = PropertyTaxAnnualBill::financialYearEnd($fy);

        // Prevent duplicate generation for the same financial year
        $alreadyExists = PropertyTaxAnnualBill::where('financial_year', $fy)->exists();
        if ($alreadyExists) {
            return back()->with('error', "Property tax annual bills for FY {$fy} have already been generated.");
        }

        $count = 0;

        DB::transaction(function () use ($fy, $start, $end, &$count) {
            PropertyTaxRecord::chunk(100, function ($records) use ($fy, $start, $end, &$count) {
                foreach ($records as $record) {
                    $currentTax      = $record->current_total ?? 0;
                    $previousBalance = $record->balance ?? 0;
                    
                    // The total amount this bill covers is current year + any previous arrears
                    $totalBillAmount = $currentTax + $previousBalance;

                    // Auto-mark as paid if no amount due at all
                    $autoStatus = ($totalBillAmount <= 0) ? 'paid' : 'pending';

                    PropertyTaxAnnualBill::create([
                        'citizen_id'        => $record->citizen_id ?? null,
                        'record_id'         => $record->id,
                        'customer_no'       => $record->customer_no,
                        'customer_name'     => $record->customer_name,
                        'financial_year'    => $fy,
                        'bill_period_start' => $start->toDateString(),
                        'bill_period_end'   => $end->toDateString(),
                        'house_tax'         => $record->current_house_tax ?? 0,
                        'electricity_tax'   => $record->current_electricity_tax ?? 0,
                        'health_tax'        => $record->current_health_tax ?? 0,
                        'previous_balance'  => $previousBalance,
                        'bill_amount'       => $currentTax,
                        'balance'           => $totalBillAmount, // Track total balance due here
                        'status'            => $autoStatus,
                        'due_date'          => $start->copy()->addMonths(3),
                        'bill_no'           => 'PT-' . $fy . '-' . str_pad($record->id, 5, '0', STR_PAD_LEFT),
                    ]);

                    // IMPORTANT: Update the master record's balance to include the current year's tax
                    $record->increment('balance', $currentTax);

                    $count++;
                }
            });
        });

        return redirect()->route('admin.tax-collection.index')
            ->with('success', "Property tax annual bills for FY {$fy} generated successfully. Total: {$count} bills.");
    }

    // ─── Mark as Paid ─────────────────────────────────────────────────────────

    public function markAsPaid(Request $request, $billId)
    {
        // Check if this is a PropertyTaxAnnualBill or MonthlyTaxBill
        $bill = MonthlyTaxBill::find($billId);
        $isAnnual = false;
        if (!$bill) {
            $bill = PropertyTaxAnnualBill::findOrFail($billId);
            $isAnnual = true;
        }

        $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . $bill->balance,
            'payment_method' => 'required|in:cash,online,cheque,bank_transfer',
            'remarks'        => 'nullable|string',
        ]);

        $amount = $request->amount;
        $transactionId = 'OFF-' . strtoupper(uniqid());
        $attempt = $this->createAdminTaxPaymentAttempt(
            $bill,
            (float) $amount,
            $isAnnual,
            $request->payment_method,
            $request->remarks,
            $transactionId
        );

        try {
            DB::transaction(function () use ($bill, $amount, $request, $isAnnual, $transactionId) {
                $this->applyBillCollection(
                    $bill,
                    (float) $amount,
                    $request->payment_method,
                    $request->remarks,
                    $isAnnual,
                    $transactionId
                );
            });

            $this->finalizeAdminTaxPaymentAttempt($attempt, 'success');
        } catch (\Throwable $e) {
            [$failureCode, $failureReason] = $this->mapFailure($e->getMessage());
            $this->finalizeAdminTaxPaymentAttempt($attempt, 'failed', $failureCode, $failureReason);

            return back()->with('error', $failureReason);
        }

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function markAnnualPropertyBillAsPaid(Request $request, PropertyTaxAnnualBill $bill)
    {
        return $this->markAsPaid($request, $bill->id);
    }

    public function updateStatus(Request $request, MonthlyTaxBill $bill)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,partial,overdue',
        ]);

        $bill->update(['status' => $request->status]);

        return back()->with('success', 'Status updated successfully.');
    }

    // ─── Debug Fast-Forward (water tax ONLY) ─────────────────────────────────

    public function debugFastForward()
    {
        if (!auth('admin')->user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admin can use Time Travel.');
        }

        $latest = MonthlyTaxBill::where('tax_type', 'water_tax')
            ->orderBy('bill_year', 'desc')
            ->orderBy('bill_month', 'desc')
            ->first();

        if ($latest) {
            $year  = $latest->bill_year;
            $month = $latest->bill_month;
            if ($month == 12) { $month = 1; $year++; }
            else { $month++; }
        } else {
            $year  = date('Y');
            $month = date('n');
        }

        $exists = MonthlyTaxBill::where('tax_type', 'water_tax')
            ->where('bill_year', $year)
            ->where('bill_month', $month)
            ->exists();

        if ($exists) {
            return back()->with('error', "Water tax bills for {$month}/{$year} already exist.");
        }

        DB::transaction(function () use ($year, $month) {
            WaterTaxRecord::chunk(100, function ($records) use ($year, $month) {
                foreach ($records as $record) {
                    MonthlyTaxBill::create([
                        'citizen_id'    => $record->citizen_id ?? null,
                        'tax_type'      => 'water_tax',
                        'record_id'     => $record->id,
                        'customer_no'   => $record->customer_no,
                        'customer_name' => $record->customer_name,
                        'bill_year'     => $year,
                        'bill_month'    => $month,
                        'bill_amount'   => $record->monthly_bill,
                        'balance'       => $record->monthly_bill,
                        'status'        => 'pending',
                        'due_date'      => Carbon::create($year, $month, 15)->addMonth(),
                    ]);
                    $record->increment('balance', $record->monthly_bill);
                }
            });
        });

        $monthName = Carbon::create($year, $month, 1)->format('F Y');
        return redirect()->route('admin.tax-collection.index')
            ->with('success', "TIME TRAVEL: Fast-forwarded water tax to {$monthName}.");
    }

    public function bulkWater(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No records selected.');
        }

        if ($action === 'mark_paid') {
            $processed = 0;
            $failed = 0;
            $skipped = 0;

            foreach (MonthlyTaxBill::whereIn('id', $ids)->get() as $bill) {
                $amount = (float) $bill->balance;
                if ($amount <= 0) {
                    $skipped++;
                    continue;
                }

                $transactionId = 'OFF-' . strtoupper(uniqid());
                $attempt = $this->createAdminTaxPaymentAttempt(
                    $bill,
                    $amount,
                    false,
                    'cash',
                    'Bulk mark paid by admin',
                    $transactionId
                );

                try {
                    DB::transaction(function () use ($bill, $amount, $transactionId) {
                        $this->applyBillCollection(
                            $bill,
                            $amount,
                            'cash',
                            'Bulk mark paid by admin',
                            false,
                            $transactionId
                        );
                    });

                    $this->finalizeAdminTaxPaymentAttempt($attempt, 'success');
                    $processed++;
                } catch (\Throwable $e) {
                    [$failureCode, $failureReason] = $this->mapFailure($e->getMessage());
                    $this->finalizeAdminTaxPaymentAttempt($attempt, 'failed', $failureCode, $failureReason);
                    $failed++;
                }
            }

            $message = $processed . ' water bills marked as paid.';
            if ($skipped > 0) {
                $message .= ' ' . $skipped . ' already-settled bills skipped.';
            }
            if ($failed > 0) {
                $message .= ' ' . $failed . ' bill(s) failed.';
            }

            return back()->with($failed > 0 ? 'warning' : 'success', $message);
        }

        if ($action === 'send_reminder') {
            // Placeholder for actual SMS/Email sending logic
            return back()->with('success', count($ids) . ' reminders sent (simulated).');
        }

        return back()->with('error', 'Invalid action selected.');
    }

    public function bulkProperty(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No records selected.');
        }

        if ($action === 'mark_paid') {
            $processed = 0;
            $failed = 0;
            $skipped = 0;

            foreach (PropertyTaxAnnualBill::whereIn('id', $ids)->get() as $bill) {
                $amount = (float) $bill->balance;
                if ($amount <= 0) {
                    $skipped++;
                    continue;
                }

                $transactionId = 'OFF-' . strtoupper(uniqid());
                $attempt = $this->createAdminTaxPaymentAttempt(
                    $bill,
                    $amount,
                    true,
                    'cash',
                    'Bulk mark paid by admin',
                    $transactionId
                );

                try {
                    DB::transaction(function () use ($bill, $amount, $transactionId) {
                        $this->applyBillCollection(
                            $bill,
                            $amount,
                            'cash',
                            'Bulk mark paid by admin',
                            true,
                            $transactionId
                        );
                    });

                    $this->finalizeAdminTaxPaymentAttempt($attempt, 'success');
                    $processed++;
                } catch (\Throwable $e) {
                    [$failureCode, $failureReason] = $this->mapFailure($e->getMessage());
                    $this->finalizeAdminTaxPaymentAttempt($attempt, 'failed', $failureCode, $failureReason);
                    $failed++;
                }
            }

            $message = $processed . ' property bills marked as paid.';
            if ($skipped > 0) {
                $message .= ' ' . $skipped . ' already-settled bills skipped.';
            }
            if ($failed > 0) {
                $message .= ' ' . $failed . ' bill(s) failed.';
            }

            return back()->with($failed > 0 ? 'warning' : 'success', $message);
        }

        if ($action === 'send_reminder') {
            // Placeholder for actual SMS/Email sending logic
            return back()->with('success', count($ids) . ' reminders sent (simulated).');
        }

        return back()->with('error', 'Invalid action selected.');
    }

    private function applyBillCollection(
        MonthlyTaxBill|PropertyTaxAnnualBill $bill,
        float $amount,
        string $paymentMethod,
        ?string $remarks,
        bool $isAnnual,
        string $transactionId
    ): void {
        $remainingBalance = max(0, (float) $bill->balance - $amount);

        $bill->update([
            'paid_amount'    => (float) $bill->paid_amount + $amount,
            'balance'        => $remainingBalance,
            'status'         => $remainingBalance <= 0 ? 'paid' : 'partial',
            'payment_method' => $paymentMethod,
            'paid_date'      => now(),
            'marked_by'      => auth('admin')->id(),
            'remarks'        => $remarks,
        ]);

        Payment::firstOrCreate(
            ['transaction_id' => $transactionId],
            [
                'citizen_id'     => $bill->citizen_id,
                'tax_type'       => $isAnnual ? 'property_tax' : $bill->tax_type,
                'bill_id'        => $isAnnual ? null : $bill->id,
                'amount'         => $amount,
                'payment_method' => $paymentMethod,
                'status'         => 'completed',
                'paid_at'        => now(),
                'processed_by'   => auth('admin')->id(),
                'remarks'        => $remarks,
            ]
        );

        \App\Helpers\Logger::log(
            "Collected ₹{$amount} for " . ($isAnnual ? 'property_tax' : $bill->tax_type),
            $bill,
            'payment',
            ['amount' => $amount, 'method' => $paymentMethod]
        );

        $record = $isAnnual
            ? PropertyTaxRecord::find($bill->record_id)
            : WaterTaxRecord::find($bill->record_id);

        if ($record) {
            $record->decrement('balance', $amount);
        }
    }

    private function createAdminTaxPaymentAttempt(
        MonthlyTaxBill|PropertyTaxAnnualBill $bill,
        float $amount,
        bool $isAnnual,
        string $paymentMethod,
        ?string $remarks,
        string $transactionId
    ): ?TaxPayment {
        $taxTypeModel = TaxType::query()
            ->where('slug', $isAnnual ? 'property-tax' : 'water-tax')
            ->orWhere('slug', $isAnnual ? 'property_tax' : 'water_tax')
            ->first();

        if (!$taxTypeModel) {
            Log::warning('Tax type not configured for admin payment attempt.', [
                'is_annual' => $isAnnual,
                'bill_id' => $bill->id,
            ]);
            return null;
        }

        [$periodStart, $periodEnd] = $this->resolvePeriodRange($bill, $isAnnual);
        $citizen = $bill->relationLoaded('citizen')
            ? $bill->citizen
            : ($bill->citizen_id ? Citizen::find($bill->citizen_id) : null);

        $attemptCreateData = [
            'citizen_id'      => $bill->citizen_id,
            'citizen_name'    => $bill->customer_name ?? ($citizen->name ?? 'Citizen'),
            'citizen_phone'   => $citizen->phone ?? '-',
            'citizen_address' => $citizen->address ?? '-',
            'tax_type'        => $isAnnual ? 'property_tax' : 'water_tax',
            'tax_type_id'     => $taxTypeModel->id,
            'record_id'       => $bill->record_id,
            'transaction_id'  => $transactionId,
            'amount'          => $amount,
            'period_type'     => $isAnnual ? 'yearly' : 'monthly',
            'period_start'    => $periodStart,
            'period_end'      => $periodEnd,
            'payment_method'  => $paymentMethod,
            'payment_status'  => 'pending',
            'payment_data'    => [
                'source' => 'admin_collection',
                'is_annual' => $isAnnual,
                'admin_id' => auth('admin')->id(),
                'remarks' => $remarks,
                'initiated_at' => now()->toDateTimeString(),
            ],
        ];

        if ($this->hasTaxPaymentsColumn('status')) {
            $attemptCreateData['status'] = 'pending';
        }

        if ($this->hasTaxPaymentsColumn('failure_reason')) {
            $attemptCreateData['failure_reason'] = null;
        }

        return TaxPayment::create($attemptCreateData);
    }

    private function finalizeAdminTaxPaymentAttempt(
        ?TaxPayment $attempt,
        string $status,
        ?string $failureCode = null,
        ?string $failureReason = null
    ): void {
        if (!$attempt) {
            return;
        }

        if ($status === 'success') {
            $attemptUpdateData = [
                'payment_status' => 'completed',
                'paid_at' => now(),
                'payment_data' => array_merge($attempt->payment_data ?? [], [
                    'finalized_at' => now()->toDateTimeString(),
                ]),
            ];

            if ($this->hasTaxPaymentsColumn('status')) {
                $attemptUpdateData['status'] = 'success';
            }

            if ($this->hasTaxPaymentsColumn('failure_reason')) {
                $attemptUpdateData['failure_reason'] = null;
            }

            $attempt->update($attemptUpdateData);
            return;
        }

        $attemptUpdateData = [
            'payment_status' => 'failed',
            'payment_data' => array_merge($attempt->payment_data ?? [], [
                'failure_code' => $failureCode,
                'failure_reason' => $failureReason,
                'finalized_at' => now()->toDateTimeString(),
            ]),
        ];

        if ($this->hasTaxPaymentsColumn('status')) {
            $attemptUpdateData['status'] = 'failed';
        }

        if ($this->hasTaxPaymentsColumn('failure_reason')) {
            $attemptUpdateData['failure_reason'] = $failureReason;
        }

        $attempt->update($attemptUpdateData);
    }

    private function resolvePeriodRange(MonthlyTaxBill|PropertyTaxAnnualBill $bill, bool $isAnnual): array
    {
        if (!$isAnnual) {
            $start = Carbon::create((int) $bill->bill_year, (int) $bill->bill_month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();
            return [$start, $end];
        }

        $financialYear = (string) ($bill->financial_year ?? '');
        $parts = explode('-', $financialYear);
        $startYear = isset($parts[0]) ? (int) trim($parts[0]) : ((int) date('Y') - 1);

        $start = Carbon::create($startYear, 4, 1)->startOfDay();
        $end = Carbon::create($startYear + 1, 3, 31)->endOfDay();

        return [$start, $end];
    }

    private function mapFailure(string $errorMessage): array
    {
        $haystack = strtoupper($errorMessage);

        if (str_contains($haystack, 'CANCEL')) {
            return ['USER_CANCELLED', 'Payment cancelled by user'];
        }

        if (str_contains($haystack, 'TIMEOUT') || str_contains($haystack, 'TIMED OUT')) {
            return ['TIMEOUT', 'Payment gateway timeout'];
        }

        if (
            str_contains($haystack, 'NETWORK')
            || str_contains($haystack, 'CONNECTION')
            || str_contains($haystack, 'UNAVAILABLE')
        ) {
            return ['NETWORK', 'Network issue, please retry'];
        }

        if (
            str_contains($haystack, 'VERIFY')
            || str_contains($haystack, 'CHECKSUM')
            || str_contains($haystack, 'INVALID')
            || str_contains($haystack, 'MISMATCH')
        ) {
            return ['VERIFY_FAIL', 'Payment verification failed'];
        }

        return ['UNKNOWN', 'Technical issue, please try again'];
    }

    private function hasTaxPaymentsColumn(string $column): bool
    {
        if (!array_key_exists($column, self::$taxPaymentsColumnCache)) {
            self::$taxPaymentsColumnCache[$column] = Schema::hasColumn('tax_payments', $column);
        }

        return self::$taxPaymentsColumnCache[$column];
    }
}
