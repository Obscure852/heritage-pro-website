<div class="help-text">
    <div class="help-title">Authors</div>
    <p class="help-content">Manage the authors available in the library catalog. Authors created here will appear in the book add/edit forms.</p>
</div>

{{-- Inline Add Row --}}
<div class="settings-section">
    <div class="row g-2 align-items-end" style="max-width: 600px;">
        <div class="col">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control form-control-sm" id="authorFirstName" placeholder="First name" maxlength="100">
        </div>
        <div class="col">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control form-control-sm" id="authorLastName" placeholder="Last name" maxlength="100">
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary btn-sm" id="addAuthorBtn" style="margin-bottom: 1px;">
                <i class="fas fa-plus me-1"></i> Add
            </button>
        </div>
    </div>
</div>

{{-- Search --}}
<div class="mb-3" style="max-width: 300px;">
    <input type="text" class="form-control form-control-sm" id="authorSearch" placeholder="Search authors...">
</div>

{{-- Authors Table --}}
<div class="table-responsive">
    <table class="table table-sm table-hover mb-0" id="authorsTable">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th style="width: 80px;">Books</th>
                <th style="width: 120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($authors as $author)
                <tr data-id="{{ $author->id }}">
                    <td class="row-number">{{ $loop->iteration }}</td>
                    <td class="author-first-name">
                        <span class="display-value">{{ $author->first_name }}</span>
                        <input type="text" class="form-control form-control-sm inline-edit-input d-none" value="{{ $author->first_name }}" maxlength="100">
                    </td>
                    <td class="author-last-name">
                        <span class="display-value">{{ $author->last_name }}</span>
                        <input type="text" class="form-control form-control-sm inline-edit-input d-none" value="{{ $author->last_name }}" maxlength="100">
                    </td>
                    <td><span class="count-badge">{{ $author->books_count + $author->books_pivot_count }}</span></td>
                    <td>
                        <div class="display-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary edit-author-btn" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-author-btn" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <div class="edit-actions d-none">
                            <button type="button" class="btn btn-sm btn-success save-author-btn" title="Save">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary cancel-author-btn" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="5" class="text-center text-muted py-4">No authors found. Add one above.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
