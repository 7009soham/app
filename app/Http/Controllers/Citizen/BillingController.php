<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\MonthlyTaxBill;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxAnnualBill;
use App\Models\PropertyTaxRecord;
use App\Models\TaxPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * Every bill the citizen has, for both taxes.
     *
     * This used to list WaterTaxRecord, which is the master ledger row rather
     * than a bill, so the page printed raw import fields: bill_no is null on
     * imported rows and rendered as "#-", and period is a free-text column that
     * rendered as "23". Property tax never appeared at all, which is why a
     * successful property payment looked to the citizen like it had gone
     * nowhere.
     *
     * Both bill tables are normalised into one shape here so the view stays
     * simple and the two taxes cannot drift apart again.
     */
    public function index()
    {
        $citizen = Auth::guard('citizen')->user();

        $water = MonthlyTaxBill::where('citizen_id', $citizen->id)
            ->where('tax_type', 'water_tax')
            ->orderByDesc('bill_year')
            ->orderByDesc('bill_month')
            ->get()
            ->map(fn (MonthlyTaxBill $b) => [
                'id' => $b->id,
                'tax' => __('messages.water_tax'),
                'tax_key' => 'water',
                'bill_no' => $b->id,
                'period' => Carbon::create((int) $b->bill_year, (int) $b->bill_month, 1)->translatedFormat('F Y'),
                'amount' => (float) $b->bill_amount,
                'paid' => (float) $b->paid_amount,
                'balance' => (float) $b->balance,
                'status' => $b->status,
                'due_date' => $b->due_date,
                'paid_date' => $b->paid_date,
                'invoice_url' => route('citizen.billing.invoice', $b->record_id),
                'sort' => sprintf('%04d%02d', $b->bill_year, $b->bill_month),
            ]);

        $property = PropertyTaxAnnualBill::where(function ($q) use ($citizen) {
                $q->where('citizen_id', $citizen->id)
                  ->orWhereHas('propertyTaxRecord', function ($rq) use ($citizen) {
                      $rq->where('citizen_id', $citizen->id);
                  });
            })
            ->orderByDesc('bill_period_start')
            ->get()
            ->map(fn (PropertyTaxAnnualBill $b) => [
                'id' => $b->id,
                'tax' => __('messages.property_tax'),
                'tax_key' => 'property',
                'bill_no' => $b->bill_no ?: $b->id,
                'period' => $b->financial_year,
                'amount' => (float) $b->bill_amount,
                'paid' => (float) $b->paid_amount,
                'balance' => (float) $b->balance,
                'status' => $b->status,
                'due_date' => $b->due_date,
                'paid_date' => $b->paid_date,
                'invoice_url' => route('citizen.billing.property-invoice', $b->id),
                'sort' => (string) ($b->bill_period_start?->format('Ymd') ?? '0'),
            ]);

        $bills = $water->concat($property)->sortByDesc('sort')->values();

        // The office generates bills manually, so a citizen can have real
        // payments and no bills at all. Showing an empty page in that case
        // reads as "your payment vanished", which is exactly the complaint
        // this page caused. Surface the receipts instead.
        $payments = TaxPayment::where('citizen_id', $citizen->id)
            ->where('status', 'success')
            ->orderByDesc('paid_at')
            ->get();

        return view('citizen.billing.index', compact('bills', 'payments'));
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
