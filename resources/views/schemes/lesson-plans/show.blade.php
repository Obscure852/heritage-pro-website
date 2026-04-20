@extends('layouts.master')
@section('title')
    Lesson Plan &mdash; {{ $lessonPlan->topic }}
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        .schemes-header {
            position: relative;
            overflow: hidden;
            padding: 32px 32px 28px;
        }

        .schemes-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 1px 1px, rgba(255,255,255,0.07) 1px, transparent 0);
            background-size: 24px 24px;
            pointer-events: none;
        }

        .schemes-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .schemes-header > * { position: relative; z-index: 1; }

        .schemes-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .form-container { padding: 32px; }

        .form-section-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            margin-right: 10px;
            flex-shrink: 0;
        }

        .section-title {
            display: flex;
            align-items: center;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 8px;
        }

        .detail-card {
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            padding: 16px;
        }

        .detail-card .field-label {
            margin-bottom: 6px;
        }

        .detail-card .field-value {
            font-weight: 600;
        }

        .content-block {
            background: #fafbfc;
            border: 1px solid #f3f4f6;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 16px;
        }

        .content-block .field-label {
            margin-bottom: 8px;
        }

        .content-block .field-value ul,
        .content-block .field-value ol {
            margin: 0;
            padding-left: 20px;
        }

        .reflection-section {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 24px;
            margin-top: 8px;
        }

        .reflection-section .section-title {
            margin-top: 0;
            border-image: none;
            border-left: 3px solid #22c55e;
            border-bottom-color: #bbf7d0;
        }

        .reflection-section .form-section-icon {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .reflection-section .field-value {
            color: #15803d;
            line-height: 1.6;
        }

        /* --- Workflow Trail --- */
        .wf-trail {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 16px 20px 8px;
            background: #fafbfc;
        }
        .wf-event {
            display: flex;
            gap: 14px;
            min-height: 48px;
        }
        .wf-event:last-child .wf-stem { display: none; }
        .wf-rail {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 14px;
            flex-shrink: 0;
            padding-top: 2px;
        }
        .wf-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .wf-stem {
            width: 2px;
            flex: 1;
            background: #e2e8f0;
            margin: 4px 0;
        }
        .wf-body {
            flex: 1;
            padding-bottom: 14px;
        }
        .wf-head {
            display: flex;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
        }
        .wf-action {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }
        .wf-meta {
            font-size: 12px;
            color: #64748b;
        }
        .wf-time {
            font-size: 11px;
            color: #94a3b8;
            display: block;
            margin-top: 1px;
        }
        .wf-comment {
            margin-top: 6px;
            font-size: 12px;
            color: #475569;
            font-style: italic;
            padding: 6px 10px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            line-height: 1.4;
        }

        @media (max-width: 768px) {
            .schemes-header { padding: 20px; }
            .form-container { padding: 20px; }
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Lesson Plan
        @endslot
    @endcomponent

    @php
        $subjectName = $lessonPlan->entry?->scheme?->klassSubject?->gradeSubject?->subject?->name
            ?? $lessonPlan->entry?->scheme?->optionalSubject?->gradeSubject?->subject?->name
            ?? null;
    @endphp

    <div class="schemes-container">
        <div class="schemes-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin: 0;">{{ $lessonPlan->topic }}</h3>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="header-pill">
                            <i class="fas fa-calendar-day me-1"></i> {{ $lessonPlan->date?->format('d M Y') }}
                        </span>
                        @if ($lessonPlan->period)
                            <span class="header-pill">
                                <i class="fas fa-clock me-1"></i> {{ $lessonPlan->period }}
                            </span>
                        @endif
                        @if ($subjectName)
                            <span class="header-pill">
                                <i class="fas fa-book me-1"></i> {{ $subjectName }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    @if ($lessonPlan->entry?->scheme)
                        <a href="{{ route('schemes.show', $lessonPlan->entry->scheme) }}" class="btn-outline-white">
                            <i class="fas fa-arrow-left"></i> Back to Scheme
                        </a>
                    @else
                        <a href="{{ route('schemes.teacher.dashboard') }}" class="btn-outline-white">
                            <i class="fas fa-arrow-left"></i> Dashboard
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="form-container">
            {{-- Scheme link --}}
            @if ($lessonPlan->entry?->scheme && $subjectName)
                <div class="help-text mb-4">
                    <div class="help-title"><i class="fas fa-link me-1"></i> Part of Scheme</div>
                    <div class="help-content">
                        <a href="{{ route('schemes.show', $lessonPlan->entry->scheme) }}" style="color: #2563eb;">
                            {{ $subjectName }} &mdash; Week {{ $lessonPlan->entry->week_number }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Status badge --}}
            @php
                $statusColors = [
                    'draft' => 'secondary',
                    'submitted' => 'info',
                    'supervisor_reviewed' => 'primary',
                    'revision_required' => 'danger',
                    'approved' => 'success',
                    'taught' => 'success',
                ];
                $badgeColor = $statusColors[$lessonPlan->status] ?? 'secondary';
            @endphp
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="status-badge status-{{ $lessonPlan->status }}">
                        <span class="status-dot dot-{{ $lessonPlan->status }}"></span>
                        {{ ucfirst(str_replace('_', ' ', $lessonPlan->status)) }}
                    </span>
                    @if ($lessonPlan->status === 'taught' && $lessonPlan->taught_at)
                        <span class="text-muted" style="font-size: 12px;">
                            <i class="far fa-clock me-1"></i>{{ $lessonPlan->taught_at->format('d M Y') }}
                        </span>
                    @endif
                </div>
                <div class="action-buttons d-flex gap-1">
                    @if ($canEdit)
                        <a href="{{ route('lesson-plans.edit', $lessonPlan) }}" title="Edit"
                           class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom">
                            <i class="fas fa-edit"></i>
                        </a>
                    @endif
                    @if ($isTeacherOwner || \App\Policies\SchemeOfWorkPolicy::isAdmin(auth()->user()))
                        <button type="button" title="Delete" class="btn btn-sm btn-outline-danger"
                                id="btn-delete-lesson-plan" data-bs-toggle="tooltip" data-bs-placement="bottom">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Revision required alert --}}
            @if ($lessonPlan->status === 'revision_required')
                @if ($lessonPlan->review_comments)
                    <div class="help-text mb-3" style="border-left-color: #ef4444; background: #fef2f2;">
                        <div class="help-title" style="color: #991b1b;"><i class="fas fa-exclamation-circle me-1"></i> HOD Revision Required</div>
                        <div class="help-content" style="color: #7f1d1d;">{{ $lessonPlan->review_comments }}</div>
                        @if ($lessonPlan->reviewer)
                            <div class="help-content mt-1" style="font-size: 11px; color: #9ca3af;">— {{ $lessonPlan->reviewer->full_name }}, {{ $lessonPlan->reviewed_at?->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                @endif
                @if ($lessonPlan->supervisor_comments)
                    <div class="help-text mb-3" style="border-left-color: #f59e0b; background: #fffbeb;">
                        <div class="help-title" style="color: #92400e;"><i class="fas fa-exclamation-triangle me-1"></i> Supervisor Feedback</div>
                        <div class="help-content" style="color: #78350f;">{{ $lessonPlan->supervisor_comments }}</div>
                        @if ($lessonPlan->supervisorReviewer)
                            <div class="help-content mt-1" style="font-size: 11px; color: #9ca3af;">— {{ $lessonPlan->supervisorReviewer->full_name }}, {{ $lessonPlan->supervisor_reviewed_at?->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                @endif
            @endif

            {{-- Workflow action buttons --}}
            <div class="d-flex flex-wrap gap-2 mb-4">
                @if ($canSubmit)
                    <form action="{{ route('lesson-plans.submit', $lessonPlan) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-loading">
                            <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Submit for Review</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Submitting...</span>
                        </button>
                    </form>
                @endif

                @if ($canSupervisorReview)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#supervisorApproveModal">
                        <i class="fas fa-check me-1"></i> Approve (Supervisor)
                    </button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#supervisorReturnModal">
                        <i class="fas fa-undo me-1"></i> Return for Revision
                    </button>
                @endif

                @if ($canHodReview)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#hodApproveModal">
                        <i class="fas fa-check-double me-1"></i> Approve (HOD)
                    </button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#hodReturnModal">
                        <i class="fas fa-undo me-1"></i> Return for Revision
                    </button>
                @endif

                @if ($canMarkTaught)
                    <button type="button" class="btn btn-outline-success" id="btn-mark-taught">
                        <i class="fas fa-check-circle me-1"></i> Mark as Taught
                    </button>
                @endif
            </div>

            {{-- Lesson Details --}}
            <div class="section-title">
                <span class="form-section-icon"><i class="fas fa-calendar-day"></i></span>
                Lesson Details
            </div>
            <div class="detail-grid">
                <div class="detail-card">
                    <div class="field-label">Day</div>
                    <div class="field-value">{{ $lessonPlan->date?->format('D, d M Y') ?? '—' }}</div>
                </div>
                <div class="detail-card">
                    <div class="field-label">Periods/Week</div>
                    <div class="field-value {{ $lessonPlan->period ? '' : 'empty' }}">
                        {{ $lessonPlan->period ?? '—' }}
                    </div>
                </div>
                <div class="detail-card">
                    <div class="field-label">Status</div>
                    <div class="field-value">
                        @if ($lessonPlan->status === 'planned')
                            <span class="badge bg-info">Planned</span>
                        @else
                            <span class="badge bg-success">Taught</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Topic & Objectives --}}
            <div class="section-title">
                <span class="form-section-icon"><i class="fas fa-bullseye"></i></span>
                Topic &amp; Objectives
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="content-block">
                        <div class="field-label">Topic</div>
                        <div class="field-value">{{ $lessonPlan->topic }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="content-block">
                        <div class="field-label">Sub-topic</div>
                        <div class="field-value {{ $lessonPlan->sub_topic ? '' : 'empty' }}">
                            {{ $lessonPlan->sub_topic ?? '—' }}
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="content-block">
                        <div class="field-label">Learning Objectives</div>
                        <div class="field-value {{ $lessonPlan->learning_objectives ? '' : 'empty' }}">
                            {!! $lessonPlan->learning_objectives ?? '—' !!}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lesson Plan --}}
            <div class="section-title">
                <span class="form-section-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                Lesson Plan
            </div>
            <div class="content-block">
                <div class="field-label">Content</div>
                <div class="field-value {{ $lessonPlan->content ? '' : 'empty' }}">
                    {!! $lessonPlan->content ?? '—' !!}
                </div>
            </div>
            <div class="content-block">
                <div class="field-label">Activities</div>
                <div class="field-value {{ $lessonPlan->activities ? '' : 'empty' }}">
                    {!! $lessonPlan->activities ?? '—' !!}
                </div>
            </div>
            <div class="content-block">
                <div class="field-label">Teaching/Learning Aids</div>
                <div class="field-value {{ $lessonPlan->teaching_learning_aids ? '' : 'empty' }}">
                    {!! $lessonPlan->teaching_learning_aids ?? '—' !!}
                </div>
            </div>
            <div class="content-block">
                <div class="field-label">Lesson Evaluation</div>
                <div class="field-value {{ $lessonPlan->lesson_evaluation ? '' : 'empty' }}">
                    {!! $lessonPlan->lesson_evaluation ?? '—' !!}
                </div>
            </div>

            {{-- Resources & Homework --}}
            <div class="section-title">
                <span class="form-section-icon"><i class="fas fa-book-reader"></i></span>
                Resources &amp; Homework
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="content-block">
                        <div class="field-label">Reference Materials</div>
                        <div class="field-value {{ $lessonPlan->resources ? '' : 'empty' }}">
                            {!! $lessonPlan->resources ?? '—' !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="content-block">
                        <div class="field-label">Homework</div>
                        <div class="field-value {{ $lessonPlan->homework ? '' : 'empty' }}">
                            {!! $lessonPlan->homework ?? '—' !!}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Workflow History --}}
            @if ($lessonPlan->supervisor_reviewed_by || $lessonPlan->reviewed_by)
                <div class="section-title" style="margin-top: 28px;">
                    <span class="form-section-icon"><i class="fas fa-route"></i></span>
                    Review Trail
                </div>
                <div class="wf-trail">
                    {{-- Created --}}
                    <div class="wf-event">
                        <div class="wf-rail">
                            <span class="wf-dot" style="background: #94a3b8;"></span>
                            <span class="wf-stem"></span>
                        </div>
                        <div class="wf-body">
                            <div class="wf-head">
                                <span class="wf-action">Created</span>
                                <span class="wf-meta">{{ $lessonPlan->teacher?->full_name ?? 'Unknown' }}</span>
                            </div>
                            <span class="wf-time">{{ $lessonPlan->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    {{-- Submitted --}}
                    @if (!in_array($lessonPlan->status, ['draft']))
                        <div class="wf-event">
                            <div class="wf-rail">
                                <span class="wf-dot" style="background: #3b82f6;"></span>
                                <span class="wf-stem"></span>
                            </div>
                            <div class="wf-body">
                                <div class="wf-head">
                                    <span class="wf-action">Submitted for Review</span>
                                    <span class="wf-meta">{{ $lessonPlan->teacher?->full_name }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Supervisor Review --}}
                    @if ($lessonPlan->supervisor_reviewed_by)
                        @php
                            $supReturned = $lessonPlan->status === 'revision_required' && $lessonPlan->supervisor_comments && !$lessonPlan->reviewed_by;
                        @endphp
                        <div class="wf-event">
                            <div class="wf-rail">
                                <span class="wf-dot" style="background: {{ $supReturned ? '#ef4444' : '#6366f1' }};"></span>
                                <span class="wf-stem"></span>
                            </div>
                            <div class="wf-body">
                                <div class="wf-head">
                                    <span class="wf-action">{{ $supReturned ? 'Returned by Supervisor' : 'Supervisor Approved' }}</span>
                                    <span class="wf-meta">{{ $lessonPlan->supervisorReviewer?->full_name }}</span>
                                </div>
                                <span class="wf-time">{{ $lessonPlan->supervisor_reviewed_at?->format('d M Y, H:i') }}</span>
                                @if ($lessonPlan->supervisor_comments)
                                    <div class="wf-comment">"{{ $lessonPlan->supervisor_comments }}"</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- HOD Review --}}
                    @if ($lessonPlan->reviewed_by)
                        @php
                            $hodReturned = $lessonPlan->status === 'revision_required' && $lessonPlan->review_comments;
                        @endphp
                        <div class="wf-event">
                            <div class="wf-rail">
                                <span class="wf-dot" style="background: {{ $hodReturned ? '#ef4444' : '#10b981' }};"></span>
                                <span class="wf-stem"></span>
                            </div>
                            <div class="wf-body">
                                <div class="wf-head">
                                    <span class="wf-action">{{ $hodReturned ? 'Returned by HOD' : 'HOD Approved' }}</span>
                                    <span class="wf-meta">{{ $lessonPlan->reviewer?->full_name }}</span>
                                </div>
                                <span class="wf-time">{{ $lessonPlan->reviewed_at?->format('d M Y, H:i') }}</span>
                                @if ($lessonPlan->review_comments)
                                    <div class="wf-comment">"{{ $lessonPlan->review_comments }}"</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Taught --}}
                    @if ($lessonPlan->status === 'taught' && $lessonPlan->taught_at)
                        <div class="wf-event">
                            <div class="wf-rail">
                                <span class="wf-dot" style="background: #059669;"></span>
                            </div>
                            <div class="wf-body">
                                <div class="wf-head">
                                    <span class="wf-action">Lesson Delivered</span>
                                    <span class="wf-meta">{{ $lessonPlan->teacher?->full_name }}</span>
                                </div>
                                <span class="wf-time">{{ $lessonPlan->taught_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Reflection Notes --}}
            @if ($lessonPlan->status === 'taught')
                @if ($lessonPlan->reflection_notes)
                    <div class="reflection-section">
                        <div class="section-title">
                            <span class="form-section-icon"><i class="fas fa-journal-whills"></i></span>
                            Reflection Notes
                        </div>
                        <div class="field-value">{!! $lessonPlan->reflection_notes !!}</div>
                    </div>
                @else
                    <div class="help-text" style="border-left-color: #f59e0b; background: #fffbeb; margin-top: 24px;">
                        <div class="help-title" style="color: #92400e;">
                            <i class="fas fa-sticky-note me-1"></i> No Reflection Notes Yet
                        </div>
                        <div class="help-content" style="color: #78350f;">
                            You haven't added reflection notes yet.
                            <a href="{{ route('lesson-plans.edit', $lessonPlan) }}" style="color: #d97706; font-weight: 600;">Add reflection notes</a>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Hidden forms --}}
    <form id="delete-lesson-plan-form" action="{{ route('lesson-plans.destroy', $lessonPlan) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    @if ($canMarkTaught)
        <form id="mark-taught-form" action="{{ route('lesson-plans.mark-taught', $lessonPlan) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endif

    {{-- Supervisor Approve Modal --}}
    @if ($canSupervisorReview)
        <div class="modal fade" id="supervisorApproveModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('lesson-plans.supervisor-approve', $lessonPlan) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Supervisor Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Approve this lesson plan and forward to HOD for final review?</p>
                            <div class="mb-3">
                                <label class="form-label">Comments (optional)</label>
                                <textarea name="comments" class="form-control" rows="3" placeholder="Any feedback for the teacher..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-loading">
                                <span class="btn-text"><i class="fas fa-check me-1"></i> Approve</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Approving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="supervisorReturnModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('lesson-plans.supervisor-return', $lessonPlan) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Return for Revision</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Comments <span class="text-danger">*</span></label>
                                <textarea name="comments" class="form-control" rows="3" required placeholder="Explain what needs to be revised..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning btn-loading">
                                <span class="btn-text"><i class="fas fa-undo me-1"></i> Return</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- HOD Approve Modal --}}
    @if ($canHodReview)
        <div class="modal fade" id="hodApproveModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('lesson-plans.approve', $lessonPlan) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">HOD Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Approve this lesson plan? The teacher will be able to mark it as taught after approval.</p>
                            <div class="mb-3">
                                <label class="form-label">Comments (optional)</label>
                                <textarea name="comments" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-loading">
                                <span class="btn-text"><i class="fas fa-check-double me-1"></i> Approve</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Approving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="hodReturnModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('lesson-plans.return-for-revision', $lessonPlan) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Return for Revision</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Comments <span class="text-danger">*</span></label>
                                <textarea name="comments" class="form-control" rows="3" required placeholder="Explain what needs to be revised..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning btn-loading">
                                <span class="btn-text"><i class="fas fa-undo me-1"></i> Return</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('script')
    <script>
    (function () {
        'use strict';

        function showToast(message, icon) {
            icon = icon || 'success';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: message,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
            });
        }

        @if (session('success'))
            showToast(@json(session('success')), 'success');
        @endif

        @if (session('error'))
            showToast(@json(session('error')), 'error');
        @endif

        // Initialize tooltips
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(function(el) {
            new bootstrap.Tooltip(el, { trigger: 'hover', delay: { show: 300, hide: 0 } });
        });

        // Delete confirmation
        const deleteBtn = document.getElementById('btn-delete-lesson-plan');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                Swal.fire({
                    title: 'Delete this lesson plan?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        document.getElementById('delete-lesson-plan-form').submit();
                    }
                });
            });
        }

        // Mark as Taught confirmation
        const markTaughtBtn = document.getElementById('btn-mark-taught');
        if (markTaughtBtn) {
            markTaughtBtn.addEventListener('click', function () {
                Swal.fire({
                    title: 'Mark as Taught?',
                    text: 'This will record today as the taught date. You can then add reflection notes.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, mark as taught',
                    cancelButtonText: 'Cancel',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        markTaughtBtn.disabled = true;
                        document.getElementById('mark-taught-form').submit();
                    }
                });
            });
        }

        // Modal form loading states
        document.querySelectorAll('.modal form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"].btn-loading');
                if (btn) { btn.classList.add('loading'); btn.disabled = true; }
            });
        });

        // Submit button loading state
        document.querySelectorAll('form:not(.modal form) button[type="submit"].btn-loading').forEach(function (btn) {
            btn.closest('form').addEventListener('submit', function () {
                btn.classList.add('loading');
                btn.disabled = true;
            });
        });
    })();
    </script>
@endsection
