<!doctype html>
<html lang="en">
<body style="margin:0;background:#f4f7fc;color:#172033;font-family:Arial,sans-serif;line-height:1.6;">
    <div style="max-width:620px;margin:0 auto;padding:36px 20px;">
        <div style="background:#101630;color:#fff;padding:24px 28px;border-radius:14px 14px 0 0;">
            <strong style="font-size:20px;">Heritage Pro</strong>
            <div style="margin-top:4px;color:rgba(255,255,255,.68);font-size:12px;letter-spacing:.08em;text-transform:uppercase;">Verification code</div>
        </div>
        <div style="background:#fff;padding:30px 28px;border:1px solid #dce5f0;border-top:0;border-radius:0 0 14px 14px;">
            <p>Hello {{ $invitation->contact_name ?: 'there' }},</p>
            <p>Use the following code to access your Heritage Pro client setup:</p>
            <p style="font-size:32px;letter-spacing:.25em;font-weight:700;color:#243b7a;">{{ $code }}</p>
            <p style="font-size:13px;color:#64748b;">This code expires in {{ $expiresInMinutes }} minutes. Never share it with anyone who is not authorized to complete the setup.</p>
        </div>
    </div>
</body>
</html>
