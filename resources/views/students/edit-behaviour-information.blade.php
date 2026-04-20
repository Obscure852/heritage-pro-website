@extends('layouts.master')
@section('title')
    Edit Behaviour Record
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
            <a class="text-muted font-size-14" href="{{ route('students.show', $student->id) }}">{{ $student->first_name }}'s Profile</a>
        @endslot
        @slot('title')
            Edit Behaviour Record
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
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="form-container">
                <div class="page-header">
                    <h4 class="page-title text-muted">Edit Incident for {{ $student->full_name }}</h4>
                </div>

                <form class="needs-validation" method="post" action="{{ route('students.update-student-behaviour', $behaviour->id) }}" novalidate>
                    @csrf

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Incident Details</div>
                            <div class="help-content">Update the behaviour incident details.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="date">Incident Date <span style="color:red;">*</span></label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" name="date" id="date" value="{{ old('date', $behaviour->date ?? '') }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="behaviour_type">Behaviour Type <span style="color:red;">*</span></label>
                                <select name="behaviour_type" id="behaviour_type" class="form-select @error('behaviour_type') is-invalid @enderror" required>
                                    <option value="">Select Behaviour Type ...</option>
                                    <option value="Tardiness" {{ old('behaviour_type', $behaviour->behaviour_type) == 'Tardiness' ? 'selected' : '' }}>Tardiness</option>
                                    <option value="Absence" {{ old('behaviour_type', $behaviour->behaviour_type) == 'Absence' ? 'selected' : '' }}>Absence</option>
                                    <option value="Fighting" {{ old('behaviour_type', $behaviour->behaviour_type) == 'Fighting' ? 'selected' : '' }}>Fighting</option>
                                    <option value="Drugs & Narcotics" {{ old('behaviour_type', $behaviour->behaviour_type) == 'Drugs & Narcotics' ? 'selected' : '' }}>Drugs & Narcotics</option>
                                    <option value="Bullying" {{ old('behaviour_type', $behaviour->behaviour_type) == 'Bullying' ? 'selected' : '' }}>Bullying</option>
                                    <option value="Dressing" {{ old('behaviour_type', $behaviour->behaviour_type) == 'Dressing' ? 'selected' : '' }}>Dressing</option>
                                    <option value="Misconduct" {{ old('behaviour_type', $behaviour->behaviour_type) == 'Misconduct' ? 'selected' : '' }}>Misconduct</option>
                                </select>
                                @error('behaviour_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Description <span style="color:red;">*</span></label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" required>{{ old('description', $behaviour->description ?? '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="action_taken">Action Taken <span style="color:red;">*</span></label>
                                <textarea name="action_taken" id="action_taken" class="form-control @error('action_taken') is-invalid @enderror" rows="3" required>{{ old('action_taken', $behaviour->action_taken ?? '') }}</textarea>
                                @error('action_taken')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="remarks">Remarks <span style="color:red;">*</span></label>
                                <textarea name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3" required>{{ old('remarks', $behaviour->remarks ?? '') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="reported_by">Reported By <span style="color:red;">*</span></label>
                                <input class="form-control @error('reported_by') is-invalid @enderror" type="text" value="{{ old('reported_by', $behaviour->reported_by ?? '') }}" name="reported_by" id="reported_by" required>
                                @error('reported_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-secondary" href="{{ route('students.show', $student->id) }}">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        @can('manage-students')
                            @if (!session('is_past_term'))
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Update</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Updating...
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
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#reported_by').autocomplete({
                source: '/search-names',
                minLength: 2,
            });

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
