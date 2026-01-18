@extends('layouts.app')

@section('title', 'Report Issue - Grievance Redressal')

@push('styles')
<style>
    .grievance-page {
        padding: 60px 0;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 80vh;
    }

    .grievance-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 36px;
        font-weight: 700;
        color: #1e3a5f;
        margin-bottom: 12px;
    }

    .page-header p {
        font-size: 16px;
        color: #64748b;
        max-width: 500px;
        margin: 0 auto;
    }

    .grievance-form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .form-header {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        padding: 24px 32px;
        color: white;
    }

    .form-header h2 {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-header p {
        font-size: 14px;
        opacity: 0.9;
    }

    .form-body {
        padding: 32px;
    }

    .form-section {
        margin-bottom: 28px;
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 600;
        color: #1e3a5f;
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-section-title i {
        color: #ef4444;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    @media (max-width: 640px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-label .required {
        color: #ef4444;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        font-size: 15px;
        font-family: inherit;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: #f9fafb;
        color: #1f2937;
        transition: all 0.3s ease;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #ef4444;
        background: white;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .form-select {
        cursor: pointer;
    }

    /* Category Grid */
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }

    .category-option {
        position: relative;
    }

    .category-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .category-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 16px 12px;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .category-option label i {
        font-size: 24px;
        color: #6b7280;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .category-option label span {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
    }

    .category-option input:checked + label {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .category-option input:checked + label i {
        color: #ef4444;
    }

    /* Image Upload */
    .image-upload {
        position: relative;
    }

    .image-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f9fafb;
    }

    .image-upload-area:hover {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .image-upload-area i {
        font-size: 40px;
        color: #9ca3af;
        margin-bottom: 12px;
    }

    .image-upload-area p {
        font-size: 14px;
        color: #6b7280;
    }

    .image-upload-area .hint {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 8px;
    }

    .image-upload input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .image-preview {
        display: none;
        margin-top: 16px;
    }

    .image-preview img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 12px;
        object-fit: cover;
    }

    .image-preview.active {
        display: block;
    }

    /* Location */
    .location-section {
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
    }

    .get-location-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .get-location-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .location-display {
        margin-top: 16px;
        padding: 12px 16px;
        background: white;
        border-radius: 8px;
        display: none;
    }

    .location-display.active {
        display: block;
    }

    .location-display i {
        color: #16a34a;
        margin-right: 8px;
    }

    /* Submit Button */
    .submit-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }

    /* Track Link */
    .track-link {
        text-align: center;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .track-link a {
        color: #1e3a5f;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .track-link a:hover {
        color: #ef4444;
    }

    /* Error Styles */
    .is-invalid {
        border-color: #ef4444 !important;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 13px;
        margin-top: 6px;
    }

    /* Alert */
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .alert i {
        font-size: 20px;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<section class="grievance-page">
    <div class="container grievance-container">
        <div class="page-header">
            <h1><i class="fas fa-bullhorn"></i> Grievance Redressal</h1>
            <p>Report issues in your area and we'll work to resolve them promptly</p>
        </div>

        <div class="grievance-form-card">
            <div class="form-header">
                <h2><i class="fas fa-file-alt"></i> Submit Your Complaint</h2>
                <p>Fill in the details below to report an issue. All fields marked with * are required.</p>
            </div>

            <div class="form-body">
                @if($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <form action="{{ route('grievance.store') }}" method="POST" enctype="multipart/form-data" id="grievanceForm">
                    @csrf

                    <!-- Personal Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-user"></i> Personal Information
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Full Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-input @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" placeholder="Enter your name" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number <span class="required">*</span></label>
                                <input type="tel" name="phone" class="form-input @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}" placeholder="10-digit mobile number" 
                                       maxlength="10" pattern="[0-9]{10}" required>
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email (Optional)</label>
                                <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" placeholder="your@email.com">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-input @error('address') is-invalid @enderror" 
                                       value="{{ old('address') }}" placeholder="Your address">
                                @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Issue Category -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-tag"></i> Issue Category <span class="required">*</span>
                        </div>
                        <div class="category-grid">
                            @foreach($categories as $key => $label)
                            <div class="category-option">
                                <input type="radio" name="category" id="cat_{{ $key }}" value="{{ $key }}" 
                                       {{ old('category') == $key ? 'checked' : '' }} required>
                                <label for="cat_{{ $key }}">
                                    <i class="fas {{ $key === 'water_leakage' ? 'fa-tint' : ($key === 'road_damage' ? 'fa-road' : ($key === 'electricity' ? 'fa-bolt' : ($key === 'sanitation' ? 'fa-broom' : ($key === 'drainage' ? 'fa-water' : ($key === 'streetlight' ? 'fa-lightbulb' : 'fa-question-circle'))))) }}"></i>
                                    <span>{{ $label }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('category')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-align-left"></i> Problem Description <span class="required">*</span>
                        </div>
                        <div class="form-group">
                            <textarea name="description" class="form-textarea @error('description') is-invalid @enderror" 
                                      placeholder="Describe the issue in detail. Include relevant information like when you noticed it, severity, etc." 
                                      required minlength="20">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-camera"></i> Upload Photo (Optional)
                        </div>
                        <div class="image-upload">
                            <div class="image-upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click or drag to upload an image</p>
                                <span class="hint">JPEG, PNG, GIF up to 5MB</span>
                            </div>
                            <input type="file" name="image" id="imageInput" accept="image/*">
                            <div class="image-preview" id="imagePreview">
                                <img id="previewImg" src="" alt="Preview">
                            </div>
                        </div>
                        @error('image')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Location -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-map-marker-alt"></i> Issue Location (Optional)
                        </div>
                        <div class="location-section">
                            <button type="button" class="get-location-btn" id="getLocationBtn">
                                <i class="fas fa-crosshairs"></i>
                                Get Current Location
                            </button>
                            <div class="location-display" id="locationDisplay">
                                <i class="fas fa-check-circle"></i>
                                <span id="locationText">Location captured</span>
                            </div>
                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                            <input type="hidden" name="location_address" id="locationAddress" value="{{ old('location_address') }}">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Complaint
                    </button>
                </form>

                <div class="track-link">
                    <a href="{{ route('grievance.track') }}">
                        <i class="fas fa-search"></i>
                        Already submitted? Track your complaint status
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Image preview
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const uploadArea = document.getElementById('uploadArea');

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.classList.add('active');
                uploadArea.style.display = 'none';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Get location
    const getLocationBtn = document.getElementById('getLocationBtn');
    const locationDisplay = document.getElementById('locationDisplay');
    const locationText = document.getElementById('locationText');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationAddressInput = document.getElementById('locationAddress');

    getLocationBtn.addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting location...';
        getLocationBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                latitudeInput.value = lat;
                longitudeInput.value = lng;

                // Try to get address using reverse geocoding
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        locationAddressInput.value = address;
                        locationText.textContent = address.length > 60 ? address.substring(0, 60) + '...' : address;
                    })
                    .catch(() => {
                        locationText.textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                    });

                locationDisplay.classList.add('active');
                getLocationBtn.innerHTML = '<i class="fas fa-check"></i> Location captured';
                getLocationBtn.style.background = 'linear-gradient(135deg, #16a34a 0%, #22c55e 100%)';

                setTimeout(() => {
                    getLocationBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Update Location';
                    getLocationBtn.disabled = false;
                }, 2000);
            },
            function(error) {
                alert('Unable to get your location. Please try again or enter location manually.');
                getLocationBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Get Current Location';
                getLocationBtn.disabled = false;
            }
        );
    });

    // Phone number validation
    document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '').substring(0, 10);
    });
</script>
@endpush
