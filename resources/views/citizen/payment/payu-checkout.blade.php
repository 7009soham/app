<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Nothing here should be cached or indexed: it carries a one-time hash. --}}
    <meta name="robots" content="noindex, nofollow">
    <title>Redirecting to PayU…</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 24px;
        }
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 36px 28px;
            max-width: 380px;
            width: 100%;
            text-align: center;
        }
        .spinner {
            width: 38px;
            height: 38px;
            margin: 0 auto 18px;
            border: 3px solid #e2e8f0;
            border-top-color: #1a365d;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .spinner { animation: none; } }
        h1 { font-size: 18px; margin: 0 0 8px; color: #1a365d; }
        p { margin: 0 0 6px; font-size: 14px; color: #475569; line-height: 1.6; }
        .amount { font-size: 22px; font-weight: 600; color: #1a365d; margin: 14px 0; }
        .warn { margin-top: 18px; font-size: 12px; color: #92400e; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 10px; }
        button { margin-top: 16px; padding: 11px 20px; font-size: 14px; font-weight: 600; color: #fff; background: #1a365d; border: 0; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h1>Taking you to PayU</h1>
        <p>{{ $label }}</p>
        <div class="amount">₹{{ number_format((float) $amount, 2) }}</div>
        <p>Please wait. Do not press back or close this window.</p>

        <form id="payuForm" method="POST" action="{{ $action }}">
            @foreach($fields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            {{-- Shown only if the automatic submit does not happen, e.g. with
                 JavaScript disabled. --}}
            <noscript>
                <p class="warn">JavaScript is turned off. Tap the button to continue.</p>
                <button type="submit">Continue to PayU</button>
            </noscript>
        </form>

        <p class="warn" id="fallback" style="display:none;">
            Still here? <button type="submit" form="payuForm">Continue to PayU</button>
        </p>
    </div>

    <script>
        // Submitted on load rather than inline so the form is fully parsed.
        window.addEventListener('load', function () {
            document.getElementById('payuForm').submit();
        });

        // If the redirect has not happened after a few seconds, give the
        // citizen a manual way out instead of an endless spinner.
        setTimeout(function () {
            var fallback = document.getElementById('fallback');
            if (fallback) {
                fallback.style.display = 'block';
            }
        }, 6000);
    </script>
</body>
</html>
