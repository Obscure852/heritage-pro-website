<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreTagRequest;
use App\Http\Requests\Documents\UpdateTagRequest;
use App\Models\DocumentTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TagController extends Controller {
    /**
     * Display the tag admin page.
     */
    public function index(): View {
        Gate::authorize('manage-document-categories');

        $tags = DocumentTag::with('createdBy:id,firstname,lastname')
            ->orderBy('name')
            ->get();

        return view('documents.tags.index', compact('tags'));
    }

    /**
     * Store a new tag.
     */
    public function store(StoreTagRequest $request): JsonResponse {
        Gate::authorize('manage-document-categories');

        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by_user_id'] = $request->user()->id;

        // Admin-created tags default to official
        if (!isset($validated['is_official'])) {
            $validated['is_official'] = true;
        }

        $tag = DocumentTag::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag created.',
            'tag' => $tag,
        ]);
    }

    /**
     * Update an existing tag.
     */
    public function update(UpdateTagRequest $request, DocumentTag $tag): JsonResponse {
        Gate::authorize('manage-document-categories');

        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated.',
            'tag' => $tag->fresh(),
        ]);
    }

    /**
     * Delete a tag (only if unused).
     */
    public function destroy(DocumentTag $tag): JsonResponse {
        Gate::authorize('manage-document-categories');

        if ($tag->usage_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tag with existing usage. Merge into another tag instead.',
            ], 422);
        }

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted.',
        ]);
    }

    /**
     * Merge one tag into another (source -> target).
     * Re-tags all documents, updates usage counts, deletes source.
     */
    public function merge(Request $request): JsonResponse {
        Gate::authorize('manage-document-categories');

        $request->validate([
            'source_id' => 'required|integer|exists:document_tags,id',
            'target_id' => 'required|integer|exists:document_tags,id|different:source_id',
        ]);

        $result = DB::transaction(function () use ($request) {
            $source = DocumentTag::findOrFail($request->source_id);
            $target = DocumentTag::findOrFail($request->target_id);

            $sourceDocIds = $source->documents()->pluck('documents.id');
            $targetDocIds = $target->documents()->pluck('documents.id');

            // Documents that need to be retagged (exist on source but not already on target)
            $needsRetagging = $sourceDocIds->diff($targetDocIds);

            // Update pivot rows for documents that need retagging
            if ($needsRetagging->isNotEmpty()) {
                DB::table('document_tag')
                    ->where('tag_id', $source->id)
                    ->whereIn('document_id', $needsRetagging->toArray())
                    ->update(['tag_id' => $target->id]);
            }

            // Delete remaining source pivot rows (duplicates where doc already had target tag)
            DB::table('document_tag')
                ->where('tag_id', $source->id)
                ->delete();

            // Recalculate target usage count
            $target->update([
                'usage_count' => DB::table('document_tag')
                    ->where('tag_id', $target->id)
                    ->count(),
            ]);

            $sourceName = $source->name;
            $targetName = $target->name;

            // Delete source tag
            $source->delete();

            return [
                'source_name' => $sourceName,
                'target_name' => $targetName,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => "Merged '{$result['source_name']}' into '{$result['target_name']}'.",
        ]);
    }

    /**
     * Search tags for Select2 AJAX source.
     * Returns tags matching name LIKE %q%, limited to 20.
     */
    public function search(Request $request): JsonResponse {
        $query = $request->get('q', '');

        $tags = DocumentTag::where('name', 'LIKE', '%' . $query . '%')
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($tag) {
                return ['id' => $tag->id, 'text' => $tag->name];
            });

        return response()->json($tags);
    }
}
