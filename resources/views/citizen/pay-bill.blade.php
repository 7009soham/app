@extends('citizen.layout')

@section('title', 'Pay Bill')
@section('page-title', 'Pay Bill')

@push('styles')
<style>
    .pay-bill-container {
        max-width: 600px;
        margin: 0 auto;
    }

    /* Payment Card */
    .payment-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    .payment-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 32px;
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .payment-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .payment-header-content {
        position: relative;
    }

    .payment-icon {
        width: 72px;
        height: 72px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 32px;
    }

    .payment-header h2 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .payment-header p {
        opacity: 0.9;
        font-size: 15px;
    }

    /* Payment Body */
    .payment-body {
        padding: 32px;
    }

    /* Bill Details */
    .bill-details {
        background: var(--surface-secondary);
        border-radius: var(--radius);
        padding: 24px;
        margin-bottom: 24px;
    }

    .bill-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed var(--border);
    }

    .bill-row:last-child {
        border-bottom: none;
    }

    .bill-label {
        font-size: 14px;
        color: var(--text-secondary);
    }

    .bill-value {
        font-size: 15px;
        font-weight: 500;
        color: var(--text-primary);
    }

    .bill-row.total {
        border-top: 2px solid var(--border);
        border-bottom: none;
        margin-top: 12px;
        padding-top: 16px;
    }

    .bill-row.total .bill-label {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .bill-row.total .bill-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--secondary);
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 20px;
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
        padding: 14px 16px;
        font-size: 16px;
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

    /* Payment Methods */
    .payment-methods {
        margin-bottom: 24px;
    }

    .method-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 12px;
    }

    .method-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .method-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: var(--surface);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        cursor: pointer;
        transition: var(--transition);
    }

    .method-option:hover {
        border-color: var(--primary);
    }

    .method-option.selected {
        border-color: var(--primary);
        background: rgba(30, 58, 95, 0.05);
    }

    .method-option input {
        display: none;
    }

    .method-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .method-icon img {
        max-width: 100%;
        max-height: 100%;
    }

    .method-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
    }

    /* Pay Button */
    .pay-btn {
        width: 100%;
        padding: 16px 24px;
        font-size: 18px;
        font-weight: 700;
        font-family: inherit;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
        color: white;
        box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
    }

    .pay-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(249, 115, 22, 0.5);
    }

    .pay-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    /* Info Notice */
    .info-notice {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        padding: 16px;
        border-radius: var(--radius);
        margin-bottom: 24px;
        font-size: 14px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .info-notice.success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    .info-notice.warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    .info-notice i {
        font-size: 18px;
        flex-shrink: 0;
    }

    /* Back Link */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 24px;
        transition: var(--transition);
    }

    .back-link:hover {
        color: var(--primary);
    }

    /* Secure Badge */
    .secure-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 16px;
        background: var(--surface-secondary);
        border-radius: var(--radius);
        margin-top: 24px;
        font-size: 13px;
        color: var(--text-secondary);
    }

    .secure-badge i {
        color: #16a34a;
    }
</style>
@endpush

@section('content')
<div class="pay-bill-container">
    <a href="{{ $taxType === 'water' ? route('citizen.water-tax') : route('citizen.property-tax') }}" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Back to {{ $taxType === 'water' ? 'Water' : 'Property' }} Tax
    </a>

    <div class="payment-card">
        <div class="payment-header">
            <div class="payment-header-content">
                <div class="payment-icon">
                    <i class="fas fa-{{ $taxType === 'water' ? 'tint' : 'home' }}"></i>
                </div>
                <h2>Pay {{ ucfirst($taxType) }} Tax</h2>
                <p>Secure online payment via PhonePe</p>
            </div>
        </div>

        <div class="payment-body">
            @if($record)
            <!-- Bill Details -->
            <div class="bill-details">
                <div class="bill-row">
                    <span class="bill-label">Customer Name</span>
                    <span class="bill-value">{{ $record->customer_name }}</span>
                </div>
                <div class="bill-row">
                    <span class="bill-label">Customer No</span>
                    <span class="bill-value">{{ $record->customer_no }}</span>
                </div>
                <div class="bill-row">
                    <span class="bill-label">Monthly Bill</span>
                    <span class="bill-value">₹{{ number_format($record->monthly_bill, 2) }}</span>
                </div>
                @if($record->period)
                <div class="bill-row">
                    <span class="bill-label">Period</span>
                    <span class="bill-value">{{ $record->period }}</span>
                </div>
                @endif
                <div class="bill-row">
                    <span class="bill-label">Outstanding Balance</span>
                    <span class="bill-value" style="color: #dc2626;">₹{{ number_format($record->balance, 2) }}</span>
                </div>
                @php
                    $totalAmount = $record->balance;
                @endphp
                <div class="bill-row total">
                    <span class="bill-label">Total Amount Due</span>
                    <span class="bill-value">₹{{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>

            @php
                $phonePeEnabled = \App\Models\SiteSetting::get('phonepe_enabled', '0') === '1';
                $phonePeConfigured = !empty(\App\Models\SiteSetting::get('phonepe_merchant_id', '')) 
                                  && !empty(\App\Models\SiteSetting::get('phonepe_salt_key', ''));
            @endphp

            @if($phonePeEnabled && $phonePeConfigured)
            <!-- Payment Gateway Active -->
            <div class="info-notice success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Online Payment Available</strong><br>
                    Pay securely using UPI, Cards, or Wallets via PhonePe.
                </div>
            </div>

            <!-- Payment Form -->
            <form action="{{ route('citizen.payment.initiate') }}" method="POST" id="paymentForm">
                @csrf
                <input type="hidden" name="tax_type" value="{{ $taxType }}">
                <input type="hidden" name="record_id" value="{{ $record->id }}">
                <input type="hidden" name="amount" value="{{ $totalAmount }}">

                <!-- Payment Methods -->
                <div class="payment-methods">
                    <div class="method-title">Payment Gateway</div>
                    <div class="method-options">
                        <label class="method-option selected">
                            <input type="radio" name="payment_method" value="phonepe" checked>
                            <div class="method-icon">
                                <i class="fas fa-mobile-alt" style="font-size: 24px; color: #5f259f;"></i>
                            </div>
                            <span class="method-name">PhonePe</span>
                        </label>
                        <label class="method-option">
                            <input type="radio" name="payment_method" value="phonepe">
                            <div class="method-icon">
                                <i class="fas fa-qrcode" style="font-size: 24px; color: #00baf2;"></i>
                            </div>
                            <span class="method-name">UPI / Cards</span>
                        </label>
                    </div>
                </div>

                <!-- Pay Button -->
                <button type="submit" class="pay-btn" id="payBtn">
                    <i class="fas fa-shield-alt"></i>
                    Pay ₹{{ number_format($totalAmount, 2) }} Securely
                </button>
            </form>
            @else
            <!-- Payment Gateway Not Configured -->
            <div class="info-notice warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Online Payment Coming Soon</strong><br>
                    Online payment is being set up. Please visit Gram Panchayat office for payment or contact the helpline.
                </div>
            </div>

            <!-- Disabled Payment Methods -->
            <div class="payment-methods">
                <div class="method-title">Select Payment Method</div>
                <div class="method-options" style="opacity: 0.5;">
                    <label class="method-option selected" style="cursor: not-allowed;">
                        <div class="method-icon">
                            <i class="fas fa-mobile-alt" style="font-size: 24px; color: #5f259f;"></i>
                        </div>
                        <span class="method-name">PhonePe</span>
                    </label>
                    <label class="method-option" style="cursor: not-allowed;">
                        <div class="method-icon">
                            <i class="fas fa-qrcode" style="font-size: 24px; color: #00baf2;"></i>
                        </div>
                        <span class="method-name">UPI</span>
                    </label>
                </div>
            </div>

            <!-- Disabled Pay Button -->
            <button type="button" class="pay-btn" disabled>
                <i class="fas fa-lock"></i>
                Pay ₹{{ number_format($totalAmount, 2) }}
            </button>
            @endif

            <div class="secure-badge">
                <i class="fas fa-shield-alt"></i>
                Secured by 256-bit SSL encryption
            </div>
            @else
            <div style="text-align: center; padding: 40px 0;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                <h3 style="margin-bottom: 8px;">Record Not Found</h3>
                <p style="color: var(--text-secondary);">The tax record you're trying to pay for was not found.</p>
                <a href="{{ route('citizen.dashboard') }}" class="back-link" style="margin-top: 20px; display: inline-flex;">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Payment method selection
    document.querySelectorAll('.method-option').forEach(option => {
        option.addEventListener('click', () => {
            document.querySelectorAll('.method-option').forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');
        });
    });

    // Form submission handling
    const paymentForm = document.getElementById('paymentForm');
    const payBtn = document.getElementById('payBtn');
    
    if (paymentForm && payBtn) {
        paymentForm.addEventListener('submit', function(e) {
            payBtn.disabled = true;
            payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        });
    }
</script>
@endpush
@endsection

