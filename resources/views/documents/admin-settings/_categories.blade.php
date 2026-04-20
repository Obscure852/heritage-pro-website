<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="fas fa-folder-tree me-2 text-primary"></i>Categories</h5>
    <button type="button" class="btn btn-primary" id="btnNewCategory">
        <i class="fas fa-plus me-1"></i> New Category
    </button>
</div>

@if($categories->isEmpty())
    <div class="empty-state">
        <i class="fas fa-folder-open d-block"></i>
        <h5>No categories yet</h5>
        <p>Create your first document category to get started.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Parent</th>
                    <th>Icon</th>
                    <th>Color</th>
                    <th>Retention</th>
                    <th>Approval</th>
                    <th>Active</th>
                    <th>Order</th>
                    <th>Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>
                            @if($category->parent_id)
                                <span class="child-category">&#8627; {{ $category->name }}</span>
                            @else
                                <strong>{{ $category->name }}</strong>
                            @endif
                        </td>
                        <td>{{ $category->parent->name ?? '-' }}</td>
                        <td>
                            @if($category->icon)
                                <i class="{{ $category->icon }}"></i>
                                <small class="text-muted ms-1">{{ $category->icon }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($category->color)
                                <span class="color-swatch" style="background-color: {{ $category->color }}"></span>
                                <small class="text-muted ms-1">{{ $category->color }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $category->retention_days ? $category->retention_days . ' days' : '-' }}</td>
                        <td>
                            <span class="badge-{{ $category->requires_approval ? 'yes' : 'no' }}">
                                {{ $category->requires_approval ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-{{ $category->is_active ? 'active' : 'inactive' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $category->sort_order }}</td>
                        <td>{{ $category->documents_count }}</td>
                        <td class="action-btns">
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="openEditCategoryModal({{ $category->id }}, {{ json_encode($category) }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="deleteCategory({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->documents_count }}, {{ $category->children_count }})"
                                @if($category->documents_count > 0 || $category->children_count > 0) title="Cannot delete: has {{ $category->documents_count }} document(s) and {{ $category->children_count }} child category(ies)" @endif
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Create/Edit Category Modal --}}
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm">
                @csrf
                <input type="hidden" id="categoryId" name="category_id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="catName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="catName" name="name" required maxlength="100" placeholder="e.g. Financial Documents">
                    </div>
                    <div class="mb-3">
                        <label for="catDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="catDescription" name="description" rows="2" maxlength="500" placeholder="Describe the purpose of this category..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="catParent" class="form-label">Parent Category</label>
                        <select class="form-select" id="catParent" name="parent_id">
                            <option value="">None (Top-level)</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Only top-level categories can be parents (max 2 levels).</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="catIcon" class="form-label">Icon (CSS class)</label>
                            <input type="text" class="form-control" id="catIcon" name="icon" placeholder="fas fa-folder" maxlength="50">
                            <small class="text-muted">e.g., fas fa-file, fas fa-book</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="catColor" class="form-label">Color</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" class="form-control form-control-color" id="catColorPicker" value="#3b82f6" style="width:40px;height:38px;">
                                <input type="text" class="form-control" id="catColor" name="color" placeholder="#3b82f6" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="catSortOrder" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="catSortOrder" name="sort_order" value="0" min="0" placeholder="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="catRetention" class="form-label">Retention (days)</label>
                            <input type="number" class="form-control" id="catRetention" name="retention_days" min="1" placeholder="Optional">
                        </div>
                        <div class="col-md-4 mb-3 d-flex flex-column justify-content-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="catRequiresApproval" name="requires_approval" value="1">
                                <label class="form-check-label" for="catRequiresApproval">Requires Approval</label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="catIsActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="catIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Category</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
