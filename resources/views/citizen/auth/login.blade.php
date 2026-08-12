<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Citizen Login - Gram Panchayat</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #1e3a5f;
            --primary-light: #2d5a8e;
            --primary-dark: #0f1f33;
            --secondary: #f97316;
            --secondary-light: #fb923c;
            --accent: #10b981;
            --background: #f8fafc;
            --surface: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --error: #ef4444;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
            --radius: 12px;
            --radius-lg: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: slideUp 0.5s ease forwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 40px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .login-logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .login-logo i {
            font-size: 36px;
            color: white;
        }

        .login-header h1 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
        }

        .login-header p {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            position: relative;
        }

        .login-body {
            padding: 32px;
        }

        /* Steps */
        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn 0.4s ease forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .step-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .step-desc {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .input-group {
            position: relative;
        }

        .input-prefix {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            padding-left: 50px;
            font-size: 16px;
            font-family: inherit;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            color: var(--text-primary);
            transition: var(--transition);
            letter-spacing: 1px;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(30, 58, 95, 0.1);
        }

        .form-input.otp-input {
            padding-left: 16px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 8px;
        }

        .btn {
            width: 100%;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.5);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: var(--surface);
            color: var(--text-primary);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Alert Messages */
        .alert {
            padding: 14px 16px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }

        .alert i {
            font-size: 18px;
            flex-shrink: 0;
        }

        /* Change Phone */
        .change-phone {
            text-align: center;
            margin-top: 16px;
        }

        .change-phone a {
            color: var(--primary);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .change-phone a:hover {
            color: var(--secondary);
        }

        /* Resend OTP */
        .resend-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .resend-text {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .resend-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .resend-btn:hover {
            color: var(--secondary);
        }

        .resend-btn:disabled {
            color: var(--text-muted);
            cursor: not-allowed;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            padding: 24px 32px;
            background: var(--background);
            border-top: 1px solid var(--border);
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-footer a:hover {
            color: var(--secondary);
        }

        /* Debug OTP */
        .debug-otp {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .debug-otp strong {
            font-weight: 600;
        }

        /* Back to home */
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-home a {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-home a:hover {
            color: white;
        }

        .lang-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 4px;
            border-radius: 8px;
            backdrop-filter: blur(4px);
            z-index: 10;
        }
        .lang-switcher a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .lang-switcher a:hover, .lang-switcher a.active {
            background: white;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="back-home">
        <a href="{{ route('home') }}">
            <i class="fas fa-arrow-left"></i>
            {{ __('messages.back_to_home') }}
        </a>
    </div>

    <div class="lang-switcher">
        <a href="{{ route('language.switch-param', 'en') }}" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
        <a href="{{ route('language.switch-param', 'hi') }}" class="{{ app()->getLocale() == 'hi' ? 'active' : '' }}">हिंदी</a>
        <a href="{{ route('language.switch-param', 'mr') }}" class="{{ app()->getLocale() == 'mr' ? 'active' : '' }}">मराठी</a>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-landmark"></i>
                </div>
                <h1>{{ __('messages.citizen_portal') }}</h1>
                <p>{{ __('messages.tax_payment_system') }}</p>
            </div>

            <div class="login-body">
                @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                <!-- Step 1: Enter Phone -->
                <div class="step active" id="stepPhone">
                    <h2 class="step-title">{{ __('messages.login_with_phone') }}</h2>
                    <p class="step-desc">{{ __('messages.enter_mobile_otp') }}</p>

                    <div id="phoneError" class="alert alert-error" style="display: none;"></div>

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.mobile_number') }}</label>
                        <div class="input-group">
                            <span class="input-prefix">+91</span>
                            <input type="tel" id="phoneInput" class="form-input"
                                   value="{{ $prefillPhone ?? '' }}"
                                   placeholder="Enter 10 digit number"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   autocomplete="tel"
                                   {{ !empty($prefillPhone) ? 'autofocus' : '' }}>
                        </div>
                    </div>

                    <button type="button" id="sendOtpBtn" class="btn btn-primary">
                        <span>{{ __('messages.send_otp') }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Step 2: Verify OTP -->
                <div class="step" id="stepOtp">
                    <h2 class="step-title">{{ __('messages.verify_otp') }}</h2>
                    <p class="step-desc">{!! __('messages.enter_otp_code', ['phone' => '<strong id="displayPhone"></strong>']) !!}</p>

                    <div id="otpError" class="alert alert-error" style="display: none;"></div>
                    <div id="otpSuccess" class="alert alert-success" style="display: none;"></div>

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.enter_otp') }}</label>
                        <input type="text" id="otpInput" class="form-input otp-input" 
                               placeholder="000000" 
                               maxlength="6" 
                               pattern="[0-9]{6}"
                               autocomplete="one-time-code">
                    </div>

                    <button type="button" id="verifyOtpBtn" class="btn btn-primary">
                        <span>{{ __('messages.verify_login') }}</span>
                        <i class="fas fa-check"></i>
                    </button>

                    <div class="change-phone">
                        <a href="#" id="changePhoneBtn">
                            <i class="fas fa-edit"></i> {{ __('messages.change_phone') }}
                        </a>
                    </div>

                    <div class="resend-section">
                        <span class="resend-text">{{ __('messages.didnt_receive_otp') }} </span>
                        <button type="button" id="resendOtpBtn" class="resend-btn" disabled>
                            {!! __('messages.resend_in', ['seconds' => '<span id="countdown">30</span>']) !!}
                        </button>
                    </div>
                </div>
            </div>

            <div class="login-footer">
                <p>{!! __('messages.contact_help', ['url' => route('contact')]) !!}</p>
            </div>
        </div>
    </div>

    <div id="recaptcha-container" style="display: none;"></div>

    <!-- Firebase SDK (Add your Firebase config) -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>

    <script>
        // Firebase configuration, entirely from Admin > Settings > Firebase.
        //
        // No fallback values on purpose. Hardcoded defaults previously pointed
        // at an unrelated Firebase project, so clearing the settings would have
        // silently authenticated citizens against someone else's project
        // instead of failing visibly.
        const firebaseConfig = {
            apiKey: @json(\App\Models\SiteSetting::get('firebase_api_key', '')),
            authDomain: @json(\App\Models\SiteSetting::get('firebase_auth_domain', '')),
            projectId: @json(\App\Models\SiteSetting::get('firebase_project_id', '')),
            storageBucket: @json(\App\Models\SiteSetting::get('firebase_storage_bucket', '')),
            messagingSenderId: @json(\App\Models\SiteSetting::get('firebase_messaging_sender_id', '')),
            appId: @json(\App\Models\SiteSetting::get('firebase_app_id', '')),
            measurementId: @json(\App\Models\SiteSetting::get('firebase_measurement_id', ''))
        };

        const firebaseEnabled = "{{ \App\Models\SiteSetting::get('firebase_enabled', '0') }}" === "1";
        let firebaseInitialized = false;
        let recaptchaVerifier = null;
        let confirmationResult = null;

        if (firebaseEnabled && firebaseConfig.apiKey && firebaseConfig.projectId) {
            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }
            firebaseInitialized = true;
        }

        // DOM Elements
        const stepPhone = document.getElementById('stepPhone');
        const stepOtp = document.getElementById('stepOtp');
        const phoneInput = document.getElementById('phoneInput');
        const otpInput = document.getElementById('otpInput');
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        const verifyOtpBtn = document.getElementById('verifyOtpBtn');
        const changePhoneBtn = document.getElementById('changePhoneBtn');
        const resendOtpBtn = document.getElementById('resendOtpBtn');
        const displayPhone = document.getElementById('displayPhone');
        const phoneError = document.getElementById('phoneError');
        const otpError = document.getElementById('otpError');
        const otpSuccess = document.getElementById('otpSuccess');
        const countdown = document.getElementById('countdown');

        let phoneNumber = '';
        let countdownTimer = null;

        function mapFirebaseError(error, phase = 'send') {
            const code = error?.code || '';
            const sendErrors = {
                'auth/invalid-phone-number': 'Invalid mobile number format. Please enter a valid 10-digit number.',
                'auth/missing-phone-number': 'Phone number is missing. Please enter your mobile number.',
                'auth/too-many-requests': 'Too many attempts. Please wait a few minutes and try again.',
                'auth/quota-exceeded': 'SMS quota exceeded for Firebase project. Please contact support.',
                'auth/app-not-authorized': 'This domain is not authorized in Firebase. Please contact admin.',
                // Deliberately vague: Firebase returns this code both when the
                // Phone provider is off AND when the SMS region policy blocks
                // the destination country. The detail below distinguishes them.
                'auth/operation-not-allowed': 'SMS could not be sent. Please contact the Gram Panchayat office.',
                'auth/captcha-check-failed': 'reCAPTCHA verification failed. Please retry.',
                'auth/network-request-failed': 'Network issue while contacting Firebase. Please check internet and retry.',
            };
            const verifyErrors = {
                'auth/invalid-verification-code': 'Invalid OTP. Please check and enter the correct OTP.',
                'auth/code-expired': 'OTP has expired. Please request a new OTP.',
                'auth/session-expired': 'OTP session expired. Please request a new OTP.',
                'auth/too-many-requests': 'Too many verification attempts. Please wait and try again.',
                'auth/network-request-failed': 'Network issue while verifying OTP. Please retry.',
            };

            var friendly = (phase === 'verify' && verifyErrors[code])
                ? verifyErrors[code]
                : (sendErrors[code] || 'Firebase OTP service error. Please try again.');

            // Log the raw code and message. A single Firebase code can have
            // several causes - auth/operation-not-allowed means either the
            // Phone provider is off or the SMS region policy excludes the
            // country - and a friendly message alone hides which one it is,
            // which turns a two-minute console fix into a guessing game.
            if (code || error?.message) {
                console.error('[Firebase OTP]', code, error?.message || '');
            }

            return friendly;
        }

        if (!firebaseInitialized) {
            showError(phoneError, 'Firebase OTP is not enabled or configured properly. Please contact Gram Panchayat office.');
            sendOtpBtn.disabled = true;
        }

        // Show/hide loading state
        function setLoading(button, loading) {
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<div class="spinner"></div> Please wait...';
            } else {
                button.disabled = false;
                if (button === sendOtpBtn) {
                    button.innerHTML = '<span>Send OTP</span><i class="fas fa-arrow-right"></i>';
                } else if (button === verifyOtpBtn) {
                    button.innerHTML = '<span>Verify & Login</span><i class="fas fa-check"></i>';
                }
            }
        }

        // Show error
        function showError(element, message) {
            element.innerHTML = '<i class="fas fa-exclamation-circle"></i><span>' + message + '</span>';
            element.style.display = 'flex';
        }

        // Hide error
        function hideError(element) {
            element.style.display = 'none';
        }

        // Start countdown
        function startCountdown() {
            let seconds = 30;
            resendOtpBtn.disabled = true;
            
            countdownTimer = setInterval(() => {
                seconds--;
                countdown.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(countdownTimer);
                    resendOtpBtn.disabled = false;
                    resendOtpBtn.innerHTML = "{{ __('messages.resend_otp') }}";
                }
            }, 1000);
        }

        // Send OTP
        sendOtpBtn.addEventListener('click', async () => {
            hideError(phoneError);
            
            const rawPhone = phoneInput.value.replace(/\D/g, '');
            phoneNumber = rawPhone.length > 10 ? rawPhone.slice(-10) : rawPhone;
            phoneInput.value = phoneNumber;
            
            if (!/^[0-9]{10}$/.test(phoneNumber)) {
                showError(phoneError, 'Please enter a valid 10-digit phone number');
                return;
            }

            setLoading(sendOtpBtn, true);

            try {
                if (!firebaseInitialized) {
                    showError(phoneError, 'Firebase OTP is not available. Please contact Gram Panchayat office.');
                    setLoading(sendOtpBtn, false);
                    return;
                }

                // First, check with backend if phone exists
                const response = await fetch('{{ route("citizen.send-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ phone: phoneNumber })
                });

                const data = await response.json();

                if (!response.ok) {
                    showError(phoneError, data.message || 'Failed to send OTP');
                    setLoading(sendOtpBtn, false);
                    return;
                }

                try {
                    if (!recaptchaVerifier) {
                        recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                            size: 'invisible',
                            callback: () => {},
                        });
                        await recaptchaVerifier.render();
                    }

                    confirmationResult = await firebase.auth().signInWithPhoneNumber(
                        '+91' + phoneNumber,
                        recaptchaVerifier
                    );

                    stepPhone.classList.remove('active');
                    stepOtp.classList.add('active');
                    displayPhone.textContent = '+91 ' + phoneNumber;
                    startCountdown();
                } catch (firebaseError) {
                    showError(phoneError, mapFirebaseError(firebaseError, 'send'));
                    setLoading(sendOtpBtn, false);
                    return;
                }

            } catch (error) {
                showError(phoneError, 'An error occurred. Please try again.');
            }

            setLoading(sendOtpBtn, false);
        });

        // Verify OTP
        verifyOtpBtn.addEventListener('click', async () => {
            hideError(otpError);
            
            const otp = otpInput.value.trim();
            
            if (!/^[0-9]{6}$/.test(otp)) {
                showError(otpError, 'Please enter a valid 6-digit OTP');
                return;
            }

            setLoading(verifyOtpBtn, true);

            try {
                if (!confirmationResult) {
                    showError(otpError, 'Please request OTP first.');
                    setLoading(verifyOtpBtn, false);
                    return;
                }

                let firebaseIdToken = null;

                try {
                    const firebaseUserCredential = await confirmationResult.confirm(otp);
                    firebaseIdToken = await firebaseUserCredential.user.getIdToken(true);
                } catch (firebaseError) {
                    showError(otpError, mapFirebaseError(firebaseError, 'verify'));
                    setLoading(verifyOtpBtn, false);
                    return;
                }

                // Verify with backend
                const response = await fetch('{{ route("citizen.verify-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        phone: phoneNumber,
                        firebase_id_token: firebaseIdToken
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    showError(otpError, data.message || 'OTP verification failed');
                    setLoading(verifyOtpBtn, false);
                    return;
                }

                // Success - redirect to dashboard
                otpSuccess.innerHTML = '<i class="fas fa-check-circle"></i><span>Login successful! Redirecting...</span>';
                otpSuccess.style.display = 'flex';

                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("citizen.dashboard") }}';
                }, 1000);

            } catch (error) {
                showError(otpError, 'An error occurred. Please try again.');
                setLoading(verifyOtpBtn, false);
            }
        });

        // Change phone number
        changePhoneBtn.addEventListener('click', (e) => {
            e.preventDefault();
            stepOtp.classList.remove('active');
            stepPhone.classList.add('active');
            hideError(otpError);
            otpInput.value = '';
            confirmationResult = null;
            if (countdownTimer) clearInterval(countdownTimer);
        });

        // Resend OTP
        resendOtpBtn.addEventListener('click', () => {
            resendOtpBtn.innerHTML = `{!! __('messages.resend_in', ['seconds' => '<span id="countdown">30</span>']) !!}`;
            sendOtpBtn.click();
        });

        // Auto-Tab for OTP input
        phoneInput.addEventListener('input', (e) => {
            let digits = e.target.value.replace(/\D/g, '');
            if (digits.length > 10) {
                digits = digits.slice(-10);
            }
            e.target.value = digits;
        });

        otpInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Auto-submit OTP when 6 digits entered
        otpInput.addEventListener('input', () => {
            if (otpInput.value.length === 6) {
                verifyOtpBtn.click();
            }
        });
    </script>
</body>
</html>
