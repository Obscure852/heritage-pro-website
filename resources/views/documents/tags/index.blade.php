@extends('layouts.master')
@section('title')
    Document Tags
@endsection
@section('css')
    <style>
        .tags-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .tags-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .tags-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .tags-header p {
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

        .btn-warning-custom {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning-custom:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .tags-body {
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

        .color-swatch {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
            margin-right: 6px;
        }

        .badge-official {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-user {
            background: #f3f4f6;
            color: #6b7280;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .usage-badge {
            background: #f0fdf4;
            color: #166534;
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .usage-badge.zero {
            background: #f3f4f6;
            color: #9ca3af;
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

        .merge-arrow {
            font-size: 24px;
            color: #6b7280;
            text-align: center;
            padding: 10px 0;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="tags-container">
            <div class="tags-header d-flex justify-content-between align-items-center">
                <div>
                    <h4><i class="fas fa-tags me-2"></i>Document Tags</h4>
                    <p>Manage document tags and merge duplicates</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('documents.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Documents
                    </a>
                    @if($tags->count() >= 2)
                        <button type="button" class="btn btn-warning-custom" id="btnMergeTags">
                            <i class="fas fa-code-branch"></i> Merge Tags
                        </button>
                    @endif
                    <button type="button" class="btn btn-light" id="btnNewTag">
                        <i class="fas fa-plus"></i> New Tag
                    </button>
                </div>
            </div>

            <div class="tags-body">
                @if($tags->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-tags d-block"></i>
                        <h5>No tags yet</h5>
                        <p>Create your first document tag to get started.</p>
                        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i> Create Tag
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Color</th>
                                    <th>Type</th>
                                    <th>Usage Count</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tags as $tag)
                                    <tr>
                                        <td>
                                            @if($tag->color)
                                                <span class="color-swatch" style="background-color: {{ $tag->color }}"></span>
                                            @endif
                                            <strong>{{ $tag->name }}</strong>
                                        </td>
                                        <td>
                                            @if($tag->color)
                                                <code>{{ $tag->color }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-{{ $tag->is_official ? 'official' : 'user' }}">
                                                {{ $tag->is_official ? 'Official' : 'User' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="usage-badge {{ $tag->usage_count === 0 ? 'zero' : '' }}">
                                                {{ $tag->usage_count }}
                                            </span>
                                        </td>
                                        <td>{{ $tag->createdBy->full_name ?? '-' }}</td>
                                        <td class="action-btns">
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick="openEditModal({{ $tag->id }}, {{ json_encode($tag) }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="deleteTag({{ $tag->id }}, '{{ addslashes($tag->name) }}', {{ $tag->usage_count }})"
                                                @if($tag->usage_count > 0) title="Merge this tag instead - it has {{ $tag->usage_count }} document(s)" @endif
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

    {{-- Create/Edit Tag Modal --}}
    <div class="modal fade" id="tagModal" tabindex="-1" aria-labelledby="tagModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tagModalLabel">New Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tagForm">
                    @csrf
                    <input type="hidden" id="tagId" name="tag_id" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tagName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tagName" name="name" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="tagDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="tagDescription" name="description" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tagColor" class="form-label">Color</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" class="form-control form-control-color" id="tagColorPicker" value="#3b82f6" style="width:40px;height:38px;">
                                <input type="text" class="form-control" id="tagColor" name="color" placeholder="#3b82f6" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tagIsOfficial" name="is_official" value="1" checked>
                                <label class="form-check-label" for="tagIsOfficial">Official Tag</label>
                            </div>
                            <small class="text-muted">Official tags are created by administrators. Uncheck for user-created tags.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Save Tag</span>
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

    {{-- Merge Tags Modal --}}
    <div class="modal fade" id="mergeModal" tabindex="-1" aria-labelledby="mergeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mergeModalLabel"><i class="fas fa-code-branch me-2"></i>Merge Tags</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="mergeForm">
                    @csrf
                    <div class="modal-body">
                        <div class="help-text" style="background: #fffbeb; border-left-color: #f59e0b;">
                            <div class="help-title" style="color: #92400e;">Warning</div>
                            <div class="help-content" style="color: #78350f;">
                                Merging will move all documents from the source tag to the target tag, then permanently delete the source tag. This cannot be undone.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="mergeSource" class="form-label">Merge FROM (source - will be deleted)</label>
                            <select class="form-select" id="mergeSource" name="source_id" required>
                                <option value="">Select source tag...</option>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" data-usage="{{ $tag->usage_count }}">{{ $tag->name }} ({{ $tag->usage_count }} docs)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="merge-arrow">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="mb-3">
                            <label for="mergeTarget" class="form-label">Merge INTO (target - will keep)</label>
                            <select class="form-select" id="mergeTarget" name="target_id" required>
                                <option value="">Select target tag...</option>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}">{{ $tag->name }} ({{ $tag->usage_count }} docs)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning-custom btn-loading" id="btnConfirmMerge">
                            <span class="btn-text"><i class="fas fa-code-branch"></i> Merge Tags</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Merging...
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
        const tagModal = new bootstrap.Modal(document.getElementById('tagModal'));
        const mergeModal = new bootstrap.Modal(document.getElementById('mergeModal'));
        let editingTagId = null;

        // Sync color picker with text input
        document.getElementById('tagColorPicker').addEventListener('input', function () {
            document.getElementById('tagColor').value = this.value;
        });
        document.getElementById('tagColor').addEventListener('input', function () {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                document.getElementById('tagColorPicker').value = this.value;
            }
        });

        document.getElementById('btnNewTag').addEventListener('click', function () {
            openCreateModal();
        });

        var btnMerge = document.getElementById('btnMergeTags');
        if (btnMerge) {
            btnMerge.addEventListener('click', function () {
                document.getElementById('mergeForm').reset();
                mergeModal.show();
            });
        }

        function openCreateModal() {
            editingTagId = null;
            document.getElementById('tagModalLabel').textContent = 'New Tag';
            document.getElementById('tagForm').reset();
            document.getElementById('tagId').value = '';
            document.getElementById('tagIsOfficial').checked = true;
            document.getElementById('tagColorPicker').value = '#3b82f6';
            tagModal.show();
        }

        function openEditModal(id, tag) {
            editingTagId = id;
            document.getElementById('tagModalLabel').textContent = 'Edit Tag';
            document.getElementById('tagId').value = id;
            document.getElementById('tagName').value = tag.name || '';
            document.getElementById('tagDescription').value = tag.description || '';
            document.getElementById('tagColor').value = tag.color || '';
            document.getElementById('tagColorPicker').value = tag.color || '#3b82f6';
            document.getElementById('tagIsOfficial').checked = !!tag.is_official;
            tagModal.show();
        }

        document.getElementById('tagForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const submitBtn = form.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }

            const formData = {
                name: document.getElementById('tagName').value,
                description: document.getElementById('tagDescription').value || null,
                color: document.getElementById('tagColor').value || null,
                is_official: document.getElementById('tagIsOfficial').checked ? 1 : 0,
                _token: '{{ csrf_token() }}',
            };

            const isEdit = !!editingTagId;
            const url = isEdit
                ? '{{ url("documents/tags") }}/' + editingTagId
                : '{{ route("documents.tags.store") }}';

            if (isEdit) {
                formData._method = 'PUT';
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function (response) {
                    tagModal.hide();
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

        document.getElementById('mergeForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const sourceId = document.getElementById('mergeSource').value;
            const targetId = document.getElementById('mergeTarget').value;

            if (!sourceId || !targetId) {
                Swal.fire({ icon: 'warning', title: 'Select Both Tags', text: 'Please select both source and target tags.' });
                return;
            }

            if (sourceId === targetId) {
                Swal.fire({ icon: 'warning', title: 'Invalid Selection', text: 'Source and target must be different tags.' });
                return;
            }

            const sourceName = document.querySelector('#mergeSource option[value="' + sourceId + '"]').textContent.split(' (')[0];
            const targetName = document.querySelector('#mergeTarget option[value="' + targetId + '"]').textContent.split(' (')[0];
            const sourceUsage = document.querySelector('#mergeSource option[value="' + sourceId + '"]').dataset.usage || 0;

            Swal.fire({
                title: 'Confirm Merge',
                html: 'This will move all <strong>' + sourceUsage + '</strong> document(s) from <strong>"' + sourceName + '"</strong> to <strong>"' + targetName + '"</strong> and delete <strong>"' + sourceName + '"</strong>.<br><br>This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Yes, merge them',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    const submitBtn = document.getElementById('btnConfirmMerge');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }

                    $.ajax({
                        url: '{{ route("documents.tags.merge") }}',
                        type: 'POST',
                        data: {
                            source_id: sourceId,
                            target_id: targetId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            mergeModal.hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Merged',
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
                            Swal.fire({
                                icon: 'error',
                                title: 'Merge Failed',
                                text: xhr.responseJSON?.message || 'Failed to merge tags.',
                            });
                        }
                    });
                }
            });
        });

        function deleteTag(id, name, usageCount) {
            if (usageCount > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Delete',
                    text: 'This tag is used by ' + usageCount + ' document(s). Use "Merge Tags" to merge it into another tag instead.',
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
                        url: '{{ url("documents/tags") }}/' + id,
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
                                text: xhr.responseJSON?.message || 'Failed to delete tag.',
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
