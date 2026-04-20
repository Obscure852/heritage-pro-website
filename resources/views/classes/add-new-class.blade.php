@extends('layouts.master')
@section('title')
    New Class | Academic Management
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

        /* Section Title */
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-grid.single-column {
            grid-template-columns: 1fr;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-group label .required {
            color: #dc2626;
        }

        .form-group .form-control,
        .form-group .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .form-group .form-control:focus,
        .form-group .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-group .form-control::placeholder {
            color: #9ca3af;
        }

        /* Full Width Field */
        .full-width {
            grid-column: 1 / -1;
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

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-back,
            .btn-save {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('academic.index') }}">Classes</a>
        @endslot
        @slot('title')
            New Class
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="settings-container">
        <div class="settings-header">
            <h3><i class="fas fa-plus-circle me-2"></i>Create New Class</h3>
            <p>Add a new class to the academic system</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">Class Information</div>
                <div class="help-content">
                    Fill in the required details to create a new class. Fields marked with <span style="color: #dc2626;">*</span> are required.
                    You can assign a class teacher, select the term and grade, and optionally assign class monitors.
                </div>
            </div>

            <form class="needs-validation" method="post" action="{{ route('academic.store') }}" novalidate>
                @csrf

                <div class="section-title">Basic Information</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Class Name <span class="required">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="e.g., 1A, 2B, 3C">
                    </div>

                    <div class="form-group">
                        <label for="user">Class Teacher <span class="required">*</span></label>
                        <select name="user" id="user" class="form-select">
                            <option value="">Select Class Teacher...</option>
                            @if (!empty($teachers))
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->lastname . ' ' . $teacher->firstname }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="term">Term <span class="required">*</span></label>
                        <select name="term" id="term" class="form-select">
                            <option value="">Select Term...</option>
                            @if (!empty($terms))
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}">
                                        {{ 'Term ' . $term->term . ', ' . $term->year }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="grade">Grade <span class="required">*</span></label>
                        <select name="grade" id="grade" class="form-select">
                            <option value="">Select Grade...</option>
                            @if (!empty($grades))
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year">Academic Year <span class="required">*</span></label>
                        <select name="year" id="year" class="form-select">
                            <option value="">Select Year...</option>
                            @for ($year = date('Y') - 1; $year <= date('Y') + 3; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    @if ($schoolType->type === 'Senior')
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">Select Type...</option>
                                <option value="Triple Award">Triple Award</option>
                                <option value="Double Award">Double Award</option>
                                <option value="Single Award">Single Award</option>
                            </select>
                        </div>
                    @endif
                </div>

                <div class="section-title" style="margin-top: 24px;">Class Representatives (Optional)</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="monitor_id">Monitor</label>
                        <select name="monitor_id" id="monitor_id" class="form-select">
                            <option value="">Select Monitor...</option>
                            @if (!empty($maleStudents))
                                @foreach ($maleStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="monitress_id">Monitress</label>
                        <select name="monitress_id" id="monitress_id" class="form-select">
                            <option value="">Select Monitress...</option>
                            @if (!empty($femaleStudents))
                                @foreach ($femaleStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('academic.index') }}" class="btn-back">
                        <i class="bx bx-arrow-back"></i> Back to Classes
                    </a>
                    @if (!session('is_past_term'))
                        <button type="submit" class="btn-save">
                            <span class="btn-text"><i class="bx bx-save"></i> Create Class</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Creating...
                            </span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.needs-validation');

            form.addEventListener('submit', function(e) {
                const btn = form.querySelector('.btn-save');
                if (btn) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                }
            });
        });
    </script>
@endsection
