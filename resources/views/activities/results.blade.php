@extends('layouts.master')

@section('title')
    Event Results
@endsection

@section('css')
    @include('activities.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            {{ $event->title }} Results
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Event Results</h1>
                <p class="page-subtitle">Review the event, record individual or house outcomes, and keep awards and points visible against the event record.</p>
            </div>
            <div class="activities-actions">
                <a href="{{ route('activities.events.index', $activity) }}" class="btn btn-light border">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>

        @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'events'])

        <div class="info-note">
            <div class="help-title">Event Context</div>
            <div class="help-content">
                {{ $event->title }} |
                {{ \App\Models\Activities\ActivityEvent::eventTypes()[$event->event_type] ?? ucfirst($event->event_type) }} |
                {{ optional($event->start_datetime)->format('d M Y, H:i') }}
                @if ($event->end_datetime)
                    to {{ $event->end_datetime->format('d M Y, H:i') }}
                @endif
                | {{ $event->location ?: ($activity->default_location ?: 'No location set') }}
            </div>
        </div>

        @if ($event->status !== \App\Models\Activities\ActivityEvent::STATUS_COMPLETED)
            <div class="help-text">
                <div class="help-title">Results Locked Until Completion</div>
                <div class="help-content">
                    Mark the event as completed on the Events page before saving results here.
                </div>
            </div>
        @endif

        <div class="roster-summary-grid">
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Event Status</div>
                    <div class="detail-value">{{ \App\Models\Activities\ActivityEvent::statuses()[$event->status] ?? ucfirst($event->status) }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Result Rows</div>
                    <div class="roster-summary-value">{{ $resultsSummary['total_results'] }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Awards</div>
                    <div class="roster-summary-value">{{ $resultsSummary['award_count'] }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Points Total</div>
                    <div class="roster-summary-value">{{ $resultsSummary['points_total'] }}</div>
                </div>
            </div>
        </div>

        <div class="management-grid">
            <div class="section-stack">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Recorded Results</h5>
                                <p class="management-subtitle">Awards, placements, and points remain visible here as the event detail output.</p>
                            </div>
                        </div>

                        <div class="section-stack compact-stack">
                            <div>
                                <div class="detail-label mb-2">Student Results</div>
                                @if ($groupedResults[\App\Models\Activities\ActivityResult::PARTICIPANT_STUDENT]->isNotEmpty())
                                    <div class="management-list">
                                        @foreach ($groupedResults[\App\Models\Activities\ActivityResult::PARTICIPANT_STUDENT] as $result)
                                            <div class="management-item">
                                                <div class="management-item-title">{{ $result->participant_name }}</div>
                                                <div class="management-item-meta">
                                                    @if ($result->result_label)
                                                        <span class="summary-chip pill-muted">{{ $result->result_label }}</span>
                                                    @endif
                                                    @if (!is_null($result->placement))
                                                        <span class="summary-chip pill-primary">Place {{ $result->placement }}</span>
                                                    @endif
                                                    @if (!is_null($result->points))
                                                        <span class="summary-chip pill-primary">{{ $result->points }} pts</span>
                                                    @endif
                                                    @if ($result->award_name)
                                                        <span class="summary-chip pill-primary">{{ $result->award_name }}</span>
                                                    @endif
                                                    @if (!is_null($result->score_value))
                                                        <span class="summary-chip pill-muted">Score {{ number_format((float) $result->score_value, 2) }}</span>
                                                    @endif
                                                </div>
                                                @if ($result->notes)
                                                    <div class="management-item-notes">{{ $result->notes }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="summary-empty mb-0">No student results have been recorded yet.</p>
                                @endif
                            </div>

                            @if ($event->house_linked)
                                <div>
                                    <div class="detail-label mb-2">House Results</div>
                                    @if ($groupedResults[\App\Models\Activities\ActivityResult::PARTICIPANT_HOUSE]->isNotEmpty())
                                        <div class="management-list">
                                            @foreach ($groupedResults[\App\Models\Activities\ActivityResult::PARTICIPANT_HOUSE] as $result)
                                                <div class="management-item">
                                                    <div class="management-item-title">{{ $result->participant_name }}</div>
                                                    <div class="management-item-meta">
                                                        @if ($result->result_label)
                                                            <span class="summary-chip pill-muted">{{ $result->result_label }}</span>
                                                        @endif
                                                        @if (!is_null($result->placement))
                                                            <span class="summary-chip pill-primary">Place {{ $result->placement }}</span>
                                                        @endif
                                                        @if (!is_null($result->points))
                                                            <span class="summary-chip pill-primary">{{ $result->points }} pts</span>
                                                        @endif
                                                        @if ($result->award_name)
                                                            <span class="summary-chip pill-primary">{{ $result->award_name }}</span>
                                                        @endif
                                                        @if (!is_null($result->score_value))
                                                            <span class="summary-chip pill-muted">Score {{ number_format((float) $result->score_value, 2) }}</span>
                                                        @endif
                                                    </div>
                                                    @if ($result->notes)
                                                        <div class="management-item-notes">{{ $result->notes }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="summary-empty mb-0">No house results have been recorded yet.</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($canManageResults)
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="summary-card-title">Student Result Register</h5>
                            <p class="management-subtitle">Check the rows you want to keep. Unchecked rows are removed from this event's student result record.</p>

                            @if ($studentParticipants->isNotEmpty())
                                <form action="{{ route('activities.results.update', [$activity, $event]) }}"
                                    method="POST"
                                    class="needs-validation"
                                    novalidate
                                    data-activity-form>
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="scope" value="{{ \App\Models\Activities\ActivityResult::PARTICIPANT_STUDENT }}">

                                    <div class="result-register-list">
                                        @foreach ($studentParticipants as $enrollment)
                                            @php
                                                $existing = $studentResultMap->get($enrollment->student_id);
                                                $selectedState = old('scope') === \App\Models\Activities\ActivityResult::PARTICIPANT_STUDENT
                                                    ? old('results.' . $enrollment->student_id . '.selected')
                                                    : !is_null($existing);
                                            @endphp
                                            <div class="management-item result-register-item">
                                                <label class="candidate-checkbox-item">
                                                    <div class="candidate-checkbox-shell">
                                                        <input type="checkbox" name="results[{{ $enrollment->student_id }}][selected]" value="1" {{ $selectedState ? 'checked' : '' }}>
                                                    </div>
                                                    <div class="candidate-preview-content">
                                                        <div class="management-item-title">{{ $enrollment->student?->full_name ?: 'Unknown student' }}</div>
                                                        <div class="candidate-preview-pill-row mt-2">
                                                            @if ($enrollment->gradeSnapshot?->name)
                                                                <span class="summary-chip pill-muted">{{ $enrollment->gradeSnapshot->name }}</span>
                                                            @endif
                                                            @if ($enrollment->klassSnapshot?->name)
                                                                <span class="summary-chip pill-muted">{{ $enrollment->klassSnapshot->name }}</span>
                                                            @endif
                                                            @if ($enrollment->houseSnapshot?->name)
                                                                <span class="summary-chip pill-muted">{{ $enrollment->houseSnapshot->name }}</span>
                                                            @endif
                                                        </div>

                                                        <div class="participant-result-grid mt-3">
                                                            <input type="text" class="form-control" name="results[{{ $enrollment->student_id }}][result_label]" value="{{ old('results.' . $enrollment->student_id . '.result_label', $existing?->result_label) }}" placeholder="Result label, for example Winner">
                                                            <input type="number" class="form-control" name="results[{{ $enrollment->student_id }}][placement]" value="{{ old('results.' . $enrollment->student_id . '.placement', $existing?->placement) }}" placeholder="Placement">
                                                            <input type="number" class="form-control" name="results[{{ $enrollment->student_id }}][points]" value="{{ old('results.' . $enrollment->student_id . '.points', $existing?->points) }}" placeholder="Points">
                                                            <input type="text" class="form-control" name="results[{{ $enrollment->student_id }}][award_name]" value="{{ old('results.' . $enrollment->student_id . '.award_name', $existing?->award_name) }}" placeholder="Award name">
                                                            <input type="number" step="0.01" class="form-control" name="results[{{ $enrollment->student_id }}][score_value]" value="{{ old('results.' . $enrollment->student_id . '.score_value', $existing?->score_value) }}" placeholder="Score">
                                                            <input type="text" class="form-control result-notes-input" name="results[{{ $enrollment->student_id }}][notes]" value="{{ old('results.' . $enrollment->student_id . '.notes', $existing?->notes) }}" placeholder="Optional notes about this outcome">
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary btn-loading" {{ $event->status !== \App\Models\Activities\ActivityEvent::STATUS_COMPLETED ? 'disabled' : '' }}>
                                            <span class="btn-text"><i class="fas fa-save"></i> Save Student Results</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            @else
                                <p class="summary-empty mb-0">No eligible students were attached to the activity on this event date.</p>
                            @endif
                        </div>
                    </div>

                    @if ($event->house_linked)
                        <div class="card-shell">
                            <div class="card-body p-4">
                                <h5 class="summary-card-title">House Result Register</h5>
                                <p class="management-subtitle">House rows are stored as result references only and do not change house membership.</p>

                                <form action="{{ route('activities.results.update', [$activity, $event]) }}"
                                    method="POST"
                                    class="needs-validation"
                                    novalidate
                                    data-activity-form>
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="scope" value="{{ \App\Models\Activities\ActivityResult::PARTICIPANT_HOUSE }}">

                                    <div class="result-register-list">
                                        @foreach ($houseParticipants as $house)
                                            @php
                                                $existing = $houseResultMap->get($house->id);
                                                $selectedState = old('scope') === \App\Models\Activities\ActivityResult::PARTICIPANT_HOUSE
                                                    ? old('results.' . $house->id . '.selected')
                                                    : !is_null($existing);
                                            @endphp
                                            <div class="management-item result-register-item">
                                                <label class="candidate-checkbox-item">
                                                    <div class="candidate-checkbox-shell">
                                                        <input type="checkbox" name="results[{{ $house->id }}][selected]" value="1" {{ $selectedState ? 'checked' : '' }}>
                                                    </div>
                                                    <div class="candidate-preview-content">
                                                        <div class="management-item-title">{{ $house->name }}</div>
                                                        <div class="participant-result-grid mt-3">
                                                            <input type="text" class="form-control" name="results[{{ $house->id }}][result_label]" value="{{ old('results.' . $house->id . '.result_label', $existing?->result_label) }}" placeholder="Result label, for example Champions">
                                                            <input type="number" class="form-control" name="results[{{ $house->id }}][placement]" value="{{ old('results.' . $house->id . '.placement', $existing?->placement) }}" placeholder="Placement">
                                                            <input type="number" class="form-control" name="results[{{ $house->id }}][points]" value="{{ old('results.' . $house->id . '.points', $existing?->points) }}" placeholder="Points">
                                                            <input type="text" class="form-control" name="results[{{ $house->id }}][award_name]" value="{{ old('results.' . $house->id . '.award_name', $existing?->award_name) }}" placeholder="Award name">
                                                            <input type="number" step="0.01" class="form-control" name="results[{{ $house->id }}][score_value]" value="{{ old('results.' . $house->id . '.score_value', $existing?->score_value) }}" placeholder="Score">
                                                            <input type="text" class="form-control result-notes-input" name="results[{{ $house->id }}][notes]" value="{{ old('results.' . $house->id . '.notes', $existing?->notes) }}" placeholder="Optional notes about the house outcome">
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary btn-loading" {{ $event->status !== \App\Models\Activities\ActivityEvent::STATUS_COMPLETED ? 'disabled' : '' }}>
                                            <span class="btn-text"><i class="fas fa-save"></i> Save House Results</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="section-stack">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <h5 class="summary-card-title">Event Output Summary</h5>
                        <div class="section-stack compact-stack mt-3">
                            <div class="detail-card">
                                <div class="detail-label">Student Results</div>
                                <div class="detail-value">{{ $resultsSummary['student_results'] }}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">House Results</div>
                                <div class="detail-value">{{ $resultsSummary['house_results'] }}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Placed Outcomes</div>
                                <div class="detail-value">{{ $resultsSummary['placed_count'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-shell">
                    <div class="card-body p-4">
                        <h5 class="summary-card-title">House Integrity Rule</h5>
                        <div class="help-text mb-0">
                            <div class="help-content">
                                House results reference the selected house only for scoring and award outputs. Student membership, term house allocations, and any house ownership data remain unchanged by this page.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
@endsection
