@extends('layouts.master')
@section('title')
    Supervisor Dashboard — Schemes of Work
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
            Supervisor Dashboard
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
            <h3 style="margin: 0;">Supervisor Dashboard — Schemes of Work</h3>
            <p style="margin: 6px 0 0 0; opacity: .9;">{{ $termLabel }}</p>
        </div>

        <div class="form-container">

            {{-- Summary Stats --}}
            @php
                $totalCount    = $schemes->count();
                $pendingCount  = $pending->count();
                $approvedCount = $schemes->whereIn('status', ['supervisor_reviewed', 'under_review', 'approved'])->count();
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
                    <div class="stat-card stat-warning animate-in" style="--i: 1">
                        <span class="stat-icon icon-warning"><i class="bx bx-time-five text-warning"></i></span>
                        <div class="stat-number text-warning">{{ $pendingCount }}</div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card stat-success animate-in" style="--i: 2">
                        <span class="stat-icon icon-success"><i class="bx bx-check-circle text-success"></i></span>
                        <div class="stat-number text-success">{{ $approvedCount }}</div>
                        <div class="stat-label">Forwarded / Approved</div>
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

            {{-- Pending Reviews Section --}}
            <div class="section-title">Pending Reviews</div>

            <div class="help-text">
                <div class="help-title">Action Required</div>
                <div class="help-content">
                    These schemes from your direct reports require your review — forward to HOD or return for revision.
                </div>
            </div>

            @if ($pending->isEmpty())
                <div class="empty-state">
                    <i class="bx bx-check-double"></i>
                    <p>No schemes pending your review.</p>
                </div>
            @else
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
                            @foreach ($pending as $scheme)
                                @php
                                    $subjectName = $scheme->klassSubject?->gradeSubject?->subject?->name
                                        ?? $scheme->optionalSubject?->gradeSubject?->subject?->name
                                        ?? '—';
                                    $classLabel = $scheme->klassSubject?->klass?->name
                                        ?? 'Optional';
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

            {{-- All Subordinate Schemes Section --}}
            <div class="section-title">All Subordinate Schemes — {{ $termLabel }}</div>

            @if ($schemes->isEmpty())
                <div class="empty-state">
                    <i class="bx bx-folder-open"></i>
                    <p>No schemes found for your direct reports this term.</p>
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
            window.location.href = "{{ route('schemes.supervisor.dashboard') }}" + '?term_id=' + this.value;
        });
    </script>
@endsection
