<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Payment...</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 40px 32px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .logo { font-size: 36px; margin-bottom: 16px; }
        h2 { font-size: 20px; color: #1e293b; margin-bottom: 8px; }
        p { color: #64748b; font-size: 14px; margin-bottom: 24px; }
        .spinner {
            width: 40px; height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .amount {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 700;
            color: #166534;
        }
        .cancel-link {
            display: inline-block;
            margin-top: 16px;
            font-size: 13px;
            color: #94a3b8;
            text-decoration: none;
        }
        .cancel-link:hover { color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">💳</div>
        <div class="spinner"></div>
        <h2>Opening Payment Gateway</h2>
        <p>Please wait while we redirect you to Razorpay's secure payment page.</p>

        <div class="amount">₹{{ number_format($amount / 100, 2) }}</div>

        <p style="font-size:12px; color:#94a3b8;">Do not refresh or close this page.</p>
        <a href="{{ route('citizen.payment-history') }}" class="cancel-link">Cancel and go back</a>
    </div>

    {{-- Hidden form for Razorpay callback POST --}}
    <form id="razorpay-return-form" action="{{ route('citizen.payment.razorpay-return') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
        <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
        <input type="hidden" name="razorpay_signature" id="rzp_signature">
    </form>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            key: '{{ $keyId }}',
            amount: {{ $amount }},
            currency: 'INR',
            order_id: '{{ $orderId }}',
            name: '{{ config('app.name', 'Neral Gram Panchayat') }}',
            description: '{{ $description }}',
            prefill: {
                name: '{{ addslashes($citizenName) }}',
                email: '{{ addslashes($citizenEmail ?? '') }}',
                contact: '{{ $citizenPhone ?? '' }}',
            },
            theme: { color: '#1e40af' },
            handler: function (response) {
                document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
                document.getElementById('rzp_order_id').value = response.razorpay_order_id;
                document.getElementById('rzp_signature').value = response.razorpay_signature;
                document.getElementById('razorpay-return-form').submit();
            },
            modal: {
                ondismiss: function () {
                    window.location.href = '{{ route('citizen.payment-history') }}?cancelled=1';
                }
            }
        };

        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function (response) {
            document.getElementById('rzp_payment_id').value = response.error.metadata.payment_id || '';
            document.getElementById('rzp_order_id').value = response.error.metadata.order_id || '';
            document.getElementById('rzp_signature').value = '';
            document.getElementById('razorpay-return-form').submit();
        });

        // Auto-open after a short delay so the page is visible
        setTimeout(function () { rzp.open(); }, 500);
    </script>
</body>
</html>
