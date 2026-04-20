@extends('layouts.master')
@section('title')
    Scheme Document
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    @include('schemes.partials.document-styles')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Document View
        @endslot
    @endcomponent

    @php
        $subjectName = $scheme->klassSubject?->gradeSubject?->subject?->name
            ?? ($scheme->optionalSubject?->gradeSubject?->subject?->name ?? 'Unknown Subject');

        $gradeName = $scheme->klassSubject?->gradeSubject?->grade?->name
            ?? ($scheme->optionalSubject?->gradeSubject?->grade?->name ?? '—');

        $classLabel = $scheme->klassSubject?->klass?->name
            ?? ('Optional: ' . ($scheme->optionalSubject?->gradeSubject?->subject?->name ?? '—'));

        $statusColors = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'under_review' => 'warning',
            'approved' => 'success',
            'revision_required' => 'danger',
        ];

        $documentView = $documentView ?? 'full';
        $allEntries = $scheme->entries->sortBy('week_number')->values();
        $entriesWithLessonPlans = $allEntries->filter(fn ($entry) => $entry->lessonPlans->isNotEmpty())->values();
        $displayEntries = $documentView === 'lesson-plans' ? $entriesWithLessonPlans : $allEntries;
        $lessonPlanCount = $allEntries->sum(fn ($entry) => $entry->lessonPlans->count());
        $entryCountLabel = $documentView === 'lesson-plans' ? 'Weeks Shown' : 'Entries';
        $documentSubtitle = $documentView === 'lesson-plans' ? 'Scheme Lesson Plans' : 'Scheme of Work';
        $documentHint = $documentView === 'lesson-plans'
            ? 'Lesson-plan print mode — only weeks with lesson plans are shown.'
            : 'Printable document view — use Ctrl+P to print';
        $sendEmailErrors = $errors->getBag('sendSchemeEmail');
        $defaultRecipientEmails = collect($defaultEmailRecipients ?? [])->pluck('email')->implode(', ');
        $headerMetaItems = [
            ['icon' => 'fas fa-chalkboard', 'text' => $classLabel],
            ['icon' => 'fas fa-user', 'text' => $scheme->teacher?->full_name ?? '—'],
            ['icon' => 'fas fa-calendar-alt', 'text' => 'Term ' . ($scheme->term?->term ?? '—') . ', ' . ($scheme->term?->year ?? '—')],
            ['icon' => 'fas fa-layer-group', 'text' => $gradeName],
        ];
        $defaultEmailSubject = sprintf(
            '%s Scheme of Work - %s - Term %s, %s',
            $subjectName,
            $gradeName,
            $scheme->term?->term ?? '—',
            $scheme->term?->year ?? '—'
        );
    @endphp

    <div class="doc-toolbar">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted" style="font-size: 13px;">
                {{ $documentHint }}
            </span>
        </div>
        <div class="doc-toolbar-actions d-flex align-items-center gap-2">
            <div class="btn-group" role="group" aria-label="Document display mode">
                <a href="{{ route('schemes.document', $scheme) }}"
                    class="btn btn-sm {{ $documentView === 'full' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    Full Scheme
                </a>
                <a href="{{ route('schemes.document', ['scheme' => $scheme, 'view' => 'lesson-plans']) }}"
                    class="btn btn-sm {{ $documentView === 'lesson-plans' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    Lesson Plans Only
                </a>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sendSchemeEmailModal">
                <i class="fas fa-envelope me-1"></i> Send Email
            </button>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <div class="doc-container">
        <div class="doc-header">
            @include('schemes.partials.document-letterhead', [
                'school' => $school ?? null,
                'subtitle' => $documentSubtitle,
                'title' => $subjectName,
                'metaItems' => $headerMetaItems,
            ])
        </div>

        <div class="doc-body">
            <div class="doc-summary">
                <div class="doc-summary-item">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $scheme->status }}">
                            {{ ucfirst(str_replace('_', ' ', $scheme->status)) }}
                        </span>
                    </span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">Total Weeks</span>
                    <span class="value">{{ $scheme->total_weeks }}</span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">{{ $entryCountLabel }}</span>
                    <span class="value">{{ $displayEntries->count() }}</span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">Lesson Plans</span>
                    <span class="value">{{ $lessonPlanCount }}</span>
                </div>
                <div class="doc-summary-item">
                    <span class="label">Completed</span>
                    <span class="value">{{ $displayEntries->where('status', 'completed')->count() }} / {{ $displayEntries->count() }}</span>
                </div>
            </div>

            @if ($documentView === 'lesson-plans')
                <div class="help-text" style="margin-bottom: 24px;">
                    <div class="help-title">Lesson Plans Only</div>
                    <div class="help-content">
                        This mode prints only the scheme weeks that already have lesson plans attached.
                    </div>
                </div>
            @endif

            @forelse ($displayEntries as $entry)
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

                        @if ($entry->lessonPlans->count() > 0)
                            @foreach ($entry->lessonPlans as $plan)
                                <div class="lesson-plan-card">
                                    <h5>
                                        <i class="fas fa-clipboard-list"></i>
                                        Lesson Plan: {{ $plan->topic }}
                                        @if ($plan->status === 'taught')
                                            <span class="badge bg-success" style="font-size: 11px;">Taught</span>
                                        @else
                                            <span class="badge bg-info" style="font-size: 11px;">Planned</span>
                                        @endif
                                    </h5>
                                    <div class="lp-meta">
                                        <span><i class="fas fa-calendar me-1"></i> {{ $plan->date?->format('d M Y') ?? '—' }}</span>
                                        @if ($plan->period)
                                            <span><i class="fas fa-clock me-1"></i> {{ $plan->period }}</span>
                                        @endif
                                        @if ($plan->status === 'taught' && $plan->taught_at)
                                            <span><i class="fas fa-check-circle me-1"></i> Taught {{ $plan->taught_at->format('d M Y') }}</span>
                                        @endif
                                    </div>

                                    <div class="lp-grid">
                                        @if ($plan->sub_topic)
                                            <div>
                                                <div class="field-label">Sub-topic</div>
                                                <div class="field-value lined-sheet lined-sheet--compact">{{ $plan->sub_topic }}</div>
                                            </div>
                                        @endif
                                        @if ($plan->learning_objectives)
                                            <div class="full-width">
                                                <div class="field-label">Learning Objectives</div>
                                                <div class="field-value lined-sheet">{!! $plan->learning_objectives !!}</div>
                                            </div>
                                        @endif
                                        @if ($plan->content)
                                            <div class="full-width">
                                                <div class="field-label">Content</div>
                                                <div class="field-value lined-sheet">{!! $plan->content !!}</div>
                                            </div>
                                        @endif
                                        @if ($plan->activities)
                                            <div class="full-width">
                                                <div class="field-label">Activities</div>
                                                <div class="field-value lined-sheet">{!! $plan->activities !!}</div>
                                            </div>
                                        @endif
                                        @if ($plan->teaching_learning_aids)
                                            <div>
                                                <div class="field-label">Teaching/Learning Aids</div>
                                                <div class="field-value lined-sheet">{!! $plan->teaching_learning_aids !!}</div>
                                            </div>
                                        @endif
                                        @if ($plan->lesson_evaluation)
                                            <div>
                                                <div class="field-label">Lesson Evaluation</div>
                                                <div class="field-value lined-sheet">{!! $plan->lesson_evaluation !!}</div>
                                            </div>
                                        @endif
                                        @if ($plan->resources)
                                            <div>
                                                <div class="field-label">Resources</div>
                                                <div class="field-value lined-sheet">{!! $plan->resources !!}</div>
                                            </div>
                                        @endif
                                        @if ($plan->homework)
                                            <div>
                                                <div class="field-label">Homework</div>
                                                <div class="field-value lined-sheet">{!! $plan->homework !!}</div>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($plan->status === 'taught' && $plan->reflection_notes)
                                        <div class="reflection-box">
                                            <div class="reflection-title"><i class="fas fa-journal-whills me-1"></i> Reflection Notes</div>
                                            <div class="reflection-content lined-sheet">{!! $plan->reflection_notes !!}</div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <p>{{ $documentView === 'lesson-plans' ? 'No lesson plans found for this scheme yet.' : 'No weekly entries found.' }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="sendSchemeEmailModal" tabindex="-1" aria-labelledby="sendSchemeEmailModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('schemes.email-document', $scheme) }}" method="POST" id="sendSchemeEmailForm">
                    @csrf
                    <input type="hidden" name="document_view" value="{{ $documentView }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendSchemeEmailModalLabel">Send Scheme Document by Email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($sendEmailErrors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($sendEmailErrors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="scheme-email-recipients" class="form-label">Recipients</label>
                            <textarea
                                id="scheme-email-recipients"
                                name="recipients"
                                rows="3"
                                class="form-control @if ($sendEmailErrors->has('recipients')) is-invalid @endif"
                                placeholder="Enter one or more email addresses separated by commas or new lines"
                                required
                            >{{ old('recipients', $defaultRecipientEmails) }}</textarea>
                            <div class="form-text">
                                The field is prefilled with the teacher's supervisor and the school deputy head. You can add or remove any addresses.
                            </div>
                            @if (!empty($defaultEmailRecipients))
                                <div class="mt-2 small text-muted">
                                    @foreach ($defaultEmailRecipients as $recipient)
                                        <div>{{ $recipient['label'] }} ({{ $recipient['email'] }})</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="scheme-email-subject" class="form-label">Email Subject</label>
                            <input
                                id="scheme-email-subject"
                                type="text"
                                name="subject"
                                value="{{ old('subject', $defaultEmailSubject) }}"
                                class="form-control @if ($sendEmailErrors->has('subject')) is-invalid @endif"
                                required
                            >
                        </div>

                        <div class="mb-0">
                            <label for="scheme-email-message" class="form-label">Message</label>
                            <textarea
                                id="scheme-email-message"
                                name="message"
                                rows="5"
                                class="form-control @if ($sendEmailErrors->has('message')) is-invalid @endif"
                                placeholder="Optional note to include above the scheme document"
                            >{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading" id="sendSchemeEmailSubmitBtn">
                            <span class="btn-text">
                                <i class="fas fa-paper-plane me-1"></i> Send Email
                            </span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Sending...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sendSchemeEmailForm = document.getElementById('sendSchemeEmailForm');
            if (sendSchemeEmailForm) {
                sendSchemeEmailForm.addEventListener('submit', function(event) {
                    const submitBtn = sendSchemeEmailForm.querySelector('button[type="submit"].btn-loading');

                    if (!sendSchemeEmailForm.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        sendSchemeEmailForm.classList.add('was-validated');
                        return;
                    }

                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }

            @if ($sendEmailErrors->any())
                const sendSchemeEmailModal = document.getElementById('sendSchemeEmailModal');
                if (sendSchemeEmailModal) {
                    const modal = new bootstrap.Modal(sendSchemeEmailModal);
                    modal.show();
                }
            @endif
        });
    </script>
@endsection
