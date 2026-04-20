@php
    $depth = $depth ?? 0;
    $path = collect($topic['path'] ?? [trim((string) ($topic['title'] ?? ''))])
        ->map(fn ($segment) => trim((string) $segment))
        ->filter()
        ->values();
    $pathLabel = trim((string) ($topic['path_label'] ?? \App\Helpers\SyllabusStructureHelper::pathLabel($path->all())));
    $objectiveGroups = collect($topic['objective_groups'] ?? [])
        ->map(function ($group) {
            return [
                'key' => trim((string) ($group['key'] ?? 'objectives')),
                'label' => trim((string) ($group['label'] ?? 'Objectives')),
                'objectives' => collect($group['objectives'] ?? [])
                    ->map(function ($objective) {
                        return [
                            'text' => trim((string) ($objective['text'] ?? '')),
                            'code' => filled($objective['code'] ?? null) ? trim((string) $objective['code']) : null,
                            'cognitive_level' => filled($objective['cognitive_level'] ?? null)
                                ? trim((string) $objective['cognitive_level'])
                                : null,
                            'sequence' => is_numeric($objective['sequence'] ?? null)
                                ? (int) $objective['sequence']
                                : null,
                            'local_objective_id' => isset($objective['local_objective_id'])
                                ? (int) $objective['local_objective_id']
                                : null,
                        ];
                    })
                    ->filter(fn ($objective) => $objective['text'] !== '')
                    ->values(),
            ];
        })
        ->filter(fn ($group) => $group['objectives']->isNotEmpty())
        ->values();
    $allObjectives = $objectiveGroups
        ->flatMap(fn ($group) => $group['objectives'])
        ->values();
    $subtopics = collect($topic['subtopics'] ?? [])->filter(fn ($item) => is_array($item))->values();
    $topicPayload = [
        'section_form' => trim((string) ($topic['section_form'] ?? $section['form'] ?? '')),
        'unit_id' => trim((string) ($topic['unit_id'] ?? $unit['id'] ?? '')),
        'unit_title' => trim((string) ($topic['unit_title'] ?? $unit['title'] ?? '')),
        'topic_title' => trim((string) ($topic['title'] ?? '')),
        'sub_topic_title' => $pathLabel,
        'path' => $path->all(),
        'path_label' => $pathLabel,
        'context_key' => trim((string) ($topic['context_key'] ?? '')),
        'local_topic_id' => isset($topic['local_topic_id']) ? (int) $topic['local_topic_id'] : null,
        'objective_groups' => $objectiveGroups->map(function ($group) {
            return [
                'key' => $group['key'],
                'label' => $group['label'],
                'objectives' => $group['objectives']->values()->all(),
            ];
        })->all(),
        'all_objectives' => $allObjectives->all(),
    ];
@endphp

<article class="syllabus-topic-card syllabus-topic-planner-card planner-node-depth-{{ $depth }}">
    <script type="application/json" class="planner-topic-payload">{!! json_encode($topicPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <div class="planner-topic-card-head">
        <div>
            <div class="planner-topic-kicker">
                @if (!empty($topicPayload['unit_id']))
                    <span class="syllabus-unit-code">{{ $topicPayload['unit_id'] }}</span>
                @endif
                <span>{{ $topicPayload['unit_title'] ?: 'Unit' }}</span>
                @if ($depth > 0)
                    <span class="planner-linked-badge">Sub-topic</span>
                @endif
            </div>
            <h6 class="syllabus-topic-title">{{ $topic['title'] ?? 'Topic' }}</h6>
            @if ($pathLabel !== '' && $pathLabel !== trim((string) ($topic['title'] ?? '')))
                <div class="text-muted" style="font-size: 12px; margin-top: 4px;">{{ $pathLabel }}</div>
            @endif
            @if (filled($topic['description'] ?? null))
                <div class="text-muted" style="font-size: 12px; margin-top: 6px;">{{ $topic['description'] }}</div>
            @endif
        </div>

        <div class="planner-topic-actions">
            <button type="button" class="btn btn-sm btn-primary planner-insert-action" data-action="plan"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Plan week" @disabled($plannerReadonly ?? false)>
                <i class="fas fa-wand-magic-sparkles"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary planner-insert-action" data-action="topic"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Insert topic" @disabled($plannerReadonly ?? false)>
                <i class="fas fa-bookmark"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary planner-insert-action" data-action="sub-topic"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Insert sub-topic" @disabled($plannerReadonly ?? false)>
                <i class="fas fa-diagram-project"></i>
            </button>
            @if ($allObjectives->isNotEmpty())
                <button type="button" class="btn btn-sm btn-outline-secondary planner-insert-action" data-action="all-objectives"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Insert objectives" @disabled($plannerReadonly ?? false)>
                    <i class="fas fa-list-check"></i>
                </button>
            @endif
        </div>
    </div>

    @foreach ($objectiveGroups as $groupIndex => $group)
        <div class="syllabus-objective-group">
            <div class="planner-objective-group-head">
                <div class="syllabus-objective-label">{{ $group['label'] }}</div>
                <button type="button" class="btn btn-link btn-sm planner-group-action"
                    data-action="group-objectives" data-group-index="{{ $groupIndex }}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Insert all objectives in this group"
                    @disabled($plannerReadonly ?? false)>
                    Insert all
                </button>
            </div>
            <ul class="syllabus-objective-list planner-objective-list">
                @foreach ($group['objectives'] as $objectiveIndex => $objective)
                    <li class="planner-objective-row">
                        <button type="button" class="planner-objective-insert"
                            data-action="single-objective" data-group-index="{{ $groupIndex }}"
                            data-objective-index="{{ $objectiveIndex }}" aria-label="Insert objective"
                            data-bs-toggle="tooltip" data-bs-placement="left" title="Insert this objective"
                            @disabled($plannerReadonly ?? false)>
                            <i class="fas fa-plus"></i>
                        </button>
                        <span>
                            @if (!empty($objective['code']))
                                <strong>{{ $objective['code'] }}</strong>
                                <span class="me-1">·</span>
                            @endif
                            {{ $objective['text'] }}
                        </span>
                        @if (!empty($objective['local_objective_id']))
                            <span class="planner-linked-badge">Linked</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

    @if ($allObjectives->isEmpty())
        <div class="syllabus-empty-state planner-card-empty">
            No objectives are defined for this topic yet.
        </div>
    @endif

    @if ($subtopics->isNotEmpty())
        <details class="planner-subtopic-tree" @if ($depth < 1) open @endif>
            <summary>
                <span>Sub-topics</span>
                <span class="syllabus-count">{{ $subtopics->count() }}</span>
            </summary>
            <div class="planner-subtopic-list">
                @foreach ($subtopics as $subtopic)
                    @include('schemes.partials.planner-topic-node', [
                        'topic' => $subtopic,
                        'section' => $section,
                        'unit' => $unit,
                        'plannerReadonly' => $plannerReadonly ?? false,
                        'depth' => $depth + 1,
                    ])
                @endforeach
            </div>
        </details>
    @endif
</article>
