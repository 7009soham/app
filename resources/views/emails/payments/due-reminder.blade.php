<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Due Reminder</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
<div style="max-width:680px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
    <div style="padding:20px 24px;background:#1e3a5f;color:#ffffff;">
        <h2 style="margin:0;font-size:22px;">Payment Due Reminder</h2>
        <p style="margin:8px 0 0;font-size:13px;opacity:0.95;">Gram Panchayat Tax Payment System</p>
    </div>

    <div style="padding:24px;">
        <p style="margin:0 0 14px;">Dear {{ $payload['citizen_name'] ?? 'Citizen' }},</p>
        <p style="margin:0 0 14px;">Please make your {{ ($payload['tax_type'] ?? '') === 'water_tax' ? 'Water Tax' : 'Property Tax' }} payment before <strong>{{ $payload['due_date'] ?? '-' }}</strong> to avoid penalty.</p>

        <table style="width:100%;border-collapse:collapse;margin:16px 0;">
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Customer No</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['customer_no'] ?? '-' }}</td>
            </tr>
            @if(!empty($payload['bill_no']))
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Bill No</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['bill_no'] }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Outstanding Amount</td>
                <td style="padding:10px;border:1px solid #e2e8f0;font-weight:700;">Rs {{ number_format((float)($payload['amount_due'] ?? 0), 2) }}</td>
            </tr>
            @if(!empty($payload['billing_period']))
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Billing Period</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['billing_period'] }}</td>
            </tr>
            @endif
        </table>

        @if(!empty($payload['payment_link']))
        <p style="margin:18px 0;">
            <a href="{{ $payload['payment_link'] }}" style="display:inline-block;background:#f97316;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:600;">Open Portal for Payment</a>
        </p>
        @endif

        <p style="margin:16px 0 0;font-size:13px;color:#475569;">If already paid, please ignore this reminder.</p>
    </div>
</div>
</body>
</html>
