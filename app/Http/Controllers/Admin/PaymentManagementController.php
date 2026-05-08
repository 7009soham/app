<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['citizen', 'processor', 'bill']);

        // Permission Filtering
        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$user->isSuperAdmin()) {
            $canViewWater = $user->hasPermission('water_tax.view');
            $canViewProperty = $user->hasPermission('property_tax.view');

            if ($canViewWater && !$canViewProperty) {
                $query->where('tax_type', 'water_tax');
            } elseif (!$canViewWater && $canViewProperty) {
                $query->where('tax_type', 'property_tax');
            } elseif (!$canViewWater && !$canViewProperty) {
                abort(403, 'You do not have permission to view payments.');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('citizen', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('tax_type')) {
            $query->where('tax_type', $request->tax_type);
        }

        // Filter by Demand (schema uses citizens.demand_id)
        $demandId = $request->input('demand_id') ?? $request->input('demand_number');
        if (!empty($demandId)) {
            $query->whereHas('citizen', function ($q) use ($demandId) {
                $q->where('demand_id', $demandId);
            });
        }

        $payments = $query->latest('paid_at')->paginate(20)->withQueryString();

        $demands = \App\Models\Demand::all();

        return view('admin.payments.index', compact('payments', 'demands'));
    }

    public function show(Payment $payment)
    {
        return view('admin.payments.show', compact('payment'));
    }

    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No records selected.');
        }

        if ($action === 'delete') {
            Payment::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' payment records deleted successfully.');
        }

        if ($action === 'export') {
            $records = Payment::with('citizen')->whereIn('id', $ids)->orderBy('paid_at', 'desc')->get();
            $filename = 'payments_bulk_' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
            $callback = function () use ($records) {
                $file = fopen('php://output', 'w');
                fputcsv($file, [
                    'Transaction ID', 'Citizen Name', 'Tax Type', 'Amount', 'Status', 'Payment Method', 'Paid At'
                ]);
                foreach ($records as $record) {
                    fputcsv($file, [
                        $record->transaction_id,
                        $record->citizen->name ?? 'N/A',
                        $record->tax_type,
                        $record->amount,
                        $record->status,
                        $record->payment_method,
                        $record->paid_at
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Invalid action selected.');
    }
}
