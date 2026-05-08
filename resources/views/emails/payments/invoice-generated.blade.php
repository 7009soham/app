<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Generated</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a;">
<div style="max-width:760px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
    <div style="padding:20px 24px;background:#0f766e;color:#ffffff;display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
        <div>
            <h2 style="margin:0;font-size:22px;line-height:1.3;">Invoice Generated</h2>
            <p style="margin:8px 0 0;font-size:13px;opacity:0.95;">Gram Panchayat Tax & Citizen Services Portal</p>
        </div>
        <div style="text-align:right;min-width:180px;">
            <p style="margin:0;font-size:12px;opacity:0.9;">Invoice No</p>
            <p style="margin:4px 0 0;font-size:16px;font-weight:700;">{{ $payload['invoice_number'] ?? '-' }}</p>
        </div>
    </div>

    <div style="padding:24px;">
        <p style="margin:0 0 12px;">Dear {{ $payload['citizen_name'] ?? 'Citizen' }},</p>
        <p style="margin:0 0 16px;color:#334155;">
            Your {{ ($payload['tax_type'] ?? '') === 'water_tax' ? 'Water Tax' : 'Property Tax' }} invoice has been generated. Please review the details below and complete payment before the due date.
        </p>

        <table style="width:100%;border-collapse:collapse;margin:16px 0;border:1px solid #e2e8f0;">
            <tr>
                <td style="padding:12px;border:1px solid #e2e8f0;background:#f8fafc;width:220px;">Customer Number</td>
                <td style="padding:12px;border:1px solid #e2e8f0;">{{ $payload['customer_no'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:12px;border:1px solid #e2e8f0;background:#f8fafc;">Invoice Date</td>
                <td style="padding:12px;border:1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($payload['invoice_date'] ?? now())->format('d M Y, h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding:12px;border:1px solid #e2e8f0;background:#f8fafc;">Billing Period</td>
                <td style="padding:12px;border:1px solid #e2e8f0;">{{ $payload['billing_period'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:12px;border:1px solid #e2e8f0;background:#f8fafc;">Due Date</td>
                <td style="padding:12px;border:1px solid #e2e8f0;font-weight:700;color:#b45309;">{{ \Carbon\Carbon::parse($payload['due_date'] ?? now())->format('d M Y') }}</td>
            </tr>
        </table>

        <table style="width:100%;border-collapse:collapse;margin:18px 0;border:1px solid #e2e8f0;">
            <thead>
            <tr>
                <th style="text-align:left;padding:12px;border:1px solid #e2e8f0;background:#f8fafc;font-size:13px;">Description</th>
                <th style="text-align:right;padding:12px;border:1px solid #e2e8f0;background:#f8fafc;font-size:13px;">Amount (Rs)</th>
            </tr>
            </thead>
            <tbody>
            @foreach(($payload['line_items'] ?? []) as $lineItem)
                <tr>
                    <td style="padding:12px;border:1px solid #e2e8f0;">{{ $lineItem['description'] ?? '-' }}</td>
                    <td style="padding:12px;border:1px solid #e2e8f0;text-align:right;">{{ number_format((float)($lineItem['amount'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="padding:12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">Subtotal</td>
                <td style="padding:12px;border:1px solid #e2e8f0;background:#f8fafc;text-align:right;font-weight:600;">{{ number_format((float)($payload['subtotal'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td style="padding:12px;border:1px solid #e2e8f0;background:#ecfdf5;font-weight:700;">Total Amount Due</td>
                <td style="padding:12px;border:1px solid #e2e8f0;background:#ecfdf5;text-align:right;font-weight:700;">{{ number_format((float)($payload['amount_due'] ?? 0), 2) }}</td>
            </tr>
            </tbody>
        </table>

        @if(!empty($payload['late_fee_note']))
            <p style="margin:0 0 14px;font-size:13px;color:#475569;">{{ $payload['late_fee_note'] }}</p>
        @endif

        @if(!empty($payload['payment_link']))
            <p style="margin:18px 0;">
                <a href="{{ $payload['payment_link'] }}" style="display:inline-block;background:#0f766e;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:600;">Pay Invoice Now</a>
            </p>
        @endif

        <p style="margin:12px 0 0;font-size:13px;color:#64748b;">
            For support, contact {{ $payload['support_email'] ?? 'the Gram Panchayat office' }}.
        </p>
    </div>
</div>
</body>
</html>
