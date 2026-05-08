<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\TaxPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $citizen = Auth::guard('citizen')->user();
        
        $payments = TaxPayment::where('citizen_id', $citizen->id)
            ->with(['taxType'])
            ->latest()
            ->paginate(15);

        return view('citizen.payments.index', compact('payments'));
    }

    public function show(TaxPayment $payment)
    {
        if ((string) $payment->citizen_id !== (string) Auth::guard('citizen')->id()) {
            abort(403);
        }
        return view('citizen.payments.show', compact('payment'));
    }
}
