@extends('layouts.master')
@section('title', 'Assessment Module')

@section('css')
    <style>
        /* Main Container */
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .required::after {
            content: '*';
            color: #dc3545;
            margin-left: .25rem;
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assessment.test-list') }}">Back</a>
        @endslot
        @slot('title')
            Update {{ $test->name ?? '' }} Test
        @endslot
    @endcomponent

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

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="fas fa-edit me-2"></i>Edit Criteria-Based Test</h3>
                    <p>Update test configuration and details</p>
                </div>
                <div class="settings-body">
                    <form method="POST" action="{{ route('reception.update-criteria-test', $test->id) }}" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="term" value="{{ $currentTerm->id ?? 0 }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Sequence</label>
                                <select name="sequence" class="form-select form-select-sm" required>
                                    <option value="">Select…</option>
                                    @for ($i = 1; $i < 6; $i++)
                                        <option value="{{ $i }}" {{ $test->sequence == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Abbrev.</label>
                                <input type="text" name="abbrev" value="{{ old('abbrev', $test->abbrev) }}" class="form-control form-control-sm" placeholder="e.g. Aug" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">CA / Exam Name</label>
                                <input type="text" name="name" value="{{ old('name', $test->name) }}" class="form-control form-control-sm" placeholder="e.g. End‑of‑Term Test" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Subject</label>
                                <select name="subject" class="form-select form-select-sm" required>
                                    <option value="">Select Subject…</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ $test->grade_subject_id == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->grade->name }} | {{ $subject->subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Type</label>
                                <select name="type" class="form-select form-select-sm" required>
                                    <option value="">Select…</option>
                                    <option value="CA" {{ $test->type == 'CA' ? 'selected' : '' }}>CA</option>
                                    <option value="Exam" {{ $test->type == 'Exam' ? 'selected' : '' }}>Exam</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Include in Report Card</label>
                                <select name="assessment" class="form-select form-select-sm" required>
                                    <option value="">Select…</option>
                                    <option value="1" {{ $test->assessment == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ $test->assessment == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Grade</label>
                                <select name="grade_id" class="form-select form-select-sm" required>
                                    <option value="">Select Grade…</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}" {{ $grade->id == $test->grade_id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Start Date</label>
                                <input type="date" name="start_date" value="{{ old('start_date', $test->start_date) }}" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">End Date</label>
                                <input type="date" name="end_date" value="{{ old('end_date', $test->end_date) }}" class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('reception.criteria-tests') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            <button type="submit" class="btn-save">
                                <span class="btn-text"><i class="fas fa-save"></i> Update Test</span>
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
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form.needs-validation');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const btn = form.querySelector('.btn-save');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection
