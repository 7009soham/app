<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Payment could not be verified</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --navy: #1a365d; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 24px; background: #f1f5f9; color: #1e293b;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .wrap { max-width: 460px; width: 100%; }
        .brand { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 16px; color: var(--navy); font-weight: 600; }
        .brand img { height: 34px; width: auto; max-width: 120px; object-fit: contain; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
        .banner { background: linear-gradient(135deg, #b45309, #d97706); color: #fff; padding: 30px 26px 24px; text-align: center; }
        .banner i { font-size: 40px; }
        .banner h1 { font-size: 19px; margin: 14px 0 0; }
        .body { padding: 24px 26px 28px; }
        p { margin: 0 0 14px; font-size: 15px; line-height: 1.65; color: #475569; }
        .warn { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; border-radius: 10px; padding: 14px; font-weight: 500; }
        .ref { font-family: monospace; font-size: 14px; background: #f1f5f9; padding: 10px 12px; border-radius: 8px; word-break: break-all; }
        a.btn { display: block; text-align: center; padding: 12px 18px; border-radius: 9px; font-weight: 600; text-decoration: none; background: var(--navy); color: #fff; margin-top: 18px; }
    </style>
</head>
<body>
@php
    $siteName = \App\Models\SiteSetting::get('site_name', 'Gram Panchayat');
    $siteLogo = \App\Models\SiteSetting::get('site_logo', '');
    $phone = \App\Models\SiteSetting::get('contact_phone', '');
@endphp
<div class="wrap">
    <div class="brand">
        @if($siteLogo)
            <img src="{{ asset('storage/' . $siteLogo) }}" alt="">
        @else
            <i class="fas fa-landmark"></i>
        @endif
        <span>{{ $siteName }}</span>
    </div>

    <div class="card">
        <div class="banner">
            <i class="fas fa-triangle-exclamation"></i>
            <h1>Payment could not be verified</h1>
        </div>
        <div class="body">
            <p>{{ $reason }}</p>

            {{-- The single most important instruction: we cannot tell them
                 whether they were charged, so paying again risks paying twice. --}}
            <p class="warn">
                Please do not attempt the payment again until this is checked. If money
                was debited it will either be credited to your account shortly or
                reversed automatically by your bank.
            </p>

            @if($reference)
                <p style="margin-bottom:6px;">Quote this reference:</p>
                <p class="ref">{{ $reference }}</p>
            @endif

            <p>
                Contact the Gram Panchayat office
                @if($phone)
                    on <strong>{{ $phone }}</strong>
                @endif
                to confirm the status of this payment.
            </p>

            <a class="btn" href="{{ url('/') }}">Back to home</a>
        </div>
    </div>
</div>
</body>
</html>
