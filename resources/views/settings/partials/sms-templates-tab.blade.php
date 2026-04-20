<div class="help-text">
    <div class="help-title"><i class="fas fa-info-circle me-2"></i>SMS Templates</div>
    <p class="help-content">Create and manage reusable SMS templates here. These templates remain part of Communications Setup so they can be prepared before SMS sending is activated.</p>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div class="d-flex flex-wrap gap-3">
        <div class="template-stat-card">
            <span class="template-stat-value">{{ $smsTemplates->total() }}</span>
            <span class="template-stat-label">Total Templates</span>
        </div>
        <div class="template-stat-card">
            <span class="template-stat-value">{{ $smsTemplateActiveCount }}</span>
            <span class="template-stat-label">Active Templates</span>
        </div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
        <i class="fas fa-plus"></i> New Template
    </button>
</div>

<div class="form-section">
    <div class="row g-3 align-items-end">
        <div class="col-lg-5 col-md-6">
            <label for="smsTemplateSearchFilter" class="form-label">Search Templates</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="smsTemplateSearchFilter"
                    placeholder="Search by name or content..."
                    value="{{ request('sms_template_search') }}"
                    onkeyup="debounceSmsTemplateSearch()">
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <label for="smsTemplateCategoryFilter" class="form-label">Category</label>
            <select class="form-select" id="smsTemplateCategoryFilter" onchange="filterSmsTemplates()">
                <option value="">All Categories</option>
                @foreach ($smsTemplateCategories as $key => $label)
                    <option value="{{ $key }}" {{ request('sms_template_category') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-3 col-md-12">
            <button type="button" class="btn btn-secondary w-100" onclick="resetSmsTemplateFilters()">
                <i class="fas fa-undo"></i> Reset Filters
            </button>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-title">
        <i class="fas fa-code"></i> Available Placeholders
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach ($smsTemplatePlaceholders as $placeholder => $description)
            <span class="placeholder-tag" title="{{ $description }}">{{ $placeholder }}</span>
        @endforeach
    </div>
</div>

@if ($smsTemplates->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-0">No SMS templates found. Create your first template to start building reusable messages.</p>
    </div>
@else
    <div class="row">
        @foreach ($smsTemplates as $template)
            <div class="col-xl-6">
                <div class="template-card {{ !$template->is_active ? 'inactive' : '' }}">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="template-name">{{ $template->name }}</div>
                            <span class="template-category">{{ $smsTemplateCategories[$template->category] ?? $template->category }}</span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" type="button">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="smsTemplateEdit({{ $template->id }})">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="smsTemplateToggle({{ $template->id }})">
                                        <i class="fas fa-toggle-{{ $template->is_active ? 'on' : 'off' }} me-2"></i>{{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="smsTemplateDelete({{ $template->id }})">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if ($template->description)
                        <p class="text-muted small mb-2">{{ $template->description }}</p>
                    @endif

                    <div class="template-content">{{ Str::limit($template->content, 180) }}</div>

                    <div class="template-meta d-flex justify-content-between flex-wrap gap-2">
                        <span><i class="fas fa-text-width me-1"></i>{{ strlen($template->content) }} chars ({{ ceil(strlen($template->content) / 160) }} SMS)</span>
                        <span><i class="fas fa-chart-bar me-1"></i>Used {{ $template->usage_count }} times</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $smsTemplates->links() }}
    </div>
@endif

<div class="modal fade" id="createTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createTemplateForm" method="POST" action="{{ route('sms-templates.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create SMS Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="255" placeholder="e.g., Fee Reminder">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" required>
                            @foreach ($smsTemplateCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="content" rows="4" required maxlength="480"
                            placeholder="Type your message here. Use placeholders like {student_name} for dynamic content."
                            oninput="updateSmsTemplateCharacterCount(this, 'createSmsTemplateCharCount')"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Use placeholders from the list above</small>
                            <span id="createSmsTemplateCharCount" class="character-counter">0/480 characters (0 SMS)</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Description (Optional)</label>
                        <input type="text" class="form-control" name="description" maxlength="500"
                            placeholder="Brief description of when to use this template">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editTemplateForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit SMS Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editSmsTemplateName" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" id="editSmsTemplateCategory" required>
                            @foreach ($smsTemplateCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="content" id="editSmsTemplateContent" rows="4" required maxlength="480"
                            oninput="updateSmsTemplateCharacterCount(this, 'editSmsTemplateCharCount')"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Use placeholders from the list above</small>
                            <span id="editSmsTemplateCharCount" class="character-counter">0/480 characters (0 SMS)</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Description (Optional)</label>
                        <input type="text" class="form-control" name="description" id="editSmsTemplateDescription" maxlength="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
