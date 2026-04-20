@extends('layouts.master')
@section('title')
    Scheme of Work
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        /* --- Show page specific styles --- */

        /* Header pattern overlay */
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

        /* Stat items (admissions-style) */
        .stat-item { padding: 10px 0; }
        .stat-item h4 { font-size: 1.5rem; font-weight: 700; }
        .stat-item small { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Frosted stat cards (legacy, kept for compat) */
        .stat-card-row {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .stat-glass {
            background: rgba(255,255,255,0.13);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 10px;
            padding: 14px 20px;
            text-align: center;
            min-width: 90px;
            transition: background 0.2s;
        }

        .stat-glass:hover {
            background: rgba(255,255,255,0.2);
        }

        .stat-glass .stat-number {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
            color: white;
            letter-spacing: -0.03em;
        }

        .stat-glass .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.75);
            margin-top: 4px;
            font-weight: 600;
        }

        /* Action buttons in header */
        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .syllabus-outline {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .syllabus-outline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 0 12px;
        }

        .syllabus-outline-title {
            font-size: 15px;
            font-weight: 700;
            color: #1f2937;
        }

        .syllabus-outline-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .syllabus-outline-header .btn {
            padding: 5px 10px;
            font-size: 12px;
        }

        .syllabus-section,
        .syllabus-unit {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            background: #fff;
        }

        .syllabus-section summary,
        .syllabus-unit summary {
            list-style: none;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            font-weight: 600;
            color: #111827;
        }

        .syllabus-section summary::-webkit-details-marker,
        .syllabus-unit summary::-webkit-details-marker {
            display: none;
        }

        .syllabus-section-body {
            padding: 0 12px 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .syllabus-unit {
            background: #f9fafb;
        }

        .syllabus-unit-heading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .syllabus-unit-code {
            font-size: 12px;
            font-weight: 700;
            color: #1d4ed8;
            background: #dbeafe;
            border-radius: 999px;
            padding: 2px 8px;
        }

        .syllabus-count {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
        }

        .syllabus-topics {
            padding: 0 12px 12px;
            display: grid;
            gap: 10px;
        }

        .syllabus-topic-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 14px;
        }

        .syllabus-topic-title {
            margin: 0 0 10px;
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
        }

        .syllabus-objective-group + .syllabus-objective-group {
            margin-top: 10px;
        }

        .syllabus-objective-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .syllabus-objective-list {
            margin: 0;
            padding-left: 18px;
            color: #374151;
            font-size: 13px;
        }

        .syllabus-objective-list li + li {
            margin-top: 4px;
        }

        .syllabus-empty-state {
            padding: 16px;
            border: 1px dashed #d1d5db;
            border-radius: 3px;
            color: #6b7280;
            font-size: 13px;
            background: #fff;
        }

        /* Week tabs */
        .week-tabs-wrapper {
            position: relative;
            margin-bottom: 0;
        }

        .week-tabs-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
        }

        .week-tabs-scroll::-webkit-scrollbar {
            display: none;
        }

        .week-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #e5e7eb;
            min-width: max-content;
        }

        .week-tab {
            padding: 10px 18px;
            font-size: 12.5px;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            border: none;
            background: none;
            position: relative;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.01em;
        }

        .week-tab:hover {
            color: #374151;
            background: #f8fafc;
        }

        .week-tab.active {
            color: #1e40af;
            background: #eff6ff;
        }

        .week-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 8px;
            right: 8px;
            height: 2.5px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 2px;
        }

        /* Completed/skipped tab tints */
        .week-tab.tab-completed {
            color: #166534;
        }

        .week-tab.tab-skipped {
            color: #92400e;
        }

        .week-tab .tab-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .tab-dot-planned { background: #d1d5db; }
        .tab-dot-in_progress { background: #3b82f6; }
        .tab-dot-completed { background: #22c55e; }
        .tab-dot-skipped { background: #f59e0b; }

        .tab-scroll-btn {
            position: absolute;
            top: 0;
            bottom: 2px;
            width: 32px;
            border: none;
            cursor: pointer;
            z-index: 2;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #6b7280;
            transition: color 0.2s;
        }

        .tab-scroll-btn:hover { color: #1f2937; }
        .tab-scroll-btn.scroll-left {
            left: 0;
            background: linear-gradient(90deg, white 60%, transparent);
            padding-right: 8px;
        }
        .tab-scroll-btn.scroll-right {
            right: 0;
            background: linear-gradient(270deg, white 60%, transparent);
            padding-left: 8px;
        }
        .tab-scroll-btn.visible { display: flex; }

        /* Tab content panels */
        .week-tab-panel {
            display: none;
            opacity: 0;
            transform: translateY(6px);
        }

        .week-tab-panel.active {
            display: block;
            animation: tabFadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes tabFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .entry-row { transition: all 0.2s; }
        .entry-row.saving { opacity: 0.6; pointer-events: none; }

        .entry-toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            flex-wrap: wrap;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 16px;
            background: #fafbfc;
            padding: 12px 16px;
            border-radius: 3px;
        }

        .entry-body { padding: 0 16px 16px; }
        .entry-body .row { margin-bottom: 0; }
        .entry-field { margin-bottom: 4px; }
        .entry-field textarea { min-height: 60px; resize: vertical; }
        .entry-field.learning-objectives-editor .ck.ck-editor__editable_inline,
        .entry-field textarea.learning-objectives-input { min-height: 260px !important; }

        .save-indicator {
            color: #22c55e;
            font-size: 13px;
            display: none;
            align-items: center;
            gap: 4px;
            animation: fadeInUp 0.3s ease-out;
        }

        .save-indicator.visible { display: inline-flex; }
        .save-indicator.pending { color: #d97706; display: inline-flex; }

        /* Objective tags */
        .objective-tag {
            background: #eef2ff;
            color: #3b4ea8;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12.5px;
            display: inline-block;
            margin: 3px;
            border: 1px solid #c7d2fe;
            transition: all 0.15s ease;
        }

        .objective-tag:hover {
            background: #e0e7ff;
            border-color: #a5b4fc;
        }

        .objective-tag .btn-remove-obj {
            background: none;
            border: none;
            color: #3b4ea8;
            cursor: pointer;
            padding: 0 0 0 4px;
            font-size: 12px;
            line-height: 1;
        }

        .objective-tag .btn-remove-obj:hover { color: #dc3545; }

        .objectives-area {
            min-height: 44px;
            padding: 10px;
            border: 1.5px dashed #d1d5db;
            border-radius: 6px;
            background: #fafbfc;
            margin-bottom: 10px;
            transition: border-color 0.2s;
        }

        .objectives-area:hover {
            border-color: #93c5fd;
        }

        .objective-empty-note { color: #6b7280; font-size: 12px; }

        /* Objective browser offcanvas */
        .offcanvas-objective-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .offcanvas-objective-item:last-child { border-bottom: none; }

        .offcanvas-topic-header {
            font-weight: 600;
            font-size: 13px;
            color: #1f2937;
            margin: 12px 0 6px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        .obj-code-tag {
            font-weight: 600;
            font-size: 11px;
            color: #374151;
            background: #f3f4f6;
            padding: 1px 6px;
            border-radius: 3px;
            white-space: nowrap;
        }

        .obj-cog-tag {
            font-size: 11px;
            padding: 1px 6px;
            border-radius: 20px;
            background: #e0e7ff;
            color: #3730a3;
            white-space: nowrap;
        }

        .planner-toolbar {
            padding: 0 12px 12px;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .planner-toolbar-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 12px; }
        .planner-toolbar-label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .planner-target-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .planner-target-chip {
            display: inline-flex; align-items: center; gap: 6px;
            border-radius: 999px; background: #dbeafe; color: #1d4ed8;
            padding: 7px 12px; font-size: 12px; font-weight: 600;
        }
        .planner-target-hint { color: #64748b; font-size: 12px; margin-top: 6px; }
        .planner-mode-toggle {
            height: 36px;
        }
        .planner-mode-toggle .btn {
            height: 36px;
            font-size: 12px;
            font-weight: 600;
            padding: 0 16px;
            line-height: 34px;
            border-color: #cbd5e1;
            color: #475569;
        }
        .planner-mode-toggle .btn-check:checked + .btn {
            background: #1e293b;
            border-color: #1e293b;
            color: #fff;
        }
        .planner-target-row .form-select {
            height: 36px;
            font-size: 13px;
        }
        .planner-search-wrap { position: relative; }
        .planner-search-wrap i { position: absolute; top: 50%; left: 12px; transform: translateY(-50%); color: #94a3b8; font-size: 13px; }
        .planner-search-wrap .form-control { padding-left: 36px; }
        .planner-content { padding: 0 12px 12px; overflow-y: auto; flex: 1; }
        .planner-empty-search { text-align: center; color: #64748b; font-size: 13px; padding: 32px 16px; }

        .syllabus-topic-planner-card {
            border: 1px solid #e5e7eb; border-radius: 3px;
            padding: 14px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .planner-topic-card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        .planner-topic-kicker { display: inline-flex; align-items: center; gap: 8px; color: #64748b; font-size: 12px; font-weight: 600; margin-bottom: 6px; }
        .planner-topic-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .planner-topic-actions .btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; padding: 0; font-size: 12px; line-height: 1; border-radius: 3px; }
        .planner-topic-actions .btn i { font-size: 12px; }

        /* Tooltip styling — must appear above offcanvas (z-index 1045) */
        .tooltip { font-family: inherit; z-index: 1080 !important; }
        .tooltip .tooltip-inner { background: #1e293b; color: #fff; font-size: 12px; font-weight: 500; padding: 5px 10px; border-radius: 3px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); max-width: 200px; }
        .tooltip .tooltip-arrow::before { border-top-color: #1e293b; }
        .tooltip.bs-tooltip-bottom .tooltip-arrow::before { border-bottom-color: #1e293b; }
        .tooltip.bs-tooltip-start .tooltip-arrow::before { border-left-color: #1e293b; }
        .tooltip.bs-tooltip-end .tooltip-arrow::before { border-right-color: #1e293b; }

        .planner-objective-group-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
        .planner-objective-list { margin-bottom: 0; padding-left: 0; list-style: none; }
        .planner-objective-row { display: grid; grid-template-columns: 28px minmax(0, 1fr) auto; gap: 10px; align-items: start; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .planner-objective-row:last-child { border-bottom: none; }
        .planner-objective-insert { width: 28px; height: 28px; border: none; border-radius: 999px; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; transition: transform 0.2s ease, background 0.2s ease; }
        .planner-objective-insert:hover { background: #dbeafe; transform: translateY(-1px); }
        .planner-linked-badge { display: inline-flex; align-items: center; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 600; padding: 4px 8px; }
        .planner-card-empty { margin-top: 8px; }

        @media (max-width: 767px) {
            .planner-topic-card-head { flex-direction: column; }
            .planner-topic-actions { justify-content: flex-start; }
            .planner-target-row { align-items: stretch; }
        }

        .field-error { color: #dc3545; font-size: 12px; margin-top: 3px; }

        /* Review panels */
        .review-container {
            background: white;
            border-radius: 6px;
            padding: 0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06), 0 4px 16px rgba(0, 0, 0, 0.04);
            margin-top: 20px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .review-container .review-accent {
            height: 3px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        }

        .review-body { padding: 24px 28px; }

        /* Timeline review history */
        .review-timeline {
            position: relative;
            padding-left: 28px;
        }

        .review-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: #e5e7eb;
            border-radius: 1px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -23px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2.5px solid white;
            box-shadow: 0 0 0 2px #d1d5db;
            z-index: 1;
        }

        .timeline-dot.dot-success { background: #22c55e; box-shadow: 0 0 0 2px #bbf7d0; }
        .timeline-dot.dot-info { background: #3b82f6; box-shadow: 0 0 0 2px #bfdbfe; }
        .timeline-dot.dot-warning { background: #f59e0b; box-shadow: 0 0 0 2px #fde68a; }
        .timeline-dot.dot-danger { background: #ef4444; box-shadow: 0 0 0 2px #fecaca; }
        .timeline-dot.dot-primary { background: #6366f1; box-shadow: 0 0 0 2px #c7d2fe; }
        .timeline-dot.dot-secondary { background: #6b7280; box-shadow: 0 0 0 2px #d1d5db; }

        .timeline-content {
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 6px;
            padding: 12px 16px;
        }

        .timeline-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 4px;
        }

        .timeline-action {
            font-weight: 600;
            font-size: 13px;
            color: #1f2937;
        }

        .timeline-date {
            font-size: 11.5px;
            color: #9ca3af;
            font-weight: 500;
        }

        .timeline-actor {
            font-size: 12.5px;
            color: #6b7280;
        }

        .timeline-comment {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #374151;
            line-height: 1.5;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .schemes-header { padding: 20px; }
            .schemes-body { padding: 16px; }
            .stat-card-row { flex-wrap: wrap; justify-content: flex-start; gap: 8px; }
            .stat-glass { min-width: 72px; padding: 10px 14px; }
            .stat-glass .stat-number { font-size: 1.25rem; }
            .stat-glass .stat-label { font-size: 0.65rem; }
            .entry-toolbar { flex-direction: column; align-items: flex-start; padding: 12px; }
            .entry-toolbar .ms-auto { width: 100%; justify-content: flex-start; }
            .week-tab { padding: 8px 12px; font-size: 11.5px; }
            .header-actions { justify-content: flex-start; width: 100%; margin-top: 12px; }
            .review-body { padding: 16px; }
            .review-timeline { padding-left: 24px; }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Scheme of Work
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle-outline label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @php
        $subjectName =
            $scheme->klassSubject?->gradeSubject?->subject?->name ??
            ($scheme->optionalSubject?->gradeSubject?->subject?->name ?? 'Scheme');

        $classLabel =
            $scheme->klassSubject?->klass?->name ??
            'Optional: ' . ($scheme->optionalSubject?->gradeSubject?->subject?->name ?? '—');

        $statusColors = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'supervisor_reviewed' => 'primary',
            'under_review' => 'warning',
            'approved' => 'success',
            'revision_required' => 'danger',
        ];
        $badgeColor = $statusColors[$scheme->status] ?? 'secondary';

        $isFullyLocked = in_array($scheme->status, ['submitted', 'supervisor_reviewed', 'under_review']);
        $isApproved = $scheme->status === 'approved';
        $isLocked = $isFullyLocked; // backward compat for JS
        $isTeacherOwner = $scheme->teacher_id === auth()->id();
        $canEditSchemeContent = auth()->user()->can('update', $scheme);
        if ($scheme->isDerivedFromStandard()) {
            $canEditSchemeContent = false;
        }
        $canManageLessonPlans = \App\Policies\SchemeOfWorkPolicy::isAdmin(auth()->user()) || $isTeacherOwner;
        $hasStructuredPlannerSource = \App\Helpers\SyllabusStructureHelper::hasSections($plannerStructure);
        $hasPlannerReference = $hasStructuredPlannerSource || !is_null($syllabusDocument);
        $plannerUsesScheme = $plannerSourceType === 'scheme';
        $plannerSourceLabel = $plannerUsesScheme ? 'published scheme' : 'syllabus';
        $plannerButtonLabel = 'Browse Scheme';

        $completedEntries = $scheme->entries->where('status', 'completed')->count();
        $totalEntries = $scheme->entries->count();
    @endphp

    <div class="schemes-container">
        <div class="schemes-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin: 0;">{{ $subjectName }}</h3>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="header-pill"><i class="fas fa-chalkboard me-1"></i> {{ $classLabel }}</span>
                        <span class="header-pill"><i class="fas fa-calendar-alt me-1"></i> Term {{ $scheme->term?->term }},
                            {{ $scheme->term?->year }}</span>
                        <span class="header-pill"><i class="fas fa-user me-1"></i>
                            {{ $scheme->teacher?->full_name ?? auth()->user()->full_name }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $scheme->total_weeks }}</h4>
                                <small class="opacity-75">Weeks</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalEntries }}</h4>
                                <small class="opacity-75">Entries</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $completedEntries }}</h4>
                                <small class="opacity-75">Completed</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                @if ($totalEntries > 0)
                                    <h4 class="mb-0 fw-bold text-white">{{ round(($completedEntries / $totalEntries) * 100) }}%</h4>
                                @else
                                    <h4 class="mb-0 fw-bold text-white">0%</h4>
                                @endif
                                <small class="opacity-75">Progress</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="schemes-body">
            @if ($scheme->isDerivedFromStandard())
                <div class="help-text" style="border-left-color: #8b5cf6; background: #f5f3ff;">
                    <div class="help-title" style="color: #5b21b6;">
                        <i class="fas fa-layer-group"></i> Department Standard Scheme
                    </div>
                    <div class="help-content" style="color: #4c1d95;">
                        This scheme was prepared by your department for <strong>{{ $scheme->standardScheme?->subject?->name }}</strong> ({{ $scheme->standardScheme?->grade?->name }}).
                        The weekly topics and objectives are set. Your task is to <strong>create lesson plans</strong> for each week using the button on each entry.
                        @if ($scheme->standardScheme)
                            <a href="{{ route('standard-schemes.show', $scheme->standardScheme) }}" class="text-decoration-underline" style="color: #5b21b6;">
                                View Standard Scheme
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if ($scheme->status === 'draft' && $isTeacherOwner)
                <div class="help-text" style="border-left-color: #3b82f6; background: #eff6ff;">
                    <div class="help-title" style="color: #1e40af;">
                        <i class="fas fa-info-circle"></i> Draft Scheme
                    </div>
                    <div class="help-content" style="color: #1e3a8a;">
                        Fill in your weekly entries below. When you're ready, click <strong>"Submit for Review"</strong> to send this scheme
                        @if ($hasSupervisor ?? false)
                            to your supervisor for review.
                        @else
                            to your HOD for approval.
                        @endif
                    </div>
                </div>
            @endif

            <div class="help-text">
                <div class="help-title">Weekly Entries</div>
                <div class="help-content">
                    Fill in each week's topic, sub-topic, objectives, and duration. You can also link syllabus objectives
                    to each entry.
                </div>
            </div>

            @if ($syllabusUnavailable)
                <div class="help-text" style="border-left-color: #f59e0b; background: #fffbeb;">
                    <div class="help-title" style="color: #92400e;">
                        <i class="fas fa-exclamation-triangle"></i> Syllabus Unavailable
                    </div>
                    <div class="help-content" style="color: #78350f;">
                        The shared syllabus JSON could not be fetched and there is no cached copy or linked PDF
                        available for this subject right now.
                    </div>
                </div>
            @endif

            @if ($scheme->status === 'revision_required' && $scheme->review_comments)
                <div class="help-text" style="border-left-color: #ef4444; background: #fef2f2;">
                    <div class="help-title" style="color: #991b1b;">
                        <i class="fas fa-exclamation-circle"></i> Revision Required
                    </div>
                    <div class="help-content" style="color: #7f1d1d;">
                        {{ $scheme->review_comments }}
                    </div>
                    @if ($scheme->reviewer)
                        <div class="help-content mt-1" style="font-size: 11px;">
                            — {{ $scheme->reviewer->full_name }}, {{ $scheme->reviewed_at?->format('d M Y H:i') }}
                        </div>
                    @endif
                </div>
            @endif

            @if ($scheme->status === 'revision_required' && $scheme->supervisor_comments && !$scheme->review_comments)
                <div class="help-text" style="border-left-color: #ef4444; background: #fef2f2;">
                    <div class="help-title" style="color: #991b1b;">
                        <i class="fas fa-exclamation-circle"></i> Revision Required (Supervisor)
                    </div>
                    <div class="help-content" style="color: #7f1d1d;">
                        {{ $scheme->supervisor_comments }}
                    </div>
                    @if ($scheme->supervisorReviewer)
                        <div class="help-content mt-1" style="font-size: 11px;">
                            — {{ $scheme->supervisorReviewer->full_name }}, {{ $scheme->supervisor_reviewed_at?->format('d M Y H:i') }}
                        </div>
                    @endif
                </div>
            @endif

            @if ($isFullyLocked && $isTeacherOwner)
                <div class="help-text" style="border-left-color: #f59e0b; background: #fffbeb;">
                    <div class="help-title" style="color: #92400e;">
                        <i class="fas fa-lock"></i> Editing Locked
                    </div>
                    <div class="help-content" style="color: #78350f;">
                        This scheme is {{ str_replace('_', ' ', $scheme->status) }} and cannot be edited.
                    </div>
                </div>
            @elseif ($isApproved && $isTeacherOwner)
                <div class="help-text" style="border-left-color: #22c55e; background: #f0fdf4;">
                    <div class="help-title" style="color: #065f46;">
                        <i class="fas fa-check-circle"></i> Approved
                    </div>
                    <div class="help-content" style="color: #064e3b;">
                        This scheme is approved. You can still edit entries that are not yet completed.
                    </div>
                </div>
            @elseif (!$canEditSchemeContent)
                <div class="help-text" style="border-left-color: #64748b; background: #f8fafc;">
                    <div class="help-title" style="color: #334155;">
                        <i class="fas fa-eye"></i> View Only
                    </div>
                    <div class="help-content" style="color: #475569;">
                        You can review this scheme, browse its planning reference, and inspect lesson plans, but weekly content editing is restricted to the scheme owner and admins.
                    </div>
                </div>
            @endif

            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-12">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="status-badge status-{{ $scheme->status }}">
                            {{ ucfirst(str_replace('_', ' ', $scheme->status)) }}
                        </span>
                        @if ($scheme->cloned_from_id)
                            <span class="text-muted" style="font-size: 12px;">
                                <i class="fas fa-clone me-1"></i> Cloned from #{{ $scheme->cloned_from_id }}
                            </span>
                        @endif
                        @if ($scheme->is_published)
                            <span class="text-muted" style="font-size: 12px;">
                                <i class="fas fa-bookmark me-1 text-success"></i> Published Reference
                            </span>
                        @endif
                        <span class="text-muted" style="font-size: 12px;">
                            {{ $subjectId ?? '—' }} / {{ $gradeName ?? '—' }}
                        </span>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                        @can('submit', $scheme)
                            <button type="button" class="btn btn-success btn-sm"
                                style="padding: 6px 16px; font-size: 13px; font-weight: 500;"
                                data-bs-toggle="modal" data-bs-target="#submitSchemeModal">
                                <i class="fas fa-paper-plane me-1"></i> Submit for Review
                            </button>
                        @endcan
                        <div class="action-buttons d-flex gap-1">
                            <a href="{{ route('schemes.document', $scheme) }}" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="View as Document">
                                <i class="fas fa-file-alt"></i>
                            </a>
                            @can('publishReference', $scheme)
                                @if ($scheme->is_published)
                                    <form action="{{ route('schemes.unpublish-reference', $scheme) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Unpublish Reference Scheme">
                                            <i class="fas fa-bookmark"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('schemes.publish-reference', $scheme) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Publish as Reference Scheme">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                            @can('clone', $scheme)
                                <a href="#" class="btn btn-sm btn-outline-info"
                                    data-bs-toggle="modal" data-bs-target="#cloneSchemeModal"
                                    data-tooltip="true" data-bs-placement="bottom" title="Clone Scheme">
                                    <i class="fas fa-copy"></i>
                                </a>
                            @endcan
                            @can('delete', $scheme)
                                <a href="#" class="btn btn-sm btn-outline-danger"
                                    id="btn-delete-scheme"
                                    data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete Scheme">
                                    <i class="fas fa-trash"></i>
                                </a>
                            @endcan
                            @if ($hasPlannerReference)
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary btn-open-syllabus-planner"
                                    data-bs-toggle="offcanvas" data-bs-target="#objectiveBrowserOffcanvas"
                                    data-tooltip="true" data-bs-placement="bottom" title="{{ $plannerButtonLabel }}">
                                    <i class="fas fa-book-open"></i>
                                </button>
                            @endif
                            @if ($syllabusDocument)
                                <a href="#" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#syllabusPdfModal"
                                    data-tooltip="true" data-bs-placement="bottom" title="View Syllabus PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @php $sortedEntries = $scheme->entries->sortBy('week_number'); @endphp

            <div class="section-title">
                Weekly Entries ({{ $totalEntries }} weeks)
            </div>

            {{-- Entries container with event delegation --}}
            <div class="entries-container" id="entries-container">
                @if ($scheme->entries->count() > 0)
                    {{-- Tab bar --}}
                    <div class="week-tabs-wrapper">
                        <button type="button" class="tab-scroll-btn scroll-left" id="tab-scroll-left">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="week-tabs-scroll" id="week-tabs-scroll">
                            <div class="week-tabs" id="week-tabs">
                                @foreach ($sortedEntries as $index => $entry)
                                    @php $entryStatus = $entry->status ?? 'planned'; @endphp
                                    <button type="button" class="week-tab {{ $loop->first ? 'active' : '' }}"
                                        data-tab-target="tab-panel-{{ $entry->id }}"
                                        data-entry-id="{{ $entry->id }}">
                                        <span class="tab-dot tab-dot-{{ $entryStatus }}"
                                            id="tab-dot-{{ $entry->id }}"></span>
                                        Wk {{ $entry->week_number }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <button type="button" class="tab-scroll-btn scroll-right" id="tab-scroll-right">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    {{-- Tab panels --}}
                    @foreach ($sortedEntries as $index => $entry)
                        @php $entryStatus = $entry->status ?? 'planned'; @endphp
                        @php $entryContentLocked = !$canEditSchemeContent || $isFullyLocked || ($isApproved && $entryStatus === 'completed'); @endphp
                        <div class="week-tab-panel {{ $loop->first ? 'active' : '' }}"
                            id="tab-panel-{{ $entry->id }}">

                            <div class="entry-row" id="entry-row-{{ $entry->id }}"
                                data-entry-id="{{ $entry->id }}" data-scheme-id="{{ $scheme->id }}"
                                data-entry-status="{{ $entryStatus }}" data-week-number="{{ $entry->week_number }}"
                                data-week-label="Wk {{ $entry->week_number }}">

                                {{-- Toolbar: status, save indicator, lesson plan --}}
                                <div class="entry-toolbar">
                                    <select class="form-select status-select" name="status"
                                        style="width: auto; min-width: 140px;" data-entry-id="{{ $entry->id }}"
                                        @disabled($entryContentLocked)>
                                        <option value="planned" {{ $entryStatus === 'planned' ? 'selected' : '' }}>
                                            Planned</option>
                                        <option value="in_progress"
                                            {{ $entryStatus === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $entryStatus === 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                        <option value="skipped" {{ $entryStatus === 'skipped' ? 'selected' : '' }}>
                                            Skipped</option>
                                    </select>

                                    <span class="save-indicator" id="save-indicator-{{ $entry->id }}">
                                        <i class="fas fa-check-circle"></i> Saved
                                    </span>

                                    <div class="ms-auto d-flex align-items-center gap-2">
                                        @if ($entry->lessonPlans->count() > 0)
                                            <span class="badge bg-info">{{ $entry->lessonPlans->count() }} lesson
                                                plan(s)</span>
                                        @endif
                                        @if ($canManageLessonPlans)
                                            <a href="{{ route('lesson-plans.create', ['scheme_entry_id' => $entry->id]) }}"
                                                class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i> Lesson Plan
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- Entry form fields --}}
                                <div class="entry-body">
                                    <input type="hidden" class="entry-syllabus-topic-id"
                                        value="{{ $entry->syllabus_topic_id }}">
                                    <input type="hidden" class="entry-objective-ids"
                                        value='@json($entry->objectives->pluck("id")->values()->all())'>

                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <div class="entry-field">
                                                <label class="form-label">Topic</label>
                                                <input type="text" class="form-control entry-field-input"
                                                    name="topic" placeholder="Week topic..."
                                                    value="{{ $entry->topic }}" @disabled($entryContentLocked)>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="entry-field">
                                                <label class="form-label">Sub-topic</label>
                                                <textarea class="form-control entry-field-input" name="sub_topic" rows="2" placeholder="Sub-topic..."
                                                    @disabled($entryContentLocked)>{{ $entry->sub_topic }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="entry-field learning-objectives-editor">
                                                <label class="form-label">Learning Objectives</label>
                                                <textarea class="form-control entry-field-input learning-objectives-input" name="learning_objectives" rows="7"
                                                    placeholder="What students will learn..." @disabled($entryContentLocked)>{{ $entry->learning_objectives }}</textarea>
                                            </div>
                                        </div>

                                        @if (!$scheme->isDerivedFromStandard() || $entry->objectives->isNotEmpty())
                                        <div class="col-md-12">
                                            <div class="entry-field">
                                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                                    <label class="form-label mb-0">Linked Syllabus Objectives</label>
                                                    @if (!$scheme->isDerivedFromStandard())
                                                        <span class="text-muted" style="font-size: 12px;">
                                                            Add from the reference drawer, then save the entry once.
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="objectives-area" id="entry-objectives-{{ $entry->id }}">
                                                    @forelse ($entry->objectives as $objective)
                                                        <span class="objective-tag" data-objective-id="{{ $objective->id }}"
                                                            data-objective-text="{{ $objective->objective_text }}"
                                                            data-objective-code="{{ $objective->code ?? '' }}">
                                                            @if ($objective->code)
                                                                <span class="me-1">{{ $objective->code }}</span>
                                                            @endif
                                                            {{ \Illuminate\Support\Str::limit($objective->objective_text, 120) }}
                                                            <button type="button" class="btn-remove-obj"
                                                                aria-label="Remove objective" @disabled($entryContentLocked)>
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </span>
                                                    @empty
                                                        <div class="objective-empty-note">
                                                            No linked syllabus objectives yet. Use {{ $plannerButtonLabel }} to add them.
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if ($hasPlannerReference)
                                            <div class="col-md-12">
                                                <button type="button"
                                                    class="btn btn-outline-secondary btn-open-syllabus-planner"
                                                    data-entry-id="{{ $entry->id }}"
                                                    data-week-number="{{ $entry->week_number }}"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#objectiveBrowserOffcanvas">
                                                    <i class="fas fa-{{ $hasStructuredPlannerSource ? 'book-open' : 'file-pdf' }} me-1 {{ $hasStructuredPlannerSource ? '' : 'text-danger' }}"></i>
                                                    {{ $plannerButtonLabel }}
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Entry errors display --}}
                                    <div class="entry-errors mt-2" id="entry-errors-{{ $entry->id }}"></div>

                                    {{-- Save button --}}
                                    @if (!$entryContentLocked)
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="button" class="btn btn-primary btn-loading btn-save-entry"
                                                data-entry-id="{{ $entry->id }}">
                                                <span class="btn-text"><i class="fas fa-save"></i> Save Entry</span>
                                                <span class="btn-spinner d-none">
                                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                                        aria-hidden="true"></span>
                                                    Saving...
                                                </span>
                                            </button>
                                        </div>
                                    @else
                                        <div class="d-flex justify-content-end mt-3">
                                            <span class="text-muted" style="font-size: 12px;">
                                                {{ !$canEditSchemeContent ? 'View only for your account.' : 'This entry is currently locked.' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted" style="padding: 40px 0;">
                        <i class="fas fa-calendar-alt" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0" style="font-size: 15px;">No weekly entries found for this scheme.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Review History (visible to all viewers)                          --}}
    {{-- ================================================================ --}}
    @if ($scheme->workflowAudits->count() > 0)
        @php
            $auditDotColors = [
                'approved' => 'success',
                'submitted' => 'info',
                'supervisor_reviewed' => 'primary',
                'under_review' => 'warning',
                'revision_required' => 'danger',
            ];
        @endphp
        <div class="review-container">
            <div class="review-accent"></div>
            <div class="review-body">
                <div class="section-title" style="margin-top: 0;"><i class="fas fa-history me-2" style="opacity: 0.5;"></i>Review History</div>
                <div class="review-timeline">
                    @foreach ($scheme->workflowAudits->take(10) as $audit)
                        <div class="timeline-item">
                            <div class="timeline-dot dot-{{ $auditDotColors[$audit->to_status] ?? 'secondary' }}"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="timeline-action">
                                        <span class="badge bg-{{ $statusColors[$audit->to_status] ?? 'secondary' }}" style="font-size: 11px;">
                                            {{ ucfirst(str_replace('_', ' ', $audit->action)) }}
                                        </span>
                                    </span>
                                    <span class="timeline-date">
                                        <i class="far fa-clock me-1"></i>{{ $audit->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                                <div class="timeline-actor">
                                    <i class="far fa-user me-1"></i>{{ $audit->actor?->full_name ?? '—' }}
                                </div>
                                @if ($audit->comments)
                                    <div class="timeline-comment">
                                        "{{ $audit->comments }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- HOD Review Panel                                                 --}}
    {{-- ================================================================ --}}
    @can('review', $scheme)
        <div class="review-container">
            <div class="review-accent"></div>
            <div class="review-body">
                <div class="section-title" style="margin-top: 0;"><i class="fas fa-user-tie me-2" style="opacity: 0.5;"></i>HOD Review Actions</div>
                <div class="d-flex gap-2 flex-wrap">
                    @if (in_array($scheme->status, ['submitted', 'supervisor_reviewed']))
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#placeUnderReviewModal">
                            <i class="fas fa-eye"></i> Place Under Review
                        </button>
                    @endif

                    @if ($scheme->status === 'under_review')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#approveSchemeModal">
                            <i class="fas fa-check-circle"></i> Approve
                        </button>

                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#returnRevisionModal">
                            <i class="fas fa-undo"></i> Return for Revision
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endcan

    {{-- ================================================================ --}}
    {{-- Supervisor Review Panel                                           --}}
    {{-- ================================================================ --}}
    @can('supervisorReview', $scheme)
        <div class="review-container">
            <div class="review-accent"></div>
            <div class="review-body">
                <div class="section-title" style="margin-top: 0;"><i class="fas fa-user-check me-2" style="opacity: 0.5;"></i>Supervisor Review Actions</div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#supervisorApproveModal">
                        <i class="fas fa-arrow-right"></i> Forward to HOD
                    </button>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                        data-bs-target="#supervisorReturnModal">
                        <i class="fas fa-undo"></i> Return for Revision
                    </button>
                </div>
            </div>
        </div>
    @endcan

    {{-- ================================================================ --}}
    {{-- Supervisor Approve Modal                                          --}}
    {{-- ================================================================ --}}
    @can('supervisorReview', $scheme)
        <div class="modal fade" id="supervisorApproveModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('schemes.supervisor-approve', $scheme) }}" method="POST" id="supervisor-approve-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-arrow-right me-2"></i>Forward to HOD</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="help-text">
                                <div class="help-content">
                                    This scheme will be forwarded to the HOD for final review and approval.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="supervisor-approve-comments" class="form-label">Comments</label>
                                <textarea class="form-control" id="supervisor-approve-comments" name="comments" rows="3" maxlength="2000"
                                    placeholder="Add a note (optional)..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm btn-loading">
                                <span class="btn-text"><i class="fas fa-arrow-right"></i> Forward to HOD</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Forwarding...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    {{-- ================================================================ --}}
    {{-- Supervisor Return Modal                                           --}}
    {{-- ================================================================ --}}
    @can('supervisorReview', $scheme)
        <div class="modal fade" id="supervisorReturnModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('schemes.supervisor-return', $scheme) }}" method="POST" id="supervisor-return-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Return for Revision</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">Optionally provide comments for the teacher.</p>
                            <div class="mb-3">
                                <label for="supervisor-revision-comments" class="form-label">Revision Comments</label>
                                <textarea class="form-control" id="supervisor-revision-comments" name="comments" rows="4"
                                    maxlength="2000" placeholder="Describe what needs to be revised..."></textarea>
                                @error('comments')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-sm btn-loading">
                                <span class="btn-text"><i class="fas fa-undo"></i> Return for Revision</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Returning...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    {{-- ================================================================ --}}
    {{-- Return for Revision Modal                                         --}}
    {{-- ================================================================ --}}
    @can('review', $scheme)
        @if ($scheme->status === 'under_review')
            <div class="modal fade" id="returnRevisionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('schemes.return-for-revision', $scheme) }}" method="POST"
                            id="return-revision-form">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Return for Revision</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted mb-3">Provide comments explaining what the teacher needs to revise.</p>
                                <div class="mb-3">
                                    <label for="revision-comments" class="form-label">Revision Comments <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="revision-comments" name="comments" rows="4" required minlength="5"
                                        maxlength="2000" placeholder="Describe what needs to be revised..."></textarea>
                                    @error('comments')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger btn-sm btn-loading">
                                    <span class="btn-text"><i class="fas fa-undo"></i> Return for Revision</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Returning...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan

    {{-- ================================================================ --}}
    {{-- Submit for Review Modal                                          --}}
    {{-- ================================================================ --}}
    @can('submit', $scheme)
        <div class="modal fade" id="submitSchemeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('schemes.submit', $scheme) }}" method="POST" id="submit-scheme-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i>Submit for Review</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="help-text">
                                <div class="help-content">
                                    @if (($hasSupervisor ?? false) && !$scheme->hasPassedSupervisorReview())
                                        Your scheme will be sent to your supervisor for review. You will not be able to edit it until the review is complete.
                                    @else
                                        Your scheme will be sent to your HOD for review. You will not be able to edit it until the review is complete.
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="submit-comments" class="form-label">Comments</label>
                                <textarea class="form-control" id="submit-comments" name="comments" rows="3" maxlength="2000"
                                    placeholder="Add a note (optional)..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm btn-loading">
                                <span class="btn-text"><i class="fas fa-paper-plane"></i> Submit for Review</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Submitting...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    {{-- ================================================================ --}}
    {{-- Place Under Review Modal                                         --}}
    {{-- ================================================================ --}}
    @can('review', $scheme)
        @if (in_array($scheme->status, ['submitted', 'supervisor_reviewed']))
            <div class="modal fade" id="placeUnderReviewModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('schemes.place-under-review', $scheme) }}" method="POST" id="place-under-review-form">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Place Under Review</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="help-text">
                                    <div class="help-content">
                                        This scheme will be marked as under active review.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="place-review-comments" class="form-label">Comments</label>
                                    <textarea class="form-control" id="place-review-comments" name="comments" rows="3" maxlength="2000"
                                        placeholder="Add a note (optional)..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning btn-sm btn-loading">
                                    <span class="btn-text"><i class="fas fa-eye"></i> Place Under Review</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Processing...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan

    {{-- ================================================================ --}}
    {{-- Approve Scheme Modal                                             --}}
    {{-- ================================================================ --}}
    @can('review', $scheme)
        @if ($scheme->status === 'under_review')
            <div class="modal fade" id="approveSchemeModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('schemes.approve', $scheme) }}" method="POST" id="approve-scheme-form">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Approve Scheme</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="help-text">
                                    <div class="help-content">
                                        This will approve the scheme. The teacher will still be able to edit entries that are not yet completed.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="approve-comments" class="form-label">Comments</label>
                                    <textarea class="form-control" id="approve-comments" name="comments" rows="3" maxlength="2000"
                                        placeholder="Add approval notes (optional)..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success btn-sm btn-loading">
                                    <span class="btn-text"><i class="fas fa-check-circle"></i> Approve</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Approving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan

    {{-- ================================================================ --}}
    {{-- Delete form (hidden)                                             --}}
    {{-- ================================================================ --}}
    <form id="delete-scheme-form" action="{{ route('schemes.destroy', $scheme) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- ================================================================ --}}
    {{-- Clone Modal                                                       --}}
    {{-- ================================================================ --}}
    <div class="modal fade" id="cloneSchemeModal" tabindex="-1" aria-labelledby="cloneSchemeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cloneSchemeModalLabel">
                        <i class="fas fa-copy me-2"></i>Clone Scheme
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('schemes.clone', $scheme) }}" method="POST" id="clone-scheme-form">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted" style="font-size: 14px;">
                            Clone this scheme into a different term. All weekly entry data will be copied.
                        </p>
                        <div class="mb-3">
                            <label for="clone_term_id" class="form-label">Target Term <span
                                    class="text-danger">*</span></label>
                            @php
                                $defaultCloneTermId = old('term_id');
                                if (empty($defaultCloneTermId)) {
                                    $defaultCloneTermId = optional($terms->firstWhere('id', '!=', $scheme->term_id))
                                        ->id;
                                }
                            @endphp
                            <select name="term_id" id="clone_term_id" class="form-select" required>
                                <option value="">Select Term...</option>
                                @foreach ($terms as $term)
                                    @if ($term->id !== $scheme->term_id)
                                        <option value="{{ $term->id }}"
                                            {{ (string) $defaultCloneTermId === (string) $term->id ? 'selected' : '' }}>
                                            Term {{ $term->term }},{{ $term->year }}
                                        </option>
                                    @else
                                        <option value="{{ $term->id }}" disabled>
                                            Term {{ $term->term }},{{ $term->year }} (current)
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="text-muted mt-1" style="font-size: 12px;">
                                The first available term is pre-selected. If a scheme already exists for that term, an error
                                will be shown.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading" id="btn-clone-submit">
                            <span class="btn-text"><i class="fas fa-copy"></i> Clone Scheme</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                Cloning...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Objective Browser Offcanvas                                       --}}
    {{-- ================================================================ --}}
    @if ($hasPlannerReference)
        <div class="offcanvas offcanvas-end" tabindex="-1" id="objectiveBrowserOffcanvas"
            aria-labelledby="objectiveBrowserOffcanvasLabel" style="width: 600px; max-width: 100vw;">
            <div class="offcanvas-header" style="border-bottom: 1px solid #e2e8f0;">
                <h5 class="offcanvas-title" id="objectiveBrowserOffcanvasLabel" style="font-size: 15px; font-weight: 700; color: #1e293b;">
                    @if ($hasStructuredPlannerSource)
                        <i class="fas fa-book-open me-2 text-muted"></i>{{ $plannerStructure['title'] ?? 'Reference Scheme' }}
                    @else
                        <i class="fas fa-file-pdf me-2 text-danger"></i>{{ $syllabusDocument->title ?? $syllabusDocument->original_name }}
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0" style="display: flex; flex-direction: column;">
                @if ($hasStructuredPlannerSource)
                    @if ($canEditSchemeContent)
                        {{-- Editable mode: show full toolbar --}}
                        <div class="help-text" style="margin: 12px 12px 0; border-radius: 3px;">
                            <div class="help-content">
                                @if ($plannerUsesScheme)
                                    Use the published reference scheme below while planning each week. Choose a target week,
                                    search the reference, and insert the topic, sub-topic, or objectives without manual copying.
                                @else
                                    No published reference scheme is available for this subject right now, so the syllabus is shown instead.
                                @endif
                            </div>
                        </div>
                        <div class="planner-toolbar">
                            <div class="planner-toolbar-grid">
                                <div>
                                    <label for="planner-target-entry" class="planner-toolbar-label">Target week</label>
                                    <div class="planner-target-row">
                                        <select id="planner-target-entry" class="form-select form-select-sm"
                                            style="max-width: 220px;">
                                            @forelse ($sortedEntries as $entry)
                                                <option value="{{ $entry->id }}">Wk {{ $entry->week_number }}</option>
                                            @empty
                                                <option value="">No weekly entries</option>
                                            @endforelse
                                        </select>
                                        <span class="planner-target-chip" id="planner-target-badge">
                                            <i class="fas fa-crosshairs"></i>
                                            <span>{{ $sortedEntries->first() ? 'Wk ' . $sortedEntries->first()->week_number : 'No target' }}</span>
                                        </span>
                                    </div>
                                    <div class="planner-target-hint" id="planner-target-hint">
                                        Planner actions stage content into the selected week. Save the entry once you are satisfied.
                                    </div>
                                </div>

                                <div>
                                    <span class="planner-toolbar-label">Objectives mode</span>
                                    <div class="btn-group planner-mode-toggle" role="group">
                                        <input type="radio" class="btn-check" name="plannerObjectiveMode"
                                            id="planner-mode-replace" value="replace" checked>
                                        <label class="btn btn-outline-secondary" for="planner-mode-replace">
                                            Replace
                                        </label>
                                        <input type="radio" class="btn-check" name="plannerObjectiveMode"
                                            id="planner-mode-append" value="append">
                                        <label class="btn btn-outline-secondary" for="planner-mode-append">
                                            Append
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label for="planner-search" class="planner-toolbar-label">Search reference</label>
                                    <div class="planner-search-wrap">
                                        <i class="fas fa-search"></i>
                                        <input type="search" id="planner-search" class="form-control"
                                            placeholder="Search topics, sub-topics, or objectives">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Read-only mode: search only, no toolbar --}}
                        <div style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0;">
                            <div class="planner-search-wrap">
                                <i class="fas fa-search"></i>
                                <input type="search" id="planner-search" class="form-control form-control-sm"
                                    placeholder="Search topics, sub-topics, or objectives...">
                            </div>
                        </div>
                    @endif

                    <div class="planner-content">
                        @include('schemes.partials.structured-syllabus-outline', [
                            'structure' => $plannerStructure,
                            'syllabusDocument' => $plannerUsesScheme ? null : $syllabusDocument,
                            'plannerReadonly' => true,
                        ])
                        <div class="planner-empty-search d-none" id="planner-empty-state">
                            No reference topics match your search.
                        </div>
                    </div>
                @else
                    <iframe id="offcanvasPdfIframe" src="" style="flex: 1; width: 100%; border: none;"
                        title="Syllabus PDF"></iframe>
                @endif
            </div>
        </div>
    @endif
    {{-- ================================================================ --}}
    {{-- Syllabus PDF Viewer Modal                                       --}}
    {{-- ================================================================ --}}
    @if ($syllabusDocument)
        <div class="modal fade" id="syllabusPdfModal" tabindex="-1" aria-labelledby="syllabusPdfModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="syllabusPdfModalLabel">
                            <i
                                class="fas fa-file-pdf me-2"></i>{{ $syllabusDocument->title ?? $syllabusDocument->original_name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe id="syllabusPdfIframe" src="" style="width: 100%; height: 75vh; border: none;"
                            title="Syllabus PDF"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('script')
    <script>
        (function() {
            'use strict';

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const CSRF_TOKEN = csrfMeta ? csrfMeta.content : '';
            const SCHEME_ID = {{ $scheme->id }};
            const SYLLABUS_PREVIEW_URL = @json(isset($syllabusDocument, $syllabus) ? route('syllabi.document.preview', $syllabus) : '');

            // Workflow locking flags
            const isFullyLocked = {{ $isFullyLocked ? 'true' : 'false' }};
            const isApproved = {{ $isApproved ? 'true' : 'false' }};
            const canEditSchemeContent = {{ $canEditSchemeContent ? 'true' : 'false' }};
            const isLocked = isFullyLocked; // backward compat
            const learningObjectivesEditors = {};
            const plannerState = {
                targetEntryId: null,
            };
            const entriesContainer = document.getElementById('entries-container');
            const plannerTargetSelect = document.getElementById('planner-target-entry');
            const plannerTargetBadge = document.getElementById('planner-target-badge');
            const plannerTargetHint = document.getElementById('planner-target-hint');
            const plannerSearchInput = document.getElementById('planner-search');
            const plannerEmptyState = document.getElementById('planner-empty-state');

            // ================================================================
            // CKEditor: Learning Objectives (initialize per tab/panel)
            // ================================================================
            function initLearningObjectivesEditors(scope) {
                if (typeof ClassicEditor === 'undefined') {
                    return;
                }

                const root = scope || document;
                root.querySelectorAll('textarea.learning-objectives-input').forEach(function(textarea) {
                    const row = textarea.closest('.entry-row');
                    const entryId = row ? row.dataset.entryId : textarea.name;

                    if (!entryId || learningObjectivesEditors[entryId]) {
                        return;
                    }

                    ClassicEditor
                        .create(textarea, {
                            toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                        })
                        .then(function(editor) {
                            let isBootstrapping = true;
                            learningObjectivesEditors[entryId] = editor;

                            // Keep textarea value in sync for existing payload collector logic.
                            textarea.value = editor.getData();
                            editor.model.document.on('change:data', function() {
                                textarea.value = editor.getData();
                                if (!isBootstrapping) {
                                    markEntryDirty(entryId);
                                }
                            });
                            isBootstrapping = false;

                            const editable = editor.ui.view.editable.element;
                            if (editable) {
                                editable.style.minHeight = '260px';
                            }

                            if (!canEditSchemeContent || isFullyLocked) {
                                editor.enableReadOnlyMode('scheme-lock');
                            } else if (isApproved && row && row.dataset.entryStatus === 'completed') {
                                editor.enableReadOnlyMode('scheme-lock');
                            }
                        })
                        .catch(function(error) {
                            console.error('CKEditor init error for entry ' + entryId + ':', error);
                        });
                });
            }

            // ================================================================
            // Helper: showToast
            // ================================================================
            function showToast(message, icon) {
                icon = icon || 'success';
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon,
                    title: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
            }

            // ================================================================
            // Helper: escapeHtml
            // ================================================================
            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str || '';
                return div.innerHTML;
            }

            // ================================================================
            // Helper: show/clear entry errors
            // ================================================================
            function showEntryErrors(entryId, errors) {
                const container = document.getElementById('entry-errors-' + entryId);
                if (!container) return;
                let html = '';
                if (typeof errors === 'string') {
                    html = '<div class="field-error">' + escapeHtml(errors) + '</div>';
                } else {
                    Object.values(errors).forEach(function(msgs) {
                        (Array.isArray(msgs) ? msgs : [msgs]).forEach(function(msg) {
                            html += '<div class="field-error">' + escapeHtml(msg) + '</div>';
                        });
                    });
                }
                container.innerHTML = html;
            }

            function clearEntryErrors(entryId) {
                const container = document.getElementById('entry-errors-' + entryId);
                if (container) container.innerHTML = '';
            }

            function normalizePlannerText(value) {
                return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
            }

            function getEntryRow(entryId) {
                return document.getElementById('entry-row-' + entryId);
            }

            function getActiveEntryId() {
                const activeTab = document.querySelector('#week-tabs .week-tab.active');

                return activeTab ? parseInt(activeTab.dataset.entryId, 10) : null;
            }

            function getEntryWeekLabel(entryId) {
                const row = getEntryRow(entryId);

                return row ? (row.dataset.weekLabel || ('Wk ' + entryId)) : ('Wk ' + entryId);
            }

            function markEntryDirty(entryId) {
                const indicator = document.getElementById('save-indicator-' + entryId);
                if (!indicator) return;

                indicator.innerHTML = '<i class="fas fa-pen"></i> Unsaved changes';
                indicator.classList.add('visible', 'pending');
            }

            function showEntrySaved(entryId) {
                const indicator = document.getElementById('save-indicator-' + entryId);
                if (!indicator) return;

                indicator.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
                indicator.classList.remove('pending');
                indicator.classList.add('visible');

                window.setTimeout(function() {
                    if (!indicator.classList.contains('pending')) {
                        indicator.classList.remove('visible');
                    }
                }, 2500);
            }

            function getEntryFieldValue(entryId, fieldName) {
                const row = getEntryRow(entryId);
                if (!row) return '';

                if (fieldName === 'learning_objectives' && learningObjectivesEditors[entryId]) {
                    return learningObjectivesEditors[entryId].getData();
                }

                const field = row.querySelector('[name="' + fieldName + '"]');

                return field ? field.value : '';
            }

            function setEntryFieldValue(entryId, fieldName, value) {
                const row = getEntryRow(entryId);
                if (!row) return;

                const nextValue = value || '';
                const field = row.querySelector('[name="' + fieldName + '"]');
                if (!field) return;

                if (fieldName === 'learning_objectives' && learningObjectivesEditors[entryId]) {
                    learningObjectivesEditors[entryId].setData(nextValue);
                    field.value = nextValue;
                    return;
                }

                field.value = nextValue;
            }

            function getObjectiveIdsInput(entryId) {
                const row = getEntryRow(entryId);

                return row ? row.querySelector('.entry-objective-ids') : null;
            }

            function readEntryObjectiveIds(entryId) {
                const input = getObjectiveIdsInput(entryId);
                if (!input) return [];

                try {
                    return Array.from(new Set(JSON.parse(input.value || '[]').map(function(id) {
                        return parseInt(id, 10);
                    }).filter(Boolean)));
                } catch (error) {
                    return [];
                }
            }

            function writeEntryObjectiveIds(entryId, objectiveIds) {
                const input = getObjectiveIdsInput(entryId);
                if (!input) return;

                input.value = JSON.stringify(Array.from(new Set((objectiveIds || []).map(function(id) {
                    return parseInt(id, 10);
                }).filter(Boolean))));
            }

            function readRenderedLinkedObjectives(entryId) {
                const area = document.getElementById('entry-objectives-' + entryId);
                if (!area) return [];

                return Array.from(area.querySelectorAll('.objective-tag')).map(function(tag) {
                    return {
                        id: parseInt(tag.dataset.objectiveId, 10),
                        code: tag.dataset.objectiveCode || '',
                        objective_text: tag.dataset.objectiveText || '',
                    };
                }).filter(function(objective) {
                    return objective.id;
                });
            }

            function renderEntryObjectiveTags(entryId, objectives) {
                const area = document.getElementById('entry-objectives-' + entryId);
                if (!area) return;

                if (!Array.isArray(objectives) || objectives.length === 0) {
                    area.innerHTML = '<div class="objective-empty-note">No linked syllabus objectives yet. Use {{ $plannerButtonLabel }} to add them.</div>';
                    return;
                }

                area.innerHTML = objectives.map(function(objective) {
                    const code = objective.code ? '<span class="me-1">' + escapeHtml(objective.code) + '</span>' : '';
                    const fullText = String(objective.objective_text || '');
                    const label = escapeHtml(fullText.length > 120 ? fullText.slice(0, 117) + '...' : fullText);
                    const rawText = escapeHtml(fullText);

                    return '<span class="objective-tag" data-objective-id="' + objective.id + '" data-objective-text="' + rawText +
                        '" data-objective-code="' + escapeHtml(objective.code || '') + '">' + code + label +
                        '<button type="button" class="btn-remove-obj" aria-label="Remove objective"><i class="fas fa-times"></i></button></span>';
                }).join('');
            }

            function setEntryLinkedObjectives(entryId, objectives) {
                const normalized = Array.from(new Map((objectives || []).filter(function(objective) {
                    return objective && objective.id;
                }).map(function(objective) {
                    return [parseInt(objective.id, 10), {
                        id: parseInt(objective.id, 10),
                        code: objective.code || '',
                        objective_text: objective.objective_text || '',
                    }];
                })).values());

                writeEntryObjectiveIds(entryId, normalized.map(function(objective) {
                    return objective.id;
                }));
                renderEntryObjectiveTags(entryId, normalized);
            }

            function stripHtmlToText(html) {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html || '';

                return normalizePlannerText(wrapper.textContent || '');
            }

            function buildObjectiveListHtml(objectiveTexts) {
                const cleaned = Array.from(new Set((objectiveTexts || []).map(function(text) {
                    return String(text || '').trim();
                }).filter(Boolean)));

                if (!cleaned.length) {
                    return '';
                }

                return '<ul>' + cleaned.map(function(text) {
                    return '<li>' + escapeHtml(text) + '</li>';
                }).join('') + '</ul>';
            }

            function appendObjectiveHtml(existingHtml, objectiveTexts) {
                const incoming = (objectiveTexts || []).map(function(text) {
                    return String(text || '').trim();
                }).filter(Boolean);

                if (!incoming.length) {
                    return existingHtml || '';
                }

                const parser = new DOMParser();
                const doc = parser.parseFromString('<div id="planner-objective-root">' + (existingHtml || '') + '</div>', 'text/html');
                const root = doc.getElementById('planner-objective-root');
                const lists = root.querySelectorAll('ul,ol');
                const list = lists.length ? lists[lists.length - 1] : doc.createElement('ul');

                if (!lists.length) {
                    root.appendChild(list);
                }

                incoming.forEach(function(text) {
                    const item = doc.createElement('li');
                    item.textContent = text;
                    list.appendChild(item);
                });

                return root.innerHTML;
            }

            function canEditEntry(entryId, showWarning) {
                if (!canEditSchemeContent) {
                    if (showWarning !== false) {
                        showToast('This scheme is view only for your account.', 'warning');
                    }
                    return false;
                }

                if (isFullyLocked) {
                    if (showWarning !== false) {
                        showToast('This scheme is locked and cannot be edited.', 'warning');
                    }
                    return false;
                }

                const row = getEntryRow(entryId);
                if (!row) {
                    if (showWarning !== false) {
                        showToast('Select a weekly entry first.', 'warning');
                    }
                    return false;
                }

                if (isApproved && row.dataset.entryStatus === 'completed') {
                    if (showWarning !== false) {
                        showToast('Completed entries cannot be edited.', 'warning');
                    }
                    return false;
                }

                return true;
            }

            function getPlannerObjectiveMode() {
                const selected = document.querySelector('input[name="plannerObjectiveMode"]:checked');

                return selected ? selected.value : 'replace';
            }

            function parsePlannerCardPayload(card) {
                if (!card) return null;
                if (card._plannerPayload) return card._plannerPayload;

                const script = card.querySelector('.planner-topic-payload');
                if (!script) return null;

                try {
                    card._plannerPayload = JSON.parse(script.textContent || '{}');
                } catch (error) {
                    card._plannerPayload = null;
                }

                return card._plannerPayload;
            }

            function updatePlannerTargetUi() {
                const targetEntryId = plannerState.targetEntryId;
                const targetLabel = targetEntryId ? getEntryWeekLabel(targetEntryId) : 'No target';

                if (plannerTargetBadge) {
                    const badgeLabel = plannerTargetBadge.querySelector('span');
                    if (badgeLabel) {
                        badgeLabel.textContent = targetLabel;
                    } else {
                        plannerTargetBadge.textContent = targetLabel;
                    }
                }

                if (plannerTargetHint) {
                    plannerTargetHint.textContent = targetEntryId
                        ? 'Planner actions stage content into ' + targetLabel + '. Save the entry once you are satisfied.'
                        : 'Select a week before inserting reference content.';
                }

                if (plannerTargetSelect) {
                    plannerTargetSelect.value = targetEntryId ? String(targetEntryId) : '';
                }
            }

            function setPlannerTarget(entryId, options) {
                const nextEntryId = parseInt(entryId, 10) || getActiveEntryId();
                plannerState.targetEntryId = nextEntryId || null;

                if (!options || options.activateTab !== false) {
                    const tab = document.querySelector('#week-tabs .week-tab[data-entry-id="' + plannerState.targetEntryId + '"]');
                    if (tab) {
                        activateWeekTab(tab);
                    }
                }

                updatePlannerTargetUi();
            }

            function applyPlannerSelection(selection, options) {
                const entryId = plannerState.targetEntryId || getActiveEntryId();
                if (!entryId || !canEditEntry(entryId)) {
                    return;
                }

                const mode = (options && options.objectiveMode) || getPlannerObjectiveMode();
                const row = getEntryRow(entryId);
                const objectiveSelection = Array.from(new Map((selection.objectives || []).filter(function(objective) {
                    return objective && normalizePlannerText(objective.text);
                }).map(function(objective) {
                    return [normalizePlannerText(objective.text), {
                        text: String(objective.text || '').trim(),
                        localObjectiveId: objective.local_objective_id ? parseInt(objective.local_objective_id, 10) : null,
                    }];
                })).values());

                if (!row) {
                    return;
                }

                if (options && options.replaceTopic) {
                    setEntryFieldValue(entryId, 'topic', selection.unit_title || '');
                }

                if (options && options.replaceSubTopic) {
                    setEntryFieldValue(entryId, 'sub_topic', selection.sub_topic_title || selection.topic_title || '');
                }

                if (options && options.assignLocalTopic) {
                    const topicInput = row.querySelector('.entry-syllabus-topic-id');
                    if (topicInput) {
                        topicInput.value = selection.local_topic_id ? String(selection.local_topic_id) : '';
                    }
                }

                let insertedObjectives = 0;
                if (options && options.applyObjectives) {
                    const currentHtml = getEntryFieldValue(entryId, 'learning_objectives');
                    const currentText = stripHtmlToText(currentHtml);
                    const incomingTexts = objectiveSelection.map(function(objective) {
                        return objective.text;
                    });

                    let objectiveHtml = currentHtml;
                    if (mode === 'replace' || !currentHtml.trim()) {
                        objectiveHtml = buildObjectiveListHtml(incomingTexts);
                        insertedObjectives = incomingTexts.length;
                    } else {
                        const missingTexts = objectiveSelection.filter(function(objective) {
                            return !currentText.includes(normalizePlannerText(objective.text));
                        }).map(function(objective) {
                            return objective.text;
                        });
                        insertedObjectives = missingTexts.length;
                        objectiveHtml = missingTexts.length ? appendObjectiveHtml(currentHtml, missingTexts) : currentHtml;
                    }

                    if (objectiveHtml !== currentHtml) {
                        setEntryFieldValue(entryId, 'learning_objectives', objectiveHtml);
                    }

                    const linkedObjectives = objectiveSelection.filter(function(objective) {
                        return objective.localObjectiveId;
                    }).map(function(objective) {
                        return {
                            id: objective.localObjectiveId,
                            code: '',
                            objective_text: objective.text,
                        };
                    });

                    if (mode === 'replace') {
                        setEntryLinkedObjectives(entryId, linkedObjectives);
                    } else if (linkedObjectives.length) {
                        setEntryLinkedObjectives(entryId, readRenderedLinkedObjectives(entryId).concat(linkedObjectives));
                    }
                }

                markEntryDirty(entryId);

                const feedback = [];
                if (options && options.replaceTopic) {
                    feedback.push('topic');
                }
                if (options && options.replaceSubTopic) {
                    feedback.push('sub-topic');
                }
                if (options && options.applyObjectives && insertedObjectives > 0) {
                    feedback.push(insertedObjectives + ' objective' + (insertedObjectives === 1 ? '' : 's'));
                }

                showToast(feedback.length
                    ? feedback.join(', ') + ' inserted into ' + getEntryWeekLabel(entryId) + '. Save entry when ready.'
                    : 'Selection already exists in ' + getEntryWeekLabel(entryId) + '.', feedback.length ? 'success' : 'info');
            }

            function filterPlannerCards(query) {
                const normalizedQuery = normalizePlannerText(query);
                const cards = Array.from(document.querySelectorAll('.syllabus-topic-planner-card'));
                let visibleCards = 0;

                cards.forEach(function(card) {
                    const matches = !normalizedQuery || normalizePlannerText(card.textContent).includes(normalizedQuery);
                    card.classList.toggle('d-none', !matches);
                    if (matches) {
                        visibleCards += 1;
                    }
                });

                document.querySelectorAll('.planner-unit').forEach(function(unit) {
                    const hasVisibleCards = unit.querySelector('.syllabus-topic-planner-card:not(.d-none)');
                    unit.classList.toggle('d-none', !hasVisibleCards);
                    if (normalizedQuery && hasVisibleCards) {
                        unit.open = true;
                    }
                });

                document.querySelectorAll('.planner-section').forEach(function(section) {
                    const hasVisibleCards = section.querySelector('.syllabus-topic-planner-card:not(.d-none)');
                    section.classList.toggle('d-none', !hasVisibleCards);
                    if (normalizedQuery && hasVisibleCards) {
                        section.open = true;
                    }
                });

                if (plannerEmptyState) {
                    plannerEmptyState.classList.toggle('d-none', visibleCards > 0);
                }
            }

            if (plannerTargetSelect) {
                plannerTargetSelect.addEventListener('change', function() {
                    setPlannerTarget(plannerTargetSelect.value);
                });
            }

            if (plannerSearchInput) {
                plannerSearchInput.addEventListener('input', function() {
                    filterPlannerCards(plannerSearchInput.value);
                });
            }

            document.querySelectorAll('.btn-open-syllabus-planner').forEach(function(button) {
                button.addEventListener('click', function() {
                    setPlannerTarget(button.dataset.entryId || getActiveEntryId(), {
                        activateTab: !!button.dataset.entryId
                    });
                });
            });

            document.addEventListener('click', function(e) {
                const actionButton = e.target.closest('.planner-insert-action, .planner-group-action, .planner-objective-insert');
                if (!actionButton) {
                    return;
                }

                const card = actionButton.closest('.syllabus-topic-planner-card');
                const payload = parsePlannerCardPayload(card);
                if (!payload) {
                    showToast('Unable to read the reference topic.', 'error');
                    return;
                }

                const action = actionButton.dataset.action;
                if (action === 'plan') {
                    applyPlannerSelection({
                        unit_title: payload.unit_title,
                        topic_title: payload.topic_title,
                        sub_topic_title: payload.sub_topic_title,
                        local_topic_id: payload.local_topic_id,
                        objectives: payload.all_objectives || [],
                    }, {
                        replaceTopic: true,
                        replaceSubTopic: true,
                        assignLocalTopic: true,
                        applyObjectives: true,
                    });
                    return;
                }

                if (action === 'topic') {
                    applyPlannerSelection({
                        unit_title: payload.unit_title,
                        topic_title: payload.topic_title,
                        sub_topic_title: payload.sub_topic_title,
                        local_topic_id: payload.local_topic_id,
                    }, {
                        replaceTopic: true,
                    });
                    return;
                }

                if (action === 'sub-topic') {
                    applyPlannerSelection({
                        unit_title: payload.unit_title,
                        topic_title: payload.topic_title,
                        sub_topic_title: payload.sub_topic_title,
                        local_topic_id: payload.local_topic_id,
                    }, {
                        replaceSubTopic: true,
                        assignLocalTopic: true,
                    });
                    return;
                }

                if (action === 'all-objectives' || action === 'group-objectives') {
                    const objectives = action === 'group-objectives'
                        ? (((payload.objective_groups || [])[parseInt(actionButton.dataset.groupIndex, 10)] || {}).objectives || [])
                        : (payload.all_objectives || []);

                    applyPlannerSelection({
                        unit_title: payload.unit_title,
                        topic_title: payload.topic_title,
                        sub_topic_title: payload.sub_topic_title,
                        local_topic_id: payload.local_topic_id,
                        objectives: objectives,
                    }, {
                        assignLocalTopic: true,
                        applyObjectives: true,
                    });
                    return;
                }

                if (action === 'single-objective') {
                    const groupIndex = parseInt(actionButton.dataset.groupIndex, 10);
                    const objectiveIndex = parseInt(actionButton.dataset.objectiveIndex, 10);
                    const group = (payload.objective_groups || [])[groupIndex] || {};
                    const objective = (group.objectives || [])[objectiveIndex];

                    if (!objective) {
                        showToast('Objective not found.', 'error');
                        return;
                    }

                    applyPlannerSelection({
                        unit_title: payload.unit_title,
                        topic_title: payload.topic_title,
                        sub_topic_title: payload.sub_topic_title,
                        local_topic_id: payload.local_topic_id,
                        objectives: [objective],
                    }, {
                        assignLocalTopic: true,
                        applyObjectives: true,
                        objectiveMode: 'append',
                    });
                }
            });

            // ================================================================
            // Collect entry field values
            // ================================================================
            function collectEntryPayload(entryId) {
                const row = document.getElementById('entry-row-' + entryId);
                if (!row) return {};

                const fields = {};
                row.querySelectorAll('.entry-field-input').forEach(function(el) {
                    fields[el.name] = el.value;
                });

                if (learningObjectivesEditors[entryId]) {
                    fields.learning_objectives = learningObjectivesEditors[entryId].getData();
                }

                // Status from the header select
                const statusSelect = row.querySelector('.status-select');
                if (statusSelect) {
                    fields.status = statusSelect.value;
                }

                // Syllabus topic ID from hidden input
                const topicIdInput = row.querySelector('.entry-syllabus-topic-id');
                if (topicIdInput) {
                    fields.syllabus_topic_id = topicIdInput.value || null;
                }

                fields.objective_ids = readEntryObjectiveIds(entryId);

                return fields;
            }

            // ================================================================
            // saveEntry(entryId)
            // ================================================================
            function saveEntry(entryId) {
                if (!canEditSchemeContent) {
                    showToast('This scheme is view only for your account.', 'warning');
                    return;
                }

                if (isFullyLocked) {
                    showToast('This scheme is locked and cannot be edited.', 'warning');
                    return;
                }

                const row = document.getElementById('entry-row-' + entryId);

                if (isApproved && row && row.dataset.entryStatus === 'completed') {
                    showToast('Completed entries cannot be edited.', 'warning');
                    return;
                }

                const saveBtn = row ? row.querySelector('.btn-save-entry') : null;

                clearEntryErrors(entryId);

                if (row) row.classList.add('saving');
                if (saveBtn) {
                    saveBtn.classList.add('loading');
                    saveBtn.disabled = true;
                }

                const payload = collectEntryPayload(entryId);

                fetch('/schemes/' + SCHEME_ID + '/entries/' + entryId, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: JSON.stringify(payload),
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            return {
                                status: res.status,
                                data: data
                            };
                        });
                    })
                    .then(function(result) {
                        if (result.status === 422) {
                            showEntryErrors(entryId, result.data.errors || result.data.message ||
                                'Validation error.');
                            return;
                        }
                        if (result.status >= 400) {
                            showEntryErrors(entryId, result.data.message || 'Error saving entry.');
                            showToast('Error saving entry.', 'error');
                            return;
                        }

                        // Sync entry status data attribute from saved entry
                        if (row && result.data.entry && result.data.entry.status) {
                            row.dataset.entryStatus = result.data.entry.status;

                            // If approved scheme and entry just became completed, lock it
                            if (isApproved && result.data.entry.status === 'completed') {
                                disableEntryRow(row);
                            }
                        }

                        if (row && result.data.entry) {
                            const topicIdInput = row.querySelector('.entry-syllabus-topic-id');
                            if (topicIdInput) {
                                topicIdInput.value = result.data.entry.syllabus_topic_id || '';
                            }
                        }

                        if (result.data.entry && Array.isArray(result.data.entry.objectives)) {
                            setEntryLinkedObjectives(entryId, result.data.entry.objectives);
                        }

                        showEntrySaved(entryId);
                        showToast('Entry saved.');
                    })
                    .catch(function() {
                        showEntryErrors(entryId, 'Network error. Please try again.');
                        showToast('Error saving entry.', 'error');
                    })
                    .finally(function() {
                        if (row) row.classList.remove('saving');
                        if (saveBtn) {
                            saveBtn.classList.remove('loading');
                            saveBtn.disabled = false;
                        }
                    });
            }

            // ================================================================
            // Event delegation on #entries-container
            // ================================================================
            if (entriesContainer) {
                entriesContainer.addEventListener('click', function(e) {
                    const button = e.target.closest('button');
                    if (!button) return;

                    if (button.classList.contains('btn-save-entry')) {
                        const entryId = parseInt(button.dataset.entryId, 10);
                        saveEntry(entryId);
                        return;
                    }

                    if (button.classList.contains('btn-remove-obj')) {
                        const row = button.closest('.entry-row');
                        if (!row) return;

                        const entryId = parseInt(row.dataset.entryId, 10);
                        if (!canEditEntry(entryId)) {
                            return;
                        }

                        const tag = button.closest('.objective-tag');
                        if (!tag) return;

                        const remainingObjectives = readRenderedLinkedObjectives(entryId).filter(function(objective) {
                            return objective.id !== parseInt(tag.dataset.objectiveId, 10);
                        });

                        setEntryLinkedObjectives(entryId, remainingObjectives);
                        markEntryDirty(entryId);
                        showToast('Objective link removed. Save entry when ready.', 'info');
                    }
                });
            }

            // ================================================================
            // Status dropdown change: auto-save entry
            // ================================================================
            if (entriesContainer) {
                entriesContainer.addEventListener('change', function(e) {
                    if (e.target && e.target.classList.contains('status-select')) {
                        const entryId = parseInt(e.target.dataset.entryId, 10);
                        saveEntry(entryId);
                    }
                });

                entriesContainer.addEventListener('input', function(e) {
                    const row = e.target.closest('.entry-row');
                    if (!row || !e.target.classList.contains('entry-field-input')) {
                        return;
                    }

                    markEntryDirty(row.dataset.entryId);
                });
            }

            // ================================================================
            // Delete scheme: SweetAlert2 confirm
            // ================================================================
            const deleteBtn = document.getElementById('btn-delete-scheme');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Delete this scheme?',
                        text: 'This will permanently remove the scheme and all its entries. This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel',
                    }).then(function(result) {
                        if (!result.isConfirmed) return;
                        document.getElementById('delete-scheme-form').submit();
                    });
                });
            }

            // ================================================================
            // Clone form: btn-loading on submit
            // ================================================================
            const cloneForm = document.getElementById('clone-scheme-form');
            if (cloneForm) {
                cloneForm.addEventListener('submit', function() {
                    const submitBtn = cloneForm.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }

            // ================================================================
            // Lock: disable save buttons, status dropdowns, and entry inputs
            // ================================================================
            function disableEntryRow(row) {
                var saveBtn = row.querySelector('.btn-save-entry');
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.title = 'Editing locked — entry is completed';
                }
                var statusSel = row.querySelector('.status-select');
                if (statusSel) {
                    statusSel.disabled = true;
                }
                row.querySelectorAll('.entry-field-input').forEach(function(input) {
                    input.readOnly = true;
                    input.style.backgroundColor = '#f9fafb';
                });
                var entryId = row.dataset.entryId;
                if (entryId && learningObjectivesEditors[entryId]) {
                    learningObjectivesEditors[entryId].enableReadOnlyMode('scheme-lock');
                }
            }

            if (!canEditSchemeContent || isFullyLocked) {
                document.querySelectorAll('.btn-save-entry').forEach(function(btn) {
                    btn.disabled = true;
                    btn.title = canEditSchemeContent
                        ? 'Editing locked — scheme is {{ str_replace('_', ' ', $scheme->status) }}'
                        : 'View only — only the scheme owner or admin can edit';
                });
                document.querySelectorAll('.status-select').forEach(function(sel) {
                    sel.disabled = true;
                });
                document.querySelectorAll('.entry-field-input').forEach(function(input) {
                    input.readOnly = true;
                    input.style.backgroundColor = '#f9fafb';
                });
            } else if (isApproved) {
                document.querySelectorAll('.entry-row').forEach(function(row) {
                    if (row.dataset.entryStatus === 'completed') {
                        disableEntryRow(row);
                    }
                });
            }

            // ================================================================
            // Workflow modal forms: btn-loading on submit
            // ================================================================
            ['submit-scheme-form', 'place-under-review-form', 'approve-scheme-form', 'return-revision-form', 'supervisor-approve-form', 'supervisor-return-form'].forEach(function(formId) {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', function() {
                        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                        if (submitBtn) {
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                    });
                }
            });

            // ================================================================
            // Week tabs: switching
            // ================================================================
            function activateWeekTab(tab) {
                if (!tab) return;
                var targetId = tab.getAttribute('data-tab-target');
                if (!targetId) return;

                // Deactivate all tabs and panels
                document.querySelectorAll('.week-tab').forEach(function(t) {
                    t.classList.remove('active');
                });
                document.querySelectorAll('.week-tab-panel').forEach(function(p) {
                    p.classList.remove('active');
                });

                // Activate selected tab and panel with re-triggered animation
                tab.classList.add('active');
                var panel = document.getElementById(targetId);
                if (panel) {
                    // Force animation restart by removing then re-adding active class
                    panel.style.animation = 'none';
                    panel.offsetHeight; // trigger reflow
                    panel.style.animation = '';
                    panel.classList.add('active');
                    initLearningObjectivesEditors(panel);
                }
            }

            document.querySelectorAll('#week-tabs .week-tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    activateWeekTab(tab);
                    setPlannerTarget(tab.dataset.entryId, {
                        activateTab: false
                    });
                });
            });

            // Initialize editor for whichever tab is active on first render.
            var initialPanel = document.querySelector('.week-tab-panel.active');
            if (initialPanel) {
                initLearningObjectivesEditors(initialPanel);
            }
            setPlannerTarget(getActiveEntryId(), {
                activateTab: false
            });

            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"], [data-tooltip="true"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl, { trigger: 'hover', delay: { show: 300, hide: 0 } });
            });

            // ================================================================
            // Week tabs: scroll buttons for overflow
            // ================================================================
            var tabScroll = document.getElementById('week-tabs-scroll');
            var scrollLeftBtn = document.getElementById('tab-scroll-left');
            var scrollRightBtn = document.getElementById('tab-scroll-right');

            function updateScrollButtons() {
                if (!tabScroll || !scrollLeftBtn || !scrollRightBtn) return;
                var canScrollLeft = tabScroll.scrollLeft > 0;
                var canScrollRight = tabScroll.scrollLeft + tabScroll.clientWidth < tabScroll.scrollWidth - 1;

                scrollLeftBtn.classList.toggle('visible', canScrollLeft);
                scrollRightBtn.classList.toggle('visible', canScrollRight);
            }

            if (tabScroll) {
                tabScroll.addEventListener('scroll', updateScrollButtons);
                window.addEventListener('resize', updateScrollButtons);
                updateScrollButtons();
            }

            if (scrollLeftBtn) {
                scrollLeftBtn.addEventListener('click', function() {
                    tabScroll.scrollBy({
                        left: -200,
                        behavior: 'smooth'
                    });
                });
            }

            if (scrollRightBtn) {
                scrollRightBtn.addEventListener('click', function() {
                    tabScroll.scrollBy({
                        left: 200,
                        behavior: 'smooth'
                    });
                });
            }

            // ================================================================
            // Update tab dot color when status changes
            // ================================================================
            var statusDotMap = {
                planned: 'tab-dot-planned',
                in_progress: 'tab-dot-in_progress',
                completed: 'tab-dot-completed',
                skipped: 'tab-dot-skipped',
            };

            document.getElementById('entries-container').addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('status-select')) {
                    var entryId = e.target.dataset.entryId;
                    var newStatus = e.target.value;
                    var dot = document.getElementById('tab-dot-' + entryId);
                    if (dot) {
                        dot.className = 'tab-dot ' + (statusDotMap[newStatus] || 'tab-dot-planned');
                    }
                }
            });

            // ================================================================
            // Syllabus PDF Modal: lazy-load iframe on open, clear on close
            // ================================================================
            var syllabusPdfModal = document.getElementById('syllabusPdfModal');
            if (syllabusPdfModal) {
                syllabusPdfModal.addEventListener('show.bs.modal', function() {
                    var iframe = document.getElementById('syllabusPdfIframe');
                    if (iframe && SYLLABUS_PREVIEW_URL) {
                        iframe.setAttribute('src', SYLLABUS_PREVIEW_URL);
                    }
                });
                syllabusPdfModal.addEventListener('hidden.bs.modal', function() {
                    var iframe = document.getElementById('syllabusPdfIframe');
                    if (iframe) iframe.removeAttribute('src');
                });
            }

            // ================================================================
            // Offcanvas PDF pane: lazy-load on open, clear on close
            // ================================================================
            var objOffcanvas = document.getElementById('objectiveBrowserOffcanvas');
            var offcanvasPdfIframe = document.getElementById('offcanvasPdfIframe');
            if (objOffcanvas) {
                objOffcanvas.addEventListener('show.bs.offcanvas', function(event) {
                    var trigger = event.relatedTarget;
                    setPlannerTarget(trigger && trigger.dataset && trigger.dataset.entryId ? trigger.dataset.entryId : getActiveEntryId(), {
                        activateTab: !!(trigger && trigger.dataset && trigger.dataset.entryId)
                    });

                    if (offcanvasPdfIframe && SYLLABUS_PREVIEW_URL) {
                        offcanvasPdfIframe.setAttribute('src', SYLLABUS_PREVIEW_URL + '#toolbar=1&navpanes=0');
                    }
                });
                objOffcanvas.addEventListener('hidden.bs.offcanvas', function() {
                    if (offcanvasPdfIframe) {
                        offcanvasPdfIframe.removeAttribute('src');
                    }
                });
            }

        })();
    </script>
@endsection
