<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="fas fa-tags me-2 text-primary"></i>Tags</h5>
    <div class="d-flex gap-2">
        @if($tags->count() >= 2)
            <button type="button" class="btn btn-warning-custom btn-sm" id="btnMergeTags">
                <i class="fas fa-code-branch"></i> Merge Tags
            </button>
        @endif
        <button type="button" class="btn btn-primary" id="btnNewTag">
            <i class="fas fa-plus me-1"></i> New Tag
        </button>
    </div>
</div>

@if($tags->isEmpty())
    <div class="empty-state">
        <i class="fas fa-tags d-block"></i>
        <h5>No tags yet</h5>
        <p>Create your first document tag to get started.</p>
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
                        <td>{{ $tag->createdBy ? $tag->createdBy->full_name : '-' }}</td>
                        <td class="action-btns">
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="openEditTagModal({{ $tag->id }}, {{ json_encode($tag) }})">
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

{{-- Create/Edit Tag Modal --}}
<div class="modal fade" id="tagModal" tabindex="-1" aria-labelledby="tagModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                        <input type="text" class="form-control" id="tagName" name="name" required maxlength="100" placeholder="e.g. Confidential">
                    </div>
                    <div class="mb-3">
                        <label for="tagDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="tagDescription" name="description" rows="2" maxlength="500" placeholder="Describe what this tag represents..."></textarea>
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
<div class="modal fade" id="mergeModal" tabindex="-1" aria-labelledby="mergeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
