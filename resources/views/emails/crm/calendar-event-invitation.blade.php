<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Calendar invitation</title>
</head>
<body style="margin: 0; padding: 0; background: #f4f7fb; color: #0f172a; font-family: Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f4f7fb; padding: 28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; background: #ffffff; border: 1px solid #dbe5f0; border-radius: 6px; overflow: hidden;">
                    <tr>
                        <td style="padding: 24px 28px; background: linear-gradient(135deg, #2563eb 0%, #36b9cc 100%); color: #ffffff;">
                            <p style="margin: 0 0 8px; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;">Calendar Invitation</p>
                            <h1 style="margin: 0; font-size: 24px; line-height: 1.25;">{{ $event->title }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px;">
                            <p style="margin: 0 0 18px; color: #334155; font-size: 15px; line-height: 1.6;">
                                Hi {{ $attendee->display_name ?: 'there' }}, you have been added to a CRM calendar event. Please confirm your availability.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 22px; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 10px 0; color: #64748b; font-size: 13px; width: 130px;">When</td>
                                    <td style="padding: 10px 0; color: #0f172a; font-size: 14px;">
                                        @if ($event->all_day)
                                            {{ $event->starts_at?->format('M d, Y') }} (all day)
                                        @else
                                            {{ $event->starts_at?->format('M d, Y H:i') }} - {{ $event->ends_at?->format('H:i') }}
                                        @endif
                                    </td>
                                </tr>
                                @if ($event->location)
                                    <tr>
                                        <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Location</td>
                                        <td style="padding: 10px 0; color: #0f172a; font-size: 14px;">{{ $event->location }}</td>
                                    </tr>
                                @endif
                                @if ($organizerName)
                                    <tr>
                                        <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Organizer</td>
                                        <td style="padding: 10px 0; color: #0f172a; font-size: 14px;">{{ $organizerName }}</td>
                                    </tr>
                                @endif
                                @if ($event->calendar)
                                    <tr>
                                        <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Calendar</td>
                                        <td style="padding: 10px 0; color: #0f172a; font-size: 14px;">{{ $event->calendar->name }}</td>
                                    </tr>
                                @endif
                            </table>

                            @if ($event->description)
                                <p style="margin: 0 0 22px; padding: 14px 16px; background: #f8fafc; border-left: 4px solid #2563eb; color: #334155; font-size: 14px; line-height: 1.6;">
                                    {{ $event->description }}
                                </p>
                            @endif

                            <p style="margin: 0 0 14px; color: #0f172a; font-size: 15px; font-weight: 700;">Confirm availability</p>

                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    @foreach ($responseOptions as $option)
                                        <td style="padding: 0 8px 8px 0;">
                                            <a href="{{ $option['url'] }}" style="display: inline-block; padding: 11px 16px; border-radius: 4px; background: {{ $option['key'] === 'available' ? '#0ab39c' : ($option['key'] === 'tentative' ? '#f7b84b' : '#f06548') }}; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700;">
                                                {{ $option['label'] }}
                                            </a>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>

                            <p style="margin: 18px 0 0; color: #64748b; font-size: 12px; line-height: 1.5;">
                                These links are unique to this invitation and expire after the event response window closes.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
