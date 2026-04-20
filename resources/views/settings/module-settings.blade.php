@extends('layouts.master')
@section('title')
    Module Settings
@endsection
@section('css')
    <style>
        .module-settings-page {
            --ms-primary: #2563eb;
            --ms-primary-dark: #1d4ed8;
            --ms-surface: #ffffff;
            --ms-surface-soft: #f8fbff;
            --ms-border: #dbe5f0;
            --ms-muted: #64748b;
            --ms-heading: #0f172a;
            --ms-success-bg: #ecfdf3;
            --ms-success-text: #047857;
            --ms-danger-bg: #fef2f2;
            --ms-danger-text: #b91c1c;
        }

        .module-settings-shell {
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 62%);
            border: 1px solid #e4ecf5;
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .module-settings-hero {
            padding: 32px 32px 28px;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(125, 211, 252, 0.32), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #1d4ed8 58%, #2563eb 100%);
        }

        .module-settings-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 3px;
            margin-bottom: 14px;
            background: rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.88);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .module-settings-hero h3 {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .module-settings-hero p {
            max-width: 680px;
            margin: 12px 0 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 14px;
            line-height: 1.7;
        }

        .module-settings-hero .stat-item {
            padding: 10px 0;
        }

        .module-settings-hero .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .module-settings-hero .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .module-settings-body {
            padding: 32px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 28px;
        }

        .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .help-content p:last-child {
            margin-bottom: 0;
        }

        .module-section-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .module-section-heading h4 {
            margin: 0;
            color: var(--ms-heading);
            font-size: 20px;
            font-weight: 700;
        }

        .module-section-heading p {
            margin: 6px 0 0;
            color: var(--ms-muted);
            font-size: 13px;
        }

        .module-section-caption {
            color: var(--ms-muted);
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .module-card {
            background: var(--ms-surface);
            border: 1px solid var(--ms-border);
            border-radius: 3px;
            padding: 22px;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease,
                background-color 0.18s ease;
        }

        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        .module-card.is-active {
            border-color: rgba(37, 99, 235, 0.22);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .module-card.is-inactive {
            background: #fcfcfd;
        }

        .module-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .module-card-title {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            min-width: 0;
        }

        .module-card-icon {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.16) 0%, rgba(14, 165, 233, 0.08) 100%);
            color: var(--ms-primary);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
        }

        .module-card-icon i {
            font-size: 22px;
        }

        .module-card-title h5 {
            margin: 0;
            color: var(--ms-heading);
            font-size: 17px;
            font-weight: 700;
        }

        .module-card-title p {
            margin: 6px 0 0;
            color: var(--ms-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .module-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 78px;
            padding: 7px 12px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            white-space: nowrap;
        }

        .module-status-badge.status-visible {
            background: var(--ms-success-bg);
            color: var(--ms-success-text);
        }

        .module-status-badge.status-hidden {
            background: var(--ms-danger-bg);
            color: var(--ms-danger-text);
        }

        .module-toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-top: 18px;
            padding: 16px 18px;
            border-radius: 3px;
            background: rgba(148, 163, 184, 0.08);
        }

        .module-toggle-copy {
            min-width: 0;
        }

        .module-toggle-copy strong {
            display: block;
            color: var(--ms-heading);
            font-size: 13px;
            font-weight: 700;
        }

        .module-toggle-copy span {
            display: block;
            margin-top: 4px;
            color: var(--ms-muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .module-switch {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
            flex-shrink: 0;
        }

        .module-switch .form-check-input {
            width: 56px;
            height: 30px;
            margin: 0;
            cursor: pointer;
            background-color: #cbd5e1;
            border-color: #cbd5e1;
            border-radius: 999px;
            box-shadow: none;
        }

        .module-switch .form-check-input:checked {
            background-color: var(--ms-primary);
            border-color: var(--ms-primary);
        }

        .module-switch .form-check-input:focus {
            border-color: var(--ms-primary);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.16);
        }

        .module-roles {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #e7eef6;
        }

        .module-roles-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .module-roles-title strong {
            color: var(--ms-heading);
            font-size: 13px;
            font-weight: 700;
        }

        .module-roles-title span {
            color: var(--ms-muted);
            font-size: 12px;
        }

        .module-role-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #dbe5f0;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
        }

        .module-role-empty {
            margin: 0;
            padding: 14px 16px;
            border-radius: 3px;
            background: #f8fafc;
            color: var(--ms-muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .module-settings-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 28px;
            padding: 24px 0 0;
            border-top: 1px solid #e5edf5;
        }

        .module-settings-footer-title {
            display: block;
            color: var(--ms-heading);
            font-size: 14px;
            font-weight: 700;
        }

        .module-settings-footer-copy {
            display: block;
            margin-top: 4px;
            color: var(--ms-muted);
            font-size: 13px;
        }

        .module-settings-page .btn-primary {
            border: none;
            border-radius: 3px;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--ms-primary) 0%, var(--ms-primary-dark) 100%);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
            color: #fff;
            font-weight: 700;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .module-settings-page .btn-primary:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 18px 30px rgba(37, 99, 235, 0.24);
            filter: saturate(1.03);
        }

        .btn-loading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-loading .btn-text {
            display: inline-flex;
            align-items: center;
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

        @media (max-width: 1199.98px) {
            .modules-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .module-settings-footer,
            .module-section-heading {
                flex-direction: column;
                align-items: stretch;
            }

        }

        @media (max-width: 575.98px) {
            .module-settings-hero,
            .module-settings-body {
                padding: 24px 20px;
            }

            .module-card {
                padding: 18px;
            }

            .module-card-top,
            .module-toggle-row {
                flex-direction: column;
                align-items: stretch;
            }

            .module-status-badge,
            .module-switch {
                align-self: flex-start;
            }

            .module-settings-hero .text-md-end {
                text-align: left !important;
            }
        }
    </style>
@endsection
@section('content')
    @php
        $moduleCollection = collect($modules);
        $totalModules = $moduleCollection->count();
        $visibleCount = $moduleCollection->where('visible', true)->count();
        $hiddenCount = $totalModules - $visibleCount;
        $linkedRolesCount = $moduleCollection->sum(fn($module) => count($module['roles'] ?? []));
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('setup.school-setup') }}">Settings</a>
        @endslot
        @slot('title')
            Module Settings
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="module-settings-page">
        <div class="module-settings-shell">
            <div class="module-settings-hero">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <span class="module-settings-kicker">
                            <i class="fas fa-sliders-h"></i>
                            System Access
                        </span>
                        <h3>Module visibility</h3>
                        <p>
                            Choose which optional modules appear across the staff experience. Saving here updates the
                            sidebar, the topbar module launcher, and shared profile navigation in one place.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white js-visible-count">{{ $visibleCount }}</h4>
                                    <small class="opacity-75">Visible</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white js-hidden-count">{{ $hiddenCount }}</h4>
                                    <small class="opacity-75">Hidden</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $linkedRolesCount }}</h4>
                                    <small class="opacity-75">Linked Roles</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="module-settings-body">
                <div class="help-text">
                    <div class="help-title">
                        Scope
                    </div>
                    <div class="help-content">
                        <p>
                            These switches are global. They do not delete data, but they can remove entry points for staff
                            until the module is re-enabled.
                        </p>
                    </div>
                </div>

                <form action="{{ route('setup.module-settings-update') }}" method="POST">
                    @csrf

                    <div class="module-section-heading">
                        <div>
                            <h4>Available modules</h4>
                            <p>Review visibility state, then save once after you finish your changes.</p>
                        </div>
                        <div class="module-section-caption">{{ $totalModules }} module(s) configured</div>
                    </div>

                    <div class="modules-grid">
                        @foreach ($modules as $key => $module)
                            @php
                                $roleCount = count($module['roles'] ?? []);
                                $isVisible = (bool) $module['visible'];
                            @endphp

                            <div class="module-card {{ $isVisible ? 'is-active' : 'is-inactive' }}">
                                <div class="module-card-top">
                                    <div class="module-card-title">
                                        <span class="module-card-icon">
                                            <i class="{{ $module['icon'] }}"></i>
                                        </span>
                                        <div>
                                            <h5>{{ $module['name'] }}</h5>
                                            <p class="module-card-description">
                                                {{ $isVisible
                                                    ? 'Shown in the sidebar, module launcher, and shared profile navigation.'
                                                    : 'Removed from the sidebar, module launcher, and shared profile navigation until re-enabled.' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="module-status-badge {{ $isVisible ? 'status-visible' : 'status-hidden' }}">
                                        {{ $isVisible ? 'Visible' : 'Hidden' }}
                                    </span>
                                </div>

                                <div class="module-toggle-row">
                                    <div class="module-toggle-copy">
                                        <strong class="module-availability-label">{{ $isVisible ? 'Visible to users' : 'Hidden from users' }}</strong>
                                        <span class="module-availability-copy">
                                            {{ $roleCount > 0
                                                ? $roleCount . ' linked ' . \Illuminate\Support\Str::plural('role', $roleCount) . ' will follow this setting.'
                                                : 'This module affects navigation visibility only.' }}
                                        </span>
                                    </div>
                                    <div class="form-check form-switch module-switch">
                                        <input
                                            class="form-check-input module-toggle-input"
                                            type="checkbox"
                                            role="switch"
                                            name="modules[{{ $key }}]"
                                            value="1"
                                            id="module_{{ $key }}"
                                            {{ $isVisible ? 'checked' : '' }}
                                        >
                                    </div>
                                </div>

                                <div class="module-roles">
                                    <div class="module-roles-title">
                                        <strong>Linked staff roles</strong>
                                        <span>{{ $roleCount }} role(s)</span>
                                    </div>
                                    @if ($roleCount > 0)
                                        <div class="module-role-list">
                                            @foreach ($module['roles'] as $role)
                                                <span class="role-badge">{{ $role }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="module-role-empty">
                                            No additional staff roles are hidden when this module is disabled.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="module-settings-footer">
                        <div>
                            <span class="module-settings-footer-title">Apply all visibility changes together</span>
                            <span class="module-settings-footer-copy">Save once to update module access across the application.</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save me-2"></i>Save Changes</span>
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
@endsection

@section('script')
<script>
    $(document).ready(function() {
        function updateSummaryCounts() {
            var totalModules = $('.module-toggle-input').length;
            var visibleCount = $('.module-toggle-input:checked').length;
            var hiddenCount = totalModules - visibleCount;

            $('.js-visible-count').text(visibleCount);
            $('.js-hidden-count').text(hiddenCount);
        }

        function updateModuleCardState($input) {
            var isVisible = $input.is(':checked');
            var $card = $input.closest('.module-card');
            var $badge = $card.find('.module-status-badge');
            var $availabilityLabel = $card.find('.module-availability-label');
            var $availabilityCopy = $card.find('.module-availability-copy');
            var $description = $card.find('.module-card-description');

            $card.toggleClass('is-active', isVisible);
            $card.toggleClass('is-inactive', !isVisible);

            $badge
                .toggleClass('status-visible', isVisible)
                .toggleClass('status-hidden', !isVisible)
                .text(isVisible ? 'Visible' : 'Hidden');

            $availabilityLabel.text(isVisible ? 'Visible to users' : 'Hidden from users');
            $description.text(
                isVisible
                    ? 'Shown in the sidebar, module launcher, and shared profile navigation.'
                    : 'Removed from the sidebar, module launcher, and shared profile navigation until re-enabled.'
            );

            if ($input.closest('.module-card').find('.module-role-list').length > 0) {
                var roleCount = $input.closest('.module-card').find('.module-role-list .role-badge').length;
                $availabilityCopy.text(
                    roleCount > 0
                        ? roleCount + ' linked ' + (roleCount === 1 ? 'role' : 'roles') + ' will follow this setting.'
                        : 'This module affects navigation visibility only.'
                );
            } else {
                $availabilityCopy.text('This module affects navigation visibility only.');
            }
        }

        $('.module-toggle-input').each(function() {
            updateModuleCardState($(this));
        });

        updateSummaryCounts();

        $('.module-toggle-input').on('change', function() {
            updateModuleCardState($(this));
            updateSummaryCounts();
        });

        $('form').on('submit', function(e) {
            var $btn = $(this).find('button[type="submit"].btn-loading');
            if ($btn.length) {
                $btn.addClass('loading');
                $btn.prop('disabled', true);
            }
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert .btn-close').click();
        }, 5000);
    });
</script>
@endsection
