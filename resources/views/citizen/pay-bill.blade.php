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

    /* Shown when only one gateway is live, so the single tile does not look
       like an unexplained choice. */
    .method-note {
        margin: 10px 0 0;
        font-size: 13px;
        color: var(--text-secondary, #64748b);
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
        {{ $taxType === 'water' ? __('messages.back_to_water_tax') : __('messages.back_to_property_tax') }}
    </a>

    <div class="payment-card">
        <div class="payment-header">
            <div class="payment-header-content">
                <div class="payment-icon">
                    <i class="fas fa-{{ $taxType === 'water' ? 'tint' : 'home' }}"></i>
                </div>
                <h2>{{ __('messages.pay_tax_title', ['type' => __('messages.' . $taxType . '_tax')]) }}</h2>
                <p>{{ __('messages.secure_payment_desc') }}</p>
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
                @if($taxType === 'water')
                <div class="bill-row">
                    <span class="bill-label">{{ __('messages.customer_no') }}</span>
                    <span class="bill-value">{{ $record->customer_no }}</span>
                </div>
                @elseif($taxType === 'property' && !empty($record->property_no))
                <div class="bill-row">
                    <span class="bill-label">Property No</span>
                    <span class="bill-value">{{ $record->property_no }}</span>
                </div>
                @endif
                <div class="bill-row">
                    <span class="bill-label">{{ __('messages.bill_per_month', ['amount' => '']) }}</span>
                    <span class="bill-value">₹{{ number_format($record->monthly_bill, 2) }}</span>
                </div>
                @if($record->period)
                <div class="bill-row">
                    <span class="bill-label">{{ __('messages.period') ?? 'Period' }}</span>
                    <span class="bill-value">{{ $record->period }}</span>
                </div>
                @endif
                <div class="bill-row">
                    <span class="bill-label">{{ __('messages.outstanding_amount') }}</span>
                    <span class="bill-value" style="color: #dc2626;">₹{{ number_format($record->balance, 2) }}</span>
                </div>
                @php
                    $taxAmount = $record->balance;
                    $convenienceFeePercent = floatval(\App\Models\SiteSetting::get('convenience_fee_percentage', '0'));
                    $convenienceFee = round($taxAmount * $convenienceFeePercent / 100, 2);
                    $totalAmount = $taxAmount + $convenienceFee;
                @endphp
                @if($convenienceFeePercent > 0)
                <div class="bill-row">
                    <span class="bill-label">
                        <i class="fas fa-info-circle" style="color: #7c3aed;"></i>
                        Convenience Fee ({{ $convenienceFeePercent }}%)
                    </span>
                    <span class="bill-value" style="color: #7c3aed;" id="convenienceFeeDisplay">₹{{ number_format($convenienceFee, 2) }}</span>
                </div>
                @endif
                <div class="bill-row total">
                    <span class="bill-label">{{ __('messages.total_amount_due') }}</span>
                    <span class="bill-value" id="totalAmountDisplay">₹{{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>

            @php
                // Driven entirely by Admin > Settings > Payment. Previously this
                // checked PhonePe specifically, so switching the active gateway
                // to Razorpay hid the payment form altogether.
                $gateways = app(\App\Services\PaymentGatewayRegistry::class)->available($taxType);
                $defaultGateway = app(\App\Services\PaymentGatewayRegistry::class)->default($taxType);
            @endphp

            @if(!empty($gateways))
            <!-- Payment Gateway Active -->
            <div class="info-notice success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>{{ __('messages.online_payment_available') }}</strong><br>
                    {{ __('messages.online_payment_desc') }}
                </div>
            </div>

            <!-- Payment Form -->
            <form action="{{ route('citizen.payment.initiate') }}" method="POST" id="paymentForm">
                @csrf
                <input type="hidden" name="tax_type" value="{{ $taxType }}">
                <input type="hidden" name="record_id" value="{{ $record->id }}">
                <input type="hidden" name="amount" id="paymentAmount" value="{{ $totalAmount }}">
                <input type="hidden" name="convenience_fee" id="convenienceFeeInput" value="{{ $convenienceFee }}">

                @php
                    $partialEnabled = \App\Models\SiteSetting::get('partial_payment_enabled', '0') === '1';
                    $allow50 = \App\Models\SiteSetting::get('partial_payment_allow_50', '0') === '1';
                    $allow75 = \App\Models\SiteSetting::get('partial_payment_allow_75', '0') === '1';
                @endphp

                @if($partialEnabled && ($allow50 || $allow75))
                <!-- Payment Amount Selection -->
                <div class="payment-methods" style="margin-bottom: 24px;">
                    <div class="method-title">{{ __('messages.select_payment_amount') }}</div>
                    <div class="method-options" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));">
                        
                        <!-- Full Amount -->
                        <label class="method-option selected amount-option" onclick="selectAmount({{ $taxAmount }}, this)">
                            <div class="method-icon" style="background: #dcfce7; color: #16a34a; border-radius: 50%;">
                                <i class="fas fa-check-circle" style="font-size: 18px;"></i>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span class="method-name">{{ __('messages.full_amount') }}</span>
                                <span style="font-size: 13px; font-weight: 700; color: #16a34a;">₹{{ number_format($totalAmount, 2) }}</span>
                                @if($convenienceFeePercent > 0)
                                <span style="font-size: 10px; color: #94a3b8;">Tax: ₹{{ number_format($taxAmount, 2) }} + Fee: ₹{{ number_format($convenienceFee, 2) }}</span>
                                @endif
                            </div>
                        </label>

                        @if($allow75)
                        <!-- 75% Amount -->
                        @php 
                            $taxAmount75 = round($taxAmount * 0.75, 2);
                            $fee75 = round($taxAmount75 * $convenienceFeePercent / 100, 2);
                            $total75 = $taxAmount75 + $fee75;
                        @endphp
                        <label class="method-option amount-option" onclick="selectAmount({{ $taxAmount75 }}, this)">
                            <div class="method-icon" style="background: #fff7ed; color: #ea580c; border-radius: 50%;">
                                <i class="fas fa-chart-pie" style="font-size: 18px;"></i>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span class="method-name">{{ __('messages.amount_75') }}</span>
                                <span style="font-size: 13px; font-weight: 700; color: #ea580c;">₹{{ number_format($total75, 2) }}</span>
                                @if($convenienceFeePercent > 0)
                                <span style="font-size: 10px; color: #94a3b8;">Tax: ₹{{ number_format($taxAmount75, 2) }} + Fee: ₹{{ number_format($fee75, 2) }}</span>
                                @endif
                            </div>
                        </label>
                        @endif

                        @if($allow50)
                        <!-- 50% Amount -->
                        @php 
                            $taxAmount50 = round($taxAmount * 0.50, 2);
                            $fee50 = round($taxAmount50 * $convenienceFeePercent / 100, 2);
                            $total50 = $taxAmount50 + $fee50;
                        @endphp
                        <label class="method-option amount-option" onclick="selectAmount({{ $taxAmount50 }}, this)">
                            <div class="method-icon" style="background: #eff6ff; color: #3b82f6; border-radius: 50%;">
                                <i class="fas fa-adjust" style="font-size: 18px;"></i>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span class="method-name">{{ __('messages.amount_50') }}</span>
                                <span style="font-size: 13px; font-weight: 700; color: #3b82f6;">₹{{ number_format($total50, 2) }}</span>
                                @if($convenienceFeePercent > 0)
                                <span style="font-size: 10px; color: #94a3b8;">Tax: ₹{{ number_format($taxAmount50, 2) }} + Fee: ₹{{ number_format($fee50, 2) }}</span>
                                @endif
                            </div>
                        </label>
                        @endif

                    </div>
                </div>

                <script>
                    var convenienceFeePercent = {{ $convenienceFeePercent }};

                    function selectAmount(baseTaxAmount, element) {
                        // Calculate convenience fee on the selected tax amount
                        var fee = Math.round(baseTaxAmount * convenienceFeePercent / 100 * 100) / 100;
                        var totalWithFee = Math.round((baseTaxAmount + fee) * 100) / 100;

                        // Update hidden inputs
                        document.getElementById('paymentAmount').value = totalWithFee;
                        document.getElementById('convenienceFeeInput').value = fee;
                        
                        // Update UI displays
                        var feeDisplay = document.getElementById('convenienceFeeDisplay');
                        if (feeDisplay) {
                            feeDisplay.textContent = '₹' + fee.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                        var totalDisplay = document.getElementById('totalAmountDisplay');
                        if (totalDisplay) {
                            totalDisplay.textContent = '₹' + totalWithFee.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }

                        // Update selection highlight
                        document.querySelectorAll('.amount-option').forEach(opt => opt.classList.remove('selected'));
                        element.classList.add('selected');

                        // Update Pay Button Text
                        var btn = document.getElementById('payBtn');
                        var template = '{{ __('messages.pay_securely', ['amount' => ':amount']) }}';
                        var newText = template.replace(':amount', totalWithFee.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        btn.innerHTML = '<i class="fas fa-shield-alt"></i> ' + newText;
                    }
                </script>
                @endif

                <!-- Payment Methods, from Admin > Settings > Payment -->
                <div class="payment-methods">
                    <div class="method-title">{{ __('messages.payment_gateway') }}</div>
                    <div class="method-options">
                        @foreach($gateways as $gateway)
                            <label class="method-option {{ $gateway['key'] === $defaultGateway ? 'selected' : '' }}">
                                <input type="radio" name="payment_method" value="{{ $gateway['key'] }}"
                                    {{ $gateway['key'] === $defaultGateway ? 'checked' : '' }}>
                                <div class="method-icon">
                                    <i class="{{ $gateway['icon'] }}" style="font-size: 24px; color: {{ $gateway['colour'] }};"></i>
                                </div>
                                <span class="method-name">{{ $gateway['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if(count($gateways) === 1)
                        <p class="method-note">
                            {{ collect($gateways)->first()['description'] }}
                        </p>
                    @endif
                </div>

                <!-- Pay Button -->
                <button type="submit" class="pay-btn" id="payBtn">
                    <i class="fas fa-shield-alt"></i>
                    {{ __('messages.pay_securely', ['amount' => number_format($totalAmount, 2)]) }}
                </button>
            </form>
            @else
            <!-- Payment Gateway Not Configured -->
            <div class="info-notice warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>{{ __('messages.online_payment_soon') }}</strong><br>
                    {{ __('messages.online_payment_soon_desc') }}
                </div>
            </div>

            <!-- Disabled Payment Methods -->
            <div class="payment-methods">
                <div class="method-title">{{ __('messages.select_payment_method') }}</div>
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
                {{ __('messages.pay_amount_simple', ['amount' => number_format($totalAmount, 2)]) }}
            </button>
            @endif

            <div class="secure-badge">
                <i class="fas fa-shield-alt"></i>
                {{ __('messages.secured_by') }}
            </div>
            @else
            <div style="text-align: center; padding: 40px 0;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                <h3 style="margin-bottom: 8px;">{{ __('messages.record_not_found') }}</h3>
                <p style="color: var(--text-secondary);">{{ __('messages.record_not_found_desc') }}</p>
                <a href="{{ route('citizen.dashboard') }}" class="back-link" style="margin-top: 20px; display: inline-flex;">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('messages.back_to_dashboard') }}
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
        option.addEventListener('click', function() {
            // Find parent container to scope selection
            const container = this.closest('.method-options');
            if (container) {
                container.querySelectorAll('.method-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
            }
            
            // Check radio input if present
            const radio = this.querySelector('input[type="radio"]');
            if(radio) {
                radio.checked = true;
            }
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

