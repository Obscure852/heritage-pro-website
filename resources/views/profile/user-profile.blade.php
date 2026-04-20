@extends('layouts.master')
@section('title')
    My Profile
@endsection
@section('css')
    <style>
        :root {
            --prof-primary: #4e73df;
            --prof-secondary: #36b9cc;
            --prof-accent: #f59e0b;
            --prof-dark: #0f172a;
            --prof-text: #334155;
            --prof-muted: #94a3b8;
            --prof-border: #e2e8f0;
            --prof-surface: #f8fafc;
            --prof-white: #ffffff;
        }

        /* ── Animations ── */
        @keyframes profFadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes profSlideIn {
            from { opacity: 0; transform: translateX(-12px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes profPulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(78, 115, 223, 0.3); }
            50% { box-shadow: 0 0 0 8px rgba(78, 115, 223, 0); }
        }

        @keyframes profFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @keyframes profShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .prof-animate {
            animation: profFadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .prof-animate-delay-1 { animation-delay: 0.08s; }
        .prof-animate-delay-2 { animation-delay: 0.16s; }
        .prof-animate-delay-3 { animation-delay: 0.24s; }
        .prof-animate-delay-4 { animation-delay: 0.32s; }

        /* ── Profile Header ── */
        .profile-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 0;
            border-radius: 3px 3px 0 0;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -15%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.07) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-header::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -8%;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Noise texture overlay */
        .profile-header-noise {
            position: absolute;
            inset: 0;
            opacity: 0.03;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        .profile-header-inner {
            position: relative;
            z-index: 1;
            padding: 36px;
        }

        .profile-header-content {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        /* ── Avatar ── */
        .avatar-wrapper {
            position: relative;
            width: 108px;
            height: 108px;
            flex-shrink: 0;
        }

        .avatar-wrapper img {
            width: 108px;
            height: 108px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.3s;
        }

        .avatar-wrapper:hover img {
            transform: scale(1.04);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 108px;
            height: 108px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.25s;
            cursor: pointer;
        }

        .avatar-wrapper:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-overlay i {
            color: white;
            font-size: 18px;
        }

        .avatar-overlay span {
            color: rgba(255,255,255,0.85);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        /* ── Profile Info ── */
        .profile-info {
            flex: 1;
        }

        .profile-info h4 {
            margin: 0 0 2px 0;
            font-family: inherit;
            font-weight: 700;
            font-size: 26px;
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        .profile-info .position-text {
            opacity: 0.8;
            font-size: 14px;
            margin-bottom: 12px;
            font-weight: 400;
            font-family: inherit;
        }

        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 13px;
            font-family: inherit;
        }

        .profile-meta .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,255,255,0.12);
            padding: 5px 14px;
            border-radius: 24px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
            transition: background 0.2s, transform 0.2s;
            font-weight: 400;
        }

        .profile-meta .meta-pill:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }

        .profile-meta .meta-pill i {
            font-size: 11px;
            opacity: 0.7;
        }

        /* ── Stats ── */
        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-family: inherit;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
            font-family: inherit;
        }

        /* ── Container ── */
        .profile-container {
            background: var(--prof-white);
            border-radius: 0 0 3px 3px;
        }

        /* ── Tabs ── */
        .profile-tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            border-bottom: 1px solid var(--prof-border);
            padding: 0 8px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            background: var(--prof-surface);
        }

        .profile-tabs::-webkit-scrollbar {
            display: none;
        }

        .profile-tabs .tab-link {
            white-space: nowrap;
            padding: 14px 20px;
            font-size: 13px;
            font-weight: 500;
            color: var(--prof-muted);
            text-decoration: none;
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            background: none;
            font-family: inherit;
            position: relative;
        }

        .profile-tabs .tab-link:hover {
            color: var(--prof-primary);
            background: rgba(78, 115, 223, 0.04);
        }

        .profile-tabs .tab-link.active {
            color: var(--prof-primary);
            border-bottom-color: var(--prof-primary);
            background: var(--prof-white);
        }

        .profile-tabs .tab-link i {
            font-size: 13px;
            transition: color 0.2s;
        }

        .profile-tabs .tab-link:not(.active) i {
            color: var(--prof-muted);
        }

        .profile-tabs .tab-link.active i {
            color: var(--prof-primary);
        }

        .tab-body {
            padding: 28px 32px;
        }

        /* ── Form Styles ── */
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 0;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: var(--prof-dark);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--prof-border);
            font-family: inherit;
            letter-spacing: -0.2px;
        }

        .help-text {
            background: linear-gradient(135deg, #f0f4ff 0%, #f8fafc 100%);
            padding: 14px 18px;
            border-left: 4px solid var(--prof-primary);
            border-radius: 0 8px 8px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: var(--prof-dark);
            margin-bottom: 3px;
            font-size: 14px;
        }

        .help-text .help-content {
            color: var(--prof-muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--prof-text);
            font-size: 13px;
            letter-spacing: 0.1px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--prof-border);
            border-radius: 3px;
            font-size: 14px;
            font-family: inherit;
            color: var(--prof-dark);
            transition: all 0.2s cubic-bezier(0.22, 1, 0.36, 1);
            background-color: var(--prof-white);
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--prof-primary);
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.12);
        }

        .form-control[readonly] {
            background-color: var(--prof-surface);
            color: var(--prof-muted);
            border-style: dashed;
        }

        .form-control::placeholder {
            color: #cbd5e1;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* ── Input Group with Icon ── */
        .input-icon-group {
            position: relative;
        }

        .input-icon-group .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--prof-muted);
            font-size: 13px;
            pointer-events: none;
            z-index: 2;
        }

        .input-icon-group .form-control,
        .input-icon-group .form-select {
            padding-left: 40px;
        }

        /* ── Buttons ── */
        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.35);
            color: white;
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.2);
        }

        .btn-sm {
            padding: 7px 14px;
            font-size: 13px;
            border-radius: 3px;
        }

        .btn-outline-primary {
            background: transparent;
            border: 1.5px solid rgba(78, 115, 223, 0.3);
            color: var(--prof-primary);
        }

        .btn-outline-primary:hover {
            background: var(--prof-primary);
            border-color: var(--prof-primary);
            color: white;
            box-shadow: 0 2px 8px rgba(78, 115, 223, 0.25);
        }

        .btn-outline-danger {
            background: transparent;
            border: 1.5px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .btn-outline-danger:hover {
            background: #ef4444;
            border-color: #ef4444;
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid var(--prof-border);
            margin-top: 32px;
        }

        /* ── Tables ── */
        .prof-table-card {
            border: 1px solid var(--prof-border);
            border-radius: 10px;
            overflow: hidden;
        }

        .prof-table-card .table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .prof-table-card .table thead th {
            background: var(--prof-surface);
            font-weight: 600;
            color: var(--prof-text);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--prof-border);
            white-space: nowrap;
        }

        .prof-table-card .table td {
            padding: 12px 16px;
            vertical-align: middle;
            color: var(--prof-text);
            border-bottom: 1px solid #f1f5f9;
        }

        .prof-table-card .table tbody tr {
            transition: background 0.15s;
        }

        .prof-table-card .table tbody tr:hover {
            background: #f8faff;
        }

        .prof-table-card .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Action Buttons in Tables ── */
        .action-btn-group {
            display: flex;
            gap: 6px;
        }

        .action-btn-group .btn {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
        }

        /* ── Empty States ── */
        .empty-state {
            text-align: center;
            padding: 56px 24px;
        }

        .empty-state-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .empty-state-icon i {
            font-size: 28px;
            color: var(--prof-primary);
            opacity: 0.6;
        }

        .empty-state h6 {
            font-size: 15px;
            font-weight: 600;
            color: var(--prof-dark);
            margin-bottom: 4px;
        }

        .empty-state p {
            margin: 0;
            font-size: 13px;
            color: var(--prof-muted);
        }

        /* ── Badges ── */
        .prof-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            gap: 4px;
        }

        .prof-badge-info {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px solid #dbeafe;
        }

        .prof-badge-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #dcfce7;
        }

        .prof-badge-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .prof-badge-neutral {
            background: var(--prof-surface);
            color: var(--prof-muted);
            border: 1px solid var(--prof-border);
        }

        /* ── Modals ── */
        .modal-content {
            border: none;
            border-radius: 3px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06), 0 20px 48px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .modal-header {
            background: var(--prof-surface);
            border-bottom: 1px solid var(--prof-border);
            padding: 18px 24px;
        }

        .modal-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--prof-dark);
            font-family: inherit;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--prof-border);
            background: var(--prof-surface);
        }

        .required-star {
            color: #ef4444;
        }

        /* ── Search Box ── */
        .prof-search {
            position: relative;
        }

        .prof-search .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--prof-muted);
            font-size: 13px;
            pointer-events: none;
        }

        .prof-search .form-control {
            padding-left: 40px;
            border-radius: 3px;
            background: var(--prof-surface);
            border-color: var(--prof-border);
        }

        .prof-search .form-control:focus {
            background: var(--prof-white);
        }

        /* ── Tab Header Row ── */
        .tab-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 16px;
        }

        .tab-header-row .section-title {
            margin: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        /* ── IP Code ── */
        code {
            background: var(--prof-surface);
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 12px;
            color: var(--prof-text);
            border: 1px solid var(--prof-border);
        }


        /* ── Responsive ── */
        @media (max-width: 768px) {
            .profile-header-inner {
                padding: 20px;
            }

            .profile-header-content {
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }

            .profile-meta {
                justify-content: center;
            }

            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .profile-tabs {
                padding: 0 4px;
            }

            .profile-tabs .tab-link {
                padding: 12px 14px;
                font-size: 12px;
            }

            .tab-body {
                padding: 20px 16px;
            }

            .tab-header-row {
                flex-direction: column;
                align-items: stretch;
            }

            .action-btn-group {
                flex-wrap: nowrap;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            @if (session('message'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $errors->first() }}</strong>
                    @if ($errors->count() > 1)
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $i => $error)
                                @if ($i > 0)
                                    <li class="small">{{ $error }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Profile Header --}}
            <div class="profile-header prof-animate">
                <div class="profile-header-noise"></div>
                <div class="profile-header-inner">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <div class="profile-header-content">
                                <div class="avatar-wrapper" id="avatarWrapper">
                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/images/users/default-profile.png') }}"
                                        alt="{{ $user->full_name }}" id="avatarImage"
                                        onerror="this.src='{{ asset('assets/images/users/default-profile.png') }}'">
                                    <div class="avatar-overlay" id="avatarOverlay">
                                        <i class="fas fa-camera"></i>
                                        <span>Change</span>
                                    </div>
                                    <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/jpg"
                                        style="display:none;">
                                </div>
                                <div class="profile-info">
                                    <h4>{{ $user->full_name }}</h4>
                                    <div class="position-text">{{ $user->position ?? 'No position set' }}</div>
                                    <div class="profile-meta">
                                        @if($user->email)
                                            <span class="meta-pill"><i class="fas fa-envelope"></i> {{ $user->email }}</span>
                                        @endif
                                        @if($user->phone)
                                            <span class="meta-pill"><i class="fas fa-phone"></i> {{ $user->phone }}</span>
                                        @endif
                                        <span class="meta-pill"><i class="fas fa-building"></i> {{ $user->department ?? 'No department' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white">{{ $logsCount }}</h4>
                                        <small class="opacity-75">Logins</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white">{{ $userQualifications->count() }}</h4>
                                        <small class="opacity-75">Qualifications</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white">{{ $user->workHistory->count() }}</h4>
                                        <small class="opacity-75">Work History</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Profile Container --}}
            <div class="profile-container prof-animate prof-animate-delay-2">
                {{-- Tab Navigation --}}
                @php
                    $smsEnabled = $communicationChannels['sms_enabled'] ?? false;
                    $staffMessaging = $staffMessagingFeatures ?? ['direct_messages_enabled' => false];
                @endphp
                <div class="profile-tabs" id="profileTabs">
                    <button class="tab-link active" data-tab="profile">
                        <i class="fas fa-user"></i> Profile
                    </button>
                    <button class="tab-link" data-tab="qualifications">
                        <i class="fas fa-graduation-cap"></i> Qualifications
                    </button>
                    <button class="tab-link" data-tab="work-history">
                        <i class="fas fa-building"></i> Work History
                    </button>
                    <button class="tab-link" data-tab="login-history">
                        <i class="fas fa-clock-rotate-left"></i> Login History
                    </button>
                    @if ($staffMessaging['direct_messages_enabled'] ?? false)
                        <a class="tab-link" href="{{ route('staff.messages.inbox') }}">
                            <i class="fas fa-comments"></i> Direct Messages
                        </a>
                    @endif
                    <button class="tab-link" data-tab="email-logs">
                        <i class="fas fa-envelope-open-text"></i> Email Logs
                    </button>
                    @if ($smsEnabled)
                    <button class="tab-link" data-tab="sms-logs">
                        <i class="fas fa-comment-sms"></i> SMS Logs
                    </button>
                    @endif
                </div>

                {{-- Tab Content --}}
                <div class="tab-body">

                    {{-- Tab 1: Profile --}}
                    <div class="tab-pane-custom active" id="tab-profile">
                        <div class="help-text prof-animate">
                            <div class="help-title"><i class="fas fa-info-circle me-1" style="color:var(--prof-primary);"></i> Your Personal Information</div>
                            <div class="help-content">Update your personal details below. Position and department can only be changed by HR/Admin.</div>
                        </div>

                        <form method="POST" action="{{ route('users.update-profile-details', $user) }}" id="profileForm">
                            @csrf
                            @method('PUT')

                            <h6 class="section-title"><i class="fas fa-id-card me-2" style="color:var(--prof-primary); font-size:13px;"></i>Basic Information</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">First Name <span class="required-star">*</span></label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" class="form-control" name="firstname"
                                            value="{{ old('firstname', $user->firstname) }}" placeholder="e.g. John" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name <span class="required-star">*</span></label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" class="form-control" name="lastname"
                                            value="{{ old('lastname', $user->lastname) }}" placeholder="e.g. Mosweu" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth <span class="required-star">*</span></label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-calendar input-icon"></i>
                                        <input type="text" class="form-control" name="date_of_birth"
                                            value="{{ old('date_of_birth', $user->formatted_date_of_birth ?? '') }}"
                                            placeholder="dd/mm/yyyy" maxlength="10" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ID Number / Passport <span class="required-star">*</span></label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-id-badge input-icon"></i>
                                        <input type="text" class="form-control" name="id_number"
                                            value="{{ old('id_number', $user->id_number) }}" placeholder="e.g. 123456789" required>
                                    </div>
                                </div>
                            </div>

                            <h6 class="section-title"><i class="fas fa-address-book me-2" style="color:var(--prof-primary); font-size:13px;"></i>Contact Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="required-star">*</span></label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email', $user->email) }}" placeholder="e.g. john@example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-phone input-icon"></i>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone', $user->phone) }}" placeholder="(+267) 71234567">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nationality <span class="required-star">*</span></label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-globe input-icon"></i>
                                        <select class="form-select" name="nationality" required>
                                            <option value="">Select Nationality</option>
                                            @if (!empty($nationalities))
                                                @foreach ($nationalities as $nationality)
                                                    <option value="{{ $nationality->name }}"
                                                        {{ $user->nationality == $nationality->name ? 'selected' : '' }}>
                                                        {{ $nationality->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Position <i class="fas fa-lock text-muted" style="font-size:10px;"></i></label>
                                    <input type="text" class="form-control" value="{{ $user->position ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Department <i class="fas fa-lock text-muted" style="font-size:10px;"></i></label>
                                    <input type="text" class="form-control" value="{{ $user->department ?? '' }}" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="3" placeholder="e.g. P.O. Box 123, Gaborone">{{ old('address', $user->address) }}</textarea>
                                </div>
                            </div>

                            <h6 class="section-title"><i class="fas fa-briefcase me-2" style="color:var(--prof-primary); font-size:13px;"></i>Employment Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Personal Payroll Number</label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-hashtag input-icon"></i>
                                        <input type="text" class="form-control" name="personal_payroll_number"
                                            value="{{ old('personal_payroll_number', $user->personal_payroll_number ?? '') }}" placeholder="e.g. PRN-00123">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DPSM Personal File No</label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-folder-open input-icon"></i>
                                        <input type="text" class="form-control" name="dpsm_personal_file_number"
                                            value="{{ old('dpsm_personal_file_number', $user->dpsm_personal_file_number ?? '') }}" placeholder="e.g. DPSM-00456">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Appointment</label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-calendar-check input-icon"></i>
                                        <input type="date" class="form-control" name="date_of_appointment"
                                            value="{{ old('date_of_appointment', optional($user->date_of_appointment)->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Grade (Earning Band)</label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-layer-group input-icon"></i>
                                        <select class="form-select" name="earning_band">
                                            <option value="">Select Grade</option>
                                            @foreach ($earningBands as $earningBand)
                                                <option value="{{ $earningBand->name }}"
                                                    {{ old('earning_band', $user->earning_band ?? '') === $earningBand->name ? 'selected' : '' }}>
                                                    {{ $earningBand->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Tab 2: Qualifications --}}
                    <div class="tab-pane-custom" id="tab-qualifications" style="display:none;">
                        <div class="help-text">
                            <div class="help-title"><i class="fas fa-graduation-cap me-1" style="color:var(--prof-primary);"></i> Your Qualifications</div>
                            <div class="help-content">Add your academic qualifications and certifications. Contact HR if a qualification is not listed in the dropdown.</div>
                        </div>
                        <div class="tab-header-row">
                            <h5 class="section-title">My Qualifications</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQualificationModal">
                                <i class="fas fa-plus"></i> Add Qualification
                            </button>
                        </div>

                        <div class="prof-table-card">
                            <div class="table-responsive">
                                <table class="table align-middle" id="qualificationsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Qualification</th>
                                            <th>Level</th>
                                            <th>University / College</th>
                                            <th>Start Date</th>
                                            <th>Completion Date</th>
                                            <th style="width:100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($userQualifications as $index => $qual)
                                            <tr>
                                                <td><span style="color:var(--prof-muted); font-weight:500;">{{ $index + 1 }}</span></td>
                                                <td><strong style="color:var(--prof-dark);">{{ $qual->qualification->qualification ?? '' }}</strong></td>
                                                <td><span class="prof-badge prof-badge-info">{{ $qual->level ?? '' }}</span></td>
                                                <td>{{ $qual->college ?? '' }}</td>
                                                <td>{{ $qual->start_date ?? '' }}</td>
                                                <td>{{ $qual->completion_date ?? '' }}</td>
                                                <td>
                                                    <div class="action-btn-group">
                                                        <button class="btn btn-outline-primary btn-sm edit-qualification-btn"
                                                            data-id="{{ $qual->id }}"
                                                            data-qualification-id="{{ $qual->qualification_id }}"
                                                            data-level="{{ $qual->level ?? '' }}"
                                                            data-college="{{ $qual->college ?? '' }}"
                                                            data-start="{{ $qual->start_date ?? '' }}"
                                                            data-completion="{{ $qual->completion_date ?? '' }}"
                                                            title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('profile.qualifications.destroy', $qual->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Remove">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">
                                                            <i class="fas fa-graduation-cap"></i>
                                                        </div>
                                                        <h6>No Qualifications Yet</h6>
                                                        <p>Add your academic qualifications and certifications to complete your profile.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 3: Work History --}}
                    <div class="tab-pane-custom" id="tab-work-history" style="display:none;">
                        <div class="help-text">
                            <div class="help-title"><i class="fas fa-building me-1" style="color:var(--prof-primary);"></i> Your Work History</div>
                            <div class="help-content">Record your previous and current employment history. Leave the end date blank for your current position.</div>
                        </div>
                        <div class="tab-header-row">
                            <h5 class="section-title">My Work History</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWorkHistoryModal">
                                <i class="fas fa-plus"></i> Add Work History
                            </button>
                        </div>

                        <div class="prof-table-card">
                            <div class="table-responsive">
                                <table class="table align-middle" id="workHistoryTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Workplace</th>
                                            <th>Type of Work</th>
                                            <th>Role</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th style="width:100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($user->workHistory as $index => $work)
                                            <tr>
                                                <td><span style="color:var(--prof-muted); font-weight:500;">{{ $index + 1 }}</span></td>
                                                <td><strong style="color:var(--prof-dark);">{{ $work->workplace }}</strong></td>
                                                <td><span class="prof-badge prof-badge-neutral">{{ $work->type_of_work }}</span></td>
                                                <td>{{ $work->role }}</td>
                                                <td>{{ $work->start }}</td>
                                                <td>
                                                    @if($work->end)
                                                        {{ $work->end }}
                                                    @else
                                                        <span class="prof-badge prof-badge-success">Present</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="action-btn-group">
                                                        <button class="btn btn-outline-primary btn-sm edit-work-btn"
                                                            data-id="{{ $work->id }}"
                                                            data-workplace="{{ $work->workplace }}"
                                                            data-type="{{ $work->type_of_work }}"
                                                            data-role="{{ $work->role }}"
                                                            data-start="{{ $work->start }}"
                                                            data-end="{{ $work->end ?? '' }}"
                                                            title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('profile.work-history.destroy', $work->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Remove">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">
                                                            <i class="fas fa-building"></i>
                                                        </div>
                                                        <h6>No Work History Yet</h6>
                                                        <p>Record your previous and current employment to build your career profile.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 4: Login History --}}
                    <div class="tab-pane-custom" id="tab-login-history" style="display:none;">
                        <div class="help-text">
                            <div class="help-title"><i class="fas fa-shield-halved me-1" style="color:var(--prof-primary);"></i> Login History</div>
                            <div class="help-content">A log of your recent login activity including IP addresses and any profile changes made during each session.</div>
                        </div>
                        <div class="tab-header-row">
                            <h5 class="section-title">Login Activity</h5>
                            <div class="prof-search" style="min-width: 260px;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="form-control" placeholder="Search IP or changes..."
                                    id="logSearchInput">
                            </div>
                        </div>

                        <div class="prof-table-card">
                            <div class="table-responsive">
                                <table class="table align-middle" id="loginHistoryTable">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th style="width:150px;">IP Address</th>
                                            <th>Changes</th>
                                            <th style="width:180px;">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $stringifyChangeValue = function ($value): string {
                                                if (is_string($value) || is_numeric($value)) {
                                                    return (string) $value;
                                                }

                                                if (is_bool($value)) {
                                                    return $value ? 'true' : 'false';
                                                }

                                                if ($value === null) {
                                                    return 'null';
                                                }

                                                if (is_array($value)) {
                                                    $flattened = \Illuminate\Support\Arr::dot($value);

                                                    if ($flattened === []) {
                                                        return '[]';
                                                    }

                                                    return collect($flattened)->map(function ($item, $key) {
                                                        if (is_bool($item)) {
                                                            $item = $item ? 'true' : 'false';
                                                        } elseif ($item === null) {
                                                            $item = 'null';
                                                        } elseif (is_array($item)) {
                                                            $item = json_encode($item);
                                                        }

                                                        return is_string($key) ? "{$key}: {$item}" : (string) $item;
                                                    })->implode(', ');
                                                }

                                                return json_encode($value) ?: '[complex data]';
                                            };
                                        @endphp
                                        @if ($user->logs->count() > 0)
                                            @foreach ($user->logs as $index => $log)
                                                @php
                                                    $changesText = '';
                                                    if (isset($log->changes['data']) && is_array($log->changes['data'])) {
                                                        $changesText = implode(' ', array_keys($log->changes['data']));
                                                    }
                                                @endphp
                                                <tr class="log-row"
                                                    data-search="{{ strtolower(($log->ip_address ?? '') . ' ' . $changesText) }}">
                                                    <td><span style="color:var(--prof-muted); font-weight:500;">{{ $index + 1 }}</span></td>
                                                    <td><code>{{ $log->ip_address ?? 'N/A' }}</code></td>
                                                    <td>
                                                        @if (isset($log->changes['data']) && is_array($log->changes['data']))
                                                            @foreach ($log->changes['data'] as $field => $value)
                                                                <span class="prof-badge prof-badge-info me-1">{{ ucfirst(str_replace('_', ' ', $field)) }}: {{ Str::limit($stringifyChangeValue($value), 15) }}</span>
                                                            @endforeach
                                                        @else
                                                            <span style="color:var(--prof-muted); font-size:13px;">No changes</span>
                                                        @endif
                                                    </td>
                                                    <td style="color:var(--prof-muted); font-size:13px;">
                                                        <i class="fas fa-clock me-1" style="font-size:11px;"></i>
                                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4">
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">
                                                            <i class="fas fa-history"></i>
                                                        </div>
                                                        <h6>No Login History</h6>
                                                        <p>Your login activity will appear here once you start using the system.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 5: Email Logs --}}
                    <div class="tab-pane-custom" id="tab-email-logs" style="display:none;">
                        <div class="help-text">
                            <div class="help-title"><i class="fas fa-envelope me-1" style="color:var(--prof-primary);"></i> Email Logs</div>
                            <div class="help-content">Emails sent to you by the system, including notifications, reports, and announcements.</div>
                        </div>
                        <h5 class="section-title" style="margin-bottom:20px;">Received Emails</h5>

                        <div class="prof-table-card">
                            <div class="table-responsive">
                                <table class="table align-middle" id="emailLogsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Body</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($user->receivedEmails as $index => $email)
                                            <tr>
                                                <td><span style="color:var(--prof-muted); font-weight:500;">{{ $index + 1 }}</span></td>
                                                <td style="color:var(--prof-muted); font-size:13px; white-space:nowrap;">
                                                    <i class="fas fa-clock me-1" style="font-size:11px;"></i>
                                                    {{ $email->created_at ? $email->created_at->format('Y-m-d H:i') : '' }}
                                                </td>
                                                <td><strong style="color:var(--prof-dark);">{{ $email->subject ?? '' }}</strong></td>
                                                <td style="color:var(--prof-text); font-size:13px;">{{ Str::limit($email->body ?? '', 60) }}</td>
                                                <td>
                                                    @if ($email->status === 'sent')
                                                        <span class="prof-badge prof-badge-success"><i class="fas fa-check" style="font-size:10px;"></i> Sent</span>
                                                    @elseif ($email->status === 'failed')
                                                        <span class="prof-badge prof-badge-danger"><i class="fas fa-times" style="font-size:10px;"></i> Failed</span>
                                                    @else
                                                        <span class="prof-badge prof-badge-neutral">{{ ucfirst($email->status ?? 'Unknown') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">
                                                            <i class="fas fa-envelope-open-text"></i>
                                                        </div>
                                                        <h6>No Emails Yet</h6>
                                                        <p>System emails sent to you will appear here.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if ($smsEnabled)
                    {{-- Tab 6: SMS Logs --}}
                    <div class="tab-pane-custom" id="tab-sms-logs" style="display:none;">
                        <div class="help-text">
                            <div class="help-title"><i class="fas fa-comment-sms me-1" style="color:var(--prof-primary);"></i> SMS Logs</div>
                            <div class="help-content">SMS messages sent to your registered phone number by the school.</div>
                        </div>
                        <h5 class="section-title" style="margin-bottom:20px;">SMS Messages</h5>

                        <div class="prof-table-card">
                            <div class="table-responsive">
                                <table class="table align-middle" id="smsLogsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>From</th>
                                            <th>Body</th>
                                            <th>Segments</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($user->messages as $index => $message)
                                            <tr>
                                                <td><span style="color:var(--prof-muted); font-weight:500;">{{ $index + 1 }}</span></td>
                                                <td style="color:var(--prof-muted); font-size:13px; white-space:nowrap;">
                                                    <i class="fas fa-clock me-1" style="font-size:11px;"></i>
                                                    {{ $message->created_at ? $message->created_at->format('Y-m-d H:i') : '' }}
                                                </td>
                                                <td>{{ $school_data->school_name ?? '' }}</td>
                                                <td style="color:var(--prof-text); font-size:13px;">{{ Str::limit($message->body ?? '', 60) }}</td>
                                                <td><span class="prof-badge prof-badge-neutral">{{ $message->sms_count ?? '' }}</span></td>
                                                <td><span class="prof-badge prof-badge-success"><i class="fas fa-check" style="font-size:10px;"></i> Delivered</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6">
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">
                                                            <i class="fas fa-comment-sms"></i>
                                                        </div>
                                                        <h6>No SMS Messages</h6>
                                                        <p>SMS messages sent to you will appear here.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- Add Qualification Modal --}}
    <div class="modal fade" id="addQualificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-graduation-cap me-2" style="color:var(--prof-primary);"></i>Add Qualification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('profile.qualifications.store') }}" id="addQualForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Qualification <span class="required-star">*</span></label>
                            <select class="form-select" name="qualification_id" required>
                                <option value="">Select Qualification</option>
                                @foreach ($qualifications as $qual)
                                    <option value="{{ $qual->id }}">{{ $qual->qualification }} ({{ $qual->qualification_code }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Can't find your qualification? Please contact HR or an administrator to have it added to the system.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="level" placeholder="e.g. Diploma, Degree, Masters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">University / College <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="college" placeholder="e.g. University of Botswana" required>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="required-star">*</span></label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Completion Date <span class="required-star">*</span></label>
                                    <input type="date" class="form-control" name="completion_date" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" style="background:#f1f5f9; color:var(--prof-text);" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Save Qualification</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Qualification Modal --}}
    <div class="modal fade" id="editQualificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2" style="color:var(--prof-primary);"></i>Edit Qualification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editQualForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Qualification <span class="required-star">*</span></label>
                            <select class="form-select" name="qualification_id" id="editQualId" required>
                                <option value="">Select Qualification</option>
                                @foreach ($qualifications as $qual)
                                    <option value="{{ $qual->id }}">{{ $qual->qualification }} ({{ $qual->qualification_code }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Can't find your qualification? Please contact HR or an administrator to have it added to the system.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="level" id="editQualLevel" placeholder="e.g. Diploma, Degree, Masters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">University / College <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="college" id="editQualCollege" placeholder="e.g. University of Botswana" required>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="required-star">*</span></label>
                                    <input type="date" class="form-control" name="start_date" id="editQualStart" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Completion Date <span class="required-star">*</span></label>
                                    <input type="date" class="form-control" name="completion_date" id="editQualCompletion" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" style="background:#f1f5f9; color:var(--prof-text);" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Update Qualification</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add Work History Modal --}}
    <div class="modal fade" id="addWorkHistoryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-briefcase me-2" style="color:var(--prof-primary);"></i>Add Work History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('profile.work-history.store') }}" id="addWorkForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Workplace <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="workplace" placeholder="e.g. Heritage Junior Secondary School" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type of Work <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="type_of_work" placeholder="e.g. Teaching, Administration" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="role" placeholder="e.g. Senior Teacher, Head of Department" required>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="required-star">*</span></label>
                                    <input type="date" class="form-control" name="start" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" style="background:#f1f5f9; color:var(--prof-text);" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Save Work History</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Work History Modal --}}
    <div class="modal fade" id="editWorkHistoryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2" style="color:var(--prof-primary);"></i>Edit Work History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editWorkForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Workplace <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="workplace" id="editWorkPlace" placeholder="e.g. Heritage Junior Secondary School" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type of Work <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="type_of_work" id="editWorkType" placeholder="e.g. Teaching, Administration" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="role" id="editWorkRole" placeholder="e.g. Senior Teacher, Head of Department" required>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="required-star">*</span></label>
                                    <input type="date" class="form-control" name="start" id="editWorkStart" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end" id="editWorkEnd">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" style="background:#f1f5f9; color:var(--prof-text);" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Update Work History</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('components.crop-modal')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            // ========================================
            // Tab Switching with Persistence
            // ========================================
            const tabLinks = document.querySelectorAll('.tab-link[data-tab]');
            const tabPanes = document.querySelectorAll('.tab-pane-custom');
            const storageKey = 'profile_active_tab';

            function activateTab(tabName) {
                tabLinks.forEach(link => link.classList.remove('active'));
                tabPanes.forEach(pane => {
                    pane.style.display = 'none';
                    pane.classList.remove('active');
                });

                const activeLink = document.querySelector(`.tab-link[data-tab="${tabName}"]`);
                const activePane = document.getElementById(`tab-${tabName}`);

                if (activeLink && activePane) {
                    activeLink.classList.add('active');
                    activePane.style.display = 'block';
                    activePane.classList.add('active');
                    localStorage.setItem(storageKey, tabName);

                    // Scroll active tab into view
                    activeLink.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                }
            }

            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    activateTab(this.getAttribute('data-tab'));
                });
            });

            // Restore last active tab
            const savedTab = localStorage.getItem(storageKey);
            if (savedTab) {
                activateTab(savedTab);
            }

            // ========================================
            // Avatar AJAX Upload with Cropping
            // ========================================
            const avatarOverlay = document.getElementById('avatarOverlay');
            const avatarInput = document.getElementById('avatarInput');
            const avatarImage = document.getElementById('avatarImage');

            avatarOverlay.addEventListener('click', function() {
                avatarInput.click();
            });

            CropHelper.init(avatarInput, function(blob) {
                const formData = new FormData();
                formData.append('avatar', blob, 'avatar.jpg');
                formData.append('_token', '{{ csrf_token() }}');

                avatarImage.style.opacity = '0.5';

                $.ajax({
                    url: "{{ route('profile.update-avatar') }}",
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            avatarImage.src = response.avatar_url + '?t=' + Date.now();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Upload failed.', 'error');
                        }
                        avatarImage.style.opacity = '1';
                        CropHelper.hideModal();
                    },
                    error: function(xhr) {
                        avatarImage.style.opacity = '1';
                        let msg = 'An error occurred while uploading.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                        CropHelper.hideModal();
                    }
                });
            });

            // ========================================
            // Loading Button Animations
            // ========================================
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            // ========================================
            // Delete Confirmation
            // ========================================
            document.querySelectorAll('.delete-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const self = this;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, remove it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            self.submit();
                        }
                    });
                });
            });

            // ========================================
            // Edit Qualification Modal
            // ========================================
            document.querySelectorAll('.edit-qualification-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const qualId = this.getAttribute('data-qualification-id');
                    const level = this.getAttribute('data-level');
                    const college = this.getAttribute('data-college');
                    const start = this.getAttribute('data-start');
                    const completion = this.getAttribute('data-completion');

                    const baseUrl = "{{ route('profile.qualifications.update', ':id') }}";
                    document.getElementById('editQualForm').action = baseUrl.replace(':id', id);
                    document.getElementById('editQualId').value = qualId;
                    document.getElementById('editQualLevel').value = level;
                    document.getElementById('editQualCollege').value = college;
                    document.getElementById('editQualStart').value = start;
                    document.getElementById('editQualCompletion').value = completion;

                    new bootstrap.Modal(document.getElementById('editQualificationModal')).show();
                });
            });

            // ========================================
            // Edit Work History Modal
            // ========================================
            document.querySelectorAll('.edit-work-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const workplace = this.getAttribute('data-workplace');
                    const type = this.getAttribute('data-type');
                    const role = this.getAttribute('data-role');
                    const start = this.getAttribute('data-start');
                    const end = this.getAttribute('data-end');

                    const baseUrl = "{{ route('profile.work-history.update', ':id') }}";
                    document.getElementById('editWorkForm').action = baseUrl.replace(':id', id);
                    document.getElementById('editWorkPlace').value = workplace;
                    document.getElementById('editWorkType').value = type;
                    document.getElementById('editWorkRole').value = role;
                    document.getElementById('editWorkStart').value = start;
                    document.getElementById('editWorkEnd').value = end;

                    new bootstrap.Modal(document.getElementById('editWorkHistoryModal')).show();
                });
            });

            // ========================================
            // Login History Search Filter
            // ========================================
            const logSearch = document.getElementById('logSearchInput');
            if (logSearch) {
                logSearch.addEventListener('input', function() {
                    const term = this.value.toLowerCase();
                    document.querySelectorAll('.log-row').forEach(function(row) {
                        const searchData = row.getAttribute('data-search') || '';
                        row.style.display = searchData.includes(term) ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endsection
