@php
    $colors = [
        'draft' => ['bg' => '#64748b20', 'text' => '#64748b'],
        'pending' => ['bg' => '#f7b84b20', 'text' => '#d97706'],
        'approved' => ['bg' => '#0ab39c20', 'text' => '#0ab39c'],
        'rejected' => ['bg' => '#f0654820', 'text' => '#f06548'],
        'cancelled' => ['bg' => '#343a4020', 'text' => '#6b7280'],
    ];
    $c = $colors[$status] ?? $colors['draft'];
@endphp
<span class="crm-pill" style="background: {{ $c['bg'] }}; color: {{ $c['text'] }};">
    {{ ucfirst($status) }}
</span>
