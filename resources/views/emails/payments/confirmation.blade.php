<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
<div style="max-width:680px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
    <div style="padding:20px 24px;background:#1e3a5f;color:#ffffff;">
        <h2 style="margin:0;font-size:22px;">Payment Confirmation</h2>
        <p style="margin:8px 0 0;font-size:13px;opacity:0.95;">Gram Panchayat Tax Payment System</p>
    </div>

    <div style="padding:24px;">
        <p style="margin:0 0 14px;">Dear {{ $payload['citizen_name'] ?? 'Citizen' }},</p>
        <p style="margin:0 0 14px;">Your payment has been received successfully. Thank you for making your {{ ($payload['tax_type'] ?? '') === 'water_tax' ? 'Water Tax' : 'Property Tax' }} payment on time.</p>

        <table style="width:100%;border-collapse:collapse;margin:16px 0;">
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Transaction ID</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['transaction_id'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Payment Date</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($payload['payment_date'] ?? now())->format('d M Y, h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Invoice No</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['invoice_number'] ?? '-' }}</td>
            </tr>
            @if(!empty($payload['billing_period']))
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Billing Period</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['billing_period'] }}</td>
            </tr>
            @endif
            @if(!empty($payload['due_date']))
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Due Date</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($payload['due_date'])->format('d M Y') }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Tax Amount</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">Rs {{ number_format((float)($payload['tax_amount'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Convenience Fee</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">Rs {{ number_format((float)($payload['convenience_fee'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#ecfdf5;font-weight:700;">Total Paid</td>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#ecfdf5;font-weight:700;">Rs {{ number_format((float)($payload['total_amount'] ?? 0), 2) }}</td>
            </tr>
        </table>

        @if(!empty($payload['invoice_link']))
        <p style="margin:18px 0;">
            <a href="{{ $payload['invoice_link'] }}" style="display:inline-block;background:#f97316;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:600;">View Invoice</a>
        </p>
        <p style="margin:0;font-size:13px;color:#475569;">The invoice link always reflects your latest payment-updated bill.</p>
        @endif

        <p style="margin:16px 0 0;font-size:13px;color:#475569;">Please keep this email for your records.</p>
    </div>
</div>
</body>
</html>
