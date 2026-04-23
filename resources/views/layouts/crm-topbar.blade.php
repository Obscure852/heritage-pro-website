@php
    $crmUser = auth()->user();
    $moduleRegistry = app(\App\Services\Crm\CrmModuleRegistry::class);
    $launcherModules = $moduleRegistry->launcherModulesFor($crmUser);
    $companyName = $crmBrandingSettings?->company_name ?: 'Heritage Pro';
    $companyLogoUrl = $crmBrandingSettings?->company_logo_url ?: asset('assets/images/heritage-pro-logo.jpg');
    $userInitials = collect(preg_split('/\s+/', trim($crmUser->name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $segment) => strtoupper(substr($segment, 0, 1)))
        ->implode('');
@endphp
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex align-items-center">
            <div class="navbar-brand-box">
                <a href="{{ route('crm.dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" height="24">
                        <span class="logo-txt">{{ $companyName }}</span>
                    </span>
                </a>

                <a href="{{ route('crm.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" height="24">
                        <span class="logo-txt">{{ $companyName }}</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="bx bx-menu"></i>
            </button>

            <form class="app-search d-none d-lg-block" onsubmit="return false;">
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm" placeholder="Global Search..."
                        id="crm-global-search" autocomplete="off" aria-label="Global Search">
                    <span class="shortcut-hint" id="crm-search-shortcut-hint" aria-hidden="true"></span>
                    <i class="bx bx-search search-icon"></i>
                    <div class="search-results" id="crm-search-results" hidden></div>
                </div>
            </form>
        </div>

        <div class="d-flex align-items-center">
            @include('crm.attendance.partials.clock-button')

            <div class="staff-presence-launcher me-2" id="crm-presence-launcher">
                <button type="button" class="btn header-item staff-presence-trigger" id="crm-presence-trigger"
                    aria-haspopup="true" aria-expanded="false">
                    <span class="staff-presence-trigger-copy">
                        <span class="staff-presence-dot"></span>
                        <span class="staff-presence-trigger-label">Online CRM Users</span>
                    </span>
                    <span class="staff-presence-trigger-count" id="crm-presence-count">0</span>
                    <span class="staff-presence-trigger-unread" id="crm-presence-unread-badge" hidden>0</span>
                </button>

                <div class="crm-floating-panel staff-presence-panel" id="crm-presence-panel" hidden>
                    <div class="staff-presence-panel-header">
                        <div>
                            <strong>Online CRM users</strong>
                            <span>Presence updates while you work.</span>
                        </div>
                        <span id="crm-presence-panel-count">0 online</span>
                    </div>

                    <div class="staff-presence-search">
                        <input type="search" class="form-control form-control-sm" id="crm-presence-search"
                            placeholder="Search by name or role">
                    </div>

                    <div class="staff-presence-panel-note">
                        Use the launcher to see who is active now, review unread discussions, and jump straight into the right thread.
                    </div>

                    <div class="staff-presence-sound-control">
                        <div class="staff-presence-sound-actions">
                            <button
                                type="button"
                                class="btn btn-light crm-btn-light btn-sm staff-presence-sound-toggle {{ $crmUser->crm_discussion_sound_enabled ? 'is-enabled' : 'is-muted' }}"
                                id="crm-presence-sound-toggle"
                                data-enabled="{{ $crmUser->crm_discussion_sound_enabled ? 'true' : 'false' }}"
                                aria-pressed="{{ $crmUser->crm_discussion_sound_enabled ? 'true' : 'false' }}"
                            >
                                <i class="bx {{ $crmUser->crm_discussion_sound_enabled ? 'bx-volume-full' : 'bx-volume-mute' }}"></i>
                                <span>{{ $crmUser->crm_discussion_sound_enabled ? 'Sound on' : 'Sound off' }}</span>
                            </button>
                            <button
                                type="button"
                                class="btn btn-light crm-btn-light btn-sm staff-presence-sound-preview"
                                id="crm-presence-sound-preview"
                            >
                                <i class="bx bx-play-circle"></i>
                                <span>Test sound</span>
                            </button>
                        </div>
                        <span class="staff-presence-sound-status" id="crm-presence-sound-status">
                            {{ $crmUser->crm_discussion_sound_enabled
                                ? 'Sound plays for new unread activity only, not for the thread you already have open.'
                                : 'Discussion sounds are muted for this account.' }}
                        </span>
                    </div>

                    <div class="staff-presence-unread-panel" id="crm-presence-unread-panel" hidden>
                        <div class="staff-presence-unread-header">
                            <strong>Unread discussions</strong>
                            <a href="{{ route('crm.discussions.index') }}" class="btn btn-light crm-btn-light btn-sm">
                                <i class="bx bx-chat"></i> Open inbox
                            </a>
                        </div>
                        <div class="staff-presence-unread-list" id="crm-presence-unread-list"></div>
                    </div>

                    <div class="staff-presence-list" id="crm-presence-list">
                        <div class="staff-presence-loading">Loading online CRM users...</div>
                    </div>
                </div>
            </div>

            <div class="module-launcher me-2" id="crm-module-launcher">
                <button type="button" class="btn header-item module-launcher-toggle" id="crm-module-launcher-trigger"
                    aria-haspopup="true" aria-expanded="false" aria-label="Open modules">
                    <i class="bx bx-grid-alt font-size-18"></i>
                </button>

                <div class="crm-floating-panel module-launcher-menu" id="crm-module-launcher-menu" hidden>
                    <div class="module-launcher-title">Modules</div>
                    <div class="module-launcher-grid">
                        @foreach ($launcherModules as $module)
                            <a class="module-launcher-item" href="{{ $module['url'] }}">
                                <span class="module-launcher-icon">
                                    <i class="{{ $module['icon'] }}"></i>
                                </span>
                                <span class="module-launcher-label">{{ $module['label'] }}</span>
                                <span class="module-launcher-caption">{{ $module['caption'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="module-launcher crm-user-menu" id="crm-user-menu">
                <button type="button" class="btn header-item bg-soft-light border-start border-end rounded-0 crm-panel-trigger"
                    id="crm-user-dropdown" aria-haspopup="true" aria-expanded="false">
                    @if ($crmUser->avatar_url)
                        <img src="{{ $crmUser->avatar_url }}" alt="{{ $crmUser->name }}"
                            class="crm-user-avatar-circle crm-user-avatar-photo">
                    @else
                        <span class="crm-user-avatar-circle crm-user-avatar-placeholder">{{ $userInitials ?: 'CU' }}</span>
                    @endif
                    <span class="d-none d-xl-inline-block ms-2 fw-medium">{{ $crmUser->name }}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="crm-floating-panel crm-user-menu-panel" id="crm-user-dropdown-panel" hidden>
                    <div class="dropdown-header">{{ config('heritage_crm.roles.' . $crmUser->role, ucfirst($crmUser->role)) }}</div>
                    <a class="dropdown-item" href="{{ route('crm.dashboard') }}"><i class="bx bx-home font-size-16 align-middle me-2"></i> Dashboard</a>
                    <a class="dropdown-item" href="{{ route('crm.discussions.index') }}"><i class="bx bx-chat font-size-16 align-middle me-2"></i> Discussions</a>
                    <a class="dropdown-item" href="{{ route('website.home') }}"><i class="bx bx-link-external font-size-16 align-middle me-2"></i> Public site</a>
                    <div class="dropdown-divider"></div>
                    <form id="crm-logout-form" action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bx bx-log-out font-size-16 align-middle me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
