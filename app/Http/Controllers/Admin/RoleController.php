<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $actor = Auth::guard('admin')->user();

        if (!$actor->hasPermission('roles.edit')) {
            abort(403, 'You do not have permission to edit roles.');
        }

        // Prevent editing superadmin role
        if ($role->type === 'superadmin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Super Admin role cannot be edited.');
        }

        // The escalation this closes: nothing stopped an admin editing the very
        // role granting their own rights, so they could add every permission to
        // themselves and set type=admin. Changing your own authority is not an
        // administrative action, it is an escalation.
        if ($actor->role_id === $role->id) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'You cannot edit the role assigned to your own account. Ask a Super Admin.');
        }

        // A ceiling, not just a gate: you may not grant a permission you do not
        // hold yourself, so an admin cannot mint rights beyond their own.
        $requested = (array) $request->input('permissions', []);
        $beyond = array_values(array_filter(
            $requested,
            fn ($p) => !$actor->hasPermission($p)
        ));

        if ($beyond !== []) {
            return back()->withInput()->with(
                'error',
                'You cannot grant permissions you do not hold: ' . implode(', ', $beyond)
            );
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

        $before = ['permissions' => $role->permissions, 'type' => $role->type];

        $role->update($validated);

        AdminAuditLog::record(
            'roles.update',
            $role,
            $before,
            ['permissions' => $role->permissions, 'type' => $role->type],
            $role->slug
        );

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
