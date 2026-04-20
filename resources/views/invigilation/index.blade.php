@extends('layouts.master')

@section('title')
    Invigilation Roster
@endsection

@section('css')
    @include('invigilation.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Invigilation Roster
        @endslot
        @slot('title')
            Invigilation Roster Manager
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    <div class="invigilation-filter-row">
        <select name="term" id="termId" class="form-select term-select">
            @foreach ($terms as $term)
                <option value="{{ $term->id }}" {{ (int) $term->id === (int) ($selectedTerm->id ?? 0) ? 'selected' : '' }}>
                    {{ 'Term ' . $term->term . ', ' . $term->year }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="invigilation-container">
        <div class="invigilation-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-1 text-white">Invigilation Roster Manager</h3>
                    <p class="mb-0 opacity-75">
                        Build exam series, assign rooms, generate invigilator duties, and publish printable rosters for the selected term.
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['series'] }}</h4>
                                <small class="opacity-75">Series</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['sessions'] }}</h4>
                                <small class="opacity-75">Sessions</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['assignments'] }}</h4>
                                <small class="opacity-75">Assignments</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="invigilation-body">
            <div class="help-text">
                <div class="help-title">Module Overview</div>
                <div class="help-content">
                    Create one invigilation series per mock, final, or custom exam period. Each series manages subject sessions, room coverage,
                    assignment generation, conflict review, and publishing.
                </div>
            </div>

            @include('invigilation.partials.module-nav', ['current' => 'manager'])

            <div class="section-toolbar">
                <div>
                    <h5 class="invigilation-section-title">Series List</h5>
                    <p class="invigilation-section-subtitle">Open a series to manage sessions, rooms, assignments, and reports.</p>
                </div>
                <div class="module-header-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSeriesModal">
                        <i class="fas fa-plus me-1"></i> New Series
                    </button>
                </div>
            </div>

            <div class="card-shell invigilation-table-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Series</th>
                                    <th>Coverage</th>
                                    <th>Published</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($series as $item)
                                    @php
                                        $roomCount = $item->sessions->flatMap->rooms->count();
                                        $requiredSlots = $item->sessions->flatMap->rooms->sum('required_invigilators');
                                        $assignedSlots = $item->sessions->flatMap->rooms->sum(fn ($room) => $room->assignments->count());
                                        $coverage = $requiredSlots > 0 ? round(($assignedSlots / $requiredSlots) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="series-table-name">
                                                <strong>{{ $item->name }}</strong>
                                                <div class="invigilation-meta-pills">
                                                    <span class="summary-chip status-{{ $item->status }}">{{ \App\Models\Invigilation\InvigilationSeries::statuses()[$item->status] ?? ucfirst($item->status) }}</span>
                                                    <span class="summary-chip pill-muted">{{ \App\Models\Invigilation\InvigilationSeries::types()[$item->type] ?? ucfirst($item->type) }}</span>
                                                    <span class="summary-chip pill-muted">{{ $item->term?->term ? 'Term ' . $item->term->term . ', ' . $item->term->year : 'No term assigned' }}</span>
                                                </div>
                                                <div class="series-table-meta">
                                                    {{ $item->sessions->count() }} session(s), {{ $roomCount }} room(s),
                                                    default {{ $item->default_required_invigilators }} invigilator(s) / room.
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $assignedSlots }}/{{ $requiredSlots }}</strong>
                                            <div class="series-table-meta">{{ $coverage }}% covered</div>
                                        </td>
                                        <td>
                                            @if ($item->published_at)
                                                <strong>{{ $item->published_at->format('d M Y H:i') }}</strong>
                                                <div class="series-table-meta">By {{ $item->publisher?->full_name ?? 'System' }}</div>
                                            @else
                                                <span class="text-muted">Not published</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="series-actions justify-content-end">
                                                <a href="{{ route('invigilation.show', $item) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    {{ $item->isDraft() ? 'Open Manager' : 'View Roster' }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="invigilation-empty-cell">
                                            @include('invigilation.partials.empty-state', [
                                                'icon' => 'fas fa-clipboard-list',
                                                'title' => 'No invigilation series exist for the selected term.',
                                                'copy' => 'Create a new series to start building sessions, room coverage, and invigilator assignments for this exam cycle.',
                                            ])
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade invigilation-modal" id="createSeriesModal" tabindex="-1" aria-labelledby="createSeriesModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="createSeriesModalLabel">New Series</h5>
                        <p class="mb-0 opacity-75">Start a new mock, final, or custom invigilation cycle.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('invigilation.store') }}" method="POST" class="form-body" id="create-series-form">
                    @csrf
                    <div class="modal-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label required-label" for="name">Series Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Mid-Year Mock Series">
                            </div>
                            <div class="form-group">
                                <label class="form-label required-label" for="type">Series Type</label>
                                <select class="form-select" id="type" name="type">
                                    @foreach ($seriesTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $createDefaults['default_type']) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-label" for="default_required_invigilators">Default Invigilators / Room</label>
                                <input type="number" class="form-control" id="default_required_invigilators" name="default_required_invigilators" min="1" max="10" value="{{ old('default_required_invigilators', $createDefaults['default_required_invigilators']) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label required-label" for="term_id">Term</label>
                                <select class="form-select" id="term_id" name="term_id">
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->id }}" {{ (int) old('term_id', $selectedTerm->id ?? 0) === (int) $term->id ? 'selected' : '' }}>
                                            {{ 'Term ' . $term->term . ', ' . $term->year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-label" for="eligibility_policy">Eligibility Policy</label>
                                <select class="form-select" id="eligibility_policy" name="eligibility_policy">
                                    @foreach ($eligibilityPolicies as $key => $label)
                                        <option value="{{ $key }}" {{ old('eligibility_policy', $createDefaults['default_eligibility_policy']) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-label" for="timetable_conflict_policy">Timetable Conflict Policy</label>
                                <select class="form-select" id="timetable_conflict_policy" name="timetable_conflict_policy">
                                    @foreach ($timetablePolicies as $key => $label)
                                        <option value="{{ $key }}" {{ old('timetable_conflict_policy', $createDefaults['default_timetable_conflict_policy']) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group grid-span-full">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Special instructions for this exam series">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <input type="hidden" name="balancing_policy" value="balanced">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save me-1"></i> Create Series</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('invigilation.partials.form-loading-script')
    <script>
        $(document).ready(function() {
            $('#termId').on('change', function() {
                $.ajax({
                    url: "{{ route('students.term-session') }}",
                    method: 'POST',
                    data: {
                        term_id: $(this).val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        window.location = "{{ route('invigilation.index') }}";
                    }
                });
            });
        });

        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                const createSeriesModal = document.getElementById('createSeriesModal');

                if (createSeriesModal && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(createSeriesModal).show();
                }
            });
        @endif
    </script>
@endsection
