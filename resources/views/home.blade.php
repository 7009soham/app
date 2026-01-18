@extends('layouts.app')

@section('title', ($settings['site_name'] ?? 'Gram Panchayat') . ' - ' . ($settings['site_tagline'] ?? 'Welcome'))

@section('content')
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
                        <h1>Welcome to {{ $settings['site_name'] ?? 'Gram Panchayat' }}</h1>
                        <p>{{ $settings['site_tagline'] ?? 'Serving Our Community' }}</p>
                        <a href="{{ route('citizen.login') }}" class="btn btn-primary btn-lg">Login to Pay Tax</a>
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
                        <span class="stat-label">Citizens Served</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <div class="stat-content">
                        <span class="stat-number" data-target="10000">0</span>
                        <span class="stat-label">Payments Processed</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-content">
                        <span class="stat-number" data-target="100">0</span>
                        <span class="stat-label">% Secure</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-headset"></i>
                    <div class="stat-content">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Support Available</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="services">
        <div class="container">
            <div class="section-header">
                <h2>Our Services</h2>
                <p>Pay your taxes conveniently and access various government services online</p>
            </div>
            
            <div class="services-grid">
                @foreach($taxTypes as $taxType)
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas {{ $taxType->icon ?? 'fa-receipt' }}"></i>
                        </div>
                        <h3>{{ $taxType->name }}</h3>
                        <p>{{ $taxType->description }}</p>
                        <a href="{{ route('citizen.login') }}" class="btn btn-outline">Login to Pay</a>
                    </div>
                @endforeach

                <!-- Grievance Redressal Card -->
                <div class="service-card grievance-card">
                    <div class="service-icon" style="background: linear-gradient(135deg, #ef4444, #f87171);">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>Grievance Redressal</h3>
                    <p>Report issues like water leakage, road damage, or other problems. We'll address your concerns promptly.</p>
                    <a href="{{ route('grievance.create') }}" class="btn btn-outline">Report Issue</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-us-section">
        <div class="container">
            <div class="section-header">
                <h2>Experience seamless and secure tax payment services</h2>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Quick & Easy</h3>
                    <p>Pay your taxes in just a few clicks from anywhere, anytime</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>100% Secure</h3>
                    <p>Your transactions are protected with industry-standard encryption</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3>Instant Receipt</h3>
                    <p>Get digital receipts instantly after successful payment</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>Payment History</h3>
                    <p>Track all your past payments and download receipts anytime</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Need Help?</h2>
                <p>Our support team is available to assist you with any queries</p>
                <div class="cta-buttons">
                    <a href="tel:{{ $settings['contact_phone'] ?? '' }}" class="btn btn-white">
                        <i class="fas fa-phone"></i> Call Us
                    </a>
                    <a href="mailto:{{ $settings['contact_email'] ?? '' }}" class="btn btn-outline-white">
                        <i class="fas fa-envelope"></i> Email Us
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Slider functionality
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }
    
    if (slides.length > 1) {
        document.getElementById('prevSlide')?.addEventListener('click', () => showSlide(currentSlide - 1));
        document.getElementById('nextSlide')?.addEventListener('click', () => showSlide(currentSlide + 1));
        dots.forEach((dot, index) => dot.addEventListener('click', () => showSlide(index)));
        
        // Auto-play
        setInterval(() => showSlide(currentSlide + 1), 5000);
    }
    
    // Counter animation
    const counters = document.querySelectorAll('.stat-number[data-target]');
    const observerOptions = { threshold: 0.5 };
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.target);
                let current = 0;
                const increment = target / 50;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.ceil(current).toLocaleString('en-IN');
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target.toLocaleString('en-IN');
                    }
                };
                updateCounter();
                counterObserver.unobserve(counter);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => counterObserver.observe(counter));
    
    // Mobile menu
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    
    mobileMenuBtn?.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        mobileMenuBtn.classList.toggle('active');
    });
</script>
@endpush
