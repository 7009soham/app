<!-- Hero Slider -->
<section class="hero-slider" id="heroSlider">
    <div class="slider-container">
        @forelse($sliders as $index => $slider)
            <div class="slide {{ $index === 0 ? 'active' : '' }}" style="background-image: url('{{ $slider->image_url }}');">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    @if($slider->title)
                        <h1>{{ $slider->title }}</h1>
                    @endif
                    @if($slider->subtitle)
                        <p>{{ $slider->subtitle }}</p>
                    @endif
                    @if($slider->link && $slider->button_text)
                        <a href="{{ $slider->link }}" class="btn btn-primary btn-lg">{{ $slider->button_text }}</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="slide active" style="background: linear-gradient(135deg, #1a365d 0%, #2d5a87 100%);">
                <div class="slide-content">
                    <h1>{{ __('messages.welcome_to') }} {{ $settings['site_name'] ?? 'Gram Panchayat' }}</h1>
                    <p>{{ $settings['site_tagline'] ?: __('messages.serving_community') }}</p>
                    <a href="{{ route('citizen.login') }}" class="btn btn-primary btn-lg">{{ __('messages.login_to_pay_tax') }}</a>
                </div>
            </div>
        @endforelse
    </div>

    @if(count($sliders) > 1)
        <div class="slider-nav">
            <button class="slider-btn prev" id="prevSlide"><i class="fas fa-chevron-left"></i></button>
            <button class="slider-btn next" id="nextSlide"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="slider-dots">
            @foreach($sliders as $index => $slider)
                <button class="dot {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}"></button>
            @endforeach
        </div>
    @endif
</section>

<!-- Quick Stats -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-content">
                    <span class="stat-number" data-target="5000">0</span>
                    <span class="stat-label">{{ __('messages.citizens_served') }}</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-file-invoice-dollar"></i>
                <div class="stat-content">
                    <span class="stat-number" data-target="10000">0</span>
                    <span class="stat-label">{{ __('messages.payments_processed') }}</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <div class="stat-content">
                    <span class="stat-number" data-target="100">0</span>
                    <span class="stat-label">% {{ __('messages.secure') }}</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-headset"></i>
                <div class="stat-content">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">{{ __('messages.support_available') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section" id="services">
    <div class="container">
        <div class="section-header">
            <h2>{{ __('messages.our_services') }}</h2>
            <p>{{ __('messages.services_description') }}</p>
        </div>

        <div class="services-grid">
            @foreach($taxTypes as $taxType)
                @if(strtolower(trim($taxType->name)) === 'house tax')
                    @continue
                @endif
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas {{ $taxType->icon ?? 'fa-receipt' }}"></i>
                    </div>
                    <h3>{{ $taxType->name }}</h3>
                    <p>{{ $taxType->description }}</p>
                    <a href="{{ route('citizen.login') }}" class="btn btn-outline">{{ __('messages.login_to_pay') }}</a>
                </div>
            @endforeach

            <!-- Grievance Redressal Card -->
            <div class="service-card grievance-card">
                <div class="service-icon" style="background: linear-gradient(135deg, #ef4444, #f87171);">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3>{{ __('messages.grievance_redressal') }}</h3>
                <p>{{ __('messages.grievance_description') }}</p>
                <a href="{{ route('grievance.create') }}" class="btn btn-outline">{{ __('messages.report_issue') }}</a>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-us-section">
    <div class="container">
        <div class="section-header">
            <h2>{{ __('messages.seamless_experience') }}</h2>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>{{ __('messages.quick_easy') }}</h3>
                <p>{{ __('messages.quick_easy_desc') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>{{ __('messages.secure_100') }}</h3>
                <p>{{ __('messages.secure_100_desc') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3>{{ __('messages.instant_receipt') }}</h3>
                <p>{{ __('messages.instant_receipt_desc') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h3>{{ __('messages.payment_history') }}</h3>
                <p>{{ __('messages.payment_history_desc') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>{{ __('messages.need_help') }}</h2>
            <p>{{ __('messages.support_team') }}</p>
            <div class="cta-buttons">
                <a href="tel:{{ $settings['contact_phone'] ?? '' }}" class="btn btn-white">
                    <i class="fas fa-phone"></i> {{ __('messages.call_us') }}
                </a>
                <a href="mailto:{{ $settings['contact_email'] ?? '' }}" class="btn btn-outline-white">
                    <i class="fas fa-envelope"></i> {{ __('messages.email_us') }}
                </a>
            </div>
        </div>
    </div>
</section>
