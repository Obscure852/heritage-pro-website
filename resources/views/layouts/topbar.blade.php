<style>
    .app-search .position-relative {
        width: 320px;
    }

    .app-search .form-control {
        height: 38px;
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 3px;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid #e9ebef;
    }

    /* Extra right padding to accommodate shortcut hint and icon */
    .app-search .form-control {
        padding-right: 96px;
    }

    .app-search .form-control:focus {
        background-color: #fff;
        border-color: #556ee6;
        box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.15);
    }

    .app-search .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #74788d;
        font-size: 14px;
        pointer-events: none;
        z-index: 10;
    }

    .app-search .shortcut-hint {
        position: absolute;
        right: 36px;
        /* placed before the search icon */
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 6px;
        background: #f3f3f9;
        color: #000;
        border: 1px solid #e9ebef;
        border-radius: 4px;
        font-size: 11px;
        line-height: 1;
        user-select: none;
        pointer-events: none;
        z-index: 9;
    }

    .app-search .shortcut-hint kbd {
        font-family: inherit;
        background: #fff;
        border: 1px solid #e9ebef;
        border-bottom-color: #dfe3e8;
        border-radius: 3px;
        padding: 1px 4px;
        box-shadow: inset 0 -1px 0 #dfe3e8;
        font-size: 11px;
        color: #000;
        font-weight: 600;
    }

    .app-search .shortcut-hint .shortcut-plus {
        font-size: 10px;
        color: #000;
        line-height: 1;
        margin: 0 2px;
    }

    .app-search .search-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #fff;
        border-radius: 3px;
        box-shadow: 0 5px 15px rgba(30, 32, 37, 0.12);
        z-index: 1050;
        max-height: calc(100vh - 150px);
        overflow-y: auto;
        border: 1px solid #e9ebef;
    }

    .app-search .search-section {
        border-bottom: 1px solid #e9ebef;
    }

    .app-search .search-section:last-child {
        border-bottom: none;
    }

    .app-search .section-header {
        padding: 10px 16px;
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 13px;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .app-search .section-header i {
        font-size: 16px;
        color: #74788d;
    }

    .module-launcher {
        position: relative;
        display: flex;
        align-items: center;
        align-self: center;
        height: 38px;
        margin-right: 8px;
    }

    .module-launcher-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        min-width: 38px;
        padding: 0;
        border: 1px solid #e9ebef;
        border-radius: 3px;
        background: #fff;
        box-shadow: none;
        line-height: 1;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease,
            background-color 0.18s ease;
    }

    .module-launcher-toggle:hover,
    .module-launcher-toggle:focus,
    .module-launcher-toggle[aria-expanded="true"] {
        border-color: #cbd5e1;
        background: #f8fafc;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    }

    .module-launcher-menu {
        width: 420px;
        padding: 14px;
        border: 1px solid #eef1f7;
        border-radius: 3px;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.14);
    }

    .module-launcher-title {
        margin-bottom: 12px;
        padding: 0 4px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7b8190;
    }

    .module-launcher-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .module-launcher-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        min-height: 104px;
        border-radius: 3px;
        padding: 14px 10px 12px;
        font-weight: 500;
        color: #495057;
        white-space: normal;
        text-align: center;
        border: 1px solid #edf1f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease,
            background 0.18s ease;
    }

    .module-launcher-item:hover,
    .module-launcher-item:focus {
        background: linear-gradient(180deg, #ffffff 0%, #f2f6ff 100%);
        color: #212529;
        border-color: rgba(85, 110, 230, 0.2);
        box-shadow: 0 12px 24px rgba(85, 110, 230, 0.12);
        transform: translateY(-2px);
    }

    .module-launcher-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 3px;
        background: linear-gradient(135deg, rgba(85, 110, 230, 0.16) 0%, rgba(85, 110, 230, 0.08) 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    .module-launcher-icon i {
        font-size: 1.55rem;
        color: #556ee6;
    }

    .module-launcher-label {
        display: block;
        font-size: 0.84rem;
        font-weight: 600;
        line-height: 1.25;
        color: inherit;
    }

    @media (max-width: 575.98px) {
        .module-launcher-menu {
            width: min(420px, calc(100vw - 24px));
        }

        .module-launcher-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .app-search .result-item {
        padding: 10px 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .app-search .result-item:hover {
        background-color: #f3f3f9;
    }

    .app-search .result-name {
        font-weight: 500;
        color: #495057;
        font-size: 13px;
        margin-bottom: 3px;
    }

    .app-search .result-details {
        font-size: 12px;
        color: #74788d;
        line-height: 1.4;
    }

    .app-search .result-details span:not(:last-child)::after {
        content: "•";
        margin: 0 6px;
        color: #adb5bd;
    }

    .app-search .no-results,
    .app-search .loading-results {
        padding: 12px 16px;
        text-align: center;
        color: #74788d;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .app-search .loading-spinner {
        width: 18px;
        height: 18px;
        border: 2px solid #e9ebef;
        border-top-color: #556ee6;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (min-width: 992px) {
        .app-search .position-relative {
            width: 400px;
        }
    }

    @media (min-width: 1200px) {
        .app-search .position-relative {
            width: 480px;
        }
    }

    .staff-presence-launcher {
        position: relative;
        display: flex;
        align-items: center;
        margin-right: 8px;
        width: 200px;
    }

    .staff-presence-trigger {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        justify-content: space-between;
        width: 100%;
        height: 38px;
        padding: 6px 16px;
        border-radius: 3px;
        border: 1px solid #e9ebef;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .staff-presence-trigger:hover,
    .staff-presence-trigger[aria-expanded="true"] {
        border-color: #cbd5e1;
        background: #f8fafc;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    }

    .staff-presence-trigger-copy {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        flex: 1;
        overflow: hidden;
    }

    .staff-presence-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.14);
        flex-shrink: 0;
    }

    .staff-presence-trigger-label {
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .staff-presence-trigger-meta {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        margin-left: 12px;
        flex-shrink: 0;
    }

    .staff-presence-trigger-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
    }

    .staff-presence-trigger-badge {
        min-width: 20px;
        height: 20px;
        line-height: 20px;
        padding: 0 6px;
    }

    .staff-presence-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 100%;
        min-width: 320px;
        max-width: calc(100vw - 24px);
        background: #fff;
        border: 1px solid #e9ebef;
        border-radius: 3px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.14);
        z-index: 1100;
        overflow: hidden;
    }

    .staff-presence-panel-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .staff-presence-panel-header strong {
        display: block;
        color: #0f172a;
        font-size: 14px;
    }

    .staff-presence-panel-header span {
        color: #64748b;
        font-size: 12px;
    }

    .staff-presence-search {
        padding: 14px 18px 0;
    }

    .staff-presence-search .form-control {
        height: 38px;
        border-radius: 8px;
        border-color: #dbe4ee;
        font-size: 13px;
    }

    .staff-presence-panel-note {
        margin: 12px 18px 0;
        padding: 10px 12px;
        border-radius: 8px;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
    }

    .staff-presence-list {
        max-height: 360px;
        overflow-y: auto;
        padding: 14px 18px;
    }

    .staff-presence-empty,
    .staff-presence-loading {
        padding: 20px 12px;
        text-align: center;
        color: #64748b;
        font-size: 13px;
    }

    .staff-presence-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f8fafc;
    }

    .staff-presence-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .staff-presence-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .staff-presence-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .staff-presence-meta {
        min-width: 0;
        flex: 1;
    }

    .staff-presence-name {
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .staff-presence-role,
    .staff-presence-last-seen {
        color: #64748b;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .staff-presence-item .btn {
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .staff-presence-panel-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 18px 16px;
        border-top: 1px solid #f1f5f9;
        background: #fcfdff;
    }

    .staff-presence-panel-footer small {
        color: #64748b;
        font-size: 12px;
    }

    .staff-presence-unread-section {
        border-top: 1px solid #f1f5f9;
        background: #fff7f7;
        padding: 12px 18px 6px;
    }

    .staff-presence-section-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #b91c1c;
        margin-bottom: 8px;
    }

    .staff-presence-section-title i {
        font-size: 14px;
        line-height: 1;
    }

    .staff-presence-unread-list {
        display: flex;
        flex-direction: column;
        gap: 2px;
        max-height: 260px;
        overflow-y: auto;
    }

    .staff-presence-unread-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 8px;
        border-radius: 8px;
        background: transparent;
        border: 1px solid transparent;
        cursor: pointer;
        text-align: left;
        width: 100%;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .staff-presence-unread-item:hover,
    .staff-presence-unread-item:focus-visible {
        background: #fff;
        border-color: #fecaca;
        outline: none;
    }

    .staff-presence-unread-item + .staff-presence-unread-item {
        border-top: 1px dashed #fde2e2;
    }

    .staff-presence-unread-item:hover + .staff-presence-unread-item,
    .staff-presence-unread-item:focus-visible + .staff-presence-unread-item {
        border-top-color: transparent;
    }

    .staff-presence-unread-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(135deg, #f97316 0%, #dc2626 100%);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        position: relative;
    }

    .staff-presence-unread-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .staff-presence-unread-body {
        min-width: 0;
        flex: 1;
    }

    .staff-presence-unread-top {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: space-between;
        margin-bottom: 2px;
    }

    .staff-presence-unread-name {
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
        flex: 1;
    }

    .staff-presence-unread-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 18px;
        padding: 0 6px;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .staff-presence-unread-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: #64748b;
        margin-bottom: 2px;
    }

    .staff-presence-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #cbd5e1;
        display: inline-block;
    }

    .staff-presence-status-dot.is-online {
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.18);
    }

    .staff-presence-unread-preview {
        color: #475569;
        font-size: 12px;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }

    .staff-presence-unread-time {
        color: #94a3b8;
        font-size: 11px;
        margin-top: 2px;
    }

    @keyframes staff-presence-pulse {
        0%   { transform: scale(1);   box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.55); }
        40%  { transform: scale(1.28); box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
        70%  { transform: scale(1);   box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
        100% { transform: scale(1);   box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }

    .staff-presence-trigger-badge.is-pulsing {
        animation: staff-presence-pulse 1.1s ease-out 3;
    }

    @media (prefers-reduced-motion: reduce) {
        .staff-presence-trigger-badge.is-pulsing {
            animation: none;
        }
    }

    @media (max-width: 991.98px) {
        .staff-presence-launcher {
            width: auto;
        }

        .staff-presence-trigger-label {
            display: none;
        }

        .staff-presence-trigger {
            padding: 0 10px;
            gap: 8px;
        }

        .staff-presence-panel {
            width: 320px;
            min-width: 320px;
            right: -68px;
        }
    }

    @media (min-width: 992px) {
        .staff-presence-launcher {
            width: 220px;
        }
    }

    @media (min-width: 1200px) {
        .staff-presence-launcher {
            width: 240px;
        }
    }
</style>
@php
    $moduleVisibility = app(\App\Services\ModuleVisibilityService::class);
    $staffMessaging = $staffMessagingFeatures ?? [
        'direct_messages_enabled' => false,
        'presence_launcher_enabled' => false,
        'launcher_poll_seconds' => 45,
    ];
    $staffMessagingClientConfig = [
        'directMessagesEnabled' => (bool) ($staffMessaging['direct_messages_enabled'] ?? false),
        'presenceLauncherEnabled' => (bool) ($staffMessaging['presence_launcher_enabled'] ?? false),
        'pollSeconds' => (int) ($staffMessaging['launcher_poll_seconds'] ?? 45),
        'routes' => [
            'launcher' => route('staff.messages.launcher', [], false),
            'heartbeat' => route('staff.messages.heartbeat', [], false),
            'unreadCount' => route('staff.messages.unread-count', [], false),
            'startConversation' => route('staff.messages.start', [], false),
            'inbox' => route('staff.messages.inbox', [], false),
        ],
    ];
    $moduleLauncherItems = auth()->check() ? $moduleVisibility->getLauncherModulesForUser(auth()->user()) : [];
@endphp
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                        <span class="logo-txt">Heritage Pro</span>
                    </span>
                </a>

                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                        <span class="logo-txt">Heritage Pro</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            @php
                $canSearch =
                    auth()->user() &&
                    (Gate::allows('access-students') ||
                        Gate::allows('access-sponsors') ||
                        Gate::allows('access-admissions') ||
                        Gate::allows('access-hr'));
            @endphp

            @if ($canSearch)
                <form class="app-search d-none d-lg-block" onsubmit="return false;">
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-sm" placeholder="Global Search..."
                            id="global-search" autocomplete="off" aria-label="Global Search"
                            aria-keyshortcuts="Control+Space Alt+Space">
                        <span class="shortcut-hint" id="search-shortcut-hint" aria-hidden="true"></span>
                        <i class="fa fa-search search-icon"></i>
                        <div class="search-results" id="search-results" style="display: none;"></div>
                    </div>
                </form>
            @endif
        </div>

        <div class="d-flex">
            @if (($staffMessaging['direct_messages_enabled'] ?? false) && ($staffMessaging['presence_launcher_enabled'] ?? false))
                <div class="staff-presence-launcher" id="staff-presence-launcher">
                    <button type="button" class="btn header-item staff-presence-trigger" id="staff-presence-trigger"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="staff-presence-trigger-copy">
                            <span class="staff-presence-dot"></span>
                            <span class="staff-presence-trigger-label">Online Staff</span>
                        </span>
                        <span class="staff-presence-trigger-meta">
                            <span class="staff-presence-trigger-count" id="staff-presence-count">0</span>
                            <span class="badge rounded-pill bg-danger staff-presence-trigger-badge"
                                id="staff-presence-unread" style="display: none;"></span>
                            <i class="bx bx-chevron-down font-size-16 text-muted"></i>
                        </span>
                    </button>

                    <div class="staff-presence-panel" id="staff-presence-panel" hidden>
                        <div class="staff-presence-panel-header">
                            <div>
                                <strong>Online staff</strong>
                                <span>Updates quietly while you work.</span>
                            </div>
                            <span id="staff-presence-panel-count">0 online</span>
                        </div>

                        <div class="staff-presence-search">
                            <input type="search" class="form-control form-control-sm" id="staff-presence-search"
                                placeholder="Search by name, role, or department">
                        </div>

                        <div class="staff-presence-panel-note">
                            The launcher stays collapsed by default. Open it only when you need to message someone.
                        </div>

                        <div class="staff-presence-unread-section" id="staff-presence-unread-section" hidden>
                            <div class="staff-presence-section-title">
                                <i class="bx bx-envelope" aria-hidden="true"></i>
                                <span>Unread messages</span>
                            </div>
                            <div class="staff-presence-unread-list" id="staff-presence-unread-list" role="list"></div>
                        </div>

                        <div class="staff-presence-list" id="staff-presence-list">
                            <div class="staff-presence-loading">Loading online staff...</div>
                        </div>

                        <div class="staff-presence-panel-footer">
                            <small id="staff-presence-footer-note">No unread conversations</small>
                            <a href="{{ route('staff.messages.inbox') }}" class="btn btn-sm btn-light">Open Inbox</a>
                        </div>
                    </div>
                </div>
            @endif

            @if (!empty($moduleLauncherItems))
                <div class="dropdown d-inline-block text-muted module-launcher">
                    <button type="button" class="btn header-item module-launcher-toggle"
                        id="page-header-module-launcher" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false" aria-label="Open modules">
                        <i data-feather="grid" class="icon-lg" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end module-launcher-menu"
                        id="page-header-module-launcher-menu" aria-labelledby="page-header-module-launcher">
                        <div class="module-launcher-title">Modules</div>
                        <div class="module-launcher-grid">
                            @foreach ($moduleLauncherItems as $moduleLauncherItem)
                                <a class="dropdown-item module-launcher-item"
                                    id="page-header-module-launcher-item-{{ $moduleLauncherItem['key'] }}"
                                    href="{{ $moduleLauncherItem['url'] }}">
                                    <span class="module-launcher-icon">
                                        <i class="{{ $moduleLauncherItem['icon'] }}"></i>
                                    </span>
                                    <span class="module-launcher-label">{{ $moduleLauncherItem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end rounded-0"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <img class="rounded-circle header-profile-user"
                        src="@if (Auth::user()->avatar != '') {{ URL::asset('storage/' . Auth::user()->avatar) }}@else{{ asset('assets/images/users/default-profile.png') }} @endif"
                        alt="Header Avatar">
                    <span
                        class="d-none d-xl-inline-block ms-1 fw-medium">{{ auth()->user()->username ?? auth()->user()->full_name }}

                    </span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i><br>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('staff.profile') }}"><i
                            class="fa fa-user font-size-16 align-middle me-2"></i> My Profile</a>
                    @if ($staffMessaging['direct_messages_enabled'] ?? false)
                        <a class="dropdown-item" href="{{ route('staff.messages.inbox') }}"><i
                                class="fa fa-comments font-size-16 align-middle me-2"></i> Direct Messages</a>
                    @endif
                    @if ($moduleVisibility->isModuleVisible('schemes'))
                        @can('access-schemes')
                            <a class="dropdown-item" href="{{ route('schemes.index') }}"><i
                                    class="fa fa-chalkboard-teacher font-size-16 align-middle me-2"></i> Lesson Plans</a>
                        @endcan
                    @endif
                    <a class="dropdown-item" href="{{ route('documents.index') }}"><i
                            class="fa fa-folder-open font-size-16 align-middle me-2"></i> My Documents</a>
                    <a class="dropdown-item" href="{{ route('logs.tutorials') }}"><i
                            class="fa fa-video font-size-16 align-middle me-2"></i> Tutorials</a>

                    <a class="dropdown-item text-danger" href="javascript:void();"
                        onclick="logoutAndClearStorage(event)"><i
                            class="fa fa-sign-out-alt font-size-16 align-middle me-1"></i> <span
                            key="t-logout">Logout</span></a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    function logoutAndClearStorage(event) {
        event.preventDefault();
        clearLocalStorage();
        document.getElementById('logout-form').submit();
    }

    function clearLocalStorage() {
        localStorage.clear();
    }


    document.addEventListener('DOMContentLoaded', function() {
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    try {
                        func(...args);
                    } catch (error) {
                        console.error('Debounce error:', error);
                    }
                }, wait);
            };
        }

        const baseUrl = window.location.origin;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const staffMessagingConfig = @json($staffMessagingClientConfig);

        function initializeGlobalSearch() {
            const searchInput = document.getElementById('global-search');
            const searchResults = document.getElementById('search-results');
            const shortcutHint = document.getElementById('search-shortcut-hint');
            const isMac = /Mac|iPod|iPhone|iPad/.test(navigator.platform);
            const routes = {
                student: {
                    path: `${baseUrl}/students/show/`
                },
                admission: {
                    path: `${baseUrl}/admissions/view/`
                },
                sponsor: {
                    path: `${baseUrl}/sponsors/edit/`
                },
                user: {
                    path: `${baseUrl}/staff/view/`
                }
            };

            if (shortcutHint) {
                shortcutHint.innerHTML = isMac ?
                    '<kbd>Opt</kbd><span class="shortcut-plus">+</span><span>Space</span>' :
                    '<kbd>Ctrl</kbd><span class="shortcut-plus">+</span><span>Space</span>';
            }

            function isEditableElement(element) {
                if (!element) {
                    return false;
                }

                const tag = element.tagName;
                return element.isContentEditable || tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
            }

            function focusSearch(e) {
                if (!searchInput) {
                    return;
                }

                searchInput.focus();
                searchInput.select();

                if (e) {
                    e.preventDefault();
                }
            }

            document.addEventListener('keydown', function(e) {
                const active = document.activeElement;
                if (isEditableElement(active) && active !== searchInput) {
                    return;
                }

                const isSpace = e.code === 'Space' || e.key === ' ';
                const altSpace = isMac && e.altKey && isSpace;
                const ctrlSpace = !isMac && e.ctrlKey && isSpace;

                if (altSpace || ctrlSpace) {
                    focusSearch(e);
                }
            });

            if (!searchInput || !searchResults) {
                return;
            }

            function showLoading() {
                searchResults.innerHTML = `
                    <div class="loading-results">
                        <span class="loading-spinner"></span>
                        <span>Searching...</span>
                    </div>
                `;
                searchResults.style.display = 'block';
            }

            function hideResults() {
                searchResults.style.display = 'none';
            }

            function showResults() {
                searchResults.style.display = 'block';
            }

            function displayMessage(message, iconClass = 'bx-info-circle') {
                searchResults.innerHTML = `
                    <div class="no-results">
                        <i class='bx ${iconClass}'></i>
                        <span>${message}</span>
                    </div>
                `;
                showResults();
            }

            function handleError(error) {
                console.error('Search error:', error);
                displayMessage('Error loading results. Please try again.', 'bx-error-circle');
            }

            function navigateToEntity(type, id) {
                try {
                    if (!routes[type]) {
                        throw new Error(`Invalid entity type: ${type}`);
                    }

                    if (!id) {
                        throw new Error('Invalid ID provided');
                    }

                    window.location.replace(`${routes[type].path}${id}`);
                } catch (error) {
                    console.error('Navigation error:', error);
                    alert('An error occurred while navigating to the selected item.');
                }
            }

            async function performSearch(query) {
                try {
                    const searchUrl = `${baseUrl}/dashboard/search?query=${encodeURIComponent(query)}`;
                    const response = await fetch(searchUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    const resultCounts = data.count || {};
                    const totalResults = Object.values(resultCounts).reduce((total, value) => total + value,
                        0);

                    if (data.html) {
                        searchResults.innerHTML = data.html;
                    }

                    if (totalResults === 0) {
                        displayMessage('No matching results found.');
                    } else {
                        showResults();
                    }
                } catch (error) {
                    handleError(error);
                    console.error('Full error details:', error);
                }
            }

            const debouncedSearch = debounce((query) => {
                if (query.length >= 2) {
                    showLoading();
                    performSearch(query);
                } else {
                    hideResults();
                }
            }, 300);

            searchInput.addEventListener('input', function() {
                debouncedSearch(this.value.trim());
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideResults();
                    this.blur();
                }
            });

            document.addEventListener('click', function(e) {
                const resultItem = e.target.closest('.result-item');
                if (resultItem) {
                    e.preventDefault();

                    if (resultItem.dataset.type && resultItem.dataset.id) {
                        navigateToEntity(resultItem.dataset.type, resultItem.dataset.id);
                    }
                }

                if (!e.target.closest('.app-search')) {
                    hideResults();
                }
            });

            searchInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 2) {
                    showResults();
                }
            });

            window.addEventListener('popstate', hideResults);
        }

        function initializeStaffMessaging() {
            if (!staffMessagingConfig.directMessagesEnabled) {
                return;
            }

            const sidebarBadge = document.getElementById('staff-messages-sidebar-badge');
            const launcher = document.getElementById('staff-presence-launcher');
            const trigger = document.getElementById('staff-presence-trigger');
            const panel = document.getElementById('staff-presence-panel');
            const list = document.getElementById('staff-presence-list');
            const search = document.getElementById('staff-presence-search');
            const count = document.getElementById('staff-presence-count');
            const panelCount = document.getElementById('staff-presence-panel-count');
            const unreadBadge = document.getElementById('staff-presence-unread');
            const footerNote = document.getElementById('staff-presence-footer-note');
            const unreadSection = document.getElementById('staff-presence-unread-section');
            const unreadList = document.getElementById('staff-presence-unread-list');
            const originalDocTitle = document.title;
            const AUTO_CLOSE_MS = 10000;
            let launcherDisabled = false;
            let refreshTimer = null;
            let isPanelOpen = false;
            let currentPollSeconds = Math.max(parseInt(staffMessagingConfig.pollSeconds, 10) || 45, 15);
            let scheduledPollSeconds = currentPollSeconds;
            let lastKnownUnreadCount = null;
            let titleFlashTimer = null;
            let autoCloseTimer = null;
            let autoOpenedBySystem = false;
            let userDismissedAutoOpen = false;
            let isHoveringPanel = false;
            let isDocumentFocused = !document.hidden && (document.hasFocus ? document.hasFocus() : true);
            let faviconOriginalHref = null;
            let faviconBaseImage = null;
            let faviconUpdatesEnabled = true;

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function initialsFor(name) {
                const parts = String(name || 'SU')
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean)
                    .slice(0, 2);

                return parts.map((part) => part.charAt(0).toUpperCase()).join('') || 'SU';
            }

            function setBadgeCount(element, value) {
                if (!element) {
                    return;
                }

                const countValue = Number(value) || 0;
                if (countValue > 0) {
                    element.textContent = countValue > 99 ? '99+' : String(countValue);
                    element.style.display = 'inline-flex';
                } else {
                    element.textContent = '';
                    element.style.display = 'none';
                }
            }

            function updateUnreadState(unreadCount) {
                setBadgeCount(unreadBadge, unreadCount);
                setBadgeCount(sidebarBadge, unreadCount);

                if (footerNote) {
                    footerNote.textContent = unreadCount > 0 ?
                        `${unreadCount} unread conversation${unreadCount === 1 ? '' : 's'}` :
                        'No unread conversations';
                }
            }

            function updateOnlineCount(onlineCount) {
                const value = Number(onlineCount) || 0;
                if (count) {
                    count.textContent = value;
                }

                if (panelCount) {
                    panelCount.textContent = `${value} online`;
                }
            }

            function pulseBadge() {
                if (!unreadBadge) {
                    return;
                }

                unreadBadge.classList.remove('is-pulsing');
                // Force reflow so the animation restarts if class is re-added quickly.
                void unreadBadge.offsetWidth;
                unreadBadge.classList.add('is-pulsing');
            }

            function startTitleFlash(unreadCount) {
                // Only flash the tab title when the user isn't actively looking.
                if (isDocumentFocused) {
                    return;
                }

                stopTitleFlash(true);

                const normalized = Math.max(0, Number(unreadCount) || 0);
                if (normalized < 1) {
                    document.title = originalDocTitle;
                    return;
                }

                const prefix = `(${normalized > 99 ? '99+' : normalized}) New message${normalized === 1 ? '' : 's'}`;
                let showBadge = true;
                document.title = `${prefix} · ${originalDocTitle}`;

                titleFlashTimer = window.setInterval(function() {
                    showBadge = !showBadge;
                    document.title = showBadge ? `${prefix} · ${originalDocTitle}` : originalDocTitle;
                }, 1200);
            }

            function stopTitleFlash(keepCurrentTitle = false) {
                if (titleFlashTimer) {
                    window.clearInterval(titleFlashTimer);
                    titleFlashTimer = null;
                }

                if (!keepCurrentTitle) {
                    document.title = originalDocTitle;
                }
            }

            function updateFavicon(unreadCount) {
                if (!faviconUpdatesEnabled) {
                    return;
                }

                try {
                    const link = document.querySelector('link[rel="icon"]') ||
                        document.querySelector('link[rel="shortcut icon"]');
                    if (!link) {
                        return;
                    }

                    if (faviconOriginalHref === null) {
                        faviconOriginalHref = link.getAttribute('href');
                    }

                    const normalized = Math.max(0, Number(unreadCount) || 0);
                    if (normalized < 1) {
                        if (faviconOriginalHref) {
                            link.setAttribute('href', faviconOriginalHref);
                        }
                        return;
                    }

                    const applyOverlay = function(baseImg) {
                        try {
                            const canvas = document.createElement('canvas');
                            canvas.width = 32;
                            canvas.height = 32;
                            const ctx = canvas.getContext('2d');
                            if (!ctx) {
                                return;
                            }

                            if (baseImg) {
                                try {
                                    ctx.drawImage(baseImg, 0, 0, 32, 32);
                                } catch (drawError) {
                                    // Cross-origin taint — draw a neutral background.
                                    ctx.fillStyle = '#1f2937';
                                    ctx.fillRect(0, 0, 32, 32);
                                }
                            } else {
                                ctx.fillStyle = '#1f2937';
                                ctx.fillRect(0, 0, 32, 32);
                            }

                            ctx.beginPath();
                            ctx.arc(23, 9, 8, 0, Math.PI * 2);
                            ctx.fillStyle = '#dc2626';
                            ctx.fill();
                            ctx.lineWidth = 2;
                            ctx.strokeStyle = '#ffffff';
                            ctx.stroke();

                            const dataUrl = canvas.toDataURL('image/png');
                            link.setAttribute('href', dataUrl);
                        } catch (exportError) {
                            // toDataURL can throw on tainted canvases — disable further attempts.
                            faviconUpdatesEnabled = false;
                        }
                    };

                    if (faviconBaseImage && faviconBaseImage.complete && faviconBaseImage.naturalWidth > 0) {
                        applyOverlay(faviconBaseImage);
                        return;
                    }

                    if (!faviconOriginalHref) {
                        applyOverlay(null);
                        return;
                    }

                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = function() {
                        faviconBaseImage = img;
                        applyOverlay(img);
                    };
                    img.onerror = function() {
                        applyOverlay(null);
                    };
                    img.src = faviconOriginalHref;
                } catch (error) {
                    // Favicon is cosmetic — never let it break polling.
                    faviconUpdatesEnabled = false;
                }
            }

            function renderUnreadSenders(senders) {
                if (!unreadSection || !unreadList) {
                    return;
                }

                if (!Array.isArray(senders) || senders.length === 0) {
                    unreadSection.hidden = true;
                    unreadList.innerHTML = '';
                    return;
                }

                unreadList.innerHTML = senders.map(function(sender) {
                    const name = String(sender?.name || 'Staff user');
                    const senderUnreadCount = Math.max(1, Number(sender?.unread_count) || 1);
                    const countLabel = senderUnreadCount > 99 ? '99+' : String(senderUnreadCount);
                    const preview = String(sender?.latest_preview || 'New message');
                    const timeLabel = sender?.latest_message_label ? String(sender.latest_message_label) : '';
                    const online = Boolean(sender?.is_online);
                    const statusText = online ? 'online' : 'offline';
                    const statusClass = online ? 'is-online' : '';
                    const conversationUrl = sender?.conversation_url ? String(sender.conversation_url) : '';
                    const avatar = sender?.avatar_url ?
                        `<img src="${escapeHtml(sender.avatar_url)}" alt="${escapeHtml(name)}">` :
                        escapeHtml(initialsFor(name));

                    return `
                        <button type="button" class="staff-presence-unread-item" data-staff-unread-url="${escapeHtml(conversationUrl)}">
                            <div class="staff-presence-unread-avatar">${avatar}</div>
                            <div class="staff-presence-unread-body">
                                <div class="staff-presence-unread-top">
                                    <span class="staff-presence-unread-name">${escapeHtml(name)}</span>
                                    <span class="staff-presence-unread-count">${escapeHtml(countLabel)}</span>
                                </div>
                                <div class="staff-presence-unread-status">
                                    <span class="staff-presence-status-dot ${statusClass}"></span>
                                    <span>${escapeHtml(statusText)}</span>
                                    ${timeLabel ? `<span>· ${escapeHtml(timeLabel)}</span>` : ''}
                                </div>
                                <div class="staff-presence-unread-preview">${escapeHtml(preview)}</div>
                            </div>
                        </button>
                    `;
                }).join('');

                unreadSection.hidden = false;
            }

            function cancelAutoClose() {
                if (autoCloseTimer) {
                    window.clearTimeout(autoCloseTimer);
                    autoCloseTimer = null;
                }
            }

            function scheduleAutoClose() {
                cancelAutoClose();

                // Hover pauses; hidden document pauses (user hasn't seen it yet).
                if (isHoveringPanel || !isDocumentFocused) {
                    return;
                }

                autoCloseTimer = window.setTimeout(function() {
                    autoCloseTimer = null;
                    if (!autoOpenedBySystem || isHoveringPanel) {
                        return;
                    }
                    closePanel();
                }, AUTO_CLOSE_MS);
            }

            function triggerAttention() {
                pulseBadge();

                if (!panel || !trigger || launcherDisabled) {
                    return;
                }

                // User manually opened the panel — don't hijack it.
                if (isPanelOpen && !autoOpenedBySystem) {
                    return;
                }

                // User already dismissed this pop — wait for a fresh increase.
                if (userDismissedAutoOpen) {
                    return;
                }

                if (!isPanelOpen) {
                    autoOpenedBySystem = true;
                    openPanel();
                }
                scheduleAutoClose();
            }

            function handleUnreadChange(newCount, senders) {
                const normalizedCount = Math.max(0, Number(newCount) || 0);
                const previousCount = lastKnownUnreadCount;

                updateUnreadState(normalizedCount);
                renderUnreadSenders(Array.isArray(senders) ? senders : []);

                if (normalizedCount === 0) {
                    stopTitleFlash();
                    updateFavicon(0);
                    userDismissedAutoOpen = false;
                    cancelAutoClose();
                    if (autoOpenedBySystem && isPanelOpen) {
                        closePanel();
                    }
                    lastKnownUnreadCount = 0;
                    return;
                }

                updateFavicon(normalizedCount);
                if (!isDocumentFocused) {
                    startTitleFlash(normalizedCount);
                }

                const isFirstPoll = previousCount === null;
                const isIncrease = previousCount !== null && normalizedCount > previousCount;

                if (isFirstPoll || isIncrease) {
                    if (isIncrease) {
                        // Reset dismissal so a fresh arrival can pop again.
                        userDismissedAutoOpen = false;
                    }
                    triggerAttention();
                }

                lastKnownUnreadCount = normalizedCount;
            }

            function closePanel() {
                if (!panel || !trigger) {
                    return;
                }

                panel.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
                isPanelOpen = false;
                autoOpenedBySystem = false;
                cancelAutoClose();
            }

            function openPanel() {
                if (!panel || !trigger || launcherDisabled) {
                    return;
                }

                panel.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                isPanelOpen = true;
            }

            function renderUsers(users, activeQuery = '') {
                if (!list) {
                    return;
                }

                if (!Array.isArray(users) || users.length === 0) {
                    const message = activeQuery ? 'No matching online staff found.' :
                        'No staff members are currently online.';
                    list.innerHTML = `<div class="staff-presence-empty">${escapeHtml(message)}</div>`;
                    return;
                }

                list.innerHTML = users.map((user) => {
                    const roleParts = [user.position, user.department].filter(Boolean);
                    const roleText = roleParts.length ? roleParts.join(' | ') : 'Staff user';
                    const lastSeenText = user.last_seen_label ? `Seen ${user.last_seen_label}` :
                        'Active now';
                    const avatar = user.avatar_url ?
                        `<img src="${escapeHtml(user.avatar_url)}" alt="${escapeHtml(user.name)}">` :
                        escapeHtml(initialsFor(user.name));

                    return `
                        <div class="staff-presence-item">
                            <div class="staff-presence-avatar">${avatar}</div>
                            <div class="staff-presence-meta">
                                <div class="staff-presence-name">${escapeHtml(user.name)}</div>
                                <div class="staff-presence-role">${escapeHtml(roleText)}</div>
                                <div class="staff-presence-last-seen">${escapeHtml(lastSeenText)}</div>
                            </div>
                            <button type="button" class="btn btn-light btn-sm" data-staff-message-start="${escapeHtml(user.id)}">
                                Message
                            </button>
                        </div>
                    `;
                }).join('');
            }

            function createRequestError(message, details = {}) {
                const error = new Error(message);
                Object.assign(error, details);

                return error;
            }

            function shouldDisableLauncher(error) {
                const message = String(error?.data?.message || error?.data?.error?.message || '');

                return Number(error?.status) === 403 && message.toLowerCase().includes('disabled');
            }

            function logMessagingError(context, error) {
                console.error(context, {
                    message: error?.message || 'Unknown error',
                    status: error?.status ?? null,
                    redirected: Boolean(error?.redirected),
                    url: error?.url || null,
                    response: error?.data ?? error?.bodyPreview ?? null,
                });
            }

            async function parseJsonResponse(response, url) {
                const contentType = String(response.headers.get('content-type') || '').toLowerCase();

                if (!contentType.includes('application/json')) {
                    const bodyPreview = (await response.text()).slice(0, 200);

                    throw createRequestError(
                        `Expected JSON response from ${url} but received ${contentType || 'an unknown content type'}.`, {
                            status: response.status,
                            redirected: response.redirected,
                            url: response.url || url,
                            bodyPreview,
                        }
                    );
                }

                return response.json();
            }

            async function fetchJson(url, options = {}) {
                const {
                    headers: optionHeaders = {},
                    ...requestOptions
                } = options;

                const response = await fetch(url, {
                    credentials: 'same-origin',
                    ...requestOptions,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        ...optionHeaders
                    }
                });

                if (response.redirected) {
                    throw createRequestError(
                        `Request to ${url} was redirected to ${response.url || url}.`, {
                            status: response.status,
                            redirected: true,
                            url: response.url || url,
                        }
                    );
                }

                const data = await parseJsonResponse(response, url);

                if (!response.ok) {
                    throw createRequestError(
                        data?.message || data?.error?.message ||
                        `Request to ${url} failed with status ${response.status}.`, {
                            status: response.status,
                            redirected: false,
                            url: response.url || url,
                            data,
                        }
                    );
                }

                return data;
            }

            function stopLauncherPolling() {
                launcherDisabled = true;
                if (refreshTimer) {
                    window.clearInterval(refreshTimer);
                    refreshTimer = null;
                }

                closePanel();

                if (launcher) {
                    launcher.style.display = 'none';
                }

                handleUnreadChange(0, []);
            }

            async function syncUnreadCount() {
                try {
                    const data = await fetchJson(staffMessagingConfig.routes.unreadCount);
                    if (shouldDisableLauncher(data)) {
                        stopLauncherPolling();
                        return;
                    }

                    handleUnreadChange(data?.count || 0, []);
                } catch (error) {
                    if (shouldDisableLauncher(error)) {
                        stopLauncherPolling();
                        return;
                    }

                    logMessagingError('Unread count refresh failed.', error);
                }
            }

            async function sendPresenceHeartbeat() {
                if (document.hidden || launcherDisabled) {
                    return;
                }

                try {
                    const response = await fetchJson(staffMessagingConfig.routes.heartbeat, {
                        method: 'POST',
                        body: JSON.stringify({
                            last_path: window.location.pathname
                        }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    if (shouldDisableLauncher(response)) {
                        stopLauncherPolling();
                    }
                } catch (error) {
                    if (shouldDisableLauncher(error)) {
                        stopLauncherPolling();
                        return;
                    }

                    logMessagingError('Presence heartbeat failed.', error);
                }
            }

            async function syncLauncher(query = '') {
                if (!staffMessagingConfig.presenceLauncherEnabled || launcherDisabled) {
                    await syncUnreadCount();
                    return;
                }

                try {
                    const url = new URL(staffMessagingConfig.routes.launcher, window.location.origin);
                    if (query) {
                        url.searchParams.set('query', query);
                    }

                    const data = await fetchJson(url.toString());
                    if (shouldDisableLauncher(data)) {
                        stopLauncherPolling();
                        return;
                    }

                    currentPollSeconds = Math.max(parseInt(data.poll_seconds, 10) || currentPollSeconds,
                        15);
                    if (currentPollSeconds !== scheduledPollSeconds) {
                        scheduleRefreshLoop();
                    }

                    updateOnlineCount(data.online_count || 0);
                    handleUnreadChange(data.unread_count || 0, data.unread_senders || []);
                    renderUsers(data.users || [], query);
                } catch (error) {
                    if (shouldDisableLauncher(error)) {
                        stopLauncherPolling();
                        return;
                    }

                    logMessagingError('Presence launcher refresh failed.', error);
                }
            }

            async function refreshMessagingState(query = '') {
                await sendPresenceHeartbeat();

                if (staffMessagingConfig.presenceLauncherEnabled) {
                    await syncLauncher(query);
                } else {
                    await syncUnreadCount();
                }
            }

            function scheduleRefreshLoop() {
                if (refreshTimer) {
                    window.clearInterval(refreshTimer);
                }

                scheduledPollSeconds = currentPollSeconds;
                refreshTimer = window.setInterval(function() {
                    const query = isPanelOpen && search ? search.value.trim() : '';
                    refreshMessagingState(query);
                }, currentPollSeconds * 1000);
            }

            window.addEventListener('staff-messaging:refresh', function() {
                if (launcherDisabled) {
                    return;
                }

                const query = isPanelOpen && search ? search.value.trim() : '';
                refreshMessagingState(query);
            });

            if (trigger && panel) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (isPanelOpen) {
                        if (autoOpenedBySystem) {
                            // User is actively dismissing an auto-opened pop.
                            userDismissedAutoOpen = true;
                        }
                        closePanel();
                        return;
                    }

                    // Manual open — the user "owns" this state, clear auto flags.
                    userDismissedAutoOpen = false;
                    autoOpenedBySystem = false;
                    cancelAutoClose();
                    openPanel();
                    const query = search ? search.value.trim() : '';
                    syncLauncher(query);
                });

                document.addEventListener('click', function(e) {
                    if (!launcher || !launcher.contains(e.target)) {
                        if (isPanelOpen && autoOpenedBySystem) {
                            userDismissedAutoOpen = true;
                        }
                        closePanel();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        if (isPanelOpen && autoOpenedBySystem) {
                            userDismissedAutoOpen = true;
                        }
                        closePanel();
                    }
                });

                // Hover pauses the auto-close timer so the user has time to read.
                panel.addEventListener('mouseenter', function() {
                    isHoveringPanel = true;
                    cancelAutoClose();
                });

                panel.addEventListener('mouseleave', function() {
                    isHoveringPanel = false;
                    if (autoOpenedBySystem && isPanelOpen) {
                        scheduleAutoClose();
                    }
                });

                // Any click inside the panel = user is interacting; stop auto-closing.
                panel.addEventListener('click', function() {
                    cancelAutoClose();
                    autoOpenedBySystem = false;
                });
            }

            if (unreadList) {
                unreadList.addEventListener('click', function(e) {
                    const button = e.target.closest('[data-staff-unread-url]');
                    if (!button) {
                        return;
                    }

                    const url = button.getAttribute('data-staff-unread-url');
                    if (!url) {
                        return;
                    }

                    cancelAutoClose();
                    window.location.assign(url);
                });
            }

            if (search) {
                const debouncedPresenceSearch = debounce((query) => {
                    if (!launcherDisabled) {
                        syncLauncher(query);
                    }
                }, 250);

                search.addEventListener('input', function() {
                    debouncedPresenceSearch(this.value.trim());
                });
            }

            if (list) {
                list.addEventListener('click', async function(e) {
                    const button = e.target.closest('[data-staff-message-start]');
                    if (!button) {
                        return;
                    }

                    const recipientId = button.getAttribute('data-staff-message-start');
                    if (!recipientId) {
                        return;
                    }

                    button.disabled = true;
                    button.textContent = 'Opening...';

                    try {
                        const data = await fetchJson(staffMessagingConfig.routes
                            .startConversation, {
                                method: 'POST',
                                body: JSON.stringify({
                                    recipient_id: recipientId
                                }),
                                headers: {
                                    'Content-Type': 'application/json'
                                }
                            });

                        if (shouldDisableLauncher(data)) {
                            stopLauncherPolling();
                            return;
                        }

                        if (data?.redirect_url) {
                            window.location.assign(data.redirect_url);
                        }
                    } catch (error) {
                        if (shouldDisableLauncher(error)) {
                            stopLauncherPolling();
                            return;
                        }

                        logMessagingError('Failed to open staff conversation.', error);
                        button.disabled = false;
                        button.textContent = 'Message';
                    }
                });
            }

            window.addEventListener('focus', function() {
                isDocumentFocused = true;
                stopTitleFlash();
                // If a system auto-open panel was waiting in the background, start its countdown now.
                if (autoOpenedBySystem && isPanelOpen) {
                    scheduleAutoClose();
                }
                refreshMessagingState(search ? search.value.trim() : '');
            });

            window.addEventListener('blur', function() {
                isDocumentFocused = false;
                // Don't auto-close the panel while the user is away — they haven't seen it.
                cancelAutoClose();
            });

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    isDocumentFocused = false;
                    cancelAutoClose();
                } else {
                    isDocumentFocused = true;
                    stopTitleFlash();
                    if (autoOpenedBySystem && isPanelOpen) {
                        scheduleAutoClose();
                    }
                    refreshMessagingState(search ? search.value.trim() : '');
                }
            });

            refreshMessagingState();
            scheduleRefreshLoop();
        }

        initializeGlobalSearch();
        initializeStaffMessaging();
    });
</script>
