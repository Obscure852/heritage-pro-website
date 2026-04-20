<?php

namespace App\Http\Controllers\Schemes;

use App\Helpers\TermHelper;
use App\Helpers\SyllabusStructureHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schemes\StoreSchemeRequest;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\SchoolSetup;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Schemes\StandardScheme;
use App\Models\Schemes\Syllabus;
use App\Models\Term;
use App\Models\User;
use App\Mail\Schemes\SchemeDocumentMail;
use App\Services\Schemes\SchemeService;
use App\Services\Schemes\SyllabusSourceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class SchemeController extends Controller {
    private const DEPUTY_HEAD_POSITIONS = [
        'Deputy School Head',
        'Deputy Principal',
        'Assistant Principal',
        'Vice Principal',
    ];

    /**
     * List schemes visible to the authenticated user.
     * Teachers see their own, supervisors see their own plus supervised teachers,
     * and HOD/admin viewers see all teacher schemes.
     */
    public function index(): View {
        $this->authorize('viewAny', SchemeOfWork::class);

        $user = auth()->user();
        $visibilityMode = SchemeOfWork::visibilityModeFor($user);

        $query = SchemeOfWork::select([
            'id', 'klass_subject_id', 'optional_subject_id', 'term_id',
            'teacher_id', 'status', 'total_weeks', 'cloned_from_id', 'standard_scheme_id', 'created_at',
        ])->with([
            'term',
            'klassSubject.gradeSubject.subject',
            'klassSubject.klass',
            'optionalSubject.gradeSubject.subject',
            'teacher',
        ])->visibleTo($user)
            ->orderBy('created_at', 'desc');

        $schemes = $query->paginate(20);
        $showTeacherColumn = $visibilityMode !== 'own';
        $schemeListTitle = match ($visibilityMode) {
            'all' => 'All Schemes',
            'supervised' => 'My & Supervised Schemes',
            default => 'My Schemes',
        };
        $schemeListHelp = match ($visibilityMode) {
            'all' => 'You are viewing every teacher scheme in the module.',
            'supervised' => 'You are viewing your own schemes and those owned by teachers who report to you.',
            default => 'You are viewing only the schemes assigned to you.',
        };

        return view('schemes.index', compact(
            'schemes',
            'showTeacherColumn',
            'schemeListTitle',
            'schemeListHelp'
        ));
    }

    /**
     * Show the form for creating a new scheme.
     */
    public function create(): View {
        $this->authorize('create', SchemeOfWork::class);

        $currentTerm = TermHelper::getCurrentTerm();
        $termId      = session('selected_term_id', $currentTerm?->id);

        $klassSubjects   = KlassSubject::where('user_id', auth()->id())
            ->where('term_id', $termId)
            ->with(['gradeSubject.subject', 'klass', 'teacher'])
            ->get();

        $optionalSubjects = OptionalSubject::where('user_id', auth()->id())
            ->where('term_id', $termId)
            ->with(['gradeSubject.subject', 'teacher'])
            ->get();

        $terms = Term::orderBy('year', 'desc')->orderBy('term', 'desc')->get();

        return view('schemes.create', compact('klassSubjects', 'optionalSubjects', 'terms', 'currentTerm'));
    }

    /**
     * Store a new scheme with auto-generated weekly entries.
     */
    public function store(StoreSchemeRequest $request, SchemeService $schemeService): RedirectResponse {
        try {
            $scheme = $schemeService->createWithEntries($request->validated(), auth()->id());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('schemes.show', $scheme)
            ->with('success', "Scheme created with {$scheme->total_weeks} weekly entries.");
    }

    /**
     * Display a single scheme with all entries and objectives loaded.
     */
    public function show(SchemeOfWork $scheme, SyllabusSourceService $syllabusSourceService): View {
        $this->authorize('view', $scheme);

        $scheme->load([
            'entries.objectives.topic',
            'entries.syllabusTopic',
            'entries.lessonPlans:id,scheme_of_work_entry_id',
            'term',
            'klassSubject.gradeSubject.subject',
            'klassSubject.gradeSubject.grade',
            'klassSubject.klass',
            'optionalSubject.gradeSubject.subject',
            'optionalSubject.gradeSubject.grade',
            'teacher.reportsTo',
            'reviewer',
            'publisher',
            'supervisorReviewer',
            'workflowAudits.actor',
        ]);

        // Resolve subject_id and grade_name for objective browser pre-filtering
        $gradeSubject = $scheme->gradeSubject;
        $subjectId    = $gradeSubject?->subject_id;
        $gradeName    = $gradeSubject?->grade?->name;
        $gradeId      = $gradeSubject?->grade_id ?? $gradeSubject?->grade?->id;

        $referenceScheme = null;
        $plannerSourceType = null;
        $plannerStructure = null;

        if ($subjectId && $gradeId) {
            $referenceScheme = $this->resolvePublishedReferenceScheme((int) $subjectId, (int) $gradeId, (int) $scheme->term_id);
            $plannerStructure = $this->buildPublishedSchemePlannerStructure($referenceScheme);

            if (SyllabusStructureHelper::hasSections($plannerStructure)) {
                $plannerSourceType = 'scheme';
            } else {
                $referenceScheme = null;
                $plannerStructure = null;
            }
        }

        // Find the active syllabus for this subject/grade so the show page can
        // fall back to the syllabus when no published reference scheme exists.
        $syllabus = null;
        $syllabusStructure = null;
        $syllabusPlannerStructure = null;
        $syllabusDocument = null;
        $syllabusUnavailable = false;
        if ($subjectId && $gradeName) {
            $syllabusQuery = \App\Models\Schemes\Syllabus::query()
                ->where('subject_id', $subjectId)
                ->forGrade($gradeName)
                ->where('is_active', true);

            if (is_null($referenceScheme)) {
                $syllabus = (clone $syllabusQuery)
                    ->with(['document', 'topics.objectives'])
                    ->first();

                $syllabusStructure = $syllabusSourceService->getDisplayStructure($syllabus);
                $syllabusPlannerStructure = $this->buildPlannerStructure($syllabus, $syllabusStructure);
                $syllabusDocument = $syllabus?->document;
                $syllabusUnavailable = !is_null($syllabus)
                    && filled($syllabus->source_url)
                    && is_null($syllabusStructure)
                    && is_null($syllabusDocument);

                if (SyllabusStructureHelper::hasSections($syllabusPlannerStructure ?? $syllabusStructure)) {
                    $plannerStructure = $syllabusPlannerStructure ?? $syllabusStructure;
                    $plannerSourceType = 'syllabus';
                }
            } else {
                $syllabus = (clone $syllabusQuery)
                    ->with('document')
                    ->first();
                $syllabusDocument = $syllabus?->document;
            }
        }

        // Cloneable schemes: teacher's own past schemes + approved schemes (excluding current)
        $cloneableSchemes = SchemeOfWork::select(['id', 'klass_subject_id', 'optional_subject_id', 'term_id', 'status'])
            ->with(['term'])
            ->where('id', '!=', $scheme->id)
            ->where(function ($q) {
                $q->where('teacher_id', auth()->id())
                  ->orWhere('status', 'approved');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $terms = Term::orderBy('year', 'desc')->orderBy('term', 'desc')->get();

        $hasSupervisor = $scheme->requiresSupervisorReview();

        return view('schemes.show', compact(
            'scheme',
            'subjectId',
            'gradeName',
            'cloneableSchemes',
            'terms',
            'referenceScheme',
            'plannerSourceType',
            'plannerStructure',
            'syllabus',
            'syllabusStructure',
            'syllabusPlannerStructure',
            'syllabusDocument',
            'syllabusUnavailable',
            'hasSupervisor'
        ));
    }

    /**
     * Display a printable document view of the scheme with all entries and lesson plans.
     */
    public function document(Request $request, SchemeOfWork $scheme): View {
        $this->authorize('view', $scheme);

        $this->loadDocumentRelations($scheme);

        $defaultEmailRecipients = $this->resolveDefaultDocumentRecipients($scheme);
        $documentView = in_array($request->query('view'), ['full', 'lesson-plans'], true)
            ? $request->query('view')
            : 'full';

        return view('schemes.document', [
            'scheme' => $scheme,
            'defaultEmailRecipients' => $defaultEmailRecipients,
            'school' => SchoolSetup::current(),
            'documentView' => $documentView,
        ]);
    }

    /**
     * Email the scheme document to one or more recipients.
     */
    public function sendDocumentEmail(Request $request, SchemeOfWork $scheme): RedirectResponse
    {
        $this->authorize('view', $scheme);

        $this->loadDocumentRelations($scheme);

        $validated = validator($request->all(), [
            'recipients' => ['required', 'string', 'max:2000'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'document_view' => ['nullable', 'in:full,lesson-plans'],
        ])->validateWithBag('sendSchemeEmail');

        ['emails' => $recipientEmails, 'invalid' => $invalidEmails] = $this->normalizeRecipientEmails($validated['recipients']);

        if ($recipientEmails->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['recipients' => 'Enter at least one email address.'], 'sendSchemeEmail');
        }

        if ($invalidEmails->isNotEmpty()) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'recipients' => 'These email addresses are invalid: ' . $invalidEmails->implode(', '),
                ], 'sendSchemeEmail');
        }

        try {
            Mail::to($recipientEmails->all())->send(
                new SchemeDocumentMail(
                    $scheme,
                    $request->user(),
                    $validated['subject'],
                    $validated['message'] ?? null,
                    SchoolSetup::current()
                )
            );
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'send' => 'Unable to send the scheme document right now. Please try again.',
                ], 'sendSchemeEmail');
        }

        $redirectParameters = ['scheme' => $scheme];
        if (($validated['document_view'] ?? 'full') === 'lesson-plans') {
            $redirectParameters['view'] = 'lesson-plans';
        }

        return redirect()->route('schemes.document', $redirectParameters)
            ->with('success', 'Scheme document emailed successfully.');
    }

    /**
     * Soft-delete the scheme and all its entries.
     */
    public function destroy(SchemeOfWork $scheme): RedirectResponse {
        $this->authorize('delete', $scheme);

        $scheme->entries()->delete();
        $scheme->delete();

        return redirect()->route('schemes.index')
            ->with('success', 'Scheme deleted successfully.');
    }

    /**
     * Clone a scheme into a new term.
     */
    public function clone(Request $request, SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('clone', $scheme);

        $request->validate(['term_id' => 'required|integer|exists:terms,id']);

        try {
            $clone = $schemeService->cloneScheme($scheme, auth()->id(), (int) $request->term_id);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('schemes.show', $clone)
            ->with('success', 'Scheme cloned successfully.');
    }

    private function loadDocumentRelations(SchemeOfWork $scheme): void
    {
        $scheme->loadMissing([
            'entries.lessonPlans' => function ($query) {
                $query->orderBy('date');
            },
            'entries.objectives.topic',
            'entries.syllabusTopic',
            'term',
            'klassSubject.gradeSubject.subject',
            'klassSubject.gradeSubject.grade',
            'klassSubject.klass',
            'optionalSubject.gradeSubject.subject',
            'optionalSubject.gradeSubject.grade',
            'teacher.reportsTo',
        ]);
    }

    /**
     * @return array<int, array{email: string, label: string}>
     */
    private function resolveDefaultDocumentRecipients(SchemeOfWork $scheme): array
    {
        $recipients = collect();

        $supervisor = $scheme->teacher?->reportsTo;
        if ($supervisor && $supervisor->hasValidEmail()) {
            $recipients->push([
                'email' => $supervisor->email,
                'label' => 'Supervisor: ' . trim($supervisor->full_name),
            ]);
        }

        $deputyHead = $this->resolveDeputyHead();
        if ($deputyHead && $deputyHead->hasValidEmail()) {
            $recipients->push([
                'email' => $deputyHead->email,
                'label' => trim(($deputyHead->position ?? 'Deputy School Head') . ': ' . $deputyHead->full_name),
            ]);
        }

        return $recipients
            ->unique(fn (array $recipient): string => mb_strtolower($recipient['email']))
            ->values()
            ->all();
    }

    private function resolveDeputyHead(): ?User
    {
        return User::query()
            ->where('active', 1)
            ->where('status', 'Current')
            ->whereIn('position', self::DEPUTY_HEAD_POSITIONS)
            ->select('id', 'firstname', 'middlename', 'lastname', 'email', 'position')
            ->first();
    }

    /**
     * @return array{emails: Collection<int, string>, invalid: Collection<int, string>}
     */
    private function normalizeRecipientEmails(string $rawRecipients): array
    {
        $tokens = collect(preg_split('/[\s,;]+/', $rawRecipients) ?: [])
            ->map(static fn ($email): string => trim((string) $email))
            ->filter();

        $invalid = $tokens
            ->filter(static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) === false)
            ->values();

        $emails = $tokens
            ->filter(static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique(static fn (string $email): string => mb_strtolower($email))
            ->values();

        return [
            'emails' => $emails,
            'invalid' => $invalid,
        ];
    }

    private function buildPlannerStructure(?Syllabus $syllabus, ?array $structure): ?array
    {
        if (!$syllabus || !is_array($structure) || !SyllabusStructureHelper::hasSections($structure)) {
            return $structure;
        }

        $syllabus->loadMissing('topics.objectives');
        $localTopicsByKey = $syllabus->topics->mapWithKeys(function ($topic): array {
            return [$this->localTopicPlannerKey($topic) => $topic];
        });
        $fallbackTopicsByName = $syllabus->topics->groupBy(function ($topic): string {
            return $this->normalizePlannerText($topic->name);
        });

        // Global objective lookups as fallback when topic-level matching fails
        $globalObjectivesByCode = $syllabus->topics
            ->flatMap(fn ($topic) => $topic->objectives)
            ->filter(fn ($objective) => filled($objective->code))
            ->mapWithKeys(fn ($objective): array => [$this->normalizePlannerText($objective->code) => (int) $objective->id]);
        $globalObjectivesByText = $syllabus->topics
            ->flatMap(fn ($topic) => $topic->objectives)
            ->mapWithKeys(fn ($objective): array => [$this->normalizePlannerText($objective->objective_text) => (int) $objective->id]);

        $planner = $structure;

        foreach ($planner['sections'] as $sectionIndex => $section) {
            foreach (($section['units'] ?? []) as $unitIndex => $unit) {
                foreach (($unit['topics'] ?? []) as $topicIndex => $topic) {
                    $planner['sections'][$sectionIndex]['units'][$unitIndex]['topics'][$topicIndex] = $this->decoratePlannerTopic(
                        $topic,
                        $section,
                        $unit,
                        $localTopicsByKey,
                        $fallbackTopicsByName,
                        $globalObjectivesByCode,
                        $globalObjectivesByText
                    );
                }
            }
        }

        return $planner;
    }

    private function decoratePlannerTopic(
        array $topic,
        array $section,
        array $unit,
        Collection $localTopicsByKey,
        Collection $fallbackTopicsByName,
        Collection $globalObjectivesByCode,
        Collection $globalObjectivesByText
    ): array {
        $sectionForm = trim((string) ($section['form'] ?? ''));
        $unitId = trim((string) ($unit['id'] ?? ''));
        $unitTitle = trim((string) ($unit['title'] ?? ''));
        $path = array_values(array_filter(array_map(function ($segment): string {
            return trim((string) $segment);
        }, $topic['path'] ?? [trim((string) ($topic['title'] ?? ''))])));
        $pathLabel = SyllabusStructureHelper::pathLabel($path);
        $contextKey = SyllabusStructureHelper::buildPlannerKey($sectionForm, $unitId, $unitTitle, $path);
        $localTopic = $localTopicsByKey->get($contextKey);

        if (!$localTopic) {
            $fallbackMatches = $fallbackTopicsByName->get($this->normalizePlannerText((string) ($topic['title'] ?? '')), collect());
            if ($fallbackMatches instanceof Collection && $fallbackMatches->count() === 1) {
                $localTopic = $fallbackMatches->first();
            }
        }

        if ($localTopic) {
            $localObjectivesByCode = $localTopic->objectives
                ->filter(fn ($objective) => filled($objective->code))
                ->mapWithKeys(function ($objective): array {
                    return [$this->normalizePlannerText($objective->code) => (int) $objective->id];
                });
            $localObjectivesByText = $localTopic->objectives->mapWithKeys(function ($objective): array {
                return [$this->normalizePlannerText($objective->objective_text) => (int) $objective->id];
            });
        } else {
            // Fall back to global objective lookups when topic matching fails
            $localObjectivesByCode = $globalObjectivesByCode;
            $localObjectivesByText = $globalObjectivesByText;
        }

        $objectiveGroups = collect($topic['objective_groups'] ?? [])
            ->map(function ($group, $groupIndex) use ($localObjectivesByCode, $localObjectivesByText) {
                $objectives = collect($group['objectives'] ?? [])
                    ->map(function ($objective) use ($localObjectivesByCode, $localObjectivesByText) {
                        $code = trim((string) ($objective['code'] ?? ''));
                        $text = trim((string) ($objective['text'] ?? ''));

                        return [
                            'text' => $text,
                            'code' => $code !== '' ? $code : null,
                            'cognitive_level' => filled($objective['cognitive_level'] ?? null)
                                ? trim((string) $objective['cognitive_level'])
                                : null,
                            'sequence' => is_numeric($objective['sequence'] ?? null)
                                ? (int) $objective['sequence']
                                : null,
                            'local_objective_id' => $this->matchPlannerObjectiveId(
                                $code,
                                $text,
                                $localObjectivesByCode,
                                $localObjectivesByText
                            ),
                        ];
                    })
                    ->filter(fn (array $objective): bool => $objective['text'] !== '')
                    ->values()
                    ->all();

                return [
                    'key' => trim((string) ($group['key'] ?? ('group_' . ($groupIndex + 1)))),
                    'label' => trim((string) ($group['label'] ?? ('Objectives ' . ($groupIndex + 1)))),
                    'objectives' => $objectives,
                ];
            })
            ->filter(fn (array $group): bool => !empty($group['objectives']))
            ->values()
            ->all();

        $subtopics = collect($topic['subtopics'] ?? [])
            ->map(fn ($subtopic) => is_array($subtopic)
                ? $this->decoratePlannerTopic($subtopic, $section, $unit, $localTopicsByKey, $fallbackTopicsByName, $globalObjectivesByCode, $globalObjectivesByText)
                : null)
            ->filter()
            ->values()
            ->all();

        $topic['section_form'] = $sectionForm;
        $topic['unit_id'] = $unitId;
        $topic['unit_title'] = $unitTitle;
        $topic['path'] = $path;
        $topic['path_label'] = $pathLabel !== '' ? $pathLabel : trim((string) ($topic['title'] ?? ''));
        $topic['context_key'] = $contextKey;
        $topic['local_topic_id'] = $localTopic?->id;
        $topic['objective_groups'] = $objectiveGroups;
        $topic['subtopics'] = $subtopics;

        return $topic;
    }

    private function resolvePublishedReferenceScheme(int $subjectId, int $gradeId, int $termId): ?StandardScheme
    {
        return StandardScheme::query()
            ->where('subject_id', $subjectId)
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->whereNotNull('published_at')
            ->with([
                'entries.objectives.topic',
                'entries.syllabusTopic',
                'subject',
                'grade',
                'panelLead',
                'creator',
                'publisher',
            ])
            ->orderByDesc('published_at')
            ->first();
    }

    private function buildPublishedSchemePlannerStructure(?StandardScheme $referenceScheme): ?array
    {
        if (!$referenceScheme) {
            return null;
        }

        $referenceScheme->loadMissing(['entries.objectives.topic', 'entries.syllabusTopic', 'subject', 'grade', 'panelLead', 'creator', 'publisher']);

        $units = $referenceScheme->entries
            ->sortBy('week_number')
            ->map(function ($entry): ?array {
                $objectiveGroups = $this->buildReferenceObjectiveGroups($entry);
                $hasPlannableContent = filled($entry->topic)
                    || filled($entry->sub_topic)
                    || !empty($objectiveGroups);

                if (!$hasPlannableContent) {
                    return null;
                }

                $topicLabel = trim((string) ($entry->topic ?: ('Week ' . $entry->week_number)));
                $subTopicLabel = trim((string) ($entry->sub_topic ?: $topicLabel));

                return [
                    'id' => 'WK ' . $entry->week_number,
                    'title' => 'Week ' . $entry->week_number,
                    'topics' => [[
                        'section_form' => 'Published Scheme',
                        'unit_id' => 'WK ' . $entry->week_number,
                        'unit_title' => 'Week ' . $entry->week_number,
                        'title' => $topicLabel,
                        'description' => $entry->syllabusTopic?->name
                            ? 'Linked syllabus topic: ' . $entry->syllabusTopic->name
                            : 'Published standard scheme entry',
                        'path' => [$subTopicLabel],
                        'path_label' => $subTopicLabel,
                        'context_key' => 'standard-scheme-entry-' . $entry->id,
                        'local_topic_id' => $entry->syllabus_topic_id ? (int) $entry->syllabus_topic_id : null,
                        'objective_groups' => $objectiveGroups,
                        'subtopics' => [],
                    ]],
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (empty($units)) {
            return null;
        }

        $ownerName = trim((string) (
            $referenceScheme->panelLead?->full_name
            ?? $referenceScheme->panelLead?->name
            ?? $referenceScheme->creator?->full_name
            ?? $referenceScheme->creator?->name
            ?? ''
        ));
        $title = 'Published Standard Scheme';
        if ($ownerName !== '') {
            $title .= ' · ' . $ownerName;
        }

        return [
            'title' => $title,
            'sections' => [[
                'form' => 'Published Scheme',
                'units' => $units,
            ]],
        ];
    }

    private function buildReferenceObjectiveGroups($entry): array
    {
        $linkedObjectives = $entry->objectives
            ->map(function ($objective): array {
                return [
                    'text' => trim((string) $objective->objective_text),
                    'code' => filled($objective->code) ? trim((string) $objective->code) : null,
                    'cognitive_level' => filled($objective->cognitive_level) ? trim((string) $objective->cognitive_level) : null,
                    'sequence' => is_numeric($objective->sequence ?? null) ? (int) $objective->sequence : null,
                    'local_objective_id' => (int) $objective->id,
                ];
            })
            ->filter(fn (array $objective): bool => $objective['text'] !== '')
            ->values()
            ->all();

        if (!empty($linkedObjectives)) {
            return [[
                'key' => 'linked_objectives',
                'label' => 'Linked Objectives',
                'objectives' => $linkedObjectives,
            ]];
        }

        $freeformObjectives = collect($this->extractReferenceObjectiveTexts($entry->learning_objectives))
            ->values()
            ->map(function (string $text, int $index): array {
                return [
                    'text' => $text,
                    'code' => null,
                    'cognitive_level' => null,
                    'sequence' => $index + 1,
                    'local_objective_id' => null,
                ];
            })
            ->all();

        if (empty($freeformObjectives)) {
            return [];
        }

        return [[
            'key' => 'planned_objectives',
            'label' => 'Planned Objectives',
            'objectives' => $freeformObjectives,
        ]];
    }

    private function extractReferenceObjectiveTexts(?string $html): array
    {
        $value = trim((string) $html);
        if ($value === '') {
            return [];
        }

        $normalized = preg_replace('/<\s*br\s*\/?>/i', "\n", $value) ?? $value;
        $normalized = preg_replace('/<\/(li|p|div|h[1-6])>/i', "\n", $normalized) ?? $normalized;
        $normalized = html_entity_decode(strip_tags($normalized), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return collect(preg_split('/\R+/', $normalized) ?: [])
            ->map(function ($line): string {
                $line = trim((string) $line);
                $line = preg_replace('/^[\-\x{2022}\*\d\.\)\(]+\s*/u', '', $line) ?? $line;

                return trim($line);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizePlannerText(?string $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));
        $normalized = preg_replace('/[^\pL\pN]+/u', ' ', $normalized) ?? $normalized;

        return preg_replace('/\s+/', ' ', $normalized) ?? '';
    }

    private function localTopicPlannerKey($topic): string
    {
        $context = SyllabusStructureHelper::parseTopicDescription($topic->description);
        $path = $context['path'];

        if (empty($path)) {
            $path = [trim((string) $topic->name)];
        }

        return SyllabusStructureHelper::buildPlannerKey(
            $context['section_form'],
            $context['unit_id'],
            $context['unit_title'],
            $path
        );
    }

    private function matchPlannerObjectiveId(
        ?string $code,
        ?string $text,
        Collection $localObjectivesByCode,
        Collection $localObjectivesByText
    ): ?int {
        $normalizedCode = $this->normalizePlannerText($code);
        if ($normalizedCode !== '' && $localObjectivesByCode->has($normalizedCode)) {
            return (int) $localObjectivesByCode->get($normalizedCode);
        }

        $normalizedText = $this->normalizePlannerText($text);
        if ($normalizedText !== '' && $localObjectivesByText->has($normalizedText)) {
            return (int) $localObjectivesByText->get($normalizedText);
        }

        return null;
    }
}
