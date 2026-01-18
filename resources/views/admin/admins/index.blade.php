@extends('admin.layouts.app')

@section('title', 'Admins Management')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }

    .btn-add {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
    }

    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 16px 20px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }

    .data-table th {
        background: #f8fafc;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .admin-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .admin-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
        font-weight: 600;
    }

    .admin-name {
        font-weight: 600;
        color: #1e293b;
    }

    .admin-email {
        font-size: 13px;
        color: #64748b;
    }

    .role-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .role-badge.superadmin {
        background: #fee2e2;
        color: #dc2626;
    }

    .role-badge.admin {
        background: #dbeafe;
        color: #2563eb;
    }

    .role-badge.employee {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-badge.inactive {
        background: #fee2e2;
        color: #dc2626;
    }

    .actions-cell {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background: #eff6ff;
        color: #2563eb;
    }

    .btn-edit:hover {
        background: #dbeafe;
    }

    .btn-secret {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-secret:hover {
        background: #fde68a;
    }

    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #fee2e2;
    }

    .self-badge {
        background: #e0e7ff;
        color: #4f46e5;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 10px;
        margin-left: 8px;
    }

    .last-login {
        font-size: 13px;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Admins Management</h1>
    <a href="{{ route('admin.admins.create') }}" class="btn-add">
        <i class="fas fa-plus"></i> Add Admin
    </a>
</div>

<div class="table-card">
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                <tr>
                    <td>
                        <div class="admin-info">
                            <div class="admin-avatar">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="admin-name">
                                    {{ $admin->name }}
                                    @if($admin->id == $currentAdminId)
                                    <span class="self-badge">You</span>
                                    @endif
                                </div>
                                <div class="admin-email">{{ $admin->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge {{ $admin->role->type ?? 'admin' }}">
                            {{ $admin->role->name ?? 'No Role' }}
                        </span>
                    </td>
                    <td>{{ $admin->phone ?? 'N/A' }}</td>
                    <td>
                        <span class="status-badge {{ $admin->is_active ? 'active' : 'inactive' }}">
                            <i class="fas {{ $admin->is_active ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            {{ $admin->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="last-login">
                        {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}
                    </td>
                    <td>
                        <div class="actions-cell">
                            <a href="{{ route('admin.admins.edit', $admin) }}" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            @if($admin->id != $currentAdminId && !$admin->isSuperAdmin())
                            <form action="{{ route('admin.admins.impersonate', $admin) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-secret" title="Secret Login">
                                    <i class="fas fa-user-secret"></i>
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" style="display: inline;"
                                  onsubmit="return confirm('Are you sure you want to delete this admin?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
