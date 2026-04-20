@extends('layouts.master')
@section('title')
    Standard Scheme Print
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    @include('schemes.partials.document-styles')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('standard-schemes.index') }}">Standard Schemes</a>
        @endslot
        @slot('title')
            Print View
        @endslot
    @endcomponent

    @php
        $sortedEntries = $standardScheme->entries->sortBy('week_number');
        $totalObjectives = $sortedEntries->sum(fn ($entry) => $entry->objectives->count());
        $teacherCount = $standardScheme->derivedSchemes->pluck('teacher_id')->filter()->unique()->count();
        $headerMetaItems = [
            ['icon' => 'fas fa-book', 'text' => $standardScheme->subject?->name ?? '—'],
            ['icon' => 'fas fa-layer-group', 'text' => $standardScheme->grade?->name ?? '—'],
            ['icon' => 'fas fa-calendar-alt', 'text' => 'Term ' . ($standardScheme->term?->term ?? '—') . ', ' . ($standardScheme->term?->year ?? '—')],
            ['icon' => 'fas fa-user-tie', 'text' => 'Panel Lead: ' . ($standardScheme->panelLead?->full_name ?? '—')],
        ];
    @endphp

    <div class="doc-toolbar">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted" style="font-size: 13px;">
                Printable standard scheme view — use Ctrl+P to print
            </span>
        </div>
        <div class="doc-toolbar-actions d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <div class="doc-container">
        <div class="doc-header">
            @include('schemes.partials.document-letterhead', [
                'school' => $school ?? null,
                'subtitle' => 'Standard Scheme of Work',
                'title' => $standardScheme->subject?->name ?? 'Standard Scheme',
                'metaItems' => $headerMetaItems,
            ])
        </div>

        <div class="doc-body">
            <div class="doc-summary">
                <div class="doc-summary-item">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $standardScheme->status }}">
                            {{ ucfirst(str_replace('_', ' ', $standardScheme->status)) }}
                        </span>
                    </span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">Department</span>
                    <span class="value">{{ $standardScheme->department?->name ?? '—' }}</span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">Total Weeks</span>
                    <span class="value">{{ $standardScheme->total_weeks }}</span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">Entries</span>
                    <span class="value">{{ $sortedEntries->count() }}</span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">Objectives</span>
                    <span class="value">{{ $totalObjectives }}</span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">Teachers</span>
                    <span class="value">{{ $teacherCount }}</span>
                </div>
            </div>

            @if (filled($standardScheme->review_comments))
                <div class="field-row">
                    <div class="field-label">Review Comments</div>
                    <div class="field-value lined-sheet">{!! $standardScheme->review_comments !!}</div>
                </div>
            @endif

            @forelse ($sortedEntries as $entry)
                <div class="week-block block-{{ $entry->status ?? 'planned' }} animate-in" style="--i: {{ $loop->index }}">
                    <div class="week-header">
                        <h4>Week {{ $entry->week_number }}</h4>
                        <div class="d-flex align-items-center gap-2">
                            <span class="entry-status-dot dot-{{ $entry->status ?? 'planned' }}"></span>
                            <span style="font-size: 13px; color: #6b7280; text-transform: capitalize;">
                                {{ str_replace('_', ' ', $entry->status ?? 'planned') }}
                            </span>
                        </div>
                    </div>
                    <div class="week-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-row">
                                    <div class="field-label">Topic</div>
                                    <div class="field-value lined-sheet lined-sheet--compact {{ $entry->topic ? '' : 'empty' }}">
                                        {{ $entry->topic ?? '—' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-row">
                                    <div class="field-label">Sub-topic</div>
                                    <div class="field-value lined-sheet lined-sheet--compact {{ $entry->sub_topic ? '' : 'empty' }}">
                                        {{ $entry->sub_topic ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-label">Learning Objectives</div>
                            <div class="field-value lined-sheet {{ $entry->learning_objectives ? '' : 'empty' }}">
                                {!! $entry->learning_objectives ?? '—' !!}
                            </div>
                        </div>

                        @if ($entry->objectives->isNotEmpty())
                            <div class="field-row">
                                <div class="field-label">Linked Syllabus Objectives</div>
                                <div class="objective-list">
                                    @foreach ($entry->objectives as $objective)
                                        <span class="objective-pill">
                                            @if (filled($objective->code))
                                                <strong>{{ $objective->code }}</strong>
                                            @endif
                                            {{ $objective->objective_text }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <p>No weekly entries found.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
