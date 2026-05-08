<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Connected Successfully</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
<div style="max-width:680px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
    <div style="padding:20px 24px;background:#1e3a5f;color:#ffffff;">
        <h2 style="margin:0;font-size:22px;">Gmail Connected Successfully</h2>
        <p style="margin:8px 0 0;font-size:13px;opacity:0.95;">Gram Panchayat Citizen Portal</p>
    </div>

    <div style="padding:24px;">
        <p style="margin:0 0 14px;">Dear {{ $payload['citizen_name'] ?? 'Citizen' }},</p>
        <p style="margin:0 0 14px;">Your Gmail account has been successfully linked with your Gram Panchayat citizen account.</p>

        <div style="margin:16px 0;padding:14px 16px;border:1px solid #e2e8f0;background:#f8fafc;border-radius:10px;">
            <p style="margin:0 0 10px;font-weight:700;">You will now receive:</p>
            <ul style="margin:0;padding-left:18px;">
                <li style="margin:6px 0;">Invoices</li>
                <li style="margin:6px 0;">Due date reminders</li>
                <li style="margin:6px 0;">Completion and status notifications</li>
            </ul>
        </div>

        <table style="width:100%;border-collapse:collapse;margin:16px 0;">
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Customer No</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['customer_no'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Phone</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['citizen_phone'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Linked On</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($payload['linked_at'] ?? now())->format('d M Y, h:i A') }}</td>
            </tr>
        </table>

        <p style="margin:16px 0 0;font-size:13px;color:#475569;">If this was not done by you, please disconnect Gmail from your profile and contact the Gram Panchayat office immediately.</p>
    </div>
</div>
</body>
</html>
