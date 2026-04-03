@extends('citizen.layout')

@section('title', __('messages.my_profile'))
@section('page-title', __('messages.my_profile'))

@push('styles')
<style>
    /* Profile Header */
    .profile-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: var(--radius-lg);
        padding: 40px;
        margin-bottom: 32px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .profile-content {
        position: relative;
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 700;
        border: 4px solid rgba(255,255,255,0.3);
    }

    .profile-info h2 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .profile-info p {
        opacity: 0.9;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .verified-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        margin-top: 12px;
    }

    .verified-badge i {
        color: #34d399;
    }

    /* Profile Form Card */
    .profile-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .card-header {
        padding: 24px;
        border-bottom: 1px solid var(--border);
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-title i {
        color: var(--primary);
    }

    .card-body {
        padding: 24px;
    }

    /* Form Styles */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-input {
        width: 100%;
        padding: 12px 16px;
        font-size: 15px;
        font-family: inherit;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        background: var(--surface);
        color: var(--text-primary);
        transition: var(--transition);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(30, 58, 95, 0.1);
    }

    .form-input:disabled {
        background: var(--surface-secondary);
        color: var(--text-secondary);
        cursor: not-allowed;
    }

    .form-input.textarea {
        min-height: 100px;
        resize: vertical;
    }

    .input-hint {
        font-size: 13px;
        color: var(--text-secondary);
        margin-top: 6px;
    }

    /* Actions */
    .form-actions {
        padding: 24px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        font-family: inherit;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(249, 115, 22, 0.4);
    }

    .btn-secondary {
        background: var(--surface-secondary);
        color: var(--text-primary);
    }

    .btn-secondary:hover {
        background: var(--border);
    }

    /* Info Cards */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-top: 24px;
    }

    .info-card {
        background: var(--surface-secondary);
        border-radius: var(--radius);
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .info-card-icon {
        width: 48px;
        height: 48px;
        background: var(--surface);
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--primary);
        flex-shrink: 0;
    }

    .info-card-content {
        flex: 1;
    }

    .info-card-label {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .info-card-value {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
    }

    @media (max-width: 640px) {
        .profile-content {
            flex-direction: column;
            text-align: center;
        }

        .profile-info p {
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-content">
        <div class="profile-avatar">
            {{ strtoupper(substr($citizen->name, 0, 1)) }}
        </div>
        <div class="profile-info">
            <h2>{{ $citizen->name }}</h2>
            <p>
                <i class="fas fa-phone"></i>
                +91 {{ $citizen->phone }}
            </p>
            @if($citizen->phone_verified_at)
            <div class="verified-badge">
                <i class="fas fa-check-circle"></i>
                {{ __('messages.phone_verified') }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Account Info -->
<div class="info-grid">
    <div class="info-card">
        <div class="info-card-icon">
            <i class="fas fa-id-card"></i>
        </div>
        <div class="info-card-content">
            <div class="info-card-label">{{ __('messages.customer_no') }}</div>
            <div class="info-card-value">{{ $citizen->customer_no }}</div>
        </div>
    </div>

    <div class="info-card">
        <div class="info-card-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="info-card-content">
            <div class="info-card-label">{{ __('messages.member_since') }}</div>
            <div class="info-card-value">{{ $citizen->created_at->format('d M Y') }}</div>
        </div>
    </div>

    <div class="info-card">
        <div class="info-card-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="info-card-content">
            <div class="info-card-label">{{ __('messages.last_login') }}</div>
            <div class="info-card-value">{{ $citizen->phone_verified_at ? $citizen->phone_verified_at->format('d M Y, h:i A') : 'N/A' }}</div>
        </div>
    </div>
</div>

<!-- Edit Profile Form -->
<form action="{{ route('citizen.profile.update') }}" method="POST" style="margin-top: 32px;">
    @csrf
    @method('PUT')

    <div class="profile-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit"></i>
                {{ __('messages.edit_profile') }}
            </h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.full_name') }}</label>
                    <input type="text" class="form-input" 
                           value="{{ $citizen->name }}" disabled>
                    <div class="input-hint">{{ __('messages.name_cannot_change') }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.mobile_number') }}</label>
                    <input type="text" class="form-input" 
                           value="+91 {{ $citizen->phone }}" disabled>
                    <div class="input-hint">{{ __('messages.phone_cannot_change') }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.customer_no') }}</label>
                    <input type="text" class="form-input" 
                           value="{{ $citizen->customer_no }}" disabled>
                    <div class="input-hint">{{ __('messages.customer_no_cannot_change') }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input"
                           value="{{ old('email', $citizen->email) }}"
                           placeholder="Enter email for invoice and reminder notifications">
                    @error('email')
                    <div class="input-hint" style="color: #dc2626;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">{{ __('messages.address') }}</label>
                    <textarea name="address" class="form-input textarea" 
                              placeholder="{{ __('messages.enter_address') }}">{{ old('address', $citizen->address) }}</textarea>
                    @error('address')
                    <div class="input-hint" style="color: #dc2626;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="reset" class="btn btn-secondary">
                <i class="fas fa-undo"></i>
                {{ __('messages.reset') }}
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                {{ __('messages.save_changes') }}
            </button>
        </div>
    </div>
</form>
@endsection
