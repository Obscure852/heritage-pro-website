@extends('layouts.master')

@section('title')
    Safeguarding Concern #{{ $concern->id }}
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <style>
        /* Safeguarding Container */
        .safeguarding-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .safeguarding-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .safeguarding-body {
            padding: 24px;
        }

        /* Info Badge on Colored Background */
        .info-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 3px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .info-badge strong {
            color: white;
            display: block;
            font-size: 13px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .info-badge span {
            color: white;
            font-size: 20px;
            font-weight: 600;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        /* Form Elements */
        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .required-field::after {
            content: " *";
            color: red;
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }

        /* Nav Tabs */
        .nav-tabs .nav-link {
            border-radius: 3px 3px 0 0 !important;
        }

        .nav-tabs .nav-link.active {
            color: #ef4444;
            border-color: #dee2e6 #dee2e6 #fff;
        }
    </style>
@endsection

@section('content')
    @php
        $riskColors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
        ];
        $riskColor = $riskColors[$concern->risk_level] ?? 'secondary';

        $statusColors = [
            'identified' => 'info',
            'investigating' => 'warning',
            'referred' => 'primary',
            'monitoring' => 'secondary',
            'closed' => 'success',
        ];
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.safeguarding.index') }}">Back</a>
        @endslot
        @slot('title')
            Concern #{{ $concern->id }}
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

    <!-- Header -->
    <div class="safeguarding-container mb-3">
        <div class="safeguarding-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">{{ $concern->student->full_name ?? 'Unknown Student' }}</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        Concern #{{ $concern->id }} •
                        {{ $concern->date_identified ? \Carbon\Carbon::parse($concern->date_identified)->format('d M Y') : 'N/A' }}
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="info-badge text-center">
                                <strong>Risk</strong>
                                <span class="d-block">{{ ucfirst($concern->risk_level) }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-badge text-center">
                                <strong>Status</strong>
                                <span class="d-block">{{ ucfirst($concern->status) }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-badge text-center">
                                <strong><i class="fas fa-lock"></i></strong>
                                <span class="d-block" style="font-size:14px;">Level 4</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                                type="button" role="tab" aria-controls="details" aria-selected="true">
                                Concern Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="immediate-action-tab" data-bs-toggle="tab"
                                data-bs-target="#immediate-action" type="button" role="tab"
                                aria-controls="immediate-action" aria-selected="false">
                                Record Immediate Action
                                @if ($concern->immediate_action_taken)
                                    <span class="badge bg-success ms-2">Done</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="authority-notification-tab" data-bs-toggle="tab"
                                data-bs-target="#authority-notification" type="button" role="tab"
                                aria-controls="authority-notification" aria-selected="false">
                                Authority Notification
                                @if ($concern->authorities_notified)
                                    <span class="badge bg-success ms-2">Done</span>
                                @elseif ($concern->category?->notify_authorities)
                                    <span class="badge bg-warning ms-2">Required</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="parent-notification-tab" data-bs-toggle="tab"
                                data-bs-target="#parent-notification" type="button" role="tab"
                                aria-controls="parent-notification" aria-selected="false">
                                Parent Notification
                                @if ($concern->parents_informed)
                                    <span class="badge bg-success ms-2">Done</span>
                                @else
                                    <span class="badge bg-warning ms-2">Pending</span>
                                @endif
                            </button>
                        </li>
                        @if ($concern->status !== 'closed')
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="close-concern-tab" data-bs-toggle="tab"
                                    data-bs-target="#close-concern" type="button" role="tab"
                                    aria-controls="close-concern" aria-selected="false">
                                    Close Concern
                                </button>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="details" role="tabpanel"
                            aria-labelledby="details-tab">
                            <form action="{{ route('welfare.safeguarding.update', $concern) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="student">Student</label>
                                            <input type="text" class="form-control form-control" id="student"
                                                value="{{ $concern->student->full_name ?? '-' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="date_identified">Date of Concern</label>
                                            <input type="text" class="form-control form-control"
                                                id="date_identified"
                                                value="{{ $concern->date_identified ? \Carbon\Carbon::parse($concern->date_identified)->format('d M Y') : 'N/A' }}"
                                                disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="category_id">Category</label>
                                            <select name="category_id" class="form-select form-select"
                                                id="category_id" required>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ $concern->category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="risk_level">Risk Level</label>
                                            <select name="risk_level" class="form-select form-select" id="risk_level"
                                                required>
                                                <option value="low"
                                                    {{ $concern->risk_level === 'low' ? 'selected' : '' }}>
                                                    Low</option>
                                                <option value="medium"
                                                    {{ $concern->risk_level === 'medium' ? 'selected' : '' }}>Medium
                                                </option>
                                                <option value="high"
                                                    {{ $concern->risk_level === 'high' ? 'selected' : '' }}>High</option>
                                                <option value="critical"
                                                    {{ $concern->risk_level === 'critical' ? 'selected' : '' }}>Critical
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="status">Status</label>
                                            <select name="status" class="form-select form-select" required>
                                                <option value="identified"
                                                    {{ $concern->status === 'identified' ? 'selected' : '' }}>Identified
                                                </option>
                                                <option value="investigating"
                                                    {{ $concern->status === 'investigating' ? 'selected' : '' }}>
                                                    Investigating</option>
                                                <option value="referred"
                                                    {{ $concern->status === 'referred' ? 'selected' : '' }}>Referred
                                                </option>
                                                <option value="monitoring"
                                                    {{ $concern->status === 'monitoring' ? 'selected' : '' }}>Monitoring
                                                </option>
                                                <option value="closed"
                                                    {{ $concern->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="source_of_concern">Source of
                                                Concern</label>
                                            <select name="source_of_concern" class="form-select form-select"
                                                id="source_of_concern" required>
                                                <option value="student_disclosure"
                                                    {{ $concern->source_of_concern === 'student_disclosure' ? 'selected' : '' }}>
                                                    Student Disclosure</option>
                                                <option value="staff_observation"
                                                    {{ $concern->source_of_concern === 'staff_observation' ? 'selected' : '' }}>
                                                    Staff Observation</option>
                                                <option value="parent_report"
                                                    {{ $concern->source_of_concern === 'parent_report' ? 'selected' : '' }}>
                                                    Parent Report</option>
                                                <option value="peer_report"
                                                    {{ $concern->source_of_concern === 'peer_report' ? 'selected' : '' }}>
                                                    Peer Report</option>
                                                <option value="external_referral"
                                                    {{ $concern->source_of_concern === 'external_referral' ? 'selected' : '' }}>
                                                    External Referral</option>
                                                <option value="anonymous"
                                                    {{ $concern->source_of_concern === 'anonymous' ? 'selected' : '' }}>
                                                    Anonymous</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="concern_details">Concern
                                                Details</label>
                                            <textarea name="concern_details" class="form-control form-control" id="concern_details" rows="5" required>{{ old('concern_details', $concern->concern_details) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="indicators_observed">Indicators
                                                Observed</label>
                                            <textarea name="indicators_observed" class="form-control form-control" id="indicators_observed" rows="3"
                                                placeholder="Physical signs, behavioral changes, disclosures...">{{ old('indicators_observed', $concern->indicators_observed) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                @if ($concern->welfareCase)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-link me-2"></i>
                                                <strong>Linked Case:</strong>
                                                <a href="{{ route('welfare.cases.edit', $concern->welfareCase) }}"
                                                    class="alert-link">
                                                    {{ $concern->welfareCase->case_number }} -
                                                    {{ $concern->welfareCase->title }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('welfare.safeguarding.index') }}"
                                                class="btn-secondary btn me-2">
                                                <i class="fas fa-arrow-left"></i> Back
                                            </a>
                                            <button type="submit" class="btn-primary btn">
                                                <i class="fas fa-save font-size-16 align-middle me-2"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="immediate-action" role="tabpanel"
                            aria-labelledby="immediate-action-tab">
                            @if (!$concern->immediate_action_taken)
                                <form action="{{ route('welfare.safeguarding.immediate-action', $concern) }}"
                                    method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label required-field">Describe the action taken</label>
                                        <textarea name="immediate_action_details" class="form-control form-control" rows="4"
                                            placeholder="What steps were taken to ensure the child's immediate safety?" required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn-warning btn">
                                            <i class="fas fa-check me-1"></i> Record Immediate Action
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="mb-2">Immediate Action Recorded</h6>
                                    <p class="mb-2">{{ $concern->immediate_action_details }}</p>
                                    <small class="text-muted">
                                        Recorded {{ $concern->updated_at->diffForHumans() }}
                                    </small>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="authority-notification" role="tabpanel"
                            aria-labelledby="authority-notification-tab">
                            @if (!$concern->authorities_notified)
                                @if ($concern->category?->notify_authorities)
                                    <div class="alert alert-danger mb-3">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        This category requires notification to authorities.
                                    </div>
                                @endif
                                <form action="{{ route('welfare.safeguarding.notify-authorities', $concern) }}"
                                    method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label required-field">Authority Reference Number</label>
                                        <input type="text" name="authority_reference"
                                            class="form-control form-control" placeholder="e.g., MASH-2024-12345"
                                            required>
                                        <small class="text-muted">Enter the reference provided by the authority</small>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn-danger btn">
                                            <i class="fas fa-share me-1"></i> Record Authority Notification
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-shield-alt text-success font-size-48"></i>
                                    <h6 class="mt-2">Authorities Notified</h6>
                                    <p class="text-muted mb-2">
                                        Reference: <strong>{{ $concern->authority_reference }}</strong>
                                    </p>
                                    <small class="text-muted">
                                        {{ $concern->authorities_notified_at ? \Carbon\Carbon::parse($concern->authorities_notified_at)->format('d M Y \a\t H:i') : '' }}
                                    </small>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="parent-notification" role="tabpanel"
                            aria-labelledby="parent-notification-tab">
                            @if (!$concern->parents_informed)
                                <form action="{{ route('welfare.safeguarding.notify-parents', $concern) }}"
                                    method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Parent Response</label>
                                        <textarea name="parent_response" class="form-control form-control" rows="3"
                                            placeholder="Document the parent's response (optional)"></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn-primary btn">
                                            <i class="fas fa-user-check me-1"></i> Record Parent Notification
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-user-check text-success font-size-48"></i>
                                    <h6 class="mt-2">Parents Informed</h6>
                                    <small class="text-muted d-block mb-2">
                                        {{ $concern->parents_informed_at ? \Carbon\Carbon::parse($concern->parents_informed_at)->format('d M Y \a\t H:i') : '' }}
                                    </small>
                                    @if ($concern->parent_response)
                                        <div class="border rounded p-2 bg-light text-start mt-2">
                                            <strong>Response:</strong>
                                            <p class="mb-0 mt-1">{{ $concern->parent_response }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($concern->status !== 'closed')
                            <div class="tab-pane fade" id="close-concern" role="tabpanel"
                                aria-labelledby="close-concern-tab">
                                <form action="{{ route('welfare.safeguarding.close', $concern) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label required-field">Outcome Summary</label>
                                        <textarea name="outcome" class="form-control form-control" rows="4"
                                            placeholder="Summarize the outcome and any ongoing monitoring arrangements..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn-secondary btn"
                                            onclick="return confirm('Are you sure you want to close this concern?')">
                                            <i class="fas fa-check-circle me-1"></i> Close Concern
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($concern->status === 'closed')
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-check-circle text-success font-size-48"></i>
                        <h6 class="mt-2">Concern Closed</h6>
                        <p class="text-muted mb-0">
                            Closed by {{ $concern->closedBy->full_name ?? 'Unknown' }}<br>
                            {{ $concern->closed_at ? \Carbon\Carbon::parse($concern->closed_at)->format('d M Y \a\t H:i') : '' }}
                        </p>
                        @if ($concern->outcome)
                            <div class="border rounded p-3 bg-light text-start mt-3">
                                <strong>Outcome:</strong>
                                <p class="mb-0 mt-1">{{ $concern->outcome }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
@endsection
