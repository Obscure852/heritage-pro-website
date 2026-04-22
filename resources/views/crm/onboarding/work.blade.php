@extends('layouts.auth')

@section('title', 'Work Details')
@section('auth_hide_media', '1')
@section('auth_kicker', 'Step 2 of 2')
@section('auth_heading', 'Set your work profile')
@section('auth_copy', 'Finish the assignment details that control how you appear in reporting lines, staff lists, and internal CRM workflows.')
@section('auth_media_kicker', 'Final Step')
@section('auth_media_heading', 'Unlock full CRM access')
@section('auth_media_copy', 'Once these work details are saved, you will be taken into the CRM workspace with your profile and reporting structure ready to use.')

@section('auth_progress')
    <div class="auth-progress-track" aria-hidden="true">
        <span class="auth-progress-bar" style="width: 100%;"></span>
    </div>
    <div class="auth-progress-pills">
        <span class="auth-progress-step is-complete">1. Identity</span>
        <span class="auth-progress-step is-current">2. Work details</span>
    </div>
@endsection

@section('auth_helper')
    <div class="auth-helper">
        <strong>Review your placement carefully.</strong>
        <span>These fields affect team visibility, approvals, and how your account appears inside the CRM. You can still update them later if your assignment changes.</span>
    </div>
@endsection

@section('auth_content')
    <form method="POST" action="{{ route('crm.onboarding.work.update') }}" class="auth-form" id="crm-onboarding-work-form">
        @csrf
        @method('PATCH')

        <div class="auth-form-grid">
            <div class="auth-field">
                <label for="department_id">Department <span class="auth-required">*</span></label>
                <select id="department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $user->department_id) === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field">
                <label for="position_id">Position <span class="auth-required">*</span></label>
                <select id="position_id" name="position_id" class="form-select @error('position_id') is-invalid @enderror" required>
                    <option value="">Select position</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" @selected((string) old('position_id', $user->position_id) === (string) $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
                @error('position_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field">
                <label for="reports_to_user_id">Reporting manager <span class="auth-required">*</span></label>
                <select id="reports_to_user_id" name="reports_to_user_id" class="form-select @error('reports_to_user_id') is-invalid @enderror" required>
                    <option value="">Select manager</option>
                    @foreach ($reportingUsers as $manager)
                        <option value="{{ $manager->id }}" @selected((string) old('reports_to_user_id', $user->reports_to_user_id) === (string) $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
                @error('reports_to_user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field">
                <label for="employment_status">Employment status <span class="auth-required">*</span></label>
                <select id="employment_status" name="employment_status" class="form-select @error('employment_status') is-invalid @enderror" required>
                    <option value="">Select status</option>
                    @foreach ($employmentStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('employment_status', $user->employment_status ?: 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('employment_status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field">
                <label for="date_of_appointment">Date of appointment <span class="auth-required">*</span></label>
                <input id="date_of_appointment" name="date_of_appointment" type="date" class="form-control @error('date_of_appointment') is-invalid @enderror" value="{{ old('date_of_appointment', optional($user->date_of_appointment)->format('Y-m-d')) }}" required>
                @error('date_of_appointment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field">
                <label for="personal_payroll_number">Personal payroll number</label>
                <input id="personal_payroll_number" name="personal_payroll_number" class="form-control @error('personal_payroll_number') is-invalid @enderror" value="{{ old('personal_payroll_number', $user->personal_payroll_number) }}">
                @error('personal_payroll_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </form>

    <div class="auth-form-footer">
        <div class="auth-form-footer-copy">
            <a href="{{ route('crm.onboarding.profile') }}" class="auth-link">Back to identity details</a>
        </div>

        <div class="auth-form-footer-actions">
            @if ($canSkipWork)
                <form method="POST" action="{{ route('crm.onboarding.work.skip') }}">
                    @csrf
                    <button type="submit" class="auth-ghost-button">Skip</button>
                </form>
            @endif

            <button type="submit" form="crm-onboarding-work-form" class="auth-submit btn-loading">
                <span class="btn-text">
                    Finish setup
                    <svg class="auth-submit-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12H19" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12 5L19 12L12 19" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
            </button>
        </div>
    </div>
@endsection
