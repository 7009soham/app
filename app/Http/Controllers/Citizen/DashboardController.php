<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use App\Models\TaxPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get the authenticated citizen
     */
    private function getCitizen()
    {
        return Auth::guard('citizen')->user();
    }

    /**
     * Display the citizen dashboard
     */
    public function index()
    {
        $citizen = $this->getCitizen();
        
        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        // Get water tax records
        $waterTaxRecords = WaterTaxRecord::where('citizen_id', $citizen->id)
            ->orWhere('phone', $citizen->phone)
            ->orderBy('a_no')
            ->get();

        // Get property tax records
        $propertyTaxRecords = PropertyTaxRecord::where('citizen_id', $citizen->id)
            ->orWhere('phone', $citizen->phone)
            ->orderBy('a_no')
            ->get();

        // Get payment history
        $paymentHistory = TaxPayment::where('citizen_phone', $citizen->phone)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Calculate totals
        $totalWaterTaxBalance = $waterTaxRecords->sum('balance');
        $totalPropertyTaxBalance = $propertyTaxRecords->sum('balance');
        $totalBalance = $totalWaterTaxBalance + $totalPropertyTaxBalance;

        return view('citizen.dashboard', compact(
            'citizen',
            'waterTaxRecords',
            'propertyTaxRecords',
            'paymentHistory',
            'totalWaterTaxBalance',
            'totalPropertyTaxBalance',
            'totalBalance'
        ));
    }

    /**
     * Show water tax details
     */
    public function waterTax()
    {
        $citizen = $this->getCitizen();
        
        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $waterTaxRecords = WaterTaxRecord::where('citizen_id', $citizen->id)
            ->orWhere('phone', $citizen->phone)
            ->orderBy('a_no')
            ->get();

        return view('citizen.water-tax', compact('citizen', 'waterTaxRecords'));
    }

    /**
     * Show property tax details
     */
    public function propertyTax()
    {
        $citizen = $this->getCitizen();
        
        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $propertyTaxRecords = PropertyTaxRecord::where('citizen_id', $citizen->id)
            ->orWhere('phone', $citizen->phone)
            ->orderBy('a_no')
            ->get();

        return view('citizen.property-tax', compact('citizen', 'propertyTaxRecords'));
    }

    /**
     * Show payment history
     */
    public function paymentHistory()
    {
        $citizen = $this->getCitizen();
        
        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $payments = TaxPayment::where('citizen_phone', $citizen->phone)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('citizen.payment-history', compact('citizen', 'payments'));
    }

    /**
     * Show pay bill form
     */
    public function payBill(Request $request)
    {
        $citizen = $this->getCitizen();
        
        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $taxType = $request->get('type', 'water'); // water or property
        $recordId = $request->get('record_id');

        if ($taxType === 'water') {
            $record = WaterTaxRecord::find($recordId);
        } else {
            $record = PropertyTaxRecord::find($recordId);
        }

        return view('citizen.pay-bill', compact('citizen', 'taxType', 'record'));
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'tax_type' => 'required|in:water,property',
            'record_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
        ]);

        $citizen = $this->getCitizen();
        
        if (!$citizen) {
            return response()->json(['success' => false, 'message' => 'Please login first.'], 401);
        }

        // TODO: Integrate with PhonePe or another payment gateway
        // For now, return success with payment initiation details

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway integration pending. Please contact Gram Panchayat office for payment.',
        ]);
    }

    /**
     * Show citizen profile
     */
    public function profile()
    {
        $citizen = $this->getCitizen();
        
        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        return view('citizen.profile', compact('citizen'));
    }

    /**
     * Update citizen profile
     */
    public function updateProfile(Request $request)
    {
        $citizen = $this->getCitizen();
        
        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $citizen->update([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return redirect()->route('citizen.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
