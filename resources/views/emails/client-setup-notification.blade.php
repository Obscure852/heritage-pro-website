<p>Hello{{ $notification->recipient_name ? ' ' . $notification->recipient_name : '' }},</p>

<h2>{{ $notification->payload['heading'] ?? $notification->subject }}</h2>

<p>{{ $notification->payload['message'] ?? 'There is an update to a Heritage Pro client setup submission.' }}</p>

@if (! empty($notification->payload['details']))
    <ul>
        @foreach ($notification->payload['details'] as $detail)
            <li>{{ $detail }}</li>
        @endforeach
    </ul>
@endif

@if (! empty($notification->payload['action_url']))
    <p>
        <a href="{{ $notification->payload['action_url'] }}">
            {{ $notification->payload['action_label'] ?? 'Open client setup' }}
        </a>
    </p>
@endif

<p>Heritage Pro</p>
