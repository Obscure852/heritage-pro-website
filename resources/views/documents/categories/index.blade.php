@extends('layouts.master')
@section('title')
    Document Categories
@endsection
@section('css')
    <style>
        .categories-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .categories-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .categories-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .categories-header p {
            margin: 4px 0 0;
            opacity: 0.85;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-light:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .categories-body {
            padding: 24px;
        }

        .table th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }

        .table td {
            vertical-align: middle;
            font-size: 14px;
        }

        .child-category {
            color: #6b7280;
        }

        .color-swatch {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-yes {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-no {
            background: #f3f4f6;
            color: #6b7280;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .action-btns .btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="categories-container">
            <div class="categories-header d-flex justify-content-between align-items-center">
                <div>
                    <h4><i class="fas fa-folder-tree me-2"></i>Document Categories</h4>
                    <p>Manage document categories and hierarchy</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('documents.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Documents
                    </a>
                    <button type="button" class="btn btn-light" id="btnNewCategory">
                        <i class="fas fa-plus"></i> New Category
                    </button>
                </div>
            </div>

            <div class="categories-body">
                @if($categories->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-folder-open d-block"></i>
                        <h5>No categories yet</h5>
                        <p>Create your first document category to get started.</p>
                        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i> Create Category
                        </button>
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
                                                onclick="openEditModal({{ $category->id }}, {{ json_encode($category) }})">
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
            </div>
        </div>
    </div>

    {{-- Create/Edit Category Modal --}}
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
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
                            <input type="text" class="form-control" id="catName" name="name" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="catDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="catDescription" name="description" rows="2" maxlength="500"></textarea>
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
                                <input type="number" class="form-control" id="catSortOrder" name="sort_order" value="0" min="0">
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
@endsection

@section('js')
    <script>
        const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        let editingCategoryId = null;

        // Sync color picker with text input
        document.getElementById('catColorPicker').addEventListener('input', function () {
            document.getElementById('catColor').value = this.value;
        });
        document.getElementById('catColor').addEventListener('input', function () {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                document.getElementById('catColorPicker').value = this.value;
            }
        });

        document.getElementById('btnNewCategory').addEventListener('click', function () {
            openCreateModal();
        });

        function openCreateModal() {
            editingCategoryId = null;
            document.getElementById('categoryModalLabel').textContent = 'New Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('catIsActive').checked = true;
            document.getElementById('catColorPicker').value = '#3b82f6';
            // Show all parent options
            document.querySelectorAll('#catParent option').forEach(opt => opt.style.display = '');
            categoryModal.show();
        }

        function openEditModal(id, category) {
            editingCategoryId = id;
            document.getElementById('categoryModalLabel').textContent = 'Edit Category';
            document.getElementById('categoryId').value = id;
            document.getElementById('catName').value = category.name || '';
            document.getElementById('catDescription').value = category.description || '';
            document.getElementById('catParent').value = category.parent_id || '';
            document.getElementById('catIcon').value = category.icon || '';
            document.getElementById('catColor').value = category.color || '';
            document.getElementById('catColorPicker').value = category.color || '#3b82f6';
            document.getElementById('catSortOrder').value = category.sort_order || 0;
            document.getElementById('catRetention').value = category.retention_days || '';
            document.getElementById('catRequiresApproval').checked = !!category.requires_approval;
            document.getElementById('catIsActive').checked = category.is_active !== false;

            // Hide self from parent dropdown to prevent self-reference
            document.querySelectorAll('#catParent option').forEach(opt => {
                opt.style.display = (parseInt(opt.value) === id) ? 'none' : '';
            });

            categoryModal.show();
        }

        document.getElementById('categoryForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const submitBtn = form.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }

            const formData = {
                name: document.getElementById('catName').value,
                description: document.getElementById('catDescription').value || null,
                parent_id: document.getElementById('catParent').value || null,
                icon: document.getElementById('catIcon').value || null,
                color: document.getElementById('catColor').value || null,
                sort_order: parseInt(document.getElementById('catSortOrder').value) || 0,
                retention_days: document.getElementById('catRetention').value ? parseInt(document.getElementById('catRetention').value) : null,
                requires_approval: document.getElementById('catRequiresApproval').checked ? 1 : 0,
                is_active: document.getElementById('catIsActive').checked ? 1 : 0,
                _token: '{{ csrf_token() }}',
            };

            const isEdit = !!editingCategoryId;
            const url = isEdit
                ? '{{ url("documents/categories") }}/' + editingCategoryId
                : '{{ route("documents.categories.store") }}';

            if (isEdit) {
                formData._method = 'PUT';
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function (response) {
                    categoryModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function () {
                        window.location.reload();
                    });
                },
                error: function (xhr) {
                    if (submitBtn) {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                    }
                    const msg = xhr.responseJSON?.message || 'An error occurred.';
                    const errors = xhr.responseJSON?.errors;
                    let errorText = msg;
                    if (errors) {
                        errorText = Object.values(errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorText,
                    });
                }
            });
        });

        function deleteCategory(id, name, docCount, childCount) {
            if (docCount > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Delete',
                    text: 'This category has ' + docCount + ' document(s). Remove or reassign them first.',
                });
                return;
            }
            if (childCount > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Delete',
                    text: 'This category has ' + childCount + ' child category(ies). Remove them first.',
                });
                return;
            }

            Swal.fire({
                title: 'Delete "' + name + '"?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("documents/categories") }}/' + id,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(function () {
                                window.location.reload();
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Failed to delete category.',
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
