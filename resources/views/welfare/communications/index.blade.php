@extends('layouts.master')

@section('title')
    Parent Communications
@endsection

@section('css')
    <style>
        /* Page Container */
        .communication-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .communication-header {
            background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .communication-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #a855f7;
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
            line-height: 1.5;
            margin: 0;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Input Group */
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            border-right: none;
            border-radius: 3px 0 0 3px !important;
            color: #6b7280;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 3px 3px 0 !important;
        }

        .input-group .form-control:focus {
            border-color: #3b82f6;
            box-shadow: none;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px !important;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
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
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('welfare.dashboard') }}">Welfare</a>
        @endslot
        @slot('title')
            Parent Communications
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

    <div class="communication-container">
        <div class="communication-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Parent Communications</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Track parent-school communications and interactions</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['this_week'] ?? 0 }}</h4>
                                <small class="opacity-75">This Week</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['pending_follow_up'] ?? 0 }}</h4>
                                <small class="opacity-75">Pending</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['meetings_scheduled'] ?? 0 }}</h4>
                                <small class="opacity-75">Meetings</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="communication-body">
            <div class="help-text">
                <div class="help-title">Communications Directory</div>
                <div class="help-content">
                    Browse and manage all parent communications. Use the search and filters to find specific communications.
                    Track follow-ups and meeting schedules.
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-9 col-md-12">
                    <form method="GET" action="{{ route('welfare.communications.index') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search student..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="welfare_update" {{ request('type') === 'welfare_update' ? 'selected' : '' }}>Welfare Update</option>
                                    <option value="concern" {{ request('type') === 'concern' ? 'selected' : '' }}>Concern</option>
                                    <option value="positive_feedback" {{ request('type') === 'positive_feedback' ? 'selected' : '' }}>Positive</option>
                                    <option value="meeting" {{ request('type') === 'meeting' ? 'selected' : '' }}>Meeting</option>
                                    <option value="incident_notification" {{ request('type') === 'incident_notification' ? 'selected' : '' }}>Incident</option>
                                    <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="method" class="form-select">
                                    <option value="">All Methods</option>
                                    <option value="phone" {{ request('method') === 'phone' ? 'selected' : '' }}>Phone</option>
                                    <option value="email" {{ request('method') === 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="in_person" {{ request('method') === 'in_person' ? 'selected' : '' }}>In Person</option>
                                    <option value="video_call" {{ request('method') === 'video_call' ? 'selected' : '' }}>Video Call</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="direction" class="form-select">
                                    <option value="">Direction</option>
                                    <option value="outbound" {{ request('direction') === 'outbound' ? 'selected' : '' }}>Outbound</option>
                                    <option value="inbound" {{ request('direction') === 'inbound' ? 'selected' : '' }}>Inbound</option>
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-6 col-sm-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-6 col-sm-6">
                                <a href="{{ route('welfare.communications.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-3 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('welfare.communications.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Record Communication
                    </a>
                </div>
            </div>

            <!-- Communications Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Parent</th>
                                    <th>Type</th>
                                    <th>Method</th>
                                    <th>Subject</th>
                                    <th>Direction</th>
                                    <th>Follow-up</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($communications as $communication)
                                    <tr>
                                        <td>{{ $communication->communication_date->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('welfare.communications.edit', $communication) }}">
                                                {{ $communication->student->full_name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $communication->parent_guardian_name }}</td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ ucfirst(str_replace('_', ' ', $communication->type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $methodIcons = [
                                                    'phone' => 'bx-phone',
                                                    'email' => 'bx-envelope',
                                                    'in_person' => 'bx-user',
                                                    'video_call' => 'bx-video',
                                                    'sms' => 'bx-message',
                                                    'letter' => 'bx-file',
                                                    'home_visit' => 'bx-home',
                                                ];
                                            @endphp
                                            <i class="bx {{ $methodIcons[$communication->method] ?? 'bx-message' }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $communication->method)) }}
                                        </td>
                                        <td>{{ Str::limit($communication->subject, 30) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $communication->direction === 'outbound' ? 'primary' : 'success' }}-subtle text-{{ $communication->direction === 'outbound' ? 'primary' : 'success' }}">
                                                <i class="fas fa-{{ $communication->direction === 'outbound' ? 'arrow-up' : 'arrow-down' }}"></i>
                                                {{ ucfirst($communication->direction) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($communication->follow_up_required)
                                                @if ($communication->follow_up_completed)
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="fas fa-check me-1"></i>Done
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                        @if ($communication->follow_up_date)
                                                            ({{ \Carbon\Carbon::parse($communication->follow_up_date)->format('d M') }})
                                                        @endif
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('welfare.communications.edit', $communication) }}"
                                                    class="btn btn-outline-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-comments" style="font-size: 48px; opacity: 0.5;"></i>
                                            <p class="mb-0 mt-2">No communications found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($communications->hasPages())
                        <div class="mt-3">
                            {{ $communications->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
