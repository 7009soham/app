<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $settings['site_description'] ?? 'Official Gram Panchayat Portal' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $settings['site_name'] ?? 'Gram Panchayat')</title>

    <!-- Progressive Web App -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e3a5f">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Neral GP">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/favicon-16.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ \App\Helpers\Asset::versioned('css/app.css') }}">
    
    <!-- Language Selector Styles -->
    <style>
        /* Language Modal Styles */
        .language-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .language-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .language-modal {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 24px;
            padding: 40px;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.8) translateY(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .language-modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #f97316, #ea580c, #c2410c);
        }

        .language-modal-overlay.active .language-modal {
            transform: scale(1) translateY(0);
        }

        .language-modal-header {
            margin-bottom: 30px;
        }

        .language-modal-header .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.3);
        }

        .language-modal-header .icon i {
            font-size: 28px;
            color: white;
        }

        .language-modal-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            font-family: 'Inter', 'Noto Sans Devanagari', sans-serif;
        }

        .language-modal-header p {
            color: #64748b;
            font-size: 15px;
            font-family: 'Inter', 'Noto Sans Devanagari', sans-serif;
        }

        .language-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 25px;
        }

        .language-option {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            position: relative;
        }

        .language-option:hover {
            border-color: #f97316;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.15);
        }

        .language-option.selected {
            border-color: #f97316;
            background: linear-gradient(145deg, #fff7ed, #ffedd5);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.2);
        }

        .language-option.selected::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            background: #f97316;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .language-option .flag {
            font-size: 36px;
            margin-bottom: 12px;
            display: block;
        }

        .language-option .lang-name {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            display: block;
            margin-bottom: 4px;
        }

        .language-option .lang-native {
            font-size: 14px;
            color: #64748b;
            font-family: 'Noto Sans Devanagari', 'Inter', sans-serif;
        }

        .language-modal .btn-continue {
            display: block !important;
            visibility: visible !important;
            width: 100%;
            padding: 16px 32px;
            background: linear-gradient(135deg, #f97316, #ea580c) !important;
            color: #ffffff !important;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
            font-family: 'Inter', 'Noto Sans Devanagari', sans-serif;
            text-align: center;
            opacity: 1 !important;
        }

        .language-modal .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
        }

        .language-modal .btn-continue:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Wrapper */
        .lang-dropdown {
            position: relative;
            list-style: none;
        }

        /* Switcher */
        .language-switcher {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            padding: 6px 12px;
            background: #ffffff;
            color: #000000;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Dropdown menu */
        .lang-dropdown-menu {
            position: absolute;
            top: 110%;
            right: 0;
            background: #ffffff;
            border: 1px solid #ccc;
            border-radius: 6px;
            display: none;
            min-width: 140px;
            z-index: 999;
        }

        /* Invisible bridge to prevent menu from closing when moving cursor across the gap */
        .lang-dropdown-menu::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 0;
            width: 100%;
            height: 20px;
            background: transparent;
        }

        /* Links */
        .lang-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            color: #000;
            text-decoration: none;
        }

        .lang-dropdown-menu a:hover,
        .lang-dropdown-menu a.active {
            background: #f0f0f0;
            font-weight: 600;
        }

        /* Show on hover */
        .lang-dropdown:hover .lang-dropdown-menu {
            display: block;
        }

        @media (max-width: 768px) {
            .language-modal {
                padding: 30px 20px;
                max-width: 95%;
            }

            .language-options {
                grid-template-columns: 1fr;
            }
            
            .language-option {
                padding: 16px 12px;
            }

            .language-modal-header h2 {
                font-size: 20px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Language Selection Modal (First Visit) -->
    <div class="language-modal-overlay" id="languageModal">
        <div class="language-modal">
            <div class="language-modal-header">
                <div class="icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h2>Select Your Language / अपनी भाषा चुनें / तुमची भाषा निवडा</h2>
                <p>Choose your preferred language / अपनी पसंदीदा भाषा चुनें / तुमची पसंतीची भाषा निवडा</p>
            </div>
            
            <form id="languageForm" action="{{ route('language.switch') }}" method="POST">
                @csrf
                <input type="hidden" name="locale" id="selectedLocale" value="en">
                
                <div class="language-options">
                    <div class="language-option selected" data-locale="en" onclick="selectLanguage('en')">
                        <span class="flag">🇮🇳</span>
                        <span class="lang-name">English</span>
                        <span class="lang-native">English</span>
                    </div>
                    <div class="language-option" data-locale="hi" onclick="selectLanguage('hi')">
                        <span class="flag">🇮🇳</span>
                        <span class="lang-name">हिंदी</span>
                        <span class="lang-native">Hindi</span>
                    </div>
                    <div class="language-option" data-locale="mr" onclick="selectLanguage('mr')">
                        <span class="flag">🇮🇳</span>
                        <span class="lang-name">मराठी</span>
                        <span class="lang-native">Marathi</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-continue">
                    Continue / जारी रखें / पुढे जा <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="{{ route('home') }}" class="logo">
                    @if(!empty($settings['site_logo']))
                        <img src="{{ asset('storage/' . $settings['site_logo']) }}"
                             alt="{{ $settings['site_name'] ?? 'Gram Panchayat' }}" class="logo-mark">
                    @else
                        <span class="logo-emblem"><i class="fas fa-landmark" aria-hidden="true"></i></span>
                    @endif
                    <span class="logo-text">
                        {{ $settings['site_name'] ?? 'Gram Panchayat' }}
                        @if(!empty($settings['site_tagline']))
                            <small class="logo-tagline">{{ $settings['site_tagline'] }}</small>
                        @endif
                    </span>
                </a>
                
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
                <ul class="nav-links" id="navLinks">
                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">{{ __('messages.home') }}</a></li>
                    <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">{{ __('messages.about') }}</a></li>
                    <li><a href="{{ route('digital-services') }}" class="{{ request()->routeIs('digital-services') ? 'active' : '' }}">{{ __('messages.services') }}</a></li>
                    
                    <li class="lang-dropdown">
                        <div class="language-switcher">
                            <span class="current-lang">
                                {{ app()->getLocale() == 'hi' ? 'हिंदी' : (app()->getLocale() == 'mr' ? 'मराठी' : 'EN') }}
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </div>

                        <div class="lang-dropdown-menu">
                            <a href="#" onclick="event.preventDefault(); switchLanguage('en');"
                               class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">
                                🇮🇳 English
                            </a>
                            <a href="#" onclick="event.preventDefault(); switchLanguage('hi');"
                               class="{{ app()->getLocale() == 'hi' ? 'active' : '' }}">
                                🇮🇳 हिंदी
                            </a>
                            <a href="#" onclick="event.preventDefault(); switchLanguage('mr');"
                               class="{{ app()->getLocale() == 'mr' ? 'active' : '' }}">
                                🇮🇳 मराठी
                            </a>
                        </div>
                    </li>
                    
                    <li><a href="{{ route('citizen.login') }}" class="btn btn-primary">{{ __('messages.login') }}</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>
                        @if(!empty($settings['site_logo']))
                            <img src="{{ asset('storage/' . $settings['site_logo']) }}"
                                 alt="" class="footer-logo-mark">
                        @else
                            <i class="fas fa-landmark"></i>
                        @endif
                        {{ $settings['site_name'] ?? 'Gram Panchayat' }}
                    </h3>
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
                
                @if(!empty($quickLinks) && count($quickLinks))
                    <div class="footer-section">
                        <h4>{{ __('messages.quick_links') }}</h4>
                        <ul>
                            @foreach($quickLinks as $link)
                                <li>
                                    <a href="{{ $link->url }}" @if($link->open_new_tab) target="_blank" rel="noopener" @endif>
                                        {{ $link->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $footerAddress = $settings['address'] ?? null;
                    $footerPhone = $settings['contact_phone'] ?? null;
                    $footerEmail = $settings['contact_email'] ?? null;
                @endphp
                @if(!empty($footerAddress) || !empty($footerPhone) || !empty($footerEmail))
                    <div class="footer-section">
                        <h4>{{ __('messages.contact_us') }}</h4>
                        <ul class="contact-info">
                            @if(!empty($footerAddress))
                                <li>
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $footerAddress }}</span>
                                </li>
                            @endif
                            @if(!empty($footerPhone))
                                <li>
                                    <i class="fas fa-phone"></i>
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $footerPhone) }}">{{ $footerPhone }}</a>
                                </li>
                            @endif
                            @if(!empty($footerEmail))
                                <li>
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
            
            <div class="footer-bottom">
                <nav class="footer-legal" aria-label="{{ __('messages.legal_policies') }}">
                    <ul>
                        <li><a href="{{ route('privacy-policy') }}">{{ __('messages.privacy_policy') }}</a></li>
                        <li><a href="{{ route('terms-conditions') }}">{{ __('messages.terms_conditions') }}</a></li>
                        <li><a href="{{ route('refund-policy') }}">{{ __('messages.refund_policy') }}</a></li>
                        <li><a href="{{ route('disclaimer') }}">{{ __('messages.disclaimer') }}</a></li>
                        <li><a href="{{ route('accessibility-statement') }}">{{ __('messages.accessibility_statement') }}</a></li>
                        <li><a href="{{ route('copyright-policy') }}">{{ __('messages.copyright_policy') }}</a></li>
                        <li><a href="{{ route('hyperlinking-policy') }}">{{ __('messages.hyperlinking_policy') }}</a></li>
                    </ul>
                </nav>
                <p>&copy; {{ date('Y') }} {{ $settings['site_name'] ?? 'Gram Panchayat' }}. {{ __('messages.all_rights_reserved') }}</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <!-- Language Selection Script -->
    <script>
        // Check if language has been selected before
        document.addEventListener('DOMContentLoaded', function() {
            // Check localStorage for language preference
            const hasSelectedLanguage = localStorage.getItem('languageSelected');
            const modal = document.getElementById('languageModal');
            
            // Show modal only on first visit (check both localStorage and session)
            @if(!session()->has('locale'))
                if (!hasSelectedLanguage) {
                    setTimeout(function() {
                        modal.classList.add('active');
                    }, 500);
                }
            @endif
        });

        function selectLanguage(locale) {
            // Remove selected class from all options
            document.querySelectorAll('.language-option').forEach(function(option) {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            document.querySelector('[data-locale="' + locale + '"]').classList.add('selected');
            
            // Update hidden input
            document.getElementById('selectedLocale').value = locale;
        }

        // Handle form submission
        document.getElementById('languageForm').addEventListener('submit', function() {
            // Mark language as selected in localStorage
            localStorage.setItem('languageSelected', 'true');
        });

        // Function to switch language from dropdown
        function switchLanguage(lang) {
            window.location.href = "/language/" + lang;
        }
    </script>
    
    @stack('scripts')

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav" id="bottomNav" aria-label="Bottom Navigation">
        <a href="{{ route('home') }}" id="bnav-home"
           class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}"
           aria-label="Home">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>

        <a href="{{ route('citizen.login') }}" id="bnav-taxpay"
           class="bottom-nav-item {{ request()->routeIs('citizen.pay-bill') || request()->routeIs('citizen.dashboard') || request()->routeIs('citizen.login') && !request()->routeIs('home') && !request()->routeIs('about') ? '' : '' }}"
           aria-label="Tax Pay">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Tax Pay</span>
        </a>

        <a href="{{ route('digital-services') }}" id="bnav-services"
           class="bottom-nav-item {{ request()->routeIs('digital-services') ? 'active' : '' }}"
           aria-label="Services">
            <i class="fas fa-th-large"></i>
            <span>Services</span>
        </a>

        <a href="{{ auth('citizen')->check() ? route('citizen.profile') : route('citizen.login') }}" id="bnav-profile"
           class="bottom-nav-item {{ request()->routeIs('citizen.profile') ? 'active' : '' }}"
           aria-label="Profile">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
    </nav>

    <script>
        // Bottom nav active state for Tax Pay button
        (function() {
            var path = window.location.pathname;
            var taxPayBtn = document.getElementById('bnav-taxpay');
            if (path.startsWith('/citizen') && !path.includes('/profile')) {
                taxPayBtn && taxPayBtn.classList.add('active');
            }
            // Services scroll detection
            var servicesBtn = document.getElementById('bnav-services');
            if (window.location.hash === '#services') {
                servicesBtn && servicesBtn.classList.add('active');
            }
        })();
    </script>

    <script>
        // Registered after load so it never competes with the page's own requests.
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                // updateViaCache:'none' because nginx serves sw.js with a
                // ten-year max-age. Without it the browser can keep serving the
                // old worker, and the cache-version bump that clears stale
                // assets never runs.
                navigator.serviceWorker.register('{{ asset('sw.js') }}', {
                    scope: '/',
                    updateViaCache: 'none',
                })
                    .catch(function (error) {
                        console.warn('Service worker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
