@extends('admin.layouts.app')

@section('title', 'Firebase OTP')

@section('content')
<div class="page-header">
    <h1>Firebase OTP</h1>
    <p>Configure your website settings</p>
</div>

<div class="settings-layout">
    @include('admin.settings.partials.nav', ['active' => 'firebase'])

    <div class="settings-content">
        <form action="{{ route('admin.settings.firebase.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

                <div id="firebase">
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
                                <label for="firebase_measurement_id">Measurement ID <span style="font-weight:400;color:#64748b;">(optional)</span></label>
                                <input type="text" id="firebase_measurement_id" name="firebase_measurement_id" class="form-control"
                                       value="{{ $firebaseSettings->firstWhere('key', 'firebase_measurement_id')?->value ?? '' }}"
                                       placeholder="G-XXXXXXXXXX">
                                <small style="color: #64748b;">
                                    Only used if Google Analytics is added to the site. Storing it here does not
                                    start any tracking on its own.
                                </small>
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

            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Firebase OTP
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
@include('admin.settings.partials.styles')
@endpush
