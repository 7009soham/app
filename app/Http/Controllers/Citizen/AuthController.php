<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Mail\CitizenEmailOtpMail;
use App\Models\Citizen;
use App\Models\SiteSetting;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

    private function isEmailVerificationEnabled(): bool
    {
        $value = strtolower(trim((string) SiteSetting::get('email_verification_enabled', '1')));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function citizenHasColumn(string $column): bool
    {
        static $citizenColumns = null;

        if ($citizenColumns === null) {
            try {
                $citizenColumns = array_flip(Schema::getColumnListing('citizens'));
            } catch (\Throwable $e) {
                $citizenColumns = [];
            }
        }

        return isset($citizenColumns[$column]);
    }

    private function safeCitizenUpdate(Citizen $citizen, array $attributes): void
    {
        $filtered = [];

        foreach ($attributes as $column => $value) {
            if ($this->citizenHasColumn($column)) {
                $filtered[$column] = $value;
            }
        }

        if (!empty($filtered)) {
            $citizen->update($filtered);
        }
    }

    private function linkEmailToCitizen(Citizen $citizen, string $email): void
    {
        DB::transaction(function () use ($citizen, $email): void {
            $lockedCitizen = Citizen::whereKey($citizen->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingOwner = Citizen::whereRaw('LOWER(email) = ?', [$email])
                ->where('id', '!=', $lockedCitizen->id)
                ->lockForUpdate()
                ->first();

            if ($existingOwner) {
                $this->safeCitizenUpdate($existingOwner, [
                    'email' => null,
                    'email_verified_at' => null,
                    'otp' => null,
                    'otp_expires_at' => null,
                    'otp_sent_at' => null,
                ]);
            }

            $this->safeCitizenUpdate($lockedCitizen, [
                'email' => $email,
                'email_verified_at' => now(),
                'otp' => null,
                'otp_expires_at' => null,
                'otp_sent_at' => null,
            ]);
        });
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
     * Send OTP to citizen email (independent from phone/Firebase flow)
     */
    public function sendEmailOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);
        $citizen = Auth::guard('citizen')->user();

        if (!$citizen) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to verify your email.',
            ], 401);
        }

        $pendingProfileRequest = Session::get('profile_email_change_request');
        $hasPendingProfileRequest =
            is_array($pendingProfileRequest)
            && (int) data_get($pendingProfileRequest, 'citizen_id', 0) === (int) $citizen->id
            && (string) data_get($pendingProfileRequest, 'operation', '') === 'set'
            && trim((string) data_get($pendingProfileRequest, 'email', '')) !== '';

        if ($hasPendingProfileRequest) {
            $email = strtolower(trim((string) data_get($pendingProfileRequest, 'email')));
        } else {
            $email = strtolower(trim((string) ($validated['email'] ?? '')));
            if ($email === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid email address.',
                ], 422);
            }
        }

        $emailOwner = Citizen::whereRaw('LOWER(email) = ?', [$email])->first();
        $emailExistsForAnotherCitizen = $emailOwner && (int) $emailOwner->id !== (int) $citizen->id;

        $sendRateKey = 'citizen-email-otp-send:' . $citizen->id . ':' . sha1($request->ip());
        if (RateLimiter::tooManyAttempts($sendRateKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests. Please wait and try again.',
                'seconds_remaining' => RateLimiter::availableIn($sendRateKey),
            ], 429);
        }

        Session::put('citizen_email_otp_pending', [
            'citizen_id' => (int) $citizen->id,
            'email' => $email,
        ]);
        Session::put('show_profile_email_otp', true);
        Session::put('profile_pending_email', $email);
        Session::put('profile_email_otp_operation', 'set');

        if (!$this->isEmailVerificationEnabled()) {
            $this->linkEmailToCitizen($citizen, $email);

            Session::forget('citizen_email_otp_pending');
            Session::forget('show_profile_email_otp');
            Session::forget('profile_pending_email');
            Session::forget('profile_email_otp_operation');
            Session::forget('profile_email_change_request');

            return response()->json([
                'success' => true,
                'message' => 'Email verification is disabled by admin. Email marked verified.',
            ]);
        }

        $cooldownEndsAt = $citizen->otp_sent_at ? $citizen->otp_sent_at->copy()->addSeconds(30) : null;
        if ($cooldownEndsAt && $cooldownEndsAt->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another OTP.',
                'seconds_remaining' => now()->diffInSeconds($cooldownEndsAt),
            ], 429);
        }

        $otp = $citizen->generateOtp();
        $this->safeCitizenUpdate($citizen, ['otp_sent_at' => now()]);
        RateLimiter::hit($sendRateKey, 600);
        RateLimiter::clear('citizen-email-otp-attempt:' . $citizen->id . ':' . sha1($email));

        try {
            Mail::to($email)->send(new CitizenEmailOtpMail([
                'citizen_name' => $citizen->name,
                'otp' => $otp,
                'expires_in_minutes' => 10,
            ]));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP email right now. Please try again.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => $emailExistsForAnotherCitizen
                ? 'Email already exists. Please verify to continue'
                : 'OTP has been sent to your email address.',
        ]);
    }

    /**
     * Verify OTP for citizen email (independent from phone/Firebase flow)
     */
    public function verifyEmailOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
        ]);

        $citizen = Auth::guard('citizen')->user();

        if (!$citizen) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to verify your email.',
            ], 401);
        }

        $pending = Session::get('citizen_email_otp_pending');
        $pendingEmail = strtolower(trim((string) data_get($pending, 'email', '')));
        $pendingCitizenId = (int) data_get($pending, 'citizen_id', 0);
        $requestedEmail = strtolower(trim((string) ($validated['email'] ?? '')));

        if ($requestedEmail !== '' && $requestedEmail !== $pendingEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Verification email mismatch. Please request a new OTP.',
            ], 422);
        }

        if ($pendingCitizenId !== (int) $citizen->id || $pendingEmail === '') {
            return response()->json([
                'success' => false,
                'message' => 'Verification session expired. Please request a new OTP.',
            ], 422);
        }

        $email = $pendingEmail;

        if (!$this->isEmailVerificationEnabled()) {
            $this->linkEmailToCitizen($citizen, $email);

            Session::forget('citizen_email_otp_pending');
            Session::forget('show_profile_email_otp');
            Session::forget('profile_pending_email');
            Session::forget('profile_email_otp_operation');
            Session::forget('profile_email_change_request');

            return response()->json([
                'success' => true,
                'message' => 'Email verification is disabled by admin. Email marked verified.',
            ]);
        }

        $otpValidated = $request->validate([
            'otp' => ['required', 'string', 'regex:/^\d{4,6}$/'],
        ]);
        $otp = trim((string) $otpValidated['otp']);

        $attemptKey = 'citizen-email-otp-attempt:' . $citizen->id . ':' . sha1($email);
        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many invalid OTP attempts. Please request a new OTP.',
                'seconds_remaining' => RateLimiter::availableIn($attemptKey),
            ], 429);
        }

        if (!$citizen->verifyOtp($otp)) {
            $isExpired = $citizen->otp_expires_at && $citizen->otp_expires_at->isPast();
            RateLimiter::hit($attemptKey, 600);

            return response()->json([
                'success' => false,
                'message' => $isExpired ? 'OTP has expired. Please request a new OTP.' : 'Invalid OTP.',
            ], 422);
        }

        try {
            $this->linkEmailToCitizen($citizen, $email);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to link email right now. Please try again.',
            ], 500);
        }

        Session::forget('citizen_email_otp_pending');
        Session::forget('show_profile_email_otp');
        Session::forget('profile_pending_email');
        Session::forget('profile_email_otp_operation');
        Session::forget('profile_email_change_request');
        RateLimiter::clear($attemptKey);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ]);
    }

    /**
     * Logout citizen
     */
    public function logout(Request $request)
    {
        Auth::guard('citizen')->logout();
        Session::forget('citizen_phone_pending');
        Session::forget('citizen_email_otp_pending');
        Session::forget('show_profile_email_otp');
        Session::forget('profile_pending_email');
        Session::forget('profile_email_otp_operation');
        Session::forget('profile_email_change_request');

        return redirect()->route('citizen.login')->with('success', 'Logged out successfully.');
    }
}
