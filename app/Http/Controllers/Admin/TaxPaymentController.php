<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxPayment;
use App\Models\TaxType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxPaymentController extends Controller
{
    /**
     * Display a listing of all tax payments
     */
    public function index(Request $request)
    {
        $query = TaxPayment::with('taxType');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        // Filter by tax type
        if ($request->filled('tax_type')) {
            $query->where('tax_type_id', $request->tax_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by name, phone, or transaction ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('citizen_name', 'like', "%{$search}%")
                  ->orWhere('citizen_phone', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        // Get summary stats
        $stats = [
            'total_amount' => TaxPayment::completed()->sum('amount'),
            'total_transactions' => TaxPayment::completed()->count(),
            'pending_count' => TaxPayment::pending()->count(),
            'failed_count' => TaxPayment::failed()->count(),
        ];

        // Get tax types for filter dropdown
        $taxTypes = TaxType::where('is_active', true)->get();

        // Paginate results
        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.tax-payments.index', compact('payments', 'stats', 'taxTypes'));
    }

    /**
     * Display the specified tax payment
     */
    public function show(TaxPayment $taxPayment)
    {
        $taxPayment->load('taxType');
        return view('admin.tax-payments.show', compact('taxPayment'));
    }

    /**
     * Export tax payments to CSV
     */
    public function export(Request $request)
    {
        $query = TaxPayment::with('taxType')->completed();

        // Apply same filters as index
        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('paid_at', 'desc')->get();

        $filename = 'tax_payments_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payments) {
            $handle = fopen('php://output', 'w');
            
            // Header row
            fputcsv($handle, [
                'Transaction ID',
                'Citizen Name',
                'Phone',
                'Address',
                'Tax Type',
                'Period Type',
                'Amount',
                'Payment Status',
                'Payment Method',
                'Paid At',
            ]);

            // Data rows
            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->transaction_id,
                    $payment->citizen_name,
                    $payment->citizen_phone,
                    $payment->citizen_address,
                    $payment->taxType->name ?? '-',
                    ucfirst($payment->period_type ?? '-'),
                    $payment->amount,
                    ucfirst($payment->payment_status),
                    $payment->payment_method ?? '-',
                    $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
