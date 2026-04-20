@extends('layouts.master')
@section('title')
    Link Grade Option | Academic Management
@endsection

@section('css')
    <style>
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

        /* Custom Tabs */
        .custom-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 12px;
        }

        .custom-tabs .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            color: #4b5563;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .custom-tabs .nav-link:hover {
            color: #3b82f6;
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .custom-tabs .nav-link.active {
            color: white;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: #3b82f6;
        }

        .custom-tabs .nav-link i {
            font-size: 16px;
        }

        /* Tab Content */
        .tab-content {
            padding: 0;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
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

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 20px;
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

        .btn-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-link:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-link.loading .btn-text {
            display: none;
        }

        .btn-link.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-link:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Section Title */
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3b82f6;
        }

        /* Table Styling */
        .link-table {
            width: 100%;
            border-collapse: collapse;
        }

        .link-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .link-table tbody td {
            padding: 12px 16px;
            color: #4b5563;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .link-table tbody tr:hover {
            background: #f9fafb;
        }

        .link-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badge Styles */
        .badge-option {
            background: #e0f2fe;
            color: #0284c7;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 4px;
        }

        .status-linked {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #10b981;
            font-size: 13px;
        }

        .status-linked i {
            font-size: 18px;
        }

        /* Action Buttons */
        .btn-unlink {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            background: #fee2e2;
            border: none;
            border-radius: 3px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-unlink:hover {
            background: #dc2626;
            color: white;
        }

        .btn-unlink.loading .btn-text {
            display: none;
        }

        .btn-unlink.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-unlink:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-edit-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #fef3c7;
            border: none;
            border-radius: 3px;
            color: #d97706;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-edit-option:hover {
            background: #d97706;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .custom-tabs {
                flex-direction: column;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-back,
            .btn-link {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('subjects.index') }}">Subjects</a>
        @endslot
        @slot('title')
            {{ $subject->subject->name ?? '' }}
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

    <div class="settings-container">
        <div class="settings-header">
            <h3><i class="fas fa-link me-2"></i>Link Grade Option</h3>
            <p>Manage grade option links for {{ $subject->subject->name ?? 'this subject' }}</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">Grade Option Linking</div>
                <div class="help-content">
                    Link grade option sets to subjects for assessments. You can view linked subjects and manage grade option
                    sets from the tabs below.
                </div>
            </div>

            <ul class="nav custom-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#home" role="tab">
                        <i class="bx bx-link text-white"></i> Link Grade Options
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#profile" role="tab">
                        <i class="bx bx-list-ul"></i> Grade Options
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="home" role="tabpanel">
                    <form action="{{ route('subject.link-to-subject-option') }}" method="POST">
                        @csrf
                        <input type="hidden" name="grade_subject_id" value="{{ $subject->id }}">

                        <div class="form-group">
                            <label for="grade_option">Select Grade Option</label>
                            <select name="grade_option_set_id" class="form-select" id="grade_option" data-trigger>
                                <option value="">Select Grade Option Set ...</option>
                                @foreach ($gradeOptionSets as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('subjects.index') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            @can('manage-academic')
                                @if (!session('is_past_term'))
                                    <button type="submit" class="btn-link">
                                        <span class="btn-text"><i class="bx bx-link"></i> Link</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Linking...
                                        </span>
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </form>

                    <h5 class="section-title">Linked Subjects</h5>

                    <div class="table-responsive">
                        <table class="link-table">
                            <thead>
                                <tr>
                                    <th>Grade Subject Name</th>
                                    <th>Grade Option Sets</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gradeSubjects as $subject)
                                    <tr>
                                        <td><strong>{{ $subject->subject->name }}</strong></td>
                                        <td>
                                            @foreach ($subject->gradeOptionSets as $set)
                                                <span class="badge-option">{{ $set->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach ($subject->gradeOptionSets as $set)
                                                <span class="status-linked">
                                                    <i class="bx bx-link"></i> Linked
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach ($subject->gradeOptionSets as $set)
                                                <form action="{{ route('subject.unlink-option-set') }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="grade_subject_id"
                                                        value="{{ $subject->id }}">
                                                    <input type="hidden" name="grade_option_set_id"
                                                        value="{{ $set->id }}">
                                                    @can('manage-academic')
                                                        @if (!session('is_past_term'))
                                                            <button type="submit" class="btn-unlink">
                                                                <span class="btn-text"><i class="bx bx-unlink"></i> Unlink</span>
                                                                <span class="btn-spinner d-none">
                                                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                                    Unlinking...
                                                                </span>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                </form>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane" id="profile" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="section-title mb-0" style="border: none; padding: 0;">Grade Option Sets</h5>
                        @can('manage-academic')
                            <a href="{{ route('subject.create-grade-option') }}"
                                style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border-radius: 3px; font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s;">
                                <i class="bx bx-plus"></i> New Option Set
                            </a>
                        @endcan
                    </div>

                    @if ($gradeOptionSets->isNotEmpty())
                        <div class="table-responsive">
                            <table class="link-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Grade Option Set Name</th>
                                        <th>Status</th>
                                        @can('manage-academic')
                                            <th style="width: 80px;">Action</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gradeOptionSets as $index => $optionSet)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $optionSet->name ?? '' }}</strong></td>
                                            <td>
                                                @php $linked = false; @endphp
                                                @foreach ($gradeSubjects as $subject)
                                                    @foreach ($subject->gradeOptionSets as $option)
                                                        @if ($option->id == $optionSet->id)
                                                            @php $linked = true; @endphp
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                                @if ($linked)
                                                    <span class="status-linked">
                                                        <i class="bx bx-link"></i> Linked
                                                    </span>
                                                @else
                                                    <span style="color: #6b7280;">Not in use</span>
                                                @endif
                                            </td>
                                            @can('manage-academic')
                                                <td>
                                                    <a class="btn-edit-option"
                                                        href="{{ route('subject.edit-grade-option', $optionSet->id) }}"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center" style="padding: 40px 20px;">
                            <i class="bx bx-slider-alt" style="font-size: 48px; color: #d1d5db;"></i>
                            <h6 class="mt-3" style="color: #374151; font-weight: 600;">No Option Sets Yet</h6>
                            <p style="color: #6b7280; font-size: 14px; max-width: 380px; margin: 8px auto 20px;">
                                Create an option set to define grading criteria (e.g. Beginning, Developing, Proficient) for component-based subjects.
                            </p>
                            @can('manage-academic')
                                <a href="{{ route('subject.create-grade-option') }}"
                                    style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border-radius: 3px; font-size: 14px; font-weight: 500; text-decoration: none;">
                                    <i class="bx bx-plus"></i> Create Option Set
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        var btn = form.querySelector('.btn-link, .btn-unlink');
                        if (btn) {
                            btn.classList.add('loading');
                            btn.disabled = true;
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
@endsection
