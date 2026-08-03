@extends('admin.layouts.app')

@section('title', 'Payment Gateway')

@section('content')
<div class="page-header">
    <h1>Payment Gateway</h1>
    <p>Configure your website settings</p>
</div>

<div class="settings-layout">
    @include('admin.settings.partials.nav', ['active' => 'payment'])

    <div class="settings-content">
        <form action="{{ route('admin.settings.payment.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

                <div id="payment">
                    @php $paymentSettings = $settings->get('payment', collect()); @endphp
    
                    {{-- Active Gateway Selector --}}
                    <div class="card" style="margin-bottom: 20px;">
                        <div class="card-header">
                            <h3><i class="fas fa-credit-card"></i> Payment Gateway</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label style="font-weight: 600; margin-bottom: 10px; display: block;">Active Payment Gateway</label>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">Select which gateway processes citizen payments. Configure credentials for your chosen gateway below.</p>
                                <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                                    @php $activeGateway = $paymentSettings->firstWhere('key', 'active_payment_gateway')?->value ?? 'phonepe'; @endphp
                                    <label class="gateway-option {{ $activeGateway === 'phonepe' ? 'selected' : '' }}" style="flex: 1; min-width: 160px; cursor: pointer; border: 2px solid {{ $activeGateway === 'phonepe' ? '#5f259f' : '#e2e8f0' }}; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 12px; transition: all 0.2s;">
                                        <input type="radio" name="active_payment_gateway" value="phonepe" {{ $activeGateway === 'phonepe' ? 'checked' : '' }} style="accent-color: #5f259f;">
                                        <span style="font-size: 22px;">📱</span>
                                        <div>
                                            <div style="font-weight: 600; color: #1e293b;">PhonePe</div>
                                            <div style="font-size: 12px; color: #64748b;">v2 API · UPI, Cards, Wallets</div>
                                        </div>
                                    </label>
                                    <label class="gateway-option {{ $activeGateway === 'razorpay' ? 'selected' : '' }}" style="flex: 1; min-width: 160px; cursor: pointer; border: 2px solid {{ $activeGateway === 'razorpay' ? '#3b82f6' : '#e2e8f0' }}; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 12px; transition: all 0.2s;">
                                        <input type="radio" name="active_payment_gateway" value="razorpay" {{ $activeGateway === 'razorpay' ? 'checked' : '' }} style="accent-color: #3b82f6;">
                                        <span style="font-size: 22px;">💳</span>
                                        <div>
                                            <div style="font-weight: 600; color: #1e293b;">Razorpay</div>
                                            <div style="font-size: 12px; color: #64748b;">Standard Checkout · UPI, Cards, NetBanking</div>
                                        </div>
                                    </label>
                                    <label class="gateway-option {{ $activeGateway === 'payu' ? 'selected' : '' }}" style="flex: 1; min-width: 160px; cursor: pointer; border: 2px solid {{ $activeGateway === 'payu' ? '#00838f' : '#e2e8f0' }}; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 12px; transition: all 0.2s;">
                                        <input type="radio" name="active_payment_gateway" value="payu" {{ $activeGateway === 'payu' ? 'checked' : '' }} style="accent-color: #00838f;">
                                        <span style="font-size: 22px;">🏦</span>
                                        <div>
                                            <div style="font-weight: 600; color: #1e293b;">PayU</div>
                                            <div style="font-size: 12px; color: #64748b;">Hosted Checkout · UPI, Cards, NetBanking</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    {{-- Shared: Partial Payments & Convenience Fee --}}
                    <div class="card" style="margin-bottom: 20px;">
                        <div class="card-header">
                            <h3><i class="fas fa-sliders-h"></i> Payment Options</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group" style="padding: 16px; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; margin-bottom: 20px;">
                                <label style="color: #92400e; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">Partial Payment Options</label>
                                <div style="margin-bottom: 15px;">
                                    <label for="partial_payment_enabled" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="checkbox" id="partial_payment_enabled" name="partial_payment_enabled" value="1"
                                            @if(($paymentSettings->firstWhere('key', 'partial_payment_enabled')?->value ?? '0') == '1') checked @endif
                                            style="width: 18px; height: 18px;">
                                        <span style="font-weight: 500;">Allow Partial Payments</span>
                                    </label>
                                    <small style="display: block; margin-left: 28px; color: #64748b;">If enabled, citizens can choose to pay a percentage of their bill.</small>
                                </div>
                                <div id="partial_options" style="margin-left: 28px; display: {{ ($paymentSettings->firstWhere('key', 'partial_payment_enabled')?->value ?? '0') == '1' ? 'block' : 'none' }};">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Allowed Percentages:</label>
                                    <div style="display: flex; gap: 20px;">
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="checkbox" name="partial_payment_allow_50" value="1"
                                                @if(($paymentSettings->firstWhere('key', 'partial_payment_allow_50')?->value ?? '0') == '1') checked @endif>
                                            <span>50% (Half)</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="checkbox" name="partial_payment_allow_75" value="1"
                                                @if(($paymentSettings->firstWhere('key', 'partial_payment_allow_75')?->value ?? '0') == '1') checked @endif>
                                            <span>75% (3/4th)</span>
                                        </label>
                                    </div>
                                </div>
                                <script>
                                    document.getElementById('partial_payment_enabled').addEventListener('change', function() {
                                        document.getElementById('partial_options').style.display = this.checked ? 'block' : 'none';
                                    });
                                </script>
                            </div>
    
                            <div class="form-group" style="padding: 16px; background: #faf5ff; border: 1px solid #d8b4fe; border-radius: 8px; margin-bottom: 0;">
                                <label style="color: #6b21a8; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                    <i class="fas fa-percentage"></i> Convenience Fee
                                </label>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                    Add a convenience fee percentage on top of tax payments to cover gateway charges. This fee is shown as a separate line item to citizens before they pay.
                                </p>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="number" id="convenience_fee_percentage" name="convenience_fee_percentage" class="form-control"
                                           value="{{ $paymentSettings->firstWhere('key', 'convenience_fee_percentage')?->value ?? '0' }}"
                                           placeholder="2" min="0" max="20" step="0.1" style="max-width: 120px;">
                                    <span style="font-size: 16px; font-weight: 600; color: #6b21a8;">%</span>
                                </div>
                                <small style="color: #64748b; margin-top: 6px; display: block;">Set to 0 to disable. Example: 2 means 2% added to every online payment.</small>
                            </div>
                        </div>
                    </div>
    
                    {{-- PhonePe Config --}}
                    <div class="card gateway-config-card" id="phonepe-config" style="margin-bottom: 20px; display: {{ $activeGateway === 'phonepe' ? 'block' : 'none' }};">
                        <div class="card-header" style="background: linear-gradient(135deg, #f3e8ff 0%, #ede9fe 100%); border-bottom: 1px solid #c4b5fd;">
                            <h3><i class="fas fa-rupee-sign" style="color: #5f259f;"></i> PhonePe v2 Configuration</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info" style="background: #f3e8ff; border: 1px solid #c4b5fd; color: #5b21b6; margin-bottom: 20px;">
                                <i class="fas fa-info-circle"></i>
                                Get your credentials from the <a href="https://developer.phonepe.com/" target="_blank" style="color: #5b21b6;">PhonePe Developer Portal</a>.
                                Features: SHA256 checksum security · UPI, Cards, Wallets, Net Banking · Real-time callbacks.
                            </div>
    
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="phonepe_enabled" value="1"
                                        @if(($paymentSettings->firstWhere('key', 'phonepe_enabled')?->value ?? '0') == '1') checked @endif>
                                    <span style="font-weight: 600;">Enable PhonePe</span>
                                </label>
                            </div>
    
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label for="phonepe_merchant_id">Merchant ID *</label>
                                    <input type="text" id="phonepe_merchant_id" name="phonepe_merchant_id" class="form-control"
                                           value="{{ $paymentSettings->firstWhere('key', 'phonepe_merchant_id')?->value ?? '' }}"
                                           placeholder="MERCHANTUAT">
                                </div>
                                <div class="form-group">
                                    <label for="phonepe_salt_index">Salt Index *</label>
                                    <input type="text" id="phonepe_salt_index" name="phonepe_salt_index" class="form-control"
                                           value="{{ $paymentSettings->firstWhere('key', 'phonepe_salt_index')?->value ?? '1' }}"
                                           placeholder="1">
                                </div>
                            </div>
    
                            <div class="form-group">
                                <label for="phonepe_salt_key">Salt Key *</label>
                                <input type="password" id="phonepe_salt_key" name="phonepe_salt_key" class="form-control"
                                       value=""
                                       placeholder="{{ !empty($paymentSettings->firstWhere('key', 'phonepe_salt_key')?->value) ? 'Salt key is set — leave blank to keep it' : 'Enter your Salt Key' }}">
                                <small style="color: #64748b;">Leave blank to keep the existing key. Used to generate the SHA256 checksum for API calls.</small>
                            </div>
    
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label for="phonepe_env">Environment</label>
                                    <select id="phonepe_env" name="phonepe_env" class="form-control">
                                        @php $ppEnv = $paymentSettings->firstWhere('key', 'phonepe_env')?->value ?? 'sandbox'; @endphp
                                        <option value="sandbox" {{ $ppEnv === 'sandbox' ? 'selected' : '' }}>🧪 Sandbox (Testing)</option>
                                        <option value="production" {{ $ppEnv === 'production' ? 'selected' : '' }}>🚀 Production (Live)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Callback URL (readonly)</label>
                                    <input type="text" class="form-control" value="{{ url('/citizen/payment/callback') }}" readonly style="background: #f1f5f9; font-size: 13px;">
                                    <small style="color: #64748b;">Add this in PhonePe dashboard as redirect URL.</small>
                                </div>
                            </div>
    
                            <div style="background: #f8fafc; border-radius: 8px; padding: 14px; margin-top: 4px; font-family: monospace; font-size: 12px; color: #64748b;">
                                <strong style="font-family: sans-serif; font-size: 13px; color: #374151;">API Endpoints (auto-configured):</strong><br>
                                Sandbox: https://api-preprod.phonepe.com/apis/pg-sandbox<br>
                                Production: https://api.phonepe.com/apis/hermes
                            </div>
                        </div>
                    </div>
    
                    {{-- Razorpay Config --}}
                    <div class="card gateway-config-card" id="razorpay-config" style="margin-bottom: 20px; display: {{ $activeGateway === 'razorpay' ? 'block' : 'none' }};">
                        <div class="card-header" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-bottom: 1px solid #93c5fd;">
                            <h3><i class="fas fa-credit-card" style="color: #1d4ed8;"></i> Razorpay Configuration</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info" style="background: #eff6ff; border: 1px solid #93c5fd; color: #1e40af; margin-bottom: 20px;">
                                <i class="fas fa-info-circle"></i>
                                Get your credentials from the <a href="https://dashboard.razorpay.com/" target="_blank" style="color: #1e40af;">Razorpay Dashboard</a>.
                                Features: Standard Checkout · UPI, Cards, NetBanking, Wallets · HMAC signature verification.
                            </div>
    
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="razorpay_enabled" value="1"
                                        @if(($paymentSettings->firstWhere('key', 'razorpay_enabled')?->value ?? '0') == '1') checked @endif>
                                    <span style="font-weight: 600;">Enable Razorpay</span>
                                </label>
                            </div>
    
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label for="razorpay_key_id">Key ID *</label>
                                    <input type="text" id="razorpay_key_id" name="razorpay_key_id" class="form-control"
                                           value="{{ $paymentSettings->firstWhere('key', 'razorpay_key_id')?->value ?? '' }}"
                                           placeholder="rzp_test_xxxxxxxxxxxxxxxx">
                                    <small style="color: #64748b;">Starts with <code>rzp_test_</code> for sandbox, <code>rzp_live_</code> for production.</small>
                                </div>
                                <div class="form-group">
                                    <label for="razorpay_env">Environment</label>
                                    <select id="razorpay_env" name="razorpay_env" class="form-control">
                                        @php $rzpEnv = $paymentSettings->firstWhere('key', 'razorpay_env')?->value ?? 'sandbox'; @endphp
                                        <option value="sandbox" {{ $rzpEnv === 'sandbox' ? 'selected' : '' }}>🧪 Sandbox (Testing)</option>
                                        <option value="production" {{ $rzpEnv === 'production' ? 'selected' : '' }}>🚀 Production (Live)</option>
                                    </select>
                                </div>
                            </div>
    
                            <div class="form-group">
                                <label for="razorpay_key_secret">Key Secret *</label>
                                <input type="password" id="razorpay_key_secret" name="razorpay_key_secret" class="form-control"
                                       value=""
                                       placeholder="Enter your Key Secret">
                                <small style="color: #64748b;">Leave blank to keep existing secret. Used for HMAC payment signature verification.</small>
                            </div>
    
                            <div class="form-group">
                                <label>Callback URL (readonly)</label>
                                <input type="text" class="form-control" value="{{ url('/citizen/payment/razorpay-return') }}" readonly style="background: #f1f5f9; font-size: 13px;">
                                <small style="color: #64748b;">This URL handles the payment return from Razorpay. No dashboard configuration needed.</small>
                            </div>
                        </div>
                    </div>
    
                    {{-- PayU Config --}}
                    <div class="card gateway-config-card" id="payu-config" style="margin-bottom: 20px; display: {{ $activeGateway === 'payu' ? 'block' : 'none' }};">
                        <div class="card-header" style="background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%); border-bottom: 1px solid #67e8f9;">
                            <h3><i class="fas fa-university" style="color: #00838f;"></i> PayU Configuration</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning" style="background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; margin-bottom: 20px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Configuration only.</strong> The PayU checkout flow is not yet implemented, so selecting PayU as the active gateway will not process citizen payments. Keep PhonePe or Razorpay active until the integration is completed.
                            </div>
    
                            <div class="alert alert-info" style="background: #ecfeff; border: 1px solid #67e8f9; color: #0e7490; margin-bottom: 20px;">
                                <i class="fas fa-info-circle"></i>
                                Get your credentials from the <a href="https://onboarding.payu.in/app/account" target="_blank" style="color: #0e7490;">PayU Dashboard</a>.
                                PayU uses a signed form redirect with SHA-512 hashing · UPI, Cards, NetBanking, Wallets.
                            </div>
    
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="payu_enabled" value="1"
                                        @if(($paymentSettings->firstWhere('key', 'payu_enabled')?->value ?? '0') == '1') checked @endif>
                                    <span style="font-weight: 600;">Enable PayU</span>
                                </label>
                            </div>
    
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label for="payu_merchant_key">Merchant Key *</label>
                                    <input type="text" id="payu_merchant_key" name="payu_merchant_key" class="form-control"
                                           value="{{ $paymentSettings->firstWhere('key', 'payu_merchant_key')?->value ?? '' }}"
                                           placeholder="gtKFFx">
                                    <small style="color: #64748b;">Shown as <code>Key</code> in the PayU dashboard.</small>
                                </div>
                                <div class="form-group">
                                    <label for="payu_env">Environment</label>
                                    <select id="payu_env" name="payu_env" class="form-control">
                                        @php $payuEnv = $paymentSettings->firstWhere('key', 'payu_env')?->value ?? 'sandbox'; @endphp
                                        <option value="sandbox" {{ $payuEnv === 'sandbox' ? 'selected' : '' }}>🧪 Sandbox (Testing)</option>
                                        <option value="production" {{ $payuEnv === 'production' ? 'selected' : '' }}>🚀 Production (Live)</option>
                                    </select>
                                </div>
                            </div>
    
                            <div class="form-group">
                                <label for="payu_merchant_salt">Merchant Salt *</label>
                                <input type="password" id="payu_merchant_salt" name="payu_merchant_salt" class="form-control"
                                       value=""
                                       placeholder="{{ !empty($paymentSettings->firstWhere('key', 'payu_merchant_salt')?->value) ? 'Salt is set — leave blank to keep it' : 'Enter your Merchant Salt' }}">
                                <small style="color: #64748b;">Leave blank to keep the existing salt. Used to generate the SHA-512 request hash.</small>
                            </div>
    
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label for="payu_merchant_id">Merchant ID (MID)</label>
                                    <input type="text" id="payu_merchant_id" name="payu_merchant_id" class="form-control"
                                           value="{{ $paymentSettings->firstWhere('key', 'payu_merchant_id')?->value ?? '' }}"
                                           placeholder="Optional — used for reconciliation">
                                </div>
                                <div class="form-group">
                                    <label>Callback URL (readonly)</label>
                                    <input type="text" class="form-control" value="{{ url('/citizen/payment/payu-return') }}" readonly style="background: #f1f5f9; font-size: 13px;">
                                    <small style="color: #64748b;">Add as both Success and Failure URL in the PayU dashboard.</small>
                                </div>
                            </div>
    
                            <div style="background: #f8fafc; border-radius: 8px; padding: 14px; margin-top: 4px; font-family: monospace; font-size: 12px; color: #64748b;">
                                <strong style="font-family: sans-serif; font-size: 13px; color: #374151;">API Endpoints (auto-configured):</strong><br>
                                Sandbox: https://test.payu.in/_payment<br>
                                Production: https://secure.payu.in/_payment
                            </div>
                        </div>
                    </div>
    
                    <script>
                        (function () {
                            var radios = document.querySelectorAll('input[name="active_payment_gateway"]');
                            var cards = {
                                phonepe: document.getElementById('phonepe-config'),
                                razorpay: document.getElementById('razorpay-config'),
                                payu: document.getElementById('payu-config')
                            };
                            var accents = { phonepe: '#5f259f', razorpay: '#3b82f6', payu: '#00838f' };
                            var gatewayLabels = document.querySelectorAll('.gateway-option');
    
                            function switchGateway(value) {
                                Object.keys(cards).forEach(function (name) {
                                    if (cards[name]) {
                                        cards[name].style.display = name === value ? 'block' : 'none';
                                    }
                                });
                                gatewayLabels.forEach(function (lbl) {
                                    var radio = lbl.querySelector('input[type=radio]');
                                    var isSelected = radio && radio.value === value;
                                    lbl.style.borderColor = isSelected ? (accents[value] || '#e2e8f0') : '#e2e8f0';
                                });
                            }
    
                            radios.forEach(function (radio) {
                                radio.addEventListener('change', function () {
                                    switchGateway(this.value);
                                });
                            });
                        })();
                    </script>
                </div>
    
                <!-- Notifications Settings -->

            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Payment Gateway
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
@include('admin.settings.partials.styles')
@endpush
