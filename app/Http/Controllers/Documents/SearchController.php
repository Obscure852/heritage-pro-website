<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentTag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller {
    /**
     * Display search results with filter sidebar.
     *
     * Searches document titles, descriptions, and tag names.
     * Applies permission-scoped filtering via scopeVisibleTo.
     * All filters combine with AND logic.
     */
    public function index(Request $request): View {
        $this->authorize('viewAny', Document::class);

        $searchQuery = trim($request->input('q', ''));

        $query = Document::with(['owner:id,firstname,lastname', 'category:id,name', 'tags:id,name'])
            ->select([
                'id', 'ulid', 'title', 'description', 'source_type', 'extension', 'mime_type',
                'size_bytes', 'status', 'visibility', 'owner_id', 'category_id',
                'folder_id', 'created_at', 'updated_at',
            ]);

        // Text search on title, description + tag names
        if ($searchQuery !== '') {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhereHas('tags', function ($tq) use ($searchQuery) {
                      $tq->where('name', 'LIKE', '%' . $searchQuery . '%');
                  });
            });
        }

        // Filter: file_type (extension)
        if ($request->filled('file_type')) {
            $query->where('extension', $request->input('file_type'));
        }

        // Filter: category_id
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter: tag_ids (array) -- requires ALL selected tags (AND logic)
        if ($request->filled('tag_ids') && is_array($request->input('tag_ids'))) {
            foreach ($request->input('tag_ids') as $tagId) {
                $query->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('document_tags.id', (int) $tagId);
                });
            }
        }

        // Filter: date_from
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        // Filter: date_to
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Filter: owner_id
        if ($request->filled('owner_id')) {
            $query->where('owner_id', (int) $request->input('owner_id'));
        }

        // Filter: status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter: include_archived (SRC-05) -- exclude archived by default
        if (!$request->boolean('include_archived')) {
            $query->notArchived();
        }

        // Permission filtering (SRC-04)
        $query->visibleTo($request->user());

        // Paginate at 100 per page (SRC-09)
        $documents = $query->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends($request->only(['q', 'file_type', 'category_id', 'tag_ids', 'date_from', 'date_to', 'owner_id', 'status', 'include_archived']));

        // Load filter option data for sidebar
        $categories = DocumentCategory::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $tags = DocumentTag::select('id', 'name')
            ->orderBy('name')
            ->get();

        $fileTypes = Document::select('extension')
            ->distinct()
            ->whereNotNull('extension')
            ->orderBy('extension')
            ->pluck('extension');

        $statuses = [
            Document::STATUS_DRAFT => 'Draft',
            Document::STATUS_PENDING_REVIEW => 'Pending Review',
            Document::STATUS_UNDER_REVIEW => 'Under Review',
            Document::STATUS_REVISION_REQUIRED => 'Revision Required',
            Document::STATUS_APPROVED => 'Approved',
            Document::STATUS_PUBLISHED => 'Published',
            Document::STATUS_ARCHIVED => 'Archived',
        ];

        $owners = User::whereIn('id', Document::select('owner_id')->distinct())
            ->select('id', 'firstname', 'lastname')
            ->orderBy('firstname')
            ->get();

        return view('documents.search.index', compact(
            'documents',
            'searchQuery',
            'categories',
            'tags',
            'fileTypes',
            'statuses',
            'owners',
        ));
    }

    /**
     * Return live search suggestions as JSON.
     *
     * Searches title matches and tag name matches, deduplicates, returns top 8.
     * Uses XHR abort pattern on the client to handle stale responses.
     */
    public function suggestions(Request $request): JsonResponse {
        $this->authorize('viewAny', Document::class);

        $searchQuery = trim($request->input('q', ''));

        if (mb_strlen($searchQuery) < 2) {
            return response()->json([]);
        }

        $user = $request->user();

        // Title matches
        $titleMatches = Document::select('id', 'ulid', 'title', 'extension')
            ->where('title', 'LIKE', '%' . $searchQuery . '%')
            ->notArchived()
            ->visibleTo($user)
            ->limit(8)
            ->get();

        // Tag name matches
        $tagMatches = Document::select('documents.id', 'documents.ulid', 'documents.title', 'documents.extension')
            ->join('document_tag', 'documents.id', '=', 'document_tag.document_id')
            ->join('document_tags', 'document_tag.tag_id', '=', 'document_tags.id')
            ->where('document_tags.name', 'LIKE', '%' . $searchQuery . '%')
            ->notArchived()
            ->visibleTo($user)
            ->limit(8)
            ->get();

        // Merge, deduplicate by id, take 8
        $merged = $titleMatches->merge($tagMatches)
            ->unique('id')
            ->take(8)
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'ulid' => $doc->ulid,
                    'title' => $doc->title,
                    'extension' => $doc->extension,
                ];
            })
            ->values();

        return response()->json($merged);
    }
}
