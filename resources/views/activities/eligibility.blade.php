@extends('layouts.master')

@section('title')
    Activity Eligibility
@endsection

@section('css')
    @include('activities.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            {{ $activity->name }} Eligibility
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Eligibility Rules</h1>
                <p class="page-subtitle">Define structured grade, class, house, and student-filter targets for bulk roster preparation.</p>
            </div>
        </div>

        @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'eligibility'])

        <div class="help-text">
            <div class="help-title">Structured Targeting</div>
            <div class="help-content">
                Eligibility targets support safe bulk rostering later. If all sections are left empty, the activity remains manually managed for staff-selected students only.
            </div>
        </div>

        <div class="management-grid">
            <div class="card-shell">
                <div class="card-body p-4">
                    <h5 class="summary-card-title">Current Eligibility Configuration</h5>
                    <div class="section-stack">
                        <div>
                            <div class="detail-label mb-2">Grades</div>
                            @if (!empty($selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_GRADE]))
                                <div class="summary-chip-group">
                                    @foreach ($grades->whereIn('id', $selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_GRADE]) as $grade)
                                        <span class="summary-chip">{{ $grade->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="summary-empty mb-0">No grade targets selected.</p>
                            @endif
                        </div>

                        <div>
                            <div class="detail-label mb-2">Classes</div>
                            @if (!empty($selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_CLASS]))
                                <div class="summary-chip-group">
                                    @foreach ($klasses->whereIn('id', $selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_CLASS]) as $klass)
                                        <span class="summary-chip">{{ $klass->name }}{{ $klass->grade ? ' (' . $klass->grade->name . ')' : '' }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="summary-empty mb-0">No class targets selected.</p>
                            @endif
                        </div>

                        <div>
                            <div class="detail-label mb-2">Houses</div>
                            @if (!empty($selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_HOUSE]))
                                <div class="summary-chip-group">
                                    @foreach ($houses->whereIn('id', $selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_HOUSE]) as $house)
                                        <span class="summary-chip">{{ $house->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="summary-empty mb-0">No house targets selected.</p>
                            @endif
                        </div>

                        <div>
                            <div class="detail-label mb-2">Student Filters</div>
                            @if (!empty($selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_STUDENT_FILTER]))
                                <div class="summary-chip-group">
                                    @foreach ($studentFilters->whereIn('id', $selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_STUDENT_FILTER]) as $filter)
                                        <span class="summary-chip">{{ $filter->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="summary-empty mb-0">No student-filter targets selected.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-body p-4">
                    <h5 class="summary-card-title">Update Eligibility Targets</h5>

                    <form action="{{ route('activities.eligibility.update', $activity) }}" method="POST" id="activity-eligibility-form" class="needs-validation" novalidate data-activity-form>
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label class="form-label" for="grades">Grades</label>
                            <select class="form-select multi-select-shell @error('grades.*') is-invalid @enderror"
                                id="grades"
                                name="grades[]"
                                multiple>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}"
                                        {{ in_array($grade->id, old('grades', $selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_GRADE]), true) ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="field-help">Hold Command or Ctrl to select multiple grades.</div>
                            @error('grades.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="klasses">Classes</label>
                            <select class="form-select multi-select-shell @error('klasses.*') is-invalid @enderror"
                                id="klasses"
                                name="klasses[]"
                                multiple>
                                @foreach ($klasses as $klass)
                                    <option value="{{ $klass->id }}"
                                        {{ in_array($klass->id, old('klasses', $selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_CLASS]), true) ? 'selected' : '' }}>
                                        {{ $klass->name }}{{ $klass->grade ? ' (' . $klass->grade->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="field-help">Use class targets when the activity is restricted to a specific stream or section.</div>
                            @error('klasses.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="houses">Houses</label>
                            <select class="form-select multi-select-shell @error('houses.*') is-invalid @enderror"
                                id="houses"
                                name="houses[]"
                                multiple>
                                @foreach ($houses as $house)
                                    <option value="{{ $house->id }}"
                                        {{ in_array($house->id, old('houses', $selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_HOUSE]), true) ? 'selected' : '' }}>
                                        {{ $house->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="field-help">House targeting references existing house records only and never edits house membership.</div>
                            @error('houses.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="student_filters">Student Filters</label>
                            <select class="form-select multi-select-shell @error('student_filters.*') is-invalid @enderror"
                                id="student_filters"
                                name="student_filters[]"
                                multiple>
                                @foreach ($studentFilters as $filter)
                                    <option value="{{ $filter->id }}"
                                        {{ in_array($filter->id, old('student_filters', $selectedTargets[\App\Models\Activities\ActivityEligibilityTarget::TARGET_STUDENT_FILTER]), true) ? 'selected' : '' }}>
                                        {{ $filter->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="field-help">Use student filters for boarding/day, sponsor-related, or other configured student cohorts.</div>
                            @error('student_filters.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('activities.show', $activity) }}" class="btn btn-secondary">
                                <i class="bx bx-x"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Eligibility</span>
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
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
@endsection
