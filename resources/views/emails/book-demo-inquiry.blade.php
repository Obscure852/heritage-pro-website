<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New Heritage Pro Demo Request</title>
</head>
<body style="margin:0; padding:24px; background:#f5f7fb; font-family:Inter, Arial, sans-serif; color:#1f2937;">
    <div style="max-width:680px; margin:0 auto; background:#ffffff; border:1px solid #dfe4ef; border-radius:18px; overflow:hidden;">
        <div style="padding:24px 28px; background:linear-gradient(135deg, #434DB0 0%, #363FA0 100%); color:#ffffff;">
            <div style="font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; opacity:0.82;">Book Demo Request</div>
            <h1 style="margin:12px 0 0; font-size:28px; line-height:1.15;">New website inquiry received.</h1>
        </div>

        <div style="padding:28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                    <td style="padding:0 0 18px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:4px;">Full name</div>
                        <div style="font-size:16px; font-weight:600;">{{ $submission['full_name'] }}</div>
                    </td>
                    <td style="padding:0 0 18px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:4px;">Role</div>
                        <div style="font-size:16px; font-weight:600;">{{ $submission['role'] }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 0 18px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:4px;">Institution</div>
                        <div style="font-size:16px; font-weight:600;">{{ $submission['institution'] }}</div>
                    </td>
                    <td style="padding:0 0 18px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:4px;">Work email</div>
                        <div style="font-size:16px; font-weight:600;">{{ $submission['work_email'] }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 0 18px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:4px;">Phone</div>
                        <div style="font-size:16px; font-weight:600;">{{ $submission['phone'] }}</div>
                    </td>
                    <td style="padding:0 0 18px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:4px;">Edition</div>
                        <div style="font-size:16px; font-weight:600;">{{ $submission['edition'] }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:0 0 18px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:4px;">Number of learners</div>
                        <div style="font-size:16px; font-weight:600;">{{ $submission['learner_band'] }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:0;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:8px;">Notes</div>
                        <div style="padding:16px 18px; border-radius:14px; background:#f7f8fb; border:1px solid #dfe4ef; font-size:15px; line-height:1.65;">
                            {{ $submission['notes'] !== '' ? $submission['notes'] : 'No additional notes provided.' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
