@extends('layouts.master')
@section('title', 'Edit Final Grade Subject')
@section('css')
<style>
    .form-container {
        background: white;
        border-radius: 3px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .form-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .form-body {
        padding: 32px;
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

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .required {
        color: #dc2626;
        margin-left: 4px;
    }

    .form-control,
    .form-select {
        width: 100%;
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
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-light {
        background: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }

    .btn-light:hover {
        background: #e9ecef;
        color: #495057;
        transform: translateY(-1px);
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

    @media (max-width: 768px) {
        .form-header {
            padding: 16px;
        }

        .form-body {
            padding: 16px;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
@component('components.breadcrumb')
@slot('li_1') <a href="{{ route('finals.subjects.index') }}">Back</a> @endslot
@slot('title') Edit Final Grade Subject @endslot
@endcomponent

@if (session('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-all me-2"></i><strong>{{ session('message') }}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="mdi mdi-block-helper me-2"></i><strong>{{ session('error') }}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-block-helper me-2"></i><strong>{{ $error }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endforeach
@endif

<div class="form-container">
    <div class="form-header">
        <h3 style="margin:0;">Edit Final Grade Subject</h3>
        <p style="margin:6px 0 0 0; opacity:.9;">Update subject classification and department</p>
    </div>
    <div class="form-body">
        <div class="help-text">
            <div class="help-title">Subject Details</div>
            <div class="help-content">
                Update the subject classification and department assignment. Core subjects are required for all students, while optional subjects can be selected based on student preferences.
            </div>
        </div>

        <form action="{{ route('finals.subjects.update', $finalGradeSubject) }}" method="POST" id="editSubjectForm">
            @csrf
            @method('PUT')

            <div class="section-title">Subject Information</div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="department_id">Department</label>
                    <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $finalGradeSubject->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="type">Type<span class="required">*</span></label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="1" {{ $finalGradeSubject->type == 1 ? 'selected' : '' }}>Core</option>
                        <option value="0" {{ $finalGradeSubject->type == 0 ? 'selected' : '' }}>Optional</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="mandatory">Mandatory Subject</label>
                    <select name="mandatory" id="mandatory" class="form-select @error('mandatory') is-invalid @enderror">
                        <option value="1" {{ $finalGradeSubject->mandatory ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ !$finalGradeSubject->mandatory ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('finals.subjects.index') }}" class="btn btn-light">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Update Subject</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Updating...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editSubjectForm');

        form.addEventListener('submit', function(event) {
            if (form.checkValidity()) {
                const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            }
        });
    });
</script>
@endsection
