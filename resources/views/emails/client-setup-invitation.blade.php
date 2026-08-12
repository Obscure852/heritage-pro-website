<!doctype html>
<html lang="en">
<body style="margin:0;background:#f4f7fc;color:#172033;font-family:Arial,sans-serif;line-height:1.6;">
    <div style="max-width:620px;margin:0 auto;padding:36px 20px;">
        <div style="background:#101630;color:#fff;padding:24px 28px;border-radius:14px 14px 0 0;">
            <strong style="font-size:20px;">Heritage Pro</strong>
            <div style="margin-top:4px;color:rgba(255,255,255,.68);font-size:12px;letter-spacing:.08em;text-transform:uppercase;">Client setup</div>
        </div>
        <div style="background:#fff;padding:30px 28px;border:1px solid #dce5f0;border-top:0;border-radius:0 0 14px 14px;">
            <p>Hello {{ $invitation->contact_name ?: 'there' }},</p>
            <p>You have been invited to complete the Heritage Pro client setup questionnaire. You can save your progress and return later using the same link.</p>
            <p><a href="{{ $setupUrl }}" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700;">Open client setup</a></p>
            <p style="font-size:13px;color:#64748b;">This invitation expires {{ optional($invitation->expires_at)->format('d M Y H:i') }}. You will be asked to verify your email before accessing saved information.</p>
            <p style="font-size:13px;color:#64748b;">If you were not expecting this invitation, you can ignore this email.</p>
        </div>
    </div>
</body>
</html>
