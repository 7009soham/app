@extends('citizen.layout')

@section('title', app()->getLocale() == 'hi' ? 'नई शिकायत दर्ज करें' : (app()->getLocale() == 'mr' ? 'नवीन तक्रार नोंदवा' : 'Submit New Grievance'))
@section('page-title', app()->getLocale() == 'hi' ? 'नई शिकायत दर्ज करें' : (app()->getLocale() == 'mr' ? 'नवीन तक्रार नोंदवा' : 'Submit New Grievance'))

@section('content')
<style>
    .form-card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 32px;
        box-shadow: var(--shadow);
        max-width: 800px;
        margin: 0 auto;
    }

    .form-header {
        margin-bottom: 32px;
    }

    .form-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-subtitle {
        color: var(--text-secondary);
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-primary);
        font-size: 14px;
    }

    .form-label.required::after {
        content: ' *';
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        font-size: 14px;
        font-family: inherit;
        transition: var(--transition);
        background: var(--surface);
        color: var(--text-primary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
    }

    .form-control:disabled {
        background: var(--surface-secondary);
        cursor: not-allowed;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .form-help {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 4px;
    }

    .form-error {
        font-size: 12px;
        color: #dc2626;
        margin-top: 4px;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
    }

    .category-card {
        position: relative;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 16px;
        cursor: pointer;
        transition: var(--transition);
    }

    .category-card:hover {
        border-color: var(--primary-light);
        background: rgba(30, 58, 95, 0.02);
    }

    .category-card input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .category-card input[type="radio"]:checked + .category-content {
        border-color: var(--primary);
    }

    .category-card input[type="radio"]:checked ~ .category-label {
        color: var(--primary);
        font-weight: 600;
    }

    .category-icon {
        font-size: 24px;
        margin-bottom: 8px;
        color: var(--primary);
    }

    .category-label {
        font-size: 14px;
        color: var(--text-primary);
        transition: var(--transition);
    }

    .category-card input[type="radio"]:checked {
        accent-color: var(--primary);
    }

    .category-card.selected {
        border-color: var(--primary);
        background: rgba(30, 58, 95, 0.05);
    }

    .file-upload-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
        width: 100%;
    }

    .file-upload-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 24px;
        border: 2px dashed var(--border);
        border-radius: var(--radius);
        cursor: pointer;
        transition: var(--transition);
        background: var(--surface-secondary);
    }

    .file-upload-label:hover {
        border-color: var(--primary);
        background: rgba(30, 58, 95, 0.02);
    }

    .file-upload-wrapper input[type="file"] {
        position: absolute;
        left: -9999px;
    }

    .image-preview {
        margin-top: 16px;
        border-radius: var(--radius);
        overflow: hidden;
        max-width: 300px;
    }

    .image-preview img {
        width: 100%;
        height: auto;
        display: block;
    }

    .location-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: var(--transition);
    }

    .location-button:hover {
        background: var(--primary-light);
    }

    .location-button:disabled {
        background: var(--text-muted);
        cursor: not-allowed;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--border);
    }

    .btn {
        padding: 12px 24px;
        border-radius: var(--radius);
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-light);
    }

    .btn-secondary {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-primary);
    }

    .btn-secondary:hover {
        background: var(--surface-secondary);
    }

    @media (max-width: 640px) {
        .form-card {
            padding: 20px;
        }

        .category-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="form-card">
    <div class="form-header">
        <h2 class="form-title">
            {{ app()->getLocale() == 'hi' ? 'नई शिकायत दर्ज करें' : (app()->getLocale() == 'mr' ? 'नवीन तक्रार नोंदवा' : 'Submit New Grievance') }}
        </h2>
        <p class="form-subtitle">
            {{ app()->getLocale() == 'hi' ? 'कृपया अपनी शिकायत का विवरण प्रदान करें। हम इसे जल्द से जल्द हल करेंगे।' : (app()->getLocale() == 'mr' ? 'कृपया तुमच्या तक्रारीचा तपशील प्रदान करा. आम्ही लवकरात लवकर ती सोडवू.' : 'Please provide details of your grievance. We will resolve it as soon as possible.') }}
        </p>
    </div>

    <form action="{{ route('citizen.grievances.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Category Selection -->
        <div class="form-group">
            <label class="form-label required">
                {{ app()->getLocale() == 'hi' ? 'श्रेणी' : (app()->getLocale() == 'mr' ? 'श्रेणी' : 'Category') }}
            </label>
            <div class="category-grid">
                @foreach($categories as $key => $label)
                    <label class="category-card {{ old('category') == $key ? 'selected' : '' }}">
                        <input type="radio" name="category" value="{{ $key }}" {{ old('category') == $key ? 'checked' : '' }} required>
                        <div class="category-icon">
                            <i class="fas fa-{{ match($key) {
                                'water_leakage' => 'tint',
                                'road_damage' => 'road',
                                'electricity' => 'bolt',
                                'sanitation' => 'trash',
                                'drainage' => 'water',
                                'streetlight' => 'lightbulb',
                                default => 'circle'
                            } }}"></i>
                        </div>
                        <div class="category-label">{{ $label }}</div>
                    </label>
                @endforeach
            </div>
            @error('category')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Description -->
        <div class="form-group">
            <label class="form-label required" for="description">
                {{ app()->getLocale() == 'hi' ? 'विवरण' : (app()->getLocale() == 'mr' ? 'तपशील' : 'Description') }}
            </label>
            <textarea 
                name="description" 
                id="description" 
                class="form-control" 
                placeholder="{{ app()->getLocale() == 'hi' ? 'अपनी शिकायत का विस्तृत विवरण दें (कम से कम 20 अक्षर)' : (app()->getLocale() == 'mr' ? 'तुमच्या तक्रारीचा तपशीलवार वर्णन करा (किमान 20 अक्षरे)' : 'Provide a detailed description of your grievance (minimum 20 characters)') }}"
                required
            >{{ old('description') }}</textarea>
            @error('description')
                <div class="form-error">{{ $message }}</div>
            @else
                <div class="form-help">
                    {{ app()->getLocale() == 'hi' ? 'कम से कम 20 अक्षर, अधिकतम 2000 अक्षर' : (app()->getLocale() == 'mr' ? 'किमान 20 अक्षरे, कमाल 2000 अक्षरे' : 'Minimum 20 characters, maximum 2000 characters') }}
                </div>
            @enderror
        </div>

        <!-- Image Upload -->
        <div class="form-group">
            <label class="form-label" for="image">
                {{ app()->getLocale() == 'hi' ? 'फोटो अपलोड करें (वैकल्पिक)' : (app()->getLocale() == 'mr' ? 'फोटो अपलोड करा (पर्यायी)' : 'Upload Photo (Optional)') }}
            </label>
            <div class="file-upload-wrapper">
                <label for="image" class="file-upload-label">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: var(--primary);"></i>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 4px;">
                            {{ app()->getLocale() == 'hi' ? 'फोटो अपलोड करने के लिए क्लिक करें' : (app()->getLocale() == 'mr' ? 'फोटो अपलोड करण्यासाठी क्लिक करा' : 'Click to upload photo') }}
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary);">
                            {{ app()->getLocale() == 'hi' ? 'JPG, PNG, GIF (अधिकतम 5MB)' : (app()->getLocale() == 'mr' ? 'JPG, PNG, GIF (जास्तीत जास्त 5MB)' : 'JPG, PNG, GIF (max 5MB)') }}
                        </div>
                    </div>
                </label>
                <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(this)">
            </div>
            <div id="imagePreview" class="image-preview" style="display: none;"></div>
            @error('image')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Location -->
        <div class="form-group">
            <label class="form-label">
                {{ app()->getLocale() == 'hi' ? 'स्थान (वैकल्पिक)' : (app()->getLocale() == 'mr' ? 'स्थान (पर्यायी)' : 'Location (Optional)') }}
            </label>
            <button type="button" class="location-button" onclick="getLocation()">
                <i class="fas fa-map-marker-alt"></i>
                <span id="locationButtonText">
                    {{ app()->getLocale() == 'hi' ? 'अपना स्थान प्राप्त करें' : (app()->getLocale() == 'mr' ? 'तुमचे स्थान मिळवा' : 'Get My Location') }}
                </span>
            </button>
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
            <input type="text" name="location_address" id="location_address" class="form-control" style="margin-top: 12px;" 
                   placeholder="{{ app()->getLocale() == 'hi' ? 'स्थान का पता' : (app()->getLocale() == 'mr' ? 'स्थानाचा पत्ता' : 'Location address') }}" 
                   value="{{ old('location_address') }}" readonly>
            @error('latitude')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
                {{ app()->getLocale() == 'hi' ? 'शिकायत दर्ज करें' : (app()->getLocale() == 'mr' ? 'तक्रार सबमिट करा' : 'Submit Grievance') }}
            </button>
            <a href="{{ route('citizen.grievances.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                {{ app()->getLocale() == 'hi' ? 'रद्द करें' : (app()->getLocale() == 'mr' ? 'रद्द करा' : 'Cancel') }}
            </a>
        </div>
    </form>
</div>

<script>
    // Image preview
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.innerHTML = '';
            preview.style.display = 'none';
        }
    }

    // Category selection visual feedback
    document.querySelectorAll('.category-card input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('selected');
            });
            if (this.checked) {
                this.closest('.category-card').classList.add('selected');
            }
        });
    });

    // Geolocation
    function getLocation() {
        const button = event.target.closest('.location-button');
        const buttonText = document.getElementById('locationButtonText');
        
        if (!navigator.geolocation) {
            alert('{{ app()->getLocale() == 'hi' ? 'जियोलोकेशन आपके ब्राउज़र द्वारा समर्थित नहीं है' : (app()->getLocale() == 'mr' ? 'भौगोलिक स्थान तुमच्या ब्राउझरद्वारे समर्थित नाही' : 'Geolocation is not supported by your browser') }}');
            return;
        }

        button.disabled = true;
        buttonText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ app()->getLocale() == 'hi' ? 'प्राप्त कर रहे हैं...' : (app()->getLocale() == 'mr' ? 'मिळवत आहे...' : 'Getting location...') }}';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                // Reverse geocoding (optional - requires API)
                document.getElementById('location_address').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                
                buttonText.innerHTML = '<i class="fas fa-check"></i> {{ app()->getLocale() == 'hi' ? 'स्थान प्राप्त किया गया' : (app()->getLocale() == 'mr' ? 'स्थान मिळाले' : 'Location captured') }}';
                button.disabled = false;
            },
            function(error) {
                alert('{{ app()->getLocale() == 'hi' ? 'स्थान प्राप्त करने में त्रुटि: ' : (app()->getLocale() == 'mr' ? 'स्थान मिळवताना त्रुटी: ' : 'Error getting location: ') }}' + error.message);
                buttonText.innerHTML = '<i class="fas fa-map-marker-alt"></i> {{ app()->getLocale() == 'hi' ? 'अपना स्थान प्राप्त करें' : (app()->getLocale() == 'mr' ? 'तुमचे स्थान मिळवा' : 'Get My Location') }}';
                button.disabled = false;
            }
        );
    }
</script>
@endsection
