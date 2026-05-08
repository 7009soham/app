<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification OTP</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
<div style="max-width:680px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
    <div style="padding:20px 24px;background:#1e3a5f;color:#ffffff;">
        <h2 style="margin:0;font-size:22px;">Email Verification OTP</h2>
        <p style="margin:8px 0 0;font-size:13px;opacity:0.95;">Gram Panchayat Citizen Portal</p>
    </div>

    <div style="padding:24px;">
        <p style="margin:0 0 14px;">Dear {{ $payload['citizen_name'] ?? 'Citizen' }},</p>
        <p style="margin:0 0 14px;">Use the OTP below to verify your email address:</p>

        <div style="margin:16px 0;padding:14px 16px;border:1px solid #e2e8f0;background:#f8fafc;border-radius:10px;text-align:center;">
            <p style="margin:0 0 8px;font-size:13px;color:#475569;">Your OTP</p>
            <p style="margin:0;font-size:30px;font-weight:700;letter-spacing:6px;color:#0f172a;">{{ $payload['otp'] ?? '000000' }}</p>
        </div>

        <p style="margin:0 0 12px;font-size:13px;color:#475569;">This OTP expires in {{ $payload['expires_in_minutes'] ?? 10 }} minutes.</p>
        <p style="margin:0;font-size:13px;color:#475569;">If you did not request this OTP, please ignore this email.</p>
    </div>
</div>
</body>
</html>
