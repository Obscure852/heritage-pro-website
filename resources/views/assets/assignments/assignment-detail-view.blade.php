@extends('layouts.master')
@section('title', 'Assignment Details')

@section('css')
    <style>
        .assignment-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .assignment-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .assignment-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .assignment-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .assignment-body {
            padding: 24px;
        }

        .status-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.assigned {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
        }

        .status-badge.returned {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .status-badge.overdue {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .info-section {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .info-section-header {
            background: #f1f5f9;
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 15px;
            display: flex;
            align-items: center;
        }

        .info-section-header i {
            margin-right: 10px;
            color: #4e73df;
        }

        .info-section-body {
            padding: 20px;
            background: white;
        }

        .info-table {
            width: 100%;
            margin: 0;
        }

        .info-table th {
            width: 35%;
            padding: 10px 12px;
            font-weight: 500;
            color: #6b7280;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .info-table td {
            padding: 10px 12px;
            color: #374151;
            border: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .info-table a {
            color: #4e73df;
            text-decoration: none;
            font-weight: 500;
        }

        .info-table a:hover {
            text-decoration: underline;
        }

        .condition-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            height: 100%;
        }

        .condition-card-header {
            background: #f1f5f9;
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 15px;
            display: flex;
            align-items: center;
        }

        .condition-card-header i {
            margin-right: 10px;
            color: #4e73df;
        }

        .condition-card-body {
            padding: 20px;
        }

        .condition-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .condition-item:last-child {
            border-bottom: none;
        }

        .condition-label {
            font-weight: 500;
            color: #6b7280;
            font-size: 14px;
        }

        .condition-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .condition-badge.new {
            background: #dcfce7;
            color: #166534;
        }

        .condition-badge.good {
            background: #dbeafe;
            color: #1e40af;
        }

        .condition-badge.fair {
            background: #fef3c7;
            color: #b45309;
        }

        .condition-badge.poor {
            background: #fee2e2;
            color: #dc2626;
        }

        .condition-change {
            font-size: 14px;
            font-weight: 500;
        }

        .condition-change.improved {
            color: #059669;
        }

        .condition-change.deteriorated {
            color: #dc2626;
        }

        .condition-change.unchanged {
            color: #6b7280;
        }

        .notes-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            height: 100%;
        }

        .notes-card-header {
            background: #f1f5f9;
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 15px;
            display: flex;
            align-items: center;
        }

        .notes-card-header i {
            margin-right: 10px;
            color: #4e73df;
        }

        .notes-card-body {
            padding: 20px;
        }

        .notes-section {
            margin-bottom: 20px;
        }

        .notes-section:last-child {
            margin-bottom: 0;
        }

        .notes-section h6 {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .notes-content {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            padding: 12px;
            background: #f9fafb;
            border-radius: 3px;
            border-left: 3px solid #e5e7eb;
        }

        .notes-content.empty {
            font-style: italic;
            color: #9ca3af;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-action i {
            font-size: 16px;
        }

        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-back:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-return {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
        }

        .btn-return:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-dropdown {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
        }

        .btn-dropdown:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .dropdown-menu {
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
        }

        .dropdown-item {
            padding: 10px 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
        }

        .dropdown-item i {
            width: 18px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .assignment-header {
                padding: 20px;
            }

            .info-section-body .row {
                flex-direction: column;
            }

            .info-section-body .row > div {
                margin-bottom: 16px;
            }

            .info-section-body .row > div:last-child {
                margin-bottom: 0;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.assignments.index') }}">Back</a>
        @endslot
        @slot('li_2')
            Assignments
        @endslot
        @slot('title')
            Assignment Details
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @endif

    <div class="assignment-container">
        <div class="assignment-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h4><i class="bx bx-clipboard me-2"></i>Assignment Details</h4>
                    <p>{{ $assignment->asset->name ?? 'N/A' }} ({{ $assignment->asset->asset_code ?? '' }})</p>
                </div>
                <div class="status-badges">
                    @if ($assignment->status === 'Assigned')
                        <span class="status-badge assigned">Assigned</span>
                        @if ($assignment->isOverdue())
                            <span class="status-badge overdue">Overdue</span>
                        @endif
                    @elseif($assignment->status === 'Returned')
                        <span class="status-badge returned">Returned</span>
                    @else
                        <span class="status-badge" style="background: #6b7280; color: white;">{{ $assignment->status }}</span>
                    @endif
                </div>
            </div>
            <div class="header-actions">
                @if ($assignment->status === 'Assigned' && !$assignment->actual_return_date)
                    <a href="{{ route('assets.assignments.return', $assignment->id) }}" class="btn btn-sm btn-return">
                        <i class="bx bx-transfer-alt"></i> Process Return
                    </a>
                @endif
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-dropdown dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded me-1"></i> More Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('assets.show', $assignment->asset_id) }}">
                                <i class="bx bx-package"></i> View Asset
                            </a>
                        </li>
                        @if ($assignment->assignable_type === 'App\\Models\\User')
                            <li>
                                <a class="dropdown-item" href="{{ route('assets.show-user-assignments', $assignment->assignable_id) }}">
                                    <i class="bx bx-user"></i> View User's Assignments
                                </a>
                            </li>
                        @endif
                        @if ($assignment->isOverdue())
                            <li>
                                <a class="dropdown-item text-warning" href="#">
                                    <i class="bx bx-envelope"></i> Send Reminder
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="assignment-body">
            <!-- Assignment Information -->
            <div class="info-section">
                <div class="info-section-header">
                    <i class="bx bx-info-circle"></i>
                    Assignment Information
                </div>
                <div class="info-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="info-table">
                                <tbody>
                                    <tr>
                                        <th>Asset:</th>
                                        <td>
                                            <a href="{{ route('assets.show', $assignment->asset_id) }}">
                                                {{ $assignment->asset->name ?? 'N/A' }}
                                            </a>
                                            <small class="d-block text-muted">{{ $assignment->asset->asset_code ?? '' }}</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Category:</th>
                                        <td>{{ $assignment->asset->category->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Assigned To:</th>
                                        <td>
                                            @if ($assignment->assignable_type === 'App\\Models\\User')
                                                <a href="{{ route('assets.show-user-assignments', $assignment->assignable_id) }}">
                                                    {{ $assignment->assignable->firstname ?? '' }}
                                                    {{ $assignment->assignable->lastname ?? '' }}
                                                </a>
                                            @else
                                                {{ $assignment->assignable_type }} {{ $assignment->assignable_id }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Assigned By:</th>
                                        <td>{{ $assignment->assignedByUser->firstname ?? '' }} {{ $assignment->assignedByUser->lastname ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Assignment Date:</th>
                                        <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="info-table">
                                <tbody>
                                    <tr>
                                        <th>Expected Return:</th>
                                        <td>
                                            @if ($assignment->expected_return_date)
                                                {{ $assignment->expected_return_date->format('M d, Y') }}
                                                @if ($assignment->isOverdue() && $assignment->status === 'Assigned')
                                                    <span class="status-badge overdue ms-1" style="font-size: 10px; padding: 2px 6px;">
                                                        Overdue by {{ now()->diffInDays($assignment->expected_return_date) }} days
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Actual Return:</th>
                                        <td>
                                            @if ($assignment->actual_return_date)
                                                {{ $assignment->actual_return_date->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">Not returned yet</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Received By:</th>
                                        <td>
                                            @if ($assignment->receivedByUser)
                                                {{ $assignment->receivedByUser->firstname ?? '' }}
                                                {{ $assignment->receivedByUser->lastname ?? '' }}
                                            @else
                                                <span class="text-muted">Not applicable</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if ($assignment->status === 'Assigned')
                                                <span class="status-badge assigned" style="font-size: 11px; padding: 3px 8px;">Assigned</span>
                                            @elseif($assignment->status === 'Returned')
                                                <span class="status-badge returned" style="font-size: 11px; padding: 3px 8px;">Returned</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Duration:</th>
                                        <td>
                                            @if ($assignment->actual_return_date)
                                                {{ $assignment->assigned_date->diffInDays($assignment->actual_return_date) }} days
                                            @else
                                                {{ $assignment->assigned_date->diffInDays(now()) }} days <span class="text-muted">(ongoing)</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Condition & Notes -->
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="condition-card">
                        <div class="condition-card-header">
                            <i class="bx bx-check-shield"></i>
                            Condition
                        </div>
                        <div class="condition-card-body">
                            <div class="condition-item">
                                <span class="condition-label">Condition on Assignment:</span>
                                @php
                                    $assignCondition = strtolower($assignment->condition_on_assignment ?? 'good');
                                @endphp
                                <span class="condition-badge {{ $assignCondition }}">
                                    {{ $assignment->condition_on_assignment }}
                                </span>
                            </div>

                            @if ($assignment->status === 'Returned')
                                <div class="condition-item">
                                    <span class="condition-label">Condition on Return:</span>
                                    @php
                                        $returnCondition = strtolower($assignment->condition_on_return ?? 'good');
                                    @endphp
                                    <span class="condition-badge {{ $returnCondition }}">
                                        {{ $assignment->condition_on_return }}
                                    </span>
                                </div>

                                <div class="condition-item">
                                    <span class="condition-label">Condition Change:</span>
                                    @php
                                        $conditions = [
                                            'New' => 4,
                                            'Good' => 3,
                                            'Fair' => 2,
                                            'Poor' => 1,
                                        ];
                                        $assignmentCondition = $conditions[$assignment->condition_on_assignment] ?? 0;
                                        $returnConditionVal = $conditions[$assignment->condition_on_return] ?? 0;
                                        $difference = $returnConditionVal - $assignmentCondition;
                                    @endphp

                                    @if ($difference > 0)
                                        <span class="condition-change improved">
                                            <i class="bx bx-trending-up me-1"></i> Improved
                                        </span>
                                    @elseif ($difference < 0)
                                        <span class="condition-change deteriorated">
                                            <i class="bx bx-trending-down me-1"></i> Deteriorated
                                        </span>
                                    @else
                                        <span class="condition-change unchanged">
                                            <i class="bx bx-minus me-1"></i> No change
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="notes-card">
                        <div class="notes-card-header">
                            <i class="bx bx-note"></i>
                            Notes
                        </div>
                        <div class="notes-card-body">
                            <div class="notes-section">
                                <h6>Assignment Notes</h6>
                                <div class="notes-content {{ !$assignment->assignment_notes ? 'empty' : '' }}">
                                    {{ $assignment->assignment_notes ?: 'No assignment notes provided.' }}
                                </div>
                            </div>

                            @if ($assignment->status === 'Returned')
                                <div class="notes-section">
                                    <h6>Return Notes</h6>
                                    <div class="notes-content {{ !$assignment->return_notes ? 'empty' : '' }}">
                                        {{ $assignment->return_notes ?: 'No return notes provided.' }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('assets.assignments.index') }}" class="btn-action btn-back">
                    <i class="bx bx-arrow-back"></i> Back to Assignments
                </a>
                @if ($assignment->status === 'Assigned' && !$assignment->actual_return_date)
                    <a href="{{ route('assets.assignments.return', $assignment->id) }}" class="btn-action btn-return">
                        <i class="bx bx-transfer-alt"></i> Process Return
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
