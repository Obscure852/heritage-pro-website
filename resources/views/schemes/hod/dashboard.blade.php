@extends('layouts.master')
@section('title')
    HOD Dashboard — Schemes of Work
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            HOD Dashboard
        @endslot
    @endcomponent

    @php
        $statusColors = [
            'draft'                => 'secondary',
            'submitted'            => 'info',
            'supervisor_reviewed'  => 'primary',
            'under_review'         => 'warning',
            'approved'             => 'success',
            'revision_required'    => 'danger',
        ];

        $termLabel = $currentTerm ? 'Term ' . $currentTerm->term . ' ' . $currentTerm->year : '—';
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
                <option value="{{ $t->id }}" {{ $t->id == $currentTerm->id ? 'selected' : '' }}>
                    Term {{ $t->term }}, {{ $t->year }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="schemes-container">
        <div class="header">
            <h3 style="margin: 0;">HOD Dashboard — Schemes of Work</h3>
            <p style="margin: 6px 0 0 0; opacity: .9;">{{ $termLabel }}</p>
        </div>

        <div class="form-container">

            {{-- Summary Stats --}}
            @php
                $totalCount    = $schemes->count();
                $approvedCount = $schemes->where('status', 'approved')->count();
                $pendingCount  = $pending->count();
                $draftCount    = $schemes->where('status', 'draft')->count();
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card stat-primary animate-in" style="--i: 0">
                        <span class="stat-icon icon-primary"><i class="bx bx-file text-primary"></i></span>
                        <div class="stat-number text-primary">{{ $totalCount }}</div>
                        <div class="stat-label">Total Schemes</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card stat-success animate-in" style="--i: 1">
                        <span class="stat-icon icon-success"><i class="bx bx-check-circle text-success"></i></span>
                        <div class="stat-number text-success">{{ $approvedCount }}</div>
                        <div class="stat-label">Approved</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card stat-warning animate-in" style="--i: 2">
                        <span class="stat-icon icon-warning"><i class="bx bx-time-five text-warning"></i></span>
                        <div class="stat-number text-warning">{{ $pendingCount }}</div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card stat-secondary animate-in" style="--i: 3">
                        <span class="stat-icon icon-secondary"><i class="bx bx-edit text-secondary"></i></span>
                        <div class="stat-number text-secondary">{{ $draftCount }}</div>
                        <div class="stat-label">Draft</div>
                    </div>
                </div>
            </div>

            {{-- Standard Schemes Section --}}
            @php
                $standardSchemes = \App\Models\Schemes\StandardScheme::query()
                    ->where('term_id', $currentTerm->id)
                    ->visibleTo(auth()->user())
                    ->with(['subject', 'grade'])
                    ->withCount('derivedSchemes')
                    ->get();
            @endphp
            @if ($standardSchemes->isNotEmpty())
                <div class="section-title">Standard Schemes</div>
                <div class="help-text" style="border-left-color: #8b5cf6;">
                    <div class="help-title">Department Standard Schemes</div>
                    <div class="help-content">Subject-level schemes for {{ $termLabel }}. Publish and distribute to all teachers.</div>
                </div>
                <div class="table-responsive mb-4">
                    <table class="table scheme-table mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Status</th>
                                <th>Published</th>
                                <th>Distributed</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($standardSchemes as $ss)
                                <tr>
                                    <td>{{ $ss->subject?->name }}</td>
                                    <td>{{ $ss->grade?->name }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $ss->status }}">
                                            <span class="status-dot dot-{{ $ss->status }}"></span>
                                            {{ ucfirst(str_replace('_', ' ', $ss->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($ss->published_at)
                                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 11px;">Yes</span>
                                        @else
                                            <span class="text-muted" style="font-size: 12px;">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $ss->derived_schemes_count }}</td>
                                    <td>
                                        <a href="{{ route('standard-schemes.show', $ss) }}" class="btn btn-sm btn-outline-primary btn-action">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            @can('manage-standard-schemes')
                @if ($standardSchemes->isEmpty())
                    <div class="manage-card mb-4" style="cursor: pointer;" onclick="window.location='{{ route('standard-schemes.create') }}'">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div class="card-icon"><i class="bx bx-layer"></i></div>
                            <div>
                                <div style="font-weight: 600; color: #1f2937; font-size: 15px;">Create Standard Schemes</div>
                            </div>
                        </div>
                        <p style="font-size: 13px; color: #6b7280; margin: 0;">
                            No standard schemes for this term yet. Create subject-level schemes to distribute to teachers.
                        </p>
                    </div>
                @endif
            @endcan

            {{-- Pending Reviews Section --}}
            <div class="section-title">Pending Reviews</div>

            <div class="help-text">
                <div class="help-title">Action Required</div>
                <div class="help-content">
                    These schemes require your attention — review and approve or return for revision.
                </div>
            </div>

            @if ($pending->isEmpty())
                <div class="empty-state">
                    <i class="bx bx-check-double"></i>
                    <p>No schemes pending review.</p>
                </div>
            @else
                @php
                    $sortedPending = $pending->sortBy(function ($s) {
                        return $s->status === 'submitted' ? 0 : 1;
                    });
                @endphp
                <div class="table-responsive mb-4">
                    <table class="table scheme-table mb-0">
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sortedPending as $scheme)
                                @php
                                    $subjectName = $scheme->klassSubject?->gradeSubject?->subject?->name
                                        ?? $scheme->optionalSubject?->gradeSubject?->subject?->name
                                        ?? '—';
                                    $classLabel = $scheme->klassSubject?->klass?->name
                                        ?? 'Optional';
                                    $badgeColor = $statusColors[$scheme->status] ?? 'secondary';
                                @endphp
                                <tr class="row-{{ $scheme->status }} animate-in" style="--i: {{ $loop->index }}">
                                    <td>{{ $scheme->teacher?->name ?? '—' }}</td>
                                    <td>{{ $subjectName }}</td>
                                    <td>{{ $classLabel }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $scheme->status }}">
                                            <span class="status-dot dot-{{ $scheme->status }}"></span>
                                            {{ ucfirst(str_replace('_', ' ', $scheme->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $scheme->updated_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('schemes.show', $scheme) }}"
                                               class="btn btn-sm btn-primary btn-action">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                            <a href="{{ route('schemes.document', $scheme) }}"
                                               class="btn btn-sm btn-outline-secondary btn-action" title="View Document">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Coverage Metrics Section --}}
            <div class="section-title">Objective Coverage by Teacher</div>

            <div class="help-text">
                <div class="help-title">About Coverage</div>
                <div class="help-content">
                    Coverage shows how many syllabus objectives are planned in scheme entries, taught via lesson plans, and assessed via linked tests.
                </div>
            </div>

            @if ($coverage->isEmpty())
                <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bx bx-info-circle fs-5"></i>
                    No scheme data available for coverage analysis this term.
                </div>
            @else
                <div class="table-responsive mb-4">
                    <table class="table scheme-table mb-0">
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Planned</th>
                                <th>Taught</th>
                                <th>Assessed</th>
                                <th>Coverage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($coverage as $row)
                                @php
                                    $coverageBadge = $statusColors[$row->scheme_status] ?? 'secondary';
                                    if ($row->planned_count > 0) {
                                        $coveragePct = round(($row->assessed_count / $row->planned_count) * 100);
                                        $coverageColor = $coveragePct >= 75 ? 'success' : ($coveragePct >= 50 ? 'warning' : 'danger');
                                        $coverageLabel = $coveragePct . '%';
                                    } else {
                                        $coveragePct = 0;
                                        $coverageColor = 'secondary';
                                        $coverageLabel = 'N/A';
                                    }
                                @endphp
                                <tr class="animate-in" style="--i: {{ $loop->index }}">
                                    <td>{{ $row->teacher_name }}</td>
                                    <td>{{ $row->subject_name }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $row->scheme_status }}">
                                            {{ ucfirst(str_replace('_', ' ', $row->scheme_status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $row->planned_count }}</td>
                                    <td>{{ $row->taught_count }}</td>
                                    <td>{{ $row->assessed_count }}</td>
                                    <td>
                                        <div class="coverage-bar-wrap">
                                            <div class="coverage-bar">
                                                <div class="coverage-bar-fill fill-{{ $coverageColor }}" style="width: {{ $coveragePct }}%"></div>
                                            </div>
                                            <span class="badge bg-{{ $coverageColor }}" style="font-size: 11px;">{{ $coverageLabel }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- All Department Schemes Section --}}
            <div class="section-title">All Department Schemes — {{ $termLabel }}</div>

            @if ($schemes->isEmpty())
                <div class="empty-state">
                    <i class="bx bx-folder-open"></i>
                    <p>No schemes found for your department(s) this term.</p>
                </div>
            @else
                @php
                    $groupedByTeacher = $schemes->groupBy('teacher_id');
                @endphp

                @foreach ($groupedByTeacher as $teacherId => $teacherSchemes)
                    @php
                        $teacherName = $teacherSchemes->first()->teacher?->name ?? 'Unknown Teacher';
                        $initials = collect(explode(' ', $teacherName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                    @endphp
                    <div class="teacher-group animate-in" style="--i: {{ $loop->index }}">
                        <div class="teacher-heading">
                            <span class="teacher-avatar">{{ $initials }}</span>
                            {{ $teacherName }}
                            <span class="badge bg-light text-secondary" style="font-weight: 500; font-size: 12px;">
                                {{ $teacherSchemes->count() }} scheme{{ $teacherSchemes->count() !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table scheme-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Weeks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teacherSchemes as $scheme)
                                        @php
                                            $subjectName = $scheme->klassSubject?->gradeSubject?->subject?->name
                                                ?? $scheme->optionalSubject?->gradeSubject?->subject?->name
                                                ?? '—';
                                            $classLabel = $scheme->klassSubject?->klass?->name
                                                ?? 'Optional';
                                            $badgeColor = $statusColors[$scheme->status] ?? 'secondary';
                                        @endphp
                                        <tr class="row-{{ $scheme->status }}">
                                            <td>{{ $subjectName }}</td>
                                            <td>{{ $classLabel }}</td>
                                            <td>
                                                <span class="status-badge status-{{ $scheme->status }}">
                                                    {{ ucfirst(str_replace('_', ' ', $scheme->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $scheme->total_weeks }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('schemes.show', $scheme) }}"
                                                       class="btn btn-sm btn-outline-primary btn-action" title="View Scheme">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('schemes.document', $scheme) }}"
                                                       class="btn btn-sm btn-outline-secondary btn-action" title="View Document">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
@endsection
@section('script')
    <script>
        document.getElementById('termSelector').addEventListener('change', function () {
            window.location.href = "{{ route('schemes.hod.dashboard') }}" + '?term_id=' + this.value;
        });
    </script>
@endsection
