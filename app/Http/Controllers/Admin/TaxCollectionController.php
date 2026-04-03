<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyTaxBill;
use App\Models\Payment;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use App\Models\PropertyTaxAnnualBill;
use App\Models\Citizen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxCollectionController extends Controller
{
    public function index(Request $request)
    {
        $year    = $request->input('year', date('Y'));
        $month   = $request->input('month', date('n'));
        $status  = $request->input('status');
        $taxType = $request->input('tax_type');
        $search  = $request->input('search');

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
        if ($request->filled('demand_number')) {
            $waterQuery->whereHas('citizen', function ($q) use ($request) {
                $q->where('demand_number', $request->demand_number);
            });
        }

        $waterBills = $canViewWater ? $waterQuery->latest()->paginate(20, ['*'], 'water_page') : collect();

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
        if ($request->filled('demand_number')) {
            $propertyQuery->whereHas('citizen', function ($q) use ($request) {
                $q->where('demand_number', $request->demand_number);
            });
        }

        $propertyBills = $canViewProperty
            ? $propertyQuery->latest()->paginate(20, ['*'], 'property_page')
            : collect();

        return view('admin.tax-collection.index', compact(
            'waterBills', 'propertyBills',
            'year', 'month', 'fy',
            'canViewWater', 'canViewProperty'
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

        DB::transaction(function () use ($bill, $amount, $request, $isAnnual) {
            $bill->update([
                'paid_amount'    => $bill->paid_amount + $amount,
                'balance'        => $bill->balance - $amount,
                'status'         => ($bill->balance - $amount) <= 0 ? 'paid' : 'partial',
                'payment_method' => $request->payment_method,
                'paid_date'      => now(),
                'marked_by'      => auth('admin')->id(),
                'remarks'        => $request->remarks,
            ]);

            // Create Transaction Record
            Payment::create([
                'citizen_id'     => $bill->citizen_id,
                'tax_type'       => $isAnnual ? 'property_tax' : $bill->tax_type,
                'bill_id'        => $isAnnual ? null : $bill->id,
                'amount'         => $amount,
                'payment_method' => $request->payment_method,
                'status'         => 'completed',
                'paid_at'        => now(),
                'processed_by'   => auth('admin')->id(),
                'remarks'        => $request->remarks,
                'transaction_id' => 'OFF-' . strtoupper(uniqid()),
            ]);

            \App\Helpers\Logger::log(
                "Collected ₹{$amount} for " . ($isAnnual ? 'property_tax' : $bill->tax_type),
                $bill,
                'payment',
                ['amount' => $amount, 'method' => $request->payment_method]
            );

            // Update main record balance
            if ($isAnnual) {
                $record = PropertyTaxRecord::find($bill->record_id);
            } else {
                $record = WaterTaxRecord::find($bill->record_id);
            }

            if ($record) {
                $record->decrement('balance', $amount);
            }
        });

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
            foreach (MonthlyTaxBill::whereIn('id', $ids)->get() as $bill) {
                // simple logic to mark as paid
                $bill->status = 'paid';
                $bill->paid_amount = $bill->balance + $bill->paid_amount;
                $bill->balance = 0;
                $bill->save();
            }
            return back()->with('success', count($ids) . ' water bills marked as paid.');
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
            foreach (PropertyTaxAnnualBill::whereIn('id', $ids)->get() as $bill) {
                // simple logic to mark as paid
                $bill->status = 'paid';
                $bill->paid_amount = $bill->balance + $bill->paid_amount;
                $bill->balance = 0;
                $bill->save();
            }
            return back()->with('success', count($ids) . ' property bills marked as paid.');
        }

        if ($action === 'send_reminder') {
            // Placeholder for actual SMS/Email sending logic
            return back()->with('success', count($ids) . ' reminders sent (simulated).');
        }

        return back()->with('error', 'Invalid action selected.');
    }
}
