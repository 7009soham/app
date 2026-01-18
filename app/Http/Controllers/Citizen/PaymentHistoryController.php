<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $citizen = Auth::guard('citizen')->user();
        
        $payments = Payment::where('citizen_id', $citizen->id)
            ->with(['bill'])
            ->latest('paid_at')
            ->paginate(15);

        return view('citizen.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        if ($payment->citizen_id !== Auth::guard('citizen')->id()) {
            abort(403);
        }
        return view('citizen.payments.show', compact('payment'));
    }
}
