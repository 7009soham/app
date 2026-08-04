<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Failed attempts allowed before the account/IP pair is locked out. */
    private const MAX_ATTEMPTS = 5;

    /** Lockout duration in seconds once MAX_ATTEMPTS is reached. */
    private const LOCKOUT_SECONDS = 900;

    /**
     * Throttle key for a login attempt.
     *
     * Keyed on email *and* IP so one attacker cannot lock a real administrator
     * out of their own account from an unrelated address.
     */
    private function throttleKey(Request $request): string
    {
        return 'admin-login:' . Str::lower((string) $request->input('email')) . '|' . $request->ip();
    }

    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $throttleKey = $this->throttleKey($request);

        // This panel controls every citizen's tax record, so failed attempts are
        // capped before the password is ever checked.
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            \App\Helpers\Logger::log(
                "Admin login blocked by rate limit for {$request->input('email')} from {$request->ip()}",
                null,
                'auth'
            );

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        if (!$admin->is_active) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);
            return back()->withErrors(['email' => 'Your account has been deactivated'])->withInput();
        }

        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            RateLimiter::clear($throttleKey);

            $admin->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            \App\Helpers\Logger::log("Admin logged in", $admin, 'auth');

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

        \App\Helpers\Logger::log(
            "Failed admin login for {$request->input('email')} from {$request->ip()}",
            null,
            'auth'
        );

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            \App\Helpers\Logger::log("Admin logged out", Auth::guard('admin')->user(), 'auth');
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }
}
