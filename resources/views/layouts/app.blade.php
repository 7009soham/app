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
    {{-- "default", not "black-translucent". Translucent asks iOS to run content
         under a transparent status bar, and the only way to compensate is
         env(safe-area-inset-top), which stays 0 without viewport-fit=cover. The
         installed app was therefore drawing the fixed masthead underneath the
         clock. Letting iOS reserve the bar is the cheaper correct answer. --}}
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
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

    <script>
        // Runs before first paint so a citizen who has chosen larger text or high
        // contrast does not watch the page render at the default and then jump.
        // Deliberately inline and synchronous for that reason.
        (function () {
            try {
                var scale = localStorage.getItem('gpTextScale');
                if (scale && /^1(\.\d+)?$/.test(scale)) {
                    document.documentElement.style.setProperty('--text-scale', scale);
                }
                if (localStorage.getItem('gpHighContrast') === '1') {
                    document.documentElement.classList.add('high-contrast');
                }
            } catch (e) {
                // Private browsing can throw on localStorage access. Defaults are fine.
            }
        })();
    </script>
    
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
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            /* A fixed box overflows where the document cannot scroll, so without
               these the tail of the dialog is simply unreachable. flex-start
               rather than center because centring spills out of BOTH edges. */
            align-items: flex-start;
            overflow-y: auto;
            padding: var(--spacing-4);
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
            width: 100%;
            margin: auto;
            max-height: calc(100vh - 2 * var(--spacing-4));
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.8) translateY(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            position: relative;
            overflow-y: auto;
        }

        .language-modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            /* Was clipped by the dialog's overflow:hidden, which had to go so the
               dialog could scroll. */
            border-radius: 24px 24px 0 0;
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
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            font-family: 'Inter', 'Noto Sans Devanagari', sans-serif;
            line-height: 1.3;
        }

        /* Two scripts stacked rather than three joined by slashes: the old
           "Select Your Language / अपनी भाषा चुनें / तुमची भाषा निवडा"
           ran to three lines on a phone and the languages are named on the
           buttons anyway. */
        .language-modal-header h2 span {
            display: block;
        }

        .language-modal-header h2 [lang="mr"] {
            font-size: 17px;
            font-weight: 600;
            color: #475569;
        }

        .language-modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: #475569;
            font-size: 18px;
            cursor: pointer;
        }

        .language-modal-close:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .language-modal-close:focus-visible {
            outline: 3px solid #b45309;
            outline-offset: 2px;
        }

        .language-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 25px;
        }

        .language-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            border: 2px solid #94a3b8;
            border-radius: 16px;
            /* 44px minimum target, and a submit button now rather than a div. */
            padding: 18px 12px;
            min-height: 44px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            color: #1e293b;
            font-family: inherit;
            text-align: center;
        }

        .language-option:focus-visible {
            outline: 3px solid #b45309;
            outline-offset: 2px;
        }

        .language-option:hover {
            border-color: #f97316;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.15);
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

        /* Wrapper */
        .lang-dropdown {
            position: relative;
            list-style: none;
        }

        /* Switcher. A real <button> now, so it needs the font reset. */
        .language-switcher {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            padding: 6px 12px;
            background: #ffffff;
            /* #595959 is the lightest grey that still clears 4.5:1 on white; the
               chevron and label both use it. */
            color: #1f2937;
            border: 1px solid #6b7280;
            border-radius: 6px;
            font-family: inherit;
            font-size: inherit;
            font-weight: 600;
            line-height: 1.4;
        }

        .language-switcher:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        .language-switcher i {
            font-size: 0.7em;
            transition: transform var(--transition-fast);
        }

        .language-switcher[aria-expanded="true"] i {
            transform: rotate(180deg);
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
        .lang-dropdown-menu a:focus-visible {
            background: #e8eef6;
        }

        /* "You are here" was a #f0f0f0 tint at 1.14:1 on white, which is not a
           visible indicator. A left rule and the weight carry it instead. */
        .lang-dropdown-menu a.active {
            font-weight: 700;
            box-shadow: inset 3px 0 0 var(--color-primary);
            background: #eef3f9;
        }

        /* Opened by the button, so it works on touch where there is no hover.
           Hover is kept as a convenience for a mouse. */
        .lang-dropdown-menu.open,
        .lang-dropdown:hover .lang-dropdown-menu,
        .lang-dropdown:focus-within .lang-dropdown-menu {
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
    {{-- First focusable thing on the page. WCAG 2.4.1: without it a keyboard user
         crosses the logo, three nav links, the language menu and the login button
         on all twenty public pages before reaching content. --}}
    <a class="skip-link" href="#main">{{ __('messages.skip_to_main') }}</a>

    {{-- First-visit language chooser. This is the first screen a new citizen
         meets and it blocks the page, so it was also the worst thing on it: the
         three options were <div>s with an onclick and no tabindex, so there was
         no keyboard path at all; there was no Escape, backdrop or close
         dismissal; and the stack measured about 950px against a 667px phone with
         no scroll, which put the Continue button off-screen. A first-time mobile
         visitor was hard-blocked behind an opaque overlay.

         Each language is now its own submit button carrying its own locale, so
         choosing is one tap instead of select-then-confirm, it works with the
         keyboard and with JavaScript off, and dropping the confirm step plus the
         three identical flag glyphs takes about 300px out of the height. --}}
    <div class="language-modal-overlay" id="languageModal">
        <div class="language-modal" role="dialog" aria-modal="true" aria-labelledby="languageModalHeading">
            <button type="button" class="language-modal-close" id="languageModalClose" aria-label="{{ __('messages.close') }}">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>

            <div class="language-modal-header">
                <div class="icon" aria-hidden="true">
                    <i class="fas fa-globe"></i>
                </div>
                <h2 id="languageModalHeading">
                    <span lang="en">Select your language</span>
                    <span lang="mr">तुमची भाषा निवडा</span>
                </h2>
            </div>

            <form action="{{ route('language.switch') }}" method="POST">
                @csrf
                <div class="language-options">
                    @foreach(['en' => ['English', 'English'], 'hi' => ['हिंदी', 'Hindi'], 'mr' => ['मराठी', 'Marathi']] as $locale => $labels)
                        <button type="submit" name="locale" value="{{ $locale }}" class="language-option" lang="{{ $locale }}">
                            <span class="lang-name">{{ $labels[0] }}</span>
                            <span class="lang-native" lang="en">{{ $labels[1] }}</span>
                        </button>
                    @endforeach
                </div>
            </form>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        {{-- The tricolour and the parent-government line are what make a citizen
             read this as a state organ rather than a brand, inside about a second.
             Deliberately NOT the State Emblem of India: its use is restricted by
             the State Emblem of India (Prohibition of Improper Use) Act 2005. --}}
        <div class="tricolour" aria-hidden="true"></div>

        <div class="gov-bar">
            <div class="container gov-bar__inner">
                {{-- The Panchayat naming itself and stating where it sits. It must
                     NOT claim "Government of Maharashtra" or any parent-government
                     attribution: that is a badge the state grants, not something a
                     village body may assert on its own, and the same reasoning that
                     keeps the State Emblem off this page applies to the wordmark.
                     The Marathi name is a proper noun, so it is not translated and
                     is tagged for screen readers (WCAG 3.1.2). --}}
                <p class="gov-bar__attrib">
                    <span lang="mr">नेरळ ग्रामपंचायत</span>
                    <span class="gov-bar__rule" aria-hidden="true"></span>
                    <span class="gov-bar__en">{{ __('messages.jurisdiction') }}</span>
                </p>

                <div class="gov-bar__tools">
                    <div class="text-size" role="group" aria-label="{{ __('messages.text_size') }}">
                        <button type="button" class="text-size__btn" data-text-scale="down"
                                aria-label="{{ __('messages.decrease_text_size') }}">A&minus;</button>
                        <button type="button" class="text-size__btn" data-text-scale="reset"
                                aria-label="{{ __('messages.reset_text_size') }}">A</button>
                        <button type="button" class="text-size__btn" data-text-scale="up"
                                aria-label="{{ __('messages.increase_text_size') }}">A+</button>
                    </div>

                    <button type="button" class="gov-bar__contrast" data-contrast-toggle aria-pressed="false">
                        <i class="fas fa-circle-half-stroke" aria-hidden="true"></i>
                        <span class="gov-bar__contrast-label">{{ __('messages.high_contrast') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="container">
            <nav class="nav" aria-label="{{ __('messages.official_portal') }}">
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
                    
                    {{-- Was a <div> opened purely by :hover, which made the three
                         languages unreachable by keyboard and unreachable by touch,
                         where there is no hover at all. Now a real button with real
                         links: the locale route is a plain GET, so this works with
                         JavaScript off too. --}}
                    <li class="lang-dropdown">
                        <button type="button" class="language-switcher" id="langToggle"
                                aria-expanded="false" aria-controls="langMenu">
                            <span class="current-lang">
                                {{ app()->getLocale() == 'hi' ? 'हिंदी' : (app()->getLocale() == 'mr' ? 'मराठी' : 'EN') }}
                            </span>
                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        </button>

                        <div class="lang-dropdown-menu" id="langMenu">
                            @foreach(['en' => 'English', 'hi' => 'हिंदी', 'mr' => 'मराठी'] as $locale => $label)
                                <a href="{{ route('language.switch-param', $locale) }}"
                                   lang="{{ $locale }}"
                                   class="{{ app()->getLocale() === $locale ? 'active' : '' }}"
                                   @if(app()->getLocale() === $locale) aria-current="true" @endif>
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </li>
                    
                    <li><a href="{{ route('citizen.login') }}" class="btn btn-primary">{{ __('messages.login') }}</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    {{-- tabindex="-1" so the skip link can actually place focus here. --}}
    <main class="main-content" id="main" tabindex="-1">
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
                
                {{-- Peer columns, so they are all h4. Quick Links used to be an
                     h3 beside an h4, which mis-stated the outline. --}}
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

                {{-- Outbound links to the parent governments. On an Indian portal
                     this column is a recognition signal in itself: a citizen reads
                     "this sits inside a real hierarchy" from it. --}}
                <div class="footer-section">
                    <h4>{{ __('messages.important_links') }}</h4>
                    <ul>
                        @foreach([
                            'https://www.india.gov.in/' => 'India.gov.in',
                            'https://www.maharashtra.gov.in/' => 'Maharashtra.gov.in',
                            'https://raigad.gov.in/' => 'Raigad District',
                            'https://www.digitalindia.gov.in/' => 'Digital India',
                        ] as $url => $label)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">
                                    {{ $label }}
                                    <i class="fas fa-arrow-up-right-from-square footer-external" aria-hidden="true"></i>
                                    <span class="sr-only">({{ __('messages.opens_new_window') }})</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

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
                            {{-- When the counter is actually open is one of the two
                                 things a citizen most often needs from a Panchayat
                                 site, and it was nowhere on the portal. --}}
                            <li>
                                <i class="fas fa-clock" aria-hidden="true"></i>
                                <span>
                                    <span class="contact-label">{{ __('messages.office_hours') }}</span>
                                    {{ __('messages.office_hours_value') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
            
            {{-- Names the accountable office and states when the content actually
                 changed, both of which GIGW expects and neither of which the
                 footer carried. The stamp is a real value, not today's date. --}}
            <div class="footer-meta">
                <p class="footer-owner">{{ __('messages.content_owned_by') }}</p>
                @if(!empty($contentUpdatedAt))
                    <p class="footer-updated">
                        {{ __('messages.last_updated') }}:
                        <time datetime="{{ $contentUpdatedAt->toDateString() }}">{{ $contentUpdatedAt->translatedFormat('d M Y') }}</time>
                    </p>
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
    <script src="{{ \App\Helpers\Asset::versioned('js/app.js') }}"></script>
    
    <!-- Language Selection Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('languageModal');
            const dialog = modal ? modal.querySelector('.language-modal') : null;

            if (!modal || !dialog) {
                return;
            }

            let lastFocused = null;

            function remember() {
                try {
                    localStorage.setItem('languageSelected', 'true');
                } catch (e) {
                    // Private browsing. The session still carries the choice.
                }
            }

            function close() {
                modal.classList.remove('active');
                remember();

                if (lastFocused) {
                    lastFocused.focus();
                }
            }

            function open() {
                lastFocused = document.activeElement;
                modal.classList.add('active');
                // Focus the dialog rather than the first language, so no option
                // looks preselected and a screen reader reads the heading first.
                dialog.querySelector('.language-option')?.focus();
            }

            // Choosing submits the form, so only mark it seen; the navigation does
            // the rest. Escape, the close button and the backdrop all dismiss,
            // because a modal with no exit on the first screen a citizen sees is
            // a trap rather than a chooser.
            modal.addEventListener('submit', remember);
            document.getElementById('languageModalClose')?.addEventListener('click', close);

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    close();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    close();
                }
            });

            @if(!session()->has('locale'))
                let seen = null;
                try {
                    seen = localStorage.getItem('languageSelected');
                } catch (e) {
                    // Treated as a first visit.
                }

                if (!seen) {
                    setTimeout(open, 500);
                }
            @endif
        });
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
