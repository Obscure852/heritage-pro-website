@extends('layouts.crm')

@section('title', 'Users Settings')
@section('crm_heading', 'Users Settings')
@section('crm_subheading', 'Manage the staff directory master data used across profiles, assignments, and directory filters.')

@php
    $listSections = ['departments', 'positions', 'filters'];
    $isListSection = in_array($activeSection, $listSections, true);
    $settings = $settings ?? null;
    $companyNameValue = old('company_name', $settings?->company_name ?? 'Heritage Pro');
    $companyLogoPreviewId = 'crm-company-logo-preview';
    $companyLogoFallbackId = 'crm-company-logo-fallback';
    $companyLogoInputId = 'crm-company-logo-input';
    $companyLogoHiddenId = 'crm-company-logo-hidden';
    $loginImagePreviewId = 'crm-login-image-preview';
    $loginImageFallbackId = 'crm-login-image-fallback';
    $loginImageInputId = 'crm-login-image-input';
    $loginImageHiddenId = 'crm-login-image-hidden';
    $companyLogoUrl = old('company_logo_cropped_image') ?: ($settings?->company_logo_url ?? null);
    $loginImageUrl = old('login_image_cropped_image') ?: ($settings?->login_image_url ?? null);
    $companyInitials = collect(preg_split('/\s+/', trim((string) $companyNameValue)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');

    if ($activeSection === 'departments') {
        $items = $departments;
        $singularLabel = 'department';
        $tableHeading = 'Current departments';
        $tableKicker = 'Directory values';
        $helperCopy = 'Create the department master list used in staff profiles and directory filters.';
        $emptyMessage = 'No departments have been added yet.';
        $emptyIcon = 'bx bx-building-house';
        $storeRoute = route('crm.users.settings.departments.store');
        $updateRouteTemplate = route('crm.users.settings.departments.update', ['crmUserDepartment' => '__ID__']);
        $editModel = $editDepartment;
        $defaultSortOrder = $departments->count() + 1;
        $sectionRoute = route('crm.users.settings.departments');
        $deleteRouteName = 'crm.users.settings.departments.destroy';
        $deleteMessage = 'Are you sure you want to permanently delete this department?';
    } elseif ($activeSection === 'positions') {
        $items = $positions;
        $singularLabel = 'position';
        $tableHeading = 'Current positions';
        $tableKicker = 'Directory values';
        $helperCopy = 'Create the position master list used in staff profiles and directory filters.';
        $emptyMessage = 'No positions have been added yet.';
        $emptyIcon = 'bx bx-briefcase-alt-2';
        $storeRoute = route('crm.users.settings.positions.store');
        $updateRouteTemplate = route('crm.users.settings.positions.update', ['crmUserPosition' => '__ID__']);
        $editModel = $editPosition;
        $defaultSortOrder = $positions->count() + 1;
        $sectionRoute = route('crm.users.settings.positions');
        $deleteRouteName = 'crm.users.settings.positions.destroy';
        $deleteMessage = 'Are you sure you want to permanently delete this position?';
    } elseif ($activeSection === 'filters') {
        $items = $filters;
        $singularLabel = 'custom filter';
        $tableHeading = 'Current custom filters';
        $tableKicker = 'Reusable tags';
        $helperCopy = 'Create reusable filter pills that can be attached to staff records and used in directory filtering.';
        $emptyMessage = 'No custom filters have been added yet.';
        $emptyIcon = 'bx bx-slider-alt';
        $storeRoute = route('crm.users.settings.filters.store');
        $updateRouteTemplate = route('crm.users.settings.filters.update', ['crmUserFilter' => '__ID__']);
        $editModel = $editFilter;
        $defaultSortOrder = $filters->count() + 1;
        $sectionRoute = route('crm.users.settings.filters');
        $deleteRouteName = 'crm.users.settings.filters.destroy';
        $deleteMessage = 'Are you sure you want to permanently delete this custom filter?';
    }

    if ($isListSection) {
        $oldModalSection = old('_settings_modal_section');
        $oldModalMode = old('_settings_modal_mode');
        $oldModalRecordId = (int) old('_settings_modal_record_id', 0);
        $resolvedEditModel = $editModel;

        if ($oldModalSection === $activeSection && $oldModalMode === 'edit' && $oldModalRecordId > 0) {
            $resolvedEditModel = $items->firstWhere('id', $oldModalRecordId) ?: $editModel;
        }

        $hasEditContext = $resolvedEditModel !== null;
        $autoOpenMode = $oldModalSection === $activeSection
            ? ($oldModalMode === 'edit' && $resolvedEditModel ? 'edit' : 'create')
            : ($editModel ? 'edit' : 'create');
        $shouldAutoOpenModal = ($errors->any() && $oldModalSection === $activeSection) || $editModel !== null;
        $initialRecordId = $hasEditContext ? $resolvedEditModel->id : null;
        $initialName = old('name', $hasEditContext ? $resolvedEditModel->name : '');
        $initialSortOrder = old('sort_order', $hasEditContext ? $resolvedEditModel->sort_order : $defaultSortOrder);
        $initialIsActive = (string) old('is_active', $hasEditContext ? ($resolvedEditModel->is_active ? '1' : '0') : '1') === '1';
        $initialAction = $hasEditContext ? str_replace('__ID__', (string) $resolvedEditModel->id, $updateRouteTemplate) : $storeRoute;
        $initialMethod = $hasEditContext ? 'PATCH' : 'POST';
    }
@endphp

@section('crm_actions')
    <a href="{{ route('crm.users.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to users
    </a>
@endsection

@push('head')
    <style>
        .crm-settings-list-card .crm-card-title {
            align-items: center;
        }

        .crm-settings-table-empty {
            text-align: center;
            color: #64748b;
            padding: 24px 16px;
        }

        .crm-settings-table-empty-copy {
            margin: 0;
        }

        .crm-settings-table-empty-icon {
            display: block;
            margin: 0 0 12px;
            color: #94a3b8;
            font-size: 30px;
            line-height: 1;
        }

        .crm-settings-form-shell {
            display: grid;
            gap: 20px;
        }

        .crm-settings-form-copy {
            margin: 0 0 18px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        .crm-branding-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            align-items: start;
        }

        .crm-branding-card {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            display: grid;
            gap: 18px;
        }

        .crm-branding-card-head {
            display: grid;
            gap: 8px;
        }

        .crm-branding-card-head h3 {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
        }

        .crm-branding-card-head p {
            margin: 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        .crm-branding-trigger {
            cursor: pointer;
            display: grid;
            justify-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .crm-branding-shell {
            width: min(100%, 220px);
            aspect-ratio: 1 / 1;
            border-radius: 3px;
            border: 1px solid #d8e4f2;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.16), transparent 38%),
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .crm-branding-trigger:hover .crm-branding-shell {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.14);
        }

        .crm-branding-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .crm-branding-placeholder {
            width: 100%;
            height: 100%;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            color: #1d4ed8;
            text-align: center;
            padding: 18px;
        }

        .crm-branding-placeholder-icon {
            position: relative;
            width: 56px;
            height: 56px;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid #bfdbfe;
            font-size: 24px;
        }

        .crm-branding-placeholder-icon .crm-avatar-upload-plus {
            right: -8px;
            bottom: -8px;
        }

        .crm-branding-placeholder-text {
            display: grid;
            gap: 6px;
        }

        .crm-branding-placeholder-text strong {
            font-size: 26px;
            line-height: 1;
            color: #0f172a;
        }

        .crm-branding-placeholder-text span {
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
        }

        .crm-branding-caption {
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
            text-align: center;
        }

        .crm-settings-modal .modal-content {
            border: 0;
            border-radius: 3px;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.18);
        }

        .crm-settings-modal .modal-header,
        .crm-settings-modal .modal-footer {
            border-color: #e5e7eb;
            padding: 18px 22px;
        }

        .crm-settings-modal .modal-body {
            padding: 22px;
        }

        .crm-settings-modal .modal-title {
            font-size: 19px;
            font-weight: 600;
            color: #1f2937;
        }

        .crm-settings-modal-copy {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 991.98px) {
            .crm-branding-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 767.98px) {
            .crm-settings-list-card .crm-card-title {
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="crm-stack">
        @include('crm.users.settings._tabs', ['activeSection' => $activeSection])

        @include('crm.partials.helper-text', [
            'title' => 'Users Settings',
            'content' => 'Maintain the reusable master data that powers staff profiles and directory filters.',
        ])

        @if ($isListSection)
            <section class="crm-card crm-settings-list-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">{{ $tableKicker }}</p>
                        <h2>{{ $tableHeading }}</h2>
                        <p>{{ $helperCopy }}</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-crm-user-setting-open="create">
                        <i class="bx bx-plus"></i> Add {{ $singularLabel }}
                    </button>
                </div>

                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Sort</th>
                                <th>Users</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td>{{ $item->users_count }}</td>
                                    <td>
                                        <span class="crm-pill {{ $item->is_active ? 'success' : 'muted' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a
                                                href="{{ $sectionRoute }}?edit={{ $item->id }}"
                                                class="btn crm-icon-action"
                                                title="Edit {{ $singularLabel }}"
                                                aria-label="Edit {{ $singularLabel }}"
                                                data-crm-user-setting-open="edit"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                data-sort-order="{{ $item->sort_order }}"
                                                data-is-active="{{ $item->is_active ? 1 : 0 }}"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route($deleteRouteName, $item),
                                                'message' => $deleteMessage,
                                                'label' => 'Delete ' . $singularLabel,
                                                'iconOnly' => true,
                                            ])
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="crm-settings-table-empty">
                                        <i class="{{ $emptyIcon }} crm-settings-table-empty-icon" aria-hidden="true"></i>
                                        <p class="crm-settings-table-empty-copy">{{ $emptyMessage }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @elseif ($activeSection === 'company-information')
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Company information</p>
                        <h2>Business profile</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('crm.settings.company-information.update') }}" class="crm-form crm-settings-form-shell">
                    @csrf
                    @method('PATCH')

                    <p class="crm-settings-form-copy">Maintain the core company details used across CRM branding, login screens, and internal reference points.</p>

                    <div class="crm-field-grid">
                        <div class="crm-field">
                            <label for="company_name">Company name <span class="text-danger">*</span></label>
                            <input id="company_name" name="company_name" value="{{ old('company_name', $settings?->company_name ?? '') }}" required>
                        </div>
                        <div class="crm-field">
                            <label for="company_email">Company email</label>
                            <input id="company_email" name="company_email" type="email" value="{{ old('company_email', $settings?->company_email ?? '') }}">
                        </div>
                        <div class="crm-field">
                            <label for="company_phone">Company phone</label>
                            <input id="company_phone" name="company_phone" value="{{ old('company_phone', $settings?->company_phone ?? '') }}">
                        </div>
                        <div class="crm-field">
                            <label for="company_website">Website</label>
                            <input id="company_website" name="company_website" type="url" value="{{ old('company_website', $settings?->company_website ?? '') }}">
                        </div>
                        <div class="crm-field full">
                            <label for="company_address_line_1">Address line 1</label>
                            <input id="company_address_line_1" name="company_address_line_1" value="{{ old('company_address_line_1', $settings?->company_address_line_1 ?? '') }}">
                        </div>
                        <div class="crm-field full">
                            <label for="company_address_line_2">Address line 2</label>
                            <input id="company_address_line_2" name="company_address_line_2" value="{{ old('company_address_line_2', $settings?->company_address_line_2 ?? '') }}">
                        </div>
                        <div class="crm-field">
                            <label for="company_city">City / town</label>
                            <input id="company_city" name="company_city" value="{{ old('company_city', $settings?->company_city ?? '') }}">
                        </div>
                        <div class="crm-field">
                            <label for="company_state">State / province</label>
                            <input id="company_state" name="company_state" value="{{ old('company_state', $settings?->company_state ?? '') }}">
                        </div>
                        <div class="crm-field">
                            <label for="company_country">Country</label>
                            <input id="company_country" name="company_country" value="{{ old('company_country', $settings?->company_country ?? '') }}">
                        </div>
                        <div class="crm-field">
                            <label for="company_postal_code">Postal code</label>
                            <input id="company_postal_code" name="company_postal_code" value="{{ old('company_postal_code', $settings?->company_postal_code ?? '') }}">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Save company information</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </section>
        @else
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Branding</p>
                        <h2>Logo and login image</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('crm.settings.branding.update') }}" class="crm-form crm-settings-form-shell">
                    @csrf
                    @method('PATCH')

                    <p class="crm-settings-form-copy">Upload the company logo shown in the CRM shell and the image displayed on the login screen. Both assets use the same square cropper for consistent presentation.</p>

                    <div class="crm-branding-grid">
                        <div class="crm-branding-card">
                            <div class="crm-branding-card-head">
                                <h3>Company logo</h3>
                                <p>Used in the CRM topbar and supporting brand touchpoints.</p>
                            </div>

                            <label for="{{ $companyLogoInputId }}" class="crm-branding-trigger">
                                <span class="crm-branding-shell">
                                    @if ($companyLogoUrl)
                                        <img src="{{ $companyLogoUrl }}" alt="{{ $companyNameValue }}" id="{{ $companyLogoPreviewId }}" class="crm-branding-image">
                                    @else
                                        <img src="" alt="{{ $companyNameValue }}" id="{{ $companyLogoPreviewId }}" class="crm-branding-image d-none">
                                    @endif
                                    <span id="{{ $companyLogoFallbackId }}" class="crm-branding-placeholder {{ $companyLogoUrl ? 'd-none' : '' }}">
                                        <span class="crm-branding-placeholder-icon" aria-hidden="true">
                                            <i class="bx bx-camera"></i>
                                            <span class="crm-avatar-upload-plus">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                        </span>
                                        <span class="crm-branding-placeholder-text">
                                            <strong>{{ $companyInitials !== '' ? $companyInitials : 'CP' }}</strong>
                                            <span>Click to upload and crop the company logo.</span>
                                        </span>
                                    </span>
                                </span>
                                <span class="crm-branding-caption">Click the logo area to {{ $companyLogoUrl ? 'replace' : 'add' }} the company logo.</span>
                            </label>

                            <input
                                id="{{ $companyLogoInputId }}"
                                class="d-none"
                                type="file"
                                accept="image/*"
                                data-cropper-input
                                data-cropper-title="Crop company logo"
                                data-cropper-note="The logo is saved as a square image for consistent presentation across the CRM shell."
                                data-cropper-hidden-target="{{ $companyLogoHiddenId }}"
                                data-cropper-preview-target="{{ $companyLogoPreviewId }}"
                                data-cropper-fallback-target="{{ $companyLogoFallbackId }}"
                            >
                            <input type="hidden" name="company_logo_cropped_image" id="{{ $companyLogoHiddenId }}" value="{{ old('company_logo_cropped_image') }}">
                        </div>

                        <div class="crm-branding-card">
                            <div class="crm-branding-card-head">
                                <h3>Login image</h3>
                                <p>Shown on the authentication screen to reinforce the CRM visual identity.</p>
                            </div>

                            <label for="{{ $loginImageInputId }}" class="crm-branding-trigger">
                                <span class="crm-branding-shell">
                                    @if ($loginImageUrl)
                                        <img src="{{ $loginImageUrl }}" alt="{{ $companyNameValue }} login image" id="{{ $loginImagePreviewId }}" class="crm-branding-image">
                                    @else
                                        <img src="" alt="{{ $companyNameValue }} login image" id="{{ $loginImagePreviewId }}" class="crm-branding-image d-none">
                                    @endif
                                    <span id="{{ $loginImageFallbackId }}" class="crm-branding-placeholder {{ $loginImageUrl ? 'd-none' : '' }}">
                                        <span class="crm-branding-placeholder-icon" aria-hidden="true">
                                            <i class="bx bx-image-add"></i>
                                            <span class="crm-avatar-upload-plus">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                        </span>
                                        <span class="crm-branding-placeholder-text">
                                            <strong>{{ $companyInitials !== '' ? $companyInitials : 'LG' }}</strong>
                                            <span>Click to upload and crop the login image.</span>
                                        </span>
                                    </span>
                                </span>
                                <span class="crm-branding-caption">Click the image area to {{ $loginImageUrl ? 'replace' : 'add' }} the login image.</span>
                            </label>

                            <input
                                id="{{ $loginImageInputId }}"
                                class="d-none"
                                type="file"
                                accept="image/*"
                                data-cropper-input
                                data-cropper-title="Crop login image"
                                data-cropper-note="The login image is saved as a square image for consistent presentation on the authentication screen."
                                data-cropper-hidden-target="{{ $loginImageHiddenId }}"
                                data-cropper-preview-target="{{ $loginImagePreviewId }}"
                                data-cropper-fallback-target="{{ $loginImageFallbackId }}"
                            >
                            <input type="hidden" name="login_image_cropped_image" id="{{ $loginImageHiddenId }}" value="{{ old('login_image_cropped_image') }}">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Save branding</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>

    @if ($isListSection)
        <div class="modal fade crm-settings-modal" id="crm-user-setting-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form
                        method="POST"
                        action="{{ $initialAction }}"
                        class="crm-form"
                        id="crm-user-setting-form"
                    >
                        @csrf
                        <input type="hidden" name="_method" id="crm-user-setting-method" value="{{ $initialMethod }}">
                        <input type="hidden" name="_settings_modal_mode" id="crm-user-setting-mode" value="{{ $autoOpenMode }}">
                        <input type="hidden" name="_settings_modal_section" id="crm-user-setting-section" value="{{ $activeSection }}">
                        <input type="hidden" name="_settings_modal_record_id" id="crm-user-setting-record-id" value="{{ $initialRecordId }}">

                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title" id="crm-user-setting-modal-title">
                                    {{ $hasEditContext ? 'Edit ' . $singularLabel : 'Add ' . $singularLabel }}
                                </h2>
                                <p class="crm-settings-modal-copy" id="crm-user-setting-modal-copy">
                                    {{ $hasEditContext ? 'Update this reusable value and the change will flow through the CRM immediately.' : 'Create a reusable value that staff profiles and directory filters can use immediately.' }}
                                </p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="crm-field-grid">
                                <div class="crm-field">
                                    <label for="crm-user-setting-name">Name</label>
                                    <input
                                        id="crm-user-setting-name"
                                        name="name"
                                        value="{{ $initialName }}"
                                        required
                                    >
                                </div>
                                <div class="crm-field">
                                    <label for="crm-user-setting-sort-order">Sort order</label>
                                    <input
                                        id="crm-user-setting-sort-order"
                                        name="sort_order"
                                        type="number"
                                        min="1"
                                        value="{{ $initialSortOrder }}"
                                    >
                                </div>
                                <div class="crm-field full">
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="crm-check">
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            id="crm-user-setting-active"
                                            @checked($initialIsActive)
                                        >
                                        <span>Active {{ $singularLabel }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">
                                <i class="bx bx-x"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary btn-loading" id="crm-user-setting-submit">
                                <span class="btn-text" id="crm-user-setting-submit-text">
                                    <i class="fas fa-save"></i> {{ $hasEditContext ? 'Save ' . $singularLabel : 'Add ' . $singularLabel }}
                                </span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($isListSection)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalElement = document.getElementById('crm-user-setting-modal');

                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                var modal = new bootstrap.Modal(modalElement);
                var form = document.getElementById('crm-user-setting-form');
                var methodInput = document.getElementById('crm-user-setting-method');
                var modeInput = document.getElementById('crm-user-setting-mode');
                var sectionInput = document.getElementById('crm-user-setting-section');
                var recordInput = document.getElementById('crm-user-setting-record-id');
                var title = document.getElementById('crm-user-setting-modal-title');
                var copy = document.getElementById('crm-user-setting-modal-copy');
                var submitText = document.getElementById('crm-user-setting-submit-text');
                var nameField = document.getElementById('crm-user-setting-name');
                var sortOrderField = document.getElementById('crm-user-setting-sort-order');
                var activeField = document.getElementById('crm-user-setting-active');
                var singularLabel = @json($singularLabel);
                var activeSection = @json($activeSection);
                var defaultSortOrder = @json($defaultSortOrder);
                var storeRoute = @json($storeRoute);
                var updateRouteTemplate = @json($updateRouteTemplate);
                var autoOpen = @json($shouldAutoOpenModal);
                var autoOpenMode = @json($autoOpenMode);
                var initialPayload = {
                    id: @json($initialRecordId),
                    name: @json($initialName),
                    sortOrder: @json($initialSortOrder),
                    isActive: @json($initialIsActive),
                };

                function submitLabel(isEdit) {
                    return (isEdit ? 'Save ' : 'Add ') + singularLabel;
                }

                function modalTitle(isEdit) {
                    return (isEdit ? 'Edit ' : 'Add ') + singularLabel;
                }

                function modalCopy(isEdit) {
                    return isEdit
                        ? 'Update this reusable value and the change will flow through the CRM immediately.'
                        : 'Create a reusable value that staff profiles and directory filters can use immediately.';
                }

                function updateAction(recordId) {
                    return updateRouteTemplate.replace('__ID__', String(recordId));
                }

                function configureModal(mode, payload, options) {
                    var isEdit = mode === 'edit';
                    var preserveState = options && options.preserveState;

                    form.action = isEdit && payload.id ? updateAction(payload.id) : storeRoute;
                    methodInput.value = isEdit ? 'PATCH' : 'POST';
                    modeInput.value = isEdit ? 'edit' : 'create';
                    sectionInput.value = activeSection;
                    recordInput.value = isEdit && payload.id ? String(payload.id) : '';
                    title.textContent = modalTitle(isEdit);
                    copy.textContent = modalCopy(isEdit);
                    submitText.innerHTML = '<i class="fas fa-save"></i> ' + submitLabel(isEdit);

                    if (preserveState) {
                        return;
                    }

                    nameField.value = payload.name || '';
                    sortOrderField.value = payload.sortOrder !== null && payload.sortOrder !== undefined && payload.sortOrder !== '' ? payload.sortOrder : defaultSortOrder;
                    activeField.checked = payload.isActive !== false;
                }

                document.querySelectorAll('[data-crm-user-setting-open="create"]').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        configureModal('create', {
                            id: null,
                            name: '',
                            sortOrder: defaultSortOrder,
                            isActive: true,
                        });
                        modal.show();
                    });
                });

                document.querySelectorAll('[data-crm-user-setting-open="edit"]').forEach(function (trigger) {
                    trigger.addEventListener('click', function (event) {
                        event.preventDefault();

                        configureModal('edit', {
                            id: trigger.getAttribute('data-id'),
                            name: trigger.getAttribute('data-name') || '',
                            sortOrder: trigger.getAttribute('data-sort-order') || '',
                            isActive: trigger.getAttribute('data-is-active') === '1',
                        });

                        modal.show();
                    });
                });

                modalElement.addEventListener('shown.bs.modal', function () {
                    nameField.focus();
                });

                modalElement.addEventListener('hidden.bs.modal', function () {
                    var currentUrl = new URL(window.location.href);

                    if (currentUrl.searchParams.has('edit')) {
                        currentUrl.searchParams.delete('edit');
                        window.history.replaceState({}, document.title, currentUrl.pathname + currentUrl.search);
                    }
                });

                if (autoOpen) {
                    configureModal(autoOpenMode, initialPayload, { preserveState: true });
                    modal.show();
                }
            });
        </script>
    @endif
@endpush
