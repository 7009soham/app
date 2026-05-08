@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="page-header">
    <h1>Site Settings</h1>
    <p>Configure your website settings</p>
</div>

<div class="settings-layout">
    <!-- Settings Navigation -->
    <div class="settings-nav card">
        <div class="card-body">
            <ul class="settings-menu">
                <li class="active" data-tab="general">
                    <i class="fas fa-cog"></i> General Settings
                </li>
                <li data-tab="social">
                    <i class="fas fa-share-alt"></i> Social Links
                </li>
                <li data-tab="firebase">
                    <i class="fas fa-mobile-alt"></i> Firebase OTP
                </li>
                <li data-tab="payment">
                    <i class="fas fa-credit-card"></i> Payment Gateway
                </li>
                <li data-tab="notifications">
                    <i class="fas fa-bell"></i> Notifications
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Settings Forms -->
    <div class="settings-content">
        <form id="settings-update-form" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- General Settings -->
            <div class="settings-tab active" id="general">
                <div class="card">
                    <div class="card-header">
                        <h3>General Settings</h3>
                    </div>
                    <div class="card-body">
                        @php $generalSettings = $settings->get('general', collect()); @endphp
                        
                        <div class="form-group">
                            <label for="site_name">Site Name</label>
                            <input type="text" id="site_name" name="site_name" class="form-control" 
                                   value="{{ $generalSettings->firstWhere('key', 'site_name')?->value ?? '' }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_tagline">Tagline</label>
                            <input type="text" id="site_tagline" name="site_tagline" class="form-control" 
                                   value="{{ $generalSettings->firstWhere('key', 'site_tagline')?->value ?? '' }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Site Description</label>
                            <textarea id="site_description" name="site_description" class="form-control" rows="3">{{ $generalSettings->firstWhere('key', 'site_description')?->value ?? '' }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control" 
                                   value="{{ $generalSettings->firstWhere('key', 'contact_email')?->value ?? '' }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" class="form-control" 
                                   value="{{ $generalSettings->firstWhere('key', 'contact_phone')?->value ?? '' }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="2">{{ $generalSettings->firstWhere('key', 'address')?->value ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Social Settings -->
            <div class="settings-tab" id="social">
                <div class="card">
                    <div class="card-header">
                        <h3>Social Links</h3>
                    </div>
                    <div class="card-body">
                        @php $socialSettings = $settings->get('social', collect()); @endphp
                        
                        <div class="form-group">
                            <label for="facebook_url"><i class="fab fa-facebook"></i> Facebook URL</label>
                            <input type="url" id="facebook_url" name="facebook_url" class="form-control" 
                                   value="{{ $socialSettings->firstWhere('key', 'facebook_url')?->value ?? '' }}" 
                                   placeholder="https://facebook.com/yourpage">
                        </div>
                        
                        <div class="form-group">
                            <label for="twitter_url"><i class="fab fa-twitter"></i> Twitter URL</label>
                            <input type="url" id="twitter_url" name="twitter_url" class="form-control" 
                                   value="{{ $socialSettings->firstWhere('key', 'twitter_url')?->value ?? '' }}" 
                                   placeholder="https://twitter.com/yourhandle">
                        </div>
                        
                        <div class="form-group">
                            <label for="instagram_url"><i class="fab fa-instagram"></i> Instagram URL</label>
                            <input type="url" id="instagram_url" name="instagram_url" class="form-control" 
                                   value="{{ $socialSettings->firstWhere('key', 'instagram_url')?->value ?? '' }}" 
                                   placeholder="https://instagram.com/yourhandle">
                        </div>
                        
                        <div class="form-group">
                            <label for="youtube_url"><i class="fab fa-youtube"></i> YouTube URL</label>
                            <input type="url" id="youtube_url" name="youtube_url" class="form-control" 
                                   value="{{ $socialSettings->firstWhere('key', 'youtube_url')?->value ?? '' }}" 
                                   placeholder="https://youtube.com/yourchannel">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Firebase Settings -->
            <div class="settings-tab" id="firebase">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fab fa-google" style="color: #ea4335;"></i> Firebase OTP Configuration</h3>
                    </div>
                    <div class="card-body">
                        @php $firebaseSettings = $settings->get('firebase', collect()); @endphp
                        
                        <div class="alert alert-info" style="background: #fef3c7; border: 1px solid #fcd34d; color: #92400e;">
                            <i class="fas fa-info-circle"></i>
                            Configure Firebase for OTP authentication in the Citizen Portal. 
                            Get your credentials from the <a href="https://console.firebase.google.com/" target="_blank">Firebase Console</a>.
                            <br><br>
                            <strong>Steps:</strong>
                            <ol style="margin: 10px 0 0 20px;">
                                <li>Create a Firebase project</li>
                                <li>Enable Phone Authentication in Authentication → Sign-in method</li>
                                <li>Go to Project Settings → General → Your apps → Web app</li>
                                <li>Copy the config values below</li>
                            </ol>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="firebase_api_key">API Key</label>
                                <input type="text" id="firebase_api_key" name="firebase_api_key" class="form-control" 
                                       value="{{ $firebaseSettings->firstWhere('key', 'firebase_api_key')?->value ?? '' }}"
                                       placeholder="AIzaSy...">
                            </div>
                            
                            <div class="form-group">
                                <label for="firebase_auth_domain">Auth Domain</label>
                                <input type="text" id="firebase_auth_domain" name="firebase_auth_domain" class="form-control" 
                                       value="{{ $firebaseSettings->firstWhere('key', 'firebase_auth_domain')?->value ?? '' }}"
                                       placeholder="your-project.firebaseapp.com">
                            </div>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="firebase_project_id">Project ID</label>
                                <input type="text" id="firebase_project_id" name="firebase_project_id" class="form-control" 
                                       value="{{ $firebaseSettings->firstWhere('key', 'firebase_project_id')?->value ?? '' }}"
                                       placeholder="your-project-id">
                            </div>
                            
                            <div class="form-group">
                                <label for="firebase_storage_bucket">Storage Bucket</label>
                                <input type="text" id="firebase_storage_bucket" name="firebase_storage_bucket" class="form-control" 
                                       value="{{ $firebaseSettings->firstWhere('key', 'firebase_storage_bucket')?->value ?? '' }}"
                                       placeholder="your-project.appspot.com">
                            </div>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="firebase_messaging_sender_id">Messaging Sender ID</label>
                                <input type="text" id="firebase_messaging_sender_id" name="firebase_messaging_sender_id" class="form-control" 
                                       value="{{ $firebaseSettings->firstWhere('key', 'firebase_messaging_sender_id')?->value ?? '' }}"
                                       placeholder="123456789012">
                            </div>
                            
                            <div class="form-group">
                                <label for="firebase_app_id">App ID</label>
                                <input type="text" id="firebase_app_id" name="firebase_app_id" class="form-control" 
                                       value="{{ $firebaseSettings->firstWhere('key', 'firebase_app_id')?->value ?? '' }}"
                                       placeholder="1:123456789012:web:abc123">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="firebase_enabled">Status</label>
                            <select id="firebase_enabled" name="firebase_enabled" class="form-control">
                                @php $firebaseEnabled = $firebaseSettings->firstWhere('key', 'firebase_enabled')?->value ?? '0'; @endphp
                                <option value="1" {{ $firebaseEnabled == '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ $firebaseEnabled == '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                            <small style="color: #64748b;">Keep this enabled for real Firebase OTP authentication.</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Settings -->
            <div class="settings-tab" id="payment">
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
                                   value="{{ $paymentSettings->firstWhere('key', 'phonepe_salt_key')?->value ?? '' }}"
                                   placeholder="Enter your Salt Key">
                            <small style="color: #64748b;">Used to generate the SHA256 checksum for API calls.</small>
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

                <script>
                    (function () {
                        var radios = document.querySelectorAll('input[name="active_payment_gateway"]');
                        var ppCard = document.getElementById('phonepe-config');
                        var rzpCard = document.getElementById('razorpay-config');
                        var gatewayLabels = document.querySelectorAll('.gateway-option');

                        function switchGateway(value) {
                            ppCard.style.display = value === 'phonepe' ? 'block' : 'none';
                            rzpCard.style.display = value === 'razorpay' ? 'block' : 'none';
                            gatewayLabels.forEach(function (lbl) {
                                var radio = lbl.querySelector('input[type=radio]');
                                var isSelected = radio && radio.value === value;
                                lbl.style.borderColor = isSelected
                                    ? (value === 'phonepe' ? '#5f259f' : '#3b82f6')
                                    : '#e2e8f0';
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
            <div class="settings-tab" id="notifications">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bell"></i> Notifications Settings</h3>
                    </div>
                    <div class="card-body">
                        @php $notificationSettings = $settings->get('notifications', collect()); @endphp
                        @php $legacySmtpSettings = $settings->get('smtp', collect()); @endphp
                        @php $legacyPaymentSettings = $settings->get('payment', collect()); @endphp

                        <div class="form-group" style="padding: 16px; background: #ecfeff; border: 1px solid #a5f3fc; border-radius: 8px; margin-bottom: 20px;">
                            <label style="color: #0e7490; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                <i class="fas fa-envelope-open-text"></i> Advance Due Reminder Emails
                            </label>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                Send reminder email before due date: "Please make your payment before X date to avoid penalty".
                            </p>

                            <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: center;">
                                <label for="due_reminder_enabled" style="display:flex; align-items:center; gap:8px; margin:0; cursor:pointer;">
                                    <input type="checkbox" id="due_reminder_enabled" name="due_reminder_enabled" value="1"
                                        @if(($notificationSettings->firstWhere('key', 'due_reminder_enabled')?->value ?? $legacyPaymentSettings->firstWhere('key', 'due_reminder_enabled')?->value ?? '1') == '1') checked @endif>
                                    <span>Enable reminders</span>
                                </label>

                                <div style="display:flex; align-items:center; gap:8px;">
                                    <label for="due_reminder_days_before" style="margin:0; color:#0f172a; font-weight:500;">Days before due date</label>
                                    <input type="number" id="due_reminder_days_before" name="due_reminder_days_before" class="form-control"
                                           value="{{ $notificationSettings->firstWhere('key', 'due_reminder_days_before')?->value ?? $legacyPaymentSettings->firstWhere('key', 'due_reminder_days_before')?->value ?? '3' }}"
                                           min="0" max="30" step="1" style="width:90px;">
                                </div>
                            </div>
                            <small style="color: #64748b; margin-top: 8px; display: block;">
                                Configure the scheduler to run daily so reminders are sent on time.
                            </small>
                        </div>

                        <div class="form-group" style="padding: 16px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px;">
                            <label style="color: #0f172a; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                <i class="fas fa-envelope"></i> SMTP Email Delivery (Invoices, Confirmation, Due Reminders)
                            </label>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                Configure SMTP so payment confirmation, invoice emails, and due-date reminder emails are sent from this system.
                            </p>

                            <label for="smtp_enabled" style="display:flex; align-items:center; gap:8px; margin:0 0 14px; cursor:pointer;">
                                <input type="checkbox" id="smtp_enabled" name="smtp_enabled" value="1"
                                    @if(($notificationSettings->firstWhere('key', 'smtp_enabled')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_enabled')?->value ?? '0') == '1') checked @endif>
                                <span>Enable SMTP email sending</span>
                            </label>

                            <div id="smtp_options" style="display: {{ ($notificationSettings->firstWhere('key', 'smtp_enabled')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_enabled')?->value ?? '0') == '1' ? 'block' : 'none' }};">
                                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_host">SMTP Host</label>
                                        <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                                               value="{{ $notificationSettings->firstWhere('key', 'smtp_host')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_host')?->value ?? '' }}"
                                               placeholder="smtp.gmail.com">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_port">Port</label>
                                        <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                                               value="{{ $notificationSettings->firstWhere('key', 'smtp_port')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_port')?->value ?? '587' }}"
                                               min="1" max="65535" step="1">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_encryption">Encryption</label>
                                        @php $smtpEncryption = $notificationSettings->firstWhere('key', 'smtp_encryption')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_encryption')?->value ?? 'tls'; @endphp
                                        <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                                            <option value="tls" {{ $smtpEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ $smtpEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                                            <option value="none" {{ $smtpEncryption === 'none' ? 'selected' : '' }}>None</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_username">SMTP Username</label>
                                        <input type="text" id="smtp_username" name="smtp_username" class="form-control"
                                               value="{{ $notificationSettings->firstWhere('key', 'smtp_username')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_username')?->value ?? '' }}"
                                               placeholder="sender@example.com">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_password">SMTP Password</label>
                                        <input type="password" id="smtp_password" name="smtp_password" class="form-control"
                                               value="" placeholder="Enter password or app password">
                                        <small style="color: #64748b;">Leave blank to keep existing password.</small>
                                    </div>
                                </div>

                                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-top: 14px;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_from_address">From Email Address</label>
                                        <input type="email" id="smtp_from_address" name="smtp_from_address" class="form-control"
                                               value="{{ $notificationSettings->firstWhere('key', 'smtp_from_address')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_from_address')?->value ?? '' }}"
                                               placeholder="sender@example.com">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_from_name">From Name</label>
                                        <input type="text" id="smtp_from_name" name="smtp_from_name" class="form-control"
                                               value="{{ $notificationSettings->firstWhere('key', 'smtp_from_name')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_from_name')?->value ?? '' }}"
                                               placeholder="Neral Gram Panchayat">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_timeout">Timeout (seconds)</label>
                                        <input type="number" id="smtp_timeout" name="smtp_timeout" class="form-control"
                                               value="{{ $notificationSettings->firstWhere('key', 'smtp_timeout')?->value ?? $legacySmtpSettings->firstWhere('key', 'smtp_timeout')?->value ?? '30' }}"
                                               min="5" max="300" step="1">
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.getElementById('smtp_enabled').addEventListener('change', function() {
                                    document.getElementById('smtp_options').style.display = this.checked ? 'block' : 'none';
                                });
                            </script>
                        </div>

                        <div class="form-group" style="padding: 16px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; margin-bottom: 20px;">
                            <label style="color: #166534; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                <i class="fas fa-user-check"></i> Email Verification
                            </label>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                Control whether citizens must verify email using OTP. If disabled, email is marked verified directly in backend.
                            </p>

                            <label for="email_verification_enabled" style="display:flex; align-items:center; gap:8px; margin:0; cursor:pointer;">
                                <input type="checkbox" id="email_verification_enabled" name="email_verification_enabled" value="1"
                                    @if(($notificationSettings->firstWhere('key', 'email_verification_enabled')?->value ?? '1') == '1') checked @endif>
                                <span>Enable Email Verification</span>
                            </label>
                        </div>

                        @php
                            $emailTemplateDefinitions = [
                                [
                                    'title' => 'Invoice Generation Email',
                                    'mode_key' => 'email_template_invoice_mode',
                                    'subject_key' => 'email_template_invoice_subject',
                                    'body_key' => 'email_template_invoice_body',
                                    'placeholder_hint' => '{{user_name}}, {{invoice_number}}, {{invoice_date}}, {{billing_period}}, {{due_date}}, {{amount_due}}, {{details_table}}, {{invoice_line_items}}',
                                ],
                                [
                                    'title' => 'Due Date Reminder Email',
                                    'mode_key' => 'email_template_due_reminder_mode',
                                    'subject_key' => 'email_template_due_reminder_subject',
                                    'body_key' => 'email_template_due_reminder_body',
                                    'placeholder_hint' => '{{user_name}}, {{customer_no}}, {{bill_no}}, {{billing_period}}, {{due_date}}, {{amount_due}}, {{details_table}}',
                                ],
                                [
                                    'title' => 'Payment Success Email',
                                    'mode_key' => 'email_template_payment_success_mode',
                                    'subject_key' => 'email_template_payment_success_subject',
                                    'body_key' => 'email_template_payment_success_body',
                                    'placeholder_hint' => '{{user_name}}, {{transaction_id}}, {{payment_date}}, {{tax_amount}}, {{convenience_fee}}, {{total_amount}}, {{details_table}}',
                                ],
                                [
                                    'title' => 'Payment Failure Email',
                                    'mode_key' => 'email_template_payment_failure_mode',
                                    'subject_key' => 'email_template_payment_failure_subject',
                                    'body_key' => 'email_template_payment_failure_body',
                                    'placeholder_hint' => '{{user_name}}, {{transaction_id}}, {{attempted_at}}, {{total_amount}}, {{failure_reason}}, {{next_step}}, {{details_table}}',
                                ],
                                [
                                    'title' => 'Completion / Status Notification Email',
                                    'mode_key' => 'email_template_completion_mode',
                                    'subject_key' => 'email_template_completion_subject',
                                    'body_key' => 'email_template_completion_body',
                                    'placeholder_hint' => '{{user_name}}, {{ticket_number}}, {{service_name}}, {{previous_status}}, {{current_status}}, {{updated_at}}, {{summary}}, {{admin_remarks}}, {{details_table}}',
                                ],
                            ];
                        @endphp

                        <div class="form-group" style="padding: 16px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px;">
                            <label style="color: #0f172a; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                <i class="fas fa-pen-nib"></i> Email Content Templates (Rich Text Editor)
                            </label>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                Configure custom email subject and body content using the editor. The system converts content to clean HTML, injects dynamic placeholders, and applies the fixed professional email layout (header, footer, and styling).
                            </p>

                            <div class="email-template-group">
                                <div class="form-row" style="max-width: 460px; margin-bottom: 10px;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="email-template-selector">Select Email Type</label>
                                        <select id="email-template-selector" class="form-control">
                                            @foreach($emailTemplateDefinitions as $template)
                                                <option value="{{ $template['body_key'] }}" {{ $loop->first ? 'selected' : '' }}>
                                                    {{ $template['title'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="email-template-help" style="margin-top: 6px;">
                                            Choose one email type from the dropdown to edit subject and body.
                                        </small>
                                    </div>
                                </div>

                                @foreach($emailTemplateDefinitions as $template)
                                    @php
                                        $subjectValue = $notificationSettings->firstWhere('key', $template['subject_key'])?->value ?? '';
                                        $bodyValue = $notificationSettings->firstWhere('key', $template['body_key'])?->value ?? '';
                                        $modeValue = $notificationSettings->firstWhere('key', $template['mode_key'])?->value ?? (trim((string) $bodyValue) === '' ? 'current' : 'custom');
                                    @endphp

                                    <div class="email-template-card {{ $loop->first ? '' : 'is-hidden' }}" data-template-card="{{ $template['body_key'] }}">
                                        <h4>{{ $template['title'] }}</h4>

                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label for="{{ $template['mode_key'] }}">Template Mode</label>
                                            <select id="{{ $template['mode_key'] }}"
                                                    name="{{ $template['mode_key'] }}"
                                                    class="form-control email-template-mode-select"
                                                    data-custom-fields-target="{{ $template['body_key'] }}_custom_fields">
                                                <option value="current" {{ $modeValue === 'current' ? 'selected' : '' }}>Use current system template (currently used)</option>
                                                <option value="custom" {{ $modeValue === 'custom' ? 'selected' : '' }}>Use custom rich text template</option>
                                            </select>
                                            <small class="email-template-help" style="margin-top: 6px;">
                                                Current system template uses the existing built-in email layout. Custom mode uses the editor content with the fixed professional wrapper.
                                            </small>
                                        </div>

                                        <div id="{{ $template['body_key'] }}_custom_fields" class="custom-template-fields {{ $modeValue === 'custom' ? '' : 'is-hidden' }}">

                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label for="{{ $template['subject_key'] }}">Email Subject</label>
                                            <input type="text"
                                                   id="{{ $template['subject_key'] }}"
                                                   name="{{ $template['subject_key'] }}"
                                                   class="form-control"
                                                   value="{{ old($template['subject_key'], $subjectValue) }}"
                                                   placeholder="Enter subject line">
                                        </div>

                                        <div class="form-group" style="margin-bottom: 10px;">
                                            <label for="{{ $template['body_key'] }}_editor">Email Body</label>
                                            <textarea class="email-template-source"
                                                      style="display:none;"
                                                      name="{{ $template['body_key'] }}"
                                                      id="{{ $template['body_key'] }}">{!! old($template['body_key'], $bodyValue) !!}</textarea>
                                            <div id="{{ $template['body_key'] }}_editor"
                                                 class="email-template-editor"
                                                 data-source-input="{{ $template['body_key'] }}"></div>
                                        </div>

                                        <small class="email-template-help">
                                            Placeholders: {{ $template['placeholder_hint'] }}
                                        </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>

        <div class="card mb-4">
            <div class="card-header">
                <h3><i class="fas fa-paper-plane"></i> System Email Simulation (Admin Test)</h3>
            </div>
            <div class="card-body">
                <p style="margin:0 0 14px;color:#475569;">
                    Trigger invoice generation, due reminder, payment status (success and failure), and completion/status notifications for preview and testing.
                </p>

                <form action="{{ route('admin.settings.simulate-emails') }}" method="POST">
                    @csrf

                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="form-group">
                            <label for="simulation_email">Recipient Email</label>
                            <input
                                type="email"
                                id="simulation_email"
                                name="simulation_email"
                                class="form-control"
                                value="{{ old('simulation_email', 'soham.tare@somaiya.edu') }}"
                                placeholder="soham.tare@somaiya.edu"
                                required
                            >
                            @error('simulation_email')
                                <small style="color:#dc2626;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="simulation_name">Recipient Name</label>
                            <input
                                type="text"
                                id="simulation_name"
                                name="simulation_name"
                                class="form-control"
                                value="{{ old('simulation_name', 'Soham Tare') }}"
                                placeholder="Soham Tare"
                            >
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:4px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="preview_only" value="1" {{ old('preview_only') ? 'checked' : '' }}>
                            <span>Preview only (generate HTML previews without sending emails)</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play-circle"></i> Run Email Simulation
                    </button>
                </form>
            </div>
        </div>

        @if(session('email_simulation_result'))
            @php $simulationResult = session('email_simulation_result'); @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-envelope-open-text"></i> Latest Simulation Result</h3>
                </div>
                <div class="card-body">
                    <p style="margin:0 0 6px;"><strong>Recipient:</strong> {{ $simulationResult['recipient_name'] ?? '-' }} ({{ $simulationResult['recipient_email'] ?? '-' }})</p>
                    <p style="margin:0 0 6px;"><strong>Mode:</strong> {{ $simulationResult['send_mode'] ?? '-' }}</p>
                    <p style="margin:0 0 6px;"><strong>Preview Directory:</strong> {{ $simulationResult['preview_directory'] ?? '-' }}</p>
                    <p style="margin:0 0 14px;"><strong>Summary:</strong> Total {{ $simulationResult['summary']['total'] ?? 0 }}, Sent {{ $simulationResult['summary']['sent'] ?? 0 }}, Failed {{ $simulationResult['summary']['failed'] ?? 0 }}</p>

                    <div style="overflow:auto;">
                        <table style="width:100%;border-collapse:collapse;min-width:720px;">
                            <thead>
                                <tr>
                                    <th style="text-align:left;padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Email Type</th>
                                    <th style="text-align:left;padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Subject</th>
                                    <th style="text-align:left;padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Status</th>
                                    <th style="text-align:left;padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($simulationResult['emails'] ?? []) as $emailResult)
                                    <tr>
                                        <td style="padding:10px;border:1px solid #e2e8f0;">{{ $emailResult['label'] ?? '-' }}</td>
                                        <td style="padding:10px;border:1px solid #e2e8f0;">{{ $emailResult['subject'] ?? '-' }}</td>
                                        <td style="padding:10px;border:1px solid #e2e8f0;">
                                            <span style="font-weight:600;">{{ strtoupper($emailResult['status'] ?? '-') }}</span>
                                            @if(!empty($emailResult['error']))
                                                <div style="margin-top:4px;color:#dc2626;font-size:12px;">{{ $emailResult['error'] }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:10px;border:1px solid #e2e8f0;">
                                            @if(!empty($emailResult['preview_url']))
                                                <a href="{{ $emailResult['preview_url'] }}" target="_blank" rel="noopener">Open preview</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 24px;
    }
    
    .settings-nav {
        position: sticky;
        top: 84px;
        height: fit-content;
    }
    
    .settings-menu {
        list-style: none;
    }
    
    .settings-menu li {
        padding: 12px 16px;
        cursor: pointer;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--color-gray-600);
        transition: all 0.2s;
    }
    
    .settings-menu li:hover {
        background: var(--color-gray-100);
    }
    
    .settings-menu li.active {
        background: var(--admin-primary);
        color: white;
    }
    
    .settings-tab {
        display: none;
    }
    
    .settings-tab.active {
        display: block;
    }

    .email-template-group {
        display: grid;
        gap: 14px;
    }

    .email-template-card {
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        background: #ffffff;
        padding: 14px;
    }

    .email-template-card.is-hidden {
        display: none;
    }

    .custom-template-fields.is-hidden {
        display: none;
    }

    .email-template-card h4 {
        margin: 0 0 12px;
        font-size: 15px;
        color: #1e293b;
    }

    .email-template-editor {
        background: #ffffff;
        border-radius: 8px;
    }

    .email-template-editor .ql-editor {
        min-height: 150px;
        font-size: 14px;
        line-height: 1.6;
    }

    .email-template-help {
        display: block;
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }
    
    @media (max-width: 768px) {
        .settings-layout {
            grid-template-columns: 1fr;
        }
        
        .settings-nav {
            position: static;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    document.querySelectorAll('.settings-menu li').forEach(item => {
        item.addEventListener('click', function() {
            // Remove active from all
            document.querySelectorAll('.settings-menu li').forEach(i => i.classList.remove('active'));
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            
            // Add active to clicked
            this.classList.add('active');
            document.getElementById(this.dataset.tab).classList.add('active');
        });
    });

    const settingsForm = document.getElementById('settings-update-form');
    if (settingsForm) {
        const templateSelector = document.getElementById('email-template-selector');
        const templateCards = document.querySelectorAll('.email-template-card[data-template-card]');
        const templateModeSelectors = document.querySelectorAll('.email-template-mode-select');

        const showSelectedTemplateCard = function (templateKey) {
            templateCards.forEach(card => {
                const cardKey = card.getAttribute('data-template-card');
                const isMatch = cardKey === templateKey;
                card.classList.toggle('is-hidden', !isMatch);
            });
        };

        if (templateSelector && templateCards.length > 0) {
            showSelectedTemplateCard(templateSelector.value || templateCards[0].getAttribute('data-template-card'));
            templateSelector.addEventListener('change', function () {
                showSelectedTemplateCard(this.value);
            });
        }

        const syncCustomFieldsVisibility = function (modeSelectElement) {
            const targetId = modeSelectElement.dataset.customFieldsTarget;
            const targetElement = targetId ? document.getElementById(targetId) : null;
            if (!targetElement) {
                return;
            }

            targetElement.classList.toggle('is-hidden', modeSelectElement.value !== 'custom');
        };

        templateModeSelectors.forEach(modeSelectElement => {
            syncCustomFieldsVisibility(modeSelectElement);
            modeSelectElement.addEventListener('change', function () {
                syncCustomFieldsVisibility(this);
            });
        });

        const sourceTextareas = document.querySelectorAll('.email-template-source');

        if (typeof Quill === 'undefined') {
            sourceTextareas.forEach(textarea => {
                textarea.style.display = 'block';
                textarea.rows = 8;
                textarea.classList.add('form-control');
            });
        } else {
            const editors = [];

            document.querySelectorAll('.email-template-editor').forEach(editorElement => {
                const sourceId = editorElement.dataset.sourceInput;
                const sourceTextarea = document.getElementById(sourceId);

                if (!sourceTextarea) {
                    return;
                }

                const quill = new Quill(editorElement, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            [{ align: [] }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                quill.root.innerHTML = sourceTextarea.value || '<p><br></p>';
                editors.push({ quill, sourceTextarea });
            });

            settingsForm.addEventListener('submit', function () {
                editors.forEach(({ quill, sourceTextarea }) => {
                    sourceTextarea.value = quill.root.innerHTML;
                });
            });
        }
    }
</script>
@endpush
