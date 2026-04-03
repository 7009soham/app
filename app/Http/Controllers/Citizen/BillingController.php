<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxAnnualBill;
use App\Models\PropertyTaxRecord;
use App\Models\TaxPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * List water tax bills for the logged-in citizen
     */
    public function index()
    {
        $citizen = Auth::guard('citizen')->user();

        $bills = WaterTaxRecord::where('citizen_id', $citizen->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('citizen.billing.index', compact('bills'));
    }

    /**
     * Show water tax invoice
     */
    public function show($id)
    {
        $citizen = Auth::guard('citizen')->user();
        $bill = WaterTaxRecord::where('citizen_id', $citizen->id)
            ->where('id', $id)
            ->firstOrFail();

        $latestPayment = TaxPayment::where('citizen_id', $citizen->id)
            ->where('tax_type', 'water_tax')
            ->where('record_id', $bill->id)
            ->where('status', 'success')
            ->orderByDesc('paid_at')
            ->first();

        return view('citizen.billing.invoice', compact('bill', 'citizen', 'latestPayment'));
    }

    /**
     * Show property tax annual bill invoice
     */
    public function showPropertyInvoice($id)
    {
        $citizen = Auth::guard('citizen')->user();

        // Find the annual bill linked to this citizen's property records
        $bill = PropertyTaxAnnualBill::where('id', $id)
            ->where(function ($q) use ($citizen) {
                $q->where('citizen_id', $citizen->id)
                  ->orWhereHas('propertyTaxRecord', function ($rq) use ($citizen) {
                      $rq->where('citizen_id', $citizen->id)
                         ->orWhere('phone', $citizen->phone);
                  });
            })
            ->firstOrFail();

        $record = PropertyTaxRecord::find($bill->record_id);

        $latestPayment = TaxPayment::where('citizen_id', $citizen->id)
            ->where('tax_type', 'property_tax')
            ->where('record_id', $bill->record_id)
            ->where('status', 'success')
            ->orderByDesc('paid_at')
            ->first();

        return view('citizen.billing.property-invoice', compact('bill', 'citizen', 'record', 'latestPayment'));
    }
}
