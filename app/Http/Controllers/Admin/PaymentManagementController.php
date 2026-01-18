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

        $payments = $query->latest('paid_at')->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        return view('admin.payments.show', compact('payment'));
    }
}
