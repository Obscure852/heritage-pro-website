@extends('layouts.master')

@section('title')
    Edit Case {{ $case->case_number }}
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <style>
        /* Page Container */
        .welfare-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .welfare-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .welfare-body {
            padding: 24px;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
            border-radius: 3px 3px 0 0 !important;
        }

        .card-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Elements */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .required-field::after {
            content: " *";
            color: #dc2626;
            margin-left: 4px;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
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
            color: white;
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

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #b02a37 0%, #8b1f2d 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
            color: white;
        }

        /* Loading Button */
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

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 3px !important;
            font-weight: 500;
            font-size: 12px;
        }

        .info-badge {
            background: #f3f4f6;
            padding: 8px 12px;
            border-radius: 3px;
            font-size: 13px;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        /* Table Styling */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Checkboxes */
        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-check-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
@endsection

@section('content')
    @php
        $priorityColors = [
            'low' => ['border' => '#6c757d', 'badge' => 'secondary'],
            'medium' => ['border' => '#0d6efd', 'badge' => 'primary'],
            'high' => ['border' => '#fd7e14', 'badge' => 'warning'],
            'critical' => ['border' => '#dc3545', 'badge' => 'danger'],
        ];

        $statusColors = [
            'open' => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary'],
            'in_progress' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning'],
            'pending_approval' => ['bg' => 'bg-info-subtle', 'text' => 'text-info'],
            'resolved' => ['bg' => 'bg-success-subtle', 'text' => 'text-success'],
            'closed' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary'],
            'escalated' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger'],
        ];

        $currentPriority = $priorityColors[$case->priority] ?? $priorityColors['medium'];
        $currentStatus = $statusColors[$case->status] ?? $statusColors['open'];
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('welfare.cases.index') }}">Back</a>
        @endslot
        @slot('title')
            Edit Case
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i>
                    <strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-outline label-icon"></i>
                    <strong>{{ session('warning') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle-outline label-icon"></i>
                    <strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-block-helper label-icon"></i>
                <strong>{{ $error }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    <!-- Case Summary Header -->
    <div class="welfare-container mb-3">
        <div class="welfare-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-1 text-white">Case #{{ $case->case_number }}</h4>
                    <p class="mb-0 opacity-75">{{ $case->title }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    @php
                        $statusBadgeMap = [
                            'open' => 'primary',
                            'in_progress' => 'warning',
                            'pending_approval' => 'info',
                            'resolved' => 'success',
                            'closed' => 'secondary',
                            'escalated' => 'danger',
                        ];
                        $statusBadge = $statusBadgeMap[$case->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusBadge }} fs-6 px-3 py-2">
                        {{ str_replace('_', ' ', ucfirst($case->status)) }}
                    </span>
                    <span class="badge bg-{{ $currentPriority['badge'] }} fs-6 px-3 py-2 ms-2">
                        {{ ucfirst($case->priority) }} Priority
                    </span>
                    @if ($case->requires_approval)
                        @if ($case->approval_status === 'pending')
                            <span class="badge bg-warning fs-6 px-3 py-2 ms-2">
                                Pending Approval
                            </span>
                        @elseif($case->approval_status === 'approved')
                            <span class="badge bg-success fs-6 px-3 py-2 ms-2">
                                Approved
                            </span>
                        @elseif($case->approval_status === 'rejected')
                            <span class="badge bg-danger fs-6 px-3 py-2 ms-2">
                                Rejected
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="welfare-body">
            <div class="d-flex flex-wrap gap-2">
                <div class="info-badge">
                    <i class="fas fa-user me-1"></i>
                    Student: {{ $case->student->full_name }}
                </div>
                <div class="info-badge">
                    <i class="fas fa-tag me-1"></i>
                    Type: {{ $case->welfareType->name ?? 'N/A' }}
                </div>
                <div class="info-badge">
                    <i class="fas fa-calendar me-1"></i>
                    Opened: {{ $case->opened_at->format('d M Y') }}
                </div>
                @if ($case->updated_at && $case->updated_at->ne($case->created_at))
                    <div class="info-badge">
                        <i class="fas fa-clock me-1"></i>
                        Updated: {{ $case->updated_at->format('d M Y') }}
                    </div>
                @endif
                @if ($case->assignedTo)
                    <div class="info-badge">
                        <i class="fas fa-user-check me-1"></i>
                        Assigned: {{ $case->assignedTo->full_name }}
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('welfare.cases.update', $case) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="case_number">Case Number</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $case->case_number }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="student">Student</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $case->student->full_name }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="title">Case Title</label>
                                    <input type="text" name="title"
                                        class="form-control form-control-sm @error('title') is-invalid @enderror"
                                        id="title" value="{{ old('title', $case->title) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="summary">Summary / Notes</label>
                                    <textarea name="summary" class="form-control form-control-sm" id="summary" rows="4"
                                        placeholder="Provide detailed information about the case, including background, current situation, and any relevant notes...">{{ old('summary', $case->summary) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <div class="d-flex gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="priority" value="low"
                                                id="priority-low" {{ $case->priority === 'low' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority-low">
                                                <span class="badge bg-secondary">Low</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="priority"
                                                value="medium" id="priority-medium"
                                                {{ $case->priority === 'medium' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority-medium">
                                                <span class="badge bg-primary">Medium</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="priority"
                                                value="high" id="priority-high"
                                                {{ $case->priority === 'high' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority-high">
                                                <span class="badge bg-warning">High</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="priority"
                                                value="critical" id="priority-critical"
                                                {{ $case->priority === 'critical' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority-critical">
                                                <span class="badge bg-danger">Critical</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                    @if ($case->isPendingApproval())
                                        @can('approve', $case)
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#approveModal">
                                                <i class="bx bx-check me-1"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#rejectModal">
                                                <i class="bx bx-x me-1"></i> Reject
                                            </button>
                                        @endcan
                                    @endif

                                    @can('assign', $case)
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#assignModal">
                                            <i class="bx bx-user-plus me-1"></i> Assign
                                        </button>
                                    @endcan

                                    @if ($case->status !== 'closed')
                                        @can('escalate', $case)
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#escalateModal">
                                                <i class="bx bx-up-arrow-alt me-1"></i> Escalate
                                            </button>
                                        @endcan

                                        @can('close', $case)
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#closeModal">
                                                <i class="bx bx-lock me-1"></i> Close
                                            </button>
                                        @endcan
                                    @else
                                        @can('reopen', $case)
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#reopenModal">
                                                <i class="bx bx-revision me-1"></i> Reopen
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="status">Status</label>
                                    <select name="status"
                                        class="form-select form-select-sm @error('status') is-invalid @enderror"
                                        id="status-select">
                                        <option value="open" {{ $case->status === 'open' ? 'selected' : '' }}>Open
                                        </option>
                                        <option value="in_progress"
                                            {{ $case->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="pending_approval"
                                            {{ $case->status === 'pending_approval' ? 'selected' : '' }}>Pending Approval
                                        </option>
                                        <option value="resolved" {{ $case->status === 'resolved' ? 'selected' : '' }}>
                                            Resolved</option>
                                        <option value="closed" {{ $case->status === 'closed' ? 'selected' : '' }}>Closed
                                        </option>
                                        <option value="escalated" {{ $case->status === 'escalated' ? 'selected' : '' }}>
                                            Escalated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="assigned_to">Assigned To</label>
                                    <select name="assigned_to" class="form-control form-control-sm" id="staff-select">
                                        <option value="">Unassigned</option>
                                        @foreach ($staff as $member)
                                            <option value="{{ $member->id }}"
                                                {{ $case->assigned_to == $member->id ? 'selected' : '' }}>
                                                {{ $member->full_name }} @if ($member->position)
                                                    - {{ $member->position }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('welfare.cases.index') }}" class="btn btn-secondary btn-sm me-2">
                                        <i class="bx bx-arrow-back"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save me-1"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('welfare.partials.case-modals')
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Choices.js for staff select
            new Choices('#staff-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select staff member',
                shouldSort: true
            });

            // Initialize Choices.js for status select
            new Choices('#status-select', {
                searchEnabled: false,
                itemSelectText: ''
            });

            // Handle priority checkboxes - ensure only one can be selected at a time
            document.querySelectorAll('input[name="priority"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        // Uncheck all other priority checkboxes
                        document.querySelectorAll('input[name="priority"]').forEach(cb => {
                            if (cb !== this) {
                                cb.checked = false;
                            }
                        });
                    }
                });
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
