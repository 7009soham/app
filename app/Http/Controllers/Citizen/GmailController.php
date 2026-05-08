<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GmailController extends Controller
{
    /**
    * Save the citizen's email and mark Gmail as linked.
     */
    public function saveEmail(Request $request)
    {
        $citizen = Auth::guard('citizen')->user();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        return redirect()->route('citizen.profile')
            ->with('error', 'Direct email linking is disabled. Please verify your email using OTP on the profile page.');
    }

    /**
     * Dismiss the notification banner without providing an email.
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
