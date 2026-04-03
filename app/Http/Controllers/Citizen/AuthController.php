<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\SiteSetting;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Normalize phone to plain 10-digit Indian mobile format.
     */
    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

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
        $phone = $this->normalizePhone($request->input('phone'));

        if (!preg_match('/^\d{10}$/', $phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 10-digit phone number.',
            ], 422);
        }

        $citizen = Citizen::where('phone', $phone)->first();

        $waterRecordQuery = WaterTaxRecord::where('phone', $phone)
            ->orWhere('phone', '+91' . $phone)
            ->orWhereRaw("REPLACE(REPLACE(phone, '+91', ''), ' ', '') = ?", [$phone]);

        if ($citizen) {
            $waterRecordQuery->orWhere('citizen_id', $citizen->id);
        }

        $propertyRecordQuery = PropertyTaxRecord::where('phone', $phone)
            ->orWhere('phone', '+91' . $phone)
            ->orWhereRaw("REPLACE(REPLACE(phone, '+91', ''), ' ', '') = ?", [$phone]);

        if ($citizen) {
            $propertyRecordQuery->orWhere('citizen_id', $citizen->id);
        }

        $waterRecord = $waterRecordQuery->first();
        $propertyRecord = $propertyRecordQuery->first();

        $firebaseEnabled = SiteSetting::get('firebase_enabled', '0') === '1';

        if (!$firebaseEnabled) {
            return response()->json([
                'success' => false,
                'message' => 'OTP service is temporarily unavailable. Please contact Gram Panchayat office.',
            ], 503);
        }

        if (!$citizen && !$waterRecord && !$propertyRecord) {
            return response()->json([
                'success' => false,
                'message' => 'No records found for this phone number. Please contact Gram Panchayat office.',
            ], 404);
        }

        if (!$citizen) {
            $citizen = Citizen::firstOrCreate(
                ['phone' => $phone],
                [
                    'customer_no' => $waterRecord->customer_no ?? $propertyRecord->customer_no,
                    'name' => $waterRecord->customer_name ?? $propertyRecord->customer_name,
                ]
            );
        }

        // Keep citizen-linked records synchronized with the verified login phone.
        if ($waterRecord) {
            WaterTaxRecord::where('id', $waterRecord->id)->update([
                'citizen_id' => $citizen->id,
                'phone' => $phone,
            ]);
            WaterTaxRecord::where('citizen_id', $citizen->id)->update(['phone' => $phone]);
        }
        if ($propertyRecord) {
            PropertyTaxRecord::where('id', $propertyRecord->id)->update([
                'citizen_id' => $citizen->id,
                'phone' => $phone,
            ]);
            PropertyTaxRecord::where('citizen_id', $citizen->id)->update(['phone' => $phone]);
        }

        // Store citizen_phone in session for OTP verification
        Session::put('citizen_phone_pending', $phone);

        return response()->json([
            'success' => true,
            'message' => 'Please verify your phone number using the OTP sent to your device.',
            'firebase_enabled' => $firebaseEnabled,
        ]);
    }

    /**
     * Verify OTP and login citizen
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|size:10',
            'firebase_id_token' => 'required|string',
        ]);

        $phone = $this->normalizePhone($request->phone);

        if (!preg_match('/^\d{10}$/', $phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number.',
            ], 422);
        }

        $citizen = Citizen::where('phone', $phone)->first();

        if (!$citizen) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number.',
            ], 404);
        }

        $firebaseEnabled = SiteSetting::get('firebase_enabled', '0') === '1';
        $firebaseApiKey = SiteSetting::get('firebase_api_key');

        if (!$firebaseEnabled || empty($firebaseApiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Firebase OTP is not configured. Please contact Gram Panchayat office.',
            ], 503);
        }

        try {
            $verifyResponse = Http::timeout(15)->post(
                'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . $firebaseApiKey,
                ['idToken' => $request->firebase_id_token]
            );

            if (!$verifyResponse->ok()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to verify OTP. Please try again.',
                ], 400);
            }

            $users = $verifyResponse->json('users', []);
            $firebasePhone = data_get($users, '0.phoneNumber');
            $normalizedFirebasePhone = $this->normalizePhone($firebasePhone);

            if (empty($normalizedFirebasePhone) || $normalizedFirebasePhone !== $phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP phone verification mismatch. Please try again.',
                ], 400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'OTP verification service is currently unavailable. Please try again.',
            ], 503);
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
