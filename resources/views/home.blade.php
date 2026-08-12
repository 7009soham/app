@extends('layouts.app')

@section('title', ($settings['site_name'] ?? 'Gram Panchayat') . (!empty($settings['site_tagline']) ? ' - ' . $settings['site_tagline'] : ''))

@section('content')
    @php
        // Nothing is looked up here. The number is handed to the OTP login and
        // the citizen sees their own bills only after verifying the phone, so
        // no name, address or amount is disclosed to whoever types it.
        $lookupAction = route('citizen.login');
    @endphp

    <!-- Hero Slider -->
    <section class="hero-slider" id="heroSlider">
        <div class="slider-container">
            @forelse($sliders as $index => $slider)
                <div class="slide {{ $index === 0 ? 'active' : '' }}">
                    <div class="slide-bg" style="background-image: url('{{ $slider->image_url }}');"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <span class="slide-kicker">{{ __('messages.official_digital_portal') }}</span>
                        @if($slider->title)
                            <h1>{{ $slider->title }}</h1>
                        @endif
                        @if($slider->subtitle)
                            <p>{{ $slider->subtitle }}</p>
                        @endif
                        @if($slider->link && $slider->button_text)
                            <a href="{{ $slider->link }}" class="btn btn-hero btn-lg">{{ $slider->button_text }} <span class="btn-arrow"><i class="fas fa-arrow-right" aria-hidden="true"></i></span></a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="slide active">
                    <div class="slide-content">
                        <span class="slide-kicker">{{ __('messages.official_digital_portal') }}</span>
                        <h1>{{ __('messages.welcome_to') }} {{ $settings['site_name'] ?? 'Gram Panchayat' }}</h1>
                        <p>{{ $settings['site_tagline'] ?: __('messages.serving_community') }}</p>
                        <a href="{{ route('citizen.login') }}" class="btn btn-hero btn-lg">{{ __('messages.login_to_pay_tax') }} <span class="btn-arrow"><i class="fas fa-arrow-right" aria-hidden="true"></i></span></a>
                    </div>
                </div>
            @endforelse
        </div>
        
        @if(count($sliders) > 1)
            <div class="slider-nav">
                <button type="button" class="slider-btn prev" id="prevSlide" aria-label="{{ __('messages.previous') }}">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="slider-btn next" id="nextSlide" aria-label="{{ __('messages.next') }}">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
            <div class="slider-dots">
                @foreach($sliders as $index => $slider)
                    <button type="button" class="dot {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}"
                            aria-label="{{ __('messages.go_to_slide', ['number' => $index + 1]) }}"
                            @if($index === 0) aria-current="true" @endif></button>
                @endforeach
                {{-- WCAG 2.2.2: the slides start moving on their own and last longer
                     than five seconds, so a mechanism to stop them is a Level A
                     requirement, not a nicety. --}}
                <button type="button" class="slider-pause" id="sliderPause" aria-pressed="false">
                    <i class="fas fa-pause" aria-hidden="true"></i>
                    <span class="sr-only">{{ __('messages.pause_slideshow') }}</span>
                </button>
            </div>
        @endif
    </section>

    <!-- Find my bill -->
    <section class="bill-lookup" aria-labelledby="billLookupHeading">
        <div class="container">
            <form class="lookup-card" method="GET" action="{{ $lookupAction }}" data-reveal>
                <h2 class="lookup-heading" id="billLookupHeading">{{ __('messages.find_my_bills') }}</h2>
                <p class="lookup-hint">{{ __('messages.find_my_bills_hint') }}</p>

                <div class="lookup-field">
                    <span class="lookup-prefix" aria-hidden="true">+91</span>
                    <label class="sr-only" for="lookupPhone">{{ __('messages.mobile_number') }}</label>
                    <input type="tel"
                           id="lookupPhone"
                           name="phone"
                           class="lookup-input"
                           inputmode="numeric"
                           autocomplete="tel-national"
                           maxlength="10"
                           pattern="[6-9][0-9]{9}"
                           placeholder="{{ __('messages.mobile_number') }}"
                           required>
                    <button type="submit" class="lookup-submit">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <span>{{ __('messages.find') }}</span>
                    </button>
                </div>

                <p class="lookup-note">
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    {{ __('messages.find_my_bills_secure') }}
                </p>
            </form>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="quick-actions" aria-label="{{ __('messages.quick_actions') }}">
        <div class="container">
            <div class="qa-card" data-reveal>
                <a href="{{ route('citizen.login') }}" class="qa-item">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-house" aria-hidden="true"></i></span>
                    <span>{{ __('messages.property_tax') }}</span>
                </a>
                <a href="{{ route('citizen.login') }}" class="qa-item">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-droplet" aria-hidden="true"></i></span>
                    <span>{{ __('messages.water_tax') }}</span>
                </a>
                <a href="{{ route('grievance.create') }}" class="qa-item qa-item--new" data-badge="{{ __('messages.new') }}">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-bullhorn" aria-hidden="true"></i></span>
                    <span>{{ __('messages.grievance') }}</span>
                </a>
                <a href="{{ route('digital-services') }}" class="qa-item">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-file-lines" aria-hidden="true"></i></span>
                    <span>{{ __('messages.certificates') }}</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Quick Stats -->
    <section class="stats-section">
        <div class="container">
            {{-- These were hard-coded as 5,000 citizens served and 10,000 payments
                 processed. Both were invented, and on a tax portal an invented
                 figure costs more trust than it buys. The roll is a real count;
                 the other three describe the service rather than its usage. --}}
            <div class="stats-strip" data-reveal>
                @if(!empty($stats['properties_on_roll']))
                    <div class="stat-card">
                        <span class="icon-chip icon-chip--tint"><i class="fas fa-house-chimney" aria-hidden="true"></i></span>
                        <div class="stat-content">
                            <span class="stat-number" data-target="{{ $stats['properties_on_roll'] }}">0</span>
                            <span class="stat-label">{{ __('messages.properties_on_roll') }}</span>
                        </div>
                    </div>
                @endif
                <div class="stat-card">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-list-check" aria-hidden="true"></i></span>
                    <div class="stat-content">
                        <span class="stat-number" data-target="{{ $stats['services_online'] ?? 3 }}">0</span>
                        <span class="stat-label">{{ __('messages.services_online') }}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-lock" aria-hidden="true"></i></span>
                    <div class="stat-content">
                        <span class="stat-number">256<span class="stat-unit">-bit</span></span>
                        <span class="stat-label">{{ __('messages.payment_security') }}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-clock" aria-hidden="true"></i></span>
                    <div class="stat-content">
                        <span class="stat-number">24<span class="stat-unit">/7</span></span>
                        <span class="stat-label">{{ __('messages.online_always') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="services">
        <div class="container">
            <div class="section-header" data-reveal>
                <span class="section-eyebrow">{{ __('messages.services') }}</span>
                <h2>{{ __('messages.our_services') }}</h2>
                <p>{{ __('messages.services_description') }}</p>
                <a href="{{ route('digital-services') }}" class="section-link">{{ __('messages.view_all') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>

            <div class="services-grid">
                @foreach($taxTypes as $taxType)
                    @if(strtolower(trim($taxType->name)) === 'house tax')
                        @continue
                    @endif
                    <a href="{{ route('citizen.login') }}" class="service-card" data-reveal>
                        <span class="icon-chip icon-chip--gradient"><i class="fas {{ $taxType->icon ?? 'fa-receipt' }}" aria-hidden="true"></i></span>
                        <div class="service-body">
                            <h3>{{ $taxType->name }}</h3>
                            <p>{{ $taxType->description }}</p>
                        </div>
                        <span class="service-cta">{{ __('messages.login_to_pay') }}</span>
                        <span class="service-go"><i class="fas fa-chevron-right" aria-hidden="true"></i></span>
                    </a>
                @endforeach

                <!-- Grievance Redressal Card -->
                <a href="{{ route('grievance.create') }}" class="service-card" data-reveal>
                    <span class="icon-chip icon-chip--danger"><i class="fas fa-bullhorn" aria-hidden="true"></i></span>
                    <div class="service-body">
                        <h3>{{ __('messages.grievance_redressal') }}</h3>
                        <p>{{ __('messages.grievance_description') }}</p>
                    </div>
                    <span class="service-cta">{{ __('messages.report_issue') }}</span>
                    <span class="service-go"><i class="fas fa-chevron-right" aria-hidden="true"></i></span>
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-us-section">
        <div class="container">
            <div class="section-header" data-reveal>
                <h2>{{ __('messages.seamless_experience') }}</h2>
            </div>

            <div class="features-grid">
                <div class="feature-card" data-reveal>
                    <span class="icon-chip icon-chip--tint feature-chip"><i class="fas fa-bolt" aria-hidden="true"></i></span>
                    <h3>{{ __('messages.quick_easy') }}</h3>
                    <p>{{ __('messages.quick_easy_desc') }}</p>
                </div>
                <div class="feature-card" data-reveal>
                    <span class="icon-chip icon-chip--tint feature-chip"><i class="fas fa-shield-alt" aria-hidden="true"></i></span>
                    <h3>{{ __('messages.secure_100') }}</h3>
                    <p>{{ __('messages.secure_100_desc') }}</p>
                </div>
                <div class="feature-card" data-reveal>
                    <span class="icon-chip icon-chip--tint feature-chip"><i class="fas fa-receipt" aria-hidden="true"></i></span>
                    <h3>{{ __('messages.instant_receipt') }}</h3>
                    <p>{{ __('messages.instant_receipt_desc') }}</p>
                </div>
                <div class="feature-card" data-reveal>
                    <span class="icon-chip icon-chip--tint feature-chip"><i class="fas fa-history" aria-hidden="true"></i></span>
                    <h3>{{ __('messages.payment_history') }}</h3>
                    <p>{{ __('messages.payment_history_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content" data-reveal>
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
@endsection

@push('scripts')
<script>
    // Slider functionality
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => {
            dot.classList.remove('active');
            dot.removeAttribute('aria-current');
        });

        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
            dots[currentSlide].setAttribute('aria-current', 'true');
        }
    }

    if (slides.length > 1) {
        document.getElementById('prevSlide')?.addEventListener('click', () => showSlide(currentSlide - 1));
        document.getElementById('nextSlide')?.addEventListener('click', () => showSlide(currentSlide + 1));
        dots.forEach((dot, index) => dot.addEventListener('click', () => showSlide(index)));

        // Auto-play, but stoppable. WCAG 2.2.2 requires a pause mechanism for
        // motion that starts on its own and runs longer than five seconds, and
        // anyone who has asked the OS for reduced motion never gets it started.
        const pauseBtn = document.getElementById('sliderPause');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let timer = null;

        function play() {
            if (timer === null) {
                timer = setInterval(() => showSlide(currentSlide + 1), 5000);
            }
            pauseBtn?.setAttribute('aria-pressed', 'false');
            pauseBtn?.querySelector('i')?.classList.replace('fa-play', 'fa-pause');
        }

        function pause() {
            clearInterval(timer);
            timer = null;
            pauseBtn?.setAttribute('aria-pressed', 'true');
            pauseBtn?.querySelector('i')?.classList.replace('fa-pause', 'fa-play');
        }

        pauseBtn?.addEventListener('click', () => (timer === null ? play() : pause()));

        if (reduceMotion) {
            pause();
        } else {
            play();
        }

        // Advancing the hero under someone who is reading it or tabbing through
        // it is the same problem the pause button solves, so stop on both.
        const sliderEl = document.getElementById('heroSlider');
        sliderEl.addEventListener('mouseenter', () => timer !== null && pause());
        sliderEl.addEventListener('focusin', () => timer !== null && pause());

        // Touch swipe (arrows are hidden on mobile)
        let touchStartX = 0;
        sliderEl.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        sliderEl.addEventListener('touchend', (e) => {
            const dx = e.changedTouches[0].screenX - touchStartX;
            if (Math.abs(dx) > 50) showSlide(currentSlide + (dx < 0 ? 1 : -1));
        }, { passive: true });
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

    // Scroll reveals (gated so content is never hidden without JS)
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (revealEls.length && 'IntersectionObserver' in window
        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('reveal-ready');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -5% 0px' });
        revealEls.forEach((el, i) => {
            el.style.transitionDelay = `${(i % 4) * 60}ms`;
            revealObserver.observe(el);
        });
    }

    // Mobile menu
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    
    mobileMenuBtn?.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        mobileMenuBtn.classList.toggle('active');
    });
</script>
@endpush
