@extends('layouts.master')
@section('title')
    F4 Placement Recommendations
@endsection

@section('css')
    <style>
        :root {
            --pathway-triple: #4f46e5;
            --pathway-triple-bg: #eef2ff;
            --pathway-double: #d97706;
            --pathway-double-bg: #fffbeb;
            --pathway-single: #059669;
            --pathway-single-bg: #ecfdf5;
            --pathway-unclassified: #64748b;
            --pathway-unclassified-bg: #f1f5f9;
        }

        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Dashboard Summary Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .dashboard-card {
            border-radius: 3px;
            padding: 16px 20px;
            border: 1px solid #e5e7eb;
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card.triple {
            border-left-color: var(--pathway-triple);
            background: var(--pathway-triple-bg);
        }

        .dashboard-card.double {
            border-left-color: var(--pathway-double);
            background: var(--pathway-double-bg);
        }

        .dashboard-card.single {
            border-left-color: var(--pathway-single);
            background: var(--pathway-single-bg);
        }

        .dashboard-card.unclassified {
            border-left-color: var(--pathway-unclassified);
            background: var(--pathway-unclassified-bg);
        }

        .dashboard-card-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
            margin-bottom: 10px;
        }

        .dashboard-card.triple .dashboard-card-icon { background: var(--pathway-triple); }
        .dashboard-card.double .dashboard-card-icon { background: var(--pathway-double); }
        .dashboard-card.single .dashboard-card-icon { background: var(--pathway-single); }
        .dashboard-card.unclassified .dashboard-card-icon { background: var(--pathway-unclassified); }

        .dashboard-card-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .dashboard-card-count {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            line-height: 1;
        }

        .dashboard-card-target {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
        }

        .dashboard-mini-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin-top: 10px;
            overflow: hidden;
        }

        .dashboard-mini-bar-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .dashboard-card.triple .dashboard-mini-bar-fill { background: var(--pathway-triple); }
        .dashboard-card.double .dashboard-mini-bar-fill { background: var(--pathway-double); }
        .dashboard-card.single .dashboard-mini-bar-fill { background: var(--pathway-single); }
        .dashboard-card.unclassified .dashboard-mini-bar-fill { background: var(--pathway-unclassified); }

        .metric-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.15);
            font-size: 12px;
            font-weight: 500;
        }

        .info-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .controls-row {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .controls-row .form-select {
            min-width: 240px;
        }

        .action-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Pathway Section Cards */
        .section-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 24px;
            overflow: hidden;
            border-left: 4px solid #e5e7eb;
        }

        .section-card.pathway-triple { border-left-color: var(--pathway-triple); }
        .section-card.pathway-double { border-left-color: var(--pathway-double); }
        .section-card.pathway-single { border-left-color: var(--pathway-single); }
        .section-card.pathway-unclassified { border-left-color: var(--pathway-unclassified); }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .section-card.pathway-triple .section-header { background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%); }
        .section-card.pathway-double .section-header { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); }
        .section-card.pathway-single .section-header { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); }
        .section-card.pathway-unclassified .section-header { background: #f8fafc; }

        .section-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .section-body {
            padding: 20px;
        }

        /* Pathway Badges */
        .pathway-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .pathway-badge.badge-triple { background: var(--pathway-triple); color: white; }
        .pathway-badge.badge-double { background: var(--pathway-double); color: white; }
        .pathway-badge.badge-single { background: var(--pathway-single); color: white; }
        .pathway-badge.badge-unclassified { background: var(--pathway-unclassified); color: white; }

        /* Info chips per pathway */
        .section-card.pathway-triple .info-chip { background: #e0e7ff; color: #3730a3; }
        .section-card.pathway-double .info-chip { background: #fef3c7; color: #92400e; }
        .section-card.pathway-single .info-chip { background: #d1fae5; color: #065f46; }
        .section-card.pathway-unclassified .info-chip { background: #e2e8f0; color: #475569; }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .btn-loading .btn-text {
            display: inline-flex;
            align-items: center;
        }

        .btn-loading .btn-spinner {
            display: none;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex;
            align-items: center;
        }

        .table tbody td {
            vertical-align: middle;
            font-size: 13px;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .student-avatar-placeholder.male {
            background: #dbeafe;
            color: #1e40af;
        }

        .student-avatar-placeholder.female {
            background: #fce7f3;
            color: #be185d;
        }

        .student-name {
            font-weight: 600;
            color: #111827;
        }

        .student-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .target-note {
            color: #6b7280;
            font-size: 12px;
            margin-top: 10px;
        }

        /* Grade Badges */
        .grade-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .grade-badge.grade-a { background: #d1fae5; color: #059669; }
        .grade-badge.grade-b { background: #dbeafe; color: #3b82f6; }
        .grade-badge.grade-c { background: #fef9c3; color: #a16207; }
        .grade-badge.grade-d { background: #ffedd5; color: #ea580c; }
        .grade-badge.grade-e,
        .grade-badge.grade-u { background: #fee2e2; color: #ef4444; }
        .grade-badge.grade-m { background: #ede9fe; color: #8b5cf6; }

        .grade-na {
            color: #9ca3af;
            font-size: 12px;
            font-style: italic;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-pill i {
            font-size: 10px;
        }

        .status-pill.recommended {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.overflow {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pill.unclassified {
            background: #e5e7eb;
            color: #4b5563;
        }

        .empty-state {
            text-align: center;
            padding: 32px 16px;
            color: #6b7280;
        }

        /* Class Capacity Cards */
        .capacity-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .capacity-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px 14px;
            background: #f9fafb;
        }

        .capacity-card-name {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .capacity-card-count {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .capacity-card-count strong {
            color: #111827;
        }

        .capacity-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .capacity-bar-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease;
            background: #3b82f6;
        }

        .capacity-bar-fill.at-capacity {
            background: #ef4444;
        }

        .capacity-bar-fill.near-capacity {
            background: #f59e0b;
        }

        .capacity-input {
            width: 70px;
            padding: 4px 8px;
            font-size: 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
        }

        .capacity-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .manual-override-toggle {
            font-size: 11px;
            color: #6b7280;
            cursor: pointer;
            text-decoration: underline;
            text-decoration-style: dotted;
        }

        .manual-override-toggle:hover {
            color: #3b82f6;
        }

        .section-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            background: #f9fafb;
        }

        .section-footer .selected-count {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .section-footer .selected-count span {
            color: #3b82f6;
        }

        @media (max-width: 1200px) {
            .dashboard-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .section-header {
                padding: 14px 16px;
            }

            .section-body {
                padding: 16px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }

            .section-footer {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Admissions
        @endslot
        @slot('title')
            Placement Recommendations
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-outline label-icon"></i><strong>{{ session('warning') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $pathwayIcons = [
            'triple' => 'fas fa-flask',
            'double' => 'fas fa-vials',
            'single' => 'fas fa-atom',
            'unclassified' => 'fas fa-question-circle',
        ];
        $selectedAdmissions = collect(old('selected_admissions', []))->map(fn($id) => (int) $id)->all();
    @endphp

    <div class="settings-container">
        <div class="settings-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h3><i class="fas fa-layer-group me-2"></i>F4 Placement Recommendations</h3>
                    <p>Review recommended science pathways, select target students, and allocate them into matching Senior classes.</p>
                </div>
                @if ($selectedTerm)
                    <div class="d-flex flex-wrap gap-2">
                        <span class="metric-badge"><i class="fas fa-calendar-alt"></i> Term {{ $selectedTerm->term }}, {{ $selectedTerm->year }}</span>
                        <span class="metric-badge"><i class="fas fa-users"></i> {{ collect($placementGroups)->sum('count') }} Pending Admissions</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">Placement Workflow</div>
                <div class="help-content">
                    Use the selected term to generate recommendation groups from F3 junior-school grades. Students are ranked by Science, then Mathematics, then Overall grade. Allocate one pathway at a time — start with Triple Science, then Double, then Single when it is in use. Leave a student on Auto-assign to place them into a matching class, or use Choose Class to override them into any F4 class for the term.
                    <strong>Note:</strong> This page allocates students to base classes only. Optional science subjects (Biology, Chemistry, Physics) should be assigned separately through the Optional Subjects module after placement is complete.
                </div>
            </div>

            {{-- Missing Class Types Warning --}}
            @if (!empty($missingClassTypes))
                <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-1"></i> Missing Class Types</h6>
                    @if (count($missingClassTypes) === 1)
                        @php $missing = array_values($missingClassTypes)[0]; @endphp
                        <p class="mb-2">
                            No <strong>{{ $missing['class_type'] }}</strong> classes exist for this term.
                            {{ $missing['count'] }} {{ $missing['label'] }} student{{ $missing['count'] !== 1 ? 's' : '' }} cannot be auto-allocated.
                        </p>
                        <p class="mb-0" style="font-size: 13px;">
                            Create {{ $missing['class_type'] }} F4 classes in the
                            <a href="{{ route('finals.classes.index') }}" class="alert-link">Class Allocations</a>,
                            or use <strong>Choose Class</strong> on each student to place them into another F4 class.
                        </p>
                    @else
                        <p class="mb-2">The following class types have no F4 classes for this term:</p>
                        <ul class="mb-2">
                            @foreach ($missingClassTypes as $missing)
                                <li><strong>{{ $missing['class_type'] }}</strong> &mdash; {{ $missing['count'] }} {{ $missing['label'] }} student{{ $missing['count'] !== 1 ? 's' : '' }}</li>
                            @endforeach
                        </ul>
                        <p class="mb-0" style="font-size: 13px;">
                            Create the missing class types in the
                            <a href="{{ route('finals.classes.index') }}" class="alert-link">Class Allocations</a>,
                            or use <strong>Choose Class</strong> on individual students to assign them to another F4 class.
                        </p>
                    @endif
                </div>
            @endif

            {{-- Dashboard Summary Cards --}}
            @if (collect($placementGroups)->sum('count') > 0)
                <div class="dashboard-cards">
                    @foreach ($placementGroups as $group)
                        @php
                            $pw = $group['pathway'];
                            $target = $group['target_count'];
                            $count = $group['count'];
                            $pct = $target ? min(100, round(($count / $target) * 100)) : 0;
                        @endphp
                        <div class="dashboard-card {{ $pw }}">
                            <div class="dashboard-card-icon">
                                <i class="{{ $pathwayIcons[$pw] ?? 'fas fa-question-circle' }}"></i>
                            </div>
                            <div class="dashboard-card-label">{{ $group['label'] }}</div>
                            <div class="dashboard-card-count">{{ $count }}</div>
                            @if (!is_null($target))
                                <div class="dashboard-card-target">Target: {{ $target }} students</div>
                                <div class="dashboard-mini-bar">
                                    <div class="dashboard-mini-bar-fill" style="width: {{ $pct }}%"></div>
                                </div>
                            @else
                                <div class="dashboard-card-target">No target set</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('admissions.placement') }}" class="controls-row">
                <div>
                    <label for="term_id" class="form-label">Selected Term</label>
                    <select name="term_id" id="term_id" class="form-select">
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ (string) $selectedTermId === (string) $term->id ? 'selected' : '' }}>
                                Term {{ $term->term }}, {{ $term->year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="action-row">
                    <a href="{{ route('admissions.settings', ['summary_term_id' => $selectedTermId]) }}" class="btn btn-secondary">
                        <i class="fas fa-cog me-1"></i> Import / Criteria Settings
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-bolt me-1"></i> Generate Recommendations
                    </button>
                </div>
            </form>

            @foreach ($placementGroups as $group)
                @php
                    $classOptions = collect($classesByType->get($group['class_type'], []));
                    $allF4Classes = $classesByType->flatten();
                    $overrideClassOptions = $classOptions
                        ->concat($allF4Classes->reject(fn($klass) => $classOptions->contains('id', $klass->id)))
                        ->values();
                    $pw = $group['pathway'];
                    $isAllocatable = $pw !== 'unclassified'
                        && collect($group['students'])->isNotEmpty()
                        && $classOptions->isNotEmpty();
                    $isManualOnly = $pw !== 'unclassified'
                        && collect($group['students'])->isNotEmpty()
                        && $classOptions->isEmpty()
                        && $allF4Classes->isNotEmpty();
                @endphp

                @if ($isAllocatable || $isManualOnly)
                    <form method="POST" action="{{ route('admissions.allocate-placement') }}" class="pathway-form" data-pathway="{{ $pw }}">
                        @csrf
                        <input type="hidden" name="term_id" value="{{ $selectedTermId }}">
                        <input type="hidden" name="pathway" value="{{ $pw }}">
                @endif

                <div class="section-card pathway-{{ $pw }}">
                    <div class="section-header">
                        <div>
                            <h5>{{ $group['label'] }}</h5>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="info-chip">Count: {{ $group['count'] }}</span>
                                @if (!is_null($group['target_count']))
                                    <span class="info-chip">Target: {{ $group['target_count'] }}</span>
                                    <span class="info-chip">Pre-selected: {{ $group['selected_count'] }}</span>
                                @endif
                                @if ($group['class_type'])
                                    <span class="info-chip">Class Type: {{ $group['class_type'] }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="pathway-badge badge-{{ $pw }}">
                            <i class="{{ $pathwayIcons[$pw] ?? 'fas fa-question-circle' }}"></i>
                            {{ $group['label'] }}
                        </span>
                    </div>

                    <div class="section-body">
                        {{-- Class Capacity Cards --}}
                        @if ($pw !== 'unclassified' && $classOptions->isNotEmpty())
                            <div class="capacity-cards" id="capacityCards_{{ $pw }}">
                                @foreach ($classOptions as $klass)
                                    @php
                                        $currentCount = $klass->students_count ?? 0;
                                        $maxStudents = $klass->max_students;
                                        $pctUsed = $maxStudents ? min(100, round(($currentCount / $maxStudents) * 100)) : 0;
                                        $barClass = '';
                                        if ($maxStudents) {
                                            if ($currentCount >= $maxStudents) $barClass = 'at-capacity';
                                            elseif ($pctUsed >= 80) $barClass = 'near-capacity';
                                        }
                                    @endphp
                                    <div class="capacity-card">
                                        <div class="capacity-card-name">{{ $klass->name }}</div>
                                        <div class="capacity-card-count">
                                            <strong>{{ $currentCount }}</strong> / {{ $maxStudents ?? 'No limit' }} students
                                        </div>
                                        @if ($maxStudents)
                                            <div class="capacity-bar">
                                                <div class="capacity-bar-fill {{ $barClass }}" style="width: {{ $pctUsed }}%"></div>
                                            </div>
                                        @endif
                                        <div class="d-flex align-items-center gap-1">
                                            <label class="form-label mb-0" style="font-size: 11px; color: #6b7280;">Max:</label>
                                            <input type="number" min="0" class="capacity-input capacity-input-field"
                                                data-klass-id="{{ $klass->id }}"
                                                data-pathway="{{ $pw }}"
                                                value="{{ $maxStudents }}"
                                                placeholder="No limit">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-secondary btn-loading save-capacity-btn" data-pathway="{{ $pw }}">
                                    <span class="btn-text"><i class="fas fa-database me-1"></i> Save {{ $group['label'] }} Capacities</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        @endif

                        @if (collect($group['students'])->isEmpty())
                            <div class="empty-state">
                                <i class="fas fa-inbox mb-2" style="font-size: 28px;"></i>
                                <div>No admissions currently fall under {{ $group['label'] }} for the selected term.</div>
                            </div>
                        @else
                            {{-- In-section notice when pathway has no matching classes --}}
                            @if ($pw !== 'unclassified' && $classOptions->isEmpty())
                                <div class="alert alert-warning mb-3" role="alert">
                                    <strong><i class="fas fa-exclamation-triangle me-1"></i> No {{ $group['class_type'] }} classes found for this term.</strong>
                                    <p class="mb-1 mt-2">To allocate these {{ $group['count'] }} students:</p>
                                    <ul class="mb-0" style="font-size: 13px;">
                                        <li>Create {{ $group['class_type'] }} F4 classes in the <a href="{{ route('finals.classes.index') }}" class="alert-link">Class Allocations</a>, then return to this page</li>
                                        <li>Or use <strong>Choose Class</strong> on individual students to place them into any other F4 class for the term</li>
                                    </ul>
                                </div>
                            @endif
                            @if ($pw !== 'unclassified')
                                @php
                                    $selectableRows = collect($group['students'])->filter(function ($row) use ($group, $classOptions, $allF4Classes) {
                                        $checkboxDisabled = $group['pathway'] === 'unclassified'
                                            || ($group['class_type'] && $classOptions->isEmpty() && $allF4Classes->isEmpty());

                                        return !$checkboxDisabled;
                                    });
                                    $hasRecommendedRows = $selectableRows->contains(fn($row) => !empty($row['auto_selected']));
                                    $selectionMatchesDefault = $selectableRows->isNotEmpty()
                                        && $selectableRows->every(function ($row) use ($selectedAdmissions, $hasRecommendedRows) {
                                            $admissionId = (int) data_get($row, 'admission.id');
                                            $isSelected = in_array($admissionId, $selectedAdmissions, true)
                                                || (empty($selectedAdmissions) && !empty($row['auto_selected']));
                                            $shouldBeSelected = $hasRecommendedRows ? !empty($row['auto_selected']) : true;

                                            return $isSelected === $shouldBeSelected;
                                        });
                                    $selectButtonLabel = $selectionMatchesDefault
                                        ? 'Clear Selection'
                                        : ($hasRecommendedRows ? 'Select Recommended' : 'Select All');
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <button type="button" class="btn btn-sm btn-secondary select-recommended-btn" data-pathway="{{ $pw }}">
                                        <i class="fas fa-check-double me-1"></i>
                                        <span class="select-action-label">{{ $selectButtonLabel }}</span>
                                    </button>
                                    <span class="selected-count" style="font-size: 13px; font-weight: 600; color: #374151;">
                                        <i class="fas fa-check-square me-1 text-primary"></i>
                                        <span class="selected-count-number" data-pathway="{{ $pw }}">0</span> selected
                                    </span>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 48px;">#</th>
                                            <th style="width: 56px;">Pick</th>
                                            <th>Student</th>
                                            <th>Connect ID</th>
                                            <th>Science / P. Agric</th>
                                            <th>Mathematics</th>
                                            <th>Overall</th>
                                            <th>Status</th>
                                            <th style="width: 180px;">Class</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($group['students'] as $row)
                                            @php
                                                $admission = $row['admission'];
                                                $checkboxDisabled = $group['pathway'] === 'unclassified' || ($group['class_type'] && $classOptions->isEmpty() && $allF4Classes->isEmpty());
                                                $defaultClassId = old("allocations.{$admission->id}.klass_id");
                                                $showOverrideSelect = filled((string) $defaultClassId);
                                                $isChecked = in_array((int) $admission->id, $selectedAdmissions, true) || (empty($selectedAdmissions) && $row['auto_selected']);
                                            @endphp
                                            <tr>
                                                <td>{{ $row['rank'] }}</td>
                                                <td class="text-center">
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input placement-checkbox"
                                                        name="selected_admissions[]"
                                                        value="{{ $admission->id }}"
                                                        data-pathway="{{ $pw }}"
                                                        data-default-selected="{{ $row['auto_selected'] ? 1 : 0 }}"
                                                        {{ $isChecked && !$checkboxDisabled ? 'checked' : '' }}
                                                        {{ $checkboxDisabled ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <div class="student-cell">
                                                        @php
                                                            $initials = strtoupper(substr($admission->first_name ?? '', 0, 1) . substr($admission->last_name ?? '', 0, 1));
                                                            $genderClass = $admission->gender == 'M' ? 'male' : 'female';
                                                        @endphp
                                                        <div class="student-avatar-placeholder {{ $genderClass }}">{{ $initials ?: 'ST' }}</div>
                                                        <div>
                                                            <div class="student-name">{{ $admission->full_name }}</div>
                                                            <div class="student-meta">Status: {{ $admission->status }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $admission->connect_id ?: 'N/A' }}</td>
                                                <td>
                                                    @if ($row['science'])
                                                        <span class="grade-badge grade-{{ strtolower($row['science']) }}">{{ $row['science'] }}</span>
                                                        @if (($row['science_subject'] ?? null) === 'Private Agriculture')
                                                            <span class="badge bg-success-subtle text-success" style="font-size: 10px; margin-left: 4px;">P.Agric</span>
                                                        @endif
                                                    @else
                                                        <span class="grade-na">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($row['mathematics'])
                                                        <span class="grade-badge grade-{{ strtolower($row['mathematics']) }}">{{ $row['mathematics'] }}</span>
                                                    @else
                                                        <span class="grade-na">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($row['overall'])
                                                        <span class="grade-badge grade-{{ strtolower($row['overall']) }}">{{ $row['overall'] }}</span>
                                                    @else
                                                        <span class="grade-na">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($group['pathway'] === 'unclassified')
                                                        <span class="status-pill unclassified"><i class="fas fa-question-circle"></i> Unclassified</span>
                                                    @elseif ($row['auto_selected'])
                                                        <span class="status-pill recommended"><i class="fas fa-check-circle"></i> Recommended</span>
                                                    @else
                                                        <span class="status-pill overflow"><i class="fas fa-arrow-circle-down"></i> Overflow</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($group['pathway'] === 'unclassified')
                                                        <span class="text-muted" style="font-size: 12px;">Complete grades first</span>
                                                    @elseif ($classOptions->isEmpty() && $allF4Classes->isEmpty())
                                                        <span class="text-danger" style="font-size: 12px;">No F4 classes found</span>
                                                    @elseif ($classOptions->isEmpty())
                                                        <span
                                                            class="manual-override-toggle"
                                                            data-admission="{{ $admission->id }}"
                                                            data-open-label="Choose Class"
                                                            data-close-label="Hide Class Choices">
                                                            <i class="fas fa-sliders-h me-1"></i><span class="override-label">{{ $showOverrideSelect ? 'Hide Class Choices' : 'Choose Class' }}</span>
                                                        </span>
                                                        <select class="form-select form-select-sm mt-1 manual-class-select {{ $showOverrideSelect ? '' : 'd-none' }}"
                                                            name="allocations[{{ $admission->id }}][klass_id]"
                                                            data-admission="{{ $admission->id }}"
                                                            {{ $showOverrideSelect ? '' : 'disabled' }}>
                                                            <option value="">-- Select a class --</option>
                                                            @foreach ($overrideClassOptions as $klass)
                                                                <option value="{{ $klass->id }}" {{ (string) $defaultClassId === (string) $klass->id ? 'selected' : '' }}>
                                                                    {{ $klass->name }} ({{ $klass->type }}) ({{ $klass->students_count ?? 0 }}{{ $klass->max_students ? '/' . $klass->max_students : '' }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <span class="auto-assign-label" data-admission="{{ $admission->id }}" style="font-size: 12px; color: #6b7280;">
                                                            <i class="fas fa-magic me-1"></i>Auto-assign to {{ $group['class_type'] }}
                                                        </span>
                                                        <span
                                                            class="manual-override-toggle"
                                                            data-admission="{{ $admission->id }}"
                                                            data-open-label="Choose Class"
                                                            data-close-label="Use Auto-assign">
                                                            <i class="fas fa-sliders-h me-1"></i><span class="override-label">{{ $showOverrideSelect ? 'Use Auto-assign' : 'Choose Class' }}</span>
                                                        </span>
                                                        <select class="form-select form-select-sm mt-1 manual-class-select {{ $showOverrideSelect ? '' : 'd-none' }}"
                                                            name="allocations[{{ $admission->id }}][klass_id]"
                                                            data-admission="{{ $admission->id }}"
                                                            {{ $showOverrideSelect ? '' : 'disabled' }}>
                                                            <option value="">Keep auto-assign</option>
                                                            @foreach ($overrideClassOptions as $klass)
                                                                <option value="{{ $klass->id }}" {{ (string) $defaultClassId === (string) $klass->id ? 'selected' : '' }}>
                                                                    {{ $klass->name }} ({{ $klass->type }}) ({{ $klass->students_count ?? 0 }}{{ $klass->max_students ? '/' . $klass->max_students : '' }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if (!is_null($group['target_count']))
                                <div class="target-note">
                                    Students are ranked by Science, Mathematics, then Overall grade. The first {{ $group['target_count'] }} students in {{ $group['label'] }} are pre-selected. Auto-assign uses {{ $group['class_type'] }} classes first, while Choose Class lets you override to any other F4 class for the term.
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Per-pathway allocate footer --}}
                    @if ($isAllocatable)
                        <div class="section-footer">
                            <div class="selected-count">
                                <i class="fas fa-check-square me-1 text-primary"></i>
                                <span class="selected-count-number" data-pathway="{{ $pw }}">0</span> students selected
                            </div>
                            <button type="submit" class="btn btn-primary btn-loading allocate-btn" data-pathway="{{ $pw }}">
                                <span class="btn-text"><i class="fas fa-user-check me-1"></i> Allocate {{ $group['label'] }}</span>
                                <span class="btn-spinner">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Allocating...
                                </span>
                            </button>
                        </div>
                    @elseif ($isManualOnly)
                        <div class="section-footer">
                            <div class="selected-count">
                                <i class="fas fa-check-square me-1 text-primary"></i>
                                <span class="selected-count-number" data-pathway="{{ $pw }}">0</span> students selected
                            </div>
                            <button type="submit" class="btn btn-primary btn-loading allocate-btn" data-pathway="{{ $pw }}">
                                <span class="btn-text"><i class="fas fa-user-check me-1"></i> Allocate {{ $group['label'] }} (Choose Classes)</span>
                                <span class="btn-spinner">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Allocating...
                                </span>
                            </button>
                        </div>
                    @endif
                </div>

                @if ($isAllocatable || $isManualOnly)
                    </form>
                @endif
            @endforeach
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function getEnabledCheckboxes(pathway) {
                return Array.prototype.slice.call(document.querySelectorAll('.placement-checkbox[data-pathway="' + pathway + '"]'))
                    .filter(function(checkbox) {
                        return !checkbox.disabled;
                    });
            }

            function hasRecommendedRows(checkboxes) {
                return checkboxes.some(function(checkbox) {
                    return checkbox.dataset.defaultSelected === '1';
                });
            }

            function matchesIntendedSelection(checkboxes, recommendedOnly) {
                if (checkboxes.length === 0) {
                    return false;
                }

                return checkboxes.every(function(checkbox) {
                    var shouldBeSelected = recommendedOnly ? checkbox.dataset.defaultSelected === '1' : true;
                    return checkbox.checked === shouldBeSelected;
                });
            }

            function updateSelectButtonState(pathway) {
                var button = document.querySelector('.select-recommended-btn[data-pathway="' + pathway + '"]');
                if (!button) {
                    return;
                }

                var label = button.querySelector('.select-action-label');
                var checkboxes = getEnabledCheckboxes(pathway);

                if (checkboxes.length === 0) {
                    if (label) {
                        label.textContent = 'Select All';
                    }
                    return;
                }

                var recommendedOnly = hasRecommendedRows(checkboxes);
                var nextActionLabel = matchesIntendedSelection(checkboxes, recommendedOnly)
                    ? 'Clear Selection'
                    : (recommendedOnly ? 'Select Recommended' : 'Select All');

                if (label) {
                    label.textContent = nextActionLabel;
                }
            }

            // Select buttons — scoped per pathway
            document.querySelectorAll('.select-recommended-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var pathway = this.dataset.pathway;
                    var checkboxes = getEnabledCheckboxes(pathway);
                    var recommendedOnly = hasRecommendedRows(checkboxes);
                    var shouldClear = matchesIntendedSelection(checkboxes, recommendedOnly);

                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = shouldClear
                            ? false
                            : (recommendedOnly ? checkbox.dataset.defaultSelected === '1' : true);
                    });

                    updateSelectedCount(pathway);
                    updateSelectButtonState(pathway);
                });
            });

            // Update selected count per pathway
            function updateSelectedCount(pathway) {
                var checked = document.querySelectorAll('.placement-checkbox[data-pathway="' + pathway + '"]:checked:not(:disabled)');
                var countEls = document.querySelectorAll('.selected-count-number[data-pathway="' + pathway + '"]');
                countEls.forEach(function(el) {
                    el.textContent = checked.length;
                });
            }

            // Listen to checkbox changes
            document.querySelectorAll('.placement-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    updateSelectedCount(this.dataset.pathway);
                    updateSelectButtonState(this.dataset.pathway);
                });
            });

            // Initialize counts and button labels
            var seenPathways = {};
            Array.prototype.slice.call(document.querySelectorAll('[data-pathway]')).forEach(function(el) {
                if (el.dataset.pathway) {
                    seenPathways[el.dataset.pathway] = true;
                }
            });

            Object.keys(seenPathways).forEach(function(pathway) {
                updateSelectedCount(pathway);
                updateSelectButtonState(pathway);
            });

            // Form submit — add loading state to that section's allocate button
            document.querySelectorAll('.pathway-form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    var pathway = this.dataset.pathway;

                    // Disable manual override selects that are still on "Auto-assign"
                    this.querySelectorAll('.manual-class-select').forEach(function(select) {
                        if (select.disabled || !select.value) {
                            select.disabled = true;
                        }
                    });

                    var allocateBtn = this.querySelector('.allocate-btn');
                    if (allocateBtn) {
                        allocateBtn.classList.add('loading');
                        allocateBtn.disabled = true;
                    }
                });
            });

            // Manual override toggles
            document.querySelectorAll('.manual-override-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    var admissionId = this.dataset.admission;
                    var select = document.querySelector('.manual-class-select[data-admission="' + admissionId + '"]');
                    var autoLabel = document.querySelector('.auto-assign-label[data-admission="' + admissionId + '"]');
                    var label = this.querySelector('.override-label');

                    if (select) {
                        var isHidden = select.classList.contains('d-none');
                        if (isHidden) {
                            select.classList.remove('d-none');
                            select.disabled = false;
                            if (autoLabel) autoLabel.classList.add('d-none');
                            if (label) label.textContent = this.dataset.closeLabel || 'Use Auto-assign';
                        } else {
                            select.classList.add('d-none');
                            select.disabled = true;
                            select.value = '';
                            if (autoLabel) autoLabel.classList.remove('d-none');
                            if (label) label.textContent = this.dataset.openLabel || 'Choose Class';
                        }
                    }
                });
            });

            // Save capacity buttons — scoped per pathway
            document.querySelectorAll('.save-capacity-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var pathway = this.dataset.pathway;
                    var inputs = document.querySelectorAll('.capacity-input-field[data-pathway="' + pathway + '"]');
                    var capacities = [];

                    inputs.forEach(function(input) {
                        capacities.push({
                            klass_id: input.dataset.klassId,
                            max_students: input.value === '' ? null : parseInt(input.value, 10)
                        });
                    });

                    if (capacities.length === 0) return;

                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admissions.update-class-capacity") }}';

                    var csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    var termInput = document.createElement('input');
                    termInput.type = 'hidden';
                    termInput.name = 'term_id';
                    termInput.value = '{{ $selectedTermId }}';
                    form.appendChild(termInput);

                    capacities.forEach(function(cap, i) {
                        var klassInput = document.createElement('input');
                        klassInput.type = 'hidden';
                        klassInput.name = 'capacities[' + i + '][klass_id]';
                        klassInput.value = cap.klass_id;
                        form.appendChild(klassInput);

                        var maxInput = document.createElement('input');
                        maxInput.type = 'hidden';
                        maxInput.name = 'capacities[' + i + '][max_students]';
                        maxInput.value = cap.max_students === null ? '' : cap.max_students;
                        form.appendChild(maxInput);
                    });

                    document.body.appendChild(form);

                    btn.classList.add('loading');
                    btn.disabled = true;
                    form.submit();
                });
            });
        });
    </script>
@endsection
