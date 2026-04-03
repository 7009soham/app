<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GmailController extends Controller
{
    /**
     * Save the citizen's email and dismiss the notification banner.
     */
    public function saveEmail(Request $request)
    {
        $citizen = Auth::guard('citizen')->user();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $request->validate([
            'email' => 'required|email|max:255|unique:citizens,email,' . $citizen->id,
        ]);

        $citizen->update([
            'email'            => $request->email,
            'banner_dismissed' => true,
        ]);

        return redirect()->route('citizen.dashboard')
            ->with('success', 'Your email has been saved. You will now receive invoices, due date reminders, and updates in your inbox.');
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
