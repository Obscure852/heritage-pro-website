<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreCategoryRequest;
use App\Http\Requests\Documents\UpdateCategoryRequest;
use App\Models\DocumentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller {
    /**
     * Display the category admin page.
     */
    public function index(): View {
        Gate::authorize('manage-document-categories');

        $categories = DocumentCategory::with('parent:id,name')
            ->withCount('documents', 'children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $parentCategories = DocumentCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('documents.categories.index', compact('categories', 'parentCategories'));
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse {
        Gate::authorize('manage-document-categories');

        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        // Enforce 2-level hierarchy: if parent is set, parent must not have a parent
        if (!empty($validated['parent_id'])) {
            $parent = DocumentCategory::find($validated['parent_id']);
            if ($parent && $parent->parent_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create more than 2 levels of category hierarchy.',
                ], 422);
            }
        }

        $category = DocumentCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created.',
            'category' => $category,
        ]);
    }

    /**
     * Update an existing category.
     */
    public function update(UpdateCategoryRequest $request, DocumentCategory $category): JsonResponse {
        Gate::authorize('manage-document-categories');

        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        // Enforce 2-level hierarchy: if parent_id is set, the parent must NOT itself have a parent_id
        if (!empty($validated['parent_id'])) {
            $parent = DocumentCategory::find($validated['parent_id']);
            if ($parent && $parent->parent_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create more than 2 levels of category hierarchy.',
                ], 422);
            }
        }

        // If this category has children, it cannot become a child itself
        if (!empty($validated['parent_id']) && $category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set a parent on a category that has children. Remove children first.',
            ], 422);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated.',
            'category' => $category->fresh(),
        ]);
    }

    /**
     * Delete a category.
     */
    public function destroy(DocumentCategory $category): JsonResponse {
        Gate::authorize('manage-document-categories');

        if ($category->documents()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing documents.',
            ], 422);
        }

        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with child categories. Remove children first.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted.',
        ]);
    }
}
