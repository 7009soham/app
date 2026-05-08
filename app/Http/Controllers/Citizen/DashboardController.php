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

        // Count only confirmed successful payments (do not count cancelled/failed/back attempts)
        $totalPaymentsMade = TaxPayment::where('citizen_phone', $citizen->phone)
            ->completed()
            ->count();

        // Calculate totals
        $totalWaterTaxBalance = $waterTaxRecords->sum('balance');
        $totalPropertyTaxBalance = $propertyTaxRecords->sum('balance');
        $totalBalance = $totalWaterTaxBalance + $totalPropertyTaxBalance;

        return view('citizen.dashboard', compact(
            'citizen',
            'waterTaxRecords',
            'propertyTaxRecords',
            'totalPaymentsMade',
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
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'remove_email' => 'nullable|in:0,1',
        ]);

        $name = trim((string) $request->input('name', $citizen->name));
        if ($name === '') {
            $name = $citizen->name;
        }

        $address = trim((string) $request->input('address', ''));
        $address = $address !== '' ? $address : null;

        $citizen->update([
            'name' => $name,
            'address' => $address,
        ]);

        $currentEmail = $citizen->email !== null ? strtolower(trim((string) $citizen->email)) : '';
        $requestedEmail = strtolower(trim((string) $request->input('email', '')));
        $shouldRemoveEmail = (string) $request->input('remove_email', '0') === '1';

        if ($shouldRemoveEmail && $currentEmail !== '') {
            $citizen->update([
                'email' => null,
                'email_verified_at' => null,
                'otp' => null,
                'otp_expires_at' => null,
                'otp_sent_at' => null,
            ]);

            Session::forget('profile_email_change_request');
            Session::forget('profile_email_otp_operation');
            Session::forget('show_profile_email_otp');
            Session::forget('profile_pending_email');

            return redirect()->route('citizen.profile')
                ->with('success', 'Email removed successfully.');
        }

        $emailOperation = null;
        $targetEmail = null;

        if ($requestedEmail !== '' && $requestedEmail !== $currentEmail) {
            $emailOperation = 'set';
            $targetEmail = $requestedEmail;
        }

        if ($emailOperation !== null && $targetEmail !== null) {
            Session::put('profile_email_change_request', [
                'citizen_id' => (int) $citizen->id,
                'operation' => $emailOperation,
                'email' => $targetEmail,
            ]);

            $message = 'Please verify OTP sent to your new email before saving it.';

            return redirect()->route('citizen.profile')
                ->with('warning', $message)
                ->with('show_profile_email_otp', true)
                ->with('profile_pending_email', $targetEmail)
                ->with('profile_email_otp_operation', $emailOperation);
        }

        Session::forget('profile_email_change_request');

        return redirect()->route('citizen.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
