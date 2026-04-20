@extends('layouts.master')
@section('title')
    Admin Dashboard — Schemes of Work
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        .schemes-container { box-shadow: none; }
        .stat-item { padding: 10px 0; }
        .stat-item h4 { font-size: 1.5rem; font-weight: 700; }
        .stat-item small { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }

        .completion-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        @media (max-width: 768px) {
            .completion-grid { grid-template-columns: repeat(3, 1fr); }
        }
        .completion-tile {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 14px 12px;
            text-align: center;
            background: #fff;
            transition: border-color 0.15s;
        }
        .completion-tile:hover { border-color: #93c5fd; }
        .completion-tile .tile-number {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
        }
        .completion-tile .tile-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-top: 4px;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Admin Dashboard
        @endslot
    @endcomponent

    @php
        $termLabel = $term ? 'Term ' . $term->term . ' ' . $term->year : '—';

        $statuses = [
            'draft'                => ['label' => 'Draft',               'badge' => 'secondary', 'icon' => 'bx bx-edit'],
            'submitted'            => ['label' => 'Submitted',           'badge' => 'info',      'icon' => 'bx bx-upload'],
            'supervisor_reviewed'  => ['label' => 'Supervisor Reviewed', 'badge' => 'primary',   'icon' => 'bx bx-user-check'],
            'under_review'         => ['label' => 'Under Review',        'badge' => 'warning',   'icon' => 'bx bx-time-five'],
            'approved'             => ['label' => 'Approved',            'badge' => 'success',   'icon' => 'bx bx-check-circle'],
            'revision_required'    => ['label' => 'Revision Required',   'badge' => 'danger',    'icon' => 'bx bx-revision'],
        ];

        $total = array_sum($completion);
        $missingCount = $missingSchemes->count();
    @endphp

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

    <div class="d-flex justify-content-end mb-3">
        <select id="termSelector" class="form-select term-select">
            @foreach ($terms as $t)
                <option value="{{ $t->id }}" {{ $t->id == $term->id ? 'selected' : '' }}>
                    Term {{ $t->term }}, {{ $t->year }}
                </option>
            @endforeach
        </select>
    </div>

    @php
        $ssTotal = \App\Models\Schemes\StandardScheme::where('term_id', $term->id)->count();
        $ssApproved = \App\Models\Schemes\StandardScheme::where('term_id', $term->id)->where('status', 'approved')->count();
        $ssPublished = \App\Models\Schemes\StandardScheme::where('term_id', $term->id)->whereNotNull('published_at')->count();
        $ssDistributed = \App\Models\Schemes\StandardScheme::where('term_id', $term->id)->whereHas('derivedSchemes')->count();
    @endphp

    <div class="schemes-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin: 0;">Admin Dashboard — Scheme of Work</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">{{ $termLabel }}</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $ssTotal }}</h4>
                                <small class="opacity-75">Standard</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $ssApproved }}</h4>
                                <small class="opacity-75">Approved</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $ssPublished }}</h4>
                                <small class="opacity-75">Published</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $ssDistributed }}</h4>
                                <small class="opacity-75">Distributed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container">

            {{-- Section 1: School-Wide Completion --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-title" style="margin-bottom: 0;">Scheme Completion Overview</div>
                @if ($grades->isNotEmpty())
                    <select id="gradeFilter" class="form-select form-select-sm" style="width: auto; min-width: 120px;">
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" {{ (int) $selectedGradeId === $grade->id ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="completion-grid">
                <div class="completion-tile">
                    <div class="tile-number" style="color: #1e293b;">{{ $total }}</div>
                    <div class="tile-label">Total</div>
                </div>
                @foreach ($statuses as $statusKey => $statusMeta)
                    @php
                        $count = $completion[$statusKey] ?? 0;
                        $colorMap = [
                            'secondary' => '#64748b',
                            'info'      => '#0891b2',
                            'primary'   => '#3b82f6',
                            'warning'   => '#d97706',
                            'success'   => '#16a34a',
                            'danger'    => '#dc2626',
                        ];
                        $tileColor = $colorMap[$statusMeta['badge']] ?? '#64748b';
                    @endphp
                    <div class="completion-tile">
                        <div class="tile-number" style="color: {{ $tileColor }};">{{ $count }}</div>
                        <div class="tile-label">{{ $statusMeta['label'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Section 2: Missing Schemes --}}
            <div class="section-title">
                Missing Schemes
                @if ($missingCount > 0)
                    <span class="badge bg-danger ms-2" style="font-size: 12px; border-radius: 20px; padding: 3px 10px;">{{ $missingCount }}</span>
                @endif
            </div>

            <div class="help-text mb-3">
                <div class="help-content">
                    Teachers with class assignments for {{ $termLabel }} who have not yet received or created a scheme.
                </div>
            </div>

            @if ($missingSchemes->isEmpty())
                <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                    <i class="bx bx-check-circle fs-5" style="animation: checkPop 0.4s ease-out both;"></i>
                    All teachers have schemes for their assignments in this grade.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table missing-table mb-0">
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($missingSchemes as $row)
                                <tr class="animate-in" style="--i: {{ $loop->index }}">
                                    <td>{{ $row->teacher_name }}</td>
                                    <td>{{ $row->subject_name }}</td>
                                    <td>{{ $row->class_name ?? '—' }}</td>
                                    <td>{{ $row->grade_name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
@endsection
@section('script')
    <script>
        function navigateDashboard() {
            var termId = document.getElementById('termSelector').value;
            var gradeId = document.getElementById('gradeFilter')?.value || '';
            var params = '?term_id=' + termId;
            if (gradeId) params += '&grade_id=' + gradeId;
            window.location.href = "{{ route('schemes.admin.dashboard') }}" + params;
        }

        document.getElementById('termSelector').addEventListener('change', navigateDashboard);

        var gradeFilter = document.getElementById('gradeFilter');
        if (gradeFilter) {
            gradeFilter.addEventListener('change', navigateDashboard);
        }
    </script>
@endsection
