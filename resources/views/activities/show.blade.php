@extends('layouts.master')

@section('title')
    Activity Details
@endsection

@section('css')
    @include('activities.partials.theme')
    <style>
        .activity-show-container {
            box-shadow: none;
        }

        .activity-show-container .stat-item {
            background: transparent;
        }

        .activity-show-body .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .activity-show-body .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .activity-show-body .btn-light {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .activity-show-body .btn-light:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .info-row {
            display: flex;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            width: 160px;
            flex-shrink: 0;
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .info-value {
            color: #111827;
            font-weight: 500;
            font-size: 14px;
        }

        .lifecycle-btn {
            padding: 8px 18px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .lifecycle-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .lifecycle-btn.btn-activate {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .lifecycle-btn.btn-activate:hover {
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .lifecycle-btn.btn-pause {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .lifecycle-btn.btn-pause:hover {
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .lifecycle-btn.btn-close-activity {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .lifecycle-btn.btn-close-activity:hover {
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .lifecycle-btn.btn-archive {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }

        .lifecycle-btn.btn-archive:hover {
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
            color: white;
        }

        .controls-list li {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            font-size: 13px;
            color: #4b5563;
        }

        .controls-list li i {
            font-size: 14px;
            width: 18px;
            text-align: center;
        }

        .controls-list .enabled {
            color: #059669;
        }

        .controls-list .disabled {
            color: #9ca3af;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #6b7280;
        }

        .meta-item i {
            font-size: 13px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            {{ $activity->name }}
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="activity-show-container">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 style="margin:0;">{{ $activity->name }}</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $activity->code }} | Term {{ $activity->term?->term ?? 'N/A' }} - {{ $activity->year }}
                    </p>
                </div>
                <div class="col-md-5">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $activity->active_staff_assignments_count }}</h4>
                                <small class="opacity-75">Staff</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $activity->enrollments_count }}</h4>
                                <small class="opacity-75">Students</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $activity->sessions_count }}</h4>
                                <small class="opacity-75">Sessions</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $activity->events_count }}</h4>
                                <small class="opacity-75">Events</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="activity-show-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <span class="status-badge status-{{ $activity->status }}">
                    {{ \App\Models\Activities\Activity::statuses()[$activity->status] ?? ucfirst($activity->status) }}
                </span>
                <div class="d-flex flex-wrap gap-2">
                    @can('manage-activities')
                        <a href="{{ route('activities.edit', $activity) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit Activity
                        </a>
                    @endcan
                </div>
            </div>

            @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'overview'])

            <div class="row g-4 mb-4">
                {{-- Left column: Activity details --}}
                <div class="col-lg-8">
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="section-title" style="margin-top:0;">Activity Information</h5>

                            <div class="info-row">
                                <div class="info-label">Category</div>
                                <div class="info-value">{{ \App\Models\Activities\Activity::categories()[$activity->category] ?? $activity->category }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Delivery Mode</div>
                                <div class="info-value">{{ \App\Models\Activities\Activity::deliveryModes()[$activity->delivery_mode] ?? $activity->delivery_mode }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Participation</div>
                                <div class="info-value">{{ \App\Models\Activities\Activity::participationModes()[$activity->participation_mode] ?? $activity->participation_mode }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Result Mode</div>
                                <div class="info-value">{{ \App\Models\Activities\Activity::resultModes()[$activity->result_mode] ?? $activity->result_mode }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Capacity</div>
                                <div class="info-value">{{ $activity->capacity ? $activity->capacity . ' students' : 'Unlimited' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Default Location</div>
                                <div class="info-value">{{ $activity->default_location ?: 'Not set' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Fee Type</div>
                                <div class="info-value">
                                    {{ $activity->feeType?->name ?: 'Not linked' }}
                                    @if ($activity->default_fee_amount)
                                        <span class="text-muted ms-1">(P {{ number_format((float) $activity->default_fee_amount, 2) }})</span>
                                    @endif
                                </div>
                            </div>

                            <h5 class="section-title">Description</h5>
                            <p class="mb-0 text-muted" style="font-size:14px; line-height:1.6;">
                                {{ $activity->description ?: 'No description provided yet.' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Right column: Controls & metadata --}}
                <div class="col-lg-4">
                    <div class="card-shell mb-4">
                        <div class="card-body p-4">
                            <h5 class="section-title" style="margin-top:0;">Controls</h5>
                            <ul class="list-unstyled mb-0 controls-list">
                                <li>
                                    @if ($activity->attendance_required)
                                        <i class="fas fa-check-circle enabled"></i>
                                        <span>Attendance required</span>
                                    @else
                                        <i class="fas fa-times-circle disabled"></i>
                                        <span class="text-muted">Attendance not required</span>
                                    @endif
                                </li>
                                <li>
                                    @if ($activity->allow_house_linkage)
                                        <i class="fas fa-check-circle enabled"></i>
                                        <span>House-linked reporting</span>
                                    @else
                                        <i class="fas fa-times-circle disabled"></i>
                                        <span class="text-muted">House-linked reporting off</span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="section-title" style="margin-top:0;">Record Info</h5>
                            <div class="d-flex flex-column gap-3">
                                <div class="meta-item">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Created by {{ $activity->creator?->full_name ?: 'Unknown' }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Created {{ $activity->created_at?->format('d M Y, H:i') }}</span>
                                </div>
                                @if ($activity->updater)
                                    <div class="meta-item">
                                        <i class="fas fa-user-edit"></i>
                                        <span>Updated by {{ $activity->updater->full_name }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-history"></i>
                                        <span>Updated {{ $activity->updated_at?->format('d M Y, H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <div class="management-header mb-0">
                                <div>
                                    <h5 class="summary-card-title mb-0">Activity Ownership</h5>
                                    <p class="management-subtitle">Lead and supporting staff assignments for this activity.</p>
                                </div>
                                @can('manageStaff', $activity)
                                    <a href="{{ route('activities.staff.index', $activity) }}" class="btn btn-light">
                                        <i class="fas fa-users-cog me-1"></i> Manage
                                    </a>
                                @endcan
                            </div>

                            @if ($staffSummary['primaryCoordinator'])
                                <div class="management-item mb-3">
                                    <div class="management-item-title">
                                        {{ $staffSummary['primaryCoordinator']->user?->full_name ?: 'Unknown staff member' }}
                                    </div>
                                    <div class="management-item-meta">
                                        <span class="summary-chip pill-primary">
                                            <i class="fas fa-star"></i> Primary Coordinator
                                        </span>
                                        <span class="summary-chip pill-muted">
                                            {{ \App\Models\Activities\ActivityStaffAssignment::roles()[$staffSummary['primaryCoordinator']->role] ?? ucfirst($staffSummary['primaryCoordinator']->role) }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <p class="summary-empty mb-3">No primary coordinator is assigned yet. Activation stays locked until one is set.</p>
                            @endif

                            @if ($staffSummary['activeAssignments']->isNotEmpty())
                                <div class="summary-chip-group">
                                    @foreach ($staffSummary['activeAssignments'] as $assignment)
                                        <span class="summary-chip">
                                            {{ $assignment->user?->full_name ?: 'Unknown staff member' }}
                                            <strong>{{ \App\Models\Activities\ActivityStaffAssignment::roles()[$assignment->role] ?? ucfirst($assignment->role) }}</strong>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="summary-empty mb-0">No active staff assignments are linked to this activity yet.</p>
                            @endif

                            @if ($staffSummary['historicalCount'] > 0)
                                <p class="field-help mb-0">Historical assignments retained: {{ $staffSummary['historicalCount'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <div class="management-header mb-0">
                                <div>
                                    <h5 class="summary-card-title mb-0">Eligibility Summary</h5>
                                    <p class="management-subtitle">Structured grade, class, house, and student-filter targets.</p>
                                </div>
                                @can('manageEligibility', $activity)
                                    <a href="{{ route('activities.eligibility.edit', $activity) }}" class="btn btn-light">
                                        <i class="fas fa-filter me-1"></i> Manage
                                    </a>
                                @endcan
                            </div>

                            <div class="section-stack">
                                @foreach ($eligibilitySummary as $group)
                                    <div>
                                        <div class="detail-label mb-2">{{ $group['label'] }}</div>
                                        @if (!empty($group['items']))
                                            <div class="summary-chip-group">
                                                @foreach ($group['items'] as $item)
                                                    <span class="summary-chip">{{ $item }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="summary-empty mb-0">No {{ strtolower($group['label']) }} targets selected.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lifecycle actions --}}
            @can('manage-activities')
                <div class="card-shell">
                    <div class="card-body p-4">
                        <h5 class="section-title" style="margin-top:0;">Lifecycle Actions</h5>
                        <div class="help-text" style="margin-bottom:16px;">
                            <div class="help-content">
                                Transition this activity through its lifecycle. Available actions depend on the current status.
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            @if ($activity->status === \App\Models\Activities\Activity::STATUS_DRAFT)
                                <form method="POST" action="{{ route('activities.activate', $activity) }}">
                                    @csrf
                                    <button type="submit" class="lifecycle-btn btn-activate">
                                        <i class="fas fa-play"></i> Activate
                                    </button>
                                </form>
                            @endif

                            @if ($activity->status === \App\Models\Activities\Activity::STATUS_ACTIVE)
                                <form method="POST" action="{{ route('activities.pause', $activity) }}">
                                    @csrf
                                    <button type="submit" class="lifecycle-btn btn-pause">
                                        <i class="fas fa-pause"></i> Pause
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('activities.close', $activity) }}">
                                    @csrf
                                    <button type="submit" class="lifecycle-btn btn-close-activity">
                                        <i class="fas fa-stop"></i> Close
                                    </button>
                                </form>
                            @endif

                            @if ($activity->status === \App\Models\Activities\Activity::STATUS_PAUSED)
                                <form method="POST" action="{{ route('activities.activate', $activity) }}">
                                    @csrf
                                    <button type="submit" class="lifecycle-btn btn-activate">
                                        <i class="fas fa-play"></i> Resume
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('activities.close', $activity) }}">
                                    @csrf
                                    <button type="submit" class="lifecycle-btn btn-close-activity">
                                        <i class="fas fa-stop"></i> Close
                                    </button>
                                </form>
                            @endif

                            @if (in_array($activity->status, [
                                \App\Models\Activities\Activity::STATUS_DRAFT,
                                \App\Models\Activities\Activity::STATUS_ACTIVE,
                                \App\Models\Activities\Activity::STATUS_PAUSED,
                                \App\Models\Activities\Activity::STATUS_CLOSED,
                            ], true))
                                <form method="POST" action="{{ route('activities.archive', $activity) }}">
                                    @csrf
                                    <button type="submit" class="lifecycle-btn btn-archive">
                                        <i class="fas fa-archive"></i> Archive
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
