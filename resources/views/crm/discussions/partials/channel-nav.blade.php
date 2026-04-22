@php
    $active = $active ?? 'hub';
    $items = [
        [
            'key' => 'hub',
            'label' => 'Discussions Hub',
            'icon' => 'bx bx-grid-alt',
            'url' => route('crm.discussions.index'),
        ],
        [
            'key' => 'app',
            'label' => 'App Messaging',
            'icon' => 'bx bx-message-square-dots',
            'url' => route('crm.discussions.app.workspace'),
        ],
        [
            'key' => 'email',
            'label' => 'Email',
            'icon' => 'bx bx-envelope',
            'url' => route('crm.discussions.email.index'),
        ],
        [
            'key' => 'whatsapp',
            'label' => 'WhatsApp',
            'icon' => 'bx bxl-whatsapp',
            'url' => route('crm.discussions.whatsapp.index'),
        ],
    ];
@endphp

<nav class="crm-discussions-nav">
    @foreach ($items as $item)
        <a href="{{ $item['url'] }}" class="crm-discussions-nav-link {{ $active === $item['key'] ? 'active' : '' }}">
            <i class="{{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
