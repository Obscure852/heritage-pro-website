<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create or reset your password for {{ $companyName }}</title>
</head>
<body style="margin:0; padding:0; background:#edf4ff; font-family:'Segoe UI', Arial, sans-serif; color:#0f172a;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        Use this secure link to create or reset your CRM password and continue into the workspace.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; background:#edf4ff;">
        <tr>
            <td align="center" style="padding:24px 14px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px; border-collapse:separate;">
                    <tr>
                        <td style="border:1px solid #dbe5f0; border-radius:20px; overflow:hidden; background:#ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:24px 28px 24px; background:linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); border-bottom:1px solid #e7eef8;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                            <tr>
                                                <td valign="middle">
                                                    <div style="font-size:21px; line-height:1.1; font-weight:800; color:#0f172a;">{{ $companyName }}</div>
                                                    <div style="margin-top:4px; font-size:11px; line-height:1.4; letter-spacing:0.12em; text-transform:uppercase; color:#64748b;">CRM access</div>
                                                </td>
                                                <td align="right" valign="middle">
                                                    <span style="display:inline-block; padding:7px 12px; border-radius:999px; background:#e0edff; border:1px solid #bdd5ff; font-size:11px; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; color:#2563eb;">Secure access</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:28px 28px 16px;">
                                        <div style="display:inline-block; padding:7px 12px; border-radius:999px; background:#eff6ff; border:1px solid #dbeafe; font-size:11px; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; color:#2563eb;">Password reset</div>
                                        <h1 style="margin:16px 0 0; font-size:34px; line-height:1.08; letter-spacing:-0.03em; font-weight:800; color:#0f172a;">Create or reset your CRM password</h1>
                                        <p style="margin:16px 0 0; font-size:16px; line-height:1.7; color:#475569;">
                                            Hello {{ $recipientName !== '' ? $recipientName : 'there' }}, use the secure button below to choose a new password for
                                            <strong style="color:#0f172a;">{{ $recipientEmail }}</strong> and continue into the CRM workspace.
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 28px 8px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                            <tr>
                                                <td style="padding:18px 20px; border-radius:14px; background:#f8fafc; border-left:4px solid #2563eb;">
                                                    <div style="font-size:16px; line-height:1.5; font-weight:800; color:#0f172a;">Use this link to create your password or recover access.</div>
                                                    <div style="margin-top:6px; font-size:14px; line-height:1.7; color:#64748b;">
                                                        If your account was created for you by an administrator, this is the same link you use to set your password for the first time.
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding:18px 28px 6px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                            <tr>
                                                <td align="center" bgcolor="#2563eb" style="border-radius:8px;">
                                                    <a href="{{ $resetUrl }}" style="display:inline-block; padding:15px 28px; font-size:15px; line-height:1.2; font-weight:800; color:#ffffff; text-decoration:none; border-radius:8px; background:#2563eb;">
                                                        Reset password →
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 28px 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                            <tr>
                                                <td style="padding:16px 18px; border:1px solid #dbe5f0; border-radius:14px; background:#ffffff;">
                                                    <div style="font-size:13px; line-height:1.6; color:#475569;">
                                                        This secure link expires in <strong style="color:#0f172a;">{{ $expireMinutes }} minutes</strong>. If you did not request a password reset, no further action is required.
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:28px 28px 0;">
                                        <div style="font-size:12px; line-height:1.5; letter-spacing:0.1em; text-transform:uppercase; color:#64748b;">Backup link</div>
                                        <div style="margin-top:8px; padding:14px 16px; border-radius:14px; background:#f8fafc; border:1px dashed #cbd5e1; font-size:13px; line-height:1.8; color:#334155; word-break:break-all;">
                                            <a href="{{ $resetUrl }}" style="color:#1d4ed8; text-decoration:none;">{{ $resetUrl }}</a>
                                        </div>
                                    </td>
                                </tr>

                                @if ($companyEmail || $companyPhone || $companyWebsiteUrl)
                                    <tr>
                                        <td style="padding:22px 28px 28px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                                <tr>
                                                    <td style="padding:18px 20px; border-radius:16px; background:#eff6ff;">
                                                        <div style="font-size:12px; line-height:1.5; letter-spacing:0.1em; text-transform:uppercase; color:#2563eb; font-weight:800;">Need help?</div>
                                                        <div style="margin-top:8px; font-size:14px; line-height:1.8; color:#475569;">
                                                            @if ($companyEmail)
                                                                <span style="display:inline-block; margin-right:14px;">Email: <a href="mailto:{{ $companyEmail }}" style="color:#1d4ed8; text-decoration:none;">{{ $companyEmail }}</a></span>
                                                            @endif
                                                            @if ($companyPhone)
                                                                <span style="display:inline-block; margin-right:14px;">Phone: <span style="color:#0f172a;">{{ $companyPhone }}</span></span>
                                                            @endif
                                                            @if ($companyWebsiteUrl)
                                                                <span style="display:inline-block;">Website: <a href="{{ $companyWebsiteUrl }}" style="color:#1d4ed8; text-decoration:none;">{{ $companyWebsiteLabel }}</a></span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:18px 18px 0; font-size:12px; line-height:1.6; color:#64748b;">
                            This message was sent because a password reset was requested for your CRM account. If that was not you, you can safely ignore this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
