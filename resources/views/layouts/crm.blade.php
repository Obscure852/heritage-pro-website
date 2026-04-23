<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Heritage Pro CRM') | Heritage Pro CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heritage Pro CRM Workspace">
    <meta name="author" content="Platinum Developers">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.crm-head-css')
    <style>
        .crm-user-profile-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 28px;
            align-items: start;
        }

        .crm-user-profile-main {
            min-width: 0;
        }

        .crm-user-profile-side {
            min-width: 0;
        }

        .crm-user-avatar-panel {
            display: grid;
            gap: 18px;
            margin-top: 25px;
            padding: 24px;
            border: 1px solid #d8e4f2;
            border-radius: 3px;
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.16), transparent 38%),
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .crm-user-avatar-panel-copy h3 {
            margin: 4px 0 8px;
            font-size: 19px;
            color: #0f172a;
        }

        .crm-user-avatar-picker {
            display: grid;
            gap: 18px;
            justify-items: center;
        }

        .crm-user-avatar-shell,
        .crm-user-summary-avatar {
            flex: 0 0 auto;
        }

        .crm-user-avatar-shell {
            width: 128px;
            height: 128px;
            border-radius: 3px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.14), rgba(37, 99, 235, 0.22));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .crm-user-avatar-shell-xl {
            width: min(100%, 220px);
            height: auto;
            aspect-ratio: 1 / 1;
            border-radius: 3px;
            box-shadow: 0 18px 38px rgba(37, 99, 235, 0.16);
        }

        .crm-user-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }

        .crm-user-avatar-shell .crm-initial-avatar {
            width: 100%;
            height: 100%;
            border-radius: inherit;
            display: inline-flex;
            flex-direction: column;
            gap: 14px;
        }

        .crm-avatar-upload-icon {
            position: relative;
            width: 52px;
            height: 52px;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.22);
            font-size: 20px;
        }

        .crm-avatar-upload-plus {
            position: absolute;
            right: -6px;
            bottom: -6px;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: #1d4ed8;
            border: 2px solid #60a5fa;
            font-size: 10px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.12);
        }

        .crm-avatar-upload-initials {
            line-height: 1;
        }

        .crm-user-avatar-trigger {
            cursor: pointer;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .crm-user-avatar-trigger:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 36px rgba(37, 99, 235, 0.14);
        }

        .crm-user-avatar-hint {
            text-align: center;
        }

        .crm-user-avatar-tip-list {
            display: grid;
            gap: 12px;
        }

        .crm-user-avatar-tip {
            display: grid;
            grid-template-columns: 40px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            padding: 12px 14px;
            border-radius: 3px;
            background: rgba(255, 255, 255, 0.84);
            border: 1px solid #e2e8f0;
        }

        .crm-user-avatar-tip strong,
        .crm-user-avatar-tip span {
            display: block;
        }

        .crm-user-avatar-tip span {
            color: #64748b;
            font-size: 13px;
            line-height: 1.45;
            margin-top: 4px;
        }

        .crm-user-avatar-tip-icon {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1d4ed8;
            font-size: 18px;
        }

        .crm-pill-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .crm-select-pill {
            position: relative;
            margin: 0;
        }

        .crm-select-pill-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .crm-select-pill-face {
            display: inline-flex;
            align-items: center;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
            transition: all 0.18s ease;
            user-select: none;
        }

        .crm-select-pill-input:checked + .crm-select-pill-face {
            border-color: #2563eb;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.14);
        }

        .crm-select-pill-input:focus-visible + .crm-select-pill-face {
            outline: 0;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
        }

        .crm-select-pill-face:hover {
            border-color: #93c5fd;
            transform: translateY(-1px);
        }

        .crm-empty-inline {
            padding: 12px 14px;
            border: 1px dashed #cbd5e1;
            border-radius: 3px;
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
        }

        .crm-clock-widget {
            display: inline-flex;
            align-items: center;
        }

        .crm-clock-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #e9ebef;
            background: #fff;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
            white-space: nowrap;
        }

        .crm-clock-btn.is-out {
            color: #334155;
        }

        .crm-clock-btn.is-out:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        }

        .crm-clock-btn.is-in {
            border-color: #6ee7b7;
            color: #065f46;
            background: rgba(10, 179, 156, 0.06);
        }

        .crm-clock-btn.is-in:hover {
            border-color: #34d399;
            background: rgba(10, 179, 156, 0.1);
            box-shadow: 0 6px 18px rgba(10, 179, 156, 0.12);
        }

        .crm-clock-btn i {
            font-size: 16px;
        }

        .crm-clock-timer {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            border-radius: 999px;
            background: rgba(10, 179, 156, 0.12);
            color: #065f46;
            font-size: 11px;
            font-weight: 700;
        }

        .crm-clock-label {
            display: none;
        }

        @media (min-width: 768px) {
            .crm-clock-label {
                display: inline;
            }
        }

        .crm-slide-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: 420px;
            height: 100vh;
            background: #ffffff;
            border-left: 1px solid #e5e7eb;
            box-shadow: -4px 0 24px rgba(0, 0, 0, 0.08);
            z-index: 1050;
            transform: translateX(100%);
            transition: transform 0.25s ease;
            overflow-y: auto;
            padding: 24px;
        }

        .crm-slide-panel.is-open {
            transform: translateX(0);
        }

        .crm-slide-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .crm-slide-panel-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.2);
            z-index: 1049;
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }

        .crm-slide-panel-backdrop.is-visible {
            opacity: 1;
            pointer-events: auto;
        }

        @media (max-width: 768px) {
            .crm-slide-panel {
                width: 100%;
            }
        }

        .crm-user-summary {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
        }

        .crm-user-summary-main {
            display: flex;
            gap: 18px;
            align-items: center;
        }

        .crm-user-summary-main h2 {
            margin: 0 0 6px;
        }

        .crm-user-summary-aside {
            min-width: 280px;
        }

        .crm-stack-sm {
            display: grid;
            gap: 10px;
        }

        .crm-file-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            background: #fff;
        }

        .crm-file-row strong,
        .crm-list-item strong {
            display: block;
        }

        .crm-file-row .crm-muted,
        .crm-list-item .crm-muted {
            display: block;
            margin-top: 4px;
        }

        .crm-list-item-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .crm-inline-form {
            display: inline-flex;
        }

        .crm-cropper-backdrop[hidden] {
            display: none !important;
        }

        .crm-cropper-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.72);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            padding: 24px;
        }

        .crm-cropper-modal {
            width: min(840px, 100%);
            background: #fff;
            border-radius: 3px;
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.25);
            overflow: hidden;
        }

        .crm-cropper-header,
        .crm-cropper-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid #e5e7eb;
        }

        .crm-cropper-footer {
            border-bottom: 0;
            border-top: 1px solid #e5e7eb;
        }

        .crm-cropper-body {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 240px;
            gap: 20px;
            padding: 22px;
            background: #f8fafc;
        }

        .crm-cropper-canvas-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.06), rgba(59, 130, 246, 0.08));
            border-radius: 3px;
            padding: 18px;
            min-height: 420px;
        }

        .crm-cropper-canvas {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16);
            cursor: grab;
            background: #dbeafe;
        }

        .crm-cropper-canvas.is-dragging {
            cursor: grabbing;
        }

        .crm-cropper-controls {
            display: grid;
            gap: 16px;
            align-content: start;
        }

        .crm-cropper-note {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }

        @media (max-width: 992px) {
            .crm-user-profile-layout {
                grid-template-columns: 1fr;
            }

            .crm-user-summary,
            .crm-user-summary-main,
            .crm-file-row,
            .crm-list-item-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .crm-user-summary-aside {
                min-width: 0;
                width: 100%;
            }

            .crm-cropper-body {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('head')
</head>
<body class="crm-body pace-done" data-sidebar="light" data-layout="vertical" data-sidebar-size="lg">
    <div id="layout-wrapper">
        @include('layouts.crm-topbar')
        @include('layouts.crm-sidebar')

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="crm-shell-content">
                        @php
                            $routeName = request()->route()?->getName();
                            $modules = collect(config('heritage_crm.modules', []))
                                ->map(fn (array $module, string $key) => $module + ['key' => $key]);

                            $activeModule = $modules->first(function (array $module) use ($routeName) {
                                foreach ($module['match'] ?? [$module['route']] as $pattern) {
                                    if (\Illuminate\Support\Str::is($pattern, (string) $routeName)) {
                                        return true;
                                    }
                                }

                                return false;
                            });

                            $activeChild = collect($activeModule['children'] ?? [])->first(function (array $child) use ($routeName) {
                                foreach ($child['match'] ?? [$child['route']] as $pattern) {
                                    if (\Illuminate\Support\Str::is($pattern, (string) $routeName)) {
                                        return true;
                                    }
                                }

                                return false;
                            });

                            $heading = trim($__env->yieldContent('crm_heading', 'Heritage Pro CRM'));
                            $breadcrumbTitle = trim($__env->yieldContent('crm_breadcrumb_title'));
                            $moduleLabel = $activeChild['label'] ?? ($activeModule['label'] ?? $heading);

                            if ($breadcrumbTitle === '') {
                                if ($routeName === 'crm.dashboard') {
                                    $breadcrumbTitle = 'Dashboard';
                                } else {
                                    $breadcrumbTitle = $moduleLabel;
                                }
                            }

                            $breadcrumbOne = trim($__env->yieldContent('crm_breadcrumb_1'));
                            $breadcrumbOneUrl = trim($__env->yieldContent('crm_breadcrumb_1_url'));
                            $breadcrumbTwo = trim($__env->yieldContent('crm_breadcrumb_2'));
                            $breadcrumbTwoUrl = trim($__env->yieldContent('crm_breadcrumb_2_url'));

                            if ($breadcrumbOne === '') {
                                $breadcrumbOne = 'CRM';
                                $breadcrumbOneUrl = route('crm.dashboard');
                            }

                            $subheading = trim($__env->yieldContent('crm_subheading', 'Manage customers, contacts, requests, pipeline settings, and internal communication from one workspace.'));
                            $headerStats = trim($__env->yieldContent('crm_header_stats'));
                            $hasHeaderStats = $headerStats !== '';
                            $shellAttributes = trim($__env->yieldContent('crm_shell_attributes'));
                        @endphp

                        @include('components.breadcrumb', [
                            'title' => $breadcrumbTitle,
                            'li_1' => $breadcrumbOne,
                            'li_1_url' => $breadcrumbOneUrl,
                            'li_2' => $breadcrumbTwo !== '' ? $breadcrumbTwo : null,
                            'li_2_url' => $breadcrumbTwoUrl !== '' ? $breadcrumbTwoUrl : null,
                        ])

                        @if ($hasHeaderStats)
                            @hasSection('crm_actions')
                                <div class="crm-page-header crm-page-header-tools-only">
                                    <div class="crm-page-tools">
                                        @yield('crm_actions')
                                    </div>
                                </div>
                            @endif

                            <section class="crm-summary-hero">
                                <div class="crm-summary-hero-copy">
                                    <h1 class="crm-summary-hero-title">{{ $heading }}</h1>
                                    <p class="crm-summary-hero-subtitle">{{ $subheading }}</p>
                                </div>
                                <div class="crm-summary-hero-stats">
                                    {!! $headerStats !!}
                                </div>
                            </section>
                        @else
                            <div class="crm-page-header">
                                <div>
                                    <h1 class="crm-page-title">{{ $heading }}</h1>
                                    <p class="crm-page-subtitle">{{ $subheading }}</p>
                                </div>

                                @hasSection('crm_actions')
                                    <div class="crm-page-tools">
                                        @yield('crm_actions')
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div {!! $shellAttributes !== '' ? $shellAttributes : '' !!}>
                            @include('crm.partials.flash')
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>

            @include('layouts.crm-footer')
        </div>
    </div>

    <div id="crm-image-cropper-modal" class="crm-cropper-backdrop" hidden>
        <div class="crm-cropper-modal" role="dialog" aria-modal="true" aria-labelledby="crm-image-cropper-title">
            <div class="crm-cropper-header">
                <div>
                    <p class="crm-kicker">Profile image</p>
                    <h3 id="crm-image-cropper-title">Crop image</h3>
                </div>
                <button type="button" class="btn btn-light crm-btn-light" id="crm-image-cropper-cancel-top">
                    <i class="bx bx-x"></i> Close
                </button>
            </div>
            <div class="crm-cropper-body">
                <div class="crm-cropper-canvas-wrap">
                    <canvas id="crm-image-cropper-canvas" class="crm-cropper-canvas" width="360" height="360"></canvas>
                </div>
                <div class="crm-cropper-controls">
                    <div class="crm-field">
                        <label for="crm-image-cropper-zoom">Zoom</label>
                        <input id="crm-image-cropper-zoom" type="range" min="1" max="3" step="0.01" value="1">
                    </div>
                    <div class="crm-field">
                        <label>Instructions</label>
                        <div class="crm-help">
                            <div class="crm-help-title">Drag to reposition</div>
                            <div class="crm-help-content">Move the image inside the square and adjust the zoom until the framing looks right.</div>
                        </div>
                    </div>
                    <p class="crm-cropper-note" id="crm-image-cropper-note">The image is saved as a square image for consistent display across CRM.</p>
                </div>
            </div>
            <div class="crm-cropper-footer">
                <button type="button" class="btn btn-light crm-btn-light" id="crm-image-cropper-cancel-bottom">
                    <i class="bx bx-arrow-back"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="crm-image-cropper-apply">
                    <i class="bx bx-crop"></i> Apply crop
                </button>
            </div>
        </div>
    </div>

    <audio id="crm-discussion-sound" preload="auto">
        <source src="{{ asset('assets/sounds/crm-message-soft.wav') }}" type="audio/wav">
    </audio>

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metismenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var shellRoot = document.getElementById('layout-wrapper');

            if (window.bootstrap && bootstrap.Modal && bootstrap.Modal.Default) {
                bootstrap.Modal.Default.backdrop = 'static';
            }

            if (window.jQuery && typeof window.jQuery.fn.metisMenu === 'function') {
                window.jQuery('#side-menu').metisMenu();
            }

            var menuToggle = document.getElementById('vertical-menu-btn');
            if (menuToggle) {
                menuToggle.addEventListener('click', function () {
                    document.body.classList.toggle('sidebar-enable');

                    if (window.innerWidth >= 992) {
                        document.body.classList.toggle('vertical-collpsed');
                    }
                });
            }

            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    var submitBtn = form.querySelector('button[type="submit"].btn-loading');

                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            document.querySelectorAll('.crm-form input, .crm-form textarea, .crm-filter-form input, .crm-filter-form textarea').forEach(function (field) {
                if (field.hasAttribute('placeholder')) {
                    return;
                }

                if (['checkbox', 'radio', 'hidden', 'submit', 'button', 'file'].indexOf(field.type) !== -1) {
                    return;
                }

                var fieldId = field.getAttribute('id');
                var label = fieldId ? document.querySelector('label[for="' + fieldId + '"]') : null;
                var labelText = label ? label.textContent.replace('*', '').trim() : '';

                if (labelText !== '') {
                    field.setAttribute('placeholder', 'Enter ' + labelText.toLowerCase());
                }
            });

            function syncToastProgress(toast, duration) {
                var progress = toast.querySelector('[data-crm-toast-progress]');

                if (!progress) {
                    return;
                }

                progress.style.animation = 'none';
                progress.offsetHeight;
                progress.style.animation = 'crmToastProgress ' + duration + 'ms linear forwards';
                progress.style.animationPlayState = toast.classList.contains('is-paused') ? 'paused' : 'running';
            }

            function closeToast(toast) {
                if (!toast || toast.classList.contains('is-closing')) {
                    return;
                }

                toast.classList.add('is-closing');

                if (toast._crmToastTimer) {
                    window.clearTimeout(toast._crmToastTimer);
                    toast._crmToastTimer = null;
                }

                window.setTimeout(function () {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 260);
            }

            function startToastTimer(toast, duration) {
                toast._crmToastRemaining = duration;
                toast._crmToastStartedAt = Date.now();

                if (toast._crmToastTimer) {
                    window.clearTimeout(toast._crmToastTimer);
                }

                syncToastProgress(toast, duration);

                toast._crmToastTimer = window.setTimeout(function () {
                    closeToast(toast);
                }, duration);
            }

            function pauseToast(toast) {
                if (toast.classList.contains('is-closing') || !toast._crmToastTimer) {
                    return;
                }

                window.clearTimeout(toast._crmToastTimer);
                toast._crmToastTimer = null;
                toast._crmToastRemaining = Math.max(0, toast._crmToastRemaining - (Date.now() - toast._crmToastStartedAt));
                toast.classList.add('is-paused');

                var progress = toast.querySelector('[data-crm-toast-progress]');

                if (progress) {
                    progress.style.animationPlayState = 'paused';
                }
            }

            function resumeToast(toast) {
                if (toast.classList.contains('is-closing')) {
                    return;
                }

                var remaining = parseInt(toast._crmToastRemaining || 0, 10);

                if (remaining <= 0) {
                    closeToast(toast);
                    return;
                }

                toast.classList.remove('is-paused');
                startToastTimer(toast, remaining);
            }

            document.querySelectorAll('[data-crm-toast]').forEach(function (toast) {
                var duration = parseInt(toast.getAttribute('data-duration') || '4800', 10);
                var closeButton = toast.querySelector('[data-crm-toast-close]');

                startToastTimer(toast, duration);

                if (closeButton) {
                    closeButton.addEventListener('click', function () {
                        closeToast(toast);
                    });
                }

                toast.addEventListener('mouseenter', function () {
                    pauseToast(toast);
                });

                toast.addEventListener('mouseleave', function () {
                    resumeToast(toast);
                });
            });

            document.querySelectorAll('[data-dropzone]').forEach(function (dropzone) {
                var input = dropzone.querySelector('[data-dropzone-input]');
                var list = dropzone.querySelector('[data-dropzone-list]');

                if (!input || !list) {
                    return;
                }

                function formatSize(bytes) {
                    if (!bytes) {
                        return '0 KB';
                    }

                    var units = ['B', 'KB', 'MB', 'GB'];
                    var size = bytes;
                    var unitIndex = 0;

                    while (size >= 1024 && unitIndex < units.length - 1) {
                        size = size / 1024;
                        unitIndex += 1;
                    }

                    var precision = unitIndex === 0 ? 0 : 1;

                    return size.toFixed(precision) + ' ' + units[unitIndex];
                }

                function renderFiles(files) {
                    list.innerHTML = '';

                    if (!files || files.length === 0) {
                        list.innerHTML = '<div class="crm-dropzone-empty">No files selected yet.</div>';
                        return;
                    }

                    Array.from(files).forEach(function (file) {
                        var item = document.createElement('div');
                        item.className = 'crm-dropzone-file';
                        item.innerHTML = '<strong>' + file.name + '</strong><span>' + formatSize(file.size) + '</span>';
                        list.appendChild(item);
                    });
                }

                function assignFiles(fileList) {
                    var incomingFiles = Array.from(fileList || []);

                    if (!input.multiple) {
                        if (typeof DataTransfer === 'undefined') {
                            input.files = incomingFiles.length > 0 ? fileList : null;
                            renderFiles(input.files);
                            return;
                        }

                        var singleTransfer = new DataTransfer();

                        if (incomingFiles.length > 0) {
                            singleTransfer.items.add(incomingFiles[0]);
                        }

                        input.files = singleTransfer.files;
                        renderFiles(input.files);
                        return;
                    }

                    if (typeof DataTransfer === 'undefined') {
                        input.files = fileList;
                        renderFiles(input.files);
                        return;
                    }

                    var dataTransfer = new DataTransfer();

                    Array.from(input.files || []).forEach(function (file) {
                        dataTransfer.items.add(file);
                    });

                    incomingFiles.forEach(function (file) {
                        dataTransfer.items.add(file);
                    });

                    input.files = dataTransfer.files;
                    renderFiles(input.files);
                }

                dropzone.addEventListener('click', function (event) {
                    if (event.target === input || event.target.closest('[data-dropzone-input]')) {
                        return;
                    }

                    if (event.target.closest('a, button')) {
                        return;
                    }

                    input.click();
                });

                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.add('is-dragover');
                    });
                });

                ['dragleave', 'dragend', 'drop'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.remove('is-dragover');
                    });
                });

                dropzone.addEventListener('drop', function (event) {
                    if (!event.dataTransfer || !event.dataTransfer.files) {
                        return;
                    }

                    assignFiles(event.dataTransfer.files);
                });

                input.addEventListener('change', function () {
                    renderFiles(input.files);
                });

                renderFiles(input.files);
            });

            var cropperModal = document.getElementById('crm-image-cropper-modal');
            var cropperCanvas = document.getElementById('crm-image-cropper-canvas');
            var cropperContext = cropperCanvas ? cropperCanvas.getContext('2d') : null;
            var cropperZoom = document.getElementById('crm-image-cropper-zoom');
            var cropperApply = document.getElementById('crm-image-cropper-apply');
            var cropperCancelTop = document.getElementById('crm-image-cropper-cancel-top');
            var cropperCancelBottom = document.getElementById('crm-image-cropper-cancel-bottom');
            var cropperTitle = document.getElementById('crm-image-cropper-title');
            var cropperNote = document.getElementById('crm-image-cropper-note');
            var cropperState = {
                image: null,
                scale: 1,
                minScale: 1,
                x: 0,
                y: 0,
                dragging: false,
                startX: 0,
                startY: 0,
                originX: 0,
                originY: 0,
                targetInput: null,
                previewImage: null,
                fallbackTarget: null,
                fileInput: null,
                objectUrl: null
            };

            function closeCropper() {
                if (!cropperModal) {
                    return;
                }

                cropperModal.hidden = true;

                if (cropperState.fileInput) {
                    cropperState.fileInput.value = '';
                }

                if (cropperState.objectUrl) {
                    URL.revokeObjectURL(cropperState.objectUrl);
                }

                cropperState.image = null;
                cropperState.targetInput = null;
                cropperState.previewImage = null;
                cropperState.fallbackTarget = null;
                cropperState.fileInput = null;
                cropperState.objectUrl = null;
                cropperState.dragging = false;
            }

            function renderCropperCanvas() {
                if (!cropperContext || !cropperState.image) {
                    return;
                }

                var minX = cropperCanvas.width - (cropperState.image.width * cropperState.scale);
                var minY = cropperCanvas.height - (cropperState.image.height * cropperState.scale);

                cropperState.x = Math.min(0, Math.max(minX, cropperState.x));
                cropperState.y = Math.min(0, Math.max(minY, cropperState.y));

                cropperContext.clearRect(0, 0, cropperCanvas.width, cropperCanvas.height);
                cropperContext.fillStyle = '#dbeafe';
                cropperContext.fillRect(0, 0, cropperCanvas.width, cropperCanvas.height);
                cropperContext.drawImage(
                    cropperState.image,
                    cropperState.x,
                    cropperState.y,
                    cropperState.image.width * cropperState.scale,
                    cropperState.image.height * cropperState.scale
                );
            }

            function startCropper(fileInput, file) {
                if (!cropperModal || !cropperCanvas || !cropperZoom || !file) {
                    return;
                }

                var hiddenTargetId = fileInput.getAttribute('data-cropper-hidden-target');
                var previewTargetId = fileInput.getAttribute('data-cropper-preview-target');
                var fallbackTargetId = fileInput.getAttribute('data-cropper-fallback-target');
                var cropperTitleText = fileInput.getAttribute('data-cropper-title') || 'Crop image';
                var cropperNoteText = fileInput.getAttribute('data-cropper-note') || 'The image is saved as a square image for consistent display across CRM.';
                var targetInput = hiddenTargetId ? document.getElementById(hiddenTargetId) : null;
                var previewImage = previewTargetId ? document.getElementById(previewTargetId) : null;
                var fallbackTarget = fallbackTargetId ? document.getElementById(fallbackTargetId) : null;

                if (!targetInput || !previewImage) {
                    fileInput.value = '';
                    return;
                }

                var objectUrl = URL.createObjectURL(file);
                var image = new Image();

                image.onload = function () {
                    cropperState.image = image;
                    cropperState.fileInput = fileInput;
                    cropperState.targetInput = targetInput;
                    cropperState.previewImage = previewImage;
                    cropperState.fallbackTarget = fallbackTarget;
                    cropperState.objectUrl = objectUrl;
                    cropperState.minScale = Math.max(
                        cropperCanvas.width / image.width,
                        cropperCanvas.height / image.height
                    );
                    cropperState.scale = cropperState.minScale;
                    cropperState.x = (cropperCanvas.width - (image.width * cropperState.scale)) / 2;
                    cropperState.y = (cropperCanvas.height - (image.height * cropperState.scale)) / 2;

                    cropperZoom.min = cropperState.minScale.toFixed(2);
                    cropperZoom.max = Math.max(cropperState.minScale * 3, cropperState.minScale + 0.5).toFixed(2);
                    cropperZoom.step = '0.01';
                    cropperZoom.value = cropperState.scale.toFixed(2);
                    if (cropperTitle) {
                        cropperTitle.textContent = cropperTitleText;
                    }
                    if (cropperNote) {
                        cropperNote.textContent = cropperNoteText;
                    }

                    cropperModal.hidden = false;
                    renderCropperCanvas();
                };

                image.src = objectUrl;
            }

            document.querySelectorAll('[data-cropper-input]').forEach(function (input) {
                input.addEventListener('change', function () {
                    if (!input.files || !input.files[0]) {
                        return;
                    }

                    startCropper(input, input.files[0]);
                });
            });

            if (cropperZoom) {
                cropperZoom.addEventListener('input', function () {
                    if (!cropperState.image) {
                        return;
                    }

                    var newScale = parseFloat(cropperZoom.value || cropperState.minScale);
                    var centerX = cropperCanvas.width / 2;
                    var centerY = cropperCanvas.height / 2;
                    var ratio = newScale / cropperState.scale;

                    cropperState.x = centerX - ((centerX - cropperState.x) * ratio);
                    cropperState.y = centerY - ((centerY - cropperState.y) * ratio);
                    cropperState.scale = newScale;
                    renderCropperCanvas();
                });
            }

            function dragPosition(event) {
                if (!cropperState.dragging) {
                    return;
                }

                var point = event.touches ? event.touches[0] : event;
                cropperState.x = cropperState.originX + (point.clientX - cropperState.startX);
                cropperState.y = cropperState.originY + (point.clientY - cropperState.startY);
                renderCropperCanvas();
            }

            if (cropperCanvas) {
                ['mousedown', 'touchstart'].forEach(function (eventName) {
                    cropperCanvas.addEventListener(eventName, function (event) {
                        if (!cropperState.image) {
                            return;
                        }

                        var point = event.touches ? event.touches[0] : event;
                        cropperState.dragging = true;
                        cropperState.startX = point.clientX;
                        cropperState.startY = point.clientY;
                        cropperState.originX = cropperState.x;
                        cropperState.originY = cropperState.y;
                        cropperCanvas.classList.add('is-dragging');
                    });
                });
            }

            ['mousemove', 'touchmove'].forEach(function (eventName) {
                document.addEventListener(eventName, dragPosition, {passive: false});
            });

            ['mouseup', 'mouseleave', 'touchend', 'touchcancel'].forEach(function (eventName) {
                document.addEventListener(eventName, function () {
                    cropperState.dragging = false;
                    if (cropperCanvas) {
                        cropperCanvas.classList.remove('is-dragging');
                    }
                });
            });

            if (cropperApply) {
                cropperApply.addEventListener('click', function () {
                    if (!cropperState.targetInput || !cropperState.previewImage) {
                        closeCropper();
                        return;
                    }

                    var dataUrl = cropperCanvas.toDataURL('image/png');
                    cropperState.targetInput.value = dataUrl;
                    cropperState.previewImage.src = dataUrl;
                    cropperState.previewImage.classList.remove('d-none');

                    if (cropperState.fallbackTarget) {
                        cropperState.fallbackTarget.classList.add('d-none');
                    }

                    closeCropper();
                });
            }

            [cropperCancelTop, cropperCancelBottom].forEach(function (button) {
                if (!button) {
                    return;
                }

                button.addEventListener('click', function () {
                    closeCropper();
                });
            });

            function debounce(callback, wait) {
                var timeout;

                return function () {
                    var args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(function () {
                        callback.apply(null, args);
                    }, wait);
                };
            }

            function hidePanel(panel, trigger) {
                if (!panel || !trigger) {
                    return;
                }

                panel.hidden = true;
                trigger.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }

            function showPanel(panel, trigger) {
                if (!panel || !trigger) {
                    return;
                }

                panel.hidden = false;
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }

            function wireFloatingPanel(triggerId, panelId, options) {
                var trigger = document.getElementById(triggerId);
                var panel = document.getElementById(panelId);

                if (!trigger || !panel) {
                    return {hide: function () {}};
                }

                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    var willShow = panel.hidden;

                    if (options && Array.isArray(options.closeOthers)) {
                        options.closeOthers.forEach(function (closeItem) {
                            closeItem.hide();
                        });
                    }

                    if (willShow) {
                        showPanel(panel, trigger);
                        if (options && typeof options.onOpen === 'function') {
                            options.onOpen();
                        }
                    } else {
                        hidePanel(panel, trigger);
                    }
                });

                panel.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                return {
                    hide: function () {
                        hidePanel(panel, trigger);
                    },
                    panel: panel,
                    trigger: trigger
                };
            }

            var searchInput = document.getElementById('crm-global-search');
            var searchResults = document.getElementById('crm-search-results');
            var shortcutHint = document.getElementById('crm-search-shortcut-hint');
            var isMac = /Mac|iPhone|iPad|iPod/.test(navigator.platform);

            if (shortcutHint) {
                shortcutHint.innerHTML = isMac ? '<kbd>⌘</kbd><span>+</span><kbd>K</kbd>' : '<kbd>Ctrl</kbd><span>+</span><kbd>Space</kbd>';
            }

            function renderSearchResults(payload) {
                if (!searchResults) {
                    return;
                }

                if (!payload.sections || payload.sections.length === 0) {
                    searchResults.innerHTML = '<div class="no-results">No matching CRM records found.</div>';
                    searchResults.hidden = false;
                    return;
                }

                searchResults.innerHTML = payload.sections.map(function (section) {
                    return '<div class="search-section">' +
                        '<div class="section-header"><i class="' + section.icon + '"></i><span>' + section.label + '</span></div>' +
                        section.items.map(function (item) {
                            return '<a class="result-item" href="' + item.url + '">' +
                                '<div class="result-name">' + item.label + '</div>' +
                                '<div class="result-details">' + (item.secondary || '') + '</div>' +
                            '</a>';
                        }).join('') +
                    '</div>';
                }).join('');

                searchResults.hidden = false;
            }

            var searchRequest = debounce(function () {
                var term = searchInput.value.trim();

                if (term.length < 2) {
                    searchResults.hidden = true;
                    searchResults.innerHTML = '';
                    return;
                }

                searchResults.hidden = false;
                searchResults.innerHTML = '<div class="loading-results"><span class="loading-spinner"></span><span>Searching CRM...</span></div>';

                fetch('{{ route('crm.search') }}?q=' + encodeURIComponent(term), {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(renderSearchResults)
                    .catch(function () {
                        searchResults.innerHTML = '<div class="no-results">Search is temporarily unavailable.</div>';
                    });
            }, 220);

            if (searchInput && searchResults) {
                searchInput.addEventListener('input', searchRequest);
                searchInput.addEventListener('focus', function () {
                    if (searchResults.innerHTML !== '') {
                        searchResults.hidden = false;
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                var openSearch = (isMac && event.metaKey && event.key.toLowerCase() === 'k')
                    || (!isMac && event.ctrlKey && event.code === 'Space')
                    || (!isMac && event.altKey && event.code === 'Space');

                if (openSearch && searchInput) {
                    event.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }

                if (event.key === 'Escape' && searchResults) {
                    searchResults.hidden = true;
                }
            });

            var presenceCount = document.getElementById('crm-presence-count');
            var presencePanelCount = document.getElementById('crm-presence-panel-count');
            var presenceList = document.getElementById('crm-presence-list');
            var presenceSearch = document.getElementById('crm-presence-search');
            var presenceUnreadBadge = document.getElementById('crm-presence-unread-badge');
            var presenceUnreadPanel = document.getElementById('crm-presence-unread-panel');
            var presenceUnreadList = document.getElementById('crm-presence-unread-list');
            var presenceSoundToggle = document.getElementById('crm-presence-sound-toggle');
            var presenceSoundPreview = document.getElementById('crm-presence-sound-preview');
            var presenceSoundStatus = document.getElementById('crm-presence-sound-status');
            var sidebarDiscussionsBadge = document.getElementById('crm-sidebar-discussions-badge');
            var sidebarChannelBadges = Array.prototype.slice.call(document.querySelectorAll('[data-crm-discussion-channel-badge]'));
            var discussionSound = document.getElementById('crm-discussion-sound');
            var activeDiscussionThreadNode = document.querySelector('[data-crm-active-discussion-thread]');
            var activeDiscussionThread = activeDiscussionThreadNode
                ? (parseInt(activeDiscussionThreadNode.getAttribute('data-crm-active-discussion-thread') || '', 10) || null)
                : null;
            var soundPreferenceRequest = null;
            var discussionSoundEnabled = presenceSoundToggle
                ? presenceSoundToggle.getAttribute('data-enabled') === 'true'
                : true;
            var discussionSoundUnlocked = false;
            var discussionSoundSeeded = false;
            var knownUnreadActivityKeys = {};
            var pendingUnreadActivityKeys = {};

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderPresenceUsers(payload) {
                if (!presenceCount || !presencePanelCount || !presenceList) {
                    return;
                }

                presenceCount.textContent = payload.online_count || 0;
                presencePanelCount.textContent = (payload.online_count || 0) + ' online';

                if (!payload.users || payload.users.length === 0) {
                    presenceList.innerHTML = '<div class="staff-presence-empty">No other CRM users are online right now.</div>';
                    return;
                }

                presenceList.innerHTML = payload.users.map(function (user) {
                    return '<div class="staff-presence-item">' +
                        '<span class="crm-initial-avatar">' + escapeHtml(user.initials) + '</span>' +
                        '<div class="staff-presence-meta">' +
                            '<div class="staff-presence-name">' + escapeHtml(user.name) + '</div>' +
                            '<div class="staff-presence-role">' + escapeHtml(user.role) + '</div>' +
                            '<div class="staff-presence-last-seen">Last active ' + escapeHtml(user.last_seen_label) + '</div>' +
                        '</div>' +
                        '<a href="' + user.discussion_url + '" class="btn btn-primary btn-sm">Message</a>' +
                    '</div>';
                }).join('');
            }

            function unreadActivityKey(thread) {
                if (!thread) {
                    return '';
                }

                return [
                    thread.thread_id || thread.id || '',
                    thread.message_id || '',
                    thread.activity_at || '',
                ].join(':');
            }

            function syncDiscussionSoundToggle(enabled) {
                discussionSoundEnabled = !!enabled;

                if (!presenceSoundToggle) {
                    return;
                }

                presenceSoundToggle.setAttribute('data-enabled', discussionSoundEnabled ? 'true' : 'false');
                presenceSoundToggle.setAttribute('aria-pressed', discussionSoundEnabled ? 'true' : 'false');
                presenceSoundToggle.classList.toggle('is-enabled', discussionSoundEnabled);
                presenceSoundToggle.classList.toggle('is-muted', !discussionSoundEnabled);
                presenceSoundToggle.innerHTML = discussionSoundEnabled
                    ? '<i class="bx bx-volume-full"></i><span>Sound on</span>'
                    : '<i class="bx bx-volume-mute"></i><span>Sound off</span>';

                if (presenceSoundStatus) {
                    presenceSoundStatus.textContent = discussionSoundEnabled
                        ? 'Sound plays for new unread activity only, not for the thread you already have open.'
                        : 'Discussion sounds are muted for this account.';
                }

                if (!discussionSoundEnabled) {
                    pendingUnreadActivityKeys = {};
                }
            }

            function primeDiscussionSoundPlayback() {
                if (!discussionSound || discussionSoundUnlocked) {
                    return;
                }

                var originalVolume = discussionSound.volume;
                discussionSound.volume = 0;
                var attempt = discussionSound.play();

                if (!attempt || typeof attempt.then !== 'function') {
                    discussionSound.pause();
                    discussionSound.currentTime = 0;
                    discussionSound.volume = originalVolume;
                    discussionSoundUnlocked = true;
                    return;
                }

                attempt.then(function () {
                    discussionSound.pause();
                    discussionSound.currentTime = 0;
                    discussionSound.volume = originalVolume;
                    discussionSoundUnlocked = true;
                    flushPendingDiscussionSound();
                }).catch(function () {
                    discussionSound.currentTime = 0;
                    discussionSound.volume = originalVolume;
                });
            }

            function registerDiscussionSoundUnlock() {
                ['pointerdown', 'keydown', 'touchstart'].forEach(function (eventName) {
                    document.addEventListener(eventName, primeDiscussionSoundPlayback, { passive: true });
                });
            }

            function flushPendingDiscussionSound() {
                if (!discussionSoundEnabled || !discussionSoundUnlocked || !discussionSound || Object.keys(pendingUnreadActivityKeys).length === 0) {
                    return;
                }

                discussionSound.pause();
                discussionSound.currentTime = 0;
                discussionSound.volume = 1;

                var pendingKeys = Object.keys(pendingUnreadActivityKeys);
                var clearPendingKeys = function () {
                    pendingKeys.forEach(function (key) {
                        delete pendingUnreadActivityKeys[key];
                    });
                };

                var playback = discussionSound.play();

                if (playback && typeof playback.then === 'function') {
                    playback.then(function () {
                        clearPendingKeys();
                    }).catch(function () {
                        // Keep pending keys so the next unlocked poll can retry.
                    });
                    return;
                }

                if (playback && typeof playback.catch === 'function') {
                    playback.catch(function () {
                        // Keep pending keys so the next unlocked poll can retry.
                    });
                    return;
                }

                clearPendingKeys();
            }

            function playDiscussionSoundPreview() {
                if (!discussionSound) {
                    return;
                }

                discussionSound.pause();
                discussionSound.currentTime = 0;
                discussionSound.volume = 1;

                var playback = discussionSound.play();

                if (playback && typeof playback.then === 'function') {
                    playback.then(function () {
                        discussionSoundUnlocked = true;

                        if (presenceSoundStatus) {
                            presenceSoundStatus.textContent = discussionSoundEnabled
                                ? 'Preview played. New unread activity will chime automatically.'
                                : 'Preview played. Automatic discussion sounds are still muted.';
                        }
                    }).catch(function () {
                        if (presenceSoundStatus) {
                            presenceSoundStatus.textContent = 'The browser blocked audio playback. Click the test button again and check system volume.';
                        }
                    });
                }
            }

            function playDiscussionSoundIfNeeded(payload) {
                var threads = payload && Array.isArray(payload.threads) ? payload.threads : [];
                var nextKnownActivityKeys = {};
                var nextPendingActivityKeys = {};

                threads.forEach(function (thread) {
                    var key = unreadActivityKey(thread);

                    if (!key) {
                        return;
                    }

                    nextKnownActivityKeys[key] = true;

                    if (!discussionSoundSeeded || knownUnreadActivityKeys[key]) {
                        if (pendingUnreadActivityKeys[key]) {
                            nextPendingActivityKeys[key] = thread;
                        }

                        return;
                    }

                    if (activeDiscussionThread && parseInt(thread.thread_id || 0, 10) === activeDiscussionThread) {
                        return;
                    }

                    nextPendingActivityKeys[key] = thread;
                });

                pendingUnreadActivityKeys = nextPendingActivityKeys;

                if (!discussionSoundSeeded) {
                    discussionSoundSeeded = true;
                    knownUnreadActivityKeys = nextKnownActivityKeys;
                    pendingUnreadActivityKeys = {};
                    return;
                }

                knownUnreadActivityKeys = nextKnownActivityKeys;

                flushPendingDiscussionSound();
            }

            function renderUnreadThreads(payload) {
                var count = parseInt((payload && payload.count) || 0, 10) || 0;
                var countLabel = count > 99 ? '99+' : String(count);

                if (payload && Object.prototype.hasOwnProperty.call(payload, 'discussion_sound_enabled')) {
                    syncDiscussionSoundToggle(!!payload.discussion_sound_enabled);
                }

                if (presenceUnreadBadge) {
                    presenceUnreadBadge.hidden = count === 0;
                    presenceUnreadBadge.textContent = countLabel;
                }

                if (sidebarDiscussionsBadge) {
                    sidebarDiscussionsBadge.hidden = count === 0;
                    sidebarDiscussionsBadge.textContent = countLabel;
                }

                if (sidebarChannelBadges.length > 0) {
                    var channelCounts = payload && payload.channel_counts ? payload.channel_counts : {};

                    sidebarChannelBadges.forEach(function (badge) {
                        var channel = badge.getAttribute('data-crm-discussion-channel-badge') || '';
                        var channelCount = parseInt(channelCounts[channel] || 0, 10) || 0;
                        var channelLabel = channelCount > 99 ? '99+' : String(channelCount);

                        badge.hidden = channelCount === 0;
                        badge.textContent = channelLabel;
                    });
                }

                if (!presenceUnreadPanel || !presenceUnreadList) {
                    return;
                }

                if (count === 0 || !payload.threads || payload.threads.length === 0) {
                    presenceUnreadPanel.hidden = true;
                    presenceUnreadList.innerHTML = '';
                    playDiscussionSoundIfNeeded({ threads: [] });
                    return;
                }

                presenceUnreadPanel.hidden = false;
                presenceUnreadList.innerHTML = payload.threads.map(function (thread) {
                    return '<a class="staff-presence-unread-link" href="' + thread.url + '">' +
                        '<span class="staff-presence-unread-icon"><i class="' + escapeHtml(thread.icon || 'bx bx-chat') + '"></i></span>' +
                        '<div class="staff-presence-unread-copy">' +
                            '<strong>' + escapeHtml(thread.label) + '</strong>' +
                            '<span class="staff-presence-unread-meta">' +
                                escapeHtml(thread.channel_label || 'Discussion') +
                                (thread.activity_reason_label ? ' · ' + escapeHtml(thread.activity_reason_label) : '') +
                                (thread.sender_label ? ' · ' + escapeHtml(thread.sender_label) : '') +
                                (thread.activity_label ? ' · ' + escapeHtml(thread.activity_label) : '') +
                            '</span>' +
                            '<span class="staff-presence-unread-preview">' + escapeHtml(thread.preview || 'Open this discussion to review the latest activity.') + '</span>' +
                        '</div>' +
                    '</a>';
                }).join('');

                playDiscussionSoundIfNeeded(payload);
            }

            function fetchPresence(searchTerm) {
                var query = searchTerm ? '?q=' + encodeURIComponent(searchTerm) : '';

                return fetch('{{ route('crm.presence.launcher') }}' + query, {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        renderPresenceUsers(payload);
                        return payload;
                    })
                    .catch(function () {
                        if (presenceList) {
                            presenceList.innerHTML = '<div class="staff-presence-empty">Unable to load online CRM users.</div>';
                        }
                    });
            }

            function fetchUnreadThreads() {
                return fetch('{{ route('crm.presence.unread-count') }}', {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        renderUnreadThreads(payload);
                        return payload;
                    })
                    .catch(function () {
                        renderUnreadThreads({ count: 0, threads: [] });
                    });
            }

            var presencePanelHandle = wireFloatingPanel('crm-presence-trigger', 'crm-presence-panel', {
                onOpen: function () {
                    fetchPresence(presenceSearch ? presenceSearch.value.trim() : '');
                }
            });

            var launcherPanelHandle = wireFloatingPanel('crm-module-launcher-trigger', 'crm-module-launcher-menu', {
                closeOthers: [presencePanelHandle]
            });

            var userPanelHandle = wireFloatingPanel('crm-user-dropdown', 'crm-user-dropdown-panel', {
                closeOthers: [presencePanelHandle, launcherPanelHandle]
            });

            if (presencePanelHandle && launcherPanelHandle && presencePanelHandle.trigger) {
                presencePanelHandle.trigger.addEventListener('click', function () {
                    launcherPanelHandle.hide();
                    userPanelHandle.hide();
                });
                launcherPanelHandle.trigger.addEventListener('click', function () {
                    presencePanelHandle.hide();
                    userPanelHandle.hide();
                });
            }

            if (presenceSearch) {
                presenceSearch.addEventListener('input', debounce(function () {
                    fetchPresence(presenceSearch.value.trim());
                }, 220));
            }

            if (presenceSoundToggle) {
                syncDiscussionSoundToggle(discussionSoundEnabled);

                presenceSoundToggle.addEventListener('click', function () {
                    var nextEnabled = !(presenceSoundToggle.getAttribute('data-enabled') === 'true');

                    if (soundPreferenceRequest) {
                        return;
                    }

                    syncDiscussionSoundToggle(nextEnabled);

                    soundPreferenceRequest = fetch('{{ route('crm.presence.discussion-sound.update') }}', {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            crm_discussion_sound_enabled: nextEnabled
                        })
                    }).then(function (response) {
                        if (!response.ok) {
                            throw new Error('Unable to save sound preference.');
                        }

                        return response.json();
                    }).then(function (payload) {
                        syncDiscussionSoundToggle(!!payload.discussion_sound_enabled);
                    }).catch(function () {
                        syncDiscussionSoundToggle(!nextEnabled);
                    }).finally(function () {
                        soundPreferenceRequest = null;
                    });
                });
            }

            if (presenceSoundPreview) {
                presenceSoundPreview.addEventListener('click', function () {
                    playDiscussionSoundPreview();
                });
            }

            registerDiscussionSoundUnlock();

            function sendHeartbeat() {
                fetch('{{ route('crm.presence.heartbeat') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        path: window.location.pathname
                    })
                }).catch(function () {});
            }

            sendHeartbeat();
            fetchPresence('');
            fetchUnreadThreads();

            var pollSeconds = {{ (int) config('heritage_crm.presence.launcher_poll_seconds', 45) }};
            window.setInterval(function () {
                sendHeartbeat();
                fetchPresence(presenceSearch ? presenceSearch.value.trim() : '');
                fetchUnreadThreads();
            }, pollSeconds * 1000);

            document.addEventListener('click', function (event) {
                if (searchResults && searchInput && !searchResults.contains(event.target) && event.target !== searchInput) {
                    searchResults.hidden = true;
                }

                presencePanelHandle.hide();
                launcherPanelHandle.hide();
                userPanelHandle.hide();
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
