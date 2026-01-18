<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

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
        
        $currentAdminId = Session::get('admin_id');
        $originalAdminId = Session::get('original_admin_id');
        
        return view('admin.admins.index', compact('admins', 'currentAdminId', 'originalAdminId'));
    }

    /**
     * Show the form for creating a new admin
     */
    public function create()
    {
        $roles = Role::where('type', '!=', 'superadmin')->get();
        return view('admin.admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:15',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

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
        // Prevent editing super admin unless you're a super admin
        $currentAdmin = Admin::find(Session::get('admin_id'));
        if ($admin->isSuperAdmin() && !$currentAdmin->isSuperAdmin()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot edit a Super Admin.');
        }

        $roles = Role::all();
        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified admin
     */
    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:15',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

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
        $currentAdminId = Session::get('admin_id');
        
        // Prevent deleting yourself
        if ($admin->id == $currentAdminId) {
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
        $currentAdminId = Session::get('admin_id');
        $currentAdmin = Admin::find($currentAdminId);

        // Only super admins can impersonate
        if (!$currentAdmin->isSuperAdmin()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You do not have permission to do this.');
        }

        // Cannot impersonate yourself
        if ($admin->id == $currentAdminId) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot impersonate yourself.');
        }

        // Store original admin ID to allow switching back
        Session::put('original_admin_id', $currentAdminId);
        Session::put('admin_id', $admin->id);

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

        Session::put('admin_id', $originalAdminId);
        Session::forget('original_admin_id');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome back, ' . $originalAdmin->name);
    }
}
