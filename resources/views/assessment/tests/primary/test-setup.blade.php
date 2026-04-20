@extends('layouts.master')
@section('title')
    Assessment Module
@endsection
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
            margin-left: 4px;
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
            Exam/Test Setup
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="fas fa-clipboard-list me-2"></i>Exam/Test Setup</h3>
                    <p>Create a new assessment test for primary school</p>
                </div>
                <div class="settings-body">
                    <form class="needs-validation" method="post" action="{{ route('assessment.store') }}" novalidate>
                        <input type="hidden" name="term" value="{{ $currentTerm->id ?? 0 }}">
                        <input type="hidden" name="year" value="{{ $currentTerm->year ?? 0 }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Exam/Test Sequence</label>
                                <select name="sequence" class="form-select" required>
                                    <option value="">Select Number ...</option>
                                    @for ($i = 1; $i < 6; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">CA/Exam Name</label>
                                <input type="text" name="name" placeholder="e.g. Term 1 End of Year"
                                    class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Abbreviation</label>
                                <input type="text" name="abbrev" placeholder="e.g. Aug"
                                    class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Class Subject</label>
                                <select name="subject" class="form-select" required>
                                    <option value="">Select Subject ...</option>
                                    @if (!empty($sortedSubjects))
                                        @foreach ($sortedSubjects as $subject)
                                            @if ($subject->subject)
                                                <option value="{{ $subject->id }}">
                                                    {{ $subject->grade->name . ' | ' . $subject->subject->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="">Select type ...</option>
                                    <option value="CA">CA</option>
                                    <option value="Exam">Exam</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Include in Report Card</label>
                                <select name="assessment" class="form-select" required>
                                    <option value="">Select Assessment ...</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Out Of</label>
                                <input type="number" name="out_of" placeholder="e.g. 100"
                                    class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Grade</label>
                                <select name="grade" class="form-select" required>
                                    <option value="">Select Grade ...</option>
                                    @if (!empty($grades))
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('assessment.test-list') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            <button type="submit" class="btn-save">
                                <span class="btn-text"><i class="fas fa-save"></i> Create Test</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Creating...
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
