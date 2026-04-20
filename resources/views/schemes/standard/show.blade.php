@extends('layouts.master')
@section('title')
    Standard Scheme — {{ $standardScheme->subject?->name }}
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        .schemes-container { box-shadow: none; }

        .schemes-header {
            position: relative;
            overflow: hidden;
            padding: 32px 32px 28px;
        }
        .schemes-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.07) 1px, transparent 0);
            background-size: 24px 24px;
            pointer-events: none;
        }
        .schemes-header > * { position: relative; z-index: 1; }
        .schemes-header h3 { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1.2; }

        .stat-item { padding: 10px 0; }
        .stat-item h4 { font-size: 1.5rem; font-weight: 700; }
        .stat-item small { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }

        .status-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .status-toolbar-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            min-width: 0;
        }
        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            margin-left: auto;
        }
        .header-actions .btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .header-actions form { margin: 0; }
        .entry-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
            margin-top: 20px;
        }

        .week-tabs-wrapper {
            display: flex;
            align-items: center;
            gap: 0;
            border-bottom: 2px solid #e5e7eb;
            overflow-x: auto;
            scrollbar-width: thin;
            padding: 0 4px;
        }
        .week-tab {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .week-tab:hover { color: #3b82f6; background: rgba(59,130,246,0.04); }
        .week-tab.active { color: #3b82f6; border-bottom-color: #3b82f6; font-weight: 600; }

        .week-tab-panel { display: none; animation: tabFadeIn 0.3s ease; }
        .week-tab-panel.active { display: block; }
        @keyframes tabFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        .entry-row { padding: 20px 0; }
        .entry-field { margin-bottom: 16px; }
        .entry-field label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; display: block; }
        .field-error { color: #dc3545; font-size: 12px; margin-top: 4px; }

        .objective-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            margin: 3px;
        }
        .objective-tag .remove-objective {
            cursor: pointer;
            color: #dc3545;
            font-weight: 700;
            margin-left: 4px;
        }

        .contributors-panel {
            margin-top: 24px;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }
        .contributors-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .contributors-header-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.01em;
        }
        .contributors-count {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            background: #e2e8f0;
            padding: 2px 8px;
            border-radius: 3px;
        }
        .contributors-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 0;
        }
        .contributor-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            transition: background 0.1s;
        }
        .contributor-card:hover { background: #f8fafc; }
        .contributor-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
            letter-spacing: 0.02em;
        }
        .contributor-avatar.role-lead { background: #dcfce7; color: #166534; }
        .contributor-avatar.role-contributor { background: #dbeafe; color: #1e40af; }
        .contributor-avatar.role-viewer { background: #f1f5f9; color: #475569; }
        .contributor-info { min-width: 0; }
        .contributor-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .contributor-role-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .contributor-role-label.role-lead { color: #16a34a; }
        .contributor-role-label.role-contributor { color: #2563eb; }
        .contributor-role-label.role-viewer { color: #64748b; }
        .contributors-empty {
            padding: 20px 16px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }

        .distribution-table { font-size: 13px; }
        .distribution-table th { font-weight: 600; color: #374151; }

        .review-container { margin-top: 24px; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; border-left: 4px solid; }
        .timeline-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        .timeline-item:last-child { border-bottom: none; }
        .timeline-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
        .timeline-content { flex: 1; }

        /* =========================================================
           Syllabus Drawer — Refined Utilitarian Aesthetic
           ========================================================= */

        /* Tooltip must appear above offcanvas (z-index 1045) */
        .tooltip { z-index: 1080 !important; }

        /* --- Toolbar --- */
        .planner-toolbar-row {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }
        .planner-toolbar-row .planner-col {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .planner-toolbar-row .planner-col-grow { flex: 1; }
        .planner-toolbar-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }
        /* Equal height for select and button-group */
        .planner-toolbar-row .form-select {
            height: 36px;
            font-size: 13px;
            border-color: #cbd5e1;
            border-radius: 3px;
        }
        .planner-toolbar-row .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .planner-toolbar-row .btn-group {
            height: 36px;
        }
        .planner-toolbar-row .btn-group .btn {
            height: 36px;
            font-size: 12px;
            font-weight: 600;
            padding: 0 16px;
            border-color: #cbd5e1;
            color: #475569;
            line-height: 34px;
        }
        .planner-toolbar-row .btn-group .btn-check:checked + .btn {
            background: #1e293b;
            border-color: #1e293b;
            color: #fff;
        }

        /* --- Search bar --- */
        #syllabusPanel .drawer-search-wrap {
            padding: 10px 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
        }
        #syllabusPanel .drawer-search-wrap .form-control {
            border-radius: 3px;
            border-color: #e2e8f0;
            font-size: 13px;
            padding-left: 34px;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242.656a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") no-repeat 12px center;
        }
        #syllabusPanel .drawer-search-wrap .form-control:focus {
            background-color: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.10);
        }

        /* --- Outline header --- */
        #syllabusPanel .syllabus-outline { gap: 0; padding: 0; }
        #syllabusPanel .syllabus-outline-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0 12px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 10px;
        }
        #syllabusPanel .syllabus-outline-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        #syllabusPanel .syllabus-outline-meta {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 1px;
            font-weight: 500;
        }

        /* --- Section (Form 1, Form 2 …) — top-level accordion --- */
        #syllabusPanel .syllabus-section {
            border: none;
            border-radius: 0;
            margin-bottom: 2px;
            background: transparent;
        }
        #syllabusPanel .syllabus-section > summary {
            padding: 9px 12px;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            list-style: none;
            border-radius: 3px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: background 0.15s;
        }
        #syllabusPanel .syllabus-section > summary:hover { background: #e2e8f0; }
        #syllabusPanel .syllabus-section > summary::-webkit-details-marker { display: none; }
        #syllabusPanel .syllabus-section > summary::before {
            content: '+';
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 700;
            font-size: 14px;
            line-height: 1;
            color: #64748b;
            margin-right: 10px;
            width: 14px;
            text-align: center;
            flex-shrink: 0;
        }
        #syllabusPanel .syllabus-section[open] > summary::before {
            content: '−';
        }
        #syllabusPanel .syllabus-section[open] > summary {
            border-radius: 3px 3px 0 0;
            border-bottom-color: transparent;
        }

        /* Section body — indented */
        #syllabusPanel .syllabus-section-body {
            padding: 6px 0 8px 20px;
            border-left: 2px solid #cbd5e1;
            margin-left: 14px;
            margin-bottom: 6px;
        }

        /* --- Unit (1.1 Listening, 1.2 Speaking) — nested --- */
        #syllabusPanel .syllabus-unit {
            border-left: none;
            margin: 2px 0;
            padding-left: 0;
        }
        #syllabusPanel .syllabus-unit > summary {
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            list-style: none;
            border-radius: 3px;
            transition: background 0.12s;
        }
        #syllabusPanel .syllabus-unit > summary:hover { background: #f1f5f9; }
        #syllabusPanel .syllabus-unit > summary::-webkit-details-marker { display: none; }
        #syllabusPanel .syllabus-unit > summary::before {
            content: '+';
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 700;
            font-size: 12px;
            line-height: 1;
            color: #94a3b8;
            margin-right: 8px;
            width: 12px;
            text-align: center;
            flex-shrink: 0;
        }
        #syllabusPanel .syllabus-unit[open] > summary::before { content: '−'; }

        #syllabusPanel .syllabus-unit-heading { display: inline-flex; align-items: center; gap: 0; }
        #syllabusPanel .syllabus-unit-code {
            background: #e0e7ff;
            color: #4338ca;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 2px;
            margin-right: 6px;
            letter-spacing: 0.02em;
        }
        #syllabusPanel .syllabus-count {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
            margin-left: auto;
            padding-left: 8px;
        }

        /* Topics container — indented further */
        #syllabusPanel .syllabus-topics {
            padding: 4px 0 4px 18px;
            border-left: 2px solid #e0e7ff;
            margin-left: 12px;
        }

        /* --- Topic card --- */
        #syllabusPanel .syllabus-topic-planner-card {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 10px 12px;
            margin: 4px 0;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        #syllabusPanel .syllabus-topic-planner-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 2px 8px rgba(59,130,246,0.08);
        }

        #syllabusPanel .planner-topic-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 0;
        }
        #syllabusPanel .planner-topic-kicker {
            font-size: 10px;
            color: #94a3b8;
            margin-bottom: 2px;
            font-weight: 500;
        }
        #syllabusPanel .syllabus-topic-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            line-height: 1.3;
        }

        /* --- Topic action buttons — compact icon pills --- */
        #syllabusPanel .planner-topic-actions {
            display: flex;
            gap: 3px;
            flex-shrink: 0;
        }
        #syllabusPanel .planner-topic-actions .btn {
            width: 24px;
            height: 24px;
            padding: 0;
            font-size: 10px;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-width: 1px;
        }
        #syllabusPanel .planner-topic-actions .btn-primary {
            background: #3b82f6;
            border-color: #3b82f6;
        }
        #syllabusPanel .planner-topic-actions .btn-primary:hover {
            background: #2563eb;
        }
        #syllabusPanel .planner-topic-actions .btn-outline-secondary {
            background: #fff;
            border-color: #d1d5db;
            color: #6b7280;
        }
        #syllabusPanel .planner-topic-actions .btn-outline-secondary:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #334155;
        }

        /* --- Objective groups --- */
        #syllabusPanel .syllabus-objective-group {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f1f5f9;
        }
        #syllabusPanel .planner-objective-group-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        #syllabusPanel .syllabus-objective-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
        }
        #syllabusPanel .planner-group-action {
            font-size: 10px;
            padding: 2px 8px;
            color: #3b82f6;
            text-decoration: none;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            background: #eff6ff;
            font-weight: 600;
            transition: all 0.12s;
        }
        #syllabusPanel .planner-group-action:hover {
            background: #3b82f6;
            color: #fff;
            border-color: #3b82f6;
            text-decoration: none;
        }

        /* Objective list */
        #syllabusPanel .syllabus-objective-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #syllabusPanel .planner-objective-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 4px 0;
            font-size: 12px;
            color: #475569;
            line-height: 1.45;
        }

        /* Insert button per objective — minimal ghost style */
        #syllabusPanel .planner-objective-insert {
            width: 18px;
            height: 18px;
            min-width: 18px;
            border: none;
            border-radius: 2px;
            background: transparent;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            cursor: pointer;
            transition: all 0.12s;
            margin-top: 2px;
            padding: 0;
        }
        #syllabusPanel .planner-objective-insert:hover {
            background: #3b82f6;
            color: #fff;
            transform: none;
        }
        #syllabusPanel .planner-objective-insert i {
            font-size: 9px;
        }

        /* Subtopic tree */
        #syllabusPanel .planner-subtopic-tree {
            margin-top: 8px;
            padding-left: 14px;
            border-left: 2px solid #e0e7ff;
        }
        #syllabusPanel .planner-subtopic-tree > summary {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            list-style: none;
        }
        #syllabusPanel .planner-subtopic-tree > summary::-webkit-details-marker { display: none; }
        #syllabusPanel .planner-subtopic-tree > summary::before {
            content: '+';
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 700;
            font-size: 11px;
            line-height: 1;
            color: #94a3b8;
            margin-right: 6px;
            width: 10px;
            display: inline-block;
            text-align: center;
        }
        #syllabusPanel .planner-subtopic-tree[open] > summary::before { content: '−'; }

        /* Empty states */
        #syllabusPanel .planner-card-empty,
        #syllabusPanel .syllabus-empty-state {
            font-size: 11px;
            color: #94a3b8;
            padding: 4px 0;
        }

        /* Linked badge */
        #syllabusPanel .planner-linked-badge {
            font-size: 9px;
            padding: 1px 6px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 600;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('standard-schemes.index') }}">Standard Schemes</a>
        @endslot
        @slot('title')
            {{ $standardScheme->subject?->name }} — {{ $standardScheme->grade?->name }}
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

    <div class="schemes-container">
        {{-- ===== HEADER ===== --}}
        <div class="header schemes-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin: 0;">{{ $standardScheme->subject?->name ?? 'Standard Scheme' }}</h3>
                    <div class="d-flex flex-wrap gap-2 mt-2" style="font-size: 13px;">
                        <span class="badge bg-white bg-opacity-25 text-white"><i class="fas fa-graduation-cap me-1"></i>{{ $standardScheme->grade?->name }}</span>
                        <span class="badge bg-white bg-opacity-25 text-white"><i class="fas fa-calendar me-1"></i>Term {{ $standardScheme->term?->term }} {{ $standardScheme->term?->year }}</span>
                        <span class="badge bg-white bg-opacity-25 text-white"><i class="fas fa-building me-1"></i>{{ $standardScheme->department?->name }}</span>
                        @if ($standardScheme->isPublished())
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Published</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    @php
                        $filledEntries = $standardScheme->entries->filter(fn($e) => $e->topic || $e->syllabus_topic_id)->count();
                        $totalObjectives = $standardScheme->entries->sum(fn($e) => $e->objectives->count());
                        $teacherCount = $standardScheme->derivedSchemes->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $standardScheme->total_weeks }}</h4>
                                <small class="opacity-75">Weeks</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $filledEntries }}</h4>
                                <small class="opacity-75">Entries</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalObjectives }}</h4>
                                <small class="opacity-75">Objectives</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $teacherCount }}</h4>
                                <small class="opacity-75">Teachers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container">
            {{-- Status badge --}}
            <div class="status-toolbar">
                <div class="status-toolbar-meta">
                    <span class="status-badge status-{{ $standardScheme->status }}">
                        <span class="status-dot dot-{{ $standardScheme->status }}"></span>
                        {{ ucfirst(str_replace('_', ' ', $standardScheme->status)) }}
                    </span>
                    <span class="text-muted" style="font-size: 13px;">
                        Created by {{ $standardScheme->creator?->full_name }} on {{ $standardScheme->created_at->format('d M Y') }}
                    </span>
                </div>

                {{-- ===== ACTION BUTTONS ===== --}}
                <div class="header-actions">
                    <a href="{{ route('standard-schemes.print', $standardScheme) }}"
                        class="btn btn-outline-primary"
                        target="_blank"
                        rel="noopener">
                        <i class="fas fa-print me-1"></i> Print Page
                    </a>

                    @if ($canSubmit)
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitModal">
                            <i class="fas fa-paper-plane me-1"></i> Submit for Review
                        </button>
                    @endif

                    @if ($canReview && $standardScheme->status === 'submitted')
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#placeUnderReviewModal">
                            <i class="fas fa-search me-1"></i> Place Under Review
                        </button>
                    @endif

                    @if ($canReview && $standardScheme->status === 'under_review')
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#returnModal">
                            <i class="fas fa-undo me-1"></i> Return for Revision
                        </button>
                    @endif

                    @if ($canPublish && !$standardScheme->isPublished())
                        <form action="{{ route('standard-schemes.publish', $standardScheme) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-bookmark me-1"></i> Publish
                            </button>
                        </form>
                    @endif

                    @if ($canUnpublish && $standardScheme->isPublished())
                        <form action="{{ route('standard-schemes.unpublish', $standardScheme) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-bookmark me-1"></i> Unpublish
                            </button>
                        </form>
                    @endif

                    @if ($canClone && $copyTerms->isNotEmpty())
                        <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#copyStandardSchemeModal">
                            <i class="fas fa-copy me-1"></i> Copy to Another Term
                        </button>
                    @endif

                    @if ($syllabus && !$syllabusUnavailable)
                        <button class="btn btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#syllabusPanel">
                            <i class="fas fa-book me-1"></i> Browse Syllabus
                        </button>
                    @endif
                </div>
            </div>

            {{-- Alerts --}}
            @if ($standardScheme->status === 'draft' && $canEdit)
                <div class="alert alert-info" style="font-size: 13px;">
                    <i class="fas fa-info-circle me-2"></i>
                    This standard scheme is in <strong>draft</strong> mode. Fill in the weekly entries below, then submit for review.
                </div>
            @endif

            @if ($standardScheme->status === 'revision_required' && $standardScheme->review_comments)
                <div class="alert alert-warning" style="font-size: 13px;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Revision Required:</strong> {{ $standardScheme->review_comments }}
                </div>
            @endif

            {{-- ===== WEEKLY ENTRY TABS ===== --}}
            <div class="section-title">Weekly Entries</div>

            <div class="week-tabs-wrapper" id="weekTabsBar">
                @foreach ($standardScheme->entries as $entry)
                    <div class="week-tab {{ $loop->first ? 'active' : '' }}"
                         data-week="{{ $entry->week_number }}"
                         onclick="switchTab({{ $entry->week_number }})">
                        <span class="status-dot dot-{{ $entry->status }}" style="width: 8px; height: 8px;"></span>
                        Week {{ $entry->week_number }}
                    </div>
                @endforeach
            </div>

            @foreach ($standardScheme->entries as $entry)
                <div class="week-tab-panel {{ $loop->first ? 'active' : '' }}" id="week-panel-{{ $entry->week_number }}">
                    <div class="entry-row" data-entry-id="{{ $entry->id }}" data-week="{{ $entry->week_number }}">

                        {{-- Status select --}}
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <select class="form-select form-select-sm entry-status-select" style="width: 140px;" data-entry-id="{{ $entry->id }}">
                                @foreach (['planned', 'taught', 'completed', 'skipped'] as $s)
                                    <option value="{{ $s }}" {{ $entry->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <span class="entry-save-indicator text-success d-none" style="font-size: 12px;">
                                <i class="fas fa-check"></i> Saved
                            </span>
                        </div>

                        {{-- Topic --}}
                        <div class="entry-field">
                            <label>Topic</label>
                            <input type="text" class="form-control entry-topic" value="{{ $entry->topic }}" placeholder="Enter topic..." data-entry-id="{{ $entry->id }}" >
                        </div>

                        {{-- Sub-topic --}}
                        <div class="entry-field">
                            <label>Sub-topic</label>
                            <textarea class="form-control entry-subtopic" rows="2" placeholder="Enter sub-topic..." data-entry-id="{{ $entry->id }}" >{{ $entry->sub_topic }}</textarea>
                        </div>

                        {{-- Learning Objectives --}}
                        <div class="entry-field">
                            <label>Learning Objectives</label>
                            <textarea class="form-control entry-objectives ck-editor-field" rows="4" placeholder="Enter learning objectives..." data-entry-id="{{ $entry->id }}" id="objectives-{{ $entry->id }}" >{{ $entry->learning_objectives }}</textarea>
                        </div>

                        {{-- Linked Objectives (only shown when local syllabus objectives exist) --}}
                        @if ($entry->objectives->isNotEmpty())
                            <div class="entry-field">
                                <label>Linked Syllabus Objectives</label>
                                <div class="objective-tags-container" id="objectives-tags-{{ $entry->id }}">
                                    @foreach ($entry->objectives as $obj)
                                        <span class="objective-tag" data-objective-id="{{ $obj->id }}">
                                            <strong>{{ $obj->code }}</strong> {{ Str::limit($obj->objective_text, 60) }}
                                            @if ($canEdit)
                                                <span class="remove-objective" onclick="removeObjective({{ $entry->id }}, {{ $obj->id }})">&times;</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="objective-tags-container d-none" id="objectives-tags-{{ $entry->id }}"></div>
                        @endif

                        <div class="field-error d-none" id="entry-error-{{ $entry->id }}"></div>

                        {{-- Save button --}}
                        @if ($canEdit)
                            <div class="entry-actions">
                                <button class="btn btn-primary btn-loading btn-save-entry" data-entry-id="{{ $entry->id }}">
                                    <span class="btn-text"><i class="fas fa-save"></i> Save Week {{ $entry->week_number }}</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- ===== CONTRIBUTORS ===== --}}
            <div class="contributors-panel">
                <div class="contributors-header">
                    <span class="contributors-header-title"><i class="fas fa-users me-2" style="color: #94a3b8;"></i>Subject Panel</span>
                    <span class="contributors-count">{{ $standardScheme->contributors->count() }}</span>
                </div>
                @if ($standardScheme->contributors->isNotEmpty())
                    <div class="contributors-list">
                        @foreach ($standardScheme->contributors as $contributor)
                            @php
                                $role = $contributor->pivot->role;
                                $nameParts = explode(' ', trim($contributor->full_name));
                                $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1) . substr(end($nameParts) !== $nameParts[0] ? end($nameParts) : '', 0, 1));
                            @endphp
                            <div class="contributor-card">
                                <div class="contributor-avatar role-{{ $role }}">{{ $initials }}</div>
                                <div class="contributor-info">
                                    <div class="contributor-name">{{ $contributor->full_name }}</div>
                                    <div class="contributor-role-label role-{{ $role }}">{{ ucfirst($role) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="contributors-empty">No contributors assigned to this scheme yet.</div>
                @endif
            </div>

            {{-- ===== DISTRIBUTION STATUS ===== --}}
            @if ($standardScheme->derivedSchemes->isNotEmpty())
                <div class="section-title">Distribution Status</div>
                <div class="table-responsive">
                    <table class="table distribution-table mb-0">
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Class / Optional</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($standardScheme->derivedSchemes as $derived)
                                <tr>
                                    <td>{{ $derived->teacher?->full_name ?? '—' }}</td>
                                    <td>{{ $derived->klassSubject?->klass?->name ?? $derived->optionalSubject?->name ?? '—' }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $derived->status }}">
                                            <span class="status-dot dot-{{ $derived->status }}"></span>
                                            {{ ucfirst(str_replace('_', ' ', $derived->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('schemes.show', $derived) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ===== WORKFLOW AUDIT TRAIL ===== --}}
            @if ($standardScheme->workflowAudits->isNotEmpty())
                <div class="review-container" style="border-left-color: #6366f1;">
                    <div class="section-title" style="margin-top: 0;">Workflow History</div>
                    @foreach ($standardScheme->workflowAudits as $audit)
                        <div class="timeline-item">
                            @php
                                $dotColors = [
                                    'submitted' => '#3b82f6',
                                    'placed_under_review' => '#f59e0b',
                                    'approved' => '#10b981',
                                    'revision_required' => '#ef4444',
                                    'published' => '#8b5cf6',
                                    'unpublished' => '#6b7280',
                                    'distributed' => '#06b6d4',
                                ];
                            @endphp
                            <div class="timeline-dot" style="background: {{ $dotColors[$audit->action] ?? '#9ca3af' }};"></div>
                            <div class="timeline-content">
                                <div style="font-size: 13px; font-weight: 600; color: #374151;">
                                    {{ ucfirst(str_replace('_', ' ', $audit->action)) }}
                                </div>
                                <div style="font-size: 12px; color: #6b7280;">
                                    {{ $audit->actor?->full_name ?? 'System' }} &mdash; {{ $audit->created_at->format('d M Y H:i') }}
                                </div>
                                @if ($audit->comments)
                                    <div style="font-size: 12px; color: #4b5563; margin-top: 4px; font-style: italic;">
                                        "{{ $audit->comments }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ===== MODALS ===== --}}

    @if ($canClone && $copyTerms->isNotEmpty())
        <div class="modal fade" id="copyStandardSchemeModal" tabindex="-1" aria-labelledby="copyStandardSchemeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('standard-schemes.clone', $standardScheme) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="copyStandardSchemeModalLabel">
                                <i class="fas fa-copy me-2"></i>Copy Standard Scheme
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted" style="font-size: 14px;">
                                Copy this standard scheme into another term or year for the same subject and grade name. Weekly content will be copied into a new draft.
                            </p>
                            <div class="mb-3">
                                <label for="copy_term_id" class="form-label">Target Term <span class="text-danger">*</span></label>
                                @php
                                    $defaultCopyTermId = old('term_id');
                                    if (empty($defaultCopyTermId)) {
                                        $defaultCopyTermId = $copyTerms->first()?->id;
                                    }
                                @endphp
                                <select name="term_id" id="copy_term_id" class="form-select" required>
                                    <option value="">Select Term...</option>
                                    @foreach ($copyTerms as $term)
                                        <option value="{{ $term->id }}" {{ (string) $defaultCopyTermId === (string) $term->id ? 'selected' : '' }}>
                                            Term {{ $term->term }}, {{ $term->year }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-muted mt-1" style="font-size: 12px;">
                                    The selected term must already have the same grade and subject allocation configured.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark btn-loading">
                                <span class="btn-text"><i class="fas fa-copy me-1"></i> Copy Scheme</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Copying...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Submit Modal --}}
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('standard-schemes.submit', $standardScheme) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Submit for Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Submit this standard scheme for HOD review?</p>
                        <div class="mb-3">
                            <label class="form-label">Comments (optional)</label>
                            <textarea name="comments" class="form-control" rows="3" placeholder="Any notes for the reviewer..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-loading">
                            <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Submit</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Submitting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Place Under Review Modal --}}
    <div class="modal fade" id="placeUnderReviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('standard-schemes.place-under-review', $standardScheme) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Place Under Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Begin reviewing this standard scheme?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-search me-1"></i> Place Under Review</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('standard-schemes.approve', $standardScheme) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Standard Scheme</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Approve this standard scheme? Once approved, it can be published and distributed to teachers.</p>
                        <div class="mb-3">
                            <label class="form-label">Comments (optional)</label>
                            <textarea name="comments" class="form-control" rows="3"></textarea>
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

    {{-- Return for Revision Modal --}}
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('standard-schemes.return-for-revision', $standardScheme) }}" method="POST">
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
                            <span class="btn-text"><i class="fas fa-undo me-1"></i> Return for Revision</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== SYLLABUS OFFCANVAS ===== --}}
    @if ($syllabus && $syllabusStructure)
        <div class="offcanvas offcanvas-end" tabindex="-1" id="syllabusPanel" style="width: 600px; max-width: 100vw;">
            <div class="offcanvas-header" style="border-bottom: 1px solid #e2e8f0;">
                <h5 class="offcanvas-title" style="font-size: 15px; font-weight: 700; color: #1e293b;"><i class="fas fa-book me-2 text-muted"></i>Syllabus Browser</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-0" style="display: flex; flex-direction: column;">
                @if ($canEdit)
                    <div class="help-text" style="margin: 12px 12px 0; border-radius: 3px;">
                        <div class="help-content" style="font-size: 12px;">
                            Choose a target week, then use the action buttons to insert topics, sub-topics, or objectives into the entry. Save the entry when ready.
                        </div>
                    </div>
                    <div class="planner-toolbar-row">
                        <div class="planner-col planner-col-grow">
                            <span class="planner-toolbar-label">Target week</span>
                            <select id="planner-target-entry" class="form-select">
                                @foreach ($standardScheme->entries as $entry)
                                    <option value="{{ $entry->id }}">Week {{ $entry->week_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="planner-col">
                            <span class="planner-toolbar-label">Objectives mode</span>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="plannerObjectiveMode" id="planner-mode-replace" value="replace" checked>
                                <label class="btn btn-outline-secondary" for="planner-mode-replace">Replace</label>
                                <input type="radio" class="btn-check" name="plannerObjectiveMode" id="planner-mode-append" value="append">
                                <label class="btn btn-outline-secondary" for="planner-mode-append">Append</label>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="drawer-search-wrap">
                    <input type="search" class="form-control" id="plannerSearch" placeholder="Search topics, sub-topics, or objectives...">
                </div>
                <div style="flex: 1; overflow-y: auto; padding: 14px 16px;" id="plannerContent">
                    @include('schemes.partials.structured-syllabus-outline', [
                        'structure' => $syllabusStructure,
                        'syllabusDocument' => null,
                        'plannerReadonly' => !$canEdit,
                    ])
                    <div class="text-center text-muted py-4 d-none" id="planner-empty-state">
                        No topics match your search.
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('script')
    <script>
    (function () {
        'use strict';

        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        var schemeId = {{ $standardScheme->id }};
        var canEdit = {{ $canEdit ? 'true' : 'false' }};

        // ================================================================
        // Toast helper
        // ================================================================
        function showToast(message, icon) {
            Swal.fire({ toast: true, position: 'top-end', icon: icon || 'success', title: message, showConfirmButton: false, timer: 2500 });
        }

        // ================================================================
        // CKEditor instances
        // ================================================================
        var ckEditors = {};

        function initEditors() {
            if (typeof ClassicEditor === 'undefined') return;
            document.querySelectorAll('textarea.ck-editor-field').forEach(function (textarea) {
                var key = textarea.id || textarea.dataset.entryId;
                if (ckEditors[key]) return;
                ClassicEditor.create(textarea, {
                    toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                }).then(function (editor) {
                    ckEditors[key] = editor;
                    editor.ui.view.editable.element.style.minHeight = '200px';
                }).catch(function (err) { console.error('CKEditor error:', err); });
            });
        }

        if (typeof ClassicEditor !== 'undefined') initEditors();
        else window.addEventListener('load', initEditors);

        // ================================================================
        // Tab switching
        // ================================================================
        window.switchTab = function (weekNumber) {
            document.querySelectorAll('.week-tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.week-tab-panel').forEach(function (p) { p.classList.remove('active'); });
            document.querySelector('.week-tab[data-week="' + weekNumber + '"]')?.classList.add('active');
            document.getElementById('week-panel-' + weekNumber)?.classList.add('active');

            // Sync planner target
            var targetSelect = document.getElementById('planner-target-entry');
            if (targetSelect) {
                var entryRow = document.querySelector('#week-panel-' + weekNumber + ' .entry-row');
                if (entryRow) targetSelect.value = entryRow.dataset.entryId;
                updatePlannerBadge();
            }
        };

        // ================================================================
        // Entry field getters / setters
        // ================================================================
        function getEntryRow(entryId) {
            return document.querySelector('.entry-row[data-entry-id="' + entryId + '"]');
        }

        function getEntryFieldValue(entryId, field) {
            if (field === 'learning_objectives') {
                var editorKey = 'objectives-' + entryId;
                if (ckEditors[editorKey]) return ckEditors[editorKey].getData();
                var ta = document.getElementById(editorKey);
                return ta ? ta.value : '';
            }
            var el = document.querySelector('[data-entry-id="' + entryId + '"].entry-' + field);
            return el ? el.value : '';
        }

        function setEntryFieldValue(entryId, field, value) {
            if (field === 'learning_objectives') {
                var editorKey = 'objectives-' + entryId;
                if (ckEditors[editorKey]) {
                    ckEditors[editorKey].setData(value);
                    return;
                }
                var ta = document.getElementById(editorKey);
                if (ta) ta.value = value;
                return;
            }
            var el = document.querySelector('[data-entry-id="' + entryId + '"].entry-' + field);
            if (el) el.value = value;
        }

        // ================================================================
        // Planner state
        // ================================================================
        var plannerTargetSelect = document.getElementById('planner-target-entry');
        var plannerEmptyState = document.getElementById('planner-empty-state');

        function getTargetEntryId() {
            if (plannerTargetSelect) return parseInt(plannerTargetSelect.value, 10) || null;
            var activePanel = document.querySelector('.week-tab-panel.active .entry-row');
            return activePanel ? parseInt(activePanel.dataset.entryId, 10) : null;
        }

        if (plannerTargetSelect) {
            plannerTargetSelect.addEventListener('change', function () {
                // Switch to the matching week tab
                var opt = plannerTargetSelect.options[plannerTargetSelect.selectedIndex];
                if (opt) {
                    var weekMatch = opt.text.match(/Week\s+(\d+)/);
                    if (weekMatch) switchTab(parseInt(weekMatch[1], 10));
                }
            });
        }

        function getPlannerObjectiveMode() {
            var selected = document.querySelector('input[name="plannerObjectiveMode"]:checked');
            return selected ? selected.value : 'replace';
        }

        // ================================================================
        // Planner card payload parser
        // ================================================================
        function parsePlannerCardPayload(card) {
            if (!card) return null;
            if (card._plannerPayload) return card._plannerPayload;
            var script = card.querySelector('.planner-topic-payload');
            if (!script) return null;
            try { card._plannerPayload = JSON.parse(script.textContent || '{}'); }
            catch (e) { card._plannerPayload = null; }
            return card._plannerPayload;
        }

        // ================================================================
        // HTML helpers for objectives
        // ================================================================
        function buildObjectiveListHtml(texts) {
            if (!texts.length) return '';
            return '<ul>' + texts.map(function (t) { return '<li>' + t + '</li>'; }).join('') + '</ul>';
        }

        function stripHtmlToText(html) {
            var div = document.createElement('div');
            div.innerHTML = html;
            return (div.textContent || div.innerText || '').toLowerCase();
        }

        function normalizePlannerText(text) {
            return String(text || '').toLowerCase().replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
        }

        // ================================================================
        // Linked objectives rendering
        // ================================================================
        function renderObjectiveTags(entryId, objectives) {
            var container = document.getElementById('objectives-tags-' + entryId);
            if (!container) return;
            if (!objectives.length) {
                container.innerHTML = '<span class="text-muted" style="font-size: 12px;">No objectives linked yet.</span>';
                return;
            }
            container.innerHTML = objectives.map(function (obj) {
                return '<span class="objective-tag" data-objective-id="' + obj.id + '">' +
                    '<strong>' + (obj.code || '') + '</strong> ' + (obj.objective_text || obj.text || '').substring(0, 60) +
                    (canEdit ? '<span class="remove-objective" onclick="removeObjective(' + entryId + ', ' + obj.id + ')">&times;</span>' : '') +
                    '</span>';
            }).join('');
        }

        function readRenderedLinkedObjectives(entryId) {
            var container = document.getElementById('objectives-tags-' + entryId);
            if (!container) return [];
            var objectives = [];
            container.querySelectorAll('.objective-tag').forEach(function (tag) {
                objectives.push({ id: parseInt(tag.dataset.objectiveId, 10), code: '', objective_text: tag.textContent.trim() });
            });
            return objectives;
        }

        // ================================================================
        // Apply planner selection to entry
        // ================================================================
        function applyPlannerSelection(selection, options) {
            var entryId = getTargetEntryId();
            if (!entryId || !canEdit) {
                showToast('Select a target week first.', 'warning');
                return;
            }

            var mode = (options && options.objectiveMode) || getPlannerObjectiveMode();
            var feedback = [];

            // Insert topic
            if (options && options.replaceTopic && selection.unit_title) {
                setEntryFieldValue(entryId, 'topic', selection.unit_title);
                feedback.push('topic');
            }

            // Insert sub-topic
            if (options && options.replaceSubTopic) {
                setEntryFieldValue(entryId, 'subtopic', selection.sub_topic_title || selection.topic_title || '');
                feedback.push('sub-topic');
            }

            // Insert objectives
            if (options && options.applyObjectives && selection.objectives && selection.objectives.length) {
                var currentHtml = getEntryFieldValue(entryId, 'learning_objectives');
                var incomingTexts = selection.objectives.map(function (o) { return o.text || ''; }).filter(Boolean);

                var objectiveHtml;
                if (mode === 'replace' || !currentHtml.trim()) {
                    objectiveHtml = buildObjectiveListHtml(incomingTexts);
                } else {
                    var existingText = stripHtmlToText(currentHtml);
                    var newTexts = incomingTexts.filter(function (t) { return !existingText.includes(t.toLowerCase()); });
                    objectiveHtml = newTexts.length
                        ? currentHtml + buildObjectiveListHtml(newTexts)
                        : currentHtml;
                }

                if (objectiveHtml !== currentHtml) {
                    setEntryFieldValue(entryId, 'learning_objectives', objectiveHtml);
                    feedback.push(incomingTexts.length + ' objective' + (incomingTexts.length === 1 ? '' : 's'));
                }

                // Sync linked objective IDs
                var linkedObjectives = selection.objectives.filter(function (o) { return o.local_objective_id; })
                    .map(function (o) { return { id: o.local_objective_id, code: o.code || '', objective_text: o.text }; });

                if (linkedObjectives.length) {
                    var existing = mode === 'replace' ? [] : readRenderedLinkedObjectives(entryId);
                    var merged = existing.concat(linkedObjectives);
                    var seen = {};
                    merged = merged.filter(function (o) { if (seen[o.id]) return false; seen[o.id] = true; return true; });
                    renderObjectiveTags(entryId, merged);
                }
            }

            showToast(feedback.length
                ? feedback.join(', ') + ' inserted. Save entry when ready.'
                : 'Selection already present in entry.', feedback.length ? 'success' : 'info');
        }

        // ================================================================
        // Planner action button handler (delegated)
        // ================================================================
        document.addEventListener('click', function (e) {
            var actionButton = e.target.closest('.planner-insert-action, .planner-group-action, .planner-objective-insert');
            if (!actionButton) return;

            var card = actionButton.closest('.syllabus-topic-planner-card');
            var payload = parsePlannerCardPayload(card);
            if (!payload) {
                showToast('Unable to read the reference topic.', 'error');
                return;
            }

            var action = actionButton.dataset.action;

            if (action === 'plan') {
                applyPlannerSelection({
                    unit_title: payload.unit_title,
                    topic_title: payload.topic_title,
                    sub_topic_title: payload.sub_topic_title,
                    local_topic_id: payload.local_topic_id,
                    objectives: payload.all_objectives || [],
                }, { replaceTopic: true, replaceSubTopic: true, applyObjectives: true });
                return;
            }

            if (action === 'topic') {
                applyPlannerSelection({
                    unit_title: payload.unit_title,
                    topic_title: payload.topic_title,
                }, { replaceTopic: true });
                return;
            }

            if (action === 'sub-topic') {
                applyPlannerSelection({
                    unit_title: payload.unit_title,
                    topic_title: payload.topic_title,
                    sub_topic_title: payload.sub_topic_title,
                }, { replaceSubTopic: true });
                return;
            }

            if (action === 'all-objectives' || action === 'group-objectives') {
                var objectives = action === 'group-objectives'
                    ? (((payload.objective_groups || [])[parseInt(actionButton.dataset.groupIndex, 10)] || {}).objectives || [])
                    : (payload.all_objectives || []);
                applyPlannerSelection({
                    unit_title: payload.unit_title,
                    topic_title: payload.topic_title,
                    sub_topic_title: payload.sub_topic_title,
                    local_topic_id: payload.local_topic_id,
                    objectives: objectives,
                }, { applyObjectives: true });
                return;
            }

            if (action === 'single-objective') {
                var groupIndex = parseInt(actionButton.dataset.groupIndex, 10);
                var objectiveIndex = parseInt(actionButton.dataset.objectiveIndex, 10);
                var group = (payload.objective_groups || [])[groupIndex] || {};
                var objective = (group.objectives || [])[objectiveIndex];
                if (!objective) { showToast('Objective not found.', 'error'); return; }
                applyPlannerSelection({
                    unit_title: payload.unit_title,
                    topic_title: payload.topic_title,
                    sub_topic_title: payload.sub_topic_title,
                    local_topic_id: payload.local_topic_id,
                    objectives: [objective],
                }, { applyObjectives: true, objectiveMode: 'append' });
            }
        });

        // ================================================================
        // Planner search with proper tree filtering
        // ================================================================
        var plannerSearchInput = document.getElementById('plannerSearch');
        if (plannerSearchInput) {
            plannerSearchInput.addEventListener('input', function () {
                var query = normalizePlannerText(this.value);
                var cards = document.querySelectorAll('.syllabus-topic-planner-card');
                var visibleCards = 0;

                cards.forEach(function (card) {
                    var matches = !query || normalizePlannerText(card.textContent).includes(query);
                    card.classList.toggle('d-none', !matches);
                    if (matches) visibleCards++;
                });

                document.querySelectorAll('.planner-unit').forEach(function (unit) {
                    var hasVisible = unit.querySelector('.syllabus-topic-planner-card:not(.d-none)');
                    unit.classList.toggle('d-none', !hasVisible);
                    if (query && hasVisible) unit.open = true;
                });

                document.querySelectorAll('.planner-section').forEach(function (section) {
                    var hasVisible = section.querySelector('.syllabus-topic-planner-card:not(.d-none)');
                    section.classList.toggle('d-none', !hasVisible);
                    if (query && hasVisible) section.open = true;
                });

                if (plannerEmptyState) {
                    plannerEmptyState.classList.toggle('d-none', visibleCards > 0);
                }
            });
        }

        // ================================================================
        // Save entry
        // ================================================================
        document.querySelectorAll('.btn-save-entry').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var entryId = this.dataset.entryId;
                var entryRow = getEntryRow(entryId);
                var statusSelect = entryRow.querySelector('.entry-status-select');

                btn.classList.add('loading');
                btn.disabled = true;

                // Collect linked objective IDs from rendered tags
                var objectiveIds = [];
                var container = document.getElementById('objectives-tags-' + entryId);
                if (container) {
                    container.querySelectorAll('.objective-tag').forEach(function (tag) {
                        var id = parseInt(tag.dataset.objectiveId, 10);
                        if (id) objectiveIds.push(id);
                    });
                }

                var payload = {
                    topic: getEntryFieldValue(entryId, 'topic'),
                    sub_topic: getEntryFieldValue(entryId, 'subtopic'),
                    learning_objectives: getEntryFieldValue(entryId, 'learning_objectives'),
                    status: statusSelect ? statusSelect.value : 'planned',
                    objective_ids: objectiveIds,
                };

                fetch('/standard-schemes/' + schemeId + '/entries/' + entryId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                }).then(function (r) { return r.json(); })
                .then(function (data) {
                    btn.classList.remove('loading');
                    btn.disabled = false;

                    if (data.success) {
                        var indicator = entryRow.querySelector('.entry-save-indicator');
                        if (indicator) {
                            indicator.classList.remove('d-none');
                            setTimeout(function () { indicator.classList.add('d-none'); }, 2000);
                        }
                        // Update the tab dot color
                        var weekNumber = entryRow.dataset.week;
                        var tabDot = document.querySelector('.week-tab[data-week="' + weekNumber + '"] .status-dot');
                        if (tabDot && data.entry) {
                            tabDot.className = 'status-dot dot-' + data.entry.status;
                        }
                        showToast('Entry saved');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save entry.' });
                    }
                }).catch(function (err) {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' });
                });
            });
        });

        // ================================================================
        // Objective removal
        // ================================================================
        window.removeObjective = function (entryId, objectiveId) {
            var container = document.getElementById('objectives-tags-' + entryId);
            var currentIds = [];
            container.querySelectorAll('.objective-tag').forEach(function (tag) {
                var id = parseInt(tag.dataset.objectiveId, 10);
                if (id !== objectiveId) currentIds.push(id);
            });

            fetch('/standard-schemes/' + schemeId + '/entries/' + entryId + '/objectives', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ objective_ids: currentIds }),
            }).then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    var tag = container.querySelector('[data-objective-id="' + objectiveId + '"]');
                    if (tag) tag.remove();
                    showToast('Objective removed');
                }
            });
        };

        // ================================================================
        // Modal form loading states
        // ================================================================
        document.querySelectorAll('.modal form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"].btn-loading');
                if (btn) { btn.classList.add('loading'); btn.disabled = true; }
            });
        });
    })();
    </script>
@endsection
