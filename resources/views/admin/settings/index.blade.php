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
            </ul>
        </div>
    </div>
    
    <!-- Settings Forms -->
    <div class="settings-content">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
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
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-rupee-sign" style="color: #5f259f;"></i> PhonePe v2 Payment Gateway</h3>
                    </div>
                    <div class="card-body">
                        @php $paymentSettings = $settings->get('payment', collect()); @endphp
                        @php $smtpSettings = $settings->get('smtp', collect()); @endphp
                        
                        <div class="alert alert-info" style="background: #f3e8ff; border: 1px solid #c4b5fd; color: #5b21b6;">
                            <i class="fas fa-info-circle"></i>
                            Configure your PhonePe v2 payment gateway credentials. Get your credentials from the 
                            <a href="https://developer.phonepe.com/" target="_blank">PhonePe Developer Portal</a>.
                            <br><br>
                            <strong>PhonePe v2 API Features:</strong>
                            <ul style="margin: 10px 0 0 20px;">
                                <li>Improved security with SHA256 checksum</li>
                                <li>Support for UPI, Cards, Wallets & Net Banking</li>
                                <li>Real-time payment status callbacks</li>
                            </ul>
                        </div>
                        
                        <div class="form-group">
                            <label for="phonepe_enabled">Payment Gateway Status</label>
                            <select id="phonepe_enabled" name="phonepe_enabled" class="form-control">
                                @php $paymentEnabled = $paymentSettings->firstWhere('key', 'phonepe_enabled')?->value ?? '0'; @endphp
                                <option value="1" {{ $paymentEnabled == '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ $paymentEnabled == '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>

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

                        <div class="form-group" style="padding: 16px; background: #faf5ff; border: 1px solid #d8b4fe; border-radius: 8px; margin-bottom: 20px;">
                            <label style="color: #6b21a8; font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">
                                <i class="fas fa-percentage"></i> Convenience Fee
                            </label>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                Add a convenience fee percentage on top of tax payments to cover payment gateway charges (e.g., PhonePe charges ~2%). This fee will be shown as a separate line item to citizens before they pay.
                            </p>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="number" id="convenience_fee_percentage" name="convenience_fee_percentage" class="form-control" 
                                       value="{{ $paymentSettings->firstWhere('key', 'convenience_fee_percentage')?->value ?? '0' }}"
                                       placeholder="2" min="0" max="20" step="0.1"
                                       style="max-width: 120px;">
                                <span style="font-size: 16px; font-weight: 600; color: #6b21a8;">%</span>
                            </div>
                            <small style="color: #64748b; margin-top: 6px; display: block;">Set to 0 to disable convenience fee. Example: 2 means 2% will be added to every online payment.</small>
                        </div>

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
                                        @if(($paymentSettings->firstWhere('key', 'due_reminder_enabled')?->value ?? '1') == '1') checked @endif>
                                    <span>Enable reminders</span>
                                </label>

                                <div style="display:flex; align-items:center; gap:8px;">
                                    <label for="due_reminder_days_before" style="margin:0; color:#0f172a; font-weight:500;">Days before due date</label>
                                    <input type="number" id="due_reminder_days_before" name="due_reminder_days_before" class="form-control"
                                           value="{{ $paymentSettings->firstWhere('key', 'due_reminder_days_before')?->value ?? '3' }}"
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
                                    @if(($smtpSettings->firstWhere('key', 'smtp_enabled')?->value ?? '0') == '1') checked @endif>
                                <span>Enable SMTP email sending</span>
                            </label>

                            <div id="smtp_options" style="display: {{ ($smtpSettings->firstWhere('key', 'smtp_enabled')?->value ?? '0') == '1' ? 'block' : 'none' }};">
                                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_host">SMTP Host</label>
                                        <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                                               value="{{ $smtpSettings->firstWhere('key', 'smtp_host')?->value ?? '' }}"
                                               placeholder="smtp.gmail.com">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_port">Port</label>
                                        <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                                               value="{{ $smtpSettings->firstWhere('key', 'smtp_port')?->value ?? '587' }}"
                                               min="1" max="65535" step="1">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_encryption">Encryption</label>
                                        @php $smtpEncryption = $smtpSettings->firstWhere('key', 'smtp_encryption')?->value ?? 'tls'; @endphp
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
                                               value="{{ $smtpSettings->firstWhere('key', 'smtp_username')?->value ?? '' }}"
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
                                               value="{{ $smtpSettings->firstWhere('key', 'smtp_from_address')?->value ?? '' }}"
                                               placeholder="sender@example.com">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_from_name">From Name</label>
                                        <input type="text" id="smtp_from_name" name="smtp_from_name" class="form-control"
                                               value="{{ $smtpSettings->firstWhere('key', 'smtp_from_name')?->value ?? '' }}"
                                               placeholder="Neral Gram Panchayat">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="smtp_timeout">Timeout (seconds)</label>
                                        <input type="number" id="smtp_timeout" name="smtp_timeout" class="form-control"
                                               value="{{ $smtpSettings->firstWhere('key', 'smtp_timeout')?->value ?? '30' }}"
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
                            <small style="color: #64748b;">This key is used to generate the checksum for secure API calls.</small>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="phonepe_env">Environment *</label>
                                <select id="phonepe_env" name="phonepe_env" class="form-control">
                                    @php $env = $paymentSettings->firstWhere('key', 'phonepe_env')?->value ?? 'sandbox'; @endphp
                                    <option value="sandbox" {{ $env === 'sandbox' ? 'selected' : '' }}>🧪 Sandbox (Testing)</option>
                                    <option value="production" {{ $env === 'production' ? 'selected' : '' }}>🚀 Production (Live)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="phonepe_callback_url">Callback URL</label>
                                <input type="text" id="phonepe_callback_url" class="form-control" 
                                       value="{{ url('/citizen/payment/callback') }}" readonly 
                                       style="background: #f1f5f9;">
                                <small style="color: #64748b;">Add this URL in PhonePe dashboard as redirect URL.</small>
                            </div>
                        </div>
                        
                        <div class="api-endpoints" style="background: #f8fafc; border-radius: 8px; padding: 16px; margin-top: 20px;">
                            <h4 style="font-size: 14px; color: #374151; margin-bottom: 12px;">
                                <i class="fas fa-link"></i> API Endpoints (Auto-configured)
                            </h4>
                            <div style="font-family: monospace; font-size: 13px; color: #64748b;">
                                <div style="margin-bottom: 8px;">
                                    <strong>Sandbox:</strong> https://api-preprod.phonepe.com/apis/pg-sandbox
                                </div>
                                <div>
                                    <strong>Production:</strong> https://api.phonepe.com/apis/hermes
                                </div>
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
    </div>
</div>
@endsection

@push('styles')
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
</script>
@endpush
