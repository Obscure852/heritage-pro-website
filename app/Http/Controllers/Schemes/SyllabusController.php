<?php

namespace App\Http\Controllers\Schemes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schemes\StoreSyllabusRequest;
use App\Http\Requests\Schemes\UpdateSyllabusRequest;
use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Schemes\Syllabus;
use App\Models\Schemes\SyllabusTopic;
use App\Services\Documents\DocumentStorageService;
use App\Services\Documents\ExternalDocumentCacheService;
use App\Services\Schemes\SyllabusImportService;
use App\Services\Schemes\SyllabusSourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SyllabusController extends Controller {
    public function index(): View {
        $this->authorize('manage-syllabi');

        $syllabi = Syllabus::with(['subject', 'document'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('schemes.syllabi.index', compact('syllabi'));
    }

    public function create(): View {
        $this->authorize('manage-syllabi');

        $subjects       = Subject::orderBy('name')->get(['id', 'name']);
        $levels         = Grade::distinct()->orderBy('level')->pluck('level');
        $gradeNames     = Grade::orderBy('sequence')->get(['name'])->unique('name')->values();
        $cognitivelevels = self::cognitiveLevels();

        return view('schemes.syllabi.create', compact('subjects', 'levels', 'gradeNames', 'cognitivelevels'));
    }

    public function store(StoreSyllabusRequest $request): RedirectResponse {
        $syllabus = Syllabus::create($request->validated());

        return redirect()->route('syllabi.edit', $syllabus)
            ->with('success', 'Syllabus created successfully.');
    }

    public function edit(Syllabus $syllabus, SyllabusImportService $syllabusImportService): View {
        $this->authorize('manage-syllabi');

        return $this->buildEditView($syllabus, $syllabusImportService);
    }

    public function update(UpdateSyllabusRequest $request, Syllabus $syllabus): RedirectResponse {
        $this->authorize('edit-syllabi');

        $data = $request->validated();
        $sourceUrlChanged = ($data['source_url'] ?? null) !== $syllabus->source_url;

        $syllabus->update($data);

        if ($sourceUrlChanged) {
            app(SyllabusSourceService::class)->clearCache($syllabus->fresh());
        }

        return redirect()->route('syllabi.edit', $syllabus)
            ->with('success', 'Syllabus updated successfully.');
    }

    public function refreshCache(
        Syllabus $syllabus,
        SyllabusSourceService $sourceService,
        SyllabusImportService $syllabusImportService
    ): RedirectResponse {
        $this->authorize('edit-syllabi');

        try {
            $sourceService->refresh($syllabus);
            $syllabus->refresh();

            $message = 'Remote syllabus cache refreshed successfully.';
            if (!$syllabus->topics()->exists()) {
                $importSummary = $syllabusImportService->populateFromCachedStructure($syllabus);
                if ($importSummary['topics'] > 0) {
                    $message = 'Remote syllabus cache refreshed and '
                        . $importSummary['topics'] . ' topic(s) with '
                        . $importSummary['objectives'] . ' objective(s) populated.';
                }
            } else {
                $message = 'Remote syllabus cache refreshed successfully. Existing local topics were left unchanged; use Sync Topics to apply JSON updates.';
            }
        } catch (\Throwable $e) {
            return redirect()->route('syllabi.edit', $syllabus)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('syllabi.edit', $syllabus)
            ->with('success', $message);
    }

    public function populateFromCache(
        Syllabus $syllabus,
        SyllabusImportService $syllabusImportService
    ): RedirectResponse {
        $this->authorize('edit-syllabi');

        try {
            $importSummary = $syllabusImportService->populateFromCachedStructure($syllabus);
        } catch (\Throwable $e) {
            return redirect()->route('syllabi.edit', $syllabus)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('syllabi.edit', $syllabus)
            ->with(
                'success',
                'Populated ' . $importSummary['topics'] . ' topic(s) and '
                . $importSummary['objectives'] . ' objective(s) from the cached JSON.'
            );
    }

    public function syncFromCache(
        Syllabus $syllabus,
        SyllabusImportService $syllabusImportService
    ): RedirectResponse {
        $this->authorize('edit-syllabi');

        try {
            $syncSummary = $syllabusImportService->syncFromCachedStructure($syllabus);
        } catch (\Throwable $e) {
            return redirect()->route('syllabi.edit', $syllabus)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('syllabi.edit', $syllabus)
            ->with('success', $this->buildSyncSummaryMessage($syncSummary));
    }

    public function previewSyncFromCache(
        Syllabus $syllabus,
        SyllabusImportService $syllabusImportService
    ): View|RedirectResponse {
        $this->authorize('edit-syllabi');

        try {
            $syncPreview = $syllabusImportService->previewSyncFromCachedStructure($syllabus);
        } catch (\Throwable $e) {
            return redirect()->route('syllabi.edit', $syllabus)
                ->with('error', $e->getMessage());
        }

        return $this->buildEditView($syllabus, $syllabusImportService, $syncPreview);
    }

    public function destroy(Syllabus $syllabus): RedirectResponse {
        $this->authorize('edit-syllabi');

        $syllabus->delete();

        return redirect()->route('syllabi.index')
            ->with('success', 'Syllabus deleted successfully.');
    }

    public function documentSearch(Request $request): JsonResponse {
        $this->authorize('manage-syllabi');

        $query = Document::select('id', 'title', 'original_name')
            ->visibleTo($request->user());

        if ($q = $request->input('q')) {
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', '%' . $q . '%')
                    ->orWhere('original_name', 'like', '%' . $q . '%');
            });
        }

        $documents = $query->limit(10)->get();

        return response()->json($documents);
    }

    public function objectiveBrowser(Request $request): JsonResponse {
        $this->authorize('access-schemes');

        $request->validate([
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'grade_name' => ['nullable', 'string'],
        ]);

        $objectives = \DB::table('syllabus_objectives as obj')
            ->join('syllabus_topics as t', 't.id', '=', 'obj.syllabus_topic_id')
            ->join('syllabi as s', 's.id', '=', 't.syllabus_id')
            ->where('s.is_active', true)
            ->whereNull('s.deleted_at')
            ->when($request->input('subject_id'), fn ($q, $v) => $q->where('s.subject_id', $v))
            ->when($request->input('grade_name'), fn ($q, $v) => $q->whereRaw('JSON_CONTAINS(s.grades, ?)', [json_encode($v)]))
            ->select(
                'obj.id',
                'obj.code',
                'obj.objective_text',
                'obj.cognitive_level',
                'obj.sequence',
                't.name as topic_name',
                't.sequence as topic_sequence'
            )
            ->orderBy('t.sequence')
            ->orderBy('obj.sequence')
            ->get();

        return response()->json(['objectives' => $objectives]);
    }

    public function previewDocument(
        Syllabus $syllabus,
        Request $request,
        DocumentStorageService $storageService,
        ExternalDocumentCacheService $externalDocumentCacheService
    ): StreamedResponse {
        $this->authorize('access-schemes');

        $syllabus->loadMissing('document');

        if (!$syllabus->is_active || !$syllabus->document) {
            abort(404, 'Syllabus document not found.');
        }

        $document = $syllabus->document;
        $stream = null;
        $mimeType = $document->mime_type ?: 'application/pdf';
        $filename = $document->original_name ?: ($document->title ?: 'syllabus.pdf');

        if ($document->isExternalUrl()) {
            $cachedDocument = $externalDocumentCacheService->openStream($document);
            if ($cachedDocument === null) {
                abort(404, 'Remote syllabus document could not be loaded.');
            }

            $stream = $cachedDocument['stream'];
            $mimeType = $cachedDocument['mime_type'];
            $filename = $cachedDocument['filename'];
        } else {
            $stream = $storageService->download($document->storage_path);

            if ($stream === null) {
                abort(404, 'Document file not found.');
            }
        }

        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,
            'action' => DocumentAudit::ACTION_PREVIEWED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'metadata' => [
                'source' => 'syllabus-module',
                'syllabus_id' => $syllabus->id,
            ],
        ]);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public static function cognitiveLevels(): array {
        return [
            'Knowledge',
            'Comprehension',
            'Application',
            'Analysis',
            'Synthesis',
            'Evaluation',
        ];
    }

    /**
     * @param array{
     *     topics_created:int,
     *     topics_updated:int,
     *     topics_deleted:int,
     *     topics_preserved:int,
     *     objectives_created:int,
     *     objectives_updated:int,
     *     objectives_deleted:int,
     *     objectives_preserved:int
     * } $syncSummary
     */
    private function buildSyncSummaryMessage(array $syncSummary): string
    {
        return 'Syllabus synced from cached JSON. '
            . 'Topics: '
            . $syncSummary['topics_created'] . ' created, '
            . $syncSummary['topics_updated'] . ' updated, '
            . $syncSummary['topics_deleted'] . ' deleted, '
            . $syncSummary['topics_preserved'] . ' preserved. '
            . 'Objectives: '
            . $syncSummary['objectives_created'] . ' created, '
            . $syncSummary['objectives_updated'] . ' updated, '
            . $syncSummary['objectives_deleted'] . ' deleted, '
            . $syncSummary['objectives_preserved'] . ' preserved.';
    }

    /**
     * @param array{
     *     summary: array{
     *         topics_created:int,
     *         topics_updated:int,
     *         topics_deleted:int,
     *         topics_preserved:int,
     *         objectives_created:int,
     *         objectives_updated:int,
     *         objectives_deleted:int,
     *         objectives_preserved:int
     *     },
     *     changes: array{
     *         topics: array{
     *             created: array<int, array<string, mixed>>,
     *             updated: array<int, array<string, mixed>>,
     *             deleted: array<int, array<string, mixed>>,
     *             preserved: array<int, array<string, mixed>>
     *         },
     *         objectives: array{
     *             created: array<int, array<string, mixed>>,
     *             updated: array<int, array<string, mixed>>,
     *             deleted: array<int, array<string, mixed>>,
     *             preserved: array<int, array<string, mixed>>
     *         }
     *     }
     * }|null $syncPreview
     */
    private function buildEditView(
        Syllabus $syllabus,
        SyllabusImportService $syllabusImportService,
        ?array $syncPreview = null
    ): View {
        $syllabus->load(['subject', 'document', 'topics.objectives']);

        $subjects        = Subject::orderBy('name')->get(['id', 'name']);
        $levels          = Grade::distinct()->orderBy('level')->pluck('level');
        $gradeNames      = Grade::orderBy('sequence')->get(['name'])->unique('name')->values();
        $cognitivelevels = self::cognitiveLevels();
        $cachedImportSummary = $syllabusImportService->summarizeStructure(
            is_array($syllabus->cached_structure) ? $syllabus->cached_structure : null
        );
        $hasLocalTopics = $syllabus->topics->isNotEmpty();
        $canPopulateFromCache = !$hasLocalTopics && $cachedImportSummary['topics'] > 0;
        $canSyncFromCache = $hasLocalTopics && $cachedImportSummary['topics'] > 0;
        $canEditSyllabus = request()->user()?->can('edit-syllabi') ?? false;

        return view('schemes.syllabi.edit', compact(
            'syllabus',
            'subjects',
            'levels',
            'gradeNames',
            'cognitivelevels',
            'cachedImportSummary',
            'hasLocalTopics',
            'canPopulateFromCache',
            'canSyncFromCache',
            'canEditSyllabus',
            'syncPreview'
        ));
    }
}
