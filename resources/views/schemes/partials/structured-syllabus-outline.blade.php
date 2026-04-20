@php
    $title = $structure['title'] ?? 'Syllabus';
    $sections = $structure['sections'] ?? [];
@endphp

<div class="syllabus-outline" id="syllabus-planner-outline">
    <div class="syllabus-outline-header">
        <div>
            <div class="syllabus-outline-title">{{ $title }}</div>
            <div class="syllabus-outline-meta">
                {{ count($sections) }} section{{ count($sections) === 1 ? '' : 's' }}
            </div>
        </div>

        @if (!empty($syllabusDocument))
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                data-bs-target="#syllabusPdfModal">
                <i class="fas fa-file-pdf me-1 text-danger"></i> Open PDF
            </button>
        @endif
    </div>

    @forelse ($sections as $section)
        @php $units = $section['units'] ?? []; @endphp
        <details class="syllabus-section planner-section" @if ($loop->first) open @endif>
            <summary>
                <span>{{ $section['form'] ?? 'Section ' . $loop->iteration }}</span>
                <span class="syllabus-count">
                    {{ count($units) }} unit{{ count($units) === 1 ? '' : 's' }}
                </span>
            </summary>

            <div class="syllabus-section-body">
                @forelse ($units as $unit)
                    @php $topics = $unit['topics'] ?? []; @endphp
                    <details class="syllabus-unit planner-unit" @if ($loop->first && $loop->parent->first) open @endif>
                        <summary>
                            <span class="syllabus-unit-heading">
                                @if (!empty($unit['id']))
                                    <span class="syllabus-unit-code">{{ $unit['id'] }}</span>
                                @endif
                                <span>{{ $unit['title'] ?? 'Unit ' . $loop->iteration }}</span>
                            </span>
                            <span class="syllabus-count">
                                {{ count($topics) }} topic{{ count($topics) === 1 ? '' : 's' }}
                            </span>
                        </summary>

                        <div class="syllabus-topics">
                            @forelse ($topics as $topic)
                                @include('schemes.partials.planner-topic-node', [
                                    'topic' => $topic,
                                    'section' => $section,
                                    'unit' => $unit,
                                    'plannerReadonly' => $plannerReadonly ?? false,
                                    'depth' => 0,
                                ])
                            @empty
                                <div class="syllabus-empty-state">No topics defined for this unit.</div>
                            @endforelse
                        </div>
                    </details>
                @empty
                    <div class="syllabus-empty-state">No units defined for this section.</div>
                @endforelse
            </div>
        </details>
    @empty
        <div class="syllabus-empty-state">No syllabus sections are available yet.</div>
    @endforelse
</div>
