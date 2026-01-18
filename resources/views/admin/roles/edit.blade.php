@extends('admin.layouts.app')

@section('title', 'Edit Role - ' . $role->name)

@push('styles')
<style>
    .page-header {
        margin-bottom: 24px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .back-link:hover {
        color: #1e3a5f;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .form-body {
        padding: 32px;
    }

    .form-section {
        margin-bottom: 32px;
    }

    .form-section-title {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.2s ease;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #1e3a5f;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
    }

    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }

    .permission-group {
        background: #f9fafb;
        border-radius: 10px;
        padding: 20px;
    }

    .permission-group-title {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .permission-group-title i {
        color: #1e3a5f;
    }

    .permission-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
    }

    .permission-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #1e3a5f;
    }

    .permission-item label {
        font-size: 14px;
        color: #475569;
        cursor: pointer;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 24px 32px;
        background: #f9fafb;
        border-top: 1px solid #f1f5f9;
    }

    .btn-cancel {
        padding: 12px 24px;
        background: white;
        color: #64748b;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-submit {
        padding: 12px 24px;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
    }

    .select-all-btn {
        font-size: 12px;
        color: #2563eb;
        background: none;
        border: none;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <a href="{{ route('admin.roles.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Roles
    </a>
    <h1 class="page-title">Edit Role: {{ $role->name }}</h1>
</div>

<form action="{{ route('admin.roles.update', $role) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="form-card">
        <div class="form-body">
            <div class="form-section">
                <h3 class="form-section-title">Role Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role Name *</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $role->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="admin" {{ old('type', $role->type) === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="employee" {{ old('type', $role->type) === 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="3">{{ old('description', $role->description) }}</textarea>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">
                    Permissions
                    <button type="button" class="select-all-btn" onclick="toggleAllPermissions(this)">Select All</button>
                </h3>
                
                <div class="permissions-grid">
                    @php $rolePermissions = $role->permissions ?? []; @endphp
                    @foreach($permissionGroups as $groupName => $permissions)
                    <div class="permission-group">
                        <div class="permission-group-title">
                            <i class="fas fa-shield-alt"></i>
                            {{ $groupName }}
                        </div>
                        @foreach($permissions as $key => $label)
                        <div class="permission-item">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}" 
                                   id="perm_{{ $key }}" 
                                   {{ in_array($key, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                            <label for="perm_{{ $key }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.roles.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Update Role
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function toggleAllPermissions(btn) {
        const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => cb.checked = !allChecked);
        btn.textContent = allChecked ? 'Select All' : 'Deselect All';
    }
</script>
@endpush
