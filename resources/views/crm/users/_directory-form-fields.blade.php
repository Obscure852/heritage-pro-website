@php
    $formMode = $formMode ?? 'create';
    $avatarPreviewId = 'crm-user-avatar-preview-' . $formMode . '-' . ($user->id ?? 'new');
    $avatarFallbackId = 'crm-user-avatar-fallback-' . $formMode . '-' . ($user->id ?? 'new');
    $avatarInputId = 'crm-user-avatar-input-' . $formMode . '-' . ($user->id ?? 'new');
    $avatarHiddenId = 'crm-user-avatar-hidden-' . $formMode . '-' . ($user->id ?? 'new');
    $avatarUrl = old('avatar_cropped_image') ?: ($user->avatar_url ?? null);
    $initials = collect(preg_split('/\s+/', trim((string) old('name', $user->name ?? 'CRM User'))) ?: [])
        ->filter()
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
    $selectedFilterIds = collect(old('custom_filter_ids', isset($user) ? $user->customFilters->pluck('id')->all() : []))
        ->map(fn ($value) => (string) $value)
        ->all();
@endphp

<div class="crm-user-profile-layout">
    <div class="crm-user-profile-main">
        <div class="crm-field-grid">
            <div class="crm-field">
                <label for="name">Name <span class="text-danger">*</span></label>
                <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Enter full name" required>
            </div>
            <div class="crm-field">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" placeholder="Enter email address" required>
            </div>
            <div class="crm-field">
                <label for="phone">Phone <span class="text-danger">*</span></label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="Enter phone number" required>
            </div>
            <div class="crm-field">
                <label for="id_number">ID number <span class="text-danger">*</span></label>
                <input id="id_number" name="id_number" value="{{ old('id_number', $user->id_number ?? '') }}" placeholder="Enter staff ID number" required>
            </div>
            <div class="crm-field">
                <label for="date_of_birth">Date of birth <span class="text-danger">*</span></label>
                <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', optional($user->date_of_birth ?? null)?->format('Y-m-d')) }}" required>
            </div>
            <div class="crm-field">
                <label for="gender">Gender <span class="text-danger">*</span></label>
                <select id="gender" name="gender" required>
                    <option value="">Select gender</option>
                    @foreach ($genders as $value => $label)
                        <option value="{{ $value }}" @selected(old('gender', $user->gender ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-field">
                <label for="nationality">Nationality <span class="text-danger">*</span></label>
                <input id="nationality" name="nationality" value="{{ old('nationality', $user->nationality ?? '') }}" placeholder="Enter nationality" required>
            </div>
            <div class="crm-field">
                <label for="employment_status">Employment status <span class="text-danger">*</span></label>
                <select id="employment_status" name="employment_status" required>
                    <option value="">Select status</option>
                    @foreach ($employmentStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('employment_status', $user->employment_status ?? 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-field">
                <label for="department_id">Department <span class="text-danger">*</span></label>
                <select id="department_id" name="department_id" required>
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $user->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-field">
                <label for="position_id">Position <span class="text-danger">*</span></label>
                <select id="position_id" name="position_id" required>
                    <option value="">Select position</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" @selected((string) old('position_id', $user->position_id ?? '') === (string) $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-field">
                <label for="reports_to_user_id">Reporting to <span class="text-danger">*</span></label>
                <select id="reports_to_user_id" name="reports_to_user_id" required>
                    <option value="">Select manager</option>
                    @foreach ($reportingUsers as $manager)
                        <option value="{{ $manager->id }}" @selected((string) old('reports_to_user_id', $user->reports_to_user_id ?? '') === (string) $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-field">
                <label for="personal_payroll_number">Personal payroll number</label>
                <input id="personal_payroll_number" name="personal_payroll_number" value="{{ old('personal_payroll_number', $user->personal_payroll_number ?? '') }}" placeholder="Enter payroll number">
            </div>
            <div class="crm-field">
                <label for="date_of_appointment">Date of appointment <span class="text-danger">*</span></label>
                <input id="date_of_appointment" name="date_of_appointment" type="date" value="{{ old('date_of_appointment', optional($user->date_of_appointment ?? null)?->format('Y-m-d')) }}" required>
            </div>
            <div class="crm-field full">
                <label>Custom filters</label>
                @include('crm.users._custom-filter-pills', [
                    'filters' => $customFilters,
                    'selectedIds' => $selectedFilterIds,
                    'inputName' => 'custom_filter_ids[]',
                    'inputIdPrefix' => 'crm-user-custom-filter-' . $formMode,
                ])
                <span class="crm-muted-copy">Select every reusable staff tag that applies to this profile.</span>
            </div>

            @if ($formMode === 'create')
                <div class="crm-field">
                    <label for="role">Role <span class="text-danger">*</span></label>
                    <select id="role" name="role">
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role ?? 'rep') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-field">
                    <label>&nbsp;</label>
                    <label class="crm-check">
                        <input type="checkbox" name="active" value="1" @checked(old('active', $user->active ?? true))>
                        <span>Active login account</span>
                    </label>
                </div>
            @else
                <input type="hidden" name="role" value="{{ old('role', $user->role) }}">
                @if ($canAdminUsers ?? false)
                    <div class="crm-field">
                        <label>&nbsp;</label>
                        <label class="crm-check">
                            <input type="checkbox" name="active" value="1" @checked(old('active', $user->active))>
                            <span>Active login account</span>
                        </label>
                    </div>
                @else
                    <input type="hidden" name="active" value="{{ $user->active ? '1' : '0' }}">
                @endif
            @endif
        </div>
    </div>

    <aside class="crm-user-profile-side">
        <div class="crm-user-avatar-panel">
            <div class="crm-user-avatar-panel-copy">
                <p class="crm-kicker">Profile image</p>
                <h3>{{ $formMode === 'create' ? 'Add a staff photo' : 'Refresh the staff photo' }}</h3>
                <p class="crm-muted-copy">Choose an image, crop it in the square editor, then save the profile to publish the new photo everywhere in CRM.</p>
            </div>

            <div class="crm-user-avatar-picker">
                <label for="{{ $avatarInputId }}" class="crm-user-avatar-shell crm-user-avatar-shell-xl crm-user-avatar-trigger">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ old('name', $user->name ?? 'CRM user') }}" id="{{ $avatarPreviewId }}" class="crm-user-avatar-image">
                    @else
                        <img src="" alt="{{ old('name', $user->name ?? 'CRM user') }}" id="{{ $avatarPreviewId }}" class="crm-user-avatar-image d-none">
                    @endif
                    <span id="{{ $avatarFallbackId }}" class="crm-initial-avatar crm-initial-avatar-lg {{ $avatarUrl ? 'd-none' : '' }}">
                        <span class="crm-avatar-upload-icon" aria-hidden="true">
                            <i class="fas fa-camera"></i>
                            <span class="crm-avatar-upload-plus">
                                <i class="fas fa-plus"></i>
                            </span>
                        </span>
                        <span class="crm-avatar-upload-initials">{{ $initials !== '' ? $initials : 'CU' }}</span>
                    </span>
                </label>
                <span class="crm-muted-copy crm-user-avatar-hint">Click the image area to {{ $avatarUrl ? 'replace' : 'add' }} the staff photo.</span>
            </div>

            <div class="crm-user-avatar-tip-list">
                <div class="crm-user-avatar-tip">
                    <span class="crm-user-avatar-tip-icon"><i class="bx bx-crop"></i></span>
                    <div>
                        <strong>Square crop</strong>
                        <span>The editor keeps staff photos consistent across cards and summaries.</span>
                    </div>
                </div>
                <div class="crm-user-avatar-tip">
                    <span class="crm-user-avatar-tip-icon"><i class="bx bx-refresh"></i></span>
                    <div>
                        <strong>Easy replacement</strong>
                        <span>Select another file any time to reopen the cropper and update the preview.</span>
                    </div>
                </div>
            </div>

            <input
                id="{{ $avatarInputId }}"
                class="d-none"
                type="file"
                accept="image/*"
                data-cropper-input
                data-cropper-hidden-target="{{ $avatarHiddenId }}"
                data-cropper-preview-target="{{ $avatarPreviewId }}"
                data-cropper-fallback-target="{{ $avatarFallbackId }}"
            >
            <input type="hidden" name="avatar_cropped_image" id="{{ $avatarHiddenId }}" value="{{ old('avatar_cropped_image') }}">
        </div>
    </aside>
</div>
