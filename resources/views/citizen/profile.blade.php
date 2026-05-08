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
        justify-content: space-between;
        flex-wrap: wrap;
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

    .profile-info {
        flex: 1;
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

    .profile-progress {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .completion-ring {
        width: 106px;
        height: 106px;
        border-radius: 50%;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.25);
    }

    .completion-ring-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(10, 24, 43, 0.55);
        border: 1px solid rgba(255,255,255,0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    .completion-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        opacity: 0.9;
    }

    .gmail-link-card {
        position: relative;
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 52%, #7c3aed 100%);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-bottom: 28px;
        color: #fff;
        box-shadow: 0 8px 32px rgba(37, 99, 235, 0.35);
        overflow: hidden;
        animation: bannerSlideIn 0.45s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .gmail-link-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: radial-gradient(ellipse at 75% 40%, rgba(255,255,255,0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .gmail-link-content {
        position: relative;
    }

    .gmail-link-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .gmail-link-desc {
        font-size: 14px;
        opacity: 0.92;
        margin-bottom: 14px;
    }

    .gmail-features {
        list-style: none;
        margin: 0 0 18px;
        padding: 0;
        display: grid;
        gap: 8px;
    }

    .gmail-features li {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }

    .gmail-features li i {
        color: #bbf7d0;
    }

    .gmail-link-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .gmail-link-input {
        flex: 1;
        min-width: 230px;
        padding: 11px 14px;
        border-radius: var(--radius);
        border: none;
        font-size: 14px;
        color: var(--text-primary);
        background: rgba(255,255,255,0.96);
        outline: none;
    }

    .gmail-link-input:focus {
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.4);
    }

    .connect-gmail-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        border: none;
        border-radius: var(--radius);
        background: #fff;
        color: #1e3a5f;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }

    .connect-gmail-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.2);
    }

    .gmail-link-error {
        margin-top: 10px;
        font-size: 13px;
        color: #fee2e2;
    }

    .otp-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
        align-items: center;
    }

    .otp-input {
        width: 170px;
        max-width: 100%;
        padding: 11px 14px;
        border-radius: var(--radius);
        border: none;
        font-size: 15px;
        letter-spacing: 4px;
        text-align: center;
        color: var(--text-primary);
        background: rgba(255,255,255,0.96);
        outline: none;
    }

    .otp-input:focus {
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.4);
    }

    .resend-otp-btn {
        background: transparent;
        border: 1px solid rgba(255,255,255,0.55);
        color: #fff;
        border-radius: var(--radius);
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }

    .resend-otp-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .resend-otp-btn:not(:disabled):hover {
        background: rgba(255,255,255,0.12);
    }

    .otp-message {
        margin-top: 10px;
        font-size: 13px;
        color: #d1fae5;
    }

    .otp-message.error {
        color: #fee2e2;
    }

    .email-otp-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .email-otp-modal.show {
        display: flex;
    }

    .email-otp-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(2, 6, 23, 0.65);
        backdrop-filter: blur(2px);
    }

    .email-otp-modal-dialog {
        position: relative;
        width: 100%;
        max-width: 460px;
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 20px 60px rgba(2, 6, 23, 0.35);
    }

    .email-otp-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    .email-otp-modal-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
    }

    .email-otp-modal-close {
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
    }

    .email-otp-modal-subtitle {
        margin: 0 0 14px;
        color: #475569;
        font-size: 14px;
    }

    .email-otp-modal-alert {
        margin: 0 0 14px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        font-weight: 600;
    }

    .email-otp-modal-input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 11px 12px;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .email-otp-modal-otp {
        letter-spacing: 6px;
        text-align: center;
        font-size: 22px;
        font-weight: 700;
    }

    .email-otp-modal-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .email-otp-modal-message {
        margin-top: 12px;
        font-size: 13px;
        color: #0f766e;
        min-height: 18px;
    }

    .email-otp-modal-message.error {
        color: #b91c1c;
    }

    @keyframes bannerSlideIn {
        from { opacity: 0; transform: translateY(-12px); }
        to { opacity: 1; transform: translateY(0); }
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

        .profile-progress {
            align-self: center;
        }

        .profile-info p {
            justify-content: center;
        }

        .gmail-link-title {
            font-size: 18px;
        }

        .gmail-link-form {
            flex-direction: column;
            align-items: stretch;
        }

        .gmail-link-input {
            min-width: 0;
            width: 100%;
        }

        .connect-gmail-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
@php
    $profileChecks = [
        !empty($citizen->name),
        !empty($citizen->phone),
        !empty($citizen->customer_no),
        !empty($citizen->email),
        !empty($citizen->address),
        !empty($citizen->phone_verified_at),
    ];
    $profileCompletion = (int) round((count(array_filter($profileChecks)) / count($profileChecks)) * 100);
    $profileCompletionDegrees = (int) round(($profileCompletion / 100) * 360);
    $gmailLinked = !empty($citizen->email);
    $showProfileEmailOtp = (bool) session('show_profile_email_otp', false);
    $showEmailOtpCard = !$gmailLinked;
    $pendingOtpEmail = (string) session('profile_pending_email', old('email', ''));
    $showRemoveEmailButton = !empty($citizen->email);
@endphp

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
        <div class="profile-progress">
            <div class="completion-ring" style="background: conic-gradient(#34d399 {{ $profileCompletionDegrees }}deg, rgba(255,255,255,0.25) {{ $profileCompletionDegrees }}deg);">
                <div class="completion-ring-inner">{{ $profileCompletion }}%</div>
            </div>
            <div class="completion-label">Profile Completed</div>
        </div>
    </div>
</div>

@if($showEmailOtpCard)
<div class="gmail-link-card">
    <div class="gmail-link-content">
        <div class="gmail-link-title">
            <i class="fab fa-google"></i>
            {{ $gmailLinked ? 'Email verification required' : 'Gmail not linked' }}
        </div>
        <p class="gmail-link-desc">
            {{ $gmailLinked
                ? 'Email already exists. Please verify to continue'
                : 'Connect Gmail to activate email notifications for your account.' }}
        </p>
        <ul class="gmail-features">
            <li><i class="fas fa-check-circle"></i> Invoice delivery to email</li>
            <li><i class="fas fa-check-circle"></i> Due date reminders</li>
            <li><i class="fas fa-check-circle"></i> Completion and status notifications</li>
        </ul>
        <form action="{{ route('citizen.banner.save-email') }}" method="POST" class="gmail-link-form" id="emailOtpForm" data-auto-send="{{ $showProfileEmailOtp ? '1' : '0' }}">
            @csrf
            <input
                type="email"
                name="email"
                id="emailOtpEmail"
                class="gmail-link-input"
                value="{{ $pendingOtpEmail }}"
                placeholder="Enter your Gmail address"
                autocomplete="email"
                required
            >
            <button type="button" id="sendEmailOtpBtn" class="connect-gmail-btn">
                <i class="fas fa-link"></i>
                Send OTP
            </button>

            <div id="emailOtpVerifySection" style="display:{{ $showProfileEmailOtp ? 'block' : 'none' }}; width:100%;">
                <div class="otp-row">
                    <input
                        type="text"
                        id="emailOtpInput"
                        class="otp-input"
                        placeholder="000000"
                        maxlength="6"
                        autocomplete="one-time-code"
                    >
                    <button type="button" id="verifyEmailOtpBtn" class="connect-gmail-btn">
                        <i class="fas fa-check"></i>
                        Verify OTP
                    </button>
                    <button type="button" id="resendEmailOtpBtn" class="resend-otp-btn" disabled>
                        Resend OTP (<span id="emailOtpCountdown">30</span>s)
                    </button>
                </div>
                <div id="emailOtpMessage" class="otp-message">{{ $showProfileEmailOtp ? 'Enter OTP sent to your email.' : '' }}</div>
            </div>
        </form>
        @error('email')
        <div class="gmail-link-error">{{ $message }}</div>
        @enderror
    </div>
</div>
@endif

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
    <input type="hidden" name="remove_email" id="removeEmailFlag" value="{{ old('remove_email', '0') }}">

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
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="email" id="email" name="email" class="form-input"
                               value="{{ old('email', $citizen->email) }}"
                               placeholder="Enter email for invoice and reminder notifications">
                        @if($showRemoveEmailButton)
                        <button type="button" id="removeEmailBtn" class="btn btn-secondary" style="white-space: nowrap; padding: 12px 16px;">
                            Remove Email
                        </button>
                        @endif
                    </div>
                    <div class="input-hint">Use Remove Email to disconnect Gmail and pause email notifications.</div>
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

@if($showProfileEmailOtp)
<div id="emailOtpModal" class="email-otp-modal show" data-auto-send="1">
    <div class="email-otp-modal-backdrop" id="emailOtpModalBackdrop"></div>
    <div class="email-otp-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="emailOtpModalTitle">
        <div class="email-otp-modal-header">
            <h3 class="email-otp-modal-title" id="emailOtpModalTitle">Verify Email</h3>
            <button type="button" class="email-otp-modal-close" id="emailOtpModalClose" aria-label="Close">&times;</button>
        </div>
        <p class="email-otp-modal-subtitle">Enter OTP sent to your email to continue with update.</p>
        <div class="email-otp-modal-alert">Email changes require OTP verification before saving.</div>

        <input type="email" id="emailOtpModalEmail" class="email-otp-modal-input" value="{{ $pendingOtpEmail }}" readonly>
        <input type="text" id="emailOtpModalInput" class="email-otp-modal-input email-otp-modal-otp" placeholder="000000" maxlength="6" autocomplete="one-time-code">

        <div class="email-otp-modal-actions">
            <button type="button" class="btn btn-primary" id="emailOtpModalVerifyBtn">Verify OTP</button>
            <button type="button" class="btn btn-secondary" id="emailOtpModalResendBtn" disabled>Resend OTP (<span id="emailOtpModalCountdown">30</span>s)</button>
        </div>
        <div id="emailOtpModalMessage" class="email-otp-modal-message"></div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const otpForm = document.getElementById('emailOtpForm');
        const emailInput = document.getElementById('emailOtpEmail');
        const sendBtn = document.getElementById('sendEmailOtpBtn');
        const verifySection = document.getElementById('emailOtpVerifySection');
        const otpInput = document.getElementById('emailOtpInput');
        const verifyBtn = document.getElementById('verifyEmailOtpBtn');
        const resendBtn = document.getElementById('resendEmailOtpBtn');
        const countdownEl = document.getElementById('emailOtpCountdown');
        const messageEl = document.getElementById('emailOtpMessage');

        if (!otpForm || !emailInput || !sendBtn || !verifySection || !otpInput || !verifyBtn || !resendBtn || !countdownEl || !messageEl) {
            return;
        }

        const autoSend = otpForm.dataset.autoSend === '1';

        let timerId = null;
        let secondsLeft = 0;

        const setMessage = (text, isError = false) => {
            messageEl.textContent = text;
            messageEl.classList.toggle('error', isError);
        };

        const stopTimer = () => {
            if (timerId) {
                clearInterval(timerId);
                timerId = null;
            }
        };

        const startTimer = (seconds = 30) => {
            stopTimer();
            secondsLeft = seconds;
            resendBtn.disabled = true;
            countdownEl.textContent = String(secondsLeft);

            timerId = setInterval(() => {
                secondsLeft -= 1;
                if (secondsLeft <= 0) {
                    stopTimer();
                    countdownEl.textContent = '0';
                    resendBtn.disabled = false;
                    return;
                }

                countdownEl.textContent = String(secondsLeft);
            }, 1000);
        };

        const postJson = async (url, payload) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            let data = {};
            try {
                data = await response.json();
            } catch (_) {
                data = {};
            }

            return { response, data };
        };

        const sendOtp = async () => {
            const email = emailInput.value.trim();
            if (!email) {
                setMessage('Please enter your email address first.', true);
                return;
            }

            sendBtn.disabled = true;
            resendBtn.disabled = true;
            setMessage('');

            try {
                const { response, data } = await postJson('{{ route('citizen.send-email-otp') }}', { email });

                if (!response.ok) {
                    const seconds = Number(data.seconds_remaining || 0);
                    if (response.status === 429 && seconds > 0) {
                        startTimer(seconds);
                    }
                    setMessage(data.message || 'Unable to send OTP.', true);
                    return;
                }

                verifySection.style.display = 'block';
                otpInput.focus();
                startTimer(30);
                setMessage(data.message || 'OTP sent successfully.');
            } catch (error) {
                setMessage('Network error while sending OTP. Please try again.', true);
            } finally {
                sendBtn.disabled = false;
            }
        };

        const resendOtp = async () => {
            await sendOtp();
        };

        const verifyOtp = async () => {
            const email = emailInput.value.trim();
            const otp = otpInput.value.replace(/\D/g, '').trim();

            if (!email) {
                setMessage('Please enter your email address.', true);
                return;
            }

            if (!otp || otp.length < 4 || otp.length > 6) {
                setMessage('Please enter a valid OTP.', true);
                return;
            }

            verifyBtn.disabled = true;
            setMessage('');

            try {
                const { response, data } = await postJson('{{ route('citizen.verify-email-otp') }}', { email, otp });

                if (!response.ok) {
                    setMessage(data.message || 'OTP verification failed.', true);
                    return;
                }

                setMessage(data.message || 'Email verified successfully.');
                stopTimer();
                setTimeout(() => {
                    window.location.reload();
                }, 900);
            } catch (error) {
                setMessage('Network error while verifying OTP. Please try again.', true);
            } finally {
                verifyBtn.disabled = false;
            }
        };

        sendBtn.addEventListener('click', sendOtp);
        resendBtn.addEventListener('click', resendOtp);
        verifyBtn.addEventListener('click', verifyOtp);

        // Prevent fallback form POST to legacy save-email endpoint during OTP flow.
        otpForm.addEventListener('submit', (event) => {
            event.preventDefault();

            if (verifySection.style.display === 'none') {
                sendOtp();
                return;
            }

            verifyOtp();
        });

        otpInput.addEventListener('input', () => {
            otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6);
        });

        if (autoSend && emailInput.value.trim() !== '') {
            sendOtp();
        }
    })();

    (function () {
        const profileForm = document.querySelector('form[action="{{ route('citizen.profile.update') }}"]');
        const emailInput = document.getElementById('email');
        const removeEmailFlag = document.getElementById('removeEmailFlag');
        const removeEmailBtn = document.getElementById('removeEmailBtn');

        if (!emailInput || !removeEmailFlag) {
            return;
        }

        const initialEmailValue = emailInput.value || '';

        const syncRemoveButtonState = () => {
            if (removeEmailBtn) {
                removeEmailBtn.disabled = emailInput.value.trim() === '';
            }
        };

        if (removeEmailBtn) {
            removeEmailBtn.addEventListener('click', () => {
                if (emailInput.value.trim() === '') {
                    return;
                }

                const confirmed = window.confirm('Are you sure you want to remove your email?');
                if (!confirmed) {
                    return;
                }

                emailInput.value = '';
                removeEmailFlag.value = '1';
                syncRemoveButtonState();
                emailInput.focus();
            });
        }

        emailInput.addEventListener('input', () => {
            if (emailInput.value.trim() !== '') {
                removeEmailFlag.value = '0';
            }

            syncRemoveButtonState();
        });

        profileForm?.addEventListener('reset', () => {
            window.setTimeout(() => {
                removeEmailFlag.value = '0';
                emailInput.value = initialEmailValue;
                syncRemoveButtonState();
            }, 0);
        });

        syncRemoveButtonState();
    })();

    (function () {
        const modal = document.getElementById('emailOtpModal');
        if (!modal) {
            return;
        }

        const backdrop = document.getElementById('emailOtpModalBackdrop');
        const closeBtn = document.getElementById('emailOtpModalClose');
        const emailInput = document.getElementById('emailOtpModalEmail');
        const otpInput = document.getElementById('emailOtpModalInput');
        const verifyBtn = document.getElementById('emailOtpModalVerifyBtn');
        const resendBtn = document.getElementById('emailOtpModalResendBtn');
        const countdownEl = document.getElementById('emailOtpModalCountdown');
        const messageEl = document.getElementById('emailOtpModalMessage');

        if (!backdrop || !closeBtn || !emailInput || !otpInput || !verifyBtn || !resendBtn || !countdownEl || !messageEl) {
            return;
        }

        const autoSend = modal.dataset.autoSend === '1';
        let timerId = null;
        let secondsLeft = 0;

        const setMessage = (text, isError = false) => {
            messageEl.textContent = text;
            messageEl.classList.toggle('error', isError);
        };

        const stopTimer = () => {
            if (timerId) {
                clearInterval(timerId);
                timerId = null;
            }
        };

        const startTimer = (seconds = 30) => {
            stopTimer();
            secondsLeft = seconds;
            resendBtn.disabled = true;
            countdownEl.textContent = String(secondsLeft);

            timerId = setInterval(() => {
                secondsLeft -= 1;
                if (secondsLeft <= 0) {
                    stopTimer();
                    countdownEl.textContent = '0';
                    resendBtn.disabled = false;
                    return;
                }

                countdownEl.textContent = String(secondsLeft);
            }, 1000);
        };

        const postJson = async (url, payload) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            let data = {};
            try {
                data = await response.json();
            } catch (_) {
                data = {};
            }

            return { response, data };
        };

        const sendOtp = async () => {
            const email = emailInput.value.trim();
            if (!email) {
                setMessage('Please enter your email address first.', true);
                return;
            }

            setMessage('');
            resendBtn.disabled = true;

            try {
                const { response, data } = await postJson('{{ route('citizen.send-email-otp') }}', { email });

                if (!response.ok) {
                    const seconds = Number(data.seconds_remaining || 0);
                    if (response.status === 429 && seconds > 0) {
                        startTimer(seconds);
                    }

                    setMessage(data.message || 'Unable to send OTP.', true);
                    return;
                }

                setMessage(data.message || 'OTP has been sent to your email address.');
                startTimer(30);
                otpInput.focus();
            } catch (error) {
                setMessage('Network error while sending OTP. Please try again.', true);
            }
        };

        const verifyOtp = async () => {
            const email = emailInput.value.trim();
            const otp = otpInput.value.replace(/\D/g, '').trim();

            if (!email) {
                setMessage('Please enter your email address.', true);
                return;
            }

            if (!otp || otp.length !== 6) {
                setMessage('Please enter a valid 6-digit OTP.', true);
                return;
            }

            verifyBtn.disabled = true;
            setMessage('');

            try {
                const { response, data } = await postJson('{{ route('citizen.verify-email-otp') }}', { email, otp });

                if (!response.ok) {
                    setMessage(data.message || 'OTP verification failed.', true);
                    return;
                }

                setMessage(data.message || 'Email verified and linked successfully.');
                stopTimer();
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } catch (error) {
                setMessage('Network error while verifying OTP. Please try again.', true);
            } finally {
                verifyBtn.disabled = false;
            }
        };

        const closeModal = () => {
            modal.classList.remove('show');
            stopTimer();
        };

        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);
        resendBtn.addEventListener('click', sendOtp);
        verifyBtn.addEventListener('click', verifyOtp);

        otpInput.addEventListener('input', () => {
            otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6);
        });

        if (autoSend && emailInput.value.trim() !== '') {
            sendOtp();
        }
    })();
    });
</script>
@endpush
