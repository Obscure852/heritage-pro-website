@extends('layouts.master')
@section('title')
    Standard Schemes
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        .stat-item { padding: 10px 0; }
        .stat-item h4 { font-size: 1.5rem; font-weight: 700; }
        .stat-item small { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Standard Schemes
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
        $totalSchemes = $schemes->count();
        $approvedCount = $schemes->where('status', 'approved')->count();
        $publishedCount = $schemes->filter(fn($s) => $s->published_at)->count();
        $draftCount = $schemes->where('status', 'draft')->count();
    @endphp

    <div class="schemes-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin: 0;">Standard Schemes of Work</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">Subject-level schemes distributed to all teachers</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalSchemes }}</h4>
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
                                <h4 class="mb-0 fw-bold text-white">{{ $publishedCount }}</h4>
                                <small class="opacity-75">Published</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $draftCount }}</h4>
                                <small class="opacity-75">Draft</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container">
            <div class="help-text">
                <div class="help-title">Standard Schemes</div>
                <div class="help-content">
                    Standard schemes define the term-level curriculum plan for a subject and grade. Once approved, they can be published as a teacher reference and distributed to create read-only individual schemes.
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
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
                @can('manage-standard-schemes')
                    <a href="{{ route('standard-schemes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Standard Scheme
                    </a>
                @endcan
            </div>

            @if ($schemes->isEmpty())
                <div class="placeholder-message">
                    <i class="bx bx-file"></i>
                    <p>No standard schemes found for this grade.</p>
                </div>
            @else
                <div class="section-title">Standard Schemes — {{ $grades->firstWhere('id', (int) $selectedGradeId)?->name ?? '' }}</div>
                <div class="table-responsive">
                    <table class="table scheme-table mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Term</th>
                                <th>Weeks</th>
                                <th>Status</th>
                                <th>Published</th>
                                <th>Teachers</th>
                                <th>Panel Lead</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schemes as $scheme)
                                @php
                                    $statusColors = [
                                        'draft'             => 'secondary',
                                        'submitted'         => 'info',
                                        'under_review'      => 'warning',
                                        'approved'          => 'success',
                                        'revision_required' => 'danger',
                                    ];
                                    $badgeColor = $statusColors[$scheme->status] ?? 'secondary';
                                @endphp
                                <tr class="row-{{ $scheme->status }} animate-in" style="--i: {{ $loop->index }}">
                                    <td>{{ $scheme->subject?->name ?? '—' }}</td>
                                    <td>{{ $scheme->grade?->name ?? '—' }}</td>
                                    <td>Term {{ $scheme->term?->term }} {{ $scheme->term?->year }}</td>
                                    <td>{{ $scheme->total_weeks }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $scheme->status }}">
                                            <span class="status-dot dot-{{ $scheme->status }}"></span>
                                            {{ ucfirst(str_replace('_', ' ', $scheme->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($scheme->published_at)
                                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 11px;">
                                                <i class="fas fa-check-circle me-1"></i>Published
                                            </span>
                                        @else
                                            <span class="text-muted" style="font-size: 12px;">Not published</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 11px;">
                                            {{ $scheme->derived_schemes_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>{{ $scheme->panelLead?->full_name ?? $scheme->creator?->full_name ?? '—' }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('standard-schemes.show', $scheme) }}"
                                               class="btn btn-sm btn-outline-primary btn-action"
                                               title="View Standard Scheme">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @can('delete', $scheme)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-action btn-delete-scheme"
                                                        data-scheme-url="{{ route('standard-schemes.destroy', $scheme) }}"
                                                        title="Delete Standard Scheme">
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
            @endif
        </div>
    </div>

    <form id="delete-scheme-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection
@section('script')
    <script>
    (function () {
        'use strict';
        document.querySelectorAll('.btn-delete-scheme').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var schemeUrl = this.dataset.schemeUrl;
                Swal.fire({
                    title: 'Delete this standard scheme?',
                    text: 'This will permanently remove the standard scheme and all its entries.',
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
        // Grade filter — navigate with query param (sticky via session)
        var gradeFilter = document.getElementById('gradeFilter');
        if (gradeFilter) {
            gradeFilter.addEventListener('change', function () {
                window.location.href = '{{ route("standard-schemes.index") }}?grade_id=' + this.value;
            });
        }
    })();
    </script>
@endsection
