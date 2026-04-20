<div class="help-text">
    <div class="help-title">Publishers</div>
    <p class="help-content">Manage the publishers available in the library catalog. Publishers created here will appear in the book add/edit forms.</p>
</div>

{{-- Inline Add Row --}}
<div class="settings-section">
    <div class="row g-2 align-items-end" style="max-width: 450px;">
        <div class="col">
            <label class="form-label">Publisher Name</label>
            <input type="text" class="form-control form-control-sm" id="publisherName" placeholder="Publisher name" maxlength="150">
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary btn-sm" id="addPublisherBtn" style="margin-bottom: 1px;">
                <i class="fas fa-plus me-1"></i> Add
            </button>
        </div>
    </div>
</div>

{{-- Search --}}
<div class="mb-3" style="max-width: 300px;">
    <input type="text" class="form-control form-control-sm" id="publisherSearch" placeholder="Search publishers...">
</div>

{{-- Publishers Table --}}
<div class="table-responsive">
    <table class="table table-sm table-hover mb-0" id="publishersTable">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Publisher Name</th>
                <th style="width: 80px;">Books</th>
                <th style="width: 120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($publishers as $publisher)
                <tr data-id="{{ $publisher->id }}">
                    <td class="row-number">{{ $loop->iteration }}</td>
                    <td class="publisher-name">
                        <span class="display-value">{{ $publisher->name }}</span>
                        <input type="text" class="form-control form-control-sm inline-edit-input d-none" value="{{ $publisher->name }}" maxlength="150">
                    </td>
                    <td><span class="count-badge">{{ $publisher->books_count }}</span></td>
                    <td>
                        <div class="display-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary edit-publisher-btn" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-publisher-btn" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <div class="edit-actions d-none">
                            <button type="button" class="btn btn-sm btn-success save-publisher-btn" title="Save">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary cancel-publisher-btn" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="4" class="text-center text-muted py-4">No publishers found. Add one above.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
