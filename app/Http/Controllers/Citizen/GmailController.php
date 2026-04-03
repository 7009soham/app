<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailController extends Controller
{
    /**
     * Redirect the citizen to Google's OAuth consent screen.
     */
    public function redirectToGoogle()
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');

        if (!$clientId) {
            return redirect()->route('citizen.dashboard')
                ->with('error', 'Google OAuth is not configured. Please contact the administrator.');
        }

        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => url($redirectUri),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => csrf_token(),
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    /**
     * Handle the Google OAuth callback and store tokens.
     */
    public function handleGoogleCallback(Request $request)
    {
        $citizen = Auth::guard('citizen')->user();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        if ($request->has('error')) {
            return redirect()->route('citizen.dashboard')
                ->with('error', 'Gmail connection was cancelled or denied.');
        }

        $code  = $request->get('code');
        $state = $request->get('state');

        if (!$code) {
            return redirect()->route('citizen.dashboard')
                ->with('error', 'Invalid OAuth response from Google.');
        }

        try {
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code'          => $code,
                'client_id'     => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri'  => url(config('services.google.redirect')),
                'grant_type'    => 'authorization_code',
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('Gmail OAuth token exchange failed', [
                    'citizen_id' => $citizen->id,
                    'response'   => $tokenResponse->body(),
                ]);

                return redirect()->route('citizen.dashboard')
                    ->with('error', 'Failed to connect Gmail. Please try again.');
            }

            $tokenData = $tokenResponse->json();

            $citizen->update([
                'is_gmail_connected'  => true,
                'gmail_access_token'  => $tokenData['access_token'] ?? null,
                'gmail_refresh_token' => $tokenData['refresh_token'] ?? null,
                'banner_dismissed'    => true,
            ]);

            return redirect()->route('citizen.dashboard')
                ->with('success', 'Gmail connected successfully! You will now receive invoices and reminders in your inbox.');
        } catch (\Exception $e) {
            Log::error('Gmail OAuth callback exception', [
                'citizen_id' => $citizen->id,
                'error'      => $e->getMessage(),
            ]);

            return redirect()->route('citizen.dashboard')
                ->with('error', 'An error occurred while connecting Gmail. Please try again.');
        }
    }

    /**
     * Dismiss the Gmail connection banner for this session.
     */
    public function dismissBanner(Request $request)
    {
        $citizen = Auth::guard('citizen')->user();

        if ($citizen) {
            $citizen->update(['banner_dismissed' => true]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }
}
