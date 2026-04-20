@extends('layouts.master')
@section('title')
    Staff Work History
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

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
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

        .form-group {
            margin-bottom: 16px;
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
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
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
            .form-container {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff.staff-view', $user->id) }}">{{ $user->full_name }}</a>
        @endslot
        @slot('title')
            New Work History
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

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Add Work History for {{ $user->full_name }}</h1>
        </div>

        <div class="help-text">
            <div class="help-title">New Work Experience</div>
            <div class="help-content">
                Add previous employment and work experience for this staff member. All fields marked with <span class="text-danger">*</span> are required.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('staff.store-work-history') }}" novalidate>
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <div class="form-group">
                <label class="form-label" for="workplace">Place of Work <span class="required">*</span></label>
                <input type="text" class="form-control @error('workplace') is-invalid @enderror"
                    name="workplace" id="workplace" placeholder="e.g., Debswana Diamond Company"
                    value="{{ old('workplace') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="type_of_work">Type of Work <span class="required">*</span></label>
                <select name="type_of_work" id="type_of_work" class="form-select @error('type_of_work') is-invalid @enderror" required>
                    <option value="">Select Type of Work</option>
                    <option value="Teaching" {{ old('type_of_work') == 'Teaching' ? 'selected' : '' }}>Teaching</option>
                    <option value="Non Teaching" {{ old('type_of_work') == 'Non Teaching' ? 'selected' : '' }}>Non Teaching</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Role <span class="required">*</span></label>
                <input type="text" class="form-control @error('role') is-invalid @enderror"
                    name="role" id="role" placeholder="e.g., Database Administrator"
                    value="{{ old('role') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="start">Start Date <span class="required">*</span></label>
                <input type="date" class="form-control @error('start') is-invalid @enderror"
                    name="start" id="start" value="{{ old('start') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="end">End Date <span class="required">*</span></label>
                <input type="date" class="form-control @error('end') is-invalid @enderror"
                    name="end" id="end" value="{{ old('end') }}" required>
            </div>

            <div class="form-actions">
                <a href="{{ route('staff.staff-view', $user->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text">
                        <i class="fas fa-save me-1"></i> Save Work History
                    </span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
