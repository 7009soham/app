<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailTitle ?? 'System Notification' }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a;">
<div style="max-width:720px;margin:24px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
    <div style="padding:20px 24px;background:#1e3a5f;color:#ffffff;">
        <h2 style="margin:0;font-size:22px;">{{ $mailTitle ?? 'System Notification' }}</h2>
        <p style="margin:8px 0 0;font-size:13px;opacity:0.95;">{{ $mailSubtitle ?? (config('app.name') . ' Notifications') }}</p>
    </div>

    <div style="padding:24px;">
        {!! $bodyHtml ?? '' !!}

        @if(!empty($ctaLink) && !empty($ctaText))
            <p style="margin:18px 0 0;">
                <a href="{{ $ctaLink }}" style="display:inline-block;background:#f97316;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:600;">{{ $ctaText }}</a>
            </p>
        @endif
    </div>

    <div style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;">
        <p style="margin:0 0 6px;font-size:12px;color:#64748b;">This is a system-generated email from {{ config('app.name') }}.</p>
        <p style="margin:0;font-size:12px;color:#94a3b8;">&copy; {{ now()->format('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</div>
</body>
</html>
