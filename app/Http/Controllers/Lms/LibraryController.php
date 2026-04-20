<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\CourseTemplate;
use App\Models\Lms\LibraryCollection;
use App\Models\Lms\LibraryItem;
use App\Models\Lms\LibraryItemUsage;
use App\Models\Lms\LibraryItemVersion;
use App\Models\Lms\LibraryTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LibraryController extends Controller {
    // Main library view
    public function index(Request $request) {
        $user = Auth::user();

        $collections = LibraryCollection::accessibleBy($user)
            ->rootLevel()
            ->withCount('items')
            ->orderBy('name')
            ->get();

        $query = LibraryItem::accessibleBy($user);

        // Filters
        if ($request->type) {
            $query->ofType($request->type);
        }

        if ($request->collection_id) {
            $query->where('collection_id', $request->collection_id);
        }

        if ($request->tag) {
            $query->whereHas('tags', fn($q) => $q->where('slug', $request->tag));
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $items = $query->with(['collection', 'tags', 'creator'])
            ->latest()
            ->paginate(24);

        $tags = LibraryTag::withCount('items')
            ->orderByDesc('items_count')
            ->take(20)
            ->get();

        $recentlyViewed = \DB::table('lms_library_recent_views')
            ->where('user_id', $user->id)
            ->orderByDesc('viewed_at')
            ->take(5)
            ->pluck('item_id');

        $recentItems = LibraryItem::whereIn('id', $recentlyViewed)->get();

        // Get type counts for stats
        $typeCounts = LibraryItem::accessibleBy($user)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $totalItems = array_sum($typeCounts);

        return view('lms.library.index', compact('collections', 'items', 'tags', 'recentItems', 'typeCounts', 'totalItems'));
    }

    // Create collection
    public function createCollection(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'parent_id' => 'nullable|exists:lms_library_collections,id',
            'visibility' => 'in:private,shared,public',
        ]);

        $validated['created_by'] = Auth::id();

        $collection = LibraryCollection::create($validated);

        return redirect()->route('lms.library.collection', $collection)
            ->with('success', 'Collection created.');
    }

    // View collection
    public function collection(LibraryCollection $collection) {
        $user = Auth::user();

        // Authorization check
        if ($collection->visibility === 'private' && $collection->created_by !== $user->id) {
            abort(403);
        }

        $children = $collection->children()->withCount('items')->get();
        $items = $collection->items()
            ->accessibleBy($user)
            ->with(['tags', 'creator'])
            ->paginate(24);

        return view('lms.library.collection', compact('collection', 'children', 'items'));
    }

    // Upload item
    public function upload(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:512000', // 500MB max
            'collection_id' => 'nullable|exists:lms_library_collections,id',
            'visibility' => 'in:private,shared,public',
            'tags' => 'nullable|string',
            'is_template' => 'boolean',
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $type = $this->detectFileType($mimeType, $file->getClientOriginalExtension());

        $path = $file->store('lms/library', 'public');

        $item = LibraryItem::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $type,
            'mime_type' => $mimeType,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'collection_id' => $validated['collection_id'] ?? null,
            'visibility' => $validated['visibility'] ?? 'private',
            'is_template' => $validated['is_template'] ?? false,
            'created_by' => Auth::id(),
        ]);

        // Handle tags
        if (!empty($validated['tags'])) {
            $tagNames = array_map('trim', explode(',', $validated['tags']));
            $tagIds = [];
            foreach ($tagNames as $name) {
                if (!empty($name)) {
                    $tag = LibraryTag::findOrCreateByName($name);
                    $tagIds[] = $tag->id;
                }
            }
            $item->tags()->sync($tagIds);
        }

        // Generate thumbnail for videos/images
        $this->generateThumbnail($item);

        return redirect()->route('lms.library.item', $item)
            ->with('success', 'Item uploaded successfully.');
    }

    // View item
    public function show(LibraryItem $item) {
        $user = Auth::user();

        // Authorization check
        if ($item->visibility === 'private' && $item->created_by !== $user->id) {
            abort(403);
        }

        // Log view
        \DB::table('lms_library_recent_views')->updateOrInsert(
            ['user_id' => $user->id, 'item_id' => $item->id],
            ['viewed_at' => now()]
        );

        $item->load(['collection', 'tags', 'creator', 'versions', 'usages.usable']);

        $isFavorite = $item->favorites()->where('user_id', $user->id)->exists();

        return view('lms.library.item', compact('item', 'isFavorite'));
    }

    // Edit item
    public function edit(LibraryItem $item) {
        if ($item->created_by !== Auth::id() && !Auth::user()->can('manage-lms-content')) {
            abort(403);
        }

        $collections = LibraryCollection::accessibleBy(Auth::user())->get();
        $allTags = LibraryTag::orderBy('name')->get();

        return view('lms.library.edit', compact('item', 'collections', 'allTags'));
    }

    // Update item
    public function update(Request $request, LibraryItem $item) {
        if ($item->created_by !== Auth::id() && !Auth::user()->can('manage-lms-content')) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'collection_id' => 'nullable|exists:lms_library_collections,id',
            'visibility' => 'in:private,shared,public',
            'tags' => 'nullable|string',
        ]);

        $item->update($validated);

        // Handle tags
        if (isset($validated['tags'])) {
            $tagNames = array_map('trim', explode(',', $validated['tags']));
            $tagIds = [];
            foreach ($tagNames as $name) {
                if (!empty($name)) {
                    $tag = LibraryTag::findOrCreateByName($name);
                    $tagIds[] = $tag->id;
                }
            }
            $item->tags()->sync($tagIds);
        }

        return redirect()->route('lms.library.item', $item)
            ->with('success', 'Item updated.');
    }

    // Upload new version
    public function uploadVersion(Request $request, LibraryItem $item) {
        $validated = $request->validate([
            'file' => 'required|file|max:512000',
            'change_notes' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('lms/library', 'public');

        $latestVersion = $item->versions()->max('version_number') ?? 0;

        LibraryItemVersion::create([
            'item_id' => $item->id,
            'version_number' => $latestVersion + 1,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'change_notes' => $validated['change_notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Update main item
        $item->update([
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'New version uploaded.');
    }

    // Delete item
    public function destroy(LibraryItem $item) {
        if ($item->created_by !== Auth::id() && !Auth::user()->can('manage-lms-content')) {
            abort(403);
        }

        // Check if used anywhere
        if ($item->usages()->count() > 0) {
            return back()->with('error', 'Cannot delete: item is in use.');
        }

        // Delete file
        if ($item->file_path) {
            Storage::disk('public')->delete($item->file_path);
        }

        $item->delete();

        return redirect()->route('lms.library.index')
            ->with('success', 'Item deleted.');
    }

    // Toggle favorite
    public function toggleFavorite(LibraryItem $item) {
        $user = Auth::user();

        if ($item->favorites()->where('user_id', $user->id)->exists()) {
            $item->favorites()->detach($user->id);
            $message = 'Removed from favorites.';
        } else {
            $item->favorites()->attach($user->id);
            $message = 'Added to favorites.';
        }

        return back()->with('success', $message);
    }

    // My favorites
    public function favorites() {
        $items = Auth::user()->favoriteLibraryItems()
            ->with(['collection', 'tags'])
            ->paginate(24);

        return view('lms.library.favorites', compact('items'));
    }

    // Use item in course
    public function useInCourse(Request $request, LibraryItem $item) {
        $validated = $request->validate([
            'usable_type' => 'required|string',
            'usable_id' => 'required|integer',
        ]);

        LibraryItemUsage::create([
            'item_id' => $item->id,
            'usable_type' => $validated['usable_type'],
            'usable_id' => $validated['usable_id'],
            'used_by' => Auth::id(),
        ]);

        $item->incrementUsage();

        return response()->json(['success' => true]);
    }

    // Course templates
    public function templates() {
        $templates = CourseTemplate::where('is_public', true)
            ->orWhere('created_by', Auth::id())
            ->with('creator')
            ->orderBy('name')
            ->get();

        return view('lms.library.templates', compact('templates'));
    }

    // Create template from course
    public function createTemplate(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:lms_courses,id',
            'category' => 'nullable|in:' . implode(',', array_keys(CourseTemplate::$categories)),
            'is_public' => 'boolean',
        ]);

        $course = \App\Models\Lms\Course::with('modules.content')->find($validated['course_id']);

        // Build structure from course
        $structure = [
            'modules' => $course->modules->map(function ($module) {
                return [
                    'title' => $module->title,
                    'description' => $module->description,
                    'content' => $module->content->map(function ($content) {
                        return [
                            'title' => $content->title,
                            'type' => $content->type,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];

        $template = CourseTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'blank',
            'structure' => $structure,
            'settings' => [
                'enrollment_type' => $course->enrollment_type,
                'max_students' => $course->max_students,
            ],
            'is_public' => $validated['is_public'] ?? false,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('lms.library.templates')
            ->with('success', 'Template created from course.');
    }

    protected function detectFileType(string $mimeType, string $extension): string {
        if (str_starts_with($mimeType, 'video/')) return 'video';
        if (str_starts_with($mimeType, 'audio/')) return 'audio';
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if ($mimeType === 'application/pdf') return 'pdf';

        $docTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (in_array($mimeType, $docTypes)) return 'document';

        $spreadsheetTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (in_array($mimeType, $spreadsheetTypes)) return 'spreadsheet';

        $presentationTypes = ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        if (in_array($mimeType, $presentationTypes)) return 'presentation';

        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) return 'archive';

        return 'other';
    }

    protected function generateThumbnail(LibraryItem $item): void {
        // Placeholder - would use FFmpeg for videos, image processing for images
        // For now, just skip thumbnail generation
    }
}
