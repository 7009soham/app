<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $settings['site_description'] ?? 'Official Gram Panchayat Portal' }}">
    <title>@yield('title', $settings['site_name'] ?? 'Gram Panchayat')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="{{ route('home') }}" class="logo">
                    <i class="fas fa-landmark"></i>
                    <span>{{ $settings['site_name'] ?? 'Gram Panchayat' }}</span>
                </a>
                
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
                <ul class="nav-links" id="navLinks">
                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
                    <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="{{ route('citizen.login') }}" class="btn btn-primary">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3><i class="fas fa-landmark"></i> {{ $settings['site_name'] ?? 'Gram Panchayat' }}</h3>
                    <p>{{ $settings['site_description'] ?? 'Serving our community with dedication and transparency.' }}</p>
                    <div class="social-links">
                        @if(!empty($settings['facebook_url']))
                            <a href="{{ $settings['facebook_url'] }}" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if(!empty($settings['twitter_url']))
                            <a href="{{ $settings['twitter_url'] }}" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        @endif
                        @if(!empty($settings['instagram_url']))
                            <a href="{{ $settings['instagram_url'] }}" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        @endif
                        @if(!empty($settings['youtube_url']))
                            <a href="{{ $settings['youtube_url'] }}" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        @endif
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        @foreach($quickLinks ?? [] as $link)
                            <li>
                                <a href="{{ $link->url }}" @if($link->open_new_tab) target="_blank" @endif>
                                    {{ $link->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $settings['address'] ?? 'Gram Panchayat Office' }}</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:{{ $settings['contact_phone'] ?? '' }}">{{ $settings['contact_phone'] ?? '' }}</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:{{ $settings['contact_email'] ?? '' }}">{{ $settings['contact_email'] ?? '' }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $settings['site_name'] ?? 'Gram Panchayat' }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
