<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Signed, single-outcome page: never cache or index it. --}}
    <meta name="robots" content="noindex, nofollow">
    <title>Payment {{ ucfirst($outcome) }} — {{ \App\Models\SiteSetting::get('site_name', 'Gram Panchayat') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --navy: #1a365d; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 24px; background: #f1f5f9;
            font-family: 'Inter', 'Noto Sans Devanagari', system-ui, sans-serif; color: #1e293b;
        }
        .wrap { max-width: 460px; width: 100%; }
        .brand {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-bottom: 16px; color: var(--navy); font-weight: 600; font-size: 16px;
        }
        .brand img { height: 34px; width: auto; max-width: 120px; object-fit: contain; }
        .brand i { font-size: 22px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; width: 100%; overflow: hidden; }
        .banner { padding: 30px 26px 24px; text-align: center; color: #fff; }
        .banner.success { background: linear-gradient(135deg, #15803d, #16a34a); }
        .banner.cancelled { background: linear-gradient(135deg, #334155, #475569); }
        .banner.deferred { background: linear-gradient(135deg, #b45309, #d97706); }
        .banner.failed { background: linear-gradient(135deg, #b91c1c, #dc2626); }
        .banner i { font-size: 42px; }
        .banner h1 { font-size: 20px; margin: 14px 0 0; }
        .body { padding: 24px 26px 28px; }
        .msg { font-size: 15px; line-height: 1.65; color: #475569; margin: 0 0 20px; }
        dl { margin: 0 0 22px; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 14px; font-size: 14px; }
        .row + .row { border-top: 1px solid #e2e8f0; }
        dt { color: #64748b; margin: 0; }
        dd { margin: 0; font-weight: 600; text-align: right; word-break: break-all; }
        .actions { display: grid; gap: 10px; }
        a.btn {
            display: block; text-align: center; padding: 12px 18px; border-radius: 9px;
            font-size: 15px; font-weight: 600; text-decoration: none;
        }
        .btn-primary { background: var(--navy); color: #fff; }
        .btn-ghost { border: 1px solid #cbd5e1; color: #334155; }
        .ref { font-size: 12px; color: #64748b; text-align: center; margin-top: 16px; }
    </style>
</head>
<body>
@php
    $siteName = \App\Models\SiteSetting::get('site_name', 'Gram Panchayat');
    $siteLogo = \App\Models\SiteSetting::get('site_logo', '');
@endphp
<div class="wrap">
{{-- Official identity: someone who has just paid money needs to see whose
     site confirmed it, even though this page sits outside the portal chrome. --}}
<div class="brand">
    @if($siteLogo)
        <img src="{{ asset('storage/' . $siteLogo) }}" alt="">
    @else
        <i class="fas fa-landmark"></i>
    @endif
    <span>{{ $siteName }}</span>
</div>
<div class="card">
    <div class="banner {{ $outcome }}">
        <i class="fas {{ ['success' => 'fa-circle-check', 'cancelled' => 'fa-circle-xmark', 'deferred' => 'fa-clock'][$outcome] ?? 'fa-triangle-exclamation' }}"></i>
        <h1>
            {{ ['success' => 'Payment Successful', 'cancelled' => 'Payment Cancelled', 'deferred' => 'Payment Received'][$outcome] ?? 'Payment Not Completed' }}
        </h1>
    </div>

    <div class="body">
        <p class="msg">{{ $message }}</p>

        <dl>
            <div class="row">
                <dt>Amount</dt>
                <dd>₹{{ number_format((float) $payment->amount, 2) }}</dd>
            </div>
            <div class="row">
                <dt>Tax</dt>
                <dd>{{ ucwords(str_replace('_', ' ', $payment->tax_type)) }}</dd>
            </div>
            <div class="row">
                <dt>Reference</dt>
                <dd>{{ $payment->transaction_id }}</dd>
            </div>
            @if($payment->paid_at)
                <div class="row">
                    <dt>Paid at</dt>
                    <dd>{{ $payment->paid_at->format('d M Y, g:i A') }}</dd>
                </div>
            @endif
        </dl>

        <div class="actions">
            @if($isLoggedIn)
                <a class="btn btn-primary" href="{{ route('citizen.payment-history') }}">View my transactions</a>
                <a class="btn btn-ghost" href="{{ route('citizen.dashboard') }}">Back to dashboard</a>
            @else
                {{-- Their session expired while they were at the gateway. The
                     outcome is still shown, because being told whether the money
                     went through matters more than being signed in. --}}
                <a class="btn btn-primary" href="{{ route('citizen.login') }}">Log in to view your records</a>
                <a class="btn btn-ghost" href="{{ url('/') }}">Back to home</a>
            @endif
        </div>

        <p class="ref">Keep the reference number if you need to contact the Gram Panchayat office.</p>
    </div>
</div>
</div>
</body>
</html>
