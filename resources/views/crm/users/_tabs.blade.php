@php
    $tabItems = [
        'profile' => ['label' => 'Profile', 'icon' => 'bx bx-id-card'],
        'qualifications' => ['label' => 'Qualifications', 'icon' => 'bx bx-certification'],
        'roles' => ['label' => 'Roles allocation', 'icon' => 'bx bx-shield-quarter'],
        'history' => ['label' => 'Login history', 'icon' => 'bx bx-time-five'],
        'settings' => ['label' => 'Settings', 'icon' => 'bx bx-cog'],
    ];
@endphp

<div class="crm-tabs crm-tabs-top">
    @foreach ($tabItems as $tabKey => $tab)
        <a href="{{ route('crm.users.edit', ['user' => $user, 'tab' => $tabKey]) }}" @class(['crm-tab', 'is-active' => $activeTab === $tabKey])>
            <i class="{{ $tab['icon'] }}"></i>
            <span>{{ $tab['label'] }}</span>
        </a>
    @endforeach
</div>
