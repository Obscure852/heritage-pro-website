@extends('layouts.master')

@section('title')
    Intervention Plan
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <style>
        /* Intervention Header */
        .intervention-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            padding: 24px 28px;
            border-radius: 3px 3px 0 0;
        }

        .intervention-body {
            padding: 24px 28px;
            background: white;
            border-radius: 0 0 3px 3px;
        }

        .info-badge {
            background: #f3f4f6;
            padding: 8px 12px;
            border-radius: 3px;
            font-size: 13px;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .card-body {
            padding: 20px;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
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

        /* Tabs */
        .nav-tabs-custom {
            border-bottom: 2px solid #e5e7eb;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6b7280;
            padding: 12px 20px;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #3b82f6;
            border-bottom-color: #93c5fd;
        }

        .nav-tabs-custom .nav-link.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            font-weight: 600;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 3px !important;
            font-weight: 500;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    @php
        $statusColors = [
            'draft' => 'secondary',
            'active' => 'success',
            'on_hold' => 'warning',
            'completed' => 'primary',
        ];
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('welfare.intervention-plans.index') }}">Intervention Plans</a>
        @endslot
        @slot('title')
            Plan Details
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-block-helper me-2"></i>{{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    <!-- Plan Summary Header -->
    <div class="card mb-3">
        <div class="intervention-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-1 text-white">{{ $plan->title }}</h4>
                    <p class="mb-0 opacity-75">{{ ucfirst($plan->intervention_type) }} Intervention Plan</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-{{ $statusColors[$plan->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$plan->status] ?? 'secondary' }}">
                        {{ str_replace('_', ' ', ucfirst($plan->status)) }}
                    </span>
                    @if ($plan->parent_consent)
                        <span class="badge bg-success ms-2">Consent Given</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="intervention-body">
            <div class="d-flex flex-wrap gap-2">
                <div class="info-badge">
                    <i class="bx bx-user me-1"></i>
                    Student: {{ $plan->student->full_name ?? 'N/A' }}
                </div>
                <div class="info-badge">
                    <i class="bx bx-user-circle me-1"></i>
                    Coordinator: {{ $plan->coordinator->full_name ?? 'N/A' }}
                </div>
                @if ($plan->next_review_date)
                    <div class="info-badge {{ $plan->next_review_date->isPast() ? 'bg-danger text-white border-danger' : '' }}">
                        <i class="bx bx-calendar me-1"></i>
                        Next Review: {{ $plan->next_review_date->format('d M Y') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap gap-2">
                        @if ($plan->status === 'draft')
                            <form action="{{ route('welfare.intervention-plans.activate', $plan) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-play"></i> Activate Plan
                                </button>
                            </form>

                            <form action="{{ route('welfare.intervention-plans.destroy', $plan) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        @endif

                        @if ($plan->status === 'active')
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                                <i class="fas fa-clipboard"></i> Add Review
                            </button>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#holdModal">
                                <i class="fas fa-pause"></i> Put On Hold
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#completeModal">
                                <i class="fas fa-check-double"></i> Complete Plan
                            </button>
                        @endif

                        @if ($plan->status === 'on_hold')
                            <form action="{{ route('welfare.intervention-plans.resume', $plan) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-play"></i> Resume Plan
                                </button>
                            </form>
                        @endif

                        @if (!$plan->parent_consent && $plan->status !== 'completed')
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#consentModal">
                                <i class="fas fa-user-check"></i> Record Consent
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Plan Details -->
        <div class="col-md-8">
            <!-- Edit Form (for draft/active) -->
            @if (in_array($plan->status, ['draft', 'active']))
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Plan Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('welfare.intervention-plans.update', $plan) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Intervention Type</label>
                                        <select name="intervention_type" class="form-select" required>
                                            <option value="academic" {{ $plan->intervention_type === 'academic' ? 'selected' : '' }}>Academic</option>
                                            <option value="behavioral" {{ $plan->intervention_type === 'behavioral' ? 'selected' : '' }}>Behavioral</option>
                                            <option value="social" {{ $plan->intervention_type === 'social' ? 'selected' : '' }}>Social</option>
                                            <option value="emotional" {{ $plan->intervention_type === 'emotional' ? 'selected' : '' }}>Emotional</option>
                                            <option value="attendance" {{ $plan->intervention_type === 'attendance' ? 'selected' : '' }}>Attendance</option>
                                            <option value="other" {{ $plan->intervention_type === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Review Frequency</label>
                                        <select name="review_frequency" class="form-select" required>
                                            <option value="weekly" {{ $plan->review_frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="fortnightly" {{ $plan->review_frequency === 'fortnightly' ? 'selected' : '' }}>Fortnightly</option>
                                            <option value="monthly" {{ $plan->review_frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="termly" {{ $plan->review_frequency === 'termly' ? 'selected' : '' }}>Termly</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ $plan->title }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required>{{ $plan->description }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Goals</label>
                                        <textarea name="goals" class="form-control" rows="3" required>{{ $plan->goals }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Strategies</label>
                                        <textarea name="strategies" class="form-control" rows="3" required>{{ $plan->strategies }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Coordinator</label>
                                        <select name="coordinator_id" id="coordinator-select" class="form-control" required>
                                            @foreach ($coordinators as $coordinator)
                                                <option value="{{ $coordinator->id }}" {{ $plan->coordinator_id == $coordinator->id ? 'selected' : '' }}>
                                                    {{ $coordinator->full_name }} ({{ $coordinator->position ?? 'Staff' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Target End Date</label>
                                        <input type="date" name="target_end_date" class="form-control"
                                            value="{{ $plan->target_end_date ? $plan->target_end_date->format('Y-m-d') : '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('welfare.intervention-plans.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <!-- Read-only for completed plans -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Plan Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Description:</strong></p>
                        <p>{{ $plan->description }}</p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Goals:</strong></p>
                                <p>{{ $plan->goals }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Strategies:</strong></p>
                                <p>{{ $plan->strategies }}</p>
                            </div>
                        </div>
                        @if ($plan->outcome_summary)
                            <hr>
                            <p><strong>Outcome:</strong></p>
                            <p>{{ $plan->outcome_summary }}</p>
                            <p><strong>Goals Achieved:</strong> {{ $plan->goals_achieved ? 'Yes' : 'No' }}</p>
                        @endif
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('welfare.intervention-plans.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reviews Section -->
            @if ($plan->reviews && $plan->reviews->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Review History</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($plan->reviews->sortByDesc('review_date') as $review)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $review->review_date->format('d M Y') }}</strong>
                                        <span class="badge bg-{{ $review->progress_rating >= 4 ? 'success' : ($review->progress_rating >= 3 ? 'warning' : 'danger') }} ms-2">
                                            Progress: {{ $review->progress_rating }}/5
                                        </span>
                                    </div>
                                    <small class="text-muted">By {{ $review->reviewer->full_name ?? 'N/A' }}</small>
                                </div>
                                <p class="mb-1 mt-2">{{ $review->summary }}</p>
                                @if ($review->recommendations)
                                    <p class="mb-0 text-muted"><strong>Recommendations:</strong> {{ $review->recommendations }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column - Info Cards -->
        <div class="col-md-4">
            <!-- Plan Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Plan Information</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Start Date:</strong> {{ $plan->start_date ? $plan->start_date->format('d M Y') : 'Not set' }}</p>
                    <p class="mb-2"><strong>Target End:</strong> {{ $plan->target_end_date ? $plan->target_end_date->format('d M Y') : 'Not set' }}</p>
                    <p class="mb-2"><strong>Review Frequency:</strong> {{ ucfirst($plan->review_frequency) }}</p>
                    <p class="mb-2"><strong>Created By:</strong> {{ $plan->createdBy->full_name ?? 'N/A' }}</p>
                    @if ($plan->activated_at)
                        <p class="mb-2"><strong>Activated:</strong> {{ $plan->activated_at->format('d M Y') }}</p>
                    @endif
                    @if ($plan->completed_at)
                        <p class="mb-2"><strong>Completed:</strong> {{ $plan->completed_at->format('d M Y') }}</p>
                    @endif
                </div>
            </div>

            <!-- Consent Status -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Parent Consent</h6>
                </div>
                <div class="card-body">
                    @if ($plan->parent_consent)
                        <div class="alert alert-success mb-0">
                            <i class="bx bx-check-circle me-1"></i> Consent obtained on {{ $plan->consent_date ? $plan->consent_date->format('d M Y') : 'N/A' }}
                            @if ($plan->consent_notes)
                                <hr class="my-2">
                                <small>{{ $plan->consent_notes }}</small>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bx bx-time me-1"></i> Consent not yet obtained
                        </div>
                    @endif
                </div>
            </div>

            <!-- Hold Info -->
            @if ($plan->status === 'on_hold' && $plan->hold_reason)
                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title mb-0">On Hold</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $plan->hold_reason }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Review Modal -->
    <div class="modal fade" id="addReviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('welfare.intervention-plans.add-review', $plan) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Review Date <span class="text-danger">*</span></label>
                                    <input type="date" name="review_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Progress Rating <span class="text-danger">*</span></label>
                                    <select name="progress_rating" class="form-select" required>
                                        <option value="1">1 - No Progress</option>
                                        <option value="2">2 - Minimal Progress</option>
                                        <option value="3" selected>3 - Some Progress</option>
                                        <option value="4">4 - Good Progress</option>
                                        <option value="5">5 - Excellent Progress</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Summary <span class="text-danger">*</span></label>
                            <textarea name="summary" class="form-control" rows="3" required
                                placeholder="Summary of progress and observations..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Recommendations</label>
                            <textarea name="recommendations" class="form-control" rows="2"
                                placeholder="Any recommendations for next steps..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Next Review Date</label>
                            <input type="date" name="next_review_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Add Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hold Modal -->
    <div class="modal fade" id="holdModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('welfare.intervention-plans.hold', $plan) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Put Plan On Hold</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Reason for Hold <span class="text-danger">*</span></label>
                            <textarea name="hold_reason" class="form-control" rows="3" required
                                placeholder="Explain why the plan is being put on hold..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Put On Hold</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Modal -->
    <div class="modal fade" id="completeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('welfare.intervention-plans.complete', $plan) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Complete Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Outcome Summary <span class="text-danger">*</span></label>
                            <textarea name="outcome_summary" class="form-control" rows="3" required
                                placeholder="Summary of the intervention outcomes..."></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="goals_achieved" value="1" id="goalsAchieved">
                            <label class="form-check-label" for="goalsAchieved">
                                Goals were achieved
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Complete Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Consent Modal -->
    <div class="modal fade" id="consentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('welfare.intervention-plans.consent', $plan) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Record Parent Consent</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Consent Status <span class="text-danger">*</span></label>
                            <select name="consent_obtained" class="form-select" required>
                                <option value="1">Consent Obtained</option>
                                <option value="0">Consent Refused</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="consent_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="consent_notes" class="form-control" rows="2"
                                placeholder="Any notes about the consent..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Record Consent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const coordinatorSelect = document.getElementById('coordinator-select');
            if (coordinatorSelect) {
                new Choices(coordinatorSelect, {
                    searchEnabled: true,
                    itemSelectText: ''
                });
            }
        });
    </script>
@endsection
