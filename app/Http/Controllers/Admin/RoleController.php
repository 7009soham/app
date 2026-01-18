<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::withCount('admins')->orderBy('created_at', 'desc')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissionGroups = Role::getPermissionsByGroup();
        return view('admin.roles.create', compact('permissionGroups'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'type' => 'required|in:admin,employee',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['permissions'] = $validated['permissions'] ?? [];

        Role::create($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing a role
     */
    public function edit(Role $role)
    {
        // Prevent editing superadmin role
        if ($role->type === 'superadmin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Super Admin role cannot be edited.');
        }

        $permissionGroups = Role::getPermissionsByGroup();
        return view('admin.roles.edit', compact('role', 'permissionGroups'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing superadmin role
        if ($role->type === 'superadmin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Super Admin role cannot be edited.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'type' => 'required|in:admin,employee',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['permissions'] = $validated['permissions'] ?? [];

        $role->update($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Prevent deleting superadmin role
        if ($role->type === 'superadmin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Super Admin role cannot be deleted.');
        }

        // Check if role has admins
        if ($role->admins()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role with assigned admins.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
