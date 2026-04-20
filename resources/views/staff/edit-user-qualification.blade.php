@extends('layouts.master')
@section('title')
    Edit Qualification
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
            Edit Qualification
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
            <h1 class="page-title">Edit Qualification for {{ $user->full_name }}</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Update Qualification</div>
            <div class="help-content">
                Modify the qualification details below. All fields marked with <span class="text-danger">*</span> are required.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('staff.update-x-qualification', $qualification->id) }}" novalidate>
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <div class="form-group">
                <label class="form-label" for="college">University/College <span class="required">*</span></label>
                <input type="text" class="form-control @error('college') is-invalid @enderror"
                    name="college" id="college" placeholder="e.g., University of Botswana"
                    value="{{ old('college', $qualification->college ?? '') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="qualification_code">Qualification <span class="required">*</span></label>
                <select name="qualification_code" id="qualification_code" class="form-select @error('qualification_code') is-invalid @enderror" required>
                    <option value="">Select Qualification</option>
                    @foreach ($qualifications as $qual)
                        <option value="{{ $qual->id }}" {{ $qual->id == $qualification->qualification_id ? 'selected' : '' }}>
                            {{ $qual->qualification_code }} | {{ $qual->qualification }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="start_date">Start Date <span class="required">*</span></label>
                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                    name="start_date" id="start_date" value="{{ old('start_date', $qualification->start_date ?? '') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="completion_date">Completion Date <span class="required">*</span></label>
                <input type="date" class="form-control @error('completion_date') is-invalid @enderror"
                    name="completion_date" id="completion_date" value="{{ old('completion_date', $qualification->completion_date ?? '') }}" required>
            </div>

            <div class="form-actions">
                <a href="{{ route('staff.staff-view', $user->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text">
                        <i class="fas fa-save me-1"></i> Update Qualification
                    </span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Updating...
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
