@extends('layouts.master')
@section('title')
    Teacher Dashboard &mdash; Schemes of Work
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
            Teacher Dashboard
        @endslot
    @endcomponent

    <div class="schemes-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 style="margin: 0;">My Schemes Dashboard</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">
                        Term {{ $currentTerm->term }} {{ $currentTerm->year }} &mdash; Week {{ $currentWeek }}
                    </p>
                </div>
                <div class="col-md-5 text-end d-flex justify-content-end align-items-center gap-2 flex-wrap">
                    <a href="{{ route('schemes.index') }}" class="btn-outline-white">
                        <i class="fas fa-list"></i> View All Schemes
                    </a>
                    <a href="{{ route('lesson-plans.create') }}" class="btn-outline-white">
                        <i class="fas fa-plus"></i> New Lesson Plan
                    </a>
                </div>
            </div>
        </div>

        <div class="form-container">

            {{-- Section 1: My Schemes --}}
            <div class="section-title">My Schemes</div>

            @if ($schemes->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>You have no schemes for this term. Schemes will appear here once your department publishes a standard scheme for your subject.</p>
                </div>
            @else
                @php
                    $statusColors = [
                        'draft'                => 'secondary',
                        'submitted'            => 'info',
                        'supervisor_reviewed'  => 'primary',
                        'under_review'         => 'warning',
                        'revision_required'    => 'danger',
                        'approved'             => 'success',
                    ];
                @endphp

                @foreach ($schemes as $scheme)
                    @php
                        $subjectName = $scheme->klassSubject?->gradeSubject?->subject?->name
                            ?? $scheme->optionalSubject?->gradeSubject?->subject?->name
                            ?? 'Scheme #' . $scheme->id;

                        $className = $scheme->klassSubject?->klass?->name ?? null;
                        $badgeColor = $statusColors[$scheme->status] ?? 'secondary';
                    @endphp

                    <div class="scheme-card card-{{ $scheme->status }} animate-in" style="--i: {{ $loop->index }}">
                        <div class="scheme-card-header">
                            <div>
                                <div class="scheme-name">
                                    <span class="status-dot dot-{{ $scheme->status }}"></span>
                                    {{ $subjectName }}
                                </div>
                                @if ($className)
                                    <div style="font-size: 12px; color: #6b7280;">{{ $className }}</div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="status-badge status-{{ $scheme->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $scheme->status)) }}
                                </span>
                                <span class="text-muted" style="font-size: 12px;">
                                    {{ $scheme->entries->count() }} weeks
                                </span>
                            </div>
                        </div>

                        <div class="scheme-card-body">
                            @if ($scheme->entries->isEmpty())
                                <div class="text-muted text-center py-3" style="font-size: 13px;">No entries</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0" style="font-size: 13px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">Week</th>
                                                <th>Topic</th>
                                                <th style="width: 110px;">Status</th>
                                                <th style="width: 90px;">Lesson Plans</th>
                                                <th style="width: 40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($scheme->entries->sortBy('week_number') as $entry)
                                                <tr class="{{ $entry->week_number === $currentWeek ? 'current-week-row' : '' }}">
                                                    <td>
                                                        Wk {{ $entry->week_number }}
                                                        @if ($entry->week_number === $currentWeek)
                                                            <span class="badge bg-primary ms-1" style="font-size: 10px;">Now</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ Str::limit($entry->topic ?? '—', 40) }}</td>
                                                    <td>
                                                        @php
                                                            $entryStatusColors = [
                                                                'planned'     => 'secondary',
                                                                'in_progress' => 'info',
                                                                'completed'   => 'success',
                                                                'skipped'     => 'warning',
                                                            ];
                                                        @endphp
                                                        <span class="badge bg-{{ $entryStatusColors[$entry->status ?? 'planned'] ?? 'secondary' }}">
                                                            {{ ucfirst(str_replace('_', ' ', $entry->status ?? 'planned')) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $entry->lessonPlans->count() }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('lesson-plans.create', ['scheme_entry_id' => $entry->id]) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Add Lesson Plan">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="scheme-card-footer">
                            <a href="{{ route('schemes.show', $scheme) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye me-1"></i> View Full Scheme
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Section 2: Upcoming Lesson Plans --}}
            <div class="section-title">Upcoming Lesson Plans</div>

            @if ($upcomingLessonPlans->isEmpty())
                <div class="empty-state" style="padding: 24px 20px;">
                    <i class="fas fa-calendar-alt"></i>
                    <p>No upcoming lesson plans.</p>
                    <a href="{{ route('lesson-plans.create') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus me-1"></i> Create One
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Topic</th>
                                <th>Period</th>
                                <th>Week</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($upcomingLessonPlans as $plan)
                                <tr class="animate-in" style="--i: {{ $loop->index }}">
                                    <td>{{ $plan->date?->format('d M Y') }}</td>
                                    <td>{{ Str::limit($plan->topic, 40) }}</td>
                                    <td>{{ $plan->period ?? '—' }}</td>
                                    <td>
                                        @if ($plan->entry)
                                            Wk {{ $plan->entry->week_number }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('lesson-plans.show', $plan) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
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
    })();
    </script>
@endsection
