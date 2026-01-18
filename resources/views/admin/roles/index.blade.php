@extends('admin.layouts.app')

@section('title', 'Roles Management')

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

    .roles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .role-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .role-header {
        padding: 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .role-name {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .role-type {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .role-type.superadmin {
        background: #fee2e2;
        color: #dc2626;
    }

    .role-type.admin {
        background: #dbeafe;
        color: #2563eb;
    }

    .role-type.employee {
        background: #dcfce7;
        color: #16a34a;
    }

    .role-body {
        padding: 20px;
    }

    .role-description {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .role-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 16px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .stat-item i {
        color: #64748b;
    }

    .stat-item span {
        font-size: 14px;
        color: #374151;
    }

    .permission-count {
        background: #f1f5f9;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 13px;
        color: #475569;
        margin-bottom: 16px;
    }

    .role-actions {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .btn-edit {
        background: #eff6ff;
        color: #2563eb;
    }

    .btn-edit:hover {
        background: #dbeafe;
    }

    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #fee2e2;
    }

    .protected-badge {
        background: #f1f5f9;
        color: #64748b;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Roles Management</h1>
    <a href="{{ route('admin.roles.create') }}" class="btn-add">
        <i class="fas fa-plus"></i> Create Role
    </a>
</div>

<div class="roles-grid">
    @foreach($roles as $role)
    <div class="role-card">
        <div class="role-header">
            <span class="role-name">{{ $role->name }}</span>
            <span class="role-type {{ $role->type }}">{{ $role->type }}</span>
        </div>
        <div class="role-body">
            <p class="role-description">{{ $role->description ?? 'No description provided.' }}</p>
            
            <div class="role-stats">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <span>{{ $role->admins_count }} Admins</span>
                </div>
            </div>

            <div class="permission-count">
                <i class="fas fa-key"></i>
                {{ $role->type === 'superadmin' ? 'All Permissions' : count($role->permissions ?? []) . ' Permissions' }}
            </div>

            @if($role->type === 'superadmin')
            <div class="protected-badge">
                <i class="fas fa-lock"></i> Protected Role - Cannot be modified
            </div>
            @else
            <div class="role-actions">
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn-action btn-edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" style="flex: 1;" 
                      onsubmit="return confirm('Are you sure you want to delete this role?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action btn-delete" style="width: 100%;" 
                            {{ $role->admins_count > 0 ? 'disabled' : '' }}>
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
