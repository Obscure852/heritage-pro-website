@extends('layouts.master')
@section('title')
    Edit Book Allocation
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .form-section {
            margin-bottom: 28px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-control[readonly] {
            background-color: #f3f4f6;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
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
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.show', $student) }}">{{ $student->first_name }}'s Profile</a>
        @endslot
        @slot('title')
            Return Book
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
            <div class="form-container">
                <div class="page-header">
                    <h4 class="page-title text-muted">Return Book for {{ $student->fullName }}</h4>
                </div>

                <form id="bookForm" action="{{ route('students.update-allocation', $allocation->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="grade_id" value="{{ $student->currentClass()->grade_id }}">
                    <input type="hidden" name="allocation_id" value="{{ $allocation->id }}">

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Book Return</div>
                            <div class="help-content">Enter the return date and condition of the book.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="book_id" class="form-label">Book</label>
                                <input type="text" class="form-control" value="{{ $allocation->copy->book->title }}" readonly>
                                <input type="hidden" name="book_id" value="{{ $allocation->copy->book_id }}">
                            </div>
                            <div class="col-md-6">
                                <label for="accession_number" class="form-label">Accession Number</label>
                                <input type="text" name="accession_number" id="accession_number" class="form-control" value="{{ $allocation->copy->accession_number }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="allocation_date" class="form-label">Allocation Date</label>
                                <input type="date" name="allocation_date" id="allocation_date" class="form-control" value="{{ $allocation->allocation_date->format('Y-m-d') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due Date <span style="color:red;">*</span></label>
                                <input type="date" name="due_date" id="due_date" class="form-control" value="{{ $allocation->due_date->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="condition_on_allocation" class="form-label">Condition on Allocation</label>
                                <select name="condition_on_allocation" id="condition_on_allocation" class="form-select" {{ $allocation->return_date ? 'disabled' : '' }}>
                                    @foreach (['New', 'Good', 'Fair', 'Poor'] as $condition)
                                        <option value="{{ $condition }}" {{ $allocation->condition_on_allocation == $condition ? 'selected' : '' }}>
                                            {{ $condition }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="return_date" class="form-label">Return Date</label>
                                <input type="date" name="return_date" id="return_date" class="form-control" value="{{ $allocation->return_date?->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="condition_on_return" class="form-label">Condition on Return</label>
                                <select name="condition_on_return" id="condition_on_return" class="form-select">
                                    <option value="">Select Return Condition ...</option>
                                    @foreach (['Good', 'Fair', 'Poor', 'Damaged', 'Lost'] as $condition)
                                        <option value="{{ $condition }}" {{ $allocation->condition_on_return == $condition ? 'selected' : '' }}>
                                            {{ $condition }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ $allocation->notes }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-secondary" href="{{ route('students.show', $student) }}">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        @can('manage-students')
                            @if (!session('is_past_term'))
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            @endif
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/pristinejs/pristinejs.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/libs/choice-js/choice-js.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/pages/form-advanced.init.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var returnDateInput = document.getElementById('return_date');
            var conditionOnReturnSelect = document.getElementById('condition_on_return');

            returnDateInput.addEventListener('change', function() {
                if (this.value) {
                    conditionOnReturnSelect.setAttribute('required', 'required');
                } else {
                    conditionOnReturnSelect.removeAttribute('required');
                }
            });
            returnDateInput.dispatchEvent(new Event('change'));

            // Loading button animation
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn && form.checkValidity()) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });
        });
    </script>
@endsection
