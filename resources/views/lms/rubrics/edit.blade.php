@extends('layouts.master')

@section('title', 'Edit Rubric')

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

        .warning-text {
            background: #fef3c7;
            padding: 12px;
            border-left: 4px solid #f59e0b;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .warning-text .warning-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 4px;
        }

        .warning-text .warning-content {
            color: #92400e;
            font-size: 13px;
            line-height: 1.4;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-check-input {
            border-radius: 3px;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .text-danger {
            color: #dc2626;
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

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            color: white;
        }

        .btn-sm {
            padding: 8px 14px;
            font-size: 13px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        /* Criterion Card */
        .criterion-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .criterion-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .criterion-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .criterion-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
            margin: 0;
        }

        .criterion-title a {
            color: inherit;
            text-decoration: none;
        }

        .criterion-title a:hover {
            color: #3b82f6;
        }

        .criterion-points {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 600;
        }

        .criterion-description {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .levels-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .level-badge {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            color: #374151;
        }

        .level-badge .points {
            color: #3b82f6;
            font-weight: 600;
            margin-left: 4px;
        }

        .criterion-actions {
            display: flex;
            gap: 8px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .action-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }

        .action-btn.danger:hover {
            border-color: #dc2626;
            color: #dc2626;
            background: #fef2f2;
        }

        .empty-criteria {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 3px;
        }

        .empty-criteria > i {
            font-size: 32px;
            color: #d1d5db;
            margin-bottom: 12px;
        }

        /* Summary Card */
        .summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .summary-card h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 12px 0;
        }

        .summary-stat {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-stat:last-child {
            border-bottom: none;
        }

        .summary-stat .label {
            color: #6b7280;
            font-size: 14px;
        }

        .summary-stat .value {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .total-points-highlight {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 16px;
            border-radius: 3px;
            text-align: center;
            margin-top: 16px;
        }

        .total-points-highlight .label {
            font-size: 12px;
            opacity: 0.9;
        }

        .total-points-highlight .value {
            font-size: 28px;
            font-weight: 700;
        }

        /* Danger Zone */
        .danger-zone {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 20px;
        }

        .danger-zone h3 {
            font-size: 14px;
            font-weight: 600;
            color: #991b1b;
            margin: 0 0 12px 0;
        }

        .danger-zone p {
            color: #991b1b;
            font-size: 13px;
            margin: 0 0 16px 0;
        }

        .alert {
            border-radius: 3px;
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
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Edit Rubric
        @endslot
    @endcomponent

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Info Form -->
            <form action="{{ route('lms.rubrics.update', $rubric) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-container mb-4">
                    <div class="page-header">
                        <h1 class="page-title">Edit Rubric</h1>
                    </div>

                    @if ($assignmentCount > 0)
                        <div class="warning-text">
                            <div class="warning-title"><i class="fas fa-exclamation-triangle me-2"></i>Rubric In Use</div>
                            <div class="warning-content">
                                This rubric is used by {{ $assignmentCount }}
                                assignment{{ $assignmentCount > 1 ? 's' : '' }}.
                                Changes will affect grading for those assignments.
                            </div>
                        </div>
                    @endif

                    <h3 class="section-title">Basic Information</h3>

                    <div class="form-group">
                        <label class="form-label">Rubric Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $rubric->title) }}" placeholder="e.g., Essay Writing Rubric" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"
                            placeholder="Brief description of this rubric's purpose">{{ old('description', $rubric->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_template" id="isTemplate"
                                value="1" {{ old('is_template', $rubric->is_template) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isTemplate">
                                Save as Template
                                <small class="text-muted d-block">Templates are visible to other instructors</small>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 16px; padding-top: 16px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <!-- Criteria Section -->
            <div class="form-container">
                <div class="section-header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #1f2937; margin: 0;">Grading Criteria</h3>
                    <a href="{{ route('lms.rubrics.criteria.create', $rubric) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Criterion
                    </a>
                </div>

                @if ($rubric->criteria->count())
                    @foreach ($rubric->criteria->sortBy('sequence') as $criterion)
                        <div class="criterion-card">
                            <div class="criterion-header">
                                <h4 class="criterion-title">
                                    <a href="{{ route('lms.rubrics.criteria.edit', [$rubric, $criterion]) }}">
                                        {{ $criterion->title }}
                                    </a>
                                </h4>
                                <span class="criterion-points">{{ number_format($criterion->max_points) }} pts</span>
                            </div>

                            @if ($criterion->description)
                                <div class="criterion-description">{{ $criterion->description }}</div>
                            @endif

                            <div class="levels-list">
                                @foreach ($criterion->levels->sortByDesc('points') as $level)
                                    <span class="level-badge">
                                        {{ $level->title }}<span class="points">{{ $level->points }}</span>
                                    </span>
                                @endforeach
                            </div>

                            <div class="criterion-actions">
                                <a href="{{ route('lms.rubrics.criteria.edit', [$rubric, $criterion]) }}"
                                    class="action-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('lms.rubrics.criteria.destroy', [$rubric, $criterion]) }}"
                                    method="POST" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this criterion?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-criteria">
                        <i class="fas fa-list-ul d-block"></i>
                        <p class="mb-3">No criteria added yet</p>
                        <a href="{{ route('lms.rubrics.criteria.create', $rubric) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus font-size-16 me-2" style="vertical-align: middle;"></i>Add First Criterion
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card">
                <h3><i class="fas fa-chart-bar me-2"></i>Rubric Summary</h3>
                <div class="summary-stat">
                    <span class="label">Criteria</span>
                    <span class="value">{{ $rubric->criteria->count() }}</span>
                </div>
                <div class="summary-stat">
                    <span class="label">Performance Levels</span>
                    <span class="value">{{ $rubric->criteria->sum(fn($c) => $c->levels->count()) }}</span>
                </div>
                <div class="summary-stat">
                    <span class="label">Assignments Using</span>
                    <span class="value">{{ $assignmentCount }}</span>
                </div>
                <div class="total-points-highlight">
                    <div class="label">Total Points</div>
                    <div class="value">{{ number_format($rubric->total_points) }}</div>
                </div>
            </div>

            <div class="summary-card">
                <h3><i class="fas fa-info-circle me-2"></i>Rubric Info</h3>
                <div class="summary-stat">
                    <span class="label">Created by</span>
                    <span class="value">{{ $rubric->creator->name ?? 'Unknown' }}</span>
                </div>
                <div class="summary-stat">
                    <span class="label">Created</span>
                    <span class="value">{{ $rubric->created_at->format('M d, Y') }}</span>
                </div>
                <div class="summary-stat">
                    <span class="label">Last updated</span>
                    <span class="value">{{ $rubric->updated_at->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="summary-card">
                <h3><i class="fas fa-arrow-left me-2"></i>Navigation</h3>
                <a href="{{ route('lms.rubrics.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-arrow-left"></i> Back to Rubrics
                </a>
            </div>

            @if ($assignmentCount == 0)
                <div class="danger-zone">
                    <h3><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h3>
                    <p>Permanently delete this rubric. This action cannot be undone.</p>
                    <form action="{{ route('lms.rubrics.destroy', $rubric) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this rubric? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Delete Rubric
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
