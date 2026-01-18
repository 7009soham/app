@extends('admin.layouts.app')

@section('title', 'Add Admin')

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
        max-width: 700px;
    }

    .form-body {
        padding: 32px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group.half {
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
    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.2s ease;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: #1e3a5f;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #1e3a5f;
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
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
    }

    .password-hint {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <a href="{{ route('admin.admins.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Admins
    </a>
    <h1 class="page-title">Add New Admin</h1>
</div>

<form action="{{ route('admin.admins.store') }}" method="POST">
    @csrf
    
    <div class="form-card">
        <div class="form-body">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" required 
                       placeholder="Enter full name">
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" required 
                           placeholder="admin@example.com">
                </div>
                <div class="form-group half">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" 
                           placeholder="10-digit number">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-input" required 
                           placeholder="Minimum 6 characters">
                    <p class="password-hint">Minimum 6 characters</p>
                </div>
                <div class="form-group half">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="form-input" required 
                           placeholder="Confirm password">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Role *</label>
                <select name="role_id" class="form-select" required>
                    <option value="">Select a role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }} ({{ ucfirst($role->type) }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label for="is_active">Active</label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.admins.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-plus"></i> Create Admin
            </button>
        </div>
    </div>
</form>
@endsection
