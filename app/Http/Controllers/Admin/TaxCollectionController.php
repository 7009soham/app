<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyTaxBill;
use App\Models\Payment;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use App\Models\Citizen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxCollectionController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));
        $status = $request->input('status');
        $taxType = $request->input('tax_type');
        $search = $request->input('search');

        $query = MonthlyTaxBill::query()
            ->with(['citizen'])
            ->where('bill_year', $year)
            ->where('bill_month', $month);

        if ($status) {
            $query->where('status', $status);
        }

        if ($taxType) {
            $query->where('tax_type', $taxType);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_no', 'like', "%{$search}%");
            });
        }

        $bills = $query->latest()->paginate(20);

        return view('admin.tax-collection.index', compact('bills', 'year', 'month'));
    }

    public function generateMonthlyBills()
    {
        // This method calculates bills for the current month for all active records
        // Ideally this should be a job, but for now we'll do it synchronously or via command
        // For demonstration, let's just show a view to confirm generation
        return view('admin.tax-collection.generate');
    }

    public function storeMonthlyBills(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));
        
        // Check if bills already exist for this month
        $exists = MonthlyTaxBill::where('bill_year', $year)->where('bill_month', $month)->exists();
        if ($exists) {
            return back()->with('error', "Bills for {$month}/{$year} already exist.");
        }

        DB::transaction(function() use ($year, $month) {
            // Process Water Tax Records
            // Assuming WaterTaxRecord has a 'monthly_bill' field
            WaterTaxRecord::chunk(100, function($records) use ($year, $month) {
                foreach ($records as $record) {
                    MonthlyTaxBill::create([
                        'citizen_id' => $record->citizen_id ?? 0, // Fallback if not linked, though strictly should be
                        'tax_type' => 'water_tax',
                        'record_id' => $record->id,
                        'customer_no' => $record->customer_no,
                        'customer_name' => $record->customer_name,
                        'bill_year' => $year,
                        'bill_month' => $month,
                        'bill_amount' => $record->monthly_bill,
                        'balance' => $record->monthly_bill, // Initially balance = bill amount
                        'status' => 'pending',
                        'due_date' => Carbon::create($year, $month, 15)->addMonth(), // Due date logic
                    ]);
                }
            });

            // Process Property Tax Records
             PropertyTaxRecord::chunk(100, function($records) use ($year, $month) {
                foreach ($records as $record) {
                    MonthlyTaxBill::create([
                        'citizen_id' => $record->citizen_id ?? 0,
                        'tax_type' => 'property_tax',
                        'record_id' => $record->id,
                        'customer_no' => $record->customer_no,
                        'customer_name' => $record->customer_name,
                        'bill_year' => $year,
                        'bill_month' => $month,
                        'bill_amount' => $record->monthly_bill,
                        'balance' => $record->monthly_bill,
                        'status' => 'pending',
                        'due_date' => Carbon::create($year, $month, 15)->addMonth(),
                    ]);
                }
            });
        });

        return redirect()->route('admin.tax-collection.index')->with('success', 'Monthly bills generated successfully. Total generated.');
    }

    public function markAsPaid(Request $request, MonthlyTaxBill $bill)
    {
        // Permission check can be done via middleware or gate
        // if (!auth('admin')->user()->can('mark_tax_paid')) { abort(403); }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $bill->balance,
            'payment_method' => 'required|in:cash,online,cheque,bank_transfer',
            'remarks' => 'nullable|string',
        ]);

        $amount = $request->amount;
        
        DB::transaction(function() use ($bill, $amount, $request) {
            $bill->update([
                'paid_amount' => $bill->paid_amount + $amount,
                'balance' => $bill->balance - $amount,
                'status' => ($bill->balance - $amount) <= 0 ? 'paid' : 'partial',
                'payment_method' => $request->payment_method,
                'paid_date' => now(),
                'marked_by' => auth('admin')->id(),
                'remarks' => $request->remarks,
            ]);

            // Create Transaction Record
            Payment::create([
                'citizen_id' => $bill->citizen_id,
                'tax_type' => $bill->tax_type,
                'bill_id' => $bill->id,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'paid_at' => now(),
                'processed_by' => auth('admin')->id(),
                'remarks' => $request->remarks,
                'transaction_id' => 'OFF-' . strtoupper(uniqid()),
            ]);

            // Also update the main Tax Record Balance if it tracks total outstanding
            if ($bill->tax_type == 'water_tax') {
                $record = WaterTaxRecord::find($bill->record_id);
                if ($record) {
                    $record->decrement('balance', $amount);
                    $record->increment('amount_paid', $amount);
                }
            } elseif ($bill->tax_type == 'property_tax') {
                $record = PropertyTaxRecord::find($bill->record_id);
                if ($record) {
                    $record->decrement('balance', $amount);
                    $record->increment('amount_paid', $amount);
                }
            }
        });

        return back()->with('success', 'Payment recorded successfully.');
    }
    public function updateStatus(Request $request, MonthlyTaxBill $bill)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,partial,overdue',
        ]);

        $bill->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status updated successfully.');
    }
}
