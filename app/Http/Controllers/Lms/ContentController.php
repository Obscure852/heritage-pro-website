<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\ContentItem;
use App\Models\Lms\ContentProgress;
use App\Models\Lms\Document;
use App\Models\Lms\Enrollment;
use App\Models\Lms\LibraryItem;
use App\Models\Lms\LibraryItemUsage;
use App\Models\Lms\Module;
use App\Models\Lms\Quiz;
use App\Models\Lms\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller {
    public function index(Module $module) {
        $module->load([
            'course',
            'contentItems' => function ($query) {
                $query->orderBy('sequence');
            },
        ]);

        return view('lms.content.index', compact('module'));
    }

    public function create(Module $module) {
        Gate::authorize('manage-lms-content');

        $contentTypes = ContentItem::CONTENT_TYPES;

        // Get library items grouped by compatible content type
        $user = Auth::user();
        $libraryItems = [
            'video_upload' => LibraryItem::accessibleBy($user)->compatibleWith('video_upload')->get(),
            'document' => LibraryItem::accessibleBy($user)->compatibleWith('document')->get(),
            'image' => LibraryItem::accessibleBy($user)->compatibleWith('image')->get(),
            'audio' => LibraryItem::accessibleBy($user)->compatibleWith('audio')->get(),
        ];

        return view('lms.content.create', compact('module', 'contentTypes', 'libraryItems'));
    }
    

    public function store(Request $request, Module $module) {
        Gate::authorize('manage-lms-content');
        if ($request->filled('library_item_id')) {
            return $this->storeFromLibrary($request, $module);
        }

        $baseValidation = [
            'type' => 'required|in:' . implode(',', array_keys(ContentItem::CONTENT_TYPES)),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'estimated_duration' => 'nullable|integer|min:1',
        ];

        $type = $request->input('type');
        $typeValidation = match ($type) {
            'text' => ['content' => 'required|string'],
            'document' => ['file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:20480'],
            'video_youtube' => ['youtube_url' => ['required', 'url', 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/']],
            'video_upload' => ['video_file' => 'required|file|mimes:mp4,webm,mov|max:512000'],
            'audio' => ['audio_file' => 'required|file|mimes:mp3,wav,ogg|max:51200'],
            'image' => ['image_file' => 'required|image|max:10240'],
            'quiz' => [
                'quiz_instructions' => 'nullable|string',
                'quiz_time_limit' => 'nullable|integer|min:1',
                'quiz_passing_score' => 'nullable|integer|min:0|max:100',
                'quiz_max_attempts' => 'nullable|integer|min:1',
                'quiz_shuffle_questions' => 'boolean',
            ],
            'external_link' => ['external_url' => 'required|url'],
            default => [],
        };

        $validated = $request->validate(array_merge($baseValidation, $typeValidation));
        $maxSequence = $module->contentItems()->max('sequence') ?? 0;

        $contentData = [
            'module_id' => $module->id,
            'type' => $type,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'sequence' => $maxSequence + 1,
            'is_required' => $request->boolean('is_required'),
            'estimated_duration' => $validated['estimated_duration'] ?? null,
        ];

        try {
            switch ($type) {
                case 'text':
                    $contentData['content'] = $validated['content'];
                    break;

                case 'document':
                    $file = $request->file('file');
                    $path = $file->store('lms/documents', 'public');
                    if (!$path) {
                        throw new \RuntimeException('Failed to upload document file.');
                    }
                    $contentData['file_path'] = $path;

                    // Create document record
                    $document = Document::create([
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size_bytes' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'document_type' => Document::detectDocumentType($file->getMimeType()),
                        'allow_download' => true,
                    ]);
                    $contentData['contentable_id'] = $document->id;
                    $contentData['contentable_type'] = Document::class;
                    break;

                case 'video_youtube':
                    $video = Video::create([
                        'source_type' => 'youtube',
                        'source_id' => Video::extractYoutubeId($validated['youtube_url']),
                    ]);
                    $contentData['contentable_id'] = $video->id;
                    $contentData['contentable_type'] = Video::class;
                    break;

                case 'video_upload':
                    $file = $request->file('video_file');
                    $path = $file->store('lms/videos', 'public');
                    if (!$path) {
                        throw new \RuntimeException('Failed to upload video file.');
                    }

                    $video = Video::create([
                        'source_type' => 'upload',
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_size_bytes' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'transcoding_status' => Video::TRANSCODING_NOT_REQUIRED,
                    ]);
                    $contentData['contentable_id'] = $video->id;
                    $contentData['contentable_type'] = Video::class;
                    break;

                case 'quiz':
                    $quiz = Quiz::create([
                        'title' => $validated['title'],
                        'instructions' => $validated['quiz_instructions'] ?? null,
                        'time_limit_minutes' => $validated['quiz_time_limit'] ?? null,
                        'passing_score' => $validated['quiz_passing_score'] ?? 70,
                        'max_attempts' => $validated['quiz_max_attempts'] ?? null,
                        'shuffle_questions' => $request->boolean('quiz_shuffle_questions'),
                        'show_correct_answers' => true,
                    ]);
                    $contentData['contentable_id'] = $quiz->id;
                    $contentData['contentable_type'] = Quiz::class;
                    break;

                case 'external_link':
                    $contentData['external_url'] = $validated['external_url'];
                    break;

                case 'audio':
                    $file = $request->file('audio_file');
                    $path = $file->store('lms/audio', 'public');
                    if (!$path) {
                        throw new \RuntimeException('Failed to upload audio file.');
                    }
                    $contentData['file_path'] = $path;
                    break;

                case 'image':
                    $file = $request->file('image_file');
                    $path = $file->store('lms/images', 'public');
                    if (!$path) {
                        throw new \RuntimeException('Failed to upload image file.');
                    }
                    $contentData['file_path'] = $path;
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Content upload failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'module_id' => $module->id,
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to upload content. Please try again.');
        }

        $contentItem = ContentItem::create($contentData);

        // Update the contentable's content_item_id if it has one (for bi-directional relationship)
        if ($contentItem->contentable && method_exists($contentItem->contentable, 'contentItem')) {
            $contentItem->contentable->update(['content_item_id' => $contentItem->id]);
        }

        // Redirect to quiz question editor if quiz
        if ($type === 'quiz') {
            return redirect()
                ->route('lms.quizzes.questions', $contentItem->contentable)
                ->with('success', 'Quiz created. Now add questions.');
        }

        return redirect()
            ->route('lms.modules.edit', $module)
            ->with('success', 'Content added successfully.');
    }

    public function show(ContentItem $content) {
        $content->load(['module.course', 'contentable']);

        return view('lms.content.show', compact('content'));
    }

    public function edit(ContentItem $content) {
        Gate::authorize('manage-lms-content');

        $content->load(['module.course', 'contentable', 'libraryItem']);
        $contentTypes = ContentItem::CONTENT_TYPES;

        // Get library items grouped by compatible content type (for changing source)
        $user = Auth::user();
        $libraryItems = [
            'video_upload' => LibraryItem::accessibleBy($user)->compatibleWith('video_upload')->get(),
            'document' => LibraryItem::accessibleBy($user)->compatibleWith('document')->get(),
            'image' => LibraryItem::accessibleBy($user)->compatibleWith('image')->get(),
            'audio' => LibraryItem::accessibleBy($user)->compatibleWith('audio')->get(),
        ];

        return view('lms.content.edit', compact('content', 'contentTypes', 'libraryItems'));
    }

    public function update(Request $request, ContentItem $content) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'estimated_duration' => 'nullable|integer|min:1',
            'content' => 'nullable|string',
            'youtube_url' => 'nullable|url',
            'external_url' => 'nullable|url',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:20480',
            'video_file' => 'nullable|file|mimes:mp4,webm,mov|max:512000',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:51200',
            'image_file' => 'nullable|image|max:10240',
            'library_item_id' => 'nullable|string',
        ]);

        $content->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_required' => $request->boolean('is_required'),
            'estimated_duration' => $validated['estimated_duration'],
        ]);

        // Handle library item changes for applicable types
        $libraryItemId = $request->input('library_item_id');
        $isLibraryChange = $libraryItemId && $libraryItemId !== '' && $libraryItemId !== 'upload';
        $isUploadChange = $libraryItemId === 'upload';

        // Handle type-specific updates
        switch ($content->type) {
            case 'text':
                $content->update(['content' => $validated['content'] ?? $content->content]);
                break;

            case 'video_youtube':
                if (!empty($validated['youtube_url'])) {
                    if ($content->contentable) {
                        $content->contentable->update([
                            'source_id' => Video::extractYoutubeId($validated['youtube_url']),
                        ]);
                    } else {
                        // Create new video contentable
                        $video = Video::create([
                            'source_type' => 'youtube',
                            'source_id' => Video::extractYoutubeId($validated['youtube_url']),
                        ]);
                        $content->update([
                            'contentable_id' => $video->id,
                            'contentable_type' => Video::class,
                        ]);
                    }
                }
                break;

            case 'external_link':
                $content->update(['external_url' => $validated['external_url']]);
                break;

            case 'document':
                if ($isLibraryChange) {
                    $this->switchToLibraryItem($content, $libraryItemId);
                } elseif ($isUploadChange && $request->hasFile('file')) {
                    $file = $request->file('file');
                    $path = $file->store('lms/documents', 'public');

                    // Clear library reference if switching from library to upload
                    $this->clearLibraryReference($content);

                    if ($content->contentable) {
                        // Delete old file and update existing document
                        if ($content->contentable->file_path && !$content->isFromLibrary()) {
                            Storage::disk('public')->delete($content->contentable->file_path);
                        }
                        $content->contentable->update([
                            'original_filename' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'file_size_bytes' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'document_type' => Document::detectDocumentType($file->getMimeType()),
                        ]);
                    } else {
                        // Create new document contentable
                        $document = Document::create([
                            'original_filename' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'file_size_bytes' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'document_type' => Document::detectDocumentType($file->getMimeType()),
                            'allow_download' => true,
                        ]);
                        $content->update([
                            'contentable_id' => $document->id,
                            'contentable_type' => Document::class,
                        ]);
                    }
                    $content->update(['file_path' => $path, 'library_item_id' => null]);
                }
                break;

            case 'video_upload':
                if ($isLibraryChange) {
                    $this->switchToLibraryItem($content, $libraryItemId);
                } elseif ($isUploadChange && $request->hasFile('video_file')) {
                    $file = $request->file('video_file');
                    $path = $file->store('lms/videos', 'public');

                    // Clear library reference
                    $this->clearLibraryReference($content);

                    if ($content->contentable && $content->contentable instanceof Video) {
                        if ($content->contentable->file_path && !$content->isFromLibrary()) {
                            Storage::disk('public')->delete($content->contentable->file_path);
                        }
                        $content->contentable->update([
                            'source_type' => 'upload',
                            'file_path' => $path,
                            'original_filename' => $file->getClientOriginalName(),
                            'file_size_bytes' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                        ]);
                    } else {
                        $video = Video::create([
                            'source_type' => 'upload',
                            'file_path' => $path,
                            'original_filename' => $file->getClientOriginalName(),
                            'file_size_bytes' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'transcoding_status' => Video::TRANSCODING_NOT_REQUIRED,
                        ]);
                        $content->update([
                            'contentable_id' => $video->id,
                            'contentable_type' => Video::class,
                        ]);
                    }
                    $content->update(['file_path' => $path, 'library_item_id' => null]);
                }
                break;

            case 'audio':
                if ($isLibraryChange) {
                    $this->switchToLibraryItem($content, $libraryItemId);
                } elseif ($isUploadChange && $request->hasFile('audio_file')) {
                    $file = $request->file('audio_file');

                    // Clear library reference
                    $this->clearLibraryReference($content);

                    // Delete old file if exists and not from library
                    if ($content->file_path && !$content->isFromLibrary()) {
                        Storage::disk('public')->delete($content->file_path);
                    }

                    $path = $file->store('lms/audio', 'public');
                    $content->update(['file_path' => $path, 'library_item_id' => null]);
                }
                break;

            case 'image':
                if ($isLibraryChange) {
                    $this->switchToLibraryItem($content, $libraryItemId);
                } elseif ($isUploadChange && $request->hasFile('image_file')) {
                    $file = $request->file('image_file');

                    // Clear library reference
                    $this->clearLibraryReference($content);

                    // Delete old file if exists and not from library
                    if ($content->file_path && !$content->isFromLibrary()) {
                        Storage::disk('public')->delete($content->file_path);
                    }

                    $path = $file->store('lms/images', 'public');
                    $content->update(['file_path' => $path, 'library_item_id' => null]);
                }
                break;
        }

        return back()->with('success', 'Content updated successfully.');
    }

    /**
     * Switch content item to use a library item.
     */
    protected function switchToLibraryItem(ContentItem $content, int $libraryItemId): void {
        $libraryItem = LibraryItem::findOrFail($libraryItemId);

        // Verify user can access this library item
        $canAccess = LibraryItem::accessibleBy(Auth::user())
            ->where('id', $libraryItem->id)
            ->exists();

        if (!$canAccess) {
            return;
        }

        DB::transaction(function () use ($content, $libraryItem) {
            // Clear old library reference if exists
            $this->clearLibraryReference($content);

            // Update content to use library item
            $content->update([
                'library_item_id' => $libraryItem->id,
                'file_path' => $libraryItem->file_path,
                'external_url' => $libraryItem->external_url,
            ]);

            // Create usage tracking record
            LibraryItemUsage::create([
                'item_id' => $libraryItem->id,
                'usable_type' => ContentItem::class,
                'usable_id' => $content->id,
                'used_by' => Auth::id(),
            ]);

            // Increment usage count
            $libraryItem->increment('usage_count');
        });
    }

    /**
     * Clear library reference from content item.
     */
    protected function clearLibraryReference(ContentItem $content): void {
        if (!$content->library_item_id) {
            return;
        }

        DB::transaction(function () use ($content) {
            // Delete usage tracking
            LibraryItemUsage::where('usable_type', ContentItem::class)
                ->where('usable_id', $content->id)
                ->delete();

            // Decrement usage count on old library item
            if ($content->libraryItem) {
                $content->libraryItem->decrement('usage_count');
            }

            $content->update(['library_item_id' => null]);
        });
    }

    public function destroy(ContentItem $content) {
        Gate::authorize('manage-lms-content');

        // Guard: prevent deleting quizzes with in-progress attempts
        if ($content->contentable instanceof Quiz) {
            $inProgressCount = $content->contentable->attempts()
                ->whereNull('submitted_at')
                ->count();

            if ($inProgressCount > 0) {
                $verb = $inProgressCount === 1 ? 'is' : 'are';
                return back()->with('error', "Cannot delete this quiz. There {$verb} {$inProgressCount} attempt(s) currently in progress.");
            }
        }

        $module = $content->module;

        DB::transaction(function () use ($content) {
            // Handle library-sourced content cleanup
            if ($content->isFromLibrary()) {
                // Delete usage tracking record
                LibraryItemUsage::where('usable_type', ContentItem::class)
                    ->where('usable_id', $content->id)
                    ->delete();

                // Decrement usage count on library item
                if ($content->libraryItem) {
                    $content->libraryItem->decrement('usage_count');
                }
                // Do NOT delete the library item's file - it's shared
            } else {
                // Only delete files for non-library content
                if ($content->file_path) {
                    Storage::disk('public')->delete($content->file_path);
                }

                // Delete contentable (video, document, quiz, etc.)
                if ($content->contentable) {
                    if ($content->contentable instanceof Video && $content->contentable->file_path) {
                        Storage::disk('public')->delete($content->contentable->file_path);
                    }
                    if ($content->contentable instanceof Document) {
                        Storage::disk('public')->delete($content->contentable->file_path);
                    }
                    $content->contentable->delete();
                }
            }

            $content->delete();
        });

        // Reorder remaining content
        $module->contentItems()
            ->orderBy('sequence')
            ->get()
            ->each(function ($c, $index) {
                $c->update(['sequence' => $index + 1]);
            });

        return redirect()
            ->route('lms.modules.edit', $module)
            ->with('success', 'Content deleted successfully.');
    }

    public function reorder(Request $request, Module $module) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'integer|exists:lms_content_items,id',
        ]);

        foreach ($validated['items'] as $sequence => $itemId) {
            ContentItem::where('id', $itemId)
                ->where('module_id', $module->id)
                ->update(['sequence' => $sequence + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Content player for students viewing content
     */
    public function player(ContentItem $content) {
        $content->load(['module.course', 'contentable']);

        // Null safety check for module and course
        if (!$content->module || !$content->module->course) {
            abort(404, 'Content not found or not associated with a course.');
        }

        $course = $content->module->course;
        $studentId = Auth::guard('student')->id();

        if (!$studentId) {
            return redirect()
                ->route('lms.courses.show', $course)
                ->with('error', 'You must be logged in as a student to access content.');
        }

        // Get enrollment
        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('student_id', $studentId)
            ->first();

        if (!$enrollment) {
            return redirect()
                ->route('lms.courses.show', $course)
                ->with('error', 'You must be enrolled in this course to access content.');
        }

        // Get or create progress record
        $progress = ContentProgress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'content_item_id' => $content->id,
        ], [
            'started_at' => now(),
        ]);

        // Get previous and next content items
        $allContent = $content->module->contentItems()->orderBy('sequence')->get();
        $currentIndex = $allContent->search(fn($c) => $c->id === $content->id);
        $previousContent = $currentIndex > 0 ? $allContent[$currentIndex - 1] : null;
        $nextContent = $currentIndex < $allContent->count() - 1 ? $allContent[$currentIndex + 1] : null;

        return view('lms.content.player', compact(
            'content',
            'course',
            'enrollment',
            'progress',
            'previousContent',
            'nextContent'
        ));
    }

    /**
     * Update content progress (AJAX)
     */
    public function updateProgress(Request $request, ContentItem $content) {
        $validated = $request->validate([
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        return DB::transaction(function () use ($content, $validated) {
            $enrollment = Enrollment::where('course_id', $content->module->course_id)
                ->where('student_id', Auth::guard('student')->id())
                ->lockForUpdate()
                ->firstOrFail();

            // Use updateOrCreate with unique constraint to prevent duplicates
            $progress = ContentProgress::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'content_item_id' => $content->id,
                ],
                [
                    'started_at' => DB::raw('COALESCE(started_at, NOW())'),
                ]
            );

            // Reload to get current values
            $progress->refresh();

            $progress->update([
                'progress_percentage' => max($progress->progress_percentage ?? 0, $validated['progress_percentage']),
                'time_spent_seconds' => ($progress->time_spent_seconds ?? 0) + ($validated['time_spent'] ?? 0),
                'last_accessed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'progress' => $progress->progress_percentage,
            ]);
        });
    }

    /**
     * Mark content as complete
     */
    public function markComplete(Request $request, ContentItem $content) {
        return DB::transaction(function () use ($content) {
            $enrollment = Enrollment::where('course_id', $content->module->course_id)
                ->where('student_id', Auth::guard('student')->id())
                ->lockForUpdate()
                ->firstOrFail();

            // Use updateOrCreate with unique constraint to prevent duplicates
            $progress = ContentProgress::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'content_item_id' => $content->id,
                ],
                [
                    'started_at' => DB::raw('COALESCE(started_at, NOW())'),
                ]
            );

            // Reload and lock for update
            $progress = ContentProgress::lockForUpdate()->find($progress->id);

            if (!$progress->completed_at) {
                $progress->markAsCompleted();
            }

            // Update enrollment progress
            $enrollment->calculateProgress();

            return response()->json([
                'success' => true,
                'course_progress' => $enrollment->progress_percentage,
            ]);
        });
    }

    /**
     * Get library items for the content picker modal (AJAX).
     */
    public function libraryItems(Request $request, Module $module) {
        Gate::authorize('manage-lms-content');

        $query = LibraryItem::accessibleBy(Auth::user());

        // Filter by content type if specified
        if ($request->filled('content_type')) {
            $query->compatibleWith($request->input('content_type'));
        }

        // Filter by library item type
        if ($request->filled('type')) {
            $query->ofType($request->input('type'));
        }

        // Filter by collection
        if ($request->filled('collection_id')) {
            $query->where('collection_id', $request->input('collection_id'));
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        $items = $query->with('collection')
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        // Format items for JSON response
        $formattedItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'type' => $item->type,
                'type_label' => LibraryItem::$types[$item->type] ?? $item->type,
                'icon' => $item->icon,
                'thumbnail_url' => $item->thumbnail_path
                    ? Storage::disk('public')->url($item->thumbnail_path)
                    : null,
                'file_size' => $item->formatted_size,
                'duration' => $item->formatted_duration,
                'collection_name' => $item->collection?->name,
                'compatible_types' => $item->getCompatibleContentTypes(),
                'default_content_type' => $item->getDefaultContentType(),
                'usage_count' => $item->usage_count,
            ];
        });

        return response()->json([
            'items' => $formattedItems,
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /**
     * Get library item preview data (AJAX).
     */
    public function previewLibraryItem(LibraryItem $item) {
        Gate::authorize('manage-lms-content');

        // Check accessibility
        if (!$item->accessibleBy(Auth::user())->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $previewUrl = null;
        if ($item->file_path) {
            $previewUrl = Storage::disk('public')->url($item->file_path);
        } elseif ($item->external_url) {
            $previewUrl = $item->external_url;
        }

        return response()->json([
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'type' => $item->type,
            'type_label' => LibraryItem::$types[$item->type] ?? $item->type,
            'icon' => $item->icon,
            'file_name' => $item->file_name,
            'file_size' => $item->formatted_size,
            'duration' => $item->formatted_duration,
            'mime_type' => $item->mime_type,
            'thumbnail_url' => $item->thumbnail_path
                ? Storage::disk('public')->url($item->thumbnail_path)
                : null,
            'preview_url' => $previewUrl,
            'compatible_types' => $item->getCompatibleContentTypes(),
            'default_content_type' => $item->getDefaultContentType(),
            'collection_name' => $item->collection?->name,
            'creator_name' => $item->creator?->name,
            'usage_count' => $item->usage_count,
            'created_at' => $item->created_at->format('M d, Y'),
            'updated_at' => $item->updated_at->format('M d, Y'),
        ]);
    }

    /**
     * Create content item from library item.
     */
    protected function storeFromLibrary(Request $request, Module $module) {
        $validated = $request->validate([
            'library_item_id' => 'required|exists:lms_library_items,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'estimated_duration' => 'nullable|integer|min:1',
        ]);

        $libraryItem = LibraryItem::findOrFail($validated['library_item_id']);

        // Verify user can access this library item
        $canAccess = LibraryItem::accessibleBy(Auth::user())
            ->where('id', $libraryItem->id)
            ->exists();

        if (!$canAccess) {
            return back()->with('error', 'You do not have access to this library item.');
        }

        // Get the default content type for this library item
        $contentType = $libraryItem->getDefaultContentType();

        if (!$contentType) {
            return back()->with('error', 'This library item type cannot be used as content.');
        }

        // Get next sequence number
        $maxSequence = $module->contentItems()->max('sequence') ?? 0;

        $contentItem = null;

        DB::transaction(function () use ($validated, $libraryItem, $module, $contentType, $maxSequence, &$contentItem) {
            // Create the content item with library_item_id reference
            $contentData = [
                'module_id' => $module->id,
                'type' => $contentType,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'sequence' => $maxSequence + 1,
                'is_required' => request()->boolean('is_required'),
                'estimated_duration' => $validated['estimated_duration'] ?? $libraryItem->duration_seconds ? (int) ceil($libraryItem->duration_seconds / 60) : null,
                'library_item_id' => $libraryItem->id,
                // For library-sourced content, reference library item's file
                'file_path' => $libraryItem->file_path,
                'external_url' => $libraryItem->external_url,
            ];

            $contentItem = ContentItem::create($contentData);

            // Create usage tracking record
            LibraryItemUsage::create([
                'item_id' => $libraryItem->id,
                'usable_type' => ContentItem::class,
                'usable_id' => $contentItem->id,
                'used_by' => Auth::id(),
            ]);

            // Increment usage count on library item
            $libraryItem->increment('usage_count');
        });

        return redirect()
            ->route('lms.modules.edit', $module)
            ->with('success', 'Content added from library successfully.');
    }
}
