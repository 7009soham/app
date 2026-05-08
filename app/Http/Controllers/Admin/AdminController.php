<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display a listing of admins
     */
    public function index()
    {
        $admins = Admin::with('role')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $currentAdmin = Auth::guard('admin')->user();
        $currentAdminId = $currentAdmin ? $currentAdmin->id : null;
        $originalAdminId = Session::get('original_admin_id');
        
        return view('admin.admins.index', compact('admins', 'currentAdminId', 'originalAdminId'));
    }

    /**
     * Show the form for creating a new admin
     */
    public function create()
    {
        $roles = Role::query()
            ->whereIn('type', ['admin', 'superadmin'])
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('admin.admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request)
    {
        $currentAdmin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:15',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $role = Role::find($validated['role_id']);
        if (!$role || !in_array($role->type, ['admin', 'superadmin'], true)) {
            return redirect()->back()->withInput()->with('error', 'Please select a valid Admin or Super Admin role.');
        }

        if ($role->type === 'superadmin' && (!$currentAdmin || !$currentAdmin->isSuperAdmin())) {
            return redirect()->back()->withInput()->with('error', 'Only Super Admin can create another Super Admin.');
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Admin::create($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    /**
     * Show the form for editing an admin
     */
    public function edit(Admin $admin)
    {
        $currentAdmin = Auth::guard('admin')->user();

        if (!$currentAdmin) {
            return redirect()->route('admin.login')->with('error', 'Session expired.');
        }

        // Prevent editing super admin unless you're a super admin
        if ($admin->isSuperAdmin() && !$currentAdmin->isSuperAdmin()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot edit a Super Admin.');
        }

        $roles = Role::query()
            ->whereIn('type', ['admin', 'superadmin'])
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified admin
     */
    public function update(Request $request, Admin $admin)
    {
        $currentAdmin = Auth::guard('admin')->user();

        if (!$currentAdmin) {
            return redirect()->route('admin.login')->with('error', 'Session expired.');
        }

        if ($admin->isSuperAdmin() && !$currentAdmin->isSuperAdmin()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot edit a Super Admin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:15',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $role = Role::find($validated['role_id']);
        if (!$role || !in_array($role->type, ['admin', 'superadmin'], true)) {
            return redirect()->back()->withInput()->with('error', 'Please select a valid Admin or Super Admin role.');
        }

        if ($role->type === 'superadmin' && !$currentAdmin->isSuperAdmin()) {
            return redirect()->back()->withInput()->with('error', 'Only Super Admin can assign Super Admin role.');
        }

        // Only update password if provided
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $admin->update($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }


    /**
     * Remove the specified admin
     */
    public function destroy(Admin $admin)
    {
        $currentAdmin = Auth::guard('admin')->user();
        
        if (!$currentAdmin) {
            return redirect()->route('admin.login');
        }

        // Prevent deleting yourself
        if ($admin->id == $currentAdmin->id) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting super admin
        if ($admin->isSuperAdmin()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Super Admin cannot be deleted.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    /**
     * Impersonate (secret login) as another admin
     */
    public function impersonate(Admin $admin)
    {
        $currentAdmin = Auth::guard('admin')->user();

        if (!$currentAdmin) {
            return redirect()->route('admin.login')->with('error', 'Session expired. Please login again.');
        }

        // Only super admins can impersonate
        if (!$currentAdmin->isSuperAdmin()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You do not have permission to do this.');
        }

        // Cannot impersonate yourself
        if ($admin->id == $currentAdmin->id) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot impersonate yourself.');
        }

        // Store original admin ID to allow switching back
        Session::put('original_admin_id', $currentAdmin->id);
        
        \App\Helpers\Logger::log("Impersonated admin {$admin->name}", $admin, 'security');
        
        // Login as new admin
        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.dashboard')
            ->with('success', 'You are now logged in as ' . $admin->name);
    }

    /**
     * Stop impersonating and return to original admin
     */
    public function stopImpersonate()
    {
        $originalAdminId = Session::get('original_admin_id');

        if (!$originalAdminId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No impersonation session found.');
        }

        $originalAdmin = Admin::find($originalAdminId);

        if ($originalAdmin) {
            Auth::guard('admin')->login($originalAdmin);
            \App\Helpers\Logger::log("Stopped impersonation", $originalAdmin, 'security');
        }

        Session::forget('original_admin_id');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome back, ' . ($originalAdmin->name ?? 'Admin'));
    }

    /**
     * Secret login as citizen by phone number
     */
    public function loginAsCitizen(Request $request)
    {
        $currentAdmin = Auth::guard('admin')->user();
        
        // Security check: Only allow if logged in admin
        if (!$currentAdmin) {
            return redirect()->route('admin.login');
        }

        // Only admins with permission can login as citizen
        if (!$currentAdmin->hasPermission('citizens.impersonate')) {
            return back()->with('error', 'You do not have permission to perform this action.');
        }

        // Validate phone
        $request->validate([
            'phone' => 'required|string'
        ]);
        
        $phone = $request->phone;
        
        // Find citizen
        $citizen = \App\Models\Citizen::where('phone', $phone)->first();
        
        if (!$citizen) {
            return back()->with('error', 'Citizen account not found for this phone number.');
        }

        // Preserve the current CSRF token so existing admin-tab forms remain valid
        // even though guard login regenerates the session and token.
        $currentCsrfToken = $request->session()->token();
        
        // Login as citizen
        Auth::guard('citizen')->login($citizen);

        if (is_string($currentCsrfToken) && $currentCsrfToken !== '') {
            $request->session()->put('_token', $currentCsrfToken);
        }
        
        // Redirect to citizen dashboard
        return redirect()->route('citizen.dashboard')
            ->with('success', 'Logged in as citizen ' . $citizen->name);
    }
}
