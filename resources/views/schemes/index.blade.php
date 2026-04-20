@extends('layouts.master')
@section('title')
    Schemes of Work
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        .schemes-container { box-shadow: none; }

        .view-toggle { display: inline-flex; border: 1px solid #d1d5db; border-radius: 3px; overflow: hidden; }
        .view-toggle-btn {
            padding: 6px 12px;
            background: #fff;
            border: none;
            color: #6b7280;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .view-toggle-btn + .view-toggle-btn { border-left: 1px solid #d1d5db; }
        .view-toggle-btn.active { background: #1e293b; color: #fff; }
        .view-toggle-btn:hover:not(.active) { background: #f1f5f9; }

        .scheme-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
        }
        .scheme-grid > a {
            display: flex;
        }
        .scheme-card {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 16px;
            background: #fff;
            transition: border-color 0.15s, box-shadow 0.15s;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }
        .scheme-card:hover { border-color: #93c5fd; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
        .scheme-card-subject {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }
        .scheme-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 12px;
            color: #64748b;
        }
        .scheme-card-meta span { display: inline-flex; align-items: center; gap: 4px; }
        .scheme-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid #f1f5f9;
        }
        .stat-item { padding: 10px 0; }
        .stat-item h4 { font-size: 1.5rem; font-weight: 700; }
        .stat-item small { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Schemes of Work
        @endslot
        @slot('title')
            {{ $schemeListTitle ?? 'Schemes of Work' }}
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
        $totalCount = $schemes->total();
        $approvedCount = $schemes->getCollection()->where('status', 'approved')->count();
        $draftCount = $schemes->getCollection()->where('status', 'draft')->count();
        $standardCount = $schemes->getCollection()->filter(fn($s) => $s->standard_scheme_id)->count();
    @endphp

    <div class="schemes-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin: 0;">Schemes of Work</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">{{ $schemeListHelp ?? 'Manage your scheme of work plans' }}</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $approvedCount }}</h4>
                                <small class="opacity-75">Approved</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $draftCount }}</h4>
                                <small class="opacity-75">Draft</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $standardCount }}</h4>
                                <small class="opacity-75">Standard</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container">
            <div class="help-text">
                <div class="help-title">Schemes of Work Module</div>
                <div class="help-content">
                    {{ $schemeListHelp ?? 'Create and manage structured schemes of work tied to syllabus objectives.' }}
                </div>
            </div>

            @can('create', \App\Models\Schemes\SchemeOfWork::class)
                <div class="text-end mb-3">
                    <a href="{{ route('schemes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create New Scheme
                    </a>
                </div>
            @endcan

            @can('manage-syllabi')
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <a href="{{ route('syllabi.index') }}" class="text-decoration-none">
                            <div class="manage-card">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                    <div class="card-icon">
                                        <i class="bx bx-book-open"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1f2937; font-size: 15px;">Manage Syllabi</div>
                                    </div>
                                </div>
                                <p style="font-size: 13px; color: #6b7280; margin: 0; line-height: 1.4;">
                                    Create and manage syllabus records with topics and objectives.
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            @endcan

            @if ($schemes->isEmpty())
                <div class="placeholder-message">
                    <i class="bx bx-file"></i>
                    <p>No schemes are available in your current visibility scope.</p>
                </div>
            @else
                @php
                    $statusColors = [
                        'draft'                => 'secondary',
                        'submitted'            => 'info',
                        'supervisor_reviewed'  => 'primary',
                        'under_review'         => 'warning',
                        'approved'             => 'success',
                        'revision_required'    => 'danger',
                    ];
                @endphp

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title" style="margin: 0;">{{ $schemeListTitle ?? 'Schemes' }}</div>
                    <div class="view-toggle">
                        <button class="view-toggle-btn" data-view="grid" title="Grid view"><i class="fas fa-th-large"></i></button>
                        <button class="view-toggle-btn active" data-view="list" title="List view"><i class="fas fa-list"></i></button>
                    </div>
                </div>

                {{-- GRID VIEW --}}
                <div id="schemes-grid" class="scheme-grid d-none">
                    @foreach ($schemes as $scheme)
                        @php
                            $subjectName = $scheme->klassSubject?->gradeSubject?->subject?->name
                                ?? $scheme->optionalSubject?->gradeSubject?->subject?->name
                                ?? '—';
                            $classLabel = $scheme->klassSubject?->klass?->name
                                ?? ('Optional: ' . ($scheme->optionalSubject?->gradeSubject?->subject?->name ?? '—'));
                        @endphp
                        <a href="{{ route('schemes.show', $scheme) }}" class="text-decoration-none">
                            <div class="scheme-card animate-in" style="--i: {{ $loop->index }}">
                                <div>
                                    <div class="scheme-card-subject">{{ $subjectName }}</div>
                                    <div class="scheme-card-meta mt-1">
                                        <span><i class="fas fa-chalkboard"></i> {{ $classLabel }}</span>
                                        <span><i class="fas fa-calendar"></i> Term {{ $scheme->term?->term }} {{ $scheme->term?->year }}</span>
                                        @if ($showTeacherColumn ?? false)
                                            <span><i class="fas fa-user"></i> {{ $scheme->teacher?->full_name ?? '—' }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="scheme-card-footer">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="status-badge status-{{ $scheme->status }}">
                                            <span class="status-dot dot-{{ $scheme->status }}"></span>
                                            {{ ucfirst(str_replace('_', ' ', $scheme->status)) }}
                                        </span>
                                        @if ($scheme->standard_scheme_id)
                                            <span class="badge" style="font-size: 10px; background: #f5f3ff; color: #7c3aed; border: 1px solid #e9d5ff;">Standard</span>
                                        @endif
                                    </div>
                                    <span style="font-size: 11px; color: #94a3b8;">{{ $scheme->total_weeks }} wks</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- LIST VIEW --}}
                <div id="schemes-list">
                    <div class="table-responsive">
                        <table class="table scheme-table mb-0">
                            <thead>
                                <tr>
                                    @if ($showTeacherColumn ?? false)
                                        <th>Teacher</th>
                                    @endif
                                    <th>Subject</th>
                                    <th>Class / Optional</th>
                                    <th>Term</th>
                                    <th>Weeks</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schemes as $scheme)
                                    @php
                                        $subjectName = $scheme->klassSubject?->gradeSubject?->subject?->name
                                            ?? $scheme->optionalSubject?->gradeSubject?->subject?->name
                                            ?? '—';
                                        $classLabel = $scheme->klassSubject?->klass?->name
                                            ?? ('Optional: ' . ($scheme->optionalSubject?->gradeSubject?->subject?->name ?? '—'));
                                    @endphp
                                    <tr class="row-{{ $scheme->status }}">
                                        @if ($showTeacherColumn ?? false)
                                            <td>{{ $scheme->teacher?->full_name ?? '—' }}</td>
                                        @endif
                                        <td>{{ $subjectName }}</td>
                                        <td>{{ $classLabel }}</td>
                                        <td>Term {{ $scheme->term?->term }} {{ $scheme->term?->year }}</td>
                                        <td>{{ $scheme->total_weeks }}</td>
                                        <td>
                                            <span class="status-badge status-{{ $scheme->status }}">
                                                <span class="status-dot dot-{{ $scheme->status }}"></span>
                                                {{ ucfirst(str_replace('_', ' ', $scheme->status)) }}
                                            </span>
                                            @if ($scheme->standard_scheme_id)
                                                <span class="badge" style="font-size: 10px; background: #f5f3ff; color: #7c3aed; border: 1px solid #e9d5ff;">Standard</span>
                                            @endif
                                        </td>
                                        <td>{{ $scheme->created_at->format('d M Y') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('schemes.show', $scheme) }}"
                                                   class="btn btn-sm btn-outline-primary btn-action"
                                                   title="View Scheme">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @can('delete', $scheme)
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-action btn-delete-scheme"
                                                            data-scheme-url="{{ route('schemes.destroy', $scheme) }}"
                                                            title="Delete Scheme">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($schemes->hasPages())
                    <div class="d-flex justify-content-end mt-3">
                        {{ $schemes->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Hidden delete form --}}
    <form id="delete-scheme-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection
@section('script')
    <script>
    (function () {
        'use strict';

        // View toggle (grid/list) with localStorage persistence
        var gridView = document.getElementById('schemes-grid');
        var listView = document.getElementById('schemes-list');
        var toggleBtns = document.querySelectorAll('.view-toggle-btn');
        var storageKey = 'schemes_view_mode';

        function setView(mode) {
            if (gridView && listView) {
                gridView.classList.toggle('d-none', mode !== 'grid');
                listView.classList.toggle('d-none', mode !== 'list');
            }
            toggleBtns.forEach(function (btn) {
                btn.classList.toggle('active', btn.dataset.view === mode);
            });
            try { localStorage.setItem(storageKey, mode); } catch (e) {}
        }

        toggleBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setView(this.dataset.view);
            });
        });

        // Restore saved preference (default: list)
        try {
            var saved = localStorage.getItem(storageKey);
            if (saved === 'list' || saved === 'grid') setView(saved);
        } catch (e) {
            setView('list');
        }

        // Delete confirmation
        document.querySelectorAll('.btn-delete-scheme').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var schemeUrl = this.dataset.schemeUrl;

                Swal.fire({
                    title: 'Delete this scheme?',
                    text: 'This will permanently remove the scheme and all its entries.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    var form = document.getElementById('delete-scheme-form');
                    form.action = schemeUrl;
                    form.submit();
                });
            });
        });
    })();
    </script>
@endsection
