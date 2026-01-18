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
                                <option value="0" {{ $firebaseEnabled == '0' ? 'selected' : '' }}>Disabled (Fallback OTP)</option>
                            </select>
                            <small style="color: #64748b;">When disabled, the system will use backend SMS OTP as fallback.</small>
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
