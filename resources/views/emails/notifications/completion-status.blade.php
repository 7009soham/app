<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completion and Status Notification</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
<div style="max-width:700px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
    <div style="padding:20px 24px;background:#1e3a5f;color:#ffffff;">
        <h2 style="margin:0;font-size:22px;">Completion and Status Notification</h2>
        <p style="margin:8px 0 0;font-size:13px;opacity:0.95;">Gram Panchayat Citizen Services</p>
    </div>

    <div style="padding:24px;">
        <p style="margin:0 0 14px;">Dear {{ $payload['citizen_name'] ?? 'Citizen' }},</p>
        <p style="margin:0 0 14px;">Your {{ $payload['service_name'] ?? 'service request' }} update is now available.</p>

        <table style="width:100%;border-collapse:collapse;margin:16px 0;">
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;width:220px;">Reference Number</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['ticket_number'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Previous Status</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ $payload['previous_status'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Current Status</td>
                <td style="padding:10px;border:1px solid #e2e8f0;font-weight:700;color:#166534;">{{ $payload['current_status'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #e2e8f0;background:#f8fafc;">Updated At</td>
                <td style="padding:10px;border:1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($payload['updated_at'] ?? now())->format('d M Y, h:i A') }}</td>
            </tr>
        </table>

        @if(!empty($payload['summary']))
            <p style="margin:0 0 12px;"><strong>Update Summary:</strong> {{ $payload['summary'] }}</p>
        @endif

        @if(!empty($payload['admin_remarks']))
            <p style="margin:0 0 12px;"><strong>Admin Remarks:</strong> {{ $payload['admin_remarks'] }}</p>
        @endif

        @if(!empty($payload['portal_link']))
            <p style="margin:16px 0 0;">
                <a href="{{ $payload['portal_link'] }}" style="display:inline-block;background:#f97316;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:600;">View Full Details</a>
            </p>
        @endif

        <p style="margin:16px 0 0;font-size:13px;color:#64748b;">This is an automated status update. Please do not reply to this email.</p>
    </div>
</div>
</body>
</html>
