<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the citizen login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('citizen')->check()) {
            return redirect()->route('citizen.dashboard');
        }
        
        return view('citizen.auth.login');
    }

    /**
     * Send OTP to citizen phone
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|size:10',
        ]);

        $phone = $request->phone;

        // Check if citizen exists (by phone in water or property tax records)
        $waterRecord = WaterTaxRecord::where('phone', $phone)->first();
        $propertyRecord = PropertyTaxRecord::where('phone', $phone)->first();

        if (!$waterRecord && !$propertyRecord) {
            return response()->json([
                'success' => false,
                'message' => 'No records found for this phone number. Please contact Gram Panchayat office.',
            ], 404);
        }

        // Get or create citizen
        $citizen = Citizen::firstOrCreate(
            ['phone' => $phone],
            [
                'customer_no' => $waterRecord->customer_no ?? $propertyRecord->customer_no,
                'name' => $waterRecord->customer_name ?? $propertyRecord->customer_name,
            ]
        );

        // Link citizen to their records
        if ($waterRecord && !$waterRecord->citizen_id) {
            WaterTaxRecord::where('phone', $phone)->update(['citizen_id' => $citizen->id]);
        }
        if ($propertyRecord && !$propertyRecord->citizen_id) {
            PropertyTaxRecord::where('phone', $phone)->update(['citizen_id' => $citizen->id]);
        }

        // Generate OTP (for Firebase fallback)
        $otp = $citizen->generateOtp();

        // Store citizen_phone in session for OTP verification
        Session::put('citizen_phone_pending', $phone);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully. Please verify using Firebase.',
            // In development, return OTP for testing (remove in production)
            'debug_otp' => config('app.debug') ? $otp : null,
        ]);
    }

    /**
     * Verify OTP and login citizen
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|size:10',
            'firebase_verified' => 'sometimes|boolean',
            'otp' => 'required_without:firebase_verified|string|size:6',
        ]);

        $phone = $request->phone;
        $citizen = Citizen::where('phone', $phone)->first();

        if (!$citizen) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number.',
            ], 404);
        }

        // If Firebase verified the OTP
        if ($request->firebase_verified) {
            $citizen->markPhoneAsVerified();
            Auth::guard('citizen')->login($citizen);
            Session::forget('citizen_phone_pending');

            return response()->json([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => route('citizen.dashboard'),
            ]);
        }

        // Fallback OTP verification (if Firebase fails)
        if (!$citizen->verifyOtp($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 400);
        }

        $citizen->markPhoneAsVerified();
        Auth::guard('citizen')->login($citizen);
        Session::forget('citizen_phone_pending');

        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'redirect' => route('citizen.dashboard'),
        ]);
    }

    /**
     * Logout citizen
     */
    public function logout(Request $request)
    {
        Auth::guard('citizen')->logout();
        Session::forget('citizen_phone_pending');

        return redirect()->route('citizen.login')->with('success', 'Logged out successfully.');
    }
}
