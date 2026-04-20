@extends('layouts.master')
@section('title')
    New Textbook Allocation
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
            New Book Allocation
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
                    <h4 class="page-title text-muted">Allocate Book to {{ $student->fullName }}</h4>
                </div>

                <form id="bookForm" action="{{ route('students.allocate-book') }}" method="POST">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="grade_id" value="{{ $student->currentGrade->id }}">

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Book Details</div>
                            <div class="help-content">Select a book and enter the allocation details.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="book_id" class="form-label">Book <span style="color:red;">*</span></label>
                                <select name="book_id" id="book_id" data-trigger class="form-select" required>
                                    <option value="">Select Book</option>
                                    @foreach ($books as $book)
                                        <option value="{{ $book->id }}">
                                            {{ $book->title }} (Available)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="accession_number" class="form-label">Accession Number <span style="color:red;">*</span></label>
                                <input type="text" name="accession_number" id="accession_number" class="form-control" required>
                                <small class="text-muted">Override the accession number if needed</small>
                            </div>
                            <div class="col-md-6">
                                <label for="allocation_date" class="form-label">Allocation Date <span style="color:red;">*</span></label>
                                <input type="date" name="allocation_date" id="allocation_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due Date <span style="color:red;">*</span></label>
                                <input type="date" name="due_date" id="due_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="condition_on_allocation" class="form-label">Condition on Allocation</label>
                                <select name="condition_on_allocation" id="condition_on_allocation" class="form-select">
                                    <option value="">Select Condition on Allocation ...</option>
                                    @foreach (['New', 'Good', 'Fair', 'Poor'] as $condition)
                                        <option value="{{ $condition }}">{{ $condition }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-secondary" href="{{ route('students.show', $student->id) }}">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        @can('manage-students')
                            @if (!session('is_past_term'))
                                <button type="submit" name="save_and_new" value="1" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-plus"></i> Save & New</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            @endif
                        @endcan
                    </div>

                    @foreach ($books as $book)
                        <input type="hidden" id="accession_{{ $book->id }}" value="{{ $availableCopies[$book->id] ?? '' }}">
                    @endforeach
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var bookSelect = document.getElementById('book_id');
            var accessionInput = document.getElementById('accession_number');

            function updateAccessionNumber() {
                var selectedBookId = bookSelect.value;
                var accessionField = document.getElementById('accession_' + selectedBookId);
                if (accessionField) {
                    accessionInput.value = accessionField.value || '';
                } else {
                    accessionInput.value = '';
                }
            }

            bookSelect.addEventListener('change', updateAccessionNumber);
            if (bookSelect.value) {
                updateAccessionNumber();
            }

            var dueDateInput = document.getElementById('due_date');
            var defaultDueDate = new Date();
            defaultDueDate.setDate(defaultDueDate.getDate() + 14);
            dueDateInput.value = defaultDueDate.toISOString().split('T')[0];

            // Loading button animation
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = e.submitter;
                    if (submitBtn && submitBtn.classList.contains('btn-loading') && form.checkValidity()) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });
        });
    </script>
@endsection
