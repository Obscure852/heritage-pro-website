{{ $companyName }}

Create or reset your CRM password

Hello {{ $recipientName !== '' ? $recipientName : 'there' }},

Use the secure link below to create a new password for {{ $recipientEmail }} and continue into the CRM workspace.

{{ $resetUrl }}

This link expires in {{ $expireMinutes }} minutes.

If your account was created for you by an administrator, this is the same link you use to set your password for the first time.

If you did not request a password reset, no further action is required.

@if ($companyEmail || $companyPhone || $companyWebsiteUrl)
Need help?
@if ($companyEmail)
Email: {{ $companyEmail }}
@endif
@if ($companyPhone)
Phone: {{ $companyPhone }}
@endif
@if ($companyWebsiteUrl)
Website: {{ $companyWebsiteLabel }}
@endif
@endif
