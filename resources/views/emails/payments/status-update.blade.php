<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status Update</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
@php
    $status = strtolower((string)($payload['status'] ?? 'success'));
    $isFailed = $status === 'failed';
    $statusText = $isFailed ? 'Payment Failed' : 'Payment Successful';
    $accent = $isFailed ? '#dc2626' : '#166534';
    $banner = $isFailed ? '#fef2f2' : '#ecfdf5';
@endphp
<div style="max-width:680px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
    <div style="padding:20px 24px;background:{{ $accent }};color:#ffffff;">
        <h2 style="margin:0;font-size:22px;">{{ $statusText }}</h2>
        <p style="margin:8px 0 0;font-size:13px;opacity:0.95;">Gram Panchayat Tax Payment System</p>
    </div>

    <div style="padding:24px;">
        <p style="margin:0 0 14px;">Dear {{ $payload['citizen_name'] ?? 'Citizen' }},</p>
        <p style="margin:0 0 14px;">{{ $isFailed ? 'Your online payment attempt could not be completed.' : 'Your online payment was processed successfully.' }}</p>

        <div style="padding:12px 14px;border:1px solid #e2e8f0;background:{{ $banner }};border-radius:8px;margin:0 0 16px;">
            <strong style="color:{{ $accent }};">Current Status: {{ $statusText }}</strong>
        </div>

        <table style="width:100%;border-collapse:collapse;margin:16px 0;">
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;width:220px;">Transaction ID</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['transaction_id'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Tax Type</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ ($payload['tax_type'] ?? '') === 'water_tax' ? 'Water Tax' : 'Property Tax' }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Attempted At</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($payload['attempted_at'] ?? now())->format('d M Y, h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Amount</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">Rs {{ number_format((float)($payload['amount'] ?? 0), 2) }}</td>
            </tr>
        </table>

        @if($isFailed && !empty($payload['failure_reason']))
            <p style="margin:0 0 10px;"><strong>Reason:</strong> {{ $payload['failure_reason'] }}</p>
        @endif

        @if(!empty($payload['next_step']))
            <p style="margin:0 0 12px;"><strong>Next Step:</strong> {{ $payload['next_step'] }}</p>
        @endif

        @if(!empty($payload['retry_link']) && $isFailed)
            <p style="margin:14px 0 0;">
                <a href="{{ $payload['retry_link'] }}" style="display:inline-block;background:#0f172a;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:600;">Retry Payment</a>
            </p>
        @endif
    </div>
</div>
</body>
</html>
