@extends('layouts.master')

@section('title')
    New Intervention Plan
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('welfare.intervention-plans.index') }}">Intervention Plans</a>
        @endslot
        @slot('title')
            New Plan
        @endslot
    @endcomponent

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-block-helper me-2"></i>{{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Create Intervention Plan</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('welfare.intervention-plans.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Student <span class="text-danger">*</span></label>
                                    <select name="student_id" id="student-select" class="form-control" required>
                                        <option value="">Select Student</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->full_name }} ({{ $student->admission_number ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Intervention Type <span class="text-danger">*</span></label>
                                    <select name="intervention_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="academic" {{ old('intervention_type') === 'academic' ? 'selected' : '' }}>Academic</option>
                                        <option value="behavioral" {{ old('intervention_type') === 'behavioral' ? 'selected' : '' }}>Behavioral</option>
                                        <option value="social" {{ old('intervention_type') === 'social' ? 'selected' : '' }}>Social</option>
                                        <option value="emotional" {{ old('intervention_type') === 'emotional' ? 'selected' : '' }}>Emotional</option>
                                        <option value="attendance" {{ old('intervention_type') === 'attendance' ? 'selected' : '' }}>Attendance</option>
                                        <option value="other" {{ old('intervention_type') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Plan Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required
                                placeholder="Brief title for the intervention plan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" required
                                placeholder="Describe the reason for this intervention and current situation...">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Goals <span class="text-danger">*</span></label>
                                    <textarea name="goals" class="form-control" rows="3" required
                                        placeholder="What are the expected outcomes/goals of this plan?">{{ old('goals') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Strategies <span class="text-danger">*</span></label>
                                    <textarea name="strategies" class="form-control" rows="3" required
                                        placeholder="What strategies will be used to achieve the goals?">{{ old('strategies') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Plan Coordinator <span class="text-danger">*</span></label>
                                    <select name="coordinator_id" id="coordinator-select" class="form-control" required>
                                        <option value="">Select Coordinator</option>
                                        @foreach ($coordinators as $coordinator)
                                            <option value="{{ $coordinator->id }}" {{ old('coordinator_id') == $coordinator->id ? 'selected' : '' }}>
                                                {{ $coordinator->full_name }} ({{ $coordinator->position ?? 'Staff' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Review Frequency <span class="text-danger">*</span></label>
                                    <select name="review_frequency" class="form-select" required>
                                        <option value="">Select Frequency</option>
                                        <option value="weekly" {{ old('review_frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="fortnightly" {{ old('review_frequency') === 'fortnightly' ? 'selected' : '' }}>Fortnightly</option>
                                        <option value="monthly" {{ old('review_frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="termly" {{ old('review_frequency') === 'termly' ? 'selected' : '' }}>Termly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ old('start_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Target End Date</label>
                                    <input type="date" name="target_end_date" class="form-control"
                                        value="{{ old('target_end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Link to Welfare Case</label>
                                    <select name="welfare_case_id" id="case-select" class="form-control">
                                        <option value="">None</option>
                                        @foreach ($cases as $case)
                                            <option value="{{ $case->id }}" {{ old('welfare_case_id') == $case->id ? 'selected' : '' }}>
                                                {{ $case->case_number }} - {{ $case->student->full_name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('welfare.intervention-plans.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#student-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select student'
            });

            new Choices('#coordinator-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select coordinator'
            });

            new Choices('#case-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Link to case'
            });
        });
    </script>
@endsection
